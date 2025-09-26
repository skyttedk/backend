<?php

namespace GFUnit\development\login;
use GFBiz\Gavevalg\ShopUserLogin;
use GFBiz\Gavevalg\ShopCloseCheck;
use GFBiz\units\UnitController;

class Controller extends UnitController
{

    public function __construct()
    {
        parent::__construct(__FILE__);


    }

    public function index() {
        view("view");
    }

    public function login()
    {

        // Get input
        $username = $_POST["username"] ?? "";
        $password = $_POST["password"] ?? "";
        $shopid = intval($_POST["shopid"] ?? 0);
        $concept = $_POST["concept"] ?? "";
        $createtoken = intval($_POST["createtoken"] ?? "") == 1;

        ob_start();

        try {
            
            // Perform login
            $loginModel = new ShopUserLogin();
    
            // Lock to shop or concept
            if(intval($shopid) > 0) {
                $loginModel->lockToShopID($shopid);
            } else if(trim($concept) != "") {
                $loginModel->lockToConcept($concept);
            }
            
            if($loginModel->loginWithUsernamePassword($username,$password)) {
                echo "LOGIN VALID!<br>";

                $shopUser = $loginModel->getShopUser();
                echo "Shopuser: [".$shopUser->id."] ".$shopUser->username."<br>";
                echo "Shop: [".$loginModel->getShop()->id."] ".$loginModel->getShop()->name."<br>";
                echo "Current token: ".$shopUser->token."<br>";
                echo "Closes at: ";

                $closeDate = ShopCloseCheck::getShopCloseDate($loginModel->getShopUser()->shop_id,$loginModel->getShopUser()->expire_date->format("Y-m-d"));
                if($closeDate == null) {
                    echo "COULD NOT DETERMINE CLOSE DATE";
                } else {
                    echo $closeDate->format("Y-m-d H:i");
                }
                echo "<br>";


                if($createtoken) {
                    $token = $loginModel->createToken();
                    echo "<br>Token created: ".$token;
                }

                \system::connection()->commit();

            } else {
                echo "LOGIN INVALID: [".$loginModel->getErrorCode()."] ".$loginModel->getErrorMessage();
            }

        } catch (\Exception $e) {
            echo "Exception during login: ".$e->getMessage()."<br>File: ".$e->getFile()." @ ".$e->getLine();
        }

        $output = ob_get_contents();
        ob_end_clean();

        $this->view("view",array("output" => $output));

    }

    public function token()
    {

        // Get input
        $token = $_POST["token"] ?? "";
        $shopid = intval($_POST["shopid"] ?? 0);
        $concept = $_POST["concept"] ?? "";

        ob_start();

        try {

            // Perform login
            $loginModel = new ShopUserLogin();

            // Lock to shop or concept
            if(intval($shopid) > 0) {
                $loginModel->lockToShopID($shopid);
            } else if(trim($concept) != "") {
                $loginModel->lockToConcept($concept);
            }

            if($loginModel->loginWithToken($token)) {
                echo "TOKEN VALID!<br>";

                $shopUser = $loginModel->getShopUser();
                echo "Shopuser: [".$shopUser->id."] ".$shopUser->username."<br>";
                echo "Shop: [".$loginModel->getShop()->id."] ".$loginModel->getShop()->name."<br>";
                echo "Current token: ".$shopUser->token."<br>";
                echo "Closes at: ";

                $closeDate = ShopCloseCheck::getShopCloseDate($loginModel->getShopUser()->shop_id,$loginModel->getShopUser()->expire_date->format("Y-m-d"));
                if($closeDate == null) {
                    echo "COULD NOT DETERMINE CLOSE DATE";
                } else {
                    echo $closeDate->format("Y-m-d H:i");
                }
                echo "<br>";


                \system::connection()->commit();

            } else {
                echo "TOKEN INVALID: [".$loginModel->getErrorCode()."] ".$loginModel->getErrorMessage();
            }

        } catch (\Exception $e) {
            echo "Exception during token check: ".$e->getMessage()."<br>File: ".$e->getFile()." @ ".$e->getLine();
        }

        $output = ob_get_contents();
        ob_end_clean();

        $this->view("view",array("output" => $output));

    }

    public function close()
    {

        // Get input
        $shopid = intval($_POST["shopid"] ?? 0);
        $expiredate = $_POST["expire_date"] ?? "";

        ob_start();

        try {


            $closeDate = ShopCloseCheck::getShopCloseDate($shopid,$expiredate);
            echo "Deadline ".$expiredate." til shop ".$shopid." lukker: ";
            if($closeDate == null) {
                echo "COULD NOT DETERMINE CLOSE DATE";
            } else {
                echo $closeDate->format("Y-m-d H:i");
            }

        } catch (\Exception $e) {
            echo "Exception during token check: ".$e->getMessage()."<br>File: ".$e->getFile()." @ ".$e->getLine();
        }

        $output = ob_get_contents();
        ob_end_clean();

        $this->view("view",array("output" => $output));

    }



}