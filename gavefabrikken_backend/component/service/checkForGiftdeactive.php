<?php

include_once "../../includes/config.php";

set_time_limit ( 3000 );
ini_set('memory_limit', '128M');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include("sms/db/db.php");
$db = new Dbsqli();
$db->setKeepOpen();

$handle = fopen("atslette.txt", "r");
if ($handle) {
    while (($line = fgets($handle)) !== false) {
       echo $line;
    }

    fclose($handle);
} else {
    // error opening the file.
}









?>