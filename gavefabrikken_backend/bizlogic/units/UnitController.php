<?php

namespace GFBiz\units;

class UnitController
{

    private $unitControllerFile = "";

    public function __construct($unitPath) {
        $this->unitControllerFile = $unitPath;
    }

    public function view($viewName,$data=null)
    {


        // Ignore if trying to access parent directory
        if($viewName !== null && strstr($viewName,"..")) return;


        // Set viewname to php file
        if(trimgf($viewName) == "") $viewName = "view";
        $viewName = trimgf($viewName).".php";



        // Get path of view
        $path = realpath(dirname($this->unitControllerFile));
        $filepath = $path."/".$viewName;

        // Check for view
        if(!file_exists($filepath)) $filepath = $path."/".strtolower($viewName);
        if(!file_exists($filepath)) {
            $pathSplit = explode("/",$path);
            if($viewName == "view.php") {
                echo "This unit does not have a default view";
            } else {
                echo "<div> Could not find component view: ".$pathSplit[countgf($pathSplit)-2]."/".$pathSplit[countgf($pathSplit)-1]."/".$viewName."</div>";
            }
            return;
        }

        // Check data
        if(!is_array($data)) $data = array();
        extract($data);

        // Split controller file to find controller path
        $controllerSplit = explode("/",$this->unitControllerFile);
        array_pop($controllerSplit);
        $unitName = array_pop($controllerSplit);
        $unitName = array_pop($controllerSplit)."/".$unitName;

        // Add standard data
        $assetPath = \GFConfig::BACKEND_URL."units/".$unitName."/";
        $servicePath = \GFConfig::BACKEND_URL."index.php?rt=unit/".$unitName."/";
        $unitPath = \GFConfig::BACKEND_URL."index.php?rt=unit/";

        // Include view
        include($filepath);
    }

    public function requirePost()
    {
        if($_SERVER['REQUEST_METHOD'] !== 'POST') {
            \response::error("Request needs to be post");
            exit();
        }
    }

    public function requireGet()
    {
        if($_SERVER['REQUEST_METHOD'] !== 'GET') {
            \response::error("Request needs to be get");
            exit();
        }
    }

    public function requireDelete()
    {
        if($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            \response::error("Request needs to be delete");
            exit();
        }
    }

}