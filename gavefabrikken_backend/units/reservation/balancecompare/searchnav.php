<?php

namespace GFUnit\reservation\balancecompare;

use GFBiz\units\UnitController;
use GFCommon\Model\Navision\ReservationWS;



class SearchNav extends UnitController
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


        $navisionClient = new ReservationWS($this->language);
        $navisionItems = $navisionClient->getByItemNo($this->itemno,5000);

        $balanceSum = 0;

        if(is_array($navisionItems)) {
            foreach($navisionItems as $navisionitem) {

                if($this->location == "" || $this->location == $navisionitem->getLocationCode()) {

                    $balanceSum += $navisionitem->getAdjustment();

                    echo "<tr>
                        <td>".$navisionitem->getEntryNo()."</td>
                        <td>".$navisionitem->getLocationCode()."</td>
                        <td>".$navisionitem->getEntryDate()."</td>
                        <td>".$navisionitem->getAdjustment()."</td>
                        <td style='".($navisionitem->getNewBalance() != $balanceSum ? "background: yellow" : "")."'>".$balanceSum."</td>
                        <td style='".($navisionitem->getNewBalance() != $balanceSum ? "background: yellow" : "")."'>".$navisionitem->getNewBalance()."</td>
                        <td>".$navisionitem->getNote()."</td>
                        <td colspan='8'></td>
                    </tr>";

                }

            }
        }

    }

    private function setError($errorMessage) {

    }



}