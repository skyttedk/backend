<?php


include("model/dbsqli.class.php");
Class shopItemSyncController Extends baseController {

    public function Index() {
        echo "shopItemSyncController";
    }
    public function syncItemsNo(){
        $shopID = $_POST["shopID"];
        $sql = "SELECT * FROM `present_model` WHERE `present_id` in (SELECT present.id FROM `present` WHERE `shop_id` = ".$shopID.")  and `language_id` =1";
        $localPresents = Dbsqli::getSql2($sql);
        foreach ($localPresents as $localP){
            $masterPresent = Dbsqli::getSql2("SELECT * FROM `present_model` WHERE `model_id` = ".$localP["original_model_id"]." and `language_id` =1");
            if($masterPresent[0]["model_present_no"] !=  $localP["model_present_no"]){
                $sql =  "update present_model set model_present_no = '".$masterPresent[0]["model_present_no"]."' where model_id =". $localP["model_id"];
                Dbsqli::setSql2( $sql);
            }
        }
        //response::success();
    }
    public function countSyncItemsNo(){
        $shopID = $_POST["shopID"];
        $count = 0;
        $sql = "SELECT * FROM `present_model` WHERE `present_id` in (SELECT present.id FROM `present` WHERE `shop_id` = ".$shopID.")  and `language_id` =1";
        $localPresents = Dbsqli::getSql2($sql);
        foreach ($localPresents as $localP){
            $masterPresent = Dbsqli::getSql2("SELECT * FROM `present_model` WHERE `model_id` = ".$localP["original_model_id"]." and `language_id` =1");
            if($masterPresent[0]["model_present_no"] !=  $localP["model_present_no"]){
                $count++;
            }
        }
        $res = array("count"=>$count);
        response::success(json_encode($res));
    }


}