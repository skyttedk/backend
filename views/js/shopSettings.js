var  lukshopData = "";
var shopSettings = {

self : this,

init: async function(){
      this.initVoucher();
      this.hasDataChanged()
      this.initEvent()
},
initEvent:function(){
    $("#loadCustomReceiptTextModule").off('click').on('click', function (e) {
        e.preventDefault(); // valgfrit: undgå standard handling

        loadScriptOnce('views/js/customReceiptText.js', function () {
            DeliveryModule.init(); // Initialiser modulet
            DeliveryModule.open(); // Åbn modal
        });
    });
},
initVoucher:async function(){
    var hasVoucher = await voucher.companyHasVoucher(_companyID);

    if($("#finish_shop").is(':empty')  ){
        $("#shop-voucher").prop('disabled', true);
        $("#shop-voucher").html('You can first assign Voucher when shop is closed')
    } else {
    if(hasVoucher.data.HasVoucher == 0){
            $("#shop-voucher").unbind( "click" ).click( async function(){
               $("#shop-voucher").prop('disabled', true);
               $("#shop-voucher").html('System is working')
               var result =  await voucher.assign(_companyID);
               $("#shop-voucher").html('Voucher is assigned')
           })
       } else {
           $("#shop-voucher").prop('disabled', true);
           $("#shop-voucher").html('Voucher is assigned')
       }
    }


},

// grundet data i indstillinger kan ændre sig fra orderbekræftigelses skemaet og shopboard tjekkes disse og opdateres
hasDataChanged: async function (){
    // check periode start dato and slut dato
    let shop = await this.loadShopData();
    this.updateDataAllOver(shop.data.shop[0])
},
loadShopData:function ()
{

    return new Promise(function(resolve, reject)
    {

        $.ajax(
            {
                url: 'index.php?rt=shop/read',
                type: 'POST',
                dataType: 'json',
                data:{id: _editShopID}
            }
        ).done(function(res)
            {
                resolve(res)
            }
        )
    })
},
    // update start and end date in "indstillinger" because they are change elseware
updateDataAllOver:function(data)
{
    // set start and end dato
    if( data.start_date  != null){
        var str = data.start_date
        res = str.split("-");
        $("#shopFrom2").val(res[2]+"-"+res[1]+"-"+res[0]);
    }
    if(data.end_date != null){
        var str = data.end_date;
        res = str.split("-");
        $("#shopTo2").val(res[2]+"-"+res[1]+"-"+res[0]);
    }
    $("#shopFrom2").datepicker("destroy");
    $("#shopTo2").datepicker("destroy");

    $("#shopFrom2").datepicker({
        dateFormat: "dd-mm-yy",
        numberOfMonths: 3,
        showButtonPanel: true
    });
    $("#shopTo2").datepicker({
        dateFormat: "dd-mm-yy",
        numberOfMonths: 3,
        showButtonPanel: true
    });
},




showInsertGiftMenu:function(){
    ajax({"shop_id":_editShopID},"shop/getShopPresents","shopSettings.showInsertGiftMenuResponse","");


},
showInsertGiftMenuResponse:function(response){

             var tempHtml = "";
        presentsHtml = "<center><table border=1 >";
        for(var i=0;response.data.length >i;i++){
            var modelJson = $.parseJSON(response.data[i].variant_list)
         //   console.log(modelJson)
            if(modelJson.length > 0){
                tempHtml = "";
                gaveId = response.data[i].id;
                gaveNavn =  response.data[i].name;
                tempHtml = "";

               $.each(modelJson, function(i, item) {

                    if(item.language_id == "1"){
                        var modelData = [];


                        for(var i=0;item.feltData.length > i;i++){
                            var key = Object.keys(item.feltData[i])[0];
                            modelData[key] = item.feltData[i][key]
                        }
                        // console.log(modelData)

                        tempHtml+="<tr><td height=30 width=400>"+gaveNavn+"</td><td width=250>"+modelData['variant']+"</td><td width=250>"+modelData['variantSub']+"</td><td><button onclick=\"gavevalg.do1ChangeGift('"+gaveId+"','"+modelData['variant']+"','"+modelData['variantSub']+"','"+modelData['variantNr']+"','"+modelData['variantId']+"') \">V�lg</button></td></tr>";
                     }
                })
                presentsHtml+=tempHtml ;
            } else {
                presentsHtml+= "<tr><td height=30>"+response.data[i].name+"</td><td></td><td></td><td><button onclick=\"gavevalg.doChangeGif1t('"+response.data[i].id+"','','','','0' ) \">V�lg</button></td></tr>";
            }

        }
         presentsHtml+="</table></center>"


    $("#insertGiftMenu").html(presentsHtml+"<hr /><br />" );
    $("#insertGiftMenu").show();
},
setShopFinish:function()
{
    if (confirm("Vil du afslutte shoppen nu") == true) {
     shopSettings.initVoucher();

      var formData = {};
      formData["id"] = _editShopID;
      formData["close_date"] = new Date().toISOString().slice(0, 19).replace('T', ' ');

      $("#finish_shop").html("Shop afsluttet d. "+formData["close_date"]);
      ajax(formData,"shop/update","shopSettings.setShopFinishResponse","");
    }


},
    setShopFinishResponse:function(response){
        $("#finishBtn").html("Tryk for at &oelig;ndre afslutningsdato til i dag");
        $("#finish_shop").show();
        $("#ejValgteBtn").show();

    },
    finalFinishedNAV:function()
    {



        let confText = "Ønsker du at sætte shoppen til afstemt med Navision ?";
        let state = 1;
        if($("#finish_shop").html() == ""){
            confText = "Shoppen er IKKE alsluttet, ønsker du at sætte shoppen til afstemt med Navision";
            state = 1;
        }
        if(!$('#final_finished').is(":checked")){
            confText = "Ønsker du at ophæve, at shoppen er afstemt med Navision";
            state = 0;
        }


        let conf = confirm(confText)
        if(conf == false){
            state == 1 ? $("#final_finished").prop('checked', false) : $("#final_finished").prop('checked', true);
            return;
        }
        const enteredCode = prompt("Indtast 'luk1234' for at fortsætte:");
        const correctCode = "luk1234"; // Erstat med din ønskede kode

        if (enteredCode === correctCode) {
            alert("Koden er godkendt. Handlingen udføres. ");
        } else {
            alert("Koden er forkert. Handlingen blev annulleret.");
            $("#final_finished").prop('checked', false);
            die("end");
        }


        var formData = {};
        formData["id"] = _editShopID;
        formData["final_finished"] = state;
        ajax(formData,"shop/update","shopSettings.finalFinishedNAVResponse","");
    },
    finalFinishedNAVResponse:function(res)
    {
        console.log(res);
    },



updateShopIsActive:function(data){
    system.work();
    var formData = {};
    formData["id"] = _editShopID;
     // benyt active hvis du vil lukke p� den gamle m�de
//    formData["active"] = ( $(data).is(':checked') ? "1" : "0" );
    formData["soft_close"] = ( $(data).is(':checked') ? "0" : "1" );
    alert("lukker / Aabner shop")
    ajax(formData,"shop/update","shopSettings.response","");
},

updateShopIsTryout:function(data){
    system.work();
    var formData = {};
    formData["id"] = _editShopID;
    formData["is_demo"] = ( $(data).is(':checked') ? "1" : "0" );
    ajax(formData,"shop/update","shopSettings.response","");
},
updateShopIsBlock:function(data){
    system.work();
    var formData = {};
    formData["id"] = _editShopID;
    formData["blocked"] = ( $(data).is(':checked') ? "1" : "0" );
    ajax(formData,"shop/update","shopSettings.response","");
},
updateLanguage:function(formData){
    system.work();
            var shop ={};
            var language_enabled = {};
            language_enabled['lang_de'] = "0"
            language_enabled['lang_se'] = "0"
            language_enabled['lang_eng'] = "0"
            language_enabled['lang_no'] = "0"
            language_enabled['lang_dk'] = "0"
            if($("#lang_dk").is(':checked')) {
                language_enabled['lang_dk'] = "1";
            }
            if($("#lang_no").is(':checked')) {
               language_enabled['lang_no'] = "1";
            }
            if($("#lang_eng").is(':checked')) {
                language_enabled['lang_eng'] = "1";
            }
            if($("#lang_se").is(':checked')) {
              language_enabled['lang_se'] = "1";
            }
            if($("#lang_de").is(':checked')) {
              language_enabled['lang_de'] = "1";
             }

           shop['language_settings']    =    '{"lang_dk":'+language_enabled['lang_dk']+',"lang_no":'+language_enabled['lang_no']+',"lang_eng":'+language_enabled['lang_eng']+',"lang_se":'+language_enabled['lang_se']+',"lang_de":'+language_enabled['lang_de']+'}'
           shop["id"] = _editShopID;
           ajax(shop,"shop/update","shopSettings.response","");

},
updateStartDato:function(){


},
updateEndDato:function(formData){

},
updateMailToCustomer:function(formData){

},
checkData:function(){
     system.work();
     var shop = {};
    ajax(shop,"shop/checkShopSettings","shopSettings.sysCheckResponse","");

},
response :function(){
    shopSettings.checkData()
},
sysCheckResponse: function(){
    system.endWork();
}



}