<?php

namespace GFUnit\cardshop\pluklister;
use GFBiz\Model\Cardshop\ShopMetadata;

class Privatlevering extends PlukReport
{



    public function run() {


        // Get order infor (order, shopuser,company_order, company, present and presentmodel)
        $sql = "SELECT
            company_order.order_no
           	,company.name as company_name
            , company.nav_customer_no
            , `shop_user`.`id` as shopuser_id
            ,`shop_user`.`username`
            , `shop_user`.`expire_date`
            , `order`.`shop_id`
            , `order`.`order_timestamp`
            , present_model.model_present_no
            , present_model.model_name
            , present_model.model_no
            , present_model.fullalias
            , company_order.order_no as ordernumber
            
        FROM
            company_order, company, `order`, shop_user, present, present_model  
        WHERE 
        present.id = `order`.present_id && `order`.present_model_id = present_model.model_id && present_model.language_id = 1 && present_model.present_id = present.id &&
        `order`.`shopuser_id` = `shop_user`.`id` && company_order.company_id = company.id && ( `shop_user`.expire_date = '".$this->expire."' && `shop_user`.shop_id = ".$this->shopid." && `shop_user`.`blocked` =0 && shop_user.shutdown = 0 AND `shop_user`.`is_delivery` = 1) and company_order.id = shop_user.company_order_id && company_order.order_state not in (7,8)
        ORDER BY company_order.cvr, company_order.order_no, `order`.`present_name`, `order`.`present_model_name` ";

        $orderList = \Order::find_by_sql($sql);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=privatleveringsliste-'.$this->shop->name.'-'.date("d-m-Y").'.csv');

        // create a file pointer connected to the output stream
        $output = fopen('php://output', 'w');

        // Write headlines
        fwrite($output,'Gavekort nr;');
        fwrite($output,'Navn;');
        fwrite($output,'Email;');
        fwrite($output,'Mobil;');
        fwrite($output,'Adresse;');
        fwrite($output,'Adresse2;');
        fwrite($output,'Postnr;');
        fwrite($output,'By;');
        fwrite($output,'Land;');
        fwrite($output,'Varebeskrivelse;');
        fwrite($output,'Farve/model/version;');
        fwrite($output,'Varenr;');
        fwrite($output,'Alias;');
        fwrite($output,'Virksomhed;');
        fwrite($output,'BS nr;');
        fwrite($output,'Nav debitor nr;');
        fwrite($output,'Oprettet;');
        fwrite($output,'Total;');
        fwrite($output,'Remaining;');
        fwrite($output,'Due;');
        fwrite($output,'Status;');
        fwrite($output,"\n");

        foreach($orderList as $order) {

            // Get user attributes
            $userData = $this->getUserData($order->shopuser_id,$order->shop_id);
            $outputData = array();


            $outputData[] = $order->username;

            $outputData[] = $userData["name"];
            $outputData[] = $userData["email"];
            $outputData[] = $userData["telefon"];
            $outputData[] = $userData["address"];
            $outputData[] = $userData["address2"];
            $outputData[] = $userData["postnr"];
            $outputData[] = $userData["bynavn"];
            $outputData[] = $userData["land"];

            $outputData[] = $order->model_name;
            $outputData[] = $order->model_no;
            $outputData[] = $order->model_present_no;
            $outputData[] = $this->fullalias($this->shopid, $order->fullalias);



            $outputData[] = $order->company_name;
            $outputData[] = $order->ordernumber;
            $outputData[] = $order->nav_customer_no;
            $outputData[] = $order->order_timestamp->format('Y-m-d H:i:s');

            $client = $this->getOrderWS($this->shopSettings->language_code);
            $deliveryState = "Unknown";

            $orderStatus = $client->getStatus($order->ordernumber);
            if($orderStatus != null) {


                $outputData[] = $orderStatus->getPrepaymentAmount();
                $outputData[] = $orderStatus->getRemPrepaymentAmountLCY();
                $outputData[] = $orderStatus->getDuePrepaymentAmountLCY();

                if(intval($orderStatus->getRemPrepaymentAmountLCY()) > 0) {
                    $deliveryState = "STOP: ".$orderStatus->getRemPrepaymentAmountLCY()." not paid";
                } else {
                    $deliveryState = "OK";
                }

            } else {
                $outputData[] = 'No balance data';
                $outputData[] = '';
                $outputData[] = '';
                $deliveryState = "STOP: No nav data, wait for nav or manual override";
            }

            $outputData[] = $deliveryState;
            fwrite($output,utf8_decode(implode(";",$outputData))."\n");
        }

        fwrite($output,"\n");


    }

    protected function fullalias($shopid, $alias) {
        return ShopMetadata::getShopValueAlias($shopid) . (strlen(intval($alias)) == 1 ? "0" : "") . $alias;
    }
    
    private $orderWs = array();

    private function getOrderWS($countryCode)
    {
        if(intval($countryCode) <= 0) {
            throw new \Exception("Trying to create order service with no nav country");
        }
        if(isset($this->orderWs[intval($countryCode)])) {
            return $this->orderWs[intval($countryCode)];
        }
        $this->orderWs[intval($countryCode)] = new \GFCommon\Model\Navision\OrderStatusWS(intval($countryCode));
        return $this->orderWs[intval($countryCode)];
    }

    private function getUserData($shopuserid,$shopid)
    {

        $nameAttributes = ShopMetadata::getNameAttrList();
        $adress1Attributes = ShopMetadata::getAddress1AttrList();
        $adress2Attributes = ShopMetadata::getAddress2AttrList();
        $postnrAttributes = ShopMetadata::getZipAttrList();
        $bynavnAttributes = ShopMetadata::getCityAttrList();
        $emailAttributes = ShopMetadata::getEmailAttrList();
        $phoneAttributes = ShopMetadata::getPhoneAttrList();


        $shopuserData = array(
            "name" => "-",
            "address" => "-",
            "address2" => "-",
            "postnr" => "-",
            "bynavn" => "-",
            "land" => $this->getCountry($shopid),
            "telefon" => "-",
            "email" => "-"
        );

        $userAttributes = \UserAttribute::find_by_sql("SELECT * FROM user_attribute WHERE shopuser_id = ".$shopuserid);
        foreach($userAttributes as $attribute) {

            if(in_array($attribute->attribute_id,$nameAttributes)) $shopuserData["name"] = $attribute->attribute_value;
            if(in_array($attribute->attribute_id,$adress1Attributes)) $shopuserData["address"] = $attribute->attribute_value;
            if(in_array($attribute->attribute_id,$adress2Attributes)) $shopuserData["address2"] = $attribute->attribute_value;
            if(in_array($attribute->attribute_id,$postnrAttributes)) $shopuserData["postnr"] = $attribute->attribute_value;
            if(in_array($attribute->attribute_id,$bynavnAttributes)) $shopuserData["bynavn"] = $attribute->attribute_value;
            if(in_array($attribute->attribute_id,$emailAttributes)) $shopuserData["email"] = $attribute->attribute_value;
            if(in_array($attribute->attribute_id,$phoneAttributes)) $shopuserData["telefon"] = $attribute->attribute_value;

        }

        return $shopuserData;
    }

    private function getCountry($shopid)
    {

        return ShopMetadata::getShopCountry($shopid);
    }

}