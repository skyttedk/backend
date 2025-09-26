<?php
// Controller Order
// Date created  Thu, 26 May 2016 19:44:28 +0200
// Created by Bitworks
class ExternalController Extends baseController {
    public function Index() {
    }


    private function validatetodkenShop($shopID,$token)
    {

        if($token == "NJycUpZGVhMJvQ7Kmb88uXRgX6VMhpvUcEBPj9NhmJ2tjxQB") return true;
        $rs = ShopUser::find_by_token($token);
        if($rs){
            if($rs->is_demo == 1 && $rs->shop_id == $shopID){
                return true;
            }
        } else {
            http_response_code(400);
            return;
        }

    }

    public function openShops() {
        // Set timezone to Europe/Copenhagen for consistent datetime operations
        date_default_timezone_set('Europe/Copenhagen');

        // Safety date boundaries to prevent accidentally opening too many shops
        $target_date = date('Y-m-d');
        $next_date = date('Y-m-d', strtotime($target_date . ' +1 day'));
        $target_datetime = date('Y-m-d H:i:s');

        // Find shops that should be opened now (start_date + start_time has passed and shop is not active)
        // Safety check: only consider shops with start_date before tomorrow (< next_date)
        $shops = Shop::find('all', array(
            'conditions' => array(
                'CONCAT(start_date, " ", COALESCE(start_time, "00:00:00")) <= ? AND start_date < ?',
                $target_datetime, $next_date
            )
        ));

        foreach($shops as $shop) {
            echo $shop->attributes["id"]. "- ". $shop->attributes["name"].": opened at ".$target_datetime."\n";
            $shop->is_demo = 0;
            $shop->active = 1;
            $shop->soft_close = 0;
            $shop->save();
        }
        $dummy = array();
        $dummy['shops_opened'] = countgf($shops);
        $dummy['timestamp'] = $target_datetime;
        response::success(json_encode($dummy));
    }


    public function createExpirationMails()  {
        $shops = Shop::all(array('end_date' => (string) date('Y-m-d')));
        foreach($shops as $shop){
            $shop->email_list = str_replace(",",";",$shop->email_list);
            $mailaddresses = explode(";", $shop->email_list);
            foreach($mailaddresses as $mailaddress){
                $mailqueue = new MailQueue();
    			$mailqueue->sender_name = 'Gavefabrikken';
    			$mailqueue->recipent_name = '';
    			$mailqueue->recipent_email = $mailaddress;
    			$mailqueue->subject ='Shop Udløber';
    			$mailqueue->body ='Shoppen \''.$shop->name.'\' udløber d.'.$shop->end_date->format('d-m-Y');
                $mailqueue->mailserver_id = $shop->mailserver_id;
    			$mailqueue->save();
            }
        }
        $dummy = array();
        $dummy['date'] = (string) date('Y-m-d');
        $dummy['expired'] = countgf($shops);
        response::success(json_encode($dummy));
    }


    public function sendMailsToShopsWithNoOrders() {
        //Skal være nyt felt expiration_warning_date
        $shops = Shop::all(array('expire_warning_date' => (string) date('Y-m-d')));
        $result = 0;
        foreach ($shops as $shop) {
          $result += $this->sendMailsToUsersWithNoOrders($shop->id);
        }
        $dummy = [];
        $dummy['reminders sent'] = $result;
        response::success(json_encode($dummy));
    }

    //Kaldes fra kunden backend
     public function sendMailsToUsersWithNoOrders2() {
         $this->validatetodkenShop($_POST['shop_id'],$_POST['token']);
         $result = $this->sendMailsToUsersWithNoOrders($_POST['shop_id']);

         $dummy = [];
         $dummy['reminders sent'] = $result;
         response::success(json_encode($dummy));
     }

    //


    private function sendMailsToUsersWithNoOrders($shopId) {

      $i=0;
      $shopusers = ShopUser::all(array('shop_id' => $shopId,'is_demo' => 0));
      $subject ="Gavevalg deadline";


      foreach($shopusers as $shopuser)
      {

          if(!$shopuser->has_Orders())   {
          // find email
          foreach($shopuser->attributes_ as $attribute)
    	   {
		    if($attribute->is_email)
		      $email = $attribute->attribute_value;
           }

           //dan link
           $shop = Shop::find($shopuser->shop_id);
           $link = GFConfig::SHOP_URL_PRIMARY."gavevalg/".$shop->link;

          $expire_date ='...';

            if($shop->end_date)
              $expire_date = $shop->end_date->format('d-m-Y');


                $html ="<html><head>
                        <meta charset='UTF-8'>
                      </head><body style='font-family: calibri;font-size: 16px;'>";
                if($shop->localisation == 1){
                $html.="
                        K&aelig;re gavemodtager,<br><br>

                        Vi n&aelig;rmer os deadline for valg af &aring;rets julegave, og vi har endnu ikke registreret dit valg.<br><br>

                        Du kan stadig n&aring; at v&aelig;lge via dette link: $link<br><br>
                        Login:<br>
                        <table width=250>
                        <tr><td width=100>Brugernavn:</td><td>".$shopuser->username."</td></tr>
                        <tr><td>Adgangskode:</td><td>".$shopuser->password."</td></tr>
                        </table> <br>


                        {EXTRA}
                        Med venlig hilsen <br>
                        GaveFabrikken  <br><br>

                        <span >OBS: Dette er en autogenereret mail, der ikke kan besvares.<span>
                          <br>     <br>
                        <hr>
                        <br>";
                }
                //  Bem&aelig;rk at deadline for valg af gave er ".$expire_date."<br><br>
                if($shop->localisation == 4){
                $html.="
                       <p>Kj&aelig;re gavemottaker.</p>

                        <p>Det n&aelig;rmer seg deadline for valg av gave, men vi kan ikke se &aring; registrert noe fra deg?</p>
                        <p>Det er fortsatt tid, g&aring; til $link</p>
                        Logg Inn:<br>
                        <table width=250>
                        <tr><td width=100>Brukernavn:</td><td>".$shopuser->username."</td></tr>
                        <tr><td>Passord:</td><td>".$shopuser->password."</td></tr>
                        </table> <br>


                        <p>Med vennlig hilsen<br />GaveFabrikken</p>
                          <br>     <br>
                        <hr>
                        <br>";
                }
                if($shop->localisation == 5){
                 $subject=" Deadline för val av present";
                $link = GFConfig::SHOP_URL_SE.'gavevalg/'.$shop->link;
                $html.="
                      <p>K&auml;ra presentmottagare,&nbsp;</p>
                      <p>Vi n&auml;rmar oss deadline f&ouml;r att v&auml;lja &aring;rets julklapp och vi har &auml;nnu inte registrerat ditt val.&nbsp;</p>
                      <p>Du kan fortfarande v&auml;lja via denna l&auml;nk: $link</p>
                      <p>Log in p&aring; uppgifterna som har blivit utskickat till din mejl:</p>

                       <table width=250>
                        <tr><td width=100>Brugernavn:</td><td>".$shopuser->username."</td></tr>
                        <tr><td>Password:</td><td>".$shopuser->password."</td></tr>
                       </table> <br>
                       {EXTRA}
                      <p>Med v&auml;nliga h&auml;lsningar<br>
                      PresentBolaget</p>
                      <p>OBS: Detta &auml;r ett automatiskt genererat e-postmeddelande som inte kan besvaras.</p><br>";

                }
                //     <p>Husk at aller siste frist for &aring; velge er ".$expire_date."<br />&nbsp;</p>
                  $html.="
                        Dear Gift Recipient ,<br><br>

                        We are near the deadline for choosing this years Christmas gift, and we have not yet registered your choice.<br><br>

                        You can still choose from this link:  $link<br><br>
                        Login:<br>
                        <table width=250>
                        <tr><td width=100>User name:</td><td>".$shopuser->username."</td></tr>
                        <tr><td>Password:</td><td>".$shopuser->password."</td></tr>
                        </table> <br>

                        {EXTRA}
                       Yours sincerely <br>
                        GaveFabrikken  <br><br>

                        <span class='highlight' style='background-color: yellow'>Note: This is an autogenerated mail that cannot be answered.<span>
                      </body>";
                //  Note that the deadline for gift selection is  ".$expire_date."<br><br>  
               if($shop->id==297) {
                   $novonordisktext = "Hvis du ikke foretager et valg, vil du modtage Happy X-mas Tree og donation til UNICEF.<br><br>";
               } else {
                   $novonordisktext = "";
               }

              $html = str_replace('{EXTRA}',$novonordisktext,$html);

              if($email!="") {
                  $mailqueue = new MailQueue();
           		  $mailqueue->sender_name  ="Gavefabrikken";
        		  $mailqueue->sender_email  ="Gavefabrikken@gavefabrikken.dk";
         		  $mailqueue->recipent_email = $email;
           		  $mailqueue->subject = $subject;
                  $mailqueue->user_id =  $shopuser->id;
                  $mailqueue->body = $html;
                  if($shop->localisation == 5) {
                    $mailqueue->mailserver_id = 5;
                  } else {
                      $mailqueue->mailserver_id = $shop->mailserver_id;
                  }

       		      $mailqueue->save();
              }
              $i+=1;
          }
      }

      $mailqueue = new MailQueue();
      $mailqueue->sender_name  ="Gavefabrikken";
      $mailqueue->sender_email  ="Gavefabrikken@gavefabrikken.dk";
      $mailqueue->recipent_email = 'us@gavefabrikken.dk';
      $mailqueue->subject ="Gavevalg deadline sendt";
      $mailqueue->user_id =  $shopuser->id;
      $mailqueue->body = $i.' reminders blev sendt for shop id '.$shop->id;
      $mailqueue->mailserver_id = $shop->mailserver_id;
      $mailqueue->save();
      return($i);
  }



  //  *********************** 24 Gaver  *************************'

    public function fetchOrder24() {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://24gaver.dk/service/sync/dsflkhjkjehkljfdzhkljsdf409843798dkshjdfkljhjdlks.php?token=fdj4fhkj43598fgdds87yg543iuthg");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        $order =  json_decode(base64_decode($output));
        curl_close($ch);
        return($order);
   }

 public function confirmOrder24($id) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://24gaver.dk/service/sync/confirmSync.php?token=dsflkhjkjdddsddnmdnmbdfff8dkshjdfkljhjdlks&order_id=".$id);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($ch);
    curl_close($ch);
 }

 public function rejectOrder24($id) {

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://24gaver.dk/service/sync/errorSync.php?token=asfdasdf&order_id=".$id);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($ch);
    curl_close($ch);
 }


    public function importFrom24Gaver() {
      try {

       $external = $this->fetchOrder24()->data;
       $companyimport = new  CompanyImport();
       $companyimport->cvr      =   $external->cvr;
       $companyimport->name     =   $external->firmaNavn;
       $companyimport->quantity =   $external->order;
       $companyimport->bill_to_address = $external->vej.' '.$external->husnr;
       $companyimport->bill_to_address_2 = "";
       $companyimport->bill_to_postal_code = $external->postnr;
       $companyimport->bill_to_city = $external->bynavn;
       $companyimport->ship_to_address = $external->secVej.' '.$external->secHusnr;
       $companyimport->ship_to_address_2 = "";
       $companyimport->ship_to_postal_code = $external->secPostnr;
       $companyimport->ship_to_city =  $external->secBynavn;
       $companyimport->contact_name = $external->fortrolig;
       $companyimport->contact_phone = $external->tlf;
       $companyimport->contact_email = $external->email;
       $companyimport->shipment_method  = $external->leveringsmetode;

       if($external->leveret=="home") {
          $external->leveret = '01-01-2019';
       }

       $expiredate = ExpireDate::find_by_display_date_and_blocked($external->leveret, 0);
        if(!isset($expiredate))
          throw new exception('Invalid expire date: '.$external->leveret);

       $companyimport->expire_date =   DateTime::createFromFormat('d-m-Y', $external->leveret);


       if($external->gavekort == '400') {
           $companyimport->shop_id   = 54;
       } elseif($external->gavekort == '560') {
           $companyimport->shop_id   = 55;
       } elseif($external->gavekort == '640') {
           $companyimport->shop_id   = 56;
       }


       $companyimport->value   = $external->gavekort;
       $companyimport->shop_name = '24Gaver';
       $companyimport->salesperson = $external->saleman;
       $companyimport->noter = $external->note;

       $companyimport->save();
       $dummy = [];
       $dummy['imported'] = 1;
       response::success(json_encode($dummy));
       $this->confirmOrder24($external->id);
     } catch (Exception $e) {

       $dummy['imported'] = 0;
       $dummy['message'] = $e->getMessage();

       response::success(json_encode($dummy));

       $this->rejectOrder24($external->id);

   }
    }



      //  *********************** Frømmegavekortet  *************************'

    public function fetchOrderDromme() {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://drommegavekortet.dk/sync/dsflkhjkjehkljfdzhkljsdf409843798dkshjdfkljhjdlks.php?token=fdj4fhkj43598fgdds87yg543iuthg");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        $order =  json_decode(base64_decode($output));
        curl_close($ch);
        return($order);
   }

 public function confirmOrderDromme($id) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://drommegavekortet.dk/sync/confirmSync.php?token=dsflkhjkjdddsddnmdnmbdfff8dkshjdfkljhjdlks&order_id=".$id);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($ch);
    curl_close($ch);
 }

 public function rejectOrderDromme($id) {

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://drommegavekortet.dk/sync/errorSync.php?token=asfdasdf&order_id=".$id);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($ch);
    curl_close($ch);
 }


    public function importFromDromme() {


      try {

         $external = $this->fetchOrderDromme()->data;
         $companyimport = new  CompanyImport();
         $companyimport->cvr      =   $external->cvr;
         $companyimport->name     =   $external->firmaNavn;
         $companyimport->quantity =   $external->order;
         $companyimport->bill_to_address = $external->vej.' '.$external->husnr;
         $companyimport->bill_to_address_2 = "";
         $companyimport->bill_to_postal_code = $external->postnr;
         $companyimport->bill_to_city = $external->bynavn;
         $companyimport->ship_to_address = $external->secVej.' '.$external->secHusnr;
         $companyimport->ship_to_address_2 = "";
         $companyimport->ship_to_postal_code = $external->secPostnr;
         $companyimport->ship_to_city =  $external->secBynavn;
         $companyimport->contact_name = $external->fortrolig;
         $companyimport->contact_phone = $external->tlf;
         $companyimport->contact_email = $external->email;
         $companyimport->shipment_method  = $external->leveringsmetode;
         $expiredate = ExpireDate::find_by_display_date_and_blocked($external->leveret, 0);
         if(!isset($expiredate))
          throw new exception('Invalid expire date: '.$external->leveret);

         $companyimport->expire_date =   DateTime::createFromFormat('d-m-Y', $external->leveret);

       if($external->deleveryValue == '100') {
           $companyimport->shop_id   = 287;
       } elseif($external->deleveryValue == '200') {
           $companyimport->shop_id   = 290;
       } elseif($external->deleveryValue == '300') {
           $companyimport->shop_id   = 310;
       }  else {
           throw new exception('invalid value');
       }

       $companyimport->value   = $external->deleveryValue;
       $companyimport->shop_name = 'Drømmegavekortet';
       $companyimport->salesperson = $external->salesperson;
       $companyimport->noter = $external->noter;
       $companyimport->save();
       $dummy = [];
       $dummy['imported'] = 1;
       response::success(json_encode($dummy));
       $this->confirmOrderDromme($external->id);

     } catch (Exception $e) {

       $dummy['imported'] = 0;
       $dummy['message'] = $e->getMessage();
       response::success(json_encode($dummy));
       $this->rejectOrderDromme($external->id);
   }
    }


      //  *********************** Kender Du Typen  Gaver  *************************'

    public function fetchOrderKDT() {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://julegavetypen.dk/sync/dsflkhjkjehkljfdzhkljsdf409843798dkshjdfkljhjdlks.php?token=fdj4fhkj43598fgdds87yg543iuthg");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        $order =  json_decode(base64_decode($output));
        curl_close($ch);
        return($order);
   }

 public function confirmOrderKDT($id) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://julegavetypen.dk/sync/confirmSync.php?token=dsflkhjkjdddsddnmdnmbdfff8dkshjdfkljhjdlks&order_id=".$id);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($ch);
    curl_close($ch);
 }

 public function rejectOrderKDT($id) {

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://julegavetypen.dk/sync/errorSync.php?token=asfdasdf&order_id=".$id);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($ch);
    curl_close($ch);
 }


    public function importFromKDT() {


      try {

         $external = $this->fetchOrderKDT()->data;
         $companyimport = new  CompanyImport();
         $companyimport->shop_id   = 265;
         $companyimport->cvr      =   $external->cvr;
         $companyimport->name     =   $external->firmaNavn;
         $companyimport->quantity =   $external->order;
         $companyimport->bill_to_address = $external->vej.' '.$external->husnr;
         $companyimport->bill_to_address_2 = "";
         $companyimport->bill_to_postal_code = $external->postnr;
         $companyimport->bill_to_city = $external->bynavn;
         $companyimport->ship_to_address = $external->secVej.' '.$external->secHusnr;
         $companyimport->ship_to_address_2 = "";
         $companyimport->ship_to_postal_code = $external->secPostnr;
         $companyimport->ship_to_city =  $external->secBynavn;
         $companyimport->contact_name = $external->fortrolig;
         $companyimport->contact_phone = $external->tlf;
         $companyimport->contact_email = $external->email;
         $companyimport->shipment_method  = $external->leveringsmetode;
         $expiredate = ExpireDate::find_by_display_date_and_blocked($external->leveret, 0);
         if(!isset($expiredate))
          throw new exception('Invalid expire date: '.$external->leveret);

         $companyimport->expire_date =   DateTime::createFromFormat('d-m-Y', $external->leveret);
         $companyimport->value   = '600';
         $companyimport->shop_name = 'Kender du typen';
         $companyimport->salesperson = $external->salesperson;
         $companyimport->noter = $external->noter;


       $companyimport->save();
       $dummy = [];
       $dummy['imported'] = 1;
       response::success(json_encode($dummy));
       $this->confirmOrderKDT($external->id);

     } catch (Exception $e) {

       $dummy['imported'] = 0;
       $dummy['message'] = $e->getMessage();
       response::success(json_encode($dummy));
       $this->rejectOrderKDT($external->id);
   }
    }





    //  *********************** Påske *************************'

      // klades ikke endnu
    // kan de pase al leveringsmetode er blank på allesammen....   dvs inge på email
    // der er ikke nogle gavekort med leveringsadresse, så jeg kan ikke se hvodan de set ud
    public function fetchOrderPaaskegavekortet() {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://Paaskegavekortet.dk/service/sync/dsflkhjkjehkljfdzhkljsdf409843798dkshjdfkljhjdlks.php?token=fdj4fhkj43598fgdds87yg543iuthg");
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.73 Safari/537.36");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        $output = curl_exec($ch);
        $order =  json_decode(base64_decode($output));
        curl_close($ch);
        return($order);
   }

 public function confirmOrderPaaskegavekortet($id) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://Paaskegavekortet.dk/service/sync/confirmSync.php?token=dsflkhjkjdddsddnmdnmbdfff8dkshjdfkljhjdlks&order_id=".$id);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.73 Safari/537.36");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    $output = curl_exec($ch);
    curl_close($ch);
 }

 public function rejectOrderPaaskegavekortet($id) {

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://Paaskegavekortet.dk/service/sync/errorSync.php?token=dsflkhjkjdddsddnmdnmbdfff8dkshjdfkljhjdlks&order_id=".$id);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.73 Safari/537.36");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($ch);
    curl_close($ch);
 }


   public function importFromPaaskegavekortet() {

      try {
           $external = $this->fetchOrderPaaskegavekortet()->data;
           $companyimport = new  CompanyImport();
           $companyimport->cvr = $external->cvr;
           $companyimport->name = $external->firmaNavn;
           $companyimport->quantity = $external->order;
           $companyimport->bill_to_address = $external->vej.' '.$external->husnr;
           $companyimport->bill_to_address_2 = "";
           $companyimport->bill_to_postal_code = $external->postnr;
           $companyimport->bill_to_city = $external->bynavn;
           $companyimport->ship_to_address = $external->secVej.' '.$external->secHusnr;
           $companyimport->ship_to_address_2 = "";
           $companyimport->ship_to_postal_code = $external->secPostnr;
           $companyimport->ship_to_city =  $external->secBynavn;
           $companyimport->contact_name = $external->fortrolig;
           $companyimport->contact_phone = $external->tlf;
           $companyimport->contact_email = $external->email;
           $companyimport->shipment_method ='e';
           $companyimport->expire_date =   '2017-03-27';
           $companyimport->shop_id   = 251;      //Påskagavekortet
           $companyimport->value   = 100;
           $companyimport->shop_name = 'Påskegavekortet';
           $companyimport->salesperson = $external->saleman;
           $companyimport->noter = $external->note;
           $companyimport->save();
           $dummy = [];
           $dummy['imported'] = 1;
           response::success(json_encode($dummy));
           $this->confirmOrderPaaskegavekortet($external->id);
     } catch (Exception $e) {
           $dummy['imported'] = 0;
           $dummy['message'] = $e->getMessage();
           response::success(json_encode($dummy));
          $this->rejectOrderPaaskegavekortet($external->id);
     }
   }




    //  *********************** julegavekortet  *************************'

    // Obs... denne importer pr. kun fra julegavekortet... skal laves funktioner til de andre sites også
    public function importFromJulegavekortet() {
     $n = 0 ;
     External::setConnection('julegavekortet');
     $externals = External::find('all',array(
        'conditions' => array('active = ? AND IsSync = ? AND id >  ?',0,0, 0),
        'order'  => 'id ASC',
        'limit' => 100
    ));

    foreach($externals as $external)    {
       $companyimport = new  CompanyImport();
       $companyimport->cvr = $external->cvr;
       $companyimport->name = $external->firmanavn;
       $companyimport->quantity = $external->order;
       $companyimport->bill_to_address = $external->vej.' '.$external->husnr;
       $companyimport->bill_to_address_2 = "";
       $companyimport->bill_to_postal_code = $external->postnr;
       $companyimport->bill_to_city = $external->bynavn;
       $companyimport->ship_to_address = $external->secvej.' '.$external->sechusnr;
       $companyimport->ship_to_address_2 = "";
       $companyimport->ship_to_postal_code = $external->secpostnr;
       $companyimport->ship_to_city =  $external->secbynavn;
       $companyimport->contact_name = $external->fortrolig;
       $companyimport->contact_phone = $external->tlf;
       $companyimport->contact_email = $external->email;
       $companyimport->shipment_method  = $external->leveringsmetode;


        $expiredate = ExpireDate::find_by_display_date_and_blocked($external->leveret, 0);
        if(!isset($expiredate))
          throw new exception('Invalid expire date: '.$external->leveret);

       $companyimport->expire_date =   DateTime::createFromFormat('d-m-Y', $external->leveret);



       $companyimport->shop_id   = 52;
       $companyimport->value   = 560;
       $companyimport->shop_name = 'Julegavekortet';
       $companyimport->salesperson = $external->salesperson;
       $companyimport->noter = $external->noter;
       $companyimport->save();
       $external->issync = 1;
       $external->save();
       $n+=1;
    }
      $dummy = [];
      $dummy['imported'] = $n;
      response::success(json_encode($dummy));
  }

  public function importFromGuldgavekortet() {
     $n = 0 ;
     External::setConnection('guldgavekortet');
     $externals = External::find('all',array(
        'conditions' => array('active = ? AND IsSync = ? AND id >  ?',0,0,0),
        'order'  => 'id ASC',
        'limit' => 1
    ));


    foreach($externals as $external)    {
       $companyimport = new  CompanyImport();
       $companyimport->cvr = $external->cvr;
       $companyimport->name = $external->firmanavn;
       $companyimport->quantity = $external->order;

       $companyimport->bill_to_address = $external->vej.' '.$external->husnr;
       $companyimport->bill_to_address_2 = "";
       $companyimport->bill_to_postal_code = $external->postnr;
       $companyimport->bill_to_city = $external->bynavn;
       $companyimport->ship_to_address = $external->secvej.' '.$external->sechusnr;
       $companyimport->ship_to_address_2 = "";
       $companyimport->ship_to_postal_code = $external->secpostnr;
       $companyimport->ship_to_city =  $external->secbynavn;
       $companyimport->contact_name =  $external->fortrolig;
       $companyimport->contact_phone = $external->tlf;
       $companyimport->contact_email = $external->email;
       $companyimport->shipment_method  = $external->deliverymothode;


        $expiredate = ExpireDate::find_by_display_date_and_blocked($external->leveret, 0);
        if(!isset($expiredate))
          throw new exception('Invalid expire date: '.$external->leveret);

       $companyimport->expire_date =   DateTime::createFromFormat('d-m-Y', $external->leveret);


       $companyimport->shop_id   =  53;
       $companyimport->value     =  800;
       $companyimport->shop_name = 'Guldgavekortet';
       $companyimport->salesperson = $external->salesperson;
       $companyimport->noter = $external->noter;

       $companyimport->save();

       $external->issync = 1;
       $external->save();
       $n+=1;
    }
      $dummy = [];
      $dummy['imported'] = $n;
      response::success(json_encode($dummy));
  }





      //  *********************** Julegavekortet NO  *************************'
   public function importFromJulegavekortetNO() {
     $n = 0;
     External::setConnection('julegavekortetNo');
     $externals = External::find('all',array(
        'conditions' => array('active = ? AND IsSync = ? AND id >  ?',0,0,0),
        'order'  => 'id ASC',
        'limit' => 10
    ));

    // print_R($externals);

    foreach($externals as $external)    {
       $companyimport = new  CompanyImport();
       $companyimport->cvr = $external->cvr;
       $companyimport->name = $external->firmanavn;
       $companyimport->quantity = $external->order;

       $companyimport->bill_to_address = $external->vej.' '.$external->husnr;
       $companyimport->bill_to_address_2 = "";
       $companyimport->bill_to_postal_code = $external->postnr;
       $companyimport->bill_to_city = $external->bynavn;


       $companyimport->ship_to_company = $external->sekvirk;
       $companyimport->ship_to_address = $external->secvej.' '.$external->sechusnr;
       $companyimport->ship_to_address_2 = "";
       $companyimport->ship_to_postal_code = $external->secpostnr;
       $companyimport->ship_to_city =  $external->secbynavn;

       $companyimport->contact_name = $external->fortrolig;
       $companyimport->contact_phone = $external->tlf;
       $companyimport->contact_email = $external->email;

       $companyimport->shipment_method  = $external->shipment_method;
       $companyimport->giftwrap = $external->giftwrap;

       $companyimport->expire_date =   DateTime::createFromFormat('d-m-Y', $external->leveret);
  //     $companyimport->expire_date =   correctDate2($companyimport->expire_date);     // Fix dato

       if($external->deleveryvalue == '400') {
           $companyimport->shop_id   = 57;
       } elseif($external->deleveryvalue == '600') {
           $companyimport->shop_id   = 58;
       } elseif($external->deleveryvalue == '800') {
           $companyimport->shop_id   = 59;
       } elseif($external->deleveryvalue == '300') {
           $companyimport->shop_id   = 272;
       }   else {
           throw new exception('invalid value');
       }

       $companyimport->value   = $external->deleveryvalue;
       $companyimport->shop_name = 'Julegavekortet NO';
       $companyimport->salesperson = $external->salesperson;
       $companyimport->noter = $external->noter;
       $companyimport->save();

       $external->issync = 1;
       $external->save();
       $n+=1;
      }
      $dummy = [];
      $dummy['imported'] = $n;
      response::success(json_encode($dummy));
  }

  public function updateOrderPresentDetails() {
      $system = System::first();
      $n=0;
      $presents = Present::find('all', array(
        'conditions' => array('modified_datetime > ?',$system->last_order_update)
      ));
      foreach($presents as $present) {
          $variantlist = json_decode($present->variant_list);
          if (count($variantlist)>0)
          {
          foreach($variantlist as $variant)
            {
              if($variant->language_id==1)
              {
                $modelname = ($variant->feltData[0]->variant)."###".($variant->feltData[1]->variantSub);
                $modelno = ($variant->feltData[2]->variantNr);
                $orders = Order::find('all',array(
                    'conditions'=> array('present_id = ? AND present_model_present_no= ?' ,$present->id,$modelno)));
               foreach($orders as $order)
                   {
                     $presentchanged = false;
                     $presentchanged = false;

                     if($order->present_name != $present->name) {
                       $order->present_name =    $present->name;
                       $presentchanged = true;
                     }

                    if($order->present_internal_name != $present->internal_name) {
                       $order->present_internal_name =    $present->internal_name;
                       $presentchanged = true;
                     }
                    if($order->present_vendor != $present->vendor) {
                       $order->present_vendor =    $present->vendor;
                       $presentchanged = true;
                     }

                    if($order->present_model_name != $modelname) {
                       $order->present_model_name =    $modelname;
                       $presentchanged = true;
                     }
                     if($presentchanged) {
                       $order->save();
                       $n+=1;
                     }
                  }
              }
             }
          } else {
              $orders = Order::find('all',array('present_id'=>$present->id));
              foreach($orders as $order)
                   {
                     $presentchanged = false;

                     if($order->present_name != $present->name) {
                       $order->present_name =    $present->name;
                       $presentchanged = true;
                     }

                     if($order->present_internal_name != $present->internal_name) {
                       $order->present_internal_name =    $present->internal_name;
                       $presentchanged = true;
                     }
                     if($order->present_vendor != $present->vendor) {
                       $order->present_vendor =    $present->vendor;
                       $presentchanged = true;
                     }
                     if($presentchanged) {
                       $order->save();
                       $n+=1;
                     }
                  }
                }
          }
      $system->last_order_update = date('d-m-Y H:i:s');
      $system->save();
      $dummy = [];
      $dummy['updated'] = $n;
      response::success(json_encode($dummy));
     }
   }





  function nxs_cURLTest($url, $msg, $testText){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.73 Safari/537.36");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    $response = curl_exec($ch);
    $errmsg = curl_error($ch);
    $cInfo = curl_getinfo($ch);
    curl_close($ch);
    echo $response;
}


?>
