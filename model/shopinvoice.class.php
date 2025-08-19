<?php

// Model ShopInvoice

class ShopInvoice extends ActiveRecord\Model {
    static $table_name = "shop_invoice";
    static $primary_key = "id";
    static $before_save = array('onBeforeSave');
    static $after_save = array('onAfterSave');
    static $before_create = array('onBeforeCreate');
    static $after_create = array('onAfterCreate');
    static $before_update = array('onBeforeUpdate');
    static $after_update = array('onAfterUpdate');
    static $before_destroy = array('onBeforeDestroy'); // virker ikke
    static $after_destroy = array('onAfterDestroy');

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
    //---------------------------------------------------------------------------------------
    // Static CRUD Methods
    //---------------------------------------------------------------------------------------

    static public function createShopInvoice($data) {
        $obj = new ShopInvoice($data);
        $obj->save();
        return ($obj);
    }

    static public function readShopInvoice($id) {
        $obj = ShopInvoice::find($id);
        return ($obj);
    }

    static public function updateShopInvoice($data) {
        $obj = ShopInvoice::find($data['id']);
        $obj->update_attributes($data);
        $obj->save();
        return ($obj);
    }

    static public function deleteShopInvoice($id, $realDelete = false) {
        $obj = ShopInvoice::find($id);
        if ($realDelete) {
            $obj->delete();
        }
        else { //Soft delete
            $obj->deleted = 1;
            $obj->save();
        }
    }
    //---------------------------------------------------------------------------------------
    // Custom Methods
    //---------------------------------------------------------------------------------------

}
