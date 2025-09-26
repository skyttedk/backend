<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require "routing.php";

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

define("routeUrl",$_SERVER['DOCUMENT_ROOT'] . "/gavefabrikken_backend/module/presentsCms/api/controllers/");

$url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// handling api to call
$routing = new routing($url);
try {
    $res = $routing->run();
    $res = array(
            'status' => 1,
            'data' => $res );
    echo json_encode($res);
}
catch (exception $e) {
    $res =  array(
            'status' => 0,
            'msg' => $e->getMessage() );
    echo json_encode($res);
}

/*
Options +FollowSymLinks -MultiViews
RewriteEngine On

RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond $1 !^(index\.php)
RewriteRule ^([a-zA-Z0-9_]+)?/?([a-zA-Z0-9_]+)?/?([a-zA-Z0-9_]+)?/?([a-zA-Z0-9_]+)?/?$ index.php?company=$1 [NC,L]

*/



?>