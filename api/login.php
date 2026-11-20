<?php
// POST /api/login.php {email, password} with X-CSRF-Token header.
// Fetch the token first from GET /api/session.php.
// 200 {ok, email, csrf} on success; 401 on bad credentials;
// 429 when throttled (5 failures / 15 min per IP + email).
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/json.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/csrf.php';

nsm_require_method('POST');
nsm_session_start();
nsm_csrf_check();
$data = nsm_input_json();
if (!is_array($data)) {
    nsm_fail('Invalid request.', 400);
}
$email = trim((string) ($data['email'] ?? ''));
$password = (string) ($data['password'] ?? '');
if ($email === '' || $password === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    nsm_fail('Enter a valid email and password.', 400);
}
$result = nsm_attempt_login($email, $password);
if (!$result['ok']) {
    nsm_fail($result['error'], $result['status']);
}
nsm_json(array(
    'ok' => true,
    'email' => $result['admin']['email'],
    'csrf' => nsm_csrf_token(),
));
