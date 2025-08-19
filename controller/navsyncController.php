<?php

/**
 *
 * This controller runs the navision sync
 * It syncs from company_order table and uses the navsync_status field to determine what to sync and to write result of sync
 *
 * Meaning of company_order.navsync_status
 * 0: Waiting - Not synced yet
 * 1: Syncing - Is currently syncing
 * 2: Block - Should not be synced
 * 3: Synced - Has been synced sucessfully - response written in navsync_response
 *
 * 10: DB Error - Could not load company_order or company
 * 12: JSON Encode error - Could not encode order data to json
 * 20: SOAP Client error - Got exception from the soap client
 * 22: SOAP Unexpected return error - Did not get the expected return message from ws
 *
 * After sync navsync_date will always be updated no matter the status code
 * The queue will only run navsync_status = 0
 *
 */
     
  function mailProblem($subject,$content)
  {
    $body ="";
    $modtager = "sc@interactive.dk";
    $message = "Navsync problem:<br>".$content;
  	$headers = "From: noreply@julegavekortet.dk <noreply@julegavekortet.dk>" . "\r\n";
    $headers .= "Reply-To:  <noreply@julegavekortet.dk>\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8";
  	$result = mailgf($modtager, $subject, $message, $headers);
  }


class navsyncRunner
{

    /** CLASS MEMBERS **/
    private $lastErrorType = 0;
    private $lastErrorMessage = "";
    private $lastReturnValue = "";

    private $requestBody = "";
    private $responseBody = "";

    /** SYNC HELPERS **/

    public function getRequestBody()
    {
        return $this->requestBody;
    }

    public function getResponseBodyObject()
    {
        return $this->responseBody;
    }

    public function getResponseBody()
    {
        return print_r($this->responseBody, true);
    }

    /** STATUS RETURN **/
    public function getReturnValue()
    {
        return $this->lastReturnValue;
    }

    public function getLastErrorType()
    {
        return $this->lastErrorType;
    }

    public function getLastErrorMessage()
    {
        return $this->lastErrorMessage;
    }

    private function setErrorMessage($companyorder, $errorType, $errorMessage,$sendMail=true)
    {

        // Set on object
        $companyorder->navsync_status = $errorType;
        $companyorder->navsync_date = date('d-m-Y H:i:s');
        $companyorder->navsync_error = $errorMessage;
        $companyorder->save();
        System::connection()->commit();
        System::connection()->transaction();

        // Set error message and type
        $this->lastErrorType = $errorType;
        $this->lastErrorMessage = $errorMessage;
        
        // Mail
        if($sendMail) {
            mailProblem("Navsync error ".$errorType,"Error ".$errorType.": ".$errorMessage."<br>Company order: ".$companyorder->id);
        }
        
        return false;

    }
    
    private function setLevErrorMessage($shopuser, $errorType, $errorMessage)
    {

        // Set on object
        if($shopuser != null)
        {
          $shopuser->navsync_status = $errorType;
          $shopuser->navsync_date = date('d-m-Y H:i:s');
          $shopuser->navsync_error = $errorMessage;
          $shopuser->save();
          System::connection()->commit();
          System::connection()->transaction();
        }
        
        // Set error message and type
        $this->lastErrorType = $errorType;
        $this->lastErrorMessage = $errorMessage;
        
        // Mail
        if($errorType != 16) {
            mailProblem("Navsync levering error ".$errorType,"Error ".$errorType.": ".$errorMessage."<br>Shopuser order: ".($shopuser == null ? "" : $shopuser->id));
        }
        
        return false;

    }
    
    
   
    
   
    
    ///////////////////////
    // SYNC COMPANY ORDER
    ///////////////////////

    public function getOrderData(CompanyOrder $co,Company $c)
    {

      // Find week no
      $expiredate = ExpireDate::all(array('conditions' => array('expire_date' => $co->expire_date->format('Y-m-d')), 'limit' => 1));
      if(count($expiredate) == 0) 
      {
           mailProblem("Navsync error expiredate","Error: Could not resolse expire date to week ".$co->expire_date->format('Y-m-d')."<br>Company order: ".$co->id);
      }
      $week_no = $expiredate[0]->week_no;

      // Prepare product list
      $products = array();
      $productstring = $co->earlyorderlist;
      $productsplit = explode("\n",$productstring);
      for($i=0;$i<count($productsplit);$i++)
      {
        if(trimgf($productsplit[$i]) != "")
        {
          $products[] = array("varenr" => trimgf($productsplit[$i]),"amount" => 1);
        }
      }
      
      $mailsent = 0;
      if($co->id >= 280 && $co->id <= 540) $mailsent = 1;

      // Check if has parent
      if($c->pid > 0) {
          $parentco = Company::find($c->pid);
          if($parentco instanceof Company && $parentco->id > 0 && $parentco->id == $c->pid) {

              $co->company_name = $parentco->name;
              $co->cvr = $parentco->cvr;
              $co->ean = $parentco->ean;
              $c->bill_to_address = $parentco->bill_to_address;
              $c->bill_to_address_2 = $parentco->bill_to_address_2;
              $c->bill_to_postal_code = $parentco->bill_to_postal_code;
              $c->bill_to_city = $parentco->bill_to_city;
              $c->bill_to_country = $parentco->bill_to_country;
              $c->bill_to_email = $parentco->bill_to_email;

          }
      }

        // Prepare params
        $orderData = array(
            "id" => $co->id,
            "order_no" => $co->order_no,
            "company_id" => $co->company_id,
            "company_name" => $co->company_name,
            "shop_id" => $co->shop_id,
            "shop_name" => substr($co->shop_name,0,20),
            "salesperson" => $co->salesperson,
            "salenote" => $co->salenote,
            "quantity" => $co->quantity,
            "expire_date" => $co->expire_date->format('Y-m-d'),
            "week_no" => $week_no,
            "certificate_no_begin" => $co->certificate_no_begin,
            "certificate_no_end" => $co->certificate_no_end,
            "certificate_value" => intval($co->certificate_value),
            "ship_to_company" => $co->ship_to_company,
            "ship_to_address" => $co->ship_to_address,
            "ship_to_address2" => $co->ship_to_address_2,
            "ship_to_postal_code" => $co->ship_to_postal_code,
            "ship_to_city" => $co->ship_to_city,
            "contact_name" => $co->contact_name,
            "contact_email" => $co->contact_email,
            "contact_phone" => $co->contact_phone,
            "spdeal" => $co->spdeal,
            "spdealtxt" => $co->spdealtxt,
            "cvr" => trimgf(str_replace(" ","",$co->cvr)),
            "ean" => $co->ean,
            "phone" => $c->phone,
            "is_appendix_order" => $co->is_appendix_order,
            "is_email" => $co->is_email,
            "giftwrap" => $co->giftwrap,
            "so_nr" => $c->so_no,
            "bill_to_address" => $c->bill_to_address,
            "bill_to_address_2" => $c->bill_to_address_2,
            "bill_to_postal_code" => $c->bill_to_postal_code,
            "bill_to_city" => $c->bill_to_city,
            "bill_to_country" => $c->bill_to_country,
            "bill_to_email" => $c->bill_to_email,
            "created_datetime" => $co->created_datetime->format('Y-m-d H:i:s'),
            "modified_datetime" => $co->modified_datetime->format('Y-m-d H:i:s'),
            "ordernote" => $co->ordernote,
            "earlyorder" => intval($co->earlyorder) == 1 ? 1 : 0,
            "requisition_no" => trimgf($co->requisition_no),
            "mailsent" => $mailsent,
            "products" => $products,
            "carry_up" => $co->gift_spe_lev,
            "shipment_token" => ($co->shipment_token == null ? "" : $co->shipment_token)     
        );

        // Utf8 encode all strings
        foreach ($orderData as $key => $val) {
            if (is_string($val)) {
                $orderData[$key] = trimgf($val);
            }
        }

      //echo "<pre>".print_r($orderData,true)."</pre>";
   
        return $orderData;

    }

    public function syncCompanyOrder($companyorder)
    {

      
        // Check company order
        if ($companyorder == null || !isset($companyorder->id) || intval($companyorder->id) <= 0)
            return $this->setErrorMessage($companyorder, 10, "Could not find companyorder");

        // Find and check company
        $c = Company::find($companyorder->company_id);
        if ($c == null || !isset($c->id) || intval($c->id) <= 0)
            return $this->setErrorMessage($companyorder, 10, "Could not load company with id: " . $companyorder->company_id . " on companyorder: " . $companyorder->id);


        //  Set lang by shop
        $dkShops = array(52,575,54,55,56,53,287,290,310,247,248);
        $noShops = array(272,57,58,59,574);
        $seShops = array(1832,1981,4793,5117,8271);
        if(in_array($companyorder->shop_id,$dkShops)) $c->language_code = 1;
        else if(in_array($companyorder->shop_id,$noShops)) $c->language_code = 4;
        else {
            if(in_array($companyorder->shop_id,$seShops)) {
                return $this->setErrorMessage($companyorder, 11, "SE order: " . $companyorder->id,false);
            } else {
                return $this->setErrorMessage($companyorder, 11, "No language code set on companyorder: " . $companyorder->id);
            }
        }
        
        // Check language
        if($c->language_code != 1 && $c->language_code != 4)
        {
           
            return $this->setErrorMessage($companyorder, 11, "No language code set on companyorder: " . $companyorder->id);
        }
        
        if($companyorder->is_cancelled == 1)
        {
           return $this->setErrorMessage($companyorder, 13, "Order is cancelled");
        }

        // Check syncstatus
        if ($companyorder->navsync_status != 0) {
            $this->lastErrorMessage = "Could not be synced, status is " . $companyorder->navsync_status;
            $this->lastErrorType = $companyorder->navsync_status;
            return false;
        }
        
        // Set company bill_to_country
        if(trimgf($c->bill_to_country) == "")
        {
          $c->bill_to_country = ($c->language_code == 4 ? "Norge" : "Danmark"); 
        }
        
        // Check if company is parent
        $childList = Company::find_by_sql("SELECT * FROM company WHERE pid > 0 && pid = ".intval($companyorder->company_id));
        $isParent = countgf($childList) > 0;
        
        // Check token
        if($isParent && $companyorder->is_email == 0 && trimgf($companyorder->shipment_token) == "") {
            $shipmentToken = substr(bin2hex(openssl_random_pseudo_bytes(8)).$companyorder->id.bin2hex(openssl_random_pseudo_bytes(10)),0,22);
            $companyorder->shipment_token = $shipmentToken;
        }
        
        // Set sync start
        $companyorder->navsync_status = 1;
        $companyorder->navsync_date = date('d-m-Y H:i:s');
        $companyorder->save();
        System::connection()->commit();
        System::connection()->transaction();

        // Find order data
        $orderData = $this->getOrderData($companyorder,$c);
        if ($orderData === false) return false;

        // Prepare request params
        $orderData["shop_name"] = utf8_encode($orderData["shop_name"]);
        $requestData = array("request" => $orderData);
        
        $requestJSON = json_encode($requestData);
        $params = array("request" => $requestJSON);

        if ($requestJSON == null || $requestJSON == "") 
        {
          mailProblem("Could not encode order data","Order ".$companyorder->id." could not be encoded:<br>".print_r(array("data" => $requestData,"json" => $requestJSON),true));
          return $this->setErrorMessage($companyorder, 12, "Could not encode companyorder data to json");
        }
        
        // Prepare soap client
        $wsdlUrl = \GFCommon\Model\Navision\NavClient::getNavisionUrl($c->language_code,"GavekortWS");
        if($wsdlUrl === null) {
            return $this->setErrorMessage($companyorder, 11, "Not valid language code ".$c->language_code." on order id ".$companyorder->id);
        }

        echo "Syncing to ".$wsdlUrl." - order ".$companyorder->id."\r\n\r\n";

        $options = array('login' => GFConfig::NAVISION_USERNAME, 'password' => GFConfig::NAVISION_PASSWORD, 'trace' => true);
        $client = new SoapClient($wsdlUrl, $options);

        // Call navision sync
        try {
            $response = @$client->__soapCall("CreateOrder", array(array("request" => $requestJSON)));
        } catch (Exception $e) {
             print_r($client->__getLastRequest());
            return $this->setErrorMessage($companyorder, 20, "WS Exception: " . $e->getMessage());
        }

        // Get request body
        $this->requestBody = $client->__getLastRequest();
        $this->responseBody = $response;

        // Get response text
        if ($response == null || !property_exists($response, "return_value") || trimgf($response->return_value) == "") {
            return $this->setErrorMessage($companyorder, 22, "WS Response unknown: " . print_r($response, true));
        }

        // Set return value
        $this->lastReturnValue = trimgf($response->return_value);

        // Set success
        $companyorder->navsync_status = 3;
        $companyorder->navsync_date = date('d-m-Y H:i:s');
        $companyorder->navsync_response = $this->lastReturnValue;
        $companyorder->save();
        
        // Update navision debitor number on company
        if(intval($this->lastReturnValue) > 0)
        {
          $c->nav_debitor_no = intval($this->lastReturnValue);
          $c->save();
        }

        //$this->closePrivatlevering($companyorder);
        
        System::connection()->commit();
        System::connection()->transaction();
                       
        return true;

    }

    private function closePrivatlevering($companyorder) {

        $expiredate = ExpireDate::all(array('conditions' => array('expire_date' => $companyorder->expire_date->format('Y-m-d')), 'limit' => 1));
        if($companyorder->id > 0 && countgf($expiredate)  > 0 && $expiredate[0]->is_delivery == 1) {

            // Find shopusers
            $shopuserList = ShopUser::all(array('company_order_id' => $companyorder->id,'is_demo'=>0,"is_delivery" => 1));

            if(count($shopuserList) > 0) {

                foreach($shopuserList as $shopUser) {
                    $shopUser->blocked = 1;
                    $shopUser->save();
                }

                // Update company order
                    $companyorder->nav_levering_blocked = 1;

            }
        }

    }


    public function syncCompanyOrderID($companyorderid)
    {
        $companyorder = CompanyOrder::find(intval($companyorderid));
        return $this->syncCompanyOrder($companyorder);
    }



    /////////////////////////////
    /// SPECIAL SYNC
    /////////////////////////////
    ///

    public function syncSpecialCompanyOrderID($companyorderid)
    {
        $companyorder = CompanyOrder::find(intval($companyorderid));
        return $this->syncSpecialCompanyOrder($companyorder);
    }

    public function syncSpecialCompanyOrder($companyorder)
    {


        // Check company order
        if ($companyorder == null || !isset($companyorder->id) || intval($companyorder->id) <= 0)
            return $this->setErrorMessage($companyorder, 10, "Could not find companyorder");

        // Find and check company
        $c = Company::find($companyorder->company_id);
        if ($c == null || !isset($c->id) || intval($c->id) <= 0)
            return $this->setErrorMessage($companyorder, 10, "Could not load company with id: " . $companyorder->company_id . " on companyorder: " . $companyorder->id);


        //  Set lang by shop
        $dkShops = array(52,575,54,55,56,53,287,290,310,247,248);
        $noShops = array(272,57,58,59,574);
        $seShops = array(1832,1981,4793,5117,8271);
        if(in_array($companyorder->shop_id,$dkShops)) $c->language_code = 1;
        else if(in_array($companyorder->shop_id,$noShops)) $c->language_code = 4;
        else {
            if(in_array($companyorder->shop_id,$seShops)) {
                return $this->setErrorMessage($companyorder, 11, "SE order: " . $companyorder->id,false);
            } else {
                return $this->setErrorMessage($companyorder, 11, "No language code set on companyorder: " . $companyorder->id);
            }
        }

        // Check language
        if($c->language_code != 1 && $c->language_code != 4)
        {

            return $this->setErrorMessage($companyorder, 11, "No language code set on companyorder: " . $companyorder->id);
        }

        if($companyorder->is_cancelled == 1)
        {
            return $this->setErrorMessage($companyorder, 13, "Order is cancelled");
        }

        /*
        // Check syncstatus
        if ($companyorder->navsync_status != 0) {
            $this->lastErrorMessage = "Could not be synced, status is " . $companyorder->navsync_status;
            $this->lastErrorType = $companyorder->navsync_status;
            return false;
        }
        */

        $bstoso = array("BS43188" => "260814",
            "BS42383" => "259187",
            "BS40658" => "255493",
            "BS41596" => "257747",
            "BS48103" => "270009",
            "BS47553" => "268961",
            "BS47264" => "268201",
            "BS47268" => "268205",
            "BS51609" => "277840",
            "BS51416" => "277243",
            "BS51293" => "276787",
            "BS47888" => "269538",
            "BS49737" => "273505",
            "BS48456" => "270575",
            "BS48457" => "270577",
            "BS50019" => "273828",
            "BS44407" => "263039",
            "BS40541" => "255788",
            "BS49088" => "272231",
            "BS50194" => "274282",
            "BS49375" => "272787",
            "BS43897" => "262115",
            "BS50503" => "275093",
            "BS42189" => "258858",
            "BS47143" => "268066",
            "BS43226" => "260871",
            "BS49278" => "272707",
            "BS50072" => "274071",
            "BS51003" => "275944",
            "BS41091" => "256538",
            "BS41256" => "256962",
            "BS49272" => "272701",
            "BS43954" => "262244",
            "BS43955" => "262257",
            "BS40568" => "255371",
            "BS44157" => "262615",
            "BS44158" => "262616",
            "BS44160" => "262618",
            "BS44165" => "262625",
            "BS50864" => "275757",
            "BS45562" => "265108",
            "BS50289" => "274490",
            "BS51456" => "277348",
            "BS51038" => "275995",
            "BS50393" => "274624",
            "BS49430" => "272935",
            "BS45774" => "266309",
            "BS45773" => "265530",
            "BS45771" => "265528",
            "BS45772" => "265529",
            "BS47542" => "268989",
            "BS45770" => "265527",
            "BS47539" => "268987",
            "BS47541" => "268988"
        );

        // Set company bill_to_country
        if(trimgf($c->bill_to_country) == "")
        {
            $c->bill_to_country = ($c->language_code == 4 ? "Norge" : "Danmark");
        }

        // Check if company is parent
        $childList = Company::find_by_sql("SELECT * FROM company WHERE pid > 0 && pid = ".intval($companyorder->company_id));
        $isParent = countgf($childList) > 0;

        // Check token
        if($isParent && $companyorder->is_email == 0 && trimgf($companyorder->shipment_token) == "") {
            $shipmentToken = substr(bin2hex(openssl_random_pseudo_bytes(8)).$companyorder->id.bin2hex(openssl_random_pseudo_bytes(10)),0,22);
            $companyorder->shipment_token = $shipmentToken;
        }

        // Set sync start
        /*
        $companyorder->navsync_status = 1;
        $companyorder->navsync_date = date('d-m-Y H:i:s');
        $companyorder->save();
        System::connection()->commit();
        System::connection()->transaction();
        */

        // Find order data
        $orderData = $this->getOrderData($companyorder,$c);
        if ($orderData === false) return false;

        if(!isset($bstoso[$orderData["order_no"]])) {
            return $this->setErrorMessage($companyorder, 12, "BS nr. ".$orderData["order_no"]." not found in so nr list");
        } else
        {
            $orderData["so_nr"] = "SO".$bstoso[$orderData["order_no"]];
        }

        // Prepare request params
        $orderData["shop_name"] = utf8_encode($orderData["shop_name"]);
        $requestData = array("request" => $orderData);

        $requestJSON = json_encode($requestData);
        $params = array("request" => $requestJSON);

        if ($requestJSON == null || $requestJSON == "")
        {
            mailProblem("Could not encode order data","Order ".$companyorder->id." could not be encoded:<br>".print_r(array("data" => $requestData,"json" => $requestJSON),true));
            return $this->setErrorMessage($companyorder, 12, "Could not encode companyorder data to json");
        }

        // Prepare soap client
        $wsdlUrl = \GFCommon\Model\Navision\NavClient::getNavisionUrl($c->language_code,"GavekortWS");
        if($wsdlUrl === null) {
            return $this->setErrorMessage($companyorder, 11, "Not valid language code ".$c->language_code." on order id ".$companyorder->id);
        }

        echo "Syncing to ".$wsdlUrl." - order ".$companyorder->id."\r\n\r\n";

        $options = array('login' => GFConfig::NAVISION_USERNAME, 'password' => GFConfig::NAVISION_PASSWORD, 'trace' => true);
        $client = new SoapClient($wsdlUrl, $options);

        echo "SYNCING <pre>".print_r($requestJSON)."</pre>";
 

        // Call navision sync
        try {
            $response = @$client->__soapCall("CreateOrder", array(array("request" => $requestJSON)));
        } catch (Exception $e) {
            print_r($client->__getLastRequest());
            return $this->setErrorMessage($companyorder, 20, "WS Exception: " . $e->getMessage());
        }

        // Get request body
        $this->requestBody = $client->__getLastRequest();
        $this->responseBody = $response;

        var_dump($response);

        // Get response text
        if ($response == null || !property_exists($response, "return_value") || trimgf($response->return_value) == "") {
            return $this->setErrorMessage($companyorder, 22, "WS Response unknown: " . print_r($response, true));
        }

        // Set return value
        $this->lastReturnValue = trimgf($response->return_value);

        // Set success
        /*
        $companyorder->navsync_status = 3;
        $companyorder->navsync_date = date('d-m-Y H:i:s');
        $companyorder->navsync_response = $this->lastReturnValue;
        $companyorder->save();
        */
        // Update navision debitor number on company
        if(intval($this->lastReturnValue) > 0)
        {
            /*
            $c->nav_debitor_no = intval($this->lastReturnValue);
            $c->save();
            */
        }

        //$this->closePrivatlevering($companyorder);

        System::connection()->commit();
        System::connection()->transaction();

        return true;

    }
    
     ///////////////////////
    // SYNC PRIVATLEVERING
    ///////////////////////
    
    public function syncShopUserID($shopuserid)
    {
    
        echo "Start syncing levering";
    
        // Load and check shopuser
        $shopuser = ShopUser::find($shopuserid);
        if($shopuser == null || $shopuserid <= 0 || $shopuser->id <= 0)
        {
          return $this->setLevErrorMessage(null, 10, "Could not find shopuser ".$shopuserid);
        }
        else if($shopuser->navsync_status != 0)
        {
            return $this->setLevErrorMessage(null, 10, "Cant sync shopuser ".$shopuserid." sync status i not 0, but is: ".$shopuser->navsync_status);
        }
        else if($shopuser->blocked == 1)
        {
          return $this->setLevErrorMessage($shopuser, 11, "Cant sync shopuser ".$shopuserid.", shopuser blocked");
        }
        else if($shopuser->is_demo == 1)
        {
          return $this->setLevErrorMessage($shopuser, 12, "Cant sync shopuser ".$shopuserid.", is demo");
        }
        else if($shopuser->is_delivery == 0)
        {
          return $this->setLevErrorMessage($shopuser, 12, "Cant sync shopuser ".$shopuserid.", is not delivery");
        }
        /*
        if($shopuser->company_id == 12870) {
              return $this->setLevErrorMessage($shopuser, 12, "Cant sync shopuser ".$shopuserid.", blocked for now");                                                                                                  
        }
          */
        // Set sync start
        $shopuser->navsync_status = 1;
        $shopuser->navsync_date = date('d-m-Y H:i:s');
        $shopuser->save();
        System::connection()->commit();
        System::connection()->transaction();
        
        // Load order
        $orderList = Order::all(array('conditions' => array('shopuser_id' => $shopuser->id), 'limit' => 1));
        if(count($orderList) == 0)
        {
            return $this->setLevErrorMessage(null, 10, "Cant sync shopuser ".$shopuserid.", no order is found");
        }
        
        $order = $orderList[0];
        if($order->shopuser_id != $shopuser->id) return $this->setLevErrorMessage($shopuser, 13, "Cant sync shopuser ".$shopuserid.", error in shopuser id");
        else if($order->shop_id != $shopuser->shop_id) return $this->setLevErrorMessage($shopuser, 13, "Cant sync shopuser ".$shopuserid.", error in shop id");
        else if($order->company_id != $shopuser->company_id) return $this->setLevErrorMessage($shopuser, 13, "Cant sync shopuser ".$shopuserid.", error in company id");
        //else if($order->is_delivery != 1) return $this->setLevErrorMessage($shopuser, 13, "Cant sync shopuser ".$shopuserid.", order is not set to delivery");
        else if($order->is_demo != 0) return $this->setLevErrorMessage($shopuser, 13, "Cant sync shopuser ".$shopuserid.", order is set to demo");
        
        $presentName = explode("###",$order->present_model_name);
        if(count($presentName) == 0) return $this->setLevErrorMessage($shopuser, 13, "Cant sync shopuser ".$shopuserid.", no present name on order");
        if(count($presentName) < 2) $presentName[1] = "";
        
        // Load shop
        $shop = Shop::find($shopuser->shop_id);
        if($shop->id <= 0) return $this->setLevErrorMessage($shopuser, 14, "Cant sync shopuser ".$shopuserid.", could not find shop");
           
        // Set and check language by shop
        $dkShops = array(52,575,54,55,56,53,287,290,310,247,248);
        $noShops = array(272,57,58,59,574);
        $seShops = array(1832,1981,4793,5117,8271);
        
        if(in_array($shopuser->shop_id,$dkShops)) $order->language_id = 1;
        else if(in_array($shopuser->shop_id,$noShops)) {
            $order->language_id = 4;
            return $this->setLevErrorMessage($shopuser, 16, "Cant sync shopuser ".$shopuserid.", NO user ignored");
        }
        else if(in_array($shopuser->shop_id,$seShops)) {
            $order->language_id = 5;
            return $this->setLevErrorMessage($shopuser, 16, "Cant sync shopuser ".$shopuserid.", SE user ignored");
        }
        
        if($order->language_id != 1 && $order->language_id != 4)
        {
            return $this->setLevErrorMessage($shopuser, 15, "Cant sync shopuser ".$shopuserid.", could not find language");
        }
        
        // Load user attributes
        $userattributes = UserAttribute::all(array('conditions' => array('shopuser_id' => $shopuser->id)));
        $userdatamap = array();
        if(count($userattributes) > 0)
        {
          foreach($userattributes as $ua)
          {
              $userdatamap[$ua->attribute_id] = $ua->attribute_value;
          }
        }
        else return $this->setLevErrorMessage($shopuser, 17, "Cant sync shopuser ".$shopuserid.", no attributes on shopuser"); 
        
        // Init userdata
        $userdata = array(
          "name" => "",
          "ship_to_address" => "",
          "ship_to_address2" => "",
          "ship_to_postal_code" => "",
          "ship_to_city" => "",
          "ship_to_country" => $order->language_id == 4 ? "Norge" : "Danmark",
          "email" => "",
          "phone" => "",
        );
        
        // Define attributes with address data
        $adress1Attributes = array(751,588,596,604);
        $adress2Attributes = array(752,589,597,605);
        $zipAttributes = array(753,590,598,606);
        $cityAttributes = array(754,591,599,607);
        $phoneAttributes = array(767,761,763,765);
        
        // Load shop attributes
        $shopattributes = ShopAttribute::all(array('conditions' => array('shop_id' => $shopuser->shop_id)));
        if(count($shopattributes) > 0)
        {
            foreach($shopattributes as $sa)
            {
               
               $id = $sa->id;
               $value = isset($userdatamap[$id]) ? $userdatamap[$id] : "";
               
               if($sa->is_name == 1)
               {
                  $userdata["name"] = $value; 
               }
               else if($sa->is_email == 1)
               {
                  $userdata["email"] = $value; 
               }
               else if(in_array($id,$adress1Attributes))
               {
                    $userdata["ship_to_address"] = $value;
               }  
               else if(in_array($id,$adress2Attributes))
               {
                    $userdata["ship_to_address2"] = $value;
               }   
               else if(in_array($id,$zipAttributes))
               {
                    $userdata["ship_to_postal_code"] = $value;
               }  
               else if(in_array($id,$cityAttributes))
               {
                    $userdata["ship_to_city"] = $value;
               }  
               else if(in_array($id,$phoneAttributes))
               {
                    $userdata["phone"] = $value;
               }  
              
            }
        }
        else return $this->setLevErrorMessage($shopuser, 17, "Cant sync shopuser ".$shopuserid.", no attributes on shop"); 
        
        // Check userdata
        if($userdata["name"] == "" && $userdata["ship_to_address"] == "")
        {
          return $this->setLevErrorMessage($shopuser, 18, "Cant sync shopuser ".$shopuserid.", no delivery data"); 
        }
        
        $products = array();
        $varnrLinjer = array($order->present_model_present_no);
        if(strstr($order->present_model_present_no,"***"))
        {
          $split = explode("***",$order->present_model_present_no);
          $varnrLinjer = array();
          if(count($split) > 0)
          {
            foreach($split as $splitnr)
            {
              if(trimgf($splitnr) != "")
              {
                 $varnrLinjer[] = trimgf($splitnr);
              }
            }
          }
        }
        
        foreach($varnrLinjer as $varenr)
        {
           $products[] = array(
              "varenr" => $varenr,
              "present" => utf8_decode(substr(utf8_encode($presentName[0]),0,49)),
              "model" => utf8_decode(substr(utf8_encode($presentName[1]),0,49)),
              "shop" => $shop->name,
              "username" => $shopuser->username,
              "amount" => 1,
              "is_giftcertificate" => $shopuser->is_giftcertificate,
              "giftcertificate_value" => $shop->card_value
            ); 
        }
        
        
        // Build data
        $syncData = array(
          "name" =>  $userdata["name"],
          "ship_to_address" =>  $userdata["ship_to_address"],
          "ship_to_address2" =>  $userdata["ship_to_address2"],
          "ship_to_postal_code" =>  $userdata["ship_to_postal_code"],
          "ship_to_city" =>  $userdata["ship_to_city"],
          "ship_to_country" =>  $userdata["ship_to_country"],
          "email" =>  $userdata["email"],
          "phone" =>  $userdata["phone"],
          "products" => $products          
        );
        
        /*
       
                    echo "<br>NAV DATA<br><pre>";
        print_r($requestData);
        echo "</pre><br>".json_last_error()."<br>";
        echo $requestJSON;
          */
        
        // Prepare request params
        $requestData = array("request" => $syncData);
        $requestJSON = json_encode($requestData);
        $params = array("request" => $requestJSON);
        
      
        
        if ($requestJSON == null || $requestJSON == "") return $this->setLevErrorMessage($shopuser, 19, "Cant sync shopuser ".$shopuserid.", cant encode request"); 

        //mailProblem("Nav privatlevering",$requestJSON);

        // Prepare soap client
        $wsdlUrl = \GFCommon\Model\Navision\NavClient::getNavisionUrl($order->language_id,"GavekortWS");
        if($wsdlUrl === null)
        {
            return $this->setLevErrorMessage($shopuser, 20, "Cant sync shopuser ".$shopuserid.", not valid language: ".$order->language_id); 
        }
        
        echo "Syncing to ".$wsdlUrl."\r\n\r\n";

        $options = array('login' => GFConfig::NAVISION_USERNAME, 'password' => GFConfig::NAVISION_PASSWORD, 'trace' => true);
        $client = new SoapClient($wsdlUrl, $options);
        
        // Call navision sync
        try {
            $response = @$client->__soapCall("CreatePrivateOrders", array(array("request" => $requestJSON)));
        } catch (Exception $e) {
             print_r($client->__getLastRequest());
             return $this->setLevErrorMessage($shopuser, 21, "WS Exception on shopuser ".$shopuserid.", ".$e->getMessage()); 
        }

        // Get request body
        $this->requestBody = $client->__getLastRequest();
        $this->responseBody = $response;
        
        // Get response text
        if ($response == null || !property_exists($response, "return_value") || trimgf($response->return_value) == "") {
            return $this->setLevErrorMessage($shopuser, 22, "WS Unknown response on shopuser ".$shopuserid.", ". print_r($response, true)); 
        }
        
        // Set return value
        $this->lastReturnValue = trimgf($response->return_value);
        
       
        // Set success
        $shopuser->navsync_status = 3;
        $shopuser->navsync_date = date('d-m-Y H:i:s');
        $shopuser->navsync_response = $this->lastReturnValue;
        $shopuser->save();
		
        // Commit and return success
        System::connection()->commit();
        System::connection()->transaction();
        
        //mailProblem("Nav privatlevering",$requestJSON);
        
        return true;
        
    }

}


/******************************
 *  COMPANY ORDERSYNC CLASS
 ******************************/

class navsyncQueue
{



    public function syncBatch($limit = 100, $debugMode = false)
    {

        $processSuccess = 0;
        $processFailure = 0;

        if ($limit <= 0) return;
        $orderqueue = CompanyOrder::all(array('conditions' => array('navsync_status' => 0), 'limit' => $limit, 'order' => 'id asc'));
        if (count($orderqueue) > 0) {
            foreach ($orderqueue as $companyorder) {
                $result = $this->syncCompanyOrderID($companyorder->id, $debugMode);
                if ($result) $processSuccess++;
                else $processFailure++;
            }

            if ($debugMode == true) {
                echo "\r\n<br>\r\nBATCH RUN " . $limit . " RUN RESULT<br>\r\nSUCCESS: " . $processSuccess . "<br>\r\nFAILURES: " . $processFailure . "<br>\r\n";
            }
        } else {
            if ($debugMode == true) echo "NO ORDERS TO PROCESS";
        }
    }

    public function syncCompanyOrderID($companyorderid, $debugMode = false)
    {

        if ($debugMode == true) {
            echo "\r\n<br>\r\nSYNCING COMPANYORDER: " . $companyorderid . "<br>\r\n";
        }

        $syncModel = new navsyncRunner();
        $result = $syncModel->syncCompanyOrderID($companyorderid);

        if ($debugMode == true) {

            if ($result) echo "ORDER HAS BEEN SYNCED - Return value is: " . $syncModel->getReturnValue() . "";
            else echo "ORDER COULD NOT BE SYNCED: [" . $syncModel->getLastErrorType() . "] " . $syncModel->getLastErrorMessage() . "";
/*
            echo "\r\n<br><br>\r\nREQUEST<br>\r\n";
            echo $syncModel->getRequestBody();
            echo "\r\n<br><br>\r\nRESPONSE<br>\r\n";
            echo $syncModel->getResponseBody();
            echo "\r\n\<br><br>r\n";
*/
        }

        return $result;
    }

}

/******************************
 *  PRIVATLEVERING SYNC CLASS
 ******************************/

class navsyncLeveringQueue
{
    public function syncBatch($limit = 100, $debugMode = false)
    {

        $processSuccess = 0;
        $processFailure = 0;
        if ($limit <= 0) return;
        
        $nextSync = $this->getNextLeveringSync($limit);
        if(count($nextSync) > 0)
        {
          foreach($nextSync as $sync)
          {
            $result = $this->syncShopUserID($sync->id, $debugMode);
            if ($result) $processSuccess++;
            else $processFailure++;
          }
        }
        
        if ($debugMode == true) {
          echo "\r\n<br>\r\nBATCH RUN LEVERING " . $limit . " RUN RESULT<br>\r\nSUCCESS: " . $processSuccess . "<br>\r\nFAILURES: " . $processFailure . "<br>\r\n";
        }
        
        //if($limit > 0)
        
    }
    
    

    public function getNextLeveringSync($limit=1)
     {
        $sql = "SELECT
            `shop_user`.`username`
            , `shop_user`.`id`
            , `shop_user`.`blocked`
            , `shop_user`.`is_delivery`
            , `shop_user`.`delivery_printed`
            , `shop_user`.`expire_date`
            , `order`.`present_name`
            , `order`.`present_model_name`
            , `order`.`shop_id`
        FROM
            `order`
            INNER JOIN `shop_user`
                ON (`order`.`shopuser_id` = `shop_user`.`id`)
        WHERE ( `shop_user`.`blocked` =0
            AND `shop_user`.`is_delivery` = 1
            AND `shop_user`.`delivery_print_date` IS NULL AND `shop_user`.`navsync_status` = 0 AND (`order`.order_timestamp < (NOW() - INTERVAL 26 HOUR))
             ) 

        ORDER BY `order`.`id` ASC LIMIT ".intval($limit);

        $nextPrivatLevering = ShopUser::find_by_sql($sql);
        if(count($nextPrivatLevering) > 0) return $nextPrivatLevering;
        else return array();
     }

    public function syncShopUserID($shopuserid, $debugMode = false)
    {

        if ($debugMode == true) {
            echo "\r\n<br>\r\nSYNCING SHOPUSER PRIVATLEVERING: " . $shopuserid . "<br>\r\n";
        }

        $syncModel = new navsyncRunner();
        $result = $syncModel->syncShopUserID($shopuserid);

        if ($debugMode == true) {

            if ($result) echo "LEVERING HAS BEEN SYNCED - Return value is: " . $syncModel->getReturnValue() . "";
            else echo "LEVERING COULD NOT BE SYNCED: [" . $syncModel->getLastErrorType() . "] " . $syncModel->getLastErrorMessage() . "";

            echo "\r\n<br><br>\r\nREQUEST<br>\r\n";
            echo $syncModel->getRequestBody();
            echo "\r\n<br><br>\r\nRESPONSE<br>\r\n";
            echo $syncModel->getResponseBody();
            echo "\r\n\<br><br>r\n";
        }

        return $result;
    }
}

class navsyncController Extends baseController
{


    public function runspecial()
    {
        //https://gavefabrikken.dk//gavefabrikken_backend/index.php?rt=navsync/runspecial&token=4kdRdmcst6fH
        $this->checkAccess();

        $syncModel = new navsyncRunner();
        $syncModel->syncSpecialCompanyOrderID(24858);

    }

    public function index()
    {
        $this->checkAccess();
        echo "No action on this endpoint!";
    }
    
    public function nav2021()
    {


        if(\router::$systemUser == null) {
            echo "NOT LOGGED IN!";
            exit();
        }

        $model = new \GFCommon\Model\Navision\Nav2021();
        $model->dispatch();
    
    }

    // Run orders

    public function runqueue()
    {
        // DISABLE FOR NOW
                        return;
        $this->checkAccess();
        $queueModel = new navsyncQueue();
        $queueModel->syncBatch(100, $this->isDebugMode());
    }

    public function runid()
    {
        // DISABLE FOR NOW
      return;

        $this->checkAccess();
        $id = isset($_GET["id"]) ? intval($_GET["id"]) : 0;
        if ($id == 0) {
            echo "NO ID PROVIDED!";
            return;
        }

        $queueModel = new navsyncQueue();
        $queueModel->syncCompanyOrderID($id, $this->isDebugMode());

    }
    
    public function checkid()
    {
      
        $this->checkAccess();
        $id = isset($_GET["id"]) ? intval($_GET["id"]) : 0;
        if ($id == 0) {
            echo "NO ID PROVIDED!";
            return;
        }

        $co = CompanyOrder::find($id);
        $c = Company::find($co->company_id);

        $queueModel = new navsyncRunner();
        $orderData = $queueModel->getOrderData($co,$c);
        //echo "<pre>".print_r($orderData,true)."</pre>";
        
    }



    // Run privatlevering
    
    public function runleveringid()
    {
        // disable for now
        return;
      $this->checkAccess();
      $id = isset($_GET["id"]) ? intval($_GET["id"]) : 0;
        if ($id == 0) {
            echo "NO ID PROVIDED!";
            return;
        }

        $queueModel = new navsyncLeveringQueue();
        $queueModel->syncShopUserID($id, $this->isDebugMode());
      
    }
    
    public function runleveringbatch()
    {

        // disable for now
        return;
        
      $this->checkAccess();
      $queueModel = new navsyncLeveringQueue();
      $queueModel->syncBatch(100, $this->isDebugMode());
    }
    
    // Check status
    
    public function check()
    {
    
      $since = time()-(60*20);
      $sql = "SELECT * FROM `company_order` WHERE (created_datetime < '" . date("Y-m-d", $since) . "' && navsync_status IN (0,1)) ";
      $errors = CompanyOrder::find_by_sql($sql);

      if(count($errors) > 0)
      {
        header("HTTP/1.0 500 Service down");
        echo countgf($errors)." WAITING!";
        exit();
	    }
      else echo "OK";
  
    }

    public function status()
    {
        $this->checkAccess();


        $lastDay = $this->getSyncCount(time() - 60 * 60 * 24);
        $lastWeek = $this->getSyncCount(time() - 60 * 60 * 24 * 7);
        $lastMonth = $this->getSyncCount(time() - 60 * 60 * 24 * 30);
        $lastTotal = $this->getSyncCount(0);

        ?><table style="width: 100%"><tr><td style="width: 50%;" valign=top>
        
        <h3>Navision sync status:</h3>

        <table style="width: 600px; border-collapse: collapse;" border=1>
            <tr>
                <td>Antal fordelt p� status</td>
                <td>Alle</td>
                <td>Sidste m�ned</td>
                <td>Sidste uge</td>
                <td>Sidste 24 timer</td>
            </tr>
            <tr>
                <td>0: Venter p� sync</td>
                <td><?php echo isset($lastTotal[0]) ? $lastTotal[0] : 0; ?></td>
                <td><?php echo isset($lastMonth[0]) ? $lastMonth[0] : 0; ?></td>
                <td><?php echo isset($lastWeek[0]) ? $lastWeek[0] : 0; ?></td>
                <td><?php echo isset($lastDay[0]) ? $lastDay[0] : 0; ?></td>
            </tr>
            <tr>
                <td>1: Behandler</td>
                <td><?php echo isset($lastTotal[1]) ? $lastTotal[1] : 0; ?></td>
                <td><?php echo isset($lastMonth[1]) ? $lastMonth[1] : 0; ?></td>
                <td><?php echo isset($lastWeek[1]) ? $lastWeek[1] : 0; ?></td>
                <td><?php echo isset($lastDay[1]) ? $lastDay[1] : 0; ?></td>
            </tr>
            <tr>
                <td>2: Blokkeret</td>
                <td><?php echo isset($lastTotal[2]) ? $lastTotal[2] : 0; ?></td>
                <td><?php echo isset($lastMonth[2]) ? $lastMonth[2] : 0; ?></td>
                <td><?php echo isset($lastWeek[2]) ? $lastWeek[2] : 0; ?></td>
                <td><?php echo isset($lastDay[2]) ? $lastDay[2] : 0; ?></td>
            </tr>
            <tr>
                <td>3: Sync OK</td>
                <td><?php echo isset($lastTotal[3]) ? $lastTotal[3] : 0; ?></td>
                <td><?php echo isset($lastMonth[3]) ? $lastMonth[3] : 0; ?></td>
                <td><?php echo isset($lastWeek[3]) ? $lastWeek[3] : 0; ?></td>
                <td><?php echo isset($lastDay[3]) ? $lastDay[3] : 0; ?></td>
            </tr>
            <tr>
                <td>10: Data DB error</td>
                <td><?php echo isset($lastTotal[10]) ? $lastTotal[10] : 0; ?></td>
                <td><?php echo isset($lastMonth[10]) ? $lastMonth[10] : 0; ?></td>
                <td><?php echo isset($lastWeek[10]) ? $lastWeek[10] : 0; ?></td>
                <td><?php echo isset($lastDay[10]) ? $lastDay[10] : 0; ?></td>
            </tr>
            <tr>
                <td>12: Data prepare error</td>
                <td><?php echo isset($lastTotal[12]) ? $lastTotal[12] : 0; ?></td>
                <td><?php echo isset($lastMonth[12]) ? $lastMonth[12] : 0; ?></td>
                <td><?php echo isset($lastWeek[12]) ? $lastWeek[12] : 0; ?></td>
                <td><?php echo isset($lastDay[12]) ? $lastDay[12] : 0; ?></td>
            </tr>
            <tr>
                <td>13: Data prepare error</td>
                <td><?php echo isset($lastTotal[13]) ? $lastTotal[13] : 0; ?></td>
                <td><?php echo isset($lastMonth[13]) ? $lastMonth[13] : 0; ?></td>
                <td><?php echo isset($lastWeek[13]) ? $lastWeek[13] : 0; ?></td>
                <td><?php echo isset($lastDay[13]) ? $lastDay[13] : 0; ?></td>
            </tr>
            <tr>
                <td>20: WS Call error</td>
                <td><?php echo isset($lastTotal[20]) ? $lastTotal[20] : 0; ?></td>
                <td><?php echo isset($lastMonth[20]) ? $lastMonth[20] : 0; ?></td>
                <td><?php echo isset($lastWeek[20]) ? $lastWeek[20] : 0; ?></td>
                <td><?php echo isset($lastDay[20]) ? $lastDay[20] : 0; ?></td>
            </tr>
            <tr>
                <td>22: WS Response error</td>
                <td><?php echo isset($lastTotal[22]) ? $lastTotal[22] : 0; ?></td>
                <td><?php echo isset($lastMonth[22]) ? $lastMonth[22] : 0; ?></td>
                <td><?php echo isset($lastWeek[22]) ? $lastWeek[22] : 0; ?></td>
                <td><?php echo isset($lastDay[22]) ? $lastDay[22] : 0; ?></td>
            </tr>
        </table>

        <h3>Seneste 100 syncs</h3>
        <table style="width: 100%; border-collapse: collapse;" border=1>
            <tr>
                <td>ID</td>
                <td>Order NO</td>
                <td>Status</td>
                <td>Sync date</td>
                <td>Response</td>
                <td>Error</td>
            </tr><?php

            $synclist = CompanyOrder::all(array('conditions' => array(), 'limit' => 100, 'order' => 'navsync_date desc'));
            foreach ($synclist as $co) {
                if ($co->navsync_date != null) {
                    ?>
                    <tr>
                    <td><?php echo $co->id; ?></td>
                    <td><?php echo $co->order_no; ?></td>
                    <td><?php echo $co->navsync_status; ?></td>
                    <td><?php echo $co->navsync_date->format('Y-m-d H:i:s'); ?></td>
                    <td><?php echo $co->navsync_response; ?></td>
                    <td><?php echo $co->navsync_error; ?></td>
                    </tr><?php
                }
            }

            ?>

        </table>
        
        </td><td style="width: 50%;" valign=top>

          <h3>Privatlevering sync</h3>
          
          <?php
          
          $model = new navsyncLeveringQueue();
    $waitingLevering = $model->getNextLeveringSync($limit=10000);
    echo "Venter p� sync: ".countgf($waitingLevering);
    
          
           ?>
          
          <table>
          
          </table>
        
        </td></tr></table><?php

    }
    
    /**
     * PRIVATLEVERING FUNCTIONALITY
     */
     
     private function getAllPrivatleveringCount()
     {
        
     } 
     
    
     
     private function getNextLeveringSync()
     {
        $sql = "SELECT
            `shop_user`.`username`
            , `shop_user`.`id`
            , `shop_user`.`blocked`
            , `shop_user`.`is_delivery`
            , `shop_user`.`delivery_printed`
            , `shop_user`.`expire_date`
            , `order`.`present_name`
            , `order`.`present_model_name`
            , `order`.`shop_id`
        FROM
            `order`
            INNER JOIN `shop_user`
                ON (`order`.`shopuser_id` = `shop_user`.`id`)
        WHERE ( `shop_user`.`blocked` =0
            AND `shop_user`.`is_delivery` = 1 
            AND `shop_user`.`delivery_print_date` IS NULL
             )
        ORDER BY `order`.`id` ASC LIMIT 1";
        
        $nextPrivatLevering = ShopUser::find_by_sql($sql);
        if(count($nextPrivatLevering) > 0) return $nextPrivatLevering[0];
        else return null;
     }

    /**
     * GET DEBITOR INFORMATION
     */
     
     public function fetchdebitor()
    {
        $this->checkAccess();
        $debitorNo = isset($_GET["debitorno"]) ? intval($_GET["debitorno"]) : 0;
        $langCode = isset($_GET["langcode"]) ? intval($_GET["langcode"]) : 0;  
        $data = $this->getdebitorinfo($debitorNo,$langCode);
        if($data == null)
        {
          echo "Service error: ".$this->getLastError();
        }                  
        else 
        {
          echo var_dump($data);
        }
    }
    
    private $error = "";
    private function getLastError() { return $this->error; }
    private function getdebitorinfo($debitor_no,$lang_code)
    {
        // Check debitor no
        if(trimgf($debitor_no) == "")
        {
            $this->error = "Debitor number not specified"; return null;
        }
    
        // Prepare soap client
        $wsdlUrl = \GFCommon\Model\Navision\NavClient::getNavisionUrl($lang_code,"GavekortWS");
        if($wsdlUrl === null)
        {
          $this->error = "No language code specified";
          return null;
        }

        $options = array('login' => GFConfig::NAVISION_USERNAME, 'password' => GFConfig::NAVISION_PASSWORD, 'trace' => true);
        $client = new SoapClient($wsdlUrl, $options);

        // Call navision sync
        try {
            $response = @$client->__soapCall("GetCustomerData", array(array("custNo" => $debitor_no)));
        } catch (Exception $e) {
              $this->error = "Error calling service: ".$e->getMessage(); return null;
        }

        // Get request body
        $this->requestBody = $client->__getLastRequest();
        $this->responseBody = $response;
        
        if($this->responseBody == null || !isset($this->responseBody->return_value))
        {
          $this->error = "Empty response from service"; return null;                                                                            
        }
        
        // Parse response
        $response = json_decode($this->responseBody->return_value,true);
        
        if($response == null || !isset($response["CustomerData"]) || !is_array($response["CustomerData"]))
        {
          $this->error = "Could not parse response from service"; return null;  
        }
      
        return $response["CustomerData"];
    }


    /**
     * HELPERS
     */

    private function getSyncCount($since)
    {
        $sql = "SELECT navsync_status, COUNT(id) as ordercount FROM `company_order` WHERE created_datetime > '" . date("Y-m-d", $since) . "' GROUP BY navsync_status ORDER BY navsync_status";
        $data = CompanyOrder::find_by_sql($sql);

        $returnArray = array(0 => 0, 1 => 0, 2 => 0, 3 => 0, 10 => 0, 12 => 0, 20 => 0, 22 => 0);

        if (count($data) > 0) {
            foreach ($data as $item) {
                $returnArray[$item->navsync_status] = $item->ordercount;
            }
        }

        return $returnArray;
    }

    private function checkAccess()
    {
        if (!isset($_GET["token"]) || $_GET["token"] != "4kdRdmcst6fH") {
            echo "You don't have permission to view this page!";
            exit();
        }
    }

    private function isDebugMode()
    {
        return isset($_GET["debug"]) && $_GET["debug"] == "true";
    }
    
    
    /**
     * CHECK BSNUMBER
     */
     
     
     public function ordernocheck()
     {
     
        $type = "last";
        $lastnum = 10;
        
        if(isset($_GET["num"]) && intval($_GET["num"]) > 0) $lastnum = intval($_GET["num"]);
        //else if(isset($_GET["id"]) && intval($_GET["id"]) > 0) $type = "id";
        else if(isset($_GET["all"]) && trimgf($_GET["all"]) == "true") $type = "all";
        else if(isset($_GET["date"])) {
            $date = strtotime($_GET["date"]);
            if($date > 0) $type = "date";
        }
        
        $orderlist = array();
        if($type == "last")
        {
            $sql = "SELECT * FROM `company_order` ORDER BY created_datetime DESC LIMIT ".$lastnum;
            $orderlist = CompanyOrder::find_by_sql($sql);
        
        }
        else if($type == "id")
        {
            $sql = "SELECT * FROM `company_order` WHERE id = ".intval($_GET["id"]);
            $orderlist = CompanyOrder::find_by_sql($sql);
        
        }
        else if($type == "all")
        {
            $sql = "SELECT * FROM `company_order` ORDER BY created_datetime DESC";
            $orderlist = CompanyOrder::find_by_sql($sql);
        
        }
        else
        {
            $sql = "SELECT * FROM `company_order` WHERE created_datetime >= '" . date("Y-m-d H:i:s", $date) . "' && created_datetime < '" . date("Y-m-d H:i:s", $date+60*60*24) . "' ORDER BY created_datetime DESC";
            $orderlist = CompanyOrder::find_by_sql($sql);
        }
     
        if(count($orderlist) > 0)
        {
            foreach($orderlist as $order)
            {
            
                
                
                //  Set lang by shop
                $dkShops = array(52,575,54,55,56,53,287,290,310,247,248);
                $noShops = array(272,57,58,59,574);
                if(in_array($order->shop_id,$dkShops)) $lang = 1;
                else if(in_array($order->shop_id,$noShops)) $lang = 4;
                else { echo "UNKNOWN LANGUAGE: ".$order->shop_id; exit(); }
                $orderno = $order->order_no;
                
                $resp = $this->checkorderexists($orderno,$lang);
                echo $order->id.";".$order->company_name.";".$orderno.";".$order->navsync_status.";";
                if($this->getLastError() != "") echo "ERROR;".$this->getLastError().";\r\n";
                else if($resp == 1) echo "EXISTS;\r\n";
                else echo "NOT FOUND;".$resp.";\r\n";
        
            }
        }
     
       
        
     }
     
    
     
     
    
    private function checkorderexists($bsno,$lang_code)
    {
    
        $this->error = "";
    
        // Check debitor no
        if(trimgf($bsno) == "") { $this->error = "Order number not specified"; return null; }
    
        // Prepare soap client
        $wsdlUrl = \GFCommon\Model\Navision\NavClient::getNavisionUrl($lang_code,"GavekortWS");
        if($wsdlUrl === null) {
            $this->error = "No language code specified";
            return null;
        }
        
        $options = array('login' => GFConfig::NAVISION_USERNAME, 'password' => GFConfig::NAVISION_PASSWORD, 'trace' => true);
        $client = new SoapClient($wsdlUrl, $options);

        // Call navision sync
        try {
            $response = @$client->__soapCall("CheckOrderExists", array(array("bSNumber" => $bsno)));
        } catch (Exception $e) {
              $this->error = "Error calling service: ".$e->getMessage(); return null;
        }

        // Get request body
        $this->requestBody = $client->__getLastRequest();
        $this->responseBody = $response;
        
        if($this->responseBody == null || !isset($this->responseBody->return_value))
        {
          $this->error = "Empty response from service"; return null;                                                                            
        }
        
        // Parse response
        $response = json_decode($this->responseBody->return_value,true);
        return intval($response);
    } 
    
    
    /**
     * CREATE TILBUD
     */

    public function oprettilbud()
    {

        // Check token
        if(!isset($_GET["navtoken"]) || $_GET["navtoken"] != "vk5Dfk4g211Hlp09dkxNhkl6v")
        {
            echo json_encode(array("code" => "1","success" => false,"error" => "Token invalid or missing","data" => null));
        }

        // Create data
        // TODO

        // Send mail
        $mailText = "Navision opret tilbud kaldt:<br>
URI: ".$_SERVER["REQUEST_URI"]."
GET VARS:<br><pre>".print_r($_GET,true)."</pre><br>
POST VARS:<br><pre>".print_r($_POST,true)."</pre><br>
RAW INPUT:<br>".file_get_contents("php://input");
        

        mailProblem("NAVISION - OPRET TILBUD",$mailText);

        // Output ok
        echo json_encode(array("code" => "10","success" => true,"error" => "","data" => array("id" => rand(1,1000))));

    }
    
}
                                      