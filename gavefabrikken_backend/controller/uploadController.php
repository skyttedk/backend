<?php
class uploadController Extends baseController {
    public function Index() {


    }
    public function file()
    {
        $result =  Upload::rawFile($_FILES,"files/temp/");

        if($result){
            echo json_encode(array("status"=>1,"path"=>$result));
        } else {
            echo json_encode(array("status"=>0));
        }

    }

    public function logo()
    {
        echo Upload::uploadFile($_FILES,"gavefabrikken_backend/views/media/logo","");
    }
    public function giftImg()
    {

        if($_GET["target"] == "variant"){
            echo Upload::uploadFile($_FILES,"gavefabrikken_backend/views/media/type",200);
        } else if($_GET["target"] == "present"){
            $result = Upload::uploadFileGifts($_FILES,"../views/media/temp",1000);

            echo json_encode($result);
        }

    }
    public function shop()
    {
        echo Upload::uploadFile($_FILES,"../views/media/logo",200);
    }
    public function presentation()
    {
        echo Upload::uploadFileP($_FILES,"../views/media/presentation",1000);
    }
    public function presentationPdf()
    {
        echo Upload::uploadPdf($_FILES,"../views/media/presentation","15");
    }
    public function presentationSmall()
    {
        echo Upload::uploadFileP($_FILES,"../views/media/presentation",500);
    }


}

?>