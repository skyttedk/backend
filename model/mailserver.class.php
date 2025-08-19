<?php
// Model MailServer
// Date created  Mon, 16 Jan 2017 15:27:08 +0100
// Created by Bitworks
//***************************************************************
//   (PRI) id                            int(11)             NO
//   (   ) name                          varchar(30)         YES
//   (   ) server_name                   varchar(200)        YES
//   (   ) username                      varchar(200)        YES
//   (   ) password                      varchar(200)        YES
//   (   ) sender_email                  varchar(200)        YES
//   (   ) sender_name                   varchar(200)        YES
//***************************************************************

class MailServer extends ActiveRecord\Model {
    static $table_name  = "mail_server";
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


        testMaxLength($this,'name',30);
        testMaxLength($this,'server_name',200);
        testMaxLength($this,'username',200);
        testMaxLength($this,'password',200);
        testMaxLength($this,'sender_email',200);
        testMaxLength($this,'sender_name',200);

        $this->name = trimgf($this->name);
        $this->server_name = trimgf($this->server_name);
        $this->username = trimgf($this->username);
        $this->password = trimgf($this->password);
        $this->sender_email = trimgf($this->sender_email);
        $this->sender_name = trimgf($this->sender_name);

    }


//---------------------------------------------------------------------------------------
// Static CRUD Methods
//---------------------------------------------------------------------------------------

    static public function createMailServer($data) {
        $mailserver = new MailServer($data);
        $mailserver->save();
        return($mailserver);
    }

    static public function readMailServer($id) {
        $mailserver = MailServer::find($id);
        return($mailserver);
    }

    static public function updateMailServer($data) {
        $mailserver = MailServer::find($data['id']);
        $mailserver->update_attributes($data);
        $mailserver->save();
        return($mailserver);
    }

}
?>

