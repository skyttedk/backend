<?php

// Model WebOrderLog
// Date created  Wed, 11 Jan 2017 14:14:58 +0100
// Created by Bitworks
//***************************************************************
//   (PRI) id                            int(11)             NO
//   (   ) app_username                  varchar(50)         NO
//   (   ) created_date                  datetime            NO
//   (   ) company_id                    int(11)             YES
//   (   ) shop_id                       int(11)             YES
//   (   ) shopuser_id                   int(11)             YES
//   (   ) order_id                      int(11)             YES
//   (   ) extradata                     varchar(100)        YES
//   (   ) log_event                     varchar(100)        YES
//   (   ) log_description               text                YES
//***************************************************************
class WebOrderLog extends ActiveRecord\Model {
    static $table_name = "weborderlog";
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

    static public function createWebOrderLog($data) {
        $webOrderLog = new WebOrderLog($data);
        $webOrderLog->save();
        return ($webOrderLog);
    }

    static public function readWebOrderLog($id) {
        $webOrderLog = WebOrderLog::find($id);
        return ($webOrderLog);
    }

    static public function updateWebOrderLog($data) {
        $webOrderLog = WebOrderLog::find($data['id']);
        $webOrderLog->update_attributes($data);
        $webOrderLog->save();
        return ($webOrderLog);
    }

    static public function deleteWebOrderLog($id, $realDelete = false) {
        if ($realDelete) {
            $webOrderLog = WebOrderLog::find($id);
            $webOrderLog->delete();
        }
        else { //Soft delete
            $webOrderLog->deleted = 1;
            $webOrderLog->save();
        }
    }
    //---------------------------------------------------------------------------------------
    // Custom Methods
    //---------------------------------------------------------------------------------------

}
?>

