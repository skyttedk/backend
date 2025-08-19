<?php

namespace GFUnit\lister\fragt;
use GFBiz\Model\Cardshop\OrderFreightState;
use GFBiz\units\UnitController;
use GFCommon\Model\Navision\FreightCalculator;


class Controller extends UnitController
{

    public function __construct() {
        parent::__construct(__FILE__);
    }

    public function index() {



    }

    /**
     * FRAGTLISTE OUTPUT
     */
    
    public function fragtliste() {

        $dataList = array();

        $deadline = "2022-11-06";
        $country = 4;

        // SQL TO GET ORDERS
        $orderSQL = "SELECT * FROM `company_order` where expire_date = '".$deadline."' && shop_id in (select shop_id from cardshop_settings where language_code = ".$country.") && order_state not in (1,2,3,7,8) ORDER BY expire_date ASC, shop_name ASC";
        $orderList = \CompanyOrder::find_by_sql($orderSQL);

        $bsMap = $this->toBSMap($orderList);

        foreach($orderList as $order) {

            $parentCompany = $this->getCompany($order->company_id);

            $baseData = array(
                $order->order_no,
                $order->shop_name,
                $order->expire_date->format('Y-m-d'),
                $order->quantity,
                $parentCompany->name
            );
            $emptyData = array("", "", "", "", "");

            $total = FreightCalculator::calculateNOOrderFreightSum($order);
            $freightLines =  FreightCalculator::getNOOrderFreightLines($order);

            $freightTotal = 0;
            $totalCards = 0;
            $totalFreightCards = 0;

            foreach ($freightLines as $index => $freightLine) {

                if($index == 0) $dataList[] = array();

                $freightCompany = $this->getCompany($freightLine["company_id"]);

                $freightData = array(
                    $freightCompany->ship_to_company,
                    $freightCompany->ship_to_postal_code,
                    $freightCompany->ship_to_city,
                    FreightCalculator::getNorwayPostalCodeArea($freightCompany->ship_to_postal_code),
                    $freightLine["active_cards"],
                    $freightLine["billcount"],
                    $freightLine["amount"],
                    implode(", ", $this->orderlistToBS($freightLine["fromorders"],$bsMap))
                );

                if ($index == 0) {
                    $dataList[] = array_merge($baseData, $freightData);
                } else {
                    $dataList[] = array_merge($emptyData, $freightData);
                }

                $totalCards += $freightLine["active_cards"];
                $totalFreightCards += $freightLine["billcount"];
                $freightTotal += $freightLine["amount"];

            }

            if(count($freightLines) > 1) {
                $dataList[] = array_merge($emptyData, array("TOTAL","","","",$totalCards,$totalFreightCards,$freightTotal,$total));
            }

        }


        $headers = array(
            "Ordre nr",
            "Shop",
            "Deadline",
            "Antal bestilt",
            "Virksomhed",
            "Modtager virksomhed",
            "Modtager postnr",
            "Modtager by",
            "Post zone",
            "Aktive kort",
            "Betal frag for (antal kort)",
            "Fragtbeløb",
            "Ordre i forsendelse"
        );

        header('Content-Encoding: UTF-8');
        header('Content-type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename=fragtexport.csv');
        echo "\xEF\xBB\xBF"; // UTF-8 BOM
        foreach($headers as $header) {
            echo $header.";";
        }

        foreach($dataList as $dataRow) {
            foreach($dataRow as $dataCell) {
                echo ($dataCell).";";
            }
            echo "\r\n";
        }
        
    }
    


    private function orderlistToBS($orderlist,$map) {
        $bslist = array();
        foreach($orderlist as $orderid) {
            $bslist[] = $map[$orderid];
        }
        return $bslist;
    }

    private $companyCache = [];
    private function getCompany($companyid) {

        if(!isset($this->companyCache[intval($companyid)])) {
            $this->companyCache[intval($companyid)] = \Company::find(intval($companyid));
        }
        return $this->companyCache[intval($companyid)];
    }

    private function toBSMap($orderList) {
        $bsMap = array();
        foreach($orderList as $order) {
            $bsMap[$order->id] = $order->order_no;
        }
        return $bsMap;
    }

    /**
     * NORSK FREIGHT CALCULATOR
     */

    private $freightLineCache;

    private function getFreightLines($companyOrder) {

        if($this->freightLineCache == null) {
            $this->freightLineCache = array();
        }

        $shop = $companyOrder->shop_id;
        $expireDate = $companyOrder->expire_date->format("Y-m-d");

        // Load freight
        if(!isset($this->freightLineCache[$shop]) || !isset($this->freightLineCache[$shop][$expireDate])) {

            if(!isset($this->freightLineCache[$shop])) $this->freightLineCache[$shop] = array();
            $this->freightLineCache[$shop][$expireDate] = array();

            $sql = "select count(distinct shop_user.id) as activecards, company_id as delivery_company, min(company_order_id) as billto_order, group_concat(distinct company_order_id) as orders_in_delivery, expire_date, shop_id from shop_user where blocked = 0 && shutdown = 0 && company_order_id in (SELECT id FROM `company_order` where shop_id in (select shop_id from cardshop_settings where language_code = 4) && order_state not in (1,2,3,7,8)) group by company_id, expire_date, shop_id ORDER BY company_order_id ASC, `shop_user`.`company_order_id` ASC";
            $partDeliveryList = \ShopUser::find_by_sql($sql);
            foreach($partDeliveryList as $partDelivery) {


                if(!isset($this->freightLineCache[$shop][$expireDate][intval($partDelivery->billto_order)])) {
                    $this->freightLineCache[$shop][$expireDate][intval($partDelivery->billto_order)] = array();
                }

                $orderids = explode(",",$partDelivery->orders_in_delivery);
                foreach($orderids as $orderid) {
                    if(intval($orderid) > 0) {

                        if(!isset($this->freightLineCache[$shop][$expireDate][intval($orderid)])) {
                            $this->freightLineCache[$shop][$expireDate][intval($orderid)] = array();
                        }

                        // If part delivery is paid to bill
                        if(intval($orderid) == $partDelivery->billto_order) {

                            $item = array(
                                "company_id" => $partDelivery->delivery_company,
                                "active_cards" => $partDelivery->activecards,
                                "billcount" => $partDelivery->activecards,
                                "fromorders" => $orderids
                            );

                            $this->freightLineCache[$shop][$expireDate][intval($partDelivery->billto_order)][] = $item;

                        }

                        // If part delivery is not paid to bill
                        else {

                            $item = array(
                                "company_id" => $partDelivery->delivery_company,
                                "active_cards" => $partDelivery->activecards,
                                "billcount" => 0,
                                "fromorders" => $orderids
                            );

                            $this->freightLineCache[$shop][$expireDate][intval($orderid)][] = $item;

                        }
                    }
                }

            }

        }


        // Find order lines
        $orderLines = $this->freightLineCache[$shop][$expireDate][$companyOrder->id] ?: array();
        return $orderLines;

    }

    /**
     * FREIGHT REPORT 2
     */

    /*
    public function fragtliste2() {

        $dataList = array();

        $deadline = "2022-11-06";
        $country = 4;

        // SQL TO GET ORDERS
        $orderSQL = "SELECT * FROM `company_order` where expire_date = '".$deadline."' && shop_id in (select shop_id from cardshop_settings where language_code = ".$country.") && order_state not in (1,2,3,7,8) ORDER BY expire_date ASC, shop_name ASC";
        $orderList = \CompanyOrder::find_by_sql($orderSQL);

        $dataList = array();
        $bsMap = $this->toBSMap($orderList);

        foreach($orderList as $order) {

            $dataList[] = array();

            $parentCompany = $this->getCompany($order->company_id);

            $baseData = array(
                $order->order_no,
                $order->shop_name,
                $order->expire_date->format('Y-m-d'),
                $order->quantity,
                $parentCompany->name
            );
            $emptyData = array("", "", "", "", "");

            $freightLines = $this->getFreightLines($order);
            $freightTotal = 0;
            $totalCards = 0;
            $totalFreightCards = 0;

            foreach ($freightLines as $index => $freightLine) {

                $freightCompany = $this->getCompany($freightLine["company_id"]);

                $freightAmount = 0;
                if ($freightLine["billcount"] > 0) {
                    $freightAmount = FreightCalculator::calculateFreight($order->shop_id, $freightLine["billcount"], false, $freightCompany);
                }

                $freightData = array(
                    $freightCompany->ship_to_company,
                    $freightCompany->ship_to_postal_code,
                    $freightCompany->ship_to_city,
                    FreightCalculator::getNorwayPostalCodeArea($freightCompany->ship_to_postal_code),
                    $freightLine["active_cards"],
                    $freightLine["billcount"],
                    $freightAmount,
                    implode(", ", $this->orderlistToBS($freightLine["fromorders"],$bsMap))
                );

                if ($index == 0) {
                    $dataList[] = array_merge($baseData, $freightData);
                } else {
                    $dataList[] = array_merge($emptyData, $freightData);
                }

                $totalCards += $freightLine["active_cards"];
                $totalFreightCards += $freightLine["billcount"];
                $freightTotal += $freightAmount;

            }

            if(count($freightLines) > 1) {
                $dataList[] = array_merge($emptyData, array("TOTAL","","","",$totalCards,$totalFreightCards,$freightTotal));
            }

        }


        $headers = array(
            "Ordre nr",
            "Shop",
            "Deadline",
            "Antal bestilt",
            "Virksomhed",
            "Modtager virksomhed",
            "Modtager postnr",
            "Modtager by",
            "Post zone",
            "Aktive kort",
            "Betal frag for (antal kort)",
            "Fragtbeløb",
            "Ordre i forsendelse"
        );

        header('Content-Encoding: UTF-8');
        header('Content-type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename=fragtexport.csv');
        echo "\xEF\xBB\xBF"; // UTF-8 BOM
        foreach($headers as $header) {
            echo $header.";";
        }

        foreach($dataList as $dataRow) {
            foreach($dataRow as $dataCell) {
                echo ($dataCell).";";
            }
            echo "\r\n";
        }




    }
    */


    /*
        public function fragtliste() {


            $dataList = array();

            // SQL TO GET ORDERS
            $orderSQL = "SELECT * FROM `company_order` where shop_id in (select shop_id from cardshop_settings where language_code = 4) && order_state not in (1,2,3,7,8) ORDER BY expire_date ASC, shop_name ASC";
            $orderList = \CompanyOrder::find_by_sql($orderSQL);
            foreach($orderList as $order) {

                $dataList[] = array();

                // Update freight on companyorder
                $freightState = OrderFreightState::updateOrderFreightState($order);

                // Load parent company
                $parentCompany = \Company::find($order->company_id);

                // Get shopusercount per company
                $shopUserSQL = "SELECT company_id, count(id) as cards FROM `shop_user` where company_order_id = ".$order->id." && blocked = 0 && shutdown = 0 group by company_id;";
                $shopUserCount = \ShopUser::find_by_sql($shopUserSQL);

                // Find base data for order
                $baseData = array(
                    $order->order_no,
                    $order->shop_name,
                    $order->expire_date->format('Y-m-d'),
                    $order->quantity,
                    $parentCompany->name
                );

                $totalCards = 0;
                $totalFreight = 0;

                // For each delivery address
                foreach($shopUserCount as $companyCount) {

                    // Load company
                    $company = \Company::find($companyCount->company_id);

                    // Find extra data
                    $extraData = array(
                        $company->ship_to_company,
                        $company->ship_to_postal_code,
                        $company->id == $parentCompany->id ? "PARENT" : "CHILD",
                        $companyCount->cards
                    );

                    $freightAmount = "?";
                    $freightType = "UNKNOWN";

                    // Calculate freight
                    if($freightState == 1) {

                        // Load freigh addresses and active cards on this company, shop_id and expire_date
                        $freightAmount = FreightCalculator::calculateFreight($order->shop_id,$companyCount->cards,false,$company);
                        $freightType = "CALCULATED";

                    }

                    // Add fixed price freight to this order
                    else if($freightState == 3) {
                        $companyShippingCost = \companyshippingcost::find("first",array("conditions" => array("company_id" => $this->companyOrder->company_id)));
                        if($companyShippingCost != null && $companyShippingCost->company_id == $this->companyOrder->company_id && $companyShippingCost->cost >= 0) {
                            $freightAmount = $companyShippingCost->cost;
                            $freightType = "FIXEDCOST";
                        } else {
                            throw new \Exception("Order freight state set to fixed price, but price could not be found: company_id = ".$this->companyOrder->company_id);
                        }
                    }

                    else if($freightState == 0) {
                        $freightType = "NOT-SET";
                    } else if($freightState == 4) {
                        $freightType = "OTHER-FIXED";
                        $freightAmount = 0;
                    } else if($freightState == 2) {
                        $freightType = "OTHER-CALCULATED";
                        $freightAmount = 0;
                    } else if($freightState == 5) {
                        $freightType = "FIXEDCOST";
                        $freightAmount = 0;
                    } else if($freightState == 6) {
                        $freightType = "CANCELLED";
                        $freightAmount = 0;
                    }

                    $extraData[] = $freightType;
                    $extraData[] = $freightAmount;

                    // Add extra data together with base data and add to data list
                    $companyData = array_merge(array_values($baseData),$extraData);
                    $dataList[] = $companyData;

                    $totalCards += $companyCount->cards;
                    $totalFreight += ($freightAmount != "?" ? $freightAmount : 0);

                }

                if(count($shopUserCount) > 1) {
                    $dataList[] = array_merge($baseData,array("TOTAL","","AKTIVE KORT:",$totalCards,"TOTAL FRAGT:",$totalFreight));

                }




                // Output and exit
                if(count($dataList) > 200) {
                    break;
                    echo "<pre>".print_r($dataList,true)."</pre>";
                    \System::connection()->commit();
                    exit();
                }
            }

            $headers = array(
                "Ordre nr",
                "Shop",
                "Deadline",
                "Antal bestilt",
                "Virksomhed",
                "Modtager virksomhed",
                "Modtager postnr",
                "Dellevering (child)",
                "Aktive kort",
                "Fragtberegning",
                "Fragtbeløb"
            );

            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=fragtexport.csv');

            foreach($headers as $header) {
                echo $header.";";
            }

            foreach($dataList as $dataRow) {
                foreach($dataRow as $dataCell) {
                    echo utf8_encode($dataCell).";";
                }
                echo "\r\n";
            }

        }
    */

}
