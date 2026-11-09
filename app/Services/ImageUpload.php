<?php
class ImageUpload {
  const MAX = 2 * 1024 * 1024;
  /** Validates and stores an uploaded image, returns its public URL. */
  static function store(?array $f): string {
    if (!$f || $f['error'] !== UPLOAD_ERR_OK || $f['size'] > self::MAX) out(['error' => 'Upload a JPG, PNG or WebP image under 2 MB.'], 422);
    $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][(new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name'])] ?? null;
    if (!$ext || !@getimagesize($f['tmp_name'])) out(['error' => 'Only JPG, PNG or WebP images are allowed.'], 422);
    $name = bin2hex(random_bytes(10)) . ".$ext";
    move_uploaded_file($f['tmp_name'], __DIR__ . '/../../public/uploads/' . $name);
    return '/uploads/' . $name;
  }
}
