<?php
// One-time setup for the Nitiksham site API.
//
// GET  /api/install.php?token=<INSTALL_TOKEN>  -> {installed: bool}
// POST /api/install.php  {token, email, password}
//   Creates the tables from schema.sql and the first admin account.
//
// The token comes only from the INSTALL_TOKEN environment variable (or
// config.local.php) — never from this file. The installer refuses to run
// twice: once an admin exists it answers 409, unless the reset flag file
// exists (STORAGE_PATH/.allow-reinstall, or INSTALL_RESET_FILE). With the
// flag present it drops and recreates the tables, creates the new admin,
// and removes the flag.
//
// Schema location: the build copies db/schema.sql to dist/api/schema.sql
// next to this file, so the deployed API is self-contained. The installer
// reads __DIR__ . '/schema.sql' first and falls back to the repo layout
// (__DIR__ . '/../db/schema.sql') for local development only.
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/json.php';

function nsm_install_token_ok($provided) {
    $token = nsm_config('install_token');
    if ($token === '' || !is_string($provided) || $provided === '') {
        return false;
    }
    return hash_equals($token, $provided);
}

function nsm_is_installed() {
    try {
        $st = nsm_db()->query('SELECT COUNT(*) FROM admins');
        return (int) $st->fetchColumn() > 0;
    } catch (PDOException $e) {
        return false;
    }
}

// Splits the schema file into statements. Comment-only lines are dropped
// first; each statement ends with a semicolon at end of line.
function nsm_split_sql($sql) {
    $lines = preg_split('/\r?\n/', $sql);
    $kept = array();
    foreach ($lines as $line) {
        if (preg_match('/^\s*(--|#)/', $line)) {
            continue;
        }
        $kept[] = $line;
    }
    $parts = preg_split('/;\s*(?:\r?\n|$)/', implode("\n", $kept));
    $out = array();
    foreach ($parts as $part) {
        if (trim($part) !== '') {
            $out[] = trim($part);
        }
    }
    return $out;
}

function nsm_schema_path() {
    $candidates = array(__DIR__ . '/schema.sql', __DIR__ . '/../db/schema.sql');
    foreach ($candidates as $path) {
        if (is_file($path)) {
            return $path;
        }
    }
    return null;
}

$method = $_SERVER['REQUEST_METHOD'] ?? '';

if (nsm_config('install_token') === '') {
    nsm_fail('Installer is not configured.', 500);
}

if ($method === 'GET') {
    $provided = isset($_GET['token']) ? (string) $_GET['token'] : nsm_header('X-Install-Token');
    if (!nsm_install_token_ok($provided)) {
        nsm_fail('Invalid install token.', 403);
    }
    nsm_json(array('installed' => nsm_is_installed()));
}

nsm_require_method('POST');
$data = nsm_input_json();
if (!is_array($data)) {
    nsm_fail('Invalid request.', 400);
}
$provided = isset($data['token']) ? (string) $data['token'] : nsm_header('X-Install-Token');
if (!nsm_install_token_ok($provided)) {
    nsm_fail('Invalid install token.', 403);
}
$email = strtolower(trim((string) ($data['email'] ?? '')));
$password = (string) ($data['password'] ?? '');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    nsm_fail('Enter a valid admin email.', 400);
}
if (mb_strlen($password) < 12) {
    nsm_fail('Password must be at least 12 characters.', 400);
}

$installed = nsm_is_installed();
$resetFile = nsm_config('install_reset_file');
$reset = is_file($resetFile);
if ($installed && !$reset) {
    nsm_fail('Already installed.', 409);
}

$schemaPath = nsm_schema_path();
if ($schemaPath === null) {
    nsm_fail('Schema file missing: expected schema.sql next to install.php (or db/schema.sql in the repo).', 500);
}
$schema = file_get_contents($schemaPath);
if ($schema === false) {
    nsm_fail('Schema file could not be read.', 500);
}

try {
    $pdo = nsm_db();
} catch (PDOException $e) {
    error_log('Nitiksham install: database connection failed: ' . $e->getMessage());
    nsm_fail('Database connection failed.', 500);
}

if ($installed && $reset) {
    foreach (array('login_attempts', 'enquiries', 'records', 'admins') as $table) {
        $pdo->exec('DROP TABLE IF EXISTS ' . $table);
    }
}
foreach (nsm_split_sql($schema) as $statement) {
    try {
        $pdo->exec($statement);
    } catch (PDOException $e) {
        // Index creation is the one non-idempotent step (MySQL has no
        // CREATE INDEX IF NOT EXISTS); an already-existing index is fine.
        if (stripos(ltrim($statement), 'CREATE INDEX') === 0) {
            continue;
        }
        error_log('Nitiksham install: schema statement failed: ' . $e->getMessage());
        nsm_fail('Database setup failed.', 500);
    }
}
if ($reset) {
    @unlink($resetFile);
}

$storage = rtrim(nsm_config('storage_path'), '/');
if (!is_dir($storage) && !mkdir($storage, 0750, true)) {
    nsm_fail('Storage directory could not be created.', 500);
}

$st = $pdo->prepare('INSERT INTO admins (id, email, password_hash, created_at) VALUES (?, ?, ?, ?)');
$st->execute(array(nsm_uuid(), $email, password_hash($password, PASSWORD_DEFAULT), nsm_now()));
nsm_json(array('ok' => true, 'email' => $email));
