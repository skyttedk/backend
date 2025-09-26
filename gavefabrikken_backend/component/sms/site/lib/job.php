<?php

class job
{
    public $db;
    function __construct() {
        $this->db = new Dbsqli();
    }
    public function create($navn,$grpId,$title,$body)
    {
        $query = "insert into sms_jobs(`job_name`,`grp_send_to`,`title`,`body`) values('".$navn."',".$grpId.",'".$title."','".$body."')";
        $this->db->set($query);
        $jobId = 9;
        $queue = new queue;
        $queue->createNewList($jobId,9,$title,$body);
    }




}



?>


