<?php

namespace GFUnit\navision\mailservice;
use GFBiz\units\UnitController;
use GFCommon\Model\Navision\MailListWS;

class Controller extends UnitController
{

    public function __construct()
    {
        parent::__construct(__FILE__);

    }

    public function runtest()
    {
        $mailjob = new MailServiceJob(0,"5");
        //$mailjob->testSendMail();
    }

    public function rundev($language=1) {


        \GFCommon\Model\Navision\NavClient::setNavDevMode(true);
        $this->runlang($language,1,true);

    }

    public function runall($output=0)
    {


        // Do not run navision jobs in this period
        if(in_array(intval(date("H")),array(3,4))) {
            exit();
        }

        \GFCommon\DB\CronLog::startCronJob("NavMailService");

        $this->runlang(1,$output);
        $this->runlang(4,$output);
        $this->runlang(5,$output);

        \response::silentsuccess();
        \GFCommon\DB\CronLog::endCronJob($this->mailsError > 0 ? 2 : 1,$this->mailsOK." sendt, ".$this->mailsError." fejlet");


    }

    private $mailsOK = 0;
    private $mailsError = 0;

    public function runlang($langCode=0,$output=0,$isDev=false)
    {

        echo "<br>Process nav mail queue on lang: ".$langCode."<br>";

        $mailListClient = new MailListWS($langCode);
        $mailList = $mailListClient->getAllItems();
        $mailsOk = 0; $mailsError = 0;

        echo "Found ".countgf($mailList)." mails to send<br>";
        //$mailList = array_reverse($mailList);

        foreach($mailList as $index => $mail) {

            if(intval($mail->getMailNo()) > 0) {
                echo "Process mail no ".$mail->getMailNo()."<br>";

                $mailJob = new MailServiceJob($mail->getMailNo(),$langCode,$isDev);
                if($mailJob->sendMail()) {
                    $mailsOk++;
                    $this->mailsOK++;
                } else {
                    $mailsError++;
                    $this->mailsError++;
                }

                // End transaction
                \system::connection()->commit();
                \System::connection()->transaction();

                if($isDev == true) {
                    echo "DEV IS DONE!";
                    exit();
                }

                if($index > 5) {
                    echo "Max mails reached!<br>";
                    break;
                }


            } else {
                echo "DO NOT PROCESS, NO MAIL NO<br>";
            }


        }

        echo "Mails sent: ".$mailsOk.".  Mails error: ".$mailsError."";


    }

}