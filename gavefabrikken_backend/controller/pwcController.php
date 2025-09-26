<?php

class PwcController Extends baseController
{

    public function Index()
    {
    }
    public function create()
    {
        $data = $_POST["postData"];

        $data["post_data"] = base64_encode(json_encode($data));
        $pwc = new Pwc($data);
        $return = $pwc->save();
        $dummy = [];
        response::success($return);

        //response::success(make_json("response", $return));
    }


}