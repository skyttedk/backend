<?php

namespace GFUnit\navision\syncexternalshipment;
use GFUnit\navision\syncshipment\ShipmentSync;

class ExternalShipmentSync
{

    private $shipmentWS = array();

    public function runcheck($output)
    {

        // Find shipments waiting
        $sql = "SELECT * FROM `shipment` WHERE shipment_type != 'directdelivery' && shipment_type != 'privatedelivery' && from_certificate_no > 0 && companyorder_id in (select id from company_order where order_state > 3 && shop_id in (select shop_id from cardshop_settings where language_code in (4,5))) && shipment_state in (2,5) && handle_country > 0 order by RAND() desc";
        $shipmentList = \Shipment::find_by_sql($sql);

        if($output) echo "Found ".countgf($shipmentList)." to check<br>";

        $startTime = time();

        $completed = 0;
        $notcompleted = 0;
        $failed = 0;

        $processNow = array(66092,64135,65070,65229,65796,65897,65899,66049,66058,66070,66076,66091);

        // Foreach shipment
        foreach($shipmentList as $shipobj)
        {

            // Run for max 40 seconds
            if(time() - $startTime > 40) {
                echo "TIMEOUT, abort for now!";
                return;
            }

            $shipment = \Shipment::find($shipobj->id);
            if($output) echo "<br>Checking shipment ".$shipment->id."<br>";

            try {

                $externalShipmentWS = $this->getShipmentWS($shipment->handle_country);
                $result = $externalShipmentWS->getByCertificateNo($shipment->from_certificate_no);
                
                if($result->getExternalShipmentComplete() || in_array($shipobj->id,$processNow)) {

                    if($output) echo "HANDLING COMPLETED, SYNC TO ORIGIN COUNTRY!<br>";

                    // Save shipment
                    $shipment->shipment_state = 5;
                    $shipment->save();

                    // Sync shipment again
                    $model = new ShipmentSync($output);
                    $model->syncShipment($shipment);

                    // Commit database
                    \system::connection()->commit();
                    \System::connection()->transaction();

                    $completed++;

                    //return;

                } else {
                    $notcompleted++;
                }

            } catch(\Exception $e) {
                echo "Not found in nav ".$shipment->id." (".$shipment->shipment_type."): ".$e->getMessage()."<br>";
                $failed++;
            }

        }

        echo "Completed: ".$completed.", not completed: ".$notcompleted.", failed: ".$failed;


    }

    /**
     * @param $countryCode
     * @return \GFCommon\Model\Navision\ExternalShipStatusWS
     * @throws \Exception
     */
    private function getShipmentWS($countryCode)
    {
        if(intval($countryCode) <= 0) {
            throw new \Exception("Trying to create order service with no nav country");
        }
        if(isset($this->shipmentWS[intval($countryCode)])) {
            return $this->shipmentWS[intval($countryCode)];
        }
        $this->shipmentWS[intval($countryCode)] = new \GFCommon\Model\Navision\ExternalShipStatusWS(intval($countryCode));
        return $this->shipmentWS[intval($countryCode)];
    }


}