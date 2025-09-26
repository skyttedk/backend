<?php

namespace GFUnit\reservation\balancecompare;

use GFBiz\units\UnitController;
use GFCommon\Model\Navision\ReservationWS;



class SearchCS extends UnitController
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


    }

    public function output() {


        $navisionLogList = \NavisionReservationLog::find_by_sql("SELECT * FROM navision_reservation_log WHERE itemno LIKE '{$this->itemno}' ".($this->location != "" ? "AND location = '{$this->location}'" : "")." AND language_id = ".intval($this->language)." ORDER BY created ASC");
        $localList = array();

        if(is_array($navisionLogList)) {
            foreach($navisionLogList as $navisionlog) {
                $localList[] = $navisionlog;
            }
        }

        $balanceSum = 0;

        if(is_array($localList)) {
            foreach($localList as $localItem) {

                if($this->location == "" || $this->location == $localItem->location) {

                    $balanceSum += $localItem->delta;

                    echo "<tr>
                        <td>".$localItem->id."</td>
                        <td>".$localItem->location."</td>
                        <td>".$localItem->created->format("Y-m-d")."</td>
                        <td>".$localItem->delta."</td>
                        <td style='".($localItem->balance != $balanceSum ? "background: yellow" : "")."'>".$balanceSum."</td>
                        <td style='".($localItem->balance != $balanceSum ? "background: yellow" : "")."'>".$localItem->balance."</td>
                        <td>".$localItem->notes."</td>
                        <td colspan='8'></td>
                    </tr>";

                }

            }
        }

    }

    private function setError($errorMessage) {

    }



}