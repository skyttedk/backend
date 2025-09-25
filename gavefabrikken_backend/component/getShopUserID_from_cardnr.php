<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


ini_set('memory_limit','2048M');
set_time_limit(0);

include("sms/db/db.php");

$List = [];


$db = new Dbsqli();
$db->setKeepOpen();


$fileData = function() {
    $file = fopen(__DIR__ . '/list1.csv', 'r');

    if (!$file)
        die('file does not exist or cannot be opened');

    while (($line = fgets($file)) !== false) {
        yield $line;
    }
    fclose($file);
};

 foreach ($fileData() as $line) {
      getUserID($line,$db);

 }


function getUserID($card,$db)
{
    $part = explode(";",$card );
    $cardID = $part[0];
    $sql = "select id from shop_user where username = '".$cardID."'";
    $rs = $db->get($sql);
    echo $rs["data"][0]["id"].";".$part[1].";".$part[0];
    echo "<br>";
}
