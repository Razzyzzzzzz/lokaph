<?php
class Business {
  static function distSql() { return '6371*ACOS(LEAST(1,COS(RADIANS(:lat))*COS(RADIANS(l.lat))*COS(RADIANS(l.lng)-RADIANS(:lng))+SIN(RADIANS(:lat))*SIN(RADIANS(l.lat))))'; }
  static function search($f, &$total = null) {
    $has = is_numeric($f['lat'] ?? null) && is_numeric($f['lng'] ?? null); $p = []; $d = 'NULL';
    if ($has) { $d = self::distSql(); $p[':lat'] = (float)$f['lat']; $p[':lng'] = (float)$f['lng']; }
    $in = "SELECT b.id,b.name,b.description,b.phone,b.facebook,b.logo,b.cover,b.featured,b.verified,b.is_sample,b.created_at,c.id category_id,c.name category,
      l.address,l.city,l.province,l.lat,l.lng,$d distance,
      (SELECT ROUND(AVG(rating),1) FROM reviews WHERE business_id=b.id) rating,(SELECT COUNT(*) FROM reviews WHERE business_id=b.id) reviews,
      (SELECT COUNT(*) FROM products WHERE business_id=b.id AND status='approved') products,(SELECT MIN(price) FROM products WHERE business_id=b.id AND status='approved') min_price,
      EXISTS(SELECT 1 FROM business_hours h WHERE h.business_id=b.id AND h.day=WEEKDAY(NOW()) AND h.is_closed=0 AND CURTIME() BETWEEN h.opens AND h.closes) is_open
      FROM businesses b JOIN business_locations l ON l.business_id=b.id JOIN business_categories c ON c.id=b.category_id WHERE b.status='approved'";
    if (!empty($f['id'])) { $in .= ' AND b.id=:id'; $p[':id'] = (int)$f['id']; }
    $w = [];
    if (!empty($f['q'])) { $w[] = '(name LIKE :q OR category LIKE :q OR city LIKE :q OR province LIKE :q)'; $p[':q'] = '%' . $f['q'] . '%'; }
    if (!empty($f['category'])) { $w[] = 'category_id=:cat'; $p[':cat'] = (int)$f['category']; }
    if (!empty($f['city'])) { $w[] = '(city LIKE :city OR province LIKE :city)'; $p[':city'] = '%' . $f['city'] . '%'; }
    if (!empty($f['open'])) $w[] = 'is_open=1';
    if (!empty($f['max_price'])) { $w[] = 'min_price<=:mp'; $p[':mp'] = (float)$f['max_price']; }
    if ($has && !empty($f['radius'])) { $w[] = 'distance<=:rad'; $p[':rad'] = (float)$f['radius']; }
    $o = ['rating' => 'rating DESC', 'newest' => 'created_at DESC'][$f['sort'] ?? ''] ?? ($has ? 'distance ASC' : 'featured DESC, name');
    $where = $w ? 'WHERE ' . implode(' AND ', $w) : ''; $lim = '';
    if (!empty($f['limit'])) { // server-side pagination (limit is clamped to 50)
      $lim = ' LIMIT ' . max(1, min(50, (int)$f['limit'])) . ' OFFSET ' . max(0, (int)($f['offset'] ?? 0));
      $total = (int)q("SELECT COUNT(*) FROM ($in) t $where", $p)->fetchColumn();
    }
    $rows = q("SELECT * FROM ($in) t $where ORDER BY $o, id$lim", $p)->fetchAll();
    $total ??= count($rows);
    foreach ($rows as &$r) { $r['lat'] = (float)$r['lat']; $r['lng'] = (float)$r['lng']; $r['is_open'] = (bool)$r['is_open']; if ($r['distance'] !== null) $r['distance'] = round((float)$r['distance'], 2); }
    return $rows;
  }
}