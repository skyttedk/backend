<?php

namespace GFUnit\cardshop\companyform;
use GFBiz\units\UnitController;
use GFCommon\Model\Navision\CustomerWS;
use GFCommon\Model\Navision\NavClient;
use GFCommon\Model\Navision\PaymentTermsWS;

class Controller extends UnitController
{

    public function __construct()
    {
        parent::__construct(__FILE__);
    }

    /**
     * SERVICES
     */

    public function get($companyid = 0) {

        if(intval($companyid) <= 0) {
            \response::error("No company id provided");
            return;
        }

        $company = \Company::find(intval($companyid));
        \response::success(make_json("result", $company));

    }

    public function shutdown(){
      $companyID = $_POST["companyID"];
      $settings= $_POST["settings"];
      $company = \Company::find(intval($companyID));
      $company->shutdown = $settings;
      $company->save();
      $result = \Dbsqli::setSql2("update shop_user set shutdown = ".$settings." where company_id=".$companyID);


      // Output company
      \response::success(make_json("result", $company));


    }

    public function manblock($companyid) {

        $company = \Company::find(intval($companyid));

        if($company == null || $company->id == 0) {
            throw new \Exception("Could not find company");
        }

        if(!isset($_POST["msg"]) || trim($_POST["msg"]) == "") {
            throw new \Exception("No block note, please provide a message");
        }

        // Add action log
        \ActionLog::logAction("company-navblock", "Der er oprettet en manuel navision blokkering af kunden ".$company->name. " (cvr: ".$company->cvr.")","Besked fra bruger: ".$_POST["msg"],0,0,$company->id,0,0,0,0);

        \BlockMessage::createCompanyBlock($company->id,"COMPANY_MANUAL_BLOCK",$_POST["msg"],false,"");
        \response::success(make_json("result", $company));

    }

    public function invoicedata($companyid = 0)
    {

        // Check company id
        if(intval($companyid) <= 0) {
            \response::error("No company id provided");
            return;
        }

        // Load company
        $company = \Company::find(intval($companyid));
        if($company->nav_customer_no == 0) {
            \response::error("Customer not synced to navision yet.");
            return;
        }

        // Create customer webservice
        try {
            $customerListWS = new \GFCommon\Model\Navision\CustomerWS($company->language_code);
            $customerObj = $customerListWS->getByCustomerNo($company->nav_customer_no);

            if($customerObj == null) {
                throw new \Exception("Customer no ".$company->nav_customer_no." not found");
            }

            // Update company
            $company->excempt_invoicefee = $customerObj->isExcemptFromInvoiceFee() ? 1 : 0;
            $company->excempt_envfee = $customerObj->isExcemptFromEnvFee() ? 1 : 0;
            $company->cvr = $customerObj->getCVR();
            $company->ean = $customerObj->getEAN();
            $company->name = $customerObj->getName();
            $company->bill_to_address = $customerObj->getAddress();
            $company->bill_to_address_2 = $customerObj->getAddress2();
            $company->bill_to_postal_code = $customerObj->getPostCode();
            $company->bill_to_city = $customerObj->getCity();
            $company->bill_to_country = $customerObj->getCountryCode();
            $company->save();


            \system::connection()->commit();

            echo json_encode(array("status" => 1, "data" => array("result" => $customerObj->frontendMapper())));
            
        } catch (\Exception $e) {
            \response::error("Could not get data from navision: ".$e->getMessage());
            return;
        }

    }

    public function updateinvoicedata($companyid)
    {


        if($_SERVER['REQUEST_METHOD'] !== 'POST') {
            \response::error("Request needs to be post");
            return;
        }

        // Check company id
        if(intval($companyid) <= 0) {
            \response::error("No company id provided");
            return;
        }

        // Load company
        $company = \Company::find(intval($companyid));
        if($company->nav_customer_no == 0) {
            \response::error("Customer not synced to navision yet.");
            return;
        }

        // Check invoice details is set
        if(!isset($_POST["invoicedetails"]) || trimgf($_POST["invoicedetails"]) == "") {
            \response::error("No invoice details set.");
            return;
        }

        // Get details
        $invoiceData = trimgf($_POST["invoicedetails"]);
        $debugData = isset($_POST["debugdata"]) ? trimgf($_POST["debugdata"]) : "";

        \BlockMessage::createCompanyBlock($company->id,"COMPANY_UPDATE_INVOICEDATA",$invoiceData,false,$debugData);

        // Add action log
        \ActionLog::logAction("company", "Anmodning om faktura data opdatering: ".$company->name. " (cvr: ".$company->cvr.")",urldecode($invoiceData),0,0,$company->id,0,0,0,0);

        \system::connection()->commit();
        echo json_encode(array("status" => 1));

    }

    public function syncinvoicedata($companyid = 0)
    {

        if($_SERVER['REQUEST_METHOD'] !== 'POST') {
            \response::error("Request needs to be post");
            return;
        }

        // Check company id
        if(intval($companyid) <= 0) {
            \response::error("No company id provided");
            return;
        }

        // Load company
        $company = \Company::find(intval($companyid));
        if($company->nav_customer_no == 0) {
            \response::error("Customer not synced to navision yet.");
            return;
        }

        // Create customer webservice
        try {

            // Load navision data
            $customerListWS = new \GFCommon\Model\Navision\CustomerWS();
            $customerObj = $customerListWS->getByCustomerNo($company->nav_customer_no);

        } catch (\Exception $e) {
            \response::error("Could not get data from navision: ".$e->getMessage());
            return;
        }

        // Update company
        $company->bill_to_address = $customerObj->getAddress();
        $company->bill_to_postal_code = $customerObj->getPostCode();
        $company->bill_to_city = $customerObj->getCity();
        $company->bill_to_country = $customerObj->getCountryCode();
        $company->save();

        // Output company
        \response::success(make_json("result", $company));

    }

    public function create()
    {
        // Check company data
        if(!isset($_POST["companydata"])) {
            \response::error("No company data provided");
            return;
        }

        // Create company
        $company = \GFBiz\Model\Cardshop\CompanyLogic::createCompany($_POST["companydata"]);
        \response::success(make_json("result", $company));

    }

    public function update($companyid=0)
    {

        // Check company data
        if(!isset($_POST["companydata"])) {
            \response::error("No company data provided");
            return;
        }

        if(intval($companyid) <= 0) {
            \response::error("No company id provided");
            return;
        }

        // Create company
        $company = \GFBiz\Model\Cardshop\CompanyLogic::updateCompany($companyid,$_POST["companydata"]);
        \response::success(make_json("result", $company));

    }

    /*
     * SHIPPING DEAL
     */

    public function getshippingprice($companyid=0){

        $companyID =  intval($companyid);
        $result = \Dbsqli::getSql2("select * from company_shipping_cost where company_id=".$companyID);
        $response = array("status" => 1, "has_deal" => false, "cost" => 0,"handle_manual" => 0);

        try {
            $company = \Company::find($companyid);
            if($company->manual_freight == 1) {
                $response["handle_manual"] = 1;
            }
        } catch (\Exception $e) {
            $response["handle_manual"] = 0;
        }

        if(sizeofgf($result) == 0){
            $response["has_deal"] = false;
        } else {
            if($result[0]["cost"] <= -1){
                $response["has_deal"] = false;
            } else {
                $response["has_deal"] = true;
                $response["cost"] = $result[0]["cost"];
            }
        }

        echo json_encode($response);

    }

    public function updateshippingprice($companyid=0){

        $companyID = intval($companyid);

        // Check company data
        if(!isset($_POST["cost"])) {
            \response::error("No cost data provided");
            return;
        }


        $cost = $_POST["cost"];
        if($cost == ""){
            $cost = -1;
        }

        $result = \Dbsqli::getSql2("select * from company_shipping_cost where company_id=".$companyID);
        if(sizeofgf($result) == 0){
            $sql = "insert into company_shipping_cost (company_id,cost) value (".$companyID.",".$cost." )";
            \Dbsqli::setSql2($sql);
        } else {
            $sql ="update company_shipping_cost set cost = ".$cost." where company_id =". $companyID;
            \Dbsqli::setSql2($sql);
        }

        $logText = "";
        if($cost == -1) {
            $logText = "Fragtprisaftale deaktiveret";
        } else if($cost == 0) {
            $logText = "Fragtprisaftale sat til 0 (gratis fragt)";
        } else {
            $logText = "Fragtprisaftale sat til ".$cost;
        }

        $company = \Company::find($companyID);
        \ActionLog::logAction("CompanyShippingPrice", "Fragtprisaftale opdateret for ".$company->name.": ".$logText,"",0,0,$company->id,0,0,0,0);


        $this->getshippingprice($companyid);
        \System::connection()->commit();

    }

    public function updateshippingmanhandle($companyid=0){

        $companyID = intval($companyid);

        // Check company data
        if(!isset($_POST["manualhandle"])) {
            \response::error("No cost data provided");
            return;
        }

        $manualHandle = intvalgf($_POST["manualhandle"]);
        if($manualHandle != 0 && $manualHandle != 1) {
            \response::error("Unknown manual handle value");
            return;
        }

        $sql ="update company set manual_freight = ".$manualHandle." where id =". $companyID;
        \Dbsqli::setSql2($sql);

        $company = \Company::find($companyid);
        \ActionLog::logAction("CompanyShippingManual", ($manualHandle == 1 ? "Fragt markeret til manuel beregning for" : "Markering for manuel beregning af fragt fjernet for")." ".$company->name,"",0,0,$company->id,0,0,0,0);


        $this->getshippingprice($companyid);
        \System::connection()->commit();
    }

    public function getpaymentterms($language_code = null) {

        $language_code = intval($language_code);
        if(!in_array($language_code,array(1,4,5))) {
            throw new \Exception("Invalid language code");
        }

        try {

            $client = new PaymentTermsWS($language_code);
            $items = $client->getAllItems();

            $returnList = array(array("code" => "","description" => "Standard betingelser"));
            foreach($items as $item) {
                $returnList[] = array("code" => $item->getCode(),"description" => $item->getDescription());
            }

            echo json_encode(array("status" => 1, "data" => $returnList));

        } catch(\Exception $e) {
            throw new \Exception("Error fetching payment terms from navision");
        }

    }

    public function changenavno() {

        $company = \Company::find($_POST["companyID"]);
        $navisionNo = intvalgf($_POST["navno"]);

        if($navisionNo <= 0) {
            echo json_encode(array("status" => 0, "message" => "Der er ikke angivet et kundenr."));
            return;
        }

        $languageId = 0;
        $canChange = true;

        $companyOrderList = \CompanyOrder::find_by_sql("select * from company_order where company_id = ".$company->id);
        foreach($companyOrderList as $companyOrder) {
            $order = \CompanyOrder::find($companyOrder->id);
            $settings = \CardshopSettings::find_by_sql("select * from cardshop_settings where shop_id = ".$order->shop_id);
            if(count($settings) == 0) {
                echo json_encode(array("status" => 0, "message" => "Ingen cardshop_settings på shop: ".$order->shop_id));
                return;
            }

            $languageId = $settings[0]->language_code;
            if(in_array($order->order_state, array(4,5,6,7,8,9,10))) {
                $canChange = false;
                break;
            }
        }

        if($canChange == false) {
            echo json_encode(array("status" => 0, "message" => "Der er synkroniserede ordre på denne kunde, kan ikke skifte kundenr."));
            return;
        }

        if($languageId == 0) {
            $languageId = \router::$systemUser->language;
        }

        if($languageId == 0) {
            echo json_encode(array("status" => 0, "message" => "Kan ikke finde kunde land."));
            return;
        }

        // Check on nav
        try {

            $customerClient = new CustomerWS($languageId);
            $customer = $customerClient->getByCustomerNo($navisionNo);

        } catch (\Exception $e) {

        }

        if($customer == null) {
            echo json_encode(array("status" => 0, "message" => "Kan ikke finde kundenr i navision."));
            return;
        }

        $debugData = array("companyid" => $company->id,"navno" => $navisionNo);
        \BlockMessage::createCompanyBlock($company->id,"COMPANY_CUSTOMERNO_CHANGE",\router::$systemUser->name." anmoder om ændring af navision kundenr fra ".$company->nav_customer_no." til ".$navisionNo.": ".$customer->getName()." (cvr: ".$customer->getCVR().").",true,json_encode($debugData));
        echo json_encode(array("status" => 1,"message" => "Anmodning om kundenr skift er gemt, det bliver først opdateret når det godkendes."));

        \system::connection()->commit();

    }
    
}