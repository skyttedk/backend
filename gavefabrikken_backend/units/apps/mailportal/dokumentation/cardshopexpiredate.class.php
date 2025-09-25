<?php

class CardshopExpiredate extends BaseModel {

    static $table_name = "cardshop_expiredate";
    static $primary_key = "id";

    //Relations
    static $has_many = array();

    /**
     * DB EVENTS
     */

    static $before_create = array('onBeforeCreate');
    function onBeforeCreate() {
        $this->validateFields();
    }

    static $after_create = array('onAfterCreate');
    function onAfterCreate() { }

    static $before_update = array('onBeforeUpdate');
    function onBeforeUpdate() {
        $this->validateFields();
    }

    static $after_update = array('onAfterUpdate');
    function onAfterUpdate() { }

    static $before_destroy = array('onBeforeDestroy');
    function onBeforeDestroy() { }

    static $after_destroy = array('onAfterDestroy');
    function onAfterDestroy() { }

    /**
     * FIELD VALIDATIN
     */

    function validateFields() {

    }


}

?>