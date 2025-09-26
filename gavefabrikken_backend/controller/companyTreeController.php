<?php
// Controller CompanyNotesEx
// Date created  Wed, 11 Oct 2017 14:30:30 +0200
// Created by Bitworks
class companytreeController Extends baseController {
    public function Index() {
        echo "hddej";
    }
    public function add()
    {
           echo "hejdd";
    }
    public function remove()
    {

    }




/***** privat help functions *****/
    public function addMultiNodes()
    {
        $postData = $_POST["data"];
        $path = $postData["path"];
        $fn = fopen(getcwd()."/".$path,"r");
          while(! feof($fn))  {
            $result = fgets($fn);
            $pieces = explode(";", $result);
            if(sizeofgf($pieces) ==8 && trimgf(implode("",$pieces)) != ""){

                $companydata = [];
                $pos = strpos($pieces[0], "navne");
                if($pos === false){
                    $companydata["pid"] = $postData["pid"];
                    $companydata["name"] = $postData["name"];
                    $companydata["bill_to_address"] =  $postData["bill_to_address"];
                    $companydata["bill_to_address_2"] = $postData["bill_to_address_2"];
                    $companydata["bill_to_postal_code"] = $postData["bill_to_postal_code"];
                    $companydata["bill_to_city"] = $postData["bill_to_city"];
                    $companydata["cvr"] = $postData["cvr"];
                    $companydata["ean"] = $postData["ean"];

                    $companydata["ship_to_company"] = $pieces[0];
                    $companydata["ship_to_address"] = $pieces[1];
                    $companydata["ship_to_address_2"] = $pieces[2];
                    $companydata["ship_to_postal_code"] = $pieces[3];
                    $companydata["ship_to_city"] = $pieces[4];
                    $companydata["contact_name"] = $pieces[5];
                    $companydata["contact_phone"] = $pieces[6];
                    $companydata["contact_email"] = $pieces[7];

                   // $companydata =  //(array) $_POST['companydata'];
                    $companydata['username'] = $companydata["cvr"];
                    $companydata['password'] = $companydata["cvr"];
                    $companydata['is_gift_certificate'] = 1;
                    $company = new Company();
                    $company->update_attributes($companydata);
                    $company->save();
               }
            }

          }
        fclose($fn);
        response::success(make_json("result", $company));
    }



}
?>