<?php

namespace GFBiz\Siteservice;

use GFBiz\Model\Cardshop\CardshopSettingsLogic;

class WebOrder extends ServiceHelper
{

    /**
     * CREATE ORDER FROM POST
     */

    public function createFromPost($shop_id)
    {

        $this->mailLog("WEBORDERDATA", "Web order init");


        // Hardcoded for se shop 440
        /*
        if($_POST["shop_id"] == 7735 || (isset($_POST["org_shop_id"]) && $_POST["org_shop_id"] == 7735)) {
            $_POST["shop_id"] = 1832;
            $shop_id = 1832;
            $_POST["bonus_cards"] = array(40);
        }
        */

        $_POST["expire_date_org"] = $_POST["expire_date"];
        $postProcessExpireDate = false;
        if(isset($_POST["expire_date"]) && $_POST["expire_date"] == "03-01-2024") {
            $postProcessExpireDate = true;
            $_POST["expire_date"] = "31-12-2023";
        }



        // Fix date for luksusgavekort
        if(isset($_POST["expire_date"]) && ($_POST["expire_date"] == "LUKS" || $_POST["expire_date"] == "LUKS-PRIV")) {
            $_POST["expire_date"] = "01-01-2030";
        }
        if(isset($_POST["expire_date"]) && $_POST["expire_date"] == "LUKS-VIRK") {
            $_POST["expire_date"] = "01-02-2030";
        }

        // Check web order log
        $existingOrder = \WebOrderLog::find('all',array("conditions" => array("input" => json_encode($_POST),"shop_id" => (isset($_POST["shop_id"]) ? intval($_POST["shop_id"]) : 0))));
        if(count($existingOrder) > 0) {
            foreach($existingOrder as $eo) {
                if(time()-strtotime($eo->created_datetime) < 3600) {
                    $this->validationError("cvr","exactlength","Order already created.",null,null,9);
                    return;
                }
            }
        }

        // Load shop data
        if(!$this->loadShopData($shop_id)) {
            return false;
        }

        // Validate inputs
        if(!$this->validateInput()) {
            return false;
        }

        // Run input rules
        if(!$this->runInputRules()) {
            return false;
        }

        // Create / find company
        $c = $this->createOrderCompany();

        // Check company id to make sure it is set
        if($c == null || !($c instanceof \Company) || $c->id == 0)
        {
            return $this->outputServiceError(41,"Could not find or create company");
        }

        // Add shop link
        $this->addShopLink($c);

        // Setup companyorder
        $co = $this->createOrderSetupOrder($c);

        // Check expire date
        $shopExpireDate = $this->shopSettings->getWeekByExpireDate($co->expire_date);
        if($shopExpireDate == null) {
            return $this->outputServiceError(42,"Invalid expire date");
        }

        // Check deadline
        $isEmail = intval($co->is_email) == 1;

        // Rule for converting card to e-mail if physical and only e-mail is open
        if($isEmail == 0 && $shopExpireDate->isPhysicalWebsaleOpen() == false && $shopExpireDate->isEmailWebsaleOpen() == true) {
            $isEmail = 1;
            $co->is_email = 1;
            $_POST["is_email"] = 1;
            $_POST["converted_to_email"] = 1;
        }

        if((($isEmail && $shopExpireDate->isEmailWebsaleOpen() == false) || (!$isEmail && $shopExpireDate->isPhysicalWebsaleOpen() == false))) {
            return $this->outputServiceError(43,"Sale on on this shop / deadline / ".($isEmail ? "email" : "physical")." cards have closed");
        }

        // Check card values
        $isValuesShop = trimgf($this->shopSettings->getSettings()->card_values) != "";
        $hasValuesInput = isset($_POST["values"]) && (is_array($_POST["values"]) || trimgf($_POST["values"]) != "");

        if(!$isValuesShop && $hasValuesInput) {
            return $this->outputServiceError(52,"Card values not allowed");
        } else if($isValuesShop && !$hasValuesInput) {
            return $this->outputServiceError(52,"Card values required");
        } else if($isValuesShop && $hasValuesInput) {

            $cardValues = explode(",",$this->shopSettings->getSettings()->card_values);

            // Check if $_POST
            if(is_array($_POST["values"])) {
                $inputValues = $_POST["values"];
            } else if(trimgf($_POST["values"]) != "") {
                $inputValues = explode(",",trimgf($_POST["values"]));
            } else {
                $inputValues = array();
            }

            $selectedValues = array();

            foreach($inputValues as $selectedValue) {
                if(in_array($selectedValue,$cardValues)) {
                    $selectedValues[] = $selectedValue;
                } else {
                    return $this->outputServiceError(52,"Card values not allowed: ".$selectedValue);
                }
            }

            if(count($selectedValues) == 0) {
                return $this->outputServiceError(52,"No card values selected");
            }

            // Sort selected values
            sort($selectedValues);
            $co->card_values = implode(",",$selectedValues);

            // Calculate average of card values and set as certificate value
            $avgValue = 0;
            foreach($selectedValues as $selectedValue) {
                $avgValue += intval($selectedValue);
            }
            $avgValue = $avgValue / count($selectedValues);
            $co->certificate_value = $avgValue;

        }

        // Bonus cards
        $isBonusShop = trimgf($this->shopSettings->getSettings()->bonus_presents) != "";
        $hasBonusInput = isset($_POST["bonus_cards"]) && (is_array($_POST["bonus_cards"])) && count($_POST["bonus_cards"]) > 0;

        $hasBonusSelected = false;
        $bonusAmountSelected = 0;

        if($isBonusShop && $hasBonusInput) {

            $bonusValues = explode(",",$this->shopSettings->getSettings()->bonus_presents);
            $selectedBonus = intval(trimgf($_POST["bonus_cards"][0]));

            // If selected is not in values
            if(!in_array($selectedBonus,$bonusValues)) {
                return $this->outputServiceError(53,"Bonus card not allowed: ".$selectedBonus);
            }

            $hasBonusSelected = true;
            $bonusAmountSelected = $selectedBonus;
            $co->card_values = intval($this->shopSettings->getSettings()->card_price/100).",".intval(($this->shopSettings->getSettings()->card_price+($selectedBonus*100))/100);

        } else if($isBonusShop) {
            
            $co->card_values = intval($this->shopSettings->getSettings()->card_price/100);
            
        }




        // Extract gift certificated
        $giftcertificates = $this->extractGiftcards($co,$shopExpireDate->getExpireDate());

        if(!is_array($giftcertificates) || countgf($giftcertificates) == 0) {
            return $this->outputServiceError(44,"Could not allocate gift certificates (0 extracted)");
        }

        // Set certificates
        $co->certificate_no_begin  =  $giftcertificates[0]->certificate_no;
        $co->certificate_no_end    =  $giftcertificates[countgf($giftcertificates)-1]->certificate_no;

        // Doublecheck that start to end is equal quantity
        if((intval($co->certificate_no_end)-intval($co->certificate_no_begin)+1) != $co->quantity) {
            return $this->outputServiceError(45,"Could not allocate certificates (interval jump/mismatch)");
        }

        // Save order
        $co->save();
        if($co->id == 0) {
            return $this->outputServiceError(46,"Could not save order");
        }

        // Update company state
        if($c->company_state == 0) {
            $c->company_state = 1;
            $c->save();
        }

        // Add to shop
        foreach($giftcertificates as $giftcertificate)  {
            \GiftCertificate::addToShop($giftcertificate->id,$this->shop->id,$c->id,$co->id,$co->card_values);
        }

        // Post process expire date
        if($postProcessExpireDate) {

            $co = \GFBiz\Model\Cardshop\CompanyOrderLogic::moveExpireDate($co->id,$_POST["expire_date_org"]);
            if($co == null) {
                return $this->outputServiceError(42,"Invalid expire date post-process!!");
            }

            $shopExpireDate = $this->shopSettings->getWeekByExpireDate($co->expire_date);
            if($shopExpireDate == null) {
                return $this->outputServiceError(42,"Invalid expire date post-process");
            }
        }

        // Create shop products
        $this->createShopProducts($co,$shopExpireDate,$hasBonusSelected,$bonusAmountSelected);

        // Send card shipments
        $this->sendCardShipments($co);

        // Send receipt
        if($this->cardshopSettings->language_code == 1) {
            $daKvittering = new OrderMailDA();
            $daKvittering->sendConfirmationEmail($co->contact_name,$co->contact_email);
        } else if($this->cardshopSettings->language_code == 4) {
            $noKvittering = new OrderMailNO();
            $noKvittering->sendConfirmationEmail($co->contact_name,$co->contact_email);
        } else if($this->cardshopSettings->language_code == 5) {
            $seKvittering = new OrderMailSE();
            $seKvittering->sendConfirmationEmail($co->contact_name,$co->contact_email);
        }

        $response = array("status" => 1, "order_no" => $co->order_no,"order_id" => $co->id,"quantity" => $co->quantity,"itemno" => $this->shopSettings->getSettings()->concept_code,"amount_ex_vat" => ($co->quantity*$this->shopSettings->getSettings()->card_price)/100,"amount_inc_vat" => ($co->quantity*$this->shopSettings->getSettings()->card_price*$this->shopSettings->getSettings()->card_moms_multiplier)/100);

        // Save to log
        $weborderlog = new \WebOrderLog();
        $weborderlog->error = "";
        $weborderlog->input = json_encode($_POST);
        $weborderlog->output = json_encode($response);
        $weborderlog->orderid = $co->id;
        $weborderlog->shop_id = isset($_POST["shop_id"]) ? intval($_POST["shop_id"]) : 0;
        $weborderlog->url = $_SERVER["REQUEST_URI"];
        $weborderlog->save();
  
        // Commit
        \system::connection()->commit();

        // Output ok and return
        //$this->mailLog("Web order created",print_r(array("status" => 1, "order_no" => $co->order_no,"order_id" => $co->id),true));
        $this->outputServiceSuccess($response);



        return true;

    }

    private function sendCardShipments($companyOrder)
    {
        try {
            $cardShipmentModel = new \GFBiz\Model\Cardshop\ShipmentLogic("giftcard");
            $cardShipmentModel->createDefault($companyOrder->id,true);
        } catch (\Exception $e) {
            return $this->outputServiceError(47,"Error creating card shipment");
        }
        return true;

    }



    /**
     * LOAD DATA
     */

    /** @var int */
    private $shopID;

    /** @var \Shop */
    private $shop;

    /** @var \CardshopSettings */
    private $cardshopSettings;

    /** @var CardshopSettingsLogic */
    private $shopSettings;
    private function loadShopData($shop_id)
    {

        // Set shop id
        $this->shopID = intval($shop_id);

        // Load cardshop
        try {
            $cardSettings = \CardshopSettings::find('all',array("conditions" => array("shop_id" => $this->shopID)));
            $this->cardshopSettings = $cardSettings[0];
        } catch (\Exception $e) {
            $this->outputServiceError(31,"Invalid shop_id ".$shop_id);
        }

        // Load shop
        $this->shop = \Shop::find($this->shopID);

        // Load settings logic
        $this->shopSettings = new CardshopSettingsLogic($this->shop->id);

        return true;
    }



    /**
     * ORDER VALIDATION
     */

    private function validateInput()
    {

        // Company
        $this->validateStringInput("companyname",true,2,50);
        $this->validateStringInput("cvr",true,8,16);

        if($this->cardshopSettings->language_code == 1) {
            $this->validateStringInput("ean",false,13,13);
            $this->validateIntegerInput("ean",false,1000000000000,9999999999999);
        }

        $this->validateStringInput("phone",false,8,12);

        // Contact
        $this->validateStringInput("contact_name",true,3,50);
        $this->validateStringInput("contact_phone",true,3,50);
        $this->validateEmailInput("contact_email",true);

        // Bill to address
        $this->validateStringInput("bill_to_address",true,3,50);
        $this->validateStringInput("bill_to_postal_code",true,3,20);
        $this->validateStringInput("bill_to_city",true,3,30);
        $this->validateStringInput("bill_to_country",true,2,2);

        if(isset($_POST["bill_to_email"]) || $this->cardshopSettings->language_code == 4) {
            $this->validateEmailInput("bill_to_email",true);
        }

        // Ship to address
        if($this->getPostInt("use_shipping_address") == 1) {
            $this->validateStringInput("ship_to_company",true,3,50);
            $this->validateStringInput("ship_to_address",true,3,50);
            $this->validateStringInput("ship_to_postal_code",true,3,20);
            $this->validateStringInput("ship_to_city",true,3,30);
            $this->validateStringInput("ship_to_country",true,2,2);
        }
        else {
            $_POST["ship_to_company"] = $_POST["companyname"];
            $_POST["ship_to_address"] = $_POST["bill_to_address"];
            $_POST["ship_to_address_2"] = $this->getPostString("bill_to_address_2");
            $_POST["ship_to_postal_code"] = $_POST["bill_to_postal_code"];
            $_POST["ship_to_city"] = $_POST["bill_to_city"];
            $_POST["ship_to_country"] = $_POST["bill_to_country"];
        }

        // Order
        $this->validateIntegerInput("shop_id",true,1,999999);
        //$this->validateIntegerInput("quantity",true,5,100);

        $minAmount = 5;
        if(in_array($this->shopID,array(2961,2963,2962,2960)) && isset($_POST["is_email"]) && $_POST["is_email"] == 1) {
            $minAmount = 1;
        }
        
        if(in_array($this->shopID,array(290))) {
            $minAmount = 10;
        }

        $this->validateIntegerInput("quantity",true,$minAmount,200);
        $this->validateStringInput("expire_date",true,10,10);
        $this->validateIntegerInput("is_email",true,0,1);
        $this->validateIntegerInput("giftwrap",false,0,2);
        $this->validateIntegerInput("namelabel",false,0,1);
        $this->validateStringInput("requisition_no",false,0,150);

        return true;

    }

    private function validateEmailInput($name,$required) {

        if(!$this->validateStringInput($name,$required,3,50)) {
            return false;
        }
        else if (isset($_POST[$name]) && !filter_var($this->getPostString($name), FILTER_VALIDATE_EMAIL)) {
            return $this->validationError($name,"invalidemail","not a valid e-mail");
        }
        return true;
    }

    private function validateIntegerInput($name,$required=true,$minValue=0,$maxValue=99999999) {

        if(!isset($_POST[$name])) {
            if($required == true) {
                return $this->validationError($name,"required","value is required");
            } else {
                return true;
            }
        }
        else{
            $val = intval(trimgf($_POST[$name]));

            if(trimgf($_POST[$name]) == "") {
                return $this->validationError($name,"empty","value is empty");
            } else if($minValue > $val) {
                return $this->validationError($name,"lowvalue","value must be at least ".$minValue,$minValue);
            }
            else if($maxValue < $val) {
                return $this->validationError($name,"highvalue","value must be under ".$maxValue,null,$maxValue);
            }

            return true;

        }


    }

    private function validateStringInput($name,$required=true,$minSize=0,$maxSize=1000)
    {

        if(!isset($_POST[$name])) {
            if($required == true) {
                return $this->validationError($name,"required","value is required");
            } else {
                return true;
            }
        }
        else{
            $val = trimgf($_POST[$name]);
            $len = strlen($val);

            if($minSize > 0 && $val == "") {
                return $this->validationError($name,"empty","string must be at least ".$minSize." characters",$minSize);
            } else if($minSize > $len) {
                return $this->validationError($name,"tooshort","string must be at least ".$minSize." characters",$minSize);
            }
            else if($maxSize < $len) {
                return $this->validationError($name,"toolong","string must not be longer than ".$maxSize." characters",$maxSize);
            }

            return true;

        }


    }

    private function validationError($field,$type,$message,$min=null,$max=null,$length=null)
    {
        $this->outputServiceError(40,"Validation error on field ".$field.": ".$message,true,array("field" => $field,"type" => $type,"min" => $min,"max" => $max,"length" => $length));
        return false;
    }



    /**
     * SPECIAL UPDATES TO DATA
     */

    private function runInputRules()
    {

        // CVR number in denmark
        if($this->cardshopSettings->language_code == 1) {

            $cvr = $this->getPostString("cvr");
            $cvr = trimgf(str_replace(array("_","-",".",",","-"," "),"",$cvr));
            $cvr = intval($cvr);

            if($cvr <= 0 || strlen($cvr."") != 8) {
                $this->validationError("cvr","exactlength","CVR must be 10 digit number ",null,null,8);
            } else {
                $_POST["cvr"] = $cvr;
            }

        }

        // CVR number in sweden
        if($this->cardshopSettings->language_code == 5) {

            $cvr = $this->getPostString("cvr");
            $cvr = trimgf(str_replace(array("_","-",".",",","-"),"",$cvr));
            $cvr = intval($cvr);

            if($cvr <= 0 || strlen($cvr."") != 10) {
                $this->validationError("cvr","exactlength","CVR must be 10 digit number ",null,null,10);
            } else {
                $_POST["cvr"] = substr($cvr,0,6)."-".substr($cvr,6,4);
            }
            
            if(isset($_POST["bill_to_postal_code"])) {
                $_POST["bill_to_postal_code"] = str_replace(" ","",trimgf($_POST["bill_to_postal_code"]));
                if(strlen($_POST["bill_to_postal_code"]) == 5) {
                    $_POST["bill_to_postal_code"] = substr($_POST["bill_to_postal_code"],0,3)." ".substr($_POST["bill_to_postal_code"],3);
                }
            }

            if(isset($_POST["ship_to_postal_code"])) {
                $_POST["ship_to_postal_code"] = str_replace(" ","",trimgf($_POST["ship_to_postal_code"]));
                if(strlen($_POST["ship_to_postal_code"]) == 5) {
                    $_POST["ship_to_postal_code"] = substr($_POST["ship_to_postal_code"],0,3)." ".substr($_POST["ship_to_postal_code"],3);
                }
            }

        }

        // CVR number in norway
        if($this->cardshopSettings->language_code == 4) {
            $cvr = $this->getPostString("cvr");
            $cvr = trimgf(str_replace(array(" ","_","-",".",",","-"),"",$cvr));
            $cvr = intval($cvr);
            if($cvr <= 0 || strlen($cvr."") != 9) {
                $this->validationError("cvr","exactlength","CVR must be 9 digit number ",null,null,9);
            }
            $_POST["cvr"] = $cvr;
        }

        return true;

    }

    /**
     * Find or create company
     */

    private function createOrderCompany()
    {

        // Get company inputs
        $c = new \Company();
        $c->name = $this->getPostString("companyname");
        $c->phone = $this->getPostString("phone");
        $c->website = "";
        $c->language_code = $this->cardshopSettings->language_code;
        $c->cvr = $this->getPostString("cvr");
        $c->ean = $this->getPostString("ean");
        $c->username = $this->getPostString("contact_email");

        $c->bill_to_address = $this->getPostString("bill_to_address");
        $c->bill_to_address_2 = $this->getPostString("bill_to_address_2");
        $c->bill_to_postal_code = $this->getPostString("bill_to_postal_code");
        $c->bill_to_city = $this->getPostString("bill_to_city");
        $c->bill_to_country = $this->getPostString("bill_to_country");
        $c->bill_to_email = $this->getPostString("bill_to_email");

        if($this->getPostInt("use_shipping_address") == 1) {
            $c->ship_to_company = $this->getPostString("ship_to_company");
            $c->ship_to_address = $this->getPostString("ship_to_address");
            $c->ship_to_address_2 = $this->getPostString("ship_to_address_2");
            $c->ship_to_postal_code = $this->getPostString("ship_to_postal_code");
            $c->ship_to_city = $this->getPostString("ship_to_city");
            $c->ship_to_country = $this->getPostString("ship_to_country");
            $c->ship_to_attention = $this->getPostString("ship_to_attention");
        } else {
            $c->ship_to_company = $this->getPostString("companyname");
            $c->ship_to_address = $this->getPostString("bill_to_address");
            $c->ship_to_address_2 = $this->getPostString("bill_to_address_2");
            $c->ship_to_postal_code = $this->getPostString("bill_to_postal_code");
            $c->ship_to_city = $this->getPostString("bill_to_city");
            $c->ship_to_country = $this->getPostString("bill_to_country");
            $c->ship_to_attention = $this->getPostString("contact_name");
        }

        if(trimgf($c->ship_to_attention) == "") {
            $c->ship_to_attention = $this->getPostString("contact_name");
        }

        $c->contact_name = $this->getPostString("contact_name");
        $c->contact_phone = $this->getPostString("contact_phone");
        $c->contact_email = $this->getPostString("contact_email");

        // Set default company
        $c->so_no = "";
        $c->sales_person = "";
        $c->gift_responsible = "";
        $c->footer = "";
        $c->logo = "";
        $c->password = $this->createOrderPasswordGenerate(8);
        $c->active = 1;
        $c->deleted = 0;
        $c->pick_group = "";
        $c->is_gift_certificate = 1;
        $c->address_updated = 0;
        $c->internal_note = "";
        $c->rapport_note = "";
        $c->company_state = 1;

        // Search for existing company
        $companyMatch = \Company::find('all', array('conditions' => array('pid = 0 && (language_code = '.$this->cardshopSettings->language_code.' && (cvr != "" and cvr is not null and cvr like ?) OR (ean != "" and ean is not null and ean like ?)) and active = 1 and deleted = 0 and ship_to_postal_code LIKE ? and is_gift_certificate = 1 and ship_to_address LIKE ? and contact_email LIKE ?', $c->cvr,$c->ean,$c->ship_to_postal_code,$c->ship_to_address,$c->contact_email)));

        // Validate company and save new company
        if(count($companyMatch) == 0)
        {
            $c->save();
        }
        else
        {
            $c = $companyMatch[0];
        }

        return $c;

    }

    
    private function addShopLink($company)
    {
        $shopLinks = \CompanyShop::find("all",array("conditions" => array("shop_id" => $this->shopID,"company_id" => $company->id)));
        if(count($shopLinks) == 0) {
            
            // Add company to shop
            try {
                $companyshop = new \CompanyShop();
                $companyshop->company_id = $company->id;
                $companyshop->shop_id = $this->shop->id;
                $companyshop->save();
            } catch(Exception $e) {}
            
        }
    }

    private function createOrderSetupOrder($c)
    {

        // Create order
        $co = new \CompanyOrder();

        $co->quantity = $this->getPostInt("quantity");
        $co->expire_date = $this->getPostString("expire_date");
        $co->is_email = $this->getPostInt("is_email") == 1 ? 1 : 0;

        if($this->getPostInt("use_shipping_address") == 1) {
            $co->ship_to_company = $this->getPostString("ship_to_company");
            $co->ship_to_address = $this->getPostString("ship_to_address");
            $co->ship_to_address_2 = $this->getPostString("ship_to_address_2");
            $co->ship_to_postal_code = $this->getPostString("ship_to_postal_code");
            $co->ship_to_city = $this->getPostString("ship_to_city");
            $co->ship_to_country = $this->getPostString("ship_to_country");
        } else {
            $co->ship_to_company = $this->getPostString("companyname");
            $co->ship_to_address = $this->getPostString("bill_to_address");
            $co->ship_to_address_2 = $this->getPostString("bill_to_address_2");
            $co->ship_to_postal_code = $this->getPostString("bill_to_postal_code");
            $co->ship_to_city = $this->getPostString("bill_to_city");
            $co->ship_to_country = $this->getPostString("bill_to_country");
        }


        $co->contact_name = $this->getPostString("contact_name");
        $co->contact_phone = $this->getPostString("contact_phone");
        $co->contact_email = $this->getPostString("contact_email");


        $co->giftwrap = $this->getPostInt("giftwrap") == 1 ? 1 : 0;
        $co->name_label = $this->getPostInt("giftwrap") == 2 ? 1 : 0;
        $co->requisition_no = $this->getPostString("requisition_no");
        //$co->ordernote = $this->getPostString("ordernote");
        //$co->earlyorder = $this->getPostInt("earlyorder") == 1 ? 1 : 0;

        // Default fields
        $co->cvr = $c->cvr;
        $co->ean = $c->ean;
        $co->salesperson = "IMPORT";
        $co->salenote = "";
        $co->is_printed = 0;
        $co->is_shipped = 0;
        $co->is_invoiced = 0;
        $co->spdeal = "";
        $co->spdealtxt = "";
        $co->is_cancelled = 0;
        $co->is_appendix_order = 0;
        $co->freight_calculated = 0;
        $co->navsync_status = 200;
        $co->nav_on_hold = 0;
        $co->order_state = 1;
        $co->shipment_ready = 0;
        $co->shipment_on_hold = 0;
        $co->prepayment = 1;

        // Check reference, if MHS set salesperson to MHS
        if($this->cardshopSettings->language_code == 1 && (trim(strtolower($co->requisition_no)) == "mhs" || trim(strtolower($co->requisition_no)) == "ms")) {
            $co->salesperson = "MHS";
        }

        // Check reference, if MHS set salesperson to MHS
        if($this->cardshopSettings->language_code == 5 && (trim(strtolower($co->requisition_no)) == "mhs" || trim(strtolower($co->requisition_no)) == "ms")) {
            $co->salesperson = "MS";
        }

        // Calculated fields
        $system = \system::first();
        $co->order_no = \Numberseries::getNextNumber($system->company_order_nos_id);
        $co->company_id = $c->id;
        $co->company_name = $c->name;
        $co->shop_id = $this->shop->id;
        $co->shop_name = $this->shop->name;
        $co->certificate_no_begin = "";
        $co->certificate_no_end = "";
        $co->certificate_value = $this->shop->card_value;

        return $co;
    }

    private function extractGiftcards($companyOrder,$expire_date)
    {

        // Find gift certificates
        try{
            if($companyOrder->is_email == 1) {
                $giftcertificates = \GiftCertificate::findBatchEmail($this->shop->id,$companyOrder->quantity,$expire_date,$this->shop->reservation_group);
            }   else {
                $giftcertificates = \GiftCertificate::findBatchPrint($this->shop->id,$companyOrder->quantity,$expire_date,$this->shop->reservation_group);
            }
        } catch (Exception $e) {
            return $this->outputServiceError(44,"Could not allocate gift certificates");
        }

        // No cards extracted
        return $giftcertificates;
    }

    private function createShopProducts($companyOrder,$shopExpireDate,$hasBonusSelected=false,$bonusAmountSelected=0)
    {
        // Create default order items
        foreach($this->shopSettings->getProducts() as $productItem) {

            $coi = new \CompanyOrderItem();
            $coi->companyorder_id = $companyOrder->id;
            $coi->quantity = ($productItem->isPerCard() ? $companyOrder->quantity : 1);
            $coi->type = $productItem->getCode();
            $coi->price = $productItem->getPrice();
            $coi->isdefault = 1;

            if($companyOrder->card_values != null && $coi->type == "CONCEPT") {
                $coi->price = $companyOrder->certificate_value*100;
            }

            if(!$productItem->isDefault()) {
                $coi->quantity = 0;
            }

            if($productItem->getExtraDataField("restrict") == "privatedelivery" && $shopExpireDate->isDelivery() == false) {
                $coi->quantity = 0;
            }

            if($productItem->getExtraDataField("restrict") == "physicalcards" && $companyOrder->is_email == 1) {
                $coi->quantity = 0;
            }

            // Set card fee on danish
            if($coi->type == "CARDFEE" && $companyOrder->is_email == 0 && $this->cardshopSettings->cardfee_use == 1) {
                $coi->quantity = 1;
            }

            // Set card delivery
            if($coi->type == "CARDDELIVERY") {
                if ($companyOrder->is_email == 0) {
                    $coi->quantity = 1;
                } else {
                    $coi->quantity = 0;
                }
            }

            // Set private delivery
            if($coi->type == "PRIVATEDELIVERY") {
                if($shopExpireDate->isDelivery() || $this->shopSettings->getSettings()->language_code == 5) {
                    $coi->quantity = 1;
                } else {
                    $coi->quantity = 0;
                }
            }

            // Set giftwrap
            if($coi->type == "GIFTWRAP") {
                if($companyOrder->giftwrap == 1 || $this->getPostInt("giftwrap") == 1) {
                    $coi->quantity = 1;
                } else {
                    $coi->quantity = 0;
                }

            }

            // Set namelabel
            if($coi->type == "NAMELABELS" && $this->shopSettings->getSettings()->namelabels_use > 0) {
                if($companyOrder->name_label == 1 || $this->getPostInt("namelabels") == 1) {
                    $coi->quantity = 1;
                } else {
                    $coi->quantity = 0;
                }
            }

            // Bonus presents
            if($coi->type == "BONUSPRESENTS") {
                if(!$hasBonusSelected) {
                    $coi->quantity = 0;
                } else {
                    $coi->quantity = $companyOrder->quantity;
                    $coi->price = intval($bonusAmountSelected*100);
                }
            }

            $coi->save();

        }
    }

    /********************************** OLD STYLE ORDER *************************************/

/*
    public function createTEMP()
    {

        // Send gavekort e-mails
        if($co->is_email == 1)
        {
            $this->sendMailOrderEmail($co);
        }

        // Send confirmation email to contact
        if(in_array($co->shop_id,array(272,57,58,59,574)))
        {
            $this->sendconfirmmailno($co->contact_name,$co->contact_email);
        }
        else if(in_array($co->shop_id,array(1832,1981)))
        {
            $this->sendconfirmmailse($co->contact_name,$co->contact_email);
        }
        else
        {
            $this->sendconfirmmailda($co->contact_name,$co->contact_email);
        }

        system::connection()->commit();

        // Return success
        $this->outputServiceSuccess(array("id" => $co->id));

    }
*/


    /**
     * HELPERS
     */

    private function getPostString($key) {
        return (isset($_POST[$key]) ? htmlspecialchars(trimgf($_POST[$key]), ENT_QUOTES, 'UTF-8') : "");
    }

    private function getPostInt($key) {
        return isset($_POST[$key]) ? intval($_POST[$key]) : 0;
    }

    public static function createOrderPasswordGenerate($length=8)
    {
        // Variable til adgangskode
        $passstring = "1234567890qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM123456789";
        $pass = "";

        // Generer adgangskode
        for($i=0;$i<intval($length);$i++)
            $pass .= $passstring[rand(0,(strlen($passstring)-1))];

        // Returner adgangskode
        return $pass;
    }

}
