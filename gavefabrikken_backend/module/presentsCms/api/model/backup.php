<?php
include "service/db.php";

class present
{
    public static function create($post){

     $dbConn = new db;
        $data =  $post["data"];
        $radom = rand(pow(10, 10-1), pow(10, 10)-1);
        $price =  json_encode($data["price"]);
        $sql = "INSERT INTO present
        (name,nav_name,internal_name,logo,vendor,pt_layout,pt_img,pt_img_small,oko_present,kunhos,prisents_nav_price,pt_price,show_to_saleperson)
        VALUES (
        '".$radom."',
        '".utf8_encode($data["nav_name"])."',
        '".$radom."',
        '',
        '".utf8_encode($data["vendor"])."',
        ".$data["pt_layout"].",
        '".$data["pt_img"]."',
        '".$data["pt_imgSmall"]."',
        ".$data["oko_present"].",
        ".$data["kunhos"].",
        '".$data["prisents_nav_price"]."',
        '".$price."',1)";
       $return =  $dbConn->set($sql);
       $lastID =   $return["last_id"];

       $sql = "INSERT INTO present_description (language_id,present_id,`caption`,short_description,long_description)
            values (
             1,
             ".$lastID.",
             '".utf8_encode(utf8_encode($data["caption"]))."',
             '".$data["shortDescription"]."',
             '".$data["detailDescription"]."'
            )";
       return $dbConn->set($sql);





    }






    public static function readAll($post)
    {
          $dbConn = new db;
          $sql = "";
          if($post["lang"] == 1){
                $sql = "select id,nav_name as caption,pt_img,pt_img_small,pt_price,vendor  from present where active = 1 and show_to_saleperson = 1 and copy_of = 0 order by nav_name";
          }
          if($post["lang"] == 4){
                $sql = "select present.*, caption,vendor from present INNER join present_description on
                        present_description.present_id = present.id
                        where active = 1 and show_to_saleperson_no = 1 and copy_of = 0 and language_id = 4 order by caption";
          }
          return $dbConn->get($sql);
    }
    public static function getByLetterGroup($post)
    {
          if (!is_numeric($post["letter"]) && sizeofgf($post["letter"]) != 1 ) {
            die("Db error");
          }
          $dbConn = new db;
          $values = array($post["letter"]);
          $sql = "select present.id, present.nav_name,short_description, media_path,pt_img,pt_price from present
            inner join present_description on
            present.id = present_description.present_id
            inner join present_media on
            present.id = present_media.present_id
            where
                nav_name like '".$post["letter"]."%' and
                active = 1 and
                present_description.language_id =  1 and
                show_to_saleperson = 1 and
                present.copy_of = 0 and

                present_media.index = 0 order by present.id ";
          //$sql = "select * from present where nav_name LIKE '?%' and active = 1 limit 50";
          return $dbConn->get($sql);
    }
    public static function getById($post)
    {
          $dbConn = new db;
          $values = array($post["id"]);
          if($post["lang"] == 1) {
          $sql = "select present.id,pt_img, present.nav_name,short_description, media_path,pt_price from present
            inner join present_description on
            present.id = present_description.present_id
            inner join present_media on
            present.id = present_media.present_id
            where
                present.id = ? and
                active = 1 and
                present_description.language_id =  1 and
                show_to_saleperson = 1 and
                present.copy_of = 0 and
                present_media.index = 0 order by present.id   ";
          }
          if($post["lang"] == 4) {
          $sql = "select present.id,pt_img, present.nav_name,short_description, media_path,pt_price from present
            inner join present_description on
            present.id = present_description.present_id
            inner join present_media on
            present.id = present_media.present_id
            where
                present.id = ? and
                active = 1 and
                present_description.language_id =  4 and
                show_to_saleperson_no = 1 and
                present.copy_of = 0 and
                present_media.index = 0 order by present.id   ";
          }
          return $dbConn->get($sql,"s",$values);
    }
    public static function freeTextSearch($post)
    {
          $dbConn = new db;
          $values = array();
          $wherePars = [];
          $textParts = explode(" ",$post["text"]);

         if($post["lang"] == 1) {
          foreach($textParts as $item){
            array_push($wherePars,'(`nav_name` like ("%'.$item.'%") or `vendor` like ("%'.$item.'%"))');
          }
          $sql = 'SELECT * FROM `present` WHERE ('.implode(" && ",$wherePars).') and show_to_saleperson = 1 and present.copy_of = 0';
          return $dbConn->get($sql);
         }
         if($post["lang"] == 4) {
          foreach($textParts as $item){
            array_push($wherePars,'(`caption` like ("%'.$item.'%") or `vendor` like ("%'.$item.'%"))');
          }
          $sql = 'select present.*, caption from present INNER join present_description on
                        present_description.present_id = present.id
                        where active = 1 and ('.implode(" && ",$wherePars).') and show_to_saleperson_no = 1 and copy_of = 0 and language_id = 4 order by caption';
          return $dbConn->get($sql);
         }
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


