<?php

namespace GFUnit\tools\wizard;
use GFBiz\units\UnitController;
use GFCommon\Model\Navision\OrderXML;
use GFCommon\Model\Navision\SalesHeaderWS;
use GFCommon\Model\Navision\SalesLineWS;
use GFCommon\Model\Navision\Shipment2XML;

class Controller extends UnitController
{

    public function __construct()
    {
        parent::__construct(__FILE__);
    }

    public function index()
    {
        echo "test";
    }

    public function dashboard() {
        $this->view("dashboard");
    }

    public function trimshopattribute()
    {

        $this->view("trimshopattribute");

    }

    public function navpaystatus() {
        $this->view("navpaystatus");
    }

    public function navshipmentxml() {
        $this->view("navshipmentxml");
    }

    public function navorderxml() {
        $this->view("navorderxml");
    }


    public function csuploadattribute() {
        $this->view("csuploadattribute");
    }

    public function csmerge2() {
        $this->view("csmerge2");
    }

    public function movefield() {
        $this->view("movefield");
    }

    /**
     * PROMPT

    Jeg har en multipage wizard, bygget med HTML, CSS og JavaScript, der anvender funktionaliteter fra Bootstrap 4 til at style interfacet. Wizarden tillader brugerne at navigere gennem en række selvstændige trin, og hver "trin" er repræsenteret af en 'div' med klassen 'wizard-step'. Hvert trin skal nu være selvstændigt og indeholde sin egen JavaScript-funktion, der specificerer, hvordan det skal initialiseres, inden for samme 'div'.

    Derudover har jeg nogle globale hjælpefunktioner, som hvert trin kan kalde for at udføre almindelige opgaver:
    - `enableNextButton()`: En funktion, der aktiverer "Næste" knappen i wizarden.
    - `disableNextButton()`: En funktion, der deaktiverer "Næste" knappen i wizarden.
    - `fetchJsonData(url, data)`: En asynkron funktion der bruger en POST-anmodning til at hente JSON-data fra en server og returnerer disse data eller `null` ved fejl.
    - `logMessage(message, isError)`: En funktion der logger en besked til en dedikeret log-sektion i appen; `isError` er en boolean, der angiver om det er en fejlbesked.

    Jeg vil gerne tilføje et nyt selvstændigt trin til denne wizard. Her er detaljerne for det nye trin:

    På siden skal der være en textinput der hedder shopid med label Shop ID.
    Efter den, på samme linje er en knap der hedder test.
    Init funktionen skal sætte næste knappen til at være disabled og når man trykker på tjek bliver den enabled.
    Der skal også være en label der hedder Shop navn og plads til et readonly inputfelt med navnet på shoppen man tjekker.
     */


    /**
     * HELPER SERVICES
     */

    public function servicenavorderxml()
    {

        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        $orderid = $data["orderid"];

        // If order starts with BS (check uppercase)
        if(strtoupper(substr($orderid,0,2)) == "BS") {

            $order = \CompanyOrder::find_by_order_no($orderid);
            
        } else {
            $order = \CompanyOrder::find($orderid);
        }



        $orderXML = new OrderXML($order,1);
        $xml = $orderXML->getXML();


        echo json_encode(array("xml" => $xml, "id" => $order->id));
        exit();

    }

    public function servicenavshipmentxml() {

        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        $shipmentid = $data["shipmentid"];

        $shipment = \Shipment::find($shipmentid);

        $shiptoMaster = null;
        if($shipment->shipto_state == 2) {
            $shiptoMaster = \Shipment::find('first',array("conditions" => array("companyorder_id" => $shipment->companyorder_id,"shipment_type" => "giftcard","shipto_state" => 1)));
        }

        $xmlModel = new Shipment2XML($shipment,$shiptoMaster);
        $xmlDoc = $xmlModel->getXML();

        echo json_encode(array("xml" => $xmlDoc, "id" => $shipment->id));
        exit();

    }

    public function checkbspaystatus()
    {

        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        $bsno = $data["bsno"];

        // Load companyorder
        $companyOrder = \CompanyOrder::find_by_order_no($bsno);
        $shopSettings = \CardshopSettings::find_by_shop_id($companyOrder->shop_id);

        $client = new \GFCommon\Model\Navision\OrderStatusWS($shopSettings->language_code);
        $orderStatus = $client->getStatus($companyOrder->order_no);

        $tolkning = "UKENDT";
        if($orderStatus == null) {
            $tolkning = "KAN IKKE HENTE DATA";
        }
        else if(intval($orderStatus->getRemPrepaymentAmountLCY()) > 0) {
            $tolkning = "IKKE BETALT";
        } else {
            $tolkning = "BETALT";
        }

        $data = array("BSNr" => $companyOrder->order_no,"CompanyOrderID" => $companyOrder->id,"Virksomhed" => $companyOrder->company_name,"Tolkning" => $tolkning,"NavisionRespons" => ($orderStatus == null ? "NULL" : $orderStatus->getDataArray()));

        echo json_encode($data);


    }

    public function checkshop()
    {

        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        try {
            $shop = \Shop::find(intval($data["shopid"]));

            $attributes = \ShopAttribute::find_all_by_shop_id($shop->id);
            
            $attList = array();
            foreach($attributes as $att) {
                $attList[] = array("id" =>$att->id,"name" => $att->name);
            }

            echo json_encode(array("status" => 1, "id" => $shop->id,"name" => $shop->name,"attributes" => $attList));
        } catch (\Exception $e) {
            echo json_encode(array("status" => 0, "message" => "Shop not found","e" => $e->getMessage()));
            return;
        }

    }

}