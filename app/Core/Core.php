<?php
$cfg = require __DIR__ . '/../../config/config.php';
if (PHP_SAPI !== 'cli') session_start();
spl_autoload_register(function ($c) { foreach (['Controllers', 'Models', 'Middleware', 'Services'] as $d) { $f = __DIR__ . "/../$d/$c.php"; if (is_file($f)) return require $f; } });
function db(): PDO { static $p; global $cfg;
  if (!$p) { $p = new PDO("mysql:host={$cfg['host']};dbname={$cfg['db']};charset=utf8mb4", $cfg['user'], $cfg['pass'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => true]); $p->exec("SET time_zone='+08:00'"); }
  return $p; }
function q($sql, $a = []) { $s = db()->prepare($sql); $s->execute($a); return $s; }
function out($d, $c = 200) { http_response_code($c); header('Content-Type: application/json'); echo json_encode($d); exit; }
function body() { static $b; return $b ??= (json_decode(file_get_contents('php://input'), true) ?: $_POST); }
function user() { return $_SESSION['user'] ?? null; }
function csrf() { if (in_array($_SERVER['REQUEST_METHOD'], ['POST', 'PUT', 'DELETE']) && !hash_equals($_SESSION['csrf'] ?? '', $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '')) out(['error' => 'Session expired. Refresh the page.'], 419); }
function notify($uid, $msg) { q('INSERT INTO notifications(user_id,message) VALUES(?,?)', [$uid, $msg]); }
class Router {
  static $r = [];
  static function add($m, $p, $h) { self::$r[] = [$m, '#^' . preg_replace('#\{\w+\}#', '(\d+)', $p) . '$#', $h]; }
  static function run($m, $path) { foreach (self::$r as [$mm, $re, $h]) if ($mm === $m && preg_match($re, $path, $x)) { array_shift($x); [$c, $f] = explode('@', $h); (new $c)->$f(...$x); return; } out(['error' => 'Not found'], 404); }
}