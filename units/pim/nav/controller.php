<?php

namespace GFUnit\pim\nav;

use GFBiz\units\UnitController;
use GFCommon\Model\Navision\ItemsWS;
use GFCommon\Model\Navision\SalesPricesWS;
use GFUnit\pim\sync\nav;
class Controller extends UnitController
{
    public function __construct()
    {
        parent::__construct(__FILE__);

    }
    public function loadNewNAVItems()
    {
       // $nav = new Nav;
        echo "asdf";
        //$res = $nav->loadNewNAVItems();
        //print_R($res);
    }

    public function getsalesprices($languageid=1,$varenr="") {

        $languageid = intvalgf($languageid);
        $varenr = trimgf($varenr);

        if(!in_array($languageid, array(1,4,5))) {
            echo json_encode(array("status" => 0, "error" => "Invalid language id"));
            return;
        }
        if($languageid == 4) $languageid = 1;

        if($varenr == "") {
            echo json_encode(array("status" => 0, "error" => "Empty item no"));
            return;
        }


        try {

            $client = new SalesPricesWS($languageid);
            $servicePrices = $client->getItemSalesPrices($varenr);
            $returnData = array();

            if(countgf($servicePrices) > 0) {
                foreach($servicePrices as $price) {
                    $returnData[] = $price->getDataArray();
                }
            } else {
                echo json_encode(array("status" => 0, "error" => "No salesprices found for this item no"));
            }

            echo json_encode(array("status" => 1, "data" => $returnData));

        } catch (\Exception $e) {
            echo json_encode(array("status" => 0, "error" => "Error in fetching data from Navision"));
            return;
        }



    }


    public function getitem($languageid=1,$varenr="") {

        $languageid = intvalgf($languageid);
        $varenr = trimgf($varenr);

        if(!in_array($languageid, array(1,4,5))) {
            echo json_encode(array("status" => 0, "error" => "Invalid language id"));
            return;
        }
        if($languageid == 4) $languageid = 1;

        if($varenr == "") {
            echo json_encode(array("status" => 0, "error" => "Empty item no"));
            return;
        }

        try {

            $client = new ItemsWS($languageid);
            $navisionItem = $client->getItem($varenr);
            
            if($navisionItem == null) {
                echo json_encode(array("status" => 0, "error" => "No item found for this item no"));
                return;
            }

            echo json_encode(array("status" => 1, "data" => $navisionItem->getDataArray()));


        } catch (\Exception $e) {
            echo json_encode(array("status" => 0, "error" => "Error in fetching data from Navision"));
            return;
        }



    }

}