<?php

namespace GFUnit\navision\synccompany;
use GFBiz\units\UnitController;
use GFCommon\Model\Navision\CustomerObj;
use GFCommon\Model\Navision\CustomerWS;
use GFCommon\Model\Navision\CustomerXML;
use GFCommon\Model\Navision\NavClient;
use GFCommon\Model\Navision\OrderWS;

class CompanySync
{

    /**
     * COMPANY STATES
     * 0: Arkiveret,
     * 1: Oprettet, ikke nav synkroniseret,
     * 2: afventer manuel godkendelse,
     * 3: godkendt, ikke synkroniseret,
     * 4: blokkeret,
     * 5: synkroniseret,
     * 6 synkronisering fejlet,
     * 7: child, synkroniseres ikke
     *
     */

    private $waitingCompanyList = null;
    private $outputMessages = false;
    private $customerNavClient;
    private $blockMessages = array();
    private $isTechBlock = false;

    public function __construct($output=false)
    {
        //\GFCommon\Model\Navision\NavClient::setNavDevMode(true);
        $this->outputMessages = $output;
    }

    public function getCompanyForSync()
    {
        $this->loadWaiting();
        return $this->waitingCompanyList;
    }

    public function countWaiting()
    {
        $this->loadWaiting();
        return countgf($this->waitingCompanyList);
    }

    public function syncAll()
    {
        $this->loadWaiting();
        $this->log("Start sync all, loaded ".$this->countWaiting());
        foreach($this->waitingCompanyList as $index => $company) {
            $this->syncCompany($company);
            //if($index > 25) return;
            return;
        }
        $this->waitingCompanyList = null;
    }

    public function syncAllJob()
    {
        $this->loadWaitingJob();
        $this->log("Start sync all, loaded ".$this->countWaiting());
        foreach($this->waitingCompanyList as $company) {
            $this->syncCompany($company);
        }
        $this->waitingCompanyList = null;
    }

    public function showNext()
    {
        $this->loadWaiting();
        echo "Waiting for sync: ".countgf($this->waitingCompanyList)."<br>";
        foreach($this->waitingCompanyList as $company) {
            echo $company->id.": ".$company->name." (".$company->language_code.")<br>";
        }
    }

    public function syncCompany(\Company $c)
    {

        $company = \Company::find($c->id);

        $this->logNewSync();
        $this->log("Start syncing ".$company->id." - ".$company->name);
        $this->blockMessages = array();
        $this->isTechBlock = false;

        // Check company
        if($company->id != $c->id || !in_array($company->company_state,array(1,3))) {
            $this->log("Abort sync, id or state mismatch");
            return;
        }


        // If company has parent, do not sync
        if($company->pid > 0) {

            $this->log(" - Company is a child, do not sync with navision");

            try {
                $parentCompany = \Company::find($company->pid);
                if($parentCompany->language_code != $company->language_code) {
                    \BlockMessage::createCompanyBlock($company->id,"COMPANY_PARENT_LANGDIF","Parent and child has different language codes.",true,$this->syncMessages);
                }
                else {
                    $this->log(" - State updated to child company");
                    $company->company_state = 7;
                    $company->save();
                }
            }
            catch(\Exception $e) {
                \BlockMessage::createCompanyBlock($company->id,"COMPANY_PARENT_INVALID","Could not find parent (".$company->pid.") in navision.",true,$this->syncMessages);
            }

            // Commit after each sync
            \system::connection()->commit();
            \System::connection()->transaction();
            return;
        }

        // Company cleared, sync with nav
        if($company->company_state == 3)
        {

            $this->log(" - Company is cleared for sync");

            // Customer number set, do not sync, but check
            if($company->nav_customer_no > 0) {

                $this->log(" - Company already has nav customer no");
                $validCustomer = true;

                try {
                    $customer = $this->getCustomerWS($company->language_code)->getByCustomerNo($company->nav_customer_no);
                    if($customer == null) {
                        $this->log(" - Navision customer not set");
                        $validCustomer = false;
                    }
                    else if($customer->getCustomerNo() != $company->nav_customer_no) {
                        $this->log(" - Navision customer number does not match");
                        $validCustomer = false;
                    }
                } catch (\Exception $e) {
                    $this->log(" - Navision exception, could not lookup customer: ".$e->getMessage());
                    $validCustomer = false;
                }

                // The set customer if valid, set to synced
                if($validCustomer == true) {

                    $this->log(" - Existing company number checked and cleared, company is synced");
                    $company->excempt_invoicefee = $customer->isExcemptFromInvoiceFee() ? 1 : 0;
                    $company->excempt_envfee = $customer->isExcemptFromEnvFee() ? 1 : 0;
                    $company->company_state = 5;
                    $company->save();

                }

                else {

                    $this->log(" - Existing customer number could not be validated, save in error state");
                    $company->company_state = 6;
                    $company->save();

                    \BlockMessage::createCompanyBlock($company->id,"COMPANY_APPROVED_NOTVALIDATED","Kunde ikke finde debitor nr ".$company->nav_customer_no." i navision.",true,$this->syncMessages);

                }

            }

            // Customer number not set, create new
            else
            {
                $this->createCompanyInNav($company);
            }

        }

        // New company, check if it can be auto-cleared
        else {

            $hasBlock = false;

            if(trimgf($company->contact_email) != "" && filter_var($company->contact_email,FILTER_VALIDATE_EMAIL) == false) {
                \BlockMessage::createCompanyBlock($company->id,"COMPANY_EMAIL_INVALID","Kontaktperson e-mail ikke en korrekt e-mail adresse: ".$company->contact_email,false,"");
                $hasBlock = true;
                $company->company_state = 2;
                $company->save();
            }

            if(trimgf($company->bill_to_email) != "" && filter_var($company->bill_to_email,FILTER_VALIDATE_EMAIL) == false) {
                \BlockMessage::createCompanyBlock($company->id,"COMPANY_EMAIL_INVALID","Faktura e-mail ikke en korrekt e-mail adresse: ".$company->bill_to_email,false,"");
                $hasBlock = true;
                $company->company_state = 2;
                $company->save();
            }


            if($this->isTestCompany($company)) {
                \BlockMessage::createCompanyBlock($company->id,"COMPANY_SUSPECTED_TEST","Kunde har test eller gavefabrikken data, er muligvis en test kunde, godkend kun hvis den skal overføres til navisino.",false,$this->syncMessages);
                $this->log(" - Blocked, looks like test: COMPANY_SUSPECTED_TEST");
                $company->company_state = 2;
                $company->save();
                $hasBlock = true;
            }

            // DK, check cvr
            if($company->language_code == 1) {
                $cvrapiClient = new \GFCommon\Model\Bisnode\BisnodeClient();
                $response = $cvrapiClient->searchCVR($company->cvr);
                if($response == null || $response->companyBasic == null || !is_array($response->companyBasic) || countgf($response->companyBasic) == 0) {
                    \BlockMessage::createCompanyBlock($company->id,"COMPANY_INVALID_CVR","Kundens cvr nr ".$company->cvr." kunne ikke findes, tjek at det er et gyldigt cvr nr.",false,$this->syncMessages);
                    $this->log(" - Blocked, cvr ".$company->cvr." not found in bisnode: COMPANY_INVALID_CVR");
                    $company->company_state = 2;
                    $company->save();
                    $hasBlock = true;
                    mailgf("sc@interactive.dk", "BISNODE CVR NOT FOUND","Could not find ".$company->cvr." for company ".$company->id."<br>\nBisnode response<br>\n".print_r($response,true));
                }
            }

            // Check country
            if($company->language_code == 0 || !\GFBiz\Model\Config\LanguageLogic::validLanguage($company->language_code)) {

                $this->log(" - Company does not have valid language, set to blocked");

                $company->company_state = 2;
                $company->save();

                \BlockMessage::createCompanyBlock($company->id,"COMPANY_MISSING_LANGUAGE","Kunde er ikke tildelt en landekode: ".$company->language_code."",true,$this->syncMessages);

            }

            // If nav customer is set - check against it
            else if($company->nav_customer_no > 0) {

                $this->log(" - Company has customer no already ".$company->nav_customer_no);

                if($this->checkAgainstExistingNavision($company)) {

                    $this->log(" - Company is a match, auto complete sync");
                    $company->company_state = 5;
                    $company->save();

                }

                else {

                    $this->log(" - Company is not a match, needs approval");
                    $company->company_state = 2;
                    $company->save();

                    \BlockMessage::createCompanyBlock($company->id,"COMPANY_BAD_MATCH","Valgte kunde nr har ikke et godt match i navision: ".implode(", ",$this->blockMessages).".",false,$this->syncMessages);

                }

            }

            // If nav customer not set, look it up
            else if($hasBlock == false){

                // Check for customer in navision
                $this->log(" - Search navision for existing customer match");

                $lookupResult = $this->lookupInNavision($company);
                if($this->navCheckBlocked == false) {

                    // Approved to create new company
                    if($lookupResult == null) {

                        // Check if company has valid data
                        if($this->checkCompanyData($company)) {
                            $this->createCompanyInNav($company);
                        }

                        // Block for bad data
                        else {
                            $this->log(" - Company missing data: ".implode(", ",$this->lastCheckMessages));
                            $company->company_state = 2;
                            $company->save();

                            \BlockMessage::createCompanyBlock($company->id,"COMPANY_DATA_MISSING","Kunde mangler data: ".implode(", ",$this->lastCheckMessages),false,$this->syncMessages);

                        }

                    }

                    // Use existing customer
                    else {
                        $company->excempt_invoicefee = $lookupResult->isExcemptFromInvoiceFee() ? 1 : 0;
                        $company->excempt_envfee = $lookupResult->isExcemptFromEnvFee() ? 1 : 0;
                        $company->nav_customer_no = $lookupResult->getCustomerNo();
                        $company->company_state = 5;
                        $company->save();
                    }

                }

            }

        }

        // Commit after each sync
        \system::connection()->commit();
        \System::connection()->transaction();
    }

    /**
     * Check company for valid data
     */

    private $lastCheckMessages;

    private function checkCompanyData(\Company $company)
    {
        $this->lastCheckMessages = array();
        if(trimgf($company->name) == "") $this->lastCheckMessages[] = "Missing company name";
        if(trimgf($company->bill_to_address) == "") $this->lastCheckMessages[] = "Missing company address";
        if(trimgf($company->bill_to_postal_code) == "") $this->lastCheckMessages[] = "Missing postal code";
        if(trimgf($company->bill_to_city) == "") $this->lastCheckMessages[] = "Missing city";
        if(trimgf($company->contact_email) == "") $this->lastCheckMessages[] = "Missing e-mail";
        if(trimgf($company->contact_phone) == "") $this->lastCheckMessages[] = "Missing phone number";
        if(trimgf($company->cvr) == "") $this->lastCheckMessages[] = "Missing VAT number";
        return countgf($this->lastCheckMessages) == 0;
    }

    /**
     * Create company in navision
     */

    private function createCompanyInNav(\Company $company)
    {

        // NO HANDELSBANKEN
        if($company->language_code == 4 && $company->cvr == "971171324") {
            $company->company_state = 5;
            $company->nav_customer_no = 8912;
            $company->save();
            return;
        }

        if($this->hasImportOrders($company)) {
            if(\BlockMessage::hasApprovedMessage("COMPANY_NEW_IMPORT",$company->id,0,0) == false) {
                \BlockMessage::createCompanyBlock($company->id,"COMPANY_NEW_IMPORT","Virksomheden er ny og har web-bestillinger, tjek virksomheden før den oprettes.",false,$this->syncMessages);
                $this->log(" - Blocked, new with import orders: COMPANY_NEW_IMPORT");
                $company->company_state = 2;
                $company->save();
                return;
            }
        }

        // Generate company xml
        try {
            $companyXML = new \GFCommon\Model\Navision\CustomerXML($company);
            $this->log(" - Company xml:<br>".$companyXML->getXML()."<br>");
        }
        catch (\Exception $e) {
            $this->log(" - Error creating customer xml document: ".$e->getMessage());
            $company->company_state = 6;
            $company->save();

            \BlockMessage::createCompanyBlock($company->id,"COMPANY_XML_EXCEPTION",$e->getMessage(),true,$this->syncMessages);
            return;
        }


        // Upload to navision
        try {

            $client = new \GFCommon\Model\Navision\OrderWS($company->language_code);
            $client->uploadCustomerDoc($companyXML->getXML());

            // Update company
            $company->company_state = 5;
            $company->nav_customer_no = $client->getLastCustomerNo();
            $company->save();

            \ActionLog::logAction("CompanyNavCreated", "Virksomhed synkroniseret til navision: ".$company->name.", har fået debitor nr. ".$company->nav_customer_no,"",0,0,$company->id,0,0,0,0);
            
            // Update last company number if web order
            if($companyXML->getCustomerNumber() > 0) {
                \NavisionCompanyNo::setUsedCompanyNo($company->language_code,$companyXML->getCustomerNumber());
            }


        } catch (\Exception $e) {
            $this->log(" - Error creating customer in navision: ".$e->getMessage());
            $company->company_state = 6;
            $company->save();

            \BlockMessage::createCompanyBlock($company->id,"COMPANY_CREATE_EXCEPTION",$e->getMessage(),true,$this->syncMessages);

        }

    }

    /**
     * Check new company against customers in navision
     */

    private $navCheckMatches = 0;
    private $navCheckBlocked = false;

    public function lookupInNavision(\Company $company,$useBestMatch=false)
    {

        $this->navCheckBlocked = false;

        try {

            // Create customer client service
            $customerClient = $this->getCustomerWS($company->language_code);
            $matchList = array();

            // Look for companies in navision
            if(trimgf($company->ean) != "") {
                $matchList = $customerClient->searchByEAN(trimgf($company->ean),100);
                $this->log(" -- Found ".countgf($matchList)." from ean number");
            }

            if(count($matchList) == 0 && trimgf($company->cvr) != ""){
                $matchList = $customerClient->searchByCVR(trimgf(str_replace(array(" ","-"),"",$company->cvr)),100);
                $this->log(" -- Found ".countgf($matchList)." from cvr number");
            }

            if(count($matchList) == 0)
            {
                $matchList = $customerClient->searchByName($company->name,true,100);
                $this->log(" -- Found ".countgf($matchList)." from name match");
            }

        } catch (\Exception $e) {

            $this->log(" -- Exception checking navision: ".$e->getMessage());
            $this->navCheckBlocked = true;

            $company->company_state = 6;
            $company->save();

            \BlockMessage::createCompanyBlock($company->id,"COMPANY_LOOKUP_EXCEPTION",$e->getMessage(),true,$this->syncMessages);

            return null;
        }

        // New customer, create in nav
        if(count($matchList) == 0) {
            $this->navCheckBlocked = false;
            return null;
        }

        // Look for perfect matches
        $perfectMatchList = array();
        $bestMatch = null;
        $bestMatchCount = 0;

        foreach($matchList as $match) {

            $perfectMatch = true;
            $matches = 0;

            $this->log(" -- Check ".$match->getCustomerNo().": ".$match->getName());

            if($this->checkString($company->name) != $this->checkString($match->getName())) {
                $this->log(" -- name mismatch (cs: ".trimgf(mb_strtolower($company->name))." / nav: ".trimgf(mb_strtolower($match->getName())).")");
                $perfectMatch = false;
            } else {
                $matches++;
            }

            if($this->checkString($company->bill_to_address) != $this->checkString($match->getAddress())) {
                $this->log(" -- bill_to_address mismatch (cs: ".trimgf(mb_strtolower($company->bill_to_address))." / nav: ".trimgf(mb_strtolower($match->getAddress())).")");
                $perfectMatch = false;
            } else {
                $matches++;
            }

            if($this->checkString($company->bill_to_postal_code) != $this->checkString($match->getPostCode())) {
                $this->log(" -- bill_to_postal_code mismatch (cs: ".trimgf(mb_strtolower($company->bill_to_postal_code))." / nav: ".trimgf(mb_strtolower($match->getPostCode())).")");
                $perfectMatch = false;
            } else {
                $matches++;
            }

            if($this->checkString($company->bill_to_city) != $this->checkString($match->getCity())) {
                $this->log(" -- bill_to_city mismatch (cs: ".trimgf(mb_strtolower($company->bill_to_city))." / nav: ".trimgf(mb_strtolower($match->getCity())).")");
                $perfectMatch = false;
            } else {
                $matches++;
            }

            /*
            if(trimgf(mb_strtolower($company->bill_to_country)) != trimgf(mb_strtolower($match->getCountryCode()))) {
                $this->log(" -- bill_to_country mismatch");
                $perfectMatch = false;
            }
            */

            if($this->checkString($company->cvr) != $this->checkString($match->getCVR())) {
                $this->log(" -- cvr mismatch (cs: ".trimgf(mb_strtolower($company->cvr))." / nav: ".trimgf(mb_strtolower($match->getCVR())).")");
                $perfectMatch = false;
            } else {
                $matches++;
            }

            if($this->checkString($company->ean) != $this->checkString($match->getEAN())) {
                $this->log(" -- ean mismatch (cs: ".trimgf(mb_strtolower($company->ean))." / nav: ".trimgf(mb_strtolower($match->getEAN())).")");
                $perfectMatch = false;
            } else {
                $matches++;
            }

            if($match->isBlocked()) {
                $this->log(" -- customer is blocked in nav!");
                $perfectMatch = false;
                $matches -= 5;
            }

            if($perfectMatch) {
                $perfectMatchList[] = $match;
            }

            if($bestMatch == null || $matches > $bestMatchCount) {
                $bestMatch = $match;
                $bestMatchCount = $matches;
            }

        }

        // No perfect matches
        if(count($perfectMatchList) == 0) {

            if($useBestMatch == true) {
                $perfectMatchList[] = $bestMatch;
                $this->log(" -- no perfect match, best match upgraded to perfect match.");
            }
            else {

                $this->log(" -- No matches were a perfect match, send to manual approval");
                $this->navCheckBlocked = true;
                $company->company_state = 2;
                $company->save();

                \BlockMessage::createCompanyBlock($company->id,"COMPANY_NEW_MISMATCH","Fandt ".countgf($matchList)." resultater i navision på kunden. Bedste match er: ".$bestMatch->getName()." (debitor nr.: ".$bestMatch->getCustomerNo()."), ".$bestMatch->getAddress().", ".$bestMatch->getPostCode()." ".$bestMatch->getCity(),false,$this->syncMessages);
                return null;

            }

        }



        // 1 perfect match (use it)
        $this->log(" -- 1 perfect match found: ".$perfectMatchList[0]->getCustomerNo()." ");
        $this->navCheckBlocked = false;
        return $perfectMatchList[0];

    }

    /**
     * Check new company against existing navision customer
     */

    private function checkAgainstExistingNavision(\Company $company)
    {

        $validCustomer = true;

        try {
            $customer = $this->getCustomerWS($company->language_code)->getByCustomerNo($company->nav_customer_no);
            if($customer == null) {
                $this->blockMessages[] = "Could not find customer number ".$company->nav_customer_no." in navision";
                $this->log(" - Navision customer not set");
                $validCustomer = false;
            }
            else if($customer->getCustomerNo() != $company->nav_customer_no) {
                $this->blockMessages[] = "Could not find customer number ".$company->nav_customer_no." in navision (no mismatch)";
                $this->isTechBlock = true;
                $this->log(" - Navision customer number does not match");
                $validCustomer = false;
            }
            else {

                $this->log(" - Has navision match");
                //$this->log("<pre>".print_r($customer,true)."</pre>");

                if($this->checkString($company->name) != $this->checkString($customer->getName())) {
                    $this->log(" -- Name mismatch");
                    $this->blockMessages[] = "Invoice name does not match (cs: ".trimgf(mb_strtolower($company->name))." / nav: ".trimgf(mb_strtolower($customer->getName())).")";
                    $validCustomer = false;
                }

                if($this->checkString($company->bill_to_address) != $this->checkString($customer->getAddress())) {
                    $this->log(" -- Address mismatch");
                    $this->blockMessages[] = "Invoice address does not match (cs: ".trimgf(mb_strtolower($company->bill_to_address))." / nav: ".trimgf(mb_strtolower($customer->getAddress())).")";
                    $validCustomer = false;
                }

                if($this->checkString($company->bill_to_postal_code) != $this->checkString($customer->getPostCode())) {
                    $this->log(" -- Post code mismatch");
                    $this->blockMessages[] = "Invoice post code does not match (cs: ".trimgf(mb_strtolower($company->bill_to_postal_code))." / nav: ".trimgf(mb_strtolower($customer->getPostCode())).")";
                    $validCustomer = false;
                }

                if($this->checkString($company->bill_to_city) != $this->checkString($customer->getCity())) {
                    $this->log(" -- City mismatch");
                    $this->blockMessages[] = "Invoice city does not match (cs: ".trimgf(mb_strtolower($company->bill_to_city))." / nav: ".trimgf(mb_strtolower($customer->getCity())).")";
                    $validCustomer = false;
                }
/*
                if($this->checkString($company->bill_to_country) != $this->checkString($customer->getCountryCode())) {
                    $this->log(" -- Country mismatch");
                    $this->blockMessages[] = "Invoice country does not match (cs: ".trimgf(mb_strtolower($company->name))." / nav: ".trimgf(mb_strtolower($customer->getName())).")";
                    $validCustomer = false;
                }
*/
                if($this->checkString($company->cvr) != $this->checkString($customer->getCVR())) {
                    $this->log(" -- CVR mismatch");
                    $this->blockMessages[] = "Invoice CVR does not match (cs: ".trimgf(mb_strtolower($company->cvr))." / nav: ".trimgf(mb_strtolower($customer->getCVR())).")";
                    $validCustomer = false;
                }

                if($this->checkString($company->ean) != $this->checkString($customer->getEAN())) {
                    $this->log(" -- EAN mismatch");
                    $this->blockMessages[] = "Invoice EAN does not match (cs: ".trimgf(mb_strtolower($company->ean))." / nav: ".trimgf(mb_strtolower($customer->getEAN())).")";
                    $validCustomer = false;
                }

            }

        } catch (\Exception $e) {
            $this->blockMessages[] = "Error looking up customer no ".$company->nav_customer_no." in navision";
            $this->isTechBlock = true;
            $this->log(" - Navision exception, could not lookup customer: ".$e->getMessage());
            $validCustomer = false;

        }

        return $validCustomer;

    }

    /**
     * HELPERS
     */

    private function checkString($val) {

        $val = trimgf(mb_strtolower($val));

        // Remove double space
        $val = str_replace("  "," ",$val,$count);

        // Remove special adress ambiguities
        $val = str_replace(array(". sal",".th",". th",".tv",". tv","a/s","aps","i/s"),"",$val);

        // Match å with aa
        $val = str_replace(array("å",utf8_encode("å"),utf8_decode("å")),"aa",$val);
        $val = str_replace(array(" og "," och"),"&",$val);

        // Remove special characters
        $val = str_replace(array(",",".","-","_","(",")","=","#","\"","'","*","|","?","!"," "),"",$val,$count);

        return $val;

    }

    private $customerWs = array();
    private $messages = array();
    private $syncMessages = array();

    private function getCustomerWS($countryCode)
    {
        if(intval($countryCode) <= 0) {
            throw new \Exception("Trying to create customer service with no nav country");
        }

        if(isset($this->customerWs[intval($countryCode)])) {
            return $this->customerWs[intval($countryCode)];
        }

        $this->customerWs[intval($countryCode)] = new \GFCommon\Model\Navision\CustomerWS(intval($countryCode));

        return $this->customerWs[intval($countryCode)];
    }

    private function logNewSync() {
        $this->syncMessages = array();
    }

    private function log($message) {
        if($this->outputMessages) {
            echo htmlentities($message)."<br>";
        }
        $this->messages[] = $message;
        $this->syncMessages[] = $message;
    }

    private function loadWaiting()
    {

        $languages = array(1,4,5);
        $lockToSalesPerson = "";
        $lockToDeadline = "";

        if($this->waitingCompanyList != null) return;

        $salesLockSQL = "";
        if($lockToSalesPerson != null && $lockToSalesPerson != "") {
            $salesLockSQL = " && company.id IN (SELECT company_id FROM company_order WHERE salesperson = '".$lockToSalesPerson."')";
        }

        $dateLockSQL = "";
        if($lockToDeadline != null && $lockToDeadline != "") {
            $dateLockSQL = " && company.id IN (SELECT company_id FROM company_order WHERE expire_date = '".$lockToDeadline."')";
        }

        $extraCompanyLockSQL = "";
        $extraCompanyOrderLockSQL = "";
        //$extraCompanyOrderLockSQL = " && id NOT IN (150,196,279,298,463,536,683,712,718,737,956,958,963,966,994,1000,1021,1022,1057,1073,1114,1122,1170,1387,1408,1445,1521,1538,1586,1591,1628,1711,1727,1735,1744,1762,1778,1923,1956,1963,1982,2034,2038,2046,2050,2072,2081,2111,2234,2277,2423,2424,2456,2470,2595,2603,2611,2647,2678,2710,2796,2801,2915,2938,2991,2993,3001,3033,3125,3127)";

        //$this->waitingCompanyList = \Company::find_by_sql("SELECT * FROM company WHERE company_state IN (1,3) && language_code in (".implode(",",$languages).")".$salesLockSQL);
        //$sql = "SELECT company.* FROM company LEFT JOIN company_order ON company.id = company_order.company_id WHERE order_state not in (7,8) && company_state IN (1,3) ".$extraCompanyLockSQL." ".$salesLockSQL." ".$dateLockSQL." && language_code in (".implode(",",$languages).") &&  company.id IN (select company_id from company_order WHERE order_state NOT IN (7,8) ".$extraCompanyOrderLockSQL.") order by company_order.id asc";
        $sql = "SELECT company.* FROM company LEFT JOIN company_order ON company.id = company_order.company_id WHERE order_state not in (7,8) && company_state IN (1,3) ".$extraCompanyLockSQL." ".$salesLockSQL." ".$dateLockSQL." && language_code in (".implode(",",$languages).") order by company_order.id asc";
        $this->waitingCompanyList = \Company::find_by_sql($sql);


    }

    private function loadWaitingJob()
    {
        $languages = array(1,4,5);
        if($this->waitingCompanyList != null) return;
        //$sql = "SELECT company.* FROM company LEFT JOIN company_order ON company.id = company_order.company_id WHERE order_state not in (7,8) && company_state IN (1,3) && language_code in (".implode(",",$languages).") &&  company.id IN (select company_id from company_order WHERE order_state NOT IN (7,8)) order by company_order.id asc";
        $sql = "SELECT company.* FROM company LEFT JOIN company_order ON company.id = company_order.company_id WHERE order_state not in (7,8) && company_state IN (1,3) && language_code in (".implode(",",$languages).") order by company_order.id asc";
        $this->waitingCompanyList = \Company::find_by_sql($sql);
    }

    private function hasImportOrders(\Company $company) {
        $orders = \CompanyOrder::find("all",array("conditions" => array("company_id" => $company->id,"salesperson" => "IMPORT")));
        return countgf($orders) > 0;
    }

    private function isTestCompany(\Company $company)
    {

        // Return false if previously approved
        $approvedBlocks = \BlockMessage::find("all",array("conditions" => array("company_id" => $company->id,"block_type" => "COMPANY_SUSPECTED_TEST","release_status" => 1)));
        if(count($approvedBlocks) > 0) {
            return false;
        }

        if(strstr($company->name,"test")) {
            return true;
        }

        if(strstr($company->cvr,"11111111")) {
            return true;
        }

        if(strstr($company->ean,"11111111")) {
            return true;
        }

        if(strstr($company->bill_to_address,"test")) {
            return true;
        }

        if(strstr($company->bill_to_email,"@gavefabrikken.dk") || strstr($company->bill_to_email,"@interactive.dk")) {
            return true;
        }

        if(strstr($company->ship_to_company,"test")) {
            return true;
        }

        if(strstr($company->contact_name,"test")) {
            return true;
        }

        if(strstr($company->contact_email,"@gavefabrikken.dk") || strstr($company->contact_email,"@interactive.dk")) {
            return true;
        }

        return false;

    }

}