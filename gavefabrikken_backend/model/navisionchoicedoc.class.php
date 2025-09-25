<?php

// Model NavisionChoiceDoc
// Created by Bitworks
//***************************************************************
//   (PRI) id                            int(11)             NO
//***************************************************************

class NavisionChoiceDoc extends ActiveRecord\Model {
    static $table_name = "navision_choice_doc";
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

    static public function createNavisionChoiceDoc($data) {
        $object = new NavisionChoiceDoc($data);
        $object->save();
        return ($object);
    }

    static public function readNavisionChoiceDoc($id) {
        $object = NavisionChoiceDoc::find($id);
        return ($object);
    }

    static public function updateNavisionChoiceDoc($data) {
        $object = NavisionChoiceDoc::find($data['id']);
        $object->update_attributes($data);
        $object->save();
        return ($object);
    }

    static public function deleteNavisionChoiceDoc($id) {
        $object = NavisionChoiceDoc::find($id);
        $object->delete();
    }
    //---------------------------------------------------------------------------------------
    // Custom Methods
    //---------------------------------------------------------------------------------------

}
?>

