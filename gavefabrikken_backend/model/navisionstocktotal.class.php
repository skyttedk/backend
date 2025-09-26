<?php

// Model NavisionStockTotal
// Date created  Wed, 11 Jan 2017 14:14:58 +0100
// Created by Bitworks

class NavisionStockTotal extends ActiveRecord\Model {

    static $table_name = "navision_stock_total";
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

    static public function createNavisionStockTotal($data) {
        $NavisionStockTotal = new NavisionStockTotal($data);
        $NavisionStockTotal->save();
        return ($NavisionStockTotal);
    }

    static public function readNavisionStockTotal($id) {
        $NavisionStockTotal = NavisionStockTotal::find($id);
        return ($NavisionStockTotal);
    }

    static public function updateNavisionStockTotal($data) {
        $NavisionStockTotal = NavisionStockTotal::find($data['id']);
        $NavisionStockTotal->update_attributes($data);
        $NavisionStockTotal->save();
        return ($NavisionStockTotal);
    }

    static public function deleteNavisionStockTotal($id, $realDelete = false) {
        $NavisionStockTotal = NavisionStockTotal::find($id);
        if ($realDelete) {
            $NavisionStockTotal->delete();
        }
        else { //Soft delete
            $NavisionStockTotal->deleted = 1;
            $NavisionStockTotal->save();
        }
    }
    //---------------------------------------------------------------------------------------
    // Custom Methods
    //---------------------------------------------------------------------------------------


}
