<?php

namespace GFUnit\valgshopdev\main;
use GFBiz\units\UnitController;

class Controller extends UnitController
{

    public function __construct()
    {
        parent::__construct(__FILE__);
    }
    
    public function testservice() {
        echo "I am a test service!";
    }
    public function getSalespersonCodeSold()
    {
        if (!isset($_GET["lang"]) || !(int)$_GET["lang"]) {
            echo json_encode([
                "status" => 0,
                "error" => "Ugyldig eller manglende sprog-ID"
            ]);
            exit;
        }
        $language_id = (int)$_GET["lang"];
        $language_id = ($language_id === 5) ? 1 : $language_id;
        $ShopMetadata =  \SystemUser::find_by_sql("SELECT salespersoncode FROM `system_user`  WHERE  `salespersoncode` != '' and language = ".$language_id." order by salespersoncode");
        echo json_encode(array("status" => 1,"data"=>$ShopMetadata));
    }

    public function paymenttermslist() {

    }

    /**
     * SERVICES
     */

    public function overbookedEndDate($date){
 /*       $sql = "WITH dates_with_high_count AS (
    SELECT DATE(end_date) AS date
    FROM shop
    WHERE end_date IS NOT NULL AND end_date > '2024-04-01 14:12:30'
    GROUP BY DATE(end_date)
    HAVING COUNT(*) > 40
    )

        SELECT id as shop_id
        FROM (
    SELECT s.*, 
           ROW_NUMBER() OVER (PARTITION BY DATE(s.end_date) ORDER BY s.modified_datetime DESC) AS row_num
    FROM shop s
    INNER JOIN dates_with_high_count dhc ON DATE(s.end_date) = dhc.date
   
        ) AS subquery
    WHERE row_num > 40";
*/
        $sql = "SELECT DATE(end_date) AS date, COUNT(*) AS count_per_day FROM shop WHERE end_date IS NOT NULL AND start_date = '".$date."' GROUP BY date  having count_per_day > 40  ORDER BY date";
        $shops = \Shop::find_by_sql($sql);
        return $shops;
    }
    private function convertDateFormat($date) {

        if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $date)) {
            list($day, $month, $year) = explode('-', $date);
            return "$year-$month-$day";
        }
        return $date;
    }

    public function overbookedStartDate($date){
        $sql = "SELECT DATE(start_date) AS date, COUNT(*) AS count_per_day FROM shop WHERE start_date IS NOT NULL AND start_date = '".$date."' GROUP BY date having count_per_day > 40  ORDER BY date";
        $shops = \Shop::find_by_sql($sql);
        return $shops;
    }

    public function overbookedDeliveryDate($date){
        $sql = "SELECT DATE(delivery_date) AS date, COUNT(*) AS count_per_day FROM shop_metadata WHERE delivery_date IS NOT NULL AND delivery_date = '".$date."' GROUP BY date  having count_per_day > 40  ORDER BY date";
        $shops = \Shop::find_by_sql($sql);
        return $shops;
    }
    public function overbooked(){
        if (!isset($_GET["lang"]) || !(int)$_GET["lang"]) {
            echo json_encode([
                "status" => 0,
                "error" => "Ugyldig eller manglende sprog-ID"
            ]);
            exit;
        }
        $language_id = (int)$_GET["lang"];
        $language_id = ($language_id === 5) ? 1 : $language_id;
        if($language_id != 1){

            return;
        }


        $token = "sdafoijhiousadfy8a9asudkhasdf89asdf89DSAFAFSFAD!!fadsfklas";




        $shop_id = $_POST["shop_id"];
        $mailTxt = "";
        $startDate = isset($_POST["orderOpenCloseChopStartDate"]) ? $this->overbookedStartDate($this->convertDateFormat($_POST["orderOpenCloseChopStartDate"])) : null;
        if (isset($startDate) && $startDate !== null && sizeof($startDate)) {
            if(!$this->isApprov($shop_id,"shop_start")) {
                $mailTxt .= "<tr><td>Shop åbne</td><td>Dato: " . $_POST["orderOpenCloseChopStartDate"] . "</td><td>Antal valgte:" . $startDate[0]->attributes["count_per_day"] . "</td><td>
                <a mc:disable-tracking href='https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=overbookedApproval/approv&token=" . $token . "&shopid=" . $shop_id . "&action=shop_start'>Godkend</a></td>
               <td> <a mc:disable-tracking href='https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=overbookedApproval/noApprov&token=" . $token . "&shopid=" . $shop_id . "&action=shop_start&sa=" . $_POST["salesperson_code"] . "'>Afvis (mail sendes)</a></td>
                </tr>";
                $this->notApprov($shop_id, "shop_start");
            }
        }

        $endDate = isset($_POST["orderOpenCloseChopEndDate"]) ? $this->overbookedEndDate($this->convertDateFormat($_POST["orderOpenCloseChopEndDate"])) : null;
        if (isset($endDate) && $endDate !== null && sizeof($endDate)) {
            if(!$this->isApprov($shop_id,"shop_end")) {
                $mailTxt .= "<tr><td>Shop luk</td><td>Dato: " . $_POST["orderOpenCloseChopEndDate"] . "</td><td>Antal valgte:" . $endDate[0]->attributes["count_per_day"] . "</td><td>
                 <a mc:disable-tracking href='https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=overbookedApproval/approv&token=" . $token . "&shopid=" . $shop_id . "&action=shop_end'>Godkend</a></td>
               <td>  <a mc:disable-tracking href='https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=overbookedApproval/noApprov&token=" . $token . "&shopid=" . $shop_id . "&action=shop_end&sa=" . $_POST["salesperson_code"] . "'>Afvis (mail sendes)</a></td>
                 </tr>";
                $this->notApprov($shop_id, "shop_end");
            }
        }

        $deliveryDate = isset($_POST["delivery_date"]) ? $this->overbookedDeliveryDate($this->convertDateFormat($_POST["delivery_date"])) : null;
        if (isset($deliveryDate) && $deliveryDate !== null && sizeof($deliveryDate)) {
            if(!$this->isApprov($shop_id,"shop_delivery")){
                $mailTxt.= "<tr><td>Levering</td><td>Dato: ".$_POST["delivery_date"]."</td><td>Antal valgte:".$deliveryDate[0]->attributes["count_per_day"]."</td><td>
                <a mc:disable-tracking href='https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=overbookedApproval/approv&token=".$token."&shopid=".$shop_id."&action=shop_delivery'>Godkend</a></td>
               <td> <a mc:disable-tracking href='https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=overbookedApproval/noApprov&token=".$token."&shopid=".$shop_id."&action=shop_delivery&sa=".$_POST["salesperson_code"]."'>Afvis (mail sendes)</a></td>
                </tr>";
                $this->notApprov($shop_id,"shop_delivery");
            }

        }
        
        if($mailTxt != ""){
            $header = "<table cellspacing='10' width='400' border='1'><tr><td>Valgshop </td><td><b>".$_POST["name"]."</b></td></tr><tr><td>Sælger</td><td><b>".$_POST["salesperson_code"]."</b></td></tr><tr><td>Antal gaver</td><td>".$_POST["user_count"]."</td></tr></table>";
            $mailTxt = "<div>Følgende er overbooket:</div><br>".$header."<br><table cellspacing='10' width='600' border='1'>".$mailTxt."</table>";
          
            $this->sendApprovalMail($mailTxt);
        }
        echo json_encode([
            "status" => 1
        ]);
    }

    public function isApprov($shopId,$action){
        $shopApproval = \ShopApproval::find('first', [
            'conditions' => ['shop_id = ?', $shopId]
        ]);
        try {
            return $shopApproval->attributes[$action] == 3 ? true:false;
        } catch (Exception $e) {
            return false;

        }

    }

    public function notApprov($shopId,$action)
    {
        $shopApproval = \ShopApproval::find('first', [
            'conditions' => ['shop_id = ?', $shopId]
        ]);

        if ($shopApproval) {
            $shopApproval->$action = 0;
            $rs = $shopApproval->save();


        }
    }
    public function sendApprovalMail($mailTxt){
    $mailqueue = new \MailQueue();
        $mailqueue->sender_name  = 'Gavefabrikken';
        $mailqueue->sender_email = 'Gavefabrikken@gavefabrikken.dk';
        $mailqueue->recipent_email = 'sg@gavefabrikken.dk';
        $mailqueue->subject ='Ordre dato overbooket';
        $mailqueue->body =$mailTxt;
        $mail = $mailqueue->save();
        \system::connection()->commit();
        echo json_encode(array("status" => 1,"data"=>$mail));
    }

    public function loadPrivateReturVirksomhedAdress()
    {
       $shopID = $_GET["shop_id"];
    //    $rs =  \ShopCompanyReturnAdress::find_by_sql("select * from shop_company_return_adress where shop_d");
        $rs = \ShopCompanyReturnAdress::find('first', ['conditions' => ['shop_id' => $shopID]]);

        echo json_encode(array("status" => 1,"data"=>$rs));
    }
    public function saveUpdatePrivateReturVirksomhedAdress()
    {
        $shopID = $_POST["shop_id"];
        $data = $_POST;
        $address = \ShopCompanyReturnAdress::find('first', ['conditions' => ['shop_id' => $shopID]]);

        if (!$address) {
            $address = new \ShopCompanyReturnAdress();
        }

            $address->shop_id = $shopID;
            $address->address = $data['street'];
            $address->address2 = $data['street2'];
            $address->postal_code = $data['postalCode'];
            $address->city = $data['city'];
            $address->contact_name = $data['contactPerson'];
            $address->contact_phone = $data['contactPhone'];
            $address->contact_email = $data['contactEmail'];
            $address->country = $data['country'];


            $rs = $address->save();
        \system::connection()->commit();
            echo json_encode(array("status" => 1,"data"=>$rs));
    }


    public function orderOpenCloseChopEvents(){
        // localisation
        if (!isset($_GET["lang"]) || !(int)$_GET["lang"]) {
            echo json_encode([
                "status" => 0,
                "error" => "Ugyldig eller manglende sprog-ID"
            ]);
            exit;
        }
        $language_id = (int)$_GET["lang"];
        $language_id = ($language_id === 5) ? 1 : $language_id;
    


        $openSql = "SELECT  DATE(start_date) AS date, COUNT(*) AS count_per_day FROM shop WHERE localisation = ".$language_id." and start_date IS NOT NULL and `start_date` > '2024-04-01 14:12:30' GROUP BY DATE(start_date)";
        $openRS = \Shop::find_by_sql($openSql);
        $closeSql = "SELECT  DATE(end_date) AS date, COUNT(*) AS count_per_day FROM shop WHERE localisation = ".$language_id." and end_date IS NOT NULL and `end_date` > '2024-04-01 14:12:30' GROUP BY DATE(end_date)";
        $closeRS = \Shop::find_by_sql($closeSql);
        $return = array("start"=>$openRS,"end"=>$closeRS);
        echo json_encode(array("status" => 1,"data"=>$return));
    }
    public function getDeliveryDates()
    {
        // localisation
        if (!isset($_GET["lang"]) || !(int)$_GET["lang"]) {
            echo json_encode([
                "status" => 0,
                "error" => "Ugyldig eller manglende sprog-ID"
            ]);
            exit;
        }
        $language_id = (int)$_GET["lang"];
        $language_id = ($language_id === 5) ? 1 : $language_id;
        $sql ="SELECT delivery_date AS date, COUNT(*) as count_per_day FROM shop_metadata
                inner join shop on shop.id = shop_metadata.shop_id                                     
                where localisation = ".$language_id." and delivery_date IS NOT NULL GROUP BY delivery_date";
        $rs = \ShopMetadata::find_by_sql($sql);
        echo json_encode(array("status" => 1,"data"=>$rs));
    }
    public function saveShopMetadata($shopid=0)
    {
      //    print_r($_POST);

        try {

            unset($_POST["foreign_norge_date"]);
            unset($_POST["foreign_sverige_date"]);
            unset($_POST["foreign_tyskland_date"]);
            unset($_POST["foreign_england_date"]);
            unset($_POST["foreign_eu_date"]);
            unset($_POST["foreign_amerika_date"]);
            unset($_POST["foreign_andre_date"]);
            


        // Load shop
        $shop = \Shop::find(intval($shopid));
        $shop->start_date = $_POST["orderOpenCloseChopStartDate"];
        $shop->end_date = $_POST["orderOpenCloseChopEndDate"];
        $shop->saleperson = $_POST["salesperson_code"];


      //  $shop->sales_person = $_POST["salesperson_code"];
        $shop->save();
        unset($_POST["start_date"]);
        unset($_POST["end_date"]);
        unset($_POST["orderOpenCloseChopStartDate"]);
        unset($_POST["orderOpenCloseChopEndDate"]);



        // Load Company

        $CompanyIndex = \Company::find_by_sql("SELECT company_id FROM `company_shop` WHERE `shop_id` = ".intval($_POST["shop_id"]));
        $companyID = $CompanyIndex[0]->attributes["company_id"];
        $company = \Company::find($companyID);
        $company->bill_to_email = $_POST["bill_to_email"];
        $company->ship_to_address = $_POST["ship_to_address"];
        $company->ship_to_address_2 = $_POST["ship_to_address_2"];
        $company->ship_to_postal_code = $_POST["ship_to_postal_code"];
        $company->ship_to_city = $_POST["ship_to_city"];
        $company->contact_name = $_POST["contact_name"];
        $company->contact_email = $_POST["contact_email"];
        $company->contact_phone = $_POST["contact_phone"];

        $company->sales_person = $_POST["salesperson_code"];



        $company->save();

        unset($_POST["name"]);
        unset($_POST["bill_to_email"]);
        unset($_POST["ship_to_address"]);
        unset($_POST["ship_to_address_2"]);
        unset($_POST["ship_to_postal_code"]);
        unset($_POST["ship_to_city"]);
        unset($_POST["contact_name"]);
        unset($_POST["contact_email"]);
        unset($_POST["contact_phone"]);




        if(!$shop || $shop->id == 0) {
            throw new \Exception("Kan ikke finde shop ".$shopid);
        }

        if($shop->id != intval($_POST["shop_id"])) {
            throw new \Exception("Shop id mismatch (".$shop->id." != ".intval($_POST["shop_id"]).")");
        }

        // Load shop metadata
        $shopMetadata = \ShopMetadata::find(intval($_POST["id"]));

        if($shopMetadata == null || $shopMetadata->id == 0) {
            $shopMetadata = new \ShopMetadata();
            $shopMetadata->shop_id = $shop->id;
        }

        if($shopMetadata->shop_id != $shop->id) {
            throw new \Exception("Shop id mismatch (".$shopMetadata->shop_id." != ".$shop->id.")");
        }

        // Set data on shop metadata
        unset($_POST["id"]);
        unset($_POST["shop_id"]);
        unset($_POST["payment_terms_proposed"]);

        // Update shopmetadata
        $shopMetadata->update_attributes($_POST);
        $shopMetadata->save();

        } catch (\Exception $e) {
            throw new \Exception("Fejl, kunne ikke gemme shop metadata: ".$e->getMessage());
        }

         \system::connection()->commit();
        echo json_encode(array("status" => 1,"data"=>"test","shopid" => intval($shopid)));




    }
    public function getShopAddress()
    {
        $shopID = $_POST["shopID"];
        $list = \ShopAddress::find('all', array(
            'conditions' => array(
                'shop_id = ? AND (dot = 1 or carryup = 1)',
                $shopID
            )

        ));
        echo json_encode(array("status" => 1,"data"=>$list));
    }
    public function getshopmetadata() {
        $shopID = intval($_POST["shop_id"]);

        $CompanyIndex = \Company::find_by_sql("SELECT company_id FROM `company_shop` WHERE `shop_id` = ".intval($_POST["shop_id"]));
        $companyID = $CompanyIndex[0]->attributes["company_id"];



        $shopMetadata = \ShopMetadata::find_by_sql("
            SELECT 
                shop_metadata.*,
                shop.start_date,
                shop.end_date,
                cs.name,
                cs.ship_to_address,
                cs.ship_to_address_2,
                cs.ship_to_postal_code,
                cs.ship_to_city,
                cs.contact_name,
                cs.contact_email,
                cs.contact_phone,
                cs.bill_to_email
            
            
                FROM shop_metadata 
                inner join shop on shop.id = shop_metadata.shop_id
                inner join (SELECT company.*,company_shop.shop_id FROM `company_shop` inner join company on company.id = company_shop.company_id where company_shop.shop_id =  ".intval($_POST["shop_id"]).") cs on shop.id = cs.shop_id                                                                   
                                                                 
                WHERE shop_metadata.shop_id = ".$shopID);
        //$shopMetadata = \ShopMetadata::find_by_sql("SELECT * FROM shop_metadata WHERE shop_id = ".$shopID);

        if(count($shopMetadata) == 0) {
            throw new \Exception("Kan ikke finde nogle ordredata på shop ".$shopID);
        }

        echo json_encode(array("status" => 1,"data"=>array("metadata" => $shopMetadata)));


    }
    public function getApprovalState() {
        $shopID = intval($_POST["shop_id"]);

        $shopApproval = \ShopMetadata::find_by_sql("SELECT * FROM shop_approval WHERE shop_id = ".$shopID);


        echo json_encode(array("status" => 1,"data"=>array("metadata" => $shopApproval)));
    }
    public function doConfirmOrderApproval()
    {
        $state = intval($_POST["state"]);
        if($state == 0){
            $state = 1;
        } else if($state == 2){
            $state = 4;
        } else if($state == 3){
            $state = 4;
        }

        $data = array("orderdata_approval"=>$state);
        $shopID = intval($_POST["shop_id"]);
        $ShopApproval = \ShopApproval::find_by_shop_id(intval($_POST["shop_id"]));

        if($ShopApproval == null) {
            $ShopApproval = new \ShopApproval();
            $ShopApproval->shop_id = $shopID;
        }

        $ShopApproval->update_attributes($data);
        $ShopApproval->save();

        // If it does not have order_no set it
        $ShopMetadata = \ShopMetadata::find_by_shop_id($shopID);
        if($ShopMetadata == null) {
            throw new \Exception("Kan ikke finde shop metadata for shop ".$shopID);
        }

        if($ShopMetadata->order_no == "") {
            $ShopMetadata->order_no = \NumberSeries::getNextNumber(21);
            $ShopMetadata->save();
        }

        \system::connection()->commit();
        echo json_encode(array("status" => 1,"shopid" => intval($shopID)));

    }

}