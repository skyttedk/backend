<?php
// Model Language
// Date created  Mon, 16 Jan 2017 15:27:02 +0100
// Created by Bitworks
//***************************************************************
//   (PRI) id                            int(11)             NO
//   (   ) language_code                 varchar(2)          NO
//   (   ) name                          varchar(100)        NO
//***************************************************************
class Language extends BaseModel {
	static $table_name  = "language";
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
      	testRequired($this,'language_code');
        testRequired($this,'name');

		testMaxLength($this,'language_code',2);
        testMaxLength($this,'name',100);

        $this->language_code = trimgf($this->language_code);
        $this->name = trimgf($this->name);

    }

    public static function getLanguageMap()
    {
        $list = self::find('all');
        $map = array();
        foreach($list as $item) {
            $map[$item->id] = $item;
        }
        return $map;
    }

//---------------------------------------------------------------------------------------
// Static CRUD Methods
//---------------------------------------------------------------------------------------

	static public function createLanguage($data) {
		$language = new Language($data);
        $language->save();
        return($language);
	}

	static public function readLanguage($id) {
		$language = Language::find($id);
        return($language);
	}

	static public function updateLanguage($data) {
		$language = Language::find($data['id']);
		$language->update_attributes($data);
        $language->save();
        return($language);
	}

	static public function deleteLanguage($id,$realDelete=true) {

	    if($realDelete) {
            $language = Language::find($id);
    		$language->delete();
          } else {  //Soft delete
            $language->deleted = 1;
		    $language->save();
          }
    }
    


}
?>