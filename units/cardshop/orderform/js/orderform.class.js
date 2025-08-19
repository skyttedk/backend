
import tpOrderform from '../tp/orderform.tp.js?v=123';
import tpCompanyForm from '../../companyform/tp/companyForm.tp.js?v=123';
import Cards from '../../cards/js/cards.class.js?v=123';
import companyform from '../../companyform/js/companyForm.class.js?v=123';
import Companylist from '../../companylist/js/companylist.class.js?v=123';
import Access from '../../main/js/access.js';


import Base from '../../main/js/base.js';
export default class Orderform extends Base {
    constructor(companyID) {
        super();
        this.access = new Access();
        this.companyID = companyID;
        this.languageCode = "";
        this.CurrentOrder = {};
        this.childData = {};
        this.companyform = new companyform();
        this.Run();
        this.earlyCounter = 0;
        this.companyData = {};
        this.transactionError = [];
        this.accessList = [86,63,50,110,138,155,196,338];
        this.alternativCardShipping = false;
        this.useenvfee = false;
        this.orderInprocess = false;
        this.products = null;
    }
    async Run() {

        this.Layout();
        this.SetEvents();
        this.OrderFormShops();
    }

    async Layout() {
        this.Modal(tpOrderform.orderForm());
        this.OrderFormSalespersons();
        this.UserAccess();
    }
    SetEvents() {
    let ME = this;

        $("#OrderFormSave").unbind("click").click(
       //     function(){ ME.CreateOrder() }
        )
        $("#OrderFormSaveWithoutEarly").unbind("click").click(
            function(){ ME.CreateOrder(false) }
        )
        $("#AdditionalDeleveryAdressBtn").unbind("click").click(
            function(){ ME.AdditionalDeleveryAdress() }
        )
        $("#AdditionalearlyAdressBtn").unbind("click").click(
           function(){
                ME.earlyCounter++;
                ME.AdditionalDeleveryAdressEarlyorders()
                ME.HandelOrderBtn();
           }
        )
        $("#alternativCardShipping").unbind("click").click(()=>
          {
            if($("#alternativCardShippingForm").hasClass("hide") == true) {
              $("#alternativCardShippingForm").slideDown("hide").removeClass("hide");
              $(".companyform-slider-text").removeClass("text-deactivated")
              $("#alternativCardShippingForm").html( tpCompanyForm.shippingForm("alternativCardShippingElement") )
              ME.alternativCardShipping = true;
              $(".sendToParent").prop('checked', false);
              $(".sendToParent").hide()
            } else {
               ME.ResetAlternativCardShipping(false);
            }
          }
        );
        $('#OrderFormAmountInput').unbind("change").change(function() {
            // Din funktion her
            let ele = $("[data-id='MINORDERFEE']");
            if($(this).val() < 5){
                if(!ele.is(":checked")){
                    ele.click();
                }
            } else {
                if(ele.is(":checked")){
                    ele.click();
                }
            }
        });


        $('input[name=orderFormPrepayment]').unbind("change").change(function() {
            ME.updatePrepaymentStatus();
        })
        $('input[name=editOrderPrepaymentDates]').unbind("change").change(function() {
            ME.updatePrepaymentStatus();
        })

        ME.updatePrepaymentStatus();

        $('.usedot').unbind("change").change(function(event) {
            let elm = event.target;
            if (elm.checked) {
                document.querySelector('.dotdetails').style.display = 'block';
            } else {
                document.querySelector('.dotdetails').style.display = 'none';
            }
        })

        $('.dotpricetype').unbind("change").change(function(event) {
            let elm = event.target;
            var parent = $(elm).closest('.dotdeliverydiv');
            var priceType = $(elm).val();

            if(priceType == '3') {
                parent.find('.dotpriceamount').parent().show();
            } else {
                parent.find('.dotpriceamount').parent().hide();
            }
        })

        $('.usecarryup').unbind("change").change(function(event) {
            let elm = event.target;
            if (elm.checked) {
                document.querySelector('.carryupdetails').style.display = 'block';
                $('.carryuppricetype').trigger('change');
            } else {
                document.querySelector('.carryupdetails').style.display = 'none';
            }
        })

        $('.carryuppricetype').unbind("change").change(function(event) {
            let elm = event.target;
            var parent = $(elm).closest('.carryupdiv');
            var priceType = $(elm).val();

            if(priceType == '3') {
                parent.find('.carryuppriceamount').parent().show();
            } else {
                parent.find('.carryuppriceamount').parent().hide();
            }
        })


    }

    updatePrepaymentStatus() {

        console.log('update status');
        if($('#prepdato').is(':checked')) {
            $('input[name=prepaymentdate]').show();
        } else {
            $('input[name=prepaymentdate]').hide();
        }

        if($('input[name=orderFormPrepayment]:checked').val() == '1') {
            $('#prepaymentwarn').hide();
        } else {
            $('#prepaymentwarn').show();
        }

/*
        // No prepayment checked, force show and select ok
        if($('input[name=orderFormPrepayment]:checked').val() == '0') {
            $('input[name=editOrderPrepaymentDates]').prop('checked', true);
            $('#prepaymentdatebuttons').show();
            $('input[name=prepaymentdate]').prop('disabled', true).parent().hide();
        }

        else if($('input[name=editOrderPrepaymentDates]').is(':checked')) {
            $('#prepaymentdatebuttons').show();
            $('input[name=prepaymentdate]').prop('disabled', false).parent().show();
        } else {
            $('#prepaymentdatebuttons').hide();
            $('input[name=prepaymentdate]').prop('disabled', true).parent().hide();
        }

 */
/*
        console.log('update prepayment status');

        if($('input[name=orderFormPrepayment]:checked').is(':disabled')) {
            $('input[name=editOrderPrepaymentDates]').prop('disabled', true);
            $('input[name=prepaymentdate]').prop('disabled', true);
            $('input[name=prepaymentduedate]').prop('disabled', true);
        } else {
            $('input[name=editOrderPrepaymentDates]').prop('disabled', false);
            $('input[name=prepaymentdate]').prop('disabled', false);
            $('input[name=prepaymentduedate]').prop('disabled', false);
        }

        if($('input[name=orderFormPrepayment]:checked').val() == '1') {

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

    ResetAlternativCardShipping(updateSlider = true){
        if(updateSlider == true){
            $("#alternativCardShippingSlider").prop('checked', false);
        }
        $(".companyform-slider-text").addClass("text-deactivated")
        $("#alternativCardShippingForm").slideUp("hide").addClass("hide");

        $("#alternativCardShippingForm").html( "" )
        this.alternativCardShipping = false;
        $(".sendToParent").prop('checked', false);
        $(".sendToParent").show()
    }


    async OrderFormSalespersons(){
        let layoutData = await super.post("cardshop/orderform/salespersons",{language:window.LANGUAGE});
        $("#OrderFormSaleperson").html(tpOrderform.Saleperson(layoutData.salespersonlist));

    }
    UserAccess(){
        if(this.access.validate(window.USERID,1)){
            $("#OrderFormFreeAmountInput_UserAccess").show();
        }

        //if(window.USERID == 86 || window.USERID == 63 || window.USERID == 50 || window.USERID == 110 || window.USERID == 138 || window.USERID == 155){


/*
      if(this.accessList.indexOf(parseInt(window.USERID)) != -1){
              $("#OrderFormFreeAmountInput_UserAccess").show();
        }
*/
    }

    HandelOrderBtn(){
      //  this.earlyCounter > 0 ? $("#OrderFormSave").show() : $("#OrderFormSave").hide();
    }

    async OrderFormShops(){
        let ME = this;
        $(".OrderFormHide").hide();
        this.companyData = await super.post("cardshop/companyform/get/"+this.companyID );
        this.languageCode = this.companyData.data.result[0].language_code;

        let shops = await super.post("cardshop/orderform/countryshops/"+this.languageCode,{});
        $("#OrderFormShops").html(tpOrderform.OrderFormShops(shops.result));
        $("#OrderFormShopsSelect").unbind("change").change(function() {
            $("#OrderFormAdditionalProducts").html("");
            ME.CurrentOrder = {shop:$(this).val()}
            ME.OrderFormDeadline( $(this).val() );
            ME.ResetAlternativCardShipping();
        });
    }
    async OrderFormDeadline(shopType){
        let ME = this;

        $("#OrderFormDeadlineContainer").hide();
        $("#OrderFormShippingMethodContainer").hide();
        $("#OrderFormAmountContainer").hide();
        $("#AdditionalDeleveryAdress").hide();

        $("#AdditionalEarlyordersDeleveryAdress").show()

        this.childData = await super.post("cardshop/companylist/childs/"+this.companyID);
        let deadline = await super.post("cardshop/orderform/shopweeks/"+shopType,{});
        $("#OrderFormDeadline").html(tpOrderform.DeadlineFormShops(deadline.result));
        $("#OrderFormDeadlineContainer").show();
        $("#OrderFormDeadlineSelect").unbind("change").change(function() {
            $(".alternativCardShippingContainer").hide()
                ME.ResetAlternativCardShipping();
            $("#OrderFormAdditionalProducts").html("");
             ME.useenvfee = $(this).find(":selected").attr("useenvfee");
            ME.OrderFormShippingMethod( {expire_date:$(this).val(),sale_is_open: $(this).find(":selected").attr("saleisopen"), special:$(this).find(":selected").attr("special"), websale_is_open: $(this).find(":selected").attr("websaleisopen") } );

            let homedelivery = $("#OrderFormDeadlineSelect").find(":selected").attr("homedelivery") == "0" ? true : false;
            // Show / hide dot and carryup
            if(homedelivery) {
                $('.dotdeliverydiv').hide();
                $('.carryupdiv').hide();
                $('.usecarryup').attr('checked',false)
                $('.usedot').attr('checked',false)
            } else {
                $('.dotdeliverydiv').show();
                $('.carryupdiv').show();
            }
        });

        // Card values
        var values = deadline.result[0].card_values;
        if(values == undefined || values == null || values.length == 0) {
            $("#OrderFormValuesContainer").hide();
            $("#OrderFormValues").html('');
        } else {
            $("#OrderFormValuesContainer").show();
            $("#OrderFormValues").html(tpOrderform.ValuesFormShops(values));
        }
        

    }
    async OrderFormShippingMethod(options) {
        let ME = this;

        //$(".OrderFormShippingInput").hide();
        $("#OrdershippingMethod").html(tpOrderform.shippingMethod())
        $("#OrderFormShippingMethodContainer").show();

        //options.sale_is_open == "true" ? $("#OrderFormPhysicalShippingInput").parent().show() : $("#OrderFormPhysicalShippingInput").parent().hide();
        //options.websale_is_open == "true" ? $("#OrderFormEmailShippingInput").parent().show() : $("#OrderFormEmailShippingInput").parent().hide();
        if(options.special == "mailonly") {
            $("#OrderFormPhysicalShippingInput").parent().hide()
            $('#OrderFormEmailShippingInput').attr('checked','checked');
            ME.OrderFormAdditionalProducts();
        }
        $(".OrderFormShippingInput").unbind("click").click(function() {
          $("#AdditionalDeleveryAdress").show()
          $(this).val() == "physical" ? $(".sendToParentWrapper").show() : $(".sendToParentWrapper").hide();
          if($(this).val() == "physical") {
            $(".alternativCardShippingContainer").show()
          } else {
            $(".alternativCardShippingContainer").hide()
            ME.ResetAlternativCardShipping();
          }
        //  $(this).val() == "physical" ? $(".sendToParent").siblings().show() : $(".sendToParent").siblings().hide();
            ME.OrderFormAdditionalProducts();
        })

        this.OrderFormAmount();

    }
    OrderFormAmount() {
        $("#OrderFormAmountInput").val(0);
        $("#OrderFormAmountContainer").show();

    }
    async OrderFormAdditionalProducts(){

        let products = await super.post("cardshop/orderform/shopproducts/"+this.CurrentOrder.shop,{expireDate: $('#OrderFormDeadlineSelect').val()});
        let homedelivery = $("#OrderFormDeadlineSelect").find(":selected").attr("homedelivery") == "0" ? true : false;
        let deliveryType = $('input[name="flexRadioDefault"]:checked').val();

        $("#OrderFormAdditionalProducts").html(tpOrderform.AdditionalProducts(products.result,homedelivery,deliveryType))

        console.log('UPDTATE ADDITIONAL PRODUCTS');
        console.log(products);
        this.products = products;

        if(products.hasOwnProperty('extra') && products.extra.hasOwnProperty('dot_use') && products.extra.hasOwnProperty('dot_price')) {

            if(products.extra.dot_use == 0) {
                $('.dotdeliverydiv').hide();
                $('.usedot').prop('checked',false)
            } else {
                $('.dotdeliverydiv').show();
                $('.dotpricetype option[value=1]').html('Standard: '+(products.extra.dot_price/100));
                $('.dotpriceamount').val(products.extra.dot_price/100);
            }

        }

        if(products.hasOwnProperty('extra') && products.extra.hasOwnProperty('carryup_use') && products.extra.hasOwnProperty('carryup_price')) {
            
            if(products.extra.carryup_use == 0) {
                $('.carryupdiv').hide();
                $('.usecarryup').prop('checked',false)
            } else {
                $('.carryupdiv').show();
                $('.carryuppricetype option[value=1]').html('Standard: '+(products.extra.carryup_price/100));
                $('.carryuppriceamount').val(products.extra.carryup_price/100);
            }

        }

        $(".AdditionalProductsActivate").unbind("change").change(function() {
            let element = $(this).attr("data-id")
            $(this).is(':checked')  ? $("#"+element).prop( "disabled", false ) : $("#"+element).prop( "disabled", true );

            // tilpasser de to checkbox, så de opfører sig som radiobtn
            if($(this).attr("data-id") == "GIFTWRAP") {
                 if ($(this).is(':checked')) {
                     if ($("[data-id='NAMELABELS']").is(':checked')) {
                         $("[data-id='NAMELABELS']").click()
                     }

                }
            }
            if($(this).attr("data-id") == "NAMELABELS") {
                if ($(this).is(':checked')) {
                    if ($("[data-id='GIFTWRAP']").is(':checked')) {
                        $("[data-id='GIFTWRAP']").click()
                    }
                }
            }


        })
        $('.AdditionalProductsActivate[data-id="CONCEPT"]').parent().parent().hide();
        if(this.access.validate(window.USERID,2)){
            $('.AdditionalProductsActivate[data-id="CONCEPT"]').parent().parent().show();
        }

        if(this.access.validate(window.USERID,3)){
            if(this.useenvfee == 'true'){
              $("#OrderFormAdditionalProducts").append("<input id='useEnvfee' style='margin-right:20px;' type='checkbox' checked /><label style='color:green'>Milj&oslash;afgift</label")
            }
        }

       

    }
    async AdditionalDeleveryAdress(){
        let self = this;
        let token = this.Token();
        let adressHtml = tpOrderform.cardAmountInput(token)+" "+tpCompanyForm.shippingForm(token)+tpOrderform.deliveryOptionsInput(token,self.products);

        let childDropdown = tpOrderform.childList(this.childData.result,token);
        let html = "<div id='"+token+"' class='newAdditionalDeleveryAdress'><div style='width:100%; height:50px;'> <button  type='button' class='btn btn-danger removeAdditionalDeleveryAdress'>Remove</button></div><div class='sendToParentWrapper'><input type='checkbox' class='sendToParent "+token+"'  /> <label> Kun Gaver til adressen (gavekort sendes samlet til hovedadresse) </label></div><br><br>  "+childDropdown+" <br><br> "+adressHtml+"<hr></div>";
        $("#OrderFormAdditionalDeleveryAdress").append(html)
        $("#ship_to_country."+token).val(window.LANGUAGE)
        $("#OrderFormAdditionalDeleveryAdress .ship_to_phone").closest('.row').show();

        $(".removeAdditionalDeleveryAdress").unbind("click").click(function() {
            $(this).parent().parent().detach()
        })
        $(".OrderFormChildListNewAdress").unbind("change").change(function() {
            self.InsetChildAdressToForm( $(this).val(), $(this).find(":selected").attr("data-id")  );
        })
        this.alternativCardShipping == true ? $(".sendToParentWrapper").hide() : $(".sendToParentWrapper").show()

        $(".sendToParent").unbind("click").click(function() {
            $(".alternativCardShippingContainer").hide();
        })


        $('.childdeliveryoptionsparent').each(function() {
            var parent = $(this);

            parent.find('.childusedot').unbind("change").bind("change", function(event) {
                let elm = event.target;
                if (elm.checked) {
                    parent.find('.childdotdetails').show();
                } else {
                    parent.find('.childdotdetails').hide();
                }
            }).trigger('change');

            parent.find('.childdotpricetype').unbind("change").bind("change", function(event) {
                let elm = event.target;
                var priceType = $(elm).val();

                if(priceType == '3') {
                    parent.find('.childdotpriceamount').parent().show();
                } else {
                    parent.find('.childdotpriceamount').parent().hide();
                }
            }).trigger('change');

            parent.find('.childusecarryup').unbind("change").bind("change", function(event) {
                let elm = event.target;
                if (elm.checked) {
                    parent.find('.childcarryupdetails').show();
                } else {
                    parent.find('.childcarryupdetails').hide();
                }
            }).trigger('change');

            parent.find('.childcarryuppricetype').unbind("change").bind("change", function(event) {
                let elm = event.target;
                var priceType = $(elm).val();
                if(priceType == '3') {
                    parent.find('.childcarryuppriceamount').parent().show();
                } else {
                    parent.find('.childcarryuppriceamount').parent().hide();
                }
            }).trigger('change');
        });


    }
    async AdditionalDeleveryAdressEarlyorders(){
        let self = this;
        let token = this.Token();
        let earlyList = "";
        let earlyPresentData = await super.post("cardshop/orderform/getEarlyPresent",{language:window.LANGUAGE});

        let adressHtml = tpCompanyForm.shippingForm(token)+tpOrderform.earlyOrderContact(this.companyData.data.result[0],token);

        let childDropdown = tpOrderform.childList(this.childData.result,token);
        for(let i=0;i<4;i++){
            earlyList+= tpOrderform.earlyOrderPresent(earlyPresentData.result,token);
        }

        let html = "<div id='"+token+"' class='newAdditionalDeleveryAdress'><div style='width:100%; height:50px;'> <button  type='button' class='btn btn-danger removeEarlyordersAdress'>Remove</button></div><hr>"+earlyList+"<hr><br>"+childDropdown+"<br>"+adressHtml+"<hr></div>";
        $("#OrderFormEarlyordersDeleveryAdress").append(html)
        

        $("."+token+"#language_code").val(window.LANGUAGE)
        // bind actions
        $(".removeEarlyordersAdress").unbind("click").click(function() {
            self.earlyCounter == 0 ? "" : self.earlyCounter--;
            $(this).parent().parent().detach()
            self.HandelOrderBtn();
        })
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

        // make som custom changes in standart form
        $(".ship_to_company."+token).parent().prev().html("Navn");
        $(".ship_to_attention."+token).parent().parent().hide();
         // inset parent company deleverey adress ind
        let parentDevAdress = this.companyform.ReadFormValues("shippingForm");
        this.companyform.InsetFormValue(parentDevAdress,"shippingForm",token,".");





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



    Modal(html){
        $(".modal-body").html("System is working");
        $("#ModalFullscreenLabel").html("Create new order");
        $(".modal-body").html(html);
        $('#ModalFullscreen').modal('show');
        $('.modal-footer').html(tpOrderform.orderAndEarlyOrder());
        $("#OrderFormSave").hide();




    }

       /***  Layout logic   */


    /***  Bizz logic   */
    async CreateOrder(sendEarly=true){
       let ME = this;
       if(ME.orderInprocess == true){
          return;
       }
       ME.orderInprocess = true;
       let priceNotNull = true;
       window.uu_id = this.uuidv4();
       window.alwaysDev = [];
       let transactionError = [];  //{status:1,msg:""};
       // check that price is higher than zero
       $('.AdditionalProductsActivate').each(function(i, obj) {
                var type = $(obj).attr("data-id");
                if(type == "CONCEPT"){
                  let price = $("#"+type).val();
                  if(price == "" || price == 0 || price == "0" || price == null){
                    ME.priceNotNull = false;
                  }
                }

        });
        if(this.priceNotNull == false){

          let html = `<center>
               <p> Gavekort pris under Produkter m&aring; ikke s&oelig;ttes til 0. Du skal v&oelig;lge antal kort der skal s&oelig;lges og efterf&oslash;lgende s&oelig;tte antal kort der skal v&oelig;re gratis, se nedenst&aring;ende billede: </p><br>
               <img src="/gavefabrikken_backend/units/assets/img/freecard.jpg" alt="" />
             </center>
          `;

          ME.orderInprocess = false;
          super.OpenModal("Fej i pris",html)


          return;
        }






       if(parseInt( $("#OrderFormAmountInput").val() ) == 0 || $("#OrderFormAmountInput").val() == "" ){
           ME.orderInprocess = false;
            super.Toast("Number af cards placed on Additional Delevery Address are higher than ordet cards","FEJL",true );
            return;
        }

        if(this.CheckCardAmount() > ($("#OrderFormAmountInput").val()*1) ) {
            ME.orderInprocess = false;
            super.Toast("Number af cards placed on Additional Delevery Address are higher than ordet cards ");
            return;
        }
      //  $("#OrderFormSave").unbind("click").html("Saving!!!")
        let error = await this.validateNewOrderForm();
        if(error == true){
            ME.orderInprocess = false;
            super.Toast("Fejl i formular","FEJL",true );
            return;
        }

        
        if($('input.cardvalues').length > 0 && $('input.cardvalues:checked').length == 0) {
            ME.orderInprocess = false;
            super.Toast("Select card values","FEJL",true );
            return;
        }


        let postData = {};
        postData.shop_id = $("#OrderFormShopsSelect").val();
        postData.expire_date = $("#OrderFormDeadlineSelect").val();
        postData.quantity = $("#OrderFormAmountInput").val();
        postData.is_email =  $('input[name="flexRadioDefault"]:checked').val() == "email" ? "1":"0";
        postData.salesperson = $("#OrderFormSalepersonList").val();
        postData.salenote    = $("#OrderFormInternnote").val();
        postData.spdealtxt   = $("#OrderFormDeliveryAgreements").val();
        postData.ordernote   = $("#OrderFormOrderNotes").val();
        postData.values = $('input.cardvalues:checked').map(function() { return this.value; }).get().join(',');
        postData.formreport = ME.generateFormReport('#newOrderForm');

        if(this.access.validate(window.USERID,4)){
            postData.excempt_envfee  =  $("#useEnvfee").is(":checked") ? "0":"1";
        }
        postData.company_id = this.companyID;

        // free cards
        if($("#OrderFormFreeAmountInput").val() > 0 )  postData.free_cards = $("#OrderFormFreeAmountInput").val();

        // prepayment
        postData.prepayment = $("input:radio[name ='orderFormPrepayment']:checked").val();
        postData.prepayment_date = $("input[name ='prepaymentdate']").val();

        // dot delivery
        postData.dot_active = $('.usedot').is(":checked") ? "1":"0";
        postData.dot_price_type = $('.dotpricetype').val();
        postData.dot_price_amount = $('.dotpriceamount').val();
        postData.dot_description = $('.dotdescription').val();

        // carryup
        postData.carryup_active = $('.usecarryup').is(":checked") ? "1":"0";
        postData.carryup_price_type = $('.carryuppricetype').val();
        postData.carryup_price_amount = $('.carryuppriceamount').val();
        postData.carryup_type = $('input[name=carryuptypenew]:checked').val();

        // custom reff
        postData.requisition_no = $("#OrderFormReference").val();

        //  *****  trancation control  ****

        postData.shop_id == "" || postData.shop_id == null  ? this.transactionError.push({status:0,msg:"postData.shop_id"}) : "";
        postData.expire_date == "" || postData.shop_id == null ? this.transactionError.push({status:0,msg:"postData.expire_date"}) : "";
        postData.quantity == "" || postData.shop_id == null ? this.transactionError.push({status:0,msg:"postData.quantity"}) : "";
        postData.is_email == "" || postData.shop_id == null ? this.transactionError.push({status:0,msg:"postData.is_email"}) : "";
        postData.salesperson == "" || postData.shop_id == null ? this.transactionError.push({status:0,msg:"postData.salesperson"}) : "";
        postData.company_id == "" || postData.shop_id == null ? this.transactionError.push({status:0,msg:"postData.company_id"}) : "";
        postData.prepayment == "" || postData.shop_id == null ? this.transactionError.push({status:0,msg:"postData.prepayment"}) : "";
        //  ***** end trancation control  ****

        let result =  await super.post("cardshop/orderform/create",{orderdata:postData});

        // handle EarlyOrders
        if($( "#OrderFormEarlyordersDeleveryAdress" ).find(".newAdditionalDeleveryAdress").length > 0){
            this.handelEarlyOrders(result.data.result[0].id);
            await super.post("cardshop/earlyorder/sendcompanyorder/"+result.data.result[0].id,false);
        }

        // handle Product
        let orderItemsPostData = this.GetUpdateOrderItems();
        if(orderItemsPostData.length > 0){
            let products = await super.post("cardshop/orderform/updateitems/"+result.data.result[0].id,{orderitemlist:orderItemsPostData});
        }
        // --- end handle product --------


        // shipment
        let shipmentToParent = [];
        let invoiceCompanyCardshipment = {}
        let certificateBegin = result.data.result[0].certificate_no_begin;
        let certificateEnd = result.data.result[0].certificate_no_end;
        let certificateQuantity = result.data.result[0].quantity;
        let companyorder_id = result.data.result[0].id;
        invoiceCompanyCardshipment.companyorder_id = result.data.result[0].id;
        if(result.data.result[0].ship_to_company == "") {
            invoiceCompanyCardshipment.shipto_name =  result.data.result[0].company_name;
        } else {
            invoiceCompanyCardshipment.shipto_name =  result.data.result[0].ship_to_company;
        }

        invoiceCompanyCardshipment.shipto_address = result.data.result[0].ship_to_address;
        invoiceCompanyCardshipment.shipto_address2 = result.data.result[0].ship_to_address_2;
        invoiceCompanyCardshipment.shipto_postcode = result.data.result[0].ship_to_postal_code;
        invoiceCompanyCardshipment.shipto_city = result.data.result[0].ship_to_city;
        invoiceCompanyCardshipment.shipto_country = window.LANGUAGE;
        invoiceCompanyCardshipment.shipto_contact = "";
        invoiceCompanyCardshipment.shipto_email = "";
        invoiceCompanyCardshipment.shipto_phone = "";
        invoiceCompanyCardshipment.language_code   = window.LANGUAGE;


            //  *****  trancation control  ****

        invoiceCompanyCardshipment.companyorder_id == "" || invoiceCompanyCardshipment.companyorder_id == null  ? this.transactionError.push({status:0,msg:"invoiceCompanyCardshipment.companyorder_id"}) : "";
        invoiceCompanyCardshipment.shipto_name == "" || invoiceCompanyCardshipment.shipto_name == null  ? this.transactionError.push({status:0,msg:"invoiceCompanyCardshipment.shipto_name"}) : "";
        invoiceCompanyCardshipment.shipto_address == "" || invoiceCompanyCardshipment.shipto_address == null  ? this.transactionError.push({status:0,msg:"invoiceCompanyCardshipment.shipto_address"}) : "";
        invoiceCompanyCardshipment.shipto_postcode == "" || invoiceCompanyCardshipment.shipto_postcode == null  ? this.transactionError.push({status:0,msg:"invoiceCompanyCardshipment.shipto_postcode"}) : "";
        invoiceCompanyCardshipment.shipto_city == "" || invoiceCompanyCardshipment.shipto_city == null  ? this.transactionError.push({status:0,msg:"invoiceCompanyCardshipment.shipto_city"}) : "";
        invoiceCompanyCardshipment.shipto_country == "" || invoiceCompanyCardshipment.shipto_country == null  ? this.transactionError.push({status:0,msg:"invoiceCompanyCardshipment.shipto_country"}) : "";
        invoiceCompanyCardshipment.language_code == "" || invoiceCompanyCardshipment.shipto_email == null  ? this.transactionError.push({status:0,msg:"invoiceCompanyCardshipment.shipto_email"}) : "";

            //  *****  trancation control end ****

            if ($("#OrderFormAdditionalDeleveryAdress").find(".newAdditionalDeleveryAdress").length > 0){
                let usetCards = 0;
                let cardshipmentData = [];

                $("#OrderFormAdditionalDeleveryAdress").find(".newAdditionalDeleveryAdress").each( async function(i, obj) {
                        let Additional = {};
                        Additional.from_certificate_no = ( (certificateBegin *1) +usetCards)
                        Additional.to_certificate_no =  (Additional.from_certificate_no *1) + ($(obj).find(".AdditionalOrderFormAmount").val() * 1) - 1;
                        usetCards+= ($(obj).find(".AdditionalOrderFormAmount").val() * 1);
                        Additional.companyorder_id = companyorder_id;
                        Additional.shipto_name =  $(obj).find("#ship_to_company").val()
                        Additional.shipto_address = $(obj).find("#ship_to_address").val()
                        Additional.shipto_address2 = $(obj).find("#ship_to_address_2").val()
                        Additional.shipto_postcode = $(obj).find("#ship_to_postal_code").val()
                        Additional.shipto_city = $(obj).find("#ship_to_city").val()
                        Additional.shipto_country = window.LANGUAGE;
                        Additional.shipto_contact =  $(obj).find("#ship_to_attention").val()
                        Additional.shipto_email = "";
                        Additional.shipto_phone = $(obj).find('.ship_to_phone').val();
                        Additional.ship_to_country  = window.LANGUAGE;
                        Additional.language_code = window.LANGUAGE;

                        var deliveryData = {};
                        deliveryData.note = $(obj).find('.deliverynote').val();
                        deliveryData.dot_active = $(obj).find('.childusedot').is(":checked") ? "1":"0";
                        deliveryData.dot_price_type = $(obj).find('.childdotpricetype').val();
                        deliveryData.dot_price_amount = $(obj).find('.childdotpriceamount').val();
                        deliveryData.dot_description = $(obj).find('.childdotdescription').val();
                        deliveryData.carryup_active = $(obj).find('.childusecarryup').is(":checked") ? "1":"0";
                        deliveryData.carryup_price_type = $(obj).find('.childcarryuppricetype').val();
                        deliveryData.carryup_price_amount = $(obj).find('.childcarryuppriceamount').val();
                        deliveryData.carryup_type = $(obj).find('.childcarryuptype:checked').val();

                        // Send cards to child or send to parent

                       $(obj).find(".sendToParent").is(":checked") ? shipmentToParent.push(Additional):cardshipmentData.push(Additional);
                       if(ME.alternativCardShipping == true){
                            window.alwaysDev.push(Additional);
                       }

                       // check if delevery adress exist else create
                       let companyid = $(obj).find(".OrderFormChildListNewAdress").find(':selected').attr('company-id');
                       if($(obj).find(".OrderFormChildListNewAdress").val() == "none"){
                         let newAdress = await ME.createNewDeleveryAdress(Additional);
                         companyid = newAdress.data.result[0].id;
                         ME.updateCompanySearch();
                       }
                       await ME.MoveCards(companyorder_id,companyid,Additional.from_certificate_no,Additional.to_certificate_no,deliveryData);
                })
                 if(postData.is_email == 0  && ME.alternativCardShipping == false){
                    const promises = cardshipmentData.map(async data => {
                    await ME.CreateCardshipment(data);
                })

                // prepare shiptment note to parent
                let shipment_note = "";
                let child_cert_start = 0;
                let child_cert_end = 0;

                shipmentToParent.forEach( async function(obj) {
                    if(this.access.validate(window.USERID,5)){


                    let shipmentChild = {};
                    shipmentChild.from_certificate_no = obj.from_certificate_no
                    shipmentChild.to_certificate_no =  obj.to_certificate_no
                    if(child_cert_start == 0 || child_cert_start > obj.from_certificate_no) child_cert_start = obj.from_certificate_no;
                    if(child_cert_end == 0 || child_cert_end < obj.to_certificate_no) child_cert_end = obj.to_certificate_no;

                    shipmentChild.companyorder_id = companyorder_id;
                    shipmentChild.shipto_name =   obj.shipto_name
                    shipmentChild.shipto_address = obj.shipto_address
                    shipmentChild.shipto_address2 = obj.shipto_address2
                    shipmentChild.shipto_postcode = obj.shipto_postcode
                    shipmentChild.shipto_city = obj.shipto_city
                    shipmentChild.shipto_country = window.LANGUAGE;
                    shipmentChild.shipto_contact =  ""
                    shipmentChild.shipto_email = "";
                    shipmentChild.shipto_phone = "";
                    shipmentChild.language_code = window.LANGUAGE;
                    shipmentChild.uselink = true
                    shipmentChild.series_master = false
                    shipmentChild.series_uuid = window.uu_id;
                    shipment_note = "Har folge seddel";
                    console.log(shipmentChild);


                    await ME.CreateCardshipment(shipmentChild);



                      } else {



                    let antal =   (parseInt(obj.to_certificate_no) - parseInt(obj.from_certificate_no)) + 1 ;

                    shipment_note+= " \n ";
                    shipment_note+= obj.from_certificate_no + " - " + obj.to_certificate_no + " ( "+antal+" stk. )  \n ";
                    shipment_note+= obj.shipto_name + " \n ";
                    shipment_note+= obj.shipto_address + " \n ";
                    if(obj.shipto_address2  != ""){
                          shipment_note+= obj.shipto_address2 + " \n ";
                    }
                    shipment_note+= obj.shipto_postcode + " \n ";
                    shipment_note+= obj.shipto_city + " \n ";
                    shipment_note+= "\n ---------------- END ---------------- \n";

                    }
                });

                // Create shipment for parent
                invoiceCompanyCardshipment.from_certificate_no = 0;
                invoiceCompanyCardshipment.to_certificate_no = 0;
                if(  (usetCards*1) < (certificateQuantity *1) || shipment_note != ""  ){
                    if(  (usetCards*1) < (certificateQuantity *1) ) {
                        invoiceCompanyCardshipment.from_certificate_no = ( (certificateBegin *1) +usetCards)
                        invoiceCompanyCardshipment.to_certificate_no =   certificateEnd;
                        invoiceCompanyCardshipment.shipment_note = shipment_note;
                        if(this.access.validate(window.USERID,6)){
                            invoiceCompanyCardshipment.uselink = true
                            invoiceCompanyCardshipment.series_master = true
                            invoiceCompanyCardshipment.series_uuid = window.uu_id;
                        }
                    }
                    if(this.access.validate(window.USERID,7)){
                        if(invoiceCompanyCardshipment.from_certificate_no == 0 || invoiceCompanyCardshipment.to_certificate_no == 0) {
                            invoiceCompanyCardshipment.from_certificate_no = child_cert_start
                            invoiceCompanyCardshipment.to_certificate_no = child_cert_end;
                            invoiceCompanyCardshipment.uselink = true
                            invoiceCompanyCardshipment.series_master = true
                            invoiceCompanyCardshipment.series_uuid = window.uu_id;
                        }
                    }
                    await this.CreateCardshipment(invoiceCompanyCardshipment);
                }

               }

            } else {
                if(postData.is_email == 0 && this.alternativCardShipping == false){
                    invoiceCompanyCardshipment.from_certificate_no = result.data.result[0].certificate_no_begin;
                    invoiceCompanyCardshipment.to_certificate_no =  result.data.result[0].certificate_no_end;
                    await this.CreateCardshipment(invoiceCompanyCardshipment);
                }
            }
        // if alternative shipment is activatet
            if(this.alternativCardShipping == true){
            let alternativeShipment = {}
            alternativeShipment.from_certificate_no = certificateBegin
            alternativeShipment.to_certificate_no =  certificateEnd

            alternativeShipment.companyorder_id = companyorder_id;
            alternativeShipment.shipto_name =  $("#alternativCardShippingForm").find("#ship_to_company").val()
            alternativeShipment.shipto_address = $("#alternativCardShippingForm").find("#ship_to_address").val()
            alternativeShipment.shipto_address2 = $("#alternativCardShippingForm").find("#ship_to_address_2").val()
            alternativeShipment.shipto_postcode = $("#alternativCardShippingForm").find("#ship_to_postal_code").val()
            alternativeShipment.shipto_city = $("#alternativCardShippingForm").find("#ship_to_city").val()
            alternativeShipment.shipto_country = window.LANGUAGE;
            alternativeShipment.shipto_contact =  $("#alternativCardShippingForm").find("#ship_to_attention").val()
            alternativeShipment.shipto_email = "";
            alternativeShipment.shipto_phone = "";
            alternativeShipment.ship_to_country  = window.LANGUAGE;
            alternativeShipment.language_code = window.LANGUAGE;
                if(this.access.validate(window.USERID,8)){

                    alternativeShipment.uselink = true
                    alternativeShipment.series_master = true
                    alternativeShipment.series_uuid = window.uu_id;

             }

            await this.CreateCardshipment(alternativeShipment);
                if(this.access.validate(window.USERID,9)){

                    if(window.alwaysDev.length > 0){
                  window.alwaysDev.forEach( async function(obj) {



                    let shipmentChild = {};

                    shipmentChild.from_certificate_no = obj.from_certificate_no
                    shipmentChild.to_certificate_no =  obj.to_certificate_no

                    shipmentChild.companyorder_id = companyorder_id;
                    shipmentChild.shipto_name =   obj.shipto_name
                    shipmentChild.shipto_address = obj.shipto_address
                    shipmentChild.shipto_address2 = obj.shipto_address2
                    shipmentChild.shipto_postcode = obj.shipto_postcode
                    shipmentChild.shipto_city = obj.shipto_city
                    shipmentChild.shipto_country = window.LANGUAGE;
                    shipmentChild.shipto_contact =  ""
                    shipmentChild.shipto_email = "";
                    shipmentChild.shipto_phone = "";
                    shipmentChild.language_code = window.LANGUAGE;
                    shipmentChild.uselink = true
                    shipmentChild.series_master = false
                    shipmentChild.series_uuid = window.uu_id;

                    console.log(shipmentChild);
                    await ME.CreateCardshipment(shipmentChild);

              })
               }
            }



            }

        ME.orderInprocess = false;

        super.Toast("Order is created");
        $('#ModalFullscreen').modal('hide');

        console.log(this.transactionError);

        // Frigiver ordre til nav
        await super.post("cardshop/orderform/navready/"+companyorder_id,{});
        // Frigiver til pakning
        await super.post("cardshop/cardshipment/sendcompanyorder/"+companyorder_id,{});
        new Cards(this.companyID);
          // Intern Information
        // updata companylist

    }

    validateNewOrderForm()
    {
        let self = this;
        let error = false;
        return new Promise(resolve => {
            self.resetNewOrderValidate();
            $( ".madatory-select" ).each(function( index ) {
                if($(this).val() == null ){
                    $(this).css('background-color', 'red');
                    error = true;
                }
            });
            if($(".madatory-radio:checked").val() == undefined){
                $(".madatory-radio").css('background-color', 'red');
                error = true;
            }
            $( ".madatory-text" ).each(function( index ) {
                if($(this).val() == "" ){
                    $(this).css('background-color', 'red');
                    error = true;
                }
            });
            if(self.validateEarlyOrderForm() == true){
                error = true;
            }
            resolve(error);
        });
    }
    validateEarlyOrderForm()
    {
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
            // set order adress
            if(jQuery.isEmptyObject(earlyData) ){
                $( ".earlyOrder."+elementID).css('background-color', 'red');
                error = true;
            }

            if( !("itemno" in earlyData)  || !("quantity" in earlyData)){
                $( ".earlyOrder."+elementID).css('background-color', 'red');
                error = true;
            }


        });
         return error;
    }

    resetNewOrderValidate(){
            $( ".madatory-select" ).each(function( index ) {
               $(this).css('background-color', '');
            });
            $(".madatory-radio").css('background-color', '');
            $( ".madatory-text" ).each(function( index ) {
                $(this).css('background-color', '');
            });


    }

    async handelEarlyOrders(companyorder_id){
        let self = this;
        let earlyItems = [];
        let earlyItemCounter = 1;
        let earlyData = {};
        let earlyOrders = [];
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

            earlyData.shipto_name       = $( "."+elementID+".ship_to_company").val();
            earlyData.shipto_address    = $( "."+elementID+".ship_to_address").val();
            earlyData.shipto_address2   = $( "."+elementID+".ship_to_address_2").val();
            earlyData.shipto_postcode   = $( "."+elementID+".ship_to_postal_code").val();
            earlyData.shipto_city       = $( "."+elementID+".ship_to_city").val();
            earlyData.shipto_country    = window.LANGUAGE;
            earlyData.shipto_contact    = $( "."+elementID+".AdditionalOrderFormContact").val();
            earlyData.shipto_email      = $( "."+elementID+".AdditionalOrderFormEmail").val();
            earlyData.shipto_phone      = $( "."+elementID+".AdditionalOrderFormMobile").val();
            earlyData.companyorder_id   = companyorder_id

            // trancation control
            earlyData.shipto_name == "" || earlyData.shipto_name == null  ? self.transactionError.push({status:0,msg:"earlyData.shipto_name"}) : "";
            earlyData.shipto_address == "" || earlyData.shipto_address == null ? self.transactionError.push({status:0,msg:"earlyData.shipto_address"}) : "";
            earlyData.shipto_postcode == "" || earlyData.shipto_postcode == null ? self.transactionError.push({status:0,msg:"earlyData.shipto_postcode"}) : "";
            earlyData.shipto_city == "" || earlyData.shipto_city == null  ? self.transactionError.push({status:0,msg:"earlyData.shipto_city"}) : "";
            earlyData.shipto_country == "" || earlyData.shipto_country == null ? self.transactionError.push({status:0,msg:"earlyData.shipto_country"}) : "";
            earlyData.shipto_contact == "" || earlyData.shipto_contact == null ? self.transactionError.push({status:0,msg:"earlyData.shipto_contact"}) : "";
            earlyData.shipto_email == "" || earlyData.shipto_email == null ? self.transactionError.push({status:0,msg:"earlyData.shipto_email"}) : "";
            earlyData.shipto_phone == "" || earlyData.shipto_phone == null ? self.transactionError.push({status:0,msg:"earlyData.shipto_phone"}) : "";
            earlyData.companyorder_id == "" || earlyData.companyorder_id == null ? self.transactionError.push({status:0,msg:"earlyData.companyorder_id"}) : "";
            earlyData.itemno == ""  || earlyData.itemno == null ? self.transactionError.push({status:0,msg:"earlyData.itemno"}) : "";
            parseInt(earlyData.quantity) == 0  || earlyData.shipto_name == null ? self.transactionError.push({status:0,msg:"earlyData.quantity"}) : "";
            // trancation control end

            earlyOrders.push(earlyData);

        });
            return new Promise(resolve => {
                earlyOrders.map(async earlyOrder => {
                    await self.CreateEarlyOrder(earlyOrder)
                })
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
    async MoveCards(companyorder_id,companyid,start,end,deliveryData){
        return new Promise(async resolve => {
            await super.post("cardshop/cards/movecards/"+companyorder_id,{
                companyid:companyid,
                startcertificate:start,
                endcertificate:end,
                deliveryData:deliveryData
            });
            resolve();
        })
    }

    async CreateCardshipment(postData){

        let ME = this;
        return new Promise(async resolve => {
            await super.post("cardshop/cardshipment/create",{shipmentdata:postData});
            resolve();
        });
    }

    async createNewDeleveryAdress(data){

        let earlyOrderData = {};

        earlyOrderData.language_code =         window.LANGUAGE;
        earlyOrderData.ship_to_company =       data.shipto_name
        earlyOrderData.ship_to_attention =     data.shipto_contact
        earlyOrderData.ship_to_address =       data.shipto_address
        earlyOrderData.ship_to_address_2 =     data.shipto_address2
        earlyOrderData.ship_to_postal_code =   data.shipto_postcode
        earlyOrderData.ship_to_city =          data.shipto_city
        earlyOrderData.ship_to_phone =          data.shipto_phone
        earlyOrderData.pid =                   this.companyID;

        earlyOrderData.language_code =  window.LANGUAGE;
        earlyOrderData.cvr = "11111111"
        earlyOrderData.name = "child"
        earlyOrderData.bill_to_address = "11111111"
        earlyOrderData.bill_to_address_2 = ""
        earlyOrderData.bill_to_postal_code = "11111111"
        earlyOrderData.bill_to_city = "11111111"
        earlyOrderData.ean = "11111111"
        earlyOrderData.bill_to_email = "11111111"
        earlyOrderData.contact_name = "11111111"
        earlyOrderData.contact_phone = "11111111"
        earlyOrderData.contact_email = "11111111@11111111.dk"

        // trancation control
        earlyOrderData.language_code == "" || earlyOrderData.language_code == null  ? this.transactionError.push({status:0,msg:"earlyOrderData.language_code"}) : "";
        earlyOrderData.ship_to_company == "" || earlyOrderData.ship_to_company == null  ? this.transactionError.push({status:0,msg:"earlyOrderData.ship_to_company"}) : "";
        earlyOrderData.ship_to_attention == "" || earlyOrderData.ship_to_attention == null  ? this.transactionError.push({status:0,msg:"earlyOrderData.ship_to_attention"}) : "";
        earlyOrderData.ship_to_address == "" || earlyOrderData.ship_to_address == null  ? this.transactionError.push({status:0,msg:"earlyOrderData.ship_to_address"}) : "";
        earlyOrderData.ship_to_city == "" || earlyOrderData.ship_to_city == null  ? this.transactionError.push({status:0,msg:"earlyOrderData.ship_to_city"}) : "";
        earlyOrderData.pid == "" || earlyOrderData.pid == null  ? this.transactionError.push({status:0,msg:"earlyOrderData.pid"}) : "";
        earlyOrderData.language_code == "" || earlyOrderData.language_code == null  ? this.transactionError.push({status:0,msg:"earlyOrderData.language_code"}) : "";
        // trancation control end

        return new Promise(async resolve => {
            let result = await super.post("cardshop/companyform/create",{companydata:earlyOrderData});
            resolve(result);
        })
    }


    CheckCardAmount(){
         let totalCards = 0;
         $('.AdditionalOrderFormAmount').each(function(i, obj) {
             totalCards+= ($(obj).val()*1);
         })
         return totalCards;
    }


    GetUpdateOrderItems(){
        let self = this;
        let postData = []

        $('.AdditionalProductsActivate').each(function(i, obj) {

                var type = $(obj).attr("data-id");
                var defaultValue  = ( $(obj).attr("defaultValue") == $("#"+type).val() ) ? 1:0;
                let quantity =  $(obj).is(":checked") ? "1":"0";

                type == "" || type == null  ? self.transactionError.push({status:0,msg:"postData.prepayment"}) : "";
                type == "" || type == null  ? self.transactionError.push({status:0,msg:"postData.prepayment"}) : "";
                type == "" || type == null  ? self.transactionError.push({status:0,msg:"postData.prepayment"}) : "";
                type == "" || type == null ? self.transactionError.push({status:0,msg:"postData.prepayment"}) : "";

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
    updateCompanySearch(){
      let InsetInSearch = $(".cardshop #companylist-search").val()
      let cl = new Companylist;
      cl.InsetInSearch(InsetInSearch);
      cl.SearchAndSelect( );
    }


    Rand(){
        return Math.random().toString(36).substr(2);
    }
    Token(){
       return this.Rand() + this.Rand();
    }
    uuidv4() {
        return ([1e7]+-1e3+-4e3+-8e3+-1e11).replace(/[018]/g, c =>
            (c ^ crypto.getRandomValues(new Uint8Array(1))[0] & 15 >> c / 4).toString(16)
        );
    }

    generateFormReport(selector) {

        var ME = this;

        try {
            // Tjek om selector eksisterer i DOM
            if ($(selector).length === 0) {
                return "Fejl: Element med selector '" + selector + "' blev ikke fundet.";
            }

            let report = "ORDRE FORMULAR RAPPORT\n";
            report += "Genereret: " + new Date().toLocaleString() + "\n";
            report += "Selector: " + selector + "\n";
            report += "=".repeat(50) + "\n\n";

            // Find alle relevante elementer i den korrekte DOM-rækkefølge
            const allElements = $(selector).find("fieldset, legend, input, select, textarea, button[type='submit']");

            if (allElements.length === 0) {
                return report + "Ingen relevante elementer fundet indenfor selectoren.";
            }

            // Hold styr på radioknapper, så vi kun viser dem én gang
            const processedRadios = new Set();

            // Gennemgå hvert element i DOM-rækkefølgen
            allElements.each(function(index) {
                const $element = $(this);
                const tagName = this.tagName.toLowerCase();

                // Håndter legend elements (overskrift til fieldset)
                if (tagName === 'legend') {
                    const legendText = $element.text().trim();
                    report += `OVERSKRIFT: ${legendText}\n`;
                    report += "-".repeat(legendText.length + 11) + "\n\n";
                    return; // Skip til næste element
                }

                // Spring fieldset over, men behold indholdet
                if (tagName === 'fieldset') {
                    return; // Skip til næste element
                }

                // For formular-elementer, tilføj detaljeret information
                if (['input', 'select', 'textarea', 'button'].includes(tagName)) {
                    const type = $element.attr("type") || tagName;
                    const name = $element.attr("name") || $element.attr("id") || "Unavngivet felt " + (index + 1);

                    // Specialhåndtering af radioknapper
                    if (type === "radio") {
                        const radioGroupKey = `radio-${name}`;

                        // Hvis vi allerede har behandlet denne radiogruppe, så spring over
                        if (processedRadios.has(radioGroupKey)) {
                            return;
                        }

                        // Find den valgte radio i denne gruppe
                        const $selectedRadio = $(`input[type="radio"][name="${name}"]:checked`);

                        // Hvis ingen er valgt, så vis det
                        if ($selectedRadio.length === 0) {
                            const label = ME.findGroupLabel(name) || name;
                            report += `Felt: ${label}\n`;
                            report += `Værdi: Intet valgt\n`;
                            report += `Teknik: VIST, type: radio, navn: ${name}\n\n`;
                        } else {
                            // Vis info om den valgte radio
                            const value = $selectedRadio.val() || "Ingen værdi";
                            const label = ME.findLabelText($selectedRadio) || ME.findGroupLabel(name) || name;
                            const isHidden = $selectedRadio.is(":hidden") ||
                                $selectedRadio.css("display") === "none" ||
                                $selectedRadio.css("visibility") === "hidden";
                            const isDisabled = $selectedRadio.prop("disabled");
                            const isRequired = $selectedRadio.prop("required");

                            report += `Felt: ${label}\n`;
                            report += `Værdi: ${value} (Valgt)\n`;

                            // Tekniske detaljer samlet på én linje
                            let techDetails = `Teknik: ${isHidden ? "SKJULT" : "VIST"}, type: radio, navn: ${name}`;
                            if (isDisabled) techDetails += ", DEAKTIVERET";
                            if (isRequired) techDetails += ", PÅKRÆVET";
                            report += techDetails + "\n\n";
                        }

                        // Marker denne radiogruppe som behandlet
                        processedRadios.add(radioGroupKey);

                    } else {
                        // Normal håndtering af andre felttyper
                        const value = $element.val() || "Ingen værdi";
                        const label = ME.findLabelText($element) || name;
                        const isHidden = $element.is(":hidden") || $element.css("display") === "none" || $element.css("visibility") === "hidden";
                        const isDisabled = $element.prop("disabled");
                        const isRequired = $element.prop("required");

                        // Felt label/navn
                        report += `Felt: ${label}\n`;

                        // Værdi information FØRST
                        if (type === "checkbox") {
                            const isChecked = $element.prop("checked");
                            report += `Værdi: ${value} (${isChecked ? "Valgt" : "Ikke valgt"})\n`;
                        } else if (tagName === "select") {
                            const selectedText = $element.find("option:selected").text();
                            report += `Værdi: ${value} (${selectedText})\n`;

                            // For multi-select, vis antal valgte
                            if ($element.prop("multiple") && $element.val() && $element.val().length > 0) {
                                report += `Multiple valg: ${$element.val().length} valgt\n`;
                            }
                        } else {
                            report += `Værdi: ${value}\n`;
                        }

                        // Tekniske detaljer samlet på én linje
                        let techDetails = `Teknik: ${isHidden ? "SKJULT" : "VIST"}, type: ${type}, navn: ${name}`;
                        if (isDisabled) techDetails += ", DEAKTIVERET";
                        if (isRequired) techDetails += ", PÅKRÆVET";
                        report += techDetails + "\n\n";
                    }
                }
            });

            return report;
        } catch (error) {
            return "Fejl ved generering af rapport: " + error.message;
        }
    }

    findLabelText($element) {
        // Prøv at finde label via 'for' attribut
        const id = $element.attr("id");
        if (id) {
            const label = $("label[for='" + id + "']").text().trim();
            if (label) return label;
        }

        // Prøv at finde label som parent
        const parentLabel = $element.closest("label").clone()
            .children().remove().end()
            .text().trim();

        if (parentLabel) return parentLabel;

        // Prøv at finde en tætliggende overskrift eller andre labels
        const nearestHeading = $element.prev("h1, h2, h3, h4, h5, h6, label, span.label").text().trim();
        if (nearestHeading) return nearestHeading;

        return null;
    }


    findGroupLabel(name) {

        const $firstRadio = $(`input[type="radio"][name="${name}"]:first`);

        // Tjek for en fieldset legend over gruppen
        const $fieldset = $firstRadio.closest('fieldset');
        if ($fieldset.length > 0) {
            const legend = $fieldset.find('legend').first().text().trim();
            if (legend) return legend;
        }

        // Tjek for et DIV eller lignende container med en overskrift
        const $container = $firstRadio.closest('div, section, article');
        if ($container.length > 0) {
            const heading = $container.find('h1, h2, h3, h4, h5, h6').first().text().trim();
            if (heading) return heading;
        }

        // Hvis ingen af ovenstående, så brug blot navnet
        return name;
    }


}