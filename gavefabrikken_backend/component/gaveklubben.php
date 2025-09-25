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
        $csv.= $this->shop();
        $this->makeCsv($csv);
    }

    public function kort(){
        $sql = "select attribute_value,user_attribute.shopuser_id from user_attribute where is_email = 1 and shopuser_id in (
        SELECT `user_attribute`.shopuser_id FROM `user_attribute` 
        inner join shop_attribute on 
        user_attribute.attribute_id = shop_attribute.id
        inner join `order` on
        user_attribute.shopuser_id = `order`.shopuser_id
        WHERE `attribute_value` LIKE 'ja' and `order`.order_timestamp > '2024-07-01' and  user_attribute.shop_id in (52,53,54,55,56,575,290,310,575,2548,2395,9321,7121,4668,4662)
        and shop_attribute.name =  'Gaveklubben tilmelding') having attribute_value not like ('%.no') and attribute_value not like ('%.se') ";
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
            WHERE `attribute_value` LIKE 'ja' and `order`.order_timestamp > '2024-05-01' and  user_attribute.shop_id in ( select id FROM `shop` WHERE `is_company` = 1 and 
            name not like ('no%') and rapport_email not like ('%th@ga%') and localisation = 1  )
            and shop_attribute.name =  'Gaveklubben tilmelding') having attribute_value not like ('%.no') and attribute_value not like ('%.se')  ";
            $rs = $this->db->get($sql);
            $cvr="";
            foreach ($rs["data"] as $ele){
                $cvr.= $ele["attribute_value"]."\n";
            }
            return $cvr;

    }
    public function makeCsv($cvr){
        $filename = "klubben.csv";
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename='.$filename);
        $output = fopen('php://output', 'w');
        fwrite($output,$cvr);
    }
}


















