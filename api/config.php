<?php
// Nitiksham site API — configuration.
//
// Values come from environment variables. A config.local.php file that
// returns an array of overrides may also be used; it must live OUTSIDE the
// web root (for example next to public_html) and is never committed.
// Recognised keys: db_dsn, db_user, db_pass, storage_path, install_token,
// install_reset_file, mail_log_path, contact_email.
//
// Never put a real secret in this file. Placeholders only, e.g. <your-token>.

function nsm_config_local_paths() {
    $paths = array();
    $envPath = getenv('NITIKSHAM_CONFIG');
    if (is_string($envPath) && $envPath !== '') {
        $paths[] = $envPath;
    }
    // One level above the project root: on the server api/ lives in
    // public_html/api/, so this is the home directory, outside the web root.
    $paths[] = dirname(__DIR__, 2) . '/config.local.php';
    // Project root (handy for local development only).
    $paths[] = dirname(__DIR__) . '/config.local.php';
    return $paths;
}

function nsm_config($key = null, $default = null) {
    static $config = null;
    if ($config === null) {
        $config = array(
            // Production: mysql:host=<host>;dbname=<db>;charset=utf8mb4
            // Tests: sqlite:<path-to-file>
            'db_dsn' => getenv('DB_DSN') ?: '',
            'db_user' => getenv('DB_USER') ?: '',
            'db_pass' => getenv('DB_PASS') ?: '',
            // Private file storage, outside the web root on the server.
            'storage_path' => getenv('STORAGE_PATH') ?: dirname(__DIR__) . '/storage',
            'install_token' => getenv('INSTALL_TOKEN') ?: '',
            'install_reset_file' => getenv('INSTALL_RESET_FILE') ?: '',
            // When set, mail is appended to this file instead of sent.
            // Used by the test harness only; leave unset in production.
            'mail_log_path' => getenv('MAIL_LOG_PATH') ?: '',
            'contact_email' => getenv('CONTACT_EMAIL') ?: 'contact@nitiksham.com',
            'max_file_size' => 15 * 1024 * 1024, // 15 MiB
        );
        foreach (nsm_config_local_paths() as $path) {
            if (is_file($path)) {
                $local = require $path;
                if (is_array($local)) {
                    $config = array_merge($config, $local);
                }
                break;
            }
        }
        if ($config['install_reset_file'] === '') {
            $config['install_reset_file'] = rtrim($config['storage_path'], '/') . '/.allow-reinstall';
        }
    }
    if ($key === null) {
        return $config;
    }
    return array_key_exists($key, $config) ? $config[$key] : $default;
}
