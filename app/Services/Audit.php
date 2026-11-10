<?php
class Audit {
  /** Records who did what. Never throws: auditing must not break the request. */
  static function log(string $action, ?string $type = null, ?int $id = null, ?string $details = null, ?int $uid = null): void {
    try {
      q('INSERT INTO audit_logs(user_id,action,entity_type,entity_id,details,ip) VALUES(?,?,?,?,?,?)',
        [$uid ?? (user()['id'] ?? null), $action, $type, $id, $details !== null ? mb_substr($details, 0, 500) : null, $_SERVER['REMOTE_ADDR'] ?? null]);
    } catch (Throwable $e) { error_log($e); }
  }
}