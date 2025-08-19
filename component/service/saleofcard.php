<?php

include_once "../../includes/config.php";

if ($_GET["token"] != "sdfj894y893fy87!fsdlkfhj489iu3fhosklhf") {
    die("No access");
}

//[BACKENDURL]]/component/service/saleofcard.php?token=sdfj894y893fy87!fsdlkfhj489iu3fhosklhf
set_time_limit(3000);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include "db.php";

$rapport = new SaleOfCard();
$rapport->loadCardOnBSNumber();
$rapport->array_to_csv_download();
class SaleOfCard {
    private $dbConn;
    private $exportData = [];

    public function __construct() {
        $this->dbConn = new Dbsqli();
        $this->dbConn->setKeepOpen();
    }

    public function loadCardOnBSNumber() {
        $this->exportData = [["Kort type", "BS-nummer", "Antal kort p� k�bsordre", "Antal aktive kort", "Diff mellem k�b og aktive kort","Total k�b"]];
        $sql = "SELECT company_order_id, count(*) as antal FROM `shop_user` WHERE `is_demo` = 0 AND `is_giftcertificate` = 1 AND `blocked` = 0 group by company_order_id order by shop_id,id";
        $cardRs = $this->dbConn->get($sql);

        foreach ($cardRs["data"] as $card) {
            $sql = "SELECT company_order.*,company_order.shop_id as company_shop_id,shop.name as shopname FROM `company_order` inner join shop on company_order.shop_id = shop.id where company_order.id =  '" . $card["company_order_id"] . "' and company_order.id not in  (32573,24426,32574,23030,24427,23959,23961,26340,26630,26735,26906,32270,26738,26749) and is_cancelled = 0";
            $companyRs = $this->dbConn->get($sql);

            if (sizeofgf($companyRs["data"]) > 0) {
                $data = $companyRs["data"];
                try {
                    $diff = ($data[0]["quantity"] == $card["antal"]) ? "0" : ($data[0]["quantity"] * 1 - $card["antal"] * 1);
                    $pris =  ($this->getCardPrice($data[0]["company_shop_id"]) *1) * ($card["antal"] * 1);
                    array_push($this->exportData, [utf8_decode($data[0]["shopname"]), $data[0]["order_no"], $data[0]["quantity"], $card["antal"], $diff,$pris]);
                } catch (Exception $e) {
                    echo $data[0]["order_no"] . "<br>";
                }
            }
        }
    }

    public function getCardPrice($cardID) {

        switch ($cardID) {
            case "52":
                return 560*1.25;
                break;
            case "53":
                return 800*1.25;
                break;
            case "54":
                return 400*1.25;
                break;
            case "55":
                return 560*1.25;
                break;
            case "56":
                return 640*1.25;
                break;
            case "57":
                return 400;
                break;
            case "58":
                return 600;
                break;
            case "59":
                return 800;
                break;
            case "247":
                return 600*1.25;
                break;
            case "248":
                return 800*1.25;
                break;
            case "287":
                return 100*1.25;
                break;
             case "290":
                return 200*1.25;
                break;
            case "310":
                return 300*1.25;
                break;
            case "575":
                return 640*1.25;
                break;
            case "1832":
                return 400;
                break;
            case "4793":
                return 300;
                break;
            case "5117":
                return 600;
                break;
            case "1981":
                return 800;
                break;
            case "272":
                return 300;
                break;

            default:
                return -1;
        }

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
        // tell the browser it's going to be a csv file
        header('Content-Type: application/csv');
        // tell the browser we want to save it instead of displaying it
        header('Content-Disposition: attachment; filename="' . $filename . '";');
        // make php send the generated csv lines to the browser
        fpassthru($f);
    }

}

?>