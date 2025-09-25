<?php

namespace GFUnit\valgshop\services;
use GFBiz\units\UnitController;
use GFCommon\Model\Navision\CustomerWS;

class Controller extends UnitController
{

    public function __construct()
    {
        parent::__construct(__FILE__);
    }

    public function index() {

        echo json_encode(array("status"=> 0,"message" => "No endpoint here"));
    }

    public function navcustomerlookup() {


        $validLanguages = array(1,4,5);

        if(isset($_POST["lang"])) {
            $lang = intval($_POST["lang"]);
        } else if(isset($_GET["lang"])) {
            $lang = intval($_GET["lang"]);
        } else {
            echo json_encode(array("status"=> 0,"message" => "No language provided."));
            exit();
        }

        if(!in_array($lang, $validLanguages)) {
            echo json_encode(array("status"=> 0,"message" => "Invalid language."));
            exit();
        }

        if(isset($_POST["debitorno"])) {
            $navdebitorno = intval($_POST["debitorno"]);
        } else if(isset($_GET["debitorno"])) {
            $navdebitorno = intval($_GET["debitorno"]);
        } else {
            echo json_encode(array("status"=> 0,"message" => "No debitor number provided."));
            exit();
        }

        if($navdebitorno <= 0) {
            echo json_encode(array("status"=> 0,"message" => "Invalid debitor number."));
            exit();
        }

       

        // Check on nav
        try {

            $customerClient = new CustomerWS($lang);
            $customer = $customerClient->getByCustomerNo($navdebitorno);

        } catch (\Exception $e) {
            echo json_encode(array("status" => 0, "message" => "Error in navision lookup, please try again later."));
            return;
        }

        if($customer == null) {
            echo json_encode(array("status" => 0, "message" => "Could not find debitor no in navision."));
            return;
        }

        echo json_encode(array("status" => 1,"customer" => $customer->frontendMapper()));
    }


}