<?php
include("sms/db/db.php");

$unsuList = [];


$db = new Dbsqli();
$db->setKeepOpen();



$sql = "select tlf from sms_unsubscribe having length(tlf) > 7 " ;
$rs = $db->get($sql);

foreach($rs["data"] as $tlf){
    $sqlShopUser = "SELECT `shopuser_id` FROM `user_attribute` WHERE `attribute_value` like '".$tlf["tlf"]."'";
    $rsShopUser = $db->get($sqlShopUser);
    if(sizeofgf($rsShopUser["data"]) > 0){
        foreach($rsShopUser["data"] as $su){
          $sqlMail = "SELECT `attribute_value` from user_attribute WHERE `shopuser_id` = ".$su["shopuser_id"]." and is_email = 1" ;
          $rsMail = $db->get($sqlMail);
            if(sizeofgf($rsMail["data"]) > 0){
                foreach($rsMail["data"] as $mail){
                    echo $mail["attribute_value"]."<br>";

                }



            }
        }
    }

}

foreach($rs["data"] as $tlf){
    $sqlShopUser = "SELECT `shopuser_id` FROM `user_attribute` WHERE `attribute_value` like '45".$tlf["tlf"]."'";
    $rsShopUser = $db->get($sqlShopUser);
    if(sizeofgf($rsShopUser["data"]) > 0){
        foreach($rsShopUser["data"] as $su){
          $sqlMail = "SELECT `attribute_value` from user_attribute WHERE `shopuser_id` = ".$su["shopuser_id"]." and is_email = 1" ;
          $rsMail = $db->get($sqlMail);
            if(sizeofgf($rsMail["data"]) > 0){
                foreach($rsMail["data"] as $mail){
                    echo $mail["attribute_value"]."<br>";

                }



            }
        }
    }

}

 foreach($rs["data"] as $tlf){
    $sqlShopUser = "SELECT `shopuser_id` FROM `user_attribute` WHERE `attribute_value` like '0045".$tlf["tlf"]."'";
    $rsShopUser = $db->get($sqlShopUser);
    if(sizeofgf($rsShopUser["data"]) > 0){
        foreach($rsShopUser["data"] as $su){
          $sqlMail = "SELECT `attribute_value` from user_attribute WHERE `shopuser_id` = ".$su["shopuser_id"]." and is_email = 1" ;
          $rsMail = $db->get($sqlMail);
            if(sizeofgf($rsMail["data"]) > 0){
                foreach($rsMail["data"] as $mail){
                    echo $mail["attribute_value"]."<br>";

                }



            }
        }
    }

}
foreach($rs["data"] as $tlf){
    $sqlShopUser = "SELECT `shopuser_id` FROM `user_attribute` WHERE `attribute_value` like '+45".$tlf["tlf"]."'";
    $rsShopUser = $db->get($sqlShopUser);
    if(sizeofgf($rsShopUser["data"]) > 0){
        foreach($rsShopUser["data"] as $su){
          $sqlMail = "SELECT `attribute_value` from user_attribute WHERE `shopuser_id` = ".$su["shopuser_id"]." and is_email = 1" ;
          $rsMail = $db->get($sqlMail);
            if(sizeofgf($rsMail["data"]) > 0){
                foreach($rsMail["data"] as $mail){
                    echo $mail["attribute_value"]."<br>";

                }



            }
        }
    }

}
?>