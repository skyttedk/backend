<?php

namespace GFUnit\valgshop\main;
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
                <a mc:disable-tracking href='https://system.gavefabrikken.dk/2024/gavefabrikken_backend/index.php?rt=overbookedApproval/approv&token=" . $token . "&shopid=" . $shop_id . "&action=shop_start'>Godkend</a></td>
               <td> <a mc:disable-tracking href='https://system.gavefabrikken.dk/2024/gavefabrikken_backend/index.php?rt=overbookedApproval/noApprov&token=" . $token . "&shopid=" . $shop_id . "&action=shop_start&sa=" . $_POST["salesperson_code"] . "'>Afvis (mail sendes)</a></td>
                </tr>";
                $this->notApprov($shop_id, "shop_start");
            }
        }

        $endDate = isset($_POST["orderOpenCloseChopEndDate"]) ? $this->overbookedEndDate($this->convertDateFormat($_POST["orderOpenCloseChopEndDate"])) : null;
        if (isset($endDate) && $endDate !== null && sizeof($endDate)) {
            if(!$this->isApprov($shop_id,"shop_end")) {
                $mailTxt .= "<tr><td>Shop luk</td><td>Dato: " . $_POST["orderOpenCloseChopEndDate"] . "</td><td>Antal valgte:" . $endDate[0]->attributes["count_per_day"] . "</td><td>
                 <a mc:disable-tracking href='https://system.gavefabrikken.dk/2024/gavefabrikken_backend/index.php?rt=overbookedApproval/approv&token=" . $token . "&shopid=" . $shop_id . "&action=shop_end'>Godkend</a></td>
               <td>  <a mc:disable-tracking href='https://system.gavefabrikken.dk/2024/gavefabrikken_backend/index.php?rt=overbookedApproval/noApprov&token=" . $token . "&shopid=" . $shop_id . "&action=shop_end&sa=" . $_POST["salesperson_code"] . "'>Afvis (mail sendes)</a></td>
                 </tr>";
                $this->notApprov($shop_id, "shop_end");
            }
        }

        $deliveryDate = isset($_POST["delivery_date"]) ? $this->overbookedDeliveryDate($this->convertDateFormat($_POST["delivery_date"])) : null;
        if (isset($deliveryDate) && $deliveryDate !== null && sizeof($deliveryDate)) {
            if(!$this->isApprov($shop_id,"shop_delivery")){
                $mailTxt.= "<tr><td>Levering</td><td>Dato: ".$_POST["delivery_date"]."</td><td>Antal valgte:".$deliveryDate[0]->attributes["count_per_day"]."</td><td>
                <a mc:disable-tracking href='https://system.gavefabrikken.dk/2024/gavefabrikken_backend/index.php?rt=overbookedApproval/approv&token=".$token."&shopid=".$shop_id."&action=shop_delivery'>Godkend</a></td>
               <td> <a mc:disable-tracking href='https://system.gavefabrikken.dk/2024/gavefabrikken_backend/index.php?rt=overbookedApproval/noApprov&token=".$token."&shopid=".$shop_id."&action=shop_delivery&sa=".$_POST["salesperson_code"]."'>Afvis (mail sendes)</a></td>
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
    


        $openSql = "SELECT  DATE(start_date) AS date, COUNT(*) AS count_per_day FROM shop WHERE localisation = ".$language_id." and start_date IS NOT NULL and `start_date` > '2025-04-01 14:12:30' GROUP BY DATE(start_date)";
        $openRS = \Shop::find_by_sql($openSql);
        $closeSql = "SELECT  DATE(end_date) AS date, COUNT(*) AS count_per_day FROM shop WHERE localisation = ".$language_id." and end_date IS NOT NULL and `end_date` > '2025-04-01 14:12:30' GROUP BY DATE(end_date)";
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
        $shop->login_background = $_POST["login_background"];

      //  $shop->sales_person = $_POST["salesperson_code"];
        $shop->save();
        unset($_POST["start_date"]);
        unset($_POST["end_date"]);
        unset($_POST["orderOpenCloseChopStartDate"]);
        unset($_POST["orderOpenCloseChopEndDate"]);
        unset($_POST["login_background"]);


        // Load Company

        $CompanyIndex = \Company::find_by_sql("SELECT company_id FROM `company_shop` WHERE `shop_id` = ".intval($_POST["shop_id"]));


        $companyID = $CompanyIndex[0]->attributes["company_id"];
        $company = \Company::find($companyID);
        $company->cvr = $_POST["cvr"];
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
        unset($_POST["cvr"]);
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
      //  $shopMetadata->update_attributes($_POST);



        // generelt

        $genereltPost = [
            'order_type',
            'nav_debitor_no',
        ];

        $fakturainfoPost = [
            'nav_debitor_no',
            'salesperson_code',
            'prepayment',
            'invoice_fee',
            'user_count',
            'present_count',
            'multiple_budgets_data',
            'budget',
            'payment_terms',
            'payment_special',
            'payment_special_note',
            'requisition_no',
            'has_contract',
            'discount_option',
            'discount_value',
            'valgshop_fee',
            'environment_fee',
            'economy_info',
            'economy_info_note',
            'order_info',
            'order_info_note',
            'flex_budget'
        ];
        $leveringPost = [
            'delivery_type',
            'delivery_date',
            'flex_start_delivery_date',
            'flex_end_delivery_date',
            'early_delivery',
            'multiple_deliveries',
             'multiple_deliveries_data', 
            'handover_date',
            'private_delivery',
            'privatedelivery_price',
            'private_retur_type',
            'foreign_delivery',
            'language_names',
            'foreign_norge_date',
            'foreign_delivery_names',
            'foreign_delivery_date',
            'foreign_tyskland_date',
            'foreign_england_date',
            'foreign_eu_date',
            'foreign_amerika_date',
            'foreign_andre_date',
            'carryup_use',
            'deliveryprice_option',
            'deliveryprice_amount',
            'deliveryprice_note',
            'dot_amount',
            'dot_price',
            'dot_note',
            'dot_use',
            'carryup_amount',
            'carryup_price',
            'carryup_note',
            'deliveryprice_amount',
            'deliveryprice_note',
            'delivery_note_internal',
            'delivery_note_external'
        ];

         $gaverPost = [
                'plant_tree',
                'plant_tree_price',
                'present_papercard',
                'present_papercard_price',
                'present_papercard_price_group',
                'present_wrap',
                'present_wrap_price',
                'autogave_use',
                'autogave_itemno',
                'present_nametag',
                'handling_notes',
                'loan_use',
                'loan_deliverydate',
                'loan_pickupdate',
                'loan_notes',
                'present_nametag_price',
                'handling_special'
         ];
         $valgshopPost = [
                'orderOpenCloseChopStartDate',
                'orderOpenCloseChopEndDate',
                'deadline_testshop',
                'deadline_changes',
                'deadline_customerdata',
                'deadline_listconfirm',
                'reminder_use',
                'login_background',
                'user_username',
                'user_username_note',
                'user_password',
                'gaveklubben_link',
                'language_names_list',
                'language',
                'reminder_date',
                'reminder_note',
                'user_password_note',
                'otheragreements_note',
                'deliverydate_receipt',
                'deliverydate_receipt_toggle',
                'deliverydate_receipt_custom_text'

         ];










        $postFields = array_merge($genereltPost, $fakturainfoPost, $leveringPost,$gaverPost,$valgshopPost);
        foreach ($postFields as $field) {
            if (isset($_POST[$field])) {
                $shopMetadata->$field = $_POST[$field];
            }
        }

        // SPECIAL BUDGET HÅNDTERING
        if (isset($_POST['multiple_budgets_toggle'])) {
            if ($_POST['multiple_budgets_toggle'] == '1') {
                // Multiple budgets er valgt
                if (isset($_POST['multiple_budgets_data']) && !empty($_POST['multiple_budgets_data'])) {
                    $shopMetadata->multiple_budgets_data = $_POST['multiple_budgets_data'];
                }

                // Ryd enkelt budget felter
                $shopMetadata->budget = null;
                $shopMetadata->flex_budget = 0;

            } else {
                // Enkelt budget er valgt
                // budget og flex_budget er allerede sat i loopet ovenfor

                // Ryd multiple budgets data
                $shopMetadata->multiple_budgets_data = null;
            }
        }





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
                shop.login_background,
                cs.name,
                cs.ship_to_address,
                cs.ship_to_address_2,
                cs.ship_to_postal_code,
                cs.ship_to_city,
                cs.contact_name,
                cs.contact_email,
                cs.contact_phone,
                cs.bill_to_email,
                cs.cvr
            
            
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
// Tilføj disse metoder til Controller klassen

// Tilføj disse metoder til Controller klassen

    public function uploadDocument($shopid = 0) {
        // Start med at deaktivere HTML fejl output
        ini_set('display_errors', 0);
        header('Content-Type: application/json');

        try {
            if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
                throw new \Exception('Ingen fil uploadet eller fil fejl');
            }

            $shopId = intval($_POST['shop_id']);
            if($shopId == 0 && $shopid > 0) {
                $shopId = intval($shopid);
            }

            $documentType = $_POST['document_type'];

            // Validate file
            $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'image/jpeg', 'image/png', 'image/jpg'];

            $fileType = $_FILES['file']['type'];
            if (!in_array($fileType, $allowedTypes)) {
                throw new \Exception('Filtypen er ikke tilladt: ' . $fileType);
            }

            // Create upload directory - brug relativ sti fra controller
            $baseUploadPath = $_SERVER['DOCUMENT_ROOT'] . '/2025/gavefabrikken_backend/uploads/';
            $uploadDir = $baseUploadPath . 'shop_documents/' . $shopId . '/';

            if (!file_exists($uploadDir)) {
                if (!mkdir($uploadDir, 0777, true)) {
                    throw new \Exception('Kunne ikke oprette upload mappe: ' . $uploadDir);
                }
            }

            // Generate unique filename
            $fileExtension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '_' . time() . '.' . $fileExtension;
            $uploadPath = $uploadDir . $filename;

            // Move uploaded file
            if (!move_uploaded_file($_FILES['file']['tmp_name'], $uploadPath)) {
                throw new \Exception('Fejl ved flytning af fil til: ' . $uploadPath);
            }

            // Save to database
            $document = new \ShopDocument();
            $document->shop_id = $shopId;
            $document->document_type = $documentType;
            $document->filename = $filename;
            $document->original_filename = $_FILES['file']['name'];
            $document->file_size = $_FILES['file']['size'];
            $document->file_type = $fileType;
            $document->uploaded_by = \router::$systemUser ? \router::$systemUser->id : null;
            $document->save();

            \system::connection()->commit();

            echo json_encode([
                'status' => 1,
                'data' => $document->attributes,
                'message' => 'Fil uploadet succesfuldt'
            ]);

        } catch (\Exception $e) {
            echo json_encode([
                'status' => 0,
                'message' => $e->getMessage()
            ]);
        }
        exit;
    }

    public function getDocuments() {
        // Deaktiver HTML fejl output
        ini_set('display_errors', 0);
        header('Content-Type: application/json');

        try {
            $shopId = intval($_GET['shop_id']);
            $documentType = $_GET['document_type'];

            if($shopId == 0) {
                throw new \Exception('Ugyldigt shop ID');
            }

            $documents = \ShopDocument::find('all', [
                'conditions' => ['shop_id = ? AND document_type = ? AND deleted = 0', $shopId, $documentType],
                'order' => 'upload_date DESC'
            ]);

            $data = [];
            if($documents) {
                foreach ($documents as $doc) {
                    $data[] = $doc->attributes;
                }
            }

            echo json_encode([
                'status' => 1,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            echo json_encode([
                'status' => 0,
                'message' => $e->getMessage(),
                'data' => []
            ]);
        }
        exit;
    }

    public function downloadDocument($docid = 0) {
        try {
            // Håndter både GET parameter og URL parameter
            $docId = 0;
            if(isset($_GET['id'])) {
                $docId = intval($_GET['id']);
            } elseif($docid > 0) {
                $docId = intval($docid);
            }

            if($docId == 0) {
                throw new \Exception('Dokument ID mangler');
            }

            $document = \ShopDocument::find($docId);

            if (!$document) {
                throw new \Exception('Dokument ikke fundet');
            }

            $baseUploadPath = $_SERVER['DOCUMENT_ROOT'] . '/2025/gavefabrikken_backend/uploads/';
            $filePath = $baseUploadPath . 'shop_documents/' . $document->shop_id . '/' . $document->filename;

            if (!file_exists($filePath)) {
                throw new \Exception('Fil ikke fundet på serveren: ' . $document->filename);
            }

            // Clear any output buffers
            while (ob_get_level()) {
                ob_end_clean();
            }

            // Set headers for download
            header('Content-Type: ' . $document->file_type);
            header('Content-Disposition: attachment; filename="' . $document->original_filename . '"');
            header('Content-Length: ' . filesize($filePath));
            header('Cache-Control: private');
            header('Pragma: public');

            // Læs og output fil
            readfile($filePath);
            exit;

        } catch (\Exception $e) {
            header('Content-Type: text/plain');
            echo 'Fejl: ' . $e->getMessage();
            exit;
        }
    }

    public function deleteDocument($docid = 0) {
        // Deaktiver HTML fejl output
        ini_set('display_errors', 0);
        header('Content-Type: application/json');

        try {
            $docId = intval($_POST['id']);
            if($docId == 0 && $docid > 0) {
                $docId = intval($docid);
            }

            $shopId = intval($_POST['shop_id']);

            $document = \ShopDocument::find($docId);

            if (!$document || $document->shop_id != $shopId) {
                throw new \Exception('Dokument ikke fundet eller ugyldigt');
            }

            // Soft delete
            $document->deleted = 1;
            $document->deleted_date = date('Y-m-d H:i:s');
            $document->deleted_by = \router::$systemUser ? \router::$systemUser->id : null;
            $document->save();

            \system::connection()->commit();

            echo json_encode([
                'status' => 1,
                'message' => 'Dokument slettet'
            ]);

        } catch (\Exception $e) {
            echo json_encode([
                'status' => 0,
                'message' => $e->getMessage()
            ]);
        }
        exit;
    }

// Test metode til debugging
    public function testDocuments() {
        header('Content-Type: application/json');

        // Test upload sti
        $baseUploadPath = $_SERVER['DOCUMENT_ROOT'] . '/2025/gavefabrikken_backend/uploads/';
        $testData = [
            'status' => 1,
            'message' => 'API virker',
            'shop_id' => $_GET['shop_id'] ?? 'ingen',
            'upload_path' => $baseUploadPath,
            'path_exists' => file_exists($baseUploadPath) ? 'Ja' : 'Nej',
            'path_writable' => is_writable($baseUploadPath) ? 'Ja' : 'Nej',
            'document_root' => $_SERVER['DOCUMENT_ROOT']
        ];

        echo json_encode($testData);
        exit;
    }
}