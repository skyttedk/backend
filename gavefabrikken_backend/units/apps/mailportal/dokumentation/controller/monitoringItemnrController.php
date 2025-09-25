<?php
class monitoringItemnrController extends baseController
{
    public function Index()
    {

    }
    public function freeSearch(){

        $itemNumber = $_POST["itemnr"];
        $returnData = $this->getOrderDataByItemnr($itemNumber);
        echo json_encode(["data"=>$returnData]) ;
    }
    public function getAllreserved()
    {
        $reservationDataRaw = $this->getReservationData();
        echo json_encode(["data"=>$reservationDataRaw]) ;
    }

    public function loadUsingItemnr(){
        $data = [];
        //$reservationDataRaw = $this->getReservationData();

        foreach($reservationDataRaw as $ele){
             $returnData = $this->getOrderDataByItemnr($ele["model_present_no"]);

             $data["data_".$ele["model_present_no"]] =  $returnData;
        }
        echo json_encode(["data"=>$data]) ;
    }



    public function load(){

        $reservationDataRaw = $this->getReservationData();
        $filterDelimiterData = $this->unpackDelimiter($reservationDataRaw);
        $data = $this->unpackSam($filterDelimiterData);
        $reservationData =  $this->removeDublet($data);

        $orderDataRaw =  $this->getOrderData();
        $filterDelimiterData = $this->unpackDelimiter($orderDataRaw);
        $data = $this->unpackSam($filterDelimiterData);
        $orderData =  $this->removeDublet($data);

        $this->makePresentation($reservationData,$orderData);
    }

    public function loadShopStatus()
    {
        $itemNumber = $_POST["itemnr"];
        $sql = "SELECT shop.name as shop_name, present_reservation.quantity ,present_model.model_name as mm_name,present_model.model_no as mm_type, COUNT(`order`.`id`) as order_antal, present.* FROM `present`
            inner JOIN shop on present.shop_id = shop.id
            inner JOIN present_model on present.id = present_model.present_id
            inner join present_reservation on present_model.model_id = present_reservation.model_id
            inner join `order` on present_reservation.model_id =  `order`.`present_model_id`
            WHERE
            `model_present_no` = '".$itemNumber."' and present_model.language_id  and present.shop_id != 0 ";
        $returnData = Dbsqli::getSql2($sql);
        echo json_encode(["data"=>$returnData]);
    }





    private function makePresentation($reservationData,$orderData){

        $presentation = [];
        $warningLevel_exceeded = [];
        $warningLevel_high = [];
        $warningLevel_low = [];
        $noneSelected = [];


        foreach($reservationData as $key => $value ){
//
            $orderCount = isset($orderData[$key]) ? $orderData[$key]:0;
            $procent = $orderCount != 0 && $value !=0 ?  ( $orderCount / $value) *100 : 0;

            // overskredet
            $key = str_replace("item_","",$key);
            $procent = round($procent,0);
            $html = "<button data-id='".$key."' class='monitoringItemInfo'>Info</button>";
            if($procent > 100){
                 $warningLevel_exceeded[] = ["itemnr"=>$key,"reserved"=>$value,"order"=>$orderCount,"procent"=>$procent,"info"=>$html];
            }
            // over 80 % valgte
            if($procent < 100 && $procent >= 80){
                $warningLevel_high[] = ["itemnr"=>$key,"reserved"=>$value,"order"=>$orderCount,"procent"=>$procent,"info"=>$html];
            }
            // under 80 % valgte
            if($procent < 80 && $orderCount != 0){
                $warningLevel_low[] = ["itemnr"=>$key,"reserved"=>$value,"order"=>$orderCount,"procent"=>$procent,"info"=>$html];
            }
            // ingen order givet (m�ske problem)
            if($orderCount == 0){
                $noneSelected[] = ["itemnr"=>$key,"reserved"=>$value,"order"=>$orderCount,"procent"=>$procent,"info"=>$html];
            }

        }
        $returnData = ["exceeded"=>$warningLevel_exceeded,"high"=>$warningLevel_high,"low"=>$warningLevel_low,"noneSelected"=>$noneSelected];
        echo json_encode(["data"=>$returnData]);
     }



    private function getOrderDataByItemnr($itemnr)
    {
          $itemNumber = $itemnr;
        // ikke sam nr.
         $listToSearch = [];
         if (strpos(strtolower($itemNumber), "sam") !== false) {

            $result = $this->lookupSam($itemNumber);
            if(sizeofgf($result) > 0){
                 foreach($result as $ele){
                    $listToSearch[] =  $ele["no"];
                 }
                   $List = "'".implode("','", $listToSearch)."'";
                   $sql = "select distinct parent_item_no from navision_bomitem where no in (".$List.") and deleted is null";
                   $result = Dbsqli::getSql2($sql);
                   if(sizeofgf($result) > 0){
                       foreach($result as $ele){
                           $listToSearch[] =  $ele["parent_item_no"];
                       }
                   }
             }

         } else {

             $sql = "select distinct parent_item_no from navision_bomitem where no ='".$itemNumber."' and deleted is null";
             $result = Dbsqli::getSql2($sql);
             if(sizeofgf($result) > 0){
                 foreach($result as $ele){
                     $listToSearch[] =  $ele["parent_item_no"];
                 }
             }
         }                                                                         



        $listToSearch[] = $itemNumber ;

        $List = "'".implode("','", $listToSearch)."'";

        // SELECT * FROM `navision_bomitem` WHERE `no` LIKE '1016759'

        $sql = "SELECT present_model.model_present_no , present_model.model_name, present_model.model_no, COUNT(order_2511.id) as antal, order_2511.`shop_id` as  order_shop_id ,shop.name,order_2511.`shop_is_gift_certificate`,pr.*  FROM order_2511
        inner JOIN present_model on  order_2511.`present_model_id` = present_model.model_id and present_model.language_id = 1
        left join

        (SELECT `present_reservation`.* FROM `present_reservation`
        inner JOIN present_model on  `present_reservation`.`model_id` = present_model.model_id and present_model.language_id = 1
        where present_model.model_present_no in( ".$List.") )

        pr on order_2511.`present_model_id`= pr.`model_id`
        inner join shop on order_2511.shop_id = shop.id
        where present_model.model_present_no in (".$List.") GROUP by present_model.model_present_no ,order_2511.`shop_id`


         ";

//         having  antal >  pr.quantity

       return Dbsqli::getSql2($sql);
    }



    private function getOrderData()
    {
        $sql = "SELECT present_model.model_present_no,COUNT(`order`.id) as c FROM `order`
                inner join present_model on `order`.present_model_id = present_model.model_id and present_model.language_id = 1
                GROUP by `present_model_id` ";
        return Dbsqli::getSql2($sql);
    }




    private function getReservationData()
    {
        $sql = "select   present_model.model_present_no,  present_model.model_name, present_model.model_no,sum(present_reservation.quantity) as c from present_reservation
                inner join present_model on present_reservation.model_id = present_model.model_id and present_model.language_id = 1
                group by present_model.model_present_no ";
        return Dbsqli::getSql2($sql);
    }
    private function unpackDelimiter($data)
    {
        $returnData = [];
        foreach($data as $item){
      //     print_R($item);
           $hasChange = false;
           if (!strpos($item["model_present_no"], "***") === false) {
                $pieces = explode("***", $item["model_present_no"]);
                foreach($pieces as $ele){
                    $returnData[] = array("model_present_no"=>$ele,"c"=>$item["c"]);
                }
           } else {
               $returnData[] = array("model_present_no"=>$item["model_present_no"],"c"=>$item["c"]);
           }
        }
        return $returnData;
    }
    private function unpackSam($data)
    {
       $returnData = [];
       foreach($data as $item){
           $string = str_replace(' ', '', $item["model_present_no"]);
           $model_present_no = trimgf(strtolower($string));
           if (strpos($model_present_no, "sam") !== false) {
                $sam = $this->lookupSam($model_present_no);
                if(is_array($sam)){
                    foreach($sam as $ele){
                        $returnData[] = array("model_present_no"=>$ele["no"],"c"=>$item["c"]);
                    }
                } else {
                    $returnData[] = array("model_present_no"=>$sam,"c"=>$item["c"]);
                }

           } else {
                $returnData[] = array("model_present_no"=>$item["model_present_no"],"c"=>$item["c"]);
           }
        }
        return $returnData;
    }
    private function lookupSam($item)
    {
        $sql = "select distinct no from navision_bomitem where parent_item_no ='".$item."'";
        return Dbsqli::getSql2($sql);
    }
    private function removeDublet($data)
    {
          $returnData = [];
          foreach($data as $item){
            if(isset($returnData["item_".$item["model_present_no"]])){
                $returnData["item_".$item["model_present_no"]] +=  intval($item["c"]);
            } else {
                $returnData["item_".$item["model_present_no"]] =  intval($item["c"]);
            }

          }
          return $returnData;
    }




}









/*
       $sql = 'SELECT id FROM `shop` WHERE (`name` like ("NO %") || rapport_email = "th@gavefabrikken.no") and shop.is_demo = 0 AND shop.active = 1 AND shop.deleted = 0 and shop.soft_close = 0';
        $listNoRS = Dbsqli::SetSql2($sql);
        $listToSql = [];
        foreach($listNoRS as $ele){
            array_push($listToSql,$ele["id"]);
        }
        echo "tore";
*/