<?php

namespace GFUnit\navision\synccompany;
use GFBiz\units\UnitController;

class Controller extends UnitController
{

    public function __construct()
    {
        parent::__construct(__FILE__);
    }

    public function runsync()
    {
        echo "RUN COMPANY SYNC";
        $model = new CompanySync(true);
        $model->syncAll();
    }


    public function syncid($companyid=0)
    {
        //\GFCommon\Model\Navision\NavClient::setNavDevMode(true);
        echo "Start sync company id: ".intval($companyid);
        $company = \Company::find(intval($companyid));
        $model = new CompanySync(true);
        $model->syncCompany($company);
    }


    public function check()
    {
        // CHECK FOR PROBLEMS
    }

    public function dashboard()
    {
        $dashboard = new Dashboard();
        $dashboard->dashboard();
    }

    public function blocklist($type="",$id=0)
    {

        $blockList = new BlockMessage();

        if($type == "company" && intval($id) > 0) {
            $blockList->releaseCompany(intval($id));
        }

        $blockList->messageList();

    }

    public function next()
    {
        $model = new CompanySync(true);
        $model->showNext();
    }


}