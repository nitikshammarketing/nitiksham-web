<?php
// GET /api/manage/items.php — admin only.
// All records including archived, newest first, with admin fields
// (archived + liveUrl/demoUrl for software).
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../json.php';
require_once __DIR__ . '/../auth.php';

nsm_require_method('GET');
nsm_require_admin();
$rows = nsm_list_records('all');
$out = array();
foreach ($rows as $row) {
    $out[] = nsm_record_shape($row, true);
}
nsm_json($out);
