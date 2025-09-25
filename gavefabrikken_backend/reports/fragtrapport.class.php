<?php
class fragtRapport Extends reportBaseController{
    public function run() {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=fragtjournal.csv');

        $output = fopen('php://output', 'w');
        fwrite($output,
           		$this->encloseWithQuotes("Ordrenr").";".
           		$this->encloseWithQuotes("Antal").";".
              	$this->encloseWithQuotes("Udløb uge").";".
                $this->encloseWithQuotes("Virksomhedsnavn").";".
                $this->encloseWithQuotes("CVR").";".
                $this->encloseWithQuotes("Fragt").";".
             "\n");



        //  der skal laves et ny felt: freight_calculated på sshop_user tabellen i stedet for.
        //  Der skal ikke dannes fragt på annullerede kort/slettede kort

        //  **  Skal beregne korrekt antal brugere/kor... eller er det dem som har lavet en orre.. er de
        //  **  samle fakturering i Navison.
        // -  Skal grupperes så ikke sender
        // -  Skal tage højde for om der er slettet kort..
        // -  Skal tage højde for alt hvad der er lavet efterfølgende.

        $companyorders = CompanyOrder::find('all',array(
           'conditions' => array(
  //         'expire_date' => '2016-12-31',
           'is_printed' => 1,
           'is_shipped' => 1,
           'is_cancelled' => 0,
           'is_invoiced' => 1,
           'freight_calculated' => 0 )
        ));

        foreach($companyorders as $companyorder)
        {
          $company = Company::find($companyorder->company_id);
          fwrite($output,
       		$this->encloseWithQuotes($companyorder->order_no).";".
            $this->encloseWithQuotes($this->calculateQuantity($companyorder->company_id,$companyorder->expire_date)).";".
            $this->encloseWithQuotes(expiredate2WeekNo($companyorder->expire_date)).";".
            $this->encloseWithQuotes($company->name).";".
            $this->encloseWithQuotes($company->cvr).";".
            $this->encloseWithQuotes($this->calculatefreight($this->calculateQuantity($companyorder->company_id,$companyorder->expire_date))).";\n"
           );
            $companyorder->freight_calculated = 1;
            $companyorder->save();
        }
 }

 function calculateQuantity($companyid,$expiredate) {
       $orders = Order::find('all',array(
           'conditions' => array(
           'company_id' => $companyid,
           'gift_certificate_end_date' => $expiredate,
           'freight_calculated' => 0 )
        ));
        return(count($orders));

 }

 function calculatefreight($quantity) {
     if($quantity >=  0  && $quantity <= 10)       { return(388.0); }
     elseif($quantity >=  11 && $quantity <= 20)   { return(558.0); }
     elseif($quantity >=  21 && $quantity <= 40)   { return(1116.0); }
     elseif($quantity >=  41 && $quantity <= 60)   { return(1674.0); }
     elseif($quantity >=  61 && $quantity <= 80)   { return(2232.0); }
     elseif($quantity >=  81 && $quantity <= 100)  { return(2790.0); }
     elseif($quantity >=  101 && $quantity <= 120) { return(3013.0); }
     elseif($quantity >=  121 && $quantity <= 140) { return(3515.0); }
     elseif($quantity >=  141 && $quantity <= 160) { return(4018.0); }
     elseif($quantity >=  161 && $quantity <= 180) { return(4520.0); }
     elseif($quantity >=  181 && $quantity <= 200) { return(5022.0); }
     elseif($quantity >=  201 && $quantity <= 220) { return(5217.0); }
     elseif($quantity >=  221 && $quantity <= 240) { return(5692.0); }
     elseif($quantity >=  241 && $quantity <= 260) { return(6166.0); }
     elseif($quantity >=  261 && $quantity <= 280) { return(6640.0); }
     elseif($quantity >=  281 && $quantity <= 300) { return(6696.0); }
     elseif($quantity >=  301 && $quantity <= 320) { return(7142.0); }
     elseif($quantity >=  321 && $quantity <= 340) { return(7589.0); }
     elseif($quantity >=  341 && $quantity <= 360) { return(8035.0); }
     elseif($quantity >=  361 && $quantity <= 380) { return(8482.0); }
     elseif($quantity >=  381 && $quantity <= 400) { return(8928.0); }
}



  public function updateOrderDates($expiredate) {

      $shopusers =  ShopUser::find_by_sql("
              SELECT
            `shop_user`.`username`
            , `shop_user`.`expire_date` AS `exp1`
            , `shop_user`.`blocked`
            , `shop_user`.`username`
            , `company_order`.`certificate_no_begin`
            , `company_order`.`certificate_no_end`
            , `company_order`.`id`
        FROM
            `shop_user`
            INNER JOIN `company_order`
                ON (`shop_user`.`company_id` = `company_order`.`company_id`)
        WHERE (`shop_user`.`expire_date` ='$expiredate'
            AND `shop_user`.`blocked` =0)
        GROUP BY `company_order`.`id`");

      foreach($shopusers as $shopuser){
          $companyorder =CompanyOrder::find($shopuser->id);
          if($companyorder->expire_date->format('Y-m-d') != $expiredate)  {
             echo $companyorder->expire_date->format('Y-m-d').'<br>';
               $companyorder->expire_date  = $expiredate;
               $companyorder->save(false);
             }
      }

  }


 function encloseWithQuotes($value)
{
    if (empty($value)) {
        return "";
    }
    $value = str_replace(';', '_', $value);
   return($value);
}

}
?>