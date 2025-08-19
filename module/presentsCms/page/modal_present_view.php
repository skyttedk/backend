<style>
.modalPresent input{
  width: 300px;
}
.modalPresent table, .modalPresent td, .modalPresent th {
  border: 1px solid #ddd;
  text-align: left;
}

.modalPresent table {
  border-collapse: collapse;
  width: 100%;
}

.modalPresent th, .modalPresent td {
  padding: 15px;
}
.modalPresent #pt-progress{
  display: none;
}
.modalPresent #pt-progress-small{
  display: none;
}
.img-container  {
    height: 100px;
    background-size: contain;
    background-repeat: no-repeat;
    text-align: center;
    border-top:1px solid black;
    padding:10px;
    margin-top:10px;

}
.img-container-small{
    height: 100px;
    background-size: contain;
    background-repeat: no-repeat;
    text-align: center;
    border-top:1px solid black;
    padding:10px;
    margin-top:10px;

}
.modalPresent .error{
  border:1px solid red;
  background-color: red;
}

</style>
<div class="modal fade top  " id="modalPresentView" tabindex="-1" role="dialog" aria-labelledby="modalPresent"
  aria-hidden="true">

  <!-- Add class .modal-full-height and then add class .modal-right (or other classes from list above) to set a position to the modal -->
  <div class="modal-dialog modal-full-height modal-top w-100" role="document">


    <div class="modal-content ">
      <div class="modal-header">
        <h4 class="modal-title w-100" id="modalPresent">GAVEADMINISTATION</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body modalPresent">
      <fieldset>
      <legend><b>Generelle informationer</b></legend>
      <table>
        <tr><td id="nav">NAV Varenavn:</td><td><input class="nav_name" type="text" /></td></tr>
        <tr><td>Kostpris fra Navision:</td><td><input class="prisents_nav_price" type="number" min="0" /></td></tr>
        <tr><td>Leverandør / Mærke:</td><td><input class="vendor" type="text" /></td></tr>


        <tr><td>Specialaftale pris:</td><td><input class="special" type="number" min="0" /></td></tr>
        <tr><td>Budget:</td><td><input class="budget1" type="number" min="0" /></td></tr>
        <tr><td>Vejl. udsalgspris:</td><td><input class="pris" type="number" min="0" /></td></tr>


        <tr><td>Vis øko gave logo:</td><td> <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" id="oko_present">
                <label class="custom-control-label" for="oko_present"></label>
                </div></td>
        </tr>
        <tr><td>Vis Kun hos logo:</td><td> <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input" id="kunhos">
                <label class="custom-control-label" for="kunhos"></label>
                </div></td>
        </tr>
      </table>
      </fieldset>
      <br>
      <fieldset>
      <legend><b>Produkt beskrivelse</b></legend>
      <table>
      <tr><td>Overskrift:</td><td><input class="caption" type="text" /></td></tr>
      <tr><td>Kort beskrivelse:</td><td><textarea class="shortDescription" id="shortDescription"></textarea></td></tr>
      <tr><td>Detaljeret beskrivelse:</td><td><textarea class="detailDescription" id="detailDescription"></textarea></td></tr>
      </table>
      </fieldset>
      <br>
      <div>
      <fieldset>
      <legend><b>Skabelon / billeder</b></legend>
      <table>
      <tr><td id="skabelon">Skabelon:</td><td>
      <center>
      <table  style="width: 300px;">
      <tr >
        <td><img src="image/layout1.jpg" alt="" width="150" /></td><td><img src="image/layout2.jpg" alt="" width="150" /></td>
      </tr>
      <tr >
        <td><input type="radio" id="layout_1" name="layoutSelect" value="1"></td>
        <td><input type="radio" id="layout_2" name="layoutSelect" value="2"></td>
      </tr>

      </table>
      </center>


      </td></tr>
      <tr><td id="bigPresentation">Præsentationsbillede (Det store):</td><td>
        <form class="uploadFile" enctype="multipart/form-data">
            <input  name="file" type="file" id="file-big" />
            <input  id="pt-upload" type="button" value="Upload billedet" />   <progress  id="pt-progress"></progress>
        </form>
        <div class="img-container"></div>
        </td>
      </tr>
      <tr><td>Ekstra billede (Det lille):</td><td>
          <form class="uploadFileSmall" enctype="multipart/form-data">
          <div><input type="button" value="Slet billedet" id="pt_delete_small_img" /> </div>
          <hr>
          <input  name="file" type="file" id="file-small" />
          <input  id="pt-uploadSmall" type="button" value="Upload billede" />   <progress  id="pt-progress-small"></progress>
          </form>
          <div class="img-container-small"></div>
      </td></tr>
      </table>
      </fieldset>
      </div>
      </div>   
      <div class="modal-footer justify-content-center">
        <button type="button" class="btn btn-secondary"  data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="presentAdminUpdata">Gem / opdatere</button>
      </div>

  </div>
   </div>
</div>