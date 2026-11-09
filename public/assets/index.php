<?php
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (PHP_SAPI === 'cli-server' && is_file(__DIR__ . $path)) return false;
require __DIR__ . '/../app/Core/Core.php';
if (!str_starts_with($path, '/api/')) { require __DIR__ . '/../app/Views/app.php'; exit; }
try { csrf(); require __DIR__ . '/../routes/api.php'; Router::run($_SERVER['REQUEST_METHOD'], substr($path, 4)); }
catch (Throwable $e) { error_log($e); out(['error' => 'Server error'], 500); }
