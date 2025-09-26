<?php
ini_set('max_execution_time', 400); //300 seconds = 5 minutes
ini_set('memory_limit','2048M');


include("sms/db/db.php");
echo "unsu";
$unsuList = [];


$db = new Dbsqli();
$db->setKeepOpen();
/*
290 1918
310
575
2395
4662
4668
*/


$sql = "select DISTINCT( attribute_value),shopuser_id from user_attribute where attribute_id in (582,767,761,763,765,16646,28556) and shopuser_id in (
SELECT shopuser_id FROM `user_attribute`
inner join shop_attribute on
user_attribute.attribute_id = shop_attribute.id
WHERE `attribute_value` LIKE 'ja' and user_attribute.shop_id
in (52,53,54,55,56,2395,9321,4668) and shop_attribute.name =  'Gaveklubben tilmelding')";
$rs = $db->get($sql);


$i = 0;
foreach($rs["data"] as $key=>$val){
  $tele = trimgf($val["attribute_value"]);

    if(strlen($tele) == 8){
        echo $tele."----".$val["shopuser_id"]."<br>";
    }

}


/*
foreach($rs["data"] as $key=>$val){
    if(strlen($val["tlf"]) > 6){
     $sqlTjeck = "select tlf from sms_user where tlf like '%".$val["tlf"]."%'";
        $tjeckRs = $db->get($sqlTjeck);
        if(sizeofgf($tjeckRs["data"]) >0){
            echo $updata = "update sms_user set active = 0 where tlf like '%".$val["tlf"]."%'";
        $db->set($updata);
        $i++;
    }
}
}
*/
echo $i;





?>