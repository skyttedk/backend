<div id="dialog-CardShop-MultiCreate"  style="display:none;">
<fieldset>
<legend><b>Masseindl&oelig;sning af leveringsaddresser via csv fil</b></legend>
        <form class="uploadFileCSMC" enctype="multipart/form-data">
            <input  name="CSMCfile" type="file" id="CSMCfile" />
            <input  id="CSMCupload" type="button" value="Ind&oelig;s fil" />   <progress  style="display:none;" id="CSMC-progress"></progress>
        </form>
        <br>
        <a href="files/template.csv">Download template csv file</a>
</fieldset>
<br />
<fieldset>
<legend><b>Tilf&oslash;j ekstra leveringsaddresse</b></legend>

<br />
<table  width="500">
<tr>
    <td  width="130">Virksonhed: </td><td><input type="text" class="ship_to_company_node" /></td>
</tr>


<tr>
    <td  width="130">adresse1: </td><td><input type="text" class="ship_to_address_node" /></td>
</tr>
<tr>
    <td>adresse2: </td><td><input type="text" class="ship_to_address_2_node" /></td>
</tr><tr>
    <td>Postnr.:</td><td><input type="text" class="ship_to_postal_code_node" /></td>
</tr><tr>
    <td>By:</td><td><input type="text" class="ship_to_city_node" /></td>
</tr>
</table>
<hr />
<table  width="500">
<tr>
    <td width="180">Fortrolig kontaktperson:</td><td width="350"><input type="text" class="contact_name_node" /></td>
</tr>
<tr>
    <td>Telefonnummer:</td><td><input type="text" class="contact_phone_node" /></td>
</tr>
<tr>
    <td>E-mailadresse:</td><td><input type="text" class="contact_email_node" /></td>
</tr>

</table>
<button style="float: right; margin-right:10px;" onclick="cardCompany.newCompanyNode()">Tilf&oslash;j leveringsaddresse</button>
</fieldset>
</div>