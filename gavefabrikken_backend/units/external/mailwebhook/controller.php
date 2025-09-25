<?php

namespace GFUnit\external\mailwebhook;
use GFBiz\units\UnitController;

class Controller extends UnitController
{

    public function __construct()
    {
        parent::__construct(__FILE__);




    }


    public function cronjob($output=0) {

        \GFCommon\DB\CronLog::startCronJob("MailWebhookProcess");

        if($output == 1) {
            echo "MAIL EVENT HANDLER CRONJOB!";
        }

        $mailEventList = \MailEvent::find_by_sql("select * from mail_event where handled is null");
        $handler = new MailEventHandler();

        $okCountTotal = 0;
        $errorCountTotal = 0;

        foreach($mailEventList as $mailEvent) {

            if($output == 1) {
                echo "<br>MAIL EVENT: ".$mailEvent->id.": ";
            }

            $countEvents = 0;
            $countOK = 0;
            $countError = 0;
            $errorList = array();

            $postData = json_decode($mailEvent->post_data,true);
            if(isset($postData["mandrill_events"])) {

                $mandrillEvents = json_decode($postData["mandrill_events"],true);
                if($mandrillEvents != null && is_array($mandrillEvents) && count($mandrillEvents) > 0) {

                    foreach($mandrillEvents as $event) {

                        $countEvents++;
                        $response = $handler->handleMailEvent($event,$mailEvent->id);
                        if($response === true) {
                            $countOK++;
                        } else if(trim($response) != "") {
                            $countError++;
                            $errorList[] = $response;
                        }
                    }
                }

                //
                //echo "<pre>".print_r(json_decode($postData["mandrill_events"],true),true)."</pre>";
            }


            if($output == 1) {
                echo " ".$countEvents." events, ".$countOK." ok, ".$countError." problems";
                if(count($errorList) > 0) {
                    echo "<br><ul><li>".implode("</li><li>",$errorList)."</li></ul><br>";
                }
            }

            // Update mail event
            $mailUpdate = \MailEvent::find($mailEvent->id);
            $mailUpdate->handled = date('d-m-Y H:i:s');
            $mailUpdate->ok_count = $countOK;
            $mailUpdate->error_count = $countError;
            $mailUpdate->error_data = implode("\r\n",$errorList);
            $mailUpdate->save();

            $okCountTotal += $countOK;
            $errorCountTotal += $countError;

        }
        
        \response::silentsuccess();

        \GFCommon\DB\CronLog::endCronJob(1,$okCountTotal." ok, ".$errorCountTotal." problems");

        //$handler->printStats();


    }

}