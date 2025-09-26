<?php
// Controller media
// Date created  Wed, 06 Apr 2016 09:39:56 +0200
// Created by Bitworks
class stats2019Controller  {
  public function Index() {
  }

  public function test(){
    echo "hej";
  }
  public function totalCard(){
      $sqlNotSelect = "SELECT COUNT(*) as antal  FROM `shop_user` WHERE `shop_id`  in ( select id from shop where  is_gift_certificate = 1 ) AND blocked = 0 ";
      $rsNotSelect = Dbsqli::getSql2($sqlNotSelect);
      echo $rsNotSelect[0]["antal"];
  }


  public function getAllStats()
  {
      $shop_id = $_POST["shop_id"];
       if($_POST["shop_id"] == "0"){
              $sqlNotSelect = "SELECT COUNT(*) as antal  FROM `shop_user` WHERE `shop_id` in ( select id from shop where  is_gift_certificate = 1 ) AND blocked = 0 order by antal";
       } else {
              $sqlNotSelect = "SELECT COUNT(*) as antal  FROM `shop_user` WHERE `shop_id` = ".$shop_id." AND blocked = 0 order by antal";
       }
      $rsNotSelect = Dbsqli::getSql2($sqlNotSelect);

      if(sizeofgf($rsNotSelect) <= 0 ){
        echo "ingen data";
        return;
      }
       $allCard = $rsNotSelect[0]["antal"];
       if($_POST["shop_id"] == "0"){
            $sql = "SELECT  count(present_id) as total, present_model_id,  `present_id`, `present_name`,present_model_name FROM `order` INNER join shop_user on shop_user.username = order.user_username WHERE order.shop_id  in ( select id from shop where  is_gift_certificate = 1 ) and shop_user.blocked = 0  group by present_id,present_model_id order by total desc";
       } else {
          $sql = "SELECT  count(present_id) as total,  `present_id`, present_model_id, `present_name`,present_model_name FROM `order` INNER join shop_user on shop_user.username = order.user_username WHERE order.shop_id = ".$shop_id." and shop_user.blocked = 0 group by present_id,present_model_id order by total desc";
       }


      $rs = Dbsqli::getSql2($sql);

            $total = 0;
      $totalProcent = 0;
      foreach($rs as $dataRow){
          $total+= $dataRow["total"]*1;
      }
      $notSelect = $allCard - $total;

      $html = "<p>Antal der mangler at v&oelig;lge: ".$notSelect."</p>";
      $html.= "<p>Total antal kort: ".$allCard."</p>";
      $html.= "<table><tr><th>ID</th><th>Antal valgte gaver</th><th>Antal i %</th><th>Fremskrevet v&oelig;rdi</th><th>Gave</th><th>Model</th><th>Varenr</th></tr>";
      foreach($rs as $dataRow){
        $procent =  ($dataRow["total"] / $total ) *100;
        $procent = round($procent,2);
        $totalProcent+= $procent;
        $guess =  ($notSelect * $procent) / 100 ;
        $guess =  round($guess) + $dataRow["total"];
        $inputId =  $dataRow["present_id"]."_".base64_encode($dataRow["present_model_name"]);
        $inputId = str_replace("=","",$inputId);
        $modelSql = "select model_name, model_no,model_present_no  from present_model where language_id = 1 and model_id= ".$dataRow["present_model_id"];
        $rsModel = Dbsqli::getSql2($modelSql);
        $dataRow["present_model_name"] = $rsModel[0]["model_name"];
        $modelNavn = $rsModel[0]["model_no"];
        $varenr =  $rsModel[0]["model_present_no"];


        $modelBase64 = base64_encode($dataRow["present_model_name"]);
          $html.= "<tr><td>".$dataRow["present_id"]."</td><td  id='val_".$inputId."' >".$dataRow["total"]."</td><td>".$procent."%</td><td>".$guess."</td><td>".str_replace("###"," - ",$dataRow["present_model_name"])."</td> <td>".$modelNavn."</td><td>".$varenr."</td></tr>";
//        $html.= "<tr><td>".$dataRow["present_id"]."</td><td  id='val_".$inputId."' >".$dataRow["total"]."</td><td>".$procent."%</td><td>".$guess."</td><td>".$dataRow["present_name"]."</td><td>".str_replace("###"," - ",$dataRow["present_model_name"])."</td><td><input id='".$inputId."' type=\"number\" /></td><td><button onclick=\"updateStuck('".$dataRow["present_id"]."','".$inputId."','".$modelBase64."')\">Gem</button></td> </tr>";
      }
      $html.= "<tr style=\"font-size:16px;\"><td></td><td>Totale antal</td><td>".$total."</td><td>".$totalProcent."%</td><td></td><td></td><td></td><td></td></tr>";
      $html.= "</table>";
      echo $html;




  }


   public function cardSale(){

         $sql = "SELECT COUNT(*) as antal, shop.name, `shop_user`.shop_id  FROM `shop_user`
            inner join shop on shop.id = shop_user.shop_id
            inner join company_order on `shop_user`.company_order_id = company_order.id
            WHERE `shop_user`.`shop_id` in ( 52,54,55,56,53,290,310,575 ) AND shop_user.blocked = 0 and is_cancelled = 0 group by `shop_user`.shop_id";


          $rsAll = Dbsqli::getSql2($sql);
         //   Print_r($rsAll);
          $sum = 0;
          $sumLastYear = 0;
          $sumToday = 0;
          $sumTodayLastYear = 0;
          $sumMonth = 0;
          $sumMonthLastYear = 0;
          $SaleThisYear2018 = 0;
          $SaleTotalThisYear = 0;
          //echo "<div >".$this->getSameDayInWeekLastYear()."</div>";
          echo "<h2>Danske shops</h2>";
          echo "<table width= 300>";
          echo utf8_encode("<tr><th>Shop</th><th>Total solgte 2019</th><th>Antal solgte totalt 2020</th><th>Antal solgte totalt til og med samme dag, 2019</th><th>Antal solgte indev�rende m�ned 2020</th><th>Antal solgte indev�rende m�ned 2019</th><th>Antal solgte i dag 2020</th><th>Antal solgte i dag 2019</th></tr>");


          foreach($rsAll as $key=>$val){

            $rsTotalSaleThisYear = Dbsqli::getSql2("SELECT COUNT(`shop_user`.`id`) as antal FROM gavefabrikken_2019.`shop_user` inner join gavefabrikken_2019.company_order on `shop_user`.company_order_id = company_order.id WHERE `shop_user`.`blocked` = 0 and `shop_user`.`shop_id` = ".$val["shop_id"]."  and is_cancelled = 0 GROUP BY `shop_user`.shop_id order by `shop_user`.shop_id");
            $SaleTotalThisYear =   sizeofgf($rsTotalSaleThisYear) > 0 ? $rsTotalSaleThisYear[0]["antal"] : "0";

            $rsSaleThisYear = Dbsqli::getSql2("SELECT COUNT(`shop_user`.`id`) as antal FROM `shop_user` inner join company_order on `shop_user`.company_order_id = company_order.id WHERE `shop_user`.`blocked` = 0 and `shop_user`.`shop_id` = ".$val["shop_id"]."  and is_cancelled = 0 and DATE(`created_datetime`) = DATE(NOW()) GROUP BY `shop_user`.shop_id order by `shop_user`.shop_id");
            $SaleThisYear =   sizeofgf($rsSaleThisYear) > 0 ? $rsSaleThisYear[0]["antal"] : "0";
//                                               echo "SELECT COUNT(`shop_user`.`id`) as antal FROM gavefabrikken_2019.`shop_user` inner join gavefabrikken_2019.company_order on `shop_user`.company_order_id = company_order.id WHERE `shop_user`.`blocked` = 0 and `shop_user`.`shop_id` = ".$val["shop_id"]."  and is_cancelled = 0 and DATE(`created_datetime`) = ".$this->getSameDayInWeekLastYear()." GROUP BY `shop_user`.shop_id order by `shop_user`.shop_id <br><br>";
            $rsSaleLastYear = Dbsqli::getSql2("SELECT COUNT(`shop_user`.`id`) as antal FROM gavefabrikken_2019.`shop_user` inner join gavefabrikken_2019.company_order on `shop_user`.company_order_id = company_order.id WHERE `shop_user`.`blocked` = 0 and `shop_user`.`shop_id` = ".$val["shop_id"]."  and is_cancelled = 0 and DATE(`created_datetime`) = '".$this->getSameDayInWeekLastYear()."' GROUP BY `shop_user`.shop_id order by `shop_user`.shop_id");
//            $rsSaleLastYear = Dbsqli::getSql2("SELECT COUNT(`shop_user`.`id`) as antal FROM gavefabrikken_2019.`shop_user` inner join gavefabrikken_2019.company_order on `shop_user`.company_order_id = company_order.id WHERE `shop_user`.`blocked` = 0 and `shop_user`.`shop_id` = ".$val["shop_id"]."  and is_cancelled = 0 and DATE(`created_datetime`) = DATE(NOW() -INTERVAL 1 Year) GROUP BY `shop_user`.shop_id order by `shop_user`.shop_id");
            $SaleLastYear =   sizeofgf($rsSaleLastYear) > 0 ? $rsSaleLastYear[0]["antal"] : "0";

            $rsSumMonth = Dbsqli::getSql2("SELECT COUNT(`shop_user`.`id`) as antal FROM `shop_user` inner join company_order on `shop_user`.company_order_id = company_order.id WHERE `shop_user`.`blocked` = 0 and `shop_user`.`shop_id` = ".$val["shop_id"]."  and is_cancelled = 0 and  MONTH(`created_datetime`) = MONTH(CURRENT_DATE()) AND YEAR(`created_datetime`) = YEAR(CURRENT_DATE())  GROUP BY `shop_user`.shop_id order by `shop_user`.shop_id");
            $saleMonth  = sizeofgf($rsSumMonth) > 0 ? $rsSumMonth[0]["antal"] : "0";

            $rsSumMonthLastYear = Dbsqli::getSql2("SELECT COUNT(`shop_user`.`id`) as antal FROM gavefabrikken_2019.`shop_user` inner join gavefabrikken_2019.company_order on `shop_user`.company_order_id = company_order.id WHERE `shop_user`.`blocked` = 0 and `shop_user`.`shop_id` = ".$val["shop_id"]."  and is_cancelled = 0 and MONTH(`created_datetime`) = MONTH(CURRENT_DATE()-INTERVAL 1 Year) AND YEAR(`created_datetime`) = YEAR(CURRENT_DATE()-INTERVAL 1 Year) GROUP BY `shop_user`.shop_id order by `shop_user`.shop_id");
            $saleMonthLastYear  = sizeofgf($rsSumMonthLastYear) > 0 ? $rsSumMonthLastYear[0]["antal"] : "0";


            $rsSaleLastYearToDate = Dbsqli::getSql2("SELECT COUNT(`shop_user`.`id`) as antal FROM gavefabrikken_2019.`shop_user` inner join gavefabrikken_2019.company_order on `shop_user`.company_order_id = company_order.id WHERE `shop_user`.`blocked` = 0 and `shop_user`.`shop_id` = ".$val["shop_id"]."  and is_cancelled = 0 and DATE(`created_datetime`) <= DATE(NOW()-INTERVAL 1 Year) GROUP BY `shop_user`.shop_id order by `shop_user`.shop_id");
            $SaleLastYearToDate =   sizeofgf($rsSaleLastYearToDate) > 0 ? $rsSaleLastYearToDate[0]["antal"] : "0";

                echo "<tr><td>".$val["name"]."</td><td>".$SaleTotalThisYear."</td><td>".$val["antal"]."</td><td>".$SaleLastYearToDate."</td><td>".$saleMonth."</td><td>".$saleMonthLastYear."</td><td>".$SaleThisYear."</td><td>".$SaleLastYear."</td></tr>";
                $sum+= $val["antal"];
                $sumLastYear+= $SaleLastYearToDate;
                $sumToday+= $SaleThisYear;
                $sumTodayLastYear+= $SaleLastYear;
                $sumMonth += $saleMonth;
                $sumMonthLastYear += $saleMonthLastYear;
                $SaleThisYear2018+= $SaleTotalThisYear;
            }

          echo "<tr><td><b>Total</b></td><td><b>".$SaleThisYear2018."</b></td><td><b>".$sum."</b></td><td><b>".$sumLastYear."</b></td><td><b>".$sumMonth."</b></td><td><b>".$sumMonthLastYear."</b></td><td><b>".$sumToday."</b></td><td><b>".$sumTodayLastYear."</b></td></tr>";

          echo "</table><hr>";

          // -----------------------  norge -------------------
                   echo "<h2>Norske shops</h2>";




          $sql = "SELECT COUNT(*) as antal, shop.name, `shop_user`.shop_id  FROM `shop_user`
            inner join shop on shop.id = shop_user.shop_id
            inner join company_order on `shop_user`.company_order_id = company_order.id
            WHERE `shop_user`.`shop_id` in (574,57,58,59,272 ) AND shop_user.blocked = 0 and is_cancelled = 0 group by `shop_user`.shop_id";
          $rsAll = Dbsqli::getSql2($sql);
          $sum = 0;
          $sumLastYear = 0;
          $sumToday = 0;
          $sumTodayLastYear = 0;
          $sumMonth = 0;
          $sumMonthLastYear = 0;
          $SaleThisYear2018 = 0;
          echo "<table width= 300>";
          echo utf8_encode("<tr><th>Shop</th><th>Total solgte 2019</th><th>Antal solgte totalt 2020</th><th>Antal solgte totalt til og med samme dag, 2019</th><th>Antal solgte indev�rende m�ned 2020</th><th>Antal solgte indev�rende m�ned 2019</th><th>Antal solgte i dag 2020</th><th>Antal solgte i dag 2019 </th></tr>");
       foreach($rsAll as $key=>$val){

            $rsTotalSaleThisYear = Dbsqli::getSql2("SELECT COUNT(`shop_user`.`id`) as antal FROM gavefabrikken_2019.`shop_user` inner join gavefabrikken_2019.company_order on `shop_user`.company_order_id = company_order.id WHERE `shop_user`.`blocked` = 0 and `shop_user`.`shop_id` = ".$val["shop_id"]."  and is_cancelled = 0 GROUP BY `shop_user`.shop_id order by `shop_user`.shop_id");
            $SaleTotalThisYear =   sizeofgf($rsTotalSaleThisYear) > 0 ? $rsTotalSaleThisYear[0]["antal"] : "0";

            $rsSaleThisYear = Dbsqli::getSql2("SELECT COUNT(`shop_user`.`id`) as antal FROM `shop_user` inner join company_order on `shop_user`.company_order_id = company_order.id WHERE `shop_user`.`blocked` = 0 and `shop_user`.`shop_id` = ".$val["shop_id"]."  and is_cancelled = 0 and DATE(`created_datetime`) = DATE(NOW()) GROUP BY `shop_user`.shop_id order by `shop_user`.shop_id");
            $SaleThisYear =   sizeofgf($rsSaleThisYear) > 0 ? $rsSaleThisYear[0]["antal"] : "0";

            $rsSaleLastYear = Dbsqli::getSql2("SELECT COUNT(`shop_user`.`id`) as antal FROM gavefabrikken_2019.`shop_user` inner join gavefabrikken_2019.company_order on `shop_user`.company_order_id = company_order.id WHERE `shop_user`.`blocked` = 0 and `shop_user`.`shop_id` = ".$val["shop_id"]."  and is_cancelled = 0 and DATE(`created_datetime`) =  '".$this->getSameDayInWeekLastYear()."' GROUP BY `shop_user`.shop_id order by `shop_user`.shop_id");
         //   $rsSaleLastYear = Dbsqli::getSql2("SELECT COUNT(`shop_user`.`id`) as antal FROM gavefabrikken_2019.`shop_user` inner join gavefabrikken_2019.company_order on `shop_user`.company_order_id = company_order.id WHERE `shop_user`.`blocked` = 0 and `shop_user`.`shop_id` = ".$val["shop_id"]."  and is_cancelled = 0 and DATE(`created_datetime`) = DATE(NOW() -INTERVAL 1 Year) GROUP BY `shop_user`.shop_id order by `shop_user`.shop_id");
            $SaleLastYear =   sizeofgf($rsSaleLastYear) > 0 ? $rsSaleLastYear[0]["antal"] : "0";

            $rsSumMonth = Dbsqli::getSql2("SELECT COUNT(`shop_user`.`id`) as antal FROM `shop_user` inner join company_order on `shop_user`.company_order_id = company_order.id WHERE `shop_user`.`blocked` = 0 and `shop_user`.`shop_id` = ".$val["shop_id"]."  and is_cancelled = 0 and MONTH(`created_datetime`) = MONTH(CURRENT_DATE()) AND YEAR(`created_datetime`) = YEAR(CURRENT_DATE()) GROUP BY `shop_user`.shop_id order by `shop_user`.shop_id");
            $saleMonth  = sizeofgf($rsSumMonth) > 0 ? $rsSumMonth[0]["antal"] : "0";

            $rsSumMonthLastYear = Dbsqli::getSql2("SELECT COUNT(`shop_user`.`id`) as antal FROM gavefabrikken_2019.`shop_user` inner join gavefabrikken_2019.company_order on `shop_user`.company_order_id = company_order.id WHERE `shop_user`.`blocked` = 0 and `shop_user`.`shop_id` = ".$val["shop_id"]."  and is_cancelled = 0 and MONTH(`created_datetime`) = MONTH(CURRENT_DATE()-INTERVAL 1 Year) AND YEAR(`created_datetime`) = YEAR(CURRENT_DATE()-INTERVAL 1 Year) GROUP BY `shop_user`.shop_id order by `shop_user`.shop_id");
            $saleMonthLastYear  = sizeofgf($rsSumMonthLastYear) > 0 ? $rsSumMonthLastYear[0]["antal"] : "0";


            $rsSaleLastYearToDate = Dbsqli::getSql2("SELECT COUNT(`shop_user`.`id`) as antal FROM gavefabrikken_2019.`shop_user` inner join gavefabrikken_2019.company_order on `shop_user`.company_order_id = company_order.id WHERE `shop_user`.`blocked` = 0 and `shop_user`.`shop_id` = ".$val["shop_id"]."  and is_cancelled = 0 and DATE(`created_datetime`) <= DATE(NOW()-INTERVAL 1 Year) GROUP BY `shop_user`.shop_id order by `shop_user`.shop_id");
            $SaleLastYearToDate =   sizeofgf($rsSaleLastYearToDate) > 0 ? $rsSaleLastYearToDate[0]["antal"] : "0";

                echo "<tr><td>".$val["name"]."</td><td>".$SaleTotalThisYear."</td><td>".$val["antal"]."</td><td>".$SaleLastYearToDate."</td><td>".$saleMonth."</td><td>".$saleMonthLastYear."</td><td>".$SaleThisYear."</td><td>".$SaleLastYear."</td></tr>";
                $sum+= $val["antal"];
                $sumLastYear+= $SaleLastYearToDate;
                $sumToday+= $SaleThisYear;
                $sumTodayLastYear+= $SaleLastYear;
                $sumMonth += $saleMonth;
                $sumMonthLastYear += $saleMonthLastYear;
                $SaleThisYear2018+= $SaleTotalThisYear;
            }
            echo "<tr><td><b>Total</b></td><td><b>".$SaleThisYear2018."</b></td><td><b>".$sum."</b></td><td><b>".$sumLastYear."</b></td><td><b>".$sumMonth."</b></td><td><b>".$sumMonthLastYear."</b></td><td><b>".$sumToday."</b></td><td><b>".$sumTodayLastYear."</b></td></tr>";
              echo "</table><hr>";

             // -----------------------  norge -------------------
                   echo "<h2>Svenske shops</h2>";




          $sql = "SELECT COUNT(*) as antal, shop.name, `shop_user`.shop_id  FROM `shop_user`
            inner join shop on shop.id = shop_user.shop_id
            inner join company_order on `shop_user`.company_order_id = company_order.id
            WHERE `shop_user`.`shop_id` in (1832  ) AND shop_user.blocked = 0 and is_cancelled = 0 group by `shop_user`.shop_id";
          $rsAll = Dbsqli::getSql2($sql);
          $sum = 0;
          $sumLastYear = 0;
          $sumToday = 0;
          $sumTodayLastYear = 0;
          $sumMonth = 0;
          $sumMonthLastYear = 0;
          $SaleThisYear2018 = 0;
          echo "<table width= 300>";
          echo utf8_encode("<tr><th>Shop</th><th>Total solgte 2019</th><th>Antal solgte totalt 2020</th><th>Antal solgte totalt til og med samme dag, 2019</th><th>Antal solgte indev�rende m�ned 2020</th><th>Antal solgte indev�rende m�ned 2019</th><th>Antal solgte i dag 2020</th><th>Antal solgte i dag 2019 </th></tr>");
       foreach($rsAll as $key=>$val){

            $rsTotalSaleThisYear = Dbsqli::getSql2("SELECT COUNT(`shop_user`.`id`) as antal FROM gavefabrikken_2019.`shop_user` inner join gavefabrikken_2019.company_order on `shop_user`.company_order_id = company_order.id WHERE `shop_user`.`blocked` = 0 and `shop_user`.`shop_id` = ".$val["shop_id"]."  and is_cancelled = 0 GROUP BY `shop_user`.shop_id order by `shop_user`.shop_id");
            $SaleTotalThisYear =   sizeofgf($rsTotalSaleThisYear) > 0 ? $rsTotalSaleThisYear[0]["antal"] : "0";

            $rsSaleThisYear = Dbsqli::getSql2("SELECT COUNT(`shop_user`.`id`) as antal FROM `shop_user` inner join company_order on `shop_user`.company_order_id = company_order.id WHERE `shop_user`.`blocked` = 0 and `shop_user`.`shop_id` = ".$val["shop_id"]."  and is_cancelled = 0 and DATE(`created_datetime`) = DATE(NOW()) GROUP BY `shop_user`.shop_id order by `shop_user`.shop_id");
            $SaleThisYear =   sizeofgf($rsSaleThisYear) > 0 ? $rsSaleThisYear[0]["antal"] : "0";

            $rsSaleLastYear = Dbsqli::getSql2("SELECT COUNT(`shop_user`.`id`) as antal FROM gavefabrikken_2019.`shop_user` inner join gavefabrikken_2019.company_order on `shop_user`.company_order_id = company_order.id WHERE `shop_user`.`blocked` = 0 and `shop_user`.`shop_id` = ".$val["shop_id"]."  and is_cancelled = 0 and DATE(`created_datetime`) =  '".$this->getSameDayInWeekLastYear()."' GROUP BY `shop_user`.shop_id order by `shop_user`.shop_id");
         //   $rsSaleLastYear = Dbsqli::getSql2("SELECT COUNT(`shop_user`.`id`) as antal FROM gavefabrikken_2019.`shop_user` inner join gavefabrikken_2019.company_order on `shop_user`.company_order_id = company_order.id WHERE `shop_user`.`blocked` = 0 and `shop_user`.`shop_id` = ".$val["shop_id"]."  and is_cancelled = 0 and DATE(`created_datetime`) = DATE(NOW() -INTERVAL 1 Year) GROUP BY `shop_user`.shop_id order by `shop_user`.shop_id");
            $SaleLastYear =   sizeofgf($rsSaleLastYear) > 0 ? $rsSaleLastYear[0]["antal"] : "0";

            $rsSumMonth = Dbsqli::getSql2("SELECT COUNT(`shop_user`.`id`) as antal FROM `shop_user` inner join company_order on `shop_user`.company_order_id = company_order.id WHERE `shop_user`.`blocked` = 0 and `shop_user`.`shop_id` = ".$val["shop_id"]."  and is_cancelled = 0 and MONTH(`created_datetime`) = MONTH(CURRENT_DATE()) AND YEAR(`created_datetime`) = YEAR(CURRENT_DATE()) GROUP BY `shop_user`.shop_id order by `shop_user`.shop_id");
            $saleMonth  = sizeofgf($rsSumMonth) > 0 ? $rsSumMonth[0]["antal"] : "0";

            $rsSumMonthLastYear = Dbsqli::getSql2("SELECT COUNT(`shop_user`.`id`) as antal FROM gavefabrikken_2019.`shop_user` inner join gavefabrikken_2019.company_order on `shop_user`.company_order_id = company_order.id WHERE `shop_user`.`blocked` = 0 and `shop_user`.`shop_id` = ".$val["shop_id"]."  and is_cancelled = 0 and MONTH(`created_datetime`) = MONTH(CURRENT_DATE()-INTERVAL 1 Year) AND YEAR(`created_datetime`) = YEAR(CURRENT_DATE()-INTERVAL 1 Year) GROUP BY `shop_user`.shop_id order by `shop_user`.shop_id");
            $saleMonthLastYear  = sizeofgf($rsSumMonthLastYear) > 0 ? $rsSumMonthLastYear[0]["antal"] : "0";


            $rsSaleLastYearToDate = Dbsqli::getSql2("SELECT COUNT(`shop_user`.`id`) as antal FROM gavefabrikken_2019.`shop_user` inner join gavefabrikken_2019.company_order on `shop_user`.company_order_id = company_order.id WHERE `shop_user`.`blocked` = 0 and `shop_user`.`shop_id` = ".$val["shop_id"]."  and is_cancelled = 0 and DATE(`created_datetime`) <= DATE(NOW()-INTERVAL 1 Year) GROUP BY `shop_user`.shop_id order by `shop_user`.shop_id");
            $SaleLastYearToDate =   sizeofgf($rsSaleLastYearToDate) > 0 ? $rsSaleLastYearToDate[0]["antal"] : "0";

                echo "<tr><td>".$val["name"]."</td><td>".$SaleTotalThisYear."</td><td>".$val["antal"]."</td><td>".$SaleLastYearToDate."</td><td>".$saleMonth."</td><td>".$saleMonthLastYear."</td><td>".$SaleThisYear."</td><td>".$SaleLastYear."</td></tr>";
                $sum+= $val["antal"];
                $sumLastYear+= $SaleLastYearToDate;
                $sumToday+= $SaleThisYear;
                $sumTodayLastYear+= $SaleLastYear;
                $sumMonth += $saleMonth;
                $sumMonthLastYear += $saleMonthLastYear;
                $SaleThisYear2018+= $SaleTotalThisYear;
            }
            echo "<tr><td><b>Total</b></td><td><b>".$SaleThisYear2018."</b></td><td><b>".$sum."</b></td><td><b>".$sumLastYear."</b></td><td><b>".$sumMonth."</b></td><td><b>".$sumMonthLastYear."</b></td><td><b>".$sumToday."</b></td><td><b>".$sumTodayLastYear."</b></td></tr>";
              echo "</table><hr>";
   }






    public function getStats(){
      // get not used card
      $shop_id = $_POST["shop_id"];
      $deadline = $_POST["deadline"];

      if($_POST["shop_id"] == "0"){
            $sqlNotSelect = "SELECT COUNT(*) as antal  FROM `shop_user` WHERE `shop_id` in ( select id from shop where  is_gift_certificate = 1 ) and `shop_user`.blocked = 0";
      } else {
            $sqlNotSelect = "SELECT COUNT(*) as antal  FROM `shop_user` WHERE `shop_id` = ".$shop_id. " AND `expire_date` = '".$deadline."' AND blocked = 0";
      }


      $rsNotSelect = Dbsqli::getSql2($sqlNotSelect);
      if(sizeofgf($rsNotSelect) <= 0 ){
        echo "ingen data";
        return;
      }

      $allCard = $rsNotSelect[0]["antal"];
      if($_POST["shop_id"] == "0"){
            $sql = "SELECT shop_user.expire_date, count(present_id) as total, present_model_id, `present_id`, `present_name`,present_model_name FROM `order` INNER join shop_user on shop_user.username = order.user_username WHERE order.shop_id in in ( select id from shop where  is_gift_certificate = 1 ) and shop_user.blocked = 0 group by present_id,present_model_id  order by total desc";
      } else {
          $sql = "SELECT shop_user.expire_date, count(present_id) as total, present_model_id, `present_id`, `present_name`,present_model_name FROM `order` INNER join shop_user on shop_user.username = order.user_username WHERE order.shop_id = ".$shop_id." and shop_user.expire_date =  '".$deadline."' and shop_user.blocked = 0 group by present_id,present_model_id  order by total desc";
      }


      $rs = Dbsqli::getSql2($sql);


      $total = 0;
      $totalProcent = 0;

      foreach($rs as $dataRow){
          $total+= $dataRow["total"]*1;
      }
      $notSelect = $allCard - $total;

      $html = "<p>Antal der mangler at v&oelig;lge: ".$notSelect."</p>";
      $html.= "<p>Total antal kort: ".$allCard."</p>";
      $html.= "<table><tr><th>Deadline</th><th>ID</th><th>Antal valgte gaver</th><th>Antal i %</th><th>Fremskrevet v&oelig;rdi</th><th>Gave</th><th>Model</th><th>Varenr</th></tr>";
      foreach($rs as $dataRow){
          $procent =  ($dataRow["total"] / $total ) *100;
        $procent = round($procent,2);
        $totalProcent+= $procent;
        $guess =  ($notSelect * $procent) / 100 ;
        $guess =  round($guess) + $dataRow["total"];
        $inputId =  $dataRow["present_id"]."_".base64_encode($dataRow["present_model_name"]);
        $inputId = str_replace("=","",$inputId);
        $modelSql = "select model_name, model_no,model_present_no  from present_model where language_id = 1 and model_id= ".$dataRow["present_model_id"];
        $rsModel = Dbsqli::getSql2($modelSql);
        $dataRow["present_model_name"] = $rsModel[0]["model_name"];
        $modelNavn = $rsModel[0]["model_no"];
        $varenr =  $rsModel[0]["model_present_no"];


        $modelBase64 = base64_encode($dataRow["present_model_name"]);
        //<td><input id='".$inputId."' type=\"number\" /></td><td><button onclick=\"updateStuck('".$dataRow["present_id"]."','".$inputId."','".$modelBase64."')\">Gem</button></td>

         $html.= "<tr><td>".$dataRow["expire_date"]."</td><td>".$dataRow["present_id"]."</td><td  id='val_".$inputId."' >".$dataRow["total"]."</td><td>".$procent."%</td><td>".$guess."</td><td>".str_replace("###"," - ",$dataRow["present_model_name"])."</td> <td>".$modelNavn."</td><td>".$varenr."</td></tr>";
        //$html.= "<tr><td>".$dataRow["expire_date"]."</td><td>".$dataRow["present_id"]."</td><td>".$dataRow["total"]."</td><td>".$procent."%</td><td>".$guess."</td><td>".$dataRow["present_name"]."</td><td>".str_replace("###"," - ",$dataRow["present_model_name"])."</td></tr>";
      }
      $html.= "<tr style=\"font-size:16px;\"><td></td><td>Totale antal</td><td>".$total."</td><td>".$totalProcent."%</td><td></td><td></td><td></td><td></td><td></td></tr>";
      $html.= "</table>";
      echo $html;
    }
    public function updateStats()
    {

      $sql = "select * from stock_reservation where present_id = ".$_POST["present_id"]." and model_id = '".$_POST["model_id"]."' and shop_id = ".$_POST["shopId"]."  and card_deadline = '".$_POST["deadline"]."' and active = 1";
      $rs = Dbsqli::getSql2($sql);
      if(sizeofgf($rs) == 0 ){
         $sql = "INSERT INTO stock_reservation (quantity,present_id,model_id,model_id_base64,shop_id,card_deadline) VALUES ( ".$_POST["quantity"].",".$_POST["present_id"].",'".$_POST["model_id"]."','".$_POST["model_id_base64"]."',".$_POST["shopId"].",'".$_POST["deadline"]."')";
         $rs = Dbsqli::setSql2($sql);
         if($rs){
           echo $_POST["quantity"];
         } else {
           echo "error";
         }
      } else {
        $id = $rs[0]["id"];
        $sql = "update stock_reservation set quantity = ".$_POST["quantity"]." where id = ".$id;
        $rs = Dbsqli::setSql2($sql);
        $sql = "select quantity from stock_reservation where id = ".$id;
        $rs = Dbsqli::getSql2($sql);
        echo  $rs[0]["quantity"];
      }

    }
    public function getStatsData()
    {
        if($_POST["shopId"] == "0"){
            $sql = "select quantity,model_id from stock_reservation where shop_id in( select id from shop where  is_gift_certificate = 1 )  and active = 1";
        } else {
            $sql = "select quantity,model_id from stock_reservation where shop_id = ".$_POST["shopId"]."  and card_deadline = '".$_POST["deadline"]."' and active = 1";
        }

        $rs = Dbsqli::getSql2($sql);
        echo json_encode($rs);
    }
    public function getStatsDataAll()
    {
        $sql = "select quantity,model_id from stock_reservation where shop_id = ".$_POST["shopId"]." and active = 1";
        $rs = Dbsqli::getSql2($sql);
        echo json_encode($rs);
    }
    public function getSameDayInWeekLastYear(){
        $sql = "";
        $rs = Dbsqli::getSql2("SELECT WEEKOFYEAR(NOW()) as thisyear");
        $thisYearWeekNr =  $rs[0]["thisyear"];
        $rs = Dbsqli::getSql2("select WEEKOFYEAR(DATE(NOW() -INTERVAL 1 Year)) as lastyear");
        $lastYearWeekNr =  $rs[0]["lastyear"];
        $rs = Dbsqli::getSql2("SELECT WEEKDAY(NOW()) as thisyear");
        $thisYearDayNr =  $rs[0]["thisyear"];
        $rs = Dbsqli::getSql2("select WEEKDAY(DATE(NOW() -INTERVAL 1 Year)) as lastyear");
        $lastYearDayNr =  $rs[0]["lastyear"];

        $intervalDay;
        //echo $thisYearWeekNr."--".$lastYearWeekNr."<br><br>";
        //echo $thisYearDayNr."--".$lastYearDayNr."<br><br>";
        if($thisYearWeekNr == $lastYearWeekNr){
            $intervalDay =   ($lastYearDayNr*1) - ($thisYearDayNr*1);
        }
        if($thisYearWeekNr < $lastYearWeekNr){
            $intervalDay =   (7-($lastYearDayNr*1)) + ($thisYearDayNr*1);
        }
        if($thisYearWeekNr > $lastYearWeekNr){
            $intervalDay =   ((7-($lastYearDayNr*1)) + ($thisYearDayNr*1))*-1 ;
        }
        //echo "<br><br>";
        $rs = Dbsqli::getSql2("SELECT DATE_SUB(DATE(NOW() -INTERVAL 1 Year), INTERVAL ".$intervalDay." DAY) as correctionDate");
        return explode(" ", $rs[0]["correctionDate"])[0];
    }



}
?>
