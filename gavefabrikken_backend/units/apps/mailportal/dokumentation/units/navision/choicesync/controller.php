<?php

namespace GFUnit\navision\choicesync;
use GFBiz\units\UnitController;
use GFCommon\Model\Navision\OrderWS;
use GFUnit\navision\synccompany\CompanySync;
use GFUnit\navision\syncorder\OrderSync;
use GFUnit\navision\syncshipment\ShipmentSync;
use GFUnit\navision\syncexternalshipment\ExternalShipmentSync;

class Controller extends UnitController
{

    public function __construct()
    {
        parent::__construct(__FILE__);
    }

    public function navsync($output=0)
    {

        // Find orders to sync
        $sql = "SELECT company_order.id FROM `company_order`, shop_user where company_order.id = shop_user.company_order_id && company_order.shop_id in (select shop_id from cardshop_settings where language_code = 5) && company_order.expire_date in (select expire_date from expire_date where is_delivery = 1) && company_order.order_state = 10 && shop_user.delivery_state = 2 GROUP BY company_order.id";
        $companyorderlist = \CompanyOrder::find_by_sql($sql);

        echo "Found ".countgf($companyorderlist)." companyorders to choicesync";
        $count = 0;
        foreach($companyorderlist as $companyOrder) {


            $co = \CompanyOrder::find($companyOrder->id);
            $this->runOrderChoice($co);

            if($count >= 10) return;
            $count++;


        }

        /*
        // Find orders to close
        $sql = "SELECT company_order.id, company_order.order_no, company_order.company_name, company_order.quantity, company_order.expire_date , company_order.free_cards, count(shop_user.id) FROM company_order, shop_user WHERE shop_user.company_order_id = company_order.id && shop_user.delivery_state = 2 && company_order.shop_id in (select shop_id from cardshop_settings where language_code = 5) && company_order.expire_date = '2023-12-31' && company_order.order_state in (4,5) group by company_order.id ORDER BY company_order.expire_date DESC";
        $companyorderlist = \CompanyOrder::find_by_sql($sql);

        echo "Found ".countgf($companyorderlist)." companyorders to set to sent";
        $count = 0;
        foreach($companyorderlist as $companyOrder) {

            $co = \CompanyOrder::find($companyOrder->id);
            $co->order_state = 9;
            $co->nav_synced = 0;
            $co->save();

        }
*/
        // Commit changes
        \System::connection()->commit();
        \System::connection()->transaction();

    }

    public function test()
    {

        // Load companyorder
        //$companyOrder = \CompanyOrder::find(205);
        //$this->runOrderChoice($companyOrder);

        //$this->runTestSync(7503);

    }

    private function runTestSync($orderid)  {

        $companyOrder = \CompanyOrder::find($orderid);
        $this->runOrderChoice($companyOrder);
    }

    
    private function runOrderChoice($companyOrder)
    {

        echo "Loaded order: ".$companyOrder->order_no." (".$companyOrder->id.")<br>";

        // Load shopsettings
        $shopSettings = new \GFBiz\Model\Cardshop\CardshopSettingsLogic($companyOrder->shop_id);

        // Check language
        if($shopSettings->getSettings()->language_code != 5) {
            echo "Not swedish order";
            return;
        }

        // Check private delivery
        $expireDateObj = $shopSettings->getWeekByExpireDate($companyOrder->expire_date->format("Y-m-d"));
        if($expireDateObj->getWeekNo() != 0) {
            echo "Not private delivery order";
            return;
        }

        // Check order state
        if($companyOrder->order_state != 10) {
            echo "Could not sync, order state is not closed ".$companyOrder->order_state;
            return;
        }

        // Get last version
        $lastVersion = \NavisionChoiceDoc::find('first',array("conditions" => array("company_order_id" => $companyOrder->id),"order" => "version desc"));
        if($lastVersion == null) {
            echo "No version 0 found for order, cant sync choices";
            return;
        }

        $nextVersion = $lastVersion->version+1;

        // Choicexml
        $choiceXML = new \GFCommon\Model\Navision\ChoiceXML($companyOrder,$nextVersion);

        try {

            echo "Choice xml<br>";
            $xml = $choiceXML->getXML();
            echo htmlspecialchars($xml);

        } catch (\Exception $e) {
            echo "Error generating choice xml: ".$e->getMessage();
            return;
        }

        // Save choice document
        $choiceDoc = new \NavisionChoiceDoc();
        $choiceDoc->company_order_id = $companyOrder->id;
        $choiceDoc->order_no = $companyOrder->order_no;
        $choiceDoc->xmldoc = $xml;
        $choiceDoc->version = $nextVersion;
        $choiceDoc->cardcount = countgf($choiceXML->getCardIDList());
        $choiceDoc->status = 0;
        $choiceDoc->error = "";
        $choiceDoc->navision_call_log_id = 0;
        $choiceDoc->shopuserlist = implode(",",$choiceXML->getCardIDList());
        $choiceDoc->lastversion = 0;
        $choiceDoc->save();

        // Send companyorder
        try {

            //\GFCommon\Model\Navision\NavClient::setNavDevMode(true);
            $orderWS = new OrderWS(5);
            $orderWS->uploadChoiceDoc($xml);

            $choiceDoc->status = 1;

        }
        catch(\Exception $e) {
            echo "Error sending choices: ".$e->getMessage()."<br>";

            $choiceDoc->status = 3;
            $choiceDoc->error = $e->getMessage();
            $choiceDoc->save();

            \System::connection()->commit();
            \System::connection()->transaction();
            return;
        }

        // Close private delivery
        $blockedUsers = array();
        $shutdownUsers = array();
        $deliveredUsers = array();
        $closedUsers = array();
        $activeUsers = array();

        // Load and check / update users
        $shopUserList = \ShopUser::find("all",array('conditions' => array('company_order_id' => intval($companyOrder->id))));
        foreach($shopUserList as $shopUser) {

            if($shopUser->blocked == 1) {
                $blockedUsers[] = $shopUser;
            } else if($shopUser->shutdown == 1) {
                $shutdownUsers[] = $shopUser;
            } else if($shopUser->delivery_state == 2) {
                $deliveredUsers[] = $shopUser;
            } else if($shopUser->delivery_state == 5) {
                $closedUsers[] = $shopUser;
            } else {
                $activeUsers[] = $shopUser;
            }

            // Close users synced
            if(in_array($shopUser->id,$choiceXML->getCardIDList())) {
                $shopUser->delivery_state = 5;
                $shopUser->save();
            }

        }

        // Post process order
        echo " - Post processing ".countgf($blockedUsers)." blocked, ".countgf($shutdownUsers)." shutdown, ".countgf($deliveredUsers)." delivered, ".countgf($closedUsers)." closed, ".countgf($activeUsers)." active<br>";
        

        // Check if anyone left and close order
        if(count($activeUsers) == 0) {

            echo " -- No active left, close order for good!<br>";
            $companyOrder->order_state = 12;
            $companyOrder->save();

            // Set choice doc to lastversion
            $choiceDoc->lastversion = 1;

        }

        $choiceDoc->save();

        \System::connection()->commit();
        \System::connection()->transaction();

    }

}