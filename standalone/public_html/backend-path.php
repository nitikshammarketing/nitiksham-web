<?php
/** Locate private backend files outside the cPanel document root.
 * A cPanel account can serve production and preview from a nested public_html folder.
 */
declare(strict_types=1);

function nsm_private_root(): string {
    $webroot = realpath(__DIR__) ?: __DIR__;
    if (preg_match('~^(/home/[^/]+)(?:/|$)~', $webroot, $matches)) {
        return $matches[1] . '/private';
    }
    return dirname(__DIR__) . '/private';
}
