<style>
.modal-body, .modal-footer{
    background-color: white;
}
#saleperson-select-list{
  display: none;
}
#updatePresentation{
  display:none;
}
.edit-allprice{
  display: none;
}
.presentation-elememt-set{
  cursor: grab;
}

</style>
<div class="modal fade right" id="cart" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
  aria-hidden="true">

  <!-- Add class .modal-full-height and then add class .modal-right (or other classes from list above) to set a position to the modal -->
  <div class="modal-dialog modal-full-height modal-right" role="document">


    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title w-100" id="myModalLabel">Kurv og PDF generering</h4>


        <button type="button" class="close " data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" style="overflow: scroll;">
          <div id="cart-presentation-name2"></div>
          <div id="cart-presentation-link"></div>

          <i class="fas fa-file-download fa-2x presentation-set-build" title="Download pdf dokument"></i>
          <img id="busy-fa-file-download" src="../presentsCms/image/jobsloding.gif" height="50" alt="" />
          <i class="fas fa-dollar-sign fa-2x presentation-multi-price" style="margin-left:10px;"></i>
          <i class="fas fa-clone fa-2x presentation-copy" data-target="#copyModal" data-toggle="modal" ></i>
         <br><br>


       <div class="accordion" id="accordionpdfPrintOpions" >
  <div class="card z-depth-0 bordered">
    <div class="card-header" id="headingOne">
      <h5 class="mb-0">
        <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#pdfPrintOpions"
          aria-expanded="true" aria-controls="pdfPrintOpions">
          PDF indstillinger
        </button>
      </h5>
    </div>
    <div id="pdfPrintOpions" class="collapse " aria-labelledby="headingOne"
      data-parent="#accordionpdfPrintOpions">
      <div class="card-body salepersonList">

      </div>
    </div>

  </div>

</div>

          <br>
        <div id="saleperson-select-list"></div>

   
<br><br>
<div class="presentation-set">
            <ul id="sortable"></ul>

        </div>
      </div>
      <div class="modal-footer justify-content-center">
        <button  type="button" class="btn btn-secondary closeCart" data-dismiss="modal">Luk</button>
        <button  type="button" class="btn btn-secondary closePresentation" data-dismiss="modal">Luk Pr&aelig;sentation</button>
         <button type="button" class="btn btn-primary" id="createPresentation" data-toggle="modal" data-target="#newPresentation">
           Opret ny presentation
         </button>
        <button type="button" id="updatePresentation" class="btn btn-primary" data-toggle="modal" >
            Opdater
        </button>

      </div>
    </div>
  </div>
</div>