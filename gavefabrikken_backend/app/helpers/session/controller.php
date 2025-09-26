<?php

namespace GFApp\helpers\session;
use GFBiz\app\AppController;

class Controller extends AppController
{

    public function __construct()
    {
        parent::__construct(__FILE__);
    }

    public function check()
    {
        if(\router::$systemUser == null) {


            $email = "sc@interactive.dk";
            $subject = "Inactive session in session check";
            $message = "User not logged in..<br>\r\nSESSION<br>\r\n<pre>".print_r($_SESSION,true)."<br>Data:<br>\r\n<pre>".print_r($_POST,true)."</pre>";

            $headers = "From: noreply@julegavekortet.dk <noreply@julegavekortet.dk>" . "\r\n";
            $headers .= "Reply-To: <noreply@julegavekortet.dk>\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8";
            mailgf($email,$subject, $message, $headers);

            echo json_encode(array("loggedin" => false));

        } else {

            echo json_encode(array("loggedin" => true,"userid" => \router::$systemUser->id));

        }
    }

    public function phpinfo() {
        phpinfo();
    }



}
