<?php

namespace GFUnit\navision\homerunner;
use GFBiz\units\UnitController;

class Controller extends UnitController
{

    public function __construct()
    {
        parent::__construct(__FILE__);

    }

    public function testget()
    {

        echo "TEST GET!";

        $client = new HomerunnerClient();
        $response = $client->getAllOrders(100);

        echo "<pre>";
        print_r($response);
        echo "</pre>";

    }

    public function index()
    {
        echo "HOMERUNNER INTO!";

        // Usage
        $client = new HomerunnerClient();

        try {

            /*
            $orders = $client->getAllOrders();

            echo "<pre>";
            print_r($orders);
            echo "</pre>";

            */

            /*
            $status = 'sent-to-wms';
            $from = '2024-10-01 00:00:00';
            $to = '2024-10-31 23:59:59';

            $updatedOrders = $client->getUpdatedOrders($status, $from, $to);
            echo "<pre>";
            print_r($updatedOrders);
            echo "</pre>";
*/

            /*
            $orderid = "4161dbad-9387-42eb-beda-573fdb3a25e7";
            $client->getOrderById($orderid);
            echo "<pre>";
            print_r($client->getOrderById($orderid));
            echo "</pre>";

            */

            $json = OrderData::shipmentToJSON(215783);
            echo $json;

            echo "<pre>";
            print_r($client->createOrder($json));
            echo "</pre>";

            echo "LAST ID: ".$client->getLastLogID();

        } catch (\Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }

    }

    public function createorderdata()
    {

        try {
            $json = OrderData::shipmentToJSON(215783);
            echo $json;
        } catch (\Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }



        return;

        // Example usage
        $shipment = new OrderData();
        //$shipment->setWarehouse('distributionplus');
        //$shipment->setSender('Presentbolaget', 'TestFirma', 'TestGade 123', '', '9000', 'Aalborg', 'DK', '77340500', 'email@testfirma.dk');
        $shipment->setReceiver('TestNavn', 'TestFirma', 'TestGade 123', '', '9000', 'Aalborg', 'DK', '77340500', 'email@testfirma.dk', '77340500', 'email@testfirma.dk');
        //$shipment->setDimensions('20', '20', '6', '20');
        //$shipment->setCarrierInfo('bring', 'private', 'delivery');
        //$shipment->setAdditionalInfo('Order no: 2220192', 1, '', '', 'LabelPrint', 0);
        $shipment->addOrderLine(1, '123', [
            'description' => 'description',
            'total_price' => '123',
            'currency_code' => 'DKK',
            'sender_tariff' => '123',
            'origin_country' => 'DK',
            'receiver_tariff' => '123',
            'weight' => '123'
        ], 'https://coolrunner.dk/image.png');

        $jsonPayload = $shipment->toJson();
        echo $jsonPayload;

    }

}
