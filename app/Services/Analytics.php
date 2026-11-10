<?php
class Analytics {
  const COLS = ['profile_views', 'product_views', 'direction_clicks', 'call_clicks'];

  /** Counts one event for today. Visits by the owner or an admin are ignored. */
  static function hit(int $businessId, string $col): void {
    if (!in_array($col, self::COLS, true)) return;
    try {
      $u = user();
      if ($u && ($u['role'] === 'admin' || q('SELECT 1 FROM businesses WHERE id=? AND owner_id=?', [$businessId, $u['id']])->fetch())) return;
      q("INSERT INTO business_daily_stats(business_id,day,$col) VALUES(?,CURDATE(),1) ON DUPLICATE KEY UPDATE $col=$col+1", [$businessId]);
    } catch (Throwable $e) { error_log($e); }
  }

  private static function window(int $days): array {
    $today = new DateTimeImmutable('today', new DateTimeZone('Asia/Manila')); $from = $today->modify('-' . ($days - 1) . ' days');
    return [$from, $today, $from->modify("-$days days"), $from->modify('-1 day')];
  }
  static function days($v): int { return in_array((int)$v, [7, 30, 90], true) ? (int)$v : 30; }

  static function business(int $id, int $days): array {
    [$from, $to, $pFrom, $pTo] = self::window($days); $f = $from->format('Y-m-d'); $t = $to->format('Y-m-d');
    $totals = function ($a, $b) use ($id) {
      $r = q('SELECT COALESCE(SUM(profile_views),0) profile_views,COALESCE(SUM(product_views),0) product_views,COALESCE(SUM(direction_clicks),0) direction_clicks,COALESCE(SUM(call_clicks),0) call_clicks FROM business_daily_stats WHERE business_id=? AND day BETWEEN ? AND ?', [$id, $a, $b])->fetch();
      $r['inquiries'] = q('SELECT COUNT(*) FROM inquiries WHERE business_id=? AND DATE(created_at) BETWEEN ? AND ?', [$id, $a, $b])->fetchColumn();
      $r['favorites'] = q('SELECT COUNT(*) FROM favorites WHERE business_id=? AND DATE(created_at) BETWEEN ? AND ?', [$id, $a, $b])->fetchColumn();
      return array_map('intval', $r);
    };
    $daily = q('SELECT day,profile_views,product_views,direction_clicks,call_clicks FROM business_daily_stats WHERE business_id=? AND day BETWEEN ? AND ?', [$id, $f, $t])->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);
    $inq = q('SELECT DATE(created_at) d,COUNT(*) c FROM inquiries WHERE business_id=? AND DATE(created_at) BETWEEN ? AND ? GROUP BY d', [$id, $f, $t])->fetchAll(PDO::FETCH_KEY_PAIR);
    $series = [];
    for ($i = 0; $i < $days; $i++) { $d = $from->modify("+$i days")->format('Y-m-d'); $r = $daily[$d] ?? [];
      $series[] = ['day' => $d, 'profile_views' => (int)($r['profile_views'] ?? 0), 'product_views' => (int)($r['product_views'] ?? 0), 'direction_clicks' => (int)($r['direction_clicks'] ?? 0), 'call_clicks' => (int)($r['call_clicks'] ?? 0), 'inquiries' => (int)($inq[$d] ?? 0)]; }
    return ['days' => $days, 'from' => $f, 'to' => $t, 'totals' => $totals($f, $t), 'previous' => $totals($pFrom->format('Y-m-d'), $pTo->format('Y-m-d')), 'series' => $series,
      'top_products' => q("SELECT name,views,price FROM products WHERE business_id=? AND status='approved' ORDER BY views DESC LIMIT 5", [$id])->fetchAll()];
  }

  static function platform(int $days): array {
    [$from, $to, $pFrom, $pTo] = self::window($days); $f = $from->format('Y-m-d'); $t = $to->format('Y-m-d');
    $totals = function ($a, $b) {
      $n = fn($sql) => (int)q($sql, [$a, $b])->fetchColumn();
      return ['new_users' => $n('SELECT COUNT(*) FROM users WHERE DATE(created_at) BETWEEN ? AND ?'), 'new_businesses' => $n('SELECT COUNT(*) FROM businesses WHERE DATE(created_at) BETWEEN ? AND ?'),
        'inquiries' => $n('SELECT COUNT(*) FROM inquiries WHERE DATE(created_at) BETWEEN ? AND ?'), 'profile_views' => $n('SELECT COALESCE(SUM(profile_views),0) FROM business_daily_stats WHERE day BETWEEN ? AND ?'),
        'product_views' => $n('SELECT COALESCE(SUM(product_views),0) FROM business_daily_stats WHERE day BETWEEN ? AND ?'), 'direction_clicks' => $n('SELECT COALESCE(SUM(direction_clicks),0) FROM business_daily_stats WHERE day BETWEEN ? AND ?')];
    };
    $by = fn($sql) => q($sql, [$f, $t])->fetchAll(PDO::FETCH_KEY_PAIR);
    $u = $by('SELECT DATE(created_at) d,COUNT(*) FROM users WHERE DATE(created_at) BETWEEN ? AND ? GROUP BY d');
    $b = $by('SELECT DATE(created_at) d,COUNT(*) FROM businesses WHERE DATE(created_at) BETWEEN ? AND ? GROUP BY d');
    $i = $by('SELECT DATE(created_at) d,COUNT(*) FROM inquiries WHERE DATE(created_at) BETWEEN ? AND ? GROUP BY d');
    $v = $by('SELECT day,SUM(profile_views) FROM business_daily_stats WHERE day BETWEEN ? AND ? GROUP BY day');
    $series = [];
    for ($k = 0; $k < $days; $k++) { $d = $from->modify("+$k days")->format('Y-m-d');
      $series[] = ['day' => $d, 'new_users' => (int)($u[$d] ?? 0), 'new_businesses' => (int)($b[$d] ?? 0), 'inquiries' => (int)($i[$d] ?? 0), 'profile_views' => (int)($v[$d] ?? 0)]; }
    return ['days' => $days, 'from' => $f, 'to' => $t, 'totals' => $totals($f, $t), 'previous' => $totals($pFrom->format('Y-m-d'), $pTo->format('Y-m-d')), 'series' => $series,
      'top_businesses' => q('SELECT b.id,b.name,SUM(s.profile_views) views,SUM(s.direction_clicks) directions FROM business_daily_stats s JOIN businesses b ON b.id=s.business_id WHERE s.day BETWEEN ? AND ? GROUP BY b.id,b.name ORDER BY views DESC LIMIT 5', [$f, $t])->fetchAll(),
      'categories' => q("SELECT c.name,COUNT(*) n FROM businesses b JOIN business_categories c ON c.id=b.category_id WHERE b.status='approved' GROUP BY c.id,c.name ORDER BY n DESC")->fetchAll()];
  }

  /** Sends rows as a downloadable CSV (UTF-8 BOM for Excel; formula characters are neutralised). */
  static function csv(string $filename, array $headers, array $rows): void {
    header('Content-Type: text/csv; charset=utf-8'); header('Content-Disposition: attachment; filename="' . $filename . '"'); header('Cache-Control: private, no-store');
    $out = fopen('php://output', 'w'); fwrite($out, "\xEF\xBB\xBF"); fputcsv($out, $headers);
    foreach ($rows as $r) fputcsv($out, array_map(fn($v) => is_string($v) && preg_match('/^[=+\-@]/', $v) ? "'" . $v : $v, array_values($r)));
    fclose($out); exit;
  }
}