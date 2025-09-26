<?php

namespace GFUnit\pim\gavevalg;

use GFBiz\units\UnitController;



class Controller extends UnitController
{
    public function __construct()
    {
        parent::__construct(__FILE__);


    }
    public function syncToPim ()
    {

        $p = new present;
       $res = $p->syncSingle(113681);
       var_dump($res);
    }
}