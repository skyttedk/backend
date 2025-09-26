<?php

ini_set('max_execution_time', 300); //300 seconds = 5 minutes
ini_set('memory_limit','2048M');
ini_set('display_errors', 1);
error_reporting(E_ALL);
include("sms/db/db.php");


//


//$shopID = 6428;



$sr = new shopreceipt;

/*
$p1 =  $sr->pris(93776);
$p2 =  $sr->pris(799627);
$p3= $p1 + $p2;
echo number_format((float)$p3, 2, '.', '');
*/

$list = $sr->getReceiptData($shopID);



$i = 0;
foreach ($list as $item){
    $i++;
     $email = $item["masterData"]["email"];
     $html = $sr->makeReceipt($item);
    // $email = "us@gavefabrikken.dk";
     $sr->sendMail($email,$html);
}
echo $i;
echo "done";
/*
 * INSERT INTO `mail_queue`
(`mailserver_id`, `sender_name`, `sender_email`, `recipent_name`, `recipent_email`, `subject`, `body`)VALUES( '4', 'Gavefabrikken', 'Gavefabrikken@gavefabrikken.dk', '', 'us@bitworks.dk','test', 'hej med dig');
 */


// Hej navn, hermed fremsende oversigt af ekstra valge gaver. ”Betaling skal ske på MobilePay nr. 192308 senest den 15. december.
//Skriv dine initaler + jul i kommentarfeltet.”

class shopreceipt
{
    private $db;
    public function __construct() {
        $this->db = new Dbsqli();
        $this->db->setKeepOpen();
    }
    public function sendMail($mail,$html){
      //  $mail = "us@gavefabrikken.dk";
        $html = $this->stringConvert($html);
        $body = "<html lang='da'><head> <meta charset='UTF-8'> </head><body>";
        $body.= $html."</body></html>";
        $body = $body;
         $sql = 'INSERT INTO `mail_queue`
            (`mailserver_id`, `sender_name`, `sender_email`, `recipent_name`, `recipent_email`, `subject`, `body`)
            VALUES( "4", "Gavefabrikken", "Gavefabrikken@gavefabrikken.dk", "", "'.$mail.'","Opgørelse af ekstra gaver", "'.$body.'")';
            $this->db->set($sql);
    }


    public function getReceiptData($shopID){
        $returnData = [];
        $userList = $this->userlist($shopID);
        foreach ($userList["data"] as $shopUser){
            $shopUserID = $shopUser["basket_id"];
            $userData = $this->getUserData($shopUserID);
            $salelist = $this->getSalelist($shopUserID);
            array_push($returnData,array("masterData"=>$userData,"salelist"=>$salelist));
        }
        return $returnData;
    }
    public function makeReceipt($userData){
        // Dine ekstra valgte gaver
        $priceSum = 0;
        $html = "";$htmlheader="";

        $html.="<table width='80%'>";
        foreach ($userData["salelist"]["data"] as $sale){
            $priceSum+= $this->pris($sale["model_id"])*1;
            $time = $sale["order_timestamp"];
            $pris = $this->pris($sale["model_id"]);
            $kr = number_format((float)$pris, 2, '.', '');
            $html.="
            <tr><td>Ordernummer: {$sale["order_no"]}</td><td>Dato: {$time}</td></tr>
            <tr><td><br></td></tr>
            <tr><td><b>Produkt:</b></td></tr>
            <tr><td>{$sale["model_name"]}<br>{$sale["model_no"]}</td><td><img width='100' src='https://system.gavefabrikken.dk/gavefabrikken_backend/views/media/type/{$sale["media_path"]}' alt='Product image' /></td></tr>
            <tr><td colspan='2'>PRIS: {$kr } kr.</td></tr>
             
            <tr><td colspan='2'><hr></td></tr>
            ";
        }
        $html.="</table></div><br>
            Med venlig hilsen<br>
            GaveFabrikken A/S";

        $htmlheader = "<div><table width='80%'><tr><td><p><b>Hej {$userData["masterData"]["name"]}</b></p>
                <p>Tilkøbet skal du betale på Mobilepay nr. 192308 senest den dag, hvor julegaverne udleveres.</p>
                <p>Vi tager forbehold for eventuelle prisfejl.</p>
                <br><b><u>Samlet pris: ".number_format((float)$priceSum, 2, '.', '')." kr.</u></b><br><br><br><b>Valgte ekstra gaver:</b><br><hr></td></tr></table>";
        return  $htmlheader.$html;
    }

    public function userlist($shopID){
        $sql = "SELECT DISTINCT(`basket_id`) FROM `shop_user` WHERE `basket_id` != 0 and shutdown = 0 and shutdown = 0 and is_demo = 0 and shop_id =".$shopID;
        return $this->db->get($sql);  
    }
    public function getUserData($userID){
        $name="";$email = "";
        $sql = "SELECT *  FROM `user_attribute` WHERE `shopuser_id` = ".$userID." and (`is_email` = 1 || `is_name` = 1)";
        $rs = $this->db->get($sql);
        foreach ($rs["data"] as $element){
            if($element["is_name"] == 1){
                $name = $element["attribute_value"];
            }
            if($element["is_email"] == 1){
                $email = $element["attribute_value"];
            }
        }
        return array("name"=>$name,"email"=>$email);
    }
    
    public function getSalelist($parentShopUser){
        $sql = "SELECT `order`.order_timestamp ,`shop_user`.id,order_no, present_model.model_present_no,present_model.model_name,present_model.model_no,present_model.media_path,present_model.model_id  FROM `order` 
        inner join present_model on `order`.present_model_id = present_model.model_id 
        inner join `shop_user` on `order`.`shopuser_id` = `shop_user`.id 
        where `basket_id` != 0 AND `order`.`shopuser_id` in(  SELECT id FROM `shop_user` WHERE `basket_id` = ".$parentShopUser."   )  AND present_model.language_id = 1 and present_model.model_present_no != '230139' order by order_no ";
        return $this->db->get($sql);
    }
    private function stringConvert($string){
        $string = str_replace("æ","&aelig;",$string);
        $string = str_replace("ø","&oslash;",$string);
        $string = str_replace("å","&aring;",$string);
        $string = str_replace("Æ","&AElig;;",$string);
        $string = str_replace("Ø","&Oslash;",$string);
        $string = str_replace("Å","&Aring;",$string);

        return $string;
    }
    public function pris($modelnr){

$pris = array(
    "199256"=>800,
    "199262"=>800,
    "199263"=>800,
    "199265"=>800,
    "199277"=>700,
    "199278"=>700,
    "199280"=>700,
    "199283"=>750,
    "199284"=>750,
    "199289"=>800,
    "199291"=>700,
    "199292"=>700,
    "199295"=>800,
    "199301"=>750,
    "199309"=>625,
    "199310"=>625,
    "199314"=>700,
    "199319"=>700,
    "199329"=>700,
    "199340"=>700,
    "199341"=>700,
    "199344"=>500,
    "199345"=>500,
    "222325"=>750,
    "244492"=>800,
    "244493"=>800,
    "264596"=>700,
    "264597"=>700,
    "268781"=>700,
    "271168"=>700,
    "272948"=>700
);
         return $pris[$modelnr];


    }

}