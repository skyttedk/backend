<?php
// Model SystemSession
// Date created  Mon, 16 Jan 2017 15:29:59 +0100
// Created by Bitworks

class SystemSession extends BaseModel {
  static $table_name = "system_session";
  static $primary_key = "id";
  static $before_create = array('onBeforeCreate');
  static $after_create =  array('onAfterCreate');

// Trigger functions
  function onBeforeCreate() {
    $this->created_datetime = date('d-m-Y H:i:s');
    $this->validateFields();
  }

    function onAfterCreate()  {
    
    }

  function validateFields() 
  {

  }

//---------------------------------------------------------------------------------------
// Static CRUD Methods
//---------------------------------------------------------------------------------------
	
  
  public static function updateSession()
  {
  
      // Disabled the system session for performance reasons
      // User / login info should be stored in system_log instead
      return;
  
      // Session id
      $sessionid = session_id();
      $ip = self::get_client_ip();
      $browser = isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'],0,250) : "webservice"; 
      $userid = isset($_SESSION) && isset($_SESSION["syslogin"]) ? intval($_SESSION["syslogin"]) : 0;
       
      // Find users session
      $sessions = SystemSession::all(array('session' => $sessionid));
      
      if(count($sessions) == 0)
      {
        $ses = new SystemSession();
        $ses->session = $sessionid;
        $ses->ip = $ip;
        $ses->user_id = $userid;
        $ses->browser = $browser;
        $ses->created_datetime = date('d-m-Y H:i:s');
        $ses->save();
      }
      else 
      {
        if($sessions[0]->user_id == 0 && $userid > 0)
        {
          $sessions[0]->user_id = $userid;
          $sessions[0]->save();
        }
      }
      
                     
  }


    // Function to get the client IP address
 public static function get_client_ip() {
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