<div id="modalNewSmsJob" style="display:none;">
  <table border=0 width=380 >
<tr>
<td width="320" valign="top" style="color:white;">
    <fieldset style="width:300px">
        <legend>JOB-NAVN</legend>
        <input id="job_navn" style="width:250px;" value="" />
    </fieldset>
    <fieldset style="width:300px">
        <legend>TELEFON-NUMMER</legend>
        <input id="job_tlf" style="width:150px;" value="" /><button onclick="smsjob.sendTest();">Send test</button>
    </fieldset>
    <fieldset style="width:300px">
        <legend>V&OElig;LG SMS GRUPPE</legend>
        <select id="job_grp">
            <option value="" disabled selected >V&oelig;lg gruppe</option>
            <option value="9">Gavekort</option>
        </select>
    </fieldset>

    <fieldset style="width:300px;">
        <legend>SMS TITEL:</legend>

        <input id="job_smsTitle" style="width:280px;" >
         <br />
    </fieldset>
    <fieldset style="width:300px;">
        <legend>SMS TEKST:</legend>

        <textarea id="job_smsTxt" rows="6" style="width: 280px;"></textarea>
        <div><label id="job_chrNumber" style="font-size:12px;">0 ud af 160 tegn er benyttet</label></div>
    </fieldset>
    <br /><br />

</td>
</tr>
</table>

</div>