<?php

namespace GFUnit\cardshop\freight;

class FreightEditor {

    public function saveEditor()
    {



        // Load inputs
        try {
            $companyid = intvalgf($_POST['companyid']);
            $includechilds = $_POST['includechilds'] == 1 ? true : false;
            $items = $_POST['items'];
            $freightItemsData = $_POST['freightitems'];
        }
        catch (\Exception $e) {
            $this->addError("Fejl i data: ".$e->getMessage());
            $this->outputResponse();
            return;
        }

        // Load company
        $company = $this->loadCompany($companyid);
        if($company == null) {
            $this->addError("Kunne ikke finde virksomhed");
            $this->outputResponse();
            return;
        }

        // Load freight helper
        $helper = new CSFreightHelper($company->id);
        $freightItems = $helper->getFreightItemsForCompany($includechilds,false);

        if(count($freightItems) == 0) {
            $this->addError("Ingen fragt fundet");
            $this->outputResponse();
            return;
        }

        // Check versus count on items and freight items
        if(count($items) == 0) {
            $this->addError("Ingen items fundet");
            $this->outputResponse();
            return;
        }
        if(count($freightItemsData) == 0) {
            $this->addError("Ingen fragt fundet");
            $this->outputResponse();
            return;
        }
        if(count($items) != count($freightItemsData) || count($items) != count($freightItems)) {
            $this->addError("Antal items og fragt items passer ikke sammen");
            $this->outputResponse();
            return;
        }


        sort($items);

        $dataMap = [];
        $dataIDs = [];
        foreach($freightItemsData as $itemsData) {
            $dataIDs[] = $itemsData['key'];
            $dataMap[$itemsData["key"]] = $itemsData;
        }

        sort($dataIDs);
        if(implode(",",$items) != implode(",",$dataIDs)) {
            $this->addError("Items data passer ikke sammen");
            $this->outputResponse();
            return;
        }

        $fetchIDs = [];
        foreach($freightItems as $freightItem) { $fetchIDs[] = $freightItem->getUniqueKey(); }
        sort($fetchIDs);
        if(implode(",",$items) != implode(",",$fetchIDs)) {
            $this->addError("Items db passer ikke sammen");
            $this->outputResponse();
            return;
        }

        // Loop through items
        foreach($freightItems as $freighItem) {

            try {

                $shop = \Shop::find($freighItem->getShopId());
                $cardshopSettings = \CardshopSettings::find_by_shop_id($shop->id);

                // Load data
                $data = $dataMap[$freighItem->getUniqueKey()];

                // Freight object
                $freightObject = $freighItem->getCardshopFreight();

                if($freightObject == null) {

                    $freightObject = new \CardshopFreight();
                    $freightObject->company_id = $freighItem->getCompanyId();

                    $order = \CompanyOrder::find_by_order_no($freighItem->getFirstCompanyOrder());
                    $freightObject->company_order_id = $order->id;

                    if($freightObject->company_id == 0) {
                        $this->addError("Der er ikke angivet en gyldig virksomhed",$freighItem->getUniqueKey());
                    }
                    if($freightObject->company_order_id == 0) {
                        $this->addError("Der er ikke angivet en gyldig ordre",$freighItem->getUniqueKey());
                    }

                    $freightObject->created = new \DateTime();

                }

                // Set freight note
                $freightObject->note = trimgf($data['note']);

                // Set freight dot
                $freightObject->dot = intvalgf($data["dot"]) == 1 ? 1 : 0;
                if($freightObject->dot == 1) {

                    $freightObject->dot_note = trimgf($data['dotdescription'] ?? "");

                    try {
                        // Kontrollér at alle påkrævede felter er udfyldt
                        if (empty($data['dotdate']) || empty($data['dotstart']) || empty($data['dotend'])) {
                            throw new \Exception("Alle dato- og tidsfelter skal være udfyldt.");
                        }

                        // Opret datetime-objekter for start og slut
                        $dotStartDateTime = new \DateTime($data['dotdate'] . ' ' . $data['dotstart']);
                        $dotEndDateTime = new \DateTime($data['dotdate'] . ' ' . $data['dotend']);

                        // Validér årstal
                        if ($dotStartDateTime->format('Y') < \GFConfig::SALES_SEASON || $dotStartDateTime->format('Y') > \GFConfig::SALES_SEASON) {
                            throw new \Exception("Årstal ikke korrekt.");
                        }

                        // Validér at sluttidspunkt er efter starttidspunkt
                        if ($dotEndDateTime <= $dotStartDateTime) {
                            throw new \Exception("Sluttidspunkt skal være efter starttidspunkt.");
                        }

                        // Gem værdierne i objektet
                        $freightObject->dot_date = $dotStartDateTime;
                        $freightObject->dot_date_end = $dotEndDateTime;

                    } catch (\Exception $e) {
                        $this->addError("Der er ikke angivet gyldige dato- og tidspunkter til DOT: " . $e->getMessage(), $freighItem->getUniqueKey());
                    }


                    $freightObject->dot_pricetype = intvalgf($data['dotpricetype']);

                    // Check dot note

                    if($freightObject->dot_date == null) {
                        $this->addError("Der er ikke angivet en dato til dot",$freighItem->getUniqueKey());
                    }

                    // Set price
                    if($freightObject->dot_pricetype == 1) {
                        $freightObject->dot_price = $cardshopSettings->dot_price;
                    } else if($freightObject->dot_pricetype == 2) {
                        $freightObject->dot_price = 0;
                    } else if ($freightObject->dot_pricetype == 3) {
                        $freightObject->dot_price = intval(floatval($data['dotpriceamount'] ?? 0)*100);
                        if($freightObject->dot_price < 0) $this->addError("Dot pris kan ikke være negativ.",$freighItem->getUniqueKey());
                        else if($freightObject->dot_price > 2500000) $this->addError("Dot pris kan ikke være over 25000.",$freighItem->getUniqueKey());
                    } else {
                        $this->addError("Der er ikke valgt en gyldig pristype til dot",$freighItem->getUniqueKey());
                    }

                } else {
                    $freightObject->dot_note = "";
                    $freightObject->dot_date = null;
                    $freightObject->dot_pricetype = 0;
                    $freightObject->dot_price = 0;
                }

                // Set freight carryup
                $freightObject->carryup = intvalgf($data["carryup"]) == 1 ? 1 : 0;
                if($freightObject->carryup == 1) {

                    $freightObject->carryup_pricetype = intvalgf($data['carryupprice']);

                    if($freightObject->carryup_pricetype == 1) {
                        $freightObject->carryup_price = $cardshopSettings->carryup_price;
                    } else if($freightObject->carryup_pricetype == 2) {
                        $freightObject->carryup_price = 0;
                    } else if ($freightObject->carryup_pricetype == 3) {
                        $freightObject->carryup_price = intval(floatval($data['carryuppriceamount'] ?? 0)*100);
                        if($freightObject->carryup_price < 0) $this->addError("Carryup pris kan ikke være negativ.",$freighItem->getUniqueKey());
                        else if($freightObject->carryup_price > 2500000) $this->addError("Carryup pris kan ikke være over 25000.",$freighItem->getUniqueKey());
                    } else {
                        $this->addError("Der er ikke valgt en gyldig pristype til carryup",$freighItem->getUniqueKey());
                    }

                    $freightObject->carryuptype = intvalgf($data['carryuptype']);
                    $validCarryupTypes = array(1,2,3);

                    if(!in_array($freightObject->carryuptype,$validCarryupTypes)) {
                        $this->addError("Der er ikke valgt en gyldig carryup type",$freighItem->getUniqueKey());
                    }

                } else {
                    $freightObject->carryup_pricetype = 0;
                    $freightObject->carryup_price = 0;
                    $freightObject->carryuptype = 0;
                }

                $freightObject->updated = new \DateTime();

                // Determine if remove or save
                if($freightObject->note == "" && $freightObject->dot == 0 && $freightObject->carryup == 0) {

                    if($freightObject->id > 0) {
                        $freightObject->delete();
                    }
                } else {
                    $freightObject->save();
                }

            }
            catch (\Exception $e) {
                $this->addError("Der opstod fejl ved ".$freighItem->getUniqueKey().": ".$e->getMessage(),$freighItem->getUniqueKey());
            }

        }

        $this->outputResponse();
    }

    private $saveErrors =  array();

    private function addError($error,$code="global") {
        $this->saveErrors[] = ['code' => str_replace(":","_",$code), 'error' => $error];
    }

    private function outputResponse() {
        if(count($this->saveErrors) == 0) {
            echo json_encode(['success' => 1]);
            \system::connection()->commit();
        } else {

            $hasGlobalErrors = false;
            foreach($this->saveErrors as $error) {
                if($error['code'] == 'global') {
                    $hasGlobalErrors = true;
                    break;
                }
            }

            if(!$hasGlobalErrors) {
                $this->addError("Der er ".count($this->saveErrors)." fejl, se ovenfor og ret for at gemme.");
            }

            echo json_encode(['success' => 0, 'error' => $this->saveErrors]);
        }
        exit();
    }

    public function dispatchEditor($companyid,$includechilds) {

        // Load company
        $company = $this->loadCompany($companyid);
        if($company == null) return;

        // Load freight helper
        $helper = new CSFreightHelper($company->id);
        $freightItems = $helper->getFreightItemsForCompany($includechilds,false);

        if(count($freightItems) == 0) {
            $this->showEditorError("Ingen leveringer fundet. Bemærk at privatleveringer ikke vises her.");
            return;
        }

        $this->showEditorTop();

        echo "<h2 style='margin-bottom: 12px;'>Fragt detaljer for ".$company->name." ".($includechilds ? " og evt. underleveringer" : "")."</h2>";
        echo "<div>Rediger fragtdetaljerne for hver enkelt leverance på kunden. Husk at gemme på knappen nederst.</div>";
        echo "<div style='padding-top: 12px;'>";

        $itemsIDList = [];

        foreach($freightItems as $item) {

            $editor = new EditorElement($item);
            echo $editor->renderEditor();

            $itemsIDList[] = $item->getUniqueKey();

        }

        echo "</div>";

        // Output an hidden error messsage panel in red and a success message in green
        ?>
        <div id="errorpanelglobal" class="savepanel" style="margin-top: 12px; display: none; padding: 12px; background-color: #ffcccc; border: 1px solid #ff0000; margin-bottom: 12px;"></div>
        <div id="successpanel" class="savepanel" style="margin-top: 12px; display: none; padding: 12px; background-color: #ccffcc; border: 1px solid #00ff00; margin-bottom: 12px;"></div>
        <?php

        ?><div style="padding-top: 12px;"><button class="btn-blue" onclick="saveFreightForm()">Gem fragt-opsætning</button></div>

        <script>

            function saveFreightForm()
            {

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
                    item.dotdate = $(this).find('.dotdate').val();
                    item.carryup = $(this).find('.usecarryup').is(':checked') ? 1 : 0;
                    item.carryupprice = $(this).find('.carryuppricetype').val();
                    item.carryuppriceamount = $(this).find('.carryuppriceamount').val();
                    item.carryuptype = $(this).find('.carryuptype:checked').val();
                    freightItems.push(item);
                });

                // Collect post data
                var postData = {'companyid': <?php echo $company->id; ?>,includechilds: <?php echo $includechilds ? 1 : 0; ?>,items: <?php echo json_encode($itemsIDList); ?>,freightitems: freightItems};

                console.log(postData);

                // Make post request
                var url = "index.php?rt=unit/cardshop/freight/companyfreightsave";
                $.post(url, postData, function(data) {
                    console.log(data);
                    if(data.success == 1) {
                        $('#successpanel').show().html("Fragt-opsætning er gemt.");
                    } else {

                        // go through error objects in data.error
                        var errorHTML = "";
                        for(var i = 0; i < data.error.length; i++) {
                            var errorObj = data.error[i];
                            var errorMsg = errorObj.error;
                            var errorKey = errorObj.code;

                            console.log('SHOW ERROR '+errorKey+' '+errorMsg);

                            $('#errorpanel'+errorKey).show().append("<div>"+errorMsg+"</div>");

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

                if(priceType == '3') {
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

                if(priceType == '3') {
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
            $this->showEditorError("Error loading company (".$companyid.").");
            return null;
        }

        // Check company
        if($company == null || $company->id == 0) {
            $this->showEditorError("Company not found (".$companyid.").");
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

    if($this->topOutputted == true) return;
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