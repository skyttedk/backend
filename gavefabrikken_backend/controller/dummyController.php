<?php

class dummyController Extends baseController
{
    public function Index()
    {
        header("Location: https://www.gavefabrikken.dk/");
    }

    public function hello() {
        echo "Hello World!";
    }

    public function navcustomerlookup()
    {

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

        // Iff navdebitor no is odd / even
        if($navdebitorno % 2 == 0) {
            echo '{"status":1,"customer":{"customer_no":"'.$navdebitorno.'","name":"Test Company","address":"Tester Road 1231","address2":"","postcode":"5000","city":"Odense","country":"DK","cvr":"1324578","ean":"","contact":"John Smith","phone":"15784785","email":"test@gavefabrikken.dk","blocked":"_blank_","credit_limit":"0","invoice_email":"faktura@gavefabrikken.dk","bill_to_email":"bill@gavefabrikken.dk","currency_code":"","gln":"","salesperson_code":"GS","last_modified":"","payment_method_code":"EANDEBITOR"}}';
        } else {
            echo json_encode(array("status"=> 0,"message" => "Could not find debitor no in navision."));
            exit();
        }

    }

}