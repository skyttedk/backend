<?php
// Controller media
// Date created  Wed, 06 Apr 2016 09:39:56 +0200
// Created by Bitworks
class stats2020Controller  {
  public function Index() {
  }

  public function test(){
    echo "hej";
  }
  public function loginStats(){
     $totalLogin = 0;
     $totalGavevalg = 0;
     $totalRequest = 0;
     echo "<script>setTimeout(function() {
  location.reload();
}, 10000); </script>";
    if($_GET["token"] == "saddsfsdflkfj489fyth"){

        echo "<h3>CPU in percent of cores used (5 min avg):<u> ".$this->get_server_cpu_usage()."</u></h3>";

        $sql = "select day(`created_datetime`) as Day, hour(`created_datetime`) as Hour, count(*) as Count  FROM gavefabrikken.`system_log` WHERE `controller` LIKE 'login' AND `action` LIKE 'loginShopUserByToken' AND `created_datetime` > SUBDATE(NOW(),1) group by day(created_datetime), hour(created_datetime) ORDER BY `id`  DESC ";
        $rs = Dbsqli::getSql2($sql);
        $sql2 = "select day(`order_timestamp`) as Day, hour(`order_timestamp`) as Hour, count(*) as Count  FROM gavefabrikken.`order` WHERE  `order_timestamp` > SUBDATE(NOW(),1) group by day(`order_timestamp`), hour(`order_timestamp`) ORDER BY `id`  DESC";
        $rs2 = Dbsqli::getSql2($sql2);
        $sql3 = "select day(`created_datetime`) as Day, hour(`created_datetime`) as Hour, count(*) as Count  FROM gavefabrikken.`system_log` WHERE `created_datetime` > SUBDATE(NOW(),1) AND `action` not LIKE 'loginStats' group by day(created_datetime), hour(created_datetime) ORDER BY `id`  DESC ";
        $rs3 = Dbsqli::getSql2($sql3);
        echo "<table border=1 width=100% style='font-size:2vh;'><tr><th>Dag</th><th>Time</th><th>Login</th><th>Gavevalg</th><th>Request</th></tr>";
        for($i=0;$i<sizeofgf($rs);$i++){



            $rs2[$i]["Count"] = isset($rs2[$i]["Count"]) ? $rs2[$i]["Count"]:0;
            $rs3[$i]["Count"] = isset($rs3[$i]["Count"]) ? $rs3[$i]["Count"]:0;

            echo "<tr><td>".$rs[$i]["Day"]."</td><td>".$rs[$i]["Hour"]."</td><td>".$rs[$i]["Count"]."</td><td>".$rs2[$i]["Count"]."</td><td>".$rs3[$i]["Count"]."</td></tr>";
            $totalLogin+= $rs[$i]["Count"]*1;
            $totalGavevalg+= $rs2[$i]["Count"]*1;
            $totalRequest+= $rs3[$i]["Count"]*1;
        }
        echo "<tr><td>TOTAL</td><td></td><td><b>".$totalLogin."</b></td><td><b>".$totalGavevalg."</b></td><td><b>".$totalRequest."</b></td></tr>";
        echo "</table>";
    }

  }

  public function loginStatsDev(){
     $totalLogin = 0;
     $totalGavevalg = 0;
     $totalRequest = 0;
     $totalLoginError = 0;
     $totalError = 0;
     echo "<script>setTimeout(function() {
  location.reload();
}, 600000); </script>";
    if($_GET["token"] == "saddsfsdflkfj489fyth"){

        echo "<h3>CPU in percent of cores used (5 min avg):<u> ".$this->get_server_cpu_usage()."</u></h3>";

        $sql = "select day(`created_datetime`) as Day, hour(`created_datetime`) as Hour, count(*) as Count  FROM gavefabrikken_2020.`system_log` WHERE `controller` LIKE 'login' AND `action` LIKE 'loginShopUserByToken' AND `created_datetime` > SUBDATE(NOW(),1) group by day(created_datetime), hour(created_datetime) ORDER BY `id`  DESC ";
        $rs = Dbsqli::getSql2($sql);
        $sql2 = "select day(`order_timestamp`) as Day, hour(`order_timestamp`) as Hour, count(*) as Count  FROM gavefabrikken_2020.`order` WHERE  `order_timestamp` > SUBDATE(NOW(),1) group by day(`order_timestamp`), hour(`order_timestamp`) ORDER BY `id`  DESC";
        $rs2 = Dbsqli::getSql2($sql2);
        $sql3 = "select day(`created_datetime`) as Day, hour(`created_datetime`) as Hour, count(*) as Count  FROM gavefabrikken_2020.`system_log` WHERE `created_datetime` > SUBDATE(NOW(),1) AND `action` not LIKE 'loginStats' group by day(created_datetime), hour(created_datetime) ORDER BY `id`  DESC ";
        $rs3 = Dbsqli::getSql2($sql3);
        $sql4 = "select day(`created_datetime`) as Day, hour(`created_datetime`) as Hour, count(*) as Count  FROM gavefabrikken_2020.`system_log` WHERE `controller` LIKE 'login' AND `action` LIKE 'loginShopUserByToken' and committed = 0 AND `created_datetime` > SUBDATE(NOW(),1) group by day(created_datetime), hour(created_datetime) ORDER BY `id`  DESC ";
        $rs4 = Dbsqli::getSql2($sql4);
        $sql5 = "select day(`created_datetime`) as Day, hour(`created_datetime`) as Hour, count(*) as Count  FROM gavefabrikken_2020.`system_log` WHERE `created_datetime` > SUBDATE(NOW(),1) AND `action` not LIKE 'loginStats' and committed = 0  group by day(created_datetime), hour(created_datetime) ORDER BY `id`  DESC ";
        $rs5 = Dbsqli::getSql2($sql5);



        echo "<table border=1 width=100% style='font-size:2vh;'><tr><th>Dag</th><th>Time</th><th>Login</th><th>Gavevalg</th><th>Request</th><th>Login error</th><th>Error</th></tr>";
        for($i=0;$i<sizeofgf($rs);$i++){
            $LoginError = 0;
            $error = 0;
            try {
               $LoginError = $rs4[$i]["Count"];
             } catch (Exception $e) { }
             try {
                 $error = $rs5[$i]["Count"];
             } catch (Exception $e) { }


            echo "<tr><td>".$rs[$i]["Day"]."</td><td>".$rs[$i]["Hour"]."</td><td>".$rs[$i]["Count"]."</td><td>".$rs2[$i]["Count"]."</td><td>".$rs3[$i]["Count"]."</td><td>".$LoginError."</td><td>".$error."</td></tr>";
            $totalLogin+= $rs[$i]["Count"]*1;
            $totalGavevalg+= $rs2[$i]["Count"]*1;
            $totalRequest+= $rs3[$i]["Count"]*1;
            $totalLoginError+= $LoginError*1;
            $totalError+= $error*1;
        }
        echo "<tr><td>TOTAL</td><td></td><td><b>".$totalLogin."</b></td><td><b>".$totalGavevalg."</b></td><td><b>".$totalRequest."</b></td><td><b>".$totalLoginError."</b></td><td><b>".$totalError."</b></td></tr>";
        echo "</table>";
    }

  }

  private function get_server_cpu_usage(){

   $exec_loads = sys_getloadavg();
   $exec_cores = trimgf(shell_exec("grep -P '^processor' /proc/cpuinfo|wc -l"));
   return round($exec_loads[1]/($exec_cores + 1)*100, 0) . '%';
  }


  public function saleStats(){
    if($_GET["token"] == "saddsfsdflkfj489fyth"){
        $sql = "select day(`created_datetime`) as Day, hour(`created_datetime`) as Hour, count(*) as Count  FROM gavefabrikken_2020.`system_log` WHERE `controller` LIKE 'login' AND `action` LIKE 'loginShopUserByToken' AND `created_datetime` > SUBDATE(NOW(),1) group by day(created_datetime), hour(created_datetime) ORDER BY `id`  DESC";
        $rs = Dbsqli::getSql2($sql);
        echo "<table border=1>";
        foreach($rs as $ele){
            echo "<tr><td>".$ele["Day"]."</td><td>".$ele["Hour"]."</td><td>".$ele["Count"]."</td></tr>";
        }
        echo "</table>";
    }

  }



  public function totalCard(){
      $sqlNotSelect = "SELECT COUNT(*) as antal  FROM gavefabrikken_2020.`shop_user` WHERE `shop_id`  in ( select id from gavefabrikken_2020.shop where  is_gift_certificate = 1 ) AND blocked = 0 ";
      $rsNotSelect = Dbsqli::getSql2($sqlNotSelect);
      echo $rsNotSelect[0]["antal"];
  }

  public function getCsvFile(){

    $this->loadStats($_GET["deadline"],$_GET["shop_id"],true);
  }
  public function getAllStats()
  {
      $this->loadStats($_POST["deadline"],$_POST["shop_id"]);
  }

  public function loadStats($deadline,$shopId,$returnCsv=false)
  {

       $csv = [];
       $expireDateSql = "";
       if($deadline != "alle"){
           $expireDateSql = "and shop_user.expire_date ='".$deadline."'";
       }

       if($shopId == "0"){
              $sqlNotSelect = "SELECT COUNT(*) as antal  FROM gavefabrikken_2020.`shop_user` WHERE `shop_id` in ( select id from shop where  is_gift_certificate = 1 ) AND blocked = 0 ".$expireDateSql." order by antal";
       } else {
              $sqlNotSelect = "SELECT COUNT(*) as antal  FROM gavefabrikken_2020.`shop_user` WHERE `shop_id` = ".$shopId." AND blocked = 0 ".$expireDateSql." order by antal";
       }
      $rsNotSelect = Dbsqli::getSql2($sqlNotSelect);

      if(sizeofgf($rsNotSelect) <= 0 ){
        echo "ingen data";
        return;
      }
       $allCard = $rsNotSelect[0]["antal"];

       if($shopId == "0"){
             $sql = "
            SELECT present_model.model_name, present_model.model_no, present_model.model_present_no,COUNT(`present_model_id`) as antal FROM gavefabrikken_2020.`order`
inner JOIN gavefabrikken_2020.present_model on present_model.model_id = `order`.`present_model_id`
inner JOIN gavefabrikken_2020.shop_user on shop_user.id = `order`.`shopuser_id`
WHERE `shop_is_gift_certificate` = 1 and present_model.language_id = 1 and
shop_user.blocked = 0 AND
shop_user.is_demo = 0 ".$expireDateSql."
GROUP by present_model.model_present_no order by present_model.model_present_no, present_model.model_name,present_model.model_no
            ";
       } else {
            $sql = "
            SELECT present_model.model_name, present_model.model_no, present_model.model_present_no,COUNT(`present_model_id`) as antal FROM gavefabrikken_2020.`order`
inner JOIN gavefabrikken_2020.present_model on present_model.model_id = `order`.`present_model_id`
inner JOIN gavefabrikken_2020.shop_user on shop_user.id = `order`.`shopuser_id`
WHERE `shop_is_gift_certificate` = 1 and present_model.language_id = 1 and
shop_user.blocked = 0 AND
shop_user.is_demo = 0 and
`order`.shop_id = ".$shopId."
".$expireDateSql."
GROUP by present_model.model_present_no order by present_model.model_present_no, present_model.model_name,present_model.model_no";

       }


      $rs = Dbsqli::getSql2($sql);

      $total = 0;
      $totalProcent = 0;

      foreach($rs as $dataRow){

          $total+= $dataRow["antal"]*1;
      }
      $notSelect = $allCard - $total;
      $radomId = $this->generateRandomString();

      $html = "<div class='statsContent'><p>Antal der mangler at v&oelig;lge: ".$notSelect."</p>";
      $html.= "<p>Total antal kort: ".$allCard."</p>";
      $html.= "<table id='".$radomId."'>  <thead><tr><th>Varenr</th><th>Gave</th><th>Model</th><th>Antal valgte gaver</th><th>Antal i %</th><th>Fremskrevet v&oelig;rdi</th></thead></tr>  <tbody>";

      foreach($rs as $dataRow){

        $procent =  ($dataRow["antal"] / $total ) *100;
        $procent = round($procent,2);
        $totalProcent+= $procent;
        $guess =  ($notSelect * $procent) / 100 ;
        $guess =  round($guess) + $dataRow["antal"];

        $html.= "<tr>
            <td>".$dataRow["model_present_no"]."</td>
            <td>".$dataRow["model_name"]."</td>
            <td>".$dataRow["model_no"]."</td>
            <td>".$dataRow["antal"]."</td>
            <td>".$procent."</td>
            <td>".$guess."</td>
        </tr>";
        $csv[] = [$dataRow["model_present_no"],utf8_decode($dataRow["model_name"]),utf8_decode($dataRow["model_no"]),$dataRow["antal"],$procent,$guess];
      }
      $html.= "  </tbody></table>";
      $html.= "<br><div>Totale antal gaver: <b>".$total."</b> (".$totalProcent."%)</div></div>";
      $html.= "<script>setTimeout(function(){  $('#".$radomId."').DataTable({ 'pageLength': 500 });  $('#".$radomId."').fadeIn(400)   }, 400) </script>";

      if($returnCsv)  $this->array_to_csv_download($csv, $filename = "export.csv", $delimiter=";");
      else echo $html;





  }
  function array_to_csv_download($array, $filename = "export.csv", $delimiter=";") {
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
    // tell the browser we want to save it instead of displaying it
    header('Content-Disposition: attachment; filename="'.$filename.'";');
    // make php send the generated csv lines to the browser
    fpassthru($f);
}

   function generateRandomString($length = 10) {
    return substr(str_shuffle(str_repeat($x='abcdefghijklmnopqrstuvwxyzSDDFSGRFTYJFDTRYH', ceil($length/strlen($x)) )),1,$length);
}

   public function cardSale(){
            $time = new DateTime('now');
            $dateLastYeah = $time->modify('-1 year')->format('Y-m-d');

            $sql = "SELECT COUNT(*) as antal, shop.name, `shop_user`.shop_id  FROM gavefabrikken_2020.`shop_user`
            inner join gavefabrikken_2020.shop on shop.id = shop_user.shop_id
            inner join gavefabrikken_2020.company_order on `shop_user`.company_order_id = company_order.id
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
    //      echo "<div >".$this->getSameDayInWeekLastYear()."</div>";
          echo "<h2>Danske shops</h2>";
          echo "<table width= 300>";
          echo utf8_encode("<tr><th>Shop</th><th>Total solgte 2019</th><th>Antal solgte totalt 2020</th><th>Antal solgte totalt til og med samme dag, 2019</th><th>Antal solgte indev�rende m�ned 2020</th><th>Antal solgte indev�rende m�ned 2019</th><th>Antal solgte i dag 2020</th><th>Antal solgte i dag 2019</th></tr>");


          foreach($rsAll as $key=>$val){

            $rsTotalSaleThisYear = Dbsqli::getSql2("SELECT COUNT(`shop_user`.`id`) as antal FROM gavefabrikken_2019.`shop_user` inner join gavefabrikken_2019.company_order on `shop_user`.company_order_id = company_order.id WHERE `shop_user`.`blocked` = 0 and `shop_user`.`shop_id` = ".$val["shop_id"]."  and is_cancelled = 0 GROUP BY `shop_user`.shop_id order by `shop_user`.shop_id");
            $SaleTotalThisYear =   sizeofgf($rsTotalSaleThisYear) > 0 ? $rsTotalSaleThisYear[0]["antal"] : "0";

            $rsSaleThisYear = Dbsqli::getSql2("SELECT COUNT(`shop_user`.`id`) as antal FROM gavefabrikken_2020.`shop_user` inner join company_order on `shop_user`.company_order_id = company_order.id WHERE `shop_user`.`blocked` = 0 and `shop_user`.`shop_id` = ".$val["shop_id"]."  and is_cancelled = 0 and DATE(`created_datetime`) = DATE(NOW()) GROUP BY `shop_user`.shop_id order by `shop_user`.shop_id");
            $SaleThisYear =   sizeofgf($rsSaleThisYear) > 0 ? $rsSaleThisYear[0]["antal"] : "0";
//                                               echo "SELECT COUNT(`shop_user`.`id`) as antal FROM gavefabrikken_2019.`shop_user` inner join gavefabrikken_2019.company_order on `shop_user`.company_order_id = company_order.id WHERE `shop_user`.`blocked` = 0 and `shop_user`.`shop_id` = ".$val["shop_id"]."  and is_cancelled = 0 and DATE(`created_datetime`) = ".$this->getSameDayInWeekLastYear()." GROUP BY `shop_user`.shop_id order by `shop_user`.shop_id <br><br>";
            $rsSaleLastYear = Dbsqli::getSql2("SELECT COUNT(`shop_user`.`id`) as antal FROM gavefabrikken_2019.`shop_user` inner join gavefabrikken_2019.company_order on `shop_user`.company_order_id = company_order.id WHERE `shop_user`.`blocked` = 0 and `shop_user`.`shop_id` = ".$val["shop_id"]."  and is_cancelled = 0 and DATE(`created_datetime`) = '".$this->getSameDayInWeekLastYear()."' GROUP BY `shop_user`.shop_id order by `shop_user`.shop_id");
//            $rsSaleLastYear = Dbsqli::getSql2("SELECT COUNT(`shop_user`.`id`) as antal FROM gavefabrikken_2019.`shop_user` inner join gavefabrikken_2019.company_order on `shop_user`.company_order_id = company_order.id WHERE `shop_user`.`blocked` = 0 and `shop_user`.`shop_id` = ".$val["shop_id"]."  and is_cancelled = 0 and DATE(`created_datetime`) = DATE(NOW() -INTERVAL 1 Year) GROUP BY `shop_user`.shop_id order by `shop_user`.shop_id");
            $SaleLastYear =   sizeofgf($rsSaleLastYear) > 0 ? $rsSaleLastYear[0]["antal"] : "0";

            $rsSumMonth = Dbsqli::getSql2("SELECT COUNT(`shop_user`.`id`) as antal FROM gavefabrikken_2020.`shop_user` inner join company_order on `shop_user`.company_order_id = company_order.id WHERE `shop_user`.`blocked` = 0 and `shop_user`.`shop_id` = ".$val["shop_id"]."  and is_cancelled = 0 and  MONTH(`created_datetime`) = MONTH(CURRENT_DATE()) AND YEAR(`created_datetime`) = YEAR(CURRENT_DATE())  GROUP BY `shop_user`.shop_id order by `shop_user`.shop_id");
            $saleMonth  =   sizeofgf($rsSumMonth) > 0 ? $rsSumMonth[0]["antal"] : "0";

            $rsSumMonthLastYear = Dbsqli::getSql2("SELECT COUNT(`shop_user`.`id`) as antal FROM gavefabrikken_2019.`shop_user` inner join gavefabrikken_2019.company_order on `shop_user`.company_order_id = company_order.id WHERE `shop_user`.`blocked` = 0 and `shop_user`.`shop_id` = ".$val["shop_id"]."  and is_cancelled = 0 and MONTH(`created_datetime`) = MONTH(CURRENT_DATE()-INTERVAL 1 Year) AND YEAR(`created_datetime`) = YEAR(CURRENT_DATE()-INTERVAL 1 Year) GROUP BY `shop_user`.shop_id order by `shop_user`.shop_id");
            $saleMonthLastYear  =  sizeofgf($rsSumMonthLastYear) > 0 ? $rsSumMonthLastYear[0]["antal"] : "0";


            $rsSaleLastYearToDate = Dbsqli::getSql2("SELECT COUNT(`shop_user`.`id`) as antal FROM gavefabrikken_2019.`shop_user` inner join gavefabrikken_2019.company_order on `shop_user`.company_order_id = company_order.id WHERE `shop_user`.`blocked` = 0 and `shop_user`.`shop_id` = ".$val["shop_id"]."  and is_cancelled = 0 and DATE(`created_datetime`) <=  '".$dateLastYeah."' GROUP BY `shop_user`.shop_id order by `shop_user`.shop_id");
            $SaleLastYearToDate =  sizeofgf($rsSaleLastYearToDate) > 0 ? $rsSaleLastYearToDate[0]["antal"] : "0";

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




          $sql = "SELECT COUNT(*) as antal, shop.name, `shop_user`.shop_id  FROM gavefabrikken_2020.`shop_user`
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

            $rsSaleThisYear = Dbsqli::getSql2("SELECT COUNT(`shop_user`.`id`) as antal FROM gavefabrikken_2020.`shop_user` inner join company_order on `shop_user`.company_order_id = company_order.id WHERE `shop_user`.`blocked` = 0 and `shop_user`.`shop_id` = ".$val["shop_id"]."  and is_cancelled = 0 and DATE(`created_datetime`) = DATE(NOW()) GROUP BY `shop_user`.shop_id order by `shop_user`.shop_id");
            $SaleThisYear =   sizeofgf($rsSaleThisYear) > 0 ? $rsSaleThisYear[0]["antal"] : "0";

            $rsSaleLastYear = Dbsqli::getSql2("SELECT COUNT(`shop_user`.`id`) as antal FROM gavefabrikken_2019.`shop_user` inner join gavefabrikken_2019.company_order on `shop_user`.company_order_id = company_order.id WHERE `shop_user`.`blocked` = 0 and `shop_user`.`shop_id` = ".$val["shop_id"]."  and is_cancelled = 0 and DATE(`created_datetime`) =  '".$this->getSameDayInWeekLastYear()."' GROUP BY `shop_user`.shop_id order by `shop_user`.shop_id");
         //   $rsSaleLastYear = Dbsqli::getSql2("SELECT COUNT(`shop_user`.`id`) as antal FROM gavefabrikken_2019.`shop_user` inner join gavefabrikken_2019.company_order on `shop_user`.company_order_id = company_order.id WHERE `shop_user`.`blocked` = 0 and `shop_user`.`shop_id` = ".$val["shop_id"]."  and is_cancelled = 0 and DATE(`created_datetime`) = DATE(NOW() -INTERVAL 1 Year) GROUP BY `shop_user`.shop_id order by `shop_user`.shop_id");
            $SaleLastYear =   sizeofgf($rsSaleLastYear) > 0 ? $rsSaleLastYear[0]["antal"] : "0";

            $rsSumMonth = Dbsqli::getSql2("SELECT COUNT(`shop_user`.`id`) as antal FROM gavefabrikken_2020.`shop_user` inner join company_order on `shop_user`.company_order_id = company_order.id WHERE `shop_user`.`blocked` = 0 and `shop_user`.`shop_id` = ".$val["shop_id"]."  and is_cancelled = 0 and MONTH(`created_datetime`) = MONTH(CURRENT_DATE()) AND YEAR(`created_datetime`) = YEAR(CURRENT_DATE()) GROUP BY `shop_user`.shop_id order by `shop_user`.shop_id");
            $saleMonth  = sizeofgf($rsSumMonth) > 0 ? $rsSumMonth[0]["antal"] : "0";

            $rsSumMonthLastYear = Dbsqli::getSql2("SELECT COUNT(`shop_user`.`id`) as antal FROM gavefabrikken_2019.`shop_user` inner join gavefabrikken_2019.company_order on `shop_user`.company_order_id = company_order.id WHERE `shop_user`.`blocked` = 0 and `shop_user`.`shop_id` = ".$val["shop_id"]."  and is_cancelled = 0 and MONTH(`created_datetime`) = MONTH(CURRENT_DATE()-INTERVAL 1 Year) AND YEAR(`created_datetime`) = YEAR(CURRENT_DATE()-INTERVAL 1 Year) GROUP BY `shop_user`.shop_id order by `shop_user`.shop_id");
            $saleMonthLastYear  = sizeofgf($rsSumMonthLastYear) > 0 ? $rsSumMonthLastYear[0]["antal"] : "0";


            $rsSaleLastYearToDate = Dbsqli::getSql2("SELECT COUNT(`shop_user`.`id`) as antal FROM gavefabrikken_2019.`shop_user` inner join gavefabrikken_2019.company_order on `shop_user`.company_order_id = company_order.id WHERE `shop_user`.`blocked` = 0 and `shop_user`.`shop_id` = ".$val["shop_id"]."  and is_cancelled = 0 and DATE(`created_datetime`) <= '".$dateLastYeah."' GROUP BY `shop_user`.shop_id order by `shop_user`.shop_id");
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




          $sql = "SELECT COUNT(*) as antal, shop.name, `shop_user`.shop_id  FROM gavefabrikken_2020.`shop_user`
            inner join shop on shop.id = shop_user.shop_id
            inner join company_order on `shop_user`.company_order_id = company_order.id
            WHERE `shop_user`.`shop_id` in (1832 , 1981) AND shop_user.blocked = 0 and is_cancelled = 0 group by `shop_user`.shop_id";
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

            $rsSaleThisYear = Dbsqli::getSql2("SELECT COUNT(`shop_user`.`id`) as antal FROM gavefabrikken_2020.`shop_user` inner join company_order on `shop_user`.company_order_id = company_order.id WHERE `shop_user`.`blocked` = 0 and `shop_user`.`shop_id` = ".$val["shop_id"]."  and is_cancelled = 0 and DATE(`created_datetime`) = DATE(NOW()) GROUP BY `shop_user`.shop_id order by `shop_user`.shop_id");
            $SaleThisYear =   sizeofgf($rsSaleThisYear) > 0 ? $rsSaleThisYear[0]["antal"] : "0";

            $rsSaleLastYear = Dbsqli::getSql2("SELECT COUNT(`shop_user`.`id`) as antal FROM gavefabrikken_2019.`shop_user` inner join gavefabrikken_2019.company_order on `shop_user`.company_order_id = company_order.id WHERE `shop_user`.`blocked` = 0 and `shop_user`.`shop_id` = ".$val["shop_id"]."  and is_cancelled = 0 and DATE(`created_datetime`) =  '".$this->getSameDayInWeekLastYear()."' GROUP BY `shop_user`.shop_id order by `shop_user`.shop_id");
         //   $rsSaleLastYear = Dbsqli::getSql2("SELECT COUNT(`shop_user`.`id`) as antal FROM gavefabrikken_2019.`shop_user` inner join gavefabrikken_2019.company_order on `shop_user`.company_order_id = company_order.id WHERE `shop_user`.`blocked` = 0 and `shop_user`.`shop_id` = ".$val["shop_id"]."  and is_cancelled = 0 and DATE(`created_datetime`) = DATE(NOW() -INTERVAL 1 Year) GROUP BY `shop_user`.shop_id order by `shop_user`.shop_id");
            $SaleLastYear =   sizeofgf($rsSaleLastYear) > 0 ? $rsSaleLastYear[0]["antal"] : "0";

            $rsSumMonth = Dbsqli::getSql2("SELECT COUNT(`shop_user`.`id`) as antal FROM gavefabrikken_2020.`shop_user` inner join company_order on `shop_user`.company_order_id = company_order.id WHERE `shop_user`.`blocked` = 0 and `shop_user`.`shop_id` = ".$val["shop_id"]."  and is_cancelled = 0 and MONTH(`created_datetime`) = MONTH(CURRENT_DATE()) AND YEAR(`created_datetime`) = YEAR(CURRENT_DATE()) GROUP BY `shop_user`.shop_id order by `shop_user`.shop_id");
            $saleMonth  = sizeofgf($rsSumMonth) > 0 ? $rsSumMonth[0]["antal"] : "0";

            $rsSumMonthLastYear = Dbsqli::getSql2("SELECT COUNT(`shop_user`.`id`) as antal FROM gavefabrikken_2019.`shop_user` inner join gavefabrikken_2019.company_order on `shop_user`.company_order_id = company_order.id WHERE `shop_user`.`blocked` = 0 and `shop_user`.`shop_id` = ".$val["shop_id"]."  and is_cancelled = 0 and MONTH(`created_datetime`) = MONTH(CURRENT_DATE()-INTERVAL 1 Year) AND YEAR(`created_datetime`) = YEAR(CURRENT_DATE()-INTERVAL 1 Year) GROUP BY `shop_user`.shop_id order by `shop_user`.shop_id");
            $saleMonthLastYear  = sizeofgf($rsSumMonthLastYear) > 0 ? $rsSumMonthLastYear[0]["antal"] : "0";


            $rsSaleLastYearToDate = Dbsqli::getSql2("SELECT COUNT(`shop_user`.`id`) as antal FROM gavefabrikken_2019.`shop_user` inner join gavefabrikken_2019.company_order on `shop_user`.company_order_id = company_order.id WHERE `shop_user`.`blocked` = 0 and `shop_user`.`shop_id` = ".$val["shop_id"]."  and is_cancelled = 0 and DATE(`created_datetime`) <= '".$dateLastYeah."' GROUP BY `shop_user`.shop_id order by `shop_user`.shop_id");
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
            $sqlNotSelect = "SELECT COUNT(*) as antal  FROM gavefabrikken_2020.`shop_user` WHERE `shop_id` in ( select id from shop where  is_gift_certificate = 1 ) and `shop_user`.blocked = 0";
      } else {
            $sqlNotSelect = "SELECT COUNT(*) as antal  FROM gavefabrikken_2020.`shop_user` WHERE `shop_id` = ".$shop_id. " AND `expire_date` = '".$deadline."' AND blocked = 0";
      }


      $rsNotSelect = Dbsqli::getSql2($sqlNotSelect);
      if(sizeofgf($rsNotSelect) <= 0 ){
        echo "ingen data";
        return;
      }

      $allCard = $rsNotSelect[0]["antal"];
      if($_POST["shop_id"] == "0"){
            $sql = "SELECT shop_user.expire_date, count(present_id) as total, present_model_id, `present_id`, `present_name`,present_model_name FROM gavefabrikken_2020.`order` INNER join shop_user on shop_user.username = order.user_username WHERE order.shop_id in in ( select id from shop where  is_gift_certificate = 1 ) and shop_user.blocked = 0 group by present_id,present_model_id  order by total desc";
      } else {
          $sql = "SELECT shop_user.expire_date, count(present_id) as total, present_model_id, `present_id`, `present_name`,present_model_name FROM gavefabrikken_2020.`order` INNER join shop_user on shop_user.username = order.user_username WHERE order.shop_id = ".$shop_id." and shop_user.expire_date =  '".$deadline."' and shop_user.blocked = 0 group by present_id,present_model_id  order by total desc";
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
        $modelSql = "select model_name, model_no,model_present_no  from gavefabrikken_2020.present_model where language_id = 1 and model_id= ".$dataRow["present_model_id"];
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
        $sql = "select quantity from gavefabrikken_2020.stock_reservation where id = ".$id;
        $rs = Dbsqli::getSql2($sql);
        echo  $rs[0]["quantity"];
      }

    }
    public function getStatsData()
    {
        if($_POST["shopId"] == "0"){
            $sql = "select quantity,model_id from gavefabrikken_2020.stock_reservation where shop_id in( select id from shop where  is_gift_certificate = 1 )  and active = 1";
        } else {
            $sql = "select quantity,model_id from gavefabrikken_2020.stock_reservation where shop_id = ".$_POST["shopId"]."  and card_deadline = '".$_POST["deadline"]."' and active = 1";
        }

        $rs = Dbsqli::getSql2($sql);
        echo json_encode($rs);
    }
    public function getStatsDataAll()
    {
        $sql = "select quantity,model_id from gavefabrikken_2020.stock_reservation where shop_id = ".$_POST["shopId"]." and active = 1";
        $rs = Dbsqli::getSql2($sql);
        echo json_encode($rs);
    }
    public function getSameDayInWeekLastYear(){
          $today = new \DateTime();

        $year  = (int) $today->format('Y');
        $week  = (int) $today->format('W'); // Week of the year
        $day   = (int) $today->format('w'); // Day of the week (0 = sunday)

        $sameDayLastYear = new \DateTime();
        $sameDayLastYear->setISODate($year - 1, $week, $day);
        return $sameDayLastYear->format('Y-m-d (l, W)');
    }



}
?>
