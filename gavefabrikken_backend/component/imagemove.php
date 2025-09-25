<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


ini_set('memory_limit','2048M');
set_time_limit(0);

include("sms/db/db.php");



$unsuList = [];


$db = new Dbsqli();
$db->setKeepOpen();
$sql = "SELECT distinct(media_path) FROM gavefabrikken2023.`present_media` WHERE `present_id` in ( SELECT id FROM gavefabrikken2023.`present` WHERE shop_id in (SELECT id FROM gavefabrikken2023.`shop` ) )";
$rs =  $db->get($sql);


$i = 0;
foreach($rs["data"] as $img){
    echo $filename = $img["media_path"].".jpg";
    echo "<br>";
    $i++;
    move($filename);


}
echo $i;
//move("ZFMV7qMHj8gJCTW1UNKWQlmdAs6G4W.jpg");


function move($fileToFind){
    $parentDirectory = dirname(__DIR__);
    $directory = $parentDirectory.'/views/media/user/';

// Specify the file you want to find


// Specify the directory where you want to move the file
    $destinationDirectory = $parentDirectory.'/views/media/moveimg/';

// Specify the path of the source file
    $sourceFile = $directory.$fileToFind;



// Specify the new filename (optional, if you want to rename the file while copying)
    $newFileName = $fileToFind;

// Build the destination path including the new filename if provided
     $destinationPath = $destinationDirectory . ($newFileName ?? basename($sourceFile));

// Perform the copy operation
    if (copy($sourceFile, $destinationPath)) {
        echo "File copied successfully.<br>";
    } else {
        echo "Failed to copy file.<br>";
    }


}

// SELECT * FROM `present` WHERE shop_id in (SELECT id  FROM `shop` WHERE `localisation` = 5)