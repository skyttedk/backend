<?php

class GFConfig {

    /**
     * GF SEASON
     * Årstal for den sæson systemet er sat op til.
     */

    CONST SALES_SEASON = 2025;

    /**
     * URL / PATH SETTINGS
     * URL og lokal sti til applikationen
     */

    CONST BACKEND_URL = "https://system.gavefabrikken.dk/gavefabrikken_backend/";
    CONST BACKEND_PATH = "/var/www/backend/public_html/gavefabrikken_backend/";

    CONST SHOP_URL_PRIMARY = "https://findgaven.dk/";
    CONST SHOP_URL_DK = "https://findgaven.dk/";
    CONST SHOP_URL_NO = "https://gavevalg.no/";
    CONST SHOP_URL_SE = "https://dinjulklapp.se/";

    CONST SHOPAPI_URL = "https://system.gavefabrikken.dk/api/";

    CONST SHOP_URL_LASTYEAR = "https://findgaven.dk/2024/";

    /**
     * ARCHIVE URLS
     */

    CONST ARCHIVE_LASTYEAR_URL = "https://gavefabrikken.dk/gavefabrikken_backend/";
    CONST ARCHIVE_2019_URL = "https://gavefabrikken.dk/2020/gavefabrikken_backend/";
    CONST ARCHIVE_2020_URL = "https://gavefabrikken.dk/2021/gavefabrikken_backend/";
    CONST ARCHIVE_2021_URL = "https://gavefabrikken.dk/gavefabrikken_backend/";
    CONST ARCHIVE_2022_URL = "https://system.gavefabrikken.dk/2022/gavefabrikken_backend/";
    CONST ARCHIVE_2023_URL = "https://system.gavefabrikken.dk/2023/gavefabrikken_backend/";

    CONST ARCHIVE_2024_URL = "https://system.gavefabrikken.dk/2024/gavefabrikken_backend/";

    /**
     * DATABASE SETTINGS
     * Indstillinger til database adgang
     */

    CONST DB_USERNAME = "gavefabrikken";
    CONST DB_PASSWORD = "RbxuBBjamVy!h3v@8z";
    CONST DB_HOST = "10.0.0.10";
    CONST DB_DATABASE = "gavefabrikken2025";


    /**
     * NAVISION SETUP
     * Opsætning til navision
     */

    CONST NAVISION_PRODUCTION_MODE = false;

    // DA
    CONST NAVISION_DA_HOST = "https://nav.gavefabrikken.dk:17247/Prod_WS/WS/Gavefabrikken/";
    CONST NAVISION_DA_USERNAME = "GFWS";
    CONST NAVISION_DA_PASSWORD = "q]5]R7-N,cs!^o6RHE#U1g5^X8hs-iLh)";
    
    CONST NAVISION_DEV_DA_HOST = "https://nav.gavefabrikken.dk:18247/Test_WS/WS/Gavefabrikken/";
    CONST NAVISION_DEV_DA_USERNAME = "GFWS";
    CONST NAVISION_DEV_DA_PASSWORD = "q]5]R7-N,cs!^o6RHE#U1g5^X8hs-iLh)";

    // NO
    CONST NAVISION_NO_HOST = "https://nav.gavefabrikken.dk:17247/Prod_WS/WS/Gavefabrikken%20Norge/";
    CONST NAVISION_NO_USERNAME = "GFWS";
    CONST NAVISION_NO_PASSWORD = "q]5]R7-N,cs!^o6RHE#U1g5^X8hs-iLh)";

    CONST NAVISION_DEV_NO_HOST = "https://nav.gavefabrikken.dk:18247/Test_WS/WS/Gavefabrikken%20Norge/";
    CONST NAVISION_DEV_NO_USERNAME = "GFWS";
    CONST NAVISION_DEV_NO_PASSWORD = "q]5]R7-N,cs!^o6RHE#U1g5^X8hs-iLh)";

    // SE
    CONST NAVISION_SE_HOST = "http://gavefab-app01.nav.ecitconsulting.dk:17047/GAVEFAB-SE/WS/Presentbolaget/";
    CONST NAVISION_SE_USERNAME = "webservices";
    CONST NAVISION_SE_PASSWORD = "ZFuSExEyxZQr5zUH5prTTbYB2bH5uAM6";

    CONST NAVISION_DEV_SE_HOST = "http://gavefab-app01.nav.ecitconsulting.dk:17047/GAVEFAB-SE/WS/Test%20Presentbolaget/";
    CONST NAVISION_DEV_SE_USERNAME = "webservices";
    CONST NAVISION_DEV_SE_PASSWORD = "ZFuSExEyxZQr5zUH5prTTbYB2bH5uAM6";



    /**
     * COUNTRY CODES
     */
    
    CONST LANG_DENMARK = 1;
    CONST LANG_NORWAY = 4;
    CONST LANG_SWEDEN = 5;

    CONST LANGCODE_DENMARK = "dk";
    CONST LANGCODE_NORWAY = "no";
    CONST LANGCODE_SWEDEN = "se";


}

// Add scripts that must be loaded everytime
include(dirname(__FILE__)."/gfmigrate.php");
