<?php

namespace GFUnit\cardshop\valgshop;
use GFBiz\units\UnitController;

class Controller extends UnitController
{

    public function __construct()
    {
        parent::__construct(__FILE__);
    }

    public function searchCompany()
    {
      $searchText = $_POST["searchTxt"];
      $searchText = trimgf($searchText);
      $result =   \Dbsqli::getSql2("select id,name from shop where name like('%".$searchText."%') and is_company = 1 and language_id = 1 order by name");
      $return = array("status"=>1,"data"=>$result);
      echo json_encode($return) ;
    }
    public function getCompanyEmployees($shopID)
    {
        // hvis der skal tjekkes om bruger er blokkeret eller lukket
        $blockedShutdown  =  in_array($shopID,array(4346,6593)) ? "": " AND shop_user.blocked = 0  and shop_user.blocked = 0 ";
         

        $result =   \Dbsqli::getSql2("SELECT
                    shop_user_replaced.username as r_username,
                    shop_user_replaced.id as r_id,
                    shop_user_replaced.password as r_password,
                    shop_user.is_replaced, shop_user.id as shopuser_id, `order`.`user_username`,`order`.`user_email`,`order`.`user_name`,present_model.model_present_no,present_model.model_no ,present_model.model_name,present_model.fullalias  FROM `shop_user`
                    left JOIN `order` on shop_user.id = `order`.`shopuser_id`
                    left join present_model on `order`.`present_model_id` = present_model.model_id and present_model.language_id = 1
                    left join (select * from shop_user where replacement_id != 0) as shop_user_replaced on  shop_user.id = shop_user_replaced.replacement_id
                    WHERE
                    shop_user.shop_id  = ".intval($shopID)." 
                    ".$blockedShutdown."
                    and shop_user.is_demo = 0;

                    ");
      $return = array("status"=>1,"data"=>$result);
      echo json_encode($return) ;

     }



}


/*
           $result =   \Dbsqli::getSql2("SELECT order_history.*, present_model.model_name, present_model.model_no,present_model.model_present_no,present_model.media_path FROM `order_history`
                        inner JOIN present_model on order_history.present_model_id = present_model.model_id and present_model.language_id = 1
                        WHERE shopuser_id in ( SELECT `shopuser_id` FROM `order_history` WHERE `user_username` =  ".$cardnr."  and `shop_is_gift_certificate` = 1) and `shop_is_gift_certificate` = 1 order by id DESC");
           $return = array("status"=>1,"data"=>$result);
           echo json_encode($return) ;

           */