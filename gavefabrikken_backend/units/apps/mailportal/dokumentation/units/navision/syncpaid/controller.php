<?php

namespace GFUnit\navision\syncpaid;
use GFBiz\units\UnitController;

class Controller extends UnitController
{

    public function __construct()
    {
        parent::__construct(__FILE__);
    }

    public function syncpayments()
    {

        \GFCommon\DB\CronLog::startCronJob("NavCSSyncPaid");

        // Sync payment state on companyorder
        $this->syncPaymentLanguage(1, true);
        $this->syncPaymentLanguage(4, false);
        $this->syncPaymentLanguage(5, false);

        \GFCommon\DB\CronLog::endCronJob(1,"OK");


    }

    public function syncpaymentlang($lang) {

        if(intval($lang) <= 0) {
            throw new \Exception("No language code");
        } else if(intval($lang) > 10) {
            throw new \Exception("Language code to high");
        }

        $this->syncPaymentLanguage(intval($lang), false);
    }

    public function syncearlyorders()
    {
        // Sync earlyorders for release
        //$this->enableEarlyOrders(1);
        //$this->enableEarlyOrders(4);
        //$this->enableEarlyOrders(5);

    }

    private function syncPaymentLanguage($languageCode,$enableEarlyOrders=false) {

        echo "<h2>Syncing payment state for companyorders in language ".$languageCode."</h2>";

        // Check selectors used as or statements to select orders
        $checkSelectors = array(
            "(id in (select companyorder_id from shipment where shipment_type = 'earlyorder' && shipment_state = 1))"

        );

        // Load orders to check
        $sql = "SELECT * FROM company_order where order_state in (4,5,9,10) && nav_paid = 0 && (prepayment = 1 or order_state = 10) && shop_id in (select shop_id from cardshop_settings where language_code = ".intval($languageCode).") && (".implode(" OR ",$checkSelectors).");";


        $orderList = \CompanyOrder::find_by_sql($sql);
        echo "FOUND ".count($orderList)." ORDERS<br><br>";

        // Mark orders as paid
        foreach($orderList as $order) {
            try {

                echo "Syncing order ".$order->id."<br>";
                $this->syncPaymentOrder($languageCode,$order->id);

            } catch(\Exception $e) {
                echo "Error: ".$e->getMessage()."<br>";
            }
        }

        // Complete transaction and start a new
        \System::connection()->commit();
        \System::connection()->transaction();

    }

    private function syncPaymentOrder($languageCode,$companyOrderId) {

        $companyOrder = \CompanyOrder::find($companyOrderId);
        if(!($companyOrder->id == $companyOrderId && in_array($companyOrder->order_state, array(4,5,9,10)))) {
            echo "- validation error<br>";
            return;
        }

        $client = $this->getOrderWS($languageCode);
        $orderStatus = $client->getStatus($companyOrder->order_no);

        if($orderStatus == null) {
            echo " - did not get a valid navision response, try later<br>";
            return;
        }
        else if(intval($orderStatus->getRemPrepaymentAmountLCY()) > 0) {
            echo " - due amount not paid, try again later<br>";
            return;
        }

        // Paid ok
        echo " - mark as paid<br>";
        $companyOrder->nav_paid = 1;
        $companyOrder->save();

    }

    private function enableEarlyOrders($languageCode) {

        echo "<h2>Syncing earlyorders for release ".$languageCode."</h2>";

        $holdBackItems = array(
            1 => array("SAM4178","SAM4164","230139"),
            4 => array("230139","SAM4164","SAM4178","230139-EFTERLEV"),
            5 => array("230139","SAM4178","SAM4164","230139-EFTERLEV")
        );

        $sql = "select shipment.* 
        from shipment, company_order, cardshop_settings, company 
        where 
            shipment.companyorder_id = company_order.id && 
            company_order.shop_id = cardshop_settings.shop_id &&
            company.id = company_order.company_id && 
            
            cardshop_settings.language_code = ".intval($languageCode)." && 
            
            shipment.shipment_type = 'earlyorder' && 
            shipment.shipment_state = 1 && 
            shipment.force_syncnow = 0 && 
            
            company_order.shipment_on_hold = 0 && 
            company_order.nav_lastsync <= NOW() - INTERVAL 48 HOUR &&
            
            (company_order.prepayment = 0 or company_order.nav_paid = 1 or company_order.allow_delivery = 1 or company.allow_delivery = 1) && (itemno not in ('".implode("','",$holdBackItems[$languageCode])."') and itemno2  not in ('".implode("','",$holdBackItems[$languageCode])."') and itemno3 not in ('".implode("','",$holdBackItems[$languageCode])."') and itemno4 not in ('".implode("','",$holdBackItems[$languageCode])."') and itemno5 not in ('".implode("','",$holdBackItems[$languageCode])."'))";



        $shipmentList = \Shipment::find_by_sql($sql);
        echo "FOUND ".count($shipmentList)." SHIPMENTS<br><br>";

        foreach($shipmentList as $shipment) {

            $ship = \Shipment::find($shipment->id);

            try {
                echo "Enable early order ".$ship->id."<br>";
                $ship->force_syncnow = 1;
                $ship->handle_country = 1;
                $ship->save();
            } catch(\Exception $e) {
                echo "Error: ".$e->getMessage()."<br>";
            }
        }


        // Complete transaction and start a new
        \System::connection()->commit();
        \System::connection()->transaction();

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