<style>
.earlorder-quick-text label{
  width: 250px;
  display:inline-block;
}
.earlorder-quick-text:hover{
  background-color: white;
}

</style>

<div id="dialog_message_AddNewCard" class="table1" title="" style="display:none;">
  <fieldset>
    <legend>Gavekort</legend>
    <h3 style="color:red" id="cardAmountInShop"></h3>
    <select class="formGavekort" onchange="cardAddNewCard.menuGaveCard(this.options[this.selectedIndex].value)">
        <option disabled="" selected="" value="0">V&oelig;lg</option>
        <option value="52">Julegavekort</option>
        <option value="24Gaver">24Gaver</option>
        <option value="53">Guldkort</option>
        <option value="onlyDrom">Dr&oslash;mmegavekortet</option>
        <option value="575">designjulegaven</option>
        <option value="norge">julegavekort NORGE</option>
        <option value="574">Gullgavekortet (norge)</option>
        <option value="1832">24 Julklappar-360 (se)</option>
        <option value="1981">24 Julklappar-800 (se)</option>
    </select>
  </fieldset>
  <fieldset style="display:none" class="only24">
    <legend>V&oelig;lg v&oelig;rdi</legend>
    <select class="formSelectValue1" onchange="cardAddNewCard.menuValueCard(this.options[this.selectedIndex].value)">
        <option disabled="" selected="" value="0">V&oelig;lg</option>
        <option value="54">400 kr.</option>
        <option value="55">560 kr.</option>
        <option value="56">640 kr.</option>
    </select>
  </fieldset>
  <fieldset style="display:none" class="onlyNorge">
    <legend>V&oelig;lg v&oelig;rdi</legend>
    <select class="formSelectValue2" onchange="cardAddNewCard.menuValueCard(this.options[this.selectedIndex].value)">
        <option disabled="" selected="" value="0">V&oelig;lg</option>
        <option value="272">300 kr.</option>
        <option value="57">400 kr.</option>
        <option value="58">600 kr.</option>
        <option value="59">800 kr.</option>
    </select>
  </fieldset>
    <fieldset style="display:none" class="onlyDrom">
    <legend>V&oelig;lg v&oelig;rdi</legend>
    <select class="formSelectValue3" onchange="cardAddNewCard.menuValueCard(this.options[this.selectedIndex].value)">
        <option disabled="" selected="" value="0">V&oelig;lg</option>
        <option value="290">200 kr.</option>
        <option value="310">300 kr.</option>
    </select>
  </fieldset>



  <fieldset style="display:none" class="cardFormDeadline">
    <legend>V&oelig;lg Gavevalg deadline</legend>
    <select class="formDeadline" onchange="cardAddNewCard.menuDeadlineCard(this.options[this.selectedIndex].value)">
        <option disabled=""
        selected="" value="0">V&oelig;lg</option>

      <option style="display:none" class="deadline_groupe_1 deadline_groupe" value="2020-11-01">Uge 48</option>
          <option  class="deadline_groupe_1 deadline_groupe" value="2020-11-15">Uge 50</option>
        <option class="deadline_groupe_1 deadline_groupe " value="2020-11-29">Uge 51 (kun jgk) </option>
        <option class="deadline_groupe_1 deadline_groupe" value="2020-12-31"> Uge 4 (2021)</option>

        <option  class="deadline_groupe_2 deadline_groupe" value="2020-11-08">Uge 49</option>
        <option  class="deadline_groupe_2 deadline_groupe" value="2020-11-22">Uge 51</option>
        <option  class="deadline_groupe_2 deadline_groupe" value="2020-12-31">Uge 4 (2021)</option>

        <option style="display:none"  class="deadline_groupe_3 deadline_groupe" value="2020-11-01">Uge 48</option>
        <option  class="deadline_groupe_3 deadline_groupe" value="2020-11-15">Uge 50</option>
        <option class="deadline_groupe_3 deadline_groupe" value="2020-12-31">Uge 4 (2021)</option>
        <option class="deadline_groupe_3 deadline_groupe" value="2020-11-07">2020-11-07 (Hjemmelevering kun e-mail kort)</option>
        <option class="deadline_groupe_3 deadline_groupe" value="2021-01-03">2021-01-03 (Hjemmelevering kun e-mail kort)</option>

        <option style="display:none" class="deadline_groupe_5 deadline_groupe" value="2020-11-01">Uge 48</option>
        <option  class="deadline_groupe_5 deadline_groupe" value="2020-11-15">Uge 50</option>
        <option class="deadline_groupe_5 deadline_groupe" value="2020-12-31">Uge 4 (2021)</option>
        <option class="deadline_groupe_5 deadline_groupe" value="2020-11-07">2020-11-07 (Hjemmelevering kun e-mail kort)</option>
        <option class="deadline_groupe_5 deadline_groupe" value="2021-01-03">2021-01-03 (Hjemmelevering kun e-mail kort)</option>


        <option class="home" style="display:none;" value="2021-04-01">Gaven leveres hjemme</option>

        <option  class="deadline_groupe_4 deadline_groupe" value="2020-11-08">Uge 49</option>
        <option  class="deadline_groupe_4 deadline_groupe" value="2020-11-22">Uge 51</option>
        <option  class="deadline_groupe_4 deadline_groupe" value="2020-12-31">Uge 4(2021)</option>
        <option  class="deadline_groupe_4 deadline_groupe" value="2021-12-31">Gaven leveres hjemme</option>
    </select>
  </fieldset>
  <fieldset style="display:none" class="cardFormNumber">
    <legend>Antal</legend>
        <input type="number" class="cardToSelect"  />
           <div class="showNorge">

    </div>
  </fieldset>

  <fieldset style="display:none" class="cardFormToSendMethod">
    <legend>Forsendelse metode</legend>
    <input type="radio" name="cardFormToSendMethod" value="0" > Fysisk<br>
    <input type="radio" name="cardFormToSendMethod" value="1"> Mail

  </fieldset>
  <fieldset >
    <legend>S&oelig;lger</legend>
      <input id="saleperson" type="text" size="35" />
  </fieldset>

  <fieldset id="giftwrapContainer" >
    <legend>Tilvalg gaveindpakning ny</legend>
      <input id="giftwrap" class="giftnuskaldetvirke" type="checkbox">
  </fieldset>
   <fieldset id="giftSpecialDev" >
    <legend>Tilvalg opb&oelig;ring</legend>
      <input id="giftSpeLev" class="giftSpeLev"  type="checkbox">
  </fieldset>
<!--
     <fieldset class="cardAccess"  >

   <legend>Tilvalg: fragt </legend>
      Tilvalg: <input id="freeDelivery" class="freeDelivery "  type="checkbox"> Bel&oslash;b:
      <input type="number" id="cardFormShipping" />
  </fieldset>
 -->

  <fieldset >
    <legend>Interne noter</legend>
      <textarea id="cardFormSaleperson" rows="4" cols="50"></textarea>
  </fieldset>
    <fieldset >
    <legend>Leveringsaftaler</legend>
      <textarea id="sp" rows="4" cols="50"></textarea>
  </fieldset>
  <hr />
  <div id="earlyGift">
  <label>Tilf&oslash;j early gave</label><input id="earlyShow" type="checkbox" onclick="cardAddNewCard.showEarly()">
  </div>
  <fieldset id="earlyGiftWrapper" style="display:none;">
    <legend>Early gave</legend>
      <div id="earlyorderQuickList"></div>
      <textarea id="earlyorderList" rows="4" cols="50"></textarea>
  </fieldset>

    <hr />
    <div id="cardToMultibleAdd">
    <label>Tilf&oslash;j kort til flere leveringsadresser</label><input id="useMultibleAddr" type="checkbox" onclick="cardAddNewCard.showMultibleAddr()">
    </div>
  <fieldset id="cardToMultibleAddrWrapper" style="display:none;">
    <legend>Leveringsadresse</legend>
      <div id="cardToMultibleAddrList"></div>
  </fieldset>
  <hr />


  <button style="display:none; color:red;  font-size: 14px;"  class="cardAddNewCard" onclick="cardAddNewCard.selectCardFromPool()">Opret kort med faktura</button>


   <button style="display:none; float: right; font-size: 12px;"  class="cardAddNewCard cardAccess nobill" onclick="cardAddNewCard.selectCardFromPoolNoNAV()">Opret kort UDEN faktura</button>



</div>