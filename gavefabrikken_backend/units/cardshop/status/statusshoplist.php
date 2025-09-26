<?php

namespace GFUnit\cardshop\status;
use GFBiz\units\UnitController;

class StatusShopList
{

    private $expireDateMap = array();

    private function hasExpireDate($shopId,$expireDateId) {
        return isset($this->expireDateMap[$shopId]) && in_array($expireDateId,$this->expireDateMap[$shopId]);
    }

    private $userCount = array();
    private $orderCount = array();

    public function getUserCount($shopId,$expireDate) {
        return isset($this->userCount[$shopId][$expireDate]) ? $this->userCount[$shopId][$expireDate] : 0;
    }

    public function getOrderCount($shopId,$expireDate) {
        return isset($this->orderCount[$shopId][$expireDate]) ? $this->orderCount[$shopId][$expireDate] : 0;
    }

    public function getPercentageOrdered($shopId,$expireDate) {
        $userCount = $this->getUserCount($shopId,$expireDate);
        $orderCount = $this->getOrderCount($shopId,$expireDate);
        if($userCount == 0) return 0;
        return round($orderCount / $userCount * 100,0);
    }

    public function notSelected($shopId,$expireDate) {
        $userCount = $this->getUserCount($shopId,$expireDate);
        $orderCount = $this->getOrderCount($shopId,$expireDate);
        return $userCount - $orderCount;
    }

    public function generateShopList($languageList)
    {

        $expireDateList = \ExpireDate::find('all',array("order" => "expire_date asc"));
        $shopList = \CardshopSettings::find('all',array("conditions" => "language_code in (".implode(",",$languageList).")", "order" => "concept_code asc"));
        $shopMap = [];
        foreach($shopList as $shop) {
            $shopMap[$shop->shop_id] = $shop;
        }

        $shopdates = \CardshopExpiredate::find_by_sql("SELECT * FROM `cardshop_expiredate`");
        foreach($shopdates as $shopdate) {
            if(isset($shopMap[$shopdate->shop_id])) {
                if(!isset($this->expireDateMap[$shopdate->shop_id])) $this->expireDateMap[$shopdate->shop_id] = array();
                $this->expireDateMap[$shopdate->shop_id][] = $shopdate->expire_date_id;
            }
        }

        $usedExpireDates = [];
        foreach($this->expireDateMap as $shopId => $expireDates) {
            foreach($expireDates as $expireDateId) {
                $usedExpireDates[$expireDateId] = true;
            }
        }

        // Remove unused expire dates
        $expireDateList = array_filter($expireDateList, function($expireDate) use ($usedExpireDates) {
            return isset($usedExpireDates[$expireDate->id]);
        });


        // load user and order data
        $sql = "SELECT 
    su.shop_id,
    su.expire_date,
    COUNT(DISTINCT su.id) AS antal_brugere,
    COUNT(DISTINCT o.id) AS antal_ordrer
FROM 
    `shop_user` su
LEFT JOIN 
    `order` o ON o.shopuser_id = su.id
WHERE 
    su.blocked = 0 
    AND su.company_order_id IN (
        SELECT co.id 
        FROM company_order co
        JOIN cardshop_settings cs ON cs.shop_id = co.shop_id
        WHERE cs.language_code IN (1,4,5) 
        AND co.order_state NOT IN (7,8,20,21)
    )
GROUP BY 
    su.shop_id, su.expire_date;";

        $userData = \ShopUser::find_by_sql($sql);
        foreach($userData as $dataRow) {

            // Put data into usercount and order count
            $this->userCount[$dataRow->shop_id][$dataRow->expire_date->format("d-m-Y")] = $dataRow->antal_brugere;
            $this->orderCount[$dataRow->shop_id][$dataRow->expire_date->format("d-m-Y")] = $dataRow->antal_ordrer;

        }

    ?><!-- Shops Table -->
    <h2 class="section-header">Status på shops</h2>
    <div class="table-container">
        <table>
            <thead>
            <tr>
                <th>Navn</th>

                <?php
                    foreach($expireDateList as $expireDate) {
                        echo '<th style="text-align: center;">Uge '.$expireDate->week_no.'<br>'.$expireDate->expire_date->format("d-m-Y").'</th>';
                    }
                ?>
                <th title="Synkroniser ordre til navision">Ordre</th>
                <th title="Send gavekort">Mail</th>
                <th title="Send fysiske kort">Kort</th>
                <th title="Send privatleveringer">Privat</th>
                <th title="Send earlyordre">Early</th>
            </tr>
            </thead>


            <?php

            if(in_array(1,$languageList)) {
                $this->printLanguage(1,"Danmark",$expireDateList,$shopList);
            }

            if(in_array(4,$languageList)) {
                $this->printLanguage(4,"Norge",$expireDateList,$shopList);
            }

            if(in_array(5,$languageList)) {
                $this->printLanguage(5,"Sverige",$expireDateList,$shopList);
            }

            ?>
            </tbody>
        </table>
    </div><?php

    }

    private function printLanguage($langcode,$langName,$expireDates,$shopList) {

        ?><thead>
            <th colspan="<?php echo 6+count($expireDates); ?>">
                <?php echo $langName; ?>
            </th>
        </thead>
        <tbody><?php

            foreach($shopList as $shop) {
                if($shop->language_code == $langcode) {
                    $this->printShop($shop,$expireDates);
                }
            }

        ?></tbody><?php

    }

    private function printShop($shop,$expireDates) {

            ?><tr>
            <td><?php echo $shop->concept_code ?></td>
            <?php
            foreach($expireDates as $expireDate) {
                $this->printShopCell($expireDate, $shop);
            }

            if($shop->navsync_orders == 1) {
                echo "<td style='background: green; color: white;'>Ja</td>";
            } else {
                echo "<td style='background: lightcoral; color: white;'>Nej</td>";
            }

        if($shop->send_certificates == 1) {
            echo "<td style='background: green; color: white;'>Ja</td>";
        } else {
            echo "<td style='background: lightcoral; color: white;'>Nej</td>";
        }

        if($shop->navsync_shipments == 1) {
            echo "<td style='background: green; color: white;'>Ja</td>";
        } else {
            echo "<td style='background: lightcoral; color: white;'>Nej</td>";
        }

        if($shop->navsync_shipments == 1 && $shop->navsync_privatedelivery == 1) {
            echo "<td style='background: green; color: white;'>Ja</td>";
        } else {
            echo "<td style='background: lightcoral; color: white;'>Nej</td>";
        }

        if($shop->navsync_shipments == 1 && $shop->navsync_earlyorders == 1) {
            echo "<td style='background: green; color: white;'>Ja</td>";
        } else {
            echo "<td style='background: lightcoral; color: white;'>Nej</td>";
        }

            ?>


            </tr><?php

    }

    private function printShopCell($expireDate,$shop) {

        $background = "#FFFFFF";
        $color = "#555555";
        $content = "&nbsp;";
        $title = "";

        $weekNo = $expireDate->week_no;
        $open = null;
        $close = null;
        $closeWebsale = null;
        $closeSale = null;


        if ($expireDate->is_delivery) {
            $open = $shop->private_open;
            $close = $shop->private_close;
            $closeWebsale = $shop->private_close_websale;
            $closeSale = $shop->private_close_sale;
        }

        else if($expireDate->is_special_private == 1) {
            $open = $shop->special_private1_open;
            $close = $shop->special_private1_close;
            $closeWebsale = $shop->special_private1_close_websale;
            $closeSale = $shop->special_private1_close_sale;
        }

        else if($expireDate->is_special_private == 2) {
            $open = $shop->special_private2_open;
            $close = $shop->special_private2_close;
            $closeWebsale = $shop->special_private2_close_websale;
            $closeSale = $shop->special_private2_close_sale;
        }

        else if($weekNo == 47) {
            $open = $shop->week_47_open;
            $close = $shop->week_47_close;
            $closeWebsale = $shop->week_47_close_websale;
            $closeSale = $shop->week_47_close_sale;
        }
        else if($weekNo == 48) {
            $open = $shop->week_48_open;
            $close = $shop->week_48_close;
            $closeWebsale = $shop->week_48_close_websale;
            $closeSale = $shop->week_48_close_sale;
        }

        else if($weekNo == 49) {
            $open = $shop->week_49_open;
            $close = $shop->week_49_close;
            $closeWebsale = $shop->week_49_close_websale;
            $closeSale = $shop->week_49_close_sale;
        }
        else if($weekNo == 50) {
            $open = $shop->week_50_open;
            $close = $shop->week_50_close;
            $closeWebsale = $shop->week_50_close_websale;
            $closeSale = $shop->week_50_close_sale;
        }
        else if($weekNo == 51) {
            $open = $shop->week_51_open;
            $close = $shop->week_51_close;
            $closeWebsale = $shop->week_51_close_websale;
            $closeSale = $shop->week_51_close_sale;
        }
        else if($weekNo == 4 || $weekNo == 5) {
            $open = $shop->week_04_open;
            $close = $shop->week_04_close;
            $closeWebsale = $shop->week_04_close_websale;
            $closeSale = $shop->week_04_close_sale;
        }

        $physicalCloseDays = $shop->physical_close_days;


        $content = "<div style='float: left; font-size: 0.75em;' title='Ikke valgt: ".$this->notSelected($shop->shop_id, $expireDate->expire_date->format("d-m-Y"))."'>".$this->getPercentageOrdered($shop->shop_id, $expireDate->expire_date->format("d-m-Y"))."%</div>".$this->getUserCount($shop->shop_id, $expireDate->expire_date->format("d-m-Y"));


        if(!$this->hasExpireDate($shop->shop_id, $expireDate->id)) {
            $content = "";
            // Not in shop
        }

        // If open is null or now is before $open (datetime)
        else if($open == null || new \DateTime() < $open) {
            $background = "#FFD700";
            $color = "#000000";
            $title = "Ikke åbnet endnu, åbner d. ".($open != null ? $open->format("d-m-Y H:i") : "??");
        }

        // If close is null or now is after close
        else if($close == null || new \DateTime() > $close) {
            $background = "#FF0000";
            $color = "#FFFFFF";
            $title = "Lukket, lukkede d. ".($close != null ? $close->format("d-m-Y H:i") : "??");
        }
        // If closeWebsale is not null and closeWebsale is before now
        else if($closeWebsale != null && new \DateTime() > $closeWebsale) {
            $background = "#AA0000";
            $color = "#FFFFFF";
            $title = "Lukket for websalg, lukker d. ".($close != null ? $close->format("d-m-Y H:i") : "??");
        }
        // If closeWebsale is not null and closeWebsale minus $physicalCloseDays days is before now
        else if($closeWebsale != null && new \DateTime() > $closeWebsale->sub(new \DateInterval("P".$physicalCloseDays."D"))) {
            $background = "#FF8888";
            $color = "#FFFFFF";
            $title = "Lukket for fysisk salg, lukker d. ".($close != null ? $close->format("d-m-Y H:i") : "??");
        }
        // Open (green)
        else {
            $background = "#00FF00";
            $color = "#000000";
            $title = "Åben, lukker d. ".($close != null ? $close->format("d-m-Y H:i") : "??");
        }




        echo '<td style="text-align: right; background: '.$background.'; color: '.$color.'" title="'.$title.'">'.$content.'</td>';

    }



}