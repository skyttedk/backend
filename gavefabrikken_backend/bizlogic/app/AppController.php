<?php

namespace GFBiz\app;

class AppController
{

    private $appControllerFile = "";

    public function __construct($appPath) {
        $this->appControllerFile = $appPath;
    }

    public function view($viewName,$data=null)
    {
        // Ignore if trying to access parent directory
        if(strstr($viewName,"..")) return;

        // Set viewname to php file
        if(trimgf($viewName) == "") $viewName = "view";
        $viewName = trimgf($viewName).".php";

        // Get path of view
        $path = realpath(dirname($this->appControllerFile));
        $filepath = $path."/".$viewName;

        // Check for view
        if(!file_exists($filepath)) $filepath = $path."/".strtolower($viewName);
        if(!file_exists($filepath)) {
            $pathSplit = explode("/",$path);
            if($viewName == "view.php") {
                echo "This app does not have a default view";
            } else {
                echo "<div> Could not find component view: ".$pathSplit[countgf($pathSplit)-2]."/".$pathSplit[countgf($pathSplit)-1]."/".$viewName."</div>";
            }
            return;
        }

        // Check data
        if(!is_array($data)) $data = array();
        extract($data);

        // Split controller file to find controller path
        $controllerSplit = explode("/",$this->appControllerFile);
        array_pop($controllerSplit);
        $appName = array_pop($controllerSplit);
        $appName = array_pop($controllerSplit)."/".$appName;

        // Add standard data
        $assetPath = \GFConfig::BACKEND_URL."app/".$appName."/";
        $servicePath = \GFConfig::BACKEND_URL."index.php?rt=app/".$appName."/";
        $appPath = \GFConfig::BACKEND_URL."index.php?rt=app/";

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