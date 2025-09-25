<?php
ini_set('memory_limit', '128M');
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
include("db.php");

class emailHandle
{
    private $db;
    private $path;
    private $count=0;
    function __construct() {
        $this->db = new Dbsqli();
        $this->db->setKeepOpen();

    }
    public function setEmailListPath($path){
       $this->path = $path;
    }
    public function getUnsubTlf(){


        if(empty($this->path) ) {
            echo "hej";
            throw new Exception('Path missing');
            return;
        }
        $handle = fopen($this->path, "r");
        if ($handle) {
            while (($email = fgets($handle)) !== false) {



                try {
                    $email = trim(preg_replace('/\s\s+/', ' ', $email));
                }
                catch(Exception $e) {
                    echo "error: ".$email;
                }

                $oa= $this->getShopuserID($email);
                $this->count++;
                if(sizeof($oa["data"]) > 0){
                    foreach ($oa["data"] as $ele){
                        $res = $this->getTlfnr($ele["shopuser_id"]);
                        if(sizeof($res["data"]) > 0){
                            $this->saveUnsub($ele["shopuser_id"],$res["data"][0]["attribute_value"],$email);
                        }
                    }
                }
            }

            fclose($handle);
        }
        return $this->count;

    }
    private function getShopuserID($email){
        $sql = "SELECT * FROM gavefabrikken2024.`order_attribute` WHERE `attribute_value` LIKE '".$email."'";
        return $this->db->get($sql);
    }
    private function getTlfnr($shopuserID){
         $sql = "SELECT * FROM gavefabrikken2024.`order_attribute` WHERE `shopuser_id` = ".$shopuserID." AND `attribute_name` LIKE '%Mobilnummer%'";
        return $this->db->get($sql);
    }
    private function saveUnsub($shopuserID,$tlf,$email){
        try {
          echo   $sql = "INSERT INTO gavefabrikken2024.`sms_unsubscribe_mail` ( `shop_user`, `mail`, `tlf`) VALUES ( $shopuserID, '$email','$tlf')";
            $this->db->set($sql);
        }
        catch(Exception $e) {
            echo "dberror: ".$email;
            echo "<br>";
        }
    }



}













?>