<?php

class queue
{
    public $db;
    function __construct() {
        $this->db = new Dbsqli();
    }
    public function createNewList($jobId,$grpId,$smsTitle,$smsBody)
    {
        set_time_limit ( 120 ) ;

        $hasNameTag = false;
        $dubletControl = [];
        $userObj = new user;
        $userlist = $userObj->getGrpUserList($grpId);
        $this->db->setKeepOpen();
        if(strpos($smsBody,"#navn#") != -1){
            $hasNameTag = true;
        }
        print_R ($userlist["data"]) ;
        foreach($userlist["data"] as $user)
        {

            $smsBodyDb = $smsBody;
            if($hasNameTag == true){
                $smsBodyDb = str_replace("#navn#",$user["fornavn"],$smsBody);
            }
               $tlf = trimgf($user["tlf"]);
               //$tlf = "53746555";
               if(strlen($tlf) == 8 ){
                   echo $query = "insert into sms_queue(`fk_sms_job_id`,`fk_sms_user_id`,`send_tlf`,`send_from_title`,`send_body`) values(".$jobId.",55,'".$tlf."','".$smsTitle."','".$smsBodyDb."')";
                    $this->db->set($query);
               }
            /*
            if(!in_array($user["tlf"],$dubletControl)){
               $dubletControl[] = $user["tlf"];
               $query = "insert into sms_queue(`fk_sms_job_id`,`fk_sms_user_id`,`send_tlf`,`send_from_title`,`send_body`) values(".$jobId.",".$user["id"].",'".$user["tlf"]."','".$smsTitle."','".$smsBodyDb."')";
               $this->db->set($query);
            }
            */

        }
        $this->db->setCloseDb();
        return true;
    }
    public function sendHandler()
    {
        set_time_limit ( 200 ) ;
        $this->db->setKeepOpen();

        $sql = "select * from sms_queue where active = 1 and is_send = 0 and fk_sms_job_id = 9 limit 0,200";
        $rs = $this->db->get($sql);
        $i=0;
        foreach($rs["data"] as $sendItem){
            $i++;
            echo $sendItem["send_tlf"];
            echo "<br />";
            echo $sendItem["send_body"];
            echo "<hr />";
            $gateway = new gateway();
            $gateway->send($sendItem["send_body"],$sendItem["send_tlf"],$sendItem["send_from_title"]);
            $query = "update sms_queue set is_send = '1' where id=".$sendItem["id"];
            $this->db->set($query);

        }
        echo "END: ".$i;


    }
    public function rette(){
        set_time_limit ( 200 ) ;

        $this->db->setKeepOpen();

        $sql = "select * from sms_queue where active = 1 and is_send = 1 and fk_sms_job_id = 10 limit 0,1";
        $rs = $this->db->get($sql);

        $i=0;
        foreach($rs["data"] as $sendItem){
            $i++;
            $str = $sendItem["send_body"];
            $str = str_replace("�", utf8_encode("�"),$str);
            echo  ($str);

        }
    }
}




?>