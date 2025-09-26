<?php

// Model NavisionCallLog
// Created by Bitworks
//***************************************************************
//   (PRI) id                            int(11)             NO
//***************************************************************

class NavisionSalesPrice extends ActiveRecord\Model {

    static $table_name = "navision_salesprice";
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

    static public function createNavisionSalesPrice($data) {
        $object = new NavisionSalesPrice($data);
        $object->save();
        return ($object);
    }

    static public function readNavisionSalesPrice($id) {
        $object = NavisionSalesPrice::find($id);
        return ($object);
    }

    static public function updateNavisionSalesPrice($data) {
        $object = NavisionSalesPrice::find($data['id']);
        $object->update_attributes($data);
        $object->save();
        return ($object);
    }

    static public function deleteNavisionSalesPrice($id) {
        $object = NavisionSalesPrice::find($id);
        $object->delete();
    }

    //---------------------------------------------------------------------------------------
    // Custom Methods
    //---------------------------------------------------------------------------------------

}
