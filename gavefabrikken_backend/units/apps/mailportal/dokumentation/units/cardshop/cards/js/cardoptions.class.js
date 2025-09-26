import Base from '../../main/js/base.js';
import tpCardoptions from '../tp/cardoptions.tp.js?v=123';


export default class Cardsoptions extends Base {
    constructor(companyID="",shopuserID,isCompany=false,callback="") {
        super();
        this.masterData;
        this.orderHistory;
        this.replacement;
        this.cardData;
        this.orderstate = 0;
        this.isCompany = isCompany;
        this.callback = callback;
        this.companyID = companyID;
        this.shopuserID = shopuserID;

        this.LoadData();
    }

    async LoadData(){


        this.orderHistory = await super.post("cardshop/cards/getCardOrderHistory/"+this.shopuserID);
        this.replacement = await super.post("cardshop/cards/getReplacementList/"+window.LANGUAGE);
        this.cardData = await super.post("cardshop/cards/getCardData/"+this.shopuserID);
        if(this.isCompany == false || this.isCompany == "valgshopReplacement"){
            let canEdit = await super.post("cardshop/cards/canEditmasterData/"+this.shopuserID);
            let result = await super.post("cardshop/cards/getMasterData/"+this.shopuserID);
            this.masterData = result.data;
            this.orderstate = canEdit.data;
        }



        this.Layout();
    }

    async Layout(){

       super.OpenModal("V&oelig;lg leveringsadressen",tpCardoptions.stucture);
       $( "#tabsMasterData" ).tabs();
       if(this.isCompany == false || this.isCompany == "valgshopReplacement"){
           $("#tabsMasterData-1").html(tpCardoptions.cardInfo(this.masterData,this.shopuserID,this.orderstate,this.cardData));

       }
       $("#tabsMasterData-2").html(tpCardoptions.invoiceLayout(this.orderHistory));
       if(this.cardData.data[0].is_replaced == 1){
           let replacementCardData =  await super.post("cardshop/cards/getReplacementCardData/"+this.shopuserID);
           $("#tabsMasterData-3").html(tpCardoptions.showReplacementCardData(replacementCardData.data[0]));
         //   $("#tabsMasterData-3").html("<br><div>Kortet har allerede f&aring;et tildelt et erstatningskort</div><br>");
       } else {
         $("#tabsMasterData-3").html(tpCardoptions.ReplacementList(this.replacement));
       }
       // replacment card for valgshop, dont show replacment options
       if(this.isCompany == "valgshopReplacement"){
                  $("#tabsMasterData-3").html("" +
                      " <button class='delete-replacementCard' data-id='"+this.shopuserID+"'>(under udvikling)Slet kortet (fx hvis kunden skal have et nyt erstatsningskort) </button> " +
                      " Kortet er et erstatningskort");
       }
       this.SetUpdateMasterdataEvent();


    }



    async SetUpdateMasterdataEvent(){
        let self = this;
        $(".updateMasterData").unbind("click").click(
            function(){

                let needUpdate = {};
                needUpdate.masterData = [];
                needUpdate.orderData = [];
                //alert($(this).attr("data-id"))
                $(".masterDataElement").each(function( index ) {
                    //if($( this ).attr("org-val") != $( this ).val()){
                        let obj = {"shopuser_id":self.shopuserID, "attribute_id":$( this ).attr("attr-id"),"attribute_value":$( this ).val()}
                        needUpdate.masterData.push(obj);
                        // order update
                        if($( this ).attr("is-name") == 1 ){
                            let obj = {"shopuser_id":self.shopuserID, "field":"name","attribute_value":$( this ).val()}
                            needUpdate.orderData.push(obj);
                        }
                        if($( this ).attr("is-email") == 1 ){
                            let obj = {"shopuser_id":self.shopuserID, "field":"email","attribute_value":$( this ).val()}
                            needUpdate.orderData.push(obj);
                        }

                    //}
                });


                if(needUpdate.orderData.length > 0 || needUpdate.masterData.length > 0 || $('.updateCountry').length > 0){
                   var countryName = $('.updateCountry').val()
                    $("#tabsMasterData-1").html("systemet arbejder");
                    self.doUpdateMasterData(needUpdate.masterData,needUpdate.orderData,countryName)
                }
            }
        )

        $(".card-replacement").unbind("click").click(
           function(){
                    if (confirm("Skal kortet erstattes ?") == true) {
                    $("#tabsMasterData-3").html("SYSTEMET ARBEJDER!")
                    self.doReplacement($( this ).attr("data-id"))
                    }
           }
        )
        $(".delete-replacementCard").unbind("click").click(
            function(){
                self.deleteReplacementCard($(this).attr("data-id") );
            }
        )


    }
    async deleteReplacementCard(shop_id){
        await super.post("cardshop/cards/deleteReplacementCard/"+shop_id);
    }

    async doReplacement(shop_id)
    {
        let postData = {
            target:shop_id,
            shop_user:this.shopuserID,
            language:window.LANGUAGE

        };
        await super.post("cardshop/cards/replacementCard",postData);
        let replacementCardData =  await super.post("cardshop/cards/getReplacementCardData/"+this.shopuserID);
        $("#tabsMasterData-3").html(tpCardoptions.showReplacementCardData(replacementCardData.data[0]));
        if(this.isCompany == false){
           $(".cardscards").trigger( "click" );
        } else {
            this.callback.callback();
        }

    }

    async doUpdateMasterData(masterData,orderData,country)
    {

        let data = {"masterData":masterData,"orderData":orderData,"country": country}
        console.trace(data);
        await super.post("cardshop/cards/updateMasterData",data);
        let result = await super.post("cardshop/cards/getMasterData/"+this.shopuserID);
        this.masterData = result.data;
        $("#tabsMasterData-1").html(tpCardoptions.cardInfo(this.masterData,this.shopuserID,this.orderstate,this.cardData));
        this.SetUpdateMasterdataEvent();
        super.Toast("Brugerens informationer er blevet opdateret");
        $(".cardscards").trigger( "click" );
    }


}