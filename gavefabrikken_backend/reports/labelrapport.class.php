<?php

class Labelrapport Extends reportBaseController {
    private $shopID;
    private $attributeID;
    private $csvData = array();

    function __construct($shopId,$attributeId) {
        $this->shopID = $shopId;
        $this->attributeID = $attributeId;
    }
    public function make() {
        $this->csvData[] = ["Gavenr.","Gave","Farve / Variant","Navn","Firma navn"];


        $detectToInsertEmptyLine = "";
        $rapRs = ShopUser::find_by_sql("SELECT `shop_user`.id, fullalias, `present_model`.model_name , `present_model`.`model_no`, `user_attribute`.`attribute_value` FROM `shop_user`
            inner JOIN `order` on `shop_user`.`id` = `order`.`shopuser_id`
            inner join `present_model` on `present_model`.`model_id` = `order`.present_model_id
            inner join  user_attribute on `shop_user`.`id` = `user_attribute`.`shopuser_id`
            WHERE
            `shop_user`.`shop_id` = ".$this->shopID." AND
            `shop_user`.`blocked` = 0 and
            `shop_user`.`is_demo` = 0 AND
            `user_attribute`.attribute_id = ".$this->attributeID." and
            `present_model`.language_id = 1
            order by `user_attribute`.`attribute_value`, fullalias + 0 ");

        foreach ($rapRs as $rapItem) {
            if($detectToInsertEmptyLine == "" ){ $detectToInsertEmptyLine = $rapItem->attribute_value;  }
            if($detectToInsertEmptyLine != $rapItem->attribute_value) {
                $detectToInsertEmptyLine = $rapItem->attribute_value;
                $this->csvData[] = ["","","","",""];
            }

//            echo $name = $this->getName($rapItem->id)[0]->attribute_value;
            $this->csvData[] = ["Gave nr. ".$rapItem->fullalias, utf8_decode($rapItem->model_name),utf8_decode($rapItem->model_no),utf8_decode($this->getName($rapItem->id)[0]->attribute_value),utf8_decode($rapItem->attribute_value) ];

        }
    }
    public function arrayToCsvDownload( ) {
        $filename = "label-rapport-".$this->shopID.".csv";
        $delimiter = ";";
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '";');

        // open the "output" stream
        // see http://www.php.net/manual/en/wrappers.php.php#refsect2-wrappers.php-unknown-unknown-unknown-descriptioq
        $f = fopen('php://output', 'w');

        foreach ($this->csvData as $line) {
            fputcsv($f, $line, $delimiter);
        }
    }

    private function getName($shopuserID) {
        return UserAttribute::find_by_sql("SELECT *  FROM `user_attribute` WHERE `shopuser_id` = " . $shopuserID . " AND `is_name` = 1 ORDER BY `shopuser_id`");
    }

}

?>