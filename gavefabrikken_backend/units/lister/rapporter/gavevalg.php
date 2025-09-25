<?php

namespace GFUnit\lister\rapporter;



class Gavevalg
{

    public function gavevalgrapport($language,$startDate,$endDate)
    {

        //echo "Hent rapport $language $startDate $endDate<br><br>";

        // Load shops
        $shopList = \CardshopSettings::find_by_sql("SELECT * FROM cardshop_settings WHERE language_code = ".$language);
        $shopMap = array();
        $shopIDList = array();

        foreach($shopList as $shop) {
            $shopMap[$shop->shop_id] = $shop;
            $shopIDList[] = $shop->shop_id;
        }

        // Load nav cost price
        $navLanguage = $language;
        if($navLanguage == 5) $navLanguage = 1;
        $navisionItems = \NavisionItem::find_by_sql("SELECT * FROM navision_item where language_id = ".$navLanguage." ORDER BY deleted ASC");
        $costPriceMap = array();

        foreach($navisionItems as $navItem) {
            if(!isset($costPriceMap[$navItem->no])) {
                $costPriceMap[$navItem->no] = $navItem->unit_cost;
            }
        }
        /*
        echo "Shop id list";
        print_r($shopIDList);

        echo "<br><br>Navision cost price";
        print_r($costPriceMap);
        */

        // Make sql to perform
        $sql = "SELECT `order`.shop_id, present_model.model_present_no, present_model.model_name, present_model.model_no FROM [DB].`order`, [DB].present_model where `order`.order_timestamp >= '".$startDate."' && present_model.model_id = `order`.present_model_id && `order`.present_id = present_model.present_id && present_model.language_id = 1 && `order`.order_timestamp < '".$endDate."' && `order`.shop_id in (".implode(",",$shopIDList).")";

        $orderList = array();

        // Perform on each db
        $dbNames = array(
            \GFConfig::DB_DATABASE,
            //"gavefabrikken_2020"
        );
        foreach($dbNames as $dbName) {

            $dbSql = str_replace("[DB]",$dbName,$sql);
            //echo $dbName.": ".$dbSql."<br><br>";

            $orders = \Order::find_by_sql($dbSql);
            //echo "Found ".count($orders)." orders in ".$dbName."<br>";
            foreach($orders as $order) {
                $orderList[] = $order;
            }

        }

        //echo "Orderlist now has: ".count($orderList);

        $presentData = array();
        $shopCount = array();

        // Process each
        foreach($orderList as $order) {

            $itemNo = $order->model_present_no;
            $shopID = $order->shop_id;

            if(!isset($presentData[$itemNo])) {
                $presentName = $order->model_name.(trim($order->model_no) == "" ? "" : ", ".$order->model_no);
                $presentData[$itemNo] = array("itemno" => $itemNo, "cost" => (isset($costPriceMap[$itemNo]) ? $costPriceMap[$itemNo] : ""), "name" => $presentName);
            }

            if(!isset($shopCount[$shopID])) {
                $shopCount[$shopID] = array();
            }

            if(!isset($shopCount[$shopID][$itemNo])) {
                $shopCount[$shopID][$itemNo] = 0;
            }

            $shopCount[$shopID][$itemNo]++;

        }

        // Output
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=gavevalg-se-'.date("dmYHi").'.csv');



        foreach($shopCount as $shopID => $countData) {

            echo "Koncept;".$shop->concept_code."\n";
            echo "Varenr;Navn;Cost;Antal valg\n";

            $shop = $shopMap[$shopID];

            //echo "Koncept: ".$shop->concept_code."<br>";

            $keys = array_keys($countData);
            sort($keys);

            foreach($keys as $key) {

                echo $this->decodeVal($key).";".$this->decodeVal($presentData[$key]["name"]).";".$this->decodeVal($presentData[$key]["cost"]).";".$this->decodeVal($countData[$key]).";\n";
                //echo $key." - ".$countData[$key]." - ".$presentData[$key]["name"]." - ".$presentData[$key]["cost"]."<br>";
            }
            echo "\n\n\n";
        }




        //echo "<pre>".print_r($shopCount,true)."</pre>";

    }

    private function decodeVal($val) {
        return utf8_decode(trim(str_replace(array("\r","\n",";"),array(""," ",""),$val)));
    }

}