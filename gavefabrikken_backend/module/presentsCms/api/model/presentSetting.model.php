<?php
include "service/db.php";
class presentSetting
{
    public static function load($post)
    {
          $dbConn = new db;
          $values = array($post["id"]);
          $sql = "select * from presentation_sale_pdf where presentation_id = ?";
          return $dbConn->get($sql,"s",$values);
    }
    public static function update($post)
    {
          $dbConn = new db;
          $values = array();
          $sql = "update presentation_sale_pdf set setting = '".json_encode($post["config"])."' where presentation_id = '".$post["presentationId"]."'  and  present_id = '".$post["id"]."'";
          return $dbConn->set($sql,"",$values);
    }
    public static function loadPriceShow($post){
          $dbConn = new db;
          $values = array($post["id"]);
          $sql = "select * from presentation_sale where id = ?";
          return $dbConn->get($sql,"s",$values);
    }
    public static function updatePriceShow($post){
          $dbConn = new db;
          $values = array($post);
          $sql = "update presentation_sale set show_price = ".json_encode($post["showPrice"])." where id = '".$post["id"]."'";
          return $dbConn->set($sql,"",$values);
    }
}


?>