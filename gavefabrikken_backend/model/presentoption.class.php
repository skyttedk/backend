<?php
// Model PresentMedia
// Date created  Mon, 16 Jan 2017 15:29:24 +0100
// Created by Bitworks
//***************************************************************
//   (PRI) id                            int(11)             NO
//   (MUL) present_id                    int(11)             NO
//   (   ) media_path                    varchar(1024)       NO
//   (   ) index                         int(11) unsigned    YES
//***************************************************************
class PresentOption extends BaseModel
{
    static $table_name = "present_options";
    static $primary_key = "id";



}