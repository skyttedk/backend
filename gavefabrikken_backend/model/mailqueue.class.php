<?php
// Model MailQueue
// Date created  Mon, 16 Jan 2017 15:27:06 +0100
// Created by Bitworks
//***************************************************************
//   (PRI) id                            int(11)             NO
//   (   ) mailserver_id                 int(11)             YES
//   (   ) sender_name                   varchar(50)         NO
//   (   ) sender_email                  varchar(100)        NO
//   (   ) recipent_name                 varchar(50)         NO
//   (MUL) recipent_email                varchar(100)        NO
//   (MUL) subject                       varbinary(50)       NO
//   (   ) body                          mediumtext          NO
//   (MUL) sent                          tinyint(4)          YES
//   (   ) error                         tinyint(4)          YES
//   (   ) created_datetime              datetime            YES
//   (   ) sent_datetime                 datetime            YES
//   (   ) error_message                 text                YES
//   (   ) order_id                      int(4)              YES  //gavevalg
//   (   ) company_order_id              int(4)              YES  //kvitteringsnr
//   (   ) user_id                       int(11)             YES
//   (   ) body_base_64                  tinyint(4)          YES
//   (   ) mark                          tinyint(4)          YES
//***************************************************************
class MailQueue extends BaseModel
{
    static $table_name = "mail_queue";
    static $primary_key = "";
    static $before_save = array('onBeforeSave');
    static $after_save = array('onAfterSave');
    static $before_create = array('onBeforeCreate');
    static $after_create = array('onAfterCreate');
    static $before_update = array('onBeforeUpdate');
    static $after_update = array('onAfterUpdate');
    static $before_destroy = array('onBeforeDestroy'); // virker ikke
    static $after_destroy = array('onAfterDestroy');

    // Trigger functions

    static public function createMailQueue($data, $category = 5)
    {
        $mailqueue = new MailQueue($data);
        $mailqueue->sender_name = 'Gavefabrikken';
        $mailqueue->sender_email = 'Gavefabrikken@gavefabrikken.dk';
        $mailqueue->category = $category;
        $mailqueue->save();
        return ($mailqueue);
    }

    static public function readMailQueue($id)
    {
        $mailqueue = MailQueue::find($id);
        return ($mailqueue);
    }

    static public function parseQueueWithIDs()
    {
        $result = [];

        $system = system::first();
        if ($system->is_mailing_withids != 0) {
            $result['busy'] = 1;
            return $result;
        }

        //Set is_mailing to true, save and commit
        $system->is_mailing_withids = 1;
        $system->save();
        System::connection()->commit();
        System::connection()->transaction();

        // Fetch mails with order_id or company_order_id
        $mailqueue = MailQueue::all(array('conditions' => array("sent = 0 && error = 0 && (delivery_datetime IS NULL || delivery_datetime < NOW()) && ((order_id is not null and order_id > 0) || (company_order_id is not null and company_order_id > 0)) && TRIM(LOWER(SUBSTRING_INDEX(recipent_email, '@', -1))) NOT IN (SELECT LOWER(domain) FROM mail_queue_block WHERE released IS NULL and override = 0)"), 'limit' => 60, 'order' => 'priority desc, id asc'));

        $result["found"] = count($mailqueue);
        $result['sent'] = 0;
        $result['error'] = 0;

        // Process each e-mail
        foreach ($mailqueue as $mail) {


            try {
                MailQueue::sendMail($mail);
                $mail->sent = 1;
                $mail->sent_datetime = date('d-m-Y H:i:s');
                $mail->error_message = '';
                $mail->save();
                $result['sent'] += 1;
            } catch (Exception $ex) {
                $mail->error = 1;
                $mail->error_message = $ex->getMessage();
                $mail->save();
                $result['error'] += 1;
            }

            // Commit og start transaktion igen
            System::connection()->commit();
            System::connection()->transaction();

        }

        //Set is_mailing to false
        $system = system::first();
        $system->is_mailing_withids = 0;
        $system->save();
        System::connection()->commit();
        System::connection()->transaction();

        return $result;
    }


    static public function parseQueueWithOutIDs()
    {
        $result = [];

        $system = system::first();
        if ($system->is_mailing_withoutids != 0) {
            $result['busy'] = 1;
            return $result;
        }

        //Set is_mailing to true, save and commit
        $system->is_mailing_withoutids = 1;
        $system->save();
        System::connection()->commit();
        System::connection()->transaction();

        // Fetch mails with order_id or company_order_id
        $mailqueue = MailQueue::all(array('conditions' => array("sent = 0 && error = 0 && (delivery_datetime IS NULL || delivery_datetime < NOW()) && ((order_id = 0 OR order_id IS NULL)  && (company_order_id = 0 OR company_order_id IS NULL)) && TRIM(LOWER(SUBSTRING_INDEX(recipent_email, '@', -1))) NOT IN (SELECT LOWER(domain) FROM mail_queue_block WHERE released IS NULL  and override = 0)"), 'limit' => 60, 'order' => 'priority desc, id asc'));

        $result["found"] = count($mailqueue);
        $result['sent'] = 0;
        $result['error'] = 0;

        // Process each e-mail
        foreach ($mailqueue as $mail) {

            try {
                MailQueue::sendMail($mail);
                $mail->sent = 1;
                $mail->sent_datetime = date('d-m-Y H:i:s');
                $mail->error_message = '';
                $mail->save();
                $result['sent'] += 1;
            } catch (Exception $ex) {
                $mail->error = 1;
                $mail->error_message = $ex->getMessage();
                $mail->save();
                $result['error'] += 1;
            }

            // Commit og start transaktion igen
            System::connection()->commit();
            System::connection()->transaction();

        }

        //Set is_mailing to false
        $system = system::first();
        $system->is_mailing_withoutids = 0;
        $system->save();
        System::connection()->commit();
        System::connection()->transaction();

        return $result;
    }

    /**
     * Old mailqueue parsing
     * @return array
     * @throws \ActiveRecord\DatabaseException
     */
    static public function parseQueue()
    {
        /*
         * Old parseQueue method, remove in 2025, leaved in now for reference (SC 17/04 2024)
        $result = [];
        $system = system::first();
        if ($system->is_mailing == 0) {
            //Set is_mailing to true
            $system->is_mailing = 1;
            $system->save();
            System::connection()->commit();
            System::connection()->transaction();
            //$mailqueue = MailQueue::all(array('conditions' => array('sent' => 0, 'error' => 0  ), 'limit' => 40, 'order' => 'id desc'));
            $mailqueue = MailQueue::all(array('conditions' => array('sent = 0 && error = 0 && (delivery_datetime IS NULL || delivery_datetime < NOW())'), 'limit' => 30, 'order' => 'priority desc, id desc'));

            echo "COUNT: " . count($mailqueue);
            $result['sent'] = 0;
            $result['error'] = 0;
            foreach ($mailqueue as $mail) {

                // router har allerede startet en transaktioner n�r vi n�r hertil
                try {
                    $pos = strpos($mail->subject, "Oversiktsdfsdf");
                    if ($pos) {
                        $mail->sent = 22;
                        $mail->sent_datetime = date('d-m-Y H:i:s');
                        $mail->error_message = '';
                        $mail->save();
                        $result['sent'] += 1;
                    } else {
                        MailQueue::sendMail($mail);
                        $mail->sent = 1;
                        $mail->sent_datetime = date('d-m-Y H:i:s');
                        $mail->error_message = '';
                        $mail->save();
                        $result['sent'] += 1;
                    }

                } catch (Exception $ex) {
                    $mail->error = 1;
                    $mail->error_message = $ex->getMessage();
                    $mail->save();
                    $result['error'] += 1;
                }
                // Commit og start transaktion igen
                System::connection()->commit();
                System::connection()->transaction();
            }
            //Set is_mailing to false
            $system = system::first();
            $system->is_mailing = 0;
            $system->save();
            System::connection()->commit();
            System::connection()->transaction();
        } else {
            $result['busy'] = 1;
        }
        return ($result);
        */
    }


    /**
     * Sending a single mail
     * @param $mailqueue
     * @return void
     * @throws \ActiveRecord\RecordNotFound
     * @throws phpmailerException
     */
    static public function sendMail($mailqueue)
    {
        $htmlBody = $mailqueue->body;

        $mailserverid = $mailqueue->mailserver_id;
        /*
        if(strstr(strtolower($mailqueue->recipent_email),"@hotmail.com") || strstr(strtolower($mailqueue->recipent_email),"@live.dk") || strstr(strtolower($mailqueue->recipent_email),"@outlook.dk"))
        {
           $mailserverid = 1;
        }
        */

        // Special domains send through gavefabrikken.email
        if (strstr(strtolower($mailqueue->recipent_email), "@gea.dk") || strstr(strtolower($mailqueue->recipent_email), "@gea.com") || strstr(strtolower($mailqueue->recipent_email), "@tofffpsoe.dk") || strstr(strtolower($mailqueue->recipent_email), "@topddddsoe.com")) {
            $mailserverid = 7;
            $mailqueue->mailserver_id = $mailserverid;
        }

        $mailserver = MailServer::find($mailserverid);
        $mail = new PHPMailer;
        $mail->isSMTP();
        $mail->CharSet = "UTF-8";
        $mail->isHTML(true);

        if (trimgf($mailserver->username) == "" && trimgf($mailserver->password) == "") {
            $mail->SMTPAuth = false;
            //$mail->SMTPSecure = 'tls';
            //$mail->Username = $mailserver->username;
            //$mail->Password = $mailserver->password;
            $mail->SMTPOptions = array('ssl' => array('verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true));
        } else {
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = 'tls';
            $mail->Username = $mailserver->username;
            $mail->Password = $mailserver->password;

        }

        $mail->Host = $mailserver->server_name;
        $mail->From = $mailserver->sender_email;
        $mail->FromName = $mailserver->sender_name;
        $mail->addAddress(trimgf($mailqueue->recipent_email), '');
        $mail->Subject = ($mailqueue->subject);
        $mail->Body = $htmlBody;
        $mail->AltBody = '';

        if ($mailserverid === 4) {
            $mail->Port = 587;
            $mail->addCustomHeader('X-MC-Metadata', json_encode(['mail_id' => $mailqueue->id, 'category' => $mailqueue->category,]));
        }

        if (!$mail->send()) {
            throw new exception($mail->ErrorInfo);
        }

    }

    function onBeforeSave()
    {
    }

    function onAfterSave()
    {
    }

    function onBeforeCreate()
    {
        $this->created_datetime = date('d-m-Y H:i:s');
        $this->validateFields();

        $this->sender_name = trimgf($this->sender_name);
        $this->sender_email = trimgf($this->sender_email);
        $this->recipent_name = trimgf($this->recipent_name);
        $this->recipent_email = trimgf($this->recipent_email);

        if ($this->mailserver_id != 5 && $this->mailserver_id != 6 && $this->mailserver_id != 7 && $this->mailserver_id != 8) {
            $this->mailserver_id = 4;
        }
        // Send til backend user email, hvis vi er i testDB.
        $system = system::first();
        if (!$system->is_production) $this->recipent_email = $system->test_email;

    }

    function validateFields()
    {
        testRequired($this, 'sender_name');
        testRequired($this, 'sender_email');
        testRequired($this, 'recipent_email');
        testRequired($this, 'subject');
        testRequired($this, 'body');
        testMaxLength($this, 'sender_name', 50);
        testMaxLength($this, 'sender_email', 100);
        testMaxLength($this, 'recipent_name', 50);
        testMaxLength($this, 'recipent_email', 100);
    }

    function onAfterCreate()
    {
    }
    //---------------------------------------------------------------------------------------
    // Static CRUD Methods
    //---------------------------------------------------------------------------------------

    function onBeforeUpdate()
    {
        $this->validateFields();
        $this->sender_name = trimgf($this->sender_name);
        $this->sender_email = trimgf($this->sender_email);
        $this->recipent_name = trimgf($this->recipent_name);
        $this->recipent_email = trimgf($this->recipent_email);
    }

    function onAfterUpdate()
    {
    }

    //---------------------------------------------------------------------------------------
    // Custom Methods
    //---------------------------------------------------------------------------------------

    function onBeforeDestroy()
    {
    }

    function onAfterDestroy()
    {
    }

}
