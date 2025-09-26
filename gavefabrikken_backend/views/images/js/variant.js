var _tempVariantID;
var _modelToRemove;
var variant = {
    addNew : function(){
        var newVareNr = $("#variantNew > td:nth-child(3) > input" ).val();
        var error = 0;
         $(".prisentVariantVal").each(function () {
            if(newVareNr == this.value && error == 0){
                error = 1;
                alert("Varenummer er benyttet paa anden gave i sampak")
            }
         })
        if(error == 0){
            ajax({present_id:gaveAdmin.editId},"present/createNewModel","variant.addNewHtml");
        }

    },
    addNewHtml : function(response){
       // var sampak = "<hr><textarea onclick=\"regEventAction('none')\"   id='sampak_"+item.id+"' rows='4' cols='35'>"+item.sampak_items+"</textarea><br><button style='font-size:10px;' onclick='gaveAdmin.editItemnrInSampak(\""+item.id+"\")'>Opdatere varer i sampak</button>";
        var sampakIsAdd = false;
        var i = 1;
        var error = "";
        var html = '';
        $("#variantNew > td > input" ).each(function () {
                var lc  =  this.value.toLowerCase();

                if(i==3 && (lc.search("sam") > -1)){
                    html+= "<td ><input type='text' class='prisentVariantVal' value='"+this.value+"'><hr><textarea onclick=\"regEventAction('none')\"   id='sampak_"+response.data.model_id+"' rows='4' cols='35'></textarea><br><button style='font-size:10px;' onclick='gaveAdmin.editItemnrInSampak(\""+response.data.model_id+"\")'>Opdatere varer i sampak</button></td>";
                } else {
                    html+= "<td ><input type='text' class='prisentVariantVal' value='"+this.value+"'></td>";
                }


                this.value = "";
                i++;

        });
        var img = $( "#variantNew td:nth-child(4)" ).html()
        //$( "#variantNew td:nth-child(4)" ).html("")

        if(img.search("blank") == -1){
            $( "#variantNew td:nth-child(4)" ).html("<img class=\"variantImg prisentVariantVal\" src=\"views/media/type/blank.jpg\" />")
        } else {
           var randomVal =  randomString(5, 'bcdefghijklmnopqrstuv');
           img =  "<img class=\"variantImg prisentVariantVal\" width=50 src=\"views/media/type/blank.jpg?"+randomVal+"\" />"
        }



        if(error != ""){ alert(error) }
        else{
            htmlAction1 ="<td>"+img+"</td><td><img class=\"icon\" src='views/media/icon/1373253296_delete_64.png' title=\"Slet\" onclick='variant.deleteItem(this)' height='25' width='25'> <img class=\"icon\" src='views/media/icon/bill.png' title=\"V&oelig;lg billede\" onclick='variant.showUploadDialog(this)' height='30' width='30'> </td></tr>";
            htmlAction2 ="<td></td><td></td></tr>";

            $('#tabsVari-dk > table').append('<tr bgcolor="#9EBEF5" data-id="1" data-variantId="'+response.data.model_id+'">'+html+htmlAction1);
            $('#tabsVari-en > table').append('<tr bgcolor="#9EBEF5" data-id="2" data-variantId="'+response.data.model_id+'">'+html+htmlAction2);
            $('#tabsVari-de > table').append('<tr bgcolor="#9EBEF5" data-id="3" data-variantId="'+response.data.model_id+'">'+html+htmlAction2);
            $('#tabsVari-no > table').append('<tr bgcolor="#9EBEF5" data-id="4" data-variantId="'+response.data.model_id+'">'+html+htmlAction2);
            $('#tabsVari-sv > table').append('<tr bgcolor="#9EBEF5" data-id="5" data-variantId="'+response.data.model_id+'">'+html+htmlAction2);


        }
         $( "#variantNew td:nth-child(4)" ).html("<img src=\"views/media/type/blank.jpg\">")

      variant.updateNewItem();

    },
    updateNewItem : function()
    {
        var variant = [];
        var langId = "";
        var variantId = "";
        $(".presentsVariant").each(function( index ) {

                var lineIndex = 0;
                $('tr', $(this)).each(function (obj, index) {

                    if($(this).find('.prisentVariantVal').length != 0){

                       var feltData = [];
                       feltData.push ({'variantId':$(this).attr("data-variantId")} )
                       langId = $(this).attr("data-id");
                       var  i = 0;
                       $('input', $(this)).each(function (obj, index) {
                           if(i == 0) { feltData.push ({'variant':this.value} ) }
                           if(i == 1) { feltData.push ({'variantSub':this.value} ) }
                           if(i == 2) { feltData.push ({'variantNr':this.value} ) }
                           i++;
                       })
                       $('.variantImg', $(this)).each(function (obj, index) {
                            if(langId == 1){
                                   feltData.push ({'variantImg':this.src} )
                            } else {
                                   feltData.push ({'variantImg':"###"} )
                            }

                       })
                       $('.variantCheckbox', $(this)).each(function (obj, index) {
                            feltData.push ({'variantCheck':this.value} )
                       })
                       variant.push ( {'language_id':langId,'feltData':feltData,'sortOrder':lineIndex} )
                       lineIndex++;
                    }
                });
        });
        var formData = {
            'present_id':gaveAdmin.editId,
            'variant_list':JSON.stringify(variant)

        };
       // console.log(formData)

       ajax(formData,"present/updateModels","variant.updateNewItemResponse");
    },
    updateNewItemResponse : function(){



    },
    deleteItem : function(element){
        var r = confirm("Vil du slette gaven i sampak");
        if (r == true) {
            _modelToRemove =  element
            var model = $(element).parent().parent().attr("data-variantid");
            ajax({"bundleId":gaveAdmin.editId,"model":model},"present/deletePresentInBundle","variant.deleteItemReturn");
        }
    },
    deleteItemReturn : function(responce){
        var index = $(_modelToRemove).parent().parent().index( );
            langIndex = index - 1;
        $('#tabsVari-dk > table > tbody > tr' ).get(index).remove()
        $('#tabsVari-en > table > tbody > tr').get(langIndex).remove()
        $('#tabsVari-de > table > tbody > tr').get(langIndex).remove()
        $('#tabsVari-no > table > tbody > tr').get(langIndex).remove()
        $('#tabsVari-sv > table > tbody > tr').get(langIndex).remove()
    },

    showUploadDialog : function(id){

        var html = "  <iframe width=\"98%\" height=\"370\" frameborder=\"0\" scrolling=\"no\" src=\"views/uploadVariantImg_view.php\">"
                $("#logoDialog").html(html)
        _tempVariantID = id
        $( "#logoDialog" ).dialog({
            title: "Inds&oelig;t billede",
            resizable: true,
            width:700,
            height:500,
            open: function(){

            },
            buttons: {
                'Ok': function() {
                    $(this).dialog('close');
                }
            }
        } );

    },
    insertImg : function(id){

        if(_tempVariantID == "0"){
            $( "#variantNew td:nth-child(4)" ).html( "<img class=\"variantImg prisentVariantVal\" src='views/media/type/"+id+".jpg' width=50  />" );
            $("#logoDialog").dialog('close');
        } else {
            var parentObj = $(_tempVariantID).parent()
            var prevImgSrc =  $(parentObj ).prev().children().attr( 'src' )
            var imgHtml = "<img class=\"variantImg prisentVariantVal\" src='views/media/type/"+id+".jpg' width=50  />";
            $(parentObj ).prev().html( imgHtml );
            $("#logoDialog").dialog('close');

            var images = $('#presentVariantTabs').find('img').map(function() {
                   var element = this.src;
                   if(element.indexOf(prevImgSrc) > -1){
                        this.src = "views/media/type/"+id+".jpg";
                    }
                }).get();
        }



    }
}