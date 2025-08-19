<?php

namespace GFUnit\pim\sync;
use GFBiz\units\UnitController;

class Datamapping extends UnitController
{

    public function __construct()
    {
        parent::__construct(__FILE__);

    }
    public function channelsSingleItems($pimData, $id = ""){
        $data = json_decode($pimData);
        var_dump($data);


    }




public function channelsItems($pimData,$id="")
    {

  //  echo "<br><br><br><br><br><br><br><br><br>";
        $mappedData = [];
        $data = json_decode($pimData);
        $html = $img = "";


        for($i=0;$i<sizeof($data->data);$i++){

            $mappedData[$i]["id"] =  $data->data[$i]->id;
            $itemnr =  $data->data[$i]->attributes->product_no->value ?? false ? $data->data[$i]->attributes->product_no->value : "";
            if($id != ""){
                if($itemnr != $id){
                    continue;
                }
            }

    //  var_dump($data->data[$i]);
            //updated_at
            $mappedData[$i]["updated_at"] =  $data->data[$i]->attributes->updated_at->value ?? false ? $data->data[$i]->attributes->updated_at->value : "";

            //Supplier
            $mappedData[$i]["supplier"] =  $data->data[$i]->attributes->supplier->value ?? false ? $data->data[$i]->attributes->supplier->value : "";
            //Status
            $mappedData[$i]["status"] =  $data->data[$i]->attributes->status->value ?? false ? $data->data[$i]->attributes->status->value : "";
            //Storeview
            $storeview = $data->data[$i]->attributes->storeview ?? false ? $data->data[$i]->attributes->storeview : "";
            if($storeview  != "")
            {
                foreach ($storeview as $storeviewItem){
                    $mappedData[$i]["storeview"][] = $storeviewItem->value;
                }
            } else {
                $mappedData[$i]["storeview"] = "";
            }
            // varenr
            $mappedData[$i]["itemnr"] = $data->data[$i]->attributes->product_no->value ?? false ? $data->data[$i]->attributes->product_no->value : "";
            // Erp product name
            $mappedData[$i]["erp_product_name"] = $data->data[$i]->attributes->erp_product_name->value ?? false ? $data->data[$i]->attributes->erp_product_name->value : "";
            // Product type
            $mappedData[$i]["product_type"]   =    $data->data[$i]->attributes->product_type->value ?? false ? $data->data[$i]->attributes->product_type->value : "";
            // overskrift
            $mappedData[$i]["product_name_da"] =   $data->data[$i]->attributes->product_name_da->value ?? false ? $data->data[$i]->attributes->product_name_da->value : "";
            $mappedData[$i]["product_name_en"] =   $data->data[$i]->attributes->product_name_en->value ?? false ? $data->data[$i]->attributes->product_name_en->value : "";
            $mappedData[$i]["product_name_no"] =   $data->data[$i]->attributes->product_name_no->value ?? false ? $data->data[$i]->attributes->product_name_no->value : "";
            $mappedData[$i]["product_name_se"] =   $data->data[$i]->attributes->product_name_se->value ?? false ? $data->data[$i]->attributes->product_name_se->value : "";
            // kort beskrivelse
            $mappedData[$i]["short_description_da"] =    $data->data[$i]->attributes->short_description_da->value ?? false ? $data->data[$i]->attributes->short_description_da->value : "";
            $mappedData[$i]["short_description_en"] =    $data->data[$i]->attributes->short_description_en->value ?? false ? $data->data[$i]->attributes->short_description_en->value : "";
            $mappedData[$i]["short_description_no"] =    $data->data[$i]->attributes->short_description_no->value ?? false ? $data->data[$i]->attributes->short_description_no->value : "";
            $mappedData[$i]["short_description_se"] =    $data->data[$i]->attributes->short_description_se->value ?? false ? $data->data[$i]->attributes->short_description_se->value : "";
            // lang beskrivelse
            $mappedData[$i]["description_da"] =    $data->data[$i]->attributes->description_da->value ?? false ? $data->data[$i]->attributes->description_da->value : "";
            $mappedData[$i]["description_en"] =    $data->data[$i]->attributes->description_en->value ?? false ? $data->data[$i]->attributes->description_en->value : "";
            $mappedData[$i]["description_no"] =    $data->data[$i]->attributes->description_no->value ?? false ? $data->data[$i]->attributes->description_no->value : "";
            $mappedData[$i]["description_se"] =    $data->data[$i]->attributes->description_se->value ?? false ? $data->data[$i]->attributes->description_se->value : "";
            //Category
            $category = $data->data[$i]->attributes->category ?? false ? $data->data[$i]->attributes->category : "";
            if($category  != "")
            {
                foreach ($category as $categoryItem){
                    $mappedData[$i]["category"][] = $categoryItem->value;
                }
            } else {
                $mappedData[$i]["category"] = "";
            }
            // Vejl. Udsalgspris tekst
            $mappedData[$i]["vejl_udsalgspris_tekst_da"] =    $data->data[$i]->attributes->vejl_udsalgspris_tekst_da->value ?? false ? $data->data[$i]->attributes->vejl_udsalgspris_tekst_da->value : "";
            $mappedData[$i]["vejl_udsalgspris_tekst_en"] =    $data->data[$i]->attributes->vejl_udsalgspris_tekst_en->value ?? false ? $data->data[$i]->attributes->vejl_udsalgspris_tekst_en->value : "";
            $mappedData[$i]["vejl_udsalgspris_tekst_no"] =    $data->data[$i]->attributes->vejl_udsalgspris_tekst_no->value ?? false ? $data->data[$i]->attributes->vejl_udsalgspris_tekst_no->value : "";
            $mappedData[$i]["vejl_udsalgspris_tekst_se"] =    $data->data[$i]->attributes->vejl_udsalgspris_tekst_se->value ?? false ? $data->data[$i]->attributes->vejl_udsalgspris_tekst_se->value : "";
            // image
            $mappedData[$i]["img"] = [];
            //$data->data[$i]->attributes->image_1->value ?? false ? $mappedData[$i]["img"][] = $this->getImgUrl($data->data[$i]->attributes->image_1->value) : "";
            //$img = $data->data[$i]->attributes->image_1->value ?? false ? $data->data[$i]->attributes->image_1->value : "";
            //$data->data[$i]->attributes->image_3->value ?? false ? $data->data[$i]->attributes->image_3->value : "";
            //$data->data[$i]->attributes->image_4->value ?? false ? $data->data[$i]->attributes->image_4->value : "";
            $img1 =  $data->data[$i]->attributes->image_1->value ?? false ? $data->data[$i]->attributes->image_1->value : "";
            if($img1 != ""){
                $obj = $this->getImgUrl($img1);
                $imgJ = json_decode($obj);
                $mappedData[$i]["img"][] = $imgJ->data->attributes->url;
            }
            $img2 =  $data->data[$i]->attributes->image_2->value ?? false ? $data->data[$i]->attributes->image_2->value : "";
            if($img2 != ""){
                $obj = $this->getImgUrl($img2);
                $imgJ = json_decode($obj);
                $mappedData[$i]["img"][] = $imgJ->data->attributes->url;
            }
            $img3 =  $data->data[$i]->attributes->image_3->value ?? false ? $data->data[$i]->attributes->image_3->value : "";
            if($img3 != ""){
                $obj = $this->getImgUrl($img3);
                $imgJ = json_decode($obj);
                $mappedData[$i]["img"][] = $imgJ->data->attributes->url;
            }
            $img4 =  $data->data[$i]->attributes->image_4->value ?? false ? $data->data[$i]->attributes->image_4->value : "";
            if($img4 != ""){
                $obj = $this->getImgUrl($img4);
                $imgJ = json_decode($obj);
                $mappedData[$i]["img"][] = $imgJ->data->attributes->url;
            }
            // omtanke
            $mappedData[$i]["gave_med_omtanke_da"] =    $data->data[$i]->attributes->gave_med_omtanke_da->value ?? false ? $data->data[$i]->attributes->gave_med_omtanke_da->value : "";
            $mappedData[$i]["gave_med_omtanke_en"] =    $data->data[$i]->attributes->gave_med_omtanke_en->value ?? false ? $data->data[$i]->attributes->gave_med_omtanke_en->value : "";
            $mappedData[$i]["gave_med_omtanke_no"] =    $data->data[$i]->attributes->gave_med_omtanke_no->value ?? false ? $data->data[$i]->attributes->gave_med_omtanke_no->value : "";
            $mappedData[$i]["gave_med_omtanke_se"] =    $data->data[$i]->attributes->gave_med_omtanke_se->value ?? false ? $data->data[$i]->attributes->gave_med_omtanke_se->value : "";
            // kun_hos_gavefabrikken
            $mappedData[$i]["kun_hos_gavefabrikken_da"] =    $data->data[$i]->attributes->kun_hos_gavefabrikken_da->value ?? false ? $data->data[$i]->attributes->kun_hos_gavefabrikken_da->value : "";
            $mappedData[$i]["kun_hos_gavefabrikken_en"] =    $data->data[$i]->attributes->kun_hos_gavefabrikken_en->value ?? false ? $data->data[$i]->attributes->kun_hos_gavefabrikken_en->value : "";
            $mappedData[$i]["kun_hos_gavefabrikken_no"] =    $data->data[$i]->attributes->kun_hos_gavefabrikken_no->value ?? false ? $data->data[$i]->attributes->kun_hos_gavefabrikken_no->value : "";
            $mappedData[$i]["kun_hos_gavefabrikken_se"] =    $data->data[$i]->attributes->kun_hos_gavefabrikken_se->value ?? false ? $data->data[$i]->attributes->kun_hos_gavefabrikken_se->value : "";

/*
            $img =  $data->data[$i]->attributes->image_1->value ?? false ? $data->data[$i]->attributes->image_1->value : "";
            if($img != ""){
                $obj = $this->getImgUrl($img);
                $imgJ = json_decode($obj);
                $img =  $imgJ->data->attributes->url;
                $img = "<img width='150px' src='".$img."'  />";
            }
*/


/*
            vejl_udsalgspris_tekst_da
            $budget_price_da = $data->data[$i]->attributes->budget_price_da ?? false ? $data->data[$i]->attributes->budget_price_da : "";
            if($budget_price_da != "")
            {
                foreach ($budget_price_da as $budget_price_daItem){
                    $mappedData[$i]["budget_price_da"][] = $budget_price_daItem->value;
                }
            } else {
                $mappedData[$i]["budget_price_da"] = "";
            }
*/
            // billeder

            //kunhos

            //gave med omtanke


       //     die("sdaf");

/*
            $product_name_da = $data->data[$i]->attributes->product_name_da->value ?? false ? $data->data[$i]->attributes->product_name_da->value : "";
            $description_da =  $data->data[$i]->attributes->description_da->value ?? false ? $data->data[$i]->attributes->description_da->value : "";
            $short_description_da =  $data->data[$i]->attributes->short_description_da->value ?? false ? $data->data[$i]->attributes->short_description_da->value : "";
            //$itemnr =  $data->data[$i]->attributes->product_no->value ?? false ? $data->data[$i]->attributes->product_no->value : "";


            $erpname =  $data->data[$i]->attributes->erp_product_name->value ?? false ? $data->data[$i]->attributes->erp_product_name->value : "";
            $img =  $data->data[$i]->attributes->image_1->value ?? false ? $data->data[$i]->attributes->image_1->value : "";
            if($img != ""){
                $obj = $this->getImgUrl($img);
                $imgJ = json_decode($obj);
                $img =  $imgJ->data->attributes->url;
                $img = "<img width='150px' src='".$img."'  />";
            }

            //  echo $data->data[$i]->attributes->updated_at->value;

            // echo $data->data[$i]->attributes->product_no->value;
            $html.="<table  >
                <tr><td width='100'>Img</td><td>".$img."</td></tr>
                <tr><td width='100'>Varenr</td><td>".$itemnr."</td></tr>
                <tr><td width='100'>ERP navn</td><td>".$erpname."</td></tr>
                <tr><td width='100'>Overskrift</td><td>".$product_name_da."</td></tr>
                <tr><td width='100'>Kort beskrivelse</td><td>".$short_description_da."</td></tr>
                <tr><td width='100'>Lang beskrivelse</td><td>".$description_da."</td></tr>
                
                </table>";
*/
        }
       // print_R($mappedData);
        return $mappedData;
      //  return $html."<hr>";

    }
    private function getImgUrl($id){
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://gavefabrikken.kontainer.com/jsonapi/v2/dam/files/'.$id.'/cdn');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "{\n  \"data\": {\n    \"type\": \"cdn\"\n  }\n}");

        $headers = array();
        $headers[] = 'Accept: application/vnd.api+json';
        //$headers[] = 'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiI1NDMiLCJqdGkiOiI1M2U4NzA2NDY4MzNjNTg0NTVjZGZiOTUxYmM2NzIxZTU5MTAwZmEzYjNlYWM5MjExOTQ0YWIwOWIzYjQxZjE1MGY5NjdiNTA5MzQzMmQzOCIsImlhdCI6MTY3NzUwNjIyOS45OTkyLCJuYmYiOjE2Nzc1MDYyMjkuOTk5MjAyLCJleHAiOjE3NDA1NzgyMTUuMDA1OTk4LCJzdWIiOiIzMTQ3OSIsInNjb3BlcyI6W119.YN1Rz6lar3-dIWKLe_gN6rm-5MB85c1nt8iYNTiup-gPXyoGW_8I2Da-Ju-jj_Srr9cDuYv_3C6WerGxqLarVQ0_ztdgNwh69SjsBcrmnHedaaHgM7hhV9C4gWCBQN0cIHcNTAYL5mnpYGuh-iot1uIv0NoK-cDquj23qwIWwgh-CgzIzFafXpTb_nHn3-QfXvAJTO2l3zbnjLRGiO2-7sl3CVbutJWUGaoiRyYM9e9aAmIr1H25RJRhI24mBoAPoiiFr0JCVv8MPRWGgd5sC0WUcFrf-jYvx1bu_wiwQ8PuQdJUS0ibpgJffY4z6G_zDhcD3_1mhk_u7k45SUaCvu1q1aeSK3YScYlR-_iCckN53M7AgKK0ScnF7UHtZYFecXG5Mb2qMoUFcnHTQG8tm4qpFv5pM2TLYyenzYlDVy4I_1VNAe9xV7d03BrUx1ldO3BwVKcJJQXJtJUv6VUXweb2FUpS9sm7Guz_USYcd2koFFPTpRteNOippnqorzYyMgn5tCuKHGc4SLN2X9fuNVVanVImKkA3Y8KLIW-VbX2CyLs_B4aHOy4Z0RrwahNRYLxIPXPJ_1FEgY_SZqYecIh90mt7AHgUrmqB4UHqoPhV3QxX3TQtgDPou3-U_2ZhN24djM6yNboDnWK61erSaUn7sIdgP3MmGguf9BMR7Ok';
        $headers[] = 'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiI1NDMiLCJqdGkiOiI3ZTg2ZThhYWU3NjkwZjcxOTk4ZGMwYzNjYTQ0MzBiOTEyNTU5MDk4ZjNjZGI1YjRiNWYyZjU0NmZlNjYzNWE0M2M4NGUyYjllN2YwMGU5ZiIsImlhdCI6MTc0MDY1MTM1MS41ODE1OTUsIm5iZiI6MTc0MDY1MTM1MS41ODE1OTgsImV4cCI6MTgwMzcyMzMyMi4wMjIzNzIsInN1YiI6IjMxNDc5Iiwic2NvcGVzIjpbXX0.P6cA4NwJoEzSchfvLQY_cjHd_g4j49o1iUzeZ85P9Jf6OZix5lYzVfJieqbUIA6sZaNUHbrklIRBuKNV8jDc06sJXE0SVpB8oJUvWAQQPqxh7V-he1ExxVi70USE8lLRVdO1MRGh8ELIVq1wWndf-1_F_HKKJFzB3afEQhLc4evrxu8MZmVtEGmryFUorVnzAy8VNO7XhpW6a9evLggOtnocJzxF_9kGmpCpPvxpkTFJ9ThLlU0ol5mciNjUUm4upH0Z3nkUcFoxCAXrb1edG3j_Fjkily6qkFqIyQUnCBJSZXuWvxA5Xb2qIXmRpD1jbgLvyJJn4QybFaj3401NBb_QtTCcCorzMmqcq5krORpq-q2nzXS76dzTgRdgKCL3o87o0wjrY5X_LIXxsvBr4PjkrD-RUSxahpsq44SFqGDg74Ue56XtZVnbMGkA9A86bKyZZRZcRnwlWwzX9EiDopGYUHdV05HJtucil1wZ0RSbkwwtCQIz1nPqEeWqM7igVoaG0DSPTHAlVkb4PeI3zgqL5sHQioVflwQCJmlVtK5cylq-FJXykWW_fSk5XFmy1ihJgUVqxvtSZjWUJpEfNGJzJnWQTSYTmnJdFb2c9qr15eP-ZOG6RR7td71XICbZXi3zKYqlJ6YdYiy1RBdaz3ThuQ5vLmpANLblyB9qUOI';
        $headers[] = 'Content-Type: application/vnd.api+json';
        $headers[] = 'X-Csrf-Token: ';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);

        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        return $result;
    }
}