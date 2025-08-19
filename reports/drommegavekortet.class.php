<?php
/*
  rapport over drommegavekort
  //Kom denne shop overhovedet  nogensiden op at køre     ??????

*/
class drommeGavekortRapport Extends reportBaseController{

    public function run() {

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=gavermedlevering.csv');

        // create a file pointer connected to the output stream
        $output = fopen('php://output', 'w');
        /*
        $shopusers = ShopUser::find_by_sql("SELECT
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
            AND `shop_user`.`is_delivery` =1
            AND `shop_user`.`delivery_printed` = 0 )
        ORDER BY `order`.`present_name` ASC;");
          */

       //Sidste rapport genereret d 15.12 08:42
     $shopusers = ShopUser::find_by_sql("SELECT
        `shop_user`.`id`
        , `shop_user`.`is_delivery`
        , `shop_user`.`delivery_printed`
        , `order`.`order_timestamp`
        , `shop_user`.`username`
        , `shop_user`.`expire_date`
        , `order`.`present_name`
        , `order`.`present_model_name`
        FROM
            `order`
            RIGHT JOIN `shop_user`
                ON (`order`.`shopuser_id` = `shop_user`.`id`)
        WHERE (
            `shop_user`.`is_delivery` =1
)
            AND (`order`.`order_timestamp` > '2016-11-29 21:32:00');
        ");

//        die(ShopUser::connection()->last_query);


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

              fwrite($output,$this->encloseWithQuotes($shopuser->expire_date->format('d-m-Y')).';');
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
                    $shopuser2 = ShopUser::find($shopuser->id);
               $shopuser2->delivery_printed = 1;
               $shopuser2->save();
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