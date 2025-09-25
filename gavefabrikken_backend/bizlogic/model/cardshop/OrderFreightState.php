<?php

namespace GFBiz\Model\Cardshop;

class OrderFreightState
{

    /*
     * FREIGHT_STATE is a field in the company_order table and defines how freight is calculated on the order
     *
     * The field can have these values
     *
     * Not updated before
     * 0: Not calculated
     *
     * Normal freight
     * 1: Add calculated freight to this order
     * 2: Freight is added to another order, dont use on this order
     *
     * Fixed cost
     * 3: Fixed cost on this order
     * 4: Fixed cost on company, but cost is added to another order
     * 5: Fixed cost set to 0
     *
     * Other states
     * 6: Order cancelled, do not used for freight
     *
     * This class can update freight states for an order or company
     *
     */

    /**
     * Updates freight state on a specific order, this can affect other orders on the same company as well
     * @param $companyOrderID
     * @return int Freight state of the company order
     */
    public static function updateOrderIDFreightState(&$companyOrderID)
    {
        $companyorder = \CompanyOrder::find($companyOrderID);
        return self::updateOrderFreightState($companyorder);
    }


    /**
     * Updates freight state on a specific order, this can affect other orders on the same company as well
     * @param \CompanyOrder $companyOrder
     * @return mixed|null Freight state on the order
     */
    public static function updateOrderFreightState(\CompanyOrder &$companyOrder)
    {
        $companyOrder->freight_state = self::updateCompanyFreightState($companyOrder->company_id,$companyOrder);
        return $companyOrder->freight_state;
    }

    public static function updateCompanyFreightState($companyID,&$companyOrder = null)
    {

        // Load companyorders
        $companyOrderList = \CompanyOrder::find("all",array("conditions" => array("company_id" => $companyID)));
        if(!is_array($companyOrderList) || countgf($companyOrderList) == 0) {
            return;
        }

        // Load active cards on orders
        $companyOrderIDList = array();
        $activeCardMap = array();
        foreach($companyOrderList as $co) {
            $companyOrderIDList[] = $co->id;
            $activeCardMap[$co->id] = 0;
        }

        // Load and set number of active cards
        $activeCardsList = \ShopUser::find_by_sql("SELECT count(id) as active_cards, company_order_id FROM shop_user WHERE company_order_id IN (".implode(",",$companyOrderIDList).") && blocked = 0 GROUP BY company_order_id");
        foreach($activeCardsList as $aci) {
            $activeCardMap[$aci->company_order_id] = $aci->active_cards;
        }

        // Find closed orders
        $newOrderList = array();
        foreach($companyOrderList as $co) {
            if($co->order_state == 7 || $co->order_state == 8 || $co->is_cancelled == 1 || $activeCardMap[$co->id] == 0) {

                // Set freight state to cancelled
                if($co->freight_state != 6) {
                    $co->freight_state = 6;
                    $co->nav_synced = 0;
                    $co->save();
                }

                // Update companyorder
                if($companyOrder instanceof \CompanyOrder && $co->id == $companyOrder->id && $companyOrder->freight_state != $co->freight_state) {
                    $companyOrder->freight_state = $co->freight_state;
                    $companyOrder->nav_synced = 0;
                }

            } else {
                $newOrderList[] = $co;
            }
        }
        $companyOrderList = $newOrderList;
        if(count($newOrderList) == 0) {
            return;
        }

        // Load shipping cost
        $shippingCost = \companyshippingcost::find('first',array("conditions" => array("company_id" => $companyID)));
        if($shippingCost instanceof \companyshippingcost && $shippingCost->cost != -1) {
            foreach($companyOrderList as $ci => $co) {

                // Set freight state on shipping cost
                if($shippingCost->cost == 0) {
                    if($co->freight_state != 5) {
                        $co->freight_state = 5;
                        $co->nav_synced = 0;
                    }
                }
                else if($ci == 0) {
                    if($co->freight_state != 3) {
                        $co->freight_state = 3;
                        $co->nav_synced = 0;
                    }
                }
                else {
                    if($co->freight_state != 4) {
                        $co->freight_state = 4;
                        $co->nav_synced = 0;
                    }
                }

                $co->save();

                // Update companyorder
                if($companyOrder instanceof \CompanyOrder && $co->id == $companyOrder->id && $companyOrder->freight_state != $co->freight_state) {
                    $companyOrder->freight_state = $co->freight_state;
                    $companyOrder->nav_synced = 0;
                }

            }
        }

        // No fixed shipping cost
        else {

            // Split into delivery maps
            $orderDeliveryMap = array();
            foreach($companyOrderList as $co) {
                $key = $co->shop_id."_".$co->expire_date->format('Y-m-d');
                if(!isset($orderDeliveryMap)) $orderDeliveryMap[$key] = array();
                $orderDeliveryMap[$key][] = $co;
            }

            // Process each delivery map
            foreach($orderDeliveryMap as $key => $deliveryList) {

                // Find primary, either first, the one currently 1 or currently 3
                $currentPrimary = null;
                $currentPrimaryState = 0;
                foreach($deliveryList as $co) {
                    if($currentPrimary == null) {
                        $currentPrimary = $co;
                        $currentPrimaryState = $co->freight_state;
                    } else if($co->freight_state == 1 && $currentPrimary->freight_state != 1) {
                        $currentPrimary = $co;
                        $currentPrimaryState = $co->freight_state;
                    } else if($co->freight_state == 3 && $currentPrimaryState != 1 && $currentPrimaryState != 3) {
                        $currentPrimary = $co;
                        $currentPrimaryState = $co->freight_state;
                    }
                }

                // Update state on all orders
                foreach($deliveryList as $co) {

                    // Update freight state
                    if($currentPrimary->id == $co->id) {
                        if($co->freight_state != 1) {
                            $co->freight_state = 1;
                            $co->nav_synced = 0;
                        }
                    } else {
                        if($co->freight_state != 2) {
                            $co->freight_state = 2;
                            $co->nav_synced = 0;
                        }
                    }
                    $co->save();

                    // Update companyorder
                    if($companyOrder instanceof \CompanyOrder && $co->id == $companyOrder->id && $companyOrder->freight_state != $co->freight_state) {
                        $companyOrder->freight_state = $co->freight_state;
                        $companyOrder->nav_synced = 0;
                    }
                }

            }

        }

        if($companyOrder instanceof \CompanyOrder && $companyOrder->id > 0) {
            return $companyOrder->freight_state;
        }
        else return 0;
    }

}

