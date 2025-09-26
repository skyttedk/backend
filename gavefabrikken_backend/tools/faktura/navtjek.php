<?php
$nav = [];
$dbData = [];

     $lines = file("jgk.txt");
        foreach ($lines as $line_num => $line) {
            $nav[] = trimgf($line);
        }
//print_r($nav);
//echo sizeofgf($nav);

       $conn = new mysqli(GFConfig::DB_HOST, GFConfig::DB_USERNAME, GFConfig::DB_PASSWORD,GFConfig::DB_DATABASE);
       if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        //(danske) $sql = "select order_no from company_order where is_cancelled = '0' and shop_id IN ('52','53','54','55','56') and is_invoiced = '1' ";

        $sql = "select order_no from company_order where is_cancelled = '0' and shop_id IN ('52')  ";

        $row =array();
        $result = $conn->query($sql);
        while ($rows = $result->fetch_assoc()) {
            $row[] = $rows;
        }


        foreach($row as $data){
            $dbData[] = $data["order_no"];

        }
       // print_r($dbData);
       //echo sizeofgf($dbData);


       // tjek alle i system er i listen
       $count = 0;
       $outputData = "";
       $missing = [];
       foreach($dbData as $orderFraSystem){
            if (!in_array($orderFraSystem, $nav)) {
                $count++;
                $outputData.= $orderFraSystem."<br>";
                $missing[] = $orderFraSystem;
            }
       }
       echo "jgk <br />";
       echo "Total fra system:".sizeofgf($dbData) ."<br />";
       echo "Total der ikke er i nav listen: ".$count."<br />";
       echo $outputData;
      // ---------------------------------
        $sql = "select * from company_order where order_no in( '".implode("','",$missing )."')  ";

        $row =array();
        $result = $conn->query($sql);
        while ($rows = $result->fetch_assoc()) {
            $row[] = $rows;
        }
        print_r($row);

        $conn->close();




?>