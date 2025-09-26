<?php
// Model Shop
// Date created  Mon, 16 Jan 2017 15:29:32 +0100
// Created by Bitworks
//***************************************************************
//   (PRI) id                            int(11)             NO
//   (MUL) name                          varchar(50)         NO
//   (   ) alias                         varchar(100)        YES
//   (UNI) link                          varbinary(100)      NO
//   (   ) is_gift_certificate           tinyint(1)          YES
//   (   ) is_company                    tinyint(1)          YES
//   (   ) is_demo                       tinyint(1)          YES
//   (   ) demo_username                 varchar(45)         YES
//   (   ) demo_password                 varchar(45)         YES
//   (   ) demo_user_id                  int(11)             YES
//   (   ) start_date                    date                YES
//   (   ) start_time                    time                YES
//   (MUL) end_date                      date                YES
//   (   ) end_time                      time                YES
//   (   ) expire_warning_date           date                YES
//   (   ) image_path                    varchar(1024)       YES
//   (   ) logo_enabled                  tinyint(1)          YES
//   (   ) zoom_enabled                  tinyint(1)          YES
//   (   ) language_enabled              varchar(1024)       YES
//   (   ) language_settings             varchar(1024)       YES
//   (   ) email_list                    varchar(1024)       YES
//   (   ) active                        tinyint(4)          YES
//   (   ) deleted                       tinyint(4)          YES
//   (   ) no_series                     int(11)             YES
//   (   ) reservation_group             int(11)             YES
//   (   ) open_for_registration         tinyint(4)          YES
//   (   ) blocked                       tinyint(4)          YES
//   (   ) blocked_text                  text                YES
//   (   ) mailserver_id                 int(11)             YES
//   (   ) language_id                   int(11)             YES
//   (   ) shipment_date                 date                NO
//***************************************************************
class Shop extends BaseModel {
  static $table_name = "shop";
  static $primary_key = "id";

  //Relations
  static $has_many = array(
                     array('company_shops', 'class_name' => 'CompanyShop'),
                     array('presents', 'class_name' => 'ShopPresent','order' => 'index_ asc'),
                     array('descriptions', 'class_name' => 'ShopDescription'),
                     array('attributes_','class_name' => 'ShopAttribute','order' => "`index` asc"),
                     array('addresses','class_name' => 'ShopAddress','order' => "`index` asc"),
                     array('users', 'class_name' => 'ShopUser'),
                     array('orders', 'class_name' => 'Order'),
                     array('deactivated_models', 'class_name' => 'ShopModel'),
                     array('company', 'class_name' => 'Company', 'through' => 'company_shops'),
                     array('reports', 'class_name' => 'ShopReport'),
    );



  static $calculated_attributes = array("has_orders","has_users","company");

  static $before_create = array('onBeforeCreate');
  static $after_create = array('onAfterCreate');
  static $before_update = array('onBeforeUpdate');
  static $after_update = array('onAfterUpdate');
  static $before_destroy = array('onBeforeDestroy'); // virker ikke
  static $after_destroy = array('onAfterDestroy');

  public function company() {
     //Der b�r h�jest ligge en valgshop-company pr shop.
     foreach($this->company_shops as $companyshop) {
      $company = Company::find($companyshop->company_id);
      if($company->is_gift_certificate==0) {
          return((array)$company->attributes) ;
      }
     }
  }

  public function has_orders() {
    $orders = Order::find_by_sql("select count(*) as quantity from `order` WHERE shop_id=$this->id AND is_demo=0");
    return($orders[0]->quantity);
   }

  public function has_users() {
    $shopusers = ShopUser::find_by_sql("select count(*) as quantity from `shop_user` WHERE shop_id =$this->id AND is_demo=0");
    return($shopusers[0]->quantity);
   }
  function onBeforeCreate() {
    $this->created_datetime = date('d-m-Y H:i:s');
    $this->modified_datetime = date('d-m-Y H:i:s');
    $this->validateFields();
  }
  function onAfterCreate() {
  }
  function onBeforeUpdate() {
    $this->modified_datetime = date('d-m-Y H:i:s');
    $this->validateFields();
  }
  function onAfterUpdate() {
  }
  function onBeforeDestroy() {
      if($this->is_gift_certificate==1)
        throw new exception('gavekort shops kan ikke slettes');
  }
  function onAfterDestroy() {

    ShopDescription::table()->delete(array('shop_id' => $this->id));     // trigger not called
    ShopAttribute::table()->delete(array('shop_id' => $this->id));
    ShopReport::table()->delete(array('shop_id' => $this->id));
    PresentReservation::table()->delete(array('shop_id' => $this->id));

    foreach($this->company_shops as $companyshop) {
      $company = Company::find($companyshop->company_id);
      $company->delete(true);
    }
    CompanyShop::table()->delete(array('shop_id' => $this->id));       // triggers bot called

    foreach ($this->users as $shopuser)
    {
       $shopuser->delete(true);
    }


    foreach ($this->presents as $shoppresent)
    {
       $present = Present::find($shoppresent->present_id);
       if($present->shop_id==$this->id) {
         $present->delete(true);
       }
       $shoppresent->delete(true);
    }

  }
  function validateFields() {
    //TODO: Check at is_compay of is_fodt certificate begger er 1
    testRequired($this, 'name');
    testRequired($this, 'link');
    testMaxLength($this, 'name', 50);

    testMaxLength($this, 'image_path', 1024);
    testMaxLength($this,'language_enabled',1024);
    testMaxLength($this,'email_list',1024);
    testMaxLength($this,'demo_username',45);
    testMaxLength($this,'demo_password',45);

    $this->image_path = trimgf($this->image_path);
    $this->name = trimgf($this->name);
    $this->language_enabled = trimgf($this->language_enabled);
    $this->email_list = trimgf($this->email_list);
    $this->link = strtolower($this->link);
   }

  static public function readShop($id) {

        $shop = Shop::find($id);
        return($shop);
    }
  static public function deleteShop($id, $realDelete = true) {
      $shop->deleted = 1;
      $shop->save();
  }
  //---------------------------------------------------------------------------------------
  // Custom Methods
  //---------------------------------------------------------------------------------------

    public static function updateSingle($data) {
        $shop = Shop::find($data["id"]);
        $shop->update_attributes($data);
        $shop->save();

    }



    public function addCompany($companyId) {

        $companyshop = new CompanyShop();
        $companyshop->company_id = $companyId;
        $companyshop->shop_id = $this->id;
        $companyshop->save();
    }

    public static function removeCompany($companyId) {
        //skal implementeres
    }

  public static function createGiftcertificateShop($data) {
  }

  // Opret valgshop
  public static function createCompanyShop($data) {


    //Fetch data
    $shopData = (array) json_decode($data['shop']);
    $descriptionData = json_decode($data['descriptions']);
    $companyData = (array) json_decode($data['company']);

    //1. Create Company
    $company = new Company();
    $company->update_attributes($companyData);
    $company->save();

    $username = $companyData['username'];
    $password = $companyData['password'];

    $token = getToken(30);
    //2. Create Shop
    $shop = new Shop();
    $shop->update_attributes($shopData);
    $shop->is_company = true;
    $shop->demo_username = $username;
    $shop->demo_password = $password;
    $shop->token = $token;
    $shop->save();

    //3. Create Shop Descriptions
    foreach ($descriptionData as $description) {
      $shopDescription = new ShopDescription();
      $shopDescription->shop_id = $shop->id;
      $shopDescription->update_attributes((array)$description);
      $shopDescription->save();
    }

    $shop->addCompany($company->id);
    $shop->save();


   // Create default attributes;
   $shopattribute = new ShopAttribute();
   $shopattribute->shop_id = $shop->id;
   $shopattribute->index  = 2;
   $shopattribute->name  = 'Brugernavn';
   $shopattribute->is_username  = 1;
   $shopattribute->data_type = 1;
   $shopattribute->save();

   // Create default attributes;
   $shopattribute = new ShopAttribute();
   $shopattribute->shop_id = $shop->id;
   $shopattribute->index  = 3;
   $shopattribute->name  = 'Password';
   $shopattribute->is_password  = 1;
   $shopattribute->data_type = 1;
   $shopattribute->save();

   // Create default attributes;
   $shopattribute = new ShopAttribute();
   $shopattribute->shop_id = $shop->id;
   $shopattribute->index  = 1;
   $shopattribute->name  = 'Navn';
   $shopattribute->is_name  = 1;
   $shopattribute->is_visible  = 1;
   $shopattribute->is_mandatory  = 1;
   $shopattribute->data_type = 1;
   $shopattribute->is_locked = 1;
   $shopattribute->save();

   // Create default attributes;
   $shopattribute = new ShopAttribute();
   $shopattribute->shop_id = $shop->id;
   $shopattribute->index  = 4;
   $shopattribute->name  = 'Email';
   $shopattribute->is_email  = 1;
   $shopattribute->is_visible  = 1;
   $shopattribute->is_mandatory  = 1;
   $shopattribute->data_type = 1;
   $shopattribute->save();

      // Create default attributes;
   $shopattribute = new ShopAttribute();
   $shopattribute->shop_id = $shop->id;
   $shopattribute->index  = 5;
   $shopattribute->name  = 'Gaveklubben tilmelding';
   $shopattribute->is_visible  = 1;
   $shopattribute->is_mandatory  = 1;
   $shopattribute->data_type = 1;
   $shopattribute->is_list = 1;
   $shopattribute->list_data = "#radio#ja
#radio#nej";
   $shopattribute->save();



    //Create a demo user
   	$shopUser= new ShopUser();
    $shopUser->shop_id = $shop->id;
    $shopUser->company_id = $company->id;
    $shopUser->username = $username;
    $shopUser->password = $password;
    $shopUser->is_demo = 1;
    $shopUser->save(false);
    $shop->demo_user_id = $shopUser->id;
    $shop->save();


    //Opret demo brugers attributes
    foreach($shop->attributes_ as $shopattribute) {
      $userattribute = new UserAttribute();
      $userattribute->shopuser_id  = $shopUser->id;
      $userattribute->attribute_id   = $shopattribute->id;
      $userattribute->shop_id     = $shop->id;
      $userattribute->company_id  = $company->id;
      $userattribute->is_username = $shopattribute->is_username;
      $userattribute->is_password = $shopattribute->is_password;
      $userattribute->is_email    = $shopattribute->is_email;
      $userattribute->is_name     = $shopattribute->is_name;
      if($userattribute->is_username ==1)
        $userattribute->attribute_value = $username;

      if($userattribute->is_password ==1)
        $userattribute->attribute_value = $password;
      $userattribute->save();
    }

      // Opret tom shop_metadata
      $c = new ShopMetadata();
      $c->shop_id = $shop->id;
      $c->save();

        return ($shop);

   }


  public static function updateCompanyShop($data) {


    //Fetch data
    $shopData = (array) json_decode($data['shop']);

    if($shopData['start_date']=="stop"){
        unset($shopData['start_date']);
    } else {
        if ($shopData['start_date'] == "###")
            $shopData['start_date'] = "";

        if ($shopData['start_date']) {
            if (!preg_match("/\d{4}\-\d{2}-\d{2}/", $shopData['start_date'])) {
                throw new exception('Ugyldig datoformat i start dato');
            }
        }

        // Handle start_time field
        if(isset($shopData['start_time'])) {
            if($shopData['start_time'] == "stop" || $shopData['start_time'] == "###" || $shopData['start_time'] == "") {
                $shopData['start_time'] = null;
            } else if (!preg_match("/\d{2}:\d{2}(:\d{2})?/", $shopData['start_time'])) {
                throw new exception('Ugyldig tidsformat i start tid');
            }
        }
    }
    if($shopData['end_date']=="stop"){
        unset($shopData['end_date']);
    } else {
        if($shopData['end_date']=="###")
           $shopData['end_date'] ="";

        if($shopData['end_date']) {
            if (!preg_match("/\d{4}\-\d{2}-\d{2}/", $shopData['end_date'])) {
                throw new exception('Ugyldig datoformat i slut dato');
            }
        }

        // Handle end_time field
        if(isset($shopData['end_time'])) {
            if($shopData['end_time'] == "stop" || $shopData['end_time'] == "###" || $shopData['end_time'] == "") {
                $shopData['end_time'] = null;
            } else if (!preg_match("/\d{2}:\d{2}(:\d{2})?/", $shopData['end_time'])) {
                throw new exception('Ugyldig tidsformat i slut tid');
            }
        }
    }
    $descriptionData = (array) json_decode($data['descriptions']);
    $companyData = (array) json_decode($data['company']);

    $username = $companyData['username'];
    $password = $companyData['password'];

    //1. Update Company
    $company = Company::find($companyData['id']);
    $company->update_attributes($companyData);
    $company->save();

      //1.2 Load shop
      $shop = Shop::find($shopData['id']);

      // 1.3 Check logo on shop has not been reset
      if(trimgf(str_replace(array("###","none"),"",$shop->image_path)) != "" && trimgf(str_replace(array("###","none"),"",$shopData["image_path"])) == "")
      {
          throw new exception('Logo er fjernet fra shop, sørg for at der er valgt et logo.');
      }

      //2. Update Shop
      $shop->update_attributes($shopData);
      $shop->demo_username = $username;
      $shop->demo_password = $password;
      $shop->save();

    //3. Update Shop Descriptions
    foreach ($descriptionData as $description) {
      $shopDescription = ShopDescription::find($description->id);

      // Check description
      if(trimgf(str_replace("###","",$shopDescription->description)) != "" && trimgf(str_replace("###","",$description->description)) == "")
      {
        throw new exception('Der mangler beskrivelse til shoppen (lang id '.$shopDescription->language_id.')');
      }

      $shopDescription->update_attributes((array)$description);
      $shopDescription->save();
    }

		// Update address
		if(isset($_POST["addresslist"]))
		{
				ShopAddress::updateShopAddresses($shop->id,$_POST["addresslist"]);
		}

   //Opret demo bruger hvis denne ikke findes
   if($shop->demo_user_id==0) {

    $shopUser= new ShopUser();
    $shopUser->shop_id = $shop->id;
    $shopUser->company_id = $company->id;
    $shopUser->username = $username;
    $shopUser->password = $password;

    $shopUser->is_demo = 1;
    $shopUser->save(false);
    $shop->demo_user_id = $shopUser->id;
    $shop->save();

    } else {
        $shopUser = ShopUser::find($shop->demo_user_id);
        $shopUser->username = $username;
        $shopUser->password = $password;
        $shopUser->save();
    }

    //opdater/opret demo brugers attributes hvis der er tilf�jet �ndre p� dem
    foreach($shop->attributes_ as $shopattribute) {
        $userattribute = UserAttribute::find_by_shopuser_id_and_attribute_id($shopUser->id,$shopattribute->id);
        if(!isset($userattribute))
          $userattribute = new UserAttribute();
         $userattribute->shopuser_id  = $shopUser->id;
         $userattribute->attribute_id   = $shopattribute->id;
         $userattribute->shop_id     = $shop->id;
         $userattribute->company_id  = $company->id;
         $userattribute->is_username = $shopattribute->is_username;
         $userattribute->is_password = $shopattribute->is_password;
         $userattribute->is_email    = $shopattribute->is_email;
         $userattribute->is_name     = $shopattribute->is_name;
         if($userattribute->is_username ==1)
           $userattribute->attribute_value = $username;

         if($userattribute->is_password ==1)
           $userattribute->attribute_value =  $password;

         $userattribute->save();
    }
  }

  //Create or update demo user
  private function updateDemoUser() {


  }

  // Present functions
  static public function addPresent($data) {
    $shopPresent = new ShopPresent();
    $shopPresent->update_attributes($data);
    $shopPresent->save();
    return($shopPresent);
  }
  // fjerner alle gaver på preview shoppen
  static public function previewPresent($data)  {
    $shopID =  $data["shop_id"];
    $shopPresent = ShopPresent::find_by_shop_id($shopID);
    if($shopPresent)   {    $shopPresent->delete();       }
   $shopPresent = new ShopPresent();
    $shopPresent->update_attributes($data);
    $shopPresent->save();
    return($shopPresent);

  }
  static public function removePresent($data) {
    $shopPresent = ShopPresent::find_by_shop_id_and_present_id($data['shop_id'],$data['present_id']);
    $shopPresent->delete();
  }

  static public function activatePresent($id) {
    $shopPresent = ShopPresent::find($id);
    $shopPresent->active = 1;
    $shopPresent->save();
  }

  static public function deactivatePresent($id) {
    $shopPresent = ShopPresent::find($id);
    $shopPresent->active = 0;
    $shopPresent->save();
  }


  // Attribute functions
  static public function addAttribute($data) {
    // bloker hvis der ligger ordre, eller brugere er tilf�jes..
    $ShopAttribute = new ShopAttribute();
    $ShopAttribute->update_attributes($data);
    $ShopAttribute->save();
    // tilf�j attribute til eksisterende brugere:
    $shopusers = ShopUser::all(array('shop_id' => $ShopAttribute->shop_id));
    foreach($shopusers as $shopuser) {
        //2. Create User Attributes
        $userAttribute = new UserAttribute();
        $userAttribute->shop_id =         $ShopAttribute->shop_id;
        $userAttribute->company_id =      $shopuser->company_id;
        $userAttribute->shopuser_id =     $shopuser->id;
        $userAttribute->attribute_id =    $ShopAttribute->id;
        $userAttribute->attribute_value = '';
        $userAttribute->is_username =     $ShopAttribute->is_username;
        $userAttribute->is_password =     $ShopAttribute->is_password;
        $userAttribute->is_email =        $ShopAttribute->is_email;
        $userAttribute->is_name =         $ShopAttribute->is_name;
        $userAttribute->save();
    }
    return($ShopAttribute);
  }

  static public function updateAttribute($data) {
    $ShopAttribute =  ShopAttribute::find($data['id']);
    $ShopAttribute->  update_attributes($data);
    $ShopAttribute-> save();
    return($ShopAttribute);
  }
  static public function removeAttribute($id)  {
    $ShopAttribute = ShopAttribute::find($id);
    $ShopAttribute->delete();
    UserAttribute::table()->delete(array('attribute_id' => $id));
    return($ShopAttribute);
  }



  // User functions
  static public function removeShopUser($id) {

    $shopUser = ShopUser::find($id);
    $shop = Shop::find($shopUser->shop_id);
    if($shop->is_gift_certificate)
    {
        //  ud kommenter denne linje hvis du �nsker at l�gge gavekort tilbage
        //  throw new exception("Gavekort kan ikke slettes. Deaktiver gavekortet i stedet for.");
        $giftcertificate = GiftCertificate::find_by_certificate_no($shopUser->username);
        $giftcertificate->shop_id = 0;
        $giftcertificate->company_id = 0;
        $giftcertificate->save();
    }

    $shopUser->delete();

    }


  static public function updateShopUser($data) {
        $shopData = (object) $data;
        $shop = Shop::find($shopData->shop_id);
        $shopUser= ShopUser::find($shopData->user_id);
        $userData = (Array) json_decode($data['attributes']);
        foreach($userData as $attributeData) {
            $shopAttribute = ShopAttribute::find($attributeData->attribute_id);
            $userAttribute = UserAttribute::find_by_shopuser_id_and_attribute_id($shopData->user_id,$attributeData->attribute_id);
            $userAttribute->attribute_value = $attributeData->attribute_value;
            $userAttribute->save();
            if($userAttribute->is_username ==1)
              $shopUser->username = $attributeData->attribute_value;

            if($userAttribute->is_password ==1)
              $shopUser->password = $attributeData->attribute_value;

        }
        $shopUser->save();
       return($shopUser);
    }





    static public function addShopUser2($data) {



  $shopData = (object) json_decode($data['data']);
    $shop = Shop::find($shopData->shopId);
    ShopUser::validateShopAttributes($shopData->shopId);

    $userData = (Array) json_decode($data['attributes_']);

    $shopAttributeCount = countgf($shop->attributes_);
    $dataAttributeCount =  countgf($userData);
    if($shopAttributeCount <> $dataAttributeCount) {
       throw new exception("Fejl!. Antallet af attributter er $dataAttributeCount men der er definerer $shopAttributeCount attributter p� shoppen");
    }


    $shopUser= new ShopUser();
    $shopUser->shop_id = $shop->id;
    $shopUser->company_id = $shopData->companyId;     // denne er nem nok for valgshop.. man vi skal angive dennn ellers
    $shopUser->username = uniqid();     			  // Generer tmp unik username
    $shopUser->password = uniqid();     		 	  // Generer tmp unik password
    $shopUser->is_demo = 0;
    $shopUser->save(false);

    foreach($userData as $attributeData) {
        //2. Create User Attributes
        $shopAttribute = ShopAttribute::find($attributeData->id);
        $userAttribute = new UserAttribute();
        $userAttribute->shop_id = $shop->id;
        $userAttribute->company_id = $shopData->companyId;
        $userAttribute->shopuser_id = $shopUser->id;
        $userAttribute->attribute_id = $shopAttribute->id;
        $userAttribute->attribute_value = $attributeData->value;
        $userAttribute->is_username = $shopAttribute->is_username;
        $userAttribute->is_password = $shopAttribute->is_password;
        $userAttribute->is_email = $shopAttribute->is_email;
        $userAttribute->is_name = $shopAttribute->is_name;
        $userAttribute->save();

        if($userAttribute->is_username ==1)
          $shopUser->username = $attributeData->value;

        if($userAttribute->is_password ==1)
           $shopUser->password = $attributeData->value;
    }



     $existinguser = ShopUser::find_by_shop_id_and_username($shop->id, $shopUser->username);

     if(countgf($existinguser) >0) {
         throw new exception('dublet');
     }

      $shopUser->save();
      return($shopUser);

   }




    static public function addShopUser($data) {

    $shopData = (object) json_decode($data['data']);
    $shop = Shop::find($shopData->shopId);
    ShopUser::validateShopAttributes($shopData->shopId);

    $userData = (Array) json_decode($data['attributes_']);

    $shopAttributeCount = countgf($shop->attributes_);
    $dataAttributeCount =  countgf($userData);
    if($shopAttributeCount <> $dataAttributeCount) {
       throw new exception("Fejl!. Antallet af attributter er $dataAttributeCount men der er definerer $shopAttributeCount attributter p� shoppen");
    }


    $shopUser= new ShopUser();
    $shopUser->shop_id = $shop->id;
    $shopUser->company_id = $shopData->companyId;     // denne er nem nok for valgshop.. man vi skal angive dennn ellers
    $shopUser->username = uniqid();     			  // Generer tmp unik username
    $shopUser->password = uniqid();     		 	  // Generer tmp unik password
    $shopUser->is_demo = 0;
    $shopUser->save(false);

    foreach($userData as $attributeData) {
        //2. Create User Attributes
        $shopAttribute = ShopAttribute::find($attributeData->id);
        $userAttribute = new UserAttribute();
        $userAttribute->shop_id = $shop->id;
        $userAttribute->company_id = $shopData->companyId;
        $userAttribute->shopuser_id = $shopUser->id;
        $userAttribute->attribute_id = $shopAttribute->id;
        $userAttribute->attribute_value = cleanText($attributeData->value);
        $userAttribute->is_username = $shopAttribute->is_username;
        $userAttribute->is_password = $shopAttribute->is_password;
        $userAttribute->is_email = $shopAttribute->is_email;
        $userAttribute->is_name = $shopAttribute->is_name;
        $userAttribute->save();

        if($userAttribute->is_username ==1)
          $shopUser->username = cleanText($attributeData->value);

        if($userAttribute->is_password ==1)
           $shopUser->password = cleanText($attributeData->value);
    }



     $existinguser = ShopUser::find_by_shop_id_and_username($shop->id, $shopUser->username);



     if( $existinguser != null ) {
         throw new exception('dublet');
     }
     $shopUser->save();
     return($shopUser);
   }




   static public function getUsersWithNoOrders($shop_id) {
    $shopusers = ShopUser::all(array('shop_id' =>$shop_id));
    $result = [];
    foreach($shopusers as $shopuser) {
        if(count($shopuser->orders)==0)
          array_push($result,$shopuser);
    }
    return($result);
   }

}
   function crypto_rand_secure($min, $max)
{
    $range = $max - $min;
    if ($range < 1) return $min; // not so random...
    $log = ceil(log($range, 2));
    $bytes = (int) ($log / 8) + 1; // length in bytes
    $bits = (int) $log + 1; // length in bits
    $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
    do {
        $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
        $rnd = $rnd & $filter; // discard irrelevant bits
    } while ($rnd > $range);
    return $min + $rnd;
}

   function getToken($length)
{
    $token = "";
    $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
    $codeAlphabet.= "0123456789";
    $max = strlen($codeAlphabet); // edited

    for ($i=0; $i < $length; $i++) {
        $token .= $codeAlphabet[crypto_rand_secure(0, $max-1)];
    }

    return $token;
}
function cleanText($string) {
    // Remove newlines and trim whitespace
    return trim(str_replace(["\r\n", "\r", "\n"], '', $string));
}

?>