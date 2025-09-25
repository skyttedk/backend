<?php
// Controller present
// Date created  Tue, 05 Apr 2016 20:16:03 +0200
// Created by Bitworks
//   dump(Present::table()->last_sql);
class PresentController Extends baseController {

  public function Index() {
  }




  public function updateCategory()
  {
      $Present = Present::find($_POST['shopID']);
      $Present->shop_present_category_id = $_POST['categoryID'];
      $Present->save();
      response::success(json_encode($Present));
  }



  public function paperData(){
      //$presentModels = Presentmodel::all( array(
        //  'conditions' => array(' present_id = ? and language_id = ? ',$presentId,1)));
      $shopID = $_POST["shopID"];
      $languageID = $_POST["languageID"];
      $presentlist = Present::find_by_sql("SELECT
present.id,  
present.logo,
present.alias,
present_model.model_name,
present_model.model_no,
present_model.fullalias

FROM `present` 
inner join present_model on present_model.present_id = present.id

WHERE 
present.`shop_id` = 3512 and
present.active = 1 and
present.deleted = 0 and
present_model.language_id = 1

order by fullalias");

      $medialist = Media::find_by_sql("SELECT 
present.id,
present_media.media_path,
present_media.index

FROM `present` 
inner join present_media on present_media.present_id = present.id

WHERE 
present.`shop_id` = 3512 and
present.active = 1 and
present.deleted = 0 order by present.id, present_media.index");
      $returnData = [
          "presentlist" => $presentlist,
          "medialist" => $medialist
      ];
      response::success(json_encode($returnData));
  }




  public function updateSampakList(){

    $present_model_sampak =  Dbsqli::getSql2("select * from present_model_sampak where model_id=".$_POST["id"]) ;

    if(sizeofgf($present_model_sampak) == 0){
        Dbsqli::setSql2( "INSERT INTO present_model_sampak (model_id,item_list) VALUES (".$_POST["id"].",'".$_POST["list"]."')");
    } else {
        Dbsqli::setSql2( "update present_model_sampak set item_list = '".$_POST["list"]."' where model_id = ".$_POST["id"]);
    }

    Dbsqli::setSql2( "update present_model set sampak_items = '".$_POST["list"]."' where model_id = ".$_POST["id"]." and language_id = 1");
    /*
    $PresentModel = PresentModel::find($_POST["id"]);
    $PresentModel->sampak_items = $_POST["list"];
    $PresentModel->save();
    response::success(json_encode($PresentModel));
    */
  }

  public function updatePresentation() {

        $Present = Present::find($_POST['id']);
        $Present->pt_img = $_POST['pt_img'];
        $Present->pt_img_small = $_POST['pt_imgSmall'];
        $Present->pt_layout = $_POST['pt_layout'];
        $Present->pt_price = json_encode($_POST['pt_price']);
        $Present->pt_price_no = json_encode($_POST['pt_price_no']);
      $Present->pt_price_se = json_encode($_POST['pt_price_se']);
       // $Present->pt_saleperson = $_POST['pt_saleperson'];
        $Present->save();
        response::success(json_encode($Present));

  }

    public function getPresentsModel(){
           $PresentModel = Present::find_by_sql("select * from present_model where language_id = 1 and  present_id = ".$_GET['id']);
           response::success(json_encode($PresentModel));
    }
    public function getPimSyncDato(){
        $res = Present::find_by_sql("select DATE_FORMAT(pim_sync_time, '%Y-%m-%d %H:%i:%s') as formatted_pim_sync_time ,pim_id from present where id  = ".$_GET['id']);
        response::success(json_encode($res));
    }

    public function getStrength(){
        $modelID = $_POST["modelID"];
        $strength = PresentModel::all(array('conditions' => 'model_id = '.$modelID ));
        response::success(json_encode($strength));
    }
    public function updateStrength(){
        $modelID = $_POST["modelID"];
        $sps = $_POST['sps'];
        $sql = "update present_model set strength = ".$sps." where model_id = ".$modelID;
        $rs = Dbsqli::setSql2($sql);
        $dummy = [];
        response::success(json_encode($dummy));
    }

    public function testTemplateCompatible(){
        $shopID = $_POST["shopID"];
        $present4 =   Present::find_by_sql("select * from present where pt_layout = 4 and  `deleted` =  0 and active = 1 and  shop_id = ".$shopID);
        $hasLayout4 = sizeof($present4) > 0 ? 1:0;
        $presentOther =   Present::find_by_sql("select * from present where pt_layout != 5 and  `deleted` =  0 and active = 1 and  shop_id = ".$shopID);
        $hasLayoutOther = sizeof($presentOther) > 0 ? 1:0;
        $test = 0;

        if($hasLayout4 == 1) $test = 1;
        if($hasLayoutOther == 1) $test = 2;
        if($hasLayout4 == 1 && $hasLayoutOther == 1 ) $test = 3;
        $return = ["test"=>$test];
        response::success(json_encode($return));

      }
      public function doUpdateToTemplate5(){
          $shopID = $_POST["shopID"];
          \Dbsqli::setSql2("UPDATE `present` SET `pt_layout` = '5' WHERE shop_id=".$shopID);
          $dummy = [];
          response::success(json_encode($dummy));
      }

  //  ------------- v2 functioner  --------------------------- //

      public function updateInStock(){
         $present =  Present::find($_POST["present_id"]);
         $state = $_POST["in_stock"] == 1 ? 1:0;
         $present->in_stock = $state;
          $present->save();
          $dummy = [];
          response::success(json_encode($dummy));


      }



      public function kunhos(){
        $presentModel = Present::find($_POST['id']);
        $presentModel->kunhos = $_POST['state'];
        $presentModel->save();
         $dummy = [];
         response::success(json_encode($dummy));

      }
      public function omtanke(){
        $presentModel = Present::find($_POST['id']);
        $presentModel->omtanke = $_POST['state'];
        $presentModel->save();
         $dummy = [];
         response::success(json_encode($dummy));

      }


      public function ptSmallImgDelete(){
        $presentModel = Present::find($_POST['id']);
        $presentModel->pt_img_small = "";
        $presentModel->save();
         $dummy = [];
         response::success(json_encode($dummy));

      }
      public function showBrands(){
          $presents = Present::find('all',array('group'=>'id', 'joins' => array('descriptions','models'),'conditions' => array(
              " shop_id = 4739 and present_description.language_id = 1 and present_model.language_id = 1 "
          ),'order' => 'id desc'));
      //      print_R($presents);



          $this->registry->template->presents = $presents;
          $this->registry->template->localisation = router::getSystemUserLocalisation();
          $this->registry->template->copy = "";
          echo $this->registry->template->show('valgshop-gaveliste-brand_view');

  }


      public function searchPresentsV2() {
          $sysuser =   router::$systemUser;
          $stockMin = $_POST["stockMin"];
          $localisation =  $sysuser->attributes["language"];
          if($localisation == 5){
              $localisation = 1;
          }

      $sql = "";
      $pieces = explode(" ", $_POST['search']);
      $pudgetSql = "";
       if($_POST['budget'] != "none"){
          $pudgetSql = " and price_group = ".$_POST['budget'];
           if($localisation == 4){
               $pudgetSql = " and price_group_no = ".$_POST['budget'];
           }
       }
  // costprice

          $priceStart = $_POST["cost_start"];
          $priceEnd = $_POST["cost_end"];
          $sqlPrice = "";
          if($priceStart != "" || $priceEnd != "") {
              $sqlPrice = "and  (present.price >= ".$priceStart." && present.price <= ".$priceEnd.") ";
              if($localisation == 4){
                 $sqlPrice = "and  (present.price_no >= ".$priceStart." && present.price_no <= ".$priceEnd.") ";
              }

          }
          $sqlCountrySearch = "";

          if (isset($_POST["countrySearch"])) {
              switch ($_POST["countrySearch"]) {
                  case "searchNO":
                      $sqlCountrySearch = " and model_present_no LIKE 'n%'";
                      break;

                  case "searchDK":
                      $sqlCountrySearch = " and model_present_no NOT LIKE 'n%'";
                      break;

                  // Optionally, handle default case
                  default:
                      // Handle unexpected value
                      break;
              }
          }

          // test if sysuser is saleperson
          $sysuser =   router::$systemUser;
          $show_to_saleperson = false;


          $sysuserPremission = UserTabPermission::all(array('conditions' => 'systemuser_id = '.$sysuser->attributes["id"].' and tap_id = 1000' ));
          if(sizeof($sysuserPremission) > 0){
               $show_to_saleperson = true;

          }


      foreach($pieces as $part){
          if($localisation == 1) {
              $sql .= " and
          (
             nav_name like '%$part%' or
             present_no like '%$part%' or
             internal_name like '%$part%' or
             vendor like '%$part%' or
             present_description.caption like '%$part%' or
             model_present_no like '%$part%' or
             model_name like '%$part%'or
             model_no like '%$part%'
           ) ";
          }
          if($localisation == 4){
              $sql .= " and
          (
             nav_name_no like '%$part%' or
             present_no like '%$part%' or
             internal_name like '%$part%' or
             vendor like '%$part%' or
             present_description.caption like '%$part%' or
             model_present_no like '%$part%' or
             model_name like '%$part%'or
             model_no like '%$part%'
           ) ";
          }
     }
          $stockSql = "present.id in ( SELECT distinct present_id FROM `present_model` WHERE present_model.is_deleted = 0 and present_model.present_id > 0 and `model_present_no` in(SELECT itemno FROM `magento_stock_total` WHERE `available` >= ".$stockMin.") and present_model.language_id = 1) and";
          $stockSql =  $stockMin == "" ? "" : $stockSql;
      if($show_to_saleperson == true){

          if($localisation == 1){

              $presents = Present::find('all',array('group'=>'id', 'joins' => array('descriptions','models'),'conditions' => array(
                  $stockSql.    " show_to_saleperson = 1 and deleted = 0 and shop_id = 0 and copy_of = 0 and present_description.language_id = 1 and present_model.language_id = 1 ".$sql." ".$pudgetSql." ".$sqlPrice.$sqlCountrySearch
              ),'offset' => $_POST['offset'] , 'limit' => $_POST['limit'],'order' => 'id desc'));
          }
          if($localisation == 4){

              $presents = Present::find('all',array('group'=>'id', 'joins' => array('descriptions','models'),'conditions' => array(
                  $stockSql.    " show_to_saleperson_no = 1 and deleted = 0 and shop_id = 0 and copy_of = 0 and present_description.language_id = 4 and present_model.language_id = 4 ".$sql." ".$pudgetSql." ".$sqlPrice.$sqlCountrySearch
              ),'offset' => $_POST['offset'] , 'limit' => $_POST['limit'],'order' => 'id desc'));
          }




      } else {

          if($localisation == 1){
            $presents = Present::find('all',array('group'=>'id', 'joins' => array('descriptions','models'),'conditions' => array(
                $stockSql.   " deleted = 0 and shop_id = 0 and copy_of = 0 and present_description.language_id = 1 and present_model.language_id = 1 ".$sql." ".$pudgetSql." ".$sqlPrice.$sqlCountrySearch
            ),'offset' => $_POST['offset'] , 'limit' => $_POST['limit'],'order' => 'id desc'));
          }
          if($localisation == 4){

              $presents = Present::find('all',array('group'=>'id', 'joins' => array('descriptions','models'),'conditions' => array(
                  $stockSql.    " deleted = 0 and shop_id = 0 and copy_of = 0 and present_description.language_id = 4 and present_model.language_id = 4 ".$sql." ".$pudgetSql." ".$sqlPrice.$sqlCountrySearch
              ),'offset' => $_POST['offset'] , 'limit' => $_POST['limit'],'order' => 'id desc'));
          }

      }

  //
      $this->registry->template->presents = $presents;
      $this->registry->template->localisation = router::getSystemUserLocalisation();
      $this->registry->template->copy = "";
      if(isset($_POST["mode"])){
          $this->registry->template->copy = "copy";
      }
      if(sizeof($presents) == 0 and $_POST['limit'] < 30){
          echo "Ingen gaver matchede søgningen";
      }

      echo $this->registry->template->show('valgshop-gaveliste_view');

    }

        public function searchVariantsV2() {

          $sql = "";
      $pieces = explode(" ", $_POST['search']);
      foreach($pieces as $part){
          $sql.= " and
          (
             nav_name like '%$part%' or
             present_no like '%$part%' or
             internal_name like '%$part%' or
             vendor like '%$part%' or
             present_description.caption like '%$part%' or
             model_present_no like '%$part%' or
             model_name like '%$part%' or
             model_no like '%$part%'
           )";
     }

      $presents = Present::find('all',array('group'=>'id', 'joins' => array('descriptions','models'),'conditions' => array(
          "deleted = 0 and shop_id = 0 and present_description.language_id = 1 and present_model.language_id = 1 ".$sql
             ),'offset' => $_POST['offset'] , 'limit' => $_POST['limit'],'order' => 'id desc'));

          $this->registry->template->presents = $presents;
          $this->registry->template->copy = "nocopy";
          if(isset($_POST["copy"])){
              $this->registry->template->copy = "copy";
          }
          echo $this->registry->template->show('valgshop-gaveliste_view');
      }

     public function readAllV3(){
        print_R($_POST);



     }

     public function readAllV2(){



        $shopid = 0;
        $show_to_saleperson = false;
        if(isset($_POST['shop_id'])){
            $shopid = $_POST['shop_id'];
        }



        $sysuser =   router::$systemUser;
     //   print_R($sysuser);
        $localisation =  $sysuser->attributes["language"];
        $userID =  $sysuser->attributes["id"];
        if($localisation == 5){
            $localisation = 1;
        }
         $sqlCountrySearch = "";

         if (isset($_POST["countrySearch"])) {
             switch ($_POST["countrySearch"]) {
                 case "searchNO":
                     $sqlCountrySearch = " and model_present_no LIKE 'n%'";
                     break;

                 case "searchDK":
                     $sqlCountrySearch = " and model_present_no NOT LIKE 'n%'";
                     break;

                 // Optionally, handle default case
                 default:
                     // Handle unexpected value
                     break;
             }
         }


        // test if sysuser is saleperson
        $sysuserPremission = UserTabPermission::all(array('conditions' => 'systemuser_id = '.$sysuser->attributes["id"].' and tap_id = 1000' ));
        if(sizeof($sysuserPremission) > 0){
            $show_to_saleperson = true;
        }
  // budget
         $pudgetSql = "";
         if($_POST['budget'] != "none"){
             $pudgetSql = " and price_group = ".$_POST['budget'];
             if($localisation == 4){
                 $pudgetSql = " and price_group_no = ".$_POST['budget'];
             }
         }
  // costprice

         $priceStart = $_POST["cost_start"];
         $priceEnd = $_POST["cost_end"];
         $stockMin = $_POST["stockMin"];
         $sqlPrice = "";
          if($priceStart != "" || $priceEnd != "") {
              $sqlPrice = "and  (present.price >= ".$priceStart." && present.price <= ".$priceEnd.") ";
              if($localisation == 4){
                  $sqlPrice = "and  (price_no >= ".$priceStart." && price_no <= ".$priceEnd.") ";
              }

          }

         //show_to_saleperson
         $showToSaleperson = " show_to_saleperson = 1 ";
         if($localisation == 4){
             $showToSaleperson = " show_to_saleperson_no = 1 ";
            // $_POST['limit'] = 200;
         }
         $stockSql = "present.id in ( SELECT distinct present_id FROM `present_model` WHERE present_model.is_deleted = 0 and present_model.present_id > 0 and `model_present_no` in(SELECT itemno FROM `magento_stock_total` WHERE `available` >= ".$stockMin.") and present_model.language_id = 1) and";
         $stockSql =  $stockMin == "" ? "" : $stockSql;

         if($show_to_saleperson == true){
             $conditions = array();
             if($localisation == 4 ){
                 $conditions = array('show_to_saleperson_no = ? and deleted = ?  AND (shop_id = ? OR shop_id = ?)',1,0,0,$shopid);
             } else {
                 $conditions = array('show_to_saleperson = ? and deleted = ?  AND (shop_id = ? OR shop_id = ?)',1,0,0,$shopid);
             }

             $presents = Present::find('all',array('group'=>'id', 'joins' => array('descriptions','models'),'conditions' => array(
                 $stockSql.  " deleted = 0 and shop_id = 0 and present_description.language_id = ".$localisation." and present_model.language_id = ".$localisation." and ".$showToSaleperson.$pudgetSql." ".$sqlPrice.$sqlCountrySearch." "
             ),'offset' => $_POST['offset'] , 'limit' => $_POST['limit'],'order' => 'id desc'));
             /*
             $presents = present::all( array(
                 'conditions' => $conditions,'offset' => $_POST['offset'] , 'limit' => $_POST['limit'],'order' => 'id desc'
             ));
             */
       } else {
             if($userID == 861111){

                 $presents = Present::find('all',array('group'=>'id', 'joins' => array('descriptions','models'),'conditions' => array(
                     $stockSql. " deleted = 0 and shop_id = 0 and present_description.language_id = ".$localisation." and present_model.language_id = ".$localisation."  ".$pudgetSql." ".$sqlPrice.$sqlCountrySearch." "
                 ),'offset' => $_POST['offset'] , 'limit' => 1,'order' => 'id desc'));

                 print_R($presents);
                 die("asfdasdf");


             }



           $presents = Present::find('all',array('group'=>'id', 'joins' => array('descriptions','models'),'conditions' => array(
               $stockSql. " deleted = 0 and shop_id = 0 and present_description.language_id = ".$localisation." and present_model.language_id = ".$localisation."  ".$pudgetSql." ".$sqlPrice.$sqlCountrySearch." "
           ),'offset' => $_POST['offset'] , 'limit' => $_POST['limit'],'order' => 'id desc'));

           /*
           $presents = present::all( array(
               'conditions' => array('deleted = ?  AND (shop_id = ? OR shop_id = ?)',0,0,$shopid),'offset' => $_POST['offset'] , 'limit' => $_POST['limit'],'order' => 'id desc'
           ));
           */
       }
        /*
         if(router::$systemUser->attributes["id"] == 86){
             $presents = Present::find('all',array('group'=>'id', 'joins' => array('descriptions','models'),'conditions' => array($stockSql.
                 " deleted = 0 and shop_id = 0 and present_description.language_id = ".$localisation." and present_model.language_id = ".$localisation." and ".$showToSaleperson.$pudgetSql." ".$sqlPrice." "
             ),'offset' => $_POST['offset'] , 'limit' => $_POST['limit'],'order' => 'id desc'));
             foreach($presents as $present){
                 print_R($present->models[0]->attributes["model_present_no"]);
             }
           }
        */


         //  response::success(json_encode($presents));
    $this->registry->template->presents = $presents;
    $this->registry->template->localisation = router::getSystemUserLocalisation();
    $this->registry->template->copy = "";
    if(isset($_POST["mode"])){
        $this->registry->template->copy = "copy";
    }
    echo $this->registry->template->show('valgshop-gaveliste_view');
         if(sizeof($presents) == 0 and $_POST['limit'] < 30){
             echo "Ingen gaver matchede søgningen";
         }
  }
    public function getModelsV3(){
        $sysuser =   router::$systemUser;
        $localisation = $sysuser->attributes["language"];

        $presentId =   $_POST['present_id'];

        $shopId = $_POST["shop_id"];

        $presentModels = Presentmodel::all( array(
            'conditions' => array(' present_id = ? and language_id = ? ',$presentId,$localisation)));
        $present = Present::find($presentId);
        $presentModels[0]->attributes["hide_for_demo_user"] = $present->attributes["hide_for_demo_user"];
        $presentModels[0]->attributes["lock_for_sync"] = $present->attributes["lock_for_sync"];
        $presentModels[0]->attributes["show_if_home_delivery"] = $present->attributes["show_if_home_delivery"];
        response::success(json_encode($presentModels));

    }
  public function getModelsV2(){
      $sysuser =   router::$systemUser;
      $localisation = 1; // $sysuser->attributes["language"];

    $presentId =   $_POST['present_id'];

    $shopId = $_POST["shop_id"];
      $allCategories = ShopPresentCategory::find_all_by_shop_id_and_active($shopId,1);

    $presentModels = Presentmodel::all( array(
            'conditions' => array(' present_id = ? and language_id = ? ',$presentId,$localisation)));
    $present = Present::find($presentId);
    $presentModels[0]->attributes["hide_for_demo_user"] = $present->attributes["hide_for_demo_user"];
    $presentModels[0]->attributes["show_master"] = $present->attributes["show_master"];
      $presentModels[0]->attributes["lock_for_sync"] = $present->attributes["lock_for_sync"];
    $presentModels[0]->attributes["show_if_home_delivery"] = $present->attributes["show_if_home_delivery"];
    $presentModels[0]->attributes["shop_present_category_id"] = $present->attributes["shop_present_category_id"];
    $presentModels[0]->attributes["shop_present_category_list"] = $allCategories;


    response::success(json_encode($presentModels));

  }
    public function createUnikPresent_v3(){
        $present = present::addPresentToShop($_POST["present_id"],$_POST["shop_id"]);
        $options = array('include' => array('descriptions', 'present_media'));
        response::success(make_json("present", $present, $options));
    }

  public function createUnikPresent_v2(){
        $present = present::addPresentToShop($_POST["present_id"],$_POST["shop_id"]);
        $options = array('include' => array('descriptions', 'present_media'));
        response::success(make_json("present", $present, $options));
  }
  public function addChildToPresentState1(){

        $present = present::addPresentToShop($_POST["present_id"],$_POST["shop_id"],$_POST["parentPresent_id"]);
        $options = array('include' => array('descriptions', 'present_media'));

        if($present){
            response::success(make_json("present", $present, $options));
        } else {
          response::error("Pressent not created");
        }
    }
    public function addChildToPresentState2(){
        $presentId = $_POST['present_id'];
        $shopId = $_POST['shop_id'];
        \Dbsqli::setSql2("update present set shop_id= ".($shopId*-1)." where shop_id = ".$shopId." and id =".$presentId);
        response::success(json_encode([]));
    }


  public function removePresent_v2(){
      $presentId = $_POST['presentId'];
      $shopId = $_POST['shop_id'];

          $ShopPresents = Order::all(array(
              'conditions' => array('present_id' => $presentId, 'shop_id' => $shopId)));
          if(sizeof($ShopPresents) > 0){
              $errorMsg = "Der er foretaget valg på gaven";
              response::error($errorMsg);
          } else {
              $newShopID = $shopId*-1;
              $res =  $ShopPresent = Present::find($presentId);
              \Dbsqli::setSql2("update present set shop_id= ".$newShopID.", active = 0, deleted = 1 where shop_id = ".$shopId." and id =".$presentId);
              \Dbsqli::setSql2("update shop_present set shop_id= ".$newShopID. ",active = 0, is_deleted = 0 where shop_id = ".$shopId." and present_id=".$presentId);


          }
          $dummy = [];
          response::success(json_encode($res));


/*
          $presentId = $_POST['presentId'];

//        $ShopPresent = ShopPresent::all(array('conditions' => array('present_id = ? AND shop_id  = ?',$_POST['presentId'],$_POST['shop_id'])));
          //   $ShopPresent = ShopPresent::find_present_id($_POST['presentId']);

          $ShopPresents = ShopPresent::all(array(
              'conditions' => array('present_id' => $presentId, 'shop_id' => $shopId)));
          $ShopPresent = ShopPresent::find($ShopPresents[0]->attributes["id"]);
          $ShopPresent->is_deleted = 1;
          $ShopPresent->active = 0;
          $ShopPresent->save();
          $dummy = [];

          response::success(json_encode($dummy));

      }
*/
  }
  public function getDeletedPresents_v2(){
        $shopId = $_POST['shop_id'];
        $shopId = -1*$shopId;
        $Presents = Present::find_by_sql("SELECT present.id, present.hide_for_demo_user, present.nav_name,present.active,present.deleted,shop_present.*  FROM present
        INNER JOIN shop_present ON present.id = shop_present.present_id  where present.shop_id= ".$shopId."   order by present.nav_name" );
        response::success(json_encode($Presents));

  }
    public function getDeletedPresents_v3(){
        $shopId = $_POST['shop_id'];
        $Presents = Present::find_by_sql("SELECT present.*, present_model.model_present_no  
            FROM `present` 
            INNER JOIN `present_model` ON present_model.present_id = present.id 
            WHERE present_model.language_id = 1 AND `shop_id` = '".$shopId."'
            ORDER BY `present`.`modified_datetime` DESC" );
        response::success(json_encode($Presents));

    }
  public function activateDeletedPresents_v2(){
        $presentId = $_POST['presentId'];
        $Present = Present::find($presentId);


        $Present->shop_id = abs($Present->shop_id);

        $Present->hide_for_demo_user = 0;
        $Present->active = 1;
        $Present->deleted = 0;
      $Present->gift_choice_flag = 1;
       $Present->save();


        $ShopPresents = ShopPresent::all(array(
        'conditions' => array('present_id' => $presentId)));
        $ShopPresent = ShopPresent::find($ShopPresents[0]->attributes["id"]);


        $ShopPresent->is_deleted = 0;
        $ShopPresent->active = 1;
        $ShopPresent->shop_id = abs($ShopPresent->shop_id);
        $ShopPresent->index_ = 1000;
        $ShopPresent->save();


        $Presents = Present::find_by_sql("SELECT present.id,  present.nav_name,present_media.media_path, shop_present.*  FROM present
        INNER JOIN shop_present ON present.id = shop_present.present_id
        INNER JOIN present_media ON present.id = present_media.present_id

        where present_media.index = 0 and shop_present.present_id= '".$presentId."'" );
        response::success(json_encode($Presents));
  }
  public function updateSampakPresent_v2() {
        $PresentModel = PresentModel::find($_POST['present_id']);
        $PresentModel->active = $_POST['active'];
        $PresentModel->save();
       response::success(json_encode($PresentModel));
  }
    public function updateHideForDemo_v2() {
        $Present = Present::find($_POST['present_id']);
        $Present->hide_for_demo_user = $_POST['active'];
        $Present->save();
       response::success(json_encode($Present));
  }
    public function updateHideForHomedelevery_v2() {
        $Present = Present::find($_POST['present_id']);
        $Present->show_if_home_delivery = $_POST['active'];
        $Present->save();
       response::success(json_encode($Present));
  }


    public function updateShowMasterPresent() {
        $Present = Present::find($_POST["id"]);
        $Present->show_master = $_POST["show_master"];
        $Present->save();
        response::success(json_encode($Present));
    }
    public function updateLockPresent_v2() {
        $Present = Present::find($_POST["id"]);
        $Present->lock_for_sync = $_POST["lock_for_sync"];
        $Present->save();
       response::success(json_encode($Present));
  }
public function checkIndexOrder() {
    // Tjek om POST data eksisterer
    if (!isset($_POST["sortList"]) || empty($_POST["sortList"]) || !isset($_POST["shop_id"])) {
        response::error("Manglende sorterings data eller shop_id");
        return;
    }

    $sortList = explode(",", $_POST["sortList"]);
    $shopId = (int)$_POST["shop_id"];

    // Fjern tomme værdier og trim whitespace
    $sortList = array_filter(array_map('trim', $sortList), function($value) {
        return !empty($value) && is_numeric($value);
    });

    if (empty($sortList)) {
        response::error("Ingen gyldige present IDs modtaget");
        return;
    }

    try {
        $indexMatches = true;
        $mismatches = array();

        for($i = 0; $i < count($sortList); $i++) {
            $presentId = (int)$sortList[$i];

            // Find ShopPresent for dette present_id
            $ShopPresents = ShopPresent::all(array(
                'conditions' => array(
                    'present_id' => $presentId,
                    'shop_id' => $shopId
                )
            ));

            if (empty($ShopPresents)) {
                $indexMatches = false;
                $mismatches[] = "Present ID {$presentId} ikke fundet";
                continue;
            }

            $currentIndex = (int)$ShopPresents[0]->attributes["index_"];

            // Tjek om nuværende index_ matcher den ønskede position
            if ($currentIndex !== $i) {
                $indexMatches = false;
                $mismatches[] = "Present ID {$presentId}: forventet index {$i}, faktisk index {$currentIndex}";
            }
        }

        if ($indexMatches) {
            response::success(json_encode(array(
                'matches' => true,
                'message' => 'Index rækkefølge matcher'
            )));
        } else {
            response::success(json_encode(array(
                'matches' => false,
                'message' => 'Index rækkefølge matcher IKKE',
                'mismatches' => $mismatches
            )));
        }

    } catch (Exception $e) {
        error_log("Fejl i checkIndexOrder: " . $e->getMessage());
        response::error("Der opstod en fejl under tjek af index rækkefølge");
    }
}
  public function sortPresent_V2() {
        $sortList = explode(",",$_POST["sortList"]);
           try {
                // Start database transaktion for konsistens
                // (hvis dit ORM understøtter det)

                for($i = 0; $i < count($sortList); $i++) {
                    $presentId = (int)$sortList[$i]; // Cast til int for sikkerhed

                    $ShopPresents = ShopPresent::all(array(
                        'conditions' => array('present_id' => $presentId)
                    ));

                    // Tjek om vi fandt nogle resultater
                    if (empty($ShopPresents)) {
                        error_log("Ingen ShopPresent fundet for present_id: " . $presentId);
                        continue; // Spring over i stedet for at fejle helt
                    }

                    $ShopPresent = ShopPresent::find($ShopPresents[0]->attributes["id"]);

                    if ($ShopPresent) {
                        $ShopPresent->index_ = $i;
                        $ShopPresent->save();
                    } else {
                        error_log("Kunne ikke finde ShopPresent med id: " . $ShopPresents[0]->attributes["id"]);
                    }
                }

                // Commit transaktion hvis relevant

                response::success(json_encode(array('message' => 'Sortering opdateret')));

            } catch (Exception $e) {
                // Rollback transaktion hvis relevant
                error_log("Fejl i sortPresent_V2: " . $e->getMessage());
                response::error("Der opstod en fejl under sorteringen");
            }
       /*
        for($i=0; $i < countgf($sortList);$i++   ){
         $ShopPresents = ShopPresent::all(array(
          'conditions' => array('present_id' => $sortList[$i])));
            $ShopPresent = ShopPresent::find($ShopPresents[0]->attributes["id"]);
            $ShopPresent->index_ = $i;
            $ShopPresent->save();
        }
        */
        $dummy = [];
        response::success(json_encode($dummy));
  }

  public function deactivatePresent_v2()
  {
         $presentId = $_POST['presentId'];
        $shopId = $_POST['shop_id'];
//        $ShopPresent = ShopPresent::all(array('conditions' => array('present_id = ? AND shop_id  = ?',$_POST['presentId'],$_POST['shop_id'])));
    //   $ShopPresent = ShopPresent::find_present_id($_POST['presentId']);


        $ShopPresents = ShopPresent::all(array(
        'conditions' => array('present_id' => $presentId, 'shop_id' =>$shopId)));
        $ShopPresent = ShopPresent::find($ShopPresents[0]->attributes["id"]);
        $ShopPresent->active = 0;
        $ShopPresent->is_deleted = 0;
        $ShopPresent->save();
        $dummy = [];

       response::success(json_encode($dummy));
  }

  public function getDeactivedPresents_v2(){
        $shopId = $_POST['shop_id'];
        $Presents = Present::find_by_sql("SELECT present.id, present.hide_for_demo_user, present.nav_name,shop_present.*  FROM present
        INNER JOIN shop_present ON present.id = shop_present.present_id  where shop_present.shop_id= '".$shopId."' order by present.nav_name" );
     response::success(json_encode($Presents));

  }

  public function activateDeactivatedPresents_v2(){
        $presentId = $_POST['presentId'];
        $Present = Present::find($presentId);
        $Present->hide_for_demo_user = 0;
        $Present->save();

        $ShopPresents = ShopPresent::all(array(
        'conditions' => array('present_id' => $presentId)));
        $ShopPresent = ShopPresent::find($ShopPresents[0]->attributes["id"]);
        $ShopPresent->active = 1;
        $ShopPresent->is_deleted = 0;
        $ShopPresent->index_ = 1000;
        $ShopPresent->save();
         $Presents = Present::find_by_sql("SELECT present.id, present.nav_name,present_media.media_path, shop_present.*  FROM present
        INNER JOIN shop_present ON present.id = shop_present.present_id
        INNER JOIN present_media ON present.id = present_media.present_id

        where present_media.index = 0 and shop_present.present_id= '".$presentId."'" );
        response::success(json_encode($Presents));
  }


//  ------------- v2 functioner  END --------------------------- //


  public function getBundle(){
    $bundleId = intval($_POST["bundleId"]);
    $company_id = intval($_POST["companyId"]);

    $PresentModelList =  PresentModel::find_by_sql("SELECT * FROM present_model WHERE model_id in (SELECT model_id FROM present_model WHERE present_id  =  ".$bundleId."  and language_id = 1 and active = 0 and is_deleted = 0) ||
    model_id in (SELECT model_id FROM shop_present_company_rules WHERE present_id  =  ".$bundleId." and `company_id` =  ".$company_id."   and  rules = 1 )  ORDER BY model_id,language_id ASC");
    // fejl fra starten active er aktive n�r den har v�rdien 0 , den burde v�re 1, men det kan ikke laves om
    for($i=0;sizeofgf($PresentModelList) > $i;$i++){
      $PresentModelList[$i]->attributes["active"] = 0;
    }
    response::success(json_encode($PresentModelList));
  }

  public function deletePresentInBundle(){
    $bundleId = intval($_POST["bundleId"]);
    $model = intval($_POST["model"]);
    $newBundleId = -1*$bundleId;
//      $newBundleId = $bundleId;

    // Check for orders on present_model
    $orders = \Order::find('all', array('conditions' => array( 'present_model_id' => ($model))));
    if(countgf($orders) > 0) {
        throw new \Exception("Model kan ikke slettes da der er lavet mindst 1 gavevalgt på den.");
    }
    
    // Check for active reservations on present_model
    $reservations = \PresentReservation::find_by_sql("SELECT * FROM present_reservation where model_id =  ".($model)." && sync_time is not null && sync_quantity > 0");
    if(countgf($reservations) > 0) {
        throw new \Exception("Model kan ikke slettes da den har aktive reservationer i navision, sæt til 0 og synkroniser før den kan slettes.");
    }

    // Update present_model to remove from present
    Dbsqli::setSql2("update present_model set present_id = ".$newBundleId." WHERE present_id = ".$bundleId." and model_id = ".$model) ;
    $dummy = [];

    // Update reservations
    Dbsqli::setSql2("update present_reservation set present_id = ".$newBundleId.", shop_id = -1*shop_id WHERE present_id = ".$bundleId." && model_id = model_id = ".$model) ;

    response::success(json_encode($dummy));
  }



  // Opret gave
  public function create() {
    $present = present::createPresent($_POST);
    $options = array('include' => array('descriptions', 'present_media'));
    response::success(make_json("present", $present, $options));
  }

  // Opret variant
  static public function createVariant() {
    $present = present::createPresentVariant($_POST);
    $options = array('include' => array('descriptions', 'present_media'));
    response::success(make_json("present", $present, $options));
  }
  static public function makeUnikVariant(){

        //  INSERT INTO present_media (`present_id`,`media_path`,`index`) SELECT `present_id`,`media_path`,`index` FROM present_media where id = 40323
 //  INSERT INTO present_media (`present_id`,`media_path`,`index`) SELECT `present_id`,`media_path`,`index` FROM present_media where id = 40323
       //  ShopAttribute::find_by_sql("SELECT * FROM shop_attribute WHERE shop_id = ".intval($this->shopid)." && (is_name = 1 || is_email = 1)");
        //   find_by_sql
       $presentToCopy = present::find(3465);
       unset($presentToCopy->attributes["id"]);
       $options = $presentToCopy->attributes;
       $present = new present($options);
       $present->save();
       System::connection()->commit();
       echo $present->id;
     //  response::success(make_json("present", $present, $options));
  }








  // Opret shop variant
  static public function createShopVariant() {
    //udfyld shop_id p� gaven
    $present = present::createShopVariant($_POST);
    $options = array('include' => array('descriptions', 'present_media'));
    response::success(make_json("present", $present, $options));
  }

  // Opdater gave
  public function update() {
      $present = present::updatePresent($_POST);
      $options = array('include' => array('descriptions', 'present_media'));
      response::success(make_json("present", $present, $options));
  }


  // Slet gave
  public function delete() {
    $present = present::deletePresent($_POST['id'],false);
    $options = array('include' => array('descriptions', 'present_media'));
    response::success(make_json("present", $present, $options));
  }
  public function undoDelete(){
    $present = present::undoDelete($_POST['id'],false);
    response::success(make_json("present", $present));
  }
  // Slet gave (bruges kun i backend)
  public function deleteReal() {
    $present = present::deletePresent($_POST['id'],true);
    $options = array('include' => array('descriptions', 'present_media'));
    response::success(make_json("present", $present, $options));
  }

  // Find gave
  public function read() {
    $present = present::readPresent($_POST['id']);
    $options = array('include' => array('descriptions', 'present_media' ,'models'));
    response::success(make_json("present", $present, $options));
  }

  // Find alle gaver
  



  public function readAll() {
      $shopid = 0;
      if(isset($_POST['shop_id']))
        $shopid = $_POST['shop_id'];

      $presents = present::all( array(
            'conditions' => array('deleted = ? and copy_of= 0  AND (shop_id = ? OR shop_id = ?)',0,0,$shopid),
       ));

    $this->registry->template->presents = $presents;
    $this->registry->template->copy = "";
    if(isset($_POST["mode"])){
        $this->registry->template->copy = "copy";
    }

    echo $this->registry->template->show('presentAdminHeadlist_view');
  }


  // Find top 10 gaver
  public function readTop10() {
    $presents = present::all(array(
        'conditions' => array('deleted' => 0, 'shop_id' => 0,'copy_of' => 0), 'limit' => '40', 'order' => 'id desc'
        ));
   
    $this->registry->template->presents = $presents;
    $this->registry->template->copy = "";
    //print_r($presents);

    if(isset($_POST["mode"])){
        $this->registry->template->copy = "copy";
    }

    echo $this->registry->template->show('presentAdminHeadlist_view');
  }

  // S�g p� alle master gaver
  public function searchPresents() {
    $sql = "";

    if(strpos($_POST['search'], 'id') === 0){
        $id = str_replace("id", "", $_POST['search']);

        $presents = Present::find('all', array('group' => 'id', 'joins' => array('descriptions', 'models'), 'conditions' => array(
            "present.id = ".$id." and deleted = 0 and shop_id = 0 and copy_of = 0 and present_description.language_id = 1 and present_model.language_id = 1"
        ), 'limit' => '100', 'order' => 'id desc'));
  
    }
    else {

        $pieces = explode(" ", $_POST['search']);
        foreach ($pieces as $part) {
            $sql .= " and
        (
           nav_name like '%$part%' or
           sampak_items like '%$part%' or
           present_no like '%$part%' or
           vendor like '%$part%' or
           present_description.caption like '%$part%' or
           model_present_no like '%$part%' or
           model_name like '%$part%'or
           model_no like '%$part%' or
           present.id like '%$part%' 



         )";
        }

        $presents = Present::find('all', array('group' => 'id', 'joins' => array('descriptions', 'models'), 'conditions' => array(
            "deleted = 0 and shop_id = 0 and copy_of = 0 and present_description.language_id = 1 and present_model.language_id = 1" . $sql
        ), 'limit' => '100', 'order' => 'id desc'));
        if ($_POST['search'] == "") {
            echo $sql;
            die("");
        }

    }

    $this->registry->template->presents = $presents;
    $this->registry->template->copy = "";



    if(isset($_POST["mode"])){
        $this->registry->template->copy = "copy";
    }

      echo $this->registry->template->show('presentAdminHeadlist_view');
    
    /*
      if($_POST['search'] == "well"){
          echo $this->registry->template->show('ulle');
      } else {
          echo $this->registry->template->show('presentAdminHeadlist_view');
      }
    */

  }

    public function searchPresentsDeleted() {
    $sql = "";
    $pieces = explode(" ", $_POST['search']);
    foreach($pieces as $part){
        $sql.= " and
        (
           nav_name like '%$part%' or
           present_no like '%$part%' or
   
           vendor like '%$part%' or
           present_description.caption like '%$part%' or
           model_present_no like '%$part%' or
           model_name like '%$part%'or
           model_no like '%$part%'
         )";
   }

    $presents = Present::find('all',array('group'=>'id', 'joins' => array('descriptions','models'),'conditions' => array(
        "deleted = 1 and shop_id = 0 and copy_of = 0 and present_description.language_id = 1 and present_model.language_id = 1 ".$sql
   	),'limit' => '100', 'order' => 'id desc'));

    $this->registry->template->presents = $presents;
    $this->registry->template->copy = "";
    if(isset($_POST["mode"])){
        $this->registry->template->copy = "copy";
    }

    echo $this->registry->template->show('presentAdminHeadlist_view');

  }



  // S�g p� alle master varianter
    public function searchVariants() {
        $sql = "";
    $pieces = explode(" ", $_POST['search']);
    foreach($pieces as $part){
      echo  $sql.= " and
        (
           nav_name like '%$part%' or
           present_no like '%$part%' or
           internal_name like '%$part%' or
           vendor like '%$part%' or
           present_description.caption like '%$part%' or
           model_present_no like '%$part%' or
           model_name like '%$part%' or
           model_no like '%$part%'
         )";
   }

    $presents = Present::find('all',array('group'=>'id', 'joins' => array('descriptions','models'),'conditions' => array(
        "deleted = 0 and  present.pim_id > 0 and shop_id = 0 and present_description.language_id = 1 and present_model.language_id = 1 ".$sql
   	)));

        //$presents = present::all(array('conditions' => array("deleted = 0 and shop_id = 0 ".$sql  )));

   //    $options = array('include' => array('descriptions', 'present_media'));
        //response::success(make_json("presents", $presents, $options));

        $this->registry->template->presents = $presents;
        $this->registry->template->copy = "nocopy";
        if(isset($_POST["copy"])){
            $this->registry->template->copy = "copy";
        }
        echo $this->registry->template->show('presentAdminHeadlist_variant_view');
    }

     public function searchVariantsDeleted() {
        $sql = "";
    $pieces = explode(" ", $_POST['search']);
    foreach($pieces as $part){
        $sql.= " and
        (
           nav_name like '%$part%' or
           present_no like '%$part%' or
           internal_name like '%$part%' or
           vendor like '%$part%' or
           present_description.caption like '%$part%' or
           model_present_no like '%$part%' or
           model_name like '%$part%' or
           model_no like '%$part%'
         )";
   }

    $presents = Present::find('all',array('group'=>'id', 'joins' => array('descriptions','models'),'conditions' => array(
        "deleted = 1 and shop_id = 0 and present_description.language_id = 1 and present_model.language_id = 1 ".$sql
   	)));

        //$presents = present::all(array('conditions' => array("deleted = 0 and shop_id = 0 ".$sql  )));

   //    $options = array('include' => array('descriptions', 'present_media'));
        //response::success(make_json("presents", $presents, $options));
        $this->registry->template->presents = $presents;
        $this->registry->template->copy = "nocopy";
        if(isset($_POST["copy"])){
            $this->registry->template->copy = "copy";
        }
        echo $this->registry->template->show('presentAdminHeadlist_variant_view');
    }


  public function searchVariants_old() {
    $what = $_POST['search'];
    $presents = Present::find('all',array('joins' => array('descriptions'),'conditions' => array(
        "deleted = 0 and shop_id = 0 and copy_of > 0 and present_description.language_id = 1  and
        (
           name like '%$what%' or
           present_no like '%$what%' or
           internal_name like '%$what%' or
           present_description.caption like '%$what%'
         )"
   	)));

    //foreach($presents as $present) {
    //    echo $present->name;
    //}
    $this->registry->template->presents = $presents;
    $this->registry->template->copy = "";
    if(isset($_POST["mode"])){
        $this->registry->template->copy = "copy";
    }

    echo $this->registry->template->show('presentAdminHeadlist_variant_view');

  }


  //Find varianter af gave
  public function readVariants() {
    $presents = present::all(array('conditions' => array('deleted' => 0, 'shop_id' => 0, 'copy_of' => $_POST['id'])));

    $options = array('include' => array('descriptions', 'present_media'));
    //response::success(make_json("presents", $presents, $options));
    $this->registry->template->presents = $presents;
    $this->registry->template->copy = "nocopy";
    if(isset($_POST["copy"])){
        $this->registry->template->copy = "copy";
    }
    echo $this->registry->template->show('presentAdminHeadlist_view');

  }
    // finder alle gaver p� en shop
    public function getPresentsOnShop() {
        $shopId = $_POST["shop_id"];
        //$join = 'LEFT JOIN shop ON(shop.id = present_reservation.shop_id)';
        //$PresentReservation = PresentReservation::find('all',array('joins' => $join,'conditions' => array('shop.is_demo = ? AND shop.is_gift_certificate = ? AND active = ? AND deleted = ?',0,0,1,0),'having'=>'quantity > 250'));
        $join = 'LEFT JOIN shop_present ON(present.id = shop_present.present_id)';
        $presentList =  Present::find('all',array('joins' => $join,'conditions' => array('shop_present.shop_id = ? ',$shopId)));
        response::success(json_encode($presentList));

    }




  //Find TILH�RSDFORHOLD af gave

  // Variant af en gave
  // ShopVariuant af gave.
  public function getAllVariants() {
    $result = [];
    $presentid = $_POST['id'];


    $master= $this->findMasterPresent($_POST['id']);
    $master_shopvariants = present::all(array('conditions' => array('deleted = ? AND shop_id  > ? AND copy_of = ?',0,0,$master->id)));

    $result['master'] = $this->presentToObj($master,$presentid);

    $master_shopvariants_array = [];
    $result['master_shopvariants'] = $this->presentArrayAppendRecords($master_shopvariants_array,$master_shopvariants,$presentid);

    $variant_array = [];
    $variant_shopvariant_array = [];
    $this->findAllVariants($variant_array,$variant_shopvariant_array,$master->id,$presentid);

    $result['variants'] =    $variant_array;
    $result['variants_shopvariants'] = $variant_shopvariant_array;

    response::success(json_encode($result));
  }

  // vi skal finde shopvarianter af alle varianter
  private function findAllVariants(&$variant_array,&$variant_shopvariant_array,$presentid,$caller_id) {
   $variants = present::all(array('conditions' => array('deleted = ? AND shop_id = ? AND copy_of = ?',0,0,$presentid)));
   if(count($variants)>0) {
     $variant_array = $this->presentArrayAppendRecords($variant_array,$variants,$caller_id);
     foreach($variants as $variant) {
         $this->findAllShopVariants($variant_shopvariant_array,$variant->id,$caller_id);
         $this->findAllVariants($variant_array,$variant_shopvariant_array,$variant->id,$caller_id);
     }
   }
}

  private function findAllShopVariants(&$variant_shopvariant_array,$presentid,$caller_id) {
   $variants = present::all(array('conditions' => array('deleted = ? AND shop_id <> ? AND copy_of = ?',0,0,$presentid)));
   if(count($variants)>0) {
     $variant_shopvariant_array = $this->presentArrayAppendRecords($variant_shopvariant_array,$variants,$caller_id);
     foreach($variants as $variant) {

         $this->findAllShopVariants($variant_shopvariant_array,$variant->id,$caller_id);
     }
   }
}

private function presentArrayAppendRecords($presentArray,$presents,$caller_id) {
   foreach($presents as $present) {
      $presentArray[] = $this->presentToObj($present,$caller_id);
    }
    return($presentArray);
}

private function presentToObj($present,$caller_id) {
    $presentObj =   new stdClass();
    $presentObj->id = $present->id;
    $presentObj->copy_of = $present->copy_of;
    $presentObj->name = $present->name;

    if($present->id == $caller_id)
      $presentObj->is_caller = 1;
    else
      $presentObj->is_caller = 0;

    $presentObj->shops = [];
    // shops...
    foreach($present->shop_presents as $shoppresent) {
       try {
            $shopObj =   new stdClass();
            $shopObj->id = $shoppresent->id;
            $shop = Shop::find($shoppresent->shop_id);
            $shopObj->name = $shop->name;
            $presentObj->shops[] =$shopObj;
        }
        catch (Exception $e) {}
    }

    return($presentObj);
}

  public function findMasterPresent($present_id) {

       $present = present::readPresent($present_id);

       if ($present->copy_of ==0 && $present->shop_id==0) {
         return($present);
       } else {
         return($this->findMasterPresent($present->copy_of));
       }
  }


  // Find readShop
  public function readShop() {
    $presents = present::all(array('conditions' => array('deleted' => 0, 'shop_id' => $_POST['shop_id'])));
    $options = array('include' => array('descriptions', 'present_media'));
    response::success(make_json("presents", $presents, $options));
   }


    public function getVariantList() {
       $present = Present::find($_POST['id']);
       $dummy = [];
       $dummy['variant_list'] = $present->variant_list;
       response::success(json_encode($dummy));
    }

    public function updateVariantList() {
       $present = Present::find($_POST['id']);
       $present->variant_list = $_POST['variant_list'];
       $present->save();
       $dummy = [];
       response::success(json_encode($dummy));

    }

    public function isVariantOnOrder() {
        $order = Order::find_by_present_id_and_present_model_present_no($_POST['present_id'],$_POST['model_no']);
        if(count($order)==0)
           throw new exception('not found');

          $dummy = [];
          response::success(json_encode($dummy));
    }

   //DHTMLX Data Connector Methods


   public function getList() {
       // id skal ikke v�re i listen da den er objuklatira

       $presents = present::all( array(
            'conditions' => array('deleted = ?  AND (shop_id = ?)',0,0),
            'select' => 'id,present_no,name,price,active,created_datetime'
       ));
      echo $this->render_json($presents);
   }

   //Models 2017-04-02
   public function getModels() {
      $models = PresentModel::all(array('present_id' =>$_POST['present_id']));
      response::success(make_json("model", $models));
   }

    //static public function addModel() {
    //  $model = Present::addModel($_POST);
    //  response::success(make_json("model", $model));
    //}

    static public function createNewModel() {
      $modelId = Present::createNewModel($_POST['present_id']);
      $dummy = [];
      $dummy['model_id'] = $modelId;
      response::success(json_encode($dummy));
    }
   public function getIdFromModelId(){
        $modelID = $_POST["modelID"];
       $presents = PresentModel::all( array(
           'conditions' => array('model_id = ?  AND language_id = ?  ',$modelID,1)
       ));
       response::success(json_encode($presents));
      
   }
   static public function updateModels() {
      Present::updateModels($_POST['present_id'],$_POST['variant_list']);
      $dummy = [];
      response::success(json_encode($dummy));
   }

   //static public function removeModel()  {
   //   $presentModel = PresentModel::find($_POST['id']);
   //   $presentModel->delete();
   //    response::success(make_json("model", $presentModel));
   //}

   static public function activateModel()  {
      $presentModel = PresentModel::find($_POST['id']);
      $presentModel->active = 1;
      $presentModel->save();
       $dummy = [];
      response::success(json_encode($dummy));
    }
  static public function deactivateModel()  {
      $presentModel = PresentModel::find($_POST['id']);
      $presentModel->active = 0;
      $presentModel->save();
      $dummy = [];
      response::success(json_encode($dummy));
   }

   function render_json($dataset) {
   $recordCount = countgf($dataset);
   $j=0;
   echo "{\"rows\":[";
   foreach($dataset as $record) {
       $attributeCount = countgf($record->attributes);
       $i = 1;
       echo "{\"id\":".$record->{"id"}.", \"data\":[";
       foreach($record->attributes as $destinationKey => $destinationValue)
        {
            if($destinationKey!="id"){
                if(gettype($record->{$destinationKey})=="object")
                  echo '"'.$record->{$destinationKey}->format('d-m-Y H:m:s').'"';
                else
                  echo '"'.$record->{$destinationKey}.'"';
            $i++;
            if($i<$attributeCount)
              echo ",";

            }
        }
       echo "]}";
       $j++;
       if($j<$recordCount)
       echo ",";

    }
    echo  "]}";

  }

}
?>

