<?php
class AdminController {
  function __construct() { Auth::need('admin'); }
  function stats() {
    $c = fn($s) => (int)q($s)->fetchColumn();
    out(['customers' => $c('SELECT COUNT(*) FROM users WHERE role_id=1'), 'owners' => $c('SELECT COUNT(*) FROM users WHERE role_id=2'),
      'approved' => $c("SELECT COUNT(*) FROM businesses WHERE status='approved'"), 'pending' => $c("SELECT COUNT(*) FROM businesses WHERE status='pending'"),
      'products' => $c('SELECT COUNT(*) FROM products'), 'reviews' => $c('SELECT COUNT(*) FROM reviews'), 'inquiries' => $c('SELECT COUNT(*) FROM inquiries'),
      'open_reports' => $c("SELECT COUNT(*) FROM reports WHERE status='open'"), 'visits' => $c('SELECT COALESCE(SUM(views),0) FROM businesses')]);
  }
  function businesses() { out(q('SELECT b.id,b.name,b.status,b.verified,b.featured,b.is_sample,u.name owner,c.name category,l.city FROM businesses b JOIN users u ON u.id=b.owner_id JOIN business_categories c ON c.id=b.category_id LEFT JOIN business_locations l ON l.business_id=b.id ORDER BY FIELD(b.status,"pending","approved","rejected"),b.id DESC')->fetchAll()); }
  function act($id) {
    $a = body()['action'] ?? ''; $b = q('SELECT owner_id,name FROM businesses WHERE id=?', [$id])->fetch() ?: out(['error' => 'Not found'], 404);
    $map = ['approve' => "status='approved'", 'reject' => "status='rejected'", 'verify' => 'verified=1', 'unverify' => 'verified=0', 'feature' => 'featured=1', 'unfeature' => 'featured=0'];
    if ($a === 'delete') q('DELETE FROM businesses WHERE id=?', [$id]); elseif (isset($map[$a])) q("UPDATE businesses SET {$map[$a]} WHERE id=?", [$id]); else out(['error' => 'Unknown action'], 422);
    if ($a === 'approve' || $a === 'reject') notify($b['owner_id'], "Your business {$b['name']} was " . ($a === 'approve' ? 'approved and is now live.' : 'rejected.'));
    out(['ok' => 1]);
  }
  function users() { out(q('SELECT u.id,u.name,u.email,u.active,r.name role,u.created_at FROM users u JOIN roles r ON r.id=u.role_id ORDER BY u.id DESC')->fetchAll()); }
  function toggleUser($id) { if ($id == user()['id']) out(['error' => 'You cannot disable yourself.'], 422); q('UPDATE users SET active=1-active WHERE id=?', [$id]); out(['ok' => 1]); }
  function reports() { out(q('SELECT r.*,u.name reporter FROM reports r JOIN users u ON u.id=r.user_id ORDER BY r.id DESC LIMIT 100')->fetchAll()); }
  function addCategory() { $b = body(); $t = ($b['type'] ?? '') === 'product' ? 'product_categories' : 'business_categories'; if (!trim($b['name'] ?? '')) out(['error' => 'Enter a category name.'], 422); q("INSERT INTO $t(name) VALUES(?)", [trim($b['name'])]); out(['ok' => 1]); }
  function deleteProduct($id) { q('DELETE FROM products WHERE id=?', [$id]); out(['ok' => 1]); }
}
