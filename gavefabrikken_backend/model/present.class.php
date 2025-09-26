<?php
// Model Present
// Date created  Mon, 16 Jan 2017 15:29:17 +0100
// Created by Bitworks
//***************************************************************
//   (PRI) id                            int(11)             NO
//   (   ) name                          varchar(100)        NO
//   (   ) internal_name                 varchar(100)        NO
//   (   ) present_no                    varchar(250)        NO
//   (MUL) copy_of                       int(11)             YES
//   (   ) shop_id                       int(11)             YES
//   (   ) logo                          varchar(1024)       YES
//   (   ) price                         decimal(15,2)       YES
//   (   ) price_group                   decimal(15,2)       YES
//   (   ) indicative_price              decimal(15,2)       YES
//   (   ) is_grouped                    tinyint(1)          YES
//   (   ) present_list                  varchar(1024)       YES
//   (   ) variant_list                  text                YES
//   (   ) vendor                        varchar(100)        YES
//   (   ) created_datetime              datetime            NO
//   (MUL) modified_datetime             datetime            NO
//   (   ) active                        tinyint(1)          YES
//   (MUL) deleted                       tinyint(1)          YES
//   (   ) limit                         int(11)             YES
//   (   ) present_substitute            int(11)             YES
//   (   ) alias                         int(11)             YES
//***************************************************************

class Present extends BaseModel {
  static $table_name = "present";
  static $primary_key = "id";
  static $has_many = array(
      array('present_media', 'class_name' => 'PresentMedia'),
      array('descriptions', 'class_name' => 'PresentDescription'),
      array('orders', 'class_name' => 'Order'),
      array('models', 'class_name' => 'PresentModel'),
      array('shop_presents', 'class_name' => 'ShopPresent'),
    );

   static $calculated_attributes = array("used_on_shop","has_orders","has_variants","is_variant","media");

   public function media() {

        try {
            //$media = PresentMedia::find($this->id);
            //return($media);
        } catch (Exception $ex) {
            //die($ex->getMessage());
        }

   }

   public function is_variant() {
     return(!$this->copy_of==0);
   }

   function used_on_shop() {
     return(count($this->shop_presents)>0);
   }

   function has_orders() {
     return(count($this->orders)>0);
     return false;
   }

   function has_models() {
     return(count($this->models)>0);
     return false;
   }

   public function has_variants() {
       $variants = present::all(array('conditions' => array('deleted' => 0,'shop_id' => 0,'copy_of' => $this->id)));
       if(count($variants)>0)
         return(true);
       else
         return(false);
   }

  static $before_create = array('onBeforeCreate');
  static $after_create = array('onAfterCreate');
  static $before_update = array('onBeforeUpdate');
  static $after_update = array('onAfterUpdate');
  static $after_destroy = array('onAfterDestroy');
  static $before_destroy =  array('onBeforeDestroy') ;

  function onBeforeCreate() {
    $this->created_datetime = date('d-m-Y H:i:s');
    $this->modified_datetime = date('d-m-Y H:i:s');
    $this->validateFields();
  }

  function onAfterCreate() {
  }

  function onBeforeUpdate() {
    $this->modified_datetime = date('d-m-Y H:i:s');
    $this->validateFields();
  }

  function onAfterUpdate() {
  }
  function onBeforeDestroy() {

    //if ($this->used_on_shop())
    //  throw new exception('Gaven kan ikke slettes da den er tilknyttet p� en shop');

    //if ($this->has_orders())
    //  throw new exception('Gave kan ikke slettes, da der findes ordre p� denne gave.');

   }

  function onAfterDestroy() {
     PresentMedia::table()->delete(array('present_id' => $this->id));
     PresentDescription::table()->delete(array('present_id' => $this->id));
     PresentModel::table()->delete(array('present_id' => $this->id));
     PresentReservation::table()->delete(array('present_id' => $this->id));
  }

  function validateFields() {
    //testRequired($this, 'name');
    //testRequired($this, 'internal_name');
    //testRequired($this, 'present_no');

    testRequired($this, 'created_datetime');
    testRequired($this, 'modified_datetime');

    testMaxLength($this, 'name', 100);
    testMaxLength($this, 'internal_name', 100);
    testMaxLength($this, 'present_no', 250);
    testMaxLength($this,'logo',1024);

    //Validate Present List
    //Laves not om til json
    $presentsIDs = preg_split('/\r\n|\r|\n/', $this->present_list);
    // if($this->is_grouped)
    //  {
        foreach ($presentsIDs as $presentID)
        {
           //if($presentID == $this->id)
           //  throw new Exception("En gave kan ikke indeholde sig selv");

           // Check at vare ikke er markeret som slettet
           //$present = Present::find($presentID);
           //if($present->deleted)
           //  throw new Exception("Gave $presentID er markeret som slettet");

            // Check at gave er aktiv
            //if(!$present->active)
            //  throw new Exception("Gave $presentID er ikke aktive");

            // Check at varenr i present_list ikke selv er grupperet
            //if($present->is_grouped)
            //  throw new Exception("Gave $presentID er en grupperet vare");
        }
  //  } else {
        //$this->present_list = '';   s� nulsltil den
    //  }
    // N� fandme ikke redigere Navision varenr. hvis der allerede ligger ordre p� en

    if($this->copy_of){
      Present::find($this->copy_of);
      //check at copy_odfikke er deleted
    }

    if($this->shop_id){
      Shop::find(abs($this->shop_id));
    }

    $this->name = trimgf($this->name);
    $this->internal_name = trimgf($this->internal_name);
    $this->present_no = trimgf($this->present_no);

    $this->validatePresentNoFormat();
  }


 function validatePresentNoFormat(){
    if($this->present_no !== "")
    {
        $present_list = preg_split('/,/', trimgf($this->present_no));
        foreach ($present_list as $p)
        {
           if(trimgf($p)=="")
             throw new exception('Ugyldigt gaveliste format. ');
        }
        }
 }


  //used only for testing!!!
  private function validatePresentList($presentList) {
    if (trimgf($presentList) != "")
        {
        }
  }

  static public function deleteAll() {
   // foreach (Present::all() as $present) {
   //   $present->delete();
   // }
  }
//---------------------------------------------------------------------------------------
// Static CRUD Methods
//---------------------------------------------------------------------------------------
  static public function addPresentToShop($id, $shopId, $pchild=0) {

      $presentOriginal = Present::find($id);


      $presentVariant = new Present($presentOriginal->attributes);
      $presentVariant->copy_of = $presentOriginal->id;
      $presentVariant->shop_id =  abs($shopId);
      $presentVariant->pchild = $pchild;
      $presentVariant->id = null;
      $presentVariant->save();

     // Present Descriptions
     foreach($presentOriginal->descriptions as $description)  {
        $presentdescription = new PresentDescription();
        $presentdescription->present_id = $presentVariant->id;
        $presentdescription->language_id = $description->language_id;
        $presentdescription->caption = $description->caption;
        $presentdescription->short_description = $description->short_description;
        $presentdescription->long_description = $description->long_description;
        $presentdescription->caption_presentation = $description->caption_presentation;
        $presentdescription->save();
     }

     //Present Media
     foreach($presentOriginal->present_media as $medium)  {
        $presentmedia = new PresentMedia();
        $presentmedia->present_id = $presentVariant->id;
        $presentmedia->media_path = $medium->media_path;
        $presentmedia->index = $medium->index;
        $presentmedia->save();
     }

    $prevModelList = $presentOriginal->models;
    $orgModelList =  Presentmodel::find_by_sql("SELECT * FROM present_model WHERE present_id = ".$presentOriginal->id." ORDER BY model_id ASC, language_id ASC");

     //Present Models
     $lastPrensentNr = "";
     foreach($orgModelList as $model)  {
        if($lastPrensentNr != $model->model_id){
            $max = PresentModel::find_by_sql('SELECT MAX(model_id) AS modelid FROM present_model ');
            $max = $max[0]->modelid +1;
            $lastPrensentNr = $model->model_id;
        }
        $newmodel = new PresentModel();
        $newmodel->model_id         = $max;
        $newmodel->original_model_id = $model->model_id;
        $newmodel->present_id       = $presentVariant->id;
        $newmodel->language_id      = $model->language_id;
        $newmodel->model_present_no = $model->model_present_no;
        $newmodel->model_name       = $model->model_name;
        $newmodel->model_no         = $model->model_no;
        $newmodel->media_path       = $model->media_path;
        $newmodel->active           = $model->active;
        $newmodel->save();

     }


    //Fix models
    /*
    $searchFor   = 'variantId';
    $replaceWith = 'variantIdOriginal';
    $new_variant_list = str_replace ($searchFor , $replaceWith , $presentVariant->variant_list);
    Present::updateVariantList($presentVariant->id,$new_variant_list);
    // inset into shop_pressent
     */
     if ($presentVariant->attributes["id"] < 1){
        throw new exception('Gave kan ikke tilf�jes shoppen da den allerede er tilf�jet');
    }
    if($pchild == 0) {
        $shopPresent = new ShopPresent();
        $shopPresent->shop_id = $shopId;
        $shopPresent->present_id = $presentVariant->attributes["id"];
        $shopPresent->index_ = -1;
        $shopPresent->save();
    }
     return ($presentVariant);

  }





  static public function createPresentVariant($id) {

      $presentOriginal = Present::find($id);
      if($presentOriginal->copy_of<>0)
        throw new exception("Der kan ikke oprettes en variant af en variant!");

      $presentVariant = new Present($presentOriginal->attributes);
      // denne funktion er lavet om s� der bliver oprettet en �gte copy, derfor er nedenst�ende linje udkommenteret
      //$presentVariant->copy_of = $presentOriginal->id;
      $presentVariant->id = null;
      $presentVariant->save();

     // Present Descriptions
     foreach($presentOriginal->descriptions as $description)  {
        $presentdescription = new PresentDescription();
        $presentdescription->present_id = $presentVariant->id;
        $presentdescription->language_id = $description->language_id;
        $presentdescription->caption = $description->caption;
        $presentdescription->short_description = $description->short_description;
        $presentdescription->long_description = $description->long_description;
        $presentdescription->caption_presentation = $description->caption_presentation;
        $presentdescription->save();
     }

     //Present Media
     foreach($presentOriginal->present_media as $medium)  {
        $presentmedia = new PresentMedia();
        $presentmedia->present_id = $presentVariant->id;
        $presentmedia->media_path = $medium->media_path;
        $presentmedia->index = $medium->index;
        $presentmedia->save();
     }

    
     $prevModelList = $presentOriginal->models;
    $orgModelList =  Presentmodel::find_by_sql("SELECT * FROM present_model WHERE present_id = ".$presentOriginal->id." ORDER BY model_id ASC, language_id ASC");

                                           
     //Present Models
     $lastPrensentNr = "";
     foreach($orgModelList as $model)  {
        if($lastPrensentNr != $model->model_id){
            $max = PresentModel::find_by_sql('SELECT MAX(model_id) AS modelid FROM present_model ');
            $max = $max[0]->modelid +1;
            $lastPrensentNr = $model->model_id;
        }      
        $newmodel = new PresentModel();
        $newmodel->model_id         = $max;
        $newmodel->original_model_id = $model->model_id;
        $newmodel->present_id       = $presentVariant->id;
        $newmodel->language_id      = $model->language_id;
        $newmodel->model_present_no = $model->model_present_no;
        $newmodel->model_name       = $model->model_name;
        $newmodel->model_no         = $model->model_no;
        $newmodel->media_path       = $model->media_path;
        $newmodel->active           = $model->active;
        $newmodel->save();

     }
                                                                              

    //Fix models
    /*
    $searchFor   = 'variantId';
    $replaceWith = 'variantIdOriginal';
    $new_variant_list = str_replace ($searchFor , $replaceWith , $presentVariant->variant_list);
    Present::updateVariantList($presentVariant->id,$new_variant_list);
    */
    return ($presentVariant);

  }

  static public function createShopVariant($data) {

    $presentData =(array)json_decode($data['present']);
    $presentData['copy_of'] = $presentData['id'];

    if(!isset($presentData['shop_id']))
      throw new Exception("Feltet shop_id skal s�ttes til id p� den shop varianten skal tilknyttes!");

    unset($presentData['id']);
    $data['present']  = json_encode($presentData);
    $present =Present::createPresent($data);

    //Fix models
    $searchFor   = 'variantId';
    $replaceWith = 'variantIdOriginal';
    $new_variant_list = str_replace ( $searchFor , $replaceWith , $present->variant_list);
    Present::updateVariantList($present->id,$new_variant_list);


    $shopPresentOld = ShopPresent::find_by_shop_id_and_present_id($present->shop_id,$present->copy_of);

    //Tilf�j gave til de valgte gaver
    $shopPresent = new ShopPresent();
    $shopPresent->shop_id =    $present->shop_id;
    $shopPresent->present_id = $present->id;
    $shopPresent->properties = $shopPresentOld->properties;
    $shopPresent->save();


    $shopPresentOld->delete();

    return ($present);
  }


  //Hj�lpe funktion til at oprette model_id'er og inds�tte dem i variant_list json
   static public function updateVariantList($presentId,$variant_list) {
     $result ="";
     $resultArray = [];
     $variantlist = json_decode($variant_list);
     foreach($variantlist as $variant) {
       $variantno = '';
       $variantId = '';
       foreach($variant->feltData as $feltdata) {
            if(isset($feltdata->variantNr)) {
              $variantno = $feltdata->variantNr;
            }
            if(isset($feltdata->variantId)) {
              $variantId = $feltdata->variantId;
            }
            if(!$variantno=="")
              $resultArray[$variantno] = $variantId;
        }
     }

    $update = false;
    //create new model_id
    $modelId = PresentModel::find_by_sql('SELECT MAX(model_id) AS modelid FROM present_model ');
    $modelId = $modelId[0]->modelid +1;
    foreach($resultArray as $key => $value) {
    if($value=="") {
      $update=true;
      $searchFor   = '{"variantNr":"'.$key.'"}';
      $replaceWith = '{"variantId":"'.$modelId.'"},{"variantNr":"'.$key.'"}';
      $variant_list = str_replace ( $searchFor , $replaceWith , $variant_list);
      $modelId = $modelId+1;
    } else {

         }
     }
    Present::updateModels($presentId,$variant_list);
    return($result);
  }


  static public function createPresent($data) {

     //Logo
     $logoData = json_decode($data['logo']);

     //Present
     $presentData =(array)json_decode($data['present']);
     $presentData['variant_list'] =  $data['variant'];
     $presentData['internal_name'] = $presentData['name'];
      $presentData['pt_layout'] = 5;
     if($logoData[0]->logo)
       $presentData['logo'] = $logoData[0]->logo;

     $present = new Present($presentData);
     $present->save();

     // Present Descriptions
     $descriptions = json_decode($data['descriptions']);
     foreach($descriptions as $description)  {
        $presentdescription = new PresentDescription();
        $presentdescription->present_id = $present->id;
        $presentdescription->language_id = $description->language_id;
        $presentdescription->caption = $description->caption;
        $presentdescription->short_description = $description->short_description;
        $presentdescription->long_description = $description->long_description;
        $presentdescription->caption_presentation = $description->caption_presentation;

        $presentdescription->save();
     }

     //Present Media
     $mediaData = json_decode($data['media']);
     foreach($mediaData as $medium)  {
        $presentmedia = new PresentMedia();
        $presentmedia->present_id = $present->id;
        $presentmedia->media_path = $medium->media_path;
        $presentmedia->index = $medium->index;
        $presentmedia->save();
     }

     //Present Models
     Present::createModelFromVariantList($present);

    return ($present);
  }

  static public function readPresent($id) {
    $present = Present::find($id);
    return ($present);
  }


  static public function updatePresent($data) {

     //Logo
     $logoData = json_decode($data['logo']);



     //Present
     $presentData =(array)json_decode($data['present']);
     $presentData['variant_list'] =  $data['variant'];
     $presentData['internal_name'] = $presentData['name'];

     if($logoData[0]->logo){
       $presentData['logo'] = $logoData[0]->logo;
       $presentData['logo_size'] = $logoData[0]->logo_size;
     }
     $presentData["moms"] =  $data['moms'];
     

     $present = Present::find($presentData['id']);
     $present->update_attributes((array)$presentData);
     $present->save();

     //Descriptions
     $descriptionData =  json_decode($data['descriptions']);
     foreach($descriptionData as $description)  {
        $presentdescription = PresentDescription::find($description->id);
        $presentdescription->update_attributes((array)$description);
        $presentdescription->save();
     }







     //Present Media  + der kan v�re tilf�jet eller �ndret.-.. med det er bare billeder, s� slet og opret dem igen
     PresentMedia::table()->delete(array('present_id' => $present->id));
     $mediaData = json_decode($data['media']);
     foreach($mediaData as $medium)  {
        $presentmedia = new PresentMedia();
        $presentmedia->present_id = $present->id;
        $presentmedia->media_path = $medium->media_path;
        $presentmedia->index = $medium->index;
        $presentmedia->save();
     }

     //Present Models
     Present::createModelFromVariantList($present);
     return ($present);
  }


  static public function undoDelete($id) {
    $present = Present::find($id);
    $present->deleted = 0;
    $present->active = 1;
    $present->save();
  }



  static public function deletePresent($id, $realDelete = false) {


    $present = Present::find($id);
    if ($realDelete)
    {
     $present->deleted = 1;
      $present->active = 0;
        $present->save();
    }  else  {
      //Soft delete
        $present->deleted = 1;
        $present->active = 0;
        $present->save();
    }
  }
  //---------------------------------------------------------------------------------------
  // Custom Methods
  //---------------------------------------------------------------------------------------

  //Model Functions
  //bruges vis ikke

  //static public function addModel($data) {
    // bloker hvis der ligger ordre, eller brugere er tilf�jes..
    //$presentModel = new PresentModel();
   // $presentModel->update_attributes($data);
  //  $presentModel->save();
  //  return($presentModel);
    //der skal laves en pr. sproglag

//  }
  //  static public function updateModel($data) {
  //    $presentModel =  PresentModel::find($data['id']);
  //    $presentModel->  update_attributes($data);
  //    $presentModel-> save();
  //    return($presentModel);
  //  }

  // static public function removeModel($id)  {
  //    $presentModel = PresentModel::find($id);
  //    $presentModel->delete();
  //    return($presentModel);
 //  }


   //Present Models
    static public function updateModels($present_id,$variant_list) {
      $present = Present::find($present_id);
      $present->variant_list  = $variant_list;
      $present->save();
      Present::createModelFromVariantList($present);
    }


    static public function createNewModel($present_id) {

      $present =  Present::find($present_id);  //tjek at present findes
      $max = PresentModel::find_by_sql('SELECT MAX(model_id) AS modelid FROM present_model ');
      $max = $max[0]->modelid +1;

      // bloker hvis der ligger ordre, eller brugere er tilf�jes..

      $presentModel = new PresentModel();
      $presentModel->model_id=$max;
      $presentModel->present_id=$present_id;
      $presentModel->language_id=1;
      $presentModel->save();

      $presentModel = new PresentModel();
      $presentModel->model_id=$max;
      $presentModel->present_id=$present_id;
      $presentModel->language_id=2;
      $presentModel->save();

      $presentModel = new PresentModel();
      $presentModel->model_id=$max;
      $presentModel->present_id=$present_id;
      $presentModel->language_id=3;
      $presentModel->save();

      $presentModel = new PresentModel();
      $presentModel->model_id=$max;
      $presentModel->present_id=$present_id;
      $presentModel->language_id=4;
      $presentModel->save();

      $presentModel = new PresentModel();
      $presentModel->model_id=$max;
      $presentModel->present_id=$present_id;
      $presentModel->language_id=5;
      $presentModel->save();

      return($max);

  }


   static public function Activate($id) {
      $present = Present::find($id);
      $present->active = 1;
      $present->save();
   }

   static public function Deactivate($id) {
      $present = Present::find($id);
      $present->active = 0;
      $present->save();
   }

   static public function getPresentDescription($presentid,$languageid) {
     $presentdescription = PresentDescription::find_by_present_id_and_language_id($presentid,$languageid);
     return($presentdescription->caption);
   }


   //27.03.2017
   //Ny funktion som skal vedligeholder model tabellen, ud fra variant_list
   //Man m� ikke slette en mode, som indg�r i en ordre...
   //Der skal v�re en deaktiverins felt

   static public function createModelFromVariantList($present) {

        $presenModelNoLanguageMap = array();

        if(isset($present->variant_list)) {

             $variantlist = json_decode($present->variant_list);


             //nulstil dummyfelt p� alle emodeller
             $presentmodels = PresentModel::all(array('conditions' => array('present_id = ?', $present->id)));
             foreach($presentmodels as $presentmodel) {
                $presentmodel->dummy = 0;
                $presentmodel->save();
             }

             foreach($variantlist as $variant) {
                $var = '';
                $variantsub = '';
                $variantno = '';
                $variantImg = '';
                $variantId = '';     // 11-07-2017


                $variantIdOriginal   = '';
                $variantCheck = null;
                $languageid = $variant->language_id;


                //pil data ud af det m�rkelige objekt
                foreach($variant->feltData as $feltdata) {

                      if(isset($feltdata->variantNr)) {
                         $variantno = $feltdata->variantNr;
                       }

                      if(isset($feltdata->variant)) {
                        $var = $feltdata->variant;
                      }

                      if(isset($feltdata->variantSub)) {
                        $variantsub = $feltdata->variantSub;
                      }

                      if(isset($feltdata->variantImg)) {
                        $variantImg = $feltdata->variantImg;
                      }

                      if(isset($feltdata->variantCheck)) {
                        $variantCheck = $feltdata->variantCheck;
                      }

                      if(isset($feltdata->variantId)) {
                        $variantId = $feltdata->variantId;
                      }

                      if(isset($feltdata->variantIdOriginal)) {
                        $variantIdOriginal = $feltdata->variantIdOriginal;
                      }


                   }

                 // Skal vi slette alle gamle gaver
                 //TODO: variant check er kun sat p� det danske sprog�ag
                   //if variantId <> '' skal kodes her



                  if($variantId == '') {

                    $presentmodel =PresentModel::find_by_present_id_and_language_id_and_model_present_no($present->id,$languageid,$variantno);
                  } else {

                    $presentmodel =PresentModel::find_by_model_id_and_language_id($variantId,$languageid);
                  }
                 
                  if(count($presentmodel->attributes)==0) {

                    //throw new exception($present->id);

                     //her opretter vi dem fra bat dundt.... vi har ikke noget id... hmm
                     $presentmodel =new PresentModel();
                     $presentmodel->dummy               = 1;
                     $presentmodel->present_id          =  $present->id;
                     $presentmodel->model_id            =  $variantId;
                     $presentmodel->language_id         =  $languageid;
                     $presentmodel->model_present_no    =  $variantno;
                     $presentmodel->original_model_id   =  $variantIdOriginal;
                     $presentmodel->model_name        =  $var;
                     $presentmodel->model_no          =  $variantsub;
                     $presentmodel->media_path        =  $variantImg;
                     /*if($variantCheck==="on")
                       $presentmodel->active            =  true;
                     else
                       $presentmodel->active            =  false;*/
                     $presentmodel->save();
                     
                     if(trimgf($presentmodel->model_present_no) != "")
                     {
                        if(isset($presenModelNoLanguageMap[$presentmodel->language_id])) $presenModelNoLanguageMap[$presentmodel->language_id] = array();
                        if(isset($presenModelNoLanguageMap[$presentmodel->language_id][trimgf(strtolower($presentmodel->model_present_no))]))
                        {
                            //echo "Ens varenr: ".$presentmodel->model_present_no; exit();
                        }
                        else $presenModelNoLanguageMap[$presentmodel->language_id][trimgf(strtolower($presentmodel->model_present_no))] = true;
                     }
                     
                  }else{
                     $presentmodel->dummy             =  1;
                     $presentmodel->model_name        =  $var;
                     $presentmodel->model_id          =  $variantId;
                     $presentmodel->model_present_no  =  $variantno;
                     $presentmodel->model_no          =  $variantsub;
                     $presentmodel->media_path        =  $variantImg;
                    /* if($variantCheck==="on") {
                       $presentmodel->active            =  true;
                    } else {
                       $presentmodel->active            =  false;
                    } */
                       $presentmodel->save();
                       
                     if(trimgf($presentmodel->model_present_no) != "")
                     {
                        if(!isset($presenModelNoLanguageMap[$presentmodel->language_id])) $presenModelNoLanguageMap[$presentmodel->language_id] = array();
                        if(!isset($presenModelNoLanguageMap[$presentmodel->language_id][trimgf(strtolower($presentmodel->model_present_no))]))
                        {
                            //echo "Ens varenr: ".$presentmodel->model_present_no; exit();
                        }
                        else $presenModelNoLanguageMap[$presentmodel->language_id][trimgf(strtolower($presentmodel->model_present_no))] = true;
                     }
                  }
             }

             //s� skal vi slette eventuelle rester
             $presentmodels = PresentModel::all(array('conditions' => array('present_id = ? AND dummy = 0', $present->id)));
             foreach($presentmodels as $presentmodel) {
                // jeg har deaktiveret denne, jeg tror vi skal lave en indeviduel slet metode
               // PresentModel::deletePresentModel($presentmodel->id,$presentmodel->model_id,true);
             }

         }
         //Delete presentmodels not in liste... nononoo
   }

   static public function getModelDescription($presentid,$modelno,$languageid) {

       //$presentdescription = PresentDescription::find_by_present_id_and_language_id($presentid,$languageid);
        die('virker ikke');
        $present = Present::find($presentid);
        $modelname ="";
        if(isset($present->variant_list)) {
            $variantlist = json_decode($present->variant_list);
             foreach($variantlist as $variant) {
               if(isset($variant->language_id)) {
                 if($variant->language_id == $languageid)
                   $variant = '';
                   $variantsub = '';
                   $variantno = '';
                   if (isset($variant->feltData)) {
                       die($modelno);
                       foreach($variant->feltData as $var)
                       {
                          if(isset($var->variantNo)) {
                             $variantno = $var->variantNo;
                          }
                          if(isset($var->variant)) {
                             $variant = $var->variant;
                          }
                          if(isset($var->variantSub)) {
                             $variantsub = $var->variantSub;
                          }
                       }

                       if($variantno == $modelno) {
                           die('as');
                         $modelname = utf8_decode($variant.' - '.$variantsub);
                       }
                   }
                }
              }
           }
           return $modelname;
   }

}

