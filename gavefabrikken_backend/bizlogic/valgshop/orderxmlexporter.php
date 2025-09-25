<?php

namespace GFBiz\valgshop;

class OrderXMLExporter extends OrderExporter
{

    public function __construct()
    {

    }

    private function xmlString($val,$maxLength = null) {
        if($maxLength != null && $maxLength > 0) {
            $val = substr($val,0,$maxLength);
        }
        return htmlspecialchars($val);
    }

    private function getOrderLine($typeCode,$code,$quantity,$price,$description="*",$billToCustomerNo=null,$gift_code=null,$displayPrice=null)
    {

        if (strpos($code, 'TEXT') !== false && strlen($description) > 50) {
            $descArray = explode("\n", trim(chunk_split($description, 50)));
            $xml = "";
            foreach ($descArray as $index => $desc) {
                $xml .= $this->getOrderLine($typeCode, $code, ($index == 0 ? $quantity : null), ($index == 0 ? $price : null), $desc, $billToCustomerNo, ($index == 0 ? $gift_code : null));
            }
            return $xml;
        }

        if($typeCode === null) {
            throw new \Exception("Unknown order line type: ".$typeCode);
        }

        $discountPct = null;
        if($description == null) $description = "*";

        $billToCustomerNoXML = "";
        if(trimgf($billToCustomerNo) != "" && intval($billToCustomerNo) > 0) {
            $billToCustomerNoXML = '<bill_to_customerno>'.intval($billToCustomerNo).'</bill_to_customerno>
        ';
        }

        // Display price
        $displayPriceXML = "";
        if($displayPrice != null) {
            $displayPriceXML = '<display_price>' . $this->navNumberFormat($displayPrice/100) . '</display_price>';
            $price = 0;
        }

        return '
      <line>
        '.$billToCustomerNoXML.'<type>'.$typeCode.'</type>
        <code>'.trimgf($code).'</code>'.($gift_code == null ? '': '
        <gift_code>'.trimgf($gift_code).'</gift_code>').'
        <description>'.substr($this->xmlString(str_replace(array("<",">"),"",$description)),0,49).'</description>
        <quantity>'.$this->navNumberFormat($quantity).'</quantity>
        '.('<price>'.($price === null ? "-999" : $this->navNumberFormat($price/100)).'</price>').'
        '.$displayPriceXML.'
        '.('<discount_pct>'.$this->navNumberFormat($discountPct).'</discount_pct>').'
        <decimal_factor>1.00</decimal_factor>
        
      </line>';
    }

    private function navNumberFormat($number)
    {
        if(is_string($number)) {
            echo "NUMBER IS STRING: ".$number; return 0;
        }
        return ($number == null ? '0.00' : number_format($number,2,".",""));
    }

    public function export()
    {

        // Generate xml
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<orders>
  <order>
    ';


        foreach($this->headers as $header) {

            $val = $header['value'];
            if(is_bool($val)) {
                if($val) $val = "true";
                else $val = "false";
            }

            $xml .= '<'.$header['name'].'>'.$val.'</'.$header['name'].'>
    ';
        }


        $xml .= '<lines>';

        foreach($this->lines as $line) {
            $xml .= $this->getOrderLine($line["code"], $line["type"], $line["quantity"],$line["price"], $line["description"],$line["bill_to_customer_no"],$line["gift_code"],$line["display_price"]);
        }

        $xml .= '</lines>
    <notes>
    </notes>
  </order>
</orders>';

        return $xml;

    }

}