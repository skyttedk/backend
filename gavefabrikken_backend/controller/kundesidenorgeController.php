<?php
  if (session_status() == PHP_SESSION_NONE) session_start();
Class KundesidenorgeController Extends baseController {

    public function index() {
      $_SESSION["syslogin".GFConfig::SALES_SEASON] = "40";
      $dummy['link']  = "dsf";
      response::success(json_encode($dummy));
    }

    public function login() {

      $dummy['link']  = 'sdfsd';
      response::success(json_encode($dummy));
    }

    public function sendTo()
    {

    }



    public function getData()
    {

        $email = $_POST["email"];

      $data = CompanyOrder::find_by_sql("SELECT company.cvr,company.ean,company.name, company.ship_to_company,company.ship_to_attention,company.ship_to_address,company.ship_to_address_2,company.ship_to_address_2,company.ship_to_postal_code,company.ship_to_city,company.ship_to_country,company.contact_name, shop_user.username, shop_user.password, shop_user.expire_date, `order`.`user_name`,`order`.`user_email`,`order`.`present_name`,`order`.`present_model_name`, `order`.order_no, shop_user.company_id,shop_user.shop_id from company
inner JOIN shop_user ON shop_user.company_id = company.id
left JOIN `order` ON `order`.user_username = shop_user.username

where company.id in (select id from company where  contact_email = '".$email."'  and  deleted = 0   ) and shop_user.blocked = 0 and  shop_user.expire_date = '2017-11-05' ORDER BY company.ship_to_city, `order`.`user_name`  ASC");
       response::success(make_json("result", $data));
    }
    public function getExcel()
    {
         $email = $_GET["email"];
         $sql = "SELECT company.cvr,company.ean,company.name, company.ship_to_company,company.ship_to_attention,company.ship_to_address,company.ship_to_address_2,company.ship_to_postal_code,company.ship_to_city,company.ship_to_country,company.contact_name, shop_user.username, shop_user.password, shop_user.expire_date, `order`.`user_name`,`order`.`user_email`,`order`.`present_name`,`order`.`present_model_name`, `order`.order_no, shop_user.company_id,shop_user.shop_id from company
        inner JOIN shop_user ON shop_user.company_id = company.id
        left JOIN `order` ON `order`.user_username = shop_user.username
        where company.id  in (select id from company where  contact_email = '".$email."'  and  deleted = 0   ) and shop_user.blocked = 0   and shop_user.expire_date = '2017-11-05' ORDER BY company.ship_to_city, `order`.`user_name` ASC
        ";
        $rs = Dbsqli::getSql2($sql);
       // print_r($rs);
     //   tr><th>Firma navn</th><th>Cvr</th><th>Ean</th><th>Adresse</th><th>By</th><th>Kortnummer</th><th>Adgangskode</th><th>Udløbsdato</th><th>Navn</th><th>Email</th><th>Gave</th><th>Model</th><th>Ordre nr.</th></tr></thead> <tbody> "

        $cvr = "";
       $cvr.= "Firma navn;Org.nummer;adresse;Kortnummer;passord;Gavevalg deadline;Navn;Email;Gave;Model;Bestillingsnr. \n";
        foreach($rs as $key=>$item){
            $model = $item["present_model_name"];
            $model = str_replace("###","",$model);
            $model = str_replace("###","",$model);
            $model = str_replace("###","",$model);
            $adress = "";

            if($item["ship_to_company"] != null || $item["ship_to_company"] != "" ){
              $adress.= $item["ship_to_company"].", ";
            }
            $adress.= $item["ship_to_address"];
            if($item["ship_to_address_2"] != null || $item["ship_to_address_2"] != "" ){
              $adress.= $item["ship_to_address_2"].", ";
            }
            $adress.= $item["ship_to_postal_code"].", ".$item["ship_to_city"];
            $cvr.= $item["name"].";".$item["cvr"].";".$adress.";".$item["username"].";".$item["password"].";".$item["expire_date"].";".$item["user_name"].";".$item["user_email"].";".$item["present_name"].";".$model.";".$item["order_no"]."\n";

        }
        header('Content-Type: application/csv');
        header('Content-Disposition: attachement; filename="data.csv"');
        echo $cvr; exit();
    }
}
?>
