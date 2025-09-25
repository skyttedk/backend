<?php
class shop_present_rules extends ActiveRecord\Model {
	static $table_name  = "shop_present_rules";
	static $primary_key = "id";


	static $before_save =  array('onBeforeSave');
	static $after_save =  array('onAfterSave');

	static $before_create =  array('onBeforeCreate');
	static $after_create =  array('onAfterCreate');

	static $before_update =  array('onBeforeUpdate');
	static $after_update =  array('onAfterUpdate');

	static $before_destroy =  array('onBeforeDestroy');  // virker ikke
	static $after_destroy =  array('onAfterDestroy');

	// Trigger functions
	function onBeforeSave() {}
    function onAfterSave()  {}

	function onBeforeCreate() {


	    $this->validateFields();
	}
	function onAfterCreate()  {}

	function onBeforeUpdate() {

	    $this->validateFields();
	}

	function onAfterUpdate()  {}
    function onBeforeDestroy() {}
	function onAfterDestroy()  {

	}
    function validateFields() {
      	testRequired($this,'shop_id');
     testRequired($this,'profile_data');




    }
}

?>