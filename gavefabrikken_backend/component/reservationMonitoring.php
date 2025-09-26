<?php
ini_set('max_execution_time', 300); //300 seconds = 5 minutes
ini_set('memory_limit','2048M');
include ("frem.php");
// https://system.gavefabrikken.dk/gavefabrikken_backend/component/reservationMonitoring.php?token=sdfsdalkkljdsflj4893478tr8gswfuf6478tf38qf&action=queue
// https://system.gavefabrikken.dk/gavefabrikken_backend/component/reservationMonitoring.php?token=sdfsdalkkljdsflj4893478tr8gswfuf6478tf38qf&action=newjob
if(!isset($_GET["token"] ) || !isset($_GET["action"] )){
    die("Ingen adgang");
}

if($_GET["token"] != "sdfsdalkkljdsflj4893478tr8gswfuf6478tf38qf"){
    die("Ingen adgang");
}

if($_GET["action"] == "newjob"){
    $job = new job;
    $job->createJob();
    echo "1";
}
if($_GET["action"] == "queue"){
    $queue = new queue;
    $queue->run();

}
if($_GET["action"] == "newjobshop"){
    $queue = new queue;
    $queue->createQueueShopItems();
}
if($_GET["action"] == "queueShop"){
    $queue = new queue;
    $queue->queueShopItems();
}


if($_GET["action"] == "single"){
    $data = array();
    $itemNumber = new ItemNumber;
    $lines = file('signe1.csv');
    $count = 0;
    foreach($lines as $line) {
        $lineData =  explode(";", $line);
        $itemNO = $lineData[0];
        $shopsStr = $lineData[1];
        $shops =  explode(",", $shopsStr);
        foreach ($shops as $shop){
           $shopID =   trim($shop);
           $res = $itemNumber->searchItemNr($itemNO,$shopID );
          // print_R($res);
           $res = $res["data"][0];
           $data[] = array(
               $itemNO,
             $res["model_present_no"],
             $res["shop_name"],
             $res["model_name"],
             $res["model_no"],
             $res["antal"],
             $res["forcast"]["forecast"]
           );
        }



    }
    $myfile = fopen("duff_part4.txt", "w") or die("Unable to open file!");
   // print_r($data);
    foreach ( $data as $item) {
        $txt = implode(";", $item)."\n";
        fwrite($myfile, $txt);
    }
    fclose($myfile);
    echo "end";
    /*
    $itemNumber = new ItemNumber;
    $itemNr = $_POST["itemNr"];
    $shopID = $_POST["shopID"];
    $res = $itemNumber->searchItemNr($itemNr,$shopID );
    print_R($res);
    //$queue->run();
*/
}

