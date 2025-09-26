<?php
set_time_limit ( 3000 );
ini_set('memory_limit', '128M');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Controller media
// Date created  Wed, 06 Apr 2016 09:39:56 +0200
// Created by Bitworks
class stats2021Controller
{
    public function Index()
    {
    }
    public static $metadata = array(
        54 => array("valuealias" => 4, "name" => 587, "address1" => 588, "address2" => 589, "zip" => 590, "city" => 591, "email" => 586, "phone" => 761, "gaveklub" => 762, "country" => "Danmark", "lang_code" => "da", "lang_num" => 1),
        55 => array("valuealias" => 5, "name" => 595, "address1" => 596, "address2" => 597, "zip" => 598, "city" => 599, "email" => 594, "phone" => 763, "gaveklub" => 764, "country" => "Danmark", "lang_code" => "da", "lang_num" => 1),
        56 => array("valuealias" => 6, "name" => 603, "address1" => 604, "address2" => 605, "zip" => 606, "city" => 607, "email" => 602, "phone" => 765, "gaveklub" => 766, "country" => "Danmark", "lang_code" => "da", "lang_num" => 1),
        575 => array("valuealias" => "D7-", "name" => 2932, "address1" => 0, "address2" => 0, "zip" => 0, "city" => 0, "email" => 2933, "phone" => 0, "gaveklub" => 16122, "country" => "Danmark", "lang_code" => "da", "lang_num" => 1),
        4662 => array("valuealias" => "D9-", "name" => 27269, "address1" => 0, "address2" => 0, "zip" => 0, "city" => 0, "email" => 27270, "phone" => 0, "gaveklub" => 27271, "country" => "Danmark", "lang_code" => "da", "lang_num" => 1),
        310 => array("valuealias" => 3, "name" => 1332, "address1" => 0, "address2" => 0, "zip" => 0, "city" => 0, "email" => 1333, "phone" => 0, "gaveklub" => 4969, "country" => "Danmark", "lang_code" => "da", "lang_num" => 1),
        290 => array("valuealias" => 2, "name" => 1292, "address1" => 0, "address2" => 0, "zip" => 0, "city" => 0, "email" => 1293, "phone" => 0, "gaveklub" => 1918, "country" => "Danmark", "lang_code" => "da", "lang_num" => 1),
        2548 => array("valuealias" => "GR-", "name" => 14405, "address1" => 0, "address2" => 0, "zip" => 0, "city" => 0, "email" => 14406, "phone" => 0, "gaveklub" => 14407, "country" => "Danmark", "lang_code" => "da", "lang_num" => 1),
        53 => array("valuealias" => "G8-", "name" => 718, "address1" => 751, "address2" => 752, "zip" => 753, "city" => 754, "email" => 719, "phone" => 767, "gaveklub" => 768, "country" => "Danmark", "lang_code" => "da", "lang_num" => 1),
        2395 => array("valuealias" => "G9-", "name" => 13652, "address1" => 16642, "address2" => 16643, "zip" => 16644, "city" => 16645, "email" => 13653, "phone" => 16646, "gaveklub" => 13654, "country" => "Danmark", "lang_code" => "da", "lang_num" => 1),
        52 => array("valuealias" => "JK5-", "name" => 32, "address1" => 0, "address2" => 0, "zip" => 0, "city" => 0, "email" => 31, "phone" => 582, "gaveklub" => 583, "country" => "Danmark", "lang_code" => "da", "lang_num" => 1),
        4668 => array("valuealias" => "JK7-", "name" => 27299, "address1" => 0, "address2" => 0, "zip" => 0, "city" => 0, "email" => 27300, "phone" => 28556, "gaveklub" => 27301, "country" => "Danmark", "lang_code" => "da", "lang_num" => 1),
        7121 => array("valuealias" => "JGV-", "name" => 27299, "address1" => 0, "address2" => 0, "zip" => 0, "city" => 0, "email" => 27300, "phone" => 28556, "gaveklub" => 27301, "country" => "Danmark", "lang_code" => "da", "lang_num" => 1),


        2960 => array("valuealias" => "L4-", "name" => 16989, "address1" => 17479, "address2" => 17480, "zip" => 17481, "city" => 17482, "email" => 16990, "phone" => 17483, "gaveklub" => 16991, "country" => "Danmark", "lang_code" => "da", "lang_num" => 1),
        2961 => array("valuealias" => "L1-", "name" => 16994, "address1" => 17494, "address2" => 17495, "zip" => 17496, "city" => 17497, "email" => 16995, "phone" => 17498, "gaveklub" => 16996, "country" => "Danmark", "lang_code" => "da", "lang_num" => 1),
        2962 => array("valuealias" => "L6-", "name" => 16999, "address1" => 17484, "address2" => 17485, "zip" => 17486, "city" => 17487, "email" => 17000, "phone" => 17488, "gaveklub" => 17001, "country" => "Danmark", "lang_code" => "da", "lang_num" => 1),
        2963 => array("valuealias" => "L8-", "name" => 17004, "address1" => 17489, "address2" => 17490, "zip" => 17491, "city" => 17492, "email" => 17005, "phone" => 17493, "gaveklub" => 17006, "country" => "Danmark", "lang_code" => "da", "lang_num" => 1),
        2999 => array("valuealias" => "L2-", "name" => 17454, "address1" => 17499, "address2" => 17500, "zip" => 17502, "city" => 17482, "email" => 17455, "phone" => 17503, "gaveklub" => 17456, "country" => "Danmark", "lang_code" => "da", "lang_num" => 1),
        9321 => array("valuealias" => "GULD1400-", "name" => 0, "address1" => 0, "address2" => 0, "zip" => 0, "city" => 0, "email" => 0, "phone" => 0, "gaveklub" => 0, "country" => "Danmark", "lang_code" => "da", "lang_num" => 1),

        4793 => array("valuealias" => "S3-", "name" => 27958, "address1" => 28558, "address2" => 28559, "zip" => 28560, "city" => 28561, "email" => 27959, "phone" => 28562, "gaveklub" => 27960, "country" => "Sverige", "lang_code" => "se", "lang_num" => 5),
        1832 => array("valuealias" => "S4-", "name" => 10085, "address1" => 10747, "address2" => 10748, "zip" => 10749, "city" => 10750, "email" => 10086, "phone" => 11667, "gaveklub" => 16132, "country" => "Sverige", "lang_code" => "se", "lang_num" => 5),
        5117 => array("valuealias" => "S6-", "name" => 29576, "address1" => 29589, "address2" => 29590, "zip" => 29591, "city" => 29592, "email" => 29577, "phone" => 29593, "gaveklub" => 29578, "country" => "Sverige", "lang_code" => "se", "lang_num" => 5),
        1981 => array("valuealias" => "S8-", "name" => 11057, "address1" => 11668, "address2" => 11669, "zip" => 11670, "city" => 11671, "email" => 11058, "phone" => 11672, "gaveklub" => 16133, "country" => "Sverige", "lang_code" => "se", "lang_num" => 5),
        2558 => array("valuealias" => "S12-", "name" => 14457, "address1" => 16275, "address2" => 16276, "zip" => 16277, "city" => 16278, "email" => 14458, "phone" => 16872, "gaveklub" => 14459, "country" => "Sverige", "lang_code" => "se", "lang_num" => 5),
        9495 => array("valuealias" => "S4AI-", "name" => 54819, "address1" => 54822, "address2" => 54823, "zip" => 54824, "city" => 54825, "email" => 54820, "phone" => 54826, "gaveklub" => 54821, "country" => "Sverige", "lang_code" => "se", "lang_num" => 5),

        2549 => array("valuealias" => 9, "name" => 14410, "address1" => 16862, "address2" => 16863, "zip" => 16864, "city" => 16865, "email" => 14411, "phone" => 16866, "gaveklub" => 14412, "country" => "Norge", "lang_code" => "no", "lang_num" => 4),
        2550 => array("valuealias" => 2, "name" => 14415, "address1" => 16867, "address2" => 16868, "zip" => 16869, "city" => 16870, "email" => 14416, "phone" => 16871, "gaveklub" => 14417, "country" => "Norge", "lang_code" => "no", "lang_num" => 4),
        4740 => array("valuealias" => 7, "name" => 27659, "address1" => 27662, "address2" => 27663, "zip" => 27664, "city" => 27665, "email" => 27660, "phone" => 27666, "gaveklub" => 28557, "country" => "Norge", "lang_code" => "no", "lang_num" => 4),
        574 => array("valuealias" => 1, "name" => 2928, "address1" => 10767, "address2" => 10768, "zip" => 10769, "city" => 10770, "email" => 2929, "phone" => 4305, "gaveklub" => 4300, "country" => "Norge", "lang_code" => "no", "lang_num" => 4),
        59 => array("valuealias" => 8, "name" => 727, "address1" => 10763, "address2" => 10764, "zip" => 10765, "city" => 10766, "email" => 728, "phone" => 4304, "gaveklub" => 4299, "country" => "Norge", "lang_code" => "no", "lang_num" => 4),
        58 => array("valuealias" => 6, "name" => 93, "address1" => 10759, "address2" => 10760, "zip" => 10761, "city" => 10762, "email" => 92, "phone" => 4303, "gaveklub" => 4298, "country" => "Norge", "lang_code" => "no", "lang_num" => 4),
        57 => array("valuealias" => 4, "name" => 722, "address1" => 10755, "address2" => 10756, "zip" => 10757, "city" => 10758, "email" => 723, "phone" => 4302, "gaveklub" => 4297, "country" => "Norge", "lang_code" => "no", "lang_num" => 4),
        272 => array("valuealias" => 3, "name" => 1228, "address1" => 10751, "address2" => 10752, "zip" => 10753, "city" => 10754, "email" => 1229, "phone" => 4301, "gaveklub" => 4296, "country" => "Norge", "lang_code" => "no", "lang_num" => 4)
    );


    public function cardSale()
    {
        $today_start = date("Y-m-d") . " 00:00:01";
        $today_end = date("Y-m-d") . " 23:59:59";
        $month_start = date('Y-m-01') . " 00:00:01";
        $month_end = date("Y-m-t", strtotime(date("Y-m-d"))) . " 23:59:59";

        // Beregn "samme dag" for sammenligning
        $same_day_this_year = date("Y-m-d") . " 23:59:59";
        $same_day_last_year = $this->getSameDayLastYear() . " 23:59:59";

        // 2025 data (nuværende år)
        $data_2025_dk = array(
            "total" => $this->getSalePresentYear(1,"","2025-01-01 16:47:14","2025-12-24 23:47:14"),
            "day" => $this->getSalePresentYear(1,"", $today_start, $today_end),
            "month" => $this->getSalePresentYear(1,"", $month_start, $month_end),
            "total_day" => $this->getSalePresentYear(1,"","2025-01-01 16:47:14", $same_day_this_year)
        );

        // 2024 data (forrige år data)
        $data_2024_dk = unserialize(base64_decode($this->getDataFromPreviusYears(1, 2024)));
        // Tidligere års data
        $data_2023_dk = unserialize(base64_decode($this->getDataFromPreviusYears(1, 2023)));
        $data_2022_dk = unserialize(base64_decode($this->getDataFromPreviusYears(1, 2022)));

        $data_2025_no = array(
            "total" => $this->getSalePresentYear(4,"","2025-01-01 16:47:14","2025-12-24 23:47:14"),
            "day" => $this->getSalePresentYear(4,"", $today_start, $today_end),
            "month" => $this->getSalePresentYear(4,"", $month_start, $month_end),
            "total_day" => $this->getSalePresentYear(4,"","2025-01-01 16:47:14", $same_day_this_year)
        );

        $data_2024_no = unserialize(base64_decode($this->getDataFromPreviusYears(4, 2024)));
        $data_2023_no = unserialize(base64_decode($this->getDataFromPreviusYears(4, 2023)));
        $data_2022_no = unserialize(base64_decode($this->getDataFromPreviusYears(4, 2022)));

        $data_2025_se = array(
            "total" => $this->getSalePresentYear(5,"","2025-01-01 16:47:14","2025-12-24 23:47:14"),
            "day" => $this->getSalePresentYear(5,"", $today_start, $today_end),
            "month" => $this->getSalePresentYear(5,"", $month_start, $month_end),
            "total_day" => $this->getSalePresentYear(5,"","2025-01-01 16:47:14", $same_day_this_year)
        );
        $data_2025_se = $this->Handlingse400440Display($data_2025_se);

        $data_2024_se = unserialize(base64_decode($this->getDataFromPreviusYears(5, 2024)));
        $data_2024_se = $this->Handlingse400440Display($data_2024_se);

        $data_2023_se = unserialize(base64_decode($this->getDataFromPreviusYears(5, 2023)));
        $data_2022_se = unserialize(base64_decode($this->getDataFromPreviusYears(5, 2022)));

        $cardTitleHtml = $this->buildTitleCol(1);

        //-------------------------- DANSKE SHOPS ------------------------
        echo "<h1>Danske shops</h1>";

        // 2025 data
        $sortData = $this->buildCol2021($data_2025_dk, "total", 1);
        $total_2025 = $this->buildCol($sortData);
        $total_2025_Html = '<div class="v-flex"><div class="header-title">Antal solgte totalt 2025</div>' . $total_2025 . '</div>';

        $sortData = $this->buildCol2021($data_2025_dk, "day", 1);
        $day_2025 = $this->buildCol($sortData);
        $day_2025_Html = '<div class="v-flex"><div class="header-title">Antal solgte i dag 2025</div>' . $day_2025 . '</div>';

        $sortData = $this->buildCol2021($data_2025_dk, "month", 1);
        $month_2025 = $this->buildCol($sortData);
        $month_2025_Html = '<div class="v-flex"><div class="header-title">Antal solgte indeværende måned 2025</div>' . $month_2025 . '</div>';



        // 2024 data
        $sortData = $this->buildCol2021($data_2024_dk, "total", 1);
        $total_2024 = $this->buildCol($sortData);
        $total_2024_Html = '<div class="v-flex"><div class="header-title">Antal solgte totalt 2024</div>' . $total_2024 . '</div>';

        $sortData = $this->buildCol2021($data_2024_dk, "day", 1);
        $day_2024 = $this->buildCol($sortData);
        $day_2024_Html = '<div class="v-flex"><div class="header-title">Antal solgte i dag 2024</div>' . $day_2024 . '</div>';

        $sortData = $this->buildCol2021($data_2024_dk, "month", 1);
        $month_2024 = $this->buildCol($sortData);
        $month_2024_Html = '<div class="v-flex"><div class="header-title">Antal solgte indeværende måned 2024</div>' . $month_2024 . '</div>';

        $sortData = $this->buildCol2021($data_2024_dk, "total_day", 1);
        $total_day_2024 = $this->buildCol($sortData);
        $total_day_2024_Html = '<div class="v-flex"><div class="header-title">Antal solgte totalt til og med samme dag, 2024</div>' . $total_day_2024 . '</div>';

        // 2023 data
        $sortData = $this->buildCol2021($data_2023_dk, "total", 1);
        $total_2023 = $this->buildCol($sortData);
        $total_2023_Html = '<div class="v-flex"><div class="header-title">Antal solgte totalt 2023</div>' . $total_2023 . '</div>';

        $sortData = $this->buildCol2021($data_2023_dk, "day", 1);
        $day_2023 = $this->buildCol($sortData);
        $day_2023_Html = '<div class="v-flex"><div class="header-title">Antal solgte i dag 2023</div>' . $day_2023 . '</div>';

        $sortData = $this->buildCol2021($data_2023_dk, "month", 1);
        $month_2023 = $this->buildCol($sortData);
        $month_2023_Html = '<div class="v-flex"><div class="header-title">Antal solgte indeværende måned 2023</div>' . $month_2023 . '</div>';

        $sortData = $this->buildCol2021($data_2023_dk, "total_day", 1);
        $total_day_2023 = $this->buildCol($sortData);
        $total_day_2023_Html = '<div class="v-flex"><div class="header-title">Antal solgte totalt til og med samme dag, 2023</div>' . $total_day_2023 . '</div>';

        // 2022 data
        $sortData = $this->buildCol2021($data_2022_dk, "total", 1);
        $total_2022 = $this->buildCol($sortData);
        $total_2022_Html = '<div class="v-flex"><div class="header-title">Antal solgte totalt 2022</div>' . $total_2022 . '</div>';

        $sortData = $this->buildCol2021($data_2022_dk, "day", 1);
        $day_2022 = $this->buildCol($sortData);
        $day_2022_Html = '<div class="v-flex"><div class="header-title">Antal solgte i dag 2022</div>' . $day_2022 . '</div>';

        $sortData = $this->buildCol2021($data_2022_dk, "month", 1);
        $month_2022 = $this->buildCol($sortData);
        $month_2022_Html = '<div class="v-flex"><div class="header-title">Antal solgte indeværende måned 2022</div>' . $month_2022 . '</div>';

        $sortData = $this->buildCol2021($data_2022_dk, "total_day", 1);
        $total_day_2022 = $this->buildCol($sortData);
        $total_day_2022_Html = '<div class="v-flex"><div class="header-title">Antal solgte totalt til og med samme dag, 2022</div>' . $total_day_2022 . '</div>';

        echo '<div class="flex-container">' .
            $cardTitleHtml .
            $total_2025_Html .
            $day_2025_Html.
            $month_2025_Html.


            $total_2024_Html .
            $day_2024_Html.
            $month_2024_Html.
            $total_day_2024_Html.

            $total_2023_Html.
            $day_2023_Html.
            $month_2023_Html.
            $total_day_2023_Html.

            $total_2022_Html.
            $day_2022_Html.
            $month_2022_Html.
            $total_day_2022_Html.

            '</div>';
        echo "<br><hr>";

        //-------------------------- NORSKE SHOPS ------------------------
        $cardTitleHtml = $this->buildTitleCol(4);

        echo "<h1>Norske shops</h1>";

        // 2025 NO data
        $sortData = $this->buildCol2021($data_2025_no, "total", 4);
        $total_2025_no = $this->buildCol($sortData);
        $total_2025_Html_no = '<div class="v-flex"><div class="header-title">Antal solgte totalt 2025</div>' . $total_2025_no . '</div>';

        $sortData = $this->buildCol2021($data_2025_no, "day", 4);
        $day_2025_no = $this->buildCol($sortData);
        $day_2025_Html_no = '<div class="v-flex"><div class="header-title">Antal solgte i dag 2025</div>' . $day_2025_no . '</div>';

        $sortData = $this->buildCol2021($data_2025_no, "month", 4);
        $month_2025_no = $this->buildCol($sortData);
        $month_2025_Html_no = '<div class="v-flex"><div class="header-title">Antal solgte indeværende måned 2025</div>' . $month_2025_no . '</div>';



        // 2024 NO data
        $sortData = $this->buildCol2021($data_2024_no, "total", 4);
        $total_2024_no = $this->buildCol($sortData);
        $total_2024_Html_no = '<div class="v-flex"><div class="header-title">Antal solgte totalt 2024</div>' . $total_2024_no . '</div>';

        $sortData = $this->buildCol2021($data_2024_no, "day", 4);
        $day_2024_no = $this->buildCol($sortData);
        $day_2024_Html_no = '<div class="v-flex"><div class="header-title">Antal solgte i dag 2024</div>' . $day_2024_no . '</div>';

        $sortData = $this->buildCol2021($data_2024_no, "month", 4);
        $month_2024_no = $this->buildCol($sortData);
        $month_2024_Html_no = '<div class="v-flex"><div class="header-title">Antal solgte indeværende måned 2024</div>' . $month_2024_no . '</div>';

        $sortData = $this->buildCol2021($data_2024_no, "total_day", 4);
        $total_day_2024_no = $this->buildCol($sortData);
        $total_day_2024_Html_no = '<div class="v-flex"><div class="header-title">Antal solgte totalt til og med samme dag, 2024</div>' . $total_day_2024_no . '</div>';

        // 2023 NO data
        $sortData = $this->buildCol2021($data_2023_no, "total", 4);
        $total_2023_no = $this->buildCol($sortData);
        $total_2023_Html_no = '<div class="v-flex"><div class="header-title">Antal solgte totalt 2023</div>' . $total_2023_no . '</div>';

        $sortData = $this->buildCol2021($data_2023_no, "day", 4);
        $day_2023_no = $this->buildCol($sortData);
        $day_2023_Html_no = '<div class="v-flex"><div class="header-title">Antal solgte i dag 2023</div>' . $day_2023_no . '</div>';

        $sortData = $this->buildCol2021($data_2023_no, "month", 4);
        $month_2023_no = $this->buildCol($sortData);
        $month_2023_Html_no = '<div class="v-flex"><div class="header-title">Antal solgte indeværende måned 2023</div>' . $month_2023_no . '</div>';

        $sortData = $this->buildCol2021($data_2023_no, "total_day", 4);
        $total_day_2023_no = $this->buildCol($sortData);
        $total_day_2023_Html_no = '<div class="v-flex"><div class="header-title">Antal solgte totalt til og med samme dag, 2023</div>' . $total_day_2023_no . '</div>';

        // 2022 NO data
        $sortData = $this->buildCol2021($data_2022_no, "total", 4);
        $total_2022_no = $this->buildCol($sortData);
        $total_2022_Html_no = '<div class="v-flex"><div class="header-title">Antal solgte totalt 2022</div>' . $total_2022_no . '</div>';

        $sortData = $this->buildCol2021($data_2022_no, "day", 4);
        $day_2022_no = $this->buildCol($sortData);
        $day_2022_Html_no = '<div class="v-flex"><div class="header-title">Antal solgte i dag 2022</div>' . $day_2022_no . '</div>';

        $sortData = $this->buildCol2021($data_2022_no, "month", 4);
        $month_2022_no = $this->buildCol($sortData);
        $month_2022_Html_no = '<div class="v-flex"><div class="header-title">Antal solgte indeværende måned 2022</div>' . $month_2022_no . '</div>';

        $sortData = $this->buildCol2021($data_2022_no, "total_day", 4);
        $total_day_2022_no = $this->buildCol($sortData);
        $total_day_2022_Html_no = '<div class="v-flex"><div class="header-title">Antal solgte totalt til og med samme dag, 2022</div>' . $total_day_2022_no . '</div>';

        echo '<div class="flex-container">' .
            $cardTitleHtml .
            $total_2025_Html_no .
            $day_2025_Html_no .
            $month_2025_Html_no .


            $total_2024_Html_no .
            $day_2024_Html_no .
            $month_2024_Html_no .
            $total_day_2024_Html_no .

            $total_2023_Html_no.
            $day_2023_Html_no.
            $month_2023_Html_no.
            $total_day_2023_Html_no.

            $total_2022_Html_no.
            $day_2022_Html_no.
            $month_2022_Html_no.
            $total_day_2022_Html_no.

            '</div>';
        echo "<br><hr>";

        //-------------------------- SVENSKE SHOPS ------------------------
        $cardTitleHtml = $this->buildTitleCol(5);

        echo "<h1>Svenske shops</h1>";

        // 2025 SE data
        $sortData = $this->buildCol2021($data_2025_se, "total", 5);
        $total_2025_se = $this->buildCol($sortData);
        $total_2025_Html_se = '<div class="v-flex"><div class="header-title">Antal solgte totalt 2025</div>' . $total_2025_se . '</div>';

        $sortData = $this->buildCol2021($data_2025_se, "day", 5);
        $day_2025_se = $this->buildCol($sortData);
        $day_2025_Html_se = '<div class="v-flex"><div class="header-title">Antal solgte i dag 2025</div>' . $day_2025_se . '</div>';

        $sortData = $this->buildCol2021($data_2025_se, "month", 5);
        $month_2025_se = $this->buildCol($sortData);
        $month_2025_Html_se = '<div class="v-flex"><div class="header-title">Antal solgte indeværende måned 2025</div>' . $month_2025_se . '</div>';



        // 2024 SE data
        $sortData = $this->buildCol2021($data_2024_se, "total", 5);
        $total_2024_se = $this->buildCol($sortData);
        $total_2024_Html_se = '<div class="v-flex"><div class="header-title">Antal solgte totalt 2024</div>' . $total_2024_se . '</div>';

        $sortData = $this->buildCol2021($data_2024_se, "day", 5);
        $day_2024_se = $this->buildCol($sortData);
        $day_2024_Html_se = '<div class="v-flex"><div class="header-title">Antal solgte i dag 2024</div>' . $day_2024_se . '</div>';

        $sortData = $this->buildCol2021($data_2024_se, "month", 5);
        $month_2024_se = $this->buildCol($sortData);
        $month_2024_Html_se = '<div class="v-flex"><div class="header-title">Antal solgte indeværende måned 2024</div>' . $month_2024_se . '</div>';

        $sortData = $this->buildCol2021($data_2024_se, "total_day", 5);
        $total_day_2024_se = $this->buildCol($sortData);
        $total_day_2024_Html_se = '<div class="v-flex"><div class="header-title">Antal solgte totalt til og med samme dag, 2024</div>' . $total_day_2024_se . '</div>';

        // 2023 SE data
        $sortData = $this->buildCol2021($data_2023_se, "total", 5);
        $total_2023_se = $this->buildCol($sortData);
        $total_2023_Html_se = '<div class="v-flex"><div class="header-title">Antal solgte totalt 2023</div>' . $total_2023_se . '</div>';

        $sortData = $this->buildCol2021($data_2023_se, "day", 5);
        $day_2023_se = $this->buildCol($sortData);
        $day_2023_Html_se = '<div class="v-flex"><div class="header-title">Antal solgte i dag 2023</div>' . $day_2023_se . '</div>';

        $sortData = $this->buildCol2021($data_2023_se, "month", 5);
        $month_2023_se = $this->buildCol($sortData);
        $month_2023_Html_se = '<div class="v-flex"><div class="header-title">Antal solgte indeværende måned 2023</div>' . $month_2023_se . '</div>';

        $sortData = $this->buildCol2021($data_2023_se, "total_day", 5);
        $total_day_2023_se = $this->buildCol($sortData);
        $total_day_2023_Html_se = '<div class="v-flex"><div class="header-title">Antal solgte totalt til og med samme dag, 2023</div>' . $total_day_2023_se . '</div>';

        // 2022 SE data
        $sortData = $this->buildCol2021($data_2022_se, "total", 5);
        $total_2022_se = $this->buildCol($sortData);
        $total_2022_Html_se = '<div class="v-flex"><div class="header-title">Antal solgte totalt 2022</div>' . $total_2022_se . '</div>';

        $sortData = $this->buildCol2021($data_2022_se, "day", 5);
        $day_2022_se = $this->buildCol($sortData);
        $day_2022_Html_se = '<div class="v-flex"><div class="header-title">Antal solgte i dag 2022</div>' . $day_2022_se . '</div>';

        $sortData = $this->buildCol2021($data_2022_se, "month", 5);
        $month_2022_se = $this->buildCol($sortData);
        $month_2022_Html_se = '<div class="v-flex"><div class="header-title">Antal solgte indeværende måned 2022</div>' . $month_2022_se . '</div>';

        $sortData = $this->buildCol2021($data_2022_se, "total_day", 5);
        $total_day_2022_se = $this->buildCol($sortData);
        $total_day_2022_Html_se = '<div class="v-flex"><div class="header-title">Antal solgte totalt til og med samme dag, 2022</div>' . $total_day_2022_se . '</div>';

        echo '<div class="flex-container">' .
            $cardTitleHtml .
            $total_2025_Html_se .
            $day_2025_Html_se.
            $month_2025_Html_se.


            $total_2024_Html_se .
            $day_2024_Html_se.
            $month_2024_Html_se.
            $total_day_2024_Html_se.

            $total_2023_Html_se.
            $day_2023_Html_se.
            $month_2023_Html_se.
            $total_day_2023_Html_se.

            $total_2022_Html_se.
            $day_2022_Html_se .
            $month_2022_Html_se .
            $total_day_2022_Html_se .

            '</div>';
        echo "<br><hr>";
    }

    // Ny hjælpemetode til at beregne samme dag sidste år
    private function getSameDayLastYear()
    {
        $today = new DateTime();
        $lastYear = clone $today;
        $lastYear->modify('-1 year');
        return $lastYear->format('Y-m-d');
    }

    // split up 400 in 400 and 440
    private function Handlingse400440Display($data)
    {
        foreach ($data as $key => &$section) {
            // Gennemgå hvert element i sektionen
            foreach ($section as &$item) {
                // Tjek først om card_values ikke er null og indeholder '440'
                if (!is_null($item['card_values']) && $item['card_values'] !== '' && strpos($item['card_values'], '440') !== false) {
                    // Modificer shop_id ved at tilføje '_440'
                    $item['shop_id'] = $item['shop_id'] . '440';
                }
            }
        }
        return $data;
    }

    public function getDataFromPreviusYears($lang, $year = 2022)
    {
        $url = 'https://system.gavefabrikken.dk/gavefabrikken_backend/component/stats2022.php?lang=' . $lang . '&year=' . $year;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_close($curl);
        return curl_exec($curl);
    }

    public function getOldData($lang, $year = 2021)
    {
        $url = 'https://gavefabrikken.dk/gavefabrikken_backend/component/test.php?lang=' . $lang . '&year=' . $year;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_close($curl);
        return curl_exec($curl);
    }

    public function getOldData2020($lang, $year)
    {
        $url = 'https://gavefabrikken.dk/gavefabrikken_backend/component/test2.php?lang=' . $lang . '&year=' . $year;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_close($curl);
        return curl_exec($curl);
    }

    public function buildCol2021($data, $coll, $lang)
    {
        $sortArr = [];
        $langListe = [];
        if ($lang == 1) {
            $langListe = array(7121,52, 4668, 53, 2395, 9321, 54, 55, 56, 290, 310, 575, 4662, 2548, 2961, 2960, 2962, 2963);
        }
        if ($lang == 4) {
            $langListe = array(272, 57, 58, 59, 574, 2550,  4740,2549);
        }
        if ($lang == 5) {
            $langListe = array(4793,1832, 5117,1981, 2558,8271,9495 ); // 1832440
        }

        foreach ($langListe as $ele) {
            $sortArr[] = array(
                "antal" => $this->findDataOnShopID($data[$coll], $ele),
                "concept_code" => "con",
                "shop_id" => $ele
            );
        }
        return $sortArr;
    }

    public function findDataOnShopID($data, $shopID)
    {
        $return = 0;
        foreach ($data as $ele) {
            if ($ele["shop_id"] == $shopID) {
                $return = $ele["antal"];
            }
        }
        return $return;
    }

    public function buildCol($list)
    {
        $returnData["sum"] = 0;
        $returnData["html"] = "";
        foreach ($list as $ele) {
            $ele['antal'] = $ele['antal'] == "" ? "0" : $ele['antal'];
            $returnData["sum"] += $ele['antal'] * 1;
            $returnData["html"] .= "<div class='" . $ele['concept_code'] . "'>" . $ele['antal'] . "</div>";
        }
        $returnData["html"] .= "<div class='total'><b>" . $returnData["sum"] . "</b></div>";

        return $returnData["html"];
    }

    public function buildTitleCol($lang)
    {
        $returnHtml = "";
        $sql = "SELECT * FROM `cardshop_settings` WHERE `language_code` =" . $lang;
        $rs = Dbsqli::getSql2($sql);

        $sortArr = [];
        $langListe = [];
        if ($lang == 1) {
            $langListe = array(7121,52, 4668, 53, 2395, 9321, 54, 55, 56, 290, 310, 575, 4662, 2548, 2961, 2960, 2962, 2963);
        }
        if ($lang == 4) {
            $langListe = array(272, 57, 58, 59, 574, 2550,  4740,2549);
        }
        if ($lang == 5) {
            $langListe = array(4793,1832, 5117,1981, 2558,8271,9495 ); // 1832440
        }

        foreach ($langListe as $ele) {
            if($ele == 1832440){
                $sortArr[] = array( "concept_code" => "SE-440");
                continue;
            }

            $sortArr[] = array(
                "concept_code" => $this->findConceptCode($rs, $ele)
            );
        }

        foreach ($sortArr as $ele) {
            $ele['concept_code'] = $ele['concept_code'] == "2558" ? "SE-1200 (udgået)" :  $ele['concept_code'] ;
            $returnHtml .= "<div class='consept'>" . $ele['concept_code'] . "</div>";
        }
        $returnHtml .= "<div ><b>TOTAL</b></div>";
        return '<div class="v-flex"><div class="header-title">Shop</div>' . $returnHtml . '</div>';
    }

    public function findConceptCode($data, $shopID)
    {
        $return = $shopID;
        foreach ($data as $ele) {
            if ($ele["shop_id"] == $shopID) {
                $return = $ele["concept_code"];
            }
            if ($shopID == 2548) {
                $return = "GRON(Udgået)";
            }
            if ($shopID == 2549) {
                $return = "BRA(Udgået)";
            }
            if ($shopID == 9321) {
                $return = "GULD1400";
            }
        }
        return $return;
    }

    public function getSalePresentYear($lang,$dbprefix, $start, $slut)
    {
        // kun se pga 400/440 budget
        $seSql = $lang == 5 ? " ,shop_user.card_values ": "";

        $sql = $query = "SELECT
    c.card_values,
    language_code,
    {$dbprefix}cardshop_settings.concept_code,
    IFNULL(c.antal, 0) as antal,
    `{$dbprefix}cardshop_settings`.shop_id 
FROM
    `{$dbprefix}cardshop_settings`
LEFT JOIN(
        SELECT {$dbprefix}shop_user.shop_id, {$dbprefix}shop_user.card_values,
        COUNT({$dbprefix}shop_user.id) AS antal
    FROM
        {$dbprefix}shop_user
    LEFT JOIN {$dbprefix}company_order ON {$dbprefix}shop_user.company_order_id = {$dbprefix}company_order.id
    WHERE
        {$dbprefix}company_order.`created_datetime` > '" . $start . "' 
        AND {$dbprefix}company_order.`created_datetime` <= '" . $slut . "'  
        AND `salesperson` NOT LIKE ('%us%') 
        AND {$dbprefix}company_order.order_state NOT IN(7,8,20)  
        AND {$dbprefix}company_order.is_cancelled = 0  
        AND company_order_id NOT IN( 
            SELECT id 
            FROM {$dbprefix}company_order 
            WHERE company_id IN(44780,44794,44795,45363,45364,45365,52468,52469,52470,44780,
                    44794,
                    44795,
                    45363,
                    45364,
                    45365,
                    45363,
                    45363,
                    45363,
                    45363,
                    52468,
                    52468,
                    52468,
                    52468,
                    52468,
                    45364,
                    45364,
                    45364,
                    45364,
                    52468,
52468,
52468,
52468,
52468,
44780,
44780,
44780,
44780
                    
                    )
        )
        AND {$dbprefix}shop_user.is_demo = 0 
        AND {$dbprefix}shop_user.blocked = 0 
        AND {$dbprefix}shop_user.shutdown = 0
        
    GROUP BY
        {$dbprefix}shop_user.shop_id {$seSql}
) AS c
ON
    `{$dbprefix}cardshop_settings`.shop_id = c.shop_id
WHERE 
    language_code = '$lang'
ORDER BY
    language_code,
    {$dbprefix}cardshop_settings.show_index";;

        return Dbsqli::getSql2($sql);
    }

    public function test()
    {
        echo "hej";
    }

    public function loginStats()
    {
        $totalLogin = 0;
        $totalGavevalg = 0;
        $totalRequest = 0;
        echo "<script>setTimeout(function() {
  location.reload();
}, 10000); </script>";
        if ($_GET["token"] == "saddsfsdflkfj489fyth") {

            echo "<h3>CPU in percent of cores used (5 min avg):<u> " . $this->get_server_cpu_usage() . "</u></h3>";

            $sql = "select day(`created_datetime`) as Day, hour(`created_datetime`) as Hour, count(*) as Count  FROM `system_log` WHERE `controller` LIKE 'login' AND `action` LIKE 'loginShopUserByToken' AND `created_datetime` > SUBDATE(NOW(),1) group by day(created_datetime), hour(created_datetime) ORDER BY `id`  DESC ";
            $rs = Dbsqli::getSql2($sql);
            $sql2 = "select day(`order_timestamp`) as Day, hour(`order_timestamp`) as Hour, count(*) as Count  FROM `order` WHERE  `order_timestamp` > SUBDATE(NOW(),1) group by day(`order_timestamp`), hour(`order_timestamp`) ORDER BY `id`  DESC";
            $rs2 = Dbsqli::getSql2($sql2);
            $sql3 = "select day(`created_datetime`) as Day, hour(`created_datetime`) as Hour, count(*) as Count  FROM `system_log` WHERE `created_datetime` > SUBDATE(NOW(),1) AND `action` not LIKE 'loginStats' group by day(created_datetime), hour(created_datetime) ORDER BY `id`  DESC ";
            $rs3 = Dbsqli::getSql2($sql3);
            echo "<table border=1 width=100% style='font-size:2vh;'><tr><th>Dag</th><th>Time</th><th>Login</th><th>Gavevalg</th><th>Request</th></tr>";
            for ($i = 0; $i < sizeofgf($rs); $i++) {


                $rs2[$i]["Count"] = isset($rs2[$i]["Count"]) ? $rs2[$i]["Count"] : 0;
                $rs3[$i]["Count"] = isset($rs3[$i]["Count"]) ? $rs3[$i]["Count"] : 0;

                echo "<tr><td>" . $rs[$i]["Day"] . "</td><td>" . $rs[$i]["Hour"] . "</td><td>" . $rs[$i]["Count"] . "</td><td>" . $rs2[$i]["Count"] . "</td><td>" . $rs3[$i]["Count"] . "</td></tr>";
                $totalLogin += $rs[$i]["Count"] * 1;
                $totalGavevalg += $rs2[$i]["Count"] * 1;
                $totalRequest += $rs3[$i]["Count"] * 1;
            }
            echo "<tr><td>TOTAL</td><td></td><td><b>" . $totalLogin . "</b></td><td><b>" . $totalGavevalg . "</b></td><td><b>" . $totalRequest . "</b></td></tr>";
            echo "</table>";
        }
    }

    public function loginStatsDev()
    {
        $totalLogin = 0;
        $totalGavevalg = 0;
        $totalRequest = 0;
        $totalLoginError = 0;
        $totalError = 0;
        echo "<script>setTimeout(function() {
  location.reload();
}, 600000); </script>";
        if ($_GET["token"] == "saddsfsdflkfj489fyth") {

            echo "<h3>CPU in percent of cores used (5 min avg):<u> " . $this->get_server_cpu_usage() . "</u></h3>";

            $sql = "select day(`created_datetime`) as Day, hour(`created_datetime`) as Hour, count(*) as Count  FROM `system_log` WHERE `controller` LIKE 'login' AND `action` LIKE 'loginShopUserByToken' AND `created_datetime` > SUBDATE(NOW(),1) group by day(created_datetime), hour(created_datetime) ORDER BY `id`  DESC ";
            $rs = Dbsqli::getSql2($sql);
            $sql2 = "select day(`order_timestamp`) as Day, hour(`order_timestamp`) as Hour, count(*) as Count  FROM `order` WHERE  `order_timestamp` > SUBDATE(NOW(),1) group by day(`order_timestamp`), hour(`order_timestamp`) ORDER BY `id`  DESC";
            $rs2 = Dbsqli::getSql2($sql2);
            $sql3 = "select day(`created_datetime`) as Day, hour(`created_datetime`) as Hour, count(*) as Count  FROM `system_log` WHERE `created_datetime` > SUBDATE(NOW(),1) AND `action` not LIKE 'loginStats' group by day(created_datetime), hour(created_datetime) ORDER BY `id`  DESC ";
            $rs3 = Dbsqli::getSql2($sql3);
            $sql4 = "select day(`created_datetime`) as Day, hour(`created_datetime`) as Hour, count(*) as Count  FROM `system_log` WHERE `controller` LIKE 'login' AND `action` LIKE 'loginShopUserByToken' and committed = 0 AND `created_datetime` > SUBDATE(NOW(),1) group by day(created_datetime), hour(created_datetime) ORDER BY `id`  DESC ";
            $rs4 = Dbsqli::getSql2($sql4);
            $sql5 = "select day(`created_datetime`) as Day, hour(`created_datetime`) as Hour, count(*) as Count  FROM `system_log` WHERE `created_datetime` > SUBDATE(NOW(),1) AND `action` not LIKE 'loginStats' and committed = 0  group by day(created_datetime), hour(created_datetime) ORDER BY `id`  DESC ";
            $rs5 = Dbsqli::getSql2($sql5);


            echo "<table border=1 width=100% style='font-size:2vh;'><tr><th>Dag</th><th>Time</th><th>Login</th><th>Gavevalg</th><th>Request</th><th>Login error</th><th>Error</th></tr>";
            for ($i = 0; $i < sizeofgf($rs); $i++) {
                $LoginError = 0;
                $error = 0;
                try {
                    $LoginError = $rs4[$i]["Count"];
                } catch (Exception $e) {
                }
                try {
                    $error = $rs5[$i]["Count"];
                } catch (Exception $e) {
                }


                echo "<tr><td>" . $rs[$i]["Day"] . "</td><td>" . $rs[$i]["Hour"] . "</td><td>" . $rs[$i]["Count"] . "</td><td>" . $rs2[$i]["Count"] . "</td><td>" . $rs3[$i]["Count"] . "</td><td>" . $LoginError . "</td><td>" . $error . "</td></tr>";
                $totalLogin += $rs[$i]["Count"] * 1;
                $totalGavevalg += $rs2[$i]["Count"] * 1;
                $totalRequest += $rs3[$i]["Count"] * 1;
                $totalLoginError += $LoginError * 1;
                $totalError += $error * 1;
            }
            echo "<tr><td>TOTAL</td><td></td><td><b>" . $totalLogin . "</b></td><td><b>" . $totalGavevalg . "</b></td><td><b>" . $totalRequest . "</b></td><td><b>" . $totalLoginError . "</b></td><td><b>" . $totalError . "</b></td></tr>";
            echo "</table>";
        }
    }

    private function get_server_cpu_usage()
    {
        $exec_loads = sys_getloadavg();
        $exec_cores = trimgf(shell_exec("grep -P '^processor' /proc/cpuinfo|wc -l"));
        return round($exec_loads[1] / ($exec_cores + 1) * 100, 0) . '%';
    }

    public function saleStats()
    {
        if ($_GET["token"] == "saddsfsdflkfj489fyth") {
            $sql = "select day(`created_datetime`) as Day, hour(`created_datetime`) as Hour, count(*) as Count  FROM `system_log` WHERE `controller` LIKE 'login' AND `action` LIKE 'loginShopUserByToken' AND `created_datetime` > SUBDATE(NOW(),1) group by day(created_datetime), hour(created_datetime) ORDER BY `id`  DESC";
            $rs = Dbsqli::getSql2($sql);
            echo "<table border=1>";
            foreach ($rs as $ele) {
                echo "<tr><td>" . $ele["Day"] . "</td><td>" . $ele["Hour"] . "</td><td>" . $ele["Count"] . "</td></tr>";
            }
            echo "</table>";
        }
    }

    public function totalCard()
    {
        $sqlNotSelect = "SELECT COUNT(*) as antal  FROM `shop_user` WHERE `shop_id`  in (  select shop_id from `cardshop_settings`  ) AND blocked = 0 ";
        $rsNotSelect = Dbsqli::getSql2($sqlNotSelect);
        echo $rsNotSelect[0]["antal"];
    }

    public function getCsvFile()
    {
        if($_GET["alias"] == 1){
            $this->loadAliasStats($_GET["deadline"], $_GET["shop_id"], true);
        } else {
            $this->loadStats($_GET["deadline"], $_GET["shop_id"], true);
        }
    }

    public function getAllStats()
    {
        $dg2024 = [

            "52" => "50.63%",
            "54" => "42.39%",
            "55" => "50.5%",
            "56" => "52.99%",
            "53" => "47.06%",
            "290" => "52.85 %",
            "310" => "54.51 %",
            "575" => "48.42 %",
            "574" => "52.19 %",
            "272" => "	52.25 %",
            "57" => "	53.89 %",
            "58" => "55.28 %",
            "59" => "51.59 %",
            "1832" =>"45.83 %",
            "1981" => "	42.54 %",
            "4662" => "46.33 %",
            "4668" => "48.02%",
            "2395" => "49.63%",
            "2550" => "51.28 %",
            "4740" => "58.19 %",
            "4793" => "46.97 %",
            "5117" => "	38.38 %",
            "7121" => "31.77%",
            "7376" => "N/A",
            "8271" => "N/A",
            "9495" =>"N/A",

        ];

        $dg2023 = [
            "52" => "49.57%",
            "54" => "46.02%",
            "55" => "51.67%",
            "56" => "49.8%",
            "53" => "47.26%",
            "290" => "52.74%",
            "310" => "52.74%",
            "575" => "45.83%",
            "574" => "55.75%",
            "272" => "47.47%",
            "57" => "51.62%",
            "58" => "51.78%",
            "59" => "50.59%",
            "1832" => "45.91%",
            "1981" => "48.21%",
            "4662" => "44.09%",
            "4668" => "48.13%",
            "2395" => "49.84%",
            "2550" => "53.19%",
            "4740" => "61.42%",
            "4793" => "44.42%",
            "5117" => "45.71%",
            "7376" => "0%",
            "9495" =>"N/A",
            "8271"=>"N/A",
        ];

        $dg2022 = [
            "52" => "	49.06%",
            "54" => "46.1%",
            "55" => "49.19%",
            "56" => "46.36%",
            "53" => "44.03%",
            "290" => "49.66%",
            "310" => "53.93%",
            "575" => "43.43%",
            "574" => "60.81%",
            "272" => "59.27%",
            "57" => "59.04%",
            "58" => "49.06%",
            "59" => "61.7%",
            "1832" => "51.96%",
            "1981" => "49.27%",
            "7376" => "0%",
            "9495" =>"N/A",
            "8271"=>"N/A",
        ];


        $shopId = $_POST["shop_id"];

        // Check if the key exists in the $dg2022 array
        $result2022 = isset($dg2022[$shopId]) ? $dg2022[$shopId]:"ingen data";
        $result2023 = isset($dg2023[$shopId]) ? $dg2023[$shopId]:"ingen data";
        $result2024 = isset($dg2024[$shopId]) ? $dg2024[$shopId]:"ingen data";

        if ($_POST["shop_id"] == "0") {
            $this->loadStats_org($_POST["deadline"], $_POST["shop_id"]);
        } else {
            if ($_POST["alias"] == 0) {
                echo "<label><b>Total DG 2024: " . $result2024 . "</b></label><br>";
                echo "<label><b>Total DG 2023: " . $result2023 . "</b></label><br>";
                echo "<label><b>Total DG 2022: " . $result2022 . "</b></label><br>";

                $this->loadStats($_POST["deadline"], $_POST["shop_id"]);
            } else {
                $this->loadAliasStats($_POST["deadline"], $_POST["shop_id"]);
            }
        }
    }

    public function loadAliasStats($deadline, $shopID,$returnCsv=false)
    {
        if($deadline == "alle"){
            $deadline = "";
        }  else {
            $deadline = " and `expire_date` = '" . $deadline . "'";
        }

        $sql = "
SELECT 
    present.id, 
    present_model.active as model_active ,
    sp.active as shop_present_active, 
    present_model.fullalias, 
    present_model.model_present_no,
    present_model.model_no,
    present_model.model_name,
    IFNULL(order_counts.order_antal, 0) as order_antal,
    quantity 
FROM 
    present_model
  LEFT JOIN 
    (SELECT 
        present_model_id, 
        COUNT(id) as order_antal 
     FROM 
        `order` 
     WHERE 
        shopuser_id IN (SELECT id from shop_user WHERE shop_id = ". $shopID." and `blocked` = 0 and `shutdown` = 0  " . $deadline . " )
     GROUP BY 
        present_model_id) AS order_counts 
    ON order_counts.present_model_id = present_model.model_id
        
 JOIN 
    present 
    ON present_model.present_id = present.id

LEFT JOIN 
    present_reservation 
    ON present_reservation.model_id = present_model.model_id 
    AND present_reservation.shop_id = present.shop_id
LEFT JOIN 
    shop_present sp
    ON sp.present_id = present_model.present_id
    AND sp.shop_id = present.shop_id

WHERE 
    present_model.language_id = 1 
    AND present.shop_id = ". $shopID ."
GROUP BY 
    present_model.model_id  
ORDER BY 
    order_antal ASC";

        $rs = Dbsqli::getSql2($sql);
        $radomId = $this->generateRandomString();
        $html = "<table style='display:none' id='" . $radomId . "'>  <thead><tr><th>Varenr</th><th>Alias</th><th>Aktiv</th><th>Gave</th><th>Model</th><th>Antal valgte gaver</th><th>Reserverede</th></thead></tr>  <tbody>";
        foreach ($rs as $dataRow) {
            $presentActive =  $dataRow["model_active"] == 0 ? "Aktiv":"Ikke aktiv";
            if($dataRow["shop_present_active"] == 0 && $presentActive == "Aktiv"){
                $presentActive = "Hele gave lukket";
            }

            $prefix = "";
            $fullalias = $dataRow["fullalias"];

            $antalTal = preg_match_all('/\d/', $fullalias);

            // Hvis der er mindre end to tal, tilføj 0'er i starten
            if ($antalTal < 2) {
                $mangler = 2 - $antalTal;
                $fullalias = str_repeat("0", $mangler) . $fullalias;
            }
            $prefix = self::$metadata[$shopID]["valuealias"];

            $html .= "<tr>
                <td>" . $dataRow["model_present_no"] . "</td>
                <td>" . $prefix.$fullalias . "</td>
                <td>" . $presentActive. "</td>
                <td>" . $dataRow["model_name"] . "</td>
                <td>" . $dataRow["model_no"] . "</td>
                <td>" . $dataRow["order_antal"] . "</td>
                <td>" . $dataRow["quantity"] . "</td>
            </tr>";
            $csv[] = [$dataRow["model_present_no"],$prefix.$fullalias, $presentActive,utf8_decode($dataRow["model_name"]), utf8_decode($dataRow["model_no"]), $dataRow["order_antal"], $dataRow["quantity"] ];
        }

        $html .= "  </tbody></table>";
        $html .= "<script>setTimeout(function(){  $('#" . $radomId . "').DataTable({ 'pageLength': 500 });  $('#" . $radomId . "').fadeIn(400)   }, 400) </script>";

        if ($returnCsv) $this->array_to_csv_download($csv, $filename = "export.csv", $delimiter = ";");
        else echo $html;
    }

    public function getDeadlines()
    {
        $shopID = $_POST["shop_id"];
        $shopID = $shopID ==  "1832_440" ? "1832":$shopID;
        $deadlinesRs = Dbsqli::getSql2("SELECT DISTINCT expire_date,expire_date.`week_no` FROM expire_date WHERE expire_date in ( select distinct expire_date  from shop_user where shop_id = " . $shopID . ")  order by expire_date");

        $html = '<select id="deadline" onchange="newDeadline() ">  <option value="alle">alle</option> ';
        foreach ($deadlinesRs as $deadline) {
            $html .= '<option value="' . $deadline["expire_date"] . '">' . $deadline["expire_date"] . ', uge' . $deadline["week_no"] . '</option>';
        }
        echo $html .= ' </select>';
    }


    public function loadStats($deadline,$shopId,$returnCsv=false)
    {
        $cardValueConditions = [
            '1832' => "HAVING `card_values` = '400'",
            '1832_440' => "HAVING `card_values` = '400,440'"
        ];
        $cardValueConditionsCount = [
            '1832' => " and `card_values` = '400' ",
            '1832_440' => " and `card_values` = '400,440' "
        ];
        $havingSE400440Count = $cardValueConditionsCount[$shopId] ?? '';
        $havingSE400440 = $cardValueConditions[$shopId] ?? '';
        $shopId = $shopId == '1832_440'? 1832 :  $shopId;

        $csv = [];
        $expireDateSql = "";
        if ($deadline != "alle")
        {
            $expireDateSql = "and shop_user.expire_date ='".$deadline."'";
        }

        if ($shopId == "0") {
            $sqlNotSelect = "SELECT COUNT(*) as antal  FROM `shop_user` WHERE 
                                           `shop_id` in (  select shop_id from `cardshop_settings` ) and 
                                           company_order_id not in(SELECT id FROM company_order where is_cancelled = 1 || order_state  IN(7,8,20)   )  AND
                                           company_order_id NOT IN( select id from company_order where company_id in(   44780 ,44794,44795,45363,45364,45365,44780,
                44794,
                44795,
                45363,
                45364,
                45365,
                45363,
                45363,
                45363,
                45363,
                52468,
                52468,
                52468,
                52468,
                52468,
                45364,
                45364,
                45364,
                45364,52468,
52468,
52468,
52468,
52468,
44780,
44780,
44780,
44780
                                                                                                                    )) and 
                                           blocked = 0 and blocked=0 and is_demo = 0 " . $expireDateSql . " order by antal";
        } else {

            $sqlNotSelect = "SELECT COUNT(*) as antal, shop_user.card_values  FROM `shop_user` WHERE 
                                            `shop_id` = " . $shopId . " and 
                                            company_order_id not in(SELECT id FROM company_order where is_cancelled = 1 || order_state  IN(7,8,20)   ) AND 
                                            company_order_id NOT IN( select id from company_order where company_id in(   44780 ,44794,44795,45363,45364,45365,                    44780,
                44794,
                44795,
                45363,
                45364,
                45365,
                45363,
                45363,
                45363,
                45363,
                52468,
                52468,
                52468,
                52468,
                52468,
                45364,
                45364,
                45364,
                45364,
                52468,
52468,
52468,
52468,
52468,
44780,
44780,
44780,
44780
                  )) AND
                                            company_order_id in( select id from company_order where company_order.`created_datetime` > '2025-01-01 16:47:14' AND company_order.`created_datetime` <= '2025-12-24 23:47:14' )  AND
                                            blocked = 0 and blocked = 0  and is_demo = 0 " . $expireDateSql . $havingSE400440Count . " order by antal";
        }

        $rsNotSelect = Dbsqli::getSql2($sqlNotSelect);

        if (sizeofgf($rsNotSelect) <= 0) {
            echo "ingen data";
            return;
        }
        $allCard = $rsNotSelect[0]["antal"];

        $l = 1;
        $noShops = array("272","57","58","59","574","2550","4740");
        if (in_array($shopId, $noShops)) {
            $l = 4;
        }

        if ($shopId == "0") {
            $sql = "
        SELECT present_model.model_name, present_model.model_no, present_model.model_present_no,COUNT(`present_model_id`) as antal, shop_user.card_values FROM `order`
inner JOIN present_model on present_model.model_id = `order`.`present_model_id`
inner JOIN shop_user on shop_user.id = `order`.`shopuser_id`

WHERE `shop_is_gift_certificate` = 1 and present_model.language_id = 1 and
company_order_id not in(SELECT id FROM company_order where is_cancelled = 1 || order_state  IN(7,8,20)   ) and
company_order_id NOT IN( select id from company_order where company_id in(   44780 ,44794,44795,45363,45364,45365,52468,52469,52470,                    44780,
                44794,
                44795,
                45363,
                45364,
                45365,
                45363,
                45363,
                45363,
                45363,
                52468,
                52468,
                52468,
                52468,
                52468,
                45364,
                45364,
                45364,
                45364,
                                                                     52468,
52468,
52468,
52468,
52468,
44780,
44780,
44780,
44780
                                                                     
                                                                     )) and  
shop_user.blocked = 0 AND
shop_user.is_demo = 0 " . $expireDateSql . $havingSE400440. "
GROUP by present_model.model_present_no order by present_model.model_present_no, present_model.model_name,present_model.model_no
        ";

        } else {
            $sql = "
        SELECT present_model.model_name,  present_model.model_no, present_model.model_present_no,COUNT(`present_model_id`) as antal, navision_item.standard_cost,cardshop_settings.card_price,  present_reservation.quantity, present_model.active as model_active ,shop_user.card_values  FROM `order`
inner JOIN present_model on present_model.model_id = `order`.`present_model_id`
inner JOIN shop_user on shop_user.id = `order`.`shopuser_id`
left join navision_item on  present_model.model_present_no = navision_item.no
left join cardshop_settings on `order`.shop_id =  cardshop_settings.shop_id
LEFT join  present_reservation on present_model.model_id = present_reservation.model_id
            
LEFT join  shop_present on present_model.present_id = shop_present.present_id                                     
WHERE
    
    `shop_is_gift_certificate` = 1 and 
      present_model.language_id = 1 and
company_order_id not in(SELECT id FROM company_order where is_cancelled = 1 or order_state IN(7,8,20)   ) and
 (navision_item.language_id = ".$l." or navision_item.language_id IS NULL )  and 
(navision_item.blocked = 0 or navision_item.blocked IS NULL ) and
(shop_user.blocked = 0 or shop_user.blocked IS NULL) AND
company_order_id NOT IN( select id from company_order where company_id in( 44780 ,44794,44795,45363,45364,45365,52468,52469,52470,                    44780,
                44794,
                44795,
                45363,
                45364,
                45365,
                45363,
                45363,
                45363,
                45363,
                52468,
                52468,
                52468,
                52468,
                52468,
                45364,
                45364,
                45364,
                45364,
                  52468,
52468,
52468,
52468,
52468,
44780,
44780,
44780,
44780)) and 
shop_user.is_demo = 0 and
`order`.shop_id = " . $shopId . "
" . $expireDateSql . "
GROUP by present_model.model_present_no ".$havingSE400440." order by present_model.model_present_no, present_model.model_name,present_model.model_no";

        }

        $rs = Dbsqli::getSql2($sql);

        $total = 0;
        $totalProcent = 0;

        $totalDbCalc = 0;
        $countItems = 0;
        $hasZoroStandardPris = false;

        foreach ($rs as $dataRow) {
            $total += $dataRow["antal"] * 1;
        }

        // PROTECTION AGAINST DIVISION BY ZERO
        if ($total == 0) {
            echo "Ingen data tilgængelig - ingen gaver er blevet valgt endnu.";
            return;
        }

        $notSelect = $allCard - $total;
        $radomId = $this->generateRandomString();

        $html = "<div class='statsContent'><p>Antal der mangler at v&oelig;lge: " . $notSelect . "</p>";
        $html .= "<p>Total antal kort: " . $allCard . "</p>";
        $html .= "<table style='display:none' id='" . $radomId . "'>  <thead><tr><th>Varenr</th><th>Aktiv</th><th>Gave</th><th>Model</th><th>Antal valgte gaver</th><th>Reserverede</th><th>Antal i %</th><th>Fremskrevet v&oelig;rdi</th><th>Kost Pris</th><th>DB stk</th><th>DB total</th><th>DB %</th></thead></tr>  <tbody>";

        if(sizeof($rs)==0){
            echo "Ingen data";
            return;
        }

        foreach ($rs as $dataRow) {

            $dataRow["card_price"] = $dataRow["card_price"];

            // SAFE DIVISION: Check that $total is not zero
            $procent = $total > 0 ? ($dataRow["antal"] / $total) * 100 : 0;
            $procent = round($procent, 2);
            $totalProcent += $procent;
            $guess = ($notSelect * $procent) / 100;
            $guess = round($guess) + $dataRow["antal"];

            /* db beregner */

            $seShops = array("1832","1981","2558","4793","5117","8271","9495");
            if (in_array($shopId, $seShops)) {
                $dataRow["card_price"]  = $dataRow["card_price"] * 0.65;

            }

            $salgspris = ($dataRow["card_price"] / 100);
            if($dataRow["standard_cost"] == null){
                $dataRow["standard_cost"] = ($dataRow["card_price"] / 100);

            }
            if ($dataRow["standard_cost"] == 0) {
                $hasZoroStandardPris = true;
                $dataRow["standard_cost"] = $salgspris;
            } else {
                $countItems += $dataRow["antal"];
            }
            $l = 1;

            //Salgspris – standard costpris = dækningsbidrag

            $dbCalcUnit = (($dataRow["card_price"] / 100) - $dataRow["standard_cost"]);
            $dbCalcTotal = round((($dataRow["card_price"] / 100) - $dataRow["standard_cost"]) * $dataRow["antal"]);
            $totalDbCalc += $dbCalcTotal;

            // SAFE DIVISION: Check that card_price is not zero
            $cardPricePerUnit = ($dataRow["card_price"] / 100);
            $dbProcent = $cardPricePerUnit > 0 ? round(($dbCalcUnit * 100) / $cardPricePerUnit, 2) : 0;

            $presentActive =  $dataRow["model_active"] == 0 ? "Aktiv":"Ikke aktiv";

            $html .= "<tr>
        <td>" . $dataRow["model_present_no"] . "</td>
         <td>" . $presentActive. "</td>
        <td>" . $dataRow["model_name"] . "</td>

        <td>" . $dataRow["model_no"] . "</td>
        <td>" . $dataRow["antal"] . "</td>
           <td>" . $dataRow["quantity"] . "</td>
        <td>" . $procent . "</td>
        <td>" . $guess . "</td>
         <td>" . $dataRow["standard_cost"] . "</td>  
          <td>" . $dbCalcUnit . "</td>
        <td>" . $dbCalcTotal . "</td>
        <td>" . $dbProcent . "%</td>        
    </tr>";
            $csv[] = [$dataRow["model_present_no"], $presentActive,utf8_decode($dataRow["model_name"]), utf8_decode($dataRow["model_no"]), $dataRow["antal"], $dataRow["quantity"] , $procent, $guess];
        }

        // SAFE DIVISION: Check that countItems is not zero before calculating totalSale
        $totalSale = $countItems > 0 ? round($salgspris * $countItems) : 0;

        // SAFE DIVISION: Check that totalSale is not zero before calculating percentage
        $totalDBCalcProcent = $totalSale > 0 ? round($totalDbCalc * 100 / $totalSale, 2) : 0;

        $cssRed = $hasZoroStandardPris == true ? "css-red" : "";

        $html .= "  </tbody></table>";
        $html .= "<div class='css-db " . $cssRed . "'><div style='font-size: 10px'>Hvis teksten er rød, skyldes det at der i listen er varer med ej defineret DB </div><table ><tr><td>Total DB</td><td>" . $totalSale . "</td></tr><tr><td>Total DG %</td><td>" . $totalDBCalcProcent . " %</td></tr></table> </div>";
        $html .= "<br><div>Totale antal gaver: <b>" . $total . "</b> (" . $totalProcent . "%)</div></div>";
        $html .= "<script>setTimeout(function(){  $('#" . $radomId . "').DataTable({ 'pageLength': 500 });  $('#" . $radomId . "').fadeIn(400)   }, 400) </script>";

        if ($returnCsv) $this->array_to_csv_download($csv, $filename = "export.csv", $delimiter = ";");
        else echo $html;
    }

















    public function loadStats_v1($deadline,$shopId,$returnCsv=false)
    {
        $cardValueConditions = [
            '1832' => "HAVING `card_values` = '400'",
            '1832_440' => "HAVING `card_values` = '400,440'"
        ];
        $cardValueConditionsCount = [
            '1832' => " and `card_values` = '400' ",
            '1832_440' => " and `card_values` = '400,440' "
        ];
        $havingSE400440Count = $cardValueConditionsCount[$shopId] ?? '';
        $havingSE400440 = $cardValueConditions[$shopId] ?? '';
        $shopId = $shopId == '1832_440'? 1832 :  $shopId;

        $csv = [];
        $expireDateSql = "";
        if ($deadline != "alle")
        {
            $expireDateSql = "and shop_user.expire_date ='".$deadline."'";
        }

        if ($shopId == "0") {
            $sqlNotSelect = "SELECT COUNT(*) as antal  FROM `shop_user` WHERE 
                                               `shop_id` in (  select shop_id from `cardshop_settings` ) and 
                                               company_order_id not in(SELECT id FROM company_order where is_cancelled = 1 || order_state  IN(7,8,20)   )  AND
                                               company_order_id NOT IN( select id from company_order where company_id in(   44780 ,44794,44795,45363,45364,45365,44780,
                    44794,
                    44795,
                    45363,
                    45364,
                    45365,
                    45363,
                    45363,
                    45363,
                    45363,
                    52468,
                    52468,
                    52468,
                    52468,
                    52468,
                    45364,
                    45364,
                    45364,
                    45364,52468,
52468,
52468,
52468,
52468,
44780,
44780,
44780,
44780
                                                                                                                        )) and 
                                               blocked = 0 and blocked=0 and is_demo = 0 " . $expireDateSql . " order by antal";
        } else {

            $sqlNotSelect = "SELECT COUNT(*) as antal, shop_user.card_values  FROM `shop_user` WHERE 
                                                `shop_id` = " . $shopId . " and 
                                                company_order_id not in(SELECT id FROM company_order where is_cancelled = 1 || order_state  IN(7,8,20)   ) AND 
                                                company_order_id NOT IN( select id from company_order where company_id in(   44780 ,44794,44795,45363,45364,45365,                    44780,
                    44794,
                    44795,
                    45363,
                    45364,
                    45365,
                    45363,
                    45363,
                    45363,
                    45363,
                    52468,
                    52468,
                    52468,
                    52468,
                    52468,
                    45364,
                    45364,
                    45364,
                    45364,
                    52468,
52468,
52468,
52468,
52468,
44780,
44780,
44780,
44780
                      )) AND
                                                company_order_id in( select id from company_order where company_order.`created_datetime` > '2025-01-01 16:47:14' AND company_order.`created_datetime` <= '2025-12-24 23:47:14' )  AND
                                                blocked = 0 and blocked = 0  and is_demo = 0 " . $expireDateSql . $havingSE400440Count . " order by antal";
        }

        $rsNotSelect = Dbsqli::getSql2($sqlNotSelect);

        if (sizeofgf($rsNotSelect) <= 0) {
            echo "ingen data";
            return;
        }
        $allCard = $rsNotSelect[0]["antal"];

        $l = 1;
        $noShops = array("272","57","58","59","574","2550","4740");
        if (in_array($shopId, $noShops)) {
            $l = 4;
        }

        if ($shopId == "0") {
            $sql = "
            SELECT present_model.model_name, present_model.model_no, present_model.model_present_no,COUNT(`present_model_id`) as antal, shop_user.card_values FROM `order`
inner JOIN present_model on present_model.model_id = `order`.`present_model_id`
inner JOIN shop_user on shop_user.id = `order`.`shopuser_id`

WHERE `shop_is_gift_certificate` = 1 and present_model.language_id = 1 and
company_order_id not in(SELECT id FROM company_order where is_cancelled = 1 || order_state  IN(7,8,20)   ) and
company_order_id NOT IN( select id from company_order where company_id in(   44780 ,44794,44795,45363,45364,45365,52468,52469,52470,                    44780,
                    44794,
                    44795,
                    45363,
                    45364,
                    45365,
                    45363,
                    45363,
                    45363,
                    45363,
                    52468,
                    52468,
                    52468,
                    52468,
                    52468,
                    45364,
                    45364,
                    45364,
                    45364,
                                                                         52468,
52468,
52468,
52468,
52468,
44780,
44780,
44780,
44780
                                                                         
                                                                         )) and  
shop_user.blocked = 0 AND
shop_user.is_demo = 0 " . $expireDateSql . $havingSE400440. "
GROUP by present_model.model_present_no order by present_model.model_present_no, present_model.model_name,present_model.model_no
            ";

        } else {
            $sql = "
            SELECT present_model.model_name,  present_model.model_no, present_model.model_present_no,COUNT(`present_model_id`) as antal, navision_item.standard_cost,cardshop_settings.card_price,  present_reservation.quantity, present_model.active as model_active ,shop_user.card_values  FROM `order`
inner JOIN present_model on present_model.model_id = `order`.`present_model_id`
inner JOIN shop_user on shop_user.id = `order`.`shopuser_id`
left join navision_item on  present_model.model_present_no = navision_item.no
left join cardshop_settings on `order`.shop_id =  cardshop_settings.shop_id
LEFT join  present_reservation on present_model.model_id = present_reservation.model_id
                
LEFT join  shop_present on present_model.present_id = shop_present.present_id                                     
WHERE
    
    `shop_is_gift_certificate` = 1 and 
      present_model.language_id = 1 and
company_order_id not in(SELECT id FROM company_order where is_cancelled = 1 or order_state IN(7,8,20)   ) and
 (navision_item.language_id = ".$l." or navision_item.language_id IS NULL )  and 
(navision_item.blocked = 0 or navision_item.blocked IS NULL ) and
(shop_user.blocked = 0 or shop_user.blocked IS NULL) AND
company_order_id NOT IN( select id from company_order where company_id in( 44780 ,44794,44795,45363,45364,45365,52468,52469,52470,                    44780,
                    44794,
                    44795,
                    45363,
                    45364,
                    45365,
                    45363,
                    45363,
                    45363,
                    45363,
                    52468,
                    52468,
                    52468,
                    52468,
                    52468,
                    45364,
                    45364,
                    45364,
                    45364,
                      52468,
52468,
52468,
52468,
52468,
44780,
44780,
44780,
44780)) and 
shop_user.is_demo = 0 and
`order`.shop_id = " . $shopId . "
" . $expireDateSql . "
GROUP by present_model.model_present_no ".$havingSE400440." order by present_model.model_present_no, present_model.model_name,present_model.model_no";

        }

        $rs = Dbsqli::getSql2($sql);

        $total = 0;
        $totalProcent = 0;

        $totalDbCalc = 0;
        $countItems = 0;
        $hasZoroStandardPris = false;

        foreach ($rs as $dataRow) {
            $total += $dataRow["antal"] * 1;
        }
        $notSelect = $allCard - $total;
        $radomId = $this->generateRandomString();

        $html = "<div class='statsContent'><p>Antal der mangler at v&oelig;lge: " . $notSelect . "</p>";
        $html .= "<p>Total antal kort: " . $allCard . "</p>";
        $html .= "<table style='display:none' id='" . $radomId . "'>  <thead><tr><th>Varenr</th><th>Aktiv</th><th>Gave</th><th>Model</th><th>Antal valgte gaver</th><th>Reserverede</th><th>Antal i %</th><th>Fremskrevet v&oelig;rdi</th><th>Kost Pris</th><th>DB stk</th><th>DB total</th><th>DB %</th></thead></tr>  <tbody>";

        if(sizeof($rs)==0){
            echo "Ingen data";
            return;
        }
        foreach ($rs as $dataRow) {

            $dataRow["card_price"] = $dataRow["card_price"];
            $procent = ($dataRow["antal"] / $total) * 100;
            $procent = round($procent, 2);
            $totalProcent += $procent;
            $guess = ($notSelect * $procent) / 100;
            $guess = round($guess) + $dataRow["antal"];

            /* db beregner */

            $seShops = array("1832","1981","2558","4793","5117","8271","9495");
            if (in_array($shopId, $seShops)) {
                $dataRow["card_price"]  = $dataRow["card_price"] * 0.65;

            }

            $salgspris = ($dataRow["card_price"] / 100);
            if($dataRow["standard_cost"] == null){
                $dataRow["standard_cost"] = ($dataRow["card_price"] / 100);

            }
            if ($dataRow["standard_cost"] == 0) {
                $hasZoroStandardPris = true;
                $dataRow["standard_cost"] = $salgspris;
            } else {
                $countItems += $dataRow["antal"];
            }
            $l = 1;

            //Salgspris � standard costpris = d�kningsbidrag

            $dbCalcUnit = (($dataRow["card_price"] / 100) - $dataRow["standard_cost"]);
            $dbCalcTotal = round((($dataRow["card_price"] / 100) - $dataRow["standard_cost"]) * $dataRow["antal"]);
            $totalDbCalc += $dbCalcTotal;
            //  D�kningsbidrag x 100 / salgspris = d�kningsgrad
            $dbProcent = round(($dbCalcUnit * 100) / ($dataRow["card_price"] / 100),2);
            $totalSale = round($salgspris * $countItems);
            $presentActive =  $dataRow["model_active"] == 0 ? "Aktiv":"Ikke aktiv";

            $html .= "<tr>
            <td>" . $dataRow["model_present_no"] . "</td>
             <td>" . $presentActive. "</td>
            <td>" . $dataRow["model_name"] . "</td>

            <td>" . $dataRow["model_no"] . "</td>
            <td>" . $dataRow["antal"] . "</td>
               <td>" . $dataRow["quantity"] . "</td>
            <td>" . $procent . "</td>
            <td>" . $guess . "</td>
             <td>" . $dataRow["standard_cost"] . "</td>  
              <td>" . $dbCalcUnit . "</td>
            <td>" . $dbCalcTotal . "</td>
            <td>" . $dbProcent . "%</td>        
        </tr>";
            $csv[] = [$dataRow["model_present_no"], $presentActive,utf8_decode($dataRow["model_name"]), utf8_decode($dataRow["model_no"]), $dataRow["antal"], $dataRow["quantity"] , $procent, $guess];
        }
        $totalSale = round($salgspris * $countItems);
        $totalDBCalcProcent = round($totalDbCalc * 100 / $totalSale,2);
        $cssRed = $hasZoroStandardPris == true ? "css-red" : "";

        $html .= "  </tbody></table>";
        $html .= "<div class='css-db " . $cssRed . "'><div style='font-size: 10px'>Hvis teksten er rød, skyldes det at der i listen er varer med ej defineret DB </div><table ><tr><td>Total DB</td><td>" . $totalSale . "</td></tr><tr><td>Total DG %</td><td>" . $totalDBCalcProcent . " %</td></tr></table> </div>";
        $html .= "<br><div>Totale antal gaver: <b>" . $total . "</b> (" . $totalProcent . "%)</div></div>";
        $html .= "<script>setTimeout(function(){  $('#" . $radomId . "').DataTable({ 'pageLength': 500 });  $('#" . $radomId . "').fadeIn(400)   }, 400) </script>";

        if ($returnCsv) $this->array_to_csv_download($csv, $filename = "export.csv", $delimiter = ";");
        else echo $html;
    }

    public function loadStats_org($deadline,$shopId,$returnCsv=false)
    {
        $csv = [];
        $expireDateSql = "";
        if($deadline != "alle"){
            $expireDateSql = "and shop_user.expire_date ='".$deadline."'";
        }

        if($shopId == "0"){
            $sqlNotSelect = "SELECT COUNT(*) as antal  FROM `shop_user` 
                          WHERE 
                              `shop_id` in ( select shop_id from `cardshop_settings`  ) and 
                              company_order_id not in(SELECT id FROM company_order where is_cancelled = 1 || order_state  IN(7,8,20)    )  AND 
                              company_order_id NOT IN( select id from company_order where company_id in(   44780 ,44794,44795,45363,45364,45365,52468,52469,52470,                    44780,
                    44794,
                    44795,
                    45363,
                    45364,
                    45365,
                    45363,
                    45363,
                    45363,
                    45363,
                    52468,
                    52468,
                    52468,
                    52468,
                    52468,
                    45364,
                    45364,
                    45364,
                    45364,
                                                                                                       52468,
52468,
52468,
52468,
52468,
44780,
44780,
44780,
44780)) AND 
                              blocked = 0 and is_demo = 0 and shutdown = 0 ".$expireDateSql." order by antal";
        } else {
            $sqlNotSelect = "SELECT COUNT(*) as antal  FROM `shop_user` WHERE `shop_id` = ".$shopId." and company_order_id not in(SELECT id FROM company_order where is_cancelled = 1   )  AND blocked = 0 and is_demo = 0 and shutdown = 0  AND company_order_id NOT IN( select id from company_order where company_id in(   44780 ,44794,44795,45363,45364,45365,                    44780,
                    44794,
                    44795,
                    45363,
                    45364,
                    45365,
                    45363,
                    45363,
                    45363,
                    45363,
                    52468,
                    52468,
                    52468,
                    52468,
                    52468,
                    45364,
                    45364,
                    45364,
                    45364,
                      52468,
52468,
52468,
52468,
52468,
44780,
44780,
44780,
44780))  ".$expireDateSql." order by antal";
        }
        $rsNotSelect = Dbsqli::getSql2($sqlNotSelect);

        if(sizeofgf($rsNotSelect) <= 0 ){
            echo "ingen data";
            return;
        }
        $allCard = $rsNotSelect[0]["antal"];

        if($shopId == "0"){
            $sql = "
            SELECT present_model.model_name, present_model.model_no, present_model.model_present_no,COUNT(`present_model_id`) as antal FROM `order`
inner JOIN present_model on present_model.model_id = `order`.`present_model_id`
inner JOIN shop_user on shop_user.id = `order`.`shopuser_id`
                                                                                                                                       
WHERE `shop_is_gift_certificate` = 1 and present_model.language_id = 1 and
company_order_id not in(SELECT id FROM company_order where is_cancelled = 1 || order_state  IN(7,8,20)   ) and
company_order_id NOT IN( select id from company_order where company_id in(   44780 ,44794,44795,45363,45364,45365,52468,52469,52470,                    44780,
                    44794,
                    44795,
                    45363,
                    45364,
                    45365,
                    45363,
                    45363,
                    45363,
                    45363,
                    52468,
                    52468,
                    52468,
                    52468,
                    52468,
                    45364,
                    45364,
                    45364,
                    45364  ))  and  

shop_user.blocked = 0 AND
shop_user.is_demo = 0 ".$expireDateSql."
GROUP by present_model.model_present_no order by present_model.model_present_no, present_model.model_name,present_model.model_no
            ";
        } else {
            $sql = "
            SELECT present_model.model_name, present_model.model_no, present_model.model_present_no,COUNT(`present_model_id`) as antal FROM `order`
inner JOIN present_model on present_model.model_id = `order`.`present_model_id`
inner JOIN shop_user on shop_user.id = `order`.`shopuser_id`
WHERE `shop_is_gift_certificate` = 1 and present_model.language_id = 1 and
company_order_id not in(SELECT id FROM company_order where is_cancelled = 1   ) and
company_order_id not in( 44780 ,44794,44795,45363,45364,45365 ) and  
shop_user.blocked = 0 AND
shop_user.is_demo = 0 and
`order`.shop_id = ".$shopId."
".$expireDateSql."
GROUP by present_model.model_present_no order by present_model.model_present_no, present_model.model_name,present_model.model_no";

        }

        $rs = Dbsqli::getSql2($sql);

        $total = 0;
        $totalProcent = 0;

        foreach($rs as $dataRow){
            $total+= $dataRow["antal"]*1;
        }
        $notSelect = $allCard - $total;
        $radomId = $this->generateRandomString();

        $html = "<div class='statsContent'><p>Antal der mangler at v&oelig;lge: ".$notSelect."</p>";
        $html.= "<p>Total antal kort: ".$allCard."</p>";
        $html.= "<table style='display:none' id='".$radomId."'>  <thead><tr><th>Varenr</th><th>Gave</th><th>Model</th><th>Antal valgte gaver</th><th>Antal i %</th><th>Fremskrevet v&oelig;rdi</th></thead></tr>  <tbody>";

        foreach($rs as $dataRow){
            $procent =  ($dataRow["antal"] / $total ) *100;
            $procent = round($procent,2);
            $totalProcent+= $procent;
            $guess =  ($notSelect * $procent) / 100 ;
            $guess =  round($guess) + $dataRow["antal"];
            $guess= "";
            $procent ="";
            $html.= "<tr>
            <td>".$dataRow["model_present_no"]."</td>
            <td>".$dataRow["model_name"]."</td>
            <td>".$dataRow["model_no"]."</td>
            <td>".$dataRow["antal"]."</td>
            <td>".$procent."</td>
            <td>".$guess."</td>
        </tr>";
            $csv[] = [$dataRow["model_present_no"],utf8_decode($dataRow["model_name"]),utf8_decode($dataRow["model_no"]),$dataRow["antal"],$procent,$guess];
        }
        $html.= "  </tbody></table>";
        $html.= "<br><div>Totale antal gaver: <b>".$total."</b> (".$totalProcent."%)</div></div>";
        $html.= "<script>setTimeout(function(){  $('#".$radomId."').DataTable({ 'pageLength': 500 });  $('#".$radomId."').fadeIn(400)   }, 400) </script>";

        if($returnCsv)  $this->array_to_csv_download($csv, $filename = "export.csv", $delimiter=";");
        else echo $html;
    }

    function array_to_csv_download($array, $filename = "export.csv", $delimiter=";") {
        // open raw memory as file so no temp files needed, you might run out of memory though
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
        header('Content-Disposition: attachment; filename="'.$filename.'";');
        // make php send the generated csv lines to the browser
        fpassthru($f);
    }

    function generateRandomString($length = 10) {
        return substr(str_shuffle(str_repeat($x='abcdefghijklmnopqrstuvwxyzSDDFSGRFTYJFDTRYH', ceil($length/strlen($x)) )),1,$length);
    }

    public function getStats(){
        // get not used card
        $shop_id = $_POST["shop_id"];
        $deadline = $_POST["deadline"];

        if($_POST["shop_id"] == "0"){
            $sqlNotSelect = "SELECT COUNT(*) as antal  FROM `shop_user` WHERE `shop_id` in (  select shop_id from `cardshop_settings` ) and `shop_user`.blocked = 0";
        } else {
            $sqlNotSelect = "SELECT COUNT(*) as antal  FROM `shop_user` WHERE `shop_id` = ".$shop_id. " AND `expire_date` = '".$deadline."' AND blocked = 0";
        }

        $rsNotSelect = Dbsqli::getSql2($sqlNotSelect);
        if(sizeofgf($rsNotSelect) <= 0 ){
            echo "ingen data";
            return;
        }

        $allCard = $rsNotSelect[0]["antal"];
        if($_POST["shop_id"] == "0"){
            $sql = "SELECT shop_user.expire_date, count(present_id) as total, present_model_id, `present_id`, `present_name`,present_model_name FROM `order` INNER join shop_user on shop_user.username = order.user_username WHERE order.shop_id in  (  select shop_id from `cardshop_settings`  ) and shop_user.blocked = 0 group by present_id,present_model_id  order by total desc";
        } else {
            $sql = "SELECT shop_user.expire_date, count(present_id) as total, present_model_id, `present_id`, `present_name`,present_model_name FROM `order` INNER join shop_user on shop_user.username = order.user_username WHERE order.shop_id = ".$shop_id." and shop_user.expire_date =  '".$deadline."' and shop_user.blocked = 0 group by present_id,present_model_id  order by total desc";
        }

        $rs = Dbsqli::getSql2($sql);

        $total = 0;
        $totalProcent = 0;

        foreach($rs as $dataRow){
            $total+= $dataRow["total"]*1;
        }
        $notSelect = $allCard - $total;

        $html = "<p>Antal der mangler at v&oelig;lge: ".$notSelect."</p>";
        $html.= "<p>Total antal kort: ".$allCard."</p>";
        $html.= "<table><tr><th>Deadline</th><th>ID</th><th>Antal valgte gaver</th><th>Antal i %</th><th>Fremskrevet v&oelig;rdi</th><th>Gave</th><th>Model</th><th>Varenr</th></tr>";
        foreach($rs as $dataRow){
            $procent =  ($dataRow["total"] / $total ) *100;
            $procent = round($procent,2);
            $totalProcent+= $procent;
            $guess =  ($notSelect * $procent) / 100 ;
            $guess =  round($guess) + $dataRow["total"];
            $inputId =  $dataRow["present_id"]."_".base64_encode($dataRow["present_model_name"]);
            $inputId = str_replace("=","",$inputId);
            $modelSql = "select model_name, model_no,model_present_no  from present_model where language_id = 1 and model_id= ".$dataRow["present_model_id"];
            $rsModel = Dbsqli::getSql2($modelSql);
            $dataRow["present_model_name"] = $rsModel[0]["model_name"];
            $modelNavn = $rsModel[0]["model_no"];
            $varenr =  $rsModel[0]["model_present_no"];

            $modelBase64 = base64_encode($dataRow["present_model_name"]);

            $html.= "<tr><td>".$dataRow["expire_date"]."</td><td>".$dataRow["present_id"]."</td><td  id='val_".$inputId."' >".$dataRow["total"]."</td><td>".$procent."%</td><td>".$guess."</td><td>".str_replace("###"," - ",$dataRow["present_model_name"])."</td> <td>".$modelNavn."</td><td>".$varenr."</td></tr>";
        }
        $html.= "<tr style=\"font-size:16px;\"><td></td><td>Totale antal</td><td>".$total."</td><td>".$totalProcent."%</td><td></td><td></td><td></td><td></td><td></td></tr>";
        $html.= "</table>";
        echo $html;
    }

    public function updateStats()
    {
        $sql = "select * from stock_reservation where present_id = ".$_POST["present_id"]." and model_id = '".$_POST["model_id"]."' and shop_id = ".$_POST["shopId"]."  and card_deadline = '".$_POST["deadline"]."' and active = 1";
        $rs = Dbsqli::getSql2($sql);
        if(sizeofgf($rs) == 0 ){
            $sql = "INSERT INTO stock_reservation (quantity,present_id,model_id,model_id_base64,shop_id,card_deadline) VALUES ( ".$_POST["quantity"].",".$_POST["present_id"].",'".$_POST["model_id"]."','".$_POST["model_id_base64"]."',".$_POST["shopId"].",'".$_POST["deadline"]."')";
            $rs = Dbsqli::setSql2($sql);
            if($rs){
                echo $_POST["quantity"];
            } else {
                echo "error";
            }
        } else {
            $id = $rs[0]["id"];
            $sql = "update stock_reservation set quantity = ".$_POST["quantity"]." where id = ".$id;
            $rs = Dbsqli::setSql2($sql);
            $sql = "select quantity from stock_reservation where id = ".$id;
            $rs = Dbsqli::getSql2($sql);
            echo  $rs[0]["quantity"];
        }
    }

    public function getStatsData()
    {
        if($_POST["shopId"] == "0"){
            $sql = "select quantity,model_id from stock_reservation where shop_id in(  select shop_id from `cardshop_settings`  )  and active = 1";
        } else {
            $sql = "select quantity,model_id from stock_reservation where shop_id = ".$_POST["shopId"]."  and card_deadline = '".$_POST["deadline"]."' and active = 1";
        }

        $rs = Dbsqli::getSql2($sql);
        echo json_encode($rs);
    }

    public function getStatsDataAll()
    {
        $sql = "select quantity,model_id from stock_reservation where shop_id = ".$_POST["shopId"]." and active = 1";
        $rs = Dbsqli::getSql2($sql);
        echo json_encode($rs);
    }

    public function getSameDayInWeekLastYear($time=true){
        $today = new \DateTime();

        $year  = (int) $today->format('Y');
        $week  = (int) $today->format('W'); // Week of the year

        $day   = (int) $today->format('w'); // Day of the week (0 = sunday)
        if($day == 0) {
            $day = 7;
        }
        $sameDayLastYear = new \DateTime();
        $sameDayLastYear->setISODate($year - 1, $week, $day);
        if($time==true){
            return $sameDayLastYear->format('Y-m-d H:i:s');
        } else {
            return $sameDayLastYear->format('Y-m-d');
        }
    }

    public function getMounthLastYear($time=true,$correction=0){
        $today = new \DateTime();

        $year  = (int) $today->format('Y');
        $week  = (int) $today->format('W'); // Week of the year

        $day   = (int) $today->format('w'); // Day of the week (0 = sunday)
        if($day == 0) {
            $day = 7;
        }
        $sameDayLastYear = new \DateTime();
        $sameDayLastYear->setISODate($year - $correction, $week, $day);
        if($time==true){
            return $sameDayLastYear->format('Y-m-d H:i:s');
        } else {
            return $sameDayLastYear->format('Y-m-d');
        }
    }

    public function getSameDayInWeekLastYear2019($time=true){
        $today = new \DateTime();

        $year  = (int) $today->format('Y');
        $week  = (int) $today->format('W'); // Week of the year
        $day   = (int) $today->format('w'); // Day of the week (0 = sunday)
        if($day == 0) {
            $day = 7;
        }
        $sameDayLastYear = new \DateTime();
        $sameDayLastYear->setISODate($year - 2, $week, $day);
        if($time==true){
            return $sameDayLastYear->format('Y-m-d H:i:s');
        } else {
            return $sameDayLastYear->format('Y-m-d');
        }
    }

    // DB V2
    public function dbCalcV22021($shopID,$end,$l)
    {
        $dato2021 =  $this->getSameDayInWeekLastYearDB(true,1);

        $end = "all"; //base64_encode($dato2021);
        $url = 'https://gavefabrikken.dk/2024/gavefabrikken_backend/component/db2021.php?shopID='.$shopID."&end=".$end."&l=".$l;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_close($curl);
        $res = curl_exec($curl);
        //die($res);
        $dbShopData = json_decode($res,true);

        return $this->dbCalc($dbShopData,$l);
    }

    //52,53,54,55,290,310,575,2548,2395
    public function getSameDayInWeekLastYearDB($time=true,$correction=0){
        $today = new \DateTime();

        $year  = (int) $today->format('Y');
        $week  = (int) $today->format('W'); // Week of the year

        $day   = (int) $today->format('w'); // Day of the week (0 = sunday)
        if($day == 0) {
            $day = 7;
        }
        $sameDayLastYear = new \DateTime();
        $sameDayLastYear->setISODate($year - $correction, $week, $day);
        if($time==true){
            return $sameDayLastYear->format('Y-m-d H:i:s');
        } else {
            return $sameDayLastYear->format('Y-m-d');
        }
    }

    public function dbCalcV2DK(){
        //
        $this->dbCalcV2("52,53,2395,54,55,56,575,2548,290,310",1);
    }

    public function dbCalcV2NO(){
        $this->dbCalcV2("272,57,58,59,574,2550,2549,4740",4);
    }

    public function dbCalcV2SE(){
        $this->dbCalcV2("1832,1981,2558,4793,5117,8271,9495",1);
    }

    public function dbCalcV2($shopList,$l)
    {
        $end = "all"; //$this->getSameDayInWeekLastYearDB(false,1)." 23:59:59";

        // 52,53,2395,54,55,56,575,2548,290,310
        // 272,57,58,59,574,2550,2549
        // 1832,1981,2558,4793,5117
        $csv = "season;shop_id;cardname;cardprice;totalSale;totalDb;dbProcent;Mangler varenr. \n";
        $sql = "SELECT shop_id FROM `cardshop_settings` WHERE shop_id in (".$shopList.")";
        $rsShopList = Dbsqli::getSql2($sql);
        $result= array();
        foreach ($rsShopList as $shop){
            $dbShopData = $this->dbCalcGetRawData($shop["shop_id"],$l);
            $res2022 = $this->dbCalc($dbShopData);
            $res2022["totalSale"] = number_format($res2022["totalSale"], 2, ',', '.');
            $res2022["totalDb"] = number_format($res2022["totalDb"], 2, ',', '.');

            $res2021 = $this->dbCalcV22021($shop["shop_id"],"all",$l);
            $res2021["season"] = "2021";
            $res2021["totalSale"] = number_format($res2021["totalSale"], 2, ',', '.');
            $res2021["totalDb"] = number_format($res2021["totalDb"], 2, ',', '.');

            $csv.= implode(';',$res2022);
            $csv.= "\n";
            $csv.= implode(';',$res2021);
            $csv.= "\n";
        }

        header('Content-Type: application/csv');
        header('Content-Disposition: attachement; filename="data.csv"');
        echo $csv; exit();
    }

    public function dbCalc($data){
        $hasZoroStandardPris = false;
        $totalDb = 0;
        $countItems = 0;
        $salgspris = 0;
        $cardname = "";
        foreach ($data as $shop){
            $orgPris = ($shop["card_price"]/ 100);
            $seShops = array("1832","1981","2558","4793","5117","8271","9495");
            if (in_array($shop["shop_id"], $seShops)) {
                $shop["card_price"]  = $shop["card_price"] * 0.7;
            }

            $cardname = $shop["concept_code"];
            $salgspris = ($shop["card_price"]/ 100);
            if($shop["standard_cost"] == 0){
                $hasZoroStandardPris = true;
                $shop["standard_cost"] = $salgspris;

            } else {
                $countItems+= $shop["antal"];
            }

            $dbCalcUnit = (($shop["card_price"]/ 100) - $shop["standard_cost"])*$shop["antal"] ;
            $totalDb+= $dbCalcUnit;
        }
        $totalSale = round($salgspris  * $countItems);
        $totalDBCalcProcent =   round($totalDb*100 / $totalSale,2);
        $totalDBCalcProcent = str_replace(',', '.', $totalDBCalcProcent);
        $totalDb = round($totalDb);
        return array("season"=>"2022","shop_id"=>$shop["shop_id"],  "cardname"=>$cardname, "cardprice"=>$orgPris,"totalSale"=>$totalSale,"totalDb"=>$totalDb,"dbProcent"=>$totalDBCalcProcent,"hasZoroStandardPris"=>$hasZoroStandardPris);
    }

    public function dbCalcGetRawData($shop_id,$l){
        $start="2022-04-01 16:47:14";
        $slut="2022-12-24 16:47:14";
        $sql = "SELECT
 `order`.shop_id,   
present_model_id,
COUNT(`order`.id) as antal,
present_model.model_present_no,
navision_item.standard_cost,
cardshop_settings.concept_code,
card_price

FROM `order`
 INNER JOIN present_model ON present_model.model_id = `order`.`present_model_id`
 LEFT JOIN navision_item ON present_model.model_present_no = navision_item.no
 LEFT JOIN cardshop_settings ON `order`.shop_id = cardshop_settings.shop_id


WHERE
present_model.language_id = 1 AND
`order`.`shop_id` = ".$shop_id." and

navision_item.language_id  = ".$l." AND
navision_item.blocked = 0 AND

`order`.`order_timestamp` > '".$start."' AND
`order`.`order_timestamp` <= '".$slut."'


GROUP BY
`present_model_id`
";
        return Dbsqli::getSql2($sql);
    }

}
?>