<?php

namespace GFUnit\privatedelivery\sedsv;


class CostReport
{

    private $helper;

    public function __construct()
    {
        $this->helper = new Helpers();
    }

    public function dispatch()
    {


        // Load lager
        $dsvLager = DSVLager::getLagerQuantityMap();


        $itemList = array();
        foreach($dsvLager as $itemNo => $quantity) {
            $itemList[] = $itemNo;
        }

        // Get shipments missing
        $sql = "SELECT itemno, count(id) as itemcount FROM `shipment` WHERE `handler` LIKE 'mydsv' && shipment_state = 1 GROUP BY itemno ORDER BY itemcount DESC";
        $shipmentList = \Shipment::find_by_sql($sql);

        $waitingItems = array();

        $addToWaiting = array(
            "15817" =>	19,
            "16792" =>	1,
            "200134" =>	18,
            "20671" =>	1,
            "20673" =>	1,
            "210114" =>	2,
            "210171" =>	2,
            "210193" =>	1,
            "21569" =>	2,
            "220125" =>	7,
            "220138" =>	2,
            "220146" =>	2,
            "220152" =>	5,
            "220159" =>	8,
            "25858" =>	2,
            "9006" => 4
        );

        foreach($addToWaiting as $itemNo => $itemCount) {
            $waitingItems[$itemNo] = $itemCount;
        }

        foreach($shipmentList as $shipment) {

            $itemNo = trim(mb_strtolower($shipment->itemno));
            $itemCount = $shipment->itemcount;

            $bomItemList = \NavisionBomItem::find_by_sql("select * from navision_bomitem where language_id = 1 && parent_item_no like '".$itemNo."' && deleted is null");

            if(count($bomItemList) > 0) {
                foreach($bomItemList as $bomItem) {

                    $bomItemNo = trim(mb_strtolower($bomItem->no));
                    $bomQuantity = $bomItem->quantity_per;

                    if(!in_array($bomItemNo,$itemList)) {
                        $itemList[] = $bomItemNo;
                    }

                    if(!isset($waitingItems[$bomItemNo])) {
                        $waitingItems[$bomItemNo] = 0;
                    }
                    $waitingItems[$bomItemNo] += $itemCount*$bomQuantity;

                }
            } else {

                if(!in_array($itemNo,$itemList)) {
                    $itemList[] = $itemNo;
                }

                if(!isset($waitingItems[$itemNo])) {
                    $waitingItems[$itemNo] = 0;
                }
                $waitingItems[$itemNo] += $itemCount;

            }

        }



        sort($itemList);

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="dsv-costrapport-'.date("Ymd").'.csv"');
        header('Cache-Control: max-age=0');


        echo utf8_decode("Varenr;Varenavn;DSV Lager;Venter på afsendelse;Difference (negativ mangler ved DSV);Kostpris;Total værdi ved DSV\r\n");

        foreach($itemList as $itemNo) {

            $itemData = \NavisionItem::find_by_sql("select * from navision_item where language_id = 1 && no like '".$itemNo."' && deleted is null");
            if(count($itemData) == 0) $itemData = null;
            else $itemData = $itemData[0];

            $itemName = "NOT FOUND IN NAV";
            $costPrice = "N/A";
            $dsvCount = 0;
            $waiting = 0;

            if($itemData != null) {
                $itemName = $itemData->description;
                $costPrice = $itemData->unit_cost;
            }

            if(isset($dsvLager[$itemNo])) $dsvCount = intval($dsvLager[$itemNo]);
            if(isset($waitingItems[$itemNo])) $waiting = intval($waitingItems[$itemNo]);

            $dif = $dsvCount-$waiting;

            echo utf8_decode($itemNo.";".$itemName.";".$dsvCount.";".$waiting.";".$dif.";".number_format($costPrice,2,",",".").";".number_format(($dsvCount*$costPrice),2,",",".")."\r\n");

        }

    }

}