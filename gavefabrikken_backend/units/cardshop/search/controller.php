<?php

namespace GFUnit\cardshop\search;
use GFBiz\units\UnitController;

class Controller extends UnitController
{

    public function __construct()
    {
        parent::__construct(__FILE__);
    }

    public function getEmail()
    {
        $email = $_POST["searchTxt"];
        $mail = \MailQueue::find_by_sql("SELECT id,sent,error,sent_datetime,error_message,subject, is_smtp_error, bounce_type FROM `mail_queue` where  recipent_email = '".$email."' ORDER BY `id` DESC limit 30");


        for($i=0;$i<countgf($mail);$i++) {

            if($mail[$i]->is_smtp_error == 1 && trim($mail[$i]->bounce_type) != "") {
                $mail[$i]->error = 1;
                $mail[$i]->error_message = $mail[$i]->bounce_type;
            }

        }


        $response = array("status" => 1, "data" => $mail);
        echo json_encode($response);
    }
    public function readEmail($id)
    {
        // helene.ericsson@olivia.se
        $mail = \MailQueue::find($id);
        $mail->attributes["body"] = ($mail->attributes["body"]);

        $response = array("status" => 1, "data" => $mail);
        echo json_encode($response);
    }


    public function getCompanyInfo($companyID)
    {
        $CompanyRs =   \Dbsqli::getSql2("SELECT * FROM company WHERE id=".intval($companyID));
        $response = array("status" => 1, "data" => $CompanyRs);
        echo json_encode($response);
    }
     public function searchTrack($userID)
    {
        $res=  \ShopUser::getShopUserDeliveryStatus($userID);
        $details = \ShopUser::getShopUserDeliveryStatus($userID,true);
        $response = array("status" => 1, "data" => $res,"htmldetails" => $details);
        echo json_encode($response);
    }

    public function isReplacementCard($companyID){
        if(intval($companyID) <= 0) return false;
        $cardshopWithReplacement = \CardshopSettings::find('first',array("conditions" => array("replacement_company_id" => intval($companyID))));
        if($cardshopWithReplacement == null || $cardshopWithReplacement->id == 0) return false;
        else return true;
    }

    public  function handleReplacementCard(){
        /* $handle:
        0 = "ej replacement card";
        1 = "Replacement card";
        2 = "Deleted Replacement card";
        3 = "last year replacement card";
        4 = "Last year deleted Replacement";

        */
        $search =  $_POST["searchTxt"];

        // ---- tjekker kortnr. ---- //
        // fra i år
        $serviceMsg = array();
        $handle = 0;

        $res = \Dbsqli::getSql2("SELECT * FROM shop_user WHERE username=".intval($search));
        if(sizeofgf($res) != 0){
            if(sizeofgf($res) != 0 && $this->isReplacementCard($res[0]["company_id"])) {
                $handle = intval($res[0]["replacement_id"]) > 0 ? 2 : 1;
            }
        } else {
            $res = \Dbsqli::getSql2("SELECT * FROM gavefabrikken2021.shop_user WHERE username=".intval($search));
            if(sizeofgf($res) != 0 && $this->isReplacementCard($res[0]["company_id"])) {
                $handle = intval($res[0]["replacement_id"]) > 0 ? 3 : 4;
            }
        }

        // handle = 1 no handling
        if($handle == 2){
            $serviceMsg[] = "Kortet er et erstatningskort, der er blevet slettet";
            $infoRes = $this->gettingDeletedReplacmentInfo($res[0]["replacement_id"]);

            if($infoRes == false){
                $serviceMsg[] = "Kortet kan ikke findes";
            } else {
                $serviceMsg[] = "Det oprindelige kortnr er: '" . $infoRes[0]["username"] . "'";
                $infoRes[0]["is_giftcertificate"] == 1 ?
                    $serviceMsg[] =  "Kortet er et 'Gavekort'" :
                    $serviceMsg[]  = "Kortet er fra en valgshop";
            }
        }
        if($handle == 3){
            $serviceMsg[] = "Kortet er et erstatningskort fra 2021";
            $infoRes = $this->gettingDeletedReplacmentInfo($res[0]["replacement_id"],"gavefabrikken2021.");
            if(sizeofgf($infoRes) != 0){
                $serviceMsg[] = "Det oprindelige kortnr. er: ". $infoRes[0]["username"];
            }
        }
        if($handle == 4){
            $serviceMsg[] = "Kortet er et erstatningskort fra 2021, der er blevet slettet";
            $infoRes = $this->gettingDeletedReplacmentInfo($res[0]["replacement_id"],"gavefabrikken2021.");
            if(sizeofgf($infoRes) != 0){
                $serviceMsg[] = "Det oprindelige kortnr. er: ". $infoRes[0]["username"];
            }
        }


        print_r($res);
        // er kortet et replesming kort
        // er kortet et replacement kort der er blevet slettet
        // er kortet et org der er blvet replace



        // ---- tjekker ordre nr. ---- //

        print_r($serviceMsg);
    }
    public function gettingDeletedReplacmentInfo($cardNr,$db=""){
        $cardNr = intval($cardNr) < 0 ? $cardNr*-1 : $cardNr;
        $res = \Dbsqli::getSql2("SELECT * FROM ".$db."shop_user WHERE id=".$cardNr);
        return sizeofgf($res) != 0 ? $res : false;
    }


    public function doSuperSearch()
    {
           //
           $searchTxt = $_POST["searchTxt"];
           $searchTxt = trimgf($searchTxt);
           $return = "";

           $result2021Hist = [];


             $result2021Hist =   \Dbsqli::getSql2("SELECT order_history.*, present_model.model_name, present_model.model_no,present_model.model_present_no,present_model.media_path,
                     company.name,
                company.cvr,
                company.ean,
                company.id,
                company.pid,
                company.contact_name,
                company.contact_phone,
                company.contact_email,
                company.ship_to_company,
                company.ship_to_attention,
                company.ship_to_address,
                company.ship_to_address_2,
                company.ship_to_postal_code,
                company.ship_to_city,
                 shop_user.replacement_id,
                 shop_user.is_replaced,
                 shop_user.is_delivery,
                 su.shopuser_id as su_shopuser_id
                        FROM order_history
                        inner JOIN present_model on order_history.present_model_id = present_model.model_id and present_model.language_id = 1
                        inner join company on  order_history.company_id =  company.id
                        inner join (SELECT `shopuser_id` FROM order_history WHERE `order_no` = '".$searchTxt."' || `user_username` like ('%".$searchTxt."%')  || `user_email` like('%".$searchTxt."%') || `user_name` like ('%".$searchTxt."%')  ) as su
                        on order_history.shopuser_id = su.shopuser_id
                        inner join shop_user on su.shopuser_id = shop_user.id
                        group by order_no
                        order by order_history.id DESC limit 30");



            // SEARCH FOR SE SHIPMENTS
             if(intvalgf($searchTxt) > 0) {

                 $resultShipment =  \Dbsqli::getSql2("SELECT 
                        order_history.*, 
                        present_model.model_name, 
                        present_model.model_no,
                        present_model.model_present_no,
                        present_model.media_path,
                        company.name,
                        company.cvr,
                        company.ean,
                        company.id,
                        company.pid,
                        company.contact_name,
                        company.contact_phone,
                        company.contact_email,
                        company.ship_to_company,
                        company.ship_to_attention,
                        company.ship_to_address,
                        company.ship_to_address_2,
                        company.ship_to_postal_code,
                        company.ship_to_city,
                        shop_user.replacement_id,
                        shop_user.is_replaced,
                        shop_user.is_delivery,
                        su.shopuser_id as su_shopuser_id,
                        shipment.id as shipment_id
                    FROM order_history
                    INNER JOIN present_model ON order_history.present_model_id = present_model.model_id AND present_model.language_id = 1
                    INNER JOIN company ON order_history.company_id = company.id
                    INNER JOIN (
                        SELECT shopuser_id 
                        FROM order_history 
                    ) AS su ON order_history.shopuser_id = su.shopuser_id
                    INNER JOIN shop_user ON su.shopuser_id = shop_user.id
                    INNER JOIN shipment ON shipment.from_certificate_no = shop_user.username 
                        AND shipment.handler LIKE 'dpse' 
                        AND shipment.shipment_type = 'privatedelivery' 
                        AND shipment.shipment_state = 2 
                        AND shipment.id = ".intvalgf($searchTxt)."
                    GROUP BY order_no
                    ORDER BY order_history.id DESC 
                    LIMIT 30;
                    ");

                 if($resultShipment != null && countgf($resultShipment) > 0) {
                     foreach($resultShipment as $shipment) {
                         $result2021Hist[] = $shipment;
                     }
                 }

             }


             /*
             if(sizeofgf($result2021Hist) > 1){
                $result2021Hist[0] = $result2021order[0];
                $return1 = $result2021Hist;
             } else{
               $return1 =  $result2021order;
             }
               */
           if(sizeofgf($result2021Hist) != 0){
                  $return = array("status"=>1,"data"=>$result2021Hist,"type"=>1);
                  echo json_encode($return) ;
                  return;
           }

           $result2021Shop =   \Dbsqli::getSql2("
                SELECT `shop_user`.*, IF(company_order.floating_expire_date IS NULL,company_order.expire_date,DATE(company_order.floating_expire_date)) as expire_date,
                company.name,
                company.cvr,
                company.ean,
                company.id,
                company.pid,
                company.contact_name,
                company.contact_phone,
                company.contact_email,
                company_order.order_no,
                company_order.salesperson,
                company_order.certificate_no_begin,
                company_order.certificate_no_end,
                company_order.shop_name,
                company_order.is_email,
                company_order.certificate_value,
                company_order.welcome_mail_is_send,
                      shop_user.replacement_id,
                 shop_user.is_replaced
                        FROM `shop_user`
                inner join company on `shop_user`.company_id =  company.id
                inner join company_order on `shop_user`.company_order_id =  company_order.id
                 WHERE `shop_user`.`username` LIKE '".$searchTxt."'  ORDER BY `shop_user`.`id` DESC limit 30");
           if(sizeofgf($result2021Shop) != 0){
                $return = array("status"=>1,"data"=>$result2021Shop,"type"=>2);
                  echo json_encode($return) ;
                  return;
           }

            $result2021Company =   \Dbsqli::getSql2("SELECT * FROM company WHERE `name` like ('%".$searchTxt."%')  || `contact_name` like ('%".$searchTxt."%')  || `contact_phone` like ('%".$searchTxt."%')  || `contact_email` like ('%".$searchTxt."%') || `cvr` like ('%".$searchTxt."%') || `ean` like ('%".$searchTxt."%') ORDER BY `id`  DESC limit 30");
            if(sizeofgf($result2021Company) != 0){
                  $return = array("status"=>1,"data"=>$result2021Company,"type"=>5);
                  echo json_encode($return) ;
                  return;
            }

                   // --- pr�v 2020
                  $result2020Hist =   \Dbsqli::getSql2("
                        SELECT
                        gavefabrikken2021.order_history.*,
                        gavefabrikken2021.present_model.model_name,
                        gavefabrikken2021.present_model.model_no,
                        gavefabrikken2021.present_model.model_present_no,
                        gavefabrikken2021.present_model.media_path FROM gavefabrikken2021.`order_history`
                        inner JOIN gavefabrikken2021.present_model on gavefabrikken2021.order_history.present_model_id = gavefabrikken2021.present_model.model_id and gavefabrikken2021.present_model.language_id = 1
                        WHERE shopuser_id in ( SELECT `shopuser_id` FROM gavefabrikken2021.`order_history` WHERE `order_no` = '".$searchTxt."' || `user_username` like ('%".$searchTxt."%')  || `user_email` like('%".$searchTxt."%') || `user_name` like ('%".$searchTxt."%')   and  `shop_is_gift_certificate` = 1) and `shop_is_gift_certificate` = 1 order by id DESC limit 30");
                 if(sizeofgf($result2020Hist) != 0){
                       $return = array("status"=>1,"data"=>$result2020Hist,"type"=>3);
                       echo json_encode($return) ;
                      return;
                 }
                 $result202Shop =   \Dbsqli::getSql2("SELECT * FROM gavefabrikken2021.`shop_user` WHERE `username` LIKE '".$searchTxt."' AND `is_giftcertificate` = 1 ORDER BY `id` DESC limit 30");
                 if(sizeofgf($result202Shop) != 0){
                      $return = array("status"=>1,"data"=>$result202Shop,"type"=>4);
                     echo json_encode($return) ;
                      return;
                 }

                 $return = array("status"=>1,"data"=>"","type"=>0);
                   echo json_encode($return) ;
    }


   public function getCardInvoiceHistory($cardnr)
   {
           $result =   \Dbsqli::getSql2("SELECT order_history.*, present_model.model_name, present_model.model_no,present_model.model_present_no,present_model.media_path FROM `order_history`
                        inner JOIN present_model on order_history.present_model_id = present_model.model_id and present_model.language_id = 1
                        WHERE shopuser_id in ( SELECT `shopuser_id` FROM `order_history` WHERE `user_username` =  ".$cardnr."  and `shop_is_gift_certificate` = 1) and `shop_is_gift_certificate` = 1 order by id DESC");
           $return = array("status"=>1,"data"=>$result);
           echo json_encode($return) ;
   }







    public function doCardSearch()
    {
           $searchTxt = $_POST["searchTxt"];

           $result =   \Dbsqli::getSql2("SELECT order_history.*, present_model.model_name, present_model.model_no,present_model.model_present_no,present_model.media_path FROM `order_history`
                        inner JOIN present_model on order_history.present_model_id = present_model.model_id and present_model.language_id = 1
                        WHERE shopuser_id in ( SELECT `shopuser_id` FROM `order_history` WHERE `user_username` =  ".$searchTxt."  and `shop_is_gift_certificate` = 1) and `shop_is_gift_certificate` = 1 order by id DESC");
           $return = array("status"=>1,"data"=>$result);
           echo json_encode($return) ;
    }
    public function doEmailSearch()
    {
           $searchTxt = $_POST["searchTxt"];

           $result =   \Dbsqli::getSql2("SELECT order_history.*, present_model.model_name, present_model.model_no,present_model.model_present_no,present_model.media_path FROM `order_history`
                        inner JOIN present_model on order_history.present_model_id = present_model.model_id and present_model.language_id = 1
                        WHERE shopuser_id in ( SELECT `shopuser_id` FROM `order_history` WHERE `user_email` like('%".$searchTxt."%')  and `shop_is_gift_certificate` = 1) and `shop_is_gift_certificate` = 1 order by  id DESC limit 30");

           $return = array("status"=>1,"data"=>$result);
           echo json_encode($return) ;
    }
    public function doNameSearch()
    {
           $searchTxt = $_POST["searchTxt"];

           $result =   \Dbsqli::getSql2("SELECT order_history.*, present_model.model_name, present_model.model_no,present_model.model_present_no,present_model.media_path FROM `order_history`
                        inner JOIN present_model on order_history.present_model_id = present_model.model_id and present_model.language_id = 1
                        WHERE shopuser_id in ( SELECT `shopuser_id` FROM `order_history` WHERE `user_name` like ('%".$searchTxt."%')  and `shop_is_gift_certificate` = 1) and `shop_is_gift_certificate` = 1 order by id DESC limit 30");
           $return = array("status"=>1,"data"=>$result);
           echo json_encode($return) ;
    }




}
