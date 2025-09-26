

var sm = {
    tabledata:[],
    updateCounter:0,
    numberOfItemToUpdate:0,
    tabledata:[],
    falloverPresent:[],
    load:function()
    {
        ajax({shop_id:_editShopID},"shop/getPresentStatsShop","sm.loadResponse");
    },
    calcReservation:function(){
        ajax({shop_id:_editShopID},"shop/getShopPresentsNew","sm.calcReservationResponse");
    },
    calcReservationResponse: async function (res){
      $("#storage-to-many").html("");
        let stop = false;
        res.data.forEach(function(present) {

            if(present.attributes.strength*1 == 0){
                stop = true;
            }
        });
      if(stop == true){
          alert("Ikke alle styrker er sat, der kan ikke udføres et foreslag!")
          return;
      }



      let calcRes =  await sm.doCalcReservation(res);
      let sum = 0;
        res.data.forEach(function(present) {
            calcRes[present.attributes.strength-1]
            let antal = sm.roundToNearest5(calcRes[present.attributes.strength-1]);
            //let antal = calcRes[present.attributes.strength-1];
            sum+=antal
            if($("#model_"+present.attributes.model_id).val() == 0 || $("#model_"+present.attributes.model_id).val() == ""  ){
                $("#model_"+present.attributes.model_id).val(antal)
                $("#model_"+present.attributes.model_id).parent().parent().find( ".needToUpdata" ).show();
            } else {
                $("#suggestions_"+present.attributes.model_id).html(antal)
            }

        });
        let sold = $("#storage-present-sold").val();

        if( Math.round(sold*1.35) < Math.round(sum*1)  ){
            let tooMany = Math.round( Math.round(sum) - Math.round(sold*1.35));
            $("#storage-to-many").html("For mange er blevet reserveret, du må manuelt nedskrive med "+tooMany+" hvis du benytter dette forslag");
        }
        ajax({shop_id:_editShopID,amount:sold},"shop/setSold");

    },
    doCalcReservation: async function(res){
        return new Promise((resolve, reject) => {
            let  styrker = [];
            res.data.forEach(function(present) {
                styrker.push(present.attributes.strength);
            });
            let calcFre = sm.calculateFrequency(styrker)


            let sold = $("#storage-present-sold").val();
            if(sold == "" && sold==0){
                return;
            }
            let maxSold = Math.round(sold*1.3);
            $("#storage-max-res").val(maxSold);

            let co = sm.calculateOrder(calcFre,100);
            let resStrength1 = Math.round((maxSold * (co[0]/100 ) )/ calcFre[1] );
            let resStrength2 = Math.round((maxSold * (co[1]/100 ) )/ calcFre[2] );
            let resStrength3 = Math.round((maxSold * (co[2]/100 ) )/ calcFre[3] );
            resolve ([resStrength1, resStrength2, resStrength3]);
        });
    },
    calculateOrder:function(strength,presentSold){
        let quantity1 = strength[1];
        let quantity2 = strength[2];
        let quantity3 = strength[3];

        let sold = presentSold;
        let strength1 = 1; // Base strength
        let strength2 = 2; // Strength 3 is twice as large as strength 2
        let strength3 = 4; // Strength 3 is 4 times as large as strength 1

        // Calculate the total 'strength units'
        let totalStrengthUnits = strength1 * quantity1 + strength2 * quantity2 + strength3 * quantity3;

        // Calculate the proportion of each item to the total
        let proportion1 = (strength1 * quantity1) / totalStrengthUnits;
        let proportion2 = (strength2 * quantity2) / totalStrengthUnits;
        let proportion3 = (strength3 * quantity3) / totalStrengthUnits;

        // Now distribute the 100 items according to these proportions
        let orderStrength1 = Math.round(100 * proportion1);
        let orderStrength2 = Math.round(100 * proportion2);
        let orderStrength3 = Math.round(100 * proportion3);

        // Adjust if the total is not exactly 100 due to rounding
        let totalOrder = orderStrength1 + orderStrength2 + orderStrength3;
        if (totalOrder > presentSold) {
            let excess = totalOrder - presentSold;
            if (orderStrength3 >= excess) {
                orderStrength3 -= excess;
            } else if (orderStrength2 >= excess) {
                orderStrength2 -= excess;
            } else {
                orderStrength1 -= excess;
            }
        } else if (totalOrder < presentSold) {
            let shortfall = presentSold - totalOrder;
            if (orderStrength3 + shortfall <= presentSold) {
                orderStrength3 += shortfall;
            } else if (orderStrength2 + shortfall <= presentSold) {
                orderStrength2 += shortfall;
            } else {
                orderStrength1 += shortfall;
            }
        }
        return [orderStrength1, orderStrength2, orderStrength3];
    },
    calculateFrequency:function (array){
        let frequency = {1:0,2:0,3:0};
        for (let i = 0; i < array.length; i++) {
            let num = array[i];
            if (frequency.hasOwnProperty(num)) {
                frequency[num]++;
            } else {
                frequency[num] = 1;
            }
        }
        return frequency;
    },
    roundToNearest5:function (number) {
        return Math.ceil(number / 5) * 5;
    },
    loadResponse:function(response){
        sm.tabledata = [];
        var is_active = 1;
        var problem = response.data.problem;
        var emailToMotify = response.data.email;
        console.log(problem)
        var emailToMotifyHtml = "";
        var problemHtml = "";
        if(problem != ""){  problemHtml = problem.join();  }
        if(emailToMotify == null ) { emailToMotify = ""; }

        emailToMotifyHtml = "<label><b>Notifikation email:</b> </label><input id=\"notifikation\" value='"+emailToMotify+"' type=\"text\" size=\"40\" />"


        var html_active="<tr><td colspan=2>"+problemHtml+"</td><td colspan=5 style=\"text-align:center;\">"+emailToMotifyHtml+"</td><td colspan=4 ><button style=\"background-color: #f44336; color:white;\" type=\"button\" onclick=\"sm.initUpdate()\">Opdatere alle felter</button></td></tr><tr><th width=70>Gave</th>	<th width=70>Model</th>	<th width=50>Model kode</th>	<th width=20>Antal valgte</th>	<th width=20>Antal reserveret</th>	<th width=20>Advarsel</th>	<th width=20></th>	<th width=90>Erstatningsgave</th>	<th width=20>Luk</th><th width=20></th><th width=40>Autopilot</th></tr><tr><td valign=center colspan=11 style=\" text-align: center;background-color: #FFFF00;\">AKTIVE GAVER </td></tr>";
        var html_not_active = "";
        var html = "";

        response.data =  response.data.data;
        for (var key in response.data) {
            is_active = 1;
            html = ""
            // tjekker om gaven er lukket eller �ben
            if(response.data[key].present_is_active == "1" || response.data[key].present_is_deletet == "1" ||  response.data[key].present_total_is_active == "0" ){
              is_active = 2;

            }
            if(response.data[key].present_total_is_deletet == "1"){
                is_active = 3;
            }


            var showFalloverIcon;
            response.data[key].reserved_quantity > 0 ? showFalloverIcon = 1 : showFalloverIcon = false;
            if(response.data[key].model_present_no == undefined) { response.data[key].model_present_no = ""; }
            if(response.data[key].model_present_name == undefined) { response.data[key].model_present_name = ""; }

                var data = {present_id:response.data[key].present_id, present_model_id:response.data[key].present_model_id, reservation_id:response.data[key].reservation_id,present_properties:response.data[key].present_properties,present_name:response.data[key].present_name,model_present_name:response.data[key].model_present_name }
                sm.tabledata.push(data)
            var smActiveClass;
            is_active == 1 ? smActiveClass = "storageMonitoringData" : smActiveClass = "smNone";
            html+="<tr  class='storageMonitoringData' data='"+key+"' id='rowid_"+key+"'><td >"+response.data[key].present_name+"</td><td class=\"resName\">"+response.data[key].model_present_name+"</td><td>"+response.data[key].model_present_no+"</td><td class=\"smOrder\">"+response.data[key].order_count+"</td>";
            html+="<td><input style=\"width:50px;\" id='model_"+response.data[key].present_model_id+"'  class=\"smQuantity\" onchange=\"sm.showUpdateBtn(this)\" value='"+response.data[key].reserved_quantity+"' /><br><div style='color: red;' id='suggestions_"+response.data[key].present_model_id+"'></div></td>";
            html+="<td><input style=\"width:50px;\" class=\"smWarning\" onchange=\"sm.showUpdateBtn(this)\" value='"+response.data[key].warning_level+"' /></td>";



/*
            html+="<td><select class=\"smWarning\"  onchange=\"sm.showUpdateBtn(this)\">";
            if(response.data[key].warning_level == ""){
              response.data[key].warning_level = 80;
            }

            var i = 10;
            while(i<=100){
                if(response.data[key].warning_level == i){
                    html+="<option selected value='"+i+"'>"+i+"%</option>"
                } else {
                    html+="<option value='"+i+"'>"+i+"%</option>"
                }
                i+=10;
            }
            html+="</select></td>";
*/
            var isCheck = "";
            if(response.data[key].do_close == 1){
                isCheck = "checked";
            }
            let hasAutopilot = response.data[key].autotopilot == 1 ? "checked":"";

            if(is_active == 1){

                if(showFalloverIcon == true){
                    html+="<td><div  class=\"needToUpdata\" style=\"color:red; display:none; font-size:16px;\"><b>!</b></div></td><td>"+response.data[key].replacement_present_name+"</td><td><input class=\"luk\" "+isCheck+" type=\"checkbox\" onchange=\"sm.showUpdateBtn(this)\" /></td><td><img style=\"cursor:pointer;\" width=\"23\" height=\"23\" title=\"fallover gave\" src=\"views/media/icon/gave.png\" onclick=\"sm.showFalloverModal('"+data.present_id+"','"+data.present_model_id+"','"+response.data[key].reservation_id+"')\"></td><td><input class=\"autopilot\" "+hasAutopilot+" type=\"checkbox\" onchange=\"sm.showUpdateBtn(this)\" /></td></tr>";
                } else {
                    html+="<td><div  class=\"needToUpdata\" style=\"color:red; display:none; font-size:16px;\"><b>!</b></div></td><td>"+response.data[key].replacement_present_name+"</td><td><input class=\"luk\" "+isCheck+" type=\"checkbox\" onchange=\"sm.showUpdateBtn(this)\" /></td><td><img  width=\"23\" height=\"23\" title=\"fallover gave\" src=\"views/media/icon/1373253314_present_64.png\" /></td><td><input class=\"autopilot\" "+hasAutopilot+" type=\"checkbox\" onchange=\"sm.showUpdateBtn(this)\" /></td></tr>";
                }
                html_active+= html;
            }
            if(is_active == 2) {
                 // sm.tabledata.pop()
                    html+="<td></td><td></td><td></td><td></td><td></td></tr>";
                html_not_active+=html;
            }

        }
        $("#storageMonitoring").html(  html_active+"<tr><td valign=center colspan=11 style=\" text-align: center;background-color: #FFFF00;\"><span style=\"color:red;\">IKKE</span> AKTIVE GAVER </td></tr>"+html_not_active);
        sm.updateWarning();
        system.endWork();
        if($("#model_"+present.attributes.model_id).val() == 0 || $("#model_"+present.attributes.model_id).val() == ""  ){
            ajax({shop_id:_editShopID},"shop/getSold","sm.getSoldRes");
        }

    },
    getSoldRes:function(res){

        $("#storage-present-sold").val(res.data[0].sold);
    },
    showUpdateBtn:function(obj){
        $(obj).parent().parent().find( ".needToUpdata" ).show();
    },
    initUpdate:function(){
        system.work();


        sm.updateCounter = 0;
        sm.numberOfItemToUpdate =  $( ".storageMonitoringData" ).length;
         console.log(sm.numberOfItemToUpdate)
        sm.updateQueueController();
    },
    updateQueueController:function(){
        if(sm.numberOfItemToUpdate >  sm.updateCounter){
            var itemNumber = $( ".storageMonitoringData" ).eq( sm.updateCounter ).attr("data") ;
            var do_close = "";
            let autotopilot = "";
            var rowData =  sm.tabledata[itemNumber];
            var rowObj  =  $("#rowid_"+itemNumber);

            var quantity =      rowObj.find(".smQuantity").val();
           // console.log(quantity);
            var warning_level = rowObj.find(".smWarning").val();
            var do_closeObj =  rowObj.find(".luk")
            var do_autopilotObj =  rowObj.find(".autopilot")
            console.log(rowObj)
            $(do_closeObj).is(':checked') ? do_close = "1" : do_close = "0";
            $(do_autopilotObj).is(':checked') ? autotopilot = "1" : autotopilot = "0";
            // if ingen �ndringer s� spring over
            console.log(rowData)
            if($(rowObj).find(".needToUpdata").css('display') !== 'none') {
                sm.update(quantity,warning_level,rowData.present_id,rowData.present_model_id,rowData.reservation_id,do_close,autotopilot)

            } else {
                sm.updateResponse();
            }
        } else {
            var formdata = {
                "id":_editShopID,
                "rapport_email":$("#notifikation").val()
            }
            ajax(formdata,"shop/updateEmailNotification","sm.updateEmailNotificationResponse");
        }
    },
    updateEmailNotificationResponse:function(){
       sm.load();
    },
    update:function(quantity,warning_level,present_id,present_model_id,reservation_id,do_close,autotopilot){
        var formdata = {
           "shop_id":_editShopID,
           "present_id" : present_id,
           "model_id" : present_model_id,
           "warning_level" : warning_level,
           "quantity" : quantity,
           "do_close" : do_close,
            "autotopilot":autotopilot
        }
        if(reservation_id == ""){
            ajax(formdata,"reservation/saveReservation","sm.updateResponse");
        } else {
            formdata.id = reservation_id;
            ajax(formdata,"reservation/saveReservation","sm.updateResponse");
        }
    },
    updateResponse:function(){
        sm.updateCounter++;
        sm.updateQueueController();
    },
    updateWarning:function()
    {
        $( "#storageMonitoring tr" ).each(function( index ) {
            var reserved =  $( this ).find(".smQuantity").val();
            var warningLevel = $( this ).find(".smWarning").val();
            var orderCount = $( this ).find(".smOrder").html();
            var warningColorRed = warningLevel / 100;
            $(this).css("background-color", "white");

            if(parseInt(reserved) != 0){
               if( (parseInt(warningLevel) + parseInt(orderCount) ) > parseInt(reserved)   ){
                      $(this).css("background-color", "#f4a688");
               }
            }
        });
    },
    chekIfActive:function(target_present_id, target_present_model_id){


    },
    showFalloverModal:function(target_present_id, target_present_model_id,id) {
        var html = "<table width=100% border=1><tr><th>Gavenavn</th><th></th></tr>";
        var doShow = true;
        var presentIsInList = [];
        html+="<tr><td>Nulstil erstatningsgave</td><td><input type=\"button\"  value=\"Benyt\"  onclick=\"sm.regFalloverPresent('"+target_present_id+"','"+target_present_model_id+"','0','','"+id+"')\"></td><td>Autopilot</td></tr>";
        for (var key in sm.tabledata) {
            doShow = true;
            if(sm.tabledata[key].present_properties == null){
                doShow = false;
            } else {
                var option = sm.tabledata[key].present_properties;
                option =  jQuery.parseJSON(option);
                var variantList = option.variantListOption;
                var variantListOptionArr = variantList.toString().split(",");
                if(option.aktivOption == true){  //&& variantListOptionArr.indexOf(sm.tabledata[key].present_model_id.toString()) != -1){
                    doShow = false;
                }
            }

            if(doShow == true && presentIsInList.indexOf(sm.tabledata[key].present_id.toString()) == -1){
               html+="<tr><td>"+sm.tabledata[key].present_name+"</td><td><input type=\"button\"  value=\"Benyt\"  onclick=\"sm.regFalloverPresent('"+target_present_id+"','"+target_present_model_id+"','"+sm.tabledata[key].present_id+"','"+sm.tabledata[key].present_name+"','"+id+"')\"></td></tr>";
            }
            presentIsInList.push(sm.tabledata[key].present_id.toString());
        }
        html+= "</table>";
        showModal(html, "Erstatningsgave",500,400)
   },
   regFalloverPresent:function(target_present_id,target_present_model_id,present_id,replacement_present_name,id){
        var formdata = {
           "id":id,
           "shop_id":_editShopID,
           "present_id" : target_present_id,
           "model_id" : target_present_model_id,
           "replacement_present_id" : present_id,
           "replacement_present_name" : replacement_present_name
        }

        ajax(formdata,"reservation/saveReservation","sm.regFalloverPresentResponse");
   },
   regFalloverPresentResponse:function(response){
        if(response.status == "1"){
            closeMedal();
            sm.load();
        }

   }


}