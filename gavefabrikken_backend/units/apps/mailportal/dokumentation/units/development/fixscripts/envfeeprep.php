<?php

namespace GFUnit\development\fixscripts;

use GFCommon\Model\Navision\OrderStatusWS;
use GFUnit\navision\syncorder\OrderSync;

class EnvFeePrep
{

    private $stats;
    private $navClient;

    public function run()
    {

        // Create nav client
        $this->navClient = new OrderStatusWS(1);


        $this->runsync();
        return;

        $this->stats = array("handled" => 0,"deleted_before" => 0,"deleted_after" => 0,"notsynced_yet" => 0,"invalid_state" => 0,"has_prepayment" => 0, "no_prepayment" => 0);
        echo "ENVFEE PREP<br>";

        // Load all orders
        $sql = "SELECT * FROM `company_order` WHERE shop_id in (select shop_id from cardshop_settings where language_code = 1) && excempt_envfee = 0";
        $companyOrderList = \CompanyOrder::find_by_sql($sql);

        echo "LOADED: ".countgf($companyOrderList)."<br>";

        foreach($companyOrderList as $index => $co) {
            $this->processCompanyOrderForEnvFee($co->id);
            if($index > 250) break;
        }

        echo "<pre>".print_r($this->stats,true)."</pre>";
        \System::connection()->commit();

    }

    public function runsync()
    {

        $sql = "SELECT * FROM `company_order` WHERE order_state > 3 && order_state < 7 && shop_id in (select shop_id from cardshop_settings where language_code = 1) && excempt_envfee != 1 && envfee_run = 0";
        $companyOrderList = \CompanyOrder::find_by_sql($sql);

        echo "RUN ON ".countgf($companyOrderList)." ORDERS";

        $counter = 0;
        foreach($companyOrderList as $co) {

            $counter++;
            // Check for acontopayments
            $orderStatus = $this->navClient->getStatus($co->order_no);

            // If valid and above 0, cancel
            if($orderStatus != null && $orderStatus->getPrepaymentEntryCountTotal() > 0) {

                echo "<br></br>ABORT, DO NOT SYNC THIS ORDER!<br>";

                $companyOrder = \CompanyOrder::find($co->id);
                $companyOrder->excempt_envfee = 1;
                $companyOrder->save();

            } else {

                $this->syncEnvFeeDocument($co->id);

                $companyOrder = \CompanyOrder::find($co->id);
                $companyOrder->envfee_run = 4;
                $companyOrder->save();

            }

            \System::connection()->commit();
            \System::connection()->transaction();


            if($counter >= 200) return;

        }


    }
    
    private function syncEnvFeeDocument($companyOrderID) {
        
        
        echo "<br><hr><br>SYNC ENVFEE ON ".$companyOrderID."<br>";
        $companyOrder = \CompanyOrder::find($companyOrderID);

        $syncModel = new OrderSync(true);
        $syncModel->syncCompanyOrder($companyOrder);
        
    }

    private function processCompanyOrderForEnvFee($companyOrderID)
    {

        // Load companyorder
        $companyOrder = \CompanyOrder::find($companyOrderID);
        $this->stats["handled"]++;

        echo "<br><hr>Process ".$companyOrder->id." - ".$companyOrder->order_no." - ".$companyOrder->order_state."<br><br>";

        $orderStatus = $this->navClient->getStatus($companyOrder->order_no);
        if($orderStatus == null) {
            if($companyOrder->order_state == 7 || $companyOrder->order_state == 8) $this->stats["deleted_before"]++;
            else if($companyOrder->order_state <= 3) $this->stats["notsynced_yet"]++;
            else {
                echo "INVALID STATE, NO RESPONSE BUT SHOULD HAVE!<br>";
                $this->stats["invalid_state"]++;
            }

            $companyOrder->excempt_envfee = 3;
            $companyOrder->save();

        } else {

            $prepaymentCount = $orderStatus->getPrepaymentEntryCountTotal();
            if($prepaymentCount == 0) {
                $this->stats["no_prepayment"]++;
                $companyOrder->excempt_envfee = 2;
                $companyOrder->save();
            } else {
                $this->stats["has_prepayment"]++;
                $companyOrder->excempt_envfee = 1;
                $companyOrder->save();
            }



            echo "<pre>".print_r($orderStatus->frontendMapper(),true)."</pre>";

        }


    }

}