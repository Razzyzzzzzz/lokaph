<?php
/** Stores verification documents OUTSIDE the public folder. They are only served to admins through the API. */
class DocumentUpload {
  const MAX = 5 * 1024 * 1024;
  const DIR = __DIR__ . '/../../storage/verification';
  static function store(?array $f): string {
    if (!$f || $f['error'] !== UPLOAD_ERR_OK || $f['size'] > self::MAX) out(['error' => 'Upload a PDF, JPG, PNG or WebP file under 5 MB.'], 422);
    $ext = ['application/pdf' => 'pdf', 'image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][(new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name'])] ?? null;
    if (!$ext) out(['error' => 'Only PDF, JPG, PNG or WebP files are allowed.'], 422);
    if (!is_dir(self::DIR)) mkdir(self::DIR, 0775, true);
    $name = bin2hex(random_bytes(16)) . ".$ext";
    move_uploaded_file($f['tmp_name'], self::DIR . '/' . $name);
    return $name;
  }
}