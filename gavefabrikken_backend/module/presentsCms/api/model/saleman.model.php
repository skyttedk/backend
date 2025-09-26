<?php
include "service/db.php";

class saleman
{
    public static function readAll($post)
    {
          $dbConn = new db;
          $sql = "select * from presentation_sale_profile where lang = ".$post["lang"]." order by name ";
          return $dbConn->get($sql);
    }
}