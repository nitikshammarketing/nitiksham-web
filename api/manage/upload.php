<?php
// POST /api/manage/upload.php — admin only, multipart form, X-CSRF-Token.
// Fields: kind (design|newsletter), title (<=120), description (<=1000),
// published (optional, truthy = publish now), file (<=15 MiB).
// Only valid JPG/PNG/WebP/PDF files pass the magic-byte check.
// The file is stored on disk under STORAGE_PATH as <uuid>.<ext> and a
// record is created. Returns the admin record shape with status 201.
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../json.php';
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../csrf.php';

nsm_require_method('POST');
$admin = nsm_require_admin();
nsm_csrf_check();

$kind = isset($_POST['kind']) ? (string) $_POST['kind'] : '';
$rawTitle = trim((string) ($_POST['title'] ?? ''));
if ($rawTitle === '' || mb_strlen($rawTitle) > 120) {
    nsm_fail('Enter a title and valid category.', 400);
}
$title = nsm_clean_text($rawTitle, 120);
$description = nsm_clean_multiline($_POST['description'] ?? '', 1000);
if (!in_array($kind, array('design', 'newsletter'), true)) {
    nsm_fail('Enter a title and valid category.', 400);
}

$file = isset($_FILES['file']) ? $_FILES['file'] : null;
if ($file === null || !isset($file['tmp_name']) || is_array($file['tmp_name'])) {
    nsm_fail('Choose a file up to 15 MB.', 400);
}
$size = (int) ($file['size'] ?? 0);
if (!empty($file['error']) || $size < 1 || $size > nsm_config('max_file_size')) {
    nsm_fail('Choose a file up to 15 MB.', 400);
}
$tmp = $file['tmp_name'];
if (!is_uploaded_file($tmp)) {
    nsm_fail('Invalid uploaded file.', 400);
}
$mime = nsm_detect_mime($tmp);
$ext = $mime !== null ? nsm_mime_ext($mime) : null;
if ($mime === null || $ext === null) {
    nsm_fail('Only valid JPG, PNG, WebP and PDF files are supported.', 400);
}

$id = nsm_uuid();
$fileName = $id . '.' . $ext;
$storage = rtrim(nsm_config('storage_path'), '/');
if (!is_dir($storage) && !mkdir($storage, 0750, true)) {
    nsm_fail('Upload failed.', 500);
}
if (!move_uploaded_file($tmp, $storage . '/' . $fileName)) {
    nsm_fail('Upload failed.', 500);
}

$published = isset($_POST['published'])
    && in_array(strtolower((string) $_POST['published']), array('1', 'true', 'yes', 'on'), true)
    ? 1 : 0;
try {
    $st = nsm_db()->prepare(
        'INSERT INTO records (id, kind, title, description, mime, published, archived,' .
        ' owner_id, file_name, file_size, created_at) VALUES (?, ?, ?, ?, ?, ?, 0, ?, ?, ?, ?)'
    );
    $st->execute(array(
        $id, $kind, $title, $description, $mime, $published,
        $admin['id'], $fileName, $size, nsm_now(),
    ));
} catch (PDOException $e) {
    @unlink($storage . '/' . $fileName);
    error_log('Nitiksham upload: database insert failed: ' . $e->getMessage());
    nsm_fail('Could not save the file.', 500);
}
nsm_json(nsm_record_shape(nsm_get_record($id), true), 201);
