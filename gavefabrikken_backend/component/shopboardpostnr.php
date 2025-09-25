<?php


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


ini_set('memory_limit', '2048M');
set_time_limit(0);

include("sms/db/db.php");


$db = new Dbsqli();
$db->setKeepOpen();
// Sti til CSV-filen
$filsti = 'datapost.csv';
//  component/shopboardpostnr.php
// Åbn filen
$handle = fopen($filsti, "r");
if ($handle) {
    while (($line = fgets($handle)) !== false) {
        $array = explode(";", $line);

        $sql = "SELECT ship_to_postal_code FROM `company` WHERE id in (SELECT company_id FROM `company_shop` WHERE `shop_id` = ".$array[2].")";
       $rs = $db->get($sql);
       
        array_unshift($array, $rs["data"][0]["ship_to_postal_code"]);

        echo implode(";", $array)."<br>";


    }

    fclose($handle);
}
?>