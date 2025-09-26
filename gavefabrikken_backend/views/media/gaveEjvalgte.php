<div id="ejValgteBtn" style="float: right; padding-left: 10px; display: none;"><button style="background-color: red;" type="button" onclick="openGaveEjvalgte(_editShopID )">Gave til ejvalgte</button></div>
<div style="display: none;" id="gaveEjValgteDialog"></div>
<script>

var _ejValgtGaveid, _ejValgtGavemodelid,_ejValgtCounter;

function openGaveEjvalgte(id){
  _ejValgtGaveid = "";
  _ejValgtGavemodelid = "";
  _ejValgtCounter = 0;
  $("#ejvalgteSaveStatus").html("");

     $('#gaveEjValgteDialog').html('<div style="padding: 20px; text-align: center;">Henter gaver i shop..</div>');
     $('#gaveEjValgteDialog').dialog({
            title: 'Gaver til dem der ikke har valgt',
            modal: true,
            width:700,
            height:500,
            buttons: {
 	            Luk: function() {
                    $( this ).dialog( "close" );
                }
            }
        });
         ajax({"shop_id":_editShopID},"shop/getShopPresentsNew","visEjvalgteGaveListe","");
}
function visEjvalgteGaveListe(response){
  var presentsHtml = "";
        presentsHtml = "<div id='ejvalgteSaveStatus' style='color:red;'></div><br><center><table border=1 >";
        for(var i=0;response.data.length >i;i++){
            var d =  response.data[i].attributes;
            presentsHtml+="<tr><td height=30 width=400>"+d.model_name+"</td><td width=250>"+d.model_no+"</td><td width=250>"+d.model_present_no+"</td><td><button onclick=\"opretGaveEjvalgte('"+d.present_id+"','"+d.model_id+"') \">V&oelig;lg</button></td></tr>";
        }
        presentsHtml+="</table></center>";
        $("#gaveEjValgteDialog").html(presentsHtml );
}
function opretGaveEjvalgte(gaveid,modelid){
    _ejValgtGaveid      = gaveid;
    _ejValgtGavemodelid = modelid;
    var tjek ="";
    if(_ejValgtCounter == 0){
        tjek = prompt("Er du sikker, vil du tildele gaver til dem som ikke har valgt.\n Indtast koden: gaver1234");
    }

    if (tjek == "gaver1234" || _ejValgtCounter > 0) {
            var formData = {};
            formData["shopid"] = _editShopID;
            formData["present_id"] = gaveid;
            formData["model_id"] = modelid;
            ajax(formData,"order/autoSelectSpecificPresents","gaverUnderTildeling","");


    } else {
        alert("Forkert kode eller du har valgt at trykke cancel");
        //opretGaveEjvalgte(gaveid,modelid)
    }



    /*
   alert("hej");
    var formData = {};
    formData["shopid"] = "266";
    formData["present_id"] = "2767";
    formData["model_id"] = "1003";
    ajax(formData,"order/autoSelectSpecificPresents","shopSettings.response","");
   */



}
function gaverUnderTildeling(res){
    _ejValgtCounter++
    $("#ejvalgteSaveStatus").html("<h3>Systemet arbejder, det kan tage tid. ligenu er der oprettet "+_ejValgtCounter+" order</h3>")

  if(res.data.orderStatus == 1){
    opretGaveEjvalgte(_ejValgtGaveid,_ejValgtGavemodelid);
  } else {
    alert("alle order er nu oprettet")
    $( "#gaveEjValgteDialog" ).dialog( "close" );
  }
}


</script>