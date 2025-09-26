

<?php
    echo "<script src='views/js/valgshop-gaver.js?".rand(10,1000)."'></script>";
    echo "<script src='views/js/valgshop-gaver-custom-receipt.js?".rand(10,1000)."'></script>";
    echo "<script src='views/js/valgshop-gaver-sync.js?".rand(10,1000)."'></script>";
    echo "<script src='views/js/copyshop.js?".rand(10,1000)."'></script>";
    echo "<link href='views/css/valgshop-gaver.css?".rand(10,1000)."' rel='stylesheet'>";

    if($hideForsaleperson != ""){
        echo "
        <style>
        .salemane-noshow{
            display: none !important;
        }       
        </style>
        ";
    } else {
        echo "
        <style>
        .salemane-only-show{
            display: none !important;
        }       
        </style>
        ";
    }
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
        if(_editShopID == 4299){
            $(".ulle").show()
        }
 }



</script>
<div id="vsg-main">
    <center>
     <div id="vsg-main-menu" style=" text-align: center;">
     <table width="100%" border="0" >
        <tr>

 <td >
     <div>

         <input type="radio" id="visAlle" name="countrySearch" value="searchAll" checked>
         <label for="visAlle" style="margin-right: 20px;">Vis alle</label>

         <input type="radio" id="visKunNo" name="countrySearch" value="searchNO">
         <label for="visKunNo" style="margin-right: 20px;">Vis kun NO</label>

         <input type="radio" id="visKunDk" name="countrySearch" value="searchDK">
         <label for="visKunDk" style="margin-right: 20px;">Vis kun DK</label>
     </div>

     <input placeholder="Søg" type="text" size="25" style="height:20px;" id="vsg-search" onclick="" />
     <select  name="pim-budget-budget" id="pim-budget-budget" style="margin: 5px;padding: 5px; font-size: 10px;">
         <option value="none">Intet budget valgt</option>
    </select> <button class="button" onclick="valgshopGaver.searchResetFields()" style="color: #d12b1a; font-size:0.9em; height:31px; margin-left: 10px;  ">Nulstil</button><button class="button " onclick="valgshopGaver.initSearch()" style="font-size:0.9em; height:31px; ">S&oslash;gning</button><br>
     <div  style=""><span>Fra/til pris: </span><input type="number" style="width: 80px;" placeholder="Kostpris fra" min="0" id="kostpris-start"   /> <input style="width: 80px;" placeholder="Kostpris til" type="number" min="0" id="kostpris-end" />
     <span> | Brands:</span><input id="showBrands" type="checkbox"> <span> | Lagerantal: </span> <input  id="searchStockMin" type="number" placeholder="Minimum" style="width: 70px;" />
     </div><br>

    </td>
 </tr>
 </table>
     </div>
    </center>
    <br> <br>
     <div id="vsg-content">
     <table>
     <tr>
     <td id="vsg-content-data">

     </td>
         <td id="vsg-content-data">
             <button id="loadMorePresent" >Indlæs flere</button>
         </td>
     </tr>
     </table>

     </div>

 <!--   <button id="vsg-lasyload" onclick="valgshopGaver.load()">Hent flere gaver</button>  -->
</div>

<div id="vsg-leftpanel">
    <div id="vsg-leftpanel-menu">
      <button class="button salemane-noshow" onClick="openGaveAlias(_editShopID )">Gave alias</button>
        <button  onclick="copyShop.init(_editShopID )" class="button button2 "  title="Kopi gaver" >Kopi gaver</button>
      <button  onclick="openGaveEjvalgte(_editShopID )" class="button button3 salemane-noshow"  title="Vælg gave til dem der ikke har valgt" >Ejvalgte</button>
      <button  onclick="syncShopPresent(_editShopID )" class="button button1 salemane-noshow" title="Synkronisering med standard gaven i gaveadmin" >Sync</button>
      <button  onclick="downloadShopPresent(_editShopID )" class="button button2 " title="Download liste med alle valgte gaver">Download</button>
        <!-- valgshop-gaverdownload_view.php -->
        <button  onclick="ShopPresentStrength(_editShopID )" class="button button2 ">Styrker</button>
        <button  onclick="syncGetUpdateCount(_editShopID )" class="button button2 ulle" style="display: none">Varenummer</button>
        <button  onclick="ShowOrdredataMedal(_editShopID )" class="button button2 ">Ordredata</button>
        <button  onclick="ShowReservationdataMedal(_editShopID )" class="button button2 ">Reservation</button>
        <button  onclick="ShowsCategoriMedal(_editShopID )" class="button button2 ">Kategori</button>
        <button  onclick="ShowDbCalcModal(_editShopID )" class="button button2 ">DB</button>
        <br>
      <!--  <button style="display: none; color: red;" class="button update_to_template_4" >Opdatere alle gaver til ny Skabelon</button> -->
    </div>
    <div id="vsg-leftpanel-content" style="overflow-y: scroll;">
        <div id="vsg-inShopPresent-tab">
        <ul>
          <li><a href="#vsg-tabs-1" >Aktive</a></li>
          <li class="salemane-noshow"><a href="#vsg-tabs-2" onclick="valgshopGaver.showDeactivedPresents()">De-aktiv</a></li>
          <li  class=""><a href="#vsg-tabs-3" onclick="valgshopGaver.showDeletePresents()">Slettede</a></li>
<!--          <li><a href="#vsg-tabs-4">Log</a></li>  -->
        </ul>
        <div id="vsg-tabs-1" class="scrollInShopGave">
            <div id="vsg-active-present" class="scroll"></div>
        </div>
        <div id="vsg-tabs-2" class="scrollInShopGave salemane-noshow">
              <div id="vsg-deactive-present"  class="scroll"></div>
        </div>
        <div id="vsg-tabs-3" class="scrollInShopGave ">
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
<div id="copyshopModal"></div>
<script>
    function ShowsCategoriMedal(shopID){
        $("#ejvalgteSaveStatus").html("");

        $('#gaveEjValgteDialog').html('<iframe  width="100%" style="height: 99%" src="https://system.gavefabrikken.dk//gavefabrikken_backend/index.php?rt=unit/valgshop/categories/show&shopID='+_editShopID+'"></iframe>');
        let w =  $(window).width() > 800 ? 800: $(window).width()
        $('#gaveEjValgteDialog').dialog({
            title: 'Kategorier',
            modal: true,
            width: w,
            height: $(window).height()-200,
            buttons: {
                Luk: function() {
                    $(this).dialog("close");
                }
            },
            open: function() {
                // Dette fjerner padding og marginer, så dialogen virkelig bliver fuldskærm
                $('.ui-widget-overlay').css('padding', 0);
                $('.ui-widget-overlay').css('margin', 0);
                $('.ui-dialog').css('padding', 0);
                $('.ui-dialog').css('margin', 0);
            }
        });
    }

    function ShowOrdredataMedal(shopID)
    {
        /*
        if(shopID != 6490){
            alert("Order data er ved at blive opdateret")
            return;
        }


         */
        let localisation = $('input[name="localisation"]:checked').attr("data-id");
        $("#ejvalgteSaveStatus").html("");

        $('#gaveEjValgteDialog').html('<iframe id="shopdata" width="100%" style="height: 99%" src="https://system.gavefabrikken.dk//gavefabrikken_backend/index.php?rt=unit/valgshop/main&saleperson=1&shopID='+_editShopID+'&localisation='+localisation+'"></iframe>');
        let w =  $(window).width() > 800 ? 800: $(window).width()
        $('#gaveEjValgteDialog').dialog({
            title: 'Ordredata',
            modal: true,
            width: w,
            height: $(window).height()-200,
            buttons: {
                Luk: function() {
                    $(this).dialog("close");
                    location.reload();
                }
            },
            open: function() {
                // Dette fjerner padding og marginer, så dialogen virkelig bliver fuldskærm
                $('.ui-widget-overlay').css('padding', 0);
                $('.ui-widget-overlay').css('margin', 0);
                $('.ui-dialog').css('padding', 0);
                $('.ui-dialog').css('margin', 0);
            },
            close: function() {
                location.reload(); // Genindlæser siden når dialogen lukkes, uanset hvordan
            }
        });
    }
    function ShowReservationdataMedal(shopID)
    {
        $("#ejvalgteSaveStatus").html("");

        $('#gaveEjValgteDialog').html('<iframe id="shopdata" width="100%" style="height: 99%" src="https://system.gavefabrikken.dk//gavefabrikken_backend/index.php?rt=unit/valgshop/main&saleperson=2&shopID='+_editShopID+'"></iframe>');
        let w =  $(window).width() > 800 ? 800: $(window).width()
        $('#gaveEjValgteDialog').dialog({
            title: 'Reservation',
            modal: true,
            width: w,
            height: $(window).height()-200,
            buttons: {
                Luk: function() {
                    $(this).dialog("close");
                }
            },
            open: function() {
                // Dette fjerner padding og marginer, så dialogen virkelig bliver fuldskærm
                $('.ui-widget-overlay').css('padding', 0);
                $('.ui-widget-overlay').css('margin', 0);
                $('.ui-dialog').css('padding', 0);
                $('.ui-dialog').css('margin', 0);
            }
        });
    }
function ShowDbCalcModal(shopID)
{
    $("#ejvalgteSaveStatus").html("");

    $('#gaveEjValgteDialog').html('<iframe id="dbcalc" width="100%" style="height: 99%" src="https://system.gavefabrikken.dk//gavefabrikken_backend/index.php?rt=unit/valgshop/contributionmargin/view&shopID='+_editShopID+'&localisation=1"></iframe>');

    $('#gaveEjValgteDialog').dialog({
        title: 'DB Calc',
        modal: true,
        width: $(window).width() * 0.9,
        height: $(window).height() * 0.9,
        buttons: {
            Luk: function() {
                $(this).dialog("close");
            }
        },
        open: function() {
            // Centrer modal med 5% padding
            var dialog = $(this).parent();
            dialog.css({
                position: 'fixed',
                top: '5%',
                left: '5%'
            });
        }
    });
}




</script>