<!-- Modal -->
<div class="modal fade" id="settingPresent" tabindex="-1" role="dialog" aria-labelledby="settingModalLabel"
  aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="settingModalLabel">Priser</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <fieldset>
      <legend></legend>
      <table >
      <tbody>
      <tr>
      <td><input type="checkbox" id="use_custon_price" ><b style="font-size: 18px;"> Overskriv priser med følgende:</b> </td>
      </tr>
      <tr>
      <td width= 350> (Hvis du sætter et 0 i et feltet bliver prisen <u>ikke</u> vist!</td>
      </tr>
          <tr>
      <td><hr> </td>
      </tr>
      <tr>
      <td>Specialaftale: </td><td> <input size="20" id="pt_special_no"  type="text" ></td>
      </tr>
      <tr>
      <td>Budget: </td><td><input id="pt_pris_no" type="text"></td>
      </tr>
      <tr>
      <td>Vejl. udsalgspris: </td><td><input id="pt_budget_no" type="text"></td>
      </tr>
      </tbody></table>
    </fieldset>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" id="presentUpdateSetting" class="btn btn-primary">Gem indstillinger</button>
      </div>
    </div>
  </div>
</div>