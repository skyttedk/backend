<?php
// Model SystemUser
// Date created  Mon, 16 Jan 2017 15:30:02 +0100
// Created by Bitworks
//***************************************************************
//   (PRI) id                            int(11)             NO
//   (   ) name                          varchar(45)         YES
//   (UNI) username                      varchar(45)         NO
//   (   ) password                      varchar(100)        YES
//   (   ) userlevel                     int(11)             YES
//   (   ) hash                          varchar(100)        NO
//   (   ) active                        tinyint(1)          YES
//   (   ) deleted                       tinyint(1)          YES
//   (MUL) token                         varchar(45)         YES
//   (   ) token_created                 datetime            YES
//   (   ) is_service_user               tinyint(4)          YES
//   (   ) last_login                    datetime            YES
//***************************************************************
class SystemUser extends BaseModel {
  static $table_name = "system_user";
  static $primary_key = "id";

     static $has_many = array(
                     array('permissions', 'class_name' => 'UserPermission'),
                    );


  static $before_create = array('onBeforeCreate');
  static $before_update = array('onBeforeUpdate');

  // Trigger functions
  function onBeforeCreate() {
    testRequired($this, 'password');
    $this->validateFields();
  }

  function onBeforeUpdate() {
    $this->validateFields();
  }

   function onAfterDestroy()  {
      UserPermission::table()->delete(array('system_user_id' => id));
    }


  function validateFields() {
    testRequired($this, 'username');
    //Calculate password HASH
    if (!$this->password == "")
      $this->hash = $this->calculate_hash($this->password);
    testMaxLength($this, 'name', 45);
    testMaxLength($this, 'username', 45);
    $this->name = trimgf($this->name);
    $this->username = strtolower(trimgf($this->username));
    //$this->password = '';    // hvorfor s�tter du den lig med tom. N�r jeg gemmer fra system fanen bliver password ikke gemt.
  }

  private function calculate_hash($password) {
    $cost = 10;

   if(function_exists("random_bytes")) {
        $salt = strtr(base64_encode(random_bytes(16)), '+', '.');
    }
    else {
        $salt = md5(time()."_".rand(1000,9999));
    }

    $salt = sprintf("$2a$%02d$", $cost) . $salt;
    $hash = crypt($password, $salt);
    return ($hash);
  }

  static public function hash_equals($str1, $str2) {
    $res = $str1 ^ $str2;
    $ret = 0;
    for ($i = strlen($res) - 1; $i >= 0; $i--)
      $ret |= ord($res[$i]);
    return !$ret;
  }
  //---------------------------------------------------------------------------------------
  // Static CRUD Methods
  //---------------------------------------------------------------------------------------
  //

  static public function createSystemUser($data) {
    $systemuser = new SystemUser($data);
    $systemuser->save();
    return ($systemuser);
  }

  static public function readSystemUser($id) {
    return (SystemUser::find($id));
  }

  static public function readSystemUsers() {
    return (SystemUser::all());
  }

  static public function updateSystemUser($data) {
    $systemuser = SystemUser::find($data['id']);
    $systemuser->update_attributes($data);
    $systemuser->save();
    return ($systemuser);
  }

  static public function deleteSystemUser($id, $realDelete = true) {
    if ($realDelete) {
      $systemuser = SystemUser::find($id);
      $systemuser->delete();
    }
    else { //Soft delete
      $systemuser->deleted = 1;
      $systemuser->save();
    }
  }
  //---------------------------------------------------------------------------------------
  // Custom Methods
  //---------------------------------------------------------------------------------------

  static public function Login($username, $password) {
              
   $systemusers = SystemUser::find('all', array('conditions' => array('username=? and active = ? and deleted = ?', $username, 1, 0)));
    if (count($systemusers) == 0)  {
      throw new Exception('Ugyldig login');
   } else {
		                      
      $systemUser = $systemusers[0];
      if (SystemUser::hash_equals($systemUser->hash, crypt($password, $systemUser->hash))) {



        $systemUser->token = trimgf(NewGUID(), '{}');
        $systemUser->token_created = date('d-m-Y H:n:s');
        $systemUser->save();

        $accessToken = \GFCommon\Model\Tokens\SystemUserToken::createFromUserID($systemUser->id);


         $_SESSION["systemuser_login".GFConfig::SALES_SEASON] = true;
         $_SESSION["systemuser_token".GFConfig::SALES_SEASON] = $accessToken->getToken();





        //return ($systemUser->token);
        return ($systemUser);

      }
      else {
        throw new Exception('Ugyldig login');
      }
    }
  }

  static public function getByToken($token) {

    if (!$token) {
      throw new Exception('Ugyldig token');
    }
    else {
      $systemusers = SystemUser::find('all', array('conditions' => array('token=? and active = ? and deleted = ?', $token, 1, 0)));
	  if (count($systemusers) == 1)
        return ($systemusers[0]);
      else
        return(null);
    }
  }

}
?>