<?php

namespace GFBiz\valgshop;

class OrderHtmlExporter extends OrderExporter
{

    public function __construct()
    {

    }

    public function export()
    {
        ob_start();

        ?><style>

        body { font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", "Noto Sans", "Liberation Sans", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji"; font-size: 12px;}
            table.vsorderpreview { width: 100%; border-collapse: collapse; text-align: left; }
            table.vsorderpreview th { background-color: #eee; padding: 5px; }
            table.vsorderpreview td { border: 1px solid #ccc; padding: 5px; }

        </style>
            <table class="vsorderpreview">
            <thead>
                <tr>
                    <th colspan="6">
                        <div style="float: right;">genereret d. <?php echo date("d-m-Y H:i:s"); ?></div>Preview af valgshop ordre</th>
                </tr>
            </thead>
            <tbody>

            <tr <?php if($this->countErrors() > 0) { ?>style="background: indianred; color: white;"<?php } ?>><td colspan="2">Antal fejl</td><td colspan="4"><?php echo $this->countErrors(); ?></td></tr>
            <tr <?php if($this->countWarnings() > 0) { ?>style="background: yellow; "<?php } ?>><td colspan="2">Antal advarsler</td><td colspan="4"><?php echo $this->countWarnings(); ?></td></tr>

            </tbody>
        <?php if(count($this->errors) > 0) { ?>
        <thead>
        <tr>
            <th colspan="6">Fejlliste</th>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach($this->errors as $error) {
            ?><tr>
            <td colspan="6"><?php echo $error["message"]; ?></td>
            </tr><?php
        }
        ?>
        </tbody>
    <?php } ?>

        <?php if(count($this->warnings) > 0) { ?>
        <thead>
        <tr>
            <th colspan="6">Advarsler</th>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach($this->warnings as $error) {
            ?><tr>
            <td colspan="6"><?php echo $error["message"]; ?></td>
            </tr><?php
        }
        ?>
        </tbody>
    <?php } ?>
            <thead>
            <tr>
                <th colspan="6">Ordre header</th>
            </tr>
            </thead>
            <tbody>
            <?php

            $defaultCustomerNo = "";
            foreach($this->headers as $header) {

                if($header["name"] == "customerno") {
                    $defaultCustomerNo = $header["value"];
                }

                if($header["description"] !== null) {
                    $label = trimgf($header["description"]) == "" ? $header["name"] : $header["description"];
                    $value = is_bool($header["value"]) ? ($header["value"] ? "Ja" : "Nej") : $header["value"];
                    ?><tr>
                        <td colspan="2"><?php echo $label; ?></td><td colspan="4"><?php echo $value; ?></td>
                    </tr><?php
                }

            }


            ?>
            </tbody>
            <?php

            $totalPrice = 0;


            $customerNoList = array($defaultCustomerNo);
            foreach($this->lines as $line) {
                $csNO = intval(trimgf($line["bill_to_customer_no"]));
                if($csNO > 0 && !in_array($csNO, $customerNoList)) {
                    $customerNoList[] = $csNO;
                }
            }




        foreach($customerNoList as $customerNo) {

            $totalPrice = 0;
            ?><thead>
    <tr>
        <th colspan="6">Ordre linjer for debitor no: <?php echo $customerNo; ?></th>
    </tr>
    <tr>
        <th>Type</th>
        <th>Tekst</th>
        <th style="text-align: right;">Antal</th>
        <th style="text-align: right;">Enhedspris</th>
        <th style="text-align: right;">Beløb</th>
        <th>Forklaring</th>
    </tr>
    </thead>
        <tbody><?php

            foreach($this->lines as $line) {
                $csNO = intval(trimgf($line["bill_to_customer_no"]));
                if($line["bill_to_customer_no"] == $customerNo || ($csNO == 0 && $customerNo == $defaultCustomerNo)) {
                    ?><tr>

                        <td><?php echo $line["type"]; ?></td>
                        <td><?php echo $line["description"] === null ? "[Indsættes af navision]" : $line["description"]; ?></td>
                        <td style="text-align: right;"><?php echo ($line["quantity"]); ?></td>
                        <td style="text-align: right;"><?php echo $this->toDKK($line["price"]); ?></td>
                        <td style="text-align: right;"><?php echo $this->toDKK($line["quantity"]*$line["price"]); ?></td>
                        <td style="background: #eee;"><?php echo $line["metadesc"]; ?></td>
                    
                    </tr><?php
                    $totalPrice += $line["quantity"]*$line["price"];
                }
            }

            ?> </tbody>
            <thead>
        <tr>
            <th colspan="5">Total</th>
            <th><?php echo $this->toDKK($totalPrice); ?></th>
        </tr>
            </thead><?php
        }




            ?>

        

            
        </table><?php


        $content = ob_get_contents();
        ob_end_clean();

        return $content;

    }

    private function toDKK($value)
    {
        return number_format($value/100, 2, ",", ".");
    }
    
}