<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include("sms/db/db.php");
$db = new Dbsqli();
$db->setKeepOpen();

/*
$file_handle = fopen('listegaver.txt', 'r');
function get_all_lines($file_handle) {
    while (!feof($file_handle)) {
        yield fgets($file_handle);
    }
}
$count = 0;
foreach (get_all_lines($file_handle) as $id) {

    $sql = "SELECT * FROM `present_description` WHERE `present_id` = ".$id." and language_id = 5";

    $rs = $db->get($sql);

    $shortDescription = $rs["data"][0]["short_description"];
    $longDescription = $rs["data"][0]["long_description"];


    $longDescription =  base64_decode($longDescription);
    $shortDescription = base64_decode($shortDescription);
    $phrase  = $longDescription;
    $target = array("<ul>", "</ul>", "<li>","</li>","</strong></p>","");
    $replace   = array("", "</p>", "","<br/>","</strong><br>");

    $longDescription = str_replace($target, $replace, $phrase);
    $longDescription = base64_encode($shortDescription.$longDescription);

    $sql = "update `present_description` set short_description = '', long_description = '".$longDescription."'   WHERE `present_id` = ".$id." and language_id = 5";
    $rs = $db->set($sql);

}
fclose($file_handle);
echo "done";
*/


 $sql = "SELECT id  FROM `present` WHERE `copy_of` = 0 AND `active` = 1 AND `deleted` = 0 and `pt_layout` > 0";

$rs = $db->get($sql);
foreach ($rs["data"] as $item ){
    echo $sql2 = "update present set pt_layout =  4 where id=".$item["id"];
    $db->set($sql2);
}



?>