<?php

namespace GFUnit\cardshop\reports;
use GFBiz\units\UnitController;
use GFCommon\Model\CardShop\Gavevalg;

class JGVPresentExport
{

    public function exportJGV()
    {

        $itemClient = new \GFCommon\Model\Navision\ItemsWS(1);


        // Load orders
        $companyOrders = \CompanyOrder::find_by_sql("SELECT * FROM company_order WHERE company_order.shop_id = 7121 and company_order.order_state in (9,10)");
        echo "<pre>";
        echo "FOUND " . count($companyOrders) . " orders\n";

        foreach($companyOrders as $co) {

            $gavevalgList = Gavevalg::getOrderGavevalg($co->id,1,false);
            foreach($gavevalgList as $gavevalg) {

                // Get item
                $varenr = $gavevalg->varenr;

                // Default gave
                if(trimgf($varenr) == "") {

                    // IF JGV, SPECIAL RULES FOR 2024
                    if($co->shop_id == 7121 && \GFConfig::SALES_SEASON == 2024) {

                        $coValues = explode(",",$co->card_values);
                        $jgvAutogaver = array("400" => "24013", "600" => "24018", "800" => "24026");

                        if(isset($jgvAutogaver[$coValues[0]])) {
                            $varenr = $jgvAutogaver[$coValues[0]];
                        }

                    } else {
                        $varenr = $this->shopSettings->getSettings()->default_present_itemno;
                    }
                }

                $item = $itemClient->getItem($varenr);
                if($item == null) {

                    echo "<pre>";
                    var_dump($gavevalg);
                    echo "</pre>";
                    throw new \Exception("Unknown item choice no: ".$varenr);
                }

                $gavePrice = null;

                // Set price for jgv
                if($co->shop_id == 7121) {

                    $gavePrice = $gavevalg->presentValue;
                    if($gavePrice == null) {
                        $coValues = explode(",",$co->card_values);
                        $gavePrice = intval($coValues[0]);
                    }

                }


                echo $co->order_no.";".$varenr.";".$gavevalg->count.";".$gavePrice."\n";


            }

        }
        echo "</pre>";
    }

}