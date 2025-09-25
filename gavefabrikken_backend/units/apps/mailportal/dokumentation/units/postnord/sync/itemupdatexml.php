<?php


namespace GFUnit\postnord\sync;

class ItemUpdateXML extends XMLDoc
{

    public static function generateItemXML($navItem)
    {

        $eanCode = trimgf($navItem->reference_no);
        $eanXML = "";
        $eanList = "";
        $netWeightXML = "";
        $grossWeightXML = "";

        // Generate ean xml
        if($eanCode != "") {
            if(self::ean_check($eanCode)) {
                $eanXML = "<EAN>".self::xmlString($eanCode)."</EAN>
        ";
                $eanList = "    <EANCodes>
                <EANCode>
                    <EAN>".self::xmlString($eanCode)."</EAN>
                </EANCode>
            </EANCodes>";
            }
        }

        // Weight xml
        if($navItem->gross_weight != "" && $navItem->gross_weight != "0") {
            $grossWeightXML = "    <GrossWeight>". $navItem->gross_weight."</GrossWeight>
        ";
        }

        if($navItem->net_weight != "" && $navItem->net_weight != "0") {
            $netWeightXML = "    <NetWeight>". $navItem->net_weight."</NetWeight>
        ";
        }


        $vareXML = "    <Item>
            <ItemNo>".self::xmlString($navItem->no)."</ItemNo>
            <Description>".self::xmlString($navItem->description)."</Description>
            <Unit>".self::xmlString($navItem->base_unit_of_measure)."</Unit>
            ".$eanXML.$netWeightXML.$grossWeightXML.$eanList."
        </Item>
    ";

        return $vareXML;

    }

    public static function generateEnvelopeXML($itemsXML)
    {

        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<Message>
	<MessType>ITEMUPDATE</MessType>
	<CreationDate>".date("Y-m-d")."</CreationDate>
	<CreationTime>".date("H:i:s")."</CreationTime>
	<Items>
	".$itemsXML."</Items>
</Message>";

        // Validate xml
        self::verifyXML($xml,__DIR__."/schemas/ItemUpdate.xsd");

        return $xml;

    }

    private static function ean_check($ean) {
        $ean = strrev($ean);
        $checksum = substr($ean, 0, 1);
        $number = substr($ean, 1);
        $total = 0;
        for ($i = 0, $max = strlen($number); $i < $max; $i++) {
            if (($i % 2) == 0) {
                $total += ($number[$i] * 3);
            } else {
                $total += $number[$i];
            }
        }
        $mod = ($total % 10);
        $calculated_checksum = (10 - $mod);
        if ($calculated_checksum == $checksum) {
            return true;
        } else {
            return false;
        }
    }
    /** REMOVED LINES

    <EAN>7315250014747</EAN>
    <NetWeight>4.55</NetWeight>
    <GrossWeight>4.9</GrossWeight>
    <Volume>1.6</Volume>
    <UnitsPerPackage>6</UnitsPerPackage>
    <UnitsPerCase>12</UnitsPerCase>
    <UnitsPerPallet>144</UnitsPerPallet>
    <BlockBeforeExpire>120</BlockBeforeExpire>
    <ReqExpDate>1</ReqExpDate>
    <EANCodes>
    <EANCode>
    <EAN>7315250014747</EAN>
    </EANCode>
    <EANCode>
    <EAN>7315250014856</EAN>
    </EANCode>
    <EANCode>
    <EAN>7315250014826</EAN>
    </EANCode>
    </EANCodes>

     */

}