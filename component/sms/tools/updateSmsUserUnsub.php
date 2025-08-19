<?php
/*
Henter listen ind over dem der er unsub og opdatere sms_user ved at s�tte active = 0, du skal selv ind og slette dem der er sat til 0
 */

set_time_limit(3000);
ini_set('memory_limit', '512M');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include "../db/db.php";

$smsTools = new SmsTools();
$smsTools->runupdateSayNo();
//$smsTools->runmail();
//echo $smsTools->addNewTlf();

class SmsTools {
    var $counter = 0;
    function __construct() {
        $this->db = new Dbsqli();
        $this->db->setKeepOpen();

    }

    function unsubSmsUser() {
        $sql = "select tlf from sms_unsubscribe";
        $unsubRS = $this->db->get($sql);

        foreach ($unsubRS["data"] as $tele) {
            $sql = "update sms_user set active = 0 where tlf = " . trimgf($tele["tlf"]);
            $this->db->set($sql);
        }

    }
    function addNewTlf() {
        $data = [];
        $shops = [];
        $shops[] = ["shopId" => 52, "attId" => "582", "yesId" => "583"];
        $shops[] = ["shopId" => 53, "attId" => "767", "yesId" => "768"];
        $shops[] = ["shopId" => 54, "attId" => "761", "yesId" => "762"];
        $shops[] = ["shopId" => 55, "attId" => "763", "yesId" => "764"];
        $shops[] = ["shopId" => 56, "attId" => "765", "yesId" => "766"];

        foreach ($shops as $shop) {
            $listRs = $this->loadNewTele($shop);

            foreach ($listRs["data"] as $item) {
                $this->addToList($item["attribute_value"]);
            }
        }
        return "done";
    }

    function addToList($tlf) {
        $sql = "select id from sms_user where tlf = '" . trimgf($tlf) . "'";
        $rs = $this->db->get($sql);

        if (sizeofgf($rs["data"]) == 0 && strlen(trimgf($tlf)) == 8) {
            echo $sql = "insert into sms_user (tlf,grp_id,ref) values ('" . trimgf($tlf) . "',9,'ny')";
            echo "<br>";
            echo "<br>";
            $this->db->set($sql);
        }

    }

    function loadNewTele($shop) {

        $sql = "SELECT DISTINCT (`attribute_value`) FROM `user_attribute` WHERE
                `shop_id` = " . $shop['shopId'] . " AND
                `attribute_id` = " . $shop['attId'] . " and `shopuser_id` in ( SELECT `shopuser_id` from user_attribute WHERE

                                           `shop_id` = " . $shop['shopId'] . " AND
                                           `attribute_id` = " . $shop['yesId'] . "  AND
                                           `attribute_value` = 'ja'

                                          )";

        return $this->db->get($sql);
    }
    function runmail(){
        for($i=0;$i<10000 ;$i++){
           $this->handleUnsubMail();

        }
         echo "end";
    }






    function handleUnsubMail(){
        $sql = "select id,mail from tempmail where is_check = 0 and mail != '' limit 1";
        $tlfRS = $this->db->get($sql);
        if (sizeofgf($tlfRS["data"]) > 0) {

        $this->mailLog($tlfRS["data"][0]["mail"]);
        $this->unsubFromMail($tlfRS["data"][0]["mail"]);
        $this->counter++;
        $sql = "update tempmail set  is_check = 2 where id=".$tlfRS["data"][0]["id"];
        $this->db->set($sql);
        } else {
             $this->mailLog("no data");
        }
    }

    function unsubFromMail($mail) {
        $i = 0;
        $shopuserList = [];





            $sql = "SELECT distinct(shopuser_id) FROM `user_attribute` where attribute_value like '".trimgf($mail)."'";

            $userRS = $this->db->get($sql);
//                                                  1170987   Pernillepo@hotmail.com
            $this->mailLog(serialize($userRS),5,$this->counter);


            if (sizeofgf($userRS["data"]) > 0) {
            foreach ($userRS["data"] as $user) {
                $this->mailLog($user["shopuser_id"],10,$this->counter);
                $i++;
                $sql = "SELECT `attribute_value`  FROM `user_attribute` WHERE `shopuser_id` = ".$user["shopuser_id"]." and `attribute_id` in (582,767,761,763,765)";
                $tlf = $this->db->get($sql);
                if (sizeofgf($tlf["data"]) > 0) {
                    foreach($tlf["data"] as $theTlf){
                        $this->mailLog($theTlf["attribute_value"],20,$this->counter);
                        $sql = "update sms_user set active = -4 where tlf = ".trimgf($theTlf["attribute_value"]);
                        $this->db->set($sql);
                    }
                }
            }
            }




    }
    function mailLog($log,$type=0,$counter=0){

        $sql = "insert into tempmail_log (log,type,group_id) values ('".$log."',".$type.",".$type.")";
        $this->db->set($sql);
    }

    function saveEmail()
    {
       $fn = fopen("backup.txt", "r");
        while (!feof($fn)) {

            $result = fgets($fn);
                $sql = "insert into tempmail (mail) values ('".$result."')";
                    $this->db->set($sql);

            }
    }

    function saveEmailUserSayNO()
    {
       $fn = fopen("tocheck.txt", "r");
        while (!feof($fn)) {

            $result = fgets($fn);
                if(strlen(trimgf($result)) == 8){

                    $sql = "insert into tempmail (mail) values ('".trimgf($result)."')";
                    $this->db->set($sql);
                }
        }
    }
    function runupdateSayNo(){
        for($i=0;$i<6200 ;$i++){
           $this->updateSayNo();

        }
         echo "end";
    }
    function updateSayNo(){
        $sql = "select id,mail from tempmail where is_check = 0 and mail != '' limit 1";
        $tlfRS = $this->db->get($sql);
        if (sizeofgf($tlfRS["data"]) > 0) {
            $this->mailLog($tlfRS["data"][0]["mail"],11);
            $sql = "update tempmail set  is_check = 3 where id=".$tlfRS["data"][0]["id"];
            $this->db->set($sql);

            $sql = "update sms_user set active = -4 where tlf = ".trimgf($tlfRS["data"][0]["mail"]);
            $this->db->set($sql);
        } else {
             $this->mailLog("no data");
        }
    }
}

/*
};
$db = new Dbsqli();
$db->setKeepOpen();
$lines = file("unsub.txt");
foreach ($lines as $line_num => $line) {
$sql = "select shopuser_id from user_attribute where attribute_value like '".trimgf($line)."'   ";
$rs = $db->get($sql);
echo trimgf($line)."<hr />";
foreach($rs["data"] as $element){
echo $sql2 = "update sms_user set active = 0 where shopuser_id ='".$element["shopuser_id"]."'   ";
echo "<br />";
$db->set($sql2);
echo $element["shopuser_id"]."<br />";

}
echo "<hr />";
}
echo "<script>alert('asdfasd')</script>"
 */

?>