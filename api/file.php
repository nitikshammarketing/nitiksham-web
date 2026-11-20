<?php
// GET /api/file.php?id=<record-id>
// Serves a stored gallery/newsletter file from private disk storage.
//
// Access rules (mirroring the WordPress plugin):
// - archived records: never served (404, even for admins)
// - unpublished records: admins only, everyone else gets 404
// - published, unarchived records: public
// - software records have no file: 404
// PDFs are served as attachments, images inline. All responses carry
// nosniff + a sandboxed content security policy.
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/json.php';
require_once __DIR__ . '/auth.php';

nsm_require_method('GET');
$id = isset($_GET['id']) ? (string) $_GET['id'] : '';
if (!nsm_id_ok($id)) {
    http_response_code(404);
    exit;
}
$row = nsm_get_record($id);
if ($row === null || $row['kind'] === 'software' || (int) $row['archived'] === 1) {
    http_response_code(404);
    exit;
}
if ((int) $row['published'] !== 1 && nsm_current_admin() === null) {
    http_response_code(404);
    exit;
}
$mime = (string) $row['mime'];
$ext = nsm_mime_ext($mime);
$fileName = (string) $row['file_name'];
if ($ext === null || $fileName === ''
    || !preg_match('/^[a-z0-9-]+\.(jpg|png|webp|pdf)$/D', $fileName)) {
    http_response_code(404);
    exit;
}
$path = rtrim(nsm_config('storage_path'), '/') . '/' . $fileName;
if (!is_file($path)) {
    http_response_code(404);
    exit;
}
header('Content-Type: ' . $mime);
header('X-Content-Type-Options: nosniff');
header("Content-Security-Policy: default-src 'none'; sandbox");
header('Content-Disposition: ' . ($mime === 'application/pdf' ? 'attachment' : 'inline')
    . '; filename="' . $id . '.' . $ext . '"');
header('Content-Length: ' . filesize($path));
header('Cache-Control: no-store, must-revalidate');
header('Pragma: no-cache');
readfile($path);
exit;
