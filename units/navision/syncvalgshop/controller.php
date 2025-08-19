<?php

namespace GFUnit\navision\syncvalgshop;
use GFBiz\units\UnitController;

class Controller extends UnitController
{

    public function __construct()
    {
        parent::__construct(__FILE__);
    }

    public function test() {

        $vsSync = new ValgshopSync();
        $vsSync->test();

    }
    

}