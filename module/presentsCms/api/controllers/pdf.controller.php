<?php

include "model/pdf.model.php";
require 'pdfcrowd.php';
class pdfController
{
    public function go()
    {
       $dummy = array();
       return json_encode($dummy);
    }
    public function createSlide()
    {
        // $option = isset($_POST["option"]) ? $_POST["option"] : "";
        return pdf::createSlide($_POST);
    }
    public function build2(){
        echo   "https://system.gavefabrikken.dk/presentation/pdf.php?print-pdf&u=1&presentationID=".$_POST["presentation_id"]."&print";
    }
    public function build()
    {
       $url = "https://system.gavefabrikken.dk/presentation/pdf_sale_dev.php?print-pdf&u=1&token=".$_POST["presentation_id"]."&print";
       /*
       if($_POST["lang"] == "4"){
           $url = "https://gavefabrikken.dk/presentation/pdf_sale_no.php?print-pdf&u=1&presentationID=".$_POST["presentation_id"]."&print";
       }
       */


        try
        {
            // create the API client instance
            $client = new \Pdfcrowd\HtmlToPdfClient("bundy0909", "c16c892a0e8a507b747419350431df64");
            $client->setJpegQuality(80);
            $client->setConvertImagesToJpeg("all");
            // run the conversion and write the result to a file
            //$savePath = $_SERVER['DOCUMENT_ROOT']."/presentation/pdf/sale".$_POST["presentation_id"].".pdf";
            $savePath = "../../../../presentation/pdf/sale".$_POST["presentation_id"].".pdf";
            $client->convertUrlToFile($url, $savePath);
            $dummy = array("p"=>$_POST["presentation_id"]);
            return json_encode($dummy);
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
    $curl = curl_init();
    $url = "https://gavefabrikken.dk/presentation/pdf.php?print-pdf&u=1&presentationID=".$_POST["presentation_id"]."&print";
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
        "x-access-token: Amoc5EnpJKvtAGhf7UDf2ygnsG8kkV1ZzxpQiReiHGXnKfE8"
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


       file_put_contents($_SERVER['DOCUMENT_ROOT']."/presentation/pdf/sale".$_POST["presentation_id"].".pdf",file_get_contents($arr->file));
       $dummy = array("p"=>$_POST["presentation_id"]);
       return json_encode($dummy);

        }
        */


    }

}





?>