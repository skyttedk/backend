<?php

Class syncservController Extends baseController 
{

  public function Index()
  {
    $this->outputServiceError(2,"Invalid service endpoint");
  }
 
  
   /******************************************************
    *****************   UPDATE DEBITOR  ***************
    ******************************************************/
  
  public function updatedebitor()
  {
    $this->checkShopToken(false);
    $content = "UPDATE DEBITOR SERVICE CALLED<br><br>GET:<br><pre>".print_r($_GET,true)."</pre><br><br>POST:<br><pre>".print_r($_POST,true)."</pre><br><br>RAW:<br>".file_get_contents("php://input");
    $this->mailProblem("UPDATE DEBITOR CALL",$content);
    
    $bsnr = isset($_POST["bsnr"]) ? trimgf($_POST["bsnr"]) : "";
    $debitornr = isset($_POST["debitornr"]) ? trimgf($_POST["debitornr"]) : "";
    
     if($bsnr == "")
     {
        return outputServiceError(10,"No BS no",true);
     }
     
     if($debitornr == "" || intval($debitornr) == 0)
     {
         return outputServiceError(11,"Debitor no not set",true);
     }
    
    // Find order no
    $order = Companyorder::find('all', array('conditions' => array('order_no=?', $bsnr)));
    
    if(count($order) != 1)
    {
         return outputServiceError(12,"BS no ".$bsnr." not found",true);
    }
    
    $order = $order[0];
    
    // Update 
    $order->navsync_error .= "\r\nNAV-UPDATEDEBITOR:".$order->navsync_response.";".$debitornr.";".date("d-m-Y H:i:s");
    $order->navsync_response = $debitornr;
    $order->save();
    system::connection()->commit();
    
    
    echo json_encode(array("status" => 1, "error" => ""));
  }
    
    
  
   /******************************************************
    *****************  WEBSERVICE HELPERS  ***************
    ******************************************************/
    
    private function mailProblem($subject,$content)
  {
   
    $body ="";
 
    $modtager = "sc@interactive.dk";
    $message = $content;
           
  	$headers = "From: noreply@julegavekortet.dk <noreply@julegavekortet.dk>" . "\r\n";
    $headers .= "Reply-To:  <noreply@julegavekortet.dk>\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8";

  	$result = mail($modtager, $subject, $message, $headers);
   
  }
  
    // CHECK / VALIDATION
     
    private $shop;
    private $shopCode;
    private $shopID;
     
    private function checkShopToken($requireShop)
    {
    
      header('Access-Control-Allow-Origin: *'); 
      
      // General token
      $generalToken = "P8yGd2ApbHgupn5YJ4wEbQy9zGuMT4RkpgDL8mvh";
    
      
      // Get token
      if(!$this->hasInput("access-token")) return $this->outputServiceError(5,'No access-token specified'); 
      $token = $this->getInputString("access-token");
    
      if($token != $generalToken) return $this->outputServiceError(7,'Invalid access-token');
      return true;
        
    }
    
    // INPUT
    
    private $inputVars = null;
    
    private function hasInput($key)
    {
      $this->loadInput();
      return isset($this->inputVars[$key]);
    }
    
    private function getInput($key)
    {
      if($this->hasInput($key)) return $this->inputVars[$key];
      else return "";
    }
    
    private function getInputString($key)
    {
      return trimgf($this->getInput($key));
    }
    
    private function getInputInt($key)
    {
      return intval($this->getInput($key));
    }
    
    private function loadInput()
    {
      if($this->inputVars != null) return;
      
      // Load JSON
      if($this->isJSONRequest())
      {
          $input = file_get_contents("php://input");
          $jsonData = json_decode($input,true);
          if($jsonData != null && is_array($jsonData) && countgf($jsonData) > 0) $this->inputVars = $jsonData;
          else $this->outputServiceError(4,'No JSON data input');
      }           
      
      // Load post
      else
      {
        if(is_array($_POST) && countgf($_POST) > 0) $this->inputVars = $_POST;
        else $this->outputServiceError(3,'No POST data input');
      }
                         
    }
    
    private function isJSONRequest()
    {
        $contentType = isset($_SERVER["CONTENT_TYPE"]) ? strtolower($_SERVER["CONTENT_TYPE"]) : "";
        return ($contentType == "application/json" || $contentType == "text/json");
    }
    
    // OUTPUT
    
    private function outputServiceError($errorCode,$errorMessage,$terminate=false)
    {
      echo json_encode(array("status" => $errorCode,"error" => $errorMessage));
      exit();
    }
    
    private function outputServiceSuccess($data)
    {
      if(!is_array($data)) $data = array();
      echo json_encode(array_merge(array("status" => 1),$data));
    }
  
  
  /**
   * Looks for models with different model_id for same original model.
   * Generates sql to update models so all models have 5 languages with same model_id
   **/
  public function presfix()
  {
  
      // Excludeshops
      $excludeShops = array(266,577,578,579,580,0,576);
  
      // Load presents and make id
      $presentlist = Present::find_by_sql("select * from present WHERE shop_id NOT IN (".implode(",",$excludeShops).")");
      $presentmap = array();
      foreach($presentlist as $present) $presentmap[$present->id] = $present;
      echo "Loaded ".countgf($presentlist)." PRESENTS<br>";
      
      // Load present models
      $presentmodels = Presentmodel::find_by_sql("select * from present_model WHERE model_id > 0 && present_id IN (select id from present where shop_id NOT IN (".implode(",",$excludeShops).")) ORDER BY present_id asc, model_id ASC, language_id ASC");
      echo "LOADED ".countgf($presentmodels)." MODELS<br><br>";
      
      // [present_id][org_model_id][lang_id][]
      $modelidpresent = array();
      $modelmap = array();
      
      foreach($presentmodels as $model)
      {
          // Present map
          if(isset($modelidpresent[$model->model_id]))
          {
            if($modelidpresent[$model->model_id] != $model->present_id)
            {
              echo "ERROR IN PRESENT ID (".$model->model_id." / ".$model->present_id.")<br>";
            }
          }
          else 
          {
            $modelidpresent[$model->model_id] = $model->present_id;  
          }
          
          // Model map
          if(!isset($modelmap[$model->present_id])) $modelmap[$model->present_id] = array();
          if(!isset($modelmap[$model->present_id][$model->original_model_id])) $modelmap[$model->present_id][$model->original_model_id] = array();
          if(!isset($modelmap[$model->present_id][$model->original_model_id][$model->language_id])) $modelmap[$model->present_id][$model->original_model_id][$model->language_id] = array();                                                                                  
          $modelmap[$model->present_id][$model->original_model_id][$model->language_id][] = $model;
          
      }
      
      $updateModelID = "";
      $updateModelIDBack = 0;
    
      // Process models
      foreach($modelmap as $presentid => $orgmodels)
      {
        foreach($orgmodels as $orgmodelid => $langs)
        {
          if(count($langs) != 5)
          {
              echo "ERR: MISSING LANGS FOR MODEL ".$presentid." / ".$orgmodelid." (".countgf($langs).")<br>";
          }
          
          $model_ids = array();
          $danishmodel = 0;
          
          foreach($langs as $langid => $objs)
          {
            if(count($objs) > 1) echo "ERR: MULTIPLE OBJECTS FOR SAME ".$presentid." / ".$orgmodelid." / ".$langid." (".countgf($objs).")<br>";
            else if($langid == 1) 
            {
              if($danishmodel > 0) echo "DANISH MODEL ALREADY SET<br>";
              $danishmodel = $objs[0]->model_id;
            }
          }
          
          if($danishmodel == 0) echo "COULD NOT FIND DK MODEL: ".$presentid." / ".$orgmodelid."<br>";
          else
          {
              foreach($langs as $langid => $objs)
              {
                  if($objs[0]->model_id != $danishmodel)
                  {
                    echo " - UPDATE MODEL LANG ".$presentid." / ".$orgmodelid." / ".$langid." [dk: ".$danishmodel.", current: ".$objs[0]->model_id."] <br>";
                    
                    $updateModelID .= "UPDATE present_model SET model_id = ".$danishmodel." WHERE id = ".$objs[0]->id." && model_id = ".$objs[0]->model_id." && present_id = ".$presentid.";\r\n";
                    $updateModelIDBack .= "UPDATE present_model SET model_id = ".$objs[0]->model_id." WHERE id = ".$objs[0]->id." && model_id = ".$danishmodel." && present_id = ".$presentid.";\r\n";
                    
                  }
              }
          }
          
        }
      }
      
      
      echo "SQL TO SET CORRECT MODEL IDS<br><textarea style=\"height: 400px; width: 80%;\">".$updateModelID."</textarea><br>";
      echo "SQL TO SET CORRECT MODEL REVERT<br><textarea style=\"height: 400px; width: 80%;\">".$updateModelIDBack."</textarea>";
      
  }
  

   public function presint()
   {
  
      // Load all presents and put into a map
      $presentlist = Present::find_by_sql("select * from present WHERE shop_id NOT IN (266,577,578,579,580)");
      $presentmap = array();
      foreach($presentlist as $present) $presentmap[$present->id] = $present;
      
      // Pull list of models
      $modellist = Presentmodel::find_by_sql("SELECT * FROM `present_model` WHERE id NOT IN (3113,1733,1735,1737,1739,2019,2021,2023,2025,2027,2020,3558,3559,3560,3561,3562,3563,3564,3565,3566,3927,3928,3929,3930,3931,3947,3948,3949,3950,3951,3952,3953,3954,3955,3956,3957,3958,3959,3960,3961,3987,3992,4282,4317,4318,4319,4320,4321,4477,4478,4479,4480,4481,4927,4928,4929,4930,4931,4932,4933,4934,4935,4936,5427,5428,5429,5430,5431,5432,5437,5433,5438,5434,5439,5435,5440,5436,5441,5637,5638,5639,5640,5641,6427,6428,6429,6430,6431,6437,6438,6439,6440,6441,6442,6443,6444,6445,6446,3397,3402,3398,3403,3399,3404,3400,3405,3401,3406,2155,2214,2239,3025,3039,3049,2156,673,2215,3255,2240,3026,3040,2157,622,639,674,2216,3246,3259,2241,3374,2158,624,640,675,2217,3247,3263,2242,3290,3375,2159,626,641,676,2218,3248,2243,3267,3291,21,22,23,24,25,31,32,33,34,35,36,37,38,39,40,296,297,298,299,300,301,302,303,304,305,306,307,308,309,310,854,855,856,857,858,994,995,996,997,998,1074,1075,1076,1077,1078,2264,2266,2268,2270,2272,2265,2267,2269,2271,2273,2274,2275,2276,2277,2278,2279,2280,2281,2282,2283,2284,2286,2288,2290,2292,2285,2287,2289,2291,2293,2294,2295,2296,2297,2298,2299,2300,2301,2302,2303,2559,2562,2565,2568,2571,2560,2563,2566,2569,2572,2561,2564,2567,2570,2573,3218,3222,3226,3230,3234,3219,3223,3227,3231,3235,3220,3224,3228,3232,3236,3221,3225,3229,3233,3237,3238,3242,3239,3243,3240,3244,3241,3245,3249,3253,3257,3261,3265,3250,3254,3258,3262,3266,3251,3252,3256,3260,3264,3268,3269,3270,3271,3272,3273,3284,3286,3288,3292,3285,3287,3289,3318,3323,3328,3333,3338,3319,3324,3329,3334,3339,3320,3325,3330,3335,3340,3321,3326,3331,3336,3341,3322,3327,3332,3337,3342,3343,3348,3353,3358,3363,3344,3349,3354,3359,3364,3345,3350,3355,3360,3365,3346,3351,3356,3361,3366,3347,3352,3357,3362,3367,3368,3370,3372,3376,3369,3371,3373,3387,3389,3391,3393,3395,3388,3390,3392,3394,3396,3422,3423,3424,3425,3426,3427,3428,3429,3430,3431,3432,3433,3434,3435,3436,3437,3442,3447,3438,3443,3448,3439,3444,3449,3440,3445,3450,3441,3446,3451,3452,3453,3454,3455,3456,3457,3458,3459,3460,3461,3462,3463,3464,3465,3466,3482,3483,3484,3485,3486,3487,3488,3489,3490,3491,3492,3493,3494,3495,3496,3497,3498,3499,3500,3501,3502,3503,3504,3505,3506,3507,3508,3509,3510,3511,3512,3513,3514,3515,3516,3877,3878,3879,3880,3881,3882,3883,3884,3885,3886,3887,3888,3889,3890,3891,3932,3933,3934,3935,3936,3937,3938,3939,3940,3941,3962,3963,3964,3965,3966,3967,3968,3969,3970,3971,3972,3973,3974,3975,3976,3977,3978,3988,3979,3989,3980,3990,3981,3991,3982,3983,3993,3984,3994,3985,3995,3986,3996,3997,3998,3999,4000,4001,4002,4003,4004,4005,4006,4007,4008,4009,4010,4011,4012,4013,4014,4015,4016,4017,4018,4019,4020,4021,4062,4063,4064,4065,4066,4067,4068,4069,4070,4071,4072,4073,4074,4075,4076,4077,4078,4079,4080,4081,4202,4203,4204,4205,4206,4207,4208,4209,4210,4211,4212,4213,4214,4215,4216,4217,4218,4219,4220,4221,4222,4223,4224,4225,4226,4227,4228,4229,4230,4231,4232,4233,4234,4235,4236,4237,4238,4239,4240,4241,4242,4243,4244,4245,4246,4247,4248,4249,4250,4251,4283,4284,4285,4286,4572,4573,4574,4575,4576,4577,4578,4579,4580,4581,4582,4583,4584,4585,4586,4587,4588,4589,4590,4591,4592,4593,4594,4595,4596,4802,4803,4804,4805,4806,5702,5703,5704,5705,5706,5707,5708,5709,5710,5711,5712,5713,5714,5715,5716,5717,5718,5719,5720,5721) ORDER BY model_id ASC, language_id ASC");
      echo "FOUND ".countgf($presentmap)."PRESENTS AND ".countgf($modellist)." MODELS<br>";
      
      // Status counters
      $modelmap = array();
      $presentmodelmap = array();
      
      $unprocessed = array();
      $multipleinstances = array();
      $unknownpresents = array();
      
      
      // Load models into map
      foreach($modellist as $model)
      {
      
        $modelid = $model->model_id;
        $lang = $model->language_id;
        $presentid = $model->present_id;
        $id = $model->id;
        
        if($modelid > 0 && $presentid > 0)
        {
  
          if(isset($presentmap[$presentid])   )
          {
             if(!isset($presentmodelmap[$presentid])) $presentmodelmap[$presentid] = array();
             if(!isset($presentmodelmap[$presentid][$modelid])) $presentmodelmap[$presentid][$modelid] = array();
             if(!isset($presentmodelmap[$presentid][$modelid][$lang])) $presentmodelmap[$presentid][$modelid][$lang] = $model; 
             
             if(!isset($modelmap[$modelid])) $modelmap[$modelid] = array();
             if(!isset($modelmap[$modelid][$lang])) $modelmap[$modelid][$lang] = $model;
             else {
              echo "Multiple instances of model id ".$modelid.", language ".$lang." [present: ".$presentid."]<br>";
                $multipleinstances[] = $id;
                $multipleinstances[] = $modelmap[$modelid][$lang]->id;
             } 
          }
          else
          {
            echo "Unknown present id: ".$presentid."<br>";
            $unknownpresents[] = $id;
          }
  
          
        }
        else 
        {
            $unprocessed[] = $id;
        }
      }
      
      echo countgf($multipleinstances)." MODELS ARE MORE THAN ONCE:<br>
      <textarea style=\"width: 80%; height: 60px;\">SELECT * FROM present_model WHERE id IN (".implode(",",$multipleinstances).") ORDER BY model_id, language_id;</textarea><br><br>";
      
      echo countgf($unknownpresents)." WITH UNKNOWN PRESENTS:<br>
      <textarea style=\"width: 80%; height: 60px;\">SELECT * FROM present_model WHERE id IN (".implode(",",$unknownpresents).") ORDER BY model_id, language_id;</textarea><br><br>";
      
      
       echo countgf($unprocessed)." WITH UNPROCESSED:<br>
      <textarea style=\"width: 80%; height: 60px;\">SELECT * FROM present_model WHERE id IN (".implode(",",$unprocessed).") ORDER BY model_id, language_id;</textarea><br><br>";
      
      
      $missinglangs = array();
      $missinglangspresent = array();
      
    foreach($modelmap as $modelid => $map)
    {
      $missing = false;
      if(count($map) != 5)
      {
        echo "Model ".$modelid." only has ".countgf($map)." languages<br>";
        $missinglangs[] = $modelid;
        $missing = true;
      }
      
      foreach($map as $lang => $model)
      {
        if($missing && !in_array($model->present_id,$missinglangspresent))
        {
          $missinglangspresent[] = $model->present_id;
        }
      }
      
           /*
      $varenr = "";
      foreach($map as $lang => $model)
      {
         if($varenr == "") $varenr = $model->model_present_no;
         
         if(trimgf($model->model_present_no) == "")
         {
          echo "Model ".$modelid." / lang ".$lang." has no varenr<br>";
          $varenrmissing++;
         }
         if($varenr != "" && $varenr != $model->model_present_no)
         {
          echo "Model ".$modelid." / lang ".$lang." has varenr mismatch (org: ".$varenr."): ".$model->model_present_no."<br>";
          $varenrmismatch++;
         }
      
        if($model->language_id == 1)
        {
        
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
           */
    
    }
      
      
        echo "<br>".countgf($missinglangs)." MODELS WITH MISSING LANGUAGES:<br>
      <textarea style=\"width: 80%; height: 60px;\">SELECT * FROM present_model WHERE model_id IN (".implode(",",$missinglangs).") ORDER BY present_id, model_id, language_id;</textarea><br><br>";
      
        echo "<br>".countgf($missinglangspresent)." PRESENTS WITH MISSING MODEL LANGUAGES:<br>
      <textarea style=\"width: 80%; height: 60px;\">SELECT * FROM present_model WHERE present_id IN (".implode(",",$missinglangspresent).") ORDER BY present_id, model_id, language_id;</textarea><br><br>";
      
      
      /*
   
      
      $unprocessed = 0;
      $modelmap = array();
      $multipleinstances = 0;
      $missinglangs = 0;       
      $varenrmissing = 0;
      $varenrmismatch = 0;
      $presentvarenr = array();
      $missingmedia = 0;
      $duplicatevarenr = 0;
      
      
      echo "Loading models:<br><br>";
      
      foreach($modellist as $model)
      {
      
        $modelid = $model->model_id;
        $lang = $model->language_id;
        if($modelid > 0)
        {
          
          if(!isset($modelmap[$modelid])) $modelmap[$modelid] = array();
          if(!isset($modelmap[$modelid][$lang])) $modelmap[$modelid][$lang] = $model; 
          else {
            echo "Multiple instances of model id ".$modelid.", language ".$lang."<br>";
            $multipleinstances++;
          }
        }
        else $unprocessed++;
      }
      
    
   
    foreach($modelmap as $modelid => $map)
    {
    
      if(count($map) != 5)
      {
        echo "Model ".$modelid." only has ".countgf($map)." languages<br>";
        $missinglangs++;
      }
      
      $varenr = "";
      foreach($map as $lang => $model)
      {
         if($varenr == "") $varenr = $model->model_present_no;
         
         if(trimgf($model->model_present_no) == "")
         {
          echo "Model ".$modelid." / lang ".$lang." has no varenr<br>";
          $varenrmissing++;
         }
         if($varenr != "" && $varenr != $model->model_present_no)
         {
          echo "Model ".$modelid." / lang ".$lang." has varenr mismatch (org: ".$varenr."): ".$model->model_present_no."<br>";
          $varenrmismatch++;
         }
      
        if($model->language_id == 1)
        {
        
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
      
    
    }
    
    echo "<h1>Results</h1><br>";
     echo "Loaded ".countgf($modelmap)." unique models<br>";
       echo "Models not loaded (non positive model id): ".$unprocessed."<br>";
      echo "Models with multiple instances (model/lang): ".$multipleinstances."<br>";
     
     echo "Models with one or more missing languages: ".$missinglangs."<br>"; 
     echo "Models with missing varenr: ".$varenrmissing."<br>"; 
     echo "Models with varenr mismatch: ".$varenrmismatch."<br>"; 
     echo "Duplicate varenr: ".$duplicatevarenr."<br>"; 
     */
   }
   
   
   
   
   
     
}
