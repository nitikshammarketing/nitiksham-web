<?php
if(PHP_SAPI!=='cli'){http_response_code(403);exit;}
fwrite(STDERR,"Enter a new Studio password: ");
if(function_exists('shell_exec')&&stripos(PHP_OS,'WIN')!==0)@shell_exec('stty -echo');
$p=trim((string)fgets(STDIN));
if(function_exists('shell_exec')&&stripos(PHP_OS,'WIN')!==0)@shell_exec('stty echo');
fwrite(STDERR,"\n");
if(strlen($p)<14){fwrite(STDERR,"Password must contain at least 14 characters.\n");exit(1);}
echo password_hash($p,PASSWORD_DEFAULT)."\n";