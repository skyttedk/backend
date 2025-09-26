<?php
// Model CompanyOrder
// Date created  Mon, 16 Jan 2017 15:26:56 +0100
// Created by Bitworks
//***************************************************************
//   (PRI) id                            int(11)             NO
//   (UNI) order_no                      varchar(100)        YES
//   (   ) company_id                    int(11)             NO
//   (   ) company_name                  varchar(100)        YES
//   (   ) shop_id                       int(11)             NO
//   (   ) shop_name                     varchar(100)        YES
//   (   ) salesperson                   text                YES
//   (   ) quantity                      int(11)             NO
//   (   ) expire_date                   date                YES
//   (   ) is_email                      tinyint(4)          YES
//   (   ) certificate_no_begin          varchar(250)        YES
//   (   ) certificate_no_end            varchar(250)        YES
//   (   ) certificate_value             int(11)             YES
//   (   ) is_printed                    tinyint(4)          YES
//   (   ) is_shipped                    tinyint(4)          YES
//   (   ) is_invoiced                   tinyint(4)          YES
//   (   ) ship_to_address               varchar(100)        YES
//   (   ) ship_to_address_2             varchar(100)        YES
//   (   ) ship_to_postal_code           varchar(10)         YES
//   (   ) ship_to_city                  varchar(100)        YES
//   (   ) contact_name                  varchar(100)        YES
//   (   ) contact_email                 varchar(100)        YES
//   (   ) contact_phone                 varchar(20)         YES
//   (   ) is_cancelled                  tinyint(4)          YES
//   (   ) cvr                           varchar(15)         YES
//   (   ) is_appendix_order             tinyint(4)          YES
//   (   ) freight_calculated            tinyint(4)          YES
//***************************************************************
class CompanyOrder extends ActiveRecord\Model {
    static $table_name  = "company_order";
    static $primary_key = "id";

    static $before_save =  array('onBeforeSave');
    static $after_save =  array('onAfterSave');

    static $before_create =  array('onBeforeCreate');
    static $after_create =  array('onAfterCreate');

    static $before_update =  array('onBeforeUpdate');
    static $after_update =  array('onAfterUpdate');

    static $before_destroy =  array('onBeforeDestroy');  // virker ikke
    static $after_destroy =  array('onAfterDestroy');


    //Relations
    static $has_many = array(
        array('company_order_items', 'class_name' => 'CompanyOrderItem')
    );


    // Trigger functions
    function onBeforeSave() {

        if(in_array($this->shop_id,array(1832,1981,4793,5117,8271))) {

            $this->ship_to_postal_code = str_replace(" ","",trimgf($this->ship_to_postal_code));
            if(strlen($this->ship_to_postal_code) == 5) {
                $this->ship_to_postal_code = substr($this->ship_to_postal_code,0,3)." ".substr($this->ship_to_postal_code,3);
            }
/*
            $this->cardto_postal_code = str_replace(" ","",trimgf($this->cardto_postal_code));
            if(strlen($this->cardto_postal_code) == 5) {
                $this->cardto_postal_code = substr($this->cardto_postal_code,0,3)." ".substr($this->cardto_postal_code,3);
            }
*/
        }




    }
    function onAfterSave()  {}

    function onBeforeCreate() {
       $this->created_datetime = date('d-m-Y H:i:s');
       $this->modified_datetime = date('d-m-Y H:i:s');

        // Set floating expire date
        if($this->shop_id > 0) {
            $cardshopSettings = \CardshopSettings::find("first",array("conditions" => array("shop_id" => $this->shop_id)));
            if($cardshopSettings != null && $cardshopSettings->floating_expire_months != null && intval($cardshopSettings->floating_expire_months) > 0) {

                $floatingDate = new DateTime('@' . $this->created_datetime->getTimestamp());

                // Add months
                $interval = new DateInterval('P'.intval($cardshopSettings->floating_expire_months).'M');
                $floatingDate->add($interval);

                // Round to next day
                $floatingDate->setTime(0, 0, 0);
                $floatingDate->add(new DateInterval('P1D'));

                $this->floating_expire_date = date('d-m-Y H:i:s',$floatingDate->getTimestamp());

            }
        }

       $this->validateFields();
    }
    function onAfterCreate()  {}

    function onBeforeUpdate() {
      $this->modified_datetime = date('d-m-Y H:i:s');
      $this->validateFields();
    }

    function onAfterUpdate()  {}
    function onBeforeDestroy() {}
    function onAfterDestroy()  {

    }
    function validateFields() {
      testRequired($this,'company_id');
      testRequired($this,'shop_id');
      testRequired($this,'quantity');
      testRequired($this,'ship_to_address');
      testRequired($this,'ship_to_postal_code');
      testRequired($this,'ship_to_city');

       testMaxLength($this,'certificate_no_begin',250);
       testMaxLength($this,'certificate_no_end',250);

       $this->certificate_no_begin = trimgf($this->certificate_no_begin);
       $this->certificate_no_end = trimgf($this->certificate_no_end);

    }

    public static function getOrderStateList() {

        $stateList = array(
            0 => "Created",
            1 => "Ready",
            2 => "Approve",
            3 => "Approved",
            4 => "Synced",
            5 => "Sent",
            6 => "Failed",
            7 => "Cancel",
            8 => "Cancelled",
            9 => "To complete",
            10 => "Completed",
            11 => "Archived",
            20 => "Manual"
        );

        return $stateList;

    }

    public static function stateTextList($num=null)
    {

        $stateList = self::getOrderStateList();

        if($num == null) return $stateList;
        else if(isset($stateList[$num])) return $stateList[$num];
        else return "Unknown";
    }

    public function getStateText()
    {
        return self::stateTextList($this->order_state);
    }

    /*
     * * COMPANY STATES
     * - 0: Oprettet, ikke synkroniseret
     * - 1: Klar til sync
     * - 2: Afventer godkendelse
     * - 3: Godkendt
     * - 4: Synkroniseret
     * - 5: Kort afsendt (fysisk eller e-mail)
     * - 6: Synkronisering fejlet
     * - 7: Skal annulleres
     * - 8: Annulleret
     * - 9: Klar til afslutning
     * - 10: Afsluttet (fragt, momskørsel og slutfakturering er lavet)
     * - 11: Arkiveret
     */

//---------------------------------------------------------------------------------------
// Static CRUD Methods
//---------------------------------------------------------------------------------------

     static public function archiveCompanyOrder($id) {
        $companyorder = CompanyOrder::find($id);
        $companyorderhistory = new CompanyOrderHistory();

        $version = PresentModel::find_by_sql("SELECT MAX(version_no) AS maxversion FROM company_order_history WHERE order_no='".$companyorder->order_no."'");
        $version = $version[0]->maxversion +1;

        $companyorderhistory = new CompanyOrderHistory();

        $companyorderhistory->order_no = $companyorder->order_no;
        $companyorderhistory->version_no = $version;
        $companyorderhistory->company_id = $companyorder->company_id;
        $companyorderhistory->company_name = $companyorder->company_name;
        $companyorderhistory->shop_id = $companyorder->shop_id;
        $companyorderhistory->shop_name = $companyorder->shop_name;
        $companyorderhistory->salesperson = $companyorder->salesperson;
        $companyorderhistory->salenote = $companyorder->salenote;
        $companyorderhistory->quantity = $companyorder->quantity;
        $companyorderhistory->expire_date = $companyorder->expire_date;
        $companyorderhistory->is_email = $companyorder->is_email;
        $companyorderhistory->certificate_no_begin = $companyorder->certificate_no_begin;
        $companyorderhistory->certificate_no_end = $companyorder->certificate_no_end;
        $companyorderhistory->certificate_value = $companyorder->certificate_value;
        $companyorderhistory->is_printed =  $companyorder->is_printed;
        $companyorderhistory->is_shipped =  $companyorder->is_shipped;
        $companyorderhistory->is_invoiced = $companyorder->is_invoiced;
        $companyorderhistory->ship_to_company = $companyorder->ship_to_company;
        $companyorderhistory->ship_to_address = $companyorder->ship_to_address;
        $companyorderhistory->ship_to_address_2 = $companyorder->ship_to_address_2;
        $companyorderhistory->ship_to_postal_code = $companyorder->ship_to_postal_code;
        $companyorderhistory->ship_to_city = $companyorder->ship_to_city;
        $companyorderhistory->contact_name = $companyorder->contact_name;
        $companyorderhistory->contact_email = $companyorder->contact_email;
        $companyorderhistory->contact_phone = $companyorder->contact_phone;
        $companyorderhistory->spdeal = $companyorder->spdeal;
        $companyorderhistory->spdealtxt =  $companyorder->spdealtxt;
        $companyorderhistory->is_cancelled = $companyorder->is_cancelled;
        $companyorderhistory->cvr = $companyorder->cvr;
        $companyorderhistory->ean = $companyorder->ean;
        $companyorderhistory->is_appendix_order = $companyorder->is_appendix_order;
        $companyorderhistory->freight_calculated = $companyorder->freight_calculated;
        $companyorderhistory->created_datetime = date('d-m-Y H:i:s');
        //echo "|".$companyorderhistory->cvr."/".$companyorder->cvr."|";
        $companyorderhistory->save();



     }

    static public function createCompanyOrder($data) {
        $companyorder = new CompanyOrder($data);
        $companyorder->save();
        return($companyorder);
    }

    static public function readCompanyOrder($id) {
        $companyorder = CompanyOrder::find($id);
        return($companyorder);
    }

    static public function updateCompanyOrder($data) {
        $companyorder = CompanyOrder::find($data['id']);
        $companyorder->update_attributes($data);
        $companyorder->save();
        return($companyorder);
    }

    static public function deleteCompanyOrder($id,$realDelete=true) {

        if($realDelete) {
            $companyorder = CompanyOrder::find($id);
    		$companyorder->delete();
          } else {  //Soft delete
            $companyorder->deleted = 1;
            $companyorder->save();
          }
    }


}
?>