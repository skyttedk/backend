<?php
// Controller CleanUp
// Date created  Mon, 23 May 2017 20:36:22 +0200
// Created by Bitworks
class cleanupController Extends baseController {

    public function Index() {
    }


    //ovref�r brugere til n�ste �r i en ny tabel


    //Called by Cronjog.Org
    public function deleteRecords() {
           ini_set("session.gc_maxlifetime",8640);
        ini_set('memory_limit','1850M');

       //echo 'asd';
        $cleanupEntries  = Cleanup::find('all',array('conditions' => array('deleted'=>0), 'limit'=>100 ));

        foreach($cleanupEntries as $cleanupEntry)  {
            if($cleanupEntry->object_type== 'present')
            {
               //udkommenter n�r real
               //$cleanupEntry->deleted = 1;
               //$cleanupEntry->save();

            } elseif($cleanupEntry->object_type== 'shop')   {

                //udkommenter n�r real
               $shop = Shop::find($cleanupEntry->object_id);
               $shop->delete(true);
               $cleanupEntry->deleted = 1;
               $cleanupEntry->save();

           }   elseif($cleanupEntry->object_type== 'company')   {

                //udkommenter n�r real
                $company = Company::find($cleanupEntry->object_id);
               $company->delete(true);
               $cleanupEntry->deleted = 1;
               $cleanupEntry->save();

           }
         }
        $dummy = array();
        $dummy['records_deleted'] = countgf($cleanupEntries);
        response::success(json_encode($dummy));
    }

    //Called by Cronjog.Org
    //[BACKENDURL]]/index.php?rt=cleanup/runcleanUpScript
    public function runcleanUpScript() {

      //ExecuteSQL("DELETE  FROM clean_up where id > 0");


      $result = [];

      //1. empty tables
      //$this->emptyTables($result);    //   Executed 10-06-2017

      //2. Script 1. Valgshops
      //$this->cleanUpValgShops($result);    Executed 07-06-2017
                                                          +
      //3. Script 1. archive users
      //$this->backupDeliveryUsers($result);   Executed 11-06-2017

      //3. Script 1. Presents
      //$this->cleanUpPresents($result);


      //4. Script 1. Companies
      $this->cleanUpCompaniesFull($result);    //Executed 14-06-2017


      //Done
      response::success(json_encode($result));

    }

    public function emptyTables(&$result) {
      $this->emptyTable($result,'app_log');

      $this->emptyTable($result,'gift_certificate');
      $this->emptyTable($result,'mail_queue');
      $this->emptyTable($result,'company_import');
      $this->emptyTable($result,'company_order');
//      $this->emptyTable($result,'shop_model');              //bruge ikke
 //     $this->emptyTable($result,'order_address');           //bruge ikke
      $this->emptyTable($result,'order_present_entry');
      $this->emptyTable($result,'system_log');
      $this->emptyTable($result,'system_surveillance');
      $this->emptyTable($result,'vendor');
    }

    public function emptyTable(&$result,$tablename) {
      ExecuteSQL("DELETE  FROM $tablename");
      $result[$tablename] ='Table Emptied';
    }

    public function cleanUpPresents(&$result) {
      //Check om der er nogte af de aktive shops med leveringsbrugere, som har en present som er deaktiveret.
      //dette er et problem. Disse presents skal aktiveres.
      //24gaver, og guldgavekortet

      $result['presents_script_0'] ='CleaInUp Script Executed';
      $presents  = Present::find('all',array('conditions' => array('deleted'=>1) ));
      $i=0;
      foreach($presents as $present)
      {
          //$present = present::deletePresent($present->id,true);
          $cleanup = new CleanUp();
          $cleanup->object_type ='present';
          $cleanup->object_id   =  $present->id;
          $cleanup->object_name =  $present->name;
          $cleanup->save();
          $i++;
      }

      $result['presents_script_1'] =$i.' presents deleted';

      $presents  = Present::find('all',array('conditions' => array('deleted'=>0) ));
      $result['presents_script_2'] =count($presents).' presents preserved';

    }

    public function cleanUpCompaniesFull(&$result) {
//      $companies  = Company::find('all',array('conditions' => array('is_gift_certificate'=>1,'id'=>5567) ));
      $companies  = Company::find('all',array('conditions' => array('is_gift_certificate'=>1)));
      foreach($companies as $company) {
                $cleanup = new CleanUp();
                $cleanup->object_type =  'company';
                $cleanup->object_id   =  $company->id;
                $cleanup->object_name =  $company->name;
                $cleanup->save();
        }
      $result['company_script'] ='CleaInUp Script Executed';
    }



    public function test() {
       $dummy = array();

        response::success(json_encode($dummy));
        $shopusersarchives  = ShopUserArchive::find('all');
        foreach($shopusersarchives as $shopusersarchive) {
              $shopusers =   ShopUser::find('all',array('conditions' => array('username'=>$shopusersarchive->username)));
              if(count($shopusers)==1) {
                //  echo   ($shopusersarchive->username);
                  $shopusersarchive->delivery_printed =  $shopusers[0]->delivery_printed;
                  $shopusersarchive->Save();
                }

        }


        }
    //Kopier brugere med levering over i ny tabel som skal bruges til login
    public function backupDeliveryUsers() {

      $shopusers  = ShopUser::find('all',array('conditions' => array('is_delivery'=>1) ));
        foreach($shopusers as $shopuser) {
            $shopuserarchive = new ShopUserArchive ();
            $shopuserarchive->shop_id    = $shopuser->shop_id;
            $shopuserarchive->company_id = $shopuser->company_id;
            $shopuserarchive->username  = $shopuser->username;
            $shopuserarchive->password  = $shopuser->password;
            $shopuserarchive->expire_date = $shopuser->expire_date;
            $shopuserarchive->blocked = $shopuser->blocked;
            $shopuserarchive->delivery_printed =  $shopuser->delivery_printed;
            $shopuserarchive->save();
        }
    }

    public function cleanUpValgShops(&$result) {
      $shops  = Shop::find('all',array('conditions' => array('is_gift_certificate'=>0) ));
      foreach($shops as $shop)
      {
          //$present = present::deletePresent($present->id,true);
          $cleanup = new CleanUp();
          $cleanup->object_type ='shop';
          $cleanup->object_id   =  $shop->id;
          $cleanup->object_name =  $shop->name;
          $cleanup->save();
      }
      $result['shop_script'] ='CleaInUp Script Executed';
    }

 }
?>

