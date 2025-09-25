<?php

use GFCommon\Model\Navision\FreightCalculator;
use GFCommon\Model\Navision\OrderXML;

if (session_status() == PHP_SESSION_NONE) session_start();
 
 
 class soappayload
 {
 
   function soappayload($val1,$val2)
   {
      $this->val1 = $val1;
      $this->val2 = $val2;
   }
   
 }

 class isValidItemNo {
     public $itemNo = "";
 }

 
class SchController Extends baseController {

     public function testitemsync() {


         $langage_id = 1;
         $item_no = "SAM2159";

         try {
             $model = new \GFBiz\Navision\ItemSync();
             $salesPrices = $model->runSalesPriceSync($langage_id,$item_no);
         } catch (Exception $e) {
             echo "Der var en fejl i at hente fra navision: ".$e->getMessage();
             return;
         }

         // Husk at kalde system::connection()->commit(); for at der gemmes

         echo "Fand ".count($salesPrices)." på varenr ".$item_no."<br>";
         foreach($salesPrices as $salesPrice) {
             echo $salesPrice->sales_type.": ".$salesPrice->sales_code." - ".$salesPrice->unit_price."<br>";
         }


         /*
         $langage_id = 1;
         $item_no = "SAM2159";
         $runBOMSync = true;
         $isHandled = 1;
         $runPriceSync = true;

         try {
             $model = new \GFBiz\Navision\ItemSync();
             $navisionItem = $model->runItemSync($langage_id,$item_no,$runBOMSync,$isHandled,$runPriceSync);
         } catch (Exception $e) {
             echo "Der var en fejl i at hente fra navision: ".$e->getMessage();
             return;
         }

         if($navisionItem == null) {
             echo "Kunne ikke finde aktivt varenr i navision";
         } else {
             echo "Fandt varen ".$navisionItem->no.": ".$navisionItem->description;
         }

         // Husk at kalde system::connection()->commit(); for at der gemmes
*/

     }

/*
     public function freedeliverylist() {

         $companyOrderList = CompanyOrder::find_by_sql("SELECT order_no, company_name, shop_name, salesperson, quantity, expire_date, is_email, cvr, shop_id, company_id FROM `company_order` where shop_id in (select shop_id from cardshop_settings where language_code = 1) && company_id in (SELECT company_id FROM `company_shipping_cost` WHERE `cost` = 0) && order_state = 10 order by shop_name ASC");

         header('Content-Type: text/csv; charset=utf-8');
         header('Content-Disposition: attachment; filename=privatlevering-dk-fri.csv');

         $headlines = array("order_no","company_name","shop_name","salesperson","quantity","expire_date","is_email","cvr");
         foreach($headlines as $head) {
             echo $head.";";
         }
         echo "\r\n";

         foreach($companyOrderList as $co) {

             echo $co->order_no.";";
             echo utf8_decode(str_replace(";",",",$co->company_name)).";";
             echo $co->shop_name.";";
             echo $co->salesperson.";";
             echo $co->quantity.";";
             echo $co->expire_date->format("Y-m-d").";";
             echo $co->is_email.";";
             echo $co->cvr.";";

             $freighAmount = FreightCalculator::calculateFreight($co->shop_id,$co->quantity,false,Company::find($co->company_id));
                echo $freighAmount;

             echo "\r\n";

         }

     }
*/

     public function maillinks() {

         $shopList = array("24gaver400","24gaver560","24gaver640","Design","Drøm200","Drøm300","Det grønne gavekort","Guld 800","Guld 960","Julegavekortet");

         $typeList = array(
             "med indpak, med spec. lev",
             "med indpak, uden spec. lev, over 40",
             "med indpak, uden spec. lev, under 40",
             "uden indpak, med spec. lev",
             "uden indpak, uden spec. lev, over 40",
             "uden indpak, uden spec. lev, under 40"
         );

         foreach($shopList as $shop) {
             echo "<p><b>".$shop."</b><br>";
             foreach($typeList as $type) {
                 $url = 'navnelabel@gavefabrikken.dk,mi@gavefabrikken.dk?subject='.$shop.'%20-%20'.$type.'&body=Her%20er%20lister%20til%20'.$shop.'%20-%20'.$type;
                 if(strstr($type,"uden indpak")) $url .= "%0AOBS:%20Der%20skal%20printes%202%20ordresedler.";
		        echo '<a href="mailto:'.$url.'">'.$shop.' - '.$type.'</a><br>';
	        }
            echo "</p>";
         }


     }

     public function checkorderxml() {

         $companyOrder = CompanyOrder::find_by_sql("SELECT * FROM company_order WHERE order_no = 'BS72371'");

         $orderXML = new OrderXML($companyOrder[0],1);
         $xml = $orderXML->getXML();

         echo "<pre>".htmlspecialchars($xml)."</pre>";

       //  var_dump($_SESSION);
     }

public function logouts() {

    unset($_SESSION);
    unset($_COOKIE);

}

     public function testmail() {


         mailgf("sc@interactive.dk","Test af e-mail funktion","Dette er bare en test, forhold dig i ro!");

         echo "TEST!!!";

         return;
         //$this->xxmail("sc@interactive.dk","Test emne","Test test test","");


         $mail = new PHPMailer();
         $mail->isSMTP();
         $mail->CharSet    = "UTF-8";
         $mail->isHTML(true);
         $mail->SMTPAuth = false;
         $mail->Host = "185.223.25.130";
         $mail->From = "noreply@gavefabrikken.email";
         $mail->FromName = "Gavefabrikken";
         $mail->addAddress("sc@interactive.dk");
         $mail->Subject = ("test subject");
         $mail->Body    = "body here";
         $mail->AltBody = '';

         if(!$mail->send()) {
             echo "could not send!";
         } else {
             echo "sended!";
         }


     }

     public function checkweberror()
     {

         // Inputs
         $shopid = isset($_POST["shop_id"]) ? $_POST["shop_id"] : "";
         $cvr = isset($_POST["cvr"]) ? $_POST["cvr"] : "";
         $contact_email = isset($_POST["contact_email"]) ? $_POST["contact_email"] : "";
         $expire_date = isset($_POST["expire_date"]) ? $_POST["expire_date"] : "";

         $eds = explode("-",$expire_date);
         $eds = array_reverse($eds);
         $expire_date = implode("-",$eds);

         // Prepare output
         $shopName = "Ukendt";
         $lang = "";
         if(intval($shopid) > 0) {

             $shop = Shop::find(intval($shopid));
             $shopName = $shop->name;

             $cardshopsettings = CardshopSettings::find_by_sql("select * from cardshop_settings where shop_id = ".intval($shopid));
             $lang = $cardshopsettings[0]->language_code;

         }

         $responseData = array("check1" => "", "check2" => "", "check3" => "","shopname" => $shopName,"lang" => $lang);
         $ids = array();

         if($cvr == "" && $contact_email == "") {
             $responseData["check1"] = "*";
             $responseData["check2"] = "*";
             $responseData["check3"] = "*";
             echo json_encode($responseData);
             return;
         }

         // Find order
         $conditionsStrict1 = array("salesperson LIKE 'IMPORT'");
         $conditionsStrict2 = array("salesperson NOT LIKE 'IMPORT'");
         $conditionsLoose = array();

         if(intval($shopid) > 0) {
             $conditionsStrict1[] = "shop_id = ".intval($shopid);
             $conditionsStrict2[] = "shop_id = ".intval($shopid);
         }

         if(trimgf($cvr) != "") {
             $conditionsStrict1[] = " REPLACE(cvr,'-','')  like '".str_replace(array(" ","-"),"",trimgf($cvr))."'";
             $conditionsStrict2[] = " REPLACE(cvr,'-','')  like '".str_replace(array(" ","-"),"",trimgf($cvr))."'";
             $conditionsLoose[] = " REPLACE(cvr,'-','')  like '".str_replace(array(" ","-"),"",trimgf($cvr))."'";
         }

         if(trimgf($contact_email) != "") {
             $conditionsStrict1[] = "contact_email like '".str_replace(array(" ","-"),"",trimgf($contact_email))."'";
             $conditionsStrict2[] = "contact_email like '".str_replace(array(" ","-"),"",trimgf($contact_email))."'";
             $conditionsLoose[] = "contact_email like '".str_replace(array(" ","-"),"",trimgf($contact_email))."'";
         }

         if(trimgf($expire_date) != "") {
             $conditionsStrict1[] = "expire_date = '".$expire_date."'";
             $conditionsStrict2[] = "expire_date = '".$expire_date."'";
         }

         $sql1 = "SELECT * FROM company_order WHERE ".implode(" && ",$conditionsStrict1);
         //echo $sql1."\r\n";

         $sql2 = "SELECT * FROM company_order WHERE ".implode(" && ",$conditionsStrict2);
         //echo $sql2."\r\n";

         $sql3 = "SELECT * FROM company_order WHERE (".implode(" || ",$conditionsLoose).")";
         //echo $sql3."\r\n";



         $companyorderlist1 = CompanyOrder::find_by_sql($sql1);
         if(count($companyorderlist1) > 0) {
             $responseData["check1"] = countgf($companyorderlist1).": ".$companyorderlist1[0]->order_no." - ".$companyorderlist1[0]->created_datetime->format('Y-m-d H:i');
             foreach($companyorderlist1 as $co) $ids[] = $co->id;
         }

         if(count($ids) > 0) $sql2 .= " && id NOT IN (".implode(",",$ids).")";

         $companyorderlist2 = CompanyOrder::find_by_sql($sql2);
         if(count($companyorderlist2) > 0) {
             $responseData["check2"] = countgf($companyorderlist2).": ".$companyorderlist2[0]->order_no." - ".$companyorderlist2[0]->created_datetime->format('Y-m-d H:i');
             foreach($companyorderlist2 as $co) $ids[] = $co->id;
         }

         if(count($ids) > 0) $sql3 .= " && id NOT IN (".implode(",",$ids).")";

         $companyorderlist3 = CompanyOrder::find_by_sql($sql3);
         if(count($companyorderlist3) > 0) {
             $responseData["check3"] = countgf($companyorderlist3).": ".$companyorderlist3[0]->order_no." - ".$companyorderlist3[0]->created_datetime->format('Y-m-d H:i');
             foreach($companyorderlist3 as $co) $ids[] = $co->id;
         }

         //echo json_encode($_POST);

         echo json_encode($responseData);

     }

     public function unit() {


?><html>
    <head>

        <script src="<?php echo GFConfig::BACKEND_URL; ?>views/lib/jquery.min.js"></script>

    </head>
    <body>
        <h2>Test af navision komponent</h2>
        <?php GFUnit\navision\customerlist\Controller::outputView(); ?>
    </body>
</html><?php


     }

     public function svensksalg() {

         if($_GET["token"] != "fdhljkhljh3h3bsaqweufdoksf") exit();

         // Virksomheds ordre liste til sverige
         $dayOfWeek = date("w");
         $dayOffset = ($dayOfWeek <= 5 ? -1*($dayOfWeek+1) : 0);
         $dayOffset = -365;
         $endDate = mktime(0,0,0,date("m"),date("d"),date("Y"));
         $startDate = mktime(0,0,0,date("m"),date("d")+$dayOffset-7,date("Y"));
         //$sql = "SELECT company.name as Virksomhed, company.cvr as CVR, company_order.shop_name as Korttype, company_order.quantity as Antal FROM `company_order`, company where company_order.created_datetime >= '".date("Y-m-d H:i:s",$startDate)."' && company_order.created_datetime < '".date("Y-m-d H:i:s",$endDate)."' && company.id = company_order.company_id && company_order.order_state not in (7,8) && company_order.shop_id in (select shop_id from cardshop_settings where language_code = 5) order by company.name asc";
         $sql = "SELECT company.name as Virksomhed, company.cvr as CVR , company.contact_name, company.contact_phone, company.contact_email, company_order.shop_name as Korttype, company_order.quantity as Antal, company.bill_to_address, company.bill_to_postal_code, company.bill_to_city, company.bill_to_country, company.bill_to_email, company.ship_to_address, company.ship_to_postal_code, company.ship_to_city, company.ship_to_country FROM `company_order`, company where company_order.salesperson like 'IMPORT' && company_order.created_datetime >= '".date("Y-m-d H:i:s",$startDate)."' && company_order.created_datetime < '".date("Y-m-d H:i:s",$endDate)."' && company.id = company_order.company_id && company_order.order_state not in (1,2,3,7,8) && company_order.shop_id in (select shop_id from cardshop_settings where language_code = 5) order by company.name asc";
         $results = Dbsqli::getSql2($sql);

         //echo date("r",$startDate)." - ".date("r",$endDate); return;

         if(!is_array($results) || countgf($results) == 0) {
             echo "Ingen resultater"; exit();
         }

         header('Content-Type: text/csv; charset=utf-8');
         header('Content-Disposition: attachment; filename=svensksalg-'.date("dm",$startDate)."-".date("dm",$endDate-1).'.csv');
         foreach($results[0] as $key => $val) {
             echo $key.";";
         }
         echo "\n";

         foreach($results as $row)
         {
             foreach($row as $key => $val) {
                 echo utf8_decode(trimgf(str_replace(array("\r","\n",";"),array(""," ",""),$val)).";");
             }
             echo "\n";
         }

     }



     public function customsqlpull()
     {
return;
        // echo "test"; return;

         //$shopuser = ShopUser::find('first',array("conditions" => array("username" => "31707916")));
         //echo $shopuser->getDeliveryStatus(); return;


            // Virksomheds ordre liste til susanne gallus
            //$sql = "SELECT company.name as Virksomhed, company.cvr as CVR, company_order.shop_name as Korttype, company_order.quantity as Antal FROM `company_order`, company where company.id = company_order.company_id && company_order.order_state not in (7,8) && company_order.shop_id in (select shop_id from cardshop_settings where language_code = 1) order by company.name asc";
/*
         // Virksomheds ordre liste til sverige
         $dayOfWeek = date("w");
         $dayOffset = ($dayOfWeek <= 5 ? -1*($dayOfWeek+1) : 0);
         $endDate = mktime(0,0,0,date("m"),date("d")+$dayOffset,date("Y"));
         $startDate = mktime(0,0,0,date("m"),date("d")+$dayOffset-8,date("Y"));
         $sql = "SELECT company.name as Virksomhed, company.cvr as CVR, company_order.shop_name as Korttype, company_order.quantity as Antal FROM `company_order`, company where company_order.created_datetime >= '".date("Y-m-d H:i:s",$startDate)."' && company_order.created_datetime < '".date("Y-m-d H:i:s",$endDate)."' && company.id = company_order.company_id && company_order.order_state not in (7,8) && company_order.shop_id in (select shop_id from cardshop_settings where language_code = 5) order by company.name asc";
*/

         //$sql = "SELECT contact_name, contact_email FROM `company_order` where order_state in (5,6,9,10) && shop_id in (2558,1832) && expire_date = '2021-12-31'";
         //$sql = "SELECT contact_name, contact_email FROM `company_order` where order_state in (5,6,9,10) && shop_id in (1981) && expire_date = '2021-12-31'";

         //$sql = "SELECT * FROM `shipment` where shipment_type = 'privatedelivery' && from_certificate_no IN (31604244,21400434,31605667,31605725,31605358,21399161,31605469,31603940,21400036,21400540) ORDER BY `shipment_state`  DESC";

         // Free cards
         $sql = "SELECT order_no, company_name, shop_name, quantity, free_cards,  salesperson,salenote FROM `company_order` where free_cards > 0 && shop_id in (select shop_id from cardshop_settings where language_code = 1) && order_state not in (0,1,2,3,8)  && company_id != 44780 ORDER BY `company_order`.`order_state` ASC";

//         $sql = "SELECT company.name, company.cvr, sum(company_order.quantity) FROM company, `company_order` where company.id = company_order.company_id && company_order.shop_id in (select shop_id from cardshop_settings where language_code = 5) && company_order.order_state in (4,5,6,9,10) group by company.id having sum(quantity) >= 50";

         //$sql = "SELECT created_date, itemno, from_certificate_no, shipto_name, shipto_address, shipto_postcode, shipto_city, shipto_contact, shipto_email, shipto_phone FROM `shipment` WHERE `shipment_type` LIKE 'privatedelivery' AND `itemno` LIKE '210185-EFTERLEV' && shipment_state = 1 && from_certificate_no in (select username from shop_user where shutdown = 0 && blocked = 0) ORDER BY `shipment`.`shipment_state` ASC";
         //$sql = "SELECT created_date, itemno, from_certificate_no, shipto_name, shipto_address, shipto_postcode, shipto_city, shipto_contact, shipto_email, shipto_phone FROM `shipment` WHERE `shipment_type` LIKE 'privatedelivery' AND `itemno` LIKE 'sam4008' && shipment_state = 2 && from_certificate_no in (select username from shop_user where shutdown = 0 && blocked = 0) ORDER BY `shipment`.`shipment_state` ASC";

         //$sql = "SELECT shipto_name, shipto_email, itemno, description, from_certificate_no FROM shipment WHERE shipment_state = 1 && handler = 'postnord' && companyorder_id in (select id from company_order where shop_id in (select shop_id from cardshop_settings where language_code = 1)) && itemno in ('1017364','1061046','SAM2085','SAM2086','SAM2088','SAM2087','SAM2092','SAM2133','SAM1881')  && shipment_state = 1 && shipment_type = 'privatedelivery' ORDER BY `shipment`.`itemno` ASC";

         // Get danish foreign private delivery presents
         //$sql = "SELECT group_concat(company_order.order_no) as BSnr, company_order.company_name as Virksomhed, sum(company_order.quantity) as TotalAntalKort, countgf(shipment.id) as AntalKortTilLand, shipment.shipto_country as Land FROM `shipment`, company_order WHERE shipment.companyorder_id = company_order.id && shop_id in (select shop_id from cardshop_settings where language_code = 1) && `shipment_type` LIKE 'privatedelivery' AND `shipment_state` = 9 and shipment.shipto_country != 'Danmark' group by company_order.company_id, shipment.shipto_country order by company_order.company_name ASC";


         // Get swedish foreign private delivery presents
         //$sql = "SELECT group_concat(company_order.order_no) as BSnr, company_order.company_name as Virksomhed, sum(company_order.quantity) as TotalAntalKort, countgf(shipment.id) as AntalKortTilLand, shipment.shipto_country as Land  FROM `shipment`, company_order WHERE shipment.companyorder_id = company_order.id && shop_id in (select shop_id from cardshop_settings where language_code = 5) && `shipment_type` LIKE 'privatedelivery' AND `shipment_state` = 9 and shipment.shipto_country != 'Sverige' group by company_order.company_id, shipment.shipto_country order by company_order.company_name ASC";

         // Get dk orders without prepayment
         //$sql = "SELECT order_no, company_name, shop_name, salesperson, salenote, quantity, expire_date, is_email FROM `company_order` where shop_id in (select shop_id from cardshop_settings where language_code = 1) && prepayment = 0 && order_state in (4,5,6,9,10)";

         // Get se orders without prepayment
         //$sql = "SELECT order_no, company_name, shop_name, salesperson, salenote, quantity, expire_date, is_email FROM `company_order` where shop_id in (select shop_id from cardshop_settings where language_code = 5) && prepayment = 0 && order_state in (4,5,6,9,10)";


         /*
                  // Design julegave uge 49 uden indpakning
                  $sql = "SELECT * FROM `company_order` WHERE `shop_id` = 575 AND `expire_date` = '2021-11-14' AND `giftwrap` = 0 AND `order_state` < 7 ORDER BY `company_order`.`order_state` DESC";

                  // Vanessa sales list
                  $sql = "SELECT order_no, company_name, cvr, contact_name, contact_email, contact_phone  FROM `company_order` WHERE `salesperson` LIKE 'vh' && order_state < 7 ORDER BY `order_state`  DESC";
         */
//$sql = "SELECT * FROM `company_order` where shop_id in (select shop_id from cardshop_settings where language_code = 4) && expire_date = '2022-01-03' && order_state not in (7,8)";

     /*            
                             return;
                      $sql = "SELECT * FROM `company_order` WHERE certificate_no_begin IN (30207472,20233617,30202809,20234772,20236045,30206945,20236530,30201494,20252122,20253190,20252241,30203604,20251627,30207735,30205251,30162781,20107173,30203330,30207952,20237324,30201355,20224619,20235812,30204687,20234384,20354429,30203058,30204851,20236296,30208357,30205960,20220547,30206404,30206251,30201741,20253352,30202381,30203545,30202194,30204578,20354573,20242636,30206608,20253297,30206174,20234664,30205185,20101015,20238336,20126910,30162672,20252071,30206665,30202626,30126769,20354010,30163906,30203239,20353895,30206781,20252805,20347996,20236901,20354095,30205007,20234299,30190111,20142483,30204498,20108378,20251748,20234105,20235651,20237652,30126601,20237828,20237157,30202320,20237533,20233567,30204163,20236432,20277663,20115796,20236720,20353782,20234185,30208162,20353628,20351743,20353967,30202554,20254513,20188704,30229791,20236255,20237877,20237709,30203460,30203796,30109939,20235461,20252679,20353673,20192351,20353818,30209012,20235306,20234582,20253471,20238479,30206092,20233994,30207096,30204470,20237267,20176138,30203880,30208635,30205828,20237749,20175853,20354069,30203835,20234504,20127486,20353568,20354325,30207889,20204902,20237108,30205790,20354531,20100186,30205146,20235526,20251523,20251575,20353521,30203183,30206573,20235950,20175888,20127191,30203153,30204415,20353759,20354386,30203992,20241916,30204326,20235208,20237508,30202295,30204662,20251829,20251933,20253432,20353593,20353947,30204114,30204272,30208717,20126988,20236506,20251545,30205569,20236988,20166834,20113446,20253452,20238586,30206852,20237976,20252738,20253413,20354292,20251796,20235281,30208680,30204441,30205118,30204079,20127117,30111595,20236868,20235630,20238033,20252721,20252776,30207867,20203740,20236766,20238185,30209067,20253641,20234277,30203506,30201829,30205868,30206883,30208429,20235771,30208894,20236846,20238662,20238131,30207191,30206331,20118887,20173033,20125991,20251881,20354267,20354310,30201331,30206904,30208075,30208963,30208822,30203211,30203040,30206736,30205680,20225290,30201689,30201262,20243284,20241019,20348058,20207302,20353543,20353881,30208521,20108869,20235729,20235583,30208916,30204236,30206476,20237304,20233521,20233969,20104271,20167471,20251816,20251963,20252058,20253500,20354166,20237015,20237810,20235253,20236828,30205737,30208535,20234261,20238763,20235608,20237477,20241426,30202600,30204015,30203750,30209042,20251607,20251976,20252793,20353747,20354362,20354374,20354630,20353724,30190184,30203867,30203952,20114551,20102537,20106769,20160108,30208850,20238290,20237453,30201725,30202520,30207169,20242025,20207175,20237962,30125032,30177613,20252765,20353557,20354504,30208151,20238260,20347882,30201479,30204379,30205634,20237437,20234077,20237924,20169588,30201850,30206377,30207372,30208255,30133506,30154721,20234247,20251849,20251953,20252711,20353737,20353848,20353858,20354059,20354209,30202510,30205380,30205664,30207432,30208774,20354282,20108803,20175764,20233543,20350808,20351801,30207137,20126706,30174433,20252004,20252756,20252852,20353710,20354001,20354420,20235932,20237799,20207772,30202369,30208115,30208342,30202783,30204990,30207277,30208557,20234173,20238737,20351603,30203976,30208498,30230262,20176222,20237467,20237492,20238174,20238208,20238240,20238684,30204036,30206143,30206504,30206926,30207351,30208698,20251866,20353613,20353868,20354151,20354179,20354412,20238121,20238250,20238445,20251619,20234566,30204064,30204155,30204263,30204404,30205083,30205398,30205724,30206551,30206766,30207854,30208240,30208221,30205601,30208737,20238464,30201714,20237937,30129089,20204615,30129974,20238160,30207412,20237148,20238455,20238545,20238694,20235757,30202617,30205092,30208668,30201664,20235271,20238070,30205406,30205440,20234556,30204968,20251859,20252234,20353621,20354159,20354202,20354232,20354524,20354566,30203420,30204106,30206369,30207242,30207288,30208044,30208310,30208708,20235988,20236009,20238648,20251874,20252655,20252663,20234050,30205935,30206158,20171277,20102527,20235980,20236001,20236027,20236799,30220365,20237948,20348051,30205430,30205711,30207938,30208285,30208612,30202744,30205456,30207321,30202771,20251567,20251921,20251927,20252362,20354140,20354239,20354518,30203964,30204140,30204365,30206564,30207233,30207848,30208139,30208248,30208515,30208551,30208593,30208804,30208978,20236399,20238434,30207261,30207927,30208331,20235801,20201908,20238530,20238538,20238641,30205101,30205354,20247599,20351984,30201294,30205820,30205417,30207405,30207918,30208293,20235570,20236813,20238018,20203092,30204954,30205365,30208620,29071911,20207919,29071978,30202544,20264358,20264428,30205949,20236790,20238634,30206470,20251896,20251988,20252354,20252671,20252861,20352055,20353663,20353668,20353705,20353719,20353876,20354146,20354187,20354219,20354224,20354245,20354251,20354355,20354407,20354561,30208231,30208767,20234494,30202539,30203734,30203745,30204371,30204981,30205174,30205654,30205861,30206127,30206138,30206153,30206776,30206839,30206878,30206899,30207150,30207163,30207392,30207398,30207462,30207843,30208090,30208493,30208568,30208576,30208599,30208662,30208752,20238523,29071973,20251602,30201709,30202177,20235501,20235512,20235600,30203431,20252649,30204225,20236410,20237245,30206514,30207272,20238325,30207467,20354350,30208455,30208630,30201319,30201325,30205784,30208051,30208810,20251999,30201673,30159265,20244742,20173974,20347991,20176090,30204317,30205754,30205625,30162653,30133839,20241421,20241448,20348082,20141743,20176102,20208285,20237251,20238284,20234241,20238622,20238708,30203970,30230134,30208067,30208279,20238802,30162663,30151485,20238554,20235248,20236022,20237232,20237237,20238064,20238331,20238703,20238716,20238732,20264372,20234166,30201350,30202587,30203938,30204059,30205473,30205596,30205705,30206398,30206541,30206546,30206756,30206921,30207346,30207367,30207427,30208100,30208324,30208586,30208604,30208761,30209007,20353943,20233560,20234072,20235233,30202753,30202739,30208110,30208450,30208581,30202286,30203500,30205079,30205764,30208124,20236037,30205621,30230559,20235578,20251598,20176316,20236823,20237702,20237916,20238011,30122211,20347807,20176240,30230194,30208303,30202724,20235198,20235521,20235943,20237257,20238085,20238221,29071900,20236416,20234100,30202758,30204049,30204072,30205732,30206132,30207221,30207306,30207838,30208131,30208352,30208445,30209098,20233555,30201683,30202182,20235238,30204146,30205450,30205468,30206528,30206844,30208216,30208319,20255495,20234640,20176215,20176248,20234068,30205001,30207256,30127299,20173157,20173164,30152305,30201346,30204136,20236423,30205075,30207184,30207729,30208984,30209061,20201902,30206936,30209103,20234060,20256296,20354264,20354558,20251743,20251790,20264442,30203739,30205427,30205593,30128524,20252359,20251793,30202291,20251993,20251996,30205478,30207229,30207724,20354515,20176195,20208070,20248800,30204230,30205349,30207335,20234064,30203179,20241443,20238562,20238028,20238054,20238059,20238226,20238753,20238758,30202281,30205616,30205955,30207316,30209131,29071968,20235243,20235507,20237143,30206534,30207249,30207362,30208062,30208105,30208795,30130656,20235929,20238281,30209127,20238792,30202721,30204054,30206602,30207914,20256308,30201679,30202806,30204376,30204965,30205072,30205115,30206570,30207226,20238218,30208136,30208212,30208800,20195678,20236809,30202501,30205487,30208816,30204032,30205701,20235203,30202496,20235627,20237138,30205481,30206559,30206761,30207864,30207947,30208677,30209004,20251746,20251901,20354360,30202506,30203207,30205111,20238275,30129982,30203541,30203732,30203950,30205424,30206562,30207304,30207403,30207950,30208991,20255293,20251521,20251573,30201302,30203543,30203743,30203943,20252676,30205109,30205362,20353780,30206520,30206919,30207727,30208301,30208317,30208574,30208759,20251814,20255831,20256215,20264100,20241446,20176258,30168038,30203827,20233565,20234171,30201475,30203205,30205675,30205719,30207254,30208427,20235361,20235927,30204104,20235948,30204151,30204234,30204304,30204412,30205180,20236807,20236865,30205678,30205699,20237826,30206526,30207295,20238238,30208073,30208229,30208277,30208715,30202189,20235195,30202766,20235518,30203427,30203831,30203946,30204046,30204222,20236042,30205484,20238082,30207299,20238681,30201293,20277768,30205465,30205660,30206502,30207314,30208042,20256318,20112021,20234297,20234539,30202499,30202763,20235625,30203829,20236035,20236428,30205721,30206849,30207239,30207297,20238279,30208275,30208329,30209136,30129988,30203945,20104346,30126877,20234092,20252654,20252678,30204077,30204134,30206600,30206774,20238797,30209111,30209119,30209139,20233967,20234090,30201477,20234235,30202192,30202279,30202187,20235459,20235809,30204057,20236797,20237031,20237243,20237435,30206361,30207135,20238521,30208129,30209096,29071998,30204013,30204014,29072001,30205005,30205006,30205493,30205494,30205710,30206664,30206764,30207168,30207837,30207925,30207946,30208239,30208667,30208819,30209002,30209110,30209116,30209117,30209118,29071988,29071990,29071991,29071992,30202592,30203039,30203505,30203937,30204154,30204271,20252662,20252670,30204658,30204661,30205091,30205179,30205364,30205438,20353736,30205677,30206250,30206755,30207190,30207426,30207863,30208514,30208592,30208757,30208766,30208990,30208996,30209130,20251597,29071993,29071994,29071995,29071996,29071997,29071999,20241566,30162651,20235811,20205567,20238184,20238632,20238633,30209126,30202294,30205439,30205674,30205753,30206363,30206365,30206397,30206539,30206540,30206606,30206765,30207232,30207311,30207344,30207345,20237452,20238080,30209112,30201318,30201682,30202290,30202378,30202379,30202769,30203504,30203731,30203742,30203949,30204414,30204660,30204951,30204952,30205144,30205183,30205353,30229657,30229658,30205492,20237242,30205659,30205698,30206171,30206376,30206851,30206944,30207095,30207183,30207334,30208128,30208145,30208254,30208351,30208784,30208803,30209138,30202594,30204101,30205143,30205467,30205490,30205491,30205763,30206172,30206173,30206522,30206607,30206942,30207303,30207917,30209060,30209059,30209113,30209125,30201688,20234237,30201828,30201849,20235207,30202770,30203430,30204113,30204980,20236765,30205145,30205184,30205416,30205455,30205867,30205948,30206360,20237923,30206533,20238081,30207333,30207361,30207397,30207888,30208573,30209094,20173227,20108898,20144185,30205390,20238079,30206364,30206403,30207302,30207862,30207926,30209114,30209124,20234165,20235458,30202765,30203879,30204103,30204262,30204292,30204466,20236431,30204978,30204979,30205182,20236867,30205426,30205723,30205783,30205866,30206137,30206519,30206605,30206754,20238069,30207260,30208300,30208591,30208758,30208820,30208994,30208995,30209057,30209058,30209090,30168036,30168037,30202504,30202535,30202536,30202537,30202538,30203436,30204467,30208785,30209120)

&& id NOT IN (SELECT company_order_id FROM shop_user WHERE expire_date = '2021-04-01')";
       */
       
       //$sql = "SELECT attribute_name, attribute_value, shop_user.delivery_print_date FROM `order_attribute`, shop_user WHERE shop_user.id = order_attribute.shopuser_id && `order_id` IN (934887,931869,882620,968533,890352,893756,886777,922537,938761,909122) ORDER BY `shopuser_id` ASC, attribute_index ASC";
       
                        
                       // SVENSKE ORDRE MED INDPAK OG FYSISKE KORT EXCL PRIVAT LEVERING
                       /*
    $sql = "SELECT 
	company.name, company.cvr, company.bill_to_address, company.bill_to_postal_code, company.bill_to_city, company.contact_name, company.contact_email,
	COUNT(DISTINCT company_order.id) as AntalOrdre, COUNT(DISTINCT shop_user.id) as AntalAktiveKort, 
	IF(MAX(company_order.giftwrap)=1,COUNT(DISTINCT shop_user.id),0) as GaverMedIndpakning, SUM(IF(gift_certificate.is_emailed = 0,1,0)) as FysiskeKortSendt, GROUP_CONCAT(DISTINCT shop_user.expire_date) as KortDeadlines
FROM 
	company, shop_user, company_order, gift_certificate 
WHERE 
	shop_user.username = gift_certificate.certificate_no && company_order.nocards = 0 && company.id = shop_user.company_id && shop_user.expire_date != '2021-12-31' && shop_user.blocked = 0 && company.id = company_order.company_id && shop_user.shop_id IN (1832,1981) GROUP BY company.id";
                   
                         */
                   
                   // SVENSKE ORDER MED INDPAK OG FYSISKE KORT
                   /*
                     $sql = "SELECT 
	company.name, company.cvr, company.bill_to_address, company.bill_to_postal_code, company.bill_to_city, company.contact_name, company.contact_email,
	COUNT(DISTINCT company_order.id) as AntalOrdre, COUNT(DISTINCT shop_user.id) as AntalAktiveKort, 
	IF(MAX(company_order.giftwrap)=1,COUNT(DISTINCT shop_user.id),0) as GaverMedIndpakning, SUM(IF(gift_certificate.is_emailed = 0,1,0)) as FysiskeKortSendt, GROUP_CONCAT(DISTINCT shop_user.expire_date) as KortDeadlines
FROM 
	company, shop_user, company_order, gift_certificate 
WHERE 
	shop_user.username = gift_certificate.certificate_no && company_order.nocards = 0 && company.id = shop_user.company_id && shop_user.blocked = 0 && shop_user.expire_date = '2021-12-31' && company.id = company_order.company_id && shop_user.shop_id IN (1981) GROUP BY company.id";
                     */

                /*     
                     $sql = "SELECT * FROM `company_order` WHERE salesperson NOT LIKE 'test' && contact_email NOT LIKE '%interactive%' && contact_email NOT LIKE '%bitworks%' && navsync_status = 100 && ship_to_address NOT LIKE 'TEST' && company_name NOT LIKE '%test%'  
ORDER BY `company_order`.`order_no` ASC";
*/

          /*             
                       // KUN AKTIVE 
         $sql = "SELECT 
	company_order.order_no,
	company.name as company_name,
	company.cvr as company_cvr,
	company.ean as company_ean,
	company.bill_to_address,
	company.bill_to_address_2,
	company.bill_to_postal_code,
	company.bill_to_city,
	company.bill_to_country,
	company.bill_to_email,
	company_order.company_name as sales_company,
	company_order.certificate_value as sales_shop,
	company_order.salesperson as sales_person,
	company_order.salenote as sales_note,
	company.internal_note,
	company.rapport_note,
	company_order.quantity as sales_quantity,
	company_order.expire_date as sales_expiredate,
	company_order.certificate_no_begin,
	company_order.certificate_no_end,
	company_order.certificate_value,
	company_order.is_email,
	company_order.is_appendix_order,
	company_order.giftwrap as gift_wrap,
	company_order.gift_spe_lev as gift_carryup,
	company_order.earlyorderList as earlypresents,
	company.ship_to_company,
	company.ship_to_attention,
	company.ship_to_address,
	company.ship_to_address_2,
	company.ship_to_postal_code,
	company.ship_to_city,
	company.ship_to_country,
	company_order.spdealtxt as ship_dealtext,
	company.contact_name,
	company.contact_email,
	company.contact_phone,
	company_order.navsync_status,
	company_order.navsync_response as navsync_debitorid,
	shop_user.expire_date as card_expiredate,
	IF(company_order.expire_date = shop_user.expire_date,0,1) as has_moved_deadline,
	IF(company_order.company_id = company.id,0,1) as has_moved_company,
	count(shop_user.id) as cards_totalcount,
	sum(IF(blocked=0,1,0)) as cards_activecount,
	sum(IF(blocked=1,1,0)) as cards_closedcount
FROM company, company_order, shop_user WHERE 
	company_order.is_cancelled = 0 &&
	shop_user.company_order_id = company_order.id &&
	((shop_user.shop_id IN (1832,1981) && (shop_user.expire_date = '2020-11-08' || shop_user.expire_date = '2020-11-22')) || (company_order.shop_id IN (1832,1981) && (company_order.expire_date = '2020-11-08' || company_order.expire_date = '2020-11-22'))) &&
	is_giftcertificate = 1 &&
	shop_user.company_id = company.id 
GROUP BY shop_user.expire_date, company_order.expire_date, shop_user.company_id, shop_user.company_order_id
HAVING sum(IF(blocked=0,1,0)) > 0
ORDER BY company_order.order_no ASC";
*/

/*
         $sql = "SELECT 
	company_order.order_no,
	company.name as company_name,
	company.cvr as company_cvr,
	company.ean as company_ean,
	company.bill_to_address,
	company.bill_to_address_2,
	company.bill_to_postal_code,
	company.bill_to_city,
	company.bill_to_country,
	company.bill_to_email,
	company_order.company_name as sales_company,
	company_order.certificate_value as sales_shop,
	company_order.salesperson as sales_person,
	company_order.salenote as sales_note,
	company.internal_note,
	company.rapport_note,
	company_order.quantity as sales_quantity,
	company_order.expire_date as sales_expiredate,
	company_order.certificate_no_begin,
	company_order.certificate_no_end,
	company_order.certificate_value,
	company_order.is_email,
	company_order.is_appendix_order,
	company_order.giftwrap as gift_wrap,
	company_order.gift_spe_lev as gift_carryup,
	company_order.earlyorderList as earlypresents,
	company.ship_to_company,
	company.ship_to_attention,
	company.ship_to_address,
	company.ship_to_address_2,
	company.ship_to_postal_code,
	company.ship_to_city,
	company.ship_to_country,
	company_order.spdealtxt as ship_dealtext,
	company.contact_name,
	company.contact_email,
	company.contact_phone,
	company_order.navsync_status,
	company_order.navsync_response as navsync_debitorid,
	shop_user.expire_date as card_expiredate,
	IF(company_order.expire_date = shop_user.expire_date,0,1) as has_moved_deadline,
	IF(company_order.company_id = company.id,0,1) as has_moved_company,
	count(shop_user.id) as cards_totalcount,
	sum(IF(blocked=0,1,0)) as cards_activecount,
	sum(IF(blocked=1,1,0)) as cards_closedcount
FROM company, company_order, shop_user WHERE 
	company_order.is_cancelled = 0 &&
	shop_user.company_order_id = company_order.id &&
	((shop_user.shop_id IN (1832,1981) && (shop_user.expire_date = '2020-11-08' || shop_user.expire_date = '2020-11-22')) || (company_order.shop_id IN (1832,1981) && (company_order.expire_date = '2020-11-08' || company_order.expire_date = '2020-11-22'))) &&
	is_giftcertificate = 1 &&
	shop_user.company_id = company.id
GROUP BY shop_user.expire_date, company_order.expire_date, shop_user.company_id, shop_user.company_order_id
ORDER BY company_order.order_no ASC";
*/

         
         /*
$sql = "
SELECT co.company_id, co.company_name,  co.expire_date, co.shop_id,GROUP_CONCAT(co.order_no), countgf(co.id) as order_count, csc.cost FROM company_order co, company_shipping_cost csc WHERE
co.company_id = csc.company_id && csc.cost > 0 && csc.active = 1 &&
co.expire_date IN ('2020-11-01','2020-11-08','2020-11-01','020-11-22','2020-11-29') && co.shop_id IN (54,55,56,575,290,310,53,52)
GROUP BY co.expire_date, co.shop_id, co.company_id order by co.company_id, co.expire_date, co.shop_id";

*/

/*
// total antal valg på svenske gaver
$sql = "SELECT present_model.fullalias, CAST(present_model.fullalias as SIGNED) AS casted_column, present_model.model_name, present_model.model_no, 
SUM(IF(shop_user.expire_date = '2020-11-08',1,0)) as uge49, 
SUM(IF(shop_user.expire_date = '2020-11-22',1,0)) as uge51, 
SUM(IF(shop_user.expire_date = '2020-12-31',1,0)) as uge4, 
SUM(IF(shop_user.expire_date = '2021-12-31',1,0)) as privat, 
count(`order`.id) as total
FROM `shop_user`, `order`, present_model WHERE
shop_user.id = `order`.shopuser_id && `order`.present_model_id = present_model.model_id &&
shop_user.blocked = 0 && shop_user.expire_date IN ('2020-11-08','2020-11-22','2020-12-31','2021-12-31') && shop_user.shop_id = 1981 && (shop_user.expire_date != '2021-12-31' || (shop_user.expire_date = '2021-12-31' && shop_user.delivery_print_date IS NOT NULL)) &&
present_model.language_id = 1 GROUP BY present_model.model_id ORDER BY casted_column ASC, present_model.fullalias ASC LIMIT 100";
*/

                         /*
// ALL SE CONTACT EMAILS
$sql = "SELECT company_id, company.name, contact_name, contact_email , countgf(shop_user.id)
FROM company, shop_user WHERE
shop_user.shop_id IN (1832,1981) && company.contact_email != '' &&
company.id = shop_user.company_id && shop_user.blocked = 0 && company.active = 1 && company.deleted = 0
GROUP BY contact_email";
                           */
                                      /*
                           $sql = "SELECT shop_user.username, shop_user.password, shop_user.expire_date, `order`.order_no, `order`.order_timestamp, `order`.company_name, `order`.user_name, `order`.user_email,`order`.present_model_name, `order`.id  FROM `order`, shop_user WHERE `order`.present_id = 29962 && `order`.shop_id = 1981 && shopuser_id = shop_user.id && shop_user.expire_date = '2021-12-31' && (delivery_print_date IS NULL OR navsync_response = 'sebatch210112093206' || navsync_response = 'sebatch201229034202' || navsync_response = '')";
                                        */
                 /*                       
         $sql = "SELECT * FROM `company_order` WHERE navsync_status IN (100,99) && shop_id IN (52,575,54,55,56,53,287,290,310,247,248) ORDER BY `id` ASC";                                        
                   */
                          /*
                    $sql = "SELECT 
	company_order.order_no,
	company.name as company_name,
	company.cvr as company_cvr,
	company.ean as company_ean,
	company.bill_to_address,
	company.bill_to_address_2,
	company.bill_to_postal_code,
	company.bill_to_city,
	company.bill_to_country,
	company.bill_to_email,
	company_order.company_name as sales_company,
	company_order.certificate_value as sales_shop,
	company_order.salesperson as sales_person,
	company_order.salenote as sales_note,
	company.internal_note,
	company.rapport_note,
	company_order.quantity as sales_quantity,
	company_order.expire_date as sales_expiredate,
	company_order.certificate_no_begin,
	company_order.certificate_no_end,
	company_order.certificate_value,
	company_order.is_email,
	company_order.is_appendix_order,
	company_order.giftwrap as gift_wrap,
	company_order.gift_spe_lev as gift_carryup,
	company_order.earlyorderList as earlypresents,
	company.ship_to_company,
	company.ship_to_attention,
	company.ship_to_address,
	company.ship_to_address_2,
	company.ship_to_postal_code,
	company.ship_to_city,
	company.ship_to_country,
	company_order.spdealtxt as ship_dealtext,
	company.contact_name,
	company.contact_email,
	company.contact_phone,
	company_order.navsync_status,
	company_order.navsync_response as navsync_debitorid,
	shop_user.expire_date as card_expiredate,
	IF(company_order.expire_date = shop_user.expire_date,0,1) as has_moved_deadline,
	IF(company_order.company_id = company.id,0,1) as has_moved_company,
	count(shop_user.id) as cards_totalcount,
	sum(IF(blocked=0,1,0)) as cards_activecount,
	sum(IF(blocked=1,1,0)) as cards_closedcount
FROM company, company_order, shop_user WHERE 
	company_order.expire_date != shop_user.expire_date &&
	company_order.is_cancelled = 0 &&
	shop_user.company_order_id = company_order.id &&
	((shop_user.shop_id IN (52,575,54,55,56,53,287,290,310,247,248)) || (company_order.shop_id IN (52,575,54,55,56,53,287,290,310,247,248))) &&
	is_giftcertificate = 1 &&
	shop_user.company_id = company.id
GROUP BY shop_user.expire_date, company_order.expire_date, shop_user.company_id, shop_user.company_order_id
ORDER BY company_order.order_no ASC";
                   
                   */
                                 /*
                   // Hent svenske ordre med ændrede gaver efter de er trukket ud.
                   $sql = "SELECT `order`.*, shop_user.username, shop_user.delivery_print_date FROM `order`, shop_user where `order`.shopuser_id = shop_user.id && shop_user.is_delivery = 1 && shop_user.delivery_print_date IS NOT NULL && shop_user.delivery_print_date < `order`.`order_timestamp`";
                                   */
               /*                    
        $sql = "
SELECT company_order.order_no, company_order.company_name, company_order.shop_id, company_order.expire_date, company_order.quantity, shop_user.expire_date as flyttet, countgf(shop_user.id), SUM(IF(shop_user.blocked=0,1,0)) FROM `company_order`, shop_user WHERE
company_order.order_no IN ('BS40044','BS40096','BS40184','BS40271','BS40273','BS40290','BS40542','BS40892','BS41468','BS42030','BS42111','BS42254','BS42277','BS42630','BS42632','BS42634','BS42635','BS42636','BS42764','BS42814','BS42848','BS42874','BS43052','BS43083','BS43289','BS43457','BS43700','BS43799','BS43807','BS43818','BS43844','BS44029','BS44043','BS44094','BS44234','BS44356','BS44407','BS44557','BS44636','BS44750','BS45014','BS45167','BS45168','BS45169','BS45272','BS45409','BS45591','BS46069','BS46169','BS46170','BS46178','BS46533','BS46539','BS46550','BS46601','BS46642','BS46887','BS46896','BS46961','BS46963','BS46998','BS47008','BS47158','BS47219','BS47221','BS47328','BS47329','BS47363','BS47364','BS47409','BS47410','BS47411','BS47413','BS47414','BS47416','BS47417','BS47628','BS48274','BS50105','BS48572','BS48578','BS51268','BS51270','BS51299','BS51389') && 
company_order.id = shop_user.company_order_id 
GROUP BY company_order.id, shop_user.expire_date";
                                              */
                                       
                         /*              
                                       // Camfil kunder
                                       $sql = "SELECT shop_user.username as GavekortNr, shop_user.expire_date as Deadline, shop_user.delivery_print_date as SendtTilPak,  `order`.order_timestamp as ValgtDato,  `order`.user_name as Navn,  `order`.`user_email` as Email, present_model.model_name as Gave, present_model.model_no as Model, present_model.model_present_no as VareNr
FROM `shop_user`
LEFT JOIN `order` ON shop_user.id =  `order`.shopuser_id
LEFT JOIN present_model ON `order`.present_model_id=present_model.model_id AND present_model.language_id = 1
WHERE shop_user.`company_id` = 36418 && shop_user.delivery_print_date IS NULL;";
                                return;
                                */        
         $results = Dbsqli::getSql2($sql);

         if(!is_array($results) || countgf($results) == 0) {

             header('Content-Type: text/csv; charset=utf-8');
             header('Content-Disposition: attachment; filename=NULLLIST.csv');
             echo "Ingen resultater";
             exit();
         }

         header('Content-Type: text/csv; charset=utf-8');
         header('Content-Disposition: attachment; filename=customlist-'.date("dmYHi").'.csv');

         foreach($results[0] as $key => $val) {
             echo $key.";";
         }

         echo "\n";

         foreach($results as $row)
         {
             foreach($row as $key => $val) {
                 echo utf8_decode(trimgf(str_replace(array("\r","\n",";"),array(""," ",""),$val)).";");
             }
             echo "\n";
         }

     }
     
     /*
    public function extorderdata() {
    
         if(!isset($_GET["token"]) || $_GET["token"] != "dfjh4jhdfjsan4ndskkw33ssq") {
             echo json_encode(array("status" => 0,"error" => "Invalid token"));
             return;
         }
         
         $postBody = trimgf(file_get_contents("php://input"));
        if($postBody == "") {
             echo json_encode(array("status" => 0,"error" => "No input provided"));
             return;
         }
         
         $input = $postBody;
         $inputlines = json_decode(urldecode($input),true);

         $output = array();    
         if(count($inputlines) > 0) {
            foreach($inputlines as $line) {
                //$output[] = array("in" => $line, "out" => $this->getExtraDataLine($line));
                echo $line.";".$this->getExtraDataLine($line)."\r\n";
                       
            }
         }
         //echo json_encode($output);

     }         */
     
     private function getExtraDataLine($input) {
     
            $parts = explode("###",trimgf($input));
            $sql = "SELECT contact_email FROM `company` WHERE (contact_name LIKE '".$parts[1]."' or contact_phone LIKE '%$parts[2]%') && ship_to_address LIKE '".$parts[0]."' && ship_to_postal_code LIKE '%$parts[3]%' GROUP BY contact_email";
            $company = Company::find_by_sql($sql);
                    
           if(count($company) == 0) {
               return "ERROR: NO COMPANY";
           } else if(count($company) > 1) {
               return "ERROR: MULTIPLE COMPANY";
           }
           
           return $company[0]->contact_email;
     /*
         $cards = ShopUser::find_by_sql("SELECT * FROM shop_user WHERE username = '".intval($input)."' && is_giftcertificate = 1");
         if(count($cards) == 0) {
             return "ERROR: NO CARD";
         } else if(count($cards) > 1) {
             return "ERROR: MULTIPLEUSERS";
         }

         $company = Company::find($cards[0]->company_id);
         if(count($company) == 0) {
             return "ERROR: NO COMPANY";
         } else if(count($company) > 1) {
             return "ERROR: MULTIPLE COMPANY";
         }
         
         return $company->contact_email;
          */
     }
     
    public function checkintervals()
     {

            $token = isset($_GET["token"]) ? $_GET["token"] : "";
            $isMonitor = false;
            $monitorWarn = false;
            
            if($token == "dfkj4jhkfhj3jddncnk44") { $isMonitor = true; }
            else if($token == "dkfj3k4nnjdjkfdsjdf") { $isMonitor = false; }
            else { echo "No access"; exit(); }

         // FIND PROBLEM ORDERS: SELECT * FROM `company_order` WHERE certificate_no_end-certificate_no_begin+1 != quantity
         $intervals = GiftCertificate::find_by_sql("SELECT count(id) as cardcount, max(certificate_no)-min(certificate_no)+1 as intervalcount, min(certificate_no) as intstart, max(certificate_no) as intend, reservation_group, expire_date FROM `gift_certificate` WHERE shop_id = 0 group by reservation_group, expire_date having cardcount != intervalcount");
         foreach($intervals as $interval) {
             
             $cardlist = GiftCertificate::find_by_sql("SELECT * FROM gift_certificate WHERE shop_id = 0 && reservation_group = ".$interval->reservation_group." && expire_date = '".$interval->expire_date->format('Y-m-d')."' ORDER BY certificate_no ASC LIMIT 1000");
             
             
             $currentNumber = $cardlist[0]->certificate_no;
             $intervalCount = 1;
             
             for($i=1;$i<count($cardlist);$i++) {
                 
                if($cardlist[$i]->certificate_no != $currentNumber+1) {
                    break;
                }
                else {
                    $currentNumber++;
                    $intervalCount++;
                }
                
                
             }
             
             if($intervalCount <= 100) {
                if($isMonitor) $monitorWarn = true;
                else echo "<h2>WARNING</h2>";
             }
             else {
                if(!$isMonitor) echo "<h3>OK</h3>";
             }
             
             if(!$isMonitor) {
                echo "INTERVAL: resgroup ".$interval->reservation_group." - date ".$interval->expire_date->format('d-m-Y')." - interval ".$interval->intstart." to ".$interval->intend." is span of ".$interval->intervalcount." but only has ".$interval->cardcount." cards<br>";
                echo $intervalCount." left of current interval ".$cardlist[0]->certificate_no." - ".$currentNumber."<br>";
                echo "ALL CARDS: SELECT * FROM gift_certificate WHERE shop_id = 0 && reservation_group = ".$interval->reservation_group." && expire_date = '".$interval->expire_date->format('Y-m-d')."' ORDER BY certificate_no ASC<br>";
                echo "ONLY INTERVAL: SELECT * FROM gift_certificate WHERE shop_id = 0 && certificate_no >= ".$cardlist[0]->certificate_no." && certificate_no <= ".$currentNumber."  && reservation_group = ".$interval->reservation_group." && expire_date = '".$interval->expire_date->format('Y-m-d')."' ORDER BY certificate_no ASC<br>";
                echo "RESERVE INTERVAL: UPDATE gift_certificate SET company_id = 1, shop_id = 1 WHERE shop_id = 0 && certificate_no >= ".$cardlist[0]->certificate_no." && certificate_no <= ".$currentNumber."  && reservation_group = ".$interval->reservation_group." && expire_date = '".$interval->expire_date->format('Y-m-d')."' ORDER BY certificate_no ASC<br><br>";
             }
                             
             
         }
         
         if($isMonitor) {
            if($monitorWarn)
            {
                header("HTTP/1.0 500 Service down");
                echo "ERROR";
                exit();
	       }
            else echo "OK";
         }


     }
     
     public function checkcards()
    {

        $token = isset($_GET["token"]) ? $_GET["token"] : "";
        $isMonitor = false;
        $monitorWarn = false;

        if($token == "dfkj4jhkfhj3jddncnk44") { $isMonitor = true; }
        else if($token == "dkfj3k4nnjdjkfdsjdf") { $isMonitor = false; }
        else { echo "No access"; exit(); }

        // FIND PROBLEM ORDERS: SELECT * FROM `company_order` WHERE certificate_no_end-certificate_no_begin+1 != quantity
        $cardlist = GiftCertificate::find_by_sql("SELECT count(id) as cardcount, reservation_group, expire_date FROM `gift_certificate` WHERE shop_id = 0 group by reservation_group, expire_date having cardcount < 200");
        if(count($cardlist) > 0) {
            foreach($cardlist as $cards) {

                $cardcount = $cards->cardcount;
                $reservationGroup = $cards->reservation_group;
                $expireDate = $cards->expire_date;

                if($isMonitor) $monitorWarn = true;
                else echo "<h2>WARNING</h2>";

                if(!$isMonitor) {
                    echo "MISSING CARDS: resgroup ".$reservationGroup." - date ".$expireDate->format('d-m-Y')." - cards left ".$cardcount."<br><br>";
                }

            }
        }
        else
        {
            if(!$isMonitor) echo "<h3>OK</h3>";
        }

        if($isMonitor) {
            if($monitorWarn)
            {
                header("HTTP/1.0 500 Service down");
                echo "ERROR";
                exit();
            }
            else echo "OK";
        }

    }
     
    public function fsdata()
    {
    
    
        
    
    }

    public function mailtest() {
    
        //$mailqueue = MailQueue::find_by_sql("SELECT * FROM `mail_queue` WHERE sent = 0 && error = 0 && (delivery_datetime IS NULL || delivery_datetime < NOW()) ORDER BY id desc LIMIT 40");
                                                 echo date("r")."<br>";
        //$mailqueue = MailQueue::all(array('conditions' => array('sent = 0 && error = 0 && (delivery_datetime IS NULL || delivery_datetime < NOW())'), 'limit' => 40, 'order' => 'id desc'));

        //var_dump($mailqueue);
    
    }

     public function tokentest()
     {

         /**
          * Token systemet bruger klasserne i GFCommon\Model\Tokens
          * Der er forskellige typer, for ideen er at de ud over at være "dumme" tokens også kan tjekke om den bruger der fx har lavet en token også er korrekt / aktiv
          * Jeg har lavet en ny token ServiceToken som ikke er bundet op på en bruger, men du kan trække en token når du vil og tjekke den i backend.
          * Den fungerer som følgende:
          */

         // Træk en ny token (returnere en string)
         $token = \GFCommon\Model\Tokens\ServiceToken::newToken();

         // Check en token (returnere true false) ...
         $valid = \GFCommon\Model\Tokens\ServiceToken::checkToken($token);

         // Eller påkræv en token, den kaster en exception hvis der ikke er en token, og med mindre du selv griber den bør den stoppe eksekverinen af koden
         \GFCommon\Model\Tokens\ServiceToken::requireToken($token);

         // Generel token der kan bruges alle steder
         $token = \GFCommon\Model\Tokens\ServiceToken::newToken();
         echo "Din token er: ".$token."<br>";

         // Check om token er gyldig
         if(\GFCommon\Model\Tokens\ServiceToken::checkToken($token)) {
            echo "TOKEN 1 ER GYLDIG!<br>";
         }
         else {
             $tokenObj = new \GFCommon\Model\Tokens\ServiceToken($token);
             echo "TOKEN 1 ER IKKE GYLDIG: ".$tokenObj->getError();
         }

         // Check om tilfældig værdi er gyldig
         if(\GFCommon\Model\Tokens\ServiceToken::checkToken("dsfsfsdfsdf")) {
             echo "TOKEN 2 ER GYLDIG!<br>";
         }
         else {
             $tokenObj = new \GFCommon\Model\Tokens\ServiceToken("dsfsfsdfsdf");
             echo "TOKEN 2 ER IKKE GYLDIG: ".$tokenObj->getError()."<br>";
         }


         /**
          * ShopUserToken
          * Den er lidt speciel, for der er en token knyttet til en shopuser, og det betyder at når man tjekker om token er gyldig tjekker den både for token og at shopuser eksisterer og at shopuser ikke er sat til blocked
          */

         // For at lave en token skal du sende id med på shopuser
         $token = \GFCommon\Model\Tokens\ShopUserToken::newToken(1220760);

         // Du kan tjekke den på samme måde, enten returnere om den er ok
         if(\GFCommon\Model\Tokens\ShopUserToken::checkToken($token)) {
             echo "TOKEN 3 ER GYLDIG og er en gyldig shopuser<br>";
         }
         else {
             $tokenObj = new \GFCommon\Model\Tokens\ShopUserToken($token);
             echo "TOKEN 3 ER IKKE GYLDIG: ".$tokenObj->getError()."<br>";
         }

         // Eller påkræve at den er ok, eller smide en exception
         \GFCommon\Model\Tokens\ShopUserToken::checkToken($token);

         /**
          * Flere typer tokens
          * Ideen var at det jo nok primært er 1 slags bruger der har adgang til bestemte controllers, men skal forskellige brugere have adgang til samme controller må der laves et check ala.
          */

         if(!\GFCommon\Model\Tokens\ShopUserToken::checkToken($token) && !\GFCommon\Model\Tokens\ServiceToken::checkToken($token)) {
             echo "<br>Token er ikke gyldig for hverken shopuser eller service typerne.";
         }
         else {
            echo "<br>Token er enten ok for service eller shopuser..";
         }










     }

     public function imgtest()
     {
                 return;
        $sql = "SELECT * FROM present_media WHERE present_id IN (SELECT id FROM present WHERE shop_id > 0 && id IN (SELECT present_id FROM shop_present WHERE active = 0 && is_deleted = 0));";
        $presentMediaList = PresentMedia::find_by_sql($sql);
        
        echo "<table>";
        
        echo "<tr><td>id</td><td>present_id</td><td>media_path</td><td>full image</td><td>small image</td></tr>";
        
        if(is_array($presentMediaList)) {
            foreach($presentMediaList as $presentMedia) {
                
                $hasFull = file_exists(GFConfig::BACKEND_PATH."views/media/user/".$presentMedia->media_path.".jpg");
                $hasSmall = file_exists(GFConfig::BACKEND_PATH."views/media/small/".$presentMedia->media_path."_small.jpg");
                
                echo "<tr>
                    <td>".$presentMedia->id."</td>
                    <td>".$presentMedia->present_id."</td>
                    <td>".$presentMedia->media_path."</td>
                    <td>".($hasFull ? "ok" : "missing")."</td>
                    <td>".($hasSmall ? "ok" : "missing")."</td>
                </tr>";   
                                                               
            }
        }
        
        echo "</table>";
/*
        echo "Find images: ".(file_exists("/home/gave/apache/www/gavefabrikken_backend/views/media/small/") ? "findes" : "findes ikke");

        $fileList = scandir('/home/gave/apache/www/gavefabrikken_backend/views/media/small/');

        foreach($fileList as $file ) {
            if($file != "" && $file != "." && $file != "..") {
                echo $file."<br>";
            }
        }
*/
     }



  private function findtoken()
  {
      $token = NewGUID();
      $company = Company::find_by_sql("SELECT * FROM company WHERE token LIKE '".$token."'");
      if(count($company) > 0) return findtoken();
      else return $token;
  }

  public function settokens()
  {
  
    $companylist = Company::find_by_sql("SELECT * FROM company WHERE token = '' OR Token IS NULL");
    $set = array();
    foreach($companylist as $company)
    {
      $c = Company::find($company->id);
      if(trimgf($c->token) == "" && $c->id > 0)
      {
          
          $c->token = $this->findtoken();
          $c->save();  
          $set[] = $c->id;
         
      }
    }
    
     System::connection()->commit();
    echo json_encode($set);
  
  }

  /** PULL HISTORY REPORT **/
  

  public function makehistoryreport()
  {
    if(!isset($_GET["token"]) || $_GET["token"] != "fdæ3245fajkhl32jkhlkdhf") { echo "invalid token!"; return; }
                                   
    $idlist = array(212041,212103,212104,212109,212118,212134,212140,212145,212146,212148,212153,212154,212156,212161,212163,212165,212169,212175,212185,212187,212190,212199,212202,212208,212210,212213,212217,212224,212237,212243,212246,212247,212327,212336,212344,212358,212399,212426,212435,212453,212467,212480,212555,212559,212564,212566,212569,212575,212577,212584,212587,212588,212589,212598,212607,212615,212621,212625,212635,212637,212640,212641,212645,212646,212651,212656,212657,212659,212670,212685,212748,212750,212754,212755,212756,212758,212760,212773,212785,212821,212839,212841,212880,212897,212900,212902,212903,213001,213016,213027,213029,213039,213042,213048,213057,213058,213060,213062,213076,213108,213115,213120,213125,213136,213139,213149,213155,213159,213161,213165,213170,213181,213186,213189,213190,213193,213199,213202,213246,213256,213287,213289,213320,213645,213695,213781,213851,213852,213899,214002,214003,214199,214367,214399,214432,214550,214587,214596,214791,214793,214796,214797,214800,215016,215112,215486,215605,216006,216052,216138,216146,216277,216301,216306,216329,216369,216449,216450,216461,216462,216468,216475,216480,216488,216492,216500,216504,216508,216520,216521,216522,216530,216533,216538,216540,216541,216544,216554,216555,216565,216566,216569,216572,216575,216576,216579,216583,216585,216586,216596,216599,216600,216604,216611,216616,216617,216623,216626,216627,216634,216639,216643,216647,216664,216666,216670,216675,216682,216689,216691,216693,216698,216699,216700,216704,216707,216715,216716,216736,216744,216755,216758,216760,216763,216767,216777,216782,216794,216795,216797,216799,216802,216804,216805,216812,216815,216829,216834,216835,216837,216840,216849,216855,216861,216862,216863,216864,216871,216874,216875,216881,216882,216886,216888,216895,216897,216900,216920,216923,216930,216935,216939,216941,216942,216944,216947,216948,216958,216971,216974,217003,217014,217018,217019,217026,217028,217029,217032,217035,217051,217053,217059,217060,217061,217062,217063,217077,217090,217094,217102,217109,217114,217115,217119,217121,217134,217136,217137,217138,217142,217144,217147,217148,217150,217152,217154,217155,217156,217171,217194,217202,217210,217230,217232,217241,217247,217248,217261,217269,217270,217280,217294,217297,217298,217301,217303,217304,217314,217318,217325,217326,217328,217329,217332,217342,217344,217346,217347,217349,217351,217355,217364,217366,217373,217378,217385,217394,217396,217398,217410,217416,217431,217439,217441,217450,217458,217461,217462,217467,217468,217470,217473,217474,217478,217479,217480,217484,217486,217487,217488,217494,217500,217501,217502,217503,217505,217507,217508,217509,217510,217512,217514,217517,217518,217520,217521,217523,217524,217525,217530,217532,217533,217539,217547,217550,217555,217556,217560,217562,217564,217565,217566,217570,217574,217580,217581,217582,217583,217585,217587,217590,217592,217597,217599,217602,217605,217612,217616,217617,217618,217623,217631,217632,217635,217636,217641,217653,217664,217667,217668,217671,217676,217678,217682,217683,217692,217694,217696,217699,217703,217706,217712,217717,217719,217721,217724,217726,217733,217734,217735,217736,217739,217740,217741,217743,217744,217745,217746,217758,217763,217772,217778,217780,217782,217786,217788,217793,217794,217795,217796,217803,217804,217806,217814,217817,217819,217821,217823,217824,217825,217830,217837,217841,217843,217846,217848,217853,217855,217857,217858,217867,217879,217884,217888,217890,217893,217895,217897,217899,217903,217904,217906,217911,217912,217914,217921,217927,217928,217931,217932,217940,217944,217946,217948,217958,217959,217962,217964,217969,217970,217972,217982,217983,217984,217990,217991,217998,217999,218002,218008,218009,218025,218030,218037,218038,218043,218044,218052,218053,218057,218064,218065,218066,218067,218070,218071,218072,218082,218083,218084,218089,218090,218091,218094,218099,218104,218108,218109,218113,218116,218118,218123,218124,218125,218130,218131,218132,218134,218136,218142,218144,218148,218149,218153,218162,218167,218169,218172,218173,218178,218181,218185,218196,218205,218206,218212,218215,218216,218219,218220,218226,218227,218228,218233,218234,218242,218246,218271,218280,218282,218284,218287,218288,218289,218291,218294,218296,218301,218304,218307,218313,218316,218318,218323,218328,218333,218334,218340,218341,218342,218344,218348,218350,218354,218359,218364,218370,218374,218377,218379,218380,218382,218384,218385,218390,218391,218392,218395,218400,218401,218403,218404,218412,218433,218436,218437,218443,218445,218458,218464,218466,218467,218468,218471,218472,218473,218476,218477,218481,218484,218488,218495,218497,218501,218502,218504,218506,218508,218513,218520,218525,218528,218533,218544,218546,218550,218556,218560,218565,218579,218586,218598,218605,218607,218608,218611,218615,218616,218623,218624,218630,218634,218635,218637,218642,218647,218648,218649,218654,218659,218662,218666,218667,218669,218674,218678,218685,218686,218692,218704,218709,218710,218711,218720,218726,218729,218731,218736,218737,218745,218753,218756,218766,218768,218769,218782,218787,218801,218813,218829,218838,218842,218856,218860,218862,218864,218873,218878,218882,218888,218892,218900,218909,218921,218933,218940,218941,218942,218951,218956,218958,218967,218970,218972,218976,218980,218983,218987,218995,219003,219004,219005,219008,219012,219020,219022,219030,219040,219042,219047,219050,219052,219053,219056,219057,219058,219064,219066,219078,219084,219088,219091,219101,219110,219113,219115,219116,219122,219153,219165,219171,219172,219175,219178,219187,219188,219190,219192,219198,219210,219212,219213,219225,219230,219232,219236,219237,219238,219250,219258,219263,219266,219269,219275,219284,219300,219308,219317,219319,219334,219340,219357,219366,219367,219368,219373,219375,219381,219382,219390,219398,219399,219406,219413,219419,219423,219438,219451,219452,219459,219494,219496,219500,219501,219502,219505,219512,219514,219521,219522,219526,219528,219532,219541,219545,219549,219553,219554,219556,219561,219563,219593,219594,219597,219599,219603,219615,219620,219634,219656,219687,219689,219695,219708,219716,219720,219727,219728,219740,219755,219764,219767,219773,219779,219787,219788,219793,219821,219822,219833,219836,219848,219862,219863,219864,219872,219899,219908,219945,219952,219961,219967,219971,219973,219980,219984,219988,219993,220016,220022,220038,220051,220058,220072,220073,220081,220082,220084,220088,220102,220106,220107,220108,220116,220118,220129,220130,220144,220161,220169,220173,220180,220190,220194,220204,220217,220218,220219,220221,220224,220236,220241,220244,220245,220254,220262,220269,220271,220273,220279,220281,220284,220291,220292,220294,220301,220303,220306,220308,220310,220314,220321,220325,220337,220341,220348,220351,220353,220356,220362,220372,220376,220395,220396,220407,220408,220420,220424,220425,220436,220442,220447,220469,220477,220489,220496,220503,220521,220523,220526,220531,220550,220555,220559,220561,220584,220585,220605,220607,220615,220622,220632,220646,220666,220670,220678,220692,220697,220699,220701,220718,220725,220735,220740,220746,220747,220758,220760,220770,220776,220778,220792,220804,220818,220831,220835,220844,220847,220850,220855,220857,220869,220874,220885,220893,220894,220900,220901,220902,220907,220909,220910,220911,220912,220914,220916,220922,220923,220928,220931,220932,220934,220942,220948,220951,220952,220957,220965,220970,220982,220983,220984,220988,220997,221005,221019,221020,221021,221026,221038,221040,221062,221070,221072,221083,221089,221110,221115,221130,221133,221134,221136,221143,221149,221157,221173,221213,221240,221265,221272,221281,221290,221309,221311,221366,221392,221394,221460,221487,221489,221497,221513,221517,221521,221527,221559,221571,221594,221615,221621,221623,221636,221648,221665,221676,221682,221684,221732,221740,221798,221816,221827,221837,221852,221864,221901,221912,221916,221931,221933,221936,221964,221972,221987,221992,221997,222043,222057,222086,222090,222095,222101,222112,222115,222129,222134,222156,222173,222177,222179,222204,222218,222224,222271,222309,222322,222325,222365,222377,222395,222407,222439,222451,222452,222456,222462,222467,222474,222479,222485,222492,222496,222500,222501,222505,222506,222510,222534,222537,222539,222544,222548,222560,222572,222593,222597,222616,222643,222644,222646,222717,222754,222766,222784,222790,222794,222848,222872,222874,222880,222888,222941,222974,222979,222993,223028,223065,223078,223101,223113,223124,223138,223183,223194,223283,223317,223330,223412,223416,223442,223449,223483,223503,223534,223537,223558,223579,223622,223668,223697,223720,223730,223741,223762,223763,223765,223767,223784,223802,223828,223855,223902,223930,223933,223936,223953,223967,223992,224004,224022,224023,224029,224032,224044,224045,224059,224062,224075,224092,224095,224151,224170,224174,224185,224187,224202,224207,224228,224250,224262,224296,224360,224379,224400,224403,224463,224468,224530,224544,224547,224551,224556,224593,224618,224623,224681,224710,224798,224801,224802,224810,224824,224879,224901,224924,225020,225059,225080,225083,225162,225186,225216,225268,225271,225304,225326,225327,225343,225344,225400,225412,225427,225431,225464,225467,225491,225514,225569,225588,225604,225605,225612,225634,225648,225672,225698,225702,225714,225816,225937,225944,225945,225949,225977,225990,226048,226074,226088,226107,226127,226147,226151,226175,226186,226215,226232,226233,226258,226315,226338,226480,226555,226561,226821,226825,226925,227014,227018,227107,227455,227471,227543,227610,227684,227687,227757,227768,227785,227805,227856,227857,227867,227889,227917,227928,227968,228001,228006,228031,228040,228049,228070,228088,228146,228161,228167,228188,228194,228220,228314,228403,228455,228488,228548,228637,228654,228666,228699,228703,228762,228768,228778,228792,228802,228829,228843,228855,228867,228891,228904,228911,228947,228954,228959,229041,229042,229061,229065,229120,229138,229157,229176,229187,229194,229199,229209,229230,229311,229347,229352,229366,229385,229396,229543,229550,229579,229636,229639,229648,229730,229759,229825,229828,229850,229870,229928,229946,230371,230402,230434,230459,230506,230572,230581,230588,230618,230663,230666,230671,230708,230710,230754,230800,230879,230912,230985,230986,231023,231046,231055,231058,231088,231134,231317,231361,231389,231394,231435,231562,231568,231571,231602,231627,231640,231692,231850,231855,231923,231936,232098,232166,232272,232381,232405,232444,232445,232457,232487,232534,232577,232578,232609,232695,232768,232794,232832,232929,232941,232966,233092,233129,233153,233173,233224,233236,233252,233260,233294,233321,233331,233426,233523,233613,233652,233683,233703,233719,233732,233739,233859,233881,233888,233956,233989,234011,234015,234044,234080,234135,234144,234152,234174,234195,234277,234282,234295,234306,234336,234344,234383,234414,234534,234559,234560,234638,234689,234748,234757,234769,234777,234782,234818,234823,234847,234878,234948,235026,235043,235072,235198,235280,235347,235353,235368,235422,235432,235500,235554,235584,235628,235633,235683,235720,235722,235723,235765,235825,235856,235864,235886,235916,235923,235949,235958,235961,236047,236105,236164,236166,236191,236198,236235,236269,236272,236276,236277,236297,236319,236399,236411,236433,236458,236509,236513,236517,236525,236532,236635,236646,236751,236884,236897,236922,236935,236971,237001,237003,237025,237054,237099,237158,237188,237219,237229,237259,237281,237318,237342,237344,237348,237378,237397,237491,237512,237535,237550,237598,237845,237898,237957,238050,238087,238113,238232,238303,238439,238456,238482,238503,238600,238602,238772,238801,239241,239265,239268,239288,239470,239667,239675,239683,239714,239744,239761,239922,240031,240153,240176,240650,240797,240804,240975,241119,241202,241631,241889,241998,242009,242144,242376,242393,242406,242457,242513,242588,242897,242938,242939,242952,243142,243147,243217,243302,243314,243468,243612,243693,243758,243813,243890,243975,244045,244070,244224,244287,244298,244529,244572,244582,244585,244965,244969,245126,245139,245152,245154,245211,245421,245758,245849,245947,246024,246030,246031,246072,246100,246171,246501,247028,247177,247288,247483,247655,247777,247918,247987,248004,248028,248250,248280,248292,248335,248485,248813,248818,248850,249220,249557,249574,249805,249815,249905,250043,250231,250554,250971,251027,251260,251271,251350,251356,251417,251490,251706,251724,252107,252147,252426,252530,252586,252611,252704,252721,252812,252987,253055,253084,253257,253290,253302,253334,253403,253431,253713,253836,253956,254061,254232,254264,254302,254312,254322,254365,254372,254405,254410,254415,254418,254430,254447,254456,254462,254473,254495,254496,254500,254504,254506,254519,254521,254525,254526,254554,254558,254592,254595,254605,254620,254623,254626,254631,254633,254664,254711,254720,254725,254749,254752,254758,254760,254791,254797,254851,254861,254868,254903,254920,255208,255219,255235,255239,255266,255274,255286,255316,255340,255341,255359,255369,255384,255394,255416,255433,255449,255464,255470,255471,255478,255480,255484,255497,255501,255516,255526,255543,255546,255550,255557,255561,255565,255576,255600,255603,255613,255617,255618,255640,255649,255658,255700,255701,255715,255716,255728,255748,255749,255756,255757,255787,255832,255847,255849,255863,255872,255874,255875,255887,255899,255908,255924,255930,255936,255952,255953,255956,255971,255979,255992,255997,256013,256017,256032,256038,256041,256072,256090,256092,256093,256099,256105,256108,256115,256132,256162,256194,256199,256209,256210,256214,256217,256239,256245,256256,256263,256279,256288,256294,256301,256308,256323,256337,256339,256341,256345,256346,256350,256351,256356,256357,256365,256369,256371,256372,256398,256399,256407,256412,256447,256450,256464,256479,256488,256491,256492,256493,256498,256501,256504,256507,256508,256511,256513,256517,256519,256529,256536,256537,256539,256540,256638,256640,256641,256657,256671,256672,256673,256680,256681,256682,256683,256685,256693,256694,256695,256703,256708,256716,256723,256725,256748,256749,256752,256753,256757,256760,256770,256778,256780,256789,256800,256806,256813,256822,256825,256831,256832,256838,256839,256841,256848,256857,256878,256879,256881,256887,256892,256893,256903,256915,256920,256924,256933,256953,256955,256956,256959,256964,256965,256971,256977,256982,256983,256984,256989,256999,257002,257011,257013,257014,257017,257018,257019,257025,257027,257032,257034,257035,257038,257042,257044,257047,257051,257053,257054,257055,257064,257068,257070,257075,257087,257088,257089,257094,257097,257099,257100,257103,257105,257117,257123,257125,257126,257129,257146,257147,257149,257159,257167,257169,257182,257194,257198,257199,257200,257205,257207,257212,257222,257239,257242,257285,257296,257326,257398,257399,257401,257407,257412,257414,257424,257425,257460,257462,257464,257468,257472,257475,257484,257485,257512,257515,257744,257746,257767,257768,257772,257777,257778,257779,257782,257783,257784,257792,257793,257794,257796,257798,257799,257800,257805,257823,257831,257834,257838,257839,257843,257850,257853,257860,257863,257864,257865,257867,257873,257880,257881,257882,257883,257885,257894,257899,257900,257904,257915,257920,257922,257924,257926,257932,257936,257940,258009,258020,258048,258061,258064,258087);
    
    $attrlist = OrderHistoryAttribute::find_by_sql("SELECT * FROM `order_history_attribute` where orderhistory_id IN (".implode(",",$idlist).") ORDER BY id ASC");
    $attrmap = array();
    foreach($attrlist as $attr)
    {
      if(!isset($attrmap[$attr->orderhistory_id])) $attrmap[$attr->orderhistory_id] = array();
      $attrmap[$attr->orderhistory_id][$attr->attribute_name] = $attr->attribute_value;
    }                  
    
     header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=historyreport.csv');
        $output = fopen('php://output', 'w');
    
    echo "Navn;Efternavn;Email;Vejnavn; Husnummer;Postnummer;By;Land;Telefon;Gavekort;Produkt;Model;Gave nr.;Dato\r\n";
    
    foreach($idlist as $id)
    {
    
   
      $order = OrderHistory::find($id);
      $data = $attrmap[$id];
      
      $models = PresentModel::find_by_sql("SELECT * FROM present_model WHERE model_id = ".intval($order->present_model_id));
      $model = $models[0];
                  
      // Find attributes
      
      $row = array(
        $data["Navn"],
        $data["Efternavn"],
        $data["Email"],
        $data["Vejnavn"],
        $data["husnummer"],
        $data["Postnummer"],                                      
        $data["By"],
        $data["Land"],
        $data["Telefonnummer"],
        $order->user_username,
        $model->model_name,
        $model->model_no,
        $model->fullalias,
        $order->order_timestamp->format("d-m-Y H:i:s")
      );
      
      echo utf8_decode(implode(";",$row))."\r\n";
                                           
                                     
    }
    
  }                                             

  /** PULL REMINDER  MAILS **/

  public function remindermaillist()
  {
  
  
    $shopinput = isset($_GET["shops"]) ? trimgf($_GET["shops"]) : "";
    $deadline = isset($_GET["deadline"]) ? trimgf($_GET["deadline"]) : "";
    $token = isset($_GET["token"]) ? trimgf($_GET["token"]) : "";
    
    if($token != "sdfkfk3DD3kdsj3xSS34Df") exit();
    
    $shoplist = array();
    $inputsplit = explode(",",$shopinput);
    if(count($inputsplit) > 0)
    {
      foreach($inputsplit as $input)
      {
        if(intval($input) > 0)
        {
          $shoplist[] = intval($input);
        }
      }
    }
    
    
    if(count($shoplist) == 0) { echo "Ingen shops angivet.."; return; }
    
    
    $shops = $shoplist;
    
    $deadlines = array("2018-11-04","2018-11-11","2018-11-18","2018-11-25","2018-12-02","2018-12-31");
    if(in_array($deadline,$deadlines) == false)
    {
      echo "Invalid deadline"; return;
    }  
  
    
    $removeDuplicateMails = true;
  
    header('Content-Type: application/csv');
    header('Content-Disposition: attachement; filename="reminderlist-'.$deadline.'.csv"');
    

    $sumCompany = 0;
    $sumUsers = 0;
    $sumSelected = 0;
    $sumNotSelected = 0;
    $usedEmails = array();
    
    // Set tokens on missing
    $this->settokens();
    
    // Define headers
    $header = array("CompanyID","Virksomhed","CVR","Faktura adresse","Faktura postnr","Faktura by", "Levering virksomhed", "Levering adresse","Levering postnr", "Levering by","Kontaktperson","Telefon","E-mail","Antal kort","Antal valgt","Antal ikke valgt","Token");
    echo implode(';',$header)."\n";
    
    // Find companies
    $companylist = ShopUser::find_by_sql("SELECT company_id, count(id) as users FROM shop_user WHERE shop_id IN (".implode(",",$shops).") && is_demo = 0 && expire_date = '".$deadline."' && is_giftcertificate = 1 && blocked = 0 && company_id NOT IN (12441,12456,12468,12480,12500,12501,12505,12510,12512,12514,12515,12519,12520,12522,12523,12529,12530,12531,12532,12533,12535,12536,12537,12538,12539,12540,12541,12542,12543,12544,12546,12547,12549,12550,12551,12552,12553,12554,12555,12556,12557,12558,12559,12561,12562,12563,12564,12565,12566,12568,12569,12571,12572,12573,12574,12575,12576,12577,12578,12579,12580,12581,12583,12587,12588,12618,12689,12702,12994,13014,13037,13095,13098,13441,13521,13554,13555,13700,13088,18581,19308,19309,19310,12957) GROUP BY company_id");
    
    // Go through companies
    foreach($companylist as $companycount)
    {
      
      // Load company and orders
      $c = Company::find($companycount->company_id);
      $orders = ShopUser::find_by_sql("SELECT * FROM shop_user WHERE id NOT IN (SELECT shopuser_id FROM `order`) && company_id = ".$c->id." && shop_id IN (".implode(",",$shops).") && is_demo = 0 && expire_date = '".$deadline."' && is_giftcertificate = 1 && blocked = 0");
      
      // Get cound
      $totalUsers = $companycount->users;
      $totalNotSelected = countgf($orders);
      $totalOrders = $totalUsers - $totalNotSelected;
      
      // Check negative number
      if($totalOrders < 0) { echo "COUNT ERROR IN  ".$c->id; exit(); }
      
      // Update total sum
      $sumCompany++;
      $sumUsers += $totalUsers;
      $sumSelected += $totalOrders;
      $sumNotSelected += $totalNotSelected;
      
      // Find mail and check
      $mail = mb_strtolower(trimgf($c->contact_email));
      $isUsed = in_array($mail,$usedEmails);
      if(!$isUsed) $usedEmails[] = $mail;
      
      if($removeDuplicateMails == false || $isUsed == false)
      {
      
      // Add data to file
      $data = array(
        $c->id,
        $c->name,$c->cvr,$c->bill_to_address,$c->bill_to_postal_code,$c->bill_to_city,$c->ship_to_company,$c->ship_to_address,$c->ship_to_postal_code,$c->ship_to_city,
        $c->contact_name,$c->contact_phone,$c->contact_email,
        $totalUsers,
        $totalOrders,
        $totalNotSelected,
        $c->token
      );
      
      // Fix encoding and add
      foreach($data as $key => $val) $data[$key] = utf8_decode($val);
      echo implode(';',$data)."\n";
      
      }
      else
      {
        //echo "REMOVED DUPLICATE: ".$c->id." / ".$c->contact_email." / ".$c->token; 
      }
    
    }
    
    
  
    $sum = array(
    "TOTAL SUM","ANTAL KUNDER: $sumCompany","ANTAL KORT: $sumUsers","ANTAL VALGT: $sumSelected","ANTAL IKKE VALGT: $sumNotSelected"
    );
     
     
      echo implode(';',$sum)."\n";

  }
  

  public function cancellist()
  {
                       return;
      echo "<table style=\"width: 100%;\">";
  
      $kortTilSletning = 0;
      $mailAdresser = array();
      $companyBlocks = array();
  
      $companyorderlist = CompanyOrder::find_by_sql("SELECT * FROM company_order WHERE is_cancelled > 0");
      foreach($companyorderlist as $co)
      {
      
          $company = Company::find($co->company_id);
          
          $shopuserlist = ShopUser::find_by_sql("SELECT * FROM shop_user WHERE company_id = ".$company->id." && is_demo = 0 && is_giftcertificate = 1 && blocked = 0 && username >= ".$co->certificate_no_begin." && username <= ".$co->certificate_no_end);
          
          if(count($shopuserlist) > 0) 
          {
          echo "<tr>
              <td valign=top style='padding-top: 5px; padding-bottom: 10px; border-bottom: 1px solid #555555;'><span style=\"color: #888888;\">ID</span><br>".$company->id."</td>
              <td valign=top style='padding-top: 5px; padding-bottom: 10px; border-bottom: 1px solid #555555;'><span style=\"color: #888888;\">CVR</span><br>".$company->cvr."</td>
              <td valign=top style='padding-top: 5px; padding-bottom: 10px; border-bottom: 1px solid #555555;'><span style=\"color: #888888;\">EAN</span><br>".$company->ean."</td>
              <td valign=top style='padding-top: 5px; padding-bottom: 10px; border-bottom: 1px solid #555555;'>
              <span style=\"color: #888888;\">Faktura adresse</span><br>
              ".$company->name."<br>
              ".$company->bill_to_address."<br>
              ".$company->bill_to_address_2."<br>
              ".$company->bill_to_postal_code."<br>
              ".$company->bill_to_city."<br>
              ".$company->bill_to_country."
            </td>
            <td valign=top style='padding-top: 5px; padding-bottom: 10px; border-bottom: 1px solid #555555;'>
              <span style=\"color: #888888;\">Leveringsadresse</span><br>
              ".$company->ship_to_company."<br>
              ".$company->ship_to_address."<br>
              ".$company->ship_to_address_2."<br>
              ".$company->ship_to_postal_code."<br>
              ".$company->ship_to_city."<br>
              ".$company->ship_to_country."
            </td>
            <td valign=top style='padding-top: 5px; padding-bottom: 10px; border-bottom: 1px solid #555555; border-right: 1px solid #BBBBBB;'>
              <span style=\"color: #888888;\">Kontakt</span><br>
              ".$company->contact_name."<br>
              ".$company->contact_phone."<br>
              ".$company->contact_email."
            </td><td valign=top style='padding-top: 5px; padding-bottom: 10px; border-bottom: 1px solid #555555; border-right: 1px solid #BBBBBB;'>
              Aktive kort ".countgf($shopuserlist)."
            </td></tr>";
            
            if(!in_array($company->contact_email,$mailAdresser)) $mailAdresser[] = $company->contact_email;
            if(!in_array($company->id,$companyBlocks)) $companyBlocks[] = $company->id;
            $kortTilSletning += countgf($shopuserlist);
         }         
          
      }
      echo "</table>";
      
      echo "KORT TIL SLETNING: ".$kortTilSletning;
      echo "<pre>".print_r($mailAdresser,true)."</pre>";
      echo "<pre>".print_r($companyBlocks,true)."</pre>";
      
      echo implode("','",$mailAdresser)."<br><br>";
      echo implode(",",$companyBlocks);
  }


  /** COPY PRESENTS TO ANOTHER SHOP */
  
   
   
    public function testauth()
    {
    
      echo "test..<br>";
      var_dump($_SESSION);
      echo router::$username;
    
    }
    
   

    public function sessiontest()
    {
    
      if (session_status() == PHP_SESSION_NONE) session_start();
      echo "TEST SESSION!";
      
      SystemSession::updateSession();
      System::connection()->commit();
           
    
    }
    
   
    /*
     * HELPERS
     */

    const CONTROLLER = "sch";
    private function getUrl($method="") { return "../gavefabrikken_backend/index.php?rt=".SchController::CONTROLLER."/".$method; }
    private $error = "";

    /**
     * PULL DELIVERY REPORT
     */
     
     public function deliveryreport()
     {
                  
        $lang = isset($_GET["lang"]) ? $_GET["lang"] : "";
        if($lang == "da") $shops = array(52,575,54,55,56,53,287,290,310,247,248);
        else if($lang == "no") $shops = array(272,57,58,59,574);
        else { echo "Select a language.."; exit(); }                  
                  

        $token = isset($_GET["token"]) ? $_GET["token"] : "";                  
        if($token != "2AALH78a7UmM2rSdV7y2HvRFpTX7d44rmGYtKKGm") { echo "Invalid token"; exit(); }          
                  
        //$companyListSQL = "SELECT * FROM company WHERE cvr IN ('979148046','918201548')";                                                                                                                                                                                                               
        if(isset($_GET["comcvf"]) && trimgf($_GET["comcvf"]) != "") $companyListSQL = "SELECT * FROM company WHERE cvr = '".intval($_GET["comcvf"])."'";
        else $companyListSQL = "SELECT * FROM company WHERE id IN (SELECT company_id FROM company_order WHERE shop_id IN (".implode(",",$shops).") && is_cancelled = 0 GROUP BY company_id HAVING COUNT(id) > 1)";
        $companyList = Company::find_by_sql($companyListSQL);
        
        echo '<html><head><meta charset="UTF-8"></head><body>';
        
        
        echo "<table style=\"width: 100%; border-top: 1px solid #555555; font-size: 14px;\">";
        echo "<tr>
          <td colspan=6 style=\"padding-bottom: 20px; font-size: 20px; font-weight: bold;border-bottom: 1px solid #555555; padding-top: 10px;\">Aktuel information og leveringsadresse</td>
          <td colspan=1 style=\"padding-bottom: 20px; font-size: 20px; font-weight: bold;border-bottom: 1px solid #555555; padding-top: 10px;\">Kundens ordre</td>
        </tr>";
        
        foreach($companyList as $company)
        {
        
            echo "<tr>
              <td valign=top style='padding-top: 5px; padding-bottom: 10px; border-bottom: 1px solid #555555;'><span style=\"color: #888888;\">ID</span><br>".$company->id."</td>
              <td valign=top style='padding-top: 5px; padding-bottom: 10px; border-bottom: 1px solid #555555;'><span style=\"color: #888888;\">CVR</span><br>".$company->cvr."</td>
              <td valign=top style='padding-top: 5px; padding-bottom: 10px; border-bottom: 1px solid #555555;'><span style=\"color: #888888;\">EAN</span><br>".$company->ean."</td>
              <td valign=top style='padding-top: 5px; padding-bottom: 10px; border-bottom: 1px solid #555555;'>
              <span style=\"color: #888888;\">Faktura adresse</span><br>
              ".$company->name."<br>
              ".$company->bill_to_address."<br>
              ".$company->bill_to_address_2."<br>
              ".$company->bill_to_postal_code."<br>
              ".$company->bill_to_city."<br>
              ".$company->bill_to_country."
            </td>
            <td valign=top style='padding-top: 5px; padding-bottom: 10px; border-bottom: 1px solid #555555;'>
              <span style=\"color: #888888;\">Leveringsadresse</span><br>
              ".$company->ship_to_company."<br>
              ".$company->ship_to_address."<br>
              ".$company->ship_to_address_2."<br>
              ".$company->ship_to_postal_code."<br>
              ".$company->ship_to_city."<br>
              ".$company->ship_to_country."
            </td>
            <td valign=top style='padding-top: 5px; padding-bottom: 10px; border-bottom: 1px solid #555555; border-right: 1px solid #BBBBBB;'>
              <span style=\"color: #888888;\">Kontakt</span><br>
              ".$company->contact_name."<br>
              ".$company->contact_phone."<br>
              ".$company->contact_email."
            </td>";
        
            $companyOrderListSQL = "SELECT * FROM company_order WHERE shop_id IN (".implode(",",$shops).") && company_id = ".$company->id." ORDER BY id ASC";
            $companyOrderList = CompanyOrder::find_by_sql($companyOrderListSQL);
            
            echo "<td  valign=top style='width: 65%; padding-top: 5px; padding-bottom: 10px; border-bottom: 1px solid #555555;'>
              <table style=\"width: 100%; font-size: 14px;\">";
              
              echo "<tr>
                <td><span style=\"color: #888888;\">Ordre nr</span><br></td>
                <td><span style=\"color: #888888;\">Virksomhed</span><br></td>
                <td><span style=\"color: #888888;\">Sælger</span><br></td>
                <td><span style=\"color: #888888;\">Shop</span><br></td>
                <td><span style=\"color: #888888;\">Antal</span><br></td>
                <td><span style=\"color: #888888;\">Aktive</span><br></td>
                <td><span style=\"color: #888888;\">Deadline</span><br></td>
                <td><span style=\"color: #888888;\">Type</span><br></td>
                <td><span style=\"color: #888888;\">Indpakning</span><br></td>
                <td><span style=\"color: #888888;\">Leverings virksomhed</span><br></td>
                <td><span style=\"color: #888888;\">Leveringsadresse</span><br></td>
                <td><span style=\"color: #888888;\">Leverings postnr</span><br></td>
                <td><span style=\"color: #888888;\">Leverings by</span><br></td>
                <td><span style=\"color: #888888;\">Kontaktperson</span><br></td>
                <td><span style=\"color: #888888;\">Kontakt telefon</span><br></td>
                <td><span style=\"color: #888888;\">Kontakt mail</span><br></td>
              </tr>";
                   
              $hasGiftwrap = array();
              foreach($companyOrderList as $companyOrder) 
              {
                  $key = $companyOrder->shop_id."-".$companyOrder->expire_date->format("d-m-Y");
                  if(!isset($hasGiftwrap[$key])) $hasGiftwrap[$key] = false;
                  if($companyOrder->giftwrap == 1) $hasGiftwrap[$key] = true;
              }
                   
              foreach($companyOrderList as $companyOrder)
              {
                    $key = $companyOrder->shop_id."-".$companyOrder->expire_date->format("d-m-Y");
                    
                $giftCertificatesSQL = "SELECT * FROM shop_user WHERE company_id = ".$company->id." && is_giftcertificate = 1 && shop_id = ".$companyOrder->shop_id." && blocked = 0 && username >= ".$companyOrder->certificate_no_begin." && username <= ".$companyOrder->certificate_no_end."" ;
                $giftCertificatesList = GiftCertificate::find_by_sql($giftCertificatesSQL);
              
                echo "<tr ".($companyOrder->is_cancelled == 1 ? "style=\"opacity: 0.4;\"" : "").">
                  <td>".$companyOrder->order_no."</td>
                  <td ".(trimgf(mb_strtolower($companyOrder->company_name)) != trimgf(mb_strtolower($company->name)) ? "style='background: yellow;'" : "").">".$companyOrder->company_name."</td>
                  <td>".$companyOrder->salesperson."</td>
                  <td>".$companyOrder->shop_name."</td>
                  <td ".(intval($companyOrder->quantity) != countgf($giftCertificatesList) ? "style='background: red;'" : "").">".$companyOrder->quantity."</td>
                  <td ".(intval($companyOrder->quantity) != countgf($giftCertificatesList) ? "style='background: red;'" : "").">".countgf($giftCertificatesList)."</td>
                  <td>".$companyOrder->expire_date->format("d-m-Y")."</td>
                  <td>".($companyOrder->is_email == 1 ? "email" : "fysisk")."</td>
                  <td>".($hasGiftwrap[$key] ? "JA" : "NEJ")."</td>
                  <td ".(trimgf(mb_strtolower($companyOrder->ship_to_company)) != trimgf(mb_strtolower($company->ship_to_company)) ? "style='background: yellow;'" : "").">".$companyOrder->ship_to_company."</td>
                  <td ".(trimgf(mb_strtolower($companyOrder->ship_to_address)) != trimgf(mb_strtolower($company->ship_to_address)) ? "style='background: red;'" : "").">".$companyOrder->ship_to_address."</td>
                  <td ".(trimgf(mb_strtolower($companyOrder->ship_to_postal_code)) != trimgf(mb_strtolower($company->ship_to_postal_code)) ? "style='background: red;'" : "").">".$companyOrder->ship_to_postal_code."</td>
                  <td ".(trimgf(mb_strtolower($companyOrder->ship_to_city)) != trimgf(mb_strtolower($company->ship_to_city)) ? "style='background: yellow;'" : "").">".$companyOrder->ship_to_city."</td>
                  <td ".(trimgf(mb_strtolower($companyOrder->contact_name)) != trimgf(mb_strtolower($company->contact_name)) ? "style='background: yellow;'" : "").">".$companyOrder->contact_name."</td>
                  <td ".(trimgf(mb_strtolower($companyOrder->contact_phone)) != trimgf(mb_strtolower($company->contact_phone)) ? "style='background: yellow;'" : "").">".$companyOrder->contact_phone."</td>
                  <td ".(trimgf(mb_strtolower($companyOrder->contact_email)) != trimgf(mb_strtolower($company->contact_email)) ? "style='background: yellow;'" : "").">".$companyOrder->contact_email."</td>
                </tr>";
              }
                    
              echo "</table>
            </td>";
            
            echo "</tr>";
          
        }
        echo "</table>";
     
     echo "</body></html>";
     
     }


    /**
     * SOAP TEST
     */

     private $lastErrorType=0;
     private $lastErrorMessage="";
     private $lastReturnValue = "";
     
     public function getReturnValue() { return $this->lastReturnValue; }
     public function getLastErrorType() { return $this->lastErrorType; }
     public function getLastErrorMessage() { return $this->lastErrorMessage; }
     private function setErrorMessage($errorType,$errorMessage) {
        $this->lastErrorType = $errorType;
        $this->lastErrorMessage = $errorMessage;
        return false;
     }

     private function getOrderData(CompanyOrder $co)
     {
        
        // Check company order
        if($co == null || !isset($co->id) || intval($co->id) <= 0) 
          return $this->setErrorMessage(1,"Could not find companyorder"); 
        
        // Find and check company
        $c = Company::find($co->company_id);
        if($c == null || !isset($c->id) || intval($c->id) <= 0) 
          return $this->setErrorMessage(1,"Could not load company with id: ".$co->company_id." on companyorder: ".$companyorderid);
      
      // Prepare params
      $orderData = array(
        "id" => $co->id,
        "order_no" => $co->order_no,
        "company_id" => $co->company_id,
        "company_name" => $co->company_name,
        "shop_id" => $co->shop_id,
        "shop_name" => $co->shop_name,
        "salesperson" => $co->salesperson,
        "salenote" => $co->salenote,
        "quantity" => $co->quantity,
        "expire_date" => $co->expire_date->format('Y-m-d'),
        "certificate_no_begin" => $co->certificate_no_begin,
        "certificate_no_end" => $co->certificate_no_end,
        "certificate_value" => $co->certificate_value,
        "ship_to_company" => $co->ship_to_company,
        "ship_to_address" => $co->ship_to_address,
        "ship_to_address2" => $co->ship_to_address_2,
        "ship_to_postal_code" => $co->ship_to_postal_code,
        "ship_to_city" => $co->ship_to_city,
        "contact_name" => $co->contact_name,
        "contact_email" => $co->contact_email,
        "contact_phone" => $co->contact_phone,
        "spdeal" => $co->spdeal,
        "spdealtxt" => $co->spdealtxt,
        "cvr" => $co->cvr,
        "ean" => $co->ean,
        "phone" => $c->phone,
        "is_appendix_order" => $co->is_appendix_order,
        "giftwrap" => $co->giftwrap,
        "so_nr" => $c->so_no,
        "bill_to_address" => $c->bill_to_address,
        "bill_to_address_2" => $c->bill_to_address_2,
        "bill_to_postal_code" => $c->bill_to_postal_code,
        "bill_to_city" => $c->bill_to_city,
        "bill_to_country" => $c->bill_to_country,
        "bill_to_email" => $c->contact_email,
        "created_datetime" => $co->created_datetime->format('Y-m-d H:i:s'),
        "modified_datetime" => $co->modified_datetime->format('Y-m-d H:i:s'),
        "ordernote" => "",
        "earlyorder" => ""
      );
      
      foreach($orderData as $key => $val)
      {
        if(is_string($val))
        {
          $orderData[$key] = utf8_encode($val);
        } 
      }
      
      return $orderData;
      
     }

     private function syncCompanyOrder($companyorder)
     {
     
        // Find order data
        $orderData = $this->getOrderData($companyorder);
        if($orderData === false) return false;
                           
        // Prepare request params
        $requestData = array("request" => $orderData);
        $requestJSON = json_encode($requestData);
        $params = array("request"=> $requestJSON);
        
        if($requestJSON == null || $requestJSON == "") return $this->setErrorMessage(2,"Could not encode companyorder data to json");
    
        $wsdlUrl = "http://kgavenav02:7067/GFTESTWIN/WS/Gavefabrikken/Codeunit/GavekortWS";                                 
        

        /************** SOAP CLIENT APPROACH ******************/
        
        
        $options = array(
          'login' => "gavekortws",
          'password' => "Gavekort123",
          'trace' => true
        );

        $client = new SoapClient($wsdlUrl,$options);
      
        try
        {
          $response = @$client->__soapCall("CreateOrder", array(array("request"=> $requestJSON)));
        }
        catch(Exception $e)
        {
          return $this->setErrorMessage(3,"WS Exception: ".$e->getMessage());
        }        
      
        // Get request body
        $requestBody = $client->__getLastRequest();
        echo str_replace("<","\r\n<",$requestBody);
      
        // Get response text
        if($response == null || !property_exists($response,"return_value") || trimgf($response->return_value) == "")
        {
          return $this->setErrorMessage(4,"WS Response unknown: ".print_r($response,true));
        } 
      
        // Set return value
        $this->lastReturnValue = trimgf($response->return_value);
      
        return true;
        
     }
     
     private function syncCompanyOrderID($companyorderid)
     {
        $companyorder = CompanyOrder::find(intval($companyorderid));
        return $this->syncCompanyOrder($companyorder); 
     }

     public function soaptest()
     {
     
      echo "SOAP TEST NOW WITH NUSOAP CLIENT\r\n";
      
      $result = $this->syncCompanyOrderID(15080);
      if($result) echo "ORDER HAS BEEN SYNCED - Return value is: ".$this->getReturnValue();
      else echo "ORDER COULD NOT BE SYNCED: [".$this->getLastErrorType()."] ".$this->getLastErrorMessage();
      
      return;
                   /*         
      
      echo "\r\nCLIENT REQUEST\r\n";
      echo str_replace("<","\r\n<",$client->request);
                                    
      echo "\r\nCLIENT RESPONSE\r\n";                 
      var_dump($response);

      return;
      
      
      $options = array(
         'login' => "gavekortws",
         'password' => "Gavekort123",
         'trace' => true
    );

      $client = new SoapClient("http://kgavenav02:7067/GFTESTWIN/WS/Gavefabrikken/Codeunit/GavekortWS",$options);
                   */
                   
                   /*
      $orderdata = array(
        "OrderID" => "BS0001",
        "FakturaAdresse" => "Fakt. adr. 1"
      );
      
      $payload = new soappayload("test1","test2");
      
      $json = json_encode($orderdata);
    
      
      
      echo "\r\nBefore function call\r\n";
                     */
                     $orderdata = array(
        "OrderID" => "BS0001",
        "FakturaAdresse" => "Fakt. adr. 1"
      );
                     $json = json_encode($orderdata);
                     $params = array("request" => "{\"root\":" + $json + "}");
     
      // Create data
      $orderData = array(
        "OrderID" => "<BS0001>",
        "FakturaAdresse" => "Fakt. adr. 1"
      );
     
     $requestData = array("request" => $orderData);
     $requestJSON = json_encode($requestData);
     
      /* Invoke webservice method with your parameters, in this case: Function1 */
      try
      {
        $response = @$client->__soapCall("CreateOrder", array(array("request"=> utf8_decode("<![CDATA[".$requestJSON."]]>"))));
        echo "\r\nRESPONSE\r\n";
        var_dump($response);
      }
      catch(Exception $e)
      {
        echo "SOAP EXCEPTION";
        var_dump($e);
      }       
      
      echo "\r\nAfter function call\r\n";
      echo "REQUEST:\n" . $client->__getLastRequest() . "\n";

      
     }


    /******************************
     * FIX GAVE MODEL SCRIPT
     ******************************/
     
     
     
     public function checkwrap()
     {
     
        $companyOrders = CompanyOrder::find_by_sql("SELECT * FROM company_order WHERE is_cancelled = 0 && giftwrap = 1");
        $companyIDList = array();
        $companyGiftShop = array();
        $companyGiftShops = array();
        foreach($companyOrders as $order)
        {
            if(!in_array($order->company_id,$companyIDList)) $companyIDList[] = $order->company_id;
            $companyGiftShop[$order->company_id."-".$order->shop_id] = true;
            if(!isset($companyGiftShops[$order->company_id])) $companyGiftShops[$order->company_id] = array();
            $companyGiftShops[$order->company_id][] = $order->shop_id;
        }
        
       
        $companyOrders = CompanyOrder::find_by_sql("SELECT * FROM company_order WHERE is_cancelled = 0 && company_id IN (".implode(",",$companyIDList).") && giftwrap = 0");
        foreach($companyOrders as $order)
        {
          if(!isset($companyGiftShop[$order->company_id."-".$order->shop_id]))
          {
            echo "<br>[".$order->company_id."] ".$order->cvr." ".$order->company_name." - ingen indpakning registreret på shop ".$order->shop_id." (der er indpakning på ".implode(", ",$companyGiftShops[$order->company_id]).")<br>";
          }
        }
     
     }
     
     public function fixmodelscript()
     {
     
        $copyPresents = Present::find_by_sql("SELECT * FROM present WHERE copy_of > 0");
        
        echo "Processing ".countgf($copyPresents)." present copies..<br>";
        foreach($copyPresents as $present)
        {
          $this->processCopy($present);
        }
        
        echo "<br>No original present: ".$this->fmsNoOrg."<br>";
        echo "<br>Multiple original key: ".$this->fmsMultipleOrg."<br>";
        echo "<br>Multiple copy key: ".$this->fmsMultipleCopy."<br>";
        echo "<br>Model not found: ".$this->fmsModelNotFound."<br>";
        echo "<br>Found with id mismatch: ".$this->fmsFoundError."<br>";
        echo "<br>Was ok: ".$this->fmsWasOK."<br>";
        echo "<br>Is fixed: ".$this->fmsFixed."<br>";
        
     }
     
     private $fmsNoOrg = 0;
     private $fmsMultipleOrg = 0;
     private $fmsMultipleCopy = 0;
     private $fmsModelNotFound = 0;
     private $fmsFoundError = 0;
     private $fmsWasOK = 0;
     private $fmsFixed = 0;
     
     private function processCopy($copyPresent)
     {
     
        echo "<br><b>Processing ".$copyPresent->name."</b> [id = ".$copyPresent->id.", copy of ".$copyPresent->copy_of.", shop id = ".$copyPresent->shop_id."]<br>";
     
        $orgPresent = Present::find($copyPresent->copy_of);
        if($orgPresent == null || intval($orgPresent->id) == 0)
        {
          echo "<div style='color: red; font-size: 2em;'>WARNING: Could not find original present id: ".$copyPresent->copy_of."</div><br>";
          $this->fmsNoOrg++;
          return;
        }
        
        $copyModels = $this->findPresentModelMap($copyPresent->id);
        $orgModels = $this->findPresentModelMap($orgPresent->id);
        
        foreach($orgModels as $index => $models)
        {
      
          // More than 1 model with same index
          if(count($models) > 1)
          {
            echo "<div style='color: red; font-size: 2em;'>WARNING: multiple models defined on org present with key: ".$index."</div><br>";
            $this->fmsMultipleOrg++;
          }
                          
          // Index in copy
          if(isset($copyModels[$index]))
          {
            $copyModel = $copyModels[$index][0];                                                                                                                               
            if(count($copyModels[$index]) > 1)
            {
              echo "<div style='color: red; font-size: 2em;'>WARNING: multiple model copies defined on copy present with key: ".$index."</div><br>";
              $this->fmsMultipleCopy++;
            }
            else if($copyModel->original_model_id > 0)
            {
            
              if($copyModel->original_model_id != $models[0]->model_id)
              {
                  echo "<div style='color: red; font-size: 2em;'>WARNING: found match on index: ".$index." (copy id = ".$copyModel->model_id."), but ids do not match ".$copyModel->original_model_id." == ".$models[0]->model_id."</div><br>";
                  $this->fmsFoundError++;
              }
              else
              {
                echo "<div style='color: blue; font-size: 2em;'>WAS OK: ".$index." was alreade set to ".$copyModel->original_model_id."</div><br>";
                $this->fmsWasOK++;
              }
            
            }
            else 
            {
              echo "<div style='color: green; font-size: 2em;'>SET OK: found 1-to-1 match on present model ".$index."</div><br>";
              $this->fmsFixed++;
            }
          
          }
          // Index not in copy
          else            
          {                                     
              $model = $models[0];             
              echo "<div style='color: red; font-size: 2em;'>WARNING: Could not find the model in copy: ".$model->model_name." / lang: ".$model->language_id." / ".$model->model_present_no." [".$index."]</div><br>";
              $this->fmsModelNotFound++;
              
          }
        
        }
        
     }
     
     private function findPresentModelMap($presentid)
     {
        $modellist = PresentModel::find_by_sql("SELECT * FROM present_model WHERE present_id = ".intval($presentid));
        $modelmap = array();
        foreach($modellist as $model)
        {
          $modelmap[$model->language_id."//".$model->model_present_no][] = $model;
        }
        return $modelmap;
     }

    /******************************
     * MAILING
     ******************************/

    public function mailform()
    {

?><html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { margin: 25px; padding: 0px; font-size: 14px; font-family: verdana; overflow-x: hidden; }
        form { margin: 0px; padding: 0px; }
        table { font-size: 0.9em; }
        th { font-weight: bold; padding: 5px; text-align: left; border-bottom: 1px solid #555; }
        td { padding: 5px; border-bottom: 1px solid #aaa; }

    </style>
    <script src="../gavefabrikken_backend/views/lib/jquery.min.js"></script>
</head>
<body>

<h2>Send e-mail</h2>
<form>
<table style="width: 100%;">
    <tr>
        <td valign="top" style="width: 50%; padding: 25px;">
            <b>Emne</b><br>
            <input type="text" value="" size="50" style="width: 100%;" id="subject">
            <br><br>
            <b>Indhold (html)</b><br>
            <textarea cols="20" rows="40" style="width: 100%;" id="content"></textarea>
        </td>
        <td valign="top" style="width: 50%; padding: 25px;">
            <b>Test e-mail</b><br>
            <input type="text" id="testmail" size="30" style="width: 100%;"><br><br>
            <b>Modtagere</b>
            <textarea cols="20" rows="40" style="width: 100%;" id="maillist"></textarea>

        </td>
    </tr>
</table>
</form>

<div id="message"></div>

<input type="button" value="Send test e-mail" onClick="sendTestmail()">
<input type="button" value="Send ALLE e-mails" onClick="sendAllMails()">

<script>

    function getSendData()
    {
        return {subject: $('#subject').val(), content: $('#content').val(),testmail: $('#testmail').val(), maillist: $('#maillist').val() };
    }

    function sendTestmail()
    {
        var data = getSendData();
        data['action'] = 'sendtest';
        $.post( '<?php echo GFConfig::BACKEND_URL; ?>index.php?rt=sch/mailsend',data,function(response) {

            if(response == null || !response.hasOwnProperty('error')) $('#message').html('Ukendt status, stop op og tjek');
            else if(response.status == 1) $('#message').html('OK: '+response.error);
            else $('#message').html('Fejl: '+response.error);
            console.log(response);
        },'json');
    }

    function sendAllMails()
    {

        if(!confirm("Er du sikker på at du vil sende disse e-mails nu?")) return;

        var data = getSendData();
        data['action'] = 'sendmail';
        $.post( '<?php echo GFConfig::BACKEND_URL; ?>index.php?rt=sch/mailsend',data,function(response) {

            if(response == null || !response.hasOwnProperty('error')) $('#message').html('Ukendt status, stop op og tjek');
            else if(response.status == 1) $('#message').html('OK: '+response.error);
            else $('#message').html('Fejl: '+response.error);
            console.log(response);
        },'json');
    }

</script>

</body>
</html><?php

    }

    public function mailsend()
    {

        $subject = $_POST["subject"];
        $content = $_POST["content"];
        $testmail = trimgf($_POST["testmail"]);

        $validMails = 0;
        $invalidMails = 0;

        $maillist = trimgf($_POST["maillist"]);
        $mailLines = explode("\n",$maillist);
        $mails = array();
        foreach($mailLines as $mail)
        {
            $mail = trimgf($mail);
            if($mail != "")
            {
                if($this->isValidMail($mail))
                {
                    $mails[] = $mail;
                    $validMails++;
                }
                else
                {
                    $invalidMails++;
                }
            }
        }

        // Check content
        if($subject == "" || $content == "")
        {
            echo json_encode(array("status" => 0,"error" => "Der er ikke noget indhold."));
            return;
        }

        // Send test
        if($_POST["action"] == "sendtest")
        {

            // Check
            if(!$this->isValidMail($testmail))
            {
                echo json_encode(array("status" => 0,"error" => "Der er ikke angivet en gyldig test-email"));
                return;
            }

            // Send
            $this->addMailQueue($testmail,$subject,$content);
            System::connection()->commit();
            // Response
            echo json_encode(array("status" => 1,"error" => "Test e-mail sendt til ".$testmail,"mails" => countgf($mails)));
            return;
        }

        // Send mails
        else if($_POST["action"] == "sendmail")
        {

            // Check
            if(count($mails) == 0)
            {
                echo json_encode(array("status" => 0,"error" => "Der er ikke nogle gyldige modtager e-mails."));
                return;
            }

            // Send mails
            foreach($mails as $mail)
            {
                $this->addMailQueue($mail,$subject,$content);
            }

            // Response
            System::connection()->commit();
            echo json_encode(array("status" => 1,"error" => "E-mail sendt til ".countgf($mails)." modtagere","mails" => countgf($mails)));
            return;
        }

        // Unknown
        echo json_encode(array("status" => 0,"error" => "Ukendt handling, der er ikke foretaget noget"));

    }

    private function isValidMail($email)
    {
        // Hvis den ikke er over mindst 5 tegn
        if(strlen(trimgf($email)) < 5)
            return false;

        // Hvis der findes 1 @
        if(substr_count ($email,"@") != 1)
            return false;

        // Hvis der ikke er mindst 1 punktum
        if(substr_count ($email,"@") == 0)
            return false;

        // Sidste punktum højere end første @
        if(strrpos ($email,".") < strpos ($email,"@"))
            return false;

        // Returner ok
        return true;
    }

    private function addMailQueue($email,$subject,$content)
    {

        if($email == "" || $subject == "" || $content == "") return false;

        $mailqueue = new MailQueue();
        $mailqueue->sender_name  ="Gavefabrikken";
        $mailqueue->sender_email  = "Gavefabrikken@gavefabrikken.dk";
        $mailqueue->recipent_email = $email;
        $mailqueue->subject = $subject;
        $mailqueue->user_id =  0;
        $mailqueue->body = utf8_decode($content);
        $mailqueue->save();
        return true;
    }

    /********************************************
     * PLUKLISTE
     ********************************************/

    public function Index()
    {
                         return;
  
        $shopidlist = array();
        $expireDates = array();
        $statMatrix = array();

        // Get shops
        /*
        $companyOrderShops = CompanyOrder::find_by_sql("SELECT shop_id, shop_name, certificate_value FROM company_order WHERE (shop_id != 52 || (shop_id = 52 && certificate_value != 640)) GROUP BY shop_id, shop_name, certificate_value ORDER BY shop_name ASC, certificate_value ASC");
        foreach($companyOrderShops as $companyshop)
        {
            if(!in_array($companyshop->shop_id,$shopidlist))
            {
                $shopidlist[] = $companyshop->shop_id;
            }
        }
        */
        
        // Get shops
        $shoplist = Shop::find_by_sql("SELECT * FROM `shop` where is_gift_certificate = 1 && id NOT IN (262,569,287,247,248,264,263,265,251) ORDER BY `shop`.`name` ASC");
        $shopidlist = array();
        foreach($shoplist as $shop)
        {
          $shopidlist[] = $shop->id;
        }

        // Get dates
        $shopUserDeadlines = ShopUser::find_by_sql("SELECT expire_date, shop_id, COUNT(id) as usercount FROM `shop_user` WHERE shop_id IN (".implode(",",$shopidlist).") && blocked = 0 && is_demo = 0 && expire_date IS NOT NULL GROUP BY expire_date, shop_id ORDER BY expire_date ASC");
        foreach($shopUserDeadlines as $shopusercount)
        {
            $deadline = $shopusercount->expire_date->format("Y-m-d");
            if(!in_array($deadline,$expireDates))
            {
                $expireDates[] = $deadline;
            }

            $statMatrix[$shopusercount->shop_id][$deadline] = $shopusercount->attributes["usercount"];

        }

// Start output
?><html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
            body { padding: 0px; font-size: 12px; font-family: verdana; }
            td { font-size: 0.8em; padding: 5px; text-align: right; }
        </style>
           <script src="/gavefabrikken_backend/views/lib/jquery.min.js"></script>
<script src="/gavefabrikken_backend/views/lib/jquery-ui/jquery-ui.js"></script>
<link href="/gavefabrikken_backend/views/lib/jquery-ui/jquery-ui.css" rel="stylesheet">


        <script>
       var _ajaxPath = "../../gavefabrikken_backend/index.php?rt=";
        var _sysId   = '<?php echo $_SESSION["syslogin"]; ?>';
  $(function() {

        var data = {};
       data['systemuser_id'] = _sysId;

       $.post(_ajaxPath+"tab/loadGiftshopPermission",data, function(data ) {
                $("#bizType").html(data)

       });


 });

  function initTabs()
 {
        $( "#bizType" ).buttonset();
 }

   function goToShops()
 {
     window.location.href = "<?php echo GFConfig::BACKEND_URL; ?>index.php?rt=page/shopMain";
 }
 function goToImport()
 {
    window.location.href = "<?php echo GFConfig::BACKEND_URL; ?>index.php?rt=page/companyCardImport&login=sdfiuwhife";
 }
 function goTosale()
 {
    window.location.href = "<?php echo GFConfig::BACKEND_URL; ?>views/stats_view.php?token=sdlfhiekhlsk23232948yruifkgfddsfgsdfghkwsjlzdsfae23f2hd";
 }

 function goToCardShop()
 {
    window.location.href = "<?php echo GFConfig::BACKEND_URL; ?>index.php?rt=page/cardShop&token=asdf43sdha4f34o ";
 }
</script>
    </head>
    <body>
       <center>
        <div id="bizType" >


         </div>
</center>
    <br />

<br >




    <form method="post" action="<?php echo $this->getUrl("pluk"); ?>/login">

        <?php if($this->error != "") echo "<div style='color: red; font-weight: bold; padding: 20px;'>".$this->error."</div>"; ?>

        <div>
            <b>Kriterier for liste:</b>
            <table>
                <tr>
                    <td>Shop</td>
                    <td style="text-align: left;">
                        <select name="shopid"><?php
                            foreach($shoplist as $shop)
                            {
                                echo "<option value='".$shop->id."'>".$shop->name." (beløb: ".$shop->card_value.", id: ".$shop->id.")</option>";
                            }
                        ?></select>
                    </td>
                </tr>
                <tr>
                    <td>Deadline</td>
                    <td style="text-align: left;">
                        <select name="expire"><?php
                            foreach($expireDates as $date)
                            {
                                echo "<option value='".$date."'>".$date."</option>";
                            }
                            ?></select>
                    </td>
                </tr>
                <tr>
                    <td>Indpakket</td>
                    <td style="text-align: left;">
                        <select name="wrapped">
                          <option value="">-</option>
                          <option value="0">Nej</option>
                          <option value="1">Ja</option>'
                        </select>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="text-align: center;">
                        <button name="action" value="fetch" >Hent plukliste</button>
                        <button name="action" value="labels" >Hent labelliste</button>
                        <button name="action" value="checkcompany" >Tjek virksomheder</button>
                        <button name="action" value="check" disabled style="display: none;">Tjek plukliste</button>
                        <button name="action" value="labels" disabled style="display: none;">Hent labelliste</button>
                    </td>
                </tr>
            </table>

            <br><br>
            <b>Oversigt over fordeling af shop gavekort vs deadline</b>
            <table style="width: min-width: 500px;">
                <tr style="font-weight: bold;">
                    <td style="text-align: left;">Shop / Dato</td>
                    <?php foreach($expireDates as $date) echo "<td>".$date."</td>"; ?>
                    <td>Total</td>
                </tr>
                <?php
                $dateTotals = array();
                foreach($shoplist as $companyshop)
                {
                    $total = 0;
                    ?><tr>
                        <td style="text-align: left;"><?php echo $companyshop->name. "(beløb: ".$companyshop->card_value." / id: ".$companyshop->id.")"; ?></td>
                        <?php
                            foreach($expireDates as $date)
                            {

                                $amount = isset($statMatrix[$companyshop->id][$date]) ? intval($statMatrix[$companyshop->id][$date]) : 0;
                                $total+= $amount;
                                echo "<td>".$amount."</td>";

                                if(!isset($dateTotals[$date])) $dateTotals[$date] = $amount;
                                else $dateTotals[$date] += $amount;

                            }
                        ?>
                        <td><?php echo $total; ?></td>
                    </tr><?php


                }

                ?>
                <tr style="font-weight: bold;">
                    <td  style="text-align: left;">Total</td>
                    <?php

                    $total = 0;
                    foreach($expireDates as $date)
                    {
                        $total += $dateTotals[$date];
                        echo "<td>".$dateTotals[$date]."</td>";

                    }

                    ?>
                    <td><?php echo $total; ?></td>
                </tr>
            </table>

        </div>

    </form>
    </body>
</html><?php


    }


    /***
     * PLUK LISTE
     */

    public $plukWarningList = array();
    public function addPlukWarning($message)
    {
      if(trimgf($message) == "") return;
      if(in_array(trimgf($message),$this->plukWarningList)) return;
      $this->plukWarningList[] = trimgf($message);
    }

    private function overridePresentNames()
    {
      return array(
        "301" => "Pillivuyt Tekstilsett 6 deler",
        "302" => "Cavalet ryggsekk med roll-up-funksjon, 20 l.",
        "307a" => "Georg Jensen Damask badehåndkle 2 stk.",
        "307b" => "Georg Jensen Damask badehåndkle 2 stk.",
        "308a" => "Stelton EM 77 termokanne Brass",
        "308b" => "Stelton EM 77 termokanne Brass",
        "309" => "HAY Lup Copper lysestake",
        "313" => "Eva Solo Legio Nova fat 2 stk",
        "314" => "Stelton Pure Black tranchersett",
        "315" => "Thoraldsdottir lykkekopp, vennskap - 1 stk.",
        "316" => "Mørkholt strikket pledd, råhvit/sort",
        "317" => "Hi5 CIRCLE bluetooth-høyttaler, Ø:18 cm",
        "318" => "Mørkholt maritim oljelampe rustfri",
        "320a" => "Donasjon til Sykehusbarn eller Fuck Cancer",
        "320b" => "Donasjon til Sykehusbarn eller Fuck Cancer",
        "401" => "Minikompressor / luftpumpe",
        "403a" => "HAY Noc Clip lampe",
        "403b" => "HAY Noc Clip lampe",
        "404" => "5 Deler Eva Solo skål 1,8l og 4 skåler 0,4l",
        "405" => "Cavalet Trolley kabin",
        "406" => "Georg Jensen ildfaste fat 2stk.",
        "407" => "Frederik Bagger 4 stk chamapagne glass",
        "408" => "Hi5 PLUGS In Ear høretelefoner",
        "409" => "Menu quiltet vatteppe ",
        "410" => "Stantox høytrykkspyler",
        "411" => "LindDNA bordbrikker i skinn 4 stk.",
        "414a" => "Georg Jensen Damask håndkleserie",
        "414b" => "Georg Jensen Damask håndkleserie",
        "415" => "Thoraldsdottir lykkekopp, vennskap - 2 stk.",
        "416a" => "Donasjon til Sykehusbarn eller Fuck Cancer",
        "416b" => "Donasjon til Sykehusbarn eller Fuck Cancer",
        "417" => "Raadvad Sensei knivsett m. 3 kniver",
        "420" => "Hi5 CYLINDER bluetooth-høyttaler Lys og lyd",
        "421" => "Piknik-kjelke",
        "601" => "Skagerak utepeis",
        "602" => "Mørkholt flagermusstol",
        "604" => "Pillivuyt Ildfaste former 2 stk.",
        "606" => "Skagerak Bring Along parasoll",
        "608" => "Frederik Bagger Crispy Eightball 6 stk",
        "609" => "Georg Jensen Damask G2 Hodetelefon",
        "610" => "Stantox verktøykoffert",
        "611" => "Svalbard bambussengetøy 2 sett 140x200/220cm",
        "614" => "Georg Jensen Damask S2 Bluetooth-høyttaler",
        "615" => "Eva Trio Startersett – 3 deler",
        "616" => "Weber steaksett til 6 pers. stål",
        "618" => "Georg Jensen Damask strikket pledd grå/sort",
        "620" => "Kitchen Master kjøkkenmaskin",
        "623a" => "Kähler Hammershøi fat Ø40 cm",
        "623b" => "Kähler Hammershøi fat Ø40 cm",
        "625" => "LindDNA bordbrikker i skinn 6 stk.",
        "626a" => "Donasjon til Sykehusbarn eller Fuck Cancer",
        "626b" => "Donasjon til Sykehusbarn eller Fuck Cancer",
        "801" => "Skagerak utepeis med grill",
        "802" => "Vinkjøleskap med plass til 8 flasker",
        "805" => "Georg Jensen Ilse Crawford kanne 1,2 l.",
        "806" => "Royal knivsett m. 5 kniver  slipestål",
        "807a" => "El-gitar m/forsterker eller keyboard",
        "807b" => "El-gitar m/forsterker eller keyboard",
        "808a" => "Georg Jensen Damask sengetøy",
        "808b" => "Georg Jensen Damask sengetøy",
        "809" => "Eva Solo Legio Nova 7deler",
        "810" => "Raadvad Sensei knivsett m. 5 kniver og slipestål",
        "812" => "by Lassen Kubus 4 lysestake, 14x14 cm, sort",
        "813" => "Cavalet kabine trolley med vekt og USB",
        "816" => "Georg Jensen Damask G1 Hodetelefon m/støyred.",
        "817" => "Arendal Oljelamper 3 stk. Ø: 16, 20, 25 cm",
        "818" => "Frederik Bagger Signature glasspakke 18 deler",
        "819a" => "Dyrberg/Kern armbåndsur",
        "819b" => "Dyrberg/Kern armbåndsur",
        "819c" => "Dyrberg/Kern armbåndsur",
        "819d" => "Dyrberg/Kern armbåndsur",
        "820" => "Lofoten Multimøbel med 5 muligheter, sort",
        "821" => "KitchenAid kaffemaskin med kanne 1,7 l, sort",
        "822" => "Thoraldsdottir cava lykkeglass - 2 stk.",
        "826" => "WMF grytesett Saphir, 4 gryter og lokk",
        "827" => "Georg Jensen Damask S1 Bluetooth-høyttaler",
        "828a" => "Donasjon til Sykehusbarn eller Fuck Cancer",
        "828b" => "Donasjon til Sykehusbarn eller Fuck Cancer"
      );
    }
    
    private function overrideModelNames()
    {
      return array(
        "307a" => "Georg Jensen Damask håndkle 70X140 - Skifer",
        "307b" => "Georg Jensen Damask håndkle 70X140 - Hvit",
        "308a" => "Stelton EM77 termokanne 1 l., Sort",
        "308b" => "Stelton EM77 termokanne 1 l., Hvit",
        "320a" => "Donasjon, Stiftelsen Sykehusbarn",
        "416a" => "Donasjon, Stiftelsen Sykehusbarn",
        "617b" => "DFDS Mini-cruise med havutsikt",
        "626a" => "Donasjon, Stiftelsen Sykehusbarn",
        "811b" => "DFDS Mini-cruise med havutsikt",
        "828a" => "Donasjon, Stiftelsen Sykehusbarn"
      );
    }

    public function pluk()
    {
    
        ini_set('memory_limit','2000M');
        set_time_limit (40*60);

        if(!isset($_POST["shopid"]))
        {
            $this->error = "Der er ikke angivet en shop";
            return $this->Index();
        }

        if(!isset($_POST["expire"]))
        {
            $this->error = "Der er ikke angivet deadline dato";
            return $this->Index();
        }

       
        if(!isset($_POST["action"]) || ($_POST["action"] != "check" && $_POST["action"] != "fetch" && $_POST["action"] != "labels" && $_POST["action"] != "checkcompany"))
        {
            $this->error = "Ukendt handling, prøv igen.";
            return $this->Index();
        }
         
         
         try
        {
              
         
        ob_start();        
                
        // Set filter vars
        $shopid = intval($_POST["shopid"]);
        $expire = strtotime ($_POST["expire"]);
        $action = $_POST["action"];
        $wrapped = trimgf($_POST["wrapped"]);
        
        $useWrapped = ($wrapped === "1" || $wrapped === "0");
        $isWrapped = ($wrapped === "1");
       
       $start = time();
       
       
        /** PROCESS USERS AND COMPANY **/
        $autovalgName = "Autovalg";
        if($shopid == 287) $autovalgName = "Valg ej foretaget - Juleæske";
        if($shopid == 290) $autovalgName = "Valg ej foretaget - Juleæske";                                                      
        if($shopid == 310) $autovalgName = "Valg ej foretaget - Juleæske";
        if($shopid == 265) $autovalgName = "Valg ej foretaget - Juleæske";
        if($shopid == 54) $autovalgName = "Valg ej foretaget - Juleæske";
        if($shopid == 55) $autovalgName = "Valg ej foretaget - Juleæske";
        if($shopid == 56) $autovalgName = "Valg ej foretaget - Juleæske";
        if($shopid == 53) $autovalgName = "Valg ej foretaget - Fabrikkens juleæske 2017, Giga med Moët champagne";
        if($shopid == 52) $autovalgName = "Valg ej foretaget - Juleæske";
        if($shopid == 57) $autovalgName = "Autovalg";
        if($shopid == 58) $autovalgName = "Autovalg";
        if($shopid == 59) $autovalgName = "Autovalg";
          
        // Load all shop users
        
        // USED FOR SPECIAL SELECTS: Efterlevering ordre
        $shopuserlist = ShopUser::find_by_sql("SELECT * FROM shop_user WHERE id IN (SELECT shopuser_id FROM `order` WHERE present_id IN (3245,3247,3246)) && shop_id = ".$shopid." && blocked = 0 && is_demo = 0 && expire_date = '".date("Y-m-d",$expire)."'");
        //$shopuserlist = ShopUser::find_by_sql("SELECT * FROM shop_user WHERE shop_id = ".$shopid." && blocked = 0 && is_demo = 0 && expire_date = '".date("Y-m-d",$expire)."'");
        $companyusers = array();
                             
        // Divide by company
        foreach($shopuserlist as $shopuser)
        {
            if(!isset($companyusers[$shopuser->company_id])) $companyusers[$shopuser->company_id] = array();
            $companyusers[$shopuser->company_id][$shopuser->id] = array("shopuser" => $shopuser,"alias" => "0");
        }
                              
        // Load companies into map
        $companyList = Company::find_by_sql('SELECT * FROM company WHERE active = 1 ORDER BY name ASC, ship_to_company ASC');
        $companyMap = array();
        $companyAdresses = array();
        $dealCompany = array();
        $dealMap = array();
        
        foreach($companyList as $company) 
        {
          
          $companyMap[$company->id] = $company;
          
          // Add deal text
          if(trimgf($company->rapport_note) != "")
          {
            if(!isset($dealMap[$company->id])) $dealMap[$company->id] = array();
            $dealMap[$company->id][] = trimgf($company->rapport_note);
            $dealCompany[$company->cvr] = true;
          }
          
        }
                      
        // Load company orders into array
        //$orderList = Order::find_by_sql("SELECT * FROM `order` WHERE shop_id = ".$shopid." && is_demo = 0 && company_id IN (".implode(",",array_keys($companyMap)).")");
        
        // ADJUSTED ORDER SELECT: Efterlevering
        $orderList = Order::find_by_sql("SELECT * FROM `order` WHERE present_id IN (3245,3247,3246) && shop_id = ".$shopid." && is_demo = 0 && company_id IN (".implode(",",array_keys($companyMap)).")");

        /** PROCESS USERS AND COMPANY **/                     
                       
        // Load gift wrap
        $companyOrders = CompanyOrder::find_by_sql("SELECT * FROM company_order WHERE is_cancelled = 0 && company_id IN (".implode(",",array_keys($companyMap)).") && giftwrap = 1");
        $giftwrapMap = array();
        foreach($companyOrders as $co) $giftwrapMap[$co->company_id] = true;
        
        // Filter by gift wrap 
        if($useWrapped)
        {
            $newCompanyList = array();
            $newCompanyMap = array();
            foreach($companyList as $company)
            {
              if(($isWrapped == isset($giftwrapMap[$company->id])))
              {
                $newCompanyList[] = $company;
                $newCompanyMap[$company->id] = $company;
              }
              else
              {
                unset($companyusers[$company->id]);
              }
            }
        }
          echo countgf($orderList)." ORDRE / ".countgf($companyusers)." USERS..";
              
        /** FIND CERTIFICATE VALUE **/
                               
        $shopName = "";    
        $shopCertValue = 0;                                                  
        $shopCertValues = array();
        // && expire_date = '".date("Y-m-d",$expire)."'
        $companyOrders = CompanyOrder::find_by_sql("SELECT * FROM company_order WHERE is_cancelled = 0 && shop_id = ".$shopid." && company_id IN (".implode(",",array_keys($companyMap)).")");
        $certValues = array();
        
        
        
        foreach($companyOrders as $co) 
        {
                
          if(!isset($certValues[$co->company_id])) $certValues[$co->company_id] = array();
          $certValues[$co->company_id][] = array("start" => $co->certificate_no_begin, "end" => $co->certificate_no_end,"value" => $co->certificate_value);
          $shopCertValues[$co->certificate_value] = true;
          $shopName = $co->shop_name;
          
          // Find adress
          $company = $companyMap[$co->company_id];
          $companyShipName = (trimgf($company->ship_to_company) != "" ? trimgf($company->ship_to_company) : trimgf($company->name));
          
          $addressHash = $companyShipName."_".trimgf($company->ship_to_address)."_ ".trimgf($company->ship_to_address_2)."_".trimgf($company->ship_to_postal_code)."_".trimgf($company->ship_to_city);
          $addressHash = mb_strtolower($addressHash);
          if(!isset($companyAdresses[$co->company_id])) $companyAdresses[$co->company_id] = array();
          if(!isset($companyAdresses[$co->company_id][$addressHash]))
          {
              $companyAdresses[$co->company_id][$addressHash] = array("company" => $companyShipName,"address" => $company->ship_to_address,"address2" => $company->ship_to_address_2,"zip" => $company->ship_to_postal_code,"city" => $company->ship_to_city,"contact" => $company->contact_name,"phone" => $co->contact_phone,"cvr" => $company->cvr,"salesnote" => array(),"dealtext" => array(), "certs" => array());
          }  

          /*
          $addressHash = trimgf($co->ship_to_company)."_".trimgf($co->ship_to_address)."_ ".trimgf($co->ship_to_address_2)."_".trimgf($co->ship_to_postal_code)."_".trimgf($co->ship_to_city);
          $addressHash = mb_strtolower($addressHash);
          
          if(!isset($companyAdresses[$co->company_id])) $companyAdresses[$co->company_id] = array();
          if(!isset($companyAdresses[$co->company_id][$addressHash]))
          {
              $companyAdresses[$co->company_id][$addressHash] = array("company" => $co->ship_to_company,"address" => $co->ship_to_address,"address2" => $co->ship_to_address_2,"zip" => $co->ship_to_postal_code,"city" => $co->ship_to_city,"contact" => $co->contact_name,"phone" => $co->contact_phone,"cvr" => $co->cvr,"salesnote" => array(),"dealtext" => array(), "certs" => array());
              if(trimgf($companyAdresses[$co->company_id][$addressHash]["company"]) == "") $companyAdresses[$co->company_id][$addressHash]["company"] = $companyMap[$co->company_id]->name;
          }  
          */
          
          // Add deal text
          if(trimgf($co->spdealtxt) != "")
          {
            if(!isset($dealMap[$co->company_id])) $dealMap[$co->company_id] = array();
            $dealMap[$co->company_id][] = trimgf($co->spdealtxt);
            $companyAdresses[$co->company_id][$addressHash]["dealtext"][] = trimgf($co->spdealtxt);
            $dealCompany[$co->cvr] = true;
          }
          
          // Add certificate numbers
          $companyAdresses[$co->company_id][$addressHash]["certs"][] = array("start" => $co->certificate_no_begin,"end" => $co->certificate_no_end);   
          
        }
            
            
           
            
        // Put companies with deal text in top
        $dealList = array();
        $nondealList = array();
        foreach($companyList as $company)
        {
          if(isset($dealCompany[$company->cvr])) $dealList[] = $company;
          else $nondealList[] = $company;
        }
        $companyList = array_merge($dealList,$nondealList);
        
                         
        if(count($shopCertValue) == 0)
        {
          $shopCertValue = 0;
          $this->addPlukWarning("No certificate value found on company orders");
        }                         
        else foreach($shopCertValues as $index => $val) $shopCertValue = $index;
        if(count($shopCertValues) > 1) $this->addPlukWarning("Multiple certificate values found on shop: ".implode(",",array_keys($shopCertValues)));
        $valueAlias = intval($shopCertValue/100);
        
        
        if($shopid == 272) $shopCertValue = 300;
        
        if($shopid == 52) $valueAlias = "JK-";
        else if($shopid == 54) $valueAlias = "4";
        else if($shopid == 55) $valueAlias = "5";
        else if($shopid == 56) $valueAlias = "6";
        else if($shopid == 53) $valueAlias = "GK-";
        else if($shopid == 265) $valueAlias = "JT-";
        
        else if($shopid == 287) $valueAlias = "1";
        else if($shopid == 290) $valueAlias = "2";
        else if($shopid == 310) $valueAlias = "3";
        else if($shopid == 272) $valueAlias = "3";
      
        else if($shopid == 248) $valueAlias = "8";
       
       // Set translate lanugage
       $translateLanguageTo = 1;
       if($shopid == 272 || $shopid == 57 || $shopid == 58 || $shopid == 59) $translateLanguageTo = 4;
                       
        // Find override names
        $overridePresentNames = array();
        $overrideModelNames = array();
        if(in_array($shopid,array(272,57,58,59)))
        {
          $overridePresentNames = $this->overridePresentNames();
          $overrideModelNames = $this->overrideModelNames();
        }
                         
        if($shopid == 52)
        {
              $overridePresentNames["JK-13"] = "7 dele Eva Solo Legio Nova fade 2 st. 5 skåle";
        }
                         
        /** PRESENT, MODELS AND ALIAS **/                         
                        
        // Translate map
        $translateLanguageFrom = 1;
        $translateMap = array();
        
        if($translateLanguageTo != $translateLanguageFrom)
        {
          
          $translateMapTo = array();
          $translateModelList = PresentModel::find_by_sql("SELECT * FROM `present_model` WHERE language_id = ".$translateLanguageTo." && present_id IN (SELECT present_id FROM `shop_present` WHERE shop_id = ".intval($shopid).")");                        
          foreach($translateModelList as $tm)
          {
              $translateMapTo[$tm->model_id] = $tm->id;  
          }                
          
          $translateMapFrom = array();
          $translateModelList = PresentModel::find_by_sql("SELECT * FROM `present_model` WHERE language_id = ".$translateLanguageFrom." && present_id IN (SELECT present_id FROM `shop_present` WHERE shop_id = ".intval($shopid).")");                        
          foreach($translateModelList as $tm)
          {
              $translateMapFrom[$tm->model_id] = $tm->id;  
          }    
          
          foreach($translateMapFrom as $mid => $id)
          {
            if(isset($translateMapTo[$mid])) $translateMap[$id] = $translateMapTo[$mid]; 
          }       
          
        }
          
        // Load present names
        $presentDescriptionList = PresentDescription::find_by_sql("SELECT * FROM present_description WHERE language_id = ".intval($translateLanguageTo)." && present_id IN (SELECT present_id FROM `shop_present` WHERE shop_id = ".intval($shopid).")");  
        $presentNameMap = array();
        foreach($presentDescriptionList as $presentDescription)
        {
          $presentNameMap[$presentDescription->present_id] = $presentDescription->caption;
        }
          
        // Load shop present alias                       
        $shopPresent = ShopPresent::find_by_sql("SELECT * FROM `shop_present` WHERE shop_id = ".intval($shopid)." ORDER BY CAST(alias as unsigned) ASC");
        $presentAliasMap = array();
        $modelAliasMap = array();
        $productList = array();
        
        
                                                      
        // For each shop present find its alias and models aliases
        foreach($shopPresent as $sp)
        {
          $presentAliasMap[$sp->present_id] = $sp->alias;
          $modelData = json_decode($sp->modelalias,true);
          
          if(intval($sp->alias) <= 0) $this->addPlukWarning("Gave id ".$sp->present_id." har ikke noget alias");
          
          if(count($modelData) > 0)
          {
            foreach($modelData as $model => $alias)
            {
              $modelAliasMap[$model] = $alias;
              
              if(isset($translateMap[$model])) 
              {
           
                $model = $translateMap[$model];
              }
              $productList[] = array("name" => "", "present_id" => $sp->present_id,"model_id" => $model,"alias" => $sp->alias.$alias);
            }
          }
          else
          {
              $productList[] = array("name" => "", "present_id" => $sp->present_id,"model_id" => 0,"alias" => $sp->alias);
          }
          
        }
               
                       
        // Load unknown presents and fix copy_of
        $unknownList = Present::find_by_sql("SELECT * FROM `present` WHERE id NOT IN (SELECT present_id FROM shop_present WHERE shop_id = ".intval($shopid).") AND id IN (SELECT present_id FROM `order` WHERE is_demo = 0 && shop_id = ".intval($shopid).")");
        foreach($unknownList as $present)
        {
          if($present->copy_of > 0 && isset($presentAliasMap[$present->copy_of]))
          {
            $presentAliasMap[$present->id] = $presentAliasMap[$present->copy_of];
            //if(!in_array($present->copy_of,$presentIds)) $presentIds[] = $present->copy_of;
          }
          else
          {
            $presentCopies = Present::find_by_sql("SELECT * FROM `present` WHERE copy_of = ".$present->id." OR (copy_of = ".$present->copy_of." AND copy_of > 0)");
            $handles = false;
            
            if(count($presentCopies) > 0)
            {
              foreach($presentCopies as $pc)
              {
                if(isset($presentAliasMap[$pc->id]))
                {
                  $presentAliasMap[$present->id] = $presentAliasMap[$pc->id];
                  $handles = true;
                  //if(!in_array($pc->id,$presentIds)) $presentIds[] = $pc->id;
                }
              }
            }
            
            if($handles == false) 
            {
              $found = false;
              
              // SPECIAL RULES
              if($shopid == 56)
              {
                if($present->id == 2594) 
                {
                  $found = true;
                  $presentAliasMap[$present->id] = "43";
                  $aliasPresentMap["43"] = "Alfi Juwel termokande forkromet 1L";
                  $aliasModelMap["43"] = "";
                  $productList[] = array("name" => "", "present_id" => $present->id,"model_id" => 0,"alias" => "43");
                } 
                
              }
              
             
              // Not found
              if($found == false)
              {
                $this->addPlukWarning('Kunne ikke finde alias til gave id: '.$present->id);
              }
            
              
            }
            
          }
        }
                 /*
        if($shopid == 52)
        {
          $presentAliasMap[99900] = "41";
          $aliasPresentMap["41"] = "Sega spillekonsol med 80 spil";
          $aliasModelMap["41"] = "";
          $productList[] = array("name" => "", "present_id" => 99900,"model_id" => 0,"alias" => "41");
        }
        */
      
      
                   
        // Get list of present ids
        $presentIds = array_keys($presentAliasMap);
                       
        // Load presents
        $presentList = Present::find_by_sql("SELECT * FROM present WHERE id IN (".implode(",",$presentIds).")");                       
        $presentMap = array();
        foreach($presentList as $present) 
        {
          if($shopid == 290 && $present->id == 2037) $present->name = "Fabrikkens drømmeæske 2017, Large inkl. étagère";
          $presentMap[$present->id] = $present;
        }                       
          
        // Load all models
        $modelToID = array();
        $idToModel = array();
        $generalModelMap = array();
        $modelAliasNotFound = 0;
        $presentModelMap = array();
        $modelAliasMap2 = array();
        
        $presentModelList = PresentModel::find_by_sql("SELECT * FROM `present_model` WHERE present_id IN (".implode(",",$presentIds).")");
        foreach($presentModelList as $presentModel)
        {
          $modelToID[$presentModel->model_id][] = $presentModel->id;
          $idToModel[$presentModel->id] = $presentModel->model_id;
          
          if(isset($modelAliasMap[$presentModel->id])) $generalModelMap[$presentModel->model_id] = $modelAliasMap[$presentModel->id];
          $presentModelMap[$presentModel->id] = $presentModel;
          if(isset($modelAliasMap[$presentModel->id])) $modelAliasMap2[$presentModel->model_id] = $modelAliasMap[$presentModel->id];
        }
        
         
                       
        // Load unknown present model id's
        if(count($modelAliasMap) > 0)
        {
                                 
        
          $unknownModelList = PresentModel::find_by_sql("SELECT * FROM `present_model` WHERE id NOT IN (".implode(",",array_keys($modelAliasMap)).") AND model_id IN (SELECT present_model_id FROM `order` WHERE is_demo = 0 && present_model_id > 0 && shop_id = ".intval($shopid).")");
          foreach($unknownModelList as $model)
          {
          
            $found = false;
          
            if($model->model_id > 0 && isset($generalModelMap[$model->model_id]))
            {
              $modelAliasMap[$model->id] = $generalModelMap[$model->model_id];
              $found = true;
            }
            else if($model->original_model_id > 0 && isset($modelAliasMap[$model->original_model_id]))
            {
              $modelAliasMap[$model->id] = $modelAliasMap[$model->original_model_id];
              $found = true;
            }
          /*  
            // Could not find by model id, must be a copy
            else
            {
            
                          
              // Try to find similar
              $present = Present::find($model->present_id);
              $similarModels = PresentModel::find_by_sql("SELECT * FROM present_model WHERE model_present_no = '".$model->model_present_no."' && present_id IN (SELECT id from present WHERE (id = ".$present->copy_of." && id > 0) OR copy_of = ".$model->present_id.($present->copy_of > 0 ? " || copy_of = ".$present->copy_of : "").")");
              
              foreach($similarModels as $smodel)
              {
                if($smodel->model_id > 0 && isset($generalModelMap[$smodel->model_id]))
                {
                  $modelAliasMap[$smodel->id] = $generalModelMap[$smodel->model_id]; 
                  $found = true;
                }
                else if($smodel->original_model_id > 0 && isset($modelAliasMap[$smodel->original_model_id]))
                {
                  $modelAliasMap[$smodel->id] = $modelAliasMap[$smodel->original_model_id];
                  $found = true;
                }
              }  
              
            }    
            
            if($found == false)
            {
            
                $modelAliasNotFound++;
                $this->addPlukWarning("Model alias ikke fundet for model ".$model->id." / ".$model->model_id." ".$model->model_name."");
                echo "Model alias ikke fundet for model ".$model->id." / ".$model->model_id." ".$model->model_name."<br>";
                exit();
              }       
                         */                         
          }
                    
        }            
        $companyNoMatchOrders = array();
        $companyMultiOrders = array();
        $companyOrderCount = array();

        $modelMap = array();
        $modelIndex = array();
        
        $aliasCountMap = array();
                      
        $useUnknownAlias = false;         
        
                              
        $specialAdded = array();
                              
        // Process all orders
        foreach($orderList as $order)
        {
        
            if($shopid == 56 && $order->present_model_id == 0 && $order->present_id == 2485)
            {
              $order->present_model_id = 1048; 
            }
           
        
            if(isset($companyusers[$order->company_id]) && isset($companyusers[$order->company_id][$order->shopuser_id]))
            {           
                // Add to company user
                if(!isset($companyusers[$order->company_id][$order->shopuser_id]["order"]))
                {     
                    $companyusers[$order->company_id][$order->shopuser_id]["order"] = $order;
                                         
                    // Find alias
                    if(isset($presentAliasMap[$order->present_id]))
                    {
                      $alias = $presentAliasMap[$order->present_id];
                    } 
                    else
                    {
                      $this->addPlukWarning("Present id ".$order->present_id." (".$order->user_username.") does not have an alias!");
                      $alias = 99;
                    }
                                 
                    // Find subalias
                    if($order->present_model_id > 0 && isset($modelAliasMap[$order->present_model_id]))
                    {       
                      $alias .= $modelAliasMap[$order->present_model_id];
                    }
                    else if(isset($modelAliasMap2[$order->present_model_id]))
                    {         
                      $alias .= $modelAliasMap2[$order->present_model_id];
                    }
                    else if($order->present_model_id > 0)
                    {              
                      
                      $present = Present::find($order->present_id);
                      
                      $sql = "SELECT * FROM `present_model` WHERE present_id IN (SELECT id FROM present WHERE id = ".$order->present_id." OR copy_of = ".$order->present_id.") && model_present_no IN (SELECT model_present_no FROM present_model WHERE model_present_no LIKE '".$order->present_model_present_no."')";
                      $lastTryLook = PresentModel::find_by_sql($sql);
                      $found = false;
                      foreach($lastTryLook as $ltm)
                      {
                        if(isset($modelAliasMap[$ltm->id]))
                        {       
                          $alias .= $modelAliasMap[$ltm->id];
                          $found = true;
                          break;
                        }
                        else if(isset($modelAliasMap2[$ltm->id]))
                        {         
                          $alias .= $modelAliasMap2[$ltm->id];
                          $found = true;
                          break;
                        }
                      }
                      
                      if($found == false)
                      {
                      
                        // SPECIAL RULES!
                        if($shopid == 52 && ($order->present_model_id == 456 || $order->present_model_id == 457))
                        {
                          $alias .= "";
                        }  
                        
                        if($shopid == 290 && ($order->present_model_id == 1000))
                        {
                          $alias .= "b";
                          if(!in_array($order->present_model_id,$specialAdded))
                          {
                            $productList[] = array("name" => "", "present_id" => $order->present_id,"model_id" => $order->present_model_id,"alias" => $alias);
                            $presentModelMap[$order->present_model_id] = "Georg Jensen Damask håndklæde 70X140 2 stk. - Hvid";
                            $specialAdded[] = $order->present_model_id;
                          }
                        }    
                        
                        else if($shopid == 55 && ($order->present_model_id == 1028))
                        {
                          $alias .= "b";
                          if(!in_array($order->present_model_id,$specialAdded))
                          {
                            $productList[] = array("name" => "", "present_id" => $order->present_id,"model_id" => $order->present_model_id,"alias" => $alias);
                            $presentModelMap[$order->present_model_id] = "Georg Jensen Damask Håndklæder - Hvid";
                            $specialAdded[] = $order->present_model_id;
                          }
                        }     
                        
                        else if($shopid == 287 && ($order->present_model_id == 994))
                        {
                          $alias .= "b";
                          if(!in_array($order->present_model_id,$specialAdded))
                          {
                            $productList[] = array("name" => "", "present_id" => $order->present_id,"model_id" => $order->present_model_id,"alias" => $alias);
                            $presentModelMap[$order->present_model_id] = "GJD håndklæde, 1 stk 70X140 - Hvid";
                            $specialAdded[] = $order->present_model_id;
                          }
                        }     
                               
                        else if(($order->present_model_id == 1048 ))
                        {
                          $alias .= "a";
                          //echo "ADD SPECIAL 1";      exit();
                          if(!isset($presentModelMap[$order->present_model_id]))
                          {
                            $productList[] = array("name" => "", "present_id" => $order->present_id,"model_id" => $order->present_model_id,"alias" => $alias);
                            $presentModelMap[$order->present_model_id] = "Georg Jensen Damask håndklæder - Skifer";
                            $specialAdded[] = $order->present_model_id;
                          }
                        } 
                        
                        else if(($order->present_model_id == 1049))
                        {
                          $alias .= "b";
                          //echo "ADD SPECIAL 2";  exit();
                          if(!isset($presentModelMap[$order->present_model_id]))
                          {
                            $productList[] = array("name" => "", "present_id" => $order->present_id,"model_id" => $order->present_model_id,"alias" => $alias);
                            $presentModelMap[$order->present_model_id] = "Georg Jensen Damask håndklæder - Hvid";
                            $specialAdded[] = $order->present_model_id;
                          }
                        }  
                        
                        else if($shopid == 52 && ($order->present_model_id == 456))
                        {
                          $order->present_id = 99900;
                          $alias = "41";
                          if(!in_array($order->present_model_id,$specialAdded))
                          {
                            $productList[] = array("name" => "", "present_id" => $order->present_id,"model_id" => $order->present_model_id,"alias" => $alias);
                            $presentNameMap[99900] = "Sega spillekonsol med 80 spil";
                            $presentModelMap[$order->present_model_id] = "";
                            $overridePresentNames["JK-41"] = "Sega spillekonsol med 80 spil"; 
                            $specialAdded[] = $order->present_model_id;
                          }
                        }     
                        
                        
                        // Still not found
                        else
                        {
                          //echo $sql."<pre>".print_r($lastTryLook,true)."</pre>";
                          $this->addPlukWarning("Could not find model alias for present id: ".$order->present_id." / model id: ".$order->present_model_id." / model_present_model_no: ".$order->present_model_present_no)." / name: ".$order->present_name." / model: ".$order->present_model_name;
                          if($alias == "99") $alias .= "U";
                          //echo "Could not find model alias for ".$order->id." - ".$order->user_name." - ".$order->present_id." - ".$order->present_model_id."";
                          //exit();
                        }
                        
                      }
                      
                    }
                    
                    if($shopid == "52" && $alias == "18a") $alias = "17a";
                    if($alias == "99U" || $alias == "99") $useUnknownAlias = true;
                    if($shopid == 56 && $alias == "28a") $alias = "27a";
                                 
                    if(trimgf($alias) == "") $alias = "0";
                    if(!isset($aliasCountMap[$alias])) $aliasCountMap[$alias] = 0;
                    $aliasCountMap[$alias]++;
                    
                    $companyusers[$order->company_id][$order->shopuser_id]["alias"] = $alias;
                                        
                }
                // Already has order, must be a problem
                else
                {
                    if(!isset($companyMultiOrders[$order->company_id])) $companyMultiOrders[$order->company_id] = 0;
                    $companyMultiOrders[$order->company_id]++;
                }
            }
            else
            {        
                if(!isset($companyNoMatchOrders[$order->company_id])) $companyNoMatchOrders[$order->company_id] = 0;
                $companyNoMatchOrders[$order->company_id]++;
            }

            if(!isset($companyOrderCount[$order->company_id])) $companyOrderCount[$order->company_id] = 0;
            $companyOrderCount[$order->company_id]++;
        }
             
             
        // Re-order the product list
        for($i=0;$i<count($productList);$i++)
        {
          for($j=$i+1;$j<count($productList);$j++)
          {
            if(intval($productList[$i]["alias"]) > intval($productList[$j]["alias"]) || (intval($productList[$i]["alias"]) == intval($productList[$j]["alias"]) && (strcmp ( $productList[$i]["alias"],$productList[$j]["alias"] ) > 0)))
            {
              $tmp = $productList[$i];
              $productList[$i] = $productList[$j];
              $productList[$j] = $tmp;
            }
          }
        }
        
        
        $finalPresentList = array(array("name" => $autovalgName,"model" => "","alias" => $valueAlias."00"));
        $aliasPresentMap = array();
        $aliasModelMap = array();
     
        if($useUnknownAlias == true) 
        {
            $aliasPresentMap["99"] = "Ukendt gave!!!";
            $aliasModelMap["99"] = "Ukendt gave!!!";
            $aliasPresentMap["99U"] = "Ukendt gave!!!";
            $aliasModelMap["99U"] = "Ukendt gave!!!";
            $finalPresentList[] = array("name" => "Ukendt gave","model" => "Ukendt model","alias" => "99U");
        }
     
        $noOrderUsers = 0;
        foreach($companyusers as $cul)
        {
          foreach($cul as $cu)
          {
            if(!isset($cu["order"]) && isset($companyMap[$cu["shopuser"]->company_id])) $noOrderUsers++;
          }
        }
                 

        // Get shopusers outside period
        $shopuserNegativeList = ShopUser::find_by_sql("SELECT * FROM shop_user WHERE shop_id = ".$shopid." && blocked = 0 && is_demo = 0 && expire_date != '".date("Y-m-d",$expire)."'");
        $shopuserNegativeMap = array();
        foreach($shopuserNegativeList as $su)
        {
            if(!isset($shopuserNegativeMap[$su->company_id])) $shopuserNegativeMap[$su->company_id] = 0;
            $shopuserNegativeMap[$su->company_id]++;
        }       

                
            
        //////// CHECK COMPANY ///////////

        if($action == "checkcompany")
        {
        
              // Load company map
              $companyList = Company::find_by_sql("SELECT * FROM company");
             
              // Load company data
              $companyOrders = CompanyOrder::find_by_sql("SELECT * FROM company_order");
              $companyData = array();
              
              $noShops = array(57,58,59,272,574);
              
              foreach($companyOrders as $co)
              {
              
                  if(!isset($companyData[$co->company_id])) $companyData[$co->company_id] = array();
                  if(!isset($companyData[$co->company_id]["values"])) $companyData[$co->company_id]["values"] = array();
                  if(!isset($companyData[$co->company_id]["deadline"])) $companyData[$co->company_id]["deadline"] = array();
                  if(!isset($companyData[$co->company_id]["shops"])) $companyData[$co->company_id]["shops"] = array();
                  if(!isset($companyData[$co->company_id]["ordercount"])) $companyData[$co->company_id]["ordercount"] = 0;
              
                  $country = in_array($co->shop_id,$noShops) ? "NO" : "DA";
              
                  if(!in_array($country,$companyData[$co->company_id]["shops"])) $companyData[$co->company_id]["shops"][] = $country;
                  if(!in_array($co->certificate_value,$companyData[$co->company_id]["values"])) $companyData[$co->company_id]["values"][] = $co->certificate_value;
                  if(!in_array($co->expire_date->format('d-m-Y'),$companyData[$co->company_id]["deadline"])) $companyData[$co->company_id]["deadline"][] = $co->expire_date->format('d-m-Y');
                  $companyData[$co->company_id]["ordercount"]++;
                  
              }
              
      
              ?><h2>Firmaer med forskellige kort værdier: Danmark</h2>
              <table>
                <tr style="font-weight: bold; background: #aaa;">
                    <td>Firma</td>
                    <td>CVR</td>
                    <td>Kort værdier</td>
                    <td>Deadlines</td>
                </tr><?php
            
              foreach($companyList as $company)
              {
                if(isset($companyData[$company->id]))
                {
                  if(count($companyData[$company->id]["values"]) > 1 && in_array("DA",$companyData[$company->id]["shops"]))
                  {
                       echo "<tr><td style='padding: 2px; border-bottom: 1px solid #ccc;'>".$company->name."</td><td style='padding: 2px; border-bottom: 1px solid #ccc;'>".$company->cvr."</td><td style='padding: 2px; border-bottom: 1px solid #ccc;'>".implode(", ",$companyData[$company->id]["values"])."</td><td style='padding: 2px; border-bottom: 1px solid #ccc;'>".implode(", ",$companyData[$company->id]["deadline"])."</td></tr>";
                  }
                }
              }
              
              ?></table><?php
              
              ?><h2>Firmaer med forskellige kort værdier: Norge</h2>
              <table>
                <tr style="font-weight: bold; background: #aaa;">
                    <td>Firma</td>
                    <td>CVR</td>
                    <td>Kort værdier</td>
                    <td>Deadlines</td>
                </tr><?php
            
              foreach($companyList as $company)
              {
                if(isset($companyData[$company->id]))
                {
                  if(count($companyData[$company->id]["values"]) > 1 && in_array("NO",$companyData[$company->id]["shops"]))
                  {
                       echo "<tr><td style='padding: 2px; border-bottom: 1px solid #ccc;'>".$company->name."</td><td style='padding: 2px; border-bottom: 1px solid #ccc;'>".$company->cvr."</td><td style='padding: 2px; border-bottom: 1px solid #ccc;'>".implode(", ",$companyData[$company->id]["values"])."</td><td style='padding: 2px; border-bottom: 1px solid #ccc;'>".implode(", ",$companyData[$company->id]["deadline"])."</td></tr>";
                  }
                }
              }
              
              ?></table><?php
              
              //echo "<pre>".print_r($companyData,true)."</pre>";
        }
        
        //////// FETCH PLUKLISTE ///////////
        else if($action == "fetch")
        {
                     
          // Init phpexcel        
          $phpExcel = new PHPExcel();  
          $phpExcel->removeSheetByIndex(0);
          
          // Write product list
          $sheet = $phpExcel->createSheet();
          
          $phpExcel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
          $phpExcel->getDefaultStyle()->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
    
          $sheet->setTitle("Produktliste");
          
          $sheet->getColumnDimension('A')->setWidth(12);
          $sheet->getColumnDimension('B')->setWidth(45);
          $sheet->getColumnDimension('C')->setWidth(40);
          $sheet->getColumnDimension('D')->setWidth(10);
          
          $sheet->setCellValueByColumnAndRow(1,1,"Gavenr.");
          $sheet->setCellValueByColumnAndRow(2,1,"Gavenavn");
          $sheet->setCellValueByColumnAndRow(3,1,"Gavemodel");
          $sheet->setCellValueByColumnAndRow(4,1,"Antal valgt");
          $sheet->getStyle("A1:H1")->getFont()->setBold(true);
          
          
          
          $row = 2;  

          $sheet->setCellValueByColumnAndRow(1,$row,$valueAlias."00");
          $sheet->setCellValueByColumnAndRow(2,$row,$autovalgName);
          $sheet->setCellValueByColumnAndRow(3,$row,"");
          $sheet->setCellValueByColumnAndRow(4,$row,$noOrderUsers);
          $row++;              
          
          $totalCount = $noOrderUsers;
          
               
          
                   
          foreach($productList as $product)
          {
          
          
            $fullAlias = $valueAlias.(strlen(intval($product["alias"])) == 1 ? "0" : "").$product["alias"];
          
            // Find present name
            $presentName = "UKENDT NAVN";
            //if(isset($presentNameMap[$product["present_id"]])) $presentName = $presentNameMap[$product["present_id"]];
            if(isset($presentMap[$product["present_id"]])) $presentName = $presentMap[$product["present_id"]]->name;
            if(isset($overridePresentNames[$fullAlias])) $presentName = $overridePresentNames[$fullAlias];
            
            // Find model name
            $modelName = "UKENDT MODEL";
            if($product["model_id"] == 0) $modelName = "";
            else if(isset($presentModelMap[$product["model_id"]]))
            {
              $model = $presentModelMap[$product["model_id"]];    
              $modelNames = array();
              if(!is_object($model)) $modelName = $model;
              else
              {
                if(trimgf($model->model_name) != "") $modelNames[] = $model->model_name;
                if(trimgf($model->model_no) != "") $modelNames[] = $model->model_no;
                $modelName = implode(", ",$modelNames);
              }
              
            }
            if(isset($overrideModelNames[$fullAlias])) $modelName = $overrideModelNames[$fullAlias];   
            
            $aliasPresentMap[$product["alias"]] = $presentName;
            $aliasModelMap[$product["alias"]] = $modelName;
            
            $sheet->setCellValueByColumnAndRow(1,$row,$fullAlias);
            $sheet->setCellValueByColumnAndRow(2,$row,$presentName);
            $sheet->setCellValueByColumnAndRow(3,$row,$modelName);
            $sheet->setCellValueByColumnAndRow(4,$row,isset($aliasCountMap[$product["alias"]]) ? $aliasCountMap[$product["alias"]] : 0);
            $totalCount += (isset($aliasCountMap[$product["alias"]]) ? $aliasCountMap[$product["alias"]] : 0);
        
            $finalPresentList[] = array("name" => $presentName,"model" => $modelName,"alias" => $valueAlias.(strlen(intval($product["alias"])) == 1 ? "0" : "").$product["alias"]);
            
            $row++;
          }
               
            
              
               
          $sheet->setCellValueByColumnAndRow(1,$row,"");
          $sheet->setCellValueByColumnAndRow(2,$row,"");
          $sheet->setCellValueByColumnAndRow(3,$row,"Total antal");
          $sheet->setCellValueByColumnAndRow(4,$row,$totalCount);
          $row++;   
          
               
          /* START ORDRELISTE */     
               
               
          // Write order list
          $sheet = $phpExcel->createSheet();
          $sheet->setTitle("Ordreliste");
          
          $sheet->getColumnDimension('A')->setWidth(20);
          $sheet->getColumnDimension('B')->setWidth(20);
          $sheet->getColumnDimension('C')->setWidth(13);
          $sheet->getColumnDimension('D')->setWidth(30);
          $sheet->getColumnDimension('E')->setWidth(25);
          $sheet->getColumnDimension('F')->setWidth(25);
          $sheet->getColumnDimension('G')->setWidth(25);
          $sheet->getColumnDimension('H')->setWidth(15);
          $sheet->getColumnDimension('I')->setWidth(12);
          
          $sheet->setCellValueByColumnAndRow(1,1,$shopName." (id ".$shopid."): ordreliste");
          $sheet->setCellValueByColumnAndRow(3,1,"Budget: ".$shopCertValue);
          $sheet->setCellValueByColumnAndRow(5,1,"Deadline: ".date("Y-m-d",$expire));
          
          $BackBlue = array(
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => 'e9f0f2')
            ),
  		  'borders' => array(
            'top' => array(
              'style' => PHPExcel_Style_Border::BORDER_THIN
            )  ,
            'bottom' => array(
              'style' => PHPExcel_Style_Border::BORDER_THIN
            )
          )   
        );
	                       
          $sheet->getStyle('A1:H1')->applyFromArray($BackBlue);
                    
          $row = 2;
          $userCount = 0;
        
          $companyAutogave = array();
          $companyCountMap = array();
                   
                   $companyCountTotal = array();
                      
                   
            foreach($companyList as $company)
            {            
            
                if(isset($companyusers[$company->id]))
                {
                
                  $sheet->setBreak( 'A'.$row , PHPExcel_Worksheet::BREAK_ROW );
    
                   $userList = array();
                     foreach($companyusers[$company->id] as $cu) $userList[] = $cu;
                     
                     //if($company->id == 7261) { echo countgf($userList)."<pre>".print_r($userList,true)."</pre>"; exit(); }
                    
                     // Order by alias
                   for($i=0;$i<count($userList);$i++)
                   {
                      for($j=$i+1;$j<count($userList);$j++)
                      {       
                       if(intval($userList[$i]["alias"]) > intval($userList[$j]["alias"]) || (intval($userList[$i]["alias"]) == intval($userList[$j]["alias"]) && (strcmp ( $userList[$i]["alias"],$userList[$j]["alias"] ) > 0)))
                       {
                        $tmp = $userList[$i];
                        $userList[$i] = $userList[$j];
                        $userList[$j] = $tmp;
                       }
                    }                                                                         
                  }
                                
                
                      
                      // Company details
                      $row++;
                      $sheet->getStyle("A".$row.":Z".$row)->getFont()->setBold(true);
                      $sheet->setCellValueByColumnAndRow(1,$row,"Virksomhed");
                      $sheet->setCellValueByColumnAndRow(2,$row,"Adresse");
                      $sheet->setCellValueByColumnAndRow(3,$row,"Postnr");
                      $sheet->setCellValueByColumnAndRow(4,$row,"By");
                      $sheet->setCellValueByColumnAndRow(5,$row,"Kontakt navn");
                      $sheet->setCellValueByColumnAndRow(6,$row,"Kontakt telefon");
                      $sheet->setCellValueByColumnAndRow(7,$row,"CVR");
                      if($useWrapped) $sheet->setCellValueByColumnAndRow(9,$row,"Indpakket");
                      $row++;
                      
                      
                      $sheet->setCellValueByColumnAndRow(1,$row,$company->name);
                      $sheet->setCellValueByColumnAndRow(2,$row,$company->bill_to_address.(trimgf($company->bill_to_address_2) == "" ? "" : ", ".$company->bill_to_address_2));
                      $sheet->setCellValueByColumnAndRow(3,$row,$company->bill_to_postal_code);
                      $sheet->getStyle('C'.$row)->getNumberFormat()->setFormatCode('0000');
                      
                      $sheet->setCellValueByColumnAndRow(4,$row,$company->bill_to_city);
                      $sheet->setCellValueByColumnAndRow(5,$row,$company->contact_name);
                      $sheet->setCellValueByColumnAndRow(6,$row,$company->contact_phone);
                      $sheet->setCellValueByColumnAndRow(7,$row,$company->cvr);
                      if($useWrapped) $sheet->setCellValueByColumnAndRow(9,$row,$isWrapped ? "Ja" : "Nej");
                      $row++;
                      
                      
                      $companyName = (trimgf($company->ship_to_company) != "" && $translateLanguageTo == 4) ? $company->ship_to_company : $company->name;
                       
                      $sameAddress = true;
                      if($company->name != $companyName) $sameAddress = false;
                      if(($company->bill_to_address.(trimgf($company->bill_to_address_2) == "" ? "" : ", ".$company->bill_to_address_2)) != ($company->ship_to_address.(trimgf($company->ship_to_address_2) == "" ? "" : ", ".$company->ship_to_address_2))) $sameAddress = false;
                      if($company->bill_to_postal_code != $company->ship_to_postal_code) $sameAddress = false;                                                                                                                                                         
                      if($company->bill_to_city != $company->ship_to_city) $sameAddress = false;
                    
                      if($sameAddress == false)
                      {
                        
                        $sheet->getStyle("A".$row.":Z".$row)->getFont()->setBold(true);
                        $sheet->setCellValueByColumnAndRow(1,$row,"Levering");
                        $sheet->setCellValueByColumnAndRow(2,$row,"Leveringsadresse");
                        $sheet->setCellValueByColumnAndRow(3,$row,"Levering postnr");
                        $sheet->setCellValueByColumnAndRow(4,$row,"Levering by");
                        $sheet->setCellValueByColumnAndRow(5,$row,"Kontakt Navn");
                        $sheet->setCellValueByColumnAndRow(6,$row,"Kontakt telefon");
                        $sheet->setCellValueByColumnAndRow(7,$row,"CVR");
                        //if($useWrapped) $sheet->setCellValueByColumnAndRow(9,$row,"Indpakket");
                        $row++;
                        
                       
                      
                        $sheet->setCellValueByColumnAndRow(1,$row,$companyName);
                        $sheet->setCellValueByColumnAndRow(2,$row,$company->ship_to_address.(trimgf($company->ship_to_address_2) == "" ? "" : ", ".$company->ship_to_address_2));
                        $sheet->setCellValueByColumnAndRow(3,$row,$company->ship_to_postal_code);
                        $sheet->getStyle('C'.$row)->getNumberFormat()->setFormatCode('0000');
                        $sheet->setCellValueByColumnAndRow(4,$row,$company->ship_to_city);
                        $sheet->setCellValueByColumnAndRow(5,$row,$company->contact_name);
                        $sheet->setCellValueByColumnAndRow(6,$row,$company->contact_phone);
                        $sheet->setCellValueByColumnAndRow(7,$row,$company->cvr);
                        //if($useWrapped) $sheet->setCellValueByColumnAndRow(9,$row,$isWrapped ? "Ja" : "Nej");
                        $row++;
                        
                        
                      }
                      
                        $row++;  
                      
                        // Output notes
                        $noteCount = 0;
                        if(isset($dealMap[$company->id]) && countgf($dealMap[$company->id]) > 0)
                        {
                          foreach($dealMap[$company->id] as $note)
                          {
                            $sheet->getStyle("B".$row.":G".$row)->getFont()->setBold(true);
                            $sheet->getStyle('B'.$row.':G'.$row)->applyFromArray($BackBlue);
                            $sheet->setCellValueByColumnAndRow(2,$row,$note);
                            
                            $row++;
                            $noteCount++;
                          }
                        }
                        if($noteCount > 0) $row++;
                                
                       // Order list headline
                       $sheet->setCellValueByColumnAndRow(1,$row,"Firmanavn");
                       $sheet->setCellValueByColumnAndRow(2,$row,"Gavekort nr");
                       $sheet->setCellValueByColumnAndRow(3,$row,"Gave nr.");
                       $sheet->setCellValueByColumnAndRow(4,$row,"Gave");
                       $sheet->setCellValueByColumnAndRow(5,$row,"Model");
                       $sheet->setCellValueByColumnAndRow(6,$row,"Navn");
                       $sheet->setCellValueByColumnAndRow(7,$row,"E-mail");
                       $sheet->setCellValueByColumnAndRow(8,$row,"Deadline");
                       if($useWrapped) $sheet->setCellValueByColumnAndRow(9,$row,"Indpakket");
                       $sheet->getStyle("A".$row.":I".$row)->getFont()->setBold(true);
                       $row++;
                                   
                       // Output orders
                      foreach($userList as $cu)
                      {
                                    
                          $giftCertNo = isset($cu["order"]) ? $cu["order"]->gift_certificate_no : $cu["shopuser"]->username;
                    
                        
                          if(true)
                          {
                          
                          
                          
                            $alias = $valueAlias.(strlen(intval(trimgf($cu["alias"]))) == 1 ? "0" : "").$cu["alias"];
                            
                            $presentName = isset($cu["order"]) && intval($cu["alias"]) > 0  ? (isset($aliasPresentMap[$cu["alias"]]) ? $aliasPresentMap[$cu["alias"]] : "Ukendt gave") : $autovalgName;
                            $modelName = isset($cu["order"]) && intval($cu["alias"]) > 0 ? (isset($aliasModelMap[$cu["alias"]]) ? $aliasModelMap[$cu["alias"]] : "Ukendt model!") : "";
                            
                            
                            
                          
                          //if($alias == "628a") echo "<pre>".print_r(array($cu,$aliasPresentMap,$aliasModelMap),true)."</pre>";            exit();
                          
                            
                            $sheet->setCellValueByColumnAndRow(1,$row,$company->name);
                            $sheet->setCellValueByColumnAndRow(2,$row,isset($cu["order"]) ? $cu["order"]->gift_certificate_no : $cu["shopuser"]->username);
                            $sheet->setCellValueByColumnAndRow(3,$row,$alias);
                            
                            $sheet->setCellValueByColumnAndRow(4,$row,$presentName);
                            $sheet->setCellValueByColumnAndRow(5,$row,$modelName);
                            
                            $sheet->setCellValueByColumnAndRow(6,$row,isset($cu["order"]) ? $cu["order"]->user_name : "Gavekort ".$cu["shopuser"]->username);
                            $sheet->setCellValueByColumnAndRow(7,$row,isset($cu["order"]) ? $cu["order"]->user_email : "");
                            $sheet->setCellValueByColumnAndRow(8,$row,$cu["shopuser"]->expire_date->format("Y-m-d"));
                            if($useWrapped) $sheet->setCellValueByColumnAndRow(9,$row,$isWrapped ? "Ja" : "Nej");
                            $row++;
                            $userCount++;
                            
                                    
                            
                            
                            if(!isset($companyCountMap[$company->id])) $companyCountMap[$company->id] = array();
                            if(!isset($companyCountMap[$company->id][$alias])) $companyCountMap[$company->id][$alias] = 0;
                            $companyCountMap[$company->id][$alias]++;
                                     
                            if(!isset($companyCountTotal[$company->id])) $companyCountTotal[$company->id] = 0;
                            $companyCountTotal[$company->id]++;
                          
                         
                        
                            if(!isset($cu["order"]))
                            {
                              if(!isset($companyAutogave[$company->id])) $companyAutogave[$company->id] = 0;
                              $companyAutogave[$company->id]++;
                            }
                            
                            if($alias == "19") 
                            {
                              echo "Mødte alias 19 4!!!";
                              
                            }
                            
                            
                          }
                        
                      }
                 
                
                     $row++;
                     
                }
            }
        
                   
          
          /**
           * ENDORDRELISTE START VIRKSOMHEDER
           */
             
                  
          //$sheet->setCellValueByColumnAndRow(1,$row,"Usercount: ".$userCount);
        
          // Company list
          $sheet = $phpExcel->createSheet();
          $sheet->setTitle("Virksomheder");
          
          $row = 1;
          $sheet->getStyle("A".$row.":Z".$row)->getFont()->setBold(true);
          $sheet->setCellValueByColumnAndRow(1,$row,"Virksomhed");
          $sheet->setCellValueByColumnAndRow(2,$row,"CVR");
          $sheet->setCellValueByColumnAndRow(3,$row,"Antal gavekort");
          $sheet->setCellValueByColumnAndRow(4,$row,"Antal autogave");
          $sheet->setCellValueByColumnAndRow(5,$row,"Gavekort udenfor denne periode");
          if($useWrapped) $sheet->setCellValueByColumnAndRow(6,$row,"Indpakning");
          
          
          $sheet->getColumnDimension('A')->setWidth(25);
          $sheet->getColumnDimension('B')->setWidth(15);
          $sheet->getColumnDimension('C')->setWidth(15);
          $sheet->getColumnDimension('D')->setWidth(15);
          $sheet->getColumnDimension('E')->setWidth(15);
          $sheet->getColumnDimension('F')->setWidth(15);
          $row++;
                        
          foreach($companyList as $company)
          {
          
                if(isset($companyusers[$company->id]))
                {
                  
                  $autogaveCount = 0;
                  if(isset($companyAutogave[$company->id]) && $company->id > 0)
                  {
                    $autogaveCount = $companyAutogave[$company->id];
                  }
                
                  $sheet->setCellValueByColumnAndRow(1,$row,$company->name);
                  $sheet->setCellValueByColumnAndRow(2,$row,$company->cvr);
                  $sheet->setCellValueByColumnAndRow(3,$row,countgf($companyusers[$company->id]));
                  $sheet->setCellValueByColumnAndRow(4,$row,$autogaveCount);
                  $sheet->setCellValueByColumnAndRow(5,$row,isset($shopuserNegativeMap[$company->id]) ? $shopuserNegativeMap[$company->id] : 0);
                  if($useWrapped) $sheet->setCellValueByColumnAndRow(6,$row,$isWrapped ? "Ja" : "Nej");
                  $row++;
                  
                  $totalCount = 0;
                  if(isset($companyCountTotal[$company->id])) $totalCount = $companyCountTotal[$company->id];
                  if($totalCount != countgf($companyusers[$company->id]))
                  {
                    $this->addPlukWarning("Virksomheden: ".$company->name." (id = ".$company->id.") har kun ".$totalCount." med i listen, men der ligger ".countgf($companyusers[$company->id])." ordre");
                  }
                  
                  
            }
            
          }
            
            
          /**
           * ENDVIRKSOMHEDER START GAVEVALG
           */
           
         
          // Company list
          $sheet = $phpExcel->createSheet();
          $sheet->setTitle("Gavevalg");
          
          
          $sheet->getColumnDimension('A')->setWidth(22);
          $sheet->getColumnDimension('B')->setWidth(48);
          $sheet->getColumnDimension('C')->setWidth(49);
          $sheet->getColumnDimension('D')->setWidth(12);
          $sheet->getColumnDimension('E')->setWidth(25);
          $sheet->getColumnDimension('F')->setWidth(25);
          $sheet->getColumnDimension('G')->setWidth(25);
          $sheet->getColumnDimension('H')->setWidth(15);
          $sheet->getColumnDimension('I')->setWidth(12);
          
          $sheet->setCellValueByColumnAndRow(1,1,$shopName." (id ".$shopid."): ordreliste");
          $sheet->setCellValueByColumnAndRow(3,1,"Budget: ".$shopCertValue);
          $sheet->setCellValueByColumnAndRow(5,1,"Deadline: ".date("Y-m-d",$expire));
          
          $BackBlue = array(
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => 'e9f0f2')
            ),
  		  'borders' => array(
            'top' => array(
              'style' => PHPExcel_Style_Border::BORDER_THIN
            )  ,
            'bottom' => array(
              'style' => PHPExcel_Style_Border::BORDER_THIN
            )
          )   
        );
	                       
          $sheet->getStyle('A1:H1')->applyFromArray($BackBlue);
                    
          $row = 2;
          
         
              
            foreach($companyList as $company)
            {            
            
                if(isset($companyusers[$company->id]))
                {
                
                $sheet->setBreak( 'A'.$row , PHPExcel_Worksheet::BREAK_ROW );
                
                   $userList = array();
                     foreach($companyusers[$company->id] as $cu) $userList[] = $cu;
                    
                     // Order by alias
                   for($i=0;$i<count($userList);$i++)
                   {
                      for($j=$i+1;$j<count($userList);$j++)
                      {       
                       if(intval($userList[$i]["alias"]) > intval($userList[$j]["alias"]) || (intval($userList[$i]["alias"]) == intval($userList[$j]["alias"]) && (strcmp ( $userList[$i]["alias"],$userList[$j]["alias"] ) > 0)))
                       {
                        $tmp = $userList[$i];
                        $userList[$i] = $userList[$j];
                        $userList[$j] = $tmp;
                       }
                    }                                                                         
                  }
                  
                
                      
                     // Company details
                      $row++;
                      $sheet->getStyle("A".$row.":Z".$row)->getFont()->setBold(true);
                      $sheet->setCellValueByColumnAndRow(1,$row,"Virksomhed");
                      $sheet->setCellValueByColumnAndRow(2,$row,"Adresse");
                      $sheet->setCellValueByColumnAndRow(3,$row,"Postnr");
                      $sheet->setCellValueByColumnAndRow(4,$row,"By");
                      $sheet->setCellValueByColumnAndRow(5,$row,"Kontakt navn");
                      $sheet->setCellValueByColumnAndRow(6,$row,"Kontakt telefon");
                      $sheet->setCellValueByColumnAndRow(7,$row,"CVR");
                      if($useWrapped) $sheet->setCellValueByColumnAndRow(9,$row,"Indpakket");
                      $row++;
                      
                      
                      $sheet->setCellValueByColumnAndRow(1,$row,$company->name);
                      $sheet->setCellValueByColumnAndRow(2,$row,$company->bill_to_address.(trimgf($company->bill_to_address_2) == "" ? "" : ", ".$company->bill_to_address_2));
                      $sheet->setCellValueByColumnAndRow(3,$row,$company->bill_to_postal_code);
                      $sheet->getStyle('C'.$row)->getNumberFormat()->setFormatCode('0000');
                      $sheet->setCellValueByColumnAndRow(4,$row,$company->bill_to_city);
                      $sheet->setCellValueByColumnAndRow(5,$row,$company->contact_name);
                      $sheet->setCellValueByColumnAndRow(6,$row,$company->contact_phone);
                      $sheet->setCellValueByColumnAndRow(7,$row,$company->cvr);
                      if($useWrapped) $sheet->setCellValueByColumnAndRow(9,$row,$isWrapped ? "Ja" : "Nej");
                      $row++;
                      
                      $companyName = (trimgf($company->ship_to_company) != "" && $translateLanguageTo == 4) ? $company->ship_to_company : $company->name;
                       
                      $sameAddress = true;
                      if($company->name != $companyName) $sameAddress = false;
                      if(($company->bill_to_address.(trimgf($company->bill_to_address_2) == "" ? "" : ", ".$company->bill_to_address_2)) != ($company->ship_to_address.(trimgf($company->ship_to_address_2) == "" ? "" : ", ".$company->ship_to_address_2))) $sameAddress = false;
                      if($company->bill_to_postal_code != $company->ship_to_postal_code) $sameAddress = false;                                                                                                                                                         
                      if($company->bill_to_city != $company->ship_to_city) $sameAddress = false;
                    
                      if($sameAddress == false)
                      {
                        
                        $sheet->getStyle("A".$row.":Z".$row)->getFont()->setBold(true);
                        $sheet->setCellValueByColumnAndRow(1,$row,"Levering");
                        $sheet->setCellValueByColumnAndRow(2,$row,"Leveringsadresse");
                        $sheet->setCellValueByColumnAndRow(3,$row,"Levering postnr");
                        $sheet->setCellValueByColumnAndRow(4,$row,"Levering by");
                        $sheet->setCellValueByColumnAndRow(5,$row,"Kontakt Navn");
                        $sheet->setCellValueByColumnAndRow(6,$row,"Kontakt telefon");
                        $sheet->setCellValueByColumnAndRow(7,$row,"CVR");
                        //if($useWrapped) $sheet->setCellValueByColumnAndRow(9,$row,"Indpakket");
                        $row++;
                        
          
                        $sheet->setCellValueByColumnAndRow(1,$row,$companyName);
                        $sheet->setCellValueByColumnAndRow(2,$row,$company->ship_to_address.(trimgf($company->ship_to_address_2) == "" ? "" : ", ".$company->ship_to_address_2));
                        $sheet->setCellValueByColumnAndRow(3,$row,$company->ship_to_postal_code);
                        $sheet->getStyle('C'.$row)->getNumberFormat()->setFormatCode('0000');
                        $sheet->setCellValueByColumnAndRow(4,$row,$company->ship_to_city);
                        $sheet->setCellValueByColumnAndRow(5,$row,$company->contact_name);
                        $sheet->setCellValueByColumnAndRow(6,$row,$company->contact_phone);
                        $sheet->setCellValueByColumnAndRow(7,$row,$company->cvr);
                        //if($useWrapped) $sheet->setCellValueByColumnAndRow(9,$row,$isWrapped ? "Ja" : "Nej");
                        $row++;
                        
                      }
                      
                        $row++;  
                      
                      $noteCount = 0;
                      
                      // TODO : UPDATE DEALTEXT
                      if(isset($dealMap[$company->id]) && countgf($dealMap[$company->id]) > 0)
                      {
                        foreach($dealMap[$company->id] as $note)
                        {
                          $sheet->getStyle("B".$row.":F".$row)->getFont()->setBold(true);
                          $sheet->getStyle('B'.$row.':F'.$row)->applyFromArray($BackBlue);
                          $sheet->setCellValueByColumnAndRow(2,$row,$note);
                          $row++;
                          $noteCount++;
                        }
                      }
                      
                      if($noteCount > 0) $row++;
                      
                      $sheet->setCellValueByColumnAndRow(1,$row,"Gavenr.");
                      $sheet->setCellValueByColumnAndRow(2,$row,"Gavenavn");
                      $sheet->setCellValueByColumnAndRow(3,$row,"Gavemodel");
                      $sheet->setCellValueByColumnAndRow(4,$row,"Antal valgt");
                      $sheet->getStyle("A".$row.":H".$row)->getFont()->setBold(true);
                      $row++;
                        
                        $rowCount = 0;
                          
                      foreach($finalPresentList as $index => $present)
                      {
                        $count = 0;
                        if(isset($companyCountMap[$company->id]) && isset($companyCountMap[$company->id][$present["alias"]]))
                        {
                          $count = $companyCountMap[$company->id][$present["alias"]];
                        }
                        $rowCount += $count;
                        
                  
                         $sheet->setCellValueByColumnAndRow(1,$row,$present["alias"]);
                         $sheet->setCellValueByColumnAndRow(2,$row,$present["name"]);
                         $sheet->setCellValueByColumnAndRow(3,$row,$present["model"]);
                         $sheet->setCellValueByColumnAndRow(4,$row,$count);
                         $row++;
                      }
                      
                       $sheet->setCellValueByColumnAndRow(3,$row,"Total antal");
                         $sheet->setCellValueByColumnAndRow(4,$row,$rowCount);
                      
                       
                       $row++;
                 
                
                     $row++;
                     
                }
            }
            
            
            /** ENDGAVEVALG */
                        
          /*
          $row = 1;
          $col = 0;
          foreach($finalPresentList as $index => $present)
          {
              $sheet->setCellValueByColumnAndRow($index+1,$row,$present["alias"].": ".$present["name"]);
          }
          $sheet->setCellValueByColumnAndRow(count($finalPresentList)+1,$row,"Total");
          
          $row++;
          $sheetTotal = 0;
          $newAliasCountMap = array();
          
          foreach($companyList as $company)
          {
              if(isset($companyusers[$company->id]))
              {
                
                $sheet->setCellValueByColumnAndRow(1,$row,$company->name);
                $rowCount = 0;
                
                foreach($finalPresentList as $index => $present)
                {
                  $count = 0;
                  if(isset($companyCountMap[$company->id]) && isset($companyCountMap[$company->id][$present["alias"]]))
                  {
                    $count = $companyCountMap[$company->id][$present["alias"]];
                  }
                  $rowCount += $count;
                  $sheet->setCellValueByColumnAndRow($index+1,$row,$count);
                  if(!isset($newAliasCountMap[$present["alias"]])) $newAliasCountMap[$present["alias"]] = 0;
                  $newAliasCountMap[$present["alias"]] += $count;
                }    
                           
                $sheet->setCellValueByColumnAndRow(count($finalPresentList)+1,$row,$rowCount);
                $sheetTotal += $rowCount;
                              
                $row++;
            }
          }
          
          foreach($finalPresentList as $index => $present)
          {
              $sheet->setCellValueByColumnAndRow($index+1,$row,isset($newAliasCountMap[$present["alias"]]) ? $newAliasCountMap[$present["alias"]] : "0");
          }
          
          $sheet->setCellValueByColumnAndRow(count($finalPresentList)+1,$row,$sheetTotal);
          */  
            
            
            
                         
          // Add warning list
          if(count($this->plukWarningList) > 0)
          {
             $sheet = $phpExcel->createSheet();
              $sheet->setTitle("Warnings");
              for($i=0;$i<count($this->plukWarningList);$i++)
              {
                $sheet->setCellValueByColumnAndRow(2,$i+1,utf8_encode($this->plukWarningList[$i]));
              }
          }
          
          
            
                            
        
          ob_end_clean();
        
          // Output excel file
          header('Content-Type: application/vnd.ms-excel');
          header('Content-Disposition: attachment;filename="kortshop_'.$shopid.'_'.$expire.'.xlsx"');
          header('Cache-Control: max-age=0');
          $phpExcel->setActiveSheetIndex(1);
          $objWriter = new PHPExcel_Writer_Excel2007($phpExcel);
          $objWriter->save('php://output');
          exit();
          
        }
        
        
        
        

        //////// MAKE LABELS ///////////////////

        else if($action == "labels")
        {
                     
          // Init phpexcel        
          $phpExcel = new PHPExcel();  
          $phpExcel->removeSheetByIndex(0);
          
          // Write product list
          $sheet = $phpExcel->createSheet();
          
          $phpExcel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
         
          foreach($productList as $product)
          {
          
            $fullAlias = $valueAlias.(strlen(intval($product["alias"])) == 1 ? "0" : "").$product["alias"];
          
            // Find present name
            $presentName = "UKENDT NAVN";
            //if(isset($presentNameMap[$product["present_id"]])) $presentName = $presentNameMap[$product["present_id"]];
            if(isset($presentMap[$product["present_id"]])) $presentName = $presentMap[$product["present_id"]]->name;
            if(isset($overridePresentNames[$fullAlias])) $presentName = $overridePresentNames[$fullAlias];
            
            // Find model name
            $modelName = "UKENDT MODEL";
            if($product["model_id"] == 0) $modelName = "";
            else if(isset($presentModelMap[$product["model_id"]]))
            {
              $model = $presentModelMap[$product["model_id"]];    
              $modelNames = array();
              if(trimgf($model->model_name) != "") $modelNames[] = $model->model_name;
              if(trimgf($model->model_no) != "") $modelNames[] = $model->model_no;
              $modelName = implode(", ",$modelNames);
            }
            if(isset($overrideModelNames[$fullAlias])) $modelName = $overrideModelNames[$fullAlias];
            
            $aliasPresentMap[$product["alias"]] = $presentName;
            $aliasModelMap[$product["alias"]] = $modelName;
            $finalPresentList[] = array("name" => $presentName,"model" => $modelName,"alias" => $fullAlias);

          }
          
          // Write order list
          $sheet = $phpExcel->createSheet();
          $sheet->setTitle("Labelliste");
          
          $sheet->getColumnDimension('A')->setWidth(12);
          $sheet->getColumnDimension('B')->setWidth(42);
          $sheet->getColumnDimension('C')->setWidth(28.43);
          $sheet->getColumnDimension('D')->setWidth(22.71);
          $sheet->getColumnDimension('E')->setWidth(18.86);
          $sheet->getColumnDimension('F')->setWidth(8.43);
          $sheet->getColumnDimension('G')->setWidth(15);
          
          $row = 1;
          $userCount = 0;
        
          $companyAutogave = array();
          $companyCountMap = array();
          
          //$sheet->getStyle("A".$row.":Z".$row)->getFont()->setBold(true);
          $sheet->setCellValueByColumnAndRow(1,$row,"Gavenr");
          $sheet->setCellValueByColumnAndRow(2,$row,"Gave");
          $sheet->setCellValueByColumnAndRow(3,$row,"Farve / Variant");
          $sheet->setCellValueByColumnAndRow(4,$row,"Navn");
          $sheet->setCellValueByColumnAndRow(5,$row,"Firma");
          $sheet->setCellValueByColumnAndRow(6,$row,"Vej navn");
          $sheet->setCellValueByColumnAndRow(7,$row,"Gavekort nummer");
          if($useWrapped) $sheet->setCellValueByColumnAndRow(8,$row,"Indpakket");
          $row++;
          
          foreach($companyList as $company)
          {            
            
            if(isset($companyusers[$company->id]))
            {
            
                    $userList = array();
                    foreach($companyusers[$company->id] as $cu) $userList[] = $cu;

                    // Order by alias
                    for($i=0;$i<count($userList);$i++)
                    {
                        for($j=$i+1;$j<count($userList);$j++)
                        {
                            if(intval($userList[$i]["alias"]) > intval($userList[$j]["alias"]) || (intval($userList[$i]["alias"]) == intval($userList[$j]["alias"]) && (strcmp ( $userList[$i]["alias"],$userList[$j]["alias"] ) > 0)))
                            {
                                $tmp = $userList[$i];
                                $userList[$i] = $userList[$j];
                                $userList[$j] = $tmp;
                            }
                        }
                    }

                  
                            // Output orders
                            foreach($userList as $cu)
                            {
                                $giftCertNo = isset($cu["order"]) ? $cu["order"]->gift_certificate_no : $cu["shopuser"]->username;
                                /*
                                $inCerts = false;
                                foreach($address["certs"] as $cert)
                                {
                                    if($cert["start"] <= $giftCertNo && $cert["end"] >= $giftCertNo)
                                    {
                                        $inCerts = true;
                                    }
                                }
                                */
                                
                                if(true)
                                {
                                    $alias = $valueAlias.(strlen(intval(trimgf($cu["alias"]))) == 1 ? "0" : "").$cu["alias"];
                                    
                                     $presentName = isset($cu["order"]) && intval($cu["alias"]) > 0  ? (isset($aliasPresentMap[$cu["alias"]]) ? $aliasPresentMap[$cu["alias"]] : "Ukendt gave") : $autovalgName;
                                    $modelName = isset($cu["order"]) && intval($cu["alias"]) > 0 ? (isset($aliasModelMap[$cu["alias"]]) ? $aliasModelMap[$cu["alias"]] : "Ukendt model") : "";
                                     
                  									$sheet->setCellValueByColumnAndRow(1,$row,$alias);
                  									$sheet->setCellValueByColumnAndRow(2,$row,$presentName);
                  									$sheet->setCellValueByColumnAndRow(3,$row,$modelName);
                  									$sheet->setCellValueByColumnAndRow(4,$row,isset($cu["order"]) ? $cu["order"]->user_name : "Gavekort ".$cu["shopuser"]->username);
                  									$sheet->setCellValueByColumnAndRow(5,$row,$company->name);
                  									$sheet->setCellValueByColumnAndRow(6,$row,$company->ship_to_address.(trimgf($company->ship_to_address_2) == "" ? "" : ", ".$company->ship_to_address_2));
                  									$sheet->setCellValueByColumnAndRow(7,$row,isset($cu["order"]) ? $cu["order"]->gift_certificate_no : $cu["shopuser"]->username);
                  									//$sheet->setCellValueByColumnAndRow(8,$row,$cu["shopuser"]->expire_date->format("Y-m-d"));
                  									if($useWrapped) $sheet->setCellValueByColumnAndRow(8,$row,$isWrapped ? "Ja" : "Nej");
                  									$row++;
                                }

                            }

                        }
						
						            $row++;
              
          }
                    
        
          ob_end_clean();
        
          // Output excel file
          header('Content-Type: application/vnd.ms-excel');
          header('Content-Disposition: attachment;filename="kortshop_labels_'.$shopid.'_'.$expire.'.xlsx"');
          header('Cache-Control: max-age=0');
          $phpExcel->setActiveSheetIndex(1);
          $objWriter = new PHPExcel_Writer_Excel2007($phpExcel);
          $objWriter->save('php://output');
          exit();
          
        }
        /*
        else if($action == "labels")
        {

            $contentCompanyList = array();

            foreach($companyList as $company)
            {
                $companyLines = array();
                if(isset($companyusers[$company->id]))
                {
                    //$cvsContent[] = array("Firmanavn","Gavekortnr","Gave ID","Gavenr.","Gave","Gave model","Navn","E-mail","Deadline");
                    foreach($companyusers[$company->id] as $shopuser)
                    {
                        if(isset($shopuser["order"]))
                        {
                            $companyname = $company->name;
                            $username = $shopuser["shopuser"]->username;
                            $presentid = $shopuser["order"]->present_id;
                            $present = explode("###",$shopuser["order"]->present_model_name);
                            $presentname = $shopuser["order"]->present_name;
                            $presentmodel = isset($present[1]) ? $present[1] : "";
                            $name = $shopuser["order"]->user_name;
                            $email = $shopuser["order"]->user_email;
                            $deadline = $shopuser["shopuser"]->expire_date->format("Y-m-d");

                            $modelkey = strtolower($presentname."###".$presentmodel);
                            $presentNumber = array_search($modelkey,$modelIndex);
                            if($presentNumber === false)
                            {
                                echo "Fejl i gavenr, kunne ikke finde model nøgle: ".$modelkey;
                                exit();
                            }

                            $languageDescription = "";
                            $present_id = $shopuser["order"]->present_id;
                            $language_id = $shopuser["order"]->language_id;
                            if(isset($languageTexts[$language_id]) && isset($languageTexts[$language_id][$present_id])) $languageDescription = $languageTexts[$language_id][$present_id];
                            else { $languageDescription = $presentname; }

                            //$cvsContent[] = array($companyname,$username,$presentid,($presentNumber+1).$presentLetter,$presentname,$presentmodel,$name,$email,$deadline);
                            $companyLines[] = array(($presentNumber+1).$presentLetter,$languageDescription,$presentmodel,$name,$companyname,$username,$presentname);

                        }
                    }

                    foreach($companyusers[$company->id] as $shopuser)
                    {
                        if(!isset($shopuser["order"]))
                        {
                            $companyname = $company->name;
                            $username = $shopuser["shopuser"]->username;
                            $presentid = 0;
                            $presentname = "Autovalg gaveæske";
                            $presentmodel = "";
                            $name = "";
                            $email = "";
                            $deadline = $shopuser["shopuser"]->expire_date->format("Y-m-d");

                            //$cvsContent[] = array($companyname,$username,$presentid,0,$presentname,$presentmodel,$name,$email,$deadline);
                            $companyLines[] = array(0,"Autovalg gaveæske","",$name,$companyname,$username,$presentname);
                        }
                    }

                }

                if(count($companyLines) > 0) $contentCompanyList[] = $companyLines;

            }

            $outputContent = "";
            $outputLineCount = 0;


            foreach($contentCompanyList as $companyRows)
            {

                // Order rows
                for($i=0;$i<count($companyRows);$i++)
                {
                    for($j=$i+1;$j<count($companyRows);$j++)
                    {

                        if(intval($companyRows[$i][0]) > intval($companyRows[$j][0]))
                        {
                            $tmp = $companyRows[$i];
                            $companyRows[$i] = $companyRows[$j];
                            $companyRows[$j] = $tmp;
                        }
                    }
                }

                // Output rows
                foreach($companyRows as $row)
                {
                    $outputContent .= implode(";",$row)."\n";
                    $outputLineCount++;
                }

                $outputContent .= ";;;;;;;;\n";
            }

            header('Content-Type: application/octet-stream');
            header("Content-Transfer-Encoding: Binary");
            header("Content-disposition: attachment; filename=\"labels_".$shopid."-".date("Y-m-d",$expire).".csv\"");
            echo utf8_decode($outputContent);


            exit();

        }
              */
        //////// CHECK PLUKLISTE ///////////
        else if($action == "check")
        {

// Start output
?><html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { margin: 50px; padding: 0px; font-size: 14px; font-family: verdana; }
        td { font-size: 0.8em; padding: 5px; text-align: right; }
    </style>
</head>
<body>
<h2>Plukliste - tjek</h2>
Shop id: <?php echo $shopid; ?>, deadline: <?php echo date("Y-m-d",$expire); ?><br><br>


<table>
    <tr>
        <td>Firma</td>
        <td>Gavekort</td>
        <?php /*<td>Ordre</td> */ ?>
        <td>Ikke valgt</td>
        <td>Antal gavekort udenfor<br> denne periode</td>
        <td>Antal ordre uden for<br> denne periode</td>
        <td>Gavekort med mere end 1 ordre tilknyttet</td>
    </tr><?php

    $withoutOrderTotal = 0;

        foreach($companyList as $company)
        {
            if(isset($companyusers[$company->id]))
            {

                $shopuser_count = isset($companyusers[$company->id]) ? countgf($companyusers[$company->id]) : 0;
                $order_count = isset($companyOrderCount[$company->id]) ? $companyOrderCount[$company->id] : 0;
                $shopuser_withorder = 0;
                $shopuser_withoutorder = 0;

                foreach($companyusers[$company->id] as $shopuser)
                {
                    if(isset($shopuser["order"])) $shopuser_withorder++;
                    else $shopuser_withoutorder++;
                }

                $giftcardsOutsidePeriod = isset($shopuserNegativeMap[$company->id]) ? $shopuserNegativeMap[$company->id] : 0;
                $multiOrders = isset($companyMultiOrders[$company->id]) ? $companyMultiOrders[$company->id] : 0;
                $ordersOutsidePeriod = isset($companyNoMatchOrders[$company->id]) ? $companyNoMatchOrders[$company->id] : 0;


                $withoutOrderTotal += $shopuser_withoutorder;

                ?><tr>
                    <td><?php echo $company->name; ?></td>
                    <td><?php echo $shopuser_count; ?></td>
                    <?php /*<td style="<?php if($order_count > $shopuser_count) echo "background: red;"; ?>"><?php echo $order_count; ?></td> */ ?>
                    <td><?php echo $shopuser_withoutorder; ?></td>
                    <td style="<?php if($giftcardsOutsidePeriod > 0) echo "background: yellow;"; ?>"><?php echo $giftcardsOutsidePeriod; ?></td>
                    <td style="<?php if($ordersOutsidePeriod > 0) echo "background: yellow;"; ?>"><?php echo $ordersOutsidePeriod; ?></td>
                    <td style="<?php if($multiOrders > 0) echo "background: red;"; ?>"><?php echo $multiOrders; ?></td>
                </tr><?php

            }
        }
/*
        // Fetch
        echo "LETS DO IT: ".$shopid." / ".$expire;

        echo "<pre>".print_r($companyNoMatchOrders)."</pre>";
        echo "<pre>".print_r($companyMultiOrders)."</pre>";
*/
?></table>
Antal uden gavevalg: <?php echo $withoutOrderTotal; ?>
</body>
</html><?php


        }
             
        }
        catch(Exception $e)
        {
          echo "Fejl under udtræk: ";
          echo "<pre>".print_r($e,true)."</pre>";
          exit();
        }
             
        
    }

    /********************************************
     * LOGIN / LOGOUT VIEW AND LOGIC
     ********************************************/

    public function OldIndex()
    {
        $this->init(false);
        if($this->isLoggedIn)
        {
            $this->dashboard();
            exit();
        }

?><html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
            body { margin: 50px; padding: 0px; font-size: 14px; font-family: verdana; }
        </style>
    </head>
    <body>
        <h2>Log ind i gave udleverings modulet</h2>
        <form method="post" action="<?php echo $this->getUrl("login"); ?>/login">
            <div style="padding: 15px;">
                <b>Brugernavn</b><br>
                <input type="text" size="20" name="gum_username">
            </div>
            <div style="padding: 15px;">
                <b>Adgangskode</b><br>
                <input type="password" size="20" name="gum_password">
            </div>
            <?php if($this->error != "") echo "<div style='text-align: center; color: red;'>".$this->error."</div>"; ?>
            <div style="padding: 15px;">
                <input type="submit" value="Log ind">
            </div>
        </form>
    </body>
</html><?php
    }

    public function login()
    {

        $this->init(false);

        $username = isset($_POST["gum_username"]) ? $_POST["gum_username"] : "";
        $password = isset($_POST["gum_password"]) ? $_POST["gum_password"] : "";

        // Find company
        $companyList = Company::find("all",array('conditions' => array('username' => trimgf($username), 'password' => trimgf($password))));
        if(count($companyList) == 0)
        {
            $this->error = "Forkert brugernavn eller adgangskode.";
            return $this->index();
        }
        $company = $companyList[0];

        // Find shops
        $shoplinklist = CompanyShop::find('all',array("conditions" => array("company_id" => $company->id)));
        if(count($shoplinklist) == 0)
        {
            $this->error = "Ingen shops tilknyttet din bruger";
            return $this->index();
        }

        // Find shop id
        $shopid = intval($shoplinklist[0]->shop_id);
        if($shopid <= 0)
        {
            $this->error = "Kunne ikke finde shop tilknyttet din bruger.";
            return $this->index();
        }

        // Set session data
        $_SESSION["gum_authenticated"] = true;
        $_SESSION["gum_companyid"] = $company->id;
        $_SESSION["gum_shopid"] = $shopid;

        // Send user to dashboard
        header("Location: ".$this->getUrl("dashboard"));

    }

    public function logout()
    {
        $this->init(true);
        $_SESSION["gum_authenticated"] = false;
        $_SESSION["gum_companyid"] = 0;
        $_SESSION["gum_shopid"] = 0;
        $this->isLoggedIn = false;
        $this->index();
    }

    /**** PRIVATE LOGIN / INIT LOGIC ****/

    private $isLoggedIn = false;
    private $companyid = 0;
    private $shopid = array();

    /** @var  Shop */
    private $shopObject;

    /**
     * @param $needsLogin If the user needs to be logged in
     */

    private function init($needsLogin)
    {
        if (session_status() == PHP_SESSION_NONE)
        {
            ini_set("session.gc_maxlifetime",8640);
            session_set_cookie_params(12*60*60);
            session_start();
        }
        $this->isLoggedIn = false;

        if(isset($_SESSION["gum_authenticated"]) && $_SESSION["gum_authenticated"] === true && isset($_SESSION["gum_companyid"]) && $_SESSION["gum_companyid"] > 0 && isset($_SESSION["gum_shopid"]) && countgf($_SESSION["gum_shopid"]) > 0)
        {
            $this->isLoggedIn = true;
            $this->companyid = intval($_SESSION["gum_companyid"]);
            $this->shopid = intval($_SESSION["gum_shopid"]);

            // Load shop
            if($this->shopObject == null || $this->shopObject->id != $this->shopid)
            {
                $this->shopObject = Shop::find($this->shopid);
            }

        }

        if($needsLogin == true && !$this->isLoggedIn)
        {
            $this->error = "Du skal være logget ind for at udføre denne handling.";
            $this->index();
            exit();
        }

    }

    /********************************************
     * FUNCTIONALITY THAT REQUIRES LOGIN
     ********************************************/

    public function dashboard()
    {
        $this->init(true);
        $this->authTop();

        ?>
        <div style="padding: 20px;">
            <h2>Gaveregistrering</h2>
            <p>
                <p>Du kan her registrere udlevering af gaver.</p>
                <p>Sørg for at udlevering er aktiveret. Du ser og skifter status øverst i højre hjørne</p>
                <ul>
                    <li>Søg efter navn eller e-mail i søgefeltet for at finde en person.<br></li>
                    <li>Scan en QR kode for at få vist en kvittering direkte.<br></li>
                </ul>
            </p>
        </div>

        <?php


        $this->authBottom();
    }

    public function search()
    {
        $this->init(true);
        $this->authTop();

        // Find query string
        $query = isset($_POST["query"]) ? trimgf($_POST["query"]) : "";
        if($query == "")
        {
            $this->dashboard();
            exit();
        }

        // Find shop attributes
        $searchAttributes = ShopAttribute::find_by_sql("SELECT * FROM shop_attribute WHERE shop_id = ".intval($this->shopid)." && (is_name = 1 || is_email = 1)");
        $attributeidlist = array();
        $nameAttributeid = 0;
        $emailAttributeid = 0;

        foreach($searchAttributes as $sa)
        {
            if($sa->is_name == 1) $nameAttributeid = $sa->id;
            if($sa->is_email == 1) $emailAttributeid = $sa->id;
            $attributeidlist[] = $sa->id;
        }

        // Find users by attributes
        $sql = "SELECT * FROM shop_user WHERE shop_id = ".intval($this->shopid)." && id IN (SELECT shopuser_id FROM user_attribute WHERE shop_id = ".intval($this->shopid)." && attribute_value LIKE :Query && attribute_id IN (".implode(",",$attributeidlist)."))";
        $userlist = ShopUser::find_by_sql($sql,array(":Query" => "%".trimgf($query)."%"));

        // Output results
        ?><div style="padding: 20px;">
            Søgning efter <b><?php echo $query; ?></b> gav <?php echo countgf($userlist); ?> resultat<?php if(count($userlist) > 1) echo "er"; ?>
        </div><?php

        ?><table style="width: 100%;" cellpadding="0" cellspacing="0">

            <tr>
                <th>Person</th>
                <th style="text-align: center;">Gavevalg</th>
                <th>&nbsp;</th>
            </tr>
            <?php

        /**
         * @var $user ShopUser
         */
        foreach($userlist as $index => $user)
        {
            if($index == 100) break;

            $name = "Ukendt navn";
            $email = "Ukendt e-mail";

            $attr = $user->attributes();
            foreach($attr["user_attributes"] as $ua)
            {
                if($ua->attribute_id == $nameAttributeid) $name = $ua->attribute_value;
                if($ua->attribute_id == $emailAttributeid) $email = $ua->attribute_value;
            }


            echo "<tr>
                <td><b>".$name."</b><br>".$email."</td>
                <td style='text-align: center;'>";

            if($user->has_orders()) {
                echo "<div style='padding: 3px; '>".($user->order()[0]->registered == 0 ? "Ikke udleveret" : "Udleveret") . "</div>
                <input type='button' onClick='document.location=\"".$this->getUrl("register&orderno=".$user->order()[0]->order_no)."\"' value='Vis ordrenr " . $user->order()[0]->order_no . "'>";
            }
            else
            {
                echo "Ingen ordre / gavevalg";
            }

                echo "</td>
            </tr>";
        }

        ?></table><?php

        $this->authBottom();
    }

    public function register()
    {
        $this->init(false);

        if(!$this->isLoggedIn)
        {
            echo '<html>';
            echo '<head><style>body { font-family: Arial, Helvetica Neue, Helvetica, sans-serif; font-size:20px; line-height: 2; </style>';
            echo '</head>';
            echo '<body>';
            echo '<center><div style="font-size: 6vw;">Du er ikke logget ind.</div><br><a href="'.$this->getUrl("index").'">klik her for at logge ind</a><hr />';
            echo '</body></html>';
            exit();
        }

        $this->authTop();

        $orderno = isset($_REQUEST['orderno']) ? $_REQUEST['orderno'] : "";
        $Order  =  Order::find_by_order_no($orderno);

        if($Order && $Order->shop_id == $this->shopid)
        {

            $present = present::find($Order->present_id);

            echo '<div style="font-size: 24px; padding: 10px;">Gaveregistrering</div>' ;
            echo '<div style="text-align: center;"><img src="../../gavefabrikken_backend/views/media/user/'.$present->present_media[0]->media_path.'.jpg" alt="" style="max-width: 100%; height: 40vh; max-height: 800px;" /></div><br>';

            if($Order->registered==0)
            {
                if($Order && $this->shopObject->open_for_registration) {
                    echo '<div style="padding: 15px; text-align: center;">
                        <input style="width:90%;height:10vh;font-size: 18px;" type="button" onclick="location.href=\''.$this->getUrl("doregister&orderno=".$orderno).'\';" value="Registrer udlevering" />
                    </div>';
                }
                else
                {
                    echo "<div style='padding: 10px; text-align: center;'><b>Ikke udleveret</b><br>Gaveudleveringen er lukket, aktiver gaveudlevering for at registrere udlevering!</div>";
                }
            }
            else
            {
                echo "<div style='padding: 10px; text-align: center;'>
                    Ordrenr. ".$orderno.' er udleveret d.'.$Order->registered_date->format('d-m-Y')."<br>
                </div>";

                if($Order && $this->shopObject->open_for_registration) {
                    ?><div style='padding: 10px; text-align: center;'><a href='javascript:cancelRegister();'>Træk udlevering tilbage</a> </div>
                    <form id="unregisterform" method="post" action="<?php echo $this->getUrl("dounregister&orderno=" . $orderno); ?>"><input type="hidden" name="action" value="unregister"></form>
                    <script>
                        function cancelRegister() {
                            if (confirm('Er du sikker på at du vil trække udleveringen tilbage?')) {
                                $('#unregisterform').submit();
                            }
                        }
                    </script><?php
                }
            }

            if($Order->user_name !="") echo '<table width=90%  style="font-size: 18px;"><tr><td width=25%>Navn:</td><td width=75%>'.$Order->user_name.'</td></tr>';
            if($Order->present_model_name!="") echo '<tr><td><label>Model:</label></td><td>'.str_replace("###"," - ",$Order->present_model_name).'</td></tr>';
            echo '<tr><td>Email:</td><td>'.$Order->user_email.'</td></tr>';
            echo '<tr><td>Gave:</td><td>'.$Order->present_name.'</td></tr></table>';



        }
        else {
            echo '<div style="font-size: 24px; text-align: center; padding: 20px; ">Ordrenr. '.$orderno.' blev ikke fundet!</div><br><hr />' ;
        }

        $this->authBottom(true);
        //$orderno = trimgf($_GET["orderno"]);
    }

    public function doregister()
    {
        // Init and check login
        $this->init(false);
        if(!$this->isLoggedIn) return $this->register();

        // Check that shop is open
        if(!$this->shopObject->open_for_registration)
        {
            $this->error = "Der er ikke åben for udlevering. Aktiver udlevering!";
            return $this->register();
        }

        // Find order number and order
        $orderno = isset($_REQUEST['orderno']) ? $_REQUEST['orderno'] : "";
        $Order  =  Order::find_by_order_no($orderno);

        // Check order and shop
        if($Order && $Order->shop_id == $this->shopid)
        {
            if($Order->registered == 1)
            {
                $this->error = "Denne gave er allerede registreret som udleveret.";
                return $this->register();
            }
            else
            {
                $Order->registered = 1;
                $Order->registered_date = date('d-m-Y H:i:s');
                $Order->save();
                System::connection()->commit();
                header("Location: ".$this->getUrl("register&orderno=".$orderno));
            }
        }
        // Error in order or shopp
        else
        {
            return $this->register();
        }
    }

    public function dounregister()
    {

        // Init and check login
        $this->init(false);
        if(!$this->isLoggedIn) return $this->register();

        // Check that shop is open
        if(!$this->shopObject->open_for_registration)
        {
            $this->error = "Der er ikke åben for udlevering. Aktiver udlevering!";
            return $this->register();
        }

        // Find order number and order
        $orderno = isset($_REQUEST['orderno']) ? $_REQUEST['orderno'] : "";
        $Order  =  Order::find_by_order_no($orderno);

        // Check order and shop
        if($Order && $Order->shop_id == $this->shopid)
        {
            // Action not set, dismiss action
            if($_POST["action"] != "unregister")
            {
                return $this->register();
            }
            // If not already registered, dismiss
            else if($Order->registered == 0)
            {
                $this->error = "Denne gave er ikke registreret som udleveret.";
                return $this->register();
            }
            // Perform unregister action
            else
            {
                $Order->registered = 0;
                $Order->registered_date = null;
                $Order->save();
                System::connection()->commit();
                header("Location: ".$this->getUrl("register&orderno=".$orderno));
            }
        }
        // Error in shop or order
        else
        {
            return $this->register();
        }
    }

    /*************************************
     * TEMPLATE FUNCTIONALITY
     *************************************/

    private function authTop()
    {



        ?><html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
            body { margin: 0px; padding: 0px; font-size: 14px; font-family: verdana; overflow-x: hidden; }
            form { margin: 0px; padding: 0px; }

            table { font-size: 0.9em; }
            th { font-weight: bold; padding: 5px; text-align: left; border-bottom: 1px solid #555; }
            td { padding: 5px; border-bottom: 1px solid #aaa; }

            .regbtnstart { border: none; border-radius: 3px; display: inline-block; margin-top: 5px; padding: 6px; background: forestgreen; font-size: 0.8em; font-weight: bold; cursor: pointer; color: white; }
            .regbtnstop { border: none; border-radius: 3px; display: inline-block; margin-top: 5px; padding: 6px; background: red; font-size: 0.8em; font-weight: bold; cursor: pointer; color: white; }

        </style>
        <script src="<?php echo GFConfig::BACKEND_URL; ?>views/lib/jquery.min.js"></script>
    </head>
    <body>

    <div style="background: #acd6ef; border-bottom: 1px solid #333;">

        <div style="float: right; padding: 4px;">
            <a href="<?php echo $this->getUrl("dashboard"); ?>">Forside</a> |
            <a href="<?php echo $this->getUrl("logout"); ?>">Log ud</a><br>
                <button type="button" class="regbtnstart" onClick="setRegistrationOpen()" <?php if($this->shopObject->open_for_registration == 1) echo "style=\"display: none;\""; ?>>Aktiver udlevering</button>
                <button type="button" class="regbtnstop" onClick="setRegistrationClosed()" <?php if($this->shopObject->open_for_registration == 0) echo "style=\"display: none;\""; ?>>Stop udlevering</button>
        </div>

        <div style="padding: 10px;">
            <form action="<?php echo $this->getUrl("search"); ?>" method="post">
                Søg efter navn / e-mail:<br>
                <input type="text" name="query" size="20" value="<?php echo isset($_POST["query"]) ? $_POST["query"] : ""; ?>">
                <input type="submit" value="søg">
            </form>
        </div>

    </div>
    <?php
    }

    private function authBottom($reloadOnStateChange=false)
    {
        ?>

        <script>

            function setRegistrationOpen()
            {
                $.post( '<?php echo GFConfig::BACKEND_URL; ?>index.php?rt=shop/openForRegistration',{"shop_id":<?php echo $this->shopid; ?>},function() {
                    $('.regbtnstart').hide();
                    $('.regbtnstop').show();
                    <?php if($reloadOnStateChange) echo "location.reload();"; ?>
                });
            }

            function setRegistrationClosed()
            {
                $.post( '<?php echo GFConfig::BACKEND_URL; ?>index.php?rt=shop/closeForRegistration',{"shop_id":<?php echo $this->shopid; ?>},function() {
                    $('.regbtnstart').show();
                    $('.regbtnstop').hide();
                    <?php if($reloadOnStateChange) echo "location.reload();"; ?>
                });
            }

        </script>


        </body>
        </html><?php
    }

    /**
     * CHECK CSV FILES
     */

    public function checkcsv()
    {
        //error_reporting( error_reporting() & ~E_NOTICE );
        error_reporting(E_ALL ^ E_NOTICE);

        $inputFile = "files/NY JGK 48 LISTE-labels.csv";
        $inputContent = file_get_contents($inputFile);
        $inputLine = explode("\n",$inputContent);
        $lines = array();

        $errorMessages = array();
        $inFile = array();

        $companyInFileCount = array();
        $companyInDbCount = array();
        $companyDiffCount = array();
        $deadlineList = array();

        $companyNotInFile = array();

        $totalInFile = 0;

        // Parse input content
        foreach($inputLine as $line)
        {
            $linedata = explode(";",$line);
            if(isset($linedata[5]) && intval($linedata[5]) > 0)
            {

                // Get order from shopuser
                $shopUserList = ShopUser::find("all",array('conditions' => array('username' => trimgf($linedata[5]))));

                // No gavekort
                if(count($shopUserList) == 0)
                {
                    $errorMessages[] = "Warning: Gavekort nr. ".$linedata[5]." ikke fundet (virksomhed: ".$linedata[4].")";
                }
                // Has gavekort
                else
                {
                    $totalInFile++;
                    $shopUser = $shopUserList[0];
                    if(!isset($inFile[$shopUser->shop_id])) $inFile[$shopUser->shop_id] = array();
                    if(!isset($inFile[$shopUser->shop_id][$shopUser->company_id])) $inFile[$shopUser->shop_id][$shopUser->company_id] = array();
                    $inFile[$shopUser->shop_id][$shopUser->company_id][$shopUser->id] = $shopUser;

                    $deadline = $shopUser->expire_date->format("Y-m-d");
                    @$deadlineList[$deadline] = true;
                    @$companyInFileCount[$shopUser->company_id][$deadline]++;
                    @$companyInFileCount[$shopUser->company_id]["total"]++;

                    // Check order
                    $orderList = Order::find('all',array('conditions' => array('shopuser_id' => $shopUser->id)));
                    if(count($orderList) == 0 && intval($linedata[0]) > 0)
                    {
                        $errorMessages[] = "Warning: Gave valgt i filen, er ikke valgt i systemet. Gavekort nr. ".$linedata[5]." ikke fundet (virksomhed: ".$linedata[4].")";
                    }

                }
            }
        }

        foreach($inFile as $shopid => $companylist)
        {
            foreach($companylist as $companyid => $shopusers)
            {
                    // blocked = 0 && is_demo = 0
                $shopUserList = ShopUser::find("all",array('conditions' => array("blocked" => 0, "is_demo" => 0,'company_id' => intval($companyid),"shop_id" => intval($shopid))));

                foreach($shopUserList as $companyUser)
                {
                    $deadline = $companyUser->expire_date->format("Y-m-d");
                    @$deadlineList[$deadline] = true;
                    @$companyInDbCount[$companyUser->company_id][$deadline]++;
                    @$companyInDbCount[$companyUser->company_id]["total"]++;

                    if(!isset($inFile[$companyUser->shop_id][$companyUser->company_id][$companyUser->id]))
                    {
                        @$companyDiffCount[$companyUser->company_id][$deadline]++;
                        @$companyDiffCount[$companyUser->company_id]["total"]++;
                        if($deadline == "2016-11-06") @$companyNotInFile[$companyid][] = $companyUser->username;
                    }
                }

            }
        }

        // PRINT LIST
        ?><table>
            <tr>
                <td>Virksomhed</td>
                <?php foreach($deadlineList as $deadline => $val) echo "<td style='width: 100px;'>".$deadline."<br>i fil / i db</td>"; ?>
                <?php /* <td>Total<br>i fil / i db</td> */ ?>
                <td style="width: 300px;">Gavekort ikke i filen</td>
            </tr>
        <?php

        // Load companies into map
        $companyList = Company::find('all');
        $companyMap = array();
        foreach($companyList as $company) $companyMap[$company->id] = $company;

        foreach($inFile as $shopid => $companylist)
        {
            foreach ($companylist as $companyid => $shopusers)
            {

                echo "<tr><td>".$companyMap[$companyid]->name."</td>";

                foreach($deadlineList as $deadline => $val)
                {
                    echo "<td>".intval(@$companyInFileCount[$companyid][$deadline])." / ".intval(@$companyInDbCount[$companyid][$deadline])."</td>";
                }

                echo "<td>";
                $notInFile = @$companyNotInFile[$companyid];
                if(count($notInFile) > 0) echo implode(", ",$notInFile);
                else echo " ";
                echo "</td>";

                //echo "<td>".intval(@$companyInFileCount[$companyid]["total"])." / ".@$companyInDbCount[$companyid]["total"]."</td>";
                echo "</tr>";

            }
        }

        ?>
        </table><?php

        echo "Total antal gavekort i filen: ".$totalInFile."<br>";
        if(count($errorMessages) > 0)
        {
            echo "Fejlbeskeder<br>";
            foreach($errorMessages as $error) echo " - ".$error."<br>";
        }

    }

    /**
     * REFORMAT GIFT LISTS TO UPDATE PRESENT MODEL AND OTHER DATA
     */

    public function csvfix()
    {

        $batch = array(
/*
            array("inputfile" => "24gaver-400-u50-labels.csv", "output" => "24gaver-400-u50-labels-fixed.csv","reglagen" => true),
            array("inputfile" => "24gaver-560-u50-labels.csv", "output" => "24gaver-560-u50-labels-fixed.csv","reglagen" => false),
            array("inputfile" => "24gaver-640-u50-labels.csv", "output" => "24gaver-640-u50-labels-fixed.csv","reglagen" => false),
            array("inputfile" => "guld-u50-labels.csv", "output" => "guld-u50-labels-fixed.csv","reglagen" => false),
            array("inputfile" => "jgk-u50-labels.csv", "output" => "jgk-u50-labels-fixed.csv","reglagen" => false),
            array("inputfile" => "no-jgk-400-u50-labels.csv", "output" => "no-jgk-400-u50-labels-fixed.csv","reglagen" => false),
            array("inputfile" => "no-jgk-600-u50-labels.csv", "output" => "no-jgk-600-u50-labels-fixed.csv","reglagen" => false),
            array("inputfile" => "no-jgk-800-u50-labels.csv", "output" => "no-jgk-800-u50-labels-fixed.csv","reglagen" => false),
*/
            //array("inputfile" => "jgk-u51-labels.csv", "output" => "jgk-u51-labels-fixed.csv","reglagen" => false)

            /*
            array("inputfile" => "24gaver-400-u1-labels.csv", "output" => "24gaver-400-u1-labels-fixed.csv","reglagen" => true),
            array("inputfile" => "24gaver-560-u1-labels.csv", "output" => "24gaver-560-u1-labels-fixed.csv","reglagen" => false),
            array("inputfile" => "24gaver-640-u1-labels.csv", "output" => "24gaver-640-u1-labels-fixed.csv","reglagen" => false),
            array("inputfile" => "guld-u1-labels.csv", "output" => "guld-u1-labels-fixed.csv","reglagen" => false),
            array("inputfile" => "jgk-u1-labels.csv", "output" => "jgk-u1-labels-fixed.csv","reglagen" => false),
*/

//            array("inputfile" => "no-jgk-400-u1-labels.csv", "output" => "no-jgk-400-u1-labels-fixed.csv","reglagen" => false),
//            array("inputfile" => "no-jgk-600-u1-labels.csv", "output" => "no-jgk-600-u1-labels-fixed.csv","reglagen" => false),
            array("inputfile" => "no-jgk-800-u1-labels.csv", "output" => "no-jgk-800-u1-labels-fixed.csv","reglagen" => false),

        );

        foreach($batch as $batchitem)
        {

            // Load input
            $inputFile = "files/".$batchitem["inputfile"];

            echo "<br><br>Processing input file: ".$inputFile."<br>";

            if(!file_exists($inputFile))
            {
                echo "Could not find input file: ".$inputFile;
                exit();
            }

            $inputContent = file_get_contents($inputFile);
            $inputLine = explode("\n",$inputContent);
            $lines = array();
            $nextNumber = 0;

            // Parse input content
            foreach($inputLine as $line)
            {
                $linedata = explode(";",$line);
                $lines[] = $linedata;
                if($nextNumber < intval($linedata[0]))
                    $nextNumber = intval($linedata[0]);
            }

            $nextNumber++;
            $newNumberMap = array();

            foreach($lines as $key => $line)
            {


                if($batchitem["reglagen"] && isset($line[5]) && trimgf($line[5]) != "")
                {
                    $giftcardnumber = $line[5];
                    if($line[0] == "8c" || $line[0] == "4c")
                    {

                        $shopUserList = ShopUser::find("all",array('conditions' => array('username' => trimgf($giftcardnumber))));
                        if(count($shopUserList) == 0)
                        {
                            echo "Shopuser not found: username = ".$giftcardnumber;
                            exit();
                        }
                        $shopUser = $shopUserList[0];

                        $orderList = Order::find('all',array('conditions' => array('shopuser_id' => $shopUser->id)));
                        if(count($orderList) == 0)
                        {
                            echo "Order not found: username = ".$giftcardnumber."<br>";
                        }

                        $order = $orderList[0];
                        $modelNameSplit = explode("###",$order->present_model_name);
                        $subSplit = explode(" ",trimgf($modelNameSplit[0]));

                        $newModelName = utf8_decode($subSplit[countgf($subSplit)-1]." - ".$modelNameSplit[1]);
                        $newNumber = 0;

                        if(!isset($newNumberMap[$newModelName]))
                        {
                            $newNumber = $nextNumber;
                            $newNumberMap[$newModelName] = $nextNumber;
                            echo $line[0]." renamed to ".$newNumber."c - ".$newModelName."<br>";
                            $nextNumber++;
                        }
                        else
                        {
                            $newNumber = $newNumberMap[$newModelName];
                        }


                        $lines[$key][2] = $newModelName;
                        $lines[$key][0] = $newNumber."c";

                    }
                }

                if(intval($line[0]) > 0 && isset($line[5]) && trimgf($line[5]) != "")
                {

                    $giftcardnumber = $line[5];
                    $shopUserList = ShopUser::find("all",array('conditions' => array('username' => trimgf($giftcardnumber))));
                    if(count($shopUserList) == 0)
                    {
                        echo "Shopuser not found: username = ".$giftcardnumber;
                        exit();
                    }
                    $shopUser = $shopUserList[0];

                    $orderList = Order::find('all',array('conditions' => array('shopuser_id' => $shopUser->id)));
                    if(count($orderList) == 0)
                    {
                        echo "Order not found: username = ".$giftcardnumber."<br>";
                    }
                    else
                    {

                        $order = $orderList[0];

                        $present_id = $order->present_id;
                        $language_id = $order->language_id;

                        $presentDescriptionList = PresentDescription::find('all',array('conditions' => array('present_id' => intval($present_id),'language_id' => intval($language_id))));
                        if(count($presentDescriptionList) == 0)
                        {
                            echo "Present description not found for gift: ".$giftcardnumber." (".$present_id." / ".$language_id.")";
                            exit();
                        }

                        $presentDescription = $presentDescriptionList[0];
                        $translatedDescription = $presentDescription->caption;

                        $lines[$key][] = utf8_decode(str_replace(";",",",html_entity_decode($translatedDescription)));

                    }

                }

            }

            // Generate output content
            $outputLineCount = 0;
            $outputContent = "";
            foreach($lines as $line)
            {
                $outputContent .= implode(";",$line)."\n";
                $outputLineCount++;
            }

            // Save output
            $outputFile = "files/".$batchitem["output"];
            file_put_contents($outputFile,$outputContent);
            echo "\nRead ".countgf($lines)." lines from ".$inputFile.", wrote ".$outputLineCount." lines to ".$outputFile;


        }

        exit();

        // Define in/output
        $inputFile = "files/NY JGK 48 LISTE-omformatteret.csv";
        $outputFile = "files/NY JGK 48 LISTE-labels.csv";

        // Load input
        $inputContent = file_get_contents($inputFile);
        $inputLine = explode("\n",$inputContent);
        $lines = array();
        $nextNumber = 0;

        // Parse input content
        foreach($inputLine as $line)
        {
            $linedata = explode(";",$line);
            $lines[] = $linedata;
            if($nextNumber < intval($linedata[0]))
                $nextNumber = intval($linedata[0]);
        }

        $nextNumber++;
        $newNumberMap = array();

        foreach($lines as $key => $line)
        {

/*
            if(isset($line[5]) && trimgf($line[5]) != "")
            {
                $giftcardnumber = $line[5];
                //if($line[1] == "Sengelinned Nebulosa blå, 2 sæt fra Trip Trap Skagerak")
                if($line[0] == "8c" || $line[0] == "4c")
                {

                    $shopUserList = ShopUser::find("all",array('conditions' => array('username' => trimgf($giftcardnumber))));
                    if(count($shopUserList) == 0)
                    {
                        echo "Shopuser not found: username = ".$giftcardnumber;
                        exit();
                    }
                    $shopUser = $shopUserList[0];

                    $orderList = Order::find('all',array('conditions' => array('shopuser_id' => $shopUser->id)));
                    if(count($orderList) == 0)
                    {
                        echo "Order not found: username = ".$giftcardnumber."<br>";
                    }

                    $order = $orderList[0];
                    $modelNameSplit = explode("###",$order->present_model_name);
                    $subSplit = explode(" ",trimgf($modelNameSplit[0]));

                    $newModelName = utf8_decode($subSplit[countgf($subSplit)-1]." - ".$modelNameSplit[1]);
                    $newNumber = 0;

                    if(!isset($newNumberMap[$newModelName]))
                    {
                        $newNumber = $nextNumber;
                        $newNumberMap[$newModelName] = $nextNumber;
                        echo $line[0]." renamed to ".$newNumber."c - ".$newModelName."<br>";
                        $nextNumber++;
                    }
                    else
                    {
                        $newNumber = $newNumberMap[$newModelName];
                    }


                    $lines[$key][2] = $newModelName;
                    $lines[$key][0] = $newNumber."c";

                }
            }
*/



            if(intval($line[0]) > 0 && isset($line[5]) && trimgf($line[5]) != "")
            {

                $giftcardnumber = $line[5];
                $shopUserList = ShopUser::find("all",array('conditions' => array('username' => trimgf($giftcardnumber))));
                if(count($shopUserList) == 0)
                {
                    echo "Shopuser not found: username = ".$giftcardnumber;
                    exit();
                }
                $shopUser = $shopUserList[0];

                $orderList = Order::find('all',array('conditions' => array('shopuser_id' => $shopUser->id)));
                if(count($orderList) == 0)
                {
                    echo "Order not found: username = ".$giftcardnumber."<br>";
                }
                else
                {

                    $order = $orderList[0];

                    $present_id = $order->present_id;
                    $language_id = $order->language_id;

                    $presentDescriptionList = PresentDescription::find('all',array('conditions' => array('present_id' => intval($present_id),'language_id' => intval($language_id))));
                    if(count($presentDescriptionList) == 0)
                    {
                        echo "Present description not found for gift: ".$giftcardnumber." (".$present_id." / ".$language_id.")";
                        exit();
                    }
                    $presentDescription = $presentDescriptionList[0];
                    //$translatedDescription = strip_tags(base64_decode($presentDescription->short_description));
                    $translatedDescription = $presentDescription->caption;

                    //echo $line[1]." - ".$present_id." / ".$language_id.": ".$translatedDescription."<br>";
                    $lines[$key][] = utf8_decode(str_replace(";",",",html_entity_decode($translatedDescription)));

                }

            }
            else
            {
                //echo "Giftcardnumber not set: <pre>".print_r($line)."</pre><br>";
            }

        }


        // Generate output content
        $outputLineCount = 0;
        $outputContent = "";
        foreach($lines as $line)
        {
            $outputContent .= implode(";",$line)."\n";
            $outputLineCount++;
        }

        // Save output
        file_put_contents($outputFile,$outputContent);
        echo "\nRead ".countgf($lines)." lines from ".$inputFile.", wrote ".$outputLineCount." lines to ".$outputFile;

    }


    /**
     * FIX SHOP PRESENT MODELS
     **/
     
     public function fixshoppresentlanguages()
     {
     
        $noshopModels = PresentModel::find_by_sql("SELECT present_id, model_id, COUNT(id), GROUP_CONCAT(language_id) as langs FROM present_model WHERE present_id IN (SELECT id FROM present WHERE shop_id = 0) GROUP BY present_id, model_id");
        $orgModelMap = array();
        
        $newModelID = array();
          
        foreach($noshopModels as $model)
        {
          $orgModelMap[$model->model_id] = explode(",",$model->langs);
        }
     
        echo "<pre>";
        
        // Shop models
        $shopmodellist = PresentModel::find_by_sql("SELECT present_id, model_id, original_model_id, COUNT(id), GROUP_CONCAT(language_id) as langs FROM present_model WHERE original_model_id > 0 && present_id IN (SELECT id FROM present WHERE shop_id > 0) GROUP BY present_id, model_id, original_model_id");
        foreach($shopmodellist as $shopmodel)
        {
          if(!isset($orgModelMap[$shopmodel->original_model_id]))
          {
            echo "COULD NOT FIND ORIGINAL MODEL ".$shopmodel->original_model_id." \r\n"; 
            
          }
          else 
          {
          
            $langs = explode(",",$shopmodel->langs);
            $diff = array_diff($orgModelMap[$shopmodel->original_model_id],$langs);
            
            if(count($diff) > 0)
            {
              echo "\r\n\r\nDifference in model: ".$shopmodel->present_id." / ".$shopmodel->model_id." / ".$shopmodel->original_model_id."\r\n";
              //print_r($diff); print_r($orgModelMap[$shopmodel->original_model_id]);
              //print_r($shopmodel);
              
              foreach($diff as $diff_lang)
              {
                $orgmodel = PresentModel::find_by_sql("SELECT * FROM present_model WHERE model_id = ".intval($shopmodel->original_model_id)." && language_id = ".intval($diff_lang));
                if(count($orgmodel) == 1)
                {
                                     $orgmodel = $orgmodel[0];
                                     
                  echo "Create new model language:
  model_id: ".$shopmodel->model_id."
    original_model_id: ".$shopmodel->original_model_id."
    present_id: ". $shopmodel->present_id."
    model_present_no: ". $orgmodel->model_present_no."
    language_id: ".$diff_lang."
    
    ";
            
                  $newModel = new PresentModel();
                  $newModel->model_id = $shopmodel->model_id; 
                  $newModel->original_model_id = $shopmodel->original_model_id;
                  $newModel->present_id = $shopmodel->present_id;
                  $newModel->language_id = $diff_lang;
                  $newModel->model_present_no = $orgmodel->model_present_no;
                  
                  
                  $newModel->model_name = $orgmodel->model_name;
                  $newModel->model_no = $orgmodel->model_no;
                  $newModel->media_path = $orgmodel->media_path;
                  $newModel->active = $orgmodel->active;
                  $newModel->dummy = $orgmodel->dummy;
                  $newModel->is_deleted = $orgmodel->is_deleted;
                  $newModel->aliasletter = $orgmodel->aliasletter;
                  $newModel->fullalias = $orgmodel->fullalias;
                  $newModel->save();
                  
                  $newModelID[] = $newModel->id;
                  
                  
                  echo "\r\n";
                  
                
                }
                else 
                {
                  echo "\r\nERROR FINDING COPYMODEL, FOUND ".countgf($orgmodel)."\r\n";
                }
              }
              
              
            }
            else 
            {
              //echo "NO DIFF\r\n";
            }
          
          
          }
        }
        
        echo "</pre>";
        
        System::connection()->commit();
        echo "NEW MODEL ID's<br><br>";
        echo implode(",",$newModelID);
        
        
        
     }

     
  /** GENERATE RANDOM USERNAMES AND PASSWORDS **/
  
  private function generateRandomString($length = 10) {
    $characters = 'abcdefghijklmnopqrstuvwxyz';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

  public function generatecodes()
  {
      $generatedCodes = array();
      $count = 5000;
      $length = 6;
      $generated = 0;
      
      $content = "";
     
        header('Content-Type: application/csv');
    header('Content-Disposition: attachment; filename=brugernavn-koder.csv');
    header('Pragma: no-cache');
    
      while($generated < $count)
      {
        
        $username = $this->generateRandomString(6);
        $password = $this->generateRandomString(6);
      
        if(!in_array($username,$generatedCodes) && !in_array($password,$generatedCodes))
        {
          $content .= "$username;$password\r\n";
          $generated++;
           $generatedCodes[] = $username;
           $generatedCodes[] = $password;
        }
        //else echo "MATCH $username/$password\r\n\r\n";
      }
  
      echo $content;
  
  }

}

?>