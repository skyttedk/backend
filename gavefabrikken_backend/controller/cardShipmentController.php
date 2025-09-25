<?php
/*
shipment_ready status code:
0   not check jet
1   is tjeck and has childring
2   is tjek and has no childring
10  card ready to be ships


*/



class cardShipmentController Extends baseController {

  public function Index() {
    if(!isset($_GET["token"])){
      die("No acces");
    } else {
      if($_GET["token"] == "vgJWCsitE3fZ40x1aG52bExVq6M2c8BpdSfrCV55"){
             $csv =  $this->getShipment($_GET["id"])["csv"];
             $this->array_to_csv_download($csv,  "export.csv",";") ;
      } else {
        $sql = "SELECT id, is_shipped,is_cancelled  FROM `company_order` where shipment_token = '".$_GET["token"]."'";
        $companyOrderIdRS = Dbsqli::getSql2($sql);
        if($companyOrderIdRS[0]["is_shipped"] == 11){
            echo "<h3>Kort er ikke fysiske kort</h3>";
        } else if($companyOrderIdRS[0]["is_cancelled"] == 11){
            echo "<h3>Kort er lukket</h3>";
        } else {
                   echo $this->getShipment($companyOrderIdRS[0]["id"])["html"];
        }
      }
    }

  }
  public function getbyId(){
    if(!isset($_GET["token"])){
      die("No acces");
    } else {
      if($_GET["token"] == "f849yt5478eib74r6t89"){
          echo $this->getShipment($_GET["id"])["html"];
      }
    }
  }
  public function shipmentHasCard()
  {
     if(is_int($_POST["orderId"]*1) ){
        $sql = "SELECT count(id) as number FROM `shop_user` where company_order_id = ".$_POST["orderId"]." and blocked = 0  " ;
        $result = Dbsqli::getSql2($sql);

         response::success(json_encode($result));
     }
  }

  public function getShipment($id)
  {
      $html = "";
      $faktCompanyID;
      $companyOrderId;
      $companyOrderShopName;
      $companyName;
      $csv = [];
      $sql = "SELECT week_no, `company_order`.id, shop_name,company_name, shop_id FROM `company_order`
                inner join expire_date on
                expire_date.expire_date = `company_order`.expire_date
                where `company_order`.id = '".$id."'";
      $companyOrderIdRS = Dbsqli::getSql2($sql);


      if(sizeofgf($companyOrderIdRS) > 0){
        $companyOrderId = $companyOrderIdRS[0]["id"];
        $companyOrderShopName = $companyOrderIdRS[0]["shop_name"];
        $shop_id = $companyOrderIdRS[0]["shop_id"];
        $week_no = $companyOrderIdRS[0]["week_no"];
        $lang = "";
        $dk = [52,54,55,56,53,290,310,575];
        $no = [574,57,58,59,272 ];
        $se = [1832,1981,4793,5117,8271,9495];
        if (in_array($shop_id, $dk)) {
            $lang = "Dannark";
        }
        if (in_array($shop_id, $no)) {
            $lang = "Norge";
        }
        if (in_array($shop_id, $se)) {
            $lang = "Sverige";
        }


        $companyName =  $companyOrderIdRS[0]["company_name"];
      } else {
            echo "Token not valid ";
            return;
      }
      $html.="<html><meta charset='UTF-8'><style>
        body{ font-size:12px;
        }
 table, td, th {
  border: 1px solid gray;
  text-align: left;
}

table {
  border-collapse: collapse;
  width: 600px;
}

th, td {
  padding: 5px;
}
        @media print {
                      table{ page-break-after: always; }
                      button{display:none;}
      } </style>";


      $sql = "select order_no, shipment_ready_only_parent,company_id  FROM `company_order` where id  = ".$id;
      $shipmentOnlyParentRs = Dbsqli::getSql2($sql);
      $shipmentOnlyParent = $shipmentOnlyParentRs[0]["shipment_ready_only_parent"];
      $orderNo = $shipmentOnlyParentRs[0]["order_no"];

      $fordeling = "F";
      if($shipmentOnlyParent == 1){
        $fordeling = "IF";
      }
      $html.="<h1>Firma: ".$companyName. " --- <u>". $fordeling."</u>---</h1><h3> Gavekort: ".$companyOrderShopName." ---- Land: ".$lang." Uge:".$week_no."</h3>";

      $html.="<br><a href='".GFConfig::BACKEND_URL."index.php?rt=cardShipment&token=vgJWCsitE3fZ40x1aG52bExVq6M2c8BpdSfrCV55&id=".$id."&csv' mc:disable-tracking>Download CSV file</a>";

      $sql = "SELECT id,pid FROM `company` where pid = (SELECT `company_id` FROM `company_order` where id = '".$id."')";
      $result = Dbsqli::getSql2($sql);

      if(sizeofgf($result) == 0){
            $sql = "SELECT `company_id` as id FROM `company_order` where id = '".$id."'";
            $result = Dbsqli::getSql2($sql);
            if(sizeofgf($result) == 0) return;
            $faktCompanyID = $result[0]["id"];
      } else {
          $faktCompanyID = $result[0]["pid"];
          $result[] = array("id"=>$result[0]["pid"]);
          $result = array_reverse($result);
      }


      // tjekker kort

      // fakt company allways first


      // check if all card sendt to fak.virk

      $sql = "select order_no, shipment_ready_only_parent,company_id  FROM `company_order` where id  = ".$id;
      $shipmentOnlyParentRs = Dbsqli::getSql2($sql);
      $shipmentOnlyParent = $shipmentOnlyParentRs[0]["shipment_ready_only_parent"];
      $orderNo = $shipmentOnlyParentRs[0]["order_no"];
      /*
      if($shipmentOnlyParent == 1){
          $sql = "SELECT  `company_id`as id FROM `company_order` where id = ".$id;
          $result = Dbsqli::getSql2($sql);

      }
      */
          $csv[] = array(
                "company navne"=>"Firma navn",
                "ship_to_address" => "Adresse",
                "ship_to_address2"=> "Adresse2",
                "postnr" =>"postnr",
                "bynavn" => "bynavn",
                "kontaktperson"=>  "kontaktperson",
                "telefon"=> "telefon",
                "email"=>  "email",
                "bsnr"=> "bsnr"
          );

      foreach($result as $val){
/*
            if($shipmentOnlyParent == 1){
                $sql = "SELECT username,password,expire_date FROM `shop_user` where company_order_id = ".$companyOrderId." and blocked = 0  order by username " ;
            }
            else {
                $sql = "SELECT username,password,expire_date FROM `shop_user` where company_order_id = ".$companyOrderId." and company_id =".$val["id"]." and blocked = 0 order by username " ;
            }
  */
            $sql = "SELECT username,password,expire_date FROM `shop_user` where company_order_id = ".$companyOrderId." and company_id =".$val["id"]." and blocked = 0 order by username " ;
            $cardsRs = Dbsqli::getSql2($sql);
            if(sizeofgf($cardsRs) > 0 || $faktCompanyID == $val["id"]){
              $sql = "SELECT * from company where id = ".$val["id"];
              $companyRs = Dbsqli::getSql2($sql);

              $html.= "<br><hr><br>";
              $companyName = $companyRs[0]["ship_to_company"];
              if($companyName == ""){
                $companyName = $companyRs[0]["name"];
              }
              $csv[] = array(
                "company navne"=>utf8_decode($companyName),
                "ship_to_address" => utf8_decode($companyRs[0]["ship_to_address"]),
                "ship_to_address2"=> utf8_decode($companyRs[0]["ship_to_address_2"]),
                "postnr" =>$companyRs[0]["ship_to_postal_code"],
                "bynavn" => utf8_decode($companyRs[0]["ship_to_city"]),
                "kontaktperson"=>  utf8_decode($companyRs[0]["contact_name"]),
                "telefon"=> $companyRs[0]["contact_phone"],
                "email"=>  $companyRs[0]["contact_email"],
                "bsnr"=> $orderNo

                );


              $html.= "<table border=1 cellpadding=4 width=600>";
              if($shipmentOnlyParent == 1 && $faktCompanyID == $val["id"]){
                $html.= "<tr><td colspan=4><b>Denne adresse skal kort sendes til</b></td></tr>";
              }

              $html.= "<tr><td>Firma navn</td><td><b>".$companyName."</b></td></tr>";
              $html.= "<tr><td>Adresse 1</td><td>".$companyRs[0]["ship_to_address"] ."</td></tr>";
              $html.= "<tr><td>Adresse 2</td><td>".$companyRs[0]["ship_to_address_2"]."</td></tr>";
              $html.= "<tr><td>Postnummer</td><td>".$companyRs[0]["ship_to_postal_code"]."</td></tr>";
              $html.= "<tr><td>By</td><td>".$companyRs[0]["ship_to_city"]."</td></tr>";
              $html.= "<tr><td>Cvr</td><td>".$companyRs[0]["cvr"]."</td></tr>";
              $html.= "<tr><td>Ean</td><td>".$companyRs[0]["ean"]."</td></tr>";
              $html.= "<tr><td>Kontaktperson</td><td>".$companyRs[0]["contact_name"]."</td></tr>";
              $html.= "<tr><td>Kontaktperson e-mail</td><td>".$companyRs[0]["contact_email"]."</td></tr>";
              $html.= "<tr><td>Kontaktperson telefon</td><td>".$companyRs[0]["contact_phone"]."</td></tr>";
              $html.= "<tr><td colspan=2>Gavekort</td></tr>";
              $html.= "<tr><td colspan=2>";
              foreach($cardsRs as $card){
                $html.= "<b>".$card["username"]."</b><br>";
                $html.= $card["password"]."<br>";
                $html.= $card["expire_date"]."<hr>";

              }
              $html.= "</td><tr>";
            }
            $html.= "</table><div class='pagebreak1'></div></html>";
      }


      return array("html"=>$html,"csv"=>$csv);


  }
  public function freeShipmentSe(){

        $sqlGetOrderID = "select id from company_order where shop_id in (1832,1981,4793,5117,8271,9495) and shipment_ready = 0 and is_printed = 0 and is_shipped = 0 limit 1";
        $orderToHandelRS = Dbsqli::GetSql2($sqlGetOrderID);
        print_R($orderToHandelRS);
        if(sizeofgf($orderToHandelRS) == 0) return;
        $orderId =  $orderToHandelRS[0]["id"];

        $sql = "update company_order set shipment_ready = 1, shipment_ready_only_parent = 1 where id=".$orderId;

        $result = Dbsqli::setSql2($sql);
        $mailBody = utf8_decode($this->getShipment($orderId)["html"]);

        $maildata = [];
	    $maildata['sender_email'] =  "no-reply@gavefabrikken.dk";
	    $maildata['recipent_email'] = "pakgavekort@gavefabrikken.dk";
	    $maildata['subject']= "Card shipment SE";
	    $maildata['body'] = $mailBody;
	    $maildata['mailserver_id'] = 4;

        MailQueue::createMailQueue($maildata);


        $maildata = [];
	    $maildata['sender_email'] =  "no-reply@gavefabrikken.dk";
	    $maildata['recipent_email'] = "us@gavefabrikken.dk";
	    $maildata['subject']= "Card shipment SE";
	    $maildata['body'] = $mailBody;
	    $maildata['mailserver_id'] = 4;

        MailQueue::createMailQueue($maildata);
        system::connection()->commit();
        response::success(make_json("result", $result));

  }


  public function freeShipment(){
    if(is_int($_POST["orderpost"]*1) && is_int($_POST["orderpost"]*1) ){


    $sql = "update company_order set shipment_ready = 1, shipment_ready_only_parent = ".$_POST["shipmentConfig"]." where id=".$_POST["orderpost"];
    $result = Dbsqli::setSql2($sql);
    $mailBody = utf8_decode($this->getShipment($_POST["orderpost"])["html"]);

        $maildata = [];
	    $maildata['sender_email'] =  "no-reply@gavefabrikken.dk";
	    $maildata['recipent_email'] = "pakgavekort@gavefabrikken.dk";
	    $maildata['subject']= "Card shipment";
	    $maildata['body'] = $mailBody;
	    $maildata['mailserver_id'] = 4;

        MailQueue::createMailQueue($maildata);

        $maildata = [];
	    $maildata['sender_email'] =  "no-reply@gavefabrikken.dk";
	    $maildata['recipent_email'] = "th@gavefabrikken.no";
	    $maildata['subject']= "Card shipment";
	    $maildata['body'] = $mailBody;
	    $maildata['mailserver_id'] = 4;

        MailQueue::createMailQueue($maildata);


        $maildata = [];
	    $maildata['sender_email'] =  "no-reply@gavefabrikken.dk";
	    $maildata['recipent_email'] = "logistikk1@gavefabrikken.no";
	    $maildata['subject']= "Card shipment";
	    $maildata['body'] = $mailBody;
	    $maildata['mailserver_id'] = 4;


        MailQueue::createMailQueue($maildata);



        $maildata = [];
	    $maildata['sender_email'] =  "no-reply@gavefabrikken.dk";
	    $maildata['recipent_email'] = "us@gavefabrikken.dk";
	    $maildata['subject']= "Card shipment";
	    $maildata['body'] = $mailBody;
	    $maildata['mailserver_id'] = 4;

        MailQueue::createMailQueue($maildata);
        system::connection()->commit();
        response::success(make_json("result", $result));

    }
  }
  public function sendCardOnlyFakt(){
    $sql = "update company_order set shipment_ready_only_parent = ".$_POST["newVal"]." where id=".$_POST["orderpost"];
    $result = Dbsqli::setSql2($sql);
        $dummy = array();
        response::success(make_json("result", $dummy));
  }


  public function disableNoneRelevantOrders(){
    // get order to process
     $orderId;

     $sql = "SELECT id FROM `company_order` WHERE shipment_ready = 0 limit 1";
     $result = Dbsqli::getSql2($sql);

     if(sizeofgf($result) == 0){
       // no item in queue
     } else {
        $orderId  = $result[0]["id"];
        $sql = "SELECT COUNT(id) as h FROM `company` where pid = (SELECT `company_id` FROM `company_order` where id = ".$orderId.")";
        // test if order is target
        $result = Dbsqli::getSql2($sql);
        if($result[0]["h"] == 0){
        // is not target setting shipment_ready = 2;
           echo $sql = "update company_order set shipment_ready = 2 where id = ".$orderId;
        } else {
           echo $sql = "update company_order set shipment_ready = 1 where id = ".$orderId;
        }
       // Dbsqli::setSql2($sql);
     }
  }
  public function getChildslistAsCSV(){




  }




  private function array_to_csv_download($array, $filename = "export.csv", $delimiter=";") {
    // open raw memory as file so no temp files needed, you might run out of memory though
    $f = fopen('php://memory', 'w');
    // loop over the input array
    foreach ($array as $line) {
        // generate csv lines from the inner arrays

        fputcsv($f, $line, $delimiter);
    }
    // reset the file pointer to the start of the file
    fseek($f, 0);
    // tell the browser it's going to be a csv file
    header('Content-Type: application/csv');
    header('Content-Type: text/html; charset=iso-8859-1');
    // tell the browser we want to save it instead of displaying it
    header('Content-Disposition: attachment; filename="'.$filename.'";');
    // make php send the generated csv lines to the browser
    fpassthru($f);
}
}
?>