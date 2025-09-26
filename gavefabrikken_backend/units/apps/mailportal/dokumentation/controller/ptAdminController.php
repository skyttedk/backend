<?php

Class ptAdminController Extends baseController {
   public function index() {
        $this->registry->template->show('ptAdmin_view');
   }
   public function getSalePersonList(){
     $res = PresentationSaleProfile::find_by_sql("select * from presentation_sale_profile  order by name");
        response::success(json_encode($res));
   }

   public function loadPresentPrice(){
        $shopId = $_POST["id"];
        $return = Present::find_by_sql("select present.*,present_media.media_path  from present LEFT JOIN present_media on present_media.present_id = present.id where present.id in(select present_id from shop_present where shop_id = ".$shopId." and active = 1 and is_deleted = 0 order by index_) and `index` = 0");

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

   public function uploadPDF()
   {
         require 'pdfcrowd.php';
        //$url = "https://gavefabrikken.dk/presentation/pdf.php?print-pdf&u=1&presentationID=".$_POST["presentation_id"]."&print";
         //$url = "https://gavefabrikken.dk/presentation/pdf.php?print-pdf&u=1&user=1160214&print";
         $url = $_POST["url"];
       //  $url = "https://gavefabrikken.dk/presentation2022/pdf.php?print-pdf&u=1&user=sQB3rzOJ0forIrlW9uhxlm2KFYFwnk&print#/";

         $shopId = $_POST["shopId"];
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

