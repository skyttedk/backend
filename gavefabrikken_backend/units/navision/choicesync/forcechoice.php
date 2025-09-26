<?php

namespace GFUnit\navision\choicesync;

use GFCommon\Model\Navision\OrderWS;

class ForceChoice
{

    public function runForceChoiceSync()
    {

        $syncMomsCode = "moms25";

        echo "RUN FORCED CHOICES!<br>";

        $lines = $this->getOrderDifData();
        echo "FOUND " . count($lines) . " LINES TO SYNC<br>";


        $processed = 0;
        foreach($lines as $orderno => $missing) {

            $orderList = \CompanyOrder::find_by_sql('SELECT * FROM company_order where nav_wait = 0 and force_choice = 0 and order_no = \''.$orderno.'\'');
            $order = $orderList[0] ?? null;

            if($order != null && $order->id > 0 && $order->order_no == $orderno) {

                $processed++;
                echo "Order ".$orderno." is missing ".$missing."<br>";

                if($missing > 0) {
                    $this->runOrderChoice($order, $missing, $syncMomsCode);
                }

                echo "<hr>";

            }





        }

        echo "<br>Processed ".$processed." orders<br>";

    }

    private function runOrderChoice($companyOrder,$quantity,$momscode)
    {

        $companyOrder = \CompanyOrder::find($companyOrder->id);

        echo "run choice on order: ".$companyOrder->order_no." (".$companyOrder->id.")<br>";

        // Load shopsettings
        $shopSettings = new \GFBiz\Model\Cardshop\CardshopSettingsLogic($companyOrder->shop_id);

        // Check language
        if($shopSettings->getSettings()->language_code != 5) {
            echo "Not swedish order";
            $companyOrder->nav_wait = 1;
            $companyOrder->save();
            return;
        }

        // Check private delivery
        $expireDateObj = $shopSettings->getWeekByExpireDate($companyOrder->expire_date->format("Y-m-d"));
        if($expireDateObj->getWeekNo() != 0) {
            echo "Not private delivery order";
            $companyOrder->nav_wait = 2;
            $companyOrder->save();

            return;
        }

        if($quantity == 0) {
            echo "No quantity to sync";
            $companyOrder->nav_wait = 22;
            $companyOrder->save();
            return;
        }

        if($quantity < 0) {
            echo "Quantity is negative";
            $companyOrder->nav_wait = 23;
            $companyOrder->save();
            return;
        }

        if($companyOrder->force_choice > 0) {
            echo "Order already has forced choices";
            $companyOrder->nav_wait = 21;
            $companyOrder->save();
            return;
        }

        /* NOT IMPORTANT - WE ARE FORCING
        // Check order state
        if($companyOrder->order_state != 10) {
            echo "Could not sync, order state is not closed ".$companyOrder->order_state;
            $companyOrder->nav_wait = 3;
            $companyOrder->save();
            return;
        }
        */

        // Find last version of order
        $lastDoc = \NavisionOrderDoc::find_by_sql("SELECT * FROM navision_order_doc WHERE status = 1 && company_order_id = ".$companyOrder->id." ORDER BY revision DESC LIMIT 1");
        if($lastDoc == null || countgf($lastDoc) == 0) {
            echo "Cant find last order version";
            $companyOrder->nav_wait = 24;
            $companyOrder->save();
            return;
        }

        // Set last version
        $orderVersion = $lastDoc[0]->revision;

        // Get last version of choice
        $lastVersion = \NavisionChoiceDoc::find('first',array("conditions" => array("company_order_id" => $companyOrder->id),"order" => "version desc"));
        if($lastVersion == null) {
            $lastVersion = 0;
            $nextVersion = 1;
        } else {
            $nextVersion = $lastVersion->version+1;
        }

        // Choicexml
        $xml = $this->getChoiceXML($companyOrder->order_no,$orderVersion,$nextVersion,$momscode,$quantity);
        echo "Choice xml<br>";
        echo htmlspecialchars($xml)."<br>";



        // Save choice document
        $choiceDoc = new \NavisionChoiceDoc();
        $choiceDoc->company_order_id = $companyOrder->id;
        $choiceDoc->order_no = $companyOrder->order_no;
        $choiceDoc->xmldoc = $xml;
        $choiceDoc->version = $nextVersion;
        $choiceDoc->cardcount = $quantity;
        $choiceDoc->status = 0;
        $choiceDoc->error = "";
        $choiceDoc->navision_call_log_id = 0;
        $choiceDoc->shopuserlist = "forcedrun";
        $choiceDoc->lastversion = 0;
        $choiceDoc->save();

        // Send companyorder
        try {

            echo "Uploading to nav<br>";
            //\GFCommon\Model\Navision\NavClient::setNavDevMode(true);
            $orderWS = new OrderWS(5);
            $orderWS->uploadChoiceDoc($xml);
            $choiceDoc->status = 1;

        }
        catch(\Exception $e) {

            echo "Error sending choices: ".htmlentities($e->getMessage())."<br>";

            $companyOrder->nav_wait = 5;
            $companyOrder->choice_exception = $e->getMessage();
            $companyOrder->save();

            $choiceDoc->status = 3;
            $choiceDoc->error = $e->getMessage();
            $choiceDoc->save();

            \System::connection()->commit();
            \System::connection()->transaction();

            return;
        }

        echo "Post processing order status";
        $companyOrder->order_state = 12;
        $companyOrder->nav_wait = 0;
        $companyOrder->force_choice = $quantity;
        $companyOrder->choice_exception = "";
        $companyOrder->save();

        // Set choice doc to lastversion
        $choiceDoc->lastversion = 1;
        $choiceDoc->save();

        echo "<br>";

        \System::connection()->commit();
        \System::connection()->transaction();

    }

    private function getChoiceXML($orderno,$orderversion,$choiceversion,$momscode,$quantity) {


        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<choices>
';

        $xml .= "    <choice>
        <orderno>".$orderno."</orderno>
        <order_version>".$orderversion."</order_version>
        <choice_version>".$choiceversion."</choice_version>
        <code>".$momscode."</code>
        <quantity>".$this->navNumberFormat($quantity)."</quantity>
        <decimal_factor>1.00</decimal_factor>
    </choice>
";

        $xml .= '</choices>';
        return $xml;

    }

    private function navNumberFormat($number)
    {
        return ($number == null ? '0.00' : number_format($number,2,".",""));
    }


    private function getOrderDifData() {

        $difData = "";

        $splitLines = explode("\n", $difData);
        $result = array();

        foreach($splitLines as $line) {
            if(trim($line) != "") {
                $splitLine = explode(" ", $line);
                $result[trim($splitLine[0])] = $splitLine[1];
            }
        }

        return $result;

    }

}
