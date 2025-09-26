<?php

namespace GFUnit\development\fixscripts;

class CheckPrivateDelivery
{


    public function run() {

        $language = 5;

        // Load all private delivery orders
        $companyOrderList = \CompanyOrder::find_by_sql("select company_order.*, cardshop_settings.language_code from company_order, cardshop_settings where order_no not in ('BS71733','BS71734','BS71764','BS71771','BS71769','BS71765','BS71756','BS71755','BS71666','BS71665','BS71664','BS71663','BS71662') && allow_delivery = 0 && expire_date in (SELECT expire_date  FROM `expire_date` WHERE `is_delivery` = 1) && company_order.shop_id = cardshop_settings.shop_id && cardshop_settings.language_code = ".$language."");

        echo "Found ".countgf($companyOrderList)." orders<br>";
        echo "BS nr;CVR;Virksomhed;Shop;Antal bestilt;Pt. sendt;Dato for afsendelse;Afventer afsendelse;<br>\r\n";
        foreach($companyOrderList as $companyOrder) {
            $this->processCompanyOrder($companyOrder);
        }


    }

    private function processCompanyOrder(\CompanyOrder $order) {

        $payState = "";

        // Find payment status
        $client = $this->getOrderWS($order->language_code);
        $orderStatus = $client->getStatus($order->order_no);
        if($orderStatus == null) {
            $payState = "No data on order in nav";
        }
        else if(intval($orderStatus->getRemPrepaymentAmountLCY()) > 0) {
            $payState = "Amount due: ".intval($orderStatus->getRemPrepaymentAmountLCY());
        } else {
            return;
        }


        // Find shipments in shipment table
        $shipmentList = \Shipment::find_by_sql("SELECT * FROM shipment WHERE shipment_type = 'privatedelivery' && companyorder_id = ".$order->id);
        if(count($shipmentList) == 0) return;


        $states = array();
        foreach($shipmentList as $shipment) {

            $state = $shipment->shipment_state;
            $handler = $shipment->handler;
            $date = ($shipment->shipment_sync_date == null) ? "ingen dato" : $shipment->shipment_sync_date->format("d-m-Y H:i");

            if(!isset($states[$state])) {
                $states[$state] = array("count" => 1, "handler" => array($handler),"dates" => array($date));
            } else {

                $states[$state]["count"]++;
                if(!in_array($handler,$states[$state]["handler"])) $states[$state]["handler"][] = $handler;
                if(!in_array($date,$states[$state]["dates"])) $states[$state]["dates"][] = $date;

            }

        }

        /*
        // DK
        if(!isset($states[6]) || $states[6]["count"] == 0) {
            return;
        }

        $waitingCards = \ShopUser::find_by_sql("SELECT * FROM shop_user where company_order_id = ".$order->id." && delivery_state > 2");


        echo $order->order_no.";".$order->cvr.";".$order->company_name.";".$order->shop_name.";".$order->quantity.";".$states[6]["count"].";".implode(",",$states[6]["dates"]).";".countgf($waitingCards)."<br>\r\n";
*/
        // SE
        if(!isset($states[2]) || $states[2]["count"] == 0) {
            return;
        }

        $waitingCards = \ShopUser::find_by_sql("SELECT * FROM shop_user where company_order_id = ".$order->id." && delivery_state > 2");


        echo $order->order_no.";".$order->cvr.";".$order->company_name.";".$order->shop_name.";".$order->quantity.";".$states[2]["count"].";".implode(",",$states[2]["dates"]).";".countgf($waitingCards)."<br>\r\n";

        /*
        echo "<br>".$order->order_no." ".$order->company_name.": ".$payState;
        echo " - ".countgf($shipmentList)." shipments";
        echo "<br><pre>".print_r($states,true)."</pre><br>";
        */
    }

    private $orderWs = array();

    private function getOrderWS($countryCode)
    {
        if(intval($countryCode) <= 0) {
            throw new \Exception("Trying to create order service with no nav country");
        }
        if(isset($this->orderWs[intval($countryCode)])) {
            return $this->orderWs[intval($countryCode)];
        }
        $this->orderWs[intval($countryCode)] = new \GFCommon\Model\Navision\OrderStatusWS(intval($countryCode));
        return $this->orderWs[intval($countryCode)];
    }


}