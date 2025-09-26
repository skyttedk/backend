<?php
// Controller media
// Date created  Wed, 06 Apr 2016 09:39:56 +0200
// Created by Bitworks
class stats2021Controller  {
    public function Index() {
    }



    public function cardSale()
    {
        $today_start = date("Y-m-d")." 00:00:01";
        $today_end = date("Y-m-d")." 23:59:59";
        $month_start = date('Y-m-01')." 00:00:01";
        $month_end = date("Y-m-t", strtotime(date("Y-m-d")) )." 23:59:59";

        //    echo $this->get2021();


        // DK
        $rs = $this->getSalePresentYear(1);
        $cardTitleHtml = $this->buildTitleCol($rs);

        echo "<h1>Danske shops</h1>";
        // total
        $presentSaleHtml = $this->buildCol($rs);
        $presentSaleHtml = '<div class="v-flex"><div class="header-title">Antal solgte totalt 2022</div>'.$presentSaleHtml.'</div>';
        // i dag
        $rs = $this->getSalePresentYear(1,$today_start,$today_end);
        $todayHtml = $this->buildCol($rs);
        $todayHtml = '<div class="v-flex"><div class="header-title">Antal solgte i dag 2022</div>'.$todayHtml.'</div>';
        // Denne måned
        $rs = $this->getSalePresentYear(1,$month_start,$month_end);
        $monthHtml = $this->buildCol($rs);
        $monthHtml = '<div class="v-flex"><div class="header-title">Antal solgte indeværende måned 2021</div>'.$monthHtml.'</div>';

        echo '<div class="flex-container">'.$cardTitleHtml.$presentSaleHtml.$monthHtml.$todayHtml.'</div>';
        echo "<br><hr>";

        // NO
        $rs = $this->getSalePresentYear(4);
        $cardTitleHtml = $this->buildTitleCol($rs);

        echo "<h1>Norske shops</h1>";
        // total
        $presentSaleHtml = $this->buildCol($rs);
        $presentSaleHtml = '<div class="v-flex"><div class="header-title">Antal solgte totalt 2022</div>'.$presentSaleHtml.'</div>';
        // i dag
        $rs = $this->getSalePresentYear(4,$today_start,$today_end);
        $todayHtml = $this->buildCol($rs);
        $todayHtml = '<div class="v-flex"><div class="header-title">Antal solgte i dag 2022</div>'.$todayHtml.'</div>';
        // Denne måned
        $rs = $this->getSalePresentYear(4,$month_start,$month_end);
        $monthHtml = $this->buildCol($rs);
        $monthHtml = '<div class="v-flex"><div class="header-title">Antal solgte indeværende måned 2021</div>'.$monthHtml.'</div>';


        echo '<div class="flex-container">'.$cardTitleHtml.$presentSaleHtml.$monthHtml.$todayHtml.'</div>';
        echo "<br><hr>";

        // SE
        $rs = $this->getSalePresentYear(5);
        $cardTitleHtml = $this->buildTitleCol($rs);

        echo "<h1>Svenske shops</h1>";
        // total
        $presentSaleHtml = $this->buildCol($rs);
        $presentSaleHtml = '<div class="v-flex"><div class="header-title">Antal solgte totalt 2022</div>'.$presentSaleHtml.'</div>';
        // i dag
        $rs = $this->getSalePresentYear(5,$today_start,$today_end);
        $todayHtml = $this->buildCol($rs);
        $todayHtml = '<div class="v-flex"><div class="header-title">Antal solgte i dag 2022</div>'.$todayHtml.'</div>';
        // Denne måned
        $rs = $this->getSalePresentYear(5,$month_start,$month_end);
        $monthHtml = $this->buildCol($rs);
        $monthHtml = '<div class="v-flex"><div class="header-title">Antal solgte indeværende måned 2021</div>'.$monthHtml.'</div>';

        echo '<div class="flex-container">'.$cardTitleHtml.$presentSaleHtml.$monthHtml.$todayHtml.'</div>';



    }
    public function get2021()
    {
        $url = 'https://gavefabrikken.dk/gavefabrikken_backend/component/test.php';

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        echo curl_exec($curl);
        curl_close($curl);

    }

    public function buildCol($list)
    {
        $returnData["sum"] = 0;
        $returnData["html"] = "";
        foreach($list as $ele){
            $ele['antal'] = $ele['antal'] == "" ? "0" : $ele['antal'];
            $returnData["sum"]+=$ele['antal']*1;
            $returnData["html"].= "<div class='".$ele['concept_code']."'>".$ele['antal']."</div>";
        }
        $returnData["html"].= "<div class='total'><b>".$returnData["sum"]."</b></div>";

        return $returnData["html"];
    }
    public function buildTitleCol($rs)
    {
        $returnHtml = "";
        foreach($rs as $ele){
            $returnHtml.= "<div class='consept'>".$ele['concept_code']."</div>";
        }
        $returnHtml.= "<div ><b>TOTAL</b></div>";
        return '<div class="v-flex"><div class="header-title">Shop</div>'.$returnHtml.'</div>';
    }

    public function getSalePresentYear($lang,$start="2022-04-01 16:47:14",$slut="2022-12-31 16:47:14")
    {
        $sql = "SELECT
                language_code,
                cardshop_settings.concept_code,
                c.antal
            FROM
                `cardshop_settings`
            LEFT JOIN(
                    SELECT shop_user.shop_id,
                    COUNT(shop_user.id) AS antal
                FROM
                    shop_user
                LEFT JOIN company_order ON shop_user.company_order_id = company_order.id
                WHERE
                    company_order.order_state not IN(7,8) AND company_order.is_cancelled = 0 AND company_order.company_id NOT IN(
                    44780,
                    44794,
                    44795,
                    45363,
                    45364,
                    45365,69437,69439,69451
                ) AND shop_user.is_demo = 0 AND shop_user.blocked = 0 AND shop_user.shutdown = 0
                GROUP BY
                    shop_user.shop_id
            ) AS c
            ON
                `cardshop_settings`.shop_id = c.shop_id
            where 
                language_code = $lang
            ORDER BY
                language_code,
                cardshop_settings.show_index";
        return Dbsqli::getSql2($sql);
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

            $sql = "select day(`created_datetime`) as Day, hour(`created_datetime`) as Hour, count(*) as Count  FROM `system_log` WHERE `controller` LIKE 'login' AND `action` LIKE 'loginShopUserByToken' AND `created_datetime` > SUBDATE(NOW(),1) group by day(created_datetime), hour(created_datetime) ORDER BY `id`  DESC ";
            $rs = Dbsqli::getSql2($sql);
            $sql2 = "select day(`order_timestamp`) as Day, hour(`order_timestamp`) as Hour, count(*) as Count  FROM `order` WHERE  `order_timestamp` > SUBDATE(NOW(),1) group by day(`order_timestamp`), hour(`order_timestamp`) ORDER BY `id`  DESC";
            $rs2 = Dbsqli::getSql2($sql2);
            $sql3 = "select day(`created_datetime`) as Day, hour(`created_datetime`) as Hour, count(*) as Count  FROM `system_log` WHERE `created_datetime` > SUBDATE(NOW(),1) AND `action` not LIKE 'loginStats' group by day(created_datetime), hour(created_datetime) ORDER BY `id`  DESC ";
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

            $sql = "select day(`created_datetime`) as Day, hour(`created_datetime`) as Hour, count(*) as Count  FROM `system_log` WHERE `controller` LIKE 'login' AND `action` LIKE 'loginShopUserByToken' AND `created_datetime` > SUBDATE(NOW(),1) group by day(created_datetime), hour(created_datetime) ORDER BY `id`  DESC ";
            $rs = Dbsqli::getSql2($sql);
            $sql2 = "select day(`order_timestamp`) as Day, hour(`order_timestamp`) as Hour, count(*) as Count  FROM `order` WHERE  `order_timestamp` > SUBDATE(NOW(),1) group by day(`order_timestamp`), hour(`order_timestamp`) ORDER BY `id`  DESC";
            $rs2 = Dbsqli::getSql2($sql2);
            $sql3 = "select day(`created_datetime`) as Day, hour(`created_datetime`) as Hour, count(*) as Count  FROM `system_log` WHERE `created_datetime` > SUBDATE(NOW(),1) AND `action` not LIKE 'loginStats' group by day(created_datetime), hour(created_datetime) ORDER BY `id`  DESC ";
            $rs3 = Dbsqli::getSql2($sql3);
            $sql4 = "select day(`created_datetime`) as Day, hour(`created_datetime`) as Hour, count(*) as Count  FROM `system_log` WHERE `controller` LIKE 'login' AND `action` LIKE 'loginShopUserByToken' and committed = 0 AND `created_datetime` > SUBDATE(NOW(),1) group by day(created_datetime), hour(created_datetime) ORDER BY `id`  DESC ";
            $rs4 = Dbsqli::getSql2($sql4);
            $sql5 = "select day(`created_datetime`) as Day, hour(`created_datetime`) as Hour, count(*) as Count  FROM `system_log` WHERE `created_datetime` > SUBDATE(NOW(),1) AND `action` not LIKE 'loginStats' and committed = 0  group by day(created_datetime), hour(created_datetime) ORDER BY `id`  DESC ";
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
            $sql = "select day(`created_datetime`) as Day, hour(`created_datetime`) as Hour, count(*) as Count  FROM `system_log` WHERE `controller` LIKE 'login' AND `action` LIKE 'loginShopUserByToken' AND `created_datetime` > SUBDATE(NOW(),1) group by day(created_datetime), hour(created_datetime) ORDER BY `id`  DESC";
            $rs = Dbsqli::getSql2($sql);
            echo "<table border=1>";
            foreach($rs as $ele){
                echo "<tr><td>".$ele["Day"]."</td><td>".$ele["Hour"]."</td><td>".$ele["Count"]."</td></tr>";
            }
            echo "</table>";
        }

    }



    public function totalCard(){
        $sqlNotSelect = "SELECT COUNT(*) as antal  FROM `shop_user` WHERE `shop_id`  in ( select id from shop where  is_gift_certificate = 1 ) AND blocked = 0 ";
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

    public function getDeadlines()
    {
        $shopID = $_POST["shop_id"];
        //$deadlinesRs = Dbsqli::getSql2("SELECT DISTINCT(gift_certificate.expire_date),expire_date.`week_no` FROM `gift_certificate` inner join expire_date on gift_certificate.expire_date = expire_date.expire_date WHERE reservation_group in ( select reservation_group  from shop where id = ".$shopID.")  order by gift_certificate.expire_date");
        $deadlinesRs = Dbsqli::getSql2("SELECT DISTINCT expire_date,expire_date.`week_no` FROM expire_date WHERE expire_date in ( select distinct expire_date  from shop_user where shop_id = ".$shopID.")  order by expire_date");

        $html = '<select id="deadline" onchange="newDeadline() ">  <option value="alle">alle</option> ';
        foreach($deadlinesRs as $deadline){
            $html.='<option value="'.$deadline["expire_date"].'">'.$deadline["expire_date"].', uge'.$deadline["week_no"].'</option>';
        }
        echo $html.=' </select>';
    }

    public function loadStats($deadline,$shopId,$returnCsv=false)
    {

        $csv = [];
        $expireDateSql = "";
        if($deadline != "alle"){
            $expireDateSql = "and shop_user.expire_date ='".$deadline."'";
        }

        if($shopId == "0"){
            $sqlNotSelect = "SELECT COUNT(*) as antal  FROM `shop_user` WHERE `shop_id` in ( select id from shop where  is_gift_certificate = 1 ) and company_order_id not in(SELECT id FROM company_order where is_cancelled = 1   )  AND blocked = 0 and is_demo = 0 ".$expireDateSql." order by antal";
        } else {
            $sqlNotSelect = "SELECT COUNT(*) as antal  FROM `shop_user` WHERE `shop_id` = ".$shopId." and company_order_id not in(SELECT id FROM company_order where is_cancelled = 1   )  AND blocked = 0 and is_demo = 0 ".$expireDateSql." order by antal";
        }
        $rsNotSelect = Dbsqli::getSql2($sqlNotSelect);

        if(sizeofgf($rsNotSelect) <= 0 ){
            echo "ingen data";
            return;
        }
        $allCard = $rsNotSelect[0]["antal"];

        if($shopId == "0"){
            $sql = "
            SELECT present_model.model_name, present_model.model_no, present_model.model_present_no,COUNT(`present_model_id`) as antal FROM `order`
inner JOIN present_model on present_model.model_id = `order`.`present_model_id`
inner JOIN shop_user on shop_user.id = `order`.`shopuser_id`
WHERE `shop_is_gift_certificate` = 1 and present_model.language_id = 1 and
company_order_id not in(SELECT id FROM company_order where is_cancelled = 1   ) and
shop_user.blocked = 0 AND
shop_user.is_demo = 0 ".$expireDateSql."
GROUP by present_model.model_present_no order by present_model.model_present_no, present_model.model_name,present_model.model_no
            ";
        } else {
            $sql = "
            SELECT present_model.model_name, present_model.model_no, present_model.model_present_no,COUNT(`present_model_id`) as antal FROM `order`
inner JOIN present_model on present_model.model_id = `order`.`present_model_id`
inner JOIN shop_user on shop_user.id = `order`.`shopuser_id`
WHERE `shop_is_gift_certificate` = 1 and present_model.language_id = 1 and
company_order_id not in(SELECT id FROM company_order where is_cancelled = 1   ) and
shop_user.blocked = 0 AND
shop_user.is_demo = 0 and
`order`.shop_id = ".$shopId."
".$expireDateSql."
GROUP by present_model.model_present_no order by present_model.model_present_no, present_model.model_name,present_model.model_no";

        }


        $rs = Dbsqli::getSql2($sql);
        //  print_R($rs);

        $total = 0;
        $totalProcent = 0;

        foreach($rs as $dataRow){

            $total+= $dataRow["antal"]*1;
        }
        $notSelect = $allCard - $total;
        $radomId = $this->generateRandomString();

        $html = "<div class='statsContent'><p>Antal der mangler at v&oelig;lge: ".$notSelect."</p>";
        $html.= "<p>Total antal kort: ".$allCard."</p>";
        $html.= "<table style='display:none' id='".$radomId."'>  <thead><tr><th>Varenr</th><th>Gave</th><th>Model</th><th>Antal valgte gaver</th><th>Antal i %</th><th>Fremskrevet v&oelig;rdi</th></thead></tr>  <tbody>";

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
    public function getSameDayInWeekLastYear($time=true){
        $today = new \DateTime();

        $year  = (int) $today->format('Y');
        $week  = (int) $today->format('W'); // Week of the year

        $day   = (int) $today->format('w'); // Day of the week (0 = sunday)
        if($day == 0) {
            $day = 7;
        }
        $sameDayLastYear = new \DateTime();
        $sameDayLastYear->setISODate($year - 1, $week, $day);
        if($time==true){
            return $sameDayLastYear->format('Y-m-d H:i:s');
        } else {
            return $sameDayLastYear->format('Y-m-d');
        }

    }
    //    echo "Today: ".$today->format('Y-m-d (l, W)').PHP_EOL."<br>";
    //    echo "Same week and day last year: ".$sameDayLastYear->format('Y-m-d (l, W)').PHP_EOL."<br>";
    /*

        $sql = "";
        $rs = Dbsqli::getSql2("SELECT WEEKOFYEAR(NOW()) as thisyear");
    //echo    $thisYearWeekNr =  $rs[0]["thisyear"];
        $rs = Dbsqli::getSql2("select WEEKOFYEAR(DATE(NOW() -INTERVAL 1 Year)) as lastyear");
   // echo    $lastYearWeekNr =  $rs[0]["lastyear"];
        $rs = Dbsqli::getSql2("SELECT WEEKDAY(NOW()) as thisyear");
     echo   $thisYearDayNr =  $rs[0]["thisyear"];
        $rs = Dbsqli::getSql2("select WEEKDAY(DATE(NOW() -INTERVAL 1 Year)) as lastyear");
    echo    $lastYearDayNr =  $rs[0]["lastyear"];

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
     //   echo $rs[0]["correctionDate"]."<br>";
        return explode(" ", $rs[0]["correctionDate"])[0];
        */

    public function getSameDayInWeekLastYear2019($time=true){
        $today = new \DateTime();

        $year  = (int) $today->format('Y');
        $week  = (int) $today->format('W'); // Week of the year
        $day   = (int) $today->format('w'); // Day of the week (0 = sunday)
        if($day == 0) {
            $day = 7;
        }
        $sameDayLastYear = new \DateTime();
        $sameDayLastYear->setISODate($year - 2, $week, $day);
        if($time==true){
            return $sameDayLastYear->format('Y-m-d H:i:s');
        } else {
            return $sameDayLastYear->format('Y-m-d');
        }

    }


}
?>
