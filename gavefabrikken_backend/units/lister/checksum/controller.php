<?php

namespace GFUnit\lister\checksum;
use GFBiz\units\UnitController;
use GFCommon\Utils\DatabaseConnection;

class Controller extends UnitController
{

    public function __construct() {
        parent::__construct(__FILE__);
    }

    public function index() {



    }

    public function orderlist() {

        ini_set('memory_limit','2048M');
        set_time_limit(0);

        \GFCommon\DB\CronLog::startCronJob("OrderChecksum");

        $op = fopen(getcwd()."/units/lister/checksum/cheksumlist-aakj453kjvfm2m7853.txt", 'w');
        $dataList = DatabaseConnection::Query("SELECT `order`.id as id, md5(CONCAT(company_name, company_cvr, `order`.shopuser_id, user_username, user_email, user_name, present_id, present_model_id, GROUP_CONCAT(attribute_value))) as checksum FROM `order`, order_attribute WHERE `order`.id = order_attribute.order_id && `order`.id GROUP BY `order`.id order by `order`.id asc, order_attribute.id asc",null);
        $count = 0;
        if(count($dataList) > 0) {
            foreach ($dataList as $index => $row) {
                fwrite($op, "order".$row["id"].":".$row["checksum"]."\r\n");
                $count++;
            }
        }
        fclose($op);
        echo "Wrote ".$count." items";

        \response::silentsuccess();
        
    }


}