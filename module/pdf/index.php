<?php
 ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
     require 'pdfcrowd.php';

 $filename = generateRandomString(30);

try
{
    // create the API client instance
    $client = new \Pdfcrowd\HtmlToPdfClient("bundy0909", "c16c892a0e8a507b747419350431df64");
    $client->setJpegQuality(80);
    $client->setConvertImagesToJpeg("all");
    // run the conversion and write the result to a file
    $client->convertUrlToFile("https://system.gavefabrikken.dk/presentation/pdf.php?print-pdf&u=1&user=1160214&print", "example3.pdf");
}
catch(\Pdfcrowd\Error $why)
{
    // report the error
    error_log("Pdfcrowd Error: {$why}\n");

    // rethrow or handle the exception
    throw $why;
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


?>