var myDropzone;
var _tempLogoFilename = "";
var _tempDropHtml = "";
var logo = {
    timer:"",
    create : function(){
        console.log(_tempLogoFilename)


        var path = 'logo/'+_tempLogoFilename+'.jpg';
//        var send = new system_({path:path,type:"2",description:$("#logoCreateText").val()},"media/create","logo.createDone");
//        send.ajax(send)
        ajax({path:path,type:"2",description:$("#logoCreateText").val()},"media/create","logo.createDone");


    },
    createDone : function(responce){
        console.log(responce)
        var html = '<div  style="background-image: url(views/media/logo/'+_tempLogoFilename+'.jpg);" data-id="logo/'+_tempLogoFilename+'.jpg" class="logo-img" ></div>';
        $("#selectedLogo").html(html);
        $("#logoCreateText").val("");
       // myDropzone.destroy()
      // myDropzone = new Dropzone(".dropzone", { url: "index.php?rt=upload/logo"});
    },
    showLogoList : function(reponseObj){

        var html = "";

       // console.log(reponseObj)
         html ="<div id=\"editPresentlogoList\"><table width=100% border=1>"
        $.each( reponseObj.data, function( key, value ) {
            html+= '<tr class="logoListdia" data-id="'+value.description.toLowerCase()+'"><td><img src="views/media/'+value.path+'" width=100   alt="" /></td><td>'+value.description+'</td><td><img   width=30 src="views/media/icon/1373253284_save_64.png"  onclick="logo.addToPresent(\''+value.path+'\',\''+value.element_size+'\')"  /></td></tr>'

      //      html+= '<tr data-id="'+value.description+'" data-url="'+value.path+'"><td> <img src="views/media/'+value.path+'" width=50   alt="" /> </td><td>"'+value.description+'"</td><td><img  id='logo-admin-img_"+responce.data.media[0].id+"' class=\"log-admin-image\" width=100 src='views/media/1373253494_plus_64.png'  /> </td></tr>'

        });
        html+="</table></div>";
        $("#editPresentlogoListContainer").html(html);
            $('#editPresentSogLogo').on('input',function(e){
           logo.searchLogoList();
        });


    //      onclick="logo.addToPresent(this)"



        /*
        $( "#logoDialog_logo" ).dialog({
            resizable: true,
             width:700,
             height:400
          } );
          */
    },
    addToPresent : function (url,logoSize){

        $(".logoListdia").css("border","0px");
        //var bg = $(element).attr("data-url");
        //$(element).css("border","3px solid red");

             var s1 = s2 = s3 = s4 = "";
                    if(logoSize == "1"){ s1 = "selected"  }
                    if(logoSize == "2"){ s2 = "selected"  }
                    if(logoSize == "3"){ s3 = "selected"  }
                    if(logoSize == "4"){ s4 = "selected"  }
         var html ="<select class=\"log-admin-size\" style=\"display:none;\"> <option value='1' "+s1+"  >Lille</option>  <option value='2'  "+s2+">Medium</option>  <option value='3' "+s3+">Stor</option>  <option value='4' "+s4+">St&oslash;rst</option></select>"
         html+= '<div class="logo-img aaaa" style="background-image: url(views/media/'+url+');" data-id="'+url+'" logoSize="'+logoSize+'" > </div><br />';
        $("#selectedLogo").html(html);


    },
    searchLogoList : function() {
        var ordSog = $("#editPresentSogLogo").val();

         ordSog = ordSog.toLowerCase();
        if(ordSog != ""){
        $( ".logoListdia" ).each(function( index ) {
            $(this).show();
            str = $(this).attr("data-id");
            if( str.search(ordSog) == -1){
              $(this).hide();
            }
        });
       } else {
          $( ".logoListdia" ).each(function( index ) {
            $(this).show();
           });
       }
    },
    // Admin delen

    loadAdminAll : function(){

          ajax({},"media/readAll","logo.showAdminList","");


    },
    showAdminList : function(responce) {

        var html = "<table id=\"logoAdminList\" border=1 width=100% height=100%>";
         $.each( responce.data.medias, function( key, value ) {
                    var s1 = s2 = s3 = s4 = "";
                    if(value.element_size == "1"){ s1 = "selected"  }
                    if(value.element_size == "2"){ s2 = "selected"  }
                    if(value.element_size == "3"){ s3 = "selected"  }
                    if(value.element_size == "4"){ s4 = "selected"  }

            html+="<tr  class='logoListAdmindia' data-id='"+value.id+"' search-word='"+value.description.toLowerCase()+"'  data-url='"+value.path+"'><td width=130><img id='logo-admin-img_"+value.id+"'  class=\"log-admin-image\" width=100 src='views/media/"+value.path+"'/></td><td ><input class=\"log-admin-description\" style=\"width:100%\" type='text' value='"+value.description+"' /> </td><td width=80 style=\"display:none;\"><select class=\"log-admin-size\" style=\"display:none;\"> <option value='1' "+s1+"  >Lille</option>  <option value='2'  "+s2+">Medium</option>  <option value='3' "+s3+">Stor</option>  <option value='4' "+s4+">St&oslash;rst</option></select></td><td width=120><img class=\"logo-save\" style=\"margin-right:10px; margin-left:10px; cursor:pointer\" src='views/media/icon/1373253284_save_64.png' width=25 /> <img class=\"logo-edit\" style=\"margin-right:10px; cursor:pointer\" width=25 src='views/media/icon/1373253282_pencil_64.png'  /><img class=\"logo-delete\" style=\" cursor:pointer\" src='views/media/icon/1373253292_trash_64.png' width=25 /></td></tr>";
        });
        html+= "</table>";

       $("#logoAdminBoxContentLeft").html(html)
       $("#presentAdminContainer").hide();
       $("#logoAdminBox").animate({
            width: "toggle"
        });
        try {
            myDropzone.destroy();
        }
        catch(err) {

        }
        myDropzone = new Dropzone(".dropzone", { url: "index.php?rt=upload/logo"});

        $(".logo-save").click(function(){ logo.saveLogoAdmin(this)  })
        $(".logo-edit").click(function(){ logo.editLogoAdmin(this)  })
        $(".logo-delete").click(function(){ logo.deleteLogoAdmin(this)  })

        $('#sogLogo').on('input',function(e){
           logo.searchLogoAdminList();
        });
         $("#logoAdminBoxContentLeft").height($(window).height()-300)
        $( window ).resize(function() {
           $("#logoAdminBoxContentLeft").height($(window).height()-300)
        })
    },
    saveLogoAdmin : function( obj ){
        var description =  $(obj).parent().parent().find(".log-admin-description").val();
        var element_size = $(obj).parent().parent().find(".log-admin-size").val();
        var id = $(obj).parent().parent().attr("data-id");
        ajax({id:id,description:description,element_size:element_size},"media/update","logo.saveLogoAdminResponce");

    },
    saveLogoAdminResponce:function(){
        logo.logoadminMsg("Gemt");
    },
    editLogoAdmin : function( obj ){
        var id = $(obj).parent().parent().attr("data-id");
        _tempDropHtml = $("#dropzoneCreateContainer").html();
        $("#dropzoneCreate").remove();

        var html =" <form action=\"upload.php\"  data-id=\""+id+"\"  id=\"dropzoneEdit\" class=\"dropzone\"></form></br></br> <button onclick=\"logo.updateLogoImg()\">Benyt og gem logo</button> <br />  <br />"
        $( "#logoAdmin" ).html(html)
         myDropzone.destroy()
         myDropzone = new Dropzone(".dropzone", { url: "index.php?rt=upload/logo"});


           $( "#logoAdmin" ).dialog({
            create: function( event, ui ) {

            },
            close: function( event, ui ) {

                $("#dropzoneEdit").remove();
                $("#dropzoneCreateContainer").html(_tempDropHtml);
                 myDropzone.destroy()
                 myDropzone = new Dropzone(".dropzone", { url: "index.php?rt=upload/logo"});
            },
            resizable: true,
             width:700,
             height:400
          } );


    },
    updateLogoImg : function () {
       var id =  $("#dropzoneEdit").attr("data-id");
       var path = 'logo/'+_tempLogoFilename+'.jpg';
       $("#logo-admin-img_"+id).attr("src","views/media/logo/"+_tempLogoFilename+".jpg");
       ajax({path:path,id:id},"media/update","logo.updateLogoImgResponce");
    },
    updateLogoImgResponce : function(){
         $("#logoAdmin").dialog( "close" );
        logo.logoadminMsg("Logo billede opdateret");

    },

    deleteLogoAdmin : function( obj ){
        var description =  $(obj).parent().parent().find(".log-admin-description").val();
        if(confirm("Vil du slette logo: "+description )){
            var id = $(obj).parent().parent().attr("data-id");
             $(obj).parent().parent().remove();
            ajax({id:id},"media/delete","logo.deleteLogoAdminResponce");
        }


    },
    deleteLogoAdminResponce : function(){
        logo.logoadminMsg("Logo slettet");
    },



    showLogoAdmin : function(){

        _dropTarget = "";
        logo.loadAdminAll();

    },
    hideLogoAdmin : function(){
         $("#logoAdminBox").animate({
            width: "toggle"
        });

   },
       createAdmin : function(){
        var path = 'logo/'+_tempLogoFilename+'.jpg';
        if(_tempLogoFilename == ""){
            alert( "Billede mangler" )
        }

       ajax({path:path,type:"2",description:$("#logoAdminSearchWords").val(),element_size:$("#elementSize").val()},"media/create","logo.createAdminResponse");


    },
    createAdminResponse : function(responce){
              var s1 = s2 = s3 = s4 = "";
                    if(responce.data.media[0].element_size == "1"){ s1 = "selected"  }
                    if(responce.data.media[0].element_size == "2"){ s2 = "selected"  }
                    if(responce.data.media[0].element_size == "3"){ s3 = "selected"  }
                    if(responce.data.media[0].element_size == "4"){ s4 = "selected"  }

        var html ="<tr  class='logoListAdmindia' data-id='"+responce.data.media[0].id+"' search-word='"+responce.data.media[0].description.toLowerCase()+"'  data-url='"+responce.data.media[0].path+"'><td width=130><img  id='logo-admin-img_"+responce.data.media[0].id+"' class=\"log-admin-image\" width=100 src='views/media/"+responce.data.media[0].path+"'/></td><td ><input class=\"log-admin-description\" style=\"width:100%\" type='text' value='"+responce.data.media[0].description+"' /> </td><td width=80><select class=\"log-admin-size\" style=\"display:none;\"> <option value='1' "+s1+"  >Lille</option>  <option value='2'  "+s2+">Medium</option>  <option value='3' "+s3+">Stor</option>  <option value='4' "+s4+">St&oslash;rst</option></select></td><td width=120><img class=\"logo-save\" style=\"margin-right:10px; margin-left:10px; cursor:pointer\" src='views/media/icon/1373253284_save_64.png' width=25 /> <img class=\"logo-edit\" style=\"margin-right:10px; cursor:pointer\" width=25 src='views/media/icon/1373253282_pencil_64.png'  /><img class=\"logo-delete\" style=\" cursor:pointer\" src='views/media/icon/1373253292_trash_64.png' width=25 /></td></tr>";

        $("#logoAdminList").prepend(html);
        _tempLogoFilename = "";
        myDropzone.destroy()
        myDropzone = new Dropzone(".dropzone", { url: "index.php?rt=upload/logo"});
        $("#logoAdminSearchWords").val("")

    },
   searchLogoAdminList : function() {
        var ordSog = $("#sogLogo").val();
        ordSog = ordSog.toLowerCase();
        if(ordSog != ""){
        $( ".logoListAdmindia" ).each(function( index ) {
            $(this).show();
            str = $(this).attr("search-word");
            if( str.search(ordSog) == -1){
              $(this).hide();
            }
        });
       } else {
          $( ".logoListAdmindia" ).each(function( index ) {
            $(this).show();
           });
       }
    },
    logoadminMsg : function(txt){
        clearTimeout(logo.timer);
        $("#logAdmin-msg").html(txt).fadeIn();
        logo.timer = setTimeout(function(){ $("#logAdmin-msg").fadeOut(); }, 3000);
    }


}

function controlDropElemet(activeElementName)
{

    var obj = JSON.parse(activeElementName);
    _tempLogoFilename = obj.newName;
}
