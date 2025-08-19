<?php

namespace GFUnit\pim\kontainerutilities;

use GFBiz\units\UnitController;
use GFUnit\pim\sync\kontainerCom;
use GFUnit\pim\sync\gavevalg;




class Controller extends UnitController
{
    public function __construct()
    {
        parent::__construct(__FILE__);
  

    }
    public function demo()
    {
        echo "demo";
    }
    public function getNavSaleprice(){
        $itemno = $_POST["ITEMNO"];
        $res = \NavisionItem::find_by_sql(" SELECT * FROM `navision_salesprice` WHERE `item_no` = ''".$itemno."'' ORDER BY `navision_salesprice`.`language_id` ASC ");
        echo json_encode($res);
    }


    public function getNAV()
    {
        $itemno = $_POST["ITEMNO"];
        $res = \NavisionItem::find_by_sql(" select * from navision_item where no = '".$itemno."'");
        echo json_encode($res);
    }

    public function syncManuelItem_us()
    {

        $ITEMNO = "240138";
        $PIMID = 8808001;

        $kontainer = new KontainerCom;
        $gavevalg  = new Gavevalg;

        $res = $kontainer->getDataSingle("",$PIMID);
        $data = json_decode($res);

        if($PIMID == "8887207"){
            //          print_r($data);
//            die("asdf");
        }
//


        $ele = $data->data->attributes;
        $itemnr = $ele->product_no->value ?? false ? $data->data->attributes->product_no->value : "";
        $type = $ele->product_type->value ?? false ? $ele->product_type->value : "";
        $erp_product_name_da =  $ele->erp_product_name_da->value ?? false ? $ele->erp_product_name_da->value : "";
        $itemSyncDato = $ele->updated_at->value;
        $itemSyncDato = date('Y-m-d H:i:s', strtotime($itemSyncDato . ' + 0 hours'));
        $jsonBody = base64_encode(json_encode($res));
        $syncStart = date('Y-m-d H:i:s');

        $result = $gavevalg->singleSync($res,$PIMID);


        $status = $result["status"];
        $msg = json_encode($result["msg"]);


        if($type == "Product"){
      //     $sql = "insert into pim_sync_queue ( pim_id,nav_name_da,item_nr,type,sync_dato,body,sync_start,sync_end,error_msg,error,is_handled) values ($PIMID,'$erp_product_name_da','$itemnr',1,'$itemSyncDato','$jsonBody','$syncStart','$syncStart','$msg','$status',1)";
        }
        if($type == "Group"){
        //    $sql = "insert into pim_sync_queue ( pim_id,nav_name_da,item_nr,type,sync_dato,body,sync_start,sync_end,error_msg,error,is_handled) values ($PIMID,'$erp_product_name_da','',2,'$itemSyncDato','$jsonBody','$syncStart','$syncStart','$msg','$status',1)";
        }
       // \Dbsqli::setSql2($sql);

        echo json_encode($result);


    }


    public function syncManuelItem()
    {
        $PIMID =  $_POST["PIMID"];
        $ITEMNO = $_POST["ITEMNO"];
        $kontainer = new KontainerCom;
        $gavevalg  = new Gavevalg;

        $res = $kontainer->getDataSingle("",$PIMID);
        $data = json_decode($res);



        $ele = $data->data->attributes;
        $itemnr = $ele->product_no->value ?? false ? $data->data->attributes->product_no->value : "";
        $type = $ele->product_type->value ?? false ? $ele->product_type->value : "";
        $erp_product_name_da =  $ele->erp_product_name_da->value ?? false ? $ele->erp_product_name_da->value : "";
        $itemSyncDato = $ele->updated_at->value;
        $itemSyncDato = date('Y-m-d H:i:s', strtotime($itemSyncDato . ' + 0 hours'));
        $jsonBody = base64_encode(json_encode($res));
        $syncStart = date('Y-m-d H:i:s');

        $result = $gavevalg->singleSync($res,$PIMID);


        $status = $result["status"];
        $msg = json_encode($result["msg"]);

        $sql = "update pim_sync_queue set is_handled = 1 where item_nr = '".$itemnr."'";
        \Dbsqli::setSql2($sql);

        if($type == "Product"){
            $sql = "insert into pim_sync_queue ( pim_id,nav_name_da,item_nr,type,sync_dato,body,sync_start,sync_end,error_msg,error,is_handled) values ($PIMID,'$erp_product_name_da','$itemnr',1,'$itemSyncDato','$jsonBody','$syncStart','$syncStart','$msg','$status',1)";
        }
        if($type == "Group"){
            $sql = "insert into pim_sync_queue ( pim_id,nav_name_da,item_nr,type,sync_dato,body,sync_start,sync_end,error_msg,error,is_handled) values ($PIMID,'$erp_product_name_da','',2,'$itemSyncDato','$jsonBody','$syncStart','$syncStart','$msg','$status',1)";
        }
        \Dbsqli::setSql2($sql);

       echo json_encode($result);


    }
    public function gavevalgItemActiveState(){
        $PIMID =  $_POST["PIMID"];
        $sql = "select id from present where pim_id=".$PIMID;
        $result = \Dbsqli::getSql2($sql);
        if(sizeof($result) > 0){
            echo json_encode($result[0]);
        } else {
            
        }
    }


    public function preview()
    {
        $PIMID =  $_POST["PIMID"];
        $sql = "select id from present where pim_id=".$PIMID;
        $result = \Dbsqli::getSql2($sql);
        if(sizeof($result) > 0){
            echo json_encode($result[0]);
        } else {
            $kontainer = new KontainerCom;
            $pimData = $kontainer->getDataSingle("",$PIMID);
            $data = json_decode($pimData);
          //  print_r($data->data->attributes);

            echo $product_name_da =  $data->data->attributes->product_name_da->value ?? "";
            echo $description_da = $data->data->attributes->description_da->value ?? "";
            echo $gave_med_omtanke_da = $data->data->attributes->gave_med_omtanke_da->value ?? 0;
            echo $kun_hos_gavefabrikken_da = $data->data->attributes->kun_hos_gavefabrikken_da->value ?? 0;
            // $logoID =  $data->data->attributes->logo->meta->resource_item_id;


            //klogo_9041521

            $imgID = $data->data->attributes->image_1->value;
            $img1 = $kontainer->getImgUrl($imgID);
            print_r($img1);

            //echo $product_name_da =   $data->data[0]->attributes->product_name_da->value ?? false ? $data->data[0]->attributes->product_name_da->value : "";






            echo json_encode([]);
        }


    }
    public function copyitem()
    {

        $PIMID =  $_POST["PIMID"];
        $ITEMNO = $_POST["ITEMNO"];

        $copy = new copyitem;
        $copy->copyitem($ITEMNO,$PIMID);
    }


}