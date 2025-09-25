
import Base from '../../main/js/base.js?v=123';
import tpEarlyorder from '../tp/tap.earlyorder.tp.js?v=123';
import tpCompanyForm from '../../companyform/tp/companyForm.tp.js?v=123';
import companyform from '../../companyform/js/companyForm.class.js?v=123';
import tpOrderform from '../../orderform/tp/orderform.tp.js?v=123';
import Companylist from '../../companylist/js/companylist.class.js?v=123';



export default class Earlyorder extends Base {
    constructor(companyID) {
        super();
        this.companyform = new companyform();
        this.companyID = companyID;
        this.earlyorderData = {};
        this.earlyPresentData = {};
        this.childData = {};
        this.activeCompanyorderList = [];
        this.companyorder = "";
        this.run();
        this.lastorders = [];
        $(".tab-earlyorders").html("<div>SYSTEMT ARBEJDER</div>");
    }
    async run(){
        $("#cardshop-tabs-6").html("");
        this.companyData = await super.post("cardshop/companyform/get/"+this.companyID );
        this.setTabAction()
        await this.Data();
        this.BuildForm();
    }
    SetEvents(){
        let self = this;
        $("#newEarlyorder-btn").unbind("click").click(function(){
            self.showCreatNew();

        })

        $(".tab-earlyorders").show();

        // Output log number of buttons
        $('.earlyorder-edit').unbind('click').click(function() {

            let parent = $(this).closest('.newAdditionalDeleveryAdress');
            let token = parent.attr("id");

            // Remove disabled from form
            $('.'+token).attr('disabled', false);
            parent.find('.earlyorder-save').show();
            parent.find('.earlyorder-cancel').show();
            parent.find('.earlyorder-edit').hide();
            parent.find('.earlyorder-delete').hide();
            parent.find('.earlyorder-reopen').hide();

            parent.find(".OrderFormChildListNewAdress").unbind("change").change(function() {
                self.InsetChildAdressToForm( $(this).val(), $(this).find(":selected").attr("data-id")  );
            })

            parent.find(".CustomItemnrSwitch").unbind("change").change(function() {
                $(this).is(":checked") ?  $(this).parent().find('select').hide() : $(this).parent().find('select').show();
                $(this).is(":checked") ?  $(this).parent().find('select').val("none") : "";
                $(this).is(":checked") ?  $(this).parent().find('.earlyOrderCustomItemnr').show() : $(this).parent().find('.earlyOrderCustomItemnr').hide();
                $(this).is(":checked") ?  $(this).parent().parent().find(".earlyOrderPresentAmount").val(1) : $(this).parent().parent().find(".earlyOrderPresentAmount").val(0)
            })

            var currentorderid = parent.find('.ordernrinput').val();
            var select = '<select name="earlyordercoid" class="earlyordercoid '+token+'">\n';
            var hasValidOrder  = false;
            for(let i in self.lastorders){
                let order = self.lastorders[i].attributes;
                if(order.order_state != 7 && order.order_state != 8) {
                    if((currentorderid != null && currentorderid == order.order_no)) hasValidOrder = true;
                    select += '<option value="'+order.id+'" '+((currentorderid != null && currentorderid == order.order_no) ? 'selected' : '')+'>'+order.order_no+' ('+order.quantity +' x '+order.shop_name+')</option>'
                }
            }
            if(!hasValidOrder) {
                select += '<option value="none" selected>'+currentorderid+' - Ugyldig ordre!</option>';
            }
            select += '</select>\n';
            var oidparent = parent.find('.ordernrinput').parent();
            oidparent.removeClass('col-3').addClass('col-9').html(select);

        });

        $('.earlyorder-save').unbind('click').click(function() {

            let parent = $(this).closest('.newAdditionalDeleveryAdress');
            let shipmentid = $(parent).attr('companyorderid');
            let token = parent.attr("id");
            console.log('SAVE SHIPMENT: '+shipmentid+' - '+token);

            self.CreateUpdate(token,shipmentid);

        });

        $('.earlyorder-cancel').unbind('click').click(function() {
            if(confirm('Er du sikker på at du vil stoppe redigering uden at gemme? Det vil genindlæse earlygaverne igen.')) {
                self.run();
            }
        });

        $('.earlyorder-delete').unbind('click').click(function() {
               self.Remove(null,$(this).closest('.newAdditionalDeleveryAdress').attr("companyorderid") );
        });

        $('.earlyorder-reopen').unbind('click').click(function() {
            if(confirm('Er du sikker på at du vil genåbne denne earlyordre?')) {
                self.Reopen(null,$(this).closest('.newAdditionalDeleveryAdress').attr("companyorderid") );
            }
        });

    }

    setTabAction(){
        $("#cardshop-tabs-action").html(' <button id="newEarlyorder-btn" type="button" class="btn btn-outline-primary">Opret ny earlyorder</button>  ');
    }

    async Data() {
        return new Promise(async resolve => {
            this.earlyorderData = await super.post("cardshop/earlyorder/company/"+this.companyID);
            this.earlyPresentData = await super.post("cardshop/orderform/getEarlyPresent",{language:window.LANGUAGE});
            this.childData = await super.post("cardshop/companylist/childs/"+this.companyID);
            resolve();
        });
    }

    async BuildForm(){
        let self = this;
       // $("#newEarlyorder-btn").hide()
        $(".tab-earlyorders").html("");
        if(this.earlyorderData.shipmentlist.length == 0){
          $("#cardshop-tabs-6").prepend("<p>Der er ikke oprettet nogle Early orders for denne virksomhed</p>")
        }

        // Divide earlyorder shipments into groups
        const awaiting = [];
        const sent = [];
        const errorOrUnprocessed = [];
        const deleted = [];
        const unknown = [];

        // Opdel shipments efter status
        for (let i in this.earlyorderData.shipmentlist) {
            const shipment = this.earlyorderData.shipmentlist[i];
            console.log(shipment)
            if(shipment.deleted_date != null) {
                deleted.push(shipment);
            } else {
                switch (shipment.shipment_state) {
                    case 1:
                        awaiting.push(shipment);
                        break;
                    case 2:
                    case 5:
                    case 6:
                        sent.push(shipment);
                        break;
                    case 0:
                    case 3:
                        errorOrUnprocessed.push(shipment);
                        break;
                    case 4:
                        deleted.push(shipment);
                        break;
                    default:
                        unknown.push(shipment);
                        break;
                }
            }


        }

        // Funktion til at tilføje overskrifter og behandle shipments
        async function processGroup(group, title, bgColor, fontColor) {
            if (group.length > 0) {
                const headerHtml = `<div style="background-color:${bgColor}; color:${fontColor}; padding:10px; margin:10px 0; font-size:18px; font-weight:bold;">${title}</div>`;
                $(".tab-earlyorders").append(headerHtml);

                for (let shipment of group) {
                    let groupId = await this.AdditionalDeleveryAdressEarlyorders(shipment);
                    this.activeCompanyorderList = [...this.activeCompanyorderList, shipment.companyorder_id];
                    await this.insetOrderDataIntoForm(shipment, groupId);
                }
            }
        }

        // Tilføj oversigtslinje med status og antal
        let overviewHtml = '<div style="display: flex; gap: 10px; padding: 10px; margin-bottom: 20px;">';
        if (awaiting.length > 0) {
            overviewHtml += `<div style="background-color: #ADD8E6; color: #000000; padding: 5px 10px; border-radius: 10px;">Afventer: ${awaiting.length}</div>`;
        }
        if (sent.length > 0) {
            overviewHtml += `<div style="background-color: #90EE90; color: #000000; padding: 5px 10px; border-radius: 10px;">Sendt: ${sent.length}</div>`;
        }
        if (errorOrUnprocessed.length > 0) {
            overviewHtml += `<div style="background-color: #FFFFE0; color: #000000; padding: 5px 10px; border-radius: 10px;">Fejl: ${errorOrUnprocessed.length}</div>`;
        }
        if (deleted.length > 0) {
            overviewHtml += `<div style="background-color: #FFB6C1; color: #000000; padding: 5px 10px; border-radius: 10px;">Slettet: ${deleted.length}</div>`;
        }
        if (unknown.length > 0) {
            overviewHtml += `<div style="background-color: #D3D3D3; color: #000000; padding: 5px 10px; border-radius: 10px;">Ukendt status: ${unknown.length}</div>`;
        }
        overviewHtml += '</div>';

        // Tilføj oversigtslinje til DOM'en, hvis der er noget at vise
        if (awaiting.length > 0 || sent.length > 0 || errorOrUnprocessed.length > 0 || deleted.length > 0 || unknown.length > 0) {
            $(".tab-earlyorders").prepend(overviewHtml);
        }

        // Behandl grupper i den ønskede rækkefølge
        await processGroup.call(this, awaiting, "Afventer", "#ADD8E6", "#000000"); // Lys blå
        await processGroup.call(this, sent, "Sendt", "#90EE90", "#000000"); // Lys grøn
        await processGroup.call(this, errorOrUnprocessed, "Fejl", "#FFFFE0", "#000000"); // Lys gul
        await processGroup.call(this, deleted, "Slettet", "#FFB6C1", "#000000"); // Lys rød
        await processGroup.call(this, unknown, "Ukendt status", "#D3D3D3", "#000000"); // Grå


       let orders = await super.post("cardshop/orderform/getAll/"+this.companyID );
       self.lastorders = orders.result;

       if(orders.result.length > 0){
            $("#cardshop-tabs-action").show()
       } else {
            $("#cardshop-tabs-6").prepend("<p>Virksomheden har ingen ordre, som en early order kan knyttes til.</p>")
            $("#cardshop-tabs-action").hide();
       }

       if(orders.result.length > 0 ){
               for (let i in orders.result){
                    if(parseInt(orders.result[i].attributes.order_state) < 7 ){
                        this.companyorder = orders.result[i].attributes.id;
                    }
               }
       }
       if(this.companyorder != ""){
           $("#cardshop-tabs-action").show()
       } else {
           $("#cardshop-tabs-6").prepend("<p>Virksomheden har ingen ordre, som en early order kan knyttes til.</p>");
            $("#cardshop-tabs-action").hide()
       }
       window.setTimeout( () => this.SetEvents(), 500);

    }

    getOrderSelectTp(token,orderid) {

        var html = '<div class="row earlyorderco ${token}">\n' +
            '          <div class="col-12 justify-content-center align-self-center">Tilknyttet ordre:' +
            '             <select name="earlyordercoid" class="earlyordercoid '+token+'">\n'

        for(let i in this.lastorders){
            let order = this.lastorders[i].attributes;
            if(order.order_state != 7 && order.order_state != 8) {
                html += '<option value="'+order.id+'" '+((orderid != null && orderid == order.id) ? 'selected' : '')+'>'+order.order_no+' ('+order.quantity +' x '+order.shop_name+')</option>'
            }
        }

        html += '</select>\n</div></div>';
        return html;

    }

    async showCreatNew(){
        let self = this;
        let token = this.Token();
        let earlyList = "";
        let earlyPresentData = await super.post("cardshop/orderform/getEarlyPresent",{language:window.LANGUAGE});

        let adressHtml = tpEarlyorder.shippingForm(token)+tpOrderform.earlyOrderContact(this.companyData.data.result[0],token);


        let childDropdown = tpOrderform.childList(this.childData.result,token);
        for(let i=0;i<4;i++){
            earlyList+= tpOrderform.earlyOrderPresent(earlyPresentData.result,token);
        }


        let html = "<div id='OrderFormEarlyordersDeleveryAdress'><div id='"+token+"' class='newAdditionalDeleveryAdress'>"+self.getOrderSelectTp(token,0)+"<br>"+earlyList+"<hr><br>"+childDropdown+"<br>"+adressHtml+"<hr></div></div>";
        super.OpenModal("Opret ny Early order",html,tpCompanyForm.save(token));
        $("."+token+"#language_code").val(window.LANGUAGE)

        $("."+token+" select" ).unbind("change").change(function() {
            let value = 1
            if($(this).val() == "none"){
                value = 0;
            }
            $(this).parent().parent().find(".earlyOrderPresentAmount").val(value)
        })

        $(".OrderFormChildListNewAdress").unbind("change").change(function() {
            self.InsetChildAdressToForm( $(this).val(), $(this).find(":selected").attr("data-id")  );
            let groupId = $(this).find(":selected").attr("data-id");
            if($(this).val() != "none"){
                $("."+groupId+"#ship_to_company").prop('disabled', true);
                $("."+groupId+"#ship_to_address").prop('disabled', true);
                $("."+groupId+"#ship_to_address_2").prop('disabled', true);
                $("."+groupId+"#ship_to_postal_code").prop('disabled', true);
                $("."+groupId+"#ship_to_city").prop('disabled', true);
                $("."+groupId+"#language_code").prop('disabled', true);
            } else {
                $("."+groupId+"#ship_to_company").prop('disabled', false);
                $("."+groupId+"#ship_to_address").prop('disabled', false);
                $("."+groupId+"#ship_to_address_2").prop('disabled', false);
                $("."+groupId+"#ship_to_postal_code").prop('disabled', false);
                $("."+groupId+"#ship_to_city").prop('disabled', false);
                $("."+groupId+"#language_code").prop('disabled', false);
            }
        })

        $(".CustomItemnrSwitch").unbind("change").change(function() {
            $(this).is(":checked") ?  $(this).parent().find('select').hide() : $(this).parent().find('select').show();
            $(this).is(":checked") ?  $(this).parent().find('select').val("none") : "";
            $(this).is(":checked") ?  $(this).parent().find('.earlyOrderCustomItemnr').show() : $(this).parent().find('.earlyOrderCustomItemnr').hide();
            $(this).is(":checked") ?  $(this).parent().parent().find(".earlyOrderPresentAmount").val(1) : $(this).parent().parent().find(".earlyOrderPresentAmount").val(0)
        })
        let parentDevAdress = this.companyform.ReadFormValues("shippingForm");
        this.companyform.InsetFormValue(parentDevAdress,"shippingForm",token,".");

        $("."+token+"#conpanyFormSave").unbind("click").click(function() {
              self.SaveNew(token);
        })



      //  super.OpenModal()
    }
    async SaveNew(token){
        let result = this.validateEarlyOrderForm();
        if(result == true){
            alert("Fejl i formular, gave eller antal mangler")
            return;
        }

        let sendEarly = await this.handelEarlyOrders( this.companyorder  );
        await super.post("cardshop/earlyorder/sendcompanyorder/"+this.companyorder,false);
        super.CloseModal();
        new Earlyorder(this.companyID )
    }
    validateEarlyOrderForm()
    {
        let self = this;
        let earlyItems = [];
        let earlyItemCounter = 1;
        let earlyData = {};
        let earlyOrders = [];
        let error = false;

        $('.earlyorderco').css('background-color','');
        let coid = parseInt($(".earlyordercoid").val());
        if(isNaN(coid) || coid <= 0){
            $('.earlyorderco').css('background-color','red');
            return true;
        }

        $( "#OrderFormEarlyordersDeleveryAdress" ).find(".newAdditionalDeleveryAdress").each(function() {
            earlyData = {};
            earlyItems = [];
            earlyItemCounter = 1;
            let elementID = $( this ).attr("id");
            $( ".earlyOrder."+elementID ).each(function() {
                $( ".earlyOrder."+elementID).css('background-color','');
                // get all orders
                if($(this).find(".CustomItemnrSwitch").is(":checked")){
                    if( $(this).find(".earlyOrderCustomItemnr").val() != ""  ){
                        let itemnr = $(this).find(".earlyOrderCustomItemnr").val();
                        if($(this).find(".earlyOrderPresentAmount").val() > 0  ){
                             if(earlyItemCounter == 1){
                                earlyItems.push("'itemno':'"+itemnr+"'")
                                earlyItems.push("'quantity':'"+$(this).find(".earlyOrderPresentAmount").val()+"'")
                              } else {
                                let itemno = "'itemno"+earlyItemCounter+"'";
                                let quantity = "'quantity"+earlyItemCounter+"'";
                                earlyItems.push(itemno+":'"+itemnr+"'")
                                earlyItems.push(quantity+":'"+$(this).find(".earlyOrderPresentAmount").val()+"'")
                             }

                        }
                    }
                } else {
                    if( $(this).find("select").val() != ""  ){
                        let itemnr = $(this).find("select").val();
                        if($(this).find(".earlyOrderPresentAmount").val() > 0  ){
                             if(earlyItemCounter == 1){
                                earlyItems.push("'itemno':'"+itemnr+"'")
                                earlyItems.push("'quantity':'"+$(this).find(".earlyOrderPresentAmount").val()+"'")
                              } else {
                                let itemno = "'itemno"+earlyItemCounter+"'";
                                let quantity = "'quantity"+earlyItemCounter+"'";
                                earlyItems.push(itemno+":'"+itemnr+"'")
                                earlyItems.push(quantity+":'"+$(this).find(".earlyOrderPresentAmount").val()+"'")
                             }
                        }
                    }
                }
                earlyItemCounter++;

            })
            let earlyDataString = "{"+earlyItems.join(',')+"}";
            earlyData = JSON.parse(earlyDataString.replace(/'/g, '"'))
            // set order adress

            if(jQuery.isEmptyObject(earlyData) ){
                $( ".earlyOrder."+elementID).css('background-color', 'red');
                error = true;
            }

        });
        return error;
    }

    async handelEarlyOrders(companyorder_id){

        let self = this;
        let earlyItems = [];
        let earlyItemCounter = 1;
        let earlyData = {};
        let earlyOrders = [];
        let error = false;
        $( "#OrderFormEarlyordersDeleveryAdress" ).find(".newAdditionalDeleveryAdress").each(function() {
            earlyData = {};
            earlyItems = [];
            earlyItemCounter = 1;
            let elementID = $( this ).attr("id");
            $( ".earlyOrder."+elementID ).each(function() {
                // get all orders
                if($(this).find(".CustomItemnrSwitch").is(":checked")){
                    if( $(this).find(".earlyOrderCustomItemnr").val() != ""  ){
                        let itemnr = $(this).find(".earlyOrderCustomItemnr").val();
                        if($(this).find(".earlyOrderPresentAmount").val() > 0  ){
                             if(earlyItemCounter == 1){
                                earlyItems.push("'itemno':'"+itemnr+"'")
                                earlyItems.push("'quantity':'"+$(this).find(".earlyOrderPresentAmount").val()+"'")
                              } else {
                                let itemno = "'itemno"+earlyItemCounter+"'";
                                let quantity = "'quantity"+earlyItemCounter+"'";
                                earlyItems.push(itemno+":'"+itemnr+"'")
                                earlyItems.push(quantity+":'"+$(this).find(".earlyOrderPresentAmount").val()+"'")
                             }
                            earlyItemCounter++;
                        }
                    }
                } else {
                    if( $(this).find("select").val() != ""  ){
                        let itemnr = $(this).find("select").val();
                        if($(this).find(".earlyOrderPresentAmount").val() > 0  ){
                             if(earlyItemCounter == 1){
                                earlyItems.push("'itemno':'"+itemnr+"'")
                                earlyItems.push("'quantity':'"+$(this).find(".earlyOrderPresentAmount").val()+"'")
                              } else {
                                let itemno = "'itemno"+earlyItemCounter+"'";
                                let quantity = "'quantity"+earlyItemCounter+"'";
                                earlyItems.push(itemno+":'"+itemnr+"'")
                                earlyItems.push(quantity+":'"+$(this).find(".earlyOrderPresentAmount").val()+"'")
                             }
                             earlyItemCounter++;
                        }
                    }
                }


            })
            let earlyDataString = "{"+earlyItems.join(',')+"}";
            earlyData = JSON.parse(earlyDataString.replace(/'/g, '"'))
            if( !("itemno" in earlyData)  || !("quantity" in earlyData)){
                $( ".earlyOrder."+elementID).css('background-color', 'red');
                error = true;
            }

            // set order adress
            if ($( "."+elementID+".ship_to_company").val() == "" ){
               alert("fejl i oprettelsen af early order")
               error = true
            }
            earlyData.shipto_name       = $( "."+elementID+".ship_to_company").val();
            earlyData.shipto_address    = $( "."+elementID+".ship_to_address").val();
            earlyData.shipto_address2   = $( "."+elementID+".ship_to_address_2").val();
            earlyData.shipto_postcode   = $( "."+elementID+".ship_to_postal_code").val();
            earlyData.shipto_city       = $( "."+elementID+".ship_to_city").val();
            earlyData.shipto_country    = window.LANGUAGE;
            earlyData.shipto_contact    = $( "."+elementID+".AdditionalOrderFormContact").val();
            earlyData.shipto_email      = $( "."+elementID+".AdditionalOrderFormEmail").val();
            earlyData.shipto_phone      = $( "."+elementID+".AdditionalOrderFormMobile").val();
            earlyData.companyorder_id   =  $('.earlyordercoid').val();

            earlyOrders.push(earlyData);

        });
            return new Promise(resolve => {
                if(error == false){
                    earlyOrders.map(async earlyOrder => {
                        await self.CreateEarlyOrder(earlyOrder)
                    })
                }
                resolve();
            });
    }
    async CreateEarlyOrder(data){
        let EarlyOrderData = data;
        return new Promise(async resolve => {
            await super.post("cardshop/earlyorder/create",{shipmentdata:EarlyOrderData});
            resolve();
        })
    }




      InsetChildAdressToForm(id,target){
         if(id != "none"){
            this.companyform.InsetFormValue(this.childData.result[id].attributes,"shippingForm",target,".");
         } else {
            let returnObj = {};
            returnObj.cardto_address       = "";
            returnObj.cardto_address2      = "";
            returnObj.cardto_postal_code   = "";
            returnObj.cardto_city          = "";
            this.companyform.InsetFormValue(returnObj,"shippingForm",target,".");
         }

    }








     Rand(){
        return Math.random().toString(36).substr(2);
    }
    Token(){
       return this.Rand() + this.Rand();
    }


async insetOrderDataIntoForm(data,groupId) {
    let self = this;

    // return if shipment_state bigger than 0 has been shipt
    return new Promise(async resolve => {
      $('.row.earlyOrder.'+groupId).each(async function(i, obj) {
            if(i==0){
                if(data.itemno != ""){
                    $(obj).find(".earlyOrderPresentAmount."+groupId).val(data.quantity)
                    //let exist = await self.earlyPresentExist(data.itemno);
                    let exist = $(obj).find("select").find("option[value='"+data.itemno+"']").length > 0;
                    if(exist){
                        $(obj).find("select").val(data.itemno)
                    } else {
                        $(obj).find("select").hide();
                        $(obj).find(".CustomItemnrSwitch").attr('checked', true)
                        $(obj).find(".earlyOrderCustomItemnr").show().val(data.itemno);
                    }
                }
            }
            if(i==1){
                if(data.itemno2 != ""){
                    $(obj).find(".earlyOrderPresentAmount."+groupId).val(data.quantity2)
                    //let exist = await self.earlyPresentExist(data.itemno2);
                    let exist = $(obj).find("select").find("option[value='"+data.itemno2+"']").length > 0;
                    if(exist){
                        $(obj).find("select").val(data.itemno2)
                    } else {
                        $(obj).find("select").hide();
                        $(obj).find(".CustomItemnrSwitch").attr('checked', true)
                        $(obj).find(".earlyOrderCustomItemnr").show().val(data.itemno2) ;
                    }
                }
            }
            if(i==2){
                if(data.itemno3 != ""){
                    $(obj).find(".earlyOrderPresentAmount."+groupId).val(data.quantity3)
                    //let exist = await self.earlyPresentExist(data.itemno3);
                    let exist = $(obj).find("select").find("option[value='"+data.itemno3+"']").length > 0;
                    if(exist){
                        $(obj).find("select").val(data.itemno3)
                    } else {
                        $(obj).find("select").hide();
                        $(obj).find(".CustomItemnrSwitch").attr('checked', true)
                        $(obj).find(".earlyOrderCustomItemnr").show().val(data.itemno3) ;
                    }
                }
            }
            if(i==3){
                if(data.itemno4 != ""){
                    $(obj).find(".earlyOrderPresentAmount."+groupId).val(data.quantity4)
                    //let exist = await self.earlyPresentExist(data.itemno4);
                    let exist = $(obj).find("select").find("option[value='"+data.itemno4+"']").length > 0;
                    if(exist){
                        $(obj).find("select").val(data.itemno4)
                    } else {
                        $(obj).find("select").hide();
                        $(obj).find(".CustomItemnrSwitch").attr('checked', true)
                        $(obj).find(".earlyOrderCustomItemnr").show().val(data.itemno4);
                    }
                }
            }
      })
      await this.delay();
      resolve();
    });
}

earlyPresentExist(itemno){
    return new Promise(async resolve => {
        let exist =  await super.post("cardshop/orderform/earlyPresentExist",{itemno:itemno});

        let result = exist.result.length > 0 ? true:false;
        resolve(result);
    })
}


async insetDataIntoForm(data,groupId) {
    let self = this;
    return new Promise(async resolve => {
      // adresse
      $("."+groupId+"#ship_to_company").val(data.shipto_name);
      $("."+groupId+"#ship_to_address").val(data.shipto_address);
      $("."+groupId+"#ship_to_address_2").val(data.shipto_address2);
      $("."+groupId+"#ship_to_postal_code").val(data.shipto_postcode);
      $("."+groupId+"#ship_to_city").val(data.shipto_city);
      $("."+groupId+"#language_code").val(data.shipto_country);
      // contakt person
      $( "."+groupId+".AdditionalOrderFormContact").val(data.shipto_contact);
      $( "."+groupId+".AdditionalOrderFormEmail").val(data.shipto_email);
      $( "."+groupId+".AdditionalOrderFormMobile").val(data.shipto_phone);


      resolve();
    });
}

 async AdditionalDeleveryAdressEarlyorders(data){
        return new Promise(async resolve => {
        let self = this;
        let token = this.Token();
        let earlyList = "";
        let earlyPresentData = await super.post("cardshop/orderform/getEarlyPresent",{language:window.LANGUAGE});

        let adressHtml = tpEarlyorder.shippingForm(token,data)+tpEarlyorder.earlyOrderContact(token,data);

        let childDropdown = tpOrderform.childList(this.childData.result,token);
        for(let i=0;i<4;i++){
            earlyList+= tpOrderform.earlyOrderPresent(earlyPresentData.result,token);
        }

        let html = "<br><div id='"+token+"' companyorderid = '"+data.id+"' class='newAdditionalDeleveryAdress' ><div class='itemError "+token+"'></div>"+tpEarlyorder.earlyOrderMetadata(token,data)+earlyList+"<br>"+childDropdown+"<br>"+adressHtml+"</div><br><hr style='color:black; height:2px;'><br>";
        $(".tab-earlyorders").append(html);
        this.deactivateForm(token)
         resolve(token);
       });
    }
    deactivateForm(token){

       $("#cardshop-tabs-6").find("input").prop( "disabled", true );
       $("#cardshop-tabs-6").find("select").prop( "disabled", true )


    }

    async CreateUpdate(data,companyorderID=""){
           return new Promise(async resolve => {
        let elementID = data;
        let self = this;
        let earlyItems = [];
        let earlyItemCounter = 1;
        let earlyData = {};
        let earlyOrders = [];


            earlyData = {};
            earlyItems = [];
            earlyItemCounter = 1;

            $( ".earlyOrder."+elementID ).each(function() {
                // get all orders
                if($(this).find(".CustomItemnrSwitch").is(":checked")){
                    if( $(this).find(".earlyOrderCustomItemnr").val() != ""  ){
                        let itemnr = $(this).find(".earlyOrderCustomItemnr").val();
                        if($(this).find(".earlyOrderPresentAmount").val() > 0  ){
                             if(earlyItemCounter == 1){
                                earlyItems.push("'itemno':'"+itemnr+"'")
                                earlyItems.push("'quantity':'"+$(this).find(".earlyOrderPresentAmount").val()+"'")
                              } else {
                                let itemno = "'itemno"+earlyItemCounter+"'";
                                let quantity = "'quantity"+earlyItemCounter+"'";
                                earlyItems.push(itemno+":'"+itemnr+"'")
                                earlyItems.push(quantity+":'"+$(this).find(".earlyOrderPresentAmount").val()+"'")
                             }

                        }
                    }
                } else {
                    if( $(this).find("select").val() != ""  ){
                        let itemnr = $(this).find("select").val();
                        if($(this).find(".earlyOrderPresentAmount").val() > 0  ){
                             if(earlyItemCounter == 1){
                                earlyItems.push("'itemno':'"+itemnr+"'")
                                earlyItems.push("'quantity':'"+$(this).find(".earlyOrderPresentAmount").val()+"'")
                              } else {
                                let itemno = "'itemno"+earlyItemCounter+"'";
                                let quantity = "'quantity"+earlyItemCounter+"'";
                                earlyItems.push(itemno+":'"+itemnr+"'")
                                earlyItems.push(quantity+":'"+$(this).find(".earlyOrderPresentAmount").val()+"'")
                             }
                        }
                    }
                }
                earlyItemCounter++;

            })
            // remove red error background
            $( "."+elementID).removeClass("earlyOrderFormError");
            $( "."+elementID+".itemError").html("");
            // array to obj
            let earlyDataString = "{"+earlyItems.join(',')+"}";
            earlyData = JSON.parse(earlyDataString.replace(/'/g, '"'))
            // set order adress

               if($('.'+elementID+'.earlyordercoid').length > 0) {
                   earlyData.new_company_order_id = $('.'+elementID+'.earlyordercoid').val();
               }

            earlyData.shipto_name       = $( "."+elementID+".ship_to_company").val();
            earlyData.shipto_address    = $( "."+elementID+".ship_to_address").val();
            earlyData.shipto_address2   = $( "."+elementID+".ship_to_address_2").val();
            earlyData.shipto_postcode   = $( "."+elementID+".ship_to_postal_code").val();
            earlyData.shipto_city       = $( "."+elementID+".ship_to_city").val();
            earlyData.shipto_country    = $( "."+elementID+".language_code").val();
            earlyData.shipto_contact    = $( "."+elementID+".AdditionalOrderFormContact").val();
            earlyData.shipto_email      = $( "."+elementID+".AdditionalOrderFormEmail").val();
            earlyData.shipto_phone      = $( "."+elementID+".AdditionalOrderFormMobile").val();
            earlyData.companyorder_id   = this.companyID
            earlyData.shipment_type     = "earlyorder";

           // error handling
           if(  earlyItems.length == 0) $( "."+elementID+".itemError").html("<label class='earlyOrderFormError' style='color:white'>Valg af gaver mangler</label>");
           if(  !!!earlyData.shipto_name ) $( "."+elementID+".ship_to_company").addClass("earlyOrderFormError")
           if(  !!!earlyData.shipto_address ) $( "."+elementID+".ship_to_address").addClass("earlyOrderFormError")
           if(  !!!earlyData.shipto_postcode ) $( "."+elementID+".ship_to_postal_code").addClass("earlyOrderFormError")
           if(  !!!earlyData.shipto_city ) $( "."+elementID+".ship_to_city").addClass("earlyOrderFormError")
           if(  !!!earlyData.shipto_contact ) $( "."+elementID+".AdditionalOrderFormContact").addClass("earlyOrderFormError")
           if(  !!!earlyData.shipto_email ) $( "."+elementID+".AdditionalOrderFormEmail").addClass("earlyOrderFormError")
           if(  !!!earlyData.shipto_phone ) $( "."+elementID+".AdditionalOrderFormMobile").addClass("earlyOrderFormError")

           // if error end process
           if( $( "."+elementID).find(".earlyOrderFormError").length > 0 )  return;

           console.log('UPDATE EARLYORDER');
           console.log(earlyData);


           // send update
           let result = await super.post("cardshop/earlyorder/update/"+companyorderID,{shipmentdata:earlyData});
           if(result.status == 1) {
               self.run();
           }

             resolve(result);
       })

    }

    async Remove(data, companyorderID = "") {
        if (confirm("Er du sikker på at du vil slette earlyordre #" + companyorderID + "?")) {
            try {
                let result = await super.post("cardshop/earlyorder/delete/" + companyorderID);
                if (result && result.hasOwnProperty("status") && result.status == 1) {
                    this.run();
                } else if (result && result.hasOwnProperty("message")) {
                    alert("Der skete en fejl: " + result.message);
                } else {
                    alert("Der skete en fejl: Ukendt fejl");
                }
            } catch (error) {
                alert("Der skete en fejl: " + (error.message || "Ukendt fejl"));
            }
        }
    }

    async Reopen(data, companyorderID = "") {
        if (confirm("Er du sikker på at du vil genåbne earlyordre #" + companyorderID + "?")) {
            try {
                let result = await super.post("cardshop/earlyorder/reopen/" + companyorderID);
                if (result && result.hasOwnProperty("status") && result.status == 1) {
                    this.run();
                } else if (result && result.hasOwnProperty("message")) {
                    alert("Der skete en fejl: " + result.message);
                } else {
                    alert("Der skete en fejl: Ukendt fejl");
                }
            } catch (error) {
                alert("Der skete en fejl: " + (error.message || "Ukendt fejl"));
            }
        }
    }

    delay(){
        return new Promise(resolve => {
            setTimeout(function() {
              resolve();
            }, 50)
        })
    }

    Rand(){
        return Math.random().toString(36).substr(2);
    }
    Token(){
       return this.Rand() + this.Rand();
    }


}