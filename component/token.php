<?php



include("sms/db/db.php");

$unsuList = [];


$db = new Dbsqli();
$db->setKeepOpen();

$sql = "select * from company where token = ''";
$rs = $db->get($sql);



$i = 0;


//echo getToken(40);

foreach($rs["data"] as $key=>$val){
         $updata = "update company set token = '".getToken(40)."' where id ='".$val["id"]."'";
        $db->set($updata);
}
  echo "fine";


function crypto_rand_secure($min, $max)
{
    $range = $max - $min;
    if ($range < 1) return $min; // not so random...
    $log = ceil(log($range, 2));
    $bytes = (int) ($log / 8) + 1; // length in bytes
    $bits = (int) $log + 1; // length in bits
    $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
    do {
        $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
        $rnd = $rnd & $filter; // discard irrelevant bits
    } while ($rnd > $range);
    return $min + $rnd;
}

function getToken($length)
{
    $token = "";
    $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
    $codeAlphabet.= "0123456789";
    $max = strlen($codeAlphabet); // edited

    for ($i=0; $i < $length; $i++) {
        $token .= $codeAlphabet[crypto_rand_secure(0, $max-1)];
    }

    return $token;
}

/*
foreach($rs["data"] as $key=>$val){

    $sqlTjeck = "select * from klubben where telefon ='".$val["tlf"]."'";
    $tjeckRs = $db->get($sqlTjeck);
    if(sizeofgf($tjeckRs["data"]) >0){
      echo $updata = "update klubben set active = 0 where telefon ='".$val["tlf"]."'";
        $db->set($updata);
      $i++;
    }

}

echo $i;
*/



?>