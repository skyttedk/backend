<?php 


Class alertController Extends baseController 
{


    public function index()
    {
      echo "Invalid endpoint";
    }
    
    public function varenrtjek()
    {

        $varenrCacheModel = new \GFCommon\Model\Navision\VareNrCaching(250);
        $varenrCacheModel->runVareNrSync();
        $varenrCacheModel->printActionsLog();
    }

    public function varenrlist()
    {

        $query = isset($_GET["query"]) ? trimgf($_GET["query"]) : "";
        $shoplist = Shop::find_by_sql("SELECT * FROM shop where id = ".intval($query));

        ?><style>
            td {padding: 5px; border-bottom: 1px solid #C0C0C0; }
        </style><?php

        foreach($shoplist as $index => $shop) {
            echo "<h2>".$shop->name." [".$shop->id."]</h2><br>";

            $presentList = Present::find_by_sql("SELECT * FROM present WHERE shop_id = ".intval($shop->id));
            $presentModelList = PresentModel::find_by_sql("SELECT * FROM present_model WHERE present_id IN (SELECT id FROM present WHERE shop_id = ".intval($shop->id).")");
            $usedModels =  array();

            echo "Shop har ".countgf($presentList)." gaver og ".countgf($presentModelList)." modeller<br><br>";
            echo "<table cellpadding='0' cellspacing='0'><tr style='font-weight: bold; font-size: 1.5em;'><td>Gave</td><td>Varenr</td><td>Gavestatus</td><td>Varenr status</td></tr>";

            foreach($presentList as $present) {

                $presentStatus = "?";
                if($present->active == 1 && $present->deleted == 1) $presentStatus = "Aktiv og slettet";
                if($present->active == 0 && $present->deleted == 0) $presentStatus = "Ikke aktiv og ikke slettet";
                if($present->active == 1 && $present->deleted == 0) $presentStatus = "Aktiv";
                if($present->active == 0 && $present->deleted == 1) $presentStatus = "Slettet";

                echo "<tr style='background: #C0C0C0; font-weight: bold;'><td colspan='2'>".utf8_decode($present->name." / ".$present->nav_name)."</td><td>".$present->state." - ".$presentStatus."</td><td>&nbsp;</td></tr>";

                foreach($presentModelList as $presentModel) {

                    $varenr = trimgf(mb_strtolower($presentModel->model_present_no));
                    $varenrKey = $presentModel->model_id.$varenr;

                        if($presentModel->present_id == $present->id) {
                            if(!in_array($varenrKey,$usedModels)) {

                                $usedModels[] = $varenrKey;

                                $modelStatus = "?";
                                $modelColor = "#F0F0F0";

                                if($presentModel->active == 0 && $presentModel->is_deleted == 1) {
                                    $modelStatus = "Aktiv og slettet";
                                    $modelColor = "orangered";
                                }
                                if($presentModel->active == 1 && $presentModel->is_deleted == 0) {
                                    $modelStatus = "Ikke aktiv og ikke slettet";
                                    $modelColor = "orangered";
                                }
                                if($presentModel->active == 0 && $presentModel->is_deleted == 0)
                                {
                                    $modelStatus = "Aktiv";
                                    $modelColor = "green";
                                }
                                if($presentModel->active == 1 && $presentModel->is_deleted == 1)
                                {
                                    $modelStatus = "Slettet";
                                    $modelColor = "red";
                                }


                                $varenrStatus = "?";
                                $varenrColor = "#F0F0F0";

                            if($varenr == "") {
                                $varenrStatus = "VARENR TOMT";
                                $varenrColor = "orangered";
                            }
                            else {
                                $vareNrCache = \GFCommon\DB\VareNrCache::getByVareNr($varenr);
                                if($vareNrCache == null) {
                                    $varenrStatus = "IKKE TJEKKET ENDNU";
                                    $varenrColor = "orangered";
                                } else if($vareNrCache->getIsvalid() == 0) {
                                    $varenrStatus = "IKKE GYLDIGT";
                                    $varenrColor = "red";
                                } else if($vareNrCache->getIsvalid() == 1) {
                                    $varenrStatus = "OK";
                                    $varenrColor = "green";
                                } else {
                                    $varenrStatus = "UKENDT STATUS";
                                    $varenrColor = "orangered";
                                }
                            }

                            echo "<tr><td>".$presentModel->model_name."</td><td>".$varenr."</td><td style='background: ".$modelColor.";'>".$modelStatus."</td><td style='background: ".$varenrColor.";'>".$varenrStatus."</td></tr>";
                        }
                    }


                }

            }

            echo "</table>";
            if($index >= 2) exit();
        }

    }
    
    public function systemstate()
    {
    
        $token = isset($_GET["token"]) ? $_GET["token"] : "";
        if($token != "dfk456Dhk3kxcdk6fdjkdnv") return;
    
        // Load errors
        $hourLogs = SystemLog::find_by_sql("SELECT * FROM system_log WHERE error_message IS NOT NULL AND error_trace IS NOT NULL AND error_message NOT LIKE '%Ugyldig login.%' AND created_datetime >= DATE_SUB(NOW(),INTERVAL 1 HOUR)");
        $todayLogs = SystemLog::find_by_sql("SELECT * FROM system_log WHERE error_message IS NOT NULL AND error_trace IS NOT NULL AND error_message NOT LIKE '%Ugyldig login.%' AND DATE(created_datetime) = CURRENT_DATE");
        
        // Find mailqueue data
        $sentToday = MailQueue::find_by_sql("SELECT count(*) as count FROM mail_queue WHERE sent = 1 AND DATE(created_datetime) = CURRENT_DATE");
        $waiting =  MailQueue::find_by_sql("SELECT count(*) as count FROM mail_queue WHERE sent =0 && error = 0");
        $errors =  MailQueue::find_by_sql("SELECT count(*) as count FROM mail_queue WHERE error = 1 && created_datetime >= DATE_SUB(NOW(),INTERVAL 48 HOUR)");
                                                                                                                                             
        echo "<h3>Mailkø status:</h3>";
        echo "<table>
          <tr>
            <td>Sendt i dag</td><td>".$sentToday[0]->count."</td>
            </tr><tr>
            <td>Venter</td><td>".$waiting[0]->count."</td>
            </tr><tr>
            <td>Mailkø færdig om ca.</td><td>".($waiting[0]->count/15)." minutter</td>
            </tr><tr>
            <td>Fejl de sidste 48 timer</td><td>".$errors[0]->count."</td>
            </tr>
        </table><br>";                                  
        
        echo "<h3>Fejllog status:</h3>";
        echo "Fejl seneste time: ".countgf($hourLogs)."<br>";
        echo "Fejl i dag: ".countgf($todayLogs)."<br>";
                                   
      echo "<h3>Fejltyper i dag</h3>";
      
      
        
          $groupedErrors = array();
          if(count($todayLogs) > 0)
          {
             foreach($todayLogs as $log)
             {
                $key = $log->controller."_".$log->action."_".substr($log->error_message,0,25);
                if(!isset($groupedErrors[$key])) $groupedErrors[$key] = 0;
                $groupedErrors[$key]++;
             }                         
          }
          
          $sortGroups = array();
          foreach($groupedErrors as $key => $value)
          {
             $sortGroups[] = array("key" => $key, "value" => $value);
          }
           
          usort($sortGroups, function ($item1, $item2) {
            if ($item1['value'] == $item2['value']) return 0;
            return $item1['value'] < $item2['value'] ? 1 : -1;
          });
          
           echo "<table style=\"width: 100%;\">
      <tr>
            <td>Antal</td>
            <td>Controller</td>
            <td>Action</td>
            <td>Besked</td>
            
      </tr>";                                                                                                                   
           
            if(count($sortGroups) > 0)
           {
             foreach($sortGroups as $line)
             {
                $split = explode("_",$line["key"],3);
                echo "<tr>
                  <td>".$line["value"]."</td>
                  <td>".$split[0]."</td>
                  <td>".$split[1]."</td>
                  <td>".$split[2]."</td>
                </tr>";
             }
           }
           
           echo "</table>";
          
                     echo "<h3>Fejl den seneste time</h3>";
      echo "<table style=\"width: 100%;\">
      <tr>
            <td>id</td>
            <td>user_id</td>
            <td>controller</td>
            <td>action</td>
            <td>data</td>
            <td>created</td>
            <td>committed</td>
            <td>error_message</td>
            <td>error_trace</td>
      </tr>";                                                                                                                   
           
            if(count($todayLogs) > 0)
           {
             foreach($hourLogs as $log)
             {
              echo "<tr>
                <td>".$log->id."</td>
                <td>".$log->user_id."</td>
                <td>".$log->controller."</td>
                <td>".$log->action."</td>
                <td>".$log->data."</td>
                <td>".$log->created_datetime->format('d/m/Y H:i:s')."</td>
                <td>".$log->committed."</td>
                <td>".$log->error_message."</td>
                <td>".$log->error_trace."</td>
              </tr>";
             }
           }
           
           echo "</table>";
            
          
          
                                                                                                                                                                                                                                                 
    }
    
    
   /**
    * checkwrap
    * Checks orders with wrap if there are cards moved away from the order. These cards will no longer have wrap parameter
    */
   public function checkwrap()
   {
        $isWarn = isset($_GET["warn"]) && $_GET["warn"] == "1";
   
        $companyOrderList = CompanyOrder::find_by_sql("SELECT * FROM company_order WHERE id NOT IN (3734,3738,3739,5212) && giftwrap = 1");
        if($isWarn) echo countgf($companyOrderList)." orders with wrap<br><br>";
        $problems = 0;
        
        foreach($companyOrderList as $co)
        {
          $shopuserList = ShopUser::find_by_sql("SELECT * FROM shop_user WHERE company_id != ".$co->company_id." && username >= ".$co->certificate_no_begin." && username <= ".$co->certificate_no_end." && is_giftcertificate = 1");
          if(count($shopuserList) > 0) $problems++;
          if($isWarn == false && countgf($shopuserList) > 0) {
    
            echo "Company order: ".$co->id." - Cards outside order: ".countgf($shopuserList)."<br>";
            foreach($shopuserList as $su) echo $su->username.",";
            echo "<br>";
            
          }
        }
        
        if($isWarn && $problems > 0) {
          header("HTTP/1.0 500 Service down");
        }
   
        echo $problems." problems";
   
   }
   
   /**
    * preswarn
    * Monitoring service for presents and models, monitors presents and models in shops, to check for missing data or other problems
    */
   public function preswarn()
   {
      $errors = 0;
      $unprocessed = 0;
      $modelmap = array();
      $multipleinstances = 0;
      $missinglangs = 0;       
      $varenrmissing = 0;
      $varenrmismatch = 0;
      $presentvarenr = array();
      $missingmedia = 0;
      $duplicatevarenr = 0;
      
      $varenrpresentmissing = array();
      
      ob_start();
      
      // Excludeshops
      $excludeShops = array(266,577,578,579,580,0,576);
  
      // Load presents and make id
      $presentlist = Present::find_by_sql("select * from present WHERE shop_id NOT IN (".implode(",",$excludeShops).")");
      $presentmap = array();
      foreach($presentlist as $present) $presentmap[$present->id] = $present;
      
      // Load present models
      $modellist = Presentmodel::find_by_sql("select * from present_model WHERE model_id > 0 && present_id IN (select id from present where shop_id NOT IN (".implode(",",$excludeShops).")) ORDER BY present_id asc, model_id ASC, language_id ASC");
      
      foreach($modellist as $model)
      {
      
        $modelid = $model->model_id;
        $lang = $model->language_id;
        if($modelid > 0)
        {
          if(!isset($presentmap[$model->present_id]))
          {
            echo "Present id ".$model->present_id." is not set on model: ".$model->id."<br>";
            $errors++;
          }
          
          if(!isset($modelmap[$modelid])) $modelmap[$modelid] = array();
          if(!isset($modelmap[$modelid][$lang])) $modelmap[$modelid][$lang] = $model; 
          else {
            echo "Multiple instances of model id ".$modelid.", language ".$lang."<br>";
            $multipleinstances++;
            $errors++;
          }
        }
        else 
        {
          $unprocessed++;
          $errors++;
        }
      }
      
      $problemids = array();
      
    foreach($modelmap as $modelid => $map)
    {
    
      if(count($map) != 5)
      {
        echo "Model ".$modelid." only has ".countgf($map)." languages<br>";
        $missinglangs++;
        $errors++;
        $problemids[] = $modelid;
      }
      
      
      foreach($map as $lang => $model)
      {
        if($model->language_id == 1)
        {
        
          if(trimgf($model->media_path) == "")
          {
            echo "Model ".$modelid." lang 1: missing media path<br>";
            $missingmedia++;
            $errors++;
          }
              
        }
      
      }
      
      
      // Chek varenr
      $varenr = "";
      foreach($map as $lang => $model)
      {
        if($lang == 1 || $lang == 4)
        {
      	 if($varenr == "") $varenr = $model->model_present_no;
        }
      }
      if($varenr == "")  
      {
        $errors++;
      	 $varenrpresentmissing[] = $modelid;
      }
      
      
      
    }
    
      $sql =  "SELECT * FROM `present` WHERE id NOT IN (SELECT present_id FROM present_model) && shop_id > 0 && shop_id NOT IN (590000,601,579)";  
      $presentListWOModels = Present::find_by_sql($sql);
      if(count($presentListWOModels) > 0)
      {
        $errors++;
        echo countgf($presentListWOModels)." presents are missing models<br>";
      }
    
     echo "<h1>Results</h1><br>";
     echo "Loaded ".countgf($modelmap)." unique models<br>";
     echo "Models not loaded (non positive model id): ".$unprocessed."<br>";
     echo "Models with multiple instances (model/lang): ".$multipleinstances."<br>";     
     echo "Models with one or more missing languages: ".$missinglangs."<br>";
     echo "Models with missing media: ".$missingmedia."<br><br>";
     echo "Models with missing varenr: ".countgf($varenrpresentmissing)."<br><br>";
     echo "select * from present_model where model_id in (".implode(",",$problemids).") order by present_id, original_model_id, language_id<br><br>";
      echo "VARENR MISSING:  SELECT * FROM present_model WHERE model_id IN (".implode(",",$varenrpresentmissing).");<br>";
    
     $content = ob_get_contents();
     ob_end_clean();
     
      if($errors > 0)
	    {
    		header("HTTP/1.0 500 Service down");
    		echo $content;
    		exit();
	     }
        else echo "OK";
  
     //echo $content;

   }
   
   /**
    * presint2
    * Endpoint that makes a more deep check on presents and models, not for monitoring, but for debugging / investigating problems
    */ 
   public function presint2()
   {
   
      $unprocessed = 0;
      $modelmap = array();
      $multipleinstances = 0;
      $missinglangs = 0;       
      $varenrmissing = 0;
      $varenrmismatch = 0;
      $presentvarenr = array();
      $missingmedia = 0;
      $duplicatevarenr = 0;
      $missingvarenr = array();
      $mismatchvarenr = array();
      $varenrcheck_da = array();
      $duplicatevarenr_da = array();
      $varenrcheck_no = array();
      $duplicatevarenr_no = array();
      
      $varenrpresentmissing = array();
      
      // Excludeshops
      $excludeShops = array(266,577,578,579,580,0,576);
  
      // Load presents and make id
      $presentlist = Present::find_by_sql("select * from present WHERE shop_id NOT IN (".implode(",",$excludeShops).")");
      $presentmap = array();
      foreach($presentlist as $present) $presentmap[$present->id] = $present;
      
      // Load present models
      $modellist = Presentmodel::find_by_sql("select * from present_model WHERE model_id > 0 && present_id IN (select id from present where shop_id NOT IN (".implode(",",$excludeShops).")) ORDER BY present_id asc, model_id ASC, language_id ASC");
      
      // Place in model map and check for multiple instances
      foreach($modellist as $model)
      {
        $modelid = $model->model_id;
        $lang = $model->language_id;
        if(!isset($modelmap[$modelid])) $modelmap[$modelid] = array();
        if(!isset($modelmap[$modelid][$lang])) $modelmap[$modelid][$lang] = $model; 
        else {
          echo "Multiple instances of model id ".$modelid.", language ".$lang."<br>";
          $multipleinstances++;
        }
      }
      
      foreach($modelmap as $modelid => $map)
      {
    
        // Check for precisely 5 languages
        if(count($map) != 5)
        {
          echo "Model ".$modelid." only has ".countgf($map)." languages<br>";
          $missinglangs++;
        }
      
        $varenr = "";
        foreach($map as $lang => $model)
        {
      
          if($lang == 1 || $lang == 4)
          {
            if($varenr == "") $varenr = $model->model_present_no;
         
            // Varenr not set, save missing varenr
            if(trimgf($model->model_present_no) == "") 
            {
                //echo "Model ".$modelid." / lang ".$lang." has no varenr<br>";
                $varenrmissing++;
                $missingvarenr[] = $modelid;
                // PROBLEM?
            }
            
            // Varenr not same as other models
            if($varenr != "" && $varenr != $model->model_present_no)
            {
              //echo "Model ".$modelid." / lang ".$lang." has varenr mismatch (org: ".$varenr."): ".$model->model_present_no."<br>";
              $varenrmismatch++;
              $mismatchvarenr[] = $modelid;
              // PROBLEM?
            }
      
            // Check danish for duplicate varenr
            if($model->language_id == 1)
            {
        
              if(!isset($varenrcheck_da[$model->present_id])) $varenrcheck_da[$model->present_id] = array();
              if(!isset($varenrcheck_da[$model->present_id][$model->model_present_no])) $varenrcheck_da[$model->present_id][$model->model_present_no] = $model->model_id;
              else 
              {
                $duplicatevarenr_da[] = $model->present_id;
                //if($varenrcheck_da[$model->present_id][$model->model_present_no] != $model->model_id) echo "DUPLICATE VARENR! ".$model->present_id."<br>";
                //else echo "VARE NR SET MULTIPLE TIMES<bR>";
              }
            
              if(trimgf($model->media_path) == "")
              {
                echo "Model ".$modelid." lang 1: missing media path<br>";
                $missingmedia++;
              }
          
        
              if(trimgf($model->model_present_no) != "")
              {
                  if(!isset($presentvarenr[$model->present_id])) $presentvarenr[$model->present_id] = array();
                  $pvnr = trimgf(mb_strtolower($model->model_present_no));
                  if(!in_array($pvnr,$presentvarenr[$model->present_id]))
                  {
                    $presentvarenr[$model->present_id][] = $pvnr;
                  }
                  else 
                  {
                    echo "Model ".$modelid." has same varnr as another model in present: ".$model->present_id."<br>";
                    $duplicatevarenr++;
                  }
                  
              }
           
            }
        
          
        }
         if($varenr == "")  
          {
            $varenrpresentmissing[] = $modelid;
          }
      }
    
    }
    
    // Check presents without models
    $sql =  "SELECT * FROM `present` WHERE id NOT IN (SELECT present_id FROM present_model) && shop_id > 0 && shop_id NOT IN (590000,601,579)";  
    $presentListWOModels = Present::find_by_sql($sql);
  
  
  // SELECT * FROM `present` WHERE id NOT IN (SELECT present_id FROM shop_present) && shop_id > 0 && shop_id NOT IN (590000,601,579)
  // SELECT * FROM `shop_present` WHERE present_id NOT IN (SELECT id FROM present) && shop_id > 0 && shop_id NOT IN (590000,601,579)
    
    
    echo "<h1>Results</h1><br>";
    echo "Loaded ".countgf($modelmap)." unique models<br>";
    
    echo "Presents without models: ".countgf($presentListWOModels);
    if(count($presentListWOModels) > 0) echo "<br>SELECT * FROM `present` WHERE id NOT IN (SELECT present_id FROM present_model) && shop_id > 0 && shop_id NOT IN (590000,601,579)";
    
    echo "<br><br>Language check:</b><br>Models with multiple instances (model/lang): ".$multipleinstances."!!<br>";     
    echo "Models with one or more missing languages: ".$missinglangs."!!<br>"; 
    
    echo "<br><b>Varenr check</b><br>Models with missing varenr: ".$varenrmissing."<br>"; 
    echo "Models with varenr mismatch: ".$varenrmismatch."<br>"; 
    echo "Duplicate varenr: ".$duplicatevarenr."<br>"; 
    echo "Duplicate da varenr: ".countgf($duplicatevarenr_da)."<br>";
    echo "Duplicate no varenr: ".countgf($duplicatevarenr_no)."<br>";
    echo "Missing varenr: ".countgf($varenrpresentmissing);

    echo "MODEL IDS HAS NO VARENR<br>".implode(",",$missingvarenr)."<br><br>";
    echo "MODEL IDS MISMATCH VARENR<br>".implode(",",$mismatchvarenr)."<br><br>";
    echo "DUPLICATE DA VARENR: SELECT * FROM present_model WHERE present_id IN (".implode(",",$duplicatevarenr_da).");<br>";
    echo "DUPLICATE NO VARENR: SELECT * FROM present_model WHERE present_id IN (".implode(",",$duplicatevarenr_no).");<br>";
    echo "VARENR MISSING:  SELECT * FROM present_model WHERE model_id IN (".implode(",",$varenrpresentmissing).");<br>";
    
   
   }
   
   /**
    * orderwarn
    * Checks orders and if order data is present on the orders
    */
   
   public function orderwarn()
   {
      $token = isset($_GET["token"]) ? $_GET["token"] : "";
      if($token != "ds3fvXXtlk4G642D1s") exit();
      $alert = isset($_GET["alert"]) ? $_GET["alert"] : "";
      $alert = $alert == "1";
      
      ob_start();
      
      $errors = 0;
   
      // ORDER NR må ikke være dobbelt
      $doubleOrders = Order::find_by_sql("select count(id), order_no FROM `order` WHERE is_demo = 0 GROUP BY order_no HAVING COUNT(id) > 1");
      echo "Double orders: ".countgf($doubleOrders)."<br>";
      $errors += countgf($doubleOrders);
       
      // ORDER skal have en gyldig shop
      $invalidShopOrders = Order::find_by_sql("select id, order_no, shop_id FROM `order` WHERE shop_id NOT IN (SELECT id FROM shop) && id not in (1,7)");
      echo "Ordre med ugyldig shop: ".countgf($invalidShopOrders)."<br>";
      $errors += countgf($invalidShopOrders);
      
      // ORDER skal have companyid
      $invalidCompanyOrders = Order::find_by_sql("select id, order_no, company_id FROM `order` WHERE company_id NOT IN (SELECT id FROM company) && id not in (1,7);");
      echo "Ordre med ugyldigt company: ".countgf($invalidCompanyOrders)."<br>";
      $errors += countgf($invalidCompanyOrders);
       
      // ORDER skal have en shopuser der eksisterer på samme shop
      $invalidShopUserOrders = Order::find_by_sql("select id, order_no, shopuser_id FROM `order` WHERE shopuser_id NOT IN (SELECT id from shop_user WHERE company_id = `order`.company_id) && id not in (1,7);");
      echo "Ordre med ugyldig shopuser: ".countgf($invalidShopUserOrders)."<br>";
      $errors += countgf($invalidShopUserOrders);
        
      // ORDER der er giftcertificate skal have giftcertificate på samme shop
      //$invalidCertificateOrders = Order::find_by_sql("select id, order_no, user_username FROM `order` WHERE shop_is_gift_certificate = 1 && user_username NOT IN (SELECT certificate_no from gift_certificate WHERE company_id = `order`.company_id && shop_id = `order`.shop_id)");
      //echo "Ordre med ugyldig gift certificate: ".countgf($invalidCertificateOrders)."<br>";
      //$errors += countgf($invalidCertificateOrders);
       // DISABLED BECAUSE SOME CERTIFICATES ARE SET TO SHOP_ID 127 - INVESTIGATING!
       
      // ORDRE skal have present_id på samme shop
      $invalidPresentOrders = Order::find_by_sql("select id, order_no, present_id FROM `order` WHERE present_id NOT IN (SELECT id from present WHERE shop_id = `order`.shop_id) && id not in (1,7);");
      echo "Ordre med ugyldigt present_id: ".countgf($invalidPresentOrders)."<br>";
      $errors += countgf($invalidPresentOrders);
        
      // ORDER skal have present_model_present_no (varenr)
      $invalidPresentModelNoOrders = Order::find_by_sql("select id, order_no, present_id, present_model_present_no FROM `order` WHERE (present_model_present_no = '' or present_model_present_no IS NULL) && id not in (1,7)");
      echo "Ordre der mangler varenr: ".countgf($invalidPresentModelNoOrders)."<br>";
      $errors += countgf($invalidPresentModelNoOrders);
           
      // ORDER skal have present_model_id som har present_id på samme shop
      $invalidPresentModelOrders = Order::find_by_sql("select id, order_no, present_id, present_model_id FROM `order` WHERE present_model_id NOT IN (SELECT model_id from present_model WHERE present_id = `order`.present_id) && id not in (1,7)");
      echo "Ordre med ugyldigt present_model_id: ".countgf($invalidPresentModelOrders)."<br>";
      $errors += countgf($invalidPresentModelOrders);
        
      // ORDER present_model_name skal være angivet
      $invalidPresentNameOrders = Order::find_by_sql("select id, order_no, present_id, present_model_present_no FROM `order` WHERE (present_model_name = '' or present_model_name IS NULL) && id not in (1,7)");
      echo "Ordre uden present_model_name: ".countgf($invalidPresentNameOrders)."<br>";
      $errors += countgf($invalidPresentNameOrders);
       
      // ORDER ikke mere end 1 ordre på en shopuser
      $shopuserDoubleOrders = Order::find_by_sql("select count(id), order_no FROM `order` WHERE is_demo = 0 GROUP BY shopuser_id HAVING COUNT(id) > 1");
      echo "Ordre der findes mere end 1 gang på samme shopuser: ".countgf($shopuserDoubleOrders)."<br>";
      $errors += countgf($shopuserDoubleOrders);
       
      // shop skal være ok 
      $invalidShopShopUsers = Order::find_by_sql("select id, username, shop_id FROM `shop_user` WHERE shop_id NOT IN (SELECT id FROM shop)");
      echo "Shopusers med ugyldig shop forbindelse: ".countgf($invalidShopShopUsers)."<br>";
      $errors += countgf($invalidShopShopUsers);
      
      // companyid skal være ok
      $invalidCompanyShopUsers = Order::find_by_sql("select id, username, company_id FROM `shop_user` WHERE company_id NOT IN (SELECT id FROM company)");
      echo "Shopusers med ugyldig company: ".countgf($invalidCompanyShopUsers)."<br>";
      $errors += countgf($invalidCompanyShopUsers);
         
      // ikke samme brugernavn flere gange i samme shop
      $shopUserDoubleUsername = Order::find_by_sql("select count(id), username FROM `shop_user` where id not in (696199,696161) GROUP BY shop_id, username HAVING COUNT(id) > 1;");
      echo "Dobbelt brugernavn i samme shop: ".countgf($shopUserDoubleUsername)."<br>";
      $errors += countgf($shopUserDoubleUsername);
         
      echo "<br><b>Total antal fejl: ".$errors."</b>";  
      
      $content = ob_get_contents();
      ob_end_clean(); 
      
      if($alert)
      {
        if($errors > 0)
	      {
    		  header("HTTP/1.0 500 Service down");
    		  echo $content;
    		  exit();
        }
        else echo "OK";
      }
      else
      {
        echo $content;
      }
   }
   
   
}