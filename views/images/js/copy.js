var copy = {
    searchCopy : function(giftID)
    {
        if(_editShopID == ""){
            ajax({"id":giftID,"mode":"copy"},"present/readVariants","","#content");
        } else {
//            ajax({"id":giftID,"mode":"copy"},"present/readVariants","scale","html");
            ajax({"id":giftID,"mode":"copy"},"present/readVariants","copy.showCopyShopMode","html");
        }
    },

    showCopyShopMode : function(data)
    {

               $("#logoDialog").html(data)
               $("#logoDialog").css("background-color","#0076C9")
               $(".Shop").show()

               $( "#selectPresent .ui-state-default" ).each(function( index ) {
                var usedId =  $(this).attr("data-id");
                   $("#"+usedId).hide();
               })
               $(".searchPresentContainer").hide();
               var antal = 0;
               var skjult = 0;
               $( "#logoDialog .flip-container" ).each(function( index ) {
                      antal++;
                    if ( $(this).css('display') == "none") {
                       skjult++;
                   }
               })
               if(antal == skjult){
                 $("#logoDialog").html("<p style=\"color:white;\">Alle er valgt</p>");
               }



    $( "#logoDialog" ).dialog({
            title: "Gave varianter",
            resizable: true,
            width:950,
            height:600,

            buttons: {
                'Ok': function() {
                    $(".searchPresentContainer").show();
                    $("#gaveAdminBack").hide()
                    $(this).dialog('close');
                }
            },
            close: function( event, ui ) {
                $(".searchPresentContainer").show();
                $("#gaveAdminBack").hide()
            }
        } );


     /*
      $("#shopPresentSelect").html(data);
         if(flag == ""){
            $("#gaveAdminBack").hide()
            $("#gaveAdminBackShop").hide()
            flag = "sat";
        }  else {
            $("#gaveAdminBack").hide()
            $("#gaveAdminBackShop").show()
            //$(".Shop").show();
        }
        */
    },
    makeNewCopy : function(giftID,giftName)
    {
        if(confirm('Vil du at oprettet en kopi af gave: '+giftName)){
            ajax({"id":giftID},"present/createVariant","copy.makeNewCopyResponce");
        }
    },
    makeNewCopyResponce : function(data)
    {
        gaveEditData_ = data;
        ajax({},"presentAdmin/showNew","gaveAdmin.editInsertData","html");


    }




}




