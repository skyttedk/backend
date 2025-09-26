<?php

namespace GFUnit\navision\syncprivatedelivery;
use GFBiz\Model\Cardshop\ShopMetadata;
use GFBiz\units\UnitController;
use GFCommon\Model\Navision\CustomerObj;
use GFCommon\Model\Navision\CustomerWS;
use GFCommon\Model\Navision\CustomerXML;
use GFCommon\Model\Navision\NavClient;
use GFCommon\Model\Navision\OrderWS;
use GFCommon\Model\Navision\OrderXML;
use GFCommon\Model\Navision\ShipmentXML;

class ErrorCodes
{


    public static function getErrorStates() {

        return array(
            0 => "Afventer behandling",
            1 => "Afventer overførsel",
            2 => "Overført",
            3 => "Fejl i overførsel",
            4 => "Manuel pause",
            5 => "Overført og sendt til nav",
            10 => "Ukendt bruger",
            11 => "Behandlet tidligere",
            12 => "[RETRY] Blokkeret",
            13 => "[RETRY] Midlertidig luk",
            14 => "Demo",
            15 => "[RETRY] Ikke privatlevering",
            16 => "Erstattet",
            19 => "[RETRY] Bruger fejltjek",
            21 => "[RETRY] Ingen ordre",
            22 => "Ordre datafejl",
            23 => "Bruger datafejl",
            24 => "Shop datafejl",
            25 => "Virksomhed datafejl",
            26 => "Ordre ikke levering",
            27 => "Demo",
            29 => "Ordre fejtjek",
            31 => "Fejl i shop",
            33 => "Fejl i sprog",
            35 => "Fejl i virksomhed",
            36 => "[RETRY] Ordre slettet",
            37 => "[RETRY] Levering udsat",
            38 => "[RETRY] Ikke sendt til nav",
            39 => "Diverse tjek fejl",
            41 => "Fejl i gavmodel",
            42 => "Mangler varenr",
            43 => "Fejl i varenr",
            49 => "Gave fejltjek",
            50 => "Test bruger",
            51 => "Mangler navn",
            52 => "Mangler adresse",
            53 => "Mangler postnr",
            54 => "Mangler by",
            55 => "Mangler land",
            56 => "Mangler telefon",
            57 => "Mangler e-mail",
            58 => "E-mail fejl",
            59 => "Bruger fejltjek",
            61 => "[RETRY] Forsendelser pauset",
            62 => "[RETRY] Ordre ikke klar",
            63 => "[RETRY] Ingen nav data",
            64 => "[RETRY] Ikke betalt",
            69 => "Navision fejltjek",
            71 => "Forsendelse findes",
            79 => "Forsendelse fejltjek",
            89 => "Forsendelse xml fejl",
            99 => "Forsendelse commit fejl",
            100 => "Ordre skal ikke sendes",
            101 => "Donationer"
        );

    }

    public static function getRetryText($state) {
        $states = self::getErrorStates();
        if(isset($states[intval($state)])) return $states[intval($state)];
        else return "Ukendt tilstand [".$state."]";
    }

    public static function getRetryStates() {
        return array(12,13,15,16,21,36,38,61,62,63,64);
    }



}