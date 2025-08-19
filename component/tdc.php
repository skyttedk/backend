<?php

ini_set('max_execution_time', 300); //300 seconds = 5 minutes
ini_set('memory_limit','2048M');
ini_set('display_errors', 1);
error_reporting(E_ALL);
include("sms/db/db.php");

$tdc = new tdc;
$tdc->run();


class tdc
{
    private $db;
    private $shop_id = 3605;
    private $target = 25101;
    public function __construct()
    {
        $this->db = new Dbsqli();
        $this->db->setKeepOpen();
    }

    public function run()
    {
        $sql = "SELECT * FROM `user_attribute` WHERE 
                                    
                                    attribute_id = 20726 and 
                                    shop_id = ".$this->shop_id;

        $rs =  $this->db->get($sql);

        foreach ($rs["data"] as $ele){

            $pieces = explode("@", $ele["attribute_value"]);
            if(is_array($pieces)){
                if(sizeof($pieces) > 1 ){
                  //  echo  $sql = "update `user_attribute` set attribute_value =  '".strtolower($pieces[1])."' where shop_id = ".$this->shop_id." and attribute_id = ".$this->target." and shopuser_id=".$ele["shopuser_id"];
                  //  echo "<br>";
                   // $this->db->set($sql);
                }

            }
            
            //$this->db->set($sql);
        }


    }
}