<?php

namespace GFUnit\apps\autopilot;
use GFBiz\units\UnitController;
use GFCommon\Model\Navision\MagentoWS;
class Controller extends UnitController
{
    private $magentoClient;
    private $totalretsamCounter = 0;
    private $errorsamCounter = 0;
    private $sqlTorun = [];
    public function __construct()
    {
        parent::__construct(__FILE__);
    }

    /* status_stats: show  */
    public function panel()
    {
        $this->view("panel");
    }
    public function autopanel()
    {
        $this->view("autopanel");
    }
    public function testgetMaxSam()
    {
        //https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/apps/autopilot/testgetMaxSam&sku=SAM4224
        $this->magentoClient = new MagentoWS();
       echo $sku = $_GET["sku"];
        $this->getMaxSam($sku,true);
    }
    public function dd()
    {

        $resultat = AutoCompany::makeShopList();
        echo implode(",", $resultat);


    }


    public function retsam()
    {
        $this->magentoClient = new MagentoWS();
        $noError = [];
        // under 500
        //$shopList = [6577, 7728, 6837, 6548, 6964, 7284, 7607, 7758, 6965, 6821, 7711, 7788, 6534, 7929, 7444, 6569, 6766, 7522, 7853, 6905, 6505, 7147, 7850, 6470, 6797, 6142, 7387, 6819, 7419, 7784, 6249, 6994, 7012, 7190, 6842, 6417, 6522, 7391, 6410, 7525, 6353, 6687, 7804, 7460, 6771, 7880, 7426, 7092, 6879, 6849, 7154, 7160, 7710, 7495, 6159, 7755, 6897, 7019, 6448, 7365, 7193, 6650, 7337, 7581, 7554, 7697, 7698, 6545, 7860, 6597, 6584, 7102, 7583, 6519, 7540, 6698, 7569, 6578, 7252, 6827, 6910, 7398, 7341, 7136, 7181, 6255, 7732, 6646, 6576, 7417, 6722, 7312, 6800, 7827, 7165, 7587, 6239, 6946, 7288, 7553, 7397, 6952, 6472, 7014, 7503, 7656, 6903, 6861, 7227, 7782, 7595, 6951, 6352, 7425, 7388, 7038, 6603, 6513, 6583, 7668, 6767, 7487, 6563, 7146, 6806, 7216, 7228, 6930, 6541, 7496, 6911, 7343, 6254, 7866, 7299, 6882, 6708, 6754, 7044, 7987, 6667, 6883, 6953, 6458, 6769, 7620, 7541, 6852, 7031, 7009, 6715, 7677, 6999, 7021, 7371, 7110, 7054, 7179, 6628, 6427, 7593, 6845, 6491, 7353, 6791, 6728, 6560, 7408, 7423, 7474, 7037, 6834, 7301, 7450, 7178, 7304, 7049, 6665, 6538, 6941, 6721, 6656, 6412, 7430, 7115, 6727, 6383, 6898, 6739, 7533, 7010, 6890, 6985, 7018, 6561, 6449, 6889, 7348, 6859, 7080, 7079, 6838, 7266, 6189, 7003, 7457, 7748, 7253, 7051, 7532, 6467, 6955, 6775, 6248, 6517, 6527, 6318, 6741, 6641, 6668, 6388, 6787, 7483, 6554, 6725, 7888, 6488, 7917, 6919, 6495, 7242, 7159, 7614, 6853, 6674];
        //$shopList = [6168, 6278, 6279, 6287, 6293, 6351, 6391, 6406, 6413, 6414, 6419, 6539, 6604, 6609, 6613, 6615, 6666, 6671, 6744, 6750, 6760, 6770, 6782, 6795, 6802, 6885, 6939, 6978, 7116, 7157, 7394, 7442, 7475, 7521, 7582, 7740];

        foreach($shopList as $shopID){
            $hasError = false;
            $shopRes = $this->checkIfShopIsClose($shopID);

            if (empty($shopRes)) {
                echo "sadfasd";
                continue;
            }

            // henter alle sam
            $sql = "SELECT * FROM `present_model` WHERE `present_id` in (
                        SELECT id FROM `present` WHERE `shop_id` = ".$shopID."
                        ) and language_id = 1 and is_deleted = 0 and `model_present_no` LIKE 'sam%'";
            $samList = \PresentModel::find_by_sql($sql);
            foreach ($samList as $sam){
                $this->totalretsamCounter++;
                $stockavailable = $this->getMaxSam($sam->model_present_no);

                if(!$stockavailable) {

                    echo "fejl:" . $sam->model_present_no . " - " . $shopID;
                    continue;
                }

                if($stockavailable < 0 ){
                    $do_close = 0;
                    $hasError = true;
                    $this->errorsamCounter++;
                    echo "<hr>";
                    echo "<br>------ problem ------- <br>";

                    echo $shopRes[0]->attributes["name"];
                    echo "<br>";
                    echo "sku: ".$sam->model_present_no;
                    echo "<br>";
                    echo "shopid: ".$shopID;
                    echo "<br>";
                    echo "modelid: ".$sam->model_id;

                    echo "<br>";
                    echo "stock: ".$stockavailable;
                    echo "<br>";
                    $orderRs =  $this->getPresentSelected($sam->model_id,$shopID);
                    echo "Antal valgte: ".$antalValgte = $orderRs[0]->attributes["antal"];
                    echo "<br>";
                    $backupResAmount = 0;
                    $currentResAmount = 0;
                    $resBackup = $this->retsam_getbackup($sam->model_id,$shopID);
                    if($resBackup){
                        echo "backup: ". $backupResAmount = $resBackup[0]->attributes["quantity"];
                    } else {

                        echo "fejlbackup:".$sam->model_present_no." - ".$shopID;
                        echo "<br>";
                        continue;
                    }
                    echo "<br>";
                    $resCurrent = $this->getCurrentReservation($sam->model_id,$shopID);
                    if($resCurrent){
                        $do_close = $resCurrent[0]->attributes["do_close"];
                        echo "current: ". $currentResAmount = $resCurrent[0]->attributes["quantity"];
                    } else{
                        echo "fejlcurrent:".$sam->model_present_no." - ".$shopID;
                        return;
                    }
                    echo "<br>";
                    $orderRs =  $this->getPresentSelected($sam->model_id,$shopID);
                 //   echo "antal valgte: ".$antalValgte = $orderRs[0]->attributes["antal"];
                    // current er større end backup

                    if($backupResAmount < $currentResAmount){

                        echo "<br>!!!!rettes til ".$backupResAmount."<br>";
                        echo $sam->model_present_no;
                        echo "<br>";

                        if( $backupResAmount == 0){
                            echo "<br>0000 bør sættes til overvågning <br>";

                            echo $sql3 = "UPDATE `present_reservation` SET `do_close` = 1 WHERE shop_id = ".$shopID." AND `model_id` = ".$sam->model_id.";";
                            $sqlTorun[] = $sql3;
                            continue;
                        }



                        // Antal valgte er mindre end eller lige med backup og vi kan sætte antal til backup
                        if($antalValgte <= $backupResAmount ){
                            echo "<br>";

                            $sql1 = "UPDATE `present_reservation` SET `do_close` = 1, `quantity` = ".$backupResAmount." WHERE shop_id = ".$shopID." AND `model_id` = ".$sam->model_id.";";
                            $sqlTorun[] =$sql1;
                            echo "<br>";
                        } else{
                        // antal valgte er større end backup og vi må sætte antal til antal valgte
                            echo "<br>##antal valgte er større end backup og vi må sætte antal til antal valgte<br>";

                           $sql2 = "UPDATE `present_reservation` SET `do_close` = 1, `quantity` = ".$antalValgte." WHERE shop_id = ".$shopID." AND `model_id` = ".$sam->model_id.";";
                            $sqlTorun[] = $sql2;
                            echo "<br>";
                        }





                    } else {
                        if($do_close == 1) {
                            echo "<br>Er sat til overvågning<br>";
                            continue;
                        }
                        echo "<br>bør sættes til overvågning <br>";

                        $sql3 = "UPDATE `present_reservation` SET `do_close` = 1 WHERE shop_id = ".$shopID." AND `model_id` = ".$sam->model_id.";";
                        $sqlTorun[] = $sql3;
                        echo "<br>";


                    }
                    echo "<hr>";
                }



            }
            if($hasError == false){
                $noError[] = $shopID;
            }

        }
        echo "<hr>";
        echo "Total: ".$this->totalretsamCounter;
        echo "<br>";
        echo "Totalerror: ".$this->errorsamCounter;
        echo "<br>";
        echo "change: ".sizeof($sqlTorun);
        echo "<br>";
        foreach ($sqlTorun as $value) {
            echo $value . "<br>";
        }
}
private function getShopNavn($shopID)
{

}
private function checkIfShopIsClose($shopID)
{
    $sql = "SELECT * FROM `shop` WHERE `id` = ".$shopID." AND `close_date` IS NULL AND `final_finished` = 0 ";
        return \Shop::find_by_sql($sql);
}
    private function getPresentSelected($model_id,$shopid){
        $sql = "SELECT DISTINCT count(id) as antal FROM `order` WHERE `present_model_id` = ".$model_id." and shop_id= ".$shopid;
        return \Order::find_by_sql($sql);
    }
    private function retsam_getbackup($model_id,$shopid)
    {
        $sql = "SELECT quantity FROM `present_reservation_1910` where model_id = ".$model_id." and shop_id =".$shopid;
        return \PresentModel::find_by_sql($sql);
    }
private function getCurrentReservation($model_id,$shopid){
    $sql = "SELECT quantity,do_close FROM `present_reservation` where model_id = ".$model_id." and shop_id =".$shopid;
    return \PresentModel::find_by_sql($sql);
}
private function getStuckFromNAV($sku,$lang=1)
{
        try {
            $available1 = intval($this->magentoClient->GetAvailableInventoryByType($sku, $lang));
        } catch (\Exception $e) {
            $available1 = false;
        }
        return $available1;
}
private function getMaxSam($samno,$debug=false)
{


    $result = [];
    $list = \NavisionBomitem::find('all', array(
        'conditions' => array(
            'parent_item_no = ? AND language_id = 1 AND `deleted` IS NULL',
            $samno
        )
    ));

    if(!$list) return false;
    if($debug == true){
       print_R($list);
    }
    foreach ($list as $item){
        if($this->navisionitem_isExternal($item->no))
        {
            $stock = 999;
        } else {
            $stock =  $this->getStuckFromNAV($item->no);
        }


        if($stock){
            if ($item->quantity_per != 0) {
               /*
                echo "<br>";
                echo $stock;
                echo "----";
                echo $item->quantity_per;
                echo "<br>";
                */
            $avaliable = ceil((intval($stock) / intval($item->quantity_per)));
            } else {
                $avaliable = 0;
            }
            $result[] = $avaliable;

        } else {
            return false;
        }

    }
    if($debug == true){
       print_R($result);
    }


    if (!empty($result)) {
        if($debug == true){
           echo min($result);
        }
        return min($result);
    } else {
        return false;
    }
}


private function navisionitem_isExternal($sku)
{
    $sql = "select is_external from navision_item where is_external = 1 and `language_id` = 1 AND `deleted` IS NULL and no ='".$sku."'";
    $res = \NavisionItem::find_by_sql($sql);
    return (!$res) ? false:true;
}

    public function updateReservationer()
    {
        $shopID = $_POST["shop_id"];
        $adapt = "adapt_".$_POST["adapt"];

        $data = $_POST["data"];

        foreach ($data as $item){

            $model_id = $item["modelID"];
            $quantity = $item["quantity"];
            $action = $item["action"];


            $pr = \PresentReservation::find_by_shop_id_and_model_id($shopID,$model_id);
            unset($pr->attributes['id']);
            unset($pr->attributes['sync_time']);
            unset($pr->attributes['last_change']);
            $responce =   \presentreservationlog::createPresentreservationlog($pr->attributes);
            \System::connection()->commit();
            \System::connection()->transaction();

                $pr = \PresentReservation::find_by_shop_id_and_model_id($shopID,$model_id);
                $pr->$adapt = $quantity;
                $pr->quantity = $quantity;
                $pr->save();
                \System::connection()->commit();
                \System::connection()->transaction();




            if($action == 1){
                $this->handleCloseItem($model_id);
                $this->handleDeactivatePresent($model_id,$shopID);
            }
            if($action == 2){
                $this->handleDoClose($model_id,$shopID);
            }

        }

        echo json_encode(array("status" => 1));
    }
    private function handleDoClose($modelID,$shopID)
    {

        $presentReservation = \PresentReservation::find_by_shop_id_and_model_id($shopID,$modelID);
        $presentReservation->do_close = 1;
        $presentReservation->is_close = 0;
        $presentReservation->save();
        \System::connection()->commit();
        \System::connection()->transaction();
    }

    private function handleDeactivatePresent($modelID,$shopID){

        // shop_present
        $PresentModel = \PresentModel::find_by_model_id_and_language_id($modelID,1);
        $obj =  \PresentModel::find_by_present_id_and_language_id_and_active($PresentModel->present_id,1,0);
        if($obj){ } else {
       
            $shop_present = \ShopPresent::find_by_shop_id_and_present_id($shopID,$PresentModel->present_id);
            $shop_present->active = 0;
            $shop_present->save();
            \System::connection()->commit();
            \System::connection()->transaction();
        }
    }

    private function handleCloseItem($modelID){

      $PresentModel = \PresentModel::find_by_model_id_and_language_id($modelID,1);
      $PresentModelObj = \PresentModel::find($PresentModel->id);

      $PresentModelObj->active = 1;
      $PresentModelObj->save();
      \System::connection()->commit();
      \System::connection()->transaction();
    }

    public function getCurrentData($output="") {

        $shop_id = $_POST["shop_id"];


        $idNotInShop = [];
        $mapping = [];
        $problemGift = [];
        $giftFromShop_present_list = [];
        $rapportEmailData = "";
        $rapportEmail = \ShopPresent::find_by_sql("select rapport_email from shop where id = '".$shop_id."'");
        if(sizeofgf($rapportEmail) > 0){
            $rapportEmailData =  $rapportEmail[0]->attributes["rapport_email"];
        }

        $giftFromShop_present =  \ShopPresent::find_by_sql("select present_id from shop_present where shop_id = '".$shop_id."' and is_deleted = 0 ");
        for($i=0;sizeofgf($giftFromShop_present) > $i;$i++){
            $giftFromShop_present_list[] = $giftFromShop_present[$i]->attributes["present_id"];
        }
        $giftFromOrder =  \ShopPresent::find_by_sql("SELECT DISTINCT `present_id` FROM `order` WHERE `shop_id` = '".$shop_id."'" );


        foreach ( $giftFromOrder as $orderId ){
            $found = false;
            foreach ($giftFromShop_present_list as $fromShopId){
                if($orderId->present_id == $fromShopId*1){
                    // echo  $orderId->present_id."--";
                    $found = true;
                }
            }

            if($found == false){
                $unikId = ShopPresent::find_by_sql("SELECT id FROM `present` WHERE `copy_of` = ". $orderId->present_id ." and shop_id = ".$shop_id );
                //$antal = ShopPresent::find_by_sql("SELECT count(id) as antal  FROM `order` WHERE ".$shop_id." = 282 AND `present_id` = ".$orderId->present_id  );
                if(sizeofgf($unikId) > 0){
                    $mapping[$unikId[0]->attributes["id"]] = $orderId->present_id;
                } else {
                    $problemGift[] = $orderId->present_id;
                }


            }

        }
        $result = array();
        $shoppresents  = \ShopPresent::find('all',array('conditions' => array('shop_id'=>$shop_id,'is_deleted'=>0)));



        foreach($shoppresents as $shoppresent)  {
            $present = \Present::find($shoppresent->present_id);
            $presentmodels  = \PresentModel::find('all',array('conditions' => array(
                'present_id'=>$shoppresent->present_id,
                'language_id' => 1, 'is_deleted' => 0
            )));


            if(count($presentmodels)==0) {

            }   else {
                foreach($presentmodels as $presentmodel)  {

                    $presentreservation = \PresentReservation::hasReservation($shop_id,$present->id,$presentmodel->model_id);
                    $record = array();
                    $record['present_id']          =  $shoppresent->present_id;
                    $record['present_name']        =  $presentmodel->model_name;
                    $record['present_model_id']    =  $presentmodel->model_id;
                    $record['model_present_no']    =  $presentmodel->model_present_no ;
                    if($presentmodel->model_no == ""){
                        $record['model_present_name']  =  $presentmodel->model_name;
                    } else {
                        $record['model_present_name']  =  $presentmodel->model_name." / ".$presentmodel->model_no;
                    }


                    $newPresentId =  $shoppresent->present_id;
                    foreach ($mapping as $key => $val){
                        if($key == $shoppresent->present_id){
                            $newPresentId = $shoppresent->present_id.",".$val;
                        }
                    }
                    // present deativatede
                    if($shoppresent->active == 0){
                        $record['present_is_active']   =   1;
                    } else {
                        $record['present_is_active']   =  $presentmodel->active;
                    }

                    $record['present_is_deletet']  =  $presentmodel->is_deleted;
                    $record['present_total_is_deletet']  =  $shoppresent->is_deleted;
                    $record['present_total_is_active']   =   $shoppresent->active;


                    $record['present_properties_id'] = $shoppresent->id;
                    $record['order_count']   =  \Order::countPresentOnOrders($shop_id,$newPresentId,$presentmodel->model_id);
                    if(isset($presentreservation)) {
                        $record['autotopilot'] = $presentreservation->autotopilot;
                        $record['do_close'] = $presentreservation->do_close;
                        $record['reservation_id'] = $presentreservation->id;
                        $record['reserved_quantity']  = $presentreservation->quantity;
                        $record['replacement_present_name'] = $presentreservation->replacement_present_name;
                        $record['replacement_present_id'] = $presentreservation->replacement_present_id;
                        $record['warning_level']  = $presentreservation->warning_level;
                        $record['skip_navision']  = $presentreservation->skip_navision;
                        $record['ship_monitoring']  = $presentreservation->ship_monitoring;


                    } else {
                        $record['autotopilot'] = "";
                        $record['do_close'] = "";
                        $record['replacement_present_id'] = "";
                        $record['replacement_present_name'] = "";
                        $record['reservation_id'] = '';
                        $record['reserved_quantity']  = '';
                        $record['warning_level']  = '';
                        $record['skip_navision']  = 0;
                        $record['ship_monitoring']  = 0;

                    }

                    $result[] = $record;
                }
            }
        }
        if($output == "func"){
            return $result;
        }
        echo json_encode(array("status" => 1,"data"=>$result));
    }





    public function getShopCompleted()
    {

        $shop_id = $_POST["shop_id"];
        $sql = "SELECT 'order_numbers' AS type, COUNT(*) as count FROM `order` WHERE `shop_id` = ".$shop_id."
                UNION ALL
                SELECT 'su_numbers' AS type, COUNT(*) as count FROM `shop_user` WHERE `shop_id` = ".$shop_id;
        $rs = \Present::find_by_sql($sql);
        echo json_encode(array("status" => 1,"data"=>$rs));


    }
    private function generateRandomString($length = 20) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }
    public function autopilot()
    {
        $this->magentoClient = new MagentoWS();
        $shop_id = $_POST["shop_id"];
        $responseData = [];
        $itemList = $this->getCurrentData("func");

        $totalOrders = isset($_POST["total_orders"]) ?  $_POST["total_orders"] : 0;
        $procentSelected = isset($_POST["procent_selected"]) ?  $_POST["procent_selected"] : 0;
        $groupID = $this->generateRandomString();





        foreach($itemList as $item){
           $itemno =  $item["model_present_no"];
           $modelid =  $item["present_model_id"];

            $stockavailable = "N/A";

            // Lagertal: Der tages højde for SAM
            if(strncmp(strtolower($itemno), "sam", 3) === 0){
               // $mst =  \MagentoStockTotal::getMaxAvailableStockSAM($itemno);
                $mst = $this->getMaxSam($itemno,);
                $stockavailable = $mst == false ? "N/A":$mst;

            } else {
                $stockavailable =  $this->getStuckFromNAV($itemno);
                /*$stockTotal = \MagentoStockTotal::find_by_itemno($itemno );
                if($stockTotal != NULL){
                    $stockavailable = $stockTotal->attributes["available"];
                }
                */
            }

            // find ud af om varenr er extern
            $sql = "SELECT * FROM navision_item 
                WHERE no = :itemno 
                AND language_id = :language_id 
                AND deleted IS NULL";

            $params = array(
                ':itemno' => $itemno,
                ':language_id' => 1
            );
            $NavisionItem = \NavisionItem::find_by_sql($sql, $params);
            $is_external = "N/A";
            if(sizeof($NavisionItem) > 0){
                $is_external = $NavisionItem[0]->attributes["is_external"];
            }


           $newReservation = $this->getNewResevation($itemno,$shop_id);
           $data = json_decode('[' . $newReservation . ']', false);


           $forecast = $data[0]?->data?->searchData[0]?->attributes?->forcast?->forecast ?? "N/A";
           //$forecast = ($data[0]->data->searchData[0]->attributes->forcast->forecast);
           $temp = array("forecast"=>$forecast,"itemno"=>$itemno,"modelID"=>$modelid,"stockavailable"=>$stockavailable,"isExternal"=>$is_external);
           $responseData[] = $temp;
            try {
                $this->logForecast($temp,$totalOrders,$procentSelected,$groupID,$shop_id);
         } catch (Exception $e) {


            }


       }
        //https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=reservationManagement/searchItemNr



        echo json_encode(array("status" => 1,"data"=>$responseData));

    }
    private function logForecast($data,$totalOrders,$procentSelected,$groupID,$shop_id){


        $prf = new \PresentReservationForecast();
        $prf->shop_id       = $shop_id;
        $prf->model_id      = $data["modelID"];
        $prf->itemno        = $data["itemno"];
        $prf->forecast      = $data["forecast"];
        $prf->procent       = ceil($procentSelected);
        $prf->total_orders  = $totalOrders;
        $prf->stock_available= $data["stockavailable"];
        $prf->is_external   = $data["isExternal"];
        $prf->group_id      = $groupID;
        $prf->save();
        \System::connection()->commit();
        \System::connection()->transaction();




    }



    public function getNewResevation($itemno,$shopID)
    {
        $url = 'https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=reservationManagement/searchItemNrAuto';

// Initialize cURL session
        $ch = curl_init();

// Set the POST fields
        $postFields = [
            'itemNr' => $itemno,
            'shopID' => $shopID
        ];

// Set the cURL options
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true); // Set the request method to POST
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postFields)); // Pass the POST fields
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return the response as a string
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Follow redirects if any
        curl_setopt($ch, CURLOPT_HEADER, false); // Don't include the header in the output
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Set a timeout for the request

// Execute the cURL request
        $response = curl_exec($ch);

// Check for cURL errors
        if ($response === false) {
            return false;
             //'cURL error: ' . curl_error($ch);
        } else {
            // Print the response
            'Response: ' . $response;
        }

// Close the cURL session
        curl_close($ch);
        return $response;
    }

}