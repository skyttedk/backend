<?php

namespace GFUnit\cardshop\cards;
use GFBiz\Model\Cardshop\CardshopSettingsLogic;
use GFBiz\units\UnitController;
use GFCommon\Model\Navision\CountryHelper;
use GFUnit\cardshop\cards\ProformaFaktura;
use GFUnit\navision\syncprivatedelivery\PrivateDeliverySync;

class Controller extends UnitController
{

    public function __construct()
    {
        parent::__construct(__FILE__);
    }

    /**
     * SERVICES
     */

    public function getMultibleReplacementCard(){
        echo "getMultibleReplacementCard";
    }


    public function proformainvoice() {

        $cardlist = explode(",",$_POST["data"]);

        // Make sure all elements is a string, trim it and remove empty elements
        $cardlist = array_filter(array_map("trim",$cardlist),"strlen");

        // If no elements, throw exception
        if(count($cardlist) == 0) {
            throw new \Exception("No cards provided");
        }

        // Load shopusers
        $shopusers = \ShopUser::find("all",array("conditions" => array("username IN ('".implode("','",$cardlist)."')")));

        // Check shopusers is in cardlist with ->username and
        if(count($shopusers) != count($cardlist)) {
            throw new \Exception("Mismatch in cardlist and shopusers");
        }

        // find number of unique ->shop_id attribute values in shop users
        $companyids = array_unique(array_map(function($shopuser) { return $shopuser->company_id; },$shopusers));

        // if multiple, throw exeption
        if(count($companyids) > 1) {
            throw new \Exception("Multiple shops in cardlist");
        }

       
        $proformafaktura = new ProformaFaktura($shopusers,$companyids[0]);
        $proformafaktura->downloadFakturaV2();

    }

    public function getReplacementCardData($userid)
    {
        $rs = \Dbsqli::getSql2("select shop_user.*, IF(company_order.floating_expire_date IS NULL,company_order.expire_date,DATE(company_order.floating_expire_date)) as expire_date from shop_user left join company_order on shop_user.company_order_id = company_order.id where shop_user.replacement_id = ".$userid);

        $response = array("status" => 1, "data" => $rs);
        echo json_encode($response);


    }

    public function deleteReplacementCard($replacmentID)
    {




        $shopUSerReplaceCard = \ShopUser::find($replacmentID);
        $replacementID = $shopUSerReplaceCard->replacement_id;



        $shopUSerOrgCard = \ShopUser::find($replacementID);


        $shopUSerReplaceCard->replacement_id =  $shopUSerReplaceCard->replacement_id*-1;
        $res =  $shopUSerReplaceCard->save();
        \System::connection()->commit();
        \System::connection()->transaction();

        $shopUSerOrgCard->is_replaced = 0;
        $shopUSerOrgCard->save();
        \System::connection()->commit();
        \System::connection()->transaction();


        $dummy = [];
        \response::success(make_json("result",$dummy));
        //echo $shopUSerOrgCard->id;


        /*
        $rs = \Dbsqli::getSql2($sql);


        echo $rs[0]["replacement_id"];
        //is_replaced
        */
    }
    public function getCardData($userid)
    {
        $rs = \Dbsqli::getSql2("select * from shop_user where id = ".$userid);

        $response = array("status" => 1, "data" => $rs);

        // Load shipment data for card if exists
        if(count($rs) > 0) {
            $username = $rs[0]["username"];
            $shipment = \Shipment::find_by_sql("select * from shipment where shipment_type in ('privatedelivery','directdelivery') and from_certificate_no = '".$username."'");


            if(count($shipment) > 0) {
                $response["countries"] = CountryHelper::getLanguageMap();
                $response["country"] = $shipment[0]->shipto_country;
            }

        }

        echo json_encode($response);


    }
    public function getReplacementCard($companyid=0)
    {
        if(intval($companyid) <= 0) {
            throw new \Exception("Please provide a company id");
        }

        $isBlocked = 0;
        if(isset($_POST["isblocked"])) {
            if(intval($_POST["isblocked"]) > 0) $isBlocked = 1;
            else $isBlocked = 0;
        }
       /*
        $sql = "SELECT company_order.order_no as bsno, shop_user.id, shop_user.id as shopuser_id, shop_user.shop_id, company_order.company_id as order_company_id, shop_user.company_id as delivery_company_id, shop_user.username, shop_user.password,shop_user.expire_date, shop_user.blocked, shop_user.is_delivery,shop_user.company_order_id, shop_user.delivery_print_date,shop_user.navsync_date, shop_user.navsync_status, shop_user.created_date,
        `order`.id as order_id, `order`.order_no, `order`.order_timestamp, `order`.user_email, `order`.user_name, `order`.present_id, `order`.present_model_id, `order`.present_model_name, `order`.present_model_present_no
        FROM company_order, `shop_user` left join `order` on shop_user.id = `order`.`shopuser_id`
        WHERE company_order.id = shop_user.company_order_id && `shop_user`.company_id = ".intval($companyid)." && (`order`.shop_is_gift_certificate = 1 || `order`.shop_is_gift_certificate is null) && (shop_user.blocked = ".($isBlocked == 0 ? 0 : 1).")
        ORDER BY shop_user.shop_id ASC, shop_user.username ASC";
      */
        $sql = "   SELECT

	shop_user.id, shop_user.id as shopuser_id,
	shop_user.shop_id,
    shop_user.company_id as delivery_company_id,
	shop_user.company_id,
	shop_user.username,
	shop_user.password,
	IF(company_order.floating_expire_date IS NULL,company_order.expire_date,DATE(company_order.floating_expire_date)) as expire_date,
	shop_user.blocked,
	shop_user.is_delivery,
	shop_user.company_order_id,
	shop_user.delivery_print_date,
	shop_user.navsync_date,
	shop_user.navsync_status,
	shop_user.created_date,
    shop_user.replacement_id,
	`order`.id as order_id,
	`order`.order_no,
	`order`.order_timestamp,
	`order`.user_email,
	`order`.user_name,
	`order`.present_id,
	`order`.present_model_id,
	present_model.model_name,
	present_model.model_no,
	present_model.model_present_no,
	present_model.fullalias,
    shop.alias as shopalias,
    repl.username as repl_username,
    shipment.shipment_type,
    shipment.shipment_state,
    shipment.consignor_created


	FROM shop_user
    inner JOIN ( SELECT id,username FROM `shop_user` WHERE `company_id` = ".intval($companyid)." AND `is_replaced` = 1 ) as repl on `shop_user`.replacement_id = repl.id
    left join `order` on shop_user.id = `order`.`shopuser_id`
    left join present_model on `order`.present_model_id = present_model.model_id
    inner join shop on shop_user.shop_id = shop.id
    inner join company_order on shop_user.company_order_id = company_order.id
    LEFT JOIN shipment ON `order`.id = shipment.to_certificate_no AND `order`.id > 0 AND shipment.shipment_type IN ('privatedelivery','directdelivery')
    WHERE
    	(`order`.shop_is_gift_certificate = 1 || `order`.shop_is_gift_certificate is null) && (present_model.id IS NULL or present_model.language_id = 1) &&
          shop_user.blocked = 0
        ";


        $cards = \ShopUser::find_by_sql($sql);
        \response::success(make_json("result", $cards,array("except" => array("shop_attributes","order","order_details","user_attributes"))));

    }



    public function getCardOrderHistory($userid)
    {

        $order =   \Dbsqli::getSql2("SELECT `order`.*, present_model.model_name, present_model.model_no,present_model.model_present_no,present_model.media_path,
                company.name,
                company.cvr,
                company.ean,
                company.id,
                company.pid,
                company.contact_name,
                company.contact_phone,
                company.contact_email
                        FROM `order`
                        inner JOIN present_model on `order` .present_model_id = present_model.model_id and present_model.language_id = 1
                        inner join company on  `order`.company_id =  company.id
                        WHERE shopuser_id = ".intval($userid)."  order by `order`.id DESC limit 30");





        $result2021Hist =   \Dbsqli::getSql2("SELECT order_history.*, present_model.model_name, present_model.model_no,present_model.model_present_no,present_model.media_path,
                company.name,
                company.cvr,
                company.ean,
                company.id,
                company.pid,
                company.contact_name,
                company.contact_phone,
                company.contact_email
                        FROM `order_history`
                        inner JOIN present_model on order_history.present_model_id = present_model.model_id and present_model.language_id = 1
                        inner join company on  `order_history`.company_id =  company.id
                        WHERE shopuser_id = ".intval($userid)."  order by `order_history`.id DESC limit 30");
             if(sizeofgf($result2021Hist) > 1 ){
                $result2021Hist[0] =  $order[0];
                $order = $result2021Hist;
             }
            $response = array("status" => 1, "data" => $order);
            echo json_encode($response);



    }

    public function getReplacementList($language=1)
    {
        if(intval($language) <= 0) $language = 1;
        // overrule hack no access to use luksuskort
        if(intval($language) == 4) {
            $language = "1 and  shop_id in (2960,2961,2962,2963)";
        }
        
        $rs = \Dbsqli::getSql2("select distinct (shop_user.shop_id), shop.alias, COUNT(shop_user.id) as antal from shop_user
                inner JOIN shop on shop_user.shop_id = shop.id
                where shop_user.company_id in (select replacement_company_id from cardshop_settings where language_code = ".intval($language)." && replacement_company_id > 0) and
                shop_user.blocked = 0 and
                shop_user.shutdown = 0 and
                (replacement_id = 0 or replacement_id IS NULL) GROUP by shop_user.shop_id, shop_user.card_values");

        $response = array("status" => 1, "data" => $rs);
        echo json_encode($response);
    }
    public function replacementCard()
    {

        $shopuserID = $_POST["shop_user"];
        $target = $_POST["target"];
        $language = $_POST["language"];

        if(intval($language) <= 0) $language = 1;

        $values = [2960, 2961, 2962, 2963];
        if($_POST["language"] == 4 and in_array($target, $values) ){
            $language = 1;
        }


        $cardshopWithLanguage = \CardshopSettings::find('first',array("conditions" => array("language_code = ".intval($language)." && replacement_company_id > 0")));
        $companyID = $cardshopWithLanguage->replacement_company_id;

        // udtr�kker alias
           $rs = \Dbsqli::getSql2("select * from shop_user
                                   where
                                     shop_user.company_id = ".$companyID." and
                                     (replacement_id = 0 or replacement_id IS NULL) and
                                     shop_id = ".$target."
                                   limit 1");
         $replacementID =   $rs[0]["id"];
         $sql1 = "update shop_user set replacement_id = ".$shopuserID." where id = ".$replacementID;
         $sql2 = "update shop_user set is_replaced = 1 where id = ".$shopuserID;
         \Dbsqli::setSql2($sql1);
         \Dbsqli::setSql2($sql2);
         echo json_encode(array("status" => 1));

    }


    public function insetDummyEmail()
    {
        $userid = $_POST["id"];
        $sql = "update user_attribute SET `attribute_value` = 'nomail' WHERE shopuser_id =". intval($userid)." and is_email = 1";
        \Dbsqli::setSql2($sql);
        echo json_encode(array("status" => 1));
    }
    public function canEditmasterData($userid)
    {
        $sql = "SELECT `order_state` FROM `company_order` WHERE id in (SELECT `company_order_id`  FROM `shop_user` WHERE `id` = ".intval($userid).")";
        $result =  \Dbsqli::getSql2($sql);
        $orderState = $result[0]["order_state"];
        $response = array("status" => 1, "data" => $orderState);
        echo json_encode($response);
    }

    public function getComplaintList($companyID)
    {
        $sql = "select shopuser_id,count(*) as antal from order_present_complaint where company_id = ". intval($companyID)." and complaint_txt != ''  group by shopuser_id ";
        $result =  \Dbsqli::getSql2($sql);
        $response = array("status" => 1, "data" => $result);
        echo json_encode($response);
    }


    public function getComplaint($userid)
    {
        $sql = "select * from order_present_complaint where `shopuser_id` = ". intval($userid). " ORDER BY `order_present_complaint`.`id` DESC limit 1";
        $result =  \Dbsqli::getSql2($sql);
        $response = array("status" => 1, "data" => $result);
        echo json_encode($response);
    }
    public function saveComplaint(){
        $userid = intval($_POST["shopuserID"]);
        $shopid = intval($_POST["shopID"]);
        $msg    =   $_POST["msg"];
        $sql = " insert into order_present_complaint (shopuser_id,company_id,complaint_txt) values(".$userid.",".$shopid.",'".$msg."')";

        $result =  \Dbsqli::SetSql2($sql);
        $response = array("status" => 1, "data" => $result);
        echo json_encode($response);
    }

    public function getMasterData($userid)
    {

        $sql = "SELECT shop_attribute.name,  `user_attribute`.* FROM `user_attribute`
                inner JOIN `shop_attribute` ON `user_attribute`.`attribute_id` =  `shop_attribute`.`id`
                WHERE `shopuser_id` = ". intval($userid)." and `user_attribute`.is_username = 0 and `user_attribute`.is_password = 0 and shop_attribute.name not like('%Gaveklubben%')
                order by `user_attribute`.attribute_id
                ";
        $result =  \Dbsqli::getSql2($sql);
        $response = array("status" => 1, "data" => $result);
        echo json_encode($response);


    }

    public function updateMasterData() {

       $masterData =  $_POST["masterData"];
       $orderData = [];
       if(isset($_POST["orderData"])){
            $orderData =  $_POST["orderData"];
       }

       // Find shopuserid
       $shopUserID = 0;
        foreach($masterData as $key=>$val){
            $shopUserID = intval($val["shopuser_id"]);
        }

        // Load shopuser
        $shopUser = \ShopUser::find($shopUserID);

        $countryUpdateShipment = null;

        // If has shipment, find it and remove
        if ($shopUser->is_giftcertificate == 1 && intvalgf($shopUser->username) > 0) {

            // Tjek om forsendelse er oprettet
            $shipmentList = \Shipment::find_by_sql("SELECT * FROM shipment WHERE from_certificate_no = ".intvalgf($shopUser->username)." && shipment_type in ('privatedelivery','directdelivery')");
            if (count($shipmentList) > 0) {

                // If waiting, remove and reset shop_user
                if($shipmentList[0]->shipment_state <= 1 ) {

                    // Remove shipment
                    $shipment = \Shipment::find($shipmentList[0]->id);
                    $shipment->delete();

                    // Update shop user
                    $shopUser->delivery_state = 0;
                    $shopUser->navsync_response = 0;
                    $shopUser->delivery_print_date = null;
                    $shopUser->save();

                }

                // Foreign delivery
                else if($shipmentList[0]->shipment_state == 9) {

                    // Check for country
                    if(isset($_POST["country"])) {

                        $country = trimgf($_POST["country"]);
                        if($country == "" || CountryHelper::countryToCode($country) != null) {

                            $countryUpdateShipment = \Shipment::find($shipmentList[0]->id);

                        } else {
                            throw new \Exception('Land ikke angivet!');
                        }

                    } else {
                        throw new \Exception('Land ikke angivet');
                    }

                }

                // Is sent, reject order
                else {
                    throw new \Exception('Ordren er allerede sendt - kan ikke opdatere informationer!');
                }

            }

        }

        // Check if delivery_state can be reset
        if($shopUser->is_giftcertificate == 1 && $shopUser->delivery_state > 10) {
            $shopUser->delivery_state = 0;
            $shopUser->save();
        }


        // Update shopuser data
       foreach($masterData as $key=>$val){
          $sql_order_attribute = "update order_attribute set attribute_value = '".$val["attribute_value"]."' where shopuser_id = ".intval($val["shopuser_id"])." and attribute_id = ".intval($val["attribute_id"]);
          $sql_user_attribute = "update user_attribute set attribute_value = '".$val["attribute_value"]."' where shopuser_id = ".intval($val["shopuser_id"])." and attribute_id = ".intval($val["attribute_id"]);

          \Dbsqli::setSql2($sql_order_attribute);
          \Dbsqli::setSql2($sql_user_attribute);
       }

       foreach($orderData as $key=>$val){
            if($val["field"] == "name"){
                $sql_order = "UPDATE `order` SET `user_name` = '".$val["attribute_value"]."' WHERE `shopuser_id` = ".intval($val["shopuser_id"]);
                \Dbsqli::setSql2($sql_order);
            }
            if($val["field"] == "email"){
                $sql_order = "UPDATE `order` SET `user_email` = '".$val["attribute_value"]."' WHERE `shopuser_id` = ".intval($val["shopuser_id"]);
                \Dbsqli::setSql2($sql_order);
            }
       }

       // Update shipment country
        if($countryUpdateShipment != null) {

            // Get user data
            $pds = new PrivateDeliverySync();
            $userData = $pds->getUserData($shopUser->id,$shopUser->shop_id);

            $countryUpdateShipment->shipto_country = $country;

            $countryUpdateShipment->shipto_name = $userData["name"];
            $countryUpdateShipment->shipto_address = $userData["address"];
            $countryUpdateShipment->shipto_address2 = $userData["address2"];
            $countryUpdateShipment->shipto_postcode = $userData["postnr"];
            $countryUpdateShipment->shipto_city = $userData["bynavn"];
            $countryUpdateShipment->shipto_contact = $userData["name"];
            $countryUpdateShipment->shipto_email = $userData["email"];
            $countryUpdateShipment->shipto_phone = str_replace(array(" ","-"),"",trim(trim(utf8_encode($userData["telefon"]))));
            $countryUpdateShipment->save();

        }


       // Commit
        \system::connection()->commit();


       $response = array("status" => 1);
       echo json_encode($response);



    }

    public function getShopuserData($shopuserID)
    {
        $sql = "SELECT shop_user.*, shop.alias as shopalias, IF(company_order.floating_expire_date IS NULL,company_order.expire_date,DATE(company_order.floating_expire_date)) as expire_date  from shop_user
                inner join shop on shop_user.shop_id = shop.id left join company_order on shop_user.company_order_id = company_order.id
                where shop_user.id = ".intval($shopuserID) ;
        $result =  \Dbsqli::getSql2($sql);
        $response = array("status" => 1, "data" => $result);
        echo json_encode($response);

    }

    public function carddetails($cardno = "") {

        if(intval($cardno) <= 0) {
            throw new \Exception("No card number provided");
        }

        $shopUser = \ShopUser::find('all',array("conditions" => array("username" => $cardno,"is_giftcertificate" => 1)));
        \response::success(make_json("result",$shopUser));

    }

    // Get all cards in order
    public function ordercards($companyOrderID=0)
    {

        if(intval($companyOrderID) <= 0) {
            throw new \Exception("Please provide an order id");
        }

        $sql = "SELECT company_order.order_no as bsno, shop_user.id, shop_user.id as shopuser_id, shop_user.shop_id, company_order.company_id as order_company_id, shop_user.company_id as delivery_company_id, shop_user.company_id, shop_user.username, shop_user.password,shop_user.expire_date, shop_user.blocked, shop_user.is_delivery,shop_user.company_order_id, shop_user.delivery_print_date,shop_user.navsync_date, shop_user.navsync_status, shop_user.created_date,
        `order`.id as order_id, `order`.order_no, `order`.order_timestamp, `order`.user_email, `order`.user_name, `order`.present_id, `order`.present_model_id, `order`.present_model_name, `order`.present_model_present_no
        FROM company_order, `shop_user` left join `order` on shop_user.id = `order`.`shopuser_id`
        WHERE company_order.id = shop_user.company_order_id && `company_order_id` = ".intval($companyOrderID)." && (`order`.shop_is_gift_certificate = 1 || `order`.shop_is_gift_certificate is null)
        ORDER BY shop_user.shop_id ASC, shop_user.username ASC";

        $sql = "SELECT 
	company_order.order_no as bsno, 
	shop_user.id, shop_user.id as shopuser_id, 
	shop_user.shop_id, 
	company_order.company_id as order_company_id,
	shop_user.company_id as delivery_company_id, 
	shop_user.company_id, 
	shop_user.username, 
	shop_user.password,
	IF(company_order.floating_expire_date IS NULL,shop_user.expire_date,DATE(company_order.floating_expire_date)) as expire_date,
	shop_user.blocked,
	shop_user.is_delivery,
	shop_user.company_order_id, 
	shop_user.delivery_print_date,
	shop_user.navsync_date, 
	shop_user.navsync_status, 
	shop_user.created_date,  
    
	`order`.id as order_id, 
	`order`.order_no, 
	`order`.order_timestamp, 
	`order`.user_email, 
	`order`.user_name,
	`order`.present_id,
	`order`.present_model_id, 
	
	present_model.model_name,
	present_model.model_no,
	present_model.model_present_no,
	present_model.fullalias,
    shop.alias as shopalias

FROM company_order, shop, `shop_user` left join `order` on shop_user.id = `order`.`shopuser_id` left join present_model on `order`.present_model_id = present_model.model_id
WHERE shop_user.shop_id = shop.id && company_order.id = shop_user.company_order_id && `company_order_id` = ".intval($companyOrderID)." && (`order`.shop_is_gift_certificate = 1 || `order`.shop_is_gift_certificate is null) && (present_model.id IS NULL or present_model.language_id = 1)
";

        $cards = \ShopUser::find_by_sql($sql);
        \response::success(make_json("result", $cards,array("except" => array("shop_attributes","order","order_details","user_attributes"))));

    }



    public function companycards($companyid=0)
    {

        if(intval($companyid) <= 0) {
            throw new \Exception("Please provide a company id");
        }


        if(in_array($companyid,array(45363, 45364, 45365))) {
            \response::success(make_json("result", array(),array("except" => array("shop_attributes","order","order_details","user_attributes"))));
            return;
        }

        $isBlocked = 0;
        if(isset($_POST["isblocked"])) {
            if(intval($_POST["isblocked"]) > 0) $isBlocked = 1;
            else $isBlocked = 0;
        }
       /*
        $sql = "SELECT company_order.order_no as bsno, shop_user.id, shop_user.id as shopuser_id, shop_user.shop_id, company_order.company_id as order_company_id, shop_user.company_id as delivery_company_id, shop_user.username, shop_user.password,shop_user.expire_date, shop_user.blocked, shop_user.is_delivery,shop_user.company_order_id, shop_user.delivery_print_date,shop_user.navsync_date, shop_user.navsync_status, shop_user.created_date,
        `order`.id as order_id, `order`.order_no, `order`.order_timestamp, `order`.user_email, `order`.user_name, `order`.present_id, `order`.present_model_id, `order`.present_model_name, `order`.present_model_present_no
        FROM company_order, `shop_user` left join `order` on shop_user.id = `order`.`shopuser_id`
        WHERE company_order.id = shop_user.company_order_id && `shop_user`.company_id = ".intval($companyid)." && (`order`.shop_is_gift_certificate = 1 || `order`.shop_is_gift_certificate is null) && (shop_user.blocked = ".($isBlocked == 0 ? 0 : 1).")
        ORDER BY shop_user.shop_id ASC, shop_user.username ASC";
      */

        $isCardshopAdmin = CardshopSettingsLogic::isCardshopAdmin(0);
    
        $sql = "SELECT
	company_order.order_no as bsno,
	shop_user.id, shop_user.id as shopuser_id,
	shop_user.shop_id,
	company_order.company_id as order_company_id,
	shop_user.company_id as delivery_company_id,
	shop_user.company_id,
	shop_user.username,
	shop_user.password,
	IF(company_order.floating_expire_date IS NULL,shop_user.expire_date,DATE(company_order.floating_expire_date)) as expire_date,
	shop_user.blocked,
	shop_user.is_delivery,
	shop_user.company_order_id,
	shop_user.delivery_print_date,
	shop_user.navsync_date,
	shop_user.navsync_status,
	shop_user.created_date,
    shop_user.is_replaced,
    shop_user.shutdown,
	`order`.id as order_id,
	`order`.order_no,
	`order`.order_timestamp,
	`order`.user_email,
	`order`.user_name,
	`order`.present_id,
	`order`.present_model_id,
	present_model.model_name,
	present_model.model_no,
	present_model.model_present_no,
	present_model.fullalias,
    CONCAT(shop.alias, COALESCE(CONCAT(' ', company_order.card_values), '')) as shopalias,
    shipment.shipment_type,
    shipment.shipment_state,
    shipment.consignor_created,
    ".($isCardshopAdmin ? 1 : 0)." as is_admin
FROM 
	shop_user
    INNER JOIN shop ON shop_user.shop_id = shop.id
    INNER JOIN company_order ON company_order.id = shop_user.company_order_id
    LEFT JOIN `order` ON shop_user.id = `order`.`shopuser_id`
    LEFT JOIN present_model ON `order`.present_model_id = present_model.model_id AND present_model.language_id = 1
    LEFT JOIN shipment ON `order`.id = shipment.to_certificate_no AND `order`.id > 0 AND shipment.shipment_type IN ('privatedelivery','directdelivery')
WHERE 
	`shop_user`.company_id = ".intval($companyid)." 
    AND (`order`.shop_is_gift_certificate = 1 OR `order`.shop_is_gift_certificate IS NULL OR `order`.id IS NULL)
    AND (shop_user.blocked = ".($isBlocked == 0 ? 0 : 1).")
GROUP BY 
	shop_user.username
ORDER BY 
	shop_user.shop_id ASC, shop_user.username ASC;

";


        $cards = \ShopUser::find_by_sql($sql);
        \response::success(make_json("result", $cards,array("except" => array("shop_attributes","order","order_details","user_attributes"))));

    }

    public function companycards2($companyid=0)
    {

        if(intval($companyid) <= 0) {
            throw new \Exception("Please provide a company id");
        }


        if(in_array($companyid,array(45363, 45364, 45365))) {
            \response::success(make_json("result", array(),array("except" => array("shop_attributes","order","order_details","user_attributes"))));
            return;
        }

        $isBlocked = 0;
        if(isset($_POST["isblocked"])) {
            if(intval($_POST["isblocked"]) > 0) $isBlocked = 1;
            else $isBlocked = 0;
        }

        $sql = "SELECT
	company_order.order_no as bsno,
	shop_user.id, shop_user.id as shopuser_id,
	shop_user.shop_id,
	company_order.company_id as order_company_id,
	shop_user.company_id as delivery_company_id,
	shop_user.company_id,
	shop_user.username,
	shop_user.password,
	IF(company_order.floating_expire_date IS NULL,shop_user.expire_date,DATE(company_order.floating_expire_date)) as expire_date,
	shop_user.blocked,
	shop_user.is_delivery,
	shop_user.company_order_id,
	shop_user.delivery_print_date,
	shop_user.navsync_date,
	shop_user.navsync_status,
	shop_user.created_date,
    shop_user.is_replaced,
    shop_user.shutdown,
	`order`.id as order_id,
	`order`.order_no,
	`order`.order_timestamp,
	`order`.user_email,
	`order`.user_name,
	`order`.present_id,
	`order`.present_model_id,
	present_model.model_name,
	present_model.model_no,
	present_model.model_present_no,
	present_model.fullalias,
    shop.alias as shopalias,
    shipment.shipment_type,
    shipment.shipment_state,
    shipment.consignor_created
FROM 
	shop_user
    INNER JOIN shop ON shop_user.shop_id = shop.id
    INNER JOIN company_order ON company_order.id = shop_user.company_order_id
    LEFT JOIN `order` ON shop_user.id = `order`.`shopuser_id`
    LEFT JOIN present_model ON `order`.present_model_id = present_model.model_id AND present_model.language_id = 1
    LEFT JOIN shipment ON `order`.id = shipment.to_certificate_no AND `order`.id > 0 AND shipment.shipment_type IN ('privatedelivery','directdelivery')
WHERE 
	`shop_user`.company_id = ".intval($companyid)." 
    AND (`order`.shop_is_gift_certificate = 1 OR `order`.shop_is_gift_certificate IS NULL OR `order`.id IS NULL)
    AND (shop_user.blocked = ".($isBlocked == 0 ? 0 : 1).")
GROUP BY 
	shop_user.username
ORDER BY 
	shop_user.shop_id ASC, shop_user.username ASC;

";


        $cards = \ShopUser::find_by_sql($sql);
        \response::success(make_json("result", $cards,array("except" => array("shop_attributes","order","order_details","user_attributes"))));

    }

    public function parentcards($companyid=0) {

        if(intval($companyid) <= 0) {
            throw new \Exception("Please provide a company id");
        }

        $sql = "SELECT company_order.order_no as bsno, shop_user.id, shop_user.id as shopuser_id, shop_user.shop_id, company_order.company_id as order_company_id, shop_user.company_id as delivery_company_id, shop_user.username, shop_user.password,shop_user.expire_date, shop_user.blocked, shop_user.is_delivery,shop_user.company_order_id, shop_user.delivery_print_date,shop_user.navsync_date, shop_user.navsync_status, shop_user.created_date,  
        `order`.id as order_id, `order`.order_no, `order`.order_timestamp, `order`.user_email, `order`.user_name, `order`.present_id, `order`.present_model_id, `order`.present_model_name, `order`.present_model_present_no
        FROM company_order, `shop_user` left join `order` on shop_user.id = `order`.`shopuser_id`
        WHERE company_order.id = shop_user.company_order_id && `company_order`.company_id = ".intval($companyid)." && (`order`.shop_is_gift_certificate = 1 || `order`.shop_is_gift_certificate is null)
        ORDER BY shop_user.shop_id ASC, shop_user.username ASC";

        $cards = \ShopUser::find_by_sql($sql);
        \response::success(make_json("result", $cards,array("except" => array("shop_attributes","order","order_details","user_attributes"))));

    }


    // Get all cards for a company
    public function loadCardsByCompanyID($companyid = 0)
    {

        $cards = \ShopUser::find_by_sql("SELECT shop.alias, shop_user.* from shop_user
           inner join shop on shop.id = shop_user.shop_id
           where company_id = ".intval($companyid)." and
           shop_user.is_demo = 0 and
           is_giftcertificate = 1 and
           shop_user.blocked = 0
           order by shop_id,username, expire_date,company_order_id");

       \response::success(make_json("result", $cards));


    }
    public function loadBlockedCardsByCompanyID($companyid = 0)
    {

        $cards = \ShopUser::find_by_sql("SELECT shop.alias, shop_user.*, IF(company_order.floating_expire_date IS NULL,company_order.expire_date,DATE(company_order.floating_expire_date)) as expire_date,  company_order.order_no as bsno from company_order, shop_user
           inner join shop on shop.id = shop_user.shop_id
           where shop_user.company_id = ".intval($companyid)." and
           shop_user.company_order_id = company_order.id and
           shop_user.is_demo = 0 and
           is_giftcertificate = 1 and
           shop_user.blocked = 1
           order by shop_id,username, expire_date,company_order_id");

       \response::success(make_json("result", $cards));


    }



    public function movecards()
    {
        $this->requirePost();

        $companyid = intval($_POST["companyid"]);
        $certificateList = $this->getCardList();

        // Check certificates
        if(count($certificateList) == 0) {
            throw new \Exception("No cards to update");
        }

        // Load company
        $company = \Company::find($companyid);

        // Load all certificates
        $shopuserList = \ShopUser::find("all",array("conditions" => array("(username IN (".implode(",",$certificateList).") && is_giftcertificate = 1 && company_order_id > 0)")));
        if(count($certificateList) != countgf($shopuserList)) {
            throw new \Exception("Mismatch in certificate list and shopusers, move aborted");
        }

        // Order into map
        $orderMap = array();
        foreach($shopuserList as $shopuser) {
            if(!isset($orderMap[$shopuser->company_order_id])) $orderMap[$shopuser->company_order_id] = array();
            $orderMap[$shopuser->company_order_id][] = $shopuser->username;
        }

        // Move per order
        foreach($orderMap as $companyOrderID => $orderCertificateList) {

            // Move cards
            $this->moveordercards($company,$companyOrderID,$orderCertificateList);

            // Create freight object
            if(isset($_POST["deliveryData"]) && is_array($_POST["deliveryData"])) {
                $this->createCardsFreight($company->id, $companyOrderID, $_POST["deliveryData"]);
            }

        }

        \System::connection()->commit();
        echo json_encode(array("status" => 1,"cards_updated" => countgf($certificateList)));

    }

    private function createCardsFreight($companyID,$companyOrderID,$deliveryData)
    {

        $freightObject = new \CardshopFreight();
        $freightObject->company_id = $companyID;
        $freightObject->company_order_id = $companyOrderID;
        $freightObject->created = new \DateTime();
        $freightObject->updated = new \DateTime();

        try {

            $companyOrder = \CompanyOrder::find($companyOrderID);
            $cardshopSettings = \CardshopSettings::find_by_shop_id($companyOrder->shop_id);

            // Set freight note
            $freightObject->note = trimgf($deliveryData['note']);

            // Set freight dot
            $freightObject->dot = intvalgf($deliveryData["dot_active"]) == 1 ? 1 : 0;
            if($freightObject->dot == 1) {

                // Dot date
                try {
                    $dotDate = new \DateTime($deliveryData['dot_description']);
                    if ($dotDate->format('Y') < \GFConfig::SALES_SEASON || $dotDate->format('Y') > \GFConfig::SALES_SEASON) {
                        throw new \Exception("Årstal ikke korrekt.");
                    }

                } catch (\Exception $e) {
                    $dotDate = null;
                }

                $freightObject->dot_date = $dotDate;
                $freightObject->dot_note = "";
                $freightObject->dot_pricetype = intvalgf($deliveryData['dot_price_type']);

                // Set price
                if($freightObject->dot_pricetype == 1) {
                    $freightObject->dot_price = $cardshopSettings->dot_price;
                } else if($freightObject->dot_pricetype == 2) {
                    $freightObject->dot_price = 0;
                } else if ($freightObject->dot_pricetype == 3) {
                    $freightObject->dot_price = intval(floatval($deliveryData['dot_price_amount'] ?? 0)*100);
                }

            } else {
                $freightObject->dot_note = "";
                $freightObject->dot_pricetype = 0;
                $freightObject->dot_price = 0;
            }

            // Set freight carryup
            $freightObject->carryup = intvalgf($deliveryData["carryup_active"]) == 1 ? 1 : 0;
            if($freightObject->carryup == 1) {

                $freightObject->carryup_pricetype = intvalgf($deliveryData['carryup_price_type']);
                if($freightObject->carryup_pricetype == 1) {
                    $freightObject->carryup_price = $cardshopSettings->carryup_price;
                } else if($freightObject->carryup_pricetype == 2) {
                    $freightObject->carryup_price = 0;
                } else if ($freightObject->carryup_pricetype == 3) {
                    $freightObject->carryup_price = intval(floatval($deliveryData['carryup_price_amount'] ?? 0)*100);
                }

                $freightObject->carryuptype = intvalgf($deliveryData['carryup_type']);

            } else {
                $freightObject->carryup_pricetype = 0;
                $freightObject->carryup_price = 0;
                $freightObject->carryuptype = 0;
            }

            // Determine if remove or save
            if($freightObject->note == "" && $freightObject->dot == 0 && $freightObject->carryup == 0) {

                if($freightObject->id > 0) {
                    $freightObject->delete();
                }
            } else {
                $freightObject->save();
            }

        }
        catch (\Exception $e) {
            mailgf("sc@interactive.dk", "Error creating cardshopfreight","CompanyID: ".$companyID." CompanyOrderID: ".$companyOrderID."<br>Error: ".$e->getMessage()."<br><br>".print_r($deliveryData,true)."<br><br>".print_r($_POST,true));
        }

    }

    private function moveordercards($company,$companyOrderID,$certificateList)
    {

        // Get min and max
        $minCertificate = min($certificateList);
        $maxCertificate = max($certificateList);

        if(count($certificateList) == 0) {
            throw new \Exception("No cards to update");
        }

        // Load order
        $companyOrder = \CompanyOrder::find($companyOrderID);
        if($companyOrder->certificate_no_begin > $minCertificate) {
            throw new \Exception("Certificate number ".$minCertificate." is out of range of the order certificates");
        }

        if($companyOrder->certificate_no_end < $maxCertificate) {
            throw new \Exception("Certificate number ".$maxCertificate." is out of range of the order certificates");
        }

        if($companyOrder->order_state > 6) {
            throw new \Exception("Order state does not allow moving cards");
        }

        // Update cards
        foreach ($certificateList as $certNumber) {

            $shopUsers = \ShopUser::find("all",array("conditions" => array("username" => $certNumber,"company_order_id" => $companyOrder->id)));

            if(count($shopUsers) != 1) {
                throw new \Exception("Could not find certificate ".$certNumber);
            }

            // Update shop user
            $shopUser = $shopUsers[0];
            $shopUser->company_id = $company->id;
            $shopUser->save();

        }

        \ActionLog::logAction("CardsMove", count($certificateList)." kort flyttet til leveringsadressen ".$company->name. " (".$company->ship_to_address.")","Følgende kort er flyttet: ".implode(", ",$certificateList),0,$companyOrder->shop_id,$companyOrder->company_id,$companyOrder->id,0,0,0);

        // Update nav in company order
        $companyOrder->nav_synced = 0;
        $companyOrder->save();

    }

    public function blockSingleCard($shopuserID)
    {
            $rs = \ShopUser::find("all",array("conditions" => array("id" => $shopuserID)));

            if(count($rs) != 1) {
                throw new \Exception("Could not find certificate card");
            }
            // Update shop user
            $shopUser = $rs[0];
            $replacementId =  $shopUser->replacement_id;
            $shopUser->blocked = 1;
            $shopUser->shutdown = 1;
            $shopUser->replacement_id = $shopUser->replacement_id*-1;
            $shopUser->save();

            if($replacementId != 0){
                $rs = \ShopUser::find("all",array("conditions" => array("id" => $replacementId)));
                $shopUser = $rs[0];
                $shopUser->blocked = 0;
                $shopUser->is_replaced = 0;
                $shopUser->save();


            }
            \System::connection()->commit();



            echo json_encode(array("status" => 1));
    }

    public function softclose($shopuserID) {

        $rs = \ShopUser::find("all",array("conditions" => array("id" => $shopuserID)));

        if(count($rs) != 1) {
            throw new \Exception("Could not find certificate card");
        }
        // Update shop user
        $shopUser = $rs[0];
        $shopUser->shutdown = 1;
        $shopUser->save();
        \System::connection()->commit();
        echo json_encode(array("status" => 1));

    }

    public function softopen($shopuserID) {

        $rs = \ShopUser::find("all",array("conditions" => array("id" => $shopuserID)));

        if(count($rs) != 1) {
            throw new \Exception("Could not find certificate card");
        }
        // Update shop user
        $shopUser = $rs[0];
        $shopUser->shutdown = 0;
        $shopUser->save();
        \System::connection()->commit();
        echo json_encode(array("status" => 1));

    }

    // Block cards  
    public function block($companyOrderID=0)
    {

        $this->requirePost();

        // Get inputs
        $companyOrderID = intval($companyOrderID);
        $certificateList = $this->getCardList();

        $minCertificate = min($certificateList);
        $maxCertificate = max($certificateList);

        // Load order
        $companyOrder = \CompanyOrder::find($companyOrderID);
        if($companyOrder->certificate_no_begin > $minCertificate) {
            throw new \Exception("Certificate number ".$minCertificate." is out of range of the order certificates");
        }

        if($companyOrder->certificate_no_end < $maxCertificate) {
            throw new \Exception("Certificate number ".$maxCertificate." is out of range of the order certificates");
        }

        if($companyOrder->order_state > 6 && $companyOrder->order_state != 10 ) {
            throw new \Exception("Order state does not allow blocking cards");
        }

        if(count($certificateList) == 0) {
            throw new \Exception("No cards to update");
        }

        // Update cards
        foreach ($certificateList as $certNumber) {

            $shopUsers = \ShopUser::find("all",array("conditions" => array("username" => $certNumber,"company_order_id" => $companyOrder->id)));

            if(count($shopUsers) != 1) {
                throw new \Exception("Could not find certificate ".$certNumber);
            }

            // Update shop user
            $shopUser = $shopUsers[0];
            $shopUser->blocked = 1;
            $shopUser->save();

        }

        // Update nav in company order
        $companyOrder->nav_synced = 0;
        $companyOrder->save();

        \ActionLog::logAction("CardsBlocked", count($certificateList)." kort slettet på ".$companyOrder->order_no,"Følgende kort er slettet: ".implode(", ",$certificateList),0,$companyOrder->shop_id,$companyOrder->company_id,$companyOrder->id,0,0,0);

        // 31606788
        \System::connection()->commit();
        if($companyOrder->order_state == 10){
               $body = "<table border=1><tr><th>Kortnr</th><th>BS-nummer</th><th>Firma navn</th><th>cvr</th><th>ean</th></tr>";
               foreach($certificateList as $number){
                    $cardRs = \ShopUser::find_by_sql("SELECT `order_no`, company.`name`, company.`cvr`,company.`ean` FROM `company_order`
                        inner join shop_user on `company_order`.`id` = shop_user.company_order_id
                        inner join company on company_order.company_id = company.id
                        WHERE shop_user.`username` = ".$number);
                    $card = $cardRs[0]->attributes;

                    $body.="<tr><td>".$number."</td><td>".$card["order_no"]."</td><td>".$card["name"]."</td><td>".$card["cvr"]."</td><td>".$card["ean"]."</td></tr>";
               }
               $body.= "</table>";
               $this->sendOrderState10Mail($body);
        }
        echo json_encode(array("status" => 1,"cards_updated" => countgf($certificateList)));

    }


// Block cards
    public function shutdown($companyOrderID=0)
    {

        $this->requirePost();

        // Get inputs
        $companyOrderID = intval($companyOrderID);
        $certificateList = $this->getCardList();

        $minCertificate = min($certificateList);
        $maxCertificate = max($certificateList);

        // Load order
        $companyOrder = \CompanyOrder::find($companyOrderID);
        if($companyOrder->certificate_no_begin > $minCertificate) {
            throw new \Exception("Certificate number ".$minCertificate." is out of range of the order certificates");
        }

        if($companyOrder->certificate_no_end < $maxCertificate) {
            throw new \Exception("Certificate number ".$maxCertificate." is out of range of the order certificates");
        }

        //if($companyOrder->order_state > 6 && $companyOrder->order_state != 10 ) {
        //    throw new \Exception("Order state does not allow blocking cards");
        //}

        if(count($certificateList) == 0) {
            throw new \Exception("No cards to update");
        }

        $blockedCards = [];
        $openedCards = [];

        // Update cards
        foreach ($certificateList as $certNumber) {

            $shopUsers = \ShopUser::find("all",array("conditions" => array("username" => $certNumber,"company_order_id" => $companyOrder->id)));

            if(count($shopUsers) != 1) {
                throw new \Exception("Could not find certificate ".$certNumber);
            }

            // Update shop user
            $shopUser = $shopUsers[0];
            if($shopUser->shutdown == 1) {
                $shopUser->shutdown = 0;
                $openedCards[] = $shopUser->username;
            } else {
                $shopUser->shutdown = 1;
                $blockedCards[] = $shopUser->username;
            }

            $shopUser->save();

        }

        if(count($blockedCards) > 0) {
            \ActionLog::logAction("CardsShutdown", count($blockedCards)." kort blokeret på ".$companyOrder->order_no,"Følgende kort er blokeret: ".implode(", ",$blockedCards),0,$companyOrder->shop_id,$companyOrder->company_id,$companyOrder->id,0,0,0);
        }

        if(count($openedCards) > 0) {
            \ActionLog::logAction("CardsOpened", count($openedCards)." kort har fået fjernet blokering på ".$companyOrder->order_no,"Følgende kort er åbnet: ".implode(", ",$openedCards),0,$companyOrder->shop_id,$companyOrder->company_id,$companyOrder->id,0,0,0);
        }


        // 31606788
        \System::connection()->commit();

        echo json_encode(array("status" => 1,"cards_updated" => countgf($certificateList)));

    }


    private function sendOrderState10Mail($body)
    {

        $maildata = [];
	    $maildata['sender_email'] =  "no-reply@gavefabrikken.dk";
	    $maildata['recipent_email'] =  "debitor@gavefabrikken.dk";
	    $maildata['subject']= utf8_encode("Slettede gavekort");
	    $maildata['body'] = utf8_decode($body);
	    $maildata['mailserver_id'] = 4;
        \MailQueue::createMailQueue($maildata);

        $maildata = [];
	    $maildata['sender_email'] =  "no-reply@gavefabrikken.dk";
	    $maildata['recipent_email'] =  "us@gavefabrikken.dk";
	    $maildata['subject']= utf8_encode("Slettede gavekort");
	    $maildata['body'] = utf8_decode($body);
	    $maildata['mailserver_id'] = 4;
        \MailQueue::createMailQueue($maildata);

    }


    // Unblock cards
    public function unblock($companyOrderID=0)
    {

        $this->requirePost();

        // Get inputs
        $companyOrderID = intval($companyOrderID);
        $certificateList = $this->getCardList();
        $minCertificate = min($certificateList);
        $maxCertificate = max($certificateList);

        // Load order
        $companyOrder = \CompanyOrder::find($companyOrderID);
        if($companyOrder->certificate_no_begin > $minCertificate) {
            throw new \Exception("Certificate number ".$minCertificate." is out of range of the order certificates");
        }

        if($companyOrder->certificate_no_end < $maxCertificate) {
            throw new \Exception("Certificate number ".$maxCertificate." is out of range of the order certificates");
        }

        if($companyOrder->order_state > 6) {
            throw new \Exception("Order state does not allow blocking cards");
        }

        if(count($certificateList) == 0) {
            throw new \Exception("No cards to update");
        }

        // Update cards
        foreach ($certificateList as $certNumber) {

            $shopUsers = \ShopUser::find("all",array("conditions" => array("username" => $certNumber,"company_order_id" => $companyOrder->id)));

            if(count($shopUsers) != 1) {
                throw new \Exception("Could not find certificate ".$certNumber);
            }

            // Update shop user
            $shopUser = $shopUsers[0];
            $shopUser->blocked = 0;
            $shopUser->save();

        }

        // Update nav in company order
        $companyOrder->nav_synced = 0;
        $companyOrder->save();

        \ActionLog::logAction("CardsUnblocked", count($certificateList)." kort genaktiveret på ".$companyOrder->order_no,"Følgende kort er aktiveret: ".implode(", ",$certificateList),0,$companyOrder->shop_id,$companyOrder->company_id,$companyOrder->id,0,0,0);


        \System::connection()->commit();
        echo json_encode(array("status" => 1,"cards_updated" => countgf($certificateList)));
        
    }


    public function removeorder() {

        $shopuserid = intval($_POST["shopuserid"]);

        // Load the shopuser
        $shopuser = \ShopUser::find($shopuserid);
        $orderList = \Order::find_by_sql("SELECT * FROM `order` WHERE shopuser_id = ".$shopuserid." and shop_id = ".$shopuser->shop_id);
        if(count($orderList) == 0) {
            throw new \Exception("No order found for shopuser");
        }

        $order = \Order::find($orderList[0]->id);
        if($order->shopuser_id != $shopuser->id) {
            throw new \Exception("Order does not match shopuser");
        }

        // Load order attributes
        $attributes = \OrderAttribute::find_by_sql("select * from order_attribute where order_id = ".$order->id);

        // Load shipment
        $shipmentList = \Shipment::find_by_sql("select * from shipment where from_certificate_no = '".$shopuser->username."' and to_certificate_no = ".$order->id." and shipment_type in ('privatedelivery', 'directdelivery')");
        $shipment =  $shipmentList[0] ?? null;



        // Check shopuser
        if($shopuser->is_giftcertificate != 1) {
            throw new \Exception("Shopuser is not a gift certificate");
        }

        if($shopuser->is_delivery != 1) {
            throw new \Exception("Shopuser is not a privatedelivery");
        }

        if($shopuser->delivery_state == 2 && $shipment == null) {
            throw new \Exception("Shopuser is already delivered");
        }

        // Check shipment
        if($shipment != null && $shipment->shipment_state != 1) {
            throw new \Exception("Shipment is already delivered");
        }

        // Remove shipment
        if($shipment != null) {

            // Find blocks
            $blockList = \BlockMessage::find_by_sql("select * from blockmessage where shipment_id = ".$shipment->id);
            foreach($blockList as $block) {
                $blockDel = \BlockMessage::find($block->id);
                $blockDel->delete();
            }

            // Remove shipment
            $shipment = \Shipment::find($shipment->id);
            $shipment->delete();

        }

        // Remove order attributes
        foreach($attributes as $attribute) {
            $delAttr = \OrderAttribute::find($attribute->id);
            $delAttr->delete();
        }

        // Remove order
        $order->delete();

        // Update shopuser: UPDATE shop_user set delivery_printed = 0, delivery_print_date = null, delivery_state = 0, navsync_date = null, navsync_status = 0  where is_giftcertificate = 1 and username LIKE '34517989';
        $shopuser->delivery_printed = 0;
        $shopuser->delivery_print_date = null;
        $shopuser->delivery_state = 0;
        $shopuser->navsync_date = null;
        $shopuser->navsync_status = 0;
        $shopuser->save();

        // Log action
        \ActionLog::logAction("OrderDeleted", "Gavevalg slettet på ".$shopuser->username, "Ordre ".$order->order_no." på varenr ".$order->present_model_present_no." slettet",0,$shopuser->shop_id,$shopuser->company_id,$shopuser->company_order_id,$shopuser->id,$order->id,$shipment->id ?? 0);

        // Commit
        \System::connection()->commit();

        // Output
        echo json_encode(array("status" => 1,"error" => "Order removed!"));


    }


    /**
     * PRIVATE HELPERS
     */

    private function getCardList()
    {

        $certificateList = array();

        // Get by certificatelist
        if(isset($_POST["certificatelist"]) && is_array($_POST["certificatelist"])) {
            foreach($_POST["certificatelist"] as $cert) {
                if(intval($cert) > 0) $certificateList[] = intval($cert);
            }
        }
        // Get by start and end certificate
        else {
            $fromCertificate = $_POST["startcertificate"];
            $endCertificate = $_POST["endcertificate"];
            for($i=$fromCertificate;$i<=$endCertificate;$i++) {
                if(intval($i) > 0) $certificateList[] = intval($i);
            }
        }

        return $certificateList;

    }


}
