<?php

namespace GFUnit\navision\salesperson;
use GFBiz\units\UnitController;

class Controller extends UnitController
{

    public function __construct()
    {
        parent::__construct(__FILE__);

    }

    public static function outputView($viewName="view")
    {
        $controller = new Controller();
        $controller->view($viewName);
    }

    /**
     * SERVICES
     */
    
    public function getsalespersons()
    {
        $salespersonWS = new \GFCommon\Model\Navision\SalesPersonWS();
        $list = $salespersonWS->getAllSalesPerson();
        $list = $this->getSalesPersonList($list);
        echo json_encode(array("status" => 1,"salespersons" => $list));

    }
    
    public function getsalespersonoptions($selected="") {
        $salespersonWS = new \GFCommon\Model\Navision\SalesPersonWS();
        $list = $salespersonWS->getAllSalesPerson();
        $list = $this->getSalesPersonList($list);

        $options = "";
        foreach($list as $salesPerson) {
            $options .= "<option value='".$salesPerson["Code"]."' ".(trimgf(strtolower($selected)) == trimgf(strtolower($salesPerson["Code"])) ? "selected" : "").">".$salesPerson["Code"].": ".$salesPerson["Name"]."</option>";
        }

        echo json_encode(array("status" => 1,"options" => $options));
    }
    
    public function searchsalespersonname($name="") {
        $salespersonWS = new \GFCommon\Model\Navision\SalesPersonWS();
        $list = $salespersonWS->searchSalesPerson(\GFCommon\Model\Navision\SalesPersonWS::FILTER_NAME,$name);
        $list = $this->getSalesPersonList($list);
        echo json_encode(array("status" => 1,"salespersons" => $list));
    }
    
    public function validsalespersoncode($code="") {
        $salespersonWS = new \GFCommon\Model\Navision\SalesPersonWS();
        $validCode = false;
        if($code != "") {
            $validCode = $salespersonWS->validSalesPersonCode($code);
        }
        echo json_encode(array("status" => 1,"validcode" => $validCode));
    }

    /**
     * PRIVATE HELPERS
     * @param $list
     * @return array
     */

    private function getSalesPersonList($list) {
        
        $retList = array();
        foreach($list as $salesperson) {
            $retList[] = $salesperson->mapToCardshopData();
        }
        return $retList;
        
    }




}