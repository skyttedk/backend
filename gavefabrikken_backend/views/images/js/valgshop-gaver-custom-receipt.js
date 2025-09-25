var classValgshopcustomReceipt = (function ()
  {
    var _this = this;
    _this.selectedId;
    _this.standardId;
    _this.presentId;
    _this.init = function (id,standardId,presentId) {
        $.post("index.php?rt=receipt/getStandartText", {}, function(returData, status){
            _this.presentId  = presentId;
            _this.selectedId = id;
            _this.standardId = standardId;
            _this.buildMedal(returData);
        })
    }
    _this.buildMedal = function(data) {
           var accordionHtml = "<div id='receiptAccordion'>";
           var receiptToUseHtml = "<select  id='standartIdSelect'><option value='0'>Ingen tekst</option>"
           data =  JSON.parse(data);
           $.each(data.data, function(key, value){
                accordionHtml+="<h3>"+value.attributes.title+"</h3>";
                accordionHtml+="<div><p>"+value.attributes.da+"</p></div>";
                receiptToUseHtml+="<option value='"+value.attributes.id+"'>"+value.attributes.title+"</option>";
           })
           accordionHtml+="</div>";
           receiptToUseHtml+="</select>";
           $("#dialog-custom-receipt-right").html(receiptToUseHtml);
           $("#dialog-custom-receipt-left").html(accordionHtml);




           $( "#receiptAccordion" ).accordion({
              heightStyle: "content"
           });
           $( "#dialog-custom-receipt" ).dialog({
           height: 600,
      width: 700,
      modal: true,
      buttons: {
        "GEM": function() {
          $( this ).dialog( "close" );
          _this.update();
        },
        Cancel: function() {
          $( this ).dialog( "close" );
        }
      }
           });
           $('#standartIdSelect option[value='+_this.standardId+']').attr('selected','selected');

    }
    _this.update = function(){
        var textId =    $( "#standartIdSelect option:selected" ).val();
        $.post("index.php?rt=receipt/updateStandartText", {msg1:textId,id:_this.selectedId}, function(returData, status){
                var html = "<table width=100% class='vsg'>";
                    $.post("index.php?rt=present/getModelsV3", {"present_id": _this.presentId, "shop_id":_shopId}, function(returData, status){
                        var returData = JSON.parse(returData);

                        $.each(returData.data, function(key, value){
                            if(value.attributes.active == 1){ // det er omvendet 1 betyder deaktiveret
                                html+="<tr><td width=45% rowspan='2'>"+value.attributes.model_name+"</td><td width=30% rowspan='2'>"+value.attributes.model_no+"</td><td width=20% rowspan='2'>"+value.attributes.model_present_no+"</td><td width=5% > <input  onclick=\"valgshopGaver.updatePresentState('"+value.attributes.id+"')\"  type=\"checkbox\" id=\"vsg-sampak-present_"+value.attributes.id+"\" /></td></tr>";
                                html+="<tr><td><img onclick=\"alert('hej')\"  width='25' height='25' src='views/media/icon/PurchaseNoOrder-50.png' title='Ingen kvittering, ej valgt gave' /></td></tr>";
                            } else {
                                html+="<tr><td width=45% rowspan='2'>"+value.attributes.model_name+"</td><td width=30% rowspan='2'>"+value.attributes.model_no+"</td><td width=20% rowspan='2'>"+value.attributes.model_present_no+"</td><td width=5% > <input  onclick=\"valgshopGaver.updatePresentState('"+value.attributes.id+"')\" checked type=\"checkbox\" id=\"vsg-sampak-present_"+value.attributes.id+"\" /></td></tr>";
                                if(value.attributes.msg1 == 0){
                                    html+="<tr><td><img style='cursor:pointer' onclick=\"customReceipt.init('"+value.attributes.id+"','"+value.attributes.msg1+"','"+_this.presentId+"')\"  width='25' height='25' src='views/media/icon/PurchaseNoOrder-50.png' title='' /></td></tr>";
                                } else {
                                    html+="<tr><td><img style='cursor:pointer' onclick=\"customReceipt.init('"+value.attributes.id+"','"+value.attributes.msg1+"','"+_this.presentId+"')\"  width='25' height='25' src='views/media/icon/Purchase Order-50.png' title='' /></td></tr>";
                                }
                            }

                     });
                     html+="</table>";

                     $(".vsg-model-container_"+_this.presentId).html(html)
                    })
        })


    }


})