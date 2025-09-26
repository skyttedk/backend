<?php
/*
  rapport over bruger som manger at vælge gave
*/
class manglerGaveAfhentningRapport Extends reportBaseController{

    public function run($shopID) {

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=manglerafhentning.csv');
        // create a file pointer connected to the output stream
        $output = fopen('php://output', 'w');

        $shopusers = ShopUser::all(array('shop_id' => $shopID,'is_demo' => 0,'blocked' => 0));



       //Header
       foreach($shopusers[0]->attributes_ as $userattribute)
        {
            $shopattribute = ShopAttribute::find($userattribute->attribute_id);
           fwrite($output,$this->encloseWithQuotes(utf8_decode($shopattribute->name)).';');
        }
           fwrite($output,$this->encloseWithQuotes(utf8_decode("Gavevalg")).';');
           fwrite($output,$this->encloseWithQuotes(utf8_decode("Model")).';');

      fwrite($output,"\n");
      foreach($shopusers as $shopuser)
      {

         $hasPickedPresent = true;
         $hasOrder = false;

          if(count($shopuser->orders)==0) {
              $hasPickedPresent   = false;
              $hasOrder = false;

          }  else {
             $hasOrder = true;
             if($shopuser->orders[0]->registered ==0) {
                    $hasPickedPresent   = false;
                }
               }


          if(!$hasPickedPresent) {
            $userattributes = UserAttribute::all(array('shopuser_id' => $shopuser->id));
            foreach($userattributes as $attribute)
    	     {
                fwrite($output,$this->encloseWithQuotes(utf8_decode($attribute->attribute_value)).';');
             }

          if($hasOrder==true) {
                 $presentModelPart = "";
               $presentPart =   explode("###",$shopuser->orders[0]->present_model_name);
               if(count( $presentPart) > 1){
                   $presentModelPart = $presentPart[1];
               }
               fwrite($output,$this->encloseWithQuotes(utf8_decode($presentPart[0])).';');
               fwrite($output,$this->encloseWithQuotes(utf8_decode($presentModelPart)).';');
           }   else {
               fwrite($output,';');
               fwrite($output,';');
           }

           fwrite($output,"\n");
          }

      }

      fwrite($output,"\n");


 }
function encloseWithQuotes($value)
{
    if (empty($value)) {
        return "";
    }
    $value = str_replace('"', '""', $value);
    return '="'.$value.'"';
}}
?>