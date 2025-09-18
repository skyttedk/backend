<?php
// Controller PresentReservation
// Date created  Mon, 05 Sep 2016 20:36:22 +0200
// Created by Bitworks
class reservationApprovalController Extends baseController {
    public function Index() {
    }
    public function saveReservation(){
        // Start transaction

        try {
            // Always apply approval logic since this controller is only called for shop 9808
            $validation_result = $this->validateReservationForApproval($_POST);

            if ($validation_result['requires_approval']) {
                // Create approval request and send email
                $group_token = $this->createApprovalRequest($_POST, $validation_result);

                // Set shop_metadata.stock_qty_approved = 0
                $this->updateShopApprovalStatus($_POST['shop_id'], 0);

                // Return approval required status
                response::success(json_encode([
                    'status' => 'requires_approval',
                    'group_token' => $group_token,
                    'message' => 'Ændringerne er sendt til godkendelse.'
                ]));
                
            }

            // Normal flow - no approval needed
            $hasP = PresentReservation::find_by_sql("SELECT * FROM `present_reservation` WHERE `shop_id` = ".$_POST["shop_id"]." AND `present_id` = ".$_POST["present_id"]." AND `model_id` = ".$_POST["model_id"]);
            if(count($hasP) > 0){
                $presentreservation = PresentReservation::updatePresentReservation ($_POST);
            } else {
                $presentreservation = PresentReservation::createPresentReservation ($_POST);
            }

            if($_POST["do_close"] == 1){
                $sql = "UPDATE `present_reservation` set `is_close` = 0 WHERE `model_id` = ".$_POST["model_id"];
                Dbsqli::SetSql2($sql);
            }
            
            
            response::success(make_json("presentreservation", $presentreservation));
            
        } catch (Exception $e) {
            
            response::error("Error processing reservation: " . $e->getMessage());
        }
    }

    private function validateReservationForApproval($data) {
        $result = ['requires_approval' => false, 'items' => []];

        // Get shop information for language
        $shop = Shop::find($data['shop_id']);
        $language_id = $shop->localisation ?? 1; // Default to Danish

        // Get present model information
        $present_model = PresentModel::find_by_sql("
            SELECT pm.*, pm.model_present_no as itemno
            FROM present_model pm 
            WHERE pm.model_id = " . intval($data['model_id']) . "
            AND pm.present_id = " . intval($data['present_id']) . "
            AND pm.language_id = " . intval($language_id)
        );
        
        if (empty($present_model)) {
            $log = new SystemLog();
            $log->controller = 'reservationApprovalController';
            $log->action = 'validateReservationForApproval';
            $log->data = "No present model found for present_id: " . $data['present_id'] . ", model_id: " . $data['model_id'];
            $log->save();
            return $result;
        }

        $itemno = $present_model[0]->itemno;
        $log = new SystemLog();
        $log->controller = 'reservationApprovalController';
        $log->action = 'validateReservationForApproval';
        $log->data = "Item number: " . $itemno;
        $log->save();
        
        // Check if item is external - first check if item exists at all
        $navision_item_all = NavisionItem::find_by_sql("
            SELECT * FROM navision_item WHERE no = '" . $itemno . "'
        ");

        // Now check with language and deleted filters
        $navision_item = NavisionItem::find_by_sql("
            SELECT * FROM navision_item
            WHERE no = '" . $itemno . "'
            AND language_id = " . intval($language_id) . "
            AND (deleted = 0 OR deleted IS NULL)
        ");

        if (empty($navision_item)) {
            return $result; // No approval needed for non-Navision items
        }

        if ($navision_item[0]->is_external == 1) {
            return $result;
        }
        
        // Get current reservation
        $current_reservation = PresentReservation::find_by_sql("
            SELECT quantity FROM present_reservation 
            WHERE shop_id = " . intval($data['shop_id']) . "
            AND present_id = " . intval($data['present_id']) . "
            AND model_id = " . intval($data['model_id'])
        );
        
        $current_qty = $current_reservation ? $current_reservation[0]->quantity : 0;
        $new_qty = intval($data['quantity']);
        
        // Get current NAV stock (reuse the same logic as UI "Tilgængelige gaver" column)
        $nav_stock = $this->getCurrentNavStock($itemno, $language_id);

        // Calculate if the total reserved quantity would exceed available stock
        // Logic: Available stock - Total reserved quantity < 0 means we need approval
        $stock_after_reservation = $nav_stock - $new_qty;

        $log = new SystemLog();
        $log->controller = 'reservationApprovalController';
        $log->action = 'validateReservationForApproval';
        $log->data = "Stock calculation: nav_stock=$nav_stock, current_qty=$current_qty, new_qty=$new_qty, stock_after_reservation=$stock_after_reservation";
        $log->save();

        if ($stock_after_reservation < 0) {
            $result['requires_approval'] = true;
            $result['items'][] = [
                'itemno' => $itemno,
                'nav_stock' => $nav_stock,
                'requested_qty' => $new_qty,
                'is_external' => 0
            ];
        }

        // Check if this item had previous approval records that should be cleaned up
        // If the new reservation doesn't require approval, remove any existing approval records for this item
        if (!$result['requires_approval']) {
            $this->cleanupApprovalRecords($data['shop_id'], $itemno);
        }

        return $result;
    }
    
    private function getCurrentNavStock($itemno, $language_id) {
        // Use the exact same logic as getStockStatus() method in approval controller
        $sql = "SELECT * FROM navision_stock_total
                WHERE itemno = '" . $itemno . "'
                AND language_id = " . intval($language_id);
        $stock = NavisionStockTotal::find_by_sql($sql);

        return $stock ? intval($stock[0]->stock_available ?? 0) : 0;
    }

    private function cleanupApprovalRecords($shop_id, $itemno) {
        // Delete any existing approval records for this shop and item
        $sql = "DELETE FROM present_reservation_qty_approval
                WHERE shop_id = " . intval($shop_id) . "
                AND itemno = '" . $itemno . "'";
        $result = Dbsqli::SetSql2($sql);

        if ($result) {
            // Log the cleanup attempt
            $log = new SystemLog();
            $log->controller = 'reservationApprovalController';
            $log->action = 'cleanupApprovalRecords';
            $log->data = "Executed cleanup query for shop_id: " . $shop_id . ", itemno: " . $itemno;
            $log->save();

            // Check if there are any remaining approval records for this shop
            $remaining = PresentReservationQtyApproval::find_by_sql("
                SELECT COUNT(*) as count FROM present_reservation_qty_approval
                WHERE shop_id = " . intval($shop_id)
            );

            // If no approval records remain, set shop status to approved (1)
            if (empty($remaining) || $remaining[0]->count == 0) {
                $this->updateShopApprovalStatus($shop_id, 1);

                $log = new SystemLog();
                $log->controller = 'reservationApprovalController';
                $log->action = 'cleanupApprovalRecords';
                $log->data = "All approval records cleared for shop_id: " . $shop_id . " - Setting status to approved";
                $log->save();
            }
        }
    }

    private function createApprovalRequest($data, $validation_result) {
        $shop_id = $data['shop_id'];
        $item = $validation_result['items'][0]; // We only have one item in this flow
        $itemno = $item['itemno'];

        // Check if approval record already exists for this item
        $existing_approval = PresentReservationQtyApproval::find_by_sql("
            SELECT * FROM present_reservation_qty_approval
            WHERE shop_id = " . intval($shop_id) . "
            AND itemno = '" . $itemno . "'
        ");

        if (!empty($existing_approval)) {
            // Update existing approval record
            $approval = $existing_approval[0];
            $group_token = $approval->group_token;

            $sql = "UPDATE present_reservation_qty_approval
                    SET nav_stock = " . intval($item['nav_stock']) . ",
                        requested_qty = " . intval($item['requested_qty']) . "
                    WHERE shop_id = " . intval($shop_id) . "
                    AND itemno = '" . $itemno . "'";
            Dbsqli::SetSql2($sql);

            $log = new SystemLog();
            $log->controller = 'reservationApprovalController';
            $log->action = 'createApprovalRequest';
            $log->data = "Updated existing approval record for shop_id: " . $shop_id . ", itemno: " . $itemno . ", new qty: " . $item['requested_qty'];
            $log->save();

            // Email will be sent at the end with all items for this group

        } else {
            // Create new approval record
            $group_token = $this->generateUUID();

            // Get shop metadata for salesperson code
            $shop_metadata = ShopMetadata::find_by_sql("
                SELECT salesperson_code FROM shop_metadata
                WHERE shop_id = " . intval($shop_id)
            );
            $salesperson_code = $shop_metadata ? $shop_metadata[0]->salesperson_code : null;

            // Debug log to check salesperson_code
            $log_debug = new SystemLog();
            $log_debug->controller = 'reservationApprovalController';
            $log_debug->action = 'createApprovalRequest';
            $log_debug->data = "Debug: shop_id=$shop_id, shop_metadata_found=" . (count($shop_metadata) > 0 ? 'yes' : 'no') . ", salesperson_code=" . ($salesperson_code ?? 'null');
            $log_debug->save();

            $shop = Shop::find($shop_id);
            $language_id = $shop->localisation ?? 1;

            // Create new approval entry
            PresentReservationQtyApproval::create_approval_request(
                $group_token,
                $shop_id,
                $salesperson_code,
                $language_id,
                $validation_result['items']
            );

            $log = new SystemLog();
            $log->controller = 'reservationApprovalController';
            $log->action = 'createApprovalRequest';
            $log->data = "Created new approval record for shop_id: " . $shop_id . ", itemno: " . $itemno . ", qty: " . $item['requested_qty'];
            $log->save();

            // Email will be sent at the end with all items for this group
        }

        // Mark that we need to send an email, but delay it to allow batching
        $this->scheduleApprovalEmail($shop_id);

        return $group_token;
    }

    private function scheduleApprovalEmail($shop_id) {
        // Store the timestamp and shop_id in a session or temporary storage
        // This will prevent multiple emails from being sent in quick succession
        $current_time = time();
        $last_email_key = "last_approval_email_" . $shop_id;

        // Check if we recently sent an email for this shop (within last 5 seconds)
        if (isset($_SESSION[$last_email_key]) && ($current_time - $_SESSION[$last_email_key]) < 5) {
            // Too recent, skip sending email
            return;
        }

        // Mark the current time and send the email
        $_SESSION[$last_email_key] = $current_time;
        $this->sendGroupedApprovalEmail($shop_id);
    }

    private function sendGroupedApprovalEmail($shop_id) {
        // Get all current approval records for this shop
        $approval_records = PresentReservationQtyApproval::find_by_sql("
            SELECT * FROM present_reservation_qty_approval
            WHERE shop_id = " . intval($shop_id) . "
            ORDER BY itemno ASC
        ");

        if (empty($approval_records)) {
            return; // No approval records to send
        }

        $group_token = $approval_records[0]->group_token;
        $items = [];

        foreach ($approval_records as $record) {
            $items[] = [
                'itemno' => $record->itemno,
                'nav_stock' => $record->nav_stock,
                'requested_qty' => $record->requested_qty
            ];
        }

        $this->sendApprovalEmail($group_token, $shop_id, $items);
    }

    private function sendApprovalEmail($group_token, $shop_id, $items) {
        $shop = Shop::find($shop_id);

        // Get salesperson information
        $shop_metadata = ShopMetadata::find_by_sql("
            SELECT salesperson_code FROM shop_metadata
            WHERE shop_id = " . intval($shop_id)
        );

        $salesperson_info = "Ikke angivet";
        if ($shop_metadata && !empty($shop_metadata[0]->salesperson_code)) {
            $salesperson_code = $shop_metadata[0]->salesperson_code;

            // Try to get salesperson name from SystemUser table
            $system_user = SystemUser::find_by_sql("
                SELECT name, email FROM system_user
                WHERE salespersoncode = '" . $salesperson_code . "'
                LIMIT 1
            ");

            if ($system_user && !empty($system_user)) {
                $salesperson_info = $system_user[0]->name . " (" . $salesperson_code . ")";
            } else {
                $salesperson_info = $salesperson_code;
            }
        }

        $subject = "Reservation kræver godkendelse – Shop #" . $shop_id;

        $body = "<html><body>";
        $body .= "<h3>Reservation kræver godkendelse</h3>";
        $body .= "<p><strong>Shop:</strong> " . $shop->name . " (ID: " . $shop_id . ")</p>";
        $body .= "<p><strong>Sælger:</strong> " . $salesperson_info . "</p>";
        $body .= "<table border='1' cellpadding='5' cellspacing='0'>";
        $body .= "<tr><th>Varenummer</th><th>Aktuelt NAV-lager</th><th>Ønsket reserveret antal</th></tr>";
        
        foreach ($items as $item) {
            $body .= "<tr>";
            $body .= "<td>" . $item['itemno'] . "</td>";
            $body .= "<td>" . $item['nav_stock'] . "</td>";
            $body .= "<td>" . $item['requested_qty'] . "</td>";
            $body .= "</tr>";
        }
        
        $body .= "</table>";
        $body .= "</body></html>";
        
        $mail_data = [
            'sender_email' => 'system@gavefabrikken.dk',
            'recipent_email' => 'kss@gavefabrikken.dk',
            'subject' => $subject,
            'body' => $body,
            'mailserver_id' => 1
        ];
        
        MailQueue::createMailQueue($mail_data);
    }
    
    private function updateShopApprovalStatus($shop_id, $status) {
        // Update shop_metadata record (should already exist for valid shops)
        $sql = "UPDATE shop_metadata SET stock_qty_approved = " . intval($status) . " WHERE shop_id = " . intval($shop_id);
        $result = Dbsqli::SetSql2($sql);        
    }
    
    private function generateUUID() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }




    public function create() {
        $presentreservation = PresentReservation::createPresentReservation ($_POST);
        response::success(make_json("presentreservation", $presentreservation));
    }
    public function read() {
        $presentreservation = PresentReservation::readPresentReservation ($_POST['id']);
        response::success(make_json("presentreservation", $presentreservation));
    }
    public function update() {
        $presentreservation = PresentReservation::updatePresentReservation ($_POST);
        response::success(make_json("presentreservation", $presentreservation));
    }
    public function delete() {
        $presentreservation = PresentReservation::deletePresentReservation ($_POST['id'],true);
        response::success(make_json("presentreservation", $presentreservation));
    }
    //Create Variations of readAll
    public function readAll() {
        $presentreservations = PresentReservation::all();
        //$options = array('only' => array('id', 'name', 'username', 'admin', 'active'));
        $options = array();
        response::success(make_json("presentreservations", $presentreservations, $options));
    }
//---------------------------------------------------------------------------------------
// Custom Controller Actions
//---------------------------------------------------------------------------------------

    public function getAllReservations() {
       $presentreservations = PresentReservation::all();
       foreach($presentreservations as $presentreservation) {
           $current_level =   ($presentreservation->quantity *  $presentreservation->warning_level) /100;
           $presentreservation->current_level = $current_level;
           if($current_level>=$presentreservation->warning_level) {
               $presentreservation->warning_issued = 1;
           }
           $presentreservation->save();
       }
       response::success(make_json("presentreservation", $presentreservation));
    }

    // hent resercatio p� en gave
    public function getPresentReservations() {
       $presentreservation = PresentReservation::all(array('present_id' => $_POST['present_id']));
       response::success(make_json("presentreservation", $presentreservation));
    }

    // hent alle reservationer
    public function getShopReservations() {
          $presentreservations = PresentReservation::all(array('shop_id' => $_POST['shop_id']));
          $i = 0;
          foreach($presentreservations as $presentreservation ) {
            $presentreservations[$i]->order_quantity = $presentreservation->ordercount();
          }
          response::success(make_json("presentreservation", $presentreservations));
    }

    public function test() {
        //TODO: funktionen skal kobles ind i ordre controller
        //      der skal afklares om det er pr. shop
        //Skal kaldes efter at der er dannes en ordre

       $dummy = [];

       //Vi skal have model id med over p� gavevalg. hvis vu skal bruge den

       $presentreservation = PresentReservation::hasReservation($_POST['shop_id'],$_POST['present_id'],$_POST['model_id']);
       if(isset($presentreservation)) {
          if($presentreservation->warning_issued==0) {
              // skal hewnte mode_no fra tabel,, da vi ikke har id p� order tabellen endnu
            $ordercount =  Order::countPresentOnOrders($_POST['shop_id'],$_POST['present_id'],$_POST['model_no']);
            $present = Present::find($_POST['present_id']);
            $shop = Shop::find($_POST['shop_id']);

            $current_level =   ($presentreservation->quantity *  $presentreservation->warning_level) /100;

            if($current_level>=$presentreservation->warning_level) {

                  $maildata = [];
                  $maildata['sender_email'] = 'info@gavefabrikken.dk';
                  $maildata['recipent_email'] ='sigurd.skytte@gmail.com';
                  $maildata['subject']= 'Reservationsadvarsel';
                  $body = '<html><head></head><body>';
                  $body.='<h4>Reservationsadvarsel</h4><br>';
                  $body.='shop:'.$shop->name.'<br>';
                  $body.='gave:'.$present->name.'<br>';
                  $body.='modelnr.:'.$_POST['model_no'].'<br>';
                  $body.='Antal p� ordre:'.$ordercount.'<br>';
                  $body.='Advarsel ved:'.$current_level.'<br>';
                  $body.= '</body></html>';
                  $maildata['body'] = $body;
                  $maildata['mailserver_id'] = 1;
                  MailQueue::createMailQueue($maildata);
                  $presentreservation->warning_issued = 1;
                  $presentreservation->save();
            }
          }
       }
      response::success(json_encode($dummy));
    }
    public function scheduleHandler(){
        $dummy = [];
        // hent alle gaver pr shops
        //$join = 'LEFT JOIN shop ON(shop.id = present_reservation.shop_id)';
        //$PresentReservation = PresentReservation::find('all',array('joins' => $join,'conditions' => array('shop.is_demo = ? AND shop.is_gift_certificate = ? AND active = ? AND deleted = ?',0,0,1,0),'having'=>'quantity > 250'));

        $presentToClose =   PresentReservation::find_by_sql("
        select present_reservation.*,`orderNy`.c orderCount from present_reservation
            inner join shop ON present_reservation.shop_id = shop.id
            inner join (
                SELECT `present_id`,`present_model_id`,`shop_id` ,count(id) c FROM `order`  GROUP by `present_id`,`present_model_id`
                ) `orderNy` ON present_reservation.shop_id = `orderNy`.shop_id
            where
                shop.is_demo = 0 AND
                shop.is_gift_certificate = 0 AND
                shop.active = 1 AND
                shop.deleted = 0
            HAVING present_reservation.quantity > 0 and ((orderCount * (warning_level /100)) > quantity )");

      /*
      foreach($shops as $shop ) {
            echo $shop->id;


        }
        */

        response::success(json_encode($presentToClose));
        // tjek om en gave har overskredet reservation kritterierne


        // tjek om det er en model eller gaver uden modeller


        // luk gave, hvis den ikke er lukket


        // tjek om der er erstatningsgave og �ben hvis den er lukket

//             response::success(make_json("presentreservation", $presentreservation));




    }
    public function closeReservationExceed()
    {


     $presentToClose =   PresentReservation::find_by_sql("
        select present_reservation.*,`orderNy`.c as order_count, `orderNy`.present_id as present_ID ,shop.soft_close,shop.id, shop.name,shop.localisation ,shop.rapport_email from present_reservation
            inner join shop ON present_reservation.shop_id = shop.id
            inner join (
                SELECT `present_id`,`present_model_id`,`shop_id` ,count(id) c FROM `order`  GROUP by `present_id`,`present_model_id`
                ) `orderNy` ON present_reservation.model_id = `orderNy`.present_model_id
                and
                    present_reservation.`present_id` = `orderNy`.`present_id`
                and
                    shop.is_demo = 0 AND
                    shop.is_gift_certificate = 0 AND
                    shop.active = 1 AND
                    shop.deleted = 0 and
                   
                    shop.soft_close = 0 and
                    do_close = 1 and
                    is_close = 0
                  HAVING present_reservation.quantity > 0 and order_count >= quantity limit 1");


            //Dbsqli::SetSql2("INSERT into mail_queue (mailserver_id,   sender_name,sender_email ,recipent_name  ,recipent_email ,subject,body) VALUES( 4, 'Gavefabrikken','Gavefabrikken@gavefabrikken.dk','','us@gavefabrikken.dk','closeReservationExceed','" . $html . "' )");
          //     print_r($presentToClose);
            foreach($presentToClose as $model){
                 $groupid = rand(1, 30000);
                 $subject = "closeReservationExceed_".$groupid;
                 $body = "<html><body><pre><code>".json_encode($model)."</code></pre></body></html>";
                 Dbsqli::SetSql2("INSERT into mail_queue (mailserver_id,   sender_name,sender_email ,recipent_name  ,recipent_email ,subject,body) VALUES( 4, 'Gavefabrikken','Gavefabrikken@gavefabrikken.dk','','us@gavefabrikken.dk','".$subject."','".$body. "' )");

                     // total fejl men kan ikke rettet n�r active = 0, s� betyder det modsat, at den er aktive
                     // set active = 0 deactive model
                    $body = " <br><br>";
                    $body.= "update `present_model` set active = 1  where `model_id` = ".$model->model_id;
                    $body.= "<br><br>";
                    $body.= "SELECT * FROM `present_model` WHERE `present_id` = ".$model->present_id." AND `language_id` = 1 AND `active` = 1";
                       // hvis rs er = size 0, s� betyder det at present har alle modeller lukket og hele gaven skal lukkes
                    $body.= "<br><br>";
                    $body.= "update `shop_present` set active = 0 WHERE `present_id` = ".$model->present_id;
                    $body.= "<br><br><hr>";
                    $body.= "UPDATE `present_reservation` set `is_close` = 1 WHERE `model_id` = ".$model->present_id;

                    Dbsqli::SetSql2("INSERT into mail_queue (mailserver_id,   sender_name,sender_email ,recipent_name  ,recipent_email ,subject,body) VALUES( 4, 'Gavefabrikken','Gavefabrikken@gavefabrikken.dk','','us@gavefabrikken.dk','".$subject."','".$body. "' )");

                    $sql = "update `present_model` set active = 1  where `model_id` = ".$model->model_id;
                    Dbsqli::SetSql2($sql);
                    $sql = "SELECT * FROM `present_model` WHERE `present_id` = ".$model->present_id." AND `language_id` = 1 AND `active` = 0";
                    $rsModel =  Dbsqli::getSql2($sql);
                    if(sizeofgf($rsModel) == 0){
                       $sql = "update `shop_present` set active = 0 WHERE `present_id` = ".$model->present_id;
                       Dbsqli::SetSql2($sql);
                    }
                    $sql = "UPDATE `present_reservation` set `is_close` = 1 WHERE `model_id` = ".$model->model_id;
                    Dbsqli::SetSql2($sql);
                    if($model->rapport_email != ""){
                        $this->reservationNotification($model->name,$model->present_id,$model->model_id,$model->rapport_email);
                    }

            }
            print_r($presentToClose);
            echo "done22";
    }
    public function  reservationNotification($shopname, $presentID, $modelID,$email)
    {
         $sql = "SELECT * FROM `present_model` WHERE `model_id` = ".$modelID." AND `language_id` = 1";
         $rsModel =  Dbsqli::getSql2($sql);

         $sql = "SELECT * FROM `present` WHERE `id` = ".$presentID;
         $rsPresent =  Dbsqli::getSql2($sql);
//            <img width=300 src='".$rsModel[0]["media_path"]."'/>
         $body = "
           <div><b>Gave lukket i valgshop</b></div><br>
           <table width=500 cellspacing=5 cellpadding=5 border=1>
           <tr><td width=100>Shop: </td><td >".utf8_encode($shopname)."</td></tr>
           <tr><td>Gave: </td><td>".utf8_encode($rsPresent[0]["nav_name"])."</td></tr>
           <tr><td>Model: </td><td>".utf8_encode($rsModel[0]["model_name"])." - ".utf8_encode($rsModel[0]["model_no"])."</td></tr>
           <tr><td>Varenr: </td><td>".$rsModel[0]["model_present_no"]."</td></tr>
           </table><br>
            <img width=300 src=\"".$rsModel[0]["media_path"]."\"/>
         ";
         $subject = "Gave lukket i valgshop: ".utf8_encode($shopname);

         $html="<html><body>".$body."</body></html>";
         Dbsqli::SetSql2("INSERT into mail_queue (mailserver_id,   sender_name,sender_email ,recipent_name  ,recipent_email ,subject,body) VALUES( 4, 'Gavefabrikken','Gavefabrikken@gavefabrikken.dk','','".$email."','".$subject."','".$html. "' )");
    }



//            HAVING present_reservation.quantity > 0 and ((orderCount * (warning_level /100)) > quantity )");

 }
?>

