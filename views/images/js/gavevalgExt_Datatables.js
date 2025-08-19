         // [[['Verwerkende industrie sadfsadfas dfa d fsda ads sad ', 9],['Retail', 8], ['Primaire producent', 7],['Out of home', 6],['Groothandel', 5], ['Grondstof', 4], ['Consument', 3], ['Bewerkende industrie', 2]]]
    var plotCount = {};
    var plotTotal = 0;
    plotCount["Ej valgt" ] = 0;
       
    var gavevalg = {
    fieldsDeffData:{},
    fieldsDeffDB:[],
    userData:{},
    userDataDB:[],
    userOrderDataDB:[],
    presentList:{},
    presentsHtml:"",
    companyId:"",
    changeGiftUserId:"",
    tempUserId:"",

    init:function(){
            plotCount = {};
            plotTotal = 0;
            plotCount["Ej valgt" ] = 0;


        $("#gavevalgContainer").html("");
        this.fieldsDeffData = {};
        this.fieldsDeffDB = [];
        this.userData={};
        this.userDataDB=[];
        this.userOrderDataDB=[];
        this.presentList={};

        this.loadPresentsList();


    },
    loadPresentsList:function(){
            ajax({"shop_id":_editShopID},"shop/getShopPresents","gavevalg.processPresentsList","");
    },
    processPresentsList:function(response)
    {
        var tempHtml = "";
        this.presentsHtml = "<center><table border=0 >";
        for(var i=0;response.data.length >i;i++){
            var modelJson = $.parseJSON(response.data[i].variant_list)

            if(modelJson.length > 0){
                tempHtml = "";
                gaveId = response.data[i].id;
                gaveNavn =  response.data[i].name;
                tempHtml = "";

               $.each(modelJson, function(i, item) {
                    if(item.language_id == "1"){
                        tempHtml+="<tr><td height=30 width=200>"+gaveNavn+"</td><td width=200>"+item.feltData[0].variant+"</td><td width=200>"+item.feltData[1].variantSub+"</td><td><button onclick=\"gavevalg.doChangeGift('"+gaveId+"','"+item.feltData[0].variant+"','"+item.feltData[1].variantSub+"','"+item.feltData[2].variantNr+"') \">Vælg</button></td></tr>";
                     }
                })
                this.presentsHtml+=tempHtml ;
            } else {
                this.presentsHtml+= "<tr><td height=30>"+response.data[i].name+"</td><td></td><td></td><td><button onclick=\"gavevalg.doChangeGift('"+response.data[i].id+"','','','' ) \">Vælg</button></td></tr>";
            }

        }
         this.presentsHtml+="</table></center>"
         $( "#gavevalgSkiftGave").html(this.presentsHtml)

        this.loadFieldsDeff();
    },
    loadFieldsDeff:function(){
            ajax({"id":_editShopID},"shop/getShopAttributes","gavevalg.loadUserData","");
    },
    loadUserData:function(response){
        this.fieldsDeffData = response.data.attributes;

        this.companyId = response.data.company_id;
        ajax({"id":_editShopID},"shopload/getUsers","gavevalg.buildDB","");
    },
    buildDB:function(response){

        this.userData = response.data.users

        for(var j=0;this.fieldsDeffData.length > j;j++)
        {
            this.fieldsDeffDB.push({"id":this.fieldsDeffData[j].id,"name":this.fieldsDeffData[j].name,"is_list":this.fieldsDeffData[j].is_list,"list_data":this.fieldsDeffData[j].list_data });
        }
       // console.log(this.fieldsDeffDB);

        for(var j=0;this.userData.length > j;j++)
        {

            var temp = [];

            for(var i=0;this.userData[j].user_attributes.length > i;i++)
            {
                temp["id_"+this.userData[j].user_attributes[i].attributes.attribute_id] = this.userData[j].user_attributes[i].attributes.attribute_value;
            }
           // console.log(temp);
            this.userDataDB.push( {"id":this.userData[j].id,"data":temp} );
            plotTotal++;

            if(this.userData[j].has_orders == true){
                    if(plotCount[this.userData[j].orders[0].present_name] == undefined){
                       plotCount[this.userData[j].orders[0].present_name] = 1;
                    } else {
                        plotCount[this.userData[j].orders[0].present_name]+=1;
                    }
                this.userOrderDataDB["gift_"+this.userData[j].id]  = {"id":this.userData[j].orders[0].present_id,"model":this.userData[j].orders[0].present_model_name,"name":this.userData[j].orders[0].present_name,"registered":this.userData[j].orders[0].registered,"registered_date":this.userData[j].orders[0].registered_date,"present_model_present_no":this.userData[j].orders[0].present_model_present_no};
            } else {
                plotCount["Ej valgt"]+=1
                this.userOrderDataDB["gift_"+this.userData[j].id] = {"id":""};
            }


        }
        // [[['Verwerkende industrie sadfsadfas dfa d fsda ads sad ', 9],['Retail', 8], ['Primaire producent', 7],['Out of home', 6],['Groothandel', 5], ['Grondstof', 4], ['Consument', 3], ['Bewerkende industrie', 2]]]
        var data1 = [];
        //plotTotal Math.round(x)
        for (var key in plotCount) {
            var tal = (plotCount[key] / plotTotal)*100
            data1.push([plotCount[key]+"-"+key,Math.round(tal)])
        }
        data = [];
        data.push(data1)
        buildPie(data)
        this.buildTableHtml();
    },
    buildTableHtml:function()
    {
        var ingenValgte = [];
        var dropDownLists = [];
        var dataOrder = [];
        var html = "";
        dialogHtml = "<br /><table width=500>" ;
   //**     html = "<table class='gavevalg'><tr>";
        html = "<div class=\"gavevalg\"><table width=100%  id=\"userTable\"><thead><tr>";
        for(var i=0;this.fieldsDeffDB.length >i;i++){
            if(this.fieldsDeffDB[i].is_list == "1"){
               dropDownLists["drop_"+this.fieldsDeffDB[i].id] = this.fieldsDeffDB[i].list_data;
            }
            html+="<th data-id='"+this.fieldsDeffDB[i].id+"'>"+this.fieldsDeffDB[i].name+"</th>";
            dataOrder.push(this.fieldsDeffDB[i].id);
            dialogHtml+="<tr><td>"+this.fieldsDeffDB[i].name+"</td><td><input data-id='"+this.fieldsDeffDB[i].id+"' type=\"text\" /></td>";
        }

        html+="<th>Gave Titel</th><th>Model</th><th class=\"ba_admin\" valign=top><label>Bruger admin:</label><br /><img width=\"20\" height=\"20\" src=\"views/media/icon/excel2.jpg\" title=\"Download liste over dem der ikke har valgt\" onclick=\"gavevalg.downloadExcel2() \"><img width=\"20\" height=\"20\" src=\"views/media/icon/excel.png\" title=\"Download alle informationer\" onclick=\"gavevalg.downloadExcel() \"><img width=\"20\" height=\"20\" src=\"views/media/icon/1373253494_plus_64.png\" title=\"Opret ny bruger\" onclick=\"gavevalg.showCreateNew() \"><img width=\"20\" title=\"Send en mail til dem som ikke har valgt gave\" height=\"20\" src=\"views/media/icon/1373253286_letter_64.png \" onclick=\"gavevalg.sendMail() \"></td><td class=\"qr_admin\" valign=top><label >Udlevering admin:</label><br><img width=\"20\" height=\"20\" src=\"views/media/icon/excel.png\" title=\"Download list over dem der ikke har hentet deres gave\" onclick=\"gavevalg.qrExcel() \"><img width=\"20\" title=\"Send en mail til dem som ikke hentet deres gave\" height=\"20\" src=\"views/media/icon/1373253286_letter_64.png \" onclick=\"gavevalg.qrSendMail() \"></th></tr>";
        html+="</thead><tbody>"
        dialogHtml+="</table>";
        $( "#gavevalgDialog" ).html(dialogHtml);

        //xx

        for(var i=0;this.userDataDB.length >i;i++){
            var delived = "";
            html+="<tr id=\"gavevalgrow_"+this.userDataDB[i].id+"\">";
            for(var j=0;dataOrder.length>j;j++){
               // html+="<td>"+this.userDataDB[i].data["id_"+dataOrder[j]]+"</td>";
               var selectHtml;
               if( dropDownLists["drop_"+dataOrder[j]]  != null )  {
                var selectHtml = "<select data="+dataOrder[j]+" class=\"selectBoxElement\" style=\" padding:2px;  \">";
                    var tempDrop = "";
                    var selectData = dropDownLists["drop_"+dataOrder[j]].split("\n");
                    if(this.userDataDB[i].data["id_"+dataOrder[j]] == ""){
                           selectHtml+="<option  selected  value=\"\">Vælg</option>"
                    } else {
                        tempDrop = this.userDataDB[i].data["id_"+dataOrder[j]];
                        selectHtml+="<option  selected value=\""+this.userDataDB[i].data["id_"+dataOrder[j]]+"\">"+this.userDataDB[i].data["id_"+dataOrder[j]]+"</option>"
                    }
                    for(var jj=0;selectData.length > jj;jj++){
                        if(tempDrop != selectData[jj]){
                            selectHtml+="<option  value=\""+this.userDataDB[i].data["id_"+dataOrder[j]]+"\">"+selectData[jj]+"</option>"
                        }

                    }
                  selectHtml+= "</select>";
                  html+="<td>"+selectHtml+"</td>";
               } else {
                  html+="<td data-sort='"+this.userDataDB[i].data["id_"+dataOrder[j]]+"'><input field-id='"+dataOrder[j]+"' type='text' value='"+this.userDataDB[i].data["id_"+dataOrder[j]]+"' /></td>";
               }

            }

            if(this.userOrderDataDB["gift_"+this.userDataDB[i].id].id == ""){
                var selectedGift = "<td>Ikke valgt gave</td><td></td>";
            }  else {
                var modelStr =  this.userOrderDataDB["gift_"+this.userDataDB[i].id].model;
                modelStr = modelStr.replace("###"," - ");
                var selectedGift = "<td id='giftTitle_"+this.userDataDB[i].id+"'>"+this.userOrderDataDB["gift_"+this.userDataDB[i].id].name +"</td><td  id='giftModel_"+this.userDataDB[i].id+"'>"+modelStr +"</td>";
                if(this.userOrderDataDB["gift_"+this.userDataDB[i].id].registered == "0"){
                    delived = "<img width=\"25\" height=\"25\" src=\"views/media/icon/notdelived.png\" id=\"delived_"+this.userDataDB[i].id+"\" title=\"Gave ikke udleveret\" onclick=\"gavevalg.newInitRegDelive('"+this.userDataDB[i].id+"') \" />";
                } else {
                   //delived = "<img alert('Gaven er udleveret display: \n"+this.userOrderDataDB["gift_"+this.userDataDB[i].id].registered_date+"') width=\"25\" height=\"25\" src=\"views/media/icon/delived.png\" title=\""+this.userOrderDataDB["gift_"+this.userDataDB[i].id].registered_date+"\"  />"
                   delived = "<img width=\"25\" height=\"25\" src=\"views/media/icon/delived.png\" id=\"delived_"+this.userDataDB[i].id+"\" title=\"Gave er udleveret\" onclick=\"gavevalg.newInitUnRegDelive('"+this.userDataDB[i].id+"') \" />";
                }
            }


            //html+="<td> <input type=\"text\" disabled  title=\""+selectedGift+"\" name=\"usrname\" maxlength=\"20\" value=\""+selectedGift+"\"></td>";
            html+= selectedGift

//          html+="<td><select ><option value='0' style=\"color:blue;\">"+selectedGift+" </option>"+this.presentsHtml+"</select></td>";
            html+="<td class=\"ba_admin\"><img width=\"20\" height=\"20\" src=\"views/media/icon/1373253284_save_64.png\" title=\"Gem\" onclick=\"gavevalg.updateUserData('"+this.userDataDB[i].id+"') \">";

            if(this.userOrderDataDB["gift_"+this.userDataDB[i].id].id == ""){
                html+="<img width=\"23\" height=\"23\" title=\"Skift gave\" src=\"views/media/icon/gave.png\" title=\"Skift gave\"  onclick=\"gavevalg.changeGiftShowMenu('"+this.userDataDB[i].id+"')\">";
                html+="<img width=\"20\" height=\"20\" src=\"views/media/icon/PurchaseNoOrder-50.png\" title=\"Ingen kvittering, ej valgt gave\" ) \">";
            } else {
                html+="<img width=\"23\" height=\"23\" src=\"views/media/icon/history.png\" title=\"Vis tidligere valg\" onclick=\"gavevalg.loadHistory('"+this.userDataDB[i].id+"') \">" ;
                html+="<img width=\"23\" height=\"23\" title=\"Skift gave\" src=\"views/media/icon/gave.png\" title=\"Skift gave\"  onclick=\"gavevalg.changeGiftShowMenu('"+this.userDataDB[i].id+"') \">";
                html+="<img width=\"20\" height=\"20\" src=\"views/media/icon/Purchase Order-50.png\" title=\"Send kvittering\"  onclick=\"gavevalg.printReceipt('"+this.userDataDB[i].id+"') \">";
            }

            html+="<img width=\"20\" height=\"20\" src=\"views/media/icon/1373253296_delete_64.png\" title=\"Slet\" onclick=\"gavevalg.deleteUser('"+this.userDataDB[i].id+"') \">" ;
            html+="<td class=\"qr_admin\" >"+delived+"</td></tr>";


        }


        //html+="</tr></table>";
        //$("#gavevalgContainer").html(html);

        html+="</tr></tbody></table></div><br /><br /><br /><br /><br /><br />";
        $("#gavevalgContainer").html(html);
        $("#userTable").DataTable( {
           "scrollCollapse": true,
          "paging":         true,
          "searching": true,
          "pageLength": 100,
          "scrollY": _calcHeight+"px"


        } );

        //"oLanguage": { sLengthMenu: "_MENU_"}


        ajax({"shop_id":_editShopID},"shop/isOpenForRegistration","initRegistration");
    },
    loadHistory:function(userId){
            $( "#kundeDialog" ).html("systemt arbejder, vent venligt")
            dialog =  $( "#kundeDialog" ).dialog({
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

        ajax({'userId':userId},"shop2/getCardHistory","gavevalg.loadHistoryResponse","");
    },
    loadHistoryResponse:function(response){
        if(response.data == 0){
            $( "#kundeDialog" ).html("Der blev ikke fundet noget")
        } else {
            $( "#kundeDialog" ).html(Base64.decode(response.data))
        }
    },
    updateUserData:function(id)
    {
         var attributes = []
         var len = ($( "#gavevalgrow_"+id+" :input").length) ;
         $( "#gavevalgrow_"+id+" :input").each(function( index, ele ) {
            if($(ele).get(0).tagName == "INPUT" && len > index ){
                attributes.push({"attribute_id":$(ele).attr("field-id"),"attribute_value":$(ele).val()})
            }
               //console.log(this.attr("field-id"))
         })
        $( "#gavevalgrow_"+id).find(".selectBoxElement").each(function( index ) {
            var feltKey = $( this ).attr('data');
            var feltVal = $( this ).find(":selected").text();

            attributes.push({"attribute_id":feltKey,"attribute_value":feltVal});

        });





         var formData = {
             "attributes":JSON.stringify(attributes),
             "shop_id":_editShopID,
             "user_id":id}

        ajax(formData,"order/updateShopUserCustomerPanel","gavevalg.updateUserDataResponse","");
    },
    updateUserDataResponse:function(response){
        showSysMsg("Bruger opdateret")
    },
    deleteUser:function(id){
        if (confirm("Er du sikker p&aring; du vil slette!") == true) {
            ajax({"user_id":id},"shop/removeShopUser","gavevalg.deleteUserResponse","");
        }
    },
    deleteUserResponse:function(response){
        showSysMsg("Bruger slettet")
        gavevalg.init()
    },
    downloadExcel:function(){

         window.open("../gavefabrikken_backend/index.php?rt=report/userReport&shop_id="+_editShopID);
    },
    downloadExcel2:function(){

         window.open("../gavefabrikken_backend/index.php?rt=report/manglerGaveValgRapport&shop_id="+_editShopID);
    },



    sendMail:function()
    {
         if (confirm("Er du sikker på du vil sende mails ud!") == true) {
            ajax({"shop_id":_editShopID},"external/sendMailsToUsersWithNoOrders2","gavevalg.sendMailResponse","");
        }

        //  ajax({"shop_id":_editShopID},"shop/sendMAil","","");
    },
    sendMailResponse:function(response){
       showSysMsg("Mails sendt")
    },

    showCreateNew:function()
    {
       $( "#gavevalgDialog" ).dialog({
            resizable: true,
            width:700,
            height:400,
            buttons: {
                    "Gem": function() {
                        gavevalg.doCreateUser();
                        $( this ).dialog( "close" );
                    },
                    Cancel: function() {
                        $( this ).dialog( "close" );
                    }
            }
        });
    },
    doCreateUser:function()
    {
                    var attributes = [];
                    $( "#gavevalgDialog :input").each(function( index, ele ) {
                        attributes.push({"id":$(ele).attr("data-id"),"value":$(ele).val()})
                    });
                       console.log(attributes);

                      var formdata ={
                        "attributes_":JSON.stringify(attributes),
                        "data":JSON.stringify({"userId":null,"shopId":_editShopID,"companyId":this.companyId})
                    }
                    ajax(formdata,"shop/addShopUser","gavevalg.doCreateUserResponse","");
    },
    doCreateUserResponse:function(response){
        showSysMsg("Ny bruger oprettet")
       gavevalg.init()
    },
    printReceipt:function(id){
         ajax({"user_id":id},"order/resendOrderMail","gavevalg.printReceiptResponse","");
    },
    printReceiptResponse:function(response){
        showSysMsg("Kvittering sendt")
    },
    changeGiftShowMenu:function(id){
        this.changeGiftUserId = id;
    $( "#gavevalgSkiftGave" ).dialog({
        modal: true,

        width:800,
        buttons: {
        LUK: function() {
          $( this ).dialog( "close" );
        }
      }
    });
    },
    doChangeGift:function(presentsId,modelName,model,modelId){
        $( "#gavevalgSkiftGave" ).dialog( "close" );
        var formdata = {
            "shopId":_editShopID,
            "userId":this.changeGiftUserId,
            "presentsId":presentsId,
            "modelName":modelName,
            "modelId":modelId,
            "model":model


        }
        ajax(formdata,"order/changePresent","gavevalg.doChangeGiftResponse","");
    },
    doChangeGiftResponse:function(response)
    {
        if(response.status == "1"){
              showSysMsg("Ny gave valgt")
            gavevalg.init()
        } else {
            alert("Der er opstået en fejl! ")
        }
    },

    // new reg deliver
    newInitRegDelive:function(userId){
       if (confirm("Er du sikker paa du udlevere denne gave?") == true) {
            ajax({"user_id":userId},"order/getOrderNO","gavevalg.newDoRegDelive","");
            this.tempUserId = userId;
        }
    },
    newDoRegDelive:function(response){
        var orderID = response.data.orderno;
        ajax({"orderno":orderID},"registrer/doregister2","gavevalg.newDoRegDeliveResponse","");
    },
    newDoRegDeliveResponse:function(response){
        var html = "<img width=\"25\" height=\"25\" src=\"views/media/icon/delived.png\" id=\"delived_"+this.tempUserId+"\" title=\"Gave er udleveret\" onclick=\"gavevalg.newInitUnRegDelive('"+this.tempUserId+"') \" />";
        $("#delived_"+this.tempUserId).parent().html(html);

    },
    // new reg UNDO-deliver
    newInitUnRegDelive:function(userId){
       if (confirm("Vil du annullere  udleveringen") == true) {
            this.tempUserId = userId;
            ajax({"user_id":userId},"order/getOrderNO","gavevalg.newUndoRegDelive","");
        }
    },
    newUndoRegDelive:function(response){
        var orderID = response.data.orderno;
        ajax({"orderno":orderID},"registrer/dounregister2","gavevalg.newUndoRegDeliveResponse","");
    },
    newUndoRegDeliveResponse:function(response){
        var html = "<img width=\"25\" height=\"25\" src=\"views/media/icon/notdelived.png\" id=\"delived_"+this.tempUserId+"\" title=\"Gave ikke udleveret\" onclick=\"gavevalg.newInitRegDelive('"+this.tempUserId+"') \" />";

        $("#delived_"+this.tempUserId).parent().html(html);
    },

    qrRegDelive:function(userId){
         $("#delived_"+userId).attr("src","views/media/icon/delived.png");
        ajax({"user_id":userId},"order/RegisterOrder","gavevalg.qrRegDeliveResponse","");
    },
    qrRegDeliveResponse:function(response){
              showSysMsg("Gave registeret")
            notdelived
    },
    qrSendMail:function(id){
        if (confirm("Er du sikker paa, du vil sende mails ud! \ntil dem der ikke har hentet deres gave") == true) {
              showSysMsg("Mail sendt")
            ajax({"shop_id":_editShopID},"shop/sendMailsToUsersHowHasNotPickedUpPresents","","");
        }


    },
    qrExcel:function(id){
        window.location = "../gavefabrikken_backend/index.php?rt=report/manglerGaveAfhentningRapport&shop_id="+_editShopID
    }



}

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