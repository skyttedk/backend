<?php
 require 'PHPMailer/PHPMailerAutoload.php';
$action = $_POST["action"];
$function = $_POST["function"];

$process = new $action;
$process->$function();


class mailController{
    function __construct() {

    }
    public function send(){
    /*
       $mailus = new PHPMailer;
        // Set PHPMailer to use the sendmail transport
        $mailus->isSendmail();
        //Set who the message is to be sent from
        $mailus->setFrom('noreply@gavefabrikken.dk');
        //Set an alternative reply-to address
        //$mail->addReplyTo('noreply@gavefabrikken.dk', 'First Last');
        //Set who the message is to be sent to
        $mailus->addAddress($_POST["email"]);
        //Set the subject line
        $mailus->Subject = utf8_encode("רזו Test");
        //Read an HTML message body from an external file, convert referenced images to embedded,
        //convert HTML into a basic plain-text alternative body

        $html = utf8_encode("<div>local רוז</div>");
        $mailus->msgHTML($html);
        if (!$mailus->send()) {
            echo "Mailer Error: " . $mailus->ErrorInfo ." - ".$_POST["email"];
        } else {
            echo "Message sent! ".$_POST["email"];
        }
        */

    }
}






?>