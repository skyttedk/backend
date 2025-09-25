var flag = "";
var myDropzone;
$( document ).ready(function() {
    $( "#sortable" ).sortable();
    $( "#sortable" ).disableSelection();
  $( "#shopTabs" ).tabs({
    activate: function (event, ui) {
        if(ui.newPanel[0].id == "shoptabs-10"){
            $("#vsg-content").css("height",  $( document ).height()-310+"px" ) ;
            $("#vsg-leftpanel").css("height",  $( document ).height()-240+"px" ) ;
            $("#vsg-leftpanel-content").css("height",  $( document ).height()-290+"px" ) ;






        }
        if(ui.newPanel[0].id == "shoptabs-5"){
            $("#tabsFeltDeff").css("height",  $( document ).height()-230+"px" ) ;
        }
        if( ui.newPanel[0].id == "shoptabs-1"){
            $("#stamdata").css("height",  $( document ).height()-230+"px" ) ;
        }
    },
    create: function(e,ui){
      $("#stamdata").css("height",  $( document ).height()-260+"px" ) ;
    }

});

    $( window ).resize(function() {
          var activeTab = $("#shopTabs .ui-shoptabs-panel:visible").attr("id");

          if( activeTab == "shoptabs-10"){
                   $("#vsg-content").css("height",  $( document ).height()-310+"px" ) ;
            $("#vsg-leftpanel").css("height",  $( document ).height()-240+"px" ) ;
            $("#vsg-leftpanel-content").css("height",  $( document ).height()-290+"px" ) ;
          }
          if( activeTab == "shoptabs-5"){
                $("#tabsFeltDeff").css("height",  $( document ).height()-230+"px" ) ;
          }
          if( activeTab == "shoptabs-1"){
              $("#stamdata").css("height",  $( document ).height()-230+"px" ) ;
          }

    });

//             <div id="vsg-main-menu">



//   $('#shopTabs').click('tabsselect', function (event, ui) {  });





    $( "#shopDescriptionTabs" ).tabs();

       // drop.js, line ca. 369, drop skal lavet om in time
    //
       myDropzone = new Dropzone(".dropzone", { url: "index.php?rt=upload/logo"});

     tinymce.init({
            mode : "specific_textareas",
           editor_selector : "shopDescriptionText",

          height: 200,
          plugins: [
            'advlist autolink lists link image charmap print preview anchor',
            'searchreplace visualblocks code fullscreen',
            'insertdatetime media table contextmenu paste code'
          ],
          toolbar: 'insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image'
    });









    /*
       $( "#shopFrom" ).datepicker({
      defaultDate: "+1w",
      changeMonth: true,
      numberOfMonths: 3,
      dateFormat: "mm-dd-yy",

      onClose: function( selectedDate ) {
        $( "#shopTo" ).datepicker( "option", "minDate", selectedDate );
      }
    });
    $( "#shopTo" ).datepicker({
      defaultDate: "+1w",
      changeMonth: true,
      numberOfMonths: 3,
      dateFormat: "mm-dd-yy",

      onClose: function( selectedDate ) {
        $( "#shopFrom" ).datepicker( "option", "maxDate", selectedDate );
      }
    });
   */

    $( "#feltDeffContainer" ).sortable({
      placeholder: "ui-state-highlight",
      scroll : false,
      update: function( event, ui ) {
            feltDeff.update()
      }
    });
  //  $( "#feltDeffContainer" ).disableSelection();


     $( "#tabsFeltDeff" ).tabs();
     ajax({},"present/readTop10","scale","html");

    console.log("d6")
});


function controlDropElemet(response){
    var obj = JSON.parse(response);

    var imageUrl = "views/media/logo/"+obj.newName+".jpg";
    $('#selectedLogo').css('background-image', 'url(' + imageUrl + ')');

}


function scale(responce)
{

    $("#shopPresentSelect").html(responce)
    $('.flip-container').css({ transform: 'scale(.9)' });
    $(".Shop").show();
    if(flag == ""){
        $("#gaveAdminBack").hide()
        $("#gaveAdminBackShop").hide()
        flag = "sat";
    }  else {
        $("#gaveAdminBack").hide()
        $("#gaveAdminBackShop").show()
    }
    company.editLoadData();
}
function shopCopyBack(responce)
{
      ajax({},"present/readAll","scale","html");
 //ajax({"id":_editShopID},"shop/read","company.editLoadDataResponse");
}
function changeAllLangSettings()
{
    if($("#language_enabled").is(':checked')) {$( "#langliste" ).show();  } else { $( "#langliste" ).hide();  }

}
function reInitTinyce()
{
    if($("#shopDescriptionTabs").find(".mce-panel").length != 0){
        tinymce.remove()
    }


}

