<?php

namespace GFUnit\cardshop\earlyorder;
use GFBiz\units\UnitController;

class Controller extends UnitController
{

    private $cardModel;

    public function __construct()
    {
        parent::__construct(__FILE__);
        $this->cardModel = new \GFBiz\Model\Cardshop\ShipmentLogic("earlyorder");
    }

    /**
     * SERVICES
     */

    public function company($companyid=0)
    {
        $shipmentList = $this->cardModel->getCompanyShipments(intval($companyid));
        $this->cardModel->outputList($shipmentList);
    }

    public function companyorder($companyorderid=0)
    {
        $shipmentList = $this->cardModel->getCompanyOrderShipments(intval($companyorderid));
        $this->cardModel->outputList($shipmentList);
    }

    public function get($shipmentId)
    {
        $shipment = \Shipment::find(intval($shipmentId));
        if($shipment->shipment_type != $this->cardModel->getShipmentType()) {
            throw new \Exception("Invalid shipment type.");
        }
        echo json_encode(array("status" => 1, "shipment" => $this->cardModel->mapToJson($shipment)),JSON_PRETTY_PRINT);
    }

    public function create()
    {

        $note = [];
        $_POST["shipmentdata"]["shipto_name"]       == "" ?  array_push($note,"shipto_name") : "";
        $_POST["shipmentdata"]["shipto_address"]    == "" ?  array_push($note,"shipto_address") : "";
        $_POST["shipmentdata"]["shipto_postcode"]   == "" ?  array_push($note,"shipto_postcode") : "";
        $_POST["shipmentdata"]["shipto_city"]       == "" ?  array_push($note,"shipto_city") : "";
        $_POST["shipmentdata"]["shipto_country"]    == "" ?  array_push($note,"shipto_country") : "";
        $_POST["shipmentdata"]["shipto_contact"]    == "" ?  array_push($note,"shipto_contact") : "";
        $_POST["shipmentdata"]["shipto_email"]      == "" ?  array_push($note,"shipto_email") : "";
        $_POST["shipmentdata"]["shipto_phone"]      == "" ?  array_push($note,"shipto_phone") : "";

        \Dbsqli::setSql2("INSERT INTO debug_log (note,debug) VALUES ('".addslashes(json_encode($note))."','".addslashes(json_encode($_POST["shipmentdata"] ))."')");

        $this->requirePost();
        if(!isset($_POST["shipmentdata"])) {
            throw new \Exception("No shipment data provided");
        }
        $shipment = $this->cardModel->createShipment($_POST["shipmentdata"]);
        echo json_encode(array("status" => 1, "shipment" => $this->cardModel->mapToJson($shipment)),JSON_PRETTY_PRINT);

    }

    public function update($shipmentId)
    {
        try {
            $this->requirePost();
            if(intval($shipmentId) <= 0) {
                throw new \Exception("No shipment id provided");
            }
            if(!isset($_POST["shipmentdata"])) {
                throw new \Exception("No shipment data provided");
            }
            $shipment = $this->cardModel->updateShipment($shipmentId,$_POST["shipmentdata"]);
        }
        catch (\Exception $e) {
            echo json_encode(array("status" => 0,"message" => $e->getMessage()));
            return;
        }

        echo json_encode(array("status" => 1, "shipment" => $this->cardModel->mapToJson($shipment)),JSON_PRETTY_PRINT);
    }

    public function sendshipment($shipmentId=0)
    {
        $this->requirePost();
        $this->cardModel->sendShipment($shipmentId);
        \system::connection()->commit();
    }

    public function sendcompanyorder($companyorderid)
    {
        $shipmentList = $this->cardModel->getCompanyOrderShipments($companyorderid);
        $successCount = 0;
        $failCount = 0;

        foreach($shipmentList as $shipment) {
            if($this->cardModel->sendShipment($shipment->id,false)) {
                $successCount++;
            } else {
                $failCount++;
            }
        }

        \system::connection()->commit();
        echo json_encode(array("status" => (($successCount > 0 && $failCount > 0) ? 0 : 1),"ok_count" => $successCount,"fail_count" => $failCount));
    }

    public function cancelshipment($shipmentId=0)
    {
        $this->requirePost();
        $this->cardModel->cancelShipment($shipmentId);
        \system::connection()->commit();
    }

    public function cancelcompanyorder($companyorderid)
    {

        $shipmentList = $this->cardModel->getCompanyOrderShipments($companyorderid);
        $successCount = 0;
        $failCount = 0;

        foreach($shipmentList as $shipment) {
            if($this->cardModel->cancelShipment($shipment->id,false)) {
                $successCount++;
            } else {
                $failCount++;
            }
        }

        \system::connection()->commit();
        echo json_encode(array("status" => (($successCount > 0 && $failCount > 0) ? 0 : 1),"ok_count" => $successCount,"fail_count" => $failCount));

    }

    public function delete($shipmentId=0)
    {
        $this->requirePost();
        $shipment = $this->cardModel->deleteShipment($shipmentId);
        \system::connection()->commit();
        echo json_encode(array("status" => 1, "shipment" => $this->cardModel->mapToJson($shipment)),JSON_PRETTY_PRINT);
    }

    public function reopen($shipmentId=0)
    {
        $this->requirePost();
        $shipment = $this->cardModel->reopenShipment($shipmentId);
        \system::connection()->commit();
        echo json_encode(array("status" => 1, "shipment" => $this->cardModel->mapToJson($shipment)),JSON_PRETTY_PRINT);
    }


}