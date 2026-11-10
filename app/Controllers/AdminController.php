<?php
class AdminController {
  function __construct() { Auth::need('admin'); }
  function stats() {
    $c = fn($s) => (int)q($s)->fetchColumn();
    out(['customers' => $c('SELECT COUNT(*) FROM users WHERE role_id=1'), 'owners' => $c('SELECT COUNT(*) FROM users WHERE role_id=2'),
      'approved' => $c("SELECT COUNT(*) FROM businesses WHERE status='approved'"), 'pending' => $c("SELECT COUNT(*) FROM businesses WHERE status='pending'"),
      'products' => $c("SELECT COUNT(*) FROM products WHERE status='approved'"), 'reviews' => $c('SELECT COUNT(*) FROM reviews'), 'inquiries' => $c('SELECT COUNT(*) FROM inquiries'),
      'open_reports' => $c("SELECT COUNT(*) FROM reports WHERE status='open'"), 'visits' => $c('SELECT COALESCE(SUM(views),0) FROM businesses'),
      'pending_verifications' => $c("SELECT COUNT(*) FROM verification_requests WHERE status='pending'"), 'pending_products' => $c("SELECT COUNT(*) FROM products WHERE status='pending'")]);
  }
  function businesses() { out(q('SELECT b.id,b.name,b.status,b.verified,b.featured,b.is_sample,u.name owner,c.name category,l.city FROM businesses b JOIN users u ON u.id=b.owner_id JOIN business_categories c ON c.id=b.category_id LEFT JOIN business_locations l ON l.business_id=b.id ORDER BY FIELD(b.status,"pending","approved","rejected"),b.id DESC')->fetchAll()); }
  function act($id) {
    $a = body()['action'] ?? ''; $b = q('SELECT owner_id,name FROM businesses WHERE id=?', [$id])->fetch() ?: out(['error' => 'Not found'], 404);
    $map = ['approve' => "status='approved'", 'reject' => "status='rejected'", 'verify' => 'verified=1', 'unverify' => 'verified=0', 'feature' => 'featured=1', 'unfeature' => 'featured=0'];
    if ($a === 'delete') q('DELETE FROM businesses WHERE id=?', [$id]); elseif (isset($map[$a])) q("UPDATE businesses SET {$map[$a]} WHERE id=?", [$id]); else out(['error' => 'Unknown action'], 422);
    if ($a === 'approve' || $a === 'reject') notify($b['owner_id'], "Your business {$b['name']} was " . ($a === 'approve' ? 'approved and is now live.' : 'rejected.'));
    Audit::log('business_' . $a, 'business', (int)$id, $b['name']); out(['ok' => 1]);
  }
  function users() { out(q('SELECT u.id,u.name,u.email,u.active,r.name role,u.created_at FROM users u JOIN roles r ON r.id=u.role_id ORDER BY u.id DESC')->fetchAll()); }
  function toggleUser($id) {
    if ($id == user()['id']) out(['error' => 'You cannot disable yourself.'], 422);
    q('UPDATE users SET active=1-active WHERE id=?', [$id]); Audit::log('user_toggled', 'user', (int)$id); out(['ok' => 1]);
  }
  function reports() { out(q('SELECT r.*,u.name reporter FROM reports r JOIN users u ON u.id=r.user_id ORDER BY r.id DESC LIMIT 100')->fetchAll()); }
  function addCategory() {
    $b = body(); $t = ($b['type'] ?? '') === 'product' ? 'product_categories' : 'business_categories'; if (!trim($b['name'] ?? '')) out(['error' => 'Enter a category name.'], 422);
    q("INSERT INTO $t(name) VALUES(?)", [trim($b['name'])]); Audit::log('category_added', $t, null, trim($b['name'])); out(['ok' => 1]);
  }
  function deleteProduct($id) { q('DELETE FROM products WHERE id=?', [$id]); Audit::log('product_removed', 'product', (int)$id); out(['ok' => 1]); }

  // ---- Business verification ----
  function verifications() {
    $s = in_array($_GET['status'] ?? '', ['pending', 'approved', 'rejected'], true) ? $_GET['status'] : null;
    out(q('SELECT v.id,v.business_id,v.doc_type,v.note,v.status,v.review_note,v.created_at,v.reviewed_at,b.name business,u.name owner FROM verification_requests v
      JOIN businesses b ON b.id=v.business_id JOIN users u ON u.id=b.owner_id ' . ($s ? 'WHERE v.status=? ' : '') . 'ORDER BY FIELD(v.status,"pending","approved","rejected"),v.id DESC LIMIT 100', $s ? [$s] : [])->fetchAll());
  }
  /** Streams a verification document. Only admins can reach this (see constructor). */
  function document($id) {
    $v = q('SELECT document_path FROM verification_requests WHERE id=?', [$id])->fetch() ?: out(['error' => 'Not found'], 404);
    $file = DocumentUpload::DIR . '/' . basename($v['document_path']);
    if (!is_file($file)) out(['error' => 'File is missing'], 404);
    $mime = ['pdf' => 'application/pdf', 'jpg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp'][pathinfo($file, PATHINFO_EXTENSION)] ?? 'application/octet-stream';
    Audit::log('verification_document_viewed', 'verification', (int)$id);
    header('Content-Type: ' . $mime); header('X-Content-Type-Options: nosniff'); header('Content-Disposition: inline'); header('Cache-Control: private, no-store'); readfile($file); exit;
  }
  function reviewVerification($id) {
    $d = body(); $a = $d['action'] ?? ''; $note = mb_substr(trim($d['note'] ?? ''), 0, 255);
    if (!in_array($a, ['approve', 'reject'], true)) out(['error' => 'Unknown action'], 422);
    if ($a === 'reject' && $note === '') out(['error' => 'Give the owner a reason for rejecting.'], 422);
    $v = q("SELECT v.business_id,b.owner_id,b.name FROM verification_requests v JOIN businesses b ON b.id=v.business_id WHERE v.id=? AND v.status='pending'", [$id])->fetch() ?: out(['error' => 'Request not found or already reviewed.'], 404);
    q('UPDATE verification_requests SET status=?,review_note=?,reviewed_by=?,reviewed_at=NOW() WHERE id=?', [$a === 'approve' ? 'approved' : 'rejected', $note ?: null, user()['id'], $id]);
    if ($a === 'approve') q('UPDATE businesses SET verified=1 WHERE id=?', [$v['business_id']]);
    notify($v['owner_id'], $a === 'approve' ? "{$v['name']} is now verified." : "Verification for {$v['name']} was rejected: $note");
    Audit::log('verification_' . ($a === 'approve' ? 'approved' : 'rejected'), 'business', (int)$v['business_id'], $v['name'] . ($note ? " – $note" : '')); out(['ok' => 1]);
  }

  // ---- Product approval ----
  function products() {
    $s = in_array($_GET['status'] ?? '', ['pending', 'approved', 'rejected'], true) ? $_GET['status'] : 'pending';
    out(q('SELECT p.id,p.name,p.description,p.price,p.status,p.review_note,p.created_at,b.id business_id,b.name business,(SELECT path FROM product_images WHERE product_id=p.id LIMIT 1) image
      FROM products p JOIN businesses b ON b.id=p.business_id WHERE p.status=? ORDER BY p.id DESC LIMIT 100', [$s])->fetchAll());
  }
  function reviewProduct($id) {
    $d = body(); $a = $d['action'] ?? ''; $note = mb_substr(trim($d['note'] ?? ''), 0, 255);
    if (!in_array($a, ['approve', 'reject'], true)) out(['error' => 'Unknown action'], 422);
    if ($a === 'reject' && $note === '') out(['error' => 'Give the owner a reason for rejecting.'], 422);
    $p = q('SELECT p.name,b.owner_id FROM products p JOIN businesses b ON b.id=p.business_id WHERE p.id=?', [$id])->fetch() ?: out(['error' => 'Product not found'], 404);
    q('UPDATE products SET status=?,review_note=? WHERE id=?', [$a === 'approve' ? 'approved' : 'rejected', $a === 'approve' ? null : $note, $id]);
    notify($p['owner_id'], $a === 'approve' ? "Your product {$p['name']} was approved." : "Your product {$p['name']} was rejected: $note");
    Audit::log('product_' . ($a === 'approve' ? 'approved' : 'rejected'), 'product', (int)$id, $p['name'] . ($note ? " – $note" : '')); out(['ok' => 1]);
  }

  // ---- Analytics ----
  function analytics() { out(Analytics::platform(Analytics::days($_GET['days'] ?? 30))); }
  function report() {
    $a = Analytics::platform(Analytics::days($_GET['days'] ?? 30)); Audit::log('report_exported', 'analytics', null, "{$a['from']} to {$a['to']}");
    Analytics::csv("lokalph-platform-{$a['from']}-to-{$a['to']}.csv", ['Day', 'New users', 'New businesses', 'Inquiries', 'Profile views'], $a['series']);
  }

  // ---- Audit logs ----
  function audit() {
    $w = []; $p = [];
    if (!empty($_GET['q'])) { $w[] = '(a.action LIKE ? OR a.details LIKE ? OR u.name LIKE ?)'; $like = '%' . $_GET['q'] . '%'; array_push($p, $like, $like, $like); }
    $where = $w ? 'WHERE ' . implode(' AND ', $w) : '';
    $lim = max(1, min(100, (int)($_GET['limit'] ?? 25))); $off = max(0, (int)($_GET['offset'] ?? 0));
    $total = (int)q("SELECT COUNT(*) FROM audit_logs a LEFT JOIN users u ON u.id=a.user_id $where", $p)->fetchColumn();
    $rows = q("SELECT a.id,a.action,a.entity_type,a.entity_id,a.details,a.ip,a.created_at,u.name user FROM audit_logs a LEFT JOIN users u ON u.id=a.user_id $where ORDER BY a.id DESC LIMIT $lim OFFSET $off", $p)->fetchAll();
    header('X-Total-Count: ' . $total); out($rows);
  }
}