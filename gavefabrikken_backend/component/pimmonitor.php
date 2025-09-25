<?php
if($_GET["token"] != "fdslahsiuofhiuweafuaflhal" ) {
    die("no no");
};

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// https://system.gavefabrikken.dk/gavefabrikken_backend/component/pimmonitor.php?token=fdslahsiuofhiuweafuaflhal


include("sms/db/db.php");
$db = new Dbsqli();
$db->setKeepOpen();



$monitor = new monitor;
$magentoData = $monitor->getOrderFromMagento();
$navData = $monitor->getOrderFromNAV();
$monitor->processData($magentoData,$navData);




class monitor
{
    public function processData($magento,$nav){
        $magentoList = [];
        $navList = [];

        $magento =  json_decode($magento);
        $nav =  json_decode($nav);
      //  print_r($nav);
        foreach ($nav as $mitem){
            array_push($navList, $mitem->MagentoRealID);
        }



        foreach ($magento->items as $mdata){
            if (in_array($mdata->entity_id, $navList)) {

            } else {
               print_R($mdata);
            }

      /*
            print_r($mdata);
        //    echo $mdata["base_currency_code"];
            echo $mdata->store_name;
            echo $mdata->store_name;
            created_at
            entity_id
store_id
status

      */


        }
    }

    public function getOrderFromNAV(){

        $ch = curl_init();

// Set cURL options
        curl_setopt($ch, CURLOPT_URL, "https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=extservice/magentoorders&token=dsf4Df3fdsVs367yhfDCvfd-323DF212fvxzdFhjrwyhjdsdfw");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return the transfer as a string
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Follow redirects
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // Do not verify the certificate's name against host
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Do not verify the peer's SSL certificate

// Execute cURL session
        $response = curl_exec($ch);

// Check for errors
        if (curl_errno($ch)) {
            echo 'cURL error: ' . curl_error($ch);
        } else {
            // Process the response as needed

        }


        return $response;
    }
    public function getOrderFromMagento(){



        $ch = curl_init();

// Sæt cURL options
        //eq
        curl_setopt($ch, CURLOPT_URL, "https://www.shopgavefabrikken.dk/rest/default/V1/orders?searchCriteria[pageSize]=10&searchCriteria[filterGroups][0][filters][0][field]=created_at&searchCriteria[filterGroups][0][filters][0][value]=2024-02-20&searchCriteria[filterGroups][0][filters][0][conditionType]=gt");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_MAXREDIRS, 100);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "Authorization: Bearer u0tr04d9ko017d66v5e4ca3camehwvmt"
        ));


// Udfører cURL sessionen og gemmer svaret
        $response = curl_exec($ch);

// Tjekker for fejl
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }

// Lukker cURL sessionen
        curl_close($ch);

// Udskriver svaret
        return $response;


    }



}
?>
<!DOCTYPE html>

<html>

<head>
    <title>Monitor</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
</head>

<body>

<?php

?>

</body>
</html>



