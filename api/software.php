<?php
// GET /api/software.php
// Public. Published, unarchived software records, newest first.
// Only id/title/description — mirrors the WordPress plugin.
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/json.php';

nsm_require_method('GET');
$rows = nsm_list_records('software');
$out = array();
foreach ($rows as $row) {
    $r = nsm_record_shape($row, false);
    $out[] = array(
        'id' => $r['id'],
        'title' => $r['title'],
        'description' => $r['description'],
    );
}
nsm_json($out);
