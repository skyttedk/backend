
import Base from '../../main/js/base.js?v=123';
import tpFreight from '../tp/freight.tp.js?v=123';


export default class Freight extends Base {
    constructor(companyID) {
        super();
        this.companyID = companyID;
        this.run();

    }
    run()
    {
        this.Layout();

    }



    async Layout(){

        let freightPrice = await super.post("cardshop/companyform/getshippingprice/"+this.companyID );
        let notes = await super.post("cardshop/orderlist/company/"+this.companyID );
        $(".tab-notes").html( tpFreight.freightMain() );
        $("#freightContent").html( tpFreight.freightPrice(freightPrice) );
        $("#freightContent").append( tpFreight.freightManualHandle(freightPrice) );
        $("#freightContent").append( tpFreight.freightNote(notes,this.companyID) );


        this.setActions();

    }
    setActions() {
        let self = this;
        $("#freightPriceOmOff_Tp").unbind("click").click(function() {
            $("#freightPriceSave_tp").toggle();
            $("#freightPrice_tp").toggle();
            $("#freightPrice_tp").val("");
            self.updateFreightPrice();
        })

        $("#freightPriceSave_tp").unbind("click").click(function() {
            self.updateFreightPrice();
        })

        $("#freightManHand_Tp").unbind("click").click(function() {
            self.updateFreightManHandle();
        })

        $(".freightNoteOpdate_Tp").unbind("click").click(function() {
            const id = $(this).attr("id");
            const note = $(this).parent().find("textarea").val();
            self.updateFreightNote(id,note);
        })


    }

    //******** Logic
    async updateFreightPrice(){
        const price = $("#freightPriceOmOff_Tp").is(":checked") ?  $("#freightPrice_tp").val() :  -1;
        await super.post("cardshop/companyform/updateshippingprice/"+this.companyID,{cost:price});
        super.Toast("Fragtpris opdateret");
    }

    async updateFreightManHandle(){
        const manhandle = $("#freightManHand_Tp").is(":checked") ?  1 :  0;
        await super.post("cardshop/companyform/updateshippingmanhandle/"+this.companyID,{manualhandle:manhandle});
        super.Toast("Manuelt håndtering opdateret");
    }

    async updateFreightNote(id,note){
        let orderdata = {
            spdealtxt:note
        }
        await super.post("cardshop/orderform/update/"+id,{orderdata:orderdata});
        super.Toast("Fragtnote opdateret");


    }



}