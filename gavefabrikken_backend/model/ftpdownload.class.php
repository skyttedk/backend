<?php

class FtpDownload extends ActiveRecord\Model {
    static $table_name = "ftp_download";
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

   static public function createFtpDownload($data) {
        $error = false;
        if(isset($data["ftpserver_id"]) == false || is_int($data["ftpserver_id"]) == false ) $error = false;
        if(
            empty($data["file_name"]) ||
            empty($data["file_content"]) ||
            empty($data["file_type"])
          ){$error = true;}
        if($error) {
            throw new exception("Error in variables");
        }
        $ftpDownload= new FtpDownload($data);

        $ftpDownload->save();
        System::connection()->commit();
        System::connection()->transaction();
        return $ftpDownload;
    }


    static public function createObject($data) {
        $obj = new FtpDownload($data);
        $obj->save();
        return ($obj);
    }

    static public function readObject($id) {
        $obj = FtpDownload::find($id);
        return ($obj);
    }

    static public function updateObject($data) {
        $obj = FtpDownload::find($data['id']);
        $obj->update_attributes($data);
        $obj->save();
        return ($obj);
    }

    static public function deleteObject($id) {
        $obj = FtpDownload::find($id);
        $obj->delete();
    }

    //---------------------------------------------------------------------------------------
    // Custom Methods
    //---------------------------------------------------------------------------------------

}
