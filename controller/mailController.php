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


    public function runqueuewithoutids() {

        \GFCommon\DB\CronLog::startCronJob("MailQueueRunWOID");

        $result = MailQueue::parseQueueWithOutIDs();
        $json = json_encode($result);
        response::success($json);

        if(($result["busy"] ?? 0) == 1) {
            \GFCommon\DB\CronLog::endCronJob(2,"Busy, ikke kørt");
        } else {
            \GFCommon\DB\CronLog::endCronJob(($result["error"] > 0) ? 2 : 1,$result["sent"]." sendt, ".$result["error"]." fejl");
        }
    }

    public function runqueuewithids() {

        \GFCommon\DB\CronLog::startCronJob("MailQueueWIID");

        $result = MailQueue::parseQueueWithIDs();
        $json = json_encode($result);
        response::success($json);

        if(($result["busy"] ?? 0) == 1) {
            \GFCommon\DB\CronLog::endCronJob(2,"Busy, ikke kørt");
        } else {
            \GFCommon\DB\CronLog::endCronJob(($result["error"] > 0) ? 2 : 1,$result["sent"]." sendt, ".$result["error"]." fejl");
        }


    }


  // Called by Cron Job
  public function parseQueue() {

    echo "DEPRECATED!";
    return;

    \GFCommon\DB\CronLog::startCronJob("MailQueueRun");

    $data = $_POST;
    $result = MailQueue::parseQueue($data);
    $json = json_encode($result);
    response::success($json);

    \GFCommon\DB\CronLog::endCronJob($result["error"] > 0 ? 2 : 1,$result["sent"]." sendt, ".$result["error"]." fejl");

  }

    public function getMailByOrderIDToken(){

        $orderID = $_POST["orderID"];
        $token = $_POST["token"];

        try {

            $orderList = Order::find_by_sql("SELECT * FROM `order` where id =".intval($orderID));
            if(count($orderList) == 0) throw new Exception("No orders found");
            $order = $orderList[0];
            if($order == null || $order->id != intval($orderID)) {
                throw new exception("Order not found");
            }

            $userList = ShopUser::find_by_sql("SELECT * FROM `shop_user` where id =".intval($order->shopuser_id));
            if(count($userList) == 0) throw new Exception("No users found");
            $shopuser = $userList[0];
            if($shopuser == null || $shopuser->token != $token) {
                throw new exception("Shopuser not found");
            }

            if($shopuser->token != $token) {
                throw new exception("Token not valid");
            }

            $mail = MailQueue::find_by_order_id($orderID);
            response::success(make_json("response", $mail));

        } catch (Exception $e) {
            echo json_encode(array("status" => 0,"data" => array("response" => array("body","Kunne ikke hente kvittering!"))));
        }

    }

    public function getMailByOrderID(){
        //$orderID = $_POST["orderID"];
        //$mail = MailQueue::find_by_order_id($orderID);
        //response::success(make_json("response", $mail));
        echo json_encode(array("status" => 1,"data" => array("response" => array("body","Du får tilsendt kvittering på e-mail!"))));
    }

    public function getMailByOrderID47(){
        $orderID = $_POST["orderID"];
        $mail = MailQueue::find_by_order_id($orderID);
        response::success(make_json("response", $mail));
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
     $priority = $_POST["priority"];
     $mail_server = isset($_POST["mailserver"]) ? intval($_POST["mailserver"]):"4";
     if($mail_server == 0) $mail_server = 4;


     $mailqueue = new MailQueue();
     $mailqueue->sender_name = "Gavefabrikken";
     $mailqueue->sender_email = "Gavefabrikken@gavefabrikken.dk";
     $mailqueue->recipent_email = $_POST['email'];
     $mailqueue->mailserver_id = $mail_server;
     $mailqueue->subject = $_POST['subject'];
     $mailqueue->send_group = $send_group;
     $mailqueue->priority = $priority;
     
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

