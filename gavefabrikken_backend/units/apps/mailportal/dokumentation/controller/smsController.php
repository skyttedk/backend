<?php
// Controller media
// Date created  Wed, 06 Apr 2016 09:39:56 +0200
// Created by Bitworks
class smsController Extends baseController {
  public function Index() {

  }
  public function ulrich(){
  $idlist = "";
  $rs = Dbsqli::getSql2(
            "select * from company where id in (

  SELECT DISTINCT distinct (company.id) from company
        inner JOIN shop_user ON shop_user.company_id = company.id
        where  shop_user.shop_id in(57,58,59,272) AND
        shop_user.blocked = 0 )  and ship_to_address_2 != ''  and  is_gift_certificate = 1    ");
        foreach( $rs as $element ){

            $ship_to_address =  $element["ship_to_address"];
            $ship_to_address_2 = $element["ship_to_address_2"];
            $id =  $element["id"];
            $idlist.= $id.",";
          Dbsqli::setSql2("update company set ship_to_company = '".$ship_to_address."' ,ship_to_address = '".$ship_to_address_2."',ship_to_address_2 = '' where id= ".$id);


        }

       echo $idlist;

/*
     $rs = Dbsqli::getSql2("select * from company where ship_to_address_2 != ''  and  is_gift_certificate = 1   " );
     $i = 0;
     foreach( $rs as $element ){
        $i++;
        $pos = strpos($element["contact_email"], ".dk");
        if ($pos === false) {
           print_R($element);
        }

    }
    echo $i;

    */
  }



  public function sendSms(){
    $model = "";
    $order_no = "";
    if($_POST["model"] != ""){
         $model = str_replace("###"," ",$_POST["model"]);
         $order_no = $_POST["order_no"];
    }
    $query = http_build_query(array(
    'token' => 'rhSukWyyQLyFyMRXlbNBmW3LFHb15wfLXwACjUIEWnn4DBU_jrZMAORCeSCrotwf',
    'sender' => 'Valgt gave',
    'message' => 'Du har valgt: '.$model.' Ordrenr.: '.$order_no,
    'recipients.0.msisdn' => '0045'.$_POST["tlf"],
    ));
    // Send it
    $result = file_get_contents('https://gatewayapi.com/rest/mtsms?' . $query);
    // Get SMS ids (optional)
    //echo json_decode($result)->ids;


      // jeg har deaktiveret denne 2201 da vi skal have det sikret det bedre
      echo json_encode(array("status"=>"1"));
  }

}

?>