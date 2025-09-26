<?php

namespace GFUnit\development\fixscripts;

class CreateEarly
{
    public function __construct()
    {

    }

    public function createEarly()
    {

        $earlyItemNo = "2060-2329";

        // Load orders
        $sql = "SELECT * FROM `company_order` where created_datetime >= '2024-08-14 00:00:00' && created_datetime < '2024-09-05 00:00:00' && shop_id in (select shop_id from cardshop_settings where language_code = 1 && concept_parent != 'LUKS') && order_state not in (7,8)  and id not in (49230,49231,49233,49234,49236,49237,49238,49239,49240,49241,49243,49244,49245,49246,49247,49248,49251,49253,49254,49255,49256,49257,49259,49263,49270,49271,49272,49273,49274,49275,49276,49277,49280,49282,49283,49284,49286,49289,49291,49292,49293,49294,49296,49297,49299,49300,49302,49304,49306,49307,49308,49310,49311,49312,49313,49314,49315,49317,49319,49320,49321,49322,49327,49329,49330,49331,49335,49339,49346,49348,49349,49352,49353,49355,49357,49359,49360,49361,49364,49370,49372,49374,49375,49377,49378,49379,49380,49382,49383,49385,49386,49387,49389,49391,49393,49394,49397,49398,49399,49400,49401,49402,49404,49405,49409,49410,49411,49412,49415,49417,49418,49419,49420,49423,49426,49430,49434,49435,49437,49438,49440,49441,49443,49445,49446,49447,49448,49457,49458,49460,49461,49466,49467,49468,49469,49470,49472,49473,49475,49476,49480,49481,49482,49484,49485,49490,49492,49493,49494,49498,49502,49503,49505,49507,49508,49509,49510,49513,49514,49515,49516,49517,49518,49519,49521,49523,49524,49525,49526,49528,49530,49531,49532,49533,49534,49535,49536,49541,49544,49545,49546,49547,49548,49553,49554,49555,49556,49557,49558,49567,49570,49573,49575,49576,49585,49587,49592,49599,49602,49617,49619,49623,49629,49633,49634,49636,49637,49638,49639,49645,49647,49649,49651,49652,49654,49655,49656,49657,49658,49659,49662,49663,49665,49666,49667,49668,49669,49671,49672,49673,49676,49677,49678,49679,49680,49683,49686,49687,49688,49689,49690,49691,49694,49696,49697,49699,49700,49701,49702,49703,49704,49705,49708,49709,49713,49714,49716,49717,49718,49719,49724,49726,49727,49728,49731,49732,49738,49739,49741,49742,49746,49747,49748,49749,49750,49752,49755,49756,49757,49758,49759,49761,49762,49763,49764,49765,49766,49767,49768,49769,49770,49771,49773,49776,49777,49778,49779,49782,49783,49784,49786,49787,49787,49790,49791,49793,49795,49797,49799,49800,49803,49804,49805,49806,49810,49814,49817,49819,49820,49821,49823,49824,49830,49834,49835,49836,49843,49844,49845,49846,49847,49848,49854,49855,49859,49862,49863,49864,49866,49871,49872,49873,49874,49875,49876,49878,49885,49886,49889,49895,49896,49898,49899,49907,49908,49913,49930,49938,49940,49945,49946,49947,49949,49950,49952,49957,49958,49960,49961,49966,49967,49968,49973,49974,49977,49979,49981,49983,49985,49986,49987,49988,49989,49990,49992,49993,49999,50000,50002,50003,50004,50005,50007,50008,50011,50012,50013,50014,50016,50017,50018,50021,50022,50024,50026,50027,50028,50032,50033,50035,50036,50037,50038,50039,50041,50043,50046,50048,50050,50051,50052,50053,50054,50055,50056,50057,50058,50060,50061,50062,50063,50064,50067,50068,50070,50071,50072,50073,50075,50082,50083,50085,50087,50090,50091,50092,50094,50096,50097,50098,50100,50101,50111,50112,50113,50115,50116,50117,50118,50119,50120,50121,50124,50129,50132,50133,50134,50135,50142,50143,50146,50147,50150,50152,50153,50154,50159,50169,50176,50176,50177,50184,50189,50192,50196,50202,50203,50204,50207,50212,50214,50215,50217,50219,50220,50224,50225,50228,50231,50233,50235,50236,50242,50247,50249,50252,50255,50264)
ORDER BY `company_order`.`created_datetime` ASC";

        $orderList = \CompanyOrder::find_by_sql($sql);

        echo "Found ".count($orderList)." to create earlyorders for<br>";

        foreach($orderList  as $order) {

            $company = \Company::find($order->company_id);
            $this->createEarlyShipment($earlyItemNo,$order,$company);


        }

        // Complete transaction and start a new
        \System::connection()->commit();
        \System::connection()->transaction();

    }

    private function createEarlyShipment($itemno,$order,$company)
    {

        echo "Create earlyorder for ".$company->name." with itemno ".$itemno."<br>";

        $shipment = new \Shipment();
        $shipment->companyorder_id = $order->id;
        $shipment->created_date = date('d-m-Y H:i:s');
        $shipment->shipment_type = 'earylorder';
        $shipment->handler = 'navision';
        $shipment->quantity = 1;
        $shipment->itemno = $itemno;
        $shipment->description = "";

        $shipment->itemno2 = "";
        $shipment->quantity2 = 0;
        $shipment->itemno3 = "";
        $shipment->quantity3 = 0;
        $shipment->itemno4 = "";
        $shipment->quantity4 = 0;
        $shipment->itemno5 = "";
        $shipment->quantity5 = 0;

        $shipment->description2 = "";
        $shipment->description3 = "";
        $shipment->description4 = "";
        $shipment->description5 = "";

        $shipment->isshipment = 1;
        $shipment->from_certificate_no = 0;
        $shipment->to_certificate_no = 0;

        $shipment->shipto_name = $company->ship_to_company;
        $shipment->shipto_address = $company->ship_to_address;
        $shipment->shipto_address2 = $company->ship_to_address_2;
        $shipment->shipto_city = $company->ship_to_city;
        $shipment->shipto_postcode = $company->ship_to_postal_code;
        $shipment->shipto_country = $company->ship_to_country;
        $shipment->shipto_contact = $company->contact_name;
        $shipment->shipto_email = $company->contact_email;
        $shipment->shipto_phone = $company->contact_phone;

        $shipment->shipment_note = "";
        $shipment->gls_shipment = 0;
        $shipment->handle_country = 0;
        $shipment->shipment_state = 1;
        $shipment->shipment_sync_date = null;
        $shipment->deleted_date = null;
        $shipment->force_syncnow = 0;
        $shipment->series_master = 0;
        $shipment->series_uuid = null;
        $shipment->shipto_state = 0;
        $shipment->sync_delay = null;
        $shipment->sync_note = null;
        $shipment->reservation_released = null;
        $shipment->shipped_date = null;
        $shipment->consignor_created = null;
        $shipment->consignor_labelno = null;
        $shipment->nav_order_no = null;

        $shipment->save();



    }


}

