<?php

if (session_status() == PHP_SESSION_NONE)
{
    ini_set("session.gc_maxlifetime",20*60*60);
    session_set_cookie_params(20*60*60);
    session_start();
}

/**
 * $_SESSION["syslogin"] bruges ikke i denne klasse og bør udfases fra kode andre steder
 */

class router
{
    private $registry;
    private $path;

    public $file;
    public $controller;
    public $action;

    //Globals
    private static $routerInit = false;
    public static $userType;
    public static $username = "";
    public static $systemLogId = 0;
    public static $systemLogAction;
    public static $systemLogController;
    public static $shopUser;
    public static $systemUser;
    public static $systemLogSkip = false;
    public static $callStarted = 0;

    /**
     * router constructor.
     * @param Registry $registry
     */

    function __construct($registry)
    {
        $this->registry = $registry;
        router::$callStarted = microtime(true);
    }

    public static function isPublicController($controllerName)
    {
        // Define public controllers
        $publicControllers = array(
            "cardshipment",
            "orderconfirmation",
            "login",
            "bi",
            "alert",
            "cardordermails",
            "error404",
            "external",
            "extservice",
            "gfshop",
            "index",
            "kundepanel2",
            "kundepanel",
            "kundeside",
            "kundesidenorge",
            "monitoring",
            "navsync",
            "newshopload",
            "order",
            "page",
            "ping",
            "present",
            "rapport",
            "receipt",
            "afterSalesEmail",
            "mail",
            "registrer",
            "report",
            "shop",
            "shopload",
            "sms",
            // ADD OTHER CONTROLLERS TO CHECK
            "adminrapport",
            "bestilling",
            "cardshop",
            "cardshopnote",
            "cardshoppluk",
            "cleanup",
            "company",
            "comanynotesex",
            //"developer",
            "earlypresent",
            "error404",
            "gavealias",
            "generic",
            "giftcertificate",
            "infoboard",
            "lager2",
            "lager",
            "mail2",
            "mainaa",
            "media2",
            "media",
            "orderpresentcomplaint",
            "pdfcrowd",
            "presentadmin",
            "ptadmin",
            "ptimage",
            "registrer",
            "reservation",
            "shop2",
            "shopboard",
            "shoppresentrules",
            "shopsettings",
            "stats2019",
            "stats2020",
            "stats",
            "syncserv",
            //"systemlog",
            //"systemuser",
            "tab",
            "upload",
            "companytree",
            "navfront",
            "gavevalg",
            "mailtemplate",
            "voucher",
            "autoSelectSpecificPresents",
            "Labelrapport",
            "pimPresentSync",
            "pimShopPresentSync",
            "siteservice",
            "app",
            "gf",
            "stats2021",
            "ftp",
            "statsDbCalc",
            "basket",
            "reservationmanagement",
            "pwc",
            "warehouseportal",
            "presentoption",
            "paperportal",
            "papervalg",
            "overbookedApprovalController",
            "forgetpassword",
            "gfvsbe",
            "magentoorderstock",
            "gfwh"
        );

        return in_array(strtolower(trimgf($controllerName)), $publicControllers);

    }

    public static function isRouted() { return self::$routerInit; }
    public static function checkRouted() { if(!self::isRouted()) { echo "Invalid script call"; exit(); } }

     public static function getSystemUserLocalisation(){
        $sysUser =  router::$systemUser;
        $language = $sysUser->attributes["language"];
        if($language == 5){
            $language = 1;
        }
        return $language;
      }

    public function loader()
    {

        // Resovle controller / action
        $this->getController();

        // Check if controller is available
        if (is_readable($this->file) == false) {
            $this->file = $this->path . '/error404.php';
            $this->action = "index";
            $this->controller = 'error404';
        }

        // Include the controller
        include $this->file;

        // Create controller class
        $class = $this->controller . 'Controller';
        $controller = new $class($this->registry);

        // Check if action exists on controller
        if (is_callable(array($controller, $this->action)) == false) {
            $action = 'index';
        } else {
            $action = $this->action;
        }

        // Set controller and action on static members
        router::$systemLogController = $this->controller;
        router::$systemLogAction = $action;

        // Get authentication type: shop: verify by a shop token in shop table, backend: verify by session token from system_user table
        router::$userType = isset($_POST['logintype']) && $_POST["logintype"] == "shop" ? "shop" : "backend";

        // Check for public controllers
        $isPublicController = router::isPublicController(router::$systemLogController);


        try {

            // Shop login
            if (router::$userType == "shop") {

                // Check token is provided
                if(isset($_REQUEST['token']) && trimgf($_REQUEST['token']) != "")
                {
                    // Find shop user by token
                    $shopUser = Login::testToken(router::$userType, $_REQUEST['token']);

                    // Check shopuser
                    if (!isset($shopUser)) {
                        if($isPublicController == false)
                        {
                            throw new loginException("Please Login");
                        }
                    } else {
                        router::$shopUser = $shopUser;
                        router::$username = "shopuser:".$shopUser->username;
                    }
                }

                // Derfine shop controllers
                $shopControllers = array(
                    "login"
                );

                // Check it is a valid shop controller og a public controller
                if (!in_array(router::$systemLogController, $shopControllers) ) {
                    throw new loginException("You dont have access to this endpoint.");
                }

            }
            // Backend login
            else {

                // Check token
                if($this->controller == "unit") {
                    $headers = apache_request_headers();
                    if(isset($headers['Authorization']) && !strstr($headers['Authorization'],"Basic ")){
                        $_SESSION["systemuser_login".GFConfig::SALES_SEASON] = true;
                        $_SESSION["systemuser_token".GFConfig::SALES_SEASON] = $headers['Authorization'];
                    }
                }

                // Check login
                if (isset($_SESSION["systemuser_login".GFConfig::SALES_SEASON]) && isset($_SESSION["systemuser_token".GFConfig::SALES_SEASON]) && $_SESSION["systemuser_login".GFConfig::SALES_SEASON] === true) {
                    //$systemUser = Login::testToken(router::$userType, $_SESSION["systemuser_token".GFConfig::SALES_SEASON]);


                    $accessToken = new \GFCommon\Model\Tokens\SystemUserToken($_SESSION["systemuser_token".GFConfig::SALES_SEASON]);
                    if($accessToken->isValid())
                    {
                        $systemuser = SystemUser::find(intval($accessToken->getObject()->getReference()));
                        if($systemuser != null && $systemuser->id > 0 && $systemuser->id == $accessToken->getObject()->getReference())
                        {
                            router::$systemUser = $systemuser;
                            router::$username = "systemuser:".$systemuser->username;
                            if(!$accessToken->hasDataKey("static")) $accessToken->refreshToken(10);
                        }

                    }
                }

                // If not logged in and not public controller, ask for password
                if(router::$systemUser == null && !$isPublicController)
                {
                    throw new loginException("Please Login..");
                }

            }
        }
        catch (Exception $lex) {
            if($this->controller == "mainaa") {
                header("Location: index.php?rt=login");
            }
            else
            {
                response::loginRequest();
            }

            return;
        }


        // Unset logintype og token for ikke at forstyrre data
        unset($_POST['logintype']);
        unset($_REQUEST['token']);

/*
        if(self::$systemUser == null || !in_array(self::$systemUser->id, array(86,50,5,51))) {
            echo "Systemet serviceres. Vi er tilbage mellem kl. 8 og 9!";
            exit();
        }
*/

        try {

            // Create a System Log Entry
            System::connection()->transaction();
            $systemlog = new SystemLog();
            $systemlog->user_id = router::$username;
            $systemlog->controller = $this->controller;
            $systemlog->action = $action;
            $systemlog->data = json_encode($_POST);
            $systemlog->ip = isset($_SERVER['REMOTE_ADDR']) ? substr($_SERVER['REMOTE_ADDR'],0,20) : "";
            $systemlog->browser = isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'],0,150) : "";
            $systemlog->url = $_SERVER["REQUEST_URI"];
            $systemlog->save();

            router::$systemLogId = $systemlog->id;
            System::connection()->commit();

            // Start trancaction and perform action on controller
            System::connection()->transaction();
            self::$routerInit = true;
            $controller->$action();


        } catch (Exception $ex) {

            // Rollback current transaction
            try { System::connection()->rollback(); }
            catch (Exception $ex2) { };

            // Update system Log With Exception Message
            System::connection()->transaction();

            if (router::$systemLogId > 0) {

                $systemlog = SystemLog::find(router::$systemLogId);
                try {
                    $systemlog->user_id = router::$username;
                    $systemlog->error_message = $ex->getMessage();
                    $systemlog->error_trace = $ex->getTraceAsString();
                    $systemlog->save();
                    System::connection()->commit();
                } catch (Exception $ex2) {};
            }

            //Create Error Response
            $system = System::first();
            if ($system->full_trace == 1 || (self::$systemUser != null && self::$systemUser->id == 50)) {
                response::error($ex->getMessage()."<br>".$ex->getTraceAsString());
            } else {
                response::error($ex->getMessage());
            }
        }
    }


    /**
     * Sets folder to look for controllers in
     */
    public function setPath($path)
    {
        /*** check if path i sa directory ***/
        if (is_dir($path) == false) {
            throw new Exception ('Invalid controller path: `' . $path . '`');
        }
        /*** set the path ***/
        $this->path = $path;
    }

    /**
     *
     * @get the controller
     * @access private
     * @return void
     *
     */
    private function getController()
    {

        // Get route from url
        $route = (empty($_GET['rt'])) ? '' : $_GET['rt'];

        // Set default if route not set
        if (empty($route)) {
            $route = 'index';
        }

        // Explode into controller and action
        else {

            $parts = explode('/', $route);
            $this->controller = $parts[0];
            if (isset($parts[1])) {
                $this->action = $parts[1];
            }
        }

        // Check controller
        if (empty($this->controller)) {
            $this->controller = 'index';
        }

        // Check action
        if (empty($this->action)) {
            $this->action = 'index';
        }

        // Get file name of controller
        $this->file = $this->path . '/' . $this->controller . 'Controller.php';

    }


}
