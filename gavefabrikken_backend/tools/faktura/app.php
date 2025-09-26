<?php
$fakcheck = new fakcheck();
//$fakcheck->getDirFileNames("lister");
//$path = $fakcheck->loadDataFromFileController();
$fakcheck->getDbdata();
$fakcheck->loadDataFromNav();
$fakcheck->theCheck($fakcheck->nav, $fakcheck->dbData);
class fakcheck
{
    public $filenameList = array();
    public $sendDataFiler = array();
    public $dbData = array();
    public $missing = array();
    public $nav = array();

    public function getDirFileNames($path)
    {
        $cdir = scandir($path);
        foreach ($cdir as $key => $value){
            if (!in_array($value,array(".","..")))
            {
                 $this->filenameList[] = $value;
            }
        }
    }
    public function loadDataFromFileController()
    {
        foreach($this->filenameList as $filepath){
            $this->loadDataFromFiles("lister/".$filepath);
        }
    }

    public function loadDataFromFiles($path)
    {
        $lines = file($path);
        foreach ($lines as $line_num => $line) {
            $parts = explode(";",$line);
            //if(strpos($parts[0],"BS") > -1){
                $this->sendDataFiler[] = $parts[0];
           // }
        }
       // print_r($this->sendDataFiler);
       // die("end");


    }
    public function getDbdata()
    {
       $conn = new mysqli(GFConfig::DB_HOST, GFConfig::DB_USERNAME, GFConfig::DB_PASSWORD,GFConfig::DB_DATABASE);
       if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $sql = "select order_no from company_order where is_cancelled = '0' and shop_id IN ('52') and is_invoiced = '1' ";

        //$sql = "select order_no from company_order where is_cancelled = '0' and shop_id IN ('57','58','59') and is_invoiced = '1' ";
        $row =array();
        $result = $conn->query($sql);
        while ($rows = $result->fetch_assoc()) {
            $row[] = $rows;
        }
        $conn->close();

        foreach($row as $data){
            $this->dbData[] = $data["order_no"];

        }
        //print_r($this->dbData);
    }
    public function theCheck($NAV,$inDb)
    {
        foreach($inDb as $inDbData){
            if (!in_array($inDbData, $this->nav)) {
                $this->missing[] =  $inDbData;
            }

        }
        echo "Antal fra NAV: ".countgf($this->nav)."<br />";
        echo "Antal fra Gavesys:".countgf($inDb)."<br />";
        echo "Antal der mangler:".countgf($this->missing)."<br /><br /><br /><br /><br />";
        $newarr = asort($this->missing);
        foreach ($this->missing as $ele){
            echo $ele."<br />";
        }
    }
    public function loadDataFromNav()
    {
        $lines = file("indlast.txt");
        foreach ($lines as $line_num => $line) {
            $this->nav[] = trimgf($line);
        }

    }
}

?>