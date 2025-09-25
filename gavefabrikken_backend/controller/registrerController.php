<?php

class RegistrerController Extends baseController {

    /*
     * HELPERS
     */

    const CONTROLLER = "registrer";
    private function getUrl($method="") { return "".GFConfig::BACKEND_URL."index.php?rt=".RegistrerController::CONTROLLER."/".$method; }
    private $error = "";


    /********************************************
     * LOGIN / LOGOUT VIEW AND LOGIC
     ********************************************/

    public function Index()
    {
        $this->init(false);
        if($this->isLoggedIn)
        {
            $this->dashboard();
            return;
        }

        ?><html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
            body { margin: 50px; padding: 0px; font-size: 14px; font-family: verdana; }
        </style>
    </head>
    <body>
    <h2>Log ind i gave udleverings modulet</h2>
    <form method="post" action="<?php echo $this->getUrl("login"); ?>/login">
        <div style="padding: 15px;">
            <b>Brugernavn</b><br>
            <input type="text" size="20" name="gum_username">
        </div>
        <div style="padding: 15px;">
            <b>Adgangskode</b><br>
            <input type="password" size="20" name="gum_password">
        </div>
        <?php if($this->error != "") echo "<div style='text-align: center; color: red;'>".$this->error."</div>"; ?>
        <div style="padding: 15px;">
            <input type="submit" value="Log ind">
        </div>
    </form>
    </body>
        </html><?php
    }

    /*
    private $manualLogins = array(
        array("username" => "novofug", "password" => "B4tKpkLG", "shopid" => 65, "companyid" => 95, "restrict" => array("attributeid" => "447","value" => "Fuglebakken, bygning XP  - 5. december mellem kl. 07.00 og 16.00")),
        array("username" => "novobag", "password" => "WFrJ5rTZ", "shopid" => 65, "companyid" => 95, "restrict" => array("attributeid" => "447","value" => "Bagsværd, bygning 8X (Auditoriet) - 8. december mellem kl. 07.00 og 16.00")),
        array("username" => "novokal", "password" => "SvtXCuhT", "shopid" => 65, "companyid" => 95, "restrict" => array("attributeid" => "447","value" => "Kalundborg, bygning FE - 7. december mellem kl. 07.00 og 16.00"))
    );

    */
    
    private $manualLogins = array(
    /*
        array("username" => "novofug", "password" => "RXv6jh7Q", "shopid" => 297, "companyid" => 6304, "restrict" => array("attributeid" => "1844","value" => "Fuglebakken, bygning XP  - 4. december mellem kl. 07.00 og 16.00")),
        array("username" => "novobag", "password" => "S2pYxnTq", "shopid" => 297, "companyid" => 6304, "restrict" => array("attributeid" => "1844","value" => "Bagsværd, bygning 8X (Auditoriet) - 7. december mellem kl. 07.00 og 16.00")),
        array("username" => "novokal", "password" => "D2VvXkD6", "shopid" => 297, "companyid" => 6304, "restrict" => array("attributeid" => "1844","value" => "Kalundborg, bygning FE - 6. december mellem kl. 07.00 og 16.00")),
     */   
              
      array("username" => "ramcopen", "password" => "n7tQPZGq", "shopid" => 599, "companyid" => 6208, "restrict" => array("attributeid" => "3338","value" => "Rambøll, Hannemanns Allé 53, 2300 København S")),
      array("username" => "ramaarh", "password" => "e6PMem9h", "shopid" => 599, "companyid" => 6208, "restrict" => array("attributeid" => "3338","value" => "Rambøll, Olof Palmes Allé 22, 8200 Aarhus N")), 
      array("username" => "ramaalb", "password" => "3GqPN7yu", "shopid" => 599, "companyid" => 6208, "restrict" => array("attributeid" => "3338","value" => "Rambøll, Prinsensgade 11, 9000 Aalborg")), 
      array("username" => "ramoden", "password" => "NW44sAMe", "shopid" => 599, "companyid" => 6208, "restrict" => array("attributeid" => "3338","value" => "Rambøll, Englandsgade 25, 5000 Odense C")),
      array("username" => "ramvibo", "password" => "tbrpaXuJ", "shopid" => 599, "companyid" => 6208, "restrict" => array("attributeid" => "3338","value" => "Rambøll, Lundborgvej 16, 8800 Viborg")),
      array("username" => "ramvejl", "password" => "yftMsef9", "shopid" => 599, "companyid" => 6208, "restrict" => array("attributeid" => "3338","value" => "Rambøll, Lysholt Allé 6, 7100 Vejle")),
      array("username" => "ramesbj", "password" => "zX3sryLR", "shopid" => 599, "companyid" => 6208, "restrict" => array("attributeid" => "3338","value" => "Rambøll, Bavnehøjvej 5, 6700 Esbjerg")),
      array("username" => "ramhern", "password" => "HrDedELN", "shopid" => 599, "companyid" => 6208, "restrict" => array("attributeid" => "3338","value" => "Rambøll, Innovatorium, Birkcenterpark 40, 7400 Hernning")),
      array("username" => "ramfred", "password" => "26tYLrfX", "shopid" => 599, "companyid" => 6208, "restrict" => array("attributeid" => "3338","value" => "Rambøll, Danmarksgade 2A, 7000 Fredericia")),
      array("username" => "ramkold", "password" => "VgqNFdAV", "shopid" => 599, "companyid" => 6208, "restrict" => array("attributeid" => "3338","value" => "Rambøll, Kolding Åpark 1, 6000 Kolding")),
      array("username" => "ramsond", "password" => "gLaDvwR5", "shopid" => 599, "companyid" => 6208, "restrict" => array("attributeid" => "3338","value" => "Rambøll, Nørre Havnegade 43, 3. sal, 6400 Sønderborg")),
      array("username" => "ramrosk", "password" => "284jHqP2", "shopid" => 599, "companyid" => 6208, "restrict" => array("attributeid" => "3338","value" => "Rambøll, Ny Østergade 7, 4000 Roskilde")),
      array("username" => "ramhadr", "password" => "WbKrWwd5", "shopid" => 599, "companyid" => 6208, "restrict" => array("attributeid" => "3338","value" => "Rambøll, Hansborggade 30, 2 floor, 6100 Haderslev")),
        array("username" => "sctest", "password" => "sctest", "shopid" => 601, "companyid" => 13206, "restrict" => array("attributeid" => "4758","value" => "Lokation 1"))
        
    );

    private function usernameToLocation($username)
    {
        foreach($this->manualLogins as $login)
        {
            if($username == $login["username"]) return $login["restrict"]["value"];
        }
        return "";
    }

    public function login()
    {
        $this->init(false);

        $username = isset($_POST["gum_username"]) ? $_POST["gum_username"] : "";
        $password = isset($_POST["gum_password"]) ? $_POST["gum_password"] : "";

        foreach($this->manualLogins as $login)
        {
            if($username == $login["username"] && strtolower($password) == strtolower($login["password"]))
            {
                // Set session data
                $_SESSION["gum_authenticated"] = true;
                $_SESSION["gum_companyid"] = $login["companyid"];
                $_SESSION["gum_shopid"] = $login["shopid"];
                $_SESSION["gum_restrict"] = $login["restrict"];
                $_SESSION["gum_username"] = $login["username"];

                // Send user to dashboard
                header("Location: ".$this->getUrl("dashboard"));
                $this->addAppLog("LOGIN","Brugeren ".$_SESSION["gum_username"]." er logget ind");
                return;
            }
        }

        // Find company
        $companyList = Company::find("all",array('conditions' => array('username' => trimgf($username), 'password' => trimgf($password))));
        if(count($companyList) == 0)
        {
            $this->error = "Forkert brugernavn eller adgangskode.";
            return $this->index();
        }
        $company = $companyList[0];

        // Find shops
        $shoplinklist = CompanyShop::find('all',array("conditions" => array("company_id" => $company->id)));
        if(count($shoplinklist) == 0)
        {
            $this->error = "Ingen shops tilknyttet din bruger";
            return $this->index();
        }

        // Find shop id
        $shopid = intval($shoplinklist[0]->shop_id);
        if($shopid <= 0)
        {
            $this->error = "Kunne ikke finde shop tilknyttet din bruger.";
            return $this->index();
        }

        // Set session data
        $_SESSION["gum_authenticated"] = true;
        $_SESSION["gum_companyid"] = $company->id;
        $_SESSION["gum_shopid"] = $shopid;
        $_SESSION["gum_restrict"] = null;
        $_SESSION["gum_username"] = $username;

        // Send user to dashboard
        $this->addAppLog("LOGIN","Brugeren ".$_SESSION["gum_username"]." er logget ind");
        header("Location: ".$this->getUrl("dashboard"));

    }

    public function logout()
    {
        $this->init(true);
        $_SESSION["gum_authenticated"] = false;
        $_SESSION["gum_companyid"] = 0;
        $_SESSION["gum_shopid"] = 0;
        $this->isLoggedIn = false;
        $this->index();
    }

    /**** PRIVATE LOGIN / INIT LOGIC ****/

    private $isLoggedIn = false;
    private $companyid = 0;
    private $shopid = array();

    /** @var  Shop */
    private $shopObject;

    /**
     * @param $needsLogin If the user needs to be logged in
     */

    private function init($needsLogin)
    {
        if (session_status() == PHP_SESSION_NONE)
        {
            ini_set("session.gc_maxlifetime",8640);
            session_set_cookie_params(12*60*60);
            session_start();
        }
        $this->isLoggedIn = false;

        if(isset($_SESSION["gum_authenticated"]) && $_SESSION["gum_authenticated"] === true && isset($_SESSION["gum_companyid"]) && $_SESSION["gum_companyid"] > 0 && isset($_SESSION["gum_shopid"]) && countgf($_SESSION["gum_shopid"]) > 0)
        {
            $this->isLoggedIn = true;
            $this->companyid = intval($_SESSION["gum_companyid"]);
            $this->shopid = intval($_SESSION["gum_shopid"]);

            // Load shop
            if($this->shopObject == null || $this->shopObject->id != $this->shopid)
            {
                $this->shopObject = Shop::find($this->shopid);
            }

        }

        if($needsLogin == true && !$this->isLoggedIn)
        {
            $this->error = "Du skal være logget ind for at udføre denne handling.";
            $this->index();
            exit();
        }

    }

    /********************************************
     * FUNCTIONALITY THAT REQUIRES LOGIN
     ********************************************/

    public function dashboard()
    {
        $this->init(true);
        $this->authTop();

        ?>
        <div style="padding: 20px;">
            <h2>Gaveregistrering</h2>
            <p>
            <p>Du kan her registrere udlevering af gaver.</p>

            <p>Sørg for at udlevering er aktiveret. Du ser og skifter status øverst i højre hjørne</p>
            <ul>
                <li>Søg efter navn eller e-mail i søgefeltet for at finde en person.<br></li>
                <li>Scan en QR kode for at få vist en kvittering direkte.<br></li>
            </ul>
            </p>

            <?php

            if(isset($_SESSION["gum_restrict"]) && is_array($_SESSION["gum_restrict"]) && isset($_SESSION["gum_restrict"]["value"]))
            {
                echo "<div style='font-size: 1.2em;'><b>Bemærk:</b> registrering kan kun foretages for ordre til <b>".trimgf($_SESSION["gum_restrict"]["value"])."</b></div>";
            }

            ?>
        </div>

        <?php


        $this->authBottom();
    }

    public function search()
    {
        $this->init(true);
        $this->authTop();

        // Find query string
        $query = isset($_POST["query"]) ? trimgf($_POST["query"]) : "";
        if($query == "")
        {
            $this->dashboard();
            exit();
        }

        // Find shop attributes
        $searchAttributes = ShopAttribute::find_by_sql("SELECT * FROM shop_attribute WHERE shop_id = ".intval($this->shopid)." && (is_name = 1 || is_email = 1)");
        $attributeidlist = array();
        $nameAttributeid = 0;
        $emailAttributeid = 0;

        $locationAttributeid = 447;

        foreach($searchAttributes as $sa)
        {
            if($sa->is_name == 1) $nameAttributeid = $sa->id;
            if($sa->is_email == 1) $emailAttributeid = $sa->id;
            $attributeidlist[] = $sa->id;
        }

        // Find users by attributes
        $sql = "SELECT * FROM shop_user WHERE shop_id = ".intval($this->shopid)." && id IN (SELECT shopuser_id FROM user_attribute WHERE shop_id = ".intval($this->shopid)." && attribute_value LIKE :Query && attribute_id IN (".implode(",",$attributeidlist)."))";
        $userlist = ShopUser::find_by_sql($sql,array(":Query" => "%".trimgf($query)."%"));

        // Attribute map
        $userAttributeMap = array();
        $sql = "SELECT * FROM user_attribute WHERE shop_id = ".intval($_SESSION["gum_shopid"])." && attribute_id IN (".intval($nameAttributeid).",".intval($emailAttributeid).",".intval($locationAttributeid).")";
        
        $attributelist = UserAttribute::find_by_sql($sql);
        foreach($attributelist as $ua)
        {
            @$userAttributeMap[$ua->shopuser_id][$ua->attribute_id] = $ua->attribute_value;
        }

        // Output results
        ?><div style="padding: 20px;">
        Søgning efter <b><?php echo $query; ?></b> gav <?php echo countgf($userlist); ?> resultat<?php if(count($userlist) > 1) echo "er"; ?>
        <?php if(count($userlist) > 100) echo " - viser de første 100 resultater"; ?>
        </div><?php

        ?><table style="width: 100%;" cellpadding="0" cellspacing="0">

        <tr>
            <th>Person</th><?php

            if($_SESSION["gum_shopid"] == 65)
                echo "<td>Udlevering</td>";

            ?><th style="text-align: center;">Gavevalg</th>
            <th>&nbsp;</th>
        </tr>
        <?php

        /**
         * @var $user ShopUser
         */

        if(count($userlist) == 0)
        {
            ?><tr><td colspan="4" style="font-size: 1.2em; text-align: center; padding: 30px;">Der blev ikke fundet nogle resultater..</td></tr><?php
        }

        foreach($userlist as $index => $user)
        {
            if($index == 100) break;

            $name = "Ukendt navn";
            $email = "Ukendt e-mail";
            $location = "";

/*
            $attr = $user->attributes();
            foreach($attr["user_attributes"] as $ua)
            {
                if($ua->attribute_id == $nameAttributeid) $name = $ua->attribute_value;
                if($ua->attribute_id == $emailAttributeid) $email = $ua->attribute_value;
                if($ua->attribute_id == $locationAttributeid) $location = $ua->attribute_value;
            }
*/

            $name = @$userAttributeMap[$user->id][$nameAttributeid];
            $email = @$userAttributeMap[$user->id][$emailAttributeid];
            $location = @$userAttributeMap[$user->id][$locationAttributeid];

            echo "<tr>
                <td><b>".$name."</b><br>".$email."</td>";

            if($_SESSION["gum_shopid"] == 65)
            {
                $color = "";

                if(isset($_SESSION["gum_restrict"]) && is_array($_SESSION["gum_restrict"]) && isset($_SESSION["gum_restrict"]["attributeid"]) && $_SESSION["gum_restrict"]["attributeid"] == $locationAttributeid)
                {
                    if($_SESSION["gum_restrict"]["value"] != $location) $color = "red";
                    else $color = "green";
                }

                echo "<td style='color: ".$color.";'>".(strlen($location) > 20 ? (substr($location,0,20)."...") : $location)."</td>";
            }

            echo "<td style='text-align: center;'>";

            if($user->has_orders()) {
                echo "<div style='padding: 3px; '>".($user->order()[0]->registered == 0 ? "Ikke udleveret" : "Udleveret") . "</div>
                <input type='button' onClick='document.location=\"".$this->getUrl("register&orderno=".$user->order()[0]->order_no)."\"' value='Vis ordrenr " . $user->order()[0]->order_no . "'>";
            }
            else
            {
                echo "Ingen ordre / gavevalg";
            }

            echo "</td>
            </tr>";
        }

        ?></table><?php

        $this->authBottom();
    }

    private function canEditOrder(Order $order)
    {
        if(isset($_SESSION["gum_restrict"]) && isset($_SESSION["gum_restrict"]["attributeid"]))
        {
            $sql = "SELECT * FROM shop_user WHERE shop_id = ".intval($order->shop_id)." && id = ".intval($order->shopuser_id);
            $userlist = ShopUser::find_by_sql($sql,array());
            if(count($userlist) == 0) return false;

            $shopuser = $userlist[0];
            $attr = $shopuser->attributes();
            foreach($attr["user_attributes"] as $ua)
            {
                if($ua->attribute_id == $_SESSION["gum_restrict"]["attributeid"] )
                {
                    if($ua->attribute_value == $_SESSION["gum_restrict"]["value"]) return 1;
                    else if(trimgf($ua->attribute_value) == "") return 2;
                }
            }

            return 0;

        }
        else return 1;
    }

    public function register()
    {

        $this->init(false);

        if(!$this->isLoggedIn)
        {
            echo '<html>';
            echo '<head><style>body { font-family: Arial, Helvetica Neue, Helvetica, sans-serif; font-size:20px; line-height: 2; </style>';
            echo '</head>';
            echo '<body>';
            echo '<center><div style="font-size: 6vw;">Du er ikke logget ind.</div><br><a href="'.$this->getUrl("index").'">klik her for at logge ind</a><hr />';
            echo '</body></html>';
            exit();
        }

        $this->authTop();

        $orderno = isset($_REQUEST['orderno']) ? $_REQUEST['orderno'] : "";
        $Order  =  Order::find_by_order_no($orderno);

        if($Order && $Order->shop_id == $this->shopid)
        {

            $sql = "SELECT * FROM shop_user WHERE shop_id = ".intval($_SESSION["gum_shopid"])." && id = ".intval($Order->shopuser_id);
            $shopuser = ShopUser::find_by_sql($sql,array());
            $shopUserActive = (count($shopuser) == 0 || $shopuser[0]->blocked == 1) ? false : true;

            $present = present::find($Order->present_id);

            echo '<div style="font-size: 24px; padding: 10px;">Gaveregistrering</div>' ;
            echo '<div style="text-align: center;"><img src="../../gavefabrikken_backend/views/media/user/'.$present->present_media[0]->media_path.'.jpg" alt="" style="max-width: 100%; height: 40vh; max-height: 800px;" /></div><br>';

            $canEditOrder = $this->canEditOrder($Order);

            // Is not registered
            if($Order->registered==0)
            {
                if($Order && $this->shopObject->open_for_registration)
                {


                    if($canEditOrder == 0)
                    {
                        echo '<div style="text-align: center; padding: 15px; color: red;"><b>Bemærk:</b> dit login er ikke tilknyttet ordrens lokation.</div>';
                    }

                    if($canEditOrder == 2)
                    {
                        echo '<div style="text-align: center; padding: 15px; color: red;"><b>Bemærk:</b> der er ikke registreret en lokation for udlevering på dette gavekort.</div>';
                    }


                    if($shopUserActive == true)
                    {
                        echo '<div style="padding: 15px; text-align: center;">
                            <input style="width:90%;height:' . ($canEditOrder == 1 ? '10vh' : '4vh') . ';font-size: 18px;" type="button" onclick="location.href=\'' . $this->getUrl("doregister&orderno=" . $orderno) . '\';" value="Registrer udlevering" />
                        </div>';
                    }
                    else
                    {
                        echo '<div style="text-align: center; padding: 15px; color: red;"><b>Brugeren der er tilknyttet denne gave, er fjernet fra systemet!</b></div>';
                    }

                }
                else
                {
                    echo "<div style='padding: 10px; text-align: center;'><b>Ikke udleveret</b><br>Gaveudleveringen er lukket, aktiver gaveudlevering for at registrere udlevering!</div>";
                }
            }
            // Is registered
            else
            {
                echo "<div style='padding: 10px; text-align: center;'>
                    Ordrenr. ".$orderno.' er udleveret d.'.$Order->registered_date->format('d-m-Y')."<br>
                </div>";

                if($shopUserActive == false)
                {
                    echo '<div style="text-align: center; padding: 15px; color: red;"><b>Brugeren der er tilknyttet denne gave, er fjernet fra systemet!</b></div>';
                }

                if($Order && $this->shopObject->open_for_registration && $canEditOrder > 0) {
                    ?><div style='padding: 10px; text-align: center;'><a href='javascript:cancelRegister();'>Træk udlevering tilbage</a> </div>
                    <form id="unregisterform" method="post" action="<?php echo $this->getUrl("dounregister&orderno=" . $orderno); ?>"><input type="hidden" name="action" value="unregister"></form>
                    <script>
                        function cancelRegister() {
                            if (confirm('Er du sikker på at du vil trække udleveringen tilbage?')) {
                                $('#unregisterform').submit();
                            }
                        }
                    </script><?php
                }
            }

            if($Order->user_name !="") echo '<table width=90%  style="font-size: 18px;"><tr><td width=25%>Navn:</td><td width=75%>'.$Order->user_name.'</td></tr>';
            if($Order->present_model_name!="") echo '<tr><td><label>Model:</label></td><td>'.str_replace("###"," - ",$Order->present_model_name).'</td></tr>';
            echo '<tr><td>Email:</td><td>'.$Order->user_email.'</td></tr>';
            echo '<tr><td>Gave:</td><td>'.$Order->present_name.'</td></tr></table>';



        }
        else {

            $orderhistory = OrderHistory::find_by_sql("SELECT * FROM order_history WHERE order_no = ".intval($orderno));
            if(count($orderhistory) > 0 && !headers_sent())
            {

                $orders = Order::find_by_sql("SELECT * FROM `order` WHERE shopuser_id = ".intval($orderhistory[0]->shopuser_id)." && company_id = ".intval($_SESSION["gum_companyid"])." && shop_id = ".intval($_SESSION["gum_shopid"])." && is_demo = 0");
                if(count($orders) > 0)
                {
                    $order = $orders[0];
                    if($order != null && $order->order_no != $orderno)
                    {
                        header("Location: ".GFConfig::BACKEND_URL."index.php?rt=registrer/register&orderno=".$order->order_no);
                        exit();
                    }
                }

            }

            echo '<div style="font-size: 24px; text-align: center; padding: 20px; ">Ordrenr. '.$orderno.' blev ikke fundet!</div><br><hr />' ;

        }

        $this->authBottom(true);
        //$orderno = trimgf($_GET["orderno"]);
    }

    public function doregister()
    {
        // Init and check login
        $this->init(false);
        if(!$this->isLoggedIn) return $this->register();

        // Check that shop is open
        if(!$this->shopObject->open_for_registration)
        {
            $this->error = "Der er ikke åben for udlevering. Aktiver udlevering!";
            return $this->register();
        }

        // Find order number and order
        $orderno = isset($_REQUEST['orderno']) ? $_REQUEST['orderno'] : "";
        $Order  =  Order::find_by_order_no($orderno);

        // Check order and shop
        if($Order && $Order->shop_id == $this->shopid)
        {
            if($Order->registered == 1)
            {
                $this->error = "Denne gave er allerede registreret som udleveret.";
                return $this->register();
            }
            else
            {
                $Order->registered = 1;
                $Order->registered_date = date('d-m-Y H:i:s');
                $Order->save();
                $this->addAppLog("REGISTER","Gave registreret som udleveret",$Order->shopuser_id,$Order->id,"");
                System::connection()->commit();

                header("Location: ".$this->getUrl("register&orderno=".$orderno));
            }
        }
        // Error in order or shopp
        else
        {
            return $this->register();
        }
    }


 public function doregister2()
    {
            $Order  =  Order::find_by_order_no($_POST['orderno']);
            $shop = Shop::find($Order->shop_id);
            if(!$shop->open_for_registration)
               throw new exception("Der er ikke åben for udlevering. Aktiver udlevering!");
            if($Order->registered == 1)
              throw new exception("Denne gave er allerede registreret som udleveret.");
            $Order->registered = 1;
            $Order->registered_date = date('d-m-Y H:i:s');
            $Order->save();

            $logItem = new AppLog();
            $logItem->app_username = $Order->user_username;
            $logItem->created_date = date('d-m-Y H:i:s');
            $logItem->company_id = $Order->company_id;
            $logItem->shop_id = $Order->shop_id;
            $logItem->shopuser_id = $Order->shopuser_id;
            $logItem->order_id = $Order->id;;
            $logItem->extradata = "";
            $logItem->log_event = "REGISTER";
            $logItem->log_description = "Gave registreret som udleveret";
            $logItem->save(false);

            $dummy = [];
            response::success(json_encode($dummy));

      }


    public function dounregister()
    {

        // Init and check login
        $this->init(false);
        if(!$this->isLoggedIn) return $this->register();

        // Check that shop is open
        if(!$this->shopObject->open_for_registration)
        {
            $this->error = "Der er ikke åben for udlevering. Aktiver udlevering!";
            return $this->register();
        }

        // Find order number and order
        $orderno = isset($_REQUEST['orderno']) ? $_REQUEST['orderno'] : "";
        $Order  =  Order::find_by_order_no($orderno);

        // Check order and shop
        if($Order && $Order->shop_id == $this->shopid)
        {
            // Action not set, dismiss action
            if($_POST["action"] != "unregister")
            {
                return $this->register();
            }
            // If not already registered, dismiss
            else if($Order->registered == 0)
            {
                $this->error = "Denne gave er ikke registreret som udleveret.";
                return $this->register();
            }
            // Perform unregister action
            else
            {
                $Order->registered = 0;
                $Order->registered_date = null;
                $Order->save();
                $this->addAppLog("UNREGISTER","Gave registreret som ikke-udleveret",$Order->shopuser_id,$Order->id,"");
                System::connection()->commit();



                header("Location: ".$this->getUrl("register&orderno=".$orderno));
            }
        }
        // Error in shop or order
        else
        {
            return $this->register();
        }
    }



    public function dounregister2()
    {

        $Order  =  Order::find_by_order_no($_POST['orderno']);
        $shop = Shop::find($Order->shop_id);
        if(!$shop->open_for_registration)
           throw new exception("Der er ikke åben for udlevering. Aktiver udlevering!");
        if($Order->registered == 0)
          throw new exception("Denne gave er ikke registreret som udleveret.");
        $Order->registered = 0;
        $Order->registered_date = null;
        $Order->save();

        $logItem = new AppLog();
        $logItem->app_username = $Order->user_username;
        $logItem->created_date = date('d-m-Y H:i:s');
        $logItem->company_id = $Order->company_id;
        $logItem->shop_id = $Order->shop_id;
        $logItem->shopuser_id = $Order->shopuser_id;
        $logItem->order_id = $Order->id;;
        $logItem->extradata = "";
        $logItem->log_event = "UNREGISTER";
        $logItem->log_description = "Gave registreret som ikke-udleveret";
        $logItem->save(false);

        $dummy = [];
        response::success(json_encode($dummy));
      }




    /*************************************
     * TEMPLATE FUNCTIONALITY
     *************************************/

private function authTop()
{



    ?><html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { margin: 0px; padding: 0px; font-size: 14px; font-family: verdana; overflow-x: hidden; }
        form { margin: 0px; padding: 0px; }

        table { font-size: 0.9em; }
        th { font-weight: bold; padding: 5px; text-align: left; border-bottom: 1px solid #555; }
        td { padding: 5px; border-bottom: 1px solid #aaa; }

        .regbtnstart { border: none; border-radius: 3px; display: inline-block; margin-top: 5px; padding: 6px; background: forestgreen; font-size: 0.8em; font-weight: bold; cursor: pointer; color: white; }
        .regbtnstop { border: none; border-radius: 3px; display: inline-block; margin-top: 5px; padding: 6px; background: red; font-size: 0.8em; font-weight: bold; cursor: pointer; color: white; }

    </style>
    <script src="<?php echo GFConfig::BACKEND_URL; ?>views/lib/jquery.min.js"></script>
</head>
<body>

<div style="background: #acd6ef; border-bottom: 1px solid #333;">

    <div style="float: right; padding: 4px;">
        <a href="<?php echo $this->getUrl("dashboard"); ?>">Forside</a> |
        <a href="<?php echo $this->getUrl("logout"); ?>">Log ud</a><br>
        <button type="button" class="regbtnstart" onClick="setRegistrationOpen()" <?php if($this->shopObject->open_for_registration == 1) echo "style=\"display: none;\""; ?>>Aktiver udlevering</button>
        <button type="button" class="regbtnstop" onClick="setRegistrationClosed()" <?php if($this->shopObject->open_for_registration == 0) echo "style=\"display: none;\""; ?>>Stop udlevering</button>
    </div>

    <div style="padding: 10px;">
        <form action="<?php echo $this->getUrl("search"); ?>" method="post" id="searchform">
            Søg efter navn / e-mail:<br>
            <input type="text" name="query" size="20" value="<?php echo isset($_POST["query"]) ? $_POST["query"] : ""; ?>">
            <input type="button" value="søg" id="searchbtn"><img src="http://system.gavefabrikken.dk/gfb_/images/ajax-loader.gif" style="vertical-align:middle; display: none;" id="searchimg">
        </form>

        <script>

            $(document).ready(function() {
               $('#searchbtn').bind('click',function() {
                   $('#searchbtn').hide();
                   $('#searchimg').show();
                   $('#searchform').submit();
               });
            });

        </script>
    </div>

</div>
<?php
}

private function authBottom($reloadOnStateChange=false)
{
?>

<script>

    function setRegistrationOpen()
    {
        $.post( '<?php echo GFConfig::BACKEND_URL; ?>index.php?rt=shop/openForRegistration',{"shop_id":<?php echo $this->shopid; ?>},function() {
            $('.regbtnstart').hide();
            $('.regbtnstop').show();
            <?php if($reloadOnStateChange) echo "location.reload();"; ?>
        });
    }

    function setRegistrationClosed()
    {
        $.post( '<?php echo GFConfig::BACKEND_URL; ?>index.php?rt=shop/closeForRegistration',{"shop_id":<?php echo $this->shopid; ?>},function() {
            $('.regbtnstart').show();
            $('.regbtnstop').hide();
            <?php if($reloadOnStateChange) echo "location.reload();"; ?>
        });
    }

</script>


</body>
</html><?php
}


    private function addAppLog($event,$description,$shopuserid=0,$orderid=0,$extradata="")
    {
        $logItem = new AppLog();
        $logItem->app_username = $_SESSION["gum_username"];
        $logItem->created_date = date('d-m-Y H:i:s');
        $logItem->company_id = $_SESSION["gum_companyid"];
        $logItem->shop_id = $_SESSION["gum_shopid"];
        $logItem->shopuser_id = $shopuserid;
        $logItem->order_id = $orderid;
        $logItem->extradata = $extradata;
        $logItem->log_event = $event;
        $logItem->log_description = $description;
        $logItem->save(false);

    }

    public function pullapphistory()
    {
        $companyid = $_GET["companyid"];
        $shopid = $_GET["shopid"];


        // Find shop attributes
        $searchAttributes = ShopAttribute::find_by_sql("SELECT * FROM shop_attribute WHERE shop_id = ".intval($shopid)." && (is_name = 1 || is_email = 1)");
        $attributeidlist = array();
        $nameAttributeid = 0;
        $emailAttributeid = 0;
        $locationAttributeid = 447;

        foreach($searchAttributes as $sa)
        {
            if($sa->is_name == 1) $nameAttributeid = $sa->id;
            if($sa->is_email == 1) $emailAttributeid = $sa->id;
            $attributeidlist[] = $sa->id;
        }

        // Attribute map
        $userAttributeMap = array();
        $sql = "SELECT * FROM user_attribute WHERE company_id = ".intval($companyid)." && attribute_id IN (".intval($nameAttributeid).",".intval($emailAttributeid).",".intval($locationAttributeid).")";
        $attributelist = UserAttribute::find_by_sql($sql);
        foreach($attributelist as $ua)
        {
            @$userAttributeMap[$ua->shopuser_id][$ua->attribute_id] = $ua->attribute_value;
        }

        //$loglist = AppLog::find_by_sql("SELECT * FROM app_log WHERE company_id = ".intval($companyid)." && shop_id = ".intval($shopid)." ORDER BY app_username ASC, created_date DESC");   ORDER BY app_username ASC, created_date DESC
//        $loglist = AppLog::find_by_sql("SELECT * FROM app_log WHERE company_id = ".intval($companyid)." && shop_id = ".intval($shopid)." && log_event = 'REGISTER' GROUP BY `shopuser_id` HAVING max(id) = id ");
          $loglist = AppLog::find_by_sql("Select * from app_log where  id in (SELECT MAX(id) FROM app_log where shop_id = 65 GROUP BY `shopuser_id`  ) && log_event = 'REGISTER' ORDER BY  app_username ASC, created_date DESC  ");

        // Define headlines
        $headlines = array("Brugernavn","Brugernavn lokation","Tidspunkt","Handling","Person","Person e-mail","Valgt lokation","Gavekort nr.","Gave","Model");

        $datalist = array();
        $onlyOne = array();


        foreach($loglist as $logitem)
        {
                $presentNo = "";
                $presentName = "";

                if($logitem->order_id > 0)
                {
                    $order = Order::find_by_pk($logitem->order_id,null);
                    if($order != null)
                    {
                        $presentNo = $order->order_no;
                        $presentName = $order->present_name;
                        $presentModelName = str_replace('###',' - ',$order->present_model_name);
                    }
                }


                $datalist[] = array(
                    $logitem->app_username,
                    $this->usernameToLocation($logitem->app_username),
                    $logitem->created_date->format('Y-m-d H:i:s'),
                    $logitem->log_description,
                    @$userAttributeMap[$logitem->shopuser_id][$nameAttributeid],
                    @$userAttributeMap[$logitem->shopuser_id][$emailAttributeid],
                    @$userAttributeMap[$logitem->shopuser_id][$locationAttributeid],
                    $presentNo,
                    $presentName,
                    $presentModelName
                );


        }

           // }

        // Force download
        header("Content-Type: application/octet-stream");
        header("Content-Transfer-Encoding: Binary");
        header("Content-disposition: attachment; filename=\"app_log.csv\"");

        // Output file
        echo trimgf(implode(";",$headlines))."\n";
        foreach($datalist as $dataline)
        {
            echo utf8_decode(implode(";",$dataline))."\n";
        }

    }

}

?>
