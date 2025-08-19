<?php
// Model ShopUser
// Date created  Mon, 16 Jan 2017 15:29:50 +0100
// Created by Bitworks
//***************************************************************
//   (PRI) id                            int(11)             NO
//   (MUL) shop_id                       int(11)             NO
//   (MUL) company_id                    int(11)             NO
//   (MUL) username                      varchar(250)        NO
//   (   ) password                      varchar(250)        NO
//   (   ) expire_date                   date                YES
//   (   ) is_demo                       tinyint(4)          YES
//   (   ) token                         varchar(45)         YES
//   (   ) token_created                 datetime            YES
//   (   ) is_giftcertificate            int(11)             YES
//   (MUL) blocked                       tinyint(4)          YES
//   (   ) is_delivery                   tinyint(4)          YES
//   (   ) delivery_printed              tinyint(4)          YES
//   (   ) company_order_id              int(11)             YES
//***************************************************************
use GFCommon\Model\Navision\ShipmentsWS;

class ShopUser extends BaseModel {
  static $table_name = "shop_user";
  static $primary_key = "id";
  //Relations
  static $belongs_to = array(array('shop'));

  static $before_create = array('onBeforeCreate');
  static $after_create = array('onAfterCreate');
  static $before_update = array('onBeforeUpdate');
  static $after_update = array('onAfterUpdate');
  static $before_destroy = array('onBeforeDestroy');
  static $after_destroy = array('onAfterDestroy');

   static $has_many = array(
                       array('orders', 'class_name' => 'Order'),
                       array('attributes_', 'class_name' => 'UserAttribute'),
                    );

  static $calculated_attributes = array("has_orders","user_attributes","shop_attributes","order","order_details");
//  static $calculated_attributes = array("has_orders","user_attributes","shop_attributes","order");

  static $skipCalculatedAttributes = false;

  // denne er midlertidig
  public function order() {
      if(self::$skipCalculatedAttributes==false)
  	    return($this->orders);
  }

  public function order_details() {
    if(self::$skipCalculatedAttributes==false) {
	 $order = Order::find_by_shopuser_id($this->id);
     if($order) {
       $orderdetails = Order::getOrderDetails($order->id,1);
       return(json_encode($orderdetails));
     } else {
         return("{}");
       }
    }
  }

  public function shop_attributes() {
      if(self::$skipCalculatedAttributes==false)   {
        $shopattributes = ShopAttribute::all(array('shop_id'=>$this->shop_id));
        return($shopattributes);
      }
  }

  public function user_attributes() {
     if(self::$skipCalculatedAttributes==false)   {
	  return($this->attributes_);
    }
  }

  public function has_orders() {
     return(count($this->orders)>0);
  }

  // Trigger functions
  function onBeforeCreate() {

	//if($this->is_service_user)
	//{
	//  xx  $this->token = trimgf(NewGUID(), '{}');
    //  xx  $this->token_created = date('d-m-Y H:i:s');
	//}

    $this->validateFields();
  }

  function onAfterCreate() {
  }

  function onBeforeUpdate() {
    $this->validateFields();
  }

  function onAfterUpdate() {
  }

  function onBeforeDestroy() {

  }

  function onAfterDestroy() {

    // slet user attributes til denne bruger

    UserAttribute::table()->delete(array('shopuser_id' => $this->id));
    Order::table()->delete(array('shopuser_id' => $this->id));
    OrderAttribute::table()->delete(array('shopuser_id' => $this->id));

//    OrderAddress::table()->delete(array('shopuser_id' => $this->id));        bruge ikke
//    OrderHistoryAddress::table()->delete(array('shopuser_id' => $this->id));  bruges ikke

    //OrderHistory::table()->delete(array('shopuser_id' => $this->id));
    //OrderHistoryAttribute::table()->delete(array('shopuser_id' => $this->id));
    OrderPresentEntry::table()->delete(array('shopuser_id' => $this->id));

     //thek om det er en gavekortbruger, s� skal gavekortet l�gges tilbage
     /*
     if($this->is_giftcertificate)   {
          $giftcertificate = GiftCertificate::find_by_certificate_no($this->username);
          $giftcertificate->shop_id = 0;
          $giftcertificate->company_id = 0;
          $giftcertificate->save();
     }*/


  }

  function validateFields() {
    testRequired($this, 'shop_id');
    testRequired($this, 'company_id');
    testRequired($this, 'username');
    testRequired($this, 'password');
    //Check parent records exists here
    Shop::find($this->shop_id);
    Company::find($this->company_id);
    testMaxLength($this, 'username', 250);
    testMaxLength($this, 'password', 250);
    testMaxLength($this, 'token', 45);



    $this->username = lowercase(trimgf($this->username));    //custom lowercase function
    //$this->password = lowercase(trimgf($this->password));
    $this->token = trimgf($this->token);

  }

  //---------------------------------------------------------------------------------------
  // Static CRUD Methods
  //---------------------------------------------------------------------------------------
  static public function createShopUser($data) {
    $shopuser = new ShopUser($data);
    $shopuser->save();
    return ($shopuser);
  }

  static public function readShopUser($id) {
    $shopuser = ShopUser::find($id);
    return ($shopuser);
  }

  static public function updateShopUser($data) {
    $shopuser = ShopUser::find($data['id']);
    $shopuser->update_attributes($data);
    $shopuser->save();
    return ($shopuser);
  }

  static public function updateShopUserDelivery($data) {
    //Det bliver vist bare et enkelt id...
    $deliveryarray = (array)json_decode($data['data']);
    foreach($deliveryarray as $delivery) {
      $shopuser = ShopUser::find($delivery->id);
      $shopuser->is_delivery =  $delivery->is_delivery;
      $shopuser->save();
    }
  }


  static public function deleteShopUser($id, $realDelete = false) {
      //$shopuser = ShopUser::find($id);
      //$shopuser->delete();
  //  }
  //  else { //Soft delete
  //    $shopuser->deleted = 1;
  //    $shopuser->save();
  //  }
  }
  //---------------------------------------------------------------------------------------
  // Custom Methods
  //---------------------------------------------------------------------------------------

  //Check Shop Attributes er ok inden brugere oprettes...

  static function validateShopAttributes($id) {
    $shop = Shop::find($id);
    $shopUsernameCount = 0;
    $shopPasswordCount = 0;
    $shopNameCount = 0;
    // Check at attribut typer kun forekommer en gang
    foreach ($shop->attributes_ as $shopAttribute) {
      $attributeUsernameCount = 0;
      $attributePasswordCount = 0;
      $attributeNameCount = 0;


      if ($shopAttribute->is_username) {
        $shopUsernameCount += 1;
        $attributeUsernameCount += 1;
      }
      if ($shopAttribute->is_password) {
        $shopPasswordCount += 1;
        $attributePasswordCount += 1;

      }
      if ($shopAttribute->is_name) {
        $shopNameCount += 1;
        $attributeNameCount += 1;
      }


      if ($attributeUsernameCount + $attributePasswordCount + $attributeNameCount > 1)
        throw new exception("Attributten '$shopAttribute->name' kan ikke v�re markeret b�de som 'brugernavn','password','navn' mm. ");
    }

    if ($shopUsernameCount <> 1)
      throw new exception("Shop '$shop->name' skal indeholde �n 'brugernavn' attribut. Shoppen indeholder : $shopUsernameCount ");
    if ($shopPasswordCount <> 1)
      throw new exception("Shop '$shop->name' skal indeholde �n 'kodeord' attribut. Shoppen indeholder : $shopPasswordCount ");


  }

  static public function Login($shopId, $username, $password) {

      $loginCodeMessages = array(
          40 => "Ugyldig login",
          41 => "Ugyldig login",
          46 => "closed",
          42 => "Kun administrator har adgang",
          43 => "registered",
          44 => "Gavekortet er spærret"
      );

      ////////////// NEW SHOPUSER LOGIN PROCEDURE BEGIN /////////////////////

      $loginModel = new \GFBiz\Gavevalg\ShopUserLogin();
      $loginModel->lockToShopID($shopId);

      $loginModel->loginWithUsernamePassword($username,$password);
      if($loginModel->isLoginValid()) {
          return $loginModel->createToken();
      } else {
          $message = $loginModel->getErrorMessage();
          if(isset($loginCodeMessages[$loginModel->getErrorCode()])) {
              $message = $loginCodeMessages[$loginModel->getErrorCode()];
          }
          throw new Exception($message);
      }

      return;
      ////////////// NEW SHOPUSER LOGIN PROCEDURE END /////////////////////

	//vi skal have shopid med her ogs�
      $shopUsers = ShopUser::find('all', array('conditions' => array('LOWER(username)=? and LOWER(password) = ? and shop_id =?', lowercase($username), lowercase($password), $shopId)));
                          
    if (count($shopUsers) <> 1)
      throw new Exception('Ugyldig login.('.countgf($shopUsers).')');
    else {
      $shopUser = $shopUsers[0];
/*
      if($shopUser->is_giftcertificate == 0 && $shopUser->password !== $password) {
          throw new Exception('Ugyldig login.('.countgf($shopUsers).')');
      }
*/
      /*Tjek om shoppen er �ben  */
      $shop = Shop::find($shopUser->shop_id);
      if($shop->active==0) {
           if($shop->is_demo==0) {
             throw new Exception('closed');
            }
      }



      /*Hvis det er demo shop, m� kun demo burger logge ind*/
      if($shop->is_demo==1) {
          if($shop->demo_user_id !=$shopUser->id)
             throw new Exception('Kun administrator har adgang');
      }

      //Tjek om bruger har f�et gave udlevert
      $order = Order::find_by_shopuser_id($shopUser->id);
      if($order) {
          if($order->registered==1 && $order->is_demo==0 && $order->order_timestamp->getTimestamp() < (time()-60*60*24)) {
            throw new Exception('registered');
          }
          if($shop->singleselect == 1) {
              throw new Exception('registered');
          }
      }
                  
      if($shopUser->blocked==1) {
        throw new Exception('Gavekortet er sp�rret');
      }


      if($shopUser->is_giftcertificate==1)
      {                   
        $today = date("Y-m-d");   // hvis demobruger skal der l�gge 14 dage til
        if($shop->is_demo==0)
        {
              
            $expiredate = ExpireDate::find_by_expire_date($shopUser->expire_date);
            $manuallyBlocked = false;

            // 2021
            if($expiredate->display_date == "31-10-2021" && time() > mktime(10,0,0,11,2,2021)) $manuallyBlocked = true;
            if($expiredate->display_date == "07-11-2021" && time() > mktime(10,0,0,11,9,2021)) $manuallyBlocked = true;
            if($expiredate->display_date == "08-11-2021" && time() > mktime(0,0,0,11,9,2021)) $manuallyBlocked = true;
            if($expiredate->display_date == "14-11-2021" && time() > mktime(10,0,0,11,16,2021)) $manuallyBlocked = true;

            // 21/11 has 2 close dates a early and a late
            if(in_array($shop->id,array(53,2395,54,55,56,290,310)))   // Late shops
            {
                if($expiredate->display_date == "21-11-2021" && time() > mktime(7,0,0,11,24,2021)) $manuallyBlocked = true;
            }
            else // Normal close
            {
                if($expiredate->display_date == "21-11-2021" && time() > mktime(10,0,0,11,23,2021)) $manuallyBlocked = true;
            }

            if($expiredate->display_date == "28-11-2021" && time() > mktime(10,0,0,11,30,2021)) $manuallyBlocked = true;

            if($expiredate->display_date == "03-01-2022" && time() > mktime(10,0,0,1,4,2022)) $manuallyBlocked = true;
            if($expiredate->display_date == "01-04-2022" && time() > mktime(0,0,0,4,22,2022)) $manuallyBlocked = true;
            if($expiredate->display_date == "31-12-2022" && time() > mktime(0,0,0,1,1,2023)) $manuallyBlocked = true;

            if($expiredate->display_date == "31-12-2021") {
                $dkShops = array(54,55,56,575,2548,290,3310,53,2395,52);
                // DK
                if(in_array($shopUser->shop_id,$dkShops) && time() > mktime(9,0,0,1,5,2022)) $manuallyBlocked = true;
                // OTHER
                if(!in_array($shopUser->shop_id,$dkShops) && time() > mktime(10,0,0,1,4,2022)) $manuallyBlocked = true;
            }

            // SETUP CLOSING DATES FOR DEADLINES
            if($expiredate->display_date == "04-11-2018" && time() > mktime(12,0,0,11,6,2018)) $manuallyBlocked = true;
            else if($expiredate->display_date == "11-11-2018" && time() > mktime(12,0,0,11,13,2018)) $manuallyBlocked = true;
            else if($expiredate->display_date == "18-11-2018" && time() > mktime(12,0,0,11,20,2018)) $manuallyBlocked = true;
            else if($expiredate->display_date == "25-11-2018" && time() > mktime(12,0,0,11,27,2018)) $manuallyBlocked = true;
            else if($expiredate->display_date == "02-12-2018" && time() > mktime(12,0,0,12,4,2018)) $manuallyBlocked = true;
            else if($expiredate->display_date == "31-12-2018") $manuallyBlocked = true;
            
            //if($expiredate->blocked==1 || $expiredate->display_date == "05-11-2017" || $expiredate->display_date == "06-11-2017")
            //2019-01-01
            //if($expiredate->blocked==1 || $expiredate->display_date != "01-01-2019")
            //if($expiredate->blocked==1 || $expiredate->display_date == "05-11-2017" || $expiredate->display_date == "06-11-2017" || $expiredate->display_date == "12-11-2017" || $expiredate->display_date == "19-11-2017" || $expiredate->display_date == "26-11-2017" || $expiredate->display_date == "30-11-2017"  )
            if($expiredate->blocked==1 || $manuallyBlocked == true)
            {
                  throw new Exception('closed');
            }
                       
         }

       if($shopUser->delivery_print_date != null)   {
             throw new Exception('closed');
       }


        // Check if company is deactivated
        $c = Company::find($shopUser->company_id);
        if($c->onhold == 1)
        {
          throw new Exception('closed');
        }

        }
               
                   
      $shopUser->token = trimgf(NewGUID(), '{}');
      $shopUser->token_created = date('d-m-Y H:i:s');
      $shopUser->save();
      //$shopUser->shoptoken = "test";
      return ($shopUser->token);
    }
  }

  static public function getByToken($token) {
    if (!$token) {
      throw new Exception('Ugyldig token');
    }
    else {
        //$shopUsers = ShopUser::find('all', array('conditions' => array('token=?', $token)));
      $shopUsers = ShopUser::find('all', array('conditions' => array('token=? && DATE_ADD(token_created, INTERVAL 2 HOUR) > NOW()', $token)));
      if (count($shopUsers) == 1)
        return ($shopUsers[0]);
      else
        throw new Exception('Ugyldig token');
    }
  }

  public function getDeliveryStatus($details=false) {

      if($details == true) {

          $msg = "<b>Track and trace rapport for gavekort ".$this->username.":</b>";
          $closed = false;

          if($this->is_delivery == 1)
          {

              $handlers = array("foreigngls" => "udtræk til udenlandsforsendelse via GLS fil","glsexport" => "udtrukket til GLS fragtfil","postnord" => "overførsel til postnord","sespahotel" => "udtræk på svensk spa&hotel liste","mydsv" => "MyDSV privatlevering i Sverige","navision" => "navision","dpse" => "Privatlevering via DistributionPlus");
              $states = array(1 => "Afventer afsendelse fra cardshop ",2 => "Sendt til levering",3 => "Fejl i levering, kontakt teknisk support",4 => "Forsendelse blokkeret, skal ikke sendes",5 => "Behandlet eksternt",6 => "Sendt og kvittering modtaget",9 => "Afventer manuel behandling, kontakt teknisk support");

              if($this->delivery_state == 0) {
                  $msg .= "<br>Levering af ordren er ikke behandlet endnu. Som regel er det fordi gavevalg ikke er foretaget eller ventetid på 24 timer efter valg ikke er gået.";
              } else if($this->delivery_state == 1) {
                  $msg .= "<br>".($this->delivery_print_date == null ? "ingen dato" : $this->delivery_print_date->format("Y-m-d \k\l. H:i")).": Leverance klar til levering (ikke sendt endnu)";
                  try {
                      $shipment = \Shipment::find($this->navsync_response);
                      if($shipment->shipment_type == "privatedelivery" || $shipment->shipment_type == "directdelivery") {

                          $handler = isset($handlers[$shipment->handler]) ? $handlers[$shipment->handler] : "ukendt destination, kontakt teknisk support";
                          $state = isset($states[$shipment->shipment_state]) ? $states[$shipment->shipment_state] : "Ukendt status, kontakt teknisk support";

                          if($shipment->shipment_sync_date != null) {
                              $msg .= "<br>".$shipment->shipment_sync_date->format("Y-m-d \k\l. H:i").": ".$state." via ".$handler;
                          } else {
                              $msg .= "<br><b>AFVENTER</b>: ".$state." via ".$handler;
                          }

                          if($shipment->handler == "mydsv" && $shipment->shipment_state == 1) $msg .= "<br><br><b>BEMÆRK:</b> Afventer en MyDSV levering betyder det at den ikke er sendt til DSV endnu, den afventer at de får varen på lager og vi kan sende den før. Den kan derfor ikke spores ved DSV endnu.";
                          if($shipment->handler == "mydsv" && $shipment->shipment_state == 2) $msg .= "<br><br><b>BEMÆRK:</b> En ordre der er sendt til levering ved DSV kan spores ved DSV i deres eDelivery system.";
                          if($shipment->handler == "postnord") $msg .= "<br>BEMÆRK: Leverencer til postnord bliver i denne tilstand til postnord har alle varer i leverancen på lager.";
                          if($shipment->handler == "glsexport") $msg .= "<br>BEMÆRK: Disse leverancer udtrækkes til gls fragtlister hver mandag morgen, denne bør blive udtrukket næste kørsel.";
                          if($shipment->handler == "foreigngls") $msg .= "<br>BEMÆRK: Disse leverancer bliver håndteret manuelt da kunden har valgt levering til et andet land, de sendes som udgangspunkt ud hver mandag.";
                          if($shipment->handler == "sespahotel") $msg .= "<br>BEMÆRK: Denne leverance udtrækkes på en speciel liste til svenske spa&hotel gaver og afventer næste træk.";
                          if($shipment->handler == "dpse") $msg .= "<br>Leverance har fået ordrenr: <b>".$shipment->id."</b>";


                          if($shipment->handler == 'navision' && $shipment->shipment_state == 2) {

                              try {
                                  $client = new ShipmentsWS();
                                  $response = $client->getByShipment($shipment);
                                  $msg .= "<br><br><b>Status fra navision</b><br>".nl2br($response[0]->getShipmentStatus());
                              } catch (\Exception $e) {
                                  $msg .= "<br>Fejl ved opslag i navision, kan ikke finde status!";
                              }

                          }

                          $blockMessage = \BlockMessage::find_by_sql("select * from blockmessage where shipment_id = ".$shipment->id." and shipment_id > 0 and release_status = 0");
                          $techBlocks = 0; $normalBlocks = 0;
                          foreach($blockMessage as $block) {
                              if($block->tech_block == 1) $techBlocks++;
                              else $normalBlocks++;
                          }

                          if($normalBlocks > 0) {
                              $msg .= "<br><br><b>Blokeret:</b> Der er ".$normalBlocks." fejl på fejllisten for denne leverance, de skal løses før yderligere behandling.";
                          }
                          if($techBlocks > 0) {
                              $msg .= "<br><br><b>Blokeret teknisk fejl:</b> Der er problemer på denne ordre der skal løses før yderligere behandling, kontakt teknisk support.";
                          }


                      } else {
                          $msg .= "<br>Kan ikke finde forsendelse i fragtlister, kontakt teknisk support!";
                      }
                  } catch(\Exception $e) {
                      $msg .= "<br>Kan ikke finde forsendelse i fragtlister, kontakt teknisk support!";
                  }
              } else if($this->delivery_state == 2 || $this->delivery_state == 5) {

                  if($this->delivery_print_date == null) {
                      $msg .= "<br>INGEN DATO: Leverance udtrukket til levering - er der problemer med levering så kontakt teknisk support";
                  } else {
                      $msg .= "<br>".$this->delivery_print_date->format("Y-m-d \k\l. H:i").": Leverance udtrukket til levering";
                  }


                  try {
                      $shipment = \Shipment::find($this->navsync_response);
                      if($shipment->shipment_type == "privatedelivery" || $shipment->shipment_type == "directdelivery") {

                          $handler = isset($handlers[$shipment->handler]) ? $handlers[$shipment->handler] : "ukendt destination, kontakt teknisk support";
                          $state = isset($states[$shipment->shipment_state]) ? $states[$shipment->shipment_state] : "Ukendt status, kontakt teknisk support";

                          if($shipment->handler == "dpse") $msg .= "<br>Leverance har fået ordrenr: <b>".$shipment->id."</b>";

                          if($shipment->shipment_sync_date != null) {
                              $msg .= "<br>".$shipment->shipment_sync_date->format("Y-m-d \k\l. H:i").": ".$state." via ".$handler;
                          } else {
                              $msg .= "<br>AFVENTER: ".$state." via ".$handler;
                          }

                          if($shipment->shipped_date != null) {
                                $msg .= "<br>".$shipment->shipped_date->format("Y-m-d \k\l. H:i").": Pakket og sendt til levering på pakkeri";
                          }

                          if(trimgf($shipment->ttlink) != "") {
                                $msg .= "<br><a href='".trimgf($shipment->ttlink)."' rel=\"noreferrer\" target='_blank'>Track and trace link</a>";
                          }

                          if($shipment->delivered_date != null) {
                                $msg .= "<br>".$shipment->delivered_date->format("Y-m-d \k\l. H:i").": Leveret til modtager";
                          }


                          // Check post nord
                          $postnordReport = \PostnordOrderReport::find_by_sql("SELECT * FROM `postnord_orderreport` WHERE `username` LIKE '".$this->username."'");
                          if(count($postnordReport) > 0) {
                              $msg .= "<br>".$postnordReport[0]->shipment_date->format("Y-m-d \k\l. H:i").": Sendt af postnord, se track and trace links herunder:";
                              foreach($postnordReport as $report) {
                                  $msg .= "<br> - <a href='https://www.postnord.dk/varktojer/track-trace?shipmentId=".$report->shipment_no ."' rel=\"noreferrer\" target='_blank'>Postnord pakke ".$report->shipment_no ."</a> (".$report->quantity." x ".$report->itemno.")";
                              }
                          }

                          $blockMessage = \BlockMessage::find_by_sql("select * from blockmessage where shipment_id = ".$shipment->id." and shipment_id > 0 and release_status = 0");
                          $techBlocks = 0; $normalBlocks = 0;
                          foreach($blockMessage as $block) {
                            if($block->tech_block == 1) $techBlocks++;
                            else $normalBlocks++;
                          }

                          if($normalBlocks > 0) {
                                $msg .= "<br><br><b>Blokeret:</b> Der er ".$normalBlocks." fejl på fejllisten for denne leverance, de skal løses før yderligere behandling.";
                          }
                          if($techBlocks > 0) {
                               $msg .= "<br><br><b>Blokeret teknisk fejl:</b> Der er problemer på denne ordre der skal løses før yderligere behandling, kontakt teknisk support.";
                          }

                          if($shipment->handler == 'navision' && $shipment->shipment_state == 2) {

                              try {
                                  $client = new ShipmentsWS();
                                  $response = $client->getByShipment($shipment);
                                  $msg .= "<br><br><b>Status fra navision</b><br>".nl2br($response[0]->getShipmentStatus());
                              } catch (\Exception $e) {
                                  $msg .= "<br>Fejl ved opslag i navision, kan ikke finde status!";
                              }

                          }

                      } else {
                          $msg .= "<br>Kan ikke finde forsendelse i fragtlister, kontakt teknisk support!";
                      }
                  } catch(\Exception $e) {
                      $msg .= "<br>Kan ikke finde forsendelse i fragtlister, kontakt teknisk support!";
                  }

              } else {
                  $msg .= "<br>Leverance blokkeret: [".$this->delivery_state."] ".GFUnit\navision\syncprivatedelivery\ErrorCodes::getRetryText($this->delivery_state);
              }

          }


          if($this->is_replaced > 0) {
              try {
                  $replaceUser = ShopUser::find('first',array("conditions" => array("replacement_id" => $this->id)));
                  $msg .= "<br>Gavekortet er erstattet med kort ".$replaceUser->username;
              } catch (Exception $e) {
                  $msg .= "<br>Gavekortet er erstattet, kan ikke finde kort";
              }
          }
          else if($this->blocked == 1) {
              $msg .= "<br>Gavekortet er lukket/krediteret";
              $closed = true;
          }

          else if($this->shutdown == 1) {
              $msg .= "<br>Gavekortet er spærret";
              $closed = true;
          }

          // Not private delivery
          if($this->is_delivery == 0) {
              $msg .= "<br>Ugelevering - ".$this->expire_date->format("Y-m-d");
              return $msg;
          }

          if($closed == true) {
              $msg .= "<br>Yderligere behandling af ordren er stoppet!";
          }

          return $msg;
      }

      // Closed
      if($this->blocked == 1 || $this->shutdown == 1) {
          return "Kortet er lukket";
      }

      // Demo
      if($this->is_demo == 1) {
          return "Demo kort";
      }

      $replaceText = "";
      if($this->is_replaced > 0) {
          try {
              $replaceUser = ShopUser::find('first',array("conditions" => array("replacement_id" => $this->id)));
              $replaceText = " - erstattet med kort ".$replaceUser->username;
          } catch (Exception $e) {
              $replaceText = " - erstattet, kan ikke finde kort";
          }
      }

      // Not private delivery
      if($this->is_delivery == 0) {
          return "Ugelevering - ".$this->expire_date->format("Y-m-d").$replaceText;
      }

      // Not processed
      if($this->delivery_state == 2) {
          $msg = "Sendt til levering d. ".($this->delivery_print_date == null ? "ukendt dato" : $this->delivery_print_date->format("Y-m-d H:i")).$replaceText;
          return $msg;

      }

      return $this->delivery_state.": ".GFUnit\navision\syncprivatedelivery\ErrorCodes::getRetryText($this->delivery_state).$replaceText;


  }

  public static function getShopUserDeliveryStatus($shopuser_id,$details=false)
  {
      try {
          $shopuser = ShopUser::find($shopuser_id);
      } catch (Exception $e) {
          return "Kunne ikke finde gavekort";
      }
      if($shopuser == null) return "Kunne ikke finde gavekort";
      return $shopuser->getDeliveryStatus($details);

  }

  // removes all order data for a single user_id
  function removeOrderData()
  {
       // Slet ordre,attributter og present entries
        $orders = Order::all(array('shopuser_id' => $this->id));
        foreach($orders as $order){
          $order->delete();   // Sletter of s� present_entry
        }

        //Slet ordrehistory
        $orderhistories = OrderHistory::all(array('shopuser_id' => $this->id));
        foreach($orderhistories as $orderhistory){
          $orderhistory->delete();
        }
    }



}
?>