var shopShopboard = {

    hasShop : function(){
        var formData = {shopID:_editShopID}

        $.ajax(
            {
                url: 'index.php?rt=shopboard/hasValgshop',
                type: 'POST',
                dataType: 'json',
                data: formData
            }).done(function(res) {
            if(res.status == 0) { alert("Der er sket en fejl") }
            else {
                let html = "";

                // Check hvis in_shopboard_date ikke er null
                if(res.data.in_shopboard_date != null && res.data.in_shopboard_date != '') {
                    html = "<div style='color:blue'>Shoppen er oprettet " + res.data.in_shopboard_date + "</div>";
                    html += "<button onclick='shopShopboard.removeValgshop(\""+_editShopID+"\")'>Fjern oprettelsesdato</button>";
                } else {
                    // Eksisterende logik hvis in_shopboard_date er null
                    html = "Shop ej godkendt<br><button onclick='shopShopboard.createValgshop(\""+_editShopID+"\")'>Opret valgshoppen under Shopboard</button><br>";
                    if(res.data.state == 1){
                        html = "<button onclick='shopShopboard.createValgshop(\""+_editShopID+"\")'>Opret valgshoppen under Shopboard</button>";
                    }
                    if(res.data.state == 2){
                        html = "<div style='color:blue'>Shoppen er oprettet i Shopboard</div>";
                    }
                    if(res.data.state == 4){
                        html = "<div style='color:blue'>Shoppen er EJ GODKENDT men oprettet i Shopboard</div><br><button onclick='shopShopboard.createValgshop(\""+_editShopID+"\")'>Opret valgshoppen under Shopboard</button><br>";
                    }
                    if(res.data.state == 5){
                        html = "<div style='color:blue'>Reservation EJ GODKENDT</div><br><button onclick='shopShopboard.createValgshop(\""+_editShopID+"\")'>Opret valgshoppen under Shopboard</button><br>";
                    }
                }
                $("#shopShopboard").html(html+"<br><br>");
            }
        })
    },
    // Tilføj denne nye funktion
    removeValgshop : function(shopID){
        // Tilføj confirm dialog
        if (!confirm("Er du sikker på at du vil fjerne oprettelsesdatoen?")) {
            return; // Afbryd hvis brugeren trykker Cancel
        }

        var formData = {shopID: shopID}

        $.ajax({
            url: 'index.php?rt=shopboard/removeValgshopDate',
            type: 'POST',
            dataType: 'json',
            data: formData
        }).done(function(res) {
            if(res.status == 0) {
                alert("Der er sket en fejl")
            } else {
                // Genindlæs shoppen
                shopShopboard.hasShop();
            }
        })
    },
    createValgshop : function (){
        $("#shopShopboard").html("systemet arbejder");

        var formData = {
            shopID:_editShopID,
            shop_navn:$("#shopName").val(),
            salgsordrenummer:$("#so_no").val(),
            salesPerson:$("#salesPerson").val(),
            valgshopansvarlig:$("#giftResponsible").val(),
            kontaktperson:$("#shopKontakt").val(),
            telefon:$("#shopTelefon").val(),
            mail:$("#shopEmail").val(),
            shop_aabner:$("#shopFrom2").val(),
            shop_lukker:$("#shopTo2").val(),
            info:" "

        }
        $.ajax(
            {
                url: 'index.php?rt=shopboard/crateValgshop',
                type: 'POST',
                dataType: 'json',
                data: formData
            }).done(function(res) {
            if(res.status == 0) { alert("Der er sket en fejl") }
            else {
                html = "<div style='color:blue'>Shoppen er oprettet i Shopboard</div>";
                $("#shopShopboard").html(html);
            }
        })
    },
    getsaleVaAndList: function (){
        let formData = {
            shopID:_editShopID
        }
        $.ajax(
            {
                url: 'index.php?rt=shopboard/allSaleVaAndList',
                type: 'POST',
                dataType: 'json',
                data: formData
            }).done(function(res) {
                console.log(res)
            if(res.status == 0) { alert("Der er sket en fejl") }
            else {


            }
        })
    }


}
$( document ).ready(function() {
  shopShopboard.hasShop()
  shopShopboard.getsaleVaAndList()
});