<?php

namespace GFUnit\external\mailwebhook;
use GFBiz\units\UnitController;

class MailEventHandler
{

    private $stats = [];

    private function addStats($key,$value) {

        if(is_array($key)) {
            echo "KEY IS ARRAY";
            return;
        }

        if(is_array($value)) {
            echo "VALUE IS ARRAY";
            return;
        }

        if(($key) === null) {
            echo "KEY IS null";
            return;
        }

        if(($value) === null) {
            echo "KEY IS null";
            return;
        }

        if(($key) === "") {
            echo "KEY IS empty";
            return;
        }

        if(($value) === "") {
            echo "KEY IS empty";
            return;
        }

        if(!isset($this->stats[$key])) {
            $this->stats[$key] = array();
        }

        if(!isset($this->stats[$key][$value])) {
            $this->stats[$key][$value] = 0;
        }

        $this->stats[$key][$value]++;

    }

    public function printStats() {
        echo "<pre>".print_r($this->stats,true)."</pre>";
    }

    public function handleMailEvent($data,$mailEventID)
    {

        // Extract data from event
        try {

            $timeStamp = $data["msg"]["ts"];
            $state = $data["msg"]["state"];
            $email = $data["msg"]["email"];
            $diag = isset($data["msg"]["diag"]) ? $data["msg"]["diag"] : "";
            $boundeDesc = isset($data["msg"]["bounce_description"]) ? $data["msg"]["bounce_description"] : "";
            $mailid = $data["msg"]["metadata"]["mail_id"];

        } catch(\Exception $e) {
            return "Error extracting data from event: ".$e->getMessage()."<br>";

        }

        if($mailid <= 0) {
            return "Not a valid mailid: ".$mailid."<br>";
        }

        // Find mail
        try {

            $mail = \MailQueue::find($mailid);
            $mail->is_smtp_error = 1;
            $mail->bounce_type = substr("[".$mailEventID."] ".$state." - ".$boundeDesc,0,50);
            $mail->save();

        } catch(\Exception $e) {
            return "Could not find mailid: ".$mailid.": ".$e->getMessage()."<br>";
        }

        return true;

    }


    /*
     * CODE BELOW WAS USED TO INSPECT DATA IN WEBHOOK!
     *
            $this->addStats("event",$data["event"]);

            foreach($data as $key => $val) {
                $this->addStats("root-keys",$key);
            }

            foreach($data["msg"] as $key => $val) {
                $this->addStats("msg-keys",$key);
            }

            $this->addStats("msg-state",$data["msg"]["state"]);
            $this->addStats("msg-tags",$data["msg"]["tags"]);
            $this->addStats("msg-smtp_events",$data["msg"]["smtp_events"]);
            $this->addStats("msg-resends",$data["msg"]["resends"]);

            if(isset($data["msg"]["_version"]))
                $this->addStats("msg-_version",$data["msg"]["_version"]);

            if(isset($data["msg"]["diag"]))
                $this->addStats("msg-diag",$data["msg"]["diag"]);

            if(isset($data["msg"]["bgtools_code"]))
                $this->addStats("msg-bgtools_code",$data["msg"]["bgtools_code"]);

            $this->addStats("msg-metadata",$data["msg"]["metadata"]);
            $this->addStats("msg-template",$data["msg"]["template"]);
            //$this->addStats("msg-tags",$data["msg"][""]);
    */


    /*
            $this->addStats("event",$data["event"]);

            foreach($data as $key => $val) {
                $this->addStats("root-keys",$key);
            }

            foreach($data["msg"] as $key => $val) {
                $this->addStats("msg-keys",$key);
            }

            $this->addStats("msg-state",$data["msg"]["state"]);
            $this->addStats("msg-tags",$data["msg"]["tags"]);
            $this->addStats("msg-smtp_events",$data["msg"]["smtp_events"]);
            $this->addStats("msg-resends",$data["msg"]["resends"]);

            if(isset($data["msg"]["_version"]))
                $this->addStats("msg-_version",$data["msg"]["_version"]);

            if(isset($data["msg"]["diag"]))
                $this->addStats("msg-diag",$data["msg"]["diag"]);

            if(isset($data["msg"]["bgtools_code"]))
                $this->addStats("msg-bgtools_code",$data["msg"]["bgtools_code"]);

            $this->addStats("msg-metadata",$data["msg"]["metadata"]);
            $this->addStats("msg-template",$data["msg"]["template"]);
            //$this->addStats("msg-tags",$data["msg"][""]);
    */

}