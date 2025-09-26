<?php
class warehousePortalController  Extends baseController
{

    public function Index()
    {

    }




    public function makeAdressReport()
    {
        // Init phpexcel

        $token = $_GET['token']; // Antager at token kommer fra POST

        $list = ShopAddress::find('all', array(
            'select' => 'shop_address.*',
            'joins' => array(
                'INNER JOIN shop ON shop.id = shop_address.shop_id',
                'INNER JOIN navision_location ON navision_location.code = shop.reservation_code'
            ),
            'conditions' => array(
                'navision_location.token = ?',
                $token
            ),
            'order' => 'shop_address.shop_id ASC '
        ));



        $phpExcel = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $phpExcel->removeSheetByIndex(0);

        // Write header
        $outsheet = $phpExcel->createSheet();
        $outsheet->setTitle("contacts");
        $outRow = 1;


        $outsheet->getColumnDimension('B')->setWidth(20);
        $outsheet->getColumnDimension('C')->setWidth(25);
        $outsheet->getColumnDimension('D')->setWidth(24);
        $outsheet->getColumnDimension('E')->setWidth(11);
        $outsheet->getColumnDimension('F')->setWidth(16);
        $outsheet->getColumnDimension('G')->setWidth(12);
        $outsheet->getColumnDimension('H')->setWidth(11);
        $outsheet->getColumnDimension('I')->setWidth(14);
        $outsheet->getColumnDimension('J')->setWidth(15);


        $outsheet->setCellValueByColumnAndRow(1,$outRow,"Address ID");
        $outsheet->setCellValueByColumnAndRow(2,$outRow,"Company name");
        $outsheet->setCellValueByColumnAndRow(3,$outRow,"Address 1");
        $outsheet->setCellValueByColumnAndRow(4,$outRow,"Address 2");
        $outsheet->setCellValueByColumnAndRow(5,$outRow,"Postcode");
        $outsheet->setCellValueByColumnAndRow(6,$outRow,"City");
        $outsheet->setCellValueByColumnAndRow(7,$outRow,"Country Code");
        $outsheet->setCellValueByColumnAndRow(8,$outRow,"State Code");
        $outsheet->setCellValueByColumnAndRow(9,$outRow,"Contact person");
        $outsheet->setCellValueByColumnAndRow(10,$outRow,"Phone");
        $outRow++;
        foreach($list as $location) {
            $location = $location->attributes;
            $outsheet->setCellValueByColumnAndRow(1,$outRow,$this->generateToken(12));
            $outsheet->setCellValueByColumnAndRow(2,$outRow,$location["name"]);
            $outsheet->setCellValueByColumnAndRow(3,$outRow,$location["address"]);
            $outsheet->setCellValueByColumnAndRow(4,$outRow,"");
            $outsheet->setCellValueByColumnAndRow(5,$outRow,$location["zip"]);
            $outsheet->setCellValueByColumnAndRow(6,$outRow,$location["city"]);
            $outsheet->setCellValueByColumnAndRow(7,$outRow,$this->standardizeCountryCode($location["country"]));
            $outsheet->setCellValueByColumnAndRow(8,$outRow,"");
            $outsheet->setCellValueByColumnAndRow(9,$outRow,$location["att"]);
            $outsheet->setCellValueByColumnAndRow(10,$outRow,$location["phone"]);
            $outRow++;


        }

        // Send http headers

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename=adress.xlsx');
        header('Cache-Control: max-age=0');


        // Output as xlsx file
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($phpExcel);
        $writer->save("php://output");

    }

    private function generateToken($length = 10) {
        // Definerer tilladte karakterer (hexadecimale tal 0-9 og bogstaver a-f)
        $chars = '0123456789abcdef';

        $token = '';

        // Generer tilfældige karakterer indtil den ønskede længde er nået
        for ($i = 0; $i < $length; $i++) {
            $token .= $chars[rand(0, strlen($chars) - 1)];
        }

        return "GF".$token;
    }

    public function login()
    {
        $password = $_POST["password"];
        $username = $_POST["username"];
        $options = array('username' => $username,'password'=>$password);
        $navisionLocation = NavisionLocation::find('all', $options);
        if(sizeof($navisionLocation)==0){
            response::success(json_encode([]));
        } else {
            $token = $navisionLocation[0]->attributes["token"];
            $name = $navisionLocation[0]->attributes["name"];
            response::success(json_encode(["token"=>$token,"name"=>$name]));
        }
    }
    public function readStatus()
    {

        $token = $_POST["token"];
        $expireDateId = isset($_POST['expire_date_id']) ? $_POST['expire_date_id'] : null;

        $sql = "SELECT * FROM `warehouse_settings` WHERE `shop_id` in (SELECT id FROM `shop` WHERE `token` LIKE '".$token."')";

        if ($expireDateId) {
            $sql .= " AND expire_date_id = " . intval($expireDateId);
        } else {
            $sql .= " AND expire_date_id IS NULL";
        }

        $WarehouseFiles = WarehouseFiles::find_by_sql($sql);
        response::success(json_encode($WarehouseFiles));



    }
    public function buttonClick(){
        $token = $_POST["token"];
        $expireDateId = isset($_POST['expire_date_id']) ? $_POST['expire_date_id'] : null;

        $sql = "select id from warehouse_settings where shop_id in ( SELECT id FROM `shop` WHERE `token` LIKE '".$token."')";
        if ($expireDateId) {
            $sql .= " AND expire_date_id = " . intval($expireDateId);
        } else {
            $sql .= " AND expire_date_id IS NULL";
        }

        $WarehouseSettings = WarehouseSettings::find_by_sql($sql);

        if (sizeof($WarehouseSettings) == 0) {
            response::success(json_encode([]));
            return;
        }

        $logdate = date('Y-m-d H:i:s');
        if($_POST["button_type"] == "download") {
            $field = "log_download";
        }
        if($_POST["button_type"] == "files_status") {
            $field = "log_menu";
        }
        if($_POST["button_type"] == "info") {
            $field = "log_info";
        }
        if($_POST["button_type"] == "status_update") {
            $field = "log_status";
        }

        $id = $WarehouseSettings[0]->attributes["id"];
        $data = [
            "id" => $id,
            $field => $logdate
        ];

        $res = WarehouseSettings::updateFiles($data);
        response::success(json_encode($res));
    }
    public function readShopDownloadData(){

        $token = $_POST["token"];
        $expireDateId = isset($_POST['expire_date_id']) ? $_POST['expire_date_id'] : null;

        $shop = Shop::find_by_token($token);

        // Update log_menu for appropriate warehouse_settings record
        $logSql = "UPDATE warehouse_settings SET log_menu = '" . date('Y-m-d H:i:s') . "' WHERE shop_id = " . $shop->id;
        if ($expireDateId) {
            $logSql .= " AND expire_date_id = " . intval($expireDateId);
        } else {
            $logSql .= " AND expire_date_id IS NULL";
        }
        Dbsqli::setSql2($logSql);

        // Get warehouse files with expire_date_id filter
        $filesSql = "SELECT * FROM `warehouse_files` WHERE `shop_id` = " . $shop->id . " AND active = 1";
        if ($expireDateId) {
            $filesSql .= " AND expire_date_id = " . intval($expireDateId);
        } else {
            $filesSql .= " AND expire_date_id IS NULL";
        }

        $WarehouseFiles = WarehouseFiles::find_by_sql($filesSql);
        response::success(json_encode($WarehouseFiles));
    }

    /*
     * SELECT
                shop.`name`,
                shop.token,
                COUNT(`order`.id) AS order_count,
                COUNT(DISTINCT `order`.`present_model_id`) AS order_no_count,
                warehouse_settings.noter,
                warehouse_settings.note_move_order,
                warehouse_settings.packaging_status,
                shop_board.valgshopansvarlig,
                shop_board.levering,
                shop_board.udland,
                shop_board.flere_leveringsadresser,
                company.so_no

            FROM
                `shop`
            left join
                `order` ON shop.id = `order`.`shop_id`
            left join
                warehouse_settings on `shop`.id = warehouse_settings.shop_id
            left JOIN
                shop_board on shop.id = shop_board.fk_shop
            left JOIN
                company on company.id = (SELECT company_id FROM `company_shop` WHERE shop_id = `shop`.id )

            WHERE
                `shop`.`reservation_code` IN (
                    SELECT `code`
                    FROM `navision_location`
                    WHERE `token` LIKE '".$token."'
                )
              and shop.is_gift_certificate = 0
            GROUP BY
                shop.id, shop.`name`, shop.token
             order by   shop_board.levering
     *
     *
     */

    public function readShopData()
    {
        $token = $_POST["token"];







        $ShopData = Shop::find_by_sql("   SELECT 
                shop.localisation,             
                shop.`name`,
                shop.token,
         		COUNT(DISTINCT shop_user.id) AS order_count,	
                COUNT(DISTINCT `order`.`present_model_id`) AS order_no_count,
                warehouse_settings.noter,
                warehouse_settings.note_move_order,
                warehouse_settings.packaging_status,
                shop_metadata.salesperson_code as valgshopansvarlig,
                shop_metadata.delivery_date as levering,
                shop_metadata.foreign_delivery as udland,
                shop_metadata.multiple_deliveries as flere_leveringsadresser, 
                shop_metadata.so_no,
                shop_metadata.user_count,
                company.gift_responsible,
                foreign_delivery_date,

                -- Cardshop fields
                cs.shop_id as is_cardshop,
                ce.expire_date_id,
                ed.display_date as expire_display_date,
                ed.week_no as expire_week_no,
                CASE WHEN cs.shop_id IS NOT NULL THEN 'Cardshop' ELSE 'Normal' END as shop_type

            FROM
                `shop`
                inner JOIN navision_location on navision_location.code = `shop`.`reservation_code`

                -- Cardshop joins
                left JOIN cardshop_settings cs on cs.shop_id = shop.id
                left JOIN cardshop_expiredate ce on ce.shop_id = shop.id
                left JOIN expire_date ed on ed.id = ce.expire_date_id

            left join
                warehouse_settings on `shop`.id = warehouse_settings.shop_id
                AND (warehouse_settings.expire_date_id = ce.expire_date_id OR (warehouse_settings.expire_date_id IS NULL AND cs.shop_id IS NULL))
            left JOIN
                shop_metadata on shop.id = shop_metadata.shop_id
            left JOIN
                company_shop on company_shop.shop_id = shop.id
            left JOIN
                company on company.id = company_shop.company_id
	    left join 
				shop_user on shop_user.company_id =  company.id                
        left join
                `order` ON  `order`.`shopuser_id` = shop_user.id
        
        WHERE 
       
               navision_location.`token` LIKE '".$token."'
    and (shop.shop_mode = 1 or shop.shop_mode = 6)
    and (shop_metadata.so_no != '' or warehouse_settings.expire_date_id is not null)
    and (shop_metadata.so_no LIKE 'SO%' or shop_metadata.so_no LIKE 'so%'  or warehouse_settings.expire_date_id is not null)
    and (cs.shop_id IS NULL OR (ed.is_delivery != 1 AND ed.is_home_delivery != 1))
             GROUP BY
                shop.id, ce.expire_date_id
          ");


        response::success(json_encode($ShopData));
    }
    public function download()
    {
        if (isset($_GET['token'])) {
            $token = $_GET['token'];

            $options = array('token' => $token,'active' =>1);
            $file = WarehouseFiles::find('all', $options);
            if(sizeof($file) == 0){
                echo "Filen eksisterer ikke!";
                return;
            }

            $file_path = "upload/warehouse/".$file[0]->filename;
            $download_filename = $file[0]->real_filename;

            header('Content-Type: application/octet-stream');
            header("Content-Transfer-Encoding: Binary");
            header("Content-disposition: attachment; filename=\"" . $download_filename . "\"");

            readfile($file_path); // Output the file content for download
        }

    }


    public function approval()
    {
        $token = $_POST["token"];
        $approved_count_date = $_POST["approved_count_date"];

        // New approval fields
        $approved_count_date_approved_by = $_POST["approved_count_date_approved_by"];
        $approved_package_instructions_approved_by = $_POST["approved_package_instructions_approved_by"];
        $approved_ontime_approved_by = $_POST["approved_ontime_approved_by"];

        // Convert boolean strings to integers
        $approved_package_instructions = $_POST["approved_package_instructions"] == "true" ? 1 : 0;
        $approved_ontime = $_POST["approved_ontime"] == "true" ? 1 : 0;

        // Generate approved_date with PHP
        $approved_date = date('Y-m-d H:i:s');

        // Format the count date if provided
        $approved_count_date = $approved_count_date == "" ? NULL : date('d-m-Y H:i:s', strtotime($approved_count_date));

        // Get warehouse settings ID for the shop
        $expireDateId = isset($_POST['expire_date_id']) ? $_POST['expire_date_id'] : null;

        $sql = "select id from warehouse_settings where shop_id in ( SELECT id FROM `shop` WHERE `token` LIKE '".$token."')";
        if ($expireDateId) {
            $sql .= " AND expire_date_id = " . intval($expireDateId);
        } else {
            $sql .= " AND expire_date_id IS NULL";
        }

        $WarehouseSettings = WarehouseSettings::find_by_sql($sql);

        if (sizeof($WarehouseSettings) == 0) {
            // Create warehouse_settings record if it doesn't exist
            $shopSql = "SELECT id FROM `shop` WHERE `token` LIKE '".$token."'";
            $shopResult = Dbsqli::setSql2($shopSql);
            if ($shopResult && $shopResult->num_rows > 0) {
                $shop = $shopResult->fetch_assoc();
                $shopId = $shop['id'];

                $insertSql = "INSERT INTO warehouse_settings (shop_id, token";
                if ($expireDateId) {
                    $insertSql .= ", expire_date_id";
                }
                $insertSql .= ") VALUES (".$shopId.", '".generateTokenWithTime()."'";
                if ($expireDateId) {
                    $insertSql .= ", ".intval($expireDateId);
                }
                $insertSql .= ")";

                Dbsqli::setSql2($insertSql);

                // Re-fetch the created record
                $WarehouseSettings = WarehouseSettings::find_by_sql($sql);
            }

            if (sizeof($WarehouseSettings) == 0) {
                response::success(json_encode(['error' => 'Could not create warehouse_settings record']));
                return;
            }
        }

        $id = $WarehouseSettings[0]->attributes["id"];

        // Prepare data array with all fields
        $data = [
            "id" => $id,
            "pick_approval" => 1,
            "approved_count_date" => $approved_count_date,
            "approved_count_date_approved_by" => $approved_count_date_approved_by,
            "approved_package_instructions_approved_by" => $approved_package_instructions_approved_by,
            "approved_ontime_approved_by" => $approved_ontime_approved_by,
            "approved_date" => $approved_date,
            "approved_package_instructions" => $approved_package_instructions,
            "approved_ontime" => $approved_ontime
        ];

        // Update the database
        $res = WarehouseSettings::updateFiles($data);
        response::success(json_encode($res));
    }
    public function updateStatus()
    {
        $token = $_POST["token"];
        $packaging_status = $_POST['packaging_status'];
        $expireDateId = isset($_POST['expire_date_id']) ? $_POST['expire_date_id'] : null;

        $sql = "select id from warehouse_settings where shop_id in ( SELECT id FROM `shop` WHERE `token` LIKE '".$token."')";
        if ($expireDateId) {
            $sql .= " AND expire_date_id = " . intval($expireDateId);
        } else {
            $sql .= " AND expire_date_id IS NULL";
        }

        $WarehouseFiles = WarehouseSettings::find_by_sql($sql);

        if (sizeof($WarehouseFiles) == 0) {
            // Create warehouse_settings record if it doesn't exist
            $shopSql = "SELECT id FROM `shop` WHERE `token` LIKE '".$token."'";
            $shopResult = Dbsqli::setSql2($shopSql);
            if ($shopResult && $shopResult->num_rows > 0) {
                $shop = $shopResult->fetch_assoc();
                $shopId = $shop['id'];

                $insertSql = "INSERT INTO warehouse_settings (shop_id, token";
                if ($expireDateId) {
                    $insertSql .= ", expire_date_id";
                }
                $insertSql .= ") VALUES (".$shopId.", '".generateTokenWithTime()."'";
                if ($expireDateId) {
                    $insertSql .= ", ".intval($expireDateId);
                }
                $insertSql .= ")";

                Dbsqli::setSql2($insertSql);

                // Re-fetch the created record
                $WarehouseFiles = WarehouseSettings::find_by_sql($sql);
            }

            if (sizeof($WarehouseFiles) == 0) {
                response::success(json_encode(['error' => 'Could not create warehouse_settings record']));
                return;
            }
        }

        $id =  $WarehouseFiles[0]->attributes["id"];
        $data = [
            "id"=>$id,
            "packaging_status" => $packaging_status
        ];
        $res = WarehouseSettings::updateFiles($data);
        response::success(json_encode($res));
    }
    public function updateNoteToGf()
    {
        $token = $_POST["token"];
        $note = $_POST['note_from_warehouse_to_gf'];
        $expireDateId = isset($_POST['expire_date_id']) ? $_POST['expire_date_id'] : null;

        $sql = "select id from warehouse_settings where shop_id in ( SELECT id FROM `shop` WHERE `token` LIKE '".$token."')";
        if ($expireDateId) {
            $sql .= " AND expire_date_id = " . intval($expireDateId);
        } else {
            $sql .= " AND expire_date_id IS NULL";
        }

        $WarehouseFiles = WarehouseSettings::find_by_sql($sql);

        if (sizeof($WarehouseFiles) == 0) {
            // Create warehouse_settings record if it doesn't exist
            $shopSql = "SELECT id FROM `shop` WHERE `token` LIKE '".$token."'";
            $shopResult = Dbsqli::setSql2($shopSql);
            if ($shopResult && $shopResult->num_rows > 0) {
                $shop = $shopResult->fetch_assoc();
                $shopId = $shop['id'];

                $insertSql = "INSERT INTO warehouse_settings (shop_id, token";
                if ($expireDateId) {
                    $insertSql .= ", expire_date_id";
                }
                $insertSql .= ") VALUES (".$shopId.", '".generateTokenWithTime()."'";
                if ($expireDateId) {
                    $insertSql .= ", ".intval($expireDateId);
                }
                $insertSql .= ")";

                Dbsqli::setSql2($insertSql);

                // Re-fetch the created record
                $WarehouseFiles = WarehouseSettings::find_by_sql($sql);
            }

            if (sizeof($WarehouseFiles) == 0) {
                response::success(json_encode(['error' => 'Could not create warehouse_settings record']));
                return;
            }
        }

        $id =  $WarehouseFiles[0]->attributes["id"];
        $data = [
            "id"=>$id,
            "note_from_warehouse_to_gf" => $note
        ];
        $res = WarehouseSettings::updateFiles($data);
        response::success(json_encode($res));
    }
    public function showDeleveryDetail()
    {
        if(isset($_GET["shopid"])){
            $id =  $_GET["shopid"];
        }
        if(isset($_GET["token"])){
            $shop = Shop::find_by_token($_GET["token"]);
            $id = $shop->attributes["id"];
        }

        $warehouseSettings = WarehouseSettings::find_by_shop_id($id);
        if($warehouseSettings){
            $warehouseSettings->log_menu = date('Y-m-d H:i:s');
            $warehouseSettings->save();
            System::connection()->commit();
            System::connection()->transaction();
        }


        $this->registry->template->id = $id;
        echo $this->registry->template->show('warehouseDeleveryDetail_view');
    }

    public function getshopmetadata() {
        $shopID = intval($_POST["shop_id"]);

        $CompanyIndex = Company::find_by_sql("SELECT company_id FROM `company_shop` WHERE `shop_id` = ".intval($_POST["shop_id"]));
        $companyID = $CompanyIndex[0]->attributes["company_id"];



        $shopMetadata = ShopMetadata::find_by_sql("
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
                cs.bill_to_email,
                warehouse_settings.`noter` as w_noter,
                warehouse_settings.`note_move_order` as w_note_move
            
            
            
                FROM shop_metadata 
                inner join shop on shop.id = shop_metadata.shop_id
                inner join (SELECT company.*,company_shop.shop_id FROM `company_shop` inner join company on company.id = company_shop.company_id where company_shop.shop_id =  ".intval($_POST["shop_id"]).") cs on shop.id = cs.shop_id                                                                   
                    left join warehouse_settings on warehouse_settings.shop_id = shop_metadata.shop_id                                                
                WHERE shop_metadata.shop_id = ".$shopID);
        //$shopMetadata = \ShopMetadata::find_by_sql("SELECT * FROM shop_metadata WHERE shop_id = ".$shopID);

        if(count($shopMetadata) == 0) {
            throw new \Exception("Kan ikke finde nogle ordredata på shop ".$shopID);
        }

        echo json_encode(array("status" => 1,"data"=>array("metadata" => $shopMetadata)));


    }
    public function getShopAddress()
    {
        $shopID = $_POST["shopID"];
        $list = ShopAddress::find('all', array(
            'conditions' => array(
                'shop_id = ? AND (dot = 1 or carryup = 1)',
                $shopID
            )

        ));
        echo json_encode(array("status" => 1,"data"=>$list));
    }
    private function standardizeCountryCode($country) {
        // Fjern eventuelle ekstra mellemrum og konverter til lowercase for sammenligning
        $country = trim(strtolower($country));

        // Array med mapping af forskellige varianter til standard ISO koder
        $countryMapping = [
            // Danmark varianter
            'denmark' => 'DK',
            'danmark' => 'DK',
            'dansk' => 'DK',
            'danmarl' => 'DK',
            'danmmark' => 'DK',
            'danmaek' => 'DK',
            'dk' => 'DK',

            // Norge varianter
            'norway' => 'NO',
            'norge' => 'NO',
            'no' => 'NO',
            'sandnes' => 'NO', // Norsk by

            // Sverige varianter
            'sweden' => 'SE',
            'sverige' => 'SE',
            'se' => 'SE',

            // Finland varianter
            'finland' => 'FI',
            'findland' => 'FI',
            'fi' => 'FI',

            // Tyskland varianter
            'germany' => 'DE',
            'de' => 'DE',
            'tyskland' => 'DE',

            // Storbritannien varianter
            'uk' => 'GB',
            'united kingdom' => 'GB',
            'england' => 'GB',
            'storbrittanien' => 'GB',

            // Holland/Netherlands varianter
            'netherlands' => 'NL',
            'the netherlands' => 'NL',
            'holland' => 'NL',

            // Øvrige lande
            'poland' => 'PL',
            'polen' => 'PL',
            'luxembourg' => 'LU',
            'bulgaria' => 'BG',
            'spain' => 'ES',
            'spanien' => 'ES',
            'romania' => 'RO',
            'czech republic' => 'CZ',
            'iceland' => 'IS',
            'island' => 'IS',
            'croatia' => 'HR',
            'ireland' => 'IE',
            'latvia' => 'LV',
            'letland' => 'LV',
            'france' => 'FR',
            'frankrig' => 'FR',
            'italy' => 'IT',
            'italien' => 'IT',
            'austria' => 'AT',
            'switzerland' => 'CH',
            'belgium' => 'BE',
            'belgien' => 'BE',
            'benelux' => 'BX',  // Special case for Benelux region
            'lithuania' => 'LT',
            'united states' => 'US'
        ];

        // Check om landet findes i mapping
        if (isset($countryMapping[$country])) {
            return $countryMapping[$country];
        }

        // Hvis landet ikke findes i mapping, returner N/A
        return 'N/A';
    }
}