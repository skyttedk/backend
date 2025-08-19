<?php

namespace GFUnit\external\cvrsearch;
use GFBiz\units\UnitController;

class Controller extends UnitController
{

    public function __construct()
    {
        parent::__construct(__FILE__);

    }


    /**
     * SERVICES
     */


    public function cvr($language_code="",$cvr="")
    {

        // Check language_code
        if(trimgf($language_code) == "" || intval($language_code) <= 0) {
            echo json_encode(array("status" => 0,"error" => "No language code provided"));
            return;
        }

        // Check cvr number
        if(trimgf($cvr) == "") {
            echo json_encode(array("status" => 0,"error" => "No cvr number set"));
            return;
        }

        // Denmark - use bisnode
        if($language_code == 1) {
            $cvrapiClient = new \GFCommon\Model\Bisnode\BisnodeClient();
            $response = $cvrapiClient->searchCVR($cvr);
            if($response->companyBasic == null || !is_array($response->companyBasic) || countgf($response->companyBasic) == 0) {
                echo json_encode(array("status" => 0,"error" => "No results"));
                return;
            }
            echo json_encode(array("status" => 1, "company" => $cvrapiClient->frontendMapper($response)),JSON_PRETTY_PRINT);

        }
        // Norway - use cvrapi
        else if($language_code == 4) {
            $cvrapiClient = new \GFCommon\Model\External\CVRApiClient("NO");
            $response = $cvrapiClient->cvr($cvr);
            echo json_encode(array("status" => 1, "company" => $cvrapiClient->frontendMapper($response)),JSON_PRETTY_PRINT);
        }

        // Swedish - no provider found yet
        else if($language_code == 5) {
            throw new \Exception("No provider for Sweden");
        }

    }

    public function name($language_code="",$name="")
    {

        // Check language_code
        if(trimgf($language_code) == "" || intval($language_code) <= 0) {
            echo json_encode(array("status" => 0,"error" => "No language code provided"));
            return;
        }

        // Check input
        if(trimgf($name) == "") {
            echo json_encode(array("status" => 0,"error" => "No name set"));
            return;
        }

        // Denmark - use bisnode
        if($language_code == 1) {
            $cvrapiClient = new \GFCommon\Model\Bisnode\BisnodeClient();
            $response = $cvrapiClient->searchName($name);
            echo json_encode(array("status" => 1, "company" => $cvrapiClient->frontendMapper($response)),JSON_PRETTY_PRINT);
        }
        // Norway - use cvrapi
        else if($language_code == 4) {
            $cvrapiClient = new \GFCommon\Model\External\CVRApiClient("NO");
            $response = $cvrapiClient->name($name);
            echo json_encode(array("status" => 1, "company" => $cvrapiClient->frontendMapper($response)),JSON_PRETTY_PRINT);
        }

        // Swedish - no provider found yet
        else if($language_code == 5) {
            throw new \Exception("No provider for Sweden");
        }


    }

    public function cvrdetails($language_code="",$cvr="")
    {
        // Check language_code
        if(trimgf($language_code) == "" || intval($language_code) <= 0) {
            echo json_encode(array("status" => 0,"error" => "No language code provided"));
            return;
        }

        // Check cvr number
        if(trimgf($cvr) == "") {
            echo json_encode(array("status" => 0,"error" => "No cvr number set"));
            return;
        }

        // Denmark - use bisnode
        if($language_code == 1) {

            $cvrapiClient = new \GFCommon\Model\Bisnode\BisnodeClient();
            $response = $cvrapiClient->searchCVR($cvr);

            $details = array(
                "company" => $cvrapiClient->frontendMapper($response),
                "creditrating" => $cvrapiClient->getCreditRating($response->companyBasic[0]),
                "accountants" => $cvrapiClient->getAccountants($response->companyBasic[0]),
                "finances" => $cvrapiClient->getFinances($response->companyBasic[0]),
                "ownership" => $cvrapiClient->getOwnership($response->companyBasic[0]),
                "bankconnection" => $cvrapiClient->getBankConnections($response->companyBasic[0]),
                "decisionmakers" => $cvrapiClient->getDecisionMakers($response->companyBasic[0])
            );

            echo json_encode(array("status" => 1, "details" => $details),JSON_PRETTY_PRINT);

        }
        // Norway - use cvrapi
        else if($language_code == 4) {
            $cvrapiClient = new \GFCommon\Model\External\CVRApiClient("NO");
            $response = $cvrapiClient->cvr($cvr);
            echo json_encode(array("status" => 1, "company" => $cvrapiClient->frontendMapper($response)),JSON_PRETTY_PRINT);
        }

        // Swedish - no provider found yet
        else if($language_code == 5) {
            throw new \Exception("No provider for Sweden");
        }
    }

}