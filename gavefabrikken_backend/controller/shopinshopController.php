<?php
if (session_status() == PHP_SESSION_NONE) session_start();
// Controller SystemLog
// Date created  Wed, 13 Apr 2016 20:48:50 +0200
// Created by Bitworks
class ShopinshopController Extends baseController
{

    public function Index()
    {

    }
    public function makeNewUser(){
        echo $shopid = intval($_POST['shop_id']);
        die("");
        $randomToken = bin2hex(openssl_random_pseudo_bytes(16));

        // Get the current timestamp
        $timestamp = time();

        // Combine them
        $random = $randomToken . '::' . $timestamp;

        $attributeList = ShopAttribute::find_by_sql("select * from shop_attribute where shop_id = ".$shopid);
        $attributes = array();
        foreach($attributeList as $attribute) {

            $newAttr = array(
                "id" => $attribute->id,
                "value" => ""
            );

            if($attribute->is_username == 1 || $attribute->is_password == 1) {
                $newAttr["value"] = $random;
            }

            $attributes[] = $newAttr;
        }

        $form["attributes_"] = json_encode($attributes);
        $form["data"] = '{"userId":null,"shopId":"'.$shopid.'","companyId":51902}';
        $shopUserNew = Shop::addShopUser2($form);

        // Log in and return new shopuser
        $token = ShopUser::Login($_POST['shop_id'],$random,$random);
        $shopUserNewLogin = ShopUser::getByToken($token);
        $options = array('except' => array('password'));
        response::success(make_json("result", $shopUserNewLogin,$options));
    }

}