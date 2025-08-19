<?php

namespace GFUnit\development\fixscripts;

class WebOrderErrorParse
{

    private $stats;

    public function run()
    {

        $rows = [];
        $inputfields = [];
        $responsefields = [];
        $jsondata = [];

        $data = json_decode($this->getErrorJSON(),true);

        foreach($data as $row) {

            foreach($row["input"] as $inputKey => $input) {
                if(!in_array($inputKey,$inputfields)) {
                    $inputfields[] = $inputKey;
                }
            }

            foreach($row["output"] as $responseKey => $response) {
                if(!in_array($responseKey,$responsefields)) {
                    $responsefields[] = $responseKey;
                }
            }

            foreach($row["check"] as $responseKey => $response) {
                if(!in_array($responseKey,$responsefields)) {
                    $responsefields[] = $responseKey;
                }
            }
        }

        echo "Date;";
        foreach($inputfields as $i => $inputField) {
            echo $inputField.";";
        }

        foreach($responsefields as $i => $responseField) {
            echo $responseField.";";
        }

        echo "\r\n";

        foreach($data as $row) {

            $jsonItem = array("date" => $row["Date"]);

            echo $row["Date"].";";

            foreach($inputfields as $fieldName) {
                if(!isset($row["input"][$fieldName])) {
                    echo ";";
                    $jsonItem[$fieldName] = "";
                }
                else {
                    echo utf8_decode($row["input"][$fieldName]).";";
                    $jsonItem[$fieldName] = ($row["input"][$fieldName]);
                }
            }

            foreach($responsefields as $fieldName) {
                if(!isset($row["output"][$fieldName])) {
                    echo ";";
                    $jsonItem[$fieldName] = "";
                }
                else {
                    echo utf8_decode($row["output"][$fieldName]).";";
                    $jsonItem[$fieldName] = ($row["output"][$fieldName]);
                }
                $jsondata[] = $jsonItem;
            }

            foreach($responsefields as $fieldName) {
                if(!isset($row["check"][$fieldName])) {
                    echo ";";
                    $jsonItem[$fieldName] = "";
                }
                else {

                    if($fieldName == "lang") {
                        if($row["check"][$fieldName] == 1) echo "Danmark;";
                        else if($row["check"][$fieldName] == 4) echo "Norge;";
                        else if($row["check"][$fieldName] == 5) echo "Sverige;";
                        else echo ";";

                    }
                    else {
                        echo utf8_decode($row["check"][$fieldName]).";";
                        $jsonItem[$fieldName] = ($row["check"][$fieldName]);
                    }

                }
                $jsondata[] = $jsonItem;
            }
            echo "\r\n";

        }

        echo "\r\nJSON\r\n".json_encode($jsondata)."\r\n";

    }

    private function getErrorJSON() {

        $content = '[
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": "Fri, 15 Oct 2021 15:11:00 +0200",
        "input": {
            "shop_id": "2558",
            "companyname": "BOQVIST BYGG AB",
            "cvr": "556774-0153",
            "phone": "0734272862",
            "contact_phone": "0734272862",
            "contact_email": "STEFAN@BOQVISTBYGGAB.SE",
            "bill_to_address": "KROKUSV\u00c4GEN 9",
            "bill_to_postal_code": "18694",
            "bill_to_city": "VALLENTUNA",
            "requisition_no": "JULKLAPPAR TILL PERSONAL",
            "bill_to_country": "SE",
            "quantity": "7",
            "expire_date": "31-12-2022",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "SE",
            "giftwrap": "1"
        },
        "Timestamp": 1634303460,
        "check": {
            "check1": "1: BS60854 - 2021-10-15 15:11",
            "check2": "",
            "check3": "",
            "shopname": "24 julklappar 1200 2021",
            "lang": 5
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Fri, 15 Oct 2021 14:05:10 +0200 (CEST)",
        "input": {
            "shop_id": "2558",
            "companyname": "R\u00e5vind Fastigheter i \u00c5hus AB",
            "cvr": "559002-0359",
            "bill_to_address": "Videv\u00e4gen 49",
            "bill_to_postal_code": "296 38 ",
            "bill_to_city": "\u00c5hus",
            "requisition_no": "Roger Liljenberg",
            "bill_to_country": "SE",
            "quantity": "5",
            "expire_date": "31-12-2022",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "SE",
            "contact_email": ""
        },
        "Timestamp": 1634299510,
        "check": {
            "check1": "",
            "check2": "",
            "check3": "",
            "shopname": "24 julklappar 1200 2021",
            "lang": 5
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Fri, 15 Oct 2021 13:58:21 +0200 (CEST)",
        "input": {
            "shop_id": "2395",
            "companyname": "S\u00f8by Revisorer A\/S",
            "cvr": "19125742",
            "bill_to_address": "Landbrugsvej 4",
            "bill_to_postal_code": "5260",
            "bill_to_city": "Odense S",
            "requisition_no": "Margit Fr\u00f8lund Hansen",
            "bill_to_country": "DK",
            "quantity": "14",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "giftwrap": "1",
            "contact_email": ""
        },
        "Timestamp": 1634299101,
        "check": {
            "check1": "1: BS60822 - 2021-10-15 13:58",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekortet DK 960",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Fri, 15 Oct 2021 13:49:55 +0200 (CEST)",
        "input": {
            "shop_id": "1981",
            "companyname": "Bitworks",
            "cvr": "1234567489",
            "bill_to_address": "fuglebakken 14",
            "bill_to_postal_code": "5610",
            "bill_to_city": "Assens",
            "requisition_no": "ulrich s\u00f8rensen",
            "bill_to_country": "SE",
            "quantity": "5",
            "expire_date": "31-12-2022",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "SE",
            "giftwrap": "1",
            "contact_email": ""
        },
        "Timestamp": 1634298595,
        "check": {
            "check1": "2: BS60815 - 2021-10-15 13:50",
            "check2": "",
            "check3": "",
            "shopname": "24Julklappar800 (Sverige)",
            "lang": 5
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field requisition_no: string must be longer than 30 characters",
            "field": "requisition_no",
            "type": "toolong",
            "min": "30",
            "max": "",
            "length": ""
        },
        "Date": " Fri, 15 Oct 2021 13:46:39 +0200 (CEST)",
        "input": {
            "shop_id": "2558",
            "companyname": "Bene Tandv\u00e5rd AB",
            "cvr": "556944-6452",
            "phone": "08-6116560",
            "contact_name": "Jeanette Slotthed",
            "contact_phone": "08-6116560",
            "contact_email": "jeanette@benetandvard.se",
            "bill_to_address": "Artillerigatan 16",
            "bill_to_postal_code": "11451",
            "bill_to_city": "Stockholm",
            "requisition_no": "Jeanette Slotthed       e-post: jeanette@benetandvard.se",
            "bill_to_country": "SE",
            "quantity": "25",
            "expire_date": "31-12-2022",
            "is_email": "0",
            "use_shipping_address": "1",
            "ship_to_company": "Bene Tandv\u00e5rd",
            "ship_to_address": "Artillerigatan 16",
            "ship_to_address_2": "Artillerigatan 16",
            "ship_to_postal_code": "114 51",
            "ship_to_city": "Stockholm",
            "ship_to_country": "SE"
        },
        "Timestamp": 1634298399,
        "check": {
            "check1": "",
            "check2": "",
            "check3": "",
            "shopname": "24 julklappar 1200 2021",
            "lang": 5
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Fri, 15 Oct 2021 13:44:18 +0200 (CEST)",
        "input": {
            "shop_id": "59",
            "companyname": "CK Nor Bygg",
            "cvr": "912418804 ",
            "bill_to_address": "Skjellvikaveien 12c",
            "bill_to_postal_code": "3237",
            "bill_to_city": "Sandefjord",
            "requisition_no": "Sara",
            "bill_to_country": "NO",
            "bill_to_email": "sarak@byggsoker.no",
            "quantity": "14",
            "expire_date": "07-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "NO",
            "contact_email": ""
        },
        "Timestamp": 1634298258,
        "check": {
            "check1": "1: BS60811 - 2021-10-15 13:45",
            "check2": "",
            "check3": "1: BS60812 - 2021-10-15 13:47",
            "shopname": "Julegavekortet NO 800",
            "lang": 4
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Fri, 15 Oct 2021 12:54:32 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "FINS\u00d8 A\/S",
            "cvr": "35515739",
            "bill_to_address": "H\u00e5ndv\u00e6rkervej  22B",
            "bill_to_postal_code": "6261",
            "bill_to_city": "Bredebro",
            "requisition_no": "Rita Matthiesen",
            "bill_to_country": "DK",
            "quantity": "32",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1634295272,
        "check": {
            "check1": "1: BS60794 - 2021-10-15 12:54",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Fri, 15 Oct 2021 12:47:05 +0200 (CEST)",
        "input": {
            "shop_id": "53",
            "companyname": "L\u00e6gehuset Aulum",
            "cvr": "51691415",
            "ean": "5790000135455",
            "bill_to_address": "Rugbjergvej 12",
            "bill_to_postal_code": "7490",
            "bill_to_city": "Aulum",
            "requisition_no": "Silje Starklint",
            "bill_to_country": "DK",
            "quantity": "5",
            "expire_date": "21-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1634294825,
        "check": {
            "check1": "1: BS60791 - 2021-10-15 12:48",
            "check2": "",
            "check3": "1: BS60793 - 2021-10-15 12:50",
            "shopname": "Guldgavekortet DK 800",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Fri, 15 Oct 2021 12:08:03 +0200 (CEST)",
        "input": {
            "shop_id": "55",
            "companyname": "Tyrolit A\/S",
            "cvr": "55050112",
            "contact_email": "Birger.londahl@tyrolit.com",
            "bill_to_address": "Hersted\u00f8stervej 21 2.sal",
            "bill_to_postal_code": "2600 ",
            "bill_to_city": "Glostrup",
            "requisition_no": "Birger",
            "bill_to_country": "DK",
            "quantity": "8",
            "expire_date": "31-12-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK"
        },
        "Timestamp": 1634292483,
        "check": {
            "check1": "1: BS60782 - 2021-10-15 12:08",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 560",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Fri, 15 Oct 2021 11:54:26 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "Ocean Team Group A\/S",
            "cvr": "32833373",
            "bill_to_address": "Vesterhavsgade 56",
            "bill_to_postal_code": "6700",
            "bill_to_city": "Esbjerg",
            "requisition_no": "Anne-Mette Jacobsen",
            "bill_to_country": "DK",
            "quantity": "30",
            "expire_date": "21-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "giftwrap": "1",
            "contact_email": ""
        },
        "Timestamp": 1634291666,
        "check": {
            "check1": "1: BS60777 - 2021-10-15 11:58",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Fri, 15 Oct 2021 11:26:59 +0200 (CEST)",
        "input": {
            "shop_id": "575",
            "companyname": "Senzone ApS",
            "cvr": "32142265",
            "bill_to_address": "N\u00f8rreskov Bakke 14",
            "bill_to_postal_code": "8600",
            "bill_to_city": "Silkeborg",
            "requisition_no": "Martin",
            "bill_to_country": "DK",
            "quantity": "15",
            "expire_date": "14-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1634290019,
        "check": {
            "check1": "1: BS60769 - 2021-10-15 11:27",
            "check2": "",
            "check3": "",
            "shopname": "Designjulegaven",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Fri, 15 Oct 2021 11:25:10 +0200 (CEST)",
        "input": {
            "shop_id": "2395",
            "companyname": "Estate Invest A\/S",
            "cvr": "33746571",
            "phone": "47767709",
            "contact_phone": "47767709",
            "contact_email": "lisbet@estateinvest.dk",
            "bill_to_address": "S\u00f8frydvej 10",
            "bill_to_postal_code": "3300",
            "bill_to_city": "Frederiksv\u00e6rk",
            "requisition_no": "Lisbet Olsen \/ Peter Nielsen",
            "bill_to_country": "DK",
            "quantity": "17",
            "expire_date": "31-12-2021",
            "is_email": "0",
            "use_shipping_address": "1",
            "ship_to_company": "Estate Invest A\/S",
            "ship_to_address": "S\u00f8frydvej 10",
            "ship_to_address_2": "S\u00f8frydvej 10",
            "ship_to_postal_code": "3300",
            "ship_to_city": "Frederiksv\u00e6rk",
            "ship_to_country": "DK"
        },
        "Timestamp": 1634289910,
        "check": {
            "check1": "1: BS60764 - 2021-10-15 11:25",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekortet DK 960",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Fri, 15 Oct 2021 11:08:29 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "LSS Etikettering A\/S",
            "cvr": "26108209",
            "bill_to_address": "Normansvej 8",
            "bill_to_postal_code": "8920",
            "bill_to_city": "Randers NV",
            "requisition_no": "Ulla Laursen",
            "bill_to_country": "DK",
            "quantity": "43",
            "expire_date": "21-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "giftwrap": "1",
            "contact_email": ""
        },
        "Timestamp": 1634288909,
        "check": {
            "check1": "1: BS60756 - 2021-10-15 11:09",
            "check2": "",
            "check3": "1: BS60757 - 2021-10-15 11:10",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Fri, 15 Oct 2021 10:58:44 +0200 (CEST)",
        "input": {
            "shop_id": "575",
            "companyname": "Q8 grillen Br\u00f8nderslev ",
            "cvr": "21527246",
            "bill_to_address": "Jerslevvej 2",
            "bill_to_postal_code": "9700",
            "bill_to_city": "Br\u00f8nderslev ",
            "requisition_no": "Q8 grillen ",
            "bill_to_country": "DK",
            "quantity": "6",
            "expire_date": "31-10-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1634288324,
        "check": {
            "check1": "1: BS60753 - 2021-10-15 10:59",
            "check2": "",
            "check3": "",
            "shopname": "Designjulegaven",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Fri, 15 Oct 2021 10:42:55 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "Dauding Smede & Maskinfabrik",
            "cvr": "27465617",
            "bill_to_address": "davdingvej, 20",
            "bill_to_postal_code": "8740",
            "bill_to_city": "Br\u00e6dstrup",
            "requisition_no": "Kim Kristensen",
            "bill_to_country": "DK",
            "quantity": "10",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "giftwrap": "1",
            "contact_email": ""
        },
        "Timestamp": 1634287375,
        "check": {
            "check1": "1: BS60747 - 2021-10-15 10:43",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_phone: value is required",
            "field": "contact_phone",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Fri, 15 Oct 2021 10:38:07 +0200 (CEST)",
        "input": {
            "shop_id": "52",
            "companyname": "danbolig Haderslev",
            "cvr": "31051215",
            "contact_name": "Charlotte Nicolaisen",
            "bill_to_address": "N\u00f8rregade 44",
            "bill_to_postal_code": "6100",
            "bill_to_city": "Haderslev",
            "requisition_no": "Charlotte Nicolaisen",
            "bill_to_country": "DK",
            "quantity": "5",
            "expire_date": "14-11-2021",
            "is_email": "1",
            "use_shipping_address": "1",
            "ship_to_company": "danbolig Haderslev",
            "ship_to_address": "N\u00f8rregade  44",
            "ship_to_address_2": "N\u00f8rregade  44",
            "ship_to_postal_code": "6100",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1634287087,
        "check": {
            "check1": "",
            "check2": "",
            "check3": "2: BS60790 - 2021-10-15 12:46",
            "shopname": "Julegavekortet DK",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field cvr: value is required",
            "field": "cvr",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Fri, 15 Oct 2021 10:33:27 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "El-Center Vest A\/S",
            "bill_to_address": "Christiansborgvej 10",
            "bill_to_postal_code": "7560",
            "bill_to_city": "Hjerm",
            "requisition_no": "INgrid",
            "bill_to_country": "DK",
            "quantity": "21",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "giftwrap": "1",
            "cvr": "",
            "contact_email": ""
        },
        "Timestamp": 1634286807,
        "check": {
            "check1": "*",
            "check2": "*",
            "check3": "*",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Fri, 15 Oct 2021 10:07:56 +0200 (CEST)",
        "input": {
            "shop_id": "575",
            "companyname": "Tryk 2100 ApS",
            "cvr": "29635269",
            "bill_to_address": "\u00d8sterbrogade 84",
            "bill_to_postal_code": "2100",
            "bill_to_city": "K\u00f8benhavn \u00d8",
            "requisition_no": "Poul Recke",
            "bill_to_country": "DK",
            "quantity": "5",
            "expire_date": "14-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "giftwrap": "1",
            "contact_email": ""
        },
        "Timestamp": 1634285276,
        "check": {
            "check1": "1: BS60731 - 2021-10-15 10:08",
            "check2": "",
            "check3": "",
            "shopname": "Designjulegaven",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Fri, 15 Oct 2021 10:04:53 +0200 (CEST)",
        "input": {
            "shop_id": "310",
            "companyname": "Solly Plus Aps",
            "cvr": "40244379",
            "bill_to_address": "M\u00f8llegaardsvej 33",
            "bill_to_postal_code": "6933",
            "bill_to_city": "Kib\u00e6k",
            "requisition_no": "Solvita Nielsen",
            "bill_to_country": "DK",
            "quantity": "16",
            "expire_date": "21-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1634285093,
        "check": {
            "check1": "1: BS60729 - 2021-10-15 10:05",
            "check2": "",
            "check3": "",
            "shopname": "Dr\u00f8mmegavekortet 300",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Fri, 15 Oct 2021 09:56:55 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "Malerfirmaet Asberg Olesen",
            "cvr": "18295970",
            "bill_to_address": "Ribevej 25",
            "bill_to_postal_code": "6760",
            "bill_to_city": "Ribe",
            "requisition_no": "Bente Asberg Olesen",
            "bill_to_country": "DK",
            "quantity": "6",
            "expire_date": "07-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "giftwrap": "1",
            "contact_email": ""
        },
        "Timestamp": 1634284615,
        "check": {
            "check1": "2: BS60721 - 2021-10-15 09:57",
            "check2": "",
            "check3": "2: BS60724 - 2021-10-15 09:59",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Fri, 15 Oct 2021 09:49:35 +0200 (CEST)",
        "input": {
            "shop_id": "52",
            "companyname": "A. Villadsen A\/S",
            "cvr": "31771986",
            "ean": "5790002391538",
            "bill_to_address": "Vestre Hedevej 28B",
            "bill_to_postal_code": "4000",
            "bill_to_city": "Roskilde",
            "requisition_no": "Tina Hansen",
            "bill_to_country": "DK",
            "quantity": "38",
            "expire_date": "31-12-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1634284175,
        "check": {
            "check1": "1: BS60717 - 2021-10-15 09:49",
            "check2": "",
            "check3": "",
            "shopname": "Julegavekortet DK",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Fri, 15 Oct 2021 09:35:13 +0200 (CEST)",
        "input": {
            "shop_id": "575",
            "companyname": "GK Tr\u00e5dgnist ApS",
            "cvr": "21810142",
            "bill_to_address": "Engtoften 13",
            "bill_to_postal_code": "6920",
            "bill_to_city": "Videb\u00e6k",
            "requisition_no": "Mogens Andersen",
            "bill_to_country": "DK",
            "quantity": "15",
            "expire_date": "14-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "giftwrap": "1",
            "contact_email": ""
        },
        "Timestamp": 1634283313,
        "check": {
            "check1": "1: BS60711 - 2021-10-15 09:35",
            "check2": "",
            "check3": "1: BS60697 - 2021-10-15 09:03",
            "shopname": "Designjulegaven",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Fri, 15 Oct 2021 09:32:52 +0200 (CEST)",
        "input": {
            "shop_id": "53",
            "companyname": "PTL Valve Services A\/S",
            "cvr": "36982357",
            "bill_to_address": "Mukkerten 19",
            "bill_to_postal_code": "6715",
            "bill_to_city": "Esbjerg N",
            "requisition_no": "Per Bak",
            "bill_to_country": "DK",
            "quantity": "5",
            "expire_date": "21-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1634283172,
        "check": {
            "check1": "1: BS60707 - 2021-10-15 09:33",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekortet DK 800",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Fri, 15 Oct 2021 09:31:03 +0200 (CEST)",
        "input": {
            "shop_id": "2395",
            "companyname": "B\u00f8rnel\u00e6geklinikken v\/ Elise Snitker",
            "cvr": "33662971",
            "bill_to_address": "Boulevarden 9, 2. th.",
            "bill_to_postal_code": "9000",
            "bill_to_city": "Aalborg",
            "requisition_no": "Elise",
            "bill_to_country": "DK",
            "quantity": "5",
            "expire_date": "21-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "giftwrap": "1",
            "contact_email": ""
        },
        "Timestamp": 1634283063,
        "check": {
            "check1": "1: BS60706 - 2021-10-15 09:31",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekortet DK 960",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Fri, 15 Oct 2021 08:49:38 +0200 (CEST)",
        "input": {
            "shop_id": "310",
            "companyname": "GK Tr\u00e5dgnist ApS",
            "cvr": "21810142",
            "bill_to_address": "Engtoften 13",
            "bill_to_postal_code": "6920",
            "bill_to_city": "Videb\u00e6k",
            "requisition_no": "Mogens Andersen",
            "bill_to_country": "DK",
            "quantity": "5",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "giftwrap": "1",
            "contact_email": ""
        },
        "Timestamp": 1634280578,
        "check": {
            "check1": "1: BS60697 - 2021-10-15 09:03",
            "check2": "",
            "check3": "1: BS60711 - 2021-10-15 09:35",
            "shopname": "Dr\u00f8mmegavekortet 300",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Fri, 15 Oct 2021 08:45:39 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "FysioDanmark Hj\u00f8rring I\/S",
            "cvr": "42433942",
            "bill_to_address": "\u00c5strupvej 53",
            "bill_to_postal_code": "9800",
            "bill_to_city": "Hj\u00f8rring",
            "requisition_no": "Vegard Aamold",
            "bill_to_country": "DK",
            "quantity": "11",
            "expire_date": "21-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1634280339,
        "check": {
            "check1": "1: BS60688 - 2021-10-15 08:46",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Fri, 15 Oct 2021 08:39:18 +0200 (CEST)",
        "input": {
            "shop_id": "59",
            "companyname": "Papa Mios International AS",
            "cvr": "985452962",
            "bill_to_address": "Gaupemyrheia 14",
            "bill_to_postal_code": "4790",
            "bill_to_city": "Lillesand",
            "requisition_no": "Stig Theo Eriksen",
            "bill_to_country": "NO",
            "bill_to_email": "post@papamios.no",
            "quantity": "16",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "NO",
            "giftwrap": "1",
            "contact_email": ""
        },
        "Timestamp": 1634279958,
        "check": {
            "check1": "1: BS60685 - 2021-10-15 08:41",
            "check2": "",
            "check3": "",
            "shopname": "Julegavekortet NO 800",
            "lang": 4
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Fri, 15 Oct 2021 07:51:06 +0200 (CEST)",
        "input": {
            "shop_id": "53",
            "companyname": "Brand Surface ApS",
            "cvr": "32442277",
            "bill_to_address": "Carl Jacobsens Vej 16",
            "bill_to_postal_code": "2500",
            "bill_to_city": "Valby",
            "requisition_no": "Jon Axelsen",
            "bill_to_country": "DK",
            "quantity": "13",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1634277066,
        "check": {
            "check1": "1: BS60675 - 2021-10-15 07:52",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekortet DK 800",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Fri, 15 Oct 2021 07:27:34 +0200 (CEST)",
        "input": {
            "shop_id": "1832",
            "companyname": "J. Olsson Transport AB",
            "cvr": "559117-6192",
            "phone": "0722166735",
            "contact_phone": "0722166735",
            "contact_email": "j.olssontransport@gmail.com",
            "bill_to_address": "Halnav\u00e4gen 50",
            "bill_to_postal_code": "545 34",
            "bill_to_city": "T\u00f6reboda",
            "requisition_no": "Anna Olsson",
            "bill_to_country": "SE",
            "quantity": "10",
            "expire_date": "31-12-2022",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "SE"
        },
        "Timestamp": 1634275654,
        "check": {
            "check1": "1: BS60673 - 2021-10-15 07:27",
            "check2": "",
            "check3": "",
            "shopname": "24Julklappar400 (Sverige)",
            "lang": 5
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu, 14 Oct 2021 22:12:54 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "Louis Nielsen Nyk\u00f8bing Falster",
            "cvr": "31785073",
            "bill_to_address": "\u00d8sterg\u00e5gade 10-12",
            "bill_to_postal_code": "4800",
            "bill_to_city": "Nyk\u00f8bing Falster",
            "requisition_no": "Ane Skytte",
            "bill_to_country": "DK",
            "quantity": "17",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "giftwrap": "1",
            "contact_email": ""
        },
        "Timestamp": 1634242374,
        "check": {
            "check1": "1: BS60670 - 2021-10-14 22:13",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu, 14 Oct 2021 21:05:01 +0200 (CEST)",
        "input": {
            "shop_id": "575",
            "companyname": "L\u00e6ge Thore Eriksen",
            "cvr": "27053122",
            "bill_to_address": "Skt. Anne Plads 2, 3. sal",
            "bill_to_postal_code": "5000",
            "bill_to_city": "Odense C",
            "requisition_no": "Thore Eriksen",
            "bill_to_country": "DK",
            "quantity": "10",
            "expire_date": "31-10-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1634238301,
        "check": {
            "check1": "1: BS60669 - 2021-10-14 21:05",
            "check2": "",
            "check3": "",
            "shopname": "Designjulegaven",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu, 14 Oct 2021 20:12:20 +0200 (CEST)",
        "input": {
            "shop_id": "53",
            "companyname": "SETEK Process & Data",
            "cvr": "1006 0826",
            "bill_to_address": "Truds\u00f8vej 12B",
            "bill_to_postal_code": "7600",
            "bill_to_city": "Struer",
            "requisition_no": "Gregers Lund",
            "bill_to_country": "DK",
            "quantity": "6",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "giftwrap": "1",
            "contact_email": ""
        },
        "Timestamp": 1634235140,
        "check": {
            "check1": "",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekortet DK 800",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu, 14 Oct 2021 19:15:26 +0200 (CEST)",
        "input": {
            "shop_id": "2395",
            "companyname": "Proces-Automatik ApS",
            "cvr": "41248858",
            "bill_to_address": "Str\u00f8lillevej 32",
            "bill_to_postal_code": "3320",
            "bill_to_city": "Sk\u00e6vinge",
            "requisition_no": "Julegaver",
            "bill_to_country": "DK",
            "quantity": "10",
            "expire_date": "07-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1634231726,
        "check": {
            "check1": "1: BS60664 - 2021-10-14 19:15",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekortet DK 960",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu, 14 Oct 2021 16:07:47 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "konditorbager haarby bageri",
            "cvr": "30289072",
            "bill_to_address": "algade 28",
            "bill_to_postal_code": "5683",
            "bill_to_city": "Haarby",
            "requisition_no": "allan ringe",
            "bill_to_country": "DK",
            "quantity": "16",
            "expire_date": "31-12-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1634220467,
        "check": {
            "check1": "1: BS60654 - 2021-10-14 16:09",
            "check2": "",
            "check3": "1: BS60656 - 2021-10-14 16:12",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu, 14 Oct 2021 15:20:10 +0200 (CEST)",
        "input": {
            "shop_id": "2395",
            "companyname": "Atlab A\/S",
            "cvr": "42297372",
            "bill_to_address": "Magstr\u00e6de 10A, 2 & 3 sal tv. ",
            "bill_to_postal_code": "1204",
            "bill_to_city": "K\u00f8benhavn K",
            "requisition_no": "Savana Jaff",
            "bill_to_country": "DK",
            "quantity": "7",
            "expire_date": "21-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "giftwrap": "1",
            "contact_email": ""
        },
        "Timestamp": 1634217610,
        "check": {
            "check1": "",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekortet DK 960",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu, 14 Oct 2021 15:17:20 +0200 (CEST)",
        "input": {
            "shop_id": "1981",
            "companyname": "Energibyr\u00e5n Q AB",
            "cvr": "556736-3139",
            "bill_to_address": "S\u00f6derv\u00e4g 4 A 1tr",
            "bill_to_postal_code": "62158",
            "bill_to_city": "Visby",
            "requisition_no": "Peter Qvistr\u00f6m",
            "bill_to_country": "SE",
            "quantity": "9",
            "expire_date": "31-12-2022",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "SE",
            "contact_email": ""
        },
        "Timestamp": 1634217440,
        "check": {
            "check1": "1: BS60642 - 2021-10-14 15:17",
            "check2": "",
            "check3": "",
            "shopname": "24Julklappar800 (Sverige)",
            "lang": 5
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu, 14 Oct 2021 14:18:06 +0200 (CEST)",
        "input": {
            "shop_id": "2395",
            "companyname": "LinkGRC",
            "cvr": "28870787",
            "bill_to_address": "Jagtvej 223, 4 sal",
            "bill_to_postal_code": "2100",
            "bill_to_city": "K\u00f8benhavn \u00d8",
            "requisition_no": "Tomas Hellum tlf. 40337289",
            "bill_to_country": "DK",
            "quantity": "10",
            "expire_date": "07-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "giftwrap": "1",
            "contact_email": ""
        },
        "Timestamp": 1634213886,
        "check": {
            "check1": "1: BS60627 - 2021-10-14 14:18",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekortet DK 960",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu, 14 Oct 2021 14:10:07 +0200 (CEST)",
        "input": {
            "shop_id": "52",
            "companyname": "Steeltec Odense aps",
            "cvr": "24233510",
            "phone": "28402561",
            "contact_phone": "28402561",
            "contact_email": "steeltec@steeltec.dk",
            "bill_to_address": "gr\u00f8nvej 65",
            "bill_to_postal_code": "5260",
            "bill_to_city": "Odense S",
            "requisition_no": "Tine Holm",
            "bill_to_country": "DK",
            "quantity": "17",
            "expire_date": "14-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "giftwrap": "1"
        },
        "Timestamp": 1634213407,
        "check": {
            "check1": "1: BS60621 - 2021-10-14 14:10",
            "check2": "",
            "check3": "",
            "shopname": "Julegavekortet DK",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field cvr: CVR must be 10 digit number ",
            "field": "cvr",
            "type": "exactlength",
            "min": "",
            "max": "",
            "length": "10"
        },
        "Date": " Thu, 14 Oct 2021 14:08:05 +0200 (CEST)",
        "input": {
            "shop_id": "1981",
            "companyname": "Jonsson Entr AB",
            "cvr": "556698-16189",
            "phone": "0703744116",
            "contact_name": "Tomas Jonsson",
            "contact_phone": "0703744116",
            "contact_email": "tomas@ljeab.se",
            "bill_to_address": "H\u00f6rn\u00e4sv\u00e4gen 5",
            "bill_to_postal_code": "89431",
            "bill_to_city": "Sj\u00e4levad",
            "requisition_no": "Tomas Jonsson",
            "bill_to_country": "SE",
            "quantity": "12",
            "expire_date": "31-12-2022",
            "is_email": "0",
            "use_shipping_address": "1",
            "ship_to_company": "Jonsson Entr AB",
            "ship_to_address": "Sulkyv\u00e4gen 11",
            "ship_to_address_2": "Sulkyv\u00e4gen 11",
            "ship_to_postal_code": "89431",
            "ship_to_city": "Sj\u00e4levad",
            "ship_to_country": "SE"
        },
        "Timestamp": 1634213285,
        "check": {
            "check1": "",
            "check2": "",
            "check3": "3: BS60845 - 2021-10-15 14:51",
            "shopname": "24Julklappar800 (Sverige)",
            "lang": 5
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field cvr: CVR must be 10 digit number ",
            "field": "cvr",
            "type": "exactlength",
            "min": "",
            "max": "",
            "length": "10"
        },
        "Date": " Thu, 14 Oct 2021 14:04:35 +0200 (CEST)",
        "input": {
            "shop_id": "1832",
            "companyname": "Jonsson Entr AB",
            "cvr": "55669891618",
            "phone": "0703744116",
            "contact_name": "Tomas Jonsson",
            "contact_phone": "0703744116",
            "contact_email": "tomas@ljeab.se",
            "bill_to_address": "H\u00f6rn\u00e4sv\u00e4gen 5",
            "bill_to_postal_code": "89431",
            "bill_to_city": "Sj\u00e4levad",
            "requisition_no": "Tomas Jonsson",
            "bill_to_country": "SE",
            "quantity": "12",
            "expire_date": "31-12-2022",
            "is_email": "0",
            "use_shipping_address": "1",
            "ship_to_company": "Jonsson Entr AB",
            "ship_to_address": "Sulkyv\u00e4gen 11",
            "ship_to_address_2": "Sulkyv\u00e4gen 11",
            "ship_to_postal_code": "89431",
            "ship_to_city": "Sj\u00e4levad",
            "ship_to_country": "SE"
        },
        "Timestamp": 1634213075,
        "check": {
            "check1": "",
            "check2": "",
            "check3": "3: BS60845 - 2021-10-15 14:51",
            "shopname": "24Julklappar400 (Sverige)",
            "lang": 5
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu, 14 Oct 2021 13:53:28 +0200 (CEST)",
        "input": {
            "shop_id": "575",
            "companyname": "Danske Diakonhjem",
            "cvr": "38660918",
            "ean": "5798006427642",
            "phone": "21829743",
            "contact_phone": "21829743",
            "contact_email": "grj@diakon.dk",
            "bill_to_address": "Bellisvej 19",
            "bill_to_postal_code": "6950",
            "bill_to_city": "Ringk\u00f8bing",
            "requisition_no": "Grethe Jeppesen",
            "bill_to_country": "DK",
            "quantity": "7",
            "expire_date": "31-10-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_address": "Bellisvej 19",
            "ship_to_address_2": "Bellisvej 19",
            "ship_to_postal_code": "6950",
            "ship_to_city": "Ringk\u00f8bing",
            "ship_to_country": "DK"
        },
        "Timestamp": 1634212408,
        "check": {
            "check1": "1: BS60618 - 2021-10-14 13:53",
            "check2": "",
            "check3": "",
            "shopname": "Designjulegaven",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu, 14 Oct 2021 13:41:47 +0200 (CEST)",
        "input": {
            "shop_id": "59",
            "companyname": "CAE Consult Scandinavia AS",
            "cvr": "948668963",
            "bill_to_address": "Nybergflata 2",
            "bill_to_postal_code": "3737",
            "bill_to_city": "Skien",
            "requisition_no": "Maria K. Kvendb\u00f8",
            "bill_to_country": "NO",
            "bill_to_email": "invoice@ccs.no",
            "quantity": "12",
            "expire_date": "07-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "NO",
            "giftwrap": "1",
            "contact_email": ""
        },
        "Timestamp": 1634211707,
        "check": {
            "check1": "1: BS60614 - 2021-10-14 13:42",
            "check2": "",
            "check3": "",
            "shopname": "Julegavekortet NO 800",
            "lang": 4
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu, 14 Oct 2021 13:27:21 +0200 (CEST)",
        "input": {
            "shop_id": "1832",
            "companyname": "J\u00e4mtlands Travs\u00e4llskap",
            "cvr": "893200-0493",
            "phone": "070-6589451",
            "contact_phone": "070-6589451",
            "contact_email": "jan.quicklund@ostersund.travsport.se",
            "bill_to_address": "Krondikesv\u00e4gen 91A",
            "bill_to_postal_code": "83148",
            "bill_to_city": "\u00d6stersund",
            "requisition_no": "Jan Quicklund",
            "bill_to_country": "SE",
            "quantity": "25",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "SE"
        },
        "Timestamp": 1634210841,
        "check": {
            "check1": "1: BS60611 - 2021-10-14 13:28",
            "check2": "",
            "check3": "",
            "shopname": "24Julklappar400 (Sverige)",
            "lang": 5
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu, 14 Oct 2021 13:06:23 +0200 (CEST)",
        "input": {
            "shop_id": "52",
            "companyname": "klinik for fodterapi",
            "cvr": "17983377",
            "contact_email": "lone_storgaard@hotmail.com",
            "bill_to_address": "Jernbanegade 2a",
            "bill_to_postal_code": "6740",
            "bill_to_city": "Bramming",
            "requisition_no": "Lone Storgaard",
            "bill_to_country": "DK",
            "quantity": "8",
            "expire_date": "14-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK"
        },
        "Timestamp": 1634209583,
        "check": {
            "check1": "1: BS60601 - 2021-10-14 13:07",
            "check2": "",
            "check3": "",
            "shopname": "Julegavekortet DK",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu, 14 Oct 2021 12:54:53 +0200 (CEST)",
        "input": {
            "shop_id": "2395",
            "companyname": "JH import ApS",
            "cvr": "35204849",
            "bill_to_address": "T\u00f8mmergade 15 A",
            "bill_to_postal_code": "6830",
            "bill_to_city": "N\u00f8rre Nebel",
            "requisition_no": "Louise",
            "bill_to_country": "DK",
            "quantity": "14",
            "expire_date": "21-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "giftwrap": "1",
            "contact_email": ""
        },
        "Timestamp": 1634208893,
        "check": {
            "check1": "1: BS60587 - 2021-10-14 12:55",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekortet DK 960",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu, 14 Oct 2021 12:27:35 +0200 (CEST)",
        "input": {
            "shop_id": "54",
            "companyname": "\u00d8ster Snede Skole",
            "cvr": "29189587",
            "ean": "5798006261369",
            "phone": "24904891",
            "contact_phone": "24904891",
            "contact_email": "mads.paaske@hedensted.dk",
            "bill_to_address": "Ribevej 65",
            "bill_to_postal_code": "8723",
            "bill_to_city": "L\u00f8sning",
            "requisition_no": "Mads Paaske",
            "bill_to_country": "DK",
            "quantity": "21",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "giftwrap": "1"
        },
        "Timestamp": 1634207255,
        "check": {
            "check1": "1: BS60568 - 2021-10-14 12:27",
            "check2": "",
            "check3": "3: BS57406 - 2021-09-21 09:18",
            "shopname": "24 Gaver DK 400",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu, 14 Oct 2021 12:11:50 +0200 (CEST)",
        "input": {
            "shop_id": "310",
            "companyname": "Yggdrasil",
            "cvr": "18926784",
            "bill_to_address": "Slotg\u00e5rdsvej 4",
            "bill_to_postal_code": "9293",
            "bill_to_city": "Skibsted",
            "requisition_no": "Anne-Maj Seerup",
            "bill_to_country": "DK",
            "quantity": "20",
            "expire_date": "07-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1634206310,
        "check": {
            "check1": "1: BS60567 - 2021-10-14 12:12",
            "check2": "",
            "check3": "",
            "shopname": "Dr\u00f8mmegavekortet 300",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu, 14 Oct 2021 11:58:54 +0200 (CEST)",
        "input": {
            "shop_id": "2395",
            "companyname": "Werners Isolering ApS",
            "cvr": "35254323",
            "bill_to_address": "R\u00e5dyrl\u00f8kken 23",
            "bill_to_postal_code": "5210",
            "bill_to_city": "Odense NV",
            "requisition_no": "Karina Nielsen",
            "bill_to_country": "DK",
            "quantity": "6",
            "expire_date": "31-12-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "giftwrap": "1",
            "contact_email": ""
        },
        "Timestamp": 1634205534,
        "check": {
            "check1": "",
            "check2": "",
            "check3": "1: BS60563 - 2021-10-14 12:01",
            "shopname": "Guldgavekortet DK 960",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu, 14 Oct 2021 11:53:33 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "L\u00e6gernes Hus Langeskov",
            "cvr": "23365413",
            "phone": "20451399",
            "contact_phone": "20451399",
            "contact_email": "Mpb@langeskov.laegens.net",
            "bill_to_address": "Langeskov Centret 24",
            "bill_to_postal_code": "5550",
            "bill_to_city": "Langeskov",
            "requisition_no": "Maria Pia Bonnema",
            "bill_to_country": "DK",
            "quantity": "9",
            "expire_date": "21-11-2021",
            "is_email": "1",
            "use_shipping_address": "1",
            "ship_to_company": "L\u00e6gernes Hus Langeskov",
            "ship_to_address": "Langeskov Centret 24",
            "ship_to_address_2": "Langeskov Centret 24",
            "ship_to_postal_code": "5550",
            "ship_to_city": "Langeskov",
            "ship_to_country": "DK",
            "giftwrap": "1"
        },
        "Timestamp": 1634205213,
        "check": {
            "check1": "1: BS60554 - 2021-10-14 11:53",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu, 14 Oct 2021 11:52:36 +0200 (CEST)",
        "input": {
            "shop_id": "54",
            "companyname": "Bullerup sv\u00f8mmebad & Sundhedshus",
            "cvr": "32145140",
            "bill_to_address": "M\u00f8lledammen 10 A",
            "bill_to_postal_code": "5320",
            "bill_to_city": "Agedrup",
            "requisition_no": "Matilde \u00d8bro Lindegren",
            "bill_to_country": "DK",
            "quantity": "9",
            "expire_date": "31-12-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "giftwrap": "1",
            "contact_email": ""
        },
        "Timestamp": 1634205156,
        "check": {
            "check1": "1: BS60557 - 2021-10-14 11:54",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 400",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu, 14 Oct 2021 11:52:29 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "Frederiksberg Beregnerservice ApS",
            "cvr": "30551222",
            "bill_to_address": "Valh\u00f8js All\u00e9 190, A, Opgang 2, 1.sal",
            "bill_to_postal_code": "2610",
            "bill_to_city": "R\u00d8DOVRE",
            "requisition_no": "Lena Krogh Petersen",
            "bill_to_country": "DK",
            "quantity": "5",
            "expire_date": "07-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1634205149,
        "check": {
            "check1": "1: BS60555 - 2021-10-14 11:54",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu, 14 Oct 2021 11:46:42 +0200 (CEST)",
        "input": {
            "shop_id": "52",
            "companyname": "Nordsj\u00e6llands Tandl\u00e6geCenter",
            "cvr": "34941017",
            "bill_to_address": "Frederiksgade, 2A 1.sal",
            "bill_to_postal_code": "3400",
            "bill_to_city": "Hiller\u00f8d",
            "requisition_no": "??",
            "bill_to_country": "DK",
            "quantity": "20",
            "expire_date": "14-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "giftwrap": "1",
            "contact_email": ""
        },
        "Timestamp": 1634204802,
        "check": {
            "check1": "",
            "check2": "1: BS60755 - 2021-10-15 11:03",
            "check3": "",
            "shopname": "Julegavekortet DK",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu, 14 Oct 2021 11:45:09 +0200 (CEST)",
        "input": {
            "shop_id": "59",
            "companyname": "Roms\u00e5s Dyrekinikk",
            "cvr": "918721894",
            "bill_to_address": "roms\u00e5s senter 1 3etg",
            "bill_to_postal_code": "0970",
            "bill_to_city": "oslo",
            "requisition_no": "Monica Hansen",
            "bill_to_country": "NO",
            "bill_to_email": "vet.monica.hansen@gmail.com",
            "quantity": "5",
            "expire_date": "21-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "NO",
            "contact_email": ""
        },
        "Timestamp": 1634204709,
        "check": {
            "check1": "1: BS60551 - 2021-10-14 11:45",
            "check2": "",
            "check3": "",
            "shopname": "Julegavekortet NO 800",
            "lang": 4
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu, 14 Oct 2021 11:36:22 +0200 (CEST)",
        "input": {
            "shop_id": "310",
            "companyname": "Pandekagebilen",
            "cvr": "31460743",
            "bill_to_address": "Alr\u00f8vej 66",
            "bill_to_postal_code": "8300",
            "bill_to_city": "Odder",
            "requisition_no": "Tina Sindberg Nielsen",
            "bill_to_country": "DK",
            "quantity": "25",
            "expire_date": "07-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1634204182,
        "check": {
            "check1": "1: BS60547 - 2021-10-14 11:37",
            "check2": "",
            "check3": "",
            "shopname": "Dr\u00f8mmegavekortet 300",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu, 14 Oct 2021 11:27:12 +0200 (CEST)",
        "input": {
            "shop_id": "575",
            "companyname": "AquaDjurs A\/S",
            "cvr": "30532740",
            "ean": "5790001718695",
            "bill_to_address": "Langagervej 12",
            "bill_to_postal_code": "8500",
            "bill_to_city": "Grenaa",
            "requisition_no": "Palle Mikkelsen",
            "bill_to_country": "DK",
            "quantity": "22",
            "expire_date": "14-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "giftwrap": "1",
            "contact_email": ""
        },
        "Timestamp": 1634203632,
        "check": {
            "check1": "1: BS60540 - 2021-10-14 11:28",
            "check2": "",
            "check3": "",
            "shopname": "Designjulegaven",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu, 14 Oct 2021 11:24:42 +0200 (CEST)",
        "input": {
            "shop_id": "52",
            "companyname": "Filipskolen",
            "cvr": "65038617",
            "bill_to_address": "Amager Strandvej, 124A",
            "bill_to_postal_code": "2300",
            "bill_to_city": "K\u00f8benhavn S",
            "requisition_no": "Elin Riis-Sloan",
            "bill_to_country": "DK",
            "quantity": "34",
            "expire_date": "14-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "giftwrap": "1",
            "contact_email": ""
        },
        "Timestamp": 1634203482,
        "check": {
            "check1": "1: BS60538 - 2021-10-14 11:25",
            "check2": "",
            "check3": "",
            "shopname": "Julegavekortet DK",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu, 14 Oct 2021 11:09:27 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "Salon Madonna ",
            "cvr": "33955472",
            "bill_to_address": "Bredgade 22",
            "bill_to_postal_code": "6800",
            "bill_to_city": "Varde",
            "requisition_no": "Heidi ",
            "bill_to_country": "DK",
            "quantity": "5",
            "expire_date": "07-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1634202567,
        "check": {
            "check1": "1: BS60530 - 2021-10-14 11:09",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu, 14 Oct 2021 10:59:54 +0200 (CEST)",
        "input": {
            "shop_id": "1981",
            "companyname": "Rune Nystr\u00f6m Aktiebolag",
            "cvr": "5560762428",
            "bill_to_address": "Larslund Bj\u00f6rkudden",
            "bill_to_postal_code": "61192",
            "bill_to_city": "Nyk\u00f6ping",
            "requisition_no": "Ingemar Nystr\u00f6m",
            "bill_to_country": "SE",
            "quantity": "5",
            "expire_date": "31-12-2022",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "SE",
            "contact_email": ""
        },
        "Timestamp": 1634201994,
        "check": {
            "check1": "1: BS60526 - 2021-10-14 11:00",
            "check2": "",
            "check3": "",
            "shopname": "24Julklappar800 (Sverige)",
            "lang": 5
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu, 14 Oct 2021 10:38:28 +0200 (CEST)",
        "input": {
            "shop_id": "575",
            "companyname": "Tranbjerg Varmev\u00e6rk A.m.b.a.",
            "cvr": "33016719",
            "bill_to_address": "Jegstrupv\u00e6nget 630",
            "bill_to_postal_code": "8310",
            "bill_to_city": "Tranbjerg J.",
            "requisition_no": "Thomas Haar Pedersen",
            "bill_to_country": "DK",
            "quantity": "11",
            "expire_date": "14-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "giftwrap": "1",
            "contact_email": ""
        },
        "Timestamp": 1634200708,
        "check": {
            "check1": "1: BS60518 - 2021-10-14 10:40",
            "check2": "",
            "check3": "",
            "shopname": "Designjulegaven",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field requisition_no: string must be longer than 30 characters",
            "field": "requisition_no",
            "type": "toolong",
            "min": "30",
            "max": "",
            "length": ""
        },
        "Date": " Thu, 14 Oct 2021 10:31:45 +0200 (CEST)",
        "input": {
            "shop_id": "310",
            "companyname": "SKOV REJSER ApS",
            "cvr": "35476237",
            "phone": "21922593",
            "contact_name": "Skov Rejser ApS (v.Henrik Skov)",
            "contact_phone": "21922593",
            "contact_email": "henrikskov@henrikskov.com",
            "bill_to_address": "Kongensgade 31B, 1 sal (WorkHaus)",
            "bill_to_postal_code": "5000",
            "bill_to_city": "Odense C",
            "requisition_no": "Skov Rejser ApS (v.Henrik Skov)",
            "bill_to_country": "DK",
            "quantity": "5",
            "expire_date": "07-11-2021",
            "is_email": "0",
            "use_shipping_address": "1",
            "ship_to_company": "SKOV REJSER ApS",
            "ship_to_address": "Kongensgade 31B, 1 sal (WorkHaus)",
            "ship_to_address_2": "Kongensgade 31B, 1 sal (WorkHaus)",
            "ship_to_postal_code": "5000",
            "ship_to_city": "Odense C",
            "ship_to_country": "DK",
            "giftwrap": "1"
        },
        "Timestamp": 1634200305,
        "check": {
            "check1": "",
            "check2": "",
            "check3": "",
            "shopname": "Dr\u00f8mmegavekortet 300",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field companyname: value is required",
            "field": "companyname",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu, 14 Oct 2021 10:30:55 +0200 (CEST)",
        "input": {
            "shop_id": "310",
            "bill_to_country": "DK",
            "quantity": "5",
            "expire_date": "07-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "giftwrap": "1",
            "cvr": "",
            "contact_email": ""
        },
        "Timestamp": 1634200255,
        "check": {
            "check1": "*",
            "check2": "*",
            "check3": "*",
            "shopname": "Dr\u00f8mmegavekortet 300",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field requisition_no: string must be longer than 30 characters",
            "field": "requisition_no",
            "type": "toolong",
            "min": "30",
            "max": "",
            "length": ""
        },
        "Date": " Thu, 14 Oct 2021 10:30:39 +0200 (CEST)",
        "input": {
            "shop_id": "310",
            "companyname": "SKOV REJSER ApS",
            "cvr": "35476237",
            "phone": "21922593",
            "contact_name": "Henrik Skov",
            "contact_phone": "21922593",
            "contact_email": "henrikskov@henrikskov.com",
            "bill_to_address": "Kongensgade 31B, 1 sal (WorkHaus)",
            "bill_to_postal_code": "5000",
            "bill_to_city": "Odense C",
            "requisition_no": "Skov Rejser ApS (v.Henrik Skov)",
            "bill_to_country": "DK",
            "quantity": "5",
            "expire_date": "07-11-2021",
            "is_email": "0",
            "use_shipping_address": "1",
            "ship_to_company": "Skov Rejser ApS",
            "ship_to_address": "Kongensgade 31B, 1 sal (WorkHaus)",
            "ship_to_address_2": "Kongensgade 31B, 1 sal (WorkHaus)",
            "ship_to_postal_code": "5000",
            "ship_to_city": "Odense",
            "ship_to_country": "DK",
            "giftwrap": "1"
        },
        "Timestamp": 1634200239,
        "check": {
            "check1": "",
            "check2": "",
            "check3": "",
            "shopname": "Dr\u00f8mmegavekortet 300",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field companyname: string must be longer than 50 characters",
            "field": "companyname",
            "type": "toolong",
            "min": "50",
            "max": "",
            "length": ""
        },
        "Date": " Thu, 14 Oct 2021 10:22:20 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "GENERAL REINSURANCE COPENHAGEN BRANCH FILIAL AF GENERAL REINSURANCE AG TYSKLAND",
            "cvr": "31851629",
            "phone": "40825287",
            "contact_name": "Kirsten Langl\u00f8kke",
            "contact_phone": "40825287",
            "contact_email": "langloek@genre.com",
            "bill_to_address": "Weidekampsgade 14 A",
            "bill_to_postal_code": "2300",
            "bill_to_city": "K\u00f8benhavn S",
            "requisition_no": "Kirsten Langl\u00f8kke",
            "bill_to_country": "DK",
            "quantity": "6",
            "expire_date": "07-11-2021",
            "is_email": "1",
            "use_shipping_address": "1",
            "ship_to_company": "Gen Re",
            "ship_to_address": "Weidekampsgade 14 A, 2. th",
            "ship_to_address_2": "Weidekampsgade 14 A, 2. th",
            "ship_to_postal_code": "2300",
            "ship_to_city": "K\u00f8benhavn S",
            "ship_to_country": "DK",
            "giftwrap": "1"
        },
        "Timestamp": 1634199740,
        "check": {
            "check1": "",
            "check2": "1: BS60511 - 2021-10-14 10:23",
            "check3": "",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu, 14 Oct 2021 10:13:34 +0200 (CEST)",
        "input": {
            "shop_id": "53",
            "companyname": "Louis Nielsen kalundborg ApS",
            "cvr": "31946646",
            "phone": "30783043",
            "contact_phone": "30783043",
            "contact_email": "mkh@louisnielsen.dk",
            "bill_to_address": "Kordilgade 30",
            "bill_to_postal_code": "4400",
            "bill_to_city": "Kalundborg",
            "requisition_no": "Michael Hansen",
            "bill_to_country": "DK",
            "quantity": "5",
            "expire_date": "21-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK"
        },
        "Timestamp": 1634199214,
        "check": {
            "check1": "1: BS60508 - 2021-10-14 10:14",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekortet DK 800",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu, 14 Oct 2021 10:05:40 +0200 (CEST)",
        "input": {
            "shop_id": "52",
            "companyname": "L\u00e6gerne Finne, Riise og AAbenhus",
            "cvr": "38129333",
            "bill_to_address": "Vesterbrogade 74, 2 sal",
            "bill_to_postal_code": "1620",
            "bill_to_city": "K\u00f8benhavn V",
            "requisition_no": "Louise Finne",
            "bill_to_country": "DK",
            "quantity": "20",
            "expire_date": "14-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "giftwrap": "1",
            "contact_email": ""
        },
        "Timestamp": 1634198740,
        "check": {
            "check1": "1: BS60503 - 2021-10-14 10:06",
            "check2": "",
            "check3": "",
            "shopname": "Julegavekortet DK",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_email: not a valid e-mail",
            "field": "contact_email",
            "type": "invalidemail",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu, 14 Oct 2021 09:27:58 +0200 (CEST)",
        "input": {
            "shop_id": "2550",
            "companyname": "Waldemar Ellefsen AS",
            "cvr": "911508443",
            "phone": "22743232",
            "contact_name": "Samme",
            "contact_phone": "22743232",
            "contact_email": "Samme",
            "bill_to_address": "Rosenholmveien 4B",
            "bill_to_postal_code": "1252",
            "bill_to_city": "Oslo",
            "requisition_no": "Mia Andersson",
            "bill_to_country": "NO",
            "bill_to_email": "mia.andersson@we.no",
            "quantity": "10",
            "expire_date": "07-11-2021",
            "is_email": "1",
            "use_shipping_address": "1",
            "ship_to_company": "Samme",
            "ship_to_address": "Samme",
            "ship_to_address_2": "Samme",
            "ship_to_postal_code": "Samme",
            "ship_to_city": "Samme",
            "ship_to_country": "NO"
        },
        "Timestamp": 1634196478,
        "check": {
            "check1": "",
            "check2": "",
            "check3": "2: BS60494 - 2021-10-14 09:28",
            "shopname": "Guldgavekort 1200 (NO)",
            "lang": 4
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field requisition_no: string must be longer than 30 characters",
            "field": "requisition_no",
            "type": "toolong",
            "min": "30",
            "max": "",
            "length": ""
        },
        "Date": " Thu, 14 Oct 2021 09:25:25 +0200 (CEST)",
        "input": {
            "shop_id": "310",
            "companyname": "L\u00e6gerne Rasmussen og Jensen",
            "cvr": "38063936",
            "phone": "56659040",
            "contact_name": "mie jensen",
            "contact_phone": "56659040",
            "contact_email": "miej2501@gmail.com",
            "bill_to_address": "R\u00e5dhusstr\u00e6de 5a 2. sal",
            "bill_to_postal_code": "4600",
            "bill_to_city": "K\u00f8ge",
            "requisition_no": "mie jensen\/ l\u00e6gerne rasmussen og jensen",
            "bill_to_country": "DK",
            "quantity": "5",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "1",
            "ship_to_company": "L\u00e6gerne Rasmussen og Jensen",
            "ship_to_address": "R\u00e5dhusstr\u00e6de 5a 2. sal",
            "ship_to_address_2": "R\u00e5dhusstr\u00e6de 5a 2. sal",
            "ship_to_postal_code": "4600",
            "ship_to_city": "K\u00f8ge",
            "ship_to_country": "DK"
        },
        "Timestamp": 1634196325,
        "check": {
            "check1": "",
            "check2": "1: BS60495 - 2021-10-14 09:30",
            "check3": "1: BS60491 - 2021-10-14 09:20",
            "shopname": "Dr\u00f8mmegavekortet 300",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu, 14 Oct 2021 09:05:31 +0200 (CEST)",
        "input": {
            "shop_id": "1981",
            "companyname": "AB Hallstahem",
            "cvr": "5560464421",
            "phone": "0707122687",
            "contact_phone": "0707122687",
            "contact_email": "lina.andersson@hallstahem.se",
            "bill_to_address": "Box 64",
            "bill_to_postal_code": "73422",
            "bill_to_city": "Hallstahammar",
            "requisition_no": "Lina Andersson",
            "bill_to_country": "SE",
            "quantity": "50",
            "expire_date": "31-12-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "SE"
        },
        "Timestamp": 1634195131,
        "check": {
            "check1": "1: BS60485 - 2021-10-14 09:06",
            "check2": "",
            "check3": "",
            "shopname": "24Julklappar800 (Sverige)",
            "lang": 5
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu, 14 Oct 2021 08:52:00 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "Intersurgical Danmark",
            "cvr": "36966289",
            "bill_to_address": "R\u00e5dhustorvet 7, 1",
            "bill_to_postal_code": "3520",
            "bill_to_city": "Farum",
            "requisition_no": "Jette Englund",
            "bill_to_country": "DK",
            "quantity": "5",
            "expire_date": "31-12-2021",
            "is_email": "0",
            "use_shipping_address": "1",
            "ship_to_company": "Intersurgical Danmark",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1634194320,
        "check": {
            "check1": "1: BS60480 - 2021-10-14 08:53",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu, 14 Oct 2021 08:37:38 +0200 (CEST)",
        "input": {
            "shop_id": "2550",
            "companyname": "Directa Skiptvet AS",
            "cvr": "912157660",
            "bill_to_address": "Storveien 12",
            "bill_to_postal_code": "1816",
            "bill_to_city": "Skiptvet",
            "requisition_no": "Skiptvet",
            "bill_to_country": "NO",
            "bill_to_email": "ski@ebilag.directa.no",
            "quantity": "6",
            "expire_date": "21-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "NO",
            "giftwrap": "1",
            "contact_email": ""
        },
        "Timestamp": 1634193458,
        "check": {
            "check1": "1: BS60475 - 2021-10-14 08:38",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekort 1200 (NO)",
            "lang": 4
        }
    },
    {
        "output": {
            "status": "30",
            "error": "Could not find valid shop_id"
        },
        "Date": " Thu, 14 Oct 2021 08:10:12 +0200 (CEST)",
        "input": {
            "companyname": "\u00d8lholm A\/S",
            "cvr": "47475910",
            "phone": "64411166",
            "contact_name": "Claus \u00d8lholm",
            "contact_phone": "64411166",
            "contact_email": "claus@olholm-sko.dk",
            "bill_to_address": "Lollandsvej 29",
            "bill_to_postal_code": "5500",
            "bill_to_city": "Middelfart",
            "requisition_no": "Claus \u00d8lholm",
            "bill_to_country": "DK",
            "quantity": "6",
            "expire_date": "31-12-2021",
            "is_email": "0",
            "use_shipping_address": "1",
            "ship_to_company": "\u00d8lholm A\/S",
            "ship_to_address": "Lollandsvej 29",
            "ship_to_address_2": "Lollandsvej 29",
            "ship_to_postal_code": "5500",
            "ship_to_city": "Middelfart",
            "ship_to_country": "DK",
            "giftwrap": "1",
            "shop_id": ""
        },
        "Timestamp": 1634191812,
        "check": {
            "check1": "",
            "check2": "",
            "check3": "1: BS60472 - 2021-10-14 08:10",
            "shopname": "Ukendt",
            "lang": ""
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu, 14 Oct 2021 07:44:37 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "L\u00e6gerne Agertoft og Petersen ",
            "cvr": "19979512",
            "bill_to_address": "Skt Anne Plads 4, st",
            "bill_to_postal_code": "5000",
            "bill_to_city": "Odense C ",
            "requisition_no": "Hanne Petersen ",
            "bill_to_country": "DK",
            "quantity": "8",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1634190277,
        "check": {
            "check1": "1: BS60469 - 2021-10-14 07:45",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed, 13 Oct 2021 22:56:47 +0200 (CEST)",
        "input": {
            "shop_id": "53",
            "companyname": "SFbyg ApS",
            "cvr": "42518980",
            "contact_email": "sfbyg.firma@gmail.com",
            "bill_to_address": "\u00d8stervang 8",
            "bill_to_postal_code": "8380",
            "bill_to_city": "Trige",
            "requisition_no": "Saulius",
            "bill_to_country": "DK",
            "quantity": "5",
            "expire_date": "07-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK"
        },
        "Timestamp": 1634158607,
        "check": {
            "check1": "1: BS60463 - 2021-10-13 22:58",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekortet DK 800",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed, 13 Oct 2021 19:44:58 +0200 (CEST)",
        "input": {
            "shop_id": "290",
            "companyname": "F\u00e6llesinstitutionen Nustrup-Sommersted",
            "cvr": "29189757",
            "ean": "5798005260660",
            "bill_to_address": "Marie Skausvej 5",
            "bill_to_postal_code": "6560",
            "bill_to_city": "sommersted",
            "requisition_no": "Jeanette Ravn Petersen",
            "bill_to_country": "DK",
            "quantity": "20",
            "expire_date": "07-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1634147098,
        "check": {
            "check1": "1: BS60455 - 2021-10-13 19:45",
            "check2": "",
            "check3": "2: BS58554 - 2021-09-30 12:03",
            "shopname": "Dr\u00f8mmegavekortet 200",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed, 13 Oct 2021 19:34:17 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "Solsikken",
            "cvr": "40785639",
            "bill_to_address": "Storegade 23",
            "bill_to_postal_code": "6780",
            "bill_to_city": "Sk\u00e6rb\u00e6k",
            "requisition_no": "Birgitte dam",
            "bill_to_country": "DK",
            "quantity": "5",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1634146457,
        "check": {
            "check1": "1: BS60454 - 2021-10-13 19:35",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field companyname: string must be longer than 50 characters",
            "field": "companyname",
            "type": "toolong",
            "min": "50",
            "max": "",
            "length": ""
        },
        "Date": " Wed, 13 Oct 2021 19:23:11 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "GENERAL REINSURANCE COPENHAGEN BRANCH FILIAL AF GENERAL REINSURANCE AG TYSKLAND",
            "cvr": "31851629",
            "phone": "40825287",
            "contact_name": "Kirsten Langl\u00f8kke",
            "contact_phone": "40825287",
            "contact_email": "langloek@genre.com",
            "bill_to_address": "Weidekampsgade 14 A",
            "bill_to_postal_code": "2300",
            "bill_to_city": "K\u00f8benhavn S",
            "requisition_no": "Kirsten Langl\u00f8kke",
            "bill_to_country": "DK",
            "quantity": "6",
            "expire_date": "07-11-2021",
            "is_email": "1",
            "use_shipping_address": "1",
            "ship_to_company": "GENERAL REINSURANCE COPENHAGEN BRANCH FILIAL AF GENERAL REINSURANCE AG TYSKLAND",
            "ship_to_address": "Weidekampsgade 14 A, 2. sal th",
            "ship_to_address_2": "Weidekampsgade 14 A, 2. sal th",
            "ship_to_postal_code": "2300",
            "ship_to_city": "K\u00f8benhavn S",
            "ship_to_country": "DK"
        },
        "Timestamp": 1634145791,
        "check": {
            "check1": "",
            "check2": "1: BS60511 - 2021-10-14 10:23",
            "check3": "",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed, 13 Oct 2021 16:37:24 +0200 (CEST)",
        "input": {
            "shop_id": "52",
            "companyname": "Klippehuset",
            "cvr": "21870838",
            "bill_to_address": "Hobrovej 181",
            "bill_to_postal_code": "8920",
            "bill_to_city": "Randers NV",
            "requisition_no": "Janni Hastrup",
            "bill_to_country": "DK",
            "quantity": "8",
            "expire_date": "31-10-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "giftwrap": "1",
            "contact_email": ""
        },
        "Timestamp": 1634135844,
        "check": {
            "check1": "1: BS60452 - 2021-10-13 16:45",
            "check2": "",
            "check3": "",
            "shopname": "Julegavekortet DK",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field companyname: value is required",
            "field": "companyname",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed, 13 Oct 2021 16:22:27 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "phone": "31901463",
            "contact_name": "lasse nielsen",
            "contact_phone": "31901463",
            "contact_email": "lassepnielsen@hotmail.dk",
            "bill_to_address": "musikbyen 3",
            "bill_to_postal_code": "4573",
            "bill_to_city": "H\u00f8jby",
            "bill_to_country": "DK",
            "quantity": "1",
            "expire_date": "21-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_address": "musikbyen 3",
            "ship_to_address_2": "musikbyen 3",
            "ship_to_postal_code": "4573",
            "ship_to_city": "H\u00f8jby",
            "ship_to_country": "DK",
            "cvr": ""
        },
        "Timestamp": 1634134947,
        "check": {
            "check1": "",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed, 13 Oct 2021 15:36:45 +0200 (CEST)",
        "input": {
            "shop_id": "54",
            "companyname": "Bukam Entreprise ApS",
            "cvr": "38693891",
            "ean": "5797200015914",
            "phone": "41852301",
            "contact_phone": "41852301",
            "contact_email": "alb@bukam.dk",
            "bill_to_address": "M\u00e5de Industrivej 27",
            "bill_to_postal_code": "6705",
            "bill_to_city": "Esbjerg \u00d8",
            "requisition_no": "Anna Lise Buch",
            "bill_to_country": "DK",
            "quantity": "30",
            "expire_date": "07-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK"
        },
        "Timestamp": 1634132205,
        "check": {
            "check1": "1: BS60439 - 2021-10-13 15:37",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 400",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed, 13 Oct 2021 15:24:57 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "Michael Lundbech A\/S",
            "cvr": "26473756",
            "bill_to_address": "Bragesvej 5",
            "bill_to_postal_code": "4100",
            "bill_to_city": "Ringsted",
            "requisition_no": "CL",
            "bill_to_country": "DK",
            "quantity": "32",
            "expire_date": "01-04-2022",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1634131497,
        "check": {
            "check1": "1: BS60441 - 2021-10-13 15:38",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed, 13 Oct 2021 15:23:00 +0200 (CEST)",
        "input": {
            "shop_id": "58",
            "companyname": "Pro-Consult Innlandet AS",
            "cvr": "919061944",
            "bill_to_address": "Skogvegen 41",
            "bill_to_postal_code": "2318",
            "bill_to_city": "HAMAR",
            "requisition_no": "Julegaver\/Roger Johannessen",
            "bill_to_country": "NO",
            "bill_to_email": "innlandet@pro-consult.as",
            "quantity": "12",
            "expire_date": "07-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "NO",
            "giftwrap": "1",
            "contact_email": ""
        },
        "Timestamp": 1634131380,
        "check": {
            "check1": "1: BS60427 - 2021-10-13 15:23",
            "check2": "",
            "check3": "",
            "shopname": "Julegavekortet NO 600",
            "lang": 4
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed, 13 Oct 2021 15:13:48 +0200 (CEST)",
        "input": {
            "shop_id": "575",
            "companyname": "L\u00e6gehuset i Vinderup",
            "cvr": "34850399",
            "bill_to_address": "Gr\u00f8nningen 5",
            "bill_to_postal_code": "7830",
            "bill_to_city": "Vinderup",
            "requisition_no": "Karen Lise Lorentsen",
            "bill_to_country": "DK",
            "quantity": "16",
            "expire_date": "14-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "giftwrap": "1",
            "contact_email": ""
        },
        "Timestamp": 1634130828,
        "check": {
            "check1": "1: BS60420 - 2021-10-13 15:14",
            "check2": "",
            "check3": "",
            "shopname": "Designjulegaven",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed, 13 Oct 2021 15:12:13 +0200 (CEST)",
        "input": {
            "shop_id": "52",
            "companyname": "HTH Svendborg A\/S",
            "cvr": "40826580",
            "bill_to_address": "Vestergade 157",
            "bill_to_postal_code": "5700",
            "bill_to_city": "Svendborg",
            "requisition_no": "Ralph",
            "bill_to_country": "DK",
            "quantity": "14",
            "expire_date": "31-12-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1634130733,
        "check": {
            "check1": "1: BS60418 - 2021-10-13 15:12",
            "check2": "",
            "check3": "",
            "shopname": "Julegavekortet DK",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed, 13 Oct 2021 15:10:24 +0200 (CEST)",
        "input": {
            "shop_id": "1832",
            "companyname": "Tr\u00e4dg\u00e5rdsgatansconditori AB",
            "cvr": "5568709439",
            "bill_to_address": "Pr\u00e4stgatan 8c",
            "bill_to_postal_code": "26131",
            "bill_to_city": "Landskrona ",
            "requisition_no": "Zandra",
            "bill_to_country": "SE",
            "quantity": "5",
            "expire_date": "31-12-2022",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "SE",
            "giftwrap": "1",
            "contact_email": ""
        },
        "Timestamp": 1634130624,
        "check": {
            "check1": "1: BS60413 - 2021-10-13 15:11",
            "check2": "",
            "check3": "",
            "shopname": "24Julklappar400 (Sverige)",
            "lang": 5
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed, 13 Oct 2021 15:04:04 +0200 (CEST)",
        "input": {
            "shop_id": "574",
            "companyname": "Trade Tech AS",
            "cvr": "983 923 313",
            "bill_to_address": "Strandsvingen 4",
            "bill_to_postal_code": "4032",
            "bill_to_city": "Stavanger",
            "requisition_no": "Avd 9, Julegaver",
            "bill_to_country": "NO",
            "bill_to_email": "post@tradetech.no",
            "quantity": "25",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "NO",
            "contact_email": ""
        },
        "Timestamp": 1634130244,
        "check": {
            "check1": "1: BS60409 - 2021-10-13 15:05",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekort NO",
            "lang": 4
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed, 13 Oct 2021 14:55:20 +0200 (CEST)",
        "input": {
            "shop_id": "2558",
            "companyname": "Noaks Ark Stockholm",
            "cvr": "802454-3517",
            "bill_to_address": "Eriksbergsgatan 46",
            "bill_to_postal_code": "114 30",
            "bill_to_city": "Stockholm",
            "requisition_no": "RE15",
            "bill_to_country": "SE",
            "quantity": "22",
            "expire_date": "31-12-2022",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "SE",
            "contact_email": ""
        },
        "Timestamp": 1634129720,
        "check": {
            "check1": "1: BS60407 - 2021-10-13 14:55",
            "check2": "",
            "check3": "",
            "shopname": "24 julklappar 1200 2021",
            "lang": 5
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed, 13 Oct 2021 14:46:24 +0200 (CEST)",
        "input": {
            "shop_id": "574",
            "companyname": "PrivatMegleren Moss",
            "cvr": "995730359",
            "bill_to_address": "Welhavens gate 2A",
            "bill_to_postal_code": "1530",
            "bill_to_city": "Moss",
            "requisition_no": "Nina Skaanes",
            "bill_to_country": "NO",
            "bill_to_email": "line.haugen@privatmegleren.no",
            "quantity": "9",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "NO",
            "giftwrap": "1",
            "contact_email": ""
        },
        "Timestamp": 1634129184,
        "check": {
            "check1": "1: BS60404 - 2021-10-13 14:47",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekort NO",
            "lang": 4
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field expire_date: value is required",
            "field": "expire_date",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed, 13 Oct 2021 14:45:12 +0200 (CEST)",
        "input": {
            "shop_id": "2395",
            "companyname": "Circular plastic Systems",
            "cvr": "41250062",
            "phone": "93301204",
            "contact_name": "michael hassenkam",
            "contact_phone": "93301204",
            "contact_email": "michael@circularplasticsystems.com",
            "bill_to_address": "havnevej 10",
            "bill_to_postal_code": "3300",
            "bill_to_city": "Frederiksv\u00e6rk",
            "requisition_no": "michael hassenkam",
            "bill_to_country": "DK",
            "quantity": "6",
            "is_email": "0",
            "use_shipping_address": "1",
            "ship_to_company": "Circular plastic Systems",
            "ship_to_address": "havnevej 10",
            "ship_to_address_2": "havnevej 10",
            "ship_to_postal_code": "3300",
            "ship_to_city": "Frederiksv\u00e6rk",
            "ship_to_country": "DK",
            "giftwrap": "1"
        },
        "Timestamp": 1634129112,
        "check": {
            "check1": "1: BS60402 - 2021-10-13 14:45",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekortet DK 960",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field cvr: value is required",
            "field": "cvr",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed, 13 Oct 2021 14:43:25 +0200 (CEST)",
        "input": {
            "shop_id": "54",
            "companyname": "Vejle Kommune, Familieplejen",
            "bill_to_address": "Nyboesgade 35",
            "bill_to_postal_code": "7100",
            "bill_to_city": "Vejle",
            "requisition_no": "Inger Christensen",
            "bill_to_country": "DK",
            "quantity": "11",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "giftwrap": "1",
            "cvr": "",
            "contact_email": ""
        },
        "Timestamp": 1634129005,
        "check": {
            "check1": "*",
            "check2": "*",
            "check3": "*",
            "shopname": "24 Gaver DK 400",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field ean: value must be at least 1000000000000",
            "field": "ean",
            "type": "lowvalue",
            "min": "1000000000000",
            "max": "",
            "length": ""
        },
        "Date": " Wed, 13 Oct 2021 14:42:37 +0200 (CEST)",
        "input": {
            "shop_id": "54",
            "companyname": "Vejle Kommune, Familieplejen",
            "cvr": "29189900",
            "ean": "579 800 636 3",
            "phone": "76815695",
            "contact_name": "Inger Christensen",
            "contact_phone": "76815695",
            "contact_email": "inmac@vejle.dk",
            "bill_to_address": "Nyboesgade 35 1. th.",
            "bill_to_postal_code": "7100",
            "bill_to_city": "Vejle",
            "requisition_no": "Inger Christensen",
            "bill_to_country": "DK",
            "quantity": "11",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "1",
            "ship_to_company": "Familieplejen Vejle Kommune",
            "ship_to_address": "Nyboesgade 35",
            "ship_to_address_2": "Nyboesgade 35",
            "ship_to_postal_code": "7100",
            "ship_to_city": "Vejle",
            "ship_to_country": "DK",
            "giftwrap": "1"
        },
        "Timestamp": 1634128957,
        "check": {
            "check1": "1: BS60401 - 2021-10-13 14:44",
            "check2": "",
            "check3": "12: BS56274 - 2021-09-06 08:56",
            "shopname": "24 Gaver DK 400",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed, 13 Oct 2021 14:29:16 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "L\u00e6gehuset Smidstrupvej",
            "cvr": "26506069",
            "bill_to_address": "Smidstrupvej 7",
            "bill_to_postal_code": "4250",
            "bill_to_city": "Fuglebjerg",
            "bill_to_country": "DK",
            "quantity": "20",
            "expire_date": "07-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "giftwrap": "1",
            "contact_email": ""
        },
        "Timestamp": 1634128156,
        "check": {
            "check1": "",
            "check2": "1: BS60501 - 2021-10-14 09:54",
            "check3": "",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed, 13 Oct 2021 14:19:25 +0200 (CEST)",
        "input": {
            "shop_id": "2395",
            "companyname": "Byggeriets Kvalitetskontrol",
            "cvr": "25989287",
            "bill_to_address": "\u00c5dalen 13 A, 1. sal",
            "bill_to_postal_code": "6600",
            "bill_to_city": "Vejen",
            "requisition_no": "Susanne Vase",
            "bill_to_country": "DK",
            "quantity": "12",
            "expire_date": "21-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1634127565,
        "check": {
            "check1": "1: BS60395 - 2021-10-13 14:20",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekortet DK 960",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed, 13 Oct 2021 14:12:37 +0200 (CEST)",
        "input": {
            "shop_id": "52",
            "companyname": "solstr\u00e5len privat b\u00f8rnehave",
            "cvr": "34757720",
            "ean": "5790002186370",
            "contact_email": "info@solstraalen.net",
            "bill_to_address": "uls\u00f8parken 3",
            "bill_to_postal_code": "2660",
            "bill_to_city": "br\u00f8ndby strand",
            "requisition_no": "Sultan Kafa",
            "bill_to_country": "DK",
            "quantity": "6",
            "expire_date": "14-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK"
        },
        "Timestamp": 1634127157,
        "check": {
            "check1": "1: BS60392 - 2021-10-13 14:18",
            "check2": "",
            "check3": "",
            "shopname": "Julegavekortet DK",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field companyname: string must be longer than 50 characters",
            "field": "companyname",
            "type": "toolong",
            "min": "50",
            "max": "",
            "length": ""
        },
        "Date": " Wed, 13 Oct 2021 13:59:29 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "GENERAL REINSURANCE COPENHAGEN BRANCH FILIAL AF GENERAL REINSURANCE AG TYSKLAND",
            "cvr": "31851629",
            "phone": "40825287",
            "contact_name": "Kirsten Langl\u00f8kke",
            "contact_phone": "40825287",
            "contact_email": "langloek@genre.com",
            "bill_to_address": "Weidekampsgade 14 A",
            "bill_to_postal_code": "2300",
            "bill_to_city": "K\u00f8benhavn S",
            "requisition_no": "Kirsten Langl\u00f8kke",
            "bill_to_country": "DK",
            "quantity": "6",
            "expire_date": "07-11-2021",
            "is_email": "1",
            "use_shipping_address": "1",
            "ship_to_company": "GENERAL REINSURANCE COPENHAGEN BRANCH FILIAL AF GENERAL REINSURANCE AG TYSKLAND",
            "ship_to_address": "Weidekampsgade 14 A, 2. sal th",
            "ship_to_address_2": "Weidekampsgade 14 A, 2. sal th",
            "ship_to_postal_code": "2300",
            "ship_to_city": "K\u00f8benhavn S",
            "ship_to_country": "DK"
        },
        "Timestamp": 1634126369,
        "check": {
            "check1": "",
            "check2": "1: BS60511 - 2021-10-14 10:23",
            "check3": "",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed, 13 Oct 2021 13:54:47 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "Puratos A\/S",
            "cvr": "36851813",
            "phone": "30664334",
            "contact_phone": "30664334",
            "contact_email": "cvium@puratos.com",
            "bill_to_address": "Lysholt All\u00e9 3",
            "bill_to_postal_code": "7100",
            "bill_to_city": "Vejle",
            "requisition_no": "Camilla Koustrup Vium",
            "bill_to_country": "DK",
            "quantity": "10",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK"
        },
        "Timestamp": 1634126087,
        "check": {
            "check1": "1: BS60385 - 2021-10-13 13:55",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed, 13 Oct 2021 13:46:40 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "Bott Danmark A\/S",
            "cvr": "35851313",
            "bill_to_address": "Teknikervej 12-14",
            "bill_to_postal_code": "7000",
            "bill_to_city": "Fredericia",
            "requisition_no": "Pia J\u00f8rgensen",
            "bill_to_country": "DK",
            "quantity": "13",
            "expire_date": "07-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1634125600,
        "check": {
            "check1": "1: BS60383 - 2021-10-13 13:47",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed, 13 Oct 2021 13:41:56 +0200 (CEST)",
        "input": {
            "shop_id": "310",
            "companyname": "PHD Regnskab",
            "cvr": "40938524",
            "bill_to_address": "Duevej 23",
            "bill_to_postal_code": "2600",
            "bill_to_city": "Glostrup",
            "requisition_no": "Julegaver",
            "bill_to_country": "DK",
            "quantity": "6",
            "expire_date": "07-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1634125316,
        "check": {
            "check1": "1: BS60381 - 2021-10-13 13:42",
            "check2": "",
            "check3": "",
            "shopname": "Dr\u00f8mmegavekortet 300",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed, 13 Oct 2021 13:12:53 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "Inropa A\/S",
            "cvr": "26749565",
            "bill_to_address": "Gasv\u00e6rksvej 5",
            "bill_to_postal_code": "9000",
            "bill_to_city": "Aalborg",
            "requisition_no": "Julegaver 2021",
            "bill_to_country": "DK",
            "quantity": "10",
            "expire_date": "21-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "giftwrap": "1",
            "contact_email": ""
        },
        "Timestamp": 1634123573,
        "check": {
            "check1": "1: BS60378 - 2021-10-13 13:13",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed, 13 Oct 2021 13:07:39 +0200 (CEST)",
        "input": {
            "shop_id": "290",
            "companyname": "Gesten B\u00f8rnecenter",
            "cvr": "29189838",
            "ean": "5798005403500",
            "bill_to_address": "Stadion All\u00e9 2",
            "bill_to_postal_code": "6621",
            "bill_to_city": "Gesten",
            "requisition_no": "Karen Bohlbro",
            "bill_to_country": "DK",
            "quantity": "29",
            "expire_date": "21-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "giftwrap": "1",
            "contact_email": ""
        },
        "Timestamp": 1634123259,
        "check": {
            "check1": "1: BS60377 - 2021-10-13 13:08",
            "check2": "2: BS58019 - 2021-09-27 13:44",
            "check3": "3: BS56778 - 2021-09-10 10:09",
            "shopname": "Dr\u00f8mmegavekortet 200",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed, 13 Oct 2021 13:03:01 +0200 (CEST)",
        "input": {
            "shop_id": "2550",
            "companyname": "Nero Security AS",
            "cvr": "892827672",
            "bill_to_address": "Garderbakken 1",
            "bill_to_postal_code": "1900",
            "bill_to_city": "Fetsund",
            "requisition_no": "Rune Bartnes",
            "bill_to_country": "NO",
            "bill_to_email": "rune.bartnes@verisurepartner.no",
            "quantity": "12",
            "expire_date": "07-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "NO",
            "giftwrap": "1",
            "contact_email": ""
        },
        "Timestamp": 1634122981,
        "check": {
            "check1": "1: BS60376 - 2021-10-13 13:04",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekort 1200 (NO)",
            "lang": 4
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed, 13 Oct 2021 12:59:07 +0200 (CEST)",
        "input": {
            "shop_id": "575",
            "companyname": "FOF Nordsj\u00e6lland",
            "cvr": "14665919",
            "bill_to_address": "Ordrupvej 60",
            "bill_to_postal_code": "2920",
            "bill_to_city": "Charlottenlund",
            "requisition_no": "Helle Nielsen ",
            "bill_to_country": "DK",
            "quantity": "10",
            "expire_date": "14-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1634122747,
        "check": {
            "check1": "1: BS60375 - 2021-10-13 13:00",
            "check2": "",
            "check3": "",
            "shopname": "Designjulegaven",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed, 13 Oct 2021 12:29:51 +0200 (CEST)",
        "input": {
            "shop_id": "53",
            "companyname": "Ljungdahl A\/S",
            "cvr": "42421618",
            "bill_to_address": "Hejrevang 22, ",
            "bill_to_postal_code": "3450",
            "bill_to_city": "Aller\u00f8d",
            "requisition_no": "Lene Olsen",
            "bill_to_country": "DK",
            "quantity": "14",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "giftwrap": "1",
            "contact_email": ""
        },
        "Timestamp": 1634120991,
        "check": {
            "check1": "1: BS60362 - 2021-10-13 12:30",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekortet DK 800",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed, 13 Oct 2021 12:10:53 +0200 (CEST)",
        "input": {
            "shop_id": "2395",
            "companyname": "Goertek Europe ApS",
            "cvr": "36712589",
            "bill_to_address": "Falkoner Alle 1 3,",
            "bill_to_postal_code": "2000",
            "bill_to_city": "Frederiksberg",
            "requisition_no": "Stella Yi",
            "bill_to_country": "DK",
            "quantity": "5",
            "expire_date": "07-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1634119853,
        "check": {
            "check1": "1: BS60358 - 2021-10-13 12:14",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekortet DK 960",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed, 13 Oct 2021 12:04:53 +0200 (CEST)",
        "input": {
            "shop_id": "2558",
            "companyname": "Advanced Ekono AB",
            "cvr": "5569550931",
            "bill_to_address": "Kanngjutargr\u00e4nd 50",
            "bill_to_postal_code": "162 57",
            "bill_to_city": "Stockholm",
            "requisition_no": "Paula Karlsson",
            "bill_to_country": "SE",
            "quantity": "6",
            "expire_date": "31-12-2022",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "SE",
            "contact_email": ""
        },
        "Timestamp": 1634119493,
        "check": {
            "check1": "1: BS60357 - 2021-10-13 12:05",
            "check2": "",
            "check3": "",
            "shopname": "24 julklappar 1200 2021",
            "lang": 5
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed, 13 Oct 2021 11:45:59 +0200 (CEST)",
        "input": {
            "shop_id": "55",
            "companyname": "Admincom A\/S",
            "cvr": "26681758",
            "ean": "5790002044755",
            "bill_to_address": "Danmarksvej 26",
            "bill_to_postal_code": "8660",
            "bill_to_city": "Skanderborg",
            "requisition_no": "Mette Michelsen",
            "bill_to_country": "DK",
            "quantity": "9",
            "expire_date": "21-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1634118359,
        "check": {
            "check1": "1: BS60351 - 2021-10-13 11:46",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 560",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed, 13 Oct 2021 11:37:02 +0200 (CEST)",
        "input": {
            "shop_id": "2395",
            "companyname": "Heiko Stumbeck A\/S",
            "cvr": "21737046",
            "bill_to_address": "Klaregade 24",
            "bill_to_postal_code": "5000",
            "bill_to_city": "Odense C",
            "requisition_no": "Sidsel Stumbeck",
            "bill_to_country": "DK",
            "quantity": "30",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "giftwrap": "1",
            "contact_email": ""
        },
        "Timestamp": 1634117822,
        "check": {
            "check1": "1: BS60348 - 2021-10-13 11:38",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekortet DK 960",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed, 13 Oct 2021 11:34:59 +0200 (CEST)",
        "input": {
            "shop_id": "575",
            "companyname": "Malerfirma Thage W Nielsen A\/S",
            "cvr": "14041680",
            "bill_to_address": "Glarbjergvej 47",
            "bill_to_postal_code": "8920",
            "bill_to_city": "Randers NV",
            "requisition_no": "Lone Krogh Jensen",
            "bill_to_country": "DK",
            "quantity": "42",
            "expire_date": "31-12-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1634117699,
        "check": {
            "check1": "1: BS60347 - 2021-10-13 11:35",
            "check2": "",
            "check3": "",
            "shopname": "Designjulegaven",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed, 13 Oct 2021 11:20:56 +0200 (CEST)",
        "input": {
            "shop_id": "1832",
            "companyname": "\u00c5nge Elektriska AB",
            "cvr": "556230-7370",
            "bill_to_address": "Box 62",
            "bill_to_postal_code": "84121",
            "bill_to_city": "\u00c5nge",
            "requisition_no": "Greger och \u00c5ke",
            "bill_to_country": "SE",
            "quantity": "6",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "SE",
            "contact_email": ""
        },
        "Timestamp": 1634116856,
        "check": {
            "check1": "1: BS60340 - 2021-10-13 11:23",
            "check2": "",
            "check3": "",
            "shopname": "24Julklappar400 (Sverige)",
            "lang": 5
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed, 13 Oct 2021 11:10:53 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "FLYES EL-SERVICE A\/S",
            "cvr": "21840491",
            "bill_to_address": "Fabriksvej 28",
            "bill_to_postal_code": "3000",
            "bill_to_city": "Helsing\u00f8r",
            "requisition_no": "Att.: Dorthe St\u00e6hr Frederiksen",
            "bill_to_country": "DK",
            "quantity": "9",
            "expire_date": "21-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1634116253,
        "check": {
            "check1": "1: BS60334 - 2021-10-13 11:11",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed, 13 Oct 2021 11:06:58 +0200 (CEST)",
        "input": {
            "shop_id": "310",
            "companyname": "platang\u00e5rden",
            "cvr": "11259979",
            "ean": "5798009171528",
            "bill_to_address": "carit etlarsvej 17",
            "bill_to_postal_code": "1814",
            "bill_to_city": "frederiksberg c",
            "requisition_no": "Trine Rohrberg",
            "bill_to_country": "DK",
            "quantity": "5",
            "expire_date": "07-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1634116018,
        "check": {
            "check1": "1: BS60332 - 2021-10-13 11:07",
            "check2": "",
            "check3": "3: BS57425 - 2021-09-21 10:18",
            "shopname": "Dr\u00f8mmegavekortet 300",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed, 13 Oct 2021 11:04:00 +0200 (CEST)",
        "input": {
            "shop_id": "52",
            "companyname": "Fisker Skanderborg A\/S ",
            "cvr": "11757235",
            "bill_to_address": "Niels Bohrs Vej 16, Stilling",
            "bill_to_postal_code": "8660",
            "bill_to_city": "Skanderborg",
            "requisition_no": "Berit H\u00f8gh Rosenbeck",
            "bill_to_country": "DK",
            "quantity": "32",
            "expire_date": "14-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "giftwrap": "1",
            "contact_email": ""
        },
        "Timestamp": 1634115840,
        "check": {
            "check1": "1: BS60330 - 2021-10-13 11:04",
            "check2": "",
            "check3": "",
            "shopname": "Julegavekortet DK",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed, 13 Oct 2021 11:00:26 +0200 (CEST)",
        "input": {
            "shop_id": "1981",
            "companyname": "K\u00f6k&Bad G\u00e4vle AB",
            "cvr": "556631-5031",
            "bill_to_address": "V\u00e4xelgatan 1",
            "bill_to_postal_code": "802 91",
            "bill_to_city": "G\u00e4vle",
            "requisition_no": "Magnus Ohlsson",
            "bill_to_country": "SE",
            "quantity": "6",
            "expire_date": "31-12-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "SE",
            "contact_email": ""
        },
        "Timestamp": 1634115626,
        "check": {
            "check1": "1: BS60328 - 2021-10-13 11:02",
            "check2": "",
            "check3": "",
            "shopname": "24Julklappar800 (Sverige)",
            "lang": 5
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed, 13 Oct 2021 10:53:58 +0200 (CEST)",
        "input": {
            "shop_id": "2395",
            "companyname": "DASPOS A\/S",
            "cvr": "33050291",
            "bill_to_address": "Gammel Klausdalsbrovej 495",
            "bill_to_postal_code": "2730",
            "bill_to_city": "Herlev",
            "requisition_no": "Lars Gerner, Julegaver",
            "bill_to_country": "DK",
            "quantity": "8",
            "expire_date": "07-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1634115238,
        "check": {
            "check1": "1: BS60327 - 2021-10-13 10:54",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekortet DK 960",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed, 13 Oct 2021 10:51:00 +0200 (CEST)",
        "input": {
            "shop_id": "52",
            "companyname": "L\u00e6gerne Morten B\u00f8rupsgade ",
            "cvr": "8022 4516",
            "phone": "4272 0773 ",
            "contact_phone": "4272 0773 ",
            "contact_email": "lmbgade10@gmail.com",
            "bill_to_address": "Morten B\u00f8rupsgade 10, 2. ",
            "bill_to_postal_code": "8000",
            "bill_to_city": "\u00c5rhus C",
            "requisition_no": "Helga Have",
            "bill_to_country": "DK",
            "quantity": "10",
            "expire_date": "31-10-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK"
        },
        "Timestamp": 1634115060,
        "check": {
            "check1": "",
            "check2": "",
            "check3": "1: BS60323 - 2021-10-13 10:51",
            "shopname": "Julegavekortet DK",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed, 13 Oct 2021 10:49:14 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "Malernes Aktieselskab A\/S",
            "cvr": "21408301",
            "bill_to_address": "141 Teglv\u00e6nget",
            "bill_to_postal_code": "7400",
            "bill_to_city": "Herning",
            "requisition_no": "Michael Larsen",
            "bill_to_country": "DK",
            "quantity": "33",
            "expire_date": "21-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "giftwrap": "1",
            "contact_email": ""
        },
        "Timestamp": 1634114954,
        "check": {
            "check1": "1: BS60321 - 2021-10-13 10:50",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed, 13 Oct 2021 10:43:48 +0200 (CEST)",
        "input": {
            "shop_id": "55",
            "companyname": "Tandl\u00e6ge Sofie M\u00f8ller Lumholtz ApS",
            "cvr": "37139807",
            "phone": "26818533",
            "contact_phone": "26818533",
            "contact_email": "lumholtz.sofie@gmail.com",
            "bill_to_address": "\u00d8sterbrogade 148, 1. tv.",
            "bill_to_postal_code": "2100",
            "bill_to_city": "Kbh. \u00d8.",
            "requisition_no": "Sofie M\u00f8ller Lumholtz",
            "bill_to_country": "DK",
            "quantity": "20",
            "expire_date": "01-04-2022",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK"
        },
        "Timestamp": 1634114628,
        "check": {
            "check1": "1: BS60318 - 2021-10-13 10:44",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 560",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed, 13 Oct 2021 10:38:44 +0200 (CEST)",
        "input": {
            "shop_id": "575",
            "companyname": "BioStata ApS",
            "cvr": "89903319",
            "phone": "30730350",
            "contact_phone": "30730350",
            "contact_email": "finance@biostata.com",
            "bill_to_address": "Bregner\u00f8dvej 132",
            "bill_to_postal_code": "3460",
            "bill_to_city": "Birker\u00f8d",
            "requisition_no": "Margit Richoff",
            "bill_to_country": "DK",
            "quantity": "16",
            "expire_date": "14-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "giftwrap": "1"
        },
        "Timestamp": 1634114324,
        "check": {
            "check1": "1: BS60316 - 2021-10-13 10:40",
            "check2": "",
            "check3": "",
            "shopname": "Designjulegaven",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed, 13 Oct 2021 10:31:55 +0200 (CEST)",
        "input": {
            "shop_id": "53",
            "companyname": "Tandl\u00e6geselskabet Mikkel Eilersen",
            "cvr": "25767241",
            "phone": "+4530501903",
            "contact_phone": "+4530501903",
            "contact_email": "meilersen@mail.dk",
            "bill_to_address": "R\u00f8nneb\u00e6r Alle 50 b",
            "bill_to_postal_code": "3000",
            "bill_to_city": "Helsing\u00f8r",
            "requisition_no": "Christina",
            "bill_to_country": "DK",
            "quantity": "8",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK"
        },
        "Timestamp": 1634113915,
        "check": {
            "check1": "1: BS60314 - 2021-10-13 10:35",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekortet DK 800",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed, 13 Oct 2021 10:27:52 +0200 (CEST)",
        "input": {
            "shop_id": "54",
            "companyname": "Hans Johansen A\/S",
            "cvr": "25358481",
            "phone": "25334201",
            "contact_phone": "25334201",
            "contact_email": "kj@hans-johansen.dk",
            "bill_to_address": "kringelholm, 60",
            "bill_to_postal_code": "3250",
            "bill_to_city": "Gilleleje",
            "requisition_no": "karin johansen",
            "bill_to_country": "DK",
            "quantity": "5",
            "expire_date": "07-11-2021",
            "is_email": "1",
            "use_shipping_address": "1",
            "ship_to_company": "Hans Johansen A\/S",
            "ship_to_address": "kringelholm, 60",
            "ship_to_address_2": "kringelholm, 60",
            "ship_to_postal_code": "3250",
            "ship_to_city": "Gilleleje",
            "ship_to_country": "DK"
        },
        "Timestamp": 1634113672,
        "check": {
            "check1": "",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 400",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed, 13 Oct 2021 10:27:48 +0200 (CEST)",
        "input": {
            "shop_id": "2395",
            "companyname": "Svendborg Synshal ApS",
            "cvr": "29317305",
            "bill_to_address": "Kuopiovej 14",
            "bill_to_postal_code": "5700",
            "bill_to_city": "Svendborg",
            "requisition_no": "Ren\u00e9 Larsen",
            "bill_to_country": "DK",
            "quantity": "5",
            "expire_date": "07-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1634113668,
        "check": {
            "check1": "1: BS60308 - 2021-10-13 10:29",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekortet DK 960",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed, 13 Oct 2021 10:20:43 +0200 (CEST)",
        "input": {
            "shop_id": "52",
            "companyname": "Gr\u00f8nborg El A\/S",
            "cvr": "27509126",
            "ean": "5790002261817",
            "bill_to_address": "Mineralvej 15",
            "bill_to_postal_code": "9220",
            "bill_to_city": "Aalborg \u00d8",
            "requisition_no": "Martin Rytter Jensen",
            "bill_to_country": "DK",
            "quantity": "50",
            "expire_date": "14-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "giftwrap": "1",
            "contact_email": ""
        },
        "Timestamp": 1634113243,
        "check": {
            "check1": "1: BS60306 - 2021-10-13 10:21",
            "check2": "",
            "check3": "",
            "shopname": "Julegavekortet DK",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed, 13 Oct 2021 10:04:06 +0200 (CEST)",
        "input": {
            "shop_id": "54",
            "companyname": "Randers V\u00e6rkt\u00f8jssliberi",
            "cvr": "35026541",
            "phone": "+4586415311",
            "contact_phone": "+4586415311",
            "contact_email": "salg@rvs.dk",
            "bill_to_address": "Kertemindevej 38",
            "bill_to_postal_code": "8940",
            "bill_to_city": "Randers SV",
            "requisition_no": "Anita Carlsen",
            "bill_to_country": "DK",
            "quantity": "11",
            "expire_date": "21-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK"
        },
        "Timestamp": 1634112246,
        "check": {
            "check1": "1: BS60302 - 2021-10-13 10:04",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 400",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed, 13 Oct 2021 10:00:04 +0200 (CEST)",
        "input": {
            "shop_id": "55",
            "companyname": "Wimses",
            "cvr": "28175256",
            "bill_to_address": "solb\u00e6rvej 11",
            "bill_to_postal_code": "4700",
            "bill_to_city": "N\u00e6stved",
            "requisition_no": "Rikke Wimmelmann Zachariassen",
            "bill_to_country": "DK",
            "quantity": "10",
            "expire_date": "01-04-2022",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "giftwrap": "1",
            "contact_email": ""
        },
        "Timestamp": 1634112004,
        "check": {
            "check1": "1: BS60301 - 2021-10-13 10:00",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 560",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed, 13 Oct 2021 09:45:06 +0200 (CEST)",
        "input": {
            "shop_id": "310",
            "companyname": "Skanderup-Hjarup Forbundsskole",
            "cvr": "29189897",
            "ean": "5798005384540",
            "bill_to_address": "Hjarupvej 14, Skanderup",
            "bill_to_postal_code": "6640",
            "bill_to_city": "Lunderskov",
            "requisition_no": "Marianne L. Schl\u00fcnssen",
            "bill_to_country": "DK",
            "quantity": "38",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1634111106,
        "check": {
            "check1": "",
            "check2": "",
            "check3": "2: BS57975 - 2021-09-27 11:10",
            "shopname": "Dr\u00f8mmegavekortet 300",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed, 13 Oct 2021 09:41:19 +0200 (CEST)",
        "input": {
            "shop_id": "2558",
            "companyname": "Advanced Ekono AB",
            "cvr": "5569550931",
            "bill_to_address": "Kanngjutargr\u00e4nd 50",
            "bill_to_postal_code": "162 57 V\u00e4llingby",
            "bill_to_city": "Stockholm",
            "requisition_no": "Paula Karlsson",
            "bill_to_country": "SE",
            "quantity": "6",
            "expire_date": "31-12-2022",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "SE",
            "contact_email": ""
        },
        "Timestamp": 1634110879,
        "check": {
            "check1": "1: BS60357 - 2021-10-13 12:05",
            "check2": "",
            "check3": "",
            "shopname": "24 julklappar 1200 2021",
            "lang": 5
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed, 13 Oct 2021 09:30:56 +0200 (CEST)",
        "input": {
            "shop_id": "52",
            "companyname": "Restaurant Bones Hj\u00f8rring",
            "cvr": "35645748",
            "phone": "22588685",
            "contact_phone": "22588685",
            "contact_email": "ulla.hjoerring@bones.dk",
            "bill_to_address": "\u00d8stergade 19",
            "bill_to_postal_code": "9800",
            "bill_to_city": "Hj\u00f8rring",
            "requisition_no": "Ulla S\u00f8nderg\u00e5rd",
            "bill_to_country": "DK",
            "quantity": "35",
            "expire_date": "31-10-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK"
        },
        "Timestamp": 1634110256,
        "check": {
            "check1": "1: BS60288 - 2021-10-13 09:31",
            "check2": "",
            "check3": "2: BS60283 - 2021-10-13 09:28",
            "shopname": "Julegavekortet DK",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed, 13 Oct 2021 09:28:04 +0200 (CEST)",
        "input": {
            "shop_id": "2550",
            "companyname": "Le Service as",
            "cvr": "983366945",
            "bill_to_address": "Hauglandsvegen 9",
            "bill_to_postal_code": "4362",
            "bill_to_city": "VIGRESTAD",
            "bill_to_country": "NO",
            "bill_to_email": "post@leservice.no",
            "quantity": "7",
            "expire_date": "21-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "NO",
            "giftwrap": "1",
            "contact_email": ""
        },
        "Timestamp": 1634110084,
        "check": {
            "check1": "1: BS60284 - 2021-10-13 09:28",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekort 1200 (NO)",
            "lang": 4
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed, 13 Oct 2021 09:09:19 +0200 (CEST)",
        "input": {
            "shop_id": "575",
            "companyname": "Falck Ekdahl aps",
            "cvr": "35482873",
            "bill_to_address": "Rosenborggade 17, 4. sal",
            "bill_to_postal_code": "1130",
            "bill_to_city": "K\u00f8benhavn K",
            "requisition_no": "Vinni Vendelev Eriksen",
            "bill_to_country": "DK",
            "quantity": "19",
            "expire_date": "14-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "giftwrap": "1",
            "contact_email": ""
        },
        "Timestamp": 1634108959,
        "check": {
            "check1": "1: BS60277 - 2021-10-13 09:09",
            "check2": "",
            "check3": "",
            "shopname": "Designjulegaven",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed, 13 Oct 2021 09:09:01 +0200 (CEST)",
        "input": {
            "shop_id": "53",
            "companyname": "GRID System Aps",
            "cvr": "21685798",
            "bill_to_address": "Smedevangen 2",
            "bill_to_postal_code": "3540",
            "bill_to_city": "Lynge",
            "requisition_no": "Dorthe Dr\u00f8gem\u00fcller",
            "bill_to_country": "DK",
            "quantity": "11",
            "expire_date": "07-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "giftwrap": "1",
            "contact_email": ""
        },
        "Timestamp": 1634108941,
        "check": {
            "check1": "1: BS60278 - 2021-10-13 09:10",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekortet DK 800",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed, 13 Oct 2021 08:42:27 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "Roliba A\/S",
            "cvr": "10903688",
            "bill_to_address": "HVIDK\u00c6RVEJ 52",
            "bill_to_postal_code": "5250",
            "bill_to_city": "ODENSE SV",
            "requisition_no": "Jette Jensen",
            "bill_to_country": "DK",
            "quantity": "23",
            "expire_date": "07-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "giftwrap": "1",
            "contact_email": ""
        },
        "Timestamp": 1634107347,
        "check": {
            "check1": "1: BS60264 - 2021-10-13 08:42",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed, 13 Oct 2021 08:29:39 +0200 (CEST)",
        "input": {
            "shop_id": "59",
            "companyname": "DIRTYBIT AS",
            "cvr": "912002942",
            "bill_to_address": "Starvhusgaten 4",
            "bill_to_postal_code": "5014",
            "bill_to_city": "Bergen",
            "requisition_no": "Erlend Haugsdal",
            "bill_to_country": "NO",
            "bill_to_email": "erlend@dirtybit.no",
            "quantity": "23",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "NO",
            "giftwrap": "1",
            "contact_email": ""
        },
        "Timestamp": 1634106579,
        "check": {
            "check1": "1: BS60260 - 2021-10-13 08:30",
            "check2": "",
            "check3": "",
            "shopname": "Julegavekortet NO 800",
            "lang": 4
        }
    },
    {
        "output": {
            "status": "30",
            "error": "Could not find valid shop_id"
        },
        "Date": " Tue, 12 Oct 2021 22:16:08 +0200 (CEST)",
        "input": {
            "companyname": "Salling fysioterapi",
            "cvr": "19341488",
            "bill_to_address": "Helsev\u00e6nget 6",
            "bill_to_postal_code": "7870",
            "bill_to_city": "Roslev",
            "bill_to_country": "DK",
            "quantity": "13",
            "expire_date": "21-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "shop_id": "",
            "contact_email": ""
        },
        "Timestamp": 1634069768,
        "check": {
            "check1": "1: BS60250 - 2021-10-12 22:22",
            "check2": "",
            "check3": "",
            "shopname": "Ukendt",
            "lang": ""
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_email: not a valid e-mail",
            "field": "contact_email",
            "type": "invalidemail",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue, 12 Oct 2021 21:50:27 +0200 (CEST)",
        "input": {
            "shop_id": "290",
            "companyname": "Speciall\u00e6ge Jesper Thulesen aps",
            "cvr": "38755374",
            "phone": "40551814",
            "contact_name": "jesper thulesen",
            "contact_phone": "40551814",
            "contact_email": "j.thulesen@dadlnet.,dk",
            "bill_to_address": "Strandh\u00f8jsvej 8",
            "bill_to_postal_code": "2920 ",
            "bill_to_city": "Charlottenlund",
            "requisition_no": "jesper thulesen",
            "bill_to_country": "DK",
            "quantity": "7",
            "expire_date": "07-11-2021",
            "is_email": "1",
            "use_shipping_address": "1",
            "ship_to_company": "speciall\u00e6ge jesper thulesen aps",
            "ship_to_address": "strandh\u00f8jsvej 8",
            "ship_to_address_2": "strandh\u00f8jsvej 8",
            "ship_to_postal_code": "2920",
            "ship_to_city": "charlottenlund",
            "ship_to_country": "DK",
            "giftwrap": "1"
        },
        "Timestamp": 1634068227,
        "check": {
            "check1": "",
            "check2": "",
            "check3": "3: BS57913 - 2021-09-25 15:40",
            "shopname": "Dr\u00f8mmegavekortet 200",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue, 12 Oct 2021 20:58:51 +0200 (CEST)",
        "input": {
            "shop_id": "1832",
            "companyname": "Blinka Bl\u00e5 AB",
            "cvr": "556661-1165",
            "bill_to_address": "S\u00f6dra stapelgr\u00e4nd 4",
            "bill_to_postal_code": "21175",
            "bill_to_city": "Malm\u00f6",
            "requisition_no": "Caroline Alnebring ",
            "bill_to_country": "SE",
            "quantity": "10",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "SE",
            "contact_email": ""
        },
        "Timestamp": 1634065131,
        "check": {
            "check1": "1: BS60240 - 2021-10-12 20:59",
            "check2": "",
            "check3": "",
            "shopname": "24Julklappar400 (Sverige)",
            "lang": 5
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue, 12 Oct 2021 20:55:15 +0200 (CEST)",
        "input": {
            "shop_id": "55",
            "companyname": "Bodega V\u00e6rkstedet",
            "cvr": "34807299",
            "bill_to_address": "Kolt \u00d8stervej 33",
            "bill_to_postal_code": "8361",
            "bill_to_city": "Hasselager",
            "requisition_no": "Henning Stald",
            "bill_to_country": "DK",
            "quantity": "8",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1634064915,
        "check": {
            "check1": "1: BS60239 - 2021-10-12 20:59",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 560",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue, 12 Oct 2021 20:53:06 +0200 (CEST)",
        "input": {
            "shop_id": "52",
            "companyname": "Allerup Multibyg v\/Mads J\u00f8rgensen",
            "cvr": "26263034",
            "phone": "28302188",
            "contact_phone": "28302188",
            "contact_email": "mads@allerupbyg.dk",
            "bill_to_address": "Kalundborgvej 217B",
            "bill_to_postal_code": "4300",
            "bill_to_city": "Holb\u00e6k",
            "bill_to_country": "DK",
            "quantity": "5",
            "expire_date": "31-10-2021",
            "is_email": "0",
            "use_shipping_address": "1",
            "ship_to_company": "Allerup Multibyg v\/Mads J\u00f8rgensen",
            "ship_to_address": "Kalundborgvej 217B",
            "ship_to_address_2": "Kalundborgvej 217B",
            "ship_to_postal_code": "4300",
            "ship_to_city": "Holb\u00e6k",
            "ship_to_country": "DK"
        },
        "Timestamp": 1634064786,
        "check": {
            "check1": "1: BS60238 - 2021-10-12 20:53",
            "check2": "",
            "check3": "",
            "shopname": "Julegavekortet DK",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue, 12 Oct 2021 20:13:48 +0200 (CEST)",
        "input": {
            "shop_id": "52",
            "companyname": "Dalum Malerfirma",
            "cvr": "19344940",
            "bill_to_address": "Helgavej 27",
            "bill_to_postal_code": "5230",
            "bill_to_city": "Odense",
            "requisition_no": "Brian Br\u00f8ndsted",
            "bill_to_country": "DK",
            "quantity": "10",
            "expire_date": "31-10-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1634062428,
        "check": {
            "check1": "1: BS60235 - 2021-10-12 20:14",
            "check2": "",
            "check3": "",
            "shopname": "Julegavekortet DK",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue, 12 Oct 2021 19:23:56 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "Shell \/ 7-eleven",
            "cvr": "32359930",
            "phone": "40409659",
            "contact_phone": "40409659",
            "contact_email": "711dk495@7-eleven.dk",
            "bill_to_address": "Vestvejen 95",
            "bill_to_postal_code": "6200",
            "bill_to_city": "Aabenraa",
            "requisition_no": "Kenneth Lauritzen",
            "bill_to_country": "DK",
            "quantity": "5",
            "expire_date": "07-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK"
        },
        "Timestamp": 1634059436,
        "check": {
            "check1": "",
            "check2": "",
            "check3": "1: BS60234 - 2021-10-12 19:24",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue, 12 Oct 2021 17:07:51 +0200 (CEST)",
        "input": {
            "shop_id": "53",
            "companyname": "Stekavikar",
            "cvr": "33567944",
            "bill_to_address": "Odinsvej 9",
            "bill_to_postal_code": "4600",
            "bill_to_city": "K\u00f8ge",
            "requisition_no": "Stefan",
            "bill_to_country": "DK",
            "quantity": "12",
            "expire_date": "21-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "giftwrap": "1",
            "contact_email": ""
        },
        "Timestamp": 1634051271,
        "check": {
            "check1": "1: BS60232 - 2021-10-12 17:08",
            "check2": "",
            "check3": "2: BS60337 - 2021-10-13 11:19",
            "shopname": "Guldgavekortet DK 800",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue, 12 Oct 2021 16:46:39 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "Flensted Mobiler aps",
            "cvr": "70637618",
            "bill_to_address": "Brovej 6",
            "bill_to_postal_code": "5464",
            "bill_to_city": "Brenderup",
            "requisition_no": "Ole Flensted ",
            "bill_to_country": "DK",
            "quantity": "27",
            "expire_date": "07-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "giftwrap": "1",
            "contact_email": ""
        },
        "Timestamp": 1634049999,
        "check": {
            "check1": "",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue, 12 Oct 2021 16:27:41 +0200 (CEST)",
        "input": {
            "shop_id": "52",
            "companyname": "GEM\u00dc ApS",
            "cvr": "30497643",
            "bill_to_address": "Brydehusvej 13, 2. sal",
            "bill_to_postal_code": "2750",
            "bill_to_city": "Ballerup",
            "requisition_no": "Stefan A. H. Holmgren",
            "bill_to_country": "DK",
            "quantity": "9",
            "expire_date": "14-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1634048861,
        "check": {
            "check1": "1: BS60230 - 2021-10-12 16:28",
            "check2": "",
            "check3": "",
            "shopname": "Julegavekortet DK",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue, 12 Oct 2021 16:15:25 +0200 (CEST)",
        "input": {
            "shop_id": "575",
            "companyname": "Superbrugsen",
            "cvr": "40981810",
            "bill_to_address": "Centerpladsen 5",
            "bill_to_postal_code": "6360",
            "bill_to_city": "Tinglev",
            "requisition_no": "Martin Br\u00f8nd",
            "bill_to_country": "DK",
            "quantity": "19",
            "expire_date": "14-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "giftwrap": "1",
            "contact_email": ""
        },
        "Timestamp": 1634048125,
        "check": {
            "check1": "1: BS60229 - 2021-10-12 16:15",
            "check2": "",
            "check3": "",
            "shopname": "Designjulegaven",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue, 12 Oct 2021 15:49:31 +0200 (CEST)",
        "input": {
            "shop_id": "310",
            "companyname": "\u00d8strup autoophug & Genvindingsindustri",
            "cvr": "34005133",
            "phone": "21234268",
            "contact_phone": "21234268",
            "contact_email": "nanna@ostrupautoophug.dk",
            "bill_to_address": "Gammel Aalborgvej 53 A",
            "bill_to_postal_code": "9632",
            "bill_to_city": "M\u00f8ldrup",
            "requisition_no": "Nanna - Gaver",
            "bill_to_country": "DK",
            "quantity": "19",
            "expire_date": "07-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK"
        },
        "Timestamp": 1634046571,
        "check": {
            "check1": "1: BS60223 - 2021-10-12 15:49",
            "check2": "",
            "check3": "",
            "shopname": "Dr\u00f8mmegavekortet 300",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue, 12 Oct 2021 15:27:34 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "SuperBrugsen Hammerum",
            "cvr": "38556215",
            "phone": "20888813",
            "contact_phone": "20888813",
            "contact_email": "karsten.holmgaard@superbrugsen.dk",
            "bill_to_address": "Hammerum Hovedgade 72",
            "bill_to_postal_code": "7400",
            "bill_to_city": "Herning",
            "requisition_no": "Karsten Holmgaard",
            "bill_to_country": "DK",
            "quantity": "11",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "giftwrap": "1"
        },
        "Timestamp": 1634045254,
        "check": {
            "check1": "1: BS60217 - 2021-10-12 15:28",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue, 12 Oct 2021 15:25:23 +0200 (CEST)",
        "input": {
            "shop_id": "574",
            "companyname": "Pyramiden Legekontor",
            "cvr": "982191831",
            "bill_to_address": "Gloppeskogen 24",
            "bill_to_postal_code": "3260",
            "bill_to_city": "Larvik",
            "requisition_no": "Bente Pedersen B\u00f8 ",
            "bill_to_country": "NO",
            "bill_to_email": "bente23@lf-nett.no",
            "quantity": "6",
            "expire_date": "07-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "NO",
            "giftwrap": "1",
            "contact_email": ""
        },
        "Timestamp": 1634045123,
        "check": {
            "check1": "1: BS60215 - 2021-10-12 15:25",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekort NO",
            "lang": 4
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue, 12 Oct 2021 15:16:07 +0200 (CEST)",
        "input": {
            "shop_id": "52",
            "companyname": "ACG Nystr\u00f6m aps",
            "cvr": "31863937",
            "bill_to_address": "Jupitervej 4C",
            "bill_to_postal_code": "7430",
            "bill_to_city": "Ikast",
            "requisition_no": "Carina Yu",
            "bill_to_country": "DK",
            "quantity": "7",
            "expire_date": "31-10-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1634044567,
        "check": {
            "check1": "1: BS60210 - 2021-10-12 15:17",
            "check2": "",
            "check3": "1: BS60213 - 2021-10-12 15:18",
            "shopname": "Julegavekortet DK",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue, 12 Oct 2021 14:39:44 +0200 (CEST)",
        "input": {
            "shop_id": "1981",
            "companyname": "Stigab, Stig \u00d6dlund AB",
            "cvr": "556240-6339",
            "bill_to_address": "F\u00e5gelviksv\u00e4gen 18",
            "bill_to_postal_code": "14553",
            "bill_to_city": "Norsborg",
            "requisition_no": "Niclas Qvist",
            "bill_to_country": "SE",
            "quantity": "21",
            "expire_date": "31-12-2022",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "SE",
            "contact_email": ""
        },
        "Timestamp": 1634042384,
        "check": {
            "check1": "1: BS60200 - 2021-10-12 14:41",
            "check2": "",
            "check3": "",
            "shopname": "24Julklappar800 (Sverige)",
            "lang": 5
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue, 12 Oct 2021 14:33:46 +0200 (CEST)",
        "input": {
            "shop_id": "52",
            "companyname": "Scan-Med A\/S",
            "cvr": "DK14472800",
            "bill_to_address": "Dalgaardsvej 17",
            "bill_to_postal_code": "8220",
            "bill_to_city": "Brabrand",
            "requisition_no": "Kirsten Willis",
            "bill_to_country": "DK",
            "quantity": "13",
            "expire_date": "14-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1634042026,
        "check": {
            "check1": "",
            "check2": "",
            "check3": "",
            "shopname": "Julegavekortet DK",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue, 12 Oct 2021 14:22:53 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "Interglas ApS",
            "cvr": "34468893",
            "bill_to_address": "Kornmarksvej 10",
            "bill_to_postal_code": "2605",
            "bill_to_city": "Br\u00f8ndby",
            "requisition_no": "Marianne",
            "bill_to_country": "DK",
            "quantity": "27",
            "expire_date": "21-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1634041373,
        "check": {
            "check1": "1: BS60191 - 2021-10-12 14:25",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field cvr: value is required",
            "field": "cvr",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue, 12 Oct 2021 14:15:25 +0200 (CEST)",
        "input": {
            "shop_id": "272",
            "companyname": "\u00f8\u00e6\u00e5",
            "bill_to_address": "asdfasd\u00f8\u00e6\u00e5",
            "bill_to_postal_code": "2323",
            "bill_to_city": "vadsfasd\u00f8\u00e6\u00e5",
            "requisition_no": "\u00f8\u00e6\u00e5",
            "bill_to_country": "NO",
            "quantity": "5",
            "expire_date": "07-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "NO",
            "giftwrap": "1",
            "cvr": "",
            "contact_email": ""
        },
        "Timestamp": 1634040925,
        "check": {
            "check1": "*",
            "check2": "*",
            "check3": "*",
            "shopname": "Julegavekortet NO 300",
            "lang": 4
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue, 12 Oct 2021 14:15:00 +0200 (CEST)",
        "input": {
            "shop_id": "57",
            "companyname": "Espira Steinsviken barnehage",
            "cvr": "985072725",
            "bill_to_address": "Steinsvikvegen 89",
            "bill_to_postal_code": "5251",
            "bill_to_city": "S\u00d8REIDGREND",
            "requisition_no": "Kristin Hages\u00e6ter",
            "bill_to_country": "NO",
            "bill_to_email": "faktura.steinsviken@espira.no",
            "quantity": "14",
            "expire_date": "31-12-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "NO",
            "contact_email": ""
        },
        "Timestamp": 1634040900,
        "check": {
            "check1": "",
            "check2": "",
            "check3": "",
            "shopname": "Julegavekortet NO 400",
            "lang": 4
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field cvr: value is required",
            "field": "cvr",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue, 12 Oct 2021 14:14:37 +0200 (CEST)",
        "input": {
            "shop_id": "272",
            "companyname": "\u00f8\u00e6\u00e5",
            "bill_to_address": "asdfasd",
            "bill_to_postal_code": "2323",
            "bill_to_city": "vadsfasd",
            "bill_to_country": "NO",
            "quantity": "5",
            "expire_date": "07-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "NO",
            "giftwrap": "1",
            "cvr": "",
            "contact_email": ""
        },
        "Timestamp": 1634040877,
        "check": {
            "check1": "*",
            "check2": "*",
            "check3": "*",
            "shopname": "Julegavekortet NO 300",
            "lang": 4
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue, 12 Oct 2021 14:14:05 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "Nobrakes ApS",
            "cvr": "35377379",
            "ean": "4571762004606",
            "phone": "96600222",
            "contact_phone": "96600222",
            "contact_email": "abk@nobrakes.dk",
            "bill_to_address": "Marsvej 6",
            "bill_to_postal_code": "Ikast",
            "bill_to_city": "7430",
            "requisition_no": "Anette Kalles\u00f8e",
            "bill_to_country": "DK",
            "quantity": "11",
            "expire_date": "07-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "giftwrap": "1"
        },
        "Timestamp": 1634040845,
        "check": {
            "check1": "1: BS60187 - 2021-10-12 14:14",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field bill_to_email: value is required",
            "field": "bill_to_email",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue, 12 Oct 2021 14:08:44 +0200 (CEST)",
        "input": {
            "shop_id": "574",
            "companyname": "Stortingets ombudsnemnd for Forsvaret",
            "cvr": "971527439",
            "phone": "23356470\/71",
            "contact_name": "Heidi Lok\u00f8en",
            "contact_phone": "23356470\/71",
            "contact_email": "post@forsvarsombudet.no",
            "bill_to_address": "Fakturamottak DF\u00d8 - Pb 4746 Torgarden",
            "bill_to_postal_code": "7468 ",
            "bill_to_city": "Trondheim",
            "requisition_no": "4040mala",
            "bill_to_country": "NO",
            "quantity": "5",
            "expire_date": "07-11-2021",
            "is_email": "1",
            "use_shipping_address": "1",
            "ship_to_company": "Stortingets ombudsnemnd for Forsvaret",
            "ship_to_address": "Karl Johansgate 25, 3.etg",
            "ship_to_address_2": "Karl Johansgate 25, 3.etg",
            "ship_to_postal_code": "0159 ",
            "ship_to_city": "Oslo",
            "ship_to_country": "NO"
        },
        "Timestamp": 1634040524,
        "check": {
            "check1": "1: BS60185 - 2021-10-12 14:09",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekort NO",
            "lang": 4
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue, 12 Oct 2021 13:49:55 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "North Atlantic Shipping",
            "cvr": "29976465",
            "bill_to_address": "Herluf Trollesvej 1",
            "bill_to_postal_code": "9850",
            "bill_to_city": "Hirtshals",
            "bill_to_country": "DK",
            "quantity": "5",
            "expire_date": "07-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1634039395,
        "check": {
            "check1": "1: BS60175 - 2021-10-12 13:51",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field expire_date: value is required",
            "field": "expire_date",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue, 12 Oct 2021 13:43:22 +0200 (CEST)",
        "input": {
            "shop_id": "52",
            "companyname": "Topvask ApS",
            "cvr": "35028056",
            "phone": "41613068",
            "contact_name": "Brian Lindgaard",
            "contact_phone": "41613068",
            "contact_email": "Brianlj@mail.dk",
            "bill_to_address": "Juelstrupparken 30",
            "bill_to_postal_code": "9530",
            "bill_to_city": "st\u00f8vring",
            "requisition_no": "Brian LIndgaard",
            "bill_to_country": "DK",
            "quantity": "5",
            "is_email": "1",
            "use_shipping_address": "1",
            "ship_to_company": "Topvask ApS",
            "ship_to_address": "Juelstrupparken 30",
            "ship_to_address_2": "Juelstrupparken 30",
            "ship_to_postal_code": "9530",
            "ship_to_city": "st\u00f8vring",
            "ship_to_country": "DK"
        },
        "Timestamp": 1634039002,
        "check": {
            "check1": "1: BS60166 - 2021-10-12 13:43",
            "check2": "",
            "check3": "3: BS60163 - 2021-10-12 13:39",
            "shopname": "Julegavekortet DK",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue, 12 Oct 2021 13:38:45 +0200 (CEST)",
        "input": {
            "shop_id": "54",
            "companyname": "Topvask ApS",
            "cvr": "35028056",
            "bill_to_address": "Juelstrupparken 30",
            "bill_to_postal_code": "9530",
            "bill_to_city": "st\u00f8vring",
            "requisition_no": "Brian Jensen",
            "bill_to_country": "DK",
            "quantity": "6",
            "expire_date": "07-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1634038725,
        "check": {
            "check1": "1: BS60163 - 2021-10-12 13:39",
            "check2": "",
            "check3": "3: BS60164 - 2021-10-12 13:41",
            "shopname": "24 Gaver DK 400",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue, 12 Oct 2021 13:20:29 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "Viking Energi AS",
            "cvr": "34717206",
            "bill_to_address": "Stamholmen 165 - Y",
            "bill_to_postal_code": "2650",
            "bill_to_city": "Hvidovre",
            "requisition_no": "Kenneth Krogh",
            "bill_to_country": "DK",
            "quantity": "16",
            "expire_date": "07-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1634037629,
        "check": {
            "check1": "1: BS60158 - 2021-10-12 13:21",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue, 12 Oct 2021 13:04:15 +0200 (CEST)",
        "input": {
            "shop_id": "2550",
            "companyname": "Hvite Smil As",
            "cvr": "917867232",
            "bill_to_address": "Bj\u00f8rnstjerne Bj\u00f8rnsonsgate 21",
            "bill_to_postal_code": "3044",
            "bill_to_city": "Drammen",
            "requisition_no": "Claus Gamborg Nielsen",
            "bill_to_country": "NO",
            "bill_to_email": "mail@hvitesmil.no",
            "quantity": "5",
            "expire_date": "07-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "NO",
            "giftwrap": "1",
            "contact_email": ""
        },
        "Timestamp": 1634036655,
        "check": {
            "check1": "1: BS60149 - 2021-10-12 13:05",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekort 1200 (NO)",
            "lang": 4
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue, 12 Oct 2021 13:00:22 +0200 (CEST)",
        "input": {
            "shop_id": "54",
            "companyname": "Balle Sogn ",
            "cvr": "22637312",
            "ean": "5798000860049",
            "bill_to_address": "Balle Bygade 162",
            "bill_to_postal_code": "8600",
            "bill_to_city": "Silkeborg",
            "requisition_no": "Lise M\u00f8ller Nissen",
            "bill_to_country": "DK",
            "quantity": "14",
            "expire_date": "07-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1634036422,
        "check": {
            "check1": "1: BS60147 - 2021-10-12 13:01",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 400",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field companyname: value is required",
            "field": "companyname",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue, 12 Oct 2021 12:53:10 +0200 (CEST)",
        "input": {
            "shop_id": "1832",
            "bill_to_country": "SE",
            "expire_date": "31-12-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "SE",
            "cvr": "",
            "contact_email": ""
        },
        "Timestamp": 1634035990,
        "check": {
            "check1": "*",
            "check2": "*",
            "check3": "*",
            "shopname": "24Julklappar400 (Sverige)",
            "lang": 5
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_phone: value is required",
            "field": "contact_phone",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue, 12 Oct 2021 12:36:54 +0200 (CEST)",
        "input": {
            "shop_id": "53",
            "companyname": "Green Cotton Group",
            "cvr": "DK15694505",
            "contact_name": "Sanne N\u00f8rgaard Johansen",
            "bill_to_address": "Thrigesvej 5",
            "bill_to_postal_code": "7430",
            "bill_to_city": "Ikast",
            "requisition_no": "Sanne N\u00f8rgaard Johansen",
            "bill_to_country": "DK",
            "quantity": "24",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1634035014,
        "check": {
            "check1": "",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekortet DK 800",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field ship_to_postal_code: value is required",
            "field": "ship_to_postal_code",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue, 12 Oct 2021 12:17:12 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "Fonden Forcen",
            "cvr": "30656598",
            "phone": "86428604",
            "contact_name": "Sanne Juulsgaard",
            "contact_phone": "86428604",
            "contact_email": "administration@forcen.dk",
            "bill_to_address": "Kronjydevej 1",
            "bill_to_postal_code": "8960",
            "bill_to_city": "Randers S\u00d8",
            "requisition_no": "Sanne Juulsgaard",
            "bill_to_country": "DK",
            "quantity": "39",
            "expire_date": "07-11-2021",
            "is_email": "1",
            "use_shipping_address": "1",
            "ship_to_company": "Fonden Forcen",
            "ship_to_address": "Kronjydevej 1",
            "ship_to_address_2": "Kronjydevej 1",
            "ship_to_country": "DK"
        },
        "Timestamp": 1634033832,
        "check": {
            "check1": "1: BS60124 - 2021-10-12 12:17",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue, 12 Oct 2021 12:02:56 +0200 (CEST)",
        "input": {
            "shop_id": "52",
            "companyname": "Tecvent ",
            "cvr": "50257312",
            "bill_to_address": "Svallerup Bygade 17",
            "bill_to_postal_code": "4400",
            "bill_to_city": "Kalundborg",
            "requisition_no": "Pia Hansen",
            "bill_to_country": "DK",
            "quantity": "10",
            "expire_date": "14-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1634032976,
        "check": {
            "check1": "1: BS60120 - 2021-10-12 12:03",
            "check2": "",
            "check3": "",
            "shopname": "Julegavekortet DK",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue, 12 Oct 2021 11:52:29 +0200 (CEST)",
        "input": {
            "shop_id": "575",
            "companyname": "Jakobs Boligcenter",
            "cvr": "21105899",
            "contact_email": "Torben@sofahuset.dk",
            "bill_to_address": "Brundevest 1",
            "bill_to_postal_code": "6230",
            "bill_to_city": "R\u00f8dekro",
            "requisition_no": "Torben Sk\u00f8tt",
            "bill_to_country": "DK",
            "quantity": "14",
            "expire_date": "31-10-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK"
        },
        "Timestamp": 1634032349,
        "check": {
            "check1": "",
            "check2": "",
            "check3": "1: BS60112 - 2021-10-12 11:54",
            "shopname": "Designjulegaven",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field bill_to_city: string must be at least 3 characters",
            "field": "bill_to_city",
            "type": "tooshort",
            "min": "3",
            "max": "",
            "length": ""
        },
        "Date": " Tue, 12 Oct 2021 11:51:16 +0200 (CEST)",
        "input": {
            "shop_id": "52",
            "companyname": "Ry Fysioterapi",
            "cvr": "33054874",
            "phone": "86892704",
            "contact_name": "Hanne Laursen",
            "contact_phone": "86892704",
            "contact_email": "hl@ryfys.dk",
            "bill_to_address": "Rugaarden 5",
            "bill_to_postal_code": "8680",
            "bill_to_city": "Ry",
            "requisition_no": "Hanne Laursen",
            "bill_to_country": "DK",
            "quantity": "21",
            "expire_date": "14-11-2021",
            "is_email": "0",
            "use_shipping_address": "1",
            "ship_to_company": "Ry fysioterapi",
            "ship_to_address": "Rugaarden 5",
            "ship_to_address_2": "Rugaarden 5",
            "ship_to_postal_code": "8680",
            "ship_to_city": "Ry",
            "ship_to_country": "DK"
        },
        "Timestamp": 1634032276,
        "check": {
            "check1": "",
            "check2": "1: BS60197 - 2021-10-12 14:35",
            "check3": "",
            "shopname": "Julegavekortet DK",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue, 12 Oct 2021 11:47:51 +0200 (CEST)",
        "input": {
            "shop_id": "59",
            "companyname": "Cosentino Norway AS",
            "cvr": "898197042",
            "bill_to_address": "Delitoppen 3",
            "bill_to_postal_code": "1540",
            "bill_to_city": "Vestby",
            "requisition_no": "Dag Ove Pettersen",
            "bill_to_country": "NO",
            "bill_to_email": "dpettersen@cosentino.com",
            "quantity": "14",
            "expire_date": "07-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "NO",
            "giftwrap": "1",
            "contact_email": ""
        },
        "Timestamp": 1634032071,
        "check": {
            "check1": "1: BS60108 - 2021-10-12 11:48",
            "check2": "",
            "check3": "",
            "shopname": "Julegavekortet NO 800",
            "lang": 4
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field bill_to_city: string must be at least 3 characters",
            "field": "bill_to_city",
            "type": "tooshort",
            "min": "3",
            "max": "",
            "length": ""
        },
        "Date": " Tue, 12 Oct 2021 11:44:23 +0200 (CEST)",
        "input": {
            "shop_id": "52",
            "companyname": "Ry fysioterapi",
            "cvr": "33054874",
            "phone": "86892704",
            "contact_name": "Hanne Laursen",
            "contact_phone": "86892704",
            "contact_email": "hl@ryfys.dk",
            "bill_to_address": "Rugaarden 5",
            "bill_to_postal_code": "8680",
            "bill_to_city": "Ry",
            "requisition_no": "Hanne Laursen",
            "bill_to_country": "DK",
            "quantity": "21",
            "expire_date": "14-11-2021",
            "is_email": "0",
            "use_shipping_address": "1",
            "ship_to_company": "Ry fysioterapi",
            "ship_to_address": "Rugaarden 5",
            "ship_to_address_2": "Rugaarden 5",
            "ship_to_postal_code": "8680",
            "ship_to_city": "Ry",
            "ship_to_country": "DK"
        },
        "Timestamp": 1634031863,
        "check": {
            "check1": "",
            "check2": "1: BS60197 - 2021-10-12 14:35",
            "check3": "",
            "shopname": "Julegavekortet DK",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue, 12 Oct 2021 11:24:49 +0200 (CEST)",
        "input": {
            "shop_id": "2395",
            "companyname": "Greenway Denmark ApS",
            "cvr": "30556844",
            "bill_to_address": "Tolnevej 133, Tolne, Tolne",
            "bill_to_postal_code": "9870",
            "bill_to_city": "Sindal",
            "requisition_no": "S\u00f8ren Andreassen",
            "bill_to_country": "DK",
            "quantity": "9",
            "expire_date": "07-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1634030689,
        "check": {
            "check1": "",
            "check2": "1: BS60107 - 2021-10-12 11:45",
            "check3": "",
            "shopname": "Guldgavekortet DK 960",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue, 12 Oct 2021 11:21:23 +0200 (CEST)",
        "input": {
            "shop_id": "52",
            "companyname": "Frederikssund Fysioterapi ApS",
            "cvr": "28845367",
            "phone": "26180715",
            "contact_phone": "26180715",
            "contact_email": "sq@fredfys.dk",
            "bill_to_address": "\u00d8stergade 30M",
            "bill_to_postal_code": "3600",
            "bill_to_city": "Frederikssund",
            "requisition_no": "S\u00f8ren Qvist",
            "bill_to_country": "DK",
            "quantity": "12",
            "expire_date": "14-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK"
        },
        "Timestamp": 1634030483,
        "check": {
            "check1": "1: BS60100 - 2021-10-12 11:21",
            "check2": "",
            "check3": "",
            "shopname": "Julegavekortet DK",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue, 12 Oct 2021 11:17:04 +0200 (CEST)",
        "input": {
            "shop_id": "2548",
            "companyname": "L\u00e6gerne Hasseris Bymidte",
            "cvr": "83575328",
            "bill_to_address": "Thulebakken 22,1.tv.",
            "bill_to_postal_code": "9000",
            "bill_to_city": "Aalborg",
            "requisition_no": "Jette Jakobsen",
            "bill_to_country": "DK",
            "quantity": "7",
            "expire_date": "31-10-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1634030224,
        "check": {
            "check1": "1: BS60096 - 2021-10-12 11:18",
            "check2": "",
            "check3": "",
            "shopname": "Det gr\u00f8nne gavekort",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue, 12 Oct 2021 11:07:05 +0200 (CEST)",
        "input": {
            "shop_id": "55",
            "companyname": "M Office",
            "cvr": "32477534",
            "bill_to_address": "Walgerholm 1",
            "bill_to_postal_code": "3500",
            "bill_to_city": "V\u00e6rl\u00f8se",
            "requisition_no": "Nina Mogenstrup",
            "bill_to_country": "DK",
            "quantity": "15",
            "expire_date": "21-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1634029625,
        "check": {
            "check1": "1: BS60090 - 2021-10-12 11:07",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 560",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue, 12 Oct 2021 10:52:45 +0200 (CEST)",
        "input": {
            "shop_id": "52",
            "companyname": "BIS Media",
            "cvr": "36048654",
            "phone": "50501699",
            "contact_phone": "50501699",
            "contact_email": "ingrid@mediaid.dk",
            "bill_to_address": "Nannasgade 28",
            "bill_to_postal_code": "2200 N",
            "bill_to_city": "K\u00f8benhavn",
            "requisition_no": "Ingrid Br\u00f6cker",
            "bill_to_country": "DK",
            "quantity": "5",
            "expire_date": "14-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK"
        },
        "Timestamp": 1634028765,
        "check": {
            "check1": "1: BS60086 - 2021-10-12 10:53",
            "check2": "",
            "check3": "",
            "shopname": "Julegavekortet DK",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue, 12 Oct 2021 10:47:09 +0200 (CEST)",
        "input": {
            "shop_id": "2395",
            "companyname": "Merck DAnmark ",
            "cvr": "32932274",
            "bill_to_address": "Vandt\u00e5rnsvej 62A",
            "bill_to_postal_code": "2860",
            "bill_to_city": "S\u00f8borg",
            "requisition_no": "Henrik Brockenhuus-Schack",
            "bill_to_country": "DK",
            "quantity": "43",
            "expire_date": "07-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1634028429,
        "check": {
            "check1": "",
            "check2": "1: BS60631 - 2021-10-14 14:32",
            "check3": "",
            "shopname": "Guldgavekortet DK 960",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue, 12 Oct 2021 10:43:46 +0200 (CEST)",
        "input": {
            "shop_id": "52",
            "companyname": "Egatec A\/S",
            "cvr": "27348440",
            "bill_to_address": "Hvidk\u00e6rvej 3",
            "bill_to_postal_code": "5250",
            "bill_to_city": "Odense SV",
            "requisition_no": "Lise Dahl",
            "bill_to_country": "DK",
            "quantity": "29",
            "expire_date": "14-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1634028226,
        "check": {
            "check1": "1: BS60083 - 2021-10-12 10:45",
            "check2": "",
            "check3": "",
            "shopname": "Julegavekortet DK",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue, 12 Oct 2021 10:15:05 +0200 (CEST)",
        "input": {
            "shop_id": "310",
            "companyname": "marengsen",
            "cvr": "12986718",
            "ean": "5798009694836",
            "bill_to_address": "marengovej 23, marengsen",
            "bill_to_postal_code": "2300",
            "bill_to_city": "K\u00f8benhavn S",
            "requisition_no": "Karin Riis",
            "bill_to_country": "DK",
            "quantity": "12",
            "expire_date": "31-12-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1634026505,
        "check": {
            "check1": "1: BS60072 - 2021-10-12 10:15",
            "check2": "",
            "check3": "",
            "shopname": "Dr\u00f8mmegavekortet 300",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue, 12 Oct 2021 10:14:50 +0200 (CEST)",
        "input": {
            "shop_id": "52",
            "companyname": "Astralis A\/S",
            "cvr": "39990970",
            "bill_to_address": "Otto Busses Vej 7, 2. Sal",
            "bill_to_postal_code": "2450",
            "bill_to_city": "K\u00f8benhavn SV",
            "requisition_no": "Jakob Hansen",
            "bill_to_country": "DK",
            "quantity": "30",
            "expire_date": "14-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1634026490,
        "check": {
            "check1": "1: BS60073 - 2021-10-12 10:15",
            "check2": "",
            "check3": "",
            "shopname": "Julegavekortet DK",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue, 12 Oct 2021 10:12:41 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "Revisionsfirmaet Edelbo og Lund-Larsen",
            "cvr": "32327249",
            "bill_to_address": "Frederiksholms Kanal 2, 1. sal",
            "bill_to_postal_code": "1220",
            "bill_to_city": "K\u00f8benhavn K.",
            "requisition_no": "Mimi Issa",
            "bill_to_country": "DK",
            "quantity": "9",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1634026361,
        "check": {
            "check1": "",
            "check2": "1: BS60077 - 2021-10-12 10:35",
            "check3": "",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue, 12 Oct 2021 10:05:09 +0200 (CEST)",
        "input": {
            "shop_id": "2548",
            "companyname": "danbolig nyborg",
            "cvr": "32832954",
            "bill_to_address": "n\u00f8rregade 5",
            "bill_to_postal_code": "5800",
            "bill_to_city": "nyborg",
            "requisition_no": "kristina poulsen",
            "bill_to_country": "DK",
            "quantity": "5",
            "expire_date": "14-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1634025909,
        "check": {
            "check1": "1: BS60067 - 2021-10-12 10:06",
            "check2": "",
            "check3": "3: BS60068 - 2021-10-12 10:07",
            "shopname": "Det gr\u00f8nne gavekort",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue, 12 Oct 2021 09:55:23 +0200 (CEST)",
        "input": {
            "shop_id": "2395",
            "companyname": "Merck Danmark",
            "cvr": "32932274",
            "bill_to_address": "Vandt\u00e5rnsvej 62A",
            "bill_to_postal_code": "2860",
            "bill_to_city": "S\u00f8borg",
            "requisition_no": "Henrik Brockenhuus-Schack",
            "bill_to_country": "DK",
            "quantity": "43",
            "expire_date": "07-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1634025323,
        "check": {
            "check1": "",
            "check2": "1: BS60631 - 2021-10-14 14:32",
            "check3": "",
            "shopname": "Guldgavekortet DK 960",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue, 12 Oct 2021 09:53:33 +0200 (CEST)",
        "input": {
            "shop_id": "54",
            "companyname": "DGI Bornholm",
            "cvr": "15382279",
            "ean": "5790001688875",
            "bill_to_address": "Tornev\u00e6rksvej 20, 3700 R\u00f8nne",
            "bill_to_postal_code": "3700",
            "bill_to_city": "R\u00f8nne",
            "requisition_no": "Karen-Margrethe",
            "bill_to_country": "DK",
            "quantity": "12",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1634025213,
        "check": {
            "check1": "1: BS60061 - 2021-10-12 09:54",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 400",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue, 12 Oct 2021 09:35:41 +0200 (CEST)",
        "input": {
            "shop_id": "53",
            "companyname": "HL Keramik A\/S",
            "cvr": "26079500",
            "bill_to_address": "Jelsvej 7",
            "bill_to_postal_code": "6000",
            "bill_to_city": "Kolding",
            "requisition_no": "Louise Skov Christensen",
            "bill_to_country": "DK",
            "quantity": "10",
            "expire_date": "07-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1634024141,
        "check": {
            "check1": "1: BS60054 - 2021-10-12 09:43",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekortet DK 800",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue, 12 Oct 2021 09:27:32 +0200 (CEST)",
        "input": {
            "shop_id": "52",
            "companyname": "Drosthuset Glostrup",
            "cvr": "19178749",
            "bill_to_address": "Siestavej, 8",
            "bill_to_postal_code": "2600",
            "bill_to_city": "Glostrup",
            "requisition_no": "Bo Andersen",
            "bill_to_country": "DK",
            "quantity": "5",
            "expire_date": "31-12-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1634023652,
        "check": {
            "check1": "1: BS60042 - 2021-10-12 09:28",
            "check2": "",
            "check3": "",
            "shopname": "Julegavekortet DK",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue, 12 Oct 2021 09:26:23 +0200 (CEST)",
        "input": {
            "shop_id": "2395",
            "companyname": "Thisted Bolig",
            "cvr": "60513813",
            "bill_to_address": "jernbanegade, 19",
            "bill_to_postal_code": "7700",
            "bill_to_city": "THISTED",
            "requisition_no": "Chano Sauer",
            "bill_to_country": "DK",
            "quantity": "20",
            "expire_date": "21-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1634023583,
        "check": {
            "check1": "1: BS60041 - 2021-10-12 09:27",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekortet DK 960",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue, 12 Oct 2021 09:19:00 +0200 (CEST)",
        "input": {
            "shop_id": "574",
            "companyname": "Saga Regnskap Asker AS",
            "cvr": "882 571 432",
            "phone": "48208059",
            "contact_phone": "48208059",
            "contact_email": "tha@sagaservices.no",
            "bill_to_address": "Solbr\u00e5veien 41",
            "bill_to_postal_code": "1383",
            "bill_to_city": "Asker",
            "requisition_no": "Trine Hansen",
            "bill_to_country": "NO",
            "bill_to_email": "tha@sagaservices.no",
            "quantity": "24",
            "expire_date": "07-11-2021",
            "is_email": "1",
            "use_shipping_address": "1",
            "ship_to_company": "Saga Regnskap Asker og B\u00e6rum AS",
            "ship_to_address": "Solbr\u00e5veien 41",
            "ship_to_address_2": "Solbr\u00e5veien 41",
            "ship_to_postal_code": "1383",
            "ship_to_city": "Asker",
            "ship_to_country": "NO"
        },
        "Timestamp": 1634023140,
        "check": {
            "check1": "",
            "check2": "1: BS60087 - 2021-10-12 10:58",
            "check3": "",
            "shopname": "Guldgavekort NO",
            "lang": 4
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue, 12 Oct 2021 09:10:28 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "Iversen Trading",
            "cvr": "21817074",
            "bill_to_address": "Sintrupvej 7",
            "bill_to_postal_code": "8220",
            "bill_to_city": "Brabrand",
            "requisition_no": "Jens Iversen",
            "bill_to_country": "DK",
            "quantity": "10",
            "expire_date": "07-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1634022628,
        "check": {
            "check1": "1: BS60036 - 2021-10-12 09:11",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue, 12 Oct 2021 09:00:01 +0200 (CEST)",
        "input": {
            "shop_id": "2395",
            "companyname": "Hein og S\u00f8nner ",
            "cvr": "25244230",
            "phone": "92440003",
            "contact_phone": "92440003",
            "contact_email": "ms@hein-sonner.dk",
            "bill_to_address": "Lollandsvej 2",
            "bill_to_postal_code": "8940",
            "bill_to_city": "Randers SV",
            "requisition_no": "Martin Stapelfeld",
            "bill_to_country": "DK",
            "quantity": "6",
            "expire_date": "21-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK"
        },
        "Timestamp": 1634022001,
        "check": {
            "check1": "",
            "check2": "",
            "check3": "3: BS60031 - 2021-10-12 09:00",
            "shopname": "Guldgavekortet DK 960",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue, 12 Oct 2021 08:54:35 +0200 (CEST)",
        "input": {
            "shop_id": "54",
            "companyname": "B\u00f8rneinstitution Tarup-P\u00e5rup",
            "cvr": "35209115",
            "ean": "5798006614394",
            "bill_to_address": "Slotsgade 5, 2. sal th",
            "bill_to_postal_code": "5000",
            "bill_to_city": "Odense C",
            "requisition_no": "Per Kehling Lykke",
            "bill_to_country": "DK",
            "quantity": "9",
            "expire_date": "07-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1634021675,
        "check": {
            "check1": "1: BS60029 - 2021-10-12 08:56",
            "check2": "",
            "check3": "9: BS56736 - 2021-09-09 14:29",
            "shopname": "24 Gaver DK 400",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue, 12 Oct 2021 08:48:17 +0200 (CEST)",
        "input": {
            "shop_id": "2558",
            "companyname": "Nordic Spice AB",
            "cvr": "556652-2057",
            "bill_to_address": "Hejargatan 20",
            "bill_to_postal_code": "63239",
            "bill_to_city": "Eskilstuna",
            "requisition_no": "Daniel Bj\u00f6rk",
            "bill_to_country": "SE",
            "quantity": "10",
            "expire_date": "31-12-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "SE",
            "contact_email": ""
        },
        "Timestamp": 1634021297,
        "check": {
            "check1": "1: BS60025 - 2021-10-12 08:49",
            "check2": "",
            "check3": "",
            "shopname": "24 julklappar 1200 2021",
            "lang": 5
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue, 12 Oct 2021 07:52:35 +0200 (CEST)",
        "input": {
            "shop_id": "575",
            "companyname": "Tandl\u00e6gerne i Snedsted",
            "cvr": "39114119",
            "phone": "40566468",
            "contact_phone": "40566468",
            "contact_email": "tandlaegerneisnedsted@mail.dk",
            "bill_to_address": "Baneg\u00e5rdsvej 1C",
            "bill_to_postal_code": "7752",
            "bill_to_city": "Snedsted",
            "requisition_no": "Anni Klit Broks\u00f8",
            "bill_to_country": "DK",
            "quantity": "10",
            "expire_date": "14-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK"
        },
        "Timestamp": 1634017955,
        "check": {
            "check1": "2: BS56961 - 2021-09-14 10:46",
            "check2": "",
            "check3": "",
            "shopname": "Designjulegaven",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue, 12 Oct 2021 07:44:45 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "L\u00e6gerne i Gellerup",
            "cvr": "27487165",
            "phone": "60935361",
            "contact_phone": "60935361",
            "contact_email": "trine.brogaard@dadlnet.dk",
            "bill_to_address": "City Vest, Gudrunsvej",
            "bill_to_postal_code": "8220",
            "bill_to_city": "Brabrand",
            "requisition_no": "Trine Brogaard",
            "bill_to_country": "DK",
            "quantity": "7",
            "expire_date": "31-12-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK"
        },
        "Timestamp": 1634017485,
        "check": {
            "check1": "1: BS60006 - 2021-10-12 07:45",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue, 12 Oct 2021 07:23:08 +0200 (CEST)",
        "input": {
            "shop_id": "53",
            "companyname": "Esbjerg Fiskernes Indk\u00f8b A.M.B.A",
            "cvr": "45315312",
            "bill_to_address": "Havdigevej 36",
            "bill_to_postal_code": "6700",
            "bill_to_city": "Esbjerg",
            "requisition_no": "Jan Tolstrup",
            "bill_to_country": "DK",
            "quantity": "8",
            "expire_date": "21-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1634016188,
        "check": {
            "check1": "1: BS60004 - 2021-10-12 07:23",
            "check2": "",
            "check3": "1: BS60005 - 2021-10-12 07:26",
            "shopname": "Guldgavekortet DK 800",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon, 11 Oct 2021 23:34:55 +0200 (CEST)",
        "input": {
            "shop_id": "55",
            "companyname": "Barutren ApS",
            "cvr": "30208846",
            "bill_to_address": "B\u00f8gekildevej 10D",
            "bill_to_postal_code": "8361",
            "bill_to_city": "Hasselager",
            "requisition_no": "Tine Nielsen",
            "bill_to_country": "DK",
            "quantity": "30",
            "expire_date": "07-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633988095,
        "check": {
            "check1": "1: BS59999 - 2021-10-11 23:35",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 560",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon, 11 Oct 2021 22:33:25 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "Vitakraft",
            "cvr": "14312897",
            "bill_to_address": "Hasselager centervej 7st",
            "bill_to_postal_code": "8260",
            "bill_to_city": "Viby J",
            "requisition_no": "Christian",
            "bill_to_country": "DK",
            "quantity": "18",
            "expire_date": "07-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633984405,
        "check": {
            "check1": "1: BS59997 - 2021-10-11 22:34",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon, 11 Oct 2021 22:17:45 +0200 (CEST)",
        "input": {
            "shop_id": "52",
            "companyname": "Taxi vognmand ",
            "cvr": "19878988",
            "bill_to_address": "Uls\u00f8parken 27 2 Th ",
            "bill_to_postal_code": "2660 ",
            "bill_to_city": "Br\u00f8ndby strand ",
            "requisition_no": "Suhail nazir Butt",
            "bill_to_country": "DK",
            "quantity": "6",
            "expire_date": "14-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633983465,
        "check": {
            "check1": "",
            "check2": "",
            "check3": "1: BS58000 - 2021-09-27 12:56",
            "shopname": "Julegavekortet DK",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon, 11 Oct 2021 20:23:18 +0200 (CEST)",
        "input": {
            "shop_id": "2395",
            "companyname": "Fures\u00f8 Tandl\u00e6gerne",
            "cvr": "32287344",
            "bill_to_address": "Bymidten 35A",
            "bill_to_postal_code": "3500",
            "bill_to_city": "V\u00e6rl\u00f8se",
            "requisition_no": "Hanne Lollike",
            "bill_to_country": "DK",
            "quantity": "17",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633976598,
        "check": {
            "check1": "1: BS59996 - 2021-10-11 20:24",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekortet DK 960",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon, 11 Oct 2021 20:16:42 +0200 (CEST)",
        "input": {
            "shop_id": "59",
            "companyname": "Haarknuta ",
            "cvr": "998256445",
            "bill_to_address": "Storgata 54",
            "bill_to_postal_code": "2830",
            "bill_to_city": "Raufoss",
            "requisition_no": "Nina L\u00f8nhaug",
            "bill_to_country": "NO",
            "bill_to_email": "haarknutaas@ebilag.com",
            "quantity": "5",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "NO",
            "contact_email": ""
        },
        "Timestamp": 1633976202,
        "check": {
            "check1": "1: BS59995 - 2021-10-11 20:18",
            "check2": "",
            "check3": "",
            "shopname": "Julegavekortet NO 800",
            "lang": 4
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon, 11 Oct 2021 17:33:31 +0200 (CEST)",
        "input": {
            "shop_id": "2395",
            "companyname": "Macani ApS",
            "cvr": "42327409",
            "bill_to_address": "Skovengen 5",
            "bill_to_postal_code": "2791",
            "bill_to_city": "Drag\u00f8r",
            "requisition_no": "Martin M\u00f8ller",
            "bill_to_country": "DK",
            "quantity": "5",
            "expire_date": "07-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633966411,
        "check": {
            "check1": "1: BS59992 - 2021-10-11 17:34",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekortet DK 960",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon, 11 Oct 2021 17:06:08 +0200 (CEST)",
        "input": {
            "shop_id": "53",
            "companyname": "CN Svagstromsteknik",
            "cvr": "28240694",
            "bill_to_address": "Brorsonsvej 8",
            "bill_to_postal_code": "8600",
            "bill_to_city": "Silkeborg",
            "requisition_no": "Rikke Busk",
            "bill_to_country": "DK",
            "quantity": "5",
            "expire_date": "07-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633964768,
        "check": {
            "check1": "1: BS59985 - 2021-10-11 17:06",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekortet DK 800",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_email: not a valid e-mail",
            "field": "contact_email",
            "type": "invalidemail",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon, 11 Oct 2021 16:03:27 +0200 (CEST)",
        "input": {
            "shop_id": "1981",
            "companyname": "Multimix i Trelleborg AB",
            "cvr": "556646-9739",
            "phone": "0709-212230",
            "contact_name": "Jan-Ewe Larsson",
            "contact_phone": "0709-212230",
            "contact_email": "Jan-ewe.larsson@ tta.nu",
            "bill_to_address": "Sadelv\u00e4gen 26",
            "bill_to_postal_code": "23132 ",
            "bill_to_city": "Trelleborg",
            "requisition_no": "Jan-Ewe Larsson",
            "bill_to_country": "SE",
            "quantity": "5",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "1",
            "ship_to_company": "TTA Fastighet & Sk\u00f6tsel AB",
            "ship_to_address": "Pers\u00e5kersv\u00e4gen 2",
            "ship_to_address_2": "Pers\u00e5kersv\u00e4gen 2",
            "ship_to_postal_code": "231 32",
            "ship_to_city": "Trelleborg",
            "ship_to_country": "SE",
            "giftwrap": "1"
        },
        "Timestamp": 1633961007,
        "check": {
            "check1": "",
            "check2": "",
            "check3": "1: BS59979 - 2021-10-11 16:04",
            "shopname": "24Julklappar800 (Sverige)",
            "lang": 5
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon, 11 Oct 2021 15:46:21 +0200 (CEST)",
        "input": {
            "shop_id": "2395",
            "companyname": "Thomas International Danmark AS",
            "cvr": "17155180",
            "bill_to_address": "Kirke V\u00e6rl\u00f8sevej 20, 1.sal",
            "bill_to_postal_code": "3500",
            "bill_to_city": "V\u00e6rl\u00f8se",
            "requisition_no": "Lars Madsen",
            "bill_to_country": "DK",
            "quantity": "11",
            "expire_date": "21-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633959981,
        "check": {
            "check1": "1: BS59976 - 2021-10-11 15:47",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekortet DK 960",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon, 11 Oct 2021 15:40:32 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "SvineRaadgivningen",
            "cvr": "25399781",
            "bill_to_address": "Birk Centerpark 24",
            "bill_to_postal_code": "7400",
            "bill_to_city": "Herning",
            "requisition_no": "Sofie Hyldgaard",
            "bill_to_country": "DK",
            "quantity": "21",
            "expire_date": "07-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633959632,
        "check": {
            "check1": "1: BS59973 - 2021-10-11 15:40",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon, 11 Oct 2021 14:52:16 +0200 (CEST)",
        "input": {
            "shop_id": "52",
            "companyname": "Moebelpolstrer og kalechesmeden ",
            "cvr": "39598078",
            "phone": "30278860",
            "contact_phone": "30278860",
            "contact_email": "John@m-ks.dk",
            "bill_to_address": "Esb\u00f8lvej 24, Sdr Vium ",
            "bill_to_postal_code": "6893",
            "bill_to_city": "Hemmet",
            "requisition_no": "John Jacobsen ",
            "bill_to_country": "DK",
            "quantity": "11",
            "expire_date": "14-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK"
        },
        "Timestamp": 1633956736,
        "check": {
            "check1": "",
            "check2": "",
            "check3": "1: BS59959 - 2021-10-11 14:52",
            "shopname": "Julegavekortet DK",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon, 11 Oct 2021 14:50:36 +0200 (CEST)",
        "input": {
            "shop_id": "55",
            "companyname": "EINFACH ApS",
            "cvr": "32784968",
            "ean": "5790002430886",
            "bill_to_address": "Mariendalsvej 11",
            "bill_to_postal_code": "8800",
            "bill_to_city": "Viborg",
            "requisition_no": "Grith H\u00f8egmark",
            "bill_to_country": "DK",
            "quantity": "11",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633956636,
        "check": {
            "check1": "1: BS59963 - 2021-10-11 14:56",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 560",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon, 11 Oct 2021 14:18:03 +0200 (CEST)",
        "input": {
            "shop_id": "2558",
            "companyname": "Megacon AB",
            "cvr": "556221-2281",
            "bill_to_address": "Ranhammarsv\u00e4gen 20",
            "bill_to_postal_code": "16867",
            "bill_to_city": "Bromma",
            "requisition_no": "Georg Stockhaus",
            "bill_to_country": "SE",
            "quantity": "10",
            "expire_date": "31-12-2022",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "SE",
            "giftwrap": "1",
            "contact_email": ""
        },
        "Timestamp": 1633954683,
        "check": {
            "check1": "1: BS59949 - 2021-10-11 14:18",
            "check2": "",
            "check3": "",
            "shopname": "24 julklappar 1200 2021",
            "lang": 5
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon, 11 Oct 2021 14:13:48 +0200 (CEST)",
        "input": {
            "shop_id": "52",
            "companyname": "Nordisk Aktuarservice ApS",
            "cvr": "32284558",
            "bill_to_address": "N\u00f8rre Voldgade 9, 1th.",
            "bill_to_postal_code": "1358",
            "bill_to_city": "K\u00f8benhavn K",
            "requisition_no": "Charlotte Wiese",
            "bill_to_country": "DK",
            "quantity": "10",
            "expire_date": "31-10-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633954428,
        "check": {
            "check1": "1: BS59947 - 2021-10-11 14:14",
            "check2": "",
            "check3": "",
            "shopname": "Julegavekortet DK",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon, 11 Oct 2021 14:09:38 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "Revisionsfirmaet Joergen Loebner ApS",
            "cvr": "29538565",
            "bill_to_address": "N\u00f8rregade 14",
            "bill_to_postal_code": "8850",
            "bill_to_city": "Bjerringbro",
            "requisition_no": "Birgitte L\u00f8bner",
            "bill_to_country": "DK",
            "quantity": "12",
            "expire_date": "21-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633954178,
        "check": {
            "check1": "1: BS59943 - 2021-10-11 14:11",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field cvr: value is required",
            "field": "cvr",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon, 11 Oct 2021 14:04:08 +0200 (CEST)",
        "input": {
            "shop_id": "54",
            "companyname": "Jejsing b\u00f8rneUnivers",
            "ean": "5798005025801",
            "bill_to_address": "k\u00e6rvej 22 Jejsing",
            "bill_to_postal_code": "6270",
            "bill_to_city": "T\u00f8nder",
            "requisition_no": "Birthe Thomsen",
            "bill_to_country": "DK",
            "quantity": "9",
            "expire_date": "07-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "cvr": "",
            "contact_email": ""
        },
        "Timestamp": 1633953848,
        "check": {
            "check1": "*",
            "check2": "*",
            "check3": "*",
            "shopname": "24 Gaver DK 400",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon, 11 Oct 2021 13:57:28 +0200 (CEST)",
        "input": {
            "shop_id": "53",
            "companyname": "Pajbjergfonden",
            "cvr": "18149028",
            "phone": "40605400",
            "contact_phone": "40605400",
            "contact_email": "michaelstevns@hotmail.com",
            "bill_to_address": "Grindsnabevej 25",
            "bill_to_postal_code": "8300",
            "bill_to_city": "Odder",
            "requisition_no": "Ole Lykke",
            "bill_to_country": "DK",
            "quantity": "5",
            "expire_date": "07-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK"
        },
        "Timestamp": 1633953448,
        "check": {
            "check1": "1: BS59936 - 2021-10-11 13:58",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekortet DK 800",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon, 11 Oct 2021 13:49:35 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "Bof\u00e6llesskabet Kirsten Marie",
            "cvr": "32511759",
            "phone": "28694355",
            "contact_phone": "28694355",
            "contact_email": "vp@mariehjem.dk",
            "bill_to_address": "Vinkelvej 3-5",
            "bill_to_postal_code": "2800",
            "bill_to_city": "Kongens Lyngby",
            "bill_to_country": "DK",
            "quantity": "35",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "1",
            "ship_to_company": "Bof\u00e6llesskabet Kirsten Marie",
            "ship_to_address": "Vinkelvej 3-5",
            "ship_to_address_2": "Vinkelvej 3-5",
            "ship_to_postal_code": "2800",
            "ship_to_city": "Kongens Lyngby",
            "ship_to_country": "DK"
        },
        "Timestamp": 1633952975,
        "check": {
            "check1": "1: BS59931 - 2021-10-11 13:50",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon, 11 Oct 2021 13:46:50 +0200 (CEST)",
        "input": {
            "shop_id": "2550",
            "companyname": "Conturo AS",
            "cvr": "988 829 951",
            "bill_to_address": "Kornmagasingata 1",
            "bill_to_postal_code": "3160",
            "bill_to_city": "Stokke",
            "requisition_no": "Anita Romie Hoff",
            "bill_to_country": "NO",
            "bill_to_email": "faktura@conturo.no",
            "quantity": "26",
            "expire_date": "07-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "NO",
            "giftwrap": "1",
            "contact_email": ""
        },
        "Timestamp": 1633952810,
        "check": {
            "check1": "1: BS59930 - 2021-10-11 13:47",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekort 1200 (NO)",
            "lang": 4
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field ship_to_company: string must be longer than 50 characters",
            "field": "ship_to_company",
            "type": "toolong",
            "min": "50",
            "max": "",
            "length": ""
        },
        "Date": " Mon, 11 Oct 2021 13:43:24 +0200 (CEST)",
        "input": {
            "shop_id": "2548",
            "companyname": "Foreningen Erhvervsmaeglerbasen FMBA",
            "cvr": "24255328",
            "phone": "61517444",
            "contact_name": "Jakob Wegener",
            "contact_phone": "61517444",
            "contact_email": "jw@ejendomstorvet.dk",
            "bill_to_address": "Kompagnistr\u00e6de 20B, 2. sal",
            "bill_to_postal_code": "1208",
            "bill_to_city": "K\u00f8benhavn K",
            "requisition_no": "Jakob Wegener",
            "bill_to_country": "DK",
            "quantity": "6",
            "expire_date": "14-11-2021",
            "is_email": "1",
            "use_shipping_address": "1",
            "ship_to_company": "Ejendomstorvet Foreningen Erhvervsmaeglerbasen FMBA",
            "ship_to_address": "Kompagnistr\u00e6de 20B, 2. sal",
            "ship_to_address_2": "Kompagnistr\u00e6de 20B, 2. sal",
            "ship_to_postal_code": "1208",
            "ship_to_city": "K\u00f8benhavn K",
            "ship_to_country": "DK",
            "giftwrap": "1"
        },
        "Timestamp": 1633952604,
        "check": {
            "check1": "",
            "check2": "1: BS60136 - 2021-10-12 12:42",
            "check3": "",
            "shopname": "Det gr\u00f8nne gavekort",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon, 11 Oct 2021 13:31:12 +0200 (CEST)",
        "input": {
            "shop_id": "310",
            "companyname": "Aktivitetshuset Knudepunktet",
            "cvr": "29189838",
            "ean": "5798005407607",
            "bill_to_address": "K\u00e6rh\u00f8jparken 19",
            "bill_to_postal_code": "6600",
            "bill_to_city": "Vejen",
            "requisition_no": "Mette Gr\u00f8nning Lange",
            "bill_to_country": "DK",
            "quantity": "20",
            "expire_date": "07-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633951872,
        "check": {
            "check1": "1: BS59927 - 2021-10-11 13:32",
            "check2": "",
            "check3": "5: BS56778 - 2021-09-10 10:09",
            "shopname": "Dr\u00f8mmegavekortet 300",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon, 11 Oct 2021 13:29:25 +0200 (CEST)",
        "input": {
            "shop_id": "55",
            "companyname": "Brodkunsten",
            "cvr": "27308937",
            "phone": "35391739",
            "contact_phone": "35391739",
            "contact_email": "info@brodkunsten.dk",
            "bill_to_address": "Jagtvej 94",
            "bill_to_postal_code": "2200",
            "bill_to_city": "K\u00f8benhavn N",
            "requisition_no": "Helle Nielsen",
            "bill_to_country": "DK",
            "quantity": "8",
            "expire_date": "01-04-2022",
            "is_email": "0",
            "use_shipping_address": "1",
            "ship_to_company": "Br\u00f8dkunsten",
            "ship_to_address": "Jagtvej 94",
            "ship_to_address_2": "Jagtvej 94",
            "ship_to_postal_code": "2200",
            "ship_to_city": "K\u00f8benhavn N",
            "ship_to_country": "DK"
        },
        "Timestamp": 1633951765,
        "check": {
            "check1": "1: BS59926 - 2021-10-11 13:29",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 560",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon, 11 Oct 2021 13:24:44 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "FOF K\u00f8benhavn",
            "cvr": "11707513",
            "bill_to_address": "Humletorvet 27, 2. sal",
            "bill_to_postal_code": "1799",
            "bill_to_city": "K\u00f8benhavn V",
            "requisition_no": "Anja Refstrup Lyng",
            "bill_to_country": "DK",
            "quantity": "37",
            "expire_date": "21-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633951484,
        "check": {
            "check1": "1: BS59922 - 2021-10-11 13:26",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field companyname: value is required",
            "field": "companyname",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon, 11 Oct 2021 13:10:38 +0200 (CEST)",
        "input": {
            "shop_id": "1832",
            "bill_to_country": "SE",
            "quantity": "20",
            "expire_date": "31-12-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "SE",
            "cvr": "",
            "contact_email": ""
        },
        "Timestamp": 1633950638,
        "check": {
            "check1": "*",
            "check2": "*",
            "check3": "*",
            "shopname": "24Julklappar400 (Sverige)",
            "lang": 5
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon, 11 Oct 2021 13:05:14 +0200 (CEST)",
        "input": {
            "shop_id": "54",
            "companyname": "Flex Wind ApS",
            "cvr": "39191903",
            "bill_to_address": "Hjortsvangen 26",
            "bill_to_postal_code": "7323",
            "bill_to_city": "Give",
            "requisition_no": "Lena Riisgaard",
            "bill_to_country": "DK",
            "quantity": "26",
            "expire_date": "07-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633950314,
        "check": {
            "check1": "1: BS59917 - 2021-10-11 13:06",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 400",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon, 11 Oct 2021 12:54:23 +0200 (CEST)",
        "input": {
            "shop_id": "2558",
            "companyname": "Tandlakarna Lilla Torg",
            "cvr": "5562686765",
            "bill_to_address": "D\u00f6belnsgatan 7",
            "bill_to_postal_code": "29131",
            "bill_to_city": "Kristianstad",
            "requisition_no": "Anders Annerfelt",
            "bill_to_country": "SE",
            "quantity": "12",
            "expire_date": "31-12-2022",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "SE",
            "giftwrap": "1",
            "contact_email": ""
        },
        "Timestamp": 1633949663,
        "check": {
            "check1": "1: BS59914 - 2021-10-11 12:54",
            "check2": "",
            "check3": "",
            "shopname": "24 julklappar 1200 2021",
            "lang": 5
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon, 11 Oct 2021 12:50:36 +0200 (CEST)",
        "input": {
            "shop_id": "2550",
            "companyname": "Yinson Production AS",
            "cvr": "930366323",
            "bill_to_address": "Kronprinsesse M\u00e4rthas plass 1",
            "bill_to_postal_code": "0160",
            "bill_to_city": "Oslo",
            "requisition_no": "Nicolai M\u00f8nster",
            "bill_to_country": "NO",
            "bill_to_email": "nicolai.monster@yinson.com",
            "quantity": "18",
            "expire_date": "07-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "NO",
            "giftwrap": "1",
            "contact_email": ""
        },
        "Timestamp": 1633949436,
        "check": {
            "check1": "5: BS59909 - 2021-10-11 12:50",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekort 1200 (NO)",
            "lang": 4
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon, 11 Oct 2021 12:41:00 +0200 (CEST)",
        "input": {
            "shop_id": "2395",
            "companyname": "EK Entreprise A\/S",
            "cvr": "10798590",
            "bill_to_address": "Kuldyssen 8 ",
            "bill_to_postal_code": "2630",
            "bill_to_city": "Taastrup",
            "requisition_no": "Rasmus Johansen",
            "bill_to_country": "DK",
            "quantity": "27",
            "expire_date": "07-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633948860,
        "check": {
            "check1": "1: BS59908 - 2021-10-11 12:41",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekortet DK 960",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon, 11 Oct 2021 12:40:23 +0200 (CEST)",
        "input": {
            "shop_id": "2550",
            "companyname": "Yinson Production AS",
            "cvr": "930366323",
            "bill_to_address": "Kronprinsesse M\u00e4rthas plass 1",
            "bill_to_postal_code": "0160",
            "bill_to_city": "Oslo",
            "requisition_no": "Nicolai M\u00f8nster",
            "bill_to_country": "NO",
            "bill_to_email": "nicolai.monster@yinson.com",
            "quantity": "15",
            "expire_date": "07-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "NO",
            "giftwrap": "1",
            "contact_email": ""
        },
        "Timestamp": 1633948823,
        "check": {
            "check1": "5: BS59909 - 2021-10-11 12:50",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekort 1200 (NO)",
            "lang": 4
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon, 11 Oct 2021 12:31:26 +0200 (CEST)",
        "input": {
            "shop_id": "53",
            "companyname": "Home Boegelund og Friis",
            "cvr": "36198648",
            "contact_email": "larsb@home.dk",
            "bill_to_address": "Smidstrup Strandvej 69",
            "bill_to_postal_code": "3250",
            "bill_to_city": "Gilleleje",
            "requisition_no": "Lars B\u00f8gelund",
            "bill_to_country": "DK",
            "quantity": "15",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK"
        },
        "Timestamp": 1633948286,
        "check": {
            "check1": "",
            "check2": "",
            "check3": "1: BS59907 - 2021-10-11 12:32",
            "shopname": "Guldgavekortet DK 800",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon, 11 Oct 2021 12:30:14 +0200 (CEST)",
        "input": {
            "shop_id": "272",
            "companyname": "Oticon AS",
            "cvr": "818885032",
            "bill_to_address": "Hegdehaugsveien 31",
            "bill_to_postal_code": "0352 ",
            "bill_to_city": "Oslo",
            "requisition_no": "Marthe Marlene Dehli",
            "bill_to_country": "NO",
            "bill_to_email": "regnskap@demant.com",
            "quantity": "30",
            "expire_date": "07-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "NO",
            "giftwrap": "1",
            "contact_email": ""
        },
        "Timestamp": 1633948214,
        "check": {
            "check1": "1: BS59906 - 2021-10-11 12:31",
            "check2": "",
            "check3": "",
            "shopname": "Julegavekortet NO 300",
            "lang": 4
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon, 11 Oct 2021 12:29:53 +0200 (CEST)",
        "input": {
            "shop_id": "53",
            "companyname": "Home B\u00f8gelund & Friis A\/S",
            "cvr": "36198648",
            "bill_to_address": "Smidstrup Strandvej 69",
            "bill_to_postal_code": "3250",
            "bill_to_city": "Gilleleje",
            "requisition_no": "Lars B\u00f8gelund",
            "bill_to_country": "DK",
            "quantity": "15",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633948193,
        "check": {
            "check1": "1: BS59907 - 2021-10-11 12:32",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekortet DK 800",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon, 11 Oct 2021 12:19:57 +0200 (CEST)",
        "input": {
            "shop_id": "2395",
            "companyname": "DSI Hedegaard",
            "cvr": "30293363",
            "phone": "26376200",
            "contact_phone": "26376200",
            "contact_email": "hanne@botilbudhedegaard.dk",
            "bill_to_address": "Holstebrovej 92",
            "bill_to_postal_code": "6900",
            "bill_to_city": "Skjern",
            "requisition_no": "Hanne H\u00f8jgaard",
            "bill_to_country": "DK",
            "quantity": "18",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK"
        },
        "Timestamp": 1633947597,
        "check": {
            "check1": "1: BS59901 - 2021-10-11 12:20",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekortet DK 960",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon, 11 Oct 2021 12:01:28 +0200 (CEST)",
        "input": {
            "shop_id": "59",
            "companyname": "PharmaLex Norway AS",
            "cvr": "887079862",
            "bill_to_address": "Karoline Kristiansens vei 1",
            "bill_to_postal_code": "0661",
            "bill_to_city": "Oslo",
            "requisition_no": "Hanne Western",
            "bill_to_country": "NO",
            "bill_to_email": "pharmalex.norway@faktura.poweroffice.net",
            "quantity": "12",
            "expire_date": "07-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "NO",
            "contact_email": ""
        },
        "Timestamp": 1633946488,
        "check": {
            "check1": "1: BS59897 - 2021-10-11 12:02",
            "check2": "",
            "check3": "",
            "shopname": "Julegavekortet NO 800",
            "lang": 4
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon, 11 Oct 2021 11:59:13 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "Glostrup Andelsboligforening",
            "cvr": "52991315",
            "bill_to_address": "Dalvangsvej 50A",
            "bill_to_postal_code": "2600",
            "bill_to_city": "Vallensb\u00e6k",
            "requisition_no": "Tina Tonning",
            "bill_to_country": "DK",
            "quantity": "15",
            "expire_date": "21-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633946353,
        "check": {
            "check1": "1: BS59896 - 2021-10-11 11:59",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon, 11 Oct 2021 11:52:41 +0200 (CEST)",
        "input": {
            "shop_id": "575",
            "companyname": "Laege Lisbeth Falkenberg",
            "cvr": "32412843",
            "bill_to_address": "Kompagnigade 3,1",
            "bill_to_postal_code": "7800",
            "bill_to_city": "Skive",
            "requisition_no": "Lisbeth Falkenberg",
            "bill_to_country": "DK",
            "quantity": "5",
            "expire_date": "14-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633945961,
        "check": {
            "check1": "",
            "check2": "",
            "check3": "",
            "shopname": "Designjulegaven",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon, 11 Oct 2021 11:15:46 +0200 (CEST)",
        "input": {
            "shop_id": "2550",
            "companyname": "Nok Sogn og Fjordane",
            "cvr": "998382726",
            "bill_to_address": "Hornnesvegen 1",
            "bill_to_postal_code": "6809",
            "bill_to_city": "F\u00f8rde",
            "requisition_no": "Chatrine Elholm",
            "bill_to_country": "NO",
            "bill_to_email": "chatrine@noksognogfjordane.no",
            "quantity": "5",
            "expire_date": "21-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "NO",
            "giftwrap": "1",
            "contact_email": ""
        },
        "Timestamp": 1633943746,
        "check": {
            "check1": "1: BS59883 - 2021-10-11 11:19",
            "check2": "",
            "check3": "1: BS59885 - 2021-10-11 11:22",
            "shopname": "Guldgavekort 1200 (NO)",
            "lang": 4
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon, 11 Oct 2021 10:59:13 +0200 (CEST)",
        "input": {
            "shop_id": "574",
            "companyname": "Promotek AS",
            "cvr": "995411989",
            "bill_to_address": "Hunsfos N\u00e6ringspark",
            "bill_to_postal_code": "4700",
            "bill_to_city": "Vennesla",
            "requisition_no": "Julegaver",
            "bill_to_country": "NO",
            "bill_to_email": "bilag@promotek.no",
            "quantity": "7",
            "expire_date": "21-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "NO",
            "contact_email": ""
        },
        "Timestamp": 1633942753,
        "check": {
            "check1": "1: BS59881 - 2021-10-11 11:01",
            "check2": "1: BS59910 - 2021-10-11 12:51",
            "check3": "",
            "shopname": "Guldgavekort NO",
            "lang": 4
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon, 11 Oct 2021 10:57:45 +0200 (CEST)",
        "input": {
            "shop_id": "52",
            "companyname": "DKK Engros",
            "cvr": "34737347",
            "bill_to_address": "Roholmsvej 17D",
            "bill_to_postal_code": "2620",
            "bill_to_city": "Albertslund",
            "requisition_no": "Elin Truong Quach",
            "bill_to_country": "DK",
            "quantity": "14",
            "expire_date": "14-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633942665,
        "check": {
            "check1": "1: BS59879 - 2021-10-11 10:58",
            "check2": "",
            "check3": "",
            "shopname": "Julegavekortet DK",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon, 11 Oct 2021 10:53:06 +0200 (CEST)",
        "input": {
            "shop_id": "53",
            "companyname": "IT2Trust ",
            "cvr": "33258305",
            "phone": "20221972",
            "contact_phone": "20221972",
            "contact_email": "atm@it2trust.com",
            "bill_to_address": "Roskildevej 522, Kinnarps bygningen",
            "bill_to_postal_code": "2605",
            "bill_to_city": "Br\u00f8ndby",
            "requisition_no": "Anders Mortensen",
            "bill_to_country": "DK",
            "quantity": "9",
            "expire_date": "21-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK"
        },
        "Timestamp": 1633942386,
        "check": {
            "check1": "1: BS59874 - 2021-10-11 10:54",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekortet DK 800",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon, 11 Oct 2021 10:51:23 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "Scanregn A\/S",
            "cvr": "19611302",
            "bill_to_address": "Thorsvej 105",
            "bill_to_postal_code": "7200",
            "bill_to_city": "Grindsted",
            "requisition_no": "Linda Vangsgaard",
            "bill_to_country": "DK",
            "quantity": "19",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633942283,
        "check": {
            "check1": "1: BS59873 - 2021-10-11 10:52",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon, 11 Oct 2021 10:50:30 +0200 (CEST)",
        "input": {
            "shop_id": "1832",
            "companyname": "Creative Concept Nordic AB",
            "cvr": "5566-356779",
            "bill_to_address": "Eskaderv\u00e4gen 2-4, 4 tr",
            "bill_to_postal_code": "18354",
            "bill_to_city": "T\u00e4by",
            "requisition_no": "Anne-Grete Molnes Thoren",
            "bill_to_country": "SE",
            "quantity": "10",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "SE",
            "giftwrap": "1",
            "contact_email": ""
        },
        "Timestamp": 1633942230,
        "check": {
            "check1": "1: BS59872 - 2021-10-11 10:51",
            "check2": "",
            "check3": "",
            "shopname": "24Julklappar400 (Sverige)",
            "lang": 5
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon, 11 Oct 2021 10:45:07 +0200 (CEST)",
        "input": {
            "shop_id": "52",
            "companyname": "V og Co Revision",
            "cvr": "34622310",
            "bill_to_address": "Smakkeg\u00e5rdsvej 217",
            "bill_to_postal_code": "2820",
            "bill_to_city": "Gentofte",
            "requisition_no": "Thomas Viscovich",
            "bill_to_country": "DK",
            "quantity": "5",
            "expire_date": "14-11-2021",
            "is_email": "0",
            "use_shipping_address": "1",
            "ship_to_company": "V og Co Revision",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633941907,
        "check": {
            "check1": "1: BS59870 - 2021-10-11 10:45",
            "check2": "",
            "check3": "",
            "shopname": "Julegavekortet DK",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon, 11 Oct 2021 10:27:18 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "Pro-ex ApS",
            "cvr": "39140063",
            "bill_to_address": "Halkj\u00e6rvej 14",
            "bill_to_postal_code": "9200",
            "bill_to_city": "Aalborg SV",
            "requisition_no": "Linda Nielsen - 51 35 35 94",
            "bill_to_country": "DK",
            "quantity": "9",
            "expire_date": "07-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633940838,
        "check": {
            "check1": "",
            "check2": "",
            "check3": "3: BS60057 - 2021-10-12 09:49",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "30",
            "error": "Could not find valid shop_id"
        },
        "Date": " Mon, 11 Oct 2021 10:16:55 +0200 (CEST)",
        "input": {
            "companyname": "Pia Roed Aps ",
            "cvr": "25286642",
            "phone": "22844347",
            "contact_name": "Pia Roed",
            "contact_phone": "22844347",
            "contact_email": "pia@piaroed.dk",
            "bill_to_address": "Fris\u00f8r Pia Roed, Adelgade 128",
            "bill_to_postal_code": "8660",
            "bill_to_city": "Skanderborg",
            "requisition_no": "Pia Roed ",
            "bill_to_country": "DK",
            "quantity": "8",
            "expire_date": "31-10-2021",
            "is_email": "1",
            "use_shipping_address": "1",
            "ship_to_company": "Fris\u00f8r Pia Roed",
            "ship_to_address": "Fris\u00f8r Pia Roed, Adelgade 128",
            "ship_to_address_2": "Fris\u00f8r Pia Roed, Adelgade 128",
            "ship_to_postal_code": "8660",
            "ship_to_city": "Skanderborg",
            "ship_to_country": "DK",
            "shop_id": ""
        },
        "Timestamp": 1633940215,
        "check": {
            "check1": "1: BS59865 - 2021-10-11 10:17",
            "check2": "",
            "check3": "",
            "shopname": "Ukendt",
            "lang": ""
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon, 11 Oct 2021 10:12:41 +0200 (CEST)",
        "input": {
            "shop_id": "575",
            "companyname": "Toemrerfirmaet Broedrene Boergesen ApS",
            "cvr": "36044519",
            "bill_to_address": "Holmehaven 75",
            "bill_to_postal_code": "2670",
            "bill_to_city": "Greve",
            "requisition_no": "Camilla Manniche",
            "bill_to_country": "DK",
            "quantity": "14",
            "expire_date": "14-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633939961,
        "check": {
            "check1": "1: BS59864 - 2021-10-11 10:13",
            "check2": "",
            "check3": "",
            "shopname": "Designjulegaven",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon, 11 Oct 2021 10:03:04 +0200 (CEST)",
        "input": {
            "shop_id": "52",
            "companyname": "JVS T\u00f8mrer A\/S",
            "cvr": "40523413",
            "bill_to_address": "kantatevej 30 th",
            "bill_to_postal_code": "2730",
            "bill_to_city": "herlev",
            "requisition_no": "Julegaver",
            "bill_to_country": "DK",
            "quantity": "18",
            "expire_date": "14-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633939384,
        "check": {
            "check1": "1: BS59860 - 2021-10-11 10:04",
            "check2": "",
            "check3": "",
            "shopname": "Julegavekortet DK",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field ship_to_company: string must be at least 3 characters",
            "field": "ship_to_company",
            "type": "tooshort",
            "min": "3",
            "max": "",
            "length": ""
        },
        "Date": " Mon, 11 Oct 2021 10:00:28 +0200 (CEST)",
        "input": {
            "shop_id": "55",
            "companyname": "AE",
            "cvr": "31445418",
            "phone": "22593898",
            "contact_name": "Malene Michelsen",
            "contact_phone": "22593898",
            "contact_email": "mmi@ae.dk",
            "bill_to_address": "Reventlowsgade 14, 1.",
            "bill_to_postal_code": "1651",
            "bill_to_city": "K\u00f8benhavn V",
            "requisition_no": "Malene Michelsen",
            "bill_to_country": "DK",
            "quantity": "20",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "1",
            "ship_to_company": "AE",
            "ship_to_address": "Reventlowsgade 14, 1. ",
            "ship_to_address_2": "Reventlowsgade 14, 1. ",
            "ship_to_postal_code": "1651",
            "ship_to_city": "K\u00f8benhavn V",
            "ship_to_country": "DK"
        },
        "Timestamp": 1633939228,
        "check": {
            "check1": "",
            "check2": "1: BS60315 - 2021-10-13 10:36",
            "check3": "",
            "shopname": "24 Gaver DK 560",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon, 11 Oct 2021 09:36:40 +0200 (CEST)",
        "input": {
            "shop_id": "2395",
            "companyname": "Collektion Mode ApS",
            "cvr": "40934480",
            "bill_to_address": "Vestergade 9",
            "bill_to_postal_code": "8620",
            "bill_to_city": "Kjellerup ",
            "requisition_no": "Tove Handberg Pedersen ",
            "bill_to_country": "DK",
            "quantity": "9",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633937800,
        "check": {
            "check1": "1: BS59856 - 2021-10-11 09:38",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekortet DK 960",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon, 11 Oct 2021 09:25:47 +0200 (CEST)",
        "input": {
            "shop_id": "52",
            "companyname": "Klippeg\u00e5rden ApS",
            "cvr": "41517042",
            "bill_to_address": "Nytorv 8B",
            "bill_to_postal_code": "4200",
            "bill_to_city": "Slagelse",
            "requisition_no": "May Airas",
            "bill_to_country": "DK",
            "quantity": "5",
            "expire_date": "14-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633937147,
        "check": {
            "check1": "1: BS59851 - 2021-10-11 09:28",
            "check2": "",
            "check3": "",
            "shopname": "Julegavekortet DK",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "30",
            "error": "Could not find valid shop_id"
        },
        "Date": " Mon, 11 Oct 2021 08:47:03 +0200 (CEST)",
        "input": {
            "companyname": "buchsherremagasin",
            "cvr": "35975799",
            "phone": "50803540",
            "contact_name": "morten",
            "contact_phone": "50803540",
            "contact_email": "mail@buchsherremagasin.dk",
            "bill_to_address": "n\u00f8rregade, 31",
            "bill_to_postal_code": "7100",
            "bill_to_city": "VEJLE",
            "requisition_no": "mb",
            "bill_to_country": "DK",
            "quantity": "5",
            "expire_date": "14-11-2021",
            "is_email": "0",
            "use_shipping_address": "1",
            "ship_to_company": "buchs herremagasin DP NR 5611",
            "ship_to_address": "n\u00f8rregade 31, 31",
            "ship_to_address_2": "n\u00f8rregade 31, 31",
            "ship_to_postal_code": "7100",
            "ship_to_city": "Vejle",
            "ship_to_country": "DK",
            "shop_id": ""
        },
        "Timestamp": 1633934823,
        "check": {
            "check1": "1: BS59839 - 2021-10-11 08:47",
            "check2": "",
            "check3": "",
            "shopname": "Ukendt",
            "lang": ""
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon, 11 Oct 2021 07:12:30 +0200 (CEST)",
        "input": {
            "shop_id": "310",
            "companyname": "Danhostel Ishoj strand",
            "cvr": "13510806",
            "bill_to_address": "Ish\u00f8j Strandvej 13",
            "bill_to_postal_code": "2635",
            "bill_to_city": "Ish\u00f8j",
            "requisition_no": "Anette Greve Jacobsen ",
            "bill_to_country": "DK",
            "quantity": "5",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633929150,
        "check": {
            "check1": "1: BS59836 - 2021-10-11 07:12",
            "check2": "",
            "check3": "2: BS59834 - 2021-10-11 07:01",
            "shopname": "Dr\u00f8mmegavekortet 300",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field requisition_no: string must be longer than 30 characters",
            "field": "requisition_no",
            "type": "toolong",
            "min": "30",
            "max": "",
            "length": ""
        },
        "Date": " Mon, 11 Oct 2021 00:00:33 +0200 (CEST)",
        "input": {
            "shop_id": "54",
            "companyname": "Soeborgmagle Kirke",
            "cvr": "64956728",
            "phone": "42717272",
            "contact_name": "Bodil Meier Andersen",
            "contact_phone": "42717272",
            "contact_email": "Boanmean@gmail.com",
            "bill_to_address": "Gr\u00f8nnemose Alle 77",
            "bill_to_postal_code": "2860",
            "bill_to_city": "S\u00f8borg",
            "requisition_no": "Kontaktperson: Bodil Meier Andersen",
            "bill_to_country": "DK",
            "quantity": "10",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "1",
            "ship_to_company": "Soeborgmagle Kirke",
            "ship_to_address": "Gr\u00f8nnemose All\u00e9 77",
            "ship_to_address_2": "Gr\u00f8nnemose All\u00e9 77",
            "ship_to_postal_code": "2860",
            "ship_to_city": "S\u00f8borg",
            "ship_to_country": "DK"
        },
        "Timestamp": 1633903233,
        "check": {
            "check1": "",
            "check2": "1: BS59867 - 2021-10-11 10:26",
            "check3": "",
            "shopname": "24 Gaver DK 400",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Sun, 10 Oct 2021 23:24:42 +0200 (CEST)",
        "input": {
            "shop_id": "52",
            "companyname": "MY GARAGE",
            "cvr": "36953217",
            "bill_to_address": "Wittrupvej 1",
            "bill_to_postal_code": "7120",
            "bill_to_city": "Vejle \u00d8",
            "requisition_no": "Louise Rasmussen",
            "bill_to_country": "DK",
            "quantity": "35",
            "expire_date": "14-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633901082,
        "check": {
            "check1": "1: BS59833 - 2021-10-10 23:24",
            "check2": "",
            "check3": "",
            "shopname": "Julegavekortet DK",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Sun, 10 Oct 2021 18:29:45 +0200 (CEST)",
        "input": {
            "shop_id": "53",
            "companyname": "L\u00e6gehuset Hedehusene",
            "cvr": "30294467",
            "phone": "27608882",
            "contact_phone": "27608882",
            "contact_email": "amha@dadlnet.dk",
            "bill_to_address": "Hovedgaden 514 B, 1.",
            "bill_to_postal_code": "2640",
            "bill_to_city": "Hedehusene",
            "requisition_no": "Mette Holm Andersen",
            "bill_to_country": "DK",
            "quantity": "11",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK"
        },
        "Timestamp": 1633883385,
        "check": {
            "check1": "1: BS59831 - 2021-10-10 18:30",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekortet DK 800",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Sun, 10 Oct 2021 17:49:29 +0200 (CEST)",
        "input": {
            "shop_id": "2395",
            "companyname": "Gulvmanden JC",
            "cvr": "30858042",
            "bill_to_address": "Mosevej 14",
            "bill_to_postal_code": "4200",
            "bill_to_city": "Slagelse",
            "requisition_no": "John Christensen",
            "bill_to_country": "DK",
            "quantity": "11",
            "expire_date": "07-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633880969,
        "check": {
            "check1": "1: BS59829 - 2021-10-10 17:50",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekortet DK 960",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Sun, 10 Oct 2021 12:39:43 +0200 (CEST)",
        "input": {
            "shop_id": "2395",
            "companyname": "Tandl\u00e6gerne i Skibby APS  CVR: 32348769",
            "cvr": "32348769",
            "bill_to_address": "Hovedgaden 31, 31",
            "bill_to_postal_code": "4050",
            "bill_to_city": "Skibby",
            "bill_to_country": "DK",
            "quantity": "13",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633862383,
        "check": {
            "check1": "1: BS59826 - 2021-10-10 12:40",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekortet DK 960",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Sun, 10 Oct 2021 11:28:55 +0200 (CEST)",
        "input": {
            "shop_id": "2558",
            "companyname": "Karlstad Redskap AB",
            "cvr": "556292-4034",
            "bill_to_address": "Teknikv\u00e4gen 1",
            "bill_to_postal_code": "66452",
            "bill_to_city": "V\u00e5lberg",
            "requisition_no": "Malin",
            "bill_to_country": "SE",
            "quantity": "5",
            "expire_date": "21-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "SE",
            "contact_email": ""
        },
        "Timestamp": 1633858135,
        "check": {
            "check1": "1: BS59824 - 2021-10-10 11:29",
            "check2": "",
            "check3": "",
            "shopname": "24 julklappar 1200 2021",
            "lang": 5
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field cvr: string must be at least 8 characters",
            "field": "cvr",
            "type": "tooshort",
            "min": "8",
            "max": "",
            "length": ""
        },
        "Date": " Sun, 10 Oct 2021 08:50:57 +0200 (CEST)",
        "input": {
            "shop_id": "575",
            "companyname": "Landinspekt\u00f8rfirmaet Ejendomsret ApS",
            "cvr": "383736",
            "bill_to_address": "Laksev\u00e5gen 7",
            "bill_to_postal_code": "9400",
            "bill_to_city": "N\u00f8rresundby",
            "requisition_no": "Julegaver",
            "bill_to_country": "DK",
            "quantity": "12",
            "expire_date": "31-10-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "giftwrap": "1",
            "contact_email": ""
        },
        "Timestamp": 1633848657,
        "check": {
            "check1": "",
            "check2": "",
            "check3": "",
            "shopname": "Designjulegaven",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Sat,  9 Oct 2021 20:03:53 +0200 (CEST)",
        "input": {
            "shop_id": "52",
            "companyname": "S T Regnskab ",
            "cvr": "34812187",
            "bill_to_address": "Langegade 5",
            "bill_to_postal_code": "5900",
            "bill_to_city": "Rudk\u00f8bing",
            "requisition_no": "Stephanie Terndrup",
            "bill_to_country": "DK",
            "quantity": "6",
            "expire_date": "31-10-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633802633,
        "check": {
            "check1": "1: BS59817 - 2021-10-09 20:05",
            "check2": "",
            "check3": "",
            "shopname": "Julegavekortet DK",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Sat,  9 Oct 2021 18:44:50 +0200 (CEST)",
        "input": {
            "shop_id": "2558",
            "companyname": "Dine Bygg och Fastighetsservice AB",
            "cvr": "556058-4367",
            "bill_to_address": "Eckragatan 7",
            "bill_to_postal_code": "426 76",
            "bill_to_city": "V-Fr\u00f6lunda",
            "requisition_no": "Morgan Dinesen",
            "bill_to_country": "SE",
            "quantity": "10",
            "expire_date": "31-12-2022",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "SE",
            "contact_email": ""
        },
        "Timestamp": 1633797890,
        "check": {
            "check1": "",
            "check2": "",
            "check3": "",
            "shopname": "24 julklappar 1200 2021",
            "lang": 5
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Sat,  9 Oct 2021 16:38:18 +0200 (CEST)",
        "input": {
            "shop_id": "54",
            "companyname": "Ma Consulting",
            "cvr": "38674374",
            "bill_to_address": "Ravnek\u00e6rsvej 14",
            "bill_to_postal_code": "2870",
            "bill_to_city": "Dysseg\u00e5rd",
            "requisition_no": "Mette Agnete Fessel",
            "bill_to_country": "DK",
            "quantity": "5",
            "expire_date": "07-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633790298,
        "check": {
            "check1": "1: BS59816 - 2021-10-09 16:40",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 400",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Sat,  9 Oct 2021 14:32:58 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "Murermester Kristian Winther Aps",
            "cvr": "41706597",
            "bill_to_address": "Svalevej 37",
            "bill_to_postal_code": "8382",
            "bill_to_city": "Hinnerup",
            "requisition_no": "Kristian Winther",
            "bill_to_country": "DK",
            "quantity": "10",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633782778,
        "check": {
            "check1": "1: BS59815 - 2021-10-09 14:33",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Sat,  9 Oct 2021 12:43:39 +0200 (CEST)",
        "input": {
            "shop_id": "575",
            "companyname": "Toemrermester Christian Stougaard ApS",
            "cvr": "33950640",
            "bill_to_address": "Hedemarksvej 44",
            "bill_to_postal_code": "8300",
            "bill_to_city": "Odder",
            "requisition_no": "Christian Stougaard",
            "bill_to_country": "DK",
            "quantity": "9",
            "expire_date": "14-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633776219,
        "check": {
            "check1": "1: BS59814 - 2021-10-09 12:44",
            "check2": "",
            "check3": "",
            "shopname": "Designjulegaven",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_email: not a valid e-mail",
            "field": "contact_email",
            "type": "invalidemail",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Sat,  9 Oct 2021 12:09:07 +0200 (CEST)",
        "input": {
            "shop_id": "1981",
            "companyname": "berga blommor ab",
            "cvr": "556640-0205",
            "phone": "070-7411269",
            "contact_name": "Anna Fleck",
            "contact_phone": "070-7411269",
            "contact_email": "info@berga blommor.se",
            "bill_to_address": "Stor\u00e4ngsv\u00e4gen 7a",
            "bill_to_postal_code": "18431",
            "bill_to_city": "\u00c5kersberga",
            "requisition_no": "Anna",
            "bill_to_country": "SE",
            "quantity": "8",
            "expire_date": "31-12-2021",
            "is_email": "0",
            "use_shipping_address": "1",
            "ship_to_company": "Berga blommor",
            "ship_to_address": "Stor\u00e4ngsv\u00e4gen 7a",
            "ship_to_address_2": "Stor\u00e4ngsv\u00e4gen 7a",
            "ship_to_postal_code": "18431",
            "ship_to_city": "\u00c5kersberga",
            "ship_to_country": "SE"
        },
        "Timestamp": 1633774147,
        "check": {
            "check1": "1: BS59813 - 2021-10-09 12:09",
            "check2": "",
            "check3": "",
            "shopname": "24Julklappar800 (Sverige)",
            "lang": 5
        }
    },
    {
        "output": {
            "status": "30",
            "error": "Could not find valid shop_id"
        },
        "Date": " Sat,  9 Oct 2021 12:05:47 +0200 (CEST)",
        "input": {
            "companyname": "Kloakmesteren i Odder ApS",
            "cvr": "26113067",
            "phone": "21426022",
            "contact_name": "Morten Bonde",
            "contact_phone": "21426022",
            "contact_email": "bonde@kloakmesterenodder.dk",
            "bill_to_address": "\u00d8stergade, 21",
            "bill_to_postal_code": "8300",
            "bill_to_city": "Odder",
            "requisition_no": "Morten Bonde",
            "bill_to_country": "DK",
            "quantity": "19",
            "expire_date": "14-11-2021",
            "is_email": "1",
            "use_shipping_address": "1",
            "ship_to_company": "Kloakmesteren i Odder ApS",
            "ship_to_address": "Lyngvejen 1, Hal 7",
            "ship_to_address_2": "Lyngvejen 1, Hal 7",
            "ship_to_postal_code": "8300",
            "ship_to_city": "Odder",
            "ship_to_country": "DK",
            "shop_id": ""
        },
        "Timestamp": 1633773947,
        "check": {
            "check1": "1: BS59812 - 2021-10-09 12:06",
            "check2": "",
            "check3": "",
            "shopname": "Ukendt",
            "lang": ""
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Sat,  9 Oct 2021 07:02:16 +0200 (CEST)",
        "input": {
            "shop_id": "52",
            "companyname": "Hornsh\u00f8jgaard",
            "cvr": "10548942",
            "phone": "28966959",
            "contact_phone": "28966959",
            "contact_email": "Birgitte_0302@msn.com",
            "bill_to_address": "Hornsmarken 27",
            "bill_to_postal_code": "9500",
            "bill_to_city": "Hobro",
            "requisition_no": "Birgitte",
            "bill_to_country": "DK",
            "quantity": "20",
            "expire_date": "31-10-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK"
        },
        "Timestamp": 1633755736,
        "check": {
            "check1": "1: BS59811 - 2021-10-09 07:03",
            "check2": "",
            "check3": "",
            "shopname": "Julegavekortet DK",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Fri,  8 Oct 2021 20:10:41 +0200 (CEST)",
        "input": {
            "shop_id": "53",
            "companyname": "Vivild Laegehus",
            "cvr": "26396395",
            "bill_to_address": "Langgade 63 b",
            "bill_to_postal_code": "8961",
            "bill_to_city": "Alling\u00e5bro",
            "requisition_no": "Mette Kj\u00e6r",
            "bill_to_country": "DK",
            "quantity": "12",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633716641,
        "check": {
            "check1": "1: BS59807 - 2021-10-08 20:12",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekortet DK 800",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Fri,  8 Oct 2021 15:34:29 +0200 (CEST)",
        "input": {
            "shop_id": "575",
            "companyname": "All Kool",
            "cvr": "30604210",
            "bill_to_address": "Knudsminde 4 B",
            "bill_to_postal_code": "8300",
            "bill_to_city": "Odder",
            "requisition_no": "AK",
            "bill_to_country": "DK",
            "quantity": "8",
            "expire_date": "31-10-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633700069,
        "check": {
            "check1": "1: BS59792 - 2021-10-08 15:35",
            "check2": "",
            "check3": "",
            "shopname": "Designjulegaven",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Fri,  8 Oct 2021 15:25:55 +0200 (CEST)",
        "input": {
            "shop_id": "52",
            "companyname": "Speciallaege Marianne Sonderg\u00e5rd-Petersen",
            "cvr": "31233437",
            "bill_to_address": "fakkegravvej 34",
            "bill_to_postal_code": "7140",
            "bill_to_city": "Stouby",
            "bill_to_country": "DK",
            "quantity": "10",
            "expire_date": "14-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633699555,
        "check": {
            "check1": "1: BS59791 - 2021-10-08 15:30",
            "check2": "",
            "check3": "",
            "shopname": "Julegavekortet DK",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Fri,  8 Oct 2021 14:55:45 +0200 (CEST)",
        "input": {
            "shop_id": "575",
            "companyname": "Advokaterne Sankt Knuds Torv ",
            "cvr": "32 28 30 12",
            "bill_to_address": "Ryesgade 31, 1",
            "bill_to_postal_code": "8000",
            "bill_to_city": "Aarhus C",
            "requisition_no": "Anni N\u00f8rgaard Clausen",
            "bill_to_country": "DK",
            "quantity": "75",
            "expire_date": "14-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633697745,
        "check": {
            "check1": "",
            "check2": "1: BS60103 - 2021-10-12 11:36",
            "check3": "",
            "shopname": "Designjulegaven",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Fri,  8 Oct 2021 14:09:51 +0200 (CEST)",
        "input": {
            "shop_id": "54",
            "companyname": "Lyngbjerggaardskolen",
            "cvr": "1593 9648 ",
            "bill_to_address": "Ridemandsm\u00f8llevej 52, Godth\u00e5b",
            "bill_to_postal_code": "9230",
            "bill_to_city": "Svenstrup J",
            "requisition_no": "Erik Steffensen",
            "bill_to_country": "DK",
            "quantity": "25",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633694991,
        "check": {
            "check1": "1: BS59774 - 2021-10-08 14:10",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 400",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Fri,  8 Oct 2021 13:31:54 +0200 (CEST)",
        "input": {
            "shop_id": "2558",
            "companyname": "PINGSTLILJANS F\u00d6RSKOLA",
            "cvr": "873202-4602",
            "bill_to_address": "Geijersgatan 24b",
            "bill_to_postal_code": "66730",
            "bill_to_city": "FORSHAGA",
            "bill_to_country": "SE",
            "quantity": "11",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "SE",
            "contact_email": ""
        },
        "Timestamp": 1633692714,
        "check": {
            "check1": "1: BS59759 - 2021-10-08 13:33",
            "check2": "",
            "check3": "1: BS59760 - 2021-10-08 13:34",
            "shopname": "24 julklappar 1200 2021",
            "lang": 5
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Fri,  8 Oct 2021 13:18:32 +0200 (CEST)",
        "input": {
            "shop_id": "58",
            "companyname": "AUR Energi AS",
            "cvr": "922294224",
            "bill_to_address": "Aurveien 32",
            "bill_to_postal_code": "1930",
            "bill_to_city": "Aurskog",
            "requisition_no": "Christian S\u00e6ther",
            "bill_to_country": "NO",
            "bill_to_email": "aurskog@st.yx.no",
            "quantity": "9",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "NO",
            "contact_email": ""
        },
        "Timestamp": 1633691912,
        "check": {
            "check1": "1: BS59755 - 2021-10-08 13:19",
            "check2": "",
            "check3": "",
            "shopname": "Julegavekortet NO 600",
            "lang": 4
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Fri,  8 Oct 2021 13:12:18 +0200 (CEST)",
        "input": {
            "shop_id": "2548",
            "companyname": "Dansk Affaldsforening",
            "cvr": "13261679",
            "phone": "72312071",
            "contact_phone": "72312071",
            "contact_email": "ha@danskaffaldsforening.dk",
            "bill_to_address": "Vester Farimagsgade 1, 5.",
            "bill_to_postal_code": "1606",
            "bill_to_city": "K\u00f8benhavn V",
            "requisition_no": "Henriette Andersen",
            "bill_to_country": "DK",
            "quantity": "11",
            "expire_date": "31-12-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK"
        },
        "Timestamp": 1633691538,
        "check": {
            "check1": "1: BS59751 - 2021-10-08 13:12",
            "check2": "",
            "check3": "",
            "shopname": "Det gr\u00f8nne gavekort",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Fri,  8 Oct 2021 13:03:40 +0200 (CEST)",
        "input": {
            "shop_id": "272",
            "companyname": "Dolphitech AS",
            "cvr": "895052872",
            "bill_to_address": "Studievegen 16",
            "bill_to_postal_code": "2815",
            "bill_to_city": "Gj\u00f8vik",
            "requisition_no": "Christmas2021",
            "bill_to_country": "NO",
            "bill_to_email": "invoice@dolphitech.com",
            "quantity": "36",
            "expire_date": "21-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "NO",
            "contact_email": ""
        },
        "Timestamp": 1633691020,
        "check": {
            "check1": "",
            "check2": "",
            "check3": "",
            "shopname": "Julegavekortet NO 300",
            "lang": 4
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Fri,  8 Oct 2021 12:52:01 +0200 (CEST)",
        "input": {
            "shop_id": "52",
            "companyname": "Novadan",
            "cvr": "63129216",
            "bill_to_address": "Platinvej 21",
            "bill_to_postal_code": "6000",
            "bill_to_city": "Kolding",
            "requisition_no": "Personaleforening",
            "bill_to_country": "DK",
            "quantity": "70",
            "expire_date": "14-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633690321,
        "check": {
            "check1": "1: BS59745 - 2021-10-08 12:52",
            "check2": "",
            "check3": "",
            "shopname": "Julegavekortet DK",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Fri,  8 Oct 2021 12:50:44 +0200 (CEST)",
        "input": {
            "shop_id": "2549",
            "companyname": "Bj\u00f8rg Thorhallsdottir AS",
            "cvr": " 994 073 338",
            "bill_to_address": "Sandviksveien 130",
            "bill_to_postal_code": "1365",
            "bill_to_city": "Blommenholm",
            "bill_to_country": "NO",
            "bill_to_email": "hanne@bjoerg.no",
            "quantity": "7",
            "expire_date": "21-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "NO",
            "giftwrap": "1",
            "contact_email": ""
        },
        "Timestamp": 1633690244,
        "check": {
            "check1": "",
            "check2": "1: BS60719 - 2021-10-15 09:55",
            "check3": "",
            "shopname": "BRA Gavekortet (NO)",
            "lang": 4
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Fri,  8 Oct 2021 12:20:17 +0200 (CEST)",
        "input": {
            "shop_id": "575",
            "companyname": "Oris Tandlaegerne i Lyngby",
            "cvr": "39774747",
            "ean": "5790002501475",
            "bill_to_address": "Jernbanepladsen 10, 2.",
            "bill_to_postal_code": "2800",
            "bill_to_city": "Kongens Lyngby",
            "requisition_no": "Cristiine Enevolls",
            "bill_to_country": "DK",
            "quantity": "14",
            "expire_date": "14-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633688417,
        "check": {
            "check1": "1: BS59737 - 2021-10-08 12:21",
            "check2": "",
            "check3": "",
            "shopname": "Designjulegaven",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Fri,  8 Oct 2021 12:14:36 +0200 (CEST)",
        "input": {
            "shop_id": "290",
            "companyname": "Aktieselskabet Gronhoj",
            "cvr": "DK52471214",
            "bill_to_address": "Skorping Center 2",
            "bill_to_postal_code": "9520",
            "bill_to_city": "Skorping",
            "requisition_no": "vivi gronhoj",
            "bill_to_country": "DK",
            "quantity": "17",
            "expire_date": "07-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633688076,
        "check": {
            "check1": "",
            "check2": "",
            "check3": "",
            "shopname": "Dr\u00f8mmegavekortet 200",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Fri,  8 Oct 2021 12:10:23 +0200 (CEST)",
        "input": {
            "shop_id": "53",
            "companyname": "SG Nordic ApS",
            "cvr": "32934455",
            "phone": "35102022",
            "contact_phone": "35102022",
            "contact_email": "info@sgnordic.com",
            "bill_to_address": "Vesterbrogade 149, Bygn. 9. 1. sal",
            "bill_to_postal_code": "1620",
            "bill_to_city": "K\u00f8benhavn V",
            "requisition_no": "S\u00f8ren Wagner",
            "bill_to_country": "DK",
            "quantity": "5",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "1",
            "ship_to_company": "SG Nordic ApS",
            "ship_to_address": "Vesterbrogade 149, Bygn. 9. 1. sal",
            "ship_to_address_2": "Vesterbrogade 149, Bygn. 9. 1. sal",
            "ship_to_postal_code": "1620",
            "ship_to_city": "K\u00f8benhavn V",
            "ship_to_country": "DK"
        },
        "Timestamp": 1633687823,
        "check": {
            "check1": "1: BS59732 - 2021-10-08 12:10",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekortet DK 800",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Fri,  8 Oct 2021 12:10:17 +0200 (CEST)",
        "input": {
            "shop_id": "290",
            "companyname": "Aktieselskabet Gr\u00f8nh\u00f8j",
            "cvr": "52471214",
            "bill_to_address": "Skorping Center 2",
            "bill_to_postal_code": "9520",
            "bill_to_city": "Skorping",
            "bill_to_country": "DK",
            "quantity": "17",
            "expire_date": "07-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633687817,
        "check": {
            "check1": "1: BS59736 - 2021-10-08 12:15",
            "check2": "",
            "check3": "",
            "shopname": "Dr\u00f8mmegavekortet 200",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Fri,  8 Oct 2021 12:10:09 +0200 (CEST)",
        "input": {
            "shop_id": "53",
            "companyname": "SG Nordic ApS",
            "cvr": "32934455",
            "phone": "35102022",
            "contact_phone": "35102022",
            "contact_email": "info@sgnordic.com",
            "bill_to_address": "Vesterbrogade 149, Bygn. 9. 1. sal",
            "bill_to_postal_code": "1620",
            "bill_to_city": "K\u00f8benhavn V",
            "bill_to_country": "DK",
            "quantity": "5",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "1",
            "ship_to_company": "SG Nordic ApS",
            "ship_to_address": "Vesterbrogade 149, Bygn. 9. 1. sal",
            "ship_to_address_2": "Vesterbrogade 149, Bygn. 9. 1. sal",
            "ship_to_postal_code": "1620",
            "ship_to_city": "K\u00f8benhavn V",
            "ship_to_country": "DK"
        },
        "Timestamp": 1633687809,
        "check": {
            "check1": "1: BS59732 - 2021-10-08 12:10",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekortet DK 800",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Fri,  8 Oct 2021 12:06:59 +0200 (CEST)",
        "input": {
            "shop_id": "575",
            "companyname": "LoneP",
            "cvr": "37104655",
            "bill_to_address": "Storegade 8",
            "bill_to_postal_code": "6100",
            "bill_to_city": "Haderslev ",
            "requisition_no": "Camilla ",
            "bill_to_country": "DK",
            "quantity": "11",
            "expire_date": "14-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633687619,
        "check": {
            "check1": "1: BS59730 - 2021-10-08 12:08",
            "check2": "",
            "check3": "",
            "shopname": "Designjulegaven",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Fri,  8 Oct 2021 11:57:21 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "Kvist og Kristensen A\/S",
            "cvr": "10000690",
            "phone": "51790979",
            "contact_phone": "51790979",
            "contact_email": "jbj@kvistogkristensen.dk",
            "bill_to_address": "Restrup Skovvej 36",
            "bill_to_postal_code": "9240",
            "bill_to_city": "Nibe",
            "requisition_no": "Jimmi Jacobsen",
            "bill_to_country": "DK",
            "quantity": "25",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "1",
            "ship_to_company": "Kvist og Kristensen A\/S",
            "ship_to_address": "Restrup Skovvej 36",
            "ship_to_address_2": "Restrup Skovvej 36",
            "ship_to_postal_code": "9240",
            "ship_to_city": "Nibe",
            "ship_to_country": "DK"
        },
        "Timestamp": 1633687041,
        "check": {
            "check1": "",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Fri,  8 Oct 2021 11:46:05 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "Krusborg byg ApS",
            "cvr": "30495799",
            "bill_to_address": "Refshaven 97",
            "bill_to_postal_code": "7321",
            "bill_to_city": "Gadbjerg",
            "requisition_no": "Trille Nielsen",
            "bill_to_country": "DK",
            "quantity": "18",
            "expire_date": "21-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633686365,
        "check": {
            "check1": "1: BS59724 - 2021-10-08 11:46",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Fri,  8 Oct 2021 11:37:58 +0200 (CEST)",
        "input": {
            "shop_id": "1832",
            "companyname": "Esl\u00f6vs Rygg och Idrottscentrum AB",
            "cvr": "556826-9236",
            "phone": "0730353868",
            "contact_phone": "0730353868",
            "contact_email": "hanna@ryggidrottscentrum.se",
            "bill_to_address": "Bj\u00f6rnstorp 712",
            "bill_to_postal_code": "24798",
            "bill_to_city": "Genarp",
            "requisition_no": "Hanna Andersson",
            "bill_to_country": "SE",
            "quantity": "12",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "SE"
        },
        "Timestamp": 1633685878,
        "check": {
            "check1": "1: BS59720 - 2021-10-08 11:39",
            "check2": "",
            "check3": "",
            "shopname": "24Julklappar400 (Sverige)",
            "lang": 5
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Fri,  8 Oct 2021 11:16:41 +0200 (CEST)",
        "input": {
            "shop_id": "1981",
            "companyname": "Capernum Sverige AB",
            "cvr": "556938-2392",
            "phone": "010-3305004",
            "contact_phone": "010-3305004",
            "contact_email": "kristel.kiraly@capernum.se",
            "bill_to_address": "Lilla Garnisonsgatan 31",
            "bill_to_postal_code": "254 67",
            "bill_to_city": "Helsingborg",
            "requisition_no": "Kristel",
            "bill_to_country": "SE",
            "quantity": "5",
            "expire_date": "31-12-2022",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "SE"
        },
        "Timestamp": 1633684601,
        "check": {
            "check1": "1: BS59707 - 2021-10-08 11:17",
            "check2": "",
            "check3": "1: BS59705 - 2021-10-08 11:13",
            "shopname": "24Julklappar800 (Sverige)",
            "lang": 5
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field companyname: value is required",
            "field": "companyname",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Fri,  8 Oct 2021 11:03:59 +0200 (CEST)",
        "input": {
            "shop_id": "1832",
            "bill_to_country": "SE",
            "quantity": "51",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "SE",
            "cvr": "",
            "contact_email": ""
        },
        "Timestamp": 1633683839,
        "check": {
            "check1": "*",
            "check2": "*",
            "check3": "*",
            "shopname": "24Julklappar400 (Sverige)",
            "lang": 5
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Fri,  8 Oct 2021 10:49:59 +0200 (CEST)",
        "input": {
            "shop_id": "310",
            "companyname": "Boerneinstitutionen Birkelunden",
            "cvr": "29188205",
            "ean": "5798007390242",
            "bill_to_address": "Baldersvej 18",
            "bill_to_postal_code": "4200",
            "bill_to_city": "Slagelse",
            "requisition_no": "Charlotte Buur",
            "bill_to_country": "DK",
            "quantity": "16",
            "expire_date": "21-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633682999,
        "check": {
            "check1": "",
            "check2": "",
            "check3": "",
            "shopname": "Dr\u00f8mmegavekortet 300",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "30",
            "error": "Could not find valid shop_id"
        },
        "Date": " Fri,  8 Oct 2021 10:49:00 +0200 (CEST)",
        "input": {
            "companyname": "Equus Cura ApS",
            "cvr": "39534696",
            "bill_to_address": "Borres\u00f8vej 3, 3MF",
            "bill_to_postal_code": "824+",
            "bill_to_city": "Risskov",
            "requisition_no": "Louise ",
            "bill_to_country": "DK",
            "quantity": "5",
            "expire_date": "31-10-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "shop_id": "",
            "contact_email": ""
        },
        "Timestamp": 1633682940,
        "check": {
            "check1": "",
            "check2": "",
            "check3": "",
            "shopname": "Ukendt",
            "lang": ""
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Fri,  8 Oct 2021 10:44:56 +0200 (CEST)",
        "input": {
            "shop_id": "1832",
            "companyname": "Min Hemservice Stockholm AB",
            "cvr": "556808-8115",
            "phone": "0736760773",
            "contact_phone": "0736760773",
            "contact_email": "iwona.romatowska@gmail.com",
            "bill_to_address": "Run\u00f6backen 5",
            "bill_to_postal_code": "18441",
            "bill_to_city": "\u00c5kersberga",
            "requisition_no": "Iwona Romatowska",
            "bill_to_country": "SE",
            "quantity": "13",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "SE"
        },
        "Timestamp": 1633682696,
        "check": {
            "check1": "1: BS59697 - 2021-10-08 10:46",
            "check2": "",
            "check3": "",
            "shopname": "24Julklappar400 (Sverige)",
            "lang": 5
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Fri,  8 Oct 2021 10:34:47 +0200 (CEST)",
        "input": {
            "shop_id": "52",
            "companyname": "Murermester Hans Pedersen",
            "cvr": "19186105",
            "bill_to_address": "Snaven, 11",
            "bill_to_postal_code": "5631",
            "bill_to_city": "Ebberup",
            "requisition_no": "Grethe",
            "bill_to_country": "DK",
            "quantity": "10",
            "expire_date": "14-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633682087,
        "check": {
            "check1": "1: BS59690 - 2021-10-08 10:35",
            "check2": "",
            "check3": "",
            "shopname": "Julegavekortet DK",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Fri,  8 Oct 2021 10:19:12 +0200 (CEST)",
        "input": {
            "shop_id": "52",
            "companyname": "Dafolo AS",
            "cvr": "61966617",
            "bill_to_address": "Suderbovej 24",
            "bill_to_postal_code": "9900",
            "bill_to_city": "Frederikshavn",
            "requisition_no": "Hanne V Jacobsen",
            "bill_to_country": "DK",
            "quantity": "85",
            "expire_date": "31-10-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633681152,
        "check": {
            "check1": "1: BS59686 - 2021-10-08 10:20",
            "check2": "",
            "check3": "",
            "shopname": "Julegavekortet DK",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Fri,  8 Oct 2021 10:11:09 +0200 (CEST)",
        "input": {
            "shop_id": "2548",
            "companyname": "Givesco Bakery",
            "cvr": "28668031",
            "phone": "40910467",
            "contact_phone": "40910467",
            "contact_email": "bmj@givesco.dk",
            "bill_to_address": "Lysholt Alle 3",
            "bill_to_postal_code": "7100",
            "bill_to_city": "Vejle",
            "requisition_no": "Betina Jensen",
            "bill_to_country": "DK",
            "quantity": "11",
            "expire_date": "14-11-2021",
            "is_email": "1",
            "use_shipping_address": "1",
            "ship_to_company": "Givesco Bakery A\/S",
            "ship_to_address": "Lysholt Alle 3",
            "ship_to_address_2": "Lysholt Alle 3",
            "ship_to_postal_code": "7100",
            "ship_to_city": "Vejle",
            "ship_to_country": "DK",
            "giftwrap": "1"
        },
        "Timestamp": 1633680669,
        "check": {
            "check1": "1: BS59682 - 2021-10-08 10:11",
            "check2": "",
            "check3": "",
            "shopname": "Det gr\u00f8nne gavekort",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Fri,  8 Oct 2021 09:50:34 +0200 (CEST)",
        "input": {
            "shop_id": "2395",
            "companyname": "2S Consult ApS",
            "cvr": "27058973",
            "bill_to_address": "S\u00f8nderh\u00f8j 3",
            "bill_to_postal_code": "8260",
            "bill_to_city": "Viby J",
            "requisition_no": "Merete Sichlau",
            "bill_to_country": "DK",
            "quantity": "2",
            "expire_date": "07-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633679434,
        "check": {
            "check1": "",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekortet DK 960",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Fri,  8 Oct 2021 09:13:06 +0200 (CEST)",
        "input": {
            "shop_id": "290",
            "companyname": "haustruphave b\u00f8rnehus ",
            "cvr": "35209115",
            "ean": "5798006608263",
            "bill_to_address": "Berav\u00e6nget 22",
            "bill_to_postal_code": "5210",
            "bill_to_city": "Odense NV",
            "bill_to_country": "DK",
            "quantity": "13",
            "expire_date": "31-12-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633677186,
        "check": {
            "check1": "",
            "check2": "1: BS59668 - 2021-10-08 09:18",
            "check3": "9: BS56736 - 2021-09-09 14:29",
            "shopname": "Dr\u00f8mmegavekortet 200",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Fri,  8 Oct 2021 09:07:22 +0200 (CEST)",
        "input": {
            "shop_id": "2558",
            "companyname": "Bengt & Mats Byggmontage AB",
            "cvr": "556364-9051",
            "bill_to_address": "Pikullagatan 12",
            "bill_to_postal_code": "70227",
            "bill_to_city": "\u00d6rebro",
            "requisition_no": "Bengt Johansson",
            "bill_to_country": "SE",
            "quantity": "20",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "SE",
            "giftwrap": "1",
            "contact_email": ""
        },
        "Timestamp": 1633676842,
        "check": {
            "check1": "1: BS59664 - 2021-10-08 09:08",
            "check2": "",
            "check3": "",
            "shopname": "24 julklappar 1200 2021",
            "lang": 5
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Fri,  8 Oct 2021 08:50:32 +0200 (CEST)",
        "input": {
            "shop_id": "52",
            "companyname": "Orum Fysioterapi",
            "cvr": "42491403",
            "bill_to_address": "\u00d8stergade 32",
            "bill_to_postal_code": "8830",
            "bill_to_city": "Tjele",
            "requisition_no": "Rene",
            "bill_to_country": "DK",
            "quantity": "5",
            "expire_date": "14-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633675832,
        "check": {
            "check1": "1: BS59662 - 2021-10-08 08:51",
            "check2": "",
            "check3": "",
            "shopname": "Julegavekortet DK",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Fri,  8 Oct 2021 08:48:12 +0200 (CEST)",
        "input": {
            "shop_id": "1832",
            "companyname": "CaSa Economia AB",
            "cvr": "556967-7742",
            "bill_to_address": "Signalistgatan 6",
            "bill_to_postal_code": "72131",
            "bill_to_city": "V\u00e4ster\u00e5s",
            "requisition_no": "Sandra Holmer",
            "bill_to_country": "SE",
            "quantity": "8",
            "expire_date": "31-12-2022",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "SE",
            "contact_email": ""
        },
        "Timestamp": 1633675692,
        "check": {
            "check1": "1: BS59661 - 2021-10-08 08:49",
            "check2": "",
            "check3": "",
            "shopname": "24Julklappar400 (Sverige)",
            "lang": 5
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Fri,  8 Oct 2021 08:08:45 +0200 (CEST)",
        "input": {
            "shop_id": "2395",
            "companyname": "Nettraelasten",
            "cvr": "37834025",
            "bill_to_address": "\u00d8stervang 99",
            "bill_to_postal_code": "7441",
            "bill_to_city": "Bording",
            "requisition_no": "Brian Ellegaard",
            "bill_to_country": "DK",
            "quantity": "11",
            "expire_date": "31-12-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633673325,
        "check": {
            "check1": "1: BS59656 - 2021-10-08 08:10",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekortet DK 960",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Fri,  8 Oct 2021 07:59:12 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "Henrik Hansen Automobiler A\/S",
            "cvr": "21239240",
            "bill_to_address": "Transportbuen 2",
            "bill_to_postal_code": "4700",
            "bill_to_city": "N\u00e6stved",
            "requisition_no": "Frederik Hansen",
            "bill_to_country": "DK",
            "quantity": "60",
            "expire_date": "31-12-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633672752,
        "check": {
            "check1": "1: BS59654 - 2021-10-08 07:59",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Fri,  8 Oct 2021 07:44:16 +0200 (CEST)",
        "input": {
            "shop_id": "2395",
            "companyname": "Spillestedet Paletten",
            "cvr": "16959588",
            "bill_to_address": "Tingvej 20",
            "bill_to_postal_code": "8800",
            "bill_to_city": "Viborg",
            "requisition_no": "Flemming H. Christensen",
            "bill_to_country": "DK",
            "quantity": "5",
            "expire_date": "01-04-2022",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633671856,
        "check": {
            "check1": "1: BS59652 - 2021-10-08 07:44",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekortet DK 960",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu,  7 Oct 2021 20:16:17 +0200 (CEST)",
        "input": {
            "shop_id": "52",
            "companyname": "L\u00e6gerne Mollerup, Haslund og Falgren",
            "cvr": "16854832",
            "phone": "26185126",
            "contact_phone": "26185126",
            "contact_email": "vmollerup@webspeed.dk",
            "bill_to_address": "Claessensvej 1",
            "bill_to_postal_code": "3000",
            "bill_to_city": "Helsing\u00f8r",
            "requisition_no": "Vibeke Mollerup",
            "bill_to_country": "DK",
            "quantity": "6",
            "expire_date": "14-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK"
        },
        "Timestamp": 1633630577,
        "check": {
            "check1": "1: BS59650 - 2021-10-07 20:18",
            "check2": "",
            "check3": "",
            "shopname": "Julegavekortet DK",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu,  7 Oct 2021 18:16:05 +0200 (CEST)",
        "input": {
            "shop_id": "575",
            "companyname": "Epsilon Vision",
            "cvr": "40867503",
            "bill_to_address": "\u00d8stre Parkvej 82",
            "bill_to_postal_code": "4100",
            "bill_to_city": "Ringsted",
            "requisition_no": "Robert Vingaa",
            "bill_to_country": "DK",
            "quantity": "9",
            "expire_date": "14-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633623365,
        "check": {
            "check1": "1: BS59649 - 2021-10-07 18:19",
            "check2": "",
            "check3": "1: BS59651 - 2021-10-07 22:30",
            "shopname": "Designjulegaven",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "30",
            "error": "Could not find valid shop_id"
        },
        "Date": " Thu,  7 Oct 2021 17:59:16 +0200 (CEST)",
        "input": {
            "bill_to_address": "\u00d8stre Parkvej 82",
            "bill_to_postal_code": "4100",
            "bill_to_city": "Ringsted",
            "requisition_no": "Robert Vingaa",
            "bill_to_country": "DK",
            "quantity": "9",
            "expire_date": "14-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "shop_id": "",
            "cvr": "",
            "contact_email": ""
        },
        "Timestamp": 1633622356,
        "check": {
            "check1": "*",
            "check2": "*",
            "check3": "*",
            "shopname": "Ukendt",
            "lang": ""
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu,  7 Oct 2021 17:31:23 +0200 (CEST)",
        "input": {
            "shop_id": "1981",
            "companyname": "Elfirman Sydost AB",
            "cvr": "559064-9181",
            "bill_to_address": "Silverv\u00e4gen 5",
            "bill_to_postal_code": "37150",
            "bill_to_city": "Karlskrona",
            "requisition_no": "Niclas Pettersson",
            "bill_to_country": "SE",
            "quantity": "5",
            "expire_date": "31-12-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "SE",
            "contact_email": ""
        },
        "Timestamp": 1633620683,
        "check": {
            "check1": "1: BS59648 - 2021-10-07 17:32",
            "check2": "",
            "check3": "",
            "shopname": "24Julklappar800 (Sverige)",
            "lang": 5
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu,  7 Oct 2021 16:21:27 +0200 (CEST)",
        "input": {
            "shop_id": "52",
            "companyname": "L\u00e6gerne M\u00f8llegade ",
            "cvr": "16546984",
            "phone": "86806869",
            "contact_phone": "86806869",
            "contact_email": "dolmer@dadlnet.dk",
            "bill_to_address": "M\u00f8llegade 23B",
            "bill_to_postal_code": "8600",
            "bill_to_city": "Silkeborg",
            "bill_to_country": "DK",
            "quantity": "10",
            "expire_date": "14-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_address": "M\u00f8llegade 23B",
            "ship_to_address_2": "M\u00f8llegade 23B",
            "ship_to_postal_code": "8600",
            "ship_to_city": "Silkeborg",
            "ship_to_country": "DK"
        },
        "Timestamp": 1633616487,
        "check": {
            "check1": "1: BS59645 - 2021-10-07 16:22",
            "check2": "",
            "check3": "",
            "shopname": "Julegavekortet DK",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field companyname: value is required",
            "field": "companyname",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu,  7 Oct 2021 16:11:49 +0200 (CEST)",
        "input": {
            "shop_id": "2395",
            "bill_to_country": "DK",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "cvr": "",
            "contact_email": ""
        },
        "Timestamp": 1633615909,
        "check": {
            "check1": "*",
            "check2": "*",
            "check3": "*",
            "shopname": "Guldgavekortet DK 960",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu,  7 Oct 2021 15:51:30 +0200 (CEST)",
        "input": {
            "shop_id": "52",
            "companyname": "SJ Bornholm ApS",
            "cvr": "32647561",
            "contact_email": "uln@sjbornholm.dk",
            "bill_to_address": "Brovangen 18",
            "bill_to_postal_code": "3720 ",
            "bill_to_city": "Aakirkeby",
            "requisition_no": "Ulrika Nielsen",
            "bill_to_country": "DK",
            "quantity": "17",
            "expire_date": "14-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK"
        },
        "Timestamp": 1633614690,
        "check": {
            "check1": "1: BS59638 - 2021-10-07 15:52",
            "check2": "",
            "check3": "",
            "shopname": "Julegavekortet DK",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu,  7 Oct 2021 14:54:10 +0200 (CEST)",
        "input": {
            "shop_id": "2395",
            "companyname": "City Skilte ApS",
            "cvr": "30571177",
            "bill_to_address": "Marsalle 7",
            "bill_to_postal_code": "8700",
            "bill_to_city": "Horsens",
            "requisition_no": "Helle M\u00f8lgaard",
            "bill_to_country": "DK",
            "quantity": "6",
            "expire_date": "21-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633611250,
        "check": {
            "check1": "1: BS59625 - 2021-10-07 14:54",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekortet DK 960",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu,  7 Oct 2021 14:34:52 +0200 (CEST)",
        "input": {
            "shop_id": "2558",
            "companyname": "Annebergs Elektriska AB",
            "cvr": "556155-8890",
            "phone": "0706-639335",
            "contact_phone": "0706-639335",
            "contact_email": "info@annebergsel.se",
            "bill_to_address": "Slottsv\u00e4gen 2",
            "bill_to_postal_code": "523 74",
            "bill_to_city": "H\u00f6kerum",
            "requisition_no": "Jene Gustafsson",
            "bill_to_country": "SE",
            "quantity": "8",
            "expire_date": "31-12-2022",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "SE"
        },
        "Timestamp": 1633610092,
        "check": {
            "check1": "1: BS59619 - 2021-10-07 14:35",
            "check2": "",
            "check3": "",
            "shopname": "24 julklappar 1200 2021",
            "lang": 5
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu,  7 Oct 2021 14:25:35 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "Qbit ApS",
            "cvr": "36411155",
            "bill_to_address": "Lysk\u00e6r 8A, st.tv.",
            "bill_to_postal_code": "2730",
            "bill_to_city": "Herlev",
            "requisition_no": "Gaver",
            "bill_to_country": "DK",
            "quantity": "30",
            "expire_date": "07-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633609535,
        "check": {
            "check1": "1: BS59617 - 2021-10-07 14:26",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu,  7 Oct 2021 14:09:29 +0200 (CEST)",
        "input": {
            "shop_id": "2395",
            "companyname": "Tandl\u00e6gerne i Havnegade",
            "cvr": "29946973",
            "contact_email": "info@havnegadetand.dk",
            "bill_to_address": "Havnegade 81",
            "bill_to_postal_code": "4900",
            "bill_to_city": "Nakskov",
            "requisition_no": "Tina Klausen",
            "bill_to_country": "DK",
            "quantity": "9",
            "expire_date": "07-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK"
        },
        "Timestamp": 1633608569,
        "check": {
            "check1": "1: BS59607 - 2021-10-07 14:10",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekortet DK 960",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu,  7 Oct 2021 14:04:52 +0200 (CEST)",
        "input": {
            "shop_id": "2395",
            "companyname": "Molecule Consultancy Co Rebel Work Space",
            "cvr": "28515766",
            "bill_to_address": "Dampf\u00e6rgevej 27-29",
            "bill_to_postal_code": "2100",
            "bill_to_city": "K\u00f8benhavn \u00d8",
            "requisition_no": "Nanna Rodian Christensen",
            "bill_to_country": "DK",
            "quantity": "29",
            "expire_date": "21-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633608292,
        "check": {
            "check1": "1: BS59602 - 2021-10-07 14:06",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekortet DK 960",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu,  7 Oct 2021 13:55:24 +0200 (CEST)",
        "input": {
            "shop_id": "2558",
            "companyname": "Lundin Fastighetsbyra AB",
            "cvr": "556334-5502",
            "bill_to_address": "Vasagatan 26",
            "bill_to_postal_code": "411 24",
            "bill_to_city": "G\u00f6teborg",
            "requisition_no": "Robert Lundin",
            "bill_to_country": "SE",
            "quantity": "45",
            "expire_date": "31-12-2022",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "SE",
            "giftwrap": "1",
            "contact_email": ""
        },
        "Timestamp": 1633607724,
        "check": {
            "check1": "1: BS59599 - 2021-10-07 14:01",
            "check2": "",
            "check3": "",
            "shopname": "24 julklappar 1200 2021",
            "lang": 5
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_email: value is required",
            "field": "contact_email",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu,  7 Oct 2021 13:52:20 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "AOF East",
            "cvr": "15072644",
            "phone": "56316706",
            "contact_name": "Anette Amh\u00f8j",
            "contact_phone": "56316706",
            "bill_to_address": "Slagterivej 17",
            "bill_to_postal_code": "4690",
            "bill_to_city": "Haslev",
            "requisition_no": "Anette Amh\u00f8j",
            "bill_to_country": "DK",
            "quantity": "18",
            "expire_date": "07-11-2021",
            "is_email": "0",
            "use_shipping_address": "1",
            "ship_to_company": "AOF East",
            "ship_to_address": "Slagterivej 17",
            "ship_to_address_2": "Slagterivej 17",
            "ship_to_postal_code": "4690",
            "ship_to_city": "Haslev",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633607540,
        "check": {
            "check1": "1: BS59594 - 2021-10-07 13:52",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu,  7 Oct 2021 13:42:48 +0200 (CEST)",
        "input": {
            "shop_id": "53",
            "companyname": "Byens L\u00e6gehus",
            "cvr": "21373133",
            "ean": "5790000140763",
            "bill_to_address": "Store Torv 5, 2. tv.",
            "bill_to_postal_code": "8000",
            "bill_to_city": "Aarhus C",
            "requisition_no": "Mikkel Erichsen",
            "bill_to_country": "DK",
            "quantity": "7",
            "expire_date": "21-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633606968,
        "check": {
            "check1": "",
            "check2": "1: BS59595 - 2021-10-07 13:55",
            "check3": "",
            "shopname": "Guldgavekortet DK 800",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu,  7 Oct 2021 13:25:58 +0200 (CEST)",
        "input": {
            "shop_id": "2395",
            "companyname": "Rfx  Care International ",
            "cvr": "83113219",
            "bill_to_address": "Bakkeg\u00e5rdsvej 408",
            "bill_to_postal_code": "3050",
            "bill_to_city": "Humleb\u00e6k",
            "requisition_no": "Accounting",
            "bill_to_country": "DK",
            "quantity": "12",
            "expire_date": "01-04-2022",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633605958,
        "check": {
            "check1": "1: BS59588 - 2021-10-07 13:28",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekortet DK 960",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu,  7 Oct 2021 13:25:46 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "HTC Biler AS",
            "cvr": "84761214",
            "bill_to_address": "Kirkevej 67",
            "bill_to_postal_code": "8370",
            "bill_to_city": "Hadsten",
            "requisition_no": "Gitte",
            "bill_to_country": "DK",
            "quantity": "9",
            "expire_date": "21-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633605946,
        "check": {
            "check1": "1: BS59587 - 2021-10-07 13:26",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu,  7 Oct 2021 13:25:43 +0200 (CEST)",
        "input": {
            "shop_id": "2395",
            "companyname": "Rfx + Care International ",
            "cvr": "83113219",
            "bill_to_address": "Bakkeg\u00e5rdsvej 408",
            "bill_to_postal_code": "3050",
            "bill_to_city": "Humleb\u00e6k",
            "requisition_no": "Accounting",
            "bill_to_country": "DK",
            "quantity": "12",
            "expire_date": "01-04-2022",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633605943,
        "check": {
            "check1": "1: BS59588 - 2021-10-07 13:28",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekortet DK 960",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu,  7 Oct 2021 13:25:30 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "HTC Biler A\/S",
            "cvr": "84761214",
            "bill_to_address": "Kirkevej 67",
            "bill_to_postal_code": "8370",
            "bill_to_city": "Hadsten",
            "requisition_no": "Gitte",
            "bill_to_country": "DK",
            "quantity": "9",
            "expire_date": "21-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633605930,
        "check": {
            "check1": "1: BS59587 - 2021-10-07 13:26",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu,  7 Oct 2021 13:25:08 +0200 (CEST)",
        "input": {
            "shop_id": "2395",
            "companyname": "Rfx + Care International A\/S",
            "cvr": "83113219",
            "bill_to_address": "Bakkeg\u00e5rdsvej 408",
            "bill_to_postal_code": "3050",
            "bill_to_city": "Humleb\u00e6k",
            "requisition_no": "Accounting",
            "bill_to_country": "DK",
            "quantity": "12",
            "expire_date": "01-04-2022",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633605908,
        "check": {
            "check1": "1: BS59588 - 2021-10-07 13:28",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekortet DK 960",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu,  7 Oct 2021 13:17:32 +0200 (CEST)",
        "input": {
            "shop_id": "1832",
            "companyname": "Delikatessfabriken Norrbotten AB",
            "cvr": "556655-6402",
            "bill_to_address": "Hantverksgatan 2",
            "bill_to_postal_code": "94295",
            "bill_to_city": "Vidsel",
            "requisition_no": "Lena",
            "bill_to_country": "SE",
            "quantity": "11",
            "expire_date": "31-12-2022",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "SE",
            "contact_email": ""
        },
        "Timestamp": 1633605452,
        "check": {
            "check1": "1: BS59582 - 2021-10-07 13:18",
            "check2": "",
            "check3": "",
            "shopname": "24Julklappar400 (Sverige)",
            "lang": 5
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu,  7 Oct 2021 12:37:55 +0200 (CEST)",
        "input": {
            "shop_id": "53",
            "companyname": "Aveo A\/S",
            "cvr": "36944293",
            "contact_email": "kg@aveo.dk",
            "bill_to_address": "J\u00e6gerg\u00e5rdsgade 118",
            "bill_to_postal_code": "8000",
            "bill_to_city": "\u00c5rhus C",
            "requisition_no": "Kathrine Gissel",
            "bill_to_country": "DK",
            "quantity": "5",
            "expire_date": "07-11-2021",
            "is_email": "1",
            "use_shipping_address": "1",
            "ship_to_company": "Aveo A\/S",
            "ship_to_country": "DK"
        },
        "Timestamp": 1633603075,
        "check": {
            "check1": "1: BS59568 - 2021-10-07 12:38",
            "check2": "",
            "check3": "1: BS59561 - 2021-10-07 12:29",
            "shopname": "Guldgavekortet DK 800",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu,  7 Oct 2021 12:36:06 +0200 (CEST)",
        "input": {
            "shop_id": "290",
            "companyname": "rema1000 ",
            "cvr": "37512680",
            "bill_to_address": "vejlevej 259",
            "bill_to_postal_code": "6000",
            "bill_to_city": "kolding",
            "requisition_no": "martin olesen",
            "bill_to_country": "DK",
            "quantity": "14",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "giftwrap": "1",
            "contact_email": ""
        },
        "Timestamp": 1633602966,
        "check": {
            "check1": "1: BS59567 - 2021-10-07 12:37",
            "check2": "",
            "check3": "",
            "shopname": "Dr\u00f8mmegavekortet 200",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu,  7 Oct 2021 12:32:46 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "Malerfirmaet 2B as",
            "cvr": "34584524",
            "bill_to_address": "Ejby Industrivej 72",
            "bill_to_postal_code": "2600",
            "bill_to_city": "Glostrup",
            "requisition_no": "Bo Bjerregaard",
            "bill_to_country": "DK",
            "quantity": "45",
            "expire_date": "01-04-2022",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633602766,
        "check": {
            "check1": "1: BS59564 - 2021-10-07 12:33",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu,  7 Oct 2021 12:15:33 +0200 (CEST)",
        "input": {
            "shop_id": "2395",
            "companyname": "Eriksholm Skovdistrikt",
            "cvr": "28110529",
            "bill_to_address": "Eriksholmvej 40",
            "bill_to_postal_code": "4390",
            "bill_to_city": "Vipper\u00f8d",
            "requisition_no": "Charlotte Nielsen",
            "bill_to_country": "DK",
            "quantity": "5",
            "expire_date": "07-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633601733,
        "check": {
            "check1": "1: BS59558 - 2021-10-07 12:16",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekortet DK 960",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu,  7 Oct 2021 11:45:36 +0200 (CEST)",
        "input": {
            "shop_id": "575",
            "companyname": "Nordtronic A\/S",
            "cvr": "29808708",
            "bill_to_address": "Flade Engvej 4",
            "bill_to_postal_code": "9900",
            "bill_to_city": "Frederikshavn",
            "requisition_no": "Morten Lemvig",
            "bill_to_country": "DK",
            "quantity": "23",
            "expire_date": "14-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633599936,
        "check": {
            "check1": "1: BS59546 - 2021-10-07 11:48",
            "check2": "",
            "check3": "",
            "shopname": "Designjulegaven",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu,  7 Oct 2021 11:37:26 +0200 (CEST)",
        "input": {
            "shop_id": "310",
            "companyname": "kongevejens b\u00f8rnehus ",
            "cvr": "29189617",
            "ean": "5798005572060",
            "phone": "99604555",
            "contact_phone": "99604555",
            "contact_email": "lonie@ikast-brande.dk",
            "bill_to_address": "Kongevejen 1",
            "bill_to_postal_code": "7430",
            "bill_to_city": "Ikast",
            "requisition_no": "Lone Nielsen",
            "bill_to_country": "DK",
            "quantity": "34",
            "expire_date": "21-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_address": "Kongevejen 1",
            "ship_to_address_2": "Kongevejen 1",
            "ship_to_postal_code": "7430",
            "ship_to_city": "Ikast",
            "ship_to_country": "DK"
        },
        "Timestamp": 1633599446,
        "check": {
            "check1": "",
            "check2": "",
            "check3": "6: BS56752 - 2021-09-09 15:21",
            "shopname": "Dr\u00f8mmegavekortet 300",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu,  7 Oct 2021 11:04:45 +0200 (CEST)",
        "input": {
            "shop_id": "55",
            "companyname": "N\u00f8rre Djurs Hallen",
            "cvr": "46609719",
            "bill_to_address": "Idr\u00e6tsvej 2",
            "bill_to_postal_code": "8585",
            "bill_to_city": "Glesborg",
            "requisition_no": "Peter",
            "bill_to_country": "DK",
            "quantity": "15",
            "expire_date": "21-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633597485,
        "check": {
            "check1": "1: BS59523 - 2021-10-07 11:05",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 560",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu,  7 Oct 2021 10:58:31 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "Cortex Technology",
            "cvr": "78880813",
            "bill_to_address": "plastv\u00e6nget 9",
            "bill_to_postal_code": "9560",
            "bill_to_city": "hadsund",
            "requisition_no": "steffen vogel",
            "bill_to_country": "DK",
            "quantity": "16",
            "expire_date": "07-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633597111,
        "check": {
            "check1": "1: BS59519 - 2021-10-07 10:59",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu,  7 Oct 2021 10:52:33 +0200 (CEST)",
        "input": {
            "shop_id": "1832",
            "companyname": "HLR Proffsen i Sverige AB",
            "cvr": "556847-4653",
            "bill_to_address": "Drottninggatan 17",
            "bill_to_postal_code": "591 30",
            "bill_to_city": "Motala",
            "requisition_no": "Patrik Ullman",
            "bill_to_country": "SE",
            "quantity": "11",
            "expire_date": "31-12-2022",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "SE",
            "contact_email": ""
        },
        "Timestamp": 1633596753,
        "check": {
            "check1": "1: BS59518 - 2021-10-07 10:55",
            "check2": "",
            "check3": "",
            "shopname": "24Julklappar400 (Sverige)",
            "lang": 5
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu,  7 Oct 2021 10:27:13 +0200 (CEST)",
        "input": {
            "shop_id": "53",
            "companyname": "Screenpublisher ApS",
            "cvr": "36478446",
            "bill_to_address": "R\u00f8mersvej 4",
            "bill_to_postal_code": "7430",
            "bill_to_city": "Ikast",
            "requisition_no": "Annika Klausen",
            "bill_to_country": "DK",
            "quantity": "7",
            "expire_date": "21-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633595233,
        "check": {
            "check1": "1: BS59511 - 2021-10-07 10:29",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekortet DK 800",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu,  7 Oct 2021 10:17:45 +0200 (CEST)",
        "input": {
            "shop_id": "53",
            "companyname": "Akurat A\/S",
            "cvr": "39470268",
            "phone": "29160111",
            "contact_phone": "29160111",
            "contact_email": "pgr@akurat.dk",
            "bill_to_address": "Nupark 47",
            "bill_to_postal_code": "7500",
            "bill_to_city": "Holstebro",
            "requisition_no": "Peter Graugaard",
            "bill_to_country": "DK",
            "quantity": "17",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK"
        },
        "Timestamp": 1633594665,
        "check": {
            "check1": "1: BS59505 - 2021-10-07 10:18",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekortet DK 800",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu,  7 Oct 2021 10:01:29 +0200 (CEST)",
        "input": {
            "shop_id": "52",
            "companyname": "Kolding Krisecenter",
            "cvr": "71618714",
            "bill_to_address": "Nr. Bjertvej 89",
            "bill_to_postal_code": "6000",
            "bill_to_city": "Kolding",
            "requisition_no": "Britt Malkiel Nielsen",
            "bill_to_country": "DK",
            "quantity": "8",
            "expire_date": "14-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633593689,
        "check": {
            "check1": "1: BS59495 - 2021-10-07 10:02",
            "check2": "",
            "check3": "",
            "shopname": "Julegavekortet DK",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu,  7 Oct 2021 09:48:12 +0200 (CEST)",
        "input": {
            "shop_id": "53",
            "companyname": "Alflow Scandinavia ",
            "cvr": "28120826",
            "bill_to_address": "Industrivej Vest 36",
            "bill_to_postal_code": "6600",
            "bill_to_city": "Vejen",
            "requisition_no": "Jens Martin Andersen ",
            "bill_to_country": "DK",
            "quantity": "36",
            "expire_date": "21-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633592892,
        "check": {
            "check1": "1: BS59493 - 2021-10-07 09:49",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekortet DK 800",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu,  7 Oct 2021 09:39:39 +0200 (CEST)",
        "input": {
            "shop_id": "2395",
            "companyname": "GSM Teknik ApS",
            "cvr": "36503289",
            "bill_to_address": "S\u00f8nders\u00f8vej 4",
            "bill_to_postal_code": "5492",
            "bill_to_city": "Vissenbjerg",
            "requisition_no": "Tonie Fabricius Olsen",
            "bill_to_country": "DK",
            "quantity": "8",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633592379,
        "check": {
            "check1": "1: BS59492 - 2021-10-07 09:41",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekortet DK 960",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu,  7 Oct 2021 09:27:33 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "Hinnerupl\u00e6gerne",
            "cvr": "33095864",
            "phone": "29400025",
            "contact_phone": "29400025",
            "contact_email": "sekr.hinneruplaegerne@gmail.com",
            "bill_to_address": "Herredsvej 25",
            "bill_to_postal_code": "8382",
            "bill_to_city": "Hinnerup",
            "requisition_no": "Kirsten Berg",
            "bill_to_country": "DK",
            "quantity": "24",
            "expire_date": "07-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK"
        },
        "Timestamp": 1633591653,
        "check": {
            "check1": "1: BS59487 - 2021-10-07 09:28",
            "check2": "",
            "check3": "1: BS59489 - 2021-10-07 09:31",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu,  7 Oct 2021 09:19:15 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "Eigil Frederiksen A\/S",
            "cvr": "10664594",
            "bill_to_address": "Snedkergangen 1",
            "bill_to_postal_code": "2690",
            "bill_to_city": "Karlslunde",
            "requisition_no": "Pia Holmen Thorslev",
            "bill_to_country": "DK",
            "quantity": "16",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633591155,
        "check": {
            "check1": "1: BS59485 - 2021-10-07 09:19",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu,  7 Oct 2021 09:16:04 +0200 (CEST)",
        "input": {
            "shop_id": "2395",
            "companyname": "Dixie Aps",
            "cvr": "31583454",
            "bill_to_address": "kometvej 8b",
            "bill_to_postal_code": "8700",
            "bill_to_city": "horsens",
            "requisition_no": "allan feder",
            "bill_to_country": "DK",
            "quantity": "11",
            "expire_date": "07-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633590964,
        "check": {
            "check1": "1: BS59482 - 2021-10-07 09:16",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekortet DK 960",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu,  7 Oct 2021 09:08:29 +0200 (CEST)",
        "input": {
            "shop_id": "52",
            "companyname": "AM Handel og Service ApS",
            "cvr": "38735691",
            "bill_to_address": "Nordhavnsvej 24",
            "bill_to_postal_code": "8500",
            "bill_to_city": "Grenaa",
            "requisition_no": "Elisabeth Meyer",
            "bill_to_country": "DK",
            "quantity": "27",
            "expire_date": "31-10-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633590509,
        "check": {
            "check1": "1: BS59478 - 2021-10-07 09:09",
            "check2": "",
            "check3": "",
            "shopname": "Julegavekortet DK",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu,  7 Oct 2021 09:05:20 +0200 (CEST)",
        "input": {
            "shop_id": "310",
            "companyname": "Tj\u00f8rring Skole",
            "cvr": "29189919",
            "ean": "5798005495215",
            "phone": "96287070",
            "contact_phone": "96287070",
            "contact_email": "tjodv@herning.dk",
            "bill_to_address": "Gilmosevej 20",
            "bill_to_postal_code": "7400",
            "bill_to_city": "Herning",
            "requisition_no": "Dorthe Vesterb\u00e6k",
            "bill_to_country": "DK",
            "quantity": "62",
            "expire_date": "07-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK"
        },
        "Timestamp": 1633590320,
        "check": {
            "check1": "",
            "check2": "",
            "check3": "4: BS56335 - 2021-09-06 13:35",
            "shopname": "Dr\u00f8mmegavekortet 300",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu,  7 Oct 2021 08:32:06 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "Cham Lo Aps",
            "cvr": "42361275",
            "bill_to_address": "Sindshvilevej 9a, kld,",
            "bill_to_postal_code": "2000",
            "bill_to_city": "Frederiksberg",
            "bill_to_country": "DK",
            "quantity": "7",
            "expire_date": "07-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633588326,
        "check": {
            "check1": "",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu,  7 Oct 2021 08:24:31 +0200 (CEST)",
        "input": {
            "shop_id": "52",
            "companyname": "KS FOOD Consult ApS",
            "cvr": "26928826",
            "bill_to_address": "Park Alle 382",
            "bill_to_postal_code": "2625",
            "bill_to_city": "Vallensb\u00e6k",
            "requisition_no": "Kristian",
            "bill_to_country": "DK",
            "quantity": "5",
            "expire_date": "31-10-2021",
            "is_email": "1",
            "use_shipping_address": "1",
            "ship_to_company": "ks",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633587871,
        "check": {
            "check1": "1: BS59465 - 2021-10-07 08:25",
            "check2": "",
            "check3": "",
            "shopname": "Julegavekortet DK",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu,  7 Oct 2021 08:18:45 +0200 (CEST)",
        "input": {
            "shop_id": "575",
            "companyname": "Boligforeningen Kristiansdal",
            "cvr": "31491428",
            "ean": "5475121012803",
            "phone": "63142257",
            "contact_phone": "63142257",
            "contact_email": "jl@kristiansdal.dk",
            "bill_to_address": "Valmuemarken 27",
            "bill_to_postal_code": "5260",
            "bill_to_city": "Odense S",
            "requisition_no": "Jane Lyngs",
            "bill_to_country": "DK",
            "quantity": "46",
            "expire_date": "31-10-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "giftwrap": "1"
        },
        "Timestamp": 1633587525,
        "check": {
            "check1": "1: BS59463 - 2021-10-07 08:19",
            "check2": "",
            "check3": "",
            "shopname": "Designjulegaven",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu,  7 Oct 2021 07:52:34 +0200 (CEST)",
        "input": {
            "shop_id": "574",
            "companyname": "STAVANGER MOTORBAATFORENING",
            "cvr": "837748062",
            "bill_to_address": " Paradisveien 85 A",
            "bill_to_postal_code": "4012",
            "bill_to_city": "STAVANGER",
            "requisition_no": "Gro Hamre",
            "bill_to_country": "NO",
            "bill_to_email": "smbf@smbf.no",
            "quantity": "5",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "NO",
            "giftwrap": "1",
            "contact_email": ""
        },
        "Timestamp": 1633585954,
        "check": {
            "check1": "1: BS59456 - 2021-10-07 07:53",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekort NO",
            "lang": 4
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field cvr: value is required",
            "field": "cvr",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu,  7 Oct 2021 00:51:10 +0200 (CEST)",
        "input": {
            "shop_id": "57",
            "companyname": "STK",
            "bill_to_address": "Skaunaskogen 12",
            "bill_to_postal_code": "7357",
            "bill_to_city": "SKAUN",
            "bill_to_country": "NO",
            "bill_to_email": "sivto-k@hotmail.com",
            "quantity": "2",
            "expire_date": "31-12-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "NO",
            "cvr": "",
            "contact_email": ""
        },
        "Timestamp": 1633560670,
        "check": {
            "check1": "*",
            "check2": "*",
            "check3": "*",
            "shopname": "Julegavekortet NO 400",
            "lang": 4
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed,  6 Oct 2021 21:48:28 +0200 (CEST)",
        "input": {
            "shop_id": "575",
            "companyname": "Kjellerup Tandlaegecenter",
            "cvr": "37914339",
            "bill_to_address": "Sindingsgade 6",
            "bill_to_postal_code": "8620",
            "bill_to_city": "Kjellerup",
            "requisition_no": "Mehdi Kamstrup Nourbakhsh",
            "bill_to_country": "DK",
            "quantity": "13",
            "expire_date": "31-10-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633549708,
        "check": {
            "check1": "1: BS59453 - 2021-10-06 21:50",
            "check2": "",
            "check3": "",
            "shopname": "Designjulegaven",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed,  6 Oct 2021 19:45:39 +0200 (CEST)",
        "input": {
            "shop_id": "2558",
            "companyname": "Staavs Bygg i Motala AB",
            "cvr": "556784-5135",
            "phone": "0709692008",
            "contact_phone": "0709692008",
            "contact_email": "staaven@mackans.net",
            "bill_to_address": "\u00d6stermalmsgatan 93",
            "bill_to_postal_code": "59160",
            "bill_to_city": "Motala",
            "requisition_no": "Julklapp",
            "bill_to_country": "SE",
            "quantity": "6",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "1",
            "ship_to_company": "Staavs Bygg i Motala AB",
            "ship_to_postal_code": "59160",
            "ship_to_city": "Motala",
            "ship_to_country": "SE"
        },
        "Timestamp": 1633542339,
        "check": {
            "check1": "1: BS59448 - 2021-10-06 19:46",
            "check2": "",
            "check3": "",
            "shopname": "24 julklappar 1200 2021",
            "lang": 5
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed,  6 Oct 2021 17:31:22 +0200 (CEST)",
        "input": {
            "shop_id": "52",
            "companyname": "Skorstensfejermester Ole Bjerre Rasmussen",
            "cvr": "30864840",
            "phone": "26708066",
            "contact_phone": "26708066",
            "contact_email": "bjerre@skorstensfejer.nu",
            "bill_to_address": "Gyvelparken 14",
            "bill_to_postal_code": "6760 ",
            "bill_to_city": "Ribe ",
            "requisition_no": "Marianne Bjerre Rasmussen",
            "bill_to_country": "DK",
            "quantity": "10",
            "expire_date": "31-10-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK"
        },
        "Timestamp": 1633534282,
        "check": {
            "check1": "1: BS59443 - 2021-10-06 17:33",
            "check2": "",
            "check3": "",
            "shopname": "Julegavekortet DK",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed,  6 Oct 2021 16:16:58 +0200 (CEST)",
        "input": {
            "shop_id": "575",
            "companyname": "Viteco ApS",
            "cvr": "33885989",
            "bill_to_address": "Gothersgade 42 1",
            "bill_to_postal_code": "1123",
            "bill_to_city": "K\u00f8benhavn K",
            "requisition_no": "Peter Wiese",
            "bill_to_country": "DK",
            "quantity": "9",
            "expire_date": "14-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "giftwrap": "1",
            "contact_email": ""
        },
        "Timestamp": 1633529818,
        "check": {
            "check1": "1: BS59435 - 2021-10-06 16:17",
            "check2": "",
            "check3": "",
            "shopname": "Designjulegaven",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed,  6 Oct 2021 15:39:08 +0200 (CEST)",
        "input": {
            "shop_id": "310",
            "companyname": "Laegerne i Hovedgaard",
            "cvr": "42013749",
            "bill_to_address": "Stationsvej 4B",
            "bill_to_postal_code": "8732",
            "bill_to_city": "Hovedgaard",
            "requisition_no": "Camilla Olesen",
            "bill_to_country": "DK",
            "quantity": "5",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633527548,
        "check": {
            "check1": "1: BS59431 - 2021-10-06 15:40",
            "check2": "1: BS59855 - 2021-10-11 09:34",
            "check3": "1: BS59429 - 2021-10-06 15:33",
            "shopname": "Dr\u00f8mmegavekortet 300",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed,  6 Oct 2021 15:18:43 +0200 (CEST)",
        "input": {
            "shop_id": "2558",
            "companyname": "Sinfra",
            "cvr": "716419-3323",
            "bill_to_address": "Sinfra Box 1026",
            "bill_to_postal_code": "101 38",
            "bill_to_city": "Stockholm",
            "requisition_no": "Tony Doganson",
            "bill_to_country": "SE",
            "quantity": "15",
            "expire_date": "31-12-2022",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "SE",
            "giftwrap": "1",
            "contact_email": ""
        },
        "Timestamp": 1633526323,
        "check": {
            "check1": "2: BS59425 - 2021-10-06 15:20",
            "check2": "",
            "check3": "",
            "shopname": "24 julklappar 1200 2021",
            "lang": 5
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed,  6 Oct 2021 14:58:36 +0200 (CEST)",
        "input": {
            "shop_id": "2395",
            "companyname": "ENH Engineering A\/S",
            "cvr": "DK12795939",
            "bill_to_address": "Jellingvej 15",
            "bill_to_postal_code": "9230",
            "bill_to_city": "Svenstrup J",
            "requisition_no": "Jane Eliasen",
            "bill_to_country": "DK",
            "quantity": "30",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633525116,
        "check": {
            "check1": "",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekortet DK 960",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed,  6 Oct 2021 14:38:48 +0200 (CEST)",
        "input": {
            "shop_id": "575",
            "companyname": "IGEPA Group",
            "cvr": "58995916",
            "bill_to_address": "Jegstrupvej 60B",
            "bill_to_postal_code": "8361",
            "bill_to_city": "Hasselager",
            "requisition_no": "Direkt\u00f8r Flemming Friche",
            "bill_to_country": "DK",
            "quantity": "9",
            "expire_date": "14-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "giftwrap": "1",
            "contact_email": ""
        },
        "Timestamp": 1633523928,
        "check": {
            "check1": "1: BS59416 - 2021-10-06 14:39",
            "check2": "",
            "check3": "",
            "shopname": "Designjulegaven",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed,  6 Oct 2021 14:07:28 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "Tandl\u00e6gen dk Horsens",
            "cvr": "37452750",
            "bill_to_address": "Gr\u00f8nlandsvej 1, 1. sal",
            "bill_to_postal_code": "8700",
            "bill_to_city": "Horsens",
            "requisition_no": "Anders Boel",
            "bill_to_country": "DK",
            "quantity": "16",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633522048,
        "check": {
            "check1": "1: BS59410 - 2021-10-06 14:08",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed,  6 Oct 2021 14:07:03 +0200 (CEST)",
        "input": {
            "shop_id": "2395",
            "companyname": "SIGUNA INDUSTRI  MARINE ApS",
            "cvr": "40041699",
            "bill_to_address": "Nydamsvej 41-43",
            "bill_to_postal_code": "8362",
            "bill_to_city": "H\u00f8rning",
            "requisition_no": "Lars Skeldrup",
            "bill_to_country": "DK",
            "quantity": "5",
            "expire_date": "07-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633522023,
        "check": {
            "check1": "2: BS59407 - 2021-10-06 14:07",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekortet DK 960",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed,  6 Oct 2021 14:06:49 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "Tandl\u00e6gen.dk Horsens",
            "cvr": "37452750",
            "bill_to_address": "Gr\u00f8nlandsvej 1, 1. sal",
            "bill_to_postal_code": "8700",
            "bill_to_city": "Horsens",
            "requisition_no": "Anders Boel",
            "bill_to_country": "DK",
            "quantity": "16",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633522009,
        "check": {
            "check1": "1: BS59410 - 2021-10-06 14:08",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed,  6 Oct 2021 14:06:48 +0200 (CEST)",
        "input": {
            "shop_id": "2395",
            "companyname": "SIGUNA INDUSTRI & MARINE ApS",
            "cvr": "40041699",
            "bill_to_address": "Nydamsvej 41-43",
            "bill_to_postal_code": "8362",
            "bill_to_city": "H\u00f8rning",
            "requisition_no": "Lars Skeldrup",
            "bill_to_country": "DK",
            "quantity": "5",
            "expire_date": "07-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633522008,
        "check": {
            "check1": "2: BS59407 - 2021-10-06 14:07",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekortet DK 960",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field bill_to_city: string must be at least 3 characters",
            "field": "bill_to_city",
            "type": "tooshort",
            "min": "3",
            "max": "",
            "length": ""
        },
        "Date": " Wed,  6 Oct 2021 13:41:46 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": " Brejnholt Ry",
            "cvr": "39351803",
            "phone": "86891029",
            "contact_name": "Helle Jensen",
            "contact_phone": "86891029",
            "contact_email": "Hej@brejnholt.dk",
            "bill_to_address": "Kl\u00f8fteh\u00f8j 2",
            "bill_to_postal_code": "8680",
            "bill_to_city": "Ry",
            "requisition_no": "Helle Jensen",
            "bill_to_country": "DK",
            "quantity": "13",
            "expire_date": "21-11-2021",
            "is_email": "1",
            "use_shipping_address": "1",
            "ship_to_company": "Brejnholt Ry",
            "ship_to_address": "Kl\u00f8fteh\u00f8j 2",
            "ship_to_address_2": "Kl\u00f8fteh\u00f8j 2",
            "ship_to_postal_code": "8680",
            "ship_to_city": "Ry",
            "ship_to_country": "DK"
        },
        "Timestamp": 1633520506,
        "check": {
            "check1": "",
            "check2": "2: BS59396 - 2021-10-06 13:49",
            "check3": "",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed,  6 Oct 2021 13:37:59 +0200 (CEST)",
        "input": {
            "shop_id": "52",
            "companyname": "Faaborg Malerfirma",
            "cvr": "30198921",
            "phone": "26166117",
            "contact_phone": "26166117",
            "contact_email": "maler@faaborgmalerfirma.dk",
            "bill_to_address": "Odensevej 199",
            "bill_to_postal_code": "5600",
            "bill_to_city": "Faaborg",
            "requisition_no": "Lisbeth S\u00f8rensen",
            "bill_to_country": "DK",
            "quantity": "10",
            "expire_date": "31-10-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_address": "Odensevej 199",
            "ship_to_address_2": "Odensevej 199",
            "ship_to_postal_code": "5600",
            "ship_to_city": "Faaborg",
            "ship_to_country": "DK"
        },
        "Timestamp": 1633520279,
        "check": {
            "check1": "1: BS59393 - 2021-10-06 13:38",
            "check2": "",
            "check3": "2: BS59727 - 2021-10-08 11:58",
            "shopname": "Julegavekortet DK",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed,  6 Oct 2021 13:37:18 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "Xl-Byg  Brejnholt Ry",
            "cvr": "39351803",
            "bill_to_address": "Kl\u00f8fteh\u00f8j 2",
            "bill_to_postal_code": "8680",
            "bill_to_city": "Ry",
            "bill_to_country": "DK",
            "quantity": "13",
            "expire_date": "21-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633520238,
        "check": {
            "check1": "",
            "check2": "2: BS59396 - 2021-10-06 13:49",
            "check3": "",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed,  6 Oct 2021 13:35:28 +0200 (CEST)",
        "input": {
            "shop_id": "2395",
            "companyname": "ACTING Bygherrer\u00e5dgivning ApS",
            "cvr": "12417632",
            "ean": "5790002389276",
            "bill_to_address": "Vandt\u00e5rnsvej 62A, 4. sal C",
            "bill_to_postal_code": "2860",
            "bill_to_city": "S\u00f8borg",
            "requisition_no": "Anette",
            "bill_to_country": "DK",
            "quantity": "34",
            "expire_date": "07-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633520128,
        "check": {
            "check1": "1: BS59392 - 2021-10-06 13:36",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekortet DK 960",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed,  6 Oct 2021 13:33:09 +0200 (CEST)",
        "input": {
            "shop_id": "55",
            "companyname": "Mors\u00f8 St\u00e5lbyg",
            "cvr": "33962827",
            "bill_to_address": "Industrivej. 32",
            "bill_to_postal_code": "7900",
            "bill_to_city": "Nyk\u00f8bing Mors",
            "requisition_no": "Ragna Christensen",
            "bill_to_country": "DK",
            "quantity": "38",
            "expire_date": "07-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633519989,
        "check": {
            "check1": "1: BS59391 - 2021-10-06 13:34",
            "check2": "",
            "check3": "1: BS59397 - 2021-10-06 13:51",
            "shopname": "24 Gaver DK 560",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed,  6 Oct 2021 13:32:46 +0200 (CEST)",
        "input": {
            "shop_id": "53",
            "companyname": "Frederiksbergmuseerne",
            "cvr": "82330410",
            "bill_to_address": "Andebakkesti 5",
            "bill_to_postal_code": "2000",
            "bill_to_city": "Frederiksberg",
            "requisition_no": "Marion Limbrecht",
            "bill_to_country": "DK",
            "quantity": "24",
            "expire_date": "07-11-2021",
            "is_email": "0",
            "use_shipping_address": "1",
            "ship_to_company": "Fre",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633519966,
        "check": {
            "check1": "1: BS59390 - 2021-10-06 13:33",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekortet DK 800",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed,  6 Oct 2021 13:21:19 +0200 (CEST)",
        "input": {
            "shop_id": "55",
            "companyname": "H.E.W A\/S",
            "cvr": "14798285",
            "bill_to_address": "Sunek\u00e6r 6",
            "bill_to_postal_code": "5471",
            "bill_to_city": "S\u00f8nders\u00f8",
            "requisition_no": "Christine Wulff",
            "bill_to_country": "DK",
            "quantity": "10",
            "expire_date": "31-12-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633519279,
        "check": {
            "check1": "1: BS59386 - 2021-10-06 13:22",
            "check2": "",
            "check3": "1: BS59387 - 2021-10-06 13:23",
            "shopname": "24 Gaver DK 560",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed,  6 Oct 2021 13:17:32 +0200 (CEST)",
        "input": {
            "shop_id": "310",
            "companyname": "B\u00f8rnehuset Krogstenshave ",
            "cvr": "55606617",
            "ean": "5790000398409",
            "phone": "61916864",
            "contact_phone": "61916864",
            "contact_email": "ckl@hvidovre.dk",
            "bill_to_address": "Ejby Alle 34-35",
            "bill_to_postal_code": "2650",
            "bill_to_city": "Hvidovre",
            "requisition_no": "Charlotte ",
            "bill_to_country": "DK",
            "quantity": "41",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "1",
            "ship_to_company": "B\u00f8rnehuset Krogstenshave",
            "ship_to_address": "Ejby Alle 34-35",
            "ship_to_address_2": "Ejby Alle 34-35",
            "ship_to_postal_code": "2650",
            "ship_to_city": "Hvidovre",
            "ship_to_country": "DK"
        },
        "Timestamp": 1633519052,
        "check": {
            "check1": "1: BS59385 - 2021-10-06 13:17",
            "check2": "",
            "check3": "2: BS56328 - 2021-09-06 13:12",
            "shopname": "Dr\u00f8mmegavekortet 300",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed,  6 Oct 2021 12:42:32 +0200 (CEST)",
        "input": {
            "shop_id": "52",
            "companyname": "\u00d8rb\u00e6k Autogenbrug ApS",
            "cvr": "26086280",
            "phone": "63331442",
            "contact_phone": "63331442",
            "contact_email": "Ulla@65331420.dk",
            "bill_to_address": "Nyborgvej 29A",
            "bill_to_postal_code": "5853",
            "bill_to_city": "\u00d8rb\u00e6k",
            "requisition_no": "Ulla Andersen",
            "bill_to_country": "DK",
            "quantity": "30",
            "expire_date": "14-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK"
        },
        "Timestamp": 1633516952,
        "check": {
            "check1": "",
            "check2": "",
            "check3": "5: BS59447 - 2021-10-06 18:52",
            "shopname": "Julegavekortet DK",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field ship_to_postal_code: string must be at least 3 characters",
            "field": "ship_to_postal_code",
            "type": "tooshort",
            "min": "3",
            "max": "",
            "length": ""
        },
        "Date": " Wed,  6 Oct 2021 11:58:24 +0200 (CEST)",
        "input": {
            "shop_id": "52",
            "companyname": "Alfa Fredensborg",
            "cvr": "27099602",
            "phone": "48404060",
            "contact_name": "Hanne Petersen",
            "contact_phone": "48404060",
            "contact_email": "hp@alfa-fredensborg.dk",
            "bill_to_address": "Kongevejen 1",
            "bill_to_postal_code": "3480",
            "bill_to_city": "Fredensborg",
            "requisition_no": "Hanne Petersen",
            "bill_to_country": "DK",
            "quantity": "28",
            "expire_date": "14-11-2021",
            "is_email": "0",
            "use_shipping_address": "1",
            "ship_to_company": "Alfa Fredensborg",
            "ship_to_address": "Kongevejen 1",
            "ship_to_address_2": "Kongevejen 1",
            "ship_to_postal_code": "Ko",
            "ship_to_city": "3480 Fredensborg",
            "ship_to_country": "DK"
        },
        "Timestamp": 1633514304,
        "check": {
            "check1": "",
            "check2": "",
            "check3": "1: BS59369 - 2021-10-06 11:58",
            "shopname": "Julegavekortet DK",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed,  6 Oct 2021 11:56:29 +0200 (CEST)",
        "input": {
            "shop_id": "2395",
            "companyname": "Det Franske Conditori",
            "cvr": "18237571",
            "bill_to_address": "Hc \u00f8rstedsvej 44",
            "bill_to_postal_code": "1879",
            "bill_to_city": "Frb c",
            "requisition_no": "Henrik",
            "bill_to_country": "DK",
            "quantity": "10",
            "expire_date": "31-12-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633514189,
        "check": {
            "check1": "1: BS59368 - 2021-10-06 11:57",
            "check2": "",
            "check3": "1: BS59371 - 2021-10-06 12:05",
            "shopname": "Guldgavekortet DK 960",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed,  6 Oct 2021 11:47:14 +0200 (CEST)",
        "input": {
            "shop_id": "55",
            "companyname": "Toft Care",
            "cvr": "28672764",
            "phone": "60226522",
            "contact_phone": "60226522",
            "contact_email": "ts@toft-group.dk",
            "bill_to_address": "Smedevej 1, Harre",
            "bill_to_postal_code": "7870",
            "bill_to_city": "Roslev",
            "requisition_no": "Tenna",
            "bill_to_country": "DK",
            "quantity": "24",
            "expire_date": "07-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK"
        },
        "Timestamp": 1633513634,
        "check": {
            "check1": "",
            "check2": "",
            "check3": "1: BS59357 - 2021-10-06 11:47",
            "shopname": "24 Gaver DK 560",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field cvr: value is required",
            "field": "cvr",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed,  6 Oct 2021 11:33:49 +0200 (CEST)",
        "input": {
            "shop_id": "575",
            "companyname": "Meloh",
            "phone": "29910253",
            "contact_name": "Nadia J\u00f8rgensen",
            "contact_phone": "29910253",
            "contact_email": "nadia.4060@hotmail.com",
            "bill_to_address": "Svogerslev Hovedgade 78E",
            "bill_to_postal_code": "4000",
            "bill_to_city": "Roskilde",
            "bill_to_country": "DK",
            "quantity": "5",
            "expire_date": "31-10-2021",
            "is_email": "1",
            "use_shipping_address": "1",
            "ship_to_company": "Meloh",
            "ship_to_address": "Svogerslev Hovedgade 78E",
            "ship_to_address_2": "Svogerslev Hovedgade 78E",
            "ship_to_postal_code": "4000",
            "ship_to_city": "Roskilde",
            "ship_to_country": "DK",
            "cvr": ""
        },
        "Timestamp": 1633512829,
        "check": {
            "check1": "1: BS59351 - 2021-10-06 11:34",
            "check2": "",
            "check3": "1: BS59349 - 2021-10-06 11:32",
            "shopname": "Designjulegaven",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed,  6 Oct 2021 11:31:57 +0200 (CEST)",
        "input": {
            "shop_id": "2395",
            "companyname": "Meloh",
            "cvr": "38616072",
            "bill_to_address": "Svogerslev Hovedgade 78E",
            "bill_to_postal_code": "4000",
            "bill_to_city": "Roskilde",
            "requisition_no": "Nadia",
            "bill_to_country": "DK",
            "quantity": "5",
            "expire_date": "07-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633512717,
        "check": {
            "check1": "1: BS59349 - 2021-10-06 11:32",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekortet DK 960",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed,  6 Oct 2021 11:25:17 +0200 (CEST)",
        "input": {
            "shop_id": "53",
            "companyname": "Pallekoncept ApS",
            "cvr": "34716250",
            "bill_to_address": "K\u00f8gevej 230",
            "bill_to_postal_code": "4621",
            "bill_to_city": "Gadstrup",
            "requisition_no": "Jacek Majewski",
            "bill_to_country": "DK",
            "quantity": "10",
            "expire_date": "07-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633512317,
        "check": {
            "check1": "1: BS59344 - 2021-10-06 11:26",
            "check2": "",
            "check3": "2: BS59347 - 2021-10-06 11:29",
            "shopname": "Guldgavekortet DK 800",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed,  6 Oct 2021 11:19:00 +0200 (CEST)",
        "input": {
            "shop_id": "310",
            "companyname": "Gladsaxe Kommune, Boernehuset Stengaardsparken",
            "cvr": "62761113",
            "ean": "5798008694981",
            "bill_to_address": "Steng\u00e5rdsparken 21",
            "bill_to_postal_code": "2860",
            "bill_to_city": "S\u00f8borg",
            "requisition_no": "Maria Fynsk",
            "bill_to_country": "DK",
            "quantity": "16",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633511940,
        "check": {
            "check1": "1: BS59341 - 2021-10-06 11:19",
            "check2": "1: BS60099 - 2021-10-12 11:21",
            "check3": "1: BS57540 - 2021-09-22 11:22",
            "shopname": "Dr\u00f8mmegavekortet 300",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed,  6 Oct 2021 11:15:07 +0200 (CEST)",
        "input": {
            "shop_id": "52",
            "companyname": "Smiledesigns",
            "cvr": "37792276",
            "bill_to_address": "N\u00f8rre Farimagsgade 15",
            "bill_to_postal_code": "1364",
            "bill_to_city": "K\u00f8benhavn K",
            "requisition_no": "Mette Wiig",
            "bill_to_country": "DK",
            "quantity": "14",
            "expire_date": "31-10-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633511707,
        "check": {
            "check1": "1: BS59339 - 2021-10-06 11:15",
            "check2": "",
            "check3": "",
            "shopname": "Julegavekortet DK",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed,  6 Oct 2021 10:52:30 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "AE Maskiner ApS",
            "cvr": "33078943",
            "ean": "5790002503382",
            "bill_to_address": "Bronzevej 3",
            "bill_to_postal_code": "8940",
            "bill_to_city": "Randers SV",
            "requisition_no": "Julegaver 2021",
            "bill_to_country": "DK",
            "quantity": "44",
            "expire_date": "21-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633510350,
        "check": {
            "check1": "1: BS59332 - 2021-10-06 10:53",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed,  6 Oct 2021 10:42:29 +0200 (CEST)",
        "input": {
            "shop_id": "575",
            "companyname": "TinyMobileRobots ApS",
            "cvr": "37397350",
            "bill_to_address": "Sofienlystvej 9",
            "bill_to_postal_code": "8340",
            "bill_to_city": "Malling",
            "requisition_no": "Benita Schmidt",
            "bill_to_country": "DK",
            "quantity": "33",
            "expire_date": "14-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633509749,
        "check": {
            "check1": "",
            "check2": "2: BS59333 - 2021-10-06 10:58",
            "check3": "",
            "shopname": "Designjulegaven",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "30",
            "error": "Could not find valid shop_id"
        },
        "Date": " Wed,  6 Oct 2021 10:17:09 +0200 (CEST)",
        "input": {
            "companyname": "Alfa Koebenhavn ApS",
            "cvr": "28850662",
            "phone": "35353581",
            "contact_name": "Henriette Berendt",
            "contact_phone": "35353581",
            "contact_email": "faktura@alfakbh.dk",
            "bill_to_address": "Sankt Kjelds Plads 12,2",
            "bill_to_postal_code": "2100",
            "bill_to_city": "K\u00f8benhavn \u00d8",
            "requisition_no": "Morten Pedersen",
            "bill_to_country": "DK",
            "quantity": "36",
            "expire_date": "14-11-2021",
            "is_email": "0",
            "use_shipping_address": "1",
            "ship_to_company": "Alfa Koebenhavn ApS",
            "ship_to_address": "Sankt Kjelds Plads 12,2",
            "ship_to_address_2": "Sankt Kjelds Plads 12,2",
            "ship_to_postal_code": "2100",
            "ship_to_city": "K\u00f8benhavn \u00d8",
            "ship_to_country": "DK",
            "shop_id": ""
        },
        "Timestamp": 1633508229,
        "check": {
            "check1": "1: BS59323 - 2021-10-06 10:17",
            "check2": "",
            "check3": "",
            "shopname": "Ukendt",
            "lang": ""
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed,  6 Oct 2021 10:03:46 +0200 (CEST)",
        "input": {
            "shop_id": "59",
            "companyname": "North Well Gefro AS",
            "cvr": "993025046 ",
            "bill_to_address": "Harestadveien 77",
            "bill_to_postal_code": "4072",
            "bill_to_city": "RANDABERG",
            "requisition_no": "Erlend Auestad",
            "bill_to_country": "NO",
            "bill_to_email": "ea@nwg.no",
            "quantity": "41",
            "expire_date": "07-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "NO",
            "giftwrap": "1",
            "contact_email": ""
        },
        "Timestamp": 1633507426,
        "check": {
            "check1": "1: BS59316 - 2021-10-06 10:04",
            "check2": "",
            "check3": "",
            "shopname": "Julegavekortet NO 800",
            "lang": 4
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed,  6 Oct 2021 09:43:59 +0200 (CEST)",
        "input": {
            "shop_id": "52",
            "companyname": "Mayday film",
            "cvr": "29306230",
            "bill_to_address": "Gasv\u00e6rksvej 5",
            "bill_to_postal_code": "9000",
            "bill_to_city": "Aalborg",
            "requisition_no": "Stine Viuf",
            "bill_to_country": "DK",
            "quantity": "7",
            "expire_date": "14-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633506239,
        "check": {
            "check1": "1: BS59306 - 2021-10-06 09:44",
            "check2": "",
            "check3": "",
            "shopname": "Julegavekortet DK",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed,  6 Oct 2021 09:42:03 +0200 (CEST)",
        "input": {
            "shop_id": "290",
            "companyname": "Byens brod og kager",
            "cvr": "15585706",
            "bill_to_address": "\u00d8stergade 57",
            "bill_to_postal_code": "9400",
            "bill_to_city": "N\u00f8rresundby ",
            "requisition_no": "Anette ",
            "bill_to_country": "DK",
            "quantity": "21",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633506123,
        "check": {
            "check1": "1: BS59305 - 2021-10-06 09:43",
            "check2": "",
            "check3": "1: BS59307 - 2021-10-06 09:46",
            "shopname": "Dr\u00f8mmegavekortet 200",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed,  6 Oct 2021 08:36:49 +0200 (CEST)",
        "input": {
            "shop_id": "575",
            "companyname": "Laegehuset i Videbaek aps",
            "cvr": "18232545",
            "bill_to_address": "\u00d8rnevej 9",
            "bill_to_postal_code": "6920",
            "bill_to_city": "Videb\u00e6k",
            "requisition_no": "Mogens Brauner",
            "bill_to_country": "DK",
            "quantity": "8",
            "expire_date": "31-12-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633502209,
        "check": {
            "check1": "1: BS59281 - 2021-10-06 08:38",
            "check2": "",
            "check3": "",
            "shopname": "Designjulegaven",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed,  6 Oct 2021 08:26:05 +0200 (CEST)",
        "input": {
            "shop_id": "52",
            "companyname": "Bardram Luft Teknik",
            "cvr": "77982213",
            "bill_to_address": "S\u00f8nderskovvej 13",
            "bill_to_postal_code": "8362",
            "bill_to_city": "H\u00f8rning",
            "requisition_no": "Trine Spliid",
            "bill_to_country": "DK",
            "quantity": "15",
            "expire_date": "31-10-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633501565,
        "check": {
            "check1": "1: BS59277 - 2021-10-06 08:26",
            "check2": "",
            "check3": "1: BS60732 - 2021-10-15 10:09",
            "shopname": "Julegavekortet DK",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field expire_date: value is required",
            "field": "expire_date",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed,  6 Oct 2021 07:56:00 +0200 (CEST)",
        "input": {
            "shop_id": "574",
            "companyname": "O G Gulbrandsen as",
            "cvr": "913852419",
            "phone": "90977285",
            "contact_name": "Ole Gunnar",
            "contact_phone": "90977285",
            "contact_email": "O.g@gulbrandsenas.no",
            "bill_to_address": "Fossplassvegen 1",
            "bill_to_postal_code": "3622",
            "bill_to_city": "Svene",
            "requisition_no": "Oleg",
            "bill_to_country": "NO",
            "bill_to_email": "o.g@gulbrandsenas.no",
            "quantity": "5",
            "is_email": "1",
            "use_shipping_address": "1",
            "ship_to_company": "O G GUlbrandsen as",
            "ship_to_address": "Fossplassvegen 1",
            "ship_to_address_2": "Fossplassvegen 1",
            "ship_to_postal_code": "3622",
            "ship_to_city": "Svene",
            "ship_to_country": "NO"
        },
        "Timestamp": 1633499760,
        "check": {
            "check1": "1: BS59269 - 2021-10-06 07:56",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekort NO",
            "lang": 4
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue,  5 Oct 2021 21:37:13 +0200 (CEST)",
        "input": {
            "shop_id": "290",
            "companyname": "haderslev fysioterapi",
            "cvr": "35109382",
            "bill_to_address": "n\u00f8rregade 52",
            "bill_to_postal_code": "6100",
            "bill_to_city": "haderslev",
            "requisition_no": "anita nicolaysen ",
            "bill_to_country": "DK",
            "quantity": "8",
            "expire_date": "07-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633462633,
        "check": {
            "check1": "1: BS59267 - 2021-10-05 21:38",
            "check2": "",
            "check3": "",
            "shopname": "Dr\u00f8mmegavekortet 200",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue,  5 Oct 2021 21:29:46 +0200 (CEST)",
        "input": {
            "shop_id": "2395",
            "companyname": "C-SOLUTION ApS",
            "cvr": "33860714",
            "bill_to_address": "Vr\u00f8ndingvej 7",
            "bill_to_postal_code": "8700",
            "bill_to_city": "Horsens",
            "bill_to_country": "DK",
            "quantity": "19",
            "expire_date": "07-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633462186,
        "check": {
            "check1": "1: BS59266 - 2021-10-05 21:30",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekortet DK 960",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue,  5 Oct 2021 21:25:36 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "Fredensborg Sundhedscenter",
            "cvr": "12628196",
            "phone": "26208105",
            "contact_phone": "26208105",
            "contact_email": "Berit.e@mail.dk",
            "bill_to_address": "Jernbanegade 16",
            "bill_to_postal_code": "3480",
            "bill_to_city": "Fredensborg",
            "requisition_no": "Berit",
            "bill_to_country": "DK",
            "quantity": "8",
            "expire_date": "07-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK"
        },
        "Timestamp": 1633461936,
        "check": {
            "check1": "1: BS59265 - 2021-10-05 21:26",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue,  5 Oct 2021 21:21:14 +0200 (CEST)",
        "input": {
            "shop_id": "52",
            "companyname": "Ottensten",
            "cvr": "17612506",
            "bill_to_address": "Alstrup Alle 7",
            "bill_to_postal_code": "8361",
            "bill_to_city": "Hasselager",
            "requisition_no": "Inja Laganin",
            "bill_to_country": "DK",
            "quantity": "6",
            "expire_date": "14-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633461674,
        "check": {
            "check1": "1: BS59264 - 2021-10-05 21:21",
            "check2": "",
            "check3": "",
            "shopname": "Julegavekortet DK",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue,  5 Oct 2021 20:44:54 +0200 (CEST)",
        "input": {
            "shop_id": "575",
            "companyname": "B\u00e6lum Auto og Karosseri",
            "cvr": "32019358",
            "phone": "20405498",
            "contact_phone": "20405498",
            "contact_email": "ml@baelumauto.dk",
            "bill_to_address": "H\u00e5ndv\u00e6rkervej 1",
            "bill_to_postal_code": "9574",
            "bill_to_city": "B\u00e6lum",
            "requisition_no": "Marie-Louise",
            "bill_to_country": "DK",
            "quantity": "6",
            "expire_date": "31-10-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK"
        },
        "Timestamp": 1633459494,
        "check": {
            "check1": "1: BS59263 - 2021-10-05 20:45",
            "check2": "",
            "check3": "",
            "shopname": "Designjulegaven",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field cvr: value is required",
            "field": "cvr",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue,  5 Oct 2021 20:21:30 +0200 (CEST)",
        "input": {
            "shop_id": "575",
            "companyname": "L\u00e6gerne holsedore ",
            "bill_to_address": "Skt Anne plads 4, underetagen ",
            "bill_to_postal_code": "5000",
            "bill_to_city": "Odense c",
            "requisition_no": "Ann B\u00f8nnelykke ",
            "bill_to_country": "DK",
            "quantity": "6",
            "expire_date": "31-10-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "cvr": "",
            "contact_email": ""
        },
        "Timestamp": 1633458090,
        "check": {
            "check1": "*",
            "check2": "*",
            "check3": "*",
            "shopname": "Designjulegaven",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue,  5 Oct 2021 19:22:05 +0200 (CEST)",
        "input": {
            "shop_id": "52",
            "companyname": "Laegerne I Solbjerg",
            "cvr": "10031648",
            "bill_to_address": "Solbjerg Hedevej 43",
            "bill_to_postal_code": "8355",
            "bill_to_city": "Solbjerg",
            "requisition_no": "Troels Treebak",
            "bill_to_country": "DK",
            "quantity": "9",
            "expire_date": "14-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633454525,
        "check": {
            "check1": "1: BS59257 - 2021-10-05 19:22",
            "check2": "",
            "check3": "1: BS59256 - 2021-10-05 19:18",
            "shopname": "Julegavekortet DK",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue,  5 Oct 2021 17:19:55 +0200 (CEST)",
        "input": {
            "shop_id": "310",
            "companyname": "BoligOne N\u00e6stved",
            "cvr": "38619268",
            "bill_to_address": "Pr\u00e6st\u00f8vej 57",
            "bill_to_postal_code": "4700",
            "bill_to_city": "N\u00e6stved",
            "requisition_no": "Gitte K\u00f8tter",
            "bill_to_country": "DK",
            "quantity": "6",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633447195,
        "check": {
            "check1": "1: BS59255 - 2021-10-05 17:21",
            "check2": "",
            "check3": "",
            "shopname": "Dr\u00f8mmegavekortet 300",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue,  5 Oct 2021 16:46:02 +0200 (CEST)",
        "input": {
            "shop_id": "2395",
            "companyname": "BOGELUND VVS AS",
            "cvr": "27373690",
            "ean": "5790002434839",
            "bill_to_address": "Hvidsv\u00e6rmervej 127",
            "bill_to_postal_code": "2610",
            "bill_to_city": "R\u00f8dovre",
            "requisition_no": "lg",
            "bill_to_country": "DK",
            "quantity": "10",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633445162,
        "check": {
            "check1": "1: BS59254 - 2021-10-05 16:46",
            "check2": "",
            "check3": "1: BS59253 - 2021-10-05 16:43",
            "shopname": "Guldgavekortet DK 960",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue,  5 Oct 2021 15:47:26 +0200 (CEST)",
        "input": {
            "shop_id": "54",
            "companyname": "S\u00f8ren Lund M\u00f8bler A\/S",
            "cvr": "53682812",
            "bill_to_address": "Hvilhusevej 13, T\u00f8rring",
            "bill_to_postal_code": "8983",
            "bill_to_city": "Gjerlev J",
            "requisition_no": "Ove Knudsen",
            "bill_to_country": "DK",
            "quantity": "25",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633441646,
        "check": {
            "check1": "1: BS59239 - 2021-10-05 15:48",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 400",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field ship_to_address: value is required",
            "field": "ship_to_address",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue,  5 Oct 2021 15:47:22 +0200 (CEST)",
        "input": {
            "shop_id": "575",
            "companyname": "Brinks",
            "cvr": "32149596",
            "phone": "41109441",
            "contact_name": "Mona Brink Uhre",
            "contact_phone": "41109441",
            "contact_email": "mona.uhre@gmail.com",
            "bill_to_address": "N\u00f8rregade 41",
            "bill_to_postal_code": "7500",
            "bill_to_city": "Holstebro",
            "requisition_no": "Mona",
            "bill_to_country": "DK",
            "quantity": "5",
            "expire_date": "31-10-2021",
            "is_email": "0",
            "use_shipping_address": "1",
            "ship_to_company": "Brink`s",
            "ship_to_country": "DK"
        },
        "Timestamp": 1633441642,
        "check": {
            "check1": "1: BS59238 - 2021-10-05 15:48",
            "check2": "",
            "check3": "1: BS59243 - 2021-10-05 15:51",
            "shopname": "Designjulegaven",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue,  5 Oct 2021 15:43:52 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "DATS",
            "cvr": "33298013",
            "bill_to_address": "Vindegade 34 st. dk.",
            "bill_to_postal_code": "5000",
            "bill_to_city": "Odense C",
            "requisition_no": "Pia Longet",
            "bill_to_country": "DK",
            "quantity": "8",
            "expire_date": "07-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633441432,
        "check": {
            "check1": "1: BS59237 - 2021-10-05 15:44",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue,  5 Oct 2021 15:32:54 +0200 (CEST)",
        "input": {
            "shop_id": "2550",
            "companyname": "Nesodden Bygg as",
            "cvr": "898569802",
            "bill_to_address": "\u00c5senveien 5",
            "bill_to_postal_code": "1458",
            "bill_to_city": "Fjellstrand",
            "requisition_no": "Maria",
            "bill_to_country": "NO",
            "bill_to_email": "nesoddenbyggas@ebilag.com",
            "quantity": "11",
            "expire_date": "21-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "NO",
            "contact_email": ""
        },
        "Timestamp": 1633440774,
        "check": {
            "check1": "1: BS59233 - 2021-10-05 15:34",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekort 1200 (NO)",
            "lang": 4
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue,  5 Oct 2021 14:44:30 +0200 (CEST)",
        "input": {
            "shop_id": "52",
            "companyname": "Bryggens B\u00f8rne og Ungdomscenter",
            "cvr": "49732228",
            "ean": "5798009696328",
            "bill_to_address": "Artillerivej 71 C",
            "bill_to_postal_code": "2300",
            "bill_to_city": "K\u00f8benhavn S",
            "requisition_no": "120",
            "bill_to_country": "DK",
            "quantity": "22",
            "expire_date": "14-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633437870,
        "check": {
            "check1": "1: BS59221 - 2021-10-05 14:45",
            "check2": "",
            "check3": "",
            "shopname": "Julegavekortet DK",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue,  5 Oct 2021 14:20:12 +0200 (CEST)",
        "input": {
            "shop_id": "290",
            "companyname": "Bofaellesskabet Rose Marie",
            "cvr": "29250804",
            "phone": "27638900",
            "contact_phone": "27638900",
            "contact_email": "ges@mariehjem.dk",
            "bill_to_address": "Brodersens Alle",
            "bill_to_postal_code": "2900",
            "bill_to_city": "Hellerup",
            "requisition_no": "Gertrud S\u00f8rensen",
            "bill_to_country": "DK",
            "quantity": "10",
            "expire_date": "31-12-2021",
            "is_email": "0",
            "use_shipping_address": "1",
            "ship_to_company": "Bof\u00e6llesskabet Rose Marie",
            "ship_to_address": "Brodersens Alle",
            "ship_to_address_2": "Brodersens Alle",
            "ship_to_postal_code": "2900",
            "ship_to_city": "Hellerup",
            "ship_to_country": "DK",
            "giftwrap": "1"
        },
        "Timestamp": 1633436412,
        "check": {
            "check1": "1: BS59210 - 2021-10-05 14:20",
            "check2": "",
            "check3": "1: BS59213 - 2021-10-05 14:26",
            "shopname": "Dr\u00f8mmegavekortet 200",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue,  5 Oct 2021 14:04:33 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "Brdr Holst Sorensen AS",
            "cvr": "47807417",
            "bill_to_address": "Obbekj\u00e6rvej 105-107",
            "bill_to_postal_code": "6760",
            "bill_to_city": "Ribe",
            "requisition_no": "Flemming Bj\u00f8dstrup Madsen",
            "bill_to_country": "DK",
            "quantity": "60",
            "expire_date": "07-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633435473,
        "check": {
            "check1": "1: BS59207 - 2021-10-05 14:06",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue,  5 Oct 2021 14:02:58 +0200 (CEST)",
        "input": {
            "shop_id": "575",
            "companyname": "Cargill Nordic",
            "cvr": "11921019",
            "phone": "45469002",
            "contact_phone": "45469002",
            "contact_email": "anne-sofie_christiansen@cargill.com",
            "bill_to_address": "Vandt\u00e5rnsvej 62B",
            "bill_to_postal_code": "2860",
            "bill_to_city": "S\u00f8borg",
            "requisition_no": "Anne Sofie Christiansen",
            "bill_to_country": "DK",
            "quantity": "10",
            "expire_date": "31-10-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK"
        },
        "Timestamp": 1633435378,
        "check": {
            "check1": "",
            "check2": "",
            "check3": "1: BS59205 - 2021-10-05 14:04",
            "shopname": "Designjulegaven",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue,  5 Oct 2021 14:02:48 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "Brdr. Holst S\u00f8rensen",
            "cvr": "47807417",
            "bill_to_address": "Obbekj\u00e6rvej 105-107",
            "bill_to_postal_code": "6760",
            "bill_to_city": "Ribe",
            "requisition_no": "Flemming Bj\u00f8dstrup Madsen",
            "bill_to_country": "DK",
            "quantity": "60",
            "expire_date": "07-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633435368,
        "check": {
            "check1": "1: BS59207 - 2021-10-05 14:06",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue,  5 Oct 2021 13:11:28 +0200 (CEST)",
        "input": {
            "shop_id": "52",
            "companyname": "House of Kids ApS",
            "cvr": "29831386",
            "bill_to_address": "Jernaldervej 10",
            "bill_to_postal_code": "8300",
            "bill_to_city": "Odder",
            "requisition_no": "Jesper Rising Rasmussen ",
            "bill_to_country": "DK",
            "quantity": "20",
            "expire_date": "31-12-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633432288,
        "check": {
            "check1": "1: BS59193 - 2021-10-05 13:11",
            "check2": "",
            "check3": "",
            "shopname": "Julegavekortet DK",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue,  5 Oct 2021 12:53:05 +0200 (CEST)",
        "input": {
            "shop_id": "290",
            "companyname": "Spar Borrevejle ApS",
            "cvr": "38778501",
            "phone": "20458749",
            "contact_phone": "20458749",
            "contact_email": "malene.knudsen@spar.dk",
            "bill_to_address": "Hornsherredvej 3",
            "bill_to_postal_code": "4060",
            "bill_to_city": "Kirke S\u00e5by",
            "requisition_no": "Julegaver",
            "bill_to_country": "DK",
            "quantity": "20",
            "expire_date": "07-11-2021",
            "is_email": "1",
            "use_shipping_address": "1",
            "ship_to_company": "Spar Borrevejle ApS",
            "ship_to_address": "Hornsherredvej 3",
            "ship_to_address_2": "Hornsherredvej 3",
            "ship_to_postal_code": "4060",
            "ship_to_city": "Kirke S\u00e5by",
            "ship_to_country": "DK",
            "giftwrap": "1"
        },
        "Timestamp": 1633431185,
        "check": {
            "check1": "1: BS59187 - 2021-10-05 12:53",
            "check2": "",
            "check3": "2: BS59186 - 2021-10-05 12:49",
            "shopname": "Dr\u00f8mmegavekortet 200",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue,  5 Oct 2021 12:48:11 +0200 (CEST)",
        "input": {
            "shop_id": "52",
            "companyname": "Spar Borrevejle ApS",
            "cvr": "38778501",
            "bill_to_address": "Hornsherredvej 3",
            "bill_to_postal_code": "4060",
            "bill_to_city": "Kirke S\u00e5by",
            "requisition_no": "Julegaver",
            "bill_to_country": "DK",
            "quantity": "15",
            "expire_date": "31-10-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633430891,
        "check": {
            "check1": "1: BS59186 - 2021-10-05 12:49",
            "check2": "",
            "check3": "2: BS59187 - 2021-10-05 12:53",
            "shopname": "Julegavekortet DK",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue,  5 Oct 2021 12:46:27 +0200 (CEST)",
        "input": {
            "shop_id": "2550",
            "companyname": "Headspin AS",
            "cvr": " 914 036 534",
            "bill_to_address": "Nedre Bakklandet 58C",
            "bill_to_postal_code": "7014",
            "bill_to_city": "Trondheim ",
            "requisition_no": "Saira M. Butt",
            "bill_to_country": "NO",
            "bill_to_email": "faktura@headspin.no",
            "quantity": "30",
            "expire_date": "07-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "NO",
            "giftwrap": "1",
            "contact_email": ""
        },
        "Timestamp": 1633430787,
        "check": {
            "check1": "",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekort 1200 (NO)",
            "lang": 4
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue,  5 Oct 2021 12:36:29 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "Fyens Vaeddeloebsbane AS",
            "cvr": "19965910",
            "bill_to_address": "Prins Haralds All\u00e9 51 B",
            "bill_to_postal_code": "5250",
            "bill_to_city": "Odense S",
            "requisition_no": "Vicky J\u00f8rgensen",
            "bill_to_country": "DK",
            "quantity": "8",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633430189,
        "check": {
            "check1": "1: BS59180 - 2021-10-05 12:37",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue,  5 Oct 2021 12:14:40 +0200 (CEST)",
        "input": {
            "shop_id": "53",
            "companyname": "Gyldenholm Gods",
            "cvr": "28197365",
            "bill_to_address": "Gyldenholmvej 8A",
            "bill_to_postal_code": "4200",
            "bill_to_city": "Slagelse",
            "requisition_no": "Jacob Neergaard",
            "bill_to_country": "DK",
            "quantity": "6",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633428880,
        "check": {
            "check1": "1: BS59175 - 2021-10-05 12:15",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekortet DK 800",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue,  5 Oct 2021 12:06:10 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "Kosan Gascenter Sjaelland",
            "cvr": "40053077",
            "bill_to_address": "Kongstedvej 2d",
            "bill_to_postal_code": "4200",
            "bill_to_city": "Slagelse",
            "requisition_no": "Michael",
            "bill_to_country": "DK",
            "quantity": "10",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633428370,
        "check": {
            "check1": "1: BS59173 - 2021-10-05 12:06",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue,  5 Oct 2021 12:02:37 +0200 (CEST)",
        "input": {
            "shop_id": "310",
            "companyname": "Gefionsgaarden Horsens",
            "cvr": "58893811",
            "ean": "5790001661991",
            "bill_to_address": "S\u00f8nderbrogade 74",
            "bill_to_postal_code": "8700",
            "bill_to_city": "Horsens",
            "requisition_no": "Helle",
            "bill_to_country": "DK",
            "quantity": "33",
            "expire_date": "07-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633428157,
        "check": {
            "check1": "",
            "check2": "1: BS59174 - 2021-10-05 12:07",
            "check3": "",
            "shopname": "Dr\u00f8mmegavekortet 300",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue,  5 Oct 2021 11:37:26 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "Vejle Motor Compagni ",
            "cvr": "35839186",
            "phone": "26297423",
            "contact_phone": "26297423",
            "contact_email": "mrp@vmc-vejle.dk",
            "bill_to_address": "Diskovej 1 ",
            "bill_to_postal_code": "7100",
            "bill_to_city": "Vejle ",
            "requisition_no": "Trine Poulsen ",
            "bill_to_country": "DK",
            "quantity": "8",
            "expire_date": "31-12-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK"
        },
        "Timestamp": 1633426646,
        "check": {
            "check1": "",
            "check2": "",
            "check3": "1: BS59165 - 2021-10-05 11:38",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue,  5 Oct 2021 11:28:50 +0200 (CEST)",
        "input": {
            "shop_id": "2395",
            "companyname": "Laegerne Ved Dammen",
            "cvr": "39280418",
            "bill_to_address": "Bispebroen 2B",
            "bill_to_postal_code": "6100",
            "bill_to_city": "Haderslev",
            "requisition_no": "Kirsten Damkj\u00e6r",
            "bill_to_country": "DK",
            "quantity": "7",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633426130,
        "check": {
            "check1": "1: BS59164 - 2021-10-05 11:29",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekortet DK 960",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue,  5 Oct 2021 10:57:18 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "Curfew Copenhagen ApS",
            "cvr": "42175382",
            "phone": "21304420",
            "contact_phone": "21304420",
            "contact_email": "bookkeeping@curfew.dk",
            "bill_to_address": "Gartnerhaven 10",
            "bill_to_postal_code": "2800",
            "bill_to_city": "Kgs Lyngby",
            "requisition_no": "Camilla Marques",
            "bill_to_country": "DK",
            "quantity": "5",
            "expire_date": "01-04-2022",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK"
        },
        "Timestamp": 1633424238,
        "check": {
            "check1": "1: BS59154 - 2021-10-05 10:57",
            "check2": "",
            "check3": "1: BS59159 - 2021-10-05 10:59",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue,  5 Oct 2021 10:43:05 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "HDI Global Specialty",
            "cvr": "41268638",
            "bill_to_address": "Indiakaj 6, 1. sal",
            "bill_to_postal_code": "2100",
            "bill_to_city": "\u00d8sterbro",
            "requisition_no": "Ditte Friis ",
            "bill_to_country": "DK",
            "quantity": "51",
            "expire_date": "21-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633423385,
        "check": {
            "check1": "1: BS59147 - 2021-10-05 10:43",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue,  5 Oct 2021 10:06:38 +0200 (CEST)",
        "input": {
            "shop_id": "53",
            "companyname": "Cypax A\/S",
            "cvr": "16029378",
            "bill_to_address": "Industrivej 24",
            "bill_to_postal_code": "7470",
            "bill_to_city": "Karup J",
            "requisition_no": "Benny Hansen",
            "bill_to_country": "DK",
            "quantity": "5",
            "expire_date": "07-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633421198,
        "check": {
            "check1": "1: BS59135 - 2021-10-05 10:07",
            "check2": "",
            "check3": "1: BS58982 - 2021-10-04 11:15",
            "shopname": "Guldgavekortet DK 800",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue,  5 Oct 2021 09:34:26 +0200 (CEST)",
        "input": {
            "shop_id": "272",
            "companyname": "Blokkhaugen barnehage",
            "cvr": "964338531",
            "bill_to_address": "Myrdalskogen 55",
            "bill_to_postal_code": "5118",
            "bill_to_city": "Ulset",
            "requisition_no": "148644",
            "bill_to_country": "NO",
            "bill_to_email": "blokkhaugen.barnehage@bergen.kommune.no",
            "quantity": "21",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "NO",
            "contact_email": ""
        },
        "Timestamp": 1633419266,
        "check": {
            "check1": "1: BS59112 - 2021-10-05 09:34",
            "check2": "1: BS59272 - 2021-10-06 08:10",
            "check3": "1: BS57955 - 2021-09-27 10:16",
            "shopname": "Julegavekortet NO 300",
            "lang": 4
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field companyname: value is required",
            "field": "companyname",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue,  5 Oct 2021 08:20:49 +0200 (CEST)",
        "input": {
            "shop_id": "52",
            "bill_to_country": "DK",
            "quantity": "6",
            "expire_date": "31-10-2021",
            "is_email": "0",
            "ship_to_country": "DK",
            "cvr": "",
            "contact_email": ""
        },
        "Timestamp": 1633414849,
        "check": {
            "check1": "*",
            "check2": "*",
            "check3": "*",
            "shopname": "Julegavekortet DK",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue,  5 Oct 2021 08:16:27 +0200 (CEST)",
        "input": {
            "shop_id": "54",
            "companyname": "Erhvervsgymnasiet Grindsted",
            "cvr": "18117118",
            "ean": "5798000553880",
            "phone": "76720520",
            "contact_phone": "76720520",
            "contact_email": "pia.ravnkilde@eggrindsted.dk",
            "bill_to_address": "Tinghusgade 22",
            "bill_to_postal_code": "7200",
            "bill_to_city": "Grindsted",
            "requisition_no": "Pia Ravnkilde",
            "bill_to_country": "DK",
            "quantity": "30",
            "expire_date": "07-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK"
        },
        "Timestamp": 1633414587,
        "check": {
            "check1": "1: BS59091 - 2021-10-05 08:16",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 400",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field phone: string must be longer than 12 characters",
            "field": "phone",
            "type": "toolong",
            "min": "12",
            "max": "",
            "length": ""
        },
        "Date": " Mon,  4 Oct 2021 21:53:40 +0200 (CEST)",
        "input": {
            "shop_id": "53",
            "companyname": "Hair Fashion",
            "cvr": "25012666",
            "phone": "98112177\/40506484",
            "contact_name": "Betina Ravnborg",
            "contact_phone": "98112177\/40506484",
            "contact_email": "betina.ravnborg@hotmail.com",
            "bill_to_address": "Rantzausgade 3",
            "bill_to_postal_code": "9000",
            "bill_to_city": "Aalborg",
            "requisition_no": "Betina Ravnborg",
            "bill_to_country": "DK",
            "quantity": "5",
            "expire_date": "07-11-2021",
            "is_email": "1",
            "use_shipping_address": "1",
            "ship_to_company": "Betina Ravnborg",
            "ship_to_address": "Kronosvej 2",
            "ship_to_address_2": "Kronosvej 2",
            "ship_to_postal_code": "9210",
            "ship_to_city": "Aalborg s\u00f8",
            "ship_to_country": "DK"
        },
        "Timestamp": 1633377220,
        "check": {
            "check1": "",
            "check2": "1: BS59089 - 2021-10-05 08:12",
            "check3": "1: BS59655 - 2021-10-08 08:10",
            "shopname": "Guldgavekortet DK 800",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon,  4 Oct 2021 20:57:29 +0200 (CEST)",
        "input": {
            "shop_id": "2395",
            "companyname": "Clean Up af 2005 ApS",
            "cvr": "38786903",
            "phone": "61300143",
            "contact_phone": "61300143",
            "contact_email": "cleanup.dk@gmail.com",
            "bill_to_address": "Nymarks V\u00e6nge 12",
            "bill_to_postal_code": "4000",
            "bill_to_city": "Roskilde",
            "requisition_no": "Henrik Eriksen",
            "bill_to_country": "DK",
            "quantity": "24",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK"
        },
        "Timestamp": 1633373849,
        "check": {
            "check1": "1: BS59085 - 2021-10-04 21:13",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekortet DK 960",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field phone: string must be at least 8 characters",
            "field": "phone",
            "type": "tooshort",
            "min": "8",
            "max": "",
            "length": ""
        },
        "Date": " Mon,  4 Oct 2021 20:46:57 +0200 (CEST)",
        "input": {
            "shop_id": "2548",
            "companyname": "test",
            "cvr": "134213131231",
            "phone": "123123",
            "contact_name": "123124124",
            "contact_phone": "123123",
            "contact_email": "asfqef@dafs.com",
            "bill_to_address": "test",
            "bill_to_postal_code": "1234",
            "bill_to_city": "test",
            "requisition_no": "12341234",
            "bill_to_country": "DK",
            "quantity": "5",
            "expire_date": "31-10-2021",
            "is_email": "0",
            "use_shipping_address": "1",
            "ship_to_company": "werweff",
            "ship_to_address": "13413314",
            "ship_to_address_2": "13413314",
            "ship_to_postal_code": "134134134",
            "ship_to_city": "byby",
            "ship_to_country": "DK"
        },
        "Timestamp": 1633373217,
        "check": {
            "check1": "",
            "check2": "",
            "check3": "1: BS59084 - 2021-10-04 20:47",
            "shopname": "Det gr\u00f8nne gavekort",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon,  4 Oct 2021 16:35:12 +0200 (CEST)",
        "input": {
            "shop_id": "2395",
            "companyname": "Logiva A\/S",
            "cvr": "21742473",
            "bill_to_address": "Sk\u00e6ringvej 110",
            "bill_to_postal_code": "8520",
            "bill_to_city": "Lystrup",
            "requisition_no": "Michael Mikkelsen",
            "bill_to_country": "DK",
            "quantity": "12",
            "expire_date": "07-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633358112,
        "check": {
            "check1": "1: BS59077 - 2021-10-04 16:36",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekortet DK 960",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "30",
            "error": "Could not find valid shop_id"
        },
        "Date": " Mon,  4 Oct 2021 16:26:36 +0200 (CEST)",
        "input": {
            "companyname": "marianne s\u00f8rensen mad",
            "cvr": "27575919",
            "phone": "20942224",
            "contact_phone": "20942224",
            "contact_email": "mariannesorensenmad@gmail.com",
            "bill_to_address": "Byggebjerg 2",
            "bill_to_postal_code": "6534",
            "bill_to_city": "agerskov",
            "requisition_no": "marianne",
            "bill_to_country": "DK",
            "quantity": "5",
            "expire_date": "14-11-2021",
            "is_email": "0",
            "use_shipping_address": "1",
            "ship_to_company": "marianne s\u00f8rensen mad",
            "ship_to_address": "Byggebjerg 2",
            "ship_to_address_2": "Byggebjerg 2",
            "ship_to_postal_code": "6534",
            "ship_to_city": "agerskov",
            "ship_to_country": "DK",
            "shop_id": ""
        },
        "Timestamp": 1633357596,
        "check": {
            "check1": "1: BS59076 - 2021-10-04 16:27",
            "check2": "",
            "check3": "",
            "shopname": "Ukendt",
            "lang": ""
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon,  4 Oct 2021 16:17:39 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "Bramming gymnastik og idr\u00e6tsefterskole",
            "cvr": "18095513",
            "bill_to_address": "Gabelsvej 12A",
            "bill_to_postal_code": "6740",
            "bill_to_city": "Bramming",
            "requisition_no": "Nikolaj Primdal ",
            "bill_to_country": "DK",
            "quantity": "50",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633357059,
        "check": {
            "check1": "",
            "check2": "1: BS59868 - 2021-10-11 10:38",
            "check3": "",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon,  4 Oct 2021 16:12:35 +0200 (CEST)",
        "input": {
            "shop_id": "290",
            "companyname": "SuperBrugsen Galten",
            "cvr": "43726811",
            "phone": "51298711",
            "contact_phone": "51298711",
            "contact_email": "brian.kamper@superbrugsen.dk",
            "bill_to_address": "S\u00f8ndergade 47",
            "bill_to_postal_code": "8464",
            "bill_to_city": "Galten",
            "requisition_no": "Brian Kamper",
            "bill_to_country": "DK",
            "quantity": "40",
            "expire_date": "07-11-2021",
            "is_email": "0",
            "use_shipping_address": "1",
            "ship_to_company": "SuperBrugsen Galten",
            "ship_to_address": "S\u00f8ndergade 47",
            "ship_to_address_2": "S\u00f8ndergade 47",
            "ship_to_postal_code": "8464",
            "ship_to_city": "Galten",
            "ship_to_country": "DK"
        },
        "Timestamp": 1633356755,
        "check": {
            "check1": "1: BS59075 - 2021-10-04 16:12",
            "check2": "",
            "check3": "1: BS59074 - 2021-10-04 16:10",
            "shopname": "Dr\u00f8mmegavekortet 200",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon,  4 Oct 2021 16:10:10 +0200 (CEST)",
        "input": {
            "shop_id": "55",
            "companyname": "SuperBrugsen Galten",
            "cvr": "43726811",
            "bill_to_address": "S\u00f8ndergade 47",
            "bill_to_postal_code": "8464",
            "bill_to_city": "Galten",
            "requisition_no": "Brian Kamper",
            "bill_to_country": "DK",
            "quantity": "38",
            "expire_date": "07-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633356610,
        "check": {
            "check1": "1: BS59074 - 2021-10-04 16:10",
            "check2": "",
            "check3": "1: BS59075 - 2021-10-04 16:12",
            "shopname": "24 Gaver DK 560",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field cvr: value is required",
            "field": "cvr",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon,  4 Oct 2021 15:40:55 +0200 (CEST)",
        "input": {
            "shop_id": "58",
            "companyname": "Kompan Norge AS",
            "bill_to_address": "Gr\u00f8nland 53, Papirbredden 3",
            "bill_to_postal_code": "3045",
            "bill_to_country": "NO",
            "expire_date": "07-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "NO",
            "giftwrap": "1",
            "cvr": "",
            "contact_email": ""
        },
        "Timestamp": 1633354855,
        "check": {
            "check1": "*",
            "check2": "*",
            "check3": "*",
            "shopname": "Julegavekortet NO 600",
            "lang": 4
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon,  4 Oct 2021 14:57:45 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "Dansk El Forbund K\u00f8benhavn",
            "cvr": "21228419",
            "bill_to_address": "Tik\u00f8bgade 9",
            "bill_to_postal_code": "2200 ",
            "bill_to_city": "K\u00f8benhavn N.",
            "requisition_no": "Lars B\u00e6k",
            "bill_to_country": "DK",
            "quantity": "26",
            "expire_date": "07-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633352265,
        "check": {
            "check1": "",
            "check2": "1: BS59061 - 2021-10-04 15:00",
            "check3": "",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field companyname: value is required",
            "field": "companyname",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon,  4 Oct 2021 14:56:02 +0200 (CEST)",
        "input": {
            "shop_id": "52",
            "bill_to_country": "DK",
            "ship_to_country": "DK",
            "cvr": "",
            "contact_email": ""
        },
        "Timestamp": 1633352162,
        "check": {
            "check1": "*",
            "check2": "*",
            "check3": "*",
            "shopname": "Julegavekortet DK",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field phone: string must be longer than 12 characters",
            "field": "phone",
            "type": "toolong",
            "min": "12",
            "max": "",
            "length": ""
        },
        "Date": " Mon,  4 Oct 2021 14:55:51 +0200 (CEST)",
        "input": {
            "shop_id": "52",
            "companyname": "Gynaekologisk Klinik Slagelse",
            "cvr": "41965622",
            "phone": "58532310 eller 60604058",
            "contact_name": "Inge Svane",
            "contact_phone": "58532310 eller 60604058",
            "contact_email": "ihsvane@gmail.com",
            "bill_to_address": "Bjergbygade 3",
            "bill_to_postal_code": "4200",
            "bill_to_city": "Slagelse",
            "requisition_no": "Inge Svane",
            "bill_to_country": "DK",
            "quantity": "7",
            "expire_date": "31-10-2021",
            "is_email": "0",
            "use_shipping_address": "1",
            "ship_to_company": "Gynaekologisk Klinik Slagelse",
            "ship_to_address": "Bjergbygade 3",
            "ship_to_address_2": "Bjergbygade 3",
            "ship_to_postal_code": "4200",
            "ship_to_city": "Slagelse",
            "ship_to_country": "DK"
        },
        "Timestamp": 1633352151,
        "check": {
            "check1": "1: BS59059 - 2021-10-04 14:59",
            "check2": "",
            "check3": "",
            "shopname": "Julegavekortet DK",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon,  4 Oct 2021 14:55:50 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "Dansk El Forbund Kobenhavn",
            "cvr": "21228419",
            "bill_to_address": "Tik\u00f8bgade 9",
            "bill_to_postal_code": "2200 ",
            "bill_to_city": "K\u00f8benhavn N.",
            "requisition_no": "Lars B\u00e6k",
            "bill_to_country": "DK",
            "quantity": "26",
            "expire_date": "07-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633352150,
        "check": {
            "check1": "",
            "check2": "1: BS59061 - 2021-10-04 15:00",
            "check3": "",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "30",
            "error": "Could not find valid shop_id"
        },
        "Date": " Mon,  4 Oct 2021 14:55:48 +0200 (CEST)",
        "input": {
            "bill_to_country": "DK",
            "ship_to_country": "DK",
            "shop_id": "",
            "cvr": "",
            "contact_email": ""
        },
        "Timestamp": 1633352148,
        "check": {
            "check1": "*",
            "check2": "*",
            "check3": "*",
            "shopname": "Ukendt",
            "lang": ""
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon,  4 Oct 2021 14:55:26 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "Dansk El-Forbund Kobenhavn",
            "cvr": "21228419",
            "bill_to_address": "Tik\u00f8bgade 9",
            "bill_to_postal_code": "2200 ",
            "bill_to_city": "K\u00f8benhavn N.",
            "requisition_no": "Lars B\u00e6k",
            "bill_to_country": "DK",
            "quantity": "26",
            "expire_date": "07-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633352126,
        "check": {
            "check1": "",
            "check2": "1: BS59061 - 2021-10-04 15:00",
            "check3": "",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field phone: string must be longer than 12 characters",
            "field": "phone",
            "type": "toolong",
            "min": "12",
            "max": "",
            "length": ""
        },
        "Date": " Mon,  4 Oct 2021 14:55:20 +0200 (CEST)",
        "input": {
            "shop_id": "52",
            "companyname": "Gynaekologisk Klinik Slagelse",
            "cvr": "41965622",
            "phone": "58532310 eller 60604058",
            "contact_name": "Inge Svane",
            "contact_phone": "58532310 eller 60604058",
            "contact_email": "ihsvane@gmail.com",
            "bill_to_address": "Bjergbygade 3",
            "bill_to_postal_code": "4200",
            "bill_to_city": "Slagelse",
            "requisition_no": "Inge Svane",
            "bill_to_country": "DK",
            "quantity": "7",
            "expire_date": "31-10-2021",
            "is_email": "0",
            "use_shipping_address": "1",
            "ship_to_company": "Gynaekologisk Klinik Slagelse",
            "ship_to_address": "Bjergbygade 3",
            "ship_to_address_2": "Bjergbygade 3",
            "ship_to_postal_code": "4200",
            "ship_to_city": "Slagelse",
            "ship_to_country": "DK"
        },
        "Timestamp": 1633352120,
        "check": {
            "check1": "1: BS59059 - 2021-10-04 14:59",
            "check2": "",
            "check3": "",
            "shopname": "Julegavekortet DK",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "30",
            "error": "Could not find valid shop_id"
        },
        "Date": " Mon,  4 Oct 2021 14:55:16 +0200 (CEST)",
        "input": {
            "bill_to_country": "DK",
            "ship_to_country": "DK",
            "shop_id": "",
            "cvr": "",
            "contact_email": ""
        },
        "Timestamp": 1633352116,
        "check": {
            "check1": "*",
            "check2": "*",
            "check3": "*",
            "shopname": "Ukendt",
            "lang": ""
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon,  4 Oct 2021 14:55:05 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "Dansk El-Forbund K\u00f8benhavn",
            "cvr": "21228419",
            "bill_to_address": "Tik\u00f8bgade 9",
            "bill_to_postal_code": "2200 ",
            "bill_to_city": "K\u00f8benhavn N.",
            "requisition_no": "Lars B\u00e6k",
            "bill_to_country": "DK",
            "quantity": "26",
            "expire_date": "07-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633352105,
        "check": {
            "check1": "",
            "check2": "1: BS59061 - 2021-10-04 15:00",
            "check3": "",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field phone: string must be longer than 12 characters",
            "field": "phone",
            "type": "toolong",
            "min": "12",
            "max": "",
            "length": ""
        },
        "Date": " Mon,  4 Oct 2021 14:54:42 +0200 (CEST)",
        "input": {
            "shop_id": "52",
            "companyname": "Gynaekologisk Klinik Slagelse",
            "cvr": "41965622",
            "phone": "58532310 eller 60604058",
            "contact_name": "Inge Svane",
            "contact_phone": "58532310 eller 60604058",
            "contact_email": "ihsvane@gmail.com",
            "bill_to_address": "Bjergbygade 3",
            "bill_to_postal_code": "4200",
            "bill_to_city": "Slagelse",
            "requisition_no": "Inge Svane",
            "bill_to_country": "DK",
            "quantity": "7",
            "expire_date": "31-10-2021",
            "is_email": "0",
            "use_shipping_address": "1",
            "ship_to_company": "Gynaekologisk Klinik Slagelse",
            "ship_to_address": "Bjergbygade 3",
            "ship_to_address_2": "Bjergbygade 3",
            "ship_to_postal_code": "4200",
            "ship_to_city": "Slagelse",
            "ship_to_country": "DK"
        },
        "Timestamp": 1633352082,
        "check": {
            "check1": "1: BS59059 - 2021-10-04 14:59",
            "check2": "",
            "check3": "",
            "shopname": "Julegavekortet DK",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon,  4 Oct 2021 14:44:42 +0200 (CEST)",
        "input": {
            "shop_id": "57",
            "companyname": "Rajapack AS",
            "cvr": "934 713 710",
            "bill_to_address": "Delitoppen 3, 3 etg",
            "bill_to_postal_code": "1540",
            "bill_to_city": "Vestby",
            "requisition_no": "Monica Vigsnes Lie",
            "bill_to_country": "NO",
            "bill_to_email": "faktura@rajapack.no",
            "quantity": "19",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "NO",
            "giftwrap": "1",
            "contact_email": ""
        },
        "Timestamp": 1633351482,
        "check": {
            "check1": "2: BS59055 - 2021-10-04 14:45",
            "check2": "",
            "check3": "",
            "shopname": "Julegavekortet NO 400",
            "lang": 4
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon,  4 Oct 2021 14:23:23 +0200 (CEST)",
        "input": {
            "shop_id": "2550",
            "companyname": "Holmek Palletering AS",
            "cvr": " 989 568 302",
            "bill_to_address": "S\u00f8rlia 1",
            "bill_to_postal_code": "6521",
            "bill_to_city": "Frei",
            "requisition_no": "Tone Mette Romstad",
            "bill_to_country": "NO",
            "bill_to_email": "tmr@holmek.no",
            "quantity": "21",
            "expire_date": "21-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "NO",
            "contact_email": ""
        },
        "Timestamp": 1633350203,
        "check": {
            "check1": "1: BS59048 - 2021-10-04 14:23",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekort 1200 (NO)",
            "lang": 4
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon,  4 Oct 2021 14:06:59 +0200 (CEST)",
        "input": {
            "shop_id": "575",
            "companyname": "Erling Transport & Spedition AS",
            "cvr": "10406048",
            "bill_to_address": "\u00c5toftevej 2",
            "bill_to_postal_code": "7200",
            "bill_to_city": "Grindsted",
            "requisition_no": "Lars Benfeldt",
            "bill_to_country": "DK",
            "quantity": "35",
            "expire_date": "31-10-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "giftwrap": "1",
            "contact_email": ""
        },
        "Timestamp": 1633349219,
        "check": {
            "check1": "1: BS59045 - 2021-10-04 14:08",
            "check2": "",
            "check3": "",
            "shopname": "Designjulegaven",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field cvr: value is required",
            "field": "cvr",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon,  4 Oct 2021 13:22:35 +0200 (CEST)",
        "input": {
            "shop_id": "2549",
            "companyname": "Gavefabrikken",
            "phone": "+4520960563",
            "contact_name": "testsersen",
            "contact_phone": "+4520960563",
            "contact_email": "birgitte@illerupmedia.dk",
            "bill_to_address": "Carls Jacobsens vej 20",
            "bill_to_postal_code": "2500",
            "bill_to_city": "Valby",
            "requisition_no": "Test",
            "bill_to_country": "NO",
            "bill_to_email": "birgitte@illerupmedia.dk",
            "quantity": "6",
            "expire_date": "07-11-2021",
            "is_email": "0",
            "use_shipping_address": "1",
            "ship_to_company": "Test",
            "ship_to_postal_code": "2680",
            "ship_to_city": "Solr\u00f8d Strand",
            "ship_to_country": "NO",
            "giftwrap": "1",
            "cvr": ""
        },
        "Timestamp": 1633346555,
        "check": {
            "check1": "",
            "check2": "",
            "check3": "5: BS57603 - 2021-09-22 15:56",
            "shopname": "BRA Gavekortet (NO)",
            "lang": 4
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon,  4 Oct 2021 13:18:36 +0200 (CEST)",
        "input": {
            "shop_id": "2558",
            "companyname": "Sinfra ",
            "cvr": "716419-3323",
            "bill_to_address": "Sinfra Box 1026",
            "bill_to_postal_code": "101 38",
            "bill_to_city": "Stockholm",
            "requisition_no": "Doganson",
            "bill_to_country": "SE",
            "quantity": "15",
            "expire_date": "31-12-2022",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "SE",
            "giftwrap": "1",
            "contact_email": ""
        },
        "Timestamp": 1633346316,
        "check": {
            "check1": "2: BS59425 - 2021-10-06 15:20",
            "check2": "",
            "check3": "",
            "shopname": "24 julklappar 1200 2021",
            "lang": 5
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon,  4 Oct 2021 12:58:56 +0200 (CEST)",
        "input": {
            "shop_id": "575",
            "companyname": "Jersie Totalbyg ApS",
            "cvr": "71188914",
            "bill_to_address": "Rams\u00f8vejen 5",
            "bill_to_postal_code": "4621",
            "bill_to_city": "Gadstrup",
            "requisition_no": "Tina Milland",
            "bill_to_country": "DK",
            "quantity": "5",
            "expire_date": "31-10-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633345136,
        "check": {
            "check1": "1: BS59018 - 2021-10-04 13:00",
            "check2": "",
            "check3": "1: BS59019 - 2021-10-04 13:01",
            "shopname": "Designjulegaven",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon,  4 Oct 2021 11:54:47 +0200 (CEST)",
        "input": {
            "shop_id": "290",
            "companyname": "B\u00d8RNEBY \u00d8STER",
            "cvr": "29189617",
            "ean": "5798005571421",
            "bill_to_address": "s\u00f8nderparken 42a",
            "bill_to_postal_code": "7430",
            "bill_to_city": "Ikast",
            "bill_to_country": "DK",
            "quantity": "27",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633341287,
        "check": {
            "check1": "1: BS58997 - 2021-10-04 11:56",
            "check2": "",
            "check3": "5: BS56752 - 2021-09-09 15:21",
            "shopname": "Dr\u00f8mmegavekortet 200",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon,  4 Oct 2021 11:49:18 +0200 (CEST)",
        "input": {
            "shop_id": "52",
            "companyname": "Slagtekalveraadgivning",
            "cvr": "29210330",
            "bill_to_address": "HERNINGVEJ 23",
            "bill_to_postal_code": "7300",
            "bill_to_city": "Jelling",
            "bill_to_country": "DK",
            "quantity": "11",
            "expire_date": "31-10-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633340958,
        "check": {
            "check1": "1: BS58993 - 2021-10-04 11:49",
            "check2": "",
            "check3": "",
            "shopname": "Julegavekortet DK",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon,  4 Oct 2021 11:26:08 +0200 (CEST)",
        "input": {
            "shop_id": "55",
            "companyname": "Meny",
            "cvr": "31580471",
            "phone": "98442180",
            "contact_phone": "98442180",
            "contact_email": "Emeibom@live.dk",
            "bill_to_address": "Doggerbanke 20",
            "bill_to_postal_code": "9990",
            "bill_to_city": "Skagen",
            "bill_to_country": "DK",
            "quantity": "20",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK"
        },
        "Timestamp": 1633339568,
        "check": {
            "check1": "1: BS58984 - 2021-10-04 11:27",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 560",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon,  4 Oct 2021 11:14:55 +0200 (CEST)",
        "input": {
            "shop_id": "55",
            "companyname": "Cypax",
            "cvr": "16029378",
            "phone": "22341770",
            "contact_phone": "22341770",
            "contact_email": "eviltwin100@hotmail.com",
            "bill_to_address": "Industrivej 24",
            "bill_to_postal_code": "7470",
            "bill_to_city": "Karup J",
            "bill_to_country": "DK",
            "quantity": "5",
            "expire_date": "07-11-2021",
            "is_email": "0",
            "use_shipping_address": "1",
            "ship_to_company": "Cypax",
            "ship_to_address": "Industrivej",
            "ship_to_address_2": "Industrivej",
            "ship_to_postal_code": "7470",
            "ship_to_city": "Karup J",
            "ship_to_country": "DK"
        },
        "Timestamp": 1633338895,
        "check": {
            "check1": "",
            "check2": "",
            "check3": "2: BS58982 - 2021-10-04 11:15",
            "shopname": "24 Gaver DK 560",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon,  4 Oct 2021 10:46:56 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "Viliam Petersen Tandhjulsfabriken A\/S",
            "cvr": "32890210",
            "phone": "40195311",
            "contact_phone": "40195311",
            "contact_email": "hp@tandhjulsfabriken.dk",
            "bill_to_address": "Islandsvej 30",
            "bill_to_postal_code": "8700",
            "bill_to_city": "Horsens",
            "bill_to_country": "DK",
            "quantity": "24",
            "expire_date": "07-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK"
        },
        "Timestamp": 1633337216,
        "check": {
            "check1": "",
            "check2": "",
            "check3": "1: BS58978 - 2021-10-04 10:56",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon,  4 Oct 2021 10:41:34 +0200 (CEST)",
        "input": {
            "shop_id": "52",
            "companyname": "SATnordic ApS",
            "cvr": "28990677",
            "bill_to_address": "Fuglsigvej 1",
            "bill_to_postal_code": "6818",
            "bill_to_city": "\u00c5rre",
            "bill_to_country": "DK",
            "quantity": "8",
            "expire_date": "14-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633336894,
        "check": {
            "check1": "1: BS58977 - 2021-10-04 10:41",
            "check2": "",
            "check3": "",
            "shopname": "Julegavekortet DK",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon,  4 Oct 2021 10:28:28 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "Hvidsten Dyrehospital",
            "cvr": "19089290",
            "phone": "86477488",
            "contact_phone": "86477488",
            "contact_email": "klinikken@hvidstendyrehospital.dk",
            "bill_to_address": "Mariagervej 455 A",
            "bill_to_postal_code": "8981",
            "bill_to_city": "Spentrup",
            "bill_to_country": "DK",
            "quantity": "9",
            "expire_date": "07-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK"
        },
        "Timestamp": 1633336108,
        "check": {
            "check1": "1: BS58975 - 2021-10-04 10:29",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field ean: value must be at least 1000000000000",
            "field": "ean",
            "type": "lowvalue",
            "min": "1000000000000",
            "max": "",
            "length": ""
        },
        "Date": " Mon,  4 Oct 2021 10:10:08 +0200 (CEST)",
        "input": {
            "shop_id": "55",
            "companyname": "Lystruphave Efterskole",
            "cvr": "25937295",
            "ean": "5790 00249572",
            "phone": "24409634",
            "contact_name": "Gorm Olesen",
            "contact_phone": "24409634",
            "contact_email": "go@lystruphave.dk",
            "bill_to_address": "Lystruphavevej 10",
            "bill_to_postal_code": "8654",
            "bill_to_city": "Bryrup",
            "bill_to_country": "DK",
            "quantity": "30",
            "expire_date": "21-11-2021",
            "is_email": "1",
            "use_shipping_address": "1",
            "ship_to_company": "Lystruphave Efterskole",
            "ship_to_address": "Lystruphavevej 10",
            "ship_to_address_2": "Lystruphavevej 10",
            "ship_to_postal_code": "8654",
            "ship_to_city": "Bryrup",
            "ship_to_country": "DK"
        },
        "Timestamp": 1633335008,
        "check": {
            "check1": "1: BS58970 - 2021-10-04 10:11",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 560",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon,  4 Oct 2021 09:53:57 +0200 (CEST)",
        "input": {
            "shop_id": "2395",
            "companyname": "Aarhus Administration og Raadgivning ApS",
            "cvr": "41303603",
            "bill_to_address": "J\u00e6gerg\u00e5rdsgade 156 G",
            "bill_to_postal_code": "8000",
            "bill_to_city": "Aarhus C",
            "bill_to_country": "DK",
            "quantity": "6",
            "expire_date": "07-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633334037,
        "check": {
            "check1": "1: BS58966 - 2021-10-04 09:54",
            "check2": "1: BS60499 - 2021-10-14 09:44",
            "check3": "",
            "shopname": "Guldgavekortet DK 960",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon,  4 Oct 2021 09:11:23 +0200 (CEST)",
        "input": {
            "shop_id": "52",
            "companyname": "Danske Havne",
            "cvr": "14582428",
            "phone": "+4520208615",
            "contact_phone": "+4520208615",
            "contact_email": "tkp@danskehavne.dk",
            "bill_to_address": "Bredgade 23, 2. TV",
            "bill_to_postal_code": "1260",
            "bill_to_city": "K\u00f8benhavn K",
            "bill_to_country": "DK",
            "quantity": "6",
            "expire_date": "14-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK"
        },
        "Timestamp": 1633331483,
        "check": {
            "check1": "1: BS58958 - 2021-10-04 09:11",
            "check2": "",
            "check3": "",
            "shopname": "Julegavekortet DK",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon,  4 Oct 2021 08:58:12 +0200 (CEST)",
        "input": {
            "shop_id": "55",
            "companyname": "Ester\'s Reng\u00f8ring ApS",
            "cvr": "41852682",
            "phone": "20139375",
            "contact_phone": "20139375",
            "contact_email": "ester@ester.dk",
            "bill_to_address": "Tv\u00e6rvej 2A",
            "bill_to_postal_code": "8832",
            "bill_to_city": "Skals",
            "bill_to_country": "DK",
            "quantity": "20",
            "expire_date": "07-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK"
        },
        "Timestamp": 1633330692,
        "check": {
            "check1": "1: BS58953 - 2021-10-04 08:58",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 560",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon,  4 Oct 2021 08:57:27 +0200 (CEST)",
        "input": {
            "shop_id": "2395",
            "companyname": "Stohn Advokatanpartsselskab",
            "cvr": "41013990",
            "bill_to_address": "Esplanaden 26 st.",
            "bill_to_postal_code": "1263",
            "bill_to_city": "K\u00f8benhavn K",
            "bill_to_country": "DK",
            "quantity": "11",
            "expire_date": "21-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "contact_email": ""
        },
        "Timestamp": 1633330647,
        "check": {
            "check1": "1: BS58951 - 2021-10-04 08:57",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekortet DK 960",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field companyname: value is required",
            "field": "companyname",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Sun,  3 Oct 2021 22:58:07 +0200 (CEST)",
        "input": {
            "shop_id": "53",
            "bill_to_country": "DK",
            "quantity": "1",
            "expire_date": "01-04-2022",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "cvr": "",
            "contact_email": ""
        },
        "Timestamp": 1633294687,
        "check": {
            "check1": "*",
            "check2": "*",
            "check3": "*",
            "shopname": "Guldgavekortet DK 800",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field cvr: value is required",
            "field": "cvr",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Sun,  3 Oct 2021 20:48:28 +0200 (CEST)",
        "input": {
            "shop_id": "52",
            "companyname": "Fjordkredsen",
            "phone": "60224344",
            "contact_name": "Kirsten Busk",
            "contact_phone": "60224344",
            "contact_email": "kibu@dlf.org",
            "bill_to_address": "N\u00f8rregade 22A",
            "bill_to_postal_code": "6950",
            "bill_to_city": "Ringk\u00f8bing",
            "bill_to_country": "DK",
            "quantity": "5",
            "expire_date": "31-10-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "cvr": ""
        },
        "Timestamp": 1633286908,
        "check": {
            "check1": "1: BS59635 - 2021-10-07 15:39",
            "check2": "",
            "check3": "",
            "shopname": "Julegavekortet DK",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field ean: string must be at least 13 characters",
            "field": "ean",
            "type": "tooshort",
            "min": "13",
            "max": "",
            "length": ""
        },
        "Date": " Sun,  3 Oct 2021 10:03:42 +0200 (CEST)",
        "input": {
            "shop_id": "52",
            "companyname": "Maaloev Fysioterapi",
            "cvr": "26203155",
            "ean": "1008254598",
            "phone": "22403870",
            "contact_name": "Lone Bregnh\u00f8j",
            "contact_phone": "22403870",
            "contact_email": "l.bregnhoj@gmail.com",
            "bill_to_address": "M\u00e5l\u00f8v Hovedgade 61",
            "bill_to_postal_code": "2760 ",
            "bill_to_city": "M\u00e5l\u00f8v",
            "bill_to_country": "DK",
            "quantity": "8",
            "expire_date": "31-10-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_city": "Birker\u00f8d",
            "ship_to_country": "DK"
        },
        "Timestamp": 1633248222,
        "check": {
            "check1": "1: BS58923 - 2021-10-03 10:04",
            "check2": "",
            "check3": "",
            "shopname": "Julegavekortet DK",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field quantity: value is required",
            "field": "quantity",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Sat,  2 Oct 2021 23:46:38 +0200 (CEST)",
        "input": {
            "shop_id": "2395",
            "companyname": "A Safe Scandinavia",
            "cvr": "35253229",
            "phone": "26808083",
            "contact_name": "Finn S\u00f8rensen",
            "contact_phone": "26808083",
            "contact_email": "finn.sorensen@asafe.dk",
            "bill_to_address": "Rugv\u00e6nget 46",
            "bill_to_postal_code": "2630",
            "bill_to_city": "Taastrup",
            "bill_to_country": "DK",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "1",
            "ship_to_company": "A Safe Scandinavia",
            "ship_to_address": "Klerkev\u00e6nget 20",
            "ship_to_address_2": "Klerkev\u00e6nget 20",
            "ship_to_postal_code": "8700",
            "ship_to_city": "Horsens",
            "ship_to_country": "DK"
        },
        "Timestamp": 1633211198,
        "check": {
            "check1": "",
            "check2": "",
            "check3": "1: BS58920 - 2021-10-02 23:46",
            "shopname": "Guldgavekortet DK 960",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "30",
            "error": "Could not find valid shop_id"
        },
        "Date": " Fri,  1 Oct 2021 15:31:02 +0200 (CEST)",
        "input": {
            "companyname": "Tina Hald Johansen ",
            "cvr": "36107308",
            "phone": "20208103",
            "contact_name": "Tina Hald Johansen",
            "contact_phone": "20208103",
            "contact_email": "tinahald@gmail.com",
            "bill_to_address": "Torvebyen 2, 1",
            "bill_to_postal_code": "4600",
            "bill_to_city": "K\u00f8ge",
            "bill_to_country": "DK",
            "quantity": "5",
            "expire_date": "14-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "shop_id": ""
        },
        "Timestamp": 1633095062,
        "check": {
            "check1": "1: BS58902 - 2021-10-01 15:31",
            "check2": "",
            "check3": "",
            "shopname": "Ukendt",
            "lang": ""
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field ean: value must be at least 1000000000000",
            "field": "ean",
            "type": "lowvalue",
            "min": "1000000000000",
            "max": "",
            "length": ""
        },
        "Date": " Fri,  1 Oct 2021 14:01:28 +0200 (CEST)",
        "input": {
            "shop_id": "53",
            "companyname": "aof  center odense",
            "cvr": "31571391",
            "ean": "5790 0023 298",
            "phone": "40119342",
            "contact_name": "Gitte Overgaard",
            "contact_phone": "40119342",
            "contact_email": "gitte@aofcenterodense.dk",
            "bill_to_address": "rug\u00e5rdsvej 15B",
            "bill_to_postal_code": "5000",
            "bill_to_city": "Odense C",
            "bill_to_country": "DK",
            "quantity": "21",
            "expire_date": "07-11-2021",
            "is_email": "0",
            "use_shipping_address": "1",
            "ship_to_company": "aof  center odense",
            "ship_to_address": "Sprotoften 1",
            "ship_to_address_2": "Sprotoften 1",
            "ship_to_postal_code": "5800",
            "ship_to_city": "Nyborg",
            "ship_to_country": "DK"
        },
        "Timestamp": 1633089688,
        "check": {
            "check1": "1: BS58876 - 2021-10-01 14:02",
            "check2": "",
            "check3": "1: BS59632 - 2021-10-07 15:34",
            "shopname": "Guldgavekortet DK 800",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field cvr: value is required",
            "field": "cvr",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu, 30 Sep 2021 21:48:05 +0200 (CEST)",
        "input": {
            "shop_id": "2550",
            "companyname": "Eiendomsmegler 1 Modum AS",
            "phone": "99241824",
            "contact_name": "Linn-Cecilie Bergstad ",
            "contact_phone": "99241824",
            "contact_email": "lcb@dahlas.no",
            "bill_to_address": "Erik B\u00f8rresens alle 7",
            "bill_to_postal_code": "3015",
            "bill_to_city": "DRAMMEN",
            "bill_to_country": "NO",
            "bill_to_email": "lcb@dahlas.no",
            "quantity": "31",
            "expire_date": "21-11-2021",
            "is_email": "1",
            "use_shipping_address": "1",
            "ship_to_company": "Eiendomsmegler 1 Modum AS",
            "ship_to_address": "Erik B\u00f8rresens alle 7",
            "ship_to_address_2": "Erik B\u00f8rresens alle 7",
            "ship_to_postal_code": "3015",
            "ship_to_city": "DRAMMEN",
            "ship_to_country": "NO",
            "cvr": ""
        },
        "Timestamp": 1633031285,
        "check": {
            "check1": "1: BS58715 - 2021-09-30 21:48",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekort 1200 (NO)",
            "lang": 4
        }
    },
    {
        "output": {
            "status": "30",
            "error": "Could not find valid shop_id"
        },
        "Date": " Thu, 30 Sep 2021 15:59:34 +0200 (CEST)",
        "input": {
            "companyname": "Alex Poulsen Arkitekter ",
            "cvr": "44 78 05 18",
            "phone": "33 14 63 14",
            "contact_name": "Clara Fjord Jacobsen",
            "contact_phone": "33 14 63 14",
            "contact_email": "cfj@alexpoulsen.dk",
            "bill_to_address": "Bragesgade 10B",
            "bill_to_postal_code": "2200",
            "bill_to_city": "K\u00f8benahvn N",
            "bill_to_country": "DK",
            "quantity": "33",
            "expire_date": "14-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "giftwrap": "1",
            "shop_id": ""
        },
        "Timestamp": 1633010374,
        "check": {
            "check1": "1: BS58687 - 2021-09-30 15:59",
            "check2": "",
            "check3": "",
            "shopname": "Ukendt",
            "lang": ""
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field cvr: value is required",
            "field": "cvr",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu, 30 Sep 2021 13:54:12 +0200 (CEST)",
        "input": {
            "shop_id": "54",
            "companyname": "westfloor",
            "bill_to_country": "DK",
            "quantity": "5",
            "expire_date": "07-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "cvr": "",
            "contact_email": ""
        },
        "Timestamp": 1633002852,
        "check": {
            "check1": "*",
            "check2": "*",
            "check3": "*",
            "shopname": "24 Gaver DK 400",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "30",
            "error": "Could not find valid shop_id"
        },
        "Date": " Thu, 30 Sep 2021 12:35:18 +0200 (CEST)",
        "input": {
            "companyname": "Sanyei Scandinavia",
            "cvr": "24232328",
            "phone": "22178300",
            "contact_name": "Bente R\u00f8nnow",
            "contact_phone": "22178300",
            "contact_email": "Bente@sanyei.dk",
            "bill_to_address": "Hasserisvej 136",
            "bill_to_postal_code": "9000",
            "bill_to_city": "Aalborg",
            "bill_to_country": "DK",
            "quantity": "12",
            "expire_date": "31-10-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "giftwrap": "1",
            "shop_id": ""
        },
        "Timestamp": 1632998118,
        "check": {
            "check1": "1: BS58559 - 2021-09-30 12:35",
            "check2": "1: BS59875 - 2021-10-11 10:55",
            "check3": "",
            "shopname": "Ukendt",
            "lang": ""
        }
    },
    {
        "output": {
            "status": "30",
            "error": "Could not find valid shop_id"
        },
        "Date": " Thu, 30 Sep 2021 09:49:05 +0200 (CEST)",
        "input": {
            "companyname": "Tandl\u00e6ge Brian Thomsen",
            "cvr": "34081107",
            "phone": "40979695",
            "contact_name": "Brian Thomsen",
            "contact_phone": "40979695",
            "contact_email": "bribo@post.com",
            "bill_to_address": "Vivaldisvej 17",
            "bill_to_postal_code": "9200",
            "bill_to_city": "Aalborg SV",
            "bill_to_country": "DK",
            "quantity": "7",
            "expire_date": "31-10-2021",
            "is_email": "1",
            "use_shipping_address": "1",
            "ship_to_company": "Tandl\u00e6ge Brian Thomsen",
            "ship_to_address": "nibevej 317 D",
            "ship_to_address_2": "nibevej 317 D",
            "ship_to_postal_code": "9200",
            "ship_to_city": "Aalborg SV",
            "ship_to_country": "DK",
            "shop_id": ""
        },
        "Timestamp": 1632988145,
        "check": {
            "check1": "1: BS58507 - 2021-09-30 09:49",
            "check2": "",
            "check3": "1: BS58504 - 2021-09-30 09:45",
            "shopname": "Ukendt",
            "lang": ""
        }
    },
    {
        "output": {
            "status": "30",
            "error": "Could not find valid shop_id"
        },
        "Date": " Thu, 30 Sep 2021 09:20:47 +0200 (CEST)",
        "input": {
            "companyname": "Expofreight Denmark ApS",
            "cvr": "40876308",
            "phone": "30509224",
            "contact_name": "Jacob Thulstrup Bruhn",
            "contact_phone": "30509224",
            "contact_email": "jacobb@efl.global",
            "bill_to_address": "Kildeparken 32, 1",
            "bill_to_postal_code": "8722",
            "bill_to_city": "Hedensted",
            "bill_to_country": "DK",
            "quantity": "7",
            "expire_date": "31-10-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "shop_id": ""
        },
        "Timestamp": 1632986447,
        "check": {
            "check1": "1: BS58500 - 2021-09-30 09:25",
            "check2": "",
            "check3": "",
            "shopname": "Ukendt",
            "lang": ""
        }
    },
    {
        "output": {
            "status": "30",
            "error": "Could not find valid shop_id"
        },
        "Date": " Thu, 30 Sep 2021 08:42:09 +0200 (CEST)",
        "input": {
            "companyname": "BeneFiT Fyn Aps",
            "cvr": "38930036",
            "phone": "40263536",
            "contact_name": "Peter Kromann",
            "contact_phone": "40263536",
            "contact_email": "pk@benefit.dk",
            "bill_to_address": "Stenhuggervej 34",
            "bill_to_postal_code": "5230 ",
            "bill_to_city": "Odense M",
            "bill_to_country": "DK",
            "quantity": "26",
            "expire_date": "31-10-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "giftwrap": "1",
            "shop_id": ""
        },
        "Timestamp": 1632984129,
        "check": {
            "check1": "1: BS58485 - 2021-09-30 08:42",
            "check2": "",
            "check3": "",
            "shopname": "Ukendt",
            "lang": ""
        }
    },
    {
        "output": {
            "status": "30",
            "error": "Could not find valid shop_id"
        },
        "Date": " Thu, 30 Sep 2021 08:01:15 +0200 (CEST)",
        "input": {
            "companyname": "skolelederforeningen",
            "cvr": "25062825",
            "phone": "+4530280109",
            "contact_name": "Lone Skjold Henriksen",
            "contact_phone": "+4530280109",
            "contact_email": "lsh@skolelederne.org",
            "bill_to_address": "Snaregade 10A",
            "bill_to_postal_code": "1205",
            "bill_to_city": "k\u00f8benhavn K",
            "bill_to_country": "DK",
            "quantity": "22",
            "expire_date": "31-12-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "shop_id": ""
        },
        "Timestamp": 1632981675,
        "check": {
            "check1": "",
            "check2": "",
            "check3": "2: BS58478 - 2021-09-30 08:07",
            "shopname": "Ukendt",
            "lang": ""
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field phone: string must be longer than 12 characters",
            "field": "phone",
            "type": "toolong",
            "min": "12",
            "max": "",
            "length": ""
        },
        "Date": " Wed, 29 Sep 2021 17:28:27 +0200 (CEST)",
        "input": {
            "shop_id": "2395",
            "companyname": "City Care",
            "cvr": "34607257",
            "phone": "53731612\/22554924",
            "contact_name": "Nicoline Telling",
            "contact_phone": "53731612\/22554924",
            "contact_email": "info@citycare.dk",
            "bill_to_address": "Strandvejen 86, st.",
            "bill_to_postal_code": "2900",
            "bill_to_city": "Hellerup",
            "bill_to_country": "DK",
            "quantity": "5",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK"
        },
        "Timestamp": 1632929307,
        "check": {
            "check1": "1: BS58461 - 2021-09-29 17:28",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekortet DK 960",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_email: not a valid e-mail",
            "field": "contact_email",
            "type": "invalidemail",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed, 29 Sep 2021 16:17:58 +0200 (CEST)",
        "input": {
            "shop_id": "272",
            "companyname": "\u00e6\u00f8\u00e6\u00f8\u00e6\u00f8",
            "cvr": "\u00e6\u00f8\u00e6\u00f8",
            "phone": "\u00e6\u00f8\u00e6\u00f8",
            "contact_name": "S\u00f8ren Christensen",
            "contact_phone": "\u00e6\u00f8\u00e6\u00f8",
            "contact_email": "\u00e6\u00f8\u00e6\u00f8",
            "bill_to_address": "\u00e6\u00f8\u00e6\u00f8",
            "bill_to_postal_code": "\u00e6\u00f8\u00e6\u00f8",
            "bill_to_city": "\u00e6\u00f8\u00e6\u00f8",
            "bill_to_country": "NO",
            "bill_to_email": "\u00e6\u00f8\u00e6\u00f8",
            "quantity": "5",
            "expire_date": "07-11-2021",
            "is_email": "1",
            "use_shipping_address": "1",
            "ship_to_company": "\u00e6\u00f8\u00e6\u00f8",
            "ship_to_address": "\u00e6\u00f8\u00e6\u00f8",
            "ship_to_address_2": "\u00e6\u00f8\u00e6\u00f8",
            "ship_to_postal_code": "\u00e6\u00f8\u00e6\u00f8",
            "ship_to_city": "\u00e6\u00f8\u00e6\u00f8",
            "ship_to_country": "NO"
        },
        "Timestamp": 1632925078,
        "check": {
            "check1": "",
            "check2": "",
            "check3": "",
            "shopname": "Julegavekortet NO 300",
            "lang": 4
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field phone: string must be longer than 12 characters",
            "field": "phone",
            "type": "toolong",
            "min": "12",
            "max": "",
            "length": ""
        },
        "Date": " Wed, 29 Sep 2021 14:55:21 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "Bennedikthegaard",
            "cvr": "10864293",
            "phone": "22241120 ( efter kl. 11 )",
            "contact_name": "Lis Sk\u00f8tt-Peterssen",
            "contact_phone": "22241120 ( efter kl. 11 )",
            "contact_email": "lis@bennedikthegaard.dk",
            "bill_to_address": "Allerupvej 1",
            "bill_to_postal_code": "6520",
            "bill_to_city": "Toftlund",
            "bill_to_country": "DK",
            "quantity": "11",
            "expire_date": "07-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK"
        },
        "Timestamp": 1632920121,
        "check": {
            "check1": "",
            "check2": "",
            "check3": "2: BS58436 - 2021-09-29 14:55",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed, 29 Sep 2021 14:14:51 +0200 (CEST)",
        "input": {
            "shop_id": "575",
            "companyname": "SkilteDesign",
            "cvr": "40256571",
            "phone": "22334025",
            "contact_phone": "22334025",
            "contact_email": "hj@skiltedesign.dk",
            "bill_to_address": "Cikorievej 44",
            "bill_to_postal_code": "5220",
            "bill_to_city": "Odense S\u00d8",
            "bill_to_country": "DK",
            "quantity": "5",
            "expire_date": "31-10-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK"
        },
        "Timestamp": 1632917691,
        "check": {
            "check1": "1: BS58426 - 2021-09-29 14:14",
            "check2": "",
            "check3": "1: BS58424 - 2021-09-29 14:12",
            "shopname": "Designjulegaven",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field cvr: value is required",
            "field": "cvr",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Wed, 29 Sep 2021 13:37:42 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "Ulstrup L\u00e6gepraksis",
            "phone": "86463355",
            "contact_name": "Birgitte R\u00f8jkj\u00e6r Vraa",
            "contact_phone": "86463355",
            "contact_email": "ulstruplaegepraksis@hotmail.com",
            "bill_to_address": "Teglv\u00e6rksvej 2a",
            "bill_to_postal_code": "8860",
            "bill_to_city": "Ulstrup",
            "bill_to_country": "DK",
            "quantity": "8",
            "expire_date": "21-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "cvr": ""
        },
        "Timestamp": 1632915462,
        "check": {
            "check1": "1: BS58415 - 2021-09-29 13:38",
            "check2": "",
            "check3": "1: BS58411 - 2021-09-29 13:32",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "30",
            "error": "Could not find valid shop_id"
        },
        "Date": " Wed, 29 Sep 2021 13:13:35 +0200 (CEST)",
        "input": {
            "companyname": "Synshallen Esbjerg",
            "cvr": "38764691",
            "ean": "4571778014606",
            "phone": "29709054",
            "contact_name": "Kim Friis Eskesen",
            "contact_phone": "29709054",
            "contact_email": "mail@synshallen-esbjerg.dk",
            "bill_to_address": "Hedelundvej 11",
            "bill_to_postal_code": "6705",
            "bill_to_city": "Esbjerg \u00d8",
            "bill_to_country": "DK",
            "quantity": "5",
            "expire_date": "14-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "shop_id": ""
        },
        "Timestamp": 1632914015,
        "check": {
            "check1": "",
            "check2": "",
            "check3": "1: BS58397 - 2021-09-29 13:13",
            "shopname": "Ukendt",
            "lang": ""
        }
    },
    {
        "output": {
            "status": "30",
            "error": "Could not find valid shop_id"
        },
        "Date": " Wed, 29 Sep 2021 13:04:36 +0200 (CEST)",
        "input": {
            "companyname": "Smedeg\u00e5rdens b\u00f8rnehus ",
            "cvr": "50037118",
            "ean": "5798008359347",
            "phone": "20802865",
            "contact_name": "Trine Corell Kramer ",
            "contact_phone": "20802865",
            "contact_email": "Trco@fredensborg.dk",
            "bill_to_address": "Smedebakken 9",
            "bill_to_postal_code": "2990",
            "bill_to_city": "Niv\u00e5 ",
            "bill_to_country": "DK",
            "quantity": "23",
            "expire_date": "14-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "shop_id": ""
        },
        "Timestamp": 1632913476,
        "check": {
            "check1": "1: BS58394 - 2021-09-29 13:05",
            "check2": "",
            "check3": "1: BS58395 - 2021-09-29 13:10",
            "shopname": "Ukendt",
            "lang": ""
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field phone: string must be longer than 12 characters",
            "field": "phone",
            "type": "toolong",
            "min": "12",
            "max": "",
            "length": ""
        },
        "Date": " Wed, 29 Sep 2021 12:05:43 +0200 (CEST)",
        "input": {
            "shop_id": "55",
            "companyname": "Albers Hansen Danmark",
            "cvr": "30591054",
            "phone": "21790710 eller 31100830 ",
            "contact_name": "Malene Skaftved",
            "contact_phone": "21790710 eller 31100830 ",
            "contact_email": "ms@albers-hansen.dk",
            "bill_to_address": "Virkevangen 60",
            "bill_to_postal_code": "8960",
            "bill_to_city": "Randers S\u00d8",
            "bill_to_country": "DK",
            "quantity": "15",
            "expire_date": "01-04-2022",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK"
        },
        "Timestamp": 1632909943,
        "check": {
            "check1": "",
            "check2": "",
            "check3": "3: BS58367 - 2021-09-29 12:06",
            "shopname": "24 Gaver DK 560",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "30",
            "error": "Could not find valid shop_id"
        },
        "Date": " Tue, 28 Sep 2021 14:25:51 +0200 (CEST)",
        "input": {
            "companyname": "Alfred Jensen og S\u00f8n",
            "cvr": "13739544",
            "phone": "25350200",
            "contact_name": "Malene Abildgaard",
            "contact_phone": "25350200",
            "contact_email": "ma@aj.dk",
            "bill_to_address": "Soldalen 2",
            "bill_to_postal_code": "7100",
            "bill_to_city": "Vejle ",
            "bill_to_country": "DK",
            "quantity": "10",
            "expire_date": "14-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "shop_id": ""
        },
        "Timestamp": 1632831951,
        "check": {
            "check1": "1: BS58233 - 2021-09-28 14:26",
            "check2": "",
            "check3": "1: BS58235 - 2021-09-28 14:31",
            "shopname": "Ukendt",
            "lang": ""
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field companyname: value is required",
            "field": "companyname",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue, 28 Sep 2021 14:16:06 +0200 (CEST)",
        "input": {
            "shop_id": "2395",
            "bill_to_country": "DK",
            "quantity": "5",
            "expire_date": "21-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "cvr": "",
            "contact_email": ""
        },
        "Timestamp": 1632831366,
        "check": {
            "check1": "*",
            "check2": "*",
            "check3": "*",
            "shopname": "Guldgavekortet DK 960",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field phone: string must be longer than 12 characters",
            "field": "phone",
            "type": "toolong",
            "min": "12",
            "max": "",
            "length": ""
        },
        "Date": " Tue, 28 Sep 2021 13:24:31 +0200 (CEST)",
        "input": {
            "shop_id": "2549",
            "companyname": "COMSOL AS",
            "cvr": "979555792",
            "phone": "7384 2408 \/ 9092 1024",
            "contact_name": "Rikke Ulfseth",
            "contact_phone": "7384 2408 \/ 9092 1024",
            "contact_email": "rikke.ulfseth@comsol.no",
            "bill_to_address": "Dronningensgt. 10A",
            "bill_to_postal_code": "7011",
            "bill_to_city": "Trondheim",
            "bill_to_country": "NO",
            "bill_to_email": "faktura@comsol.no",
            "quantity": "17",
            "expire_date": "07-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "NO",
            "giftwrap": "1"
        },
        "Timestamp": 1632828271,
        "check": {
            "check1": "1: BS58211 - 2021-09-28 13:24",
            "check2": "",
            "check3": "1: BS60371 - 2021-10-13 12:50",
            "shopname": "BRA Gavekortet (NO)",
            "lang": 4
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field companyname: value is required",
            "field": "companyname",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue, 28 Sep 2021 13:17:25 +0200 (CEST)",
        "input": {
            "shop_id": "575",
            "bill_to_country": "DK",
            "quantity": "5",
            "expire_date": "31-10-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "cvr": "",
            "contact_email": ""
        },
        "Timestamp": 1632827845,
        "check": {
            "check1": "*",
            "check2": "*",
            "check3": "*",
            "shopname": "Designjulegaven",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field companyname: string must be longer than 50 characters",
            "field": "companyname",
            "type": "toolong",
            "min": "50",
            "max": "",
            "length": ""
        },
        "Date": " Tue, 28 Sep 2021 13:15:50 +0200 (CEST)",
        "input": {
            "shop_id": "53",
            "companyname": "Begravelsesforretningen I\/S v_fam. Noerager Soerensen",
            "cvr": "35903496",
            "phone": "86402222",
            "contact_name": "Hanna Soerensen",
            "contact_phone": "86402222",
            "contact_email": "hanna@brdr-oest.dk",
            "bill_to_address": "Hadsundvej 61",
            "bill_to_postal_code": "8930",
            "bill_to_city": "Randers N\u00d8",
            "bill_to_country": "DK",
            "quantity": "15",
            "expire_date": "07-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK"
        },
        "Timestamp": 1632827750,
        "check": {
            "check1": "",
            "check2": "",
            "check3": "1: BS58206 - 2021-09-28 13:16",
            "shopname": "Guldgavekortet DK 800",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field companyname: value is required",
            "field": "companyname",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue, 28 Sep 2021 12:04:25 +0200 (CEST)",
        "input": {
            "shop_id": "1832",
            "bill_to_country": "SE",
            "quantity": "72",
            "expire_date": "07-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "SE",
            "giftwrap": "1",
            "cvr": "",
            "contact_email": ""
        },
        "Timestamp": 1632823465,
        "check": {
            "check1": "*",
            "check2": "*",
            "check3": "*",
            "shopname": "24Julklappar400 (Sverige)",
            "lang": 5
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field phone: string must be longer than 12 characters",
            "field": "phone",
            "type": "toolong",
            "min": "12",
            "max": "",
            "length": ""
        },
        "Date": " Tue, 28 Sep 2021 10:52:00 +0200 (CEST)",
        "input": {
            "shop_id": "55",
            "companyname": "Herning Massivtr\u00e6 AS",
            "cvr": "25449363",
            "phone": "+4553572321  kl.8.00-12.00",
            "contact_name": "Susanne Hansen",
            "contact_phone": "+4553572321  kl.8.00-12.00",
            "contact_email": "susanne@hmt.net",
            "bill_to_address": "Cedervej 6",
            "bill_to_postal_code": "7400",
            "bill_to_city": "Herning",
            "bill_to_country": "DK",
            "quantity": "23",
            "expire_date": "07-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK"
        },
        "Timestamp": 1632819120,
        "check": {
            "check1": "1: BS58155 - 2021-09-28 10:53",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 560",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field expire_date: value is required",
            "field": "expire_date",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Tue, 28 Sep 2021 10:00:06 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "REMA 1000",
            "cvr": "26303486",
            "phone": "+4529485783",
            "contact_name": "Brian Rasmussen",
            "contact_phone": "+4529485783",
            "contact_email": "783@rema1000.dk",
            "bill_to_address": "\u00d8stre landevej 38",
            "bill_to_postal_code": "4930",
            "bill_to_city": "Maribo",
            "bill_to_country": "DK",
            "quantity": "5",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "ship_to_company": "REMA 1000",
            "ship_to_address": "\u00d8stre landevej 38",
            "ship_to_address_2": "",
            "ship_to_postal_code": "4930",
            "ship_to_city": "Maribo"
        },
        "Timestamp": 1632816006,
        "check": {
            "check1": "2: BS58122 - 2021-09-28 10:00",
            "check2": "1: BS58313 - 2021-09-29 10:12",
            "check3": "6: BS58121 - 2021-09-28 09:57",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field ean: string must be at least 13 characters",
            "field": "ean",
            "type": "tooshort",
            "min": "13",
            "max": "",
            "length": ""
        },
        "Date": " Tue, 28 Sep 2021 09:52:38 +0200 (CEST)",
        "input": {
            "shop_id": "2395",
            "companyname": "Eltraco Automation ApS",
            "cvr": "27509908",
            "ean": "DK27509908",
            "phone": "30703635",
            "contact_name": "Mikael Merlin Thomsen",
            "contact_phone": "30703635",
            "contact_email": "mmt@eltraco.com",
            "bill_to_address": "H\u00f8jager, 4, Eltraco Automation",
            "bill_to_postal_code": "5270",
            "bill_to_city": "Odense N",
            "bill_to_country": "DK",
            "quantity": "5",
            "expire_date": "07-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK"
        },
        "Timestamp": 1632815558,
        "check": {
            "check1": "1: BS58119 - 2021-09-28 09:54",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekortet DK 960",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "30",
            "error": "Could not find valid shop_id"
        },
        "Date": " Mon, 27 Sep 2021 14:52:56 +0200 (CEST)",
        "input": {
            "companyname": "SHT Vand & Milj\u00f8 ApS",
            "cvr": "31887607",
            "phone": "22570692",
            "contact_name": "Rikke Gerner Hansen",
            "contact_phone": "22570692",
            "contact_email": "rgh@sht.as",
            "bill_to_address": "Br\u00f8dlandsvej 56",
            "bill_to_postal_code": "\u00d8lsted",
            "bill_to_city": "3310",
            "bill_to_country": "DK",
            "quantity": "19",
            "expire_date": "14-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "shop_id": ""
        },
        "Timestamp": 1632747176,
        "check": {
            "check1": "1: BS58043 - 2021-09-27 14:53",
            "check2": "",
            "check3": "",
            "shopname": "Ukendt",
            "lang": ""
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field cvr: value is required",
            "field": "cvr",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon, 27 Sep 2021 14:21:04 +0200 (CEST)",
        "input": {
            "shop_id": "52",
            "companyname": "Tandl\u00e6gerne Dyrberg og Hansen",
            "bill_to_address": "N\u00f8rregade 60, 1",
            "bill_to_postal_code": "7500",
            "bill_to_country": "DK",
            "quantity": "9",
            "expire_date": "14-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "cvr": "",
            "contact_email": ""
        },
        "Timestamp": 1632745264,
        "check": {
            "check1": "*",
            "check2": "*",
            "check3": "*",
            "shopname": "Julegavekortet DK",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "30",
            "error": "Could not find valid shop_id"
        },
        "Date": " Mon, 27 Sep 2021 12:55:50 +0200 (CEST)",
        "input": {
            "companyname": "Taxi vognmand",
            "cvr": "19878988",
            "phone": "26821471",
            "contact_name": "Suhail nazir butt",
            "contact_phone": "26821471",
            "contact_email": "Jaaneadam@gmail.com ",
            "bill_to_address": "Uls\u00f8parken 27 2 Th, 27 2 th",
            "bill_to_postal_code": "2660",
            "bill_to_city": "Br\u00f8ndby Strand",
            "bill_to_country": "DK",
            "quantity": "5",
            "expire_date": "31-10-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "shop_id": ""
        },
        "Timestamp": 1632740150,
        "check": {
            "check1": "1: BS58000 - 2021-09-27 12:56",
            "check2": "",
            "check3": "",
            "shopname": "Ukendt",
            "lang": ""
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field expire_date: value is required",
            "field": "expire_date",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon, 27 Sep 2021 12:50:34 +0200 (CEST)",
        "input": {
            "shop_id": "54",
            "companyname": "HMS Lagerhaller ApS",
            "cvr": "30805100",
            "phone": "70275655",
            "contact_name": "Majken S\u00f8rensen",
            "contact_phone": "70275655",
            "contact_email": "info@hmsdanmark.dk",
            "bill_to_address": "Gettrupvej 20",
            "bill_to_postal_code": "9500",
            "bill_to_city": "Hobro",
            "bill_to_country": "DK",
            "quantity": "14",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "ship_to_company": "HMS Lagerhaller ApS",
            "ship_to_address": "Gettrupvej 20",
            "ship_to_address_2": "",
            "ship_to_postal_code": "9500",
            "ship_to_city": "Hobro"
        },
        "Timestamp": 1632739834,
        "check": {
            "check1": "1: BS57997 - 2021-09-27 12:51",
            "check2": "",
            "check3": "",
            "shopname": "24 Gaver DK 400",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "30",
            "error": "Could not find valid shop_id"
        },
        "Date": " Mon, 27 Sep 2021 12:36:04 +0200 (CEST)",
        "input": {
            "companyname": "Hotel T\u00f8nderhus",
            "cvr": "35662006",
            "phone": "74722222",
            "contact_name": "Marcel Wendicke",
            "contact_phone": "74722222",
            "contact_email": "info@hoteltoenderhus.dk",
            "bill_to_address": "Jomfrustien 1",
            "bill_to_postal_code": "6270",
            "bill_to_city": "T\u00f8nder",
            "bill_to_country": "DK",
            "quantity": "35",
            "expire_date": "14-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "shop_id": ""
        },
        "Timestamp": 1632738964,
        "check": {
            "check1": "1: BS57995 - 2021-09-27 12:36",
            "check2": "",
            "check3": "",
            "shopname": "Ukendt",
            "lang": ""
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field ean: string must be at least 13 characters",
            "field": "ean",
            "type": "tooshort",
            "min": "13",
            "max": "",
            "length": ""
        },
        "Date": " Mon, 27 Sep 2021 12:10:04 +0200 (CEST)",
        "input": {
            "shop_id": "52",
            "companyname": "hospice Sj\u00e6lland",
            "cvr": "28944381",
            "ean": "579002617560",
            "phone": "53811960",
            "contact_name": "Karin H\u00f8jgaard Jeppesen",
            "contact_phone": "53811960",
            "contact_email": "kjepp@hosj.dk",
            "bill_to_address": "T\u00f8nsbergvej 61",
            "bill_to_postal_code": "4000",
            "bill_to_city": "Roskilde",
            "bill_to_country": "DK",
            "quantity": "50",
            "expire_date": "31-10-2021",
            "is_email": "0",
            "use_shipping_address": "1",
            "ship_to_company": "hospice Sj\u00e6lland",
            "ship_to_address": "T\u00f8nsbergvej 61",
            "ship_to_address_2": "T\u00f8nsbergvej 61",
            "ship_to_postal_code": "4000",
            "ship_to_city": "Roskilde",
            "ship_to_country": "DK"
        },
        "Timestamp": 1632737404,
        "check": {
            "check1": "1: BS57991 - 2021-09-27 12:11",
            "check2": "",
            "check3": "",
            "shopname": "Julegavekortet DK",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field cvr: string must be at least 8 characters",
            "field": "cvr",
            "type": "tooshort",
            "min": "8",
            "max": "",
            "length": ""
        },
        "Date": " Mon, 27 Sep 2021 09:27:28 +0200 (CEST)",
        "input": {
            "shop_id": "56",
            "companyname": "Regnestuen APS",
            "cvr": "1586666",
            "phone": "51250660",
            "contact_name": "Julie Windfeld-Vedel",
            "contact_phone": "51250660",
            "contact_email": "jwv@regnestuen.dk",
            "bill_to_address": "Struenseegade 15A 4 mf",
            "bill_to_postal_code": "2200",
            "bill_to_city": "K\u00f8benhavn N",
            "bill_to_country": "DK",
            "quantity": "10",
            "expire_date": "21-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK"
        },
        "Timestamp": 1632727648,
        "check": {
            "check1": "",
            "check2": "",
            "check3": "1: BS57940 - 2021-09-27 09:28",
            "shopname": "24 Gaver DK 640",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field ean: string must be at least 13 characters",
            "field": "ean",
            "type": "tooshort",
            "min": "13",
            "max": "",
            "length": ""
        },
        "Date": " Mon, 27 Sep 2021 08:46:21 +0200 (CEST)",
        "input": {
            "shop_id": "54",
            "companyname": "Doktor Elmira Christensen",
            "cvr": "34144451",
            "ean": "-",
            "phone": "86420711",
            "contact_name": "Heidi \u00d8gendahl",
            "contact_phone": "86420711",
            "contact_email": "info@86420711.dk",
            "bill_to_address": "\u00d8stervold 20, 2 sal",
            "bill_to_postal_code": "8900",
            "bill_to_city": "Randers C",
            "bill_to_country": "DK",
            "quantity": "5",
            "expire_date": "07-11-2021",
            "is_email": "0",
            "use_shipping_address": "1",
            "ship_to_company": "L\u00e6ge Elmira Christensen",
            "ship_to_address": "\u00d8stervold 20, 2 sal",
            "ship_to_address_2": "\u00d8stervold 20, 2 sal",
            "ship_to_postal_code": "8900",
            "ship_to_city": "Randers C",
            "ship_to_country": "DK"
        },
        "Timestamp": 1632725181,
        "check": {
            "check1": "1: BS57936 - 2021-09-27 08:48",
            "check2": "",
            "check3": "3: BS57937 - 2021-09-27 08:50",
            "shopname": "24 Gaver DK 400",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field cvr: value is required",
            "field": "cvr",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Mon, 27 Sep 2021 08:43:40 +0200 (CEST)",
        "input": {
            "shop_id": "54",
            "companyname": "L\u00e6ge Elmira Christensen",
            "bill_to_address": "\u00d8stervold 20, 2 sal",
            "bill_to_postal_code": "8900",
            "bill_to_country": "DK",
            "quantity": "5",
            "expire_date": "07-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "cvr": "",
            "contact_email": ""
        },
        "Timestamp": 1632725020,
        "check": {
            "check1": "*",
            "check2": "*",
            "check3": "*",
            "shopname": "24 Gaver DK 400",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "30",
            "error": "Could not find valid shop_id"
        },
        "Date": " Fri, 24 Sep 2021 19:09:39 +0200 (CEST)",
        "input": {
            "companyname": "Bjerges Bogf\u00f8ring ApS",
            "cvr": "40618821",
            "phone": "52308077",
            "contact_name": "tenna@bjergesbog.dk",
            "contact_phone": "52308077",
            "contact_email": "tenna@bjergesbog.dk",
            "bill_to_address": "R\u00f8nnev\u00e6nget 54",
            "bill_to_postal_code": "6870",
            "bill_to_city": "\u00d8lgod",
            "bill_to_country": "DK",
            "quantity": "5",
            "expire_date": "31-10-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "shop_id": ""
        },
        "Timestamp": 1632503379,
        "check": {
            "check1": "1: BS57910 - 2021-09-24 19:10",
            "check2": "",
            "check3": "",
            "shopname": "Ukendt",
            "lang": ""
        }
    },
    {
        "output": {
            "status": "30",
            "error": "Could not find valid shop_id"
        },
        "Date": " Fri, 24 Sep 2021 09:30:35 +0200 (CEST)",
        "input": {
            "companyname": "ASG EL-TEK ApS",
            "cvr": "42477826",
            "phone": "30200027",
            "contact_name": "Kathrin Br\u00e6ndgaard",
            "contact_phone": "30200027",
            "contact_email": "asg@asgel.dk",
            "bill_to_address": "Voldum-Rud Vej 77",
            "bill_to_postal_code": "8370",
            "bill_to_city": "Hadsten",
            "bill_to_country": "DK",
            "quantity": "10",
            "expire_date": "14-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "giftwrap": "1",
            "shop_id": ""
        },
        "Timestamp": 1632468635,
        "check": {
            "check1": "1: BS57806 - 2021-09-24 09:31",
            "check2": "",
            "check3": "",
            "shopname": "Ukendt",
            "lang": ""
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field contact_name: value is required",
            "field": "contact_name",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu, 23 Sep 2021 15:50:52 +0200 (CEST)",
        "input": {
            "shop_id": "52",
            "companyname": "L\u00e6ge Anna Salling",
            "cvr": "33424086",
            "phone": "29919682",
            "contact_phone": "29919682",
            "contact_email": "annasalling@hotmail.com",
            "bill_to_address": "Kirkegade 7, 1th",
            "bill_to_postal_code": "8900",
            "bill_to_city": "Randers C",
            "bill_to_country": "DK",
            "quantity": "5",
            "expire_date": "31-10-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK"
        },
        "Timestamp": 1632405052,
        "check": {
            "check1": "1: BS57790 - 2021-09-23 15:51",
            "check2": "",
            "check3": "1: BS57789 - 2021-09-23 15:46",
            "shopname": "Julegavekortet DK",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field cvr: CVR must be 10 digit number ",
            "field": "cvr",
            "type": "exactlength",
            "min": "",
            "max": "",
            "length": "10"
        },
        "Date": " Thu, 23 Sep 2021 13:58:10 +0200 (CEST)",
        "input": {
            "shop_id": "1832",
            "companyname": "Gavefabrikken",
            "cvr": "11111111",
            "phone": "+4520960563",
            "contact_name": "nnnn",
            "contact_phone": "+4520960563",
            "contact_email": "birgitte@illerupmedia.dk",
            "bill_to_address": "Carls Jacobsens vej 20",
            "bill_to_postal_code": "2500",
            "bill_to_city": "Valby",
            "bill_to_country": "SE",
            "quantity": "20",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "SE",
            "giftwrap": "1",
            "ship_to_company": "Gavefabrikken",
            "ship_to_address": "Carls Jacobsens vej 20",
            "ship_to_address_2": "",
            "ship_to_postal_code": "2500",
            "ship_to_city": "Valby"
        },
        "Timestamp": 1632398290,
        "check": {
            "check1": "",
            "check2": "",
            "check3": "16: BS56012 - 2021-08-26 11:49",
            "shopname": "24Julklappar400 (Sverige)",
            "lang": 5
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field cvr: CVR must be 10 digit number ",
            "field": "cvr",
            "type": "exactlength",
            "min": "",
            "max": "",
            "length": "10"
        },
        "Date": " Thu, 23 Sep 2021 13:58:07 +0200 (CEST)",
        "input": {
            "shop_id": "1832",
            "companyname": "test",
            "cvr": "12341234",
            "phone": "12341234",
            "contact_name": "test",
            "contact_phone": "12341234",
            "contact_email": "test@test.com",
            "bill_to_address": "test",
            "bill_to_postal_code": "1234",
            "bill_to_city": "test",
            "bill_to_country": "SE",
            "quantity": "5",
            "expire_date": "07-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "SE",
            "giftwrap": "1",
            "ship_to_company": "test",
            "ship_to_address": "test",
            "ship_to_address_2": "",
            "ship_to_postal_code": "1234",
            "ship_to_city": "test"
        },
        "Timestamp": 1632398287,
        "check": {
            "check1": "",
            "check2": "",
            "check3": "8: BS56254 - 2021-09-04 20:04",
            "shopname": "24Julklappar400 (Sverige)",
            "lang": 5
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field cvr: CVR must be 10 digit number ",
            "field": "cvr",
            "type": "exactlength",
            "min": "",
            "max": "",
            "length": "10"
        },
        "Date": " Thu, 23 Sep 2021 13:57:19 +0200 (CEST)",
        "input": {
            "shop_id": "1981",
            "companyname": "Gavefabrikken",
            "cvr": "11111111",
            "phone": "+4520960563",
            "contact_name": "cccc",
            "contact_phone": "+4520960563",
            "contact_email": "birgitte@illerupmedia.dk",
            "bill_to_address": "Carls Jacobsens vej 20",
            "bill_to_postal_code": "2500",
            "bill_to_city": "Valby",
            "bill_to_country": "SE",
            "quantity": "20",
            "expire_date": "21-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "SE",
            "ship_to_company": "Gavefabrikken",
            "ship_to_address": "Carls Jacobsens vej 20",
            "ship_to_address_2": "",
            "ship_to_postal_code": "2500",
            "ship_to_city": "Valby"
        },
        "Timestamp": 1632398239,
        "check": {
            "check1": "",
            "check2": "",
            "check3": "16: BS56012 - 2021-08-26 11:49",
            "shopname": "24Julklappar800 (Sverige)",
            "lang": 5
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field cvr: CVR must be 10 digit number ",
            "field": "cvr",
            "type": "exactlength",
            "min": "",
            "max": "",
            "length": "10"
        },
        "Date": " Thu, 23 Sep 2021 13:48:58 +0200 (CEST)",
        "input": {
            "shop_id": "1832",
            "companyname": "Gavefabrikken",
            "cvr": "12341234",
            "phone": "+4520960563",
            "contact_name": "fff",
            "contact_phone": "+4520960563",
            "contact_email": "birgitte@illerupmedia.dk",
            "bill_to_address": "Carls Jacobsens vej 20",
            "bill_to_postal_code": "2500",
            "bill_to_city": "Valby",
            "bill_to_country": "SE",
            "quantity": "5",
            "expire_date": "07-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "SE",
            "giftwrap": "1",
            "ship_to_company": "Gavefabrikken",
            "ship_to_address": "Carls Jacobsens vej 20",
            "ship_to_address_2": "",
            "ship_to_postal_code": "2500",
            "ship_to_city": "Valby"
        },
        "Timestamp": 1632397738,
        "check": {
            "check1": "",
            "check2": "",
            "check3": "11: BS56254 - 2021-09-04 20:04",
            "shopname": "24Julklappar400 (Sverige)",
            "lang": 5
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field phone: string must be at least 8 characters",
            "field": "phone",
            "type": "tooshort",
            "min": "8",
            "max": "",
            "length": ""
        },
        "Date": " Thu, 23 Sep 2021 13:48:58 +0200 (CEST)",
        "input": {
            "shop_id": "1832",
            "companyname": "Gavefabrikken",
            "cvr": "123412344",
            "phone": "1",
            "contact_name": "test",
            "contact_phone": "1",
            "contact_email": "ops-test02@opsving.com",
            "bill_to_address": "dummy",
            "bill_to_postal_code": "1234",
            "bill_to_city": "Dummy",
            "bill_to_country": "SE",
            "quantity": "5",
            "expire_date": "07-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "SE",
            "giftwrap": "1"
        },
        "Timestamp": 1632397738,
        "check": {
            "check1": "",
            "check2": "",
            "check3": "",
            "shopname": "24Julklappar400 (Sverige)",
            "lang": 5
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field cvr: CVR must be 10 digit number ",
            "field": "cvr",
            "type": "exactlength",
            "min": "",
            "max": "",
            "length": "10"
        },
        "Date": " Thu, 23 Sep 2021 13:48:36 +0200 (CEST)",
        "input": {
            "shop_id": "1832",
            "companyname": "Gavefabrikken",
            "cvr": "12341234",
            "phone": "+4520960563",
            "contact_name": "fff",
            "contact_phone": "+4520960563",
            "contact_email": "birgitte@illerupmedia.dk",
            "bill_to_address": "Carls Jacobsens vej 20",
            "bill_to_postal_code": "2500",
            "bill_to_city": "Valby",
            "bill_to_country": "SE",
            "quantity": "5",
            "expire_date": "07-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "SE",
            "giftwrap": "1",
            "ship_to_company": "Gavefabrikken",
            "ship_to_address": "Carls Jacobsens vej 20",
            "ship_to_address_2": "",
            "ship_to_postal_code": "2500",
            "ship_to_city": "Valby"
        },
        "Timestamp": 1632397716,
        "check": {
            "check1": "",
            "check2": "",
            "check3": "11: BS56254 - 2021-09-04 20:04",
            "shopname": "24Julklappar400 (Sverige)",
            "lang": 5
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field cvr: value is required",
            "field": "cvr",
            "type": "required",
            "min": "",
            "max": "",
            "length": ""
        },
        "Date": " Thu, 23 Sep 2021 13:48:11 +0200 (CEST)",
        "input": {
            "shop_id": "1832",
            "companyname": "Gavefabrikken",
            "phone": "+4520960563",
            "contact_name": "test",
            "contact_phone": "+4520960563",
            "contact_email": "birgitte@illerupmedia.dk",
            "bill_to_address": "Carls Jacobsens vej 20",
            "bill_to_postal_code": "2500",
            "bill_to_city": "Valby",
            "bill_to_country": "SE",
            "quantity": "5",
            "expire_date": "07-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "SE",
            "giftwrap": "1",
            "cvr": ""
        },
        "Timestamp": 1632397691,
        "check": {
            "check1": "",
            "check2": "",
            "check3": "5: BS57603 - 2021-09-22 15:56",
            "shopname": "24Julklappar400 (Sverige)",
            "lang": 5
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field cvr: CVR must be 10 digit number ",
            "field": "cvr",
            "type": "exactlength",
            "min": "",
            "max": "",
            "length": "10"
        },
        "Date": " Thu, 23 Sep 2021 13:47:31 +0200 (CEST)",
        "input": {
            "shop_id": "1832",
            "companyname": "test1",
            "cvr": "12341234",
            "phone": "12341234",
            "contact_name": "test",
            "contact_phone": "12341234",
            "contact_email": "test@test.com",
            "bill_to_address": "test",
            "bill_to_postal_code": "1234",
            "bill_to_city": "test",
            "bill_to_country": "SE",
            "quantity": "5",
            "expire_date": "07-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "SE",
            "giftwrap": "1",
            "ship_to_company": "test1",
            "ship_to_address": "test",
            "ship_to_address_2": "",
            "ship_to_postal_code": "1234",
            "ship_to_city": "test"
        },
        "Timestamp": 1632397651,
        "check": {
            "check1": "",
            "check2": "",
            "check3": "8: BS56254 - 2021-09-04 20:04",
            "shopname": "24Julklappar400 (Sverige)",
            "lang": 5
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field cvr: CVR must be 10 digit number ",
            "field": "cvr",
            "type": "exactlength",
            "min": "",
            "max": "",
            "length": "10"
        },
        "Date": " Thu, 23 Sep 2021 13:44:43 +0200 (CEST)",
        "input": {
            "shop_id": "1832",
            "companyname": "test foretage",
            "cvr": "12341234",
            "phone": "12341234",
            "contact_name": "Testman",
            "contact_phone": "12341234",
            "contact_email": "temahigadf@khgaf.com",
            "bill_to_address": "adressetrest",
            "bill_to_postal_code": "12345",
            "bill_to_city": "byby",
            "bill_to_country": "SE",
            "quantity": "6",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "SE",
            "giftwrap": "1",
            "ship_to_company": "test foretage",
            "ship_to_address": "adressetrest",
            "ship_to_address_2": "",
            "ship_to_postal_code": "12345",
            "ship_to_city": "byby"
        },
        "Timestamp": 1632397483,
        "check": {
            "check1": "",
            "check2": "",
            "check3": "6: BS56254 - 2021-09-04 20:04",
            "shopname": "24Julklappar400 (Sverige)",
            "lang": 5
        }
    },
    {
        "output": {
            "status": "30",
            "error": "Could not find valid shop_id"
        },
        "Date": " Thu, 23 Sep 2021 13:37:33 +0200 (CEST)",
        "input": {
            "companyname": "NK Byggeservice.dk",
            "cvr": "31604796",
            "phone": "+4528969496",
            "contact_name": "Niels Kylling",
            "contact_phone": "+4528969496",
            "contact_email": "dortebeier@outlook.dk",
            "bill_to_address": "Stationsvej 20 A",
            "bill_to_postal_code": "4800",
            "bill_to_city": "Nyk\u00f8bing Fl.",
            "bill_to_country": "DK",
            "quantity": "5",
            "expire_date": "31-10-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "shop_id": ""
        },
        "Timestamp": 1632397053,
        "check": {
            "check1": "1: BS57741 - 2021-09-23 13:37",
            "check2": "",
            "check3": "",
            "shopname": "Ukendt",
            "lang": ""
        }
    },
    {
        "output": {
            "status": "30",
            "error": "Could not find valid shop_id"
        },
        "Date": " Thu, 23 Sep 2021 13:36:25 +0200 (CEST)",
        "input": {
            "companyname": "Gavefabrikken",
            "phone": "+4520960563",
            "contact_phone": "+4520960563",
            "contact_email": "birgitte@illerupmedia.dk",
            "bill_to_address": "Carls Jacobsens vej 20",
            "bill_to_postal_code": "2500",
            "bill_to_city": "Valby",
            "bill_to_country": "SE",
            "quantity": "5",
            "expire_date": "21-11-2021",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "SE",
            "shop_id": "",
            "cvr": ""
        },
        "Timestamp": 1632396985,
        "check": {
            "check1": "2: BS57604 - 2021-09-22 15:57",
            "check2": "",
            "check3": "3: BS57603 - 2021-09-22 15:56",
            "shopname": "Ukendt",
            "lang": ""
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field cvr: CVR must be 10 digit number ",
            "field": "cvr",
            "type": "exactlength",
            "min": "",
            "max": "",
            "length": "10"
        },
        "Date": " Thu, 23 Sep 2021 13:34:03 +0200 (CEST)",
        "input": {
            "shop_id": "1832",
            "companyname": "NOget",
            "cvr": "12341234",
            "phone": "12341234",
            "contact_name": "Anders",
            "contact_phone": "12341234",
            "contact_email": "email@email.com",
            "bill_to_address": "adresse test",
            "bill_to_postal_code": "18600",
            "bill_to_city": "Stockholm",
            "bill_to_country": "SE",
            "quantity": "5",
            "expire_date": "07-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "SE",
            "giftwrap": "1",
            "ship_to_company": "NOget",
            "ship_to_address": "adresse test",
            "ship_to_address_2": "",
            "ship_to_postal_code": "18600",
            "ship_to_city": "Stockholm"
        },
        "Timestamp": 1632396843,
        "check": {
            "check1": "",
            "check2": "",
            "check3": "6: BS56254 - 2021-09-04 20:04",
            "shopname": "24Julklappar400 (Sverige)",
            "lang": 5
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field cvr: CVR must be 10 digit number ",
            "field": "cvr",
            "type": "exactlength",
            "min": "",
            "max": "",
            "length": "10"
        },
        "Date": " Thu, 23 Sep 2021 13:24:14 +0200 (CEST)",
        "input": {
            "shop_id": "1832",
            "companyname": "testetesr",
            "cvr": "12344322",
            "phone": "12341234",
            "contact_name": "dgbdg",
            "contact_phone": "12341234",
            "contact_email": "test@test.com",
            "bill_to_address": "testtetee",
            "bill_to_postal_code": "18600",
            "bill_to_city": "stockholm",
            "bill_to_country": "SE",
            "quantity": "5",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "1",
            "ship_to_company": "test",
            "ship_to_address": "fggdfbgf",
            "ship_to_address_2": "fggdfbgf",
            "ship_to_postal_code": "18600",
            "ship_to_city": "stockholm",
            "ship_to_country": "SE"
        },
        "Timestamp": 1632396254,
        "check": {
            "check1": "",
            "check2": "",
            "check3": "4: BS56254 - 2021-09-04 20:04",
            "shopname": "24Julklappar400 (Sverige)",
            "lang": 5
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field companyname: string must be longer than 50 characters",
            "field": "companyname",
            "type": "toolong",
            "min": "50",
            "max": "",
            "length": ""
        },
        "Date": " Thu, 23 Sep 2021 10:26:50 +0200 (CEST)",
        "input": {
            "shop_id": "2395",
            "companyname": "Revision K\u00f8benhavn Godkendt Revisionspartnerselskab",
            "cvr": "34619654",
            "phone": "22767722",
            "contact_name": "Mads Lutz J\u00f8rgensen",
            "contact_phone": "22767722",
            "contact_email": "MLJ@revisionkbh.dk",
            "bill_to_address": "Nimbusparken 24, 3. ",
            "bill_to_postal_code": "2000",
            "bill_to_city": "Frederiksberg",
            "bill_to_country": "DK",
            "quantity": "18",
            "expire_date": "21-11-2021",
            "is_email": "0",
            "use_shipping_address": "0",
            "ship_to_country": "DK"
        },
        "Timestamp": 1632385610,
        "check": {
            "check1": "1: BS57681 - 2021-09-23 10:27",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekortet DK 960",
            "lang": 1
        }
    },
    {
        "output": {
            "status": "30",
            "error": "Could not find valid shop_id"
        },
        "Date": " Thu, 23 Sep 2021 09:40:50 +0200 (CEST)",
        "input": {
            "companyname": "test",
            "cvr": "12131234",
            "phone": "12341234",
            "contact_name": "test",
            "contact_phone": "12341234",
            "contact_email": "test@test.com",
            "bill_to_address": "test",
            "bill_to_postal_code": "1234",
            "bill_to_city": "test",
            "bill_to_country": "DK",
            "quantity": "5",
            "expire_date": "07-11-2021",
            "is_email": "0",
            "use_shipping_address": "1",
            "ship_to_company": "test",
            "ship_to_address": "test",
            "ship_to_address_2": "test",
            "ship_to_postal_code": "1234",
            "ship_to_city": "test",
            "ship_to_country": "DK",
            "shop_id": ""
        },
        "Timestamp": 1632382850,
        "check": {
            "check1": "1: BS57665 - 2021-09-23 09:40",
            "check2": "",
            "check3": "3: BS56254 - 2021-09-04 20:04",
            "shopname": "Ukendt",
            "lang": ""
        }
    },
    {
        "output": {
            "status": "40",
            "error": "Validation error on field expire_date: value is required",
            "field": "expire_date",
            "type": "required"
        },
        "Date": " Thu, 23 Sep 2021 07:59:59 +0200 (CEST)",
        "input": {
            "shop_id": "2395",
            "companyname": "M\u00f8rke Fjernvarme",
            "cvr": "35580913",
            "phone": "86377230",
            "contact_name": "Bettina Petersen",
            "contact_phone": "86377230",
            "contact_email": "INFO@MOERKEFJERNVARME.DK",
            "bill_to_address": "Fabriksvej 20",
            "bill_to_postal_code": "8544",
            "bill_to_city": "M\u00f8rke",
            "bill_to_country": "DK",
            "quantity": "5",
            "is_email": "1",
            "use_shipping_address": "0",
            "ship_to_country": "DK",
            "ship_to_company": "M\u00f8rke Fjernvarme",
            "ship_to_address": "Fabriksvej 20",
            "ship_to_address_2": "",
            "ship_to_postal_code": "8544",
            "ship_to_city": "M\u00f8rke"
        },
        "Timestamp": 1632376799,
        "check": {
            "check1": "1: BS57619 - 2021-09-23 08:01",
            "check2": "",
            "check3": "",
            "shopname": "Guldgavekortet DK 960",
            "lang": 1
        }
    }
]';
        return $content;

    }

}