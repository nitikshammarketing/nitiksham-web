<?php
declare(strict_types=1);
/** Nitiksham standalone PHP backend, kept OUTSIDE the web-accessible document root. */

function nsm_config(): array {
    static $cfg = null;
    if ($cfg !== null) return $cfg;
    $file = __DIR__ . '/config.php';
    if (!is_file($file)) throw new RuntimeException('Studio backend is not configured yet.');
    $data = require $file;
    if (!is_array($data)) throw new RuntimeException('Invalid Studio configuration.');
    $cfg = $data;
    return $cfg;
}

function nsm_session_start(): void {
    if (session_status() === PHP_SESSION_ACTIVE) return;
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_httponly', '1');
    session_name('NSM_OWNER_SESSION');
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['SERVER_PORT'] ?? '') === '443');
    session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'secure' => $https, 'httponly' => true, 'samesite' => 'Strict']);
    session_start();
    if (!isset($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

function nsm_csrf(): string {
    nsm_session_start();
    return (string) $_SESSION['csrf'];
}

function nsm_csrf_valid(?string $submitted): bool {
    return is_string($submitted) && strlen($submitted) === 64 && hash_equals(nsm_csrf(), $submitted);
}

function nsm_is_owner(): bool {
    nsm_session_start();
    if (empty($_SESSION['owner']) || !isset($_SESSION['owner_at'])) return false;
    if ((time() - (int) $_SESSION['owner_at']) > 12 * 3600) {
        unset($_SESSION['owner'], $_SESSION['owner_at']);
        return false;
    }
    return true;
}

function nsm_require_owner(): void {
    if (!nsm_is_owner()) {
        header('Location: /login/', true, 302);
        exit;
    }
}

function nsm_db(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) return $pdo;
    $c = nsm_config();
    foreach (['db_host','db_name','db_user','db_pass'] as $field) {
        if (empty($c[$field])) throw new RuntimeException('Database configuration incomplete.');
    }
    $dsn = 'mysql:host=' . $c['db_host'] . ';dbname=' . $c['db_name'] . ';charset=utf8mb4';
    $pdo = new PDO($dsn, $c['db_user'], $c['db_pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    return $pdo;
}

function nsm_json($data, int $status = 200): never {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    echo json_encode($data, JSON_INVALID_UTF8_SUBSTITUTE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    exit;
}

function nsm_error(string $message, int $status = 400): never {
    nsm_json(['error' => $message], $status);
}

function nsm_require_csrf(): void {
    $header = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    $input  = $_POST['csrf'] ?? '';
    if (!nsm_csrf_valid(is_string($header) && $header !== '' ? $header : (is_string($input) ? $input : ''))) {
        nsm_error('Request verification failed. Refresh and retry.', 403);
    }
    if (!empty($_SERVER['HTTP_ORIGIN'])) {
        $originHost = parse_url($_SERVER['HTTP_ORIGIN'], PHP_URL_HOST);
        $requestHost = explode(':', (string) ($_SERVER['HTTP_HOST'] ?? ''))[0];
        if (!$originHost || strcasecmp($originHost, $requestHost) !== 0) nsm_error('Request origin rejected.', 403);
    }
}

function nsm_rate_limited(string $ip, bool $recordFailure = false): bool {
    // File-based cross-session login throttle. Directory is outside web root.
    $dir = __DIR__ . '/storage/rate-limits';
    if (!is_dir($dir) && !mkdir($dir, 0700, true) && !is_dir($dir)) return true;
    $file = $dir . '/' . hash('sha256', $ip) . '.json';
    $fp = fopen($file, 'c+');
    if ($fp === false || !flock($fp, LOCK_EX)) return true;
    $raw = stream_get_contents($fp);
    $state = json_decode($raw ?: '', true);
    $state = is_array($state) ? $state : ['count' => 0, 'since' => time()];
    if (time() - (int) $state['since'] >= 900) $state = ['count' => 0, 'since' => time()];
    if ($recordFailure) $state['count']++;
    rewind($fp);
    ftruncate($fp, 0);
    fwrite($fp, json_encode($state));
    fflush($fp);
    flock($fp, LOCK_UN);
    fclose($fp);
    return $state['count'] >= 8;
}
