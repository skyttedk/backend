<?php
// Model MailportalTemplateContent
// Date created  Wed, 28 Aug 2025
// Created by Claude for MailPortal
//***************************************************************
//   (PRI) id                            int(11)             NO
//   (   ) template_id                   int(11)             NO
//   (   ) language_code                 varchar(2)          NO
//   (   ) subject_localized             varchar(500)        YES
//   (   ) content_html                  longtext            YES
//   (   ) content_text                  longtext            YES
//   (   ) placeholders                  text                YES
//***************************************************************

class MailPortalTemplateContent extends ActiveRecord\Model {
    static $table_name  = "mailportal_template_content";
    static $primary_key = "id";



//---------------------------------------------------------------------------------------
// Static CRUD Methods
//---------------------------------------------------------------------------------------



//---------------------------------------------------------------------------------------
// Custom Methods
//---------------------------------------------------------------------------------------






}
?>

