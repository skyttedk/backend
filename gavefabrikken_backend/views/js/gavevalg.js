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

        gotoPaperPortal:function(){
            window.location.href = "../gavefabrikken_backend/index.php?rt=paperPortal&shopId="+_editShopID+"&token="+_shop_token+"&is_gf_user"
        },

        goto:function(){
            window.location.href = "../gavefabrikken_backend/index.php?rt=kundepanel&shopId="+_editShopID+"&fromback&token=NJycUpZGVhMJvQ7Kmb88uXRgX6VMhpvUcEBPj9NhmJ2tjxQB&is_gf_user"

        },
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
        this.presentsHtml = "";
        for(var i=0;response.data.length >i;i++){
            var modelJson = $.parseJSON(response.data[i].variant_list)

            if(modelJson.length > 0){
                gaveId = response.data[i].id;
                gaveNavn =  response.data[i].name;
                tempHtml = "";
                $.each(modelJson, function(i, item) {
                    if(item.language_id == "1"){
                        tempHtml+="<option value='"+gaveId+"'>"+gaveNavn+" - "+item.feltData[0].variant+" - "+item.feltData[1].variantSub+" </option>";
                     }
                })

            } else {
                this.presentsHtml+="<option value='"+response.data[i].id+"'>"+response.data[i].name+" </option>";
            }
            this.presentsHtml+=tempHtml;
        }


        this.loadFieldsDeff();
    },
    loadFieldsDeff:function(){
        console.log(_editShopID)
        ajax({"id":_editShopID},"shop/getShopAttributes","gavevalg.loadUserData","");
    },
    loadUserData:function(response){


        this.fieldsDeffData = response.data.attributes;
        this.companyId = response.data.company_id;
        ajax({"id":_editShopID},"shop/getUsers","gavevalg.buildDB","");
    },
    buildDB:function(response){
        this.userData = response.data.users

        for(var j=0;this.fieldsDeffData.length > j;j++)
        {
            this.fieldsDeffDB.push({"id":this.fieldsDeffData[j].id,"name":this.fieldsDeffData[j].name});
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
                this.userOrderDataDB["gift_"+this.userData[j].id]  = {"id":this.userData[j].orders[0].present_id,"model":this.userData[j].orders[0].present_model_name,"name":this.userData[j].orders[0].present_name};
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
        //buildPie(data)
        this.buildTableHtml();


    },  
    buildTableHtml:function()
    {
        var ingenValgte = [];



        var dataOrder = [];
        var html = "";
        dialogHtml = "<br /><table width=500>" ;
        html = "<table class='gavevalg'><tr>";

        for(var i=0;this.fieldsDeffDB.length >i;i++){
        // console.log(this.fieldsDeffDB[i])
            html+="<th data-id='"+this.fieldsDeffDB[i].id+"'>"+this.fieldsDeffDB[i].name+"</th>";
            dataOrder.push(this.fieldsDeffDB[i].id);
            dialogHtml+="<tr><td>"+this.fieldsDeffDB[i].name+"</td><td><input data-id='"+this.fieldsDeffDB[i].id+"' type=\"text\" /></td>";
        }
        html+="<td></td><td><img width=\"20\" height=\"20\" src=\"views/media/icon/excel.png\" title=\"Download alle informationer1\" onclick=\"gavevalg.downloadExcel() \"><img width=\"20\" height=\"20\" src=\"views/media/icon/1373253494_plus_64.png\" title=\"Opret ny bruger\" onclick=\"gavevalg.showCreateNew() \"><img width=\"20\" title=\"Send en mail til dem som ikke har valgt gave\" height=\"20\" src=\"views/media/icon/1373253286_letter_64.png \" onclick=\"gavevalg.sendMail() \"></td></tr>";
        dialogHtml+="</table>";
        $( "#gavevalgDialog" ).html(dialogHtml);

        for(var i=0;this.userDataDB.length >i;i++){
            html+="<tr id=\"gavevalgrow_"+this.userDataDB[i].id+"\">";
            for(var j=0;dataOrder.length>j;j++){
               // html+="<td>"+this.userDataDB[i].data["id_"+dataOrder[j]]+"</td>";
               html+="<td><input field-id='"+dataOrder[j]+"' type=\"text\" value="+this.userDataDB[i].data["id_"+dataOrder[j]]+" /></td>";

            }
            if(this.userOrderDataDB["gift_"+this.userDataDB[i].id].id == ""){
                var selectedGift = "Ikke valgt gave"
            }  else {
                var selectedGift = this.userOrderDataDB["gift_"+this.userDataDB[i].id].name +" - "+ this.userOrderDataDB["gift_"+this.userDataDB[i].id].model
            }



            //html+="<td><select ><option value='0' style=\"color:blue;\">"+selectedGift+" </option>"+this.presentsHtml+"</select></td>";
            html+="<td> <input type=\"text\" name=\"usrname\" maxlength=\"20\" value=\""+selectedGift+"\"></td>";
            html+="<td><img width=\"20\" height=\"20\" src=\"views/media/icon/1373253284_save_64.png\" title=\"Gem\" onclick=\"gavevalg.updateUserData('"+this.userDataDB[i].id+"') \">";
            if(this.userOrderDataDB["gift_"+this.userDataDB[i].id].id == ""){
                html+="<img width=\"20\" height=\"20\" src=\"views/media/icon/blank.png\"  ) \">";
                html+="<img width=\"20\" height=\"20\" src=\"views/media/icon/PurchaseNoOrder-50.png\" title=\"Ingen kvittering, ej valgt gave\" ) \">";
            } else {
                html+="<img width=\"20\" height=\"20\" src=\"views/media/icon/gift.png\" title=\"Skift gave\"  onclick=\"gavevalg.changeGift() \">";
                html+="<img width=\"20\" height=\"20\" src=\"views/media/icon/Purchase Order-50.png\" title=\"Send kvittering\"  onclick=\"gavevalg.printReceipt('"+this.userDataDB[i].id+"') \">";
            }

            html+="<img width=\"20\" height=\"20\" src=\"views/media/icon/1373253296_delete_64.png\" title=\"Slet\" onclick=\"gavevalg.deleteUser('"+this.userDataDB[i].id+"') \">" ;
            html+="<span data-id=\""+this.userDataDB[i].id+"\" class=\"complaintBtn\" title=\"Reklamation\" style=\"cursor:pointer;margin-left:5px;font-size:16px;color:#dc3545;font-weight:bold;\">⚠</span>";
            html+="</td></tr>";






        }


        html+="</tr></table>";
        $("#gavevalgContainer").html(html);
        
        // Add complaint button event handlers
        $(".complaintBtn").unbind("click").click(function(){
            gavevalg.openComplaint($(this).attr("data-id"));
        });
        
        // Load existing complaint indicators
        gavevalg.loadComplaintIndicators();

    },
    updateUserData:function(id)
    {
         var attributes = []
          $( "#gavevalgrow_"+id+" :input").each(function( index, ele ) {
            if($(ele).get(0).tagName == "INPUT"){
                attributes.push({"attribute_id":$(ele).attr("field-id"),"attribute_value":$(ele).val()})
            }


               //console.log(this.attr("field-id"))
         })


         var formData = {
             "attributes":JSON.stringify(attributes),
             "shop_id":_editShopID,
             "user_id":id}

        ajax(formData,"shop/updateShopUser","gavevalg.updateUserDataResponse","");
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
    sendMail:function()
    {
         if (confirm("Er du sikker p� du vil sende mails ud!") == true) {
            ajax({"shop_id":_editShopID},"shop/getUsersWithNoOrders","gavevalg.sendMailResponse","");
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
         ajax({"user_id":id},"mail/resendOrderMail","gavevalg.printReceiptResponse","");
    },
    printReceiptResponse:function(response){
        showSysMsg("Kvittering sendt")
    },
    openComplaint:function(shopuserID){
        // Import complaint class dynamically and open dialog
        import('../gavefabrikken_backend/units/valgshop/main/js/complaint.class.js').then(module => {
            const Complaint = module.default;
            new Complaint(_editShopID, shopuserID);
        }).catch(error => {
            console.error('Error loading complaint module:', error);
            alert('Kunne ikke indlæse reklamationssystem');
        });
    },
    loadComplaintIndicators:function(){
        // Load existing complaints and mark buttons red
        $.post("index.php?rt=cardshop/cards/getComplaintList/"+_editShopID, {}, function(response) {
            gavevalg.markComplaintButtons(response);
        });
    },
    markComplaintButtons:function(response){
        if(response.status === 1 && response.data){
            response.data.forEach(function(item) {
                $('.complaintBtn[data-id="' + item.shopuser_id + '"]').css('color', 'red');
            });
        }
    }





}