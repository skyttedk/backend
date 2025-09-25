<?php
ini_set('max_execution_time', 300); //300 seconds = 5 minutes
ini_set('memory_limit','2048M');
ini_set('display_errors', 1);
error_reporting(E_ALL);
include("sms/db/db.php");
$db = new Dbsqli();
$db->setKeepOpen();
if ($file = fopen("1234.csv", "r")) {
    while(!feof($file)) {
        $line = fgets($file);
        $line = trimgf($line);

        $sql1 = "select * from gaveklubben where  email like '".$line."'";
        $rsShop = $db->get($sql1);
        foreach ($rsShop["data"] as $rs){
            if($rs["mobil"] != "" and $rs["mobil"] != 0){
                echo "'".$rs["mobil"]."',<br>";
            }

        }

    }
    fclose($file);

}
echo "done";