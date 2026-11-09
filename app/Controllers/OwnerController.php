<?php
class OwnerController {
  private function biz() { $u = Auth::need('owner'); return [$u, q('SELECT * FROM businesses WHERE owner_id=?', [$u['id']])->fetch()]; }
  function dashboard() {
    [$u, $b] = $this->biz(); if (!$b) out(['business' => null]); $id = $b['id'];
    $b['location'] = q('SELECT * FROM business_locations WHERE business_id=?', [$id])->fetch();
    $b['hours'] = q('SELECT day,opens,closes,is_closed FROM business_hours WHERE business_id=? ORDER BY day', [$id])->fetchAll();
    out(['business' => $b,
      'products' => q('SELECT p.*,(SELECT path FROM product_images WHERE product_id=p.id LIMIT 1) image FROM products p WHERE business_id=? ORDER BY id DESC', [$id])->fetchAll(),
      'inquiries' => q('SELECT i.message,i.created_at,u.name,u.email FROM inquiries i JOIN users u ON u.id=i.user_id WHERE business_id=? ORDER BY i.id DESC LIMIT 50', [$id])->fetchAll(),
      'reviews' => q('SELECT r.rating,r.comment,r.created_at,u.name FROM reviews r JOIN users u ON u.id=r.user_id WHERE business_id=? ORDER BY r.id DESC', [$id])->fetchAll(),
      'stats' => ['visits' => (int)$b['views'], 'product_views' => (int)q('SELECT COALESCE(SUM(views),0) FROM products WHERE business_id=?', [$id])->fetchColumn()]]);
  }
  function save() {
    [$u, $b] = $this->biz(); $d = body();
    $name = trim($d['name'] ?? ''); $lat = $d['lat'] ?? ''; $lng = $d['lng'] ?? '';
    if (!$name || !(int)($d['category_id'] ?? 0) || trim($d['address'] ?? '') === '' || trim($d['city'] ?? '') === '' || trim($d['province'] ?? '') === '') out(['error' => 'Fill in name, category, address, city and province.'], 422);
    if (!is_numeric($lat) || !is_numeric($lng) || abs($lat) > 90 || abs($lng) > 180) out(['error' => 'Pin your business on the map.'], 422);
    if ($lat < 4.5 || $lat > 21.5 || $lng < 116 || $lng > 127) out(['error' => 'Business location must be inside the Philippines.'], 422);
    $vals = [(int)$d['category_id'], $name, $d['description'] ?? '', $d['phone'] ?? '', $d['facebook'] ?? '', ($d['logo'] ?? '') ?: null, ($d['cover'] ?? '') ?: null];
    if ($b) { q('UPDATE businesses SET category_id=?,name=?,description=?,phone=?,facebook=?,logo=COALESCE(?,logo),cover=COALESCE(?,cover) WHERE id=?', [...$vals, $b['id']]); $id = $b['id']; }
    else { q('INSERT INTO businesses(category_id,name,description,phone,facebook,logo,cover,owner_id) VALUES(?,?,?,?,?,?,?,?)', [...$vals, $u['id']]); $id = db()->lastInsertId(); }
    q('REPLACE INTO business_locations(business_id,address,city,province,lat,lng) VALUES(?,?,?,?,?,?)', [$id, $d['address'], $d['city'], $d['province'], $lat, $lng]);
    $h = $d['hours'] ?? ['opens' => '08:00', 'closes' => '20:00', 'closed' => []];
    for ($i = 0; $i < 7; $i++) q('REPLACE INTO business_hours(business_id,day,opens,closes,is_closed) VALUES(?,?,?,?,?)', [$id, $i, $h['opens'], $h['closes'], in_array($i, $h['closed'] ?? []) ? 1 : 0]);
    out(['ok' => 1, 'message' => $b ? 'Business updated.' : 'Submitted. An admin will review your business before it goes live.']);
  }
  function saveProduct() {
    [$u, $b] = $this->biz(); if (!$b) out(['error' => 'Register your business first.'], 422); $d = body();
    if (!trim($d['name'] ?? '') || !is_numeric($d['price'] ?? null) || $d['price'] < 0 || !(int)($d['category_id'] ?? 0)) out(['error' => 'Product needs a name, category and valid price.'], 422);
    $v = [(int)$d['category_id'], trim($d['name']), $d['description'] ?? '', $d['price'], max(0, (int)($d['stock'] ?? 0)), empty($d['available']) ? 0 : 1];
    if (!empty($d['id'])) { $own = q('SELECT id FROM products WHERE id=? AND business_id=?', [$d['id'], $b['id']])->fetch() ?: out(['error' => 'Not your product'], 403); $id = $own['id'];
      q('UPDATE products SET category_id=?,name=?,description=?,price=?,stock=?,available=? WHERE id=?', [...$v, $id]); }
    else { q('INSERT INTO products(category_id,name,description,price,stock,available,business_id) VALUES(?,?,?,?,?,?,?)', [...$v, $b['id']]); $id = db()->lastInsertId(); }
    if (!empty($d['image'])) { q('DELETE FROM product_images WHERE product_id=?', [$id]); q('INSERT INTO product_images(product_id,path) VALUES(?,?)', [$id, $d['image']]); }
    out(['ok' => 1]);
  }
  function deleteProduct($id) { [$u, $b] = $this->biz(); q('DELETE FROM products WHERE id=? AND business_id=?', [$id, $b['id'] ?? 0]); out(['ok' => 1]); }
  function upload() { Auth::need('owner'); out(['url' => ImageUpload::store($_FILES['file'] ?? null)]); }
}