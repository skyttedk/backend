<?php

namespace GFUnit\cardshop\orderform;
use GFBiz\Model\Cardshop\CardshopSettingsLogic;
use GFBiz\Model\Cardshop\DestroyOrder;
use GFBiz\units\UnitController;

class Controller extends UnitController
{

    public function __construct()
    {

        ini_set('max_execution_time', 600);
        ini_set('memory_limit','2048M');
        
        parent::__construct(__FILE__);
    }

    /**
     * SERVICES
     */

    public function get($companyorderid = 0) {

        if(intval($companyorderid) <= 0) {
            \response::error("No company order id provided");
            return;
        }

        $companyorder = \CompanyOrder::find(intval($companyorderid));
        $options = array('include' => array('company_order_items'));
        \response::success(make_json("result", $companyorder,$options));

    }
    public function getAll($companyid = 0) {

        $isCardshopAdmin = CardshopSettingsLogic::isCardshopAdmin();

        //$companyorders = \CompanyOrder::find_by_sql("SELECT *, IF(company_order.floating_expire_date IS NULL,company_order.expire_date,DATE(company_order.floating_expire_date)) as expire_date FROM company_order WHERE company_id = ".intval($companyid));
        $sql = "SELECT 
    company_order.*, CONCAT(company_order.shop_name, COALESCE(CONCAT(' ', company_order.card_values), '')) as shop_name,
    IF(company_order.floating_expire_date IS NULL, company_order.expire_date, DATE(company_order.floating_expire_date)) as expire_date,
    shipment.shipment_state, ".($isCardshopAdmin ? 1 : 0)." as is_admin
FROM 
    company_order
JOIN 
    shipment ON company_order.id = shipment.companyorder_id AND shipment.shipment_type = 'giftcard'
WHERE 
    company_order.company_id = ".intval($companyid)." GROUP BY company_order.id";
        $companyorders = \CompanyOrder::find_by_sql($sql);
        echo json_encode(array("status" => 1, "result" => $companyorders),JSON_PRETTY_PRINT);
    }


    public function getEarlyPresent(){
        $language = $_POST["language" ];
        $earlyPresents = \CompanyOrder::find_by_sql("SELECT * FROM early_present WHERE language = ".intval($language)." and active =1");
        echo json_encode(array("status" => 1, "result" => $earlyPresents));
    }
    public function earlyPresentExist(){
        $itemno= $_POST["itemno"];
        $earlyPresents = \CompanyOrder::find_by_sql("SELECT * FROM early_present WHERE active = 1 and item_nr = '".$itemno."'");
        echo json_encode(array("status" => 1, "result" => $earlyPresents));
    }


    /**
     * SALESPERSON
     */

    public function salespersons()
    {

        // Prepare
        $language = intval($_POST["language"]);
        $currentUserCode = \router::$systemUser == null ? "" : \router::$systemUser->salespersoncode;
        $salePersons = array();

        $systemUserList = \SystemUser::find("all",array("conditions" => array("active = 1 and salespersoncode != '' and language = '".$language."'")));
        $salePersons = array();

        foreach($systemUserList as $systemUser) {
            $salePersons[] = array("code" => $systemUser->salespersoncode, "name" => $systemUser->name,"email" => "");
        }

        // Get from nav
        /*
        $salesPersonService = new \GFCommon\Model\Navision\SalesPersonWS();
        $salespersonList = $salesPersonService->getAllSalesPerson();
        foreach($salespersonList as $salesPerson) {
            $salePersons[] = array("code" => $salesPerson->getCode(), "name" => $salesPerson->getName(),"email" => $salesPerson->getEmail());
        }
        */

        // Order salespersons by code
        usort($salePersons,function($a,$b) {
            return strcmp($a["name"],$b["name"]);
        });

        // Output
        echo json_encode(array("status" => 1,"current" => $currentUserCode,"salespersonlist" => $salePersons));

    }

    public function changesalesperson()
    {

        $salesperson = $_POST["salesperson"];
        $companyorderid = $_POST["companyorderid"];
        $comment = $_POST["comment"];
        
        if(trimgf($salesperson) == "") {
            echo json_encode(array("status" => 0,"message" => "Der er ikke angivet en sælger!"));
            return;
        }
        
        if(trimgf($comment) == "") {
            echo json_encode(array("status" => 0,"message" => "Der er ikke angivet en kommentar!"));
            return;
        }

        
        if(intvalgf($companyorderid) <= 0) {
            echo json_encode(array("status" => 0,"message" => "Der er ikke angivet et ordreid"));
            return;
        }

        $order = \CompanyOrder::find($companyorderid);
        
        if($order == null || $order->id == 0) {
            echo json_encode(array("status" => 0,"message" => "Kunne ikke finde ordren"));
            return;
        }
        
        if(trim(strtolower($order->salesperson)) == trim(strtolower($salesperson))) {
            echo json_encode(array("status" => 0,"message" => "Den aktive sælger er valgt!"));
            return;
        }
        
        if(!in_array($order->order_state, array(1,2,3,4,5,9))) {
            echo json_encode(array("status" => 0,"message" => "Denne ordre er ikke i en tilstand hvor der kan ændres sælger."));
            return;
        }

        $salespersonList = \NavisionSalesperson::find_by_sql("SELECT * FROM `navision_salesperson` where language_id in (select language_code from cardshop_settings where shop_id in (select shop_id from company_order where id = ".intval($companyorderid)."))");
        $found = false;

        foreach($salespersonList as $salespersonItem) {
            if($salespersonItem != null && is_string($salespersonItem->code)) {
                if(trim(strtolower($salespersonItem->code)) == trim(strtolower($salesperson))) {
                    $found = true;
                    break;
                }
            }
        }

        if($found == false) {
            echo json_encode(array("status" => 0,"message" => "Kunne ikke finde sælgeren!"));
            return;
        }

        $debugData = array("orderno" => $order->order_no,"salesperson" => $salesperson);
        \BlockMessage::createCompanyOrderBlock($order->company_id,$order->id,"COMPANYORDER_SALESPERSON_CHANGE",\router::$systemUser->name." anmoder om at ordre ".$order->order_no." får skiftet sælger fra ".$order->salesperson." til ".$salesperson.", kommentar: ".$comment,0,json_encode($debugData));
        echo json_encode(array("status" => 1));

        \system::connection()->commit();

    }

    /**
     * GET AVAILABLE SHOPS FOR A COUNTRY
     */
   /*
    public function createOrderErrorLog()
    {
       $note = $_POST["note"];
       $debug = $_POST["debug"];
       \Dbsqli::setSql2("INSERT INTO debug_log (note,debug) VALUES ('".$note."','".json_encode($_POST["shipmentdata"] )."')");
       echo json_encode(array());
    }
     */

    public function countryshops($languageCode)
    {

        if (intval($languageCode) == 0 || !\GFBiz\Model\Config\LanguageLogic::validLanguage(intval($languageCode))) {
            \response::error("Invalid language code");
            return;
        }

        // Find shops
        $shopList = \Shop::find_by_sql("SELECT * FROM shop WHERE id IN (SELECT shop_id FROM cardshop_settings WHERE is_hidden = 0 and language_code = ".intval($languageCode).") order by name asc");

        if(count($shopList) == 0) {
            \response::error("Country code has no shops");
            return;
        }

        $responseList = array();
        foreach($shopList as $shop) {
            $responseList[] = array("shop_id" => $shop->id,"language_code" => $languageCode,"name" => $shop->name,"alias" => $shop->alias,"value" => $shop->card_value);
        }

        echo json_encode(array("status" => 1, "result" => $responseList),JSON_PRETTY_PRINT);

    }

    /**
     * GET AVAILABLE SHOPS FOR A SPECIFIC COMPANY
     */
    public function companyshops($companyid) {
        $company = \Company::find($companyid);
        $this->countryshops($company->language_code);
    }

    /**
     * GET ALL WEEKS AVAILABLE TO A SHOP
     */
    public function shopweeks($shopid)
    {

        $settings = new \GFBiz\Model\Cardshop\CardshopSettingsLogic($shopid);
        $weeks = $settings->getWeeks();
        $response = array();

        foreach($weeks as $week) {
            $response[] = $week->toJsonArray();
        }

        echo json_encode(array("status" => 1, "result" => $response),JSON_PRETTY_PRINT);
    }

    /**
     * GET WEEKS A SHOP CAN SELL CARDS TO
     */
    public function shopsalesweeks($shopid)
    {

        $settings = new \GFBiz\Model\Cardshop\CardshopSettingsLogic($shopid);
        $weeks = $settings->getWeeks();
        $response = array();

        foreach($weeks as $week) {
            if($week->isSaleOpen()) {
                $response[] = $week->toJsonArray();
            }
        }

        echo json_encode(array("status" => 1, "result" => $response),JSON_PRETTY_PRINT);

    }

    /**
     * GET PRODUCTS AND PRICES ON SHOP
     */

    public function shopproducts($shopid)
    {
        $settings = new \GFBiz\Model\Cardshop\CardshopSettingsLogic($shopid);
        $products = $settings->getProducts();
        $response = array();

        foreach($products as $prodline) {
            $response[] = $prodline->toJsonArray();
        }

        $extraData = array(
            "dot_use" => $settings->getSettings()->dot_use,
            "dot_price" => $settings->getSettings()->dot_price,
            "carryup_use" => $settings->getSettings()->carryup_use,
            "carryup_price" => $settings->getSettings()->carryup_price,
            "card_values" => $settings->getSettings()->card_values
        );
        
        echo json_encode(array("status" => 1, "result" => $response,"extra" => $extraData),JSON_PRETTY_PRINT);

    }

    /**
     * CREATE A NEW ORDER
     */
    public function create()
    {
        // Check company data
        if(!isset($_POST["orderdata"])) {
            \response::error("No order data provided");
            return;
        }

        // Create company
        $order = \GFBiz\Model\Cardshop\CompanyOrderLogic::createOrder($_POST["orderdata"]);
        \response::success(make_json("result", $order));

    }


    /**
     * ON CREATE ERROR, MAKE BLOCK
     */

    public function createerror()
    {

        if($_SERVER['REQUEST_METHOD'] !== 'POST') {
            \response::error("Request needs to be post");
            return;
        }


        // Check company id
        $company_order_id = isset($_POST["id"]) ? intval($_POST["id"]) : 0;
        if(intval($company_order_id) <= 0) {
            \response::error("No company order id provided");
            return;
        }

        // Find company order
        $companyOrder = \CompanyOrder::find($company_order_id);
        if($companyOrder == null || $companyOrder->id == 0) {
            \response::error("No company order id provided");
            return;
        }

        // Get details
        $description = trimgf($_POST["description"]);
        $debugData = isset($_POST["debugdata"]) ? trimgf($_POST["debugdata"]) : "";

        \BlockMessage::createCompanyOrderBlock($companyOrder->company_id,$companyOrder->id,"COMPANYORDER_FRONTEND_ERROR",$description,true,$debugData);

        \system::connection()->commit();
        echo json_encode(array("status" => 1));

    }


    /**
     * UPDATE EXISTING ORDER
     */
    public function update($companyorderid=0)
    {

        // Check company data
        if(!isset($_POST["orderdata"])) {
            \response::error("No order data provided");
            return;
        }

        if(intval($companyorderid) <= 0) {
            \response::error("No order id provided");
            return;
        }

        // Create company
        $companyorder = \GFBiz\Model\Cardshop\CompanyOrderLogic::updateOrder($companyorderid,$_POST["orderdata"]);
        \response::success(make_json("result", $companyorder));

    }

    public function moveexpiredate($companyorderid)
    {

        // Check company data
        if(!isset($_POST["expire_date"])) {
            \response::error("No expire date provided");
            return;
        }

        // Create company
        $companyorder = \GFBiz\Model\Cardshop\CompanyOrderLogic::moveExpireDate($companyorderid,$_POST["expire_date"]);
        \response::success(make_json("result", $companyorder));

    }

    public function navready($companyorderid)
    {

        $this->requirePost();

        // Create company
        $companyorder = \CompanyOrder::find($companyorderid);
        if($companyorder->order_state != 0) {
            throw new \Exception("Order was not waiting for nav ready");
        }

        $companyorder->nav_synced = 0;
        $companyorder->order_state = 1;
        $companyorder->save();
        \response::success(make_json("result", $companyorder));

    }

    public function destroyorder()
    {

        $companyorderid = intval($_POST["companyorderid"]);
        $companyorder = DestroyOrder::destroyOrder($companyorderid,false,false);

        // Output success
        \response::success(make_json("result", $companyorder));

    }
    
    public function reviveorder() {

        // TODO!!

        //$companyorderid = intval($_POST["companyorderid"]);
        //$companyorder = DestroyOrder::destroyOrder($companyorderid,false,false,true);

        // Output success
        //\response::success(make_json("result", $companyorder));

    }

    public function navhold($companyorderid=0,$onhold=0)
    {

        $this->requirePost();

        // Create company
        $companyorder = \CompanyOrder::find($companyorderid);
        $onhold = intval($onhold) == 1 ? 1 : 0;

        if($companyorder->nav_on_hold == $onhold) {
            throw new \Exception("No change in hold status");
        }

        $validStates = array(0,1,2,3,4,5,6);
        if(!in_array($companyorder->order_state,$validStates)) {
            throw new \Exception("Order cant be updated in the state: ".$companyorder->getStateText());
        }

        // Update
        $companyorder->nav_synced = 0;
        $companyorder->nav_on_hold = $onhold;
        $companyorder->save();

        \response::success(make_json("result", $companyorder));

    }

    /**
     * UPDATE ALL COMPANY ORDER ITEMS
     */

    public function updateitems($companyorderid=0) {

        // Check item data
        if(!isset($_POST["orderitemlist"])) {
            \response::error("No order item data provided");
            return;
        }

        if(!is_array($_POST["orderitemlist"])) {
            \response::error("Orderitemlist data is not a list");
            return;
        }

        if(intval($companyorderid) <= 0) {
            \response::error("No order id provided");
            return;
        }
        
        $companyorderitem = \GFBiz\Model\Cardshop\CompanyOrderLogic::updateOrderItems($companyorderid,$_POST["orderitemlist"]);
        \response::success(make_json("result", $companyorderitem));
        
    }

    /**
     * UPDATE A SPECIFIC COMPANY ORDER ITEM
     */

    public function updateitem($companyorderid=0)
    {
        // Check item data
        if(!isset($_POST["orderitemdata"])) {
            \response::error("No order item data provided");
            return;
        }

        if(intval($companyorderid) <= 0) {
            \response::error("No order id provided");
            return;
        }

        // Create company
        $companyorderitem = \GFBiz\Model\Cardshop\CompanyOrderLogic::updateOrderItem($companyorderid,$_POST["orderitemdata"]);
        \response::success(make_json("result", $companyorderitem));
    }

    /**
     * GET ORDER ITEMS
     */
    
    public function getitems($companyorderid=0)
    {

        // Check item data
        if(intval($companyorderid) <= 0) {
            \response::error("No companyorderid provided");
            return;
        }

        $orderItems = \CompanyOrderItem::find('all',array("conditions" => array("companyorder_id" => intval($companyorderid))));
        \response::success(make_json("result", $orderItems));

    }

    public function getitemsandmeta($companyorderid=0) {

        // Check item data
        if(intval($companyorderid) <= 0) {
            \response::error("No companyorderid provided");
            return;
        }

        // Company order
        $companyorder = \CompanyOrder::find(intval($companyorderid));
        $itemmetaList = array();
        $itemmetaMap = array();

        // Load shop products
        ob_start();
        $this->shopproducts($companyorder->shop_id);
        $shopProductsJSON = ob_get_contents();
        ob_end_clean();

        $shopProductsData = json_decode($shopProductsJSON,true);
        $shopProductsList = $shopProductsData["result"];
        foreach($shopProductsList as $product) {
            $itemmetaMap[$product["code"]] = array("code" => $product["code"],"meta" => $product,"item" => null);
        }

        // Get order items
        $orderItems = \CompanyOrderItem::find('all',array("conditions" => array("companyorder_id" => intval($companyorderid))));
        foreach($orderItems as $item) {
            if(isset($itemmetaMap[$item->type])) {
                $data = make_json("result", $item);
                $array = json_decode($data,true);
                $itemmetaMap[$item->type]["item"] = $array["result"];
            }
        }

        foreach($itemmetaMap as $item) {
            $itemmetaList[] = $item;
        }

        // Load expire date
        $homeDelivery = false;
        $expiredate = \ExpireDate::find_by_sql("SELECT expire_date.* FROM expire_date, company_order, cardshop_expiredate where company_order.id = ".$companyorderid." and expire_date.is_delivery = 1 and company_order.expire_date = expire_date.expire_date && company_order.shop_id = cardshop_expiredate.shop_id && cardshop_expiredate.expire_date_id = expire_date.id");
        if(countgf($expiredate) > 0) {
            $homeDelivery = true;
        }

        echo json_encode(array("status" => 1, "result" => $itemmetaList,"homedelivery" => $homeDelivery),JSON_PRETTY_PRINT);

    }

    public function forcesync($companyorderid=0)
    {
        $this->requirePost();

        // Create company
        $companyorder = \CompanyOrder::find($companyorderid);

        if($companyorder->force_syncnow == 1) {
            throw new \Exception("Already forces sync");
        }

        $validStates = array(0,1,2,3);
        if(!in_array($companyorder->order_state,$validStates)) {
            throw new \Exception("Order cant be updated in the state: ".$companyorder->getStateText());
        }

        // Update
        $companyorder->force_syncnow = 1;
        $companyorder->nav_synced = 0;
        $companyorder->save();

        \response::success(make_json("result", $companyorder));
    }

    public function forceorderconfirmation($companyorderid=0)
    {
        $this->requirePost();

        // Create company
        $companyorder = \CompanyOrder::find($companyorderid);

        $validStates = array(0,1,2,3,4,5,6,9,10);
        if(!in_array($companyorder->order_state,$validStates)) {
            throw new \Exception("Order cant be updated in the state: ".$companyorder->getStateText());
        }

        // Update
        $companyorder->force_orderconf = 1;
        $companyorder->save();

        \response::success(make_json("result", $companyorder));
    }

    /**
     * TEST FUNCTIONS
     * These functions are used to easily test functions and to see examples on the input.
     */


    // Test function to test creation
    public function testcreate()
    {

        $_POST["orderdata"] = array(
            "shop_id" => 52,
            "expire_date" => "2021-10-31",
            "quantity" => 5,
            "is_email" => 1,
            "company_id" => 37551
        );

        $this->create();

    }

    // Test function to test update
    public function testupdate()
    {

        $companyorderid = 38289;

        $_POST["orderdata"] = array(

            "expire_date" => "2021-10-31",
            "free_cards" => 2

        );

        $this->update($companyorderid);

    }

    // Test function to test update
    public function testmovecards()
    {
        $companyorderid = 38289;
        $_POST["expire_date"] = "2021-12-31";
        $this->moveexpiredate($companyorderid);
    }

    // Test function to test update orderitem
    public function testorderitem()
    {
        $companyorderid = 38289;
        $_POST["orderitemdata"] = array(
            "type" => "DOT",
            "isdefault" => 0,
            "price" => 56100,
            "quantity" => 1
        );
        $this->updateitem($companyorderid);
    }

    // Test function to test update orderitem
    public function testorderitems()
    {
        $companyorderid = 38289;
        $_POST["orderitemlist"] = array(
            array(
                "type" => "INVOICEFEEINITIAL",
                "isdefault" => 0,
                "price" => 7900,
                "quantity" => 2
            ),array(
                "type" => "CARRYUP",
                "isdefault" => 0,
                "price" => 487,
                "quantity" => 1
            )
        );
        $this->updateitems($companyorderid);
    }

}