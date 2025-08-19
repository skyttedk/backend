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

$target = 7342;
$souce =  7311;

//copyPresentToShop($target,$souce,$db);

//copyPriceShopToShop($target,$souce,$db);



function copyPresentToShop($taget, $souce,$db){

    $sqlSouce = "SELECT copy_of FROM `present` WHERE `shop_id` = ".$souce." AND copy_of NOT IN (SELECT copy_of FROM `present` WHERE `shop_id` = ".$taget.")";
    $rsSouce = $db->get($sqlSouce);
    $sqlTarget = "select * from present where shop_id = ".$taget;
    $rsTarget = $db->get($sqlTarget);
    if(sizeof($rsTarget["data"]) > 0){
        echo "error";
        return;
    }
    foreach ($rsSouce["data"] as $souceData){
        run($souceData["copy_of"],$taget);
    }
}
function copyPriceShopToShop($taget, $souce,$db){
    $sqlSouce = "SELECT pt_price,copy_of  FROM `present` WHERE `shop_id` = ".$souce;
    $rsSouce = $db->get($sqlSouce);
    foreach ($rsSouce["data"] as $souceData){

    echo     $targetUpdate =  "UPDATE `present` set pt_price = '".$souceData["pt_price"]."' WHERE `copy_of` = ".$souceData["copy_of"]." AND `shop_id` =".$taget;
        $db->set($targetUpdate);
    }
}




function run($presentID,$targetShopID)
{

    $url = 'https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=present/createUnikPresent_v2';

// The data to be sent in the POST request
    $data = [
        'present_id' => $presentID,
        'shop_id' => $targetShopID
    ];

// Initialize cURL session
    $ch = curl_init($url);

// Set the cURL options
    curl_setopt($ch, CURLOPT_POST, true);                // Set POST method
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data)); // Set the POST fields
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);      // Return the response as a string

// Execute the POST request
    $response = curl_exec($ch);

// Check for errors
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    } else {
        // Display the response
        echo 'Response:' . $response;
    }

// Close the cURL session
    curl_close($ch);
}
?>




