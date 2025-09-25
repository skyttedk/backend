<link rel="stylesheet" type="text/css" href="views/css/ptAdmin.css?v=<?php echo rand(0, 100); ?>">
<center>
     <button id="pt-preview">PREVIEW</button>
    <button style="background-color: #4CAF50; margin-left:20px;" id="pt-save">GEM</button>
</center>


<div class="pt-admin">
  <div class="left">
  <br>
      <fieldset>
      <legend>Pr&oelig;sentationsbillede (Det store)</legend>
        <form class="uploadFile" enctype="multipart/form-data">
        <input  name="file" type="file" id="file-big" />
        <input  id="pt-upload" type="button" value="Upload billedet" />   <progress  id="pt-progress"></progress>
        </form>

      <div class="img-container"></div>
    </fieldset>
    <hr>
   <fieldset>
      <legend>Ekstra billede (Det lille) </legend>
        <form class="uploadFileSmall" enctype="multipart/form-data">
        <div><input type="button" value="Slet billedet" id="pt_delete_small_img" /> </div>
        <hr>
        <input  name="file" type="file" id="file-small" />
        <input  id="pt-uploadSmall" type="button" value="Upload billede" />   <progress  id="pt-progress-small"></progress>
        </form>

      <div class="img-container-small"></div>
    </fieldset>
  </div>
  <div class="right">


    <br>
    <fieldset>
      <legend>Skabelon</legend>
      <div class="layout"></div>
    </fieldset>

    <fieldset>
      <legend>Logo</legend>
      <input type="checkbox" id="kunhos"> <label > Vis 'Kunhos' logo  </label><br>
    <!--  <input type="checkbox" id="omtanke"><label > Vis 'Omtanke' logo </label>  -->
    </fieldset>

    <fieldset>
      <legend>Priser DANMARK</legend>
      <table>
      <tr>
      <td>Specialaftale: </td><td>SPECIALAFTALE <input size="20"  id="pt_special" type="text"  /></td><td>Vis: <input type="checkbox" id="pt_special_show"></td>
      </tr>
      <tr>
      <td>Budget: </td><td><input id="pt_pris" type="text" /></td><td>Vis: <input type="checkbox" id="pt_pris_show"></td>
      </tr>
      <tr>
      <td>Vejl. udsalgspris: </td><td><input id="pt_budget" type="text" /></td><td>Vis: <input type="checkbox" id="pt_budget_show"></td>

      </tr>
      </table>
    </fieldset>
        <fieldset>
      <legend>Priser NORGE</legend>
      <table>
      <tr>
      <td>Specialaftale: </td><td>SPECIALAFTALE <input size="20"  id="pt_special_no" type="text"  /></td><td>Vis: <input type="checkbox" id="pt_special_show_no"></td>
      </tr>
      <tr>
      <td>Budget: </td><td><input id="pt_pris_no" type="text" /></td><td>Vis: <input type="checkbox" id="pt_pris_show_no"></td>
      </tr>
      <tr>
      <td>Vejl. udsalgspris: </td><td><input id="pt_budget_no" type="text" /></td><td>Vis: <input type="checkbox" id="pt_budget_show_no"></td>

      </tr>
      </table>
    </fieldset>
  </div>

</div>
<div id="ptDialog" title="" style="display:none; ">

</div>
<script src="views/js/ptAdmin.js?v=<?php echo rand(0, 100); ?>"></script>


