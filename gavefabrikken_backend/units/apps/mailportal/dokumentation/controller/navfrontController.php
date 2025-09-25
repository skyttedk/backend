<?php

class navfrontController Extends baseController {

    public function index()
    {

    }

    public function checkvarenr()
    {
        $client = new \GFCommon\Model\Navision\NavMiscService();
        $itemNoValid = $client->IsValidItemNo($_POST["varenr"]);
        response::success(json_encode(array("valid" => $itemNoValid)));
    }

}