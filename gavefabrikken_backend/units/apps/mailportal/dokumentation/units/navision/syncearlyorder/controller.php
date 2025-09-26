<?php

namespace GFUnit\navision\syncearlyorder;
use GFBiz\units\UnitController;

class Controller extends UnitController
{

    public function __construct()
    {
        parent::__construct(__FILE__);
    }


    private function getHoldBackItems($lang)
    {
        $holdBackItems = array(
            1 => array(),
            4 => array(),
            5 => array()
        );

        /*
        $holdBackItems = array(
            1 => array("SAM4164","SAM2088","SAM4165","KO06-0103LS012","230202","sam4171"),
            4 => array("SAM4164","SAM2088","SAM4165","KO06-0103LS012","230202","sam4171"),
            5 => array("SAM4164","SAM2088","SAM4165","KO06-0103LS012","230202","sam4171")
        );
        */

        return $holdBackItems[$lang];
    }

    public function release($lang)
    {

        // Load orders
        $shipments = $this->getWaitingEarlyOrdersByCountry($lang,true);

        // Remove no orders
        $shipments = $this->noFilter($shipments,false,true);

        echo "Found ".count($shipments)." earlyorders for ".$this->langToString($lang)."\n";

        foreach($shipments as $ship)
        {

            $shipment = \Shipment::find($ship->id);
            try {
                echo "Enable early order ".$ship->id."<br>";
                $shipment->force_syncnow = 1;
                $shipment->handle_country = 1;
                $shipment->handler = 'navision';
                $shipment->save();
            } catch(\Exception $e) {
                echo "Error: ".$e->getMessage()."<br>";
            }

        }


        \System::connection()->commit();

    }

    public function readylist($lang=0,$remove=0)
    {

        $list = $this->getWaitingEarlyOrdersByCountry($lang,$remove == 1 ? true : false);

        // Force download as csv header add utf-8 encodding and bom
        header("Content-type: text/csv");
        header("Content-Disposition: attachment; filename=earlyorder_readylist.csv");
        header("Pragma: no-cache");
        header("Expires: 0");


        // Output fields as csv: shipment.shipto_name as Name, shipment.shipto_address as Address, shipment.shipto_address2 as Address2, shipment.shipto_postcode as PostCode, shipment.shipto_city as City, IF(shipment.shipto_country = 4,'NO',shipto_country) as Country,  shipment.quantity as Quantity, shipment.itemno as ItemNo, shipment.shipto_contact as Contact, shipment.shipto_email as Email, shipment.shipto_phone as Phone, company_order.order_no as OrderNo, company_order.company_name, shipment.id as ShipmentID
        echo "\xEF\xBB\xBF";
        echo "Name;Address;Address2;PostCode;City;Country;Quantity;ItemNo;Quantity2;ItemNo2;Quantity3;ItemNo3;Quantity4;ItemNo4;Quantity5;ItemNo5;Contact;Email;Phone;OrderNo;CompanyName;ShipmentID\n";
        foreach($list as $row) {

            $order = \CompanyOrder::find($row->companyorder_id);
            
            echo $row->shipto_name.";".$row->shipto_address.";".$row->shipto_address2.";".$row->shipto_postcode.";".$row->shipto_city.";".$row->shipto_country.";".$row->quantity.";".$row->itemno.";".$row->quantity2.";".$row->itemno2.";".$row->quantity3.";".$row->itemno3.";".$row->quantity4.";".$row->itemno4.";".$row->quantity5.";".$row->itemno5.";".$row->shipto_contact.";".$row->shipto_email.";".$row->shipto_phone.";".$order->order_no.";".$order->company_name.";".$row->id."\n";


            
        }



    }

    public function readysum($lang=0,$remove=0)
    {

        $list = $this->getWaitingEarlyOrdersByCountry($lang,$remove == 1 ? true : false);

        // Add items to a count map
        $countMap = array();

        foreach($list as $shipment) {

            if($shipment->quantity > 0) {
                if(!isset($countMap[$shipment->itemno])) {
                    $countMap[$shipment->itemno] = 0;
                }
                $countMap[$shipment->itemno] += $shipment->quantity;
            }

            if($shipment->quantity2 > 0) {
                if(!isset($countMap[$shipment->itemno2])) {
                    $countMap[$shipment->itemno2] = 0;
                }
                $countMap[$shipment->itemno2] += $shipment->quantity2;
            }

            if($shipment->quantity3 > 0) {
                if(!isset($countMap[$shipment->itemno3])) {
                    $countMap[$shipment->itemno3] = 0;
                }
                $countMap[$shipment->itemno3] += $shipment->quantity3;
            }

            if($shipment->quantity4 > 0) {
                if(!isset($countMap[$shipment->itemno4])) {
                    $countMap[$shipment->itemno4] = 0;
                }
                $countMap[$shipment->itemno4] += $shipment->quantity4;
            }

            if($shipment->quantity5 > 0) {
                if(!isset($countMap[$shipment->itemno5])) {
                    $countMap[$shipment->itemno5] = 0;
                }
                $countMap[$shipment->itemno5] += $shipment->quantity5;
            }

        }

        // Run through count map and trim and lowercase, if similar keys merge them
        $newCountMap = array();
        foreach($countMap as $itemno => $count) {
            $itemno = trim(strtolower($itemno));
            if(isset($newCountMap[$itemno])) {
                $newCountMap[$itemno] += $count;
            } else {
                $newCountMap[$itemno] = $count;
            }
        }

        // Sort by key
        ksort($newCountMap);

        $blockedItems = $this->getHoldBackItems($lang);

        // Convert all blockedItems to lowercase and trim
        $blockedItems = array_map('strtolower', $blockedItems);
        $blockedItems = array_map('trim', $blockedItems);


        // Force download as csv header add utf-8 encodding and bom
        header("Content-type: text/csv");
        header("Content-Disposition: attachment; filename=earlyorder_sumlist_".$lang.".csv");
        header("Pragma: no-cache");
        header("Expires: 0");


        // Output fields as csv: shipment.shipto_name as Name, shipment.shipto_address as Address, shipment.shipto_address2 as Address2, shipment.shipto_postcode as PostCode, shipment.shipto_city as City, IF(shipment.shipto_country = 4,'NO',shipto_country) as Country,  shipment.quantity as Quantity, shipment.itemno as ItemNo, shipment.shipto_contact as Contact, shipment.shipto_email as Email, shipment.shipto_phone as Phone, company_order.order_no as OrderNo, company_order.company_name, shipment.id as ShipmentID
        echo "\xEF\xBB\xBF";
        echo "ItemNo;Description;Count;Blocked\n";
        foreach($newCountMap as $itemNo => $count) {

            $description = "Ukendt";

            $items = \NavisionItem::find_by_sql("SELECT * FROM navision_item WHERE language_id = 1 && no LIKE '".$itemNo."' && deleted IS NULL");
            if(count($items) > 0) {
                $description = $items[0]->description;
            } else {
                $items = \NavisionItem::find_by_sql("SELECT * FROM navision_item WHERE language_id = 4 && no LIKE '".$itemNo."' && deleted IS NULL");
                if(count($items) > 0) {
                    $description = $items[0]->description." (NO)";
                }
                else {
                    $description = "Findes ikke i nav";
                }
            }

            $isBlocked = false;
		    if(in_array($itemNo, $blockedItems)) {
                $isBlocked = true;
            }

            echo $itemNo.";".$description.";".$count.";".($isBlocked ? "Blokkeret" : "")."\n";

        }

    }

    public function downloadno($registerSent=0)
    {

        $noallready = $this->getWaitingEarlyOrdersByCountry(4,true);



        $nocollide = $this->noFilter($noallready,true,false);

      
        // Force download as csv header add utf-8 encodding and bom
        header("Content-type: text/csv");
        header("Content-Disposition: attachment; filename=earlyorder_readylist.csv");
        header("Pragma: no-cache");
        header("Expires: 0");


        // Output fields as csv: shipment.shipto_name as Name, shipment.shipto_address as Address, shipment.shipto_address2 as Address2, shipment.shipto_postcode as PostCode, shipment.shipto_city as City, IF(shipment.shipto_country = 4,'NO',shipto_country) as Country,  shipment.quantity as Quantity, shipment.itemno as ItemNo, shipment.shipto_contact as Contact, shipment.shipto_email as Email, shipment.shipto_phone as Phone, company_order.order_no as OrderNo, company_order.company_name, shipment.id as ShipmentID
        echo "\xEF\xBB\xBF";
        echo "Name;Address;Address2;PostCode;City;Country;Quantity;ItemNo;Quantity2;ItemNo2;Quantity3;ItemNo3;Quantity4;ItemNo4;Quantity5;ItemNo5;Contact;Email;Phone;OrderNo;CompanyName;ShipmentID\n";
        foreach($nocollide as $row) {

            $order = \CompanyOrder::find($row->companyorder_id);


            echo $row->shipto_name.";".$row->shipto_address.";".$row->shipto_address2.";".$row->shipto_postcode.";".$row->shipto_city.";".$row->shipto_country.";".$row->quantity.";".$row->itemno.";".$row->quantity2.";".$row->itemno2.";".$row->quantity3.";".$row->itemno3.";".$row->quantity4.";".$row->itemno4.";".$row->quantity5.";".$row->itemno5.";".$row->shipto_contact.";".$row->shipto_email.";".$row->shipto_phone.";".$order->order_no.";".$order->company_name.";".$row->id."\n";

             if(intval($registerSent) == 1) {
                $shipment = \Shipment::find($row->id);
                $shipment->handler = "manual";
                $shipment->shipment_state = 2;
                $shipment->shipment_sync_date = date("Y-m-d H:i:s");
                $shipment->save();
            }


        }

         \system::connection()->commit();

    }

    public function dashboard()
    {



        ?>

        <h2>Earlyorder dashboard</h2>

        <h3>Optælling</h3>
        <p>Vis hvilke der er klar</p>
        <?php $this->showEarlyOrderCounterTable(); ?>

        <h3>Trin 1: sync betalingsstatus på ordre</h3>
        <p>
            <a href="https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/navision/syncpaid/syncpayments" target="_blank"><button type="button">Synkroniser betalingsstatus i alle lande</button></a>
            <a href="https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/navision/syncpaid/syncpaymentlang/1" target="_blank"><button type="button">Synkroniser betalingsstatus i DK</button></a>
            <a href="https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/navision/syncpaid/syncpaymentlang/4" target="_blank"><button type="button">Synkroniser betalingsstatus i NO</button></a>
            <a href="https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/navision/syncpaid/syncpaymentlang/5" target="_blank"><button type="button">Synkroniser betalingsstatus i SE</button></a>
        </p>

        <h3>Trin 2: udeladte varer</h3>
        <table>
            <tr>
                <td>
                    <b>DK</b>
                    <ul>
                        <?php

                        $list = $this->getHoldBackItems(1);
                        if(count($list) > 0) {
                            echo "<li>".implode("</li><li>",$list)."</li>";
                        }

                        ?>
                    </ul>
                </td>
                <td>
                    <b>NO</b>
                    <ul>
                        <?php

                        $list = $this->getHoldBackItems(1);
                        if(count($list) > 0) {
                            echo "<li>".implode("</li><li>",$list)."</li>";
                        }

                        ?>
                    </ul>
                </td>
                <td>
                    <b>SE</b>
                    <ul>
                        <?php

                        $list = $this->getHoldBackItems(1);
                        if(count($list) > 0) {
                            echo "<li>".implode("</li><li>",$list)."</li>";
                        }

                        ?>
                    </ul>
                </td>
            </tr>
        </table>


        <h3>Trin 3: Earlyordre klar</h3>
        <table>
            <tr>
                <td>
                    <b>DK</b>
                    Klar: <?php $list = $this->getWaitingEarlyOrdersByCountry(1,true); echo count($list); ?>
                    Fjernet: <?php $list2 = $this->getWaitingEarlyOrdersByCountry(1,false); echo count($list2)-count($list); ?>
                    <a href="https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/navision/syncearlyorder/readylist/1/1" target="_blank"><button type="button">Hent liste uden blokkerede varer</button></a>
                    <a href="https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/navision/syncearlyorder/readylist/1/0" target="_blank"><button type="button">Hent liste med blokkerede varer</button></a>
                </td>
                <td>
                    <b>NO</b>
                    Klar: <?php $list = $this->getWaitingEarlyOrdersByCountry(4,true); echo count($list); ?>
                    Fjernet: <?php $list2 = $this->getWaitingEarlyOrdersByCountry(4,false); echo count($list2)-count($list); ?>
                    <a href="https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/navision/syncearlyorder/readylist/4/1" target="_blank"><button type="button">Hent liste uden blokkerede varer</button></a>
                    <a href="https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/navision/syncearlyorder/readylist/4/0" target="_blank"><button type="button">Hent liste med blokkerede varer</button></a>
                </td>
                <td>
                    <b>SE</b>
                    Klar: <?php $list = $this->getWaitingEarlyOrdersByCountry(5,true); echo count($list); ?>
                    Fjernet: <?php $list2 = $this->getWaitingEarlyOrdersByCountry(5,false); echo count($list2)-count($list); ?>
                    <a href="https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/navision/syncearlyorder/readylist/5/1" target="_blank"><button type="button">Hent liste uden blokkerede varer</button></a>
                    <a href="https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/navision/syncearlyorder/readylist/5/0" target="_blank"><button type="button">Hent liste med blokkerede varer</button></a>
                </td>
            </tr>
        </table>

        <h3>Trin 4: Sumliste </h3>
        <table>
            <tr>
                <td>
                    <b>DK</b>
                    Klar: <?php $list = $this->getWaitingEarlyOrdersByCountry(1,true); echo count($list); ?>
                    Fjernet: <?php $list2 = $this->getWaitingEarlyOrdersByCountry(1,false); echo count($list2)-count($list); ?>
                    <a href="https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/navision/syncearlyorder/readysum/1/1" target="_blank"><button type="button">Hent sumliste uden blokkerede varer</button></a>
                    <a href="https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/navision/syncearlyorder/readysum/1/0" target="_blank"><button type="button">Hent sumliste med blokkerede varer</button></a>
                </td>
                <td>
                    <b>NO</b>
                    Klar: <?php $list = $this->getWaitingEarlyOrdersByCountry(4,true); echo count($list); ?>
                    Fjernet: <?php $list2 = $this->getWaitingEarlyOrdersByCountry(4,false); echo count($list2)-count($list); ?>
                    <a href="https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/navision/syncearlyorder/readysum/4/1" target="_blank"><button type="button">Hent sumliste uden blokkerede varer</button></a>
                    <a href="https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/navision/syncearlyorder/readysum/4/0" target="_blank"><button type="button">Hent sumliste med blokkerede varer</button></a>
                </td>
                <td>
                    <b>SE</b>
                    Klar: <?php $list = $this->getWaitingEarlyOrdersByCountry(5,true); echo count($list); ?>
                    Fjernet: <?php $list2 = $this->getWaitingEarlyOrdersByCountry(5,false); echo count($list2)-count($list); ?>
                    <a href="https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/navision/syncearlyorder/readysum/5/1" target="_blank"><button type="button">Hent sumliste uden blokkerede varer</button></a>
                    <a href="https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/navision/syncearlyorder/readysum/5/0" target="_blank"><button type="button">Hent sumliste med blokkerede varer</button></a>
                </td>
            </tr>
        </table>


        <h3>Trin 5: Norge - kolliderende</h3>
        <p>
            Find de earlyordre der kolliderer med både no og ikke no varer. De skal håndteres manuelt og splittes op til 1 med dansk og 1 med norsk.
        </p>
        <p>
            <?php

                $noallready = $this->getWaitingEarlyOrdersByCountry(4,true);
                $nocollide = $this->noFilter($noallready,true,true);

                echo "Der er ".count($nocollide)." kolliderende earlyordre i norge";
                $collideidlist = [];
                foreach($nocollide as $ship) {
                    $collideidlist[] = $ship->id;
                    echo "Shipment: ".$ship->id." - ".$ship->itemno." - ".$ship->itemno2." - ".$ship->itemno3." - ".$ship->itemno4." - ".$ship->itemno5."<br>";
                }

                if(count($collideidlist) > 0) {
                    echo "<p>select * from shipment where id in (".implode(",",$collideidlist).")</p>";
                }

            ?>
        </p>

        <h3>Trin 6: udtræk varer til norge</h3>
        <p>
            Træk earlyordre på norges egne varer.
        </p>
        <a>
            <a href="https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/navision/syncearlyorder/downloadno/0" target="_blank"><button>Hent uden at registrere som sendt</button></a>
            <a href="https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/navision/syncearlyorder/downloadno/1" target="_blank"><button>Hent og registrer som sendt</button></a>
        </p>


        <h3>Trin 7: frigiv earlyorders til nav</h3>
        <table>
            <tr>
                <td>
                    <b>DK</b>
                    <a href="https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/navision/syncearlyorder/release/1" target="_blank"><button type="button">Frigiv til navision</button></a>
                </td>
                <td>
                    <b>NO</b>
                    <a href="https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/navision/syncearlyorder/release/4" target="_blank"><button type="button">Frigiv til navision</button></a>
                </td>
                <td>
                    <b>SE</b>
                    <a href="https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/navision/syncearlyorder/release/5" target="_blank"><button type="button">Frigiv til navision</button></a>
                </td>
            </tr>
        </table>

        <h3>Frigivede earlyordre</h3>
        <p>
            Datoer der er synkroniseret earlyordre
        </p>
        <table>
            <tr>
                <td>Dato</td>
                <td>DK</td>
                <td>NO</td>
                <td>SE</td>
                <td>Total</td>
            </tr>
        </table>

        <?php


    }

    private $isNOVarenrCache = array();

    private function isNOVarenr($itemno)
    {

        // From cache
        if(isset($this->isNOVarenrCache[trim(strtolower($itemno))])) {
            return $this->isNOVarenrCache[trim(strtolower($itemno))];
        }

        $itemno = trim(strtolower($itemno));
        $isNO = true;

        $items = \NavisionItem::find_by_sql("SELECT * FROM navision_item WHERE language_id = 1 && no LIKE '".$itemno."' && deleted IS NULL");
        if(count($items) > 0) {
            $isNO = false;
        }


        $this->isNOVarenrCache[trim(strtolower($itemno))] = $isNO;
        return $isNO;

    }

    private function hasNOVarenr($itemno1,$itemno2,$itemno3,$itemno4,$itemno5) {

        if(trim($itemno1) != "") {
            if($this->isNOVarenr($itemno1)) {
                return true;
            }
        }

        if(trim($itemno2) != "") {
            if($this->isNOVarenr($itemno2)) {
                return true;
            }
        }

        if(trim($itemno3) != "") {
            if($this->isNOVarenr($itemno3)) {
                return true;
            }
        }

        if(trim($itemno4) != "") {
            if($this->isNOVarenr($itemno4)) {
                return true;
            }
        }

        if(trim($itemno5) != "") {
            if($this->isNOVarenr($itemno5)) {
                return true;
            }
        }

        return false;

    }

    private function hasDKVarenr($itemno1,$itemno2,$itemno3,$itemno4,$itemno5) {

        if(trim($itemno1) != "") {
            if(!$this->isNOVarenr($itemno1)) {
                return true;
            }
        }

        if(trim($itemno2) != "") {
            if(!$this->isNOVarenr($itemno2)) {
                return true;
            }
        }

        if(trim($itemno3) != "") {
            if(!$this->isNOVarenr($itemno3)) {
                return true;
            }
        }

        if(trim($itemno4) != "") {
            if(!$this->isNOVarenr($itemno4)) {
                return true;
            }
        }

        if(trim($itemno5) != "") {
            if(!$this->isNOVarenr($itemno5)) {
                return true;
            }
        }

        return false;

    }


    private function noFilter($list,$hasNo=null,$hasDK=null)
    {

        $newList = array();

        foreach($list as $row)
        {

            $addToList = true;
            $hasNOItems = $this->hasNOVarenr($row->itemno,$row->itemno2,$row->itemno3,$row->itemno4,$row->itemno5);
            $hasDKItems = $this->hasDKVarenr($row->itemno,$row->itemno2,$row->itemno3,$row->itemno4,$row->itemno5);

            if($hasNo !== null) {

                if($hasNo == true) {
                    if(!$hasNOItems) $addToList = false;
                } else {
                    if($hasNOItems) $addToList = false;
                }

            }
            if($hasDK !== null) {

                if($hasDK == true) {
                    if(!$hasDKItems) $addToList = false;
                } else {
                    if($hasDKItems) $addToList = false;
                }

            }

            if($addToList) {
                $newList[] = $row;
            }

        }

        return $newList;
    }

    private function showEarlyOrderCounterTable()
    {

        $sql = "SELECT cardshop_settings.language_code, order_state, shipment.handler, count(shipment.id) as shipmentcount, sum(shipment.quantity+quantity2+quantity3+quantity4+quantity5) as totalquantity, shipment_state, shipment.force_syncnow FROM `shipment`, company_order, cardshop_settings WHERE shipment.companyorder_id = company_order.id && company_order.shop_id = cardshop_settings.shop_id && `shipment_type` LIKE 'earlyorder' group by cardshop_settings.language_code, order_state, shipment.handler, shipment_state, shipment.force_syncnow ORDER BY `cardshop_settings`.`language_code` ASC";
        $earlyStates = \Shipment::find_by_sql($sql);

        $countMap = array();

        foreach($earlyStates as $state)
        {
            $type = "UNKNOWN";

            if($state->shipment_state == 2) {
                $type = "SENT";
            }

            else if($state->shipment_state == 0) {
                $type = "NOTREADY";
            }

            else if($state->shipment_state == 1) {
                if($state->force_syncnow == 1) {
                    $type = "INQUEUE";
                } else {
                    $type = "WAITING";
                }

            }

            else if($state->shipment_state == 4) {
                $type = "BLOCKED";
            }



            if(!isset($countMap[$state->language_code])) {
                $countMap[$state->language_code] = array("total" => 0);
            }
            if(!isset($countMap[$state->language_code][$type])) {
                $countMap[$state->language_code][$type] = 0;
            }

            $countMap[$state->language_code][$type] += $state->shipmentcount;
            $countMap[$state->language_code]["total"] += $state->shipmentcount;
        }


        ?><style>

        table td {
            padding: 10px; border: 1px solid #aaaaaa;
        }

        </style><?php

        ?><table>
            <tr>
                <td>Land</td>
                <td>Ikke klar</td>
                <td>Venter</td>
                <td>I kø</td>
                <td>Sendt</td>
                <td>Blokkeret</td>
                <td>Total</td>
            </tr>
        <?php

            foreach($countMap as $lang => $counters) {
                echo "<tr>";
                echo "<td>".$this->langToString($lang)."</td>";
                echo "<td>".($counters["NOTREADY"]??0)."</td>";
                echo "<td>".($counters["WAITING"]??0)."</td>";
                echo "<td>".($counters["INQUEUE"]??0)."</td>";
                echo "<td>".($counters["SENT"]??0)."</td>";
                echo "<td>".($counters["BLOCKED"]??0)."</td>";
                echo "<td>".($counters["total"]??0)."</td>";
                echo "</tr>";
            }

        ?>
        </table><?php

    }

    private function langToString($lang) {
        if($lang == 1) return "Danmark";
        if($lang == 4) return "Norge";
        if($lang == 5) return "Sverige";
        else return "Ukendt";
    }

    private function getWaitingEarlyOrdersByCountry($lang,$removeBlockedItems=true) {

        $blockedItems = $this->getHoldBackItems($lang);

        // Convert all blockedItems to lowercase and trim
        $blockedItems = array_map('strtolower', $blockedItems);
        $blockedItems = array_map('trim', $blockedItems);

        $sql = "select shipment.*
from shipment, company_order, cardshop_settings, company 
where 
shipment.companyorder_id = company_order.id && company_order.shop_id = cardshop_settings.shop_id && company.id = company_order.company_id &&
cardshop_settings.language_code = ".intval($lang)." && shipment.shipment_type = 'earlyorder' && shipment.shipment_state = 1 && shipment.force_syncnow = 0 && company_order.shipment_on_hold = 0 && company_order.nav_lastsync <= NOW() - INTERVAL 48 HOUR &&
(company_order.prepayment = 0 or company_order.nav_paid = 1 or company_order.allow_delivery = 1 or company.allow_delivery = 1) 
ORDER BY `ItemNo` ASC;";

        $shipmentList = \Shipment::find_by_sql($sql);

        if($removeBlockedItems == false) {
            return $shipmentList;
        }

        $newList =array();

        foreach ($shipmentList as $shipment) {

            $isBlocked = false;

            if(in_array($shipment->itemno, $blockedItems)) {
                $isBlocked = true;
            } else if(in_array($shipment->itemno2, $blockedItems)) {
                $isBlocked = true;
            } else if(in_array($shipment->itemno3, $blockedItems)) {
                $isBlocked = true;
            } else if(in_array($shipment->itemno4, $blockedItems)) {
                $isBlocked = true;
            } else if(in_array($shipment->itemno5, $blockedItems)) {
                $isBlocked = true;
            }

            if(!$isBlocked) {
                $newList[] = $shipment;
            }

        }

        return $newList;


    }

}