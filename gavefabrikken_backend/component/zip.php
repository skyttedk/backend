<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


$zip = new ZipArchive;
$filename ="test_overwrite.zip";
/*
if ($zip->open('test_overwrite.zip', ZipArchive::OVERWRITE) === TRUE)
{
    // Add file to the zip file
    $zip->addFile('zip1.txt');
    $zip->addFile('zip2.txt');
    echo "sadf3";
    // All files are added, so close the zip file.
    $zip->close();
}
*/

if ($zip->open($filename, ZipArchive::CREATE)!==TRUE) {
    exit("cannot open <$filename>\n");
}