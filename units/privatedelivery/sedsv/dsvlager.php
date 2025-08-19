<?php

namespace GFUnit\privatedelivery\sedsv;

class DSVLager
{


    /*




     UPDATE `shipment` SET itemno = '230165' WHERE `handler` LIKE 'mydsv' AND `itemno` LIKE '230165-efterlev' AND `shipment_state` = 1;
     UPDATE `shipment` SET itemno = '230191' WHERE `handler` LIKE 'mydsv' AND `itemno` LIKE '230191-efterlev' AND `shipment_state` = 1;
     UPDATE `shipment` SET itemno = '220146' WHERE `handler` LIKE 'mydsv' AND `itemno` LIKE '220146-efterlev' AND `shipment_state` = 1;
     UPDATE `shipment` SET itemno = '220147' WHERE `handler` LIKE 'mydsv' AND `itemno` LIKE '220147-efterlev' AND `shipment_state` = 1;
    UPDATE `shipment` SET itemno = '200149' WHERE `handler` LIKE 'mydsv' AND `itemno` LIKE '210148' AND `shipment_state` = 1;
    UPDATE `shipment` SET itemno = '30-LG0024OAKK' WHERE `handler` LIKE 'mydsv' AND `itemno` LIKE '30-lg0022' AND `shipment_state` = 1;

    


    // REMOVED - MAYBE SWITCHED
    UPDATE `shipment` SET itemno = '200107' WHERE `handler` LIKE 'mydsv' AND `itemno` LIKE '220136' AND `shipment_state` = 1;

     *
     */

    /**
     * GENERAL HELPERS ON ITEMNO
     * - View some general notes at the end of this file
     */

    // Replace varenr from DSV to GF
    public static function replaceVarenrList() {
        return array("30-O45GRILL" => "30-Ø45grill");
    }

    // Concvert GF varenr to DSV varenr
    public static function toDSVVarenr($varenr) {
        $replaceVarenrList = self::replaceVarenrList();
        foreach ($replaceVarenrList as $to => $from) {
            if (trim(strtolower($varenr)) == trim(strtolower($from))) {
                return $to;
            }
        }
        return $varenr;
    }


    /**
     * DSVInput - Stocklist
     */

    private static $lastStockList = null;

    private static function loadLastStockList() {
        if(self::$lastStockList != null) return;
        $list = \DSVInput::find_by_sql("SELECT * FROM `dsv_input` WHERE `type` LIKE 'stocklist' ORDER BY created DESC LIMIT 1");
        self::$lastStockList = $list[0];
    }

    public static function getLastUpdateDate() {
        self::loadLastStockList();
        return self::$lastStockList->created;
    }

    private static function getLagerRapportContent() {

        // Load from db and extract content
        self::loadLastStockList();
        $content =  self::$lastStockList->content;

        // Replace varenr rules
        $replaceVarenrList = self::replaceVarenrList();
        foreach($replaceVarenrList as $from => $to) {
            $content = str_replace($from."	",$to."	",$content);
        }

        return $content;

    }

    public static function getLagerLines() {

        $reportRows = explode("\n",self::getLagerRapportContent());
        $lines = array();

        foreach($reportRows as $row) {

            if(trim($row) != "") {

                $cols = explode("\t",$row);
                if($cols[0] != "SKU" && $cols[0] != "") {

                    $lines[] = array(
                        "itemno" => strtolower(trim($cols[0])),
                        "description" => $cols[1],
                        "quantity" => intval($cols[12])
                    );
                }
            }
        }

        return $lines;

    }

    public static function getLagerQuantityMap() {

        $lines = self::getLagerLines();
        $map = array();

        foreach($lines as $line) {
            $map[$line["itemno"]] = $line["quantity"];
        }

        return $map;
    }

    // Load last stock list and return true if less than 1 hour old from created (DateTime object)
    public static function checkStocklistUpdated() {
        self::loadLastStockList();
        $created = self::$lastStockList->created;
        $now = new \DateTime();
        $diff = $now->diff($created);
        if($diff->h < 1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * DSVInput - LagerReport
     */

    private static $lastLagerReport = null;

    private static function loadLastLagerReport() {
        if(self::$lastLagerReport != null) return;
        $list = \DSVInput::find_by_sql("SELECT * FROM `dsv_input`  WHERE `type` LIKE 'lagerreport'  AND `created` <= DATE_SUB(NOW(), INTERVAL 1 HOUR) ORDER BY `created` DESC LIMIT 1;");
        if($list == null || count($list) == 0) {
            self::$lastLagerReport = self::createLagerRapportInput();
        } else {
            self::$lastLagerReport = $list[0];
        }
    }


    public static function getLastGFRapport() {
        self::loadLastLagerReport();
        return self::$lastLagerReport->created;
    }

    public static function createLagerRapportInput() {
        $dsvin = new \DSVInput();
        $dsvin->type = "lagerreport";
        $dsvin->content = "";
        $dsvin->created = date("Y-m-d H:i:s",time());
        $dsvin->linecount = 0;
        $dsvin->save();

        return $dsvin;
    }


    /**
     * DSVInput - OrderList
     */

    private static $lastOrderList = null;

    private static function loadLastOrderList() {
        if(self::$lastOrderList != null) return;
        $list = \DSVInput::find_by_sql("SELECT * FROM `dsv_input` WHERE `type` LIKE 'orderlist' ORDER BY created DESC LIMIT 1");
        self::$lastOrderList = $list[0];
    }

    public static function getLastUpdateOrderDate() {
        self::loadLastOrderList();
        return self::$lastOrderList->created;
    }

    public static function getOrderRapportContent() {

        // Load from db and extract content
        self::loadLastOrderList();
        $content =  self::$lastOrderList->content;
        return $content;

    }

    public static function getOrderLines() {

        $reportRows = explode("\n",trim(self::getOrderRapportContent()));

        // Take first line and save as header
        $headerCols = explode("	",trim(array_shift($reportRows)));

        if(count($headerCols) != 16) {
            echo "Order list headercols is not 16 columns"; exit();
        }

        if($headerCols[1] != "Order ID") {
            echo "Col 1, Order ID does not match: ".$headerCols[1]; exit();
        }

        if($headerCols[2] != "Status") {
            echo "Col 2, Status does not match: ".$headerCols[2]; exit();
        }

        $orderlines = array();
        foreach($reportRows as $row) {

            $cols = explode("	",trim($row));
            $status = $cols[2];
            $orderid = $cols[1];

            if($status == "Released") {
                $orderlines[] = $orderid;
            }

        }

        if(count($orderlines) == 0) {
            return array();
        }

        // Fetch shipment sum
        $sql = "select itemno, count(id) as quantity from shipment where to_certificate_no in (SELECT id  FROM `order` WHERE `order_no` in (".implode(',',$orderlines).")) group by itemno, shipment_state";

        // Fetch shipment sum
        $shipmentSum = \Shipment::find_by_sql($sql);
        return $shipmentSum;

    }

    public static function getOrderQuantityMap() {

        $lines = self::getOrderLines();
        $map = array();

        foreach($lines as $line) {
            $map[strtolower(trim($line->itemno))] = $line->quantity;
        }

        //echo "<pre>".print_r($map,true)."</pre>";

        return $map;
    }

    // Load last Order list and return true if less than 1 hour old from created (DateTime object)
    public static function checkOrderlistUpdated() {
        self::loadLastOrderList();
        $created = self::$lastOrderList->created;
        $now = new \DateTime();
        $diff = $now->diff($created);
        if($diff->h < 1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * DSVInput - OrderPipeline
     */

    
    
    public static function getOrderPipeline() {

        $sql = "SELECT present_model.model_present_no as itemno, count(o.id) as quantity FROM `order` o, present_model, shop_user, company_order WHERE o.shop_id = 1832 and o.present_model_id = present_model.model_id and present_model.language_id = 1 && o.shopuser_id = shop_user.id && shop_user.blocked = 0 && shop_user.shutdown = 0 && shop_user.delivery_state not in (1,2,99,100,101) and shop_user.company_order_id = company_order.id and company_order.order_state in (4,5,9,10) group by present_model.model_present_no;";
        $orderPipeline = \Order::find_by_sql($sql);

        $map = array();
        foreach($orderPipeline as $line) {
            $map[trim(strtolower($line->itemno))] = $line->quantity;
        }
        return $map;

    }





    /********
     * KØR DENNE SQL FOR AT RETTE VARENR MANUELT
     *
     *
     *
     *

    SELECT *  FROM `shipment` WHERE `handler` LIKE 'mydsv' AND `itemno` LIKE '89007' AND `shipment_state` = 1 ORDER BY `shipment_type`  DESC
    UPDATE `shipment` SET itemno = '89006' WHERE `handler` LIKE 'mydsv' AND `itemno` LIKE '89007' AND `shipment_state` = 1 ORDER BY `shipment_type`  DESC

    SELECT *  FROM `shipment` WHERE `handler` LIKE 'mydsv' AND `itemno` LIKE '220110' AND `shipment_state` = 1 ORDER BY `shipment_type`  DESC
    UPDATE `shipment` SET itemno = '220339' WHERE `handler` LIKE 'mydsv' AND `itemno` LIKE '220110' AND `shipment_state` = 1 ORDER BY `shipment_type`  DESC

     *
     *
     *
     *
     */

}