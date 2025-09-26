<?php

use GFCommon\Model\Access\BackendPermissions;

class CardshopplukController Extends baseController {

    public function index()
    {
        echo "Ugyldig side.";
    }

    /***************** DASHBOARD **************************/

    public function dashboard()
    {
        if(BackendPermissions::session()->hasPermission(BackendPermissions::PERMISSION_KORT_PLUKLISTER) == false) {
            echo "Du har ikke rettigheder til at se denne side";
            return;
        }

        include __SITE_PATH . '/component/cardpluk/cardshoppluk.php';
        include __SITE_PATH . '/component/cardpluk/dashboard.php';
    }

    /**************** REPORT DISPATCHER *************************/

    public function pluk()
    {
        include __SITE_PATH . '/component/cardpluk/cardshoppluk.php';
        $report = new CardShopPlukReport();
        $report->pluk();
    }
    
      public function seprivat()
    {
        include __SITE_PATH . '/component/cardpluk/seprivatlev.php';
        $model = new seprivatlev();
        $model->dispatch();
    }

      public function efterlevering()
    {
        include __SITE_PATH . '/component/cardpluk/efterlevering.php';
        $model = new efterlevering();
        $model->dispatch();
    }
    
    public function sehotelspa()
    {
        include __SITE_PATH . '/component/cardpluk/sehotelspa.php';
        $model = new sehotelspa();
        $model->dispatch();
    }
    
      public function reminder()
    {
    
    /*
      if(BackendPermissions::session()->hasPermission(BackendPermissions::PERMISSION_KORT_PLUKLISTER) == false) {
            echo "Du har ikke rettigheder til at se denne side";
            return;
        }
      */
        include __DIR__ . '/../component/cardpluk/cardshoppluk.php';
        $report = new CardShopPlukReport();
        $report->remindermailfromquery();
    }
    
}
