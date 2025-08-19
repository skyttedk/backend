

<?php
    echo "<script src='views/js/valgshop-gaver.js?".rand(10,1000)."'></script>";
    echo "<script src='views/js/valgshop-gaver-custom-receipt.js?".rand(10,1000)."'></script>";
    echo "<link href='views/css/valgshop-gaver.css?".rand(10,1000)."' rel='stylesheet'>";
?>

<script>
var valgshopGaver;
var customReceipt;
    function valgshopGaverTab()
    {
        if($("#shopDescriptionTabs").find(".mce-panel").length != 0){
            tinymce.remove()
        }
       
        if (typeof valgshopGaver === 'undefined') {
            valgshopGaver = new classValgshopGaver();
            valgshopGaver.init();
        }
        if (typeof customReceipt === 'undefined') {
            customReceipt = new classValgshopcustomReceipt();

        }
 }



</script>
<div id="vsg-main">
     <div id="vsg-main-menu">
     <table width="100%" border="0" >
        <tr>

 <td style=" text-align: center;">
    <input type='radio' NAME="vsg-gavetype" value="standard" checked><label>Standard</label> <input NAME="vsg-gavetype" value="variant" type='radio'><label style='margin-right:5px;'>Variant</label>
    <input type="text" size="35" style="height:25px;" id="vsg-search" onclick="" /><button onclick="valgshopGaver.initSearch()" style="font-size:0.9em; height:31px;  ">S&oslash;g</button>
    </td>
 </tr>
 </table>
     </div>
     <div id="vsg-content">
     <table>
     <tr>
     <td id="vsg-content-data">

     </td>
     </tr>
     </table>

     </div>
 <!--   <button id="vsg-lasyload" onclick="valgshopGaver.load()">Hent flere gaver</button>  -->
</div>

<div id="vsg-leftpanel">
    <div id="vsg-leftpanel-menu">
      <button class="button" onClick="openGaveAlias(_editShopID )">Gave alias</button>
      <button  onclick="openGaveEjvalgte(_editShopID )" class="button button3">Gave til ejvalgte</button>
      <button  onclick="syncShopPresent(_editShopID )" class="button button1">Synkronisering</button>
    </div>
    <div id="vsg-leftpanel-content" style="overflow-y: scroll;">
        <div id="vsg-inShopPresent-tab">
        <ul>
          <li><a href="#vsg-tabs-1" >Aktive</a></li>
          <li><a href="#vsg-tabs-2" onclick="valgshopGaver.showDeactivedPresents()">De-aktiv</a></li>
          <li><a href="#vsg-tabs-3" onclick="valgshopGaver.showDeletePresents()">Slettede</a></li>
<!--          <li><a href="#vsg-tabs-4">Log</a></li>  -->
        </ul>
        <div id="vsg-tabs-1" class="scrollInShopGave">
            <div id="vsg-active-present" class="scroll"></div>
        </div>
        <div id="vsg-tabs-2" class="scrollInShopGave">
              <div id="vsg-deactive-present"  class="scroll"></div>
        </div>
        <div id="vsg-tabs-3" class="scrollInShopGave">
               <div id="vsg-deleted-present"  class="scroll"></div>
        </div>
        <div id="vsg-tabs-4">

        </div>
      </div>




    </div>

</div>
    <div id="vsgEditBox">

        <div id="vsgEditBoxHead">
        <table width=100% border=0>
        <tr>
        <td width="30%"><h3>Redigere gave</h3></td>
        <td width="40%"></td>
<td width="10%"> <button onclick=" valgshopGaver.closeEdit()  ">TILBAGE</button> </td>
         <td width="5%" id="vsgSavePresentBtn">  </td>
       <!-- <td width="5%">sdg <img style="cursor: pointer;" onclick="valgshopGaver.closeEdit()" src="views/media/icon/1373253296_delete_64.png" height="25" width="25" alt="" /> </td> -->
        </tr>

        </table>
        <h3></h3></div>
        <div id="vsgEditBoxContent"></div>
    </div>
<!--
<div id="vsg-present-info"></div>
  -->
<div id="dialog-other-shop" title="Shops hvor gaven er benyttet">
</div>
<div id="dialog-sync" title="Synkronisering med mastergaven">
    <div id="PimShopPresentSync"></div>
</div>
<div id="dialog-custom-receipt" title="V&oelig;lg tekst til kvittering ">
<table width=100%>
<tr>
<td width=50% id="dialog-custom-receipt-left"></td>
<td width=50% id="dialog-custom-receipt-right" align="center" valign="top"></td>
</tr>

</table>

</div>

