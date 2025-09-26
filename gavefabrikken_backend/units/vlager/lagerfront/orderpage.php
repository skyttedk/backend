<?php

namespace GFUnit\vlager\lagerfront;
use GFBiz\units\UnitController;
use GFUnit\vlager\utils\Template;
use GFUnit\vlager\utils\VLager;
class Orderpage
{


    private static $processMessage = "";
    private static $processWarning = "";

    public static function saveOrderpage($url, \VLager $vlager,$orderid) {


        try {
            \system::connection()->transaction();
        } catch (\Exception $e) {

        }

        // Load incoming order
        $order = \VLagerIncoming::find($orderid);

        // Find order lines
        $lines = \VLagerIncomingLine::find_by_sql('SELECT * FROM vlager_incoming_line WHERE vlager_incoming_id = '.$orderid);

        if($order->id == 0) {
            self::$processWarning = "Kan ikke finde ordre!";
            self::outputOrderpage($url, $vlager,$orderid);
            return;
        }

        if($order->received != null) {
            self::$processWarning = "Ordren er allerede modtaget!";
            self::outputOrderpage($url, $vlager,$orderid);
            return;
        }

        if(count($lines) == 0) {
            self::$processWarning = "Kan ikke finde ordrelinjer!";
            self::outputOrderpage($url, $vlager,$orderid);
            return;
        }



        $mailDescription = "Ordre modtaget ".$vlager->code.": ".$order->sono."\n\n";
        $mailDescription .= "Særlige bemærkninger: ".$order->receiver_note."\n\n";
        $mailDescription .= "Ordrelinjer:\n";
        $problems=0;

        try {

            // Save order lines
            foreach($lines as $line) {

                // Read data from post
                $quantityReceived = intval($_POST["line_".$line->id."_received"] ?? -1);
                $note = trim($_POST["line_".$line->id."_note"] ?? "");

                $ls = \VLagerIncomingLine::find($line->id);

                // Check quantity
                if($quantityReceived < 0) {
                    self::$processWarning = "Ugyldig modtaget antal af ".$ls->itemno."!";
                    self::outputOrderpage($url, $vlager,$orderid);
                    return;
                }

                // Save line
                $ls->quantity_received = $quantityReceived;
                $ls->note = $note;
                $ls->save();

                // Get vlager item
                VLager::updateLagerItem($vlager->id,$ls->itemno,$ls->quantity_received,"Modtaget på ".$order->sono,$ls->id,0);

                // Add to mail
                if($ls->quantity_order != $ls->quantity_received) {
                    $mailDescription .= "<b>AFVIGELSE:</b> ";
                    $problems++;
                }
                $mailDescription .= $ls->itemno.": ".$ls->quantity_order." -> ".$quantityReceived." (".$note.")\n";

            }

        } catch (\Exception $e) {
            self::$processWarning = "Der opstod en fejl ved behandling af ordre: ".$e->getMessage();
            self::outputOrderpage($url, $vlager,$orderid);
            return;
        }

        // Save order
        $order->received = new \DateTime();
        $order->receiver_note = trim($_POST["ordernote"] ?? "");
        $order->save();

        if($problems > 0) {
            $mailDescription = "Ordre modtaget med ".$problems." afvigelser:\n\n".$mailDescription;
        }

        // Commit
        \system::connection()->commit();

        // Send e-mail
        mailgf("sc@interactive.dk","Ordre modtaget ".$vlager->code.": ".$order->sono,nl2br($mailDescription));


        Template::templateTop();
        Template::outputFrontendHeader($url, $vlager);

        // output success message with back button
        ?><div class="container mt-4">
        <div class="alert alert-success">Ordren er nu modtaget og lagt på lager.</div>
        <button class="btn btn-secondary" type="button" onClick="document.location='<?php echo $url; ?>'">Tilbage</button>
    </div><br><br><br><?php


        Template::templateBottom();

    }

    public static function outputOrderpage($url, \VLager $vlager,$orderid)
    {

        // Load incoming order
        $order = \VLagerIncoming::find($orderid);

        // Find order lines
        $lines = \VLagerIncomingLine::find_by_sql('SELECT * FROM vlager_incoming_line WHERE vlager_incoming_id = '.$orderid);

        Template::templateTop();
        Template::outputFrontendHeader($url, $vlager);


        ?><div class="container mt-4">
        <!-- Tilbage Knap -->
        <div style="float: right;">
            <button class="btn btn-secondary" type="button" onClick="document.location='<?php echo $url; ?>'">Tilbage</button>
        </div>


        <h3>Ordremodtagelse</h3><br>
        <div class="row">
            <div class="col-md-6">
                <p><b>Ordre:</b> <?php echo $order->sono; ?></p>
                <p><b>Oprettet:</b> <?php echo $order->created->format("Y-m-d H:i"); ?></p>
                <p><b>Modtaget:</b> <?php echo $order->received == null ? "Ikke modtaget" : $order->received->format("Y-m-d H:i"); ?></p>
                <br>
            </div>
            <div class="col-md-6">
                <?php echo $order->sender_note; ?><br>
            </div>

        </div>


        <!-- Ordredetaljer -->


        <?php

        if(self::$processWarning != "") {
            echo "<div class='alert alert-warning'>".self::$processWarning."</div>";
        }

        if(self::$processMessage != "") {
            echo "<div class='alert alert-info'>".self::$processMessage."</div>";
        }

        ?>

        <!-- Tabel -->
        <form method="post" action="<?php echo $url."&order=".$order->id; ?>" onsubmit="return validateForm()">
            <input type="hidden" name="action" value="saveorder">
            <table class="table">
                <thead>
                <tr>
                    <th>Varenr</th>
                    <th>Beskrivelse</th>
                    <th>Antal</th>
                    <th>Modtaget</th>
                    <th>+/-</th>
                    <th>Note</th>
                </tr>
                </thead>
                <tbody>
                <?php
                $readonly = $order->received != null ? "readonly" : "";

                foreach($lines as $line) {
                    $description = "Ukendt beskrivelse";
                    $item = \NavisionItem::find_by_sql("SELECT * FROM navision_item WHERE no LIKE '".$line->itemno."' && language_id = ".$vlager->language_id." ORDER BY id DESC");
                    if(count($item) > 0) {
                        $description = $item[0]->description;
                    }

                    $difference = $line->quantity_received - $line->quantity_order;
                    $differenceText = $difference == 0 ? "OK" : ($difference > 0 ? "+$difference" : $difference);

                    echo "<tr class='receiveorderline'>";
                    echo "<td>".$line->itemno."</td>";
                    echo "<td>".$description."</td>";

                    echo "<td><input type='number' class='form-control' style='width: 80px; text-align: right;' name='line_".$line->id."_quantity' value='".$line->quantity_order."' readonly></td>";

// Brug POST værdi hvis den findes, ellers brug den nuværende værdi
                    $receivedValue = isset($_POST['line_'.$line->id.'_received']) ? $_POST['line_'.$line->id.'_received'] : $line->quantity_received;
                    echo "<td><input type='number' class='form-control quantity-received' style='width: 80px; text-align: right;' name='line_".$line->id."_received' value='".$receivedValue."' $readonly onchange='calculateDifference(this, ".$line->quantity_order.")'></td>";

                    echo "<td><span class='difference'>$differenceText</span></td>";

// Brug POST værdi hvis den findes, ellers brug den nuværende værdi
                    $noteValue = isset($_POST['line_'.$line->id.'_note']) ? $_POST['line_'.$line->id.'_note'] : $line->note;
                    echo "<td><input type='text' name='line_".$line->id."_note' class='form-control' value='".$noteValue."' $readonly></td>";

                    echo "</tr>";

                }
                ?>
                </tbody>
            </table>

            <!-- Total Difference -->
            <p><b>Total +/-:</b> <span id="totalDifference">0</span></p>

            <!-- Generel Note og Knapper -->
            <div class="form-group">
                <label for="generalNote"><b>Særlige bemærkninger</b></label>
                <?php
                // Brug POST værdi hvis den findes, ellers brug den nuværende værdi
                $orderNoteValue = isset($_POST['ordernote']) ? $_POST['ordernote'] : $order->receiver_note;
                ?>
                <textarea class="form-control" name="ordernote" id="generalNote" rows="3" <?php echo $readonly; ?>><?php echo htmlspecialchars(trimgf($orderNoteValue)); ?></textarea>
            </div>


            <!-- Godkendelse -->
            <?php if($order->received == null) { ?>
            <div class="form-check">
                <input type="checkbox" class="form-check-input" id="orderApproval" <?php echo $order->received != null ? "disabled" : ""; ?>>
                <label class="form-check-label" for="orderApproval">Jeg bekræfter, at ordren er optalt og godkendes</label>
            </div>
            <?php } ?>

            <div style="text-align: right;">
                <button class="btn btn-secondary" type="button" onClick="document.location='<?php echo $url; ?>'">Tilbage</button>
                <?php if($order->received == null) { ?>
                    <button class="btn btn-primary" type="submit">Læg på lager</button>
                <?php } else { ?>
                    <button class="btn btn-primary" type="button" onClick="createExtraDelivery()">Opret ekstra modtagelse</button>
                <?php } ?>

                <script>

                    function createExtraDelivery() {

                        if(confirm("Er du sikker på, at du vil oprette en ekstra modtagelse (del-levering)?")) {
                            $.post('<?php echo $url."&vla=extradelivery&order=".$order->id; ?>', function(data) {
                                if(parseInt(data) > 0) {
                                    document.location = '<?php echo $url; ?>&order='+data;
                                } else {
                                    alert("Der opstod en fejl: "+data);
                                }
                            });
                        }

                    }

                </script>

            </div>
        </form>
    </div><br><br><br>

        <script>
            function calculateDifference(input, quantityOrder) {
                const received = parseInt(input.value);
                const difference = received - quantityOrder;
                const differenceCell = input.closest('tr').querySelector('.difference');
                differenceCell.textContent = difference === 0 ? 'OK' : (difference > 0 ? '+' + difference : difference);

                updateTotalDifference();
            }

            function updateTotalDifference() {
                const differences = document.querySelectorAll('.difference');
                let total = 0;
                differences.forEach(diff => {
                    const value = diff.textContent === 'OK' ? 0 : parseInt(diff.textContent);
                    total += value;
                });
                document.getElementById('totalDifference').textContent = total > 0 ? '+' + total : total;
            }

            function validateForm() {
                const quantities = document.querySelectorAll('.quantity-received');
                const approval = document.getElementById('orderApproval');
                let valid = true;

                quantities.forEach(input => {
                    if (isNaN(input.value) || input.value === '') {
                        valid = false;
                    }
                });

                if (!approval.checked) {
                    valid = false;
                    alert("Du skal bekræfte, at ordren er optalt og godkendes.");
                }

                return valid;
            }

            document.addEventListener('DOMContentLoaded', updateTotalDifference);
        </script><?php

        Template::templateBottom();

    }

}