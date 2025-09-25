<?php

namespace GFUnit\cardshop\admin;


use GFBiz\Model\Cardshop\DestroyOrder;
use GFUnit\navision\syncorder\OrderSync;

class AdminOrderHelper
{

    private $co;
    
    private $cardshopSettings;
    
    private $error = null;

    public function __construct($companyorderid,$requireHash=false,$orderhash = "")
    {

        $adminUsers = array(50,86,110,138,199,153,147,124,162,51,66,190,145,217,286,285,305,304);
        $isAdmin = in_array(\router::$systemUser->id, $adminUsers);

        try {

            if(!$isAdmin) {
                throw new \Exception("You are not allowed to access this page.");
            }

            $this->co = \CompanyOrder::find($companyorderid);
            if($this->co == null) {
                throw new \Exception("Company Order not found!");
            }
            if($this->co->id <= 0) {
                throw new \Exception("Company Order not found. (".$this->co->id.")");
            }

            if($requireHash && $orderhash == "") {
                throw new \Exception("Order Hash is required!");
            }

            if(trimgf($orderhash) != "" && $this->getOrderHash() != $orderhash) {
                throw new \Exception("Invalid Order Hash: ".$orderhash." != ".$this->getOrderHash());
            }

            $this->cardshopSettings = \CardshopSettings::find_by_shop_id($this->co->shop_id);
            
        } catch (\Exception $e) {
            $this->error = $e->getMessage();
        }
        
        
        
    }

    public function setFrontMessage($message) {
        $_SESSION["csadmin_".$this->co->id] = $message;
    }
    
    public function getFrontMessage()
    {
        if(isset($_SESSION["csadmin_".$this->co->id])) {
            $message = $_SESSION["csadmin_".$this->co->id];
            unset($_SESSION["csadmin_".$this->co->id]);
            return $message;
        }
        return "";
    }
    
    public function getLanguageCode() {

        return $this->cardshopSettings->language_code;
    }

    public function getOrder() {
        return $this->co;
    }

    public function getOrderHash() {
        return md5("2jlfdj".$this->co->quantity."gsfd".$this->co->shop_id."jslrg".$this->co->id);
    }


    public function canChangeDebitorNo()
    {

        if($this->co->order_state == 10) return false;

        return true;
    }

    public function canMoveCompany()
    {
        if(in_array($this->co->order_state, array(7,8,9,10))) {
            return false;
        }
        return true;
    }

    public function canSendOrderConfirmation()
    {
        return in_array($this->co->order_state, array(4,5,6));
    }

    public function canRemoveOrder()
    {
        return in_array($this->co->order_state, array(1,2,3,4,5,6,7));
    }

    public function canReopenOrder() {

        return in_array($this->co->order_state, array(8));
    }

    public function canChangeSalesPerson()
    {
        return true;
    }

    public function canChangeDeliverySettings()
    {
        $expireDate = \ExpireDate::find_by_expire_date($this->co->expire_date);
        return $expireDate->is_delivery == 1;
    }

    public function canChangeInvoiceDate()
    {

        if(in_array($this->co->order_state, array(7,8,9,10,12))) {
            return false;
        }
        
        if($this->co->prepayment == 1 && $this->co->order_state == 4 || $this->co->order_state == 5) {
            return false;
        }
        
        return true;

    }


    /**
     * CLOSE ORDER
     */

    private $activeShopuserIDAtClose = [];
    public function closeCompanyOrder($endBuffer=true)
    {

        $this->startLogBuffering();

        echo "Closing order [".$this->co->id."] ".$this->co->order_no."<br>";

        $blockList = \BlockMessage::find_by_sql("SELECT * FROM blockmessage where company_id = ".$this->co->company_id." && release_status = 0");
        if(count($blockList) > 0 && $this->co->order_state > 3) {
            echo "Company has blocks, can not close order<br>";
            $this->error = "Virksomheden har blokeringer, kan ikke lukke ordren.";
            $this->endLogBuffering();
            return false;
        }

        // Get active users
        $shopUserList = \ShopUser::find_by_sql("SELECT * FROM shop_user where company_order_id = ".$this->co->id." && blocked = 0");
        $this->activeShopuserIDAtClose = array();
        foreach($shopUserList as $shopUser) {
            $this->activeShopuserIDAtClose[] = $shopUser->id;
        }

        // Destroy order
        if($this->co->order_state == 7) {
            echo "Order already closing, ignore destroy";
        } else {

            echo "Destroy order (update objects and set to closing)";

            try {

                DestroyOrder::destroyOrder($this->co->id,true,false);

            } catch (\Exception $e) {
                echo "Exception destroying order: ".$e->getMessage();
                $this->error = "Der opstod en fejl under lukning af ordren: "-$e->getMessage();
                $this->endLogMail("Error destroying order: ".$e->getMessage());
                return false;
            }
        }

        // Check companyorder again
        $companyorder = \CompanyOrder::find($this->co->id);

        if($companyorder->order_state == 8) {
            echo "Order is now closed before nav sync, skip navision update";
            if($endBuffer) {
                $this->endLogBuffering();
            }
            return true;
        }

        if($companyorder->order_state != 7) {
            echo "Cancelled order has unexpected state: ".$companyorder->order_state."<br>";
            $this->error = "Ordren har en uventet status: ".$companyorder->order_state." efter lukning, kan ikke gennemføre.";
            $this->endLogMail("Cancelled order has unexpected state: ".$companyorder->order_state);
            return false;
        }

        // Sync order
        echo "Synkroniser kreditering af ordre<br>";
        try {
            $syncModel = new OrderSync();
            $syncModel->syncCompanyOrder($companyorder);
        } catch (\Exception $e) {
            echo "Exception syncing destroy to nav: ".$companyorder->order_state."<br>";
            $this->error = "Der opstod en fejl under synkronisering af kreditering til NAV: ".$e->getMessage();
            $this->endLogMail("Error syncing destroy to nav: ".$e->getMessage());
            return;
        }

        if($endBuffer) {
            $this->endLogBuffering();
        }

        return true;

    }


    /**
     * REOPEN AN ORDER
     */


    public function reopenCompanyOrder($endBuffer=true)
    {

        $this->startLogBuffering();

        echo "Reopen order [".$this->co->id."] ".$this->co->order_no."<br>";

        // Revive order
        $order = \CompanyOrder::find($this->co->id);
        $orgOrderNo = $order->order_no;

        // Tjek state
        if($order->order_state != 8) {
            echo "Order is not closed state (is ".$order->order_state."), abort";
            $this->error = "Ordre ikke lukket korrekt før genåbning, kan ikke fortsætte.";
            $this->endLogMail("Order is not closed state (is ".$order->order_state.")");
            return false;
        }

        // Load active users
        echo "Aktiver blokkerede kort (".count($this->activeShopuserIDAtClose).")<br>";
        foreach($this->activeShopuserIDAtClose as $activeUserID) {
            $shopuser = \ShopUser::find($activeUserID);
            if($shopuser->company_order_id = $this->co->id) {
                $shopuser->blocked = 0;
                $shopuser->save();
            }
        }

        // Find nyt ordre nr
        $system = \system::first();
        $lastOrderNo = $order->order_no;
        $newOrderNo = \Numberseries::getNextNumber($system->company_order_nos_id);
        echo "Last order no: ".$lastOrderNo." - new order no: ".$newOrderNo."<br>";

        // Sæt nyt ordre nr og sæt state
        $order->order_no = $newOrderNo;
        $order->order_state = 1;
        $order->nav_synced = 0;
        $order->freight_state = 0;
        $order->nav_lastsync = null;
        $order->is_cancelled = 0;
        $order->save();
        echo "Updated order<br>";

        // Opdater order docs
        $orderDocsList = \NavisionOrderDoc::find("all",array("conditions" => array("company_order_id" => $order->id)));
        if(count($orderDocsList) > 0) {
            foreach($orderDocsList as $orderDoc) {
                echo "Invalidate order doc ".$orderDoc->order_no." - v ".$orderDoc->revision." [".$orderDoc->status."]<br>";
                $orderDoc->company_order_id = -1*$order->id;
                $orderDoc->save();
            }
        }

        // Opdater shipments
        $shipmentList = \Shipment::find("all",array("conditions" => array("shipment_type" => "giftcard","companyorder_id" => $order->id,"shipment_state" => 2)));
        if(count($shipmentList) > 0) {
            foreach($shipmentList as $shipment) {
                echo "Resync shipment ".$shipment->id." - ".$shipment->shipment_type."<br>";
                $shipment->shipment_state = 1;
                $shipment->shipment_sync_date = null;
                $shipment->isshipment = 0;
                $shipment->save();
            }
        }

        // Count closed shopusers
        $shopUser = \ShopUser::find_by_sql("select * from shop_user where company_order_id = ".$order->id." && (shutdown = 1 or blocked = 1)");
        echo "Order has ".countgf($shopUser)." blocked cards<br>";

        $activeShopUsers = \ShopUser::find_by_sql("select * from shop_user where company_order_id = ".$order->id." && (blocked = 0)");
        if(count($activeShopUsers) == 0) {

            echo "Ingen aktive kort, åbner alle kort på ordren igen.<br>";
            $shopUserList = \ShopUser::find("all",array('conditions' => array('company_order_id' => $order->id)));
            foreach($shopUserList as $shopUser) {
                if($shopUser->company_order_id == $order->id && $shopUser->company_order_id > 0 && $shopUser->is_demo == 0 && $shopUser->is_giftcertificate == 1) {
                    $shopUser->blocked = 0;
                    $shopUser->save();
                }
            }

        }

        // Creating block order
        echo "Block order<br> ";
        \BlockMessage::createCompanyOrderBlock($order->company_id,$order->id,"COMPANYORDER_REOPEN","Ordre ".$orgOrderNo." lukket og gen-oprettet på ".$order->order_no,false);

        if($endBuffer) {
            $this->endLogBuffering();
        }

        return true;

    }


    /**
     * MOVE COMPANY
     */

    public function moveCompanyOrder($newCompany) {

        $this->startLogBuffering();

        try {
            
            echo "Moving order [".$this->co->id."] ".$this->co->order_no." to company ".$newCompany->id." ".$newCompany->name."<br>";

    
            // Check if order is done and cancel
            if(in_array($this->co->order_state, array(9,10,12))) {
                echo "Ordren er allerede afsluttet<br>";
                throw new \Exception("Ordren er afsluttet og kan ikke flyttes.");
            }
    
            if(in_array($this->co->order_state, array(7))) {
                echo "Ordren er under annullering og kan ikke flyttes før den er annulleret.<br>";
                throw new \Exception("Ordren er under annullering og kan ikke flyttes før den er annulleret.");
            }
    
            $reopenOrder = false;


            // If should be closed first
            if(in_array($this->co->order_state, array(4,5))) {

                echo "Er oprettet i navision, krediter ordren.<br>";
                if(!$this->closeCompanyOrder(false)) {
                    throw new \Exception("Fejl under lukning af ordre: ".$this->error);
                }
                $reopenOrder = true;
            }

            // Move to new company
            $order = \CompanyOrder::find($this->co->id);
            $order->company_id = $newCompany->id;
            $order->save();
            $this->co = $order;
    
            // Update shopusers
            $shopUserList = \ShopUser::find("all",array("conditions" => array("company_order_id" => $order->id)));
            echo "Opdateret ".count($shopUserList)." kort<br>";

            foreach($shopUserList as $shopUser) {
    
                // Update shopuser
                $shopUser->company_id = $newCompany->id;
                $shopUser->save();
    
                // Find order_present_complaint for shopuser
                $complaints = \OrderPresentComplaint::find('all',array("conditions" => array("shopuser_id" => $shopUser->id)));
                foreach($complaints as $complaint) {
                    $complaint->company_id = $newCompany->id;
                    $complaint->save();
                }
    
                // Find shop_user_autoselect for shopuser
                $autoselects = \ShopUserAutoselect::find('all',array("conditions" => array("shopuser_id" => $shopUser->id)));
                foreach($autoselects as $autoselect) {
                    $autoselect->company_id = $newCompany->id;
                    $autoselect->save();
                }
    
                // Find user_attribute for shopuser
                $userAttributes = \UserAttribute::find('all',array("conditions" => array("shopuser_id" => $shopUser->id)));
                foreach($userAttributes as $userAttribute) {
                    $userAttribute->company_id = $newCompany->id;
                    $userAttribute->save();
                }

                // Find users order
                $orderList = \Order::find("all",array("conditions" => array("shopuser_id" => $shopUser->id)));
                foreach($orderList as $order) {
                    $order->company_id = $newCompany->id;
                    $order->save();

                    // Update order_attribute
                    $orderAttributes = \OrderAttribute::find('all',array("conditions" => array("order_id" => $order->id)));
                    foreach($orderAttributes as $orderAttribute) {
                        $orderAttribute->company_id = $newCompany->id;
                        $orderAttribute->save();
                    }

                    // order_present_entry
                    $orderPresentEntries = \OrderPresentEntry::find('all',array("conditions" => array("order_id" => $order->id)));
                    foreach($orderPresentEntries as $orderPresentEntry) {
                        $orderPresentEntry->company_id = $newCompany->id;
                        $orderPresentEntry->save();
                    }

                }
            }
    
            // Update block messages
            $blockMessages = \BlockMessage::find('all',array("conditions" => array("company_order_id" => $order->id)));
            echo "Opdateret ".count($blockMessages)." blokeringer<br>";
            foreach($blockMessages as $blockMessage) {
                $blockMessage->company_id = $newCompany->id;
                $blockMessage->save();
            }
    
            // Reopen companyorder
            if($reopenOrder) {
                echo "Genåbner ordre i navision<br>";
                if(!$this->reopenCompanyOrder(false)) {
                    throw new \Exception("Fejl under genåbning af ordre.");
                }
            }

        }
        catch(\Exception $e) {
            echo "Fejl i flytning: ".$e->getMessage();
            $this->error = "Fejl i flytning: ".$e->getMessage();
            $this->endLogMail("Fejl i flytning");
            $this->endLogBuffering();
            return false;
        }

        $this->endLogBuffering();
        return true;
    }


    /**
     * LOG BUFFER AND ERROR FUNCTIONS

     */


    public function hasError()
    {
        return $this->error != null;
    }

    public function getError()
    {
        return $this->error;
    }

    public function endLogMail($error) {
        $log = $this->endLogBuffering();
        $modtager = 'sc@interactive.dk';
        mailgf($modtager, "CardshopAdmin error ".$this->co->order_no,$error."\r\n<br>".$log);
    }


    private $logBufferStarted = false;

    public function startLogBuffering()
    {
        if($this->logBufferStarted) return;
        $this->logBufferStarted = true;
        ob_start();
    }

    public function endLogBuffering()
    {
        if(!$this->logBufferStarted) return "";
        $this->logBufferStarted = false;
        $log = ob_get_clean();
        ob_end_clean();
        return $log;

    }






}