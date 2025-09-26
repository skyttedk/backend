<?php

namespace GFUnit\privatedelivery\sedsv;


class OpretPluk
{

    private $helper;

    public function __construct()
    {

        $this->helper = new Helpers();

    }

    public function dispatch()
    {

        // Get varenr list
        $vareNrList = $this->helper->getVarenrList();
        $readyToProcess = array();

        // Process sampak nr
        foreach($vareNrList as $varenr) {
            if(isset($_POST[$varenr]) && isset($_POST["quantity_".$varenr]) && intval($_POST[$varenr]) == 1 && intval($_POST["quantity_".$varenr]) > 0) {
                $readyToProcess[trim(strtolower($varenr))] = intval($_POST["quantity_".$varenr]);
            }
        }

        //echo "<pre>".print_r($readyToProcess,true)."</pre>";

        $shipmentsInPluk = array();
        $plukItemCount = array();

        // Load shipments
        $shipmentsReady = $this->helper->getDSVShipmentsReady();
        foreach($shipmentsReady as $shipment) {

            //echo "CHECK: ".$shipment->id.": ".$shipment->itemno."<br>";

            $itemNo = trim(strtolower($shipment->itemno));
            if(isset($readyToProcess[$itemNo]) && intval($readyToProcess[$itemNo]) > 0) {

                $shipmentsInPluk[] = $shipment;
                $readyToProcess[$itemNo]--;

                if(!isset($plukItemCount[$itemNo])) $plukItemCount[$itemNo] = 0;
                $plukItemCount[$itemNo]++;

            }

            if(count($shipmentsInPluk) > 400) break;

        }

        echo "Processing ".count($shipmentsInPluk)." shipments on ".count($plukItemCount)." items.<br>";

        $pullDate = time();
        $errors = 0;

        // Process shipments
        foreach($shipmentsInPluk as $ship) {

            // Load data
            $shipment = \Shipment::find($ship->shipment_id);
            $order = \Order::find($ship->to_certificate_no);
            $shopuser = \ShopUser::find($order->shopuser_id);

            echo $shipment->id." / ".$order->id." / ".$shopuser->id." / ".$shopuser->username.": ".$shipment->itemno." - ";

            if($shipment->from_certificate_no != $shopuser->username) {
                echo "ERROR - Username not correct!";
                $errors++;
            } else if($shopuser->delivery_state != 1) {
                echo "ERROR - SHOPUSER DELIVERYSTATE IS ".$shopuser->delivery_state;
                $errors++;
            } else if($shipment->shipment_state != 1) {
                echo "ERROR - SHIPMENT STATE IS ".$shipment->shipment_state;
                $errors++;
            } else {

                echo "OK";
                $shopuser->delivery_state = 2;
                $shopuser->save();

                $shipment->shipment_state = 2;
                $shipment->shipment_sync_date = date('d-m-Y H:i:s',$pullDate);
                $shipment->save();

            }

            echo "<br>";

        }



        if($errors == 0) {
            echo "<br><b>Ingen fejl, træk er gemt med dato: ".date('d-m-Y H:i:s',$pullDate)."</b>";

            echo "<a href=\"index.php?rt=unit/privatedelivery/sedsv/dashboard\">Til nyt træk</a> | <a href=\"index.php?rt=unit/privatedelivery/sedsv/index\">Til fil oversigt</a>";

            \System::connection()->commit();
        } else {
            echo "<br><b>".$errors." fejl, træk IKKE gemt!</b>";
        }

    }

}