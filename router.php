<?php

$uri   = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$root  = __DIR__;
$path  = rtrim($root . str_replace('/', DIRECTORY_SEPARATOR, $uri), DIRECTORY_SEPARATOR);

if (is_dir($path)) {
    $index = $path . DIRECTORY_SEPARATOR . 'index.php';
    if (file_exists($index)) {
        chdir($path);
        define('ROUTER_DOCROOT', $root);
        require $index;
        exit;
    }
    return false;
}

if (is_file($path)) {
    return false;
}

return false;
