<?php

class Profile
{

  /**
   *
   * PROFILES CODE AND SAVES TIMESTAMPS IN LOGFILE
   * USAGE: 
   * 
      Profile::start("profilecode","Description");  // Startsprofiling 
      Profile::time("profilecode,"Description");    // Saves time code with description on the profile code
       Profile::finish("profilecode");              // Finish profiling and save details to log
   *
   * LOGFILE CAN BE FOUND AT: http://94.143.10.74/gavefabrikken_backend/profile-data.log 
   */
   
  private static $PROFILE_ENABLED = true;
  private static $DISABLE_CODES = array();

/**
  * HELPERS
  */
  
  private static $profilers = array();
  
  public static function start($code,$name="",$data=null)
  {
    if(self::isEnabled($code) == false) return false;
    if(!isset(self::$profilers[$code]))
    {
      self::$profilers[$code] = new Profile($code,$name,$data);
      return true;
    }
    return false;
  }

  public static function time($code,$description)
  {
    if(self::isEnabled($code) == false) return false;
    if(!self::isRunning($code)) return false;
    self::$profilers[$code]->timePoint($description);
    return true;
  }

  public static function finish($code,$description="")
  {
    if(self::isEnabled($code) == false) return false;
    if(!self::isRunning($code)) return false;
    self::$profilers[$code]->finishProfile($description);
    unset(self::$profilers[$code]);
    return true;
  }
  
  public static function isEnabled($code)
  {              
    if(self::$PROFILE_ENABLED == false || in_array($code,self::$DISABLE_CODES)) return false;
    else return true;
  }
  
  public static function isRunning($code)
  {
    return isset(self::$profilers[$code]);
  }

  /** PROFILE CLASS **/
  
  private $code;
  private $name;
  private $start_time;    
  private $data;
  
  private $points;
    
  public function __construct($code,$name,$data)
  {
    $this->code = $code;
    $this->name = $name;
    $this->data = $data;
    $this->start_time = microtime(true);
    $this->points = array();
  }
  
  public function timePoint($description)
  {
    $this->points[] = array("description" => $description, "time" => microtime(true));
  }

  public function finishProfile($description)
  {
    $endTime = microtime(true);
   /* 
    $logItem = new AppLog();
    $logItem->app_username = "profiler";
    $logItem->created_date = date('d-m-Y H:i:s');
    $logItem->company_id = 0;
    $logItem->shop_id = 0;
    $logItem->shopuser_id = 0;
    $logItem->order_id = 0;
    $logItem->extradata = "";
    $logItem->log_event = "Profiling: ".$this->code;
    $logItem->log_description = $this->generateProfileText($endTime,$description);
    $logItem->save();
    */
    
    //file_put_contents("profile-data.log",$this->generateProfileText($endTime,$description),FILE_APPEND );
    
  }

  private function generateProfileText($endTime,$description)
  {
  
    $pt = "=== PROFILING [".$this->code."] ===\r\n".$this->name."\r\n\r\n";
    $pt .= "=== OVERALL TIMING ===\r\nSTART: ".date("d-m-Y H:i:s",$this->start_time)." ".intval(($this->start_time-intval($this->start_time))*1000000)."\r\nEND:  ".date("d-m-Y H:i:s",$endTime)." ".intval(($endTime-intval($endTime))*1000000)."\r\n";
    $pt .= "DURATION: ".($endTime-$this->start_time)." s (".countgf($this->points)." points)\r\n\r\n";
 
    if($this->data != null && countgf($this->data) > 0)
    {
      $pt .= "=== DATA ===\r\n";
      $pt .= print_r($this->data,true)."\r\n\r\n";
    }
    
    if(count($this->points) > 0)
    {
        $lastPoint = $this->start_time;
        $pt .= "=== POINTS (index / since start (ms) / since last (ms) ===\r\n";
        foreach($this->points as $index => $point)
        {
          $pt .= " ".$index."\t".(intval(($point["time"]-$this->start_time)*1000))."\t".(intval(($point["time"]-$lastPoint)*1000))."\t".$point["description"]."\r\n";
          $lastPoint = $point["time"];
        }
        $pt .= "\r\n";
    }
    
    if($description != "")
    {
        $pt .= "=== RESULT ===\r\n";
        $pt .= trimgf($description)."\r\n\r\n";
    }
  
    return $pt;
  }

}
