<?php
// Controller SystemUser
// Date created  Tue, 12 Apr 2016 21:01:51 +0200
// Created by Bitworks
class SystemUserController Extends baseController
{
    public function Index()
    {
          if(\GFCommon\Model\Access\BackendPermissions::isAdmin()) {
        $this->registry->template->systemUsers = SystemUser::all();
        $this->registry->template->show('system_user');
        }
        else {
            $this->registry->template->show('system_nav');
        }
    }

    public function create()
    {
        $systemuser = new SystemUser();
        $data['name'] = $_POST['name'];
        $data['username'] = $_POST['username'];
        $data['password'] = $_POST['password'];
        $data['userlevel'] = $_POST['userlevel'];
        $data['active'] = $_POST['active'];
        $data['salespersoncode'] = $_POST['salespersoncode'];
        $systemuser = SystemUser::createSystemUser($data);
        response::success(make_json("systemuser", $systemuser));
        //$this->Index();
    }

    public function read()
    {
        $systemuser = SystemUser::readSystemUser($_POST['id']);
        response::success(make_json("systemuser", $systemuser));
    }

    public function update()
    {

        $systemuser = new SystemUser();
        $data['id'] = $_POST['id'];

        $data['name'] = $_POST['name'];
        $data['username'] = $_POST['username'];
        $data['password'] = $_POST['password'];
        $data['userlevel'] = $_POST['userlevel'];
        $data['active'] = $_POST['active'];
        $data['salespersoncode'] = $_POST['salespersoncode'];

        $systemuser = SystemUser::updateSystemUser($data);
        response::success(make_json("systemuser", $systemuser));
    }

    public function delete()
    {
        $data['id'] = $_POST['id'];
        $systemuser = SystemUser::deleteSystemUser($data);
        response::success(make_json("systemuser", $systemuser));
    }

    //Create Variations of readAll
    public function readAll()
    {
        $systemusers = SystemUser::all();
        //$options = array('only' => array('id', 'name', 'username', 'admin', 'active'));
        $options = array();
        response::success(make_json("systemusers", $systemusers, $options));
    }
//---------------------------------------------------------------------------------------
// Custom Controller Actions
//---------------------------------------------------------------------------------------
}

?>

