<?php

namespace GFUnit\test\cardshop;
use GFBiz\Gavevalg\ShopUserLogin;
use GFBiz\Model\Cardshop\OrderFreightState;
use GFCommon\Model\Navision\OrderXML;

class LoginTest
{

    public function dispatch()
    {

        if(isset($_POST["action"])) {

            if($_POST["action"] == "username") {
                $this->testUsernameLogin();
            } else if($_POST["action"] == "token") {
                $this->testTokenLogin();
            }

        }

        $this->showLoginForm();

    }

    private function getLoginObject()
    {
        $lock = $_POST["lock"];
        $login = new ShopUserLogin(true);

        if($lock == "") {
            echo "No lock provided";
            return null;
        } if(intval($lock) == 0) {
            $login->lockToConcept($lock);
        } else {
            $split = explode(",",$lock);
            if(count($split) == 1) $login->lockToShopID(intval($split[0]));
            else $login->lockToShopIDList($split);
        }
        return $login;
    }

    private function testUsernameLogin()
    {

        echo "<h2>Test med brugernavn</h2>";

        $username = $_POST["username"];
        $password = $_POST["password"];

        $login = $this->getLoginObject();

        $login->createWithUsernamePassword($username,$password);


        echo "<br><hr><br>";
    }

    private function testTokenLogin()
    {

        echo "<h2>Test med token</h2>";


        $token = $_POST["token"];

        $login = $this->getLoginObject();




        echo "<br><hr><br>";


    }

    private function showLoginForm()
    {




        ?><h2>Login test</h2>
        <form method="post" action="https://system.gavefabrikken.dk//gavefabrikken_backend/index.php?rt=unit/test/cardshop/logintest">
            <b>Test username / password login</b><br>
            Enter shop: <input type="text" name="lock" <?php if(isset($_POST["lock"])) echo "value='".$_POST["lock"]."'" ?>> (multiple with comma) or concept code<br>
            Username <input type="text" name="username" <?php if(isset($_POST["username"])) echo "value='".$_POST["username"]."'" ?>><br>
            Password <input type="text" name="password" <?php if(isset($_POST["password"])) echo "value='".$_POST["password"]."'" ?>><br>
            <input type="hidden" name="action" value="username">
            <button type="submit">Login</button>
        </form><br><br>

        <form method="post" action="https://system.gavefabrikken.dk//gavefabrikken_backend/index.php?rt=unit/test/cardshop/logintest">
            <b>Test token login</b><br>
            Enter shop: <input type="text" name="lock" <?php if(isset($_POST["lock"])) echo "value='".$_POST["lock"]."'" ?>> (multiple with comma) or concept code<br>
            Token <input type="text" name="token" <?php if(isset($_POST["token"])) echo "value='".$_POST["token"]."'" ?>><br>
            <input type="hidden" name="action" value="token">
            <button type="submit">Check token</button>
        </form><?php





    }

}