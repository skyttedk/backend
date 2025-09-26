<?php

namespace GFUnit\test\cardshop;

class TestLib
{

    private $langCode;
    
    public function __construct($languageCode=1)
    {
        $this->langCode = $languageCode;
    }

    public function createCompany($name,$cvr,$ean,$salesperson,$billAddress,$billAddress2,$billZip,$billCity,$billCountry,$billEmail,$shipCompany,$shipAttention,$shipAddress,$shipAddress2,$shipZip,$shipCity,$shipCountry,$contactName,$contactEmail,$contactPhone)
    {

        $companydata = array(
            "cvr" => $cvr,
            "is_gift_certificate" => 1,
            "active" => 1,
            "deleted" => 0,
            "language_code" => $this->langCode,
            "name" => $name,
            "ean" => $ean,
            "sales_person" => $salesperson,
            "bill_to_address" => $billAddress,
            "bill_to_address_2" => $billAddress2,
            "bill_to_postal_code" => $billZip,
            "bill_to_city" => $billCity,
            "bill_to_country" => $billCountry,
            "bill_to_email" => $billEmail,
            "ship_to_company" => $shipCompany,
            "ship_to_attention" => $shipAttention,
            "ship_to_address" => $shipAddress,
            "ship_to_address_2" => $shipAddress2,
            "ship_to_postal_code" => $shipZip,
            "ship_to_city" => $shipCity,
            "ship_to_country" => $shipCountry,
            "contact_name" => $contactName,
            "contact_phone" => $contactPhone,
            "contact_email" => $contactEmail
        );


        try {
            $company = \GFBiz\Model\Cardshop\CompanyLogic::createCompany($companydata);
            if($company == null) return null;
            return $company->id;
        } catch(\Exception $e) {
            return null;
        }
    }

    public function createOrder($companyId,$shopId,$expireDate,$quantity,$isEmail,$freeCards,$requisitionNo,$salesperson,$prepayment,$onhold,$carryUp,$dot,$giftwrap,$note,$conceptDiscount,$deliveryPrice,$cardFeePrice,$carryupPrice,$dotPrice,$giftwrapPrice,$invoiceInitialPrice)
    {



        $orderData = array(
            "shop_id" => $shopId,
            "expire_date" => $expireDate,
            "quantity" => $quantity,
            "salesperson" => $salesperson,
            "company_id" => $companyId,
            "is_email" => $isEmail ? 1 : 0,
            "salenote" => $note,
            "free_cards" => $freeCards,
            "requisition_no" => $requisitionNo,
            "prepayment" => $prepayment,
            "nav_on_hold" => $onhold,
            "gift_spe_lev" => $carryUp ? 1 : 0,
            "dot" => $dot ? 1 : 0,
            "giftwrap" => $giftwrap ? 1 : 0
        );

        try {

            $order = \GFBiz\Model\Cardshop\CompanyOrderLogic::createOrder($orderData);
            if($order == null) return null;

            $shopSettings = \CardshopSettings::find('first',array("conditions" => array("shop_id" => $shopId)));
            \GFBiz\Model\Cardshop\CompanyOrderLogic::updateOrderItem($order->id,array("isdefault" => 0,"type" => "CONCEPT","quantity" => $quantity,"price" => $shopSettings->card_price-($conceptDiscount*100)));
            \GFBiz\Model\Cardshop\CompanyOrderLogic::updateOrderItem($order->id,array("isdefault" => 0,"type" => "CARDDELIVERY","quantity" => (!$isEmail && $deliveryPrice > 0 ? 1 : 0),"price" => $deliveryPrice*100));
            \GFBiz\Model\Cardshop\CompanyOrderLogic::updateOrderItem($order->id,array("isdefault" => 0,"type" => "CARRYUP","quantity" => (($carryUp && $carryupPrice > 0) ? 1 : 0),"price" => $carryupPrice*100));
            \GFBiz\Model\Cardshop\CompanyOrderLogic::updateOrderItem($order->id,array("isdefault" => 0,"type" => "DOT","quantity" => (($dot && $dotPrice > 0) ? 1 : 0),"price" => $dotPrice*100));
            \GFBiz\Model\Cardshop\CompanyOrderLogic::updateOrderItem($order->id,array("isdefault" => 0,"type" => "GIFTWRAP","quantity" => (($giftwrap && $giftwrapPrice > 0) ? $quantity : 0),"price" => $giftwrapPrice*100));
            \GFBiz\Model\Cardshop\CompanyOrderLogic::updateOrderItem($order->id,array("isdefault" => 0,"type" => "INVOICEFEEINITIAL","quantity" => ($invoiceInitialPrice > 0 ? 1 : 0),"price" => $invoiceInitialPrice*100));

            \system::connection()->commit();

            return $order->id;
        } catch(\Exception $e) {
            echo "<br>ERROR: ".$e->getMessage()."<br>";
            return null;
        }

    }

    public function createCardShipment($companyOrderID,$quantity,$shipName,$shipAddress,$shipAddress2,$shipZip,$shipCity,$shipCountry,$shipContact,$shipEmail,$shipPhone)
    {

        // Load companyorder and last shipment
        $companyOrder = \CompanyOrder::find($companyOrderID);
        $lastShipment = \Shipment::find("first",array("conditions" => array("companyorder_id" => $companyOrderID), 'order' => 'to_certificate_no desc'));

        // Find certificate numbers
        if($lastShipment == null) {
            $fromCertificateNo = $companyOrder->certificate_no_begin;

        } else {
            $fromCertificateNo = $lastShipment->to_certificate_no+1;
        }

        $toCertificateNo = $fromCertificateNo+$quantity-1;

        // Create shipment
        $shipment = new \Shipment();
        $shipment->companyorder_id = $companyOrderID;
        $shipment->shipment_type = "giftcard";
        $shipment->quantity = $quantity;
        $shipment->itemno = "";
        $shipment->description = "";
        $shipment->isshipment = 1;
        $shipment->from_certificate_no = $fromCertificateNo;
        $shipment->to_certificate_no = $toCertificateNo;
        $shipment->shipto_name = $shipName;
        $shipment->shipto_address = $shipAddress;
        $shipment->shipto_address2 = $shipAddress2;
        $shipment->shipto_postcode = $shipZip;
        $shipment->shipto_city = $shipCity;
        $shipment->shipto_country = $shipCountry;
        $shipment->shipto_contact = $shipContact;
        $shipment->shipto_email = $shipEmail;
        $shipment->shipto_phone = $shipPhone;
        $shipment->shipment_state = 1;
        $shipment->save();

    }

    public function createEarlyOrderShipment($companyOrderID,$quantity,$itemNo,$shipName,$shipAddress,$shipAddress2,$shipZip,$shipCity,$shipCountry,$shipContact,$shipEmail,$shipPhone)
    {

        $shipment = new \Shipment();
        $shipment->companyorder_id = $companyOrderID;
        $shipment->shipment_type = "earlyorder";
        $shipment->quantity = $quantity;
        $shipment->itemno = $itemNo;
        $shipment->description = "Earlyorder: ".$itemNo;
        $shipment->isshipment = 1;
        $shipment->from_certificate_no = 0;
        $shipment->to_certificate_no = 0;
        $shipment->shipto_name = $shipName;
        $shipment->shipto_address = $shipAddress;
        $shipment->shipto_address2 = $shipAddress2;
        $shipment->shipto_postcode = $shipZip;
        $shipment->shipto_city = $shipCity;
        $shipment->shipto_country = $shipCountry;
        $shipment->shipto_contact = $shipContact;
        $shipment->shipto_email = $shipEmail;
        $shipment->shipto_phone = $shipPhone;
        $shipment->shipment_state = 1;
        $shipment->save();

        // Update certificate no
        $shipment->from_certificate_no = $shipment->id;
        $shipment->to_certificate_no = $shipment->id;
        $shipment->save();

    }


}