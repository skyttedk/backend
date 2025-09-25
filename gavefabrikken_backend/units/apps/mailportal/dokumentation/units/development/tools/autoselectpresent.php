<?php

namespace GFUnit\development\tools;

class AutoselectPresent
{

   
    public function dispatch()
    {

        if(isset($_POST["action"]) && $_POST["action"] == "create") {
            $this->runselect();
            echo "<hr>";
        }

        $this->showform();
        
    }
    
    public function showform()
    {
        ?><form action="/gavefabrikken_backend/index.php?rt=unit/development/tools/autoselect" method="post">
        <h3>Autovalg på ordre</h3>
        <div>Bemærk at dette overskriver eksisterende valg.</div>
        <table>
            <tr><td>Company order id </td><td><input type="text" name="companyorderid" size="10"></td><td></td></tr>
            <tr><td>%-del autoval</td><td><input type="text" name="autovalg" size="10" value="10"></td><td>hvor mange skal ikke have valgt gave</td></tr>
            <tr><td>max valg</td><td><input type="text" name="maxorders" size="10" value="100"></td><td>antal valg der max skal laves</td></tr>
            <tr><td>&nbsp;<input type="hidden" name="action" value="create"></td><td><input type="submit" value="Kør valg"></td><td>&nbsp;</td></tr>
        </table>
    </form><?php
    }
    
    public function runselect()
    {

        echo "<h3>Run selection</h3>";
        
        // Get inputs
        $companyorderid = isset($_POST["companyorderid"]) ? intval($_POST["companyorderid"]) : 0;
        $autoChoice = isset($_POST["autovalg"]) ? intval($_POST["autovalg"]) : 0;
        $maxChoice = isset($_POST["maxorders"]) ? intval($_POST["maxorders"]) : 0;



        // Company order
        $companyOrder = \CompanyOrder::find($companyorderid);
        echo "Order no: ".$companyOrder->order_no."<br>";

        // Load shopusers
        $shopusers = \ShopUser::find_by_sql("SELECT * FROM shop_user WHERE company_order_id = ".intval($companyorderid)." && is_demo = 0 && is_giftcertificate = 1 && blocked = 0");
        echo "FOUND ".countgf($shopusers)." ACTIVE CARDS<br>";

        // Load presents
        $presentmodellist = \PresentModel::find_by_sql("SELECT present.*, present_model.* FROM `present`, present_model WHERE present_model.present_id = present.id && shop_id = ".intval($companyOrder->shop_id)." && language_id = 1");
        echo "FOUND ".countgf($presentmodellist)." presents<br>";

        // Load user attributes
        $userattributes = \UserAttribute::find_by_sql("SELECT * FROM user_attribute WHERE shop_id = ".intval($companyOrder->shop_id));
        $userdata = array();
        foreach($userattributes as $userattribute) {
            if(!isset($userdata[$userattribute->shopuser_id])) $userdata[$userattribute->shopuser_id] = array();
            $userdata[$userattribute->shopuser_id][] = $userattribute;
        }

        echo "Ready to select presents:<br>";
        // Process each shop user
        foreach($shopusers as $shopuser)
        {

            echo "<br>Processing shopuser: ".$shopuser->id." - ".$shopuser->username." - ";

            // Do not select present
            if(rand(1,100) <= $autoChoice) {
                echo "<br>DO NOT SELECT";
                \Order::table()->delete(array('shopuser_id' => $shopuser->id));
            }

            // Select present
            else
            {

                $presentindex = array_rand($presentmodellist);
                $presentmodel = $presentmodellist[$presentindex];

                echo "<br>SELECT PRESENT - ".$presentmodel->nav_name." - ".$presentmodel->model_name;

                // Construct data
                $orderData = array(
                    "shopId" => $companyOrder->shop_id,
                    "userId" => $shopuser->id,
                    "presentsId" => $presentmodel->present_id,
                    "model_id" => $presentmodel->model_id,
                    "model" => $presentmodel->model_name.'###'.$presentmodel->model_no,
                    "modelData" => $presentmodel->model_present_no,
                    "_attributes" => array()
                );

                foreach($userdata[$shopuser->id] as $userattribute) {
                    if($userattribute->is_email && $userattribute->attribute_value == "") $userattribute->attribute_value = $shopuser->id."@interactive.dk";
                    $orderData["_attributes"][] = array("feltKey" => $userattribute->attribute_id,"feltVal" => $userattribute->attribute_value);
                }
                $orderData["_attributes"] = json_encode($orderData["_attributes"]);

                echo "<pre>".print_r($orderData,true)."</pre>";

                // Create order
                $order = \Order::createOrder($orderData);

            }


            echo "<br><br>";

        }

        \System::connection()->commit();
        
    }


}