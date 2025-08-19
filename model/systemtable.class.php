<?php
// Model SystemLog
// Date created  Mon, 16 Jan 2017 15:29:59 +0100
// Created by Bitworks
//***************************************************************
//   (   ) TABLE_CATALOG
//   (   ) TABLE_SCHEMA
//   (   ) TABLE_NAME
//   (   ) TABLE_TYPE
//   (   ) ENGINE
//   (   ) VERSION
//   (   ) ROW_FORMAT
//   (   ) TABLE_ROWS
//   (   ) AVG_ROW_LENGTH
//   (   ) DATA_LENGTH
//   (   ) MAX_DATA_LENGTH
//   (   ) INDEX_LENGTH
//   (   ) DATA_FREE
//   (   ) AUTO_INCREMENT
//   (   ) CREATE_TIME
//   (   ) UPDATE_TIME
//   (   ) CHECK_TIME
//   (   ) TABLE_COLLATION
//   (   ) CHECKSUM
//   (   ) CREATE_OPTIONS
//   (   ) TABLE_COMMENT
//***************************************************************
class SystemTable extends BaseModel {
    static $table_name  = "INFORMATION_SCHEMA.`TABLES`";
   var       $i=0;
    static $has_many = array(
                     array("INFORMATION_SCHEMA.`COLUMN`",
                           'class_name' => 'SystemColumn'
                           )
        );

}
?>