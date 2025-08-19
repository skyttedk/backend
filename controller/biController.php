<?php
//Report Controller er controller til Model ShopReport.
//Samt controller til alle andre rapporter

include("model/dbsqli.class.php");
Class biController Extends baseController {
    private $country;
    private $listOfReplacmentCompany = "44780,44794,44795,45363,45364,45365,52468,52469,52470,69437,69439,69451";

    public function index() {
        if((isset($_GET["token"]) ? trimgf($_GET["token"]) : "") == "ldiuhfkjgrby"){
              $this->country = "dk";

//            $this->registry->template->bi = $this->getDbOnSaleperson( $salepersonSaleData );
            $this->registry->template->show('bi_view');
        } else if((isset($_GET["token"]) ? trimgf($_GET["token"]) : "") == "lgnds33diuhfksdf234y"){
                  $this->country = "no";

        } else {
          echo "no access";
        }

	}
    public function dev() {
        if((isset($_GET["token"]) ? trimgf($_GET["token"]) : "") == "ldiuhfkjgrby"){


//            $this->registry->template->bi = $this->getDbOnSaleperson( $salepersonSaleData );
            $this->registry->template->show('bi_dev_view');
        } else {
          echo "no access";
        }

	}
    public function loadDataForChart(){


            $sql = "SELECT WEEK(co.created_datetime) AS week,  count(shop_user.id) as antal, co.salesperson from (SELECT id,`salesperson`,created_datetime FROM `company_order` WHERE `salesperson` not like ('%test%') and is_cancelled = 0 and shop_id not in(".$this->getCountryId('no').")) as co
                        inner join shop_user ON shop_user.company_order_id = co.id and
                        shop_user.shop_id not in( ".$this->getCountryId('no').") and
                        is_demo = 0 and
                        shop_user.blocked = 0 group by week,co.salesperson order by co.salesperson,week

";
        $rs =  order::find_by_sql($sql);
        response::success(make_json("soldcard", $rs));
    }


    public function loadTotal(){
       $range = [];


       $rangeSql = " and (created_datetime BETWEEN '2024-07-01 16:47:14' AND '2024-12-24 23:47:14' )";
       $country = $_POST["country"];
       if(isset($_POST["start"]) && isset($_POST["end"]) ){
           if($_POST["start"] !="" && $_POST["end"] !="" ){
                $rangeSql = " and (created_datetime BETWEEN '".$this->fixeDate($_POST["start"])."' AND '".$this->fixeDate($_POST["end"])."' )";
           }
       }

       $sql = "SELECT count(shop_user.id) as antal,shop_user.shop_id,co.shop_name from (SELECT id,shop_name FROM `company_order` WHERE is_cancelled = 0 
            AND company_order.company_id NOT IN( ".$this->listOfReplacmentCompany."  ) AND company_order.order_state not IN(7,8,11,20)  and `salesperson` not like ('%us%') and shop_id in(".$this->getCountryId($country).") ".$rangeSql."  ) as co
                        inner join shop_user ON shop_user.company_order_id = co.id and
                        shop_user.shop_id in( ".$this->getCountryId($_POST["country"])." ) and
                        is_demo = 0 and
                         shop_user.is_demo = 0 AND shop_user.blocked = 0 AND shop_user.shutdown = 0  group by shop_user.shop_id order by co.shop_name";

        $rs =  order::find_by_sql($sql);


        response::success(make_json("soldcard", $rs));
    }
    public function loadTotalSE(){
        $range = [];


        $rangeSql = " and (created_datetime BETWEEN '2024-07-01 16:47:14' AND '2024-12-24 23:47:14' )";
        $country = $_POST["country"];
        if(isset($_POST["start"]) && isset($_POST["end"]) ){
            if($_POST["start"] !="" && $_POST["end"] !="" ){
                $rangeSql = " and (created_datetime BETWEEN '".$this->fixeDate($_POST["start"])."' AND '".$this->fixeDate($_POST["end"])."' )";
            }
        }

        $sql = "SELECT count(shop_user.id) as antal,shop_user.shop_id,co.shop_name,card_values,card_values from (SELECT id,shop_name FROM `company_order` WHERE is_cancelled = 0 
            AND company_order.company_id NOT IN( ".$this->listOfReplacmentCompany."  ) AND company_order.order_state not IN(7,8,11,20)  and `salesperson` not like ('%us%') and shop_id in(".$this->getCountryId($country).") ".$rangeSql."  ) as co
                        inner join shop_user ON shop_user.company_order_id = co.id and
                        shop_user.shop_id in( ".$this->getCountryId($_POST["country"])." ) and
                        is_demo = 0 and
                         shop_user.is_demo = 0 AND shop_user.blocked = 0 AND shop_user.shutdown = 0  group by shop_user.shop_id,card_values order by co.shop_name";

        $rs =  order::find_by_sql($sql);


        response::success(make_json("soldcard", $rs));
    }
    public function getTotalJGV()
    {
        $range = [];
        $shopID = $_POST["shop_id"];
        $rangeSql = " and (created_datetime BETWEEN '2024-07-01 16:47:14' AND '2024-12-24 23:47:14' )";
        if(isset($_POST["start"]) && isset($_POST["end"]) ){
            if($_POST["start"] !="" && $_POST["end"] !="" ){
                $rangeSql = " and (order_timestamp BETWEEN '".$this->fixeDate($_POST["start"])."' AND '".$this->fixeDate($_POST["end"])."' )";
            }
        }
        $sql = "SELECT present_list,  COUNT(DISTINCT `order`.`id`) as antal FROM `order` 
                    INNER JOIN present on present.id = `order`.`present_id` 
                    WHERE  `order`.`shop_id` = ".$shopID." ".$rangeSql." 
                    GROUP by present_list";
        $rs =  order::find_by_sql($sql);
        response::success(make_json("jgv", $rs));

    }


    public function loadDBSalepersonJGV(){

        $range = [];
        $rangeSql = " and (created_datetime BETWEEN '2024-07-01 16:47:14' AND '2024-12-24 23:47:14' )";
        if(isset($_POST["start"]) && isset($_POST["end"]) ){
            if($_POST["start"] !="" && $_POST["end"] !="" ){
                $rangeSql = " and (created_datetime BETWEEN '".$this->fixeDate($_POST["start"])."' AND '".$this->fixeDate($_POST["end"])."' )";
            }
        }
        $sql = "SELECT  COUNT(DISTINCT(co.order_no)) as orderAntal, count(distinct(shop_user.id)) as antal,    present_list, co.shop_id, co.salesperson, shop_user.shop_id,co.shop_name from (SELECT order_no, shop_name ,id,`salesperson`,shop_id FROM `company_order` WHERE `salesperson` not like ('%us%')  and is_cancelled = 0 
               AND company_order.company_id NOT IN(".$this->listOfReplacmentCompany." )  AND company_order.order_state not IN(7,8,11,20)  and shop_id  in( 7121) ".$rangeSql." ) as co
                        inner join shop_user ON shop_user.company_order_id = co.id
                        left join `order` on `order`.`shopuser_id` = shop_user.id
                        left JOIN present on present.id = `order`.`present_id` 
                         
                        and
                        shop_user.shop_id  in(7121 ) and
                        shop_user.is_demo = 0 and
                        shop_user.blocked = 0 group by co.salesperson, present_list ";
        $rs =  order::find_by_sql($sql);
        response::success(make_json("jgv", $rs));

    }

    public function loadDBSaleperson(){
       $country = $_POST["country"];
       $range = [];
        $rangeSql = " and (created_datetime BETWEEN '2024-07-01 16:47:14' AND '2024-12-24 23:47:14' )";
       if(isset($_POST["start"]) && isset($_POST["end"]) ){
           if($_POST["start"] !="" && $_POST["end"] !="" ){
                $rangeSql = " and (created_datetime BETWEEN '".$this->fixeDate($_POST["start"])."' AND '".$this->fixeDate($_POST["end"])."' )";
           }
       }
       $sql = "SELECT  COUNT(DISTINCT(co.order_no)) as orderAntal, count(distinct (shop_user.id)) as antal, co.shop_id, co.salesperson, shop_user.shop_id,co.shop_name from (SELECT order_no, shop_name ,id,`salesperson`,shop_id FROM `company_order` WHERE `salesperson` not like ('%us%')  and is_cancelled = 0 
               AND company_order.company_id NOT IN(".$this->listOfReplacmentCompany." )  AND company_order.order_state not IN(7,8,11,20)  and shop_id  in(  ".$this->getCountryId($country).") ".$rangeSql." ) as co
                        inner join shop_user ON shop_user.company_order_id = co.id and
                        shop_user.shop_id  in(  ".$this->getCountryId($_POST["country"]).") and
                        is_demo = 0 and
                        shop_user.blocked = 0 group by co.salesperson, shop_user.shop_id order by co.salesperson, co.shop_name";
        $rs =  order::find_by_sql($sql);
        response::success(make_json("soldcard", $rs));
    }

    public function loadDBSalepersonSE(){
        $country = $_POST["country"];
        $range = [];
        $rangeSql = " and (created_datetime BETWEEN '2024-07-01 16:47:14' AND '2024-12-24 23:47:14' )";
        if(isset($_POST["start"]) && isset($_POST["end"]) ){
            if($_POST["start"] !="" && $_POST["end"] !="" ){
                $rangeSql = " and (created_datetime BETWEEN '".$this->fixeDate($_POST["start"])."' AND '".$this->fixeDate($_POST["end"])."' )";
            }
        }
        $sql = "SELECT  COUNT(DISTINCT(co.order_no)) as orderAntal, count(distinct (shop_user.id)) as antal, co.shop_id, co.salesperson, shop_user.shop_id,co.shop_name,card_values from (SELECT order_no, shop_name ,id,`salesperson`,shop_id FROM `company_order` WHERE `salesperson` not like ('%us%')  and is_cancelled = 0 
               AND company_order.company_id NOT IN(".$this->listOfReplacmentCompany." )  AND company_order.order_state not IN(7,8,11,20)  and shop_id  in(  ".$this->getCountryId($country).") ".$rangeSql." ) as co
                        inner join shop_user ON shop_user.company_order_id = co.id and
                        shop_user.shop_id  in(  ".$this->getCountryId($_POST["country"]).") and
                        is_demo = 0 and
                        shop_user.blocked = 0 group by co.salesperson, shop_user.shop_id,card_values order by co.salesperson,co.shop_name";
        $rs =  order::find_by_sql($sql);
        response::success(make_json("soldcard", $rs));
    }




    public function getOrderCountOnSalesperson(){
               $shopboard = Shopboard::find_by_sql("select count(shop_id) as antal from company_order where salesperson = '".$_POST["saleperson"]."' and shop_id =  ".$_POST["shop_id"]);
              response::success(json_encode($shopboard));
    }

    public function getSalepersonList(){
              $shopboard = Shopboard::find_by_sql("select distinct(salesperson) from company_order where salesperson != '' order by salesperson");
              response::success(json_encode($shopboard));
    }
    public function getSalepersonListShop(){
              $result = Shopboard::find_by_sql("select distinct(salger) from shop_board where active = true and salger != 'alle' order by salger");
              response::success(json_encode($result));
    }
	public function readShopReport() {
      //  $shopboard = Shopboard::find_by_sql("select * from shop_board where active = true  order by fane");
	  //	$report = ShopReport::readShopReport ($_POST['id']);
	//	response::success(make_json("report", $report));
    }
/*------------------valgshop  --------------------------*/
    public function getSaleOnShop(){
       $saleperson = strtolower($_POST["saleperson"]);
       $sql = "select fk_shop from shop_board where salger = '".$saleperson."'";
       $rs = order::find_by_sql($sql);
       print_r($rs);

       //$datatype = $_POST["datatype"];
       //$data = $this->getOrderOnSalesperson($saleperson);
       //response::success(json_encode($this->getDbOnSaleperson($data,$datatype)));

       echo  "hejh";
    }
    private function getCountryId($countryCode)
    {
        $countryIdList["dk"] = "'52','53','54','55','56','290','310','575','2548','2395','9321','2960','2962','2963','2961','2999','4668','4662','7121'";
        $countryIdList["no"] = "'57','58','59','272','574','2549','2550','4740','8355', '8356', '8357', '8358', '8359', '8360', '8361', '8362', '8363', '8364', '8365', '8366'";
        $countryIdList["se"] = "'1832','1981','2558','4793','5117','8271'";
        return $countryIdList[$countryCode];
    }


    /*--------------------------*/

    public function getSaleOnCardShop(){
       $saleperson = strtolower($_POST["saleperson"]);
       $datatype = $_POST["datatype"];
       $data = $this->getOrderOnSalesperson($saleperson);
       response::success(json_encode($this->getDbOnSaleperson($data,$datatype)));
    }
    public function getSoldCardAmount(){
               $saleperson = strtolower($_POST["saleperson"]);
               $sql = "SELECT count(shop_user.id) as antal,shop_user.shop_id from (SELECT DISTINCT(`company_id`) FROM `company_order` WHERE `salesperson` = '".$saleperson."' and is_cancelled = 0) as co
                        inner join shop_user ON
                        shop_user.company_id = co.company_id and shop_user.shop_id not in( ".$this->getCountryId('no').") and shop_user.blocked = 0 group by shop_user.shop_id";
                $rs =  order::find_by_sql($sql);
                response::success(make_json("soldcard", $rs));
    }
    public function getSoldCardAmountWithNoOrder(){
               $saleperson = strtolower($_POST["saleperson"]);
               $sql = "SELECT count(shop_user.id) as antal,shop_user.shop_id from (SELECT DISTINCT(`company_id`) FROM `company_order` WHERE `salesperson` = '".$saleperson."' and is_cancelled = 0) as co
                        inner join shop_user ON
                        shop_user.company_id = co.company_id
                        inner join `order` on
                        `order`.shopuser_id = shop_user.id

                        and shop_user.shop_id not in( ".$this->getCountryId('no').") and shop_user.blocked = 0 group by shop_user.shop_id";

                $rs =  order::find_by_sql($sql);
                response::success(make_json("soldcard", $rs));
    }





    private function getOrderOnSalesperson($saleperson="ad"){
        $sql = "select  orderListeSum.present_model_id, orderListeSum.antal, `present_model`.`model_present_no` from
            (select orderListe.present_model_id, COUNT(orderListe.present_model_id) as antal from
            (SELECT DISTINCT (`order`.id), `order`.`present_model_id`, `order`.`company_id` FROM `order`
            LEFT JOIN company_order ON
            company_order.company_id = `order`.company_id
            inner join shop_user on
            shop_user.id = `order`.shopuser_id
            WHERE company_order.salesperson = '".$saleperson."' and company_order.is_cancelled = 0 and
            `order`.shop_id not in( ".$this->getCountryId('no').") and shop_user.blocked = 0 ) as orderListe
            GROUP by orderListe.present_model_id ) as orderListeSum
            left join present_model on
            present_model.model_id = orderListeSum.present_model_id
            where present_model.language_id = 1";
            return order::find_by_sql($sql);
            //Array ( [present_model_id] => 142 [antal] => 57 [model_present_no] => 7210EOB
    }
    private function getDbOnSaleperson($saleData,$dataReturn="dataNoRecord"){
        $data = [];
        $dataNoRecord = [];
        $datamultible = [];
        $dataNullPrice = [];
        $dataNullKostPrice = [];

        foreach($saleData as $key=>$val){

            $sqlStr = "";
            $partsLength = 0;
            // tjekker om der er et + da det betyder sammensatte varer
            if(strpos( $val->attributes["model_present_no"],"+") > -1){
                $parts = explode("+",$val->attributes["model_present_no"]);
                $partsLength = sizeofgf($parts);
                for ($i=0;$i<sizeofgf($parts);$i++){
                     $sqlStr.="varenr like '%".trimgf($parts[$i])."%'";
                    if($i+1 != $partsLength ){
                      $sqlStr.=" and ";
                    }
                }
                $sqlStr.=" and varenr like '%+%'";
                $sql = "select *,LENGTH(`varenr`) - LENGTH(REPLACE(`varenr`, '+', '')) as `plus` from db_dg where ".$sqlStr." HAVING plus = ".($partsLength-1);
            } else {  // intet + i vare teksten
               $sql = "select * from db_dg where varenr = '".$val->attributes["model_present_no"]."'";
            }
             $rs = order::find_by_sql($sql);
             $varenavn= "";  $kostpris= ""; $enhedpris= ""; $dg = "";

             // no hits
             if(count($rs) == 0){
                $sql2 = "select * from db_sampak where varenr = '".$val->attributes["model_present_no"]."'";
                $rs2 = order::find_by_sql($sql2);
                if(count($rs2) == 0){
                    $dataNoRecord[] = $val->attributes["model_present_no"];
                } else {


                    $dg = $rs2[0]->attributes["db_pr_stk"] * $val->attributes["antal"];

                    if($dg*1 < 0 ){ $dg*=-1; }  // dg er negativ
                    $rs2[0]->attributes["dg"] = $dg;
                    $rs2[0]->attributes["antal"] = $val->attributes["antal"];
                    $rs2[0]->attributes["vare_txt"] = utf8_encode($rs2[0]->attributes["vare_txt"]);
                    $data[] = json_encode($rs2[0]->attributes);

                }



             } else if(count($rs) == 1){

                // detect hvis nogle ikke har kostpris
                if($rs[0]->attributes["kostpris"] == 0){
                    $dataNullKostPrice[] = $rs[0]->attributes;
                }



                // hvis enhedspris ikke er sat, da kan dg ikke udregnes
                $rs[0]->attributes["vare_txt"] =  utf8_encode($rs[0]->attributes["vare_txt"]);


                if($rs[0]->attributes["enhedspris"] == 0){
                    // Finder db via kort v�rdi
                    $enhedspris = "";
                    if($rs[0]->attributes["kort_value"] > 0){
                        $enhedspris = $rs[0]->attributes["kort_value"];
                        $dg_pr_stk =  $rs[0]->attributes["kort_value"] - $rs[0]->attributes["kostpris"];

                    } else {
                        $enhedspris =  $this->repnul($rs[0]->attributes["varenr"]);
                        $dg_pr_stk =  $this->repnul($rs[0]->attributes["varenr"]) - $rs[0]->attributes["kostpris"];
                        $sql = "update db_dg set kort_value = '".$this->repnul($rs[0]->attributes["varenr"])."'  where id = ".$rs[0]->attributes["id"];
                        $this->test($sql);
                    }
                    // check om dg er mere end 0
                    if($dg_pr_stk > 0){
                        $dg = $dg_pr_stk * $val->attributes["antal"];
                        if($dg*1 < 0 ){ $dg*=-1; }  // dg er negativ
                        $rs[0]->attributes["dg"] = $dg;
                        $rs[0]->attributes["antal"] = $val->attributes["antal"];
                        $rs[0]->attributes["enhedspris"] = $enhedspris;
                        $rs[0]->attributes["db_pr_stk"] = $dg_pr_stk;
                        $data[] = json_encode($rs[0]->attributes);
                    } else {
                        $dataNullPrice[] = $rs[0]->attributes;
                    }
                } else {
                    // happy path
                    $dg = $rs[0]->attributes["db_pr_stk"] * $val->attributes["antal"];
                    if($dg*1 < 0 ){ $dg*=-1; }  // dg er negativ
                    $rs[0]->attributes["dg"] = $dg;
                    $rs[0]->attributes["antal"] = $val->attributes["antal"];
                    $data[] = json_encode($rs[0]->attributes);
                }



             }
             //mere end 2 ordre
             else if(count($rs) > 1){
                for($i=0;$i<sizeofgf($rs);$i++ ){
                   $rs[$i]->attributes["vare_txt"] =  utf8_encode($rs[$i]->attributes["vare_txt"]);
                   $datamultible[] = $rs[$i]->attributes;
                }
             }

        }
           /*
            $postData = array(
            "data" => $data,
            "dataNoRecord" => $dataNoRecord,
            "datamultible" => $datamultible
          //  "dataNullPrice" => $dataNullPrice
            );
            */
        // print_r($postData);

            return $$dataReturn;
        }

        public function findNulCost()
        {
            $sql = "SELECT varenr FROM `db_dg` WHERE `enhedspris` LIKE '0' ORDER BY `db_pr_stk` ";
              $rs = order::find_by_sql($sql);
              foreach($rs as $ele){
                echo "------------------<br>";
                echo $ele->attributes["varenr"];
                echo "<br>";
                echo $this->repnul($ele->attributes["varenr"]);
                echo "<br>";
              }
        }


        private function test($sql){
          $db = new Dbsqli();
          $rs = $db->setSql2($sql);

        }





          public function repnul($number){
              $sql = "SELECT distinct(shop_id) FROM `order` WHERE `present_model_present_no` LIKE '".$number."' and shop_is_gift_certificate = 1 and shop_id not in( ".$this->getCountryId('no').")  ";
              $rs = order::find_by_sql($sql);
              if(count($rs) > 0){
                 switch ($rs[0]->attributes["shop_id"]) {
                    case 52:
                        return 560;
                    break;
                    case 54:
                        return 400;
                    break;
                    case 55:
                        return 560;
                    break;
                    case 56:
                        return 640;
                    break;
                    case 53:
                        return 800;
                    break;
                    case 290:
                        return 200;
                    break;
                    case 310:
                        return 300;
                    break;
                    case 575:
                        return 640;
                    break;
                    case 2395:
                        return 1120;
                    break;
                     case 9321:
                         return 1400;
                         break;
                    case 2548:
                        return 640;
                    break;

                    default:
                        return 0;
                }
              }else {
                return "1";
              }

        }
        private function fixeDate($date){
            $date = explode("/", $date);
            return $date[2]."-".$date[0]."-".$date[1];
        }



  }







