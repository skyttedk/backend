<?php
ini_set('max_execution_time', 300); //300 seconds = 5 minutes
ini_set('memory_limit','2048M');
error_reporting(E_ALL);     

include("sms/db/db.php");


$outputArr = [];
$db = new Dbsqli();
$db->setKeepOpen();

$sql = "select * from user_attribute where shopuser_id in( SELECT shopuser_id FROM user_attribute WHERE attribute_value NOT LIKE '%.no' && attribute_value NOT LIKE '%.sv' && is_email = 1 && shopuser_id IN (SELECT shopuser_id FROM user_attribute WHERE attribute_id IN (SELECT id FROM shop_attribute WHERE name LIKE 'Gaveklubben tilmelding') && attribute_value LIKE 'ja'))  ";
$rs = $db->get($sql);
$shopuser = [];



foreach($rs["data"] as $key => $value){
if($value["attribute_value"] != ""){
      if($value["is_email"] == "1"){
            $shopuser[$value["shopuser_id"]]["mail"] = $value["attribute_value"];
     } else if($value["is_name"]  == "1"){
            $shopuser[$value["shopuser_id"]]["name"] = $value["attribute_value"];
     } else if($value["is_username"] == "0" && $value["is_password"] == "0" && $value["is_email"] == "0" && $value["is_name"] == "0" &&  $value["attribute_value"] != "ja"){
        $shopuser[$value["shopuser_id"]]["tlf"] = $value["attribute_value"];
     } if( $value["attribute_value"] == "ja"){
        $shopuser[$value["shopuser_id"]]["tjeck"] = $value["attribute_value"];
        $shopuser[$value["shopuser_id"]]["id"] = $value["shopuser_id"];
     }
}



}
//print_R($rs);
foreach ($shopuser as $ele)
  {
        $line = $ele["id"].";".$ele["name"].";".$ele["mail"].";".$ele["tlf"].";".$ele["tjeck"];
        $tele = str_replace("+45","",$ele["tlf"]);
        $tele = str_replace("0045","",$tele);

        $tele = preg_replace('/\s+/', '', $tele);
        if(strlen($tele) == 8 && $tele != ""){
           echo  $sqlSet = "insert into klubben (shopuser_id,name,mail,telefon,tjeck,no_good) value(".$ele["id"].",'".$ele["name"]."','".$ele["mail"]."','".$tele."','".$ele["tjeck"]."',0)";
        //    $db->set($sqlSet);
        } else {
      //      $sqlSet = "insert into klubben (shopuser_id,name,mail,telefon,tjeck,no_good,active) value(".$ele["id"].",'".$ele["name"]."','".$ele["mail"]."','".$ele["tlf"]."','".$ele["tjeck"]."',1,0)";
       //     $db->set($sqlSet);
        }

  }

echo "fine";


?>