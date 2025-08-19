<?php

namespace GFUnit\privatedelivery\sedsv;
use GFBiz\units\UnitController;
use PhpOffice\PhpSpreadsheet\Calculation\TextData\Format;

// Set memory to 2gb
ini_set('memory_limit', '2048M');

class Controller extends UnitController
{

    private $helper;

    public function __construct()
    {
        parent::__construct(__FILE__);
        $this->helper = new Helpers();
    }

    public function statematrix() {
        
        $statematrix = new StateMatrix();
        $statematrix->dispatch();
        
    }
    

    public function index() {

        $action = isset($_POST["action"]) ? $_POST["action"] : "";

        if($action == "downloadmaster") {
            $masterdata = new Masterdata(false);
            $masterdata->loadFromPost();
            $masterdata->output();
            return;
        }
        if(isset($_GET["shopid"]) && intval($_GET["shopid"]) > 0 && isset($_GET["action"]) &&  $_GET["action"] == "createfile") {
            //$this->createNewList(intval($_GET["shopid"]));
        }
        else if(isset($_GET["filedate"]) && isset($_GET["action"]) && $_GET["action"] == "labellist") {
            $this->downloadFragtListe($_GET["filedate"]);
        }
        else if(isset($_GET["filedate"]) && isset($_GET["action"]) && $_GET["action"] == "checkshortage") {
            $this->checkshortage($_GET["filedate"]);
        }
        else if(isset($_GET["filedate"]) && isset($_GET["action"]) && $_GET["action"] == "removefile") {
            $this->removefile($_GET["filedate"]);
        }

        else if(isset($_POST["action"]) && $_POST["action"] == "saveinput") {

            $this->processInputFile();

        }

        else {
            $this->outputDashboard();
        }

    }

    private function checkShortage($filedate)
    {

        // Get all shipments
        $shipmentList = $this->getShipmentsInFile($filedate);
        $itemSumList = array();

        // Handle each shipment
        foreach($shipmentList as $userorder) {

            // Get item no
            $items = array();

            if(trim($userorder->itemno) != "" && $userorder->quantity > 0) {
                $lineItems = $this->getItemsFromItemNo($userorder->itemno,$userorder->quantity);
                foreach($lineItems as $li) {
                    $items[] = $li;
                }
            }

            if(trim($userorder->itemno2) != "" && $userorder->quantity2 > 0) {
                $lineItems = $this->getItemsFromItemNo($userorder->itemno2,$userorder->quantity2);
                foreach($lineItems as $li) {
                    $items[] = $li;
                }
            }

            if(trim($userorder->itemno3) != "" && $userorder->quantity3 > 0) {
                $lineItems = $this->getItemsFromItemNo($userorder->itemno3,$userorder->quantity3);
                foreach($lineItems as $li) {
                    $items[] = $li;
                }
            }

            if(trim($userorder->itemno4) != "" && $userorder->quantity4 > 0) {
                $lineItems = $this->getItemsFromItemNo($userorder->itemno4,$userorder->quantity4);
                foreach($lineItems as $li) {
                    $items[] = $li;
                }
            }

            if(trim($userorder->itemno5) != "" && $userorder->quantity5 > 0) {
                $lineItems = $this->getItemsFromItemNo($userorder->itemno5,$userorder->quantity5);
                foreach($lineItems as $li) {
                    $items[] = $li;
                }
            }


            // Output a line for each item no
            foreach($items as $itemData) {
                if(!isset($itemSumList[trim(strtolower($itemData["itemno"]))])) {
                    $itemSumList[trim(strtolower($itemData["itemno"]))] = 0;
                }
                $itemSumList[trim(strtolower($itemData["itemno"]))] += intval($itemData["quantity"]);
            }

        }

        // Load lager map
        $lagerMap = DSVLager::getLagerQuantityMap();

        echo "<h2>Tjekker lager</h2>";
        echo "<table style='min-width: 500px;'>";
        echo "<tr style='font-weight: bold; background: #C0C0C0;'><td>Varenr</td><td>På lager</td><td>I fil</td><td>Diff</td></tr>";
        foreach($itemSumList as $item => $count)
        {
            if(!isset($lagerMap[trim(strtolower($item))])) {
                $itemStockCount = 0;
            } else {
                $itemStockCount = $lagerMap[trim(strtolower($item))];
            }
            echo "<tr style='".($itemStockCount < $count ? "background: red; color: white;" : "")."'><td>".$item."</td><td>".$itemStockCount."</td><td>".$count."</td><td>".($itemStockCount-$count)."</td></tr>";
        }
        echo "</table>";

    }

    public function dsvlager() {
        $costReport = new CostReport();
        $costReport->dispatch();
    }

    public function checkmasterdata() {

        $path = "units/privatedelivery/sedsv/";
        $current = "currentmaster.csv";
        $prev = "prevmaster.csv";

        // Delete prev
        try {
            if (file_exists($path . $prev)) {
                unlink($path.$prev);
            }
        } catch(\Exception $e) {
            $this->sendMasterMail("REMOVE PREV PROBLEM","Problem removing prev: ".$e->getMessage());
        }

        // Rename current to prev
        try {
            if (file_exists($path . $current)) {
                rename($path . $current, $path . $prev);
            }
        } catch(\Exception $e) {
            $this->sendMasterMail("MOVE PROBLEM","Problem moving current to prev: ".$e->getMessage());
        }

        // Generate new file
        try {
            $masterdata = new Masterdata(true);
            $masterdata->save($path.$current,true);
        } catch(\Exception $e) {
            $this->sendMasterMail("SAVE PROBLEM","Problem saving new current: ".$e->getMessage());
        }

        // Load the two files
        $currentContent = file_get_contents($path.$current);
        $prevContent = file_get_contents($path.$prev);

        if($currentContent == $prevContent) {
           echo "SIMILAR";
        } else {
           echo "NOT SIMILAR";
            $this->sendMasterMail("CHANGED","Changes to masterdata file!");
        }

    }

    private function sendMasterMail($subject,$message) {
        mailgf("sc@interactive.dk","DSV Masterdata: ".$subject, $message."\r\n\r\nDownload masterfile: https://system.gavefabrikken.dk//gavefabrikken_backend/index.php?rt=unit/privatedelivery/sedsv/masterdata", null);
    }

    public function autopluk() {
        $dash = new Autopluk();
        $dash->dispatch();
    }

    public function masterdata() {

        $masterdata = new Masterdata(true);
        $masterdata->output();

    }

    private function removefile($date) {

        echo "Remove file with date: ".$date."<br>";

        $shipmentList = $this->getShipmentsInFile($date);
        $processed = 0;
        foreach($shipmentList as $shiprow) {

            $shipment = \Shipment::find($shiprow->shipment_id);
            $shopuser = \ShopUser::find($shiprow->shopuser_id);

            $shopuser->delivery_state = 1;
            $shopuser->save();

            $shipment->shipment_state = 1;
            $shipment->shipment_sync_date = null;
            $shipment->save();

            echo "REMOVING SHIPMENT ".$shipment->id." - SHOPUSER ".$shopuser->id."<br>";
            $processed++;

        }

        echo "REMOVED ".$processed;
        \System::connection()->commit();

    }

    public function createpluk() {
        $plukModel = new OpretPluk();
        $plukModel->dispatch();
    }

    public function dashboard() {
        $dash = new Dashboard();
        $dash->dispatch();
    }

    public function lagerrapport($shopid=0) {
        $dash = new LagerRapport();
        $dash->dispatch($shopid);
    }

    /**
     * GET LABELLIST
     */

    private function downloadFragtListe($filedate) {


        // Create excel file

        // Init phpexcel
        $phpExcel = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $phpExcel->removeSheetByIndex(0);

        // Write header
        $sheet = $phpExcel->createSheet();
        $phpExcel->getDefaultStyle()->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
        $phpExcel->getDefaultStyle()->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_TEXT);
        $sheet->setTitle("Data_OutboundOrder");

        $sheet->getColumnDimension('A')->setWidth(9);
        $sheet->getColumnDimension('B')->setWidth(32);
        $sheet->getColumnDimension('C')->setWidth(12);
        $sheet->getColumnDimension('D')->setWidth(9);
        $sheet->getColumnDimension('E')->setWidth(9);
        $sheet->getColumnDimension('F')->setWidth(9);
        $sheet->getColumnDimension('G')->setWidth(10);
        $sheet->getColumnDimension('H')->setWidth(14);
        $sheet->getColumnDimension('I')->setWidth(14);
        $sheet->getColumnDimension('J')->setWidth(14);
        $sheet->getColumnDimension('K')->setWidth(25);
        $sheet->getColumnDimension('L')->setWidth(9);
        $sheet->getColumnDimension('M')->setWidth(9);
        $sheet->getColumnDimension('N')->setWidth(12);

        $headlines = array("Order Id","Ship by Date YYYYMMDDHHMMSS","Instructions","Name","Address","Town","Postcode","Country (SWE)","Contact Name","Contact Phone","Contact Email","Line Id","Sku Id","Qty Ordered");
        foreach($headlines as $index => $headline) {
            $sheet->setCellValueByColumnAndRow($index+1,1,$headline);
        }

        $row=2;
        $date1 = date("Ymd",(time()))."000000";

        // Get all shipments
        $shipmentList = $this->getShipmentsInFile($filedate);

        // Handle each shipment
        foreach($shipmentList as $userorder) {

            // Get original order
            $order = \Order::find_by_sql("SELECT * FROM `order` WHERE shopuser_id = ".$userorder->shopuser_id);

            // Parse phone number
            $phone = intval(str_replace(array("+","-"," "),"",$userorder->shipto_phone));
            if(strlen($phone) == 9) {
                $phone = "+46".$phone;
            }

            // Get user data
            $userData = array(
                $order[0]->order_no,
                $date1,
                "",
                $this->shortenName($userorder->shipto_name,30),
                $userorder->shipto_address.(trim($userorder->shipto_address2) != "" ? ", ".trim($userorder->shipto_address2) : ""),
                $userorder->shipto_city,
                $userorder->shipto_postcode,
                $this->countryToCountryCode($userorder->shipto_country),
                $this->shortenName($userorder->shipto_contact,25),
                $phone,
                $userorder->shipto_email
            );

            // Get item no
            $items = array();

            if(trim($userorder->itemno) != "" && $userorder->quantity > 0) {
                $lineItems = $this->getItemsFromItemNo($userorder->itemno,$userorder->quantity);
                foreach($lineItems as $li) {
                    $items[] = $li;
                }
            }

            if(trim($userorder->itemno2) != "" && $userorder->quantity2 > 0) {
                $lineItems = $this->getItemsFromItemNo($userorder->itemno2,$userorder->quantity2);
                foreach($lineItems as $li) {
                    $items[] = $li;
                }
            }

            if(trim($userorder->itemno3) != "" && $userorder->quantity3 > 0) {
                $lineItems = $this->getItemsFromItemNo($userorder->itemno3,$userorder->quantity3);
                foreach($lineItems as $li) {
                    $items[] = $li;
                }
            }

            if(trim($userorder->itemno4) != "" && $userorder->quantity4 > 0) {
                $lineItems = $this->getItemsFromItemNo($userorder->itemno4,$userorder->quantity4);
                foreach($lineItems as $li) {
                    $items[] = $li;
                }
            }

            if(trim($userorder->itemno5) != "" && $userorder->quantity5 > 0) {
                $lineItems = $this->getItemsFromItemNo($userorder->itemno5,$userorder->quantity5);
                foreach($lineItems as $li) {
                    $items[] = $li;
                }
            }

            
            // Output a line for each item no
            foreach($items as $itemIndex => $itemData) {


                foreach($userData as $index => $dataVal) {
                    //echo $userorder->shipment_id." - ".$dataVal."<br>";
                    $sheet->setCellValueByColumnAndRow($index+1,$row,$dataVal);
                }

                $sheet->setCellValueByColumnAndRow(12,$row,$itemIndex+1);



                $sheet->setCellValueByColumnAndRow(13,$row,DSVLager::toDSVVarenr($itemData["itemno"]));
                $sheet->setCellValueByColumnAndRow(14,$row,$itemData["quantity"]);

                $sheet->getStyle('B' . $row)->getNumberFormat()->setFormatCode(0000);

                $row++;

            }
        }


        // Download
        // Output excel file
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="privatlevering-se-'.substr($filedate,0,10).($row >= 500 ? '-LARGEFILE' : '').'.xlsx"');
        header('Cache-Control: max-age=0');

        $objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($phpExcel);
        $objWriter->save('php://output');
        exit();

    }


    private function shortenName($name, $maxLength)
    {
        // If name is ok
        if (mb_strlen($name) <= $maxLength) {
            return $name;
        }

        // Split up names
        $parts = explode(" ", $name);

        // Only 1 or 2 parts, cut rest off
        if (count($parts) <= 2) {
            return mb_substr($name, 0, $maxLength);
        }

        foreach ($parts as $index => $part) {
            if ($index != 0 && $index != count($parts) - 1) {
                $parts[$index] = mb_strtoupper(mb_substr($part, 0, 1)) . ".";
            }
        }

        $newName = implode(" ", $parts);
        if (mb_strlen($newName) <= $maxLength) {
            return $newName;
        }

        foreach ($parts as $index => $part) {
            if ($index != 0 && $index != count($parts) - 1) {
                $parts[$index] = mb_strtoupper(mb_substr($part, 0, 1));
            }
        }

        $newName = implode(" ", $parts);
        if (mb_strlen($newName) <= $maxLength) {
            return $newName;
        }

        unset($parts[count($parts) - 2]);
        return $this->shortenName(implode(" ", $parts), $maxLength);
    }


    private function getItemsFromItemNo($itemNo,$quantity) {

        $itemData = array();

        // Get bom item
        $bomItemList = \NavisionBomItem::find_by_sql("select * from navision_bomitem where language_id = 1 && parent_item_no like '".$itemNo."' && deleted is null");
        if(count($bomItemList) > 0) {
            foreach($bomItemList as $bomItem) {
                $itemData[] = array("itemno" => $bomItem->no,"quantity" => $bomItem->quantity_per*$quantity);
            }
        }

        else {

            // Load item from nav
            $itemList = \NavisionItem::find_by_sql("select * from navision_item where language_id = 1 && no like '".$itemNo."' && deleted is null");
            if(count($itemList) > 1) {
                echo "ERROR: Multiple items with item no: ".$itemNo; exit();
            } else if(count($itemList) == 0) {
                //echo "ERROR: Could not find items with item no: ".$itemNo;
                return array();
            } else {
                $itemData[] = array("itemno" => $itemList[0]->no,"quantity" => $quantity);
            }
        }

        return $itemData;

    }

    private function countryToCountryCode($country) {

        if(in_array(strtolower($country),array(5,"se","swe","sweden","sverige"))) {
            return "SWE";
        }

        else if(in_array(strtolower($country),array(1,"dk","dnk","danmark","denmark","dan"))) {
            return "DNK";
        }

        else if(in_array(strtolower($country),array(4,"no","nor","norge","norway"))) {
            return "NOR";
        }

        return "SWE";

    }

    private function getShipmentsInFile($filedate) {
        $dateSQL = " && shipment.shipment_sync_date = '$filedate' ";
        $sql = "SELECT shipment.*, shop_user.*, shipment.id as shipment_id, shop_user.id as shopuser_id FROM shipment, shop_user where shipment.shipment_state IN (2,5) && shop_user.delivery_state = 2 && shipment.handler = 'mydsv' && shipment.shipment_type = 'privatedelivery' && navsync_response = shipment.id ".$dateSQL ."";
        return \ShopUser::find_by_sql($sql);
    }

    /**
     * CREATE A NEW LIST
     */

    private function createNewList($shopid)
    {

        $notProcessed = $this->getWaiting($shopid);
        if(count($notProcessed) == 0) {
            $this->dashError = "Der er ikke nogle nye valg i den valgte shop.";
            $this->outputDashboard();
            return;
        }

        // Navsync identifier
        $pullDate = time();
        $count = 0;

        echo "<div style='display: none;'>";

        // Foreach, find by id and set navsync_response and delivery_print_date
        foreach($notProcessed as $shopuserRow) {

            $shopuser = \ShopUser::find($shopuserRow->shopuser_id);
            $shipment = \Shipment::find($shopuserRow->shipment_id);

            if($shopuser instanceof \ShopUser && $shopuser->id > 0 && $shopuser->delivery_state == 1) {
                if($shipment instanceof \Shipment && $shipment->id > 0 && $shipment->shipment_state == 1 && $shipment->from_certificate_no == $shopuser->username) {

                    $shopuser->delivery_state = 2;
                    $shopuser->save();
                    $count++;

                    $shipment->shipment_state = 2;
                    $shipment->shipment_sync_date = date('d-m-Y H:i:s',$pullDate);
                    $shipment->save();

                    echo $shopuser->id.";".$shipment->id.";<br>";
                }
                else {
                    echo "Failed to verify shipment: ".$shopuserRow->shipment_id."<br>";
                }

            }
            else {
                echo "Failed to verify shopuser: ".$shopuserRow->shopuser_id."<br>";
            }

        }
        echo "</div>";

        if($count > 0) {

            $this->dashError = "<span style='color: red;'>Oprettede filer med " . $count . " ordre</span><br>";
            \System::connection()->commit();
        }

        $this->outputDashboard();
    }

    /**
     * SAVE INPUT FILE
     */

    private function processInputFile()
    {

        $type = $_POST["inputtype"];
        $content = $_POST["inputtext"];

        if($content == "") {
            $this->dashError = "Der er ikke noget indhold i inputfeltet";
            $this->outputDashboard();
            return;
        }

        if($type == "") {
            $this->dashError = "Der er ikke valgt en filtype";
            $this->outputDashboard();
            return;
        }

        if($type == "stocklist") {
            $this->processStockList($content);
        }
        else if($type == "orderlist") {
            $this->processOrderList($content);
        }
        else {
            $this->dashError = "Ukendt filtype";
            $this->outputDashboard();
            return;
        }

    }

    private function processStockList($content) {

        // Split content into lines
        $lines = explode("\n",$content);

        // Match first line with known headers
        $knownHeaders = "SKU	Description	Expected	Soft allocated	On hand			Allocated			Available";
        $firstLine = trim($lines[0]);

        if(trim($firstLine) != trim($knownHeaders)) {
            $this->dashError = "Første linje i filen er ikke en kendt header";
            $this->outputDashboard();
            return;
        }

        // Add to dsvinput
        $dsvin = new \DSVInput();
        $dsvin->type = "stocklist";
        $dsvin->content = $content;
        $dsvin->created = date("Y-m-d H:i:s",time());
        $dsvin->linecount = count($lines);
        $dsvin->save();

        \System::connection()->commit();
        \System::connection()->transaction();

        $this->dashError = "<span style='color: green;'>Ny stock list indlæst!</span>";

        $this->outputDashboard();

    }

    private function processOrderList($content) {


        // Split content into lines
        $lines = explode("\n",$content);

        // Match first line with known headers
        $knownHeaders = "Date	Order ID	Status	Shipped Date	Finish Date	Customer ID	Consignee	Consignment	Place	Country	Header host order reference	Header purchase order	Owner ID	Shipment Group	Creation Date	Creation Time";
        $firstLine = trim($lines[0]);

        if(trim($firstLine) != trim($knownHeaders)) {
            $this->dashError = "Første linje i filen er ikke en kendt header";
            $this->outputDashboard();
            return;
        }

        // Add to dsvinput
        $dsvin = new \DSVInput();
        $dsvin->type = "orderlist";
        $dsvin->content = $content;
        $dsvin->created = date("Y-m-d H:i:s",time());
        $dsvin->linecount = count($lines);
        $dsvin->save();

        \System::connection()->commit();
        \System::connection()->transaction();

        $this->dashError = "<span style='color: green;'>Ny order list indlæst!</span>";

        $this->outputDashboard();

    }

    /**
     * OUTPUT DASHBOARD
     */

    private $dashError = "";

    private function outputDashboard() {
        ?><h2>Svensk privatlevering - DSV</h2>

        <?php if($this->dashError != "") { ?><div style="text-align: center; color: red; font-size: 24px; font-weight: bold;"><?php echo $this->dashError; ?></div><?php $this->dashError = ""; } ?>

            <script>

                // Return todays date as dd-mm-yyyy
                function getTodayString() {
                    var now = new Date();
                    var day = ("0" + now.getDate()).slice(-2);
                    var month = ("0" + (now.getMonth() + 1)).slice(-2);
                    var today = now.getFullYear()+"-"+(month)+"-"+(day) ;
                    return today;
                }

                function sendEmail(to, cc, subject, body) {
                    let recipients = to.join(',');
                    let ccRecipients = cc.join(',');
                    let mailtoLink = `mailto:${recipients}?cc=${ccRecipients}&subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
                    document.location = mailtoLink;
                }
            </script>

        <table style="width: 100%;">
            <tr><td style="width: 33%;" valign="top">

                    <h2>Input data</h2>
                    <form action="index.php?rt=unit/privatedelivery/sedsv/index" method="post">
                    <div>
                        Filtype: <select name="inputtype">
                            <option value="">- VÆLG TYPE -</option>
                            <option value="stocklist">Stock Positions</option>
                            <option value="orderlist">Released Orders</option>
                        </select><br>
                        <textarea name="inputtext" style="width: 100%; height: 300px;"></textarea><br>
                        <button type="submit">Gem input</button>
                        <input type="hidden" name="action" value="saveinput">
                    </div>
                    </form><br>

                    <table style="width: 100%; max-width: 1000px;" cellpadding="5" cellspacing="5">
                        <tr style="font-weight: bold;"><td>Dato</td><td>Type</td><td>Linjer</td></tr><?php


                        $inputList = \DSVInput::find_by_sql("SELECT * FROM dsv_input ORDER BY created DESC");
                        foreach($inputList as $input) {
                            echo "<tr>
                                <td>".$input->created->format('Y-m-d H:i')."</td>
                                <td>".$input->type."</td>
                                <td>".$input->linecount."</td>
                            </tr>";
                        }


                        ?></table>

                </td>
                <td style="width: 33%;" valign="top">

                    <div style="float: right; padding-top: 10px; padding-right: 15px;"><button type="button" onclick="document.location='index.php?rt=unit/privatedelivery/sedsv/dashboard';">Dan filer</button></div>
                    <h2>Filer med ordre</h2>
                    <?php

                    $fileList = $this->getFileList();
                    /*
                    $waitingMap = $this->countWaiting();

                    ?><table>
                        <tr>
                            <td style="padding: 10px;">Shop</td>
                            <?php foreach($waitingMap as $shopid => $waitcount) { ?>
                                <td style="padding: 10px;"><?php echo $shopmap[$shopid]->concept_code; ?></td>
                            <?php } ?>
                        </tr>
                        <tr>
                            <td style="padding: 10px;">Antal klar</td>
                            <?php foreach($waitingMap as $shopid => $waitcount) { ?>
                                <td style="padding: 10px;"><?php echo $waitcount; ?></td>
                            <?php } ?>
                        </tr>
                        <tr>
                            <td style="padding: 10px;">Dan fil</td>
                            <?php foreach($waitingMap as $shopid => $waitcount) { ?>
                                <td style="padding: 10px;"><?php if($waitcount > 0) { ?><button type="button" onclick="document.location='?rt=unit/privatedelivery/sedsv/index&action=createfile&shopid=<?php echo $shopid; ?>';">dan fil</button><?php } ?></td>
                            <?php } ?>
                        </tr>
                    </table>
                <?php */ ?>

                    <table style="width: 100%; max-width: 1000px;" cellpadding="5" cellspacing="5">
                        <tr style="font-weight: bold;"><td>Dato</td><td>Antal</td><td>Fragtliste</td></tr><?php

                        foreach($fileList as $file) {
                            echo "<tr>
                                <td>".$file->shipment_sync_date."</td>
                                <td>".$file->shipmentcount."</td>
                                <td><a href='index.php?rt=unit/privatedelivery/sedsv/index&action=labellist&filedate=".$file->shipment_sync_date."'>fragtliste</a> <a href='index.php?rt=unit/privatedelivery/sedsv/index&action=checkshortage&filedate=".$file->shipment_sync_date."'>tjek shortage</a></td>
                            </tr>";
                        }
                        ?></table>



            </td><td style="width: 33%;" valign="top">

                    <form action="" method="post">
                    <h3>Masterdata fil til DSV</h3>
                    <?php

                    $shopidlist = array();
                    $shopList = $this->helper->getShops();
                    foreach($shopList as $shop) {
                        if($shop->language_code == 5) {
                            $shopidlist[] = $shop->shop_id;
                            echo "<label><input type='checkbox' name='shop_" . $shop->shop_id . "' value='1' checked> " . $shop->concept_code . "</label><br>";
                        }
                    }

                    ?>
                    <button>Hent masterdata fil</button>
                        <input type="hidden" name="action" value="downloadmaster">
                    </form>

                    <h3>Mail til DSV</h3>
                    <div style=""><a href="javascript:sendEmail(['cs.laa@se.dsv.com'],[],'Gavefabrikken outbound orders d. '+getTodayString(),'Vedhæftet er outbound orders for Gavefabrikken.')">Send e-mail til dsv</a></div>

                    <h3>Varenr med problemer:</h3>
                    <?php

                    $itemNoList = $this->loadPresentDataFromShops($shopidlist,true);
                    $uniqueItems = $this->findUniqueItems($itemNoList,true);
                    echo $this->dashError;

                    ?>

                    <h3>Upload ordre status</h3>
                    <form action="index.php?rt=unit/privatedelivery/sedsv/uploadstatus" method="post" enctype="multipart/form-data">
                        <input type="file" name="fileToUpload" id="fileToUpload"><br>
                        <button type="submit">Upload</button>
                    </form>


                    <h3>10 nyeste varenr</h3>
                    <ul><?php
                    $latestVarenr = \Order::find_by_sql("select present_model_present_no, min(order_timestamp) startdate from `order` where shop_id in (SELECT shop_id FROM `cardshop_settings` WHERE `privatedelivery_handler` LIKE 'mydsv') group by present_model_present_no order by min(order_timestamp) desc limit 10");
                    foreach($latestVarenr as $ovn) {
                        ?><li><?php echo $ovn->startdate.": ".$ovn->present_model_present_no; ?></li><?php
                    }
                    ?></ul>

                    <h3>Varelisterapport</h3>
                    <?php

                    foreach($shopList as $shop) {
                        if($shop->language_code == 5) {
                             echo "<a href=\"index.php?rt=unit/privatedelivery/sedsv/lagerrapport/".$shop->shop_id."\">".$shop->concept_code . "</a><br>";
                        }
                    }

                    echo "<a href=\"index.php?rt=unit/privatedelivery/sedsv/lagerrapport/0\">Alle shops</a><br>";

                    ?>

                    <h3>Varer ved DSV inkl. kostpris</h3>
                    <a href="index.php?rt=unit/privatedelivery/sedsv/dsvlager/">Hent vareliste inkl. kostpris</a>

            </td></tr>
        </table><?php
    }


    public function uploadstatus() {

        $model = new UploadOrderStatus();
        $model->processUpload();

    }

    private function countWaiting() {
        $list = $this->getWaiting(0);
        $map = array();
        foreach($list as $shopuser) {

            if(!isset($map[$shopuser->shop_id])) $map[$shopuser->shop_id] = 0;
            $map[$shopuser->shop_id]++;
        }
        return $map;
    }

    private function getWaiting($shopid) {
        $shopSQL = (intval($shopid) > 0) ? " && shop_user.shop_id = ".intval($shopid) : "";
        $sql = "SELECT shipment.*, shop_user.*, shop_user.id as shopuser_id, shipment.id as shipment_id FROM shipment, shop_user where shipment.handler = 'mydsv' && shipment.shipment_type = 'privatedelivery' && shipment.shipment_state = 1 && shop_user.delivery_state = 1 && navsync_response = shipment.id ".$shopSQL;
        return \ShopUser::find_by_sql($sql);
    }

    private function getFileList($shopid=0) {
        $shopSQL = (intval($shopid) > 0) ? " && shop_user.shop_id = ".intval($shopid) : "";
        $sql = "SELECT shipment.shipment_sync_date, shop_user.shop_id, count(shipment.id) as shipmentcount FROM shipment, shop_user where shipment.shipment_state IN (2,5) && shop_user.delivery_state = 2 && shipment.handler = 'mydsv' && shipment.shipment_type = 'privatedelivery' && navsync_response = shipment.id ".$shopSQL. " GROUP BY shipment.shipment_sync_date ORDER BY shipment.shipment_sync_date DESC";

        return \ShopUser::find_by_sql($sql);
    }

    /*
    private function downloadMasterdata() {

        $shopList = \CardshopSettings::find_by_sql("select * from cardshop_settings where language_code = 5");
        $shopidlist = array();

        foreach($shopList as $shop) {

            if(isset($_POST["shop_".$shop->shop_id]) && intval($_POST["shop_".$shop->shop_id]) == 1) {
                $shopidlist[] = $shop->shop_id;
            }

        }

        if(count($shopidlist) == 0) {
            $this->dashError = "Der er ikke valgt nogen shops";
            $this->outputDashboard();
            return;
        }

        $itemNoList = $this->loadPresentDataFromShops($shopidlist,true);

        $extraItemNos = array(
       //     "3401WHITE","210190","220162","220163","25858","220130","410879","220125","210187","200140","200141","15699","B-22-12","210188","421046-00","15817","30-LG0004","15817","14942","210186","220171","200704"
            "220171","10672","10692"
        );
        
        foreach($extraItemNos as $extraNo) {
            $itemNoList[] = $extraNo;
        }
        
        // Exclude donations gaver  - 200704 removed
        $excludePresents = array("190704","200325","200326","190701","200327",'sam1950');

        $newItemList = array();
        foreach($itemNoList as $itemNo) {
            if(!in_array($itemNo,$excludePresents)) {
                $newItemList[] = $itemNo;
            }
        }
        $itemNoList = $newItemList;

        $uniqueItems = $this->findUniqueItems($itemNoList,true);

        //echo "<pre>".print_r($uniqueItems,true)."</pre>";
        //echo $this->dashError;

        $this->objPHPExcel = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $this->objPHPExcel->getProperties()->setCreator("Gavefabrikken");
        $this->objPHPExcel->getProperties()->setLastModifiedBy("Gavefabrikken");
        $this->objPHPExcel->getProperties()->setTitle("");
        $this->objPHPExcel->getProperties()->setSubject("");
        $this->objPHPExcel->getProperties()->setDescription("");
        $this->objPHPExcel->getProperties()->setKeywords("");
        $this->objPHPExcel->getProperties()->setCategory("");


        $this->objPHPExcel->removeSheetByIndex(0);
        $sheet = $this->objPHPExcel->createSheet();
        $sheet->setTitle("Sheet1");

        $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('B')->setWidth(17);
        $sheet->getColumnDimension('C')->setWidth(17);
        $sheet->getColumnDimension('D')->setWidth(44);
        $sheet->getColumnDimension('E')->setWidth(17);
        $sheet->getColumnDimension('F')->setWidth(12);
        $sheet->getColumnDimension('G')->setWidth(12);
        $sheet->getColumnDimension('H')->setWidth(12);
        $sheet->getColumnDimension('I')->setWidth(12);
        $sheet->getColumnDimension('J')->setWidth(12);
        $sheet->getColumnDimension('K')->setWidth(12);
        $sheet->getColumnDimension('L')->setWidth(17);
        $sheet->getColumnDimension('M')->setWidth(17);
        $sheet->getColumnDimension('N')->setWidth(17);


        $sheet->setCellValueByColumnAndRow(1, 1, "");
        $sheet->setCellValueByColumnAndRow(2, 1, "");
        $sheet->setCellValueByColumnAndRow(3, 1, "");
        $sheet->setCellValueByColumnAndRow(4, 1, "");
        $sheet->setCellValueByColumnAndRow(5, 1, "");
        $sheet->setCellValueByColumnAndRow(6, 1, "CM");
        $sheet->setCellValueByColumnAndRow(7, 1, "CM");
        $sheet->setCellValueByColumnAndRow(8, 1, "CM");
        $sheet->setCellValueByColumnAndRow(9, 1, "KG");
        $sheet->setCellValueByColumnAndRow(10, 1, "m3");
        $sheet->setCellValueByColumnAndRow(11, 1, "Y/N");
        $sheet->setCellValueByColumnAndRow(12, 1, "Y/N");
        $sheet->setCellValueByColumnAndRow(13, 1, "Y/N");
        $sheet->setCellValueByColumnAndRow(14, 1, "Y/N");

        $sheet->setCellValueByColumnAndRow(1, 2, "");
        $sheet->setCellValueByColumnAndRow(2, 2, "SKU_ID");
        $sheet->setCellValueByColumnAndRow(3, 2, "EAN");
        $sheet->setCellValueByColumnAndRow(4, 2, "DESCRIPTION");
        $sheet->setCellValueByColumnAndRow(5, 2, "PRODUCT_GROUP");
        $sheet->setCellValueByColumnAndRow(6, 2, "EACH_WIDTH");
        $sheet->setCellValueByColumnAndRow(7, 2, "EACH_DEPTH");
        $sheet->setCellValueByColumnAndRow(8, 2, "EACH_HEIGHT");
        $sheet->setCellValueByColumnAndRow(9, 2, "EACH_WEIGHT");
        $sheet->setCellValueByColumnAndRow(10, 2, "EACH_VOLUME");
        $sheet->setCellValueByColumnAndRow(11, 2, "EXPIRY_REQD");
        $sheet->setCellValueByColumnAndRow(12, 2, "SERIAL_NUMBER");
        $sheet->setCellValueByColumnAndRow(13, 2, "BATCH");
        $sheet->setCellValueByColumnAndRow(14, 2, "SPECIAL HANDLING");

        $row = 3;


        foreach($uniqueItems as $item) {
            //if($item->crossreference_no != "") {
            if(!in_array($item->no,$excludePresents)) {

                $sheet->setCellValueByColumnAndRow(2, $row, $item->no);
                $sheet->setCellValueByColumnAndRow(3, $row, $item->crossreference_no."");
                $sheet->setCellValueByColumnAndRow(4, $row, $item->description);


                if($item->width > 3 || $item->length > 3 || $item->height > 3) {
                    
                    $width = $item->width;
                    if($width == 0) {
                        $width = "";
                    }
                    $sheet->setCellValueByColumnAndRow(6, $row, $width);

                    $length = $item->length;
                    if($length == 0) {
                        $length = "";
                    }
                    $sheet->setCellValueByColumnAndRow(7, $row, $length);

                    $height = $item->height;
                    if($height == 0) {
                        $height = "";
                    }
                    $sheet->setCellValueByColumnAndRow(8, $row, $height);

                    $volume = $item->cubage;
                    if($volume == 0) {
                        $volume = "";
                    }
                    $sheet->setCellValueByColumnAndRow(10, $row, $volume/(100*100*100));

                } else {


                    $width = $item->width;
                    if($width == 0) {
                        $width = "";
                    }
                    $sheet->setCellValueByColumnAndRow(6, $row, ($width === "" ? "" : $width*100));


                    $length = $item->length;
                    if($length == 0) {
                        $length = "";
                    }
                    $sheet->setCellValueByColumnAndRow(7, $row,  ($length === "" ? "" : $length*100));

                    $height = $item->height;
                    if($height == 0) {
                        $height = "";
                    }
                    $sheet->setCellValueByColumnAndRow(8, $row,  ($height === "" ? "" : $height*100));

                    $volume = $item->cubage;
                    if($volume == 0) {
                        $volume = "";
                    }
                    $sheet->setCellValueByColumnAndRow(10, $row, $volume === "" ? "" : $volume);


                }



                $weight = $item->gross_weight;
                if($weight == 0) {
                    $weight == $item->net_weight;
                }
                if($weight == 0) {
                    $weight = "";
                }
                $sheet->setCellValueByColumnAndRow(9, $row, $weight);

                $sheet->getStyle('C'.$row)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER);
                $sheet->getStyle('B'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $row++;
            }
        }
        //$sheet->getStyle('C')->getNumberFormat()->setFormatCode('@');

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="masterdata-dsv-'.implode("-",$shopidlist).'-'.date('dmY').'.xlsx"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0

        $objWriter = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($this->objPHPExcel);
        //$objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel2007');

        $objWriter->save("php://output");

    }
*/

    /**
     * UNPACK NAV ITEM NOS FROM ITEMS IN SHOP
     */

    private function findUniqueItems($itemNoList,$returnObjects=true) {

        $uniqueItemNoList = array();
        $itemObjList = array();

        foreach($itemNoList as $itemNo) {

            // Load item from nav
            $itemData = \NavisionItem::find_by_sql("select * from navision_item where language_id = 1 && no like '".$itemNo."' && deleted is null");
            $bomItemList = \NavisionBomItem::find_by_sql("select * from navision_bomitem where language_id = 1 && parent_item_no like '".$itemNo."' && deleted is null");

            // Has bom items
            if(countgf($bomItemList) > 0) {
                foreach($bomItemList as $bomItem) {
                    if(!in_array($bomItem->no,$uniqueItemNoList)) {

                        //$uniqueItemNoList[] = $bomItem->no;

                        $itemSubData = \NavisionItem::find_by_sql("select * from navision_item where language_id = 1 && no like '".$bomItem->no."' && deleted is null");
                        if(countgf($itemSubData) > 0) {
                            if(countgf($itemSubData) > 1) {
                                $this->dashError .= "WARNING: Multiple child items with no ".$bomItem->no."<br>";
                            }
                            foreach($itemSubData as $navItem) {
                                if(!in_array($navItem->no,$uniqueItemNoList)) {
                                    $uniqueItemNoList[] = $navItem->no;
                                    $itemObjList[] = $navItem;
                                }
                            }
                        } else {
                            $this->dashError .= "WARNING: Could not find child item no in nav: ".$bomItem->no."<br>";
                        }

                    }
                }
            }
            // Has items
            else if(countgf($itemData) > 0) {
                if(countgf($itemData) > 1) {
                    $this->dashError .= "WARNING: Multiple items with no ".$itemNo."<br>";
                }
                foreach($itemData as $navItem) {
                    if(!in_array($navItem->no,$uniqueItemNoList)) {
                        $uniqueItemNoList[] = $navItem->no;
                        $itemObjList[] = $navItem;
                    }
                }
            }
            // Other
            else {
                $this->dashError .= "WARNING: Could not find item no in nav: ".$itemNo."<br>";
            }

        }

        return $returnObjects ? $itemObjList : $uniqueItemNoList;
    }

    /**
     * Load present data from shop
     */

    private function loadPresentDataFromShops($shopidlist,$itemNoOnly=false) {

        if(countgf($shopidlist) == 0) return array();
        $sql = "SELECT present.name, present.nav_name, present.internal_name, present_model.model_id, present_model.present_id, present_model.model_present_no, present_model.model_name, present_model.model_no, present_model.fullalias FROM `present_model`, present where present.id = present_model.present_id && present_model.language_id = 1 && present.shop_id in (".implode(",",$shopidlist).") && present_model.fullalias != ''";
        $sql .= " && present_model.model_id in (SELECT present_model_id from `order` where shop_id in (".implode(",",$shopidlist)."))";

        $presentmodellist = \PresentModel::find_by_sql($sql);

        if($itemNoOnly == false) {
            return $presentmodellist;
        }

        $modelNoList = array();
        foreach($presentmodellist as $presentmodel) {
            $itemNo = $presentmodel->model_present_no;
            if(!in_array($itemNo,$modelNoList)) {
                $modelNoList[] = $itemNo;
            }
        }

        return $modelNoList;

    }

}