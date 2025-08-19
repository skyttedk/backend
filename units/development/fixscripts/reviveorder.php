<?php

namespace GFUnit\development\fixscripts;

class ReviveOrder
{

    private $stats;

    public function run()
    {

        if(isset($_POST["companyorderid"]) && trimgf($_POST["companyorderid"]) != "") {

            echo "<b>Start genoplivning af ordre ".$_POST["companyorderid"]."</b><br>";

            $orderinput = trimgf($_POST["companyorderid"]);
            if(intval($orderinput) > 0) {
                $order = \CompanyOrder::find(intval($orderinput));
            } else if(strtolower(substr($orderinput,0,2)) == "bs") {
                $order = \CompanyOrder::find("first", array("conditions" => array("order_no" => $orderinput)));
            }

            if($order == null || $order->id == 0) {
                echo "KUNNE IKKE FINDE ORDREN: ".$orderinput." - PRØV IGEN!";
                exit();
            }

            echo "Fandt ordren ".$order->id." - ".$order->order_no."<br>";
            echo $order->company_name."<br>";

            // Tjek state
            if($order->order_state != 8) {
                echo "Ordren er ikke slettet, kan ikke genoplive.";
                exit();
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

            echo "Revive completed<br>";
            \System::connection()->commit();

            echo "<br><br>";
        }

        ?><form method="post" action="">
        companyorder id: <input type="text" value="" name="companyorderid">  <button>Genopliv ordre</button>
    </form><?php

    }

}
