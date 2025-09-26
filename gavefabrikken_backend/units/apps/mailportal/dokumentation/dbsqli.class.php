<?php

class Dbsqli
{
    public static function getSql($data)
    {

       $conn = new mysqli(GFConfig::DB_HOST, GFConfig::DB_USERNAME, GFConfig::DB_PASSWORD,GFConfig::DB_DATABASE);
       if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        $row =array();
        $result = $conn->query($data);
        while ($rows = $result->fetch_assoc()) {
            $row[] = $rows;
        }

        echo json_encode(array("status"=>"1","data"=>$row));
        $conn->close();



    }
     public static function getSql2($data)
    {

        $conn = new mysqli(GFConfig::DB_HOST, GFConfig::DB_USERNAME, GFConfig::DB_PASSWORD,GFConfig::DB_DATABASE);
       if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        $row =array();
        $result = $conn->query($data);
        while ($rows = $result->fetch_assoc()) {
            $row[] = $rows;
        }

        return $row;
        $conn->close();
    }

    public static function setSql2($data)
    {

        $conn = new mysqli(GFConfig::DB_HOST, GFConfig::DB_USERNAME, GFConfig::DB_PASSWORD,GFConfig::DB_DATABASE);
       if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        $result = $conn->query($data);
        return  $result;
        $conn->close();
    }
    public static function protect($str)
    {
        $res = str_ireplace( array( '\'', '"', ',' , ';', '<', '>','=',':','+','-','TRUNCATE','delete' ), ' ', $str);
        return $res;
    }
    public static function getConn()
    {
        
        $conn = new mysqli(GFConfig::DB_HOST, GFConfig::DB_USERNAME, GFConfig::DB_PASSWORD,GFConfig::DB_DATABASE);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        return $conn;
    }


}






?>