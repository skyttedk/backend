<?php

namespace GFUnit\development\tools;
use GFBiz\units\UnitController;
use GFCommon\DB\CronLog;
use GFCommon\Model\Navision\Shipment2XML;

class Controller extends UnitController
{

    public function __construct()
    {
        parent::__construct(__FILE__);
    }

    public function objectfactory()
    {
        \GFCommon\Utils\ObjectFactory::runObjectFactory();
    }

    public function autoselect()
    {
        return;
        $autoselectModel = new AutoselectPresent();
        $autoselectModel->dispatch();
    }


    public function testactionlog()
    {

        echo "TEST";
/*
        \ActionLog::logAction("TesLog", "Tester log funktion","Detaljer om testen her");

        $shopUser = \ShopUser::find(3635991);
        \ActionLog::logShopUserAction("ShopUserLogin", $shopUser->username." er logget ind","",$shopUser);
*/

        \response::silentsuccess();

    }

    public function testcronlob() {


        \GFCommon\DB\CronLog::startCronJob("testcronjob");

        echo "hello!";

        $zero = 10;
        $val = 100 / $zero;

        \GFCommon\DB\CronLog::endCronJob(1,"Done testing",array("tested" => 1),"DEBUG DEBUG");

    }

    public function viewshipmentxml() {

        return;
        $shipmentid = 124584;

        $shipment = \Shipment::find($shipmentid);

        $shiptoMaster = null;
        if($shipment->shipto_state == 2) {
            $shiptoMaster = \Shipment::find('first',array("conditions" => array("companyorder_id" => $shipment->companyorder_id,"shipment_type" => "giftcard","shipto_state" => 1)));
        }

        $xmlModel = new Shipment2XML($shipment,$shiptoMaster);
        $xmlDoc = $xmlModel->getXML();

        echo "<pre>".htmlentities($xmlDoc)."</pre>";

    }

    public function viewexpiredates() {

        $expireDateList = \ExpireDate::find_by_sql("SELECT * FROM `expire_date` ORDER BY `expire_date`.`expire_date` ASC");
        $cardshopSettings = \CardshopSettings::find_by_sql("SELECT * FROM `cardshop_settings` ORDER BY `cardshop_settings`.`language_code` ASC, concept_parent ASC, concept_code ASC;");
        $shopdates = \CardshopExpiredate::find_by_sql("SELECT * FROM `cardshop_expiredate`");


        echo "<table style='width:100%;'><tr>";
        echo "<td>-</td>";

        foreach($expireDateList as $expireDate) {
            echo "<td>".$expireDate->display_date." (".$expireDate->week_no.")</td>";
        }

        echo "</tr>";

        foreach($cardshopSettings as $cs) {
            echo "<tr>";

            echo "<td>".$cs->concept_code." - ".$cs->concept_name."</td>";

            foreach($expireDateList as $expireDate) {

                $cdates = array();
                foreach($shopdates as $sd) {
                    if($cs->shop_id == $sd->shop_id && $sd->expire_date_id == $expireDate->id) {
                        $cdates[] = $sd;
                    }
                }

                if(count($cdates) == 0) {
                    echo "<td>&nbsp;</td>";
                } else {
                    echo "<td>".count($cdates)."</td>";
                }



            }

            echo "</tr>";
        }


        echo "</table>";


        $missingList = array();

        foreach($shopdates as $sd) {

            $hasShop = false;
            $hasDate = false;

            foreach($expireDateList as $ed)  {
                if($sd->expire_date_id == $ed->id) {
                    $hasDate = true;
                }
            }

            foreach($cardshopSettings as $cs) {
                if($sd->shop_id == $cs->shop_id) {
                    $hasShop = true;
                }
            }

            if(!$hasShop || !$hasDate) {
                $missingList[] = $sd->id;
                echo " - ".$sd->id." ".($hasShop ? "" : " - missing shop")." ".($hasDate ? "" : " - missing date")."<br>";
            }

        }

        echo json_encode($missingList);

    }


}