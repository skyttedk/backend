var qr = {
    presentsHtml:"",






    init:function(){
        qr.HasNotPickedUpPresents();
     },
     HasNotPickedUpPresents:function(){
            ajax({"shop_id":_editShopID},"shop/getToUsersHowHasNotPickedUpPresents","qr.HasNotPickedUpPresentsResponse","");
     },
     HasNotPickedUpPresentsResponse:function(response){
           if(response.status=="1"){     alert("asdf");
                  var tempHtml = "";
        this.presentsHtml = "<center><table border=0 >";
        for(var i=0;response.data.length >i;i++){
            var modelJson = $.parseJSON(response.data[i].variant_list)
              console.log(modelJson)
            if(modelJson.length > 0){
                tempHtml = "";
                gaveId = response.data[i].id;
                gaveNavn =  response.data[i].name;
                tempHtml = "";

               $.each(modelJson, function(i, item) {
                    if(item.language_id == "1"){
                        tempHtml+="<tr><td height=30 width=200>"+gaveNavn+"</td><td width=200>"+item.feltData[0].variant+"</td><td width=200>"+item.feltData[1].variantSub+"</td><td><button onclick=\"gavevalg.doChangeGift('"+gaveId+"','"+item.feltData[0].variant+"','"+item.feltData[1].variantSub+"','"+item.feltData[2].variantNr+"') \">Vælg</button></td></tr>";
                     }
                })
                this.presentsHtml+=tempHtml ;
            } else {
                this.presentsHtml+= "<tr><td height=30>"+response.data[i].name+"</td><td></td><td></td><td><button onclick=\"gavevalg.doChangeGift('"+response.data[i].id+"','','','' ) \">Vælg</button></td></tr>";
            }

        }
         this.presentsHtml+="</table></center>"
         $( "#gaveQRContainer").html(this.presentsHtml)


           } else {

           }
     }


}