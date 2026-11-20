<?php
// PATCH /api/manage/item.php?id=<record-id> — admin only, X-CSRF-Token.
// The .htaccess rewrite maps /api/manage/items/<id> here.
// JSON body may set published (bool), archived (bool) and/or title
// (non-empty, up to 120 characters). Returns the admin record shape.
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../json.php';
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../csrf.php';

nsm_require_method('PATCH');
nsm_require_admin();
nsm_csrf_check();

$id = isset($_GET['id']) ? (string) $_GET['id'] : '';
if ($id === '' && !empty($_SERVER['PATH_INFO'])) {
    $id = trim((string) $_SERVER['PATH_INFO'], '/');
}
if (!nsm_id_ok($id)) {
    nsm_fail('Invalid ID.', 400);
}
$row = nsm_get_record($id);
if ($row === null) {
    nsm_fail('Not found.', 404);
}
$data = nsm_input_json();
if (!is_array($data)) {
    nsm_fail('Invalid JSON.', 400);
}
$changes = array();
$params = array();
if (array_key_exists('published', $data) && is_bool($data['published'])) {
    $changes[] = 'published = ?';
    $params[] = $data['published'] ? 1 : 0;
}
if (array_key_exists('archived', $data) && is_bool($data['archived'])) {
    $changes[] = 'archived = ?';
    $params[] = $data['archived'] ? 1 : 0;
}
if (isset($data['title']) && is_string($data['title'])) {
    $rawTitle = trim($data['title']);
    if ($rawTitle === '' || mb_strlen($rawTitle) > 120) {
        nsm_fail('Enter a valid title.', 400);
    }
    $title = nsm_clean_text($rawTitle, 120);
    $changes[] = 'title = ?';
    $params[] = $title;
}
if ($changes) {
    $params[] = $id;
    $st = nsm_db()->prepare('UPDATE records SET ' . implode(', ', $changes) . ' WHERE id = ?');
    if ($st->execute($params) === false) {
        nsm_fail('Could not save changes.', 500);
    }
}
nsm_json(nsm_record_shape(nsm_get_record($id), true));
