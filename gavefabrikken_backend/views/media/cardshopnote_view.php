<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
<script src="views/js/main.js"></script>
<script src="views/js/cardshopnote.js"></script>
<div id="cardshopnote" style="font-size: 12px;">
<div style="display: inline-block; height: 400px; width: 300px; margin-right: 70px; ">
  <fieldset>
    <legend>Special aftaler fra <b>ordre</b>:</legend>
    <div id="spDeal" style="height: 180px; overflow-y:auto;">

    </div>
  </fieldset>

  <fieldset >
    <legend>Special aftaler <b>tillæg</b>:</legend>
    <div  style="height:230px; overflow-y:auto;">
        <textarea style="width: 100%; height: 100%" id="rapport_note"></textarea>
    </div>
    <button  onclick="shopNote.saveRapportNote()">Gem tillæg</button>
  </fieldset>
</div>
<div  style="display: inline-block; height: 800px; width: 300px;">
      <fieldset>
    <legend>INTERNE NOTER:</legend>
    <div style="height:450px; overflow-y:auto;">
        <textarea style="width: 100%; height: 100%" id="internal_note"></textarea>
    </div>
    <button onclick="shopNote.saveInternalNote()">Gem Noter</button>
  </fieldset>
</div>
</div>