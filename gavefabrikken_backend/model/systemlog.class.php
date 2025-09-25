<?php
// Model SystemLog
// Date created  Mon, 16 Jan 2017 15:29:59 +0100
// Created by Bitworks
//***************************************************************
//   (PRI) id                            int(11)             NO
//   (   ) user_id                       varchar(100)        YES
//   (MUL) controller                    varchar(100)        YES
//   (   ) action                        varchar(100)        YES
//   (   ) data                          text                YES
//   (MUL) created_datetime              datetime            YES
//   (MUL) committed                     tinyint(4)          YES
//   (   ) error_message                 text                YES
//   (   ) error_trace                   text                YES
//***************************************************************
class SystemLog extends BaseModel {
  static $table_name = "system_log";
  static $primary_key = "id";
  static $before_create = array('onBeforeCreate');
  static $after_create =  array('onAfterCreate');

// Trigger functions
  function onBeforeCreate() {
    $this->created_datetime = date('d-m-Y H:i:s');
    $this->validateFields();
  }

    function onAfterCreate()  {
      //Inds�t revord i system surveilance
      if($this->controller=="login" &&  $this->action=="verify") {
          $userinfo =json_decode($this->data);
          $systemsurveillance = new SystemSurveillance();
          $systemsurveillance->ip = $this->get_client_ip();
          $systemsurveillance->system_log_id = $this->id;
          $systemsurveillance->created_datetime = $this->created_datetime;
          $systemsurveillance->username = $userinfo->username;
          $systemsurveillance->password = $userinfo->password;
          $systemsurveillance->user_agent = $_SERVER['HTTP_USER_AGENT'];
          $systemsurveillance->referrer = $_SERVER['HTTP_REFERER'];
          $systemsurveillance->save();
      }
    }

  function validateFields() 
  {

    testMaxLength($this, 'user_id', 100);
    testMaxLength($this, 'controller', 100);
    testMaxLength($this, 'action', 100);

    $this->user_id = trimgf($this->user_id);
    $this->controller = trimgf($this->controller);
    $this->action = trimgf($this->action);
    $this->data = trimgf($this->data);
  }

//---------------------------------------------------------------------------------------
// Static CRUD Methods
//---------------------------------------------------------------------------------------
	static public function readSystemLog($id) {

		$systemlog = SystemLog::find($id);
        return($systemlog);

	}


    // Function to get the client IP address
function get_client_ip() {
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if(isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}


}
?>