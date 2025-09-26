var lager = {

    loadCompanyList:function(){
             ajax({},"company/getCompanyOrders","lager.loadCompanyListResponse","");
    },
    loadCompanyListResponse:function(response){
        var html = "";
        var  deal = ["11563392","32479081","31299004","31480469","56000828","29190909","32023908","12445040","21578894","33612192","29190909","47289114","31178266","12445040","25229002"];
                        // alert(response.data.companyorders.length)
        $("#total").html("Antal: "+ response.data.companyorders.length.toString())
      $.each( response.data.companyorders, function( key, value ) {

            var deleveryWeek = "";

            if(value.expire_date == "2016-10-28"){ deleveryWeek = "Uge 48"; }
            if(value.expire_date == "2016-11-06"){ deleveryWeek = "Uge 48"; }

            if(value.expire_date == "2016-11-11"){ deleveryWeek = "Uge 50"; }
            if(value.expire_date == "2016-11-20"){ deleveryWeek = "Uge 50"; }

            if(value.expire_date == "2016-12-31"){ deleveryWeek = "Uge 4 (2017)"; }
            if(value.expire_date == "2018-01-01"){ deleveryWeek = "2018-01-01"; }





            if(value.shop_name == "Julegavekortet.dk"){
                html+= "<div id=\"post_"+value.id+"\" class=\"jgk lagerMenu\"><div class=\"onlyPrint\" style=\"width:350px; height:100px;\"></div><table width=600 border=0>"
            }
            if(value.shop_name.indexOf("24") > -1 ){
                html+= "<div id=\"post_"+value.id+"\" class=\"24gaver lagerMenu\" style=\"display:none\"><div class=\"onlyPrint\" style=\"width:350px; height:100px;\"></div><table width=600 border=0>"
            }
            if(value.shop_name.indexOf("uld") > -1 ){
                html+= "<div id=\"post_"+value.id+"\" class=\"guld lagerMenu\" style=\"display:none\"><div class=\"onlyPrint\" style=\"width:350px; height:100px;\"></div><table width=600 border=0>"
            }
            if(value.shop_name.indexOf(".no") > -1 ){
                html+= "<div id=\"post_"+value.id+"\" class=\"norge lagerMenu\" style=\"display:none\"><div class=\"onlyPrint\" style=\"width:350px; height:100px;\"></div><table width=600 border=0>"
            }






            html+= "<u><h3>"+value.shop_name+"</h3></u>";
            if(value.shop_name == "24gaver.dk"){
                   html+= "<h4>Gavekort DKK. "+value.certificate_value+",-</h4>";
            }
            if(value.shop_name.indexOf(".no") > -1){
                   html+= "<h4>Gavekort  "+value.certificate_value+",-</h4>";
            }
            html+= "<b>"+deleveryWeek+"</b>";

            html+= "<div id=\"post_"+value.id+"\"><div class=\"onlyPrint\" style=\"width:100px; height:100px;\"></div><table width=600 border=0>"
            if(value.is_printed == "1"){
                html+="<tr class=\"notInPrint\" > <td width=250></td> <td width=250></td> <td><input class=\"actionCheckbox\" id=\""+value.id+"\" type=\"checkbox\" /> <label  id=\"status_"+value.id+"\">Printet</label></td> </tr>";
            } else {
                html+="<tr class=\"notInPrint\" > <td width=250></td> <td width=250></td> <td><input class=\"actionCheckbox\" id=\""+value.id+"\" type=\"checkbox\" /> <label  id=\"status_"+value.id+"\"/label> </td> </tr>";
            }

            html+="<tr> <td><b>Ordernr.:</b></td> <td><b>"+value.order_no+"</b></td> <td></td> </tr>";
            if(value.is_cancelled == "1"){
                html+="<tr> <td><h1 style=\"color:red\">ANNULERET</h1></td><td><h1 style=\"color:red\">ANNULERET</h1></td><td><h1 style=\"color:red\">ANNULERET</h1></td> </tr>";
            } else if(deal.indexOf(value.cvr) > -1){
                html+="<tr> <td><h1 style=\"color:red\">SPECIAL</h1></td><td><h1 style=\"color:red\">AFTALE</h1></td><td><h1 style=\"color:red\">IKKE SEND</h1></td> </tr>";
            } else{
                html+="<tr> <td></td> <td><br /></td> <td></td> </tr>";
            }

            html+="<tr> <td>Virksomhed:</td> <td>"+value.company_name+"</td> <td></td> </tr>";
            html+="<tr> <td>Cvr:</td> <td>"+value.cvr+"</td> <td></td> </tr>";
            html+="<tr> <td>Vej:</td> <td>"+value.ship_to_address+"</td> <td></td> </tr>";
            html+="<tr> <td>Postnummer:</td> <td>"+value.ship_to_postal_code+"</td> <td></td> </tr>";
            html+="<tr> <td>By:</td> <td>"+value.ship_to_city+"</td> <td></td> </tr>";
            html+="<tr> <td></td> <td><br /></td> <td></td> </tr>";
            html+="<tr> <td>Kontaktperson:</td> <td>"+value.contact_name+"</td> <td></td> </tr>";
            html+="<tr> <td>E-mail:</td> <td>"+value.contact_email+"</td> <td></td> </tr>";
            html+="<tr> <td>Tlf.nr.:</td> <td>"+value.contact_phone+"</td> <td></td> </tr>";
            html+= "</table>";
            html+="<br /><table  width=600 border=0>";
            html+="<tr> <td width=200>Gavekort</td> <td width=200>Start</td> <td width=200>Slut</td> <td></td>  </tr>";
            html+="<tr> <td>Antal: "+value.quantity+"</td> <td>"+value.certificate_no_begin+"</td> <td>"+value.certificate_no_end+"</td> </tr>";
            html+="</table><div class=\"footer\"></div><hr /></div>";

      });
      $("#mainContainer").html(html);
    },
    printIt : function(){
        var formData = "";
        $('.actionCheckbox').each(function (index,ele) {
            if ($(this).is(':checked')) {
                $("#status_"+this.id).html("Printet");
                formData+= this.id+";";
            } else {
                $("#post_"+this.id).hide();
            }
        });
        formData = formData.slice(0, -1);
        ajax({"id_list":formData,"is_printed":"1"},"company/setCompanyOrderPrinted","lager.printItResponse","");

    },
    printItResponse : function(response){
       window.print();
       window.location ="../gavefabrikken_backend/index.php?rt=lager";
    },
    shipIt : function(){
        if(confirm("Er du sikker på du vil sætte markerede kort til udleveret?") == true){

            var formData = "";
            $('.actionCheckbox').each(function (index,ele) {
                if ($(this).is(':checked')) {
                    formData+= this.id+";";
                } else {
                    $("#post_"+this.id).hide();
                }
            });
            formData = formData.slice(0, -1);
            ajax({"id_list":formData,"is_shipped":"1"},"company/setCompanyOrderShipped","lager.shipItResponse","");
        }
    },
    shipItResponse : function(response){
        window.location ="../gavefabrikken_backend/index.php?rt=lager";
    },
    selectAll : function(){
          $("input:checkbox").attr ( "checked" , true );
    }



}