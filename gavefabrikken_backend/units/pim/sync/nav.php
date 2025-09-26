<?php
namespace GFUnit\pim\sync;
use GFBiz\units\UnitController;
use GFUnit\pim\sync\kontainerCom;


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);






class Nav extends UnitController
{


    public function __construct()
    {
        parent::__construct(__FILE__);


    }
    public function removeFromQueue($itemnr){
        $sql = "update `navision_item` set is_handled = 1 WHERE  is_handled = 0 and deleted is null and no = '$itemnr'";
         \Dbsqli::setSql2($sql);
    }
    public static function itemnrExist($itemnr)
    {
        $res = \Dbsqli::getSql2("select * from `navision_item`  WHERE  deleted is null and no = '$itemnr'");
        return sizeof($res) > 0 ? $res : false;
    }

    public function createNewPimItem($navItemNo)
    {
        $kontainer = new kontainerCom;

        $res_dk = \Dbsqli::getSql2("SELECT * FROM `navision_item` WHERE no = '".$navItemNo."'  and is_handled = 0 and deleted is null and  language_id = 1");
        $res_no = \Dbsqli::getSql2("SELECT * FROM `navision_item` WHERE no = '".$navItemNo."'  and is_handled = 0 and deleted is null and  language_id = 4");
        $product_no     =     $navItemNo;
        $unit_price_da = 0;
        $unit_price_no = 0;
        $erp_product_name_da = "";
        $erp_product_name_no = "";
        $cost_price_da = 0;
        $cost_price_no = 0;
        $vejl_udsalgspris_tekst_da = "";
        $vejl_udsalgspris_tekst_no = "";
// N23J10806-MG-200
        if(sizeof($res_dk) > 0){
            $erp_product_name_da        = $res_dk[0]["description"];
            $cost_price_da              = $res_dk[0]["standard_cost"] == "" ? 0:$res_dk[0]["standard_cost"];
            $vejl_udsalgspris_tekst_da  = ($res_dk[0]["vejl_pris"]  == "" || $res_dk[0]["vejl_pris"]  == 0) ? "":$res_dk[0]["vejl_pris"];
            $unit_price_da               = $res_dk[0]["unit_price"];
        }
        if(sizeof($res_no) > 0){
            $erp_product_name_no        = $res_no[0]["description"];
            $cost_price_no              = $res_no[0]["standard_cost"]  == "" ? 0:$res_no[0]["standard_cost"];
            $vejl_udsalgspris_tekst_no  = ($res_no[0]["vejl_pris"]  == "" || $res_no[0]["vejl_pris"]  == 0) == "" ? "":$res_no[0]["vejl_pris"];
            $unit_price_no               = $res_no[0]["unit_price"];
        }

        $postData = "{\n  \"data\": {\n  \t\"type\": \"category_item\",\n  \t\"attributes\": {";
        $product_type = 175817;
        $postData.= "\n  \t\t\"product_type\": {\n  \t\t\t\"value\": \"" . $product_type . "\"\n  \t\t},";
        if($erp_product_name_da != "") {
            $postData.= "\n  \t\t\"erp_product_name_da\": {\n  \t\t\t\"value\": \"" . $erp_product_name_da . "\"\n  \t\t},";
        }
        if($erp_product_name_no != "") {
            $postData.= "\n  \t\t\"erp_product_name_no\": {\n  \t\t\t\"value\": \"".$erp_product_name_no."\"\n  \t\t},";
        }
        if($cost_price_da != 0) {
            $postData.= "\n  \t\t\"cost_price_da\": {\n  \t\t\t\"value\": ".$cost_price_da."\n  \t\t},";
        }
        if($cost_price_no != 0) {
            $postData.= "\n  \t\t\"cost_price_no\": {\n  \t\t\t\"value\": ".$cost_price_no."\n  \t\t},";
        }
        if($vejl_udsalgspris_tekst_da != "") {
            $postData.= "\n  \t\t\"vejl_udsalgspris_tekst_da\": {\n  \t\t\t\"value\": \"".$vejl_udsalgspris_tekst_da."\"\n  \t\t},";
        }
        if($vejl_udsalgspris_tekst_no != "") {
            $postData.= "\n  \t\t\"vejl_udsalgspris_tekst_no\": {\n  \t\t\t\"value\": \"".$vejl_udsalgspris_tekst_no."\"\n  \t\t},";
        }
        $postData.= "\n  \t\t\"product_no\": {\n  \t\t\t\"value\": \"".$product_no."\"\n  \t\t}";
        $postData.= "\n\n  \t}\n\n  }\n\n}";
        $result = $kontainer->createNewItem($postData);
        return $result;


    }







    public function doMergeNavElementToPim($navItemNo,$kontainerID)
    {
        $kontainer = new kontainerCom;
       // echo $navItemNo;
        echo $kontainerID;
        $res_dk = \Dbsqli::getSql2("SELECT * FROM `navision_item` WHERE no = '".$navItemNo."'  and is_handled = 0 and deleted is null and  language_id = 1");
        $res_no = \Dbsqli::getSql2("SELECT * FROM `navision_item` WHERE no = '".$navItemNo."'  and is_handled = 0 and deleted is null and  language_id = 4");
        $product_no             = "usdem222o"; //$navItemNo;
        $unit_price_da = 0;
        $unit_price_no = 0;
        $erp_product_name_da = "";
        $erp_product_name_no = "";
        $cost_price_da = 0;
        $cost_price_no = 0;
        $vejl_udsalgspris_tekst_da = "";
        $vejl_udsalgspris_tekst_no = "";

        if(sizeof($res_dk) > 0){
            $erp_product_name_da        = $res_dk[0]["description"];
            $cost_price_da              = $res_dk[0]["standard_cost"] == "" ? 0:$res_dk[0]["standard_cost"];
            $vejl_udsalgspris_tekst_da  = ($res_dk[0]["vejl_pris"]  == "" || $res_dk[0]["vejl_pris"]  == 0) ? "":$res_dk[0]["vejl_pris"];
            $unit_price_da               = $res_dk[0]["unit_price"];
        }
        if(sizeof($res_no) > 0){
            $erp_product_name_no        = $res_no[0]["description"];
            $cost_price_no              = $res_no[0]["standard_cost"]  == "" ? 0:$res_no[0]["standard_cost"];
            $vejl_udsalgspris_tekst_no  = ($res_no[0]["vejl_pris"]  == "" || $res_no[0]["vejl_pris"]  == 0) == "" ? "":$res_no[0]["vejl_pris"];
            $unit_price_no               = $res_no[0]["unit_price"];
        }

        $postData = "{\n  \"data\": {\n  \t\"type\": \"category_item\",\n  \t\"attributes\": {";

        $product_type = 175817;
        $postData.= "\n  \t\t\"product_type\": {\n  \t\t\t\"value\": \"" . $product_type . "\"\n  \t\t},";
        if($erp_product_name_da != "") {
            $postData.= "\n  \t\t\"erp_product_name_da\": {\n  \t\t\t\"value\": \"" . $erp_product_name_da . "\"\n  \t\t},";
        }
        if($erp_product_name_no != "") {
            $postData.= "\n  \t\t\"erp_product_name_no\": {\n  \t\t\t\"value\": \"".$erp_product_name_no."\"\n  \t\t},";
        }
        if($cost_price_da != 0) {
            $postData.= "\n  \t\t\"cost_price_da\": {\n  \t\t\t\"value\": ".$cost_price_da."\n  \t\t},";
        }
        if($cost_price_no != 0) {
            $postData.= "\n  \t\t\"cost_price_no\": {\n  \t\t\t\"value\": ".$cost_price_no."\n  \t\t},";
        }
        if($vejl_udsalgspris_tekst_da != "") {
            $postData.= "\n  \t\t\"vejl_udsalgspris_tekst_da\": {\n  \t\t\t\"value\": \"".$vejl_udsalgspris_tekst_da."\"\n  \t\t},";
        }
        if($vejl_udsalgspris_tekst_no != "") {
            $postData.= "\n  \t\t\"vejl_udsalgspris_tekst_no\": {\n  \t\t\t\"value\": \"".$vejl_udsalgspris_tekst_no."\"\n  \t\t},";
        }
        $postData.= "\n  \t\t\"product_no\": {\n  \t\t\t\"value\": \"".$product_no."\"\n  \t\t}";
        $postData.= "\n\n  \t}\n\n  }\n\n}";
       $kontainerRes =   $kontainer->updateItem($kontainerID,$postData);
       print_r($kontainerRes);
    }




    public function loadNewNAVItems($itemno="")
    {
        $itemno  = trim($itemno);
        $itemSql = $itemno == "" ? "": " and no= '".$itemno."'";


        $returnData = [];
        $kontainer = new kontainerCom;

        die("asdf");


        $res = \Dbsqli::getSql2("SELECT * FROM `navision_item` WHERE `created` > '2023-06-01 08:02:46'  and deleted is null ".$itemSql." ORDER BY `created` limit 1 ");


        foreach ($res as $item){
            $result = json_decode($kontainer->getDataOnItemnr($item["no"]));

            // state 1 : varenr ikke fundet i kontainer
            // state 2 : varenr fundet i kontainer
            if(sizeof($result->data) == 0){
                $returnData[] = array(
                    "state"=>1,
                    "itemno"=>$item["no"],
                    "description"=>$item["description"],
                    "dato"=>$item["created"]
                );
            } else {
                $returnData[] = array(
                    "state"=>2,
                    "itemno"=>$item["no"],
                    "description"=>$item["description"],
                    "dato"=>$item["created"]
                );
            }
            if($itemno != ""){
                if(sizeof($result->data) > 0 ){
                    $returnData[] = array(
                        "state"=> 3
                    );
                }
                return $returnData;
                break;
            }
        }
        return $returnData;
        // \response::success(make_json("res", $returnData));

    }
}