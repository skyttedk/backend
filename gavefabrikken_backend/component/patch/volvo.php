<?php

set_time_limit(3000);
ini_set('memory_limit', '128M');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include "../sms/db/db.php";

$volvo = new volvo();

$volvo->getUserAtt();
class volvo {
  // email 11643
  // 11982 placering     11983
    function __construct() {
        $this->db = new Dbsqli();
        $this->db->setKeepOpen();
    }

    function getUserAtt() {
        $sql = " SELECT * FROM `user_attribute` where shopuser_id in
        (
            SELECT shopuser_id FROM `user_attribute` where shop_id = 2053 and
            attribute_value not like ('%@%')
            and attribute_id =  11643
        )
        and attribute_id =  11982
        and  attribute_value like ('%@%')

         " ;

        $rs = $this->db->get($sql);

        foreach($rs["data"] as $ele){


            try{
                echo $ele["attribute_value"]."--".$ele["shopuser_id"].  "<br>";

                echo $sql = "update user_attribute set attribute_value = '".$ele["attribute_value"]."'  where attribute_id =  11643 and shopuser_id = ".$ele["shopuser_id"];
                $this->db->set($sql);

            } catch (Exception $e) {

            }

        }

    }

}

?>

