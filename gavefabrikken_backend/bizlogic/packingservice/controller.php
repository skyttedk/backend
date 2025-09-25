<?php

namespace GFBiz\PackingService;

class Controller extends ServiceHelper
{

    public function index()
    {
        $this->outputServiceError(2,"Invalid service endpoint");
    }

    public function mail() {

        // Check if post
        if(!isset($_POST)) return $this->outputServiceError(30,"No data provided");

        // Get language
        if(!isset($_POST["language_id"])) return $this->outputServiceError(31,"No language code provided");
        $validLanguages = array(1 => 4,4 => 4,5 => 5);
        $languageCode = intvalgf($_POST["language_id"]);
        if(!isset($validLanguages[$languageCode])) {
            return $this->outputServiceError(31,"Invalid language code provided");
        }

        // Mailserver
        $mailServer = \MailServer::find($validLanguages[$languageCode]);
        if($mailServer == null || $mailServer->id == 0) {
            return $this->outputServiceError(32,"No mailserver found");
        }


        // Get email
        if(!isset($_POST["email"])) return $this->outputServiceError(33,"No email provided");
        $email = trimgf($_POST["email"]);

        // Check valid e-mail
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->outputServiceError(33,"Invalid email provided");
        }

        // Get name
        if(!isset($_POST["name"])) $name = "";
        else $name = trimgf($_POST["name"]);

        // Get subject
        if(!isset($_POST["subject"])) return $this->outputServiceError(34,"No subject provided");
        $subject = trimgf($_POST["subject"]);

        // Check subject not empty
        if($subject == "") {
            return $this->outputServiceError(34,"No subject provided");
        }

        // Check for body
        if(!isset($_POST["body"])) return $this->outputServiceError(35,"No body provided");
        if(trimgf($_POST["body"]) == "") return $this->outputServiceError(35,"No body provided");
        $body = trimgf($_POST["body"]);

        // Check body is in base64 and decode
        if(!$this->is_base64($body)) return $this->outputServiceError(35,"Invalid body provided");
        $body = base64_decode($body);
        if(trimgf($body) == "") return $this->outputServiceError(35,"No body provided");


        try {

            \system::connection()->transaction();

            // Add to mail queue
            $mailqueue = new \MailQueue();
            $mailqueue->sender_name = $mailServer->sender_name;
            $mailqueue->sender_email = $mailServer->sender_email;
            $mailqueue->recipent_name = $name;
            $mailqueue->recipent_email = $email;
            $mailqueue->mailserver_id = $mailServer->id;
            $mailqueue->subject = $subject;
            $mailqueue->send_group = "packingservice";
            $mailqueue->body = $body;
            $mailqueue->save();

            \system::connection()->commit();

        } catch (\Exception $e) {
            \system::connection()->rollback();
            return $this->outputServiceError(36, "Could not save mail");
        }

        // Output data
        $this->outputServiceSuccess(array("mailid" => $mailqueue->id));

    }

    private function is_base64($s)
    {
        return (bool) preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $s);
    }


    public function locations()
    {

        // Load locations
        $locationList = \NavisionLocation::find_by_sql("SELECT * FROM navision_location WHERE blocked = 0 && deleted IS NULL ORDER BY code ASC");
        $responseData = array();

        // Process each location
        foreach($locationList as $location) {
            $responseData[] = array("code" => $location->code,"name" => $location->name,"language" => $location->language_id);
        }

        // Output data
        $this->outputServiceSuccess(array("locations" => $responseData));

    }

    public function shops($languageid="",$location="",$shopid="") {

        $location = urldecode($location);

        // Valid language id
        if(!$this->validLanguage($languageid)) {
            return $this->outputServiceError(3,"Invalid language provided");
        }

        // Validate location
        if(trimgf($location) == "") {
            return $this->outputServiceError(4,"No location provided");
        }
        else if(!$this->validLocation($location)) {
            return $this->outputServiceError(4,"Invalid location provided: ".$location);
        }

        /*
        if(trimgf($shopid) != "") {

            $explodeShopID = explode("-",$shopid);
            $shopIDNum = intval($explodeShopID);


            if(intval($shopIDNum) <= 0) {
                return  $this->outputServiceError(5,"Invalid shop ID provided");
            }

            $shop = \Shop::find(intval($shopIDNum));
            if($shop == null || $shop->id != intval($shopIDNum)) {
                return $this->outputServiceError(5,"Invalid shop ID provided");
            }



            if($shop->reservation_code != $location || $shop->reservation_language != $languageid) {
                return $this->outputServiceError(5,"Shop not on location or language");
            }

            $shopMetadata = \ShopMetadata::find("first",array("conditions" => array("reservation_state = 1 AND shop_id = ?",$shop->id)));
            if($shopMetadata->salesperson_code == "") {
                    $systemUser = new \SystemUser();
            } else {
                $systemUser = \SystemUser::find('first',array('conditions' => array('salespersoncode != \'\' && salespersoncode = ?',$shopMetadata->salesperson_code)));
            }

            $shopData = array("id" => $shop->id,"name" => $shop->name,"location" => $shop->reservation_code,"salesperson_code" => $shopMetadata->salesperson_code,"salesperson_name" => $systemUser->name,"salesperson_email" => $systemUser->email,"salesperson_phone" => $systemUser->phone,"language" => $shop->reservation_language,"delivery_date" => $shopMetadata->delivery_date, "notes" => $shopMetadata->handling_notes);
            return  $this->outputServiceSuccess(array("shop" => $shopData));

        }
*/

        // Load shops
        $shopList = \Shop::find('all',array('conditions' => array('id not in (select shop_id from cardshop_settings) AND reservation_state = 1 AND reservation_code = ? AND reservation_language = ?',$location,$languageid)));
        $responseData = array();

        // Process each shop
        foreach($shopList as $shop) {

            $shopMetadata = \ShopMetadata::find("first",array("conditions" => array("shop_id = ?",$shop->id)));
            if($shopMetadata->salesperson_code == "") {
                $systemUser = new \SystemUser();
            } else {
                $systemUser = \SystemUser::find('first',array('conditions' => array('salespersoncode != \'\' && salespersoncode = ?',$shopMetadata->salesperson_code)));
            }
            $responseData[] = array("id" => $shop->id."-VALG","shop_id" => $shop->id,"name" => $shop->name,"dimension" => "VALGSHOP","location" => $shop->reservation_code,"packing_state" => $shopMetadata->packing_state,"salesperson_code" => $shopMetadata->salesperson_code,"salesperson_name" => $systemUser->name,"salesperson_email" => $systemUser->email,"salesperson_phone" => $systemUser->phone,"language" => $shop->reservation_language,"delivery_date" => $shopMetadata->delivery_date, "notes" => $shopMetadata->handling_notes);
        }

        // Process all cardshops
        $shopList = \Shop::find('all',array('conditions' => array('id in (select shop_id from cardshop_settings where language_code = '.$languageid.') AND reservation_state = 1')));
        foreach($shopList as $cardshop) {

            $expireDates = \CardshopExpiredate::find('all',array('conditions' => array('shop_id = ?',$cardshop->id)));
            foreach($expireDates as $date) {

                $expireDate = \ExpireDate::find($date->expire_date_id);
                $ed = $expireDate->display_date;

                if((trimgf($date->reservation_code) != "" && $date->reservation_code == $location) || (trimgf($date->reservation_code) == "" && $cardshop->reservation_code == $location)) {

                    $shopMetadata = \ShopMetadata::find("first",array("conditions" => array("shop_id = ?",$cardshop->id)));
                    if($shopMetadata->salesperson_code == "") {
                        $systemUser = new \SystemUser();
                    } else {
                        $systemUser = \SystemUser::find('first',array('conditions' => array('salespersoncode != \'\' && salespersoncode = ?',$shopMetadata->salesperson_code)));
                    }
                    // Show array on multiple lines
                    //$responseData[] = array("id" => $cardshop->id."-".$ed,"shop_id" => $cardshop->id,"name" => $cardshop->name,"dimension" => $ed,"location" => $cardshop->reservation_code,"packing_state" => $expireDate->packing_state,"salesperson_code" => $shopMetadata->salesperson_code,"salesperson_name" => $systemUser->name,"salesperson_email" => $systemUser->email,"salesperson_phone" => $systemUser->phone,"language" => $cardshop->reservation_language,"delivery_date" => "Uge ".$expireDate->week_no, "notes" => $shopMetadata->handling_notes);

                    $responseData[] = array(
                        "id" => $cardshop->id."-".$ed,
                        "shop_id" => $cardshop->id,
                        "name" => $cardshop->name,
                        "dimension" => $ed,
                        "location" => $cardshop->reservation_code,
                        "packing_state" => $date->packing_state,
                        "salesperson_code" => $shopMetadata->salesperson_code,
                        "salesperson_name" => $systemUser->name,
                        "salesperson_email" => $systemUser->email,
                        "salesperson_phone" => $systemUser->phone,
                        "language" => $cardshop->reservation_language,
                        "delivery_date" => "Uge ".$expireDate->week_no,
                        "notes" => $shopMetadata->handling_notes
                    );
                }

            }

        }

        if(trimgf($shopid) != "") {
            foreach($responseData as $shop) {
                if(strtolower(trimgf($shopid)) == strtolower(trimgf($shop["id"])))
                {
                    return  $this->outputServiceSuccess(array("shop" => $shop));
                }
            }
            return $this->outputServiceError(10, "Could not find shop");
        }


        return  $this->outputServiceSuccess(array("shops" => $responseData));


    }

}