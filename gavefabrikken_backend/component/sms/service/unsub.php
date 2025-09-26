<?php
include("../db/db.php");

$db = new Dbsqli();
$db->setKeepOpen();
   $lines = file("unsub.txt");
        foreach ($lines as $line_num => $line) {
            $sql = "select shopuser_id from user_attribute where attribute_value like '".trimgf($line)."'   ";
            $rs = $db->get($sql);
            echo trimgf($line)."<hr />";
            foreach($rs["data"] as $element){
                echo $sql2 = "update sms_user set active = 0 where shopuser_id ='".$element["shopuser_id"]."'   ";
                echo "<br />";
                $db->set($sql2);
                echo $element["shopuser_id"]."<br />";

            }
            echo "<hr />";
        }
        echo "<script>alert('asdfasd')</script>"

?>