<?php

class qrRapport {
    public function run($shopID) {

        header('Content-Type: application/csv');
        header('Content-Disposition: attachement; filename="qrlog-' . time() . '.csv"');

         $sql = "select app_log.*, app_users.name as app_users_name,present_model.model_name, present_model.model_no,present_model.fullalias,order_history.`user_name`,order_history.`user_email` from app_log
        inner join order_history on app_log.order_id = order_history.order_no
        inner join app_users on  app_log.app_username =  app_users.id

        inner join present_model on order_history.present_model_id =  present_model.model_id and present_model.language_id = 1
        where app_log.shop_id = ".$shopID." order by id desc";

        $rs = Dbsqli::getSql2($sql);
        $header = array("Dato","Udlevering","Handling","Noter","Gave modtager navn","Gave modtager email","Gave","Model","Gave alias" );
        echo implode(';', $header) . "\n";

        foreach($rs as $ele){
        $record =  array($ele["created_date"],$ele["app_users_name"],$ele["log_event"],$ele["log_description"],$ele["user_name"],$ele["user_email"],$ele["model_name"],$ele["model_no"],$ele["fullalias"] );
        echo implode(';', $record) . "\n";

        }

    }
}

?>