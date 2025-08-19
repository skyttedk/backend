var lager2 = {
    temp:"",
    activeTable:"",
    selectedTab:"",
    selectedOrder:"",
    init:function(){
        $( "#tabs" ).tabs();
        lager2.getNotReleased();
    },
    changeCard:function(){
        $("#countDeleved").html("0");
        cardId = $( "#card :selected").val();
        selected = $("#tabs .ui-tabs-panel:visible").attr("id");
        if(selected == "tabs-1"){ lager2.getNotReleased('1')  }
        if(selected == "tabs-2"){ lager2.getWaitingOrder('2') }
        if(selected == "tabs-3"){ lager2.getPrintetedOrder('3') }
        if(selected == "tabs-4"){ lager2.getIsShipOrder('4') }
        if(selected == "tabs-5"){ lager2.getDeletedOrder('5') }
    },
    getDeletedOrder:function(){
            $(".tabsData").html("");
            cardId = $( "#card :selected").val();
            ajax({"cardId":cardId},"lager2/getDeletedOrder","lager2.getDeletedOrderResponse","");

    },
    getDeletedOrderResponse:function(response){
            var  actionHtml = "";
        var html = " <thead><tr><th>Order nummer</th><th>Slettet</th><th>Firmanavn</th><th>CVR</th><th>EAN</th><th>Udløbsdato</th><th>Antal_kort</th><th>Kortværdi</th><th>Kort_start</th><th>Kort_slut</th><th>Special noter</th><th></th></tr> </thead>";
   var totalCard = 0;
        var obj = response.data.result;
        lager2.activeTable = "#isDeletedOrder";
        if(obj.length == 0){
            $("#actionBar").html("");
            html = "<td>Tom Søgning</td>";
           $("#isDeletedOrder").html(html);
         } else {
         html+="<tbody>";
        for (var key in obj) {
                          totalCard+= (obj[key].certificate_no_end*1) - (obj[key].certificate_no_begin*1);
               totalCard+=1;
            action = "<button onclick=\"lager2.deleteOrder('"+obj[key].id+"','#isDeletedOrder')\">Slet ordre</button>";
             var is_cancelled = "";
            if(obj[key].is_cancelled == 1){
                action = "<button onclick=\"lager2.restoreOrder('"+obj[key].id+"','#isDeletedOrder')\">fortryd slet</button>";
                is_cancelled = "<span class=\"is_cancelled\">SLETTET</span>"
            }
               html+="<tr id=\""+obj[key].id+"\"><td>"+obj[key].order_no+"</td><td>"+is_cancelled+"</td><td>"+obj[key].company_name+"</td><td>"+obj[key].cvr+"</td><td>"+obj[key].ean+"</td><td>"+lager2.correctData(obj[key].expire_date)+"</td><td>"+obj[key].quantity+"</td><td>"+obj[key].certificate_value+"</td><td>"+obj[key].certificate_no_begin+"</td><td>"+obj[key].certificate_no_end+"</td><td>"+obj[key].spdealtxt+"</td><td>"+action+"</td></tr>";
        }
        html+="</tbody>";
           $("#countDeletedOrder").html(totalCard);
        $("#isDeletedOrder").html(html);
        lager2.initTable('#isDeletedOrder');
        }
    },
    getNotReleased:function(){
            $(".tabsData").html("");
            cardId = $( "#card :selected").val();
            ajax({"cardId":cardId},"lager2/getNotReleased","lager2.getNotReleasedResponse","");
    },



    getNotReleasedResponse:function(response){
        var  actionHtml = "";
        var html = " <thead><tr><th>Order nummer</th><th>Slettet</th><th>Firmanavn</th><th>CVR</th><th>EAN</th><th>Udløbsdato</th><th>Antal_kort</th><th>Kortværdi</th><th>Kort_start</th><th>Kort_slut</th><th>Special noter</th><th></th></tr> </thead>";
        var obj = response.data.result;
        lager2.activeTable = "#notReleased";
        if(obj.length == 0){
            $("#actionBar").html("");
            html = "<td>Tom Søgning</td>";
           $("#notReleased").html(html);
         } else {
         html+="<tbody>";
        for (var key in obj) {
            action = "<button onclick=\"lager2.deleteOrder('"+obj[key].id+"','#notReleased')\">Slet ordre</button>";
             var is_cancelled = "";
            if(obj[key].is_cancelled == 1){
                action = "<button onclick=\"lager2.restoreOrder('"+obj[key].id+"','#notReleased')\">fortryd slet</button>";
                is_cancelled = "<span class=\"is_cancelled\">SLETTET</span>"
            }
               html+="<tr id=\""+obj[key].id+"\"><td>"+obj[key].order_no+"</td><td>"+is_cancelled+"</td><td>"+obj[key].company_name+"</td><td>"+obj[key].cvr+"</td><td>"+obj[key].ean+"</td><td>"+lager2.correctData(obj[key].expire_date)+"</td><td>"+obj[key].quantity+"</td><td>"+obj[key].certificate_value+"</td><td>"+obj[key].certificate_no_begin+"</td><td>"+obj[key].certificate_no_end+"</td><td>"+obj[key].spdealtxt+"</td><td>"+action+"</td></tr>";
        }
        html+="</tbody>";
        $("#notReleased").html(html);
        lager2.initTable('#notReleased');
        }
    },
        getWaitingOrder:function(){
              $(".tabsData").html("");
           cardId = $( "#card :selected").val();
           ajax({"cardId":cardId},"lager2/getWaitingOrder","lager2.getWaitingOrderResponse","");
    },
    getWaitingOrderResponse:function(response){
        var  actionHtml = "<button style=\"margin-right:20px;\" onclick=\"lager2.doPrint()\">PRINT VALGTE</button><button onclick=\"lager2.doPrintAll()\">PRINT ALLE</button>";

        var html = " <thead><tr><th>Order nummer</th><th>Slettet</th><th>Firmanavn</th><th>CVR</th><th>EAN</th><th>Udløbsdato</th><th>Antal_kort</th><th>Kortværdi</th><th>Kort_start</th><th>Kort_slut</th><th>Special noter</th><th></th></tr> </thead>";
        var obj = response.data.result;
        var action = "";
         var totalCard = 0;
        lager2.activeTable = "#WaitingOrder";
        if(obj.length == 0){
              $("#actionBar").html("");
             html = "<td>Tom Søgning</td>";
             $("#WaitingOrder").html(html);
        } else {
             html+="<tbody>";
            for (var key in obj) {
                totalCard+= (obj[key].certificate_no_end*1) - (obj[key].certificate_no_begin*1);
               totalCard+=1;
             action = "<button onclick=\"lager2.deleteOrder('"+obj[key].id+"','#WaitingOrder')\">Slet ordre</button>";
             var is_cancelled = "";
               if(obj[key].is_cancelled == 1){
                action = "<button onclick=\"lager2.restoreOrder('"+obj[key].id+"','#WaitingOrder')\">fortryd slet</button>";
                is_cancelled = "<span class=\"is_cancelled\">SLETTET</span>"
               }
               html+="<tr id=\""+obj[key].id+"\"><td>"+obj[key].order_no+"</td><td>"+is_cancelled+"</td><td>"+obj[key].company_name+"</td><td>"+obj[key].cvr+"</td><td>"+obj[key].ean+"</td><td>"+lager2.correctData(obj[key].expire_date)+"</td><td>"+obj[key].quantity+"</td><td>"+obj[key].certificate_value+"</td><td>"+obj[key].certificate_no_begin+"</td><td>"+obj[key].certificate_no_end+"</td><td>"+obj[key].spdealtxt+"</td><td><input style=\"margin-right:20px;\" type=\"checkbox\" class=\"print\" name=\"print\" value=\""+obj[key].id+"\">"+action+" </td></tr>";
            }
            html+="</tbody>";
            $("#actionBar").html(actionHtml);
             $("#countWaiting").html(totalCard);
            $("#WaitingOrder").html(html);
            lager2.initTable('#WaitingOrder');
        }
    },

    deleteOrder:function(id,tab){
        lager2.temp = tab;
        ajax({"orderId":id},"lager2/deleteOrder","lager2.deleteOrderResponse","");
    },
    deleteOrderResponse:function(){
        if(lager2.temp == "#notReleased"){
            lager2.getNotReleased();
        }
        if(lager2.temp == "#isDeletedOrder"){
            lager2.getDeletedOrder();
        }
        if(lager2.temp == "#WaitingOrder"){
            lager2.getWaitingOrder();
        }
    },
    restoreOrder:function(id,tab){
            ajax({"orderId":id},"lager2/restoreOrder","lager2.restoreOrderResponse","");
            lager2.temp = tab;
    },
    restoreOrderResponse:function(response){
        if(lager2.temp == "#notReleased"){
            lager2.getNotReleased();
        }
        if(lager2.temp == "#isDeletedOrder"){
            lager2.getDeletedOrder();
        }
        if(lager2.temp == "#WaitingOrder"){
            lager2.getWaitingOrder();
        }

    },

    getPrintetedOrder:function(){
        $(".tabsData").html("");
        cardId = $( "#card :selected").val();
        ajax({"cardId":cardId},"lager2/getPrintetedOrder","lager2.getPrintetedOrderResponse","");
    },
    getPrintetedOrderResponse:function(response){
        var  actionHtml = "<button style=\"margin-right:20px;\" onclick=\"lager2.doPrint()\">PRINT VALGTE</button><button onclick=\"lager2.doRegSelectede()\">UDLEVERE VALGTE</button><button onclick=\"lager2.doRegAll()\">UDLEVERE ALLE</button>";
         var totalCard = 0;
        var html = " <thead><tr><th>Order nummer</th><th>Slettet</th><th>Firmanavn</th><th>CVR</th><th>EAN</th><th>Udløbsdato</th><th>Antal_kort</th><th>Kortværdi</th><th>Kort_start</th><th>Kort_slut</th><th>Special noter</th><th></th></tr> </thead>";
        var obj = response.data.result;
        var action = "";
        html+="<tbody>";
        if(obj.length == 0){
            $("#actionBar").html("");
            html = "<td>Tom Søgning</td>";
           $("#isPrintedOrder").html(html);
        } else {
        for (var key in obj) {
            var is_cancelled = "";
            totalCard+= (obj[key].certificate_no_end*1) - (obj[key].certificate_no_begin*1);
               totalCard+=1;
             if(obj[key].is_cancelled == 1){

                is_cancelled = "<span class=\"is_cancelled\">SLETTET</span>"
            }
               html+="<tr id=\""+obj[key].id+"\"><td>"+obj[key].order_no+"</td><td>"+is_cancelled+"</td><td>"+obj[key].company_name+"</td><td>"+obj[key].cvr+"</td><td>"+obj[key].ean+"</td><td>"+lager2.correctData(obj[key].expire_date)+"</td><td>"+obj[key].quantity+"</td><td>"+obj[key].certificate_value+"</td><td>"+obj[key].certificate_no_begin+"</td><td>"+obj[key].certificate_no_end+"</td><td>"+obj[key].spdealtxt+"</td><td><input type=\"checkbox\" class=\"print\" name=\"print\" value=\""+obj[key].id+"\"> </td></tr>";
        }
        html+="</tbody>";
        $("#actionBar").html(actionHtml);
          $("#countPrintedOrder").html(totalCard);
        $("#isPrintedOrder").html(html);
        lager2.initTable('#isPrintedOrder');
       }
    },
    getIsShipOrder:function(){
        $(".tabsData").html("");
        cardId = $( "#card :selected").val();
        ajax({"cardId":cardId},"lager2/getIsShipOrder","lager2.getIsShipOrderResponse","");
    },
    getIsShipOrderResponse:function(response){
        var  actionHtml = "";
        var totalCard = 0;
        var html = " <thead><tr><th>Order nummer</th><th>Firmanavn</th><th>CVR</th><th>EAN</th><th>Udløbsdato</th><th>Antal_kort</th><th>Kortværdi</th><th>Kort_start</th><th>Kort_slut</th><th>Special noter</th></tr> </thead>";
        var obj = response.data.result;
        var action = "";
        html+="<tbody>";
        if(obj.length == 0){
            $("#actionBar").html("");
            html = "<td>Tom Søgning</td>";
            $("#isShipOrder").html(html);
        } else {
        for (var key in obj) {
               totalCard+= (obj[key].certificate_no_end*1) - (obj[key].certificate_no_begin*1);
               totalCard+=1;
               html+="<tr id=\""+obj[key].id+"\"><td>"+obj[key].order_no+"</td><td>"+obj[key].company_name+"</td><td>"+obj[key].cvr+"</td><td>"+obj[key].ean+"</td><td>"+lager2.correctData(obj[key].expire_date)+"</td><td>"+obj[key].quantity+"</td><td>"+obj[key].certificate_value+"</td><td>"+obj[key].certificate_no_begin+"</td><td>"+obj[key].certificate_no_end+"</td><td>"+obj[key].spdealtxt+"</td></tr>";
        }

          html+="</tbody>";
          $("#actionBar").html(actionHtml);
          $("#isShipOrder").html(html);
          $("#countDeleved").html(totalCard);
          lager2.initTable('#isShipOrder');
        }
    },

    doRegSelectede:function(){
        var list = [];
        $( ".print" ).each(function() {
              if($(this).is(':checked') == 1){
                    list.push($( this ).val())
              }
        });
        ajax({"OrderList":list.join()},"lager2/regDelivedeOrdre","lager2.regDelivedeOrdreResponse","");

    },
    regDelivedeOrdreResponse:function(response){
         lager2.getPrintetedOrder();
    },
    doRegAll:function(){
        var list = [];
        $( "tr" ).each(function() {
              if($( this ).attr('id') != undefined){
                list.push($( this ).attr('id'))
            }
        });
         ajax({"OrderList":list.join()},"lager2/regDelivedeOrdre","lager2.regDelivedeOrdreResponse","");

    },
    doPrint:function(){
        var list = [];
        $( ".print" ).each(function() {
              if($(this).is(':checked') == 1){
                    list.push($( this ).val())
              }
        });
        var sortlist = list.sort();
        $("#doPrintList").val(sortlist.join())
        $( "#goPrint" ).submit();
        var html = "<td>Tryk på fanen for at få den opdaterede liste</td>";
         $(".tabsData").html(html);



    },
    doPrintAll:function(){
        var cardId = $( "#card :selected").val();
        $("#goPrintAllCard").val(cardId);
        $( "#goPrintAll" ).submit();
        var html = "<td>Tryk på fanen for at få den opdaterede liste</td>";
        $(".tabsData").html(html);
    },
/*
    doPrint100:function(){
        var i = 0;
        var list = [];
        $( "tr" ).each(function() {
            if(i > 100){
              return true;
            }
            if($( this ).attr('id') != undefined){
                list.push($( this ).attr('id'))
            }
            i++;
        });
        var sortlist = list.sort();
        $("#doPrintList").val(sortlist.join())
        $( "#goPrint" ).submit();
                var html = "<td>Tryk på fanen for at få den opdaterede liste</td>";
         $(".tabsData").html(html);
   },

        doPrintAll:function(){

        var list = [];
        $( "tr" ).each(function() {
              if($( this ).attr('id') != undefined){
                list.push($( this ).attr('id'))
            }

        });
        var sortlist = list.sort();
        $("#doPrintList").val(sortlist.join())
        $( "#goPrint" ).submit();
                var html = "<td>Tryk på fanen for at få den opdaterede liste</td>";
         $(".tabsData").html(html);
    },
   */
    sog:function(){
        var sogTxt = $("#sogTxt").val();
        sogTxt = sogTxt.toLowerCase();
        var first = true
        $( "tr" ).show();
        if(sogTxt != ""){
        $( "tr" ).each(function() {
            var htmlTr =  $( this ).html();
            htmlTr = htmlTr.toLowerCase();
            if(htmlTr.indexOf(sogTxt) == -1 && first == false ){
                    $( this ).hide();
            }
            first = false;
        });
      }
    },
    clearTable:function(tableId){
        var table = $(tableId).DataTable();
        table.destroy();
    },
    initTable:function(tableId){
       lager2.clearTable(tableId);
        $(tableId).DataTable( {
            "scrollY":        "85%",
            "scrollCollapse": true,
            "paging":         false
        } );
    },
    correctData:function(datoVal){
      var res = datoVal.split("-");
      return res[2]+"-"+res[1]+"-"+res[0];
    }


}