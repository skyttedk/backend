<?php
/*
receiptCardShop.class.php
Varenummer til kundernes kvittering
Varenavn til kundernes kvittering
*/

class receiptCardShop
{

    static public function getItemNumber($expireDate,$shopItemNo,$certificateValue)
    {
        $itemNumber = "";
        $cardValue = "";
        $week = self::getWeekNumber($expireDate);
        if($certificateValue != ""){
            $cardValue = "-".$certificateValue;
        }
        return $shopItemNo.$week."-".date("Y").$cardValue;

    }
    static public function getProductName($expireDate,$shopItemName,$shopItemNo,$certificateValue)
    {
        $productName = "";
        $cardValue = "";
        $week = self::getWeekNumber($expireDate);
        if($certificateValue != ""){
            $cardValue = " - ".$certificateValue;
        }
        if($shopItemNo == "JGK-NO"){
            return $shopItemName." - uke ".$week."$cardValue";
        } else if($shopItemNo == "24julklappar") {
            return $shopItemName." - vecka ".$week."$cardValue";
        } else {
            return $shopItemName." - uge ".$week."$cardValue";
        }



    }
    static private function getWeekNumber($expireDate)
    {
        if($expireDate == "01-11-2020")
            return "48";
        elseif($expireDate == "08-11-2020")
            return "49";
        elseif($expireDate == "07-11-2020")
            return "49";
        elseif($expireDate == "15-11-2020")
            return "50";
        elseif($expireDate == "22-11-2020")
            return "51";
        elseif($expireDate == "29-11-2020")
            return "51";
        elseif($expireDate == "31-12-2020")
            return "4";
        elseif($expireDate == "01-03-2021")
            return "4";
        elseif($expireDate == "01-04-2021")
            return "1";
        elseif($expireDate == "31-12-2021")
            return "1";
    }

}

?>