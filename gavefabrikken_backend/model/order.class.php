<?php
// Model Order
// Date created  Mon, 16 Jan 2017 15:27:20 +0100
// Created by Bitworks
//***************************************************************
//   (PRI) id                            int(11)             NO
//   (UNI) order_no                      int(11)             NO
//   (   ) order_timestamp               datetime            NO
//   (MUL) shop_id                       int(11)             NO
//   (   ) shop_is_gift_certificate      tinyint(4)          YES
//   (   ) shop_is_company               tinyint(4)          YES
//   (   ) company_id                    int(11)             NO
//   (   ) company_name                  varchar(100)        NO
//   (   ) company_cvr                   varchar(15)         NO
//   (   ) company_pick_group            varchar(15)         YES
//   (UNI) shopuser_id                   int(11)             NO
//   (   ) user_username                 varchar(250)        NO
//   (   ) user_email                    varchar(250)        NO
//   (   ) user_name                     varchar(250)        NO
//   (MUL) present_id                    int(11)             NO
//   (   ) present_no                    varchar(250)        NO
//   (   ) present_name                  varchar(100)        NO
//   (   ) present_internal_name         varchar(100)        NO
//   (   ) present_vendor                varchar(100)        YES
//   (   ) present_copy_of               int(11)             YES
//   (   ) present_shop_id               int(11)             YES
//   (   ) present_model_id              int(11)             YES
//   (   ) present_model_name            varchar(250)        YES
//   (   ) present_model_present_no      varchar(250)        YES
//   (   ) gift_certificate_no           varchar(20)         YES
//   (   ) gift_certificate_value        int(11)             YES
//   (   ) gift_certificate_week_no      int(11)             YES
//   (   ) gift_certificate_start_date   date                YES
//   (   ) gift_certificate_end_date     date                YES
//   (   ) registered                    tinyint(4)          YES
//   (   ) registered_date               date                YES
//   (   ) is_demo                       tinyint(4)          YES
//   (   ) language_id                   int(11)             YES
//   (   ) freight_calculated            tinyint(4)          YES
//***************************************************************



class Order extends BaseModel
{
    static $table_name  = "order";
    static $primary_key = "id";

    //Relations
    static $has_many =   array(
        array('attributes_', 'class_name' => 'OrderAttribute')
    );

    static $before_create =  array('onBeforeCreate');
    static $after_create  =  array('onAfterCreate');
    static $before_update =  array('onBeforeUpdate');
    static $after_update  =  array('onAfterUpdate');
    static $after_destroy =  array('onAfterDestroy');


    // Trigger functions
    function onBeforeCreate()
    {
        $this->validateFields();
    }

    function onAfterCreate()
    {

        //  Load order by id
        if ($this->id > 0) {
            $order = Order::find($this->id);
            $this->order_no = $order->order_no;
        } else {
            echo "Could not find new orders order_no (" . $this->id . ")";
            exit();
        }
    }

    function onBeforeUpdate()
    {
        $this->validateFields();
    }

    function onAfterUpdate() {}
    function onBeforeDestroy() {}
    function onAfterDestroy()
    {
        OrderAttribute::table()->delete(array('order_id' => $this->id));
        OrderPresentEntry::table()->delete(array('order_id' => $this->id));
    }

    function validateFields()
    {
        //testRequired($this,'order_no');
        testRequired($this, 'order_timestamp');
        testRequired($this, 'shop_id');
        testRequired($this, 'company_id');
        testRequired($this, 'company_name');
        //testRequired($this,'company_cvr');
        testRequired($this, 'shopuser_id');
        testRequired($this, 'user_username');
        testRequired($this, 'user_email');
        testRequired($this, 'present_id');
        testRequired($this, 'present_no');
        testRequired($this, 'present_name');
        testRequired($this, 'present_internal_name');

        //testMaxLength($this,'order_no',20);
        testMaxLength($this, 'company_name', 100);
        testMaxLength($this, 'company_cvr', 15);
        testMaxLength($this, 'company_pick_group', 15);
        testMaxLength($this, 'user_username', 250);
        testMaxLength($this, 'user_email', 250);
        testMaxLength($this, 'user_name', 250);
        testMaxLength($this, 'present_no', 250);
        testMaxLength($this, 'present_name', 100);
        testMaxLength($this, 'present_internal_name', 100);
        testMaxLength($this, 'present_vendor', 100);
        testMaxLength($this, 'present_model_name', 250);
        testMaxLength($this, 'gift_certificate_no', 20);

        $this->order_no = intval($this->order_no);
        $this->company_name = trimgf($this->company_name);
        $this->company_cvr = trimgf($this->company_cvr);
        $this->company_pick_group = trimgf($this->company_pick_group);
        $this->user_username = trimgf($this->user_username);
        $this->user_email = trimgf($this->user_email);
        $this->user_name = trimgf($this->user_name);
        $this->present_no = trimgf($this->present_no);
        $this->present_name = trimgf($this->present_name);
        $this->present_internal_name = trimgf($this->present_internal_name);
        $this->present_vendor = trimgf($this->present_vendor);
        $this->present_model_name = trimgf($this->present_model_name);
        $this->gift_certificate_no = trimgf($this->gift_certificate_no);
    }

    //---------------------------------------------------------------------------------------
    // Static CRUD Methods
    //---------------------------------------------------------------------------------------


    static public function createOrder($data)
    {


        // Load shop and shop user
        $ShopUser = ShopUser::find($data['userId']);
        $shop = Shop::find($ShopUser->shop_id);

        //Check at shop stadig er aktiv
        if ($shop->active == 0) {
            throw new Exception('closed');
        }

        //Spær for demobrugere
        if ($ShopUser->is_demo) {
            throw new exception('Demobrugere kan ikke vælge gave');
        }

        //Check om bruger er spærret eller MI's testshop der bruges som demo
        $blockedCompanyID = array(61469,20996,13554);
        
        if ($ShopUser->blocked == 1 || $ShopUser->shutdown == 1 || in_array($ShopUser->company_id,$blockedCompanyID)) {
            throw new Exception('Denne bruger er spærret');
        }

        //Check om gavekort er udløbet
        if ($ShopUser->is_giftcertificate == 1) {
            $shopOpen = GFBiz\Gavevalg\ShopCloseCheck::isShopOpen($ShopUser->shop_id, $ShopUser->expire_date);
            if (!$shopOpen) {
                throw new Exception('closed');
            }
        }

        // If giftcertificate, check shipment status
        if ($ShopUser->is_giftcertificate == 1 && intvalgf($ShopUser->username) > 0) {

            // Tjek om forsendelse er oprettet
            $shipmentList = Shipment::find_by_sql("SELECT * FROM shipment WHERE from_certificate_no = " . intvalgf($ShopUser->username) . " && shipment_type in ('privatedelivery','directdelivery')");
            if (count($shipmentList) > 0) {

                // If waiting, remove and reset shop_user
                if ($shipmentList[0]->shipment_state <= 1) {

                    // Remove shipment
                    $shipment = Shipment::find($shipmentList[0]->id);
                    $shipment->delete();

                    // Update shop user
                    $ShopUser->delivery_state = 0;
                    $ShopUser->delivery_print_date = null;
                    $ShopUser->save();
                }

                // Is sent, reject order
                else {
                    throw new Exception('closed');
                }
            }
        }

        // Lock order tables to insert and make other requests wait
        lockTable('`order`');
        lockTable('order_attribute');
        lockTable('order_history');

        $previousOrder = null;

        // Delete any existing order for this user
        $orders = Order::all(array('shopuser_id' => $data['userId']));
        foreach ($orders as $order) {
            $previousOrder = $order;
            $order->delete();
        }

        // Create new order and set data - start with order history
        $order = new OrderHistory();
        $order->order_no = 0;
        $order->is_demo = ($ShopUser->is_demo ? 1 : 0);
        $order->order_timestamp = date('d-m-Y H:i:s');

        // Set shop info
        $order->shop_id = $shop->id;
        $order->shop_is_gift_certificate  = $shop->is_gift_certificate;
        $order->shop_is_company = $shop->is_company;

        // User information
        $order->shopuser_id  = $ShopUser->id;
        $order->is_delivery =  $ShopUser->is_delivery;
        $order->user_email = '';
        $order->user_username = '';
        $order->user_name = '';

        // If gift certificate, update information
        if ($ShopUser->is_giftcertificate == 1) {
            $order->gift_certificate_no         = $ShopUser->username;
            $order->gift_certificate_end_date   = $ShopUser->expire_date;
        }

        // Language
        if (isset($data['langId'])) {
            $order->language_id = $data['langId'] + 1;
        } else {
            $prevOrder = OrderHistory::find_by_sql("SELECT * FROM order_history WHERE shopuser_id = " . intval($ShopUser->id));
            if (isset($prevOrder[0])) {
                $order->language_id = $prevOrder[0]->language_id;
            } else {
                $order->language_id = 1;
            }
        }




        // Set company information
        $company = Company::find($ShopUser->company_id);
        $order->company_id   = $company->id;
        $order->company_name  = $company->name;
        $order->company_cvr  = $company->cvr;
        $order->company_pick_group = $company->pick_group;

        // Set present details
        $present = Present::find($data['presentsId']);
        $order->present_id = $present->id;
        $order->present_no  = "none";        // hardkodet da vi ikke bruger den;
        $order->present_name  = $present->name;
        $order->present_internal_name = $present->internal_name;
        $order->present_vendor = $present->vendor;
        $order->present_copy_of  = $present->copy_of;
        $order->present_shop_id  = $present->shop_id;





        // Set model details
        $order->present_model_name = $data['model'] == "###" ? "" : $data['model'];
        $order->present_model_present_no =  $data['modelData'];
        $order->present_model_id = isset($data['model_id']) ? $data['model_id'] : 0;

        // Use decription from db not from post data.   
        $varenrModel = PresentModel::find_by_sql("SELECT * FROM present_model WHERE present_id = " . intval($order->present_id) . " && model_id = " . intval($order->present_model_id) . " && language_id IN (" . intval($order->language_id) . ") && model_present_no != ''");
        if (count($varenrModel) > 0) {
            $data['model'] = $varenrModel[0]->model_name;
            $order->present_model_name = $varenrModel[0]->model_name;
        }


        if ($present->shop_id != $shop->id) {
            throw new exception('shop mismatch');
        }

        // Check model
        if ($present->has_models()) {

            if ($order->present_model_present_no == "") {

                // Look up varenr in danish or norweigan
                $varenrModel = PresentModel::find_by_sql("SELECT * FROM present_model WHERE present_id = " . intval($order->present_id) . " && model_id = " . intval($order->present_model_id) . " && language_id IN (1,4) && model_present_no != ''");
                if (count($varenrModel) > 0) {
                    $order->present_model_present_no = $varenrModel[0]->model_present_no;
                }


                // Check varenr again
                if ($order->present_model_present_no == "") {
                    throw new exception('model data missing');
                }
            } else if ($order->present_model_present_no == "undefined") {
                throw new exception('model data undefined');
            }
        }






        // Process user attributes
        $attributes = (array)json_decode($data['_attributes']);
        foreach ($attributes as $attribute) {
            $userattribute = UserAttribute::all(array('shopuser_id' => $data['userId'], 'attribute_id' => $attribute->feltKey))[0];
            $userattribute->attribute_value = htmlspecialchars($attribute->feltVal, ENT_QUOTES, 'UTF-8');
            $userattribute->save();
        }

        $deliveryAttributeCount = 0;
        $deliveryAttributeSet = 0;

        // Load shop attributes and reindex to map by attribute_id
        $shopAttributeList = ShopAttribute::find_by_sql("SELECT * FROM shop_attribute WHERE shop_id = " . intval($shop->id));
        $shopAttributeMap = array();
        foreach ($shopAttributeList as $sAttr) {
            $shopAttributeMap[$sAttr->id] = $sAttr;
            if ($sAttr->is_delivery == 1) {
                $deliveryAttributeCount++;
            }
        }


        // Go through all shop user attributes and create order attributes, do not save yet
        $orderAttributeList = array();
        foreach ($ShopUser->attributes_ as $attribute) {
            if (!$attribute->is_password) {
                if (isset($shopAttributeMap[$attribute->attribute_id])) {

                    $shopattribute = $shopAttributeMap[$attribute->attribute_id];
                    $orderattribute = new OrderHistoryAttribute();
                    $orderattribute->shop_id = $shop->id;
                    $orderattribute->shopuser_id = $ShopUser->id;
                    $orderattribute->company_id = $company->id;
                    $orderattribute->attribute_name = $shopattribute->name;
                    $orderattribute->attribute_id = $attribute->attribute_id;
                    $orderattribute->attribute_value = $attribute->attribute_value;
                    $orderattribute->attribute_index = $shopattribute->index;
                    $orderattribute->is_username = $shopattribute->is_username;
                    $orderattribute->is_password = $shopattribute->is_password;
                    $orderattribute->is_name = $shopattribute->is_name;;
                    $orderattribute->is_email = $shopattribute->is_email;
                    $orderAttributeList[] = $orderattribute;

                    if ($shopattribute->is_delivery == 1 && trimgf($attribute->attribute_value) != "") {
                        $deliveryAttributeSet++;
                    }
                } else {
                    throw new exception('Could not find shop attribute1.');
                }
            }

            // Set order e-mail
            if ($attribute->is_email) {
                $order->user_email = $attribute->attribute_value;
            }

            // Set order username
            if ($attribute->is_username) {
                $order->user_username = $attribute->attribute_value;
            }

            // Set order name
            if ($attribute->is_name) {
                $order->user_name = $attribute->attribute_value;
            }
        }

        // Check for missing delivery fields
        if ($shop->is_gift_certificate == 1 && $ShopUser->is_delivery == 1 && $deliveryAttributeCount > 0 && $deliveryAttributeCount - $deliveryAttributeSet >= 2) {
            throw new exception('Missing delivery fields');
        }

        // Save order to database
        $order->save();

        // Save order attributes
        foreach ($orderAttributeList as $orderAttribute) {
            $orderAttribute->orderhistory_id = $order->id;
            $orderAttribute->save();
        }


        // Gem i rigtig ordre tabel
        $orderReal = new Order();
        copyAttributes($orderReal, $order);
        $orderReal->order_no = $order->order_no;
        $orderReal->save();

        // Save attributes to order history attribute
        foreach ($orderAttributeList as $orderattribute) {
            $orderRealattribute = new OrderAttribute();
            copyAttributes($orderRealattribute, $orderattribute);
            $orderRealattribute->order_id = $orderReal->id;
            $orderRealattribute->save();
        }

        // Fun part is over, swap history with real order object
        $order = $orderReal;


        // Opret Order presents  entry (vareposer)
        $present_list = preg_split('/,/', trimgf($order->present_no));
        foreach ($present_list as $p) {
            $orderpresententry = new  OrderPresentEntry();
            copyAttributes($orderpresententry, $order);
            $orderpresententry->order_id = $orderReal->id;
            $orderpresententry->present_no = $p;
            $orderpresententry->save();
        }


        // Specielle regler for valgshops
        $replaceShopRules = array(
            3503 => array("language" => 5, "mailserverid" => 5, "contact" => "tryg@gavefabrikken.dk"),  // SCH Testshop
            3226 => array("language" => 1, "mailserverid" => 4, "contact" => "kt@gavefabrikken.dk"),  // Carlsberg
            3083 => array("language" => 1, "mailserverid" => 4, "contact" => "tryg@gavefabrikken.dk"),  // TRYG DK
            3834 => array("language" => 4, "mailserverid" => 4, "contact" => "tryg@gavefabrikken.dk"),  // TRYG NO
            3471 => array("language" => 5, "mailserverid" => 5, "contact" => "tryg@gavefabrikken.dk"),  // TRYG SE
            3191 => array("language" => 1, "mailserverid" => 4, "contact" => "kt@gavefabrikken.dk"),  // Comwell
            3259 => array("language" => 1, "mailserverid" => 4, "contact" => "kt@gavefabrikken.dk"),  // Comwell sommergave
        );

        // If has previous order, check if new e-mail
        if ($previousOrder !== null && ($order->shop_is_gift_certificate == 1 || in_array($order->shop_id, array_keys($replaceShopRules)))) {

            $overwriteTemplateID = 0;

            if (trim(mb_strtolower($order->user_email)) != "" && trim(mb_strtolower($previousOrder->user_email)) != "" && trim(mb_strtolower($order->user_email)) != trim(mb_strtolower($previousOrder->user_email))) {

                // Giftcertificate
                if ($order->shop_is_gift_certificate == 1) {

                    // Load cardshop settings
                    $cardshopSettings = \CardshopSettings::find('first', array("conditions" => array("shop_id" => intval($order->shop_id))));
                    if ($cardshopSettings != null) {

                        // Language

                        $mailserver = 4;
                        if ($cardshopSettings->language_code == 1) $overwriteTemplateID = 23;
                        if ($cardshopSettings->language_code == 4) $overwriteTemplateID = 26;
                        if ($cardshopSettings->language_code == 5) {
                            $overwriteTemplateID = 24;
                            $mailserver = 5;
                        }

                        // Find template
                        if ($overwriteTemplateID > 0) {

                            $mailTemplate = mailtemplate::find($overwriteTemplateID);
                            $template = str_replace('{navn}', $previousOrder->user_name, $mailTemplate->template_overwritewarn);
                            $template = str_replace('{date}', $previousOrder->order_timestamp->format("d-m-Y"), $template);
                            $template = str_replace('{username}', $previousOrder->user_username, $template);

                            $maildata = [];
                            $maildata['sender_email'] =  "no-reply@gavefabrikken.dk";
                            $maildata['recipent_email'] = $previousOrder->user_email;
                            $maildata['subject'] = ($mailTemplate->subject_overwritewarn);
                            $maildata['body'] = ($template);
                            $maildata['mailserver_id'] = $mailserver;
                            $maildata['send_group'] = "overwritewarn_" . $previousOrder->id . "_" . $order->id;
                            MailQueue::createMailQueue($maildata);
                        }
                    }
                }

                // Special rules
                else if (isset($replaceShopRules[$order->shop_id])) {

                    $replace_mailserverid = $replaceShopRules[$order->shop_id]["mailserverid"];
                    $replace_templatelanguage = $replaceShopRules[$order->shop_id]["language"];
                    $replace_contactmail = $replaceShopRules[$order->shop_id]["contact"];

                    $template = "";
                    $subject = "";

                    if ($replace_templatelanguage == 1) {

                        $subject = "Dit gavevalg er blevet ændret";

                        $template = "<html><head>
<meta charset=''><title>Dit gavevalg er overskrevet.</title>
<style type=\"text/css\"> td { width:30%; } .base{ width:150px; } </style>
</head><body>
<table width='80%'><tr><td align='left'>
<p><b>Hej {navn}</b></p>
<p>Vi skriver til dig, da dit tidligere gavevalg foretaget den {date} med brugernavn {username} er blevet ændret til en anden gave. Samtidig med det nye valg af gave er der også blevet angivet en ny e-mailadresse på bestillingen. Derfor modtager du denne mail.</p>
<p>Er det dig selv, der har ændret dit gavevalg og skrevet en ny e-mailadresse, skal du ikke gøre mere, men hvis du ikke selv har foretaget det nye gavevalg, skal du kontakte os på <a href=\"mailto:{contact}\">{contact}</a> så vi kan hjælpe dig videre.</p>
<br>
Med venlig hilsen<br>
GaveFabrikken A/S<br>
<br><br>
OBS: Denne mail kan ikke besvares<br></p><p></p>			
</td></tr></table>
</body></html>";
                    } else if ($replace_templatelanguage == 4) {

                        $subject = "Ditt gavevalg har blitt endret";

                        $template = "<html><head><meta charset=''>
<title>Ditt gavevalg har blitt endret</title>
<style type='text/css'> td { width:30%; } .base{ width:150px; } </style>
</head><body><table width='80%'><tr><td align='left'>			
<p><b>Hei {navn}</b></p>
<p>Vi skriver til deg, fordi ditt tidligere gavevalg, foretatt den {date} på gavekortet med brukernavn {username}, har blitt endret til en annen gave. Samtidig med at det er valgt ny gave er det også bitt oppgitt en ny epostadresse på bestillingen. Du mottar derfor denne eposten.</p>
<p>Har du selv endret på ditt gavevalg og skrevet en ny epostadresse, skal du ikke foreta deg noe, men om du ikke selv har endret ditt gavevalg, må du kontakte vår kundeservice på <a href='mailto:{contact}'>{contact}</a>. Så vil vi hjelpe deg videre.</p>
<br>Med vennlig hilsen<br>GaveFabrikken AS<br>
<br> <br>OBS: Denne mail kan ikke besvares.<br></p><p></p></td></tr>
</table></body></html>";
                    } else if ($replace_templatelanguage == 5) {

                        $subject = "Ditt gåvoval har ändrats";

                        $template = "<html><head><meta charset=''><title></title>
<style type=\"text/css\"> td { width:30%; } .base{ width:150px; } </style></head><body><table width='80%'><tr><td colspan=2></td></tr><tr><td align='left'>	
<p><b>Hej {navn}</b></p>
<p>Vi skriver till dig då ditt tidigare gåvoval som gjordes {date} på presentkortet med användarnamn {username} har ändrats till en annan gåva.</p>
<p>Samtidigt med det nya valet av present har även en ny e-postadress angetts på beställningen. Det är därför du får detta mejl.</p>
<p>Om du själv har ändrat ditt gåvoval och angett en ny e-postadress behöver du inte göra något mer, men har du inte gjort det nya gåvovalet själv ber vi dig att kontakta vår kundtjänst på <a href='mailto:{contact}'>{contact}</a> Vår kundtjänst hjälper dig vidare</p>			
<br>Med vänliga hälsningar<br>PresentBolaget AB<br>
<br> <br>OBS :Detta mejl kan inte besvaras.<br></p>
<p></p></td></tr></table></body></html>";
                    }

                    // Send e-mail
                    if ($replace_mailserverid > 0 && $template != "") {

                        $template = str_replace("{contact}", $replace_contactmail, $template);
                        $template = str_replace('{navn}', $previousOrder->user_name, $template);
                        $template = str_replace('{date}', $previousOrder->order_timestamp->format("d-m-Y"), $template);
                        $template = str_replace('{username}', $previousOrder->user_username, $template);

                        $maildata = [];
                        $maildata['sender_email'] = "no-reply@gavefabrikken.dk";
                        $maildata['recipent_email'] = $previousOrder->user_email;
                        $maildata['subject'] = $subject;
                        $maildata['body'] = ($template);
                        $maildata['mailserver_id'] = $replace_mailserverid;
                        $maildata['send_group'] = "overwritewarn_" . $previousOrder->id . "_" . $order->id;
                        MailQueue::createMailQueue($maildata);

                        $overwriteTemplateID = 1;
                    }
                }



                // Create shopuser log
                $shopUserLog = new ShopUserLog();
                $shopUserLog->shop_user_id = $order->shopuser_id;
                $shopUserLog->type = "Overwrite";
                $shopUserLog->description = "Gavevalg overskrevet på kortnr " . $previousOrder->user_username . ". Oprindelig ordre " . $previousOrder->order_no . " (" . $previousOrder->user_email . "), ny ordre " . $order->order_no . " (" . $order->user_email . ")." . ($overwriteTemplateID > 0 ? " Der er sendt en e-mail med advarsel til " . $previousOrder->user_email . "." : "");
                $shopUserLog->save();
            }
        }

        return ($order);
    }

    public static function getAttributeName($attribute, $languageId)
    {

        $name = $attribute->name;

        try {
            $languages =  json_decode($attribute->languages);
        } catch (Exception $e) {
            return $name;
        }

        if ($languageId == 2) {
            try {
                $name = $languages->En->name;
                if ($name == "")
                    return $attribute->name;
                else
                    return $name;
            } catch (Exception $e) {
                return $attribute->name;
            }
        } else if ($languageId == 3) {
            try {
                $name = $languages->De->name;
                if ($name == "")
                    return $attribute->name;
                else
                    return $name;
            } catch (Exception $e) {
                return $attribute->name;
            }
        } else if ($languageId == 4) {
            try {
                $name = $languages->No->name;
                if ($name == "")
                    return $attribute->name;
                else
                    return $name;
            } catch (Exception $e) {
                return $attribute->name;
            }
        } else if ($languageId == 5) {
            try {
                $name = $languages->Se->name;
                if ($name == "")
                    return $attribute->name;
                else
                    return $name;
            } catch (Exception $e) {
                return $attribute->name;
            }
        } else {
            return $name;
        }

        //3 de
        //4 no
        //5 se


    }


    // Hjælpe funktion som henter billerder beskrivelse mm. på en ordre
    public static function getOrderDetails($orderId, $languageId)
    {

        $order = Order::find($orderId);
        $present = Present::find($order->present_id);
        $simpleAttributes = [];
        $result = [];

        // find email, + de attributes som skal vises p� kvittering
        foreach ($order->attributes_ as $attribute) {

            try {
                $shopattribute =  ShopAttribute::find($attribute->attribute_id);
                if ($shopattribute->is_email)
                    $result['email'] = $attribute->attribute_value;

                if ($shopattribute->is_visible) {
                    $simpleAttributes[Order::getAttributeName($shopattribute, $languageId)]  = $attribute->attribute_value;    // ny
                    //$simpleAttributes[$attribute->attribute_name]  = $attribute->attribute_value;     //Orginal
                }
            } catch (Exception $e) {
            }
        }

        $result['order_id'] = $order->id;
        $result['order_no'] = $order->order_no;
        $result['attributes'] = $simpleAttributes;
        $result['user_username'] = $order->user_username;
        //Find gavebeskrivelse
        $result['present_id'] = $order->present_id;
        foreach ($present->descriptions as $description) {
            if ($description->language_id == $languageId) {
                $result['present_description'] = $description->short_description;
                $result['present_caption']    = utf8_decode($description->caption);
            }
        }

        // Find gave billede
        $result['present_image'] =    isset($present->present_media[0]) ? $present->present_media[0]->media_path : "logo/intet.jpg";
        $result['present_model_name'] = utf8_decode($order->present_model_name);
        $result['present_model_no'] = $order->present_model_present_no;
        $result['date_stamp'] =       $order->order_timestamp->format('d-m-Y H:i:s');
        $result['present_model_id']  = $order->present_model_id;
        $result['shop_is_gift_certificate']  = $order->shop_is_gift_certificate;

        /*
        //Find Variant billede
        $result['present_variant_image']  = '';
        $variantlist = json_decode($present->variant_list);
        $isCorrectVariant = false;
        foreach($variantlist as $variant) {
            foreach($variant->feltData as $var) {

                if(isset($var->variantNr)) {
                    if($order->present_model_present_no == $var->variantNr)
                        $isCorrectVariant = true;
                }
                if(isset($var->variantImg) && $isCorrectVariant) {
                    $result['present_variant_image']  = $var->variantImg;
                    $isCorrectVariant = false;
                }
            }
        }

        */



        return ($result);
    }

    static public function deleteOrder($id, $realDelete = true)
    {

        if ($realDelete) {
            $order = Order::find($id);
            $order->delete();
        } else {  //Soft delete
            $order->deleted = 1;
            $order->save();
        }
    }

    static public function countPresentOnOrders($shop_id, $present_id, $present_model_no)
    {

        if ($present_model_no == '') {
            //echo "select count(*) as amount from `order` WHERE shop_id= ".$shop_id."  AND present_id in(".$present_id.")" ;
            $orders = order::find_by_sql("select count(*) as amount from `order` WHERE shop_id= " . $shop_id . "  AND present_id in(" . $present_id . ")");
        } else {
            $orders = order::find_by_sql("select count(*) as amount from `order` WHERE shop_id= " . $shop_id . "  AND present_id in(" . $present_id . ") AND present_model_id= '" . $present_model_no . "'");
        }
        return ($orders[0]->amount);
    }
}
