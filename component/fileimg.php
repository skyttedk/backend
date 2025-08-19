<?php
set_time_limit ( 4000 );
ini_set('memory_limit', '128M');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
include("sms/db/db.php");
$db = new Dbsqli();
$db->setKeepOpen();

$file = fopen('shops_ariveres.csv', 'r');
$shopToClose = [];
while (($line = fgetcsv($file)) !== FALSE) {
    //$line er en array af felter på den nuværende linje
    $lineArr = explode(";",$line[0]);
    if($lineArr[7] == "1" ){
        $shopToClose[] =  $lineArr[0];
    }


}

fclose($file);



/*
$directoryPath = '../../gavefabrikken_backend/views/media/user'; // Angiv stien til mappen du vil søge igennem
$files = scandir($directoryPath);

$fileCount = 0;
foreach ($files as $file) {
    if (is_file($directoryPath . '/' . $file)) { // Tjekker om det er en fil
        $fileCount++;
    }
}

echo "Der er $fileCount filer i mappen.";
*/

/*
$directoryPath =  '../../gavefabrikken_backend/views/media/user'; //'https://system.gavefabrikken.dk/gavefabrikken_backend/views/media/user/00AAJBc6EKC8otlc1K15NsvUDFRxBX.jpg';
$files = array_slice(scandir($directoryPath), 2); // Ignorerer . og ..

$count = 0;
foreach ($files as $file) {
    if (is_file($directoryPath . '/' . $file)) { // Tjekker om det er en fil
        echo $file . '<br>'; // Udskriver filnavnet

        $count++;
        if ($count == 10) { // Stopper efter de første 10 filer
            break;
        }
    }
}
echo "<br>end";
*/


echo $sql = "SELECT DISTINCT `media_path` FROM `present_media` WHERE`present_id` in( SELECT DISTINCT (id) FROM `present` WHERE `deleted` = 0 )";
$rs = $db->get($sql);


foreach ($rs["data"] as $file){
    $source = '../../gavefabrikken_backend/views/media/small/'.$file["media_path"].'_small.jpg';
    $destination = '../../gavefabrikken_backend/views/media/small_2022/'.$file["media_path"].'_small.jpg';

    if (!copy($source, $destination)) {
        echo "failed to copy $source...<br>";
    } else {

    }

}


/*

echo $sql = " SELECT DISTINCT (pt_img_small) FROM `present` WHERE `deleted` = 0";
$rs = $db->get($sql);
//print_R($rs);



foreach ($rs["data"] as $file){
//    $source = '../../fjui4uig8s8893478/'.$file["pt_img_small"];
//    $destination = '../../fjui4uig8s8893478_2022/'.$file["pt_img_small"];

    if (!copy($source, $destination)) {
        echo "failed to copy $source...<br>";
    } else {

    }

}
*/















/*
$sql ="SELECT
    $rs = $db->get($sql);
*/