<?php
include("db.php");


class Orm extends Dbsqli
{
    public function read($qurey)
    {
        print_r(parent::get($qurey));
    }
}





?>