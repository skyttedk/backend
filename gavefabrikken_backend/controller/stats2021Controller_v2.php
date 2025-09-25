<?php
set_time_limit(3000);
ini_set('memory_limit', '256M');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Controller media
// Date created  Wed, 06 Apr 2016 09:39:56 +0200
// Updated and optimized April 2025
class stats2021Controller
{
    // Metadata for shop configurations remains the same
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
        4793 => array("valuealias" => "S3-", "name" => 27958, "address1" => 28558, "address2" => 28559, "zip" => 28560, "city" => 28561, "email" => 27959, "phone" => 28562, "gaveklub" => 27960, "country" => "Sverige", "lang_code" => "se", "lang_num" => 5),
        1832 => array("valuealias" => "S4-", "name" => 10085, "address1" => 10747, "address2" => 10748, "zip" => 10749, "city" => 10750, "email" => 10086, "phone" => 11667, "gaveklub" => 16132, "country" => "Sverige", "lang_code" => "se", "lang_num" => 5),
        5117 => array("valuealias" => "S6-", "name" => 29576, "address1" => 29589, "address2" => 29590, "zip" => 29591, "city" => 29592, "email" => 29577, "phone" => 29593, "gaveklub" => 29578, "country" => "Sverige", "lang_code" => "se", "lang_num" => 5),
        1981 => array("valuealias" => "S8-", "name" => 11057, "address1" => 11668, "address2" => 11669, "zip" => 11670, "city" => 11671, "email" => 11058, "phone" => 11672, "gaveklub" => 16133, "country" => "Sverige", "lang_code" => "se", "lang_num" => 5),
        2558 => array("valuealias" => "S12-", "name" => 14457, "address1" => 16275, "address2" => 16276, "zip" => 16277, "city" => 16278, "email" => 14458, "phone" => 16872, "gaveklub" => 14459, "country" => "Sverige", "lang_code" => "se", "lang_num" => 5),
        2549 => array("valuealias" => 9, "name" => 14410, "address1" => 16862, "address2" => 16863, "zip" => 16864, "city" => 16865, "email" => 14411, "phone" => 16866, "gaveklub" => 14412, "country" => "Norge", "lang_code" => "no", "lang_num" => 4),
        2550 => array("valuealias" => 2, "name" => 14415, "address1" => 16867, "address2" => 16868, "zip" => 16869, "city" => 16870, "email" => 14416, "phone" => 16871, "gaveklub" => 14417, "country" => "Norge", "lang_code" => "no", "lang_num" => 4),
        4740 => array("valuealias" => 7, "name" => 27659, "address1" => 27662, "address2" => 27663, "zip" => 27664, "city" => 27665, "email" => 27660, "phone" => 27666, "gaveklub" => 28557, "country" => "Norge", "lang_code" => "no", "lang_num" => 4),
        574 => array("valuealias" => 1, "name" => 2928, "address1" => 10767, "address2" => 10768, "zip" => 10769, "city" => 10770, "email" => 2929, "phone" => 4305, "gaveklub" => 4300, "country" => "Norge", "lang_code" => "no", "lang_num" => 4),
        59 => array("valuealias" => 8, "name" => 727, "address1" => 10763, "address2" => 10764, "zip" => 10765, "city" => 10766, "email" => 728, "phone" => 4304, "gaveklub" => 4299, "country" => "Norge", "lang_code" => "no", "lang_num" => 4),
        58 => array("valuealias" => 6, "name" => 93, "address1" => 10759, "address2" => 10760, "zip" => 10761, "city" => 10762, "email" => 92, "phone" => 4303, "gaveklub" => 4298, "country" => "Norge", "lang_code" => "no", "lang_num" => 4),
        57 => array("valuealias" => 4, "name" => 722, "address1" => 10755, "address2" => 10756, "zip" => 10757, "city" => 10758, "email" => 723, "phone" => 4302, "gaveklub" => 4297, "country" => "Norge", "lang_code" => "no", "lang_num" => 4),
        272 => array("valuealias" => 3, "name" => 1228, "address1" => 10751, "address2" => 10752, "zip" => 10753, "city" => 10754, "email" => 1229, "phone" => 4301, "gaveklub" => 4296, "country" => "Norge", "lang_code" => "no", "lang_num" => 4)
    );

    // Shop groups by country
    private $shopGroups = [
        'dk' => [7121, 52, 4668, 53, 2395, 54, 55, 56, 290, 310, 575, 4662, 2548, 2961, 2960, 2962, 2963],
        'no' => [272, 57, 58, 59, 574, 2550, 4740, 2549],
        'se' => [4793, 1832, 1832440, 5117, 1981, 2558,8271]
    ];

    // Excluded company IDs
    private $excludedCompanyIds = [
        44780, 44794, 44795, 45363, 45364, 45365, 52468, 52469, 52470
    ];

    /**
     * Entry point to display dynamic UI with stats options
     */
    public function Index()
    {
        $this->displayStatsUI();
    }

    /**
     * Display the main statistics UI dashboard
     */
    private function displayStatsUI()
    {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Gavefabrikken Statistics</title>
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
            <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
            <link rel="stylesheet" href="assets/css/stats-styles.css">
        </head>
        <body>
            <div class="stats-container">
                <div class="stats-header">
                    <h1><i class="fa fa-chart-bar"></i> Gavefabrikken Statistics</h1>
                </div>

                <div class="stats-tabs">
                    <button class="tab-btn active" data-tab="card-sales">Kort Salg</button>
                    <button class="tab-btn" data-tab="db-stats">DB Statistik</button>
                    <button class="tab-btn" data-tab="login-stats">Login Statistik</button>
                </div>

                <div class="tab-content active" id="card-sales">
                    <div class="filter-panel">
                        <div class="filter-group">
                            <label>Land</label>
                            <select id="country-selector">
                                <option value="dk">Danmark</option>
                                <option value="no">Norge</option>
                                <option value="se">Sverige</option>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label>Shop</label>
                            <select id="shop-selector">
                                <option value="0">Alle Shops</option>
                                <!-- Options will be loaded via JS -->
                            </select>
                        </div>

                        <div class="filter-group" id="deadline-container">
                            <!-- Deadlines will be loaded via JS -->
                        </div>

                        <div class="filter-group">
                            <label>Statistik Type</label>
                            <div class="toggle-group">
                                <button class="toggle-btn active" id="normal-stats">Normal</button>
                                <button class="toggle-btn" id="alias-stats">Alias</button>
                            </div>
                        </div>
                    </div>

                    <div class="action-bar">
                        <button id="refresh-stats" class="btn btn-primary"><i class="fa fa-sync-alt"></i> Opdater</button>
                        <button id="export-csv" class="btn btn-secondary"><i class="fa fa-file-export"></i> Eksporter CSV</button>
                    </div>

                    <div id="stats-data-container">
                        <!-- Stats data will be loaded here -->
                        <div class="placeholder-message">
                            <i class="fa fa-info-circle"></i>
                            <p>Vælg en shop og deadline for at se statistik</p>
                        </div>
                    </div>
                </div>

                <div class="tab-content" id="db-stats">
                    <div class="filter-panel">
                        <div class="filter-group">
                            <label>Land</label>
                            <select id="db-country-selector">
                                <option value="dk">Danmark</option>
                                <option value="no">Norge</option>
                                <option value="se">Sverige</option>
                            </select>
                        </div>
                    </div>

                    <div class="action-bar">
                        <button id="calculate-db" class="btn btn-primary"><i class="fa fa-calculator"></i> Beregn DB</button>
                        <button id="export-db-csv" class="btn btn-secondary"><i class="fa fa-file-export"></i> Eksporter CSV</button>
                    </div>

                    <div id="db-data-container">
                        <!-- DB data will be loaded here -->
                        <div class="placeholder-message">
                            <i class="fa fa-info-circle"></i>
                            <p>Vælg et land og klik på "Beregn DB" for at se statistik</p>
                        </div>
                    </div>
                </div>

                <div class="tab-content" id="login-stats">
                    <div class="filter-panel">
                        <div class="filter-group">
                            <label>Periode</label>
                            <select id="login-period-selector">
                                <option value="1">Seneste 24 timer</option>
                                <option value="7">Seneste 7 dage</option>
                                <option value="30">Seneste 30 dage</option>
                            </select>
                        </div>
                    </div>

                    <div class="action-bar">
                        <button id="refresh-login-stats" class="btn btn-primary"><i class="fa fa-sync-alt"></i> Opdater</button>
                    </div>

                    <div id="login-stats-container">
                        <!-- Login stats will be loaded here -->
                        <div class="placeholder-message">
                            <i class="fa fa-info-circle"></i>
                            <p>Vælg en periode og klik på "Opdater" for at se login statistik</p>
                        </div>
                    </div>
                </div>
            </div>

            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
            <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
            <script src="assets/js/stats-controller.js"></script>
        </body>
        </html>
        <?php
    }

    /**
     * Display card sales statistics
     */
    public function cardSale()
    {
        // Set date ranges
        $today_start = date("Y-m-d") . " 00:00:01";
        $today_end = date("Y-m-d") . " 23:59:59";
        $month_start = date('Y-m-01') . " 00:00:01";
        $month_end = date("Y-m-t", strtotime(date("Y-m-d"))) . " 23:59:59";

        $data_2024_dk = array(
            "total" => $this->getSalePresentYear(1, "", "2024-01-01 16:47:14", "2024-12-24 23:47:14"),
            "day" => $this->getSalePresentYear(1, "", $today_start, $today_end),
            "month" => $this->getSalePresentYear(1, "", $month_start, $month_end)
        );
        $data_2023_dk = unserialize(base64_decode($this->getDataFromPreviusYears(1, 2023)));
        $data_2022_dk = unserialize(base64_decode($this->getDataFromPreviusYears(1, 2022)));

        $data_2023_no = unserialize(base64_decode($this->getDataFromPreviusYears(4, 2023)));
        $data_2022_no = unserialize(base64_decode($this->getDataFromPreviusYears(4, 2022)));

        $data_2023_se = unserialize(base64_decode($this->getDataFromPreviusYears(5, 2023)));
        $data_2022_se = unserialize(base64_decode($this->getDataFromPreviusYears(5, 2022)));

        $cardTitleHtml = $this->buildTitleCol(1);

        // Display Danish shops stats
        echo "<h1>Danske shops</h1>";
        $sortData = $this->buildCol2021($data_2024_dk, "total", 1);
        $total_2024 = $this->buildCol($sortData);
        $total_2024_Html = '<div class="v-flex"><div class="header-title">Antal solgte totalt 2024</div>' . $total_2024 . '</div>';

        $sortData = $this->buildCol2021($data_2024_dk, "day", 1);
        $day_2024 = $this->buildCol($sortData);
        $day_2024_Html = '<div class="v-flex"><div class="header-title">Antal solgte i dag 2024</div>' . $day_2024 . '</div>';

        $sortData = $this->buildCol2021($data_2024_dk, "month", 1);
        $month_2024 = $this->buildCol($sortData);
        $mounth_2024_Html = '<div class="v-flex"><div class="header-title">Antal solgte indeværende måned 2024</div>' . $month_2024 . '</div>';

        // 2023 data
        $sortData = $this->buildCol2021($data_2023_dk, "total", 1);
        $total_2023 = $this->buildCol($sortData);
        $total_2023_Html = '<div class="v-flex"><div class="header-title">Antal solgte totalt 2023</div>' . $total_2023 . '</div>';

        $sortData = $this->buildCol2021($data_2023_dk, "day", 1);
        $day_2023 = $this->buildCol($sortData);
        $day_2023_Html = '<div class="v-flex"><div class="header-title">Antal solgte i dag 2023</div>' . $day_2023 . '</div>';

        $sortData = $this->buildCol2021($data_2023_dk, "month", 1);
        $month_2023 = $this->buildCol($sortData);
        $mounth_2023_Html = '<div class="v-flex"><div class="header-title">Antal solgte indeværende måned 2023</div>' . $month_2023 . '</div>';

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
        $mounth_2022_Html = '<div class="v-flex"><div class="header-title">Antal solgte indeværende måned 2022</div>' . $month_2022 . '</div>';

        $sortData = $this->buildCol2021($data_2022_dk, "total_day", 1);
        $total_day_2022 = $this->buildCol($sortData);
        $total_day_2022_Html = '<div class="v-flex"><div class="header-title">Antal solgte totalt til og med samme dag, 2022</div>' . $total_day_2022 . '</div>';

        echo '<div class="flex-container">' .
            $cardTitleHtml .
            $total_2024_Html .
            $day_2024_Html .
            $mounth_2024_Html .
            $total_2023_Html .
            $day_2023_Html .
            $mounth_2023_Html .
            $total_day_2023_Html .
            $total_2022_Html .
            $day_2022_Html .
            $mounth_2022_Html .
            $total_day_2022_Html .
            '</div>';
        echo "<br><hr>";

        // Norwegian shops
        $data_2024_no = array(
            "total" => $this->getSalePresentYear(4, "", "2024-01-01 16:47:14", "2024-12-24 23:47:14"),
            "day" => $this->getSalePresentYear(4, "", $today_start, $today_end),
            "month" => $this->getSalePresentYear(4, "", $month_start, $month_end)
        );

        $cardTitleHtml = $this->buildTitleCol(4);

        echo "<h1>Norske shops</h1>";
        $sortData = $this->buildCol2021($data_2024_no, "total", 4);
        $total_2024 = $this->buildCol($sortData);
        $total_2024_Html = '<div class="v-flex"><div class="header-title">Antal solgte totalt 2024</div>' . $total_2024 . '</div>';

        $sortData = $this->buildCol2021($data_2024_no, "day", 4);
        $day_2024 = $this->buildCol($sortData);
        $day_2024_Html = '<div class="v-flex"><div class="header-title">Antal solgte i dag 2024</div>' . $day_2024 . '</div>';

        $sortData = $this->buildCol2021($data_2024_no, "month", 4);
        $month_2024 = $this->buildCol($sortData);
        $mounth_2024_Html = '<div class="v-flex"><div class="header-title">Antal solgte indeværende måned 2024</div>' . $month_2024 . '</div>';

        // 2023 data
        $sortData = $this->buildCol2021($data_2023_no, "total", 4);
        $total_2023_no = $this->buildCol($sortData);
        $total_2023_Html_no = '<div class="v-flex"><div class="header-title">Antal solgte totalt 2023</div>' . $total_2023_no . '</div>';

        $sortData = $this->buildCol2021($data_2023_no, "day", 4);
        $day_2023_no = $this->buildCol($sortData);
        $day_2023_Html_no = '<div class="v-flex"><div class="header-title">Antal solgte i dag 2023</div>' . $day_2023_no . '</div>';

        $sortData = $this->buildCol2021($data_2023_no, "month", 4);
        $month_2023_no = $this->buildCol($sortData);
        $mounth_2023_Html = '<div class="v-flex"><div class="header-title">Antal solgte indeværende måned 2023</div>' . $month_2023_no . '</div>';

        $sortData = $this->buildCol2021($data_2023_no, "total_day", 4);
        $total_day_2023_no = $this->buildCol($sortData);
        $total_day_2023_Html_no = '<div class="v-flex"><div class="header-title">Antal solgte totalt til og med samme dag, 2023</div>' . $total_day_2023_no . '</div>';

        // 2022 data
        $sortData = $this->buildCol2021($data_2022_no, "total", 4);
        $total_2022_no = $this->buildCol($sortData);
        $total_2022_Html_no = '<div class="v-flex"><div class="header-title">Antal solgte totalt 2022</div>' . $total_2022_no . '</div>';

        $sortData = $this->buildCol2021($data_2022_no, "day", 4);
        $day_2022_no = $this->buildCol($sortData);
        $day_2022_Html_no = '<div class="v-flex"><div class="header-title">Antal solgte i dag 2022</div>' . $day_2022_no . '</div>';

        $sortData = $this->buildCol2021($data_2022_no, "month", 4);
        $month_2022_no = $this->buildCol($sortData);
        $mounth_2022_Html_no = '<div class="v-flex"><div class="header-title">Antal solgte indeværende måned 2022</div>' . $month_2022_no . '</div>';

        $sortData = $this->buildCol2021($data_2022_no, "total_day", 4);
        $total_day_2022_no = $this->buildCol($sortData);
        $total_day_2022_Html_no = '<div class="v-flex"><div class="header-title">Antal solgte totalt til og med samme dag, 2022</div>' . $total_day_2022_no . '</div>';

        echo '<div class="flex-container">' .
            $cardTitleHtml .
            $total_2024_Html .
            $day_2024_Html .
            $mounth_2024_Html .
            $total_2023_Html_no .
            $day_2023_Html_no .
            $mounth_2023_Html .
            $total_day_2023_Html_no .
            $total_2022_Html_no .
            $day_2022_Html_no .
            $mounth_2022_Html_no .
            $total_day_2022_Html_no .
            '</div>';
        echo "<br><hr>";

        // Swedish shops
        $data_2024_se = array(
            "total" => $this->getSalePresentYear(5, "", "2024-01-01 16:47:14", "2024-12-24 23:47:14"),
            "day" => $this->getSalePresentYear(5, "", $today_start, $today_end),
            "month" => $this->getSalePresentYear(5, "", $month_start, $month_end)
        );
        $data_2024_se = $this->Handlingse400440Display($data_2024_se);

        $cardTitleHtml = $this->buildTitleCol(5);

        echo "<h1>Svenske shops</h1>";
        $sortData = $this->buildCol2021($data_2024_se, "total", 5);
        $total_2024 = $this->buildCol($sortData);
        $total_2024_Html = '<div class="v-flex"><div class="header-title">Antal solgte totalt 2024</div>' . $total_2024 . '</div>';

        $sortData = $this->buildCol2021($data_2024_se, "day", 5);
        $day_2024 = $this->buildCol($sortData);
        $day_2024_Html = '<div class="v-flex"><div class="header-title">Antal solgte i dag 2024</div>' . $day_2024 . '</div>';

        $sortData = $this->buildCol2021($data_2024_se, "month", 5);
        $month_2024 = $this->buildCol($sortData);
        $mounth_2024_Html = '<div class="v-flex"><div class="header-title">Antal solgte indeværende måned 2024</div>' . $month_2024 . '</div>';

        // 2023 data
        $sortData = $this->buildCol2021($data_2023_se, "total", 5);
        $total_2023_se = $this->buildCol($sortData);
        $total_2023_Html_se = '<div class="v-flex"><div class="header-title">Antal solgte totalt 2023</div>' . $total_2023_se . '</div>';

        $sortData = $this->buildCol2021($data_2023_se, "day", 5);
        $day_2023_se = $this->buildCol($sortData);
        $day_2023_Html_se = '<div class="v-flex"><div class="header-title">Antal solgte i dag 2023</div>' . $day_2023_se . '</div>';

        $sortData = $this->buildCol2021($data_2023_se, "month", 5);
        $month_2023_se = $this->buildCol($sortData);
        $mounth_2023_Html_se = '<div class="v-flex"><div class="header-title">Antal solgte indeværende måned 2023</div>' . $month_2023_se . '</div>';

        $sortData = $this->buildCol2021($data_2023_se, "total_day", 5);
        $total_day_2023_se = $this->buildCol($sortData);
        $total_day_2023_Html_se = '<div class="v-flex"><div class="header-title">Antal solgte totalt til og med samme dag, 2023</div>' . $total_day_2023_se . '</div>';

        // 2022 data
        $sortData = $this->buildCol2021($data_2022_se, "total", 5);
        $total_2022 = $this->buildCol($sortData);
        $total_2022_Html_se = '<div class="v-flex"><div class="header-title">Antal solgte totalt 2022</div>' . $total_2022 . '</div>';

        $sortData = $this->buildCol2021($data_2022_se, "day", 5);
        $day_2022_se = $this->buildCol($sortData);
        $day_2022_Html_se = '<div class="v-flex"><div class="header-title">Antal solgte i dag 2022</div>' . $day_2022_se . '</div>';

        $sortData = $this->buildCol2021($data_2022_se, "month", 5);
        $month_2022_se = $this->buildCol($sortData);
        $mounth_2022_Html_se = '<div class="v-flex"><div class="header-title">Antal solgte indeværende måned 2022</div>' . $month_2022_se . '</div>';

        $sortData = $this->buildCol2021($data_2022_se, "total_day", 5);
        $total_day_2022_se = $this->buildCol($sortData);
        $total_day_2022_Html_se = '<div class="v-flex"><div class="header-title">Antal solgte totalt til og med samme dag, 2022</div>' . $total_day_2022_se . '</div>';

        echo '<div class="flex-container">' .
            $cardTitleHtml .
            $total_2024_Html .
            $day_2024_Html .
            $mounth_2024_Html .
            $total_2023_Html_se .
            $day_2023_Html_se .
            $mounth_2023_Html_se .
            $total_day_2023_Html_se .
            $total_2022_Html_se .
            $day_2022_Html_se .
            $mounth_2022_Html_se .
            $total_day_2022_Html_se .
            '</div>';
        echo "<br><hr>";
    }

    /**
     * Special handling for Swedish 400/440 shop display
     */
    private function Handlingse400440Display($data)
    {
        foreach ($data as $key => &$section) {
            // Loop through each element in the section
            foreach ($section as &$item) {
                // Check if card_values is not null and contains '440'
                if (!is_null($item['card_values']) && $item['card_values'] !== '' && strpos($item['card_values'], '440') !== false) {
                    // Modify shop_id by adding '_440'
                    $item['shop_id'] = $item['shop_id'] . '440';
                }
            }
        }
        return $data;
    }

    /**
     * Get data from previous years
     */
    public function getDataFromPreviusYears($lang, $year = 2022)
    {
        $url = 'https://system.gavefabrikken.dk/gavefabrikken_backend/component/stats2022.php?lang=' . $lang . '&year=' . $year;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }

    /**
     * Get old data from 2021
     */
    public function getOldData($lang, $year = 2021)
    {
        $url = 'https://gavefabrikken.dk/gavefabrikken_backend/component/test.php?lang=' . $lang . '&year=' . $year;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }

    /**
     * Get old data from 2020
     */
    public function getOldData2020($lang, $year)
    {
        $url = 'https://gavefabrikken.dk/gavefabrikken_backend/component/test2.php?lang=' . $lang . '&year=' . $year;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }

    /**
     * Build column data for 2021-style reports
     */
    public function buildCol2021($data, $coll, $lang)
    {
        $sortArr = [];
        $langListe = [];

        if ($lang == 1) {
            $langListe = array(7121, 52, 4668, 53, 2395, 54, 55, 56, 290, 310, 575, 4662, 2548, 2961, 2960, 2962, 2963);
        }
        if ($lang == 4) {
            $langListe = array(272, 57, 58, 59, 574, 2550, 4740, 2549);
        }
        if ($lang == 5) {
            $langListe = array(4793, 1832, 1832440, 5117, 8271,1981, 2558);
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

    /**
     * Find data for a specific shop ID
     */
    public function findDataOnShopID($data, $shopID)
    {
        $return = 0;
        if (is_array($data)) {
            foreach ($data as $ele) {
                if ($ele["shop_id"] == $shopID) {
                    $return = $ele["antal"];
                    break;
                }
            }
        }
        return $return;
    }

    /**
     * Build column HTML
     */
    public function buildCol($list)
    {
        $returnData["sum"] = 0;
        $returnData["html"] = "";

        foreach ($list as $ele) {
            $ele['antal'] = $ele['antal'] == "" ? "0" : $ele['antal'];
            $returnData["sum"] += (int)$ele['antal'];
            $returnData["html"] .= "<div class='" . $ele['concept_code'] . "'>" . $ele['antal'] . "</div>";
        }

        $returnData["html"] .= "<div class='total'><b>" . $returnData["sum"] . "</b></div>";
        return $returnData["html"];
    }

    /**
     * Build title column
     */
    public function buildTitleCol($lang)
    {
        $returnHtml = "";
        $sql = "SELECT * FROM `cardshop_settings` WHERE `language_code` = " . $lang;
        $rs = Dbsqli::getSql2($sql);

        $sortArr = [];
        $langListe = [];

        if ($lang == 1) {
            $langListe = array(7121, 52, 4668, 53, 2395, 54, 55, 56, 290, 310, 575, 4662, 2548, 2961, 2960, 2962, 2963);
        }
        if ($lang == 4) {
            $langListe = array(272, 57, 58, 59, 574, 2550, 4740, 2549);
        }
        if ($lang == 5) {
            $langListe = array(4793, 1832, 1832440, 5117,8271, 1981, 2558);
        }

        foreach ($langListe as $ele) {
            if ($ele == 1832440) {
                $sortArr[] = array("concept_code" => "SE-440");
                continue;
            }

            $sortArr[] = array(
                "concept_code" => $this->findConceptCode($rs, $ele)
            );
        }

        foreach ($sortArr as $ele) {
            $ele['concept_code'] = $ele['concept_code'] == "2558" ? "SE-1200 (udgået)" : $ele['concept_code'];
            $returnHtml .= "<div class='consept'>" . $ele['concept_code'] . "</div>";
        }

        $returnHtml .= "<div><b>TOTAL</b></div>";
        return '<div class="v-flex"><div class="header-title">Shop</div>' . $returnHtml . '</div>';
    }

    /**
     * Find concept code for a shop
     */
    public function findConceptCode($data, $shopID)
    {
        $return = $shopID;

        if (is_array($data)) {
            foreach ($data as $ele) {
                if ($ele["shop_id"] == $shopID) {
                    $return = $ele["concept_code"];
                    break;
                }
            }
        }

        // Special cases
        if ($shopID == 2548) {
            $return = "GRON(Udgået)";
        }
        if ($shopID == 2549) {
            $return = "BRA(Udgået)";
        }

        return $return;
    }

    /**
     * Get sales data for the current year
     */
    public function getSalePresentYear($lang, $dbprefix, $start, $slut)
    {
        // Special SQL for Swedish shops to include card_values
        $seSql = $lang == 5 ? ", shop_user.card_values" : "";

        // Build excluded company IDs string
        $excludedIds = implode(',', $this->excludedCompanyIds);

        // Build the query
        $sql = "SELECT
            c.card_values,
            language_code,
            {$dbprefix}cardshop_settings.concept_code,
            c.antal,
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
                {$dbprefix}company_order.`created_datetime` > '$start'
                AND {$dbprefix}company_order.`created_datetime` <= '$slut'
                AND `salesperson` NOT LIKE ('%us%')
                AND {$dbprefix}company_order.order_state NOT IN(7,8,20)
                AND {$dbprefix}company_order.is_cancelled = 0
                AND company_order_id NOT IN(
                    SELECT id
                    FROM {$dbprefix}company_order
                    WHERE company_id IN($excludedIds)
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
            {$dbprefix}cardshop_settings.show_index";

        return Dbsqli::getSql2($sql);
    }

    /**
     * Display login statistics
     */
    public function loginStats()
    {
        $totalLogin = 0;
        $totalGavevalg = 0;
        $totalRequest = 0;

        echo "<script>setTimeout(function() {
            location.reload();
        }, 10000);</script>";

        if (isset($_GET["token"]) && $_GET["token"] == "saddsfsdflkfj489fyth") {
            echo "<h3>CPU in percent of cores used (5 min avg):<u> " . $this->get_server_cpu_usage() . "</u></h3>";

            // Get login stats
            $sql = "SELECT
                DAY(`created_datetime`) as Day,
                HOUR(`created_datetime`) as Hour,
                COUNT(*) as Count
            FROM `system_log`
            WHERE `controller` LIKE 'login'
                AND `action` LIKE 'loginShopUserByToken'
                AND `created_datetime` > SUBDATE(NOW(),1)
            GROUP BY DAY(created_datetime), HOUR(created_datetime)
            ORDER BY `id` DESC";
            $rs = Dbsqli::getSql2($sql);

            // Get order stats
            $sql2 = "SELECT
                DAY(`order_timestamp`) as Day,
                HOUR(`order_timestamp`) as Hour,
                COUNT(*) as Count
            FROM `order`
            WHERE `order_timestamp` > SUBDATE(NOW(),1)
            GROUP BY DAY(`order_timestamp`), HOUR(`order_timestamp`)
            ORDER BY `id` DESC";
            $rs2 = Dbsqli::getSql2($sql2);

            // Get request stats
            $sql3 = "SELECT
                DAY(`created_datetime`) as Day,
                HOUR(`created_datetime`) as Hour,
                COUNT(*) as Count
            FROM `system_log`
            WHERE `created_datetime` > SUBDATE(NOW(),1)
                AND `action` NOT LIKE 'loginStats'
            GROUP BY DAY(created_datetime), HOUR(created_datetime)
            ORDER BY `id` DESC";
            $rs3 = Dbsqli::getSql2($sql3);

            // Output statistics table
            echo "<table border=1 width=100% style='font-size:2vh;'>
                <tr><th>Dag</th><th>Time</th><th>Login</th><th>Gavevalg</th><th>Request</th></tr>";

            for ($i = 0; $i < $this->sizeOfArray($rs); $i++) {
                $rs2[$i]["Count"] = isset($rs2[$i]["Count"]) ? $rs2[$i]["Count"] : 0;
                $rs3[$i]["Count"] = isset($rs3[$i]["Count"]) ? $rs3[$i]["Count"] : 0;

                echo "<tr>
                    <td>" . $rs[$i]["Day"] . "</td>
                    <td>" . $rs[$i]["Hour"] . "</td>
                    <td>" . $rs[$i]["Count"] . "</td>
                    <td>" . $rs2[$i]["Count"] . "</td>
                    <td>" . $rs3[$i]["Count"] . "</td>
                </tr>";

                $totalLogin += (int)$rs[$i]["Count"];
                $totalGavevalg += (int)$rs2[$i]["Count"];
                $totalRequest += (int)$rs3[$i]["Count"];
            }

            echo "<tr>
                <td>TOTAL</td>
                <td></td>
                <td><b>" . $totalLogin . "</b></td>
                <td><b>" . $totalGavevalg . "</b></td>
                <td><b>" . $totalRequest . "</b></td>
            </tr>";
            echo "</table>";
        } else {
            echo "Unauthorized access";
        }
    }

    /**
     * Display detailed login statistics
     */
    public function loginStatsDev()
    {
        $totalLogin = 0;
        $totalGavevalg = 0;
        $totalRequest = 0;
        $totalLoginError = 0;
        $totalError = 0;

        echo "<script>setTimeout(function() {
            location.reload();
        }, 600000);</script>";

        if (isset($_GET["token"]) && $_GET["token"] == "saddsfsdflkfj489fyth") {
            echo "<h3>CPU in percent of cores used (5 min avg):<u> " . $this->get_server_cpu_usage() . "</u></h3>";

            // Get login stats
            $sql = "SELECT
                DAY(`created_datetime`) as Day,
                HOUR(`created_datetime`) as Hour,
                COUNT(*) as Count
            FROM `system_log`
            WHERE `controller` LIKE 'login'
                AND `action` LIKE 'loginShopUserByToken'
                AND `created_datetime` > SUBDATE(NOW(),1)
            GROUP BY DAY(created_datetime), HOUR(created_datetime)
            ORDER BY `id` DESC";
            $rs = Dbsqli::getSql2($sql);

            // Get order stats
            $sql2 = "SELECT
                DAY(`order_timestamp`) as Day,
                HOUR(`order_timestamp`) as Hour,
                COUNT(*) as Count
            FROM `order`
            WHERE `order_timestamp` > SUBDATE(NOW(),1)
            GROUP BY DAY(`order_timestamp`), HOUR(`order_timestamp`)
            ORDER BY `id` DESC";
            $rs2 = Dbsqli::getSql2($sql2);

            // Get request stats
            $sql3 = "SELECT
                DAY(`created_datetime`) as Day,
                HOUR(`created_datetime`) as Hour,
                COUNT(*) as Count
            FROM `system_log`
            WHERE `created_datetime` > SUBDATE(NOW(),1)
                AND `action` NOT LIKE 'loginStats'
            GROUP BY DAY(created_datetime), HOUR(created_datetime)
            ORDER BY `id` DESC";
            $rs3 = Dbsqli::getSql2($sql3);

            // Get login error stats
            $sql4 = "SELECT
                DAY(`created_datetime`) as Day,
                HOUR(`created_datetime`) as Hour,
                COUNT(*) as Count
            FROM `system_log`
            WHERE `controller` LIKE 'login'
                AND `action` LIKE 'loginShopUserByToken'
                AND committed = 0
                AND `created_datetime` > SUBDATE(NOW(),1)
            GROUP BY DAY(created_datetime), HOUR(created_datetime)
            ORDER BY `id` DESC";
            $rs4 = Dbsqli::getSql2($sql4);

            // Get error stats
            $sql5 = "SELECT
                DAY(`created_datetime`) as Day,
                HOUR(`created_datetime`) as Hour,
                COUNT(*) as Count
            FROM `system_log`
            WHERE `created_datetime` > SUBDATE(NOW(),1)
                AND `action` NOT LIKE 'loginStats'
                AND committed = 0
            GROUP BY DAY(created_datetime), HOUR(created_datetime)
            ORDER BY `id` DESC";
            $rs5 = Dbsqli::getSql2($sql5);

            // Output statistics table with improved formatting
            echo '<div class="stats-table-container">';
            echo '<table class="stats-table">
                <thead>
                    <tr>
                        <th>Dag</th>
                        <th>Time</th>
                        <th>Login</th>
                        <th>Gavevalg</th>
                        <th>Request</th>
                        <th>Login error</th>
                        <th>Error</th>
                    </tr>
                </thead>
                <tbody>';

            for ($i = 0; $i < $this->sizeOfArray($rs); $i++) {
                $loginError = 0;
                $error = 0;

                try {
                    if (isset($rs4[$i]["Count"])) {
                        $loginError = $rs4[$i]["Count"];
                    }
                } catch (Exception $e) {
                    // Ignore exception
                }

                try {
                    if (isset($rs5[$i]["Count"])) {
                        $error = $rs5[$i]["Count"];
                    }
                } catch (Exception $e) {
                    // Ignore exception
                }

                $rs2Count = isset($rs2[$i]["Count"]) ? $rs2[$i]["Count"] : 0;
                $rs3Count = isset($rs3[$i]["Count"]) ? $rs3[$i]["Count"] : 0;

                echo "<tr>
                    <td>" . $rs[$i]["Day"] . "</td>
                    <td>" . $rs[$i]["Hour"] . "</td>
                    <td>" . $rs[$i]["Count"] . "</td>
                    <td>" . $rs2Count . "</td>
                    <td>" . $rs3Count . "</td>
                    <td>" . $loginError . "</td>
                    <td>" . $error . "</td>
                </tr>";

                $totalLogin += (int)$rs[$i]["Count"];
                $totalGavevalg += (int)$rs2Count;
                $totalRequest += (int)$rs3Count;
                $totalLoginError += (int)$loginError;
                $totalError += (int)$error;
            }

            echo "<tr class='total-row'>
                <td>TOTAL</td>
                <td></td>
                <td><b>" . $totalLogin . "</b></td>
                <td><b>" . $totalGavevalg . "</b></td>
                <td><b>" . $totalRequest . "</b></td>
                <td><b>" . $totalLoginError . "</b></td>
                <td><b>" . $totalError . "</b></td>
            </tr>";
            echo "</tbody></table>";
            echo "</div>";
        } else {
            echo '<div class="error-message">Unauthorized access</div>';
        }
    }

    /**
     * Get server CPU usage
     */
    private function get_server_cpu_usage()
    {
        $exec_loads = sys_getloadavg();
        $exec_cores = trim(shell_exec("grep -P '^processor' /proc/cpuinfo|wc -l"));
        return round($exec_loads[1] / ($exec_cores + 1) * 100, 0) . '%';
    }

    /**
     * Display sales statistics
     */
    public function saleStats()
    {
        if (isset($_GET["token"]) && $_GET["token"] == "saddsfsdflkfj489fyth") {
            $sql = "SELECT
                DAY(`created_datetime`) as Day,
                HOUR(`created_datetime`) as Hour,
                COUNT(*) as Count
            FROM `system_log`
            WHERE `controller` LIKE 'login'
                AND `action` LIKE 'loginShopUserByToken'
                AND `created_datetime` > SUBDATE(NOW(),1)
            GROUP BY DAY(created_datetime), HOUR(created_datetime)
            ORDER BY `id` DESC";

            $rs = Dbsqli::getSql2($sql);

            echo '<div class="stats-table-container">';
            echo '<table class="stats-table">
                <thead>
                    <tr>
                        <th>Dag</th>
                        <th>Time</th>
                        <th>Antal</th>
                    </tr>
                </thead>
                <tbody>';

            foreach ($rs as $ele) {
                echo "<tr>
                    <td>" . $ele["Day"] . "</td>
                    <td>" . $ele["Hour"] . "</td>
                    <td>" . $ele["Count"] . "</td>
                </tr>";
            }

            echo "</tbody></table>";
            echo "</div>";
        } else {
            echo '<div class="error-message">Unauthorized access</div>';
        }
    }

    /**
     * Get total card count
     */
    public function totalCard()
    {
        $sqlNotSelect = "SELECT COUNT(*) as antal
            FROM `shop_user`
            WHERE `shop_id` IN (select shop_id from `cardshop_settings`)
                AND blocked = 0";

        $rsNotSelect = Dbsqli::getSql2($sqlNotSelect);
        echo $rsNotSelect[0]["antal"];
    }

    /**
     * Export statistics as CSV file
     */
    public function getCsvFile()
    {
        if (isset($_GET["alias"]) && $_GET["alias"] == 1) {
            $this->loadAliasStats($_GET["deadline"], $_GET["shop_id"], true);
        } else {
            $this->loadStats($_GET["deadline"], $_GET["shop_id"], true);
        }
    }

    /**
     * Get all statistics
     */
    public function getAllStats()
    {
        // Hardcoded DB percentages for 2023
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
            "1832_440" => "N/A",
            "8271" => "N/A",
        ];

        // Hardcoded DB percentages for 2022
        $dg2022 = [
            "52" => "49.06%",
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
            "1832_440" => "N/A",
        ];

        $shopId = $_POST["shop_id"];

        // Check if the key exists in the arrays
        $result2022 = isset($dg2022[$shopId]) ? $dg2022[$shopId] : "ingen data";
        $result2023 = isset($dg2023[$shopId]) ? $dg2023[$shopId] : "ingen data";

        if ($_POST["shop_id"] == "0") {
            // Show all shops data
            $this->loadStats_org($_POST["deadline"], $_POST["shop_id"]);
        } else {
            if ($_POST["alias"] == 0) {
                // Show normal stats with DB percentages
                echo "<label><b>Total DG 2023: " . $result2023 . "</b></label><br>";
                echo "<label><b>Total DG 2022: " . $result2022 . "</b></label><br>";
                $this->loadStats($_POST["deadline"], $_POST["shop_id"]);
            } else {
                // Show alias stats
                $this->loadAliasStats($_POST["deadline"], $_POST["shop_id"]);
            }
        }
    }

    /**
     * Get deadlines list for a shop
     */
    public function getDeadlines()
    {
        $shopID = $_POST["shop_id"];
        $shopID = $shopID == "1832_440" ? "1832" : $shopID;

        // Get deadlines from database
        $deadlinesRs = Dbsqli::getSql2("SELECT DISTINCT expire_date, expire_date.`week_no`
            FROM expire_date
            WHERE expire_date IN (
                SELECT DISTINCT expire_date
                FROM shop_user
                WHERE shop_id = " . $shopID . "
            )
            ORDER BY expire_date");

        // Build select dropdown
        $html = '<select id="deadline" onchange="newDeadline()">
            <option value="alle">alle</option>';

        foreach ($deadlinesRs as $deadline) {
            $html .= '<option value="' . $deadline["expire_date"] . '">' .
                $deadline["expire_date"] . ', uge ' . $deadline["week_no"] .
                '</option>';
        }

        $html .= '</select>';

        echo $html;
    }

    /**
     * Load alias statistics
     */
    public function loadAliasStats($deadline, $shopID, $returnCsv = false)
    {
        if ($deadline == "alle") {
            $deadline = "";
        } else {
            $deadline = " and `expire_date` = '" . $deadline . "'";
        }

        $sql = "
            SELECT
                present.id,
                present_model.active as model_active,
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
                    shopuser_id IN (
                        SELECT id
                        FROM shop_user
                        WHERE shop_id = " . $shopID . "
                            AND `blocked` = 0
                            AND `shutdown` = 0
                            " . $deadline . "
                    )
                GROUP BY
                    present_model_id
                ) AS order_counts
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
                AND present.shop_id = " . $shopID . "
            GROUP BY
                present_model.model_id
            ORDER BY
                order_antal ASC";

        $rs = Dbsqli::getSql2($sql);
        $radomId = $this->generateRandomString();
        $html = "<table style='display:none' id='" . $radomId . "'>
            <thead>
                <tr>
                    <th>Varenr</th>
                    <th>Alias</th>
                    <th>Aktiv</th>
                    <th>Gave</th>
                    <th>Model</th>
                    <th>Antal valgte gaver</th>
                    <th>Reserverede</th>
                </tr>
            </thead>
            <tbody>";

        $csv = [];

        foreach ($rs as $dataRow) {
            $presentActive = $dataRow["model_active"] == 0 ? "Aktiv" : "Ikke aktiv";
            if ($dataRow["shop_present_active"] == 0 && $presentActive == "Aktiv") {
                $presentActive = "Hele gave lukket";
            }

            $prefix = "";
            $fullalias = $dataRow["fullalias"];

            $antalTal = preg_match_all('/\d/', $fullalias);

            // If there are less than two digits, add leading zeros
            if ($antalTal < 2) {
                $mangler = 2 - $antalTal;
                $fullalias = str_repeat("0", $mangler) . $fullalias;
            }
            $prefix = self::$metadata[$shopID]["valuealias"];

            $html .= "<tr>
                <td>" . $dataRow["model_present_no"] . "</td>
                <td>" . $prefix . $fullalias . "</td>
                <td>" . $presentActive . "</td>
                <td>" . $dataRow["model_name"] . "</td>
                <td>" . $dataRow["model_no"] . "</td>
                <td>" . $dataRow["order_antal"] . "</td>
                <td>" . $dataRow["quantity"] . "</td>
            </tr>";

            $csv[] = [
                $dataRow["model_present_no"],
                $prefix . $fullalias,
                $presentActive,
                utf8_decode($dataRow["model_name"]),
                utf8_decode($dataRow["model_no"]),
                $dataRow["order_antal"],
                $dataRow["quantity"]
            ];
        }

        $html .= "</tbody></table>";
        $html .= "<script>
            setTimeout(function() {
                $('#" . $radomId . "').DataTable({ 'pageLength': 500 });
                $('#" . $radomId . "').fadeIn(400);
            }, 400)
        </script>";

        if ($returnCsv) {
            $this->array_to_csv_download($csv, $filename = "export.csv", $delimiter = ";");
        } else {
            echo $html;
        }
    }

    /**
     * Load statistics
     */
    public function loadStats($deadline, $shopId, $returnCsv = false)
    {
        $cardValueConditions = [
            '1832' => "HAVING `card_values` = '400'",
            '1832_440' => "HAVING `card_values` = '400,440'"
        ];

        $cardValueConditionsCount = [
            '1832' => " and `card_values` = '400' ",
            '1832_440' => " and `card_values` = '400,440' "
        ];

        $havingSE400440Count = isset($cardValueConditionsCount[$shopId]) ? $cardValueConditionsCount[$shopId] : '';
        $havingSE400440 = isset($cardValueConditions[$shopId]) ? $cardValueConditions[$shopId] : '';
        $shopId = $shopId == '1832_440' ? 1832 : $shopId;

        $csv = [];
        $expireDateSql = "";

        if ($deadline != "alle") {
            $expireDateSql = "and shop_user.expire_date = '" . $deadline . "'";
        }

        // Get all cards
        if ($shopId == "0") {
            $sqlNotSelect = "SELECT COUNT(*) as antal
                FROM `shop_user`
                WHERE `shop_id` IN (SELECT shop_id FROM `cardshop_settings`)
                    AND company_order_id NOT IN (
                        SELECT id
                        FROM company_order
                        WHERE is_cancelled = 1 OR order_state IN(7,8,20)
                    )
                    AND company_order_id NOT IN (
                        SELECT id
                        FROM company_order
                        WHERE company_id IN (" . implode(',', $this->excludedCompanyIds) . ")
                    )
                    AND blocked = 0
                    AND is_demo = 0
                    " . $expireDateSql . "
                ORDER BY antal";
        } else {
            $sqlNotSelect = "SELECT COUNT(*) as antal, shop_user.card_values
                FROM `shop_user`
                WHERE `shop_id` = " . $shopId . "
                    AND company_order_id NOT IN (
                        SELECT id
                        FROM company_order
                        WHERE is_cancelled = 1 OR order_state IN(7,8,20)
                    )
                    AND company_order_id NOT IN (
                        SELECT id
                        FROM company_order
                        WHERE company_id IN (" . implode(',', $this->excludedCompanyIds) . ")
                    )
                    AND company_order_id IN (
                        SELECT id
                        FROM company_order
                        WHERE company_order.`created_datetime` > '2024-01-01 16:47:14'
                            AND company_order.`created_datetime` <= '2024-12-24 23:47:14'
                    )
                    AND blocked = 0
                    AND is_demo = 0
                    " . $expireDateSql . $havingSE400440Count . "
                ORDER BY antal";
        }

        $rsNotSelect = Dbsqli::getSql2($sqlNotSelect);

        // Handle no data case
        if ($this->sizeOfArray($rsNotSelect) <= 0) {
            echo "ingen data";
            return;
        }

        $allCard = $rsNotSelect[0]["antal"];

        // Set language based on shop
        $l = 1;
        $noShops = array("272", "57", "58", "59", "574", "2550", "4740");

        if (in_array($shopId, $noShops)) {
            $l = 4;
        }

        // Get detailed statistics
        if ($shopId == "0") {
            $sql = "
                SELECT
                    present_model.model_name,
                    present_model.model_no,
                    present_model.model_present_no,
                    COUNT(`present_model_id`) as antal,
                    shop_user.card_values
                FROM `order`
                INNER JOIN present_model ON present_model.model_id = `order`.`present_model_id`
                INNER JOIN shop_user ON shop_user.id = `order`.`shopuser_id`
                WHERE
                    `shop_is_gift_certificate` = 1
                    AND present_model.language_id = 1
                    AND company_order_id NOT IN (
                        SELECT id
                        FROM company_order
                        WHERE is_cancelled = 1 OR order_state IN(7,8,20)
                    )
                    AND company_order_id NOT IN (
                        SELECT id
                        FROM company_order
                        WHERE company_id IN (" . implode(',', $this->excludedCompanyIds) . ")
                    )
                    AND shop_user.blocked = 0
                    AND shop_user.is_demo = 0
                    " . $expireDateSql . " " . $havingSE400440 . "
                GROUP BY present_model.model_present_no
                ORDER BY present_model.model_present_no, present_model.model_name, present_model.model_no";
        } else {
            $sql = "
                SELECT
                    present_model.model_name,
                    present_model.model_no,
                    present_model.model_present_no,
                    COUNT(`present_model_id`) as antal,
                    navision_item.standard_cost,
                    cardshop_settings.card_price,
                    present_reservation.quantity,
                    present_model.active as model_active,
                    shop_user.card_values
                FROM `order`
                INNER JOIN present_model ON present_model.model_id = `order`.`present_model_id`
                INNER JOIN shop_user ON shop_user.id = `order`.`shopuser_id`
                LEFT JOIN navision_item ON present_model.model_present_no = navision_item.no
                LEFT JOIN cardshop_settings ON `order`.shop_id = cardshop_settings.shop_id
                LEFT JOIN present_reservation ON present_model.model_id = present_reservation.model_id
                LEFT JOIN shop_present ON present_model.present_id = shop_present.present_id
                WHERE
                    `shop_is_gift_certificate` = 1
                    AND present_model.language_id = 1
                    AND company_order_id NOT IN (
                        SELECT id
                        FROM company_order
                        WHERE is_cancelled = 1 OR order_state IN(7,8,20)
                    )
                    AND (navision_item.language_id = " . $l . " OR navision_item.language_id IS NULL)
                    AND (navision_item.blocked = 0 OR navision_item.blocked IS NULL)
                    AND (shop_user.blocked = 0 OR shop_user.blocked IS NULL)
                    AND company_order_id NOT IN (
                        SELECT id
                        FROM company_order
                        WHERE company_id IN (" . implode(',', $this->excludedCompanyIds) . ")
                    )
                    AND shop_user.is_demo = 0
                    AND `order`.shop_id = " . $shopId . "
                    " . $expireDateSql . "
                GROUP BY present_model.model_present_no " . $havingSE400440 . "
                ORDER BY present_model.model_present_no, present_model.model_name, present_model.model_no";
        }

        $rs = Dbsqli::getSql2($sql);

        // Calculate totals
        $total = 0;
        $totalProcent = 0;
        $totalDbCalc = 0;
        $countItems = 0;
        $hasZoroStandardPris = false;

        foreach ($rs as $dataRow) {
            $total += (int)$dataRow["antal"];
        }

        $notSelect = $allCard - $total;
        $radomId = $this->generateRandomString();

        // Build HTML output
        $html = "<div class='statsContent'>";
        $html .= "<p>Antal der mangler at vælge: " . $notSelect . "</p>";
        $html .= "<p>Total antal kort: " . $allCard . "</p>";

        $html .= "<table style='display:none' id='" . $radomId . "'>
            <thead>
                <tr>
                    <th>Varenr</th>
                    <th>Aktiv</th>
                    <th>Gave</th>
                    <th>Model</th>
                    <th>Antal valgte gaver</th>
                    <th>Reserverede</th>
                    <th>Antal i %</th>
                    <th>Fremskrevet værdi</th>
                    <th>Kost Pris</th>
                    <th>DB stk</th>
                    <th>DB total</th>
                    <th>DB %</th>
                </tr>
            </thead>
            <tbody>";

        if (count($rs) == 0) {
            echo "Ingen data";
            return;
        }

        foreach ($rs as $dataRow) {
            $dataRow["card_price"] = isset($dataRow["card_price"]) ? $dataRow["card_price"] : 0;
            $procent = ($dataRow["antal"] / $total) * 100;
            $procent = round($procent, 2);
            $totalProcent += $procent;
            $guess = ($notSelect * $procent) / 100;
            $guess = round($guess) + $dataRow["antal"];

            // DB calculation
            $seShops = array("1832", "1981", "2558", "4793", "5117","8271");

            if (in_array($shopId, $seShops)) {
                $dataRow["card_price"] = $dataRow["card_price"] * 0.65;
            }

            $salgspris = ($dataRow["card_price"] / 100);

            if (!isset($dataRow["standard_cost"]) || $dataRow["standard_cost"] == null) {
                $dataRow["standard_cost"] = ($dataRow["card_price"] / 100);
            }

            if ($dataRow["standard_cost"] == 0) {
                $hasZoroStandardPris = true;
                $dataRow["standard_cost"] = $salgspris;
            } else {
                $countItems += $dataRow["antal"];
            }

            // Calculate DB
            $dbCalcUnit = (($dataRow["card_price"] / 100) - $dataRow["standard_cost"]);
            $dbCalcTotal = round((($dataRow["card_price"] / 100) - $dataRow["standard_cost"]) * $dataRow["antal"]);
            $totalDbCalc += $dbCalcTotal;
            $dbProcent = round(($dbCalcUnit * 100) / ($dataRow["card_price"] / 100), 2);
            $totalSale = round($salgspris * $countItems);
            $presentActive = isset($dataRow["model_active"]) && $dataRow["model_active"] == 0 ? "Aktiv" : "Ikke aktiv";
            $quantity = isset($dataRow["quantity"]) ? $dataRow["quantity"] : 0;

            $html .= "<tr>
                <td>" . $dataRow["model_present_no"] . "</td>
                <td>" . $presentActive . "</td>
                <td>" . $dataRow["model_name"] . "</td>
                <td>" . $dataRow["model_no"] . "</td>
                <td>" . $dataRow["antal"] . "</td>
                <td>" . $quantity . "</td>
                <td>" . $procent . "</td>
                <td>" . $guess . "</td>
                <td>" . $dataRow["standard_cost"] . "</td>
                <td>" . $dbCalcUnit . "</td>
                <td>" . $dbCalcTotal . "</td>
                <td>" . $dbProcent . "%</td>
            </tr>";

            $csv[] = [
                $dataRow["model_present_no"],
                $presentActive,
                utf8_decode($dataRow["model_name"]),
                utf8_decode($dataRow["model_no"]),
                $dataRow["antal"],
                $quantity,
                $procent,
                $guess
            ];
        }

        $totalSale = round($salgspris * $countItems);
        $totalDBCalcProcent = $totalSale > 0 ? round($totalDbCalc * 100 / $totalSale, 2) : 0;
        $cssRed = $hasZoroStandardPris ? "css-red" : "";

        $html .= "</tbody></table>";
        $html .= "<div class='css-db " . $cssRed . "'>
            <div style='font-size: 10px'>Hvis teksten er rød, skyldes det at der i listen er varer med ej defineret DB</div>
            <table>
                <tr><td>Total DB</td><td>" . $totalSale . "</td></tr>
                <tr><td>Total DG %</td><td>" . $totalDBCalcProcent . " %</td></tr>
            </table>
        </div>";

        $html .= "<br><div>Totale antal gaver: <b>" . $total . "</b> (" . $totalProcent . "%)</div></div>";
        $html .= "<script>
            setTimeout(function() {
                $('#" . $radomId . "').DataTable({ 'pageLength': 500 });
                $('#" . $radomId . "').fadeIn(400);
            }, 400)
        </script>";

        if ($returnCsv) {
            $this->array_to_csv_download($csv, $filename = "export.csv", $delimiter = ";");
        } else {
            echo $html;
        }
    }

    /**
     * Original stats loading function (preserved for compatibility)
     */
    public function loadStats_org($deadline, $shopId, $returnCsv = false)
    {
        $csv = [];
        $expireDateSql = "";

        if ($deadline != "alle") {
            $expireDateSql = "and shop_user.expire_date = '" . $deadline . "'";
        }

        if ($shopId == "0") {
            $sqlNotSelect = "SELECT COUNT(*) as antal
                FROM `shop_user`
                WHERE
                    `shop_id` IN (SELECT shop_id FROM `cardshop_settings`)
                    AND company_order_id NOT IN (
                        SELECT id
                        FROM company_order
                        WHERE is_cancelled = 1 OR order_state IN(7,8,20)
                    )
                    AND company_order_id NOT IN (
                        SELECT id
                        FROM company_order
                        WHERE company_id IN (" . implode(',', $this->excludedCompanyIds) . ")
                    )
                    AND blocked = 0
                    AND is_demo = 0
                    AND shutdown = 0
                    " . $expireDateSql . "
                ORDER BY antal";
        } else {
            $sqlNotSelect = "SELECT COUNT(*) as antal
                FROM `shop_user`
                WHERE `shop_id` = " . $shopId . "
                    AND company_order_id NOT IN (
                        SELECT id
                        FROM company_order
                        WHERE is_cancelled = 1
                    )
                    AND blocked = 0
                    AND is_demo = 0
                    AND shutdown = 0
                    AND company_order_id NOT IN (
                        SELECT id
                        FROM company_order
                        WHERE company_id IN (" . implode(',', $this->excludedCompanyIds) . ")
                    )
                    " . $expireDateSql . "
                ORDER BY antal";
        }

        $rsNotSelect = Dbsqli::getSql2($sqlNotSelect);

        if ($this->sizeOfArray($rsNotSelect) <= 0) {
            echo "ingen data";
            return;
        }

        $allCard = $rsNotSelect[0]["antal"];

        if ($shopId == "0") {
            $sql = "
                SELECT
                    present_model.model_name,
                    present_model.model_no,
                    present_model.model_present_no,
                    COUNT(`present_model_id`) as antal
                FROM `order`
                INNER JOIN present_model ON present_model.model_id = `order`.`present_model_id`
                INNER JOIN shop_user ON shop_user.id = `order`.`shopuser_id`
                WHERE
                    `shop_is_gift_certificate` = 1
                    AND present_model.language_id = 1
                    AND company_order_id NOT IN (
                        SELECT id
                        FROM company_order
                        WHERE is_cancelled = 1 OR order_state IN(7,8,20)
                    )
                    AND company_order_id NOT IN (
                        SELECT id
                        FROM company_order
                        WHERE company_id IN (" . implode(',', $this->excludedCompanyIds) . ")
                    )
                    AND shop_user.blocked = 0
                    AND shop_user.is_demo = 0
                    " . $expireDateSql . "
                GROUP BY present_model.model_present_no
                ORDER BY present_model.model_present_no, present_model.model_name, present_model.model_no";
        } else {
            $sql = "
                SELECT
                    present_model.model_name,
                    present_model.model_no,
                    present_model.model_present_no,
                    COUNT(`present_model_id`) as antal
                FROM `order`
                INNER JOIN present_model ON present_model.model_id = `order`.`present_model_id`
                INNER JOIN shop_user ON shop_user.id = `order`.`shopuser_id`
                WHERE
                    `shop_is_gift_certificate` = 1
                    AND present_model.language_id = 1
                    AND company_order_id NOT IN (
                        SELECT id
                        FROM company_order
                        WHERE is_cancelled = 1
                    )
                    AND company_order_id NOT IN (
                        SELECT id
                        FROM company_order
                        WHERE company_id IN (" . implode(',', $this->excludedCompanyIds) . ")
                    )
                    AND shop_user.blocked = 0
                    AND shop_user.is_demo = 0
                    AND `order`.shop_id = " . $shopId . "
                    " . $expireDateSql . "
                GROUP BY present_model.model_present_no
                ORDER BY present_model.model_present_no, present_model.model_name, present_model.model_no";
        }

        $rs = Dbsqli::getSql2($sql);

        $total = 0;
        $totalProcent = 0;

        foreach ($rs as $dataRow) {
            $total += (int)$dataRow["antal"];
        }

        $notSelect = $allCard - $total;
        $radomId = $this->generateRandomString();

        $html = "<div class='statsContent'>";
        $html .= "<p>Antal der mangler at vælge: " . $notSelect . "</p>";
        $html .= "<p>Total antal kort: " . $allCard . "</p>";

        $html .= "<table style='display:none' id='" . $radomId . "'>
            <thead>
                <tr>
                    <th>Varenr</th>
                    <th>Gave</th>
                    <th>Model</th>
                    <th>Antal valgte gaver</th>
                    <th>Antal i %</th>
                    <th>Fremskrevet værdi</th>
                </tr>
            </thead>
            <tbody>";

        foreach ($rs as $dataRow) {
            $procent = ($dataRow["antal"] / $total) * 100;
            $procent = round($procent, 2);
            $totalProcent += $procent;
            $guess = ($notSelect * $procent) / 100;
            $guess = round($guess) + $dataRow["antal"];

            // Empty for org view (for display purposes only)
            $guess = "";
            $procent = "";

            $html .= "<tr>
                <td>" . $dataRow["model_present_no"] . "</td>
                <td>" . $dataRow["model_name"] . "</td>
                <td>" . $dataRow["model_no"] . "</td>
                <td>" . $dataRow["antal"] . "</td>
                <td>" . $procent . "</td>
                <td>" . $guess . "</td>
            </tr>";

            $csv[] = [
                $dataRow["model_present_no"],
                utf8_decode($dataRow["model_name"]),
                utf8_decode($dataRow["model_no"]),
                $dataRow["antal"],
                $procent,
                $guess
            ];
        }

        $html .= "</tbody></table>";
        $html .= "<br><div>Totale antal gaver: <b>" . $total . "</b> (" . $totalProcent . "%)</div></div>";
        $html .= "<script>
            setTimeout(function() {
                $('#" . $radomId . "').DataTable({ 'pageLength': 500 });
                $('#" . $radomId . "').fadeIn(400);
            }, 400)
        </script>";

        if ($returnCsv) {
            $this->array_to_csv_download($csv, $filename = "export.csv", $delimiter = ";");
        } else {
            echo $html;
        }
    }

    /**
     * Export data as CSV
     */
    function array_to_csv_download($array, $filename = "export.csv", $delimiter = ";")
    {
        // Open memory file
        $f = fopen('php://memory', 'w');

        // Add data
        foreach ($array as $line) {
            fputcsv($f, $line, $delimiter);
        }

        // Reset pointer
        fseek($f, 0);

        // Set headers
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '";');

        // Output file
        fpassthru($f);
        exit;
    }

    /**
     * Generate a random string (for use as table IDs)
     */
    function generateRandomString($length = 10)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        return substr(str_shuffle(str_repeat($chars, ceil($length / strlen($chars)))), 1, $length);
    }

    /**
     * Helper function to safely get array size
     */
    private function sizeOfArray($arr)
    {
        if (is_array($arr)) {
            return count($arr);
        }
        return 0;
    }

    /**
     * Calculate DB statistics for Danish shops
     */
    public function dbCalcV2DK()
    {
        $this->dbCalcV2("52,53,2395,54,55,56,575,2548,290,310", 1);
    }

    /**
     * Calculate DB statistics for Norwegian shops
     */
    public function dbCalcV2NO()
    {
        $this->dbCalcV2("272,57,58,59,574,2550,2549,4740", 4);
    }

    /**
     * Calculate DB statistics for Swedish shops
     */
    public function dbCalcV2SE()
    {
        $this->dbCalcV2("1832,1981,2558,4793,5117,8271", 5);
    }

    /**
     * Calculate DB statistics for a list of shops
     */
    public function dbCalcV2($shopList, $l)
    {
        $end = "all";
        $csv = "season;shop_id;cardname;cardprice;totalSale;totalDb;dbProcent;Mangler varenr. \n";
        $sql = "SELECT shop_id FROM `cardshop_settings` WHERE shop_id IN (" . $shopList . ")";
        $rsShopList = Dbsqli::getSql2($sql);

        foreach ($rsShopList as $shop) {
            $dbShopData = $this->dbCalcGetRawData($shop["shop_id"], $l);
            $res2022 = $this->dbCalc($dbShopData);
            $res2022["totalSale"] = number_format($res2022["totalSale"], 2, ',', '.');
            $res2022["totalDb"] = number_format($res2022["totalDb"], 2, ',', '.');

            $res2021 = $this->dbCalcV22021($shop["shop_id"], "all", $l);
            $res2021["season"] = "2021";
            $res2021["totalSale"] = number_format($res2021["totalSale"], 2, ',', '.');
            $res2021["totalDb"] = number_format($res2021["totalDb"], 2, ',', '.');

            $csv .= implode(';', $res2022);
            $csv .= "\n";
            $csv .= implode(';', $res2021);
            $csv .= "\n";
        }

        header('Content-Type: application/csv');
        header('Content-Disposition: attachement; filename="data.csv"');
        echo $csv;
        exit();
    }

    /**
     * Calculate DB for 2021 data
     */
    public function dbCalcV22021($shopID, $end, $l)
    {
        $dato2021 = $this->getSameDayInWeekLastYearDB(true, 1);
        $end = "all";
        $url = 'https://gavefabrikken.dk/gavefabrikken_backend/component/db2021.php?shopID=' . $shopID . "&end=" . $end . "&l=" . $l;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $res = curl_exec($curl);
        curl_close($curl);

        $dbShopData = json_decode($res, true);
        return $this->dbCalc($dbShopData, $l);
    }

    /**
     * Get same day in week for last year DB calculation
     */
    public function getSameDayInWeekLastYearDB($time = true, $correction = 0)
    {
        $today = new \DateTime();
        $year = (int)$today->format('Y');
        $week = (int)$today->format('W');
        $day = (int)$today->format('w');

        if ($day == 0) {
            $day = 7;
        }

        $sameDayLastYear = new \DateTime();
        $sameDayLastYear->setISODate($year - $correction, $week, $day);

        if ($time) {
            return $sameDayLastYear->format('Y-m-d H:i:s');
        } else {
            return $sameDayLastYear->format('Y-m-d');
        }
    }

    /**
     * Calculate DB statistics
     */
    public function dbCalc($data)
    {
        $hasZoroStandardPris = false;
        $totalDb = 0;
        $countItems = 0;
        $salgspris = 0;
        $cardname = "";
        $orgPris = 0;
        $shopId = 0;

        if (is_array($data)) {
            foreach ($data as $shop) {
                $shopId = $shop["shop_id"];
                $orgPris = ($shop["card_price"] / 100);
                $seShops = array("1832", "1981", "2558", "4793", "5117","8271");

                if (in_array($shop["shop_id"], $seShops)) {
                    $shop["card_price"] = $shop["card_price"] * 0.7;
                }

                $cardname = $shop["concept_code"];
                $salgspris = ($shop["card_price"] / 100);

                if (!isset($shop["standard_cost"]) || $shop["standard_cost"] == 0) {
                    $hasZoroStandardPris = true;
                    $shop["standard_cost"] = $salgspris;
                } else {
                    $countItems += $shop["antal"];
                }

                $dbCalcUnit = (($shop["card_price"] / 100) - $shop["standard_cost"]) * $shop["antal"];
                $totalDb += $dbCalcUnit;
            }
        }

        $totalSale = round($salgspris * $countItems);
        $totalDBCalcProcent = ($totalSale > 0) ? round($totalDb * 100 / $totalSale, 2) : 0;
        $totalDBCalcProcent = str_replace(',', '.', $totalDBCalcProcent);
        $totalDb = round($totalDb);

        return array(
            "season" => "2022",
            "shop_id" => $shopId,
            "cardname" => $cardname,
            "cardprice" => $orgPris,
            "totalSale" => $totalSale,
            "totalDb" => $totalDb,
            "dbProcent" => $totalDBCalcProcent,
            "hasZoroStandardPris" => $hasZoroStandardPris ? "Ja" : "Nej"
        );
    }

    /**
     * Get raw data for DB calculation
     */
    public function dbCalcGetRawData($shop_id, $l)
    {
        $start = "2022-04-01 16:47:14";
        $slut = "2022-12-24 16:47:14";

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
            `order`.`shop_id` = " . $shop_id . " AND
            navision_item.language_id = " . $l . " AND
            navision_item.blocked = 0 AND
            `order`.`order_timestamp` > '" . $start . "' AND
            `order`.`order_timestamp` <= '" . $slut . "'
        GROUP BY
            `present_model_id`";

        return Dbsqli::getSql2($sql);
    }
}
?>
<script>
/**
 * Gavefabrikken Statistics Controller
 * Handles client-side functionality for the statistics dashboard
 */

$(document).ready(function() {
    // Tab navigation
    $('.tab-btn').on('click', function() {
        $('.tab-btn').removeClass('active');
        $(this).addClass('active');

        const tabId = $(this).data('tab');
        $('.tab-content').removeClass('active');
        $('#' + tabId).addClass('active');
    });

    // Toggle between normal and alias stats
    $('#normal-stats, #alias-stats').on('click', function() {
        $('.toggle-btn').removeClass('active');
        $(this).addClass('active');
        loadStats();
    });

    // Populate shop selector based on country
    const shopGroups = {
        'dk': [
            {id: 7121, name: 'JGV'},
            {id: 52, name: 'JK5'},
            {id: 4668, name: 'JK7'},
            {id: 53, name: 'G8'},
            {id: 2395, name: 'G9'},
            {id: 54, name: '4'},
            {id: 55, name: '5'},
            {id: 56, name: '6'},
            {id: 290, name: '2'},
            {id: 310, name: '3'},
            {id: 575, name: 'D7'},
            {id: 4662, name: 'D9'},
            {id: 2548, name: 'GR'},
            {id: 2961, name: 'L1'},
            {id: 2960, name: 'L4'},
            {id: 2962, name: 'L6'},
            {id: 2963, name: 'L8'}
        ],
        'no': [
            {id: 272, name: '3'},
            {id: 57, name: '4'},
            {id: 58, name: '6'},
            {id: 59, name: '8'},
            {id: 574, name: '1'},
            {id: 2550, name: '2'},
            {id: 4740, name: '7'},
            {id: 2549, name: '9'}
        ],
        'se': [
            {id: 4793, name: 'S3'},
            {id: 1832, name: 'S4'},
            {id: '1832_440', name: 'S4-440'},
            {id: 5117, name: 'S6'},
            {id: 1981, name: 'S8'},
            {id: 2558, name: 'S12'},
            {id: 8271, name: 'S13'},

        ]
    };

    function updateShopSelector(country) {
        const $shopSelector = $('#shop-selector');
        $shopSelector.empty();
        $shopSelector.append('<option value="0">Alle Shops</option>');

        shopGroups[country].forEach(shop => {
            $shopSelector.append(`<option value="${shop.id}">${shop.name}</option>`);
        });
    }

    // Initialize shop selector
    updateShopSelector('dk');

    // Update shops when country changes
    $('#country-selector').on('change', function() {
        updateShopSelector($(this).val());
        loadDeadlines();
    });

    // Shop selector change event
    $('#shop-selector').on('change', function() {
        loadDeadlines();
    });

    // Load deadlines for selected shop
    function loadDeadlines() {
        const shopId = $('#shop-selector').val();

        if (shopId === '0') {
            $('#deadline-container').empty();
            return;
        }

        $('#deadline-container').html('<div class="loading-indicator"><div class="spinner"></div></div>');

        $.ajax({
            url: '?action=getDeadlines',
            method: 'POST',
            data: { shop_id: shopId },
            success: function(response) {
                $('#deadline-container').html('<label>Deadline</label>' + response);
                // Attach event to the new deadline selector
                $('#deadline').on('change', function() {
                    loadStats();
                });
            },
            error: function() {
                $('#deadline-container').html('<div class="error-message">Fejl ved indlæsning af deadlines</div>');
            }
        });
    }

    // Load statistics data
    function loadStats() {
        const shopId = $('#shop-selector').val();
        const deadline = $('#deadline') ? $('#deadline').val() : 'alle';
        const isAlias = $('#alias-stats').hasClass('active') ? 1 : 0;

        if (shopId === '0') {
            $('#stats-data-container').html('<div class="placeholder-message"><i class="fa fa-info-circle"></i><p>Vælg en shop for at se statistik</p></div>');
            return;
        }

        $('#stats-data-container').html('<div class="loading-indicator"><div class="spinner"></div></div>');

        $.ajax({
            url: '?action=getAllStats',
            method: 'POST',
            data: {
                shop_id: shopId,
                deadline: deadline,
                alias: isAlias
            },
            success: function(response) {
                $('#stats-data-container').html(response);

                // Initialize DataTable if it exists
                if ($.fn.DataTable && $('table').length) {
                    $('table').DataTable({
                        responsive: true,
                        pageLength: 25,
                        language: {
                            search: "Søg:",
                            lengthMenu: "Vis _MENU_ rækker pr. side",
                            info: "Viser _START_ til _END_ af _TOTAL_ rækker",
                            paginate: {
                                first: "Første",
                                last: "Sidste",
                                next: "Næste",
                                previous: "Forrige"
                            }
                        }
                    });
                }
            },
            error: function() {
                $('#stats-data-container').html('<div class="error-message">Fejl ved indlæsning af statistik</div>');
            }
        });
    }

    // DB calculation
    function calculateDB() {
        const country = $('#db-country-selector').val();
        let action = '';

        switch(country) {
            case 'dk':
                action = 'dbCalcV2DK';
                break;
            case 'no':
                action = 'dbCalcV2NO';
                break;
            case 'se':
                action = 'dbCalcV2SE';
                break;
        }

        $('#db-data-container').html('<div class="loading-indicator"><div class="spinner"></div></div>');

        $.ajax({
            url: '?action=' + action,
            method: 'POST',
            success: function(response) {
                $('#db-data-container').html(response);
            },
            error: function() {
                $('#db-data-container').html('<div class="error-message">Fejl ved beregning af DB statistik</div>');
            }
        });
    }

    // Load login statistics
    function loadLoginStats() {
        const period = $('#login-period-selector').val();

        $('#login-stats-container').html('<div class="loading-indicator"><div class="spinner"></div></div>');

        $.ajax({
            url: '?action=loginStats&token=saddsfsdflkfj489fyth',
            method: 'GET',
            data: { period: period },
            success: function(response) {
                $('#login-stats-container').html(response);
            },
            error: function() {
                $('#login-stats-container').html('<div class="error-message">Fejl ved indlæsning af login statistik</div>');
            }
        });
    }

    // Export CSV
    function exportCSV() {
        const shopId = $('#shop-selector').val();
        const isAlias = $('#alias-stats').hasClass('active') ? 1 : 0;

        window.location.href = `?action=getCsvFile&shop_id=${shopId}&alias=${isAlias}`;
    }

    // Export DB CSV
    function exportDBCSV() {
        const country = $('#db-country-selector').val();
        let action = '';

        switch(country) {
            case 'dk':
                action = 'dbCalcV2DK';
                break;
            case 'no':
                action = 'dbCalcV2NO';
                break;
            case 'se':
                action = 'dbCalcV2SE';
                break;
        }

        window.location.href = `?action=${action}`;
    }

    // Function to handle deadline change
    window.newDeadline = function() {
        loadStats();
    };

    // Attach event handlers
    $('#refresh-stats').on('click', loadStats);
    $('#export-csv').on('click', exportCSV);
    $('#calculate-db').on('click', calculateDB);
    $('#export-db-csv').on('click', exportDBCSV);
    $('#refresh-login-stats').on('click', loadLoginStats);

    // Initialize the country selector change
    $('#db-country-selector').on('change', function() {
        // Reset DB container
        $('#db-data-container').html('<div class="placeholder-message"><i class="fa fa-info-circle"></i><p>Vælg et land og klik på "Beregn DB" for at se statistik</p></div>');
    });

    // Initialize the login period selector change
    $('#login-period-selector').on('change', function() {
        loadLoginStats();
    });

    // Add DataTables initialization for existing tables
    if ($.fn.DataTable) {
        $('table').each(function() {
            if (!$.fn.DataTable.isDataTable(this)) {
                $(this).DataTable({
                    responsive: true,
                    pageLength: 25,
                    language: {
                        search: "Søg:",
                        lengthMenu: "Vis _MENU_ rækker pr. side",
                        info: "Viser _START_ til _END_ af _TOTAL_ rækker",
                        paginate: {
                            first: "Første",
                            last: "Sidste",
                            next: "Næste",
                            previous: "Forrige"
                        }
                    }
                });
            }
        });
    }
});
</script>

<style>

/**
 * Gavefabrikken Statistics Dashboard
 * Main stylesheet
 */

:root {
    --primary-color: #3498db;
    --secondary-color: #2c3e50;
    --accent-color: #e74c3c;
    --success-color: #2ecc71;
    --warning-color: #f39c12;
    --light-gray: #f5f5f5;
    --dark-gray: #333;
    --white: #fff;
    --box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    --transition: all 0.3s ease;
}

/* Reset and Base Styles */
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body {
    font-family: 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
    line-height: 1.6;
    color: var(--dark-gray);
    background-color: var(--light-gray);
}

.stats-container {
    max-width: 1300px;
    margin: 0 auto;
    padding: 20px;
}

/* Typography */
h1, h2, h3, h4, h5, h6 {
    font-weight: 600;
    margin-bottom: 1rem;
    color: var(--secondary-color);
}

p {
    margin-bottom: 1rem;
}

/* Header */
.stats-header {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
    background-color: var(--secondary-color);
    border-radius: 8px 8px 0 0;
    color: var(--white);
    margin-bottom: 0;
}

.stats-header h1 {
    margin: 0;
    color: var(--white);
    font-size: 24px;
    display: flex;
    align-items: center;
}

.stats-header h1 i {
    margin-right: 10px;
    font-size: 28px;
}

/* Tabs */
.stats-tabs {
    display: flex;
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    overflow-x: auto;
}

.tab-btn {
    padding: 15px 20px;
    background: none;
    border: none;
    cursor: pointer;
    font-size: 16px;
    font-weight: 500;
    color: #6c757d;
    border-bottom: 2px solid transparent;
    transition: all 0.3s ease;
    white-space: nowrap;
    min-width: 120px;
}

.tab-btn:hover {
    color: var(--primary-color);
    background-color: rgba(52, 152, 219, 0.05);
}

.tab-btn.active {
    color: var(--primary-color);
    border-bottom-color: var(--primary-color);
    background-color: rgba(52, 152, 219, 0.1);
}

/* Tab content */
.tab-content {
    display: none;
    padding: 20px;
    background-color: var(--white);
    border-radius: 0 0 8px 8px;
    box-shadow: var(--box-shadow);
}

.tab-content.active {
    display: block;
}

/* Filter panel */
.filter-panel {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 20px;
    padding: 15px;
    background-color: #f8f9fa;
    border-radius: 8px;
    box-shadow: var(--box-shadow);
}

.filter-group {
    flex: 1;
    min-width: 200px;
}

.filter-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
    color: #495057;
}

.filter-group select,
.filter-group input {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ced4da;
    border-radius: 4px;
    font-size: 14px;
    transition: var(--transition);
}

.filter-group select:focus,
.filter-group input:focus {
    border-color: var(--primary-color);
    outline: none;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.25);
}

.toggle-group {
    display: flex;
    border: 1px solid #ced4da;
    border-radius: 4px;
    overflow: hidden;
}

.toggle-btn {
    flex: 1;
    padding: 8px 12px;
    background-color: #fff;
    border: none;
    cursor: pointer;
    font-size: 14px;
    transition: all 0.2s;
}

.toggle-btn.active {
    background-color: var(--primary-color);
    color: #fff;
}

/* Action bar */
.action-bar {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.btn {
    padding: 10px 15px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    display: flex;
    align-items: center;
    transition: all 0.2s;
}

.btn i {
    margin-right: 8px;
}

.btn-primary {
    background-color: var(--primary-color);
    color: #fff;
}

.btn-primary:hover {
    background-color: #2980b9;
}

.btn-secondary {
    background-color: #6c757d;
    color: #fff;
}

.btn-secondary:hover {
    background-color: #5a6268;
}

/* Placeholder message */
.placeholder-message {
    padding: 40px;
    text-align: center;
    color: #6c757d;
}

.placeholder-message i {
    font-size: 48px;
    margin-bottom: 20px;
    color: var(--primary-color);
}

.placeholder-message p {
    font-size: 16px;
}

/* Loading indicator */
.loading-indicator {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 30px 0;
}

.spinner {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: 3px solid rgba(52, 152, 219, 0.3);
    border-top-color: var(--primary-color);
    animation: spin 1s infinite linear;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Error and success messages */
.error-message {
    padding: 15px;
    background-color: #f8d7da;
    color: #721c24;
    border-radius: 4px;
    margin-bottom: 20px;
}

.success-message {
    padding: 15px;
    background-color: #d4edda;
    color: #155724;
    border-radius: 4px;
    margin-bottom: 20px;
}

/* Stats content styling */
.statsContent {
    padding: 20px;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: var(--box-shadow);
}

.statsContent p {
    margin-bottom: 10px;
    font-size: 16px;
}

.statsContent table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
}

.statsContent th {
    background-color: var(--primary-color);
    color: white;
    padding: 12px 10px;
    text-align: left;
    font-weight: 500;
}

.statsContent td {
    padding: 10px;
    border-bottom: 1px solid #eee;
}

.statsContent tr:nth-child(even) {
    background-color: #f9f9f9;
}

.statsContent tr:hover {
    background-color: #f1f1f1;
}

/* Original styling classes for compatibility */
.flex-container {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 20px;
}

.v-flex {
    flex: 1;
    min-width: 120px;
    background-color: #fff;
    border-radius: 5px;
    box-shadow: var(--box-shadow);
    overflow: hidden;
}

.header-title {
    background-color: var(--primary-color);
    color: #fff;
    padding: 10px;
    font-weight: bold;
    text-align: center;
    font-size: 14px;
}

.v-flex div:not(.header-title) {
    padding: 8px 10px;
    text-align: center;
    border-bottom: 1px solid #eee;
}

.consept {
    font-weight: 400;
}

.total {
    background-color: var(--secondary-color) !important;
    color: #fff;
    font-weight: bold;
}

.css-red {
    color: var(--accent-color);
}

.css-db {
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: 4px;
    margin-top: 20px;
}

.css-db table {
    width: auto !important;
    margin: 10px 0 !important;
}

.css-db td {
    padding: 5px 15px 5px 0 !important;
    border: none !important;
    background: none !important;
}

/* DataTables customization */
.dataTables_wrapper .dataTables_length,
.dataTables_wrapper .dataTables_filter {
    margin-bottom: 15px;
}

.dataTables_wrapper .dataTables_length select,
.dataTables_wrapper .dataTables_filter input {
    padding: 5px 8px;
    border: 1px solid #ced4da;
    border-radius: 4px;
}

.dataTables_wrapper .dataTables_filter input {
    width: auto;
    margin-left: 8px;
}

.dataTables_wrapper .dataTables_length select {
    width: auto;
    margin: 0 5px;
}

.dataTables_wrapper .dataTables_paginate .paginate_button {
    padding: 5px 10px;
    border: 1px solid #ced4da;
    border-radius: 4px;
    margin: 0 2px;
}

.dataTables_wrapper .dataTables_paginate .paginate_button.current {
    background-color: var(--primary-color) !important;
    border-color: var(--primary-color) !important;
    color: #fff !important;
}

.dataTables_wrapper .dataTables_paginate .paginate_button:hover {
    background-color: #e9ecef !important;
    color: var(--primary-color) !important;
    border-color: #ced4da !important;
}

/* Stats table container */
.stats-table-container {
    overflow-x: auto;
    margin-bottom: 20px;
}

.stats-table {
    width: 100%;
    border-collapse: collapse;
}

.stats-table th {
    background-color: var(--primary-color);
    color: white;
    padding: 12px;
    text-align: left;
}

.stats-table td {
    padding: 10px;
    border-bottom: 1px solid #eee;
}

.stats-table tbody tr:nth-child(even) {
    background-color: #f8f9fa;
}

.stats-table tbody tr:hover {
    background-color: #f1f1f1;
}

.stats-table .total-row {
    background-color: #e9ecef;
    font-weight: bold;
}

/* Responsive adjustments */
@media (max-width: 992px) {
    .filter-panel {
        flex-direction: column;
    }

    .filter-group {
        min-width: 100%;
    }

    .action-bar {
        flex-direction: column;
    }

    .btn {
        width: 100%;
    }
}

@media (max-width: 768px) {
    .stats-tabs {
        flex-wrap: wrap;
    }

    .tab-btn {
        flex: 1;
        min-width: 40%;
        text-align: center;
    }

    .flex-container {
        flex-direction: column;
    }

    .v-flex {
        min-width: 100%;
        margin-bottom: 15px;
    }
}
/* Fix for table headers and column spacing */
.statsContent th,
.stats-table th {
    padding: 15px 12px;    /* Increased padding for header cells */
    height: auto;          /* Allow header cells to grow based on content */
    min-height: 50px;      /* Minimum height for headers */
    white-space: normal;   /* Allow text to wrap */
    vertical-align: middle;/* Center content vertically */
}

.statsContent td,
.stats-table td {
    padding: 12px;         /* Consistent padding for data cells */
    height: auto;          /* Allow cells to grow based on content */
    white-space: normal;   /* Allow text to wrap */
}

/* Add appropriate spacing in DataTables */
.dataTables_wrapper .dataTable th,
.dataTables_wrapper .dataTable td {
    padding: 12px 15px;    /* Increased padding for all cells */
}

/* Ensure tables have enough space between columns */
.statsContent table,
.stats-table,
.dataTables_wrapper .dataTable {
    border-spacing: 0;
    border-collapse: separate;
    table-layout: auto;    /* Let content determine column width */
}

/* Ensure content-based column sizing */
.statsContent table th,
.stats-table th,
.dataTables_wrapper .dataTable th {
    width: auto !important;/* Override any fixed widths */
}

/* Improve table responsiveness */
.stats-table-container {
    padding: 0 5px;        /* Small padding on sides */
}

/* Prevent columns from being too narrow */
.statsContent th,
.stats-table th,
.dataTables_wrapper .dataTable th {
    min-width: 100px;      /* Minimum width for columns */
}

/* Fix for special statistical columns that may need less width */
.statsContent th:nth-child(4),
.statsContent th:nth-child(5),
.statsContent th:nth-child(6),
.stats-table th:nth-child(4),
.stats-table th:nth-child(5),
.stats-table th:nth-child(6) {
    min-width: 80px;       /* Slightly smaller for numeric columns */
}

/* Add some right margin for the last column */
.statsContent td:last-child,
.stats-table td:last-child {
    padding-right: 20px;
}
</style>