<?php

namespace GFUnit\monitor\dailyreport;
use GFBiz\units\UnitController;
use GFUnit\navision\syncprivatedelivery\ErrorCodes;

class Controller extends UnitController
{

    private $start = null;
    private $end = null;

    private $sqlStart = "";
    private $sqlEnd = "";
    private $lastTimeTick;

    public function __construct()
    {
        parent::__construct(__FILE__);
    }

    public function callmaster() {
        $masterController = new \GFUnit\privatedelivery\sedsv\Controller();
        $masterController->checkmasterdata();
    }

    public function mailyesterday()
    {

        \GFCommon\DB\CronLog::startCronJob("DailyReportSC");

        $this->start = mktime(0,0,0,date("m"),intval(date("d"))-1,date("Y"));
        $this->end = mktime(0,0,0,date("m"),intval(date("d")),date("Y"))-1;
        $report =  $this->generateContent();

        $body ="";
        $modtager = "sc@interactive.dk";
        //$modtager = "us@gavefabrikken.dk";
        $message = $report;
        $headers = "From: noreply@julegavekortet.dk <noreply@julegavekortet.dk>" . "\r\n";
        $headers .= "Reply-To:  <noreply@julegavekortet.dk>\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8";
        $result = mailgf($modtager, "GF Daglig rapport ".date("d.m.Y",$this->start), $message, $headers);

        \GFCommon\DB\CronLog::endCronJob(1,"OK",null,$report);
        \response::silentsuccess();

        $this->callmaster();

    }


    public function today()
    {
        $this->start = mktime(0,0,0,date("m"),date("d"),date("Y"));
        $this->end = mktime(0,0,0,date("m"),intval(date("d"))+1,date("Y"))-1;
        echo $this->generateContent();

        \response::silentsuccess();

    }

    public function yesterday()
    {

    }

    public function hours($hours = 1)
    {
        $this->start = time()-60*60*$hours;
        $this->end = time();
        echo $this->generateContent();
    }

    private function addTimeTick() {
        $html = "<div style='text-align: center; color: #777777; font-size: 8px;'>".(time()-$this->lastTimeTick)." sekunder</div>";
        $this->lastTimeTick = time();
        return $html;
    }

    private function generateContent()
    {

        $startTime = time();
        $this->lastTimeTick = time();

        $this->sqlStart = date("Y-m-d H:i:s",$this->start);
        $this->sqlEnd = date("Y-m-d H:i:s",$this->end);

        ob_start();


        $this->generateHeader();

        $this->generateBlocks(true);
        $this->generateBlocks(false);
        $this->generateShipments("giftcard");
        $this->generateShipments("privatedelivery");
        $this->generateShipments("earlyorder");

        $this->generatePrivateDelivery();
        $this->generateCompanyOrder();
        $this->generateCompanyOrder(1);
        $this->generateCompanyOrder(4);
        $this->generateCompanyOrder(5);
        $this->generateCompany();
        $this->generateUserOrder();
        $this->generateShopUser();
        $this->generateEmails();
        $this->generateSystemLog();
        $this->generateFooter();

        $endTime = time();

        ?><div style="padding: 20px; text-align: center; color: #A0A0A0;">Genereret på <?php echo ($endTime - $startTime); ?> sekunder</div><?php

        $content = ob_get_contents();
        ob_end_clean();

        return $content;

    }

    private function generateHeader() {

?><html>
    <head>

        <style>


            body {
                font-family: verdana; font-size: 11px; background: #F5EFE6; margin: 0px; padding: 0px;
            }

            .card {
                width: 350px; background: white;
                background: #fff;
                border-radius: 2px;
                display: inline-block;
                margin: 1rem;
                position: relative;
                padding: 10px;
                box-shadow: 0 10px 20px rgba(0,0,0,0.19), 0 6px 6px rgba(0,0,0,0.23);
                min-height: 150px;
            }

            .card h1 { font-size: 12px; margin: 0px; padding: 0px; color: #7895B2;}

            .reportdata { width: 100%;margin-top: 5px; margin-bottom: 5px; font-size: 11px;  border-collapse: collapse;  }
            .reportdata thead th { text-align: center; border: 1px solid #7895B2; padding: 3px; background: #AEBDCA; }
            .reportdata tbody td { text-align: center; border: 1px solid #7895B2; padding: 3px; padding-top: 4px; padding-bottom: 4px; font-size: 14px; }

            .reportlist { width: 100%; margin-top: 5px; margin-bottom: 5px; font-size: 11px;  border-collapse: collapse; }
            .reportlist thead th { text-align: left;  border: 1px solid #7895B2; padding: 3px; background: #AEBDCA; }
            .reportlist tbody td { text-align: left;  border: 1px solid #7895B2; padding: 3px; }

            .subcell { font-size: 11px; padding-top: 3px;}

        </style>


    </head>
    <body>

        <div style="padding: 10px; font-size: 16px; paddinb-bottom: 5px; color: #555555    ; font-weight: bold;">Gavefabrikken rapport - <?php echo date("d.m.Y H:i",$this->start); ?> - <?php echo date("d.m.Y H:i",$this->end); ?></div>

<?php

    }

    private function generateFooter() {
?></body>
        </html><?php
    }

    private function getWarnStyle($number,$warnCount,$dangerCount) {
        $style = "style=\"";

        if($number >= $dangerCount) {
            $style .= "background: red; color: white;";
        } else if($number >= $warnCount) {
            $style .= "background: yellow; color: black;";
        }

        $style .= "\"";
        return $style;
    }


    private function generateBlocks($isTech) {

        $name = $isTech ? "Tekniske blokkeringer" : "Sælger blokkeringer";

        $techValue = $isTech ? 1 : 0;

        // Find tech blocks by type
        $openBlocks = $this->getDBSingleValue("SELECT count(id) as counter, release_status FROM `blockmessage` WHERE `release_status` = 0 AND silent = 0 AND `tech_block` = ".$techValue." GROUP BY release_status");
        $newBlocks = $this->getDBSingleValue("SELECT count(id) as counter, release_status FROM `blockmessage` WHERE `created_date` >= '".$this->sqlStart."' AND created_date <= '".$this->sqlEnd."' AND `tech_block` = ".$techValue." GROUP BY release_status");
        $closedBlocks = $this->getDBSingleValue("SELECT count(id) as counter, release_status FROM `blockmessage` WHERE `release_date` >= '".$this->sqlStart."' AND release_date <= '".$this->sqlEnd."' AND `tech_block` = ".$techValue." GROUP BY release_status");

        $typeList = $this->getDBList("SELECT count(id) as counter, block_type FROM `blockmessage` WHERE release_status = 0 AND silent = 0 AND `tech_block` = ".$techValue." group by block_type ORDER BY `counter` DESC")

        ?><div class="card">

            <h1><?php echo $name; ?></h1>

            <table class="reportdata">
                <thead>
                <tr>
                    <th>Antal åbne</th>
                    <th>Antal nye</th>
                    <th>Antal løst</th>
                </tr>
                </thead>
                <tr>
                    <td <?php echo $this->getWarnStyle(intval($openBlocks),($isTech ? 1 : 50),($isTech ? 10 : 100)); ?>><?php echo intval($openBlocks); ?></td>
                    <td><?php echo intval($newBlocks); ?></td>
                    <td><?php echo intval($closedBlocks); ?></td>
                </tr>
            </table>

            <?php if(count($typeList) > 0) { ?>
            <table class="reportlist">
                <thead>
                    <tr>
                        <th>Åbne efter type</th>
                        <th>Antal</th>
                    </tr>
                </thead>
                <tbody>
                <?php

                foreach($typeList as $type) {
                    ?><tr>
                        <td><?php echo $type["block_type"]; ?></td>
                        <td><?php echo $type["counter"]; ?></td>
                    </tr><?php
                }

                ?>
                </tbody>
            </table>
            <?php } ?>
            <?php echo $this->addTimeTick(); ?>
        </div><?php


    }

    private function generateShipments($type) {

        $shipmentStatesList = $this->getDBList("SELECT count(id) as antal, shipment_type, shipment_state FROM `shipment` where shipment_type = '".$type."' group by shipment_state, shipment_type");

        $shipmentMap = array();
        foreach($shipmentStatesList as $row) {
            $shipmentMap[$row["shipment_state"]] = $row["antal"];
        }

        $shipmentStatesList = $this->getDBList("SELECT count(id) as antal, shipment_type, shipment_state FROM `shipment` where `created_date` >= '".$this->sqlStart."' AND created_date <= '".$this->sqlEnd."' AND shipment_type = '".$type."' group by shipment_state, shipment_type");

        $shipmentMapNew = array();
        foreach($shipmentStatesList as $row) {
            $shipmentMapNew[$row["shipment_state"]] = $row["antal"];
        }


        $title = "UKENDT";
        if($type == "giftcard") $title = "Shipments - Gavekort";
        else if($type == "earlyorder") $title = "Shipments - Earlyorder";
        else if($type == "privatedelivery") $title = "Shipments - Privatlevering";

        ?><div class="card">

        <h1><?php echo $title; ?></h1>

        <table class="reportdata">
            <thead>
            <tr>
                <th>Ikke godkendt (0)</th>
                <th>Klar (1)</th>
                <th>Synced (2)</th>
                <th>Error (3)</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td><?php echo $shipmentMap[0] ?? 0; ?><div class="subcell">+<?php echo $shipmentMapNew[0] ?? 0; ?> nye</div></td>
                <td><?php echo $shipmentMap[1] ?? 0; ?><div class="subcell">+<?php echo $shipmentMapNew[1] ?? 0; ?> nye</div></td>
                <td><?php echo $shipmentMap[2] ?? 0; ?><div class="subcell">+<?php echo $shipmentMapNew[2] ?? 0; ?> nye</div></td>
                <td <?php echo $this->getWarnStyle($shipmentMap[3] ?? 0,1,5); ?>><?php echo $shipmentMap[3] ?? 0; ?><div class="subcell">+<?php echo $shipmentMapNew[3] ?? 0; ?> nye</div></td>
            </tr>

            </tbody>
            <thead>
            <th>Blocked (4)</th>
            <th colspan="2">External processing (5,6,7)</th>
            <th>Countryerror</th>
            </thead>
            <tbody>
            <tr>
                <td><?php echo $shipmentMap[4] ?? 0; ?><div class="subcell">+<?php echo $shipmentMapNew[4] ?? 0; ?> nye</div></td>
                <td colspan="2"><?php echo (($shipmentMap[5] ?? 0)+($shipmentMap[6] ?? 0)+($shipmentMap[7] ?? 0)); ?><div class="subcell">+<?php echo (($shipmentMapNew[5] ?? 0)+($shipmentMapNew[6] ?? 0)+($shipmentMapNew[7] ?? 0)); ?> nye</div></td>
                <td <?php echo $this->getWarnStyle($shipmentMapNew[8] ?? 0,1,5); ?>><?php echo $shipmentMap[8] ?? 0; ?><div class="subcell">+<?php echo $shipmentMapNew[8] ?? 0; ?> nye</div></td>
            </tr>

            </tbody>

        </table>
        <?php echo $this->addTimeTick(); ?>
        </div><?php



    }

    private function generatePrivateDelivery() {

        $shopUserDeliveryStates = $this->getDBList("SELECT count(id) as antal, delivery_state FROM `shop_user` where delivery_state > 0 group by delivery_state");
        $deliveryStateMap = array();
        $deliveryErrorCodes = array();

        foreach($shopUserDeliveryStates as $stateRow) {
            if($stateRow["delivery_state"] == 1) {
                $deliveryStateMap["waiting"] = $stateRow["antal"];
            } else if($stateRow["delivery_state"] == 2) {
                $deliveryStateMap["done"] = $stateRow["antal"];
            } else if($stateRow["delivery_state"] >= 100) {
                $deliveryStateMap["noproccess"] = $stateRow["antal"];
            } else {
                if(!isset($deliveryStateMap["errors"])) $deliveryStateMap["errors"] = 0;
                $deliveryStateMap["errors"] += $stateRow["antal"];
                $deliveryErrorCodes[] = array("count" => $stateRow["antal"],"error" => ErrorCodes::getRetryText($stateRow["delivery_state"]));
            }
        }

        for($i = 0 ; $i < count($deliveryErrorCodes); $i++) {
            for($j = $i +1 ; $j < count($deliveryErrorCodes); $j++) {
                if($deliveryErrorCodes[$i]["count"] < $deliveryErrorCodes[$j]["count"]) {
                    $tmp = $deliveryErrorCodes[$i];
                    $deliveryErrorCodes[$i] = $deliveryErrorCodes[$j];
                    $deliveryErrorCodes[$j] = $tmp;
                }
            }
        }

        ?><div class="card">
            <h1>Privatleveringer</h1>
            <table class="reportdata">
                <thead>
                <tr>
                    <th>Til overførsel</th>
                    <th>Overført</th>
                    <th>Fejl</th>
                    <th>Sendes ikke</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td><?php echo $deliveryStateMap["waiting"] ?? 0; ?></td>
                    <td><?php echo $deliveryStateMap["done"] ?? 0; ?></td>
                    <td><?php echo $deliveryStateMap["errors"] ?? 0; ?></td>
                    <td><?php echo $deliveryStateMap["noprocess"] ?? 0; ?></td>
                </tr>
                </tbody>
            </table>
            <table class="reportlist">
                <thead>
                <tr>
                    <th>Fejltype</th>
                    <th>Antal</th>
                </tr>
                </thead>
                <tbody><?php

                foreach($deliveryErrorCodes as $delivery) {
                    ?><tr>
                        <td><?php echo $delivery["error"]; ?></td>
                        <td><?php echo $delivery["count"]; ?></td>
                    </tr><?php
                }

                ?>
                </tbody>
            </table>
            <?php echo $this->addTimeTick(); ?>
        </div><?php

    }

    private function generateCompanyOrder($languageCode=0) {

        $langCodes = array(1 => "Danmark",4 => "Norge", 5 => "Sverige");

        if($languageCode > 0) {
            $lockLanguageSQL = " && shop_id in (select shop_id from cardshop_settings where language_code = ".intval($languageCode).")";
        } else {
            $lockLanguageSQL = "";
        }

        $newOrders = \CompanyOrder::find_by_sql("select * from company_order where created_datetime >= '".$this->sqlStart."' && created_datetime <= '".$this->sqlEnd."' ".$lockLanguageSQL);

        $stateMap = \CompanyOrder::find_by_sql("select count(id) as counter, order_state from company_order where order_state > 0 ".$lockLanguageSQL." GROUP BY order_state");

        $stateCounter = array();
        foreach($stateMap as $stateRow) {
            $stateCounter[$stateRow->order_state] = $stateRow->counter;
        }

        $stateMapNew = \CompanyOrder::find_by_sql("select count(id) as counter, order_state from company_order where order_state > 0 && ((modified_datetime >= '".$this->sqlStart."' && modified_datetime <= '".$this->sqlEnd."') OR (nav_lastsync >= '".$this->sqlStart."' && nav_lastsync <= '".$this->sqlEnd."')) ".$lockLanguageSQL." GROUP BY order_state");

        $stateCounterNew = array();
        foreach($stateMapNew as $stateRow) {
            $stateCounterNew[$stateRow->order_state] = $stateRow->counter;
        }

        ?><div class="card">

            <h1>Gavekort ordre <?php if($languageCode > 0 && isset($langCodes[$languageCode])) echo $langCodes[$languageCode]; else echo " - alle sprog"; ?></h1>
        <div style="text-align: center; font-weight: bold;"></div>
            <table class="reportdata">
                <thead>
                <tr>
                    <th>Oprettet</th>
                    <th>0: New</th>
                    <th>1: Ready</th>
                    <th>2: Blocked</th>
                    <th>3: Released</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td><?php echo count($newOrders); ?></td>
                    <td><?php echo intvalgf($stateCounter[0] ?? 0); ?><div class="subcell">+<?php echo intvalgf($stateCounterNew[0] ?? 0); ?> nye</div></td>
                    <td><?php echo intvalgf($stateCounter[1] ?? 0); ?><div class="subcell">+<?php echo intvalgf($stateCounterNew[1] ?? 0); ?> nye</div></td>
                    <td><?php echo intvalgf($stateCounter[2] ?? 0); ?><div class="subcell">+<?php echo intvalgf($stateCounterNew[2] ?? 0); ?> nye</div></td>
                    <td><?php echo intvalgf($stateCounter[3] ?? 0); ?><div class="subcell">+<?php echo intvalgf($stateCounterNew[3] ?? 0); ?> nye</div></td>
                </tr>

                </tbody>
                <thead>
                <tr>
                    <th>4: Synced</th>
                    <th>5: Sent</th>
                    <th>6: Failed</th>
                    <th>7: To cancel</th>
                    <th>8: Cancelled</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td><?php echo intvalgf($stateCounter[4] ?? 0); ?><div class="subcell">+<?php echo intvalgf($stateCounterNew[4] ?? 0); ?> nye</div></td>
                    <td><?php echo intvalgf($stateCounter[5] ?? 0); ?><div class="subcell">+<?php echo intvalgf($stateCounterNew[5] ?? 0); ?> nye</div></td>
                    <td <?php echo $this->getWarnStyle(intvalgf($stateCounterNew[6] ?? 0),1,5); ?>><?php echo intvalgf($stateCounter[6] ?? 0); ?><div class="subcell">+<?php echo intvalgf($stateCounterNew[6] ?? 0); ?> nye</div></td>
                    <td><?php echo intvalgf($stateCounter[7] ?? 0); ?><div class="subcell">+<?php echo intvalgf($stateCounterNew[7] ?? 0); ?> nye</div></td>
                    <td><?php echo intvalgf($stateCounter[8] ?? 0); ?><div class="subcell">+<?php echo intvalgf($stateCounterNew[8] ?? 0); ?> nye</div></td>
                </tr>

                </tbody>
            </table>
            <?php echo $this->addTimeTick(); ?>
        </div><?php
    }

    private function generateCompany() {


        $stateList = \Company::find_by_sql("select count(id) as counter, company_state from company where id > 0 GROUP BY company_state");
        $stateMap = array();

        foreach($stateList as $state) {
            $stateMap[$state->company_state] = $state->counter;
        }

        $stateListNew = \Company::find_by_sql("select count(id) as counter, company_state from company where id > 0 && created_date >= '".$this->sqlStart."' && created_date <= '".$this->sqlEnd."' GROUP BY company_state");
        $stateMapNew = array();

        foreach($stateListNew as $state) {
            $stateMapNew[$state->company_state] = $state->counter;
        }

        ?><div class="card">

        <h1>Company</h1>

        <table class="reportdata">
            <thead>
            <tr>

                <th>0: Archived</th>
                <th>1: Created</th>
                <th>2: Blocked</th>
                <th>3: Released</th>
            </tr>
            </thead>
            <tbody>
            <tr>

                <td><?php echo ($stateMap[0] ?? 0); ?><div class="subcell">+<?php echo ($stateMapNew[0] ?? 0); ?> nye</div></td>
                <td><?php echo ($stateMap[1] ?? 0); ?><div class="subcell">+<?php echo ($stateMapNew[1] ?? 0); ?> nye</div></td>
                <td><?php echo ($stateMap[2] ?? 0); ?><div class="subcell">+<?php echo ($stateMapNew[2] ?? 0); ?> nye</div></td>
                <td><?php echo ($stateMap[3] ?? 0); ?><div class="subcell">+<?php echo ($stateMapNew[3] ?? 0); ?> nye</div></td>
            </tr>

            </tbody>
            <thead>
            <tr>
                <th>4: Perm block</th>
                <th>5: Synced</th>
                <th>6: Failed</th>
                <th>Total</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td><?php echo ($stateMap[4] ?? 0); ?><div class="subcell">+<?php echo ($stateMapNew[4] ?? 0); ?> nye</div></td>
                <td><?php echo ($stateMap[5] ?? 0); ?><div class="subcell">+<?php echo ($stateMapNew[5] ?? 0); ?> nye</div></td>
                <td <?php echo $this->getWarnStyle($stateMapNew[6] ?? 0,1,5); ?>><?php echo ($stateMap[6] ?? 0); ?><div class="subcell">+<?php echo ($stateMapNew[6] ?? 0); ?> nye</div></td>
                <td><?php echo (($stateMap[0] ?? 0)+($stateMap[1] ?? 0)+($stateMap[2] ?? 0)+($stateMap[3] ?? 0)+($stateMap[4] ?? 0)+($stateMap[5] ?? 0)+($stateMap[6] ?? 0)); ?><div class="subcell">+<?php echo (($stateMapNew[0] ?? 0)+($stateMapNew[1] ?? 0)+($stateMapNew[2] ?? 0)+($stateMapNew[3] ?? 0)+($stateMapNew[4] ?? 0)+($stateMapNew[5] ?? 0)+($stateMapNew[6] ?? 0)); ?> nye</div></td>
            </tr>

            </tbody>

        </table>
        <?php echo $this->addTimeTick(); ?>
        </div><?php

    }

    private function generateUserOrder() {

        $totalOrders = \Order::find_by_sql("SELECT shop_is_gift_certificate, count(id) as ordercount FROM `order` group by shop_is_gift_certificate");
        $totalOrderMap = array();

        foreach($totalOrders as $totalOrder) {
            $totalOrderMap[$totalOrder->shop_is_gift_certificate] = $totalOrder->ordercount;
        }

        $totalOrders = \Order::find_by_sql("SELECT shop_is_gift_certificate, count(id) as ordercount FROM `order` where order_timestamp >= '".$this->sqlStart."' && order_timestamp <= '".$this->sqlEnd."' group by shop_is_gift_certificate");
        $totalOrderMapNew = array();

        foreach($totalOrders as $totalOrder) {
            $totalOrderMapNew[$totalOrder->shop_is_gift_certificate] = $totalOrder->ordercount;
        }

        $totalOrderCounter = (($totalOrderMap[0] ?? 0) + ($totalOrderMap[1] ?? 0));
        $totalOrderNewCounter = (($totalOrderMapNew[0] ?? 0) + ($totalOrderMapNew[1] ?? 0));

        $overwriteCounterData = \ShopUserLog::find_by_sql("SELECT count(id) as overwritecounter FROM `shop_user_log` where type = 'Overwrite' GROUP BY type");
        $overwriteCounter = (countgf($overwriteCounterData) > 0 ? $overwriteCounterData[0]->overwritecounter : 0);

        $overwriteCounterNewData = \ShopUserLog::find_by_sql("SELECT count(id) as overwritecounter FROM `shop_user_log` where type = 'Overwrite' && created_time >= '".$this->sqlStart."' && created_time <= '".$this->sqlEnd."'  GROUP BY type");
        $overwriteCounterNew = (countgf($overwriteCounterNewData) > 0 ? $overwriteCounterNewData[0]->overwritecounter : 0);

        $loginCards = $this->getDBSingleValue("SELECT count(id) as shopusercounter FROM shop_user where is_giftcertificate = 1 && token_created >= '".$this->sqlStart."' && token_created <= '".$this->sqlEnd."'","shopusercounter");
        $createdCards = $this->getDBSingleValue("SELECT count(id) as shopusercounter FROM shop_user where is_giftcertificate = 1 && created_date >= '".$this->sqlStart."' && created_date <= '".$this->sqlEnd."'","shopusercounter");

        $loginValg = $this->getDBSingleValue("SELECT count(id) as shopusercounter FROM shop_user where is_giftcertificate = 0 && token_created >= '".$this->sqlStart."' && token_created <= '".$this->sqlEnd."'","shopusercounter");
        $createdValg = $this->getDBSingleValue("SELECT count(id) as shopusercounter FROM shop_user where is_giftcertificate = 0 && created_date >= '".$this->sqlStart."' && created_date <= '".$this->sqlEnd."'","shopusercounter");



        ?><div class="card">


        <h1>Gavevalg</h1>
        <table class="reportdata">
            <thead>
            <tr>
                <th>Antal ordre</th>
                <th>Valgshop</th>
                <th>Cardshop</th>
                <th>Gen-valg</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td><?php echo $totalOrderCounter; ?><div class="subcell">+<?php echo $totalOrderNewCounter; ?> nye</div></td>
                <td><?php echo $totalOrderMap[0] ?? 0; ?><div class="subcell">+<?php echo $totalOrderMapNew[0] ?? 0; ?> nye</div></td>
                <td><?php echo $totalOrderMap[1] ?? 0; ?><div class="subcell">+<?php echo $totalOrderMapNew[1] ?? 0; ?> nye</div></td>
                <td><?php echo $overwriteCounter; ?><div class="subcell">+<?php echo $overwriteCounterNew; ?> nye</div></td>
            </tr>

            </tbody>
        </table>
        <?php echo $this->addTimeTick(); ?>
        </div>

        <div class="card">
        <h1>Shopuser</h1>
        <table class="reportdata">
            <thead>
            <tr>
                <th>Login: card</th>
                <th>Oprettet: card</th>
                <th>Login: valg</th>
                <th>Oprettet: valg</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td><?php echo $loginCards; ?></td>
                <td><?php echo $createdCards; ?></td>
                <td><?php echo $loginValg; ?></td>
                <td><?php echo $createdValg; ?></td>
            </tr>

            </tbody>
        </table>
            <?php echo $this->addTimeTick(); ?>
        </div>
        <?php

    }

    private function generateShopUser() {

        return;


        ?><div class="card">

            <h1>Shopuser</h1>

            <table class="reportdata">
                <thead>
                <tr>
                    <th>Login: card</th>
                    <th>Oprettet: card</th>
                    <th>Login: valg</th>
                    <th>Oprettet: valg</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td><?php echo $loginCards; ?></td>
                    <td><?php echo $createdCards; ?></td>
                    <td><?php echo $loginValg; ?></td>
                    <td><?php echo $createdValg; ?></td>
                </tr>

                </tbody>



            </table>
            <?php echo $this->addTimeTick(); ?>
        </div><?php

        // TOKENS
        // OPRETTET
        // TOTAL KORT
        // TOTAL VALG
    }

    private function generateEmails() {

        $mailCountList = \MailQueue::find_by_sql("select count(id) as mailcount, sent, error from mail_queue group by sent, error");
        $mailCountMap = array();

        foreach($mailCountList as $item) {
            $mailCountMap[$item->sent."_".$item->error] = $item->mailcount;
        }


        $mailCountList = \MailQueue::find_by_sql("select count(id) as mailcount, sent, error from mail_queue where created_datetime >= '".$this->sqlStart."' && created_datetime <= '".$this->sqlEnd."' group by sent, error");
        $mailCountMapNew = array();

        foreach($mailCountList as $item) {
            $mailCountMapNew[$item->sent."_".$item->error] = $item->mailcount;
        }

        ?><div class="card">

            <h1>E-mails</h1>
            <table class="reportdata">
                <thead>
                <tr>
                    <th>Sendt</th>
                    <th>Afventer afsendelse</th>
                    <th>Fejl</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td><?php echo $mailCountMap["1_0"] ?? 0; ?><div class="subcell">+<?php echo $mailCountMapNew["1_0"] ?? 0; ?> nye</div></td>
                    <td <?php echo $this->getWarnStyle($mailCountMap["0_0"] ?? 0,100,500); ?>><?php echo $mailCountMap["0_0"] ?? 0; ?><div class="subcell">+<?php echo $mailCountMapNew["0_0"] ?? 0; ?> nye</div></td>
                    <td><?php echo $mailCountMap["0_1"] ?? 0; ?><div class="subcell">+<?php echo $mailCountMapNew["0_1"] ?? 0; ?> nye</div></td>
                </tr>

                </tbody>


            </table>
            <?php echo $this->addTimeTick(); ?>
        </div><?php

        // E-MAILS SENDT
        // E-MAILS VENTER
        // E-MAILS FEJL

    }

    private function generateSystemLog() {

        $sql = "select count(id) as counter, count(distinct ip) as ipcounter, count(distinct user_id) as usercounter, sum(IF(error_trace = '', 0,1)) as errorcounter from system_log where created_datetime >= '".$this->sqlStart."' && created_datetime <= '".$this->sqlEnd."'";
        $systemLogCounter = $this->getDBRow($sql);

        $sql = "SELECT error_message, count(id) as counter, count(distinct ip), min(action) as action, min(controller) as controller FROM `system_log` where created_datetime >= '".$this->sqlStart."' && created_datetime <= '".$this->sqlEnd."' && error_message is not null group by error_message, controller, action order by count(id) desc";
        $systemLogErrors = $this->getDBList($sql);

        $activeUsers = $this->getDBList("SELECT user_id, count(id) as counter, count(DISTINCT ip) as ipcounter FROM `system_log` where created_datetime >= '".$this->sqlStart."' && created_datetime <= '".$this->sqlEnd."' && user_id != '' group by user_id order by count(id) desc limit 10");

        $activeServices = $this->getDBList("SELECT action, controller, count(id) as counter, count(DISTINCT ip) as ipcounter FROM `system_log` where created_datetime >= '".$this->sqlStart."' && created_datetime <= '".$this->sqlEnd."' group by action, controller order by count(id) desc limit 10");

        ?>
        <div class="card">

            <h1>Mest aktive brugere (top 10)</h1>

            <table class="reportlist">
                <thead>
                <tr>
                    <th>Bruger</th>
                    <th>Antal kald</th>
                    <th>IP'er</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach($activeUsers as $user) {
                    if($user["user_id"] != "") {

                        ?><tr>
                            <td><?php echo $user["user_id"]; ?></td>
                            <td><?php echo $user["counter"]; ?></td>
                            <td><?php echo $user["ipcounter"]; ?></td>
                        </tr><?php

                    }
                } ?>
                </tbody>
            </table>
            <?php echo $this->addTimeTick(); ?>
        </div>

        <div class="card">

            <h1>Mest aktive services (top 10)</h1>
            <table class="reportlist">
                <thead>
                <tr>
                    <th>Service</th>
                    <th>Antal kald</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach($activeServices as $service) {
                    ?><tr>
                    <td><?php echo $service["controller"]."/".$service["action"]; ?></td>
                    <td><?php echo $service["counter"]; ?></td>
                    </tr><?php
                } ?>
                </tbody>
            </table>
            <?php echo $this->addTimeTick(); ?>

        </div>
        <div class="card" style="clear: both; width: 800px;">
            <h1>System log</h1>

            <table class="reportdata">
                <thead>
                <tr>
                    <th>Antal</th>
                    <th>Unikke IP'er</th>
                    <th>Brugere</th>
                    <th>Fejl</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td><?php echo intvalgf($systemLogCounter["counter"]); ?></td>
                    <td><?php echo intvalgf($systemLogCounter["ipcounter"]); ?></td>
                    <td><?php echo intvalgf($systemLogCounter["usercounter"])-1; ?></td>
                    <td><?php echo intvalgf($systemLogCounter["errorcounter"]); ?></td>
                </tr>
                </tbody>

            </table>

            <table class="reportlist">
                <thead>
                <tr>
                    <th>Controller</th>
                    <th>Action</th>
                    <th>Fejlbesked</th>
                    <th>Antal</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach($systemLogErrors as $error) {
                    if($error["action"] != "readFull_v2") {
                    ?><tr style="<?php if($this->isKnownError($error["controller"],$error["action"])) echo "color: #999999 !important;"; ?>">
                        <td><?php echo $error["controller"]; ?></td>
                        <td><?php echo $error["action"]; ?></td>
                        <td><textarea style="width: 100%; height: 60px;"><?php echo htmlspecialchars($error["error_message"]); ?></textarea></td>
                        <td><?php echo $error["counter"]; ?></td>
                    </tr><?php
                    }
                } ?>
                </tbody>
            </table>
            <?php echo $this->addTimeTick(); ?>

        </div>


        <?php

        // SYSTEMLOG BESKEDER
        // FEJLBESKED

        // Fejlbeskeder
        // Mest aktive brugere
        // Mest kaldte service

    }

    // DB FUNCTIONS
    public function getDBSingleValue($query,$key=null) {

        try {
            $result = \Dbsqli::getSql2($query);
            if($result != null && is_array($result)) {
                if(trimgf($key) == "") {
                    foreach($result[0] as $col) {
                        return $col;
                    }
                } else {
                    return $result[0][$key];
                }
            } else return null;
        } catch (\Exception $e) {
            echo "Could not perform single value query: ".$query;
            return null;
        }

    }

    public function getDBRow($query) {
        try {
            $result = \Dbsqli::getSql2($query);
            if($result != null && is_array($result)) {
                return $result[0];
            } else return array();
        } catch (\Exception $e) {
            echo "Could not perform single value query: ".$query;
            return null;
        }
    }

    public function getDBList($query) {
        try {
            $result = \Dbsqli::getSql2($query);
            return $result;
        } catch (\Exception $e) {
            echo "Could not perform single value query: ".$query;
            return null;
        }
    }


    private function isKnownError($controller,$action) {

        $knownErrors = array(
            "login-loginShopUser",
            "login-loginShopUserByToken",
            "shop-readSimple",
            "shop-getSoftClose"
        );

        return in_array($controller."-".$action,$knownErrors);

    }

}