<?php

class afterSalesEmailController Extends baseController
{
    /* =========================
     * Small, local helpers (readability)
     * ========================= */

    // Simple input sanitizer for numeric IDs to mitigate SQL injection in concatenated SQL
    private function sanitizeId($value) {
        if (is_numeric($value)) { return (int)$value; }
        if (is_string($value)) {
            $filtered = filter_var($value, FILTER_VALIDATE_INT);
            if ($filtered !== false) { return (int)$filtered; }
        }
        return null;
    }

    private function formatDateDMY($value) {
        if (!$value) { return ""; }
        if ($value instanceof DateTime) {
            return $value->format("d-m-Y");
        }
        $ts = is_numeric($value) ? (int)$value : strtotime($value);
        return $ts ? date("d-m-Y", $ts) : "";
    }

    private function wrapTable($html, $width = null) {
        $w = $width ? " width={$width}" : "";
        return "<table{$w}>".$html."</table>";
    }

    private function getRecipientEmail($companyOrderId, $fallbackEmail) {
        if ((int)$companyOrderId === 66085) {
            return "kss@fortea.dk";
        }
        return $fallbackEmail;
    }

    private function buildAddressRowsSE($lev) {
        return '
            <tr>
                <td style="width: 140px;" >F&ouml;retag</td>
                <td style="width: 140px;" >'.$lev->ship_to_company.'</td>
            </tr>
            <tr>
                <td style="width: 140px;" >Adress </td>
                <td style="width: 140px;" >'.$lev->ship_to_address.'</td>
            </tr>
            <tr>
                <td style="width: 140px;" >Stad</td>
                <td style="width: 140px;" >'.$lev->ship_to_city.'</td>
            </tr>
            <tr>
                <td style="width: 140px;" >Postnummer</td>
                <td style="width: 140px;" >'.$lev->ship_to_postal_code.'</td>
            </tr>
        ';
    }

    private function buildAddressRowsDKNO($lev) {
        return '
            <tr>
                <td style="width: 140px;" >Virksomhed</td>
                <td style="width: 140px;" >'.$lev->ship_to_company.'</td>
            </tr>
            <tr>
                <td style="width: 140px;" >Adresse</td>
                <td style="width: 140px;" >'.$lev->ship_to_address.'</td>
            </tr>
            <tr>
                <td style="width: 140px;" >By</td>
                <td style="width: 140px;" >'.$lev->ship_to_city.'</td>
            </tr>
            <tr>
                <td style="width: 140px;" >Postnr.</td>
                <td style="width: 140px;" >'.$lev->ship_to_postal_code.'</td>
            </tr>
        ';
    }

    private function buildCredentialsHeaderSE() {
        return '
            <tr>
                <td style="width: 131px;"><b>Anv&auml;ndarnamn</b></td>
                <td style="width: 97px;"><b>L&ouml;senord</b></td>
            </tr>
        ';
    }

    private function buildCredentialsHeaderNO() {
        return '
            <tr>
                <td style="width: 131px;"><b>Kortnummer</b></td>
                <td style="width: 97px;"><b>Adgangskode</b></td>
            </tr>
        ';
    }

    private function buildCredentialsRows($cardsRs, &$lastExpireOut) {
        $rows = "";
        if (sizeofgf($cardsRs) > 0) {
            foreach ($cardsRs as $card) {
                $lastExpireOut = $card->expire_date;
                $rows .= '
                    <tr>
                        <td style="width: 140px;">'.$card->username.'</td>
                        <td style="width: 140px;">'.$card->password.'</td>
                    </tr>';
            }
        }
        return $rows;
    }

    private function buildDownloadRow($url) {
        return '
            <tr>
                <td  colspan="2"><a href="'.$url.'" mc:disable-tracking>Download gavekort klar til print her</a></td>
            </tr>
            <tr><td  colspan="2"><hr></td></tr>
            <tr><td  colspan="2"></td></tr>
        ';
    }

    private function buildDownloadRowSE($url) {
        return '
            <tr>
                <td  colspan="2"><a href="'.$url.'" mc:disable-tracking>H&auml;mta presentkorten f&ouml;r utskrift h&auml;r</a></td>
            </tr>
            <tr><td  colspan="2"><hr></td></tr>
            <tr><td  colspan="2"></td></tr>
        ';
    }

    public function Index() {
        echo "hej";
    }

    /* =========================
     * SE: Web efter-salg (ny)
     * ========================= */
    public function mailAfterSaleWebSverige($companyOrderID = ""){
        $expire = "";
        if($companyOrderID == ""){
            $companyOrderID =  $this->getWebCardSverige( );
        }

        $companyOrderID = $this->sanitizeId($companyOrderID);
        if($companyOrderID === null){
          echo "none";
          return;
        }

        $levRs =  ShopUser::find_by_sql("SELECT company.* FROM company inner JOIN shop_user on company.id = shop_user.company_id WHERE company_order_id = (".$companyOrderID.") and shop_user.blocked = 0 group by company.id order by shop_user.username ");
        $html = "";

        foreach($levRs as $lev){
            $html .= $this->buildAddressRowsSE($lev);

            $cardsRs =  ShopUser::find_by_sql("SELECT username,password,expire_date FROM `shop_user` WHERE `company_order_id` = ".$companyOrderID." and blocked = 0 and company_id = ".$lev->id." order by username");

            $html .= $this->buildCredentialsHeaderSE();
            $html .= $this->buildCredentialsRows($cardsRs, $expire);

            $html .= $this->buildDownloadRowSE('https://system.gavefabrikken.dk/kundepanel/printcards.php?id='.$companyOrderID.'&token='.$lev->token);
            $html .= '
                 <tr>
                    <td  colspan="2"></td>
                </tr>
                       <tr>
                    <td  colspan="2"></td>
                </tr>
            ';
        }

        $CompanyOrderRs = CompanyOrder::find_by_sql("SELECT shop_id, contact_email,company_id from company_order WHERE id = ".$companyOrderID);
        $html = $this->wrapTable($html);

        $mailTemplate = "";

        $deadline = $this->formatDateDMY($expire);
        $token =  $this->getCompanyToken($CompanyOrderRs[0]->company_id);
        $e =  $this->getContactEmail($companyOrderID);
        $recipent = $this->getRecipientEmail($companyOrderID, $e->attributes["contact_email"]);

        if($e->floating_expire_date != null) {
            $deadline = $this->formatDateDMY($e->floating_expire_date);
        }

        $mailTemplate = mailtemplate::find(11);
        if($CompanyOrderRs[0]->shop_id == 8271){
                $mailTemplate = mailtemplate::find(28);
        }

        $pdfLink = "";

        $template = str_replace('{dato}',$deadline,$mailTemplate->template_receipt);
        $template = str_replace('{cards}',$html,$template);
        $template = str_replace('{token}',$token,$template);
        $template = str_replace('{pdf}',$pdfLink,$template);

        $maildata = [];
	    $maildata['sender_email']   = "no-reply@gavefabrikken.dk";
	    $maildata['recipent_email'] = $recipent;
	    $maildata['subject']        = utf8_encode($mailTemplate->subject_receipt);
	    $maildata['body']           = ($template);
	    $maildata['mailserver_id']  = 5;

        if($expire != "" and $mailTemplate != ""){
            MailQueue::createMailQueue($maildata);
            system::connection()->commit();
            Dbsqli::setSql2("update company_order set welcome_mail_is_send = 1 where id = ".$companyOrderID );
        }

        echo json_encode( array("status"=>1) );
    }

    /* =========================
     * SE: gammel (uændret logik)
     * ========================= */
    public function mailAfterSaleWebSverigeOld(){
         //$order = $this->shopsToUpdateWebSale();

         $cards =  $this->getWebCardSverige( );

         $expire = "";
         $mailTemplate = "";
         if(sizeofgf($cards) > 0){

            $cardList= '<tr>
                        <td style="width: 131px;">Anv&auml;ndarnamn</td>
                        <td style="width: 97px;">L&ouml;senord</td>
                    </tr>';
            foreach($cards as $card){
               $expire =  $card->expire_date;

                    $cardList.='
                    <tr>
                        <td style="width: 131px;">'.$card->username.'</td>
                        <td style="width: 97px;">'.$card->password.'</td>
                    </tr>';

            }

        $deadline = date_format($cards[0]->expire_date,"d-m-Y");
      echo  $token = $this->getCompanyToken($cards[0]->company_id);
        $e =  $this->getContactEmail($cards[0]->company_order_id);

         if($e->floating_expire_date != null) {
             $deadline = date_format($e->floating_expire_date,"d-m-Y");
         }

        $recipent = $e->attributes["contact_email"];
        echo $cards[0]->company_order_id;
        $mailTemplate = mailtemplate::find(11);
        /*
        if($expire == "2020-11-07" || $expire == "2021-01-03"){
          $mailTemplate = mailtemplate::find(10);
        } else {
          $mailTemplate = mailtemplate::find(8);
        }
        */

        $pdfLink = '<a href="'.GFConfig::SHOP_URL_SE.'kundepanel/printcards.php?id='.$cards[0]->company_order_id.'&token='.$token.'" mc:disable-tracking>H&auml;mta presentkorten f&ouml;r utskrift h&auml;r</a>';

        $mailTemplate->template_receipt;
        $template = str_replace('{dato}',$deadline,$mailTemplate->template_receipt);
        $template = str_replace('{cards}',$cardList,$template);
        $template = str_replace('{token}',$token,$template);
        $template = str_replace('{pdf}',$pdfLink,$template);

         if($expire != "" and $mailTemplate != ""){
            $maildata = [];
       	    $maildata['sender_email'] =  "no-reply@gavefabrikken.dk";
    	    $maildata['recipent_email'] = $recipent; //"us@gavefabrikken.dk";
    	    $maildata['subject']= utf8_encode($mailTemplate->subject_receipt);
    	    $maildata['body'] = utf8_decode($template);
    	    $maildata['mailserver_id'] = 5;
            MailQueue::createMailQueue($maildata);
            system::connection()->commit();
            Dbsqli::setSql2("update company_order set welcome_mail_is_send = 1 where id = ".$cards[0]->company_order_id );
         } else {
                  $dummy = [];
                  response::success(json_encode($dummy));
            }
         } else {
            $dummy = [];
            response::success(json_encode($dummy));
         }
    }

    /* =========================
     * NO: gammel (uændret logik)
     * ========================= */
    public function mailAfterSaleWebNorgeOLD(){
         //$order = $this->shopsToUpdateWebSale();

         $cards =  $this->getWebCardNorge( );
        // print_r($cards);

         $expire = "";
         $mailTemplate = "";
         if(sizeofgf($cards) > 0){

            $cardList= '<tr>
                        <td style="width: 131px;">Kortnummer</td>
                        <td style="width: 97px;">Adgangskode</td>
                    </tr>';
            foreach($cards as $card){
               $expire =  $card->expire_date;

                    $cardList.='
                    <tr>
                        <td style="width: 131px;">'.$card->username.'</td>
                        <td style="width: 97px;">'.$card->password.'</td>
                    </tr>';

            }

        $deadline = date_format($cards[0]->expire_date,"d-m-Y");
        $token = $this->getCompanyToken($cards[0]->company_id);
        $e =  $this->getContactEmail($cards[0]->company_order_id);

         if($e->floating_expire_date != null) {
             $deadline = date_format($e->floating_expire_date,"d-m-Y");
         }

        $recipent = $e->attributes["contact_email"];
        echo $cards[0]->company_order_id;

        if($expire == "2020-11-07" || $expire == "2021-01-03"){
          $mailTemplate = mailtemplate::find(10);
        } else {
          $mailTemplate = mailtemplate::find(8);
        }
        $pdfLink = '<a href="https://system.gavefabrikken.dk/kundepanel/printcards.php?id='.$cards[0]->company_order_id.'&token='.$token.'" mc:disable-tracking>Download gavekort klar til print her</a>';

        $mailTemplate->template_receipt;
        $template = str_replace('{dato}',$deadline,$mailTemplate->template_receipt);
        $template = str_replace('{cards}',$cardList,$template);
        $template = str_replace('{token}',$token,$template);
        $template = str_replace('{pdf}',$pdfLink,$template);

         $maildata = [];
	     $maildata['sender_email'] =  "no-reply@gavefabrikken.dk";
	     $maildata['recipent_email'] = $recipent; //"us@gavefabrikken.dk";
	     $maildata['subject']= utf8_encode($mailTemplate->subject_receipt);
	     $maildata['body'] = utf8_decode($template);
	     $maildata['mailserver_id'] = 4;

         if($expire != "" and $mailTemplate != ""){
            MailQueue::createMailQueue($maildata);
            system::connection()->commit();
            Dbsqli::setSql2("update company_order set welcome_mail_is_send = 1 where id = ".$cards[0]->company_order_id );
            // norge backup if reciver dont get mail

            $maildata = [];
       	    $maildata['sender_email'] =  "no-reply@gavefabrikken.dk";
    	    $maildata['recipent_email'] = "ordrebekreftelse@gavefabrikken.no";
    	    $maildata['subject']= utf8_encode($mailTemplate->subject_receipt);
    	    $maildata['body'] = utf8_decode($template);
    	    $maildata['mailserver_id'] = 4;
            MailQueue::createMailQueue($maildata);
            system::connection()->commit();

         } else {
                  $dummy = [];
                  response::success(json_encode($dummy));
            }
         } else {
            $dummy = [];
            response::success(json_encode($dummy));
         }
    }

    /* =========================
     * NO: Web etter-salg (ny)
     * ========================= */
    public function mailAfterSaleWebNorge($companyOrderID = ""){

        $expire = "";
        $mailserver_id = 4;
        if($companyOrderID == ""){
            $mailserver_id = 4;
            $companyOrderID =  $this->getWebCardNorge( );
        }

        $companyOrderID = $this->sanitizeId($companyOrderID);
        if($companyOrderID === null){
          echo "none";
          return;
        }

        $levRs =  ShopUser::find_by_sql("SELECT company.* FROM company inner JOIN shop_user on company.id = shop_user.company_id WHERE company_order_id = (".$companyOrderID.") and shop_user.blocked = 0 group by company.id order by shop_user.username ");

        $html = "";
        foreach($levRs as $lev){
            $html .= $this->buildAddressRowsDKNO($lev);

            $cardsRs =  ShopUser::find_by_sql("SELECT username,password,expire_date FROM `shop_user` WHERE `company_order_id` = ".$companyOrderID." and blocked = 0 and company_id = ".$lev->id." order by username");
            $html .= $this->buildCredentialsHeaderNO();
            $html .= $this->buildCredentialsRows($cardsRs, $expire);

            $html .= $this->buildDownloadRow(GFConfig::SHOP_URL_NO.'kundepanel/printcards.php?id='.$companyOrderID.'&token='.$lev->token);
        }

        $CompanyOrderRs = CompanyOrder::find_by_sql("SELECT contact_email,company_id from company_order WHERE id = ".$companyOrderID);
        // original bug: not assigned. Fixed:
        $html = $this->wrapTable($html);

        $mailTemplate = "";

        $deadline = $this->formatDateDMY($expire);
        $token =  $this->getCompanyToken($CompanyOrderRs[0]->company_id);
        $e =  $this->getContactEmail($companyOrderID);
        $recipent = $this->getRecipientEmail($companyOrderID, $e->attributes["contact_email"]);

        if($e->floating_expire_date != null) {
            $deadline = $this->formatDateDMY($e->floating_expire_date);
        }

        if($deadline == "03-01-2025" || $deadline == "11-11-2024"){
          $mailTemplate = mailtemplate::find(10);
        } else {
          $mailTemplate = mailtemplate::find(8);
        }

        $pdfLink = "";

        $template = str_replace('{dato}',$deadline,$mailTemplate->template_receipt);
        $template = str_replace('{cards}',$html,$template);
        $template = str_replace('{token}',$token,$template);
        $template = str_replace('{pdf}',$pdfLink,$template);

        $maildata = [];
	    $maildata['sender_email']   = "no-reply@gavefabrikken.dk";
	    $maildata['recipent_email'] =  $recipent;
	    $maildata['subject']        = utf8_encode($mailTemplate->subject_receipt);
	    $maildata['body']           = ($template);
	    $maildata['mailserver_id']  = $mailserver_id;

        if($expire != "" and $mailTemplate != ""){
            MailQueue::createMailQueue($maildata);
            system::connection()->commit();
            Dbsqli::setSql2("update company_order set welcome_mail_is_send = 1 where id = ".$companyOrderID );
        }

        echo json_encode( array("status"=>1) );
    }

    /* =========================
     * CS handler (uændret logik)
     * ========================= */
    public function csMailAfterSaleWeb(){
        $id   = isset($_POST["id"]) ? $this->sanitizeId($_POST["id"]) : null;
        $lang = isset($_POST["lang"]) ? $this->sanitizeId($_POST["lang"]) : null;

        if($id === null || $lang === null){
            echo "none";
            return;
        }

        switch ($lang) {
            case 1:  $this->mailAfterSaleWeb($id); break;
            case 4:  $this->mailAfterSaleWebNorge($id); break;
            case 5:  $this->mailAfterSaleWebSverige($id); break;
            default: echo "none"; return;
        }
    }

    /* =========================
     * DK: Web efter-salg (ny)
     * ========================= */
    public function mailAfterSaleWeb($companyOrderID = ""){

        //    echo "MIDLERTIDIGT STOPPET!";
        //    return;

        $expire = "";
        $mailserver_id = 4;
        if($companyOrderID == ""){
            $mailserver_id = 4;
            $companyOrderID =  $this->getWebCard( );
        }

        $companyOrderID = $this->sanitizeId($companyOrderID);
        if($companyOrderID === null){
          echo "none";
          return;
        }

        $levRs =  ShopUser::find_by_sql("SELECT company.* FROM company inner JOIN shop_user on company.id = shop_user.company_id WHERE company_order_id = (".$companyOrderID.") and shop_user.blocked = 0 group by company.id order by shop_user.username ");
        $html = "";

        foreach($levRs as $lev){
            $html.='
                <tr>
                    <td style="width: 150px;" >Virksomhed</td>
                    <td style="width: 250px;" >'.$lev->ship_to_company.'</td>
                </tr>
                <tr>
                    <td style="width: 150px;" >Adresse</td>
                    <td style="width: 250px;" >'.$lev->ship_to_address.'</td>
                </tr>
                <tr>
                    <td style="width: 150px;" >By</td>
                    <td style="width: 250px;" >'.$lev->ship_to_city.'</td>
                </tr>
                <tr>
                    <td style="width: 150px;" >Postnr.</td>
                    <td style="width: 250px;" >'.$lev->ship_to_postal_code.'</td>
                </tr><br>
            ';

            $cardsRs =  ShopUser::find_by_sql("SELECT username,password,expire_date FROM `shop_user` WHERE `company_order_id` = ".$companyOrderID." and blocked = 0 and company_id = ".$lev->id." order by username");
            if(sizeofgf($cardsRs) > 0){
                foreach($cardsRs as $card){
                        $expire = $card->expire_date;
                }
            }

            $html.='
                <tr>
                    <td  colspan="2"><a href="https://findgaven.dk/gavevalg/api.php?rt=kundepanel/printcards.php&id='.$companyOrderID.'&token='.$lev->token.'" mc:disable-tracking>Download gavekort klar til print her</a></td>
                </tr>
                <tr>
                
                   <i><td  colspan="2"><i><a href="https://findgaven.dk/gavevalg/api.php?rt=kundepanel/printcards.php&id='.$companyOrderID.'&token='.$lev->token.'" mc:disable-tracking>Download gift certificates ready to print here</a></i></td></i>
                </tr>
                              
                 <tr>
                    <td  colspan="2"><hr></td>
                </tr>
                 <tr>
                    <td  colspan="2"></td>
                </tr>
                       <tr>
                    <td  colspan="2"></td>
                </tr>
            ';
        }

        $CompanyOrderRs = CompanyOrder::find_by_sql("SELECT shop_id,contact_email,company_id from company_order WHERE id = ".$companyOrderID);
        $html = $this->wrapTable($html, 400);

        $mailTemplate = "";

        $deadline = $this->formatDateDMY($expire);
        $token =  $this->getCompanyToken($CompanyOrderRs[0]->company_id);
        $e =  $this->getContactEmail($companyOrderID);
        if($e->floating_expire_date != null) {
            $deadline = $this->formatDateDMY($e->floating_expire_date);
        }

        $recipent = $this->getRecipientEmail($companyOrderID, $e->attributes["contact_email"]);
        $cardType = "";
        $shop_id = $CompanyOrderRs[0]->shop_id;
        switch ($shop_id) {
            case "52":
                $cardType = "JULEGAVEKORTET-560";
            break;
            case "53":
                $cardType = "GULDGAVEKORTET-800";
            break;
            case "54":
                $cardType = "24 GAVER-400";
            break;
            case "55":
                $cardType = "24 GAVER-560";
            break;
            case "56":
                $cardType = "24 GAVER-640";
            break;
            case "290":
                $cardType = "DRØMMEGAVEKORTET-200";
            break;
            case "310":
                $cardType = "DRØMMEGAVEKORTET-300";
            break;
            case "575":
                $cardType = "DESIGNJULEGAVEN";
            break;
            case "2548":
                $cardType = "DET GRØNNE GAVEKORT";
            break;
            case "2395":
                $cardType = "GULDGAVEKORTET-1040";
            break;
            case "2960":
                $cardType = "LUKSUSGAVEKORTET-400";
            break;
            case "2961":
                $cardType = "LUKSUSGAVEKORTET-200";
            break;
            case "2962":
                $cardType = "LUKSUSGAVEKORTET-640";
            break;
            case "2963":
                $cardType = "LUKSUSGAVEKORTET-800";
            break;
            case "4668":
                $cardType = "JULEGAVEKORTET-720";
            break;
            case "6989":
                $cardType = "Julegavevalget-400";
                break;
            case "7121":
                $cardType = "Julegavevalget";
                break;
            case "7122":
                $cardType = "Julegavevalget-800";
                break;
            case "4662":
                $cardType = "DKDESIGN-960";
            break;
        }
        $cardType = utf8_decode($cardType);

        if($shop_id == "2960" || $shop_id == "2961" || $shop_id == "2962" || $shop_id == "2963" || $shop_id == "2999"){
            $mailTemplate = mailtemplate::find(18);
        } else {
            if($deadline == "01-04-2025" ){
                $mailTemplate = mailtemplate::find(5);
            } else {
              $mailTemplate = mailtemplate::find(4);
            }
        }

        $pdfLink = "";

        $mailTemplate->template_receipt;
        $template = str_replace('{dato}',$deadline,$mailTemplate->template_receipt);
        $template = str_replace('{cards}',$html,$template);
        $template = str_replace('{token}',$token,$template);
        $template = str_replace('{pdf}',$pdfLink,$template);

        $maildata = [];
	    $maildata['sender_email']   =  "no-reply@gavefabrikken.dk";
	    $maildata['recipent_email'] = $recipent;
	    $maildata['subject']        = ($mailTemplate->subject_receipt)." - ".utf8_encode($cardType);
	    $maildata['body']           = ($template);
	    $maildata['mailserver_id']  = $mailserver_id;

        if($expire != "" and $mailTemplate != ""){
            MailQueue::createMailQueue($maildata);
            system::connection()->commit();
            Dbsqli::setSql2("update company_order set welcome_mail_is_send = 1 where id = ".$companyOrderID );
        }

        echo json_encode( array("status"=>1) );
    }

    public function mailAfterSale(){
        $this->sendWelcomeMailPhysicsCard();
    }

    private function getContactEmail($orderId){
        return Companyorder::find($orderId);
    }



    private function getWebCardNorge(){
        $rs = Companyorder::find_by_sql("
        select id from company_order WHERE
            welcome_mail_is_send = 0 and
            send_welcome_mail = 1 and
            order_state in (4,5) and
            is_cancelled = 0 and
             is_email = 1  and
            created_datetime < NOW() - INTERVAL 2 HOUR  and
            shop_id IN (SELECT shop_id FROM `cardshop_settings` WHERE `language_code` = 4 && send_certificates = 1) limit 1");
        if(sizeofgf($rs) == 0){
            return false;
        } else {
            $ordernr = $rs[0]->attributes["id"];
            $shopUser =  ShopUser::find_by_sql("SELECT *  FROM `shop_user` WHERE `company_order_id` = (".$ordernr.") and blocked = 0");

            if(sizeofgf($shopUser) > 0){
                return $ordernr;
            } else {
                Dbsqli::setSql2("update company_order set welcome_mail_is_send = 1 where id = ".$ordernr );  ;
            }
        }
    }

    private function getWebCardSverige(){
        $rs = Companyorder::find_by_sql("
        select id from company_order WHERE
            welcome_mail_is_send = 0 and
            send_welcome_mail = 1 and
             is_cancelled = 0 and
            order_state in (4,5) and
             is_email = 1  and
            created_datetime < NOW() - INTERVAL 2 HOUR  and
            shop_id IN (SELECT shop_id FROM `cardshop_settings` WHERE `language_code` = 5 && send_certificates = 1 ) limit 1");
        if(sizeofgf($rs) == 0){
            return false;
        } else {
            $ordernr = $rs[0]->attributes["id"];
            $shopUser =  ShopUser::find_by_sql("SELECT *  FROM `shop_user` WHERE `company_order_id` = (".$ordernr.") and blocked = 0");

            if(sizeofgf($shopUser) > 0){
                return $ordernr;
            } else {
                Dbsqli::setSql2("update company_order set welcome_mail_is_send = 1 where id = ".$ordernr );  ;
            }
        }
    }

    private function getWebCard(){
        $rs = Companyorder::find_by_sql("
        select id from company_order WHERE
            welcome_mail_is_send = 0 and
            send_welcome_mail = 1 and
            order_state in (4,5) and
            is_cancelled = 0 and
             is_email = 1  and
            created_datetime < NOW() - INTERVAL 2 HOUR and shop_id IN (SELECT shop_id FROM `cardshop_settings` WHERE `language_code` = 1 && send_certificates = 1)  limit 1");
        if(sizeofgf($rs) == 0){
            return false;
        } else {
            $ordernr = $rs[0]->attributes["id"];
            $shopUser =  ShopUser::find_by_sql("SELECT *  FROM `shop_user` WHERE `company_order_id` = (".$ordernr.") and blocked = 0");

            if(sizeofgf($shopUser) > 0){
                return $ordernr;
            } else {
                Dbsqli::setSql2("update company_order set welcome_mail_is_send = 1 where id = ".$ordernr );
            }
        }
    }



    /* Send velkomst mail til kort køb, kun fysiske */
    private function sendWelcomeMailPhysicsCard(){
        $listOfNewOrders = Companyorder::all(array('conditions' => array(
            "welcome_mail_is_send = 0 and
             send_welcome_mail = 1  and
             is_printed = 0  and is_cancelled = 0 and
              shop_id = 52  and
              `created_datetime` > '2022-09-15 07:16:13' and
             created_datetime < NOW() - INTERVAL 2 HOUR limit 1"
            )));

        if(sizeofgf($listOfNewOrders) > 0){
            foreach($listOfNewOrders as $order){
                if($this->hasReceivedWelcomeMail($order->company_id)) continue; // tjekker om
                if($this->hasPhysicalCard($order->company_id)) continue; // tjekker om

                $token = $this->getCompanyToken($order->company_id);
                $mailTemplate = mailtemplate::find(6);
                $mailTemplate->template_receipt;
                $template = str_replace('{link}',$token,$mailTemplate->template_receipt);
                $maildata = [];
                $maildata['sender_email'] =  "no-reply@gavefabrikken.dk";
                $maildata['recipent_email'] =  $order->contact_email; // "us@gavefabrikken.dk";
                $maildata['subject']= utf8_encode($mailTemplate->subject_receipt);
                $maildata['body'] = utf8_decode($template);
                $maildata['mailserver_id'] = 4;
                Dbsqli::setSql2("update company_order set welcome_mail_is_send = 1 where company_id = ".$order->company_id );

                MailQueue::createMailQueue($maildata);
                system::connection()->commit();
            }
        }
        $dummy = [];
        response::success(json_encode($dummy));
    }

    /* send koder til kort køb, email koder  */
    private function hasReceivedWelcomeMail($cvr){
        return sizeofgf(Companyorder::find('all', array('conditions' => array('company_id = ? and welcome_mail_is_send = 1 ',$cvr )))) > 0 ? true:false;
    }

    private function getCompanyToken($companyId){
        $company = Company::Find($companyId);
        return $company->attributes["token"];
    }

    private function hasPhysicalCard($company_id)
    {
        $listOfNewOrders = Companyorder::all(array('conditions' => array(
            "is_printed = 1  and is_cancelled = 0  and  company_id = ".$company_id
        )));
        return sizeofgf($listOfNewOrders) > 0 ? true:false;

    }



    /**
     * LOAD TEST FUNCTION
     */
    public function benchpress() {

        // Readsimple
        echo "Readsimple:\r\n";

        try {

            // Setup post
            $_POST = array("link" => "julegavekortet");

            // Call function
            include "shopController.php";
            $readSimpleController = new ShopController(new Registry());
            $readSimpleController->readSimple();

        } catch (Exception $e) {
            echo "EXCEPTION: ".$e->getMessage();
        }

        echo "\r\n\r\n";

        // Perform login
        echo "Login:\r\n";

        system::connection()->transaction();

        try {

            // Setup post
            $_POST = array("username" => "demo1234","password" => "demo1234","shop_id" => 52,"logintype" => "shop");

            // Call function
            include "loginController.php";
            $loginController = new LoginController(new Registry());
            $loginController->loginShopUser();

        } catch (Exception $e) {
            echo "EXCEPTION: ".$e->getMessage();
        }

        echo "\r\n\r\n";

        // Read shop full
        echo "Read shop full:\r\n";

        system::connection()->transaction();

        try {

            // Load demo shopuser in julegacekort
            $shopUser = ShopUser::find_by_sql("SELECT * FROM `shop_user` WHERE shop_id = 52 && is_demo = 1");

            // Setup post
            $_POST = array("id" => $shopUser[0]->shop_id,"token" => $shopUser[0]->token);

            // Call function
            $readFullController = new ShopController(new Registry());
            $readFullController->readFull_v2();

        } catch (Exception $e) {
            echo "EXCEPTION: ".$e->getMessage();
        }

        echo "\r\n\r\n";

        echo "\r\n";
    }
}
