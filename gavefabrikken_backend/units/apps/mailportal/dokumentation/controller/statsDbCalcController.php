<?php

Class statsDbCalcController Extends baseController {
    public function index() {
        $this->registry->template->show('statsDbCalc_view');
 
    }

    public function getCardshops(){



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





}