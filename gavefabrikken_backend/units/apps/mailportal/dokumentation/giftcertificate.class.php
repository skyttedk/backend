<?php
// Model GiftCertificate
// Date created  Mon, 16 Jan 2017 15:27:00 +0100
// Created by Bitworks
//***************************************************************
//   (PRI) id                            int(11)             NO
//   (UNI) certificate_no                varchar(250)        NO
//   (UNI) password                      varchar(250)        NO
//   (   ) value                         decimal(10,0)       NO
//   (   ) created_date                  date                YES
//   (   ) expire_date                   date                NO
//   (MUL) shop_id                       tinyint(4)          YES
//   (   ) company_id                    int(11)             YES
//   (   ) blocked                       tinyint(4)          YES
//   (   ) week_no                       int(11)             YES
//   (MUL) no_series                     int(11)             YES
//   (   ) is_printed                    tinyint(11)         YES
//   (   ) is_emailed                    tinyint(11)         YES
//   (   ) is_delivery                   tinyint(11)         YES
//   (MUL) reservation_group             int(11)             YES
//***************************************************************
class GiftCertificate extends ActiveRecord\Model {
	static $table_name  = "gift_certificate";
	static $primary_key = "id";


	static $before_save =  array('onBeforeSave');
	static $after_save =  array('onAfterSave');

	static $before_create =  array('onBeforeCreate');
	static $after_create =  array('onAfterCreate');

	static $before_update =  array('onBeforeUpdate');
	static $after_update =  array('onAfterUpdate');

	static $before_destroy =  array('onBeforeDestroy');  // virker ikke
	static $after_destroy =  array('onAfterDestroy');

	// Trigger functions
	function onBeforeSave() {}
    function onAfterSave()  {}

	function onBeforeCreate() {

        $this->created_date = date('d-m-Y');
	    $this->validateFields();
	}
	function onAfterCreate()  {}

	function onBeforeUpdate() {

	    $this->validateFields();

	}

	function onAfterUpdate()  {}
    function onBeforeDestroy() {}
	function onAfterDestroy()  {

	}
    function validateFields() {
      	testRequired($this,'certificate_no');
		testRequired($this,'password');
		testRequired($this,'expire_date');

		testMaxLength($this,'certificate_no',250);
		testMaxLength($this,'password',250);

        $this->certificate_no = trimgf($this->certificate_no);
		$this->password = trimgf($this->password);

    }


//---------------------------------------------------------------------------------------
// Static CRUD Methods
//---------------------------------------------------------------------------------------

	static public function createGiftCertificate($data) {
		$giftcertificate = new GiftCertificate($data);
        $giftcertificate->save();
        return($giftcertificate);
	}

	static public function readGiftCertificate($id) {
		$giftcertificate = GiftCertificate::find($id);
        return($giftcertificate);
	}

	static public function updateGiftCertificate($data) {
		$giftcertificate = GiftCertificate::find($data['id']);
		$giftcertificate->update_attributes($data);
        $giftcertificate->save();
        return($giftcertificate);
	}

	static public function deleteGiftCertificate($id,$realDelete=true) {

	    if($realDelete) {
            $giftcertificate = GiftCertificate::find($id);
    		$giftcertificate->delete();
          } else {  //Soft delete
            $giftcertificate->deleted = 1;
		    $giftcertificate->save();
          }
    }

//---------------------------------------------------------------------------------------
// Custom Methods
//---------------------------------------------------------------------------------------

public static function removeFromShop($giftCertificateId) {
      throw new exception ('Call removeShopUser instead');
        //sker nu via remove shopuser
    }

public static function addToShop($giftCertificateId,$shopId,$companyId,$companyorderid,$cardValues=null) {

        $giftcertificate = GiftCertificate::find($giftCertificateId);
        $company = Company::find($companyId);
       	$shop = Shop::find($shopId);


        if(!$shop->is_gift_certificate)
          throw new exception('Der kan kun tildeles gavekort til gavekortshops. Shoppen '.$shop->name.' er ikke en gavekortshop' );

        if($giftcertificate->reservation_group!==0)
          if($shop->reservation_group !== $giftcertificate->reservation_group)
            throw new exception('Reservations grupper stemmer ikke');

        if(!($giftcertificate->company_id==0 && $giftcertificate->shop_id==0))
          throw new exception('Gavekortnr. '.$giftcertificate->certificate_no.' er allerede tildelt en anden virksomhed');

       	ShopUser::validateShopAttributes($shop->id);

    	$shopUser= new ShopUser();
        $shopUser->shop_id = $shop->id;
        $shopUser->company_id = $company->id;
        $shopUser->username = $giftcertificate->certificate_no;
        $shopUser->password = $giftcertificate->password;

        //Midlertidig hotfix til at h�ndtere uge 51.
        $shopUser->expire_date =$giftcertificate->expire_date;

        $shopUser->is_delivery = $giftcertificate->is_delivery;
        $shopUser->is_giftcertificate =1;

        $shopUser->company_order_id =   $companyorderid;  //17-04-2017
    
        if($cardValues != null) {
            $shopUser->card_values = $cardValues;
        }

        $shopUser->save(false);
        foreach($shop->attributes_ as $shopAttribute) {
    		$userAttribute = new UserAttribute();
            $userAttribute->shop_id = $shop->id;
            $userAttribute->company_id = $company->id;
            $userAttribute->shopuser_id = $shopUser->id;
            $userAttribute->attribute_id = $shopAttribute->id;
            $userAttribute->attribute_value = '';
            $userAttribute->is_username = $shopAttribute->is_username;
            $userAttribute->is_password = $shopAttribute->is_password;
            $userAttribute->is_email = $shopAttribute->is_email;
            $userAttribute->is_name = $shopAttribute->is_name;
            if($userAttribute->is_username ==1)
              $userAttribute->attribute_value = $shopUser->username;

            if($userAttribute->is_password ==1)
              $userAttribute->attribute_value = $shopUser->password;
           $userAttribute->save();
        }
        $giftcertificate->shop_id = $shop->id;
        $giftcertificate->company_id = $company->id;
        $giftcertificate->save();
  }



  // Version som bruger ugenr. stedet for date
   public static function findBatchPrint($shopId,$quantity,$expdate,$reservation_group) {
      //  find x antal gave kort udfra reservationgruppe p� shop og ugenr
      //  Er stat ugenr,m med expire date..



      $date = $expdate->expire_date->format('Y-m-d') ;
      //lockTable(GiftCertificate::$table_name);
      $shop = Shop::find($shopId);
      $giftcertificates = GiftCertificate::all(array('conditions' =>
                     array("reservation_group" => $shop->reservation_group, "expire_date" =>  $date , "company_id" => 0, "shop_id" => 0,'reservation_group' => $reservation_group,'is_printed' => 1, 'is_emailed' => 0, 'blocked' => 0),
                    'limit' => $quantity,
                    'order' => 'certificate_no'));

      /*
      if($reservation_group == 5) {
          $lastCard = intval($giftcertificates[count($giftcertificates)-1]->certificate_no);
          mailgf("sc@interactive.dk","DROM FYSISK ANTAL","TOOK ".$quantity." CARDS FROM DROM - ".$date." - last card was ".$lastCard." - ".(22353853-$lastCard)." left to 22353853");
      }
      */

      if(count($giftcertificates)!=$quantity) {
          mailgf("sc@interactive.dk","PRINTCARDS-ERROR","Der mangler fysiske gavekort til ".$shop->reservation_group.' - '.$date);
          throw new exception('Der er ikke tilstr&#xE6;kkelig med fysiske gavekort til '.$shop->reservation_group.' - '.$date);
      }

     return($giftcertificates);

  }

  // Version som bruger ugenr. stedet for date
   public static function findBatchEmail($shopId,$quantity,$expdate,$reservation_group) {


      //find x antal gave kort udfra reservationgruppe p� shop og ugenr
      $date = $expdate->expire_date->format('Y-m-d') ;
     // lockTable(GiftCertificate::$table_name);
      $shop = Shop::find($shopId);
      $giftcertificates = GiftCertificate::all(array('conditions' =>
                     array( "expire_date" => $date , "company_id" => 0, "shop_id" => 0,'reservation_group' => 0 ,'is_printed' => 0, 'is_emailed' => 1, 'blocked' => 0),
                    'limit' => $quantity,
                    'order' => 'certificate_no'));
      
      if(count($giftcertificates)!=$quantity) {
          mailgf("sc@interactive.dk","MAILCARD-ERROR","Der mangler e-mail gavekort til ".$shop->reservation_group.' - '.$date);
          throw new exception('Der er ikke tilstr&#xE6;kkelig med e-mail gavekort til '.$date);
      }


     return($giftcertificates);

  }


  //blive ikke brugt herfra... men fra admin/giftcertiticatestats.php
  static public function getStats() {
      throw new exception ('Call admin/giftcertiticatestats.php instead');
      // denne funktion bruges vist ikke
    $sql  = "
        SELECT
          `gift_certificate`.`reservation_group`
        , `gift_certificate`.`expire_date`
        , `reservation_group`.`name`
        , `gift_certificate`.`shop_id`
    FROM
        `gift_certificate`
    INNER JOIN `reservation_group`
        ON (`gift_certificate`.`reservation_group` = `reservation_group`.`id`)
    WHERE (`gift_certificate`.`is_printed` =1
    AND `gift_certificate`.`shop_id` <>0)
        GROUP BY `gift_certificate`.`reservation_group`, `gift_certificate`.`expire_date`";
   $stats = [];
   $certificategroups = GiftCertificate::find_by_sql($sql);
   foreach($certificategroups as $certificategroup) {
        $dt= $certificategroup->expire_date->format('Y-m-d');
        $stat = [];
        $stat['shop']        =  $certificategroup->name;
        $stat['expire_date'] =  $certificategroup->expire_date;
        $sql2 = "select count(*) as quantity from gift_certificate where                  reservation_group = $certificategroup->reservation_group and expire_date = '$dt' and is_printed = 1";
        $sql3 = "select count(*) as quantity from gift_certificate where shop_id = $certificategroup->shop_id and reservation_group = $certificategroup->reservation_group and expire_date = '$dt' and is_printed = 1";
        $sql2 = "select count(*) as quantity from gift_certificate where                  reservation_group = $certificategroup->reservation_group and expire_date = '$dt'";
        $sql3 = "select count(*) as quantity from gift_certificate where shop_id = $certificategroup->shop_id and reservation_group = $certificategroup->reservation_group and expire_date = '$dt'";

        $sql4 = "select * from gift_certificate where shop_id = $certificategroup->shop_id and reservation_group = $certificategroup->reservation_group and expire_date = '$dt' AND is_printed = 1 order by certificate_no desc LIMIT 1";
        $sql5 = "SELECT SUM(quantity) as quantity FROM company_order WHERE is_invoiced = 1 AND shop_id = $certificategroup->shop_id AND expire_date = '$dt' AND is_cancelled = 0";

        $certificategroupTotal = GiftCertificate::find_by_sql($sql2);
        $certificategroupTotalIssued = GiftCertificate::find_by_sql($sql3);
        $certificategroupLastIssued = GiftCertificate::find_by_sql($sql4);
        $certificatesInvoiced = CompanyOrder::find_by_sql($sql5);

        $stat['total']  =  $certificategroupTotal[0]->quantity;
        $stat['total_issued']  =  $certificategroupTotalIssued[0]->quantity;
        $stat['remaining']  =  $stat['total']-$stat['total_issued'];
        $stat['last_issued']  =  $certificategroupLastIssued[0]->certificate_no;
        $stat['invoiced']  =  $certificatesInvoiced[0]->quantity;
        $stats[] = $stat;
   }
   return($stats);
  }
}
?>