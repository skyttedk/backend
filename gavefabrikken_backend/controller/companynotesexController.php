<?php
// Controller CompanyNotesEx
// Date created  Wed, 11 Oct 2017 14:30:30 +0200
// Created by Bitworks
class CompanyNotesExController Extends baseController {
    public function Index() {
    }
    public function create() {
        $companynotesex = CompanyNotesEx::createCompanyNotesEx ($_POST);
        response::success(make_json("companynotesex", $companynotesex));
    }
    public function read() {
        $companynotesex = CompanyNotesEx::readCompanyNotesEx ($_POST['id']);
        response::success(make_json("companynotesex", $companynotesex));
    }
    public function update() {
        $companynotesex = CompanyNotesEx::updateCompanyNotesEx ($_POST);
        $companynotesex->modified_datetime = date('d-m-Y H:n:s');
        $companynotesex->save();
        response::success(make_json("companynotesex", $companynotesex));
    }
    public function delete() {
        $companynotesex = CompanyNotesEx::deleteCompanyNotesEx ($_POST['id']);
        response::success(make_json("companynotesex", $companynotesex));
    }
    //Create Variations of readAll
    public function getNotes() {
        $companynotesexes = CompanyNotesEx::all();
        $companynotesexes = CompanyNotesEx::find('all',array('conditions' =>array('company_id' => $_REQUEST['company_id'])) );
        response::success(make_json("companynotesexes", $companynotesexes));
    }
//---------------------------------------------------------------------------------------
// Custom Controller Actions
//---------------------------------------------------------------------------------------
}
?>

