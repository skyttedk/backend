<div class="modal fade right" id="itemNumberModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
  aria-hidden="true">

  <!-- Add class .modal-full-height and then add class .modal-right (or other classes from list above) to set a position to the modal -->
  <div class="modal-dialog modal-full-height modal-right" role="document">


    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title w-100" id="myModalLabel">Varenummer</h4>


        <button type="button" class="close " data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="itemNumberModal-body" style="overflow: scroll;">

      </div>
      <div class="modal-footer justify-content-center">
        <button  type="button" class="btn btn-secondary closeCart" data-dismiss="modal">Luk</button>


      </div>
    </div>
  </div>
</div>


<!-- Central Modal Small -->
<div class="modal fade" id="archiveModal" tabindex="-1" role="dialog" aria-labelledby="modalChangeMultiPrice"
     aria-hidden="true">

    <!-- Change class .modal-sm to change the size of the modal -->
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title w-100" id="myModalLabel">Alle Oplæg</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body ">
                <center>
                  <input id="archiveTxt" type="text" /><button id="archiveListSearch">Søg</button><button id="archiveListSearchAll">Vis alle</button> <br><br>
                   <div id="archiveList"></div>
                </center>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- Central Modal Small copy -->
<div class="modal fade" id="copyModal" tabindex="-1" role="dialog" aria-labelledby="modalChangeMultiPrice"
     aria-hidden="true">

    <!-- Change class .modal-sm to change the size of the modal -->
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title w-100" id="myModalLabel">Kopiere Oplæg</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body ">
                <center>
                    <div style="color:red;" id="newPresentationError"></div>
                    <label>Navn på præsentation </label><input style="margin-left:10px; width: 300px;" id="newCopyPresentationName" type="text">

                </center>
            </div>
            <div class="modal-footer">
                <button type="button" id="closeCopyModal" class="btn btn-secondary waves-effect waves-light" data-dismiss="modal">Close</button>
                <button type="button" id="createPresentationCopy" class="btn btn-primary waves-effect waves-light">Gem presentation</button>
            </div>
        </div>
    </div>

</div>




