<!-- Modal -->
<div class="modal fade" id="newPresentation" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
  aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Opret ny præsentation</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div style="color:red;" id="newPresentationError"></div>
        <label>Navn på præsentation </label><input style="margin-left:10px; width: 300px;" id="newPresentationName" type="text" />
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" id="createNewPresentation" class="btn btn-primary">Gem presentation</button>
      </div>
    </div>
  </div>
</div>