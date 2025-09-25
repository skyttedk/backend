<?php

ini_set('max_execution_time', 300); //300 seconds = 5 minutes
ini_set('memory_limit','2048M');
ini_set('display_errors', 1);
error_reporting(E_ALL);
include("sms/db/db.php");




class queue
{
    private $db;

    public function __construct()
    {
        $this->db = new Dbsqli();
        $this->db->setKeepOpen();
    }

    public function createQueueShopItems()
    {
        $sql = "SELECT max(job_id) as job FROM rm_shop_data";
        $rs = $this->db->get($sql);
        $jobID = sizeof($rs["data"]) == 0 ? 0 : $rs["data"][0]["job"] * 1 + 1;

        $sql = "SELECT present_model.`model_present_no`,shop_id FROM `shop_present` 
                left join present_model on present_model.present_id = shop_present.present_id
                where `shop_id` in (SELECT shop_id FROM `cardshop_settings` WHERE `language_code` = 5) &&
                present_model.language_id = 1";
        $rs = $this->db->get($sql);

        foreach ($rs["data"] as $ele) {
            $sql = "INSERT INTO `rm_shop_data` (`job_id`,`item_nr`, `shop_id`) VALUES ( " . $jobID . ", ' " . $ele["model_present_no"] . "'," . $ele["shop_id"] . " )";
            $this->db->set($sql);
        }
    }
}