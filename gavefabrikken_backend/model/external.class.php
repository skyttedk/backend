<?php
// Model language
// Date created  Wed, 30 Mar 2016 00:05:31 +0200
// Created by Bitworks
class External extends BaseModel {

    static $table_name  = "ordre";
    static $primary_key = "id";
    static $connection = 'julegavekortet';


    public static function setConnection($connectionname) {
            self::$connection = $connectionname;
    }


}
?>