<?php
include "service/db.php";

class pdf
{
    public static function readAll($options)
    {
          //if($option)
          $dbConn = new db;
          $sql = "select * from presentation_sale_pdf";
          return $dbConn->get($sql);
    }

    public static function createSlide($post)
    {
          $dbConn = new db;
          $values = array($post["id"],$post["presentation_id"],$post["setting"],$post["sort"]);
          $sql =  "INSERT INTO presentation_sale_pdf (present_id, author, presentation_id,setting,sort) VALUES (?, 1, ?,?,?)";
          return $dbConn->set($sql,"is",$values);
    }
}

?>

