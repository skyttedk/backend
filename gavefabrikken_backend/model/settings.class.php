<?php

// Model Settings
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
class Settings extends ActiveRecord\Model {
    static $table_name = "app_log";
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


    static public function createSettings($data) {
        $setting = new Settings($data);
        $setting->save();
        return ($setting);
    }

    static public function readSettings($id) {
        $setting = Settings::find($id);
        return ($setting);
    }

    static public function updateSettings($data) {
        $setting = Settings::find($data['id']);
        $setting->update_attributes($data);
        $setting->save();
        return ($setting);
    }

    static public function deleteSettings($id, $realDelete = false) {
        if ($realDelete) {
            $setting = Settings::find($id);
            $setting->delete();
        }

    }
    //---------------------------------------------------------------------------------------
    // Custom Methods
    //---------------------------------------------------------------------------------------

    public static function getSettingsObj($name) {
        return Settings::find("first",array("conditions" => array("settings_key" => $name)));
    }

    public static function getSettingsString($name) {
        $settingsObj = Settings::getSettingsObj($name);
        if($settingsObj == null) return "";
        return $settingsObj->settings_value;
    }

    public static function getSettingsInt($name) {
        return intval(self::getSettingsString($name));
    }

    public static function hasSettings($name) {
        return self::getSettingsObj($name) != null;
    }

    public static function setSettings($name,$value) {

        $settingsObj = self::getSettingsObj($name);
        if($settingsObj == null) {
            $settingsObj = new Settings();
            $settingsObj->setting_key = $name;
            $settingsObj->created = date('d-m-Y H:i:s');
        }

        $settingsObj->settings_value = $value;
        $settingsObj->modified = date('d-m-Y H:i:s');
        $settingsObj->save();

    }

}

?>

