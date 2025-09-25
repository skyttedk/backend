<?php
// Få token fra URL parameter
$token = $_GET['token'] ?? '';

// Tjek om token findes
if (empty($token)) {
    die('Ingen token angivet');
}

// Decode base64 token for at få filnavnet
$filename = base64_decode($token);

// Sikkerhedscheck af filnavn (fjern eventuelle directory traversal forsøg)
$filename = basename($filename);


// Sti til mappen hvor filerne ligger
$filepath = realpath(__DIR__) . DIRECTORY_SEPARATOR . 'glsretur' . DIRECTORY_SEPARATOR . $filename;

// Tjek om filen eksisterer
if (!file_exists($filepath)) {
    die('Filen blev ikke fundet');
}

// Set alle headers for download
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="'.$filename.'"');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Content-Length: ' . filesize($filepath));

// Ryd output buffer og send filen
ob_clean();
flush();
readfile($filepath);
exit;
?>