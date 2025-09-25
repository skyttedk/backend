<?php

namespace GFUnit\cardshop\status;
use GFBiz\units\UnitController;
use GFCommon\Model\Navision\FreightCalculator;

class ToolShopSetup
{


    public function showSetup($languages)
    {

        $langCodes = array(1 => "DK",4=> "NO", 5 => "SE");

        // Get concepts from cardshopsettings
        $concepts = \CardshopSettings::find_by_sql("SELECT concept_parent, language_code from cardshop_settings where is_hidden = 0 and language_code IN (".implode(",",$languages).") GROUP BY concept_parent, language_code order by concept_parent");


        $selectedConcept = $_GET["concept"] ?? "";
        $showConcept = "";

        ?><div>
            Vælg koncept der skal tjekkes: <select name="conceptcode">
                <option value="">Vælg koncept</option>
                <?php

                foreach($concepts as $concept){
                    $lang = $langCodes[$concept->language_code];
                    echo "<option value='".$concept->concept_parent."' ".($concept->concept_parent == $selectedConcept ? "selected" : "").">".$concept->concept_parent." - ".$lang."</option>";
                    if($concept->concept_parent == $selectedConcept) {
                        $showConcept = $concept->concept_parent;
                    }
                }

                ?>
            </select>
            <script>

                document.querySelector('select[name="conceptcode"]').addEventListener('change', function() {
                    // Hent den aktuelle URL som et URL-objekt
                    const url = new URL(window.location.href);

                    // Hvis værdien er tom, fjern parameteren, ellers sæt den
                    if (this.value === "") {
                        url.searchParams.delete('concept');
                    } else {
                        url.searchParams.set('concept', this.value);
                    }

                    // Opdater lokationen med den nye URL (som automatisk encoderer parametre)
                    window.location.href = url.toString();
                });

            </script>
    </div><?php

        if($showConcept == "") {

            // output message to select
            ?><div style="margin-top: 150px; text-align: center; font-weight: bold;">
                Vælg et koncept for at se indstillinger
            </div><?php

            return;
        }


        $shops = \CardshopSettings::find_by_sql("SELECT * FROM cardshop_settings where is_hidden = 0 and language_code IN (".implode(",",$languages).") and concept_parent like '".$showConcept."'");
        foreach($shops as $shop){

        }

        ?><div class="table-container">
        <table>

            <?php


            // Generelt
            $this->addHeader("Generelt", $shops, "Generelt om gavekortet");
            $this->addDataRow("Shop navn", $shops, "concept_name", "text", "Navn på shoppen");
            $this->addDataRow("Koncept", $shops, "concept_parent", "text", "Navn på gavekort konceptet");
            $this->addDataRow("Gavekort kode", $shops, "concept_code", "text", "Koden for dette gavekort");
            $this->addDataRow("Land", $shops, "language_code", "langName", "Land gavekortet sælges i");

            // Priser
            $this->addHeader("Priser", $shops, "Prisopsætning");
            $this->addDataRow("Pris på et gavekort", $shops, "card_price", "price", "Prisen på et enkelt gavekort");
            $this->addDataRow("DB på et gavekort", $shops, "card_db", "int", "Dækningsbidrag på et gavekort");
            $this->addDataRow("Moms", $shops, "card_moms_multiplier", "percentage", "Moms der ligges på et gavekort");
            $this->addDataRow("Miljøbidrag", $shops, "env_fee_percent", "float", "Miljøbidrag i % der ligges til en ordre");
            $this->addDataRow("Brug kortgebyr", $shops, "cardfee_use", "yesno", "Om der benyttes kortgebyr på fysiske kort");
            $this->addDataRow("Kortgebyr pris", $shops, "cardfee_price", "price", "Prisen på kortgebyr");
            $this->addDataRow("Kortgebyr min antal", $shops, "cardfee_minquantity", "text", "Hvis kortgebyr skal lægges til ordre under et bestemt antal kort angives det antal her");
            $this->addDataRow("Kortgebyr pr. kort", $shops, "cardfee_percard", "yesno", "Angiver om prisen på kortgebyr er per kort eller en samlet pris, ja = prisen ganges med antal kort");
            $this->addDataRow("Gebyr ved få kort", $shops, "minorderfee_use", "yesno", "Om der benyttes ekstra gebyr ved få kort");
            $this->addDataRow("Gebyr beløb", $shops, "minorderfee_price", "price", "Gebyr der benyttes ved få kort");
            $this->addDataRow("Min. antal kort før gebyr", $shops, "minorderfee_mincards", "text", "Minimum antal kort før gebyr tilføjes");

            // Gebyrer og tillæg
            $this->addHeader("Gebyrer og tillæg", $shops, "Opsætning af gebyrer og tillæg");
            $this->addDataRow("Beregn fragt automatisk", $shops, "calculate_freight", "yesno", "Angiver om systemet automatisk beregner fragten.");
            $this->addDataRow("Fakturagebyr - forudfakturering", $shops, "invoiceinitial_use", "active", "Angiver om der bruges fakturagebyr på forudfaktureringen");
            $this->addDataRow("Fakturagebyr pris", $shops, "invoiceinitial_price", "price", "Prisen på fakturagebyr på forudfakturering");
            $this->addDataRow("Fakturagebyr - slutfaktura", $shops, "invoicefinal_use", "active", "Angiver om der bruges fakturagebyr på slutfaktura");
            $this->addDataRow("Fakturagebyr pris", $shops, "invoicefinal_price", "price", "Prisen på fakturagebyr på slutfaktura");


            // Opbæring og indpakning
            $this->addHeader("DOT, opbæring og indpakning", $shops, "Opsætning for opbæring og indpakning");
            $this->addDataRow("DOT", $shops, "dot_use", "active", "Angiver om der bruges DOT levering");
            $this->addDataRow("DOT pris", $shops, "dot_price", "price", "Prisen på DOT levering");
            $this->addDataRow("Indpakning", $shops, "giftwrap_use", "active", "Angiver om der bruges indpakning");
            $this->addDataRow("Indpakning pris", $shops, "giftwrap_price", "price", "Prisen på indpakning");
            $this->addDataRow("Varenr v. ingen indpak", $shops, "giftwrap_notset_itemno", "text", "Varenr der sendes til nav når der ikke er valgt indpak");
            $this->addDataRow("Navnelabels", $shops, "namelabels_use", "active", "Angiver om der bruges navnelabels (excl. indpak)");
            $this->addDataRow("Navnelabels pris", $shops, "namelabels_price", "price", "Prisen på navnelabels");



            // Levering og forsendelse
            $this->addHeader("Levering og forsendelse", $shops, "Opsætning for levering og forsendelse");
            $this->addDataRow("Privatlevering", $shops, "privatedelivery_use", "active", "Angiver om der bruges privatlevering på denne shop");
            $this->addDataRow("Privatlevering pris", $shops, "privatedelivery_price", "price", "Prisen på privatlevering pr. kort");
            $this->addDataRow("Levering af kort", $shops, "carddelivery_use", "active", "Angiver om der bruges levering af fysiske kort");
            $this->addDataRow("Levering af kort pris", $shops, "carddelivery_price", "price", "Prisen på levering af fysiske kort (samlet pris, ikke pr. kort men pr. leveringssted)");
            $this->addDataRow("Opbæring", $shops, "carryup_use", "active", "Angiver om der bruges opbæring");
            $this->addDataRow("Opbæring pris", $shops, "carryup_price", "price", "Prisen på opbæring");
            $this->addDataRow("Forsendelsesland", $shops, "shipment_print_language", "langName", "Land der sender fysiske gavekort på konceptet.");

            $this->addHeader("Web bestillinger", $shops, "Opsætning af web bestillinger");
            $this->addDataRow("Web sælger", $shops, "web_salesperson", "text", "Sælger-kode for web bestillinger");
            $this->addDataRow("Minimum antal ved web bestilling", $shops, "min_web_cards", "text", "Mindste antal kort der kan bestilles ved web bestilling.");
            $this->addDataRow("Stop fysiske kort", $shops, "physical_close_days", "int", "Antal dage før bestilling af e-mail kort, hvor der lukkes for bestilling af fysiske kort.");

            $this->addHeader("Autovalg", $shops, "Opsætning af autovalg");
            $this->addDataRow("Varnr på autovalg", $shops, "default_present_itemno", "text", "Varenr på autogaven på gavekortet");
            $this->addDataRow("Navn på autovalg", $shops, "default_present_name", "text", "Navn på autogaven på gavekortet");


            $this->addHeader("Mix af beløb", $shops,"Opsætning for mix af beløb");
            $this->addDataRow("Gavekort beløb", $shops,"card_values", "text","Hvis angivet er det muligt at vælge mellem flere beløb og de er så angivet her.");
            $this->addDataRow("Tillægsbeløb", $shops,"bonus_presents", "text","Hvis det er muligt at låse op for ekstra gaver for en merpris angives beløbet her.");

            // Diverse indstillinger
            $this->addHeader("Synkronisering", $shops, "Indstillinger for synkronisering af data på konceptet");
            $this->addDataRow("Sync cardshop ordre", $shops, "navsync_orders", "yesno", "Synkroniseres ordre i cardshop pt.");
            $this->addDataRow("Synkronisering af cardshop ordre", $shops, "ordercs_syncwait", "text", "Antal timer der går fra ordre er oprettet af sælger i cardshop til den lægges i navision");
            $this->addDataRow("Synkronisering af web ordre", $shops, "orderweb_syncwait", "text", "Antal timer der går fra ordre er oprettet via web bestilling til den lægges i navision");
            $this->addDataRow("Sync forsendelser", $shops, "navsync_shipments", "yesno", "Synkroniseres forsendelser af gavekort");
            $this->addDataRow("Synkronisering af gavekort forsendelse", $shops, "shipment_syncwait", "text", "Antal timer der går fra ordre er synkroniseret til en forsendelse må sendes til navision.");
            $this->addDataRow("Send gavekoder på e-mail", $shops, "send_certificates", "yesno", "Send gavekoder på e-mail");
            $this->addDataRow("Sync privatleveringer", $shops, "navsync_privatedelivery", "yesno", "Synkroniseres privatleveringer pt.");
            $this->addDataRow("Sync earlyorder", $shops, "navsync_earlyorders", "yesno", "Synkroniseres earlyordre pt.");

            // Specielle datoer
            $this->addHeader("Specielle datoer", $shops, "Opsætning af specielle datoer");
            $this->addDataRow("Udløb af privatlevering", $shops, "private_expire_date", "text", "Hvornår privatleveringskort udløber");


            // Uge 48
            $this->addHeader("Uge 48", $shops, "Uge 48 opsætning");
            $this->addDataRow("Åben for valg", $shops, "week_48_open", "date", "Hvornår der åbnes for valg på uge 48");
            $this->addDataRow("Luk for valg", $shops, "week_48_close", "date", "Hvornår der lukkes for valg på uge 48");
            $this->addDataRow("Stop salg - web", $shops, "week_48_close_websale", "date", "Hvornår der lukkes salg via hjemmeside til uge 48 (fysiske kort lukkes før denne dato)");
            $this->addDataRow("Stop salg - cardshop", $shops, "week_48_close_sale", "date", "Hvornår der lukkes salg via cardshop til uge 48");

            // Uge 49
            $this->addHeader("Uge 49", $shops, "Uge 49 opsætning");
            $this->addDataRow("Åben for valg", $shops, "week_49_open", "date", "Hvornår der åbnes for valg på uge 49");
            $this->addDataRow("Luk for valg", $shops, "week_49_close", "date", "Hvornår der lukkes for valg på uge 49");
            $this->addDataRow("Stop salg - web", $shops, "week_49_close_websale", "date", "Hvornår der lukkes salg via hjemmeside til uge 49 (fysiske kort lukkes før denne dato)");
            $this->addDataRow("Stop salg - cardshop", $shops, "week_49_close_sale", "date", "Hvornår der lukkes salg via cardshop til uge 49");

            // Uge 50
            $this->addHeader("Uge 50", $shops, "Uge 50 opsætning");
            $this->addDataRow("Åben for valg", $shops, "week_50_open", "date", "Hvornår der åbnes for valg på uge 50");
            $this->addDataRow("Luk for valg", $shops, "week_50_close", "date", "Hvornår der lukkes for valg på uge 50");
            $this->addDataRow("Stop salg - web", $shops, "week_50_close_websale", "date", "Hvornår der lukkes salg via hjemmeside til uge 50 (fysiske kort lukkes før denne dato)");
            $this->addDataRow("Stop salg - cardshop", $shops, "week_50_close_sale", "date", "Hvornår der lukkes salg via cardshop til uge 50");

            // Uge 51
            $this->addHeader("Uge 51", $shops, "Uge 51 opsætning");
            $this->addDataRow("Åben for valg", $shops, "week_51_open", "date", "Hvornår der åbnes for valg på uge 51");
            $this->addDataRow("Luk for valg", $shops, "week_51_close", "date", "Hvornår der lukkes for valg på uge 51");
            $this->addDataRow("Stop salg - web", $shops, "week_51_close_websale", "date", "Hvornår der lukkes salg via hjemmeside til uge 51 (fysiske kort lukkes før denne dato)");
            $this->addDataRow("Stop salg - cardshop", $shops, "week_51_close_sale", "date", "Hvornår der lukkes salg via cardshop til uge 51");

            // Uge 04
            $this->addHeader("Uge 04", $shops, "Uge 04 opsætning");
            $this->addDataRow("Åben for valg", $shops, "week_04_open", "date", "Hvornår der åbnes for valg på uge 04");
            $this->addDataRow("Luk for valg", $shops, "week_04_close", "date", "Hvornår der lukkes for valg på uge 04");
            $this->addDataRow("Stop salg - web", $shops, "week_04_close_websale", "date", "Hvornår der lukkes salg via hjemmeside til uge 04 (fysiske kort lukkes før denne dato)");
            $this->addDataRow("Stop salg - cardshop", $shops, "week_04_close_sale", "date", "Hvornår der lukkes salg via cardshop til uge 04");

            // Privat levering
            $this->addHeader("Privat levering", $shops, "Privat levering opsætning");
            $this->addDataRow("Åben for valg", $shops, "private_open", "date", "Hvornår der åbnes for valg på uge private");
            $this->addDataRow("Luk for valg", $shops, "private_close", "date", "Hvornår der lukkes for valg på uge private");
            $this->addDataRow("Stop salg - web", $shops, "private_close_websale", "date", "Hvornår der lukkes salg via hjemmeside til uge private (fysiske kort lukkes før denne dato)");
            $this->addDataRow("Stop salg - cardshop", $shops, "private_close_sale", "date", "Hvornår der lukkes salg via cardshop til uge private");
            $this->addDataRow("Udløb af privatlevering", $shops, "private_expire_date", "text", "Hvornår privatleveringskort udløber");

            // Speciel Privat Levering 1
            $this->addHeader("Speciel Privat Levering 1", $shops, "Speciel Privat Levering 1 opsætning");
            $this->addDataRow("Luk for valg", $shops, "special_private1_close", "date", "Hvornår der lukkes for valg på norsk speciallevering 1");
            $this->addDataRow("Stop salg - web", $shops, "special_private1_close_websale", "date", "Hvornår der lukkes salg via hjemmeside til norsk speciallevering 1");
            $this->addDataRow("Stop salg - cardshop", $shops, "special_private1_close_sale", "date", "Hvornår der lukkes salg via cardshop til norsk speciallevering 1");
            $this->addDataRow("Udløb af levering", $shops, "special_private1_expiredate", "text", "Hvornår norsk speciallevering 1 udløber");

            // Speciel Privat Levering 2
            $this->addHeader("Speciel Privat Levering 2", $shops, "Speciel Privat Levering 2 opsætning");
            $this->addDataRow("Åben for valg", $shops, "special_private2_open", "date", "Hvornår der åbnes for valg på norsk speciallevering 2");
            $this->addDataRow("Luk for valg", $shops, "special_private2_close", "date", "Hvornår der lukkes for valg på norsk speciallevering 2");
            $this->addDataRow("Stop salg - web", $shops, "special_private2_close_websale", "date", "Hvornår der lukkes salg via hjemmeside til norsk speciallevering 2");
            $this->addDataRow("Stop salg - cardshop", $shops, "special_private2_close_sale", "date", "Hvornår der lukkes salg via cardshop til norsk speciallevering 2");
            $this->addDataRow("Udløb af levering", $shops, "special_private2_expiredate", "text", "Hvornår norsk speciallevering 2 udløber");

            
            // Tech
            $this->addHeader("Teknik / Intern", $shops, "Interne / tekniske informationer");
            $this->addDataRow("Shop ID", $shops, "shop_id", "int", "ID på valgshop der bruges til gavekortet");
            $this->addDataRow("FLydende deadline", $shops, "floating_expire_months", "int", "Hvis der er flydende deadline på kortet angiver dette antal måneder fra bestillingstidspunktet");
            $this->addDataRow("Earlyordre håndtering", $shops, "earlyorder_handler", "text", "Hvilket system der håndterer earlyordre");
            $this->addDataRow("Earlyordre håndteres i (land)", $shops, "earlyorder_print_language", "text", "Hvilket land der håndterer earlyordre forsendelser");
            $this->addDataRow("Privatlevering håndtering", $shops, "privatedelivery_handler", "text", "Hvilket system der håndterer privatlevering");
            $this->addDataRow("Erstatningsgavekort virksomhed", $shops, "replacement_company_id", "text", "Virksomhed som erstatningskort tilknyttes");
            $this->addDataRow("Rækkefølge", $shops, "show_index", "int", "Rækkefølge hvor dette gavekort vises på lister");

            ?>
        </table>
    </div><?php

    }

    private function addHeader($name,$shops,$help)
    {
        ?>
        <thead>
            <tr>
                <th title="<?php echo $help; ?>">
                    <?php echo $name; ?>
                </th><?php

                foreach($shops as $shop) {
                    ?><th><?php echo $shop->concept_name; ?></th><?php
                }

                ?><th>
                    <?php if(trim($help) != "") { ?><span class="icon" title="<?php echo $help; ?>">🔍</span><?php } ?>
                </th>
            </tr>

        </thead>
        <?php

    }

    private function addDataRow($name,$shops,$key,$handler,$help) {
        ?>
        <tbody>
            <tr>
                <td title="<?php echo $help; ?>"><?php echo $name; ?></td><?php

                foreach($shops as $shop) {


                    $rawValue = $shop->attributes[$key];
                    $formattedValue = $rawValue;

                    echo "<td>";
                    if(isset($handler) && trimgf($handler) != "") {
                        echo call_user_func_array(array($this,$handler),array($shop,$rawValue));
                    } else {
                        echo $rawValue;
                    }

                    echo "</td>";
                }

                ?><td>
                    <?php if(trim($help) != "") { ?><span class="icon" title="<?php echo $help; ?>">🔍</span><?php } ?>
                </td>
            </tr>
        </tbody>
        <?php
    }




    public function langName($shop) {
        if($shop->language_code == 1) return "Danmark";
        else if($shop->language_code == 2) return "England";
        else if($shop->language_code == 3) return "Tyskland";
        else if($shop->language_code == 4) return "Norge";
        else if($shop->language_code == 5) return "Sverige";
        else return "UKENDT!";
    }

    public function int($shop,$val) {
        return $val;
    }

    public function price($shop,$val) {
        return number_format(intval($val)/100,2,",",".");
    }

    public function float($shop,$val) {
        return number_format(($val),2,",",".");
    }

    public function percentage($shop,$val) {
        return number_format(($val-1)*100,0,",",".")."%";
    }

    public function text($shop,$val) {
        return (trimgf($val));
    }

    public function active($shop,$val) {
        return ($val == 0) ? "Bruges ikke" : "<b>Aktiveret</b>";
    }

    public function yesno($shop,$val) {
        return ($val == 1) ? "Ja" : "Nej";
    }

    public function date($shop,$val) {
        if($val == null) return "";
        else echo $val->format("Y-m-d H:i");
    }


}