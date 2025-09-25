<?php

set_time_limit(4000);
ini_set('memory_limit', '128M');
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
//error_reporting(E_ALL);
include "sms/db/db.php";
 // 52,53,54,55,56,575,290,310
$rapport = new AntalSolgteVarer();
$rapport->makeRapport(52);
$rapport->array_to_csv_download("export_52.csv");
class AntalSolgteVarer {
    private $dbConn;
    private $exportData = [];
    public function __construct() {
        $this->dbConn = new Dbsqli();
        $this->dbConn->setKeepOpen();

    }
    //Varenr;Antal valgte;PROBLEM / SAM nr;Antal der skal ganges med fra sam
      // [BACKENDURL]/component/rapport_varenr_antal_solgte.php
    public function makeRapport($shopID) {
        array_push($this->exportData, ["Deadline","Gavekort","Varenr","Antal valgte","Gavenavn","Model navn","SAM nr.","",""]);
        $sql = "SELECT shop.name as shop_name, `order`.shop_id as order_shop_id, shop_user.`expire_date` as shop_expire_date, present_model.`model_present_no`, present_model.model_name as present_model_name, present_model.model_no, COUNT(`order`.shopuser_id) as antal FROM `present_model`
                INNER JOIN `order` on present_model.model_id = `order`.present_model_id
                INNER JOIN shop_user on  `order`.shopuser_id = shop_user.id
                INNER JOIN shop on `order`.shop_id = shop.id
                WHERE  shop_user.`expire_date` in ( '2020-11-29') AND shop_user.`is_demo` = 0 and shop_user.blocked = 0 and `shop_user`.shop_id in (".$shopID.")
                and present_model.`language_id` = 1 GROUP by shop_user.`expire_date`,`order`.shop_id, present_model.model_present_no  order by shop_user.`expire_date`,`order`.shop_id";

        $saleListRs = $this->dbConn->get($sql);

        foreach ($saleListRs["data"] as $sale) {
            $pos = strpos(strtoupper($sale["model_present_no"]), "SAM");

            // handel SAM
            if ($pos !== false) {
                $sql = "select * from sam where styklistenr = '" . trimgf(strtoupper($sale["model_present_no"])) . "'";
                $samRS = $this->dbConn->get($sql);
                if (sizeofgf($samRS["data"]) > 0) {
                    foreach ($samRS["data"] as $sam) {
                        $antalSam = str_replace(",", "", $sam["antal"]);
                        $antal = ($sale["antal"] * 1) * ($antalSam * 1);

                //       array_push($this->exportData, [$sale["shop_expire_date"],$sale["shop_name"],$sam["nummer"],$sam["beskrivelse"],"", $antal, $sale["model_present_no"], $antalSam,$sale["antal"]]);
                         array_push($this->exportData, [$sale["shop_expire_date"],utf8_decode($sale["shop_name"]),$sam["nummer"], $antal,$sam["beskrivelse"],"", $sale["model_present_no"], "",""]);
                    }
                } else {
                    // kunne ikke finde SAM

                      array_push($this->exportData, [$sale["shop_expire_date"],utf8_decode($sale["shop_name"]),$sale["model_present_no"], $sale["antal"],utf8_decode($sale["present_model_name"]),utf8_decode($sale["model_no"]), "PROBLEM", ""]);
                }
            } else { // handel normal
                array_push($this->exportData, [$sale["shop_expire_date"],utf8_decode($sale["shop_name"]),$sale["model_present_no"], $sale["antal"],utf8_decode($sale["present_model_name"]),utf8_decode($sale["model_no"]), "", ""]);

            }

        }
      /*
        foreach ($rapportData as $data) {
            echo implode(";", $data) . "<br>";
        }
        */
    }
    public function updateSamTable() {
        $file = fopen("Styklistevarer.csv", "r");

        while (!feof($file)) {
            $line = explode(";", fgets($file));
        echo    $sql = "insert into sam (styklistenr,nummer,beskrivelse,antal) values('".$line[0]."','".$line[1]."','".$line[2]."',".$line[3].")";
            $this->dbConn->set($sql);
        }

        fclose($file);

    }
        public function array_to_csv_download($filename = "export.csv", $delimiter = ";") {
        // open raw memory as file so no temp files needed, you might run out of memory though
        $array = $this->exportData;
        $f = fopen('php://memory', 'w');
        // loop over the input array
        foreach ($array as $line) {
            // generate csv lines from the inner arrays
            fputcsv($f, $line, $delimiter);
        }
        // reset the file pointer to the start of the file
        fseek($f, 0);
        header('Content-Type: text/csv; charset=iso-8859-1');
        // tell the browser it's going to be a csv file
        header('Content-Type: application/csv');
        // tell the browser we want to save it instead of displaying it
        header('Content-Disposition: attachment; filename="' . $filename . '";');
        // make php send the generated csv lines to the browser
        fpassthru($f);
    }
}

?>