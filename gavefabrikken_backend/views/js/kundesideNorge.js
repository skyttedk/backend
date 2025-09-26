var kundesideNorge = {

    login:function(){


        $.post(_ajaxPath+"kundesidenorge/getData",{"email":email}, function(response, textStatus) {

            kundesideNorge.buildTableHtml(response.data.result);
          },"json");


    },
    buildTableHtml:function(data){
        var html = '<table class="shopboard-container" id="tableData" class="display nowrap" cellspacing="0" width="100%">';
        html+="<thead><tr><th>Firma navn</th><th>Org.nummer</th><th>Adresse</th><th>Kortnummer</th><th>Passord</th><th>Budsjett</th><th>Gavevalg deadline</th><th>Navn</th><th>Email</th><th>Gave</th><th>Model</th><th>Bestillingsnr.</th></tr></thead> <tbody> "



        for(var i=0;i<data.length;i++){
       if(data[i].username == null) data[i].username = "";
       if(data[i].user_email == null) data[i].user_email = "";
       if(data[i].present_name == null) data[i].present_name = "";
       if(data[i].present_model_name == null) data[i].present_model_name = "";
       if(data[i].order_no == null) data[i].order_no = "";
       if(data[i].user_name == null) data[i].user_name  = "";
       if(data[i].ship_to_company == null) data[i].ship_to_company  = "";
       var adress = "";
       if(data[i].ship_to_company !=  ""){
            adress+= data[i].ship_to_company+", ";
       }
       adress+= data[i].ship_to_address+", ";
       if(data[i].ship_to_address_2 !=  ""){
            adress+= data[i].ship_to_address_2+", ";
       }
       adress+=  data[i].ship_to_postal_code+", "+data[i].ship_to_city


       var  model = data[i].present_model_name;
       model = model.replace("###", " - ");
          var budsjett = "";
          if(data[i].shop_id == 57){  budsjett = "400"  }
          if(data[i].shop_id == 58){  budsjett = "600"  }
          if(data[i].shop_id == 59){  budsjett = "800"  }
          if(data[i].shop_id == 272){  budsjett = "300"  }



          html+="<tr><td>"+data[i].name+"</td>";
          html+="<td>"+data[i].cvr+"</td>";
          html+="<td>"+adress+"</td>";
          html+="<td>"+data[i].username+"</td>";
          html+="<td>"+data[i].password+"</td>";
          html+="<td>"+budsjett+"</td>";
          html+="<td>"+data[i].expire_date+"</td>";
          html+="<td>"+data[i].user_name+"</td>";
          html+="<td>"+data[i].user_email+"</td>";
          html+="<td>"+data[i].present_name+"</td>";
          html+="<td>"+model+"</td>";
          html+="<td>"+data[i].order_no+"</td>";
          html+="</tr>";
      }

        html+="</tbody>";
       // html+="<tfoot><tr><th>Firma navn</th><th>Cvr</th><th>Ean</th><th>Adresse</th><th>Kortnummer</th><th>Adgangskode</th><th>Udløbsdato</th><th>Navn</th><th>Email</th><th>Gave</th><th>Model</th><th>Ordre nr.</th></tr></tfoot> "
        html+="</table>";
        $("#main").html(html);
        kundesideNorge.initTable();
    },
    initTable:function(){
                $("#tableData").DataTable();
     }



}