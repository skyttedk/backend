<?php

Class gavevalgController Extends baseController
{

    public function Index()
    {
        throw new Exception("Unknown endpoint");
    }

    public function log()
    {
        throw new Exception("gvtr");
    }

    public function login() {

        $token = $_POST['token'];
        $shopUser = ShopUser::getByToken($token);
        $options = array('except' => array('password'));
        response::success(make_json("result", $shopUser,$options));

    }

    public function readshop() {

        // Check shopuser token
        ShopUser::getByToken($_POST["token"]);

        // Hent shop
        $shop = shop::readShop($_POST['id']);

        $allways = [];
        $allwaysClose = [];
        //$shop->attributes["link"] = "";
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

        $rules = shop_present_company_rules::find_by_sql("select distinct present_id, rules from shop_present_company_rules inner join shop_user on shop_present_company_rules.company_id = shop_user.company_id where rules > 0 and token = '".$_POST['token']."'");

        //print_r($shop);
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
        $options = array('include' => array('descriptions', 'presents', 'attributes_'));
        response::success(make_json("shop", $shop,$options));
    }

    public function presentbundle(){

        // Check shopuser token
        ShopUser::getByToken($_POST["token"]);

        // Get bundle
        $bundleId = $_POST["bundleId"];
        $PresentModelList =  PresentModel::find_by_sql("SELECT * FROM present_model WHERE model_id in (SELECT model_id FROM present_model WHERE present_id  =  ".$bundleId."  and language_id = 1 and active = 0 and is_deleted = 0) || model_id in (SELECT model_id FROM shop_present_company_rules WHERE present_id  =  ".$bundleId." and rules = 1 )  ORDER BY model_id,language_id ASC");

        // fejl fra starten active er aktive n�r den har v�rdien 0 , den burde v�re 1, men det kan ikke laves om
        for($i=0;sizeofgf($PresentModelList) > $i;$i++){
            $PresentModelList[$i]->attributes["active"] = 0;
        }

        response::success(json_encode($PresentModelList));
    }

    public function createorder() {

        // Check shopuser token
        ShopUser::getByToken($_POST["token"]);

        // Create order
        $data = $_POST;
        $order = Order::createOrder ($data);
        $this->createReceipt($order->id,$order->language_id);
        $options = array('include' => array('attributes_'));
        response::success(make_json("order", $order,$options));
    }

    public function getreceipttext(){

        // Check shopuser token
        ShopUser::getByToken($_POST["token"]);

        // Get receipt text
        $receiptTxt = Receipt::find_by_sql("SELECT * FROM `receipt_custom_part` where id = ( SELECT msg1 FROM `present_model` WHERE `model_id` = '".$_POST["model_id"]."' and `language_id` = 1  )");
        response::success(json_encode($receiptTxt));
    }

    public function sendsms(){

        // Check shopuser token
        ShopUser::getByToken($_POST["token"]);

        // Send sms
        $model = "";
        $order_no = "";
        if($_POST["model"] != ""){
            $model = str_replace("###"," ",$_POST["model"]);
            $order_no = $_POST["order_no"];
        }
        $query = http_build_query(array(
            'token' => 'rhSukWyyQLyFyMRXlbNBmW3LFHb15wfLXwACjUIEWnn4DBU_jrZMAORCeSCrotwf',
            'sender' => 'Valgt gave',
            'message' => 'Du har valgt: '.$model.' Ordrenr.: '.$order_no,
            'recipients.0.msisdn' => '0045'.$_POST["tlf"],
        ));

        // Send it
        $result = file_get_contents('https://gatewayapi.com/rest/mtsms?' . $query);

        // Get SMS ids (optional)
        //echo json_decode($result)->ids;
        echo json_encode(array("status"=>"1"));

    }

}