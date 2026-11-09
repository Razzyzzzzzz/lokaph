<?php $v = fn($f) => @filemtime(__DIR__ . '/../../public' . $f) ?: 1; ?>
<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>LokalPH – Find local businesses near you</title>
<link rel="stylesheet" href="/assets/css/app.css?v=<?= $v('/assets/css/app.css') ?>">
</head><body><div id="app"></div>
<script type="module" src="/assets/js/app.js?v=<?= $v('/assets/js/app.js') ?>"></script>
</body></html>
