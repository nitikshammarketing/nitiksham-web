<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/private/bootstrap.php';
require_once dirname(__DIR__, 2) . '/private/data.php';

nsm_session_start();
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/api', PHP_URL_PATH) ?: '/api';
$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
if (str_starts_with($path, '/api/index.php')) $path = substr($path, strlen('/api/index.php')) ?: '/';
elseif (str_starts_with($path, '/api')) $path = substr($path, 4) ?: '/';
else nsm_error('Not found.', 404);
$path = '/' . trim($path, '/');

try {
    // Static frontend only receives a boolean plus its own session status.
    if ($path === '/session' && $method === 'GET') {
        $isOwner = nsm_is_owner();
        $cfg = $isOwner ? nsm_config() : [];
        nsm_json(['authenticated' => $isOwner, 'owner' => $isOwner, 'email' => $isOwner ? (string)$cfg['admin_email'] : null]);
    }
    if ($path === '/auth/login' && $method === 'POST') {
        if (!nsm_csrf_valid(is_string($_POST['csrf'] ?? null) ? $_POST['csrf'] : null)) nsm_error('Invalid login request.', 403);
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');
        $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
        $rateLimited = nsm_rate_limited($ip);
        $cfg = nsm_config();
        $isValid = !$rateLimited && isset($cfg['admin_email'], $cfg['admin_password_hash']) &&
            hash_equals(strtolower(trim((string)$cfg['admin_email'])), $email) &&
            password_verify($password, (string)$cfg['admin_password_hash']);
        if (!$isValid) {
            if (!$rateLimited) nsm_rate_limited($ip, true);
            if ($rateLimited) {http_response_code(429); header('Retry-After: 900'); echo 'Too many login attempts. Try again later.'; exit;}
            header('Location: /login/?error=1', true, 303);
            exit;
        }
        session_regenerate_id(true);
        $_SESSION['owner'] = true;
        $_SESSION['owner_at'] = time();
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
        header('Cache-Control: no-store');
        header('Location: /studio/', true, 303);
        exit;
    }
    if ($path === '/auth/logout' && $method === 'POST') {
        nsm_require_csrf();
        $_SESSION = [];
        session_regenerate_id(true);
        header('Cache-Control: no-store');
        header('Location: /login/', true, 303);
        exit;
    }
    if ($path === '/gallery' && $method === 'GET') nsm_json(nsm_list('gallery'));
    if ($path === '/software' && $method === 'GET') {
        $rows = nsm_list('software');
        nsm_json(array_map(fn($r) => array_intersect_key($r, array_fill_keys(['id','title','description'], true)), $rows));
    }
    if (preg_match('#^/file/([a-z0-9-]{1,40})$#D', $path, $m) && $method === 'GET') {
        $row = nsm_row($m[1]);
        if (!$row || $row['kind'] === 'software' || !$row['file_name'] || (bool)$row['archived'] || (!(bool)$row['published'] && !nsm_is_owner())) nsm_error('Not found.', 404);
        $safeMimes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'application/pdf' => 'pdf'];
        $mime = (string) $row['mime'];
        if (!isset($safeMimes[$mime])) nsm_error('Not found.', 404);
        $storage = dirname(__DIR__, 2) . '/private/storage/files';
        $file = $storage . '/' . $row['file_name'];
        if (!is_file($file) || basename($file) !== $row['file_name']) nsm_error('Not found.', 404);
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . (string)filesize($file));
        header('X-Content-Type-Options: nosniff');
        header("Content-Security-Policy: default-src 'none'; sandbox");
        header('Cache-Control: no-store');
        header('Content-Disposition: ' . ($mime === 'application/pdf' ? 'attachment' : 'inline') . '; filename="' . $row['id'] . '.' . $safeMimes[$mime] . '"');
        readfile($file);
        exit;
    }

    if (!str_starts_with($path, '/manage/')) nsm_error('Not found.', 404);
    if (!nsm_is_owner()) nsm_error('Owner sign-in required.', 401);
    if (!in_array($method, ['GET','HEAD'], true)) nsm_require_csrf();

    if ($path === '/manage/items' && $method === 'GET') nsm_json(nsm_list('all'));
    if ($path === '/manage/upload' && $method === 'POST') {
        $title = nsm_limited_text($_POST['title'] ?? '', 121);
        $desc = nsm_limited_text($_POST['description'] ?? '', 1000);
        $kind = (string) ($_POST['kind'] ?? '');
        if (!in_array($kind, ['design', 'newsletter'], true) || $title === '' || (function_exists('mb_strlen') ? mb_strlen($title) : strlen($title)) > 120) nsm_error('Enter a valid title and category.');
        $entry = $_FILES['file'] ?? null;
        if (!$entry || is_array($entry['tmp_name'] ?? null) || ($entry['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) nsm_error('Choose an image or PDF file. Check PHP upload limits.');
        $max = min(15728640, (int)(nsm_config()['max_upload_bytes'] ?? 15728640));
        $size = (int) ($entry['size'] ?? 0);
        if ($size < 1 || $size > $max) nsm_error('Maximum file size is 15 MB.', 413);
        $temp = (string) ($entry['tmp_name'] ?? '');
        if (!$temp || !is_uploaded_file($temp)) nsm_error('Upload failed.');
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($temp);
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'application/pdf' => 'pdf'];
        if (!is_string($mime) || !isset($allowed[$mime])) nsm_error('Only JPG, PNG, WebP and PDF are accepted.');
        if ($mime !== 'application/pdf' && @getimagesize($temp) === false) nsm_error('Invalid image file.');
        if ($mime === 'application/pdf' && file_get_contents($temp, false, null, 0, 5) !== '%PDF-') nsm_error('Invalid PDF file.');
        $id = nsm_generate_id();
        $dir = dirname(__DIR__, 2) . '/private/storage/files';
        if (!is_dir($dir) && !mkdir($dir, 0700, true) && !is_dir($dir)) nsm_error('Cannot store uploads.', 500);
        $filename = $id . '.' . $allowed[$mime];
        $destination = $dir . '/' . $filename;
        if (!move_uploaded_file($temp, $destination)) nsm_error('Cannot store uploaded file.', 500);
        @chmod($destination, 0600);
        try {
            $query = nsm_db()->prepare('INSERT INTO nitiksham_records (id,kind,title,description,mime,published,archived,file_name,created_at) VALUES (?,?,?,?,?,?,0,?,UTC_TIMESTAMP())');
            $query->execute([$id,$kind,$title,$desc,$mime,filter_var($_POST['published'] ?? false, FILTER_VALIDATE_BOOLEAN) ? 1 : 0,$filename]);
        } catch (Throwable $e) { @unlink($destination); throw $e; }
        nsm_json(nsm_record(nsm_row($id) ?: [], true), 201);
    }
    if ($path === '/manage/software' && $method === 'POST') {
        $input = nsm_input_json();
        $title = nsm_limited_text($input['title'] ?? '', 120);
        if ($title === '') nsm_error('Enter a software name.');
        $id = trim((string) ($input['id'] ?? ''));
        if ($id === '') $id = nsm_generate_id();
        if (!nsm_uuid_ok($id)) nsm_error('Invalid software ID.');
        $existing = nsm_row($id);
        if ($existing && $existing['kind'] !== 'software') nsm_error('Invalid software ID.');
        $live = nsm_valid_https_url($input['liveUrl'] ?? '');
        $demo = nsm_valid_https_url($input['demoUrl'] ?? '');
        if ($live === null || $demo === null) nsm_error('Use valid HTTPS URLs without credentials.');
        $desc = nsm_limited_text($input['description'] ?? '', 1000);
        $published = ($input['published'] ?? false) === true ? 1 : 0;
        if ($existing) {
            $q = nsm_db()->prepare('UPDATE nitiksham_records SET title=?, description=?, live_url=?, demo_url=?, published=? WHERE id=?');
            $q->execute([$title,$desc,$live,$demo,$published,$id]);
        } else {
            $q = nsm_db()->prepare("INSERT INTO nitiksham_records (id,kind,title,description,live_url,demo_url,published,mime,archived,created_at) VALUES (?,'software',?,?,?,?,?,'',0,UTC_TIMESTAMP())");
            $q->execute([$id,$title,$desc,$live,$demo,$published]);
        }
        nsm_json(nsm_record(nsm_row($id) ?: [], true), 201);
    }
    if (preg_match('#^/manage/items/([a-z0-9-]{1,40})$#D', $path, $m) && $method === 'PATCH') {
        $current = nsm_row($m[1]);
        if (!$current) nsm_error('Not found.', 404);
        $input = nsm_input_json();
        $fields = [];
        foreach (['published','archived'] as $key) {
            if (array_key_exists($key, $input) && is_bool($input[$key])) $fields[$key] = $input[$key] ? 1 : 0;
        }
        if (isset($input['title']) && is_string($input['title'])) {
            $title = nsm_limited_text($input['title'], 120);
            if ($title === '') nsm_error('Enter a title.');
            $fields['title'] = $title;
        }
        if ($fields) {
            $clause = implode(',', array_map(fn($key) => "$key=?", array_keys($fields)));
            $q = nsm_db()->prepare("UPDATE nitiksham_records SET $clause WHERE id=?");
            $q->execute([...array_values($fields),$m[1]]);
        }
        nsm_json(nsm_record(nsm_row($m[1]) ?: [], true));
    }
    nsm_error('Not found.', 404);
} catch (Throwable $e) {
    error_log('Nitiksham backend: ' . $e->getMessage());
    nsm_error('Backend unavailable or operation failed. Please check configuration.', 503);
}

function nsm_generate_id(): string {
    $h = bin2hex(random_bytes(16));
    return substr($h, 0, 8) . '-' . substr($h, 8, 4) . '-4' . substr($h, 13, 3) . '-' . dechex((hexdec($h[16]) & 3) | 8) . substr($h, 17, 3) . '-' . substr($h, 20, 12);
}
