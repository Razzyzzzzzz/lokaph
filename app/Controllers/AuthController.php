<?php
class AuthController {
  function me() { $_SESSION['csrf'] ??= bin2hex(random_bytes(16)); out(['user' => user(), 'csrf' => $_SESSION['csrf']]); }
  function register() {
    $b = body(); $n = trim($b['name'] ?? ''); $e = strtolower(trim($b['email'] ?? '')); $p = $b['password'] ?? '';
    if (!$n || !filter_var($e, FILTER_VALIDATE_EMAIL) || strlen($p) < 8) out(['error' => 'Enter your name, a valid email, and a password of at least 8 characters.'], 422);
    if (q('SELECT 1 FROM users WHERE email=?', [$e])->fetch()) out(['error' => 'That email is already registered.'], 409);
    q('INSERT INTO users(role_id,name,email,password) VALUES(?,?,?,?)', [($b['role'] ?? '') === 'owner' ? 2 : 1, $n, $e, password_hash($p, PASSWORD_DEFAULT)]);
    $this->start(db()->lastInsertId());
  }
  function login() {
    $b = body(); $u = q('SELECT * FROM users WHERE email=?', [strtolower(trim($b['email'] ?? ''))])->fetch();
    if (!$u || !$u['active'] || !password_verify($b['password'] ?? '', $u['password'])) out(['error' => 'Wrong email or password.'], 401);
    $this->start($u['id']);
  }
  private function start($id) {
    session_regenerate_id(true);
    $u = q('SELECT u.id,u.name,u.email,r.name role FROM users u JOIN roles r ON r.id=u.role_id WHERE u.id=?', [$id])->fetch();
    $_SESSION['user'] = $u; $_SESSION['csrf'] = bin2hex(random_bytes(16)); out(['user' => $u, 'csrf' => $_SESSION['csrf']]);
  }
  function logout() { session_destroy(); out(['ok' => 1]); }
}
