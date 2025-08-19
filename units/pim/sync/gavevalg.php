<?php

namespace GFUnit\pim\sync;
include ("thirdparty/php-image-magician/php_image_magician.php");
use GFBiz\units\UnitController;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);



class Gavevalg extends UnitController
{
    private $error=[];
    public function __construct()
    {
        parent::__construct(__FILE__);

    }

    public function updateModelsItemnr($pimID,$itemno)
    {
        $sql =  "UPDATE `present_model` set `model_present_no` = '".$itemno."' WHERE `present_id`  
        in (SELECT id  FROM `present` WHERE copy_of 
            in (SELECT id  FROM `present` WHERE `pim_id` = 5810235 and shop_id = 0 and `copy_of` = 0)
        )";
     //   \Dbsqli::setSql2($sql);
    }


     public function handleGroupModel($kontainerID,$gavevalgID,$data){
      //  var_dump($data);
      $kontainer = new kontainerCom;
      $pimList = [];
      $group_product_nos = $data->group_product_nos->value ?? false ? $data->group_product_nos->value : "";
      $modelList = explode("\n", $group_product_nos);



      foreach ($modelList as $itemnr){
          if(trim($itemnr) == "") continue;
          $res = $kontainer->getDataOnItemnr(trim($itemnr));
          $res = json_decode($res);

          $Group_product_kontainerID = $res->data[0]->id;
          $att = $res->data[0]->attributes;
           $sql = "select * from present_model where pim_id = $Group_product_kontainerID and present_id =".$gavevalgID;

       $model = \Dbsqli::getSql2($sql);

          // model data
          $erp_product_name_da =  $att->erp_product_name_da->value ?? false ? $att->erp_product_name_da->value : "";
          $erp_product_name_en =  $att->erp_product_name_en->value ?? false ? $att->erp_product_name_en->value : "";
          $erp_product_name_no =  $att->erp_product_name_no->value ?? false ? $att->erp_product_name_no->value : "";
          $erp_product_name_se =  $att->erp_product_name_sv->value ?? false ? $att->erp_product_name_sv->value : "";
          $pack_billede =  $att->pack_billede->value ?? false ? $att->pack_billede->value : "none.jpg";
          if($pack_billede != "none.jpg"){
              $obj = $this->getImgUrl($pack_billede);
              $imgJ = json_decode($obj);
              $pack_billede = $this->createPackPTImg($imgJ->data->attributes->url,$Group_product_kontainerID ).".jpg";
          }
          $pimList[] = $Group_product_kontainerID;
          if(sizeof($model) == 0){

              $maxIDRs = \Dbsqli::getSql2(" SELECT max(`model_id`) as maxId FROM `present_model`");
              $maxID = $maxIDRs[0]["maxId"];
              $maxID++;

              $sql1 = "INSERT INTO `present_model` ( `model_id`, `original_model_id`, `pim_id`, `present_id`, `language_id`, `model_present_no`, `model_name`, `model_no`, `media_path`, `active`, `dummy`, `is_deleted`, `aliasletter`, `fullalias`, `moms`, `msg1`, `custom_msg1`, `sampak_items`)
            VALUES ( $maxID, '0', $Group_product_kontainerID,$gavevalgID, '1', '$itemnr', '$erp_product_name_da', '', '$pack_billede', '0', NULL, '0', '', '', '25', '0', NULL, '')";
              \Dbsqli::setSql2($sql1);
              $sql2 = "INSERT INTO `present_model` ( `model_id`, `original_model_id`, `pim_id`,`present_id`, `language_id`, `model_present_no`, `model_name`, `model_no`, `media_path`, `active`, `dummy`, `is_deleted`, `aliasletter`, `fullalias`, `moms`, `msg1`, `custom_msg1`, `sampak_items`)
            VALUES ( $maxID, '0', $Group_product_kontainerID, $gavevalgID,'2', '$itemnr', '$erp_product_name_en', '', '$pack_billede', '0', NULL, '0', '', '', '25', '0', NULL, '')";
              \Dbsqli::setSql2($sql2);
              $sql3 = "INSERT INTO `present_model` ( `model_id`, `original_model_id`,`pim_id`, `present_id`, `language_id`, `model_present_no`, `model_name`, `model_no`, `media_path`, `active`, `dummy`, `is_deleted`, `aliasletter`, `fullalias`, `moms`, `msg1`, `custom_msg1`, `sampak_items`)
            VALUES ( $maxID, '0', $Group_product_kontainerID, $gavevalgID,'3', '$itemnr', '$erp_product_name_da', '', '$pack_billede', '0', NULL, '0', '', '', '25', '0', NULL, '')";
              \Dbsqli::setSql2($sql3);
              $sql4 = "INSERT INTO `present_model` ( `model_id`, `original_model_id`, `pim_id`, `present_id`, `language_id`, `model_present_no`, `model_name`, `model_no`, `media_path`, `active`, `dummy`, `is_deleted`, `aliasletter`, `fullalias`, `moms`, `msg1`, `custom_msg1`, `sampak_items`)
            VALUES ( $maxID, '0', $Group_product_kontainerID, $gavevalgID,'4', '$itemnr', '$erp_product_name_no', '', '$pack_billede', '0', NULL, '0', '', '', '25', '0', NULL, '')";
              \Dbsqli::setSql2($sql4);
              $sql5 = "INSERT INTO `present_model` ( `model_id`, `original_model_id`, `pim_id`, `present_id`, `language_id`, `model_present_no`, `model_name`, `model_no`, `media_path`, `active`, `dummy`, `is_deleted`, `aliasletter`, `fullalias`, `moms`, `msg1`, `custom_msg1`, `sampak_items`)
            VALUES ( $maxID, '0', $Group_product_kontainerID,$gavevalgID, '5', '$itemnr', '$erp_product_name_se', '', '$pack_billede', '0', NULL, '0', '', '', '25', '0', NULL, '')";
              \Dbsqli::setSql2($sql5);

          } else {
              // update MODEL
              $insetStrModel = "model_present_no = '$itemnr', model_name = '$erp_product_name_da',  media_path = '$pack_billede'";
              $sqlUpdateModelDa = "update present_model set " . $insetStrModel . "  where present_id = " . $gavevalgID . " and language_id = 1 and pim_id=".$Group_product_kontainerID;
              \Dbsqli::setSql2($sqlUpdateModelDa);

              $insetStrModel = "model_present_no = '$itemnr', model_name = '$erp_product_name_en',  media_path = '$pack_billede'";
              $sqlUpdateModelEn = "update present_model set " . $insetStrModel . "  where present_id = " . $gavevalgID . " and language_id = 2 and pim_id=".$Group_product_kontainerID;
              \Dbsqli::setSql2($sqlUpdateModelEn);

              $insetStrModel = "model_present_no = '$itemnr', model_name = '$erp_product_name_no',  media_path = '$pack_billede'";
              $sqlUpdateModelNo = "update present_model set " . $insetStrModel . "  where present_id = " . $gavevalgID . " and language_id = 4  and pim_id=".$Group_product_kontainerID;
              \Dbsqli::setSql2($sqlUpdateModelNo);

              $insetStrModel = "model_present_no= '$itemnr', model_name = '$erp_product_name_se',  media_path = '$pack_billede'";
              $sqlUpdateModelSe = "update present_model set " . $insetStrModel . "  where present_id = " . $gavevalgID . " and language_id = 5 and pim_id=".$Group_product_kontainerID;
              \Dbsqli::setSql2($sqlUpdateModelSe);
          }
       }
        return $pimList;

      // hent data fra container
        // udtrække navnavn,pack_billede
        // tjek om nogle er blevet fjernet og tjek om der er valgt på varen

    }

    public function singleSync($data,$id){
        $kontainer = new kontainerCom;
        $data = json_decode($data);
        $archive = false;
        $kontainerID = $id;


        $error = [];

        $state = "";
        $att = $data->data->attributes;
        $archive = $att->archive->value ?? false ? $att->archive->value : "";

        $type = $att->product_type->value ?? false ? $att->product_type->value : "";

        $isStoreView = false;
        if($att->storeview ?? false){
            foreach ($att->storeview as $view){
                if($view->value == "Gavevalg") $isStoreView = true;
            }
        }
// validere billeder
        if($att->image_1->meta ?? false){ !$this->validateImg($att->image_1->meta)  ? $this->error[] = "Fejl billede nr.1":""; }
        if($att->image_2->meta ?? false){ !$this->validateImg($att->image_2->meta)  ? $this->error[] = "Fejl billede nr.2":""; }
        if($att->image_3->meta ?? false){ !$this->validateImg($att->image_3->meta)  ? $this->error[] = "Fejl billede nr.3":""; }
        if($att->image_4->meta ?? false){ !$this->validateImg($att->image_4->meta)  ? $this->error[] = "Fejl billede nr.4":""; }
        if($att->pack_billede->meta ?? false){ !$this->validateImg($att->pack_billede->meta)  ? $this->error[] = "Fejl billede pack":""; }

        if(sizeof($this->error) > 0){
            return array("status"=>1,"msg"=>$this->error);
        }



        // kontainer id
        $kontainerID =  $data->data->id;


        // godkendt
        $tekst_godkendt  =  $att->tekst_godkendt->value ?? false ? $att->tekst_godkendt->value : false;
        $billeder_godkendt  =  $att->billeder_godkendt->value ?? false ? $att->billeder_godkendt->value : false;
        $archive = $att->archive->value ?? false ? $att->billeder_godkendt->value : false;

        if($billeder_godkendt == false || $tekst_godkendt == false){
            $isStoreView = false;
        }

        $tekst_godkendt = $tekst_godkendt  == true ? "true":"false";
        $billeder_godkendt = $billeder_godkendt  == true ? "true":"false";
        /****  MAPPING ****/

        if($isStoreView == false){
            $this->error["not_approved"] = "ikke godkendt";
            return array("status"=>1,"msg"=>$this->error);
        }

        // varenr
        $itemnr =  $att->product_no->value ?? false ? $att->product_no->value : "";
        $itemnr = $this->cleanStr($itemnr);
        if($itemnr == "" && $type != "Group" ){
          $postData = "{\n  \"data\": {\n    \"type\": \"category_item\",\n    \"attributes\": {\n      \"product_no\": {\n        \"value\": \"##".$kontainerID."\"\n      }\n\n    }\n  }\n}";
          $kontainerRes =   $kontainer->updateItem($kontainerID,$postData);
          $itemnr = "##".$kontainerID;

        }

        // erp
        $erp_product_name_da =  $att->erp_product_name_da->value ?? false ? $att->erp_product_name_da->value : "";
        $erp_product_name_en =  $att->erp_product_name_en->value ?? false ? $att->erp_product_name_en->value : "";
        $erp_product_name_no =  $att->erp_product_name_no->value ?? false ? $att->erp_product_name_no->value : "";
        $erp_product_name_se =  $att->erp_product_name_sv->value ?? false ? $att->erp_product_name_sv->value : "";

        // overskrift
        $product_name_da = $att->product_name_da->value ?? false ? $att->product_name_da->value : "";
        $product_name_en = $att->product_name_en->value ?? false ? $att->product_name_en->value : "";
        $product_name_no = $att->product_name_no->value ?? false ? $att->product_name_no->value : "";
        $product_name_se = $att->product_name_sv->value ?? false ? $att->product_name_sv->value : "";
/*
        $product_name_da = addslashes($product_name_da);
        $product_name_en = addslashes($product_name_en);
        $product_name_no = addslashes($product_name_no);
        $product_name_se = addslashes($product_name_se);
*/

        // Beskrivelser
        $description_da =  $att->description_da->value ?? false ? $att->description_da->value : "###";
        $description_en =  $att->description_en->value ?? false ? $att->description_en->value : "###";
        $description_no =  $att->description_no->value ?? false ? $att->description_no->value : "###";
        $description_se =  $att->description_sv->value ?? false ? $att->description_sv->value : "###";
        if($kontainerID == "8623507"){
//            var_dump($description_se);
        }


        // Logo
        $logoKontainerID = "0";
        $vendor = $att->suppliers->value ?? false ? $att->suppliers->value : "";


        $logo =  $att->logo->value ?? false ? $att->logo->value : "";


        if($logo != ""){
         
            $id =  $att->logo->meta->resource_item_id ?? false ? $att->logo->meta->resource_item_id : "";

            $resLogo = $this->getKontainerLogoID($id);
            $logoData = json_decode($resLogo);
            $logoKontainerID = $logoData->data->attributes->logo->value ?? false ? "klogo_".$logoData->data->attributes->logo->value : 0;
        }

        // vejledende pris
        $vejl_udsalgspris_tekst_da =  $att->vejl_udsalgspris_tekst_da->value ?? false ? $att->vejl_udsalgspris_tekst_da->value : "";
        $vejl_udsalgspris_tekst_en =  $att->vejl_udsalgspris_tekst_en->value ?? false ? $att->vejl_udsalgspris_tekst_en->value : "";
        $vejl_udsalgspris_tekst_no =  $att->vejl_udsalgspris_tekst_no->value ?? false ? $att->vejl_udsalgspris_tekst_no->value : "";
        $vejl_udsalgspris_tekst_se =  $att->vejl_udsalgspris_tekst_sv->value ?? false ? $att->vejl_udsalgspris_tekst_sv->value : "";



        // budget
        $budget_price_da =  $att->budget_price_da[0]->value ?? false ? $att->budget_price_da[0]->value : 0;
        $budget_price_en =  $att->budget_price_en[0]->value ?? false ? $att->budget_price_en[0]->value : 0;
        $budget_price_no =  $att->budget_price_no[0]->value ?? false ? $att->budget_price_no[0]->value : 0;
        $budget_price_se =  $att->budget_price_sv[0]->value ?? false ? $att->budget_price_sv[0]->value : 0;
        // kost pris fra nav
        $cost_price_da =  $att->cost_price_da->value ?? false ? $att->cost_price_da->value : 0;
        $cost_price_no =  $att->cost_price_no->value ?? false ? $att->cost_price_no->value : 0;
        $cost_price_se =  $att->cost_price_se->value ?? false ? $att->cost_price_se->value : 0;


        // vis i sælger modul
        $show_in_sales_module  =  $att->show_in_sales_module->value ?? false ? $att->show_in_sales_module->value : false;
        $show_in_sales_module = $show_in_sales_module== true ? 1:0;

        $show_in_sales_module_no  =  $att->show_in_sales_module_no->value ?? false ? $att->show_in_sales_module_no->value : false;
        $show_in_sales_module_no = $show_in_sales_module_no== true ? 1:0;
        // billeder
        $img2Show = $img3Show = $img4Show = 0;

        // $pack_billede
        $pack_billede =  $att->pack_billede->value ?? false ? $att->pack_billede->value : "none.jpg";
        $pack_billede = $type == "Group" ? "": $pack_billede;

        if($pack_billede != "none.jpg" && $pack_billede != ""){
            $obj = $this->getImgUrl($pack_billede);
            $imgJ = json_decode($obj);
            //$pack_billede =  $this->createImg($pack_billede);

            $pack_billede = $this->createPackPTImg($imgJ->data->attributes->url,$kontainerID).".jpg";

        }


        $img1 =  $att->image_1->value ?? false ? $att->image_1->value : "";
        if($img1 != ""){
            $obj = $this->getImgUrl($img1);
            $imgJ = json_decode($obj);
            $img1ID = $imgJ->data->id;
            $doUpdateImg = $this->doUpdateImg($imgJ->data->id,$kontainerID,0);
           if( $doUpdateImg === true) {
                $img1 = $imgJ->data->attributes->url;
                $img1 = $this->createImg($img1);
                // skal ikke benyttes længere;
                //$this->createPTImg($img1,$imgJ->data->attributes->url);
                $filename = $img1 . "_small.jpg";
                $targetFile = $_SERVER["DOCUMENT_ROOT"] . "/gavefabrikken_backend/views/media/user/" . $img1 . ".jpg";
                $targetFileSmall = $_SERVER["DOCUMENT_ROOT"] . "/gavefabrikken_backend/views/media/small/" . $filename;
                self::createSmallImg(300, $targetFile, $targetFileSmall);
            } else {
                $img1 = $doUpdateImg;
            }
        }
        $ptFilename = "";
        $img2 =  $att->image_2->value ?? false ? $att->image_2->value : "";
        if($img2 != ""){
            $obj = $this->getImgUrl($img2);
            $imgJ = json_decode($obj);
            $img2ID = $imgJ->data->id;
            $doUpdateImg = $this->doUpdateImg($img2ID,$kontainerID,1);

            $img2Show =  $att->show_in_presentation_2->value ?? false;
            $img2Show = $img2Show == true ? 1:0;
            $img2 =  $imgJ->data->attributes->url;
            if( $doUpdateImg === true) {
                $img2 =  $this->createImg($img2);
                if($img2Show == 1){
                    $ptFilename = $imgJ->data->id.$this->generateRandomString().".jpg";
                    $this->createPTImg($ptFilename,$imgJ->data->attributes->url);
                }
            } else {
                $img2 = $doUpdateImg;
                $presentRS = \Dbsqli::getSql2("select pt_img_small from present where pim_id = $kontainerID and copy_of = 0");
                $ptFilename = $presentRS[0]["pt_img_small"];
                if($img2Show == 1 && $ptFilename == ""){
                    $ptFilename = $imgJ->data->id.$this->generateRandomString().".jpg";
                    $this->createPTImg($ptFilename,$imgJ->data->attributes->url);
                }


            }
        }

        $img3 =  $att->image_3->value ?? false ? $att->image_3->value : "";
        if($img3 != ""){
            $obj = $this->getImgUrl($img3);
            $imgJ = json_decode($obj);
            $img3ID = $imgJ->data->id;
            $doUpdateImg = $this->doUpdateImg($imgJ->data->id,$kontainerID,2);
            if( $doUpdateImg === true) {
                $img3 =  $imgJ->data->attributes->url;
                $img3 =  $this->createImg($img3);
            } else {
                $img3 = $doUpdateImg;
            }
        }
        $img4 =  $att->image_4->value ?? false ? $att->image_4->value : "";
        if($img4 != ""){
            $obj = $this->getImgUrl($img4);
            $imgJ = json_decode($obj);
            $img4ID = $imgJ->data->id;
            $doUpdateImg = $this->doUpdateImg($imgJ->data->id,$kontainerID,3);
            if( $doUpdateImg === true) {
                $img4 =  $imgJ->data->attributes->url;
                $img4 =  $this->createImg($img4);
            } else {
                $img4 = $doUpdateImg;
            }
        }

        // kun hos
        $kun_hos_gavefabrikken_da  =  $att->kun_hos_gavefabrikken_da->value ?? false ? $att->kun_hos_gavefabrikken_da->value : false;
        $kun_hos_gavefabrikken_da = $kun_hos_gavefabrikken_da  == true ? "true":"false";
        // omtanke
        $gave_med_omtanke_da  =  $att->gave_med_omtanke_da->value ?? false ? $att->gave_med_omtanke_da->value : false;
        $gave_med_omtanke_da = $gave_med_omtanke_da  == true ? 1:0;
   
        // PT images
        $ptimage = "";


        $ptimageSmall = $img2Show == 1 ? $ptFilename:"";


        // Tjek om gaven er exisistere
        $sql = "select id from present where pim_id = ".$kontainerID." and copy_of = 0 ";
        $rsPresent =  \Present::find_by_sql($sql);
        // priser (ej færdig)



/*
        $showBudgetDa = $budget_price_da == "" ? "false":"true";
        $showRetalDa =  $vejl_udsalgspris_tekst_da == 0 ?  "false":"true";
        $showBudgetNo =  $budget_price_no == "" ? "false":"true";
        $showRetalNo =  $vejl_udsalgspris_tekst_no == 0 ?  "false":"true";
        // der er byttet om på pris og budget
        $sqlPt_price = '{"pris":"'.$budget_price_da.'","vis_pris":"'.$showBudgetDa.'","budget":"'.$vejl_udsalgspris_tekst_da.'","vis_budget":"'.$showRetalDa.'","special":"","vis_special":"false"}';
        $sqlPt_price_no = '{"pris":"'.$budget_price_no.'","vis_pris":"'.$showBudgetNo.'","budget":"'.$vejl_udsalgspris_tekst_no.'","vis_budget":"'.$showRetalNo.'","special":"","vis_special":"false"}';

        $cost_price_da = (int)$cost_price_da;
        $cost_price_no = (int)$cost_price_no;
        $budget_price_da = (int)$budget_price_da;

        $vejl_udsalgspris_tekst_da = str_replace(".", "",$vejl_udsalgspris_tekst_da);

 */





        // NavPriceOverRuleSync

        $res_dk = \Dbsqli::getSql2("SELECT * FROM `navision_item` WHERE no = '".$itemnr."'  and deleted is null and  language_id = 1");
        $res_no = \Dbsqli::getSql2("SELECT * FROM `navision_item` WHERE no = '".$itemnr."'  and deleted is null and  language_id = 4");

        if(sizeof($res_dk) > 0){

            $cost_price_da              = ($res_dk[0]["standard_cost"] == "" || $res_dk[0]["standard_cost"] == 0) ? $cost_price_da : $res_dk[0]["standard_cost"];
            $vejl_udsalgspris_tekst_da  = ($res_dk[0]["vejl_pris"]  == "" || $res_dk[0]["vejl_pris"]  == 0) ? $vejl_udsalgspris_tekst_da : $res_dk[0]["vejl_pris"];
            $budget_price_da               = ($res_dk[0]["unit_price"]  == "" || $res_dk[0]["unit_price"]  == 0) ? $budget_price_da  : $res_dk[0]["unit_price"];
        }
        if(sizeof($res_no) > 0){
            $cost_price_no              = ($res_no[0]["standard_cost"] == "" || $res_no[0]["standard_cost"] == 0) ? $cost_price_no : $res_no[0]["standard_cost"];
            $vejl_udsalgspris_tekst_no  = ($res_no[0]["vejl_pris"]  == "" || $res_no[0]["vejl_pris"]  == 0) == "" ? $vejl_udsalgspris_tekst_no : $res_no[0]["vejl_pris"];
            $budget_price_no               = ($res_no[0]["unit_price"]  == "" || $res_no[0]["unit_price"]  == 0) ? $budget_price_no  : $res_no[0]["unit_price"];
        }
        // json




        $showBudgetDa = ($budget_price_da == 0 || $budget_price_da == "") ? "false":"true";
        $showRetalDa =  ($vejl_udsalgspris_tekst_da == 0 || $vejl_udsalgspris_tekst_da == "") ?  "false":"true";
        $showBudgetNo =  ($budget_price_no == 0 || $budget_price_no == "") ? "false":"true";
        $showRetalNo =  ($vejl_udsalgspris_tekst_no == 0 || $vejl_udsalgspris_tekst_no == "") ?  "false":"true";
        $showBudgetSe =  ($budget_price_se == 0 || $budget_price_se == "") ? "false":"true";
        $showRetalSe =  ($vejl_udsalgspris_tekst_se == 0 || $vejl_udsalgspris_tekst_se == "") ?  "false":"true";


        $sqlPt_price = '{"pris":"'.$budget_price_da.'","vis_pris":"'.$showBudgetDa.'","budget":"'.$vejl_udsalgspris_tekst_da.'","vis_budget":"'.$showRetalDa.'","special":"","vis_special":"false"}';
        $sqlPt_price_no = '{"pris":"'.$budget_price_no.'","vis_pris":"'.$showBudgetNo.'","budget":"'.$vejl_udsalgspris_tekst_no.'","vis_budget":"'.$showRetalNo.'","special":"","vis_special":"false"}';
        $sqlPt_price_se = '{"pris":"'.$budget_price_se.'","vis_pris":"'.$showBudgetSe.'","budget":"'.$vejl_udsalgspris_tekst_se.'","vis_budget":"'.$showRetalSe.'","special":"","vis_special":"false"}';


        $sqlPt_price = $this->cleanStr($sqlPt_price);
        $sqlPt_price_no =  $this->cleanStr($sqlPt_price_no);
        $sqlPt_price_se =  $this->cleanStr($sqlPt_price_se);



        $cost_price_da = (int)$cost_price_da;
        $cost_price_no = (int)$cost_price_no;
        $budget_price_da = (int)$budget_price_da;
        $cost_price_se = (int)$cost_price_se;

        $vejl_udsalgspris_tekst_da = str_replace(".", "",$vejl_udsalgspris_tekst_da);

        /****  MAPPING END ****/


        if(sizeof($rsPresent) == 0){
           if($archive == 1){
              ///     $this->error[] = "Varen er slette markeret, der er kryds i archive";
               //    return array("status"=>1,"msg"=>$this->error);
           }





            $sqlLogo = $logoKontainerID;
            $state = "Oprettet";

           // create PRESENT

           $sqlName = $this->generateRandomString(10);
           $present_no = "";
           $logo_size = 2;
           $present_list = "";
           $pt_layout = 5;
            $timestamp = date('Y-m-d H:i:s');
            //$pSql   = "INSERT INTO `present` (`pim_id`, `name`, `nav_name`,`nav_name_no`, `internal_name`, `present_no`,  `logo`, `logo_size`, `price`, `price_group`, `indicative_price`, `price_no`, `price_group_no`, `indicative_price_no`,  `present_list`,  `vendor`, `created_datetime`, `modified_datetime`, `pt_layout`, `pt_img`, `pt_img_small`, `pt_img_small_show`, `kunhos`,  `pt_price`, `pt_price_no`,  `show_to_saleperson`, `show_to_saleperson_no`, `prisents_nav_price`, `prisents_nav_price_no`, `oko_present`)
           //     VALUES ($kontainerID, '$sqlName', '$erp_product_name_da','$erp_product_name_no', '$sqlName', '$present_no', '$sqlLogo', $logo_size, '$cost_price_da', '$budget_price_da', '$vejl_udsalgspris_tekst_da',$cost_price_no, $budget_price_no, '$vejl_udsalgspris_tekst_no', $present_list,  '$vendor',now(),now(), $pt_layout, '$ptimage', '$ptimageSmall', $img2Show, '$kun_hos_gavefabrikken_da', '$sqlPt_price', '$sqlPt_price_no',  $show_in_sales_module, $show_in_sales_module_no, '$cost_price_da', '$cost_price_no', $gave_med_omtanke_da)";
           //  \Dbsqli::setSql2($pSql);

            $mysqli =  \Dbsqli::getConn();
            $stmt = $mysqli->prepare("INSERT INTO `present` (
                       `pim_id`,
                       `name`,
                       `nav_name`,
                       `nav_name_no`,
                       `internal_name`,
                       `present_no`,
                       `logo`,
                       `logo_size`,
                       `price`,
                       `price_group`,
                       `indicative_price`,
                       `price_no`,
                       `price_group_no`,
                       `indicative_price_no`,
                       `present_list`,
                       `vendor`,
                       `created_datetime`,
                       `modified_datetime`,
                       `pt_layout`,
                       `pt_img`,
                       `pt_img_small`,
                       `pt_img_small_show`,
                       `kunhos`,
                       `pt_price`,
                       `pt_price_no`,
                       `show_to_saleperson`,
                       `show_to_saleperson_no`,
                       `prisents_nav_price`,
                       `prisents_nav_price_no`,
                       `oko_present`,
                       `pim_sync_time`
                       ) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,now())");

            $stmt->bind_param("issssssiddsddsssssississsiissi",
                $kontainerID,
                $sqlName,
                $erp_product_name_da,
                $erp_product_name_no,
                $sqlName,
                $present_no,
                $sqlLogo,
                $logo_size,
                $cost_price_da,
                $budget_price_da,
                $vejl_udsalgspris_tekst_da,
                $cost_price_no,
                $budget_price_no,
                $vejl_udsalgspris_tekst_no,
                $present_list,
                $vendor,
                $timestamp,
                $timestamp,
                $pt_layout,
                $ptimage,
                $ptimageSmall,
                $img2Show,
                $kun_hos_gavefabrikken_da,
                $sqlPt_price,
                $sqlPt_price_no,
                $show_in_sales_module,
                $show_in_sales_module_no,
                $cost_price_da,
                $cost_price_no,
                $gave_med_omtanke_da);

            if (!$stmt->execute()) {
                return array("status"=>1,"msg"=>"db create present");
            }

            $tempID = $mysqli->insert_id;

            $shortDescription = "";

            $description_da = base64_encode($description_da);
            $description_en = base64_encode($description_en);
            $description_no = base64_encode($description_no);
            $description_se = base64_encode($description_se);

/*

            $present_descriptionSql1 = " INSERT INTO `present_description` (`present_id`, `language_id`, `caption`, `caption_presentation`, `caption_paper`, `short_description`, `long_description`)
            VALUES ( $tempID, '1', '$product_name_da', '###', '###', '$shortDescription', '$description_da') ";
            \Dbsqli::setSql2($present_descriptionSql1);


            $present_descriptionSql2  = "INSERT INTO `present_description` ( `present_id`, `language_id`, `caption`, `caption_presentation`, `caption_paper`, `short_description`, `long_description`)
            VALUES ( $tempID, '2', '$product_name_en', '###', '###', '$shortDescription', '$description_da')";
            \Dbsqli::setSql2($present_descriptionSql2);

            $present_descriptionSql3 = "INSERT INTO `present_description` ( `present_id`, `language_id`, `caption`, `caption_presentation`, `caption_paper`, `short_description`, `long_description`)
            VALUES ( $tempID, '3', '$product_name_da', '###', '###', '$shortDescription', '$description_en')";
            \Dbsqli::setSql2($present_descriptionSql3);

            $present_descriptionSql4 =  "INSERT INTO `present_description` ( `present_id`, `language_id`, `caption`, `caption_presentation`, `caption_paper`, `short_description`, `long_description`)
            VALUES ( $tempID, '4', '$product_name_no', '###', '###', '$shortDescription', '$description_no')";
            \Dbsqli::setSql2($present_descriptionSql4);


            $present_descriptionSql5 = "INSERT INTO `present_description` ( `present_id`, `language_id`, `caption`, `caption_presentation`, `caption_paper`, `short_description`, `long_description`)
            VALUES ( $tempID, '5', '$product_name_se', '###', '###', '$shortDescription', '$description_se')";
            \Dbsqli::setSql2($present_descriptionSql5);


 */





            // create DESCRIPTION
            $language_id = 1;
            $caption_presentation = '###';
            $caption_paper = '###';
            $stmt = $mysqli->prepare("INSERT INTO `present_description` (`present_id`, `language_id`, `caption`, `caption_presentation`, `caption_paper`, `short_description`, `long_description`) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iisssss", $tempID, $language_id, $product_name_da, $caption_presentation, $caption_paper, $shortDescription, $description_da);
            if (!$stmt->execute()) {
                return array("status"=>1,"msg"=>"db create DESCRIPTION");
            }

            $language_id = 2;
            $caption_presentation = '###';
            $caption_paper = '###';
            $stmt = $mysqli->prepare("INSERT INTO `present_description` (`present_id`, `language_id`, `caption`, `caption_presentation`, `caption_paper`, `short_description`, `long_description`)
            VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iisssss", $tempID, $language_id, $product_name_en, $caption_presentation, $caption_paper, $shortDescription, $description_en);
            if (!$stmt->execute()) {
                return array("status"=>1,"msg"=>"db create DESCRIPTION");
            }



            $language_id = 3;
            $caption_presentation = '###';
            $caption_paper = '###';
            $stmt = $mysqli->prepare("INSERT INTO `present_description` (`present_id`, `language_id`, `caption`, `caption_presentation`, `caption_paper`, `short_description`, `long_description`) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iisssss", $tempID, $language_id, $product_name_en, $caption_presentation, $caption_paper, $shortDescription, $description_en);
            if (!$stmt->execute()) {
                return array("status"=>1,"msg"=>"db create DESCRIPTION");
            }




            $language_id = 4;
            $caption_presentation = '###';
            $caption_paper = '###';
            $stmt = $mysqli->prepare("INSERT INTO `present_description` (`present_id`, `language_id`, `caption`, `caption_presentation`, `caption_paper`, `short_description`, `long_description`)
            VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iisssss", $tempID, $language_id, $product_name_no, $caption_presentation, $caption_paper, $shortDescription, $description_no);
            if (!$stmt->execute()) {
                return array("status"=>1,"msg"=>"db create DESCRIPTION");
            }


            $language_id = 5;
            $caption_presentation = '###';
            $caption_paper = '###';
            $stmt = $mysqli->prepare("INSERT INTO `present_description` (`present_id`, `language_id`, `caption`, `caption_presentation`, `caption_paper`, `short_description`, `long_description`) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iisssss", $tempID, $language_id, $product_name_se, $caption_presentation, $caption_paper, $shortDescription, $description_se);
            if (!$stmt->execute()) {
                return array("status"=>1,"msg"=>"db create DESCRIPTION");
            }



            // create MODEL
           if($type != "Group") {
               $aliasletter = "";
               $fullalias = "";
               $custom_msg1 = "";
               $sampak_items  = "";
               $model_no = "";


               $maxIDRs = \Dbsqli::getSql2(" SELECT max(`model_id`) as maxId FROM `present_model`");
               $maxID = $maxIDRs[0]["maxId"];
               $maxID++;
/*
               $sql1 = "INSERT INTO `present_model` ( `model_id`, `pim_id`, `present_id`, `language_id`, `model_present_no`, `model_name`, `model_no`, `media_path`, `aliasletter`, `fullalias`,  `custom_msg1`, `sampak_items`)
            VALUES ( $maxID, $kontainerID, $tempID, $language_id , '$itemnr', '$erp_product_name_da', '$model_no', '$pack_billede', '$aliasletter', '$fullalias',  '$custom_msg1', '$sampak_items')";
               \Dbsqli::setSql2($sql1);
*/
               // Define your variables here

                $language_id = '1';
                $stmt = $mysqli->prepare("INSERT INTO `present_model` ( `model_id`, `pim_id`, `present_id`, `language_id`, `model_present_no`, `model_name`, `model_no`, `media_path`, `aliasletter`, `fullalias`, `custom_msg1`, `sampak_items`)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("iissssssssss", $maxID, $kontainerID, $tempID, $language_id, $itemnr, $erp_product_name_da, $model_no, $pack_billede, $aliasletter, $fullalias, $custom_msg1, $sampak_items);
               if (!$stmt->execute()) {
                   return array("status"=>1,"msg"=>"db create model");
               }

               $language_id = '2';
               $stmt = $mysqli->prepare("INSERT INTO `present_model` ( `model_id`, `pim_id`, `present_id`, `language_id`, `model_present_no`, `model_name`, `model_no`, `media_path`, `aliasletter`, `fullalias`, `custom_msg1`, `sampak_items`)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
               $stmt->bind_param("iissssssssss", $maxID, $kontainerID, $tempID, $language_id, $itemnr, $erp_product_name_en, $model_no, $pack_billede, $aliasletter, $fullalias, $custom_msg1, $sampak_items);
               if (!$stmt->execute()) {
                   return array("status"=>1,"msg"=>"db create model");
               }

               $language_id = '3';
               $stmt = $mysqli->prepare("INSERT INTO `present_model` ( `model_id`, `pim_id`, `present_id`, `language_id`, `model_present_no`, `model_name`, `model_no`, `media_path`, `aliasletter`, `fullalias`, `custom_msg1`, `sampak_items`)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
               $stmt->bind_param("iissssssssss", $maxID, $kontainerID, $tempID, $language_id, $itemnr, $erp_product_name_en, $model_no, $pack_billede, $aliasletter, $fullalias, $custom_msg1, $sampak_items);
               if (!$stmt->execute()) {
                   return array("status"=>1,"msg"=>"db create model");
               }

               $language_id = '4';
               $stmt = $mysqli->prepare("INSERT INTO `present_model` ( `model_id`, `pim_id`, `present_id`, `language_id`, `model_present_no`, `model_name`, `model_no`, `media_path`, `aliasletter`, `fullalias`, `custom_msg1`, `sampak_items`)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
               $stmt->bind_param("iissssssssss", $maxID, $kontainerID, $tempID, $language_id, $itemnr, $erp_product_name_no, $model_no, $pack_billede, $aliasletter, $fullalias, $custom_msg1, $sampak_items);
               if (!$stmt->execute()) {
                   return array("status"=>1,"msg"=>"db create model");
               }

               $language_id = '5';
               $stmt = $mysqli->prepare("INSERT INTO `present_model` ( `model_id`, `pim_id`, `present_id`, `language_id`, `model_present_no`, `model_name`, `model_no`, `media_path`, `aliasletter`, `fullalias`, `custom_msg1`, `sampak_items`)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
               $stmt->bind_param("iissssssssss", $maxID, $kontainerID, $tempID, $language_id, $itemnr, $erp_product_name_se, $model_no, $pack_billede, $aliasletter, $fullalias, $custom_msg1, $sampak_items);
               if (!$stmt->execute()) {
                   return array("status"=>1,"msg"=>"db create model");
               }

           }


            $mysqli->close();


            // creatin img
            if($img1 != ""){
                \Dbsqli::setSql2("INSERT INTO `present_media` ( `present_id`, `media_path`, `index`,`show_small`,sync_id) VALUES ( $tempID, '$img1', '0',0,$img1ID)");
            }
            if($img2 != ""){
                \Dbsqli::setSql2("INSERT INTO `present_media` ( `present_id`, `media_path`, `index`,`show_small`,sync_id) VALUES ( $tempID, '$img2', '1',$img2Show,$img2ID)");
            }
            if($img3 != ""){
                \Dbsqli::setSql2("INSERT INTO `present_media` ( `present_id`, `media_path`, `index`,`show_small`,sync_id) VALUES ( $tempID, '$img3', '2',0,$img3ID)");
            }
            if($img4 != ""){
                \Dbsqli::setSql2("INSERT INTO `present_media` ( `present_id`, `media_path`, `index`,`show_small`,sync_id) VALUES ( $tempID, '$img4', '3',0,$img4ID)");
            }

        }
        /**** UPDATE ****/
        else {


            if(sizeof($rsPresent) > 1){
                echo "Error: multible pim ids";
            } else {
                $mysqli =  \Dbsqli::getConn();
                $state = "Opdateret";
                $ID = $rsPresent[0]->attributes["id"];

                // NavPriceOverRuleSync

                $res_dk = \Dbsqli::getSql2("SELECT * FROM `navision_item` WHERE no = '".$itemnr."'  and deleted is null and  language_id = 1");
                $res_no = \Dbsqli::getSql2("SELECT * FROM `navision_item` WHERE no = '".$itemnr."'  and deleted is null and  language_id = 4");

                if(sizeof($res_dk) > 0){

                    $cost_price_da              = ($res_dk[0]["standard_cost"] == "" || $res_dk[0]["standard_cost"] == 0) ? $cost_price_da : $res_dk[0]["standard_cost"];
                    $vejl_udsalgspris_tekst_da  = ($res_dk[0]["vejl_pris"]  == "" || $res_dk[0]["vejl_pris"]  == 0) ? $vejl_udsalgspris_tekst_da : $res_dk[0]["vejl_pris"];
                    $budget_price_da               = ($res_dk[0]["unit_price"]  == "" || $res_dk[0]["unit_price"]  == 0) ? $budget_price_da  : $res_dk[0]["unit_price"];
                }
                if(sizeof($res_no) > 0){
                    $cost_price_no              = ($res_no[0]["standard_cost"] == "" || $res_no[0]["standard_cost"] == 0) ? $cost_price_no : $res_no[0]["standard_cost"];
                    $vejl_udsalgspris_tekst_no  = ($res_no[0]["vejl_pris"]  == "" || $res_no[0]["vejl_pris"]  == 0) == "" ? $vejl_udsalgspris_tekst_no : $res_no[0]["vejl_pris"];
                    $budget_price_no               = ($res_no[0]["unit_price"]  == "" || $res_no[0]["unit_price"]  == 0) ? $budget_price_no  : $res_no[0]["unit_price"];
                }

                if($archive == 1){
                     $presentDeleted = 1;
                     $presentActive = 0;
                } else {
                     $presentDeleted = 0;
                     $presentActive = 1;
                }




                $showBudgetDa = ($budget_price_da == 0 || $budget_price_da == "") ? "false":"true";
                $showRetalDa =  ($vejl_udsalgspris_tekst_da == 0 || $vejl_udsalgspris_tekst_da == "") ?  "false":"true";
                $showBudgetNo =  ($budget_price_no == 0 || $budget_price_no == "") ? "false":"true";
                $showRetalNo =  ($vejl_udsalgspris_tekst_no == 0 || $vejl_udsalgspris_tekst_no == "") ?  "false":"true";

                $sqlPt_price = '{"pris":"'.$budget_price_da.'","vis_pris":"'.$showBudgetDa.'","budget":"'.$vejl_udsalgspris_tekst_da.'","vis_budget":"'.$showRetalDa.'","special":"","vis_special":"false"}';
                $sqlPt_price_no = '{"pris":"'.$budget_price_no.'","vis_pris":"'.$showBudgetNo.'","budget":"'.$vejl_udsalgspris_tekst_no.'","vis_budget":"'.$showRetalNo.'","special":"","vis_special":"false"}';

                $sqlPt_price = $this->cleanStr($sqlPt_price);
                $sqlPt_price_no = $this->cleanStr($sqlPt_price_no);


            //------- NavPriceOverRuleSync END --------------------



                // update present
                $stmt = $mysqli->prepare("UPDATE `present` SET 
             `deleted` = ?,
             `active` = ?,
                `pt_price_se` = ?,
                `nav_name_no` = ?,
                `nav_name` =  ?,
                `logo`  =  ?,
                `vendor` =  ?,
                `pt_img` = ?,
                `pt_img_small` = ?,
                `pt_img_small_show` = ?,
                `kunhos` = ?,
                `pt_price` =  ?,
                `pt_price_no` = ?,
                `oko_present` = ?,
                `show_to_saleperson` = ?,
                `show_to_saleperson_no` = ?,
                `prisents_nav_price` = ?,
                `prisents_nav_price_no` = ?,
                `price` = ?,
                `price_group` = ?,
                `price_no` = ?,
                `price_group_no` = ?,                
                `indicative_price` = ?,
                `indicative_price_no` = ?,
                 `pim_sync_time` = now()
              WHERE `id` = ?");

                $stmt->bind_param("iisssssssssssiiisiidiiiss",$presentDeleted, $presentActive ,$sqlPt_price_se, $erp_product_name_no, $erp_product_name_da, $logoKontainerID, $vendor, $ptimage, $ptimageSmall, $img2Show, $kun_hos_gavefabrikken_da, $sqlPt_price, $sqlPt_price_no, $gave_med_omtanke_da, $show_in_sales_module, $show_in_sales_module_no, $cost_price_da, $cost_price_no, $cost_price_da, $budget_price_da, $cost_price_no, $budget_price_no, $vejl_udsalgspris_tekst_da, $vejl_udsalgspris_tekst_no, $ID);

// Define your variables here
// ...
// ...

                if (!$stmt->execute()) {
                    return array("status"=>1,"msg"=>"db update present");
                }
/*
                $insetStrPresent = "
                nav_name_no = '$erp_product_name_no',
                nav_name =  '$erp_product_name_da',
                logo  =  '$logoKontainerID',
                vendor =  '$vendor',
                pt_img = '$ptimage',
                pt_img_small = '$ptimageSmall',
                pt_img_small_show = $img2Show,
                kunhos = '$kun_hos_gavefabrikken_da',
                pt_price =  '$sqlPt_price',
                pt_price_no = '$sqlPt_price_no',
                omtanke = '$gave_med_omtanke_da',
                show_to_saleperson = '$show_in_sales_module',
                show_to_saleperson_no = '$show_in_sales_module_no',
                prisents_nav_price = '$cost_price_da',
                prisents_nav_price_no = '$cost_price_no',
                price = $cost_price_da,
                price_group = $budget_price_da,
                price_no = $cost_price_no,
                price_group_no = $budget_price_no,                
                indicative_price = '$vejl_udsalgspris_tekst_da',
                indicative_price_no = '$vejl_udsalgspris_tekst_no'
             ";

*/




                $description_da = base64_encode($description_da);
                $description_en = base64_encode($description_en);
                $description_no = base64_encode($description_no);
                $description_se = base64_encode($description_se);

                $stmt = $mysqli->prepare("UPDATE `present_description` SET 
                `caption` = ?,
                `long_description` = ?
                WHERE `present_id` = ? AND `language_id` = 1");
                $stmt->bind_param("ssi", $product_name_da, $description_da, $ID);
                if (!$stmt->execute()) {
                    return array("status"=>1,"msg"=>"db update present_description");
                }

                $stmt = $mysqli->prepare("UPDATE `present_description` SET 
                `caption` = ?,
                `long_description` = ?
                WHERE `present_id` = ? AND `language_id` = 2");
                $stmt->bind_param("ssi", $product_name_en, $description_en, $ID);
                if (!$stmt->execute()) {
                    return array("status"=>1,"msg"=>"db update present_description");
                }

                $stmt = $mysqli->prepare("UPDATE `present_description` SET 
                `caption` = ?,
                `long_description` = ?
                WHERE `present_id` = ? AND `language_id` = 3");
                $stmt->bind_param("ssi", $product_name_en, $description_en, $ID);
                if (!$stmt->execute()) {
                    return array("status"=>1,"msg"=>"db update present_description");
                }

                $stmt = $mysqli->prepare("UPDATE `present_description` SET 
                `caption` = ?,
                `long_description` = ?
                WHERE `present_id` = ? AND `language_id` = 4");
                $stmt->bind_param("ssi", $product_name_no, $description_no, $ID);
                if (!$stmt->execute()) {
                    return array("status"=>1,"msg"=>"db update present_description");
                }

                $stmt = $mysqli->prepare("UPDATE `present_description` SET 
                `caption` = ?,
                `long_description` = ?
                WHERE `present_id` = ? AND `language_id` = 5");
                $stmt->bind_param("ssi", $product_name_se, $description_se, $ID);
                if (!$stmt->execute()) {
                    return array("status"=>1,"msg"=>"db update present_description");
                }





                /*

                                $insetStrPresent = " caption = '$product_name_da', long_description = '$description_da'";
                                $sqlUpdateDesciptionDa = "update present_description set " . $insetStrPresent . " where present_id = " . $ID . " and language_id = 1";
                                \Dbsqli::setSql2($sqlUpdateDesciptionDa);

                                $insetStrPresent = " caption = '$product_name_en', long_description = '$description_en'";
                                $sqlUpdateDesciptionEn = "update present_description set " . $insetStrPresent . " where present_id = " . $ID . " and language_id = 2";
                                \Dbsqli::setSql2($sqlUpdateDesciptionEn);

                                $insetStrPresent = " caption = '$product_name_no', long_description = '$description_no'";
                                $sqlUpdateDesciptionNo = "update present_description set " . $insetStrPresent . " where present_id = " . $ID . " and language_id = 4";
                                \Dbsqli::setSql2($sqlUpdateDesciptionNo);

                                $insetStrPresent = " caption = '$product_name_se', long_description = '$description_se'";
                                $sqlUpdateDesciptionSe = "update present_description set " . $insetStrPresent . " where present_id = " . $ID . " and language_id = 5";
                                \Dbsqli::setSql2($sqlUpdateDesciptionSe);
                */



                // update MODEL
                if($type != "Group") {
                   $stmt = $mysqli->prepare("UPDATE `present_model` SET 
                   `model_present_no` = ?,
                   `model_name` = ?,
                   `media_path` = ?
                   WHERE `present_id` = ? AND `language_id` = 1");
                   $stmt->bind_param("sssi", $itemnr, $erp_product_name_da, $pack_billede, $ID);
                    if (!$stmt->execute()) {
                        return array("status"=>1,"msg"=>"db update present_model");
                    }

                    $stmt = $mysqli->prepare("UPDATE `present_model` SET 
                   `model_present_no` = ?,
                   `model_name` = ?,
                   `media_path` = ?
                   WHERE `present_id` = ? AND `language_id` = 2");
                    $stmt->bind_param("sssi", $itemnr, $erp_product_name_en, $pack_billede, $ID);
                    if (!$stmt->execute()) {
                        return array("status"=>1,"msg"=>"db update present_model");
                    }

                    $stmt = $mysqli->prepare("UPDATE `present_model` SET 
                   `model_present_no` = ?,
                   `model_name` = ?,
                   `media_path` = ?
                   WHERE `present_id` = ? AND `language_id` = 4");
                    $stmt->bind_param("sssi", $itemnr, $erp_product_name_no, $pack_billede, $ID);
                    if (!$stmt->execute()) {
                        return array("status"=>1,"msg"=>"db update present_model");
                    }

                    $stmt = $mysqli->prepare("UPDATE `present_model` SET 
                   `model_present_no` = ?,
                   `model_name` = ?,
                   `media_path` = ?
                   WHERE `present_id` = ? AND `language_id` = 5");
                    $stmt->bind_param("sssi", $itemnr, $erp_product_name_se, $pack_billede, $ID);
                    if (!$stmt->execute()) {
                        return array("status"=>1,"msg"=>"db update present_model");
                    }




                /*
                    $insetStrModel = "model_present_no = '$itemnr', model_name = '$erp_product_name_da',  media_path = '$pack_billede'";
                    $sqlUpdateModelDa = "update present_model set " . $insetStrModel . "  where present_id = " . $ID . " and language_id = 1";
                    \Dbsqli::setSql2($sqlUpdateModelDa);

                    $insetStrModel = "model_present_no = '$itemnr', model_name = '$erp_product_name_en',  media_path = '$pack_billede'";
                    $sqlUpdateModelEn = "update present_model set " . $insetStrModel . "  where present_id = " . $ID . " and language_id = 2";
                    \Dbsqli::setSql2($sqlUpdateModelEn);

                    $insetStrModel = "model_present_no = '$itemnr', model_name = '$erp_product_name_no',  media_path = '$pack_billede'";
                    $sqlUpdateModelNo = "update present_model set " . $insetStrModel . "  where present_id = " . $ID . " and language_id = 4";
                    \Dbsqli::setSql2($sqlUpdateModelNo);

                    $insetStrModel = "model_present_no= '$itemnr', model_name = '$erp_product_name_se',  media_path = '$pack_billede'";
                    $sqlUpdateModelSe = "update present_model set " . $insetStrModel . "  where present_id = " . $ID . " and language_id = 5";
                    \Dbsqli::setSql2($sqlUpdateModelSe);
                */
                }

                $mysqli->close();

                // update image
                $deactivateImgSql = "UPDATE `present_media` set `present_id` = -$ID WHERE present_id = " . $ID;
                \Dbsqli::setSql2($deactivateImgSql);
                if ($img1 != "") {
                    \Dbsqli::setSql2("INSERT INTO `present_media` ( `present_id`, `media_path`, `index`,`show_small`,sync_id) VALUES ( $ID, '$img1', '0',0,$img1ID)");
                }
                if ($img2 != "") {
                    \Dbsqli::setSql2("INSERT INTO `present_media` ( `present_id`, `media_path`, `index`,`show_small`,sync_id) VALUES ( $ID, '$img2', '1',$img2Show,$img2ID)");
                }
                if ($img3 != "") {
                    \Dbsqli::setSql2("INSERT INTO `present_media` ( `present_id`, `media_path`, `index`,`show_small`,sync_id) VALUES ( $ID, '$img3', '2',0,$img3ID)");
                }
                if ($img4 != "") {
                    \Dbsqli::setSql2("INSERT INTO `present_media` ( `present_id`, `media_path`, `index`,`show_small`,sync_id) VALUES ( $ID, '$img4', '3',0,$img4ID)");
                }
                // updatere model itemno




            }


                if ($itemnr != "") {
                    $res_dk = \Dbsqli::getSql2("SELECT * FROM `navision_item` WHERE no = '" . $itemnr . "'  and deleted is null and  language_id = 1");
                    $res_no = \Dbsqli::getSql2("SELECT * FROM `navision_item` WHERE no = '" . $itemnr . "'   and deleted is null and  language_id = 4");

                    if (sizeof($res_dk) > 0 || sizeof($res_no) > 0) {
                        // update item nunbers in selected presents
                        gavevalgPresentModel::updateChildItemnr($kontainerID, $itemnr);
                        // opdatere
                        if (strtolower(substr($itemnr, 0, 3)) == "sam") {
                            $sql = "SELECT distinct (model_id)  FROM present_model where present_id in( SELECT id FROM `present` WHERE `pim_id` = $kontainerID and shop_id = 0 and `copy_of` = 0) ";
                            $presentIDRes = \Dbsqli::getSql2($sql);

                            gavevalgPresentModel::updateSamItem($presentIDRes[0]["model_id"], $itemnr);
                        }

                    }
                }
         
        }


        if($type == "Group") {

            // husk tjek der skal være modeller eller mega fejl og stop

            $valideStatus = $this->validateGroupItemnr($att);

            if($valideStatus !== true){
                $this->error["varenr"] = $valideStatus;
                return array("status"=>1,"msg"=>$this->error);
            }

            $presentID =  $rsPresent[0]->attributes["id"];
            $pimList = $this->handleGroupModel($kontainerID,$presentID,$att);

//            $this->removeInactiveModels($pimList,$presentID,$kontainerID);

        }
        $this->handleArchive($kontainerID,$archive);



        return ["status"=>0,"msg"=>$state];
      //  var_dump($data->data);
    }
    private function handleArchive($pimID,$state){

            $sql = "select id from present where copy_of= 0 and pim_id=".$pimID;
            $rs = \Dbsqli::getSql2($sql);
            if(sizeof($rs) > 0){
                if($state){
                    $updateSql = "update present set deleted = 1,active = 0 where id =".$rs[0]["id"];
                } else {
                    $updateSql = "update present set deleted = 0,active = 1 where id =".$rs[0]["id"];
                }
                \Dbsqli::setSql2($updateSql);
            }
    }


    private function removeInactiveModels($list,$presentID,$kontainerID){
        $sql = "UPDATE `present_model` set present_id = -".$presentID.", pim_id = -".$kontainerID." WHERE `pim_id` not in (".implode(",",$list).") and `present_id` = ".$presentID;
        \Dbsqli::setSql2($sql);
    }
    private function validateGroupItemnr($data)
    {
        $kontainer = new kontainerCom;


        $group_product_nos = $data->group_product_nos->value ?? false ? $data->group_product_nos->value : "";
        if($group_product_nos == ""){
            return "Manglende varenr";
        }
        $modelList = explode("\n", $group_product_nos);
        foreach ($modelList as $itemnr) {
            if (trim($itemnr) == "") continue;
            $res = $kontainer->getDataOnItemnr(trim($itemnr));
            $res = json_decode($res);

            if(sizeof($res->data) > 1){
              return "Varenummer eksisterer flere gange: ".$itemnr;
            }
            $r = $res->data[0]->id ?? false ? 1:0;
            if($r == 0 ){
                return "Varenummer eksisterer ikke: ".$itemnr;
            }
        }
        return true;
    }
    private function priceFromNAVSync($KontainerID,$itemno)
    {

    }

    private function createImg($img){
        $content = file_get_contents($img);

        //Store in the filesystem.
        $randomnr = $this->generateRandomString();
        $path = $randomnr.".jpg";
        $fp = fopen("./views/media/user/".$path, "w");
        fwrite($fp, $content);
        fclose($fp);
        return $randomnr;
    }
    private function createPTImg($filename,$path){
        $content = file_get_contents($path);
        //Store in the filesystem.

        $fp = fopen($_SERVER["DOCUMENT_ROOT"]."/fjui4uig8s8893478/".$filename, "w");
        fwrite($fp, $content);
        fclose($fp);

    }
    private function createPackPTImg($path,$kontainerID){
        /*
        $content = file_get_contents($path);
        //Store in the filesystem.
        $filename.=".jpg";
        $fp = fopen($_SERVER["DOCUMENT_ROOT"]."/gavefabrikken_backend/views/media/type/".$filename, "w");
        fwrite($fp, $content);
        fclose($fp);
        */
        $filename = $kontainerID."_".$this->generateRandomString(10);

        $source = $path;
        $target = $_SERVER["DOCUMENT_ROOT"]."/gavefabrikken_backend/views/media/type/".$filename.".jpg";
        $this->createSmallImg(400,$source,$target);
        return $filename;

    }
    private function createSmallImg($newWidth,$source,$target){

        $image = @imagecreatefromjpeg($source);
        if (!$image) {
            return null;
        }
        if($newWidth == ""){
            $new_width = imagesx($image);
            $new_height = imagesy($image);


        } else {
            $width = imagesx($image);
            $height = imagesy($image);
            $ration = $width / $height;
            $new_height = $newWidth / $ration;
            $new_height = (int) $new_height;
            $new_width = $newWidth;

        }

        $imageResized = imagescale($image, $new_width, $new_height);
        $write = imagejpeg($imageResized, $target);
        imagedestroy($imageResized);
        return $write;

    }
    private function getImgUrl($id){
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://gavefabrikken.kontainer.com/jsonapi/v2/dam/files/'.$id.'/cdn');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "{\n  \"data\": {\n    \"type\": \"cdn\"\n  }\n}");

        $headers = array();
        $headers[] = 'Accept: application/vnd.api+json';
        $headers[] = 'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiI1NDMiLCJqdGkiOiI3ZTg2ZThhYWU3NjkwZjcxOTk4ZGMwYzNjYTQ0MzBiOTEyNTU5MDk4ZjNjZGI1YjRiNWYyZjU0NmZlNjYzNWE0M2M4NGUyYjllN2YwMGU5ZiIsImlhdCI6MTc0MDY1MTM1MS41ODE1OTUsIm5iZiI6MTc0MDY1MTM1MS41ODE1OTgsImV4cCI6MTgwMzcyMzMyMi4wMjIzNzIsInN1YiI6IjMxNDc5Iiwic2NvcGVzIjpbXX0.P6cA4NwJoEzSchfvLQY_cjHd_g4j49o1iUzeZ85P9Jf6OZix5lYzVfJieqbUIA6sZaNUHbrklIRBuKNV8jDc06sJXE0SVpB8oJUvWAQQPqxh7V-he1ExxVi70USE8lLRVdO1MRGh8ELIVq1wWndf-1_F_HKKJFzB3afEQhLc4evrxu8MZmVtEGmryFUorVnzAy8VNO7XhpW6a9evLggOtnocJzxF_9kGmpCpPvxpkTFJ9ThLlU0ol5mciNjUUm4upH0Z3nkUcFoxCAXrb1edG3j_Fjkily6qkFqIyQUnCBJSZXuWvxA5Xb2qIXmRpD1jbgLvyJJn4QybFaj3401NBb_QtTCcCorzMmqcq5krORpq-q2nzXS76dzTgRdgKCL3o87o0wjrY5X_LIXxsvBr4PjkrD-RUSxahpsq44SFqGDg74Ue56XtZVnbMGkA9A86bKyZZRZcRnwlWwzX9EiDopGYUHdV05HJtucil1wZ0RSbkwwtCQIz1nPqEeWqM7igVoaG0DSPTHAlVkb4PeI3zgqL5sHQioVflwQCJmlVtK5cylq-FJXykWW_fSk5XFmy1ihJgUVqxvtSZjWUJpEfNGJzJnWQTSYTmnJdFb2c9qr15eP-ZOG6RR7td71XICbZXi3zKYqlJ6YdYiy1RBdaz3ThuQ5vLmpANLblyB9qUOI';
        //$headers[] = 'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiI1NDMiLCJqdGkiOiI1M2U4NzA2NDY4MzNjNTg0NTVjZGZiOTUxYmM2NzIxZTU5MTAwZmEzYjNlYWM5MjExOTQ0YWIwOWIzYjQxZjE1MGY5NjdiNTA5MzQzMmQzOCIsImlhdCI6MTY3NzUwNjIyOS45OTkyLCJuYmYiOjE2Nzc1MDYyMjkuOTk5MjAyLCJleHAiOjE3NDA1NzgyMTUuMDA1OTk4LCJzdWIiOiIzMTQ3OSIsInNjb3BlcyI6W119.YN1Rz6lar3-dIWKLe_gN6rm-5MB85c1nt8iYNTiup-gPXyoGW_8I2Da-Ju-jj_Srr9cDuYv_3C6WerGxqLarVQ0_ztdgNwh69SjsBcrmnHedaaHgM7hhV9C4gWCBQN0cIHcNTAYL5mnpYGuh-iot1uIv0NoK-cDquj23qwIWwgh-CgzIzFafXpTb_nHn3-QfXvAJTO2l3zbnjLRGiO2-7sl3CVbutJWUGaoiRyYM9e9aAmIr1H25RJRhI24mBoAPoiiFr0JCVv8MPRWGgd5sC0WUcFrf-jYvx1bu_wiwQ8PuQdJUS0ibpgJffY4z6G_zDhcD3_1mhk_u7k45SUaCvu1q1aeSK3YScYlR-_iCckN53M7AgKK0ScnF7UHtZYFecXG5Mb2qMoUFcnHTQG8tm4qpFv5pM2TLYyenzYlDVy4I_1VNAe9xV7d03BrUx1ldO3BwVKcJJQXJtJUv6VUXweb2FUpS9sm7Guz_USYcd2koFFPTpRteNOippnqorzYyMgn5tCuKHGc4SLN2X9fuNVVanVImKkA3Y8KLIW-VbX2CyLs_B4aHOy4Z0RrwahNRYLxIPXPJ_1FEgY_SZqYecIh90mt7AHgUrmqB4UHqoPhV3QxX3TQtgDPou3-U_2ZhN24djM6yNboDnWK61erSaUn7sIdgP3MmGguf9BMR7Ok';

        $headers[] = 'Content-Type: application/vnd.api+json';
        $headers[] = 'X-Csrf-Token: ';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);

        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        return $result;
    }
    private function getKontainerLogoID($id)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://gavefabrikken.kontainer.com/jsonapi/v2/pim/channels/10615/items/'.$id);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

        $headers = array();
        $headers[] = 'Accept: application/vnd.api+json';
        $headers[] = 'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiI1NDMiLCJqdGkiOiI3ZTg2ZThhYWU3NjkwZjcxOTk4ZGMwYzNjYTQ0MzBiOTEyNTU5MDk4ZjNjZGI1YjRiNWYyZjU0NmZlNjYzNWE0M2M4NGUyYjllN2YwMGU5ZiIsImlhdCI6MTc0MDY1MTM1MS41ODE1OTUsIm5iZiI6MTc0MDY1MTM1MS41ODE1OTgsImV4cCI6MTgwMzcyMzMyMi4wMjIzNzIsInN1YiI6IjMxNDc5Iiwic2NvcGVzIjpbXX0.P6cA4NwJoEzSchfvLQY_cjHd_g4j49o1iUzeZ85P9Jf6OZix5lYzVfJieqbUIA6sZaNUHbrklIRBuKNV8jDc06sJXE0SVpB8oJUvWAQQPqxh7V-he1ExxVi70USE8lLRVdO1MRGh8ELIVq1wWndf-1_F_HKKJFzB3afEQhLc4evrxu8MZmVtEGmryFUorVnzAy8VNO7XhpW6a9evLggOtnocJzxF_9kGmpCpPvxpkTFJ9ThLlU0ol5mciNjUUm4upH0Z3nkUcFoxCAXrb1edG3j_Fjkily6qkFqIyQUnCBJSZXuWvxA5Xb2qIXmRpD1jbgLvyJJn4QybFaj3401NBb_QtTCcCorzMmqcq5krORpq-q2nzXS76dzTgRdgKCL3o87o0wjrY5X_LIXxsvBr4PjkrD-RUSxahpsq44SFqGDg74Ue56XtZVnbMGkA9A86bKyZZRZcRnwlWwzX9EiDopGYUHdV05HJtucil1wZ0RSbkwwtCQIz1nPqEeWqM7igVoaG0DSPTHAlVkb4PeI3zgqL5sHQioVflwQCJmlVtK5cylq-FJXykWW_fSk5XFmy1ihJgUVqxvtSZjWUJpEfNGJzJnWQTSYTmnJdFb2c9qr15eP-ZOG6RR7td71XICbZXi3zKYqlJ6YdYiy1RBdaz3ThuQ5vLmpANLblyB9qUOI';
        //$headers[] = 'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiI1NDMiLCJqdGkiOiI1M2U4NzA2NDY4MzNjNTg0NTVjZGZiOTUxYmM2NzIxZTU5MTAwZmEzYjNlYWM5MjExOTQ0YWIwOWIzYjQxZjE1MGY5NjdiNTA5MzQzMmQzOCIsImlhdCI6MTY3NzUwNjIyOS45OTkyLCJuYmYiOjE2Nzc1MDYyMjkuOTk5MjAyLCJleHAiOjE3NDA1NzgyMTUuMDA1OTk4LCJzdWIiOiIzMTQ3OSIsInNjb3BlcyI6W119.YN1Rz6lar3-dIWKLe_gN6rm-5MB85c1nt8iYNTiup-gPXyoGW_8I2Da-Ju-jj_Srr9cDuYv_3C6WerGxqLarVQ0_ztdgNwh69SjsBcrmnHedaaHgM7hhV9C4gWCBQN0cIHcNTAYL5mnpYGuh-iot1uIv0NoK-cDquj23qwIWwgh-CgzIzFafXpTb_nHn3-QfXvAJTO2l3zbnjLRGiO2-7sl3CVbutJWUGaoiRyYM9e9aAmIr1H25RJRhI24mBoAPoiiFr0JCVv8MPRWGgd5sC0WUcFrf-jYvx1bu_wiwQ8PuQdJUS0ibpgJffY4z6G_zDhcD3_1mhk_u7k45SUaCvu1q1aeSK3YScYlR-_iCckN53M7AgKK0ScnF7UHtZYFecXG5Mb2qMoUFcnHTQG8tm4qpFv5pM2TLYyenzYlDVy4I_1VNAe9xV7d03BrUx1ldO3BwVKcJJQXJtJUv6VUXweb2FUpS9sm7Guz_USYcd2koFFPTpRteNOippnqorzYyMgn5tCuKHGc4SLN2X9fuNVVanVImKkA3Y8KLIW-VbX2CyLs_B4aHOy4Z0RrwahNRYLxIPXPJ_1FEgY_SZqYecIh90mt7AHgUrmqB4UHqoPhV3QxX3TQtgDPou3-U_2ZhN24djM6yNboDnWK61erSaUn7sIdgP3MmGguf9BMR7Ok';
        $headers[] = 'X-Csrf-Token: ';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);

       // var_dump($result);
         // return array("status"=>1,"msg"=>$this->error);
        return $result;
    }
    public function doUpdateImg($kontainerImgID,$kontainerID,$index){
        // tjek om varen eksistere
        $sql = "select id from present where pim_id=".$kontainerID." and copy_of = 0";
        $presentRS = \Dbsqli::getSql2($sql);
        if(sizeof($presentRS) == 0){
            return true;
        } else {
            //  gaven er oprettet, tjek om billede eksistere
            $sql = "select * from present_media where present_id = ( select id from present where pim_id =".$kontainerID." and copy_of =0) and `index` = ".$index." and sync_id =". $kontainerImgID;
            $mediaRS = \Dbsqli::getSql2($sql);
            return sizeof($mediaRS) == 0 ? true:$mediaRS[0]["media_path"];
        }
    }

    private function validateImg($obj){
     //   $size = $obj->dimensions->fullscreen->size*1;
        return (strtolower(pathinfo($obj->file_name, PATHINFO_EXTENSION)) == "jpg" )  ? true : false;
//&& $size < 4000000


    }
    private function generateRandomString($length = 30) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        return $randomString;
    }
    private function cleanStr($str)
    {
        $cleaned_string = preg_replace('/\s+/', '', $str);
        return trim($cleaned_string);
    }


}
