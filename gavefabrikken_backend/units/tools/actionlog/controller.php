<?php

namespace GFUnit\tools\actionlog;
use GFBiz\units\UnitController;

class Controller extends UnitController
{

    public function __construct()
    {
        parent::__construct(__FILE__);
    }

    public function index() {

        echo "INGEN SIDE HER!";

    }

    private function isTechUser() {
        return in_array(\router::$systemUser->id, array(50));
    }

    public function company($companyid)
    {

        $companyid = intvalgf($companyid);

        try {
            $company = \Company::find($companyid);
        } catch (\Exception $e) {
            $company = null;
        }

        if($company == null || $company->id == 0) {
            echo "Kan ikke finde virksomhed, prøv igen.";
            exit();
        }

        $logList = $this->loadlogs("company",$company->id);

        $this->view("loglist", array("type" => "Virksomhed","endpoint" => "company/".$companyid,"showTech" => $this->isTechUser(), "typename" => $company->name." (cvr: ".$company->cvr.")","typeid" => $company->id,"loglist" => $logList));
    }

    private function loadlogs($type,$id) {

        $condition = "id = 0";
        if($type == "company") {
            $condition = "(a.company_id = ".intval($id)." OR (a.company_id in (select id from company where pid = ".intval($id)."))) ";
        }

        if(isset($_POST["query"]) && trimgf($_POST["query"]) != "") {

            $query = trimgf($_POST["query"]);

            $condition .= " AND (
        a.headline LIKE '%".addslashes($query)."%' OR
        a.details LIKE '%".addslashes($query)."%' OR
        a.ip LIKE '%".addslashes($query)."%' OR
        su.name LIKE '%".addslashes($query)."%' OR
        shu1.username LIKE '%".addslashes($query)."%' OR
        s.name LIKE '%".addslashes($query)."%' OR
        c.name LIKE '%".addslashes($query)."%' OR
        co.order_no LIKE '%".addslashes($query)."%' OR
        shu2.username LIKE '%".addslashes($query)."%' OR
        o.order_no LIKE '%".addslashes($query)."%'
    )";

        }

        if(!$this->isTechUser()) {
            $condition .= " && is_tech = 0 ";
        }

        $sql = "SELECT 
    a.*,
    su.name AS author_systemuser_name,
    shu1.username AS author_shopuser_username,
    s.name AS shop_name,
    c.name AS company_name,
    co.order_no AS company_order_orderno,
    shu2.username AS shop_user_username,
    o.order_no AS order_orderno
    FROM actionlog a
    LEFT JOIN system_user su ON a.author_systemuser_id = su.id
    LEFT JOIN shop_user shu1 ON a.author_shopuser_id = shu1.id
    LEFT JOIN shop s ON a.shop_id = s.id
    LEFT JOIN company c ON a.company_id = c.id
    LEFT JOIN company_order co ON a.company_order_id = co.id
    LEFT JOIN shop_user shu2 ON a.shop_user_id = shu2.id
    LEFT JOIN `order` o ON a.order_id = o.id WHERE ".$condition." ORDER BY a.created DESC";

        $logList = \Actionlog::find_by_sql($sql);
        return $logList;

    }



}