<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
class shopWarehouseController Extends baseController
{

    public function Index()
    {

    }
    public function move(){


        $source_directory = 'upload/move/';
        $destination_directory = 'upload/warehouse/';



        $files = scandir($source_directory);

        foreach ($files as $file) {
            if ($file != '.' && $file != '..') {  // skip current and parent directory entries

                $shopID = substr($file, 0, 4);




                $token = generateTokenWithTime();
                $extension = pathinfo($file, PATHINFO_EXTENSION);
                $filename  = $token.".".$extension;
                $fileSize = filesize($source_directory . $file);
                $old_file = $source_directory . $file;
                $new_file = $destination_directory . $filename;

                $postData = [
                    "filename"=> $filename,
                    "file_size"=> $fileSize,
                    "extension" =>$extension,
                    "token"=>$token,
                    "real_filename"=> $file,
                    "shop_id"=> $shopID,
                    "user_id"=>40
                ];


                //     $res = WarehouseFiles::createFiles($postData);


                /*
                            if (copy($old_file, $new_file)) {
                                echo 'Moved: ' . $file . PHP_EOL;
                            } else {
                                echo 'Failed to move: ' . $file . PHP_EOL;
                            }
                */
            }

        }
        response::success(json_encode([]));
    }


    public function updateStatus()
    {
        if (isset($_POST['shop_id'])) {
            $shopID = $_POST['shop_id'];
            $expireDateId = isset($_POST['expire_date_id']) ? $_POST['expire_date_id'] : null;
            $packaging_status = $_POST['packaging_status'];

            // Use original simple logic - let ActiveRecord handle it
            $options = array('shop_id' => $shopID);
            if ($expireDateId) {
                $options['expire_date_id'] = $expireDateId;
            }

            $settings = WarehouseSettings::find('all', $options);
            if (sizeof($settings) == 0) {
                $token = generateTokenWithTime();
                $postData = [
                    "packaging_status" => $packaging_status,
                    "token" => $token,
                    "shop_id"=>$shopID,
                    "expire_date_id"=>$expireDateId,
                    "noter"=>""
                ];
                $res = WarehouseSettings::createFiles($postData);
            } else {
                $id = $settings[0]->id;
                $data = [
                    "id"=>$id,
                    "packaging_status" => $packaging_status
                ];
                $res = WarehouseSettings::updateFiles($data);
            }
            response::success(json_encode($res));
        }
    }



    public function updateNoteMoveOrder()
    {
        if (isset($_POST['shop_id'])) {
            $shopID = $_POST['shop_id'];
            $expireDateId = isset($_POST['expire_date_id']) ? $_POST['expire_date_id'] : null;

            // Use original simple logic - let ActiveRecord handle it
            $options = array('shop_id' => $shopID);
            if ($expireDateId) {
                $options['expire_date_id'] = $expireDateId;
            }

            $settings = WarehouseSettings::find('all', $options);
            $note = $_POST['note'];
            if (sizeof($settings) == 0) {
                $token = generateTokenWithTime();

                $postData = [
                    "token" => $token,
                    "shop_id"=>$shopID,
                    "expire_date_id"=>$expireDateId,
                    "note_move_order"=>$note
                ];
                $res = WarehouseSettings::createFiles($postData);
            } else {
                $id = $settings[0]->id;
                $data = [
                    "id"=>$id,
                    "note_move_order"=>$note
                ];
                $res = WarehouseSettings::updateFiles($data);
            }
            response::success(json_encode($res));
        }
    }

    public function updateNote()
    {
        if (isset($_POST['shop_id'])) {
            $shopID = $_POST['shop_id'];
            $expireDateId = isset($_POST['expire_date_id']) ? $_POST['expire_date_id'] : null;

            // Use original simple logic - let ActiveRecord handle it
            $options = array('shop_id' => $shopID);
            if ($expireDateId) {
                $options['expire_date_id'] = $expireDateId;
            }

            $settings = WarehouseSettings::find('all', $options);
            $note = $_POST['note'];
            if (sizeof($settings) == 0) {
                $token = generateTokenWithTime();

                $postData = [
                    "token" => $token,
                    "shop_id"=>$shopID,
                    "expire_date_id"=>$expireDateId,
                    "noter"=>$note
                ];
                $res = WarehouseSettings::createFiles($postData);
            } else {
                $id = $settings[0]->id;
                $data = [
                    "id"=>$id,
                    "noter"=>$note
                ];
                $res = WarehouseSettings::updateFiles($data);
            }
            response::success(json_encode($res));
        }
    }
    public function readStatus()
    {
        if (isset($_POST['shop_id'])) {
            $shopID = $_POST['shop_id'];
            $expireDateId = isset($_POST['expire_date_id']) ? $_POST['expire_date_id'] : null;

            // Check if this is a cardshop
            $cardshopCheckSql = "SELECT shop_id FROM cardshop_settings WHERE shop_id = ".$shopID;
            $cardshopCheckResult = Dbsqli::setSql2($cardshopCheckSql);
            $isCardshop = $cardshopCheckResult && $cardshopCheckResult->num_rows > 0;

            if ($isCardshop) {
                // For cardshops - ensure all delivery date records exist
                $sql = "SELECT * FROM cardshop_expiredate WHERE shop_id = ".$shopID;
                $cardshopExpireDatesResult = Dbsqli::setSql2($sql);

                if($cardshopExpireDatesResult && $cardshopExpireDatesResult->num_rows > 0) {
                    while($cardshopDate = $cardshopExpireDatesResult->fetch_assoc()) {
                        $expireDateSql = "SELECT * FROM expire_date WHERE id = ".$cardshopDate['expire_date_id']." AND is_delivery != 1";
                        $expireDateResult = Dbsqli::setSql2($expireDateSql);
                        if($expireDateResult && $expireDateResult->num_rows > 0) {
                            // Check if record exists
                            $checkSql = "SELECT id FROM warehouse_settings WHERE shop_id = ".$shopID." AND expire_date_id = ".$cardshopDate['expire_date_id'];
                            $existingResult = Dbsqli::setSql2($checkSql);
                            if (!$existingResult || $existingResult->num_rows == 0) {
                                $insertSql = "INSERT INTO warehouse_settings (shop_id,expire_date_id,token) values (".$shopID.",".$cardshopDate['expire_date_id'].",'".generateTokenWithTime()."')";
                                Dbsqli::setSql2($insertSql);
                            }
                        }
                    }
                }

                // Now get the requested record
                if ($expireDateId) {
                    $options = array('shop_id' => $shopID, 'expire_date_id' => $expireDateId);
                } else {
                    // If no expire_date_id specified for cardshop, return empty
                    response::success(json_encode([]));
                    return;
                }
            } else {
                // For normal shops - original logic
                $options = array('shop_id' => $shopID);
            }

            $settings = WarehouseSettings::find('all', $options);
            if(!$settings && !$isCardshop){
                // Only create record for normal shops if not exists
                $sql = "INSERT INTO warehouse_settings (shop_id,token) values (".$shopID.",'".generateTokenWithTime()."')";
                $res = Dbsqli::setSql2($sql);
                $settings = WarehouseSettings::find('all', $options);
            }
            response::success(json_encode($settings));
        }
    }

    public function replace(){
        if (isset($_POST['token']) && isset($_FILES['file'])) {
            $token = $_POST['token'];
            $options = array('token' => $token);
            $wfile = WarehouseFiles::find('all', $options);
            if(sizeof($wfile) == 0){
                echo "Filen eksisterer ikke!";
                return;
            }
            $id = $wfile[0]->id;
            $data = [
                "id"=>$id,
                "active"=>0
            ];
            $shopID = $wfile[0]->shop_id ;
            WarehouseFiles::updateFiles($data);

            $target_dir = "upload/warehouse/";
            $file = $_FILES['file'];

            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $realFilename = basename($file['name']);
            $fileSize = $file['size'];
            $newToken = generateTokenWithTime();
            $filename  = $newToken.".".$extension;
            $target_file = $target_dir .$filename;
            $currentDateTime = date('Y-m-d H:i:s');

            if (move_uploaded_file($file['tmp_name'], $target_file)) {
                $postData = [
                    "filename"=> $filename,
                    "file_size"=> $fileSize,
                    "extension" =>$extension,
                    "token"=>$newToken,
                    "replace_file_time"=>$currentDateTime,
                    "replace_file"=>$token,
                    "real_filename"=> $realFilename,
                    "shop_id"=> $shopID,
                    "user_id"=>40
                ];

                // print_R($postData);  // Uncomment this line if you need to debug
                $res = WarehouseFiles::createFiles($postData);
                response::success(json_encode($res));
            } else {
                echo json_encode(['status' => 'error']);
            }
        }
    }



    public function deactive(){
        if (isset($_POST['token'])) {
            $token = $_POST['token'];

            $options = array('token' => $token,'active' =>1);
            $file = WarehouseFiles::find('all', $options);
            if(sizeof($file) == 0){
                echo "Filen eksisterer ikke!";
                return;
            }
            $id = $file[0]->id;
            $data = [
                "id"=>$id,
                "active"=>0
            ];
            $res = WarehouseFiles::updateFiles($data);
            response::success(json_encode($res));
        } else {
            echo json_encode(['status' => 'error']);
        }
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

    public function multiFileupload()
    {
        // Same as before
        $target_dir = "upload/warehouse/";

        if (!empty($_FILES['files'])) {
            $total = count($_FILES['files']['name']);
            $shopID = $_GET["shop_id"];
            $expireDateId = isset($_GET["expire_date_id"]) ? $_GET["expire_date_id"] : null;

            for ($i = 0; $i < $total; $i++) {
                $extension = pathinfo(basename($_FILES["files"]["name"][$i]), PATHINFO_EXTENSION);
                $realFilename = basename($_FILES["files"]["name"][$i]);
                $fileSize = $_FILES['files']['size'][$i];

                $token = generateTokenWithTime();
                $filename  = $token.".".$extension;
                $target_file = $target_dir .$filename;
                if (move_uploaded_file($_FILES["files"]["tmp_name"][$i], $target_file)) {
                    //  echo "The file " . basename($_FILES["files"]["name"][$i]) . " has been uploaded.<br/>";
                    $postData = [
                        "filename"=> $filename,
                        "file_size"=> $fileSize,
                        "extension" =>$extension,
                        "token"=>$token,
                        "real_filename"=> $realFilename,
                        "shop_id"=> $shopID,
                        "expire_date_id"=> $expireDateId,
                        "user_id"=>40
                    ];
                    $res = WarehouseFiles::createFiles($postData);

                } else {
                    echo "Sorry, there was an error uploading " . basename($_FILES["files"]["name"][$i]) . ".<br/>";
                }
            }
        } else {
            echo "No files were uploaded.";
        }
        response::success(json_encode([]));
    }
    public function createNewFile($postdata)
    {
        $res = WarehouseFiles::createFiles($postdata);
        response::success(json_encode($res));
    }
    public function readByShop(){
        $shopID = $_POST["shop_id"];
        $expireDateId = isset($_POST['expire_date_id']) ? $_POST['expire_date_id'] : null;

        // Use original logic for normal shops, extend for cardshops
        $options = array('shop_id' => $shopID,'active' =>1);
        if ($expireDateId) {
            $options['expire_date_id'] = $expireDateId;
        }

        $WarehouseFiles = WarehouseFiles::find('all', $options);

        response::success(json_encode($WarehouseFiles));
    }

    public function getDeliveryDates(){
        if (isset($_POST['shop_id'])) {
            $shopID = $_POST['shop_id'];

            // Check if shop is a cardshop
            $sql = "SELECT shop_id FROM cardshop_settings WHERE shop_id = ".$shopID;
            $cardshopCheck = Dbsqli::setSql2($sql);

            if(!$cardshopCheck || $cardshopCheck->num_rows == 0){
                response::success(json_encode(['is_cardshop' => false, 'delivery_dates' => []]));
                return;
            }

            // Get delivery dates for cardshop
            $sql = "SELECT * FROM cardshop_expiredate WHERE shop_id = ".$shopID;
            $cardshopExpireDatesResult = Dbsqli::setSql2($sql);

            $deliveryDates = [];
            if($cardshopExpireDatesResult && $cardshopExpireDatesResult->num_rows > 0) {
                while($cardshopDate = $cardshopExpireDatesResult->fetch_assoc()) {
                    $expireDateSql = "SELECT * FROM expire_date WHERE id = ".$cardshopDate['expire_date_id']." AND is_delivery != 1";
                    $expireDateResult = Dbsqli::setSql2($expireDateSql);
                    if($expireDateResult && $expireDateResult->num_rows > 0) {
                        $expireDate = $expireDateResult->fetch_assoc();
                        $deliveryDates[] = $expireDate;
                    }
                }
            }

            response::success(json_encode(['is_cardshop' => true, 'delivery_dates' => $deliveryDates]));
        }
    }
}

function generateTokenWithTime() {
    // Generate a random string as the base for our token
    $randomString = bin2hex(random_bytes(16));

    // Get the current timestamp
    $timestamp = time();

    // Concatenate the random string and the timestamp
    $token = $randomString . $timestamp;

    // Base64 encode the token to make it URL safe
    $base64Token = base64_encode($token);

    return $base64Token;
}