<?php

include("sms/db/db.php");
$db = new Dbsqli();
$db->setKeepOpen();


$i = 0;

if ($file = fopen("datadata.txt", "r")) {
    while(!feof($file)) {
        $line = fgets($file);
        $line = trimgf($line);
            $i++;
            $sql = "select * from klubben where active=1 and no_good = 0 and  telefon ='".$line."'";
            $rs = $db->get($sql);

            $navn = explode(" ",$rs["data"][0]["name"]);
            $tele =  $rs["data"][0]["telefon"];
            $shopuser_id = $rs["data"][0]["shopuser_id"];
            $sql2 = "insert into sms_user (shopuser_id,tlf,fornavn,grp_id) value('".$shopuser_id."','".$tele."','".$navn[0]."',9)" ;
         //   $db->set($sql2);
    }
    fclose($file);
}
echo $i;

?>