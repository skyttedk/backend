<?php

// Model ShopLoan

class ShopLoan extends ActiveRecord\Model {
    static $table_name = "shop_loan";
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

    static public function createShopLoan($data) {
        $obj = new ShopLoan($data);
        $obj->save();
        return ($obj);
    }

    static public function readShopLoan($id) {
        $obj = ShopLoan::find($id);
        return ($obj);
    }

    static public function updateShopLoan($data) {
        $obj = ShopLoan::find($data['id']);
        $obj->update_attributes($data);
        $obj->save();
        return ($obj);
    }

    static public function deleteShopLoan($id, $realDelete = false) {
        $obj = ShopLoan::find($id);
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
