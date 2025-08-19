<?php
ini_set('max_execution_time', 300); //300 seconds = 5 minutes
ini_set('memory_limit','2048M');
ini_set('display_errors', 1);
error_reporting(E_ALL);
include("sms/db/db.php");



$gg = new gdpr;
$userlist = $gg->loadShopuserAndEmail();

//$shopuser =  $userlist["data"][0]["shopuser_id"];
$gg->handler($userlist["data"]);

//$unsub = new unsub;
//$unsub->readData();

class unsub
{
    private $db;
    public function __construct()
    {
        $this->db = new Dbsqli();
        $this->db->setKeepOpen();
    }
    public function readData(){
        if ($file = fopen("datadata.txt", "r")) {
            while(!feof($file)) {
                $line = fgets($file);
                echo $line = trimgf($line);
                echo "<br>";
                $this->findAndDelete($line);
            }
            fclose($file);

        }
        echo "done";
    }
    private function findAndDelete($tlf){
        $sql = "SELECT * FROM `gaveklubben` WHERE `mobil` LIKE '$tlf'";
        $res = $this->db->get($sql);
        if(sizeof($res["data"]) > 0  ){
            if( $tlf != 0 || $tlf != "0") {
                echo $unsubsql = "update `gaveklubben` set email = '###', mobil = 0 WHERE `mobil` LIKE '$tlf'";
                echo "<br>";
                $this->db->set($unsubsql);
            }
        }
    }
}





class gdpr
{
    private $db;
    private $dbname = "gavefabrikken2023";
    private $year = "2023";
    public function __construct()
    {
        $this->db = new Dbsqli();
        $this->db->setKeepOpen();
    }
    public function handler($data){
        foreach ($data as $item){
            $shopuserID = $item["shopuser_id"];
            $mail = $item["attribute_value"];
            $mobil = "";
            $orderDato = "";
            // telenr
            $tlfRes = $this->getTlf($shopuserID);
            if(sizeof($tlfRes["data"]) > 0){
                $mobil = $tlfRes["data"][0]["attribute_value"];
            }
            // order dato
            $orderDatoRes = $this->getOrderDate($shopuserID);
            if(sizeof($orderDatoRes["data"]) > 0){
                $orderDato = $orderDatoRes["data"][0]["order_timestamp"];
            }

         //   $orderDato = "NULL";
            //echo $shopuserID."-".$mail."-".$mobil."-".$orderDato;

         //   $originalString = "+45 1234 5678";

            // Fjern alle mellemrum
            $mobil = str_replace(' ', '', $mobil);

            // Fjern præfiks "0045" eller "+45" hvis de er forrest i strengen
            $mobil = preg_replace('/^0045|^\\+45/', '', $mobil);

            // Tjek om den endelige streng har præcis 8 cifre
            if (preg_match('/^\d{8}$/', $mobil)) {

            } else {
                $mobil = "";
            }


            $mobil = str_replace("'", "", $mobil);
            $mail = str_replace("'", "", $mail);

             $sql = "INSERT INTO gavefabrikken2024.`gaveklubben` ( `shopuser_id`, `email`, `mobil`, `subscribe_date`, `season`) 
                        VALUES ($shopuserID, '$mail', '$mobil', '$orderDato', $this->year)";

             $this->db->set($sql);
        }
        echo "end";
    }





    public function loadShopuserAndEmail(){
        $sql = "SELECT `attribute_value`,`shopuser_id` FROM $this->dbname.`user_attribute` inner join  gavefabrikken2023.shop on shop.id = gavefabrikken2023.`user_attribute`.shop_id WHERE `shopuser_id` in ( SELECT `shopuser_id` FROM $this->dbname.`user_attribute` WHERE `attribute_id` in (SELECT id FROM $this->dbname.`shop_attribute` WHERE `name` LIKE '%Gaveklubben%') and shop.localisation = 1 and shopuser_id in (SELECT `shopuser_id` FROM $this->dbname.`order` ) and attribute_value = 'ja' ) and `is_email` = 1";
        $res = $this->db->get($sql);
        return $res;
    }
    public function getTlf($shopuser){
        $sql = "SELECT * FROM $this->dbname.`user_attribute` WHERE `shopuser_id` = $shopuser and `attribute_id` in (
                    SELECT id FROM $this->dbname.`shop_attribute` WHERE `name` LIKE '%tele%' or `name` like '%Mobi%'
                )";
        $res = $this->db->get($sql);
        return $res;
    }
    public function getOrderDate($shopuser){
        $sql = "SELECT `order_timestamp` FROM $this->dbname.`order` WHERE `shopuser_id` =".$shopuser;
        $res = $this->db->get($sql);
        return $res;
    }



}