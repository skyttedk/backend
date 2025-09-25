<?php

$hidesystem = $hidesystem ?? false;
$hidebuttons = $hidebuttons ?? false;

if(!$hidesystem) {
    include("views/system_nav.php");
}

// Load shops
//$shops = \Shop::find_by_sql("SELECT * FROM shop WHERE id IN (select shop_id FROM cardshop_settings) ");
$shops = \Shop::find_by_sql("SELECT * FROM shop, cardshop_settings WHERE shop.id = cardshop_settings.shop_id ORDER BY cardshop_settings.language_code ASC, shop.name ASC");

?><style>
    .cardshopsettings { border-collapse: collapse; font-size: 0.9em; }
    .cardshopsettings thead tr {background: #DDD}
    .cardshopsettings thead tr.langsep {background: #BBB; }
    .cardshopsettings thead tr.langsep th { text-align: center; }
    .cardshopsettings tbody tr:nth-child(even) {background: #EEE}
    .cardshopsettings th { text-align: left; padding: 5px; border: 1px solid #555555; white-space: nowrap; }
    .cardshopsettings td { text-align: left; padding: 5px; border: 1px solid #555555; white-space: nowrap; }
    .cardshopsettings .price { text-align: right;}
</style><?php

class settingsFormatter {

    public function langName($shop,$rowDef) {
        if($shop->language_code == 1) return "Danmark";
        else if($shop->language_code == 2) return "England";
        else if($shop->language_code == 3) return "Tyskland";
        else if($shop->language_code == 4) return "Norge";
        else if($shop->language_code == 5) return "Sverige";
        else return "UKENDT!";
    }

    public function int($val) {
        return $val;
    }

    public function price($shop,$rowDef,$val) {
        return number_format(intval($val)/100,2,",",".");
    }

    public function float($shop,$rowDef,$val) {
        return number_format(($val),2,",",".");
    }

    public function percentage($shop,$rowDef,$val) {
        return number_format(($val-1)*100,0,",",".")."%";
    }

    public function text($shop,$rowDef,$val) {
        return (trimgf($val));
    }

    public function active($shop,$rowDef,$val) {
        return ($val == 0) ? "Bruges ikke" : "<b>Aktiveret</b>";
    }

    public function yesno($shop,$rowDef,$val) {
        return ($val == 1) ? "Ja" : "Nej";
    }

    public function date($shop,$rowDef,$val) {
        if($val == null) return "";
        else echo $val;
    }
}

$formatter = new settingsFormatter();

// Define data in table
$tableDefs = array(
    array("name" => "Shop navn","attr" => "name","desc" => "Name of the shop"),
    array("name" => "Shop ID","attr" => "shop_id","desc" => "Name of the shop"),

    array("name" => "Koncept","attr" => "concept_parent","desc" => "Navn på konceptet"),
    array("name" => "Shop kode","attr" => "concept_code","desc" => "Navn på shoppen"),

    array("name" => "Web sælger","attr" => "web_salesperson","format" => "text","desc" => "Sælger-kode for web bestillinger"),
    array("name" => "Beregn fragt automatisk","attr" => "calculate_freight","format" => "yesno","desc" => "Angiver om systemet automatisk beregner fragten."),

    array("name" => "Varnr på autovalg","attr" => "default_present_itemno","format" => "text","desc" => "Varenr på autogaven på gaverkortet"),
    array("name" => "Navn på autovalg","attr" => "default_present_name","format" => "text","desc" => "Navn på autogaven på gavekortet"),

    array("name" => "Varnr","attr" => "concept_code","format" => "text","desc" => "Varenr. i navision (tilføjes ugenr)"),
    array("name" => "Kort pris","attr" => "card_price","format" => "price","desc" => "Pris. pr. gavekort"),
    array("name" => "Kort moms","attr" => "card_moms_multiplier","format" => "percentage","desc" => "Moms der lægges til prisen pr. kort i navision"),
    array("name" => "Kort DB","attr" => "card_db","format" => "price","desc" => "Kort dækningsbidrag"),

    array("name" => "Privatlevering","attr" => "privatedelivery_use","format" => "active","desc" => "Angiver om der bruges privatlevering på denne shop"),
    array("name" => "Privatlevering pris","attr" => "privatedelivery_price","format" => "price","desc" => "Prisen på privatlevering pr. kort"),

    array("name" => "Kortgebyr","attr" => "cardfee_use","format" => "active","desc" => "Angiver om der bruges kortgebyr på denne shop"),
    array("name" => "Kortgebyr pris","attr" => "cardfee_price","format" => "price","desc" => "Prisen på kortgebyr"),
    array("name" => "Kortgebyr min antal","attr" => "cardfee_minquantity","format" => "text","desc" => "Hvis kortgebyr skal lægges til ordre under et bestemt antal kort angives det antal her"),
    array("name" => "Kortgebyr pr. kort","attr" => "cardfee_percard","format" => "yesno","desc" => "Angiver om  prisen på kortgebyr er per kort eller en samlet pris, ja = prisen ganges med antal kort"),

    array("name" => "Levering af kort","attr" => "carddelivery_use","format" => "active","desc" => "Angiver om der bruges levering af fysiske kort"),
    array("name" => "Levering af kort pris","attr" => "carddelivery_price","format" => "price","desc" => "Prisen på levering af fysiske kort (samlet pris, ikke pr. kort men pr. leveringssted)"),

    array("name" => "Opbæring","attr" => "carryup_use","format" => "active","desc" => "Angiver om der bruges opbærring"),
    array("name" => "Opbæring pris","attr" => "carryup_price","format" => "price","desc" => "Prisen på opbærring"),

    array("name" => "DOT","attr" => "dot_use","format" => "active","desc" => "Angiver om der bruges dot (date-of-arrival)"),
    array("name" => "DOT pris ","attr" => "dot_price","format" => "price","desc" => "Prisen på dot"),

    array("name" => "Indpakning","attr" => "giftwrap_use","format" => "active","desc" => "Angiver om der bruges indpakning"),
    array("name" => "Indpakning pris ","attr" => "giftwrap_price","format" => "price","desc" => "Prisen på indpakning"),

    array("name" => "Navnelabels","attr" => "namelabels_use","format" => "active","desc" => "Angiver om der bruges navnelabels (excl. indpak)"),
    array("name" => "Navnelabels pris ","attr" => "namelabels_price","format" => "price","desc" => "Prisen på navnelabels"),

    array("name" => "Varenr v. ingen indpak ","attr" => "giftwrap_notset_itemno","format" => "text","desc" => "Varenr der sendes til nav når der ikke er valgt indpak"),

    array("name" => "Fakturagebyr - forudfakturering","attr" => "invoiceinitial_use","format" => "active","desc" => "Angiver om der bruges fakturagebyr på forudfaktureringen"),
    array("name" => "Fakturagebyr pris ","attr" => "invoiceinitial_price","format" => "price","desc" => "Prisen på fakturagebyr på forudfakturering"),

    array("name" => "Fakturagebyr - slutfaktura","attr" => "invoicefinal_use","format" => "active","desc" => "Angiver om der bruges fakturagebyr på slutfaktura"),
    array("name" => "Fakturagebyr pris ","attr" => "invoicefinal_price","format" => "price","desc" => "Prisen på fakturagebyr på slutfaktura"),

    array("name" => "Miljøbidrag","attr" => "env_fee_percent","format" => "float","desc" => "Miljøbidrag standard %"),

    array("name" => "Gebyr ved få kort","attr" => "minorderfee_use","format" => "yesno","desc" => "Om der benyttes ekstra gebyr ved få kort"),
    array("name" => "Gebyr beløb","attr" => "minorderfee_price","format" => "price","desc" => "Miljøbidrag standard %"),
    array("name" => "Min. antal kort før gebyr","attr" => "minorderfee_mincards","format" => "text","desc" => "Miljøbidrag standard %"),
    array("name" => "Minimum antal ved web bestilling","attr" => "min_web_cards","format" => "text","desc" => "Mindste antal kort der kan bestilles ved web bestilling."),

    array("name" => "x dage før e-mail","attr" => "physical_close_days","format" => "text","desc" => "Antal dage før luk for e-mail bestilling der lukkes for fysiske kort."),


    array("name" => "Synkronisering af web ordre","attr" => "orderweb_syncwait","format" => "text","desc" => "Antal timer der går fra ordre er oprettet via web bestilling til den lægges i navision"),
    array("name" => "Synkronisering af cardshop ordre","attr" => "ordercs_syncwait","format" => "text","desc" => "Antal timer der går fra ordre er oprettet af sælger i cardshop til den lægges i navision"),
    array("name" => "Synkronisering af gavekort forsendelse","attr" => "shipment_syncwait","format" => "text","desc" => "Antal timer der går fra ordre er synkroniseret til en forsendelse må sendes til navision."),

    array("name" => "Earlyordre håndtering","attr" => "earlyorder_handler","format" => "text","desc" => "Hvilket system der håndterer earlyordre"),
    array("name" => "Privatlevering håndtering","attr" => "privatedelivery_handler","format" => "text","desc" => "Hvilket system der håndterer privatlevering"),


    array("name" => "Åben for valg","attr" => "week_47_open","format" => "date","desc" => "Hvornår der åbnes for valg på uge 47"),
    array("name" => "Luk for valg","attr" => "week_47_close","format" => "date","desc" => "Hvornår der lukkes for valg på uge 47"),
    array("name" => "Stop salg - web","attr" => "week_47_close_websale","format" => "date","desc" => "Hvornår der lukkes salg via hjemmeside til uge 47 (fysiske kort lukkes  før denne dato)"),
    array("name" => "Stop salg - cardshop","attr" => "week_47_close_sale","format" => "date","desc" => "Hvornår der lukkes salg via cardshop til uge 47"),

    array("name" => "Åben for valg","attr" => "week_48_open","format" => "date","desc" => "Hvornår der åbnes for valg på uge 48"),
    array("name" => "Luk for valg","attr" => "week_48_close","format" => "date","desc" => "Hvornår der lukkes for valg på uge 48"),
    array("name" => "Stop salg - web","attr" => "week_48_close_websale","format" => "date","desc" => "Hvornår der lukkes salg via hjemmeside til uge 48 (fysiske kort lukkes  før denne dato)"),
    array("name" => "Stop salg - cardshop","attr" => "week_48_close_sale","format" => "date","desc" => "Hvornår der lukkes salg via cardshop til uge 48"),

    array("name" => "Åben for valg","attr" => "week_49_open","format" => "date","desc" => "Hvornår der åbnes for valg på uge 49"),
    array("name" => "Luk for valg","attr" => "week_49_close","format" => "date","desc" => "Hvornår der lukkes for valg på uge 49"),
    array("name" => "Stop salg - web","attr" => "week_49_close_websale","format" => "date","desc" => "Hvornår der lukkes salg via hjemmeside til uge 49 (fysiske kort lukkes  før denne dato)"),
    array("name" => "Stop salg - cardshop","attr" => "week_49_close_sale","format" => "date","desc" => "Hvornår der lukkes salg via cardshop til uge 49"),

    array("name" => "Åben for valg","attr" => "week_50_open","format" => "date","desc" => "Hvornår der åbnes for valg på uge 50"),
    array("name" => "Luk for valg","attr" => "week_50_close","format" => "date","desc" => "Hvornår der lukkes for valg på uge 50"),
    array("name" => "Stop salg - web","attr" => "week_50_close_websale","format" => "date","desc" => "Hvornår der lukkes salg via hjemmeside til uge 50 (fysiske kort lukkes  før denne dato)"),
    array("name" => "Stop salg - cardshop","attr" => "week_50_close_sale","format" => "date","desc" => "Hvornår der lukkes salg via cardshop til uge 50"),

    array("name" => "Åben for valg","attr" => "week_51_open","format" => "date","desc" => "Hvornår der åbnes for valg på uge 51"),
    array("name" => "Luk for valg","attr" => "week_51_close","format" => "date","desc" => "Hvornår der lukkes for valg på uge 51"),
    array("name" => "Stop salg - web","attr" => "week_51_close_websale","format" => "date","desc" => "Hvornår der lukkes salg via hjemmeside til uge 51 (fysiske kort lukkes  før denne dato)"),
    array("name" => "Stop salg - cardshop","attr" => "week_51_close_sale","format" => "date","desc" => "Hvornår der lukkes salg via cardshop til uge 51"),


    array("name" => "Åben for valg","attr" => "week_04_open","format" => "date","desc" => "Hvornår der åbnes for valg på uge 04"),
    array("name" => "Luk for valg","attr" => "week_04_close","format" => "date","desc" => "Hvornår der lukkes for valg på uge 04"),
    array("name" => "Stop salg - web","attr" => "week_04_close_websale","format" => "date","desc" => "Hvornår der lukkes salg via hjemmeside til uge 04 (fysiske kort lukkes  før denne dato)"),
    array("name" => "Stop salg - cardshop","attr" => "week_04_close_sale","format" => "date","desc" => "Hvornår der lukkes salg via cardshop til uge 04"),

    array("name" => "Åben for valg","attr" => "private_open","format" => "date","desc" => "Hvornår der åbnes for valg på uge private"),
    array("name" => "Luk for valg","attr" => "private_close","format" => "date","desc" => "Hvornår der lukkes for valg på uge private"),
    array("name" => "Stop salg - web","attr" => "private_close_websale","format" => "date","desc" => "Hvornår der lukkes salg via hjemmeside til uge private (fysiske kort lukkes  før denne dato)"),
    array("name" => "Stop salg - cardshop","attr" => "private_close_sale","format" => "date","desc" => "Hvornår der lukkes salg via cardshop til uge private"),
    array("name" => "Udløb af privatlevering","attr" => "private_expire_date","format" => "text","desc" => "Hvornår privatleveringskort udløber"),

    array("name" => "Åben for valg","attr" => "special_private1_open","format" => "date","desc" => "Hvornår der åbnes for valg på uge norsk speciallevering 1"),
    array("name" => "Luk for valg","attr" => "special_private1_close","format" => "date","desc" => "Hvornår der lukkes for valg på uge norsk speciallevering 1"),
    array("name" => "Stop salg - web","attr" => "special_private1_close_websale","format" => "date","desc" => "Hvornår der lukkes salg via hjemmeside til uge norsk speciallevering 1"),
    array("name" => "Stop salg - cardshop","attr" => "special_private1_close_sale","format" => "date","desc" => "Hvornår der lukkes salg via cardshop til uge norsk speciallevering 1"),
    array("name" => "Udløb af privatlevering","attr" => "special_private1_expiredate","format" => "text","desc" => "Hvornår norsk speciallevering 1 udløber"),

    array("name" => "Åben for valg","attr" => "special_private2_open","format" => "date","desc" => "Hvornår der åbnes for valg på uge norsk speciallevering 2"),
    array("name" => "Luk for valg","attr" => "special_private2_close","format" => "date","desc" => "Hvornår der lukkes for valg på uge norsk speciallevering 2"),
    array("name" => "Stop salg - web","attr" => "special_private2_close_websale","format" => "date","desc" => "Hvornår der lukkes salg via hjemmeside til uge norsk speciallevering 2"),
    array("name" => "Stop salg - cardshop","attr" => "special_private2_close_sale","format" => "date","desc" => "Hvornår der lukkes salg via cardshop til uge norsk speciallevering 2"),
    array("name" => "Udløb af privatlevering","attr" => "special_private2_expiredate","format" => "text","desc" => "Hvornår norsk speciallevering 2 udløber"),

    array("name" => "Erstatningsgavekort virksomhed","attr" => "replacement_company_id","format" => "text","desc" => "Virksomhed som erstatningskort tilknyttes"),
    array("name" => "Flydende deadline (måneder)","attr" => "floating_expire_months","format" => "text","desc" => "Virksomhed som erstatningskort tilknyttes"),
    array("name" => "Send gavekoder på e-mail","attr" => "send_certificates","format" => "yesno","desc" => "Send gavekoder på e-mail"),

    array("name" => "Sync cardshop ordre","attr" => "navsync_orders","format" => "yesno","desc" => "Synkroniseres ordre i cardshop pt."),
    array("name" => "Sync forsendelser","attr" => "navsync_shipments","format" => "yesno","desc" => "Synkroniseres forsendelser af gavekort"),
    array("name" => "Sync privatleveringer","attr" => "navsync_privatedelivery","format" => "yesno","desc" => "Synkroniseres privatleveringer pt."),
    array("name" => "Sync earlyorder","attr" => "navsync_earlyorders","format" => "yesno","desc" => "Synkroniseres earlyordre pt."),
    array("name" => "Earlyordre håndteres i (land)","attr" => "earlyorder_print_language","format" => "text","desc" => "Hvilket land der håndterer earlyordre forsendelser"),


);

$langSepList = array(
    array("name" => "Diverse","colspan" => 5),
    array("name" => "Autogave detaljer","colspan" => 2),
    array("name" => "Gavekort detaljer","colspan" => 4),
    array("name" => "Privatlevering","colspan" => 2),
    array("name" => "Kortgebyr","colspan" => 4),
    array("name" => "Levering","colspan" => 2),
    array("name" => "Opbæring","colspan" => 2),
    array("name" => "DOT","colspan" => 2),
    array("name" => "Indpakning","colspan" => 5),
    array("name" => "Forudfakturering","colspan" => 2),
    array("name" => "Slutfaktura","colspan" => 3),
    array("name" => "Gebyr for få kort","colspan" => 3),
    array("name" => "Web bestilling","colspan" => 2),
    array("name" => "Ventetid før synkronisering (i timer)","colspan" => 3),
    array("name" => "Leverance håndtering","colspan" => 2),
    array("name" => "Uge 47","colspan" => 4),
    array("name" => "Uge 48","colspan" => 4),
    array("name" => "Uge 49","colspan" => 4),
    array("name" => "Uge 50","colspan" => 4),
    array("name" => "Uge 04","colspan" => 4),
    array("name" => "Privatlevering","colspan" => 5),
    array("name" => "Special levering 1","colspan" => 5),
    array("name" => "Special levering 2","colspan" => 5),
    array("name" => "Andre","colspan" => 3)
);


ob_start();

?><div style="margin: 10px; padding: 10px; background: #ccc; border-radius: 15px; line-height: 125%;">
    Opsætning af alle cardshops i systemet. Tabellen herunder viser indstillinger for alle shops fordelt på land.<br>
    Indstillingerne er inddelt i grupper og kan ikke redigeres her pt., kontakt Søren eller Ulrich for ændringer.<br>
    Hold musen henover en overskrift eller værdi for at læse nærmere om den enkelte indstilling.
<?php if(!$hidebuttons) { ?>
    <p>
    <label><input type="checkbox" onchange="toggleLang(this,1)" checked>Vis DK</label>
    <label><input type="checkbox" onchange="toggleLang(this,4)" checked>Vis NO</label>
    <label><input type="checkbox" onchange="toggleLang(this,5)" checked>Vis SE</label>
    </p>
    <?php } ?>
    <script>

        function toggleLang(elm,lang) {
            if(elm.checked) {
                $(".langelm"+lang).show();
            } else {
                $(".langelm"+lang).hide();
            }
        }


    </script>

</div><?php

echo "<div style='width: 100%; overflow: auto;'><table class='cardshopsettings' style='width: 100%; border: none;' cellpadding='0' cellspacing='0'>";

echo "<tbody>";

$lastLang = -10;

foreach($shops as $shop) {

    if($lastLang != $shop->language_code) {

        echo "</tbody><thead class='langelm langelm".$shop->language_code."'><tr class='langsep'><th>".$formatter->langName($shop,null)."</th>";

        foreach($langSepList as $sep) {
            echo "<th colspan='".$sep["colspan"]."'>".$sep["name"]."</th>";
        }

        echo "</tr><tr>";

        foreach($tableDefs as $rowDef) {
            echo "<th title='".$rowDef["desc"]."'>".$rowDef["name"]."</th>";
        }

        echo "</tr></thead><tbody  class='langelm langelm".$shop->language_code."'>";

        $lastLang = $shop->language_code;
    }

    echo "<tr>";
    foreach($tableDefs as $rowDef) {

        $class = "";
        if(isset($rowDef["format"]) && trimgf($rowDef["format"]) != "") $class = $rowDef["format"];
        echo "<td title='".$rowDef["desc"]."' class='".$class."'>";

        if(isset($rowDef["format"]) && trimgf($rowDef["format"]) != "") {
            //echo $formatter->$rowDef["format"]($shop,$rowDef,$shop->attributes[$rowDef["attr"]]);
            echo call_user_func_array(array($formatter,$rowDef["format"]),array($shop,$rowDef,$shop->attributes[$rowDef["attr"]]));
            //echo $shop->attributes[$rowDef["attr"]];
        } else {
            echo $shop->attributes[$rowDef["attr"]];
        }

        echo "</td>";
    }
    echo "</tr>";



}



echo "</tbody><thead><tr class='langsep'><th>Forklaring</th>";

foreach($langSepList as $sep) {
    echo "<th colspan='".$sep["colspan"]."'>".$sep["name"]."</th>";
}

echo "</tr><tr>";

foreach($tableDefs as $rowDef) {
    echo "<th title='".$rowDef["desc"]."'>".$rowDef["name"]."</th>";
}

echo "<tr>";

foreach($tableDefs as $rowDef) {
    echo "<td title=''><div style='font-size: 10px;'>".wordwrap($rowDef["desc"], 30, "<br>", true)."</div></td>";
}

echo "</tr>";

echo "</tbody></table></div>";


/*
echo "<div style='width: 100%; overflow: auto;'><table class='cardshopsettings' style='width: 100%; border: none;' cellpadding='0' cellspacing='0'>";

echo "<thead><tr><th>Indstillinger</th>";

foreach($shops as $shop) {
    echo "<th class='langelm langelm".$shop->language_code."'>".$shop->name."</th>";
}

echo "</tr></thead><tbody>";

foreach($tableDefs as $rowDef) {
    echo "<tr><th title='".$rowDef["desc"]."'>".$rowDef["name"]."</th>";

    foreach($shops as $shop) {
        echo "<td class='langelm langelm".$shop->language_code."' title='".$rowDef["desc"]."'>";

        $class = "";
        if(isset($rowDef["format"]) && trimgf($rowDef["format"]) != "") $class = $rowDef["format"];
        echo "<span class='".$class."'>";

        if(isset($rowDef["format"]) && trimgf($rowDef["format"]) != "") {
            echo call_user_func_array(array($formatter,$rowDef["format"]),array($shop,$rowDef,$shop->attributes[$rowDef["attr"]]));
        } else {
            echo $shop->attributes[$rowDef["attr"]];
        }

        echo "</span></td>";
    }

    echo "</tr>";
}

echo "</tbody></table></div>";
*/


$content = ob_get_contents();
ob_end_clean();
echo ($content);