<?php
ini_set('memory_limit', '400M');
// Controller shop
// Date created  Sun, 03 Apr 2016 21:00:47 +0200
// Created by Bitworks
class ShoploadController Extends baseController {

    public function Index() {

    }

    public function getUsers()
    {
        $shopid = intval($_POST['id']);
                       
        // Get shop users
        ShopUser::$skipCalculatedAttributes = true;

        $shopusers = ShopUser::all(array('shop_id' => $shopid,'is_demo' => 0));

           // Get user attributes
        $userattributes = UserAttribute::find('all',array('conditions' => array('shop_id' => $shopid)));
                   
        $userattributeMap = array();
        foreach($userattributes as $ua)
        {
            if(!isset($userattributeMap[$ua->attributes["shopuser_id"]]))
            {
                $userattributeMap[$ua->attributes["shopuser_id"]] = array();
            }
            $userattributeMap[$ua->attributes["shopuser_id"]][] = $ua;
        }

        // Get user orders
        $userorders = Order::find('all',array('conditions' => array('shop_id' => $shopid)));
            
        $userorderMap = array();
        foreach($userorders as $uo)
        {

            // Create array for user, if user id index does not exist
            if(!isset($userorderMap[$uo->attributes["shopuser_id"]]))
            {
                $userorderMap[$uo->attributes["shopuser_id"]] = array("attr" => array());
            }

            // Get attribute for "orders" index in response
            $orderattributes = $uo->attributes;
            if(isset($orderattributes["order_timestamp"]))
            {
                $orderattributes["order_timestamp"] = $orderattributes["order_timestamp"]->format('Y-m-d');
            }
            $userorderMap[$uo->attributes["shopuser_id"]]["attr"][] = $orderattributes;
        }
              
        // Prepare date
        $responseList = array();
        foreach($shopusers as $shopuser)
        {

            // Get user attributes
            $user = $shopuser->attributes;
            if(isset($user["token_created"]))
            {
                //$user["token_created"] = $user["token_created"];
                $user["token_created"] = $user["token_created"]->format('Y-m-d');
            }

            // Add orders
            $user["orders"] = isset($userorderMap[$user["id"]]) ? $userorderMap[$user["id"]]["attr"] : array();

            // Add user_attributes
            $user["user_attributes"] = isset($userattributeMap[$user["id"]]) ? $userattributeMap[$user["id"]] : array();

            // Add has_orders parameter
            $user["has_orders"] = countgf($user["orders"]) > 0;

            // Add user to response
            $responseList[] = $user;

        }
                
        // Prepare and output response
        $response = array("status" => 1,"message" => "", "data" => array("users" => $responseList));
        echo json_encode($response);

    }

}
?>

