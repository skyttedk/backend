<?php

Class shopboardController Extends baseController {

    public function index() {
        $userID =  \router::$systemUser->id;
        $sysUser = SystemUser::find($userID);

        $this->registry->template->language = $sysUser->attributes["language"];

        $this->registry->template->show('shopboard_view');


    }
    public function showAll() {
        $localization = $_POST["local"];
        $user =  $_POST["user"];
        if($user == "alle"){
            $shopboard = Shopboard::find_by_sql("select * from shop_board where active = true and localization = ".$localization." order by fane");
        } else {
          //  echo "select * from shop_board where active = true and valgshopansvarlig = '$user' and localization = ".$localization." order by fane";
            $shopboard = Shopboard::find_by_sql("select * from shop_board where active = true and valgshopansvarlig = '$user' and localization = ".$localization." order by fane");
        }


        response::success(make_json("shop", $shopboard));
    }
    public function allSaleVaAndList()
    {
        echo $_POST["shopID"];
        
    }


    public function crateValgshop(){

         $data = $_POST;
        $userID =  \router::$systemUser->id;
        $sysUser = SystemUser::find($userID);
        $localization = $sysUser->attributes["language"];




        $attributes =   array(
            
            "shop_navn"=>$data["shop_navn"],
            "salger" => $data["salesPerson"],
            "valgshopansvarlig" => $data["valgshopansvarlig"],
            "fk_shop" => $data["shopID"],
            "salgsordrenummer" => $data["salgsordrenummer"],
            "kontaktperson" => $data["kontaktperson"],
            "telefon" => $data["telefon"],
            "mail" => $data["mail"],
            "shop_aabner" => $data["shop_aabner"],
            "shop_lukker" => $data["shop_lukker"],
            "ordretype" => "Valgshop",
            "kunde" => $data["shop_navn"],
            "info"=>"",
            "localization"=>$localization
        );

        $shopboard = new Shopboard();
        $shopboard->update_attributes($attributes);
        $shopboard->save();

        $shop = Shop::find($data["shopID"]);
        $att = array(
            "in_shopboard" => 1,
            "in_shopboard_date" => date('Y-m-d')  // Dagens dato
        );
        $shop->update_attributes($att);
        $shop->save();


        response::success(make_json("shopboard", $shopboard));


    }


    public function hasValgshop()
    {
        $shopID =  $_POST["shopID"];
        $options = array('fk_shop' => $shopID);

        $shopboard = Shopboard::find('all', $options);
        $shop = Shop::find($shopID);


        $ShopMetadata = ShopApproval::find_by_shop_id($shopID);
        /*
         * 0 ikke godkendt og burde ikke være i shop board
         * 1 godkendt ikke i shopboard
         * 2 godkendt og er i shopboard
         * 4 ej godkendt men i shopboard
         * 5 ej reservations godkendt
         */

        $state = 0;
        if($ShopMetadata){
            if( $ShopMetadata->attributes["orderdata_approval"] == 2 ){
                if(sizeof($shopboard) > 0){
                    $state = 2;
                } else {
                    $state = 1;
                }
            } else {
                if(sizeof($shopboard) > 0){
                    $state = 4;
                }
            }
        }
        if($shop->attributes["reservation_state"] == 0){
            $state = 5;
        }

        // Konverter ActiveRecord DateTime til streng
        $shopboard_date = null;
        if($shop->attributes["in_shopboard_date"]) {
            $shopboard_date = $shop->attributes["in_shopboard_date"]->format('Y-m-d');
        }

        $response = json_encode(array(
            "state" => $state,
            "in_shopboard_date" => $shopboard_date
        ));
        response::success($response);
    }
    public function removeValgshopDate()
    {
        $shopID = $_POST["shopID"];

        $shop = Shop::find($shopID);
        $att = array(
            "in_shopboard" => 0,
            "in_shopboard_date" => null
        );
        $shop->update_attributes($att);
        $shop->save();

        response::success(make_json("success", "Oprettelsesdato fjernet"));
    }

    public function getAllActiveShops(){
        $options = array('in_shopboard' => true);
        $shops = shop::find('all', $options);
        response::success(make_json("shop", $shops));
    }

    public function getAllNotInShops(){
        $options = array('in_shopboard' => false,"is_gift_certificate" => false);
        $shops = shop::find('all', $options);
        response::success(make_json("shop", $shops));

    }
    public function addNew(){
            $attributes = $_POST["data"];
            $shopboard = new Shopboard();
            $shopboard->update_attributes($attributes);
            $shopboard->save();
            response::success(make_json("shopboard", $shopboard));
    }
    public function updateData(){
            $attributes = $_POST["data"];


            $shopboard = Shopboard::find($attributes["id"]);
            $shopboard->update_attributes($attributes);
            $shopboard->save();
            response::success(make_json("shopboard", $shopboard));
    }
    public function loadFaneData(){
        $localization = $_POST["local"];
        $faneId =  $_POST["tabId"];
        $userId =  $_POST["user"];
        if($userId == "alle"){
            $options = array('fane' => $faneId,'localization'=>$localization);
        } else {
            $options = array('fane' => $faneId,'valgshopansvarlig' =>$userId,'localization'=>$localization);
        }
        $shopboard = Shopboard::find('all', $options);
        response::success(make_json("shop", $shopboard));
    }
    public function csvData(){
        $faneId =  $_POST["tabId"];
        $userId =  $_POST["user"];
        if($userId == "alle"){
            $options = array('fane' => $faneId);
        } else {
            $options = array('fane' => $faneId,'valgshopansvarlig' =>$userId);
        }
        $shopboard = Shopboard::find('all', $options);
        response::success(make_json("shop", $shopboard));
    }


    public function loadStatus()
    {
        $postData = $_POST["postData"];
        $options = array('id' => $postData);
        $shopboard = Shopboard::find('all', $options);
        response::success(make_json("status", $shopboard));
    }
    public function updataStatus()
    {
        $postData = $_POST["data"];

          $post = Shopboard::find($postData["id"]);
        $post->fane = $postData["fane"];
        $post->save();
        $dummy = array();
        response::success(make_json("result", $dummy));

    }


}

?>