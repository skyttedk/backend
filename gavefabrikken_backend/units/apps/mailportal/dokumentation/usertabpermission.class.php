<?php
// Model UserTabPermission
// Date created  Tue, 21 Feb 2017 19:18:43 +0100
// Created by Bitworks
//***************************************************************
//   (PRI) id                            int(11)             NO
//   (   ) systemuser_id                 int(11)             NO
//   (   ) view_giftshops                tinyint(1)          YES
//   (   ) view_cardshops                tinyint(1)          YES
//   (   ) view_presentadmin             tinyint(1)          YES
//   (   ) view_system                   tinyint(1)          YES
//***************************************************************
class UserTabPermission extends ActiveRecord\Model {
    static $table_name  = "user_tab_permission";
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
      //Delete records in child tables here
      //Example:
      //<child_table>::table()->delete(array('parent_table_id' => id));

    }
    function validateFields() {
        //Check parent records exists here
    }


//---------------------------------------------------------------------------------------
// Static CRUD Methods
//---------------------------------------------------------------------------------------

    static public function createUserTabPermission($data) {
        $UserTabPermission = new UserTabPermission($data);
        $UserTabPermission->save();
        return($UserTabPermission);
    }

    static public function readUserTabPermission($id) {
        $UserTabPermission = UserTabPermission::find($id);
        return($UserTabPermission);
    }

    static public function updateUserTabPermission($data) {
        $UserTabPermission = UserTabPermission::find($data['id']);
        $UserTabPermission->update_attributes($data);
        $UserTabPermission->save();
        return($UserTabPermission);
    }

    static public function deleteUserTabPermission($id,$realDelete=true) {

        if($realDelete) {
            $UserTabPermission = UserTabPermission::find($id);
    		$UserTabPermission->delete();
          } else {  //Soft delete
            $UserTabPermission->deleted = 1;
            $UserTabPermission->save();
          }
    }
}
?>