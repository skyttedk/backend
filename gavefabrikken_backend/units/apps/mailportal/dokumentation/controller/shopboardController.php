<?php

Class shopboardController Extends baseController {

    public function index() {
        $this->registry->template->show('shopboard_view');
    }
    public function showAll() {

        $user =  $_POST["user"];
        if($user == "alle"){
            $shopboard = Shopboard::find_by_sql("select * from shop_board where active = true  order by fane");
        } else {
            $shopboard = Shopboard::find_by_sql("select * from shop_board where active = true and valgshopansvarlig = '$user' order by fane");
        }


        response::success(make_json("shop", $shopboard));
    }
    public function crateValgshop(){

         $data = $_POST;





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
            "info"=>""
        );



        $shopboard = new Shopboard();
        $shopboard->update_attributes($attributes);
        $shopboard->save();
        response::success(make_json("shopboard", $shopboard));


    }


    public function hasValgshop()
    {
        $shopID =  $_POST["shopID"];
        $options = array('fk_shop' => $shopID);

        $shopboard = Shopboard::find('all', $options);
        response::success(make_json("shop", $shopboard));
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