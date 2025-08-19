<?php

namespace GFUnit\reservation\balancecompare;

use GFBiz\units\UnitController;
use GFCommon\Model\Navision\ReservationWS;


class balancenav
{

    public $date;
    public $location;
    public $newbalance;
    public $adjustment;
    public $note;

    public $id;
    public $itemobj;

    public function __construct($itemobj) {

        $this->itemobj = $itemobj;

        // Convert yyy-mm-dd to unixtimestamp
        $this->date = strtotime($itemobj->getEntryDate());

        $this->id = $itemobj->getEntryNo();
        $this->location = $itemobj->getLocationCode();
        $this->newbalance = $itemobj->getNewBalance();
        $this->adjustment = $itemobj->getAdjustment();
        $this->note = $itemobj->getNote();

    }

    public function getBalanceKey1() {
        return $this->itemobj->getItemNo()."-".$this->location."-".$this->adjustment."-".date("Ymd",$this->date)."-".$this->id;
    }

    public function getBalanceKey2() {
        return $this->itemobj->getItemNo()."-".$this->location."-".$this->adjustment."-".date("Ymd",$this->date);
    }

}

class balancelocal
{
    public $location;
    public $shopid;
    public $created;
    public $balance;
    public $delta;
    public $note;
    public $id;
    public $itemobj;

    public function __construct($itemobj) {

        $this->itemobj = $itemobj;

        $this->id = $itemobj->id;
        $this->location = $itemobj->location;
        $this->shopid = $itemobj->shop_id;
        $this->created = $itemobj->created->format('U');
        $this->balance = $itemobj->balance;
        $this->delta = $itemobj->delta;
        $this->note = $itemobj->notes;

    }

    public function getBalanceKey1() {
        return $this->itemobj->itemno."-".$this->location."-".$this->delta."-".date("Ymd",$this->created)."-".$this->id;
    }

    public function getBalanceKey2() {
        return $this->itemobj->itemno."-".$this->location."-".$this->delta."-".date("Ymd",$this->created);
    }

}

class balancerow {


    public $local;
    public $nav;

    public function __construct($local,$nav) {
        $this->local = $local;
        $this->nav = $nav;
    }


}

class BalanceCheck extends UnitController
{

    // Define properties
    private $itemno;
    private $language;
    private $location;

    private $error = "";


    /**
     * @var balancelocal[]
     */
    private $localList;

    /**
     * @var balancenav[]
     */
    private $navlist;

    /**
     * @var balancerow[]
     */
    private $mergedrows = array();

    public function __construct($itemno,$language,$location)
    {
        // Set properties
        $this->itemno = trim($itemno);
        $this->language = intval($language);
        $this->location = $location;

        if($this->itemno == "") {
            $this->setError("Item number is missing");
            return;
        }

        if(!in_array($this->language,array(1,4,5))) {
            $this->setError("Language is missing");
            return;
        }

        $this->loadLocalData();
        $this->loadNavisionData();
        $this->mergeRows2();
        $this->validateMergedRows();
    }

    private function setError($errorMessage) {

    }

    private function loadLocalData() {

        $navisionLogList = \NavisionReservationLog::find_by_sql("SELECT * FROM navision_reservation_log WHERE itemno LIKE '{$this->itemno}' ".($this->location != "" ? "AND location = '{$this->location}'" : "")." AND language_id = ".intval($this->language)." ORDER BY created ASC");

        if(is_array($navisionLogList)) {
            foreach($navisionLogList as $navisionlog) {
                $this->localList[] = new balancelocal($navisionlog);
            }
        }

        //echo "FOUND ".count($navisionLogList)." LOCAL ROWS!";
        //echo "<pre>".print_r($navisionLogList[0],true)."</pre>";




    }

    private function loadNavisionData() {

        $navisionClient = new ReservationWS($this->language);
        $navisionItems = $navisionClient->getByItemNo($this->itemno,5000);

        if(is_array($navisionItems)) {
            foreach($navisionItems as $navisionitem) {

                if($this->location == "" || $this->location == $navisionitem->getLocationCode()) {
                    $this->navlist[] = new balancenav($navisionitem);
                } else {
                    echo "FILTER AWAY!";
                }
                
            }
        }

        //echo "FOUND ".count($navisionItems)." NAVISION ROWS!";
        //echo "<pre>".print_r($navisionItems[0],true)."</pre>";


    }

    private function mergeData() {
        $navIndex1 = [];
        $navIndex2 = [];
        $usedNavIds = [];

        // Indekser balancenav objekterne efter deres nøgle1 og nøgle2 for hurtigere søgning
        foreach ($this->navlist as $navItem) {
            $navIndex1[$navItem->getBalanceKey1()] = $navItem;
            $navIndex2[$navItem->getBalanceKey2()] = $navItem;
        }

        // Første forsøg på at matche på getBalanceKey1
        foreach ($this->localList as $localItem) {
            $key1 = $localItem->getBalanceKey1();
            if (isset($navIndex1[$key1]) && !isset($usedNavIds[$navIndex1[$key1]->id])) {
                $this->mergedrows[] = new balancerow($localItem, $navIndex1[$key1]);
                $usedNavIds[$navIndex1[$key1]->id] = true;
                unset($navIndex1[$key1]);
            }
        }

        // Andet forsøg på at matche på getBalanceKey2
        foreach ($this->localList as $localItem) {
            $key2 = $localItem->getBalanceKey2();
            if (isset($navIndex2[$key2]) && !isset($usedNavIds[$navIndex2[$key2]->id])) {
                $this->mergedrows[] = new balancerow($localItem, $navIndex2[$key2]);
                $usedNavIds[$navIndex2[$key2]->id] = true;
                unset($navIndex2[$key2]);
            }
        }

        // Tilføj eventuelle tilbageværende nav objekter som ikke har et match
        foreach ($navIndex1 as $navItem) {
            if (!isset($usedNavIds[$navItem->id])) {
                $this->mergedrows[] = new balancerow(null, $navItem);
                $usedNavIds[$navItem->id] = true;
            }
        }

        foreach ($navIndex2 as $navItem) {
            if (!isset($usedNavIds[$navItem->id])) {
                $this->mergedrows[] = new balancerow(null, $navItem);
                $usedNavIds[$navItem->id] = true;
            }
        }


        // Sorter mergedrows efter dato, ældste først
        usort($this->mergedrows, function ($a, $b) {
            // Sammenlign først på dato
            $aDate = $a->local ? $a->local->created : $a->nav->date;
            $bDate = $b->local ? $b->local->created : $b->nav->date;

            if ($aDate != $bDate) {
                return $aDate <=> $bDate;
            }

            // Hvis datoerne er ens, sammenlign på nav->id
            $aNavId = $a->nav ? $a->nav->id : PHP_INT_MAX;
            $bNavId = $b->nav ? $b->nav->id : PHP_INT_MAX;

            return $aNavId <=> $bNavId;
        });



    }

    private function mergeRows2() {
        $mergedRows = [];
        $localMatched = [];

        // Opret balancerow objekter med kun nav items
        foreach ($this->navlist as $navItem) {
            $mergedRows[] = new balancerow(null, $navItem);
        }

        // Første forsøg på at matche local items på getBalanceKey1
        foreach ($this->localList as $localItem) {
            $key1 = $localItem->getBalanceKey1();
            foreach ($mergedRows as $row) {
                if ($row->nav && $row->nav->getBalanceKey1() === $key1) {
                    $row->local = $localItem;
                    $localMatched[$localItem->id] = true;
                    break;
                }
            }
        }

        // Andet forsøg på at matche resterende local items på getBalanceKey2
        foreach ($this->localList as $localItem) {
            if (isset($localMatched[$localItem->id])) {
                continue; // Dette local item er allerede matchet
            }
            $key2 = $localItem->getBalanceKey2();
            foreach ($mergedRows as $row) {
                if ($row->nav && $row->nav->getBalanceKey2() === $key2) {
                    $row->local = $localItem;
                    $localMatched[$localItem->id] = true;
                    break;
                }
            }
        }

        // Tilføj resterende local items som nye balancerow objekter
        foreach ($this->localList as $localItem) {
            if (!isset($localMatched[$localItem->id])) {
                // Find den sidste balancerow på samme dato eller opret en ny
                $date = $localItem->created;
                $lastRowOnDate = null;
                foreach ($mergedRows as $row) {
                    if ($row->nav && $row->nav->date === $date) {
                        $lastRowOnDate = $row;
                    }
                }
                if ($lastRowOnDate) {
                    // Indsæt efter den sidste balancerow på samme dato
                    $position = array_search($lastRowOnDate, $mergedRows);
                    array_splice($mergedRows, $position + 1, 0, [new balancerow($localItem, null)]);
                } else {
                    // Ellers tilføj som en ny balancerow til slutningen
                    $mergedRows[] = new balancerow($localItem, null);
                }
            }
        }

        // Gem den opdaterede mergedrows liste
        $this->mergedrows = $mergedRows;
    }



    private function validateMergedRows() {
        $navIds = [];
        $localIds = [];
        $prevNavDate = null;
        $prevNavId = null;

        foreach ($this->mergedrows as $index => $row) {
            // Tjek for duplikat nav->id
            if (isset($row->nav) && isset($navIds[$row->nav->id])) {
                echo "Fejl: nav->id '{$row->nav->id}' er brugt mere end én gang.\n";
                exit;
            }

            // Tjek for duplikat local->id
            if (isset($row->local) && isset($localIds[$row->local->id])) {
                echo "Fejl: local->id '{$row->local->id}' er brugt mere end én gang.\n";
                exit;
            }

            // Tjek for nav->date ældre end den forrige
            if (isset($row->nav) && $prevNavDate !== null && $row->nav->date < $prevNavDate) {
                echo "Fejl: nav->date '{$row->nav->date}' er ældre end den forrige række.\n";
                exit;
            }

            // Tjek for nav->id mindre end den forrige
            if (isset($row->nav) && $prevNavId !== null && $row->nav->id < $prevNavId) {
                echo "Fejl: nav->id '{$row->nav->id}' er mindre end den forrige række.\n";
                exit;
            }

            // Opdater hjælpearrays og tidligere værdier
            if (isset($row->nav)) {
                $navIds[$row->nav->id] = true;
                $prevNavDate = $row->nav->date;
                $prevNavId = $row->nav->id;
            }

            if (isset($row->local)) {
                $localIds[$row->local->id] = true;
            }
        }

        echo "Alle tjek er bestået.\n";
    }


    private $shopNames = array();
    private function getShopName($shopid) {

        if(isset($this->shopNames[intval($shopid)])) {
            return $this->shopNames[intval($shopid)];
        } else {
            $shop = \Shop::find(intval($shopid));
            if($shop != null) {
                $this->shopNames[intval($shopid)] = $shop->name;
                return $shop->name;
            } else {
                return "Unknown shop";
            }
        }

    }

    private $navisionTypeSum = array();
    private $localTypeSum = array();

    private function addNavSum($type,$key,$delta) {

        if(!isset($this->navisionTypeSum[$type])) {
            $this->navisionTypeSum[$type] = array();
        }

        if(!isset($this->navisionTypeSum[$type][$key])) {
            $this->navisionTypeSum[$type][$key] = 0;
        }

        $this->navisionTypeSum[$type][$key] += $delta;

    }

    private function addLocalSum($type,$key,$delta) {

        if(!isset($this->localTypeSum[$type])) {
            $this->localTypeSum[$type] = array();
        }

        if(!isset($this->localTypeSum[$type][$key])) {
            $this->localTypeSum[$type][$key] = 0;
        }

        $this->localTypeSum[$type][$key] += $delta;

    }

    public function outputBalance() {

        echo count($this->mergedrows)." output now!";

        $balance = 0;

        $navDeltaSum = 0;
        $localDeltaSum = 0;

        foreach($this->mergedrows as $i => $row) {

            if($row->nav != null) {
                $balance += $row->nav->adjustment;
                $navDeltaSum += $row->nav->adjustment;
            }

            if($row->local != null) {
                $balance -= $row->local->delta;
                $localDeltaSum += $row->local->delta;
            }

            echo "<tr>";

            if($row->nav == null) {
                echo "<td style='background: red;'></td>";
                echo "<td style='background: red;'></td>";
                echo "<td style='background: red;'></td>";
                echo "<td style='background: red;'></td>";
                echo "<td style='background: red;'></td>";
                echo "<td style='background: red;'></td>";
                echo "<td style='background: red;'></td>";
            } else {
                echo "<td>".$row->nav->id."</td>";
                echo "<td>".$row->nav->location."</td>";
                echo "<td>".date("Y-m-d",$row->nav->date)."</td>";
                echo "<td>".$row->nav->adjustment."</td>";
                echo "<td>".$navDeltaSum."</td>";
                echo "<td>".$row->nav->newbalance."</td>";
                echo "<td>".$row->nav->note."</td>";

                // Add for location
                $this->addNavSum("location",$row->nav->location,$row->nav->adjustment);
                $this->addNavSum("date",date("Y-m-d",$row->nav->date), $row->nav->adjustment);
                $this->addNavSum("delta",$row->nav->adjustment,1);

                $shopsplit = explode(":",$row->nav->note);
                if(count($shopsplit) == 0) {
                    $shopname = "Empty";
                } else {
                    $shopname = $shopsplit[0];
                }

                $this->addNavSum("shop",$shopname,$row->nav->adjustment);

            }

            echo "<td>".$balance."</td>";

            if($row->local == null) {
                echo "<td style='background: red;'></td>";
                echo "<td style='background: red;'></td>";
                echo "<td style='background: red;'></td>";
                echo "<td style='background: red;'></td>";
                echo "<td style='background: red;'></td>";
                echo "<td style='background: red;'></td>";
                echo "<td style='background: red;'></td>";
                echo "<td style='background: red;'></td>";
            } else {
                echo "<td>".$row->local->location."</td>";
                echo "<td>".date("Y-m-d",$row->local->created)."</td>";
                echo "<td>".$row->local->delta."</td>";
                echo "<td>".$localDeltaSum."</td>";
                echo "<td>".$row->local->balance."</td>";

                echo "<td>".$row->local->note."</td>";
                echo "<td>".$this->getShopName($row->local->shopid)."</td>";
                echo "<td>".$row->local->id."</td>";

                // Add for location
                $this->addLocalSum("location",$row->local->location,$row->local->delta);
                $this->addLocalSum("date",date("Y-m-d",$row->local->created), $row->local->delta);
                $this->addLocalSum("delta",$row->local->delta,1);
                $this->addLocalSum("shop",$this->getShopName($row->local->shopid),$row->local->delta);
            }

            echo "</tr>";

        }

        // Output sum
        /*
        echo "<tr>";
        echo "<td colspan='3'>Navision delta sum</td>";
        echo "<td>".$navDeltaSum."</td>";
        echo "<td>".$balance."</td>";
        echo "<td colspan='3'>Local delta sum</td>";
        echo "<td>".$localDeltaSum."</td>";
*/
        // Get unique type keys
        $types = array_merge(array_keys($this->navisionTypeSum),array_keys($this->localTypeSum));
        $types = array_unique($types);



        foreach($types as $type) {

            echo "<tr style='background: #A0A0A0;'>";
            echo "<td colspan='3'>".$type."</td>";
            echo "<td colspan='3'>".(isset($this->navisionTypeSum[$type]) ? array_sum($this->navisionTypeSum[$type]) : 0)."</td>";
            echo "<td colspan='3'>".(isset($this->localTypeSum[$type]) ? array_sum($this->localTypeSum[$type]) : 0)."</td>";
            echo "<td>Diff</td>";
            echo "</tr>";

            // Get unique keys for this type
            $keys = array_merge(isset($this->navisionTypeSum[$type]) ? array_keys($this->navisionTypeSum[$type]) : array(),isset($this->localTypeSum[$type]) ? array_keys($this->localTypeSum[$type]) : array());
            $keys = array_unique($keys);

            foreach($keys as $key) {

                $navCount = isset($this->navisionTypeSum[$type][$key]) ? $this->navisionTypeSum[$type][$key] : 0;
                $localCount = isset($this->localTypeSum[$type][$key]) ? $this->localTypeSum[$type][$key] : 0;

                echo "<tr>";
                echo "<td colspan='3'>".$key."</td>";
                echo "<td colspan='3'>".$navCount."</td>";
                echo "<td colspan='3'>".$localCount."</td>";
                echo "<td style='background: ".($navCount-$localCount == 0 ? "green" : "red").";'>".($navCount-$localCount)."</td>";
                echo "</tr>";
            }

        }


    }

}