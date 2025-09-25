<?php

class StorageMonitoringSchedule
{
    public $ShopData;
    public function setShopData($shopData){
        $this->ShopData = $shopData;
    }

    public function checkForExceededPresents()
    {
       foreach($this->ShopData as $data){

            $isActive = true;
            // tjekker om feltet reserveret er sat, elles skal der ikke tjekkes
            if($data["reserved_quantity"] != ""){
                // hvis ingen present_properties er sat er gaven aktiv
                if($data["present_properties"] != ""){
                    // tjekker om  aktivOption er sat til false
                    if (strpos($data["present_properties"], '"aktivOption":false') !== false) {
                        $isActive = false;
                    } else {
                    // tjekker om gaven er en med model
                        if($data["present_model_id"] != "" ){
                        // tjekker om modellen er aktive, model id skal indgå i variantListOption
                            if (strpos($data["present_properties"], "\"".$data["present_model_id"]."\"") === false) {
                                $isActive = false;
                            }
                        }
                    }
                }
            }

            // hvis gaven er aktive
            if($isActive == true){
     //           print_r($data);
                // tjekke om reservationen er overskredet
                $order_count       = $data["order_count"];
                $reserved_quantity = $data["reserved_quantity"];
                $warning_level     = $data["warning_level"];

                if( ($reserved_quantity*($warning_level/100)) <  $order_count ){
                    // tjekker om der er en erstatsningsgave
                    if($data["do_close"] == true){
                        $present_properties =  str_replace('"aktivOption":true','"aktivOption":false',$data["present_properties"]);
                        ShopPresent::setPresentPropertiesSchedule($data["present_properties_id"],$present_properties);
                    }
                    if($data["replacement_present_id"] != ""){
                        // if false så har vi udført operationen i tidligere step
                        if($data["do_close"] == false) {
                            $present_properties =  str_replace('"aktivOption":true','"aktivOption":false',$data["present_properties"]);
                            ShopPresent::setPresentPropertiesSchedule($data["present_properties_id"],$present_properties);
                        }
                        $replacementData = $this->getPresentProperties($data["replacement_present_id"]);
                        $present_properties =  str_replace('"aktivOption":false','"aktivOption":true',$replacementData["present_properties"]);
                        $replacementData["present_properties_id"];
                        ShopPresent::setPresentPropertiesSchedule($replacementData["present_properties_id"],$present_properties);
                    }

                }

            }



       }
    }
    private function getPresentProperties($presentId){
        $return = [];
        foreach($this->ShopData as $data){
            if($data["present_id"] == $presentId){
                $return["present_properties"] = $data["present_properties"];
                $return["present_properties_id"] = $data["present_properties_id"];
                return $return;
            }
        }
    }



}


?>