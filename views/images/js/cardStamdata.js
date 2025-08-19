var cardStamdata ={

    showPluklist: function() {
        document.location = 'index.php?rt=cardshoppluk/dashboard'
    },

    showMedal:function()
    {

        $(".dialog1_name").val("")
        $(".dialog1_bill_to_address").val("")
        $(".dialog1_bill_to_address_2").val("")
        $(".dialog1_bill_to_postal_code").val("")
        $(".dialog1_bill_to_city").val("")
        $(".dialog1_cvr").val("")
        $(".dialog1_ean").val("")
        $(".dialog1_ship_to_company").val("")
        $(".dialog1_ship_to_attention").val("")
        $(".dialog1_ship_to_address").val("")
        $(".dialog1_ship_to_address_2").val("")
        $(".dialog1_ship_to_postal_code").val("")
        $(".dialog1_ship_to_city").val("")
        $(".dialog1_contact_name").val("")
        $(".dialog1_contact_phone").val("")
        $(".dialog1_contact_email").val("")

        dialog =  $( "#dialog_message_stamData" ).dialog({
            title: 'Opret nyt firma',
            autoOpen: true,
            height: 550,
            width: 550,
            modal: true,
            buttons: {
                "GEM": cardStamdata.create,
                Cancel: function() {
                    dialog.dialog( "close" );
                }
            }
        });
    },

    create:function()
    {
        var formData = {
            'name':$(".dialog1_name").val(),
            'bill_to_address':$(".dialog1_bill_to_address").val(),
            'bill_to_address_2':$(".dialog1_bill_to_address_2").val(),
            'bill_to_postal_code':$(".dialog1_bill_to_postal_code").val(),
            'bill_to_city':$(".dialog1_bill_to_city").val(),
            'cvr':$(".dialog1_cvr").val(),
            'ean':$(".dialog1_ean").val(),
            'ship_to_company':$(".dialog1_ship_to_company").val(),
            'ship_to_address':$(".dialog1_ship_to_address").val(),
            'ship_to_address_2':$(".dialog1_ship_to_address_2").val(),
            'ship_to_postal_code':$(".dialog1_ship_to_postal_code").val(),
            'ship_to_city':$(".dialog1_ship_to_city").val(),
            'contact_name':$(".dialog1_contact_name").val(),
            'contact_phone':$(".dialog1_contact_phone").val(),
            'contact_email':$(".dialog1_contact_email").val()
        };
        ajax({'companydata':formData},"company/createGiftCertificateCompany","cardStamdata.createResponse","");

    },
    createResponse:function(response){
        dialog.dialog( "close" );
        if(response.status == "1" ){
            alert("Virksomheden: "+response.data.result[0].name)
            var sogcvr= response.data.result[0].cvr;
            $(".sogCardShops").val(sogcvr);
            cardCompany.sog()
        } else {
            alert("Der er sket en fejl")
        }
    },
    show:function(company_id){
        ajax({'company_id':company_id},"company/readGiftCertificateCompany","cardStamdata.showResponse","");
    },
    showResponse:function(response){

    },
    edit:function(){

    },
    editResponse:function(response){

    },
    remove:function(company_id){
        if(confirm("Er du sikker paa du vil slette ?") ){
            ajax({'company_id':company_id},"company/deleteGiftCertificateCompany","cardStamdata.removeResponse","");
        }


        //alert("ikke muligt nu ")

    },
    removeResponse:function(response){
        if(response.status != "1" ){
            alert(response.message)
        } else {
            $("#cardsogList_"+_selectedCompany).hide();
            $("#currentSogContent").html("");
            alert("firma slettet")
        }
    },
    update:function(companyId){
        var formData = {
            'name':$(".dialog1_name_Show").val(),
            'bill_to_address':$(".dialog1_bill_to_address_Show").val(),
            'bill_to_address_2':$(".dialog1_bill_to_address_2_Show").val(),
            'bill_to_postal_code':$(".dialog1_bill_to_postal_code_Show").val(),
            'bill_to_city':$(".dialog1_bill_to_city_Show").val(),
            'cvr':$(".dialog1_cvr_Show").val(),
            'ean':$(".dialog1_ean_Show").val(),
            'ship_to_company':$(".dialog1_ship_to_company_Show").val(),
            'ship_to_address':$(".dialog1_ship_to_address_Show").val(),
            'ship_to_address_2':$(".dialog1_ship_to_address_2_Show").val(),
            'ship_to_postal_code':$(".dialog1_ship_to_postal_code_Show").val(),
            'ship_to_city':$(".dialog1_ship_to_city_Show").val(),
            'contact_name':$(".dialog1_contact_name_Show").val(),
            'contact_phone':$(".dialog1_contact_phone_Show").val(),
            'contact_email':$(".dialog1_contact_email_Show").val()
        };
        ajax({'companydata':formData,'company_id':companyId},"company/updateGiftCertificateCompany","cardStamdata.updateResponse","");

    },
    updateResponse:function(response){
        if(response.status == "1" ){
            alert("Felter opdateret")
            var sogcvr=  $(".sogCardShops").val();
            cardCompany.goToParentSog()
        //    ajax({'text':sogcvr},"company/searchGiftCertificateCompany","cardStamdata.updateData","");
        } else {
            alert("Der er sket en fejl")
        }
    },
    updateData:function(response){
        _cardSogData = response;
        var html = "<br />";
        for (var key in response.data.result) {
            html+= "<div class=\"cardsogList\" id=\"cardsogList_"+response.data.result[key].id+"\" onclick=\"cardCompany.sogShowCardCompany('"+response.data.result[key].id+"','"+response.data.result[key].name+"')\">";
            html+= "<div><label style=\"font-weight: bold\">"+response.data.result[key].name+" -</label><label> "+response.data.result[key].cvr+"</label><br /><label style=\"font-size:11px;\"> "+response.data.result[key].ship_to_address+" - </label><label style=\"font-size:11px;\"> "+response.data.result[key].ship_to_city+"</label></div></div>";
        }
        $("#currentSogList").html("<div id=\"currentSogListContainer\">"+html+"</div>")
        $("#currentSogList").hide();




    }


}