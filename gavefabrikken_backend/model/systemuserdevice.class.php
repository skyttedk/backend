<?php


class SystemUserDevice extends ActiveRecord\Model {

    static $table_name = "system_user_device";
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

    static public function createSystemUserDevice($data) {
        $object = new SystemUserDevice($data);
        $object->save();
        return ($object);
    }

    static public function readSystemUserDevice($id) {
        $object = SystemUserDevice::find($id);
        return ($object);
    }

    static public function updateSystemUserDevice($data) {
        $object = SystemUserDevice::find($data['id']);
        $object->update_attributes($data);
        $object->save();
        return ($object);
    }

    static public function deleteSystemUserDevice($id) {
        $object = SystemUserDevice::find($id);
        $object->delete();
    }

    //---------------------------------------------------------------------------------------
    // Custom Methods
    //---------------------------------------------------------------------------------------

}
