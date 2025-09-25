<?php
// Model UserPermission
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
class UserPermission extends ActiveRecord\Model {
    static $table_name  = "user_permission";
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

    static public function createUserPermission($data) {
        $userpermission = new UserPermission($data);
        $userpermission->save();
        return($userpermission);
    }

    static public function readUserPermission($id) {
        $userpermission = UserPermission::find($id);
        return($userpermission);
    }

    static public function updateUserPermission($data) {
        $userpermission = UserPermission::find($data['id']);
        $userpermission->update_attributes($data);
        $userpermission->save();
        return($userpermission);
    }

    static public function deleteUserPermission($id,$realDelete=true) {

        if($realDelete) {
            $userpermission = UserPermission::find($id);
    		$userpermission->delete();
          } else {  //Soft delete
            $userpermission->deleted = 1;
            $userpermission->save();
          }
    }
}
?>