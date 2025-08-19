<?php

class PostnordOrderReport extends ActiveRecord\Model {
    static $table_name = "postnord_orderreport";
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

    static public function createObject($data) {
        $obj = new PostnordOrderReport($data);
        $obj->save();
        return ($obj);
    }

    static public function readObject($id) {
        $obj = PostnordOrderReport::find($id);
        return ($obj);
    }

    static public function updateObject($data) {
        $obj = PostnordOrderReport::find($data['id']);
        $obj->update_attributes($data);
        $obj->save();
        return ($obj);
    }

    static public function deleteObject($id) {
        $obj = PostnordOrderReport::find($id);
        $obj->delete();
    }

    //---------------------------------------------------------------------------------------
    // Custom Methods
    //---------------------------------------------------------------------------------------

}
?>

