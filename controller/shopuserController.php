<?php
class shopuserController Extends baseController {

    public function Index() {
//    $shops = Shop::all();
//    $this->registry->template->shops = $shops;
//    $this->registry->template->show('shoplist');
        echo "shopuserController";
    }
    public function addBudgetToShopuser(){
        $shop_id = $_POST["shop_id"];
        $attribute_id = $_POST["target_att"];
        $UserAttributeList = UserAttribute::find('all', array(
            'conditions' => array(
                'shop_id = ? AND attribute_id = ?',
                $shop_id, $attribute_id
            )
        ));

        foreach ($UserAttributeList as $user){
            
            if($user->attributes["attribute_value"] != ""){

                $value = $user->attributes["attribute_value"];
                $shopUser = ShopUser::find($user->attributes["shopuser_id"]);
                $shopUser->card_values = $value;
                $shopUser->save();
            }
        }
        $dummy = [];
        response::success(json_encode($dummy));
    }

}

?>