<?php

namespace GFUnit\navision\afslutning;
use GFBiz\units\UnitController;
use GFCommon\Model\Navision\OrderWS;
use GFUnit\navision\synccompany\CompanySync;
use GFUnit\navision\syncorder\OrderSync;
use GFUnit\navision\syncshipment\ShipmentSync;
use GFUnit\navision\syncexternalshipment\ExternalShipmentSync;

class RunAfslut
{

    public function runafslut() {

        $runafslut = intval($_POST["runafslut"] ?? 0);
        $runreservation = intval($_POST["runreservation"] ?? 0);

        if($runafslut === 0 && $runreservation === 0) {
            echo "NOT ACTION MATCH!";
            exit();
        }
        else if($runafslut == 1 && $runreservation === 0) {

            $this->runafslut();

        }
        else if($runafslut == 0 && $runreservation === 1) {

            $this->completeReservations();

        } else {
            echo "INVALID ACTION MATCH!";
            exit();
        }


    }

    /*
     * COMPLETE RESERVATIONS
     */

    private function completeReservations()
    {

      

    }

    /*
     * AFSLUT ORDERS
     */

    private function afslutOrders() {

        $helper = new AfslutHelper();
        $expireDates = $helper->getExpireDateList();
        $shops = $helper->getAllCardshops();

        // Get deadlines
        $afslutList = $_POST["active_orders"] ?? [];
        if(!is_array($afslutList) || count($afslutList) <= 0) {
            echo "Der er ikke valgt nogle shop/deadlines der skal afsluttes.";
            exit();
        }

        $shopsDone = 0;
        $ordersDone = 0;

        // For all shops and expiredates
        foreach($shops as $shop) {
            foreach($expireDates as $expireDate) {
                $key = $shop->shop_id.'_'.$expireDate;
                if(in_array($key, $afslutList)) {
                    $done = $this->handleShopExpireAfslut($shop,$expireDate);
                    echo "COMPLETEDE: ".$done."<br>";
                    if($done > 0) {
                        $shopsDone++;
                        $ordersDone += $done;
                    }
                }
            }
        }

        // Print afslut and back error
        echo "Shops completed: ".$shopsDone." - ".$ordersDone."<br>";
        \System::connection()->commit();
        exit();
    }

    private function handleShopExpireAfslut($shop,$expireDate) {

        echo "<h2>Afslut ".$shop->concept_code." - ".$expireDate."</h2>";
        $finished = 0;

        // LOAD ORDERS
        $companyOrders = \CompanyOrder::find_by_sql("SELECT * FROM `company_order` where shop_id = ".intval($shop->shop_id)." && expire_date = '".$expireDate."' && order_state in (4,5) ORDER BY `company_order`.`id` ASC");
        foreach($companyOrders as $companyOrder) {

            $co = \CompanyOrder::find($companyOrder->id);
            if($co->shop_id == $shop->shop_id && in_array($co->order_state, array(4,5))) {

                // Output
                echo $co->id." - ".$co->order_no." - ".$co->shop_name." - ".$co->company_name."<br>";

                // Update companyorder
                $co->order_state = 9;
                $co->nav_synced = 0;
                $co->save();

                // Run actionlog
                \ActionLog::logAction("CompanyOrderUpdated", "Ordre sat til afslutning: ".$co->order_no,"",0,$co->shop_id,$co->company_id,$co->id,0,0,0);

                $finished++;

            }

        }

        echo "<br><hr><br>";
        return $finished;

    }

}