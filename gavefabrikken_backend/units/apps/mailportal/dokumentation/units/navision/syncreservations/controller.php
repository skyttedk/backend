<?php

namespace GFUnit\navision\syncreservations;
use ActiveRecord\Model;
use GFBiz\units\UnitController;
use GFCommon\Model\Navision\NavDebugTools;
use GFCommon\Model\Navision\OrderWS;


class Controller extends UnitController
{

    private $output = false;

    public function __construct()
    {
        parent::__construct(__FILE__);
    }


    // Rollback bs done, specially fo no orders
    public function bscounter() {

        $model = new CounterSO();
        $model->bscounter();

    }


    public function counterjob()
    {

        $model = new CounterSO();
        $model->runPrivateDeliveryJob();
    }

    public function rename()
    {
        $rh = new RenameHelper();
        $rh->dispatch();
    }

    public function counterpd()
    {
        $model = new CounterSO();
        $model->counterpd();
    }

    public function checklist() {


        $countOK = 0;
        $countError = 0;

        $shopList = $this->getShopList();
        foreach($shopList as $shop) {
            if($shop->reservation_state > 0) {
                $model = new SyncReservationV2($shop->id,false);
                if($model->checkForErrors()) {
                    echo "<br>".$shop->id.": ".$shop->name."<br>";
                    echo "<ul><li>".implode("</li><li>",$model->getLogItems())."</li></ul>";
                    $countError++;
                    //exit();
                } else {
                    $countOK++;
                }
            }
        }

        // Output result
        if($countError > 0) {
            echo "Fandt fejl i ".$countError." shops ud af ".$countOK."<br>";
        } else {
            echo "Fandt ingen fejl i ".$countOK." shops<br>";
        }

    }

    /*
     * VIS VARER DER IKKE ER UDLIGNET ENDNU
     */
    public function notcountered() {
        $model = new CounterSO();
        $model->notcountered();
    }

    /**
     * VIS VARER DER ER UDLIGNET FRA EN SO
     */
    public function iscountered() {
        $model = new CounterSO();
        $model->iscountered();
    }

    /**
     * VISER DE FÆRDIE OG UDLIGNEDE RESERVATIONER
     * Her udlignes der!!!
     */
    public function counterdone() {
        $model = new CounterSO();
        $model->rundone();
    }

    /**
     * INDLÆS SPECIFIKKE SO NR TIL NavisionReservationDone
     */
    public function counterso() {
        $model = new CounterSO();
        $model->importSO();
    }


    /**
     * INDLÆS CARDSHOP AFSLUTNINGER
     */
    public function countercs() {
        $model = new CounterSO();
        $model->importCS();
    }



    /*
     * When problems are found with counters, set revert = 1 on navision_reservation_done and run this script to roll them back
     */
    public function revertcounter()
    {

        $model = new CounterSO();
        $model->revertCounters();

    }


    public function syncjobrun() {


      
        \GFCommon\DB\CronLog::startCronJob("NavSyncReservation");
        
        // Fetch data
        $shopNeedsSync = $this->getShopsToSync(true);
        $shopMetadata = $this->getReservationMetadata();
        $defaultMeta = json_decode(json_encode(array("last_update" => null, "last_sync" => null, "not_synced_count" => "-", "present_count" => 0,"total_quantity" => 0)));
        $shopList = $this->getShopList();

        $syncCount = 0;

        // Mailhome content
        $mailHomeContent = array();

        // Syn all at 1 am
        $runAll = intval(date("H")) == 2;
        //$runAll = true;

        echo "Found ".count($shopList)." shops<br>";
        foreach($shopList as $shop) {

            //if($shop->reservation_state > 0 && $shop->is_gift_certificate == 0 && $shop->reservation_code != "HEDEHUSENE") {
            if($shop->reservation_state > 0 && $shop->is_gift_certificate == 0 && $shop->reservation_code == "none") {

                echo "ON HOLD: ".$shop->id." - ".$shop->name." - ".$shop->reservation_code."<br>";
                mailgf("sc@interactive.dk", "Skift location på shop: ".$shop->id." - ".$shop->name." - ".$shop->reservation_code."", "Skift location på shop: ".$shop->id." - ".$shop->name." - ".$shop->reservation_code."");
            }
            else {


                $meta = $shopMetadata[$shop->id] ?? $defaultMeta;

                $syncNow = false;

                // Get data to divide into groups
                $needsSync = isset($shopNeedsSync[$shop->id]);
                $hasProblems = ($meta->present_count == 0 || $meta->total_quantity == 0);
                $isNew = $meta->last_sync == null;
                $isActive = $shop->reservation_state == 1;

                if($isActive) {
                    if($needsSync) {
                        if($isNew) {
                            if($hasProblems) {
                                $mailHomeContent[] = "Shop: ".$shop->id." - ".$shop->name." - har problemer med at synkronisere. Er aktiv, skal synrkoniseres, er ny men har problemer!";
                            } else {
                                $syncNow = true;
                            }
                        } else {
                            $syncNow = true;
                        }
                    } else {
                        if($isNew) {
                            if($hasProblems) {
                                $mailHomeContent[] = "Shop: ".$shop->id." - ".$shop->name." - har problemer med at synkronisere. Er aktiv, skal synrkoniseres, er ny men har problemer!";
                            }
                        } else {
                            // Do nothing
                        }
                    }
                } else if(!$isNew) {
                    //$mailHomeContent[] = "Shop: ".$shop->id." - ".$shop->name." - er ikke aktiv længere, tjek om den skal nulstilles!";
                }

                if($isActive && $runAll) {
                    $syncNow = true;
                }



                // If sync and has not synced more than 10 shops
                if($syncNow) {

                    $syncCount++;
                    echo "Synkroniser shop: ".$shop->id." - ".$shop->name."<br>";
                    ob_start();
                    $model = new SyncReservationV2($shop->id,true);
                    $syncSuccess = $model->runSync();
                    ob_end_clean();

                    if(!$syncSuccess) {
                        $mailHomeContent[] = "Shop: ".$shop->id." - ".$shop->name." - fejlede i synkroniseringen: <ul><li>".implode("</li><li>",$model->getLogItems())."</li></ul>";
                    }

                }

            }


        }

        // Run all, then make a board mismatch check
        if($runAll == true) {
            $shopBoardMismatch = \Shopboard::find_by_sql("SELECT shop_board.pakkeri, shop.reservation_code, group_concat(shop.id) as shoplist, GROUP_CONCAT(shop.name) as shopnames FROM shop_board, shop where fk_shop is not null && fk_shop > 0 && fk_shop = shop.id && pakkeri != '' && pakkeri != reservation_code group by pakkeri, reservation_code;");
            if(count($shopBoardMismatch) > 0) {
                foreach($shopBoardMismatch as $boardMismatch) {
                    $mailHomeContent[] = "Location shopboard mismatch:<br>Shopboard pakkeri: ".$boardMismatch->pakkeri."<br>Shopboard location: ".$boardMismatch->reservation_code."<br>Shop ids: ".$boardMismatch->shoplist."<br>Shop names: ".$boardMismatch->shopnames."<br>Query: select * from shop_board where fk_shop in (".$boardMismatch->shoplist.")<br>Query: select * from shop where id in (".$boardMismatch->shoplist.")<br>";
                }
            }
        }

        // On problems output and send mail, only at 2 am
        if(count($mailHomeContent) > 0 && (intval(date("H")) == 2 || $runAll)) {
            echo "Mail home content: ".implode("<br>",$mailHomeContent)."<br>";
            mailgf("sc@interactive.dk", "CRON Reservationskørsel - beskeder", implode("<br>",$mailHomeContent));
        }

        \GFCommon\DB\CronLog::endCronJob(count($mailHomeContent) == 0 ? 1 : 3,count($mailHomeContent)."problemer i synkronisering");

    }


    public function presentcheck() {

        $donationModels = array();

        // Sql
        $sql = "SELECT shop.id as shop_id, shop.name, present.id as present_id, present_model.model_id, present_model.model_present_no, present_model.model_name, present_reservation.quantity, present_reservation.quantity_done, 
       (
        SELECT no 
        FROM navision_item 
        WHERE TRIM(LOWER(present_model.model_present_no)) = TRIM(LOWER(no)) 
        AND deleted IS NULL 
        LIMIT 1
    ) AS no
        FROM shop JOIN present ON shop.id = present.shop_id JOIN present_model ON present.id = present_model.present_id
        LEFT JOIN present_reservation ON present_model.model_id = present_reservation.model_id
        WHERE shop.reservation_state = 1 AND present_model.language_id = 1 AND (present_reservation.model_id IS NULL OR (present_reservation.quantity = 0 AND present_reservation.skip_navision = 0))
        ORDER BY shop.id ASC, present_model.model_present_no";

        // Load data
        $presentCheck = \PresentModel::find_by_sql($sql);

        $shopList = array();
        $shopMap = array();

        foreach($presentCheck as $check) {
            if($check->quantity !== -1) {
                if(!isset($shopMap[$check->shop_id])) {
                    $shopMap[$check->shop_id] = array();
                    $shopList[] = array($check->shop_id,$check->name);
                }
                $shopMap[$check->shop_id][] = $check;
            }
        }

        ?><html><style>
        body { font-family: verdana; font-size: 10px;}
        table { font-family: verdana; font-size: 10px;  border-collapse: collapse; }
        td,th { padding: 5px; padding-right: 15px; border-bottom: 1px solid #A0A0A0; text-align: right; }
        td:first-child, th:first-child { text-align: left;}
        th { background: #F0F0F0; font-weight: bold;}
    </style>


    <table style="width: 100%;">
        <tr>
            <td style="width: 600px" valign="top">
                <div style="padding: 10px; background: #C0C0C0; font-size: 20px; font-weight: bold;">Shops med problemer i varenr eller reservation</div>
                <table style="width: 100%;"><?php

                    foreach($shopList as $shop) {

                            $shopId = $shop[0];
                            $shopName = $shop[1];

                        ?><tr>
                            <th colspan="6" style="padding: 20px; font-size: 18px; font-weight: bold;"><?php echo $shopId." - ".$shopName; ?></th><?php

                        ?><tr style="font-size: 1.2em; font-weight: bold;">
                            <td>Gave navn</td>
                            <td>Varenr</td>
                            <td>Reservation</td>
                            <td>Udført</td>
                            <td>Varenr tjek</td>
                            <td>Gave id</td>
                            <td>Model id</td>
                        </tr><?php

                        foreach($shopMap[$shopId] as $check) {

                            $varenrok = trimgf($check->no) != "";

                            $isDonation = false;
                            if(strstr(strtolower($check->model_name), "donation")) {
                                $isDonation = true;
                            } else if(strstr(strtolower($check->model_present_no),"donation")) {
                                $isDonation = true;
                            }

                            $hide = false;
                            if($isDonation && $check->quantity === null) {
                                $hide = true;
                            }

                            if(!$hide) {
                                ?><tr>
                                    <td><?php echo $check->model_name; ?></td>
                                    <td><?php echo $check->model_present_no; ?></td>
                                    <td style="<?php if(intvalgf($check->quantity) == 0) echo "background: yellow;"; ?>"><?php echo intvalgf($check->quantity); ?></td>
                                    <td style=""><?php echo intvalgf($check->quantity_done); ?></td>
                                    <td style="<?php if(!$varenrok) echo "background: yellow;"; ?>"><?php echo $varenrok ? "OK" : "MANGLER"; ?></td>
                                    <td><?php echo $check->present_id; ?></td>
                                    <td><?php echo $check->model_id; ?></td>
                                </tr><?php

                                if($isDonation) {
                                    $donationModels[] = $check->model_id;
                                }

                            }



                        }
                    }


                ?></table>
            </td>

        </tr>
    </table>

         <div style="padding: 20px;">Donations model id: <?php echo implode(",",$donationModels); ?></div>

        </html><?php



    }

    public function reservationlist() {

        $shopNeedsSync = $this->getShopsToSync(true);
        $shopMetadata = $this->getReservationMetadata();


        $notSyncedReady = array();
        $notSyncedError = array();
        $syncedNoChanges = array();
        $syncedHasChanges = array();
        $syncedDeactivated = array();

        $defaultMeta = json_decode(json_encode(array("last_update" => null, "last_sync" => null, "not_synced_count" => "-", "present_count" => 0,"total_quantity" => 0)));
        $shopList = $this->getShopList();
        foreach($shopList as $shop) {

            $meta = $shopMetadata[$shop->id] ?? $defaultMeta;

            // Get data to divide into groups
            $needsSync = isset($shopNeedsSync[$shop->id]);
            $hasProblems = ($meta->present_count == 0 || $meta->total_quantity == 0);
            $isNew = $meta->last_sync == null;
            $isActive = $shop->reservation_state == 1;

            if($isActive) {
                if($needsSync) {
                    if($isNew) {
                        if($hasProblems) {
                            $notSyncedError[] = $shop;
                        } else {
                            $notSyncedReady[] = $shop;
                        }
                    } else {
                        $syncedHasChanges[] = $shop;
                    }
                } else {
                    if($isNew) {
                        if($hasProblems) {
                            $notSyncedError[] = $shop;
                        }
                    } else {
                        $syncedNoChanges[] = $shop;
                    }
                }
            } else if(!$isNew) {
                $syncedDeactivated[] = $shop;
            }

        }


        ?><html><style>
        body { font-family: verdana; font-size: 10px;}
        table { font-family: verdana; font-size: 10px;  border-collapse: collapse; }

        td,th { padding: 5px; padding-right: 15px; border-bottom: 1px solid #A0A0A0; text-align: right; }
        td:first-child, th:first-child { text-align: left;}
        th { background: #F0F0F0; font-weight: bold;}
    </style>


    <table style="width: 100%;">
        <tr>
            <td style="width: 33%" valign="top">

                <div style="padding: 10px; background: #C0C0C0; font-size: 20px; font-weight: bold;">
                    Navision reservations synkroniserings status
                </div>
                <table style="width: 100%;"><?php

                    $this->outputGroup("Nye shops, ikke synkroniseret - med problemer", $notSyncedError,$shopMetadata);
                    $this->outputGroup("Nye shops, ikke synkroniseret - klar", $notSyncedReady,$shopMetadata);
                    $this->outputGroup("Tidligere synkroniseret, nu deaktiveret", $syncedDeactivated,$shopMetadata);
                    $this->outputGroup("Er synkroniseret - har ændringer", $syncedHasChanges,$shopMetadata);
                    $this->outputGroup("Er synkroniseret - ingen ændringer", $syncedNoChanges,$shopMetadata);


                    ?></table>

            </td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>

    </table>



        </html><?php

    }

    private function outputGroup($title,$list,$shopMetadata) {

        if(!is_array($list) || count($list) == 0) {
            return;
        }

        ?> <thead>
        <tr>
            <th colspan="10" style="padding: 20px; font-size: 18px; font-weight: bold;"><div style="float: right;">antal: <?php echo count($list); ?></div><?php echo $title; ?></th>

        </tr>
        <tr>
            <th>id</th>
            <th>navn</th>
            <th>aktiv</th>
            <th>land</th>
            <th>lokation</th>
            <th>opdateret</th>
            <th>sidste sync</th>
            <th>gaver</th>
            <th>total</th>
            <th>antal i sidste sync</th>

        </tr>
        </thead>
        <tbody>
        <?php

        $defaultMeta = json_decode(json_encode(array("last_update" => null, "last_sync" => null, "not_synced_count" => "-", "present_count" => 0,"total_quantity" => 0)));
        foreach($list as $shop) {

            $meta = $shopMetadata[$shop->id] ?? $defaultMeta;

            ?><tr style="<?php if(isset($shopNeedsSync[$shop->id])) echo "background: yellow;"; ?>">
            <td><?php echo $shop->id; ?></td>
            <td><?php echo $shop->name; ?></td>
            <td><?php echo $shop->reservation_state == 1 ? "åben" : "lukket"; ?></td>
            <td><?php echo $shop->reservation_language; ?></td>
            <td><?php echo $shop->reservation_code; ?></td>
            <td><?php echo $meta->last_update == null ? "ingen dato" : $meta->last_update; ?></td>
            <td><?php echo $meta->last_sync == null ? "ingen dato" : $meta->last_sync; ?></td>

            <td><?php echo $meta->present_count; ?></td>
            <td><?php echo $meta->total_quantity; ?></td>
            <td><?php echo $meta->total_lastsync ?? 0; ?></td>

            </tr><?php
        }

        ?>
        </tbody><?php

    }

    public function checkbalance() {
        $model = new BalanceCheck();
        $model->runCheck();
    }

    public function balancesync() {
        $model = new BalanceSync();
        $model->runSync();
    }

    public function sync($shopid) {

        $model = new SyncReservationV2($shopid,true);
        $model->runSync();

        //$this->dashboard();

    }

    public function viewshop($shopid=0) {

        $model = new SyncReservationV2($shopid,true);
        $model->viewShop();

    }

    public function dashboard() {


        $shopNeedsSync = $this->getShopsToSync(true);
        $shopMetadata = $this->getReservationMetadata();

        ?><html><style>
            body { font-family: verdana; font-size: 10px;}
            table { font-family: verdana; font-size: 10px;  border-collapse: collapse; }

            td,th { padding: 5px; padding-right: 15px; border-bottom: 1px solid #A0A0A0; text-align: right; }
            td:first-child, th:first-child { text-align: left;}
            th { background: #F0F0F0; font-weight: bold;}
        </style>


        <table style="width: 100%;">
            <tr>
                <td style="width: 33%" valign="top">

                    <div style="padding: 10px; background: #C0C0C0; font-size: 20px; font-weight: bold;">
                        Shops
                    </div>
                    <table style="width: 100%;">
                        <thead>
                        <tr>
                            <th>id</th>
                            <th>navn</th>
                            <th>aktiv</th>
                            <th>land</th>
                            <th>lokation</th>
                            <th>2. land</th>
                            <th>2. land lokation</th>
                            <th>opdateret</th>
                            <th>sidste sync</th>
                            <th>mangler sync</th>
                            <th>gaver</th>
                            <th>total</th>
                            <th>syncet</th>
                            <th>vis</th>
                            <th>sync</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php

                        $defaultMeta = json_decode(json_encode(array("last_update" => null, "last_sync" => null, "not_synced_count" => "-", "present_count" => 0,"total_quantity" => 0)));
                        $shopList = $this->getShopList();
                        foreach($shopList as $shop) {

                            $meta = $shopMetadata[$shop->id] ?? $defaultMeta;

                            ?><tr style="<?php if(isset($shopNeedsSync[$shop->id]) && $shop->reservation_state) echo "background: yellow;"; ?>">
                                <td><?php echo $shop->id; ?></td>
                                <td><?php echo $shop->name; ?></td>
                                <td><?php echo $shop->reservation_state == 1 ? "åben" : "lukket"; ?></td>
                                <td><?php echo $shop->reservation_language; ?></td>
                                <td><?php echo $shop->reservation_code; ?></td>
                                <td><?php echo $shop->reservation_foreign_language; ?></td>
                                <td><?php echo $shop->reservation_foreign_code; ?></td>
                                <td><?php echo $meta->last_update == null ? "ingen dato" : $meta->last_update; ?></td>
                                <td><?php echo $meta->last_sync == null ? "ingen dato" : $meta->last_sync; ?></td>
                                <td><?php echo $meta->not_synced_count; ?></td>
                                <td><?php echo $meta->present_count; ?></td>
                                <td><?php echo $meta->total_quantity; ?></td>
                                <td><?php echo $meta->total_lastsync ?? 0; ?></td>
                                <td><a href="?rt=unit/navision/syncreservations/viewshop/<?php echo $shop->id; ?>">vis</a></td>
                                <td><a href="?rt=unit/navision/syncreservations/sync/<?php echo $shop->id; ?>">sync</a></td>
                            </tr><?php
                        }

                        ?>
                        </tbody>
                    </table>

                </td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>

        </table>



        </html><?php

    }

    public function getShopList() {

        $sql = "SELECT * FROM shop where reservation_state = 1 or reservation_language > 0 or reservation_code != '' or reservation_foreign_language > 0 or reservation_foreign_code != '' && id != 6063";
        return \Shop::find_by_sql($sql);

    }

    public function getReservationMetadata() {
        $sql = "select shop_id, max(update_time) as last_update, max(sync_time) as last_sync, count(id) as present_count, sum(IF(quantity=-1,0,quantity-quantity_done)) as total_quantity, sum(sync_quantity) as total_lastsync, sum(IF(IF(quantity=-1,0,quantity-quantity_done)=sync_quantity,0,1)) as not_synced_count from present_reservation group by shop_id";
        $list = \PresentReservation::find_by_sql($sql);
        $shopMap = array();
        foreach($list as $meta) {
            $shopMap[$meta->shop_id] = $meta;
        }
        return $shopMap;
    }

    public function getShopsToSync($returnMap=false)
    {
        $sql = "SELECT * from shop where id != 6063 && (reservation_state = 1 && id in (select shop_id from present_reservation where sync_quantity is null or IF(quantity=-1,0,quantity-quantity_done) != sync_quantity)) or ((reservation_state = 0 && id in (select shop_id from present_reservation where sync_quantity is not null)))";
        $syncShopList = \Shop::find_by_sql($sql);
        if(!$returnMap) {
            return $syncShopList;
        }
        $map = array();
        foreach($syncShopList as $shop) {
            $map[$shop->id] = $shop;
        }
        return $map;
    }

    

}