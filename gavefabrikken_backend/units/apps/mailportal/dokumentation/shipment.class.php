<?php

class Shipment extends BaseModel {

    static $table_name = "shipment";
    static $primary_key = "id";

    //Relations
    static $has_many = array();

    /**
     * DB EVENTS
     */

    static $before_create = array('onBeforeCreate');
    function onBeforeCreate() {
        $this->created_date = date('d-m-Y H:i:s');
        $this->validateFields();
    }

    static $after_create = array('onAfterCreate');
    function onAfterCreate() { }

    static $before_update = array('onBeforeUpdate');
    function onBeforeUpdate() {
        $this->validateFields();
    }

    static $after_update = array('onAfterUpdate');
    function onAfterUpdate() { }

    static $before_destroy = array('onBeforeDestroy');
    function onBeforeDestroy() { }

    static $after_destroy = array('onAfterDestroy');
    function onAfterDestroy() { }

    /**
     * FIELD VALIDATIN
     */

    function validateFields() {

    }

    /**
     * Special getters
     */

    public function getUniqueVarenrList($expandBom=false,$failOnVarenrCheck=false)
    {
        $itemlist = $this->getVarenrCountList(true,$expandBom,$failOnVarenrCheck);
        $itemnolist = array();
        foreach ($itemlist as $item) {
            $itemnolist[] = $item["itemno"];
        }
        return $itemnolist;
    }

    public function getVarenrCountList($collapseSimilar=true,$expandBOM = false,$failOnVarenrCheck=false) {

        // Get varenr from shipment structure
        $items = array();
        if($this->quantity != 0) {
            $items[] = array("itemno" => $this->itemno,"quantity" => $this->quantity);
        }
        if($this->quantity2 != 0) {
            $items[] = array("itemno" => $this->itemno2,"quantity" => $this->quantity2);
        }
        if($this->quantity3 != 0) {
            $items[] = array("itemno" => $this->itemno3,"quantity" => $this->quantity3);
        }
        if($this->quantity4 != 0) {
            $items[] = array("itemno" => $this->itemno4,"quantity" => $this->quantity4);
        }
        if($this->quantity5 != 0) {
            $items[] = array("itemno" => $this->itemno5,"quantity" => $this->quantity5);
        }

        // Expand to bom items
        if($expandBOM == true || $failOnVarenrCheck == true) {
            $newItems = array();

            foreach($items as $item) {
                $varenrList = \NavisionItem::find_by_sql("SELECT * FROM `navision_item` WHERE `language_id` = 1 AND `no` LIKE '".$item["itemno"]."' AND `deleted` IS NULL");
                $varenr = countgf($varenrList) > 0 ? $varenrList[0] : null;

                if($varenr == null || $varenr->deleted != null) {
                    $newItems[] = $item;
                    if($failOnVarenrCheck) throw new \Exception("Could not find varenr ".$item["itemno"]." in nav items");
                } else if($varenr->blocked == 1) {
                    $newItems[] = $item;
                    if($failOnVarenrCheck) throw new \Exception("Varenr ".$item["itemno"]." is blocked in nav items");
                } else if($varenr->assembly_bom == 1 && $expandBOM == true) {
                    $bomItems = \NavisionBomItem::find_by_sql("SELECT * FROM `navision_bomitem` WHERE `language_id` = 1 AND `parent_item_no` LIKE '".$item["itemno"]."' AND `deleted` IS NULL");
                    if (count($bomItems) > 0) {
                        foreach ($bomItems as $bomItem) {
                            $newItems[] = array("itemno" => $bomItem->no,"name" => $bomItem->description, "quantity" => $bomItem->quantity_per * $item["quantity"]);
                        }
                    } else if($failOnVarenrCheck) throw new \Exception("Could not find bom items on varenr ".$item["itemno"]);
                }
                 else {
                     $item["name"] = $varenr->description;
                     $newItems[] = $item;
                }
            }
            $items = $newItems;
        }

        // Collapse similar varenr
        if($collapseSimilar == true) {

            $newItems = array();
            foreach($items as $item) {
                $exists = false;
                foreach($newItems as $nii => $nir) {
                    if($item["itemno"] == $nir["itemno"]) {
                        $exists = true;
                        $newItems[$nii]["quantity"] += $item["quantity"];
                    }
                }
                if($exists == false) {
                    $newItems[] = $item;
                }
            }
            $items = $newItems;
        }
        return $items;
    }


    public static function getStateList() {

        $stateList = array(
            0 => "Waiting",
            1 => "Ready",
            2 => "Synced",
            3 => "Error",
            4 => "Blocked",
            5 => "Processed externally",
            6 => "Synced home",
            7 => "Ship to address",
            9 => "countryerror"
        );

        return $stateList;
    }

    public static function stateTextList($num=null)
    {
        $stateList = self::getStateList();
        if($num == null) return $stateList;
        else if(isset($stateList[$num])) return $stateList[$num];
        else return "Unknown";
    }

    public function getStateText()
    {
        return self::stateTextList($this->shipment_type);
    }


    /**
     * UPDATE SHIPMEND DATA BACK ON SHOPUSER
     */

    public function updateUserDataFromShipment()
    {

        // Check is privatedelivery
        if($this->shipment_type != "privatedelivery" && $this->shipment_type != "directdelivery") {
            throw new \Exception("Shipment ".$this->id." is not privatedelivery");
        }

        // Check shopuser and order and everything fits
        try{
            $shopuser = \ShopUser::find("first",array("conditions" => array("username" => $this->from_certificate_no,"company_order_id" => $this->companyorder_id,"is_giftcertificate" => 1)));
            if($shopuser == null || $shopuser->id == 0 || $shopuser->username != $this->from_certificate_no) {
                throw new \Exception("Could not find shopuser with username ".$this->from_certificate_no." and company_order_id ".$this->companyorder_id);
            }
        } catch (\Exception $e) {
            throw new \Exception("Could not find shopuser with username ".$this->from_certificate_no." and company_order_id ".$this->companyorder_id);
        }

        try {
            $order = \Order::find($this->to_certificate_no);
            if($order == null || $order->id == 0 || $order->id != $this->to_certificate_no) {
                throw new \Exception("Could not find order with id ".$this->to_certificate_no);
            }
        } catch (\Exception $e) {
            throw new \Exception("Could not find order with id ".$this->to_certificate_no);
        }

        // Create privatedeliverysync class
        $pds = new \GFUnit\navision\syncprivatedelivery\PrivateDeliverySync(false);

        // Hent de eksisterende brugerdata
        $oldUserData = $pds->getUserData($shopuser->id, $shopuser->shop_id);

        // Opdater userAttributes baseret på shipment data
        $userAttributes = \UserAttribute::find('all',array("conditions" => array("shopuser_id" => $shopuser->id)));
        $changes = [];
        $changeFrom = [];
        $changeTo = [];

        foreach ($userAttributes as $attribute) {
            if($shopuser->id == $attribute->shopuser_id) {
                $oldValue = $attribute->attribute_value;
                switch (true) {
                    case in_array($attribute->attribute_id, \GFBiz\Model\Cardshop\ShopMetadata::getNameAttrList()):
                        $attribute->attribute_value = $this->shipto_name;
                        break;
                    case in_array($attribute->attribute_id, \GFBiz\Model\Cardshop\ShopMetadata::getAddress1AttrList()):
                        $attribute->attribute_value = $this->shipto_address;
                        break;
                    case in_array($attribute->attribute_id, \GFBiz\Model\Cardshop\ShopMetadata::getAddress2AttrList()):
                        $attribute->attribute_value = $this->shipto_address2;
                        break;
                    case in_array($attribute->attribute_id, \GFBiz\Model\Cardshop\ShopMetadata::getZipAttrList()):
                        $attribute->attribute_value = $this->shipto_postcode;
                        break;
                    case in_array($attribute->attribute_id, \GFBiz\Model\Cardshop\ShopMetadata::getCityAttrList()):
                        $attribute->attribute_value = $this->shipto_city;
                        break;
                    case in_array($attribute->attribute_id, \GFBiz\Model\Cardshop\ShopMetadata::getEmailAttrList()):
                        $attribute->attribute_value = $this->shipto_email;
                        break;
                    case in_array($attribute->attribute_id, \GFBiz\Model\Cardshop\ShopMetadata::getPhoneAttrList()):
                        $attribute->attribute_value = str_replace(array(" ", "-"), "", trim($this->shipto_phone));
                        break;
                }
                if ($oldValue !== $attribute->attribute_value) {
                    $shopAttribute = \ShopAttribute::find($attribute->attribute_id);
                    $changes[] = "Felt '{$shopAttribute->name}' ændret fra '{$oldValue}' til '{$attribute->attribute_value}'. ";
                    $changeFrom[$attribute->attribute_id] = $oldValue;
                    $changeTo[$attribute->attribute_id] = $attribute->attribute_value;
                    $attribute->save();
                }

            }

        }

        // Hent de opdaterede brugerdata
        $newUserData = $pds->getUserData($shopuser->id, $shopuser->shop_id);

        // Forbered JSON til logning
        $logData = [
            'shopuser_id' => $shopuser->id,
            'old_data' => $oldUserData,
            'new_data' => $newUserData,
            'shipment_data' => [
                'id' => $this->id,
                'shipto_name' => $this->shipto_name,
                'shipto_address' => $this->shipto_address,
                'shipto_address2' => $this->shipto_address2,
                'shipto_postcode' => $this->shipto_postcode,
                'shipto_city' => $this->shipto_city,
                'shipto_country' => $this->shipto_country,
                'shipto_contact' => $this->shipto_contact,
                'shipto_email' => $this->shipto_email,
                'shipto_phone' => $this->shipto_phone
            ],
            'shipment_id' => $this->id,
            'changes' => $changes,
            "change_from" => $changeFrom,
            "change_to" => $changeTo,
            'change_log' => implode(" ", $changes)
        ];
        
        // Return log object
        return $logData;

    }


}

