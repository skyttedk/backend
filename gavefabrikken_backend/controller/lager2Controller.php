<?php
 if (session_status() == PHP_SESSION_NONE) session_start();
// Controller SystemLog
// Date created  Wed, 13 Apr 2016 20:48:50 +0200
// Created by Bitworks
class lager2Controller Extends baseController {

  public function Index() {
      $this->registry->template->show('lager2_view');
  }
  public function getShopGiftList(){
      $dummy = [];
      response::success(make_json("result",$dummy));
  }


  public function getPrintetedOrder(){
      $shops = $_POST["cardId"];
      $companyorder = CompanyOrder::find_by_sql("SELECT * FROM company_order WHERE  shop_id in (".$shops.") and is_email = 0  and is_cancelled = 0 and is_printed = 1 and is_shipped = 0  ORDER BY expire_date,id");
      response::success(make_json("result", $companyorder));
  }
  public function regDelivedeOrdre(){
     $list = $_POST["OrderList"];
     $order = explode(",", $list);
     for($i=0;$i < sizeofgf($order) ;$i++)
     {
        $companyorder = CompanyOrder::find($order[$i]);
        $companyorder->is_shipped = 1;
        $companyorder->save();
     }
     response::success(make_json("result", $companyorder));
  }
  public function getIsShipOrder(){
      $shops = $_POST["cardId"];
      $companyorder = CompanyOrder::find_by_sql("SELECT * FROM company_order WHERE  shop_id in (".$shops.") and is_email = 0 and is_cancelled = 0 and is_printed = 1 and is_shipped = 1  ORDER BY expire_date,id");
      response::success(make_json("result", $companyorder));
  }
 public function getDeletedOrder(){
      $shops = $_POST["cardId"];
      $companyorder = CompanyOrder::find_by_sql("SELECT * FROM company_order WHERE  shop_id in (".$shops.") and is_email = 0 and is_cancelled = 1  ORDER BY expire_date,id");
      response::success(make_json("result", $companyorder));
  }








  public function getNotReleased(){
      $shops = $_POST["cardId"];
      $companyorder = CompanyOrder::find_by_sql("SELECT * FROM company_order WHERE  shop_id in (".$shops.") and is_email = 0 and is_printed = 0 and is_shipped = 0 and subdate(NOW(),INTERVAL 1 HOUR) < created_datetime ORDER BY expire_date,id");
      response::success(make_json("result", $companyorder));
  }

  public function getWaitingOrder(){
       $shops = $_POST["cardId"];
       if(date('H:i') > 15 ){
           $companyorder = CompanyOrder::find_by_sql("SELECT * FROM company_order WHERE  shop_id in (".$shops.") and is_email = 0 and is_printed = 0 and is_shipped = 0  ORDER BY expire_date,id");
       } else {
           $companyorder = CompanyOrder::find_by_sql("SELECT * FROM company_order WHERE  shop_id in (".$shops.") and is_email = 0 and is_printed = 0 and is_shipped = 0 and subdate(NOW(),INTERVAL 1 HOUR) > created_datetime ORDER BY expire_date,id");
       }
       response::success(make_json("result", $companyorder));
  }

  public function deleteOrder(){
        $orderId = $_POST["orderId"];
        $companyorder = CompanyOrder::find($orderId);
        $companyorder->is_cancelled = 1;
        $companyorder->save();
        response::success(make_json("result", $companyorder));
        // husk at slette kort
  }
  public function restoreOrder(){
        $orderId = $_POST["orderId"];
        $companyorder = CompanyOrder::find($orderId);
        $companyorder->is_cancelled = 0;
        $companyorder->save();
        response::success(make_json("result", $companyorder));
  }
  public function goPrint(){

        $shops =  $_POST["doPrintList"];
        if($shops != ""){
            $printList = CompanyOrder::find_by_sql("SELECT *, CAST(expire_date AS char) as kortData FROM company_order WHERE id in (".$shops.")  ORDER BY expire_date,id");
            $this->printHtml($printList);
        } else {
            echo "ingen valgte";
        }





  }
  public function goPrintAll(){
     $shops =  $_POST["goPrintAllCard"];
     $printList = CompanyOrder::find_by_sql("SELECT *, CAST(expire_date AS char) as kortData FROM company_order where shop_id in (".$shops.") and is_printed = false and is_shipped = false and is_email = false  ORDER BY expire_date,id");
     $this->printHtml($printList);
  }


  public function printHtml($printList){
        include("model/dbsqli.class.php");
        $db = new Dbsqli;
        echo "<html>
        <head>
        <meta charset=\"UTF-8\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    <style>
        body {  margin: 25px; padding: 0px; font-size: 14px; font-family: verdana; overflow-x: hidden; }

}
  .note{   width:400px;  }
.page {page-break-after: always;}
            @page {
    size: auto;   /* auto is the initial value */
    margin: 0;  /* this affects the margin in the printer settings */

    </style></head><body>";

        $pageCount = 1;
        $totalCount = sizeofgf($printList);

     

        foreach($printList as $ele ){
            $val = $ele->attributes;
            $datoStr = $val["kortdata"];
            $datoArr = explode("-", $datoStr);
            $datoNewStr = $datoArr[2]."-".$datoArr[1]."-".$datoArr[0];


            echo "<center><br /><br /><br />";
            echo "<h1>".$val["shop_name"]."</h1>";

            echo "<h3>".$datoNewStr."</h3>";

            echo "<h4>".$val["certificate_value"]."</h3>";
            echo "<br /><br /><br />";
            if ($val["is_cancelled"] == 1){
                  echo "<div style=\"border:1px solid black; font-size:1.3em;\">ORDRE ANNULLERET</div>";
            }
             if($val["spdealtxt"] != ""){
                echo "<fieldset  style=\"width:500px;\">";
                echo "<legend >Special aftale</legend>";
                echo "<div class=\"note\">".$val["spdealtxt"]."</div>";
                echo "</fieldset><br />";
            }
            if($pageCount < $totalCount){
                echo "<div class=\"page\"><table width=600 border=0>";
            } else {
                echo "<div class=\"page_end\"><table width=600 border=0>";
            }

            echo "<tr><td width=200><b>Ordernr.:</b></td><td  width=400><b>".$val["order_no"]."</b></td></tr>";
            echo "<tr><td><br /></td><td></td></tr>";


                if($val["ship_to_company"] == "" ){
                    echo "<tr><td>Virksomhed:</td><td>".$val["company_name"].".</td></tr>";
                } else {
                    echo "<tr><td>Virksomhed:</td><td>".$val["ship_to_company"].".</td></tr>";
                }


            echo "<tr><td>Cvr:</td><td>".$val["cvr"]."</td></tr>";
            if($val["ean"] != ""){
                echo "<tr><td>EAN</td><td>".$val["ean"]."</td></tr>";
            }
            echo "<tr><td>Vej:</td><td>".$val["ship_to_address"]."<br />".$val["ship_to_address_2"]."</td></tr>";
            echo "<tr><td>Postnummer:</td><td>".$val["ship_to_postal_code"]."</td></tr>";
            echo "<tr><td>By:</td><td>".$val["ship_to_city"]."</td></tr>";
            echo "<tr><td><br /></td><td></td></tr>";

            echo "<tr><td>Kontaktperson:</td><td>".$val["contact_name"]."</td></tr>";
            echo "<tr><td>E-mail:</td><td>".$val["contact_email"]."</td></tr>";
            echo "<tr><td>Tlf.nr.:</td><td>".$val["contact_phone"]."</td></tr>";
            echo "</table><br />";
            echo "<table width=600 border=0>";
            echo "<tr><td>Gavekort</td><td>Start</td><td>Slut</td></tr>";
            echo "<tr><td>".$val["quantity"]."</td><td>".$val["certificate_no_begin"]."</td><td>".$val["certificate_no_end"]."</td></tr>";
            echo "</table><br /><br /><br /><br /><br />";


            if ($val["is_cancelled"] == 1){
                echo "<div style=\"border:1px solid black; font-size:1.3em;\">ORDRE ANNULLERET</div>";
            }
            echo "</div></center><br /><br /><br />";

           $sql = "update company_order set is_printed = 1 where id =".$val["id"];
         $db->setSql2($sql);
            $pageCount++;
        }


echo "<script>window.print();</script>";
echo "</body></html> ";


  }






 /*
  6637
  6640
   BS06577
   6660,6620


 SELECT id, `created_datetime` FROM `company_order` WHERE subdate(NOW(),INTERVAL 24 HOUR) < `created_datetime` ORDER BY `company_order`.`id` ASC

 $shopboard = Shopboard::find_by_sql("select * from shop_board where active = true  order by fane");
  ShopUser
  public function getAllOrders(){
      Dbsqli::getSql("select order_no,company_name,company_cvr, from order where shop_is_gift_certificate = 1   ");
  }



        $postData = $_POST["data"];

          $post = Shopboard::find($postData["id"]);
        $post->fane = $postData["fane"];
        $post->save();
        $dummy = array();
        response::success(make_json("result", $dummy));







  */






}

?>