<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


ini_set('memory_limit','2048M');
set_time_limit(0);

include("sms/db/db.php");

$List = [];


$db = new Dbsqli();
$db->setKeepOpen();


$fileData = function() {
    $file = fopen(__DIR__ . '/list1.csv', 'r');

    if (!$file)
        die('file does not exist or cannot be opened');

    while (($line = fgets($file)) !== false) {
        yield $line;
    }
    fclose($file);
};

 foreach ($fileData() as $line) {
      doReplase($line,$db);

 }



function doReplase($card,$db){
    $language = 1;
    $part = explode(";",$card );
    $userID = $part[0];
   $newCard =  replace($userID,$db,$part[2],$language);

   echo $part[1].";;".$newCard["data"][0]["username"].";".$newCard["data"][0]["password"];
   echo "<br>";

}






function replace($shopuserID,$db,$shop,$language)
{

        $replacementCardshop = \CardshopSettings::find('first',array("conditions" => array("language_code = ".intval($language)." && shop_id = ".intval($shop))));
        $companyID = $replacementCardshop->replacement_company_id;

         $rs = $db->get("select * from shop_user
                                   where
                                     shop_user.company_id = ".$companyID." and
                                     replacement_id = 0 and
                                     shop_id = ".$shop."
                                   limit 1");
         $replacementID =   $rs["data"][0]["id"];


          $sql1 = "update shop_user set replacement_id = ".$shopuserID." where id = ".$replacementID;
          $sql2 = "update shop_user set is_replaced = 1 where id = ".$shopuserID;
     //   $db->set($sql1);
    //       $db->set($sql2);

          return $rs;

     //   \Dbsqli::setSql2($sql1);
     //    \Dbsqli::setSql2($sql2);

}
/*

         echo json_encode(array("status" => 1));
*/