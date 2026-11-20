<?php
// POST /api/logout.php with X-CSRF-Token header -> {ok: true}
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/json.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/csrf.php';

nsm_require_method('POST');
nsm_session_start();
nsm_csrf_check();
nsm_logout();
nsm_json(array('ok' => true));
