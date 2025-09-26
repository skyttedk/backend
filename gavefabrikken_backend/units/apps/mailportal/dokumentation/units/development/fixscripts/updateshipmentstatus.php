<?php

namespace GFUnit\development\fixscripts;

use GFCommon\Model\Navision\ShipmentsWS;

class UpdateShipmentStatus
{

    private $stats;


    public function run()
    {

        echo "RUN SHIPMENT STATUS<br>";

        $sql = "SELECT * FROM shipment WHERE handler = 'navision' && isshipment = 1 && shipment_state in (5,6,7) && nav_order_no IS NULL LIMIT 250";
        $shipmentList = \Shipment::find_by_sql($sql);

        if(count($shipmentList) == 0) {
            echo "No shipment found";
            return;
        }

        echo "Found ".count($shipmentList)." shipment(s) to update<br>";

        $client = new ShipmentsWS();
        
        foreach($shipmentList as $shipment) {
            $this->updateShipment($shipment,$client);
        }

        echo "Done processing shipments";

        ?><script>
        window.onload = function() {
            setTimeout(function() {
                window.location.reload(true);
            }, 15000); // 15000 millisekunder = 15 sekunder
        };

    </script><?php

        \response::silentsuccess();


    }

    private function updateShipment($shipment,$client) {

        echo "PROCESS: ".$shipment->id."<br>";
        
        try {

            $result = $client->getByShipment($shipment);
            
            if($result == null || count($result) == 0) {
                echo " - IKke fundet<br>";
                return;
            }

            if(count($result) > 1) {
                echo "Fandt flere status?<br><pre>".print_r($result,true)."</pre>";
                exit();
            }

            $shipStatus = $result[0];
            echo "Consignor date: ".strtotime($shipStatus->getConsignorCreatedAt())." / ".$shipStatus->getConsignorCreatedAt()."<br>";
            echo "Consignor label: ".$shipStatus->getConsignorLabelNo()."<br>";
            echo "Nav order no: ".$shipStatus->getShipmentOrderNo()."<br>";


            // Load shipment
            $shipmentObj = \Shipment::find($shipment->id);
            $consignorCreated = strtotime($shipStatus->getConsignorCreatedAt());

            $updates = 0;

            if(trim($shipStatus->getConsignorLabelNo()) != "") {
                $updates++;
                $shipmentObj->consignor_labelno = $shipStatus->getConsignorLabelNo();
            }

            if(trim($shipStatus->getShipmentOrderNo()) != "") {
                $updates++;
                $shipmentObj->nav_order_no = $shipStatus->getShipmentOrderNo();
            }

            if($consignorCreated > 0) {
                $updates++;
                $shipmentObj->consignor_created = date("Y-m-d H:i:s",$consignorCreated);
            }

            if($updates == 0 || trimgf($shipmentObj->nav_order_no) == "") {
                $shipmentObj->nav_order_no = "NOUPDATES";
            }

            $shipmentObj->save();


        } catch (\Exception $e) {
            echo "ERROR: ".$e->getMessage()."<br>";
            $shipmentObj = \Shipment::find($shipment->id);
            $shipmentObj->nav_order_no = "ERROR: ".$e->getMessage();
            $shipmentObj->save();
            return;
        }

    }

}