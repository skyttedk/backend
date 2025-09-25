<?php
class cardshopnoteController Extends baseController {

  public function Index() {

  }
  public function loadNotes()
  {
    $this->registry->template->show('cardshopnote_view');
  }
  public function getGiftWrap()
  {
        $hasWrap = false;
        $rs = CompanyOrder::find('all',array('conditions' => array('company_id'=>$_POST["company_id"])));
        foreach($rs as $order){
            if($order->giftwrap == 1){
              $hasWrap = true;
            }
        }
        $result["hasWrap"] = $hasWrap;
        response::success(json_encode($result));
  }
  public function setGiftWrap(){
    Dbsqli::setSql2("update company_order set giftwrap = 1 where company_id = ".$_POST["company_id"] );
    $dummy = array();
    response::success(make_json("result", $dummy));
  }
  public function unsetGiftWrap(){
    Dbsqli::setSql2("update company_order set giftwrap = 0 where company_id = ".$_POST["company_id"] );
    $dummy = array();
    response::success(make_json("result", $dummy));
  }



}

?>