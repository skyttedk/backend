<?php
class user
{
    public $db;
    function __construct() {
        $this->db = new Dbsqli();
    }
    public function getGrpUserList($grpId){
        $query = "select * from sms_user where active = 1 and grp_id = 9 and tlf != '' GROUP by tlf";
   //   echo   $query = "select * from sms_user where active = 1 and grp_id =".$grpId;
        return $this->db->get($query);
    }
}