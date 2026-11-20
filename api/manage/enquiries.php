<?php
// Enquiries admin API (new — the Studio SPA needs this; the WordPress
// plugin had no enquiries endpoint).
//
// GET /api/manage/enquiries.php — admin only. All enquiries, newest first.
// DELETE /api/manage/enquiries.php?id=<enquiry-id> — admin only, with
// X-CSRF-Token header. Deletes one enquiry. Returns {ok: true}.
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../json.php';
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../csrf.php';

$method = $_SERVER['REQUEST_METHOD'] ?? '';
if ($method === 'GET') {
    nsm_require_admin();
    $st = nsm_db()->query(
        'SELECT id, name, email, company, phone, service, message, ip, created_at' .
        ' FROM enquiries ORDER BY created_at DESC'
    );
    $out = array();
    foreach ($st->fetchAll() as $row) {
        $out[] = array(
            'id' => (string) $row['id'],
            'name' => (string) $row['name'],
            'email' => (string) $row['email'],
            'company' => (string) $row['company'],
            'phone' => (string) $row['phone'],
            'service' => (string) $row['service'],
            'message' => (string) $row['message'],
            'createdAt' => nsm_iso($row['created_at']),
        );
    }
    nsm_json($out);
}

if ($method === 'DELETE') {
    nsm_require_admin();
    nsm_csrf_check();
    $id = isset($_GET['id']) ? (string) $_GET['id'] : '';
    if (!nsm_id_ok($id)) {
        nsm_fail('Invalid ID.', 400);
    }
    $st = nsm_db()->prepare('DELETE FROM enquiries WHERE id = ?');
    $st->execute(array($id));
    if ($st->rowCount() === 0) {
        nsm_fail('Not found.', 404);
    }
    nsm_json(array('ok' => true));
}

header('Allow: GET, DELETE');
nsm_fail('Method not allowed.', 405);
