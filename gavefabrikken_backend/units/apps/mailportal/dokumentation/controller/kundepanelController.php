<?php

Class kundepanelController Extends baseController {

    public function index() 
    {
    
        // Get define admin token and get inputs
        $gavefabrikToken = "NJycUpZGVhMJvQ7Kmb88uXRgX6VMhpvUcEBPj9NhmJ2tjxQB";
        $sessionToken = isset($_GET["token"]) ? trimgf($_GET["token"]) : "";
        $shopid = intval(isset($_GET["shopId"]) ? $_GET["shopId"] : 0);
       
        $validToken = false;
        
        // Check for admin token
        if($sessionToken == $gavefabrikToken)
        {
          $validToken = true;
        }

        // Check for shopuser token
        else if($sessionToken != "")
        {
          $shopUsers = ShopUser::find('all', array('conditions' => array('token=?', $sessionToken)));
          if(count($shopUsers) > 0)
          {
            if($shopUsers[0]->token == $sessionToken && $shopUsers[0]->is_demo == 1 && $shopUsers[0]->shop_id == $shopid)
            {
              $validToken = true;
            }
          }
        }

       // If token is valid, show kundepanel view
       if($validToken == true)
       {
          $this->registry->template->show('kundepanel_view');
       }
       
       // If token not valid, go to referer or gavefabrikken.dk
       else
       {
        $referer = isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : "http://www.gavefabrikken.dk/";
        header("Location: ".$referer);
       }

    }
    public function qrNewUser()
    {
       $navn = Dbsqli::protect($_POST["name"]);
       $username = Dbsqli::protect($_POST["username"]);
       $password = Dbsqli::protect($_POST["password"]);
       $shopID = intval($_POST["shopID"]);
       $token = $this->get_guid();
       $sql = "INSERT INTO `app_users` ( `name`, `username`, `password`, `shop_id`,token) VALUES ('".$navn."', '".$username."', '".$password."', ".$shopID.",'".$token."')";

       $rs = Dbsqli::setSql2($sql);
       echo $rs == true ? json_encode(array('status'=>1)) : json_encode(array('status'=>0));

    }
    public function qrUpdate()
    {
       $navn = $_POST["name"];
       $username = $_POST["username"];
       $password = $_POST["password"];
       $shopID = intval($_POST["shopID"]);
       $ID = intval($_POST["ID"]);

       $sql = "update `app_users` set `name` = '".$navn."', `username`= '".$username."', `password` = '".$password."' where `shop_id` = ".$shopID." and id = ".$ID;
       $rs = Dbsqli::setSql2($sql);

       echo $rs == true ? json_encode(array('status'=>1)) : json_encode(array('status'=>0));
    }
    public function qrDelete()
    {
       $ID = intval($_POST["ID"]);
       $shopID = intval($_POST["shopID"]);

       $sql = "update `app_users` set `password` = '', username = '".$this->radom(10)."',  active = 0 where `shop_id` = ".$shopID." and id = ".$ID;
       $rs = Dbsqli::setSql2($sql);
       echo $rs == true ? json_encode(array('status'=>1)) : json_encode(array('status'=>0));
    }
    public function qrReadUser()
    {
        $shopID = intval($_POST["shopID"]);
        $sql = "select * from `app_users` where `shop_id` = ".$shopID." and active = 1";
        $rs = Dbsqli::GetSql2($sql);
        echo json_encode($rs);

    }
    private function radom($n) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';

    for ($i = 0; $i < $n; $i++) {
        $index = rand(0, strlen($characters) - 1);
        $randomString .= $characters[$index];
    }

    return $randomString;
}
 function get_guid() {
    $data = PHP_MAJOR_VERSION < 7 ? openssl_random_pseudo_bytes(16) : random_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);    // Set version to 0100
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);    // Set bits 6-7 to 10
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

}

?>
