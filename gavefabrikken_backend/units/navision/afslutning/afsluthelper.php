<?php

namespace GFUnit\navision\afslutning;

use GFUnit\cardshop\earlyorder\EarlyOrderSplit;
use GFUnit\navision\syncexternalshipment\ExternalShipmentSync;

class AfslutHelper
{
    /**
     * @var \CardshopSettings[]
     */
    private $cardshopList;

    /**
     * @var array Cached order summary data
     */
    private $orderSummaryData = [];

    private $deliveryDates;

    private $reservationMap;

    public function __construct() {
        $this->loadOrders();
    }

    /**
     * Load orders and cardshop data
     */
    private function loadOrders() {

        // Load expire dates
        $expireDateList = \ExpireDate::find('all');
        $this->deliveryDates = [];
        foreach($expireDateList as $expireDate) {
            if($expireDate->is_delivery == 1) {
                $this->deliveryDates[] = $expireDate->expire_date->format('Y-m-d');
            }
        }

        // Load shops
        $this->cardshopList = \CardshopSettings::find_by_sql("SELECT * FROM cardshop_settings ORDER BY language_code ASC, concept_code ASC");

        $sql = "SELECT order_state, shop_id, expire_date, count(id) as ordercount FROM `company_order` group by order_state, shop_id, expire_date order by expire_date, shop_id, order_state";
        $orderSummary = \CompanyOrder::find_by_sql($sql);

        // Organize data for easier access
        foreach ($orderSummary as $row) {
            $expireDate = $row->expire_date->format('Y-m-d');

            // Initialize array structure if it doesn't exist
            if (!isset($this->orderSummaryData[$expireDate])) {
                $this->orderSummaryData[$expireDate] = [];
            }

            if (!isset($this->orderSummaryData[$expireDate][$row->shop_id])) {
                $this->orderSummaryData[$expireDate][$row->shop_id] = [];
            }

            // Store order count by state
            $this->orderSummaryData[$expireDate][$row->shop_id][$row->order_state] = (int)$row->ordercount;

        }

        // Load reservations on finished
        $sql = "SELECT cardshop_settings.concept_code, cardshop_settings.shop_id, expire_date, count(company_order.id) as order_count, sum(quantity) as quantity FROM `company_order`, cardshop_settings where cardshop_settings.language_code != 4 && company_order.shop_id = cardshop_settings.shop_id && order_state = 10 && order_no not in (SELECT sono FROM `navision_reservation_done`) group by shop_id, expire_date order by `company_order`.`expire_date` DESC;";
        $reservationList = \CompanyOrder::find_by_sql($sql);
        $this->reservationMap = [];

        foreach($reservationList as $row) {

            $key = $row->shop_id."-".$row->expire_date->format('Y-m-d');

            $this->reservationMap[$key] = array($row->order_count,$row->quantity);

        }


    }

    public function getReservationOrders($shopid,$expireDate) {
        $key = $shopid."-".$expireDate;
        return $this->reservationMap[$key][0] ?? 0;
    }

    public function getReservationQuantity($shopid,$expireDate) {
        $key = $shopid."-".$expireDate;
        return $this->reservationMap[$key][1] ?? 0;
    }

    public function isDeliveryDate($expireDate) {
        return in_array($expireDate, $this->deliveryDates);
    }

    /**
     * Get cardshops with a specific language
     * @param string $languageCode The language code to filter by
     * @return \CardshopSettings[]
     */
    public function getCardshops($languageCode) {
        $result = [];

        foreach ($this->cardshopList as $cardshop) {
            if ($cardshop->language_code === $languageCode) {
                $result[] = $cardshop;
            }
        }

        return $result;
    }

    /**
     * Get cardshops with a specific language

     * @return \CardshopSettings[]
     */
    public function getAllCardshops() {
        return $this->cardshopList;
    }


    /**
     * Return a string list of expire dates
     * @return string[]
     */
    public function getExpireDateList() {
        // Return the sorted keys (expire dates) from the summary data
        $dates = array_keys($this->orderSummaryData);

        // Sort dates chronologically
        sort($dates);

        return $dates;
    }

    /**
     * Get orders in orderstate, expiredate and shop
     * @param $shopId int shopid of the shop
     * @param $expireDate string expiredate in Y-m-d format
     * @param $orderStates int[] list of orderstates to sum and return
     * @return int sum of ordercount
     */
    public function getOrderCount($shopId, $expireDate, $orderStates) {
        $total = 0;

        // Check if we have data for this expire date and shop
        if (isset($this->orderSummaryData[$expireDate]) &&
            isset($this->orderSummaryData[$expireDate][$shopId])) {

            // Sum counts for all requested order states
            foreach ($orderStates as $state) {
                if (isset($this->orderSummaryData[$expireDate][$shopId][$state])) {
                    $total += $this->orderSummaryData[$expireDate][$shopId][$state];
                }
            }
        }

        return $total;
    }
}

