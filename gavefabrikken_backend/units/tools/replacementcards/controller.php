<?php

namespace GFUnit\tools\replacementcards;
use GFBiz\units\UnitController;

class Controller extends UnitController
{

    public function __construct()
    {
        parent::__construct(__FILE__);
    }
    public function test()
    {

    }
    public function getReplacementCardData()
    {

        $approvedlist = array(53,54,55,56,2395,1832,2558,1981,4793,5117);
        $cardToreplacelist = array();
        $lines = file(__DIR__."/replace.txt");

        foreach($lines as $line) {
            $cardToreplacelist[] =  trim(preg_replace('/\s\s+/', ' ', $line));
        }

        $errorLog = [];
        foreach($cardToreplacelist as $sourceCard){

            // hent source card data


            $sourceshopuser =  \ShopUser::find_by_sql("SELECT * FROM `shop_user` where username = '$sourceCard' and is_giftcertificate = 1");


            // tjek om der allerede er oprettet kort
            $sourceshopuser = is_array($sourceshopuser) ? $sourceshopuser: array();
            if(sizeof($sourceshopuser) == 0  ) {
                $errorLog[] = array("card"=>$sourceCard,"error"=>"no card found");
                continue ;
            }


            if(  $sourceshopuser[0]->is_replaced > 0) {
                $errorLog[] = array("card"=>$sourceCard,"error"=>"Card is replaced");
                continue ;
            }

            // Find det rette replace company id

            if(!in_array($sourceshopuser[0]->shop_id,$approvedlist)) {
                $errorLog[] = array("card"=>$sourceCard,"error"=>"Card is not in approved list");
                continue ;
            }
            $orderData =  \OrderAttribute::find_by_sql("SELECT * FROM `order_attribute` WHERE `shopuser_id` = ".$sourceshopuser[0]->id." AND `is_email` = 1");
            $email = sizeof($orderData) == 0  ? "":  $orderData[0]->attribute_value;


            $targetCompanyID = rc_cardmapping($sourceshopuser[0]->shop_id);
            // udtræk korter
            $replacementCard = \Dbsqli::getSql2("SELECT * FROM `shop_user` WHERE `shop_id` = ".$sourceshopuser[0]->shop_id." AND `company_id` = ".$targetCompanyID." AND `replacement_id` = 0 ORDER BY `shop_user`.`username` ASC LIMIT 1");
            if(!is_array($replacementCard) ) {
                $errorLog[] = array("card"=>$sourceCard,"error"=>"No replacement Card found");
                continue ;
            }
            if(sizeof($replacementCard) == 0 ) {
                $errorLog[] = array("card"=>$sourceCard,"error"=>"No replacement Card found");
                continue ;
            }
            // update source card and target card
            // source card

              $sqlSource = "update shop_user set is_replaced = 1 where username = '$sourceCard' and is_giftcertificate = 1 ";
            \Dbsqli::setSql2($sqlSource);

              $sqlTarget = "update shop_user set replacement_id = ".$sourceshopuser[0]->id ." where id = ".$replacementCard[0]["id"];
            \Dbsqli::setSql2($sqlTarget);


            echo $sourceshopuser[0]->shop_id.";".$sourceCard.";".$email.";".$replacementCard[0]["username"].";".$replacementCard[0]["password"];
            echo "<br>";
        }



        print_R($errorLog);


        echo "done";


        //print_R($rs);


    }


}


function rc_cardmapping($shopID){
    // 45363-45364-45365
    $map = array(
        "53"=>"45363",
        "54"=>"45363",
        "55"=>"45363",
        "56"=>"45363",
        "2395"=>"45363",
        "1832"=>"45364",
        "1981"=>"45364",
        "2558"=>"45364",
        "4793"=>"45364",
        "5117"=>"45364"
    );
    return $map[$shopID];
}



