<?php

namespace GFUnit\test\example;

use GFBiz\units\UnitController;

class Controller extends UnitController
{

    public function __construct()
    {
         parent::__construct(__FILE__);
    }

    /**
     * SERVICES
     */

    public function addnumbers($number1,$number2) {
        echo intval($number1)+intval($number2);
    }

    public function testmethod($number1,$number2) {
        echo json_encode(array("arg1" => $number1,"arg2" => $number2,"post" => $_POST));
    }

}