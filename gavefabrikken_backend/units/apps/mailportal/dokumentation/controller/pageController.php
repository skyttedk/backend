<?php

Class pageController Extends baseController {

    public function index() {
        //$this->registry->template->show('main_view');
        echo "index";
    }
    public function cardShop(){
        $this->registry->template->show('mainCardShops_view');
    }
    public function companyCardImport(){
        $this->registry->template->show('mainCardShopsImport_view');
    }
    public function shopMain(){
         $this->registry->template->show('main_view');
    }
    public function giftcertificatestats(){
         $this->registry->template->show('giftcertificatestats');
    }
    public function showArkiv(){
         $this->registry->template->show('arkiv_view');
    }
    public function showInfoboard(){
         $this->registry->template->show('infoboard_view');
    }
    public function showShopboard(){
         $this->registry->template->show('shopboard_view');
    }
    public function gf(){
        $this->registry->template->show('gf_view');
    }

}

?>
