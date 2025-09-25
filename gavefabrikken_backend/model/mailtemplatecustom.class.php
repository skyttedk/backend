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
class MailTemplateCustom extends ActiveRecord\Model {
    static $table_name  = "mail_template_custom";
    static $primary_key = "id";


//---------------------------------------------------------------------------------------
// Static CRUD Methods
//---------------------------------------------------------------------------------------


//---------------------------------------------------------------------------------------
// Custom Methods
//---------------------------------------------------------------------------------------


    public static function getCustomTemplate($shop_id,$language_id)   {

        //0- bruges for valgshops, eller generelt for shops der ikke har opsat templates
        $template = MailTemplateCustom::find_by_shop_id_and_language_id($shop_id,$language_id);
        if(!$template) {
            $template = MailTemplateCustom::find_by_shop_id_and_language_id(0,$language_id);
        }
        if(!$template) {
            $template = MailTemplateCustom::find_by_shop_id_and_language_id(0,1);
        }

        return($template);
    }




}
?>

