<?php

namespace GFBiz\Gavevalg;

use GFCommon\DB\ShopUser;

class ShopUserLogin
{

    /*
     * CLASS MEMBERS
     */

    private $lockShopList;

    private $loginValid = false;
    private $isToken = false;
    private $token;
    private $username;
    private $password;

    private $shopUser;
    private $shop;

    /**
     * ShopUserLogin constructor.
     * Do not use constructor to load data.
     * First call lockToShopID / lockToShopIDList / lockToConcept to lock to specific shops
     * Then call: createWithUsernamePassword to authenticate with username / password
     * Or call: createWithToken to check if a token is valid on the locked shops
     * $requireShopLock bool If login is locked to a specific shop id
     */

    public function __construct() {
    }


    /*
     * CHECK STATE AND ERROR HANDLING
     */

    /**
     * State and error handling
     * Checks if the call to createWithUsernamePassword or createWithToken was valid
     * @return bool
     */

    public function isLoginValid() {
        return ($this->loginValid == true && $this->shopUser != null && $this->shopUser->id > 0);
    }

    /**
     * Gets shopuser that is logged in
     * @return ShopUser
     */

    public function getShopUser() {
        return $this->shopUser;
    }

    /**
     * Get shop user is logged into
     * @returns Shop
     */
    public function getShop() {
        return $this->shop;
    }

    /**
     * Returns errorcode from invalid login attempt/check
     * @return int
     */
    public function getErrorCode()
    {
        return $this->loginErrorCode;
    }

    /**
     * Returns errormessage from invalid login attempt/check
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->loginErrorMessage;
    }

    /*
     * PUBLIC METHODS TO SETUP CHECK
     */

    /**
     * Lock to 1 or multiple shops by id or concept name
     * @param $shopid int Shop to lock login to
     */

    public function lockToShopID($shopid) {
        if(intval($shopid) > 0) {
            $this->lockShopList = array(intval($shopid));
        }
    }

    public function lockToShopIDList($shopIDList)
    {
        if(is_array($shopIDList) && countgf($shopIDList) > 0) {
            $this->lockShopList = array();
            foreach($shopIDList as $shopid) {
                if(intval($shopid) > 0) {
                    $this->lockShopList[] = intval($shopid);
                }
            }
        }
    }

    public function lockToConcept($conceptName) {
        $cardshopSettingsList = \CardshopSettings::find("all",array("conditions" => array("concept_parent" => $conceptName)));
        if(is_array($cardshopSettingsList) && countgf($cardshopSettingsList) > 0) {
            $this->lockShopList = array();
            foreach($cardshopSettingsList as $cardshopSettings) {
                $this->lockShopList[] = $cardshopSettings->shop_id;
            }
        }
    }


    /*
     * LOGIN CHECKS
     */

    /**
     * Verify user with username and password
     * @param $username Users username or cardno
     * @param $password Users password
     * @return bool If login is successful or not
     */

    public function loginWithUsernamePassword($username,$password)
    {
        if(!$this->checkSetup()) return false;

        $this->isToken = false;
        $this->username = $username;
        $this->password = $password;

        $this->verifyUsernamePassword();
        return $this->isLoginValid();
    }

    /**
     * Perform login using a token on shopuser
     * @param $token string Token to check on shopuser
     * @return bool If token is valid or not
     */
    public function loginWithToken($token)
    {
        if(!$this->checkSetup()) return false;

        $this->isToken = true;
        $this->token = $token;
        $this->verifyToken();
        return $this->isLoginValid();
    }


    /**
     * Create a token for a shopuser after login
     * @return string Token set on shopuser
     * @throws \Exception Throws exception if user is not logged in
     */
    public function createToken()
    {
        if(!$this->isLoginValid()) {
            throw new \Exception("User not logged in");
        }

        // Login ok, update shopuser
        $this->token = trimgf(NewGUID(), '{}');

        $this->shopUser->token = $this->token;
        $this->shopUser->token_created = date('d-m-Y H:i:s');
        $this->shopUser->save();

        return $this->token;
    }

    /*
     * PRIVATE HELPERS
     */

    private $loginErrorMessage = "";
    private $loginErrorCode = 0;

    private function setLoginOK($shopUser) {
        $this->shopUser = $shopUser;
        $this->loginValid = true;
        return true;
    }

    private function setLoginError($code,$error) {
        $this->loginErrorMessage = $error;
        $this->loginErrorCode = $code;
        return false;
    }

    private function checkSetup() {

        // Set default values
        $this->loginValid == false;
        $this->token = "";
        $this->username = "";
        $this->password = "";
        $this->shopUser = null;

        // Check if locked to shop
        if($this->lockShopList === null || !is_array($this->lockShopList) || count($this->lockShopList) == 0) {
            return $this->setLoginError(41,"No shop provided");
        }

        return true;

    }

    private function verifyToken()
    {

        // Check inputs
        if(trimgf($this->token) == "") {
            return $this->setLoginError(51,"No token provided");
        }

        // Get and check return of token
        $shopUsers = \ShopUser::find('all', array('conditions' => array('token=? and shop_id IN ('.implode(",",$this->lockShopList).')', ($this->token))));
        if(countgf($shopUsers) == 0) {
            return $this->setLoginError(52,"Token not recognized");
        }
        
        if(count($shopUsers) > 1) {
            return $this->setLoginError(53,"Multiple tokens, force new login");
        }

        // Verify shopuser
        $shopUser = $shopUsers[0];
        return $this->verifyShopUser($shopUser);
        
    }

    private function verifyUsernamePassword()
    {

        // Check inputs
        if(trimgf($this->username) == "" || trimgf($this->password) == "")
        {
            return $this->setLoginError(40,"Username or password missing");
        }

        // Get shopuser and check
        $shopUsers = \ShopUser::find('all', array('conditions' => array('LOWER(username)=? and LOWER(password) = ? and shop_id in ('.implode(",",$this->lockShopList).')', lowercase($this->username), lowercase($this->password))));
        if (countgf($shopUsers) == 0) {
            return $this->setLoginError(41,"Username or password incorrect");
        }
        
        // Get shopuser
        $shopUser = $shopUsers[0];

        // Verify shopuser
        return $this->verifyShopUser($shopUser);

    }

    private function verifyShopUser($shopUser)
    {

        $shop = \Shop::find($shopUser->shop_id);

        // Check demo shop
        if(!in_array($shopUser->shop_id,$this->lockShopList)) {
            return $this->setLoginError(41,"Not correct shop");
        }

        if($shop->login_check_strict == 1) {

            $usernameAttribute = \UserAttribute::find_by_sql("SELECT *  FROM `user_attribute` WHERE `shopuser_id` = ".intval($shopUser->id)." && is_username = 1");
            if(countgf($usernameAttribute) == 1) {
                $realUsername = $usernameAttribute[0]->attribute_value;
                if($this->username != $realUsername || $this->password != $shopUser->password) {
                    return $this->setLoginError(41,"Username or password incorrect");
                }
            }
        }

        // Check demo shop / user
        if($shop->is_demo==1 && $shop->demo_user_id != $shopUser->id) {
            return $this->setLoginError(42,"Cant log in to demo shop.");
        }

        // Check if shopuser is blocked
        if($shopUser->blocked==1 || $shopUser->shutdown == 1) {
            return $this->setLoginError(44,"Shopuser is blocked");
        }

        // Check if company is deactivated
        $c = \Company::find($shopUser->company_id);
        if($c->onhold == 1)
        {
            return $this->setLoginError(44,"Shopuser is blocked");
        }


        // hard close on specific orderid's
        $closeCOList = array(52320,52321,52322,52323,52324);
        if($shopUser->is_giftcertificate == 1 && in_array($shopUser->company_order_id,$closeCOList)) {
            return $this->setLoginError(46,"Gift certificate closed");
        }


        // Special rules for Tryg DK/NO/SE 2022, do not allow login after selecting present
        if(in_array($shopUser->shop_id,array(3083,3471,3834))) {
            $order = \Order::find_by_shopuser_id($shopUser->id);
            if($order != null) {
                return $this->setLoginError(46,"Order already registered.");
            }
        }

        // Special rules for shops with 24 hour re-selection (like delivery cardshops)
        if(in_array($shopUser->shop_id,array(4346))) {
            $order = \Order::find_by_shopuser_id($shopUser->id);
            if($order != null) {
                if($order->is_demo==0 && $order->order_timestamp->getTimestamp() < (time()-60*60*24))
                {
                    return $this->setLoginError(43,"Order already registered.");
                }
            }
        }

        // Gift certificate
        if($shopUser->is_giftcertificate==1)
        {
            if($shopUser->is_demo == 0) {

                // Is delivery
                if($shopUser->is_delivery == 1 && $shop->language_id != 4) {

                    // Check for order over 24 hours
                    $order = \Order::find_by_shopuser_id($shopUser->id);
                    if($order != null) {
                        if($order->is_demo==0 && $order->order_timestamp->getTimestamp() < (time()-60*60*24))
                        {
                            return $this->setLoginError(43,"Order already registered.");
                        }
                    }

                    // Check if delivery print date is set
                    if($shopUser->delivery_print_date != null)   {
                        return $this->setLoginError(43,"Order already registered.");
                    }
                }

                // Find company order
                try {
                    $companyOrder = \CompanyOrder::find($shopUser->company_order_id);
                    if($companyOrder->id > 0 && $companyOrder->floating_expire_date != null) {
                        $endOfDayTimestamp = strtotime('tomorrow', strtotime($companyOrder->floating_expire_date->format('Y-m-d'))) - 1;
                        if ($endOfDayTimestamp < time()) {
                            return $this->setLoginError(46,"Gift certificate closed");
                        }
                    }
                } catch (\Exception $e) {
                    $this->mailLog("Error in floating check: ".$e->getMessage());
                }

                // Get close time for card
                $shopOpen = $this->checkIsShopOpen($shopUser->shop_id,$shopUser->expire_date->format('Y-m-d'));

                // Check if card is closed
                if(!$shopOpen) {

                    $showDefaultPresentMessage = false;
                    if($shopUser->is_delivery == 0) {
                        $order = \Order::find_by_shopuser_id($shopUser->id);
                        if($order == null) {
                            $showDefaultPresentMessage = true;
                        }
                    }

                    if($showDefaultPresentMessage) {
                        return $this->setLoginError(47,"Gift certificate closed, default selected");
                    } else {
                        return $this->setLoginError(46,"Gift certificate closed");
                    }
                    
                }

            }
        }
        
        // Company shop
        else {

            // Currently no check for company shops

        }

        $this->shop = $shop;
        $this->setLoginOK($shopUser);

    }
    
    private function checkIsShopOpen($shopid,$expire_date) {
        return ShopCloseCheck::isShopOpen($shopid,$expire_date);
    }

    protected function mailLog($message) {
        $modtager = 'sc@interactive.dk';
        mailgf($modtager, "Login service error", $message."\r\n<br>\r\n<br>DUMP LOGIN CLASS:\r\n<br>\r\n<br><pre>".print_r($this)."</pre>");
    }


}