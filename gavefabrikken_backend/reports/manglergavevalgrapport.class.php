<?php
/*
  rapport over bruger som manger at v�lge gave
*/
class manglerGaveValgRapport Extends reportBaseController{

    public function run($shopID) {

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=manglergavevalg.csv');
        // create a file pointer connected to the output stream
        $output = fopen('php://output', 'w');
        $shopusers = ShopUser::all(array('shop_id' => $shopID,'is_demo' => 0));




        //Header
       foreach($shopusers[0]->attributes_ as $userattribute)
        {
            $shopattribute = ShopAttribute::find($userattribute->attribute_id);
           fwrite($output,$this->encloseWithQuotes(utf8_decode($shopattribute->name)).';');
        }

      fwrite($output,"\n");
      foreach($shopusers as $shopuser)
      {
          if(count($shopuser->orders)==0) {
            $userattributes = UserAttribute::all(array('shopuser_id' => $shopuser->id));
            foreach($userattributes as $attribute)
    	     {
                 $attribute->attribute_value = str_replace(["\r\n", "\r", "\n"], '', $attribute->attribute_value);
                fwrite($output,$this->encloseWithQuotes(utf8_decode($attribute->attribute_value)).';');
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
    $value = str_replace(';', '-', $value);
   // $value = str_replace(';', '', $value);
    return $value;
}}
?>