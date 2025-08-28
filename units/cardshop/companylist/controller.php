<?php

namespace GFUnit\cardshop\companylist;
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



    public function search2(){

        $companies = \Company::find('all', array('conditions' => array(

            'language_code = ? AND
            deleted = ?  AND
            pid = ?  AND
            is_gift_certificate = ? AND (
            phone LIKE CONCAT("%", ? ,"%") OR
            name LIKE CONCAT("%", ? ,"%") OR
            ship_to_company LIKE CONCAT("%", ? ,"%") OR
            cvr LIKE CONCAT("%", ? ,"%") OR
            contact_name LIKE CONCAT("%", ? ,"%") OR
            ean LIKE CONCAT("%", ? ,"%") OR
            bill_to_address LIKE CONCAT("%", ? ,"%") OR
            contact_phone LIKE CONCAT("%", ? ,"%") OR
            contact_email LIKE CONCAT("%", ? ,"%") )'
        ,$_POST["LANGUAGE"], 0,0,1,$_POST['text'],$_POST['text'],$_POST['text'],$_POST['text'],$_POST['text'],$_POST['text'],$_POST['text'],$_POST['text'],$_POST['text']),  'order' => 'name', 'limit' => '50'
        ));
        $list = [];
        foreach ($companies as $company){
              array_push($list, $company->id);
        }
        if(sizeofgf($list) > 0){
          $companies = \Company::find_by_sql("SELECT * from company where id in(".implode($list,',').") or pid in(".implode($list,',').") and deleted = 0 or id in ( select `company_id` from company_order WHERE order_no = '".$_POST['text']."' )  AND is_gift_certificate = 1 order by id");
        } else {
          $companies = \Company::find_by_sql("SELECT * from company where id in ( select `company_id` from company_order WHERE order_no = '".$_POST['text']."' )  AND is_gift_certificate = 1 order by id");
        }
        echo json_encode(array("status" => 1, "result" => $companies ),JSON_PRETTY_PRINT);
    }

    public function search(){
        $companies = \Company::find('all', array('conditions' => array(

            'language_code = ? AND
            deleted = ?  AND
            pid = ?  AND
            is_gift_certificate = ? AND (
            phone LIKE CONCAT("%", ? ,"%") OR
            name LIKE CONCAT("%", ? ,"%") OR
            ship_to_company LIKE CONCAT("%", ? ,"%") OR
            cvr LIKE CONCAT("%", ? ,"%") OR
            contact_name LIKE CONCAT("%", ? ,"%") OR
            ean LIKE CONCAT("%", ? ,"%") OR
            bill_to_address LIKE CONCAT("%", ? ,"%") OR
            contact_phone LIKE CONCAT("%", ? ,"%") OR
            contact_email LIKE CONCAT("%", ? ,"%") )'
        ,$_POST["LANGUAGE"], 0,0,1,$_POST['text'],$_POST['text'],$_POST['text'],$_POST['text'],$_POST['text'],$_POST['text'],$_POST['text'],$_POST['text'],$_POST['text']),  'order' => 'name', 'limit' => '300'
        ));
        $list = [];
        foreach ($companies as $company){
              array_push($list, $company->id);
        }



        if(sizeofgf($list) > 0){
          $companies = \Company::find_by_sql("SELECT * from company where id in(".implode(',',$list).") or pid in(".implode(',',$list).") and deleted = 0 or id in ( select `company_id` from company_order WHERE order_no = '".addslashes($_POST['text'])."' )  AND is_gift_certificate = 1 order by id ");
        } else {

          $companies = \Company::find_by_sql("SELECT * from company where id in ( select `company_id` from company_order WHERE order_no = '".$_POST['text']."' ) or id in ( select id from company where pid in( select `company_id` from company_order WHERE order_no = '".$_POST['text']."' ))   AND is_gift_certificate = 1 order by id");
        }
        echo json_encode(array("status" => 1, "result" => $companies ),JSON_PRETTY_PRINT);
    }

    public function childs($parentCompanyID)
    {
        $companies = \Company::find('all', array('conditions' => array('deleted = 0  AND is_gift_certificate = 1 AND pid > 0 && pid = '.intval($parentCompanyID)),  'order' => 'name', 'limit' => '100'));
        echo json_encode(array("status" => 1, "result" => $companies ),JSON_PRETTY_PRINT);
    }

    public function switchLang()
    {
        // Get user ID and language from POST data
        $userId = $_POST['user_id'];
        $newLanguage = $_POST['language'];
        
        // Security check: Only allow user ID 340 to change languages in production
        if($userId != 340) {
            echo json_encode(array("status" => 0, "message" => "Access denied - Language change not allowed for this user"));
            return;
        }
        
        // First, check if we need to backup the original language
        $sqlCheck = "SELECT language, language_original FROM system_user WHERE id = ".$userId;
        $checkResult = \Dbsqli::getSql2($sqlCheck);
        
        $backupOriginal = false;
        if($checkResult && count($checkResult) > 0) {
            $currentData = $checkResult[0];
            $currentLanguage = $currentData['language'];
            $languageOriginal = $currentData['language_original'];
            
            // If language_original is 0 or null, backup current language
            if($languageOriginal == 0 || $languageOriginal == null || $languageOriginal == '') {
                $sqlBackup = "UPDATE system_user SET language_original = ".$currentLanguage." WHERE id = ".$userId;
                $backupResult = \Dbsqli::setSql2($sqlBackup);
                $backupOriginal = true;
            }
        }
        
        // Update current language using direct SQL
        $sql2 = "UPDATE system_user SET language = ".$newLanguage." WHERE id = ".$userId;
        $u2 = \Dbsqli::setSql2($sql2);
        
        // Return response in the format expected by the ajax utility
        $result = array(
            "user_id" => $userId,
            "language" => $newLanguage,
            "original_backed_up" => $backupOriginal,
            "message" => "Language updated successfully" . ($backupOriginal ? " (original language backed up)" : "")
        );
        
        echo json_encode(array("status" => 1, "result" => $result));
    }

}