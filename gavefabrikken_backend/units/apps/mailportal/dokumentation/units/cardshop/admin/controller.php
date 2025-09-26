<?php

namespace GFUnit\cardshop\admin;
use GFBiz\Model\Cardshop\BlockMessageLogic;
use GFBiz\Model\Cardshop\DestroyOrder;
use GFBiz\units\UnitController;
use GFCommon\Model\Navision\CustomerWS;
use GFUnit\navision\syncorder\OrderSync;

class Controller extends UnitController
{

    private $adminOrderHelper;

    public function __construct()
    {
        parent::__construct(__FILE__);

    }

    public function index()
    {
        echo "NO ACTIONS";
    }

    public function coiframe($companyorderid=0)
    {

        // Check order
        $this->adminOrderHelper = new AdminOrderHelper(intval($companyorderid));
        if($this->adminOrderHelper->hasError()) {
            $this->outputError($this->adminOrderHelper->getError());
        }

        $frontMessage = $this->adminOrderHelper->getFrontMessage();

        // Generate content
        ob_start();

        if($frontMessage != "") {
            echo "<div style='background: #007bff; color: white; text-align: center; padding: 10px; margin-bottom: 20px;'>".$frontMessage."</div>";
        } ?>

        <h3 style="text-align: center;">
            Du kan udføre følgende handlinger på ordren:
        </h3>
        <div>

            <?php if($this->adminOrderHelper->canChangeDebitorNo()) { ?>
                <div style="padding: 20px; border-bottom: 1px solid #CACACA;">
                    <button type="button" class="btn btn-info form-control" onClick="goToAction('changedebitorno')">Skift kundens kundenr i navision</button>
                    <p style="margin-top: 10px; margin-bottom: 0px; text-align: center;">Kunden er oprettet på forkert kundenr i navision, behold på samme virksomhed i cardshop men flyt til andet kundenr i navision. Det nye kundenr skal være oprettet i navision.</p>
                </div>
            <?php } ?>

            <?php if($this->adminOrderHelper->canMoveCompany()) { ?>
                <div style="padding: 20px; border-bottom: 1px solid #CACACA;">
                    <button type="button" class="btn btn-info form-control" onClick="goToAction('movecompany')">Flyt til en anden virksomhed</button>
                    <p style="margin-top: 10px; margin-bottom: 0px; text-align: center;">Flyt denne ordre til en anden virksomhed i cardshop. Virksomheden skal være oprettet først.</p>
                </div>
            <?php } ?>

            <?php if($this->adminOrderHelper->canRemoveOrder()) { ?>
                <div style="padding: 20px; border-bottom: 1px solid #CACACA;">
                    <button type="button" class="btn btn-info form-control" onClick="goToAction('closeorder')">Luk / krediter ordren i navision</button>
                    <p style="margin-top: 10px; margin-bottom: 0px; text-align: center;">Vil lukke alle kort på ordren og kreditere ordren i navision.</p>
                </div>
            <?php } ?>

            <?php if($this->adminOrderHelper->canSendOrderConfirmation()) { ?>
                <div style="padding: 20px; border-bottom: 1px solid #CACACA;">
                    <button type="button" class="btn btn-info form-control" onClick="goToAction('sendorderconf')">Send ny ordrebekræftelse</button>
                    <p style="margin-top: 10px; margin-bottom: 0px; text-align: center;">Vil sende en opdatering itl navision om at sende en ny ordrebekræftelse til kunden.</p>
                </div>
            <?php } ?>

            <?php if($this->adminOrderHelper->canReopenOrder()) { ?>
                <div style="padding: 20px; border-bottom: 1px solid #CACACA;">
                    <button type="button" class="btn btn-info form-control" onClick="goToAction('reopenorder')">Genåben ordren og giv den nyt BS nr</button>
                    <p style="margin-top: 10px; margin-bottom: 0px; text-align: center;">Vil genåbne ordren så den sendes til navision med et nyt BS nr. Bemærk, vil aktivere alle kort på ordren igen.</p>
                </div>
            <?php } ?>

            <?php if($this->adminOrderHelper->canChangeSalesPerson()) { ?>
                <div style="padding: 20px; border-bottom: 1px solid #CACACA;">
                    <button type="button" class="btn btn-info form-control" onClick="goToAction('changesalesperson')">Skift sælger på ordre</button>
                    <p style="margin-top: 10px; margin-bottom: 0px; text-align: center;">Skift sælgerkoden der er angivet på ordren.</p>
                </div>
            <?php } ?>

            <?php if($this->adminOrderHelper->canChangeDeliverySettings()) { ?>
                <div style="padding: 20px; border-bottom: 1px solid #CACACA;">
                    <button type="button" class="btn btn-info form-control" onClick="goToAction('deliveryoptions')">Skift privatleverings tjek af betaling</button>
                    <p style="margin-top: 10px; margin-bottom: 0px; text-align: center;">Skift opsætning på kunde eller ordre for om der skal tjekkes for betaling ved privatlevering.</p>
                </div>
            <?php } ?>

            <?php if($this->adminOrderHelper->canChangeInvoiceDate()) { ?>
                <div style="padding: 20px; border-bottom: 1px solid #CACACA;">
                    <button type="button" class="btn btn-info form-control" onClick="goToAction('invoiceoptions')">Skift acontofakturering</button>
                    <p style="margin-top: 10px; margin-bottom: 0px; text-align: center;">Skift mulighederne for acontofakturering på ordren.</p>
                </div>
            <?php } ?>


            <script>

                function goToAction(action) {
                    let url = '<?php echo \GFConfig::BACKEND_URL ?>index.php?rt=unit/cardshop/admin/'+action+'/<?php echo $this->adminOrderHelper->getOrder()->id; ?>/<?php echo $this->adminOrderHelper->getOrderHash(); ?>';
                    window.location.href = url;
                }

            </script>

        </div>

        <?php
        $content = ob_get_contents();
        ob_end_clean();

        // Output content
        $this->view("baseview", array("content" => $content,"order" => $this->adminOrderHelper->getOrder()));

    }

    public function changedebitorno($companyorderid=0,$orderhash="")
    {

        // Check order
        $this->adminOrderHelper = new AdminOrderHelper(intval($companyorderid),true,trimgf($orderhash));
        if($this->adminOrderHelper->hasError()) {
            $this->outputError($this->adminOrderHelper->getError());
        }

        if(!$this->adminOrderHelper->canChangeDebitorNo()) {
            $this->outputError("Action not allowed on this order");
        }

        // Find orders pr company
        $companyorder = \CompanyOrder::find($companyorderid);
        $company = \Company::find($companyorder->company_id);
        $companyOrderList = \CompanyOrder::find_by_sql("select * from company_order where company_id = ".$companyorder->company_id);


        $ignoreList = array();
        $reopenList = array();
        $messages = array();
        $error = "";

        foreach($companyOrderList as $co) {

            // Not synced yet
            if(in_array($co->order_state, array(0,1,2,3))) {
                $messages[] = $co->order_no.": Gør ikke noget - Ordren er ikke synkroniseret til navision endnu.";
                $ignoreList[] = $co->order_no;
            } else if(in_array($co->order_state, array(4,5,6))) {
                $messages[] = $co->order_no.": Krediter og åben på nyt kundenr - Ordren er åben i navision og skal flyttes til nyt kundenr";
                $reopenList[] = $co->order_no;
            } else if(in_array($co->order_state, array(7,9))) {
                $error = $co->order_no.": Er i gang med at blive lukket, kan ikke ændre kundenr med en igangværende handling. Prøv igen senere.";
                $messages[] = $error;
            } else if(in_array($co->order_state, array(8))) {
                $messages[] = $co->order_no.": Gør ikke noget - Ordren er lukket i navision.";
                $ignoreList[] = $co->order_no;
            } else if(in_array($co->order_state, array(10,11,12))) {
                $messages[] = $co->order_no.": Afsluttet ordre - ændre ikke kundenr";
                $ignoreList[] = $co->order_no;
            } else {
                $error = $co->order_no.": Uventet ordrestatus, kan ikke ændre kundenr. Kontakt support.";
                $messages[] = $error;
            }

        }

        // Perform action
        if($error == "" && ($_POST["action"] ?? "") == "changedebitorno") {

            $debitorno = trim($_POST["newcompanyid"] ?? "");

            // Check on nav
            try {
                $customerClient = new CustomerWS($this->adminOrderHelper->getLanguageCode());
                $customer = $customerClient->getByCustomerNo($debitorno);
            } catch (\Exception $e) {
                $error = "Kan ikke finde kundenr i navision.";
            }

            if($customer == null) {
                $error = "Kan ikke finde kundenr i navision.";
            }

            // If no error
            if($error == "") {

                $reopenHelpers = [];
                $saveMessage = "";

                // Close orders
                foreach($reopenList as $reopenOrder) {

                    $reopenHelper = new AdminOrderHelper(intval($reopenOrder->id),false);

                    $saveMessage .= "Lukker ordrenr ".$reopenOrder->order_no.". ";
                    if(!$reopenHelper->closeCompanyOrder(false)) {
                        $error .= $reopenHelper->getError();
                        $saveMessage .= "Fejlet med fejl: ".$reopenHelper->getError().". ";
                    } else {
                        $newCO = \CompanyOrder::find($reopenOrder->id);
                        if($newCO->order_state != 8) {
                            $saveMessage .= "Fejlet med fejl: kunne ikke lukkes korrekt. ";
                            $error .= $reopenOrder->order_no.": Ordren kunne ikke lukkes korrekt, prøv igen.";
                        }
                    }

                    $reopenHelpers[] = $reopenHelper;
                }

                // Change debitor no
                $saveMessage .= "Kundenr ændret fra ".$company->nav_customer_no." til ".$debitorno.". ";
                $company->nav_customer_no = $debitorno;
                $company->save();

                // Reopen orders
                foreach ($reopenHelpers as $reopenHelper) {

                    $saveMessage .= "Genåbner ordrenr ".$reopenOrder->order_no." ";
                    if(!$reopenHelper->reopenCompanyOrder(false)) {
                        $saveMessage .= "fejlet med fejlbesked ".$reopenHelper->getError();
                        $error .= $reopenHelper->getError();
                    } else {

                        $newCO = \CompanyOrder::find($reopenOrder->id);
                        $saveMessage .= "som ".$newCO->order_no.". ";
                        \ActionLog::logAction("OrderReopened", "Ordren ".$reopenHelper->getOrder()->order_no." er genåbnet på ordre nr ".$newCO->order_no,"",0,$reopenHelper->getOrder()->shop_id,$reopenHelper->getOrder()->company_id,$reopenHelper->getOrder()->id,0,0);

                    }

                }

                // Save and commit
                \ActionLog::logAction("DebitorNoChanged", "Kundenr ændret til ".$debitorno,$saveMessage,0,0,$company->id,0,0,0);
                $this->adminOrderHelper->setFrontMessage("Kundenr flyttet til: ".$debitorno." - ".$saveMessage);
                \System::connection()->commit();

                // Redirect
                header("Location: ".\GFConfig::BACKEND_URL."index.php?rt=unit/cardshop/admin/coiframe/".$companyorderid."/".$orderhash);
                exit();

            }

        }


        // Output form
        ?><form style="margin: 50px;" method="post" action="<?php echo \GFConfig::BACKEND_URL; ?>index.php?rt=unit/cardshop/admin/changedebitorno/<?php echo $companyorderid."/".$orderhash; ?>">

        <?php if($error != "") {
            echo "<div style='background: orange; color: white; text-align: center; padding: 10px; margin: 10px; margin-left: 0px; margin-right: 0px; border-radius: 8px;'>".$error."</div>";
        } ?>

        <div class="form-group">
            <p>Denne handling vil ændre virksomhedens kundenr i navision, det betyder at alle ordre der er oprettet og lagt i navision bliver krediteret på det gamle nr. og åbnet igen på det nye. nr.</p>
            <p>Du kan se herunder hvad der vil blive ændret på ordre.</p>

            <?php if(count($messages) > 0) { ?>
                <p>
                    <b>Flytning af kundenr vil have følgende konsekvenser:</b><ul>
                    <?php echo "<li>".implode("</li><li>",$messages)."</li>"; ?>
                </ul>
                </p><br>
            <?php } ?>

            <p><b>Nuværende kundenr: <?php echo $company->nav_customer_no; ?></b></p>
            <br><p>
                Angiv nyt navision kundenr: <input type="text" name="newcompanyid" value="" placeholder="Indtast nyt kundenr"> <button class="btn btn-info" type="button" onClick="checkCustomerNo()">tjek kundenr</button>
            </p>

            <div id="adminnavinfo"></div>

        </div><br>

        <input type="hidden" name="action" value="changedebitorno">
        <button type="button" onClick="window.history.back();" class="btn btn-disabled">Annuller</button>
        <button type="submit" class="btn btn-primary" id="adminsubmitbtn" disabled>Skift navision kundenr</button>

        <script>

            function checkCustomerNo() {
                $.post('<?php echo \GFConfig::BACKEND_URL; ?>index.php?rt=unit/cardshop/admin/checknavdebitorno/<?php echo $companyorderid."/".$orderhash; ?>',{debitorno: $('input[name=newcompanyid]').val()},function(data) {

                    if(data.status == 1 && typeof data.data == 'object') {
                        $('#adminsubmitbtn').prop('disabled',false);
                        $('#adminnavinfo').html("<div style='background: green; color: white; padding: 10px; margin: 10px; margin-left: 0px; margin-right: 0px; border-radius: 8px;'>Kundenr fundet:<br>"+data.data.No+" - "+data.data.Name+"<br>"+data.data.Address+"<br>"+data.data.Post_Code+" "+data.data.City+"<br>"+data.data.Contact+"<br>"+data.data.E_Mail+"<br>Du kan nu gennemføre kundenr skift.</div>");
                    } else {
                        $('#adminsubmitbtn').prop('disabled',true);
                        $('#adminnavinfo').html("<div style='background: orange; color: white; padding: 10px; margin: 10px; margin-left: 0px; margin-right: 0px; border-radius: 8px;'>"+data.error+"</div>");
                    }

                    console.log(data);
                },'json');
            }

        </script>

        </form><?php

        $this->endOutput();

    }

    public function checknavdebitorno($companyorderid=0,$orderhash="") {

        // Check order
        $this->adminOrderHelper = new AdminOrderHelper(intval($companyorderid),true,trimgf($orderhash));
        if($this->adminOrderHelper->hasError()) {
            $this->outputServiceError($this->adminOrderHelper->getError());
            return;
        }

        // Get navision no
        $debitorno = trim($_POST["debitorno"] ?? "");
        if($debitorno == "") {
            $this->outputServiceError("Intet kundenr angivet");
            return;
        }

        // Check company
        $company = \Company::find($this->adminOrderHelper->getOrder()->company_id);

        if($company->nav_customer_no == $debitorno) {
            $this->outputServiceError("Kundenr er allerede det samme som det nuværende.");
            return;
        }

        // Check on nav
        try {
            $customerClient = new CustomerWS($this->adminOrderHelper->getLanguageCode());
            $customer = $customerClient->getByCustomerNo($debitorno);
        } catch (\Exception $e) {
            $this->outputServiceError("Kan ikke finde kundenr i navision.");
            return;
        }

        if($customer == null) {
            $this->outputServiceError("Kan ikke finde kundenr i navision.");
            return;
        }

        $this->outputServiceSuccess($customer->getDataArray());

    }

    private function outputServiceError($errorCode)
    {
        $data = array("status" => 0,"error" => $errorCode);
        echo json_encode($data);
    }

    private function outputServiceSuccess($data) {
        $data = array("status" => 1,"data" => $data);
        echo json_encode($data);
    }

    public function movecompany($companyorderid=0,$orderhash="")
    {

        // Check order
        $this->adminOrderHelper = new AdminOrderHelper(intval($companyorderid),true,trimgf($orderhash));
        if($this->adminOrderHelper->hasError()) {
            $this->outputError($this->adminOrderHelper->getError());
        }

        if(!$this->adminOrderHelper->canMoveCompany()) {
            $this->outputError("Action not allowed on this order");
        }

        // Find orders pr company
        $error = "";

        if(($_POST["action"] ?? "") == "movecompany") {

            $movetocompanyid = intvalgf($_POST["movecompanyid"] ?? 0);
            
            if($movetocompanyid <= 0) {
                $error = "Der er ikke angivet en virksomhed som ordren skal flyttes til.";
            } else if($movetocompanyid == $this->adminOrderHelper->getOrder()->company_id) {
                $error = "Ordren er allerede på den valgte virksomhed.";
            } else {

                $newCompany = \Company::find($movetocompanyid);

                // Check if company is valid
                if($newCompany == null) {
                    $error = "Kan ikke finde virksomheden der skal flyttes til.";
                } else {

                    // Move order
                    try {

                        if(!$this->adminOrderHelper->moveCompanyOrder($newCompany)) {
                            $error = $this->adminOrderHelper->getError();
                        } else {

                            $newCO = \CompanyOrder::find($companyorderid);

                            $this->adminOrderHelper->setFrontMessage("Ordre ".$newCO->order_no." er flyttet til ".$newCompany->name);
                            \ActionLog::logAction("OrderMoved", "Ordren ".$newCO->order_no." er flyttet til ny virksomhedi cardshop: ".$newCompany->name,"",0,$this->adminOrderHelper->getOrder()->shop_id,$this->adminOrderHelper->getOrder()->company_id,$this->adminOrderHelper->getOrder()->id,0,0);
                            \System::connection()->commit();

                            // Redirect
                            header("Location: ".\GFConfig::BACKEND_URL."index.php?rt=unit/cardshop/admin/coiframe/".$companyorderid."/".$orderhash);
                            exit();

                        }

                    }
                    catch (\Exception $e) {
                        $error = "Der opstod en fejl under flytningen: ".$e->getMessage();
                    }
                    
                }
                
            }

        }

        // Generate content
        ob_start();

        // Output form
        ?>

        <?php if($error != "") {
        echo "<div style='background: orange; color: white; text-align: center; padding: 10px; margin: 10px; margin-left: 0px; margin-right: 0px; border-radius: 8px;'>".$error."</div>";
    } ?>

        <div class="form-group">
            <p>Denne handling vil flytte ordren fra den nuværende kunde i cardshop til en anden kunde i cardshop. Bemærk at alle kort på ordren, inkl. kort der pt. er fordelt på under-adresser (childs) vil blive flyttet til den valgte virksomhed.</p>
            <?php

                if(in_array($this->adminOrderHelper->getOrder()->order_no, array(4,5))) {
                    ?><p>Ordren er aktiv i navision og vil blive krediteret på den nuværende kunde og åbnet på et nyt BS nr på den nye virksomhed, med mindre den har samme navision kundenr.</p><?php
                } else {
                    ?><p>
                        Ordren er ikke aktiv i navision og vil blive flyttet til den nye virksomhed uden kreditering.
                    </p><?php
                }

            ?>
            <p>
                Søg efter virksomheden der skal flyttes til: <input type="text" name="movecompanyquery" value="" placeholder="Søg efter virksomhed"> <button class="btn btn-info" type="button" onClick="moveCompanySearch()">søg</button>
            </p>

            <form style="margin: 50px;" method="post" action="<?php echo \GFConfig::BACKEND_URL; ?>index.php?rt=unit/cardshop/admin/movecompany/<?php echo $companyorderid."/".$orderhash; ?>">
            <table class="companyresulttable table table-borderet table-striped" style="display: none; width: 100%; font-size: 11px;">
                <thead>
                <tr>
                    <th>Navn</th>
                    <th>ID</th>
                    <th>Adresse</th>
                    <th>Levering</th>
                    <th>Kontakt</th>
                </tr>
                </thead>
            </table>

                <input type="hidden" name="action" value="movecompany">
                <button type="button" onClick="window.history.back();" class="btn btn-disabled">Annuller</button>
                <button type="submit" class="btn btn-primary" id="movesubmit" disabled>Flyt ordre til valgte kunde</button>

            </form>

        </div><br>




        <script>


            function moveCompanySearch() {

                $('#movesubmit').prop('disabled',true);
                $('.companyresulttable').show();
                $('.companyresulttable tbody').remove();
                $('.companyresulttable').append(' <tbody id="adminmovecompanylistbody" style="height: 300px; overflow: auto;"> </tbody>')


                $('#adminsubmitbtn').prop('disabled',true);
                $('#adminmovecompanylistbody').html('<tr><td colspan="5" style="text-align: center;">Søger...</td></tr>');
                $.post('<?php echo \GFConfig::BACKEND_URL; ?>index.php?rt=unit/cardshop/admin/querycompany/<?php echo $companyorderid."/".$orderhash; ?>',{query: $('input[name=movecompanyquery]').val()},function(data) {

                    if(data.status == 1 && typeof data.data == 'object') {

                        if(data.data.length == 0) {
                            $('#adminmovecompanylistbody').html('<tr><td colspan="5" style="text-align: center;">Fandt ingen resultater...</td></tr>');
                        }
                        else {

                            let html = "";
                            for(let i = 0; i < data.data.length; i++) {

                                html += "<tr>";
                                html += "<td><label><input type='radio' name='movecompanyid' value='"+data.data[i].id+"'>"+data.data[i].name+"</label></td>";
                                html += "<td>CVR: "+data.data[i].cvr+"<br>EAN: "+data.data[i].ean+"<br>Debitor no: "+data.data[i].navision+"<br>CS ID: "+data.data[i].id+"</td>";
                                html += "<td>"+data.data[i].address+"<br>"+data.data[i].address2+"<br>"+data.data[i].postal+" "+data.data[i].city+"</td>";
                                html += "<td>"+data.data[i].ship_to_company+"<br>"+data.data[i].ship_to_address+"<br>"+data.data[i].ship_to_postal+" "+data.data[i].ship_to_city+"</td>";
                                html += "<td>"+data.data[i].contact+"<br>"+data.data[i].email+"<br>"+data.data[i].phone+"</td>";
                                html += "</tr>";

                            }

                            $('#adminmovecompanylistbody').html(html);

                            $('input[name=movecompanyid]').change(function() {
                                $('#movesubmit').prop('disabled',false);
                            });

                        }



                    } else {
                        $('#adminmovecompanylistbody').html('<tr><td colspan="5" style="text-align: center;">Fejl: '+data.error+'</td></tr>');
                    }
                },'json');
            }

            document.getElementById('movecompanyquery').addEventListener('keypress', function(event) {
                if (event.key === "Enter") {
                    event.preventDefault();
                    moveCompanySearch();
                }
            });

        </script>

        <?php

        $this->endOutput();

    }

    public function querycompany($companyorderid=0,$orderhash="") {

        // Check order
        $this->adminOrderHelper = new AdminOrderHelper(intval($companyorderid),true,trimgf($orderhash));
        if($this->adminOrderHelper->hasError()) {
            $this->outputServiceError($this->adminOrderHelper->getError());
            return;
        }

        // Query
        $query = trim($_POST["query"] ?? "");
        if($query == "") {
            $this->outputServiceError("Intet søgeord angivet");
            return;
        }

        // Check company
        $company = \Company::find($this->adminOrderHelper->getOrder()->company_id);

        if($company->language_code == 0) {
            $this->outputServiceError("Virksomheden har ikke et sprog tilknyttet, kontakt support.");
            return;
        }

        // Search company
        $searchFields = array("name","cvr","ean","bill_to_address","bill_to_address_2","bill_to_postal_code","bill_to_city","bill_to_email","ship_to_company","ship_to_attention","ship_to_address","ship_to_address_2","ship_to_postal_code","ship_to_city","contact_name","contact_phone","contact_email");
        $searchQuery = [];
        foreach($searchFields as $field) {
            $searchQuery[] = $field." LIKE '%".addslashes($query)."%'";
        }

        $sql = "SELECT * FROM company WHERE id != ".$company->id." && language_code = ".$company->language_code." && (".implode(" OR ",$searchQuery).") ORDER BY name ASC LIMIT 0,10";
        $companyList = \Company::find_by_sql($sql);
        $searchList = array();

        foreach($companyList as $company) {
            $searchList[] = array(
                "id" => $company->id,
                "name" => $company->name,
                "cvr" => $company->cvr,
                "ean" => $company->ean,
                "address" => $company->bill_to_address,
                "address2" => $company->bill_to_address_2,
                "postal" => $company->bill_to_postal_code,
                "city" => $company->bill_to_city,
                "contact" => $company->contact_name,
                "billemail" => $company->bill_to_email,
                "email" => $company->contact_email,
                "contact" => $company->contact_name,
                "phone" => $company->contact_phone,
                "navision" => $company->nav_customer_no,
                "ship_to_company" => $company->ship_to_company,
                "ship_to_address" => $company->ship_to_address,
                "ship_to_address2" => $company->ship_to_address_2,
                "ship_to_postal" => $company->ship_to_postal_code,
                "ship_to_city" => $company->ship_to_city

            );
        }

        $this->outputServiceSuccess($searchList);

    }

    public function closeorder($companyorderid=0,$orderhash="")
    {

        // Check order
        $this->adminOrderHelper = new AdminOrderHelper(intval($companyorderid),true,trimgf($orderhash));
        if($this->adminOrderHelper->hasError()) {
            $this->outputError($this->adminOrderHelper->getError());
        }

        if(!$this->adminOrderHelper->canRemoveOrder()) {
            $this->outputError("Action not allowed on this order");
        }


        // Ouput page
        $this->startOutput();

        $error = "";
        if(($_POST["action"] ?? "") == "closeorder") {

            if($this->adminOrderHelper->closeCompanyOrder()) {

                $this->adminOrderHelper->setFrontMessage("Ordren er lukket.");

                \ActionLog::logAction("OrderClosed", "Ordren er lukket og krediteret.","",0,$this->adminOrderHelper->getOrder()->shop_id,$this->adminOrderHelper->getOrder()->company_id,$this->adminOrderHelper->getOrder()->id,0,0);

                \System::connection()->commit();

                // Redirect
                header("Location: ".\GFConfig::BACKEND_URL."index.php?rt=unit/cardshop/admin/coiframe/".$companyorderid."/".$orderhash);
                exit();

            } else {
                
                $error = $this->adminOrderHelper->getError();
            }

        }

        // Output form
        ?><form style="margin: 50px;" method="post" action="<?php echo \GFConfig::BACKEND_URL; ?>index.php?rt=unit/cardshop/admin/closeorder/<?php echo $companyorderid."/".$orderhash; ?>">

        <?php if($error != "") {
        echo "<div style='background: orange; color: white; text-align: center; padding: 10px; margin: 10px; margin-left: 0px; margin-right: 0px; border-radius: 8px;'>".$error."</div>";
        } ?>

        <div class="form-group">
            <p>Denne handling vil nedlægge ordren og er den sendt til navision krediteres og lukkes den der. Alle gavekort vil blive lukket.</p>
        </div><br>

        <input type="hidden" name="action" value="closeorder">
        <button type="button" onClick="window.history.back();" class="btn btn-disabled">Annuller</button>
        <button type="submit" class="btn btn-primary">Luk / krediter ordren</button>

        </form><?php

        $this->endOutput();

    }

    public function sendorderconf($companyorderid=0,$orderhash="")
    {
        // Check order
        $this->adminOrderHelper = new AdminOrderHelper(intval($companyorderid),true,trimgf($orderhash));
        if($this->adminOrderHelper->hasError()) {
            $this->outputError($this->adminOrderHelper->getError());
        }

        if(!$this->adminOrderHelper->canSendOrderConfirmation()) {
            $this->outputError("Action not allowed on this order");
        }

        // Ouput page
        $this->startOutput();

        $error = "";
        if(($_POST["action"] ?? "") == "sendorderconf") {

            $acceptorderconf = intvalgf($_POST["acceptorderconf"] ?? 0);

            if($acceptorderconf != 1) {

                $error = "Accepter at du vil sende en ny ordrebekræftelse.";

            } else {

                $order = \CompanyOrder::find($this->adminOrderHelper->getOrder()->id);
                $order->nav_synced = 0;
                $order->force_orderconf = 1;
                $order->save();

                $this->adminOrderHelper->setFrontMessage("Ordrebekræftelse er nu sat til at blive sendt til kunden.");

                \ActionLog::logAction("SendOrderConf", "Send ny ordrebekræftelse til kunde igangsat.","",0,$this->adminOrderHelper->getOrder()->shop_id,$this->adminOrderHelper->getOrder()->company_id,$this->adminOrderHelper->getOrder()->id,0,0);

                \System::connection()->commit();

                // Redirect
                header("Location: ".\GFConfig::BACKEND_URL."index.php?rt=unit/cardshop/admin/coiframe/".$companyorderid."/".$orderhash);
                exit();

            }

        }

        // Output form
        ?><form style="margin: 50px;" method="post" action="<?php echo \GFConfig::BACKEND_URL; ?>index.php?rt=unit/cardshop/admin/sendorderconf/<?php echo $companyorderid."/".$orderhash; ?>">

        <?php if($error != "") {
            echo "<div style='background: orange; color: white; text-align: center; padding: 10px; margin: 10px; margin-left: 0px; margin-right: 0px; border-radius: 8px;'>".$error."</div>";
        } ?>

        <div class="form-group">
            <p>Ved at udføre denne handling gives der besked til navision om at sende en ny ordrebekræftelse til kunden.</p>
            <p><label><input type="checkbox" name="acceptorderconf" value="1"> Send ny ordrebekræftelse</label></p>
            <p>Der vil gå omkring 10 minutter til ordrebekræftelsen normalt sendes ud.</p>
        </div>

        <input type="hidden" name="action" value="sendorderconf">
        <button type="button" onClick="window.history.back();" class="btn btn-disabled">Annuller</button>
        <button type="submit" class="btn btn-primary">Send ordrebekræftelse</button>

        </form><?php

        $this->endOutput();

    }

    public function reopenorder($companyorderid=0,$orderhash="")
    {

        // Check order
        $this->adminOrderHelper = new AdminOrderHelper(intval($companyorderid),true,trimgf($orderhash));
        if($this->adminOrderHelper->hasError()) {
            $this->outputError($this->adminOrderHelper->getError());
        }

        if(!$this->adminOrderHelper->canReopenOrder()) {
            $this->outputError("Action not allowed on this order");
        }


        // Ouput page
        $this->startOutput();

        $error = "";
        if(($_POST["action"] ?? "") == "reopenorder") {

            $oldOrderNo = $this->adminOrderHelper->getOrder()->order_no;

            // Close the order
            if($this->adminOrderHelper->getOrder()->order_state != 8) {

                if(!$this->adminOrderHelper->closeCompanyOrder(false)) {
                    $error = $this->adminOrderHelper->getError();
                } else {
                    $newCO = \CompanyOrder::find($companyorderid);
                    if($newCO->order_state != 8) {
                        $this->error = "Ordren kunne ikke lukkes korrekt, prøv igen.";
                    }
                }
            }
            
            // No error yet, go through with reopening
            if($error == "") {


                if(!$this->adminOrderHelper->reopenCompanyOrder(false)) {
                    $error = $this->adminOrderHelper->getError();
                } else {

                    $newCO = \CompanyOrder::find($companyorderid);

                    $this->adminOrderHelper->setFrontMessage("Ordren er genåbnet med ordre nr ".$newCO->order_no);
                    \ActionLog::logAction("OrderReopened", "Ordren ".$oldOrderNo." er genåbnet på ordre nr ".$newCO->order_no,"",0,$this->adminOrderHelper->getOrder()->shop_id,$this->adminOrderHelper->getOrder()->company_id,$this->adminOrderHelper->getOrder()->id,0,0);
                    \System::connection()->commit();

                    // Redirect
                    header("Location: ".\GFConfig::BACKEND_URL."index.php?rt=unit/cardshop/admin/coiframe/".$companyorderid."/".$orderhash);
                    exit();

                }

            }

        }

        // Output form
        ?><form style="margin: 50px;" method="post" action="<?php echo \GFConfig::BACKEND_URL; ?>index.php?rt=unit/cardshop/admin/reopenorder/<?php echo $companyorderid."/".$orderhash; ?>">

        <?php if($error != "") {
        echo "<div style='background: orange; color: white; text-align: center; padding: 10px; margin: 10px; margin-left: 0px; margin-right: 0px; border-radius: 8px;'>".$error."</div>";
    } ?>

        <div class="form-group">
            <p>Denne handling vil genåbne ordren på et nyt BS nr. Er ordren allerede åben vil den blive lukket/krediteret og derefter åbnet igen. Ordren vil fortsat være på samme kunde / kundenr.</p>
            <p>Bemærk at der oprettes en fejlbesked efter genåbning som skal godkendes før den nye ordre kan sendes i navision.</p>
        </div><br>

        <input type="hidden" name="action" value="reopenorder">
        <button type="button" onClick="window.history.back();" class="btn btn-disabled">Annuller</button>
        <button type="submit" class="btn btn-primary">Genåben ordre</button>

        </form><?php

        $this->endOutput();

    }

    public function changesalesperson($companyorderid=0,$orderhash="")
    {

        // Check order
        $this->adminOrderHelper = new AdminOrderHelper(intval($companyorderid),true,trimgf($orderhash));
        if($this->adminOrderHelper->hasError()) {
            $this->outputError($this->adminOrderHelper->getError());
        }

        if(!$this->adminOrderHelper->canChangeSalesPerson()) {
            $this->outputError("Action not allowed on this order");
        }

    
        $salesPersonCode = $this->adminOrderHelper->getOrder()->salesperson;
        $salesPersonName = "Ukendt";
        $salespersonWS = new \GFCommon\Model\Navision\SalesPersonWS($this->adminOrderHelper->getLanguageCode());

        // Load salespersons
        try {


            $list = $salespersonWS->getAllSalesPerson();

            $options = "";
            foreach($list as $salesPerson) {
                $options .= "<option value='".$salesPerson->getCode()."' ".(trimgf(strtolower($salesPersonCode)) == trimgf(strtolower($salesPerson->getCode())) ? "selected" : "").">".$salesPerson->getCode().": ".$salesPerson->getName()."</option>";

                if(strtolower(trimgf($salesPersonCode)) == strtolower(trimgf($salesPerson->getCode()))) {
                    $salesPersonName = $salesPerson->getName();
                }

            }

        }
        catch (\Exception $e) {
            $this->outputError("Kan ikke hente sælgerlisten fra navision, prøv igen senere:<br>".$e->getMessage()." (".$e->getFile()." - ".$e->getLine().")");
        }


        // Ouput page
        $this->startOutput();

        $error = "";
        if(($_POST["action"] ?? "") == "changesalesperson") {

            $newsalesperson = $_POST["salespersonnew"] ?? "";
            $newsalespersonValid = false;

            foreach($list as $salesPerson) {

                if(strtolower(trimgf($newsalesperson)) == strtolower(trimgf($salesPerson->getCode()))) {
                    $salesPersonName = $salesPerson->getName();
                    $newsalespersonValid = true;
                }

            }

            if($newsalesperson == "") {
                $error = "Der er ikke angivet en ny sælger";
            } else if (!$newsalespersonValid) {
                $error = "Den angivne sælger er ikke gyldig";
            } else if(trimgf(strtolower($newsalesperson)) == trimgf(strtolower($salesPersonCode))) {
                $error = "Der er ikke angivet en anden sælger end den nuværende";
            } else {

                $order = \CompanyOrder::find($this->adminOrderHelper->getOrder()->id);
                $order->salesperson = $newsalesperson;
                $order->nav_synced = 0;
                $order->save();

                $this->adminOrderHelper->setFrontMessage("Sælgeren på ordren er nu ændret til ".$newsalesperson.": ".$salesPersonName);

                \ActionLog::logAction("AdminChangeSalesperson", "Sælger ændret på ".$this->adminOrderHelper->getOrder()->order_no." fra ".$salesPersonCode." til ".$newsalesperson,"",0,$this->adminOrderHelper->getOrder()->shop_id,$this->adminOrderHelper->getOrder()->company_id,$this->adminOrderHelper->getOrder()->id,0,0);

                \System::connection()->commit();

                // Redirect
                header("Location: ".\GFConfig::BACKEND_URL."index.php?rt=unit/cardshop/admin/coiframe/".$companyorderid."/".$orderhash);
                exit();
            }


        }


        ?><form style="margin: 50px;" method="post" action="<?php echo \GFConfig::BACKEND_URL; ?>index.php?rt=unit/cardshop/admin/changesalesperson/<?php echo $companyorderid."/".$orderhash; ?>">

        <?php if($error != "") {
            echo "<div style='background: orange; color: white; text-align: center; padding: 10px; margin: 10px; margin-left: 0px; margin-right: 0px; border-radius: 8px;'>".$error."</div>";
        } ?>


        <div class="form-group">
            <label for="salespersoncurrent"><b>Nuværende sælger:</b></label><br>
            <?php echo $salesPersonCode.": ".$salesPersonName; ?>
        </div>
        <div class="form-group">
            <label for="salespersonnew"><b>Vælg ny sælger:</b></label><br>
            <select name="salespersonnew">
                <?php echo $options; ?>
            </select>
        </div>

        <input type="hidden" name="action" value="changesalesperson">
        <button type="button" onClick="window.history.back();" class="btn btn-disabled">Annuller</button>
        <button type="submit" class="btn btn-primary">Gem</button>

        </form><?php

        $this->endOutput();

    }

    public function invoiceoptions($companyorderid=0,$orderhash="")
    {

        // Check order
        $this->adminOrderHelper = new AdminOrderHelper(intval($companyorderid),true,trimgf($orderhash));
        if($this->adminOrderHelper->hasError()) {
            $this->outputError($this->adminOrderHelper->getError());
        }

        if(!$this->adminOrderHelper->canChangeInvoiceDate()) {
            $this->outputError("Action not allowed on this order");
        }

        // Load order and company
        $order = \CompanyOrder::find($this->adminOrderHelper->getOrder()->id);
        $company = \Company::find($order->company_id);

        $error = "";
        if(($_POST["action"] ?? "") == "invoiceoptions") {




            if(!isset($_POST["prepayment"])) {
                $error = "Der er ikke valgt en indstilling for aconto fakturering";
            } else if(intvalgf($_POST["prepayment"]) == 0) {

                if($order->prepayment == 1) {

                    // Update order
                    $order->prepayment = 0;
                    $order->save();

                    $this->adminOrderHelper->setFrontMessage("Acontofakturering er slået fra på ordren.");

                    \ActionLog::logAction("AdminChangeAcontoOptions", "Acontofakturering er slået fra på ordren " . $this->adminOrderHelper->getOrder()->order_no, '', 0, $this->adminOrderHelper->getOrder()->shop_id, $this->adminOrderHelper->getOrder()->company_id, $this->adminOrderHelper->getOrder()->id, 0, 0);
                    \System::connection()->commit();

                }

                // Redirect
                header("Location: " . \GFConfig::BACKEND_URL . "index.php?rt=unit/cardshop/admin/coiframe/" . $companyorderid . "/" . $orderhash);
                exit();


            } else if(intvalgf($_POST["prepayment"]) == 1) {

                if($order->prepayment == 0) {

                    // Update order
                    $order->prepayment = 1;

                    // Add action
                    $this->adminOrderHelper->setFrontMessage("Acontofakturering er slået til på ordren.");
                    \ActionLog::logAction("AdminChangeAcontoOptions", "Acontofakturering er slået til på ordren " . $this->adminOrderHelper->getOrder()->order_no, '', 0, $this->adminOrderHelper->getOrder()->shop_id, $this->adminOrderHelper->getOrder()->company_id, $this->adminOrderHelper->getOrder()->id, 0, 0);

                }

                $prepaymentDateTime = null;
                $prepaymentDueDateTime = null;

                if(isset($_POST["prepaymentdate"])) {

                    $prepaymentDate = trimgf($_POST["prepaymentdate"]);
                    if($prepaymentDate != "" && $prepaymentDate != "0000-00-00") {

                        $prepaymentDateTime = new \DateTime($prepaymentDate);

                        // Detect problem in date
                        if($prepaymentDateTime->format('Y-m-d') != $prepaymentDate) {
                            throw new \Exception("Invalid prepayment date");
                        }

                        $prepaymentDateTime->setTime(0,0,0);

                        // Get unixtime for date
                        $prepaymentTime = $prepaymentDateTime->getTimestamp();
                        if($prepaymentTime < (time()-60*60*24*365*2)) {
                            throw new \Exception("Prepayment date is too old");
                        }
                        if($prepaymentTime > (time()+60*60*24*365*1)) {
                            throw new \Exception("Prepayment date too far in the future");
                        }

                    }

                }

                // If prepayment_due_date set
                if(isset($_POST["prepaymentduedate"])) {

                    $prepaymentDueDate = trimgf($_POST["prepaymentduedate"]);
                    if($prepaymentDueDate != "" && $prepaymentDueDate != "0000-00-00") {

                        $prepaymentDueDateTime = new \DateTime($prepaymentDueDate);

                        // Detect problem in date
                        if($prepaymentDueDateTime->format('Y-m-d') != $prepaymentDueDate) {
                            throw new \Exception("Invalid prepayment date");
                        }

                        $prepaymentDueDateTime->setTime(0,0,0);

                        // If $prepaymentDateTime is  set, throw exception if not duedate is after $prepaymentDateTime
                        if($prepaymentDateTime != null && $prepaymentDateTime > $prepaymentDueDateTime) {
                            throw new \Exception("Prepayment date is after prepayment due date");
                        }

                        // Get unixtime for duedate
                        $dueDateTime = $prepaymentDueDateTime->getTimestamp();
                        if($dueDateTime < (time()-60*60*24*365*2)) {
                            throw new \Exception("Prepayment due date is too old");
                        }
                        if($dueDateTime > (time()+60*60*24*365*1)) {
                            throw new \Exception("Prepayment due too far in the future");
                        }

                    }

                }

                // Log date changes
                if($order->prepayment_date != $prepaymentDateTime || $order->prepayment_duedate != $prepaymentDateTime) {
                    \ActionLog::logAction("AdminChangeAcontoOptions", "Faktura for acontofaktura er ændret", 'Aconto faktura dato ændret fra '.($order->prepayment_date == null ? "Ingen dato": $order->prepayment_date->format('Y-m-d'))." til ".($prepaymentDateTime == null ? "Ingen dato": $prepaymentDateTime->format('Y-m-d')).". Betalingsfrist ændret fra ".($order->prepayment_duedate == null ? "Ingen dato": $order->prepayment_duedate->format('Y-m-d'))." til ".($prepaymentDateTime == null ? "Ingen dato": $prepaymentDateTime->format('Y-m-d')), 0, $this->adminOrderHelper->getOrder()->shop_id, $this->adminOrderHelper->getOrder()->company_id, $this->adminOrderHelper->getOrder()->id, 0, 0);
                }

                // Set dates
                $order->prepayment_date = $prepaymentDateTime;
                $order->prepayment_duedate = $prepaymentDueDateTime;

                // Save order
                $order->save();
                \System::connection()->commit();

                // Redirect
                header("Location: " . \GFConfig::BACKEND_URL . "index.php?rt=unit/cardshop/admin/coiframe/" . $companyorderid . "/" . $orderhash);
                exit();


            } else {
                $error = "Der er ikke valgt en indstilling for aconto fakturering";
            }


        }

        // Ouput page
        $this->startOutput();

        ?><form style="margin: 50px;" method="post" action="<?php echo \GFConfig::BACKEND_URL; ?>index.php?rt=unit/cardshop/admin/invoiceoptions/<?php echo $companyorderid."/".$orderhash; ?>">

        <?php  if($error != "") {
        echo "<div style='background: orange; color: white; text-align: center; padding: 10px; margin: 10px; margin-left: 0px; margin-right: 0px; border-radius: 8px;'>".$error."</div>";
    } ?>

        <p>
            Du kan her skifte indstillinger på forudfakturering af ordren. Bemærk at ændringer der laves her i admin opsætningen ikke bliver sendt til godkendelse ved økonomi.
        </p>

        <p>
            <label><input type="radio" name="prepayment" onChange="updatePrepayCheck()" value="0" <?php if($order->prepayment == 0) echo "checked"; ?>> Benyt ikke aconto fakturering</label><br>
            <label><input type="radio" name="prepayment" value="1" onChange="updatePrepayCheck()" <?php if($order->prepayment == 1) echo "checked"; ?>> Benyt aconto fakturering</label>
        </p>

        <div class="acontosettings">
            <p>
            Fakturer d. <input type="date" name="prepaymentdate" value="<?php if($order->prepayment_date != null) echo $order->prepayment_date->format('Y-m-d'); ?>" style=""><br>
            Betalingsfrist d. <input type="date" name="prepaymentduedate" value="<?php if($order->prepayment_duedate != null) echo $order->prepayment_duedate->format('Y-m-d'); ?>" style="">
            </p>
            <p>
                Angives der ikke datoer vil standard benyttes hvilket er acontofaktureringen sker med det samme ordren oprettes i nav og betalingsfristen er standard.
            </p>
        </div>


        <input type="hidden" name="action" value="invoiceoptions">
        <button type="button" onClick="window.history.back();" class="btn btn-disabled">Annuller</button>
        <button type="submit" class="btn btn-primary">Gem</button>

        </form><script>

            function updatePrepayCheck()
            {
                // check if prepayment = 1 checked and toggle .acontosettings
                if($('input[name=prepayment]:checked').val() == 1) {
                    $('.acontosettings').show();
                } else {
                    $('.acontosettings').hide();
                }
            }

            updatePrepayCheck();

    </script><?php

        $this->endOutput();


    }

    public function deliveryoptions($companyorderid=0,$orderhash="")
    {

        // Check order
        $this->adminOrderHelper = new AdminOrderHelper(intval($companyorderid),true,trimgf($orderhash));
        if($this->adminOrderHelper->hasError()) {
            $this->outputError($this->adminOrderHelper->getError());
        }

        if(!$this->adminOrderHelper->canChangeDeliverySettings()) {
            $this->outputError("Action not allowed on this order");
        }

        // Load order and company
        $order = \CompanyOrder::find($this->adminOrderHelper->getOrder()->id);
        $company = \Company::find($order->company_id);


        $error = "";
        if(($_POST["action"] ?? "") == "deliveryoptions") {

            $company_allow_delivery = intvalgf($_POST["company_allow_delivery"] ?? 100);
            $order_allow_delivery = intvalgf($_POST["order_allow_delivery"] ?? 100);

            $optionText = array(
                    -1 => "Send ikke privatleveringer",
                    0 => "Standard: Tjek for om ordre er betalt før der leveres",
                    1 => "Send privatleveringer uden betalingstjek"
            );

            if (!in_array($company_allow_delivery, array(-1, 0, 1))) {
                $error = "Ugyldig værdi for regel på kunde";
            } else if (!in_array($order_allow_delivery, array(-1, 0, 1))) {
                $error = "Ugyldig værdi for regel på ordren";
            } else {

                $company->allow_delivery = $company_allow_delivery;
                $company->save();

                $order->allow_delivery = $order_allow_delivery;
                $order->save();

                $this->adminOrderHelper->setFrontMessage("Privatleverings tjek af betaling er nu ændret.");

                \ActionLog::logAction("AdminChangeDeliveryOptions", "Privatleverings tjek af betaling ændret på " . $this->adminOrderHelper->getOrder()->order_no, "Regel for kunden: ".$optionText[$company->allow_delivery].". Regel for ordren: ".$optionText[$order->allow_delivery].".", 0, $this->adminOrderHelper->getOrder()->shop_id, $this->adminOrderHelper->getOrder()->company_id, $this->adminOrderHelper->getOrder()->id, 0, 0);

                \System::connection()->commit();

                // Redirect
                header("Location: " . \GFConfig::BACKEND_URL . "index.php?rt=unit/cardshop/admin/coiframe/" . $companyorderid . "/" . $orderhash);
                exit();
            }

        }

        // Ouput page
        $this->startOutput();

        ?><form style="margin: 50px;" method="post" action="<?php echo \GFConfig::BACKEND_URL; ?>index.php?rt=unit/cardshop/admin/deliveryoptions/<?php echo $companyorderid."/".$orderhash; ?>">

        <?php if($error != "") {
            echo "<div style='background: orange; color: white; text-align: center; padding: 10px; margin: 10px; margin-left: 0px; margin-right: 0px; border-radius: 8px;'>".$error."</div>";
        } ?>

        <p>
            Du kan her angive status på betalingtjek ved privatlevering. Bemærk at der kan åbnes og lukkes for tjek på kunden eller på ordren.
        </p>
        <p>
            Sættes der en regel på ordren er det den der bliver brugt på den pågældende ordre. Sættes der en regel på kunden er det den der bliver brugt på alle ordre for kunden, hvor der ikke er angivet andet på ordren.
        </p>
        <p>
            Vælger man ikke at sende privatleveringer er det enten fordi der er et midlertidigt problem ift. kunden, eller fordi der er en aftale om at de trækkes ud og sendes manuelt og systemet derfor ikke skal frigive dem.
        </p>

        <p>
            <b>Regel på kunde</b><br>
            <label><input type="radio" name="company_allow_delivery" value="0" <?php if($company->allow_delivery == 0) echo "checked"; ?>> Standard: Tjek for om ordre er betalt før der leveres</label><br>
            <label><input type="radio" name="company_allow_delivery" value="1" <?php if($company->allow_delivery == 1) echo "checked"; ?>> Send privatleveringer uden at tjekke betalingsstatus på ordren.</label><br>
            <label><input type="radio" name="company_allow_delivery" value="-1" <?php if($company->allow_delivery == -1) echo "checked"; ?>> Send ikke privatleveringer, heller ikke selvom der er betalt.</label><br>
        </p>

        <p>
            <b>Regel på ordren <?php echo $this->adminOrderHelper->getOrder()->order_no; ?></b><br>
            <label><input type="radio" name="order_allow_delivery" value="0" <?php if($order->allow_delivery == 0) echo "checked"; ?>> Standard: Tjek for om ordre <?php echo $this->adminOrderHelper->getOrder()->order_no; ?> er betalt før der leveres</label><br>
            <label><input type="radio" name="order_allow_delivery" value="1" <?php if($order->allow_delivery == 1) echo "checked"; ?>> Send privatleveringer uden at tjekke betalingsstatus på ordre <?php echo $this->adminOrderHelper->getOrder()->order_no; ?>.</label><br>
            <label><input type="radio" name="order_allow_delivery" value="-1" <?php if($order->allow_delivery == -1) echo "checked"; ?>> Send ikke privatleveringer, heller ikke selvom der er betalt for <?php echo $this->adminOrderHelper->getOrder()->order_no; ?>.</label><br>
        </p>

        <p>Vælges standard på ordren vil kunde-reglen blive brugt.</p><br>

        <input type="hidden" name="action" value="deliveryoptions">
        <button type="button" onClick="window.history.back();" class="btn btn-disabled">Annuller</button>
        <button type="submit" class="btn btn-primary">Gem</button>

        </form><?php

        $this->endOutput();

    }

    

    /**
     * OUTPUT HELPERS
     */

    private $outputStarted = false;
    
    private function startOutput() {
        
        $this->outputStarted = true;
        ob_start();
    }
    
    private function endOutput() {
        $this->outputStarted = false;
        $content = ob_get_contents();
        ob_end_clean();

        // Output content
        $this->view("baseview", array("content" => $content,"order" => $this->adminOrderHelper->getOrder()));

    }
    
    private function outputError($errorMessage) {

        if($this->outputStarted) {
            ob_end_clean();
            $this->outputStarted = false;
        }
        
        // Create and output simple error page
        ?><html>
            <head>
                <title>Error</title>
                <style>
                    body, html {
                        height: 100%;
                        margin: 0;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        background-color: #f8f9fa;
                    }
                    .error-container {
                        text-align: center;
                        padding: 20px;
                        border: 1px solid #dee2e6;
                        border-radius: 5px;
                        background-color: #ffffff;
                        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                    }
                    .error-title {
                        font-size: 2rem;
                        color: #dc3545;
                    }
                    .error-message {
                        font-size: 1rem;
                        color: #6c757d;
                    }
                </style>
            </head>
            <body>
                <div class="error-container">
                    <h1 class="error-title">Error</h1>
                    <p class="error-message"><?php echo $errorMessage; ?></p>
                </div>
            </body>
        </html><?php
        exit();

    }

}