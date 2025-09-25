<script>
    var spsMsgTimer;
    var downloadShopID;
function downloadShopPresent(id){
    downloadShopID = id;
    /*
    if(id != 4354){
        alert("Download er ved at blive opdateret ");
        return;
    }
*/
    ajax({"shop_id":_editShopID},"shopItems/validate","downloadShopPresentRes","");
    // window.open("../gavefabrikken_backend/index.php?rt=rapport/salepersonlist&shop_id="+id);
}
function downloadShopPresentRes(res)
{
    if(res.data == true){
        window.open("../gavefabrikken_backend/index.php?rt=rapport/salepersonlist&shop_id="+downloadShopID);
    } else {
        /*
        let r = confirm("Der er "+res.data+" varer der ikke har et valid varenr.\nØnsker du at oprette en opgave på at få rettet de varenr. der mangler?\nSå tryk 'OK' knappen." )
        if(r){
            ajax({"shop_id":_editShopID},"shopItems/sendTaskMissingItemnr","sendTaskMissingItemnr","");
        }
                 */
        window.open("../gavefabrikken_backend/index.php?rt=rapport/salepersonlist&shop_id="+downloadShopID);
    }
}
function sendTaskMissingItemnr(res){
    alert("Opgaven er sendt");
}
function ShopPresentStrength(_editShopID)
{
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
    ajax({"shop_id":_editShopID},"shop/getShopPresentsNew","sps_visGaveListe","");
}

function sps_setEvents(){
    $(".PresentStrength").unbind("change").on('change', function() {
        sps_setStrength($(this).attr("data-id"),this.value);
    });
}
function sps_setStrength(id,sps)
{
    let postData = {
        'modelID':id,
        'sps':sps
    }
    ajax(postData,"present/updateStrength","sps_setStrengthReturn","");
}
function sps_setStrengthReturn(){
    clearTimeout(spsMsgTimer);
    $("#sps-msg").html("Styrken gemt");
    spsMsgTimer = setTimeout(function (){
        $("#sps-msg").html("");
    }, 3000);

}

// strength

function sps_visGaveListe(response){
    var presentsHtml = "";
    presentsHtml = "<div style='color: red; font-size: 16px; font-weight: bold; height: 17px;' id='sps-msg'></div><div id='ejvalgteSaveStatus' style='color:red;'></div><br><center><table border=1 >";
    for(var i=0;response.data.length >i;i++){
        var d =  response.data[i].attributes;
        let option0 = d.strength*1 == 0 ? "selected":"";
        let option1 = d.strength*1 == 1 ? "selected":"";
        let option2 = d.strength*1 == 2 ? "selected":"";
        let option3 = d.strength*1 == 3 ? "selected":"";


        presentsHtml+=`<tr><td height=30 width=400>${d.model_name}</td><td width=250>${d.model_no}</td><td width=250>${d.model_present_no}</td>
        <td>
            <select name="strength" class="PresentStrength" data-id="${d.model_id}">
                <option ${option0} value="0">Ej valgt</option>
                <option ${option1} value="1">Svag</option>
                <option ${option2} value="2">Middel</option>
                <option ${option3} value="3">Stærk</option>
            </select>
        </td></tr>`
    }
    presentsHtml+="</table></center>";
    $("#gaveEjValgteDialog").html(presentsHtml );
    sps_setEvents()
}



</script>