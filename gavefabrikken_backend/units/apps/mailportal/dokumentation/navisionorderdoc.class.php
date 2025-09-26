<?php

// Model NavisionOrderDoc
// Created by Bitworks
//***************************************************************
//   (PRI) id                            int(11)             NO
//***************************************************************

class NavisionOrderDoc extends ActiveRecord\Model {
    static $table_name = "navision_order_doc";
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

    static public function createNavisionOrderDoc($data) {
        $object = new NavisionOrderDoc($data);
        $object->save();
        return ($object);
    }

    static public function readNavisionOrderDoc($id) {
        $object = NavisionOrderDoc::find($id);
        return ($object);
    }

    static public function updateNavisionOrderDoc($data) {
        $object = NavisionOrderDoc::find($data['id']);
        $object->update_attributes($data);
        $object->save();
        return ($object);
    }

    static public function deleteNavisionOrderDoc($id) {
        $object = NavisionOrderDoc::find($id);
        $object->delete();
    }
    //---------------------------------------------------------------------------------------
    // Custom Methods
    //---------------------------------------------------------------------------------------

}
?>

