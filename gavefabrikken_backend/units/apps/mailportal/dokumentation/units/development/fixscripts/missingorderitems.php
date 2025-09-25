<?php

namespace GFUnit\development\fixscripts;

class MissingOrderitems
{

    private $stats;

    public function run() {

        echo "RUN FIX ORDER ITEMS SCRIPT<br>";
        $this->stats = array("noorderdata" => 0,"multipleorderdata" => 0,"weborder" => 0,"ignored" => 0,"ready" => 0,"dataerror" => 0,"invoiceorders" => array());

        $companyOrderList = \CompanyOrder::find("all");
        echo "Found ".countgf($companyOrderList);
        $this->stats["totalcount"] = countgf($companyOrderList);

        foreach($companyOrderList as $i => $companyOrder) {
            $this->processOrder($companyOrder);
            //if($i > 200) {
            //    break;
            //}
        }

        echo "<br>ABORT SCRIPT<br>";
        echo "<pre>".print_r($this->stats,true)."</pre>";

    }

    private function processOrder($companyOrder)
    {

        $ignoreIDList = array(231);

        echo "<br><br>Processing ".$companyOrder->id." / ".$companyOrder->order_no." - ".$companyOrder->company_name."<br>";

        // Check ignored
        if(in_array($companyOrder->id,$ignoreIDList)) {
            echo "IGNORED ORDER<br>";
            $this->stats["ignored"]++;
            return;
        }

        // Check if web order
        if($companyOrder->salesperson == "IMPORT") {
            echo "WEB ORDER - NOT AFFECTED<br>";
            $this->stats["weborder"]++;
            return;
        }

        // Find in log
        $systemlog = \SystemLog::find_by_sql("SELECT * FROM system_log WHERE created_datetime >= '".date('Y-m-d H:i:s',$companyOrder->created_datetime->getTimestamp())."' && created_datetime < '".date('Y-m-d H:i:s',$companyOrder->created_datetime->getTimestamp()+10)."' && data LIKE '%orderitemlist%'");

        if(count($systemlog) == 0) {
            echo "COULD NOT FIND ORDER ITEMS DATA<br>";
            $this->stats["noorderdata"]++;
            return;
        }
        if(count($systemlog) == 0) {
            echo "MULTIPLE ORDER ITEMS DATA<br>";
            $this->stats["multipleorderdata"]++;
            return;
        }


        // Get order items
        $items = \CompanyOrderItem::find('all',array("conditions" => array("companyorder_id" => $companyOrder->id)));
        $itemsMap = array();
        foreach($items as $item) {
            $itemsMap[$item->type] = $item;
        }


        $orderdata = json_decode($systemlog[0]->data,true);
        echo "<pre>".print_r($orderdata,true)."</pre>";


        if($orderdata == null || !isset($orderdata["orderitemlist"]) || countgf($orderdata["orderitemlist"]) == 0) {
            echo "DATA ERROR<br>";
            $this->stats["dataerror"]++;
            return;
        }

        $orderlines = array();
        foreach($orderdata["orderitemlist"] as $orderline) {
            $orderlines[$orderline["type"]] = $orderline;
        }

        foreach($itemsMap as $type => $item)
        {
            echo "- Checking ".$type." is ".($item->quantity == 0 ? "DISABLED" : "ENABLED")." on order - ".(isset($orderlines[$type]) ? "POSTED" : "MISSING")." from request<br>";

            if($item->quantity > 0 && !isset($orderlines[$type])) {

                echo "PROBLEM HERE!<br>";
                $key = $type."_ENABLED_NOTPOSTED";
                if(!isset($this->stats[$key])) $this->stats[$key] = 0;
                $this->stats[$key]++;

                if($type == "INVOICEFEEINITIAL") {
                    $this->stats["invoiceorders"][] = $companyOrder->id;
                }

            }

        }



        $this->stats["ready"]++;



    }





}