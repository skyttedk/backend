

<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
<div style="font-family:verdana;font-size:small;width:500px">

<form action="index.php?rt=user">
 <fieldset>
    <legend><b>Virksomhedsoplysninger</b></legend>
      <?php
        createInput('Navn',$company->name,300);
        createInput('CVR',$company->cvr,300);
        createInput('Website',$company->website,300);
        createInput('Telefon',$company->phone,300);
        createSelect('Sprogkode',100);
        createInput('Logo',$company->logo,300);
        createInput('Footer',$company->footer,300);
      ?>

  </fieldset>
   <fieldset>
    <legend><b>Leveringsadresse</b></legend>
      <?php
        createInput('Att:',$company->ship_to_attention,300);
        createInput('Adresse',$company->ship_to_address,300);
        createInput('Adresse 2',$company->ship_to_address_2,300);
        createInput('Postnr. ',$company->ship_to_postal_code,100);
        createInput('By ',$company->ship_to_city,300);
        createInput('Land ',$company->ship_to_country,300);
     ?>
   </fieldset>

 <fieldset>
    <legend><b>Kontaktoplysninger</b></legend>
  <?php
    createInput('Kontakt navn',$company->contact_name,300);
    createInput('Kontakt email',$company->contact_email,300);
    createInput('Kontakt telefon',$company->contact_phone,300);
  ?>
  </fieldset>
 <br>

 <fieldset>
    <legend><b>Login Informationer</b></legend>
      <?php
          createInput('Virksomhed link',$company->link,300);
          createInput('Brugernavn',$company->username,300);
          createInput('Kodeord',$company->password,300,'password');
          createInput('Aktiv','',300,'checkbox');
       ?>
 </fieldset>

 <input type="submit" Value="Gem"/>
  <input type="reset" Value="Nulstil"/>
</form>

 </div>

 <?php

 function createInput($caption,$value,$width,$type="text") {
    echo '<div style="width:450px;float:left;padding:2px">';


    echo '<div style="width:150px;float:left">';
    echo '<label>'.$caption.'</label>';
    echo '</div>';

    echo '<div style="float:left;width:'.$width.'px">';
    echo '<input value="'.$value.'" style="width:'.$width.'px" type="'.$type.'"/>';
    echo '</div>';

    echo '</div>';
 }


  function createSelect($caption,$width) {
    echo '<div style="width:450px;float:left;padding:2px">';


    echo '<div style="width:150px;float:left">';
    echo '<label>'.$caption.'</label>';
    echo '</div>';

    echo '<div style="float:left;width:'.$width.'px">';
    echo '<select style="width:'.$width.'px">';
    echo '<option>DK</option>';
    echo '<option>DE</option>';
    echo '<option>EN</option>';
    echo '</select>';
    echo '</div>';

    echo '</div>';
 }
 ?>
