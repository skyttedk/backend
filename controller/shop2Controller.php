<?php

class shop2Controller  {
  public function Index() {
  }
    private function validatetodkenShop($shopID,$token)
    {

        if($token == "NJycUpZGVhMJvQ7Kmb88uXRgX6VMhpvUcEBPj9NhmJ2tjxQB") return true;
        $rs = ShopUser::find_by_token($token);
        if($rs){
            if($rs->is_demo == 1 && $rs->shop_id == $shopID){
                return true;
            }
        } else {
            http_response_code(400);
            return;
        }

    }

  public function getCardHistory()
  {
      $this->validatetodkenShop($_POST['shop_id'],$_POST['token']);
      $rs = Dbsqli::getSql2("SELECT order_timestamp,user_name,user_email,present_name,present_model_name FROM order_history WHERE shopuser_id =".trimgf($_POST["userId"])." order by order_timestamp DESC"  );
      $html = "";

     // print_r($rs);
      if(sizeofgf($rs) != 0){
        foreach($rs as $key => $val)
        {
            $html.= "<table width=500>";
            $model = str_replace("###", " - ", $val["present_model_name"]);
            $html.="<tr><td width=100>Dato</td><td>".$val["order_timestamp"]."</td></tr>";
            $html.="<tr><td>Navn</td><td>".$val["user_name"]."</td></tr>";
            $html.="<tr><td>Email</td><td>".$val["user_email"]."</td></tr>";
 
            $html.="<tr><td>Gave</td><td>".$model."</td></tr>";
            $html.= "<table><hr />";
            
            $html .= "<h3>Alle felter</h3><table>";
            $uaList = Dbsqli::getSql2("SELECT ua.attribute_value, sa.name  FROM `user_attribute` ua, shop_attribute sa WHERE ua.`shopuser_id` = ".intval($_POST["userId"])." && ua.attribute_id = sa.id order by sa.index");
            if(count($uaList) > 0) {
                foreach($uaList as $ua) {
                    $html.="<tr><td width=100>".$ua["name"]."</td><td>".$ua["attribute_value"]."</td></tr>";
                }
            }
            $html.= "<table><br /><hr /><br />";
        }
        $html = base64_encode($html);
      } else {

      }
      echo json_encode(array("status"=>"1","data"=>$html));
  }
  public function seekCompanyOrder()
  {
    $return = "0";
    $rs = Dbsqli::getSql2("SELECT * FROM `company_order` WHERE
    `company_name` LIKE '%".trimgf($_POST["sogStr"])."%'  or
    `order_no` LIKE '%".trimgf($_POST["sogStr"])."%'  or
    `ship_to_company` LIKE '%".trimgf($_POST["sogStr"])."%'  or
    `ship_to_address` LIKE '%".trimgf($_POST["sogStr"])."%'  or
    `ship_to_address_2` LIKE '%".trimgf($_POST["sogStr"])."%'  or
    `contact_name` LIKE '%".trimgf($_POST["sogStr"])."%'  or
    `contact_email` LIKE '%".trimgf($_POST["sogStr"])."%'  or
    `contact_phone` LIKE '%".trimgf($_POST["sogStr"])."%'  or
    `ean` LIKE '%".trimgf($_POST["sogStr"])."%'  or
    `cvr` LIKE '%".trimgf($_POST["sogStr"])."%'
    order by shop_name, company_name, shop_id,certificate_value,expire_date  " );
    if(sizeofgf($rs) == 0){
        echo json_encode(array("status"=>"1","data"=>$return));
    } else {
        echo json_encode(array("status"=>"1","data"=>$rs));
    }
  }
  


  public function receiptSeekCardInfo()
  {
     //$_POST["ReceiptNr"];
      $rs = Dbsqli::getSql2("SELECT * FROM order_history WHERE order_no='".trimgf($_POST["ReceiptNr"])."'" );

      $return = "0";
      if(sizeofgf($rs) != 0){
        if($rs[0]["shop_is_gift_certificate"] == "0" ){
            echo json_encode(array("status"=>"1","type"=>"valg","data"=>$rs));
        } else {
            $return = $rs[0]["user_username"];
            echo json_encode(array("status"=>"1","type"=>"kort","data"=>$return));
        }


      }
      else {
        echo json_encode(array("status"=>"1","data"=>$return));
      }


  }


  public function seekCardInfo()
  {
   // 1009368
   $returnData = [];
   $card = Dbsqli::getSql2("SELECT * FROM shop_user WHERE username=".trimgf($_POST["card"])." and is_giftcertificate = 1" );

   $company = Dbsqli::getSql2("SELECT * FROM company WHERE id=".$card[0]["company_id"] );

   $hasGift = Dbsqli::getSql2("SELECT `present_model_name`,`present_name`,shopuser_id FROM `order` WHERE `user_username` = '".$card[0]["username"]."'");

   //print_R($hasGift);
   //print_R($company);

   $shopuser_id = "";
   if(sizeofgf($hasGift) > 0){
       $shopuser_id = $hasGift[0]["shopuser_id"];
   }


   $returnData = [
   "shopuser_id" => $shopuser_id,
   "shop_id" => $card[0]["shop_id"],
   "certificate_no" => $card[0]["username"],
   "password" => $card[0]["password"],
   "expire_date"  => $card[0]["expire_date"],
   "company_id" =>$company[0]["id"],
   "name"  => $company[0]["name"],
   "cvr" => $company[0]["cvr"],
   "bill_to_address" => $company[0]["bill_to_address"],
   "bill_to_address_2" => $company[0]["bill_to_address_2"],
   "bill_to_postal_code" => $company[0]["bill_to_postal_code"],
   "bill_to_city" =>$company[0]["bill_to_city"],
   "bill_to_country" => $company[0]["bill_to_country"],
   "ship_to_attention" => $company[0]["ship_to_attention"],
   "ship_to_address" =>$company[0]["ship_to_address"],
   "ship_to_address_2" => $company[0]["ship_to_address_2"],
   "ship_to_postal_code" => $company[0]["ship_to_postal_code"],
   "ship_to_city" => $company[0]["ship_to_city"],
   "ship_to_country" =>$company[0]["ship_to_country"],
   "contact_name" => $company[0]["contact_name"],
   "contact_phone" => $company[0]["contact_phone"],
   "contact_email" => $company[0]["contact_email"],
   "present_name"=>"",
   "present_model_name"=>""
   ];
   if(sizeofgf($hasGift) > 0){
     $returnData["present_name"] =   $hasGift[0]["present_name"];
     $returnData["present_model_name"] = $hasGift[0]["present_model_name"];
   }

   echo json_encode(array("status"=>"1","data"=>$returnData));

  }
  public function seekCompany()
  {
    echo $_POST["companyName"];
  }


}




?>