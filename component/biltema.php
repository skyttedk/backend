<?php

 include("sms/db/db.php");
$db = new Dbsqli();
$db->setKeepOpen();

$file = fopen("biltema.csv","r");

while(! feof($file))
    {
        $lineEle = (explode(";",fgets($file)));
        $sql = "select * from user_attribute where shop_id = 1994 and attribute_id = 11148 and attribute_value ='".$lineEle[0]."' ";
     //echo "<br>";
       $rs = $db->get($sql);
       if(sizeofgf($rs["data"]) > 0 ){
        $shopuser_id = $rs["data"][0]["shopuser_id"];

       //   $insertSql = "update user_attribute set attribute_value = '".trimgf($lineEle[1])."'  where shopuser_id = ".$shopuser_id." and shop_id = 1994 and attribute_id = 12244";

          $db->set($insertSql);
       } else {
         echo $sql;
         echo "<br>";
         echo utf8_encode($lineEle[0]);
         echo "<br>";
       }


    }





fclose($file);

// email 11149

// 1994
// 12244

?>