<?php

class Actionlog extends ActiveRecord\Model {

    static $table_name = "actionlog";
    static $primary_key = "id";
    static $before_save = array('onBeforeSave');
    static $after_save = array('onAfterSave');
    static $before_create = array('onBeforeCreate');
    static $after_create = array('onAfterCreate');
    static $before_update = array('onBeforeUpdate');
    static $after_update = array('onAfterUpdate');
    static $before_destroy = array('onBeforeDestroy');
    static $after_destroy = array('onAfterDestroy');



    public function getTypeName() {

        // SELECT type FROM `actionlog` GROUP BY type ORDER BY `actionlog`.`type` ASC

        $knownTypes = array(
            "ShopUserLogin" => "Gavemodtager login",
            "ShopUserOrder" => "Gavemodtager valg",
            "note" => "Notat",
            "CardsBlocked" => "Kort lukket",
            "CardsMove" => "Kort flyttet",
            "CardsUnblocked" => "Kort genåbnet",
            "company" => "Virksomhed",
            "company-navblock" => "Navision blokkering",
            "CompanyOrderCreated" => "Ny cardshop ordre",
            "CompanyOrderUpdated" => "Cardshop ordre opdateret",
            "CompanyShippingManual" => "Fragtberegning",
            "CompanyShippingPrice" => "Fragtpris aftale",
            "OrderChange" => "Gavevalg ændret",
            "CardsShutdown" => "Gavekort blokeret",
            "CardsOpened" => "Gavekort genåbnet",
            "ShipmentSynced" => "Forsendelse til navision",
            "Sælger ændret",
            "OrderNavSync" => "Ordre opdateret i nav",
            "AdminChangeDeliveryOptions" => "Leveringsmuligheder ændret",
            "OrderDeleted" => "Ordre slettet"

        );

        if (isset($knownTypes[$this->type])) {
            return $knownTypes[$this->type];
        } else {
            return $this->type;
        }


    }

    /*
     * Log function helpers
     */

    public static function logAction($type, $headline, $details, $authorShopUserId = 0, $shopId = 0, $companyId = 0, $companyOrderId = 0, $shopUserId = 0, $orderId = 0, $shipmentId = 0, $otherRefs = '', $debugData = '', $isTech = false) {

        $action = new Actionlog();
        $action->type = $type;
        $action->headline = $headline;
        $action->details = $details;
        $action->created = date('Y-m-d H:i:s');
        $action->ip = $_SERVER['REMOTE_ADDR'];
        $action->author_systemuser_id = router::$systemUser == null ? 0 :  router::$systemUser->id;
        $action->author_shopuser_id = $authorShopUserId;
        $action->shop_id = $shopId;
        $action->company_id = $companyId;
        $action->company_order_id = $companyOrderId;
        $action->shop_user_id = $shopUserId;
        $action->order_id = $orderId;
        $action->shipment_id = $shipmentId;
        $action->other_refs = $otherRefs;
        $action->debugdata = $debugData;
        $action->system_log_id = router::$systemLogId;
        $action->is_tech = $isTech ? 1 : 0;
        $action->save();
        return $action;

    }


    public static function logShopUserAction($type, $headline, $details,$shopUser,$orderId=0,$shipmentId=0, $otherRefs = '', $debugData = '', $isTech = false) {

        $action = new Actionlog();
        $action->type = $type;
        $action->headline = $headline;
        $action->details = $details;
        $action->created = date('Y-m-d H:i:s');
        $action->ip = $_SERVER['REMOTE_ADDR'];
        $action->author_systemuser_id = router::$systemUser == null ? 0 :  router::$systemUser->id;
        $action->author_shopuser_id = $shopUser->id;
        $action->shop_id = $shopUser->shop_id;
        $action->company_id = $shopUser->company_id;
        $action->company_order_id = $shopUser->company_order_id;
        $action->shop_user_id = $shopUser->id;
        $action->order_id = $orderId;
        $action->shipment_id = $shipmentId;
        $action->other_refs = $otherRefs;
        $action->debugdata = $debugData;
        $action->system_log_id = router::$systemLogId;
        $action->is_tech = $isTech ? 1 : 0;
        $action->save();
        return $action;

    }




    // Trigger functions
    function onBeforeSave() {
    }

    function onAfterSave() {
    }

    function onBeforeCreate() {
        $this->validateFields();
    }

    function onAfterCreate() {
    }

    function onBeforeUpdate() {
        $this->validateFields();
    }

    function onAfterUpdate() {
    }

    function onBeforeDestroy() {
    }

    function onAfterDestroy() {
    }

    function validateFields() {
    }


}
