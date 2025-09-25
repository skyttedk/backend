<?php
// Model CompanyNotesEx
// Date created  Wed, 11 Oct 2017 14:30:29 +0200
// Created by Bitworks
//***************************************************************
//   (PRI) id                            int(11)             NO
//   (   ) company_id                    int(11)             YES
//   (   ) note                          text                YES
//   (   ) created_datetime              datetime            YES
//***************************************************************

class CompanyNotesEx extends ActiveRecord\Model {
    static $table_name  = "company_notes_ex";
    static $primary_key = "id";

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

        $this->created_datetime = date('d-m-Y H:n:s');
        $this->modified_datetime = date('d-m-Y H:n:s');
        $this->validateFields();
    }
    function onAfterCreate()  {}

    function onBeforeUpdate() {

        $this->validateFields();
    }

    function onAfterUpdate()  {

    }
    function onBeforeDestroy() {}
    function onAfterDestroy()  {
      //Delete records in child tables here

      //Example:
      //<child_table>::table()->delete(array('parent_table_id' => id));

    }
    function validateFields() {
                         throw new exception('asd');
        //Check parent records exists here
        $company = Company::find($this->company_id);


    }



//---------------------------------------------------------------------------------------
// Static CRUD Methods
//---------------------------------------------------------------------------------------

    static public function createCompanyNotesEx($data) {
                                        throw new exception('asd');    
        $companynotesex = new CompanyNotesEx($data);
        $companynotesex->save();

        return($companynotesex);
    }

    static public function readCompanyNotesEx($id) {
        $companynotesex = CompanyNotesEx::find($id);
        return($companynotesex);
    }

    static public function updateCompanyNotesEx($data) {
        $companynotesex = CompanyNotesEx::find($data['id']);
        $companynotesex->update_attributes($data);
        $companynotesex->save();
        return($companynotesex);
    }

    static public function deleteCompanyNotesEx($id,$realDelete=true) {

        if($realDelete) {
            $companynotesex = CompanyNotesEx::find($id);
    		$companynotesex->delete();
          } else {  //Soft delete
            $companynotesex->deleted = 1;
            $companynotesex->save();
          }
    }

//---------------------------------------------------------------------------------------
// Custom Methods
//---------------------------------------------------------------------------------------






}
?>

