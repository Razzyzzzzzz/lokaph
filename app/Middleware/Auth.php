<?php
class Auth {
  static function need($role = null) {
    $u = user(); if (!$u) out(['error' => 'Please log in first'], 401);
    if ($role === 'admin' && $u['role'] !== 'admin') out(['error' => 'Not allowed'], 403);
    if ($role === 'owner' && !in_array($u['role'], ['owner', 'admin'])) out(['error' => 'Not allowed'], 403);
    return $u;
  }
}
