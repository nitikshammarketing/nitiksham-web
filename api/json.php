<?php
// Nitiksham site API — JSON responses, request helpers, error handling.

// Any uncaught exception (for example a database failure) becomes a generic
// JSON 500 so internals are never leaked to the client. Details go to the
// server error log.
set_exception_handler(function ($e) {
    error_log('Nitiksham API error: ' . $e->getMessage());
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
    }
    echo json_encode(array('error' => 'Server error. Please try again later.'));
    exit(1);
});

function nsm_json($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    $out = json_encode(
        $data,
        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
    );
    if ($out === false) {
        $out = json_encode(array('error' => 'Server error. Please try again later.'));
    }
    echo $out;
    exit;
}

function nsm_fail($message, $status = 400) {
    nsm_json(array('error' => $message), $status);
}

function nsm_require_method($method) {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== $method) {
        header('Allow: ' . $method);
        nsm_fail('Method not allowed.', 405);
    }
}

// Decodes a JSON request body. Returns null when the body is empty or invalid.
function nsm_input_json() {
    $raw = file_get_contents('php://input');
    if ($raw === false || trim($raw) === '') {
        return null;
    }
    $data = json_decode($raw, true);
    return is_array($data) ? $data : null;
}

function nsm_header($name) {
    $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
    return isset($_SERVER[$key]) ? (string) $_SERVER[$key] : '';
}

function nsm_client_ip() {
    return isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : '';
}

function nsm_is_https() {
    if (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off') {
        return true;
    }
    return ($_SERVER['SERVER_PORT'] ?? '') === '443';
}
