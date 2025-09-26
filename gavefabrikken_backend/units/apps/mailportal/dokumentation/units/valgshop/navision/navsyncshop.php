<?php

namespace GFUnit\valgshop\navision;
use GFBiz\units\UnitController;
use GFBiz\valgshop\OrderBuilder;
use GFBiz\valgshop\ShopOrderModel;
use GFCommon\Model\Navision\NavClient;
use GFCommon\Model\Navision\VSOrderWS;


class NavSyncShop
{

    private $ShopID;

    private $NavisionDev;

    private $output;

    private $dryRun;

    private $shop;
    private $shopMetadata;
    private $shopApproval;
    private $shopVSState;
    private $shopVSVersions;

    private $openBlocks = array();

    public function __construct($ShopID,$output=false,$NavisionDev=false,$dryRun=false)
    {


        $this->ShopID = $ShopID;
        $this->NavisionDev = $NavisionDev;
        $this->output = $output;
        $this->dryRun = $dryRun;


        // Load shop and related data
        $this->shop = \Shop::find($ShopID);
        $this->shopMetadata = \ShopMetadata::find_by_shop_id($ShopID);
        $this->shopApproval = \ShopApproval::find_by_shop_id($ShopID);
        $this->shopVSState = \NavisionVSState::find_by_shop_id($ShopID);
        $this->shopVSVersions = \NavisionVSVersion::find_by_sql("SELECT * FROM navision_vs_version WHERE shop_id = ".$ShopID." AND status = 1 ORDER BY version DESC LIMIT 1");
        $this->openBlocks = \ShopBlockMessage::find_by_sql("select * from shop_block_message where shop_id = ".$ShopID." and release_status = 0");

        // Check objects
        if($this->shop == null) throw new \Exception("Shop not found");
        if($this->shopMetadata == null) throw new \Exception("ShopMetadata not found");
        if($this->shopApproval == null) throw new \Exception("ShopApproval not found");
        if($this->shopVSState == null) throw new \Exception("ShopVSState not found");

    }

    public function getLastVersion() {
        if($this->shopVSVersions == null || count($this->shopVSVersions) == 0) return null;
        return $this->shopVSVersions[0];
    }
    
    public function runSync()
    {

        $this->log("Start syncing shop: ".$this->shop->id.": ".$this->shop->name);

        // Set nav dev mode
        NavClient::setNavDevMode($this->NavisionDev);
        $this->log(" - Navision: ".($this->NavisionDev ? "DEV" : "PROD"));

        // Already has blocks
        if(countgf($this->openBlocks) > 0) {
            return $this->setShopSyncError("BlockCheck","Has open blocks",0,false);
        }

        // Check shop
        if($this->shop->is_gift_certificate != 0) {
            return $this->setShopSyncError("ShopCheck","Is gift certificate shop");
        }
        if($this->shop->is_company != 1) {
            return $this->setShopSyncError("ShopCheck","Is not company shop");
        }

        if($this->shop->is_demo != 0) {
            return $this->setShopSyncError("ShopCheck","Is marked as demo");
        }

        if($this->shop->deleted != 0) {
            return $this->setShopSyncError("ShopCheck","Is marked as deleted");
        }

        if($this->shop->blocked != 0) {
            return $this->setShopSyncError("ShopCheck","Is marked as deleted");
        }

        if(!in_array($this->shop->language_id, array(1,4,5))) {
            return $this->setShopSyncError("ShopCheck","Not a valid language on shop: ".$this->shop->language_id);
        }

        // Check approval
        if($this->shopApproval->orderdata_approval != 2) {
            return $this->setShopSyncError("ApprovalCheck","Order not approved: ".$this->language_id);
        }

        // Check vs state
        if($this->shopVSState->state == 2) {
            return $this->setShopSyncError("StateCheck","Already in error state");
        }
        if($this->shopVSState->state == 4) {
            return $this->setShopSyncError("StateCheck","Already cancelled");
        }
        if($this->shopVSState->state == 6 || $this->shopVSState->finished != null) {
            return $this->setShopSyncError("StateCheck","Aldready completed");
        }
        if($this->shopVSState->on_hold == 1) {
            return $this->setShopSyncError("StateCheck","On hold");
        }

        // Check metadata
        if(trimgf($this->shopMetadata->order_no) == "") {
            return $this->setShopSyncError("MetadataCheck","No order number");
        }
        if(trimgf($this->shopMetadata->salesperson_code) == "") {
            return $this->setShopSyncError("MetadataCheck","No salesperson");
        }

        // Check sync data
        $lastVersion = $this->getLastVersion();

        if($lastVersion != null) {
            if($this->shopVSState->sync_language_id != $this->shop->language_id) {
                return $this->setShopSyncError("SyncCheck","Language mismatch from first sync: ".$this->shopVSState->sync_language_id." - ".$this->shop->language_id);
            }

            if($this->shopVSState->sync_debitor_no != $this->shopMetadata->nav_debitor_no) {
                return $this->setShopSyncError("SyncCheck","Debitor no mismatch from first sync: ".$this->shopVSState->sync_debitor_no." - ".$this->shopMetadata->nav_debitor_no);
            }
        }

        // Create order document and check errors
        try {

            $shoporder = new ShopOrderModel($this->shop->id,$this->NavisionDev);
            $exporter = OrderBuilder::buildOrderXML($shoporder);
            $xml = $exporter->export();

            echo "<pre>";
            echo htmlentities($xml);
            echo "</pre>";

            if($exporter->countErrors() > 0) {
                return $this->setShopSyncError("OrderDocument","Errors in document (".$exporter->countErrors()."): ".implode("\n",$exporter->getErrors()));
            }

            if($exporter->countWarnings() > 0) {
                $this->log("OrderDocument: Warnings in document (".$exporter->countWarnings()."): ".implode("\n",$exporter->getWarnings()));
            }

        } catch (\Exception $e) {
            return $this->setShopSyncError("OrderDocument","Error creating order document xml: ".$e->getMessage());
        }

        // Check for same xml as last sync
        if($lastVersion != null) {
            if($xml == $lastVersion->xmldoc) {

                // Set last check
                $this->shopVSState->last_run_check = new \DateTime();
                $this->shopVSState->save();

                return $this->setShopSyncError("OrderDocument","Same xml as last version, abort",0,false,false,false);

            }
        }
        
        // Create order version
        $orderVersion = new \NavisionVSVersion();
        $orderVersion->shop_id = $this->shop->id;
        $orderVersion->order_no = $this->shopMetadata->order_no;
        $orderVersion->version = $shoporder->getNextVersion();
        $orderVersion->status = 0;
        $orderVersion->xmldoc = $xml;
        $orderVersion->error = "";
        $orderVersion->navision_call_log_id = 0;
        $orderVersion->save();

        // Check dry run
        if($this->dryRun) {

            $this->log("DRY RUN - Trip ends here");

            $orderVersion->error = "DRY RUN!";
            $orderVersion->save();

            // Commit
            \system::connection()->commit();
            \System::connection()->transaction();

            return;
        }

        $lastCallLogID = 0;

        // Send to nav
        try {

            //throw new \Exception("NOT READY FOR NAV YET!");

            echo "SEND NOW!";
            // Send the order to Navision
            $orderClient = new VSOrderWS($this->shop->language_id);
            $response = $orderClient->uploadOrderDoc($xml);
            $lastCallLogID = $orderClient->getLastCallID();

            // Update order version
            $orderVersion->status = 1;
            $orderVersion->error = "";
            $orderVersion->navision_call_log_id = $lastCallLogID;
            $orderVersion->save();

            // If last version, save language_id and customer no to vs state
            if($lastVersion == null) {
                $this->shopVSState->sync_language_id = $this->shop->language_id;
                $this->shopVSState->sync_debitor_no = $this->shopMetadata->nav_debitor_no;
            }

            // Update vs state
            if($this->shopVSState->state == 0) {
                $this->shopVSState->state = 1;
            }

            if($this->shopVSState->state == 3) {
                $this->shopVSState->state = 4;
            }

            $this->shopVSState->last_run_date = new \DateTime();
            $this->shopVSState->last_run_error = 0;
            $this->shopVSState->last_run_message = is_string($response) ? $response : json_encode($response);
            $this->shopVSState->needs_sync = 0;

            $this->shopVSState->save();


            echo "<br>RESPONSE:<br>";
            var_dump($response);

            echo "<br>RESPONSE DATE:<br>";
            var_dump($orderClient->getLastOrderResponse());

        } catch (\Exception $e) {

            echo "EXCEPTION: ".$e->getMessage()."<br>";

            // Update vs state
            if($this->shopVSState->state == 0 || $this->shopVSState->state == 1) {
                $this->shopVSState->state = 2;
            }

            $this->shopVSState->last_run_error = 1;
            $this->shopVSState->last_run_message = "Error sending order to Navision: ".$e->getMessage();
            $this->shopVSState->save();

            // Update order version
            $orderVersion->status = 2;
            $orderVersion->error = $e->getMessage();
            $orderVersion->navision_call_log_id = $lastCallLogID;
            $orderVersion->save();

            // Update supress order confirmation on shopmetadata
            $shopMetadata = \ShopMetadata::find($this->shopMetadata->id);
            $shopMetadata->suppress_orderconf = 1;
            $shopMetadata->save();

            return $this->setShopSyncError("Navision","Error sending order to Navision: ".$e->getMessage(),0,true,true,true,$e->getTraceAsString());

        }

        // Commit
        \system::connection()->commit();
        \System::connection()->transaction();

    }

    private function setShopSyncError($type,$error,$shopInvoiceID=0,$createBlock=true,$isTech=false,$sendMail=false,$debugData="") {

        $sendMail = true;

        // Log error
        $this->log("SHOP SYNC ERROR: ".$error);

        // Create block
        $bm = new \ShopBlockMessage();
        $bm->shop_id = $this->shop->id;
        $bm->shop_invoice_id = 0;

        $bm->block_type = $type;
        $bm->description = $error;
        $bm->release_status = 0;
        $bm->tech_block = $isTech ? 1 : 0;

        if($debugData != null) {
            if(is_object($debugData)) {
                $bm->debug_data = json_encode($debugData);

            }
            else if(is_array($debugData)) {
                if(count($debugData) > 0) {
                    $bm->debug_data = implode("\r\n",$debugData);
                }
            }
            else if(trimgf($debugData) != "") {
                $bm->debug_data = $debugData;
            }
        }

        if($createBlock) {
            $bm->save();
            $this->log(" - created new block: ".$bm->id);
        }

        // Send mail to sc
        if($sendMail) {
            self::mailLog("New shop block:<br>Type: ".$type."<br>Error: ".$error."<br>Shop: ".$this->shop->name."<br>Shop ID: ".$this->shop->id."<br>Shop Invoice ID: ".$shopInvoiceID."<br>Debug data: ".$debugData,"New shop block: ".$type." - ".$error." - ".$this->shop->name." - ".$this->shop->id." - ".$shopInvoiceID);
            $this->log(" - sent e-mail");
        }

        \system::connection()->commit();
        \System::connection()->transaction();

        return true;

    }

    protected function mailLog($message) {
        $modtager = 'sc@interactive.dk';
        mailgf($modtager, "ValgshopSync error: ".($this->shop->id ?? 0), $message);
    }

    private function log($string) {

        $this->logs[] = $string;
        if($this->output) {
            echo $string."<br>\r\n";
        }
    }



}

