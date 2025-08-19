<?php
include ("../lib/gateway.php");
include ("../lib/job.php");
include ("../lib/user.php");
include ("../lib/queue.php");
include("../../db/db.php");


//$_POST["action"] = "sms";
//$_POST["function"] = "send";


$action = $_POST["action"];
$function = $_POST["function"];

$process = new $action;
$process->$function();

/*
     $queue = new queue();
       $queue->rette();

*/

class smsController{
    function __construct() {

    }
    public function send(){
       $gateway = new gateway();
       echo $gateway->send($_POST["txt"],$_POST["nr"],$_POST["smsTitle"]);
    }
}
class jobController{
    function __construct() {

    }
    public function create(){
        $job = new job();
        $job->create($_POST["jobNavn"],$_POST["grp"],$_POST["title"],$_POST["body"]);
        echo "Job oprettet11";
    }
}
class queueController{
    public function handleQueue(){
        $queue = new queue();
        $queue->sendHandler();
    }
}




?>