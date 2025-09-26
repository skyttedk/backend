<?php

namespace GFUnit\cardshop\freight;

class FreightEditor2 {

    public function dispatchEditor($companyid, $includechilds) {

        // Load company
        $company = $this->loadCompany($companyid);
        if ($company == null) return;

        // Load freight helper
        $helper = new CSFreightHelper($company->id);
        $freightItems = $helper->getFreightItemsForCompany($includechilds, false);

        if (count($freightItems) == 0) {
            $this->showEditorError("Ingen leveringer fundet. Bemærk at privatleveringer ikke vises her.");
            return;
        }

        $this->showEditorTop();

        echo "<h2 style='margin-bottom: 12px;'>Fragt detaljer for " . $company->name . " " . ($includechilds ? " og evt. underleveringer" : "") . "</h2>";
        echo "<div>Rediger fragtdetaljerne for hver enkelt leverance på kunden. Husk at gemme på knappen nederst.</div>";
        echo "<div style='padding-top: 12px;'>";

        $itemsIDList = [];

        $currentShop = '';
        $currentExpireDate = '';

        echo "<table border='1' style='width: 100%; border-collapse: collapse;'>";
        echo "<thead><tr>
                <th>Ordre</th>
                <th>Forsendelsesoplysninger</th>
                <th>Fragtnoter</th>
                <th>DOT levering</th>
                <th>Opbæring</th>
              </tr></thead>";

        foreach ($freightItems as $item) {
            $shop = \Shop::find($item->getShopId());
            $expireDate = $item->getExpireDateText();

            if ($shop->name !== $currentShop || $expireDate !== $currentExpireDate) {
                if ($currentShop !== '') {
                    echo "</tbody>";
                }
                $currentShop = $shop->name;
                $currentExpireDate = $expireDate;

                echo "<thead><tr><th colspan='5' style='background: #EEE; padding: 5px; text-align: left;'>{$currentShop} // Deadline {$currentExpireDate}</th></tr></thead>";
                echo "<tbody>";
            }

            $editor = new EditorElement2($item);
            echo $editor->renderEditor();
            $itemsIDList[] = $item->getUniqueKey();
        }

        echo "</tbody></table>";
        echo "</div>";

        // Output an hidden error message panel in red and a success message in green
        ?>
        <div id="errorpanelglobal" class="savepanel" style="margin-top: 12px; display: none; padding: 12px; background-color: #ffcccc; border: 1px solid #ff0000; margin-bottom: 12px;"></div>
        <div id="successpanel" class="savepanel" style="margin-top: 12px; display: none; padding: 12px; background-color: #ccffcc; border: 1px solid #00ff00; margin-bottom: 12px;"></div>
        <?php

        ?><div style="padding-top: 12px;"><button class="btn-blue" onclick="saveFreightForm()">Gem fragt-opsætning</button></div>

        <script>

            function saveFreightForm() {

                $('.savepanel').hide().html('');

                var freightItems = [];
                $('.freightitemeditorparent').each(function() {

                    var item = {};
                    item.id = $(this).attr('data-freightid');
                    item.key = $(this).attr('data-itemkey');
                    item.note = $(this).find('.freightnotes').val();
                    item.dot = $(this).find('.usedot').is(':checked') ? 1 : 0;
                    item.dotpricetype = $(this).find('.dotpricetype').val();
                    item.dotpriceamount = $(this).find('.dotpriceamount').val();
                    item.dotdescription = $(this).find('.dotdescription').val();
                    item.dotdate = $(this).find('.dot_date').val();
                    item.dotstart = $(this).find('.dot_time_from').val();
                    item.dotend = $(this).find('.dot_time_to').val();
                    item.carryup = $(this).find('.usecarryup').is(':checked') ? 1 : 0;
                    item.carryupprice = $(this).find('.carryuppricetype').val();
                    item.carryuppriceamount = $(this).find('.carryuppriceamount').val();
                    item.carryuptype = $(this).find('.carryuptype:checked').val();
                    freightItems.push(item);
                });

                // Collect post data
                var postData = {'companyid': <?php echo $company->id; ?>, includechilds: <?php echo $includechilds ? 1 : 0; ?>, items: <?php echo json_encode($itemsIDList); ?>, freightitems: freightItems};

                console.log(postData);

                // Make post request
                var url = "index.php?rt=unit/cardshop/freight/companyfreightsave";
                $.post(url, postData, function(data) {
                    console.log(data);
                    if (data.success == 1) {
                        $('#successpanel').show().html("Fragt-opsætning er gemt.");
                    } else {

                        // go through error objects in data.error
                        var errorHTML = "";
                        for (var i = 0; i < data.error.length; i++) {
                            var errorObj = data.error[i];
                            var errorMsg = errorObj.error;
                            var errorKey = errorObj.code;

                            console.log('SHOW ERROR ' + errorKey + ' ' + errorMsg);

                            $('#errorpanel' + errorKey).show().append("<div>" + errorMsg + "</div>");

                        }

                    }
                }, "json");

            }

            function useDotChange(elm) {
                var parent = $(elm).closest('.freightitemeditorparent');
                if (elm.checked) {
                    parent.find('.dotdetails').show();
                } else {
                    parent.find('.dotdetails').hide();
                }
            }

            function dotPriceChange(elm) {
                var parent = $(elm).closest('.freightitemeditorparent');
                var priceType = $(elm).val();

                if (priceType == '3') {
                    parent.find('.dotpriceamount').parent().show();
                } else {
                    parent.find('.dotpriceamount').parent().hide();
                }
            }

            function useCarryupChange(elm) {
                var parent = $(elm).closest('.freightitemeditorparent');
                if (elm.checked) {
                    parent.find('.carryupdetails').show();
                } else {
                    parent.find('.carryupdetails').hide();
                }

            }

            function carryupPriceChange(elm) {
                var parent = $(elm).closest('.freightitemeditorparent');
                var priceType = $(elm).val();

                if (priceType == '3') {
                    parent.find('.carryuppriceamount').parent().show();
                } else {
                    parent.find('.carryuppriceamount').parent().hide();
                }
            }

            $(document).ready(function() {

                $('.freightitemeditorparent').each(function() {

                    useDotChange($(this).find('.usedot').get(0));
                    dotPriceChange($(this).find('.dotpricetype').get(0));
                    carryupPriceChange($(this).find('.carryuppricetype').get(0));

                });

            })

        </script>

        <?php

        $this->showEditorBottom();

    }

    private function loadCompany($companyid)
    {
        // Load company
        try {
            $company = \Company::find(intvalgf($companyid));
        } catch (\Exception $e) {
            $this->showEditorError("Error loading company (" . $companyid . ").");
            return null;
        }

        // Check company
        if ($company == null || $company->id == 0) {
            $this->showEditorError("Company not found (" . $companyid . ").");
            return null;
        }

        return $company;
    }

    private function showEditorError($error) {
        $this->showEditorTop();
        ?><h1>Ingen leveringer</h1>
        <p><?php echo $error; ?></p><?php
        $this->showEditorBottom();
    }

    private $topOutputted = false;
private function showEditorTop() {

    if ($this->topOutputted == true) return;
    $this->topOutputted = true;

    ?><html>
<head>
    <title>Freight Editor</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            line-height: 1.5;
            color: rgb(34, 34, 34);
            background-color: #FFFFFF;
            margin: 0;
            padding: 10px;
        }

        .btn-blue {
            display: inline-block;
            padding: 10px 20px;
            font-size: 16px;
            font-weight: bold;
            color: #fff;
            background-color: #007bff;
            border: none;
            border-radius: 5px;
            text-align: center;
            text-decoration: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn-blue:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<script src="views/lib/jquery.min.js"></script>
<body><?php

}

private function showEditorBottom() {

?></body>
</html><?php

}

}
?>
