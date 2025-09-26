<?php

Class bestillingController Extends baseController {

public function index() {
}

public function guldgavekort() {

   if(isset($_POST['salesperson'])) {
     $this->registry->template->salesperson  = $_POST['salesperson'];
    } else {
     $this->registry->template->salesperson  = '';

    }
    $this->tryPlaceOrder();

    $this->registry->template->show('bestilling_guldgavekortet_view');
}

public function kenderdutypen() {
   if(isset($_POST['salesperson'])) {
     $this->registry->template->salesperson  = $_POST['salesperson'];
    } else {
     $this->registry->template->salesperson  = '';

    }

    $this->tryPlaceOrder();
    $this->registry->template->show('bestilling_kenderdutypen_view');
}

public function drommegavekortet() {
   if(isset($_POST['salesperson'])) {
     $this->registry->template->salesperson  = $_POST['salesperson'];
    } else {
     $this->registry->template->salesperson  = '';

    }

    $this->tryPlaceOrder();
    $this->registry->template->show('bestilling_drommegavekortet_view');
}


public function tryPlaceOrder() {
 if(isset($_POST['shop_name'])) {
    try {
      $this->placeOrder();
      echo '<p style="color:green"><b>Ordren blev oprettet!</b></p>';
    } catch (Exception $e) {
      echo '<p style="color:red"><b>'.$e->getMessage()."!</b></p>";
    }
    }
}


 //Denne funktion benyttes af de nye bestillingssites
    public function placeOrder() {

        if(!isset($_POST['expire_date']))
          throw new exception('Angiv leveringsdato');

        $expiredate = ExpireDate::find_by_display_date_and_blocked($_POST['expire_date'], 0);
        if(!isset($expiredate))
          throw new exception('Ugyldig leveringsdato: '.$_POST['expire_date']);


        $companyimport = new  CompanyImport($_POST);

//        $companyimport->deleted = 1; //TEMP s� den ikke bliver importeret
        $companyimport->expire_date =   DateTime::createFromFormat('d-m-Y', $_POST['expire_date']);

        switch ($companyimport->shop_id) {
           case 53:
                 $companyimport->value = 800;
                 break;
           case 287:
                 $companyimport->value = 100;
                 break;
           case 290:
                 $companyimport->value = 200;
                 break;
           case 310:
                 $companyimport->value = 300;
                 break;
           case 247:
                 $companyimport->value = 600;
                 break;
           case 248:
                 $companyimport->value = 800;
                 break;
           case 265:
                 $companyimport->value = 600;
                 break;
        }

        testRequired($companyimport,'value','Angiv v�rdi');
        testRequired($companyimport,'expire_date');
        testRequired($companyimport,'shipment_method','Forsendelses metode mangler');
        testRequired($companyimport,'quantity','Antal  mangler');
        testRequired($companyimport,'name','Navn mangler');
        testRequired($companyimport,'cvr','CVR mangler');
        testRequired($companyimport,'bill_to_address','Faktureringsadresse mangler');
        testRequired($companyimport,'bill_to_postal_code','Faktureringspostnr. mangler');
        testRequired($companyimport,'bill_to_city','Fakturaeringsby mangler');
        testRequired($companyimport,'contact_name','Kontaktnavn mangler');
        testRequired($companyimport,'contact_email','Kontakt email mangler');
        testRequired($companyimport,'contact_phone','Kontakt telefon mangler');




        //Check noter
        if($companyimport->noter=="")
          $companyimport->noter =  "#@##@##@#";

        $companyimport->save();

        system::connection()->commit();


    }

}

?>

