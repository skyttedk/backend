<?php
include "service/db.php";

class  present
{

   public static $dbConn;


    public static function addPresent($post){
        print_R($post);
    }
    public static function create($post){

     $dbConn = new db;
        $price_dk = "";
        $price_no = "";
        $show_dk = 0;
        $show_no = 0;

        $data =  $post["data"];
        $radom = rand(pow(10, 10-1), pow(10, 10)-1);
        $price =  json_encode($data["price"]);
        if($post["lang"] == 1){
            $price_dk = $price;
            $show_dk = 1;
        }
        if($post["lang"] == 4){
            $price_no = $price;
            $show_no = 1;
            $data["prisents_nav_price_no"] = $data["prisents_nav_price"];
            $data["prisents_nav_price"] = 0;
        }

        $sql = "INSERT INTO present
        (name,nav_name,internal_name,logo,vendor,pt_layout,pt_img,pt_img_small,oko_present,kunhos,prisents_nav_price,prisents_nav_price_no,pt_price,pt_price_no,show_to_saleperson,show_to_saleperson_no,state)
        VALUES (
        '".$radom."',
        '".$data["nav_name"]."',
        '".$radom."',
        'logo/intet.jpg',
        '".$data["vendor"]."',
        ".$data["pt_layout"].",
        '".$data["pt_img"]."',
        '".$data["pt_img_small"]."',
        ".$data["oko_present"].",
        ".$data["kunhos"].",
        '".$data["prisents_nav_price"]."',
        '".$data["prisents_nav_price_no"]."',
        '".$price_dk."',
        '".$price_no."',
        '".$show_dk."',
        '".$show_no."',
        'c')";
       $return =  $dbConn->set($sql);
       $lastID =   $return["last_id"];

       $sql = "INSERT INTO presentation_sale_present (present_id,author,language)
            values ( ".$lastID.",".$post["user_id"].",".$post["lang"]." )";
       $dbConn->set($sql);


       $sql = "INSERT INTO present_description (language_id,present_id,`caption`,short_description,long_description)
            values ( 2, ".$lastID.",'###', '###', '###' )";
       $dbConn->set($sql);

       $sql = "INSERT INTO present_description (language_id,present_id,`caption`,short_description,long_description)
            values ( 3, ".$lastID.",'###', '###', '###' )";
       $dbConn->set($sql);

       // norge
       if($post["lang"] == "4"){
                $sql = "INSERT INTO present_description (language_id,present_id,`caption`,short_description,long_description)
            values (
             4,
             ".$lastID.",
             '".$data["caption"]."',
             '".$data["shortDescription"]."',
             '".$data["detailDescription"]."'
            )";
            $dbConn->set($sql);
       } else {
            $sql = "INSERT INTO present_description (language_id,present_id,`caption`,short_description,long_description)
            values ( 4, ".$lastID.",'###', '###', '###' )";
            $dbConn->set($sql);
       }

       $sql = "INSERT INTO present_description (language_id,present_id,`caption`,short_description,long_description)
            values ( 5, ".$lastID.",'###', '###', '###' )";
       $dbConn->set($sql);

       // danmark
       if($post["lang"] == "1"){
            $sql = "INSERT INTO present_description (language_id,present_id,`caption`,short_description,long_description)
            values (
             1,
             ".$lastID.",
             '".$data["caption"]."',
             '".$data["shortDescription"]."',
             '".$data["detailDescription"]."'
            )";
            $dbConn->set($sql);
       } else {
              $sql = "INSERT INTO present_description (language_id,present_id,`caption`,short_description,long_description)
                values ( 1, ".$lastID.",'###', '###', '###' )";
              $dbConn->set($sql);
       }

       return array("status"=>"1");
      }
   public static function getItemNr($post){
        $dbConn = new db;
        $sql = "SELECT * FROM `present_model` WHERE `present_id` = ".$post['itemnr']." and `language_id` = 1 and active = 0";
        return $dbConn->get($sql);
   }

  public static function doDelete($post){
        $dbConn = new db;
        $sql = "select * from present where `id` = ".$post['id'];
        $result = $dbConn->get($sql);
        $finalSql = "";

            if($post['lang'] == 1){
                $finalSql = "update `present` set show_to_saleperson = 0 WHERE `id` = ".$post['id'];
            }
            if($post['lang'] == 4){
                $finalSql = "update `present` set show_to_saleperson_no = 0 WHERE `id` = ".$post['id'];
            }

        return $dbConn->set($finalSql);
   }
   public static function update($post){
    $dbConn = new db;
        $data =  $post["data"];
        $price =  json_encode($data["price"]);
        $sql = "";
        if($post["lang"] == "1"){
            $sql = "update present set
            nav_name = '".$data["nav_name"]."',
            logo  = 'logo/intet.jpg',
            vendor = '".$data["vendor"]."',
            pt_layout  = ".$data["pt_layout"].",
            pt_img =  '".$data["pt_img"]."',
            pt_img_small  = '".$data["pt_img_small"]."',
            oko_present =  ".$data["oko_present"].",
            kunhos = ".$data["kunhos"].",
            prisents_nav_price = '".$data["prisents_nav_price"]."',
            pt_price =  '".$price."'
            where id = ".$post["id"];

            $sqlUpdate  =  "update present_description set
                `caption` = '".$data["caption"]."',
                short_description = '".$data["shortDescription"]."',
                long_description = '".$data["detailDescription"]."'
                where present_id = ".$post["id"]."
                and language_id = 1 ";
             $dbConn->set($sqlUpdate);

        }
        if($post["lang"] == "4"){
            $sql = "update present set
            nav_name = '".$data["nav_name"]."',
            logo  = 'logo/intet.jpg',
            vendor = '".$data["vendor"]."',
            pt_layout  = ".$data["pt_layout"].",
            pt_img =  '".$data["pt_img"]."',
            pt_img_small  = '".$data["pt_img_small"]."',
            oko_present =  ".$data["oko_present"].",
            kunhos = ".$data["kunhos"].",
            prisents_nav_price_no = '".$data["prisents_nav_price"]."',
            pt_price_no =  '".$price."'
            where id = ".$post["id"];

            $sqlUpdate  =  "update present_description set
                `caption` = '".$data["caption"]."',
                short_description = '".$data["shortDescription"]."',
                long_description = '".$data["detailDescription"]."'
                where present_id = ".$post["id"]."
                and language_id = 4 ";
             $dbConn->set($sqlUpdate);

        }

         return $dbConn->set($sql);
   }

    public static function readAll($post)
    {

        $dbConn = new db;
          $sql = "";
          if($post["lang"] == 1){
                $sql = "select present.id, state, nav_name as caption,pt_img,pt_img_small,pt_price,vendor,presentation_sale_present.`author`,pt_price as priceList
                from present
                left join presentation_sale_present on present.id = presentation_sale_present.present_id
                where show_to_saleperson = 1 and copy_of = 0 and pt_layout != 0 and shop_id = 0 and pt_img  IS NOT NULL  order by created_datetime DESC";
          }
          if($post["lang"] == 4){
                $sql = "select state, present.*, caption,vendor,presentation_sale_present.`author`,pt_price_no as priceList from present
                        INNER join present_description on present_description.present_id = present.id
                        left join presentation_sale_present on present.id = presentation_sale_present.present_id
                        where show_to_saleperson_no = 1 and copy_of = 0 and language_id = 4 and shop_id = 0 and  pt_layout != 0 and pt_img  IS NOT NULL  order by created_datetime DESC";
          }
        
          return $dbConn->get($sql);
    }
    public static function getAllItemnr(){
       $dbConn = new db;
        $sql = "select present.id,model_present_no from present
         inner join  present_model on present.id = present_model.present_id
         where present.active = 1 and show_to_saleperson = 1 and present.copy_of = 0 and language_id = 1";
         return $dbConn->get($sql);
    }
    public static function getPresentModels($presentID)
    {
        $sql = "select * from present_model where present_id =  $presentID ";
        return $dbConn->get($sql);
    }

    public static function getByLetterGroup($post)
    {
          if (!is_numeric($post["letter"]) && sizeofgf($post["letter"]) != 1 ) {
            die("Db error");
          }
          $dbConn = new db;
          $values = array($post["letter"]);
          $sql = "select present.id, present.nav_name,short_description, pt_img,pt_price from present
            inner join present_description on
            present.id = present_description.present_id

            where
                nav_name like '".$post["letter"]."%' and
                active = 1 and
                present_description.language_id =  1 and
                show_to_saleperson = 1 and
                present.copy_of = 0  order by present.id ";
          //$sql = "select * from present where nav_name LIKE '?%' and active = 1 limit 50";
          return $dbConn->get($sql);
    }
    public static function getById($post)
    {
          $dbConn = new db;
          $values = array($post["id"]);
          if($post["lang"] == 1) {
          $sql = "select present.*,`caption`,short_description,long_description from present
            inner join present_description on
            present.id = present_description.present_id

            where
                present.id = ? and
                active = 1 and
                present_description.language_id =  1 and
                show_to_saleperson = 1 and
                present.copy_of = 0  order by present.id   ";
          }
          if($post["lang"] == 4) {
          $sql = "select present.*,`caption`,short_description,long_description from present
            inner join present_description on
            present.id = present_description.present_id
            where
                present.id = ? and
                active = 1 and
                present_description.language_id =  4 and
                show_to_saleperson_no = 1 and
                present.copy_of = 0 order by present.id   ";
          }
          return $dbConn->get($sql,"s",$values);
    }

    public static function searchItemNumber($post)
    {
          $dbConn = new db;
          $values = array();
          $wherePars = [];
          $textParts = explode(" ",$post["text"]);
          foreach($textParts as $item){
          //  array_push($wherePars,"model_present_no like '".$item."'");
            array_push($wherePars,'(`model_present_no` like ("'.$item.'") or `model_present_no` like ("%***'.$item.'%") or `model_present_no` like ("%'.$item.'***%")     )');
          }
           $sql = 'SELECT distinct present_id as id FROM `present_model` WHERE ('.implode(" or ",$wherePars).')';
          return $dbConn->get($sql);
    }


    public static function freeTextSearch($post)
    {
          $dbConn = new db;
          $values = array();
          $wherePars = [];
          $textParts = explode(" ",$post["text"]);

         if($post["lang"] == 1) {
          foreach($textParts as $item){
            array_push($wherePars,'(`caption` like ("%'.$item.'%") or `vendor` like ("%'.$item.'%") or `nav_name` like ("%'.$item.'%")     )');
          }
             $sql = 'select present.*, caption from present INNER join present_description on
                        present_description.present_id = present.id
                        where active = 1 and ('.implode(" && ",$wherePars).') and show_to_saleperson = 1 and copy_of = 0 and language_id = 1 order by caption';
          $rs = $dbConn->get($sql);
             if(sizeof($rs) > 0){
                 return $rs;
             } else {
                 $sql = "SELECT distinct present_id as id FROM `present_model`
                    inner JOIN present on present.id = present_model.present_id
                    WHERE 
                    `model_present_no` = '".$post["text"]."' AND
                    `language_id` = 1 AND
                    present.active = 1  AND
                    present.show_to_saleperson = 1";
                 return $dbConn->get($sql);
             }
         }
         if($post["lang"] == 4) {
          foreach($textParts as $item){
            array_push($wherePars,'(`caption` like ("%'.$item.'%") or `vendor` like ("%'.$item.'%") or `nav_name` like ("%'.$item.'%")     )');
          }
          $sql = 'select present.*, caption from present INNER join present_description on
                        present_description.present_id = present.id
                        where  ('.implode(" && ",$wherePars).') and show_to_saleperson_no = 1 and copy_of = 0 and language_id = 4 order by caption';
          $rs = $dbConn->get($sql);
          if(sizeof($rs) > 0){
              return $rs;
          } else {
              $sql = "SELECT distinct present_id as id FROM `present_model`
                    inner JOIN present on present.id = present_model.present_id
                    WHERE 
                    `model_present_no` = '".$post["text"]."' AND
                    `language_id` = 1 AND
                    present.active = 1  AND
                    present.show_to_saleperson_no = 1";
              return $dbConn->get($sql);
          }
         }




    }
    public static function itemnrSearchOnCountry($post){
        $dbConn = new db;
        $sql = "";
        if($post["lang"] == 1) {
            $sql = "SELECT *  FROM `present_model`
                    inner JOIN present on present.id = present_model.present_id
                    WHERE 
                    `model_present_no` = '".$post["text"]."' AND
                    `language_id` = 1 AND
                    present.active = 1  AND
                    present.show_to_saleperson = 1";
        }

        if($post["lang"] == 4) {
            $sql = "SELECT *  FROM `present_model`
                    inner JOIN present on present.id = present_model.present_id
                    WHERE 
                    `model_present_no` = '".$post["text"]."' AND
                    `language_id` = 1 AND
                    present.active = 1  AND
                    present.show_to_saleperson_no = 1";
        }
        return $dbConn->get($sql);

    }
    public static function getRange($post)
    {
          $show_to_saleperson;
          $prisents_nav_price;
          if($post["lang"] == 1) { $show_to_saleperson = "show_to_saleperson"; $prisents_nav_price= "prisents_nav_price"; }
          if($post["lang"] == 4) { $show_to_saleperson = "show_to_saleperson_no";  $prisents_nav_price= "prisents_nav_price_no"; }

          $dbConn = new db;
          $sql = "SELECT * FROM `present` WHERE ".$prisents_nav_price." BETWEEN ".$post["start"]." AND ".$post["end"]." and ".$show_to_saleperson." = 1 and present.copy_of = 0";
          return $dbConn->get($sql);


    }
    public static function getNavPrice($post){
          $show_to_saleperson;
          $prisents_nav_price;
          if($post["lang"] == 1) { $show_to_saleperson = "show_to_saleperson"; $prisents_nav_price= "prisents_nav_price"; }
          if($post["lang"] == 4) { $show_to_saleperson = "show_to_saleperson_no";  $prisents_nav_price= "prisents_nav_price_no"; }

          $dbConn = new db;
          $sql = "select ".$prisents_nav_price." as nav_prise from present where active = 1 and ".$show_to_saleperson." = 1 and copy_of = 0 and ".$prisents_nav_price." != '' ";
          return $dbConn->get($sql);
    }

}

?>


