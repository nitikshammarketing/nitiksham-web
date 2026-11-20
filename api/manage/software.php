<?php
// POST /api/manage/software.php — admin only, JSON body, X-CSRF-Token.
// Creates a software record, or updates one when 'id' names an existing
// software record. liveUrl/demoUrl must be HTTPS URLs with no username
// or password. Returns the admin record shape with status 201.
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../json.php';
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../csrf.php';

nsm_require_method('POST');
$admin = nsm_require_admin();
nsm_csrf_check();

$data = nsm_input_json();
if (!is_array($data)) {
    nsm_fail('Invalid JSON.', 400);
}
$rawTitle = trim((string) ($data['title'] ?? ''));
if ($rawTitle === '' || mb_strlen($rawTitle) > 120) {
    nsm_fail('Enter a software name (up to 120 characters).', 400);
}
$title = nsm_clean_text($rawTitle, 120);
$id = (string) ($data['id'] ?? '');
if ($id === '') {
    $id = nsm_uuid();
}
if (!nsm_id_ok($id)) {
    nsm_fail('Invalid software ID.', 400);
}
$existing = nsm_get_record($id);
if ($existing !== null && $existing['kind'] !== 'software') {
    nsm_fail('Invalid software record.', 400);
}
$live = nsm_https_url($data['liveUrl'] ?? '');
$demo = nsm_https_url($data['demoUrl'] ?? '');
if ($live === false || $demo === false) {
    nsm_fail('Use an HTTPS URL without a username or password.', 400);
}
$description = nsm_clean_multiline($data['description'] ?? '', 1000);
$published = !empty($data['published']) ? 1 : 0;

if ($existing !== null) {
    $st = nsm_db()->prepare(
        'UPDATE records SET title = ?, description = ?, live_url = ?, demo_url = ?, published = ? WHERE id = ?'
    );
    $ok = $st->execute(array($title, $description, $live, $demo, $published, $id));
} else {
    $st = nsm_db()->prepare(
        'INSERT INTO records (id, kind, title, description, mime, published, archived,' .
        ' live_url, demo_url, owner_id, created_at) VALUES (?, \'software\', ?, ?, \'\', ?, 0, ?, ?, ?, ?)'
    );
    $ok = $st->execute(array(
        $id, $title, $description, $published, $live, $demo, $admin['id'], nsm_now(),
    ));
}
if ($ok === false) {
    nsm_fail('Could not save software.', 500);
}
nsm_json(nsm_record_shape(nsm_get_record($id), true), 201);
