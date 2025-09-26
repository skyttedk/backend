<?php

namespace GFBiz\Model\Cardshop;

class CompanyOrderLogic
{


    public static function createOrder($data,$isWeb=false)
    {


        // Load shop and shop settings
        $shop = \Shop::find(intval($data["shop_id"]));
        $shopSettings = new CardshopSettingsLogic($shop->id);
        unset($data["shop_id"]);

        // Check expire date on shop
        $expireDate = $data["expire_date"];
        $shopExpireDate = $shopSettings->getWeekByExpireDate($expireDate);

        if($shopExpireDate == null) {
            throw new \Exception("Could not find expire date");
        }

        // Quantity
        $quantity = intval($data["quantity"]);
        unset($data["quantity"]);

        if($isWeb && $quantity <= 4) {
            throw new \Exception("Order quantity must be at least 5.");
        }

        // Check quantity
        if($quantity <= 0 ) {
            throw new \Exception("Enter a positive quantity");
        }

        if($quantity > 10000) {
            throw new \Exception("Max 500 cards can be sold at a time");
        }

        // Load company
        $company = \Company::find(intval($data["company_id"]));
        unset($data["company_id"]);

        // Check company
        if($company->language_code != $shopSettings->getSettings()->language_code) {
            throw new \Exception("Company does not have the same language code as the cardshop");
        }

        /*
        if($company->company_state == 4) {
            throw new \Exception("Company is currently blocked, cant create orders");
        }
        */

        if($company->pid > 0) {
            throw new \Exception("Company is a child, cant create orders on a child company");
        }

        // Form report
        $formReport = isset($data["formreport"]) ? $data["formreport"] : "";
        unset($data["formreport"]);

        // Is email
        $isEmail = intval($data["is_email"]) == 1;
        unset($data["is_email"]);

        if($isWeb && (($isEmail && $shopExpireDate->isEmailWebsaleOpen() == false) || (!$isEmail && $shopExpireDate->isPhysicalWebsaleOpen() == false))) {
            throw new \Exception("Sale on the selected expire date has closed");
        } else if(!$shopExpireDate->isSaleOpen()) {
            throw new \Exception("Sale on the selected expire date has closed");
        }

        // Check for invalid fields
        self::checkInvalidFields($data,$company,true);

        // Add values to order data
        $data["company_id"] = $company->id;
        $data["shop_id"] = $shop->id;
        $data["shop_name"] = $shop->name;
        $data["quantity"] = $quantity;
        $data["is_email"] = $isEmail ? 1 : 0;
        $data["order_state"] = 0;
        $data["is_printed"] = 0;
        $data["is_shipped"] = 0;
        $data["is_invoiced"] = 0;
        $data["is_cancelled"] = 0;
        $data["navsync_status"] = 200;
        $data["nav_on_hold"] = 0;
        $data["certificate_no_begin"] = "";
        $data["certificate_no_end"] = "";
        $data["certificate_value"] = $shop->card_value;
        if(!isset($data["shipment_ready"])) $data["shipment_ready"] = 0;

        // Set salesperson if not specified
        if(!isset($data["salesperson"]) || trimgf($data["salesperson"]) == "") {
            $data["salesperson"] = \router::$systemUser == null ? "" : \router::$systemUser->salespersoncode;
        }

        // Set company data if not specified
        if(!isset($data["cvr"])) $data["cvr"] = $company->cvr;
        if(!isset($data["ean"])) $data["ean"] = $company->ean;
        if(!isset($data["company_name"])) $data["company_name"] = $company->name;
        if(!isset($data["ship_to_company"])) $data["ship_to_company"] = $company->ship_to_company;
        if(!isset($data["ship_to_address"])) $data["ship_to_address"] = $company->ship_to_address;
        if(!isset($data["ship_to_address_2"])) $data["ship_to_address_2"] = $company->ship_to_address_2;
        if(!isset($data["ship_to_postal_code"])) $data["ship_to_postal_code"] = $company->ship_to_postal_code;
        if(!isset($data["ship_to_city"])) $data["ship_to_city"] = $company->ship_to_city;
        if(!isset($data["ship_to_country"])) $data["ship_to_country"] = $company->ship_to_country;
        if(!isset($data["contact_name"])) $data["contact_name"] = $company->contact_name;
        if(!isset($data["contact_email"])) $data["contact_email"] = $company->contact_email;
        if(!isset($data["contact_phone"])) $data["contact_phone"] = $company->contact_phone;

        // Prepayment
        $prepaymentDateTime = null;
        //$prepaymentDueDateTime = null;

        if(isset($data["prepayment"]) && $data["prepayment"] == "2") {

            $data["prepayment"] = 1;

            // If prepayment_date set
            if(isset($data["prepayment_date"])) {

                $prepaymentDate = trimgf($data["prepayment_date"]);
                unset($data["prepayment_date"]);

                if($prepaymentDate != "" && $prepaymentDate != "0000-00-00") {
                    $prepaymentDateTime = new \DateTime($prepaymentDate);
                    $prepaymentDateTime->setTime(0,0,0);
                }

            }

        }

        /*
        if(!isset($data["cardto_name"])) $data["cardto_name"] = $company->cardto_name;
        if(!isset($data["cardto_address"])) $data["cardto_address"] = $company->cardto_address;
        if(!isset($data["cardto_address2"])) $data["cardto_address2"] = $company->cardto_address2;
        if(!isset($data["cardto_postal_code"])) $data["cardto_postal_code"] = $company->cardto_postal_code;
        if(!isset($data["cardto_city"])) $data["cardto_city"] = $company->cardto_city;
        if(!isset($data["cardto_country"])) $data["cardto_country"] = $company->cardto_country;
        */

        // Unset dot and carryup
        $dotData = array(
            "active" => intvalgf($data["dot_active"] ?? 0) == 1 ? 1 : 0,
            "price_type" => intvalgf($data["dot_price_type"] ?? 0),
            "price_amount" => floatval($data["dot_price_amount"] ?? 0),
            "description" => trimgf($data["dot_description"] ?? ""),
        );

        $carryupData = array(
            "active" => intvalgf($data["carryup_active"] ?? 0) == 1 ? 1 : 0,
            "price_type" => intvalgf($data["carryup_price_type"] ?? 0),
            "price_amount" => floatval($data["carryup_price_amount"] ?? 0),
            "type" => trimgf($data["carryup_type"] ?? ""),
        );

        unset($data["dot_active"]);
        unset($data["dot_price_type"]);
        unset($data["dot_price_amount"]);
        unset($data["dot_description"]);
        unset($data["carryup_active"]);
        unset($data["carryup_price_type"]);
        unset($data["carryup_price_amount"]);
        unset($data["carryup_type"]);


        // Card values
        $isValuesShop = trimgf($shopSettings->getSettings()->card_values) != "";
        $hasValuesInput = isset($data["values"]) && (is_array($data["values"]) || trimgf($data["values"]) != "");
        $cardValues = null;
        $valuesAvg = null;

        if(!$isValuesShop && $hasValuesInput) {
            throw new \Exception("Card values not allowed");
        } else if($isValuesShop && !$hasValuesInput) {
            throw new \Exception("Card values required");
        } else if($isValuesShop && $hasValuesInput) {

            $shopValues = explode(",",$shopSettings->getSettings()->card_values);

            if(is_array($data["values"])) {
                $inputValues = $data["values"];
            } else if(trimgf($data["values"]) != "") {
                $inputValues = explode(",",trimgf($data["values"]));
            } else {
                $inputValues = array();
            }

            $selectedValues = array();

            foreach($inputValues as $selectedValue) {
                if(in_array($selectedValue,$shopValues)) {
                    $selectedValues[] = $selectedValue;
                } else {
                    throw new \Exception("Invalid card value: ".$selectedValue);
                }
            }

            if(count($selectedValues) == 0) {
                throw new \Exception("No card values selected");
            }

            // Sort selected values
            sort($selectedValues);
            $cardValues = implode(",",$selectedValues);

            // Calculate average of card values and set as certificate value
            $avgValue = 0;
            foreach($selectedValues as $selectedValue) {
                $avgValue += intval($selectedValue);
            }
            $valuesAvg = $avgValue / count($selectedValues);

        }

        unset($data["values"]);

        // Create order
        $co = new \CompanyOrder();
        $co->update_attributes($data);

        // Set values
        if($cardValues != null) {

            $co->card_values = $cardValues;
            $co->certificate_value = $valuesAvg;
        }

        if($prepaymentDateTime != null) $co->prepayment_date = $prepaymentDateTime;
        //if($prepaymentDueDateTime != null) $co->prepayment_duedate = $prepaymentDueDateTime;

        // Calculated fields
        $system = \system::first();
        $co->order_no = \Numberseries::getNextNumber($system->company_order_nos_id);

        $switchedToMailCards = false;
        if($co->is_email == 0 && isset($data["salenote"]) && trimgf($data["salenote"]) == "#email#") {
            $co->is_email = 1;
            $switchedToMailCards = true;
        }

        // Find gift certificates
        try {
            if($co->is_email == 1) {
                $giftcertificates = \GiftCertificate::findBatchEmail($shop->id,$co->quantity,$shopExpireDate->getExpireDate(),$shop->reservation_group);
            }   else {
                $giftcertificates = \GiftCertificate::findBatchPrint($shop->id,$co->quantity,$shopExpireDate->getExpireDate(),$shop->reservation_group);
            }
        } catch (Exception $e) {
            throw new \Exception("Could not allocate gift certificates (0 extracted - exception)");
        }

        if($switchedToMailCards == true) {
            $co->is_email = 0;
        }

        // Update gift certificate range
        $co->certificate_no_begin  =  $giftcertificates[0]->certificate_no;
        $co->certificate_no_end    =  $giftcertificates[countgf($giftcertificates)-1]->certificate_no;

        // Check interval vs quantity
        if((intval($co->certificate_no_end)-intval($co->certificate_no_begin)+1) != $co->quantity) {
            throw new Exception("Could not allocate gift certificates (interval jump)");
        }

        // Save order
        $co->nav_synced = 0;
        $co->save();
        if($co->id == 0) {
            throw new \Exception("Could not save order");
        }

        // Update company state
        if($company->company_state == 0) {
            $company->company_state = 1;
            $company->save();
        }

        // Add to shop
        foreach($giftcertificates as $giftcertificate)  {
            \GiftCertificate::addToShop($giftcertificate->id,$shop->id,$company->id,$co->id,$co->card_values);
        }

        // Create default order items
        foreach($shopSettings->getProducts() as $productItem) {

            $coi = new \CompanyOrderItem();
            $coi->companyorder_id = $co->id;
            $coi->quantity = ($productItem->isPerCard() ? $quantity : 1);
            $coi->type = $productItem->getCode();
            $coi->price = $productItem->getPrice();
            $coi->isdefault = 1;

            if($co->card_values != null && $coi->type == "CONCEPT") {
                $coi->price = $co->certificate_value;
            }

            if(!$productItem->isDefault()) {
                $coi->quantity = 0;
            }

            if($productItem->getExtraDataField("restrict") == "privatedelivery" && $shopExpireDate->isDelivery() == false) {
                $coi->quantity = 0;
            }

            if($productItem->getExtraDataField("restrict") == "physicalcards" && $co->is_email == 1) {
                $coi->quantity = 0;
            }

            $coi->save();

        }

        // If dot OR carryup is active, create freight item
        if($dotData["active"] == 1 || $carryupData["active"] == 1 || trimgf($co->spdealtxt) != "") {

            $freightObject = new \CardshopFreight();
            $freightObject->company_id = $co->company_id;
            $freightObject->company_order_id = $co->id;
            $freightObject->created = new \DateTime();

            // Set freight note
            $freightObject->note = trimgf($co->spdealtxt);

            // Set dot
            $freightObject->dot = $dotData["active"] == 1 ? 1 : 0;
            if($freightObject->dot == 1) {

                // Dot date
                try {
                    $dotDate = new \DateTime($dotData['description']);
                    if ($dotDate->format('Y') < \GFConfig::SALES_SEASON || $dotDate->format('Y') > \GFConfig::SALES_SEASON) {
                        throw new \Exception("Årstal ikke korrekt.");
                    }
                } catch (\Exception $e) {
                    $dotDate = null;
                }

                $freightObject->dot_date = $dotDate;
                $freightObject->dot_note = "";
                $freightObject->dot_pricetype = intvalgf($dotData['price_type']);

                // Set price
                if($freightObject->dot_pricetype == 1) {
                    $freightObject->dot_price = $shopSettings->getSettings()->dot_price;
                } else if($freightObject->dot_pricetype == 2) {
                    $freightObject->dot_price = 0;
                } else if ($freightObject->dot_pricetype == 3) {
                    $freightObject->dot_price = intval(floatval($dotData['price_amount'] ?? 0)*100);
                }

            } else {
                $freightObject->dot_note = "";
                $freightObject->dot_pricetype = 0;
                $freightObject->dot_price = 0;
            }

            // Set freight carryup
            $freightObject->carryup = $carryupData["active"] == 1 ? 1 : 0;
            if($freightObject->carryup == 1) {

                $freightObject->carryup_pricetype = intvalgf($carryupData['price_type']);

                if($freightObject->carryup_pricetype == 1) {
                    $freightObject->carryup_price = $shopSettings->getSettings()->carryup_price;
                } else if($freightObject->carryup_pricetype == 2) {
                    $freightObject->carryup_price = 0;
                } else if ($freightObject->carryup_pricetype == 3) {
                    $freightObject->carryup_price = intval(floatval($carryupData['price_amount'] ?? 0)*100);
                }

                $freightObject->carryuptype = intvalgf($carryupData['type']);

            } else {
                $freightObject->carryup_pricetype = 0;
                $freightObject->carryup_price = 0;
                $freightObject->carryuptype = 0;
            }

            $freightObject->updated = new \DateTime();

            $freightObject->save();
        }

        /*
        // If salenote, create a note
        if(trimgf($co->salenote) != "") {

            // Create note
            $note = new \CompanyNotes();
            $note->company_id = $co->company_id;
            $note->priority = 0;
            $note->created_by = \router::$systemUser->id;
            $note->created_datetime = new \DateTime("now");
            $note->note = "Note fra ordreoprettelse ".$co->order_no.": ".$co->salenote;

            // Save note
            $note->save();

        }
        */

        \ActionLog::logAction("CompanyOrderCreated", "Ny cardshop ordre: ".$co->order_no." - ".$co->shop_name.": ".$co->quantity." kort", $formReport,0,$co->shop_id,$co->company_id,$co->id,0,0,0);

        // Return company order
        return $co;

    }




    public static function updateOrder($companyorderid,$data)
    {

        // Check if company_id is set, and id not, set id
        if(isset($data["company_order_id"])) unset($data["company_order_id"]);
        if(isset($data["id"])) unset($data["id"]);

        // Send ob
        $sendOB = false;
        if(isset($data["sendob"])) {
            if(intval($data["sendob"]) == 1) $sendOB = true;
            unset($data["sendob"]);
        }

        // Load company
        $co = \CompanyOrder::find(intval($companyorderid));
        $company = \Company::find($co->company_id);

        // Check for invalid fields
        self::checkInvalidFields($data,$company);

        // Check if expire date is moved
        if(isset($data["expire_date"]) && $data["expire_date"] != $co->expire_date->format('Y-m-d')) {
            self::moveOrderExpireDate($co,$data["expire_date"]);
        }

        // If prepayment_date set
        $prepaymentDateTime = null;
        if(isset($data["prepayment"]) && $data["prepayment"] == "2" && isset($data["prepayment_date"])) {

           
            $data["prepayment"] = 1;
            $prepaymentDate = trimgf($data["prepayment_date"]);
            unset($data["prepayment_date"]);

            if($prepaymentDate != "" && $prepaymentDate != "0000-00-00") {

                if($data["prepayment"] != 1) {
                    throw new \Exception("Prepayment date set, but prepayment not set");
                }

                $prepaymentDateTime = new \DateTime($prepaymentDate);

                // Detect problem in date
                if($prepaymentDateTime->format('Y-m-d') != $prepaymentDate) {
                    throw new \Exception("Invalid prepayment date");
                }

                $prepaymentDateTime->setTime(0,0,0);
                $co->prepayment_date = $prepaymentDateTime;

                // Get unixtime for date
                $prepaymentTime = $prepaymentDateTime->getTimestamp();
                if($prepaymentTime < (time()-60*60*24*365*2)) {
                    throw new \Exception("Prepayment date is too old");
                }
                if($prepaymentTime > (time()+60*60*24*365*1)) {
                    throw new \Exception("Prepayment date too far in the future");
                }


            }

        } else {
            $co->prepayment_date = null;
        }

        if(isset($data["prepayment"]) && $data["prepayment"] == "2") {
            $data["prepayment"] = 1;
        }

        // If prepayment_due_date set
        //if(isset($data["prepayment_due_date"])) {

            //$prepaymentDueDate = trimgf($data["prepayment_due_date"]);
            //unset($data["prepayment_due_date"]);

            //if($prepaymentDueDate != "" && $prepaymentDueDate != "0000-00-00") {

                //if($data["prepayment"] != 1) {
                //    throw new \Exception("Prepayment duedate set, but prepayment not set");
                //}

                //$prepaymentDueDateTime = new \DateTime($prepaymentDueDate);

                // Detect problem in date
                //if($prepaymentDueDateTime->format('Y-m-d') != $prepaymentDueDate) {
                //    throw new \Exception("Invalid prepayment date");
                //}

                //$prepaymentDueDateTime->setTime(0,0,0);
                //$co->prepayment_duedate = $prepaymentDueDate;

                // If $prepaymentDateTime is  set, throw exception if not duedate is after $prepaymentDateTime
                //if($prepaymentDateTime != null && $prepaymentDateTime > $prepaymentDueDateTime) {
                //    throw new \Exception("Prepayment date is after prepayment due date");
                //}

                // Get unixtime for duedate
                //$dueDateTime = $prepaymentDueDateTime->getTimestamp();
                //if($dueDateTime < (time()-60*60*24*365*2)) {
                //    throw new \Exception("Prepayment due date is too old");
                //}
                //if($dueDateTime > (time()+60*60*24*365*1)) {
                //    throw new \Exception("Prepayment due too far in the future");
                //}

            //}

        //}

        // Check card values
        $shopSettings = new CardshopSettingsLogic($co->shop_id);
        if(trimgf($shopSettings->getSettings()->card_values) != "") {

            $oldValues = array_filter(array_map('intval', explode(",", $co->card_values)), fn($value) => $value > 0);
            $newValues = array_filter(array_map('intval', explode(",", $data["card_values"])), fn($value) => $value > 0);
            $validValues = array_filter(array_map('intval', explode(",", trimgf($shopSettings->getSettings()->card_values))), fn($value) => $value > 0);

            if (!$newValues) {
                throw new \Exception("Ingen nye værdier valgt.");
            }

            if (!$validValues) {
                throw new \Exception("Ingen gyldige værdier fundet.");
            }

            if (array_diff($newValues, $validValues)) {
                throw new \Exception("Ugyldige værdier fundet i nye værdier.");
            }

            $removedValues = array_diff($oldValues, $newValues);

            foreach($removedValues as $removedVal) {
                $ordersOnRemoved = \ShopUser::find_by_sql("SELECT shop_user.id FROM `shop_user`, `order`, present WHERE shop_user.company_order_id = ".$co->id." and shop_user.id = `order`.shopuser_id and `order`.present_id = present.id && present.present_list = ".intval($removedVal));
                if(countgf($ordersOnRemoved) > 0) {
                    throw new \Exception("Kan ikke fjerne værdi ".$removedVal.", da der er kort med denne værdi på ordren.");
                }
            }

            // Set average value
            $averageValue = array_sum($newValues) / count($newValues);
            $co->certificate_value = $averageValue;

            // Load shop_users
            $shopUserList = \ShopUser::find("all",array('conditions' => array('company_order_id' => $co->id)));
            foreach($shopUserList as $shopUser) {
                $shopUser->card_values = $data["card_values"];
                $shopUser->save();
            }
            
            if($data["card_values"] != $co->card_values) {
                $co->force_orderconf = 1;
            }

        } else {
            unset($data["card_values"]);
        }


        // Update attributes
        $co->update_attributes($data);
        $co->nav_synced = 0;

        if($sendOB) {
            $co->force_orderconf = 1;
        }

        $co->save();

        \ActionLog::logAction("CompanyOrderUpdated", "Cardshop ordre opdateret: ".$co->order_no." - ".$co->shop_name.": ".$co->quantity." kort",json_encode($data),0,$co->shop_id,$co->company_id,$co->id,0,0,0);


        // Update order restrictions
        self::updateProductRestrictions($co);

        return $co;

    }

    public static function moveExpireDate($companyorderid,$expireDate)
    {
        $co = \CompanyOrder::find(intval($companyorderid));
        $co = self::moveOrderExpireDate($co,$expireDate);
        $co->nav_synced = 0;
        $co->save();

        self::updateProductRestrictions($co);

        return $co;
    }

    private static function moveOrderExpireDate(\CompanyOrder $companyOrder,$expireDate)
    {

        // Check the expire date has changed
        $oldExpireDate = $companyOrder->expire_date->format('Y-m-d');
        if($companyOrder->expire_date->format('Y-m-d') == $expireDate) {
            return true;
        }

        // Order is closed, do not move cards
        if($companyOrder->order_state == 9 || $companyOrder->order_state == 10) {
            throw new \Exception("Order is closed, can't move cards");
        }

        // Get new expire date
        $shopSettings = new CardshopSettingsLogic($companyOrder->shop_id);
        $expireDateObj = $shopSettings->getWeekByExpireDate($expireDate);

        // Invalid expire date
        if($expireDateObj == null) {
            throw new \Exception("Could not find expire date: ".$expireDate);
        }

        // Cards closed, do not move deadline
        else if($expireDateObj->isSaleOpen(false) == false) {
            throw new \Exception("The expire date is closed.");
        }

        // Update order
        $companyOrder->expire_date = $expireDateObj->getExpireDate()->expire_date->format("Y-m-d");

        // Get shopusers on the order
        $shopUserList = \ShopUser::find("all",array('conditions' => array('company_order_id' => $companyOrder->id)));
        foreach($shopUserList as $shopUser) {
            $shopUser->expire_date = $expireDateObj->getExpireDateText();
            $shopUser->is_delivery = $expireDateObj->isDelivery() ? 1 : 0;
            $shopUser->save();
        }

        \ActionLog::logAction("OrderMoveExpireDate", "Udløbsdato opdateret på ordre: ".$companyOrder->order_no.", fra ".$oldExpireDate." til ".$companyOrder->expire_date->format('Y-m-d'),"",0,$companyOrder->shop_id,$companyOrder->company_id,$companyOrder->id,0,0,0);

        return $companyOrder;
    }

    public static function countActiveCards($CompanyOrderID) {
        $activeCards = \ShopUser::find("all",array('conditions' => array("blocked" => 0, "is_demo" => 0,'company_order_id' => intval($CompanyOrderID))));
        return countgf($activeCards);
    }

    public static function updateOrderItem($CompanyOrderID,$OrderItemData)
    {
        $orderItemData = self::updateOrderItems($CompanyOrderID,array($OrderItemData));
        return $orderItemData[0];
    }

    public static function updateOrderItems($CompanyOrderID,$OrderItemList)
    {

        // Load company order, shopsettings and products
        $companyorder = \CompanyOrder::find(intval($CompanyOrderID));
        $shopSettings = new CardshopSettingsLogic($companyorder->shop_id);
        $productMap = $shopSettings->getProductMap();
        $activeCards = self::countActiveCards($companyorder->id);
        $shopExpireDate = $shopSettings->getWeekByExpireDate($companyorder->expire_date);

        // Process each order item
        $returnList = array();
        foreach($OrderItemList as $OrderItemData) {
            $returnList[] = self::updateCompanyOrderItem($OrderItemData,$companyorder,$productMap,$shopExpireDate,$activeCards);
        }

        // Validate companyorderitems
        self::validateOrderItems($companyorder,$shopSettings);

        // Save companyorder
        $companyorder->nav_synced = 0;
        $companyorder->save();

        // Return companyorderitem
        return $returnList;

    }

    private static function updateCompanyOrderItem($OrderItemData,&$companyorder,$productMap,$shopExpireDate,$activeCards)
    {

        // Check product
        if(!isset($OrderItemData["type"]) || trimgf($OrderItemData["type"]) == "" || !isset($productMap[$OrderItemData["type"]])) {
            throw new \Exception("Invalid product type ".(isset($OrderItemData["type"]) ? $OrderItemData["type"] : "(not provided)"));
        }

        // Get product
        $product = $productMap[$OrderItemData["type"]];

        // Load from db
        $companyorderitemList = \CompanyOrderItem::find('all',array("conditions" => array("companyorder_id" => $companyorder->id,"type" => $product->getCode())));
        $companyorderitem = null;

        // Create new
        if(count($companyorderitemList) == 0) {
            $companyorderitem = new \CompanyOrderItem();
            $companyorderitem->companyorder_id = $companyorder->id;
            $companyorderitem->type = $product->getCode();
        }

        // Multiple, keep first delete others
        else if(count($companyorderitemList) > 1) {
            for($i=0;$i<count($companyorderitemList);$i++) {
                if($i==0) $companyorderitem = $companyorderitemList[0];
                else $companyorderitemList[$i]->delete();
            }
        }

        // Single item, get it
        else $companyorderitem = $companyorderitemList[0];

        // Check item
        if($companyorderitem == null) {
            throw new \Exception("Could not find companyorderitem to update");
        }

        if(intval($OrderItemData["quantity"]) <= 0) {
            $companyorderitem->quantity = 0;
            if(isset($OrderItemData["price"])) {
                $companyorderitem->price = $OrderItemData["price"];
            }
        } else {
            $companyorderitem->quantity = ($product->isPerCard() ? ($activeCards == 0 ? 1 : $activeCards) : intval($OrderItemData["quantity"]));
            $companyorderitem->price = $OrderItemData["price"];
            $companyorderitem->isdefault = ($product->getPrice() == $OrderItemData["price"] ? 1 : 0);
        }

        /*
        if($product->getExtraDataField("restrict") == "privatedelivery" && $shopExpireDate->isDelivery() == false && $companyorderitem->quantity > 0) {
            throw new \Exception("Cant add private delivery service to a non-private delivery order");
        }

        if($product->getExtraDataField("restrict") == "physicalcards" && $companyorder->is_email == 1 && $companyorderitem->quantity > 0) {
            throw new \Exception("Cant add physical card service to a e-mail order");
        }
    */
        // Update on companyorder
        if($companyorderitem->type == "CARRYUP") {
            if($companyorderitem->quantity == 0 && $companyorder->gift_spe_lev > 0) $companyorder->gift_spe_lev = 0;
            else if($companyorderitem->quantity > 0 && $companyorder->gift_spe_lev == 0) $companyorder->gift_spe_lev = 1;
        }

        else if($companyorderitem->type == "DOT") {
            if($companyorderitem->quantity == 0 && $companyorder->dot > 0) $companyorder->dot = 0;
            else if($companyorderitem->quantity > 0 && $companyorder->dot == 0) $companyorder->dot = 1;
        }

        else if($companyorderitem->type == "GIFTWRAP") {
            if($companyorderitem->quantity == 0 && $companyorder->giftwrap > 0) $companyorder->giftwrap = 0;
            else if($companyorderitem->quantity > 0 && $companyorder->giftwrap == 0) $companyorder->giftwrap = 1;
        }

        /*
      // Check default
      if(!isset($OrderItemData["isdefault"])) {
          throw new \Exception("Please provide if orderitem is default state");
      }
      */
      /*

        // Set to default
        if($OrderItemData["isdefault"] == 1) {

            $companyorderitem->quantity = ($product->isPerCard() ? $activeCards : 1);
            $companyorderitem->price = $product->getPrice();
            $companyorderitem->isdefault = 1;

            if(!$product->isDefault()) {
                $companyorderitem->quantity = 0;
            }


        }

        // Not default
        else {
            $companyorderitem->isdefault = 0;
            
            if($product->isPerCard()) {
                $companyorderitem->quantity = $OrderItemData["quantity"] == 0 ? 0 : $activeCards;
            } else {
                $companyorderitem->quantity = $OrderItemData["quantity"];
            }
            $companyorderitem->price = $OrderItemData["price"];
        }

      */

        // Update card values on order and shop_user
        if($companyorderitem->type == "BONUSPRESENTS") {

            $settingsList = \CardshopSettings::find_by_sql("select * from cardshop_settings where shop_id = ".$companyorder->shop_id);
            $settings = $settingsList[0];

            if($companyorderitem->quantity == 0) {
                $companyorder->card_values = intval($settings->card_price/100);
            } else {
                $companyorder->card_values = intval($settings->card_price/100).",".intval(($settings->card_price+($settings->bonus_presents*100))/100);
            }

            $shopUserList = \ShopUser::find("all",array('conditions' => array('company_order_id' => $companyorder->id)));
            foreach($shopUserList as $shopUser) {
                if($shopUser->company_order_id == $companyorder->id && $shopUser->company_order_id > 0 && $shopUser->is_demo == 0 && $shopUser->is_giftcertificate == 1) {
                    $shopUser->card_values = $companyorder->card_values;
                    $shopUser->save();
                }
            }
            
        }


        // Update company order item
        $companyorderitem->save();

        // Return companyorderitem
        return $companyorderitem;

    }

    public static function validateOrderItems($companyorder,$shopSettings=null)
    {

        if($shopSettings == null) {
            $shopSettings = new CardshopSettingsLogic($companyorder->shop_id);
        }

        $productMap = $shopSettings->getProductMap();
        $shopExpireDate = $shopSettings->getWeekByExpireDate($companyorder->expire_date->format("Y-m-d"));
        
        // Find all items
        $companyorderitemList = \CompanyOrderItem::find('all',array("conditions" => array("companyorder_id" => $companyorder->id)));
        foreach($companyorderitemList as $companyorderItem) {

            $product = $productMap[$companyorderItem->type];

            if($product->getExtraDataField("restrict") == "privatedelivery" && $shopExpireDate->isDelivery() == false) {
                if($companyorderItem->quantity > 0) {
                    throw new \Exception($companyorderItem->type." can only be set on private delivery");
                }
            }

            if($product->getExtraDataField("restrict") == "physicalcards" && $companyorder->is_email == 1) {
                if($companyorderItem->quantity > 0) {
                    throw new \Exception($companyorderItem->type." can only be set on physical cards order");
                }
            }

            // TODO - Special delivery must be set to save DOT
            // TODO - Check carryup on all orders on same week
            // TODO - Check giftwrap on all orders on same week
            // TODO - Check DOT on all orders on same week

        }

    }

    private static function updateProductRestrictions($companyorder,$shopSettings = null)
    {

        if($shopSettings == null) {
            $shopSettings = new CardshopSettingsLogic($companyorder->shop_id);
        }

        $productMap = $shopSettings->getProductMap();
        $activeCards = self::countActiveCards($companyorder->id);
        $shopExpireDate = $shopSettings->getWeekByExpireDate($companyorder->expire_date);

        if($shopExpireDate == null) {
            throw new \Exception("Could not find shop expire date for ".$companyorder->expire_date->format("d-m-Y H:i").", shop ".$companyorder->shop_id);
        }

        // Find all items
        $companyorderitemList = \CompanyOrderItem::find('all',array("conditions" => array("companyorder_id" => $companyorder->id)));
        foreach($companyorderitemList as $companyorderItem) {

            if(isset($productMap[$companyorderItem->type])) {

                $product = $productMap[$companyorderItem->type];

                // Update default order lines to default values
                if($companyorderItem->isdefault == 1) {

                    // Default price
                    $companyorderItem->price = $product->getPrice();

                    // If default is not set, set quantity to 0
                    if(!$product->isDefault()) {
                        $companyorderItem->quantity = 0;
                    }
                    // Else set quantity
                    else {
                        $companyorderItem->quantity = ($product->isPerCard() ? $activeCards : 1);
                    }

                }

                if($product->getExtraDataField("restrict") == "privatedelivery" && $shopExpireDate->isDelivery() == false) {
                    $companyorderItem->quantity = 0;
                }

                if($product->getExtraDataField("restrict") == "physicalcards" && $companyorder->is_email == 1) {
                    $companyorderItem->quantity = 0;
                }

            }

        }

    }

    private static function checkInvalidFields($orderData,$company)
    {
        // Check for invalid fields
        $invalidFields = array("id","order_no","company_id","shop_id","quantity","is_email","certificate_no_begin","certificate_no_end","certificate_value","nav_done","nav_synced","nav_lastsync","order_state");
        foreach($invalidFields as $field) {
            if(isset($orderData[$field])) {
                throw new \Exception("Invalid update field set: ".$field);
            }
        }


    }
}
