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


    /**
     *  KONTAINER PIM
     */
    CONST KONTAINER_API_KEY = "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiI1NDMiLCJqdGkiOiI3ZTg2ZThhYWU3NjkwZjcxOTk4ZGMwYzNjYTQ0MzBiOTEyNTU5MDk4ZjNjZGI1YjRiNWYyZjU0NmZlNjYzNWE0M2M4NGUyYjllN2YwMGU5ZiIsImlhdCI6MTc0MDY1MTM1MS41ODE1OTUsIm5iZiI6MTc0MDY1MTM1MS41ODE1OTgsImV4cCI6MTgwMzcyMzMyMi4wMjIzNzIsInN1YiI6IjMxNDc5Iiwic2NvcGVzIjpbXX0.P6cA4NwJoEzSchfvLQY_cjHd_g4j49o1iUzeZ85P9Jf6OZix5lYzVfJieqbUIA6sZaNUHbrklIRBuKNV8jDc06sJXE0SVpB8oJUvWAQQPqxh7V-he1ExxVi70USE8lLRVdO1MRGh8ELIVq1wWndf-1_F_HKKJFzB3afEQhLc4evrxu8MZmVtEGmryFUorVnzAy8VNO7XhpW6a9evLggOtnocJzxF_9kGmpCpPvxpkTFJ9ThLlU0ol5mciNjUUm4upH0Z3nkUcFoxCAXrb1edG3j_Fjkily6qkFqIyQUnCBJSZXuWvxA5Xb2qIXmRpD1jbgLvyJJn4QybFaj3401NBb_QtTCcCorzMmqcq5krORpq-q2nzXS76dzTgRdgKCL3o87o0wjrY5X_LIXxsvBr4PjkrD-RUSxahpsq44SFqGDg74Ue56XtZVnbMGkA9A86bKyZZRZcRnwlWwzX9EiDopGYUHdV05HJtucil1wZ0RSbkwwtCQIz1nPqEeWqM7igVoaG0DSPTHAlVkb4PeI3zgqL5sHQioVflwQCJmlVtK5cylq-FJXykWW_fSk5XFmy1ihJgUVqxvtSZjWUJpEfNGJzJnWQTSYTmnJdFb2c9qr15eP-ZOG6RR7td71XICbZXi3zKYqlJ6YdYiy1RBdaz3ThuQ5vLmpANLblyB9qUOI";
    CONST KONTAINER_API_BASE_URL = "https://gavefabrikken.kontainer.com/jsonapi/v2/pim/channels/10614/items";

}

// Add scripts that must be loaded everytime
include(dirname(__FILE__)."/gfmigrate.php");
