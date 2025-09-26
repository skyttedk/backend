<?php

namespace GFUnit\cardshop\cardshipment;
use GFBiz\units\UnitController;

class Controller extends UnitController
{

    private $cardModel;

    public function __construct()
    {
        parent::__construct(__FILE__);
        $this->cardModel = new \GFBiz\Model\Cardshop\ShipmentLogic("giftcard");
    }

    /**
     * SERVICES
     */

    public function company($companyid=0)
    {
        $shipmentList = $this->cardModel->getCompanyShipments(intval($companyid));
        $this->cardModel->outputList($shipmentList);
    }

    public function companyorder($companyOrderId=0)
    {
        $shipmentList = $this->cardModel->getCompanyOrderShipments(intval($companyOrderId));
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

        $rawInput = file_get_contents('php://input');
        if(trimgf($rawInput) != "" && countgf($_POST) == 0) {
            $rawData = json_decode($rawInput,true);
            if($rawData != null) {
                $_POST = $rawData;
            }
        }

        $this->requirePost();
        if(!isset($_POST["shipmentdata"])) {
            throw new \Exception("No shipment data provided");    
        }
        $shipment = $this->cardModel->createShipment($_POST["shipmentdata"]);
        echo json_encode(array("status" => 1, "shipment" => $this->cardModel->mapToJson($shipment)),JSON_PRETTY_PRINT);
    }

    public function createjson()
    {
        $entityBody = file_get_contents('php://input');
        $data = json_decode($entityBody,true);

        if($data == null) {
            echo "INVALID POST DATA";
            exit();
        }

        $_POST = $data;
        print_r($data);
        $this->create();

    }

    public function update($shipmentId)
    {
        $this->requirePost();
        if(intval($shipmentId) <= 0) {
            throw new \Exception("No shipment id provided");
        }
        if(!isset($_POST["shipmentdata"])) {
            throw new \Exception("No shipment data provided");
        }
        $shipment = $this->cardModel->updateShipment($shipmentId,$_POST["shipmentdata"]);
        echo json_encode(array("status" => 1, "shipment" => $this->cardModel->mapToJson($shipment)),JSON_PRETTY_PRINT);
    }

    public function send($shipmentId=0)
    {
        $this->requirePost();
        $this->cardModel->sendShipment($shipmentId);
        \system::connection()->commit();
    }

    public function sendcompanyorder($companyorderid)
    {
        $companyorder = \CompanyOrder::find($companyorderid);
        $companyorder->shipment_on_hold = 0;
        $companyorder->save();

        $shipmentList = $this->cardModel->getCompanyOrderShipments($companyorderid);


        if(count($shipmentList) == 0) {
                ob_start();
                $this->createdefault($companyorderid,true);
                ob_end_clean();
                echo json_encode(array("status" => 1,"ok_count" => 1,"fail_count" => 0));
        }
        else {
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



    }

    public function cancel($shipmentId=0)
    {
        $this->requirePost();
        $this->cardModel->cancelShipment($shipmentId);
        \system::connection()->commit();
    }

    public function cancelcompanyorder($companyorderid)
    {

        $companyorder = \CompanyOrder::find($companyorderid);
        $companyorder->shipment_on_hold = 1;
        $companyorder->save();

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

    public function createdefault($companyOrderId=0,$send=0)
    {
        $this->requirePost();
        $shipment = $this->cardModel->createDefault($companyOrderId,$send==1);
        \system::connection()->commit();
        echo json_encode(array("status" => 1, "shipment" => $this->cardModel->mapToJson($shipment)),JSON_PRETTY_PRINT);

    }

    public function checkcards($companyOrderId=0)
    {
        $state = $this->cardModel->checkGiftCards($companyOrderId);
        $problems = $this->cardModel->getCardProblems();
        echo json_encode(array("status" => countgf($problems) == 0 ? 1 : 0,"problems" => $problems));
    }

    public function nextnumbers($companyOrderId=0,$quantity=1)
    {
        $this->cardModel->checkGiftCards($companyOrderId);

        if($quantity <= 0) {
            echo json_encode(array("status" => 0,"error" => "Invalid quantity"));
            return;
        }

        // Next numbers
        $nextCard = $this->cardModel->getNextCard();
        $lastCard = $nextCard+intval($quantity)-1;

        $allCards = true;
        $orderMax = $this->cardModel->getCardMax();
        if($lastCard > $orderMax) {
            $allCards = false;
            $lastCard = $orderMax;
        }

        echo json_encode(array("status" => 1,"cardstart" => $nextCard,"cardend" => $lastCard,"quantity_changed" => !$allCards,"requested_quantity" => $quantity,"fetched_quantity" => ($lastCard-$nextCard+1),"cards_left" => ($orderMax - $lastCard)));

    }

}
