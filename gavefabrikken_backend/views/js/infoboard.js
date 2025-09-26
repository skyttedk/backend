var infoboard = {
    idToUpdate:[],
    load:function(){
       $( "#tabsSur" ).tabs();
        system.work();



        ajax({},"infoboard/getPresentExceededReservation","infoboard.initResponse","");
    },
    update: async function(){
      if(infoboard.idToUpdate.length > 0 ){
          await infoboard.updateStorage();
          infoboard.load();
      } else {
          infoboard.load();
      }

    },
    filterCountry:function(country){
  
        $(".info").hide();
        $("."+country).show();
    },
    openItemInShop:function(itemId){
             $( "#infoboard-item-in-shop" ).html("Systemet arbejder");
                                $( "#infoboard-item-in-shop" ).dialog();
           $.post("index.php?rt=infoboard/getItemInShops",{itemId:itemId }, function(res, status) {
                            if(res.status == 0) { alert("Noget gik galt") }
                            else {
                             let totalOrder = res.data.totalOrder[0].antal
                             let totalReserve = res.data.totalReserve[0].antal
                             let totalUser = res.data.totalUser[0].antal
                             let totalHasNotSelected =  totalUser - totalOrder;
                             var html = `<table border=1 width=300>
                                <tr><th>Valgte</th><th>Reserverede</th><th>Ej valgte </th><th>Kunder ialt</th></tr>
                                <tr><td>${totalOrder}</td><td>${totalReserve}</td><td>${totalHasNotSelected}</td><td>${totalUser}</td></tr>
                                </table>`;
                              $( "#infoboard-item-in-shop" ).html(html);
                              /*
                                var html = "<table border=1><tr><th>Firma</th><th>gaver valg</th><th>gaver reserveret</th></tr>"

                                var totalSelectedPresent = 0;
                                var totalReservedPresent = 0;

                               for (var key in res.data) {

                                  if(res.data[key].quantity  != null  ){


                                       if((res.data[key].antal*1) > (res.data[key].quantity*1)  ){
                                            html+="<tr><td >"+res.data[key].name+"</td><td>"+res.data[key].antal+"</td><td>"+res.data[key].quantity+"</td></tr>"
                                        }

                                        totalReservedPresent+=  res.data[key].quantity*1;

                                    }
                                    totalSelectedPresent+=  res.data[key].antal*1;
                                }

                                html+="</table>";

                                $( "#infoboard-item-in-shop" ).html(html);
                                var overdue = "";
                                if( totalSelectedPresent  >=  totalReservedPresent   ) { overdue = "overdue" }
                                $( "#infoboard-item-in-shop" ).prepend("<table border=1><tr ><td>total gaver valg</td><td> Total gaver reserveret</td></tr><tr class='"+overdue+"'><td>"+totalSelectedPresent+"</td><td>"+totalReservedPresent+"</td></tr></table><br><br>");
                               */
                            }
            }, "json");



    },



    updateStorage:function(){
        return new Promise(function(resolve, reject) {
            infoboard.idToUpdate.forEach(async (id) => {
                 return new Promise(function(resolve, reject) {
                       $.post("index.php?rt=infoboard/updateWarningLevel",{reservation_id:id,warning_level:$("#present_reservation_id_"+id).val(),quantity:$("#present_quantity_"+id).val() }, function(res, status) {
                            if(res.status == 0) {  }
                            else { resolve(res) }
                        }, "json");
                })
            })
            alert("Update completed")
            infoboard.idToUpdate = [];
            resolve();
        })
    },
    /*
    updateItemStats:function(){
                return new Promise(function(resolve, reject) {
                       $.post("index.php?rt=infoboard/updateWarningLevel",{ localisation:localisation }, function(res, status) {
                            if(res.status == 0) {  }
                            else { resolve(res) }
                        }, "json");
                })
    }
     */

    showUpdateBtn:function(id){
        infoboard.idToUpdate.push(id);
        infoboard.idToUpdate = infoboard.idToUpdate.filter(onlyUnique);
    },
    initResponse:function(response){
        var html = "";
        var htmlValg = "<table width=85%><tr><th>Land</th><th>Shop</th><th>Gave</th><th>Model</th><th>Model kode</th><th>Antal valgte</th><th>Antal reserveret</th><th>Advarsel</th><th>Luk</th><th width='50'>Link to shop</th></tr>";
        var htmlKort = "<table width=85%><tr><th>Land</th><th>Shop</th><th>Gave</th><th>Model</th><th>Model kode</th><th>Antal valgte</th><th>Antal reserveret</th><th>Advarsel</th><th>Luk</th><th width='50'>Link to shop</th>";
        for (var key in response.data) {
            var insObj = response.data[key].attributes;
            doShow = true;
            if(insObj.properties == null){
                doShow = true;
            } else {
                var option = insObj.properties;
                option =  jQuery.parseJSON(option);
                var variantList = option.variantListOption;
                var variantListOptionArr = variantList.toString().split(",");

                if(option.aktivOption == false){
                    doShow = false;
                }
            }
             if(doShow == true ){
                    if( (parseInt(insObj.warning_level) + parseInt(insObj.c) ) > parseInt(insObj.quantity)){
                        var overdue = "";
                        if( parseInt(insObj.c)  >=  parseInt(insObj.quantity)   ) { overdue = "overdue" }
                        var langImg = "../gavefabrikken_backend/views/media/icon/denmark-flag-medium (1).png";
                        var land = "dk";
                        if(insObj.localisation ==  4){
                            land = "no";
                            langImg = "../gavefabrikken_backend/views/media/icon/norway-flag-medium (1).png";
                        }
                        //insObj.model_no
                        html = "<tr class= 'info "+overdue+" "+land+"'><td width='40'><img width='40'' src='"+langImg+"'  /></td><td>"+insObj.name+"</td><td>"+insObj.model_name+"</td><td>"+insObj.model_no+"</td><td>"+insObj.model_present_no+"</td><td>"+insObj.c+"</td><td><input  style='width:80px' onkeyup='infoboard.showUpdateBtn("+insObj.present_reservation_id+")' id='present_quantity_"+insObj.present_reservation_id+"' type='number' value='"+insObj.quantity+"'  />   </td><td><input style='width:80px' onkeyup='infoboard.showUpdateBtn("+insObj.present_reservation_id+")' id='present_reservation_id_"+insObj.present_reservation_id+"' type='number' value='"+insObj.warning_level+"'  /></td><td>"+insObj.end_date+"</td><td><img style='margin-right:10px' onclick=\"infoboard.linkToShop('"+insObj.shop_id+"')\" height=20 src=\"views/media/icon/123403.png\"  /><img onclick=\"infoboard.openItemInShop('"+insObj.model_present_no+"')\" height=20 src=\"views/media/icon/gave.png \"  /></td></tr>";
                    }
                    if(insObj.is_gift_certificate == 1) {
                       htmlKort+= html;
                    } else {
                       htmlValg+= html;
                    }

             }

        }

        $(".storageMonitoringValg").html(htmlValg+"</table>");
        $(".storageMonitoringKort").html(htmlKort+"</table>");
        system.endWork();
      infoboard.getPresentSelected();
    },
    linkToShop:function(id){
        var url = window.location.href;
        var urlArr = url.split("/");
        var newUrl = urlArr[0]+"/"+urlArr[1]+"/"+urlArr[2]+"/"+urlArr[3]+"/gavefabrikken_backend/index.php?rt=mainaa&editShopID="+id;

//        https://gavefabrikken.dk//index.php?rt=mainaa&editShopID=1012
//        [BACKENDURL]/index.php?rt=mainaa&sysid=40
        window.open(newUrl, "_blank");
    },
    getPresentSelected:function(){
        system.work();
        ajax({},"infoboard/getGiftSelected","infoboard.getPresentSelectedResponse","");
    },
    getPresentSelectedResponse:function(response){
         var html= "<table width=100%>";
         var timeData = "";
         var antal = "";
        for (var key in response.data) {
            var insObj = response.data[key].attributes;
             var str = insObj.t
             res = str.split("-");
            timeData+="<td>"+res[2]+"-"+res[1]+"-"+res[0]+"</td>";
            antal+="<td>"+insObj.antal+"</td>";
        }
        html+= "<tr>"+timeData+"</tr>";
        html+= "<tr>"+antal+"</tr>";
        html+="</table>";
        $(".giftSelectedStatS").html(html);
        system.endWork();
    }
}
function onlyUnique(value, index, self) {
  return self.indexOf(value) === index;
}
