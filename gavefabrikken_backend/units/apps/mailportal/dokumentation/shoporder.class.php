<?php

// Model ShopOrder

class ShopOrder extends ActiveRecord\Model {
    static $table_name = "shop_order";
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

    static public function createShopOrder($data) {
        $obj = new ShopOrder($data);
        $obj->save();
        return ($obj);
    }

    static public function readShopOrder($id) {
        $obj = ShopOrder::find($id);
        return ($obj);
    }

    static public function updateShopOrder($data) {
        $obj = ShopOrder::find($data['id']);
        $obj->update_attributes($data);
        $obj->save();
        return ($obj);
    }

    static public function deleteShopOrder($id, $realDelete = false) {
        $obj = ShopOrder::find($id);
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
