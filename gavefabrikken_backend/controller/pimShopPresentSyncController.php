<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class pimShopPresentSyncController Extends baseController {

   private $isInSync = 1;


   public function Index() {
     //  $this->registry->template->show('pimPresentSync_view');
     echo "index";
   }


   public function loadSyncPresentOnShop()
   {
       $result = [];
       $shopID =  is_int( $_GET["shopID"]*1) ? ($_GET["shopID"]*1): die("{status:0}");
       $sql = "select present_id from shop_present where shop_id = '".$shopID."' and active = 1 and is_deleted = 0 and active ORDER BY `shop_present`.`index_` ASC ";
       $result = Dbsqli::getSql2($sql);
       response::success(json_encode($result));

   }


   public function copyModelFromMaster()
   {
       $presentID =  is_int( $_GET["id"]*1) ? ($_GET["id"]*1): die("{status:0}");
       // crate
       $sql = "SELECT * FROM `present_model` WHERE `model_id` = ".$_GET["orgmodel"];
       $masterDataRs = Dbsqli::getSql2($sql);
       $maxModelIDRs = Dbsqli::getSql2("select max(model_id) as h from present_model");
       $maxModelID = $maxModelIDRs[0]["h"]+1;
       foreach($masterDataRs as $masterItem){
          
            $sql = "INSERT INTO present_model
            ( `model_id`, `original_model_id`, `present_id`, `language_id`, `model_present_no`, `model_name`, `model_no`, `media_path`, `moms`)
           VALUES
            (
            '".$maxModelID."',
            '".$_GET["orgmodel"]."',
            '".$presentID."',
            '".$masterItem["language_id"]."',
            '".trimgf($masterItem["model_present_no"])."',
            '".$masterItem["model_name"]."',
            '".$masterItem["model_no"]."',
            '".$masterItem["media_path"]."',
            '".$masterItem["moms"]."'
            )";
            Dbsqli::setSql2($sql);
       }
       $result = [];
      response::success(json_encode($result));
   }


   public function updatePresent(){
        $presentID =  is_int( $_GET["id"]*1) ? ($_GET["id"]*1): die("{status:0}");
        $masterData = $this->loadMasterData($presentID);
        $targetData = $this->loadPresentData($presentID);
        // opdatere gaver med omtank
        $this->updateGmo($masterData["oko_present"][0]["oko_present"],$presentID);
        // opdatere billeder p� gaven
        $this->updatePresentMedia($masterData["presentMedia"],$presentID);
        // Opdatere logo p� gaven
        $this->updateLogo($masterData["present"][0]["logo"],$presentID);
        // opdatere gavens tekster
        $this->updatePresentText($masterData["presentDescription"],$presentID);
        // opdatere modeller
        $this->updatePresentModel($masterData["model"],$presentID);
        // opdatere presentation quick code
        $this->updatePresentationAll($presentID);


        $result = [];
        response::success(json_encode($result));
   }

   public function loadSyncStatus(){
        $presentID =  is_int( $_GET["id"]*1) ? ($_GET["id"]*1): die("{status:0}");

        $sql = "select nav_name,lock_for_sync from present where id=".$presentID;
        $presentNameRS = Dbsqli::getSql2($sql);

        //    isInSync skal ligge bagerst, da under sync bliver det bestemt om en gave totalt er i sync eller ej
        $sync = $this->getSyncStatus( $this->loadMasterData($presentID) , $this->loadPresentData($presentID));
        $result[] = array(
                "id"=> $presentID,
                "name"=> $presentNameRS[0]["nav_name"],
                "lock"=> $presentNameRS[0]["lock_for_sync"],
                "sync" => $sync,
                "isInSync" => $this->isInSync
        );
        response::success(json_encode($result));
   }



   public function singleLogoUpdate()
   {
        $presentID =  is_int( $_GET["id"]*1) ? ($_GET["id"]*1): die("{status:0}");
        $masterData = $this->loadMasterData($presentID);
        $this->updateLogo($masterData["present"][0]["logo"],$presentID);
        $result = [];
        response::success(json_encode($result));
   }

   public function singleGmoUpdate()
   {
        $presentID =  is_int( $_GET["id"]*1) ? ($_GET["id"]*1): die("{status:0}");
        $masterData = $this->loadMasterData($presentID);
        $this->updateGmo($masterData["oko_present"][0]["oko_present"],$presentID);
        $result = [];
        response::success(json_encode($result));
   }

   public function updatePresentationAll($id)
   {
        $presentID =  is_int( $id*1) ? ($id*1): die("{status:0}");
        $masterData = $this->loadMasterData($presentID);
        $present= Present::find($presentID);

        $present->pt_img = $masterData["presentation"]["pt_img"];
        $present->pt_img_small = $masterData["presentation"]["pt_img_small"];
        $present->pt_layout = $masterData["presentation"]["pt_layout"];
        $present->kunhos = $masterData["presentation"]["kunhos"];
        $present->pt_price = $masterData["presentation"]["pt_price"];
        $present->pt_price_no = $masterData["presentation"]["pt_price_no"];
        $present->save();
      
   }


   public function updatePresentation($id="",$target="")
   {
        $id = $id == "" ? $_GET["id"] : $id;
        $presentID =  is_int( $id*1) ? ($id*1): die("{status:0}");
        $target = $target == "" ? $_GET["target"] : $target;
        $masterData = $this->loadMasterData($presentID);
        $present= Present::find($presentID);

        $present->$target = $masterData["presentation"][$target];
        $present->save();
        System::connection()->commit();
        System::connection()->transaction();
        $result = [];
        response::success(json_encode($result));


   }


   public function singleImagesUpdate()
   {
        $presentID =  is_int( $_GET["id"]*1) ? ($_GET["id"]*1): die("{status:0}");
        $masterData = $this->loadMasterData($presentID);
        $this->updatePresentMedia($masterData["presentMedia"],$presentID);
        $result = [];
        response::success(json_encode($result));
   }
   public function singleTextUpdate()
   {
        $presentID =  is_int( $_POST["id"]*1) ? ($_POST["id"]*1): die("{status:0}");
        $masterData = $this->loadMasterData($presentID);
        $this->updateSinglePresentModel($masterData["presentDescription"],$_POST);
        $result = [];
        response::success(json_encode($result));
   }
   public function updateModelField()
   {
        $presentID =  is_int( $_POST["id"]*1) ? ($_POST["id"]*1): die("{status:0}");
        //$masterData = $this->loadMasterData($presentID);
        $sql = "select ".$_POST["field"]." from present_model where model_id = '".$_POST["orgmodel"]."' and language_id = ".$_POST["lang"];
        $masterRS = Dbsqli::getSql2($sql);
        $masterValue = $masterRS[0][$_POST["field"]];

        $sql = "update present_model set ".$_POST["field"]." = '".addslashes($masterValue)."'
                    where
                    original_model_id = '".$_POST["orgmodel"]."' and
                    present_id = '".$_POST["id"]."' and
                    language_id = ".$_POST["lang"];
        Dbsqli::setSql2($sql);
        $result = [];
        response::success(json_encode($result));

 }



 // ************* Get Sync statis for present *********************
   private function loadMasterData($presentID)
   {

        $sql = "select copy_of from present where id=".$presentID;
        $masterRS = Dbsqli::getSql2($sql);
        $masterID = $masterRS[0]["copy_of"];
        return $this->loadPresentData($masterID);
   }

   private function loadPresentData($presentID)
   {
        $syncData = [];

        //  billeder
        $sql = "select * from present_media where present_id = ".$presentID." ORDER BY `present_media`.`index` ASC " ;
        $presentMedia = Dbsqli::getSql2($sql);
        $syncData["presentMedia"] = $presentMedia;
        // beskrivelser
        $sql = "select * from present_description where present_id =  $presentID";
        $presentDescription = Dbsqli::getSql2($sql);
        $syncData["presentDescription"] = $presentDescription;
        // logo/present
        $sql = "select logo from present where id =  $presentID";
        $present = Dbsqli::getSql2($sql);
        $syncData["present"] = $present;
        // �ko
        $sql = "select oko_present from present where id =  $presentID";
        $oko_present = Dbsqli::getSql2($sql);
        $syncData["oko_present"] = $oko_present;
        // presentation
        $sql = "select * from present where id =  $presentID";
        $present = Dbsqli::getSql2($sql);
        $syncData["presentation"] = $present[0];

        // modeller, henter pr presente
        $sql = "select distinct model_id from present_model where present_id = ". $presentID;
        $modelListRs = Dbsqli::getSql2($sql);

        foreach($modelListRs as $modelID){
            $sql = "select * from present_model where is_deleted = 0 and active= 0 and  present_id = ". $presentID. " and model_id=".$modelID["model_id"];
            $modelRs = Dbsqli::getSql2($sql);
            $syncData["model"][$modelID["model_id"]] = $modelRs;
        }
        return $syncData;
   }
   private function getSyncStatus($source,$target){
      $isSync = [];
        $numberOfLanguages = 5;
        // present - logo
        $isSync["present"] =  sizeofgf(array_diff($source["present"][0],$target["present"][0])) == 0 ? 1:0;
        $isSync["present"] == 0 ? $this->isInSync = 0:"";

        // present - oko_present
        $isSync["oko_present"] =  sizeofgf(array_diff($source["oko_present"][0],$target["oko_present"][0])) == 0 ? 1:0;
        $isSync["oko_present"] == 0 ? $this->isInSync = 0:"";
        // presentation
        $isSync["presentation"]["pt_img"] = $source["presentation"]["pt_img"] == $target["presentation"]["pt_img"] ? 1:0;
        $isSync["presentation"]["pt_img"] == 0 ? $this->isInSync = 0:"";

        $isSync["presentation"]["pt_img_small"] = $source["presentation"]["pt_img_small"] == $target["presentation"]["pt_img_small"] ? 1:0;
        $isSync["presentation"]["pt_img_small"] == 0 ? $this->isInSync = 0:"";

        $isSync["presentation"]["pt_layout"] = $source["presentation"]["pt_layout"] == $target["presentation"]["pt_layout"] ? 1:0;
        $isSync["presentation"]["pt_layout"] == 0 ? $this->isInSync = 0:"";

        $isSync["presentation"]["kunhos"] = $source["presentation"]["kunhos"] == $target["presentation"]["kunhos"] ? 1:0;
        $isSync["presentation"]["kunhos"] == 0 ? $this->isInSync = 0:"";

        $isSync["presentation"]["pt_price"] = $source["presentation"]["pt_price"] == $target["presentation"]["pt_price"] ? 1:0;
        $isSync["presentation"]["pt_price"] == 0 ? $this->isInSync = 0:"";

        $isSync["presentation"]["pt_price_no"] = $source["presentation"]["pt_price_no"] == $target["presentation"]["pt_price_no"] ? 1:0;
        $isSync["presentation"]["pt_price_no"] == 0 ? $this->isInSync = 0:"";


        // presentMedia
        // test i samme size
        $isSync["presentMedia"] =  (sizeofgf($source["presentMedia"]) == sizeofgf($target["presentMedia"])) ? 1:0;
        if($isSync["presentMedia"] == 1){
            $presentMediaIsSync = true;
            for($i=0;$i<sizeofgf($source["presentMedia"]);$i++){
                if($source["presentMedia"][$i]["media_path"] != $target["presentMedia"][$i]["media_path"]){
                    $presentMediaIsSync = false;
                }
            }
            $isSync["presentMedia"] = $presentMediaIsSync == true ? 1:0;
            $isSync["presentMedia"] == 0 ? $this->isInSync = 0:"";

        } else {
            $isSync["presentMedia"] = 0;
            $this->isInSync = 0;

        }
        // presentDescription
        //  $isSync["presentMedia"]
        $presentDescriptionSyncSucces = true;
        if(sizeofgf($target["presentDescription"]) != $numberOfLanguages ){
           $presentDescriptionSyncSucces = false;
           $this->isInSync = 0;

        }
        if($presentDescriptionSyncSucces== true ){
            for($i=0;$i < ($numberOfLanguages);$i++){
                // tjek samme sprog
                if(($source["presentDescription"][$i]["language_id"] == ($i+1)) && ($target["presentDescription"][$i]["language_id"] == ($i+1)) ){
                    $isSync["presentDescription"][$i]["language"] = $this->languageMatrix($source["presentDescription"][$i]["language_id"]);
                    $isSync["presentDescription"][$i]["language_id"] = $source["presentDescription"][$i]["language_id"];
                    // tjek  caption
                    $isSync["presentDescription"][$i]["caption"] =  $source["presentDescription"][$i]["caption"]  ==   $target["presentDescription"][$i]["caption"] ? 1:0;
                    // tjek  short_description
                    $isSync["presentDescription"][$i]["short_description"] =  $source["presentDescription"][$i]["short_description"]  ==   $target["presentDescription"][$i]["short_description"] ? 1:0;
                    // tjek  long_description
                    $isSync["presentDescription"][$i]["long_description"] =  $source["presentDescription"][$i]["long_description"]  ==   $target["presentDescription"][$i]["long_description"] ? 1:0;
                } else {
                   $presentDescriptionSyncSucces = false;
                   $this->isSync = 0;
                }
                if($isSync["presentDescription"][$i]["caption"] == 0) { $this->isInSync = 0;  }
                if($isSync["presentDescription"][$i]["short_description"] == 0) { $this->isInSync = 0;  }
                if($isSync["presentDescription"][$i]["long_description"] == 0) { $this->isInSync = 0;  }
            }

        }

        $isSync["model"] = $this->getModelSyncStatus($source["model"],$target["model"]);

        return $isSync;
   }
   private function getModelSyncStatus($masterModel,$targetModel)
   {
       $result = [];
       // print_R($masterModel);
        //print_R($targetModel);

        // tjek overordnet om modeler er ens
        $lang = 5;
        foreach (array_keys($masterModel)  as $masterKey){
            $temp = [];
            $temp["id"] =  $masterKey;
            if( !$this->keyExistAsValue( $masterKey,$targetModel ) ){
                // Tjekker om master har nye modeller
            //    $result["newModels"][] = $masterModel[$masterKey];
            $temp["is_new"] =  true;
            $temp["data"] =  $masterModel[$masterKey];
            $this->isInSync = 0;

            } else {

            // tjekker om master er in sync
            // print_R($masterModel[$masterKey]);
            $temp["is_new"] =  false;

            foreach( $masterModel[$masterKey] as $masterData ){

                $targetLanguageData = $this->findTargetLanguageData($targetModel,$masterData["model_id"],$masterData["language_id"]);
                 if($masterData["language_id"]== 1){
                     $temp["model_name"] = $targetLanguageData["model_name"]." - ".$targetLanguageData["model_no"];
                    $temp["itemnr"] = $targetLanguageData["model_present_no"];
                 }
              //  $result["model_".$targetLanguageData["model_id"]][$masterData["language_id"]] = $this->isIdentical($masterData,$targetLanguageData,["model_present_no","model_name","model_no","media_path","sampak_items"]);
                  $temp["status"][] = $this->isIdentical($masterData,$targetLanguageData,["model_present_no","model_name","model_no","media_path"]);
                }


          }
             $result[] =  $temp;
        }
        return $result;
   }
   private function isIdentical($source,$target,$fieldToTest)
   {
        $isIdentical = [];
        foreach($fieldToTest as $field){
            // billedet ligger kun p� den danske language_id=1
            if($field == "media_path"){
                if($source["language_id"] == 1){
                      $sourcePieces = explode("?", $source[$field]);
                      $targetPieces = explode("?", $target[$field]);
                      $source[$field] = $sourcePieces[0];
                      $target[$field] = $targetPieces[0];
                } else {
                      $source[$field] = "";
                      $target[$field] = "";
                }
            }
            if($field == "model_present_no"){
              //  echo $source[$field]. "###".$target[$field]."/n";
            }

            if( $this->cleanStr($source[$field]) != $this->cleanStr($target[$field])){
                $isIdentical[$field] = array("field"=>$field,"isSync"=> 0);
                $this->isInSync = 0;
            } else {
                $isIdentical[$field] = array("field"=>$field,"isSync"=> 1);
            }
        }
        $isIdentical["language_id"] = $source["language_id"];
        $isIdentical["language"] = $this->languageMatrix($source["language_id"]);
        return $isIdentical;
   }
   private function findTargetLanguageData($target,$masterID, $languageID)
   {
        $returnData = [];
        foreach (array_keys($target)  as $targetKey){
                foreach($target[$targetKey] as $targetData){
                    if($targetData["original_model_id"] == $masterID && $targetData["language_id"] == $languageID){
                         $returnData = $targetData;
                    }
                }
        }
        return $returnData;
   }


   private function isModelDataConsistent()
   {

   }


   private function keyExistAsValue($key,$targetArr){
        $exist = false;
        $values = new RecursiveIteratorIterator(new RecursiveArrayIterator($targetArr));
        foreach($values as $v) {
            if($v == $key){
                $exist = true;
            }
        }
        return $exist;

   }


   // ************* Sync present *********************

  private function updateSinglePresentModel($masterData,$targetData){
        foreach($masterData as $masterItem){
            if($masterItem["language_id"] == $targetData["lang"]){
                $sql = "update present_description set ".$targetData["field"]." = '".addslashes($masterItem[$targetData["field"]])."'
                where present_id = '".$targetData["id"]."' and language_id = ".$targetData["lang"];
                Dbsqli::setSql2($sql);
            }
       }
  }


   private function updatePresentModel($masterData,$targetID)
   {
        foreach (array_keys($masterData)  as $masterKey){

              foreach( $masterData[$masterKey] as $modelItem){
                    $sql = "update present_model set
                        model_present_no = '".$this->cleanStr($modelItem["model_present_no"])."',
                        model_name= '".addslashes($modelItem["model_name"])."' ,
                        model_no = '".addslashes($modelItem["model_no"])."',
                        media_path = '".$modelItem["media_path"]."'
                    where
                    original_model_id = '".$modelItem["model_id"]."' and
                    present_id = '".$targetID."' and
                    language_id = ".$modelItem["language_id"];
                    $result = Dbsqli::setSql2($sql);
              }


        }
   }
    private function cleanStr($str)
    {
        $cleaned_string = preg_replace('/\s+/', '', $str);
        return trim($cleaned_string);
    }
   private function updatePresentText($masterData,$targetID)
   {
       foreach($masterData as $masterItem){
            $sql = "update present_description set caption = '".addslashes($masterItem["caption"])."', short_description= '".$masterItem["short_description"]."' ,long_description = '".$masterItem["long_description"]."' where present_id = '".$targetID."' and language_id = ".$masterItem["language_id"];
            $result = Dbsqli::setSql2($sql);
       }
   }


   private function updateLogo($masterData,$targetID)
   {
        $sql = "update present set logo = '".$masterData."' where id =".$targetID;
        $result = Dbsqli::setSql2($sql);
   }
   private function updateGmo($masterData,$targetID)
   {
        $sql = "update present set oko_present = '".$masterData."' where id =".$targetID;
        $result = Dbsqli::setSql2($sql);
   }


   private function updatePresentMedia($master,$targetID)
   {
        // remove all target media data
        $sql = "delete from present_media where present_id = ".$targetID;
        Dbsqli::setSql2($sql);
        foreach($master as $masterItem){
            $sql = "INSERT INTO present_media ( `present_id`, `media_path`, `index`) VALUES ('".$targetID."','".$masterItem["media_path"]."','".$masterItem["index"]."')";
            $result = Dbsqli::setSql2($sql);
        }
   }



    private function languageMatrix($id){

        switch ($id) {
            case "1":
                return "Dansk";
                break;
            case "2":
                return "Engelsk";
                break;
            case "3":
                return "Tysk";
                break;
            case "4":
                return "Norsk";
                break;
            case "5":
                return "Svensk";
                break;
            default:
                return "intet land";
        }
    }
}