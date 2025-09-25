var hej = "12";
import Base from '../../main/js/base.js?v=123';
import tpOrderlist from '../../orderlist/tp/orderlist.tp.js?v=123';
export default class Orderlist extends Base {
    constructor(companyID) {
         super();

         this.companyID = companyID;
         this.internenoterHasChange = false;
         this.leveringsaftalerHasChange = false;
         this.init();

    }
    async init(){
        let data = await super.post("cardshop/orderform/getAll/"+this.companyID );
        this.Layout(data);
        this.SetEvents();

    }
    Layout(data){
        let tempHtml = "";
        let data2 = data.result;
        let orderStatusMap = {0: '0: Oprettet, venter', 1: '1: Afventer sync af ny ordre', 2: '2: På blokkeringsliste', 3: '3: Afventer sync efter block', 4: '4: Synkroniseret', 5: '5: Synkroniseret (kort sendt)', 6: '6: Synkronisering fejlet', 7: '7: Skal annulleres', 8: '8: Annulleret og lukket', 9: '9: Skal afsluttes', 10: '10: Afsluttet', 11: '11: Arkiveret', 20: '20: Behandles manuelt'};

        data2.map(function(obj) {
            let ele = obj.attributes;
            let status = orderStatusMap.hasOwnProperty(ele.order_state) ? orderStatusMap[ele.order_state] : 'Ukendt status';
            
            let cardState = "";
            if(ele.is_email == 1) {
                cardState = "E-mail";
                if(ele.welcome_mail_is_send == 1) {
                    cardState += " - sendt";
                } else {
                    cardState += " - ikke sendt";
                }
            } else {
                cardState = "Fysisk - ";

                if(ele.shipment_state == null) cardState += 'Ikke oprettet';
                else if(ele.shipment_state == 0) cardState += 'Afventer';
                else if(ele.shipment_state == 1) cardState += 'Klar';
                else if(ele.shipment_state == 2) cardState += 'Sendt til nav';
                else if(ele.shipment_state == 3) cardState += 'Fejl';
                else if(ele.shipment_state == 4) cardState += 'Blokkeret';
                else if(ele.shipment_state == 5) cardState += 'Behandlet eksternt';
                else if(ele.shipment_state == 6) cardState += 'Synkroniseret';
                else if(ele.shipment_state == 7) cardState += 'Ukendt';
                else if(ele.shipment_state == 9) cardState += 'Fejl i land';
                else cardState += 'Ukendt status ('+ele.shipment_state+')';

            }
            
            let adminBtn = (ele.is_admin == 1 ? " <button data-id=\""+ele.id+"\" data-no=\""+ele.order_no+"\" class=\"orderlistAdminBtn\" type=\"button\" class=\"btn btn-warning\" style='margin: 4px;'>Admin</button>" : "");

            tempHtml+= `<tr><td>${ele.order_no}</td><td>${ele.created_datetime.date}</td><td>${ele.shop_name}</td><td>${ele.certificate_no_begin}</td><td>${ele.certificate_no_end}</td><td>${ele.quantity}</td><td>${ele.expire_date.date}</td><td>${cardState}</td><td>${status}</td><td>${ele.salesperson}</td><td><button id="${ele.id}" class="orderlistEditBtn" type="button" class="btn btn-warning" style='margin: 4px;'>Edit</button>${adminBtn}</td></tr>`
        })
        let html = `<br><table id="order-view" class="display">
    <thead>
        <tr>
            <th>BS-nummer</th>
            <th>Dato</th>
            <th>Kort</th>
            <th>Kort start</th>
            <th>Kort slut</th>
            <th>Antal</th>
            <th>Udlobsdato</th>
            <th>Kort/koder</th>
            <th>Nav status</th>
            <th>S&oelig;lger</th>
            <th></th>
        </tr>
    </thead>

     <tbody>
        ${tempHtml}
     </tbody></table>`;
     $(".tab-orders").html(html);
    }
    SetEvents(){
        let me = this;
       $('#order-view').DataTable({
            "paging": false
       });
       $(".orderlistEditBtn").unbind("click").click(
           function()
           {
                me.LoadOrderItems( $(this).attr("id" ))
                //me.Modal()
           }
       )
        $(".orderlistAdminBtn").unbind("click").click(
            function()
            {
                var id = $(this).attr("data-id" );
                var no = $(this).attr("data-no" );
                var iframeurl = BASEURL+'cardshop/admin/coiframe/'+id;
                var iframehtml = '<iframe src="'+iframeurl+'" style="width: 100%; height: 70vh; border: none; outline: none;"></iframe>';

                $('.modal-footer').html('');
                $("#ModalFullscreenLabel").html("Admin administrator handlinger på ordre "+no+"");
                $(".modal-body").html(iframehtml);
                $('#ModalFullscreen').modal('show');

            }
        )





    }

    updatePrepaymentStatus() {

        if($('#prepdato').is(':checked')) {
            $('input[name=prepaymentdate]').show();
        } else {
            $('input[name=prepaymentdate]').hide();
        }

        if($('input[name=editOrderPrepayment]:checked').val() == '1') {
            $('#prepaymenteditwarn').hide();
        } else {
            $('#prepaymenteditwarn').show();
        }

/*
        if($('input[name]:checked').is(':disabled')) {
            console.log('disable aconto elm');
            $('input[name=editOrderPrepaymentDates]').prop('disabled', true);
            $('input[name=prepaymentdate]').prop('disabled', true);
            $('input[name=prepaymentduedate]').prop('disabled', true);
        } else {
            console.log('enable aconto elm');
            $('input[name=editOrderPrepaymentDates]').prop('disabled', false);
            $('input[name=prepaymentdate]').prop('disabled', false);
            $('input[name=prepaymentduedate]').prop('disabled', false);
        }

        if($('input[name=editOrderPrepayment]:checked').val() == '1') {

            $('#prepaymentdatecheck').show();
            if($('input[name=editOrderPrepaymentDates]').is(':checked')) {
                $('#prepaymentdatebuttons').show();
            } else {
                $('#prepaymentdatebuttons').hide();
            }

        } else {
            $('#prepaymentdatecheck').hide();
            $('#prepaymentdatebuttons').hide();
        }
 */

    }

    async LoadOrderItems(orderID){
        let self = this;
        this.internenoterHasChange = false;
        this.leveringsaftalerHasChange = false;
        this.ordernoteHasChange = false;

        const data = await super.post("cardshop/orderform/getitemsandmeta/"+orderID);

        let shopID = data.result[0].meta.shop_id;
        let itemRules = await super.post("cardshop/orderform/shopproducts/"+shopID);
        let isHomeDelevery = false;
        let isEmail = false;
        const orderData = await super.post("cardshop/orderform/get/"+orderID);

        if(data.hasOwnProperty('homedelivery') && data.homedelivery ){
            isHomeDelevery = true;
        }

        let companyData = await super.post("cardshop/orderlist/getCompanyToken",{companyid:this.companyID});
        let token = companyData.data.attributes.token;
        let html = "";

        html+= tpOrderlist.csvOptions(orderID);
        html+= tpOrderlist.pdfOptions(orderID,token);
        html+= tpOrderlist.pdfOptionsSingle(orderID,token);
        if(orderData.data.result[0].is_email == 1){
            isEmail = true;
            html+= tpOrderlist.mailOptions(orderID,"Send ny mail med koder");
        }

        html+= `<label><b>Produkter:</b></label> <hr><table width=400> ` +

        data.result.map((i) => {

        if(i.item == null || self.doShowAdditionalProductsItem(i.item[0].type,isEmail,isHomeDelevery,itemRules) == false){
          return;
        }


        let disabled = "";
        let ismandatory = ""


        let active = "disabled";
        let activeCheck = "";

        if( i.item[0].quantity > 0){
            activeCheck = "checked";
            active = "";
        }
         if(i.meta.metadata.ismandatory == true){
            ismandatory =  "disabled"
            activeCheck = "checked";
         } else {
            ismandatory = "";
         };





        let defaultValue = i.item[0].isdefault ? i.item[0].price : false;
            return `<tr><td width=30><input type="checkbox" ${activeCheck} ${ismandatory}  ${disabled} class="AdditionalProductsActivate" data-id=${i.item[0].type} /></td><td width=150>${i.meta.name}</td><td width=50><input style="width:60px" class="AdditionalProductsItems" ${active} type="text"  id="${i.item[0].type}" value="${i.item[0].price/100}" defaultValue="${defaultValue}" /></td></tr>`
        }).join('') +
        `</table>`;

        if(window.USERID == 86 || window.USERID == 63 || window.USERID == 50 || window.USERID == 110 || window.USERID == 138 || window.USERID == 155 || window.USERID == 144 ){
                let excemptEnvfeeCheck = orderData.data.result[0].excempt_envfee  == 0 ? "checked" : "";
                html+= `<input id='useEnvfee' data-id='${orderData.data.result[0].excempt_envfee}' style='margin-right:40px;' type='checkbox' ${excemptEnvfeeCheck} /><label style='color:green'>Milj&oslash;afgift</label><br>`;
        }






        html+= tpOrderlist.editOrder(orderData);
        this.Modal(html);
        let saveHtml =    `<div class="row">
                            <div class="col-10"></div>
                            <div class="col-2 "><button  class="btn btn-primary shadow-none" id="OrderFormSave">Save</button></div>
                        </div> `
        $('.modal-footer').html(saveHtml);

        // Set eventlisners
        $(".AdditionalProductsActivate").unbind("change").change(function() {
            let element = $(this).attr("data-id")
            $(this).is(':checked')  ? $("#"+element).prop( "disabled", false ) : $("#"+element).prop( "disabled", true );
        })

        $("#OrderFormSave").unbind("click").click(function() {
            self.save(orderID)
        })

       $("#internenoter").unbind("click").click(
        function()
           {
                self.internenoterHasChange = true;
           }
       )
       $("#leveringsaftaler").unbind("click").click(
        function()
           {
                self.leveringsaftalerHasChange = true;
           }
       )

       $("#ordernote").unbind("click").click(
        function()
           {
                self.ordernoteHasChange = true;
           }
       )
      $('#internenoter').unbind('input propertychange').bind('input propertychange', function() {
            self.internenoterHasChange = true;
      });
      $('#leveringsaftaler').unbind('input propertychange').bind('input propertychange', function() {
           self.leveringsaftalerHasChange = true;
      });
        $('#ordernote').unbind('input propertychange').bind('input propertychange', function() {
            self.ordernoteHasChange = true;
        });
      $(".editOrderNewCodeMail").unbind("click").click(
        function()
           {
                self.sendNewCodeMail( $(this).attr("data-id") );
           }
       )


        $("#changesalespersonshow").unbind("click").click(async function() {
            $('#changesalesperson').closest('div').show();
            let salespersonData = await self.post("cardshop/orderform/salespersons",{language:window.LANGUAGE});
            $('#changesalesperson').html('<option value="">V&aelig;lg ny s&aelig;lger</option>'+salespersonData.salespersonlist.map((i) => { if(!i.name) return; return `<option value='${i.code}'>${i.name}</option>`}));
        })

        $("#changesalespersonsave").unbind("click").click(async function() {

            let postData = {companyorderid:orderData.data.result[0].id, salesperson: $('#changesalesperson').val(), comment: $('#changesalespersoncomment').val()};

            if(postData.salesperson == undefined || postData.salesperson == null || postData.salesperson == '') {
                self.Toast("Der er ikke valgt en s&aelig;lger","FEJL",true);
                return;
            }

            if(postData.comment == undefined || postData.comment == null || postData.comment == '') {
                self.Toast("Skriv kommentar til personen der skal godkende.","FEJL",true);
                return;
            }

            let response = await self.post("cardshop/orderform/changesalesperson",postData);
            if(response == null || !response.hasOwnProperty('status') || response.status != 1) {
                if(!response.hasOwnProperty('message') || response.message == '') {
                    self.Toast("Der skete en fejl ved &aelig;ndring af s&aelig;lger","FEJL",true);
                } else {
                    self.Toast(response.message,"FEJL",true);
                }
            } else {
                self.Toast("Anmodning er gemt men ændres ikke på ordren før det er godkendt.");
                $('#changesalespersonshow').hide();
                $('#changesalespersonsave').closest('div').hide();
            }

        })
        
        $('input[name=editOrderPrepayment]').unbind("change").change(function() {
            self.updatePrepaymentStatus();
        })
        $('input[name=editOrderPrepaymentDates]').unbind("change").change(function() {
            self.updatePrepaymentStatus();
        })

        self.updatePrepaymentStatus();

    }
    // logic
    sendNewCodeMail(orderID){
        let self = this;
        $.post( "https://system.gavefabrikken.dk//gavefabrikken_backend/index.php?rt=gogo/csMailAfterSaleWeb",{id:orderID,lang:window.LANGUAGE}, function( res ) {
            const obj = JSON.parse(res);
            self.isSendtMsg();
        });
    }
    isSendtMsg()
    {
        super.Toast("Ny mail med koder er blevet sendt","Job udf&oslash;rt")
    }
    doShowAdditionalProductsItem(itemCode,isEmail,isHomeDelevery,rules){

        let doShow = true;
        rules.result.map((i) => {
             if(itemCode == i.code){
             //  requireon
             switch (i.metadata.requireon) {

                case "emailcards":
                    if(isEmail == false){
                      doShow = false;
                    }

                break;

                case "physicalcards":
                    if(isEmail == true){
                      doShow = false;
                    }
                break;

                case "privatedelivery":
                    if(isHomeDelevery == false){
                      doShow = false;
                    }
                break;

                case "companydelivery":
                    if(isHomeDelevery == true){
                      doShow = false;
                    }
                break;
            }

               //  hideon
            switch (i.metadata.hideon) {

                case "emailcards":
                    if(isEmail == true){
                      doShow = false;
                    }

                break;

                case "physicalcards":
                    if(isEmail == true){
                      doShow = false;
                    }
                break;

                case "privatedelivery":
                    if(isHomeDelevery == true){
                      doShow = false;
                    }
                break;

                case "companydelivery":
                    if(isHomeDelevery == false){
                      doShow = false;
                    }
                break;
            }

          }
        })

        return doShow;
    }


    async save(orderID){
        let item = this.GetUpdateOrderItems()

        let orderdata = {};
        if($("#editOrderFreeAmountInput").val() != '')  orderdata.free_cards = $("#editOrderFreeAmountInput").val();
        // prepayment
        orderdata.prepayment = $("input:radio[name ='editOrderPrepayment']:checked").val();
        // custom reff
        orderdata.requisition_no = $("#editOrderReference").val();
        if(this.internenoterHasChange == true){
            orderdata.salenote = $("#internenoter").val();
        }
        if(this.leveringsaftalerHasChange == true){
            orderdata.spdealtxt = $("#leveringsaftaler").val();
        }
        if(this.ordernoteHasChange == true){
            orderdata.ordernote = $("#ordernote").val();
        }
        
        if($('input[name=editOrderPrepayment]:checked').val() == '2') {
            orderdata.prepayment_date = $('input[name=prepaymentdate]').val();

        }


        if(window.USERID == 86 || window.USERID == 63 || window.USERID == 50 || window.USERID == 110 || window.USERID == 138 || window.USERID == 155 || window.USERID == 144){
            let envfee  =  $("#useEnvfee").is(":checked") ? "0":"1";
            if( $("#useEnvfee").attr("data-id") != envfee){
                orderdata.excempt_envfee =  envfee;
            }
        }
        await super.post("cardshop/orderform/update/"+orderID,{orderdata:orderdata});

        $('#ModalFullscreen').modal('hide');
        let orderItemsPostData = this.GetUpdateOrderItems();
        await super.post("cardshop/orderform/updateitems/"+orderID,{orderitemlist:orderItemsPostData});
        super.Toast("Opdateringen er udf&oslash;rt")
    }
    GetUpdateOrderItems(){
        let postData = []
        $('.AdditionalProductsActivate').each(function(i, obj) {

                var type = $(obj).attr("data-id");
                var defaultValue  = 1
                if($(obj).attr("defaultValue") == false){
                    defaultValue  = 0;
                } else {
                    defaultValue  = ( $(obj).attr("defaultValue") == $("#"+type).val() ) ? 1:0;
                }
                let quantity =  $(obj).is(":checked") ? "1":"0";

                let price = $("#"+type).val();
                price = price.replace(",", ".");
                postData.push({
                   "type": type,
                   "isdefault": defaultValue,
                   "price" :price*100,
                   "quantity" : quantity
                })

        });
        return postData
    }

    Modal(html){
        $(".modal-body").html("System is working");
        $("#ModalFullscreenLabel").html("Edit Order");
        $(".modal-body").html(html);
        $('#ModalFullscreen').modal('show');

    }
}