var _rapportData;
var _profileEditMode = "";
var rapport = {

    showNewProfile : function(){
        $( "#dialog-message" ).html("")
        var html = "";
        var htmlOption = "";
        var htmlSortOption = "";

        for(var i=0;_rapportData.length > i; i++){
            if(_rapportData[i].is_email != 1 && _rapportData[i].is_name != 1 && _rapportData[i].is_password != 1 && _rapportData[i].is_username != 1 ){
                htmlOption+='<option value="'+_rapportData[i].id+'">'+_rapportData[i].name+'</option>'
                htmlSortOption+='<option value="'+_rapportData[i].name+'">'+_rapportData[i].name+'</option>'
            }
        }

        html+='<label style="margin-left:50px;"><b>Profilnavn: </b></label><input class="newProNavn" type="txt" size=30 /><br /><br />';
        html+='<table border=0 width="450"><tr><td valign="top" align="left"><fieldset><legend><b>Prim&oelig;r gruppering</b></legend>';
        html+='<select class="newProPrime"><option value="none">ej valgt</option><option value="gavevalg">Gavevalg</option>'+htmlOption;
        html+='</select><br /></fieldset><br /></td>';

        html+='<td valign="top" align="left"><fieldset><legend><b>Sekund&oelig;r gruppering</b></legend>';
        html+='<select class="newProSec"><option value="none">ej valgt</option><option value="gavevalg">Gavevalg</option>'+htmlOption;
        html+='</select><br /></fieldset></td></tr><table>';

        html+='<table><td valign="top" align="left"><fieldset><legend><b>Nyt ark pr.</b></legend>';
        html+='<select class="newSheet"><option value="">Ej valgt</option>'+htmlSortOption;
        html+='</select><br /></fieldset></td></tr><table>';
        html+='<br />';

        html+='<fieldset><legend><b>Data pr&oelig;sentation</b></legend>';
        html+='<label>Fortl&oslash;bende</label><input class="newProDisplay" style="margin-left:5px;" type="radio" name="rapportCheckboxOption" value="fort" checked   />'
        html+='<label style="margin-left:15px;">Summeret</label><input class="newProDisplay" style="margin-left:5px;" type="radio" name="rapportCheckboxOption" value="sum" />'
        html+='</select><br /></fieldset><br />';
        html+='<fieldset><legend><b>Felter i rapporten</b></legend>';
        html+="<div style=\"width:450px;\">";
        for(var i=0;_rapportData.length > i; i++){
            html+="<div class=\"fieldsInRapport\"><input class=\"fieldsInRapportCheckbox\" type=\"checkbox\" value=\""+_rapportData[i].id+"\" /><label>"+_rapportData[i].name+"</label></div>"
        }
        html+="</div>";
        html+='</fieldset><br />';

        $( "#dialog-message" ).html(html)



        dialog = $( "#dialog-message" ).dialog({
              autoOpen: true,
              title: "Rapport designer",
              height: 600,
              width: 700,
              modal: true,
              buttons: {
                "Opret profil": rapport.addProfil,
                Cancel: function() {
                  dialog.dialog( "close" );
                }
              },
              close: function() {

              }
            });

    },
    setRapportData : function(data){
        _rapportData = data;
     },
    addProfil : function() {
        if(_profileEditMode !=  ""){
            var fieldsInRapport = [];
            $( ".fieldsInRapportCheckbox" ).each(function( index ) {
                if($(this).is(':checked')) {
                     fieldsInRapport.push( $( this ).val() );
                }
            })
            var data = '{"newSheet":"'+$( ".newSheet option:selected" ).val()+'","fieldsInRapport":"'+fieldsInRapport+'","newProNavn":"'+$(".newProNavn").val()+'","newProPrime":"'+$( ".newProPrime option:selected" ).val()+'","newProSec":"'+$( ".newProSec option:selected" ).val()+'","rapportCheckboxOption":"'+$('input[name="rapportCheckboxOption"]:checked').val()+'"}'
            $("#profile_"+_profileEditMode).html(data)
            $("#profileName_"+_profileEditMode).html($(".newProNavn").val())
             dialog.dialog( "close" );
           ajax({"shop_id":_editShopID,"id":_profileEditMode,"profile_data":data },"report/updateShopReport","rapport.updateList","");


        } else {
            var ramdom = Math.random().toString(36).substring(7);
            var fieldsInRapport = [];
            $( ".fieldsInRapportCheckbox" ).each(function( index ) {
                if($(this).is(':checked')) {
                     fieldsInRapport.push( $( this ).val() );
                }
            })
            var data = '{"newSheet":"'+$( ".newSheet option:selected" ).val()+'","fieldsInRapport":"'+fieldsInRapport+'","newProNavn":"'+$(".newProNavn").val()+'","newProPrime":"'+$( ".newProPrime option:selected" ).val()+'","newProSec":"'+$( ".newProSec option:selected" ).val()+'","rapportCheckboxOption":"'+$('input[name="rapportCheckboxOption"]:checked').val()+'"}'
            var html = "<tr><td align=\"left\" ><span id=\"profileName_"+ramdom+"\" >"+ $(".newProNavn").val() +"</span><div id=\"profile_"+ramdom+"\" style=\"display:none;\">"+data+"</div>  </td><td align=\"center\"><img onclick=\"rapport.doDownloadExcel('"+ramdom+"')\"   src=\"views/media/icon/excel.png\"  height=\"20\" width=\"20\"></td><td align=\"center\"><img onclick=\"rapport.editProFil('"+ramdom+"') \" src=\"views/media/icon/1373253282_pencil_64.png\" height=\"20\" width=\"20\"></td><td align=\"center\"><img onclick=\"rapport.deleteProfil(this,'"+$(".newProNavn").val()+"') \" src=\"views/media/icon/1373253296_delete_64.png\"  height=\"20\" width=\"20\"></td><tr>";
            console.log(data)
            dialog.dialog( "close" );
            formData = { "profile_data":data,"shop_id":_editShopID  };
            $(".rapport").append(html)
            ajax(formData,"report/createShopReport","rapport.updateList","");

        }
    },
    editProFil : function(id)
    {
        _profileEditMode = id;
        rapport.showNewProfile();
       var json = jQuery.parseJSON($("#profile_"+id).html());
        $(".newProNavn").val(json.newProNavn);
        $(".newProPrime" ).val(json.newProPrime)
        $(".newProSec" ).val(json.newProSec)
                $(".newSheet" ).val(json.newSheet)


        $('input:radio[name="rapportCheckboxOption"][value="'+json.rapportCheckboxOption+'"]').attr('checked',true);


        var  tempStr = json.fieldsInRapport;
        var tempArr =  tempStr.split(",");
        $( ".fieldsInRapportCheckbox" ).each(function( index ) {
           if( tempArr.indexOf( $( this ).val() ) != -1  ) {
                $(this).prop('checked', true);
            }
        })


    },
    deleteProfil : function(id,name)
    {

        if (confirm("Vil du slette markeret profilen: "+name) == true) {
            //$(element).parent().parent().remove();
            ajax({"id":id},"report/deleteShopReport","rapport.updateList","");
        }
    },
    doDownloadExcel : function(id)
    {
        window.open("../gavefabrikken_backend/index.php?rt=report/genericReport&id="+id+"&shop_id="+_editShopID);

    },
    updateList : function()
    {
        _profileEditMode = "";
        $(".rapport").html("")
        var html = '<tr><td colspan="4"  height="50" ><button style="margin-right:10px; float: right;" onclick="rapport.showNewProfile()">Opret ny profil</button></td> </tr> <tr>    <th width="810">Profilnavn</th><th width="30"></th><th width="30"></th><th width="30"></th> </tr>'
        $(".rapport").html(html)
        ajax({"id":_editShopID},"report/readAllShopReport","rapport.buildList","");
    },
    buildList : function(data){

        $.each( data.data.reports, function( key, val ) {
            if(val.shop_id == _editShopID)
            {
                jsonStr = val.profile_data;
                jsonStr = jsonStr.slice( 1 );
                jsonStr = jsonStr.slice(0, -1);
                jsonStr = '{'+jsonStr+'}'
                var json = jQuery.parseJSON(jsonStr);

                var data = '{"newSheet":"'+json.newSheet+'","fieldsInRapport":"'+json.fieldsInRapport+'","newProNavn":"'+json.newProNavn+'","newProPrime":"'+json.newProPrime+'","newProSec":"'+json.newProSec+'","rapportCheckboxOption":"'+json.rapportCheckboxOption+'"}'
                var html = "<tr ><td align=\"left\" ><span id=\"profileName_"+val.id+"\" >"+ json.newProNavn +"</span><div id=\"profile_"+val.id+"\" style=\"display:none;\">"+data+"</div>  </td><td align=\"center\"><img onclick=\"rapport.doDownloadExcel('"+val.id+"')\"   src=\"views/media/icon/excel.png\"  height=\"20\" width=\"20\"></td><td align=\"center\"><img onclick=\"rapport.editProFil('"+val.id+"') \" src=\"views/media/icon/1373253282_pencil_64.png\" height=\"20\" width=\"20\"></td><td align=\"center\"><img onclick=\"rapport.deleteProfil('"+val.id+"','"+json.newProNavn+"') \" src=\"views/media/icon/1373253296_delete_64.png\"  height=\"20\" width=\"20\"></td><tr>";
                $(".rapport").append(html)
            }

        })
        rapport.getPartialDeliveryList();
    },
    getPartialDeliveryList : function(){

        ajax({"id":_editShopID},"report/getPartialDeliveryList","rapport.showPartialDeliveryList","");
    },
    showPartialDeliveryList : function(data){
        if(data.status==2)
            return;

        var html="";
        $.each( data.data, function( key, val ) {
           if(val.attributes.delivery_print_date != null){
               let showDate= val.attributes.delivery_print_date.date.replace(".000000", "");
               html+="<div>"+showDate+"</div>";
            }
        })
        $("#partialDeliveryList").html(html);
        $("#partialDeliveryContainer").show();
    },
    setPartialDeliveryList : function(){
        if(confirm("Du er ved at oprettet en dellevering")) {
            ajax({"id": _editShopID}, "report/setPartialDelivery", "rapport.returnSetPartialDelivery", "");
        }
    },
    returnSetPartialDelivery : function(data){
        rapport.getPartialDeliveryList();
    }



}
