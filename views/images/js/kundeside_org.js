var kundeside = {

    login:function(){
        $("#dataCsv").hide();
        $.post(_ajaxPath+"kundeside/getData",{"token":token,"dato":dato}, function(response, textStatus) {
            if(response.status == 2 || response.status == 0){
                $("#main").html("<center><h1>System is being updated</h1></center>");
                $("#dataCsv").hide();
            } else {
                $("#dataCsv").show();
                console.log(response.data.result)
                kundeside.buildTableHtml(response.data.result);
            }

        },"json");

    },
    buildTableHtml:function(data){



        if(data.length == 0){
            $("#main").html("Ingen data fundet");

        } else {

            var html = '<table class="shopboard-container" id="tableData" class="display nowrap" cellspacing="0" width="100%">';

            if(data[0].shop_id == "57" || data[0].shop_id == "58" || data[0].shop_id == "59" || data[0].shop_id == "272" || data[0].shop_id == "574" || data[0].shop_id == "2550" || data[0].shop_id == "2549" || data[0].shop_id == "7121" || data[0].shop_id == "4668"  ){
                html+="<thead><tr><th>Firma</th><th>Adresse1</th><th>Adresse2</th><th>By</th><th>Postnr.</th><th>Kortnummer</th><th>Adgangskode</th><th>Kort</th><th>Deadline</th><th>Navn</th><th>Email</th><th>Gave</th><th>Model</th><th>Item nr.</th><th>Item alias</th><th>Ordrenr.</th></tr></thead> <tbody> "
            } else if( data[0].shop_id == "1832" || data[0].shop_id == "1981" || data[0].shop_id == "2558" || data[0].shop_id == "4793" || data[0].shop_id == "5117" ) {
                html+="<thead><tr><th>Företag</th><th>Adress 1</th><th>Adress 2</th><th>Stad</th><th>Postnummer</th><th>Användarnamn</th><th>Lösenord</th><th>Kort</th><th>Giltiga t.o.m </th><th>Namn</th><th>E-mail</th><th>Gåva</th><th>Modell</th><th>Item nr.</th><th>Item alias</th><th>Ordre nr.</th></tr></thead> <tbody> "
            } else {
                html+="<thead><tr><th>Firma</th><th>Adresse1</th><th>Adresse2</th><th>By</th><th>Postnr.</th><th>Kortnummer</th><th>Adgangskode</th><th>Kort</th><th>Udløbsdato</th><th>Navn</th><th>Email</th><th>Gave</th><th>Model</th><th>Item nr.</th><th>Item alias</th><th>Ordre nr.</th></tr></thead> <tbody> "
            }





            for(var i=0;i<data.length;i++){
                if(data[i].username == null) data[i].username = "";
                if(data[i].user_email == null) data[i].user_email = "";
                if(data[i].present_name == null) data[i].present_name = "";
                if(data[i].present_model_name == null) data[i].present_model_name = "###";
                if(data[i].order_no == null) data[i].order_no = "";
                if(data[i].user_name == null) data[i].user_name  = "";
                if(data[i].user_name == null) data[i].user_name  = "";
                if(data[i].shopName == null) data[i].shopName  = "";
                if(data[i].fullalias == null) data[i].fullalias  = "";
                if(data[i].order_no == null) data[i].order_no  = "";
                if(data[i].model_present_no == null) data[i].model_present_no  = "";

                var  model = data[i].present_model_name;
                var modelArr =  model.split("###");
                var modelArr = data[i].present_model_name.split("###");
                // Ensure both array elements exist
                if(!modelArr[1]) modelArr[1] = "";

                html+="<tr><td>"+data[i].ship_to_company+"</td>";
                html+="<td>"+data[i].ship_to_address+"</td>";
                html+="<td>"+data[i].ship_to_address_2+"</td>";
                html+="<td>"+data[i].ship_to_city+"</td>";
                html+="<td>"+data[i].ship_to_postal_code+"</td>";
                html+="<td>"+data[i].username+"</td>";
                html+="<td>"+data[i].password+"</td>";
                html+="<td>"+data[i].shopName+"</td>";
                html+="<td>"+data[i].expire_date+"</td>";
                html+="<td>"+data[i].user_name+"</td>";
                html+="<td>"+data[i].user_email+"</td>";
                html+="<td>"+modelArr[0]+"</td>";
                html+="<td>"+modelArr[1]+"</td>";
                html+="<td>"+data[i].model_present_no+"</td>";
                html+="<td>"+data[i].fullalias+"</td>";
                html+="<td>"+data[i].order_no+"</td>";
                html+="</tr>";
            }

            html+="</tbody>";
            // html+="<tfoot><tr><th>Firma navn</th><th>Cvr</th><th>Ean</th><th>Adresse</th><th>Kortnummer</th><th>Adgangskode</th><th>Udløbsdato</th><th>Navn</th><th>Email</th><th>Gave</th><th>Model</th><th>Ordre nr.</th></tr></tfoot> "
            html+="</table>";
            $("#main").html(html);
            kundeside.initTable();
        }
    },
    initTable:function(){
        $("#tableData").DataTable({
            "pageLength" : 20,
            "order": [ 5, "asc" ],
            scrollY:        '50vh',
            scrollCollapse: true,
            paging:         false
        });
    }



}