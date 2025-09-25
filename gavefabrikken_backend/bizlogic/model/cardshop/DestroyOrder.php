<?php

namespace GFBiz\Model\Cardshop;

class DestroyOrder
{


    public static function destroyOrder($companyorderid,$output=false,$commit=false)
    {

        // Find companyorder
        $companyorder = \CompanyOrder::find($companyorderid);
        if($companyorder->id != $companyorderid) {
            throw new \Exception("Could not find companyorder id");
        }

        if($output) echo "Processing ".$companyorder->order_no." (".$companyorder->id.") [".$companyorder->order_state."]<br>";

        $validStates = array(0,1,2,3,4,5,6);
        if(!in_array($companyorder->order_state,$validStates)) {
            throw new \Exception("Order cant be destroyed in the state: ".$companyorder->getStateText());
        }

        // Not synced to nav, set to cancelled directly
        if($companyorder->order_state <= 3) {
            if($output) echo "Not synced, delete directly<br>";
            $companyorder->order_state = 8;
        }
        // Synced, rollback in nav
        else {
            if($output) echo "Synced, set to rollback<br>";
            $companyorder->order_state = 7;
        }

        // Update companyorder
        $companyorder->nav_synced = 0;
        $companyorder->is_cancelled = 1;
        $companyorder->save();

        // Get shopusers
        $shopuserList = \ShopUser::find("all",array("conditions" => array("company_order_id" => $companyorder->id)));
        if($output) echo "Found ".countgf($shopuserList)." shopusers to check and close<br>";
        foreach($shopuserList as $shopUser) {
            $shopUser->blocked = 1;
            $shopUser->save();
        }

        // Get shipments and update
        $shipmentList = \Shipment::find("all",array("conditions" => array("companyorder_id" => $companyorder->id)));
        if($output) echo "Found ".countgf($shipmentList)." shipments to check and close<br>";
        foreach($shipmentList as $shipment) {
            if(in_array($shipment->shipment_state,array(0,1,5))) {
                $shipment->shipment_state = 4;
                $shipment->deleted_date = date('d-m-Y H:i:s');
                $shipment->save();
            }
        }

        // Update company
        $company = \Company::find($companyorder->company_id);
        if($company->nav_customer_no == 0) {
            $orderlist = \CompanyOrder::find_by_sql("SELECT * FROM company_order WHERE company_id = ".$company->id." && order_state NOT IN (8)");
            if($output) echo "Found ".countgf($orderlist)." non-closed orders<br>";
            if(count($orderlist) == 0) {

                if($output) echo "Set company state to 0<br>";
                $company->company_state = 0;
                $company->save();

                // Close blocks for company
                $blockList = \Blockmessage::find("all",array("conditions" => array("company_id" => $company->id,"company_order_id" => 0, "shipment_id" => 0,"release_status" => 0)));
                foreach($blockList as $block) {
                    $block->release_status = 1;
                    $block->release_date = date('d-m-Y H:i:s');
                    $block->release_user = \router::$systemUser == null ? 0 : \router::$systemUser->id;
                    $block->release_message = "Order destroyed, no orders left, block autoclosed";
                    $block->save();
                }

            }
        }

        // Close blocks
        $blockList = \Blockmessage::find("all",array("conditions" => array("company_order_id" => $companyorder->id,"release_status" => 0)));
        foreach($blockList as $block) {
            $block->release_status = 1;
            $block->release_date = date('d-m-Y H:i:s');
            $block->release_user = \router::$systemUser == null ? 0 : \router::$systemUser->id;
            $block->release_message = "Order destroyed, block autoclosed";
            $block->save();
        }

        // Commit database
        if($commit) {
            \system::connection()->commit();
            \System::connection()->transaction();
        }

        return $companyorder;

    }

}