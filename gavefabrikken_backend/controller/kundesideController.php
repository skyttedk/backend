<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

  if(session_status() == PHP_SESSION_NONE) session_start();
Class KundesideController Extends baseController {

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


    public function getShopNames(){
      $sql = "SELECT id,`alias` FROM `shop` where is_gift_certificate = 1";
      $data = Dbsqli::getSql2($sql);
      $res = [];
      foreach($data as $key=>$val){
       $res[$val["id"]] = $val["alias"];
      }
      return $res;

    }
    private function loadData($token,$dato=""){
      $email = "";
      $parentID = "";
      $dato =  trimgf($dato);
      $companyList = [];





      // child check
      $pidRs = Company::find_by_sql("SELECT id,pid,contact_email from company where token = '".$token."'");
      // check if consisensi
      /*
      if( $this->diffInContactperson(strtolower($pidRs[0]->attributes["contact_email"]),$pidRs[0]->attributes["id"]) == true)
      {
            echo json_encode(array("status"=>"2"));
            die("");
      }
       */
      $pid =  $pidRs[0]->attributes["pid"];

      if($pid*1 > 0){

        $tokenRs = Company::find_by_sql("SELECT id from company where id = ".$pid);
      } else {
        $tokenRs = Company::find_by_sql("SELECT id from company where token = '".$token."'");
      }




      if(sizeofgf($tokenRs) > 0){
          $parentID = $tokenRs[0]->attributes["id"];
           $contact_Company = Company::find_by_id($parentID);




          $relatedCompanys = Company::find_by_sql("SELECT id,contact_email from company where cvr
          in( select cvr from company where id = ".$parentID." && cvr != '' && name != 'child' && deleted = 0 && active = 1 && shutdown = 0 && ean = '' ) and pid = 0  && contact_email = '".$contact_Company->contact_email."' ");
          if(sizeofgf($relatedCompanys) > 0 && $contact_Company->contact_email != "" ){
            foreach($relatedCompanys as $company){
                if( $this->diffInContactperson(strtolower($company->contact_email),$company->id) == false)
                {
                    $companyList[] = $company->id;
                }

            }
          }
          $companyList[] = $parentID;

      } else {
         die("Error has occured");
      }

      $sqlDato = "";
      if($dato != ""){
          $sqlDato = "and shop_user.expire_date='".$dato."'";
      }


      $data = CompanyOrder::find_by_sql("SELECT shop_user.blocked, present_model.model_present_no, present_model.fullalias,company.cvr,company.ean,company.name, company.ship_to_company,company.ship_to_attention,company.ship_to_address,company.ship_to_address_2,company.ship_to_address_2,company.ship_to_postal_code,company.ship_to_city,company.ship_to_country,company.contact_name, shop_user.username, shop_user.password, IF(company_order.floating_expire_date IS NULL,company_order.expire_date,DATE(company_order.floating_expire_date)) as expire_date, `order`.`user_name`,`order`.`user_email`,`order`.`present_name`,`present_model`.model_name as `present_model_name`, `order`.order_no, shop_user.company_id,shop_user.shop_id from company
        inner JOIN shop_user ON shop_user.company_id = company.id
		inner JOIN company_order on shop_user.company_order_id = company_order.id

        left JOIN `order` ON `order`.user_username = shop_user.username
        left join present_model on  `order`.`present_model_id` =  present_model.model_id  and present_model.language_id = 1
            where (company.id in (select id from company where  pid in (".implode(', ', $companyList)." )  and  deleted = 0   ) || company.id in (".implode(', ', $companyList)." ) ) and
            shop_user.is_giftcertificate = 1 and
            shop_user.blocked = '0' ".$sqlDato. " ORDER BY  shop_user.username  ASC" );


/*
        $data = CompanyOrder::find_by_sql("SELECT shop_user.blocked, present_model.model_present_no, present_model.fullalias,company.cvr,company.ean,company.name, company.ship_to_company,company.ship_to_attention,company.ship_to_address,company.ship_to_address_2,company.ship_to_address_2,company.ship_to_postal_code,company.ship_to_city,company.ship_to_country,company.contact_name, shop_user.username, shop_user.password, shop_user.expire_date, `order`.`user_name`,`order`.`user_email`,`order`.`present_name`,`order`.`present_model_name`, `order`.order_no, shop_user.company_id,shop_user.shop_id from company
        inner JOIN shop_user ON shop_user.company_id = company.id

        left JOIN `order` ON `order`.user_username = shop_user.username
        left join present_model on  `order`.`present_model_id` =  present_model.model_id  and present_model.language_id = 1
            where (company.id in (select id from company where  pid in (".implode(', ', $companyList)." )  and  deleted = 0   ) || company.id in (".implode(', ', $companyList)." ) ) and
            shop_user.is_giftcertificate = 1 and
            shop_user.blocked = '0' ".$sqlDato. " ORDER BY  shop_user.username  ASC" );
*/

        return $data;

/*
        $data = CompanyOrder::find_by_sql("SELECT shop_user.blocked, present_model.model_present_no, present_model.fullalias,company.cvr,company.ean,company.name, company.ship_to_company,company.ship_to_attention,company.ship_to_address,company.ship_to_address_2,company.ship_to_address_2,company.ship_to_postal_code,company.ship_to_city,company.ship_to_country,company.contact_name, shop_user.username, shop_user.password, IF(company_order.floating_expire_date IS NULL,company_order.expire_date,DATE(company_order.floating_expire_date)) as expire_date, `order`.`user_name`,`order`.`user_email`,`order`.`present_name`,`order`.`present_model_name`, `order`.order_no, shop_user.company_id,shop_user.shop_id from company
        inner JOIN shop_user ON shop_user.company_id = company_id
        inner JOIN company_order ON company_order.id = shop_user.company_order_id
        left JOIN `order` ON `order`.shopuser_id = shop_user.id
        left join present_model on  `order`.`present_model_id` =  present_model.model_id  and present_model.language_id = 1
            where (company.id in (select id from company where  pid in (".implode(', ', $companyList)." )  and  deleted = 0   ) || company.id in (".implode(', ', $companyList)." ) ) and
            shop_user.is_giftcertificate = 1 and
            shop_user.blocked = '0' ".$sqlDato. " ORDER BY  shop_user.username  ASC" );
*/

      return $data;
      // where (company.id in (select id from company where  pid = '".$parentID."'  and  deleted = 0   ) || company.id in (".implode(', ', $companyList)." ) ) and
    }
    public function diffInContactperson($contact,$companyID){
        $rsCount =CompanyOrder::find_by_sql("select * from company_order where  company_id = ".$companyID." and is_cancelled = 0  ");
        $rs =CompanyOrder::find_by_sql("select * from company_order where contact_email = '".strtolower($contact)."'  and company_id = ".$companyID."  and is_cancelled = 0  ");
        if(sizeofgf($rs) > 0 ){
            if(sizeofgf($rs) == sizeofgf($rsCount) ){
                return false;
            } else {
                return true;
            }
        } else {
           return true;
        }
    }



    public function getData()
    {
      $data = $this->loadData($_POST["token"],$_POST["dato"]);



     // print_R($data);
      //  $res = ["data"=>$data,"shopNames"=>$this->getShopNames()] ;
      $shopName = $this->getShopNames();

    foreach($data as $key=>$val){
      $val->attributes["shopName"] = "";
      if($val->attributes["shop_id"] != ""){
          $val->attributes["shopName"] = $shopName[$val->attributes["shop_id"]] ;
      }
    }
       response::success(make_json("result", $data));
    }
    public function getExcel()
    {

        $shopName = $this->getShopNames();
         $dato = "";

         $selected =  $_GET["selected"];
         $onlySelected = "";
         $sqlDato = "";
         if($selected == "true") $onlySelected = "go";




       $rs = $this->loadData($_GET["token"],$_GET["dato"]);




     //   tr><th>Firma navn</th><th>Cvr</th><th>Ean</th><th>Adresse</th><th>By</th><th>Kortnummer</th><th>Adgangskode</th><th>Udløbsdato</th><th>Navn</th><th>Email</th><th>Gave</th><th>Model</th><th>Ordre nr.</th></tr></thead> <tbody> "

          $cvr = "";
          $no = array("57", "58", "59", "272", "574", "2549", "2550",'8355', '8356', '8357', '8358', '8359', '8360', '8361', '8362', '8363', '8364', '8365', '8366');
          $sw = array("1832", "1981", "2558","4793","5117","8271","9495");
      //   print_R($rs);


          if(in_array($rs[0]->attributes["shop_id"],$no)){
            $cvr.= "Firma navn;adresse;adresse2;Postnr;By;Kortnummer;Adgangskode;Kort;Deadline;Navn;Email;Gave;Model;Item nr;Item alias;Ordre nr. \n";
           } else if(in_array($rs[0]->attributes["shop_id"],$sw)){
                $cvr.= "Företag;Adress;Adress 2;Postnummer;Stad;Användarnamn;Lösenord;Kort;Giltiga t.o.m;Namn;E-mail;Gåva;Modell;Item nr.;Item alias;Ordre nr. \n";
           } else {
               $cvr.= "Firma navn;adresse;adresse2;Postnr;By;Kortnummer;Adgangskode;Kort;Deadline;Navn;Email;Gave;Model;Item nr;Item alias;Ordre nr. \n";
           }




        foreach($rs as $key=>$item){
            if($item->attributes["present_model_name"] == "") $item->attributes["present_model_name"] = "###";

            $hashtags = "###";
            if (strpos($item->attributes["present_model_name"], $hashtags) === false) {
                $item->attributes["present_model_name"].= $hashtags;
            }

            $presentPart = explode("###",$item->attributes["present_model_name"]);
            if($item->attributes["shop_id"] != ""){
                //if(isset($shopName[$item["shop_id"] ]))
                {
                    $item->attributes["shop_id"]  = $shopName[$item->attributes["shop_id"] ] ;
                }

            }

            $presentPart[0] = str_replace(",", " ", $presentPart[0]);
            $presentPart[1] = str_replace(",", " ", $presentPart[1]);


            $exprireDate = $item->attributes["expire_date"]->format('Y-m-d');

            if($onlySelected == ""){
                $cvr.= $item->attributes["ship_to_company"].";".$item->attributes["ship_to_address"].";".$item->attributes["ship_to_address_2"].";".$item->attributes["ship_to_postal_code"].";".$item->attributes["ship_to_city"].";".$item->attributes["username"].";".$item->attributes["password"].";".$item->attributes["shop_id"].";".$exprireDate.";".$item->attributes["user_name"].";".$item->attributes["user_email"].";".$presentPart[0].";".$presentPart[1].";".$item->attributes["model_present_no"].";".$item->attributes["fullalias"].";".$item->attributes["order_no"]."\n";
            } else if($onlySelected != "" && $item->attributes["present_model_name"] != "###"){
                //    $cvr.= $item->attributes["ship_to_company"]).";".utf8_decode($item->attributes["ship_to_address"]).";".utf8_decode($item->attributes["ship_to_address_2"]).";".$item->attributes["ship_to_postal_code"].";".utf8_decode($item->attributes["ship_to_city"]).";".$item->attributes["username"].";".$item->attributes["password"].";".$item->attributes["shop_id"].";".$exprireDate.";".utf8_decode($item->attributes["user_name"]).";".$item->attributes["user_email"].";".utf8_decode($presentPart[0]).";".utf8_decode($presentPart[1]).";".$item["model_present_no"].";".$item->attributes["fullalias"].";".$item->attributes["order_no"]."\n";
            }

        }
        $cvr = utf8_decode($cvr);
        header('Content-Type: application/csv');
      header('Content-Disposition: attachement; filename="data.csv"');
        echo $cvr; exit();
    }
}
?>
