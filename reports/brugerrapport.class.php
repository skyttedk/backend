<?php
/*
    rapport over alle brugere p� en shop, samt deres gave valg
*/
class BrugerRapport Extends reportBaseController{
     public function run($shopID) {
         
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=brugerrapport.csv');
        // create a file pointer connected to the output stream
        $output = fopen('php://output', 'w');
        $sql = "SELECT
                `shop_user`.`shop_id`
                , `shop_user`.`username`
                , `shop_user`.`password`
                , `user_attribute`.`attribute_value`
                , `order`.`present_name`
                , `order`.`present_model_name`
                , `present_model`.`fullalias`
            FROM
                `user_attribute`
                LEFT JOIN `shop_user`
                    ON (`user_attribute`.`shopuser_id` = `shop_user`.`id`)
                LEFT JOIN `order`
                    ON (`order`.`shopuser_id` = `shop_user`.`id`)
                LEFT JOIN `present_model`
                    ON (`order`.`present_model_id` = `present_model`.`model_id`)
            WHERE (`shop_user`.`shop_id` =$shopID
                AND `shop_user`.`is_demo` =0
                and `present_model`.language_id = 1
                )
            ORDER BY `shop_user`.`username` ASC;  ";

       $shopusers = ShopUser::find("first", array("conditions" => "shop_id = $shopID"));
        //Header
       foreach($shopusers->attributes_ as $userattribute)
        {

            $shopattribute = ShopAttribute::find($userattribute->attribute_id);
           fwrite($output,$this->encloseWithQuotes(utf8_decode($shopattribute->name)).';');
        }

       $shopusers = ShopUser::find_by_sql($sql);
       fwrite($output,$this->encloseWithQuotes(utf8_decode('Gavenavn')).';');
       fwrite($output,$this->encloseWithQuotes(utf8_decode('Model')).';');
       fwrite($output,$this->encloseWithQuotes(utf8_decode('Alias')).';');
       fwrite($output,"\n");
       $lastusername = $shopusers[0]->username;


       foreach($shopusers as $shopuser)
         {

            if($lastusername !== $shopuser->username) {
               $presentModelPart = "";

               $presentPart =   explode("###",$presentmodelname);
               if(count( $presentPart) > 1){
                   $presentModelPart = $presentPart[1];
               }


              fwrite($output,$this->encloseWithQuotes(utf8_decode($presentPart[0])).';');
              fwrite($output,$this->encloseWithQuotes(utf8_decode(str_replace('###',' - ',$presentModelPart))).';');
              fwrite($output,$fullalias.';');
              fwrite($output,"\n");
            }
             $shopuser->attribute_value = str_replace(["\r\n", "\r", "\n"], '',$shopuser->attribute_value);
            fwrite($output,$this->encloseWithQuotes(utf8_decode($shopuser->attribute_value)).';');
            $presentname =  $shopuser->present_name;
            $presentmodelname =  $shopuser->present_model_name;
            $lastusername = $shopuser->username;
            $fullalias = $shopuser->fullalias;
         }
         //gavevalg p� sidste linje
         $presentModelPart = "";
         $presentPart =   explode("###",$presentmodelname);
         if(count( $presentPart) > 1){
            $presentModelPart = $presentPart[1];
         }
         fwrite($output,$this->encloseWithQuotes(utf8_decode($presentPart[0])).';');
         fwrite($output,$this->encloseWithQuotes(utf8_decode(str_replace('###',' - ',$presentModelPart))).';');
         fwrite($output,$fullalias.';');
         fwrite($output,"\n");

 }

 public function run_old($shopID) {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=brugerrapport.csv');
        // create a file pointer connected to the output stream
        $output = fopen('php://output', 'w');
        $sql = "SELECT
                `shop_user`.`shop_id`
                , `shop_user`.`username`
                , `shop_user`.`password`
                , `user_attribute`.`attribute_value`
                , `order`.`present_name`
                , `order`.`present_model_name`
            FROM
                `user_attribute`
                LEFT JOIN `shop_user`
                    ON (`user_attribute`.`shopuser_id` = `shop_user`.`id`)
                LEFT JOIN `order`
                    ON (`order`.`shopuser_id` = `shop_user`.`id`)
            WHERE (`shop_user`.`shop_id` =$shopID
                AND `shop_user`.`is_demo` =0)
            ORDER BY `shop_user`.`username` ASC;  ";

       $shopusers = ShopUser::find("first", array("conditions" => "shop_id = $shopID"));
        //Header
       foreach($shopusers->attributes_ as $userattribute)
        {
            $shopattribute = ShopAttribute::find($userattribute->attribute_id);
           fwrite($output,$this->encloseWithQuotes(utf8_decode($shopattribute->name)).';');
        }

       $shopusers = ShopUser::find_by_sql($sql);
       fwrite($output,$this->encloseWithQuotes(utf8_decode('Gavenavn')).';');
       fwrite($output,$this->encloseWithQuotes(utf8_decode('Model')).';');
       fwrite($output,"\n");
       $lastusername = $shopusers[0]->username;
       foreach($shopusers as $shopuser)
         {
            if($lastusername !== $shopuser->username) {
               $presentModelPart = "";
               $presentPart =   explode("###",$presentmodelname);
               if(count( $presentPart) > 1){
                   $presentModelPart = $presentPart[1];
               }


               fwrite($output,$this->encloseWithQuotes(utf8_decode($presentPart[0])).';');
               fwrite($output,$this->encloseWithQuotes(utf8_decode(str_replace('###',' - ',$presentModelPart))).';');
               fwrite($output,"\n");
            }

            fwrite($output,$this->encloseWithQuotes(utf8_decode($shopuser->attribute_value)).';');
            $presentname =  $shopuser->present_name;
            $presentmodelname =  $shopuser->present_model_name;
            $lastusername = $shopuser->username;
         }
         //gavevalg p� sidste linje
         $presentModelPart = "";
         $presentPart =   explode("###",$presentmodelname);
         if(count( $presentPart) > 1){
            $presentModelPart = $presentPart[1];
         }
         fwrite($output,$this->encloseWithQuotes(utf8_decode($presentPart[0])).';');
         fwrite($output,$this->encloseWithQuotes(utf8_decode(str_replace('###',' - ',$presentModelPart))).';');
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
}

}
