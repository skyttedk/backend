<?php
// Model MailTemplate
// Date created  Mon, 16 Jan 2017 15:27:12 +0100
// Created by Bitworks
//***************************************************************
//   (PRI) id                            int(11)             NO
//   (   ) shop_id                       int(11)             NO
//   (   ) language_id                   int(11)             NO
//   (   ) sender_receipt                varchar(100)        YES
//   (   ) subject_receipt               varchar(100)        YES
//   (   ) template_receipt              text                YES
//   (   ) template_receipt_model        text                YES
//   (   ) sender_reminder_deadline      varchar(100)        YES
//   (   ) subject_reminder_deadline     varchar(100)        YES
//   (   ) template_reminder_deadline    text                YES
//   (   ) sesnder_reminder_pickup       varchar(100)        YES
//   (   ) subject_reminder_pickup       varchar(100)        YES
//   (   ) template_reminder_pickup      text                YES
//   (   ) sender_company_order          varchar(100)        YES
//   (   ) subject_company_order         varchar(100)        YES
//   (   ) template_company_order        text                YES
//   (   ) sender_order_confirmation     varchar(100)        YES
//   (   ) subject_order_confirmation    varchar(100)        YES
//   (   ) template_order_confirmation   text                YES
//   (   ) subject_reminder_giftcertificatevarchar(100)      YES
//   (   ) template_reminder_giftcertificatetext             YES
//   (   ) template_reminder_giftcertificate_listtext        YES
//***************************************************************
class MailTemplate extends ActiveRecord\Model {
    static $table_name  = "mail_template";
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
      	testRequired($this,'shop_id');
      testRequired($this,'language_id');




    }


//---------------------------------------------------------------------------------------
// Static CRUD Methods
//---------------------------------------------------------------------------------------

    static public function createMailTemplate($data) {
        $mailtemplate = new MailTemplate($data);
        $mailtemplate->save();
        return($mailtemplate);
    }

    static public function readMailTemplate($id) {
        $mailtemplate = MailTemplate::find($id);
        return($mailtemplate);
    }

    static public function updateMailTemplate($data) {
        $mailtemplate = MailTemplate::find($data['id']);
        $mailtemplate->update_attributes($data);
        $mailtemplate->save();
        return($mailtemplate);
    }

    static public function deleteMailTemplate($id,$realDelete=true) {

        if($realDelete) {
            $mailtemplate = MailTemplate::find($id);
    		$mailtemplate->delete();
          } else {  //Soft delete
            $mailtemplate->deleted = 1;
            $mailtemplate->save();
          }
    }

//---------------------------------------------------------------------------------------
// Custom Methods
//---------------------------------------------------------------------------------------


public static function getTemplate($shop_id,$language_id)   {

    //0- bruges for valgshops, eller generelt for shops der ikke har opsat templates
    $template = MailTemplate::find_by_shop_id_and_language_id($shop_id,$language_id);
    if(!$template) {
      $template = MailTemplate::find_by_shop_id_and_language_id(0,$language_id);
    }
    if(!$template) {
      $template = MailTemplate::find_by_shop_id_and_language_id(0,1);
    }

    return($template);
}




}
?>

