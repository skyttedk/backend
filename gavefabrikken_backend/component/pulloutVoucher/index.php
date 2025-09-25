<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include "../sms/db/db.php";

if(isset($_GET["amount"])){
    $voucher = new voucher();
    $voucher->getAvailable($_GET["amount"]);

} else {
    echo "Nothing happened";
}

class voucher
{
    private $dbConn;
    public function __construct() {
        $this->dbConn = new Dbsqli();
        $this->dbConn->setKeepOpen();
    }

    public function getAvailable($amount)
    {
        $i = 0;
        $sql = "SELECT `voucher` FROM `voucher` WHERE `company_id` = 0 AND `is_send` = 0 LIMIT ".$amount;
        $voucherListRs = $this->dbConn->get($sql);
        foreach($voucherListRs["data"] as $voucher){

            $sql = "update `voucher` set company_id= -1,is_send=-1 where voucher = '".$voucher["voucher"]."'" ;
            $this->dbConn->set($sql);
            $sql = "select is_send  from voucher where voucher= '".$voucher["voucher"]."'";
            $checkRs = $this->dbConn->get($sql);
            if(sizeof($checkRs) > 0){
                $i++;
                echo $voucher["voucher"]."<br>";
            } else {
                echo "problem: ".$voucher["voucher"]."<br>";
            }
        }
        echo "<h3>total: ".$i."</h3>";
    }
}







?>