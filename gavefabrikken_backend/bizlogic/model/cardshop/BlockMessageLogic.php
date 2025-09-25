<?php

namespace GFBiz\Model\Cardshop;

use GFCommon\Model\Navision\CustomerWS;
use GFUnit\navision\synccompany\CompanySync;

class BlockMessageLogic
{
    
    public static function getReturnData($blockMessage,$utf8encode=false)
    {

        $objectType = "";
        if($blockMessage->shipment_id > 0) $objectType = "shipment";
        else if($blockMessage->company_order_id > 0) $objectType = "order";
        else $objectType = "company";

        $blockTypeName = "";
        $blockType = \BlockMessage::getBlockType($blockMessage->block_type);

        $actionList = array();
        if($blockMessage->release_status == 0)
        {
            foreach($blockType["actions"] as $action) {
                $actionList[] = \BlockMessage::getAction($action);
            }
        }

        $data = array(
            "id" => $blockMessage->id,
            "object_type" => $objectType,
            "company_id" => $blockMessage->company_id,
            "company_name" => trimgf(trimgf($blockMessage->company_name1) != "" ? $blockMessage->company_name1 : $blockMessage->company_name2),
            "company_order_id" => $blockMessage->company_order_id,
            "order_no" => $blockMessage->order_no,
            "shipment_id" => $blockMessage->shipment_id,
            "block_type" => $blockMessage->block_type,
            "block_type_name" => trimgf($blockType["description"]),
            "created_by" => $blockMessage->created_by,
            "created_by_name" => trimgf($blockMessage->created_by_name),
            "created_date" => $blockMessage->created_date->format("d-m-Y H:i"),
            "description" => utf8_decode($blockMessage->description),
            "release_status" => $blockMessage->release_status,
            "release_date" => ($blockMessage->release_date == null ? null : $blockMessage->release_date->format("d-m-Y H:i")),
            "release_user" => $blockMessage->release_user,
            "release_user_name" => trimgf($blockMessage->release_user_name),
            "release_message" => trimgf($blockMessage->release_message),
            "debug_data" => utf8_decode(trimgf($blockMessage->debug_data)),
            "tech_block" => $blockMessage->tech_block,
            "actions" => $actionList
        );

        if($utf8encode == true) {
            $data["description"] = utf8_encode($data["description"]);
            $data["debug_data"] = utf8_encode($data["debug_data"]);
        }

        $data["debug_data"] = nl2br($data["debug_data"]);


        if(json_encode($data) == null) {
            if($utf8encode == true) {
                $data["description"] = "Encoding fejl, kontakt: sc@interactive.dk";
                $data["debug_data"] = "";
            } else {
                return self::getReturnData($blockMessage,true);
            }

        }

        return $data;
    }



    public static function getReturnListData($blockMessageList)
    {
        $data = array();
        if(count($blockMessageList) > 0) {
            foreach($blockMessageList as $blockMessage) {
                $data[] = self::getReturnData($blockMessage);
            }    
        }
        return $data;
    }

    public static function outputBlockMessageList($blockMessageList,$country_code=0)
    {
        header('Content-type: application/json');
        $shipmentList = BlockMessageLogic::getBlockMessages(null,null,-1,true,false,0,0,$country_code);

        echo json_encode(array("status" => 1, "blocklist" => self::getReturnListData($blockMessageList),"shipmentblock" => count($shipmentList)));
    }
    
    public static function getBlockMessages($company_id=null,$company_order_id=null,$shipment_id=null,$is_open,$is_closed,$is_tech=null,$id = 0,$country_code=0)
    {

        // Generate sql for query
        $sql = "SELECT blockmessage.*, GREATEST(blockmessage.company_id,IF(c1.id IS NULL,0,c1.id),IF(c2.id IS NULL,0,c2.id)) as company_id, created_user.name as created_by_name, updated_user.name as release_user_name, c1.name as company_name1, c2.name as company_name2, company_order.order_no FROM `blockmessage` LEFT JOIN company as c1 ON c1.id = blockmessage.company_id LEFT JOIN company_order ON company_order.id = blockmessage.company_order_id LEFT JOIN company as c2 ON c2.id = company_order.company_id LEFT JOIN system_user as created_user ON created_user.id = blockmessage.created_by LEFT JOIN system_user as updated_user ON updated_user.id = blockmessage.release_user ";
        $sql .= " WHERE blockmessage.id > 0";

        // Company id
        if($company_id == -1) $sql .= " && (c1.id > 0 || c2.id > 0)";
        else if($company_id !== null) $sql .= " && (c1.id = ".intval($company_id)." || c2.id = ".intval($company_id).")";

        // Company order id
        if($company_order_id == -1) $sql .= " && company_order_id > 0";
        else if($company_order_id !== null) $sql .= " && company_order_id = ".intval($company_id);

        // Shipment id
        if($shipment_id == -1) $sql .= " && shipment_id > 0";
        //else if($shipment_id === null) $sql .= " && shipment_id = 0";
        else if($shipment_id !== null) $sql .= " && shipment_id = ".intval($shipment_id);

        // Open / closed
        if($is_open != $is_closed) {
            if($is_open) $sql .= " && release_status = 0";
            else if($is_closed) $sql .= " && release_status > 0";
        }

        // Tech
        if($is_tech !== null) {
            $sql .= " && tech_block = ".intval($is_tech);
        }

        // ID
        if($id > 0) {
            $sql .= " && blockmessage.id = ".intval($id);
        }

        // Country code
        if($country_code > 0) {
            $sql .= " && (c2.language_code = ".intval($country_code)." OR c1.language_code = ".intval($country_code).")";
        }

        $sql .= " ORDER BY company_id asc, company_order_id asc, shipment_id asc, id asc";

      //  echo $sql;
        // Get items and return
        return \BlockMessage::find_by_sql($sql);

    }

    public static function countOpenBlocksOnObject($company_id,$company_order_id,$shipment_id,$notBlockMessageID)
    {
        $sql = "SELECT * FROM blockmessage WHERE release_status = 0 && id != ".intval($notBlockMessageID);
        if($company_id !== null) $sql .= " && company_id = ".intval($company_id);
        if($company_order_id !== null) $sql .= " && company_order_id = ".intval($company_order_id);
        if($shipment_id !== null) $sql .= " && shipment_id = ".intval($shipment_id);

        return countgf(\BlockMessage::find_by_sql($sql));
    }


    /**
     * APPROVE FUNCTIONALITY
     */

    public static function approveBlockMessage($blockMessageID,$action)
    {

        $blockMessage = \BlockMessage::find(intval($blockMessageID));

        if($blockMessage == null || $blockMessage->id <= 0) {
            throw new \Exception("Could not find block message");
        }

        if($blockMessage->release_status > 0) {
            throw new \Exception("Block message already solved");
        }

        if(trimgf($action) == "") {
            throw new \Exception("No action provided for resolving block");
        }

        $blockType = \BlockMessage::getBlockType($blockMessage->block_type);
        $actionObj = null;

        foreach($blockType["actions"] as $actionCode) {
            if($actionCode == $action) {
                $actionObj = \BlockMessage::getAction($action);
            }
        }

        if($actionObj == null) {
            throw new \Exception("Could not find action to use on block");
        }

        if($actionObj["code"] == "approve") self::approveActionApproveOnly($blockMessage);
        else if($actionObj["code"] == "approvecompany") self::approveActionApproveCompany($blockMessage);
        else if($actionObj["code"] == "approveorder") self::approveActionApproveOrder($blockMessage);
        else if($actionObj["code"] == "approveship") self::approveActionApproveShipment($blockMessage);
        else if($actionObj["code"] == "blockship") self::approveActionBlockShipment($blockMessage);
        else if($actionObj["code"] == "companymismatchapprove") self::companyMismatchApprove($blockMessage);
        else if($actionObj["code"] == "companymismatchdecline") self::companyMismatchDecline($blockMessage);
        else if($actionObj["code"] == "companymismatchretry") self::companyMismatchRetry($blockMessage);
        else if($actionObj["code"] == "deletecompany") self::approveActionDeleteCompany($blockMessage);
        else if($actionObj["code"] == "deleteorder") self::approveActionDeleteOrder($blockMessage);
        else if($actionObj["code"] == "unlinkcompany") self::approveActionUnlinkCompany($blockMessage);

        else if($actionObj["code"] == "approvesp") self::approveActionApprovSalespersonChange($blockMessage);
        else if($actionObj["code"] == "cancelsp") self::approveActionCancelSalespersonChange($blockMessage);

        else if($actionObj["code"] == "approvecc") self::approveActionApprovCompanyNoChange($blockMessage);
        else if($actionObj["code"] == "cancelcc") self::approveActionCancelCompanyNoChange($blockMessage);


        else throw new \Exception("Unknown action, no approve performed.");

        \System::connection()->commit();
/*
        $blockMessage = self::getBlockMessages(null,null,null,true,true,null,$blockMessage->id);
        $blockMessage = $blockMessage[0];
*/        header('Content-type: application/json');
        echo json_encode(array("status" => 1, "okcount" => 1));
    }




    private static function approveActionApprovCompanyNoChange($blockMessage)
    {

        $message = "";
        $data = json_decode($blockMessage->debug_data,true);
        if(!isset($data["navno"]) || trimgf($data["navno"]) == "") {
            return;
        }

        $company = \Company::find($blockMessage->company_id);
        $navisionNo = intvalgf($data["navno"]);
        $languageId = 0;
        $canChange = true;

        $companyOrderList = \CompanyOrder::find_by_sql("select * from company_order where company_id = ".$company->id);
        foreach($companyOrderList as $companyOrder) {
            $order = \CompanyOrder::find($companyOrder->id);
            $settings = \CardshopSettings::find_by_sql("select * from cardshop_settings where shop_id = ".$order->shop_id);
            $languageId = $settings[0]->language_code;
            if(in_array($order->order_state, array(4,5,6,7,8,9,10))) {
                $canChange = false;
                break;
            }
        }

        if($canChange == false) {
            return;
        }

        if($languageId == 0) {
            $languageId = \router::$systemUser->language;
        }

        if($languageId == 0) {
           return;
        }

        // Check on nav
        try {

            $customerClient = new CustomerWS($languageId);
            $customer = $customerClient->getByCustomerNo($navisionNo);

        } catch (\Exception $e) {

        }

        if($customer == null) {
            return;
        }

        // Update company
        $message = "Kundenr ændret fra fra ".$company->nav_customer_no." til ".$navisionNo.": ".$customer->getName()." (cvr: ".$customer->getCVR().").";
        $company->nav_customer_no = $navisionNo;
        $company->save();

        // Update block
        $blockMessage->release_status = 1;
        $blockMessage->release_date = date('d-m-Y H:i:s');
        $blockMessage->release_user = \router::$systemUser == null ? 0 : \router::$systemUser->id;
        $blockMessage->release_message = "Action: approvechange - ".$message;
        $blockMessage->save();

    }

    private static function approveActionCancelCompanyNoChange($blockMessage)
    {
        $blockMessage->release_status = 1;
        $blockMessage->release_date = date('d-m-Y H:i:s');
        $blockMessage->release_user = \router::$systemUser == null ? 0 : \router::$systemUser->id;
        $blockMessage->release_message = "Action: cancelchange - Ændring af kundenr afvist.";
        $blockMessage->save();
    }



    private static function approveActionApprovSalespersonChange($blockMessage)
    {

        $message = "";
        $data = json_decode($blockMessage->debug_data,true);
        if(!isset($data["salesperson"]) || trimgf($data["salesperson"]) == "") {
            return;
        }

        $order = \CompanyOrder::find($blockMessage->company_order_id);
        if(!in_array($order->order_state, array(1,2,3,4,5,9))) {
            return;
        }

        $message = "Sælger ændret fra ".$order->salesperson." til ".$data["salesperson"].".";
        $order->salesperson = $data["salesperson"];
        $order->save();

        // Update block
        $blockMessage->release_status = 1;
        $blockMessage->release_date = date('d-m-Y H:i:s');
        $blockMessage->release_user = \router::$systemUser == null ? 0 : \router::$systemUser->id;
        $blockMessage->release_message = "Action: approvechange - ".$message;
        $blockMessage->save();

    }

    private static function approveActionCancelSalespersonChange($blockMessage)
    {
        $blockMessage->release_status = 1;
        $blockMessage->release_date = date('d-m-Y H:i:s');
        $blockMessage->release_user = \router::$systemUser == null ? 0 : \router::$systemUser->id;
        $blockMessage->release_message = "Action: cancelchange - Ændring af sælger er afvist.";
        $blockMessage->save();
    }


    private static function companyMismatchApprove($blockMessage)
    {

        $company = \Company::find($blockMessage->company_id);
        $message = "";

        if($company->nav_customer_no > 0) {
            $message = "Company already synced with nav, do not continue but approve block";
        } else {

            // Find best match
            $companySync = new CompanySync();
            $match = $companySync->lookupInNavision($company,true);
            if($match == null) {
                $message = "Could not find match, create in nav";
            } else {
                $company->nav_customer_no = $match->getCustomerNo();
                $company->save();
                $message = "Company synced to customer no: ".$match->getCustomerNo();
            }

        }

        self::approveActionApproveCompany($blockMessage,$message);

    }

    private static function companyMismatchDecline($blockMessage)
    {

        $company = \Company::find($blockMessage->company_id);
        $message = "";

        if($company->nav_customer_no > 0) {
            $message = "Company already synced with nav, do not continue but approve block";
        } else {
            $message = "Decline sync with existing, company should be created as a new customer.";
        }

        self::approveActionApproveCompany($blockMessage,$message);

    }

    private static function companyMismatchRetry($blockMessage)
    {

        $company = \Company::find($blockMessage->company_id);
        $company->company_state = 1;
        $company->save();

        self::approveActionApproveOnly($blockMessage,"Retry sync");

    }

    private static function approveActionApproveOnly($blockMessage,$message=null)
    {
        $blockMessage->release_status = 1;
        $blockMessage->release_date = date('d-m-Y H:i:s');
        $blockMessage->release_user = \router::$systemUser == null ? 0 : \router::$systemUser->id;
        $blockMessage->release_message = $message == null ? "Action: approveonly" : $message;
        $blockMessage->save();
    }

    private static function approveActionApproveCompany($blockMessage,$message="")
    {
        $message = trimgf($message);
        if($message != "") $message .= " ";

        // Check for other blocks
        if(self::countOpenBlocksOnObject($blockMessage->company_id,0,0,$blockMessage->id) == 0) {
            $company = \Company::find($blockMessage->company_id);
            if($company->company_state == 2 || $company->company_state == 6) {
                $message .= "company_state changed from ".$company->company_state." to 3";
                $company->company_state = 3;
                $company->save();
            } else {
                $message = "company_state was ".$company->company_state.", not changed";
            }
        } else {
            $message = "Company has other blocks, do not release yet.";
        }

        // Update block
        $blockMessage->release_status = 1;
        $blockMessage->release_date = date('d-m-Y H:i:s');
        $blockMessage->release_user = \router::$systemUser == null ? 0 : \router::$systemUser->id;
        $blockMessage->release_message = "Action: approvecompany - ".$message;
        $blockMessage->save();

    }

    private static function approveActionApproveOrder($blockMessage)
    {

        $message = "";

        // Check for other blocks
        if(self::countOpenBlocksOnObject(null,$blockMessage->company_order_id,null,$blockMessage->id) == 0) {
            $order = \CompanyOrder::find($blockMessage->company_order_id);
            if($order->order_state == 2) {

                $message = "order_state changed from ".$order->order_state." to 3";
                $order->order_state = 3;
                $order->save();

            } else {
                $message = "order_state was ".$order->order_state.", not changed";
            }

            if($order->nav_synced != 0) {
                $order->nav_synced = 0;
                $message .= ", reset nav_synced";
                $order->save();
            }
        } else {
            $message = "Order has other blocks, do not release yet.";
        }

        // Update block
        $blockMessage->release_status = 1;
        $blockMessage->release_date = date('d-m-Y H:i:s');
        $blockMessage->release_user = \router::$systemUser == null ? 0 : \router::$systemUser->id;
        $blockMessage->release_message = "Action: approveorder - ".$message;
        $blockMessage->save();

    }

    private static function approveActionApproveShipment($blockMessage)
    {

        $message = "";

        // Check for other blocks
        if(self::countOpenBlocksOnObject(null,null,$blockMessage->shipment_id,$blockMessage->id) == 0) {
            $shipment = \Shipment::find($blockMessage->shipment_id);
            if($shipment->shipment_state == 3 || $shipment->shipment_state == 9) {

                $message = "order_state changed from ".$shipment->shipment_state." to 1";
                $shipment->shipment_state = 1;
                $shipment->shipment_sync_date = null;
                $shipment->save();

            } else {
                $message = "order_state was ".$shipment->shipment_state.", not changed";
            }
        } else {
            $message = "Shipment has other blocks, do not release yet.";
        }

        // Update block
        $blockMessage->release_status = 1;
        $blockMessage->release_date = date('d-m-Y H:i:s');
        $blockMessage->release_user = \router::$systemUser == null ? 0 : \router::$systemUser->id;
        $blockMessage->release_message = "Action: approveshipment - ".$message;
        $blockMessage->save();

    }

    private static function approveActionBlockShipment($blockMessage)
    {

        $message = "";

        // Check for other blocks
        if(self::countOpenBlocksOnObject(null,null,$blockMessage->shipment_id,$blockMessage->id) == 0) {
            $shipment = \Shipment::find($blockMessage->shipment_id);
            if($shipment->shipment_state == 3 || $shipment->shipment_state == 9) {

                $message = "order_state changed from ".$shipment->shipment_state." to 4";
                $shipment->shipment_state = 4;
                $shipment->shipment_sync_date = null;
                $shipment->save();

            } else {
                $message = "order_state was ".$shipment->shipment_state.", not changed";
            }
        } else {
            $message = "Shipment has other blocks, do not release yet.";
        }

        // Update block
        $blockMessage->release_status = 1;
        $blockMessage->release_date = date('d-m-Y H:i:s');
        $blockMessage->release_user = \router::$systemUser == null ? 0 : \router::$systemUser->id;
        $blockMessage->release_message = "Action: blockshipment - ".$message;
        $blockMessage->save();

    }
    
    private static function approveActionDeleteCompany($blockMessage)
    {

        $companyOrderList = \CompanyOrder::find('all',array("conditions" => array("company_id" => $blockMessage->company_id)));
        foreach($companyOrderList as $companyOrder) {
            DestroyOrder::destroyOrder($companyOrder->id,false,true);
        }


    }

    private static function approveActionDeleteOrder($blockMessage)
    {
        echo "DESTROY ORDER: ".$blockMessage->company_order_id;
        //$companyorder = DestroyOrder::destroyOrder($blockMessage->company_order_id,false,true);
    }

    private static function approveActionUnlinkCompany($blockMessage)
    {
        
        $company = \Company::find($blockMessage->company_id);
        $orderlist = \CompanyOrder::find("all",array("conditions" => array("company_id" => $blockMessage->company_id)));
        $hasSyncedOrders = false;

        foreach($orderlist as $order) {
            if($order->order_state > 3 && $order->order_state < 7) {
                $hasSyncedOrders = true;
            }
        }

        // Has no orders, update
        if($hasSyncedOrders == false) {

            // Company
            $prevDebitorNr = $company->nav_customer_no;
            $company->company_state = 1;
            $company->nav_customer_no = 0;
            $company->save();

            // Update company order
            $companyOrder = \CompanyOrder::find($blockMessage->company_order_id);
            if($companyOrder->order_state == 3 || $companyOrder->order_state == 2) {
                $companyOrder->order_state = 1;
                $companyOrder->save();
            }

            self::approveActionApproveOnly($blockMessage,"Debitornr ".$prevDebitorNr." fjernet fra kunde.");
            
        } 
        
        // Has orders, cant approve
        else {
            $blockMessage->description = "Nulstilling fejlet, kunden har aktive ordre. ".$blockMessage->description;
            $blockMessage->save();
        }

        //$companyorder = DestroyOrder::destroyOrder($blockMessage->company_order_id,false,true);
    }



}