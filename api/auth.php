<?php
// Nitiksham site API — session authentication.
//
// PHP sessions: strict mode, httponly cookies, SameSite=Lax, Secure flag
// whenever the request is over HTTPS. The session id is regenerated on
// login. Login attempts are throttled: 5 failures per 15 minutes per
// IP + email, tracked in the login_attempts table.
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/json.php';

const NSM_LOGIN_MAX_ATTEMPTS = 5;
const NSM_LOGIN_WINDOW_MINUTES = 15;

function nsm_session_start() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_cookies', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.cookie_secure', nsm_is_https() ? '1' : '0');
    session_start();
}

function nsm_current_admin() {
    nsm_session_start();
    $id = isset($_SESSION['admin_id']) ? (string) $_SESSION['admin_id'] : '';
    if ($id === '') {
        return null;
    }
    $st = nsm_db()->prepare('SELECT id, email, created_at FROM admins WHERE id = ?');
    $st->execute(array($id));
    $admin = $st->fetch();
    return $admin ?: null;
}

// Returns the signed-in admin row, or ends the request with 401.
function nsm_require_admin() {
    $admin = nsm_current_admin();
    if ($admin === null) {
        nsm_fail('Sign in required.', 401);
    }
    return $admin;
}

function nsm_login_throttled($ip, $email) {
    $cutoff = gmdate('Y-m-d H:i:s', time() - NSM_LOGIN_WINDOW_MINUTES * 60);
    $st = nsm_db()->prepare(
        'SELECT COUNT(*) FROM login_attempts WHERE ip = ? AND email = ? AND attempted_at >= ?'
    );
    $st->execute(array($ip, $email, $cutoff));
    return (int) $st->fetchColumn() >= NSM_LOGIN_MAX_ATTEMPTS;
}

function nsm_login_record_attempt($ip, $email) {
    $st = nsm_db()->prepare(
        'INSERT INTO login_attempts (ip, email, attempted_at) VALUES (?, ?, ?)'
    );
    $st->execute(array($ip, $email, nsm_now()));
}

function nsm_login_clear_attempts($ip, $email) {
    $st = nsm_db()->prepare('DELETE FROM login_attempts WHERE ip = ? AND email = ?');
    $st->execute(array($ip, $email));
}

// Returns array('ok' => bool, 'status' => int, 'error' => string,
// 'admin' => row) and, on success, establishes the session.
function nsm_attempt_login($email, $password) {
    $email = strtolower(trim((string) $email));
    $ip = nsm_client_ip();
    if (nsm_login_throttled($ip, $email)) {
        return array(
            'ok' => false, 'status' => 429,
            'error' => 'Too many sign-in attempts. Please try again later.',
        );
    }
    $st = nsm_db()->prepare('SELECT id, email, password_hash FROM admins WHERE email = ?');
    $st->execute(array($email));
    $admin = $st->fetch();
    if (!$admin || !password_verify((string) $password, $admin['password_hash'])) {
        nsm_login_record_attempt($ip, $email);
        return array(
            'ok' => false, 'status' => 401,
            'error' => 'Invalid email or password.',
        );
    }
    nsm_login_clear_attempts($ip, $email);
    nsm_session_start();
    session_regenerate_id(true);
    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['admin_email'] = $admin['email'];
    return array('ok' => true, 'status' => 200, 'admin' => $admin);
}

function nsm_logout() {
    nsm_session_start();
    $_SESSION = array();
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}
