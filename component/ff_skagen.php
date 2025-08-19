<?php
set_time_limit(4000);
ini_set('memory_limit', '128M');
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
//error_reporting(E_ALL);
include "sms/db/db.php";

$ff = new ffskagen();
$ff->fixproblem();

// 12253 levereingsadresse

// 10987  email

class ffskagen {
    private $dbConn;

    public function __construct() {
        $this->dbConn = new Dbsqli();
        $this->dbConn->setKeepOpen();

    }
    public function fixproblem() {
        $file = fopen("ffskagen.csv", "r");

        while (!feof($file)) {
            $line = explode(";", fgets($file));

            $sql = "select *  from user_attribute where shop_id = 1973 and attribute_id = 10986 and attribute_value like ('%" . utf8_encode($line[0]) . "%')";
            $rs = $this->dbConn->get($sql);
            if (sizeofgf($rs["data"]) > 0) {
                $shop_user = $rs["data"][0]["shopuser_id"];
                $sql_inset = "update user_attribute set attribute_value = '" . utf8_encode($line[2]) . "' where shop_id = 1973 and attribute_id = 12253 and shopuser_id = " . $shop_user;
                $this->dbConn->set($sql_inset);
                $sql_inset2 = "update user_attribute set attribute_value = '" . $line[3] . "' where shop_id = 1973 and attribute_id = 12254 and shopuser_id = " . $shop_user;
                $this->dbConn->set($sql_inset2);
            } else {
                $sql2 = "select *  from user_attribute where shop_id = 1973 and attribute_id = 10987 and attribute_value like ('%" . utf8_encode($line[1]) . "%')";
                $rs2 = $this->dbConn->get($sql2);
                if (sizeofgf($rs2["data"]) > 0) {
                    $shop_user = $rs2["data"][0]["shopuser_id"];
                    $sql_inset = "update user_attribute set attribute_value = '" . utf8_encode($line[2]) . "' where shop_id = 1973 and attribute_id = 12253 and shopuser_id = " . $shop_user;
                    $this->dbConn->set($sql_inset);
                    $sql_inset2 = "update user_attribute set attribute_value = '" . $line[3] . "' where shop_id = 1973 and attribute_id = 12254 and shopuser_id = " . $shop_user;
                    $this->dbConn->set($sql_inset2);
                } else {
                    echo $line[0] . ";" . $line[1] . ";" . utf8_encode($line[2]) . ";" . $line[3];
                    echo "<br>";
                }
            }

        }

        fclose($file);

    }

}
?>