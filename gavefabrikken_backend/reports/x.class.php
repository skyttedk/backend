<?php
/*
  rapport over gavekort med leveringsadresse som er udl�st.
  Dette er en hj�lpe rapport til Jannie... skal laves i administrations module sernere
*/

class x Extends reportBaseController{

    public function run() {

      if($_GET['token']!="dit5740")
          die('dead');

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=gavermedlevering.csv');

        // create a file pointer connected to the output stream
        $output = fopen('php://output', 'w');
        $total = false;

        $sql = "SELECT
            `shop_user`.`username`
            , `shop_user`.`id`
            , `shop_user`.`blocked`
            , `shop_user`.`is_delivery`
            , `shop_user`.`delivery_printed`
            , `shop_user`.`expire_date`
            , `order`.`present_name`
            , `order`.`present_model_name`
        FROM
            `order`
            INNER JOIN `shop_user`
                ON (`order`.`shopuser_id` = `shop_user`.`id`)
        WHERE ( `shop_user`.`blocked` =0
            AND `shop_user`.`is_delivery` =1 ) ORDER BY `order`.`present_name` ASC;";

       $shopusers = ShopUser::find_by_sql($sql);

       //Header
       if(count($shopusers)>0) {
           fwrite($output,$this->encloseWithQuotes("Dato").';');
           fwrite($output,$this->encloseWithQuotes("Gavevalg").';');
           fwrite($output,$this->encloseWithQuotes("Model").';');
           foreach($shopusers[0]->attributes_ as $userattribute)
            {
                $shopattribute = ShopAttribute::find($userattribute->attribute_id);
                if($shopattribute->is_password==0)
                  fwrite($output,$this->encloseWithQuotes(utf8_decode($shopattribute->name)).';');
            }

          fwrite($output,"\n");
          foreach($shopusers as $shopuser)
          {

              if(!$shopuser->expire_date =="")
                fwrite($output,$this->encloseWithQuotes($shopuser->expire_date->format('d-m-Y')).';');
              else
               fwrite($output,';');

              fwrite($output,$this->encloseWithQuotes(utf8_decode($shopuser->present_name)).';');
              fwrite($output,$this->encloseWithQuotes(utf8_decode(str_replace('###',' - ',$shopuser->present_model_name))).';');
              // udskriv attributter
              $userattributes = UserAttribute::all(array('shopuser_id' => $shopuser->id));
               foreach($userattributes as $attribute)
        	     {
                    if($attribute->is_password==0)
                      fwrite($output,$this->encloseWithQuotes(utf8_decode($attribute->attribute_value)).';');
                 }
                fwrite($output,"\n");


           }
       }
       fwrite($output,"\n");
    System::connection()->commit();

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