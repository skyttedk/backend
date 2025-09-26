<?php

namespace GFUnit\tools\shopboardsync;
use GFBiz\units\UnitController;
use GFCommon\Model\Navision\SalesHeaderWS;
use GFCommon\Model\Navision\SalesLineWS;

class Controller extends UnitController
{

    public function __construct()
    {
        parent::__construct(__FILE__);
    }

    public function test()
    {

        $shopboardList = \Shopboard::find_by_sql("SELECT * FROM shop_board WHERE fk_shop > 0 && salgsordrenummer != '' && nav_synced IS NULL ORDER BY fk_shop asc");
        shuffle($shopboardList);

        $count = 0;

        foreach($shopboardList as $shopBoard) {
            $count++;
            try {
                $this->processShopBoard($shopBoard->id);
            } catch (\Exception $e) {
                echo "Error processing shopboard ".$shopBoard->id." / shop ".$shopBoard->fk_shop.": ".$e->getMessage();
            }

            if($count > 100) {
                break;
            }


        }

        \system::connection()->commit();



    }

    private function processShopBoard($shopBoardID) {

        // Load
        $shopBoard = \Shopboard::find($shopBoardID);
        $shop = \Shop::find($shopBoard->fk_shop);

        // Output header
        echo "<br><hr><br><h3>Processing shopboard ".$shopBoard->id." / shop ".$shop->id." / ".$shop->name."</h3><br>";

        // SO
        echo "SO: ".$shopBoard->salgsordrenummer."<br>";
        if($shopBoard->salgsordrenummer == "") {
            throw new \Exception("No salgsordrenummer");
        }

        // Load salesorder
        $client = new SalesHeaderWS();

        $order = $client->getHeader("ORDER", $shopBoard->salgsordrenummer);
        if($order == null) {
            throw new \Exception("No salesorder found");
        }

        echo "Found SO: ".$order->getNo()." to ".$order->getBilltoCustomerNo().": ".$order->getBilltoName()."<br>Levering: ".$order->getShiptoName()."<br>";

        //echo "<pre style='font-size: 12px;'>".print_r($order,true)."</pre>";
        echo "<br>Extracting lines for ".$order->returnKeyString("No")."<br>";

        $client = new SalesLineWS();
        $result = $client->getLines( $shopBoard->salgsordrenummer);
        if($result == null || count($result) == 0) {
            echo "No lines found<br>";
            return;
        }

        $hasIndpak = false;
        $hasLabel = false;
        $hasMultiDelivery = strstr($order->getShiptoName(),"Flere modtager");
        $hasPrivateDelivery = false;

        echo "<table>";
        foreach($result as $line) {

            if($line->getNo() == "INDPAK1" || $line->getNo() == "INDPAK") {
                $hasIndpak = true;
            }

            if($line->getNo() == "LABEL1" || $line->getNo() == "LABEL") {
                $hasLabel = true;
            }

            if($line->getNo() == "GLSPAKKESHOP" || $line->getNo() == "GLS PAKKESHOP") {
                $hasPrivateDelivery = true;
            }

            echo "<tr>
                <td>".$line->getLineNo()."</td>
                <td>".$line->getType()."</td>
                <td>".$line->getNo()."</td>
                <td>".$line->getDescription()."</td>
                <td>".$line->getQuantity()."</td>
                <td>".$line->getUnitPrice()."</td>
            </tr>";
        }
        echo "</table><br>";

        echo "<h3>Shopboardupdate</h3>";
        echo "<table>";

        $indpakAction = (($shopBoard->indpakning == 1) != $hasIndpak);
        $labelAction = (($shopBoard->navn_paa_gaver == 1) != $hasLabel);
        $multiDeliveryAction = (($shopBoard->flere_leveringsadresser == 1) != $hasMultiDelivery);
        $privatLevAction = (($shopBoard->privatlevering == 1) != $hasPrivateDelivery);

        echo "<tr><td>INDPAK</td><td>".($shopBoard->indpakning == 1 ? "JA" : "NEJ")."</td><td>".($hasIndpak ? "JA" : "NEJ")."</td><td>".($indpakAction ? "OPDATER" : "-")."</td>";
        echo "<tr><td>LABELS</td><td>".($shopBoard->navn_paa_gaver == 1 ? "JA" : "NEJ")."</td><td>".($hasLabel ? "JA" : "NEJ")."</td><td>".($labelAction ? "OPDATER" : "-")."</td>";
        echo "<tr><td>MULTIDELIVERY</td><td>".($shopBoard->flere_leveringsadresser == 1 ? "JA" : "NEJ")."</td><td>".($hasMultiDelivery ? "JA" : "NEJ")."</td><td>".($multiDeliveryAction ? "OPDATER" : "-")."</td>";
        echo "<tr><td>PRIVATLEVERING</td><td>".($shopBoard->privatlevering == 1 ? "JA" : "NEJ")."</td><td>".($hasPrivateDelivery ? "JA" : "NEJ")."</td><td>".($privatLevAction ? "OPDATER" : "-")."</td>";
        echo "</table>";

        // Find log file
        $logText = array();
/*
        if($indpakAction) {
            if($hasIndpak) {
                $shopBoard->indpakning = 1;
                $logText[] = "Indpakning aktiveret: indpakning = 1 where fk_shop = ".$shopBoard->fk_shop;
            } else {
                $shopBoard->indpakning = 0;
                $logText[] = "Indpakning deaktiveret: indpakning = 0 where fk_shop = ".$shopBoard->fk_shop;
            }
        }

        if($labelAction) {
            if($hasLabel) {
                $shopBoard->navn_paa_gaver = 1;
                $logText[] = "Navn på gaver aktiveret: navn_paa_gaver = 1 where fk_shop = ".$shopBoard->fk_shop;
            } else {
                $shopBoard->navn_paa_gaver = 0;
                $logText[] = "Navn på gaver deaktiveret: navn_paa_gaver = 0 where fk_shop = ".$shopBoard->fk_shop;
            }
        }

        if($multiDeliveryAction) {
            if($hasMultiDelivery) {
                $shopBoard->flere_leveringsadresser = 1;
                $logText[] = "Flere adresser aktiveret: flere_leveringsadresser = 1 where fk_shop = ".$shopBoard->fk_shop;
            } else {
                $shopBoard->flere_leveringsadresser = 0;
                $logText[] = "Flere adresser deaktiveret: flere_leveringsadresser = 0 where fk_shop = ".$shopBoard->fk_shop;
            }
        }



 */

        if($privatLevAction) {
            if($hasPrivateDelivery) {
                $shopBoard->privatlevering = 1;
                $logText[] = "Privatlevering aktiveret: privatlevering = 1 where fk_shop = ".$shopBoard->fk_shop;
            } else {
                $shopBoard->privatlevering = 0;
                $logText[] = "Privatlevering deaktiveret: privatlevering = 0 where fk_shop = ".$shopBoard->fk_shop;
            }
        }

        $shopBoard->nav_synced = date('d-m-Y H:i:s');
        $shopBoard->save();

        if(count($logText) > 0) {
            $logFile = dirname(__FILE__).DIRECTORY_SEPARATOR."shopboardsynclog.txt";

            file_put_contents($logFile, "Sync shopboard for: ".$shop->id.": ".$shop->name." [".$shopBoard->id."]\r\n".implode("\r\n",$logText)."\r\n\r\n", FILE_APPEND);
            echo implode("<br>",$logText);
        }

    }

}


