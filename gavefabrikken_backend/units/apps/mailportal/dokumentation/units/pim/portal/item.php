<?php

namespace GFUnit\pim\portal;

class Item
{
    private $options;

    public function __construct($options=array())
    {
        $this->options = $options;
    }

    public function getPresent($id){
        $sql = "select * from present where id=".$id;
        return \Present::find_by_sql($sql);
    }
    public function getPresentDescription($id){
        $sql = "select language_id,caption,short_description,long_description from present_description where present_id = ".$id;
        return \PresentDescription::find_by_sql($sql);
    }
    public function getPresentMedia ($id){
        $sql = "SELECT * FROM `present_media` WHERE `present_id` = ".$id." ORDER BY `present_media`.`index` ASC";
        return \PresentMedia::find_by_sql($sql);
    }
    public function getPresentModel($id){
        $sql = "SELECT * FROM `present_model` WHERE `present_id` = ".$id." AND `language_id` = 1";
        return \PresentModel::find_by_sql($sql);
    }




    public function loadItemList()
    {

      $limitStart = $this->options["start"];
      $limitLength = $this->options["length"];
      $costpriceStart = $this->options["costpriceStart"];
      $costpriceTo = $this->options["costpriceTo"];




      $sqlBudgetSearch = "";
      if($costpriceStart > 0 || $costpriceTo > 0){
          $sqlBudgetSearch = " and prisents_nav_price >= ".$costpriceStart."  and prisents_nav_price <= ".$costpriceTo;
      }


      $search = "";
      if($this->options["search"]["value"] != ""){
            $search = " and (";
            $sqlParts = [];
            $pieces = explode(" ", trim($this->options["search"]["value"]));
            foreach ($pieces as $ele){
                $sqlParts[] = "(
                nav_name LIKE '%".$ele."%' ||
                caption LIKE '%".$ele."%' ||
                vendor LIKE '%".$ele."%' ||
                model_present_no LIKE '%".$ele."%' )";

            }
          $search = " and (". implode(" && ",$sqlParts)  .")";
      }



      $sql = "SELECT
            `present`.`id`,
            nav_name,logo,price_group,
            caption,
            long_description as short_description,
            `present`.vendor,

            GROUP_CONCAT(DISTINCT present_media.media_path SEPARATOR ', ') as media_p,
            GROUP_CONCAT(DISTINCT `model_present_no` SEPARATOR ', ') as itemnr

            FROM `present`
            left join present_media on present_media.present_id = present.id
            left JOIN present_description on present_description.present_id = present.id
            left join present_model on present_model.present_id = present.id
            WHERE
            present_description.language_id = 1 AND
            present_model.language_id = 1 and
            present_media.index = 0 and
            present.deleted = 0 and
            copy_of = 0
            ".$sqlBudgetSearch. $search."

   	        GROUP by `present`.`id` order by `present`.`id` desc limit ".$limitStart.", ". $limitLength;
        $total = 0;

            $sql_total = "SELECT
            `present`.`id`,
            GROUP_CONCAT(DISTINCT present_media.media_path SEPARATOR ', ') as media_p,
            GROUP_CONCAT(DISTINCT `model_present_no` SEPARATOR ', ') as itemnr

            FROM `present`
            left join present_media on present_media.present_id = present.id
            left JOIN present_description on present_description.present_id = present.id
            left join present_model on present_model.present_id = present.id
            WHERE
            present_description.language_id = 1 AND
            present_model.language_id = 1 and
            present_media.index = 0 and
            present.deleted = 0 and
            copy_of = 0
            ".$sqlBudgetSearch. $search."

   	        GROUP by `present`.`id` order by `present`.`id` ";

            $RsCount = \Present::find_by_sql($sql_total);
            $total = countgf($RsCount);




            return $this->tableOutput(\Present::find_by_sql($sql),$total);

    }

    private function tableOutput($rs,$total){
        $data = [];
        if(countgf($rs) > 0){
            foreach ($rs as $item){
                $data[] = array(
                    "imgPath"=> $this->getFirstPicUrl($item->media_p),
                    "itemnr"=>$item->itemnr,
                    "caption"=>$item->caption,
                    "nav_name"=>$item->nav_name,
                    "vendor"=>$item->vendor,
                    "short_description"=>base64_decode($item->short_description),
                    "action"=>"<div style='width: 120px; font-size: 12px;'>
                        <button class='pim-table-show' data-id='$item->id' type=button>Vis</button>
                        <button class='pim-table-add' data-id='$item->id' type=button>Tilføj</button>
                        <button class='pim-table-sync' data-id='$item->id' type=button>Sync</button>
                        </div>"
                );

            }


        }
        return $return = array(
            "recordsTotal"=>$total,
            "recordsFiltered"=>$total,
            "data" => $data
        );
    }
    private function getFirstPicUrl($path){
        $imgPath =  trim(explode(",", $path)[0]);
        return "<img style='width:95%' class='pim-table-img' src='https://system.gavefabrikken.dk/gavefabrikken_backend/views/media/user/".$imgPath.".jpg'  /> ";
    }



}
/*
 *         $returndata = array(
            "recordsTotal"=>3,
            "recordsFiltered"=>3,
            "data" => [
                ["first_name"=>"Zorita"],
                ["first_name"=>"Zenaida"],
                ["first_name"=>"Yuri"]
            ]

        );
 */
