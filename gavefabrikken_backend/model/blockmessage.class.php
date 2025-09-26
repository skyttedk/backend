<?php

class BlockMessage extends BaseModel {

    static $table_name = "blockmessage";
    static $primary_key = "id";

    //Relations
    static $has_many = array();

    /**
     * DB EVENTS
     */

    static $before_create = array('onBeforeCreate');
    function onBeforeCreate() {
        $this->created_date = date('d-m-Y H:i:s');
        $this->created_by = router::$systemUser == null ? 0 : router::$systemUser->id;
        $this->validateFields();
    }

    static $after_create = array('onAfterCreate');
    function onAfterCreate() { }

    static $before_update = array('onBeforeUpdate');
    function onBeforeUpdate() {
        $this->validateFields();
    }

    static $after_update = array('onAfterUpdate');
    function onAfterUpdate() { }

    static $before_destroy = array('onBeforeDestroy');
    function onBeforeDestroy() { }

    static $after_destroy = array('onAfterDestroy');
    function onAfterDestroy() { }

    /**
     * FIELD VALIDATIN
     */

    function validateFields() {

    }

    public function isCompanyBlock()
    {
        return $this->company_id > 0 && $this->company_order_id == 0;
    }

    public function isCompanyOrderBlock()
    {
        return $this->company_order_id > 0;
    }

    public function getBlockTypeText()
    {
        if($this->isCompanyBlock()) return "Kunde blokkeret";
        else if($this->isCompanyOrderBlock()) return "Ordre blokkeret";
        else return "Anden blokkering";
    }

    /**
     * BLOCK TYPES
     */

    public static function getBlockTypes()
    {
        return array(
            "COMPANY_APPROVED_NOTVALIDATED" => array("actions" => array("approvecompany"),"description" => "Det valgte kundenr kunne ikke findes i navision"),
            "COMPANY_MISSING_LANGUAGE" => array("actions" => array("approvecompany"),"description" => "Kunde mangler landekode"),
            "COMPANY_BAD_MATCH" => array("actions" => array("approvecompany"),"description" => "Kunde har ikke et perfekt match i navision."),
            "COMPANY_DATA_MISSING" => array("actions" => array("approvecompany"),"description" => "Der mangler data på kunde-profilen."),
            "COMPANY_XML_EXCEPTION" => array("actions" => array("approvecompany"),"description" => "Exception occured during customer xml generation."),
            "COMPANY_CREATE_EXCEPTION" => array("actions" => array("approvecompany"),"description" => "Exception occured during customer creation in nav"),
            "COMPANY_LOOKUP_EXCEPTION" => array("actions" => array("approvecompany"),"description" => "Exception in navision lookup to search for customer"),
            "COMPANY_NEW_MISMATCH" => array("actions" => array("companymismatchapprove","companymismatchdecline","companymismatchretry"),"description" => "Ny kunde har matches i navision, men der er forskel i data"),
            "COMPANY_PARENT_LANGDIF" => array("actions" => array("approvecompany"),"description" => "Parent and child company does not have the same language code."),
            "COMPANY_PARENT_INVALID" => array("actions" => array("approvecompany"),"description" => "Parent company is set but does not exist, check reference."),
            "COMPANY_UPDATE_INVOICEDATA" => array("actions" => array("approvecompany"),"description" => "Kunde faktura data opdateret, opdater manuelt i navision"),
            "COMPANY_SUSPECTED_TEST" => array("actions" => array("approvecompany","deletecompany"),"description" => "Virksomheden ligner en test kunde."),
            "COMPANY_NEW_IMPORT" => array("actions" => array("approvecompany"),"description" => "Ny virksomhed oprettet via web-bestilling."),
            "COMPANY_EMAIL_INVALID" => array("actions" => array("approvecompany"),"description" => "E-mail ikke gyldig, skal rettes"),
            "COMPANY_MANUAL_BLOCK" => array("actions" => array("approvecompany"),"description" => "Manuel blokkering oprettet, se besked"),
            "COMPANY_INVALID_CVR" => array("actions" => array("approvecompany"),"description" => "Kundens cvr nr. kunne ikke verificeres, tjek."),
            "COMPANY_CUSTOMERNO_CHANGE" => array("actions" => array("approvecc","cancelcc"),"description" => "Anmodning om ændring af navision kundenr."),

            
            "COMPANYORDER_MISSING_LANGUAGE" => array("actions" => array("approveorder"),"description" => "Company is missing language code"),
            "COMPANYORDER_NO_COMPANYSYNC" => array("actions" => array("approveorder"),"description" => "Company is not synced to navision"),
            "COMPANYORDER_XML_ERROR" => array("actions" => array("approveorder"),"description" => "Order XML error"),
            "COMPANYORDER_SYNC_ERROR" => array("actions" => array("approveorder"),"description" => "Fejl i ordre synkronisering"),
            "COMPANYORDER_SHOP_ERROR" => array("actions" => array("approveorder"),"description" => "Shop settings error"),
            "COMPANYORDER_LANGUAGE_DIFF" => array("actions" => array("approveorder"),"description" => "Language difference in order and shop settings"),
            "COMPANYORDER_COMPANY_STATE" => array("actions" => array("approveorder"),"description" => "Error in company state"),
            "COMPANYORDER_COMPANY_BLOCKED" => array("actions" => array("approveorder","unlinkcompany"),"description" => "Kunde blokkeret i navision"),
            "COMPANYORDER_SALESPERSON_MISSING" => array("actions" => array("approveorder"),"description" => "Ordre mangler sælgerkode"),
            "COMPANYORDER_COUNTRY_INVALID" => array("actions" => array("approveorder"),"description" => "Mangler lang på ordre"),
            "COMPANYORDER_SUSPECTED_TEST" => array("actions" => array("approveorder","deleteorder"),"description" => "Ordren ligner en testordre, godkend kun hvis den skal overføres til navision"),
            "COMPANYORDER_SALENOTE" => array("actions" => array("approveorder"),"description" => "Ordre har en note der skal godkendes"),
            "COMPANYORDER_SALEPERSON" => array("actions" => array("approveorder"),"description" => "Ordre har en ugyldig salgsperson kode"),
            "COMPANYORDER_EMAIL_INVALID" => array("actions" => array("approveorder"),"description" => "E-mail ikke gyldig, skal rettes"),
            "COMPANY_EANCONTACT_MISSING" => array("actions" => array("approveorder"),"description" => "Der skal angives en kontaktperson i navision på EAN kunder"),
            "COMPANYORDER_CONTACT_MISMATCH" => array("actions" => array("approveorder"),"description" => "Kontaktperson på ordre og i navision er ikke ens."),
            "COMPANYORDER_NAV_FIXFAULT" => array("actions" => array("approveorder"),"description" => "Der er et problem med ordren i navision som skal ordnes, derefter kan denne fejl frigives. Tjek navision."),
            "COMPANYORDER_FRONTEND_ERROR" => array("actions" => array("approveorder"),"description" => "Der er opstået et problem i frontend ved oprettelse, tjek ordre."),
            "COMPANYORDER_REOPEN" => array("actions" => array("approveorder"),"description" => "Ordre krediteret og genåbnet"),
            "COMPANYORDER_MULTIPLE_VALUES" => array("actions" => array("approveorder"),"description" => "Godkend værdier og fortsæt"),

            "COMPANYORDER_NOPREPAYMENT" => array("actions" => array("approveorder"),"description" => "Forudfakturering er slået fra på ordren."),
            "COMPANYORDER_PREPAYMENTDATE" => array("actions" => array("approveorder"),"description" => "Ordre har forudfaktureringsdato, tjek om den er korrekt"),
            "COMPANYORDER_PREPAYMENTDUEDATE" => array("actions" => array("approveorder"),"description" => "Ordre har betalingsfrist for forudfakturering, tjek om den er korrekt"),
            
            "COMPANYORDER_ITEM_BLOCKED" => array("actions" => array("approveorder"),"description" => "Varer kunden har valgt er blokkeret i nav."),
            "COMPANYORDER_ITEM_CONCEPT_MISSING" => array("actions" => array("approveorder"),"description" => "Ordren har ingen conceptlinje."),
            "COMPANYORDER_ITEM_CONCEPT_PRICE" => array("actions" => array("approveorder"),"description" => "Pris på koncept er ændret"),
            "COMPANYORDER_ITEM_CONCEPT_FREECARDS" => array("actions" => array("approveorder"),"description" => "Gratis kort på ordren"),
            "COMPANYORDER_ITEM_CONCEPT_ZERO" => array("actions" => array("approveorder"),"description" => "Gavekort pris er 0"),
            "COMPANYORDER_ITEM_PRIVATEDELIVERY_INVALIDFEE" => array("actions" => array("approveorder"),"description" => "Privatlevering bør ikke være på ordre uden privatlevering"),
            "COMPANYORDER_ITEM_PRIVATEDELIVERY_NOFEE" => array("actions" => array("approveorder"),"description" => "Privatleverings ordre har ikke gebyr"),
            "COMPANYORDER_ITEM_PRIVATEDELIVERY_PRICE" => array("actions" => array("approveorder"),"description" => "Pris ændret på privatlevering"),
            "COMPANYORDER_ITEM_CARDFEE_PRICE" => array("actions" => array("approveorder"),"description" => "Kortgebyr pris ændret"),
            "COMPANYORDER_ITEM_CARDDELIVERY_EMAILCARDS" => array("actions" => array("approveorder"),"description" => "Kort leveringsgebyr på ordre med e-mail kort"),
            "COMPANYORDER_ITEM_CARDDELIVERY_NOFEE" => array("actions" => array("approveorder"),"description" => "Ordre med fysiske kort har ikke kortleverings gebyr"),
            "COMPANYORDER_ITEM_CARDDELIVERY_PRICE" => array("actions" => array("approveorder"),"description" => "Delivery price changed on order"),
            "COMPANYORDER_ITEM_CARDDELIVERY_MISMATCH" => array("actions" => array("approveorder"),"description" => "Delivery fee quantity does not match shipment addresses."),
            "COMPANYORDER_ITEM_CARRYUP_NOTSELECTED" => array("actions" => array("approveorder"),"description" => "Opbæring ikke valgt på ordre, men er opkrævet"),
            "COMPANYORDER_ITEM_CARRYUP_NOFEE" => array("actions" => array("approveorder"),"description" => "Opbæring valgt på ordre men er ikke opkrævet."),
            "COMPANYORDER_ITEM_CARRYUP_PRICE" => array("actions" => array("approveorder"),"description" => "Pris på opbæring er ændret"),
            "COMPANYORDER_ITEM_DOT_NOTSELECTED" => array("actions" => array("approveorder"),"description" => "Der er ikke DOT på ordre, men den har DOT gebyr"),
            "COMPANYORDER_ITEM_DOT_NOFEE" => array("actions" => array("approveorder"),"description" => "DOT er valg på ordren, men ordren har ikke DOT gebyr."),
            "COMPANYORDER_ITEM_DOT_PRICE" => array("actions" => array("approveorder"),"description" => "DOT gebyr pris ændret"),
            "COMPANYORDER_ITEM_GIFTWRAP_NOTSELECTED" => array("actions" => array("approveorder"),"description" => "Indpakning ikke valgt men bliver opkrævet"),
            "COMPANYORDER_ITEM_GIFTWRAP_NOFEE" => array("actions" => array("approveorder"),"description" => "Indpakning valgt men bliver ikke opkrævet på ordre"),
            "COMPANYORDER_ITEM_GIFTWRAP_PRIVATE" => array("actions" => array("approveorder"),"description" => "Indpakning valgt på privatlevering, det er ikke understøttet!"),
            "COMPANYORDER_ITEM_GIFTWRAP_PRICE" => array("actions" => array("approveorder"),"description" => "Pris på indpakning er ændret"),
            "COMPANYORDER_ITEM_INVOICEFEEINITIAL_CHANGED" => array("actions" => array("approveorder"),"description" => "Fakturagebyr på forudfaktura er ændret"),
            "COMPANYORDER_ITEM_INVOICEFEEFINAL_CHANGED" => array("actions" => array("approveorder"),"description" => "Fakturagebyr på slutfaktura er ændret"),
            "COMPANYORDER_ITEM_MINORDERFEE_CHANGED" => array("actions" => array("approveorder"),"description" => "Pris for gebyr for lille ordre er ændret"),
            "COMPANYORDER_ITEM_NAMELABELFEE_CHANGED" => array("actions" => array("approveorder"),"description" => "Pris for gebyr for navnelabels er ændret"),
            "COMPANYORDER_ITEM_DOT_COMPANY_MISMATCH" => array("actions" => array("approveorder"),"description" => "DOT, der er forskellige værdier på ordre for samme kunde / koncept / leveringsuge"),
            "COMPANYORDER_ITEM_CARRYUUP_COMPANY_MISMATCH" => array("actions" => array("approveorder"),"description" => "Opbæring, der er forskellige værdier på ordre for samme kunde / koncept / leveringsuge"),
            "COMPANYORDER_ITEM_GIFTWRAP_COMPANY_MISMATCH" => array("actions" => array("approveorder"),"description" => "Indpakning, der er forskellige værdier på ordre for samme kunde / koncept / leveringsuge"),
            "COMPANYORDER_SALESPERSON_CHANGE" => array("actions" => array("approvesp","cancelsp"),"description" => "Bruger har anmodet om ændring af sælger."),
            "COMPANYORDER_ITEM_BONUSPRICE_MISSING" => array("actions" => array("approveorder"),"description" => "Bonuspris mangler på ordre"),

            "SHIPMENT_UNKNOWN_STATE" => array("actions" => array("approveship"),"description" => "Unknown state of shipment"),
            "SHIPMENT_UNKNOWN_TYPE" => array("actions" => array("approveship"),"description" => "Unknown shipment type"),
            "SHIPMENT_INVALID_ORDERSTATE" => array("actions" => array("approveship"),"description" => "Invalid order state"),
            "SHIPMENT_SYNC_ERROR" => array("actions" => array("approveship"),"description" => "Error syncing shipment to navision"),
            "SHIPMENT_COUNT_WARNING" => array("actions" => array("approveship"),"description" => "Please check giftcard count"),
            "SHIPMENT_QUANTITY_WARNING" => array("actions" => array("approveship"),"description" => "Please check giftcard quantity"),
            "SHIPMENT_OTHER_BLOCKED" => array("actions" => array("approveship"),"description" => "Blocked because of problem on other shipments on same order"),
            "SHIPMENT_NUMBER_COLLISION" => array("actions" => array("approveship"),"description" => "Shipment is invalid, has overlap on gift certificate numbers as another shipment"),
            "SHIPMENT_INVALID_ADDRESS" => array("actions" => array("approveship"),"description" => "Ikke nok data på leverance adresse"),
            "SHIPMENT_XML_ERROR" => array("actions" => array("approveship"),"description" => "Error generating shipment xml document"),
            "SHIPMENT_CARDS_BLOCKED" => array("actions" => array("approveship"),"description" => "Ingen aktive kort i forsendelse"),
            "SHIPMENT_UUID" => array("actions" => array("approveship"),"description" => "UUID shipment, hold back"),
            "SHIPMENT_HAS_SYNCDATE" => array("actions" => array("approveship"),"description" => "Shipment already has sync date"),
            "SHIPMENT_EMAIL_INVALID" => array("actions" => array("approveship"),"description" => "E-mail ikke gyldig, skal rettes"),
            "SHIPMENT_PRIVATE_CHECK" => array("actions" => array("approveship"),"description" => "Privatleverings tjek fejlet"),
            "FOREIGN_DELIVERY" => array("actions" => array("approveship","blockship"),"description" => "Mulig udenlands levering"),
            "MANUAL_CHECK" => array("actions" => array("approveship"),"description" => "Manuelt tjek af leveringsadresse"),
            "MISSING_USER_DATA" => array("actions" => array("approveship"),"description" => "Leveringsinformationer mangler"),

        );
    }

    public static function hasBlockType($blocktype) {
        $blockTypes = self::getBlockTypes();
        return isset($blockTypes[$blocktype]);
    }

    public static function getBlockType($blocktype) {
        $blockTypes = self::getBlockTypes();
        return $blockTypes[$blocktype];
    }

    public static function getBlockTypeDescription($blocktype)
    {
        $blockTypes = self::getBlockTypes();
        if(!isset($blockTypes[$blocktype])) return "Unknown block type";
        else return $blockTypes[$blocktype]["description"];
    }

    /*
     * ACTION
     */

    public static function getActions()
    {
        return array(
            "approveonly" => array("code" => "approve","name" => "Godkend","description" => "Godkendt og skjul denne besked"),
            "approvecompany" => array("code" => "approvecompany","name" => "Godkend","description" => "Godkend og fortsæt behandling af kunde"),
            "approveorder" => array("code" => "approveorder","name" => "Godkend","description" => "Godkend og fortsæt behandling af ordre"),
            "approveship" => array("code" => "approveship","name" => "Godkend","description" => "Godkend og fortsæt behandling af leverance"),
            "blockship" => array("code" => "blockship","name" => "Bloker","description" => "Annullerer og blokerer en leverance"),
            
            "deletecompany" => array("code" => "deletecompany","name" => "Slet kunde","description" => "Slet kunde og kundens ordre. Data slettes ikke men deaktiveres og blokkeres. Faktura sendt til navision for kunden vil blive krediteret."),
            "deleteorder" => array("code" => "deleteorder","name" => "Slet ordre","description" => "Data slettes ikke men ordren og gavekort deaktiveres og blokkeres. Faktura sendt til navision for ordren vil blive krediteret."),
            "unlinkcompany" => array("code" => "unlinkcompany","name" => "Nulstil kundens debitor nr","description" => "Skal kunden knyttes til en anden debitor så vælg denne for at synkronisere kunden mod nav forfra og give kunden et nyt debitor nr, sørg for at der findes en åben debitor der matcher først. Kan fejle hvis denne kunde allerede har aktive ordre."),
            
            "companymismatchapprove" => array("code" => "companymismatchapprove","name" => "Synkroniser med bedste match","description" => "Kunden kobles med det bedste match i navision."),
            "companymismatchdecline" => array("code" => "companymismatchdecline","name" => "Opret som ny debitor, create new customer","description" => "Opret kunden som en ny debitor i navision, med data fra cardshop."),
            "companymismatchretry" => array("code" => "companymismatchretry","name" => "Forsøg synkronisering igen", "description" => "Prøv at synkroniser igen. Har du ændret data i cardshop eller navision er der så mulighed for at der kan synkroniseres automatisk."),

            "approvesp" => array("code" => "approvesp","name" => "Godkend ændring","description" => "Godkend ændring"),
            "cancelsp" => array("code" => "cancelsp","name" => "Afvis ændring","description" => "Afvis ændring"),

            "approvecc" => array("code" => "approvecc","name" => "Godkend ændring","description" => "Godkend ændring"),
            "cancelcc" => array("code" => "cancelcc","name" => "Afvis ændring","description" => "Afvis ændring")
        );
    }

    public static function getAction($action)
    {
        $actions = self::getActions();
        return $actions[$action];
    }

    public static function hasAction($action) {
        $actions = self::getActions();
        return isset($actions[$action]);
    }

    public static function hasApprovedMessage($type,$companyid,$companyorderid,$shipmentid) {
        $messages = self::find_by_sql("SELECT * FROM `blockmessage` WHERE `company_id` = ".intval($companyid)." AND `company_order_id` = ".intval($companyorderid)." AND `shipment_id` = ".intval($shipmentid)." AND `block_type` LIKE '".$type."' && release_status = 1");
        return countgf($messages) > 0;
    }

    /**
     * CREATE HELPERS
     */

    public static function createCompanyBlock($companyid,$blocktype,$description,$isTechBlock = false,$debugData=null)
    {

        $bm = new BlockMessage();
        $bm->company_id = $companyid;
        $bm->company_order_id = 0;
        $bm->block_type = $blocktype;
        $bm->description = $description;
        $bm->release_status = 0;
        $bm->tech_block = $isTechBlock ? 1 : 0;

        if($debugData != null) {
            if(is_object($debugData)) {
                $bm->debug_data = json_encode($debugData);

            }
            else if(is_array($debugData)) {
                if(count($debugData) > 0) {
                    $bm->debug_data = implode("\r\n",$debugData);
                }
            }
            else if(trimgf($debugData) != "") {
                $bm->debug_data = $debugData;
            }
        }

        $bm->save();

        self::sendApprovalEmail($bm);

    }


    public static function createShipmentBlock($companyid,$company_order_id,$shipment_id,$blocktype,$description,$isTechBlock = false,$debugData=null)
    {
        $bm = new BlockMessage();
        $bm->company_id = $companyid;
        $bm->company_order_id = $company_order_id;
        $bm->block_type = $blocktype;
        $bm->description = $description;
        $bm->release_status = 0;
        $bm->tech_block = $isTechBlock ? 1 : 0;
        $bm->shipment_id = $shipment_id;

        if($debugData != null) {
            if(is_object($debugData)) {
                $bm->debug_data = json_encode($debugData);

            }
            else if(is_array($debugData)) {
                if(count($debugData) > 0) {
                    $bm->debug_data = implode("\r\n",$debugData);
                }
            }
            else if(trimgf($debugData) != "") {
                $bm->debug_data = $debugData;
            }
        }

        $bm->save();

        self::sendApprovalEmail($bm);

    }

    public static function isReplacementCard($companyID){
        if(intval($companyID) <= 0) return false;
        $cardshopWithReplacement = \CardshopSettings::find('first',array("conditions" => array("replacement_company_id" => intval($companyID))));
        if($cardshopWithReplacement == null || $cardshopWithReplacement->id == 0) return false;
        else return true;
    }

    public static function createCompanyOrderBlock($companyid,$company_order_id,$blocktype,$description,$isTechBlock = false,$debugData=null)
    {
        $bm = new BlockMessage();
        $bm->company_id = $companyid;
        $bm->company_order_id = $company_order_id;
        $bm->block_type = $blocktype;
        $bm->description = $description;
        $bm->release_status = 0;
        $bm->tech_block = $isTechBlock ? 1 : 0;

        if(self::isReplacementCard($bm->company_id)) {
            $bm->tech_block = 1;
            $bm->description = "REPLACEMENT COMPANY: ";
        }

        if($debugData != null) {
            if(is_object($debugData)) {
                $bm->debug_data = json_encode($debugData);

            }
            else if(is_array($debugData)) {
                if(count($debugData) > 0) {
                    $bm->debug_data = implode("\r\n",$debugData);
                }
            }
            else if(trimgf($debugData) != "") {
                $bm->debug_data = $debugData;
            }
        }

        $bm->save();

        self::sendApprovalEmail($bm);

    }

    private static function findApprovalEmailAddress($blockMessage,$languageCode) {

        if($blockMessage->tech_block == 1) {
            return "sc@interactive.dk";
        }
        else {
            if($languageCode > 0) {
                if($languageCode == 1) {
                    return "sc@interactive.dk";
                }
                else if($languageCode == 4) {
                    return "sc@interactive.dk";
                }
                else if($languageCode == 5) {
                    return "sc@interactive.dk";
                }
                else {
                    return "sc@interactive.dk";
                }
            }
        }

        return "soren@interactive.dk";

    }

    private static function sendApprovalEmail($blockMessage) {


        // Find company
        $company = null;
        if($blockMessage->company_id > 0) {
            $company = Company::find($blockMessage->company_id);
        }

        // Find order
        $companyOrder = null;
        if($blockMessage->company_order_id > 0) {
            $companyOrder = CompanyOrder::find($blockMessage->company_order_id);
        }
/*
        $mailqueue = new MailQueue();
        $mailqueue->sender_name = "Gavefabrikken - Approval";
        $mailqueue->sender_email = "Gavefabrikken@gavefabrikken.dk";
        $mailqueue->recipent_email = self::findApprovalEmailAddress($blockMessage,($company == null ? 0 : $company->language_code));
        $mailqueue->mailserver_id = 4;
        $mailqueue->subject = "New approval waiting - ".$blockMessage->getBlockTypeText();
        $mailqueue->send_group = "APPROVAL";

        $mailqueue->body = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"><style></style></head><body>';
        $mailqueue->body .= "New approval waiting for you #".$blockMessage->id."<br><br>";
        $mailqueue->body .= "Type: ".$blockMessage->getBlockTypeText()."<br>";

        if($company != null) {
            $mailqueue->body .= "<br>Company name: ".$company->name."<br>";
        }

        if($companyOrder != null) {
            $mailqueue->body .= "<br>Order BS no: ".$companyOrder->order_no."<br>";
        }

        $mailqueue->body .= "Approval code: ".$blockMessage->block_type."<br>";
        $mailqueue->body .= "Code description: ".BlockMessage::getBlockTypeDescription($blockMessage->block_type)."<br>";
        $mailqueue->body .= "Note: ".$blockMessage->description."<br>";

        if($blockMessage->tech_block == 1) {
            $mailqueue->body .= "<br><b>IS TECH BLOCK</b><br>";
        }

        if(in_array($mailqueue->recipent_email,array("sc@interactive.dk","soren@interactive.dk","us@gavefabrikken.dk"))) {
            $mailqueue->body .= "<br>Debug data:<br>".nl2br($blockMessage->debug_data)."<br>";
        }

        $mailqueue->body .= "</body></html>";
        $mailqueue->save();
*/
    }

}

?>