<?php
set_time_limit ( 3000 );
ini_set('memory_limit', '128M');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include("sms/db/db.php");
$db = new Dbsqli();
$db->setKeepOpen();


$shopID = $_POST["shopID"];
$shopBoardID = $_POST["shopBoardID"];

//$sql = "updata shop_board set fk_shop = ".$shopID." where id = ".$shopBoardID;
//$db->set($sql);
//echo "good";




$sql = "select shop_navn from shop_board where active = 1";
$rs = $db->get($sql);
echo sizeofgf($rs);
if(sizeofgf($rs) > 0){
$total = 0;
$hit = 0;
foreach($rs["data"] as $key=>$val){
   $total++;
   echo $val["shop_navn"];
   $sql1 = "select id,name from shop where name like '%".$val["shop_navn"]."%'";
   $rsShop = $db->get($sql1);

if( sizeofgf($rsShop["data"]) > 0){
   print_r($rsShop);
   echo sizeofgf($rsShop["data"]) ;
      echo "-------------------------------- <br><br><br><br><br><br>";
  $hit++;
}

}
}
echo "total: ".$total."<br><br>";
echo "hit med kun 2: ".$hit;




?>