<?php
// Controller shop
// Date created  Sun, 03 Apr 2016 21:00:47 +0200
// Created by Bitworks
class mail2Controller Extends baseController {

  public function Index() {
    echo "hej";
    $mailtempate = MailTemplate::getTemplate(580,1);
    print_R($mailtempate) ;
  }
  public function getEmailInfo()
 {
     $mail = $_POST["mail"];

    if (strpos($mail, '@') !== false) {
        $result = Dbsqli::getSql2("SELECT `subject`,`sent`,`error`,`error_message`,created_datetime,is_smtp_error,bounce_type FROM `mail_queue` WHERE `recipent_email` = '".$mail."' order by created_datetime");
        response::success(json_encode($result));

    } else {
      echo "error";
    }


 }
}



?>