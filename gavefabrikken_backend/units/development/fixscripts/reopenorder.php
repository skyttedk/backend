<?php

namespace GFUnit\development\fixscripts;

use GFBiz\Model\Cardshop\DestroyOrder;
use GFUnit\navision\syncorder\OrderSync;

class ReopenOrder
{

    private $stats;

    public function run()
    {


        if(isset($_POST["companyorderid"])) {


            try {
                $this->reopenOrder($_POST["companyorderid"]);
            } catch (\Exception $e) {
                echo "Exception caught during reopen order: ".$e->getMessage();
            }

            echo "<br><br>";
        }

        ?><form method="post" action="">
        <h2>Reopen order</h2>
        Companyorder id: <input type="text" value="" name="companyorderid">  <button>Reopen ordre</button>
    </form><?php

    }
    
    
    private function reopenOrder($companyorderid)
    {

        // Load order
        if(substr(strtolower(trim($companyorderid)),0,2) == "bs") {
            $companyorderlist = \CompanyOrder::find_by_sql("select * from company_order where order_no = '".trim($companyorderid)."'");
            $companyorder = $companyorderlist[0];
            $companyorderid = $companyorder->id;
        }
        else if(intval($companyorderid) > 0) {
            $companyorder = \CompanyOrder::find($companyorderid);
        } else {
            echo "Invalid order id";
            return;
        }

        if($companyorder == null || $companyorder->id == 0) {
            echo "Order not found";
            return;
        }

        if(!in_array($companyorder->order_state,array(4,5))) {
            echo "Order is not in state 4 or 5";
            return;
        }

        $orgOrderNo = $companyorder->order_no;

        $blockList = \BlockMessage::find_by_sql("SELECT * FROM blockmessage where company_id = ".$companyorder->company_id." && release_status = 0");
        if(count($blockList) > 0) {
            echo "Company has blocks, abort";
            exit();
        }

        // Get active users
        $shopUserList = \ShopUser::find_by_sql("SELECT * FROM shop_user where company_order_id = ".$companyorder->id." && blocked = 0");
        $activeShopUserList = array();
        foreach($shopUserList as $shopUser) {
            $activeShopUserList[] = $shopUser->id;
        }

        echo "Found ".count($activeShopUserList)."<br>";


        echo "Cancelled order state<br>";
        DestroyOrder::destroyOrder($companyorderid,true,false);

        // Check companyorder again
        $companyorder = \CompanyOrder::find($companyorderid);
        if($companyorder->order_state != 7) {
            echo "Cancelled order has unexpected state: ".$companyorder->order_state."<br>";
            return;
        }

        // Sync order
        echo "Synkroniser kreditering af ordre<br>";
        try {
            $syncModel = new OrderSync();
            $syncModel->syncCompanyOrder($companyorder);
        } catch (\Exception $e) {
            echo "Exception syncing destroy to nav: ".$companyorder->order_state."<br>";
            return;
        }

        // Load active users
        echo "Aktiver blokkerede kort (".count($activeShopUserList).")<br>";
        foreach($activeShopUserList as $activeUserID) {
            $shopuser = \ShopUser::find($activeUserID);
            $shopuser->blocked = 0;
            $shopuser->save();
        }

        // Revive order
        $order = \CompanyOrder::find($companyorder->id);

        // Tjek state
        if($order->order_state != 8) {
            echo "Ordren er ikke slettet, kan ikke genoplive.";
            return;
        }

        // Find nyt ordre nr
        $system = \system::first();
        $lastOrderNo = $order->order_no;
        $newOrderNo = \Numberseries::getNextNumber($system->company_order_nos_id);
        echo "Last order no: ".$lastOrderNo." - new order no: ".$newOrderNo."<br>";

        // Sæt nyt ordre nr og sæt state
        $order->order_no = $newOrderNo;
        $order->order_state = 1;
        $order->nav_synced = 0;
        $order->freight_state = 0;
        $order->nav_lastsync = null;
        $order->is_cancelled = 0;
        $order->save();
        echo "Updated order<br>";

        // Opdater order docs
        $orderDocsList = \NavisionOrderDoc::find("all",array("conditions" => array("company_order_id" => $order->id)));
        if(count($orderDocsList) > 0) {
            foreach($orderDocsList as $orderDoc) {
                echo "Invalidate order doc ".$orderDoc->order_no." - v ".$orderDoc->revision." [".$orderDoc->status."]<br>";
                $orderDoc->company_order_id = -1*$order->id;
                $orderDoc->save();
            }
        }

        // Opdater shipments
        $shipmentList = \Shipment::find("all",array("conditions" => array("shipment_type" => "giftcard","companyorder_id" => $order->id,"shipment_state" => 2)));
        if(count($shipmentList) > 0) {
            foreach($shipmentList as $shipment) {
                echo "Resync shipment ".$shipment->id." - ".$shipment->shipment_type."<br>";
                $shipment->shipment_state = 1;
                $shipment->shipment_sync_date = null;
                $shipment->isshipment = 0;
                $shipment->save();
            }
        }

        // Count closed shopusers
        $shopUser = \ShopUser::find_by_sql("select * from shop_user where company_order_id = ".$order->id." && (shutdown = 1 or blocked = 1)");
        echo "Order has ".countgf($shopUser)." blocked cards<br>";

        // Creating block order
        echo "Block order<br> ";
        \BlockMessage::createCompanyOrderBlock($order->company_id,$order->id,"COMPANYORDER_REOPEN","Ordre ".$orgOrderNo." lukket og gen-oprettet på ".$order->order_no,true);

        echo "Revive completed<br>";
        \System::connection()->commit();


    }
    

}
