<?php
include "model/saleman.model.php";
class salepersonController
{
    public function getAll()
    {
        return saleman::readAll($_POST);
    }
}?>