<?php

namespace GFUnit\navision\homerunner;

class OrderData {
    private $data;

    private static $shipmentItems;

    public static function getShipmentJSONItems() {
        return self::$shipmentItems;
    }

    public static function shipmentToJSON($shipment)
    {

        self::$shipmentItems = [];
        
        if(is_int($shipment)) {
            $shipment = \Shipment::find($shipment);
        }
        
        if(!($shipment instanceof \Shipment)) {
            throw new \Exception('Input is not a shipment');
        }
        
        if($shipment->id <= 0) {
            throw new \Exception('Shipment not found');
        }

        if($shipment->shipment_type != 'privatedelivery' && $shipment->shipment_type != 'directdelivery') {
            throw new \Exception('Shipment type ['.$shipment->shipment_type.'] not supported');
        }

        // Create order and set receiver data
        $orderData = new OrderData();
        $orderData->setReceiver($shipment->shipto_name, $shipment->shipto_contact, $shipment->shipto_address, $shipment->shipto_address2, $shipment->shipto_postcode, $shipment->shipto_city, $shipment->shipto_country, $shipment->shipto_phone, $shipment->shipto_email, $shipment->shipto_phone, $shipment->shipto_email);

        // Get item from nav
        $quantity = $shipment->quantity;
        $itemno = $shipment->itemno;

        // Check for bom
        $navbomItemList = \NavisionBomItem::find_by_sql("SELECT * FROM `navision_bomitem` WHERE language_id = 1 && parent_item_no LIKE '".$itemno."' && deleted is null");
        if(countgf($navbomItemList) > 0) {
            foreach($navbomItemList as $item) {

                $childItemNo = $item->no;
                $childQuantity = $item->quantity_per*$quantity;

                $item = \NavisionItem::find_by_sql("SELECT * FROM `navision_item` WHERE `language_id` = 1 AND `no` LIKE '".$childItemNo."' AND `deleted` IS NULL");
                if(countgf($item) == 0) {
                    throw new \Exception('Child item not found ['.$childItemNo.']');
                }
                else if(countgf($item) > 1) {
                    throw new \Exception('Multiple child items found ['.$childItemNo.']');
                }

                $item = $item[0];
                $orderData->addOrderLine($childQuantity,$childItemNo,array("description" => $item->description));

                if(!isset(self::$shipmentItems[$childItemNo])) {
                    self::$shipmentItems[$childItemNo] = $childQuantity;
                } else {
                    self::$shipmentItems[$childItemNo] += $childQuantity;
                }

            }
        }
        else {

            $item = \NavisionItem::find_by_sql("SELECT * FROM `navision_item` WHERE `language_id` = 1 AND `no` LIKE '".$itemno."' AND `deleted` IS NULL");
            if(countgf($item) == 0) {
                throw new \Exception('Item not found ['.$itemno.']');
            }
            else if(countgf($item) > 1) {
                throw new \Exception('Multiple items found ['.$itemno.']');
            }

            $item = $item[0];
            $orderData->addOrderLine($quantity,$item->no,array("description" => $item->description));

            if(!isset(self::$shipmentItems[$item->no])) {
                self::$shipmentItems[$item->no] = $quantity;
            } else {
                self::$shipmentItems[$item->no] += $quantity;
            }

        }

        // Find cardshop settings
        $companyorder = \CompanyOrder::find($shipment->companyorder_id);
        $cardshopSettings = \CardshopSettings::find_by_shop_id($companyorder->shop_id);

        // Set reference
        $orderData->setReference($shipment->id,"Order no: ".$shipment->id);
        $orderData->setComment($shipment->shipment_note);
        $orderData->setDescription($shipment->itemno);
        $orderData->setDeliveryNote($shipment->description);

        // To json and return
        return $orderData->toJson();

    }

    public function __construct() {
        $this->data = [
            'warehouse' => '',
            'sender' => [
                'name' => 'GaveFabrikken',
                'attention' => '',
                'street1' => 'Hedelykken 6',
                'street2' => '',
                'zip_code' => '2640',
                'city' => 'Hedehusene',
                'country' => 'DK',
                'phone' => '+45 70702027',
                'email' => 'info@gavefabrikken.dk'
            ],
            'receiver' => [
                'name' => '',
                'attention' => '',
                'street1' => '',
                'street2' => '',
                'zip_code' => '',
                'city' => '',
                'country' => '',
                'phone' => '',
                'email' => '',
                'notify_sms' => '',
                'notify_email' => ''
            ],
            'length' => '10',
            'width' => '10',
            'height' => '10',
            'weight' => '100',
            'carrier' => 'bring',
            'carrier_product' => 'private',
            'carrier_service' => 'droppoint',
            'reference' => '',
            'delivery_note' => '',
            'description' => '',
            'comment' => '',
            'label_format' => 'LabelPrint',
            'servicepoint_id' => '0',
            'order_lines' => [],
            'warehouse' => 'distributionplus'
        ];
    }

    public function setWarehouse($warehouse) {
        $this->data['warehouse'] = $warehouse;
    }

    public function setSender($name, $attention, $street1, $street2, $zip_code, $city, $country, $phone, $email) {
        $this->data['sender'] = array_filter([
            'name' => $name,
            'attention' => $attention,
            'street1' => $street1,
            'street2' => $street2,
            'zip_code' => $zip_code,
            'city' => $city,
            'country' => $country,
            'phone' => $phone,
            'email' => $email
        ]);
    }

    public function setReceiver($name, $attention, $street1, $street2, $zip_code, $city, $country, $phone, $email, $notify_sms, $notify_email) {

        if($name == $attention) {
            $attention = "";
        }

        if($country == "Sverige") {
            $country = "SE";
        }

        $this->data['receiver'] = array_filter([
            'name' => $name,
            'attention' => $attention,
            'street1' => $street1,
            'street2' => $street2,
            'zip_code' => $zip_code,
            'city' => $city,
            'country' => $country,
            'phone' => $this->formatSEPhoneNumber($phone),
            'email' => $email,
            'notify_sms' => $this->formatSEPhoneNumber($notify_sms),
            'notify_email' => $notify_email
        ]);
    }

    private function formatSEPhoneNumber($phone) {

        //$phone = trimgf(str_replace(array(" ","-"),"",$phone));
        //if($phone != "") {
        //    if(!(substr($phone,0,1) == "+" || substr($phone,0,2) == "46" || substr($phone,0,3) == "+46")) {
        //        if(substr($phone,0,1) === "0") $phone = substr($phone,1);
        //        $phone = "+46".$phone;
        //    }
        //}
        //return $phone;

        // If starts with + but not +46
        if(substr(trimgf($phone),0,1) == "+" && substr(trimgf($phone),0,3) != "+46") {

            // Accept other language code, just trin, remove special chars and return phone no with +
            return "+" . trimgf(preg_replace('/[^0-9]/', '', $phone));

        }
        
        // Fjern alle tegn, der ikke er cifre eller "+"
        $rensetNummer = preg_replace('/[^\d+]/', '', $phone);

        // Hvis nummeret er tomt efter rensning, returner en tom streng
        if (empty($rensetNummer)) {
            return '';
        }

        // Hvis det starter med "+", behold det, ellers fjern alt andet end cifre
        if (substr($rensetNummer, 0, 1) !== '+') {
            // Hvis nummeret starter med "0", fjern "0"
            if (substr($rensetNummer, 0, 1) === '0') {
                $rensetNummer = substr($rensetNummer, 1);
            }
            // Tilføj "+46" hvis der ikke er nogen landekode
            $rensetNummer = '+46' . $rensetNummer;
        }

        return $rensetNummer;

    }

    public function setDimensions($length, $width, $height, $weight) {
        $this->data['length'] = $length;
        $this->data['width'] = $width;
        $this->data['height'] = $height;
        $this->data['weight'] = $weight;
    }

    public function setCarrierInfo($carrier, $carrier_product, $carrier_service) {
        $this->data['carrier'] = $carrier;
        $this->data['carrier_product'] = $carrier_product;
        $this->data['carrier_service'] = $carrier_service;
    }
    
    public function setReference($order_no,$reference) {
        $this->data["order_number"] = trimgf($order_no);
        $this->data['reference'] = $reference;
    }
    
    public function setDeliveryNote($delivery_note) {
        $this->data['delivery_note'] = $delivery_note;
    }
    
    public function setDescription($description) {
        $this->data['description'] = $description;
    }
    
    public function setComment($comment) {
        $this->data['comment'] = $comment;
    }
    
    public function setAdditionalInfo($reference, $delivery_note, $description, $comment, $label_format, $servicepoint_id) {
        $this->data['reference'] = $reference;
        $this->data['delivery_note'] = $delivery_note;
        $this->data['description'] = $description;
        $this->data['comment'] = $comment;
        $this->data['label_format'] = $label_format;
        $this->data['servicepoint_id'] = $servicepoint_id;
    }

    public function addOrderLine($qty, $item_number, $customs = [], $image_url = '') {
        $orderLine = array_filter([
            'qty' => $qty,
            'item_number' => $item_number,
            'customs' => $customs,
            'image_url' => $image_url
        ]);

        $this->data['order_lines'][] = $orderLine;
    }

    public function toJson() {
        return json_encode(array_filter($this->data), JSON_PRETTY_PRINT);
    }
}
