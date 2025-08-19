<?php

class MailLibrary extends ActiveRecord\Model {
    
    static $table_name = "mail_library";
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

    static public function createMailLibrary($data) {
        $MailLibrary = new MailLibrary($data);
        $MailLibrary->save();
        return ($MailLibrary);
    }

    static public function readMailLibrary($id) {
        $MailLibrary = MailLibrary::find($id);
        return ($MailLibrary);
    }

    static public function updateMailLibrary($data) {
        $MailLibrary = MailLibrary::find($data['id']);
        $MailLibrary->update_attributes($data);
        $MailLibrary->save();
        return ($MailLibrary);
    }

    static public function deleteMailLibrary($id, $realDelete = false) {
        if ($realDelete) {
            $MailLibrary = MailLibrary::find($id);
            $MailLibrary->delete();
        }
        else { //Soft delete
            $MailLibrary = MailLibrary::find($id);
            $MailLibrary->deleted = new DateTime("now");
            $MailLibrary->save();
        }
    }
    //---------------------------------------------------------------------------------------
    // Custom Methods
    //---------------------------------------------------------------------------------------
    
    
}
?>

