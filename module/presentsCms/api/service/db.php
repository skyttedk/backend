<?php


/*
    i - Integer
    d - Double
    s - String
    b - Blob
*/



class db
{

    private $conn;

    public function __construct(){
        $this->connect();

        include_once realpath(realpath(dirname(__FILE__)) . '/../../../../../gavefabrikken_common/utils').'/bootstrap.php';
        setupGFCommon(GFConfig::DB_HOST, GFConfig::DB_USERNAME, GFConfig::DB_PASSWORD,GFConfig::DB_DATABASE);
        
    }

    public function get($query,$types=null,$parameter=null)
    {


        $types = $types;
        $arr = array();
        $stmt = $this->conn->prepare($query);

        if ( false===$stmt ) {
            throw new Exception( htmlspecialchars($this->conn->error) );
        }
        if($types != null){
         $references_to_data = array();
            foreach ($parameter as &$reference) { $references_to_data[] = &$reference; }
                unset($reference);
                call_user_func_array(
                    array($stmt, "bind_param"),
                    array_merge(array(str_repeat("s", countgf($parameter))), $references_to_data)
                );
        }
        $rc = $stmt->execute();
        if ( false===$rc ) {
             throw new Exception( htmlspecialchars($stmt->error) );
        } else {
        //fetching result would go here, but will be covered later
        $result = $stmt->get_result();
            while($row = $result->fetch_assoc()) {
                $arr[] = $row;
            }
            $stmt->close();
            if(!$arr){
                return [];
            } else {
                return $arr;
            }
        }
    }
    public function set($query,$types=null,$parameter=null)
    {
        $types = $types;
        $stmt = $this->conn->prepare($query);
        if ( false===$stmt ) {
            throw new Exception( htmlspecialchars($this->conn->error) );
        }
        if($types != ""){
         $references_to_data = array();
            foreach ($parameter as &$reference) { $references_to_data[] = &$reference; }
                unset($reference);
                call_user_func_array(
                    array($stmt, "bind_param"),
                    array_merge(array(str_repeat("s", countgf($parameter))), $references_to_data)
            );
        }

        $rc = $stmt->execute();
        if ( false===$rc ) {
             throw new Exception( htmlspecialchars($stmt->error) );
        } else {
            $affectedRows = $stmt->affected_rows;
            $lastId = $stmt->insert_id;
            $stmt->close();
            return array("affected_rows"=>$affectedRows,"last_id"=>$lastId);
        }

    }

    private function connect(){
        // Create connection
        $this->conn = new mysqli(GFConfig::DB_HOST, GFConfig::DB_USERNAME, GFConfig::DB_PASSWORD,GFConfig::DB_DATABASE);
        // Check connection
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

}
?>
