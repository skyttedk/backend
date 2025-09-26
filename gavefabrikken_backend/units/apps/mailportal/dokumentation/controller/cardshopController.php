<?php
class cardshopController Extends baseController {

  public function Index() {

  }
  public function getGiftTransport(){
        $has = false;
        $rs = CompanyOrder::find('all',array('conditions' => array('company_id'=>$_POST["company_id"])));
        foreach($rs as $order){
            if($order->gift_spe_lev == 1){
              $has = true;
            }
        }
        $result["has"] = $has;
        response::success(json_encode($result));
  }
  public function setGiftTransport(){
    Dbsqli::setSql2("update company_order set gift_spe_lev = 1 where company_id = ".$_POST["company_id"] );
    $dummy = array();
    response::success(make_json("result", $dummy));
  }
  public function unsetGiftTransport(){
    Dbsqli::setSql2("update company_order set gift_spe_lev = 0 where company_id = ".$_POST["company_id"] );
    $dummy = array();
    response::success(make_json("result", $dummy));
  }

   public function getFreeDelivery(){
        $has = false;
        $rs = CompanyOrder::find('all',array('conditions' => array('company_id'=>$_POST["company_id"])));
        foreach($rs as $order){
            if($order->free_delivery == 1){
              $has = true;
            }
        }
        $result["has"] = $has;
        response::success(json_encode($result));
  }
    public function setFreeDelivery(){
    Dbsqli::setSql2("update company_order set free_delivery = 1 where company_id = ".$_POST["company_id"] );
    $dummy = array();
    response::success(make_json("result", $dummy));
  }
  public function unsetFreeDelivery(){
    Dbsqli::setSql2("update company_order set free_delivery = 0 where company_id = ".$_POST["company_id"] );
    $dummy = array();
    response::success(make_json("result", $dummy));
  }


}

?>