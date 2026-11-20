<?php
// GET /api/session.php -> {authenticated, owner, email, csrf}
// Public. Also issues the CSRF token anonymous pages need before POSTing
// (login form, contact form).
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/json.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/csrf.php';

nsm_require_method('GET');
$admin = nsm_current_admin();
nsm_json(array(
    'authenticated' => $admin !== null,
    'owner' => $admin !== null,
    'email' => $admin ? $admin['email'] : null,
    'csrf' => nsm_csrf_token(),
));
