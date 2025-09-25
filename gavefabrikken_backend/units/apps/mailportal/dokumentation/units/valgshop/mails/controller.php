<?php

namespace GFUnit\valgshop\mails;
use GFBiz\units\UnitController;
use GFUnit\navision\mailservice\MailServiceJob;

class Controller extends UnitController
{

    public function __construct()
    {
        parent::__construct(__FILE__);
    }
    public function sendStatus(){
        $shop_id = intvalgf($_POST["shop_id"]);
        $shopMetadata = \ShopMetadata::find("first",array("conditions" => array("shop_id = ?",$shop_id)));
        if($shopMetadata == null || $shopMetadata->id == 0) {
            throw new \Exception("Could not find data for shop id ".$shop_id);
        }
        echo json_encode(array("status" => 1,"send"=>$shopMetadata->mail_welcome_sent));


    }
    public function valgshopwelcome() {


        // Get inputs
        $language_id = intvalgf($_POST["language_id"]);
        $shop_id = intvalgf($_POST["shop_id"]);
        $recipient_email = trimgf($_POST["recipient_email"]);
        $subject = trimgf($_POST["subject"]);
        $content = trimgf($_POST["content"]);
        $resend = isset($_POST["resend"]) ? intvalgf($_POST["resend"]) : 0;
        

        /*
        $resend = 1;
        $language_id = intvalgf(1);
        $shop_id = intvalgf(5109);
        $recipient_email = "sc@interactive.dk";
        $subject = "Julegaveshop 2023 - XXX";
        $content = '<span style="font-size:13.3333px"><span style="color:#000000"><span style="font-family:Arial,Helvetica,sans-serif"><span><span><span><span><span><span><span><span><span><span><span style="background-color:#ffffff"><span><span><span>K&aelig;re XXX</span></span></span></span></span></span></span></span></span></span></span></span></span></span></span></span></span><br />
<br />
<span style="font-size:13.3333px"><span style="color:#000000"><span style="font-family:Arial,Helvetica,sans-serif"><span><span><span><span><span><span><span><span><span><span><span style="background-color:#ffffff"><span><span><span>Tak fordi I har valgt os som leverand&oslash;r af jeres julegaver i &aring;r. Vi gl&aelig;der os til at hj&aelig;lpe jer gennem processen og selvf&oslash;lgelig til at levere gaverne.</span></span></span></span></span></span></span></span></span></span></span></span></span></span></span></span></span><br />
<br />
<span style="font-size:13.3333px"><span style="color:#000000"><span style="font-family:Arial,Helvetica,sans-serif"><span><span><span><span><span><span><span><span><span><span><span style="background-color:#ffffff"><span><span><span>Jeres bestilling er nu overg&aring;et til vores salgssupportafdeling, som sikrer ops&aelig;tning af gaveshoppen samt hele forl&oslash;bet. N&aring;r vi n&aelig;rmer os tiden for jeres julegaveshop, bliver I kontaktet af en supportmedarbejder, der bliver tilknyttet som jeres personlige kontaktperson, der vil hj&aelig;lpe jer gennem hele forl&oslash;bet.</span></span></span></span></span></span></span></span></span></span></span></span></span></span></span></span></span><br />
<br />
<span style="font-size:13.3333px"><span style="color:#000000"><span style="font-family:Arial,Helvetica,sans-serif"><span><span><span><span><span><span><span><span><span><span><span style="background-color:#ffffff"><span><span><span>For at kunne f&aelig;rdigg&oslash;re jeres gaveshop, har vi brug for nedenst&aring;ende:</span></span></span></span></span></span></span></span></span></span></span></span></span></span></span></span></span><br />
&nbsp;
<ul>
	<li>En medarbejderliste (se vedh&aelig;ftede skabelon til udfyldning)</li>
	<li>Evt. velkomsttekst til forsiden af shoppen (standardtekst er vedh&aelig;ftet til inspiration)</li>
	<li>Faktureringsoplysninger pr. faktura, hvis faktura skal splittes op.</li>
</ul>
<br />
<span style="font-size:13.3333px"><span style="color:#000000"><span style="font-family:Arial,Helvetica,sans-serif"><span><span><span><span><span><span><span><span><span><span><span style="background-color:#ffffff"><span><span><span>Ovenst&aring;ende vil jeres kontaktperson informere jer om, n&aring;r vi n&aelig;rmer os. Listen og vedh&aelig;ftninger er blot til forel&oslash;big information.</span></span></span></span></span></span></span></span></span></span></span></span></span></span></span></span></span><br />
<br />
<span style="font-size:13.3333px"><span style="color:#000000"><span style="font-family:Arial,Helvetica,sans-serif"><span><span><span><span><span><span><span><span><span><span><span style="background-color:#ffffff"><span><span><span>Skulle I have sp&oslash;rgsm&aring;l inden I bliver kontaktet af jeres supportkontaktperson, er I selvf&oslash;lgelig meget velkomne til at kontakte vores salgssupportafdeling p&aring;&nbsp;<a href="mailto:support@gavefabrikken.dk" title="Send en mail til support@gavefabrikken.dk">support@gavefabrikken.dk</a>&nbsp;eller jeres salgskonsulent.</span></span></span></span></span></span></span></span></span></span></span></span></span></span></span></span></span><br />
<br />
<span style="font-size:13.3333px"><span style="color:#000000"><span style="font-family:Arial,Helvetica,sans-serif"><span><span><span><span><span><span><span><span><span><span><span style="background-color:#ffffff"><span><span><span>Vi gl&aelig;der os til samarbejdet.</span></span></span></span></span></span></span></span></span></span></span></span></span></span></span></span></span><br />
<br />
<span style="font-size:13.3333px"><span style="color:#000000"><span style="font-family:Arial,Helvetica,sans-serif"><span><span><span><span><span><span><span><span><span><span><span style="background-color:#ffffff"><span><span><span>Venlig hilsen</span></span></span></span></span></span></span></span></span></span></span></span></span></span></span></span></span><br />
<br />
<span style="font-size:13.3333px"><span style="color:#000000"><span style="font-family:Arial,Helvetica,sans-serif"><span><span><span><span><span><span><span><span><span><span><span style="background-color:#ffffff"><span><span><span>Gavefabrikken</span></span></span></span></span></span></span></span></span></span></span></span></span></span></span></span></span><br />
&nbsp;';
        */

        // Check shop
        $shop = \Shop::find($shop_id);
        $shopMetadata = \ShopMetadata::find("first",array("conditions" => array("shop_id = ?",$shop->id)));
        if($shop == null || $shop->id == 0) {
            throw new \Exception("Could not find shop id ".$shop_id);
        }
        if($shopMetadata == null || $shopMetadata->id == 0) {
            throw new \Exception("Could not find data for shop id ".$shop_id);
        }

        if($shopMetadata->mail_welcome_sent != null && $resend == 0) {
            throw new \Exception("Welcome mail already sent");
        }

        if($subject == "") {
            throw new \Exception("Subject is empty");
        }

        if($content == "") {
            throw new \Exception("Content is empty");
        }

        if($recipient_email == "") {
            throw new \Exception("Recipient e-mail is empty");
        }

        // Attachments
        $attachmentList = array(
            array("path" => "/var/www/backend/public_html/gavefabrikken_backend/units/valgshop/mails/attachments/vs_welcome_dk_forside.docx","name" => "Forside tekst.docx"),
            array("path" => "/var/www/backend/public_html/gavefabrikken_backend/units/valgshop/mails/attachments/vs_welcome_dk_template.xlsx","name" => "Skabelon til medarbejderliste.xlsx")
        );

        $sendGroup = "vs_".$shop_id."_welcome";

        // Send e-mail
        try {

            $this->sendValgshopMail(array($recipient_email),array(),array(),$subject,$content,$attachmentList,$sendGroup,$language_id);
            $shopMetadata->mail_welcome_sent = date('d-m-Y H:i:s');
            $shopMetadata->save();

            \System::connection()->commit();
            echo json_encode(array("status" => 1));

        } catch (\Exception $e) {
            throw new \Exception("Error sending e-mail: " . $e->getMessage());
        }


    }


    /*
     * FUNCTIONALITY TO SEND E-MAIL
     */

    private function sendValgshopMail($recipientList,$ccList,$bccList,$subject,$body,$attachments,$sendGroup, $language_id,$checkSend = false) {


        // Get mail server
        $mailserverMap = array(1 => 4, 4 => 4, 5 => 5);
        if(!isset($mailserverMap[$language_id])) {
            throw new \Exception("Invalid lanugage set");
        }

        $mailserver = \MailServer::find($mailserverMap[$language_id]);
        if($mailserver == null || $mailserver->id == 0) {
            throw new \Exception("Mail server not found");
        }

        // Check if send before
        $isRetry = 0;
        if($checkSend) {
            $mailCheck = \MailQueue::find('first',array("conditions" => array("send_group" => $sendGroup),"order" => "id desc"));
            if($mailCheck != null) {
                if($mailCheck->error == 1) {
                    if($mailCheck->is_smtp_error >= 5) {
                        throw new \Exception("Could not send e-mail, max retries [".$sendGroup."] (mailqueue: ".$mailCheck->id.")");
                    }
                    else {
                        $isRetry = intval($mailCheck->is_smtp_error)+1;
                    }
                }
                else {
                    throw new \Exception("E-mail already sent [".$sendGroup."] (mailqueue: ".$mailCheck->id.")");
                }
            }
        }

        // Create db object
        try {
            $mailQueue = $this->createMailQueue($recipientList[0],$subject,$body,$mailserver,$sendGroup,$isRetry);
        } catch(\Exception $e) {
            throw new \Exception("Error creating mail queue object");
        }



        // Send e-mail
        try {

            // Send mail
            $this->smtpSendMail($recipientList,$ccList,$bccList,$subject,$body,$attachments,$mailserver,$mailQueue->id);

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

            throw new \Exception("Error sending e-mail: ".$e->getMessage());

        }

    }

    private function createMailQueue($recipient,$subject,$body,$mailserver,$sendGroup,$retry)
    {

        $mq = new \MailQueue();
        $mq->mailserver_id = $mailserver->id;
        $mq->sender_name = $mailserver->sender_name;
        $mq->sender_email = $mailserver->sender_email;
        $mq->recipent_name = "";

        $mq->recipent_email = $recipient;
        if(trimgf($mq->recipent_email) == "") {
            $mq->recipent_email = "norecipient@gavefabrikken.dk";
        }

        $mq->subject = $subject;
        $mq->body = html_entity_decode($body);
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

    private function smtpSendMail($recipientList,$ccList,$bccList,$subject,$body,$attachments, $mailServer,$mailQueueID) {

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

        // Set email content
        $mail->Subject = utf8_decode((html_entity_decode($subject,ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401,"UTF-8")));
        $mail->isHTML(true);
        $mail->Body = utf8_decode((html_entity_decode($body,ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401,"UTF-8")));
        $mail->Body = str_replace(array("ÔÇô","Ã”Ã‡Ã´",utf8_encode("ÔÇô"),utf8_encode("Ã”Ã‡Ã´"),utf8_decode("ÔÇô"),utf8_decode("Ã”Ã‡Ã´")),"-",$mail->Body);
        $mail->AltBody = '';

        // Add attachment
        if(is_array($attachments) && count($attachments) > 0) {
            foreach ($attachments as $attachment) {

                $filepath = $attachment["path"];
                $filename = $attachment["name"];

                if(!file_exists($filepath)) {
                    throw new \Exception("Attachment file not found: ".$filepath);
                }

                $mail->addStringAttachment(file_get_contents($filepath), $filename, "base64");

            }
        }

        // Set recipient
        if(is_array($recipientList) && countgf($recipientList) > 0) {
            foreach ($recipientList as $recipient) {
                //echo "ADD: ".$recipient."<br>";
                $mail->addAddress($recipient);
            }
        }

        // Add cc
        if(is_array($ccList) && countgf($ccList) > 0) {
            foreach($ccList as $cc) {
                //echo "ADD CC: ".$cc."<br>";
                $mail->addCC($cc);

            }
        }

        // Add bcc
        if(is_array($bccList) && countgf($bccList) > 0) {
            foreach($bccList as $bcc) {
                $mail->addBCC($bcc);
                //echo "ADD BCC: ".$bcc."<br>";
            }
        }

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
        if(!$mail->send()) {
            throw new \Exception("Error sending e-mail: ".$mail->ErrorInfo);
        }

    }

}
