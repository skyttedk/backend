var _menuGaveCard,_menuValueCard,_menuDeadlineCard;

var cardAddNewCard = {


    showMedal:function(){

        $('.formGavekort').prop('selectedIndex',0);
        $('.formSelectValue').prop('selectedIndex',0);
        $('.formDeadline').prop('selectedIndex',0);
                $("#cardFormSaleperson").val("");
        $(".cardToSelect").val(0);
        $(".only24").hide();
        $(".onlyNorge").hide();
        $(".cardFormDeadline").hide();
        $(".cardFormNumber").hide();
        $(".cardAddNewCard").hide();
        $(".cardFormToSendMethod").hide();
        $(".home").hide();
      _menuValueCard = "";
      dialog =  $( "#dialog_message_AddNewCard" ).dialog({
            title: 'Gavekort',
            autoOpen: true,
            height: 550,
            width: 550,
            modal: true,
            buttons: {
                Cancel: function() {
                dialog.dialog( "close" );
            }
        }
        });
    },
    menuGaveCard:function(val){
        _menuValueCard = "";
        _menuGaveCard = val;

        $('.formSelectValue').prop('selectedIndex',0);
        $('.formDeadline').prop('selectedIndex',0);
        $(".home").hide();
        $(".only24").hide();
        $(".onlyDrom").hide();
        $(".cardFormDeadline").hide();

        $(".cardAddNewCard").hide();
        $(".onlyNorge").hide();
        if(val == "53"){
            $(".home").show();
        }

        if(val == "onlyDrom") {
           $(".onlyDrom").show();
            $(".home").show();
            cardAddNewCard.menuValueCard(_menuGaveCard);
        }


        if(val == "24Gaver"){
           $(".only24").show();
            $(".home").show();
            cardAddNewCard.menuValueCard(_menuGaveCard);
        }
        if(val == "norge"){
            $(".onlyNorge").show();
            cardAddNewCard.menuValueCard(_menuGaveCard);
        }

    },
    menuValueCard:function(val){
         _menuValueCard = val;

         $(".cardFormDeadline").show();
         $(".deadline_groupe").hide();
         if(val == "52" || val == "57" || val == "58" || val == "59" || val == "265" ){
                $(".deadline_groupe_1").show();
         }
         if(val == "54" || val == "55" || val == "56"  || val == "53" || val == "287" || val == "290" || val == "310"){
            $(".deadline_groupe_2").show();
         }





    },
    menuDeadlineCard:function(val){
        $(".cardFormNumber").show();
        $(".cardFormToSendMethod").show();

        $(".cardAddNewCard").show();
        _menuDeadlineCard  = val;
    },
    selectCardFromPool:function(){
        $(".cardAddNewCard").hide();
        var shop_id =_menuGaveCard;
        var cardVal = 0;
        var is_delivery = "0";

        if(shop_id == "52"){
            cardVal = "560";
        }
        if(shop_id == "265"){
            cardVal = "600";
        }
        if(shop_id == "53"){
            cardVal = "800";
        }


        if(shop_id != "52" && shop_id != "53" && shop_id != "265"  ){

            if(shop_id == "24Gaver"){
                shop_id = $(".formSelectValue1").val();
                if(shop_id == "54"){ cardVal = "400" }
                if(shop_id == "55"){ cardVal = "560"  }
                if(shop_id == "56"){ cardVal = "640" }
            }
            if(shop_id == "onlyNorge") {
                shop_id = $(".formSelectValue2").val();
                if(shop_id == "57"){ cardVal = "400" }
                if(shop_id == "58"){ cardVal = "600" }
                if(shop_id == "59"){ cardVal = "800" }
            }
            if(shop_id == "onlyDrom"){
                shop_id = $(".formSelectValue3").val();
                if(shop_id == "287"){ cardVal = "100" }
                if(shop_id == "290"){ cardVal = "200"  }
                if(shop_id == "310"){ cardVal = "300" }
            }
        }
        if(_menuDeadlineCard == "2019-01-01"){
            is_delivery = "1";
        }

        var spdeal = "";
        var spdealTxt = $("#sp").val();

        var allOkay = true;
        var  salgerperson = $("#cardFormSaleperson").val()+"#@#"+spdeal+"#@##@#"+spdealTxt+"#@#";


        var formData = {
            "quantity": $(".cardToSelect").val(),
            "expire_date": _menuDeadlineCard,
            "value":cardVal,
            "company_id": _selectedCompany,
            "shop_id": shop_id,
            "is_delivery":is_delivery,
            "is_email":$('input[name=cardFormToSendMethod]:checked').val(),
            "salesperson":salgerperson
        }

        ajax(formData,"company/addCompanyOrder","cardAddNewCard.selectCardFromPoolResponse","");
    },
    selectCardFromPoolResponse:function(response){
        dialog.dialog( "close" );
        if(response.status == "1"){
            alert("kort oprettet")
        } else {
           Alert("Der er sket en fejl.")
        }
    }


}