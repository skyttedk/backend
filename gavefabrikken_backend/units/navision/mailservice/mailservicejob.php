<?php

namespace GFUnit\navision\mailservice;
use GFBiz\units\UnitController;
use GFCommon\Model\Navision\MailWS;

class MailServiceJob
{

    private $mailNo = 0;
    private $languageCode = 0;
    private $mailService = null;

    private $isDev = false;

    public function __construct($mailno,$languageCode,$isDev)
    {
        $this->mailNo = $mailno;
        $this->languageCode = $languageCode;
        $this->isDev = $isDev;
    }

    public function sendMail()
    {

        // Check mail not set
        if($this->languageCode <= 0 || $this->mailNo <= 0) {
            return $this->sendError(true,false,"Invalid parameters for sending e-mail: mailno: ".$this->mailNo.", lang ".$this->languageCode);
        }

        // Check mail no not already sent
        $sendGroup = "nav_".$this->languageCode."_".$this->mailNo;
        $mailCheck = \MailQueue::find('first',array("conditions" => array("send_group" => $sendGroup),"order" => "id desc"));
        $isRetry = 0;

        if($mailCheck != null) {
            if($mailCheck->error == 1) {
                if($mailCheck->is_smtp_error >= 5) {
                    return $this->sendError(true,false,"Could not send e-mail, max retries [".$sendGroup."] (mailqueue: ".$mailCheck->id.")");
                }
                else {
                    $isRetry = intval($mailCheck->is_smtp_error)+1;
                }
            }
            else if($this->isDev == false) {
                return $this->sendError(true,false,"Email already sent [".$sendGroup."] (mailqueue: ".$mailCheck->id.")");
            }
        }

        // Get mail from nav
        try {
            $this->mailService = new MailWS($this->languageCode);
            $mailObject = $this->mailService->getMail($this->mailNo);
        } catch (\Exception $e) {
            return $this->sendError(true,false,"Could get mail from nav [".$sendGroup."]: ".$e->getMessage());
        }

        if($this->isDev) {

            echo "<pre>";
            echo json_encode($mailObject->getDataArray(),JSON_PRETTY_PRINT);
            echo "</pre>";
          //  exit();

        }

        $recipientList = $mailObject->getRecipientEmailList();

        $hasGFReceiver = true;
        foreach($recipientList as $receiver) {
            if(strstr($receiver,"@gavefabrikken.dk") === false) {
                $hasGFReceiver = false;
            }
        }
        
        // Tell nav it has been sent
        //$this->mailService->SetStatus($this->mailNo,true,"Mail sent ok");
        //return true;

        // Get mail server
        $mailserverMap = array(1 => 4, 4 => 4, 5 => 5);
        $mailserver = \MailServer::find($mailserverMap[$this->languageCode]);

        // Create db object
        try {
            $mailQueue = $this->createMailQueue($mailObject,$mailserver,$sendGroup,$isRetry);
        } catch(\Exception $e) {
            return $this->sendError(true,false,"Error creating mail queue object ".$this->mailNo.": ".$e->getMessage()." - ".$e->getFile()." @ ".$e->getLine());
        }


        // Send e-mail
        try {

            // Send mail
            $this->smtpSendMail($mailObject,$mailserver,$mailQueue->id);

            // Set as saved
            $mailQueue->is_smtp_error = $isRetry;
            $mailQueue->error = 0;
            $mailQueue->error_message = '';
            $mailQueue->sent_datetime = date('d-m-Y H:i:s');
            $mailQueue->save();

        } catch(\Exception $e) {

            // Save error in db
            $mailQueue->is_smtp_error = $isRetry;
            $mailQueue->error = 1;
            $mailQueue->error_message = $e->getMessage();
            $mailQueue->sent_datetime = date('d-m-Y H:i:s');
            $mailQueue->save();

            return $this->sendError(true,($mailQueue->is_smtp_error >= 5),"SMTP error sending e-mail ".$this->mailNo.": ".$e->getMessage()." - ".$e->getFile()." @ ".$e->getLine());

        }

        // Tell nav it has been sent
        $this->mailService->SetStatus($this->mailNo,true,(self::$smtpMessage == "" ? "Mail sent ok" : "Mail sent ok: ".self::$smtpMessage));

        return true;

    }

    private function mailLog($content)
    {
        $modtager = "sc@interactive.dk";
        $message = "Mailservice error log<br><br>".$content."\r\n<br>\r\n<br>Data:<br>\r\n<pre>".print_r($_POST,true)."</pre>";
        $headers = "From: noreply@julegavekortet.dk <noreply@julegavekortet.dk>" . "\r\n";
        $headers .= "Reply-To:  <noreply@julegavekortet.dk>\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8";
        $result = mailgf($modtager,"Mailservice error log", $message, $headers);
    }

    private function createMailQueue($mailObj,$mailserver,$sendGroup,$retry)
    {

        $mq = new \MailQueue();
        $mq->mailserver_id = $mailserver->id;
        $mq->sender_name = $mailObj->getSenderName();
        $mq->sender_email = $mailserver->sender_email;
        $mq->recipent_name = "";

        $mq->recipent_email = $mailObj->getRecipientEmail();
        if(trimgf($mq->recipent_email) == "") {
            $mq->recipent_email = "norecipient@gavefabrikken.dk";
        }

        $mq->subject = $mailObj->getSubject();
        $mq->body = html_entity_decode($mailObj->getBody());
        $mq->sent = 1;
        $mq->error = 0;
        $mq->created_datetime = date('d-m-Y H:i:s');
        $mq->delivery_datetime = null;
        $mq->sent_datetime = null;
        $mq->error_message = "";
        $mq->order_id = 0;
        $mq->company_order_id = 0;
        $mq->user_id = 0;
        $mq->body_base_64 = 0;
        $mq->mark = 0;
        $mq->category = 0;
        $mq->is_smtp_error = $retry;
        $mq->bounce_type = null;
        $mq->send_group = $sendGroup;
        $mq->save();

        return $mq;

    }

    private function sendError($sendMailog=false,$sendNav=false,$error="")
    {

        echo "MAILJOB ERROR: ".$error."<br>";

        if($sendMailog == true) {
            $this->mailLog($error);
        }

        if($sendNav == true && $this->isDev == false) {

            echo "SET NAV STATUS: ".$this->mailNo." ERROR: ".$error;
            $this->mailService->SetStatus($this->mailNo,false,$error);
        }

        return false;

    }

    private function smtpSendMail($mailObj, $mailServer,$mailQueueID) {

        // Create php mailer object
        $mail = new \PHPMailer();
        $mail->isSMTP();

        $mail->SMTPDebug = 2;
        $mail->Debugoutput = function($str,$level) { MailServiceJob::setSMTPMessage($str); };

        // Set mailserver
        if(trimgf($mailServer->username) == "" && trimgf($mailServer->password) == "") {
            $mail->SMTPAuth = false;
        } else {
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = 'tls';
            $mail->Username = $mailServer->username;
            $mail->Password = $mailServer->password;
        }

        $mail->Host = $mailServer->server_name;
        $mail->From = $mailServer->sender_email;
        $mail->FromName = $mailServer->sender_name;

        //echo "SMTP HOST: ".$mail->Host."<br>";

        $mail->addCustomHeader('X-MC-PreserveRecipients', 'true');

        // Set email content
        $mail->Subject = utf8_decode((html_entity_decode($mailObj->getSubject(),ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401,"UTF-8")));
        $mail->isHTML(true);
        $mail->Body = utf8_decode((html_entity_decode($mailObj->getBody(),ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401,"UTF-8")));
        $mail->Body = str_replace(array("ÔÇô","Ã”Ã‡Ã´",utf8_encode("ÔÇô"),utf8_encode("Ã”Ã‡Ã´"),utf8_decode("ÔÇô"),utf8_decode("Ã”Ã‡Ã´")),"-",$mail->Body);
        $mail->AltBody = '';

        // Add attachment
        $attachments = $mailObj->getAttachments();
        if(is_array($attachments) && countgf($attachments) > 0) {
            foreach($attachments as $attachment) {
                //echo "ADD ATTACHMENT: ".$attachment["filename"]."<br>";
                if(isset($attachment["base64"]) && $attachment["base64"] != null && !is_array($attachment["base64"]) && isset($attachment["filename"]) && $attachment["filename"] != null && !is_array($attachment["filename"])) {
                    $mail->addStringAttachment(base64_decode($attachment["base64"]), utf8_decode(html_entity_decode($attachment["filename"], ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, "UTF-8")), "base64");
                }
            }
        }
/*
        echo "<pre>".print_r($mailObj,true)."</pre>";
        echo "<pre>".print_r($mailObj->getRecipientEmailList(),true)."</pre>";
        echo "<pre>".print_r($mailObj->getRecipientCCList(),true)."</pre>";
        echo "<pre>".print_r($mailObj->getRecipientBBCList(),true)."</pre>";
*/

        // Set recipient
        //echo "ADD ADDRESS: ".$mailObj->getRecipientEmail()."<br>";
        $recipientList = $mailObj->getRecipientEmailList();
        if(is_array($recipientList) && countgf($recipientList) > 0) {
            foreach ($recipientList as $recipient) {
                //echo "ADD: ".$recipient."<br>";
                $mail->addAddress($recipient);
            }
        }

        // Add cc
        $ccList = $mailObj->getRecipientCCList();
        if(is_array($ccList) && countgf($ccList) > 0) {
            foreach($ccList as $cc) {
                //echo "ADD CC: ".$cc."<br>";
                $mail->addCC($cc);

            }
        }

        // Add bcc
        $bccList = $mailObj->getRecipientBBCList();
        if(is_array($bccList) && countgf($bccList) > 0) {
            foreach($bccList as $bcc) {
                $mail->addBCC($bcc);
                //echo "ADD BCC: ".$bcc."<br>";
            }
        }

        //$mail->addAddress("sc@interactive.dk");

        // Fix mailserver settings for server 4
        if($mailServer->id == 6){
            $mail->Port = 2525;
            $mail->SMTPAutoTLS = false;
            $mail->SMTPSecure = '';

        }

        // Fix mailserver settings for server 4
        if($mailServer->id === 4){
            $mail->Port = 587;
            $mail->addCustomHeader('X-MC-Metadata', json_encode([
                'mail_id' => $mailQueueID
            ]));
        }

        // Send mail
        self::$smtpMessage = "";
        if(!$this->isDev) {
            if(!$mail->send()) {
                //exit();
                throw new exception($mail->ErrorInfo);
            }
        }

        // If is dev, clear all recipients and add local mail
        if($this->isDev) {
            $mail->clearAllRecipients();
            $mail->addAddress("sc@interactive.dk");
            $mail->send();
        }

        //exit();
        
    }


    private static $smtpMessage = "";
    public static function setSMTPMessage($str) {
        if(strstr($str,"250 2.0.0")) {
            self::$smtpMessage = str_replace("SERVER -> CLIENT: ","",$str);
        }
    }


    /*
     * TEST FUNCTION FOR MAILING
     */

    public function testSendMail() {

        $mailServer = \MailServer::find(5);
        
        // Create php mailer object
        $mail = new \PHPMailer();
        $mail->isSMTP();

        $mail->SMTPDebug = 2;
        $mail->Debugoutput = function($str,$level) { MailServiceJob::setSMTPMessage($str); };

        // Set mailserver
        if(trimgf($mailServer->username) == "" && trimgf($mailServer->password) == "") {
            $mail->SMTPAuth = false;
        } else {
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = 'tls';
            $mail->Username = $mailServer->username;
            $mail->Password = $mailServer->password;
        }

        $mail->Host = $mailServer->server_name;
        $mail->From = $mailServer->sender_email;
        $mail->FromName = $mailServer->sender_name;
        //echo "SMTP HOST: ".$mail->Host."<br>";

        $mail->addCustomHeader('X-MC-PreserveRecipients', 'true');
        
        // Set email content
        $mail->Subject = utf8_decode((html_entity_decode("Test emne",ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401,"UTF-8")));
        $mail->isHTML(true);
        $mail->Body = utf8_decode((html_entity_decode("Test body",ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401,"UTF-8")));
        $mail->Body = str_replace(array("ÔÇô","Ã”Ã‡Ã´",utf8_encode("ÔÇô"),utf8_encode("Ã”Ã‡Ã´"),utf8_decode("ÔÇô"),utf8_decode("Ã”Ã‡Ã´")),"-",$mail->Body);
        $mail->AltBody = '';

        $mail->addAddress("sc@interactive.dk");

        /*// Add attachment
        $attachments = $mailObj->getAttachments();
        if(is_array($attachments) && countgf($attachments) > 0) {
            foreach($attachments as $attachment) {
                //echo "ADD ATTACHMENT: ".$attachment["filename"]."<br>";
                $mail->addStringAttachment(base64_decode($attachment["base64"]),utf8_decode(html_entity_decode($attachment["filename"],ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401,"UTF-8")),"base64");
            }
        }
        */

        // Set recipient
        //echo "ADD ADDRESS: ".$mailObj->getRecipientEmail()."<br>";

        // Add cc
        /*
        $ccList = $mailObj->getRecipientCCList();
        if(is_array($ccList) && countgf($ccList)) {
            foreach($ccList as $cc) {
                $mail->addCC($cc);
                //echo "ADD CC: ".$cc."<br>";
            }
        }

        // Add bcc
        if(trimgf($mailObj->getRecipientBCC()) != "") {
            $mail->addBCC($mailObj->getRecipientBCC());
            //echo "ADD BCC".$mailObj->getRecipientBCC()."<br>";
        }
        */
        
        // Fix mailserver settings for server 4
        if($mailServer->id == 6){
            $mail->Port = 2525;
            $mail->SMTPAutoTLS = false;
            $mail->SMTPSecure = '';

        }

        // Fix mailserver settings for server 4
        if($mailServer->id === 4){
            $mail->Port = 587;
        }

        // Send mail
        self::$smtpMessage = "";
        if(!$mail->send()) {
            throw new exception($mail->ErrorInfo);
        }

        echo self::$smtpMessage;

    }

}