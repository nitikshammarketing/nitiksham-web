<?php
// POST /api/contact.php with X-CSRF-Token header (fetch it first from
// GET /api/session.php). Accepts a JSON body or a plain form post.
//
// Validates name/email/message (plus optional company/phone/service),
// silently accepts honeypot submissions without storing them, rate-limits
// to 5 stored enquiries per hour per IP, stores the enquiry, and emails it
// to the contact address. Always returns {ok: true} on success; a failed
// email send is logged but does not fail the request — the enquiry is
// safely stored and visible in the Studio enquiries list.
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/json.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/mailer.php';

const NSM_CONTACT_MAX_PER_HOUR = 5;

nsm_require_method('POST');
nsm_session_start();
nsm_csrf_check();

$data = nsm_input_json();
if ($data === null) {
    $data = $_POST;
}

// Honeypot field: bots fill it, humans never see it. Pretend success.
if (trim((string) ($data['website'] ?? '')) !== '') {
    nsm_json(array('ok' => true));
}

$name = nsm_clean_text($data['name'] ?? '', 120);
$email = strtolower(trim((string) ($data['email'] ?? '')));
$message = nsm_clean_multiline($data['message'] ?? '', 5000);
$company = nsm_clean_text($data['company'] ?? '', 120);
$phone = nsm_clean_text($data['phone'] ?? '', 60);
$service = nsm_clean_text($data['service'] ?? '', 120);

if ($name === '') {
    nsm_fail('Please enter your name.', 400);
}
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    nsm_fail('Please enter a valid email address.', 400);
}
if ($message === '') {
    nsm_fail('Please enter a message.', 400);
}

$ip = nsm_client_ip();
$cutoff = gmdate('Y-m-d H:i:s', time() - 3600);
$st = nsm_db()->prepare('SELECT COUNT(*) FROM enquiries WHERE ip = ? AND created_at >= ?');
$st->execute(array($ip, $cutoff));
if ((int) $st->fetchColumn() >= NSM_CONTACT_MAX_PER_HOUR) {
    nsm_fail('Too many messages from this address. Please try again later.', 429);
}

$st = nsm_db()->prepare(
    'INSERT INTO enquiries (id, name, email, company, phone, service, message, ip, created_at)' .
    ' VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
);
$st->execute(array(
    nsm_uuid(), $name, $email, $company, $phone, $service, $message, $ip, nsm_now(),
));

$body = "New website enquiry\n\n"
    . "Name: $name\n"
    . "Email: $email\n"
    . ($company !== '' ? "Company: $company\n" : '')
    . ($phone !== '' ? "Phone: $phone\n" : '')
    . ($service !== '' ? "Service: $service\n" : '')
    . "\n$message\n";
if (!nsm_send_mail(nsm_config('contact_email'), 'New website enquiry from ' . $name, $body, $email)) {
    error_log('Nitiksham contact: email send failed for enquiry from ' . $email);
}

nsm_json(array('ok' => true));
