<?php
class Dbsqli_2017
{

    public  $errorMsg;
    public  $debugMode;
    public  $conn;
    public  $returnDataFormat = "array";
    public  $insertId;
    public  $keepOpen = false;

    public function __construct() {
        $this->conn = new mysqli("localhost", "root", "Y2E3ZDZiNGIyOTZl","gavefabrikken_2017");
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
        $result = $this->conn->query($query);
        if($this->keepOpen == false){
            $this->conn->close();
        }
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
        $result = $this->conn->query($query);
        $this->lastInsertId = $this->conn->insert_id;
        if($this->keepOpen == false){
            $this->conn->close();
        }

        if($result){
            return array("status"=>"0","msg"=>"");
        } else {
            return array("status"=>"1","data"=>"");
        }
    }
    public function getLastInsetId()
    {
        return $this->lastInsertId;
    }
    public function setCloseDb()
    {
        $this->conn->close();
        //$this->keepOpen = false;
    }
    public function setKeepOpen()
    {
        $this->keepOpen = true;
    }

}






?>