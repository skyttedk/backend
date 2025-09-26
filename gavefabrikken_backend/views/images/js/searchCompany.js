 var searchCompany = {

      receiptNumber:function(){
       var s =  $("#receiptSearch").val();

         if(s.indexOf("@") > 0){
            ajax({"mail":s},"mail2/getEmailInfo","searchCompany.showEmail","");
         } else {
            ajax({"search":s},"receipt/findReceiptByNumber","searchCompany.showReceipt","");
         }

      },
      showEmail:function(res){

        var html = "<table border=1 width=100%><tr><th>Dato</th> <th>Subjekt</th> <th>1=sendt</th> <th>1=fejl</th> <th>Fejl besked</th> <th>fejl besked2</th> <th>bounce(fejl)</th> ";
          for(var j=0;res.data.length > j;j++) {
             html+="<tr><td>"+res.data[j].created_datetime+"</td>";
             html+="<td>"+res.data[j].subject+"</td>";
             html+="<td>"+res.data[j].sent+"</td>";
             html+="<td>"+res.data[j].error+"</td>";
             html+="<td>"+res.data[j].error_message+"</td>";
             html+="<td>"+res.data[j].is_smtp_error+"</td>";
             html+="<td>"+res.data[j].bounce_type+"</td></tr>";
          }
          html+="</table>";

      if(res.data.length == 0){
        html = "Ingen data";
      }
      $("#dialog-searchCompany").html(html);
        $( "#dialog-searchCompany" ).dialog({
            resizable: true,
            height: 600,
            width: 800,
            modal: true,
            buttons: {

                LUK: function() {
                    $( this ).dialog( "close" );
                }
            }
        });


      },
      showReceipt:function(res){

        var html = "<table width=100%>";


      for(var j=0;res.data.length > j;j++) {
        var kortType

        if(res.data[j].attributes.shop_is_company == 1) {
            kortType  = "Valg shop"
        } else {
            kortType  = "Gavekort shop"
        }



        html+="<tr bgcolor='#ddd'><td ></td><td></td></tr>";
        html+="<tr style='color:white;' bgcolor='#4CAF50' width=30%><td>Ordre nr.</td><td width=69% >"+res.data[j].attributes.order_no+"</td></tr>";
         html+="<tr><td> Oprettet d.</td><td>"+res.data[j].attributes.order_timestamp.date+"</td></tr>";
     html+="<tr><td>Firmanavn</td><td>"+res.data[j].attributes.company_name+"</td></tr>";
     html+="<tr><td>Brugernavn</td><td>"+res.data[j].attributes.user_name+"</td></tr>";
      html+="<tr><td>Bruger type</td><td>"+kortType+"</td></tr>";

        html+="<tr><td>Email</td><td>"+res.data[j].attributes.user_email+"</td></tr>";
        html+="<tr><td>Gave valgt</td><td>"+res.data[j].attributes.present_model_name+"</td></tr>";
      }
      html += "</table>";
      if(res.data.length == 0){
        html = "Ingen data";
      }
      $("#dialog-searchCompany").html(html);
        $( "#dialog-searchCompany" ).dialog({
            resizable: true,
            height: 600,
            width: 600,
            modal: true,
            buttons: {

                LUK: function() {
                    $( this ).dialog( "close" );
                }
            }
        });

}




 /*
       $( "#dialog-shopboard" ).dialog({
            resizable: true,
            height: 600,
            width: 600,
            modal: true,
            buttons: {
                "Gem": function() {
                    shopboard.add();
                },
                Cancel: function() {
                    $( this ).dialog( "close" );
                }
            }
        });

   */


 }