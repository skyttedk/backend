<?php

Class siteserviceController Extends baseController
{

    /**
     * Unit controller sub-controller
     * Checks for unit and performs actions on the controller
     */

    public function index($arg1="",$arg2="")
    {
        // Get path info
        $rt = isset($_GET["rt"]) ? $_GET["rt"] : "";
        $rtSplit = explode("/",$rt);

        // Extract unit folder and name
        array_shift($rtSplit);

        // Check if exists
        $className = "GFBiz\\Siteservice\\Controller";
        if(class_exists($className) == false) {
            echo "Unit does not exist: ".$className;
            return;
        }

        // Get action
        $actionName = array_shift($rtSplit);
        if(trimgf($actionName) == "") $actionName = "index";

        // Create controller class
        $unitController = new $className();



        // Check action
        if(!method_exists($unitController,$actionName)) {
            http_response_code(404);
            echo "Invalid unit path: ".$actionName;
            return;
        }

        $unitController->authorize();

        //Prepare arguments
        $param1 = null;
        $param2 = null;
        $param3 = null;
        $param4 = null;
        $param5 = null;
        for($i=0;$i<count($rtSplit);$i++)
        {
            if($i==0)$param1=$rtSplit[0];
            if($i==1)$param2=$rtSplit[1];
            if($i==2)$param3=$rtSplit[2];
            if($i==3)$param4=$rtSplit[3];
            if($i==4)$param5=$rtSplit[4];
        }

        // Call controller function
        call_user_func(array($unitController,$actionName),$param1,$param2,$param3,$param4,$param5);

    }

}