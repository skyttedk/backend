<?php
ini_set('memory_limit','265M');
ini_set('max_execution_time', 600);
// Controller media
// Date created  Wed, 06 Apr 2016 09:39:56 +0200
// Created by Bitworks
class rapportController Extends baseController {
  public function Index() {
      echo "hej";
  }

   public function pimRapport()
   {
       $sql = "SELECT
present.id as present_id,
GROUP_CONCAT(DISTINCT CASE WHEN pm.language_id = 1 THEN pm.`model_id` END SEPARATOR ', ') AS modelID,
GROUP_CONCAT(DISTINCT CASE WHEN pm.language_id = 1 THEN pm.`model_present_no` END SEPARATOR ', ') AS vareno,
    
GROUP_CONCAT(DISTINCT CASE WHEN pm.language_id = 1 THEN pm.model_name END SEPARATOR ', ') AS navn_da,
GROUP_CONCAT(DISTINCT CASE WHEN pm.language_id = 1 THEN pm.model_no END SEPARATOR ', ') AS model_da,
GROUP_CONCAT(DISTINCT CASE WHEN pd.language_id = 1 THEN pd.`caption` END SEPARATOR ', ') AS present_caption_da,
GROUP_CONCAT(DISTINCT CASE WHEN pd.language_id = 1 THEN CONVERT(FROM_BASE64(pd.`long_description`) USING utf8)  END SEPARATOR ', ') AS present_description_da,

GROUP_CONCAT(DISTINCT CASE WHEN pm.language_id = 2 THEN pm.model_name END SEPARATOR ', ') AS navn_en,
GROUP_CONCAT(DISTINCT CASE WHEN pm.language_id = 2 THEN pm.model_no END SEPARATOR ', ') AS model_en,
GROUP_CONCAT(DISTINCT CASE WHEN pd.language_id = 2 THEN pd.`caption` END SEPARATOR ', ') AS present_caption_en,
GROUP_CONCAT(DISTINCT CASE WHEN pd.language_id = 2 THEN CONVERT(FROM_BASE64(pd.`long_description`) USING utf8)  END SEPARATOR ', ') AS present_description_en,


GROUP_CONCAT(DISTINCT CASE WHEN pm.language_id = 4 THEN pm.model_name END SEPARATOR ', ') AS navn_no,
GROUP_CONCAT(DISTINCT CASE WHEN pm.language_id = 4 THEN pm.model_no END SEPARATOR ', ') AS model_no,
GROUP_CONCAT(DISTINCT CASE WHEN pd.language_id = 4 THEN pd.`caption` END SEPARATOR ', ') AS present_caption_no,
GROUP_CONCAT(DISTINCT CASE WHEN pd.language_id = 4 THEN CONVERT(FROM_BASE64(pd.`long_description`) USING utf8)  END SEPARATOR ', ') AS present_description_no,

GROUP_CONCAT(DISTINCT CASE WHEN pm.language_id = 5 THEN pm.model_name END SEPARATOR ', ') AS navn_se,
GROUP_CONCAT(DISTINCT CASE WHEN pm.language_id = 5 THEN pm.model_no END SEPARATOR ', ') AS model_se,
GROUP_CONCAT(DISTINCT CASE WHEN pd.language_id = 5 THEN pd.`caption` END SEPARATOR ', ') AS present_caption_se,
GROUP_CONCAT(DISTINCT CASE WHEN pd.language_id = 5 THEN CONVERT(FROM_BASE64(pd.`long_description`) USING utf8)  END SEPARATOR ', ') AS present_description_se



FROM `present_model` pm
LEFT JOIN present_description pd ON pd.present_id = pm.present_id
inner join present on present.id = pm.present_id
WHERE pm.`original_model_id` = 0 and
pm.is_deleted = 0 and
pm.active  = 0 and
present.pim_id = 0 and
present.copy_of = 0 and
present.deleted = 0 and
present.active = 1 and

pm.`language_id` IN (1, 2, 4, 5) and 
present.created_datetime < '2023-12-01 13:07:41'
GROUP BY pm.`model_id`, pd.`present_id`";
       $data = Dbsqli::getSql2($sql);
       print_r($data);
   }

//https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=rapport/salepersonlist2&shop_id=8184


    public function salepersonlist()
    {

        // MOVE TO UNITS (sch 14/5 2025)
     //   echo "CURRENTLY UNAVAILABLE";
     //   return;

        $id = $_GET['shop_id'];
        $childID = $id*-1;
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=vareliste.csv');
        $output = fopen('php://output', 'w');

        $sql = "SELECT * FROM `company` WHERE id in (SELECT company_id FROM `company_shop` WHERE `shop_id` = ".$id.")";
        $stamdata = Dbsqli::getSql2($sql);

        $item = $stamdata[0];

        fwrite($output,'Virksomhedsnavn;');
        fwrite($output,utf8_decode($item["name"]).';');
        fwrite($output,"\n");

        fwrite($output,utf8_decode('Sælger').';');
        fwrite($output,utf8_decode($item["sales_person"]).';');
        fwrite($output,"\n");

        fwrite($output,'CVR;');
        fwrite($output,utf8_decode($item["cvr"]).';');
        fwrite($output,"\n");

        fwrite($output,'Kontaktperson;');
        fwrite($output,utf8_decode($item["contact_name"]).';');
        fwrite($output,"\n");

        fwrite($output,'Kontakt Tlf.;');
        fwrite($output,utf8_decode($item["contact_phone"]).';');

        fwrite($output,"\n");
        fwrite($output,'Kontakt Email;');
        fwrite($output,utf8_decode($item["contact_email"]).';');

        fwrite($output,"\n");
        fwrite($output,"\n");
        fwrite($output,"\n");

        // First, get all parent products (not children) ordered by index_
        $sql = "SELECT 
        present_model.*, 
        shop_present.index_ 
    FROM 
        `present_model` 
    INNER JOIN 
        shop_present ON shop_present.present_id = present_model.present_id
    LEFT JOIN 
        present ON present.id = present_model.present_id
    WHERE 
        `present_model`.`present_id` IN (
            SELECT present_id FROM `shop_present` WHERE 
            `shop_id` = ".$id." AND
            active = 1 AND
            is_deleted = 0
        )
        AND `present_model`.language_id = 1 
        AND `present_model`.active = 0
        AND (present.pchild IS NULL OR present.pchild = 0 OR present.pchild = '')
    ORDER BY 
        shop_present.index_";

        $parentList = Dbsqli::getSql2($sql);

        fwrite($output,utf8_decode('Varenr').';');
        fwrite($output,utf8_decode('Fejl i varenr.').';');
        fwrite($output,utf8_decode('Varenavn').';');
        fwrite($output,utf8_decode('Kostpris').';');
        fwrite($output,utf8_decode('Specialpris').';');
        fwrite($output,utf8_decode('Budget').';');
        fwrite($output,utf8_decode('Vejl. pris').';');
        fwrite($output,utf8_decode('Styrker').';');
        fwrite($output,utf8_decode('Leverandør'));
        fwrite($output,"\n");
        fwrite($output,"\n");

        // Process each parent and its children
        foreach ($parentList as $parentItem) {
            // Output the parent item
            $this->writeItemToCSV($output, $parentItem);

            // If this is a "sampak", handle the special case
            if (strtolower($parentItem["model_present_no"]) == "sam") {
                $this->handleSampak($output, $parentItem);
            }

            // Get and output all child products for this parent
            $childSql = "SELECT 
            pm.*, 
            COALESCE(sp.index_, 999999) as index_
        FROM 
            `present_model` pm
        LEFT JOIN 
            shop_present sp ON sp.present_id = pm.present_id
        INNER JOIN
            present ON present.id = pm.present_id
        WHERE 
            present.pchild = ".$parentItem["present_id"]."
            AND pm.language_id = 1 and present.shop_id = ".$childID."
        ORDER BY 
            index_";

            $childList = Dbsqli::getSql2($childSql);

            if (count($childList) > 0) {
                $childName = utf8_decode($this->getPresentationGroup($parentItem["present_id"]));
                fwrite($output,"--- ".$childName." --- \n");

                foreach ($childList as $childItem) {
                    $this->writeItemToCSV($output, $childItem);

                    // If this child is a "sampak", handle it too
                    if (strtolower($childItem["model_present_no"]) == "sam") {
                        $this->handleSampak($output, $childItem);
                    }
                }

                fwrite($output,"--- slut --- \n\n");
            }
        }
    }
    private function getPresentationGroup($presentID)
    {
        $pg = PresentationGroup::find_by_group_id($presentID);
        if ($pg) {
            return $pg->type == 1 ? "Sampak":"Vælg mellem";
        } else {
            return "error";
        }
    }
// Helper function to write an item to the CSV
    private function writeItemToCSV($output, $item) {
        $sqlPresent = "SELECT * FROM `present` WHERE id= ".$item["present_id"] ." AND present.deleted = 0";
        $presentlist = Dbsqli::getSql2($sqlPresent);

        if (empty($presentlist)) {
            return; // Skip if no present data found
        }

        if($presentlist[0]["pt_price"] == null || $presentlist[0]["pt_price"] == "" || $presentlist[0]["pt_price"] == "null"){
            $kostpris = "";
            $specialpris = "";
            $budget = "";
            $vejlpris = "";
        } else {
            try {
                $priceList = json_decode($presentlist[0]["pt_price"]);
                $kostpris = strval($presentlist[0]["prisents_nav_price"]);
                $specialpris = strval($priceList->special);
                $budget = strval($priceList->pris);
                $vejlpris = strval($priceList->budget);
            }
            catch(Exception $e) {
                $kostpris = "";
                $specialpris = "";
                $budget = "";
                $vejlpris = "";
            }
        }

        $strengthID = $item["strength"];
        $strength = "Ingen valgt";
        if($strengthID == 1) { $strength = "Svag";  }
        if($strengthID == 2) { $strength = "Middel";  }
        if($strengthID == 3) { $strength = "Stærk";  }
        $vendor = utf8_decode(strval($presentlist[0]["vendor"]));

        $itemName = $item["model_no"] == "" ? utf8_decode($item["model_name"]) : utf8_decode($item["model_name"]).' - '.utf8_decode($item["model_no"]);

        $valideVarenr = $this->itemnrExist($item["model_present_no"]);

        fwrite($output,utf8_decode($item["model_present_no"]).';');
        fwrite($output,$valideVarenr.';');
        fwrite($output,$itemName.';');
        fwrite($output,$kostpris.';');
        fwrite($output,$specialpris.';');
        fwrite($output,$budget.';');
        fwrite($output,$vejlpris.';');
        fwrite($output,utf8_decode($strength).';');
        fwrite($output,$vendor);
        fwrite($output,"\n");
    }

// Helper function to handle sampak (gift packs)
    private function handleSampak($output, $item) {
        $present_model_sampak = Dbsqli::getSql2("select * from present_model_sampak where model_id=".$item["model_id"]);
        if(sizeof($present_model_sampak) > 0){
            fwrite($output,"--- Sampak --- \n");
        }
        if(sizeof($present_model_sampak) > 0){
            $item_list = $present_model_sampak[0]["item_list"];
            $array = preg_split("/\r\n|\n|\r/", $item_list);
            foreach ($array as $itemSampak){
                $valideVarenr = $this->itemnrExist($itemSampak);
                fwrite($output,utf8_decode($itemSampak).';');
                fwrite($output,$valideVarenr.';');
                fwrite($output,"\n");
            }
        }
        if(sizeof($present_model_sampak) > 0){
            fwrite($output,"--- Sampak-slut --- \n");
        }
    }



  public function salepersonlist1()
  {


      // MOVE TO UNITS (sch 14/5 2025)
      echo "CURRENTLY UNAVAILABLE";
      return;

      $id = $_GET['shop_id'];
    if($id == 8208){
        $this->salepersonlist1();
        die("");
    }
      header('Content-Type: text/csv; charset=utf-8');
       header('Content-Disposition: attachment; filename=vareliste.csv');
      $output = fopen('php://output', 'w');

      $sql = "SELECT * FROM `company` WHERE id in (SELECT company_id FROM `company_shop` WHERE `shop_id` = ".$id.")";
      $stamdata = Dbsqli::getSql2($sql);

      $item = $stamdata[0];


      fwrite($output,'Virksomhedsnavn;');
      fwrite($output,utf8_decode($item["name"]).';');
      fwrite($output,"\n");

      fwrite($output,utf8_decode('Sælger').';');
      fwrite($output,utf8_decode($item["sales_person"]).';');
      fwrite($output,"\n");

      fwrite($output,'CVR;');
      fwrite($output,utf8_decode($item["cvr"]).';');
      fwrite($output,"\n");

      fwrite($output,'Kontaktperson;');
      fwrite($output,utf8_decode($item["contact_name"]).';');
      fwrite($output,"\n");

      fwrite($output,'Kontakt Tlf.;');
      fwrite($output,utf8_decode($item["contact_phone"]).';');

      fwrite($output,"\n");
      fwrite($output,'Kontakt Email;');
      fwrite($output,utf8_decode($item["contact_email"]).';');

      fwrite($output,"\n");
      fwrite($output,"\n");
      fwrite($output,"\n");





      $sql = "SELECT * FROM `present_model` 
          inner join shop_present on shop_present.present_id = present_model.present_id
         WHERE `present_model`.`present_id` in (
        SELECT present_id   FROM `shop_present`  WHERE 
        `shop_id` = ".$id." AND
        active = 1 AND
        is_deleted = 0)
        and 
        `present_model`.language_id = 1 and
        `present_model`.active = 0 order by shop_present.index_  ";
      $list = Dbsqli::getSql2($sql);

     // print_R($list);

      fwrite($output,utf8_decode('Varenr').';');
      fwrite($output,utf8_decode('Fejl i varenr.').';');
      fwrite($output,utf8_decode('Varenavn').';');
      fwrite($output,utf8_decode('Kostpris').';');
      fwrite($output,utf8_decode('Specialpris').';');
      fwrite($output,utf8_decode('Budget').';');
      fwrite($output,utf8_decode('Vejl. pris').';');
      fwrite($output,utf8_decode('Styrker').';');
      fwrite($output,utf8_decode('Leverandør'));
      fwrite($output,"\n");
      fwrite($output,"\n");
     foreach ($list as $item){
         $sqlPresent = "SELECT *  FROM `present` WHERE id= ".$item["present_id"] ." AND present.deleted = 0";
         $presentlist = Dbsqli::getSql2($sqlPresent);

         if($presentlist[0]["pt_price"] == null || $presentlist[0]["pt_price"] == "" || $presentlist[0]["pt_price"] == "null"){
             $kostpris = "";
             $specialpris = "";
             $budget = "";
             $vejlpris = "";
         } else {
             try {
                 $priceList = json_decode($presentlist[0]["pt_price"]);
                 $kostpris = strval($presentlist[0]["prisents_nav_price"]);
                 $specialpris = strval($priceList->special);
                 $budget = strval($priceList->pris);
                 $vejlpris = strval($priceList->budget);
             }
             catch(Exception $e) {
                 $kostpris = "";
                 $specialpris = "";
                 $budget = "";
                 $vejlpris = "";
             }
         }

         $strengthID = $item["strength"];
         $strength = "Ingen valgt";
         if($strengthID == 1) { $strength = "Svag";  }
         if($strengthID == 2) { $strength = "Middel";  }
         if($strengthID == 3) { $strength = "Stærk";  }
         $vendor = utf8_decode(strval($presentlist[0]["vendor"]));

         $itemName =  $item["model_no"] == "" ? utf8_decode($item["model_name"]) : utf8_decode($item["model_name"]).' - '.utf8_decode($item["model_no"]);


         $valideVarenr = $this->itemnrExist($item["model_present_no"]);

         fwrite($output,utf8_decode($item["model_present_no"]).';');
         fwrite($output,$valideVarenr.';');
         fwrite($output,$itemName.';');
         fwrite($output,$kostpris.';');
         fwrite($output,$specialpris.';');
         fwrite($output,$budget.';');
         fwrite($output,$vejlpris.';');
         fwrite($output,utf8_decode($strength).';');
         fwrite($output,$vendor);
         fwrite($output,"\n");
         if( strtolower($item["model_present_no"]) == "sam"){
             $present_model_sampak =  Dbsqli::getSql2("select * from present_model_sampak where model_id=".$item["model_id"]) ;
             if(sizeof($present_model_sampak) > 0){
                 fwrite($output,"--- Sampak --- \n");
             }
             if(sizeof($present_model_sampak) > 0){
                 $item_list =  $present_model_sampak[0]["item_list"];
                 $array = preg_split("/\r\n|\n|\r/", $item_list);
                 foreach ($array as $itemSampak){
                     $valideVarenr = $this->itemnrExist($itemSampak);
                     fwrite($output,utf8_decode($itemSampak).';');
                     fwrite($output,$valideVarenr.';');
                     fwrite($output,"\n");
                 }
             }
             if(sizeof($present_model_sampak) > 0){
                 fwrite($output,"--- Sampak-slut --- \n");
             }
         }
     }


   //   fwrite($output,utf8_decode('Gavenavn').';');
   //   fwrite($output,utf8_decode('Model').';');
   //   fwrite($output,utf8_decode('Alias').';');
   //   fwrite($output,"\n");

  }
    private function itemnrExist($itemnr)
    {
        $sql = "SELECT * FROM `navision_item` where no = '$itemnr' and deleted is null";
        if(sizeof(Dbsqli::getSql2($sql)) > 0) return "";
        return "Fejl";

    }


  public function test(){
    echo "<a target=\"_blank\" href='".GFConfig::BACKEND_URL."index.php?rt=rapport/fragt&token=sdfjl243jf89shdikfhkf43r98&expiredate=2017-11-05&counter=50000'>2017-11-05</a><br />";
    echo "<a target=\"_blank\" href='".GFConfig::BACKEND_URL."index.php?rt=rapport/fragt&token=sdfjl243jf89shdikfhkf43r98&expiredate=2017-11-12&counter=51100'>2017-11-12</a><br />";
    echo "<a target=\"_blank\" href='".GFConfig::BACKEND_URL."index.php?rt=rapport/fragt&token=sdfjl243jf89shdikfhkf43r98&expiredate=2017-11-19&counter=51200'>2017-11-19</a><br />";
    echo "<a target=\"_blank\" href='".GFConfig::BACKEND_URL."index.php?rt=rapport/fragt&token=sdfjl243jf89shdikfhkf43r98&expiredate=2017-11-26&counter=51300'>2017-11-26</a><br />";
    echo "<a target=\"_blank\" href='".GFConfig::BACKEND_URL."index.php?rt=rapport/fragt&token=sdfjl243jf89shdikfhkf43r98&expiredate=2017-11-30&counter=51300'>2017-11-30</a><br />";
    echo "<a target=\"_blank\" href='".GFConfig::BACKEND_URL."index.php?rt=rapport/fragt&token=sdfjl243jf89shdikfhkf43r98&expiredate=2017-12-31&counter=51500'>2017-11-30</a><br />";
  }

  public function labelRapportP1(){

        $labelRapport = new Labelrapport(1989,12314);
        $labelRapport->make();
        $labelRapport->arrayToCsvDownload();
  }
    public function labelRapportP2(){

        $labelRapport = new Labelrapport(1990,12313);
        $labelRapport->make();
        $labelRapport->arrayToCsvDownload();
  }

  public function fragtFlyttedKort(){

            $masterData = [];

        if($_GET["token"] != "324jlh2345jkFSd12jcvmcpq463q"){  die("Ingen adgang"); }
        $expiredate = $_GET["expiredate"];
        $isdromme = (isset($_GET["isdrom"]) && $_GET["isdrom"] == "1");
        $isdk = (isset($_GET["isdk"]) && $_GET["isdk"] == "1");

        $counter = 0;
        $cvrCount = array();
        $eanCount = array();

        // VÆLG SHOPS
        if(!$isdk)
        {
          $shops = array(272,57,58,59,574);
          $filename = "fragtjournalFlyttetKort-".$expiredate."_no.csv";
        }
        else if($isdromme)
        {
          $shops = array(290,310);
          $filename = "fragtjournalFlyttetKort-".$expiredate."_dromme.csv";
        }
        else
        {
          $shops = array(52,575,54,55,56,53);
          $filename = "fragtjournalFlyttetKort-".$expiredate."_dk.csv";
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename='.$filename.'');
        $output = fopen('php://output', 'w');
        fwrite($output,utf8_decode("Ordrenr;CompanyID;Shop ID;Udløb uge;Gavekortnr. start;Gavekortnr. slut;Gavekort værdi;Virksomhedsnavn;CVR;Attention;Faktura adresse;Faktura adresse 2;Faktura postnr.;Faktura by;Faktura land;Levering adresse;Levering adresse 2;Levering postnr.;Levering by;Levering land;Email;Telefon;Elektronisk kort;Tillægsordre;EAN;Levering virksomhed;Total antal;Fragt;Moms 25;Moms 15;Moms 0;Donation;Samlagt med;højt match\n"));





          $sqlCompanyOrder ="
        SELECT company.* from company

WHERE company.id in (select company_id from shop_user
                                where expire_date = '".$expiredate."' and
                                shop_id in(".implode(",",$shops).") and is_demo = 0)
and company.id not in (



select company_order.company_id as c_o_id from company_order where company_order.id in (select company_order_id from shop_user
                                where expire_date = '".$expiredate."' and
                                shop_id in(".implode(",",$shops).") and is_demo = 0))
                              ";



        $companyOrderList = Dbsqli::getSql2($sqlCompanyOrder);


        foreach($companyOrderList as $mData)
        {

            // Init counters
            $moms25 = 0; $moms15 = 0; $moms0 = 0; $momsTotal = 0; $momsSpecial = 0;
            $countInCompany = 0; $countOutsideCompany = 0; $activeShopusers = "";
             $sqlShopUser = "
            SELECT count(id) as antal,
                   group_concat(id) as userlist,
                   company_id
                    FROM `shop_user` WHERE
                        is_giftcertificate = 1 &&
                        company_id = ".$mData["id"]." &&
                        is_demo = 0 &&
                        blocked = 0 &&
                        is_delivery = 0 group by company_id";

            // Shopuser results
            $shopusermap = Dbsqli::getSql2($sqlShopUser);

            foreach($shopusermap as $shopuserresult)
            {
                    $momsSql = "
              select count(*) as antal, moms from present_model
                inner join `order` on present_model.present_id =  `order`.present_id
                    where
                        present_model.language_id = 1 &&
                        `order`.shopuser_id in
                            (
                                SELECT id FROM `shop_user` WHERE
                                    company_id = ".$mData["id"]." and
                                    expire_date = '".$expiredate."' and blocked = 0
                            )
                        group by moms";
             $countInCompany = $shopuserresult["antal"];
              $momsRs = Dbsqli::getSql2($momsSql);

              foreach($momsRs as $momsData){
                if($momsData["moms"] == "0"){
                  $moms0 = $momsData["antal"];
                }
                if($momsData["moms"] == "25"){
                  $moms25 = $momsData["antal"];
                }
                if($momsData["moms"] == "15"){
                  $moms15 = $momsData["antal"];
                }
                if($momsData["moms"] == "-1"){
                  $momsSpecial = $momsData["antal"];
                }
                $momsTotal+= $momsData["antal"];
              }



              $t = $countInCompany - $momsTotal;
              $moms25+= $t;


            // Output if active cards

            if($countInCompany > 0)
            {

             fwrite($output,

                    "ingen bsnr;".
                    utf8_decode($mData["id"]).";".
                    ";".
                    utf8_decode($expiredate).";".
                    ";".
                    ";".
                    ";".
                    utf8_decode($mData["name"]).";".
                    utf8_decode($mData["cvr"]).";".
                    utf8_decode($mData["contact_name"]).";".
                    utf8_decode($mData["bill_to_address"]).";".
                    utf8_decode($mData["bill_to_address_2"]).";".
                    utf8_decode($mData["bill_to_postal_code"]).";".
                    utf8_decode($mData["bill_to_city"]).";".
                    utf8_decode($mData["bill_to_country"]).";".
                    utf8_decode($mData["ship_to_address"]).";".
                    utf8_decode($mData["ship_to_address_2"]).";".
                    utf8_decode($mData["ship_to_postal_code"]).";".
                    utf8_decode($mData["ship_to_city"]).";".
                    ";".
                    utf8_decode($mData["contact_email"]).";".
                    utf8_decode($mData["contact_phone"]).";".
                    ";".
                    ";".
                    utf8_decode($mData["ean"]).";".
                    utf8_decode($mData["ship_to_company"]).";".
                    $countInCompany.";".
                    $this->calculatefreight($countInCompany,($isdk && $isdromme)).";".
                    $moms25.";".
                    $moms15.";".
                    $moms0.";".
                    $momsSpecial.";".

          "\n");

              }




            }

        }

  }








  public function fragtOneBs(){

        $masterData = [];

        if($_GET["token"] != "324jlh2345jkFSd12jcvmcpq463q"){  die("Ingen adgang"); }
        $expiredate = $_GET["expiredate"];
        $isdromme = (isset($_GET["isdrom"]) && $_GET["isdrom"] == "1");
        $isdk = (isset($_GET["isdk"]) && $_GET["isdk"] == "1");

        $counter = 0;
        $cvrCount = array();
        $eanCount = array();

        // VÆLG SHOPS
        if(!$isdk)
        {
          $shops = array(272,57,58,59,574);
          $filename = "fragtjournal-".$expiredate."_no.csv";
        }
        else if($isdromme)
        {
          $shops = array(290,310);
          $filename = "fragtjournal-".$expiredate."_dromme.csv";
        }
        else
        {
          $shops = array(52,575,54,55,56,53);
          $filename = "fragtjournal-".$expiredate."_dk.csv";
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename='.$filename.'');
        $output = fopen('php://output', 'w');
        fwrite($output,utf8_decode("Ordrenr;Antal;Shop ID;Udløb uge;Gavekortnr. start;Gavekortnr. slut;Gavekort værdi;Virksomhedsnavn;CVR;Attention;Faktura adresse;Faktura adresse 2;Faktura postnr.;Faktura by;Faktura land;Levering adresse;Levering adresse 2;Levering postnr.;Levering by;Levering land;Email;Telefon;Elektronisk kort;Tillægsordre;EAN;Levering virksomhed;Total antal;Fragt;Moms 25;Moms 15;Moms 0;Donation;Samlagt med;højt match\n"));


        // Load company_orders that have been synced and is not cancelled for companies that are active
    //    $sqlCompanyOrder = "SELECT company.*, certificate_no_begin, certificate_no_end, company_id, order_no, company_order.id as company_order_id  FROM `company_order`, company WHERE company_order.company_id = company.id &&  navsync_status = 3 && is_cancelled = 0 && company_order.shop_id IN (".implode(",",$shops).") && expire_date = '".$expiredate."' && company.deleted = 0 && company.active = 1 && company.onhold = 0 ";
    // update 28/11-2019 company_orders expire_date are overruled by shop_user expire_date
    // vi udtager alle de bs - numre i campany_order som har aktive kort med den givne deadline
     $sqlCompanyOrder = "SELECT
        company.*,
        certificate_no_begin,
        certificate_no_end,
        company_id,
        order_no,
        company_order.id as company_order_id,
        company_order.shop_id as company_order_shop_id
            FROM `company_order`, company
                WHERE   company_order.company_id = company.id &&
                        navsync_status = 3 && is_cancelled = 0 &&
                        company_order.id IN (
                            select DISTINCT (company_order_id) from shop_user
                                where expire_date = '".$expiredate."' and
                                shop_id in(".implode(",",$shops).") and is_demo = 0
                            ) &&
                        company.deleted = 0 &&
                        company.active = 1 &&

                        company.onhold = 0  order by cvr,order_no ";



        $companyOrderList = Dbsqli::getSql2($sqlCompanyOrder);




        foreach($companyOrderList as $mData)
        {

            // Init counters
            $moms25 = 0; $moms15 = 0; $moms0 = 0; $momsTotal = 0; $momsSpecial = 0;
            $countInCompany = 0; $countOutsideCompany = 0; $activeShopusers = "";

            // Get active shopusers in order and group by company
            //$sqlShopUser = "SELECT count(id) as antal, group_concat(id) as userlist, company_id FROM `shop_user` WHERE is_giftcertificate = 1 && username BETWEEN '".$mData["certificate_no_begin"]."' AND '".$mData["certificate_no_end"]."' && is_demo = 0 && blocked = 0 && is_delivery = 0 group by company_id";

            // looper igennem  shop_user med bs-nummer og henter bruger antal og id'er
            $sqlShopUser = "
            SELECT count(id) as antal,
                   group_concat(id) as userlist,
                   company_id
                    FROM `shop_user` WHERE
                        is_giftcertificate = 1 &&
                        company_order_id = ".$mData["company_order_id"]." &&
                        is_demo = 0 &&
                        blocked = 0 &&
                        is_delivery = 0 group by company_id";

            // Shopuser results
            $shopusermap = Dbsqli::getSql2($sqlShopUser);

            foreach($shopusermap as $shopuserresult)
            {
              if($shopuserresult["company_id"] == $mData["company_id"])
              {
                $countInCompany = $shopuserresult["antal"];
                $activeShopusers = $shopuserresult["userlist"];
                if(substr($activeShopusers,-1) == ",") $activeShopusers = substr($activeShopusers,0,-1);
              }
              else
              {
                $countOutsideCompany += $shopuserresult["antal"];
              }
            }

            // Get moms count
            if($countInCompany > 0)
            {

              //$momsSql = "select count(*) as antal, moms,created_datetime from present inner join `order` on present.id =  `order`.present_id where `order`.company_id = ".$mData["company_id"]." && `order`.shopuser_id in (".$activeShopusers.") group by moms";
//              $momsSql = "select count(*) as antal, moms from present_model inner join `order` on present_model.present_id =  `order`.present_id where present_model.language_id = 1 && `order`.company_id = ".$mData["company_id"]." && `order`.shopuser_id in (".$activeShopusers.") group by moms";
              // i inner select finder vi alle aktive kort i shop_user med bsnummer og deadline
              // herefter bliver listen af shop_user brugt i order tabellen for at finde present_id, og via join finde moms i present_model
              // antal bliver også fundet her.
              // ???? hvad så med kort uden ordre ?????
              /* 2019 version med moms på present_model

                $momsSql = "
              select count(*) as antal, moms from present_model
                inner join `order` on present_model.present_id =  `order`.present_id
                    where
                        `order`.present_model_id = present_model.model_id &&
                        `present_model`.language_id = 1 &&
                        `order`.shopuser_id in
                            (
                                SELECT id FROM `shop_user` WHERE
                                    `company_order_id` = ".$mData["company_order_id"]." and
                                    expire_date = '".$expiredate."' and blocked = 0
                            )
                        group by moms";
*/


                $momsSql = "
              select count(*) as antal, moms from present
                inner join `order` on present.id =  `order`.present_id
                    where 
                        `order`.present_id = present.id && 
                        `order`.shopuser_id in
                            (
                                SELECT id FROM `shop_user` WHERE
                                    `company_order_id` = ".$mData["company_order_id"]." and
                                    expire_date = '".$expiredate."' and blocked = 0
                            )
                        group by moms";

              $momsRs = Dbsqli::getSql2($momsSql);

              foreach($momsRs as $momsData){
                if($momsData["moms"] == "0"){
                  $moms0 = $momsData["antal"];
                }
                if($momsData["moms"] == "25"){
                  $moms25 = $momsData["antal"];
                }
                if($momsData["moms"] == "15"){
                  $moms15 = $momsData["antal"];
                }
                if($momsData["moms"] == "-1"){
                  $momsSpecial = $momsData["antal"];
                }
                $momsTotal+= $momsData["antal"];
              }

            }

            $t = $countInCompany - $momsTotal;
            $moms25+= $t;


            // Output if active cards
            if($countInCompany > 0)
            {
                $str =

                    $mData["order_no"].";".
                    ";".
                    $mData["company_order_shop_id"].";".
                    utf8_decode($expiredate).";".
                    ";".
                    ";".
                    ";".
                    utf8_decode($mData["name"]).";".
                    utf8_decode($mData["cvr"]).";".
                     utf8_decode($mData["contact_name"]).";".
                    utf8_decode($mData["bill_to_address"]).";".
                    utf8_decode($mData["bill_to_address_2"]).";".
                    utf8_decode($mData["bill_to_postal_code"]).";".
                    utf8_decode($mData["bill_to_city"]).";".
                    utf8_decode($mData["bill_to_country"]).";".
                    utf8_decode($mData["ship_to_address"]).";".
                    utf8_decode($mData["ship_to_address_2"]).";".
                    utf8_decode($mData["ship_to_postal_code"]).";".
                    utf8_decode($mData["ship_to_city"]).";".
                    ";".
                    utf8_decode($mData["contact_email"]).";".
                    utf8_decode($mData["contact_phone"]).";".
                    ";".
                    ";".
                    utf8_decode($mData["ean"]).";".
                    utf8_decode($mData["ship_to_company"]).";".
                    $countInCompany.";".
                    $this->calculatefreight($countInCompany,($isdk && $isdromme)).";".
                    $moms25.";".
                    $moms15.";".
                    $moms0.";".
                    $momsSpecial.";;";

       //             $countOutsideCompany.";".
                  $masterData[] = explode(";",$str);
              }

                  $counter++;

              }
       //    print_r($masterData);
        //  samler all ordre på første bs kort
        /*
          8=cvr
          10= fakture add
          12= fakture post
          15= lev add
          17= lev post
        */

         foreach($masterData as $key=>$val){

                foreach($masterData as $key2=>$val2){
                    $isMacth = 0;
                    if(trimgf($val[2]) == trimgf($val2[2])) {$isMacth++;}
                    if(trimgf($val[8]) == trimgf($val2[8])) {$isMacth++;}
                    if(trimgf($val[10]) == trimgf($val2[10])) {  $isMacth++; }
                    if(trimgf($val[12]) == trimgf($val2[12])) { $isMacth++;}
                    if(trimgf($val[15]) == trimgf($val2[15])) { $isMacth++;}
                    if(trimgf($val[17]) == trimgf($val2[17])) { $isMacth++;}
                    if(trimgf($val[0]) == trimgf($val2[0])) { $isMacth--;}
                    if($isMacth == 6 &&  $masterData[$key][26] != 0){

                        // total
                        $masterData[$key][26]+= $masterData[$key2][26];
                        $masterData[$key2][26] = 0;
                        // ny fragt beregn
                        $masterData[$key][27] =  $this->calculatefreight($masterData[$key][26],($isdk && $isdromme));
                        $masterData[$key2][27] = 0;
                        // moms 25
                        $masterData[$key][28]+= $masterData[$key2][28];
                        $masterData[$key2][28] = 0;
                        // moms 15
                        $masterData[$key][29]+= $masterData[$key2][29];
                        $masterData[$key2][29] = 0;
                        // moms 0
                        $masterData[$key][30]+= $masterData[$key2][30];
                        $masterData[$key2][30] = 0;
                        // moms special
                        $masterData[$key][31]+= $masterData[$key2][31];
                        $masterData[$key2][31] = 0;
                        // antal samlagt
                        $masterData[$key][32]= $masterData[$key][32]."/".$masterData[$key2][0];
                        $masterData[$key2][32]= $masterData[$key][0];
                    } else {
                       if($val[8] == $val2[8]   &&  $masterData[$key][26] != 0 && $val[0] != $val2[0] && $isMacth > 4){
                          if($val[2] == $val2[2]){
                              $bs = str_replace("BS", "", $masterData[$key2][0]);
                              $masterData[$key][33]= $masterData[$key][33]." ".$bs."(".$isMacth.") ";
                          }

                        }
                    }



                }


         }

         foreach($masterData as $key=>$val){

            fwrite($output, implode(";",$val)."\n");

         }

       }




































  public function fragt(){



        if($_GET["token"] != "sdfjl243jf89shdikfhkf43r98"){  die("Ingen adgang"); }
        $expiredate = $_GET["expiredate"];
        $counter = $_GET["counter"];

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=fragtjournal-'.$expiredate.'.csv');
        $output = fopen('php://output', 'w');
        fwrite($output,utf8_decode("Ordrenr;Antal;Shop ID;Udløb uge;Gavekortnr. start;Gavekortnr. slut;Gavekort værdi;Virksomhedsnavn;CVR;Attention;Faktura adresse;Faktura adresse 2;Faktura postnr.;Faktura by;Faktura land;Levering adresse;Levering adresse 2;Levering postnr.;Levering by;Levering land;Email;Telefon;Elektronisk kort;Tillægsordre;EAN;Levering virksomhed;Total antal;Fragt;Moms 25;Moms 0\n"));

//         shop_user.shop_id in ('52','54','55','56','53','287','290','310','265','272' )  and
//          shop_user.shop_id in ('287','290','310')  and
       $sqlCompany = "select COUNT(shop_user.id) as antal, company.*, group_concat(distinct company_id) as companylist from company
        inner join shop_user on company.id = shop_user.company_id
        where company.deleted = 0 and company.active = 1  and shop_user.blocked = 0 and shop_user.is_delivery = 0 and
        shop_user.shop_id in ('52','54','55','56','53','287','290','310','575' ) and shop_user.expire_date =  '".$expiredate."' GROUP BY company.cvr, company.ean, company.bill_to_postal_code, company.bill_to_address, company.ship_to_postal_code, company.ship_to_address order by company.cvr, company.ean ";


        $cvrCount = array();
        $eanCount = array();

        $companyRs = Dbsqli::getSql2($sqlCompany);
        //$counter = 6504;
    // companylist


           foreach($companyRs as $mData){
                $moms25 = 0;
                $moms0 = 0;
                $momsTotal = 0;
                  // https://gavefabrikken.dk/gavefabrikken_backend/index.php?rt=rapport/fragt&token=sdfjl243jf89shdikfhkf43r98&expiredate=2018-11-04&counter=6504

                    $momsSql = "  select count(*) as antal, moms,created_datetime from present inner join `order`
                    on present.id =  `order`.present_id
                    where `order`.user_username in (  SELECT username FROM `shop_user` WHERE `company_id` in( ".$mData["companylist"].") AND `expire_date` =  '".$expiredate."' AND `is_giftcertificate` = 1 AND `blocked` = 0 AND `is_delivery` = 0 ) group by moms ";
                    $momsRs = Dbsqli::getSql2($momsSql);


                   $companyOrderSql = "select order_no from company_order where company_id in( ".$mData["companylist"].") and is_cancelled = 0";
                   $BSRs =  Dbsqli::getSql2($companyOrderSql);
                   $BSlist = [];
                   foreach($BSRs as $BSitem){  array_push($BSlist,$BSitem["order_no"]); }





                   foreach($momsRs as $momsData){
                        if($momsData["moms"] == "0"){
                            $moms0 = $momsData["antal"];
                        }
                        if($momsData["moms"] == "25"){
                            $moms25 = $momsData["antal"];
                        }
                        $momsTotal+= $moms25 = $momsData["antal"];
                   }

                   $t = $mData["antal"] - $momsTotal;
                   $moms25+= $t;

               fwrite($output,
//                    "BS".str_pad($counter, 5, "0", STR_PAD_LEFT).";".
                    implode("|",$BSlist).";".
                    ";".
                    ";".
                    utf8_decode($expiredate).";".
                    ";".
                    ";".
                    ";".
                    utf8_decode($mData["name"]).";".
                    utf8_decode($mData["cvr"]).";".
                    utf8_decode($mData["contact_name"]).";".
                    utf8_decode($mData["bill_to_address"]).";".
                    utf8_decode($mData["bill_to_address_2"]).";".
                    utf8_decode($mData["bill_to_postal_code"]).";".
                    utf8_decode($mData["bill_to_city"]).";".
                    utf8_decode($mData["bill_to_country"]).";".
                    utf8_decode($mData["ship_to_address"]).";".
                    utf8_decode($mData["ship_to_address_2"]).";".
                    utf8_decode($mData["ship_to_postal_code"]).";".
                    utf8_decode($mData["ship_to_city"]).";".
                    ";".
                    utf8_decode($mData["contact_email"]).";".
                    utf8_decode($mData["contact_phone"]).";".
                    ";".
                    ";".
                    utf8_decode($mData["ean"]).";".
                    utf8_decode($mData["ship_to_company"]).";".
                    $mData["antal"].";".
                    $this->calculatefreight($mData["antal"]).";".
                    $moms25.";".
                    $moms0.";".
              "\n");

              $counter++;





              }





                /*
                // Update cvr and ean count
                if(!isset($cvrCount[trimgf($mData["cvr"])])) $cvrCount[trimgf($mData["cvr"])] = 1;
                else $cvrCount[trimgf($mData["cvr"])]++;

                if(trimgf($mData["ean"]) != "")
                {
                    if(!isset($eanCount[trimgf($mData["ean"])])) $eanCount[trimgf($mData["ean"])] = 1;
                    else $eanCount[trimgf($mData["ean"])]++;
                }
                */






           /*
              echo "\n\nCVR nr. der optræder flere gange\n";
              foreach($cvrCount as $cvr => $count)
              {
                if($count > 1) echo $cvr.";".$count."\n";
              }

              echo "\n\nEAN nr. der optræder flere gange\n";
              foreach($eanCount as $ean => $count)
              {
                if($count > 1) echo $ean.";".$count."\n";
              }
            */

        }


        public function bsfragt(){



        if($_GET["token"] != "324jlh2345jkFSd12jcvmcpq463q"){  die("Ingen adgang"); }
        $expiredate = $_GET["expiredate"];
        $isdromme = (isset($_GET["isdrom"]) && $_GET["isdrom"] == "1");
        $isdk = (isset($_GET["isdk"]) && $_GET["isdk"] == "1");
        $counter = 0;
        $cvrCount = array();
        $eanCount = array();

        // VÆLG SHOPS
        if(!$isdk)
        {
          $shops = array(272,57,58,59,574);
          $filename = "fragtjournal-".$expiredate."_no.csv";
        }
        else if($isdromme)
        {
          $shops = array(290,310);
          $filename = "fragtjournal-".$expiredate."_dromme.csv";
        }
        else
        {
          $shops = array(52,575,54,55,56,53);
          $filename = "fragtjournal-".$expiredate."_dk.csv";
        }

         header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename='.$filename.'');
        $output = fopen('php://output', 'w');
        fwrite($output,utf8_decode("Ordrenr;Antal;Shop ID;Udløb uge;Gavekortnr. start;Gavekortnr. slut;Gavekort værdi;Virksomhedsnavn;CVR;Attention;Faktura adresse;Faktura adresse 2;Faktura postnr.;Faktura by;Faktura land;Levering adresse;Levering adresse 2;Levering postnr.;Levering by;Levering land;Email;Telefon;Elektronisk kort;Tillægsordre;EAN;Levering virksomhed;Total antal;Fragt;Moms 25;Moms 15;Moms 0;Donation\n"));


        // Load company_orders that have been synced and is not cancelled for companies that are active
    //    $sqlCompanyOrder = "SELECT company.*, certificate_no_begin, certificate_no_end, company_id, order_no, company_order.id as company_order_id  FROM `company_order`, company WHERE company_order.company_id = company.id &&  navsync_status = 3 && is_cancelled = 0 && company_order.shop_id IN (".implode(",",$shops).") && expire_date = '".$expiredate."' && company.deleted = 0 && company.active = 1 && company.onhold = 0 ";
    // update 28/11-2019 company_orders expire_date are overruled by shop_user expire_date
    // vi udtager alle de bs - numre i campany_order som har aktive kort med den givne deadline
     $sqlCompanyOrder = "SELECT
        company.*,
        certificate_no_begin,
        certificate_no_end,
        company_id,
        order_no,
        company_order.id as company_order_id
            FROM `company_order`, company
                WHERE   company_order.company_id = company.id &&
                        navsync_status = 3 && is_cancelled = 0 &&
                        company_order.id IN (
                            select DISTINCT (company_order_id) from shop_user
                                where expire_date = '".$expiredate."' and
                                shop_id in(".implode(",",$shops).") and is_demo = 0
                            ) &&
                        company.deleted = 0 &&
                        company.active = 1 &&
                        company.onhold = 0 ";

        $companyOrderList = Dbsqli::getSql2($sqlCompanyOrder);

        foreach($companyOrderList as $mData)
        {
            // Init counters
            $moms25 = 0; $moms15 = 0; $moms0 = 0; $momsTotal = 0; $momsSpecial = 0;
            $countInCompany = 0; $countOutsideCompany = 0; $activeShopusers = "";

            // Get active shopusers in order and group by company
            //$sqlShopUser = "SELECT count(id) as antal, group_concat(id) as userlist, company_id FROM `shop_user` WHERE is_giftcertificate = 1 && username BETWEEN '".$mData["certificate_no_begin"]."' AND '".$mData["certificate_no_end"]."' && is_demo = 0 && blocked = 0 && is_delivery = 0 group by company_id";

            // looper igennem  shop_user med bs-nummer og henter bruger antal og id'er
            $sqlShopUser = "
            SELECT count(id) as antal,
                   group_concat(id) as userlist,
                   company_id
                    FROM `shop_user` WHERE
                        is_giftcertificate = 1 &&
                        company_order_id = ".$mData["company_order_id"]." &&
                        is_demo = 0 &&
                        blocked = 0 &&
                        is_delivery = 0 group by company_id";

            // Shopuser results
            $shopusermap = Dbsqli::getSql2($sqlShopUser);

            foreach($shopusermap as $shopuserresult)
            {
              if($shopuserresult["company_id"] == $mData["company_id"])
              {
                $countInCompany = $shopuserresult["antal"];
                $activeShopusers = $shopuserresult["userlist"];
                if(substr($activeShopusers,-1) == ",") $activeShopusers = substr($activeShopusers,0,-1);
              }
              else
              {
                $countOutsideCompany += $shopuserresult["antal"];
              }
            }

            // Get moms count
            if($countInCompany > 0)
            {

              //$momsSql = "select count(*) as antal, moms,created_datetime from present inner join `order` on present.id =  `order`.present_id where `order`.company_id = ".$mData["company_id"]." && `order`.shopuser_id in (".$activeShopusers.") group by moms";
//              $momsSql = "select count(*) as antal, moms from present_model inner join `order` on present_model.present_id =  `order`.present_id where present_model.language_id = 1 && `order`.company_id = ".$mData["company_id"]." && `order`.shopuser_id in (".$activeShopusers.") group by moms";
              // i inner select finder vi alle aktive kort i shop_user med bsnummer og deadline
              // herefter bliver listen af shop_user brugt i order tabellen for at finde present_id, og via join finde moms i present_model
              // antal bliver også fundet her.
              // ???? hvad så med kort uden ordre ?????
              $momsSql = "
              select count(*) as antal, moms from present_model
                inner join `order` on present_model.present_id =  `order`.present_id
                    where
                        present_model.language_id = 1 &&
                        `order`.shopuser_id in
                            (
                                SELECT id FROM `shop_user` WHERE
                                    `company_order_id` = ".$mData["company_order_id"]." and
                                    expire_date = '".$expiredate."' and blocked = 0
                            )
                        group by moms";

              $momsRs = Dbsqli::getSql2($momsSql);

              foreach($momsRs as $momsData){
                if($momsData["moms"] == "0"){
                  $moms0 = $momsData["antal"];
                }
                if($momsData["moms"] == "25"){
                  $moms25 = $momsData["antal"];
                }
                if($momsData["moms"] == "15"){
                  $moms15 = $momsData["antal"];
                }
                if($momsData["moms"] == "-1"){
                  $momsSpecial = $momsData["antal"];
                }
                $momsTotal+= $momsData["antal"];
              }

            }

            $t = $countInCompany - $momsTotal;
            $moms25+= $t;


            // Output if active cards
            if($countInCompany > 0)
            {

               fwrite($output,
                    $mData["order_no"].";".
                    ";".
                    ";".
                    utf8_decode($expiredate).";".
                    ";".
                    ";".
                    ";".
                    utf8_decode($mData["name"]).";".
                    utf8_decode($mData["cvr"]).";".
                    utf8_decode($mData["contact_name"]).";".
                    utf8_decode($mData["bill_to_address"]).";".
                    utf8_decode($mData["bill_to_address_2"]).";".
                    utf8_decode($mData["bill_to_postal_code"]).";".
                    utf8_decode($mData["bill_to_city"]).";".
                    utf8_decode($mData["bill_to_country"]).";".
                    utf8_decode($mData["ship_to_address"]).";".
                    utf8_decode($mData["ship_to_address_2"]).";".
                    utf8_decode($mData["ship_to_postal_code"]).";".
                    utf8_decode($mData["ship_to_city"]).";".
                    ";".
                    utf8_decode($mData["contact_email"]).";".
                    utf8_decode($mData["contact_phone"]).";".
                    ";".
                    ";".
                    utf8_decode($mData["ean"]).";".
                    utf8_decode($mData["ship_to_company"]).";".
                    $countInCompany.";".
                    $this->calculatefreight($countInCompany,($isdk && $isdromme)).";".
                    $moms25.";".
                    $moms15.";".
                    $moms0.";".
                    $momsSpecial.";".
       //             $countOutsideCompany.";".
              "\n");
              }

                  $counter++;

              }


        }


        // Fragtliste kun på tillægsordre, summeres op for alle på virksomhed
        public function bsfragt2(){



        if($_GET["token"] != "324jlh2345jkFSd12jcvmcpq463w"){  die("Ingen adgang"); }
        $expiredate = $_GET["expiredate"];
        $isdk = (isset($_GET["isdk"]) && $_GET["isdk"] == "1");
        $counter = 0;
        $cvrCount = array();
        $eanCount = array();

        $drommeShops = array(290,310);

        $shopNames = array(
           290 => "Drømmegavekortet 200",
           310 => "Drømmegavekortet 300",
           272 => "Julegavekortet NO 300",
           57 => "Julegavekortet NO 400",
           58 => "Julegavekortet NO 600",
           59 => "Julegavekortet NO 800",
           574 => "Guldgavekort NO",
           52 => "Julegavekortet DK",
           575 => "Designjulegaven",
           54 => "24 Gaver 400",
           55 => "24 Gaver 560",
           56 => "24 Gaver 640",
           53 => "Guldgavekortet DK"
        );

        // VÆLG SHOPS
        if(!$isdk)
        {
          $shops = array(272,57,58,59,574);
          $filename = "fragtjournal-".$expiredate."_no.csv";
        }
        else
        {
          $shops = array(52,575,54,55,56,53,290,310);
          $filename = "fragtjournal-".$expiredate."_dk.csv";
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename='.$filename.'');
        $output = fopen('php://output', 'w');
        fwrite($output,utf8_decode("Ordrenr;Antal;Shop ID;Udløb uge;Gavekortnr. start;Gavekortnr. slut;Gavekort værdi;Virksomhedsnavn;CVR;Attention;Faktura adresse;Faktura adresse 2;Faktura postnr.;Faktura by;Faktura land;Levering adresse;Levering adresse 2;Levering postnr.;Levering by;Levering land;Email;Telefon;Elektronisk kort;Tillægsordre;EAN;Levering virksomhed;Total antal;Fragt;Moms 25;Moms 15;Moms 0;Flyttede kort\n"));





        // Load company_orders that have been synced and is not cancelled for companies that are active
        $sqlCompanyOrder = "SELECT company.*, certificate_no_begin, certificate_no_end, company_id, order_no, shop_id  FROM `company_order`, company WHERE company_order.company_id = company.id &&  navsync_status = 3 && is_cancelled = 0 && company_order.shop_id IN (".implode(",",$shops).") && expire_date = '".$expiredate."' && company.deleted = 0 && company.active = 1 && company.onhold = 0  order by company.id asc, company.cvr asc, company_order.shop_id asc";
        $companyOrderList = Dbsqli::getSql2($sqlCompanyOrder);

        $lastCompanyKey = "";
        $datalist = array();

        foreach($companyOrderList as $mData)
        {
            $currentCompanyKey = $mData["nav_debitor_no"]."-".$mData["cvr"]."-".$mData["shop_id"];

            if($currentCompanyKey == $lastCompanyKey)
            {

              // Add company to datalist
              if(!isset($datalist[$currentCompanyKey]))
              {
                $datalist[$currentCompanyKey] = $mData;
                $datalist[$currentCompanyKey]["countincompany"] = 0;
                $datalist[$currentCompanyKey]["moms25"] = 0;
                $datalist[$currentCompanyKey]["moms15"] = 0;
                $datalist[$currentCompanyKey]["moms0"] = 0;
                $datalist[$currentCompanyKey]["countoutsidecompany"] = 0;
              }

              // Init counters
              $moms25 = 0; $moms15 = 0; $moms0 = 0; $momsTotal = 0;
              $countInCompany = 0; $countOutsideCompany = 0; $activeShopusers = "";


              // Get active shopusers in order and group by company
              $sqlShopUser = "SELECT count(id) as antal, group_concat(id) as userlist, company_id FROM `shop_user` WHERE is_giftcertificate = 1 && username BETWEEN '".$mData["certificate_no_begin"]."' AND '".$mData["certificate_no_end"]."' && is_demo = 0 && blocked = 0 && is_delivery = 0 group by company_id";

              // Shopuser results
              $shopusermap = Dbsqli::getSql2($sqlShopUser);
              foreach($shopusermap as $shopuserresult)
              {
                if($shopuserresult["company_id"] == $mData["company_id"])
                {
                  $countInCompany = $shopuserresult["antal"];
                  $activeShopusers = $shopuserresult["userlist"];
                  if(substr($activeShopusers,-1) == ",") $activeShopusers = substr($activeShopusers,0,-1);
                }
                else
                {
                  $countOutsideCompany += $shopuserresult["antal"];
                }
              }

              // Get moms count
              if($countInCompany > 0)
              {
                $momsSql = "select count(*) as antal, moms,created_datetime from present inner join `order` on present.id =  `order`.present_id where `order`.company_id = ".$mData["company_id"]." && `order`.shopuser_id in (".$activeShopusers.") group by moms";

                $momsRs = Dbsqli::getSql2($momsSql);

                foreach($momsRs as $momsData){
                  if($momsData["moms"] == "0"){
                    $moms0 = $momsData["antal"];
                  }
                  if($momsData["moms"] == "25"){
                    $moms25 = $momsData["antal"];
                  }
                  if($momsData["moms"] == "15"){
                    $moms15 = $momsData["antal"];
                  }
                  $momsTotal+= $momsData["antal"];
                }

              }

              $t = $countInCompany - $momsTotal;
              $moms25+= $t;

              // Add count to data
              $datalist[$currentCompanyKey]["countincompany"] += $countInCompany;
              $datalist[$currentCompanyKey]["moms25"] += $moms25;
              $datalist[$currentCompanyKey]["moms15"] += $moms15;
              $datalist[$currentCompanyKey]["moms0"] += $moms0;
              $datalist[$currentCompanyKey]["countoutsidecompany"] += $countOutsideCompany;
          }
          $lastCompanyKey = $currentCompanyKey;
     }

     foreach($datalist as $mData)
     {

        $isdromme = in_array($mData["shop_id"],$drommeShops);
        if($mData["countincompany"] > 0)
        {
          fwrite($output,
            $mData["order_no"].";".
            ";".
            utf8_decode($shopNames[$mData["shop_id"]]).";".
            utf8_decode($expiredate).";".
            ";".
            ";".
            ";".
            utf8_decode($mData["name"]).";".
            utf8_decode($mData["cvr"]).";".
            utf8_decode($mData["contact_name"]).";".
            utf8_decode($mData["bill_to_address"]).";".
            utf8_decode($mData["bill_to_address_2"]).";".
            utf8_decode($mData["bill_to_postal_code"]).";".
            utf8_decode($mData["bill_to_city"]).";".
            utf8_decode($mData["bill_to_country"]).";".
            utf8_decode($mData["ship_to_address"]).";".
            utf8_decode($mData["ship_to_address_2"]).";".
            utf8_decode($mData["ship_to_postal_code"]).";".
            utf8_decode($mData["ship_to_city"]).";".
            ";".
            utf8_decode($mData["contact_email"]).";".
            utf8_decode($mData["contact_phone"]).";".
            ";".
            ";".
            utf8_decode($mData["ean"]).";".
            utf8_decode($mData["ship_to_company"]).";".
            $mData["countincompany"].";".
            $this->calculatefreight($mData["countincompany"],($isdk && $isdromme)).";".
            $mData["moms25"].";".
            $mData["moms15"].";".
            $mData["moms0"].";".
            $mData["countoutsidecompany"].";"."\n");

         }
     }

  }

  public function toreexport()
  {
  return;
     $sqlCompanyOrder = "SELECT company_name, ship_to_address, ship_to_postal_code, ship_to_city, salesperson, shop_name,quantity, shop_id FROM `company_order` WHERE shop_id IN (272,57,58,59,574) && is_cancelled = 0";
     $companyOrderList = Dbsqli::getSql2($sqlCompanyOrder);

      $filename = "no-order-export.csv";
      header('Content-Type: text/csv; charset=utf-8');
      header('Content-Disposition: attachment; filename='.$filename.'');
      $output = fopen('php://output', 'w');
      fwrite($output,utf8_decode("Kundenavn;Adresse;Postnr;By;Selger;Type kort;Antall\n"));

      foreach($companyOrderList as $co)
      {
        fwrite($output,utf8_decode(implode(";",array($co["company_name"],$co["ship_to_address"],$co["ship_to_postal_code"],$co["ship_to_city"],$co["salesperson"],trimgf($co["shop_name"]) == "" ? "shop ".$co["shop_id"] : $co["shop_name"],$co["quantity"]))."\n"));
      }

  }

  public function bsfragttotal(){



        if($_GET["token"] != "324jlh2345jkFSd12jcvmcpq463w"){  die("Ingen adgang"); }
        $expiredate = $_GET["expiredate"];
        $isdk = (isset($_GET["isdk"]) && $_GET["isdk"] == "1");
        $counter = 0;
        $cvrCount = array();
        $eanCount = array();

        $drommeShops = array(290,310);

        // VÆLG SHOPS
        if(!$isdk)
        {
          $shops = array(272,57,58,59,574);
          $filename = "fragtjournal-total-".$expiredate."_no.csv";
        }
        else
        {
          $shops = array(52,575,54,55,56,53,290,310);
          $filename = "fragtjournal-total-".$expiredate."_dk.csv";
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename='.$filename.'');
        $output = fopen('php://output', 'w');
        fwrite($output,utf8_decode("Ordrenr;Antal;Shop ID;Udløb uge;Gavekortnr. start;Gavekortnr. slut;Gavekort værdi;Virksomhedsnavn;CVR;Attention;Faktura adresse;Faktura adresse 2;Faktura postnr.;Faktura by;Faktura land;Levering adresse;Levering adresse 2;Levering postnr.;Levering by;Levering land;Email;Telefon;Elektronisk kort;Tillægsordre;EAN;Levering virksomhed;Total antal;Fragt;Moms 25;Moms 15;Moms 0;Flyttede kort\n"));

        // Code to resctrict to bs nr
        /*
         $validNumbers = array('BS19503','BS17164','BS20557','BS17395','BS21638','BS19580','BS19596','BS19597','BS19608','BS19610','BS19612','BS19614','BS20876','BS17380','BS17918','BS17919','BS17921','BS17922','BS17924','BS17925','BS17926','BS17927','BS17928','BS17929','BS17930','BS17931','BS17932','BS17933','BS17934','BS17935','BS17936','BS17937','BS17938','BS17939','BS17940','BS17941','BS17942','BS17943','BS18102','BS18111','BS18116','BS18117','BS18118','BS18121','BS18123','BS18125','BS20320','BS16707','BS20515','BS20516','BS16685','BS18748','BS19240','BS18514','BS18503','BS18505','BS18508','BS18511','BS18517','BS18520','BS18523','BS18527','BS18529','BS18532','BS18533','BS18534','BS18535','BS18536','BS18538','BS18540','BS18542','BS18465','BS18467','BS18468','BS18471','BS18473','BS18480','BS18481','BS18482','BS18485','BS18486','BS18488','BS18489','BS18490','BS18493','BS18495','BS18500','BS18501','BS18525','BS18462','BS16467','BS16470','BS16472','BS16478','BS16494','BS16495','BS16496','BS16500','BS16501','BS16502','BS16504','BS16505','BS16509','BS16510','BS16511','BS16514','BS21334','BS22678','BS22679','BS22027','BS22028','BS22029','BS22030','BS22031','BS22032','BS22033','BS22034','BS22035','BS22036','BS22037','BS22038','BS22039','BS22040','BS22041','BS22043','BS22044','BS22045','BS22046','BS22047','BS22048','BS22049','BS22050','BS22051','BS22052','BS22053','BS22054','BS22151','BS22471','BS17239','BS17407','BS17857','BS17197','BS17588','BS17604','BS17808','BS17880','BS17972','BS18186','BS18712','BS19979','BS20039','BS20499','BS21052','BS16293','BS22705','BS16524','BS22490','BS22491','BS19393','BS22936','BS22840','BS19493','BS19435','BS19436','BS19437','BS19438','BS22987','BS22988','BS15765','BS22996','BS17084','BS17085','BS17086','BS17087','BS23205','BS17575','BS17576','BS17577','BS17578','BS17582','BS17583','BS17584','BS17586','BS17689','BS17585','BS17579','BS17580','BS17587','BS17581','BS23007','BS23008','BS23009','BS21156','BS23010','BS23011','BS15796','BS15797','BS15798','BS17550','BS22127','BS22130','BS17404','BS22564','BS18065','BS18066','BS18067','BS21182','BS19817','BS20173','BS20178','BS20179','BS20181','BS20184','BS20186','BS20187','BS20188','BS20190','BS20192','BS20198','BS20204','BS20206','BS20207','BS20208','BS20210','BS20213','BS20214','BS20216','BS20219','BS20222','BS20224','BS20227','BS20228','BS20230','BS20231','BS20624','BS20736','BS17716','BS17717','BS17721','BS17726','BS17728','BS17730','BS17731','BS17732','BS17733','BS17735','BS17737','BS17738','BS17740','BS17742','BS15799','BS16272');
             // in_array($mData["order_no"],$validNumbers) &&
             // && company_order.order_no IN ('".implode("','",$validNumbers)."')
          */

        // Load company_orders that have been synced and is not cancelled for companies that are active
        $sqlCompanyOrder = "SELECT company.*, certificate_no_begin, certificate_no_end, company_id, order_no, shop_id  FROM `company_order`, company WHERE company_order.company_id = company.id &&  navsync_status = 3 && is_cancelled = 0 && company_order.shop_id IN (".implode(",",$shops).") && expire_date = '".$expiredate."' && company.deleted = 0 && company.active = 1 && company.onhold = 0  order by company.nav_debitor_no asc, company.cvr asc, company_order.shop_id, company.id asc";
        $companyOrderList = Dbsqli::getSql2($sqlCompanyOrder);

        $lastCompanyKey = "";
        $datalist = array();

        foreach($companyOrderList as $mData)
        {

              $currentCompanyKey = $mData["nav_debitor_no"]."-".$mData["cvr"]."-".$mData["shop_id"]; // ."-".$mData["order_no"]


              // Add company to datalist
              if(!isset($datalist[$currentCompanyKey]))
              {
                $datalist[$currentCompanyKey] = $mData;
                $datalist[$currentCompanyKey]["countincompany"] = 0;
                $datalist[$currentCompanyKey]["moms25"] = 0;
                $datalist[$currentCompanyKey]["moms15"] = 0;
                $datalist[$currentCompanyKey]["moms0"] = 0;
                $datalist[$currentCompanyKey]["countoutsidecompany"] = 0;
              }

              // Init counters
              $moms25 = 0; $moms15 = 0; $moms0 = 0; $momsTotal = 0;
              $countInCompany = 0; $countOutsideCompany = 0; $activeShopusers = "";


              // Get active shopusers in order and group by company
              $sqlShopUser = "SELECT count(id) as antal, group_concat(id) as userlist, company_id FROM `shop_user` WHERE is_giftcertificate = 1 && username BETWEEN '".$mData["certificate_no_begin"]."' AND '".$mData["certificate_no_end"]."' && is_demo = 0 && blocked = 0 && is_delivery = 0 group by company_id";

              // Shopuser results
              $shopusermap = Dbsqli::getSql2($sqlShopUser);
              foreach($shopusermap as $shopuserresult)
              {
                if($shopuserresult["company_id"] == $mData["company_id"])
                {
                  $countInCompany = $shopuserresult["antal"];
                  $activeShopusers = $shopuserresult["userlist"];
                  if(substr($activeShopusers,-1) == ",") $activeShopusers = substr($activeShopusers,0,-1);
                }
                else
                {
                  $countOutsideCompany += $shopuserresult["antal"];
                }
              }

              // Get moms count
              if($countInCompany > 0)
              {
                $momsSql = "select count(*) as antal, moms,created_datetime from present inner join `order` on present.id =  `order`.present_id where `order`.company_id = ".$mData["company_id"]." && `order`.shopuser_id in (".$activeShopusers.") group by moms";

                $momsRs = Dbsqli::getSql2($momsSql);

                foreach($momsRs as $momsData){
                  if($momsData["moms"] == "0"){
                    $moms0 = $momsData["antal"];
                  }
                  if($momsData["moms"] == "25"){
                    $moms25 = $momsData["antal"];
                  }
                  if($momsData["moms"] == "15"){
                    $moms15 = $momsData["antal"];
                  }
                  $momsTotal+= $momsData["antal"];
                }

              }

              $t = $countInCompany - $momsTotal;
              $moms25+= $t;

              // Add count to data
              $datalist[$currentCompanyKey]["countincompany"] += $countInCompany;
              $datalist[$currentCompanyKey]["moms25"] += $moms25;
              $datalist[$currentCompanyKey]["moms15"] += $moms15;
              $datalist[$currentCompanyKey]["moms0"] += $moms0;
              $datalist[$currentCompanyKey]["countoutsidecompany"] += $countOutsideCompany;

     }

     foreach($datalist as $mData)
     {

        $isdromme = in_array($mData["shop_id"],$drommeShops);
        if($mData["countincompany"] > 0)
        {
          fwrite($output,
            $mData["order_no"].";".
            ";".
            $mData["shop_id"].";".
            utf8_decode($expiredate).";".
            ";".
            ";".
            ";".
            utf8_decode($mData["name"]).";".
            utf8_decode($mData["cvr"]).";".
            utf8_decode($mData["contact_name"]).";".
            utf8_decode($mData["bill_to_address"]).";".
            utf8_decode($mData["bill_to_address_2"]).";".
            utf8_decode($mData["bill_to_postal_code"]).";".
            utf8_decode($mData["bill_to_city"]).";".
            utf8_decode($mData["bill_to_country"]).";".
            utf8_decode($mData["ship_to_address"]).";".
            utf8_decode($mData["ship_to_address_2"]).";".
            utf8_decode($mData["ship_to_postal_code"]).";".
            utf8_decode($mData["ship_to_city"]).";".
            ";".
            utf8_decode($mData["contact_email"]).";".
            utf8_decode($mData["contact_phone"]).";".
            ";".
            ";".
            utf8_decode($mData["ean"]).";".
            utf8_decode($mData["ship_to_company"]).";".
            $mData["countincompany"].";".
            $this->calculatefreight($mData["countincompany"],($isdk && $isdromme)).";".
            $mData["moms25"].";".
            $mData["moms15"].";".
            $mData["moms0"].";".
            $mData["countoutsidecompany"].";"."\n");

         }
     }

  }


   private function calculatefreight($quantity,$isDrommegavekortet=false) {


       // Drommegavekort
       if($isDrommegavekortet)
       {
           if($quantity >=  0  && $quantity <= 1)        { return(188.0); }
           elseif($quantity >=  2  && $quantity <= 4)    { return(268.0); }
           elseif($quantity >=  5  && $quantity <= 10)   { return(528.0); }
           elseif($quantity >=  11 && $quantity <= 20)   { return(748.0); }
           elseif($quantity >=  21 && $quantity <= 40)   { return(1496.0); }
           elseif($quantity >=  41 && $quantity <= 60)   { return(2244.0); }
           elseif($quantity >=  61 && $quantity <= 80)   { return(2992.0); }
           elseif($quantity >=  81 && $quantity <= 100)  { return(3740.0); }
           elseif($quantity >=  101 && $quantity <= 120) { return(4488.0); }
           elseif($quantity >=  121 && $quantity <= 140) { return(5236.0); }
           elseif($quantity >=  141 && $quantity <= 160) { return(5984.0); }
           elseif($quantity >=  161 && $quantity <= 180) { return(6732.0); }
           elseif($quantity >=  181 && $quantity <= 200) { return(7480.0); }
           elseif($quantity >=  201 && $quantity <= 220) { return(8228.0); }
           elseif($quantity >=  221 && $quantity <= 240) { return(8976.0); }
           elseif($quantity >=  241 && $quantity <= 260) { return(9724.0); }
           elseif($quantity >=  261 && $quantity <= 280) { return(10472.0); }
           elseif($quantity >=  281 && $quantity <= 300) { return(11220.0); }
           elseif($quantity >=  301 && $quantity <= 320) { return(11968.0); }
           elseif($quantity >=  321 && $quantity <= 340) { return(12716.0); }
           elseif($quantity >=  341 && $quantity <= 360) { return(13464.0); }
           elseif($quantity >=  361 && $quantity <= 380) { return(14212.0); }
           elseif($quantity >=  381 && $quantity <= 400) { return(14960.0); }
           elseif($quantity >400 ) { return(15708.0); }
       }

       // Other dk freight
       else
       {
           if($quantity >=  0  && $quantity <= 2)        { return(268.0); }
           elseif($quantity >=  3  && $quantity <= 8)    { return(528.0); }
           elseif($quantity >=  9 && $quantity <= 14)    { return(748.0); }
           elseif($quantity >=  15 && $quantity <= 20)   { return(1122.0); }
           elseif($quantity >=  21 && $quantity <= 30)   { return(1496.0); }
           elseif($quantity >=  31 && $quantity <= 40)   { return(1870.0); }
           elseif($quantity >=  41 && $quantity <= 60)   { return(2244.0); }
           elseif($quantity >=  61 && $quantity <= 80)   { return(2992.0); }
           elseif($quantity >=  81 && $quantity <= 100)  { return(3740.0); }
           elseif($quantity >=  101 && $quantity <= 120) { return(4488.0); }
           elseif($quantity >=  121 && $quantity <= 140) { return(5236.0); }
           elseif($quantity >=  141 && $quantity <= 160) { return(5984.0); }
           elseif($quantity >=  161 && $quantity <= 180) { return(6732.0); }
           elseif($quantity >=  181 && $quantity <= 200) { return(7480.0); }
           elseif($quantity >=  201 && $quantity <= 220) { return(8228.0); }
           elseif($quantity >=  221 && $quantity <= 240) { return(8976.0); }
           elseif($quantity >=  241 && $quantity <= 260) { return(9724.0); }
           elseif($quantity >=  261 && $quantity <= 280) { return(10472.0); }
           elseif($quantity >=  281 && $quantity <= 300) { return(11220.0); }
           elseif($quantity >=  301 && $quantity <= 320) { return(11968.0); }
           elseif($quantity >=  321 && $quantity <= 340) { return(12716.0); }
           elseif($quantity >=  341 && $quantity <= 360) { return(13464.0); }
           elseif($quantity >=  361 && $quantity <= 380) { return(14212.0); }
           elseif($quantity >=  381 && $quantity <= 400) { return(14960.0); }
           elseif($quantity >400 ) { return(15708.0); }
       }
   }


  /* old
  private function calculatefreight($quantity,$isDrommegavekortet=false) {


    if($isDrommegavekortet == true)
    {

       if($quantity >=  0  && $quantity <= 20)       { return(596.0); }
       elseif($quantity >=  21 && $quantity <= 40)   { return(1192.0); }
       elseif($quantity >=  41 && $quantity <= 60)   { return(1788.0); }
       elseif($quantity >=  61 && $quantity <= 80)   { return(2384.0); }
       elseif($quantity >=  81 && $quantity <= 100)  { return(2980.0); }
       elseif($quantity >=  101 && $quantity <= 120) { return(3576.0); }
       elseif($quantity >=  121 && $quantity <= 140) { return(4172.0); }
       elseif($quantity >=  141 && $quantity <= 160) { return(4768.0); }
       elseif($quantity >=  161 && $quantity <= 180) { return(5364.0); }
       elseif($quantity >=  181 && $quantity <= 200) { return(5780.0); }
       elseif($quantity >=  201 && $quantity <= 220) { return(6358.0); }
       elseif($quantity >=  221 && $quantity <= 240) { return(6936.0); }
       elseif($quantity >=  241 && $quantity <= 260) { return(7514.0); }
       elseif($quantity >=  261 && $quantity <= 280) { return(8092.0); }
       elseif($quantity >=  281 && $quantity <= 300) { return(8670.0); }
       elseif($quantity >=  301 && $quantity <= 320) { return(9248.0); }
       elseif($quantity >=  321 && $quantity <= 340) { return(9826.0); }
       elseif($quantity >=  341 && $quantity <= 360) { return(10404.0); }
       elseif($quantity >=  361 && $quantity <= 380) { return(10982.0); }
       elseif($quantity >=  381 && $quantity <= 400) { return(11200.0); }
       elseif($quantity >400 ) { return(11760.0); }

    }
    else
    {

         if($quantity >=  0  && $quantity <= 11)       { return(596.0); }
       elseif($quantity >=  12 && $quantity <= 20)   { return(894.0); }
       elseif($quantity >=  21 && $quantity <= 30)   { return(1192.0); }
       elseif($quantity >=  31 && $quantity <= 40)   { return(1490.0); }
       elseif($quantity >=  41 && $quantity <= 60)   { return(1788.0); }
       elseif($quantity >=  61 && $quantity <= 80)   { return(2384.0); }
       elseif($quantity >=  81 && $quantity <= 100)  { return(2980.0); }
       elseif($quantity >=  101 && $quantity <= 120) { return(3576.0); }
       elseif($quantity >=  121 && $quantity <= 140) { return(4172.0); }
       elseif($quantity >=  141 && $quantity <= 160) { return(4768.0); }
       elseif($quantity >=  161 && $quantity <= 180) { return(5364.0); }
       elseif($quantity >=  181 && $quantity <= 200) { return(5780.0); }
       elseif($quantity >=  201 && $quantity <= 220) { return(6358.0); }
       elseif($quantity >=  221 && $quantity <= 240) { return(6936.0); }
       elseif($quantity >=  241 && $quantity <= 260) { return(7514.0); }
       elseif($quantity >=  261 && $quantity <= 280) { return(8092.0); }
       elseif($quantity >=  281 && $quantity <= 300) { return(8670.0); }
       elseif($quantity >=  301 && $quantity <= 320) { return(9248.0); }
       elseif($quantity >=  321 && $quantity <= 340) { return(9826.0); }
       elseif($quantity >=  341 && $quantity <= 360) { return(10404.0); }
       elseif($quantity >=  361 && $quantity <= 380) { return(10982.0); }
       elseif($quantity >=  381 && $quantity <= 400) { return(11200.0); }
       elseif($quantity >400 ) { return(11760.0); }

    }
   }
   */

    // Fragtliste der trækker hver ordre ud enkeltvis
    public function bsfragtsingle(){

        if($_GET["token"] != "324jlh2345jkFSd12jcvmcpq463w"){  die("Ingen adgang"); }
        $expiredate = $_GET["expiredate"];
        $isdk = (isset($_GET["isdk"]) && $_GET["isdk"] == "1");
        $counter = 0;
        $cvrCount = array();
        $eanCount = array();

        $drommeShops = array(290,310);

        $shopNames = array(
           290 => "Drømmegavekortet 200",
           310 => "Drømmegavekortet 300",
           272 => "Julegavekortet NO 300",
           57 => "Julegavekortet NO 400",
           58 => "Julegavekortet NO 600",
           59 => "Julegavekortet NO 800",
           574 => "Guldgavekort NO",
           52 => "Julegavekortet DK",
           575 => "Designjulegaven",
           54 => "24 Gaver 400",
           55 => "24 Gaver 560",
           56 => "24 Gaver 640",
           53 => "Guldgavekortet DK"
        );

        // VÆLG SHOPS
        if(!$isdk)
        {
          $shops = array(272,57,58,59,574);
          $filename = "fragtjournal-".$expiredate."_no.csv";
        }
        else
        {
          $shops = array(52,575,54,55,56,53,290,310);
          $filename = "fragtjournal-".$expiredate."_dk.csv";
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename='.$filename.'');
        $output = fopen('php://output', 'w');
        fwrite($output,utf8_decode("Ordrenr;Antal;Shop ID;Udløb uge;Gavekortnr. start;Gavekortnr. slut;Gavekort værdi;Virksomhedsnavn;CVR;Attention;Faktura adresse;Faktura adresse 2;Faktura postnr.;Faktura by;Faktura land;Levering adresse;Levering adresse 2;Levering postnr.;Levering by;Levering land;Email;Telefon;Elektronisk kort;Tillægsordre;EAN;Levering virksomhed;Total antal;Fragt;Moms 25;Moms 15;Moms 0;Flyttede kort\n"));


        // Load company_orders that have been synced and is not cancelled for companies that are active
        $sqlCompanyOrder = "SELECT company.*, certificate_no_begin, certificate_no_end, company_id, order_no, shop_id  FROM `company_order`, company WHERE company_order.company_id = company.id  && is_cancelled = 0 && company_order.shop_id IN (".implode(",",$shops).") && expire_date = '".$expiredate."' && company.deleted = 0 && company.active = 1 && company.onhold = 0 && navsync_status = 100  order by company.id asc, company.cvr asc, company_order.shop_id asc";
        $companyOrderList = Dbsqli::getSql2($sqlCompanyOrder);

        // Maybe make a new query for other deadlines that has shopusers with this expire date
        // TODO - 30/1 - Soren asked Kristina

        $lastCompanyKey = "";
        $datalist = array();

        foreach($companyOrderList as $mData)
        {
            $currentCompanyKey = $mData["company_id"]."-".$mData["cvr"]."-".$mData["shop_id"];

            if($currentCompanyKey == $lastCompanyKey)
            {

              $listKey = $mData["company_id"]."-".$mData["cvr"]."-".$mData["shop_id"]."-".$mData["order_no"];

              // Add company to datalist
              if(!isset($datalist[$listKey]))
              {
                $datalist[$listKey] = $mData;
                $datalist[$listKey]["countincompany"] = 0;
                $datalist[$listKey]["moms25"] = 0;
                $datalist[$listKey]["moms15"] = 0;
                $datalist[$listKey]["moms0"] = 0;
                $datalist[$listKey]["countoutsidecompany"] = 0;
              }

              // Init counters
              $moms25 = 0; $moms15 = 0; $moms0 = 0; $momsTotal = 0;
              $countInCompany = 0; $countOutsideCompany = 0; $activeShopusers = "";


              // Get active shopusers in order and group by company
              $sqlShopUser = "SELECT count(id) as antal, group_concat(id) as userlist, company_id FROM `shop_user` WHERE is_giftcertificate = 1 && username BETWEEN '".$mData["certificate_no_begin"]."' AND '".$mData["certificate_no_end"]."' && is_demo = 0 && blocked = 0 && is_delivery = 0 group by company_id";

              // Shopuser results
              $shopusermap = Dbsqli::getSql2($sqlShopUser);
              foreach($shopusermap as $shopuserresult)
              {
                if($shopuserresult["company_id"] == $mData["company_id"])
                {
                  $countInCompany = $shopuserresult["antal"];
                  $activeShopusers = $shopuserresult["userlist"];
                  if(substr($activeShopusers,-1) == ",") $activeShopusers = substr($activeShopusers,0,-1);
                }
                else
                {
                  $countOutsideCompany += $shopuserresult["antal"];
                }
              }

              // Get moms count
              if($countInCompany > 0)
              {
                $momsSql = "select count(*) as antal, moms,created_datetime from present inner join `order` on present.id =  `order`.present_id where `order`.company_id = ".$mData["company_id"]." && `order`.shopuser_id in (".$activeShopusers.") group by moms";

                $momsRs = Dbsqli::getSql2($momsSql);

                foreach($momsRs as $momsData){
                  if($momsData["moms"] == "0"){
                    $moms0 = $momsData["antal"];
                  }
                  if($momsData["moms"] == "25"){
                    $moms25 = $momsData["antal"];
                  }
                  if($momsData["moms"] == "15"){
                    $moms15 = $momsData["antal"];
                  }
                  $momsTotal+= $momsData["antal"];
                }

              }

              $t = $countInCompany - $momsTotal;
              $moms25+= $t;

              // Add count to data
              $datalist[$listKey]["countincompany"] += $countInCompany;
              $datalist[$listKey]["moms25"] += $moms25;
              $datalist[$listKey]["moms15"] += $moms15;
              $datalist[$listKey]["moms0"] += $moms0;
              $datalist[$listKey]["countoutsidecompany"] += $countOutsideCompany;
          }
          $lastCompanyKey = $currentCompanyKey;
     }

     foreach($datalist as $mData)
     {

        $isdromme = in_array($mData["shop_id"],$drommeShops);
        if($mData["countincompany"] > 0)
        {
          fwrite($output,
            $mData["order_no"].";".
            ";".
            utf8_decode($shopNames[$mData["shop_id"]]).";".
            utf8_decode($expiredate).";".
            ";".
            ";".
            ";".
            utf8_decode($mData["name"]).";".
            utf8_decode($mData["cvr"]).";".
            utf8_decode($mData["contact_name"]).";".
            utf8_decode($mData["bill_to_address"]).";".
            utf8_decode($mData["bill_to_address_2"]).";".
            utf8_decode($mData["bill_to_postal_code"]).";".
            utf8_decode($mData["bill_to_city"]).";".
            utf8_decode($mData["bill_to_country"]).";".
            utf8_decode($mData["ship_to_address"]).";".
            utf8_decode($mData["ship_to_address_2"]).";".
            utf8_decode($mData["ship_to_postal_code"]).";".
            utf8_decode($mData["ship_to_city"]).";".
            ";".
            utf8_decode($mData["contact_email"]).";".
            utf8_decode($mData["contact_phone"]).";".
            ";".
            ";".
            utf8_decode($mData["ean"]).";".
            utf8_decode($mData["ship_to_company"]).";".
            $mData["countincompany"].";".
            $this->calculatefreight($mData["countincompany"],($isdk && $isdromme)).";".
            $mData["moms25"].";".
            $mData["moms15"].";".
            $mData["moms0"].";".
            $mData["countoutsidecompany"].";"."\n");

         }
     }

  }

}
?>