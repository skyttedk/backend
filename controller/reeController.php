<?php
set_time_limit ( 3000 );
ini_set('memory_limit', '128M');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
class reeController Extends baseController
{
    public function Index()
    {



        //$this->pullReplacmentCard(2958771,1832,5,"us@bitworks.dk");

        $filename = getcwd()."/controller/list.txt";

        // Åbn filen i læsemodus ('r')
        $file = fopen($filename, "r");

        // Tjek om filen blev åbnet korrekt
        if ($file) {
            // Læs filen linje for linje indtil enden af filen
            while (($line = fgets($file)) !== false) {
                // Behandl hver linje som ønsket
                $array = explode(";", $line);
                $email=  $array[1];
                $order = Order::find_by_order_no($array[0]);
                $shopuser = $order->shopuser_id;

                // funktion til at hente data
                //$this->pullReplacmentCard($shopuser,1832,5,$email);
                // funktion til at masse generere replacment kort indsæt i list.txt
                $this->getReplacmentLogin($shopuser,$email,$array[0]);
            }

            // Luk filen efter brug
            fclose($file);
        } else {
            // Fejlhåndtering, hvis filen ikke kunne åbnes
            echo "Fejl ved åbning af filen.";
        }

    }

    private function getReplacmentLogin($shopuserID,$email,$orderID){
        $rs = \Dbsqli::getSql2("
            select shop_user.*
                from shop_user 
             
                where shop_id = 1832 and shop_user.replacement_id = ".$shopuserID);
        echo $email.";;".$rs[0]["username"].";".$rs[0]["password"].";"."$orderID";
        echo "<br>";
    }





    private function pullReplacmentCard($shopuserID,$target,$language,$email){




        if(intval($language) <= 0) $language = 1;
        $cardshopWithLanguage = \CardshopSettings::find('first',array("conditions" => array("language_code = ".intval($language)." && replacement_company_id > 0")));
        $companyID = $cardshopWithLanguage->replacement_company_id;
        // udtr�kker alias
        $rs = \Dbsqli::getSql2("select * from shop_user
                                   where
                                     shop_user.company_id = ".$companyID." and
                                     (replacement_id = 0 or replacement_id IS NULL) and
                                     shop_id = ".$target."
                                   limit 1");
        $replacementID =   $rs[0]["id"];

        $sql1 = "update shop_user set replacement_id = ".$shopuserID." where id = ".$replacementID;

         $sql2 = "update shop_user set is_replaced = 1 where id = ".$shopuserID;
        \Dbsqli::setSql2($sql1);
        \Dbsqli::setSql2($sql2);
        // get
        $rs = \Dbsqli::getSql2("
            select shop_user.*, IF(company_order.floating_expire_date IS NULL,company_order.expire_date,DATE(company_order.floating_expire_date)) as expire_date 
                from shop_user 
                left join company_order on shop_user.company_order_id = company_order.id 
                where shop_user.replacement_id = ".$shopuserID);
        echo $rs[0]["username"].";".$rs[0]["password"].";".$email;
        echo "<br>";

    }

}



