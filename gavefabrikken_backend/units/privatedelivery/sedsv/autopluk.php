<?php

namespace GFUnit\privatedelivery\sedsv;


class Autopluk
{

    private $helper;

    public function __construct()
    {

        $this->helper = new Helpers();

    }

    public function dispatch()
    {

        // Load data
        $this->loadData();

        $this->templateTop();

        // Show current varebeholdning
        $this->printBeforeBeholdning();


        // Load shipments, oldest first
        $this->processShipments();

        // Create pluk
        $this->createPluk();

        // Show beholdning after
        $this->printAfterBeholdning();

        $this->templateBottom();

        $runList = isset($_POST["action"]) && trim($_POST["action"]) == "runlist";
        if($runList) {
            \System::connection()->commit();
        }

    }

    private function processShipments() {


        $shipmentList = $this->helper->getDSVShipmentsReadyOrderSorted();

        ?><div class="sectionhead" style="background: #FFD4B2; padding: 10px; font-size: 12px; font-weight: bold; color: #444444; font-size: 22px;">
            Behandler shipments <?php echo count($shipmentList); ?>
        </div><table style="width: 100%;" class="dsvtable" cellpadding="0" cellspacing="0">

            <thead>
                <tr>
                    <th>Ordrenr</th>
                    <th>Dato</th>
                    <th>Brugernavn</th>
                    <th>Shipment oprettet</th>
                    <th>Navn</th>
                    <th>Varenr</th>
                    <th>Pluk</th>
                    <th>Status</th>
                    <th>DSV Lager før</th>
                    <th>DSV Lager efter</th>
                    <th>Pakkes</th>
                    <th>Total plukket</th>
                </tr>
            </thead>
            <tbody><?php

                foreach($shipmentList as $shipment) {
                   $this->processSingleShipment($shipment);
                }

                if(count($this->currentFile) > 0) {
                    $this->fileList[] = $this->currentFile;
                }

            ?></tbody>
        </table>
        <div style="padding: 10px;">
            <b>Status</b><br>
            Shipments pakket: <?php echo $this->shipmentsSent; ?><br>
            Shipments ikke pakket: <?php echo $this->shipmentsNotSent; ?><br>
        </div>
        <?php


    }

    private function createPluk() {

        if(count($this->fileList) == 0) {
            ?><div class="sectionhead" style="background: #FFD4B2; padding: 10px; font-size: 12px; font-weight: bold; color: #444444; font-size: 22px;">
                INGEN PLUKFILER SKAL DANNES!
            </div><?php
            return;
        }

        $baseTime = time();

        $runList = isset($_POST["action"]) && trim($_POST["action"]) == "runlist";

        foreach($this->fileList as $index => $shipments) {

            $fileTime = $baseTime+10*$index;

            ?><div class="sectionhead" style="background: #FFD4B2; padding: 10px; font-size: 12px; font-weight: bold; color: #444444; font-size: 22px;">
                Danner plukfil <?php echo $index+1; ?>: <?php echo date("d-m-Y H:i:s",$fileTime); ?> med <?php count($shipments); ?> gavevalg - <?php echo $runList ? "KØR LISTE" : "DRY RUN"; ?>
            </div>
            <table style="width: 100%;" class="dsvtable" cellpadding="0" cellspacing="0">
            <thead>
            <tr>
                <th>Ordrenr</th>
                <th>Dato</th>
                <th>Brugernavn</th>
                <th>Shipment oprettet</th>
                <th>Navn</th>
                <th>Varenr</th>
                <th>Status</th>
                </tr>
            </thead><tbody><?php

            foreach($shipments as $ship) {

                $shipMessage = "Unhandled";

                // Load data
                $shipment = \Shipment::find($ship->id);
                $order = \Order::find($ship->to_certificate_no);
                $shopuser = \ShopUser::find($order->shopuser_id);

                if($shipment->from_certificate_no != $shopuser->username) {
                    $shipMessage = "ERROR - Username not correct!";
                } else if($shopuser->delivery_state != 1) {
                    $shipMessage = "ERROR - SHOPUSER DELIVERYSTATE IS ".$shopuser->delivery_state;
                } else if($shipment->shipment_state != 1) {
                    $shipMessage = "ERROR - SHIPMENT STATE IS ".$shipment->shipment_state;
                } else {

                    $shipMessage = "OK";
                    $shopuser->delivery_state = 2;
                    $shipment->shipment_state = 2;
                    $shipment->shipment_sync_date = date('d-m-Y H:i:s',$fileTime);

                    if($runList) {
                        $shopuser->save();
                        $shipment->save();
                    }

                }


                ?><tr><td><?php echo $ship->order_no; ?></td>
                <td><?php echo $ship->order_timestamp; ?></td>
                <td><?php echo $shipment->from_certificate_no; ?></td>
                <td><?php echo $shipment->created_date->format('Y-m-d H:i:s'); ?></td>
                <td><?php echo $shipment->shipto_name; ?></td>
                <td><?php echo $shipment->itemno; ?></td>
                <td><?php echo $shipMessage; ?></td>
                </tr><?php
            }

            ?></tbody></table><?php

        }





    }


    private function processSingleShipment($shipment) {

        // Index: varenr, quantity, status, stockbefore, stockafter, waitingnow, totalfile
        $itemList = array();

        // Find item data
        $itemData = $this->vareData[$this->normalizeVarene($shipment->itemno)];
        $packShipment = true;

        // Find varelines in shipment
        if($itemData["issampak"]) {
            foreach($itemData["subitems"] as $subitem) {
                $subItemData = $this->vareData[$subitem["varenr"]];
                $itemList[] = array("varenr" => $subitem["varenr"],"quantity" => $subitem["quantity"], "status" => "unprocessed","stockbefore" => $subItemData["dsvcounter"],"stockafter" => $subItemData["dsvcounter"],"waitingnow" => $subItemData["waiting"],"newwaiting" => $subItemData["waiting"]);
            }
        } else {
            $itemList[] = array("varenr" => $itemData["varenr"],"quantity" => 1, "status" => "unprocessed","stockbefore" => $itemData["dsvcounter"],"stockafter" => $itemData["dsvcounter"],"waitingnow" => $itemData["waiting"],"newwaiting" => $itemData["waiting"]);
        }

        // Process items
        foreach($itemList as $index => $item) {
            if($item["stockbefore"] > 0 && $item["stockbefore"] >= $item["quantity"]) {
                $itemList[$index]["status"] = "Kan pakkes";
            } else {
                $itemList[$index]["status"] = "Mangler";
                $packShipment = false;
            }
        }

        // If can pack, set
        if($packShipment) {

            foreach($itemList as $index => $item) {
                $itemList[$index]["stockafter"] = $item["stockbefore"] - $item["quantity"];
                $itemList[$index]["newwaiting"] = $item["waitingnow"] - $item["quantity"];
                $this->vareData[$item["varenr"]]["dsvcounter"] -= $item["quantity"];
                $this->vareData[$item["varenr"]]["newwaiting"] -= $item["quantity"];
                $this->vareData[$item["varenr"]]["packok"] += $item["quantity"];
            }

            $this->currentFile[] = $shipment;
            $this->currentCount += count($itemList);
            $this->shipmentsSent++;

        } else {
            foreach($itemList as $item) {
                $this->vareData[$item["varenr"]]["nopack"] += $item["quantity"];
            }
            $this->shipmentsNotSent++;
        }

        ?><tr>
            <td><?php echo $shipment->order_no; ?></td>
            <td><?php echo $shipment->order_timestamp; ?></td>
            <td><?php echo $shipment->from_certificate_no; ?></td>
            <td><?php echo $shipment->created_date->format('Y-m-d H:i:s'); ?></td>
            <td><?php echo $shipment->shipto_name; ?></td>
            <td><?php echo $shipment->itemno; ?></td>
            <td><?php foreach($itemList as $item) echo "<div>".$item["varenr"]." (".$item["quantity"]." stk)</div>"; ?></td>
            <td><?php foreach($itemList as $item) echo "<div>".$item["status"]."</div>"; ?></td>
            <td><?php foreach($itemList as $item) echo "<div>".$item["stockbefore"]."</div>"; ?></td>
            <td><?php foreach($itemList as $item) echo "<div>".$item["stockafter"]."</div>"; ?></td>
            <td><?php echo $packShipment ? "PAKKES" : "SKIP"; ?></td>
            <td><?php echo $this->currentCount." i fil ".$this->fileIndex; ?></td>
        </tr><?php

        if($this->currentCount >= 490) {
            $this->fileList[] = $this->currentFile;
            $this->currentFile = array();
            $this->currentCount = 0;
            $this->fileIndex++;
        }

    }

    private function printBeforeBeholdning()
    {

        ?><div class="sectionhead" style="background: #FFD4B2; padding: 10px; font-size: 12px; font-weight: bold; color: #444444; font-size: 22px;">
            Vareliste inden træk
        </div>
        <table style="width: 100%;" class="dsvtable" cellpadding="0" cellspacing="0">
            <thead>
                <tr>
                    <th>Varenr</th>
                    <th>Beskrivelse</th>
                    <th>SAM</th>
                    <th>Childs</th>
                    <th>On sam</th>
                    <th>DSV Lager</th>
                    <th>DSV Processed</th>
                    <th>DSV Counter</th>
                    <th>Waiting</th>
                    <th>New waiting</th>
                </th>
            </thead><tbody><?php

            foreach($this->vareData as $vareNr => $data) {
                ?><tr>
                    <td><?php echo $vareNr; ?></td>
                    <td><?php echo $data["navobj"]->description; ?></td>
                <td><?php echo $data["issampak"] ? "SAMPAK" : "SINGLE"; ?></td>
                    <td><?php

                        foreach($data["subitems"] as $item) {
                            echo "<div>".$item["quantity"]." stk ".$item["varenr"]."</div>";
                        }

                 ?></td>
                <td><?php

                    foreach($data["onsam"] as $item) {
                        echo "<div>".$item["quantity"]." stk ".$item["varenr"]."</div>";
                    }

                    ?></td>
                    <td><?php echo $data["dsvlager"]; ?></td>
                    <td><?php echo $data["dsvprocessed"]; ?></td>
                    <td><?php echo $data["dsvcounter"]; ?></td>
                    <td><?php echo $data["waiting"]; ?></td>
                    <td><?php echo $data["newwaiting"]; ?></td>
                </tr><?php
            }

            ?></tbody></table><?php

    }


    private function printAfterBeholdning()
    {

        ?><div class="sectionhead" style="background: #FFD4B2; padding: 10px; font-size: 12px; font-weight: bold; color: #444444; font-size: 22px;">
        Vareliste efter træk
    </div>
        <table style="width: 100%;" class="dsvtable" cellpadding="0" cellspacing="0">
        <thead>
        <tr>
            <th>Varenr</th>
            <th>Beskrivelse</th>
            <th>SAM</th>
            <th>Childs</th>
            <th>On sam</th>
            <th>DSV Lager før</th>
            <th>DSV Lager nu </th>
            <th>Ventede</th>
            <th>Venter nu</th>
            <th>Sendt</th>
            <th>Ikke sendt</th>
            </th>
        </thead><tbody><?php

        foreach($this->vareData as $vareNr => $data) {
            ?><tr>
            <td><?php echo $vareNr; ?></td>
            <td><?php echo $data["navobj"]->description; ?></td>
            <td><?php echo $data["issampak"] ? "SAMPAK" : "SINGLE"; ?></td>
            <td><?php

                foreach($data["subitems"] as $item) {
                    echo "<div>".$item["quantity"]." stk ".$item["varenr"]."</div>";
                }

                ?></td>
            <td><?php

                foreach($data["onsam"] as $item) {
                    echo "<div>".$item["quantity"]." stk ".$item["varenr"]."</div>";
                }

                ?></td>
            <td><?php echo $data["dsvlager"]; ?></td>
            <td><?php echo $data["dsvcounter"]; ?></td>
            <td><?php echo $data["waiting"]; ?></td>
            <td><?php echo $data["newwaiting"]; ?></td>
            <td><?php echo $data["packok"]; ?></td>
            <td><?php echo $data["nopack"]; ?></td>
            </tr><?php
        }

        ?></tbody></table><?php

    }

    private function templateTop() {

        ?><html>
        <head>
            <style>

                body { margin: 0px; padding: 0px; font-family: verdana; font-size: 14px; line-height: 120%; }

                * {
                    box-sizing: border-box;
                    -moz-box-sizing: border-box;
                    -webkit-box-sizing: border-box;
                }

                .header { background: #7895B2; width: 100%; height: 50px; overflow: hidden; padding: 15px; font-size: 24px; font-weight: bold; color: white; }


                .dsvtable { width: 100%; }


                .dsvTable thead {
                    position: sticky; top: 0; z-index: 1;
                }

                .dsvtable thead tr th {
                    background: #AEBDCA; color: white; padding: 5px; border-bottom: 1px solid #AEBDCA; font-size: 14px; font-weight: bold; text-align: left;
                }

                .dsvtable tbody tr td {
                    padding: 5px; border-bottom: 1px solid #AEBDCA; font-size: 14px; text-align: left;
                }

                .dsvtable tbody tr.sampak td {
                    background: #E8DFCA; padding: 5px; border-bottom: 1px solid #AEBDCA; font-size: 14px; text-align: left; font-weight: bold;
                }

                th.num { text-align: right !important;}
                td.num { text-align: right !important;}

                tr.usedbefore { background: #FFD4B2 !important; }

                .calcerr { background: red !important; color: white; }
                .calcok { background: #86C8BC !important; color: white; }
                .calczero { background: #CEEDC7 !important; color: black; }
                .calcempty {  }

                tr.hidden { display: none; }

                .footerplaceholder { height: 50px; background: blue; }
                .footer { height: 50px; width: 100%; position: fixed; bottom: 0px;background: #7895B2; }

                .footer a { color: white !important; }

            </style>
            <script src="https://system.gavefabrikken.dk/gavefabrikken_backend/views/lib/jquery.min.js"></script>
        </head>
        <body>
        <div class="header">
            <div style="float: right;">
                <button type="button" onclick="document.location='index.php?rt=unit/privatedelivery/sedsv/lagerrapport'">Træk lager rapport</button>
                <button type="button" onclick="document.location='index.php?rt=unit/privatedelivery/sedsv/index'">Gå til fil-oversigt</button>
            </div>
            DSV Vareoversigt
        </div>
        <?php

    }

    private function templateBottom() {

        ?><div class="footerplaceholder"></div>
        <div class="footer">
            <table style="width: 100%;">
                <tr>
                    <td style="width: 20%;padding: 10px;"></td>
                    <td style="width: 20%;padding: 10px; color: white;"></td>
                    <td style="width: 20%;padding: 10px; color: white;"></td>
                    <td style="width: 20%;padding: 10px;"></td>
                    <td style="width: 20%;padding: 10px;">
                        <form method="post" action="index.php?rt=unit/privatedelivery/sedsv/autopluk">
                            <button type="submit">Opret pluklister</button>
                            <input type="hidden" name="action" value="runlist">
                        </form>
                    </td>
                </tr>
            </table>
        </div>

        <script>

            $(document).ready(function() {

            });

        </script>

        </body>
        </html><?php

    }

    /**
     * LOAD DATA
     */

    private $varenrList;
    private $vareData;
    private $currentFile;
    private $currentCount;
    private $fileIndex;
    private $fileList;
    private $shipmentsSent;
    private $shipmentsNotSent;

    private function loadData()
    {

        $this->varenrList = $this->helper->getVarenrList();
        $this->vareData = array();

        // Create empty vare data in varedata map
        foreach($this->varenrList as $vareNr) {

            $vareNr = $this->normalizeVarene($vareNr);

            $this->vareData[$vareNr] = array(
                "navobj" => null,
                "onsam" => array(),
                "subitems" => array(),
                "issampak" => false,
                "varenr" => $vareNr,
                "dsvlager" => 0,
                "dsvprocessed" => 0,
                "dsvcounter" => 0,
                "waiting" => 0,
                "newwaiting" => 0,
                "packok" => 0,
                "nopack" => 0
            );
        }

        foreach($this->varenrList as $varenr) {
            if($this->helper->isSampakVarenr($varenr)) {
                $this->outputVarenrSampak($varenr);
            }
        }

        foreach($this->varenrList as $varenr) {
            if(!$this->helper->isSampakVarenr($varenr)) {
                $this->outputVarenrLine($varenr,null);
            }
        }

        $this->currentFile = array();
        $this->currentCount = 0;
        $this->fileIndex = 1;
        $this->fileList = array();
        $this->shipmentsSent = 0;
        $this->shipmentsNotSent = 0;
    }



    private function outputVarenrSampak($sampakVarenr) {

        $sampakVarenr = $this->normalizeVarene($sampakVarenr);

        // Get vare
        $vare = $this->helper->getNavVare($sampakVarenr);
        $this->vareData[$sampakVarenr]["navobj"] = $vare;

        // Already sent
        $alreadySent = $this->helper->getSentSinceLastLagerUpdate($sampakVarenr);

        // Waiting
        $waitingCount = $this->helper->getShipmentsWaitingCount($sampakVarenr);

        // Update stats
        $this->vareData[$sampakVarenr]["dsvprocessed"] += $alreadySent;
        $this->vareData[$sampakVarenr]["waiting"] += $waitingCount;
        $this->vareData[$sampakVarenr]["newwaiting"] = $this->vareData[$sampakVarenr]["waiting"];
        $this->vareData[$sampakVarenr]["issampak"] = true;

        // Count max on lager
        $maxNo = null;
        $subVarenr = $this->helper->getItemsInSampak($sampakVarenr);
        foreach($subVarenr as $subNo) {

            $subNo = $this->normalizeVarene($subNo);

            $subQuantity = $this->helper->getBomItemQuantity($sampakVarenr,$subNo);
            $subLager = $this->helper->getDSVLagerQuantity($subNo);
            $canMake = $subQuantity == 0 ? 0 : floor($subLager/$subQuantity);
            if($maxNo === null || $maxNo > $canMake) {
                $maxNo = $canMake;
            }

            $this->vareData[$sampakVarenr]["subitems"][] = array("varenr" => $subNo,"quantity" => $subQuantity);

        }

        $sentSinceLagerUpdate = $this->helper->getSentSinceLastLagerUpdate($sampakVarenr);
        if($sentSinceLagerUpdate > 0 ) {
            $maxNo -= $sentSinceLagerUpdate;
        }

        $this->vareData[$sampakVarenr]["dsvlager"] = $maxNo;
        $this->vareData[$sampakVarenr]["dsvcounter"] = $this->vareData[$sampakVarenr]["dsvlager"];

        // process child lines
        foreach ($subVarenr as $varenr) {
            $this->outputVarenrLine($varenr,$sampakVarenr);
        }
    }


    private function outputVarenrLine($varenr,$sampakNr=null)
    {
        $varenr = $this->normalizeVarene($varenr);
        $isSingleLine = ($sampakNr == null);

        // Get vare
        $vare = $this->helper->getNavVare($varenr);


        if(!isset($this->vareData[$varenr])) {
            $this->vareData[$varenr] =array(
                "navobj" => null,
                "onsam" => array(),
                "subitems" => array(),
                "issampak" => false,
                "varenr" => $varenr,
                "dsvlager" => 0,
                "dsvprocessed" => 0,
                "dsvcounter" => 0,
                "waiting" => 0,
                "newwaiting" => 0,
                "packok" => 0,
                "nopack" => 0
            );
        }

        $this->vareData[$varenr]["navobj"] = $vare;

        $prShipment = 1;
        if(!$isSingleLine) {

            // Items per shipment
            $prShipment = $this->helper->getBomItemQuantity($sampakNr,$varenr);

            // Already sent since last lager update
            $alreadySent = $this->helper->getSentSinceLastLagerUpdate($sampakNr);

            // Waiting
            $waitingCount = $this->helper->getShipmentsWaitingCount($sampakNr);

            $this->vareData[$varenr]["onsam"][] = array("varenr" => $sampakNr,"quantity" => $prShipment);

        }
        else {

            // Already sent
            $alreadySent = $this->helper->getSentSinceLastLagerUpdate($varenr);

            // Waiting
            $waitingCount = $this->helper->getShipmentsWaitingCount($varenr);

        }


        // Update processed and waiting
        $this->vareData[$varenr]["dsvprocessed"] += $alreadySent*$prShipment;
        $this->vareData[$varenr]["waiting"] += $waitingCount*$prShipment;
        $this->vareData[$varenr]["newwaiting"] = $this->vareData[$varenr]["waiting"];

        $dsvLager = $this->helper->getDSVLagerQuantity($varenr);
        $this->vareData[$varenr]["dsvlager"] = $dsvLager;
        $this->vareData[$varenr]["dsvcounter"] = $this->vareData[$varenr]["dsvlager"];

    }

    private function normalizeVarene($varenr) {
        return trim(strtolower($varenr));
    }



}
