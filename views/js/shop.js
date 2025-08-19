var shop1 = {
    _editShopID:"",
    self: shop,
    load : function(obj){
                company.searchClose();
                var html ="<button class=\"button\" onclick=\"company.save('"+_editShopID+"')\" style=\"background-color: #4CAF50; color:white; font-size:14px; padding:5px;\" >Opdatere Valgshop</button>"
                $("#trailContainer").html(html)
                stamdata.load()
    },
    saveActiveShopStatus:function(){
           alert("skal gemmes")

    },
    showList : function(){
       // alert("asdfasdf")
    }



}




var stamdata = {
    test : "hej",
    self : stamdata,

    load : function(){
       // ajax({},"shop/show","company.editShow","html");

    },
    loadResponse : function(response){
        alert("sadf")
    }




}