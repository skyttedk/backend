<?php
// Controller shop
// Date created  Sun, 03 Apr 2016 21:00:47 +0200
// Created by Bitworks
class pimPresentSyncController Extends baseController {



    public function Index() {
       $this->registry->template->show('pimPresentSync_view');
    }
    public function loadSyncStatusForPresent(){
        $presentID =  is_int( $_GET["id"]*1) ? ($_GET["id"]*1): die("{status:0}");
        $sql = "SELECT `copy_of`,shop_id,present.logo,shop.name,`present`.id FROM `present`
                inner join shop on present.shop_id = shop.id
                WHERE copy_of = $presentID AND present.active = 1 and present.deleted = 0 ";
        $copyOfList = Dbsqli::getSql2($sql);

        $basePresentSyncData = $this->getSyncData($presentID );

        $result = [];
        foreach($copyOfList as $present){
//            $result["pim_".$present["id"]]["sync"] =   );
//            $result["pim_".$present["id"]]["name"] = $present["name"];
//            $result["pim_".$present["id"]]["id"] =  $present["id"];
            $result[] = array(
                "id"=> $present["id"],
                "name"=> $present["name"],
                "sync" => $this->IsSync( $basePresentSyncData, $this->getSyncData( $present["id"] ))
            );

        }
        response::success(json_encode($result));
    }

    private function getSyncData($presentID){
        $syncData = [];

        //  present_description
        $sql = "select * from present_media where present_id = ".$presentID." ORDER BY `present_media`.`index` ASC " ;
        $presentMedia = Dbsqli::getSql2($sql);
        $syncData["presentMedia"] = $presentMedia;

        $sql = "select * from present_description where present_id =  $presentID";
        $presentDescription = Dbsqli::getSql2($sql);
        $syncData["presentDescription"] = $presentDescription;

        $sql = "select logo from present where id =  $presentID";
        $present = Dbsqli::getSql2($sql);
        $syncData["present"] = $present;

        //$sql = "SELECT * FROM `present_model` WHERE `present_id` = ".$presentID." ORDER BY `model_id`,language_id ASC"
        $sql = "SELECT distinct model_id from `present_model` WHERE `present_id` = ".$presentID;
// 42093
// 42480



        return $syncData;
    }
    private function IsSync($source,$target){
        $isSync = [];
        $numberOfLanguages = 5;
        // present - logo
        $isSync["present"] =  sizeofgf(array_diff($source["present"][0],$target["present"][0])) == 0 ? 1:0;
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
        }
        // presentDescription
        //  $isSync["presentMedia"]
        $presentDescriptionSyncSucces = true;
        if(sizeofgf($target["presentDescription"]) != $numberOfLanguages ){
           $presentDescriptionSyncSucces = false;

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
                }
            }
        }
        return $isSync;
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
