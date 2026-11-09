<?php
class PublicController {
  function categories() { out(['business' => q('SELECT * FROM business_categories')->fetchAll(), 'product' => q('SELECT * FROM product_categories')->fetchAll()]); }
  function businesses() { $rows = Business::search($_GET, $total); header('X-Total-Count: ' . $total); out($rows); }
  function business($id) {
    $b = Business::search(array_merge($_GET, ['id' => $id]))[0] ?? out(['error' => 'Business not found'], 404);
    q('UPDATE businesses SET views=views+1 WHERE id=?', [$id]);
    $b['hours'] = q('SELECT day,opens,closes,is_closed FROM business_hours WHERE business_id=? ORDER BY day', [$id])->fetchAll();
    $b['items'] = Product::search(array_merge($_GET, ['business_id' => $id]));
    $b['review_list'] = q('SELECT r.rating,r.comment,r.created_at,u.name FROM reviews r JOIN users u ON u.id=r.user_id WHERE business_id=? ORDER BY r.id DESC', [$id])->fetchAll();
    out($b);
  }
  function products() { out(Product::search($_GET)); }
  function product($id) { $r = Product::search(array_merge($_GET, ['id' => $id]))[0] ?? out(['error' => 'Product not found'], 404); q('UPDATE products SET views=views+1 WHERE id=?', [$id]); out($r); }
  function toggleFav() {
    $u = Auth::need(); $b = body(); $col = ($b['type'] ?? '') === 'product' ? 'product_id' : 'business_id'; $id = (int)($b['id'] ?? 0);
    $x = q("SELECT id FROM favorites WHERE user_id=? AND $col=?", [$u['id'], $id])->fetch();
    if ($x) q('DELETE FROM favorites WHERE id=?', [$x['id']]); else q("INSERT INTO favorites(user_id,$col) VALUES(?,?)", [$u['id'], $id]);
    out(['saved' => !$x]);
  }
  function favorites() { $u = Auth::need(); $f = fn($c) => q("SELECT $c FROM favorites WHERE user_id=? AND $c IS NOT NULL", [$u['id']])->fetchAll(PDO::FETCH_COLUMN);
    out(['businesses' => $f('business_id'), 'products' => $f('product_id')]); }
  function review() {
    $u = Auth::need(); $b = body(); $r = (int)($b['rating'] ?? 0); $id = (int)($b['business_id'] ?? 0);
    if ($r < 1 || $r > 5) out(['error' => 'Choose a rating from 1 to 5.'], 422);
    $biz = q('SELECT owner_id,name FROM businesses WHERE id=? AND status="approved"', [$id])->fetch() ?: out(['error' => 'Business not found'], 404);
    q('INSERT INTO reviews(business_id,user_id,rating,comment) VALUES(?,?,?,?) ON DUPLICATE KEY UPDATE rating=VALUES(rating),comment=VALUES(comment)', [$id, $u['id'], $r, mb_substr(trim($b['comment'] ?? ''), 0, 1000)]);
    notify($biz['owner_id'], "{$u['name']} reviewed {$biz['name']} ($r/5)"); out(['ok' => 1]);
  }
  function inquiry() {
    $u = Auth::need(); $b = body(); $msg = trim($b['message'] ?? ''); $id = (int)($b['business_id'] ?? 0);
    if (strlen($msg) < 3) out(['error' => 'Write a message for the seller.'], 422);
    $biz = q('SELECT owner_id,name FROM businesses WHERE id=?', [$id])->fetch() ?: out(['error' => 'Business not found'], 404);
    q('INSERT INTO inquiries(business_id,product_id,user_id,message) VALUES(?,?,?,?)', [$id, ($b['product_id'] ?? null) ?: null, $u['id'], mb_substr($msg, 0, 1000)]);
    notify($biz['owner_id'], "New inquiry from {$u['name']} for {$biz['name']}"); out(['ok' => 1]);
  }
  function report() { $u = Auth::need(); $b = body(); if (strlen(trim($b['reason'] ?? '')) < 3) out(['error' => 'Tell us what is wrong.'], 422);
    q('INSERT INTO reports(user_id,business_id,product_id,reason) VALUES(?,?,?,?)', [$u['id'], $b['business_id'] ?? null, $b['product_id'] ?? null, mb_substr($b['reason'], 0, 500)]); out(['ok' => 1]); }
  function notifications() { $u = Auth::need(); out(q('SELECT message,is_read,created_at FROM notifications WHERE user_id=? ORDER BY id DESC LIMIT 30', [$u['id']])->fetchAll()); }
  function readNotes() { $u = Auth::need(); q('UPDATE notifications SET is_read=1 WHERE user_id=?', [$u['id']]); out(['ok' => 1]); }
}