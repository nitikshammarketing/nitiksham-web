<?php
$root=realpath(__DIR__.'/../public_html');
$path=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH)?:'/';
if(str_contains($path,'..')){http_response_code(404);exit;}
$file=realpath($root.urldecode($path));
if($file&&str_starts_with($file,$root.DIRECTORY_SEPARATOR)&&is_file($file))return false;
if($path==='/api'||str_starts_with($path,'/api/')){require $root.'/api/index.php';return true;}
if($path==='/login'||$path==='/login/'){require $root.'/login/index.php';return true;}
if($path==='/studio'||$path==='/studio/'){require $root.'/studio/index.php';return true;}
if($path==='/')return false;
$file=$root.rtrim($path,'/').'/index.html';
if(is_file($file)){header('Content-Type: text/html; charset=utf-8');readfile($file);return true;}
return false;