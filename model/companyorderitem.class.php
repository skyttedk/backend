<?php

class CompanyOrderItem extends BaseModel {

    static $table_name = "company_order_item";
    static $primary_key = "id";

    //Relations
    static $has_many = array();

    /**
     * DB EVENTS
     */

    static $before_create = array('onBeforeCreate');
    function onBeforeCreate() {
        $this->created_date = date('d-m-Y H:i:s');
        $this->created_by = router::$systemUser == null ? 0 : router::$systemUser->id;
        $this->updated_date = date('d-m-Y H:i:s');
        $this->updated_date = router::$systemUser == null ? 0 : router::$systemUser->id;
        $this->validateFields();
    }

    static $after_create = array('onAfterCreate');
    function onAfterCreate() { }

    static $before_update = array('onBeforeUpdate');
    function onBeforeUpdate() {
        $this->updated_date = date('d-m-Y H:i:s');
        $this->updated_date = router::$systemUser == null ? 0 : router::$systemUser->id;
        $this->validateFields();
    }

    static $after_update = array('onAfterUpdate');
    function onAfterUpdate() { }

    static $before_destroy = array('onBeforeDestroy');
    function onBeforeDestroy() { }

    static $after_destroy = array('onAfterDestroy');
    function onAfterDestroy() { }

    /**
     * FIELD VALIDATIN
     */

    function validateFields() {

    }

    /**
     * GET COMPANY ORDER ITEM MAP
     */

    public static function getCompanyOrderMap($companyorder_id)
    {
        $itemMap = array();
        $itemList = CompanyOrderItem::find('all',array("conditions" => array("companyorder_id" => $companyorder_id)));
        foreach($itemList as $item) {
            $itemMap[$item->type] = $item;
        }
        return $itemMap;
    }

}

?>