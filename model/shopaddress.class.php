<?php
// Model ShopAddress
//***************************************************************
class ShopAddress extends BaseModel {
    static $table_name  = "shop_address";
    static $primary_key = "id";

    //Relations
    static $before_create =  array('onBeforeCreate');
    static $after_create =  array('onAfterCreate');

    static $before_update =  array('onBeforeUpdate');
    static $after_update =  array('onAfterUpdate');

    static $before_destroy =  array('onBeforeDestroy');
    static $after_destroy =  array('onAfterDestroy');

    // Trigger functions
    function onBeforeSave() {}
    function onAfterSave()  {}
    function onBeforeCreate() {
        $this->validateFields();
    }
    function onAfterCreate()  {}

    function onBeforeUpdate() {
        $this->validateFields();
    }

    function onAfterUpdate()  {}
    function onBeforeDestroy() {}
    function onAfterDestroy()  {

    }
    function validateFields() {

        testRequired($this,'shop_id');
        testRequired($this,'name');
        //testRequired($this,'address');
        //testRequired($this,'zip');
        //testRequired($this,'city');
        //testRequired($this,'att');
        //testRequired($this,'country');

        //Check parent records exists here
        Shop::find($this->shop_id);

        $this->name = trimgf($this->name);
        $this->address = trimgf($this->address);
        $this->zip = trimgf($this->zip);
        $this->city = trimgf($this->city);
        $this->country = trimgf($this->country);
        $this->att = trimgf($this->att);
        $this->phone = trimgf($this->phone);
    }
//---------------------------------------------------------------------------------------
// Static CRUD Methods
//---------------------------------------------------------------------------------------

    static public function createShopAddress($data) {
        $shopaddress = new ShopAddress($data);
        $shopaddress->save();
        return($shopaddress);
    }

    static public function readShopAddress($id) {
        $shopaddress = ShopAddress::find($id);
        return($shopaddress);
    }

    static public function updateShopAddress($data) {
        $shopaddress = ShopAddress::find($data['id']);
        $shopaddress->update_attributes($data);
        $shopaddress->save();
        return($shopaddress);
    }

    static public function deleteShopAddress($id,$realDelete = false) {

			$shopattribute = ShopAddress::find($id);
			$shopaddress->delete();
    }
    
//---------------------------------------------------------------------------------------
// Helpers
//---------------------------------------------------------------------------------------

	static public function updateShopAddresses($shopid,$addressjson)
	{

			// Find existing addresses and create map
			$shopAddressList = ShopAddress::find('all', array('conditions' => "shop_id = ".intval($shopid)));
			$addressMap = array();
			for($i=0;$i<count($shopAddressList);$i++)
			{
					$addressMap[$shopAddressList[$i]->id] = $shopAddressList[$i];
			}
			
			// Parse addresses
			$addressList = (array) json_decode($addressjson,true);
			if($addressList == null) return;
			
			$count = 1;
			
			// Update /insert
			for($i=0;$i<count($addressList);$i++)
			{
				$addressData = $addressList[$i];
				if(isset($addressData["id"]) && intval($addressData["id"]) > 0 && isset($addressMap[intval($addressData["id"])]))
				{
					$address = $addressMap[intval($addressData["id"])];
					unset($addressMap[intval($addressData["id"])]);
				}
				else
				{
					$address = new ShopAddress();
				}
				
				$address->shop_id = $shopid;				
				$address->name = $addressData["name"];
				$address->address = $addressData["address"];
				$address->zip = $addressData["zip"];
				$address->city = $addressData["city"];
				$address->country = $addressData["country"];
                $address->att = $addressData["att"];
                $address->phone = $addressData["phone"];
                $address->index = $count;
				$address->locations = $addressData["locations"];
                $address->vatno = $addressData["vatno"] ?? "";

                $address->freight_note = $addressData["freightnote"] ?? "";
                $address->dot = isset($addressData["dot"]) ? (intvalgf($addressData["dot"]) == 1 ? 1 : 0) : 0;
                $address->carryup = isset($addressData["carryup"]) ? (intvalgf($addressData["carryup"]) == 1 ? 1 : 0) : 0;

                $dotDate = null;
                if(isset($addressData["dotdate"]) && trimgf($addressData["dotdate"]) != "") {
                    try {
                        $dotDate = new \DateTime($addressData["dotdate"]);
                    } catch (\Exception $e) {
                        $dotDate = null;
                    }
                }else {
                    $dotDate = null;
                }

                $address->dot_date = $dotDate;
                $address->carryup_type = isset($addressData["carryuptype"]) ? (intvalgf($addressData["carryuptype"])) : 0;


                $address->save();
				$count++;
							
			}
			
			// Delete unused adresses
			if(count($addressMap) > 0)
			{
				foreach($addressMap as $address)
				{
					$address->delete();
				}
			}
	
	}

}

?>