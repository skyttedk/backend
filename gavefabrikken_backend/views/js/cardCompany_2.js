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
shopIdList["id58"] = "Jgk Norge 600";
shopIdList["id59"] = "Jgk Norge 800";
shopIdList["id265"] = "julegavetypen";

shopIdList["id287"] = "dromme 100";
shopIdList["id290"] = "dromme 200";
shopIdList["id310"] = "dromme 300";


[""].id52 = ["52","53","54","55","56","57","58","59","265","287","290","310","272"];



var cardCompany = {
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
            $( "#dialog_message" ).html("systemt arbejder, vent venligt")
            ajax({'ReceiptNr':sogStr},"shop2/receiptSeekCardInfo","cardCompany.sogWithReceiptResponse","");
        }
    },
    sogWithReceiptResponse:function(response){
        if(response.data == 0){
         $("#currentSogContent").html("Der blev ikke fundet noget");
        } else {
            if(response.data == "notCard"){
               $("#currentSogContent").html("Kvittering tilhører ikke en kort shop");
            } else {
                ajax({'card':response.data},"shop2/seekCardInfo","cardCompany.sogkortResponse","");
            }

        }
    },

    sogkort:function(sogStr){

        if (sogStr != "") {
           $( "#dialog_message" ).html("systemt arbejder, vent venligt")
           ajax({'card':sogStr},"shop2/seekCardInfo","cardCompany.sogkortResponse","");
        }
    },
    sogkortResponse:function(response){

             var kortType = "";

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

        var html ="";
        html+="<table>";
        html+="<tr><td></td><td><button onclick=\"cardCompany.shortCutToCompany('"+response.data.cvr+"','"+response.data.company_id+"')\">Gå til firma</button>";
        if(response.data.shopuser_id != "" ){
            html+="<button onclick=\"cardCompany.loadHistory('"+response.data.shopuser_id+"')\">Se historik</button></td>";
        } else {
           html+="<span>Ingen gave valgt</span>";
        }
        html+="</tr>";
        html+="<tr><td>Kort type.</td><td><h3>"+kortType+"</h3></td></tr>";
        html+="<tr><td>Kortnr.</td><td>"+response.data.certificate_no+"</td></tr>";
        html+="<tr><td>password</td><td>"+response.data.password+"</td></tr>";
        html+="<tr><td>Kort værdi</td><td>"+response.data.value+"</td></tr>";
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
        html+="<tr><td>Gavenavn</td><td>"+response.data.present_name+"</td></tr>";
        html+="<tr><td>Model</td><td>"+response.data.present_model_name+"</td></tr>";
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
         _shopCardId = shopCardId;
        if(_transferCardList.length == 0){
            var html = "";
            if(shopCardId == "52"){
                html+= " <option value=\"2017-11-5\">2017-11-05</option>";
                html+= " <option value=\"2017-11-19\">2017-11-19</option>";
                html+= " <option value=\"2017-12-31\">2017-12-31</option>";
            }
            if(shopCardId == "54" || shopCardId == "55" || shopCardId == "56"){
                html+= " <option value=\"2017-11-12\">2017-11-05</option>";
                html+= " <option value=\"2017-11-26\">2017-11-19</option>";
                html+= " <option value=\"2017-12-31\">2017-12-31</option>";
                html+= " <option class=\"deadlineShow\" value=\"2019-01-01\">Send hjem</option>";
            }
            if(shopCardId == "57" || shopCardId == "58" || shopCardId == "59" || shopCardId == "272" ){
                html+= " <option value=\"2017-11-12\">2017-11-05</option>";
                html+= " <option value=\"2017-11-26\">2017-11-19</option>";
                html+= " <option value=\"2017-12-31\">2017-12-31</option>";
            }
            if(shopCardId == "53"){
                html+= " <option value=\"2017-11-12\">2017-11-05</option>";
                html+= " <option value=\"2017-11-26\">2017-11-19</option>";
                html+= " <option value=\"2017-12-31\">2017-12-31</option>";
                html+= " <option class=\"deadlineShow\" value=\"2019-01-01\">Send hjem</option>";
            }
            if(shopCardId == "265"){
                html+= " <option value=\"2017-11-5\">2017-11-05</option>";
                html+= " <option value=\"2017-11-19\">2017-11-19</option>";
                html+= " <option value=\"2017-12-31\">2017-12-31</option>";
            }
            if(shopCardId == "287" || shopCardId == "290" || shopCardId == "310"){
                html+= " <option value=\"2017-11-12\">2017-11-05</option>";
                html+= " <option value=\"2017-11-26\">2017-11-19</option>";
                html+= " <option value=\"2017-12-31\">2017-12-31</option>";
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
           alert("tryk anullere eller indsæt overførte kort")
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
        if(confirm("Er du sikker du vil overføre kort")){
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
            alert("Vælg den virksonhed der skal modtage kort")
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
            if(confirm("Er du sikker du vil ændre deadline til "+deadline)){
                $(".cardAction").each(function( index ) {
                    if( $("#"+this.id).is(":visible") == true){
                        if( $("#"+this.id).is(":checked") == true){
                            ajax({'id':this.id,'expire_date':deadline},"giftcertificate/changeExpireDate","cardCompany.changeDeadlineResponse","");
                        }
                    }
                })
                alert("kort deadline ændret")
                cardCompany.getCompanySelectedCard(_selectedCompany)
            }
        }
    },
    changeDeadlineResponse:function(response){

    },
    hideControlCards:function(){
         $(".controlCards").hide();
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
            ajax({'text':sogStr},"company/searchGiftCertificateCompany","cardCompany.sogResponse","");
        }
        if(sogOption == "kortnr"){
             cardCompany.sogkort(sogStr)
        }
        if(sogOption == "sogWithReceipt"){
            cardCompany.sogWithReceipt(sogStr)
        }
 


    },
    closeSogList:function(){
        $("#currentSogList").hide();
    },
    sogResponse:function(response){
        _cardSogData = response;
        var html = "<br />";
        for (var key in response.data.result) {
           html+= "<div class=\"cardsogList\" id=\"cardsogList_"+response.data.result[key].id+"\" onclick=\"cardCompany.sogShowCardCompany('"+response.data.result[key].id+"','"+response.data.result[key].name+"')\">";
           html+= "<div><label style=\"font-weight: bold\">"+response.data.result[key].name+" -</label><label> "+response.data.result[key].cvr+"</label><br /><label style=\"font-size:11px;\"> "+response.data.result[key].ship_to_address+" - </label><label style=\"font-size:11px;\"> "+response.data.result[key].ship_to_city+"</label></div></div>";
        }
        $("#currentSogList").html("<div id=\"currentSogListContainer\">"+html+"</div>")

    },
    sogShowCardCompany:function(companyId){
                $("#sogMenu").html("&Aring;BEN MENU");
        $("#currentSogList").hide();
        _selectedCompany  = companyId
        var html = "";
        html+= "<div id=\"tabsCardCompany\"><ul><li><a onclick=\"cardCompany.hideControlCards()\" href=\"#tabsCardCompany-1\">Stamdata</a></li>  ";
        html+= "<li><a onclick=\"cardCompany.showControlCards()\" href=\"#tabsCardCompany-2\">Gavekort</a></li> ";
        if(canEdit == "1"){
        html+= "<table style=\"float:right; display:none;\" class=\"controlCards\"><tr> <td>";
        html+= "<select class=\"deadline\" style=\"display:none;\">";
        html+= "</select></td>";
            html+= "<td><button class=\"cardActionTransfer\" onclick=\"cardCompany.doTransfer()\" style=\"display:inline; display:none;\">Indsæt kort</button></td>";
            html+= "<td><button class=\"cardActionTransfer\" onclick=\"cardCompany.cancelTransfer()\" style=\"display:inline; display:none;\">Annullere kort</button></td>";
            html+= "<td><button class=\"cardActionMenuDeleted\" onclick=\"cardCompany.unblock()\" style=\"display:inline; display:none;\">Genaktiver kort</button></td>";
            html+= "<td><button class=\"cardActionMenu\" onclick=\"cardCompany.changeDeadline()\" style=\"display:inline;\">&OElig;ndre kort dato</button></td>";
            html+= "<td><button class=\"cardActionMenu\" onclick=\"cardCompany.transferCard()\" style=\"display:inline;\">Overf&oslash;r kort</button></td>";
            html+= "<td><button class=\"cardActionMenu\" onclick=\"cardCompany.changeHomeDeleveryStatus()\" style=\"display:inline;\">Send hjem on/off</button></td>";
            html+= "<td><button class=\"cardActionMenu\" onclick=\"cardCompany.deleteCard()\" style=\"color:red\" >Slet kort</button></td></tr></table></ul>";
        } else {
          html+= "</ul>";
        }
        html+= "<div id=\"tabsCardCompany-1\" style=\"height:450; overflow-y: auto; \"><div style=\"height:25px;\" >";
        if(canEdit == "1"){
          html+= "<img onclick=\"cardStamdata.remove('"+companyId+"')\" width=\"20\" height=\"20\" style=\"float:right;margin-right:5px;  cursor: pointer;  \"  src=\"views/media/icon/1373253296_delete_64.png\" />";
          html+= "<img onclick=\"cardAddNewCard.showMedal()\" width=\"20\" height=\"20\" style=\"float:right;margin-right:5px;  cursor: pointer;  \"  src=\"views/media/icon/gave.png\" />";
          html+= "<img onclick=\"cardStamdata.update('"+companyId+"')\" width=\"20\" height=\"20\" style=\"float:left; cursor: pointer;  \"  src=\"views/media/icon/1373253284_save_64.png\" />";
        }
        html+= ""
        html+= "</div><hr />"+cardCompany.stamdataTemplate()+"</div><div style=\"height:450; overflow-y: auto; \" id=\"tabsCardCompany-2\">Systemet arbejder</div> ";






        html+= "</div>";
        $("#currentSogContent").html("");
        $("#currentSogContent").html(html);
        $( "#tabsCardCompany" ).tabs();

        // husk at vise stamdata
        for (var key in _cardSogData.data.result) {
            if(_cardSogData.data.result[key].id == companyId){
                $(".dialog1_name_Show").val(_cardSogData.data.result[key].name);
                $(".dialog1_bill_to_address_Show").val(_cardSogData.data.result[key].bill_to_address);
                $(".dialog1_bill_to_address_2_Show").val(_cardSogData.data.result[key].bill_to_address_2);
                $(".dialog1_bill_to_postal_code_Show").val(_cardSogData.data.result[key].bill_to_postal_code);
                $(".dialog1_bill_to_city_Show").val(_cardSogData.data.result[key].bill_to_city);
                $(".dialog1_cvr_Show").val(_cardSogData.data.result[key].cvr);
                $(".dialog1_ean_Show").val(_cardSogData.data.result[key].ean);
                $(".dialog1_ship_to_attention_Show").val(_cardSogData.data.result[key].ship_to_attention);
                $(".dialog1_ship_to_address_Show").val(_cardSogData.data.result[key].ship_to_address);
                $(".dialog1_ship_to_address_2_Show").val(_cardSogData.data.result[key].ship_to_address_2);
                $(".dialog1_ship_to_postal_code_Show").val(_cardSogData.data.result[key].ship_to_postal_code);
                $(".dialog1_ship_to_city_Show").val(_cardSogData.data.result[key].ship_to_city);
                $(".dialog1_contact_name_Show").val(_cardSogData.data.result[key].contact_name);
                $(".dialog1_contact_phone_Show").val(_cardSogData.data.result[key].contact_phone);
                $(".dialog1_contact_email_Show").val(_cardSogData.data.result[key].contact_email);

            }
        }
      //  cardCompany.getCompanySelectedCard(companyId)
    },
    getCompanySelectedCard:function(companyId){
    //    ajax({'company_id':companyId},"company/getUsers","cardCompany.getCompanySelectedCardResponse","");
        ajax({'company_id':companyId},"company/getUsers2","cardCompany.getCompanySelectedCardResponse","");
    },
    getCompanySelectedCardResponse:function(response){
        _userData = response;
         alert("asdfsadf")
        console.log(_userData)



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


        var slettede = {"id52":[],"id53":[],"id54":[],"id55":[],"id56":[],"id57":[],"id58":[],"id59":[],"id265":[],"id287":[],"id290":[],"id310":[],"272":[]};
        var slettedeDoExist = false;

        var accordionHtml = "";
           var head = "";
        if(canEdit == "1"){
            head = "<tr><th>Nr</th><th>Kode</th><th>Deadline</th><th>Navn</th><th>Email</th><th>Gave</th><th>Model</th><th><input type=\"checkbox\" onclick=\"cardCompany.selectCardController(this)\" /></th><th></th><th></th></tr>";
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

                    if(response.data.users[key].orders.length > 0){
                        navn = response.data.users[key].orders[0].user_name;
                        email = response.data.users[key].orders[0].user_email;
                        gave =  response.data.users[key].orders[0].present_name;
                        model =  response.data.users[key].orders[0].present_model_name;
                    }

                    var tempHtml = "<tr id='card"+response.data.users[key].id+"'><td>"+response.data.users[key].username+"</td><td>"+response.data.users[key].password+"</td><td>"+response.data.users[key].expire_date+"</td>";
                    tempHtml+= "<td>"+navn+"</td>";
                    tempHtml+= "<td>"+email+"</td>";
                    tempHtml+= "<td>"+gave+"</td>";
                    tempHtml+= "<td>"+model+"</td>";
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
                          html_jgkNo400+= tempHtml
                               html_jgkNo400_antal+=1
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

                }

               if(response.data.users[key].blocked == "1")
               {
                    slettedeDoExist = true;
                    var navn ="";
                    var email="";
                    var gave = "Ej valgt";
                    var model = "";
                    var shopId = response.data.users[key].shop_id;
                    if(response.data.users[key].orders.length > 0){

                        navn = response.data.users[key].orders[0].user_name;
                        email = response.data.users[key].orders[0].user_email;
                        gave =  response.data.users[key].orders[0].present_name;
                        model =  response.data.users[key].orders[0].present_model_name;
                    }

                    var tempHtml = "<tr id='card"+response.data.users[key].id+"'><td>"+response.data.users[key].username+"</td><td>"+response.data.users[key].password+"</td><td>"+response.data.users[key].expire_date+"</td>";
                    tempHtml+= "<td>"+navn+"</td>";
                    tempHtml+= "<td>"+email+"</td>";
                    tempHtml+= "<td>"+gave+"</td>";
                    tempHtml+= "<td>"+model+"</td>";
                    tempHtml+= "<td><input id='"+response.data.users[key].id+"' class=\"cardAction\" type=\"checkbox\" /></td>";
                    tempHtml+= "<td><div  style='cursor:pointer;' onclick=\"cardCompany.loadHistory('"+response.data.users[key].id+"')\">H</div></td></tr>";
                    html_deleted_antal+=1
                    slettede["id"+shopId]+=tempHtml;
               }


            }
            if(html_jgkNo300 != ""){
              accordionHtml+= "<h3 onclick=\"cardCompany.cardSetting('0','272')\" data=\"typeCardNo\">Julegavekortet NO 400</h3><div style=\" height: 480px; overflow-y: auto; \"><div>Antal: "+html_jgkNo300_antal+"</div><table height=450 >"+head+html_jgkNo300+"</table></div>";
            }

            if(html_jgkNo400 != ""){
              accordionHtml+= "<h3 onclick=\"cardCompany.cardSetting('0','57')\" data=\"typeCardNo\">Julegavekortet NO 400</h3><div style=\" height: 480px; overflow-y: auto; \"><div>Antal: "+html_jgkNo400_antal+"</div><table height=450 >"+head+html_jgkNo400+"</table></div>";
            }
            if(html_jgkNo600 != ""){
              accordionHtml+= "<h3 onclick=\"cardCompany.cardSetting('0','58')\"  data=\"typeCardNo\" >Julegavekortet NO 600</h3><div style=\" height: 480px; overflow-y: auto; \"><div>Antal: "+html_jgkNo600_antal+"</div><table height=450>"+head+html_jgkNo600+"</table></div>";
            }
            if(html_jgkNo800 != ""){
              accordionHtml+= "<h3 onclick=\"cardCompany.cardSetting('0','59')\"  data=\"typeCardNo\">Julegavekortet NO 800</h3><div style=\" height: 480px; overflow-y: auto; \"><div>Antal: "+html_jgkNo800_antal+"</div><table height=450>"+head+html_jgkNo800+"</table></div>";
            }

            if(html_dromme_100 != ""){
              accordionHtml+= "<h3 onclick=\"cardCompany.cardSetting('0','287')\" data=\"typeCardNo\">drommegavekortet 100</h3><div style=\" height: 480px; overflow-y: auto; \"><div>Antal: "+html_dromme_100_antal+"</div><table height=450 >"+head+html_dromme_100+"</table></div>";
            }
            if(html_dromme_200 != ""){
              accordionHtml+= "<h3 onclick=\"cardCompany.cardSetting('0','290')\"  data=\"typeCardNo\" >drommegavekortet 200</h3><div style=\" height: 480px; overflow-y: auto; \"><div>Antal: "+html_dromme_200_antal+"</div><table height=450>"+head+html_dromme_200+"</table></div>";
            }
            if(html_dromme_300 != ""){
              accordionHtml+= "<h3 onclick=\"cardCompany.cardSetting('0','310')\"  data=\"typeCardNo\">drommegavekortet 300</h3><div style=\" height: 480px; overflow-y: auto; \"><div>Antal: "+html_dromme_300_antal+"</div><table height=450>"+head+html_dromme_300+"</table></div>";
            }


            if(html_jgk != ""){
              accordionHtml+= "<h3 onclick=\"cardCompany.cardSetting('0','52')\"  >Julegavekortet</h3><div style=\" height: 480px; overflow-y: auto; \"><div>Antal: "+html_jgk_antal+"</div><table height=450>"+head+html_jgk+"</table></div>";
            }
            if(html_jgt != ""){
              accordionHtml+= "<h3 onclick=\"cardCompany.cardSetting('0','265')\"  >Julegave-typen</h3><div style=\" height: 480px; overflow-y: auto; \"><div>Antal: "+html_jgt_antal+"</div><table height=450>"+head+html_jgt+"</table></div>";
            }

            if(html_24gaver_400 != ""){
              accordionHtml+= "<h3 onclick=\"cardCompany.cardSetting('1','54')\" data=\"typeCard24\">24gaver 400</h3><div style=\" height: 480px; overflow-y: auto; \"><div>Antal: "+html_24gaver_400_antal+"</div><table height=450>"+head+html_24gaver_400+"</table></div>";
            }
            if(html_24gaver_560 != ""){
              accordionHtml+= "<h3 onclick=\"cardCompany.cardSetting('1','55')\" data=\"typeCard24\">24gaver 560</h3><div style=\" height: 480px; overflow-y: auto; \"><div>Antal: "+html_24gaver_560_antal+"</div><table height=450>"+head+html_24gaver_560+"</table></div>";
            }
            if(html_24gaver_640 != ""){
              accordionHtml+= "<h3 onclick=\"cardCompany.cardSetting('1','56')\" data=\"typeCard24\">24gaver 640</h3><div style=\" height: 480px; overflow-y: auto; \"><div>Antal: "+html_24gaver_640_antal+"</div><table height=450>"+head+html_24gaver_640+"</table></div>";
            }
            if(html_guld != ""){
              accordionHtml+= "<h3 onclick=\"cardCompany.cardSetting('1','53')\" data=\"typeCardGold\">Gold kortet</h3><div style=\" height: 480px; overflow-y: auto; \"><div>Antal: "+html_guld_antal+"</div><table height=450>"+head+html_guld+"</table></div>";
            }
            if(slettedeDoExist == true){
              accordionHtml+= "<h3 onclick=\"cardCompany.cardSetting('2','')\" data=\"typeCardGold\">-- SLETTEDE KORT --</h3><div style=\" height: 480px; overflow-y: auto; \"><div>Antal: "+html_deleted_antal+"</div><table height=450>"+head;
              for (var key in slettede) {
                if(slettede[key] != ""){
                    accordionHtml+= "<tr><td bgcolor=yellow colspan=9 align=center><b>"+shopIdList[key]+"</b></td></tr>";
                    accordionHtml+= slettede[key];
                }
              }
              accordionHtml+="</table></div>";
            }


            accordionHtml = "<div id=\"accordionCard\" >"+accordionHtml+"</div>";
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
    },
    stamdataTemplate:function(){
        var html="";
        html+='<table width="600"><tr><td width="150">Virksomhed:</td><td width="350"><input type="text" class="stamDataFormularShow dialog1_name_Show" /></td></tr>';
        html+='<tr><td width=150>adresse1: </td><td><input type="text"  class="stamDataFormularShow dialog1_bill_to_address_Show" /></td></tr><tr><td>adresse2: </td><td><input type="text" class="stamDataFormularShow dialog1_bill_to_address_2_Show" /></td></tr>';
        html+=' <tr><td>Postnr.:</td><td><input type="text" class="stamDataFormularShow dialog1_bill_to_postal_code_Show" /></td></tr>';
        html+=' <tr><td>By:</td><td><input type="text" class="stamDataFormularShow dialog1_bill_to_city_Show" /></td></tr>';
        html+=' <tr><td>EAN:</td><td><input type="text" class="stamDataFormularShow dialog1_ean_Show" /></td></tr>';
        html+=' <tr><td>CVR Nummer:</td><td><input type="text" class="stamDataFormularShow dialog1_cvr_Show" /></td></tr></table>';
        html+='<label><b>Leveringsadresse (udfyldes kun hvis forskellig fra virksomhedsadresse):</b></label><br /><table  width="600">';
        html+='<tr> <td width=175>adresse1: </td><td><input type="text" class="stamDataFormularShow dialog1_ship_to_address_Show" /></td></tr>';
        html+='<tr><td>adresse2: </td><td><input type="text" class="stamDataFormularShow dialog1_ship_to_address_2_Show" /></td></tr>';
        html+='<tr> <td>Postnr.:</td><td><input type="text" class="stamDataFormularShow dialog1_ship_to_postal_code_Show" /></td></tr>';
        html+='<tr> <td>By:</td><td><input type="text" class="stamDataFormularShow dialog1_ship_to_city_Show" /></td></tr></table><hr /><table  width="600">';
        html+='<tr> <td width="150">Fortrolig kontaktperson:</td><td width="350"><input type="text" class="stamDataFormularShow dialog1_contact_name_Show" /></td></tr>';
        html+='<tr> <td>Telefonnummer:</td><td><input type="text" class="stamDataFormularShow dialog1_contact_phone_Show" /></td></tr>';
        html+='<tr> <td>E-mailadresse:</td><td><input type="text" class="stamDataFormularShow dialog1_contact_email_Show" /></td></tr></table>';
        return html;

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
        if(confirm("Er du sikker du vil ændre 'Send hjem indstillingerne?'")){
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
            ajax({shop_id:_shopCardId},"shop/getShopPresents","cardCompany.buildShopPresentsList","");
  },
    buildShopPresentsList:function(response)
    {
        var tempHtml = "";
        var presentsHtml = "<center><table border=0 >";
        for(var i=0;response.data.length >i;i++){
            var modelJson = $.parseJSON(response.data[i].variant_list)

            if(modelJson.length > 0){
                tempHtml = "";
                gaveId = response.data[i].id;
                gaveNavn =  response.data[i].name;
                tempHtml = "";

               $.each(modelJson, function(i, item) {
                    if(item.language_id == "1"){
                        tempHtml+="<tr><td height=30 width=200>"+gaveNavn+"</td><td width=200>"+item.feltData[0].variant+"</td><td width=200>"+item.feltData[1].variantSub+"</td><td><button onclick=\"cardCompany.doChangeGift('"+gaveId+"','"+item.feltData[0].variant+"','"+item.feltData[1].variantSub+"','"+item.feltData[2].variantNr+"') \">Vælg</button></td></tr>";
                     }
                })
                presentsHtml+=tempHtml ;
            } else {
                presentsHtml+= "<tr><td height=30>"+response.data[i].name+"</td><td></td><td></td><td><button onclick=\"cardCompany.doChangeGift('"+response.data[i].id+"','"+response.data[i].name+"','','' ) \">Vælg</button></td></tr>";
            }

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
            "modelId":modelId,
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

  }

}





var encode = document.getElementById('encode'),
    decode = document.getElementById('decode'),
    output = document.getElementById('output'),
    input = document.getElementById('input');
