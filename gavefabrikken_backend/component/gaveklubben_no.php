<?php
if( !isset($_GET["token"]) || $_GET["token"] != "sdafasdl897y89hokhoafsa" ){
    die("Ingen adgang");
}
echo "Opretter rapport";
ini_set('max_execution_time', 300); //300 seconds = 5 minutes
ini_set('memory_limit','2048M');
ini_set('display_errors', 1);
error_reporting(E_ALL);
include("sms/db/db.php");



$rapport = new rapport;
$rapport->all();

class rapport
{
    private $db;
    private $csv;
    public function __construct() {
        $this->db = new Dbsqli();
        $this->db->setKeepOpen();
        $this->csv="";
    }
    public function all(){
        $csv="";

        $csv.= $this->kort();
     //   $csv.= $this->shop();
        $this->makeCsv($csv);
    }

    public function kort(){
        $sql = "select attribute_value,user_attribute.shopuser_id from user_attribute where is_email = 1 and shopuser_id in (
        SELECT `user_attribute`.shopuser_id FROM `user_attribute` 
        inner join shop_attribute on 
        user_attribute.attribute_id = shop_attribute.id
        inner join `order` on
        user_attribute.shopuser_id = `order`.shopuser_id
        WHERE `attribute_value` LIKE 'ja' and `order`.order_timestamp > '2022-07-01' and  user_attribute.shop_id in (272,57,58,59,574,2550,4740,8355, 8356, 8357, 8358, 8359, 8360, 8361, 8362, 8363, 8364, 8365, 8366)
        and shop_attribute.name =  'Gaveklubben tilmelding') having attribute_value not like ('%.dk') and attribute_value not like ('%.se') ";
        $rs = $this->db->get($sql);
        $cvr="";
        foreach ($rs["data"] as $ele){
            $cvr.= $ele["attribute_value"]."\n";
        }
        return $cvr;


    }
    public function shop(){
        $sql = "select attribute_value,user_attribute.shopuser_id from user_attribute where is_email = 1 and shopuser_id in (
            SELECT `user_attribute`.shopuser_id FROM `user_attribute` 
            inner join shop_attribute on 
            user_attribute.attribute_id = shop_attribute.id
            inner join `order` on
            user_attribute.shopuser_id = `order`.shopuser_id
            WHERE `attribute_value` LIKE 'ja' and `order`.order_timestamp > '2022-05-01' and  user_attribute.shop_id in ( select id FROM `shop` WHERE `is_company` = 4 and 
            name not like ('dk%') and rapport_email  and localisation = 4  )
            and shop_attribute.name =  'Gaveklubben tilmelding') having attribute_value not like ('%.dk') and attribute_value not like ('%.se')  ";
        $rs = $this->db->get($sql);
        $cvr="";
        foreach ($rs["data"] as $ele){
            $cvr.= $ele["attribute_value"]."\n";
        }
        return $cvr;

    }
    public function makeCsv($cvr){
        $filename = "klubben_no.csv";
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename='.$filename);
        $output = fopen('php://output', 'w');
        fwrite($output,$cvr);
    }
}


















