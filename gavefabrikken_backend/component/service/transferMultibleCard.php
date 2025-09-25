<?php

include_once "../../includes/config.php";

set_time_limit ( 3000 );
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include("db.php");

 $i = 0;

$db = new Dbsqli();
$db->setKeepOpen();
// pickCardToMove(15244,$db);
$handle = fopen("data.csv", "r");
if ($handle) {
    while (($line = fgets($handle)) !== false) {

      $linePart =  explode(";",$line);
      $addrs =  explode(" ",$linePart[2]);

       $tlf = preg_replace('/\s+/', '', $linePart[7]);
      //$sql = "SELECT * FROM `company` WHERE `bill_to_address` LIKE '%".$addrs[0]."%' AND `ship_to_postal_code` LIKE '".$linePart[3]."' " ;
        $sql = "SELECT * FROM `company` WHERE `contact_phone` LIKE '".$tlf."' AND `ship_to_postal_code` LIKE '".$linePart[3]."' " ;
      $rs = $db->get($sql);
      // tjekker om firm eksistere
      if(sizeofgf($rs["data"]) > 0 ){
          //$cardId =  pickCardToMove(24354,$db);
           $target = $rs["data"][0]["id"];
          $sql = "select * from shop_user where company_id = ".$target;
           $rs2 = $db->get($sql);
           foreach($rs2["data"] as $item){
            echo $line.= ";".$item["username"] .";".$item["password"];
            echo "<br>";
           }
          //moveCard($cardId,$target,$db);
      } else {

        $formdata = [
            'name'=>$linePart[1],
            'bill_to_address'=>$linePart[2],
            'bill_to_address_2'=>"",
            'bill_to_postal_code'=>$linePart[3],
            'bill_to_city'=>$linePart[4],
            'cvr'=>"971171324",
            'ean'=>"",
            'ship_to_company'=>"",
            'ship_to_address'=>"",
            'ship_to_address_2'=>"",
            'ship_to_postal_code'=>"",
            'ship_to_city'=>"",
            'contact_name'=>$linePart[0],
            'contact_phone'=>$tlf,
            'contact_email'=>"no",
            'bill_to_country'=>"Norge"
          ];
          echo "mangler:".$line;
          echo "<br>";

          //  print_r($formdata);
          //createCompany($formdata,$db);

      }

    }
}

//  user_id: 801294
//company_id: 15244

//shop/moveShopUser
function pickCardToMove($company_id = 15244,$db){
     $sql = "SELECT * FROM `shop_user` WHERE `company_id` = ".$company_id." limit 0,1";
    $rs = $db->get($sql);
    return $rs["data"][0]["id"];
}
function moveCard($cardId,$target,$db){
  echo  $sql = "update `shop_user` set `company_id` = ".$target." where `id` = ". $cardId;
 // $rs = $db->set($sql);

}
