<?php

namespace GFUnit\cardshop\earlyorder;

class EarlyOrderSplit
{

    private $shipmentList = null;

    private $output = true;

    private $errorList = array();

    public function __construct($output=true)
    {
        $this->output = $output;
    }

    public function setSplitterAll() {

        //$this->shipmentList = \Shipment::find_by_sql("SELECT * FROM shipment WHERE id in (213462,213852,217280,223189,224906) and shipment_state = '1' AND shipment_type = 'earlyorder'");
        $this->shipmentList = \Shipment::find_by_sql("SELECT * FROM shipment WHERE shipment_state = '1' AND shipment_type = 'earlyorder'");
    }

    public function runSplitter($output=false)
    {

        try {
            $this->log("Run earlyorder splitter");

            if (!is_array($this->shipmentList)) {
                $this->log("NO SHIPMENTS - ABORTING!");
                return;
            }

            $this->log(" - Processing " . count($this->shipmentList) . " shipments");

            foreach ($this->shipmentList as $shipment) {
                $this->processShipment($shipment);
            }

            \System::connection()->commit();

        } catch (\Exception $e) {
            $this->error("An error occurred: " . $e->getMessage());
        }

        if(count($this->errorList) > 0) {
            mailgf('sc@interactive.dk', "Earlyorder splitter error - ".count($this->errorList)." errors", "Errors in earlyorder splitter:<br><br>".implode("<br>",$this->errorList));
        }

    }

    private function processShipment($shipmentObj) {


        $shipment = \Shipment::find($shipmentObj->id);
        $this->log("Processing shipment: " . $shipment->id );

        $vareListe = array();
        $countryMap = array();

        // Indsamling af varer og lande
        for ($i = 1; $i <= 5; $i++) {
            $quantityField = "quantity" . ($i == 1 ? "" : $i);
            $itemnoField = "itemno" . ($i == 1 ? "" : $i);

            if ($shipment->$quantityField > 0 && trimgf($shipment->$itemnoField) != "") {
                $country = $this->getItemNoCountry($shipment->$itemnoField);
                $vareListe[] = array("itemno" => $shipment->$itemnoField, "quantity" => $shipment->$quantityField, "country" => $country);
                $countryMap[$country][] = $vareListe[count($vareListe) - 1];
            }
        }

        if (count($countryMap) == 0) {
            $this->error(" - No countries found for shipment ".$shipment->id);
            return;
        }

        if (count($countryMap) == 1) {

            $this->log("- single country");

            if($shipment->handler == "autodetect") {
                if(isset($countryMap['NO'])) {
                    $shipment->handler = "manual";
                } else if(isset($countryMap['DK'])) {
                    $shipment->handler = "navision";
                } else {
                    $this->error("- Uknown country for shipment ".$shipment->id);
                    return;
                }
                $this->log("- Set handler to ".$shipment->handler);
                $shipment->save();
            }

            return;
        }

        $this->log(" - Splitting shipment into " . count($countryMap) . " shipments");

        // Behold norske varer på den oprindelige shipment
        $this->updateShipmentItems($shipment, $countryMap['NO']);
        if(in_array($shipment->handler,array("autodetect","manual","navision"))) {
            $shipment->handler = "manual";
        }
        $shipment->save();

        // Opret en ny shipment for danske varer
        $newShipment = new \Shipment();
        $this->copyShipmentData($shipment, $newShipment);
        $this->updateShipmentItems($newShipment, $countryMap['DK']);
        if(in_array($shipment->handler,array("autodetect","manual","navision"))) {
            $newShipment->handler = "navision";
        }
        $newShipment->save();

        $this->log(" - SAVED NEW SHIPMENT: ".$newShipment->id);
    }

    private function updateShipmentItems($shipment, $items) {
        for ($i = 1; $i <= 5; $i++) {
            $quantityField = "quantity" . ($i == 1 ? "" : $i);
            $itemnoField = "itemno" . ($i == 1 ? "" : $i);

            if (isset($items[$i - 1])) {
                $shipment->$itemnoField = $items[$i - 1]['itemno'];
                $shipment->$quantityField = $items[$i - 1]['quantity'];
            } else {
                $shipment->$itemnoField = "";
                $shipment->$quantityField = 0;
            }
        }
    }

    private function copyShipmentData($sourceShipment, $targetShipment) {
        $fieldsToCopy = [
            'companyorder_id', 'created_date', 'shipment_type', 'handler',
            'description', 'isshipment', 'from_certificate_no', 'to_certificate_no',
            'shipto_name', 'shipto_address', 'shipto_address2', 'shipto_city',
            'shipto_postcode', 'shipto_country', 'shipto_contact', 'shipto_email',
            'shipto_phone', 'shipment_note', 'gls_shipment', 'handle_country',
            'shipment_state', 'shipment_sync_date', 'deleted_date', 'force_syncnow',
            'series_master', 'series_uuid', 'shipto_state', 'sync_delay', 'sync_note',
            'reservation_released', 'shipped_date', 'consignor_created', 'consignor_labelno',
            'nav_order_no'
        ];

        foreach ($fieldsToCopy as $field) {
            $targetShipment->$field = $sourceShipment->$field;
        }
    }

    private function getItemNoCountry($itemno) {

        $item = \NavisionItem::find_by_sql("SELECT * FROM `navision_item` WHERE `language_id` = 1 AND `no` LIKE '".$itemno."' AND `deleted` IS NULL");
        if(count($item) > 0) return "DK";

        $item = \NavisionItem::find_by_sql("SELECT * FROM `navision_item` WHERE `language_id` = 4 AND `no` LIKE '".$itemno."' AND `deleted` IS NULL");
        if(count($item) > 0) return "NO";

        return "";

    }


    public function getErrors() {
        return $this->errorList;
    }

    private function error($string) {
        $this->errorList[] = $string;
        if($this->output) {
            echo "<b style='color:red;'>ERROR: ".$string."</b><br>";
        }
    }

    private function log($string) {
        if($this->output) {
            echo $string . "<br>";
        }
    }

}