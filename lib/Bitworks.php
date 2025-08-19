<?php
//Errors skal h�ndteres som exceptions
set_error_handler(
function ($errno, $errstr, $errfile, $errline) {
  if (error_reporting()) {
    throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
  }
}
);
class loginException extends Exception {
  public function errorMessage() {
    $errorMsg = "Login Required";
    return $errorMsg;
  }
}

function lowercase($value) {
   $value = str_replace("Ø","ø",$value); //�
   $value = str_replace("Æ","æ",$value); //
   $value = str_replace("Å","å",$value); //
   return(strtolower($value));
}

// Utility functions
function is_production() {
  $system = system::first();
  return $system->is_production;
}
function time_elapsed() {
  static $last = null;
  $now = microtime(true);
  if ($last != null) {
    echo (($now - $last) . '<br>');
  }
  $last = $now;
}
function dump($obj) {
  echo '<pre>';
  var_dump($obj);
  echo '</pre>';
}
function NewGUID() {
  $guidstr = "";
  for ($i = 1; $i <= 16; $i++) {
    $b = (int) rand(0, 0xff);
    if ($i == 7) {
      $b &= 0x0f;
      $b |= 0x40;
    } // version 4 (random)
    if ($i == 9) {
      $b &= 0x3f;
      $b |= 0x80;
    } // variant
    $guidstr .= sprintf("%02s", base_convert($b, 10, 16));
    if ($i == 4 || $i == 6 || $i == 8 || $i == 10) {
      $guidstr .= '-';
    }
  }
  return $guidstr;
}                     
function make_json($name, $data, $options = array()) {
  //if(isset($_GET["debug"]) && $_GET["debug"] == "1") var_dump($data);
    //echo $name;
    //print_R($data);
    $dataType = gettype($data);

       if($dataType == "NULL" ){
           $data = array();
       }



  if (gettype($data) == "object") {
    return "{ \"$name\" :[" . $data->to_json($options) . "]}";
  }
  else
    if (gettype($data) == "string") {
      
        return "{ \"$name\" :\"" . $data . "\"}";
    }
    else {

        $json = "";
      if (count($data) > 0) {

        foreach ($data as $row) {
          $json .= $row->to_json($options) . ",";
        }
      }
      return "{ \"$name\" :[" . rtrim($json, ",") . "]}";
  }
}
function copyAttributes($destination, $source) {
  foreach ($destination->attributes as $destinationKey => $destinationValue) {
    if ($destinationKey <> "id") {
      if (isset($source->{ $destinationKey })) {
        $destination->{
          $destinationKey
        }
        = $source->{
          $destinationKey
        }
        ;
      }
    }
  }
}
function ExecuteSQL($TSQL) {
  $ar_adapter = ActiveRecord\ConnectionManager::get_connection();
  $connection = $ar_adapter->connection;
  $connection->query($TSQL);
}

function lockTable($tableName) {
  //ExecuteSQL("LOCK TABLES $tableName WRITE");
  //ExecuteSQL("SELECT COUNT(id) FROM $tableName FOR UPDATE"); // Causes transactionsafe lock        -- slow når man skal tælle så mange records
  ExecuteSQL("SELECT MIN(id) FROM $tableName FOR UPDATE"); // Causes transactionsafe lock
}

function unlockTable() {
//ExecuteSQL("UNLOCK TABLES");
}
// Validators
function testMaxLength($record, $field, $length, $message = null) {
  $exceptionMessage = 'Maximum string length of �' . $field . '� is ' . $length . '.Current length is ' . strlengf($record->{
    $field
  }
  ) . '!';
  if (strlengf($record->{ $field }) > $length) {
    if ($message)
      $exceptionMessage = $message;
    throw new Exception($exceptionMessage);
  }
}
function testRequired($record, $field, $message = null) {
// dump($record);
  $exceptionMessage = $field . ' is required in ' . get_class($record);
  if (empty($record->{ $field })) {
    if ($message)
      $exceptionMessage = $message;
    throw new Exception($exceptionMessage);
  }
}
function testNumeric($record, $field, $message = null) {
  $exceptionMessage = $field . ' is not a valid numeric!';
  if (empty($record->{ $field }) || !is_numeric($record->{ $field })) {
    if ($message)
      $exceptionMessage = $message;
    throw new Exception($exceptionMessage);
  }
}
function testBoolean($record, $field, $message = null) {
  $exceptionMessage = $field . ' is not a valid boolean';
  if (empty($record->{ $field }) || !is_bool($record->{ $field })) {
    if ($message)
      $exceptionMessage = $message;
    throw new Exception($exceptionMessage);
  }
}
function testInteger($record, $field, $message = null) {
  $exceptionMessage = $field . ' is not a valid integer';
  echo $record->{
    $field
  }
  ;
  if (empty($record->{ $field }) || !is_int($record->{ $field })) {
    if ($message)
      $exceptionMessage = $message;
    throw new Exception($exceptionMessage);
  }
}
function generateStrongPassword($length = 9, $add_dashes = false, $available_sets = 'luds') {
  $sets = array();
  if (strpos($available_sets, 'l') !== false)
    $sets[] = 'abcdefghjknpqrstuvxyz';
  if (strpos($available_sets, 'u') !== false)
    $sets[] = 'ABCDEFGHJKMNPQRSTUVWXYZ';
  if (strpos($available_sets, 'd') !== false)
    $sets[] = '23456789';
  if (strpos($available_sets, 's') !== false)
    $sets[] = '!@#$%&*?';
  $all = '';
  $password = '';
  foreach ($sets as $set) {
    $password .= $set[array_rand(str_split($set))];
    $all .= $set;
  }
  $all = str_split($all);
  for ($i = 0; $i < $length - countgf($sets); $i++)
    $password .= $all[array_rand($all)];
  $password = str_shuffle($password);
  if (!$add_dashes)
    return $password;
  $dash_len = floor(sqrt($length));
  $dash_str = '';
  while (strlengf($password) > $dash_len) {
    $dash_str .= substr($password, 0, $dash_len) . '-';
    $password = substr($password, $dash_len);
  }
  $dash_str .= $password;
  return $dash_str;
}
?>