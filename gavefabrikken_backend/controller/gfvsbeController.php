<?php

// Controller gfvsbe - GaveFabrik ValgShop BackEnd
class GFVSBEController Extends baseController
{

    public function Index()
    {
        header("Location: https://www.findgaven.dk");
    }

    /**
     * fjvnrjee4fdiv3mfds
     * From login/loginShopUserByToken
     */
    public function fjvnrjee4fdiv3mfds()
    {
        $token = $_POST['token'];
        $shopUser = ShopUser::getByToken($token);
        $options = array('except' => array('password'));
        response::success(make_json("result", $shopUser,$options));
    }


    /**
     * jh4jkfjdvnsdkjfadskjd
     * From present/getBundle
     */

    public function jh4jkfjdvnsdkjfadskjd()
    {
        $bundleId = $_POST["bundleId"];
        $company_id = $_POST["companyId"];

        $PresentModelList =  PresentModel::find_by_sql("SELECT * FROM present_model WHERE model_id in (SELECT model_id FROM present_model WHERE present_id  =  ".$bundleId."  and language_id = 1 and active = 0 and is_deleted = 0) ||
    model_id in (SELECT model_id FROM shop_present_company_rules WHERE present_id  =  ".$bundleId." and `company_id` =  ".$company_id."   and  rules = 1 )  ORDER BY model_id,language_id ASC");

        // fejl fra starten active er aktive n�r den har v�rdien 0 , den burde v�re 1, men det kan ikke laves om
        for($i=0;sizeofgf($PresentModelList) > $i;$i++){
            $PresentModelList[$i]->attributes["active"] = 0;
        }
        response::success(json_encode($PresentModelList));
    }


    /**
     * tyjfn4ndjsdfvjhefznwq
     * From receipt/getStandartTextById
     */

    public function tyjfn4ndjsdfvjhefznwq(){

        $receiptTxt = Receipt::find_by_sql("SELECT * FROM `receipt_custom_part` where id = ( SELECT msg1 FROM `present_model` WHERE `model_id` = '".$_POST["model_id"]."' and `language_id` = 1  )");
        response::success(json_encode($receiptTxt));
    }

    /**
     * hh3jkfdshfdn3j3sadcdsf
     * From order/create
     */

    public function hh3jkfdshfdn3j3sadcdsf()
    {

        $data = $_POST;

        $order = Order::createOrder ($data);

        $shopuser = ShopUser::find($order->shopuser_id);
        ActionLog::logShopUserAction("ShopUserOrder", $shopuser->username." har valgt ".$order->present_model_present_no." på ordrenr ".$order->order_no,"",$shopuser,$order->id);

        $this->createReceipt($order->id,$order->language_id);
        $options = array('include' => array('attributes_'));
        response::success(make_json("order", $order,$options));
    }


    /**
     * gmvfj3kfvu4i5jdfkdf
     * From shop/readFull_v2
     */

    public function gmvfj3kfvu4i5jdfkdf()
    {

        // implementer sikkerhed her.
        if(intvalgf($_POST["id"]) <= 0){
            throw new exception('link_error');
        }

        $shopuser = ShopUser::find_by_token_and_shop_id($_POST['token'], intvalgf($_POST['id']));

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

        echo json_encode($responseData);

    }

}