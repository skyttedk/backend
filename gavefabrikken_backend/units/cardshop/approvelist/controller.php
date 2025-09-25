<?php

namespace GFUnit\cardshop\approvelist;
use GFBiz\Model\Cardshop\BlockMessageLogic;
use GFBiz\units\UnitController;

class Controller extends UnitController
{

    public function __construct()
    {
        parent::__construct(__FILE__);

    }
    
    /**
     * SERVICES
     */

    public function opencount($country_code=0)
    {
        $openList = BlockMessageLogic::getBlockMessages(null,null,null,true,false,0,0,$country_code);
        $shipmentList = BlockMessageLogic::getBlockMessages(null,null,-1,true,false,0,0,$country_code);

        echo json_encode(array("status" => 1, "blockcount" => countgf($openList),"shipmentblock" => count($shipmentList)));
    }

    public function getopen($type="all",$tech=0,$country_code=0)
    {

        $companyid = null;
        $companyorderid = null;
        $shipmentid = null;

        if($type == "company") {
            $companyid = -1;
        } else if($type == "order") {
            $companyorderid = -1;
        } else if($type == "shipment") {
            $shipmentid = -1;
        }

        if($tech == "all") $tech = null;

        $list = BlockMessageLogic::getBlockMessages($companyid,$companyorderid,null,true,false,$tech,0,$country_code);
        $filteredList = array();


        foreach($list as $item) {

            $objectType = "";
            if($item->shipment_id > 0) $objectType = "shipment";
            else if($item->company_order_id > 0) $objectType = "order";
            else $objectType = "company";

            //echo $type." = ".$itemType."\r\n";

            if($type == "all" || $type == "alle" || $type == $objectType) {
                if($objectType != "shipment") {
                    $filteredList[] = $item;
                }
            }
        }

        BlockMessageLogic::outputBlockMessageList($filteredList,$country_code);

    }

    public function getclosed($type="all",$tech=0,$country_code=0)
    {
        $companyid = null;
        $companyorderid = null;
        $shipmentid = null;

        if($type == "company") {
            $companyid = -1;
        } else if($type == "order") {
            $companyorderid = -1;
        } else if($type == "shipment") {
            $shipmentid = -1;
        }

        if($tech == "all") $tech = null;

        $list = BlockMessageLogic::getBlockMessages($companyid,$companyorderid,$shipmentid,false,true,$tech,0,$country_code);
        BlockMessageLogic::outputBlockMessageList($list);

    }

    public function getcompany($companyid,$tech=0,$closed=0)
    {

        $companyid = intval($companyid);
        $companyorderid = 0;
        $shipmentid = 0;

        if($companyid <= 0) {
            throw new \Exception("Company id must be a positive number.");
        }

        if($tech == "all") $tech = null;

        $isClosed = false;
        if($closed == 1) $isClosed = true;

        $list = BlockMessageLogic::getBlockMessages($companyid,$companyorderid,$shipmentid,true,$isClosed,$tech);
        BlockMessageLogic::outputBlockMessageList($list);

    }

    public function getorder($companyorderid,$tech=0,$closed=0)
    {

        $companyid = null;
        $companyorderid = intval($companyorderid);
        $shipmentid = 0;

        if($companyorderid <= 0) {
            throw new \Exception("Company order id must be a positive number.");
        }

        if($tech == "all") $tech = null;

        $isClosed = false;
        if($closed == 1) $isClosed = true;

        $list = BlockMessageLogic::getBlockMessages($companyid,$companyorderid,$shipmentid,true,$isClosed,$tech);
        BlockMessageLogic::outputBlockMessageList($list);

    }

    public function getshipment($shipmentid,$tech=0,$closed=0)
    {

        $companyid = null;
        $companyorderid = 0;
        $shipmentid = intval($shipmentid);

        if($shipmentid <= 0) {
            throw new \Exception("Shipment id must be a positive number.");
        }

        if($tech == "all") $tech = null;

        $isClosed = false;
        if($closed == 1) $isClosed = true;

        $list = BlockMessageLogic::getBlockMessages($companyid,$companyorderid,$shipmentid,true,$isClosed,$tech);
        BlockMessageLogic::outputBlockMessageList($list);

    }

    public function approve($blockMessageID)
    {

        // Check action is set
        if(!isset($_POST["action"]) && trimgf($_POST["action"]) != "") {
            throw new \Exception("No action provided");
        }
        
        $action = mb_strtolower(trimgf($_POST["action"]));
        BlockMessageLogic::approveBlockMessage($blockMessageID,$action);

    }

}