<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


ini_set('memory_limit','2048M');
set_time_limit(0);

include("sms/db/db.php");

$List = [];
$minusList = [0];

$db = new Dbsqli();
$db->setKeepOpen();


$fileData = function() {
        $file = fopen(__DIR__ . '/1.csv', 'r');

        if (!$file)
                die('file does not exist or cannot be opened');

        while (($line = fgets($file)) !== false) {
                yield $line;
        }
        fclose($file);
};

 foreach ($fileData() as $line) {
        $pieces = explode(";", $line);

        $sql = "select user_email,shopuser_id FROM `order` where shopuser_id not in (".implode($minusList,",").") and user_email = '".trimgf($pieces[1])."' and user_name = '".utf8_encode(trimgf($pieces[0]))."' and present_model_present_no = 'SAM2088' limit 1 ";
        $rs = $db->get($sql);
        $minusList[] = $rs["data"][0]["shopuser_id"];
        echo $rs["data"][0]["shopuser_id"].";".$rs["data"][0]["user_email"].";".$pieces[1];
        echo "<br>";

 }
