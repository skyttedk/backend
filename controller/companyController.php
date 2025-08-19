<?php
// Controller Order
// Date created  Thu, 26 May 2016 19:44:28 +0200
// Created by Bitworks
class CompanyController Extends baseController {

  public function Index() {
  }

  public function readByShopID(){
      $CompanyIndex = \Company::find_by_sql("SELECT company_id FROM `company_shop` WHERE `shop_id` = ".intval($_POST["shop_id"]));
      $companyID = $CompanyIndex[0]->attributes["company_id"];
      $result = Company::find($companyID);
      response::success(make_json("result", $result));
  }

  public function getParentCompany(){
     $result =  Company::find('all',array(
        'conditions' => array('id' => $_POST['id'],'deleted'=>0,'active'=>1)
     ));
    response::success(make_json("result", $result));
  }
    public function updateShippingDeal(){

    $companyID =  $_POST["companyID"];
    $cost = $_POST["cost"];
    if($cost == ""){
      $cost = -1;
    }
    $result = Dbsqli::getSql2("select * from company_shipping_cost where company_id=".$companyID);
    if(sizeofgf($result) == 0){
        $sql = "insert into company_shipping_cost (company_id,cost) value (".$companyID.",".$cost." )";
        Dbsqli::setSql2($sql);
    } else {
       $sql ="update company_shipping_cost set cost = ".$cost." where company_id =". $companyID;
       Dbsqli::setSql2($sql);
    }

    $result = Dbsqli::getSql2("select * from company_shipping_cost where company_id=".$companyID);
    if(sizeofgf($result) == 0){
             echo "No shopping deal";
       } else {
         if($result[0]["cost"] == -1){
               echo "No shopping deal";
         } else {
              echo $result[0]["cost"];
         }

       }

  }
  public function readShippingDeal(){
    $companyID =  $_POST["companyID"];
       $result = Dbsqli::getSql2("select * from company_shipping_cost where company_id=".$companyID);
       if(sizeofgf($result) == 0){
             echo "No shopping deal";
       } else {
         if($result[0]["cost"] == -1){
               echo "No shipping deal";
         } else {
              echo $result[0]["cost"];
         }

       }

  }


  public function updateCardCount(){
        $sql = "SELECT company.id, count(shop_user.company_id) as antal FROM `company` inner join shop_user on company.id = shop_user.company_id and shop_user.blocked = 0 GROUP by shop_user.company_id  order by antal DESC;";
        $rs = Dbsqli::getSql2($sql);

        foreach($rs as $item){
            try {
                  $sql = "update `company` set `hasCard` = ".$item["antal"]." where `id` = ". $item["id"];
                  Dbsqli::setSql2($sql);
            } catch (Exception $e) {

            }
        }
        $dummy = array();
        response::success(make_json("result", $dummy));
  }





   public function cardShopSale() {
     $sql = "SELECT shop.name, COUNT(*) as antal,   `expire_date`.`week_no`  FROM gavefabrikken_2019.`shop_user`
INNER JOIN gavefabrikken_2019.shop ON `shop_user`.`shop_id` = shop.id
inner join gavefabrikken_2019.expire_date on `shop_user`.expire_date =  `expire_date`.`expire_date`

WHERE `shop_user`.`company_id` = ".$_POST["company_id"]."
AND
`shop_user`.`blocked` = 0
GROUP BY
`shop_user`.`shop_id`, `shop_user`.expire_date
order by `shop_user`.`shop_id`, `shop_user`.expire_date
";
  $result = Dbsqli::getSql($sql);
      echo $result;
    //response::success(make_json("result", $result));
  }
  public function getChildsCompany(){
   $result =  Company::find('all',array(
     'conditions' => array('pid' => $_POST['id'],'deleted'=>0,'active'=>1)
     ));
    response::success(make_json("result", $result));
  }

  public function getNotes(){
     $companies = Company::find($_POST["company_id"]);
     response::success(make_json("result", $companies));
  }

  public function getSpDealsOrders(){
     $sql ="SELECT company_order.*, company.pid, (SELECT count(id) FROM `company` where pid = ".$_POST['company_id'].") as antal   FROM `company_order`
        inner JOIN company ON
        company.id = company_order.company_id WHERE is_cancelled = 0 and company_order.`company_id` = ".$_POST['company_id']." order by id DESC";

   $company_order = Companyorder::find_by_sql($sql);
   /*
        find_by_sql
     $company_order = Companyorder::find('all',array(
     'conditions' => array('company_id' => $_POST['company_id'],'is_cancelled'=>0),
     'order' => 'id DESC'
     ));
    */
     //$earlyStr = $company_order[0]->attributes["earlyorderlist"];
     //$earlyList = explode(" ", $earlyStr);
     response::success(make_json("result", $company_order));
  }
  public function changeSendOrderMail(){
   $companyOrder = Companyorder::find($_POST['id']);
     if($companyOrder->send_welcome_mail == 0){
        $companyOrder->send_welcome_mail = 1;
     } else {
        $companyOrder->send_welcome_mail = 0;
     }
      $companyOrder->save();
       $dummy = array();

     response::success(make_json("result", $companyOrder));
  }
  public function sendOrderMail(){
        $_POST["id"];
            $dummy = array();
     response::success(make_json("result", $dummy));
  }



  public function saveRapportNote(){
   $company = Company::find($_POST['company_id']);
   $company->rapport_note = $_POST['rapport_note'];
   $company->save();
        $dummy = array();
     response::success(make_json("result", $dummy));
  }
  public function saveInternalNote(){
   $company = Company::find($_POST['company_id']);
   $company->internal_note = $_POST['internal_note'];
   $company->save();
        $dummy = array();
     response::success(make_json("result", $dummy));
  }


  //denne bruges af ordreformular
  public function Search() {
    $what = $_POST['search'];
    $company = Company::all(array('conditions' => array(
    "deleted = 0 and is_gift_certificate = 1 and
    (
        name like '%$what%'  or
        cvr like '%$what%'
    )"
    )));
    response::success(make_json("result", $company));
  }

   public function deleteGiftCertificateCompany() {
     Company::deleteCompany($_POST['company_id']);
     $dummy = array();
     response::success(make_json("result", $dummy));
  }

  public function createGiftCertificateCompany() {
    //1. Create Company
    $companydata = (array) $_POST['companydata'];
    $companydata['username'] = $companydata['cvr'];
    $companydata['password'] = $companydata['cvr'];
    $companydata['is_gift_certificate'] = 1;
    $company = new Company();
    $company->update_attributes($companydata);
    $company->save();
    response::success(make_json("result", $company));

  }

  public function updateGiftCertificateCompany() {
   //1. Update Company
   $companydata = (array) $_POST['companydata'];
   $company = Company::find($_POST['company_id']);
   $company->update_attributes($companydata);
   $company->address_updated   = 1;
   $company->save();

   //opdater et eller ande flueben p� ordre
   $companyorders = CompanyOrder::find('all', array('conditions' => array( 'company_id' => $_POST['company_id'])));
   foreach($companyorders as $companyorder) {
        CompanyOrder::archiveCompanyOrder($companyorder->id);      //arkiver den f�rst

        $companyorder->company_name = $company->name;
        $companyorder->ship_to_address = $company->ship_to_address;
        $companyorder->ship_to_address_2 = $company->ship_to_address_2;
        $companyorder->ship_to_postal_code = $company->ship_to_postal_code;
        $companyorder->ship_to_city = $companyorder->ship_to_city;
        $companyorder->contact_name = $company->contact_name;
        $companyorder->contact_email = $company->contact_email;
        $companyorder->contact_phone = $company->contact_phone;
        $companyorder->cvr = $company->cvr;
        $companyorder->save();
      }
    response::success(make_json("result", $company));
  }

  public function readGiftCertificateCompany() {
    //1. read Company
    $company = Company::find($_POST['company_id']);
    $dummy = array();
    response::success(make_json("result", $company));
  }

  public function searchGiftCertificateCompany() {
    $companies = Company::find('all', array('conditions' => array(
     'deleted = ?  AND
     is_gift_certificate = ? AND (
     phone LIKE CONCAT("%", ? ,"%") OR
     name LIKE CONCAT("%", ? ,"%") OR
     ship_to_company LIKE CONCAT("%", ? ,"%") OR
     cvr LIKE CONCAT("%", ? ,"%") OR
     contact_name LIKE CONCAT("%", ? ,"%") OR
     contact_phone LIKE CONCAT("%", ? ,"%") OR
     contact_email LIKE CONCAT("%", ? ,"%") )'
    , 0,1,$_POST['text'],$_POST['text'],$_POST['text'],$_POST['text'],$_POST['text'],$_POST['text'],$_POST['text']),  'order' => 'pid,name',
    ));
    response::success(make_json("result", $companies));
  }
    public function searchGiftCertificateCompanyBS() {

    $result = Dbsqli::getSql2("select company_id from company_order where order_no = '".$_POST['text']."'");
    if(sizeofgf($result) > 0 ){
        $companies = Company::find('all', array('conditions' => array(
        'deleted = ?  AND
         is_gift_certificate = ? AND id = ?'
        ,0,1,$result[0]["company_id"]),  'order' => 'pid,name', ));
        response::success(make_json("result", $companies));

    } else {
        response::success(make_json("result", $result));
    }




  }
    public function searchGiftCertificateCompanyCSV() {
    $companies = Company::find('all', array('conditions' => array(
     'deleted = ?  AND
     is_gift_certificate = ? AND
     cvr = ?'
    , 0,1,$_POST['text'])));
    response::success(make_json("result", $companies));
  }



  public function getUsers()
  {
    // denne er tung.. brug en anden
     $shopusers = ShopUser::all(array('company_id' => $_POST['company_id'],'is_demo'=>0));
     $options = array('include' => array('orders'));
     response::success(make_json("users",$shopusers,$options));
  }

  public function getUsers2()
  {
    // optimeret version af getUsers.
     ShopUser::$skipCalculatedAttributes = true;
     $shopusers = ShopUser::all(array('company_id' => $_POST['company_id'],'is_demo'=>0));
     $options = array('include' => array('orders'));
     response::success(make_json("users",$shopusers,$options));
  }
  public function getItemNumber(){
        $result = Dbsqli::getSql2("SELECT `model_present_no`  FROM `present_model` WHERE `model_id` = ".$_GET['model_id']." and `language_id` = 1");
        response::success(json_encode($result));
  }


  //   ****** company import  ******




  public function getCompanyImports() {
    if(isset($_POST["norge"])){
            $companyimport = CompanyImport::find('all',array(
         'conditions' => array('imported = ? and deleted = ? and standby = ? and shop_name = ?',0,0,0,"Julegavekortet NO"),
         'limit' => 1,
         'order' => 'id DESC'
      ));

      if(count($companyimport)==0) {
        $companyimport = CompanyImport::find('all',array(
         'conditions' => array('imported = ? and deleted = ? and standby = ? and shop_name = ?',0,0,1,"Julegavekortet NO"),
         'limit' => 1
      ));
      }
    } else {
        $companyimport = CompanyImport::find('all',array(
         'conditions' => array('imported = ? and deleted = ? and standby = ? and shop_name not like ?',0,0,0,"%NO%"),
         'limit' => 1,
         'order' => 'id DESC'
      ));

      if(count($companyimport)==0) {
        $companyimport = CompanyImport::find('all',array(
         'conditions' => array('imported = ? and deleted = ? and standby = ? and shop_name not like  ?',0,0,1,"%NO%"),
         'limit' => 1
      ));
      }
    }




     response::success(make_json("companyorders",$companyimport));
  }



   public function getCompanyImportCount() {
   if(isset($_POST["norge"])){
     $companyimports = CompanyImport::find('all',array(
        'conditions' => array('imported = ? and deleted = ?  and shop_name like ?',0,0,"%NO%")
     ));
     } else {
        $companyimports = CompanyImport::find('all',array(
            'conditions' => array('imported = ? and deleted = ? and shop_name not like ?',0,0,"%NO%")
         ));

     }
  
     $dummy = [];
     $dummy['count'] = countgf($companyimports);
     response::success(json_encode($dummy));

 }


   public function updateCompanyImport() {
    $companyimport = CompanyImport::updateCompanyImport ($_POST);
    response::success(make_json("companyimport", $companyimport));
  }
  public function updataOnhold(){
       $company = Company::find($_POST['company_id']);
       $company->onhold = $_POST['onhold'];
        $company->save();
        $dummy = array();
        response::success(make_json("result", $dummy));


  }

 public function deleteCompanyImport() {
   //s�t til deleted
   $companyimport = CompanyImport::find($_POST['company_import_id']);
   $companyimport->deleted = true;
   $companyimport->save();
   $dummy = array();
   response::success(make_json("result", $dummy));
 }

 public function standbyCompanyImport() {
   //s�t til deleted
   $companyimport = CompanyImport::find($_POST['company_import_id']);
   $companyimport->standby = true;
   $companyimport->save();
   $dummy = array();
   response::success(make_json("result", $dummy));
 }

  //   ****** company orders  ******
  // **** Oprer ordre og tildel gavekort ****
  // formerly known as   addGiftCertificates


   public function cancelCompanyOrder()  {
        $companyorder = CompanyOrder::find_by_order_no($_POST['order_no']);
        if($companyorder->is_cancelled)
        {
           throw new exception('Order er allerede annulleret');
        }
        if($companyorder->is_shipped)
        {
           throw new exception('Ordre kan ikke annulleres da den allerede er sendt!');
        }
        $companyorder->is_cancelled = true;
        $companyorder->save();
        $shopusers = ShopUser::find('all',array(
         'conditions' => array('company_id = ? and username BETWEEN  ? AND ? ',
                     $companyorder->company_id,
                     $companyorder->certificate_no_begin,
                     $companyorder->certificate_no_end )
         )
         );
        foreach($shopusers as $shopuser) {
            $shopuser->blocked = true;
            $shopuser->save();
        }
        $dummy[] = [];
        response::success(json_encode($dummy));
   }

 public function createCompanyOrder() {
     $companyimport = CompanyImport::find($_POST['company_import_id']);


     // tjek om typer er "e" s� underst�ttes den ikke
     // NB. De bliver allerede filtreret fra.
     $is_email = false;
     if($companyimport->shipment_method=="e") {
         $is_email = true;
     }


     if($companyimport->imported==1) {
         throw new exception('Bestilling er allerede godkendt');
     }


     $companyid = $_POST['company_id'];

     // Opret/find company
     if($companyid) {
        $company = Company::find($companyid);
     } else {
       // create new companu from orderimport
       $company = new Company();
       $company->name = $companyimport->name;
       $company->phone = $companyimport->phone;
       $company->cvr = $companyimport->cvr;
       $company->username = preg_replace('/\s+/', '', $company->contact_email);
       $company->password = preg_replace('/\s+/', '', $company->cvr);
       $company->bill_to_address = $companyimport->bill_to_address;
       $company->bill_to_address_2 = $companyimport->bill_to_address_2;
       $company->bill_to_postal_code = $companyimport->bill_to_postal_code;
       $company->bill_to_city = $companyimport->bill_to_city;
       $company->bill_to_country = $companyimport->bill_to_country;

       if($companyimport->ship_to_company == "" || $companyimport->ship_to_company == null){
             $companyimport->ship_to_company = $companyimport->name;
       }

       $company->ship_to_company = $companyimport->ship_to_company;




       if(empty(trimgf($companyimport->ship_to_attention))) {
           $company->ship_to_attention = $companyimport->ship_to_attention;
       }  else {
           $company->ship_to_attention = $companyimport->ship_to_attention;
       }

       if(empty(trimgf($companyimport->ship_to_address))) {
         $company->ship_to_address = $companyimport->bill_to_address;
       }  else {
         $company->ship_to_address = $companyimport->ship_to_address;
       }

       if(empty(trimgf($companyimport->ship_to_address_2))) {
         $company->ship_to_address_2 = $companyimport->bill_to_address_2;
       }  else {
         $company->ship_to_address_2 = $companyimport->ship_to_address_2;
       }

      if(empty(trimgf($companyimport->ship_to_postal_code))) {
         $company->ship_to_postal_code = $companyimport->bill_to_postal_code;
       }  else {
         $company->ship_to_postal_code = $companyimport->ship_to_postal_code;
       }

       if(empty(trimgf($companyimport->ship_to_city))) {
          $company->ship_to_city    = $companyimport->bill_to_city;
          $company->ship_to_address = $companyimport->bill_to_address;
        }  else {
           $company->ship_to_city = $companyimport->ship_to_city;
        }

       if(empty($companyimport->ship_to_country)) {
         $company->ship_to_country = $companyimport->bill_to_country;
       }  else {
         $company->ship_to_country = $companyimport->ship_to_country;
       }
       if(empty($companyimport->ship_to_country)) {
         $company->ship_to_country = $companyimport->bill_to_country;
       }  else {
         $company->ship_to_country = $companyimport->ship_to_country;
       }





       $company->contact_name = $companyimport->contact_name;
       $company->contact_phone = $companyimport->contact_phone;
       $company->contact_email = $companyimport->contact_email;
       $company->active = 1;
       $company->is_gift_certificate = 1;
       $company->save();
     }



     //1. check om company er knyttet til denne butik, hvis s� tilf�j company til den p�g�ldense shop
     $shop = Shop::find($companyimport->shop_id);

     if($company->is_gift_certificate == 0) {
      throw new exception('Der kan ikke tildeles gavekort til valgshops');
    }

    // Tilf�j company til shoppen hvis ikke den allerede er tilf�jet
    try {
         $companyshop = new CompanyShop();
         $companyshop->company_id = $company->id;
         $companyshop->shop_id = $shop->id;
         $companyshop->save();
       }catch(Exception $e) {
    }

    $companyorderid = $this->createCompanyOrderCertificates($shop->id,$company->id,$companyimport->quantity,$companyimport->value,$companyimport->expire_date,$is_email,$companyimport->noter,$companyimport->salesperson );
    $companyorder = CompanyOrder::find($companyorderid);
    $companyorder->giftwrap = $companyimport->giftwrap;
    $companyorder->save();

    $companyimport->imported = true;
    $companyimport->save();

    // 17-04-2017 skal testes af vi f�r nytr note fel med over
    // Tilf�j noter

    $companynotes = new CompanyNotes();
    $companynotes->company_id =  $company->id;
    $companynotes->company_order_id =  $companyorderid;
    $companynotes->note =  $companyimport->noter;
    $companynotes->note_internal =  $companyimport->noter_intern;
    $companynotes->save();

    $this->createReceiptMail($companyorderid);
    $dummy = [];
    response::success(json_encode($dummy));
 }



 // Tilf�jer kort til en eksisterende kunde    (till�gsordre)
 //Denne funktion kaldes fra backenden af.




 public function addCompanyOrder_multi() {


    $spdealtxt      = $_POST['spdealtxt'];
    $salenote       = $_POST['salenote'];
    $shopid         = $_POST['shop_id'];
    $companyid      = $_POST['company_id'];
    $quantity       = $_POST['quantity'];
    $value          = $_POST['value'];
    $expiredate     = $_POST['expire_date'];
    $isemail        = $_POST['is_email'];
    $isdelivery     = $_POST['is_delivery'];
    $earlyOrder     = $_POST['earlyOrder'];
    $salesperson    = $_POST['salesperson'];
    $earlyorderList = $_POST['earlyorderList'];
    $regInNAV       = $_POST["regInNAV"];
    $giftwrap       = $_POST['giftwrap'];
    $giftSpeLev     = $_POST['giftSpeLev'];
    $freeDelivery   = $_POST['freeDelivery'];
  //  $cardFormShipping =  $_POST['cardFormShipping'];
    //Vi skal kunne have med levering til andre datoer ogs�.


    //$expiredate = expireDate::getByExpireDate($expiredate);
    //if($isdelivery==1)  {
    //  $expire_date = '2018-01-01';
    //}
    // med levering indtil 2018-01-01
    //1. check om company er knyttet til denne butik, hvis s� tilf�j company til den p�g�ldense shop


    $shop = Shop::find($shopid);
    $company = Company::find($companyid);
    if($company->is_gift_certificate == 0) {
      throw new exception('Der kan ikke tildeles gavekort til valgshops');
     }
     // Tilf�j company til shoppen hvis ikke den allere er tilf�jet
     try {
         $companyshop = new CompanyShop();
         $companyshop->company_id = $company->id;
         $companyshop->shop_id = $shop->id;
         $companyshop->save();
       }catch(Exception $e) {

     }
     try{
      $company->internal_note = $spdealtxt."\n".$company->internal_note;
      $company->save();
     }catch(Exception $e) {

     }

    $tmp = new companyOrder();
    $tmp->expire_date = $expiredate;
    $companyorderid = $this->createCompanyOrderCertificates($shopid,$companyid,$quantity,$value,$tmp->expire_date,$isemail,$salenote,$salesperson,$spdealtxt,$earlyOrder,$earlyorderList,$regInNAV);
    $companyorder = CompanyOrder::find($companyorderid);
    $companyorder->is_appendix_order = true;
    $companyorder->salesperson = $salesperson;
    $companyorder->spdealtxt =  $spdealtxt;
    $companyorder->salenote =  $salenote;
    $companyorder->earlyorder =  $earlyOrder;



    if(isset($giftwrap)) {
      $companyorder->giftwrap = $giftwrap;
    }
    if(isset($giftSpeLev)) {
      $companyorder->gift_spe_lev = $giftSpeLev;
    }
     if(isset($freeDelivery)) {
      $companyorder->free_delivery = $freeDelivery;
    }


    $companyorder->save();

    //17-04-2017 skal testes af vi f�r nytr note fel med over
    // Tilf�j noter
    $companynotes = new CompanyNotes();
    $companynotes->company_id =  $company->id;
    $companynotes->company_order_id =  $companyorderid;
    if(isset($salesperson)){
      $companynotes->note =           $salesperson;
    }



    $companynotes->save();
    // check i company shipping record
   /*
    $companyShipping = new companyshippingcost();
    $companyShipping->company_id = $company->id;
    $companyShipping->company_order_bsnumber = $companyorderid;
    $companyShipping->cost = 50;
    $companyShipping->save();
    */
    /*
    $cardFormShipping = -1;
    if($cardFormShipping != ""){
      $cardFormShipping = $cardFormShipping;
    }
    */
    //$result = Dbsqli::setSql2("insert into company_shipping_cost (company_id,company_order_id,cost) value (".$company->id.",".$companyorderid.",".$cardFormShipping." )");
     //   response::success(json_encode($result));

  // Fjernet   $this->createReceiptMail($companyorderid);
    $dummy = [];
    response::success(json_encode($dummy));


 }




 public function addCompanyOrder() {
     // appendix order


    $spdealtxt = $_POST['spdealtxt'];
    $salenote = $_POST['salenote'];
    $shopid =     $_POST['shop_id'];
    $companyid =  $_POST['company_id'];
    $quantity =   $_POST['quantity'];
    $value =      $_POST['value'];
    $expiredate = $_POST['expire_date'];
    $isemail =    $_POST['is_email'];
    $isdelivery  =$_POST['is_delivery'];
    $earlyOrder = $_POST['earlyOrder'];
    $salesperson =  $_POST['salesperson'];
    $earlyorderList =  $_POST['earlyorderList'];
    $regInNAV       = $_POST["regInNAV"];


    //Vi skal kunne have med levering til andre datoer ogs�.


    //$expiredate = expireDate::getByExpireDate($expiredate);
    //if($isdelivery==1)  {
    //  $expire_date = '2018-01-01';
    //}
    // med levering indtil 2018-01-01
    //1. check om company er knyttet til denne butik, hvis s� tilf�j company til den p�g�ldense shop


    $shop = Shop::find($shopid);
    $company = Company::find($companyid);
    if($company->is_gift_certificate == 0) {
      throw new exception('Der kan ikke tildeles gavekort til valgshops');
     }
     // Tilf�j company til shoppen hvis ikke den allere er tilf�jet
     try {
         $companyshop = new CompanyShop();
         $companyshop->company_id = $company->id;
         $companyshop->shop_id = $shop->id;
         $companyshop->save();
       }catch(Exception $e) {

     }
     try{
      $company->internal_note = $spdealtxt."\n".$company->internal_note;
      $company->save();
     }catch(Exception $e) {

     }

    $tmp = new companyOrder();
    $tmp->expire_date = $expiredate;
    $companyorderid = $this->createCompanyOrderCertificates($shopid,$companyid,$quantity,$value,$tmp->expire_date,$isemail,$salenote,$salesperson,$spdealtxt,$earlyOrder,$earlyorderList,$regInNAV);
    $companyorder = CompanyOrder::find($companyorderid);
    $companyorder->is_appendix_order = true;
    $companyorder->salesperson = $salesperson;
    $companyorder->spdealtxt =  $spdealtxt;
    $companyorder->salenote =  $salenote;
    $companyorder->earlyorder =  $earlyOrder;



    if(isset($_POST['giftwrap'])) {
      $companyorder->giftwrap = $_POST['giftwrap'];
    }
    if(isset($_POST['giftSpeLev'])) {
      $companyorder->gift_spe_lev = $_POST['giftSpeLev'];
    }
     if(isset($_POST['freeDelivery'])) {
      $companyorder->free_delivery = $_POST['freeDelivery'];
    }
    

    $companyorder->save();

    //17-04-2017 skal testes af vi f�r nytr note fel med over
    // Tilf�j noter
    $companynotes = new CompanyNotes();
    $companynotes->company_id =  $company->id;
    $companynotes->company_order_id =  $companyorderid;
    if(isset($_POST['salesperson']))
      $companynotes->note =           $_POST['salesperson'];

    if(isset($_POST['intern']))
      $companynotes->note_internal =  $_POST['intern'];

    $companynotes->save();
    // check i company shipping record
   /*
    $companyShipping = new companyshippingcost();
    $companyShipping->company_id = $company->id;
    $companyShipping->company_order_bsnumber = $companyorderid;
    $companyShipping->cost = 50;
    $companyShipping->save();
    */
    /*
    $cardFormShipping = -1;
    if($_POST['cardFormShipping'] != ""){
      $cardFormShipping = $_POST['cardFormShipping'];
    }
    */
    //$result = Dbsqli::setSql2("insert into company_shipping_cost (company_id,company_order_id,cost) value (".$company->id.",".$companyorderid.",".$cardFormShipping." )");
     //   response::success(json_encode($result));

  // Fjernet   $this->createReceiptMail($companyorderid);
    $dummy = [];
    response::success(json_encode($dummy));
 }



 //bruges til at teldele gavekort til dr�mmegavekortet....

public function  createDreamCards() {

   die('what?');
// denne funktion skal laves om.... der er meget hardcoded

    //600
     //$companyid =4904;
     //$shopid =   247;

     //800
     $companyid =4986;
     $shopid =   248;

     $quantity  = 1000;
     $weekno    = 0;
     $isemail  = false;

     $shop = Shop::find($shopid);
     $company = Company::find($companyid);
     $giftcertificates = GiftCertificate::findBatchPrint($shop->id,$quantity,$weekno,$shop->reservation_group);
     foreach($giftcertificates as $giftcertificate)  {
       GiftCertificate::addToShop($giftcertificate->id,$shop->id,$company->id,0);
     }
     $dummy = [];
     response::success(json_encode($dummy));

}

 private function createCompanyOrderCertificates($shopid,$companyid,$quantity,$value,$expiredate,$isemail,$salenote="",$salesperson="",$spdealTxt,$earlyOrder,$earlyorderList,$regInNAV) {
    // 2.  2018-01-01 <- med leveringsadresse
    // skal ogs� tilpasses til email gavekort...

    //17.04.2017
   $expiredate = expireDate::getByExpireDate($expiredate);

   if(!$expiredate)  {
     throw new exception('Ugyldig gavekortdato');
     } else {
       if($expiredate->blocked==1)
         throw new exception('Der er sp�rret for denne dato');
     }

    $shop = Shop::find($shopid);
    $company = Company::find($companyid);

   //Find det forn�dnne antal gavekort
   // 13-08-2019 mail h�ndteres et andet sted (us)

   if($isemail) {
        $giftcertificates = GiftCertificate::findBatchEmail($shop->id,$quantity,$expiredate,$shop->reservation_group);
   }   else {
        $giftcertificates = GiftCertificate::findBatchPrint($shop->id,$quantity,$expiredate,$shop->reservation_group);
   }

    //opret company order
    $system = system::first();
    $companyorder = new CompanyOrder();





    $ean = "";
    $spdeal = "";
    $spdealTxt = "";

    $ean = $company->ean;
    $companyorder->order_no = Numberseries::getNextNumber($system->company_order_nos_id);
    $companyorder->company_id            =  $company->id;
    $companyorder->company_name          =  $company->name;
    $companyorder->shop_id               =  $shop->id;
    $companyorder->shop_name             =  $shop->alias;
    $companyorder->quantity              =  $quantity;
    $companyorder->expire_date           =  $expiredate->expire_date;
    $companyorder->certificate_value     =  $value;
    $companyorder->is_email              =  $isemail;
    $companyorder->certificate_no_begin  =  $giftcertificates[0]->certificate_no;
    $companyorder->certificate_no_end    =  $giftcertificates[countgf($giftcertificates)-1]->certificate_no;
    $companyorder->ship_to_company       =  $company->ship_to_company;
    $companyorder->ship_to_address       =  $company->ship_to_address;
    $companyorder->ship_to_address_2     =  $company->ship_to_address_2;
    $companyorder->ship_to_postal_code   =  $company->ship_to_postal_code;
    $companyorder->ship_to_city          =  $company->ship_to_city;
    $companyorder->contact_name          =  $company->contact_name;
    $companyorder->contact_phone         =  $company->contact_phone;
    $companyorder->contact_email         =  $company->contact_email;
    $companyorder->cvr                   =  $company->cvr;
    $companyorder->salesperson           =  $salesperson;
    $companyorder->salenote              =  $salenote;
    $companyorder->ean                   =  $ean;
    $companyorder->spdeal                =  $spdeal;
    $companyorder->spdealtxt             =  $spdealTxt;
    $companyorder->earlyorder            =  $earlyOrder;
    $companyorder->earlyorderlist        = $earlyorderList;

       if($regInNAV == "0"){ // kort skal ikke registeret i NAV
            $companyorder->navsync_status = 100;
        }
        
        if((intval($companyorder->certificate_no_end)-intval($companyorder->certificate_no_begin)+1) != $companyorder->quantity) {
         throw new exception('Hop i kort-interval, kontakt teknisk support.');
     }
     
      $companyorder->save();

    //3. Tildel gavekort
    foreach($giftcertificates as $giftcertificate)  {
      GiftCertificate::addToShop($giftcertificate->id,$shop->id,$company->id,$companyorder->id);
      $expireDate = $giftcertificate->expire_date;
    }


    // Hvis det er email s� skal den sendes med det sammme.
    if($isemail) {
       $companyorder->is_shipped = 1;
       $companyorder->is_printed = 1;
       $companyorder->save();
       $this->createOrderMail($shop->id,$companyorder->id);
    }
     return $companyorder->id;
  }

  public function addGiftWrapOnOrder() {
    $companyorder = CompanyOrder::find($_POST['order_id']);
    $companyorder->giftwrap = 1;
    $companyorder->save();
    $this->createReceiptMail($_POST["order_id"]);
    response::success(make_json("companyorder",$companyorder));
  }

  public function removeGiftWrapOnOrder() {
    $companyorder = CompanyOrder::find($_POST['order_id']);
    $companyorder->giftwrap = 0;
    $companyorder->save();
    $this->createReceiptMail($_POST["order_id"]);
    response::success(make_json("companyorder",$companyorder));
  }

  public function testOrderMail(){

   // $this->createOrderMail($_POST["shop_id"],$_POST["company_id"]);
    $this->createReceiptMail($_POST["id"]);
    $dummy = [];
    response::success(json_encode($dummy));
  }


  // Send kvittering pr. mail.
  public function createReceiptMail($companyorderid){

    $companyorder = CompanyOrder::find($companyorderid);
    $company =   Company::find($companyorder->company_id);
    $shop    =   Shop::find($companyorder->shop_id);


    $expireDate = expireDate::getByExpireDate($companyorder->expire_date);

    // Lav de som ops�tning
    if($companyorder->shop_id==54 || $companyorder->shop_id==55 || $companyorder->shop_id==56) {
        $mailtempate = MailTemplate::getTemplate($companyorder->shop_id,1);
        $shopitemname = "24gaver";
        $shopitemno   = "24GAVER";
        $mailto = 'info@24gaver.dk';
        $title =  'Ordre bekr�ftigelse - 24gaver';
        $title2 = '24GAVER';
        $color = '#009900';
    }else if ($companyorder->shop_id==52) {
        $mailtempate = MailTemplate::getTemplate($companyorder->shop_id,1);
        $shopitemname = "Julegavekortet";
        $shopitemno   = "JGK";
        $mailto = 'info@julegavekortet.dk';
        $title =  'Ordre bekr�ftigelse - Julegavekortet';
        $title2 = 'JULEGAVEKORTET.DK';
        $color = '#cc0052';
    }else if ($companyorder->shop_id==53) {
        $mailtempate = MailTemplate::getTemplate($companyorder->shop_id,1);
        $shopitemname = "GULD";
        $shopitemno   = "GULD";
        $mailto = 'info@guldgavekortet.dk';
        $title =  'Ordre bekr�ftigelse - Guldgavekortet';
        $title2 = 'GULDGAVEKORTET.DK';
        $color = 'black';
    }else  if($companyorder->shop_id==57 || $companyorder->shop_id==58 || $companyorder->shop_id==59 || $companyorder->shop_id==272 || $companyorder->shop_id==574) {
        $mailtempate = MailTemplate::getTemplate($companyorder->shop_id,4);
        $shopitemname = "JGK";
        $shopitemno   = "JGK-NO";
        $mailto = 'info@gavefabrikken.no';
        $title =  'Ordre bekr�ftigelse - Julegavekortet';
        $title2 = 'Julegavekortet.no';
        $color = '#cc0052';

    }else if ($companyorder->shop_id==251) {
        $mailtempate = MailTemplate::getTemplate($companyorder->shop_id,1);
        $shopitemname = "P�skegavekortet";
        $shopitemno   = "PGG";
        $mailto = 'info@paaskegavekortet.dk';
        $title =  'Ordre bekr�ftigelse - P�skegavekortet';
        $title2 = 'P�SKEGAVEKORTET.DK';
        $color = 'black';
    }    else if ($companyorder->shop_id==575) {
        $mailtempate = MailTemplate::getTemplate($companyorder->shop_id,1);
        $shopitemname = "Designjulegaven ";
        $shopitemno   = "DESIGN";

        $mailto = 'info@julegavetypen.dk';
        $title =  'Ordre bekr�ftigelse - Julegavetypen';
        $title2 = 'JULEGAVETYPEN.DK';
        $color = 'black';
    }   else  if($companyorder->shop_id==247 || $companyorder->shop_id==248 || $companyorder->shop_id==287 || $companyorder->shop_id==290 || $companyorder->shop_id==310) {
        $mailtempate = MailTemplate::getTemplate($companyorder->shop_id,1);
        $shopitemname = "DGK";
        $shopitemno   = "DGK";
        $mailto = 'info@drommegavekortet.dk';
        $title =  'Ordre bekr�ftigelse - Dr�mmegavekortet';
        $title2 = 'Dr�mmegavekortet.dk';
        $color = '#60aaa9';

    } else  if($companyorder->shop_id==2960 || $companyorder->shop_id==2961 || $companyorder->shop_id==2962 || $companyorder->shop_id==2963 ) {
        $mailtempate = MailTemplate::getTemplate($companyorder->shop_id,1);
        $shopitemname = "LUKS";
        $shopitemno   = "LUKS";
        $mailto = 'info@luksusgavekortet.dk';
        $title =  'Ordre bekr�ftigelse - luksusgavekortet';
        $title2 = 'luksusgavekortet.dk';
        $color = '#60aaa9';

    }

    else  if(in_array($companyorder->shop_id,array(8355, 8356, 8357, 8358, 8359, 8360, 8361, 8362, 8363, 8364, 8365, 8366)) ) {
        $mailtempate = MailTemplate::getTemplate($companyorder->shop_id,4);
        $shopitemname = "LUKSNO";
        $shopitemno   = "LUKSNO";
        $mailto = 'info@gavefabrikken.no';
        $title =  'Ordre bekr�ftigelse - Luksuskortet';
        $title2 = 'Julegavekortet.no';
        $color = '#cc0052';

    }



    $html  = $mailtempate->template_order_confirmation;

    // Our Information
    $html = str_replace('{COMPANY}',utf8_decode($companyorder->company_name),$html);
    $html = str_replace('{MAIL_TO}',$mailto,$html);
    $html = str_replace('{TITLE}',$title,$html);
    $html = str_replace('{TITLE2}',$title2,$html);
    $html = str_replace('{COLOR}',$color,$html);


     include("model/receiptCardShop.class.php");
    $itemNumber =  receiptCardShop::getItemNumber($expireDate->toString(),$shopitemno,$companyorder->certificate_value);
    $productName = receiptCardShop::getProductName($expireDate->toString(),$shopitemname,$shopitemno,$companyorder->certificate_value);

    $html = str_replace('{ITEM_NO}',$itemNumber,$html);
    $html = str_replace('{ITEM_NAME}',$productName,$html);

    //2017-04-17
    //{SHOP_ITEM_NAME}
    //{SHOP_ITEM_NO}
    //{WEEK_NO}
    //{VALUE}
    $itemname =  $expireDate->item_name_format;
    $itemname = str_replace('{SHOP_ITEM_NAME}',$shopitemname,$itemname);
    $itemname = str_replace('{SHOP_ITEM_NO}',$shopitemno,$itemname);
    $itemname = str_replace('{WEEK_NO}',$expireDate->week_no,$itemname);
    $itemname = str_replace('{VALUE}',$companyorder->certificate_value,$itemname);

    $itemno =  $expireDate->item_no_format;
    $itemno = str_replace('{SHOP_ITEM_NAME}',$shopitemname,$itemno);
    $itemno = str_replace('{SHOP_ITEM_NO}',$shopitemno,$itemno);
    $itemno = str_replace('{WEEK_NO}',$expireDate->week_no,$itemno);
    $itemno = str_replace('{VALUE}',$companyorder->certificate_value,$itemno);

    $html = str_replace('{CVR}',utf8_decode($companyorder->cvr),$html);
    $html = str_replace('{ITEM_NAME}',$itemname,$html);
    $html = str_replace('{ITEM_NO}',$itemno,$html);

    // Company Information

    if($companyorder->shop_id==57 || $companyorder->shop_id==58 || $companyorder->shop_id==59 || $companyorder->shop_id==272) {
        if($companyorder->ship_to_company == "" || $companyorder->ship_to_company == null){
            $html = str_replace('{COMPANY_LEV_NAME}',utf8_decode($companyorder->company_name),$html);
        } else {
            $html = str_replace('{COMPANY_LEV_NAME}',utf8_decode($companyorder->ship_to_company),$html);
        }

    } else {
        $html = str_replace('{COMPANY_LEV_NAME}',utf8_decode($companyorder->company_name),$html);
    }
    $html = str_replace('{COMPANY_NAME}',utf8_decode($companyorder->company_name),$html);






    $html = str_replace('{CONTACT_NAME}',utf8_decode($companyorder->contact_name),$html);
    $html = str_replace('{CONTACT_EMAIL}',utf8_decode($companyorder->contact_email),$html);
    $html = str_replace('{CONTACT_PHONE}',utf8_decode($companyorder->contact_phone),$html);
    $html = str_replace('{CVR}',utf8_decode($companyorder->cvr),$html);

    // Order Information
    $html = str_replace('{QUANTITY}',utf8_decode($companyorder->quantity),$html);
    $html = str_replace('{VALUE}',utf8_decode($companyorder->certificate_value),$html);
    $html = str_replace('{EXPIRE_DATE}',$expireDate->display_date,$html);

    $shipmentdate ='';
    if($shop->shipment_date)
      $shipmentdate = $shop->shipment_date->format('d-m-Y');

    $html = str_replace('{SHIPMENT_DATE}',$shipmentdate,$html); //17-04-2017
    $html = str_replace('{ORDER_DATE}',date('d-m-Y') ,$html);
    $Total =  $companyorder->certificate_value * $companyorder->quantity;
    if($companyorder->giftwrap==1) {
      $Total = $Total +  ($companyorder->quantity * 25);
    }

    $totalInclVAT = $Total * 1.25;
    $VATAmount = $totalInclVAT* 0.2;
    $Total = number_format($Total,2,",",".");
    $totalInclVAT = number_format($totalInclVAT,2,",",".");
    $VATAmount = number_format($VATAmount,2,",",".");

    $html = str_replace('{TOTAL_AMOUNT}',$Total,$html);
    $html = str_replace('{TOTAL_AMOUNT_VAT}',$totalInclVAT,$html);
    $html = str_replace('{VAT_AMOUNT}',$VATAmount,$html);

    if($companyorder->giftwrap==1) {
      $html = str_replace('{GIFT_WRAP}','25',$html);
    }   else {
      $html = str_replace('{GIFT_WRAP}','0',$html);
    }

    //Billing information
    $html = str_replace('{BILL_TO_ADDRESS}',utf8_decode($company->bill_to_address),$html);
    $html = str_replace('{BILL_TO_ADDRESS_2}',utf8_decode($company->bill_to_address_2),$html);
    $html = str_replace('{BILL_TO_POSTAL_CODE}',utf8_decode($company->bill_to_postal_code.' '.$company->bill_to_city),$html);
   // $html = str_replace('{BILL_TO_CITY}',utf8_decode($company->bill_to_city),$html);
    $html = str_replace('{BILL_TO_COUNTRY}',utf8_decode($company->bill_to_country),$html);

    //Shipping information
    $html = str_replace('{SHIP_TO_ADDRESS}',utf8_decode($companyorder->ship_to_address),$html);
    $html = str_replace('{SHIP_TO_ADDRESS_2}',utf8_decode($companyorder->ship_to_address_2),$html);
    $html = str_replace('{SHIP_TO_POSTAL_CODE}',utf8_decode($companyorder->ship_to_postal_code.' '.$companyorder->ship_to_city),$html);

    //if($companyorder->ean == ""){
    //  $html = str_replace('{ean_show}','style="display:none;"',$html);
    //} else {
    //  $html = str_replace('{ean_show}','',$html);
    //  $html = str_replace('{EAN}',$companyorder->ean,$html);
    //}
    $html = str_replace('{EAN}',$companyorder->ean,$html);

    $htmlExtra = "";
    $htmlExtra.="<hr /><b>S�lger:</b><br />".$companyorder->salesperson;
    $htmlExtra.="<br /><b>Noter:</b><div style=\"width:200px;\">".nl2br(utf8_decode($companyorder->salenote))."</div></body>";
    if($companyorder->spdeal == "spdeal"){
        $companyorder->spdeal = "ja";
    } else {
        $companyorder->spdeal = "nej";
    }

    $htmlExtra.="<b>Special aftale: </b>".$companyorder->spdeal;
    $htmlExtra.="<br><b>Special aftale text: </b>".$companyorder->spdealtxt;
//    $html = str_replace('</body>',$htmlExtra,$html);
//    $html = str_replace('{SHIP_TO_CITY}',utf8_decode($companyorder->ship_to_city),$html);
    $maildata = [];
    $maildata['sender_email'] =  $mailtempate->sender_order_confirmation;
    $maildata['recipent_email'] = $shop->receipt_recipent;
    $maildata['subject']= $mailtempate->subject_order_confirmation;
    $maildata['body'] = $html.$htmlExtra;

    // Set mailserver
    $shop = Shop::find($companyorder->shop_id );
    $maildata['mailserver_id'] = $shop->mailserver_id;
    $maildata['mailserver_id'] = 1;    // s� l�nge janni modtager dem
    $maildata['company_order_id'] = $companyorder->id;  // 27-04-2017

    MailQueue::createMailQueue($maildata);
  }

public function resendReceiptMail() {

       $mail = MailQueue::find('all',array('conditions' =>array('company_order_id' => $_POST['id'])) );
       // nulstil den s� den sendes igen
       if(count($mail) > 0 ) {
         $mail = MailQueue::find($mail[0]->id);
         unset($mail->id);  // nulstil id, s� den opretter en kopi.
         $mail->sent = 0;
         $mail->error = 0;
         $mail->save();
       } else {
           throw new exception('Kvittering blev ikke fundet');
       }
       $result = [];
       response::success(json_encode($result));
   }



  // Send email med gavekortnumre
  public  function createOrderMail($shopid,$companyorderid){

        //  Set lang by shop
        $language = 1;
        $noShops = array(272,57,58,59,574);
        if(in_array($shopid,$noShops)) $language = 4;

       $shop = Shop::find($shopid);
       $companyorder = CompanyOrder::find($companyorderid);
       $company =   Company::find($companyorder->company_id);
       $mailtempate = MailTemplate::getTemplate($companyorder->shop_id,$language); // kun p� dansk
       $html  = $mailtempate->template_company_order;
       $expiredate = expireDate::getByExpireDate($companyorder->expire_date);
       // Lav de som ops�tning





       $recipient = "us@gavefabrikken.dk";
       $value = "";

     if($companyorder->shop_id==575 ) {
        $replyto = 'info@gavefabrikken.dk';
        $subject =  'Gavekoder til designjulegaven';
    }else if($companyorder->shop_id==574 ) {
        $replyto = 'info@gavefabrikken.no';
        $subject =  'Gavekoder til Gullgavekortet';
        $recipient = "ordrebekreftelse@gavefabrikken.no";
    }else if($companyorder->shop_id==54 || $companyorder->shop_id==55 || $companyorder->shop_id==56) {
        if($companyorder->shop_id==54) $value = " - 400";
        else if($companyorder->shop_id==55) $value = " - 560";
        else if($companyorder->shop_id==56) $value = " - 640";
        $replyto = 'info@24gaver.dk';
        $subject =  'Gavekoder til 24gaver'.$value;
    }else if ($companyorder->shop_id==52) {
        $replyto = 'info@julegavekortet.dk';
        $subject =  'Gavekoder til Julegavekortet';
    }else if ($companyorder->shop_id==53) {
        $replyto = 'info@guldgavekortet.dk';
        $subject =  'Gavekoder til Guldgavekortet';
    }else  if($companyorder->shop_id==57 || $companyorder->shop_id==58 || $companyorder->shop_id==59 || $companyorder->shop_id==272) {
        
        if($companyorder->shop_id==57) $value = " - 400";
        else if($companyorder->shop_id==58) $value = " - 600";
        else if($companyorder->shop_id==59) $value = " - 800";
        else if($companyorder->shop_id==272) $value = " - 300";
        
        $mailtempate = MailTemplate::getTemplate($companyorder->shop_id,4);
        $html  = $mailtempate->template_company_order;
        $replyto = 'info@julegavekortet.no';
        $subject =  'Gavekoder til Julegavekortet - '.$value;
        $recipient = "ordrebekreftelse@gavefabrikken.no";
    }else if ($companyorder->shop_id==251) {
        $replyto = 'info@paaskegavekortet.dk';
        $subject =  'Gavekoder til P�skegavekortet';
    }else if ($companyorder->shop_id==265) {
        $replyto = 'info@julegavekortet.dk';
        $subject =  'Gavekoder til Julegavetypen';
    } else  if($companyorder->shop_id==287 || $companyorder->shop_id==290 || $companyorder->shop_id==310) {
    
        if($companyorder->shop_id==290) $value = " - 200";
        else if($companyorder->shop_id==310) $value = " - 300";
      
        $replyto = 'info@drommegavekortet.dk';
        $subject =  'Gavekoder til Dr�mmegavekortet'.$value;

    } 
    else if ($companyorder->shop_id==1832 || $companyorder->shop_id==1981 || $companyorder->shop_id==4793 || $companyorder->shop_id==5117) {
         $replyto = 'info@julegavekortet.dk';
         $subject =  'Gavekoder til 24 Julklappar';
     }
    else if ($companyorder->shop_id==8271) {
        $replyto = 'info@julegavekortet.dk';
        $subject =  'Gavekoder til Sommarpresentkortet';
    }
     else {
        throw new exception('unsuppored shop type');
    }

       // lav gavekort liste
       $table = "<table>";
       $shopusers = ShopUser::find('all',array(
         'conditions' => array('company_id = ? and username BETWEEN  ? AND ? ',
            $companyorder->company_id,
            $companyorder->certificate_no_begin,
            $companyorder->certificate_no_end )
         )
        );
        if($companyorder->shop_id==57 || $companyorder->shop_id==58 || $companyorder->shop_id==59 || $companyorder->shop_id==272) {
            $table .= "<tr><td style='width:200px'>Kortnummer</td><td style='width:200px'>".($language == 4 ? "Adgangskode" : "Kodeord")."</td></tr>";
        } else {
            $table .= "<tr><td style='width:200px'>Brugernavn</td><td style='width:200px'>".($language == 4 ? "Adgangskode" : "Kodeord")."</td></tr>";
        }


        foreach($shopusers as $shopuser) {
          $table .= "<tr>";
          $table .= "<td>$shopuser->username</td>";
          $table .= "<td>$shopuser->password</td>";
          $table .= "</tr>";
        }

       $table .= "</table>";
       $html = str_replace('{COMPANY}',utf8_decode($companyorder->company_name),$html);
       $html = str_replace('{CONTACT}',utf8_decode($companyorder->contact_name),$html);
       $html = str_replace('{EMAIL}',utf8_decode($companyorder->contact_email),$html);
       $html = str_replace('{REPLYTO}',$replyto,$html);
       $html = str_replace('{SALESPERSON}',utf8_decode($companyorder->salesperson),$html);
       $html = str_replace('{GIFTCERTIFICATES}',$table,$html);
       $html = str_replace('{LINK}',$shop->receipt_link,$html);

       if($language == 1){
            if($expiredate->display_date == "31-12-2018"){
               $html = str_replace('{GAVE}',"tildeles automatisk en eksklusiv h�jtaler",$html);
            } else {
               $html = str_replace('{GAVE}',"gave�ske med vin og chokolade",$html);
            }
       }



 //      if($shop->is_norwegian==1) {
 //        $html = str_replace('{LINK}',$shop->link,$html);
   //    } else {
     //    $html = str_replace('{LINK}',$shop->receipt_link,$html);
       //}

       $html = str_replace('{DEADLINE}',$expiredate->display_date,$html);

       $maildata = [];
       $maildata['sender_email'] =  $mailtempate->sender_company_order;
       $maildata['recipent_email'] = $recipient; //$shop->receipt_recipent;
       $maildata['subject']= utf8_encode($subject);
       $maildata['body'] = $html;
       $shop = Shop::find($shopid);
       $maildata['mailserver_id'] = $shop->mailserver_id;
       if($maildata['mailserver_id'] != 5) $maildata['mailserver_id'] = 1;
       MailQueue::createMailQueue($maildata);
   }

 public function getCompanyOrders() {
     $companyorders = CompanyOrder::find('all',array(
     'conditions' => array('is_printed = ? or is_shipped = ?',0,0),
     'order' => 'certificate_no_begin asc'
     ));
     response::success(make_json("companyorders",$companyorders));
 }

 // dem som er sendt   ( forkert navn)
  public function getCompanyOrdersAll() {
     $companyorders = CompanyOrder::find('all',array(
     'conditions' => array('is_printed = 1 or is_shipped = 1',0,0),
     'order' => 'certificate_no_begin asc'
     ));
     response::success(make_json("companyorders",$companyorders));
 }

  public function getCompanyOrdersForCompany() {
     $companyorders = CompanyOrder::find('all',array(
     'conditions' => array("company_id = ? and is_cancelled = ?",$_POST['company_id'],0),
     'order' => 'certificate_no_begin asc'
     ));
     response::success(make_json("companyorders",$companyorders));
 }

 public function searchCompanyOrders() {
     $companyorders = CompanyOrder::find('all',array(
     'conditions' => array('cvr = ? AND is_cancelled = ?',$_POST['cvr'],0)
     ));
     response::success(make_json("companyorders",$companyorders));
 }

 public function getCompanyOrderCount() {
     $companyorders = CompanyOrder::find('all',array(
     'conditions' => array('is_printed = ? or is_shipped = ?',0,0)
     ));
     $dummy = [];
     $dummy['count'] = countgf($companyorders);

     response::success(json_encode($dummy));
 }

 public function getCompanyOrdersShipped() {
     $companyorders = CompanyOrder::find('all',array(
     'conditions' => array('is_printed = ? and is_shipped = ?',1,1)
     ));
     response::success(make_json("companyorders",$companyorders));
 }

 public function getCompanyImportLast50Approved() {
     $companyorders = CompanyOrder::find('all',array(
     'order' => 'id desc',
     'limit' => 50
     ));
     response::success(make_json("companyorders",$companyorders));
 }

 public function setCompanyOrderPrinted() {
    $ids = explode(';', $_POST['id_list']);
    foreach($ids as $id) {
      $companyorder = CompanyOrder::find($id);
      $companyorder->is_printed = $_POST['is_printed'];
      $companyorder->save();
    }
    response::success(make_json("companyorders",$companyorder));
 }

 public function setCompanyOrderShipped() {
    $ids = explode(';', $_POST['id_list']);
    foreach($ids as $id) {
      $companyorder = CompanyOrder::find($id);
      $companyorder->is_shipped = $_POST['is_shipped'];
      $companyorder->save();
    }
    response::success(make_json("companyorders",$companyorder));
 }
}
?>

