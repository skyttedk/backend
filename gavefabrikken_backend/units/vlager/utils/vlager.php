<?php

namespace GFUnit\vlager\utils;
use GFBiz\units\UnitController;

class VLager
{


    public static function getLagerList() {
        return \VLager::find_by_sql("SELECT * FROM vlager");
    }

    public static function updateLagerItem($vlagerid,$itemno,$quantityDelta,$description,$vlagerincominglineid=0,$shipmentid=0) {

        $itemList = \VLagerItem::find_by_sql("SELECT * FROM vlager_item WHERE itemno LIKE '".trim($itemno)."' AND vlager_id = ".intval($vlagerid));

        // Find item
        if(count($itemList) == 0) {
            $item = new \VLagerItem();
            $item->vlager_id = intval($vlagerid);
            $item->itemno = $itemno;
            $item->quantity_available = 0;
            $item->quantity_incoming = 0;
            $item->quantity_outgoing = 0;
            $item->created = new \DateTime();
            $item->updated = new \DateTime();
        } else {
            $item = \VLagerItem::find($itemList[0]->id);
        }

        // Update available
        $item->quantity_available += intval($quantityDelta);
        $item->updated = new \DateTime();
        $item->save();

        // Add to log
        $log = new \VLagerItemLog();
        $log->vlager_id = $vlagerid;
        $log->vlager_item_id = $item->id;
        $log->shipment_id = $shipmentid;
        $log->vlager_incoming_line_id = $vlagerincominglineid;
        $log->quantity = $quantityDelta;
        $log->balance = $item->quantity_available;
        $log->description = $description;
        $log->log_time = new \DateTime();
        $log->save();

        if($item->quantity_available < 0) {
            return false;
        } else {
            return true;
        }

    }


}