<?php

class cardOrderMailsController Extends baseController
{

  public function Index() {
        echo "hej";
  }
  public function mailAfterSaleSverige(){
    $this->sendWelcomeMailPhysicsCardSverige();
  }


  public function mailAfterSaleNorge(){
    $this->sendWelcomeMailPhysicsCardNorge();
  }


  public function mailAfterSale(){
    $this->sendWelcomeMailPhysicsCard();

  }

   /*  send Send velkomst mail til kort k�b, kun fysiske */
  private function sendWelcomeMailPhysicsCardSverige(){
  $listOfNewOrders = Companyorder::all(array('conditions' => array(
    "welcome_mail_is_send = 0 and
     send_welcome_mail = 1  and
     is_printed = 0  and is_cancelled = 0 and
      (shop_id in (select shop_id from cardshop_settings where language_code = 5) )  and  welcome_check = 0 and
     created_datetime < NOW() - INTERVAL 5 DAY limit 1"
    )));

   // print_r($listOfNewOrders);

    if(sizeofgf($listOfNewOrders) > 0){
    foreach($listOfNewOrders as $order){
        if($this->hasReceivedWelcomeMail($order->company_id)){
          Dbsqli::setSql2("update company_order set welcome_check = 1 where company_id = ".$order->company_id );

        } else if($this->hasPhysicalCard($order->company_id)){
            Dbsqli::setSql2("update company_order set welcome_check = 1 where company_id = ".$order->company_id );
        } else {

         $token = $this->getCompanyToken($order->company_id);
         $mailTemplate = mailtemplate::find(11);
         $mailTemplate->template_receipt;
         $template = str_replace('{link}',$token,$mailTemplate->template_receipt);
        $maildata = [];
	    $maildata['sender_email'] =  "noreply@presentbolaget.net";
	    $maildata['recipent_email'] =  "us@gavefabrikken.dk"; //$order->contact_email;
	    $maildata['subject']= utf8_encode($mailTemplate->subject_receipt);
	    $maildata['body'] = utf8_decode($order->contact_email."<br>".$template);
	    $maildata['mailserver_id'] = 5;
      $maildata['used_template']  = $mailTemplate->id;
      
        echo "update company_order set welcome_mail_is_send = 1 where company_id = ".$order->company_id;
        Dbsqli::setSql2("update company_order set welcome_mail_is_send = 1 where company_id = ".$order->company_id );

        MailQueue::createMailQueue($maildata);
        system::connection()->commit();
      }
    }


  }
        $dummy = [];
          response::success(json_encode($dummy));

  }



   /*  NORGE Send velkomst mail til kort k�b, kun fysiske */
  private function sendWelcomeMailPhysicsCardNorge(){
  $listOfNewOrders = Companyorder::all(array('conditions' => array(
    "welcome_mail_is_send = 0 and
     send_welcome_mail = 1  and
     is_printed = 0  and is_cancelled = 0 and
      (shop_id = 57 || shop_id = 58 || shop_id = 59 || shop_id = 272 || shop_id = 574)  and  welcome_check = 0 and
     created_datetime < NOW() - INTERVAL 5 DAY limit 1"
    )));

   // print_r($listOfNewOrders);

    if(sizeofgf($listOfNewOrders) > 0){
    foreach($listOfNewOrders as $order){
        if($this->hasReceivedWelcomeMail($order->company_id)){
          Dbsqli::setSql2("update company_order set welcome_check = 1 where company_id = ".$order->company_id );

        } else if($this->hasPhysicalCard($order->company_id)){
            Dbsqli::setSql2("update company_order set welcome_check = 1 where company_id = ".$order->company_id );
        } else {

         $token = $this->getCompanyToken($order->company_id);
         $mailTemplate = mailtemplate::find(7);
         $mailTemplate->template_receipt;
         $template = str_replace('{link}',$token,$mailTemplate->template_receipt);
        $maildata = [];
	    $maildata['sender_email'] =  "no-reply@gavefabrikken.dk";
	    $maildata['recipent_email'] =  $order->contact_email;
	    $maildata['subject']= utf8_encode($mailTemplate->subject_receipt);
	    $maildata['body'] = utf8_decode($template);
	    $maildata['mailserver_id'] = 2;
      $maildata['used_template']  = $mailTemplate->id;

        Dbsqli::setSql2("update company_order set welcome_mail_is_send = 1 where company_id = ".$order->company_id );

        MailQueue::createMailQueue($maildata);
        system::connection()->commit();
      }
    }


  }
        $dummy = [];
          response::success(json_encode($dummy));

  }




   /* Send velkomst mail til kort k�b, kun fysiske */
  private function sendWelcomeMailPhysicsCard(){


  $listOfNewOrders = Companyorder::all(array('conditions' => array(
    "welcome_mail_is_send = 0 and
     send_welcome_mail = 1  and
     order_state in (4,5) and
     `created_datetime` > '2021-09-29 00:00:00' and
     is_email = 0  and is_cancelled = 0 and shop_id in(SELECT shop_id FROM `cardshop_settings` WHERE `language_code` = 1 )   and welcome_check = 0 and created_datetime < NOW() - INTERVAL 5 DAY limit 1"
    )));

 
    if(sizeofgf($listOfNewOrders) > 0){
    foreach($listOfNewOrders as $order){

    $expireDate = $order->expire_date->format('Y-m-d');

        if($this->hasReceivedWelcomeMail($order->company_id)){
            Dbsqli::setSql2("update company_order set welcome_check = 1 where company_id = ".$order->company_id );
        } else if($this->hasPhysicalCard($order->company_id)){
            Dbsqli::setSql2("update company_order set welcome_check = 1 where company_id = ".$order->company_id );
        } else {

         $token = $this->getCompanyToken($order->company_id);
         $mailTemplate = "";
         if($expireDate == "2026-04-01"){
            $mailTemplate = mailtemplate::find(27);
         } else {
             $mailTemplate = mailtemplate::find(6);
         }
         $template = str_replace('{link}',$token,$mailTemplate->template_receipt);

        $maildata = [];
	    $maildata['sender_email'] =  "noreply@gavefabrikken.net";
	    $maildata['recipent_email'] =   $order->contact_email; // "us@gavefabrikken.dk";
	    $maildata['subject']= utf8_encode($mailTemplate->subject_receipt);
	    $maildata['body'] = utf8_decode($template);
	    $maildata['mailserver_id'] = 4;
      $maildata['used_template']  = $mailTemplate->id;

        Dbsqli::setSql2("update company_order set welcome_mail_is_send = 1 where company_id = ".$order->company_id );

        MailQueue::createMailQueue($maildata);
        system::connection()->commit();
        system::connection()->transaction();
        echo  $order->company_id ;
      }
    }


  }
        $dummy = [];
          response::success(json_encode($dummy));

  }



  /* send koder til kort k�b, email koder  */

   private function hasReceivedWelcomeMail($cvr){
     return sizeofgf(Companyorder::find('all', array('conditions' => array('company_id = ? and welcome_mail_is_send = 1 ',$cvr )))) > 0 ? true:false;
   }
   private function getCompanyToken($companyId){
        $company = Company::Find($companyId);
        return $company->attributes["token"];

   }
   private function hasPhysicalCard($company_id)
   {
    $listOfNewOrders = Companyorder::all(array('conditions' => array(
        "is_printed = 1  and is_cancelled = 0  and   company_id = ".$company_id
    )));
    return sizeofgf($listOfNewOrders) > 0 ? true:false;
   }





}


