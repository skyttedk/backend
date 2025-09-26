<?php
namespace GFUnit\pim\kontainerutilities;
use GFBiz\units\UnitController;
use GFUnit\pim\sync\kontainerCom;
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class copyitem extends UnitController
{

    private $kontainer;
    public function __construct()
    {
        $this->kontainer = new KontainerCom;
        parent::__construct(__FILE__);
    }

    public function copyitem($itemno,$kontainerID)
    {
       $this->itemno = $itemno;
       $this->kontainerID = $kontainerID;
       $res = $this->kontainer->getDataSingle("",(int) $this->kontainerID);
       $pimData = $this->mapdata($res);

        $res = $this->createItemInPIM($pimData);
        echo $res;
    }
    public function mapdata($data)
    {
        $pimData = [];
        $specialPimData = [];
        $res = json_decode($data);
      //  var_dump($res);
        //$Group_product_kontainerID = $res->data->id;
        $att = $res->data->attributes;

        $itemnr =  $att->product_no->value ?? false ? "Copy-of ".$att->product_no->value : "Copy-of";
        // model data
        $pimData["erp_product_name_da"] =  $att->erp_product_name_da->value ?? false ? $att->erp_product_name_da->value : "";
        $pimData["erp_product_name_en"] =  $att->erp_product_name_en->value ?? false ? $att->erp_product_name_en->value : "";
        $pimData["erp_product_name_no"] =  $att->erp_product_name_no->value ?? false ? $att->erp_product_name_no->value : "";
        $pimData["erp_product_name_se"] =  $att->erp_product_name_se->value ?? false ? $att->erp_product_name_se->value : "";

        // overskrift
        $pimData["product_name_da"] = $att->product_name_da->value ?? false ? $att->product_name_da->value : "";
        $pimData["product_name_en"] = $att->product_name_en->value ?? false ? $att->product_name_en->value : "";
        $pimData["product_name_no"] = $att->product_name_no->value ?? false ? $att->product_name_no->value : "";
        $pimData["product_name_se"] = $att->product_name_se->value ?? false ? $att->product_name_se->value : "";

        // Beskrivelser
        $pimData["description_da"] =  $att->description_da->value ?? false ? $att->description_da->value : "";
        $pimData["description_en"] =  $att->description_en->value ?? false ? $att->description_en->value : "";
        $pimData["description_no"] =  $att->description_no->value ?? false ? $att->description_no->value : "";
        $pimData["description_se"] =  $att->description_se->value ?? false ? $att->description_se->value : "";

        // vejledende pris
        $pimData["vejl_udsalgspris_tekst_da"] =  $att->vejl_udsalgspris_tekst_da->value ?? false ? $att->vejl_udsalgspris_tekst_da->value : "";
        $pimData["vejl_udsalgspris_tekst_en"] =  $att->vejl_udsalgspris_tekst_en->value ?? false ? $att->vejl_udsalgspris_tekst_en->value : "";
        $pimData["vejl_udsalgspris_tekst_no"] =  $att->vejl_udsalgspris_tekst_no->value ?? false ? $att->vejl_udsalgspris_tekst_no->value : "";
        $pimData["vejl_udsalgspris_tekst_se"] =  $att->vejl_udsalgspris_tekst_se->value ?? false ? $att->vejl_udsalgspris_tekst_se->value : "";

        // Kost pris
        $pimData["cost_price_da"] =   $att->cost_price_da->value ?? false ? $att->cost_price_da->value : "";
        $pimData["cost_price_en"] =   $att->cost_price_en->value ?? false ? $att->cost_price_en->value : "";
        $pimData["cost_price_no"] =   $att->cost_price_no->value ?? false ? $att->cost_price_no->value : "";
        $pimData["cost_price_se"] =   $att->cost_price_se->value ?? false ? $att->cost_price_se->value : "";
        // budget
        $specialPimData["budget_price_da"] =  $att->budget_price_da[0]->value ?? false ? $att->budget_price_da[0]->meta->element_option_id : "";
        $specialPimData["budget_price_en"] =  $att->budget_price_en[0]->value ?? false ? $att->budget_price_en[0]->meta->element_option_id : "";
        $specialPimData["budget_price_no"] =  $att->budget_price_no[0]->value ?? false ? $att->budget_price_no[0]->meta->element_option_id : "";
        $specialPimData["budget_price_se"] =  $att->budget_price_se[0]->value ?? false ? $att->budget_price_se[0]->meta->element_option_id : "";

        // shop settings
        $pimData["gave_med_omtanke_da"] =   $att->gave_med_omtanke_da->value ?? false ? $att->gave_med_omtanke_da->value : "";
        $pimData["gave_med_omtanke_en"] =   $att->gave_med_omtanke_en->value ?? false ? $att->gave_med_omtanke_en->value : "";
        $pimData["gave_med_omtanke_no"] =   $att->gave_med_omtanke_no->value ?? false ? $att->gave_med_omtanke_no->value : "";
        $pimData["gave_med_omtanke_se"] =   $att->gave_med_omtanke_se->value ?? false ? $att->gave_med_omtanke_se->value : "";

        $pimData["kun_hos_gavefabrikken_da"] =   $att->kun_hos_gavefabrikken_da->value ?? false ? $att->kun_hos_gavefabrikken_da->value : "";
        $pimData["kun_hos_gavefabrikken_en"] =   $att->kun_hos_gavefabrikken_en->value ?? false ? $att->kun_hos_gavefabrikken_en->value : "";
        $pimData["kun_hos_gavefabrikken_no"] =   $att->kun_hos_gavefabrikken_no->value ?? false ? $att->kun_hos_gavefabrikken_no->value : "";
        $pimData["kun_hos_gavefabrikken_se"] =   $att->kun_hos_gavefabrikken_se->value ?? false ? $att->kun_hos_gavefabrikken_se->value : "";




        // liste med vare nr
        $pimData["group_product_nos"] =   $att->group_product_nos->value ?? false ? $att->group_product_nos->value : "";
        $pimData["group_product_nos"] = str_replace("\n", "\\n", $pimData["group_product_nos"]);
        $pimData["group_product_nos"] = str_replace("\r", "\\r", $pimData["group_product_nos"]);
        // category
        $specialPimData["category"] =  $att->category[0]->value ?? false ? $att->category[0]->meta->element_option_id : "";
        // logo
        $pimData["logo"] =  $att->logo->value ?? false ? $att->logo->meta->resource_item_id : "";
        // product_type
        $pimData["product_type"] =  $att->product_type->value ?? false ? $att->product_type->meta->element_option_id : "";
        // suppliers
        $pimData["suppliers"] =  $att->suppliers->value ?? false ? $att->suppliers->meta->resource_item_id : "";
        // storeview
        if($att->storeview ?? false ? true:false){
            $storeviewList = [];
            /*foreach ($att->storeview as $storeview){
                $storeviewList[] = $storeview;
            }*/
            if(sizeof($att->storeview) > 0){
                $specialPimData["storeview"]  = $att->storeview[0]->meta->element_option_id;
            }
        }
        // billeder

        $pimData["image_1"] =  $att->image_1->value ?? false ? $att->image_1->value : "";
        $pimData["image_2"] =  $att->image_2->value ?? false ? $att->image_2->value : "";
        $pimData["image_3"] =  $att->image_3->value ?? false ? $att->image_3->value : "";
        $pimData["image_4"] =  $att->image_4->value ?? false ? $att->image_4->value : "";
        $pimData["pack_billede"] =  $att->pack_billede->value ?? false ? $att->pack_billede->value : "";
        $pimData["show_in_presentation_2"] =  $att->show_in_presentation_2->value ?? false ? $att->show_in_presentation_2->value : 0;



        return array("pim"=>$pimData,"special"=>$specialPimData,"itemnr"=>$itemnr);
    }
    public function createItemInPIM($data){

      $product_no = $data["itemnr"];

        $postData = "{\n  \"data\": {\n  \t\"type\": \"category_item\",\n  \t\"attributes\": {";
        foreach ($data["pim"] as $key=>$val){
            if($val != "") {
                $postData .= "\n  \t\t\"" . $key . "\": {\n  \t\t\t\"value\": \"" . $val . "\"\n  \t\t},";
            }
        }
        foreach ($data["special"] as $key=>$val){
            if($val != ""){
               $postData.= "\n  \t\t\"".$key."\": {\n  \t\t\t\"value\": [\"" .(int) $val. "\"] \n  \t\t},";
            }

        }


        $postData.= "\n  \t\t\"product_no\": {\n  \t\t\t\"value\": \"".$product_no."\"\n  \t\t}";

        $postData.= "\n\n  \t}\n\n  }\n\n}";


        $result = $this->kontainer->createNewItem($postData);
        return $result;

    }

}