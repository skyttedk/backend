import Base from '../../main/js/base.js';
import tpSearch from '../../search/tp/search.tp.js?v=123';
import ShowCompanyForm from '../../companyform/js/showCompanyForm.class.js?v=123';
import TabsControl from '../../main/js/tabsControl.js?v=123';
import singleCard from '../../cards/js/singleCardInfo.class.js';
export default class Search extends Base {
    constructor() {
        super();

        this.Run();
        this.searchTxt;
        this.activeSearch = false;


    }
    async Run() {
        this.Layout();
        this.SetEvents();

    }

    async Layout() {
         super.OpenModal("Super S&oslash;ger",tpSearch.superSearchLayout(),"");


    }
    SetEvents() {
        let self = this;
        $("#doSupersearch").unbind("click").click(
            function(){
                if($("#textSupersearch").val() == "") return
                self.searchDispatcher();
            }
        )
        $("#doSuperMail").unbind("click").click(
            function(){
//                if($("#textSupersearch").val() == "") return
                self.searchEmail();
            }
        )


       /*
        $(document).unbind("keypress").on('keypress',function(e) {
            if(e.which == 13) {
                if($("#textSupersearch").val() == "") return
                self.searchDispatcher();
            }
        });
        */


    }
    async searchEmail(){
        let self = this;
        let search = $("#textSupersearch").val()
        $("#supersearchResult").html("Systemet arbejder");
        let response = await super.post("cardshop/search/getEmail",{searchTxt:search} );
        $("#supersearchResult").html(tpSearch.superSearchEmail(response));
        $(".search-mail-show").unbind("click").click(
            function(){
                self.showEmail($(this).attr("data-id"));
            }
        )
    }
    async showEmail(id){
       let response = await super.post("cardshop/search/readEmail/"+id );
       super.OpenRightPanel("Subject: "+response.data.attributes.subject,response.data.attributes.body,"normal",true)
    }




    searchDispatcher(){
        if(this.activeSearch == false){
            let searchType = $("#textSupersearch").val() //$("input[name='supersearchChoise']:checked").val();
            $("#supersearchResult").html("Systemet arbejder");
            let txt =  $("#textSupersearch").val();
            txt = txt.trim();
            if(isInt(txt)){
                if(txt == "31607692") {
                    this.handleReplacementCard()
                    return;
                }
            }

            this.doSuperSearch()
        } else {
          alert("System er optaget")
        }

        /*
        if(searchType.indexOf("*") !== -1){
            this.doSuperSearch()
        } else if(searchType.indexOf("@") !== -1){
            if(searchType.length > 2){
                this.doEmailSearch()
            }
        } else if (/[a-zA-Z]/g.test(searchType)) {
            if(searchType.length > 2){
                this.doNameSearch()
            }
        } else if( searchType.length == 7){
            this.doInvoiceSearch();
        } else if( searchType.length == 8){
            this.doCardSearch();
        } else {
            $("#supersearchResult").html("Det indtastede nummer blev ikke fundet, <br>det er kun kunder og kort fra 2021 du kan s&oslash;ge efter her");
        }
          */
    }
    async handleReplacementCard(searchTxt){
        let txt =  $("#textSupersearch").val();
        txt = txt.trim();
        let response = await super.post("cardshop/search/handleReplacementCard",{searchTxt:txt},false );

        // check om replacment kort er
    }


    async doSuperSearch(){
         let self = this;

         this.activeSearch = true;
         let txt =  $("#textSupersearch").val();
         txt = txt.trim();
         let response = await super.post("cardshop/search/doSuperSearch",{searchTxt:txt},false );
         this.activeSearch = false;
         if(response.type == 0){ $("#supersearchResult").html("<p>Intet resultat</p>"); }
        console.log(response.type)

         if(response.type == 1){ $("#supersearchResult").html(tpSearch.invoiceLayout(response)); }
         if(response.type == 2){ $("#supersearchResult").html(tpSearch.companyLayout(response)); }
         if(response.type == 3){

             $("#supersearchResult").html("<h3 style='color:red'>S&oslash;geresultat fra 2021</h3>"+tpSearch.invoiceLayout(response));
         }
         if(response.type == 4){ $("#supersearchResult").html("<h3  style='color:red'>S&oslash;geresultat fra 2021</h3>"+tpSearch.companyLayout(response)); }
         if(response.type == 5){ $("#supersearchResult").html(tpSearch.onlyCompany(response)); }
        setTimeout(this.highlight(self), 300)


    }


    highlight(self){

        let content = $("#supersearchResult").html();
        let search = $("#textSupersearch").val();
        let replaceWith = "<span style='color:#ff3300;font-weight:bold;'>"+search+"</span>";
        let result = content.replaceAll(search, replaceWith);
        $("#supersearchResult").html(result);
        self.setActionAfterSearch();
    }
    gotoCompany(companyID)
    {
        let tabs = new TabsControl(companyID);
        tabs.ShowTabs();
        new ShowCompanyForm().Init(companyID);
        $("#companylist-search").val("");
        $("#companylist").html("");
        super.CloseModal();
    }
    async gotoCompanyByReplacementID(replacementID)
    {
        let shopuser = await super.post("cardshop/cards/getShopuserData/"+replacementID);
        let companyID =  shopuser.data[0].company_id;
        this.gotoCompany(companyID);



    }


    setActionAfterSearch()
    {
        let self = this;


        $(".search-track-trace").unbind("click").click(
            function(){

                self.searchTrack($(this).attr("data-id"));
            }
        )

        $(".search-quck-masterdata-edit").unbind("click").click(
            function(){
                self.showQuickMasterDataEdit($(this).attr("data-id"));
            }
        )


        $(".search-show-replacement").unbind("click").click(
            function(){
                self.openSingleCardInfo($(this).attr("data-id"),"replacement");
            }
        )
        $(".search-show-org").unbind("click").click(
            function(){
                self.openSingleCardInfo($(this).attr("data-id"),"org");
            }
        )


        $(".search-goto-company").unbind("click").click(
            function(){
               let companyID = $(this).attr("data-id");
               self.gotoCompany(companyID);

            }
        )
        $(".search-show-company-info").unbind("click").click(
            function(){
               let companyID = $(this).attr("data-id");
               self.showCompanyInfo(companyID,this);

            }
        )
        $(".search-goto-company-replacement").unbind("click").click(
            function(){
               let replacementID = $(this).attr("data-id");
               self.gotoCompanyByReplacementID(replacementID);

            }
        )

    }

    async searchTrack(shopuserID)
    {
      let track = await super.post("cardshop/search/searchTrack/"+shopuserID);
      $('.search-track-trace').parent().html('<div style="margin: 5px; margin-top: 15px; padding: 5px; background: #F0F0F0; border: #A0A0A0; font-size: 0.9em; border-radius: 3px;">'+track.htmldetails+'</div>');
    }


    async showQuickMasterDataEdit(shopuserID)
    {
        let masterData = await super.post("cardshop/cards/getMasterData/"+shopuserID);
        let canEdit = await super.post("cardshop/cards/canEditmasterData/"+shopuserID);
        let html = tpSearch.cardMasterData(masterData,shopuserID,canEdit.data);
        $("#editMasterdata").remove();
        $("#supersearchResult").hide();
        $("#textSupersearch").hide();
        $("#doSupersearch").hide();


        $('<div id="editMasterdata"></div>').insertAfter($('#textSupersearch'));
        $("#editMasterdata").hide();
        $("#editMasterdata").html(html);
        $("#editMasterdata").fadeIn(500);

        this.SetUpdateMasterdataEvent();

    }

    async showCompanyInfo(companyID,element){
      $(element).hide();
      let resonse = await super.post("cardshop/search/getCompanyInfo/"+companyID );

      let html = "<tr><td colspan=2>"+tpSearch.onlyCompany(resonse)+"</td></tr> ";
     $( html ).insertAfter( element);
    }
   async openSingleCardInfo(shopuserID,action) {
      let id = "";
      if(action == "replacement"){
        let cardData = await super.post("cardshop/cards/getReplacementCardData/"+shopuserID );
        id = cardData.data[0].id;
      }
      if(action == "org"){
         id = shopuserID;

      }
      let sc = new singleCard(id);
      sc.showCardInfo();
   }

    async doInvoiceSearch(){
         let resonse = await super.post("cardshop/search/doInvoiceSearch",{searchTxt:$("#textSupersearch").val()} );
         $("#supersearchResult").html(tpSearch.invoiceLayout(resonse));
    }
    async doCardSearch(){
         let resonse = await super.post("cardshop/search/doCardSearch",{searchTxt:$("#textSupersearch").val()} );
         $("#supersearchResult").html(tpSearch.invoiceLayout(resonse));
    }
    async doEmailSearch(){
         let resonse = await super.post("cardshop/search/doEmailSearch",{searchTxt:$("#textSupersearch").val()} );
         $("#supersearchResult").html(tpSearch.invoiceLayout(resonse));
    }
    async doNameSearch(){
         let resonse = await super.post("cardshop/search/doNameSearch",{searchTxt:$("#textSupersearch").val()} );
         $("#supersearchResult").html(tpSearch.invoiceLayout(resonse));
    }
    async SetUpdateMasterdataEvent(){
        let self = this;
        $(".searchGoBack").unbind("click").click(function(){
            $("#editMasterdata").remove();
            $("#textSupersearch").fadeIn(500);
            $("#doSupersearch").fadeIn(500);
            $("#supersearchResult").fadeIn(500);
        })



        $(".searchUpdateMasterData").unbind("click").click(

            function(){
                let needUpdate = {};
                let shopuserID = $(this).attr("data-id");
                needUpdate.masterData = [];
                needUpdate.orderData = [];

                $(".searchMasterDataElement").each(function( index ) {
                    if($( this ).attr("org-val") != $( this ).val()){
                        let obj = {"shopuser_id":shopuserID, "attribute_id":$( this ).attr("attr-id"),"attribute_value":$( this ).val()}
                        needUpdate.masterData.push(obj);
                        // order update
                        if($( this ).attr("is-name") == 1 ){
                            let obj = {"shopuser_id":shopuserID, "field":"name","attribute_value":$( this ).val()}
                            needUpdate.orderData.push(obj);
                        }
                        if($( this ).attr("is-email") == 1 ){
                            let obj = {"shopuser_id":shopuserID, "field":"email","attribute_value":$( this ).val()}
                            needUpdate.orderData.push(obj);
                        }

                    }
                });
                if(needUpdate.orderData.length > 0 || needUpdate.masterData.length > 0){
                   // $("#tabsMasterData-1").html("systemet arbejder");
                    self.doUpdateMasterData(needUpdate.masterData,needUpdate.orderData)
                } else {
                    self.updateMasterDataMsg()
                }
            }
        )
    }
    updateMasterDataMsg()
    {
      super.Toast("Du har ikke &oelig;ndret p&aring; nogle data i felterne")
    }

    async doUpdateMasterData(masterData,orderData)
    {
        let data = {"masterData":masterData,"orderData":orderData}
        await super.post("cardshop/cards/updateMasterData",data);
        this.searchDispatcher();

        super.Toast("Brugerens informationer er blevet opdateret")
        $("#editMasterdata").remove();
        $("#textSupersearch").fadeIn(500);
        $("#doSupersearch").fadeIn(500);
        $("#supersearchResult").fadeIn(500);
    }

}

function isInt(value) {
    return !isNaN(value) &&
        parseInt(Number(value)) == value &&
        !isNaN(parseInt(value, 10));
}

