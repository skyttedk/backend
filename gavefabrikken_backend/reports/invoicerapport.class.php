<?php
//

//https://github.com/PHPOffice/PHPExcel/wiki/User%20Documentation%20Overview%20and%20Quickstart%20Guide
class invoiceRapport Extends reportBaseController{
    public function run() {

        if($_GET['token']!="dit5740")
          die('dead');

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=invoicejournal.csv');
        $output = fopen('php://output', 'w');
        fwrite($output,
           		$this->encloseWithQuotes("Ordrenr").";".
           		$this->encloseWithQuotes("Antal").";".
           		$this->encloseWithQuotes("Shop ID").";".
              	$this->encloseWithQuotes("Udl�b uge").";".
              	$this->encloseWithQuotes("Gavekortnr. start").";".
              	$this->encloseWithQuotes("Gavekortnr. slut").";".
           		$this->encloseWithQuotes("Gavekort v�rdi").";".
                $this->encloseWithQuotes("Virksomhedsnavn").";".
                $this->encloseWithQuotes("CVR").";".
                $this->encloseWithQuotes("Attention").";".
                $this->encloseWithQuotes("Faktura adresse").";".
                $this->encloseWithQuotes("Faktura adresse 2").";".
                $this->encloseWithQuotes("Faktura postnr.").";".
                $this->encloseWithQuotes("Faktura by").";".
                $this->encloseWithQuotes("Faktura land").";".
                $this->encloseWithQuotes("Levering adresse").";".
                $this->encloseWithQuotes("Levering adresse 2").";".
                $this->encloseWithQuotes("Levering postnr.").";".
                $this->encloseWithQuotes("Levering by").";".
                $this->encloseWithQuotes("Levering land").";".
                $this->encloseWithQuotes("Email").";".
                $this->encloseWithQuotes("Telefon").";".
                $this->encloseWithQuotes("Elektronisk kort").";".
                $this->encloseWithQuotes("Till�gsordre").";".
                $this->encloseWithQuotes("EAN").";".
                $this->encloseWithQuotes("Levering virksomhed").";".
             "\n");



    $shops = '52,53,54,55,56,251,247,248,265,287,290,310';   // danske shops

     $companyorders = CompanyOrder::find_by_sql("SELECT
        `company_order`.*
        , `shop_user`.`company_order_id`
        , `company_order`.quantity
        , COUNT(`shop_user`.`id`) as active_users
    FROM
        `shop_user`
        INNER JOIN `company_order`
            ON (`shop_user`.`company_order_id` = `company_order`.`id`)
    WHERE (`company_order`.`shop_id` IN ($shops)
        AND `company_order`.`is_cancelled` =0
        AND `company_order`.`is_shipped` =1
        AND `shop_user`.`blocked` =0
        )
    GROUP BY `shop_user`.`company_order_id`;") ;






        foreach($companyorders as $companyorder)
        {
              $company = Company::find($companyorder->company_id);
              $expiredate = expireDate::getByExpireDate($companyorder->expire_date);
//              dump($companyorder->expire_date);
  //            die($companyorder->expire_date);
             fwrite($output,
             		$this->encloseWithQuotes($companyorder->order_no).";".
//                    $this->encloseWithQuotes($companyorder->quantity).";".        changed to active users
                    $this->encloseWithQuotes($companyorder->active_users).";".
                    $this->encloseWithQuotes($companyorder->shop_id).";".
                    $this->encloseWithQuotes($expiredate->week_no).";".
                    $this->encloseWithQuotes($companyorder->certificate_no_begin).";".
                    $this->encloseWithQuotes($companyorder->certificate_no_end).";".
                    $this->encloseWithQuotes($companyorder->certificate_value).";".
                    $this->encloseWithQuotes($company->name).";".
                    $this->encloseWithQuotes($company->cvr).";".
                    $this->encloseWithQuotes($company->contact_name).";".
                    $this->encloseWithQuotes($company->bill_to_address).";".
                    $this->encloseWithQuotes($company->bill_to_address_2).";".
                    $this->encloseWithQuotes($company->bill_to_postal_code).";".
                    $this->encloseWithQuotes($company->bill_to_city).";".
                    $this->encloseWithQuotes($company->bill_to_country).";".
                    $this->encloseWithQuotes($company->ship_to_address).";".
                    $this->encloseWithQuotes($company->ship_to_address_2).";".
                    $this->encloseWithQuotes($company->ship_to_postal_code).";".
                    $this->encloseWithQuotes($company->ship_to_city).";".
                    $this->encloseWithQuotes($company->ship_to_country).";".
                    $this->encloseWithQuotes($company->contact_email).";".
                    $this->encloseWithQuotes($company->contact_phone).";".
                    $this->encloseWithQuotes($companyorder->is_email).";".
                    $this->encloseWithQuotes($companyorder->is_appendix_order).";".
                    $this->encloseWithQuotes($companyorder->ean).";".
                    $this->encloseWithQuotes(trimgf($company->ship_to_company) == "" ? $company->name : $company->ship_to_company)."\r\n"
                );

                $companyorder2 =  CompanyOrder::find($companyorder->id);
                $companyorder2->is_invoiced = 1;
                $companyorder2->save();
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