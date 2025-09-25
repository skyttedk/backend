<?php
// Model NumberSeries
// Date created  Mon, 16 Jan 2017 15:27:18 +0100
// Created by Bitworks
//***************************************************************
//   (PRI) id                            int(11)             NO
//   (   ) name                          varchar(30)         YES
//   (   ) prefix                        varchar(10)         YES
//   (   ) decimals                      int(11)             NO
//   (   ) current_no                    int(11)             YES
//***************************************************************
class NumberSeries extends BaseModel {
    static $table_name  = "number_series";
    static $primary_key = "id";


    static $before_save =  array('onBeforeSave');
    static $after_save =  array('onAfterSave');

    static $before_create =  array('onBeforeCreate');
    static $after_create =  array('onAfterCreate');

    static $before_update =  array('onBeforeUpdate');
    static $after_update =  array('onAfterUpdate');


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

    function validateFields() {
      	testRequired($this,'decimals');
        testMaxLength($this,'name',30);
        testMaxLength($this,'prefix',10);
        $this->name = trimgf($this->name);
        $this->prefix = trimgf($this->prefix);
    }

//---------------------------------------------------------------------------------------
// Static CRUD Methods
//---------------------------------------------------------------------------------------

    static public function createNumberSeries($data) {
        $numberseries = new NumberSeries($data);
        $numberseries->save();
        return($numberseries);
    }

    static public function readNumberSeries($id) {
        $numberseries = NumberSeries::find($id);
        return($numberseries);
    }

    static public function updateNumberSeries($data) {
        $numberseries = NumberSeries::find($data['id']);
        $numberseries->update_attributes($data);
        $numberseries->save();
        return($numberseries);
    }

    static public function deleteNumberSeries($id,$realDelete=true) {

        if($realDelete) {
            $numberseries = NumberSeries::find($id);
    		$numberseries->delete();
          } else {  //Soft delete
            $numberseries->deleted = 1;
            $numberseries->save();
          }
    }

//---------------------------------------------------------------------------------------
// Custom Methods
//---------------------------------------------------------------------------------------

         static public function getNextNumber($id)  {

         lockTable(NumberSeries::$table_name);
         $numberseries = NumberSeries::find($id);
         $formatExpression = "%0".$numberseries->decimals."d";
         $result = $numberseries->prefix.sprintf($formatExpression,$numberseries->current_no);
         $numberseries->current_no +=1;
         $numberseries->save();
         unlockTable();

          return($result);
     }


}
?>