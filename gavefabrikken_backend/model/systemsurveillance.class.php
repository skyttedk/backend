<?php
// Model SystemSurveillance
// Date created  Sat, 13 May 2017 14:33:13 +0200
// Created by Bitworks
//***************************************************************
//   (PRI) id                            int(11)             NO
//   (   ) system_log_id                 int(11)             YES
//   (   ) username                      varchar(45)         YES
//   (   ) password                      varchar(100)        YES
//   (   ) ip                            varchar(50)         YES
//   (   ) user_agent                    varchar(1024)       YES
//   (   ) referrer                      varchar(1024)       YES
//   (   ) created_datetime              datetime            YES
//***************************************************************
class SystemSurveillance extends ActiveRecord\Model {
    static $table_name  = "system_surveillance";
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
        $this->created_datetime = date('d-m-Y H:n:s');

        $this->validateFields();
    }
    function onAfterCreate()  {}

    function onBeforeUpdate() {

        $this->validateFields();
    }

    function onAfterUpdate()  {}
    function onBeforeDestroy() {}
    function onAfterDestroy()  {
      //Delete records in child tables here
      //Example:
      //<child_table>::table()->delete(array('parent_table_id' => id));

    }
    function validateFields() {

        //Check parent records exists here
        testMaxLength($this,'ip',50);

        $this->ip = trimgf($this->ip);

    }


//---------------------------------------------------------------------------------------
// Static CRUD Methods
//---------------------------------------------------------------------------------------

    static public function createSystemSurveillance($data) {
        $systemsurveillance = new SystemSurveillance($data);
        $systemsurveillance->save();
        return($systemsurveillance);
    }

    static public function readSystemSurveillance($id) {
        $systemsurveillance = SystemSurveillance::find($id);
        return($systemsurveillance);
    }

    static public function updateSystemSurveillance($data) {
        $systemsurveillance = SystemSurveillance::find($data['id']);
        $systemsurveillance->update_attributes($data);
        $systemsurveillance->save();
        return($systemsurveillance);
    }

    static public function deleteSystemSurveillance($id,$realDelete=true) {

        if($realDelete) {
            $systemsurveillance = SystemSurveillance::find($id);
    		$systemsurveillance->delete();
          } else {  //Soft delete
            $systemsurveillance->deleted = 1;
            $systemsurveillance->save();
          }
    }

//---------------------------------------------------------------------------------------
// Custom Methods
//---------------------------------------------------------------------------------------






}
?>

