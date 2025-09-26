<?php

class MailEvent extends ActiveRecord\Model {
    static $table_name = "mail_event";
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

    static public function createMailEvent($data) {
        $mailevent = new MailEvent($data);
        $mailevent->save();
        return ($mailevent);
    }

    static public function readMailEvent($id) {
        $mailevent = MailEvent::find($id);
        return ($mailevent);
    }

    static public function updateMailEvent($data) {
        $mailevent = MailEvent::find($data['id']);
        $mailevent->update_attributes($data);
        $mailevent->save();
        return ($mailevent);
    }

    static public function deleteMailEvent($id, $realDelete = false) {
        if ($realDelete) {
            $mailevent = MailEvent::find($id);
            $mailevent->delete();
        }
        else { //Soft delete
            $mailevent->deleted = 1;
            $mailevent->save();
        }
    }
    //---------------------------------------------------------------------------------------
    // Custom Methods
    //---------------------------------------------------------------------------------------

}
?>

