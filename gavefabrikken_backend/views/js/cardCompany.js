var _shopHasChild
var _cardSogData;
var _selectedCompany;
var _selectedShop;
var _homeSend;
var _shopCardId;
var _transferCardList= [];
var _iterator = 0;
var _shortCutId;
var _userData;
var _userId;
var _newPresent;
var shopIdList = [];
shopIdList["id52"] = "Julegavekortet";
shopIdList["id53"] = "Guldgavekortet";
shopIdList["id54"] = "24gaver 400";
shopIdList["id55"] = "24gaver 560";
shopIdList["id56"] = "24gaver 640";
shopIdList["id272"] = "Jgk Norge 300";
shopIdList["id57"] = "Jgk Norge 400";
shopIdList["id58"] = "Jgk Norge 560";
shopIdList["id59"] = "Jgk Norge 560";
shopIdList["id265"] = "julegavetypen";
shopIdList["id574"] = "Guldgavekortet Norge";
shopIdList["id575"] = "Design";
shopIdList["id1832"] = "24julklappar - 440";
shopIdList["id9495"] = "24julklappar AI - 440";
shopIdList["id1981"] = "24julklappar - 800";
shopIdList["id4793"] = "24julklappar - 300";
shopIdList["id5117"] = "24julklappar - 600";
shopIdList["id287"] = "dromme 100";
shopIdList["id290"] = "dromme 200";
shopIdList["id310"] = "dromme 300";


[""].id52 = ["52","53","54","55","56","57","58","59","265","287","290","310","272","574","575","1981"];



var cardCompany = {
    /*
        Admin af order mail til kunderne
    */
    /* add multible deleveriry addres */
    setAction:function(){
       $(".changeHomeDeleveryStatus").hide();
       $("#multiCreateBtn").unbind('click').click(function(){
         cardCompany.openMultiCreateDialog();
       })
       $("#CSMCupload").unbind('click').click(function(){
          cardCompany.uploadMultiCreate();
       })

    },
    changeShippingDeal:function(){
      let r = confirm("Do you want to change shipping deal?")
      if(r== false) return;
       $.ajax(
           {
              url: 'index.php?rt=company/updateShippingDeal',
              type: 'POST',
              data: {companyID:_selectedCompany,cost:$("#chippingNewDeal").val()}
           }).done(function(res) {
               $("#currentShippingDeal").html(res)
               $("#chippingNewDeal").val("");
       })
    },
    readShippingDeal:function(){
        $.ajax(
           {
              url: 'index.php?rt=company/readShippingDeal',
              type: 'POST',
              data: {companyID:_selectedCompany}
           }).done(function(res) {
               $("#currentShippingDeal").html(res)
       })

    },
    openMultiCreateDialog:function(){
        cardCompany.clearMultiCreateDialog();
        dialog =  $( "#dialog-CardShop-MultiCreate" ).dialog({
            title: 'Gavekort',
            autoOpen: true,
            height: 550,
            width: 550,
            modal: true,
            buttons: {
                    Cancel: function() {
                    dialog.dialog( "close" );
                }

        }
        });
    },
   showDistributeToChild: async function(){

        companyList = await cardAddNewCard.loadChildAddr();

        dialog =  $( "#dialog-multi-move-to-child" ).dialog({
            title: 'Gavekort',
            autoOpen: true,
            height: 550,
            width: 550,
            modal: true,
            buttons: {
                Cancel: function() {
                dialog.dialog( "close" );
            },
                Transfer :function() {
                cardCompany.doDistributeToChild();
            }
        }
        });
        if(companyList.status == 1){
            cardCompany.buildDistributeToChildHtml(companyList.data.result);
        } else {
          alert("An error has occurred")
        }

    },
    buildDistributeToChildHtml:function(data){
        let html = "<table width=100%>";
        for(var i=0;i < data.length;i++){
            html+="<tr ><td width=80%>"+data[i].ship_to_company+"<br>"+data[i].ship_to_city+"<br>"+data[i].ship_to_postal_code+"<br>"+data[i].contact_name+"<br>"+data[i].contact_email+"</td>";
            html+="<td  width=20%> <input id='"+data[i].id+"' class='childToReceive'  type='number' onkeyup='cardCompany.distributeToChildCounter()' /></td>";
            html+="</tr>";
        }
        html+="</table>";
        if(data.length == 0){ html="<p>Denne virksomhed har ingen ekstra adresser tilknyttet</p>" }
        $("#dialog-multi-move-to-child").html(html);
    },
    distributeToChildCounter:function(){
       $(".childToReceive").css("color","black");
        let i = 0
        let cardCount = 0;
        $(".childToReceive").each(function( index ) {
            i+= $(this).val()*1;
        })
        $(".cardAction").each(function( index ) {
                if( $("#"+this.id).is(":visible") == true){
                    if( $("#"+this.id).is(":checked") == true){
                        cardCount++;
                    }
                }
      })

      if(i > cardCount){
        $(".childToReceive").css("color","red");
      }
    },
    doDistributeToChild:function(){
        let i = 0
        let cardCount = 0;
        $(".childToReceive").each(function( index ) {
            i+= $(this).val()*1;
        })
        $(".cardAction").each(function( index ) {
                if( $("#"+this.id).is(":visible") == true){
                    if( $("#"+this.id).is(":checked") == true){
                        cardCount++;
                    }
                }
      })

      if(i > cardCount){
        alert("too many cards selected")
        return;
      }
      var cardToMove = [];
      var targetCompany = [];
      $(".cardAction").each(function( index ) {
                if( $("#"+this.id).is(":visible") == true){
                    if( $("#"+this.id).is(":checked") == true){
                        cardToMove.push(this.id);
                    }
                }
      })
      $(".childToReceive").each(function( index ) {

          for(var i=0;i<$(this).val()*1;i++){
              targetCompany.push({company_id:$(this).attr("id"),user_id:cardToMove[0]});
              cardToMove = cardToMove.slice(1, cardToMove.length);
          }


      })

        targetCompany.forEach(async (item) => {
             await cardCompany.doMoveCardToChild(item)
        })
        dialog.dialog( "close" );
      alert("Card transfer is done")

         $.ajax(
                  {
                  url: 'index.php?rt=company/updateCardCount',
                  type: 'GET',
                  data: {}
                  }).done(function() {
                    cardCompany.sogShowCardCompany(_selectedCompany)
                  })



    },
    doMoveCardToChild:function(item){
         return new Promise(function(resolve, reject) {
         $.ajax(
            {
            url: 'index.php?rt=shop/moveShopUser',
            type: 'POST',
            dataType: 'json',
            data: item
            }).done(function(res) {
            if(res.status == 0) { resolve(res) }
            else { resolve(res) }
          })
        })
    },
    clearMultiCreateDialog:function(){
        $('#CSMCfile').val('')
        $(".ship_to_company_node").val("");
        $(".ship_to_address_node").val("");
        $(".ship_to_address_2_node").val("");
        $(".ship_to_postal_code_node").val("");
        $(".ship_to_city_node").val("");
        $(".contact_name_node").val("");
        $(".contact_phone_node").val("");
        $(".contact_email_node").val("");
    },
    uploadMultiCreate:function(){
         $("#pt-progress-small").show();
      $.ajax(
        {

        url: 'index.php?rt=upload/file',
        type: 'POST',
        data: new FormData($('.uploadFileCSMC')[0]),
        cache: false,
        contentType: false,
        processData: false,
        xhr: function () {
            var myXhr = $.ajaxSettings.xhr();
            if (myXhr.upload) {
              myXhr.upload.addEventListener('CSMC-progress', function (e)
                {
                  if (e.lengthComputable) {
                    $('CSMC-progress').attr(
                      {
                      value: e.loaded,
                      max: e.total,
                      }
                    );
                  }
                }, false
              );
            }
            return myXhr;
          }
        }
      ).done( function(res) {
          res = JSON.parse(res);
          $("#CSMC-progress").hide();
          if(res.status==1 ){

            cardCompany.MultiCreateDataHandler(res.path)
          } else {
            alert("Upload error")
          }

      });

    },
    MultiCreateDataHandler: function(path){
          var formData = {
            'name':$(".dialog1_name_Show").val(),
            'bill_to_address':$(".dialog1_bill_to_address_Show").val(),
            'bill_to_address_2':$(".dialog1_bill_to_address_2_Show").val(),
            'bill_to_postal_code':$(".dialog1_bill_to_postal_code_Show").val(),
            'bill_to_city':$(".dialog1_bill_to_city_Show").val(),
            'cvr':$(".dialog1_cvr_Show").val(),
            'ean':$(".dialog1_ean_Show").val(),
            'pid':_selectedCompany,
            'path':path}
             $.ajax(
                  {
                  url: 'index.php?rt=companyTree/addMultiNodes',
                  type: 'POST',
                  dataType: 'json',
                  data: {'data':formData}
                  }).done(function(res) {
                  if(res.status == 0) { alert(res.message) }
                  else {
                    $( "#dialog-CardShop-MultiCreate" ).dialog( "close" );
                    alert("Import job done");
                    cardCompany.sogShowCardCompany($(".dialog1_cvr_Show").val())
                  }
                })
    },
    getMultiCreateData:function(path){
         return new Promise(function(resolve, reject) {
         $.ajax(
            {
            url: 'index.php?rt=company/createGiftCertificateCompany',
            type: 'POST',
            dataType: 'json',
            data: {'companydata':postData}
            }).done(function(res) {
            if(res.status == 0) { resolve(res) }
            else { resolve(res) }
          })
        })
    },



    newCompanyNode: async function(){
        // get company info fra parent node
         var formData = {
            'name':$(".dialog1_name_Show").val(),
            'bill_to_address':$(".dialog1_bill_to_address_Show").val(),
            'bill_to_address_2':$(".dialog1_bill_to_address_2_Show").val(),
            'bill_to_postal_code':$(".dialog1_bill_to_postal_code_Show").val(),
            'bill_to_city':$(".dialog1_bill_to_city_Show").val(),
            'cvr':$(".dialog1_cvr_Show").val(),
            'ean':$(".dialog1_ean_Show").val(),

            'ship_to_company':$(".ship_to_company_node").val(),
            'ship_to_address':$(".ship_to_address_node").val(),
            'ship_to_address_2':$(".ship_to_address_2_node").val(),
            'ship_to_postal_code':$(".ship_to_postal_code_node").val(),
            'ship_to_city':$(".ship_to_city_node").val(),
            'contact_name':$(".contact_name_node").val(),
            'contact_phone':$(".contact_phone_node").val(),
            'contact_email':$(".contact_email_node").val(),
            'pid':_selectedCompany

        };
        let result = await cardCompany.createCompanyNode(formData);
        if(result.status == 1){
            $( "#dialog-CardShop-MultiCreate" ).dialog( "close" );
            alert("Job done");
        } else {
           alert(result.message);
        }

    },
    createCompanyNode:function(postData){
         return new Promise(function(resolve, reject) {
         $.ajax(
            {
            url: 'index.php?rt=company/createGiftCertificateCompany',
            type: 'POST',
            dataType: 'json',
            data: {'companydata':postData}
            }).done(function(res) {
            if(res.status == 0) { resolve(res) }
            else { resolve(res) }
          })
        })
    },


    loadOrderData:function(){
      cardCompany.setAction();
      setTimeout(function(){
        ajax({"company_id":_selectedCompany},"company/getSpDealsOrders","cardCompany.showOrderData","");
      }, 500)

    },
    showOrderData: async function(responce){

        var html = "<tr><td>BS-nummer</td><td>Dato</td><td>Kort</td><td>Kort start</td><td>Kort slut</td><td>Send status</td><td>NAV SYNC</td><td>Antal</td><td>Udl&oslash;bs dato</td><td>early gave</td><td width=60>Shipment</td></tr>";
        var obj =  responce.data.result;
        for(var i=0;i < obj.length;i++){
            var isSend = "Sendt";
            var sendMail = "";
            var iserror = "";
        //    if(	obj[i].navsync_status == "3" 	obj[i].navsync_status == "3" || ) iserror = "Fejl ";
            if(	obj[i].welcome_mail_is_send == "0") isSend = "ej Sendt"
            if(	obj[i].send_welcome_mail == true) sendMail = "checked"
        //   html+="<tr><td>"+obj[i].order_no+"</td><td>"+obj[i].created_datetime+"</td><td>"+obj[i].shop_name+"</td><td>"+obj[i].certificate_no_begin+"</td><td>"+obj[i].certificate_no_end+"</td><td>"+isSend+"</td><td><input  onclick='cardCompany.changeSendMail("+obj[i].id+",this)' type='checkbox' "+sendMail+"  /></td><td><button onclick='cardCompany.sendOrderMail("+obj[i].id+")'>Send mail</button></td></tr>"
            var freeShipment = "";

            if(obj[i].antal > 0 && obj[i].pid == "0" && obj[i].is_shipped == 0){


              if(obj[i].shipment_ready == 0){
                    freeShipment = "<div class='freeShipment_"+obj[i].id+"'><br><label for='male'>Frigiv</label><input type='checkbox' id='frigiv_"+obj[i].id+"' class='freeShipment' data-id='"+obj[i].id+"'><hr>";
                    freeShipment+="<input type='radio' id='toFakt' name='shipmentTaget' value='1'>";
                    freeShipment+="<label for='male'>Fakt</label><br>";
                    freeShipment+="<input type='radio' id='toSub'  name='shipmentTaget' value='2'>";
                    freeShipment+="<label for='female'>Alle</label><br>";
                    freeShipment+= "<a href='https://system.gavefabrikken.dk//gavefabrikken_backend/index.php?rt=cardShipment/getbyId&token=f849yt5478eib74r6t89&id="+obj[i].id+"' target='blank'>Cards shipment</a><br><br></div>";
                    setTimeout(this.shipmentHasCard(obj[i].id), 100);
                }
                if(obj[i].shipment_ready == 1){
                    let shipmentTaget =   obj[i].shipment_ready_only_parent == 1  ? "Fakt." : "Alle";
                    freeShipment = "<div class='freeShipment_"+obj[i].id+"'><input type='checkbox' id='frigiv_"+obj[i].id+"' checked='checked' disabled > <label>"+shipmentTaget+"</label><br>";
                    freeShipment+= "<a href='https://system.gavefabrikken.dk//gavefabrikken_backend/index.php?rt=cardShipment/getbyId&token=f849yt5478eib74r6t89&id="+obj[i].id+"' target='blank'>Distribution</a></div>";
                    setTimeout(this.shipmentHasCard(obj[i].id), 100);
                }


            }
            if(obj[i].antal > 0 && obj[i].pid == "0" && obj[i].is_shipped == 1){
                freeShipment+= "<a href='https://system.gavefabrikken.dk//gavefabrikken_backend/index.php?rt=cardShipment/getbyId&token=f849yt5478eib74r6t89&id="+obj[i].id+"' target='blank'>Distribution</a>";
            }

            html+="<tr><td>"+obj[i].order_no+"</td><td>"+obj[i].created_datetime+"</td><td>"+obj[i].shop_name+"</td><td>"+obj[i].certificate_no_begin+"</td><td>"+obj[i].certificate_no_end+"</td><td>"+isSend+"</td><td>"+obj[i].navsync_status+"</td><td>"+obj[i].quantity+"</td><td>"+obj[i].expire_date+"</td><td>"+obj[i].earlyorderlist+"</td><td>"+freeShipment+"</td></tr>"
        }
        $("#orderSendAdmin").html(html)
        $('.freeShipment').unbind( "click" ).click(function() {
              var orderId = $(this).attr("data-id");
              if (!$("input[name='shipmentTaget']:checked").val()) {
                alert('You need to select if cards are shipped to the nvoice company \n or to the companies where the cards belongs to.');
                $("#frigiv_"+orderId).removeAttr('checked');
                return;
              }
              if(confirm("Do you want to free this order for shipment")){
                let shipmentConfig =   $('input[name="shipmentTaget"]:checked').val();
                cardCompany.freeShipment(orderId,shipmentConfig);
              } else {
                $("#frigiv_"+orderId).removeAttr('checked');
            }

        });
    },
    freeShipment:function(orderId,shipmentConfig){
       $.ajax(
           {
              url: 'index.php?rt=cardShipment/freeShipment',
              type: 'POST',
              data: {orderpost:orderId,shipmentConfig:shipmentConfig}
           }).done(function(res) {
              cardCompany.loadOrderData();
        })
    },
    shipmentHasCard:function(orderId){
             $.ajax(
           {
              url: 'index.php?rt=cardShipment/shipmentHasCard',
              type: 'POST',
              data: {orderId:orderId}
           }).done(function(res) {
               obj = JSON.parse(res);
               if((obj.data[0].number*1) == 0){
                   $(".freeShipment_"+orderId).html("All cards are deleted");
               }

        })
    },

    changeSendMail:function(id){
      ajax({"id":id},"company/changeSendOrderMail","cardCompany.changeSendMailRes","");
    },
    changeSendMailRes:function(responce){
      if(responce.data.result[0].send_welcome_mail == "0"){
           alert("Sender ikke mail")
      } else {
           alert("Sender  mail")
      }

    },
   sendOrderMail:function(id){
     var r = confirm("Sikker paa du vil sende mail");
     if (r == true) {
         ajax({"id":id},"company/sendOrderMail","cardCompany.sendOrderMailRes","");
     }

   },
   sendOrderMailRes:function(){

         alert("mail sendt")
   },

    loadHistory:function(userId){
            $( "#dialog_message" ).html("systemt arbejder, vent venligt")
            dialog =  $( "#dialog_message" ).dialog({
                title: 'Historik',
                autoOpen: true,
                height: 550,
                width: 550,
                modal: true,
                buttons: {
                    Cancel: function() {
                    dialog.dialog( "close" );
                }
            }
            });
        ajax({'userId':userId},"shop2/getCardHistory","cardCompany.loadHistoryResponse","");
    },
    loadHistoryResponse:function(response){

    if(response.data == 0){
            $( "#dialog_message" ).html("Der blev ikke fundet noget")
        } else {
            $( "#dialog_message" ).html(Base64.decode(response.data))
        }
    },
    sogWithReceipt:function(sogStr){
        if (sogStr != "") {
            $( "#currentSogContent" ).html("systemt arbejder, vent venligt")
            ajax({'ReceiptNr':sogStr},"shop2/receiptSeekCardInfo","cardCompany.sogWithReceiptResponse","");
        }
    },



    sogWithReceiptResponse:function(res){
        if(res.data == 0){
         $("#currentSogContent").html("Der blev ikke fundet noget");
        } else if(res.type == "valg"){

            var html = "<table width=60%>";
            html+="<tr bgcolor='#ddd'><td ></td><h2>VALG SHOP</h2><td></td></tr>";
            for(var j=0;res.data.length > j;j++) {

                html+="<tr bgcolor='#4CAF50' width=30%><td><b>Ordre nr.</b></td><td width=69% ><b>"+res.data[j].order_no+"</b></td></tr>";
                html+="<tr><td> Oprettet d.</td><td>"+res.data[j].order_timestamp+"</td></tr>";
                html+="<tr><td>Firmanavn</td><td>"+res.data[j].company_name+"</td></tr>";
                html+="<tr><td>Brugernavn</td><td>"+res.data[j].user_name+"</td></tr>";
                html+="<tr><td>Email</td><td>"+res.data[j].user_email+"</td></tr>";
                html+="<tr><td>Gave valgt</td><td>"+res.data[j].present_model_name+"</td></tr>";
                  html+="<tr><td colspan=2><hr></td></tr>";
            }
            html += "</table>";
            if(res.data.length == 0){
                 html = "Ingen data";
            }

            html += "</table>";






            } else {
                ajax({'card':res.data},"shop2/seekCardInfo","cardCompany.sogkortResponse","");
            }
             $("#currentSogContent").html(html)

    },

    sogkort:function(sogStr){

        if (sogStr != "") {
           $( "#currentSogContent" ).html("systemt arbejder, vent venligt")
           ajax({'card':sogStr},"shop2/seekCardInfo","cardCompany.sogkortResponse","");
        }
    },
    sogkortResponse:function(response){

             var kortType = "";
                //  alert(response.data.shop_id)
               if(response.data.shop_id == "52") { kortType = "julegavekortet" }
                        if(response.data.shop_id  == "54") { kortType = "24gaver 400" }
                        if(response.data.shop_id  == "55") { kortType = "24gaver 560" }
                        if(response.data.shop_id  == "56") { kortType = "24gaver 640" }
                        if(response.data.shop_id  == "53") { kortType = "Guldgavekort" }
                        if(response.data.shop_id  == "272") { kortType = "julegavekortet NORGE 300" }
                        if(response.data.shop_id  == "57") { kortType = "julegavekortet NORGE 400" }
                        if(response.data.shop_id  == "58") { kortType = "julegavekortet NORGE 600" }
                        if(response.data.shop_id  == "59") { kortType = "julegavekortet NORGE 800" }
                        if(response.data.shop_id  == "265") { kortType = "julegavetypen" }
                        if(response.data.shop_id  == "287") { kortType = "Dromme 100" }
                        if(response.data.shop_id  == "290") { kortType = "Dromme 200" }
                        if(response.data.shop_id  == "310") { kortType = "Dromme 300" }
                        if(response.data.shop_id  == "574") { kortType = "Guldgavekort Norge" }
                        if(response.data.shop_id  == "575") { kortType = "Designgavekortet" }
                        if(response.data.shop_id  == "1981") { kortType = "24julklappar - 800" }
                        if(response.data.shop_id  == "1832") { kortType = "24julklappar - 440" }
                        if(response.data.shop_id  == "9495") { kortType = "24julklappar ai - 440" }
                        if(response.data.shop_id  == "4793") { kortType = "24julklappar - 300" }
                        if(response.data.shop_id  == "5117") { kortType = "24julklappar - 600" }

        var html ="";

        html+="<table>";
        html+="<tr><td></td><td><button onclick=\"cardCompany.shortCutToCompany('"+response.data.cvr+"','"+response.data.company_id+"')\">G&aring; til firma</button>";
        if(response.data.shopuser_id != "" ){
            html+="<button onclick=\"cardCompany.loadHistory('"+response.data.shopuser_id+"')\">Se historik</button></td>";
        } else {
           html+="<span>Ingen gave valgt</span>";
        }

        html+="</tr>";
        html+="<tr><td>Kort type.</td><td><h3>"+kortType+"</h3></td></tr>";
        html+="<tr><td>Kortnr.</td><td>"+response.data.certificate_no+"</td></tr>";
        html+="<tr><td>password</td><td>"+response.data.password+"</td></tr>";
        html+="<tr><td>Deadline</td><td>"+response.data.expire_date+"</td></tr>";
        html+="<tr><td colspan=2><hr /></td></tr>";
        html+="<tr><td>Virksomhed</td><td>"+response.data.name+"</td></tr>";
        html+="<tr><td>adresse1: </td><td>"+response.data.bill_to_address+"</td></tr>";
        html+="<tr><td>adresse2: </td><td>"+response.data.bill_to_address_2+"</td></tr>";
        html+="<tr><td>Postnr.:</td><td>"+response.data.bill_to_postal_code+"</td></tr>";
        html+="<tr><td>By:</td><td>"+response.data.bill_to_city+"</td></tr>";
        html+="<tr><td>CVR Nummer:</td><td>"+response.data.cvr+"</td></tr>";
        html+="<tr><td>EAN Nummer:</td><td>"+response.data.ean+"</td></tr>";
        html+="<tr><td colspan=2><hr /></td></tr>";
        html+="<tr><td colspan=2>Leveringsadresse </td></tr>";

        html+="<tr><td>adresse1: </td><td>"+response.data.ship_to_address+"</td></tr>";
        html+="<tr><td>adresse2: </td><td>"+response.data.ship_to_address_2+"</td></tr>";
        html+="<tr><td>Postnr.:</td><td>"+response.data.ship_to_postal_code+"</td></tr>";
        html+="<tr><td>By:</td><td>"+response.data.ship_to_city+"</td></tr>";
        html+="<tr><td>Fortrolig kontaktperson:</td><td>"+response.data.contact_name+"</td></tr>";
        html+="<tr><td>Telefonnummer:</td><td>"+response.data.contact_phone+"</td></tr>";
        html+="<tr><td>E-mailadresse:</td><td>"+response.data.contact_email+"</td></tr>";
        html+="<tr><td colspan=2></td>Gavevalg:</tr>";
        html+="<tr><td colspan=2><hr /></td></tr>";

        var gave = response.data.present_model_name.split("###");


        html+="<tr><td>Gavenavn</td><td>"+gave[0]+"</td></tr>";
        html+="<tr><td>Model</td><td>"+gave[1]+"</td></tr>";
        html+="</table>";
        $("#currentSogContent").html(html)
    },
    shortCutToCompany:function(cvr,companyId){
        _shortCutId = companyId;
        ajax({'text':cvr},"company/searchGiftCertificateCompany","cardCompany.shortCutToCompanyResponse","");
    },
    shortCutToCompanyResponse:function(response){
       cardCompany.sogResponse(response);
       cardCompany.sogShowCardCompany(_shortCutId);
    },


    cardSetting:function(setting,shopCardId){
        $(".changeHomeDeleveryStatus").hide();
        _shopCardId = shopCardId;
        if(_transferCardList.length == 0){
            var html = "";
             //SELECT DISTINCT(`expire_date`) FROM `gift_certificate` WHERE `shop_id` =   1832

            if(shopCardId == "52"){
                html+= " <option value=\"2020-11-01\">Uge 48 (2020-11-01)</option>";
                html+= " <option value=\"2020-11-15\">Uge 50 (2020-11-15)</option>";
                html+= " <option value=\"2020-11-29\">Uge 51 (2020-11-29)</option>";
                html+= " <option value=\"2020-12-31\">Uge 04 (2020-12-31)</option>";

            }
            if(shopCardId == "54" || shopCardId == "55" || shopCardId == "56"){
                $(".changeHomeDeleveryStatus").show();
                html+= " <option value=\"2020-11-08\">Uge 49 (2020-11-01)</option>";
                html+= " <option value=\"2020-11-22\">Uge 51 (2020-11-22)</option>";
                html+= " <option value=\"2020-12-31\">Uge 04 (2020-12-31)</option>";
                html+= " <option class=\"deadlineShow\" value=\"2021-04-01\">Hjemmelevering</option>";
            }
            if(shopCardId == "57" || shopCardId == "58" || shopCardId == "59" || shopCardId == "272" || shopCardId == "574" ){
                $(".changeHomeDeleveryStatus").show();
                html+= " <option value=\"2020-11-01\">Uge 48 (2020-11-01)</option>";
                html+= " <option value=\"2020-11-15\">Uge 50 (2020-11-15)</option>";
                html+= " <option value=\"2020-12-31\">Uge 04 (2020-12-31)</option>";
                html+= " <option class=\"deadlineShow\" value=\"2020-11-07\">Hjemmelevering (2020-11-07)</option>";
                html+= " <option class=\"deadlineShow\" value=\"2021-01-03\">Hjemmelevering (2021-01-03)</option>";
            }
            if(shopCardId == "53"){
                $(".changeHomeDeleveryStatus").show();
                html+= " <option value=\"2020-11-08\">Uge 49 (2020-11-08)</option>";
                html+= " <option value=\"2020-11-22\">Uge 51 (2020-11-22)</option>";
                html+= " <option value=\"2020-12-31\">Uge 04 (2020-12-31)</option>";
                html+= " <option class=\"deadlineShow\" value=\"2021-04-01\">Hjemmelevering</option>";
            }

            if(shopCardId == "287" || shopCardId == "290" || shopCardId == "310"){
                html+= " <option value=\"2020-11-08\">Uge 49 (2020-11-08)</option>";
                html+= " <option value=\"2020-11-22\">Uge 51 (2020-11-22)</option>";
                html+= " <option value=\"2020-12-31\">Uge 04 (2020-12-31)</option>";
            }

            if(shopCardId == "575"){
                html+= " <option value=\"2020-11-01\">Uge 48 (2020-11-01)</option>";
                html+= " <option value=\"2020-11-15\">Uge 50 (2020-11-15)</option>";
                html+= " <option value=\"2020-12-31\">Uge 04 (2020-12-31)</option>";
            }

            if(shopCardId == "1832"){
                html+= " <option value=\"2020-11-08\">Uge 49 (2020-11-08)</option>";
                html+= " <option value=\"2020-11-22\">Uge 51 (2020-11-22)</option>";
                html+= " <option value=\"2020-12-31\">Uge 04 (2020-12-31)</option>";
                html+= " <option value=\"2021-12-31\">Hjemmelevering</option>";
            }
            if(shopCardId == "1981"){
                html+= " <option value=\"2020-11-08\">Uge 49 (2020-11-08)</option>";
                html+= " <option value=\"2020-11-22\">Uge 51 (2020-11-22)</option>";
                html+= " <option value=\"2020-12-31\">Uge 04 (2020-12-31)</option>";
                html+= " <option value=\"2021-12-31\">Hjemmelevering</option>";
            }

            $(".deadline").html(html);


            $(".deadline").show();

            if(setting == "0"){
                $(".cardActionMenu").show();
                $(".deadlineShow").show();
                $(".cardActionMenuDeleted").hide();
            }
            if(setting == "1"){

                $(".cardActionMenu").show();
                $(".deadlineShow").show();
                $(".cardActionMenuDeleted").hide();
            }
            if(setting == "2"){
                 $(".deadline").hide();
                 $(".cardActionMenu").hide();
                 $(".cardActionMenuDeleted").show();

            }
        } else {
           alert("tryk anullere eller inds�t overf�rte kort")
        }
    },
    deleteCard:function(){
        if(confirm("Er du sikker du vil slette afkrydsede kort")){
            $(".cardAction").each(function( index ) {
                if( $("#"+this.id).is(":visible") == true){
                    if( $("#"+this.id).is(":checked") == true){
                        ajax({'id':this.id},"giftcertificate/blockGiftCertificate","cardCompany.deleteCardResponse","");
                    }
                }
            })
            alert("kort slettet")
            cardCompany.getCompanySelectedCard(_selectedCompany)
        }

    },
    deleteCardResponse:function(response){

    },
    transferCard:function(){
        if(confirm("Er du sikker du vil overf�re kort")){
               $(".cardActionTransfer").show();
               $(".cardActionMenuDeleted").hide();
               $(".cardActionMenu").hide();
                 $(".deadline").hide();

        $(".cardAction").each(function( index ) {
                if( $("#"+this.id).is(":visible") == true){
                    if( $("#"+this.id).is(":checked") == true){
                        _transferCardList.push(this.id);
                        //ajax({'id':this.id},"giftcertificate/blockGiftCertificate","cardCompany.deleteCardResponse","");
                    }
                }
            })
            alert("V�lg den virksonhed der skal modtage kort")
        }

    },
    doTransfer:function(){
        alert("Vent det kan tage lidt tid")
        for(var i=0;_transferCardList.length > i ;i++){
            _iterator = i;
            ajax({'user_id':_transferCardList[i],'company_id':_selectedCompany},"shop/moveShopUser","cardCompany.doTransferResponse","");
        }


    },
    doTransferResponse:function(response){
        if((_iterator+1) == _transferCardList.length ){
            $(".cardActionTransfer").hide();
            _transferCardList = [];
            cardCompany.showControlCards()
        }
    },
    cancelTransfer:function(){
       $(".cardActionTransfer").hide();
        _transferCardList = [];

    },

    unblock:function(){
        if(confirm("Er du sikker du aktivere de afkrydsede kort")){
            $(".cardAction").each(function( index ) {
                if( $("#"+this.id).is(":visible") == true){
                    if( $("#"+this.id).is(":checked") == true){
                        ajax({'id':this.id},"giftcertificate/unblockGiftCertificate","cardCompany.unblockResponse","");
                    }
                }
            })
            alert("kort genaktiveret")
            cardCompany.getCompanySelectedCard(_selectedCompany)
        }
    },
    unblockResponse:function(){


    },
    changeDeadline:function(){
        var go = true;
        var deadline = $('.deadline').val();

        if(go == true){
            if(confirm("Er du sikker du vil �ndre deadline til "+deadline)){
                $(".cardAction").each(function( index ) {
                    if( $("#"+this.id).is(":visible") == true){
                        if( $("#"+this.id).is(":checked") == true){
                            ajax({'id':this.id,'expire_date':deadline},"giftcertificate/changeExpireDate","cardCompany.changeDeadlineResponse","");
                        }
                    }
                })
                alert("kort deadline �ndret")
                cardCompany.getCompanySelectedCard(_selectedCompany)
            }
        }
    },
    changeDeadlineResponse:function(response){

    },
    hideControlCards:function(){
         $(".controlCards").hide();
         $(".deadline").hide();
         $(".cardAccess").hide();


    },
    showControlCards:function(){
         $(".controlCards").show();
         $("#tabsCardCompany-2").html("<div>Systemet arbejder</div>");
         cardCompany.getCompanySelectedCard(_selectedCompany)
    },
    sog:function(){
        $(".controlCards").hide();
        var sogOption = $(".sogOption:checked").val();
        var sogStr = $(".sogCardShops").val();

        if(sogOption == "firma"){
            $("#currentSogList").show();
            
            if(sogStr.substring(0,2).toLowerCase() == "bs"){
                ajax({'text':sogStr},"company/searchGiftCertificateCompanyBS","cardCompany.sogResponse","");
            } else {
                ajax({'text':sogStr},"company/searchGiftCertificateCompany","cardCompany.sogResponse","");
            }


        }
        if(sogOption == "kortnr"){
             cardCompany.sogkort(sogStr)
        }
        if(sogOption == "sogWithReceipt"){
            cardCompany.sogWithReceipt(sogStr)
        }
        if(sogOption == "order"){
            cardCompany.sogFirma(sogStr)
        }
    },
    sogFirma:function(sogStr){
          ajax({'sogStr':sogStr},"shop2/seekCompanyOrder","cardCompany.sogFirmaResponse","");
    },
    sogFirmaResponse:function(response){
        if(response.data == "0"){
            $("#currentSogContent").html("<h3>Ingen s&oslash;geresultater</h3>");
        } else {
            var html = "<div id=\"sogFirma\"><table width=2000><tr><th>BS</th> <th>Firma navn</th> <th>Cvr</th> <th>Ean</th> <th>Salgperson</th> <th>Note</th> <th>Kort-start</th> <th>Kort-slut</th>  <th>Antal</th>  <th>Kort</th>  <th>kortv&oelig;rdi</th>  <th>kort deadline</th>  <th>gave indpak</th> <th>Lev. firma navn</th> <th>Lev. adresse</th>  <th>Lev. adresse2</th> <th>Lev. postnr</th>  <th>Lev. by</th>  <th>Kontakt person</th>  <th>Kontakt mail</th>  <th>Kontakt tlf.</th> <th>Opret dato</th>  </tr>";
            for (var key in response.data) {
                var item = response.data[key];
                html+= "<tr><td>"+item.order_no+"</td>  <td>"+item.company_name+"</td>  <td>"+item.cvr+"</td>  <td>"+item.ean+"</td>   <td>"+item.salesperson+"</td>  <td>"+item.salenote+"</td>  <td>"+item.certificate_no_begin+"</td>  <td>"+item.certificate_no_end+"</td>  <td>"+item.quantity+"</td>  <td>"+item.shop_name+"</td>  <td>"+item.certificate_value+"</td>  <td>"+item.expire_date+"</td> <td>"+item.giftwrap+"</td> <td>"+item.ship_to_company+"</td>  <td>"+item.ship_to_address+"</td>  <td>"+item.ship_to_address_2+"</td>  <td>"+item.ship_to_postal_code+"</td>  <td>"+item.ship_to_city+"</td>  <td>"+item.contact_name+"</td>   <td>"+item.contact_email+"</td>    <td>"+item.contact_phone+"</td>    <td>"+item.created_datetime+"</td></tr> ";
            }
            html+="</table></div>";
            $("#currentSogContent").html(html);
        }
    },
    closeSogList:function(){
       // $("#currentSogList").hide();
    },
    sogResponse:function(response){
      $(".goToParentCompany").hide();
        _cardSogData = response;
        let childList = [];
        let onlyChild = true;
        var html = "<br />";
        for (var key in response.data.result) {
          // if is parent
          if(response.data.result[key].pid == "0"){
           // test is only child in resultset
           onlyChild = false;
           if(response.data.result[key].hascard > 0 ){
                html+= "<div class=\"cardsogList\" id=\"cardsogList_"+response.data.result[key].id+"\" onclick=\"cardCompany.sogShowCardCompany('"+response.data.result[key].id+"','"+response.data.result[key].name.replace(/'/g, "\\'")+"')\">";
                html+= "<div><label style=\"font-weight: bold\">"+response.data.result[key].name+" -</label><label> "+response.data.result[key].cvr+"</label><br /><label style=\"font-size:11px;\"> "+response.data.result[key].ship_to_address+" - </label><label style=\"font-size:11px;\"> "+response.data.result[key].ship_to_city+"</label><br><label>Antal kort: "+response.data.result[key].hascard+"</label></div></div><div class=\" childlist_"+response.data.result[key].id+" \"></div>";
           } else{
                html+= "<div class=\"cardsogList noHasCard\" id=\"cardsogList_"+response.data.result[key].id+"\" onclick=\"cardCompany.sogShowCardCompany('"+response.data.result[key].id+"','"+response.data.result[key].name.replace(/'/g, "\\'")+"')\">";
                html+= "<div><label style=\"font-weight: bold\">"+response.data.result[key].name+" -</label><label> "+response.data.result[key].cvr+"</label><br /><label style=\"font-size:11px;\"> "+response.data.result[key].ship_to_address+" - </label><label style=\"font-size:11px;\"> "+response.data.result[key].ship_to_city+"</label></div></div><div class=\" childlist_"+response.data.result[key].id+" \"></div>";
           }
          }

        }
        $("#currentSogList").html("<div id=\"currentSogListContainer\">"+html+"</div>")
        // display childnodes
        for (var key in response.data.result) {
          if(response.data.result[key].pid != "0"){
                let noCard = "";
                if(response.data.result[key].hascard == 0){
                  noCard = "noHasCard";
                } else {
                    // remove red color on parent if child has card
                    $("#cardsogList_"+response.data.result[key].pid).removeClass("noHasCard");
                }
                let temphtml = "<div class=\"cardsogList childnode "+noCard+" childnode_"+response.data.result[key].pid+" \" id=\"cardsogList_"+response.data.result[key].id+"\" onclick=\"cardCompany.sogShowCardCompany('"+response.data.result[key].id+"','"+response.data.result[key].name.replace(/'/g, "\\'")+"')\">";
                temphtml+= "<div><label style=\"font-weight: bold\">"+response.data.result[key].ship_to_company+" -</label><label> "+response.data.result[key].cvr+"</label><br /><label style=\"font-size:11px;\"> "+response.data.result[key].ship_to_address+" - </label><label style=\"font-size:11px;\"> "+response.data.result[key].ship_to_city+"</label><br><label>Antal kort: "+response.data.result[key].hascard+"</label></div></div>";
                if(onlyChild == true){
                    $(".goToParentCompany").show();
                    $("#currentSogListContainer").append(temphtml);
                } else {
                    $(".childlist_"+response.data.result[key].pid).append(temphtml)
                }

          }
        }


    },
    onhold:function(){

     if($('#onhold:checkbox:checked').length > 0){
        if(confirm("Vil du aktivere at der blokeres for gavevalg")){
              ajax({'company_id':_selectedCompany,onhold:1},"company/updataOnhold","cardCompany.onholdResponse","");
        } else {
          $('#onhold').prop( "checked",false )
         }
     } else {
        if(confirm("Vil du at de-aktiverre blokeringen for gavevalg")){
            ajax({'company_id':_selectedCompany,onhold:0},"company/updataOnhold","cardCompany.onholdResponse","");
        } else {  $('#onhold').prop( "checked",true )     }
     }


    },
    onholdResponse:function(response){
        alert("blokering opdateret")
    },

    loadNoterAftaler:function(){
        ajax({'company_id':_selectedCompany,},"company/getNotes","cardCompany.loadNoterAftalerRes","");
    },
    loadNoterAftalerRes:function(res ){
        _shopHasChild =  $("#internal_note").val(res.data.result[0].pid) == 0 ? true:false;

        $("#internal_note").val(res.data.result[0].internal_note)
    },


    sogShowCardCompany:function(companyId){
        $(".invoicePart").show();
        $(".goToParentCompany").hide();
        $(".cardsogList").removeClass("cardsogSelected")
        $("#cardsogList_"+companyId).addClass("cardsogSelected")
        $("#sogMenu").html("&Aring;BEN MENU");
     //   $("#currentSogList").hide();
        _selectedCompany  = companyId
        var html = "";
        html+= "<div id=\"tabsCardCompany\"><ul><li><a onclick=\"cardCompany.hideControlCards()\" href=\"#tabsCardCompany-1\">Stamdata</a></li>  ";
        html+= "<li><a onclick=\"cardCompany.showControlCards()\" href=\"#tabsCardCompany-2\">Gavekort</a></li> ";
        html+= "<li><a onclick=\"cardCompany.loadNoterAftaler()\" href=\"#tabsCardCompany-3\">NOTER / AFTALER</a></li> ";
        html+= "<li><a onclick=\"shopRules.init()\" href=\"#tabsCardCompany-4\">Regler</a></li> ";
        html+= "<li><a onclick=\"CardCompanyLayout.init()\" href=\"#tabsCardCompany-5\">Layout (not active)</a></li> ";
        if(canEdit == "1"){
        html+= "<table style=\"float:right; display:none;\" class=\"controlCards\"><tr> <td>";
        html+= "<select class=\"deadline\" style=\"display:none;\">";
        html+= "</select><button class=\"cardActionMenu cardAccess\" onclick=\"cardCompany.changeDeadline()\" style=\"display:none;\">&OElig;ndre kort dato</button></td>";
            html+= "<td><button class=\"cardActionTransfer cardAccess\" onclick=\"cardCompany.doTransfer()\" style=\"display:inline; display:none;\">Inds&OElig;t kort</button></td>";
            html+= "<td><button class=\"cardActionTransfer cardAccess\" onclick=\"cardCompany.cancelTransfer()\" style=\"display:inline; display:none;\">Annullere kort</button></td>";
            html+= "<td><button class=\"cardActionMenuDeleted cardAccess\" onclick=\"cardCompany.unblock()\" style=\"display:inline; display:none;\">Genaktiver kort</button></td>";
            html+= "<td></td>";
            html+= "<td><button class=\"cardActionMenu cardAccess\" onclick=\"cardCompany.transferCard()\" style=\"display:inline;\">Overf&oslash;r kort</button></td>";
            html+= "<td><button class=\"cardActionMenu cardAccess\" onclick=\"cardCompany.showDistributeToChild()\" style=\"display:inline;\">Overf&oslash;r kort til sub </button></td>";
             html+= "<td><button class=\"cardActionMenu cardAccess changeHomeDeleveryStatus\" onclick=\"cardCompany.changeHomeDeleveryStatus()\" style=\"display:inline;\">Send hjem on/off</button></td>";
            html+= "<td><button class=\"cardActionMenu cardAccess\" onclick=\"cardCompany.deleteCard()\" style=\"color:red\" >Slet kort</button></td></tr></table></ul>";
        } else {
          html+= "</ul>";
        }
        html+= "<div id=\"tabsCardCompany-1\" style=\"height:450; overflow-y: auto; \"><div style=\"height:25px;\" >";
        html+= "<img onclick=\"cardStamdata.update('"+companyId+"')\" width=\"20\" height=\"20\" style=\"float:left; cursor: pointer;  \"  src=\"views/media/icon/1373253284_save_64.png\" />";
        html+= "<span class='cardAccess' style='margin-left:50px; border:1px solid red; padding:2px;'>BLOKERE FOR GAVEVALG <input   type='checkbox' id='onhold' onclick='cardCompany.onhold()' /> </span>";
        html+= "<span onclick=\"cardCompany.getLastYearSale('"+companyId+"')\" style='margin-left:20px; padding:2px; cursor:pointer;'>Se sidste &aring;rs salg  </span>";
        html+= "<img onclick=\"cardAddNewCard.showMedal()\" width=\"20\" height=\"20\" style=\"float:right;margin-right:5px;  cursor: pointer;  \"  src=\"views/media/icon/gave.png\" />";




        if(canEdit == "1"){
          html+= "<img class='cardAccess' onclick=\"cardStamdata.remove('"+companyId+"')\" width=\"20\" height=\"20\" style=\"float:right;margin-right:5px;  cursor: pointer;  \"  src=\"views/media/icon/1373253296_delete_64.png\" />";

        }
        html+= "</div><hr />"+cardCompany.stamdataTemplate()+"</div><div style=\"height:450; overflow-y: auto; \" id=\"tabsCardCompany-2\">Systemet arbejder</div> ";
        html+= "<div style=\"height:450; overflow-y: auto; \" id=\"tabsCardCompany-3\">";
         html+= '             <div style="display: inline-block;  width: 450px; margin-right: 70px; ">                      '
html+='               <div style="display:none">   <fieldset>                                                                                                 '
html+='                <legend>Special aftaler fra <b>ordre</b>:</legend>                                                            '
html+='                <div id="spDeal" style="height: 180px; overflow-y:auto;">                                                      '
html+='                 </div>                                                                                                         '
html+='                  </fieldset>                                                                                                    '
html+='             <fieldset >                                                                                                          '
html+='        <legend>Special aftaler <b>till&oelig;g</b>:</legend>                                                                            '
html+='        <div  style="height:230px; overflow-y:auto;">                                                                               '
html+='            <textarea style="width: 98%; height: 98%" id="rapport_note"></textarea>                                                '
html+='        </div>                                                                                                                        '
html+='        <button  onclick="shopNote.saveRapportNote()">Gem till&oelig;g</button>                                                              '
html+='      </fieldset>                                                                                                                       '
html+='    </div></div>                                                                                                                               '
html+='     <div  style="display: inline-block; height: 800px; width: 450px; margin-left:-400px;">                                                                   '
html+='          <fieldset>                                                                                                                       '
html+='        <legend><b>Leveringsaftaler:</b></legend>                                                                                                     '
html+='        <div style="height:450px; overflow-y:auto;">                                                                                         '
html+='            <textarea style="width: 98%; height: 98%" id="internal_note"></textarea>                                                        '
html+='        </div>                                                                                                                                 '
html+='        <button onclick="shopNote.saveInternalNote()">Gem Noter</button>                                                                        '
html+='      </fieldset>                                                                                                                                '
html+='    </div> </div><div id=\"tabsCardCompany-4\" style=\"height:450;\"><table width=100% id="spr-container"></table></div>                                                                                                                                       '
html+='    <div id=\"tabsCardCompany-5\" style=\"height:450;\"> <div id="cardCompanyLayoutContainer"></div>  </div>  </div>'




        html+= "</div>";
        $("#currentSogContent").html("");
        $("#currentSogContent").html(html);
        $( "#tabsCardCompany" ).tabs({
         activate: function(event ,ui){
                        //console.log(event);
                        if(ui.newTab.index() != 4 ) CardCompanyLayout.killEditor();
                    }
        });
        $("#multiCreateBtn").show();
        // husk at vise stamdata
        for (var key in _cardSogData.data.result) {


            if(_cardSogData.data.result[key].id == companyId){
                 // disapple input if company is child
                let disable = false;
                if(_cardSogData.data.result[key].pid != 0){
                    $("#multiCreateBtn").hide();
                    disable = true;
                    $(".invoicePart").hide();
                    $(".goToParentCompany").show();
                }

                $(".dialog1_name_Show").val(_cardSogData.data.result[key].name).prop('disabled', disable);
                $(".dialog1_bill_to_address_Show").val(_cardSogData.data.result[key].bill_to_address).prop('disabled', disable);
                $(".dialog1_bill_to_address_2_Show").val(_cardSogData.data.result[key].bill_to_address_2).prop('disabled', disable);
                $(".dialog1_bill_to_postal_code_Show").val(_cardSogData.data.result[key].bill_to_postal_code).prop('disabled', disable);
                $(".dialog1_bill_to_city_Show").val(_cardSogData.data.result[key].bill_to_city).prop('disabled', disable);
                $(".dialog1_cvr_Show").val(_cardSogData.data.result[key].cvr).prop('disabled', disable);
                $(".dialog1_ean_Show").val(_cardSogData.data.result[key].ean).prop('disabled', disable);


                if (_cardSogData.data.result[key].onhold == 1){
                  $('#onhold').prop( "checked",true )
                }

                if(_cardSogData.data.result[key].ship_to_company == "" || _cardSogData.data.result[key].ship_to_company == null ){
                    $(".dialog1_ship_to_company_Show").val(_cardSogData.data.result[key].name);
                } else {
                   $(".dialog1_ship_to_company_Show").val(_cardSogData.data.result[key].ship_to_company);
                }
                $(".dialog1_ship_to_attention_Show").val(_cardSogData.data.result[key].ship_to_attention);
                $(".dialog1_ship_to_address_Show").val(_cardSogData.data.result[key].ship_to_address);
                $(".dialog1_ship_to_address_2_Show").val(_cardSogData.data.result[key].ship_to_address_2);
                $(".dialog1_ship_to_postal_code_Show").val(_cardSogData.data.result[key].ship_to_postal_code);
                $(".dialog1_ship_to_city_Show").val(_cardSogData.data.result[key].ship_to_city);
                $(".dialog1_contact_name_Show").val(_cardSogData.data.result[key].contact_name);
                $(".dialog1_contact_phone_Show").val(_cardSogData.data.result[key].contact_phone);
                $(".dialog1_contact_email_Show").val(_cardSogData.data.result[key].contact_email);
                $(".lindBackend").val("https://system.gavefabrikken.dk/kundeside/?token="+_cardSogData.data.result[key].token);
                var atag =  "<a href=\"https://system.gavefabrikken.dk/kundeside/?token="+_cardSogData.data.result[key].token+"\" target=\"_blank\"> tryk her ---></a>"
                $("#dialog1_Link").html(atag);
            }
        }
        shopNote.getAddAllnote();
        cardCompany.readShippingDeal();
        cardCompany.loadOrderData();
      //  cardCompany.getCompanySelectedCard(companyId)
    },
    getCompanySelectedCard:function(companyId){
    //    ajax({'company_id':companyId},"company/getUsers","cardCompany.getCompanySelectedCardResponse","");
        ajax({'company_id':companyId},"company/getUsers2","cardCompany.getCompanySelectedCardResponse","");
    },
    getLastYearSale:function(companyId){
        ajax({'company_id':companyId},"company/cardShopSale","cardCompany.getLastYearSaleResponse","");

    },
    getLastYearSaleResponse:function(response){
    var returnStr = "";
    for (var key in response.data) {
        returnStr+= response.data[key].name+" | levuge: "+response.data[key].week_no+" |  antal; "+response.data[key].antal+"\n"
        returnStr+="---------------- \n";
    }
        if(returnStr == "") { returnStr = "The customer did not buy anything last year" }
    alert(returnStr)

    },

    getCompanySelectedCardResponse: async  function(response){
        var modelIdList = [];
        _userData = response;

        var html_jgk = "";
        var html_24gaver_400 = "";
        var html_24gaver_560 = "";
        var html_24gaver_640 = "";
        var html_guld = "";
        var html_jgkNo300 = "";
        var html_jgkNo400 = "";
        var html_jgkNo600 = "";
        var html_jgkNo800 = "";
        var html_jgt = "";
        var html_deleted = "";
        var html_deleted_antal = 0;
        var html_jgk_antal = 0;
        var html_24gaver_400_antal = 0;
        var html_24gaver_560_antal = 0;
        var html_24gaver_640_antal = 0;
        var html_guld_antal = 0;
        var html_jgkNo300_antal = 0;
        var html_jgkNo400_antal = 0;
        var html_jgkNo600_antal = 0;
        var html_jgkNo800_antal = 0;
        var html_jgt_antal = 0;

        var html_dromme_100 = "";
        var html_dromme_200 = "";
        var html_dromme_300 = "";
        var html_dromme_100_antal = 0;
        var html_dromme_200_antal = 0;
        var html_dromme_300_antal = 0;

        var html_guldNo = "";
        var html_design = "";
        var html_guldNo_antal = 0;
        var html_design_antal = 0;

        var html_24gaverSe_360 = "";
        var html_24gaverSe_360_antal = 0;
        var html_24gaverSe_800 = "";
        var html_24gaverSe_800_antal = 0;

        //1981

        var slettede = {"id1981":[], "id1832":[],"id52":[],"id53":[],"id54":[],"id55":[],"id56":[],"id57":[],"id58":[],"id59":[],"id265":[],"id287":[],"id290":[],"id310":[],"id272":[],"id574":[],"id575":[] };
        var slettedeDoExist = false;

        var accordionHtml = "";
           var head = "";
        if(canEdit == "1"){
            head = "<tr><th>Nr</th><th>Kode</th><th>Deadline</th><th>Navn</th><th>Email</th><th>Gave</th><th>Model</th><th>Varenr</th><th><input type=\"checkbox\" onclick=\"cardCompany.selectCardController(this)\" /></th><th></th><th></th></tr>";
        } else {
            head = "<tr><th>Nr</th><th>Kode</th><th>Deadline</th><th>Navn</th><th>Email</th><th>Gave</th><th>Model</th><th></th></tr>";
        }




        for (var key in response.data.users) {
            if(response.data.users[key].blocked != "1")
            {
                    var navn ="";
                    var email="";
                    var gave = "Ej valgt";
                    var model = "";
                    var itemNo = "";
                    if(response.data.users[key].orders.length > 0){
                        navn = response.data.users[key].orders[0].user_name;
                        email = response.data.users[key].orders[0].user_email;
                        var giftName =  response.data.users[key].orders[0].present_model_name.split("###");
                        gave =  giftName[0]; //response.data.users[key].orders[0].present_name;
                        model = giftName[1] ;
                        itemNo = await cardCompany.getItemNumber(response.data.users[key].orders[0].present_model_id);

                       // gave =  response.data.users[key].orders[0].present_name;
                      //  model =  response.data.users[key].orders[0].present_model_name;
                    }

                    var tempHtml = "<tr id='card"+response.data.users[key].id+"'><td>"+response.data.users[key].username+"</td><td>"+response.data.users[key].password+"</td><td>"+response.data.users[key].expire_date+"</td>";
                    tempHtml+= "<td>"+navn+"</td>";
                    tempHtml+= "<td>"+email+"</td>";
                    tempHtml+= "<td>"+gave+"</td>";
                    tempHtml+= "<td>"+model+"</td>";
                    tempHtml+= "<td>"+itemNo+"</td>";
                    tempHtml+= "<td><input id='"+response.data.users[key].id+"' class=\"cardAction\" type=\"checkbox\" /></td>";
                  //  tempHtml+= "<td><div style='cursor:pointer;' onclick=\"cardCompany.loadHistory('"+response.data.users[key].id+"')\">H</div></td></tr>";
                      tempHtml+= "<td><img title=\"Se order historik\" onclick=\"cardCompany.loadHistory('"+response.data.users[key].id+"')\" src=\"views/media/icon/history.png\" width=\"20\" style=\"cursor: pointer;\"> </td>";
                      if(response.data.users[key].has_orders == "1") {
                        tempHtml+= "<td><img title=\"gave\" onclick=\"cardCompany.loadShopPressentDialog('"+response.data.users[key].id+"')\" src=\"views/media/icon/gave.png\" width=\"20\" style=\"cursor: pointer;\"> </td>";
                        tempHtml+= "<td><img title=\"gave\" onclick=\"cardCompany.resendReceipt('"+response.data.users[key].orders[0].id+"')\" src=\"views/media/icon/Purchase Order-50.png\" width=\"20\" style=\"cursor: pointer;\"> </td>";


                      }
                      if(response.data.users[key].is_delivery == "1") {
                        tempHtml+= "<td><img id='homeDelevery"+response.data.users[key].id+"' title=\"Hjemmelevering\" onclick=\"cardCompany.editHomeData('"+response.data.users[key].id+"')\" src=\"views/media/icon/home.png\" width=\"20\" style=\"cursor: pointer;\"> </td></tr>";
                      } else {
                        tempHtml+= "<td><img id='homeDelevery"+response.data.users[key].id+"' style=\"display:none;\" title=\"Hjemmelevering\" onclick=\"cardCompany.editHomeData('"+response.data.users[key].id+"')\" src=\"views/media/icon/home.png\" width=\"20\" style=\"cursor: pointer;\"> </td></tr>";
                      }



                        if(response.data.users[key].shop_id == "52") {
                            html_jgk+= tempHtml
                            html_jgk_antal+=1
                          }
                        if(response.data.users[key].shop_id == "54") {
                          html_24gaver_400+= tempHtml
                          html_24gaver_400_antal+=1
                          }
                        if(response.data.users[key].shop_id == "55") {
                          html_24gaver_560+= tempHtml
                                html_24gaver_560_antal+=1
                          }
                        if(response.data.users[key].shop_id == "56") {
                          html_24gaver_640+= tempHtml
                                  html_24gaver_640_antal+=1
                          }
                        if(response.data.users[key].shop_id == "53") {
                          html_guld+= tempHtml
                             html_guld_antal+=1
                           }
                        if(response.data.users[key].shop_id == "272") {
                          html_jgkNo300+= tempHtml
                               html_jgkNo300_antal+=1
                           }

                        if(response.data.users[key].shop_id == "57") {
                          html_jgkNo400+= tempHtml
                               html_jgkNo400_antal+=1
                           }
                        if(response.data.users[key].shop_id == "58") {
                           html_jgkNo600+= tempHtml
                                 html_jgkNo600_antal+=1
                           }
                        if(response.data.users[key].shop_id == "59") {
                           html_jgkNo800+= tempHtml
                                 html_jgkNo800_antal+=1
                            }
                        if(response.data.users[key].shop_id == "265") {
                           html_jgt+= tempHtml
                                 html_jgt_antal+=1
                            }

                        if(response.data.users[key].shop_id == "287") {
                           html_dromme_100+= tempHtml
                                 html_dromme_100_antal+=1
                            }

                        if(response.data.users[key].shop_id == "290") {
                           html_dromme_200+= tempHtml
                                 html_dromme_200_antal+=1
                            }

                        if(response.data.users[key].shop_id == "310") {
                           html_dromme_300+= tempHtml
                                 html_dromme_300_antal+=1
                            }
                        if(response.data.users[key].shop_id == "574") {
                           html_guldNo+= tempHtml
                                 html_guldNo_antal +=1
                            }
                        if(response.data.users[key].shop_id == "575") {
                            html_design+= tempHtml
                               html_design_antal+=1
                            }
                        if(response.data.users[key].shop_id == "1832") {
                            html_24gaverSe_360+= tempHtml
                            html_24gaverSe_360_antal+=1
                        }
                        if(response.data.users[key].shop_id == "1981") {
                            html_24gaverSe_800 += tempHtml
                            html_24gaverSe_800_antal+=1
                        }


                }

               if(response.data.users[key].blocked == "1")
               {
                    slettedeDoExist = true;
                    var navn ="";
                    var email="";
                    var gave = "Ej valgt";
                    var model = "";
                    var itemNo = "";
                    var shopId = response.data.users[key].shop_id;
                    if(response.data.users[key].orders.length > 0){

                        navn = response.data.users[key].orders[0].user_name;
                        email = response.data.users[key].orders[0].user_email;
                        gave =  response.data.users[key].orders[0].present_name;
                        model =  response.data.users[key].orders[0].present_model_name;
                        itemNo = await cardCompany.getItemNumber(response.data.users[key].orders[0].present_model_id);
                        //modelIdList.push(response.data.users[key].orders[0].present_model_id);
                    }

                    var tempHtml = "<tr id='card"+response.data.users[key].id+"'><td>"+response.data.users[key].username+"</td><td>"+response.data.users[key].password+"</td><td>"+response.data.users[key].expire_date+"</td>";
                    tempHtml+= "<td>"+navn+"</td>";
                    tempHtml+= "<td>"+email+"</td>";
                    tempHtml+= "<td>"+gave+"</td>";
                    tempHtml+= "<td>"+model+"</td>";
                    tempHtml+= "<td>"+itemNo+"</td>";
                    tempHtml+= "<td></td>";
                    tempHtml+= "<td><input id='"+response.data.users[key].id+"' class=\"cardAction\" type=\"checkbox\" /></td>";
                    tempHtml+= "<td><div  style='cursor:pointer;' onclick=\"cardCompany.loadHistory('"+response.data.users[key].id+"')\">H</div></td></tr>";
                    html_deleted_antal+=1
                    slettede["id"+shopId]+=tempHtml;
               }


            }
            if(html_jgkNo300 != ""){
              accordionHtml+= "<h3 onclick=\"cardCompany.cardSetting('0','272')\" data=\"typeCardNo\">Julegavekortet NO 300</h3><div style=\"  \"><div>Antal: "+html_jgkNo300_antal+"</div><table height=450 >"+head+html_jgkNo300+"</table></div>";
            }
            if(html_jgkNo400 != ""){
              accordionHtml+= "<h3 onclick=\"cardCompany.cardSetting('0','57')\" data=\"typeCardNo\">Julegavekortet NO 400</h3><div style=\"  \"><div>Antal: "+html_jgkNo400_antal+"</div><table height=450 >"+head+html_jgkNo400+"</table></div>";
            }
            if(html_jgkNo600 != ""){
              accordionHtml+= "<h3 onclick=\"cardCompany.cardSetting('0','58')\"  data=\"typeCardNo\" >Julegavekortet NO 600</h3><div style=\"  \"><div>Antal: "+html_jgkNo600_antal+"</div><table height=450>"+head+html_jgkNo600+"</table></div>";
            }
            if(html_jgkNo800 != ""){
              accordionHtml+= "<h3 onclick=\"cardCompany.cardSetting('0','59')\"  data=\"typeCardNo\">Julegavekortet NO 800</h3><div style=\"\"><div>Antal: "+html_jgkNo800_antal+"</div><table height=450>"+head+html_jgkNo800+"</table></div>";
            }

            if(html_dromme_100 != ""){
              accordionHtml+= "<h3 onclick=\"cardCompany.cardSetting('0','287')\" data=\"typeCardNo\">drommegavekortet 100</h3><div style=\"  \"><div>Antal: "+html_dromme_100_antal+"</div><table height=450 >"+head+html_dromme_100+"</table></div>";
            }
            if(html_dromme_200 != ""){
              accordionHtml+= "<h3 onclick=\"cardCompany.cardSetting('0','290')\"  data=\"typeCardNo\" >drommegavekortet 200</h3><div style=\"  \"><div>Antal: "+html_dromme_200_antal+"</div><table height=450>"+head+html_dromme_200+"</table></div>";
            }
            if(html_dromme_300 != ""){
              accordionHtml+= "<h3 onclick=\"cardCompany.cardSetting('0','310')\"  data=\"typeCardNo\">drommegavekortet 300</h3><div style=\"  \"><div>Antal: "+html_dromme_300_antal+"</div><table height=450>"+head+html_dromme_300+"</table></div>";
            }


            if(html_jgk != ""){
              accordionHtml+= "<h3 onclick=\"cardCompany.cardSetting('0','52')\"  >Julegavekortet</h3><div style=\"  \"><div>Antal: "+html_jgk_antal+"</div><table height=450>"+head+html_jgk+"</table></div>";
            }
            if(html_jgt != ""){
              accordionHtml+= "<h3 onclick=\"cardCompany.cardSetting('0','265')\"  >Julegave-typen</h3><div style=\"  \"><div>Antal: "+html_jgt_antal+"</div><table height=450>"+head+html_jgt+"</table></div>";
            }

            if(html_24gaver_400 != ""){
              accordionHtml+= "<h3 onclick=\"cardCompany.cardSetting('1','54')\" data=\"typeCard24\">24gaver 400</h3><div style=\"  \"><div>Antal: "+html_24gaver_400_antal+"</div><table height=450>"+head+html_24gaver_400+"</table></div>";
            }
            if(html_24gaver_560 != ""){
              accordionHtml+= "<h3 onclick=\"cardCompany.cardSetting('1','55')\" data=\"typeCard24\">24gaver 560</h3><div style=\"  \"><div>Antal: "+html_24gaver_560_antal+"</div><table height=450>"+head+html_24gaver_560+"</table></div>";
            }
            if(html_24gaver_640 != ""){
              accordionHtml+= "<h3 onclick=\"cardCompany.cardSetting('1','56')\" data=\"typeCard24\">24gaver 640</h3><div style=\" \"><div>Antal: "+html_24gaver_640_antal+"</div><table height=450>"+head+html_24gaver_640+"</table></div>";
            }
            if(html_guld != ""){
              accordionHtml+= "<h3 onclick=\"cardCompany.cardSetting('1','53')\" data=\"typeCardGold\">Gold kortet</h3><div style=\"  \"><div>Antal: "+html_guld_antal+"</div><table height=450>"+head+html_guld+"</table></div>";
            }
            if(html_guldNo != ""){
              accordionHtml+= "<h3 onclick=\"cardCompany.cardSetting('0','574')\" data=\"typeCardGold\">Gold kortet norge</h3><div style=\"  \"><div>Antal: "+html_guldNo_antal+"</div><table height=450>"+head+html_guldNo+"</table></div>";
            }
            if(html_design != ""){
              accordionHtml+= "<h3 onclick=\"cardCompany.cardSetting('0','575')\" data=\"typeCardGold\">Designjulegavekortet</h3><div style=\"  \"><div>Antal: "+html_design_antal+"</div><table height=450>"+head+html_design+"</table></div>";
            }
            if(html_24gaverSe_360 != ""){
              accordionHtml+= "<h3 onclick=\"cardCompany.cardSetting('0','1832')\" data=\"typeCardGold\">24julklappar 360</h3><div style=\"  \"><div>Antal: "+html_24gaverSe_360_antal+"</div><table height=450>"+head+html_24gaverSe_360+"</table></div>";
            }
            if(html_24gaverSe_800 != ""){
              accordionHtml+= "<h3 onclick=\"cardCompany.cardSetting('0','1981')\" data=\"typeCardGold\">24julklappar 800</h3><div style=\"  \"><div>Antal: "+html_24gaverSe_800_antal+"</div><table height=450>"+head+html_24gaverSe_800+"</table></div>";
            }


            if(slettedeDoExist == true){
              accordionHtml+= "<h3 onclick=\"cardCompany.cardSetting('2','')\" data=\"typeCardGold\">-- SLETTEDE KORT --</h3><div style=\"  \"><div>Antal: "+html_deleted_antal+"</div><table height=450>"+head;
              for (var key in slettede) {
                if(slettede[key] != ""){
                    accordionHtml+= "<tr><td bgcolor=yellow colspan=9 align=center><b>"+shopIdList[key]+"</b></td></tr>";
                    accordionHtml+= slettede[key];
                }
              }
              accordionHtml+="</table></div>";
            }


            accordionHtml = "<fieldset style='width:500px;'><legend><b>Tilk&oslash;b</b></legend><span class='cardAccess'><span id=\"giftwrap\"></span> Gaveindpakning</span><span class='cardAccess'><span id=\"giftTransport\"></span>Opb&oelig;ring</span><span class='cardAccess'></div></fieldset> <span id=\"accordionCard\" >"+accordionHtml+"</span>";

            $("#tabsCardCompany-2").html(accordionHtml);

            if(_transferCardList.length > 0){
               $(".cardActionTransfer").show();
               $(".cardActionMenuDeleted").hide();
               $(".cardActionMenu").hide();

            }

            $( "#accordionCard" ).accordion({
                heightStyle: "content",
                collapsible: true,
                active: false
            });

            ajax({'company_id':_selectedCompany},"cardshopnote/getGiftWrap","cardCompany.initGiftwrapResponse","");

    },

    getItemNumber:function(model_id){
         return new Promise(async function (resolve, reject) {

        $.ajax(
            {
            url: 'index.php?rt=company/getItemNumber',
            type: 'GET',
            dataType: 'json',
            data: {model_id: model_id}
            }
          ).done(function(res) {
                resolve(res.data[0].model_present_no)
            }
          )
            })
    },





      initGiftTransportRes:function(response){
        var isChecked = "";
        if(response.data.has == true){
            isChecked = "checked";
        }
         html = "<input onclick=\"cardCompany.updateGiftTransport()\" type=\"checkbox\" id=\"giftTransportCheck\" "+isChecked+" >";
         $("#giftTransport").html(html);
         ajax({'company_id':_selectedCompany},"cardshop/getFreeDelivery","cardCompany.initFreeDeliveryRes","");
    },




    updateGiftTransport:function(){
      var r = confirm("Vil du opdatere opbaerings status");
      if (r == true) {
        if($("#giftTransportCheck").is(':checked') == true){
              ajax({'company_id':_selectedCompany},"cardshop/setGiftTransport ","cardCompany.updateGiftTransportRes","");
        } else {
             ajax({'company_id':_selectedCompany},"cardshop/unsetGiftTransport","cardCompany.updateGiftTransportRes","");
        }
      }

    },
    updateGiftTransportRes:function(){
        alert("gaveindpakning er opdateret")
    },

 //------------    freeDeliverySet

    initFreeDeliveryRes:function(response){
        var isChecked = "";
        if(response.data.has == true){
            isChecked = "checked";
        }
         html = "<input onclick=\"cardCompany.updateFreeDelivery()\" type=\"checkbox\" id=\"freeDeliveryCheck\" "+isChecked+" >";
        // $("#freeDeliverySet").html(html);
    },
    updateFreeDelivery:function(){
      var r = confirm("Vil du opdatere fragt indstilling");
      if (r == true) {
        if($("#freeDeliveryCheck").is(':checked') == true){
              ajax({'company_id':_selectedCompany},"cardshop/setFreeDelivery ","cardCompany.updateFreeDeliveryRes","");
        } else {
             ajax({'company_id':_selectedCompany},"cardshop/unsetFreeDelivery","cardCompany.updateFreeDeliveryRes","");
        }
      }

    },
    updateFreeDeliveryRes:function(){
        alert("fragt indstilling er opdateret")
    },

   //------------



    initGiftwrapResponse:function(response){
        var isChecked = "";
        if(response.data.hasWrap == true){
            isChecked = "checked";
        }
         html = "<input onclick=\"cardCompany.updateGiftwrap()\" type=\"checkbox\" id=\"giftWrapCheck\" "+isChecked+" >";
         $("#giftwrap").html(html);

          ajax({'company_id':_selectedCompany},"cardshop/getGiftTransport","cardCompany.initGiftTransportRes","");
    },
    updateGiftwrap:function(){
      var r = confirm("Vil du opdatere gaveindpakningsstatus");
      if (r == true) {
        if($("#giftWrapCheck").is(':checked') == true){
              ajax({'company_id':_selectedCompany},"cardshopnote/setGiftWrap","cardCompany.updateGiftwrapResponse","");
        } else {
             ajax({'company_id':_selectedCompany},"cardshopnote/unsetGiftWrap","cardCompany.updateGiftwrapResponse","");
        }
      }

    },
    updateGiftwrapResponse:function(){
        alert("gaveindpakning er opdateret")
    },
    stamdataTemplate:function(){
        var html="";
         html+="<button id='multiCreateBtn'>Indl&oelig;s flere leveringsadresser</button>";
        html+='<table><tr valign="top"><td width="450"><table class="invoicePart" width="450" border=0><tr><td width="100">Virksomhed:</td><td width="350"><input type="text" class="stamDataFormularShow dialog1_name_Show" /></td></tr>';
        html+='<tr><td width=100>adresse1: </td><td><input type="text"  class="stamDataFormularShow dialog1_bill_to_address_Show" /></td></tr><tr><td>adresse2: </td><td><input type="text" class="stamDataFormularShow dialog1_bill_to_address_2_Show" /></td></tr>';
        html+=' <tr><td>Postnr.:</td><td><input type="text" class="stamDataFormularShow dialog1_bill_to_postal_code_Show" /></td></tr>';
        html+=' <tr><td>By:</td><td><input type="text" class="stamDataFormularShow dialog1_bill_to_city_Show" /></td></tr>';
        html+=' <tr><td>EAN:</td><td><input type="text" class="stamDataFormularShow dialog1_ean_Show" /></td></tr>';
        html+=' <tr><td>CVR Nummer:</td><td><input type="text" class="stamDataFormularShow dialog1_cvr_Show" /></td></tr></table>';
        html+='<div class="goToParentCompany" onclick="cardCompany.goToParentSog()"><button>Vis alle leveringsadresser</button></div><br>';
        html+='<label><b>Leveringsadresse (udfyldes kun hvis forskellig fra virksomhedsadresse):</b></label><br /><table  width="450">';
        html+='<tr> <td width=100>Virksonhed: </td><td><input type="text" class="stamDataFormularShow dialog1_ship_to_company_Show" /></td></tr>';
        html+='<tr> <td width=100>adresse1: </td><td><input type="text" class="stamDataFormularShow dialog1_ship_to_address_Show" /></td></tr>';
        html+='<tr><td>adresse2: </td><td><input type="text" class="stamDataFormularShow dialog1_ship_to_address_2_Show" /></td></tr>';
        html+='<tr> <td>Postnr.:</td><td><input type="text" class="stamDataFormularShow dialog1_ship_to_postal_code_Show" /></td></tr>';
        html+='<tr> <td>By:</td><td><input type="text" class="stamDataFormularShow dialog1_ship_to_city_Show" /></td></tr></table><hr /><table  width="450">';
        html+='<tr> <td width="100">Fortrolig kontaktperson:</td><td width="350"><input type="text" class="stamDataFormularShow dialog1_contact_name_Show" /></td></tr>';
        html+='<tr> <td>Telefonnummer:</td><td><input type="text" class="stamDataFormularShow dialog1_contact_phone_Show" /></td></tr>';
        html+='<tr> <td>E-mailadresse:</td><td><input type="text" class="stamDataFormularShow dialog1_contact_email_Show" /></td></tr></table></td>';
        html+='<td width="600" valign="top" >';
        html+='<fieldset><legend>Fragtberegning (ikke klar endnu)</legend>';
        html+='<label>Nuv&oelig;rende fragtaftale: </label><b><label id="currentShippingDeal">No shipping deal</label></b><br><br>';
        html+='<input type="number" id="chippingNewDeal" /><button onclick="cardCompany.changeShippingDeal()">Opret / &oelig;ndre fragt aftale</button></fieldset>';
        html+='<fieldset><legend>Kundens backend</legend>';
        html+='Link til backend: <span id="dialog1_Link"></span><br>';
        html+='<input type="text"  readonly class="stamDataFormularShow lindBackend" />';
        html+='</fieldset><br>';

        html+='<fieldset><legend>Udsendelse af gavekoder</legend>';
        html+='<table border=1 width=100% id="orderSendAdmin" style="font-size:11px;"></table>';
        html+=' ';
        html+='</fieldset>';


        html+='</td></tr></table>';

        return html;

    },
    goToParentSog:async function (){
       let result = await cardCompany.getParentCompany()
       if(result.status == 1){
         try {
            let cvr = result.data.result[0].cvr;
            ajax({'text':cvr},"company/searchGiftCertificateCompany","cardCompany.sogResponse","");
        } catch (error) { }
       }
    },
    getParentCompany:function () {
       return new Promise(function(resolve, reject) {
         $.ajax(
            {
            url: 'index.php?rt=company/getParentCompany',
            type: 'POST',
            dataType: 'json',
            data: {id:_selectedCompany}
            }).done(function(res) {
            if(res.status == 0) { resolve(res) }
            else { resolve(res) }
          })
        })
    },
    selectCardController:function(ele){
    if( $(ele ).is(":checked") == true   ){
         $(".cardAction").each(function( index ) {
            if( $("#"+this.id).is(":visible") == true){
                 $("#"+this.id).prop('checked', true);
            }
        })
    } else {
        $("input").prop('checked', false);
    }
  },
  changeHomeDeleveryStatus:function(){
        var data = [];
        if(confirm("Er du sikker du vil aendre 'Send hjem indstillingerne?'")){
            $(".cardAction").each(function( index ) {
                if( $("#"+this.id).is(":visible") == true){
                    if( $("#"+this.id).is(":checked") == true){
                        var userObj = cardCompany.getUserObj(this.id);

                        var obj = {};
                        obj.id = userObj.id;
                        if(userObj.is_delivery == 0){
                            $("#homeDelevery"+userObj.id).show();
                            userObj.is_delivery = 1;
                            obj.is_delivery = 1;
                        } else {
                            $("#homeDelevery"+userObj.id).hide();
                            userObj.is_delivery = 0;
                            obj.is_delivery = 0;
                        }
                        data.push(obj);
                    }
                }
            })
            var formData = {'data':JSON.stringify(data)};
             ajax(formData,"shop/updateShopUserDelivery","cardCompany.changeHomeDeleveryStatusResponse","");
        }
  },
  changeHomeDeleveryStatusResponse:function(response){
    if(response.status=="1"){
        $(".cardAction").prop('checked', false);
    } else {
      alert("Der er sket en fjel, intet er gemt");
    }
  },
  editHomeData:function(userId){
            $( "#dialog_message" ).html("systemt arbejder, vent venligt")
            dialog =  $( "#dialog_message" ).dialog({
                title: 'Rediger brugers leveringsoplysninger',
                autoOpen: true,
                height: 550,
                width: 550,
                modal: true,
                buttons: {
                    Cancel: function() {
                    dialog.dialog( "close" );
                }
            }
            });
            ajax({'user_id':userId},"order/getUserDeliveryInfo","cardCompany.editHomeDataResponse","");
  },
//  hjemmelevering
  editHomeDataResponse:function(response){
    var shopuser_id;
    var html = "<table width=95%>";
    $(response.data).each(function( index ) {
        shopuser_id = this.attributes.shopuser_id;
        html+="<tr><td width=30%>"+this.attributes.attribute_name+"</td><td  width=69%><input class=\"deleveryData\" style=\"width:95%\" data=\""+this.attributes.attribute_id+"\" type=\"text\" value=\""+this.attributes.attribute_value+"\" /></td></tr>"
    });
    html+="</table><br /><div style=\"width:95%; text-align:right;\"><input class=\"ui-button ui-corner-all ui-widget\" type=\"button\"  value=\"Opdatere oplysninger\" onclick=\"$(this).hide(); cardCompany.updateUserDeleveryData('"+shopuser_id+"')\" /></div>";
    $( "#dialog_message" ).html(html);
  },
  updateUserDeleveryData:function(shopuser_id){
        var data = [];
        $(".deleveryData").each(function( index ) {
            var obj = {};
            obj.shopuser_id = shopuser_id;
            obj.attribute_id = $(this).attr("data");
            obj.attribute_value = $(this).val();
            data.push(obj);
        });
        var formData = {'data':JSON.stringify(data)};
        ajax(formData,"order/updateUserDeliveryInfo","cardCompany.updateUserDeleveryDataResponse","");
  },
  updateUserDeleveryDataResponse:function(response){
        $( "#dialog_message" ).dialog( "close" );
  },
  getUserObj:function(userId){
    for (var key in _userData.data.users) {
        if(_userData.data.users[key].id == userId){
            return _userData.data.users[key];
        }
    }
  },
  // dialog gave
  loadShopPressentDialog:function(id){
            _userId = id;
            $( "#dialog_message" ).html("systemt arbejder, vent venligt")
                dialog =  $( "#dialog_message" ).dialog({
                title: 'Rediger brugers gavevalg',
                autoOpen: true,
                height: 550,
                width: 550,
                modal: true,
                    buttons: {
                        Cancel: function() {
                        dialog.dialog( "close" );
                    }
                }
            });
            ajax({shop_id:_shopCardId,token:"dsf984gh58b2i23t4g8"},"shopPresentRules/getPresentListOnShop","cardCompany.buildShopPresentsList","");
  },
    buildShopPresentsList:function(response)
    {


        var tempHtml = "";
        var presentsHtml = "<center><table border=0 >";
        for(var i=0;response.data.length >i;i++){
           var data =   response.data[i].attributes;
           tempHtml = "<tr><td height=30 width=200>"+data.model_name+"</td><td width=200>"+data.model_no+"</td><td width=200></td><td><button onclick=\"cardCompany.doChangeGift('"+data.present_id+"','"+data.model_name+"','"+data.model_no+"','"+data.model_id+"') \">V&oelig;lg</button></td></tr>";
           presentsHtml+=tempHtml ;

/*
            if(modelJson.length > 0){
                tempHtml = "";
                gaveId = response.data[i].id;
                gaveNavn =  response.data[i].name;
                tempHtml = "";

               $.each(modelJson, function(i, item) {
                    if(item.language_id == "1"){
                        tempHtml+="<tr><td height=30 width=200>"+gaveNavn+"</td><td width=200>"+item.feltData[0].variant+"</td><td width=200>"+item.feltData[1].variantSub+"</td><td><button onclick=\"cardCompany.doChangeGift('"+gaveId+"','"+item.feltData[0].variant+"','"+item.feltData[1].variantSub+"','"+item.feltData[2].variantNr+"') \">V�lg</button></td></tr>";
                     }
                })

            } else {
                presentsHtml+= "<tr><td height=30>"+response.data[i].name+"</td><td></td><td></td><td><button onclick=\"cardCompany.doChangeGift('"+response.data[i].id+"','"+response.data[i].name+"','','' ) \">V�lg</button></td></tr>";
            }
  */
        }
         presentsHtml+="</table></center>"
         $( "#dialog_message").html(presentsHtml)

    },
  // gensend kvittering
  resendReceipt:function(id){
    if (confirm("Godkend for at sende ny kvittering") == true) {
            ajax({order_id:id},"order/resendReceipt","cardCompany.resendReceiptResponse","");
    }
  },
  resendReceiptResponse:function(response){
       // console.log(response)
  },
  doChangeGift:function(presentsId,modelName,model,modelId){
      var formdata = {
            "shopId":_shopCardId,
            "userId":_userId,
            "presentsId":presentsId,
            "modelName":modelName,
            "model_id":modelId,
            "model":model


        }
        _newPresent =  formdata;
        $( "#dialog_message" ).html("systemt arbejder, vent venligt")
        ajax(formdata,"order/changePresent","cardCompany.doChangeGiftResponse","");
  },
  doChangeGiftResponse:function(response){

     $("#card"+_userId+" td:nth-child(6)").html(_newPresent.modelName);
     $("#card"+_userId+" td:nth-child(7)").html(_newPresent.model);
     $( "#dialog_message" ).dialog('close');

  },
      updateCompanyCards:function(cvr){

            $.ajax(
                  {
                  url: 'index.php?rt=company/updateCardCount',
                  type: 'GET',
                  data: {}
                  }).done(function() {
                    cardCompany.goToParentSog();
                  })
    }

}





var encode = document.getElementById('encode'),
    decode = document.getElementById('decode'),
    output = document.getElementById('output'),
    input = document.getElementById('input');
 var Base64 = {


    _keyStr: "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",


    encode: function(input) {
        var output = "";
        var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
        var i = 0;

        input = Base64._utf8_encode(input);

        while (i < input.length) {

            chr1 = input.charCodeAt(i++);
            chr2 = input.charCodeAt(i++);
            chr3 = input.charCodeAt(i++);

            enc1 = chr1 >> 2;
            enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
            enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
            enc4 = chr3 & 63;

            if (isNaN(chr2)) {
                enc3 = enc4 = 64;
            } else if (isNaN(chr3)) {
                enc4 = 64;
            }

            output = output + this._keyStr.charAt(enc1) + this._keyStr.charAt(enc2) + this._keyStr.charAt(enc3) + this._keyStr.charAt(enc4);

        }

        return output;
    },


    decode: function(input) {
        var output = "";
        var chr1, chr2, chr3;
        var enc1, enc2, enc3, enc4;
        var i = 0;

        input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");

        while (i < input.length) {

            enc1 = this._keyStr.indexOf(input.charAt(i++));
            enc2 = this._keyStr.indexOf(input.charAt(i++));
            enc3 = this._keyStr.indexOf(input.charAt(i++));
            enc4 = this._keyStr.indexOf(input.charAt(i++));

            chr1 = (enc1 << 2) | (enc2 >> 4);
            chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
            chr3 = ((enc3 & 3) << 6) | enc4;

            output = output + String.fromCharCode(chr1);

            if (enc3 != 64) {
                output = output + String.fromCharCode(chr2);
            }
            if (enc4 != 64) {
                output = output + String.fromCharCode(chr3);
            }

        }

        output = Base64._utf8_decode(output);

        return output;

    },

    _utf8_encode: function(string) {
        string = string.replace(/\r\n/g, "\n");
        var utftext = "";

        for (var n = 0; n < string.length; n++) {

            var c = string.charCodeAt(n);

            if (c < 128) {
                utftext += String.fromCharCode(c);
            }
            else if ((c > 127) && (c < 2048)) {
                utftext += String.fromCharCode((c >> 6) | 192);
                utftext += String.fromCharCode((c & 63) | 128);
            }
            else {
                utftext += String.fromCharCode((c >> 12) | 224);
                utftext += String.fromCharCode(((c >> 6) & 63) | 128);
                utftext += String.fromCharCode((c & 63) | 128);
            }

        }

        return utftext;
    },

    _utf8_decode: function(utftext) {
        var string = "";
        var i = 0;
        var c = c1 = c2 = 0;

        while (i < utftext.length) {

            c = utftext.charCodeAt(i);

            if (c < 128) {
                string += String.fromCharCode(c);
                i++;
            }
            else if ((c > 191) && (c < 224)) {
                c2 = utftext.charCodeAt(i + 1);
                string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
                i += 2;
            }
            else {
                c2 = utftext.charCodeAt(i + 1);
                c3 = utftext.charCodeAt(i + 2);
                string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
                i += 3;
            }

        }

        return string;
    }

}