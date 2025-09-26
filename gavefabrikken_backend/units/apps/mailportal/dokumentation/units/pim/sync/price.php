<?php

/*
Price name mapping:








*/
namespace GFUnit\pim\sync;
set_time_limit(500);
use GFBiz\units\UnitController;
use GFUnit\pim\sync\kontainerCom;
/*
   Kostpris:
 *  price     =  prisents_nav_price    =  Cost price DK = standard_cost
 *  price_no  =  prisents_nav_price_no =  Cost price NO = standard_cost
  *
 * Vejledende pris:
 *  indicative_price    = Retailprice DK  = vejl_pris
 *  indicative_price_no = Retailprice NO  = vejl_pris
 * Budget:
 * price_group      =   Budget price DK = unit_price
 * price_group_no   =   Budget price NO = unit_price
  json:
pris = price_group =   Budget price DK = unit_price
budget = indicative_price    = Retailprice DK  = vejl_pris

 */




ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class Price extends UnitController
{
    private $kontainer;

    public function __construct()
    {
        parent::__construct(__FILE__);
        $this->kontainer = new KontainerCom;

    }
    public function test()
    {
        echo "hej ehej sdlaækfjaælsdkjf";
    }


    public function totalPriceUpdate()
    {
        echo "total";
    }


    public function doSyncNavAndGavevalg($data){
        // budget
        foreach ($data as $item) {
              $updataData = array(
                "presentID" => $item["presentID"],
                "pimID" => $item["pimID"],
                "price" => $item["price"],
                "price_no" => $item["price_no"],
                "prisents_nav_price" => $item["prisents_nav_price"],
                "prisents_nav_price_no" => $item["prisents_nav_price_no"],
                "price_group" => $item["price_group"],
                "price_group_no" => $item["price_group_no"],
                "indicative_price" => $item["indicative_price"],
                "indicative_price_no" => $item["indicative_price_no"],
                "pt_price" => $item["pt_price"],
                "pt_price_no" => $item["pt_price_no"]

            );
            // Budget
            $updataData["price_group"] = $item["unit_price"] != "" ? $item["unit_price"]:0;
            $updataData["price_group_no"] = $item["unit_price_no"] != "" ? $item["unit_price_no"]:0;
            // Kostpris
            $updataData["price"]    = $item["standard_cost"] != "" ? $item["standard_cost"]:0;
            $updataData["price_no"] = $item["standard_cost_no"] != "" ? $item["standard_cost_no"]:0;
            $updataData["prisents_nav_price"]       = $updataData["price"];
            $updataData["prisents_nav_price_no"]    = $updataData["price_no"];
            // Vejledende pris
            $updataData["indicative_price"]  = $item["vejl_pris"] != "" ? $item["vejl_pris"]:"";
            $updataData["indicative_price_no"]  = $item["vejl_pris_no"] != "" ? $item["vejl_pris_no"]:"";



            // kontainer data, hvis der mangler NAV data (skulle ikke bruges alligevel
/*
            if($item["pimID"] > 0){
                $kontainerItem = json_decode($this->kontainer->getDataSingle(17196,$item["pimID"]));

                if(!isset($kontainerItem->errors)){
                    $att = $kontainerItem->data->attributes;
                    // vejledende pris
                    $vejl_udsalgspris_tekst_da =  $att->vejl_udsalgspris_tekst_da->value ?? false ? $att->vejl_udsalgspris_tekst_da->value : "";
                    $vejl_udsalgspris_tekst_no =  $att->vejl_udsalgspris_tekst_no->value ?? false ? $att->vejl_udsalgspris_tekst_no->value : "";
                    $updataData["indicative_price"]  = $updataData["indicative_price"] == "" ? $vejl_udsalgspris_tekst_da : $updataData["indicative_price"];
                    $updataData["indicative_price_no"]  = $updataData["indicative_price_no"] == "" ? $vejl_udsalgspris_tekst_no : $updataData["indicative_price_no"];


                    // budget
                    $budget_price_da =  $att->budget_price_da[0]->value ?? false ? $att->budget_price_da[0]->value : 0;
                    $budget_price_no =  $att->budget_price_no[0]->value ?? false ? $att->budget_price_no[0]->value : 0;
                    $updataData["price_group"] = $updataData["price_group"] == 0 ? $budget_price_da:$updataData["price_group"];
                    $updataData["price_group_no"] = $updataData["price_group_no"] == 0 ? $budget_price_no:$updataData["price_group_no"];

                    // kost pris fra nav
                    $cost_price_da =  $att->cost_price_da->value ?? false ? $att->cost_price_da->value : 0;
                    $cost_price_no =  $att->cost_price_no->value ?? false ? $att->cost_price_no->value : 0;
                    $updataData["price"]    = $updataData["price"]  == 0 ? $cost_price_da:$updataData["price"];
                    $updataData["price_no"] = $updataData["price_no"] == 0 ? $cost_price_no:$updataData["price_no"];
                    $updataData["prisents_nav_price"]       = $updataData["price"];
                    $updataData["prisents_nav_price_no"]    = $updataData["price_no"];

                    $temp = array(
                        "vejl_udsalgspris_tekst_da_1"=>$updataData["indicative_price"],
                        "vejl_udsalgspris_tekst_da_2"=>$vejl_udsalgspris_tekst_da,
                        "budget_price_da_1" => $updataData["price_group"],
                        "budget_price_da_2" => $budget_price_da,
                        "cost_price_da" => $updataData["price"],
                        "cost_price_da" =>$cost_price_da
                    );



                }
            }
*/

            // json
            $showBudgetDa = ($updataData["price_group"] == 0 || $updataData["price_group"] == "") ? "false":"true";
            $showRetalDa =  ($updataData["indicative_price"] == 0 || $updataData["indicative_price"] == "") ?  "false":"true";
            $showBudgetNo =  ($updataData["price_group_no"] == 0 || $updataData["price_group_no"] == "") ? "false":"true";
            $showRetalNo =  ($updataData["indicative_price_no"] == 0 || $updataData["indicative_price_no"] == "") ?  "false":"true";



           $price_group = $this->isNumericString($updataData["price_group"]) == 1 ? number_format($updataData["price_group"], 0, ',', '.') : $updataData["price_group"];
           $price_group_no = $this->isNumericString($updataData["price_group_no"]) == 1 ? number_format($updataData["price_group_no"], 0, ',', '.') : $updataData["price_group_no"];
           $indicative_price = $this->isNumericString($updataData["indicative_price"]) == 1 ?  number_format($updataData["indicative_price"], 0, ',', '.') : $updataData["indicative_price"];
           $indicative_price_no = $this->isNumericString($updataData["indicative_price_no"]) == 1 ? number_format($updataData["indicative_price_no"], 0, ',', '.') : $updataData["indicative_price_no"];

           // der er byttet om på pris og budget
           $updataData["pt_price"]  = '{"pris":"'.$price_group.'","vis_pris":"'.$showBudgetDa.'","budget":"'.$indicative_price.'","vis_budget":"'.$showRetalDa.'","special":"","vis_special":"false"}';
           $updataData["pt_price_no"]  = '{"pris":"'.$price_group_no.'","vis_pris":"'.$showBudgetNo.'","budget":"'.$indicative_price_no.'","vis_budget":"'.$showRetalNo.'","special":"","vis_special":"false"}';
           $sql = "
           price_group          =   ".$updataData["price_group"].",
           price_group_no       =   ".$updataData["price_group_no"].",
           price                =   ".$updataData["price"].",
           price_no             =   ".$updataData["price_no"].",
           prisents_nav_price   =   ".$updataData["prisents_nav_price"].",
           prisents_nav_price_no=   ".$updataData["prisents_nav_price_no"].",
           indicative_price     =   '".$indicative_price."',
           indicative_price_no  =   '".$indicative_price_no."',
           pt_price             =   '".$updataData["pt_price"]."',
           pt_price_no          =   '".$updataData["pt_price_no"]."'

           ";
           // print_r($updataData);
         $finalSql = "update present set ".$sql." where id=".$item["presentID"];
           \Dbsqli::setSql2($finalSql);

            echo "-----------------------<br>";


        }
    }
    public function handleJsonPrice(){

    }
    private function isNumericString($string) {
        return preg_match('/^[0-9]+(\.[0-9]+)?$/', $string);
    }

    public function syncNavAndGavevalg($localisation)
    {
        $returnData = [];
        $errorList = [];
        $notInNAV = [];
        $i = 0;
        $presens = $this->getActiveSinglePresents($localisation);

        foreach ($presens as $item) {
            $tempData = array(
                "presentID" => $item->attributes["id"],
                "pimID" => $item->attributes["pim_id"],
                "price" => $item->attributes["price"],
                "price_no" => $item->attributes["price_no"],
                "prisents_nav_price" => $item->attributes["prisents_nav_price"],
                "prisents_nav_price_no" => $item->attributes["prisents_nav_price_no"],
                "price_group" => $item->attributes["price_group"],
                "price_group_no" => $item->attributes["price_group_no"],
                "indicative_price" => $item->attributes["indicative_price"],
                "indicative_price_no" => $item->attributes["indicative_price_no"],
                "pt_price" => $item->attributes["pt_price"],
                "pt_price_no" => $item->attributes["pt_price_no"]

            );

            $tempData["standard_cost"] = "";
            $tempData["unit_price"] = "";
            $tempData["vejl_pris"] = "";
            $tempData["itemno"] = "";
            $tempData["standard_cost_no"] = "";
            $tempData["unit_price_no"] = "";
            $tempData["vejl_pris_no"] = "";
            $tempData["itemno_no"] = "";


         //   $model_dk = \PresentModel::find_by_sql("select id, present_id,model_present_no, count(id) as more from present_model where present_id = " . $item->attributes["id"] . " and  language_id = 1 having more = 1");
         //   $model_no = \PresentModel::find_by_sql("select id, present_id,model_present_no, count(id) as more from present_model where present_id = " . $item->attributes["id"] . " and  language_id = 4 having more = 1");
            if (sizeof($model_dk) == 1) {

                           $tempData["itemno"] = $model_dk[0]->attributes["model_present_no"] ?? false ? $model_dk[0]->attributes["model_present_no"] : "";
                           if (sizeof($model_dk) == 1) {
                               $NAVPrices = $this->getNAVPrices($model_dk[0]->attributes["model_present_no"], 1);
                               if (sizeof($NAVPrices) == 0) {
                                   $notInNAV[] = $model_dk[0]->attributes["model_present_no"];
                               } else {
                                   $tempData["standard_cost"] = $NAVPrices[0]->attributes["standard_cost"];
                                   $tempData["unit_price"] = $NAVPrices[0]->attributes["unit_price"];
                                   $tempData["vejl_pris"] = $NAVPrices[0]->attributes["vejl_pris"];
                                   $tempData["itemno"] = $model_dk[0]->attributes["model_present_no"];


                               }

                           }
                            $returnData[$item->attributes["id"]] = $tempData;

            }
            if (sizeof($model_no) == 1) {


                $tempData["itemno_no"] = $model_no[0]->attributes["model_present_no"] ?? false ? $model_dk[0]->attributes["model_present_no"] : "";
                if (sizeof($model_no) == 1) {
                    $NAVPrices = $this->getNAVPrices($model_no[0]->attributes["model_present_no"], 4);
                    if (sizeof($NAVPrices) == 0) {
                        $notInNAV[] = $model_no[0]->attributes["model_present_no"];
                    } else {
                        $tempData["standard_cost_no"] = $NAVPrices[0]->attributes["standard_cost"];
                        $tempData["unit_price_no"] = $NAVPrices[0]->attributes["unit_price"];
                        $tempData["vejl_pris_no"] = $NAVPrices[0]->attributes["vejl_pris"];



                    }

                }
                $returnData[$item->attributes["id"]] = $tempData;

            }
        }


       // print_R($notInNAV);
        return $returnData;
    }
    public function getAllPricesFromNavSync()
    {
        $returnData = [];
        //$itemList = \NavisionItem::find('all',array("conditions" => array("language_id" => 1,"limit"=>5)));
        $itemList = \NavisionItem::find_by_sql("select * from navision_item where language_id = 1 limit 1  ");
        $kontainer = new KontainerCom();
        foreach ($itemList as $item){

            $itemNO = $item->attributes["no"];
            $this->getActiveSinglePresents("10013717",1);
            /*
            $kData = json_decode($kontainer->getDataOnItemnr("61-613-401307"));
            if(sizeof($kData->data)){
                echo var_dump($kData->data);
            }
            */

        }

    }
    public function getActiveSinglePresents($localisation)
    {
        //$list = \PresentModel::find_by_sql("select * from present where id in (select present_id from present_model where model_present_no ='".$item."')" );
      //  return \Present::find_by_sql("select * from present where copy_of = 0 and shop_id = 0 and deleted = 0 " );
    }
    private function getNAVPrices($item,$localisation)
    {
        return \NavisionItem::find_by_sql("select * from navision_item where language_id = $localisation  and no = '$item' and deleted IS NULL");
    }
    private function compareNAVandGavevalg($gavevalgData,$localisation){

    }
/*
 *
 * hent alle varenr pr sporg
 * tjek mod pim -> lave liste af dem der ikke stemmer dvs ikke ens priser
 * udfyld der hvor priser mangler
 *
 * gavevalgsystem tjek mod standard varer både prissystem 1 og prissystem 2
 *
 *
 *
 */
}