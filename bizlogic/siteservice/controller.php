<?php

namespace GFBiz\Siteservice;

class Controller extends ServiceHelper
{

    public function index()
    {
        $this->outputServiceError(2,"Invalid service endpoint");
    }

    public function concept($concept="",$subAction="",$shopid=0,$shopSubaction="")
    {

        if(trimgf($concept) != "") {

            $shops = $this->getShopsByConcept($concept);
            if(count($shops) == 0) {
                $this->outputServiceError(21,"No shops on this concept");
            }

            if($subAction != "") {

                if(intval($shopid) > 0) {

                    $shop = null;
                    foreach($shops as $shopObj) {
                        if($shopObj->shop_id == intval($shopid)) {
                            $shop = $shopObj;
                        }
                    }

                    if($shop == null) {
                        $this->outputServiceError(22,"Invalid shop selected");
                    }
                    else if($subAction == "presents") {
                        $this->presentlist($shop,$shopSubaction);
                    }
                    else if($subAction == "testmail") {
/*
                        $weborderlog = new \WebOrderLog();
                        $weborderlog->error = "test";
                        $weborderlog->input = json_encode($_POST);
                        $weborderlog->output = json_encode(array("status" => 1, "order_no" => 0,"order_id" => 0));
                        $weborderlog->orderid =0;
                        $weborderlog->shop_id = isset($_POST["shop_id"]) ? intval($_POST["shop_id"]) : 0;
                        $weborderlog->url = $_SERVER["REQUEST_URI"];
                        $weborderlog->save();
                        \System::connection()->commit();
*/
                        //$this->testMail($shop);
                    }
                    else {
                        $this->outputServiceError(23,"Invalid shop action");
                    }

                } else {
                    $this->outputServiceError(24,"Provide a shopid");
                }

            }
            else {
                $this->shopMetadata($concept,$shops);
            }

        } else {
            $this->listConcepts();
        }
    }

    public function testmail($cardshopSettings)
    {
        if(!isset($_POST["email"])) {
            $this->outputServiceError(110,"No e-mail provided");
        }
        
        $email = $_POST["email"];
        $name = $_POST["name"];

        // Send receipt
        if($cardshopSettings->language_code == 1) {
            $daKvittering = new OrderMailDA();
            $daKvittering->sendConfirmationEmail($name,$email);
        } else if($cardshopSettings->language_code == 4) {
            $noKvittering = new OrderMailNO();
            $noKvittering->sendConfirmationEmail($name,$email);
        } else if($cardshopSettings->language_code == 5) {
            $seKvittering = new OrderMailSE();
            $seKvittering->sendConfirmationEmail($name,$email);
        }

        // Commit
        \system::connection()->commit();

        // Output ok and return
        $this->outputServiceSuccess(array("status" => 1));

    }

    public function presentlist($cardshopSettings,$format)
    {

        $shop = \Shop::find($cardshopSettings->shop_id);

        // Get data
        $sql = "SELECT present.id, present_description.caption, present_media.media_path
FROM `present`, present_description, present_media, shop_present
WHERE present.id = shop_present.present_id && present.id = present_media.present_id && present_media.index = 0 && present_description.language_id = ".$cardshopSettings->language_code." && present.id = present_description.present_id && present.`shop_id` = ".$cardshopSettings->shop_id." && present.id IN (SELECT present_id FROM shop_present WHERE shop_id = ".$cardshopSettings->shop_id." && active = 1 && is_deleted = 0)
ORDER BY shop_present.index_ ASC";

        $presentlist = \Present::find_by_sql($sql);

        if($format == "xml")
        {
            ob_start();
            header('Content-Type: text/xml; charset=utf-8');
            $xml = '<shop id="'.$shop->id.'" name="'.$shop->name.'" timestamp="'.date("Y-m-d H:i:s").'">
	<presentlist>';
            foreach($presentlist as $present)
            {
                $xml .= '
      <present id="'.$present->id.'">
			 <presentname>'.(str_replace("&","&#038;",$present->caption)).'</presentname>
			 <presentimage>'.GFConfig::BACKEND_PATH."views/media/user/".$present->media_path.'.jpg</presentimage>
		  </present>';
            }
            $xml .= '</presentlist>
</shop>';

            ob_end_clean();
            echo trimgf($xml);
        }

        else
        {

            // Generate present list
            $presentData = array();
            foreach($presentlist as $present)
            {
                $presentData[] = array("id" => $present->id,"name" => (str_replace("&","&#038;",$present->caption)),"image" => \GFConfig::BACKEND_URL."views/media/user/".$present->media_path.'.jpg');
            }

            // Generate data in array
            $presentData = array(
                "shop_id" => $shop->id,
                "shop_name" => $shop->name,
                "concept" => $cardshopSettings->concept_parent,
                "present_list" => $presentData
            );

            // Output
            header('Content-Type: text/json; charset=utf-8');
            echo json_encode($presentData);

        }
        
    }


    public function order()
    {

        if(isset($_POST["expire_date"]) && $_POST["expire_date"] === "03-01-2023") {
            $_POST["expire_date"] = "31-12-2022";
        }

        // Hardcoded for se shop 440
        if($_POST["shop_id"] == 7735) {
            $_POST["shop_id"] = 1832;
            $_POST["org_shop_id"] = 7735;
        }

        $this->mailLog("New order on webservice",print_r($_POST,true));

        // Get shop, check agains authorization
        if(!isset($_POST["shop_id"]) || intval($_POST["shop_id"]) <= 0) {
            $this->outputServiceError(30,"Could not find valid shop_id");
            return;
        }

        // Load cardshop
        try {
            $cardSettings = \CardshopSettings::find('all',array("conditions" => array("shop_id" => intval($_POST["shop_id"]))));
            $cardSettings = $cardSettings[0];
        } catch (\Exception $e) {
            $this->mailLog("New order - error - invalid shop id",print_r($_POST,true));
            $this->outputServiceError(31,"Invalid shop_id: ".$_POST["shop_id"]);
            return;
        }

        // Check access to the shop
        if(!$this->hasAuthCardshopSetting($cardSettings)) {
            $this->mailLog("New order - error - not authorized",print_r($_POST,true));
            $this->outputServiceError(31,"Not authorized to the provided shop id");
            return;
        }

       
        // Create from post
        $webOrder = new WebOrder();
        $webOrder->createFromPost($cardSettings->shop_id);
    }



    /**
     * CONCEPT AND SHOP ACTIONS
     */

    private function listConcepts()
    {
        // List all concepts
        $shops = \CardshopSettings::find("all");
        $concepts = array();

        // Loop shops to find concepts
        foreach($shops as $shop) {
            if($this->hasAuthCardshopSetting($shop)) {
                if(!isset($concepts[$shop->concept_parent])) {
                    $concepts[$shop->concept_parent] = $this->getConceptDetails($shop);
                }
            }
        }

        // Output
        $this->outputServiceSuccess(array("concepts" => array_values($concepts)));

    }

    private function shopMetadata($concept,$shops)
    {

        $isJGK = ($shops[0]->shop_id == 52 || $shops[0]->shop_id == 4668 || $shops[0]->shop_id == 7121);
        $privateExpireDate = $shops[0]->private_expire_date;

        $isDK = $shops[0]->language_code == 1;

        $expireDateList = \ExpireDate::find("all");
        $expireDateMap = array();



        foreach($expireDateList as $expireDate) {


/*
            if($expireDate->is_special_private == 1) {
                // Do not display special private weeks online!
            }
            else
*/


            if($isDK && $expireDate->is_special_private == 1) {

            }
            else if($expireDate->week_no == 50) {

                if($isJGK == true && $expireDate->is_jgk_50 == 1) {
                    // AKTIVER NEDENSTÅENDE LINJE NÅR UGE 50 SKAL VISES PÅ FORMULAR
                    $expireDateMap[$expireDate->week_no] = $expireDate->display_date;
                    //echo "IS 50 JGK: ".$expireDate->display_date;
                } else if($isJGK == false && $expireDate->is_jgk_50 == 0){
                    $expireDateMap[$expireDate->week_no] = $expireDate->display_date;
                    //echo "NOT 50 JGK: ".$expireDate->display_date;
                }
            }
            else if($expireDate->week_no == 0) {
                if($privateExpireDate == $expireDate->display_date || $privateExpireDate == $expireDate->expire_date->format("d-m-Y")) {
                    $expireDateMap[$expireDate->week_no] = $expireDate->display_date;
                }
            } else {
                $expireDateMap[$expireDate->week_no] = $expireDate->display_date;
            }
        }



        $values = array();
        $deadlines = array();
        $deadlinesParsed = false;
        $cardvalues = null;


        foreach($shops as $shop)
        {

             $bonusCards = null;

             // Bonus cards disabled again
            /*
             if($shop->bonus_presents != null && trimgf($shop->bonus_presents) != "") {
                $bonusCards = explode(",",$shop->bonus_presents);
             }
            */

            $values[] = array("shopid" => $shop->shop_id, "value" => intval($shop->card_price/100),"min_cards" => $shop->min_web_cards,"bonus_cards" => $bonusCards);

            // Add shop 7735 for bonus present 440 on 400 shop
            if($shop->shop_id == 1832 && $shop->bonus_presents != null) {

                $lastCardVal = $values[count($values)-1];
                if($lastCardVal["shopid"] == 1832) {
                    $lastCardVal["shopid"] = 7735;
                    $lastCardVal["value"] += $shop->bonus_presents;
                    $values[] = $lastCardVal;
                }

            }

            $now = new \DateTime('now');

            if($shop->card_values != null && trimgf($shop->card_values) != "") {
                $cardvalues = explode(",",$shop->card_values);
            }

            if($deadlinesParsed == false) {


                if($shop->language_code == 5) {
                    if($shop->private_open != null && $shop->private_open < $now && $shop->private_close != null && $shop->private_close_websale != null) {
                        $deadlineData = $this->generateDeadline(0,$expireDateMap,$shop->private_close_websale,$shop->physical_close_days,$shop->shop_id,$shop->language_code);
                        if($deadlineData != null) $deadlines[] = $deadlineData;
                    }
                }
                
                if($shop->week_47_open != null && $shop->week_47_open < $now && $shop->week_47_close != null && $shop->week_47_close_websale != null) {
                    $deadlineData = $this->generateDeadline(47,$expireDateMap,$shop->week_47_close_websale,$shop->physical_close_days,$shop->shop_id,$shop->language_code);
                    if($deadlineData != null) $deadlines[] = $deadlineData;
                }

                if($shop->week_48_open != null && $shop->week_48_open < $now && $shop->week_48_close != null && $shop->week_48_close_websale != null) {
                    $deadlineData = $this->generateDeadline(48,$expireDateMap,$shop->week_48_close_websale,$shop->physical_close_days,$shop->shop_id,$shop->language_code);
                    if($deadlineData != null) $deadlines[] = $deadlineData;
                }

                if($shop->week_49_open != null && $shop->week_49_open < $now && $shop->week_49_close != null && $shop->week_49_close_websale != null ) {
                    $deadlineData = $this->generateDeadline(49,$expireDateMap,$shop->week_49_close_websale,$shop->physical_close_days,$shop->shop_id,$shop->language_code);
                    if($deadlineData != null) $deadlines[] = $deadlineData;
                }

                if($shop->week_50_open != null && $shop->week_50_open < $now && $shop->week_50_close != null && $shop->week_50_close_websale != null) {
                    $deadlineData = $this->generateDeadline(50,$expireDateMap,$shop->week_50_close_websale,$shop->physical_close_days,$shop->shop_id,$shop->language_code);
                    if($deadlineData != null) $deadlines[] = $deadlineData;
                }

                if($shop->week_51_open != null && $shop->week_51_open < $now && $shop->week_51_close != null && $shop->week_51_close_websale != null) {
                    $deadlineData = $this->generateDeadline(51,$expireDateMap,$shop->week_51_close_websale,$shop->physical_close_days,$shop->shop_id,$shop->language_code);
                    if($deadlineData != null) $deadlines[] = $deadlineData;
                }


                if($shop->week_04_open != null && $shop->week_04_open < $now && $shop->week_04_close != null && $shop->week_04_close_websale != null) {
                    $deadlineData = $this->generateDeadline(5,$expireDateMap,$shop->week_04_close_websale,$shop->physical_close_days,$shop->shop_id,$shop->language_code);
                    if($deadlineData != null) $deadlines[] = $deadlineData;
                }


                if($shop->special_private1_open != null && $shop->special_private1_open < $now && $shop->special_private1_close != null && $shop->special_private1_close_websale != null) {

                    $deadlineData = $this->generateDeadline(49,$expireDateMap,$shop->special_private1_close_websale,$shop->physical_close_days,$shop->shop_id,$shop->language_code,true);
                    if($deadlineData != null) $deadlines[] = $deadlineData;
                }

                if($shop->special_private2_open != null && $shop->special_private2_open < $now && $shop->special_private2_close != null && $shop->special_private2_close_websale != null) {

                    $deadlineData = $this->generateDeadline(5,$expireDateMap,$shop->special_private2_close_websale,$shop->physical_close_days,$shop->shop_id,$shop->language_code,true);
                    if($deadlineData != null) $deadlines[] = $deadlineData;
                }


                if($shop->language_code == 1) {
                    if($shop->private_open != null && $shop->private_open < $now && $shop->private_close != null && $shop->private_close_websale != null) {
                        $deadlineData = $this->generateDeadline(0,$expireDateMap,$shop->private_close_websale,$shop->physical_close_days,$shop->shop_id,$shop->language_code);
                        if($deadlineData != null) $deadlines[] = $deadlineData;
                    }
                }

                if($shop->language_code == 4) {
                    if($shop->private_open != null && $shop->private_open < $now && $shop->private_close != null && $shop->private_close_websale != null) {

                        $deadlineData = $this->generateDeadline(98,$expireDateMap,$shop->private_close_websale,$shop->physical_close_days,$shop->shop_id,$shop->language_code);
                        if($deadlineData != null) $deadlines[] = $deadlineData;

                        $deadlineData = $this->generateDeadline(0,$expireDateMap,$shop->private_close_websale,$shop->physical_close_days,$shop->shop_id,$shop->language_code);
                        if($deadlineData != null) $deadlines[] = $deadlineData;

                    }
                }

                $deadlinesParsed = true;



            }

        }


        $responseData = array(
            "concept" => $this->getConceptDetails($shops[0]),
            "cards" => $values,
            "values" => $cardvalues,
            "use_giftwrap" => ($shops[0]->giftwrap_use == 1 || $shops[0]->giftwrap_use == 2),
            "use_cardfee" => ($shops[0]->cardfee_use == 1),
            "use_privatedelivery" => ($shops[0]->privatedelivery_use == 1),
            "use_namelabels" => ($shops[0]->namelabels_use == 1 || $shops[0]->namelabels_use == 2),
            "deadlines" => $deadlines,
            "service_prices" => array("giftwrap" => $shops[0]->giftwrap_price,"card_delivery" => $shops[0]->carddelivery_price,"private_delivery" => $shops[0]->privatedelivery_price,"card_fee" => $shops[0]->cardfee_price, "namelabel_fee" => $shops[0]->namelabels_price)
        );

        // Output
        $this->outputServiceSuccess($responseData);

    }

    private function generateDeadline($weekNo,$expireDateMap,$closeWebsale,$physicalCloseDays,$ShopID,$languageid,$special=false)
    {
        // Output all params for debug
        //echo "\r\n".$weekNo." - ".$closeWebsale->format("Y-m-d H:i")." - ".$physicalCloseDays." - ".$ShopID." - ".$languageid."\r\n";

        if(!isset($expireDateMap[$weekNo])) {
        //    echo "No expire date for week ".$weekNo;
            return null;
        }
        $now = new \DateTime('now');

        $closeWebSalePhysical = clone $closeWebsale;
        $closeWebSalePhysical = $closeWebSalePhysical->sub(new \DateInterval("P".intval($physicalCloseDays)."D"));

        /*
                echo "\r\n--".$physicalCloseDays."--\r\n";
                echo "\r\n1: "; echo $closeWebsale->format("Y-m-d H:i");
                echo "\r\n2: "; echo $now->format("Y-m-d H:i");
                echo "\r\n3: "; echo $closeWebsale->sub(new \DateInterval("P".intval($physicalCloseDays)."D"))->format("Y-m-d H:i");
                echo "\r\n4: "; echo $closeWebSalePhysical->format("Y-m-d H:i");
        */



        $expireDate = $expireDateMap[$weekNo];
        //echo "Expire date: ".$expireDate."\r\n";



        $weekNoLabel = $weekNo;

        //if($weekNo == 4 && $languageid == 4) {
            //$weekNoLabel = 5;
            //if($expireDate == "31-12-2022") { // in_array($ShopID,array(272,57,58,59)) &&
            //    $expireDate = "03-01-2023";
            //}
        //}


        if($weekNo == 5 && $languageid == 4) {
            $weekNoLabel = 4;
            //$weekNo = 0;
            //if($expireDate == "31-12-2022") { // in_array($ShopID,array(272,57,58,59)) &&
            //    $expireDate = "03-01-2023";
           // }
        }

        $forceClosePhysical = false;
        $specialPrivate2 = false;

        if($special) {
            $specialPrivate2 = true;
            $weekNo = 0;
            $forceClosePhysical = true;
        }

        //$forceClosePhysical = ($weekNo == 50 && $ShopID == 52);
        //if($languageid == 4) $forceClosePhysical = true;
        
        if($forceClosePhysical == true) {
            $closeWebSalePhysical = $closeWebSalePhysical->sub(new \DateInterval("P".(365)."D"));
        }


        return array("week" => $weekNoLabel,"date" => $expireDate,"is_home_delivery" => ($weekNo == 0 ? true : false),"email_open" => ($closeWebsale < $now ? false : true),"physical_open" => ($closeWebSalePhysical < $now ? false : true),"close_email" => $closeWebsale->format("Y-m-d H:i:s"),"close_physical" => $closeWebSalePhysical->format("Y-m-d H:i:s"),"special_private2" => $specialPrivate2);
    }

    private function shopPresents($shop_id)
    {
        // TODO - List shop presents
    }

    /**
     * DATA HELPERS
     */

    private function getConceptDetails($shop)
    {
        return array("concept" => $shop->concept_parent,"name" => $this->conceptName($shop->concept_parent),"country" => substr($shop->concept_parent,0,2));
    }

    private function getShopsByConcept($concept)
    {

        //$shopList = \CardshopSettings::find("all",array("conditions" => array("concept_parent" => $concept)));
        $shopList = \CardshopSettings::find_by_sql("SELECT * FROM cardshop_settings WHERE concept_parent = '".$concept."' and shop_id not in (8355,8356,8357,8358,8360) ORDER BY card_price ASC");
        $validShopList = array();

        foreach($shopList as $shop) {
            if($this->hasAuthCardshopSetting($shop)) {
                $validShopList[] = $shop;
            }
        }

        return $validShopList;
    }

    /**
     * METADATA
     */

    private function conceptName($concept)
    {
        switch ($concept) {
            case "DK24G":
                return "24 Gaver";
            case "DKDESIGN":
                return "Design julegaven";
            case "DKDROM":
                return "Drømmegavekortet";
            case "DKGULD":
                return "Guldgavekortet";
            case "DKGRON":
                return "Det grønne gavekort";
            case "DKJGK":
                return "Julegavekortet";
            case "NOBRA":
                return "BRA Gavekort";
            case "NOGULD":
                return "Gullgavekort";
            case "NOJG":
                return "Julegavekort";
            case "SEJK":
                return "24 Julklappar";
            case "LUKS":
                return "Luksusgavekortet";
            case "NOLUKS":
                return "Luksuskortet";
            case "DKJGV":
                return "Julegavevalget";
            case "SESOM":
                return "Sommarpresent";
            default:
                return "Ukendt koncept";
        }
    }



}
