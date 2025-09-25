<?php

Class PtimageController Extends baseController {

    public function Index()
    {
        echo "NO ACCESS!";
    }
    public function deleteAll()
    {

    }



    public function save()
    {
        $shopId = $_POST["shopId"];
        $data = $_POST["data"];
        $order = $_POST["order"];
        if($order == 1){
            Dbsqli::setSql2("delete from pt_image where shop_id =".$shopId);
        }


        $ptImage = new Ptimage();
        $ptImage->shop_id = $shopId;
        $ptImage->data = $data;
        $ptImage->sort = $order;
        $ptImage->save();
        response::success(json_encode($ptImage));

    }

}
?>