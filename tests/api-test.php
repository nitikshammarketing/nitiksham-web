<?php
// CLI test harness for the Nitiksham PHP API.
//
// Spins up PHP's built-in server against a temp SQLite database and temp
// file storage, then exercises the real HTTP endpoints end to end
// (install, auth, CSRF, gallery/software filters, upload validation,
// file access rules, PATCH, software create/update, contact + honeypot +
// rate limit, enquiries list/delete, login throttle).
//
// Usage: php tests/api-test.php
// Set NITIKSHAM_TEST_KEEP=1 to keep the temp dir for inspection.
//
// All credentials here are generated randomly at runtime for the throwaway
// temp database; nothing real is ever written down.

$root = dirname(__DIR__);
$tmp = sys_get_temp_dir() . '/nitiksham-api-test-' . getmypid();
$dbFile = $tmp . '/test.db';
$storage = $tmp . '/storage';
$mailLog = $tmp . '/mail.log';
$installToken = bin2hex(random_bytes(16));
$adminEmail = 'owner@example.test';
$adminPass = 'test-' . bin2hex(random_bytes(12)); // temp DB only, random
$keep = getenv('NITIKSHAM_TEST_KEEP') === '1';

$passed = 0;
$failed = 0;
function check($name, $cond, $detail = '') {
    global $passed, $failed;
    if ($cond) {
        $passed++;
        echo "PASS $name\n";
    } else {
        $failed++;
        echo 'FAIL ' . $name . ($detail !== '' ? ' — ' . $detail : '') . "\n";
    }
}

function rrmdir($dir) {
    if (!is_dir($dir)) {
        return;
    }
    foreach (scandir($dir) as $entry) {
        if ($entry === '.' || $entry === '..') {
            continue;
        }
        $path = $dir . '/' . $entry;
        if (is_dir($path)) {
            rrmdir($path);
        } else {
            @unlink($path);
        }
    }
    @rmdir($dir);
}

if (!mkdir($tmp, 0770, true)) {
    echo "FAIL could not create temp dir $tmp\n";
    exit(1);
}

// ---- HTTP client (manual cookie jars, no curl dependency) ----

function http_req($base, $method, $path, &$jar, $opts = array()) {
    $headers = array();
    if (!empty($jar)) {
        $pairs = array();
        foreach ($jar as $k => $v) {
            $pairs[] = $k . '=' . $v;
        }
        $headers[] = 'Cookie: ' . implode('; ', $pairs);
    }
    if (!empty($opts['headers'])) {
        foreach ($opts['headers'] as $h) {
            $headers[] = $h;
        }
    }
    $ctx = stream_context_create(array('http' => array(
        'method' => $method,
        'header' => implode("\r\n", $headers),
        'content' => $opts['body'] ?? '',
        'ignore_errors' => true,
        'timeout' => 15,
    )));
    $body = @file_get_contents($base . $path, false, $ctx);
    $status = 0;
    $respHeaders = array();
    if (isset($http_response_header) && is_array($http_response_header)) {
        foreach ($http_response_header as $line) {
            if (preg_match('#^HTTP/\S+\s+(\d+)#', $line, $m)) {
                $status = (int) $m[1];
            } elseif (strpos($line, ':') !== false) {
                list($k, $v) = explode(':', $line, 2);
                $k = strtolower(trim($k));
                $v = trim($v);
                $respHeaders[$k][] = $v;
                if ($k === 'set-cookie' && preg_match('/^([^=;]+)=([^;]*)/', $v, $cm)) {
                    if ($cm[2] === '') {
                        unset($jar[$cm[1]]);
                    } else {
                        $jar[$cm[1]] = $cm[2];
                    }
                }
            }
        }
    }
    $json = null;
    if (is_string($body) && $body !== '') {
        $decoded = json_decode($body, true);
        if (is_array($decoded)) {
            $json = $decoded;
        }
    }
    return array(
        'status' => $status,
        'headers' => $respHeaders,
        'body' => is_string($body) ? $body : '',
        'json' => $json,
    );
}

function header_is($res, $name, $needle) {
    $vals = $res['headers'][strtolower($name)] ?? array();
    foreach ($vals as $v) {
        if (stripos($v, $needle) !== false) {
            return true;
        }
    }
    return false;
}

function multipart_body($fields, $files) {
    $boundary = '----test' . bin2hex(random_bytes(8));
    $body = '';
    foreach ($fields as $k => $v) {
        $body .= "--$boundary\r\nContent-Disposition: form-data; name=\"$k\"\r\n\r\n$v\r\n";
    }
    foreach ($files as $k => $f) {
        $body .= "--$boundary\r\nContent-Disposition: form-data; name=\"$k\"; filename=\"{$f['filename']}\"\r\n"
            . "Content-Type: {$f['type']}\r\n\r\n{$f['content']}\r\n";
    }
    $body .= "--$boundary--\r\n";
    return array($body, 'multipart/form-data; boundary=' . $boundary);
}

// ---- Start the test server ----

$proc = null;
$base = null;
for ($port = 18765; $port <= 18784; $port++) {
    $cmd = array(
        PHP_BINARY,
        '-d', 'upload_max_filesize=32M',
        '-d', 'post_max_size=32M',
        '-S', '127.0.0.1:' . $port,
        '-t', $root,
    );
    $env = array(
        'PATH' => getenv('PATH') ?: '/usr/bin:/bin',
        'DB_DSN' => 'sqlite:' . $dbFile,
        'DB_USER' => '',
        'DB_PASS' => '',
        'STORAGE_PATH' => $storage,
        'INSTALL_TOKEN' => $installToken,
        'MAIL_LOG_PATH' => $mailLog,
    );
    $descriptors = array(
        0 => array('file', '/dev/null', 'r'),
        1 => array('file', $tmp . '/server-stdout.log', 'w'),
        2 => array('file', $tmp . '/server-stderr.log', 'w'),
    );
    $candidate = proc_open($cmd, $descriptors, $pipes, $root, $env);
    if (!is_resource($candidate)) {
        continue;
    }
    $tryBase = 'http://127.0.0.1:' . $port;
    $ready = false;
    for ($i = 0; $i < 50; $i++) {
        $st = proc_get_status($candidate);
        if (!$st['running']) {
            break;
        }
        $probe = @file_get_contents($tryBase . '/api/session.php', false, stream_context_create(array(
            'http' => array('ignore_errors' => true, 'timeout' => 2),
        )));
        if ($probe !== false) {
            $ready = true;
            break;
        }
        usleep(100000);
    }
    if ($ready) {
        $proc = $candidate;
        $base = $tryBase;
        break;
    }
    proc_terminate($candidate);
    proc_close($candidate);
}
if ($proc === null) {
    echo "FAIL could not start test server\n";
    if (!$keep) {
        rrmdir($tmp);
    }
    exit(1);
}
check('test server starts', true);

function finish($code) {
    global $proc, $tmp, $keep, $passed, $failed;
    if (is_resource($proc)) {
        proc_terminate($proc);
        proc_close($proc);
    }
    echo "\n$passed passed, $failed failed\n";
    if ($keep) {
        echo "Temp dir kept: $tmp\n";
    } else {
        rrmdir($tmp);
    }
    exit($code);
}

$anonJar = array();
$adminJar = array();

function get_csrf($base, &$jar) {
    $r = http_req($base, 'GET', '/api/session.php', $jar);
    return is_array($r['json']) && isset($r['json']['csrf']) ? (string) $r['json']['csrf'] : '';
}

// ---- Install ----

$r = http_req($base, 'GET', '/api/install.php', $anonJar);
check('install status without token -> 403', $r['status'] === 403, 'got ' . $r['status']);

$r = http_req($base, 'POST', '/api/install.php', $anonJar, array(
    'headers' => array('Content-Type: application/json'),
    'body' => json_encode(array('token' => 'wrong-token', 'email' => $adminEmail, 'password' => $adminPass)),
));
check('install with wrong token -> 403', $r['status'] === 403, 'got ' . $r['status']);

$r = http_req($base, 'POST', '/api/install.php', $anonJar, array(
    'headers' => array('Content-Type: application/json'),
    'body' => json_encode(array('token' => $installToken, 'email' => $adminEmail, 'password' => 'short')),
));
check('install with short password -> 400', $r['status'] === 400, 'got ' . $r['status']);

$r = http_req($base, 'POST', '/api/install.php', $anonJar, array(
    'headers' => array('Content-Type: application/json'),
    'body' => json_encode(array('token' => $installToken, 'email' => $adminEmail, 'password' => $adminPass)),
));
check('install succeeds', $r['status'] === 200 && ($r['json']['ok'] ?? false) === true, 'got ' . $r['status'] . ' ' . $r['body']);

$r = http_req($base, 'GET', '/api/install.php?token=' . urlencode($installToken), $anonJar);
check('install status with token reports installed', $r['status'] === 200 && ($r['json']['installed'] ?? false) === true, 'got ' . $r['status'] . ' ' . $r['body']);

$r = http_req($base, 'POST', '/api/install.php', $anonJar, array(
    'headers' => array('Content-Type: application/json'),
    'body' => json_encode(array('token' => $installToken, 'email' => 'second@example.test', 'password' => $adminPass)),
));
check('second install refused -> 409', $r['status'] === 409, 'got ' . $r['status']);

// ---- Session shape + login ----

$r = http_req($base, 'GET', '/api/session.php', $anonJar);
$anonCsrf = is_array($r['json']) ? (string) ($r['json']['csrf'] ?? '') : '';
check('session (anon) shape', $r['status'] === 200
    && ($r['json']['authenticated'] ?? null) === false
    && ($r['json']['owner'] ?? null) === false
    && array_key_exists('email', $r['json'] ?? array()) && $r['json']['email'] === null
    && preg_match('/^[0-9a-f]{64}$/', $anonCsrf) === 1, $r['body']);

$r = http_req($base, 'POST', '/api/login.php', $anonJar, array(
    'headers' => array('Content-Type: application/json'),
    'body' => json_encode(array('email' => $adminEmail, 'password' => $adminPass)),
));
check('login without CSRF -> 403', $r['status'] === 403, 'got ' . $r['status']);

$r = http_req($base, 'POST', '/api/login.php', $anonJar, array(
    'headers' => array('Content-Type: application/json', 'X-CSRF-Token: ' . $anonCsrf),
    'body' => json_encode(array('email' => $adminEmail, 'password' => 'definitely-wrong-password')),
));
check('login wrong password -> 401', $r['status'] === 401, 'got ' . $r['status']);

// Log in on the admin jar (fresh CSRF for that session).
$adminCsrf = get_csrf($base, $adminJar);
$r = http_req($base, 'POST', '/api/login.php', $adminJar, array(
    'headers' => array('Content-Type: application/json', 'X-CSRF-Token: ' . $adminCsrf),
    'body' => json_encode(array('email' => $adminEmail, 'password' => $adminPass)),
));
check('login success', $r['status'] === 200 && ($r['json']['ok'] ?? false) === true, 'got ' . $r['status'] . ' ' . $r['body']);

$r = http_req($base, 'GET', '/api/session.php', $adminJar);
check('session (admin) shape', $r['status'] === 200
    && ($r['json']['authenticated'] ?? null) === true
    && ($r['json']['owner'] ?? null) === true
    && ($r['json']['email'] ?? null) === $adminEmail
    && isset($r['json']['csrf']), $r['body']);
$adminCsrf = (string) ($r['json']['csrf'] ?? '');

// ---- Public gallery / software filters ----

$r = http_req($base, 'GET', '/api/gallery.php', $anonJar);
check('gallery initially empty', $r['status'] === 200 && $r['json'] === array(), 'got ' . $r['status'] . ' ' . $r['body']);

$r = http_req($base, 'GET', '/api/software.php', $anonJar);
$sw = is_array($r['json']) ? $r['json'] : null;
check('software lists stockdesk seed, public shape only',
    $r['status'] === 200 && is_array($sw) && count($sw) === 1
    && $sw[0]['id'] === 'stockdesk' && $sw[0]['title'] === 'Stockdesk'
    && $sw[0]['description'] === 'Plumbing inventory and stock management workspace.'
    && array_keys($sw[0]) === array('id', 'title', 'description'),
    'got ' . $r['status'] . ' ' . $r['body']);

// ---- Manage items: auth + admin shape ----

$r = http_req($base, 'GET', '/api/manage/items.php', $anonJar);
check('manage/items anon -> 401', $r['status'] === 401, 'got ' . $r['status']);

$r = http_req($base, 'GET', '/api/manage/items.php', $adminJar);
$items = is_array($r['json']) ? $r['json'] : null;
check('manage/items admin shows seed with admin fields',
    $r['status'] === 200 && is_array($items) && count($items) === 1
    && $items[0]['id'] === 'stockdesk' && $items[0]['archived'] === false
    && array_key_exists('liveUrl', $items[0]) && array_key_exists('demoUrl', $items[0]),
    'got ' . $r['status'] . ' ' . $r['body']);

// ---- Upload validation ----

$png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
$pdf = "%PDF-1.4\n1 0 obj\n<<>>\nendobj\ntrailer\n<<>>\n%%EOF\n";

function do_upload($base, &$jar, $csrf, $fields, $files) {
    list($body, $ctype) = multipart_body($fields, $files);
    $headers = array('Content-Type: ' . $ctype);
    if ($csrf !== '') {
        $headers[] = 'X-CSRF-Token: ' . $csrf;
    }
    return http_req($base, 'POST', '/api/manage/upload.php', $jar, array(
        'headers' => $headers, 'body' => $body,
    ));
}

$r = do_upload($base, $anonJar, $anonCsrf,
    array('kind' => 'design', 'title' => 'Nope'),
    array('file' => array('filename' => 'x.png', 'content' => $png, 'type' => 'image/png')));
check('upload anon -> 401', $r['status'] === 401, 'got ' . $r['status']);

$r = do_upload($base, $adminJar, '',
    array('kind' => 'design', 'title' => 'No CSRF'),
    array('file' => array('filename' => 'x.png', 'content' => $png, 'type' => 'image/png')));
check('upload without CSRF -> 403', $r['status'] === 403, 'got ' . $r['status']);

$r = do_upload($base, $adminJar, $adminCsrf,
    array('kind' => 'design', 'title' => 'No file'), array());
check('upload missing file -> 400', $r['status'] === 400, 'got ' . $r['status']);

$r = do_upload($base, $adminJar, $adminCsrf,
    array('kind' => 'design', 'title' => 'Text file'),
    array('file' => array('filename' => 'x.txt', 'content' => 'hello, plain text', 'type' => 'text/plain')));
check('upload bad MIME rejected -> 400', $r['status'] === 400, 'got ' . $r['status']);

$big = str_repeat("\0", 16 * 1024 * 1024);
$r = do_upload($base, $adminJar, $adminCsrf,
    array('kind' => 'design', 'title' => 'Too big'),
    array('file' => array('filename' => 'big.png', 'content' => $big, 'type' => 'image/png')));
unset($big);
check('upload oversize rejected -> 400', $r['status'] === 400, 'got ' . $r['status']);

$r = do_upload($base, $adminJar, $adminCsrf,
    array('kind' => 'design', 'title' => str_repeat('a', 121)),
    array('file' => array('filename' => 'x.png', 'content' => $png, 'type' => 'image/png')));
check('upload title too long -> 400', $r['status'] === 400, 'got ' . $r['status']);

$r = do_upload($base, $adminJar, $adminCsrf,
    array('kind' => 'design', 'title' => 'Test artwork', 'description' => 'A test design.', 'published' => 'true'),
    array('file' => array('filename' => 'art.png', 'content' => $png, 'type' => 'image/png')));
$pngId = is_array($r['json']) ? (string) ($r['json']['id'] ?? '') : '';
check('upload valid PNG accepted -> 201', $r['status'] === 201
    && ($r['json']['kind'] ?? '') === 'design'
    && ($r['json']['mime'] ?? '') === 'image/png'
    && ($r['json']['published'] ?? null) === true
    && $pngId !== '', 'got ' . $r['status'] . ' ' . $r['body']);

$stored = glob($storage . '/*');
check('uploaded PNG lands on disk under STORAGE_PATH',
    is_array($stored) && count($stored) === 1
    && preg_match('/^[a-z0-9-]+\.png$/', basename($stored[0])) === 1
    && filesize($stored[0]) === strlen($png),
    'files: ' . json_encode(array_map('basename', $stored ?: array())));

$r = do_upload($base, $adminJar, $adminCsrf,
    array('kind' => 'newsletter', 'title' => 'Test bulletin'),
    array('file' => array('filename' => 'doc.pdf', 'content' => $pdf, 'type' => 'application/pdf')));
$pdfId = is_array($r['json']) ? (string) ($r['json']['id'] ?? '') : '';
check('upload valid PDF accepted, unpublished by default -> 201', $r['status'] === 201
    && ($r['json']['mime'] ?? '') === 'application/pdf'
    && ($r['json']['published'] ?? null) === false
    && $pdfId !== '', 'got ' . $r['status'] . ' ' . $r['body']);

// ---- Gallery filter after uploads ----

$r = http_req($base, 'GET', '/api/gallery.php', $anonJar);
$g = is_array($r['json']) ? $r['json'] : null;
check('gallery shows published PNG only, not unpublished PDF',
    $r['status'] === 200 && is_array($g) && count($g) === 1 && $g[0]['id'] === $pngId,
    'got ' . $r['status'] . ' ' . $r['body']);

$r = http_req($base, 'GET', '/api/gallery.php?kind=newsletter', $anonJar);
check('gallery ?kind=newsletter empty while PDF unpublished',
    $r['status'] === 200 && $r['json'] === array(), 'got ' . $r['status'] . ' ' . $r['body']);

// ---- File access rules ----

$r = http_req($base, 'GET', '/api/file.php?id=' . urlencode($pngId), $anonJar);
check('file: published PNG public, bytes + headers OK',
    $r['status'] === 200 && $r['body'] === $png
    && header_is($r, 'Content-Type', 'image/png')
    && header_is($r, 'Content-Disposition', 'inline')
    && header_is($r, 'X-Content-Type-Options', 'nosniff')
    && header_is($r, 'Content-Security-Policy', 'sandbox'),
    'got ' . $r['status'] . ' headers ' . json_encode($r['headers']));

$r = http_req($base, 'GET', '/api/file.php?id=' . urlencode($pdfId), $anonJar);
check('file: unpublished PDF anon -> 404', $r['status'] === 404, 'got ' . $r['status']);

$r = http_req($base, 'GET', '/api/file.php?id=' . urlencode($pdfId), $adminJar);
check('file: unpublished PDF admin OK, attachment disposition',
    $r['status'] === 200 && $r['body'] === $pdf
    && header_is($r, 'Content-Type', 'application/pdf')
    && header_is($r, 'Content-Disposition', 'attachment'),
    'got ' . $r['status'] . ' headers ' . json_encode($r['headers']));

$r = http_req($base, 'GET', '/api/file.php?id=../etc/passwd', $anonJar);
check('file: bad id -> 404', $r['status'] === 404, 'got ' . $r['status']);

$r = http_req($base, 'GET', '/api/file.php?id=00000000-0000-0000-0000-000000000000', $anonJar);
check('file: missing record -> 404', $r['status'] === 404, 'got ' . $r['status']);

$r = http_req($base, 'GET', '/api/file.php?id=stockdesk', $anonJar);
check('file: software record -> 404', $r['status'] === 404, 'got ' . $r['status']);

// ---- PATCH publish / archive / rename ----

function do_patch($base, &$jar, $csrf, $id, $data) {
    $headers = array('Content-Type: application/json');
    if ($csrf !== '') {
        $headers[] = 'X-CSRF-Token: ' . $csrf;
    }
    return http_req($base, 'PATCH', '/api/manage/item.php?id=' . urlencode($id), $jar, array(
        'headers' => $headers, 'body' => json_encode($data),
    ));
}

$r = do_patch($base, $adminJar, '', $pdfId, array('published' => true));
check('PATCH without CSRF -> 403', $r['status'] === 403, 'got ' . $r['status']);

$r = do_patch($base, $adminJar, $adminCsrf, $pdfId, array('published' => true));
check('PATCH publish PDF', $r['status'] === 200 && ($r['json']['published'] ?? null) === true, 'got ' . $r['status'] . ' ' . $r['body']);

$r = http_req($base, 'GET', '/api/gallery.php', $anonJar);
check('gallery shows both after publish', $r['status'] === 200 && is_array($r['json']) && count($r['json']) === 2, 'got ' . $r['status'] . ' ' . $r['body']);

$r = do_patch($base, $adminJar, $adminCsrf, $pngId, array('title' => 'Renamed artwork'));
check('PATCH rename', $r['status'] === 200 && ($r['json']['title'] ?? '') === 'Renamed artwork', 'got ' . $r['status'] . ' ' . $r['body']);

$r = do_patch($base, $adminJar, $adminCsrf, $pngId, array('title' => ''));
check('PATCH empty title -> 400', $r['status'] === 400, 'got ' . $r['status']);

$r = do_patch($base, $adminJar, $adminCsrf, $pngId, array('title' => str_repeat('b', 121)));
check('PATCH title too long -> 400', $r['status'] === 400, 'got ' . $r['status']);

$r = do_patch($base, $adminJar, $adminCsrf, '00000000-0000-0000-0000-000000000000', array('published' => true));
check('PATCH missing record -> 404', $r['status'] === 404, 'got ' . $r['status']);

$r = do_patch($base, $adminJar, $adminCsrf, $pngId, array('archived' => true));
check('PATCH archive', $r['status'] === 200 && ($r['json']['archived'] ?? null) === true, 'got ' . $r['status'] . ' ' . $r['body']);

$r = http_req($base, 'GET', '/api/gallery.php', $anonJar);
check('gallery hides archived PNG', $r['status'] === 200 && is_array($r['json']) && count($r['json']) === 1, 'got ' . $r['status'] . ' ' . $r['body']);

$r = http_req($base, 'GET', '/api/file.php?id=' . urlencode($pngId), $adminJar);
check('file: archived PNG admin -> 404', $r['status'] === 404, 'got ' . $r['status']);

$r = do_patch($base, $adminJar, $adminCsrf, $pngId, array('archived' => false));
check('PATCH restore from archive', $r['status'] === 200 && ($r['json']['archived'] ?? null) === false, 'got ' . $r['status'] . ' ' . $r['body']);

$r = http_req($base, 'GET', '/api/file.php?id=' . urlencode($pngId), $anonJar);
check('file: restored PNG public again', $r['status'] === 200 && $r['body'] === $png, 'got ' . $r['status']);

// ---- Software create / update ----

function do_software($base, &$jar, $csrf, $data) {
    $headers = array('Content-Type: application/json');
    if ($csrf !== '') {
        $headers[] = 'X-CSRF-Token: ' . $csrf;
    }
    return http_req($base, 'POST', '/api/manage/software.php', $jar, array(
        'headers' => $headers, 'body' => json_encode($data),
    ));
}

$r = do_software($base, $adminJar, $adminCsrf, array(
    'title' => 'Bad URL app', 'liveUrl' => 'http://example.com/app'));
check('software http URL rejected -> 400', $r['status'] === 400, 'got ' . $r['status']);

$r = do_software($base, $adminJar, $adminCsrf, array(
    'title' => 'Creds URL app', 'liveUrl' => 'https://user:pass@example.com/app'));
check('software URL with credentials rejected -> 400', $r['status'] === 400, 'got ' . $r['status']);

$r = do_software($base, $adminJar, $adminCsrf, array(
    'id' => 'BAD ID!', 'title' => 'Bad id app'));
check('software bad id rejected -> 400', $r['status'] === 400, 'got ' . $r['status']);

$r = do_software($base, $adminJar, $adminCsrf, array(
    'title' => 'Demo App', 'description' => 'A demo.',
    'liveUrl' => 'https://example.com/app', 'demoUrl' => 'https://demo.example.com/app',
    'published' => true));
$appId = is_array($r['json']) ? (string) ($r['json']['id'] ?? '') : '';
check('software create valid -> 201', $r['status'] === 201
    && ($r['json']['kind'] ?? '') === 'software'
    && ($r['json']['liveUrl'] ?? '') === 'https://example.com/app'
    && ($r['json']['demoUrl'] ?? '') === 'https://demo.example.com/app'
    && $appId !== '', 'got ' . $r['status'] . ' ' . $r['body']);

$r = http_req($base, 'GET', '/api/software.php', $anonJar);
$ids = array();
if (is_array($r['json'])) {
    foreach ($r['json'] as $row) {
        $ids[] = $row['id'];
    }
}
check('software list includes new published app', $r['status'] === 200 && in_array($appId, $ids, true) && in_array('stockdesk', $ids, true), 'got ' . $r['status'] . ' ' . $r['body']);

$r = do_software($base, $adminJar, $adminCsrf, array(
    'id' => $appId, 'title' => 'Demo App v2', 'description' => 'Updated.',
    'liveUrl' => 'https://example.com/app', 'demoUrl' => 'https://demo2.example.com/app',
    'published' => true));
check('software update same id', $r['status'] === 201
    && ($r['json']['id'] ?? '') === $appId
    && ($r['json']['title'] ?? '') === 'Demo App v2'
    && ($r['json']['demoUrl'] ?? '') === 'https://demo2.example.com/app',
    'got ' . $r['status'] . ' ' . $r['body']);

$r = do_software($base, $adminJar, $adminCsrf, array(
    'title' => 'Hidden App', 'published' => false));
$hiddenId = is_array($r['json']) ? (string) ($r['json']['id'] ?? '') : '';
check('software create unpublished -> 201', $r['status'] === 201 && $hiddenId !== '', 'got ' . $r['status'] . ' ' . $r['body']);

$r = http_req($base, 'GET', '/api/software.php', $anonJar);
$ids = array();
if (is_array($r['json'])) {
    foreach ($r['json'] as $row) {
        $ids[] = $row['id'];
    }
}
check('software list hides unpublished app', $r['status'] === 200 && !in_array($hiddenId, $ids, true), 'got ' . $r['status'] . ' ' . $r['body']);

// ---- Contact: validation, honeypot, rate limit ----

function do_contact($base, &$jar, $csrf, $data, $form = false) {
    $headers = array();
    if ($csrf !== '') {
        $headers[] = 'X-CSRF-Token: ' . $csrf;
    }
    if ($form) {
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        $body = http_build_query($data);
    } else {
        $headers[] = 'Content-Type: application/json';
        $body = json_encode($data);
    }
    return http_req($base, 'POST', '/api/contact.php', $jar, array(
        'headers' => $headers, 'body' => $body,
    ));
}

$contactCsrf = get_csrf($base, $anonJar);

$r = do_contact($base, $anonJar, '', array('name' => 'A', 'email' => 'a@example.test', 'message' => 'Hi'));
check('contact without CSRF -> 403', $r['status'] === 403, 'got ' . $r['status']);

$r = do_contact($base, $anonJar, $contactCsrf, array('email' => 'a@example.test', 'message' => 'Hi'));
check('contact missing name -> 400', $r['status'] === 400, 'got ' . $r['status']);

$r = do_contact($base, $anonJar, $contactCsrf, array('name' => 'A', 'email' => 'not-an-email', 'message' => 'Hi'));
check('contact bad email -> 400', $r['status'] === 400, 'got ' . $r['status']);

$r = do_contact($base, $anonJar, $contactCsrf, array('name' => 'A', 'email' => 'a@example.test', 'message' => ''));
check('contact missing message -> 400', $r['status'] === 400, 'got ' . $r['status']);

$r = do_contact($base, $anonJar, $contactCsrf, array(
    'name' => 'Bot', 'email' => 'bot@example.test', 'message' => 'spam', 'website' => 'https://spam.example'));
check('contact honeypot pretends success', $r['status'] === 200 && ($r['json']['ok'] ?? false) === true, 'got ' . $r['status'] . ' ' . $r['body']);

$r = http_req($base, 'GET', '/api/manage/enquiries.php', $adminJar);
check('honeypot enquiry not stored', $r['status'] === 200 && $r['json'] === array(), 'got ' . $r['status'] . ' ' . $r['body']);

$r = do_contact($base, $anonJar, $contactCsrf, array(
    'name' => 'First Enquirer', 'email' => 'first@example.test',
    'company' => 'Acme', 'service' => 'SEO', 'message' => 'Please tell me about SEO.'));
check('contact valid (JSON) -> ok', $r['status'] === 200 && ($r['json']['ok'] ?? false) === true, 'got ' . $r['status'] . ' ' . $r['body']);

$mail = is_file($mailLog) ? file_get_contents($mailLog) : '';
check('contact email logged with recipient + content',
    strpos($mail, 'To: contact@nitiksham.com') !== false
    && strpos($mail, 'First Enquirer') !== false
    && strpos($mail, 'Please tell me about SEO.') !== false,
    'mail log: ' . substr($mail, 0, 200));

$r = do_contact($base, $anonJar, $contactCsrf, array(
    'name' => 'Second Enquirer', 'email' => 'second@example.test', 'message' => 'Form post here.'), true);
check('contact valid (form-encoded) -> ok', $r['status'] === 200 && ($r['json']['ok'] ?? false) === true, 'got ' . $r['status'] . ' ' . $r['body']);

$rateOk = true;
for ($i = 3; $i <= 5; $i++) {
    $r = do_contact($base, $anonJar, $contactCsrf, array(
        'name' => "Enquirer $i", 'email' => "user$i@example.test", 'message' => "Message $i."));
    if ($r['status'] !== 200) {
        $rateOk = false;
    }
}
check('contact valid #3-#5 -> ok (rate limit is 5/hour)', $rateOk, 'last status ' . $r['status']);

$r = do_contact($base, $anonJar, $contactCsrf, array(
    'name' => 'Sixth', 'email' => 'sixth@example.test', 'message' => 'One too many.'));
check('contact 6th in an hour -> 429', $r['status'] === 429, 'got ' . $r['status']);

// ---- Enquiries: list / delete (admin only) ----

$r = http_req($base, 'GET', '/api/manage/enquiries.php', $anonJar);
check('enquiries anon -> 401', $r['status'] === 401, 'got ' . $r['status']);

$r = http_req($base, 'GET', '/api/manage/enquiries.php', $adminJar);
$enq = is_array($r['json']) ? $r['json'] : null;
check('enquiries admin lists 5, newest first',
    $r['status'] === 200 && is_array($enq) && count($enq) === 5
    && $enq[0]['name'] === 'Enquirer 5'
    && isset($enq[0]['email'], $enq[0]['message'], $enq[0]['createdAt']),
    'got ' . $r['status'] . ' ' . $r['body']);
$delId = is_array($enq) && isset($enq[0]['id']) ? (string) $enq[0]['id'] : '';

$r = http_req($base, 'DELETE', '/api/manage/enquiries.php?id=' . urlencode($delId), $adminJar);
check('enquiries DELETE without CSRF -> 403', $r['status'] === 403, 'got ' . $r['status']);

$r = http_req($base, 'DELETE', '/api/manage/enquiries.php?id=' . urlencode($delId), $adminJar, array(
    'headers' => array('X-CSRF-Token: ' . $adminCsrf),
));
check('enquiries DELETE one -> ok', $r['status'] === 200 && ($r['json']['ok'] ?? false) === true, 'got ' . $r['status'] . ' ' . $r['body']);

$r = http_req($base, 'DELETE', '/api/manage/enquiries.php?id=' . urlencode($delId), $adminJar, array(
    'headers' => array('X-CSRF-Token: ' . $adminCsrf),
));
check('enquiries DELETE same again -> 404', $r['status'] === 404, 'got ' . $r['status']);

$r = http_req($base, 'GET', '/api/manage/enquiries.php', $adminJar);
check('enquiries list now 4', $r['status'] === 200 && is_array($r['json']) && count($r['json']) === 4, 'got ' . $r['status'] . ' ' . $r['body']);

// ---- Logout + login throttle ----

$r = http_req($base, 'POST', '/api/logout.php', $adminJar, array(
    'headers' => array('X-CSRF-Token: ' . $adminCsrf),
));
check('logout -> ok', $r['status'] === 200 && ($r['json']['ok'] ?? false) === true, 'got ' . $r['status'] . ' ' . $r['body']);

$r = http_req($base, 'GET', '/api/session.php', $adminJar);
check('session after logout unauthenticated', $r['status'] === 200 && ($r['json']['authenticated'] ?? null) === false, 'got ' . $r['status'] . ' ' . $r['body']);

// Throttle: the earlier wrong-password attempt was cleared by the
// successful login, so 5 fresh failures then a correct password must 429.
$throttleJar = array();
$throttleCsrf = get_csrf($base, $throttleJar);
$failCount = 0;
for ($i = 0; $i < 5; $i++) {
    $r = http_req($base, 'POST', '/api/login.php', $throttleJar, array(
        'headers' => array('Content-Type: application/json', 'X-CSRF-Token: ' . $throttleCsrf),
        'body' => json_encode(array('email' => $adminEmail, 'password' => 'wrong-password-' . $i)),
    ));
    if ($r['status'] === 401) {
        $failCount++;
    }
}
check('login throttle: 5 failures each -> 401', $failCount === 5, "got $failCount/5");

$r = http_req($base, 'POST', '/api/login.php', $throttleJar, array(
    'headers' => array('Content-Type: application/json', 'X-CSRF-Token: ' . $throttleCsrf),
    'body' => json_encode(array('email' => $adminEmail, 'password' => $adminPass)),
));
check('login throttle: correct password blocked -> 429', $r['status'] === 429, 'got ' . $r['status']);

finish($failed === 0 ? 0 : 1);
