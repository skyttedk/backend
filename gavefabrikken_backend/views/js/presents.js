    // data,callUrl,returnCall,returnElement
var _unik = false;
var _unikUpdata = false;
var _shopEdit = false
var _presentInShop_Id;
var _presentInShop_shopPresentId;
var _tempId;

var unikPresentInShop = {
     show : function(element){
            _localRouteToShopsOwnGift = true;
            _presentInShop_Id       = $(element).parent().parent().attr( "data-id" );
            _presentInShop_shopPresentId   = $(element).parent().parent().attr( "data-shopPresentsId" );

            if( $(element).parent().parent().attr( "data-unik" ) == 0){
                if (confirm("Vil du oprette gaven, som en unik gave") == true) {
                    _unik = true;
                    _unikUpdata = false;
                    gaveAdmin.editGiftUnik(_presentInShop_Id )
                }
            } else {
               _unik = false;
               _unikUpdata = true;
               gaveAdmin.editGiftUnik(_presentInShop_Id )
            }

    }
}


function updateSelectedPresentsIndex()
{
      var postData = [];
      var i = 0;
     $( "#sortable li" ).each(function( index ) {
       // console.log( index + ": " + $(this).attr( "data-shopPresentsId" ) );
        var id = $(this).attr( "data-shopPresentsId" );
        postData.push( {'id':id,'index':i} )
        i++;

      });
      var formData = {'data':JSON.stringify(postData)}

      ajax(formData ,"shop/setShopPresentIndexes","updateSelectedPresentsIndex_response");
}
function updateSelectedPresentsIndex_response(response){
    alert("r�kkef�lge opdateret")
}



function addToMedia(path){
    var html = ""
    for(var i=0;path.length > i;i++){

        var imgUrl = path[i].media_path

        if(imgUrl.indexOf("video") != -1)
        {
           imgUrl =  "Film-48";
        }

        html+= '<li class="ui-state-default"><div class="sort-img presentAdminImg" data-id="'+path[i].media_path+'" style="background-image: url(views/media/user/'+imgUrl+'.jpg);"> <img style="position: relative;  left: 45px; top: -60px;" class="icon" src="views/media/icon/1373253296_delete_64.png"  onclick="removePresentImgFromList(this)" height="25" width="25"></div></li>';
    }




    $("#sortable").append(html);
    $( "#sortable" ).sortable();
    $( "#sortable" ).disableSelection();
}




var shop = {
    showList : function()
    {

        setEnterFocus('')
        bizType.trail('valgshops');
        ajax({},"shop","","#content");



    },
    createNew : function()
    {

    }

}


var presentFront =  {

    preview:function(imgId,id,varerNr,varerNavn)  {
        let shopID = 3543;
        if(_localisation == 4){
            shopID = 4594;
        }
        ajax({"shop_id":shopID,"present_id":id},"shop/previewPresent","presentFront.previewInitReturn");
    },
    previewInitReturn:function()  {
           //var url= "../gavevalg/preview";
        var url = "https://findgaven.dk/gavevalg/preview"
        if(_localisation == 4){
            url = "https://findgaven.dk/gavevalg/preview_no"
        }

           $("#previewPresent").html('<iframe src="'+url+'" width="98%" height="98%" frameBorder="0">Browser not compatible.</iframe>')
             var w = $(window).width()*0.9;
             var h = $(window).height()*0.9;
             $( "#previewPresent" ).dialog({
                 resizable: true,
                width:w,
                height:h
             } );

    },

    sogPresentOption : "",
    sogtxt : "",
    sogJave : function(){
       $(".flip-container").hide();

       var  sogtext = $("#searchPresentHeadview").val();
       sogtext = sogtext.toLowerCase()
       if(sogtext == ""){
         $(".flip-container").show();
       } else {
            $(".flip-container").each(function( data ) {
                var element  = $( this ).html();
                element = element.toLowerCase()
                if(element.search(sogtext) > -1){
                    $( this ).show();
                }
          });
       }
    },

    undoDelete:function(id){
       if(confirm('Vil du genoprette gaven') ){
            //$(".flip-container").hide();
            $("div[data-id="+id+"]").parent().hide()
            ajax({"id":id},"present/undoDelete","presentFront.undoDeleteResponce");
        }
    },

    undoDeleteResponce:function(){
        alert("Gaven er genoprettet")
    },

    sog : function(){
        $("#searchPresentMsg").show()
        var functionToCall =  $('input[name=presenttypesearch]:checked').val();
        var  sogtext = $("#searchPresentHeadview").val();
        sogtext = sogtext.toLowerCase()
        this.sogtxt = sogtext;

        var postData = {"search":sogtext};
        this.sogPresentOption = functionToCall;

        if(functionToCall == "searchPresents" ){
            ajax(postData,"present/"+functionToCall,"presentFront.sogResponseStandard","html");
        } else {
            ajax(postData,"present/"+functionToCall,"presentFront.sogResponseVariant","html");
        }


    },
    sogDeleted : function(){
        $("#searchPresentMsg").show()
        var functionToCall =  $('input[name=presenttypesearch]:checked').val();
        var  sogtext = $("#searchPresentHeadview").val();
        sogtext = sogtext.toLowerCase()
        this.sogtxt = sogtext;

        var postData = {"search":sogtext};
        this.sogPresentOption = functionToCall;
        functionToCall = functionToCall+"Deleted";
        if(functionToCall == "searchPresentsDeleted" ){
            ajax(postData,"present/"+functionToCall,"presentFront.sogResponseStandard","html");
        } else {
            ajax(postData,"present/"+functionToCall,"presentFront.sogResponseVariant","html");
        }
    },
    sogResponseStandard : function(response)
    {

        if(_shopMode == true){
            $("#shopPresentSelect").html(response);
            $(".noShop").hide();
            $(".Shop").show();
            console.log("1")
        } else {
            console.log("2")
            $("#content").html(response);
            $(".Shop").hide();
            $(".noShop").show();
        }
         $("#searchPresentHeadview").val(this.sogtxt)

    },
    sogResponseVariant : function(response)
    {

        if(_shopMode == true){
            $("#shopPresentSelect").html(response);
            $(".noShop").hide();
            $(".Shop").show();

        } else {
            $("#content").html(response);
            $(".Shop").hide();
            $(".noShop").show();
        }
         $("#searchPresentHeadview").val(this.sogtxt)
        this.sogJave();
    },

    /*
    setInterKeyEvent: function(action){
        _enterFocus = "event_AdminGaverSog";
    },
     */



    sogAll : function(){
      ajax({},"present/readAll","presentFront.sogAllResponse","html");
    },
    sogAllResponse : function(response){
        if(_shopMode == true){
            $("#shopPresentSelect").html(response);
            $(".noShop").hide();
            $(".Shop").show();
 
        } else {
            $("#content").html(response);
            $(".Shop").hide();
            $(".noShop").show();
        }
    },


    showDetalDescription : function(id){
        $( "#logoDialog" ).html( $(".long_description_"+id).html() )
        $( "#logoDialog" ).dialog({
         resizable: true,
        width:700,
        height:400
        } );
    },
    deleteItem : function(id){
        if(confirm('Vil du at slette gaven') ){
            //$(".flip-container").hide();
            $("div[data-id="+id+"]").parent().hide()
            ajax({"id":id},"present/delete","presentFront.deleteItemResponce");
        }
    },
    deleteItemResponce : function()
    {

            //$(".flip-container").fadeOut(500);
            alert("gaven er slettet")
           // gaveAdmin.show()

    },

    showGiftUsedInShops : function(id)
    {

        ajax({"id":id},"present/getAllVariants","presentFront.showGiftUsedInShopsResponse");

    },
    showGiftUsedInShopsResponse : function(response){

        var html = "";
        var seletedPresentInShopsHtml = "";
        var otherRelatedeToPresentsHtml = "";
        var is_caller = false;


            var tempHtml = "";
            tempHtml+= "<tr  bgcolor=\"yellow\"><td>Gaves navn:</td><td>"+response.data.master.name+"</td><td></td></tr>";
            $.each(response.data.master.shops, function(i, item) {

                tempHtml+= "<tr><td width=100>Shop navn</td><td></td><td>"+item.name+"</td></tr>";
            });
            response.data.master.is_caller == 1 ? seletedPresentInShopsHtml+= tempHtml : otherRelatedeToPresentsHtml+= tempHtml;

            tempHtml = "";
            $.each(response.data.master_shopvariants, function(i, item) {
                tempHtml = "";
                tempHtml+= "<tr  bgcolor=\"yellow\"><td width=100>Gaves navn:</td><td>"+item.name+"</td><td></td></tr>";
                $.each(item.shops, function(i, item) {

                    tempHtml+= "<tr><td>Shop navn</td><td></td><td>"+item.name+"</td></tr>";
                });
                response.data.master_shopvariants.is_caller == 1 ? seletedPresentInShopsHtml+= tempHtml : otherRelatedeToPresentsHtml+= tempHtml;
            })


            tempHtml = "";
            $.each(response.data.variants, function(i, item) {
                tempHtml = "";
                tempHtml+= "<tr  bgcolor=\"yellow\"><td width=100>Gaves navn:</td><td>"+item.name+"</td><td></td></tr>";
                $.each(item.shops, function(i, item) {

                    tempHtml+= "<tr><td>Shop navn</td><td></td><td>"+item.name+"</td></tr>";
                });
                response.data.variants.is_caller == 1 ? seletedPresentInShopsHtml+= tempHtml : otherRelatedeToPresentsHtml+= tempHtml;
            })

            tempHtml = "";

            $.each(response.data.variants_shopvariants, function(i, item) {
                tempHtml = "";
                tempHtml+= "<tr  bgcolor=\"yellow\"><td width=100>Gaves navn:</td><td>"+item.name+"</td><td></td></tr>";
                $.each(item.shops, function(i, item2) {
                        tempHtml+= "<tr><td>Shop navn</td><td></td><td>"+item2.name+"</td></tr>";
                });
                response.data.variants_shopvariants.is_caller == 1 ? seletedPresentInShopsHtml+= tempHtml : otherRelatedeToPresentsHtml+= tempHtml;
            })

            /*
             tempHtml = "";
            tempHtml+= "<tr><td>Gaves navn:</td><td>"+response.data.master.name+"</td></tr>";
            $.each(response.data.variants.shops, function(i, item) {
                console.log(item);
                tempHtml+= "<tr><td>Shop navn</td><td>"+item.name+"</td></tr>";
            });
            response.data.variants.is_caller == 1 ? seletedPresentInShopsHtml+= tempHtml : otherRelatedeToPresentsHtml+= tempHtml;


             tempHtml = "";
            tempHtml+= "<tr><td>Gaves navn:</td><td>"+response.data.master.name+"</td></tr>";
            $.each(response.data.variants_shopvariantes.shops, function(i, item) {
                console.log(item);
                tempHtml+= "<tr><td>Shop navn</td><td>"+item.name+"</td></tr>";
            });
            response.data.variants_shopvariantes.is_caller == 1 ? seletedPresentInShopsHtml+= tempHtml : otherRelatedeToPresentsHtml+= tempHtml;
             */

        html+= "<div style=\"height:475px; width:100%; overflow: scroll; \"><h3>Shop som bliver p&aring;virket ved &oelig;ndringer</h3><br /><table border=1 width=95%>"+seletedPresentInShopsHtml+"</table><br /><br />";
        html+= "<h3>Andre shops</h3><br /><table border=1 width=95%>"+otherRelatedeToPresentsHtml+"</table></div>";
        showModal(html, "Gaveoversigt",600,600)



    }
}







