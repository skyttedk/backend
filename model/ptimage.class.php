<?php
// Model SystemLog
// Date created  Mon, 16 Jan 2017 15:29:59 +0100
// Created by Bitworks
//***************************************************************
//   (PRI) id                            int(11)             NO
//   (   ) user_id                       varchar(100)        YES
//   (MUL) controller                    varchar(100)        YES
//   (   ) action                        varchar(100)        YES
//   (   ) data                          text                YES
//   (MUL) created_datetime              datetime            YES
//   (MUL) committed                     tinyint(4)          YES
//   (   ) error_message                 text                YES
//   (   ) error_trace                   text                YES
//***************************************************************
class Ptimage extends BaseModel {
  static $table_name = "pt_image";
  static $primary_key = "id";
  static $before_create = array('onBeforeCreate');
  static $after_create =  array('onAfterCreate');

// Trigger functions
  function onBeforeCreate() {

  }

    function onAfterCreate()  {

    }

  function validateFields()
  {


  }

//---------------------------------------------------------------------------------------
// Static CRUD Methods
//---------------------------------------------------------------------------------------


}
?>