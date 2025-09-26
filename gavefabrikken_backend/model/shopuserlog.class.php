<?php

// Model ShopUserLog
// Created by Bitworks
//***************************************************************
//   (PRI) id                            int(11)             NO
//***************************************************************

class ShopUserLog extends ActiveRecord\Model {

    static $table_name = "shop_user_log";
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

    static public function createShopUserLog($data) {
        $object = new ShopUserLog($data);
        $object->save();
        return ($object);
    }

    static public function readShopUserLog($id) {
        $object = ShopUserLog::find($id);
        return ($object);
    }

    static public function updateShopUserLog($data) {
        $object = ShopUserLog::find($data['id']);
        $object->update_attributes($data);
        $object->save();
        return ($object);
    }

    static public function deleteShopUserLog($id) {
        $object = ShopUserLog::find($id);
        $object->delete();
    }

    //---------------------------------------------------------------------------------------
    // Custom Methods
    //---------------------------------------------------------------------------------------

}
