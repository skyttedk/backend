
<div id="dialog-form" title="Opret valgshop">
  <div id="shop">
    <fieldset>
      <label for="name">Navn</label>
      <input type="text" name="name" value="<?php echo ($shop->name); ?>" class="text ui-widget-content ui-corner-all"><br>

      <label for="cvr">CVR</label>
      <input type="text" name="cvr" value="<?php echo ($shop->company[0]->cvr); ?>" class="text ui-widget-content ui-corner-all"> <br>

      <label for="website">Website</label>
      <input type="text" name="website" value="<?php echo ($shop->company[0]->website); ?>" class="text ui-widget-content ui-corner-all"> <br>

      <label for="contact_name">Kontakt navn</label>
      <input type="text" name="contact_name" value="<?php echo ($shop->company[0]->contact_name); ?>" class="text ui-widget-content ui-corner-all">  <br>

      <label for="contact_phone">Kontakt telefon</label>
      <input type="text" name="contact_phone" value="<?php echo ($shop->company[0]->contact_phone); ?>" class="text ui-widget-content ui-corner-all"> <br>

      <label for="contact_email">Kontakt Email</label>
      <input type="text" name="contact_email" value="<?php echo ($shop->company[0]->contact_email); ?>" class="text ui-widget-content ui-corner-all"> <br>

      <input type="submit" tabindex="-1" style="position:absolute; top:-1000px">  <br>

    </fieldset>
  </div>
</div>


<div >
    <input id="#create_company" type="button"  onclick ="createShop();" value="Opret" name="Opret"/>
</div>

<script>

function createShop() {
  submitForm("shop","shop/createValgShop",reloadPage);
}
function reloadPage() {
    $(':input').val('');

}

</script>
