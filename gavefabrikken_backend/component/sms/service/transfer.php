<?php
// har udkomenteret linje 33 og 51 der genner

include("../db/db.php");
echo "transfer <br />";
$db = new Dbsqli();
$db->setKeepOpen();
$shopuserIdList = [];

$shopID = "56";   // vær opmærksom på denne her


$sql = " SELECT shopuser_id  FROM `user_attribute`
inner JOIN company
on user_attribute.company_id = company.id
WHERE `shopuser_id` in (select `shopuser_id` from user_attribute where `attribute_value` = 'ja') and is_email = '1' and shop_id = '".$shopID."' order by company.name ";

$sql = "select `shopuser_id` from user_attribute where `attribute_value` = 'ja' and shop_id = '".$shopID."'   ";
$rs = $db->get($sql);

foreach($rs["data"] as $userid)
{
     array_push($shopuserIdList,$userid["shopuser_id"]);
}
// get user data
$i=0;
foreach($shopuserIdList as $item)
{
//  $sql2 = "SELECT attribute_value from `user_attribute` where attribute_id = 582  and `shopuser_id`=".$item;     // 52
//  $sql2 = "SELECT attribute_value from `user_attribute` where attribute_id = 767  and `shopuser_id`=".$item;     // 53
//  $sql2 = "SELECT attribute_value from `user_attribute` where attribute_id = 761  and `shopuser_id`=".$item;     // 54
//   $sql2 = "SELECT attribute_value from `user_attribute` where attribute_id = 763  and `shopuser_id`=".$item;     // 55
//   $sql2 = "SELECT attribute_value from `user_attribute` where attribute_id = 765  and `shopuser_id`=".$item;     // 56

  $rs2 = $db->get($sql2);
  echo "<br />";
  $sql3 = "SELECT attribute_value from `user_attribute` where is_name = 1  and `shopuser_id`=".$item;
  $rs3 = $db->get($sql3);
  //print_r($rs2);
  //print_r($rs3);
   $i++;
  $tlf =  $rs2["data"]["0"]["attribute_value"];
  $tlf = preg_replace('/\s+/','',$tlf);
  $tlf = str_replace("+45","",$tlf);
  $navn = $rs3["data"]["0"]["attribute_value"];
  $pieces = explode(" ", $navn);
  $fornavn = $pieces[0];
  if(!strpos($tlf,"@")){
    if($tlf != ""){
        echo $insetSql = "insert into sms_user (`shopuser_id`, `tlf`, `fornavn`, `efternavn`, `grp_id`) values('".$item."','".$tlf."','".$fornavn."','','1')";
      // $db->set($insetSql);
    }
  }


}

echo "end-".$i;



?>