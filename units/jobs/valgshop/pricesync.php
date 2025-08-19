<?php

namespace GFUnit\jobs\valgshop;

use GFBiz\units\UnitController;
class PriceSync
{
    public function __construct()
    {

    }
    public function opdateChildsItem1(){
        $data = []; //$this->getMasterSimgleItems();
        foreach ($data as $item){
            $presentID = $item["present_id"];
            $navData = $this->getNavData($item["model_present_no"]);
            $nav_dk =  "";
            $nav_no =  "";
            $itemno = $item["model_present_no"];
            foreach ($navData as $navItem){
                if($navItem["language_id"] == 1){
                    $nav_dk =  $navItem["description"];

                }
                if($navItem["language_id"] == 4){
                    $nav_no =  $navItem["description"];
                }

            }
            // gemmer nav navn i master gaven
            $this->updateNavName($presentID,$nav_dk,$nav_no);
            // henter child id'er
            $childList = $this->getChildPresents($item["present_id"]);
            foreach ($childList as $childItem){
                $childPasentID = $childItem["id"];
                // opdatere child item nummer
                $this->updateItemno($childPasentID,$itemno);
                // Opdatere Child nav navn
                $this->updateNavName($childPasentID,$nav_dk,$nav_no);


            }
            echo "<br>";
        }
        echo "done";
    }
    private function updateItemno($id,$itemno){
       $sql = "update present_model set model_present_no = '$itemno' where present_id = $id";
     //   \Dbsqli::setSql2($sql);
    }


    private function updateNavName($id,$dk,$no)
    {
        $sqlPart = [];
        if($dk != ""){
            $sqlPart[] = "nav_name='$dk'";
        }
        if($no != ""){
            $sqlPart[] = "nav_name_no='$no'";
        }
        if($sqlPart == []){
            return;
        }
        $sql = "update present set ".implode(", ", $sqlPart)." where id = $id";
      //  \Dbsqli::setSql2($sql);
    }



    private function getModel($presentID){
        $sql = "SELECT * FROM `present_model` WHERE `present_id` = $presentID";
      //  return \Dbsqli::getSql2($sql);
    }


    private function getChildPresents($masterPresentID){
         $sql = "SELECT * FROM `present` WHERE `copy_of` = $masterPresentID";
     //   return \Dbsqli::getSql2($sql);
    }

    private function getMasterSimgleItems()
    {
        $sql = "
        SELECT 
            `present_model`.`present_id`,
            COUNT(`present_model`.`present_id`) antal,
            present_model.model_present_no
        FROM
            `present_model`
        INNER JOIN(
            SELECT
                *
            FROM
                `present`
            WHERE
                `copy_of` = 0 AND active = 1 AND deleted = 0 and pim_id > 0 and shop_id = 0
        
        ) p
        ON
            p.id = present_model.present_id AND `present_model`.language_id = 1 
        GROUP by `present_model`.`present_id` HAVING antal = 1 ;
        ";
   //     return \Dbsqli::getSql2($sql);
    }
    private function getNavData($itemno)
    {
        $sql = "SELECT * 
            FROM navision_item 
            WHERE no = '$itemno' 
            AND language_id IS NOT NULL 
            AND deleted IS NULL";
  //      return \Dbsqli::getSql2($sql);
    }



}