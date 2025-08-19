<?php


class orderConfirmationController Extends baseController {

    public function Index() {

    }

    public function testreceipt()
    {
        $this->sendReceiptMail(27708);
        
        
          system::connection()->commit();
    }

    public function runOrdersSE()
    {

        if(!isset($_GET["token"]) || $_GET["token"] != "g54s654feQxaWq") {
            echo "Invalid access"; return;
        }

        $sql = "SELECT *  FROM `company_order` WHERE `shop_id` IN (1832,1981,4793,5117,8271) && is_cancelled = 0 && is_invoiced = 0 ORDER BY id ASC";
        $companyOrderList = CompanyOrder::find_by_sql($sql);

        foreach($companyOrderList as $companyOrder) {

            try {


                echo "<br>Processing order: ".$companyOrder->id." - ";
                $this->sendReceiptMail($companyOrder->id,true);

                $updateOrder = CompanyOrder::find($companyOrder->id);
                if($updateOrder->id > 0) {
                    $updateOrder->is_invoiced = 1;
                    $updateOrder->save();
                    echo "ok / updated<br>";
                }
                else {
                    echo "ok / not updated<br>";
                }

                system::connection()->commit();

                return;
            }
            catch (Exception $e) {
                echo "<br>COULD NOT PROCESS COMPANY ORDER: ".$e->getMessage()."<br>";
                $this->mailProblem("Could not process company order: ".$companyOrder->id." (".$companyOrder->order_no.") - ".$e->getMessage());
            }



        }


    }

    private function mailProblem($content)
    {
        $message = "Orderconfirmation problem<br><br>".$content."";
        $headers = "From: noreply@julegavekortet.dk <noreply@julegavekortet.dk>" . "\r\n";
        $headers .= "Reply-To:  <noreply@julegavekortet.dk>\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8";
        $result = mailgf("sc@interactive.dk", "Problem with order confirmation mail", $message, $headers);
    }

    private function sendReceiptMail($companyOrderid,$checkIsInvoiced = false)
    {

        $companyOrder = CompanyOrder::find($companyOrderid);
        $company =   Company::find($companyOrder->company_id);
        $shop    =   Shop::find($companyOrder->shop_id);
        $expireDate = expireDate::getByExpireDate($companyOrder->expire_date);
        $shop = Shop::find($companyOrder->shop_id );

        if($companyOrder == null || !($companyOrder->id > 0)) {
            throw new Exception("Could not find company order");
        }
        else if($companyOrder->is_invoiced > 0 && $checkIsInvoiced == true) {
            throw new Exception("Order is already invoiced");
        }

        /*
        // Lav de som ops�tning
        if($companyOrder->shop_id==54 || $companyOrder->shop_id==55 || $companyOrder->shop_id==56) {
            $mailtempate = MailTemplate::getTemplate($companyOrder->shop_id,1);
            $shopitemname = "24gaver";
            $shopitemno   = "24GAVER";
            $mailto = 'info@24gaver.dk';
            $title =  'Ordre bekr�ftigelse - 24gaver';
            $title2 = '24GAVER';
            $color = '#009900';
            $mailserverid = 4;
        }
        else if($companyOrder->shop_id==52) {
            $mailtempate = MailTemplate::getTemplate($companyOrder->shop_id,1);
            $shopitemname = "Julegavekortet";
            $shopitemno   = "JGK";
            $mailto = 'info@julegavekortet.dk';
            $title =  'Ordre bekr�ftigelse - Julegavekortet';
            $title2 = 'JULEGAVEKORTET.DK';
            $color = '#cc0052';
            $mailserverid = 4;
        }
        else if($companyOrder->shop_id==53) {
            $mailtempate = MailTemplate::getTemplate($companyOrder->shop_id,1);
            $shopitemname = "GULD";
            $shopitemno   = "GULD";
            $mailto = 'info@guldgavekortet.dk';
            $title =  'Ordre bekr�ftigelse - Guldgavekortet';
            $title2 = 'GULDGAVEKORTET.DK';
            $color = 'black';
            $mailserverid = 4;
        }
        else if($companyOrder->shop_id==57 || $companyOrder->shop_id==58 || $companyOrder->shop_id==59 || $companyOrder->shop_id==272 || $companyOrder->shop_id==574) {
            $mailtempate = MailTemplate::getTemplate($companyOrder->shop_id,4);
            $shopitemname = "JGK";
            $shopitemno   = "JGK-NO";
            $mailto = 'info@gavefabrikken.no';
            $title =  'Ordre bekr�ftigelse - Julegavekortet';
            $title2 = 'Julegavekortet.no';
            $color = '#cc0052';
            $mailserverid = 4;

        }
        else if($companyOrder->shop_id==251) {
            $mailtempate = MailTemplate::getTemplate($companyOrder->shop_id,1);
            $shopitemname = "P�skegavekortet";
            $shopitemno   = "PGG";
            $mailto = 'info@paaskegavekortet.dk';
            $title =  'Ordre bekr�ftigelse - P�skegavekortet';
            $title2 = 'P�SKEGAVEKORTET.DK';
            $color = 'black';
            $mailserverid = 4;
        }
        else if($companyOrder->shop_id==575) {
            $mailtempate = MailTemplate::getTemplate($companyOrder->shop_id,1);
            $shopitemname = "Designjulegaven ";
            $shopitemno   = "DESIGN";

            $mailto = 'info@julegavetypen.dk';
            $title =  'Ordre bekr�ftigelse - Julegavetypen';
            $title2 = 'JULEGAVETYPEN.DK';
            $color = 'black';
            $mailserverid = 4;
        }
        else if($companyOrder->shop_id==247 || $companyOrder->shop_id==248 || $companyOrder->shop_id==287 || $companyOrder->shop_id==290 || $companyOrder->shop_id==310) {
            $mailtempate = MailTemplate::getTemplate($companyOrder->shop_id,1);
            $shopitemname = "DGK";
            $shopitemno   = "DGK";
            $mailto = 'info@drommegavekortet.dk';
            $title =  'Ordre bekr�ftigelse - Dr�mmegavekortet';
            $title2 = 'Dr�mmegavekortet.dk';
            $color = '#60aaa9';
            $mailserverid = 4;
        }
        */
        if($companyOrder->shop_id==1832 || $companyOrder->shop_id==1981 || $companyOrder->shop_id==4793 || $companyOrder->shop_id==5117) {
            $mailtempate = MailTemplate::getTemplate($companyOrder->shop_id,5);
            $shopitemname = "24julklappar";
            $shopitemno   = "24julklappar";
            $mailto = 'info@24julklappar.se';
            $title =  'Ordre bekr?ftigelse - 24julklappar';
            $title2 = '24julklappar';
            $color = '#009900';
            $mailserverid = 5;
            $receiver = "order@presentbolaget.se";
            //$receiver = "sc@interactive.dk";
        }
        else if($companyOrder->shop_id==8271) {
            $mailtempate = MailTemplate::getTemplate($companyOrder->shop_id,5);
            $shopitemname = "Sommarpresent";
            $shopitemno   = "Sommarpresent";
            $mailto = 'info@presentbolaget.se';
            $title =  'Ordre bekr?ftigelse - Sommarpresent';
            $title2 = 'Sommarpresent';
            $color = '#009900';
            $mailserverid = 5;
            $receiver = "order@presentbolaget.se";
            //$receiver = "sc@interactive.dk";
        }
        else {
            throw new Exception("Unknown shop");
        }

        // Get template html
        $html  = $mailtempate->template_order_confirmation;

        $certificateValue = $companyOrder->certificate_value;
        if($certificateValue == null || trimgf($certificateValue) == "") {
            $certificateValue = $shop->card_value;
        }

        // Our Information
        $html = str_replace('{COMPANY}',utf8_decode($companyOrder->company_name),$html);
        $html = str_replace('{MAIL_TO}',$mailto,$html);
        $html = str_replace('{TITLE}',$title,$html);
        $html = str_replace('{TITLE2}',$title2,$html);
        $html = str_replace('{COLOR}',$color,$html);

        include_once("model/receiptCardShop.class.php");
        $itemNumber =  receiptCardShop::getItemNumber($expireDate->toString(),$shopitemno,$certificateValue);
        $productName = receiptCardShop::getProductName($expireDate->toString(),$shopitemname,$shopitemno,$certificateValue);


        $itemNumber = "24julklappar-2020-".$shop->card_value;
        $productName = "24julklappar - ".$shop->card_value.($expireDate->is_delivery == 1 ? "" :" - vecka ".$expireDate->week_no);
        
                /*
        if($expireDate->is_delivery == 1) {
               $productName .= " - hemleverans";
        }         */

        // Terms
        $html = str_replace('{ORDER_TERMS}',($expireDate->is_delivery == 1 ? "" : "Villkor: Frakt efterfaktureras."),$html);

        // Replace texts
        $itemname =  $expireDate->item_name_format;
        $itemname = str_replace('{SHOP_ITEM_NAME}',$shopitemname,$itemname);
        $itemname = str_replace('{SHOP_ITEM_NO}',$shopitemno,$itemname);
        $itemname = str_replace('{WEEK_NO}',$expireDate->week_no,$itemname);
        $itemname = str_replace('{VALUE}',$certificateValue,$itemname);

        $itemno =  $expireDate->item_no_format;
        $itemno = str_replace('{SHOP_ITEM_NAME}',$shopitemname,$itemno);
        $itemno = str_replace('{SHOP_ITEM_NO}',$shopitemno,$itemno);
        $itemno = str_replace('{WEEK_NO}',$expireDate->week_no,$itemno);
        $itemno = str_replace('{VALUE}',$certificateValue,$itemno);
        $html = str_replace('{CVR}',utf8_decode($companyOrder->cvr),$html);

        // Company Information
        if($companyOrder->shop_id==57 || $companyOrder->shop_id==58 || $companyOrder->shop_id==59 || $companyOrder->shop_id==272 || $companyOrder->shop_id==1832 || $companyOrder->shop_id==4793 | $companyOrder->shop_id==5117 | $companyOrder->shop_id==1981) {
            if($companyOrder->ship_to_company == "" || $companyOrder->ship_to_company == null){
                $html = str_replace('{COMPANY_LEV_NAME}',utf8_decode($companyOrder->company_name),$html);
            } else {
                $html = str_replace('{COMPANY_LEV_NAME}',utf8_decode($companyOrder->ship_to_company),$html);
            }
        } else {
            $html = str_replace('{COMPANY_LEV_NAME}',utf8_decode($companyOrder->company_name),$html);
        }
        $html = str_replace('{COMPANY_NAME}',utf8_decode($companyOrder->company_name),$html);

        $html = str_replace('{CONTACT_NAME}',utf8_decode($companyOrder->contact_name),$html);
        $html = str_replace('{CONTACT_EMAIL}',utf8_decode($companyOrder->contact_email),$html);
        $html = str_replace('{CONTACT_PHONE}',utf8_decode($companyOrder->contact_phone),$html);
        $html = str_replace('{CVR}',utf8_decode($companyOrder->cvr),$html);

        // Order Information
        $html = str_replace('{EXPIRE_DATE}',$expireDate->display_date,$html);

        $shipmentdate ='';
        if($shop->shipment_date) {
            $shipmentdate = $shop->shipment_date->format('d-m-Y');
        }

        $html = str_replace('{SHIPMENT_DATE}',$shipmentdate,$html); //17-04-2017
        $html = str_replace('{ORDER_DATE}',date('d-m-Y') ,$html);

        // Item line
        $Total =  $certificateValue * $companyOrder->quantity;
        $html = str_replace('{ITEM_NO}',$itemNumber,$html);
        $html = str_replace('{ITEM_NAME}',$productName,$html);
        $html = str_replace('{QUANTITY}',utf8_decode($companyOrder->quantity),$html);
        $html = str_replace('{VALUE}',utf8_decode(number_format($certificateValue,2,",",".")),$html);
        $html = str_replace('{CARD_TOTAL_AMOUNT}',number_format($Total,2,",","."),$html);

        // Wrap line
        $wrapTotal = 0;
        if($companyOrder->giftwrap==1) {

            $wrapPrice = 25;
            $wrapTotal = $wrapPrice*$companyOrder->quantity;

            $html = str_replace("{WRAP_ITEM_DISPLAY}","",$html);
            $html = str_replace("{WRAP_ITEM_NO}","GKPAK",$html);
            $html = str_replace("{WRAP_ITEM_NAME}","Presentbolagets presentf&#xF6;rpackning",$html);
            $html = str_replace("{WRAP_VALUE}",number_format($wrapPrice,2,",","."),$html);
            $html = str_replace("{WRAP_TOTAL_AMOUNT}",number_format($wrapTotal,2,",","."),$html);
            $html = str_replace("{WRAP_QUANTITY}",$companyOrder->quantity,$html);
            
        } else {
            $html = str_replace("{WRAP_ITEM_DISPLAY}","display: none;",$html);
            $html = str_replace("{WRAP_ITEM_NO}","",$html);
            $html = str_replace("{WRAP_QUANTITY}","",$html);
            $html = str_replace("{WRAP_ITEM_NAME}","",$html);
            $html = str_replace("{WRAP_VALUE}","",$html);
            $html = str_replace("{WRAP_TOTAL_AMOUNT}","",$html);
        }
        
        // Delivery fee for delivery cards
        $deliveryTotal = 0;
        if($expireDate->is_delivery == 1) {

            $deliveryPrice = 79;
            $deliveryTotal = $deliveryPrice*$companyOrder->quantity;

            $html = str_replace("{DELIVERY_ITEM_DISPLAY}","",$html);
            $html = str_replace("{DELIVERY_ITEM_NO}","GEMHEM",$html);
            $html = str_replace("{DELIVERY_ITEM_NAME}","Frakt hemleverans",$html);
            $html = str_replace("{DELIVERY_VALUE}",number_format($deliveryPrice,2,",","."),$html);
            $html = str_replace("{DELIVERY_QUANTITY}",$companyOrder->quantity,$html);
            $html = str_replace("{DELIVERY_TOTAL_AMOUNT}",number_format($deliveryTotal,2,",","."),$html);
        } else {
            $html = str_replace("{DELIVERY_ITEM_DISPLAY}","display: none;",$html);
            $html = str_replace("{DELIVERY_ITEM_NO}","",$html);
            $html = str_replace("{DELIVERY_ITEM_NAME}","",$html);
            $html = str_replace("{DELIVERY_VALUE}","",$html);
            $html = str_replace("{DELIVERY_QUANTITY}","",$html);
            $html = str_replace("{DELIVERY_TOTAL_AMOUNT}","",$html);
        }
        
        // Shipment of physical cards
        $physTotal = 0;
        if($companyOrder->is_email==0) {
            $physPrice = 150;
            $physTotal = $physPrice*1;
            $html = str_replace("{SHIP_ITEM_DISPLAY}","",$html);
            $html = str_replace("{SHIP_ITEM_NO}","GKFRAKT",$html);
            $html = str_replace("{SHIP_ITEM_NAME}","Frakt presentkort",$html);
            $html = str_replace("{SHIP_VALUE}",number_format($physPrice,2,",","."),$html);
            $html = str_replace("{SHIP_TOTAL_AMOUNT}",number_format($physTotal,2,",","."),$html);
            $html = str_replace("{SHIP_QUANTITY}","1",$html);
        } else {
            $html = str_replace("{SHIP_ITEM_DISPLAY}","display: none;",$html);
            $html = str_replace("{SHIP_ITEM_NO}","",$html);
            $html = str_replace("{SHIP_ITEM_NAME}","",$html);
            $html = str_replace("{SHIP_VALUE}","",$html);
            $html = str_replace("{SHIP_TOTAL_AMOUNT}","",$html);
            $html = str_replace("{SHIP_QUANTITY}","",$html);
        }

        // Total
        $Total += $wrapTotal+ $deliveryTotal + $physTotal;
        $totalInclVAT = $Total * 1.25;
        $VATAmount = $totalInclVAT* 0.2;

        $html = str_replace('{TOTAL_AMOUNT}',number_format($Total,2,",","."),$html);
        $html = str_replace('{TOTAL_AMOUNT_VAT}',number_format($totalInclVAT,2,",","."),$html);
        $html = str_replace('{VAT_AMOUNT}',number_format($VATAmount,2,",","."),$html);

        //Billing information
        $html = str_replace('{BILL_TO_ADDRESS}',utf8_decode($company->bill_to_address),$html);
        $html = str_replace('{BILL_TO_ADDRESS_2}',utf8_decode($company->bill_to_address_2),$html);
        $html = str_replace('{BILL_TO_POSTAL_CODE}',utf8_decode($company->bill_to_postal_code),$html);
        $html = str_replace('{BILL_TO_CITY}',utf8_decode($company->bill_to_city),$html);
        
        //$html = str_replace('{BILL_TO_CITY}',utf8_decode($company->bill_to_city),$html);
        $html = str_replace('{BILL_TO_COUNTRY}',utf8_decode($company->bill_to_country),$html);

        //Shipping information
        $html = str_replace('{SHIP_TO_ADDRESS}',utf8_decode($companyOrder->ship_to_address),$html);
        $html = str_replace('{SHIP_TO_ADDRESS_2}',utf8_decode($companyOrder->ship_to_address_2),$html);
        $html = str_replace('{SHIP_TO_POSTAL_CODE}',utf8_decode($companyOrder->ship_to_postal_code),$html);
        $html = str_replace('{SHIP_TO_CITY}',utf8_decode($companyOrder->ship_to_city),$html);
        $html = str_replace('{EAN}',$companyOrder->ean,$html);

        $htmlExtra = "";
        $htmlExtra.="<hr /><b>saleperson:</b><br />".$companyOrder->salesperson;
        $htmlExtra.="<br /><b>Noter:</b><div style=\"width:200px;\">".nl2br(utf8_decode($companyOrder->salenote))."</div></body>";
        if($companyOrder->spdeal == "spdeal"){
            $companyOrder->spdeal = "ja";
        } else {
            $companyOrder->spdeal = "nej";
        }

        $htmlExtra.="<b>Special aftale: </b>".$companyOrder->spdeal;
        $htmlExtra.="<br><b>Special aftale text: </b>".$companyOrder->spdealtxt;
        //$html = str_replace('</body>',$htmlExtra,$html);
        //$html = str_replace('{SHIP_TO_CITY}',utf8_decode($companyOrder->ship_to_city),$html);

        $maildata = [];
        $maildata['sender_email'] =  $mailtempate->sender_order_confirmation;
        $maildata['recipent_email'] = $receiver;
        $maildata['subject']= $mailtempate->subject_order_confirmation;
        //$maildata['body'] = $html.$htmlExtra;
        $maildata['body'] = $html;

        // Set mailserver
        //$maildata['mailserver_id'] = $shop->mailserver_id;
        $maildata['mailserver_id'] = $mailserverid;
        $maildata['company_order_id'] = $companyOrder->id;

        MailQueue::createMailQueue($maildata);

    }
}
