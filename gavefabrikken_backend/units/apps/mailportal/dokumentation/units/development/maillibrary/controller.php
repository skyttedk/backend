<?php

namespace GFUnit\development\maillibrary;

use GFBiz\units\UnitController;
use GFBiz\MailLibrary\LibraryLoader;

class Controller extends UnitController
{

    public function __construct()
    {
        parent::__construct(__FILE__);
    }

    public function test() {

        echo "HELLO MAILS!";

        $mailObject = LibraryLoader::getMailTemplateObject("receipt",1,10,0);

        var_dump($mailObject);


    }

    public function createReciptNew($orderId,$languageId)
    {
        
        
        // Create new receipt
        $receiptMail = new MailTemplateReceipt();


        $receiptMail->sendEmail();


        $maildata = [];
        $maildata['sender_email'] = $mailtempate->sender_receipt;

        // Special rule for Tryg DK/NO/SE 2022, use username (username is email)
        if(in_array($shopuser->shop_id,array(3083,3471,3834))) {
            $maildata['recipent_email'] = $shopuser->username;
        }
        else {
            $maildata['recipent_email'] =$orderdetails['email'];
        }
        $maildata['order_id'] = $orderdetails['order_id'];
        $maildata['subject']= $mailtempate->subject_receipt;
        $maildata['body'] = $maintemplate;
        $maildata['sent'] = $send;
        // Set mailserver
//        $maildata['mailserver_id'] = $shop->mailserver_id;
        $maildata['mailserver_id'] = 4;

        if($shop->id == 1832 || $shop->id == 1981) {
            $maildata['mailserver_id'] = 5;
        }

        MailQueue::createMailQueue($maildata,1);
        
        
    }

    public function createReceipt($orderId,$languageId)
    {

        /**
         * LOAD DATA
         */

        $order = Order::find($orderId);
        $shopuser = ShopUser::find($order->shopuser_id);
        $shop = Shop::find($order->shop_id);
        $orderhistory = OrderHistory::find('all',array('conditions' => array('shopuser_id = ? and order_no <> ?', $shopuser->id,$order->order_no)));
        $orderdetails = Order::getOrderDetails($orderId,$languageId);

        $orderdetails['present_model_name'] = str_replace("###"," - ", $orderdetails['present_model_name']);

        $mailtempate = MailTemplate::getTemplate($order->shop_id,$languageId);
        $receipt = Receipt::find_by_sql("SELECT * FROM `receipt_custom_part` where id = ( SELECT msg1 FROM `present_model` WHERE `model_id` = '".$orderdetails['present_model_id']."' and `language_id` = 1  )");

        $presentsM = PresentModel::find_by_sql("select media_path from present_model where model_id = ".$orderdetails['present_model_id']." and language_id = 1 ");

        /**
         * PROCESS DATA
         */

        $description = base64_decode($orderdetails['present_description']);

        if($mailtempate != null) {
            $maintemplate  = $mailtempate->template_receipt;
        } else {
            $maintemplate = "";
        }

        $modelImage = $presentsM[0]->attributes["media_path"];

        if(strpos($modelImage,"blank") > -1 ){
            $modelImage = GFConfig::BACKEND_URL."/mail/image/".$orderdetails['present_image'];

        }

        /**
         * REPLACE IN TEMPLATE
         */

        $maintemplate = str_replace('{ORDERNO}',$orderdetails['order_no'],$maintemplate);
        $maintemplate = str_replace('{DATE}',$orderdetails['date_stamp'],$maintemplate);
        $maintemplate = str_replace('{PRESENT_NAME}',$orderdetails['present_caption'],$maintemplate);

        // KVITTERING CUSTOM TILFØJELSE

        $receiptTxt = "";

        if(sizeofgf($receipt) > 0){
            if($languageId == "1"){
                $receiptTxt = "<tr><td colspan='2'><hr><br>".$receipt[0]->attributes["da"]."<br><br></td></tr>";
            } else {
                $receiptTxt = "<tr><td colspan='2'><hr><br>".$receipt[0]->attributes["en"]."<br><br></td></tr>";
            }
            $maintemplate = str_replace('{RECEIPT_POS2}',$receiptTxt,$maintemplate);
        } else {
            $maintemplate = str_replace('{RECEIPT_POS2}',"",$maintemplate);
        }


        // KVITTERING CUSTOM indset ugenr tag:  #ugenr#     18033772
        if($orderdetails['shop_is_gift_certificate'] == "1"){
            // hvis hjemmelevering
            if($shopuser->is_delivery == "1" ){

                if($order->shop_id == 1832 || $order->shop_id == 1981 || $order->shop_id == 2558) {
                    $hjemmelevHtml = '<tr><td colspan="2"><hr><br><p>Ditt presentval kan ändras i 24 timmar. Ditt val kommer då att överföras till packeriet och det kommer inte att vara möjligt att göra några ändringar därefter.<br /><br />Vi skickar normalt ditt paket inom 10 arbetsdagar, dock tidigast 1 december.<br /><br />Observera att extra leveranstider på upp till 20 dagar kan upplevas under högsäsong (december, januari och februari)<br /><br />Har du beställt ett upplevelsepresentkort, vistelse eller kryssning så skickas det slutgiltiga presentkortet till dig. Detta kvitto kan därför inte användas som presentkort.</p><br></td></tr>';
                } else if($order->shop_id == 574 || $order->shop_id == 57 || $order->shop_id == 272 || $order->shop_id == 58 || $order->shop_id == 59  || $order->shop_id == 2550  || $order->shop_id == 2549) {
                    $rs = ExpireDate::find_by_sql("SELECT week_no FROM `expire_date` where expire_date = (SELECT expire_date FROM `shop_user` WHERE shop_id = ".$order->shop_id." && `username` = '".$orderdetails['user_username']."') ");
                    $weeknr = "";
                    if(sizeofgf($rs) > 0){
                        $weeknr =  $rs[0]->attributes["week_no"];
                    }
                    $hjemmelevHtml = '<tr><td colspan="2"><hr><br><p>Din gave vil bli sendt fra oss i uke '.$weeknr.', og vil hurtigst mulig bli levert til ditt n&aelig;rmeste utleveringssted. (post i butikk)</p>
                        <p>Skulle du ombestemme deg, kan du endre ditt valg helt frem til deadline. Du logger bare p&aring; gavevalg.no igjen og velger p&aring; nytt.</p><br><br></td></tr>';
                }
                else {

                    // LUKSUS
                    if(in_array($order->shop_id,array(2961,2960,2962,2963))) {
                        $hjemmelevHtml = '<tr><td colspan="2"><hr><br><p>Dit gavevalg kan &aelig;ndres i 24 timer. Derefter overf&oslash;res dit valg til pakkeriet, og det vil herefter ikke v&aelig;re muligt at foretage &aelig;ndringer.</p><p>Der kan opleves ekstra leveringstid op til 20 arbejdsdage i december, januar og februar &nbsp;(normal leveringstid 10 arbejdsdage)</p><p>Har du bestilt et oplevelsesgavekort, ophold eller cruise vil det endelige gavekort blive sendt til dig. Denne kvittering kan derfor ikke benyttes som gavebevis.</p><p>Husk, at du selv kan tilk&oslash;be flere designprodukter til gode priser p&aring; <a href="https://www.a58uy1lh5t1oilb67l0gaveklubben.dk/" mc:disable-tracking>Gaveklubben</a></p><br><br></td></tr>';
                    }
                    // OTHER DK
                    else {
                        $hjemmelevHtml = '<tr><td colspan="2"><hr><br><p>Dit gavevalg kan &aelig;ndres i 24 timer. Derefter overf&oslash;res dit valg til pakkeriet, og det vil herefter ikke v&aelig;re muligt at foretage &aelig;ndringer.</p><p>Din pakke bliver sendt med GLS og leveret til n&aelig;rmeste Pakkeshop.&nbsp;</p>                                    <p>Bem&aelig;rk at der kan opleves ekstra leveringstid op til 20 dage i h&oslash;js&aelig;sonen (december, januar og februar)</p>                                    <p>Har du bestilt et oplevelsesgavekort, ophold eller cruise vil det endelige gavekort blive sendt til dig. Denne kvittering kan derfor ikke benyttes som gavebevis.</p> <p>Husk, at du selv kan tilk&oslash;be flere designprodukter til gode priser p&aring;&nbsp;<a href="https://www.a58uy1lh5t1oilb67l0gaveklubben.dk/" target="_blank">GAVEKLUBBEN&trade;</a></p><br><br></td></tr>';
                    }

                    // OLD TEXT
                    // $hjemmelevHtml = '<tr><td colspan="2"><hr><br><p style="margin: 0cm 0cm 0.0001pt; font-size: 11pt; font-family: Calibri, sans-serif;"><strong>Dit gavevalg kan &aelig;ndres i 24 timer. Derefter overf&oslash;res dit valg til pakkeriet, og det vil herefter ikke v&aelig;re muligt at foretage &aelig;ndringer.<br /><br /><span style="font-size: 1.2em;">Din gave leveres i l&oslash;bet af 5-10 hverdage &ndash; dog tidligst fra 1. december 2021.<span></strong><br /><br /><strong>Havde du sv&aelig;rt ved at v&aelig;lge? </strong></p><p style="margin: 0cm 0cm 0.0001pt; font-size: 11pt; font-family: Calibri, sans-serif;"><strong>Husk, at du selv kan tilk&oslash;be flere designprodukter til gode priser p&aring; <u><span style="color: #44546a;">Gaveklubben&trade;</span></u><span style="color: #44546a;"> -<a href="https://www.a58uy1lh5t1oilb67l0gaveklubben.dk"> KLIK HER</a></span></strong></p><br><br></td></tr>';

                }

                $maintemplate = str_replace('{RECEIPT_POS1}',$hjemmelevHtml,$maintemplate);

            } else {
                if($order->shop_id == "57" || $order->shop_id == "58" || $order->shop_id == "59" || $order->shop_id == "272" || $order->shop_id == "574" || $order->shop_id == "2550" || $order->shop_id == "2549"){
                    $rs = ExpireDate::find_by_sql("SELECT week_no FROM `expire_date` where expire_date = (SELECT expire_date FROM `shop_user` WHERE shop_id = ".$order->shop_id." && `username` = '".$orderdetails['user_username']."') ");
                    $weeknr = "";
                    if(sizeofgf($rs) > 0){
                        $weeknr =  $rs[0]->attributes["week_no"];
                    }
                    $standartLevHtml = '<tr><td colspan="2"><hr><br><p><strong>Din gave vil bli levert til din arbeidsplass i uke '.$weeknr.' </strong></p>
                                         <p>Skulle du ombestemme deg, kan du endre ditt valg helt frem til deadline. Du logger bare p&aring; gavevalg.no igjen og velger p&aring; nytt.</p>
                                         <p>Har du valgt et cruise med DFDS vil gavekortet bli sendt til din arbeidsplass, samtidig med gavene til dine kolleger. Denne kvittering kan ikke benyttes som gavebevis.</p><br><br></td></tr>';
                    $maintemplate = str_replace('{RECEIPT_POS1}',$standartLevHtml,$maintemplate);
                }
                else if($order->shop_id == "1832" || $order->shop_id == "1981" || $order->shop_id == "2558") {

                    $standartLevHtml = '<tr><td colspan="2"><hr><br><p>Din g&aring;va levereras till din arbetsplats efter &ouml;verenskommelse med den g&aring;voansvariga.<br /><br />Har du best&auml;llt ett presentkort f&ouml;r en hotellvistelse eller M&aring; bra-upplevelse kommer v&auml;rdebeviset inom 14 dagar att skickas till dig antingen p&aring; mejl eller posten.</p><br><br></td></tr>';
                    $maintemplate = str_replace('{RECEIPT_POS1}',$standartLevHtml,$maintemplate);

                }
                else {



                    $rs = ExpireDate::find_by_sql("SELECT week_no FROM `expire_date` where expire_date = (SELECT expire_date FROM `shop_user` WHERE shop_id = ".$order->shop_id." && `username` = '".$orderdetails['user_username']."') ");
                    $weeknr = "";
                    if(sizeofgf($rs) > 0){
                        $weeknr =  $rs[0]->attributes["week_no"];
                    }

//              $standartLevHtml = '<tr><td colspan="2"><hr><br><p><strong>Din gave vil blive leveret i uge '.$weeknr.' </strong></p><p><strong>&nbsp;</strong><strong>Skulle du ombestemme dig, kan du &aelig;ndre dit valg helt frem til deadline. Du logger blot p&aring; valgshoppen igen og v&aelig;lger p&aring; ny. </strong></p>
                    //            <p><strong>Har du bestilt et oplevelsesgavekort, ophold eller cruise vil det rigtige gavekort blive sendt til din arbejdsplads p&aring; lige fod med dine kollegaers gaver. Denne kvittering kan derfor ikke bruges som gavebevis. &nbsp;</strong></p>
                    //           <p style="margin: 0cm 0cm 0.0001pt; font-size: 11pt; font-family: Calibri, sans-serif;"><strong>Husk, at du selv kan tilk&oslash;be flere designprodukter til gode priser p&aring; <u><span style="color: #44546a;">Gaveklubben&trade;</span></u><span style="color: #44546a;"> -<a target="_blank" href="https://www.a58uy1lh5t1oilb67l0gaveklubben.dk"> KLIK HER</a></span></strong></p><br><br></td></tr>';
                    $standartLevHtml = '<tr><td colspan="2"><hr><br><p><strong>Din gave vil blive leveret til din arbejdsplads i uge: '.$weeknr.' </strong></p><p>Skulle du ombestemme dig, kan du &aelig;ndre dit valg helt frem til deadline. Du logger blot p&aring; gaveshoppen igen og v&aelig;lger p&aring; ny.</p>
                <p>Har du bestilt et oplevelsesgavekort, ophold eller cruise vil det endelige gavekort blive sendt til din arbejdsplads. Denne kvittering kan derfor ikke benyttes som gavebevis.</p>
                <p>Husk, at du selv kan tilk&oslash;be flere designprodukter til gode priser p&aring; <a href="https://www.a58uy1lh5t1oilb67l0gaveklubben.dk/" mc:disable-tracking>Gaveklubben</a></p><br><br></td></tr>';

                    $maintemplate = str_replace('{RECEIPT_POS1}',$standartLevHtml,$maintemplate);
                }

            }
        } else {

            $maintemplate = str_replace('{RECEIPT_POS1}',"",$maintemplate);

        }

        $maintemplate = str_replace('{PRESENT_DESCRIPTION}',$description,$maintemplate);
        // $maintemplate = str_replace('{PRESENT_IMAGE}',$orderdetails['present_image'],$maintemplate);
        $receipt_exists = "";
        // find model image and insert


        //$result['present_model_id']  = $presentModel->media_path;


        //$fileName = pathinfo($modelImage, PATHINFO_FILENAME);
        // background-image: url("https://system.gavefabrikken.dk/gavefabrikken_backend/views/media/user/lyss46tcbbc8skggdfh11654764149.jpg"); width: 549px; height: 520px;
        $rendomFilename = self::generateRandomString(30);
        $fullPath = $_SERVER["DOCUMENT_ROOT"]."/gavefabrikken_backend/views/media/mail/".$rendomFilename. "_small.jpg";
        $realPath = "https://system.gavefabrikken.dk/gavefabrikken_backend/views/media/mail/".$rendomFilename. "_small.jpg";


        if(strstr($modelImage,"http") == false){
            $modelImage = "http://system.gavefabrikken.dk/gavefabrikken_backend/views/media/type/".$modelImage;
        }

        $image = @imagecreatefromjpeg($modelImage);
        if (!$image) {
            return null;
        }

        $width = imagesx($image);
        $height = imagesy($image);
        $ration = $width / $height;
        $new_height = 250 / $ration;
        $new_height = (int) $new_height;
        $new_width = 250;
        $imageResized = imagescale($image, $new_width, $new_height);
        $write = imagejpeg($imageResized, $fullPath);
        imagedestroy($imageResized);

        if ($write) {
            $modelImageStr = "<img src=\"".$realPath."\" alt=\"gave\" style=\"width: 250px\" />";
            $maintemplate = str_replace('{MODEL_IMAGE}',$modelImageStr ,$maintemplate);
        }

        $maintemplate = str_replace('{MODEL_NAME}',utf8_encode($orderdetails['present_model_name']),$maintemplate);


        // QR logic
        $htmlQR = "";
        if($orderdetails['shop_is_gift_certificate'] == 0 && $shop->show_qr == 1){

            $pathQr = GFConfig::BACKEND_URL."thirdparty/phpqrcode/index.php?value=".$orderdetails['order_no'];

            //$typeQr = pathinfo($pathQr, PATHINFO_EXTENSION);
            //$dataQr = file_get_contents($pathQr);
            //$baseQr = 'data:image/png;base64,' . base64_encode($dataQr);
            $htmlQR =  "<tr>
            <td colspan='2'>
            <table border=0 width=100%>
                    <tr>
                     <td ></td>
                  <td >

                  <img src='".$pathQr."' />
                  </td>
                  <td ></td>
                  </tr>

            </table>
            </td>
        </tr>";
        } else {
            $htmlQR = "";
        }

        // Shops where qr code is disabled
        $qrDisabledShops = array(1821);
        if(in_array($shop->id,$qrDisabledShops)) {
            $htmlQR = "";
        }


        $maintemplate = str_replace('{qr}',$htmlQR ,$maintemplate);
        $orderhistory = null;


        if($orderhistory == null){
            $orderhistory = [];
        }

        if(count($orderhistory)>0)  {
            $orderno = end($orderhistory)->order_no;
            $receipt_exists  = $mailtempate->template_receipt_exists;
            $receipt_exists = str_replace('{ORDER_NO}',$orderno,$receipt_exists);
        }

        $maintemplate = str_replace('{RECEIPT_EXISTS}',$receipt_exists,$maintemplate);

        $deliveryinfo = "";
        $maintemplate = str_replace('{DELIVERY_INFO}',$deliveryinfo,$maintemplate);

        //Append User Attributes
        $userattributes = "";
        foreach($orderdetails['attributes'] as $key => $val) {
            // $key_ = utf8_decode($key);
            //  $val_ = utf8_decode($val);
            if($val!="")
                $userattributes .= "<tr><td align='left' >$key</td><td align='right'>$val</td></tr>";
        }

        $maintemplate = str_replace('{USER_DETAILS}',$userattributes,$maintemplate);

        $extra = "";
        if($shop->id==297) {
            if($languageId==1)
            {
                //$extra = "</table><hr /><table><tr><td align='left' colspan=2><br>Støt UNICEFs projekter for børn verden rundt: Giv en personlig donation til UNICEF via MobilePay på +45 20 11 47 00 (skriv 'Novozymes' i kommentarfeltet). Tak!</td></tr></table>";
            }else if($languageId==2)  {
                //$extra = "</table><hr /><table><tr><td align='left' colspan=2><br>Help support UNICEF's work with children around the globe: Make a private donation to UNICEF via MobilePay on +45 20 11 47 00  (Write 'Novozymes' in the comment field). Thank you!</td></tr></table>";
            }
            $extra = utf8_decode($extra);
        }



        if($shop->shipment_date != "" && $shop->id != 297){
            //$maintemplate = str_replace('{SHIPMENT_DATE}',$shop->shipment_date->format('d-m-Y'),$maintemplate);
            if($languageId==1)
            {
                $extra = "</table><hr /><table width=300><tr><td align='left' width=150>Leveringsdato:</td><td align='left' width=150>".$shop->shipment_date->format('d-m-Y')."</td></tr></table>";
                if($shop->id == 3155 || $shop->id == 3591 || $shop->id == 3605 || $shop->id == 3602 || $shop->id == 3606 || $shop->id == 3604){
                    $extra = "</table><hr /><table width=40%><tr><td align='left' width=150>Forventet leveringsdato:</td><td align='left' width=150>".$shop->shipment_date->format('d-m-Y')."</td></tr></table><br>";

                    if($shop->id == 3155) {
                        $extra .= ' <table width=90%><tr><td><p >Hvis du fortryder dit gavevalg, kan du <a mc:disable-tracking href="https://findgaven.dk/gavevalg/tdcshop1">v&aelig;lge en anden gave her</a> til og med 14. oktober.</p>
                <p  >Har du sp&oslash;rgsm&aring;l eller problemer, s&aring; kontakt GaveFabrikken p&aring; <a href="mailto:julegaveservice@gavefabrikken.dk">julegaveservice@gavefabrikken.dk</a></p></td></tr></table>';
                    }
                    if($shop->id == 3591) {
                        $extra .=  ' <table width=90%><tr><td><p>Hvis du fortryder dit gavevalg, kan du <a mc:disable-tracking href="https://findgaven.dk/gavevalg/nuuday2022">v&aelig;lge en anden gave her</a> til og med 14. oktober.&nbsp;</p>
                        <p>Har du sp&oslash;rgsm&aring;l eller problemer, s&aring; kontakt GaveFabrikken p&aring; <a href="mailto:julegaveservice@gavefabrikken.dk">julegaveservice@gavefabrikken.dk</a></p></td></tr></table>';
                    }

                    if($shop->id == 3605) {
                        $extra .= ' <table width=90%><tr><td><p >Hvis du fortryder dit gavevalg, kan du <a mc:disable-tracking href="https://findgaven.dk/gavevalg/tdc2022-2">v&aelig;lge en anden gave her</a> til og med 11. november.</p>
                        <p  >Har du sp&oslash;rgsm&aring;l eller problemer, s&aring; kontakt GaveFabrikken p&aring; <a href="mailto:julegaveservice@gavefabrikken.dk">julegaveservice@gavefabrikken.dk</a></p></td></tr></table>';
                    }
                    if($shop->id == 3602) {
                        $extra .=  ' <table width=90%><tr><td><p>Hvis du fortryder dit gavevalg, kan du <a mc:disable-tracking href="https://findgaven.dk/gavevalg/nuuday2022-2">v&aelig;lge en anden gave her</a> til og med 11. november.&nbsp;</p>
                        <p>Har du sp&oslash;rgsm&aring;l eller problemer, s&aring; kontakt GaveFabrikken p&aring; <a href="mailto:julegaveservice@gavefabrikken.dk">julegaveservice@gavefabrikken.dk</a></p></td></tr></table>';
                    }
                    if($shop->id == 3604 || $shop->id == 3606 ) {
                        $extra .= ' <table width=90%><tr><td><p  >Har du sp&oslash;rgsm&aring;l eller problemer, s&aring; kontakt GaveFabrikken p&aring; <a href="mailto:julegaveservice@gavefabrikken.dk">julegaveservice@gavefabrikken.dk</a></p></td></tr></table>';
                    }


                }

            }else if($languageId==2)  {
                $extra = "</table><hr /><table width=90%><tr><td align='left' width=150>Delivery Date:</td><td align='left' width=150>".$shop->shipment_date->format('d-m-Y')."</td></tr></table>";
            }
            $extra = utf8_decode($extra);


        }

        if($shop->id == 1648000){
            $extra =  '<tr><td colspan=2><br><br><p><b>Julegaven kan afhentes, mod forevisning af denne kvittering, i CAU’s hus på Amager Strandvej 418 fra 30. november til 18. december i tidsrummet 9-15</b></p></td></tr>';
            $extra = utf8_decode($extra);
        }

        $maintemplate = str_replace('{EXTRA}',$extra,$maintemplate);






        /*  rambøll hack */
        if($shop->id == 599){
            $ramText1 = '<div style=" text-align: left; width:800px; margin-left:40px;"><br> <br><p style="margin: 0cm 0cm 0.0001pt; font-size: 11pt; font-family: Calibri, sans-serif; color: black;"><strong><span style="font-size: 12.0pt; font-family: Verdana, sans-serif; color: #00b0f0;">Collect your Christmas present on the 12<sup>th</sup> of December 2018</span></strong></p>
                            <p>Dear colleague<br>
                            You can pick up your Christmas present on the 12<sup>th</sup> of December 2018 from 10:00 to 14:00.</p>
                            <p>We will be using QR codes for pick up. This way you don\'t have to print the receipt.&nbsp;<br>
                            You can show the receipt on your smartphone and we will scan your code from the phone.<br />If you do not have a smartphone please print and bring the receipt with you when picking up your present.</p>
                            <p><br /><strong>NOTE:</strong> If you are not able to make it on this day, please arrange for a colleague to pick up the present for you.<br>
                            All presents must be picked up before Christmas. Remaining presents will be returned.&nbsp;</p>
                            <p><br />The Christmas Present Committee</p></div>';
            $ramText2 =   '<br><br><div style=" text-align: left; width:800px; margin-left:40px;"><p style="margin-right: 0cm; margin-left: 0cm; font-size: 11pt; font-family: Calibri, sans-serif; color: black;"><strong><span style="font-family: Verdana, sans-serif; color: #00b0f0;">Would you like to buy an addition to your company Christmas present?</span></strong></p>
                            <p>Here is the opportunity for you to buy exclusive products at favorable prices <br > at Ramboll Private Shop for a short period from 12<sup>th</sup>. December &ndash; 31<sup>th</sup>. January 2019:</p>
                             <p><a target="_blanck" href="https://www.shopgavefabrikken.dk/vipshop/907/"><strong><u>Link to shop</u></strong></a></p></div><br><br>';

            $maintemplate = str_replace('{text1}',$ramText1,$maintemplate);
            $maintemplate = str_replace('{text2}',$ramText2,$maintemplate);
        } else {
            $maintemplate = str_replace('{text1}',"",$maintemplate);
            $maintemplate = str_replace('{text2}',"",$maintemplate);
        }



        $maildata = [];
        $maildata['sender_email'] = $mailtempate->sender_receipt;

        // Special rule for Tryg DK/NO/SE 2022, use username (username is email)
        if(in_array($shopuser->shop_id,array(3083,3471,3834))) {
            $maildata['recipent_email'] = $shopuser->username;
        }
        else {
            $maildata['recipent_email'] =$orderdetails['email'];
        }
        $maildata['order_id'] = $orderdetails['order_id'];
        $maildata['subject']= $mailtempate->subject_receipt;
        $maildata['body'] = $maintemplate;
        $maildata['sent'] = $send;
        // Set mailserver
//        $maildata['mailserver_id'] = $shop->mailserver_id;
        $maildata['mailserver_id'] = 4;

        if($shop->id == 1832 || $shop->id == 1981) {
            $maildata['mailserver_id'] = 5;
        }
        

    }




}