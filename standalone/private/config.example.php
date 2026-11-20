<?php
/** Copy to config.php OUTSIDE public_html; never commit production credentials. */
return [
    'db_host' => 'localhost',
    'db_name' => 'CPANELUSER_nitiksham',
    'db_user' => 'CPANELUSER_nitiksham',
    'db_pass' => 'REPLACE_WITH_REAL_PASSWORD',
    'admin_email' => 'REPLACE_WITH_ADMIN_EMAIL',
    'admin_password_hash' => 'REPLACE_WITH_PASSWORD_HASH',
    'max_upload_bytes' => 15 * 1024 * 1024,
];
