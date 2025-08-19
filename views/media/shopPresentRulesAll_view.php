<script src="lib/jquery.min.js"></script>
<script src="lib/jquery-ui/jquery-ui.js"></script>
<script>

 $( document ).ready(function() {
       $.ajax(
            {
            url: '../index.php?rt=shopPresentRules/showAllRules',
            type: 'POST',
            dataType: 'json',
            data: {token:"dsf984gh58b2i23t4g8" }
            }
          ).done(function(res) {
            var html = "<table border=1>";
            html+= "<tr><th>Firma</th><th>cvr</th><th>leveringsfirma</th><th>adresse</th><th>postnr</th><th>by</th><th>kontaktperson</th><th>gave</th><th>model</th><th></th><th></th></tr>"
            $.each(res.data, function(index, value)
            {
                var regel = "";
                if(value.attributes.rules == 2 ){
                    regel = "<span style='color:red'>Altid lukket</span>";
                }
                if(value.attributes.rules == 1 ){
                   regel = "<span style='color:blue'>Altid &aring;bent</span>";
                }
                if(value.attributes.rules != 0){
                html+= "<tr>"
                    html+="<td>"+value.attributes.name+"</td>";
                    html+="<td>"+value.attributes.cvr+"</td>";
                    html+="<td>"+value.attributes.ship_to_company+"</td>";
                    html+="<td>"+value.attributes.ship_to_address+"</td>";
                    html+="<td>"+value.attributes.ship_to_postal_code+"</td>";
                    html+="<td>"+value.attributes.ship_to_city+"</td>";
                    html+="<td>"+value.attributes.contact_name+"</td>";
                    html+="<td bgcolor=#C2D1FA>"+value.attributes.model_name+"</td>";
                    html+="<td bgcolor=#C2D1FA>"+value.attributes.model_no+"</td>";
                    html+="<td>"+regel+"</td>";
                    html+="<td><button onclick='removeRule(this,\""+value.attributes.company_id+"\",\""+value.attributes.present_id+"\",\""+value.attributes.model_id+"\",)'>Slet regel</button></td>";
                html+="</tr>";
                }


            })
            html+= "<table>";
            $(".showAllRules").html(html);
            }
        )
});
function removeRule(ele,company_id,present_id,model_id){
 var r = confirm("Vil du slette regel");
 var ele = ele;
 if(r){
   var formData = {present_id:present_id,model_id:model_id,company_id:company_id,token:"dsf984gh58b2i23t4g8",action:"0"};
    $.ajax(
            {
            url: '../index.php?rt=shopPresentRules/updateRulesV2',
            type: 'POST',
            dataType: 'json',
            data: formData
            }
          ).done(function(res) {
              $(ele).parent().parent().hide();

           })
 }


}

</script>


<style>
#spr-container{
  font-size: 10px !importent;
}
#spr-container input[type=checkbox]
{
  /* Double-sized Checkboxes */
  -ms-transform: scale(2); /* IE */
  -moz-transform: scale(2); /* FF */
  -webkit-transform: scale(2); /* Safari and Chrome */
  -o-transform: scale(2); /* Opera */
  transform: scale(2);
  padding: 10px;
}
table {
  border-collapse: collapse;
  width: 100%;
}

th, td {
  text-align: left;
  padding: 8px;
}

tr:nth-child(even) {background-color: #f2f2f2;}

tr:hover{
  background-color:#C2D1FA;
}

</style>
<div class="showAllRules">

</div>








<!-- templates -->

