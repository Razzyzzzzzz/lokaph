<?php
class Product {
  static function search($f) {
    $has = is_numeric($f['lat'] ?? null) && is_numeric($f['lng'] ?? null); $p = []; $d = 'NULL';
    if ($has) { $d = Business::distSql(); $p[':lat'] = (float)$f['lat']; $p[':lng'] = (float)$f['lng']; }
    $w = ["b.status='approved'"];
    if (!empty($f['id'])) { $w[] = 'p.id=:id'; $p[':id'] = (int)$f['id']; }
    if (!empty($f['business_id'])) { $w[] = 'p.business_id=:bid'; $p[':bid'] = (int)$f['business_id']; }
    if (!empty($f['category'])) { $w[] = 'p.category_id=:cat'; $p[':cat'] = (int)$f['category']; }
    if (!empty($f['q'])) { $w[] = '(p.name LIKE :q OR b.name LIKE :q OR p.description LIKE :q)'; $p[':q'] = '%' . $f['q'] . '%'; }
    $o = ['price_asc' => 'p.price ASC', 'price_desc' => 'p.price DESC', 'trending' => 'p.views DESC', 'nearest' => 'distance ASC'][$f['sort'] ?? ''] ?? 'p.id DESC';
    if ($o === 'distance ASC' && !$has) $o = 'p.id DESC';
    $rows = q("SELECT p.id,p.business_id,p.name,p.description,p.price,p.stock,p.available,p.views,pc.id category_id,pc.name category,b.name business,b.phone,l.city,l.lat,l.lng,$d distance,
      (SELECT path FROM product_images WHERE product_id=p.id LIMIT 1) image
      FROM products p JOIN businesses b ON b.id=p.business_id JOIN business_locations l ON l.business_id=b.id JOIN product_categories pc ON pc.id=p.category_id
      WHERE " . implode(' AND ', $w) . " ORDER BY $o LIMIT 100", $p)->fetchAll();
    foreach ($rows as &$r) { $r['price'] = (float)$r['price']; $r['available'] = (bool)$r['available']; if ($r['distance'] !== null) $r['distance'] = round((float)$r['distance'], 2); }
    return $rows;
  }
}
