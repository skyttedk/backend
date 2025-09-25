<?php

// Model NavisionCallLog
// Created by Bitworks
//***************************************************************
//   (PRI) id                            int(11)             NO
//***************************************************************

class NavisionCompanyNo extends ActiveRecord\Model {

    static $table_name = "navision_company_no";
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

    static public function createNavisionCompanyNo($data) {
        $object = new NavisionCompanyNo($data);
        $object->save();
        return ($object);
    }

    static public function readNavisionCompanyNo($id) {
        $object = NavisionCompanyNo::find($id);
        return ($object);
    }

    static public function updateNavisionCompanyNo($data) {
        $object = NavisionCompanyNo::find($data['id']);
        $object->update_attributes($data);
        $object->save();
        return ($object);
    }

    static public function deleteNavisionCompanyNo($id) {
        $object = NavisionCompanyNo::find($id);
        $object->delete();
    }

    //---------------------------------------------------------------------------------------
    // Custom Methods
    //---------------------------------------------------------------------------------------

    public static function getNextCompanyNo($language_code) {
        try {
            $companyno = NavisionCompanyNo::find("first",array("conditions" => array("language_code" => intval($language_code))));
            if($companyno == null) {
                return 0;
            }
        } catch(Exception $e) {
            return 0;
        }
        return $companyno->next_number;
    }

    public static function setUsedCompanyNo($language_code,$company_no) {
        $companyno = NavisionCompanyNo::find("first",array("conditions" => array("language_code" => intval($language_code))));
        if($companyno == null) {
            throw new Exception("Could not find company number entry");
        }
        if($companyno->next_number <= $company_no) {
            $companyno->next_number = $company_no+1;
            $companyno->save();
        }
    }

}
?>

