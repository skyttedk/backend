window.BASEURL = "https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/";
var JSBASEURL = "https://system.gavefabrikken.dk/gavefabrikken_backend/units/pim/";
window.LANGUAGE = "";
window.VERSION = "1.1.1";
window.USERID = USERID;
window.SHOPID = SHOPID;
import Base from '../../main/js/base.js';

export default class SyncMain extends Base {
    constructor() {
        super();
        this.init();
    }
    init(){
        this.layout();
        this.datepicker();
        this.event();
    }
    async event(){
        let self = this;
        $("#btn-sync").unbind("click").click(
            function(){

                self.doSync();
            }
        )
        $("#btn-getSyncList").unbind("click").click(
            function(){
                self.showSyncItemList();
            }
        )
        $("#btn-sync-logo").unbind("click").click(
            function(){
                self.doSyncLogo();
            }
        )
        $("#btn-gavevalg").unbind("click").click(
            function(){
                self.gavevalg();
            }
        )
        $("#btn-syncLog").unbind("click").click(
            function(){
                self.loadSyncLog();
            }
        )
        $("#btn-newNAVItems").unbind("click").click(
            function(){
                self.loadNewNAVItems();
            }
        )


    }
    setSyncMergeEvent()
    {
        let self = this;
        $(".sync-merge-pim").unbind("click").click(
        function(){
            let r = confirm("Udfør sammen fletningen af varen i PIM")
            if(r){
                self.doMerge(
                    $(this).attr("pimItemNr"),
                    $(this).attr("kontainerID")
                )
            }

        })
    }
    async doMerge(navItemNo,kontainerID){

        $("#top-panel").slideUp(300);
        $(".sync-main-container-out").html("Opdaterer");
        let self = this;
        let postValue = {
            "navItemNo":navItemNo,
            "kontainerID" :kontainerID
        }
        $.post( " https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/pim/sync/doMergeNavElementToPim", postValue,function( data ) {
            alert("Varen er importeret")
            self.loadNewNAVItems();
            //  location.reload();
        });
    }




    async loadNewNAVItems(){
        let self = this;
        let itemno = $("#newNAVSingleItem").val();
        $.post( " https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/pim/sync/getNavList", {itemno:itemno},function( data ) {
            self.buildNewNavItemsTemplate(data);
        });
    }Q
    buildNewNavItemsTemplate(data){

        let tdata = JSON.parse(data);
        let html = "<table><tr><th>Dato</th><th>Varenr</th><th>NAV beskrivelse</th><th>knap</th><th>knap</th></tr>"
        tdata.total.forEach((element) => {
            if(element.state == 1) {
                html+= `<tr><td>${element.dato}</td><td class="sync-itemno">${element.itemno}</td><td>${element.description}</td><td><input type="text" value="" class="sync-pim-no"> <button pim-itemnr="${element.itemno}" class="sync-check-pim">Tjek PIM</button></td><td><button  pim-itemnr="${element.itemno}" class="sync-import-pim">Importere</button></td></tr>`
            }
            if(element.state == 3) {
                html="Varen er oprettet i PIM";
            }
        });
        html+=`</table>`
        $(".sync-main-container-out").html(html);
        this.newNAVItemsEvent()
    }
    newNAVItemsEvent(){
        let self = this;
        $(".sync-check-pim").unbind("click").click(
            function(){
                let itemno = $(this).parent().find(".sync-pim-no").val();
                let pimItemNr =  $(this).attr("pim-itemnr");
                if(itemno == "") return;
                self.syncCheckPimLookup(itemno,pimItemNr);
            }
        )
        $(".sync-import-pim").unbind("click").click(
            function(){
                self.importNewNavItem($(this).parent().parent().find(".sync-itemno").html())
            }
        )
    }
    importNewNavItem(itemno){
        let self = this;

        $(".sync-main-container-out").html("Opdaterer");
        $.post( " https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/pim/sync/navImportToPim", {itemno:itemno},function( data ) {
            alert("Varen er importeret")
            self.loadNewNAVItems();
        })
    }



    syncCheckPimLookup(itemno,pimItemNr)
    {
            let self = this;
            $.post( " https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/pim/sync/navImportGetPimData", {itemno:itemno},function( data ) {
                let returnHtml = "";
                var result = JSON.parse(data);
                result.data.forEach((element) => {

                    let html =`
                    
                    <table width='500'><tr><th width='100'>Land</th><th width='200'>NAV beskrivelse</th><th width='200'>Overskrift</th></tr>
                    <tr><td>Da</td><td>${element.erp["da"]}</td><td>${element.headline["da"]}</td></tr>
                    <tr><td>No</td><td>${element.erp["no"]}</td><td>${element.headline["no"]}</td></tr>
                    <tr><td>Sve</td><td>${element.erp["se"]}</td><td>${element.headline["se"]}</td></tr>
                    <tr><td>Eng</td><td>${element.erp["en"]}</td><td>${element.headline["en"]}</td></tr>
                    </table>
                    <div><div style="float: left; padding: 10px;">Group product no's: ${element.group_product_nos}</div>  <button pimItemNr="${pimItemNr}"  kontainerID="${element.kontainerID}"  class="sync-merge-pim" style="color: black">Sammenflet varen med denne i PIM</button></div>
                    <br> <hr> <br>
                    `
                    returnHtml+=html;

                })

                $("#top-panel-content").html(returnHtml)
                $("#top-panel").slideDown(300);
                self.setSyncMergeEvent();
            });

    }





    async loadSyncLog(){

        let dato =  $("#datepicker").val();
        let newDato = dato.replace(/\//g,'-');
       let postData = {
            "dato":newDato
       }
       let self = this;
        $.post( " https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/pim/sync/getSyncLog",postData,function( data ) {
            console.log(data)

        });

    }


    async gavevalg(){
        $.post( " https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/pim/sync/gavevalg", function( data ) {
            $(".sync-main-container-out").html(data)
        });
    }

    async doSyncLogo(){
        $(".sync-main-container-out").html("<h3>Systemet opdaterer logoerne. Vent venligst det kan tage et par minutter.</h3>")
        $.post( " https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/pim/sync/syncLogo", function( data ) {
            const myobj = JSON.parse(data);
            $(".sync-main-container-out").html("<h3>Systemet har indlæst/opdateret "+myobj.total + " logoer </h3>")
        });
    }
    async showSyncItemList(){
     //   let result = await super.post("pim/sync/getSyncList");
      // let datepicker = $("#datepicker").datepicker( 'getDate' );


       let dato =  $("#datepicker").val();
       let newDato = dato.replace(/\//g,'-');
        let gruppevarer = $("#gruppevarer").is(':checked') ? "1": "0";

        let postData = {
            "dato":newDato ,
            "grupppe":gruppevarer
        }

        let self = this;
        $.post( " https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/pim/sync/getSyncList",postData,function( data ) {
            $(".sync-main-container-out").html(data);
            self.initSync();
        });

    }
    async doSync(){
        $.post( " https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/pim/sync/sync", function( data ) {
            $(".sync-main-container-out").html(data);
        });
    }
    initSync(){
        let self = this;

        $(".do-sync").unbind("click").click(
            function(){
                self.doSingleSync($(this).attr("data-id"));
            }
        )
    }
    async doSingleSync(id){
        $.post( " https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/pim/sync/doSingleSync/"+id, function( data ) {
            console.log(data)
        });
    }

    layout(){
        $(".sync-main-container").html(this.template());
    }
    datepicker(){
        let format ="yyyy-mm-dd"
        $( "#datepicker" ).datepicker();
        $( "#format" ).on( "change", function() {
            $( "#datepicker" ).datepicker( "option", "dateFormat", format );
        });


    }






    template(){

        return `    <br><br><center>
    <table><tr>
        <td><button type="button" id="btn-newNAVItems" class="btn btn-primary">New Items</button> <input id="newNAVSingleItem" type="text" placeholder="Varenr"></td>
  </tr>
</table>
 </center>

`;
    }



}


$( document ).ready(function() {
    var Sync = new SyncMain();
});


/*
  <td > Date: <input type="text" id="datepicker" size="30">
      </td>


    <td>
        <span>Gruppevarer </span><input id="gruppevarer" type="checkbox">
    </td>

    <td>
        <button type="button" id="btn-syncLog" class="btn btn-primary">Sync log</button>
    </td>
    <td>
        <button type="button" id="btn-getSyncList" class="btn btn-primary">HENT LIST </button>
    </td>
    <td>
    <button type="button" id="btn-sync" class="btn btn-primary">SYNC</button>
    </td>
    <td>
    <button type="button" id="btn-sync-logo" class="btn btn-primary">SYNC LOGO</button>
    </td>
    <td>
    <button type="button" id="btn-gavevalg" class="btn btn-primary">Gavevalg data</button>
    </td>
 */