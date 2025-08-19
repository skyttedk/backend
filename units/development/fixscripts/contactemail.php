<?php

namespace GFUnit\development\fixscripts;

class ContactEmail
{

    private $stats;
    private $companymail;

    public function run()
    {

        echo "DO NOT RUN ANYMORE!!";
        exit();

        $this->stats = array("multiplemails" => array(),"mailupdatedbefore" => array(),"mailupdatedduring" => array(),"mailupdatedafter" => array(),"mailnotupdated" => array(),"fixsql" => array(),"updatecsv" => array());

        $this->prepareLogContent();
        echo "Preprocessed ".countgf($this->companymail)." company mail updates";

        $companyList = \Company::find_by_sql("select id, pid, name, contact_email, contact_name, contact_phone, cvr, language_code from company");
        echo "Found ".countgf($companyList);

        foreach($companyList as $index => $company) {
            //if($index > 10000) break;
            $this->processCompany($company);
        }

        echo "<pre>";
        print_r($this->stats);
        echo "</pre>";

        echo "<br><br>";
        echo implode("<br>",$this->stats["updatecsv"]);
        echo "<br><br>";
        echo implode("<br>",$this->stats["fixsql"]);

        echo "<br><br>";
        echo implode("<br>",$this->stats["multiplemails"]);

    }

    private function prepareLogContent()
    {
        $this->companymail = array();
        $content = $this->getLogContent();
        $contentLines = explode("\n",$content);
        foreach($contentLines as $line) {
            $parts = explode("\t",$line);

            $companyData = json_decode($parts[0],true);
            $email = $companyData["companydata"]["contact_email"];

            $url = $parts[1];
            $urlParts = explode("/",$url);
            $companyid = intval(str_replace("%20","",$urlParts[countgf($urlParts)-1]));

            if(intval($companyid) > 0 && trimgf($email) != "" && !isset($this->companymail[$companyid])) {
                $this->companymail[$companyid] = array(trimgf(strtolower($email)),strtotime($parts[3]));
            }

        }

    }

    private function processCompany($company)
    {


        $orderList = \CompanyOrder::find_by_sql("select id, order_no, contact_email, contact_name, contact_phone, created_datetime, quantity, certificate_value from company_order where company_id = ".intval($company->id));
        //echo $company->id." - ".countgf($orderList)."<br>";
        if(count($orderList) == 0) return;

        $mismatch = false;
        $maillist = array();

        $ordertimestart = null;
        $ordertimeend= null;

        $orderinfo = array();

        foreach($orderList as $order) {
            if(trimgf(strtolower($order->contact_email)) != trimgf(strtolower($company->contact_email))) {
                $mismatch = true;
            }

            if(!in_array(trimgf(strtolower($order->contact_email)),$maillist)) {
                $maillist[] = trimgf(strtolower($order->contact_email));
            }

            $orderinfo[] = $order->order_no.": ".$order->quantity." x ".$order->certificate_value;
            $orderinfo[] = $order->contact_email;

            if($ordertimestart == null || $ordertimestart > $order->created_datetime->getTimestamp())
                $ordertimestart = $order->created_datetime->getTimestamp();

            if($ordertimeend == null || $ordertimeend < $order->created_datetime->getTimestamp())
                $ordertimeend = $order->created_datetime->getTimestamp();
        }

        if($mismatch == false) return;

        // Problem 1, mismatch in orders
        if(count($maillist) > 1) {
            //$this->stats["multiplemails"][] = "[".$company->id."] ".$company->name." - ".$company->contact_email." (".implode(", ",$maillist).")";
            $row = array($company->id,$company->language_code,$company->name,$company->cvr,$company->contact_name,$company->contact_email,$company->contact_phone);

            $this->stats["multiplemails"][] = utf8_decode(implode(",",array_merge($row,$orderinfo)));
            return;
        }

        if(isset($this->companymail[$company->id])) {

            $changetime = $this->companymail[$company->id][1];

            if($changetime < $ordertimestart)
                $this->stats["mailupdatedbefore"][] = "[".$company->id."] ".$company->name." - ".$company->contact_email." - updated to ".$this->companymail[$company->id][0]." - order ".$maillist[0];
            else if($changetime > $ordertimeend)
                $this->stats["mailupdatedafter"][] = "[".$company->id."] ".$company->name." - ".$company->contact_email." - updated to ".$this->companymail[$company->id][0]." - order ".$maillist[0];
            else
                $this->stats["mailupdatedduring"][] = "[".$company->id."] ".$company->name." - ".$company->contact_email." - updated to ".$this->companymail[$company->id][0]." - order ".$maillist[0];

        }

        else {
            $this->stats["mailnotupdated"][] = "[".$company->id."] ".$company->name." - ".$company->contact_email." - ".$maillist[0];
            $this->stats["fixsql"][] = "UPDATE company SET contact_email = '".$orderList[0]->contact_email."', contact_name = '".$orderList[0]->contact_name."', contact_phone = '".$orderList[0]->contact_phone."' WHERE id = ".$company->id;
            $this->stats["updatecsv"][] = utf8_decode(implode(",",array($company->id,$company->name,$company->contact_name,$company->contact_email,$company->contact_phone,$orderList[0]->order_no,$orderList[0]->contact_name,$orderList[0]->contact_email,$orderList[0]->contact_phone)));
        }


        //echo $company->name." [".$company->id."] - ".countgf($orderList)."<br>";



    }

    private function getLogContent() {
        $content = '';
        return $content;
    }
}