<?php

Class ptAdminController Extends baseController {
   public function index() {
        $this->registry->template->show('ptAdmin_view');
   }
   public function getSalePersonList(){

       if (!is_int($_GET['localisation']*1)) {
           Response::error("Invalid localisation parameter: must be an integer");
       }
       $localisation = $_GET["localisation"];

     $res = PresentationSaleProfile::find_by_sql("select * from presentation_sale_profile where lang = ".$localisation." order by name");
        response::success(json_encode($res));
   }

   public function loadPresentPrice2(){
       $shopId = $_POST["id"];
       $langFieldName = "prices_".$_POST["lang"];
       $pricesPresentation = Present::find_by_sql("SELECT p.*, pm.media_path,pg.".$langFieldName." as pc_price, pg.active as pc_active
        
        
        
        FROM present p
        LEFT JOIN present_media pm ON pm.present_id = p.id AND pm.index = 0
        JOIN shop_present sp ON sp.present_id = p.id
        LEFT JOIN presentation_group pg ON pg.group_id = p.id AND pg.active = 1 and pg.type != 0 
        WHERE sp.shop_id = " . $shopId . " AND 
              sp.active = 1 AND 
              sp.is_deleted = 0");



       $pricesNAV = Present::find_by_sql("
        SELECT
                ni.*,
                p.model_present_no,
                p.present_id
            FROM
                navision_item ni
            INNER JOIN(
                SELECT DISTINCT
                    pm.model_present_no,
                    pm.present_id
                FROM
                    present_model pm
                INNER JOIN present p ON
                    pm.present_id = p.id
                WHERE
                    p.id IN(
                    SELECT DISTINCT
                        p2.copy_of
                    FROM
                        present p2
                    WHERE
                        p2.shop_id = " . $shopId . "
                )
            ) p
            ON
                p.model_present_no = ni.no");
       $return = array("pricesPresentation" => $pricesPresentation,"pricesNAV"=>$pricesNAV );

       response::success(json_encode($return));
   }
    public function loadPriceFromPim()
    {
        $shopId = $_POST["id"];
        $pList = $this->loadPresentPrice($shopId);
        $i = 0;
        foreach ($pList["pricesPresentation"] as $p){
            $pid =  $p->attributes["pim_id"];
          //  $konData = $this->getDataSingle(11439776);
            $konData = $this->getDataSingle($pid);
            $konData = json_decode($konData);
            $vejlPrice = $konData->data->attributes->vejl_udsalgspris_tekst_sv->value ?? "" ;
            print_r($p);
            $i++;
            if($i >1){
                die("asdf");
            }
          //  print_R($konData->data->attributes);

        }

    }

    public function getDataSingle($kontainerID)
    {
        $ch = curl_init();

        //curl_setopt($ch, CURLOPT_URL, 'https://gavefabrikken.kontainer.com/jsonapi/v2/pim/channels/17196/items?page[size]=1&filter[released_on][gt]=2023-02-19');
        curl_setopt($ch, CURLOPT_URL, 'https://gavefabrikken.kontainer.com/jsonapi/v2/pim/channels/10614/items/'.$kontainerID);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');


        $headers = array();
        $headers[] = 'Accept: application/vnd.api+json';
        $headers[] = 'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiI1NDMiLCJqdGkiOiI3ZTg2ZThhYWU3NjkwZjcxOTk4ZGMwYzNjYTQ0MzBiOTEyNTU5MDk4ZjNjZGI1YjRiNWYyZjU0NmZlNjYzNWE0M2M4NGUyYjllN2YwMGU5ZiIsImlhdCI6MTc0MDY1MTM1MS41ODE1OTUsIm5iZiI6MTc0MDY1MTM1MS41ODE1OTgsImV4cCI6MTgwMzcyMzMyMi4wMjIzNzIsInN1YiI6IjMxNDc5Iiwic2NvcGVzIjpbXX0.P6cA4NwJoEzSchfvLQY_cjHd_g4j49o1iUzeZ85P9Jf6OZix5lYzVfJieqbUIA6sZaNUHbrklIRBuKNV8jDc06sJXE0SVpB8oJUvWAQQPqxh7V-he1ExxVi70USE8lLRVdO1MRGh8ELIVq1wWndf-1_F_HKKJFzB3afEQhLc4evrxu8MZmVtEGmryFUorVnzAy8VNO7XhpW6a9evLggOtnocJzxF_9kGmpCpPvxpkTFJ9ThLlU0ol5mciNjUUm4upH0Z3nkUcFoxCAXrb1edG3j_Fjkily6qkFqIyQUnCBJSZXuWvxA5Xb2qIXmRpD1jbgLvyJJn4QybFaj3401NBb_QtTCcCorzMmqcq5krORpq-q2nzXS76dzTgRdgKCL3o87o0wjrY5X_LIXxsvBr4PjkrD-RUSxahpsq44SFqGDg74Ue56XtZVnbMGkA9A86bKyZZRZcRnwlWwzX9EiDopGYUHdV05HJtucil1wZ0RSbkwwtCQIz1nPqEeWqM7igVoaG0DSPTHAlVkb4PeI3zgqL5sHQioVflwQCJmlVtK5cylq-FJXykWW_fSk5XFmy1ihJgUVqxvtSZjWUJpEfNGJzJnWQTSYTmnJdFb2c9qr15eP-ZOG6RR7td71XICbZXi3zKYqlJ6YdYiy1RBdaz3ThuQ5vLmpANLblyB9qUOI';
        //$headers[] = 'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiI1NDMiLCJqdGkiOiI1M2U4NzA2NDY4MzNjNTg0NTVjZGZiOTUxYmM2NzIxZTU5MTAwZmEzYjNlYWM5MjExOTQ0YWIwOWIzYjQxZjE1MGY5NjdiNTA5MzQzMmQzOCIsImlhdCI6MTY3NzUwNjIyOS45OTkyLCJuYmYiOjE2Nzc1MDYyMjkuOTk5MjAyLCJleHAiOjE3NDA1NzgyMTUuMDA1OTk4LCJzdWIiOiIzMTQ3OSIsInNjb3BlcyI6W119.YN1Rz6lar3-dIWKLe_gN6rm-5MB85c1nt8iYNTiup-gPXyoGW_8I2Da-Ju-jj_Srr9cDuYv_3C6WerGxqLarVQ0_ztdgNwh69SjsBcrmnHedaaHgM7hhV9C4gWCBQN0cIHcNTAYL5mnpYGuh-iot1uIv0NoK-cDquj23qwIWwgh-CgzIzFafXpTb_nHn3-QfXvAJTO2l3zbnjLRGiO2-7sl3CVbutJWUGaoiRyYM9e9aAmIr1H25RJRhI24mBoAPoiiFr0JCVv8MPRWGgd5sC0WUcFrf-jYvx1bu_wiwQ8PuQdJUS0ibpgJffY4z6G_zDhcD3_1mhk_u7k45SUaCvu1q1aeSK3YScYlR-_iCckN53M7AgKK0ScnF7UHtZYFecXG5Mb2qMoUFcnHTQG8tm4qpFv5pM2TLYyenzYlDVy4I_1VNAe9xV7d03BrUx1ldO3BwVKcJJQXJtJUv6VUXweb2FUpS9sm7Guz_USYcd2koFFPTpRteNOippnqorzYyMgn5tCuKHGc4SLN2X9fuNVVanVImKkA3Y8KLIW-VbX2CyLs_B4aHOy4Z0RrwahNRYLxIPXPJ_1FEgY_SZqYecIh90mt7AHgUrmqB4UHqoPhV3QxX3TQtgDPou3-U_2ZhN24djM6yNboDnWK61erSaUn7sIdgP3MmGguf9BMR7Ok';
        $headers[] = 'X-Csrf-Token: ';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        return $result;
    }
   public function loadPresentPrice($id = ""){


       $shopId = $id == "" ? $_POST["id"]: $id;




       if($shopId == 817911){
            $this->loadPresentPrice2();
            return;
       }
        $langFieldName = "prices_".$_POST["lang"];
        // den her bliver brugt
        if($shopId != "0"){

           $pricesPresentation = Present::find_by_sql("

SELECT
    p.*,
    pm.media_path,
    pg.".$langFieldName." as pc_price,
    pg.active AS pc_active,
    sp.index_
FROM
    present p
LEFT JOIN present_media pm ON
    pm.present_id = p.id AND pm.index = 0
LEFT JOIN shop_present sp ON
    sp.present_id = p.id
LEFT JOIN presentation_group pg ON
    pg.group_id = p.id AND pg.active = 1 AND pg.type != 0

WHERE
    ((sp.shop_id = " . $shopId . " AND sp.active = 1 AND sp.is_deleted = 0)
    OR (p.shop_id = " . $shopId . " OR (p.shop_id = ".$shopId*-1 . " and p.active = 1 and p.deleted = 0 )  ))


              ");


        } else {


            $pricesPresentation = Present::find_by_sql("SELECT p.*, pm.media_path,pg.".$langFieldName." as pc_price, pg.active as pc_active
        FROM present p
        LEFT JOIN present_media pm ON pm.present_id = p.id AND pm.index = 0
        JOIN shop_present sp ON sp.present_id = p.id
        LEFT JOIN presentation_group pg ON pg.group_id = p.id AND pg.active = 1 and pg.type != 0
        WHERE sp.shop_id = " . $shopId . " AND
              sp.active = 1 AND
              sp.is_deleted = 0");


        }


            $pricesNAV = Present::find_by_sql("
        SELECT
                ni.*,
                p.model_present_no,
                p.present_id
            FROM
                navision_item ni
            INNER JOIN(
                SELECT DISTINCT
                    pm.model_present_no,
                    pm.present_id
                FROM
                    present_model pm
                INNER JOIN present p ON
                    pm.present_id = p.id
                WHERE
                    p.id IN(
                    SELECT DISTINCT
                        p2.copy_of
                    FROM
                        present p2
                    WHERE
                        p2.shop_id = " . $shopId . "
                )
            ) p
            ON
                p.model_present_no = ni.no");


             $return = array("pricesPresentation" => $pricesPresentation,"pricesNAV"=>$pricesNAV );
                    if($id != "" ){
                        return $return;
                    }
                    response::success(json_encode($return));
   }
   public function savePresentPrice(){
       $lang =  $_POST["lang"];
       if($lang == 1){
           $pt_price= $_POST["pt_price"];
           $shopId = $_POST["id"];
           $present = Present::find($shopId);
           $present->pt_price = json_encode($pt_price);
           $present->save();
       }
       if($lang == 4){
           $pt_price_no= $_POST["pt_price"];
           $shopId = $_POST["id"];
           $present = Present::find($shopId);
           $present->pt_price_no = json_encode($pt_price_no);
           $present->save();
       }
       if($lang == 5){
           $pt_price_se= $_POST["pt_price"];
           $shopId = $_POST["id"];
           $present = Present::find($shopId);
           $present->pt_price_se = json_encode($pt_price_se);
           $present->save();
       }
        response::success(json_encode(array()));

      //  $return = Present::find_by_sql("select * from present where shop_id =".$shopId." and active = 1 and deleted = 0");

   }


   public function deletePdf()
   {
        $shopId = $_GET["id"];
        $shop = Shop::find($shopId);
        $shop->pt_pdf = "";
 		$shop->save();
        response::success(json_encode(array()));
   }

   public function uploadPDFPaper(){
       require 'pdfcrowd.php';

       $url = $_POST["url"];
       $filename = generateRandomString(30);

       try
       {
           // create the API client instance
           $client = new \Pdfcrowd\HtmlToPdfClient("bundy0909", "c16c892a0e8a507b747419350431df64");
           $client->setJpegQuality(90);
           $client->setConvertImagesToJpeg("all");
           // run the conversion and write the result to a file
           //$savePath = $_SERVER['DOCUMENT_ROOT']."/presentation/pdf/sale".$filename.".pdf";
           $savePath = "../presentation/pdf/".$filename.".pdf";
           $client->convertUrlToFile($url, $savePath);
           $dummy = array("p"=>$filename);
           response::success(json_encode(array("file"=>$filename)));
       }
       catch(\Pdfcrowd\Error $why)
       {
           // report the error
           error_log("Pdfcrowd Error: {$why}\n");
           $dummy = array("error"=>$why);
           return json_encode($dummy);
           // rethrow or handle the exception
           throw $why;
       }
   }


    public function uploadPDF()
    {
        require 'pdfcrowd.php';
        //$url = "https://gavefabrikken.dk/presentation/pdf.php?print-pdf&u=1&presentationID=".$_POST["presentation_id"]."&print";
        //$url = "https://gavefabrikken.dk/presentation/pdf.php?print-pdf&u=1&user=1160214&print";
        $url = $_POST["url"];
        //  $url = "https://gavefabrikken.dk/presentation2022/pdf.php?print-pdf&u=1&user=sQB3rzOJ0forIrlW9uhxlm2KFYFwnk&print#/";

        $shopId = $_POST["shopId"];
        if($shopId == 8178){
            $url = "https://system.gavefabrikken.dk/presentation2025/pdf.php?print-pdf&u=1&user=bbh0WjIIGfLlTEkYSIZvAC5W2NgHd9&print";
        }
        $filename = generateRandomString(30);

        try
        {
            // create the API client instance
            $client = new \Pdfcrowd\HtmlToPdfClient("bundy0909", "c16c892a0e8a507b747419350431df64");
            $client->setJpegQuality(90);
            $client->setConvertImagesToJpeg("all");
            // run the conversion and write the result to a file
            //$savePath = $_SERVER['DOCUMENT_ROOT']."/presentation/pdf/sale".$filename.".pdf";
            $savePath = "../presentation/pdf/".$filename.".pdf";
            $client->convertUrlToFile($url, $savePath);
            $dummy = array("p"=>$filename);
            $shop = Shop::find($shopId);
            $shop->pt_pdf = $filename;
            $shop->save();
            // pJtjDp8kaRHz7hqdbrR1nwtGoUP3og
            response::success(json_encode(array("file"=>$filename)));
        }
        catch(\Pdfcrowd\Error $why)
        {
            // report the error
            error_log("Pdfcrowd Error: {$why}\n");
            $dummy = array("error"=>$why);
            return json_encode($dummy);
            // rethrow or handle the exception
            throw $why;
        }

        /*

        $url = $_POST["url"];
        $shopId = $_POST["shopId"];
        // "https://gavefabrikken.dk/presentation/pdf.php?print-pdf&u=1&user=814088&print#/"

       $curl = curl_init();

       curl_setopt_array($curl, array(
       CURLOPT_URL => "https://restpack.io/api/html2pdf/v6/convert",
       CURLOPT_RETURNTRANSFER => true,
       CURLOPT_CUSTOMREQUEST => "POST",
       CURLOPT_POSTFIELDS => http_build_query(
       array(
           "url" => $url,
           "json" => true,
           "pdf_page" => "A4"
       )),
       CURLOPT_HTTPHEADER => array(
           "x-access-token: V94u7vK3YT2I4wAQrvkSs7J5UryuK6FZXrrV1oPiJBup4eXS"
       ),
       ));
       $response = curl_exec($curl);
       $err = curl_error($curl);

       curl_close($curl);

       if ($err) {
       echo "cURL Error #:" . $err;
       } else {
         // get file number
         $arr = json_decode($response);
         $filenr = explode("/",$arr->file);
         $filenr = end($filenr);
         // save pdf on server
         file_put_contents("../presentation/pdf/".$filenr.".pdf",file_get_contents($arr->file));
           $shop = Shop::find($shopId);
           $shop->pt_pdf =$filenr;
            $shop->save();
          response::success(json_encode(array("file"=>$filenr)));
      }
      */

    }
}
function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

