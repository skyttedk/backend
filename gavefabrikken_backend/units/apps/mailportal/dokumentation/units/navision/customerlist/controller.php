<?php

namespace GFUnit\navision\customerlist;
use GFBiz\units\UnitController;

class Controller extends UnitController
{

    public function __construct()
    {
        parent::__construct(__FILE__);

    }

    public static function outputView($viewName="view")
    {
        $controller = new Controller();
        $controller->view($viewName);
    }

    /**
     * SERVICES
     */

    public function searchname($language_code=0,$name="") {

        $customerListWS = new \GFCommon\Model\Navision\CustomerWS(intval($language_code));
        $customerList = $customerListWS->searchByName($name,false,100);

        $responseList = array();
        foreach($customerList as $customer) {
            $responseList[] = $customer->frontendMapper();
        }

        echo json_encode(array("status" => 1,"customers" => $responseList));

    }

    public function searchcvr($language_code=0,$cvr="") {

        $customerListWS = new \GFCommon\Model\Navision\CustomerWS(intval($language_code));
        $customerList = $customerListWS->searchByCVR($cvr,100);

        $responseList = array();
        foreach($customerList as $customer) {
            $responseList[] = $customer->frontendMapper();
        }

        echo json_encode(array("status" => 1,"customers" => $responseList));

    }

    public function searchean($language_code=0,$ean="")
    {
        $customerListWS = new \GFCommon\Model\Navision\CustomerWS(intval($language_code));
        $customerList = $customerListWS->searchByEAN($ean,100);

        $responseList = array();
        foreach($customerList as $customer) {
            $responseList[] = $customer->frontendMapper();
        }

        echo json_encode(array("status" => 1,"customers" => $responseList));
    }




}