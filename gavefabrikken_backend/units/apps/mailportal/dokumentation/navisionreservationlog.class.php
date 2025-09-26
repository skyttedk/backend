<?php

// Model NavisionCallLog
// Created by Bitworks
//***************************************************************
//   (PRI) id                            int(11)             NO
//***************************************************************

class NavisionReservationLog extends ActiveRecord\Model {

    static $table_name = "navision_reservation_log";
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

    static public function createNavisionReservationLog($data) {
        $object = new NavisionReservationLog($data);
        $object->save();
        return ($object);
    }

    static public function readNavisionReservationLog($id) {
        $object = NavisionReservationLog::find($id);
        return ($object);
    }

    static public function updateNavisionReservationLog($data) {
        $object = NavisionReservationLog::find($data['id']);
        $object->update_attributes($data);
        $object->save();
        return ($object);
    }

    static public function deleteNavisionReservationLog($id) {
        $object = NavisionReservationLog::find($id);
        $object->delete();
    }

    //---------------------------------------------------------------------------------------
    // Custom Methods
    //---------------------------------------------------------------------------------------

}
