<?php
// Nitiksham site API — database layer (PDO) and record helpers.
//
// SQL here is portable across MySQL (production) and SQLite (tests):
// no MySQL-only syntax in queries. Time-window cutoffs are computed in PHP
// and bound as parameters, never with database date functions.
require_once __DIR__ . '/config.php';

function nsm_db() {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }
    $dsn = nsm_config('db_dsn');
    if ($dsn === '') {
        throw new RuntimeException('Database is not configured.');
    }
    $pdo = new PDO($dsn, nsm_config('db_user'), nsm_config('db_pass'), array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ));
    return $pdo;
}

function nsm_uuid() {
    $b = random_bytes(16);
    $b[6] = chr((ord($b[6]) & 0x0f) | 0x40); // version 4
    $b[8] = chr((ord($b[8]) & 0x3f) | 0x80); // variant
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($b), 4));
}

// Record ids are lowercase letters, digits and hyphens, up to 40 chars.
// Matches the WordPress plugin's id rule (uuids plus the 'stockdesk' seed).
function nsm_id_ok($id) {
    return is_string($id) && (bool) preg_match('/^[a-z0-9-]{1,40}$/D', $id);
}

function nsm_now() {
    return gmdate('Y-m-d H:i:s');
}

// created_at (UTC 'Y-m-d H:i:s') -> ISO-8601 '...Z', as the plugin returned.
function nsm_iso($datetime) {
    $ts = strtotime((string) $datetime . ' UTC');
    if ($ts === false) {
        $ts = 0;
    }
    return gmdate('Y-m-d\TH:i:s\Z', $ts);
}

// Public record shape, mirroring the WordPress plugin's nsm_record().
// Admin callers also get archived + liveUrl/demoUrl (software only).
function nsm_record_shape($row, $admin = false) {
    $r = array(
        'id' => (string) $row['id'],
        'kind' => (string) $row['kind'],
        'title' => (string) $row['title'],
        'description' => (string) $row['description'],
        'mime' => (string) $row['mime'],
        'published' => (bool) $row['published'],
        'createdAt' => nsm_iso($row['created_at']),
    );
    if ($admin) {
        $r['archived'] = (bool) $row['archived'];
        if ($row['kind'] === 'software') {
            $r['liveUrl'] = (string) ($row['live_url'] ?? '');
            $r['demoUrl'] = (string) ($row['demo_url'] ?? '');
        }
    }
    return $r;
}

function nsm_get_record($id) {
    if (!nsm_id_ok($id)) {
        return null;
    }
    $st = nsm_db()->prepare(
        'SELECT id, kind, title, description, mime, published, archived,' .
        ' live_url, demo_url, owner_id, file_name, file_size, created_at' .
        ' FROM records WHERE id = ?'
    );
    $st->execute(array($id));
    $row = $st->fetch();
    return $row ?: null;
}

// $filter: 'gallery' (published, unarchived design/newsletter),
// 'software' (published, unarchived software), 'all' (everything).
// $kind optionally narrows 'gallery' to one kind.
function nsm_list_records($filter = 'all', $kind = '') {
    $where = '';
    $params = array();
    if ($filter === 'gallery') {
        $where = "WHERE published = 1 AND archived = 0 AND kind IN ('design', 'newsletter')";
        if ($kind === 'design' || $kind === 'newsletter') {
            $where = 'WHERE published = 1 AND archived = 0 AND kind = ?';
            $params[] = $kind;
        }
    } elseif ($filter === 'software') {
        $where = "WHERE published = 1 AND archived = 0 AND kind = 'software'";
    }
    $st = nsm_db()->prepare(
        'SELECT id, kind, title, description, mime, published, archived,' .
        ' live_url, demo_url, owner_id, file_name, file_size, created_at' .
        " FROM records $where ORDER BY created_at DESC"
    );
    $st->execute($params);
    return $st->fetchAll();
}

// Allowed upload MIME types and their file extensions.
function nsm_mime_ext($mime) {
    $map = array(
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'application/pdf' => 'pdf',
    );
    return isset($map[$mime]) ? $map[$mime] : null;
}

// Magic-byte MIME detection, mirroring the WordPress plugin. Reads only
// the first bytes of the file. Returns null for anything not allowed.
function nsm_detect_mime($path) {
    $fh = @fopen($path, 'rb');
    if ($fh === false) {
        return null;
    }
    $head = fread($fh, 12);
    fclose($fh);
    if ($head === false) {
        return null;
    }
    if (strlen($head) >= 3 && substr($head, 0, 3) === "\xFF\xD8\xFF") {
        return 'image/jpeg';
    }
    if (strlen($head) >= 8 && substr($head, 0, 8) === "\x89PNG\r\n\x1a\n") {
        return 'image/png';
    }
    if (strlen($head) >= 12 && substr($head, 0, 4) === 'RIFF' && substr($head, 8, 4) === 'WEBP') {
        return 'image/webp';
    }
    if (strlen($head) >= 5 && substr($head, 0, 5) === '%PDF-') {
        return 'application/pdf';
    }
    return null;
}

// HTTPS-only URL with no username/password, mirroring the plugin.
function nsm_https_url($raw) {
    $url = trim((string) $raw);
    if ($url === '') {
        return '';
    }
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return false;
    }
    $parts = parse_url($url);
    if (!$parts || strtolower($parts['scheme'] ?? '') !== 'https'
        || empty($parts['host']) || isset($parts['user']) || isset($parts['pass'])) {
        return false;
    }
    return $url;
}

// Plain-text field cleanup: no tags, single-spaced, trimmed.
function nsm_clean_text($value, $maxLen) {
    $value = trim(preg_replace('/\s+/', ' ', strip_tags((string) $value)));
    return mb_substr($value, 0, $maxLen);
}

// Multi-line field cleanup: no tags, length-capped, newlines kept.
function nsm_clean_multiline($value, $maxLen) {
    return mb_substr(trim(strip_tags((string) $value)), 0, $maxLen);
}
