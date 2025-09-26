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







$presents = $db->get("select * from present_1409 where pim_id = 0 and copy_of = 0 and show_to_saleperson_no = 1");

foreach ($presents["data"] as  $p){



    $sql =  "update present set show_to_saleperson_no = 1 where id=".$p["id"];
    $db->set($sql);

}




























/*
$presents = $db->get("SELECT * FROM `present` WHERE `active` = 1 AND `deleted` = 0 and `copy_of` = 0");
//print_R($presents);
$i=0;
foreach ($presents["data"] as $present){
    if($present["pt_price"] != null || $present["pt_price"] != ""){

        $prices =  json_decode($present["pt_price"]);


        if(is_numeric($prices->pris) == 1){

            if($present["price_group"] == null || $present["price_group"] == "" || $present["price_group"] == 0.00){
                $price_group = $prices->pris*1;
                $sql = "update present set price_group =".$price_group." where id=".$present["id"];
         //       $db->set($sql);
            }
        }

        if(is_numeric($prices->budget) == 1){
           if($present["price"] == null || $present["price"] == "" || $present["price"] == 0.00){
                $price = $prices->budget*1;
                $sql = "update present set price =".$price." where id=".$present["id"];
           //     $db->set($sql);

            }
        }
    }


}


//$rs = $db->get("select pt_price from present where id = 89301");
*/






