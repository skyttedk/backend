<?php
include "service/db.php";

class presentation
{
    public static function createShop($data){

    $dbConn = new db;
        // navn på oplæg

        $sql = "UPDATE `presentation_sale` SET `has_shop` = '1' WHERE `presentation_sale`.`id` = '".$data["id"]."'";
        return $dbConn->set($sql);
    }
    public static function getPresentList()
    {
        $dbConn = new db;
        $id = $_POST["id"];
        $sql = "SELECT * FROM `presentation_sale_pdf` where  presentation_id = '".$id."'  and is_deleted = 0 ORDER BY `presentation_sale_pdf`.`sort` ASC   ";
        return $dbConn->get($sql);
    }


    public static function getAllArchive()
    {
        $dbConn = new db;
        $userID = $_POST["userId"];
        $lang =  $_POST["lang"];
        $sql = "SELECT * FROM `presentation_sale` WHERE `created` > '2022-02-01 00:00:00' and `is_deleted` = 0 and `language` = ".$lang." and name != '' and author_id !=  ".$userID." order by author_id,name";
        return $dbConn->get($sql);
    }
    public static function searchAllArchive()
    {
        $dbConn = new db;
        $userID = $_POST["userId"];
        $lang =  $_POST["lang"];
        $txt = $_POST["txt"];
        $sql = "SELECT * FROM `presentation_sale` WHERE name like ('%".$txt."%') and `created` > '2022-02-01 00:00:00' and `is_deleted` = 0 and `language` = ".$lang." and name != '' and author_id !=  ".$userID." order by author_id,name";
        return $dbConn->get($sql);
    }


    public static function updateSort()
    {
          $dbConn = new db;
          $presentationId = $_POST["presentationId"];
          $list = explode(",",$_POST["sortlist"]);
          $i=0;
          foreach($list as $item){
            $sql = "update presentation_sale_pdf set sort = ".$i." where presentation_id = '".$presentationId."' and present_id = ".$item;
            $dbConn->set($sql);
            $i++;
          }
          return [];

    }

    public static function getAllWithPresent($data)
    {
          $dbConn = new db;
          $sql = "select presentation_sale.*, presentation_sale_pdf.present_id,pt_img,nav_name,has_shop  from presentation_sale
          inner join presentation_sale_pdf on
          presentation_sale_pdf.presentation_id = presentation_sale.id
          inner join present on
          present.id = presentation_sale_pdf.present_id
          where presentation_sale.name != '' and presentation_sale.is_deleted = 0 and presentation_sale.author_id =".$data["userId"];
          return $dbConn->get($sql);
    }

    public static function getAll($data)
    {
          $dbConn = new db;
          $sql = "select * from presentation_sale where name != '' and is_deleted = 0 and author_id =".$data["userId"];
          return $dbConn->get($sql);
    }
    public static function remove($data)
    {
        $dbConn = new db;
        $values = array($data["id"]);
        $sql = "update presentation_sale set is_deleted = 1 where id = ?";
        return $dbConn->set($sql,"s",$values);

    }
    public static function create($data)
    {
        $dbConn = new db;
        $values = array();
        $sql = "INSERT INTO presentation_sale (id,author_id,name,config,language)
        values ('".$data["id"]."','".$data["author_id"]."','".$data["presentation_name"]."','".json_encode($data["config"])."','".$data["lang"]."')";
        return $dbConn->set($sql);

    }
    public static function getByConfig($data){
        $dbConn = new db;
         $sql = "select name,config from presentation_sale where id = '".$data["id"]."' and is_deleted = 0";
         return $dbConn->get($sql);
    }
    public static function getById($data){
          $dbConn = new db;
         $sql = "select pt_img, presentation_sale_pdf.* from presentation_sale_pdf
                inner JOIN present ON
                present.id = presentation_sale_pdf.present_id
                where presentation_id = '".$data["id"]."' and is_deleted = 0 order by presentation_sale_pdf.sort";
          return $dbConn->get($sql);
    }
    public static function removeById($data){
         $dbConn = new db;
         $sql = "delete from presentation_sale_pdf where presentation_id = '".$data["id"]."'";
          return $dbConn->set($sql);
    }
    public static function updateConfig($data){
          $dbConn = new db;
          $sql = "update presentation_sale set config = '".json_encode($data["config"])."' where id = '".$data["id"]."'";
          return $dbConn->set($sql);
    }
    public static function copy($data){
        $source = $data["presentationId"];
        $target = $data["targetID"];
        $userID = $data["userId"];
        $name = $data["name"];
        $dbConn = new db;
        $sql = "select * from presentation_sale where id='".$source."'";
        $rsSaleHeader = $dbConn->get($sql);
        $sql = "select * from presentation_sale_pdf where presentation_id='".$source."'";
        $rsPresents = $dbConn->get($sql);




        if(sizeofgf($rsSaleHeader) == 0) return;
            $sql = "insert into presentation_sale (config,show_price,language,id,author_id,name)
            values( '".$rsSaleHeader[0]["config"]."', ".$rsSaleHeader[0]["show_price"].",".$rsSaleHeader[0]["language"].",'".$target."',".$userID.",'".$name."' )";
            $dbConn->set($sql);

            foreach($rsPresents as $present){
                 $sql = "insert into presentation_sale_pdf ( present_id,author,presentation_id,setting,sort)
                 values (".$present["present_id"].",".$userID.",'".$target."','".$present["setting"]."',".$present["sort"].")";
                 $dbConn->set($sql);
            }
           return [];

    }





}

?>
