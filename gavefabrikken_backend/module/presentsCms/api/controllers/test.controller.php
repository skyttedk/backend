<?php

class test
{
    public function go()
    {
       $res = array(
            'status' => 1,
            'msg' => "virker");
       return json_encode($res);
    }
}


?>