<?php
// GET /api/gallery.php[?kind=design|newsletter]
// Public. Published, unarchived design/newsletter records, newest first.
// No file bytes, no admin fields — mirrors the WordPress plugin.
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/json.php';

nsm_require_method('GET');
$kind = isset($_GET['kind']) ? (string) $_GET['kind'] : '';
$rows = nsm_list_records('gallery', $kind);
$out = array();
foreach ($rows as $row) {
    $out[] = nsm_record_shape($row, false);
}
nsm_json($out);
