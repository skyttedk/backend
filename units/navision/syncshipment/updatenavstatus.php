<?php

namespace GFUnit\navision\syncshipment;
use GFBiz\units\UnitController;
use GFCommon\Model\Navision\CustomerObj;
use GFCommon\Model\Navision\CustomerWS;
use GFCommon\Model\Navision\CustomerXML;
use GFCommon\Model\Navision\NavClient;
use GFCommon\Model\Navision\OrderWS;
use GFCommon\Model\Navision\OrderXML;
use GFCommon\Model\Navision\ShipmentsWS;
use GFCommon\Model\Navision\ShipmentXML;
use GFCommon\Model\Navision\Shipment2XML;

class UpdateNavStatus
{

    public function runUpdateNavStatus()
    {

        \GFCommon\DB\CronLog::startCronJob("NavShipmentStatus");

        echo "RUN SHIPMENT STATUS SYNC:<br>";

        // Get shipment status changes
        try {
            $client = new ShipmentsWS();
            $shipmentStatus = $client->getLastUpdated(6*60,500);
        } catch (\Exception $e) {
            \GFCommon\DB\CronLog::endCronJob(3, "Nav kald fejl", $e->getMessage());
            return;
        }

        echo "FOUND ".count($shipmentStatus)." SHIPMENT STATUS UPDATES:";

        // Nothing to do, exit
        if(count($shipmentStatus) == 0) {
            \GFCommon\DB\CronLog::endCronJob(10,"Nothing to do");
            return;
        }

        foreach($shipmentStatus as $shipment) {
            $this->updateShipment($shipment);
        }

        \GFCommon\DB\CronLog::endCronJob(1,"OK");
        \response::silentsuccess();
    }

    private function updateShipment($shipmentStatus)
    {


        echo "UPDATE";
        echo "<pre>".print_r($shipmentStatus,true)."</pre>";

        try {
            $companyOrder = \CompanyOrder::find_by_order_no($shipmentStatus->getOrderNo());

            if($companyOrder->id == 0) {
                throw new \Exception("Order not found");
            }

        } catch (\Exception $e) {
            echo "ERROR finding company order: ".$e->getMessage();
            return;
        }

        // Find shipment
        $sql = "SELECT * FROM shipment where companyorder_id = ".$companyOrder->id." && handler = 'navision' && (from_certificate_no = '".$shipmentStatus->getFromGiftCardNo()."' OR id = ".$shipmentStatus->getFromGiftCardNo().")";
        $shipments = \Shipment::find_by_sql($sql);

        if(count($shipments) == 0) {
            echo "ERROR: no shipment<br>";
            return;
        }

        if(count($shipments) > 1) {
            echo "ERROR: multiple shipments<br>";
            return;
        }

        echo "FOUND SHIPMENT ID: ".$shipments[0]->id."<br>";

        // Load shipment
        $shipmentObj = \Shipment::find($shipments[0]->id);
        $consignorCreated = strtotime($shipmentStatus->getConsignorCreatedAt());

        $updates = 0;

        if(trim($shipmentStatus->getConsignorLabelNo()) != "") {
            $updates++;
            $shipmentObj->consignor_labelno = $shipmentStatus->getConsignorLabelNo();
        }

        if($consignorCreated > 0) {
            $updates++;
            $shipmentObj->consignor_created = date("Y-m-d H:i:s",$consignorCreated);
        }

        if(trim($shipmentStatus->getShipmentOrderNo()) != "") {
            $updates++;
            $shipmentObj->nav_order_no = $shipmentStatus->getShipmentOrderNo();
        }

        if($updates > 0) {
            echo "SAVED<br>";
            $shipmentObj->save();
        }

    }

}
