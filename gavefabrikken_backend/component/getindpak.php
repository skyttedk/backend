<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if($_GET["token"] != "dfsalkfj498y9hskdfh7488ifhus"){
  die("Ingen adgang");
}


include("sms/db/db.php");
$db = new Dbsqli();
$db->setKeepOpen();

$sql = "";



//$db->set($insertSql);
$giftwrap =  getGiftwrap($db,52,"2021-11-28");
$total =   getTotal($db,52,"2021-11-28");

$all =  getAllConseptAndDeadline($db);
 $csv[] = array("Konsept","Deadline","Total","Antal indpakket","% �nsker indpak");
foreach( $all["data"] as $ele){


  $id =  $ele["user_shop_id"];
  $deadline = $ele["expire_date"];
  $navn =    utf8_decode($ele["shop_name"]);
  $total =   $ele["antal"];
  $wrapAntalRS = getGiftwrap($db,$id,$deadline);
  $totalRs = getTotal($db,$id,$deadline);
  $wrapAntal = $wrapAntalRS["data"][0]["antal"];

  $prcent =  round( (($wrapAntal*1) / ($total*1)) * 100 );
   $csv[] = array($navn,$deadline,$total,$wrapAntal,$prcent);

}


 array_to_csv_download($csv);

function array_to_csv_download($array, $filename = "exportsdfs.csv", $delimiter=";") {
    // open raw memory as file so no temp files needed, you might run out of memory though
    $f = fopen('php://memory', 'w');
    // loop over the input array
    foreach ($array as $line) {
        // generate csv lines from the inner arrays

        fputcsv($f, $line, $delimiter);
        //print_r($line);
    }
    // reset the file pointer to the start of the file
    fseek($f, 0);
    // tell the browser it's going to be a csv file
    header('Content-Type: text/csv');
    // tell the browser we want to save it instead of displaying it
    header('Content-Disposition: attachment; filename="'.$filename.'";');
    // make php send the generated csv lines to the browser
    fpassthru($f);
}


function getAllConseptAndDeadline($db)
{
   $sql = "
    SELECT shop_user.shop_id as user_shop_id,company_order.shop_name,shop_user.expire_date, count(shop_user.id) as antal FROM `shop_user`
    inner join company_order on shop_user.company_order_id = company_order.id
    WHERE
    shop_user.`is_giftcertificate` = 1 AND
    shop_user.`blocked` = 0 AND
    shop_user.`shutdown` = 0 and
    company_order.`is_cancelled` = 0 AND
     order_state < 7 and
     shop_user.shop_id > 0
    group by shop_user.`shop_id`,shop_user.expire_date order by company_order.shop_name, shop_user.`shop_id`,shop_user.expire_date
   ";
     return $db->get($sql);
}


function getGiftwrap($db,$shopID,$deadline){
   $sql = "
    SELECT company_order.shop_name,shop_user.expire_date, count(shop_user.id) as antal FROM `shop_user`
    inner join company_order on shop_user.company_order_id = company_order.id
    WHERE
    shop_user.`is_giftcertificate` = 1 AND
    shop_user.`blocked` = 0 AND
    shop_user.`shutdown` = 0 and
    company_order.`is_cancelled` = 0 AND
    company_order.`giftwrap` = 1 and order_state < 7 and
    shop_user.`shop_id` =".$shopID." and
    shop_user.expire_date = '".$deadline."'";

    return $db->get($sql);
}

function getTotal($db,$shopID,$deadline){
     $sql = "
    SELECT company_order.shop_name,shop_user.expire_date, count(shop_user.id) as antal FROM `shop_user`
    inner join company_order on shop_user.company_order_id = company_order.id
    WHERE
    shop_user.`is_giftcertificate` = 1 AND
    shop_user.`blocked` = 0 AND
    shop_user.`shutdown` = 0 and
    company_order.`is_cancelled` = 0 AND
    order_state < 7 and
    shop_user.`shop_id` =".$shopID." and
    shop_user.expire_date = '".$deadline."'";

    return $db->get($sql);




}





?>