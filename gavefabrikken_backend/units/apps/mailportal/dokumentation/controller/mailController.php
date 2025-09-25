<?php
// Controller shop
// Date created  Sun, 03 Apr 2016 21:00:47 +0200
// Created by Bitworks
class mailController Extends baseController {

  public function Index() {
  }

  public function _createMailQueue() {
    $data = $_POST;
    $mailqueue = MailQueue::createMailQueue($data);
    response::success(make_json("response", $mailqueue));
  }
  // Called by Cron Job

  public function parseQueue() {



    $data = $_POST;
    $result = MailQueue::parseQueue($data);
    $json = json_encode($result);
    response::success($json);
  }


   public function createMailTest() {
     $mailqueue = new MailQueue();
     $mailqueue->sender_name = "Gavefabrikken";
     $mailqueue->sender_email = "Gavefabrikken@gavefabrikken.dk";
     $mailqueue->recipent_email =$_POST['recipent'];
     $mailqueue->mailserver_id = 1;
     $mailqueue->subject = 'Test E-mail';
     $mailqueue->body = "<html><head>
     <meta charset='UTF-8''>
      <style>
        td {
          width:50%;
         }
       </style>
     </head><body>";
//     $mailqueue->body .= utf8_decode(base64_decode($_POST['body']));
//     $mailqueue->body .= utf8_decode($_POST['body']);
//     $mailqueue->body .= 'Dette er en test E-mail.';
     $mailqueue->body .= $_POST['body'];
     $mailqueue->body .= "</body></html>";
     $mailqueue->save();
     response::success(make_json("response", $mailqueue));
   }

   public function createMailToUsername() {
    // g�lder kun for gavekort
    $shopuser = ShopUser::find_by_username($_POST['username']);
    if($shopuser->is_giftcertificate==0) {
       throw new exception('Denne funktione kan kun bruges på gavekort brugere');
    }
     if($shopuser) {
      $userattribute = UserAttribute::find_by_shopuser_id_and_is_email($shopuser->id,1);

     $mailqueue = new MailQueue();
     $mailqueue->sender_name = "Gavefabrikken";
     $mailqueue->sender_email = "Gavefabrikken@gavefabrikken.dk";
     $mailqueue->recipent_email = $userattribute->attribute_value;
     $mailqueue->mailserver_id = 4;
     $mailqueue->subject = $_POST['subject'];
     $mailqueue->body = "<html><head>
     <meta charset='UTF-8''>
      <style>
        td {
          width:50%;
         }
       </style>
    </head><body>";

     $mailqueue->body .= utf8_decode(base64_decode($_POST['body']));
     $mailqueue->body .= "</body></html>";

     $mailqueue->save();
     response::success(make_json("response", $mailqueue));
     } else {
         throw new exception ("username not found");
     }
  }

   public function createMailWithTemplate() {
     $mailqueue = new MailQueue();
     $mailqueue->sender_name = "Gavefabrikken";
     $mailqueue->sender_email = "Gavefabrikken@gavefabrikken.dk";
     $mailqueue->recipent_email = $_POST['email'];
     $mailqueue->mailserver_id = 4;
     $mailqueue->subject = $_POST['subject'];
     $mailqueue->body = '<html><head>
      <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
      <style>
        td {
          width:50%;
         }
       </style>
     </head><body>';
     $mailqueue->body .= "<a href=\"".GFConfig::SHOP_URL_PRIMARY."gavevalg/ek3066brabrand\" mc:disable-tracking>".utf8_decode($_POST['body'])."</a>";
     $mailqueue->body .= "</body></html>";
     $mailqueue->save();
     //sleep(1);
     $dummy = [];
     response::success(json_encode($dummy));
  }




  public function createMail() {

     $send_group = isset($_POST["send_group"]) ? $_POST["send_group"]:"ddd";
     $mail_server = isset($_POST["mailserver"]) ? intval($_POST["mailserver"]):"4";
     if($mail_server == 0) $mail_server = 4;

     
     $mailqueue = new MailQueue();
     $mailqueue->sender_name = "Gavefabrikken";
     $mailqueue->sender_email = "Gavefabrikken@gavefabrikken.dk";
     $mailqueue->recipent_email = $_POST['email'];
     $mailqueue->mailserver_id = $mail_server;
     $mailqueue->subject = $_POST['subject'];
     $mailqueue->send_group = $send_group;
     
     // Check for sendtime
     if(isset($_POST["sendtime"]) && trimgf($_POST["sendtime"]) != "") {
            $mailqueue->delivery_datetime = trimgf($_POST["sendtime"]);
     }

     $mailqueue->body = '<html><head>
      <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
      <style>
        td {
          width:50%;
         }
       </style>
     </head><body>';
     $mailqueue->body .= base64_decode($_POST['body']);
     $mailqueue->body .= "</body></html>";
     $mailqueue->save();
     //sleep(1);
     response::success(make_json("response", $mailqueue));
  }


   public function createMail2() {
     $mailqueue = new MailQueue();
     $mailqueue->sender_name = "Gavefabrikken";
     $mailqueue->sender_email = "Gavefabrikken@gavefabrikken.dk";
     $mailqueue->recipent_email = $_POST['email'];
     $mailqueue->mailserver_id = 4;
     $mailqueue->subject = $_POST['subject'];

     $mailqueue->body = "<html lang='da'><head>
     <meta charset='UTF-8'>
      <style>
        td {
          width:50%;
         }
       </style>
     </head><body>";

     $mailqueue->body .= base64_decode($_POST['body']);
     $mailqueue->body .= "</body></html>";
     $mailqueue->save();
     sleep(1);
     response::success(make_json("response", $mailqueue));
  }
    public function createMailToEmailRecipent() {

     $mailqueue = new MailQueue();
     $mailqueue->sender_name = "Gavefabrikken";
     $mailqueue->sender_email = "Gavefabrikken@gavefabrikken.dk";
     $mailqueue->recipent_email = $_POST['email'];
     $mailqueue->mailserver_id = 2;
     $mailqueue->subject = $_POST['subject'];
     $mailqueue->body = "<html><head>
     <meta charset='UTF-8'>
      <style>
        td {
          width:50%;
         }
       </style>
    </head><body>";

     $mailqueue->body .= utf8_decode(base64_decode($_POST['body']));
     $mailqueue->body .= "</body></html>";
     $mailqueue->save();
     response::success(make_json("response", $mailqueue));

  }


   public function createMailToCompanyResponsible() {

    $shopuser = ShopUser::find_by_username($_POST['username']);
    if($shopuser->is_giftcertificate==0) {
       throw new exception('Denne funktione kan kun bruges på gavekort brugere');
    }
     if($shopuser) {
      $company = company::find($shopuser->company_id);

     $mailqueue = new MailQueue();
     $mailqueue->sender_name = "Gavefabrikken";
     $mailqueue->sender_email = "Gavefabrikken@gavefabrikken.dk";

     $mailqueue->recipent_email = $company->contact_email;
     //$mailqueue->recipent_email = "us@gavefabrikken.dk";
     $mailqueue->mailserver_id = 2;
     $mailqueue->subject = $_POST['subject'];

    $mailqueue->body = "<html><head>
    <meta charset='UTF-8''>
      <style>
        td {
          width:50%;
         }
       </style>
    </head><body>";

     $mailqueue->body .= utf8_decode(base64_decode($_POST['body']));
     $mailqueue->body .= "</body></html>";

     $mailqueue->save();
     response::success(make_json("response", $mailqueue));
     } else {
         throw new exception ("username not found");
     }

  }

  public function getMailStats() {
    $result = [];
    $mailqueue = MailQueue::all(array('conditions' => array('sent' => 0, 'error' => 0)));
    $result['queue'] = countgf($mailqueue);
    $mailqueue = MailQueue::all(array('conditions' => array('sent' => 1, 'error' => 0)));
    $result['sent'] = countgf($mailqueue);
    $mailqueue = MailQueue::all(array('conditions' => array('sent' => 0, 'error' => 1)));
    $result['error'] = countgf($mailqueue);
    $mailqueue = MailQueue::all(array('conditions' => array('sent' => 0, 'error' => 1)));
    $systemuser = SystemUser::all(array('username' => 'mail_queue'));
    $json = json_encode($result);
    response::success($json);
  }

    public function getMailServers() {
       $mailservers = MailServer::find('all', array('select' => 'id, name'));
       response::success(make_json("servers", $mailservers));
    }

    public function showmail()
    {
       // echo 'asd';
       $mailqueue = MailQueue::find($_GET['id']);
       echo  $mailqueue->body;
    }

}
?>

