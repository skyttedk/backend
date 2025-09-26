<?php
// Model PresentModel
// Date created  Mon, 27 Mar 2017 21:04:54 +0200
// Created by Bitworks
//***************************************************************
//   (PRI) id                            int(11)             NO
//   (MUL) present_id                    int(11)             NO
//   (   ) language_id                   int(11)             YES
//   (   ) model_present_no              varchar(250)        YES
//   (   ) model_name                    varchar(250)        YES
//   (   ) model_no                      varchar(250)        YES
//   (   ) media_path                    varchar(1024)       YES
//   (   ) active                        tinyint             NO
//***************************************************************

class PresentModel extends ActiveRecord\Model {
    static $table_name  = "present_model";
    static $primary_key = "id";
      static $has_many = array(
      array('presents', 'class_name' => 'Present'));
    //Relations
    //static $has_many = array(array('<child_table>'));
    //static $belongs_to = array(array('<parent_table>'));

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
    function onBeforeDestroy() {
    }
    function onAfterDestroy()  {
      //Delete records in child tables here
      //Example:
      //<child_table>::table()->delete(array('parent_table_id' => id));

    /*
    $order = Order::find_by_present_model_id($this->model_id);
     if(count($order)>0) {
      throw new exception('Model kan ikke slettes da den er valgt p� ordre.');
     }
     */

    }
    function validateFields() {
      	testRequired($this,'present_id');

        //Check parent records exists here
        testMaxLength($this,'model_present_no',250);
        testMaxLength($this,'model_name',2048);
        testMaxLength($this,'model_no',250);

        $this->model_present_no = trimgf($this->model_present_no);
        $this->model_name = trimgf($this->model_name);
        $this->model_no = trimgf($this->model_no);
    }

//---------------------------------------------------------------------------------------
// Static CRUD Methods
//---------------------------------------------------------------------------------------

    static public function createPresentModel($data) {
        $presentmodel = new PresentModel($data);
        $presentmodel->save();
        return($presentmodel);
    }

    static public function readPresentModel($id) {
        $presentmodel = PresentModel::find($id);
        return($presentmodel);
    }

    static public function updatePresentModel($data) {
        $presentmodel = PresentModel::find($data['id']);
        $presentmodel->update_attributes($data);
        $presentmodel->save();
        return($presentmodel);
    }

    static public function deletePresentModel($id,$model_id,$realDelete=true) {
        if($realDelete) {
                $order = Order::find_by_present_model_id($model_id);
                if(count($order)>0 && $model_id > 0) {
                      throw new exception('Model '.$id.'/'.$model_id.' kan ikke slettes da den er valgt p� ordre.');
                } else {
                  $presentmodel = PresentModel::find($id);
               		$presentmodel->delete();
                }
          } else {  //Soft delete
            $presentmodel->deleted = 1;
            $presentmodel->save();
          }
    }

//---------------------------------------------------------------------------------------
// Custom Methods
//---------------------------------------------------------------------------------------



}
