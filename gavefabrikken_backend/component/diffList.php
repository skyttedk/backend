<?php

$goodlist = [];

$toSend = function() {
        $file = fopen(__DIR__ . '/tosend2021.csv', 'r');

        if (!$file)
                die('file does not exist or cannot be opened');

        while (($line = fgets($file)) !== false) {
                yield $line;
        }
        fclose($file);
};

$unsub = function() {
        $file = fopen(__DIR__ . '/unsub2021.csv', 'r');

        if (!$file)
                die('file does not exist or cannot be opened');

        while (($line = fgets($file)) !== false) {
                yield $line;
        }
        fclose($file);
};

$usbublist = [];
$toSendlist = [];
foreach ($unsub() as $line) {
   $usbublist[] =  strtolower(trimgf($line));
}
foreach ($toSend() as $line) {
   $toSendlist[] =  strtolower(trimgf($line));
}

 foreach ($toSendlist as $line) {
    $inarray = false;
    foreach ($usbublist as $uns) {
        if($line == $uns){
           $inarray = true;
        }
    }
    if($inarray == false){
      echo $line."<br>";
    }
 }