<?php
namespace GFUnit\apps\autopilot;
// https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/apps/autopilot/dd
class AutoCompany
{
    private static $shopToRun = [];
    public function __construct()
    {
    }

    static public function makeShopList($lang = 1 )
    {
        // > 500 gaver
        self::getCompanyListToRun( 20, 40, 40, 500,$lang);
        self::getCompanyListToRun( 40.1, 50, 40, 500,$lang);
        self::getCompanyListToRun( 50.1,90, 40, 500,$lang);
        //  500 gaver > 1000 gaver
        self::getCompanyListToRun( 15, 30, 501, 1000,$lang);
        self::getCompanyListToRun( 30.1, 50, 501, 1000,$lang);
        self::getCompanyListToRun( 50.1, 90, 501, 1000,$lang);
        // > 1000 gaver
        self::getCompanyListToRun( 10, 20, 1001, 100000,$lang);
        self::getCompanyListToRun( 20.1, 50, 1001, 100000,$lang);
        self::getCompanyListToRun( 50.1, 90, 1001, 100000,$lang);

        return self::$shopToRun;
    }


    static public function getCompanyListToRun($procentFrom, $procentTo, $orderCountFrom, $orderCountTo,$lang = 1)
    {
        $lang = 1;
        $shops = self::getList($procentFrom, $procentTo, $orderCountFrom, $orderCountTo);
        foreach ($shops as $shop){

            if($lang != $shop->attributes["localisation"]) { continue; } ;
            if($shop->attributes["final_finished"] == 1) { continue; } ;

            $shopID             = $shop->attributes["shop_id"];
            $totalUsers        = $shop->attributes["total_antal_brugere"];
            $procentSelected    = $shop->attributes["procent_ordre_af_brugere"];


            $stage = self::getStage($shopID,$totalUsers,$procentSelected);
            $stage = "adapt_".$stage;
            if(self::checkIfHasRun($shopID,$stage)){ continue;}

            self::$shopToRun[] = $shopID;
        }


    }

    private static function checkIfHasRun($shopID, $stage)
    {
        $list = \PresentReservation::find('all', array(
            'conditions' => array(
                'shop_id = ? AND ' . $stage . ' IS NOT NULL',
                $shopID
            ),
            'select' => 'COUNT(*) as antal'
        ));
        return $list[0]->antal > 2  ? true : false;

    }

    private static function getStage($shopID,$totalUsers,$procentSelected)
    {
        $adapt = 0;
        if ($totalUsers < 500) {
            if ($procentSelected > 20) {
                $adapt = 1;
            }
            if ($procentSelected > 40) {
                $adapt = 2;
            }
            if ($procentSelected > 50) {
                $adapt = 3;
            }
        } elseif ($totalUsers >= 500 && $totalUsers < 1000) {
            if ($procentSelected > 15) {
                $adapt = 1;
            }
            if ($procentSelected > 30) {
                $adapt = 2;
            }
            if ($procentSelected > 50) {
                $adapt = 3;
            }
        } elseif ($totalUsers > 1000) {
            if ($procentSelected > 10) {
                $adapt = 1;
            }
            if ($procentSelected > 20) {
                $adapt = 2;
            }
            if ($procentSelected > 50) {
                $adapt = 3;
            }
        }
        return $adapt;
    }

    // Tilføjet static keyword
    private static function getList($procentFrom, $procentTo, $orderCountFrom, $orderCountTo)
    {
       $sql = "SELECT 
                    shop.final_finished,            
                    shop.localisation,
                    `order`.shop_id,
                    COUNT(DISTINCT `order`.id) as order_antal, 
                    (SELECT COUNT(*) 
                     FROM shop_user 
                     WHERE shop_user.shop_id = `order`.shop_id) as total_antal_brugere,
                    CASE 
                        WHEN (SELECT COUNT(*) 
                              FROM shop_user 
                              WHERE shop_user.shop_id = `order`.shop_id) > 0 
                        THEN ROUND((COUNT(DISTINCT `order`.id) / 
                                   (SELECT COUNT(*) 
                                    FROM shop_user 
                                    WHERE shop_user.shop_id = `order`.shop_id)) * 100, 2)
                        ELSE 0
                    END as procent_ordre_af_brugere
                FROM `order`
                INNER JOIN shop ON shop.id = `order`.`shop_id` 
                WHERE 
                    `order`.`shop_is_company` = 1 AND
                    `order`.`shop_is_gift_certificate` = 0 
                GROUP BY `order`.shop_id
                HAVING 
                    (total_antal_brugere BETWEEN ".$orderCountFrom." AND ".$orderCountTo." ) and
                    (procent_ordre_af_brugere BETWEEN ".$procentFrom." and ".$procentTo.")  and 
                    total_antal_brugere > 50
                    ";


        return \Shop::find_by_sql($sql);
    }
}
?>