<?php

namespace GFUnit\vlager\incoming;
use GFBiz\units\UnitController;
use GFCommon\Model\Navision\PurchaseHeadersWS;
use GFCommon\Model\Navision\PurchaseLinesWS;
use GFCommon\Model\Navision\TransferHeadersWS;
use GFCommon\Model\Navision\TransferLinesWS;
use GFUnit\vlager\utils\VLager;


class CheckIncomingJob extends UnitController
{

    public function __construct()
    {
        parent::__construct(__FILE__);
    }

    public function checkIncomingJob()
    {

        // Load active vlager
        $vlagerList = \VLager::find_by_sql("select * from vlager where active = 1 and location is not null and location != ''");
        foreach($vlagerList as $vlager)
        {
            $this->checkVLager($vlager);
        }

        \system::connection()->commit();
    }

    private function checkVLager($vlager)
    {

        echo "Checking nav vlager: " . $vlager->name . " [NAV CODE: ".$vlager->location."]<br>";

        // Check transfer orders
        $client = new TransferHeadersWS();
        $toList = $client->getByLocation($vlager->location);

        foreach($toList as $to) {
            $this->checkVLagerTO($vlager, $to);
        }
        
        // Check purchase orders
        $client = new PurchaseHeadersWS();
        $poList = $client->getByLocation("DP-SE");

        foreach($poList as $po) {
            $this->checkVLagerPO($vlager, $po);
        }
        
    }

    private function checkVLagerPO($vlager,$po) 
    {

        echo "Checking PO: " . $po->getNo() . "<br>";

        // Find in vlager incoming
        $vlagerIncoming = \VlagerIncoming::find_by_sql("select * from vlager_incoming where sono LIKE '".$po->getNo()."' and vlager_id = ".$vlager->id);
        if(count($vlagerIncoming) > 0) {
            echo "PO already in vlager incoming<br>";
            return;
        }

        // Get important info
        $metalines = $po->getMetaLines();
        $metaData = "";
        foreach($metalines as $lineName => $lineValue) {
            $metaData .= $lineName.": ".$lineValue."<br>";
        }

        // Get lines
        $plClient = new PurchaseLinesWS();
        $lines = $plClient->getLines($po->getNo());

        // Create in vlagerincoming
        $vli = new \VLagerIncoming();
        $vli->vlager_id = $vlager->id;
        $vli->sono = $po->getNo();
        $vli->created = date("Y-m-d H:i:s");
        $vli->sender_note = $metaData;
        $vli->receiver_note = "";
        $vli->save();

        foreach($lines as $line) {

            echo " - Processing line: ".$line->getLineNo()."<br>";

            if($line->getType() != "Item") {
                echo " - Not item, is ".$line->getType()."<br>";
            } else if($line->getNo() == "") {
                echo " - Missing itemno in line<br>";
            } else if($line->getQuantity() == 0) {
                echo " - Missing quantity in line<br>";
            } else {

                $vlil = new \VLagerIncomingLine();
                $vlil->vlager_id = $vlager->id;
                $vlil->vlager_incoming_id = $vli->id;
                $vlil->itemno = $line->getNo();
                $vlil->quantity_order = $line->getQuantity();
                $vlil->quantity_received = $line->getQuantity();
                $vlil->save();

            }

        }

        echo "<br><br>";
        
    }
    
    private function checkVLagerTO($vlager,$to)
    {

        echo "Checking TO: " . $to->getNo() . "<br>";

        if($to->getStatus() != "Released") {
            echo "TO is not released: ".$to->getStatus()."<br>";
            return;
        }

        // Find in vlager incoming
        $vlagerIncoming = \VlagerIncoming::find_by_sql("select * from vlager_incoming where sono LIKE '".$to->getNo()."' and vlager_id = ".$vlager->id);
        if(count($vlagerIncoming) == 1) {
            foreach($vlagerIncoming as $vli) {
                if($vli->received != null) {
                    echo "TO already received<br>";
                    return;
                } else {
                    echo "TO not received, update<br>";
                    $this->updateTO($to->getNo(), $vlager, $vli);
                    return;
                }
            }

            return;
        } else if(count($vlagerIncoming) > 1) {
            echo "Multiple TOs found in vlager incoming, ignore!<br>";
            return;
        }

        // Get important info
        $metalines = $to->getImportantMetadata();
        $metaData = "";
        foreach($metalines as $lineName => $lineValue) {
            $metaData .= $lineName.": ".$lineValue."<br>";
        }

        // Get lines
        $tlClient = new TransferLinesWS();
        $lines = $tlClient->getLines($to->getNo());

        // Create in vlagerincoming
        $vli = new \VLagerIncoming();
        $vli->vlager_id = $vlager->id;
        $vli->sono = $to->getNo();
        $vli->created = date("Y-m-d H:i:s");
        $vli->sender_note = $metaData;
        $vli->receiver_note = "";
        $vli->save();

        foreach($lines as $line) {

            echo " - Processing line: ".$line->getLineNo()."<br>";

            if($line->getItemNo() == "") {
                echo " - Missing itemno in line<br>";
            } else if($line->getQuantity() == 0) {
                echo " - Missing quantity in line<br>";
            } else {

                $vlil = new \VLagerIncomingLine();
                $vlil->vlager_id = $vlager->id;
                $vlil->vlager_incoming_id = $vli->id;
                $vlil->itemno = $line->getItemNo();
                $vlil->quantity_order = $line->getQuantity();
                $vlil->quantity_received = $line->getQuantity();
                $vlil->save();

            }

        }

        echo "<br><br>";

    }

    private function updateTO($toNo, $vlager, $vli) {

        // Hent linjer fra NAV
        $tlClient = new TransferLinesWS();
        $newLines = $tlClient->getLines($toNo);
        //echo "Found ".count($newLines)." lines in NAV<br>";


        // Hent eksisterende linjer fra databasen
        $existingLines = \VLagerIncomingLine::find('all',array('conditions' => array('vlager_incoming_id' =>  $vli->id)));
        //echo "Found ".count($existingLines)." existing lines in database<br>";

        // Opret en map for nem adgang til eksisterende linjer
        $existingLinesMap = [];
        foreach ($existingLines as $existingLine) {
            $existingLinesMap[$existingLine->itemno] = $existingLine;
            //echo "Mapped existing line: {$existingLine->itemno} (Qty: {$existingLine->quantity_order})<br>";
        }

        // Hold styr på behandlede linjer
        $processedItems = [];


        // Behandle nye linjer
        foreach ($newLines as $newLine) {
            $itemNo = $newLine->getItemNo();
            $quantity = $newLine->getQuantity();

            // Skip hvis vi allerede har behandlet denne vare
            if(in_array($itemNo, $processedItems)) {
                //echo "Skipping duplicate line: $itemNo<br>";
                continue;
            }

            //echo "<br>Processing line - ItemNo: $itemNo, Quantity: $quantity<br>";

            // Skip invalide linjer
            if($itemNo == "") {
                //echo " - SKIPPED: Missing itemno in line<br>";
                continue;
            } else if($quantity == 0) {
                //echo " - SKIPPED: Missing quantity in line<br>";
                continue;
            }

            if (isset($existingLinesMap[$itemNo])) {
                // Opdater eksisterende linje
                $existingLine = $existingLinesMap[$itemNo];
                //echo " - UPDATING: Old qty: {$existingLine->quantity_order}, New qty: $quantity<br>";
                $existingLine->quantity_order = $quantity;
                $existingLine->quantity_received = $quantity;
                $existingLine->save();

                // Fjern fra map
                unset($existingLinesMap[$itemNo]);
            } else {
                // Opret ny linje
                //echo " - CREATING: New line with qty: $quantity<br>";
                $vlil = new \VLagerIncomingLine();
                $vlil->vlager_id = $vlager->id;
                $vlil->vlager_incoming_id = $vli->id;
                $vlil->itemno = $itemNo;
                $vlil->quantity_order = $quantity;
                $vlil->quantity_received = $quantity;
                $vlil->save();
            }

            // Marker denne vare som behandlet
            $processedItems[] = $itemNo;
        }

        // Slet linjer der ikke længere findes
        foreach ($existingLinesMap as $remainingLine) {
            //echo "<br>DELETING: Item {$remainingLine->itemno} (Qty: {$remainingLine->quantity_order})<br>";
            $remainingLine->delete();
        }

    }


    private function sendErrorEmail( $message)
    {
        mailgf("sc@interactive.dk","VLAGER Process problem", $message);
    }

}
