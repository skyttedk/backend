<?php

class dbcalcController Extends baseController
{
  public function Index() {
        echo "hej";
  }
  public function getDbCalcValgshop()
  {
       // '00017011'
       $shopID = $_POST["shopID"];
    // find antal total
        $totalPresentInShopRS = Dbsqli::getSql2("SELECT COUNT(*) as total  FROM `shop_user` WHERE `shop_id` = ".$shopID." AND `is_demo` = 0 AND `is_giftcertificate` = 0 AND `blocked` = 0 AND `shutdown` = 0");
   //  find antal med gavevalg
       $standardPresentRS = Dbsqli::getSql2("SELECT standard_cost,description  FROM `navision_item` WHERE `no` LIKE (select dbcalc_standard from shop where id = ".$shopID.")");
  // find gave info p� valgte, saml�g antal og standard cost
    $rs = Dbsqli::getSql2("  SELECT  `order`.id,  `present_model`.model_id, count(`order`.id) as c_order ,standard_cost as sum_standard_cost,`present_model`.`model_present_no`,`present_model`.`model_name`,model_no,media_path FROM `order`
        inner join present_model on `order`.present_model_id =  `present_model`.model_id
        left join navision_item on `present_model`.`model_present_no` = navision_item.no
            where
    	`order`.shop_id = ".$shopID." and
    	`present_model`.language_id = 1 and
        navision_item.language_id = 1 group by `present_model`.`model_present_no`"
        );

  $return = [
    "totalPresentInShop" => $totalPresentInShopRS,
    "standardPresent"    => $standardPresentRS,
    "selectedPresent"    => $rs
  ];
  response::success(json_encode($return));


  }
  public function getShopData(){
        $shopID = $_POST["shopID"];
        $shop = Shop::find('all', array('id' => $shopID));
        response::success(make_json("shop", $shop));

  }
  public function getShopSettings()
  {
       $shopSetting = Dbsqli::getSql2("SELECT shop.id,shop.name,cs.card_price,cs.card_db FROM `cardshop_settings` as cs
                                        INNER JOIN `shop` on shop.id = cs.shop_id and language_code = 1 and shop_id != 2999 order by shop.name");
       response::success(json_encode($shopSetting));
  }

  public function updateOptions()
  {
    $data = $_POST;
    $shop = Shop::find($data["id"]);
    $shop->update_attributes($data);
    $return = $shop->save();
    response::success(make_json("shop", $shop));
  }


  public function regFallbackPresent()
  {
        $itemnr = $_POST["itemnr"];
        $shopID = $_POST["shopid"];
        $sql = "select present_model where model_present_no = '".$itemnr."'";
        $rs = Dbsqli::getSql2($sql);
  }


  public function getForecast()
  {



  }






}




?>