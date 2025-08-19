<?php
if($_GET["token"] != "fsd4io3py875dsfsdfsdfhoidsdlfgj2093tuyg0thjio")
{
  die("Du har ikke adgang");
}
set_time_limit ( 4000 );
ini_set('memory_limit', '128M');
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
//error_reporting(E_ALL);
include("sms/db/db.php");
$db = new Dbsqli();
$db->setKeepOpen();


$sql ="SELECT

shop_user.shop_id,
shop.name,
shop_user.expire_date,


COUNT(shop_user.username) as antal
FROM `shop_user`

inner join `order` on shop_user.username = `order`.user_username
inner join shop on shop_user.shop_id = shop.id




WHERE

shop_user.is_giftcertificate = 1 and

shop_user.`is_demo` = 0 and
shop_user.`blocked` = 0 and

shop_user.shop_id in (52,53,54,55,56,290,310,569,287,575)


GROUP BY shop_user.`expire_date`,shop_user.shop_id
order by shop_user.`expire_date`,shop_user.shop_id

" ;
//shop_user.shop_id in (52,53,54,55,56,290,310,569,287,575)

$rs = $db->get($sql);
//print_r($rs);
$total = 0;
$totaltotal = 0;
echo "<table>";
echo "<tr><th>Kort</th><th>Leveringsdato</th><th>Antal valgte kort</th><th>Total antal kort</th><th></th></tr>";
foreach($rs["data"] as $item){
    $totalCard =  getTotal($item["shop_id"],$item["expire_date"],$db);
    $total+= $item["antal"];
    $totaltotal+= $totalCard;
    $hjem = "hjemmelevering";
    if($item["expire_date"] != "2022-01-01") $hjem = "";
    echo "<tr><td>".utf8_decode($item["name"])."</td><td>".$item["expire_date"]."</td><td>".$item["antal"]."</td><td>".$totalCard."</td><td>".$hjem."</td></tr>";
}
echo   "<tr><td><b>Sum</b></td><td></td><td>".$total."</td><td>".$totaltotal."</td><td></td></tr>";
echo "</table>";

function getTotal($cardId,$expireData,$db)
{

    $sql ="select count(*) as antal FROM `shop_user` where shop_id = ".$cardId." and expire_date = '".$expireData."' and
    shop_user.is_giftcertificate = 1 and
    shop_user.`is_demo` = 0 and
    shop_user.`blocked` = 0

  ";
  $res = $db->get($sql);

  return $res["data"][0]["antal"];


}
?>
<!DOCTYPE HTML>

<html>

<head>
  <title>Untitled</title>
  <style>


td, th {
  border: 1px solid #ddd;
  padding: 8px;
}

tr:nth-child(even){background-color: #f2f2f2;}

tr:hover {background-color: #ddd;}

th {
  padding-top: 12px;
  padding-bottom: 12px;
  text-align: left;
  background-color: #4CAF50;
  color: white;
}
</style>
</head>

<body>

</body>

</html>








