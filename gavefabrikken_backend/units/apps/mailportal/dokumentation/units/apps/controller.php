<?php

namespace GFUnit\apps;

use GFBiz\units\UnitController;

class Controller extends UnitController
{

    public function __construct()
    {
        parent::__construct(__FILE__);

    }

    public function test()
    {
        echo "test";
    }

}