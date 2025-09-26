<?php
ini_set('memory_limit', '900M');
class response {

  public static function success($data, $message = "") {
    if (!router::$systemLogSkip) {
      $systemlog = SystemLog::find(router::$systemLogId);
      $systemlog->committed = 1;

      if(router::$callStarted > 0) {
          $systemlog->runtime = microtime(true) - router::$callStarted;
      }
      
      $systemlog->save();
    }
    system::connection()->commit();
    echo (trimgf('{"status":"1","data":' . $data . ',"message":"' . utf8_encode($message) . '"}'));
  }

  public static function silentsuccess() {
      if (!router::$systemLogSkip) {
          $systemlog = SystemLog::find(router::$systemLogId);
          $systemlog->committed = 1;

          if(router::$callStarted > 0) {
              $systemlog->runtime = microtime(true) - router::$callStarted;
          }

          $systemlog->save();
      }
      system::connection()->commit();
  }

  public static function loginRequest() {
    $message = "Login Credentials Required";
    echo (trimgf('{"status":"2","message":"' . utf8_encode($message) . '"}'));
  }

  public static function error($message) {
    echo ('{"status":"0","data":{},"message":"' . utf8_encode($message) . '"}');
  }

  public static function returnSuccess($data, $message = "") {
    system::connection()->commit();
    return (trimgf('{"status":"1","data":' . $data . ',"message":"' . utf8_encode($message) . '"}'));
  }

}
?>