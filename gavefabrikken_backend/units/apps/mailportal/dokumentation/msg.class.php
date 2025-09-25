<?php

class msg {

  public static function send($status, $data) {
    if ($status == 1) {
      return '{"status":"1","data":' . $data . ',"error":""}';
    }
    else
      if ($status == 0) {
        return '{"status":"0","data":"","error":' . $data . '}';
      }
      else {
        return '{"status":"0","data":"error","error":"error"}';
    }
  }

  public static function ok() {
    return '{"status":"1","data":"okay","error":""}';
  }

  public static function error() {
    return '{"status":"0","data":"","error":"Der er opstået en fejl!"}';
  }

}
?>