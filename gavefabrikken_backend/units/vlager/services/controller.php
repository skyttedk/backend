<?php

namespace GFUnit\vlager\services;
use GFBiz\units\UnitController;
use GFUnit\vlager\utils\Navision;

class Controller extends UnitController
{

    public function __construct()
    {
        parent::__construct(__FILE__);
    }

    public function index()
    {
        echo "NO VIEW!";
    }

    /**
     * IMPORT SO
     */

    public function checkso()
    {

        try {

            $lager = \VLager::find(intval($_POST["vlagerid"]));
            $navision = new \GFUnit\vlager\utils\Navision();

            try {

                ob_start();

                $navision->loadSONo($_POST["sono"],$lager->language_id);

                $order = $navision->getOrder();
                $lines = $navision->getOrderLines();

                echo trim($order->getDocumentType()." nr ".$order->getNo())."\r\n";
                echo "Til: ".$order->getShiptoName()."\r\n";
                echo "Status: ".$order->getStatus()."\r\n";
                echo $order->getComment()."\r\n";
                echo "Linjer:\r\n";

                foreach($lines as $line) {
                    if($line->getQuantity() > 0) {
                        echo $line->getQuantity()." x ".$line->getNo().": ".$line->getDescription()."\r\n";
                    }

                }

                $content = ob_get_contents();
                ob_end_clean();

                echo json_encode(array("status" => 1, "message" => $content));

            } catch (\Exception $e) {
                echo json_encode(array("status" => 0, "message" => $e->getMessage()));
                return;
            }

        } catch (\Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }

    }

    public function importso()
    {

        try {

            $lager = \VLager::find(intval($_POST["vlagerid"]));
            $navision = new \GFUnit\vlager\utils\Navision();
            $navision->loadSONo($_POST["sono"],$lager->language_id);

            // Create vlagerincoming
            $io = new \VLagerIncoming();
            $io->vlager_id = $lager->id;
            $io->sono = $_POST["sono"];
            $io->created = date("Y-m-d H:i:s");
            $io->save();

            $lines = $navision->getOrderLines();
            $savedLines = 0;

            foreach($lines as $line) {
                if($line->getQuantity() > 0) {

                    $iol = new \VLagerIncomingLine();
                    $iol->vlager_id = $lager->id;
                    $iol->vlager_incoming_id = $io->id;
                    $iol->itemno = $line->getNo();
                    $iol->quantity_order = $line->getQuantity();
                    $iol->quantity_received = $line->getQuantity();
                    $iol->save();

                    $savedLines++;

                }
            }

            if($savedLines == 0) {
                throw new \Exception("Ingen linjer fundet i ordren");
            }

            echo json_encode(array("status" => 1, "message" => "Oprettet"));
            \System::connection()->commit();

        } catch (\Exception $e) {
            echo json_encode(array("status" => 0, "message" => $e->getMessage()));
        }

    }

}