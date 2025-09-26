<?php
include "service/db.php";

class admin
{
    public static function readAll()
    {
          $dbConn = new db;
          $sql = "select * from stream_admin";
          return $dbConn->get($sql);
    }
    public static function checkToken($data)
    {
        $dbConn = new db;
        $sql = "select * from stream_admin where link = ?";
        $values = array($data["token"]);
        $res = $dbConn->get($sql,"s",$values);

        if(sizeofgf($res) > 0){
            if($res[0]["active"] == 0){
                return array("active"=>"0","none"=>$data["none"],"data"=>$res);
            } else {
              return array("active"=>"1","none"=>$data["none"],"data"=>$res);
            }

        } else {
             return array("active"=>"0","none"=>$data["none"],"data"=>$res);
        }

    }


    public static function create($data)
    {
        $dbConn = new db;
        $values = array($data["company_name"],$data["link"],$data["time_to_show"]);
        $sql = "INSERT INTO stream_admin (company_name,link,time_to_show) values (?,?,?)";
        return $dbConn->set($sql,"sss",$values);

    }
    public static function update($data)
    {
        $dbConn = new db;
        $values = array($data["company_name"],$data["time_to_show"],$data["id"]);
        $sql = "update stream_admin set company_name = ?, time_to_show = ? where id = ?";
        return $dbConn->set($sql,"ssi",$values);

    }
    public static function updateStatus($data)
    {
        $dbConn = new db;
        $values = array($data["active"],$data["id"]);
        $sql = "update stream_admin set active = ? where id = ?";
        return $dbConn->set($sql,"si",$values);

    }
    public static function deleteElement($data)
    {
        $dbConn = new db;
        $values = array($data["id"]);
        $sql = "delete from stream_admin where id = ?";
        return $dbConn->set($sql,"i",$values);

    }
    public static function getAdmin()
    {
        $dbConn = new db;
        $sql = "select * from stream_admin where id = ?";
        $values = array($_POST["id"]);
        return $dbConn->get($sql,"s",$values);
    }


}


?>