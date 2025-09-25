<?php

namespace GFUnit\apps\autopilot;
use GFBiz\units\UnitController;

class Controller extends UnitController
{

    public function __construct()
    {
        parent::__construct(__FILE__);
    }

    /* status_stats: show  */
    public function panel()
    {
        $this->view("panel");
    }



    public function getCurrentData($output="") {

        $shop_id = 6490;


        $idNotInShop = [];
        $mapping = [];
        $problemGift = [];
        $giftFromShop_present_list = [];
        $rapportEmailData = "";
        $rapportEmail = \ShopPresent::find_by_sql("select rapport_email from shop where id = '".$shop_id."'");
        if(sizeofgf($rapportEmail) > 0){
            $rapportEmailData =  $rapportEmail[0]->attributes["rapport_email"];
        }

        $giftFromShop_present =  \ShopPresent::find_by_sql("select present_id from shop_present where shop_id = '".$shop_id."' and is_deleted = 0");
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



                    $record['present_is_deletet']  =  $presentmodel->is_deleted;
                    $record['present_is_active']   =   $presentmodel->active;
                    $record['present_total_is_deletet']  =  $shoppresent->is_deleted;
                    $record['present_total_is_active']   =   $shoppresent->active;


                    $record['present_properties_id'] = $shoppresent->id;
                    $record['order_count']   =  \Order::countPresentOnOrders($shop_id,$newPresentId,$presentmodel->model_id);
                    if(isset($presentreservation)) {
                        $record['do_close'] = $presentreservation->do_close;
                        $record['reservation_id'] = $presentreservation->id;
                        $record['reserved_quantity']  = $presentreservation->quantity;
                        $record['replacement_present_name'] = $presentreservation->replacement_present_name;
                        $record['replacement_present_id'] = $presentreservation->replacement_present_id;
                        $record['warning_level']  = $presentreservation->warning_level;
                        $record['skip_navision']  = $presentreservation->skip_navision;
                        $record['ship_monitoring']  = $presentreservation->ship_monitoring;
                    } else {
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
        $sql = "SELECT 'order_numbers' AS type, COUNT(*) as count FROM `order` WHERE `shop_id` = 6490
                UNION ALL
                SELECT 'su_numbers' AS type, COUNT(*) as count FROM `shop_user` WHERE `shop_id` = 6490";
        $rs = \Present::find_by_sql($sql);
        echo json_encode(array("status" => 1,"data"=>$rs));


    }
    public function autopilot()
    {
        $responseData = [];
        $itemList = $this->getCurrentData("func");
        foreach($itemList as $item){
           $itemno =  $item["model_present_no"];
           $modelid =  $item["present_model_id"];
           $newReservation = $this->getNewResevation($itemno,6490);
           $data = json_decode('[' . $newReservation . ']', false);
           $forecast = ($data[0]->data->searchData[0]->attributes->forcast->forecast);
           $responseData[] = array("forecast"=>$forecast,"itemno"=>$itemno,"modelID"=>$modelid);
           $sql = "SELECT available FROM `magento_stock_total` WHERE `itemno` LIKE '".$itemno."'";
            $rs = \Present::find_by_sql($sql);
            echo $rs[0]->attributes["available"];
            
       }
        //https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=reservationManagement/searchItemNr


        echo json_encode(array("status" => 1,"data"=>$responseData));

    }




    public function getNewResevation($itemno,$shopID)
    {
        $url = 'https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=reservationManagement/searchItemNr';

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