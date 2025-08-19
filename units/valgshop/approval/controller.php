<?php

namespace GFUnit\valgshop\approval;
use GFBiz\units\UnitController;

class Controller extends UnitController
{

    public function __construct()
    {
        parent::__construct(__FILE__);
    }

    public function testservice() {
        echo "I am a test service!";
    }

    public function updateItemno()
    {
        $model_id = $_POST["model_id"];
        $itemno =  trim($_POST["itemno"]);
        $present_id = $_POST["present_id"];
        $sql = "update present_model set model_present_no = '".$itemno."' where model_id=".$model_id." and present_id=".$present_id;

        $res =  \Dbsqli::setSql2($sql);
       echo json_encode(array("status" => 1,"data"=>$res));

    }


    public function updateSold()
    {
        $shopID = $_POST["shop_id"];
        $count =  $_POST["present_count"];
        $shopM = \ShopMetadata::find_by_sql(" select * from shop_metadata where shop_id = ".$shopID);
        if(sizeof($shopM) == 0){
            $c = new \ShopMetadata();
            $c->shop_id = $_POST["shop_id"];
            $c->user_count = $_POST["present_count"];
            $c->save();

        } else {
            $ShopMetadata =  \ShopMetadata::all(array('conditions' => 'shop_id = '.$_POST["shop_id"]  ));

            $ShopMetadata[0]->user_count = $_POST["present_count"];
            $ShopMetadata[0]->save();
        }
        \system::connection()->commit();
        echo json_encode(array("status" => 1));
    }
    public function getSold()
    {
        $shopID = $_POST["shop_id"];
        $ShopMetadata =  \ShopMetadata::all(array('conditions' => 'shop_id = '.$_POST["shop_id"]  ));
        echo json_encode(array("status" => 1,"data"=>$ShopMetadata));
    }


    public function approval(){
        $shopID = $_POST["shop_id"];
        $sql = "update shop set reservation_state=1,reservation_language =1 where id=".$shopID;
        \Dbsqli::setSql2($sql);
        echo json_encode(array("status" => 1));
    }

    public function isApproval(){
        $shopID = $_POST["shop_id"];
        $sql = "select * from shop where reservation_state=1 and id=".$shopID;
        $shop = \Shop::find_by_sql($sql);
        $res = sizeof($shop) > 0 ? '1':'0';
        $response = array("status" => 1, "data" => $res);
        echo json_encode($response);
    }
        public function getReservation_code(){
            $shopID = $_POST["shop_id"];
            $sql = "select reservation_code from shop where id=".$shopID;
            $shop = \Shop::find_by_sql($sql);
        
            $response = array("status" => 1, "data" => $shop);
            echo json_encode($response);
        }
/*
 *       $ShopPresents = ShopPresent::all(array(
        'conditions' => array('present_id' => $presentId, 'shop_id' =>$shopId)));
        $ShopPresent = ShopPresent::find($ShopPresents[0]->attributes["id"]);
        $ShopPresent->active = 0;
        $ShopPresent->is_deleted = 0;
        $ShopPresent->save();
        $dummy = [];

 */


    public function hasDeativatedItems(){
        $shopID = $_POST["shopid"];
        $shopPresents = \ShopPresent::find_by_sql("SELECT * FROM `shop_present` WHERE `shop_id` = ".$shopID." AND `active` = 0 AND `is_deleted` = 0");
        $res = sizeof($shopPresents) > 0 ? '0':'1';
        $response = array("status" => 1, "data" => $res);
        echo json_encode($response);
    }


    public function hasStrengthsSet(){
        $shopID = $_POST["shopid"];
        $presents = \PresentModel::find_by_sql(" select * from present_model where
            present_id in ( 
                SELECT id FROM `present` WHERE `shop_id` = ".intval($shopID)."   and deleted = 0 and id in( 
                SELECT present_id FROM `shop_present` WHERE `shop_id` = ".intval($shopID)."  AND `is_deleted` = 0 and active = 1 )
		    ) and language_id = 1  and is_deleted = 0 and active = 0 and strength = 0 order by present_id, model_id
            ");

        $res = sizeof($presents) > 0 ? '0':'1';
        $response = array("status" => 1, "data" => $res);
        echo json_encode($response);


    }
    public function hasValidItemNr(){
        $shopID = $_POST["shopid"];
        $models = $this->getShopPresentsModels($shopID);

        foreach ($models as $model){
         $itemnr = $model->attributes['model_present_no'];
         if(!$this->itemnrExist($itemnr)){
             echo json_encode(array("status" => 1, "data" => "0"));
             return;
         }
        }
        echo json_encode(array("status" => 1, "data" => "1"));
    }
    public function getStockStatus_odl(){
        $itemno = trim($_POST["itemnr"]);
        $shopid = trim($_POST["shopid"]);
        $stock = \MagentoStockTotal::find_by_sql(" select * from magento_stock_total   where itemno = '".$itemno."'");
        echo json_encode(array("status" => 1, "data" => $stock));

    }
public function getStockStatus(){
    $itemno = trim($_POST["itemnr"]);
    $shopid = trim($_POST["shopid"]);

    // Hent localisation fra shop tabellen
    $shop = \Shop::find_by_sql("SELECT localisation FROM shop WHERE id = '".$shopid."'");

    // Bestem hvilken language_id der skal bruges
    $language_id = 1; // default værdi
    if (!empty($shop) && isset($shop[0]->localisation) && $shop[0]->localisation == 4) {
        $language_id = 4;
    }
    $sql =  "SELECT * FROM navision_stock_total
                   WHERE itemno = '".$itemno."'
                   AND language_id = ".$language_id;
    // Hent stock data med den korrekte language_id
    $stock = \NavisionStockTotal::find_by_sql( $sql);

    echo json_encode(array("status" => 1, "data" => $stock, "language_id" => $language_id));
}


    /**
     * SERVICES
     */
    public function getShopPresentsModels($shop_id){
        return \PresentModel::find_by_sql(" select * from present_model where
            present_id in ( SELECT id FROM `present` WHERE `shop_id` = ".intval($shop_id)."   and deleted = 0 and id
		in( SELECT present_id FROM `shop_present` WHERE `shop_id` = ".intval($shop_id)."  AND `is_deleted` = 0 and active = 1 )
		) and language_id = 1  and is_deleted = 0 and active = 0 order by present_id, model_id
            ");
    }
    public function itemnrExist($itemnr)
    {
        $itemnr = trim($itemnr);
        $sql = "SELECT * FROM `navision_item` where no = '$itemnr' and deleted is null";
        if(sizeof(\Dbsqli::getSql2($sql)) > 0) return true;
        return false;

    }
    public function getItemnoInSam()
    {
        $returnData = array();
        $samItemno = trim($_POST["itemno"]);

        $sql = "SELECT * FROM `navision_bomitem` WHERE `parent_item_no` LIKE '".$samItemno."' and `language_id` = 1   and deleted is Null";
        $res = \Dbsqli::getSql2($sql);
        if(sizeof($res) > 0) {
            foreach ($res as $item){
                $itemNo =  $item["no"];
                $sql = "SELECT * FROM `navision_item` where language_id = 1 and  no = '$itemNo' and deleted is Null";
                $res = \Dbsqli::getSql2($sql);
                if(sizeof($res) > 0) {
                    $returnData[] = array(
                        "no" => $res[0]["no"],
                        "is_external" => $res[0]["is_external"]
                    );
                } else {
                    array(
                        "no"=>$itemNo,
                        "is_external"=>"N/A"
                    );
                }
            }
            echo json_encode(array("status" => 1, "data" => $returnData));
        } else {
            echo json_encode(array("status" => 1, "data" => "0"));
        }

    }
    public function itemnrExistExt()
    {

        $itemno = trim($_POST["itemnr"]);
        $sql = "SELECT * FROM `navision_item` where   no = '$itemno' and deleted is null";
        $res = \Dbsqli::getSql2($sql);
        if(sizeof($res) > 0) {
            echo json_encode(array("status" => 1, "data" => "1","item"=>$res));
        } else {
            echo json_encode(array("status" => 1, "data" => "0"));
        }
    }


}