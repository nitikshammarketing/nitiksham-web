<?php
// Nitiksham site API — CSRF protection.
//
// The token lives in the session and is returned by GET /api/session.php.
// Every POST/PATCH/DELETE request must send it as the X-CSRF-Token header
// (a form field named 'csrf' is accepted as a fallback for plain forms).
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/json.php';

function nsm_csrf_token() {
    nsm_session_start();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Ends the request with 403 when the token is missing or wrong.
function nsm_csrf_check() {
    nsm_session_start();
    $sent = nsm_header('X-CSRF-Token');
    if ($sent === '') {
        $sent = isset($_POST['csrf']) ? (string) $_POST['csrf'] : '';
    }
    $expected = isset($_SESSION['csrf_token']) ? (string) $_SESSION['csrf_token'] : '';
    if ($expected === '' || !hash_equals($expected, $sent)) {
        nsm_fail('Invalid CSRF token.', 403);
    }
}
