<?php

namespace GFUnit\navision\syncexternalshipment;
use GFBiz\units\UnitController;
use GFUnit\navision\syncshipment\ShipmentSync;

class Controller extends UnitController
{

    public function __construct()
    {
        parent::__construct(__FILE__);
    }


    public function runcheck($output=1)
    {

    
        // Do not run navision jobs in this period
        if(in_array(intval(date("H")),array(3,4,5))) {
            exit();
        }

        $syncModel = new ExternalShipmentSync();
        $syncModel->runcheck($output == 1);

    }



}