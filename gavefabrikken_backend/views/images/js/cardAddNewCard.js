// cardAddNewCard.js
var _ajaxPath = "" // https://system.gavefabrikken.dk//gavefabrikken_backend/index.php?rt=";
var _regInNAV = "1";

var _menuGaveCard,_menuValueCard,_menuDeadlineCard;
var earlyorderQuickListData_da = [
  {itemNumber:"NTB711A" ,text:"Cerruti weekendtaske "},
  {itemNumber:"200131" ,text:"TJ smartwatch rosa "},
  {itemNumber:"200132" ,text:"TJ smartwatch sort "},
  {itemNumber:"SAM1481" ,text:"RC asietter (3stk) "},
  {itemNumber:"10104-TW5168-A46" ,text:"GJD senget&oslash;j gr&aring; 140x200 "},
  {itemNumber:"10104-TW5168-A47" ,text:"GJD senget&oslash;j gr&aring; 140x220 "},
  {itemNumber:"1060-1" ,text:"Chokolade 108 stk "},
  {itemNumber:"200405" ,text:"Comwell "},
   {itemNumber:"12030" ,text:"Miiego MiiBlaster "},
   {itemNumber:"20811791BTB" ,text:"AJ8 Bellevue, gr&aring; "},
   {itemNumber:"20811794BTB" ,text:"AJ8 Bellevue, sort "},
   {itemNumber:"20811723BTB" ,text:"AJ8 Bellevue, hvid "},
   {itemNumber:"1644126" ,text:"B&OE8 in ear "},
   {itemNumber:"J25310201" ,text:"B&oslash;rge Mogensen skammel "}


];
earlyorderQuickListData_da = []

var earlyorderQuickListData_no = [
  {itemNumber:"774586" ,text:"Lion Sabatier Pluton knivsett"},
  {itemNumber:"180123" ,text:"Oslo Weekend veske"},
  {itemNumber:"N170063" ,text:"The Well De luxe"},
  {itemNumber:"20723101" ,text:"Verner Panton  VP3 matt hvit"},
  {itemNumber:"20721BTB" ,text:"Verner Panton VP3 matt sort"},
  {itemNumber:"190109" ,text:"Explorer Ryggsekk 85L"},
  {itemNumber:"2030-1063" ,text:"Luksussjokolade"},
  {itemNumber:"1011713770" ,text:"Sodastream Spirit sett"},
  {itemNumber:"190111" ,text:"GJD Weekendbag gr&aring;"},
  {itemNumber:"N180138" ,text:"Hatte-eske med marsipanhjerter, gullhjerter 260g"}
];

earlyorderQuickListData_no = [ ]

var cardAddNewCard = {


    loadEarly: async function(){
      earlyorderQuickListData_no = [ ]
      earlyorderQuickListData_da = []  
        return new Promise(function(resolve, reject) {
               $.post(_ajaxPath+"earlypresent/read",{}, function(res, status) {
                    if(res.status == 0) {  return; }
                    else {
                        var temp = [];
                        let list = res.data.early;

                        for(let i=0;list.length > i;i++){
                            if(list[i].language == 1){
                                earlyorderQuickListData_da.push({itemNumber:list[i].item_nr,text:list[i].description})
                            }
                            if(list[i].language == 4){
                                earlyorderQuickListData_no.push({itemNumber:list[i].item_nr,text:list[i].description})
                            }
                        }
                       console.log(earlyorderQuickListData_da)


                    resolve()

                    }
                }, "json");
        })
    },
    showMultibleAddr: async function(){
        _multibleOrdre = [];
        companyList = await cardAddNewCard.loadChildAddr();
        if(companyList.status == 1){
            cardAddNewCard.buildChildAddHtml(companyList.data.result);
        } else {
          alert("An error has occurred")
        }

        $( "#cardToMultibleAddrWrapper" ).toggle( "slow", function() {

        });
    },
    loadChildAddr:function(){

         return new Promise(function(resolve, reject) {
         $.ajax(
            {
            url: 'index.php?rt=company/getChildsCompany',
            type: 'POST',
            dataType: 'json',
            data: {id:_selectedCompany}
            }).done(function(res) {
            if(res.status == 0) { resolve(res) }
            else { resolve(res) }
          })
        })

    },
    buildChildAddHtml:function(data){
        let html = "<table width=100%>";
        for(var i=0;i < data.length;i++){
            html+="<tr ><td width=80%>"+data[i].ship_to_company+"<br>"+data[i].ship_to_city+"<br>"+data[i].ship_to_postal_code+"<br>"+data[i].contact_name+"<br>"+data[i].contact_email+"</td>";
            html+="<td  width=20%> <input id='"+data[i].id+"' class='childsCompanyOrder' type='number' value='0' max='100' min='0' /></td>";
            html+="</tr>";
        }
        html+="</table>";
        if(data.length == 0){ html="<p>Denne virksomhed har ingen ekstra adresser tilknyttet</p>" }
        $("#cardToMultibleAddrList").html(html);
    },

    showEarly: async function()
    {
        await cardAddNewCard.loadEarly()
        $( "#earlyGiftWrapper" ).toggle( "slow", function() {
            cardAddNewCard.buildEarlyorderQuickList();
        });
    },

    buildEarlyorderQuickList:function(){
        var html ="";
        var earlyorderQuickListData = "";
        if(_menuGaveCard != "574" && _menuGaveCard != "norge"){
             earlyorderQuickListData = earlyorderQuickListData_da;
            html+= "<div class='earlorder-quick-text'><label class='earlorder-quick-text'>Lille choko </label><input class='earlorder-quick-order' data='22030024' type='checkbox' /></div><hr>"
        } else {
            earlyorderQuickListData = earlyorderQuickListData_no;
         //  html+= "<div class='earlorder-quick-text'><label class='earlorder-quick-text'>1Kg Luksus sjokolade</label><input class='earlorder-quick-order' data='2030-1063' type='checkbox' /></div><hr>"
        }





        $.each(earlyorderQuickListData, function( index, value ) {
          if(value.text == ""){
                html+= "<hr>";
          } else {
               html+= "<div class='earlorder-quick-text'><label >"+value.text+"</label><input class='earlorder-quick-order' data='"+value.itemNumber+"' type='checkbox' /></div>"
          }

        });

        $("#earlyorderQuickList").html(html+"<hr>");

    },

    showMedal:function(){

//alert("hej It er lige ved at lave aendre ting, vent ca 15 min.")


        $('.formGavekort').prop('selectedIndex',0);
        $('.formSelectValue').prop('selectedIndex',0);
        $('.formDeadline').prop('selectedIndex',0);
         $("#cardFormSaleperson").val("");
        $(".cardToSelect").val(0);
        $(".only24").hide();
        $(".onlyNorge").hide();
        $(".cardFormDeadline").hide();
        $(".cardFormNumber").hide();
        $(".cardAddNewCard").hide();
        $(".cardFormToSendMethod").hide();
        $("#sp").val("");
        $('#giftwrap').prop('checked', false);
        $('#giftSpeLev').prop('checked', false);
    //    $('#freeDelivery').prop('checked', false);
        $('#earlyShow').prop('checked', false);
        $('#useMultibleAddr').prop('checked', false);
        $('#cardToMultibleAddrList').html('');
        $('#cardToMultibleAddrWrapper').hide();
        $('#earlyGiftWrapper').hide();
        $('#earlyorderList').val("");
       // $('#cardFormShipping').val("");

        cardAddNewCard.buildEarlyorderQuickList();

        $(".home").hide();
      _menuValueCard = "";
      dialog =  $( "#dialog_message_AddNewCard" ).dialog({
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
       cardAddNewCard.checkCardAmount();
    },
    checkCardAmount:function(shopid){
        $("#cardAmountInShop").html("");



        ajax({id:_selectedCompany},"shop/getCardAmount","cardAddNewCard.checkCardAmountReturn","");
    },
    checkCardAmountReturn:function(response){
        if(response.data != "0"){
            $("#cardAmountInShop").html("Antal kort oprettet i shoppen: "+response.data);
        }

    },
    menuGaveCard:function(val){

        $( "#earlyGiftWrapper" ).hide();
        _menuValueCard = "";
        _menuGaveCard = val;

        $('#earlyShow').prop('checked', false);
        $('#giftwrap').prop('checked', false);
        $("#giftwrapContainer").show();
        $(".notNorge").show();
        $(".showNorge").hide();
        $("#cardToMultibleAdd").show();
        $("#earlyGift").show();
        $("#giftSpecialDev").show();
        $("#giftwrapContainer").show();


       // $(".showNorge").html("<label>Skal gaven indpakkes: </label><input id=\"giftwrap\" type=\"checkbox\" />");

        $('.formSelectValue').prop('selectedIndex',0);
        $('.formDeadline').prop('selectedIndex',0);
        $(".home").hide();
        $(".only24").hide();
        $(".onlyDrom").hide();
        $(".cardFormDeadline").hide();

        $('.formSelectValue1').prop('selectedIndex',0);
        $('.formSelectValue2').prop('selectedIndex',0);
        $('.formSelectValue3').prop('selectedIndex',0);



        $(".cardAddNewCard").hide();
        $(".onlyNorge").hide();
         if(val == "52"){
               $("#giftwrapContainer").hide();
         }

        if(val == "53"){
            $(".home").show();
            $("#giftwrapContainer").hide();
        }

        if(val == "onlyDrom") {
           $(".onlyDrom").show();
            //$(".home").show();

        }


        if(val == "24Gaver"){
           $("#giftwrapContainer").hide();
           $(".only24").show();
            $(".home").show();

        }
        if(val == "norge"){
            $(".onlyNorge").show();
            $(".showNorge").show();
        }

        if(val == "1832"){
            $("#cardToMultibleAdd").hide();
            $("#earlyGift").hide();
            $("#giftSpecialDev").hide();
            $("#giftwrapContainer").hide();
        }
        if(val == "1981"){
            $("#cardToMultibleAdd").hide();
            $("#earlyGift").hide();
            $("#giftSpecialDev").hide();
            $("#giftwrapContainer").hide();
        }

        if(val == "4793"){
            $("#cardToMultibleAdd").hide();
            $("#earlyGift").hide();
            $("#giftSpecialDev").hide();
            $("#giftwrapContainer").hide();
        }

        if(val == "5117"){
            $("#cardToMultibleAdd").hide();
            $("#earlyGift").hide();
            $("#giftSpecialDev").hide();
            $("#giftwrapContainer").hide();
        }


        cardAddNewCard.menuValueCard(_menuGaveCard);
    },
    menuValueCard:function(val){
         _menuValueCard = val;

         $(".cardFormDeadline").show();
         $(".deadline_groupe").hide();
         if(val == "52" ||  val == "265" ||  val == "575"){
                $(".deadline_groupe_1").show();
         }
         if(val == "54" || val == "55" || val == "56"  || val == "53" || val == "287" || val == "290" || val == "310"){
            $(".deadline_groupe_2").show();
         }
         if(val == "57" || val == "58" || val == "59" || val == "272"){
           $(".deadline_groupe_3").show();
         }
         if(val == "1832"){
           $(".deadline_groupe_4").show();
         }
        if(val == "5117"){
            $(".deadline_groupe_4").show();
        }
        if(val == "4793"){
            $(".deadline_groupe_4").show();
        }
         if(val == "1981"){
           $(".deadline_groupe_4").show();
         }
         if(val == "574"){
           $(".deadline_groupe_5").show();
         }




    },
    menuDeadlineCard:function(val){
        $(".cardFormNumber").show();
        $(".cardFormToSendMethod").show();

        $(".cardAddNewCard").show();
        _menuDeadlineCard  = val;
        var url_string = window.location.href
        var url = new URL(url_string);
        var sysid = url.searchParams.get("systemuser_id");
         // kun adgang for tulin
        $(".nobill").hide();
        if(sysid == 63 || sysid == 72 ||sysid == 75 ||sysid == 86 ||sysid == 72 ||sysid == 75){
            $(".nobill").show();
        }
    },
    selectCardFromPoolNoNAV:function(){
        _regInNAV = "0";
        cardAddNewCard.selectCardFromPool();
    },

    selectCardFromPool: async function(){
      var error = false;
        $(".cardAddNewCard").hide();
        var shop_id =_menuGaveCard;
        var cardVal = 0;
        var is_delivery = "0";

        if(shop_id == "52"){
            cardVal = "560";
        }
        if(shop_id == "265"){
            cardVal = "600";
        }
        if(shop_id == "53"){
            cardVal = "800";
        }

        if(shop_id == "574"){
            cardVal = "1000";
        }

        if(shop_id == "575"){
            cardVal = "640";
        }
        if(shop_id == "1832"){
            cardVal = "400";
        }
        if(shop_id == "1981"){
            cardVal = "800";
        }

        if(shop_id == "4793"){
            cardVal = "300";
        }
        if(shop_id == "5117"){
            cardVal = "600";
        }






        if(shop_id != "52" && shop_id != "53" && shop_id != "265"  ){

            if(shop_id == "24Gaver"){
                shop_id = $(".formSelectValue1").val();
                if(shop_id == "54"){ cardVal = "400" }
                if(shop_id == "55"){ cardVal = "560"  }
                if(shop_id == "56"){ cardVal = "640" }
            }
            if(shop_id == "norge") {
                shop_id = $(".formSelectValue2").val();
                if(shop_id == "272"){ cardVal = "300" }
                if(shop_id == "57"){ cardVal = "400" }
                if(shop_id == "58"){ cardVal = "600" }
                if(shop_id == "59"){ cardVal = "800" }
            }
            if(shop_id == "onlyDrom"){
                shop_id = $(".formSelectValue3").val();
                if(shop_id == "287"){ cardVal = "100" }
                if(shop_id == "290"){ cardVal = "200"  }
                if(shop_id == "310"){ cardVal = "300" }
            }
        }
        if(
            _menuDeadlineCard == "2021-04-01" ||
            _menuDeadlineCard == "2020-11-07" ||
            _menuDeadlineCard == "2021-01-03" ||
            _menuDeadlineCard == "2021-12-31"

        ){
            is_delivery = "1";
        }




        var allOkay = true;

        var  giftwrap = "0";
        if($("#giftwrap").is(':checked')){
           giftwrap = "1";
        }
        var giftSpeLev = "0";
        if($("#giftSpeLev").is(':checked')){
           giftSpeLev = "1";
        }
        if($(".giftSpeLev").is(':checked')){
           giftSpeLev = "1";
        }

        var freeDelivery = "0";
        /*
        if($("#freeDelivery").is(':checked')){
           freeDelivery = "1";
        }
        if($(".freeDelivery").is(':checked')){
           freeDelivery = "1";
        }
        */



        if($(".giftnuskaldetvirke").is(':checked')){
           giftwrap = "1";
        }

        if(document.getElementById("giftwrap").checked == true){
    		giftwrap = "1";
        }


        var earlyOrder = "0";
        if($("#earlyOrder").is(':checked')){
           earlyOrder = "1";
        }
        /* -----  earlyorder   */
        var earlOrderList = ""

         $(".earlorder-quick-order").each(function( index, value ) {
                if($(this).is(':checked')){
                   earlOrderList+= $(this).attr("data")+"\n";
                }
         })
         if(earlOrderList == ""){
            earlOrderList = $('#earlyorderList').val();
         } else {
            earlOrderList+= $('#earlyorderList').val();
         }
         /*
         if(giftSpeLev == "1"){
           earlOrderList+= "PL1-SP"+"\n";
         }
           */
        /* Order from ekstra addr */

        // check for Norge om hjemmelevering kun er e-kort
        if(_menuDeadlineCard == "2021-01-03" || _menuDeadlineCard == "2020-11-07"){
          if($('input[name=cardFormToSendMethod]:checked').val() == "0"){
             error = true;
             alert("Deadline only for email codes")
          }
        }




        var childsCompanyOrder = [];

            if($("#useMultibleAddr").is(':checked')){
                $(".childsCompanyOrder").each(function( index, value ) {
                    if($(this).val() > -1 && $(this).val() < 100){
                        childsCompanyOrder.push({id:$(this).attr("id"),number:$(this).val()})
                    } else {
                        alert("ikke alle kort kan oprettet, da ekstra adresser kun kan bestille mellem 1 og 100 kort")
                        error = true;
                    }
                 })
            }

        var formData = {

            "spdealtxt":$("#sp").val(),
            "earlyOrder": earlyOrder,
            "salesperson": $("#saleperson").val(),
            "salenote": $("#cardFormSaleperson").val(),
            "quantity": $(".cardToSelect").val(),
            "expire_date": _menuDeadlineCard,
            "value":cardVal,
            "company_id": _selectedCompany,
            "shop_id": shop_id,
            "is_delivery":is_delivery,
            "is_email":$('input[name=cardFormToSendMethod]:checked').val(),
            "giftwrap":giftwrap,
            "earlyorderList":earlOrderList,
            "regInNAV":_regInNAV,
            "giftSpeLev":giftSpeLev,
            "freeDelivery":freeDelivery
        }
        _regInNAV = "1";
        if($("#saleperson").val() == ""){
            alert("Du mangler at udfylde saelger feltet")
            $(".cardAddNewCard").show();
        } else {
              if(giftwrap == "1"){
                alert("du har valgt indpakning")
               }

               /*if(_menuDeadlineCard == "2020-11-29" && $('input[name=cardFormToSendMethod]:checked').val() == "0"){
                    alert("Du kan kun vaelge koder pr mail til denne deadline")
                     $(".cardAddNewCard").show();
               } else { */
                    if(error == false){
                         if(childsCompanyOrder.length > 0){
                                let i = 1;
                               // await cardAddNewCard.doSelectCardFromPool(formData);
                                if(childsCompanyOrder.length > 0){
                                      childsCompanyOrder.forEach(async (item) => {
                                        formData.company_id = item.id
                                        formData.quantity = item.number
                                        await cardAddNewCard.doSelectCardFromPool(formData);
                                        i++;
                                        if(childsCompanyOrder.length == i ){
                                            alert("kort oprettet")
                                            dialog.dialog( "close" );
                                            cardCompany.updateCompanyCards(_selectedCompany)
                                        }
                                    })
                                }
//                            ajax(formData,"company/addCompanyOrder_multi","cardAddNewCard.selectCardFromPoolResponse","");

                         } else {
                            ajax(formData,"company/addCompanyOrder","cardAddNewCard.selectCardFromPoolResponse","");
                            $(".cardFormNumber").show();
                            $(".cardAddNewCard").show();
                         }


                    } else {
                      $(".cardAddNewCard").show();
                      $(".nobill").hide();

                    }
               /* } */


        }

    },
    doSelectCardFromPool:function(postData){
         return new Promise(function(resolve, reject) {
         $.ajax(
            {
            url: 'index.php?rt=company/addCompanyOrder',
            type: 'POST',
            dataType: 'json',
            data: postData
            }).done(function(res) {
            if(res.status == 0) { resolve(res) }
            else { resolve(res) }
          })
        })
    },



    selectCardFromPoolResponse:function(response){

       dialog.dialog( "close" );
        if(response.status == "1"){
            alert("kort oprettet")
            cardCompany.loadOrderData();

        } else {
           Alert("Der er sket en fejl.")
            $(".cardAddNewCard").show();
        }
    }


}