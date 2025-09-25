<?php
// Model CompanyNotes
// Date created  Mon, 16 Jan 2017 15:26:55 +0100
// Created by Bitworks
//***************************************************************
//   (PRI) id                            int(11)             NO
//   (   ) company_id                    int(11)             YES
//   (   ) company_order_id              int(11)             YES
//   (   ) note                          text                YES
//   (   ) note_internal                 text                YES
//***************************************************************

class CompanyNotes extends ActiveRecord\Model {
    static $table_name  = "company_notes";
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

    }


//---------------------------------------------------------------------------------------
// Static CRUD Methods
//---------------------------------------------------------------------------------------

    static public function createCompanyNotes($data) {
        $companynotes = new CompanyNotes($data);
        $companynotes->save();
        return($companynotes);
    }

    static public function readCompanyNotes($id) {
        $companynotes = CompanyNotes::find($id);
        return($companynotes);
    }

    static public function updateCompanyNotes($data) {
        $companynotes = CompanyNotes::find($data['id']);
        $companynotes->update_attributes($data);
        $companynotes->save();
        return($companynotes);
    }

    static public function deleteCompanyNotes($id,$realDelete=true) {

        if($realDelete) {
            $companynotes = CompanyNotes::find($id);
    		$companynotes->delete();
          } else {  //Soft delete
            $companynotes->deleted = 1;
            $companynotes->save();
          }
    }


}
?>

