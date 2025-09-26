<?php

namespace GFUnit\test\cardshop;

class IntegrationTest
{
    
    public function runIntegrationTest()
    {
        
        echo "RUN INTEGRATION TEST";
        $tc = new TestLib(1);

        /**
         * Companies synced
         * 37551: Interactive Creations - 74385
         * 37840: Integration Test 1 - 74387
         * 37851: Integration Test 2 - 74388
         * 37852: Integration Test 3 - 74389
         */

        // Create companies
        //$this->createCompanies($tc);

        // Create order
        //$this->createOrders($tc);

        // Step 1 changes
        //$this->step1Changes();

        $this->step2Changes();


    }

    private function createCompanies($tc)
    {

        // Create company
        $companyID = $tc->createCompany("Integration Test 1","99991000","","weborder","Integration Bill Vej 1","Adresse 2","5000","Odense","Danmark","integrationtest1@interactive.dk","Integration Test 1 Shipment","Integration Attention 1","Ship adresse 1","Ship adresse 2","1000","København K","Danmark","Contact integration 1","integrationcontact1@interactive.dk","99991000");
        echo "Created company: ".$companyID."<br>";

        // Create company
        $companyID = $tc->createCompany("Integration Test 2","99991002","","weborder","Integration Bill Vej 2","Adresse 2-2","5000","Odense","Danmark","integrationtest2@interactive.dk","Integration Test 2 Shipment","Integration Attention 2","Ship adresse 2","Ship adresse 2-2","1000","København K","Danmark","Contact integration 2","integrationcontact2@interactive.dk","99991002");
        echo "Created company: ".$companyID."<br>";

        // Create company
        $companyID = $tc->createCompany("Integration Test 3","99991003","","weborder","Integration Bill Vej 3","Adresse 2-3","5000","Odense","Danmark","integrationtest3@interactive.dk","Integration Test 3 Shipment","Integration Attention 3","Ship adresse 3","Ship adresse 2-3","1000","København K","Danmark","Contact integration 3","integrationcontact3@interactive.dk","99991003");
        echo "Created company: ".$companyID."<br>";

    }

    private function step1Changes()
    {

        echo "STEP 1<br>";
        $companyorder = \GFBiz\Model\Cardshop\CompanyOrderLogic::moveExpireDate(38294,"2021-12-31");
        var_dump($companyorder);
        $companyorder = \GFBiz\Model\Cardshop\CompanyOrderLogic::moveExpireDate(38295,"2022-04-01");
        var_dump($companyorder);
        \system::connection()->commit();
        return;

        // BS55789
        $companyorder = \CompanyOrder::find(38291);
        $companyorder->dot = 0;
        $companyorder->free_cards = 1;
        $companyorder->save();

        // BS55790
        $companyorder = \GFBiz\Model\Cardshop\CompanyOrderLogic::moveExpireDate(38294,"2021-12-31");
        $companyorder->gift_spe_lev = 1;
        $companyorder->save();

        $itemdata = array(
            "type" => "CARRYUP",
            "isdefault" => 1,
            "price" => 87800,
            "quantity" => 1
        );

        \GFBiz\Model\Cardshop\CompanyOrderLogic::updateOrderItem($companyorder->id,$itemdata);

        // BS55791
        $companyorder = \GFBiz\Model\Cardshop\CompanyOrderLogic::moveExpireDate(38295,"2022-04-01");
        $companyorder->nav_on_hold = 1;
        $companyorder->save();

        $shopuser = \ShopUser::find(1648104);
        $shopuser->blocked = 1;
        $shopuser->save();

        $shopuser = \ShopUser::find(1648103);
        $shopuser->blocked = 1;
        $shopuser->save();

        \system::connection()->commit();

    }

    private function step2Changes()
    {

        echo "STEP 2<br>";


        // BS55789
        $companyorder = \CompanyOrder::find(38291);
        $companyorder->giftwrap = 1;
        $companyorder->save();


        // BS55790
        $companyorder = \CompanyOrder::find(38294);



        // BS55791
        $companyorder = \CompanyOrder::find(38295);

    }

    private function createOrders($tc)
    {


        $orderID = $tc->createOrder("37840","52","2021-10-31","12","1","0","Test1","websale","0","0","1","1","0","Dette er en testordre","0","0","0","878","878","0","58");
        echo "<br>Created order: ".$orderID."<br>";

        $tc->createCardShipment($orderID,5,"Integration cardship 1-1","Integrationtest vej 1-1","Test 1-1",5220,"Odense","","Integration cardship 1-1","integrationcardship1-1@interactive.dk","99991011");
        $tc->createCardShipment($orderID,6,"Integration cardship 1-2","Integrationtest vej 1-2","Test 1-2",5220,"Odense","","Integration cardship 1-2","integrationcardship1-2@interactive.dk","99991012");
        $tc->createEarlyOrderShipment($orderID,1,"22030024","Integration earlyship 1-1","Integrationtest vej 1-1","Test 1-1",5220,"Odense","","Integration earlyship 1-1","integrationearlyship1-1@interactive.dk","99991021");
        $tc->createEarlyOrderShipment($orderID,2,"20811794BTB","Integration earlyship 1-2","Integrationtest vej 1-2","Test 1-2",5220,"Odense","","Integration earlyship 1-2","integrationearlyship1-2@interactive.dk","99991022");
/*
        $orderID = $tc->createOrder("37851","54","2022-04-01","15","0","0","Test2","websale","1","1","0","0","0","Dette er en testordre","0","0","0","0","0","0","58");
        echo "<br>Created order: ".$orderID."<br>";

        $orderID = $tc->createOrder("37852","575","2021-12-31","18","0","2","Test3","websale","2","0","0","0","1","Dette er en testordre","0","118","0","0","0","24","0");
        echo "<br>Created order: ".$orderID."<br>";
*/


    }



}