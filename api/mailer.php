<?php
// Nitiksham site API — outgoing mail.
//
// Production sends via PHP mail(). When the MAIL_LOG_PATH env var is set,
// mail is appended to that file instead — used by the test harness only.
require_once __DIR__ . '/config.php';

function nsm_mail_header_safe($value) {
    return trim(str_replace(array("\r", "\n"), '', (string) $value));
}

function nsm_send_mail($to, $subject, $body, $replyTo = '') {
    $to = nsm_mail_header_safe($to);
    $subject = nsm_mail_header_safe($subject);
    $from = nsm_config('contact_email');
    $log = nsm_config('mail_log_path');
    if ($log !== '') {
        $entry = 'To: ' . $to . "\nFrom: " . $from . "\n";
        if ($replyTo !== '') {
            $entry .= 'Reply-To: ' . nsm_mail_header_safe($replyTo) . "\n";
        }
        $entry .= 'Subject: ' . $subject . "\n\n" . $body . "\n---\n";
        return file_put_contents($log, $entry, FILE_APPEND | LOCK_EX) !== false;
    }
    $headers = 'From: ' . $from . "\r\n";
    if ($replyTo !== '') {
        $headers .= 'Reply-To: ' . nsm_mail_header_safe($replyTo) . "\r\n";
    }
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    return mail($to, $subject, $body, $headers);
}
