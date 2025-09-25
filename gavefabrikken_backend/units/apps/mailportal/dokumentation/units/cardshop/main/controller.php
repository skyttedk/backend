<?php

namespace GFUnit\cardshop\main;
use GFBiz\units\UnitController;

class Controller extends UnitController
{

    public function __construct()
    {
        parent::__construct(__FILE__);
    }
    public function getLanguage(){
        $systemuser = \SystemUser::readSystemUser($_POST['id']);
        \response::success(make_json("systemuser", $systemuser));
    }

    public function regUserActivity(){
      \Dbsqli::setSql2("INSERT INTO system_user_activity (user_id) VALUES (".$_POST["user"].")");
      \response::success(make_json("systemuser", []));
    }
    public function getUserActivity(){
      $result =   \Dbsqli::getSql2("SELECT user_id FROM `system_user_activity` WHERE `created` > DATE_SUB(now(), INTERVAL 10 minute) GROUP by user_id");
      echo countgf( $result );
    }

    /**
     * SERVICES
     */



}