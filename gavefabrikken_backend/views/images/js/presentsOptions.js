var _variantList;
var _shopPresentID;
var _propertiesData;
var _presentID;
var presentsOptions = {
    options : function(shopPresentID,presentID){
             _presentID = presentID;
            _shopPresentID = shopPresentID;
            ajax({id:_shopPresentID},"shop/getPresentProperties","presentsOptions.getOptionsPart2","");
   },
   getOptionsPart2 : function(response){
     _propertiesData = response;
     ajax({present_id:_presentID},"present/getModels","presentsOptions.show","");
   },
   show : function(response){

        var option = _propertiesData.data.properties;
        _variantList = response.data.model;

        var variantHtml = "";
        var temp = "";
        var html="";

        if(option == null){

            for (var key in _variantList) {
                if(_variantList[key].language_id== "1"){
                        variantHtml+= "<input checked  class=\"variantListOption\" type='checkbox' value='"+_variantList[key].model_id+"' id='option"+_variantList[key].id+"'><label> "+_variantList[key].model_name+" - "+_variantList[key].model_no+"</label><br />";

                }
            }

            html+="<br /><fieldset><legend>Model</legend>"+variantHtml+"</fieldset><br />";
            html+="<fieldset><legend>Diverse</legend><input checked type='checkbox' id='aktivOption'><label> Aktiv</label>";
            html+="<br /><input type='checkbox' checked id='prisOption' ><label> Vis vejl. pris.</label>";
            html+="<br /><input type='checkbox' checked id='modelOption' ><label> Vis model liste</label></fieldset>";
        }
        else{



            option = option.substring(0);
            option = option.substring(-1);
            option =  jQuery.parseJSON(option);
              
            var variantListOption =  option.variantListOption.toString();
            var variantListOptionArr = variantListOption.split(",");
            for (var key in _variantList) {
                if(_variantList[key].language_id== "1"){
                        if(variantListOptionArr.indexOf(_variantList[key].model_id.toString()) != -1 ){
                           variantHtml+= "<input checked  class=\"variantListOption\" type='checkbox' value='"+_variantList[key].model_id+"' id='option"+_variantList[key].id+"'><label> "+_variantList[key].model_name+" - "+_variantList[key].model_no+"</label><br />";
                       } else {
                           variantHtml+= "<input class=\"variantListOption\" type='checkbox' value='"+_variantList[key].model_id+"' id='option"+_variantList[key].id+"'><label> "+_variantList[key].model_name+" - "+_variantList[key].model_no+"</label><br />";
                       }
                }
            }




            var aktivOptionIsChecked = "";
            var prisOptionIsCheced  = "";
            var modelOptionIsCheced = "";
            if(option.aktivOption == true){  aktivOptionIsChecked = "checked"    }
            if(option.prisOption == true){  prisOptionIsCheced  = "checked"   }
            if(option.modelOption == true){  modelOptionIsCheced  = "checked"   }




            if(variantHtml == ""){ variantHtml = "<p>Ingen elementer</p>"; }
            html+="<br /><fieldset><legend>Model</legend>"+variantHtml+"</fieldset><br />";
            html+="<fieldset><legend>Diverse</legend><input type='checkbox' id='aktivOption' "+aktivOptionIsChecked+"><label> Aktiv</label>";
            html+="<br /><input type='checkbox' id='prisOption' "+prisOptionIsCheced+"><label> Vis vejl. pris.</label>"
            html+="<br /><input type='checkbox' id='modelOption' "+modelOptionIsCheced+"><label> Vis model liste</label></fieldset>";
         }
        $( "#dialog-message" ).html(html);
        dialog =  $( "#dialog-message" ).dialog({
            title: 'Gave indstilling',
            autoOpen: true,
            height: 400,
            width: 400,
            modal: true,
            buttons: {
                "GEM": presentsOptions.update,
                Cancel: function() {
                dialog.dialog( "close" );
            }
        }
        });

   },
   update : function(){
        var variantListOption = [];
        $( ".variantListOption" ).each(function() {
            if($( this ).prop('checked')){
                variantListOption.push($( this ).val() )
            }

        });

        var formData = {
            'variantListOption':variantListOption,
            'prisOption':$( "#prisOption" ).prop('checked'),
            'aktivOption':$( "#aktivOption" ).prop('checked'),
            'modelOption':$( "#modelOption" ).prop('checked')
        }
        if($( "#aktivOption" ).prop('checked') ){
            $("#sortable").find("[data-shopPresentsId='"+_shopPresentID+"']").css("opacity","1.0");
        } else {
            $("#sortable").find("[data-shopPresentsId='"+_shopPresentID+"']").css("opacity","0.3");
        }

        var formData = { id:_shopPresentID, 'data':JSON.stringify(formData) }
        ajax(formData,"shop/setPresentProperties ","presentsOptions.updateResponse","");
   },
   updateResponse : function(response){
       dialog.dialog( "close" );
       if(response.status != "1" ){
           alert(response.message)
       }
   }
}
