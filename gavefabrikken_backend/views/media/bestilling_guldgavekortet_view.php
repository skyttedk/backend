<!DOCTYPE html>
<html>
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
<head>
    <title></title>
    <script src="http://code.jquery.com/jquery-1.10.2.js"></script>

    <script>
   function validateForm() {

       var spdeal = '';

        var spdealTxt = '';
        if ($('#spdeal').is(':checked'))
        {
            spdeal = spdeal;
            spdealTxt = $('#spdealTxt').val();
        }
        var allOkay = true;
        salgerperson = $('#salesperson').val();
        var  noter = $('#noter').val()+'#@#'+spdeal+'#@#'+$('#ean').val()+'#@#'+spdealTxt;
        $('#noter').val(noter);


   }
    </script>

<head>
    <body onload="">

     <h3>Bestil Guldgavekort</h3>


    <form action="/gavefabrikken_backend/index.php?rt=bestilling/guldgavekort" onsubmit="return validateForm()" method="post" accept-charset="UTF-8">


      <input type="hidden" name="shop_id" value="53"/>
      <input type="hidden" name="shop_name" value="guldgavekortet"/>


      <br>Antal  <input name="quantity" type="number" min="0" required/>


      <hr>
      <b>Forsendelsesmetode</b><br>
      <input type="radio" required name="shipment_method" value="e">Elektronik<br>
      <input type="radio" name="shipment_method" value="f">Fysisk<br>

      <hr>
      <b>Leveringdato</b><br>

      <input type="radio" required name="expire_date" value="12-11-2017">Gavevalg deadline: 12.11.2017 og gaver leveres i uge 49<br>
      <input type="radio" name="expire_date" value="26-11-2017">Gavevalg deadline: 26.11.2017 og gaver leveres i uge 51<br>
      <input type="radio" name="expire_date" value="31-12-2017">Gavevalg deadline: 31.12.2017 og gaver leveres i uge 4<br>


      <hr>
      <b>Fakturering:</b>
      <br> Virksomhed
      <input name="name" required type="text" placeholder="" value="">

      <br>  Adresse
      <input name="bill_to_address" required type="text" placeholder="" value="">

      <br>Postnr.
      <input name="bill_to_postal_code" required type="text" placeholder="" value="">

      <br>By.
      <input name="bill_to_city"  required type="text" placeholder="" value="">

      <br>Cvr
      <input name="cvr" type="text" required placeholder="" value="">
      <br>EAN
      <input  id="ean" type="text" placeholder="" value="">

      <br>

            <hr>
      <b>Kontakt:</b> <br>

      <br>Navn
      <input name="contact_name" required type="text" placeholder="" value="">
      <br>Telefon
      <input name="contact_phone" required type="text" placeholder="" value="">
      <br>Email
      <input name="contact_email" required type="text" placeholder="" value="">
      <br>

      <hr>
      <b>Levering:</b> <br>


     <br>  Adresse
     <input name="ship_to_address" type="text" placeholder="" value="">

     <br>Postnr.
     <input name="ship_to_postal_code" type="text" placeholder="" value="">

     <br>By.
     <input name="ship_to_city" type="text" placeholder="" value="">

     <br>
          <hr>
          <br>
     <b>Intern:</b> <br>
     <br>Sælger
     <input name="salesperson" id="salesperson" required type="text" placeholder="" value="<?php echo $salesperson; ?>">

     <br>Noter:<br>
     <textarea name="noter" cols="50" rows="7" id="noter"></textarea>

     <br>Special aftale
     <input id="spdeal" type="checkbox" />

     <br>Special aftale tekst:<br>
     <textarea  cols="50" rows="7" id="spdealTxt"></textarea>

     <hr>

     <input type="submit" value="Bestil">
     </form>


    </body>
</html>