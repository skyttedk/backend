<?php

class Dbsqli
{

    public  $errorMsg;
    public  $debugMode;
    public  $conn;
    public  $returnDataFormat = "array";


    public function connect() {
        $this->conn = new mysqli(GFConfig::DB_HOST, GFConfig::DB_USERNAME, GFConfig::DB_PASSWORD,GFConfig::DB_DATABASE);
        if ($this->conn->connect_error) {
             $this->errorMsg = $this->conn->connect_error;
             return false;
        } else {
             return true;
       }
    }
    public function getErrorMsg()
    {
        return $this->errorMsg;
    }

    public function get($query)
    {
        $this->connect();
        $result = $this->conn->query($query);
        $this->conn->close();
        if(!$result){
            return array("status"=>"0","msg"=>"");
        } else {
            while ($rows = $result->fetch_assoc()) {
                $row[] = $rows;
            }
            return array("status"=>"1","data"=>$row);
        }
    }
    public function set($query)
    {
        $this->connect();
        $result = $this->conn->query($query);
        $this->conn->close();
        if($result){
            return array("status"=>"0","msg"=>"");
        } else {
            return array("status"=>"1","data"=>"");
        }
    }
}






?>