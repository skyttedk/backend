<?php

namespace GFUnit\pim\sync;

use GFBiz\units\UnitController;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);



class gavevalgPresentModel extends UnitController
{
    private $error = [];

    public function __construct()
    {
        parent::__construct(__FILE__);

    }
    public static function ping()
    {
        echo "pin GavevalgPresentModel";
    }
    public static function updateChildItemnr($pimID,$itemnr)
    {

        $res = Nav::itemnrExist($itemnr);
        if($res === false) return;
        // get list of copy af present
        $sql ="SELECT id FROM `present` where copy_of in( SELECT id FROM `present` WHERE `pim_id` = $pimID and shop_id = 0 and `copy_of` = 0)";
        $items = \Dbsqli::getSql2($sql);
        foreach ($items as $item){
            if(!self::isNotValgMellemGave($item["id"])) continue;
            // Opdaterer varenummer
            $sql = "update present_model set model_present_no = '".$itemnr."' where present_id =".$item["id"];
            \Dbsqli::setSql2($sql);
            // hvis sam nummer opdatere de varenr, som sam består af
            if (strtolower(substr($itemnr, 0, 3)) == "sam"){
                // find model id
                $sql = "select distinct (model_id) from present_model where present_id = ".$item["id"]." and model_present_no = '".$itemnr."' and is_deleted = 0";
                $modelIDRes = \Dbsqli::getSql2($sql);
                if(sizeof($modelIDRes)==1){
                    self::updateSamItem($modelIDRes[0]["model_id"],$itemnr);
                }
            }



        }

    }
    public static function  updateSamItem($modelID,$itemnr) : bool
    {

        if (!strtolower(substr($itemnr, 0, 3)) == "sam") return false;
        // slå op i tabel hent alle ware nr opret
        $sql = "SELECT GROUP_CONCAT(DISTINCT `no` SEPARATOR '\n') AS result
                FROM `navision_bomitem`
                WHERE `parent_item_no` = '$itemnr' AND `deleted` IS NULL";
        $items = \Dbsqli::getSql2($sql);
        if($items[0]["result"] == "") return false;
        $sql = "INSERT INTO present_model_sampak (model_id, item_list)
                    VALUES ($modelID, '".$items[0]["result"]."') AS new_values
                ON DUPLICATE KEY UPDATE item_list = new_values.item_list";
         \Dbsqli::setSql2($sql);
         $sql2 = "update present_model set sampak_items = '".$items[0]["result"]."' where model_id = ".$modelID." and language_id = 1";
        \Dbsqli::setSql2( $sql2);
        return true;


    }
    public static function isNotValgMellemGave($presentID): bool
    {
        $sql = "SELECT count(id) as c FROM `present_model` WHERE `present_id` = $presentID and is_deleted = 0";
        $models = \Dbsqli::getSql2($sql);
        if( sizeof($models) == 0 ) return false;
        return $models[0]["c"] == 5;
    }


}
