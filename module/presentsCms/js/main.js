$(document).ready(function () {
//alert(" hvis du akut skal lave pdf, s� ring til mig(ulrich) 53746555")
AppPcmsMain = new AppPcms.main()
   AlfabetSearch = new AppPcms.alfabetSearch();
   AppPcmsError = new AppPcms.error();
   AppPcmsSearch = new AppPcms.search();
   AppPcmsPdfOptions = new AppPcms.pdfOptions();
   AppPcmsPresentation = new AppPcms.presentation();
   AppPcmsCart = new AppPcms.cart();
   AppPcmsPresentSetting = new AppPcms.presentSetting();
   AppPresentAdmin = new AppPcms.presentAdmin();
   AppPcmsShop  =  new AppPcms.shop();
   AppArchiveArchive = new AppPcms.archive();
   //AppPcmsPresentPrice = new AppPcms.presentPrice();
   AppPcmsMain.run();


})


var AppPcms = {};
var AppPcmsMain,AlfabetSearch, AppPcmsError, AppPcmsPdfOptions,AppPcmsPresentation,AppPcmsCart,AppPcmsPresentSetting,AppPresentAdmin,AppPcmsShop,AppPcmsPresentPrice,AppArchiveArchive;
var demo = true;
var _presentationId = "";
var _presentationSet = [];
var _pdfOptionsIsQuick = true;
var _ajaxPath = "../presentsCms/api/";
var _presentSetting = new Map();
var _root = "https://system.gavefabrikken.dk/";



AppPcms.main = (function () {
    self = this;
    self.searchTimer;
    self.run = async () => {
        AppArchiveArchive.init();
        AlfabetSearch.init();
        AppPcmsPresentation.init();
        AppPcmsPdfOptions.init();
        AppPcmsShop.init();
        this.setEvents();
        $( "#sortable" ).sortable({
            stop:  function (event, ui) {
                AppPcmsPresentation.updateSort();
            }
        });
        $( "#sortable" ).disableSelection();
        AppPcmsSearch.initRange();
        AppPcmsPresentSetting.init();
        AppPresentAdmin.init();
        
       $(".fa-dollar-sign").hide();
        if(_userId == 3 || _userId == 20){
            $(".fa-dollar-sign").show();
        }

     // fjerner norge fra det nye
       /*

       if(_lang != 1){
          $(".menu-present").hide();
          $(".menu-present").removeClass("menu-present");
        }
        */
        if(_lang != 1){
         // $(".menu-export").hide();

        }
        this.returnUrl();
    }
    self.returnUrl = () => {

    }

    self.setEvents = () => {
        // skal m�ske slettes da det er implementeret i search
        $("#filter-kostpris").click(async function(){
               $(".fulltxtsearch").val("");
            let start = $("#start-kost-pris").val();
            let end  = $("#slut-kost-pris").val();
            if(start != "" && end != ""){
                let range = {start:start,end:end}
                let res = await AppPcmsSearch.getRange(range);
                if(res.data.length == 0){
                    message("Intet søgeresultat!")
                } else {
                    AlfabetSearch.freeTextInit(res)
                }
            }

         })
         $(".my-presentation").click( async function () {
             let data = await AppPcmsPresentation.loadOverview();
             AppPcmsPresentation.buildPresentation(data);
         })


         $(".presentation-set-build").click(async function(){
              message("Opretter pdf af valgte slide, vent venligt")

          $(".presentation-set-build").hide();
              $("#busy-fa-file-download").show();
              let pdf = new AppPcms.pdf;
              let presentation_id = "";
              _presentationSet = [];

              if(_presentationId == ""){
                  presentation_id = Math.random().toString(36).substring(7)+Math.random().toString(36).substring(7)+Math.random().toString(36).substring(7);
                  $('.presentation-elememt-set').each(function(ele) {
                    _presentationSet.push($(this).attr("data-id"))
                  });
                  await pdf.make(_presentationSet,presentation_id);
              } else {
                await pdf.build(_presentationId);
                presentation_id = _presentationId;
              }
              $(".presentation-set-build").show();
              $("#busy-fa-file-download").hide();
              _presentationSet = [];

              SaveToDisk(presentation_id);
        });

        $(".presentation-multi-price").click(async function(){
            new AppPcms.presentPrice().init();

        })

        $(".fa-glasses").click(async function(){
              let list = [];

              $('.presentation-elememt-set').each(function(ele) {
                    list.push($(this).attr("data-id"));
              });
              if(list.length == 0){
                message("Der er ingen valgte slide i kurv")
                return;
              }
              var h = ($( document ).height()-50);
              let html =' <iframe src="https://system.gavefabrikken.dk/presentation/pdf.php?u=1&print-pdf&isSalePreview='+list+'" width="100%" height="'+h+'" frameBorder="0"></iframe>';
              $(".pcms-main").html(html);
        })



        $(".fulltxtsearch").keyup(function() {
            $(".kostpris").val("");
            let letters = $(this).val();
            if( letters.length == 1 ){
                 setTimeout(function(){
                    if($(".fulltxtsearch").val().length == 1){
                        AppPcmsSearch.alfabet(letters);
                    }
                 }, 300)

            } else {
               clearTimeout(AppPcmsMain.searchTimer);
               AppPcmsMain.searchTimer = setTimeout( async function(){
                      $(".search").show();
                        let res = await AppPcmsSearch.getFreeText(letters);

                        if(res.data.length == 0){
                             message("Intet søgeresultat!")
                        } else {
                          AlfabetSearch.freeTextInit(res)
                        }

               }, 500)
            }
        });

        $(".my-file").click(function(){
            $("#cart").modal('show');
        })

        $(".closePresentation").click(function(){
          _presentSetting = new Map();
          $(".menu-present").show();
          $("#cart-presentation-name2").html("" );
           AppPcmsCart.resetUI();
           $(".presentation-set").find("li").remove();
            $(".closeCart").show();
            $(".edit-allprice").hide();
           $(".closePresentation").hide();
           $("#createPresentation").show();
           $("#updatePresentation").hide();
           _presentationId = "";
           $(".my-file").hide();
           $(".fa-shopping-cart").show();
           $(".fa-folder").show();
        })



        $(".menu-present").click(function(){
          // nav handling
           AppPresentAdmin.setID(0);
           AppPresentAdmin.resetFormular()
           $("#modalPresentView").modal('show');
          // action
        });
        // instantiere editor


    }
    self.toggleCartIconAndFolderOpenIcon = () => {

     ///   $( ".fa-shopping-cart" ).toggle( display );
     //   $( ".fa-folder" ).toggle( display );
     //   $( ".fa-file" ).toggle( display );
    }

})


AppPcms.error = (function() {
    self = this;
    self.reg =  (msg) => {
        if(demo == true)
            alert(msg)
    }

});
var msgTimer;
function message(msg){
   clearTimeout(msgTimer);
    $("#message").html(msg);

    $("#message").fadeIn(500, function(){
        setTimeout(function(){
            $("#message").fadeOut(1000)
        }, 3000)

    })


}

function SaveToDisk(fileURL, fileName="Presentation.pdf") {
    // for non-IE
    fileURL = "https://system.gavefabrikken.dk/presentation/pdf/sale"+fileURL+".pdf";
    if (!window.ActiveXObject) {
        var save = document.createElement('a');
        save.href = fileURL;
        save.target = '_blank';
        save.download = fileName || 'unknown';

        var evt = new MouseEvent('click', {
            'view': window,
            'bubbles': true,
            'cancelable': false
        });
        save.dispatchEvent(evt);

        (window.URL || window.webkitURL).revokeObjectURL(save.href);
    }

    // for IE < 11
    else if ( !! window.ActiveXObject && document.execCommand)     {
        var _window = window.open(fileURL, '_blank');
        _window.document.close();
        _window.document.execCommand('SaveAs', true, fileName || fileURL)
        _window.close();
    }
}
