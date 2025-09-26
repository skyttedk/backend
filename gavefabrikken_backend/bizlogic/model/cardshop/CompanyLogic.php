<?php

namespace GFBiz\Model\Cardshop;

class CompanyLogic
{


    public static function createCompany($data)
    {

        $shippingPhone = "";
        if(isset($data["ship_to_phone"])) {
            $shippingPhone = $data["ship_to_phone"];
            unset($data["ship_to_phone"]);
        }

        // Check for invalid fields
        self::checkInvalidFields($data);

        //1. Create Company
        $data['username'] = $data['cvr'];
        $data['password'] = $data['cvr'];
        $data['is_gift_certificate'] = 1;

        // Set 2021 company data
        $data['active'] = 1;
        $data['deleted'] = 0;
        $data['token'] = self::findNewToken();
        $data['created_by'] = \router::$systemUser == null ? 0 : \router::$systemUser->id;
        $data['company_state'] = 1;

        // Check data
        if(!isset($data["language_code"])) {
            throw new \Exception("No language set");
        } else if(\GFBiz\Model\Config\LanguageLogic::validLanguage(intval($data["language_code"])) == false) {
            throw new \Exception("Invalid company language set");
        }

        // Create company
        $company = new \Company();
        $company->update_attributes($data);

        if($company->pid > 0) {
            $company->contact_phone = $shippingPhone;
        }

        $company->save();

        // Add action log
        \ActionLog::logAction("company", "Virksomhed oprettet: ".$company->name. " (cvr: ".$company->cvr.")",json_encode($data),0,0,$company->id,0,0,0,0);


        return $company;

    }

    public static function updateCompany($companyid,$data)
    {

        // Check if company_id is set, and id not, set id
        if(isset($data["company_id"])) unset($data["company_id"]);
        if(isset($data["id"])) unset($data["id"]);

        $shippingPhone = "";
        if(isset($data["ship_to_phone"])) {
            $shippingPhone = $data["ship_to_phone"];
            unset($data["ship_to_phone"]);
        }

        // Check for invalid fields
        self::checkInvalidFields($data);

        // Load company
        $company = \Company::find(intval($companyid));
        $company->update_attributes($data);
        $company->address_updated = 1;

        
/*
        // Check company country code
        if(\GFBiz\Model\Config\LanguageLogic::validLanguage(intval($company->language_code)) == false) {
            throw new \Exception("Invalid company language set");
        }
*/
        // If archived company state
        if($company->company_state == 0) {
            $company->company_state = 1;
        }

        // Update phone for childs
        if($company->pid > 0) {
            $company->contact_phone = $shippingPhone;
        }

        // Save company
        $company->save();

        // Add action log
        \ActionLog::logAction("company", "Virksomhed opdateret: ".$company->name. " (cvr: ".$company->cvr.")",json_encode($data),0,0,$company->id,0,0,0,0);

        // Update orders
        $companyorders = \CompanyOrder::find('all', array('conditions' => array( 'company_id' => $company->id)));
        if(count($companyorders) > 0) {
            foreach($companyorders as $companyorder) {

                // Archive first
                \CompanyOrder::archiveCompanyOrder($companyorder->id);

                // Update contact and company info
                $companyorder->company_name = $company->name;
                $companyorder->contact_name = $company->contact_name;
                $companyorder->contact_email = $company->contact_email;
                $companyorder->contact_phone = $company->contact_phone;
                $companyorder->cvr = $company->cvr;
                $companyorder->ean = $company->ean;

                // Update shipping
                $companyorder->ship_to_company = $company->ship_to_company;
                $companyorder->ship_to_address = $company->ship_to_address;
                $companyorder->ship_to_address_2 = $company->ship_to_address_2;
                $companyorder->ship_to_postal_code = $company->ship_to_postal_code;
                $companyorder->ship_to_city = $company->ship_to_city;
                $companyorder->ship_to_country = $company->ship_to_country;
/*
                // Update cardto
                $companyorder->cardto_name = $company->cardto_name;
                $companyorder->cardto_address = $company->cardto_address;
                $companyorder->cardto_address2 = $company->cardto_address2;
                $companyorder->cardto_postal_code = $company->cardto_postal_code;
                $companyorder->cardto_city = $company->cardto_city;
                $companyorder->cardto_country = $company->cardto_country;
*/
                // Save
                $companyorder->save();

            }
        }

        // Return company
        return $company;

    }

    private static function checkInvalidFields($companyData)
    {
        $invalidFields = array("company_state","created_by","created_date","nav_customer_no");
        foreach($invalidFields as $field) {
            if(isset($companyData[$field])) {
                throw new \Exception("Invalid update field set: ".$field);
            }
        }
    }

    public static function findNewToken()
    {
        $token = NewGUID();
        $company = \Company::find_by_sql("SELECT * FROM company WHERE token LIKE '".$token."'");
        if(count($company) > 0) return self::findNewToken();
        else return $token;
    }

}