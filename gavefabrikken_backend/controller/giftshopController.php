<?php

class giftshopController Extends baseController
{

    public function Index()
    {
        die("GF");
    }


    public function read()
    {
        // implementer sikkerhed her.
        $shopID = intvalgf($_POST['id']);
        if($shopID <= 0){
            throw new exception('link_error');
        }
        $shopuser = ShopUser::find_by_token_and_shop_id($_POST['token'], $shopID);




        /*
             if(count((array)$shopuser) == 0){
               throw new exception('link_error');
             }
        */

        if(!sizeofgf($shopuser) > 0){
            $shopuser = ShopUser::find_by_token($_POST['token']);
            if(!sizeofgf($shopuser) > 0) {
                // Do not trust user inputted id
                //$shop = shop::readShop($_POST['id']);
                throw new exception("unknown");
            } else {
                $shop = shop::find($shopuser->shop_id);
                throw new exception($shop->link);
            }

        }

        //print_r($shopuser);
        //2. tjek at id = shop id på user.
        $shop = shop::readShop($_POST['id']);

        $allways = [];
        $allwaysClose = [];
        //  $shop->attributes["link"] = "";
        $shop->attributes["demo_password"] = "";
        $shop->attributes["demo_user_id"] = "";
        $shop->attributes["blocked_text"] = "";
        $shop->attributes["pt_saleperson"] = "";
        $shop->attributes["pt_shopname"] = "";
        $shop->attributes["ptupdate"] = "";
        $shop->attributes["kundepanel_email_regel"] = "";
        $shop->attributes["rapport_email"] = "";
        $shop->attributes["receipt_recipent"] = "";
        $shop->attributes["close_date"] = "";
        $shop->attributes["report_attributes"] = "";
        $shop->attributes["demo_username"] = "";
        $shop->attributes["receipt_link"] = "";
        $shop->attributes["modified_datetime"] = "";
        $shop->attributes["allways"] = $allways;
        $shop->attributes["allwaysclose"] = $allwaysClose;
        if($shopuser->username == "free"){
            $rules = [];
        } else {
            $rules = shop_present_company_rules::find_by_sql("select distinct present_id, rules from shop_present_company_rules inner join shop_user on shop_present_company_rules.company_id = shop_user.company_id where rules > 0 and token = '".$_POST['token']."'");

            $rs = company::find_by_sql("SELECT pid FROM `company` where `id` in (SELECT company_id FROM `shop_user` WHERE `token` LIKE '".$_POST['token']."')");
            if(sizeofgf($rs) > 0){
                $pid = $rs[0]->attributes["pid"];
                if($pid != 0){
                    $rules = shop_present_company_rules::find_by_sql("select distinct present_id, rules from shop_present_company_rules where rules > 0 and company_id = ".$pid);
                }
            }





        }




        $PressentOptionsRs = shop_present_company_rules::find_by_sql("SELECT present_model_options.present_id,present_model_options.expire_data,present_model_options.visibility FROM `present_model_options` INNER JOIN present on present_model_options.present_id = present.id WHERE present.shop_id = ".$_POST['id']);


        $PressentOptionsData = [];
        foreach($PressentOptionsRs as $PressentOptions){
            $PressentOptionsData[$PressentOptions->attributes["present_id"]][$PressentOptions->attributes["expire_data"]] = $PressentOptions->attributes["visibility"] ;

        }
        // den if sætning skal fjernet når vi går i produktion


        if(sizeofgf($rules) > 0 ){

            foreach($rules as $item){
                if($item->attributes["rules"] == 1){
                    $allways[] = $item->attributes["present_id"];
                }
                if($item->attributes["rules"] == 2){
                    $allwaysClose[] = $item->attributes["present_id"];
                }
            }
            $shop->attributes["allways"] = $allways;
            $shop->attributes["allwaysclose"] = $allwaysClose;
        }


        $shop->attributes["optionsData"] = $PressentOptionsData;
        $options = array('include' => array('descriptions', 'presents', 'attributes_'));


        //response::success(make_json("shop", $shop,$options));
        //return;

        // Catch normal output
        ob_start();
        response::success(make_json("shop", $shop, $options));
        $output = ob_get_contents();
        ob_end_clean();

        // Parse and define attributes to remove
        $responseData = json_decode($output, true);
        $shopRemoveAttr = array("in_shopboard", "demo_username", "demo_password", "demo_user_id", "mailserver_id", "location_attribute_id", "location_type", "created_datetime", "modified_datetime", "report_attributes", "rapport_email", "kundepanel_email_regel", "ptupdate", "pt_pdf", "pt_shopname", "pt_frontpage", "pt_mere_at_give", "pt_tree", "pt_bag_page", "pt_voucher_page", "pt_saleperson_page", "pt_layout_style", "pt_language", "pt_brands_united", "dbcalc_budget", "dbcalc_standard", "saleperson", "gdpr", "reservation_state", "reservation_language", "reservation_code", "reservation_foreign_language", "reservation_foreign_code", "final_finished", "sold", "welcome_mail", "has_users", "has_orders");
        $companyRemoveAttr = array("phone", "so_no", "sales_person", "gift_responsible", "username", "password", "bill_to_address", "bill_to_address_2", "bill_to_postal_code", "bill_to_city", "bill_to_country", "bill_to_email", "ship_to_company", "ship_to_attention", "ship_to_address", "ship_to_address_2", "ship_to_postal_code", "ship_to_city", "ship_to_country", "contact_name", "contact_phone", "contact_email", "internal_note", "rapport_note", "nav_debitor_no", "token", "import_2017", "", "", "hascard", "hasvoucher", "nav_customer_no", "nav_on_hold", "nav_min_invoicedate", "company_state", "created_date", "created_by", "prepayment", "excempt_invoicefee", "excempt_envfee", "payment_terms", "allow_delivery", "manual_freight");
        $presentRemoveAttr = array("pim_id", "pim_sync_time", "copy_of", "price", "price_no", "price_se", "price_group", "price_group_no", "price_group_se", "indicative_price", "indicative_price_no", "created_datetime", "modified_datetime", "pt_options", "pt_price", "pt_price_no", "pt_price_se", "pt_show_language", "prisents_nav_price", "prisents_nav_price_no");

        // Remove shop data
        if (isset($responseData["data"]) && isset($responseData["data"]["shop"]) && is_array($responseData["data"]["shop"])) {
            foreach ($responseData["data"]["shop"] as $sIndex => $shop) {
                foreach ($shopRemoveAttr as $attr) {
                    unset($responseData["data"]["shop"][$sIndex][$attr]);
                }
                if (isset($responseData["data"]["shop"][$sIndex]["company"])) {
                    foreach ($companyRemoveAttr as $attr) {
                        unset($responseData["data"]["shop"][$sIndex]["company"][$attr]);
                    }
                }
                if (isset($responseData["data"]["shop"][$sIndex]["presents"]) && is_array($responseData["data"]["shop"][$sIndex]["presents"])) {
                    foreach ($responseData["data"]["shop"][$sIndex]["presents"] as $pIndex => $present) {
                        foreach ($presentRemoveAttr as $attr) {
                            unset($responseData["data"]["shop"][$sIndex]["presents"][$pIndex]["present"]["attributes"][$attr]);
                        }
                    }
                }
            }
        }





        // **   hack for julegavevalget
        if($_POST["id"] == "7121") {

            $cardValues = explode(",", $shopuser->card_values);
            foreach ($responseData["data"]["shop"][0]["presents"] as $key => $present) {

                if (!in_array($present["present"]["attributes"]["present_list"], $cardValues)) {
                    unset($responseData["data"]["shop"][0]["presents"][$key]);
                }
            }
            $responseData["data"]["shop"][0]["presents"] = array_values($responseData["data"]["shop"][0]["presents"]);
            //   print_R($responseData["data"]["shop"][0]["presents"]);
        }
        // **   hack for julegavevalget END ***
        // **   hack for sv400
        if($_POST["id"] == "1832_") {

            $cardValues = explode(",", $shopuser->card_values);
            foreach ($responseData["data"]["shop"][0]["presents"] as $key => $present) {

                if (!in_array($present["present"]["attributes"]["present_list"], $cardValues)) {
                    unset($responseData["data"]["shop"][0]["presents"][$key]);
                }
            }
            $responseData["data"]["shop"][0]["presents"] = array_values($responseData["data"]["shop"][0]["presents"]);
            //   print_R($responseData["data"]["shop"][0]["presents"]);
        }
        if($_POST["id"] == "7423") {

            $cardValues = explode(",", $shopuser->card_values);
            foreach ($responseData["data"]["shop"][0]["presents"] as $key => $present) {

                if (!in_array($present["present"]["attributes"]["present_list"], $cardValues)) {
                    unset($responseData["data"]["shop"][0]["presents"][$key]);
                }
            }
            $responseData["data"]["shop"][0]["presents"] = array_values($responseData["data"]["shop"][0]["presents"]);
            //   print_R($responseData["data"]["shop"][0]["presents"]);
        }

        if($_POST["id"] == "6527") {

            $cardValues = explode(",", $shopuser->card_values);
            foreach ($responseData["data"]["shop"][0]["presents"] as $key => $present) {

                if (!in_array($present["present"]["attributes"]["present_list"], $cardValues)) {
                    unset($responseData["data"]["shop"][0]["presents"][$key]);
                }
            }
            $responseData["data"]["shop"][0]["presents"] = array_values($responseData["data"]["shop"][0]["presents"]);
            //   print_R($responseData["data"]["shop"][0]["presents"]);
        }
        if($_POST["id"] == "8826") {

            $cardValues = explode(",", $shopuser->card_values);
            foreach ($responseData["data"]["shop"][0]["presents"] as $key => $present) {

                if (!in_array($present["present"]["attributes"]["present_list"], $cardValues)) {
                    unset($responseData["data"]["shop"][0]["presents"][$key]);
                }
            }
            $responseData["data"]["shop"][0]["presents"] = array_values($responseData["data"]["shop"][0]["presents"]);
            //          print_R($responseData["data"]["shop"][0]["presents"]);
        }


        if($_POST["id"] == "9124") {
            $cardValues = explode(",", $shopuser->card_values);
            // Clean up the card values
            $cardValues = array_map('trim', $cardValues);

            foreach ($responseData["data"]["shop"][0]["presents"] as $key => $present) {

                $presentListValue = $present["present"]["attributes"]["present_list"];

                // Check if present_list contains comma-separated values
                if (strpos($presentListValue, ',') !== false) {
                    // Multiple values in present_list (e.g., "666,444")
                    $presentValues = explode(',', $presentListValue);
                    $presentValues = array_map('trim', $presentValues);

                    // Keep if ANY value in present_list matches ANY card value
                    if (empty(array_intersect($presentValues, $cardValues))) {
                        unset($responseData["data"]["shop"][0]["presents"][$key]);
                    }
                } else {
                    // Single value in present_list (e.g., "555")
                    if (!in_array(trim($presentListValue), $cardValues)) {
                        unset($responseData["data"]["shop"][0]["presents"][$key]);
                    }
                }
            }

            // Re-index the array
            $responseData["data"]["shop"][0]["presents"] = array_values($responseData["data"]["shop"][0]["presents"]);


            // print_R($responseData["data"]["shop"][0]["presents"]);
        }
        // **   hack for julegavevalget END ***
        // $responseData["data"]["master"] = $debug;
        $this->applyMasterPresentData($shopID, $responseData);
        echo json_encode($responseData);



        /*
        echo "<pre>";
        print_r($responseData);
        echo "</pre>";
        */
    }

    public function showMaster()
    {
        $shopID = 8208;
        $status = $this->identifyChooseBetweenItemsByShop($shopID);
        echo "<pre>";
        print_r($status);
        echo "</pre>";

// retur eksemple / søren her skal du bare sætte på present show_master = 1 for dem der er true
        /*
        Array
        (
            [approved] => Array
                (
                    [0] => 289873
                    [1] => 294928
                    [2] => 296274
                )
            [notApproved] => Array
                (
                )

        )
        */
    }

    public function identifyChooseBetweenItemsByShop($shopID)
    {
        $approved = [];
        $notApproved = [];
        $presentModel = PresentModel::find('all', array(
            'select' => 'COUNT(present_id) as cp, present_id',
            'conditions' => array(
                'present_id IN (SELECT id FROM present WHERE shop_id = ?) AND language_id = ?',
                $shopID,  // Fixed: Use parameter instead of hardcoded 8483
                1         // language_id
            ),
            'group' => 'present_id',
            'having' => 'cp = 2'
        ));

        foreach($presentModel as $present){
            $presentID = $present->attributes["present_id"];
            $itemNr = $this->getItemNr($presentID);

            // Fixed: Add safety check for array count
            if(count($itemNr) == 2) {
                $item1 = $this->validateIfHasMasterData($itemNr[0]->attributes["model_present_no"]);
                $item2 = $this->validateIfHasMasterData($itemNr[1]->attributes["model_present_no"]);

                if($item1 == true && $item2 == true){
                    array_push($approved, $presentID);
                } else {
                    array_push($notApproved, $presentID);
                }
            } else {
                array_push($notApproved, $presentID);
            }
        }

        // Fixed: Syntax error - use array() not array[]
        return array("approved" => $approved, "notApproved" => $notApproved);
    }

    private function validateIfHasMasterData($itemNr){
      $presentModels = PresentModel::find('all', array(
                'select' => 'DISTINCT COUNT(present_id) as total_model, model_id, present_id',
                'conditions' => array(
                    'present_id IN (
            SELECT present.id
            FROM present
            INNER JOIN present_model ON present_model.present_id = present.id
            WHERE present.copy_of = ?
              AND present.shop_id = ?
              AND present_model.language_id = ?
              AND present_model.model_present_no = ?
              and present.deleted = 0
        ) AND present_model.language_id = ?',
                    0,        // present.copy_of
                    0,        // present.shop_id
                    1,        // present_model.language_id (i subquery)
                    $itemNr, // present_model.model_present_no
                    1         // present_model.language_id (i main query)
                ),
                'group' => 'present_id',
                'having' => 'total_model = 1'
            ));
        return sizeof($presentModels) == 1 ? true:false;
    }


    private function getItemNr($presentID)
    {
            return PresentModel::find('all', array(
                'select' => 'model_present_no',
                'conditions' => array(
                    'present_id = ? AND is_deleted = 0 and language_id = 1',
                    $presentID
                )
            ));

    }



    private function applyMasterPresentData($shopID, &$responseData)
    {


        $presents = Present::find('all', array(
            'select' => 'id',
            'conditions' => array(
                'shop_id = ? AND show_master = 1',
                $shopID
            )
        ));

        foreach ($presents as $present) {
            $presentID = $present->attributes["id"];

            $presentModel = PresentModel::find('all', array(
                'select' => 'id,model_present_no,active',
                'conditions' => array(
                    'present_id = ? AND is_deleted = 0 and language_id = 1',
                    $presentID
                )
            ));

            $activeModelNo = $this->checkModelStates($presentModel);
            if ($activeModelNo) {
                $this->getMasterData($activeModelNo, $presentID, $responseData);
            }
        }
    }
    private function getMasterData($itemNr, $originalPresentId, &$responseData){
        $presentModels = PresentModel::find('all', array(
            'select' => 'DISTINCT COUNT(present_id) as total_model, model_id, present_id',
            'conditions' => array(
                'present_id IN (
        SELECT present.id 
        FROM present 
        INNER JOIN present_model ON present_model.present_id = present.id 
        WHERE present.copy_of = ? 
          AND present.shop_id = ? 
          AND present_model.language_id = ? 
          AND present_model.model_present_no = ?
          and present.deleted = 0
    ) AND present_model.language_id = ?',
                0,        // present.copy_of
                0,        // present.shop_id
                1,        // present_model.language_id (i subquery)
                $itemNr, // present_model.model_present_no
                1         // present_model.language_id (i main query)
            ),
            'group' => 'present_id',
            'having' => 'total_model = 1'
        ));

        if(sizeof($presentModels) == 1){
            $masterPresentId = $presentModels[0]->attributes["present_id"];

            $presentMedia = PresentMedia::find('all', array(
                'conditions' => array(
                    'present_id = ?',
                    $masterPresentId
                )
            ));

            $presentDescription = PresentDescription::find('all', array(
                'conditions' => array(
                    'present_id = ?',
                    $masterPresentId
                )
            ));

            // Find og erstat data i $responseData
            $this->replacePresentData($originalPresentId, $presentMedia, $presentDescription, $responseData);
        }
    }

    private function replacePresentData($originalPresentId, $masterMedia, $masterDescriptions, &$responseData) {
        if (!isset($responseData["data"]["shop"][0]["presents"])) {
            return;
        }

        foreach ($responseData["data"]["shop"][0]["presents"] as &$present) {
            if ($present["present"]["attributes"]["id"] == $originalPresentId) {
                // Erstat media data (bevar oprindelige ID struktur, men skift billeder)
                if (!empty($masterMedia) && !empty($present["present"]["attributes"]["media"])) {
                    $originalMediaCount = count($present["present"]["attributes"]["media"]);

                    for ($i = 0; $i < min($originalMediaCount, count($masterMedia)); $i++) {
                        // Bevar original id og present_id, men skift media_path
                        $present["present"]["attributes"]["media"][$i]["attributes"]["media_path"] = $masterMedia[$i]->attributes["media_path"];
                    }

                    // Opdater first_image data (bevar original id, skift kun path)
                    if (!empty($masterMedia)) {
                        $present["present"]["attributes"]["first_image_media_path"] = $masterMedia[0]->attributes["media_path"];
                    }
                }

                // Erstat description data (bevar oprindelige ID struktur, men skift tekst)
                if (!empty($masterDescriptions) && !empty($present["present"]["attributes"]["descriptions"])) {
                    foreach ($present["present"]["attributes"]["descriptions"] as &$description) {
                        $languageId = $description["attributes"]["language_id"];

                        // Find matching master description for same language
                        foreach ($masterDescriptions as $masterDesc) {
                            if ($masterDesc->attributes["language_id"] == $languageId) {
                                // Bevar original id og present_id, men skift indhold
                                $description["attributes"]["caption"] = $masterDesc->attributes["caption"];
                                $description["attributes"]["caption_presentation"] = $masterDesc->attributes["caption_presentation"];
                                $description["attributes"]["caption_paper"] = $masterDesc->attributes["caption_paper"];
                                $description["attributes"]["short_description"] = $masterDesc->attributes["short_description"];
                                $description["attributes"]["long_description"] = $masterDesc->attributes["long_description"];
                                break;
                            }
                        }
                    }
                }

                break;
            }
        }
    }
    private function checkModelStates($presentModel)
    {
        // Tjek at der er præcis 2 elementer i array
        if (count($presentModel) !== 2) {
            echo "Fejl: Forventede 2 modeller, fik " . count($presentModel);
            return false;
        }

        $model1Active = $presentModel[0]->attributes["active"];
        $model2Active = $presentModel[1]->attributes["active"];

        // Tjek om nøjagtigt én model er aktiv (XOR operation)
        if ($model1Active != $model2Active) {
            // Returner model_present_no for den aktive model
            if ($model1Active == 0) {
                return $presentModel[0]->attributes["model_present_no"];
            } else {
                return $presentModel[1]->attributes["model_present_no"];
            }
        }
        // Tjek om begge modeller er inaktive eller begge aktive
        if ($model1Active == 0 && $model2Active == 0) {
            return false;
        }

        if ($model1Active == 1 && $model2Active == 1) {
            return false;
        }
        return false;
    }

}