var imgSmall = "";
var img = "";

AppPcms.presentAdmin = (function () {
    self = this;
    self.id;
    self.init = () => {
      this.initEditor();
      this.eventHandler();
    }
    self.setID = (id) => {
        this.id = id;
    }
    self.getID = () => {
      return this.id;
    }
    self.initEditor = () => {
      tinymce.init(
      {
          mode: "specific_textareas",
          editor_selector: "shortDescription",
          menubar: false,
          height: 150,
          plugins:[
            'advlist autolink  image charmap print preview anchor',
            'searchreplace visualblocks code fullscreen',
            'contextmenu paste code'
          ],
          toolbar: 'undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent'
      }
      )
      tinymce.init(
      {
          mode: "specific_textareas",
          editor_selector: "detailDescription",
          menubar: false,
          height: 150,
          plugins:[
            'advlist autolink  image charmap print preview anchor',
            'searchreplace visualblocks code fullscreen',
            'contextmenu paste code'
          ],
          toolbar: 'undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent'
      }
      )
    }
    self.eventHandler =  () => {
         $('#pt-upload').on('click',function(){ AppPresentAdmin.upload("big") });
         $('#pt-uploadSmall').on('click',function(){ AppPresentAdmin.upload("small") });
         $("#presentAdminUpdata").click( async function(){
              let result = await AppPresentAdmin.create();
              if(result != false){
                  if(AppPresentAdmin.getID == 0){
                    alert("Gaven er oprettet")
                  } else {
                    alert("Gaven er opdateret")
                  }
                  var url = new URL(window.location.href);
                  url.searchParams.set('action','showquich');
                  window.location.href = url.href;
              }

         });
    }
    self.edit = async (id) => {
        this.setID(id);
        let result = await this.loadPresent(id);
        this.insetDataIntoFormular(result.data[0]);

    };
    self.doDelete = async (id) => {
       let result = await this.deletePresent(id);
       $('#'+id).fadeOut().remove();

    };
    self.deletePresent = (id) => {
       return new Promise(function(resolve, reject) {
               $.post(_ajaxPath+"present/doDelete",{id:id,lang:_lang}, function(res, status) {
                    if(res.status == 0) {  AppPcmsError.reg(res.msg); return; }
                    else { resolve(res) }
                }, "json");
        })
    };
    self.loadPresent = (id) => {

       return new Promise(function(resolve, reject) {
               $.post(_ajaxPath+"present/getById",{id:id,lang:_lang}, function(res, status) {
                    if(res.status == 0) {  AppPcmsError.reg(res.msg); return; }
                    else { resolve(res) }
                }, "json");
        })
    };
    self.resetFormular = () => {
         img = "";
         $(".img-container").css('background-image', 'url()');
         imgSmall = "";
         $(".img-container-small").css('background-image', 'url()');
         $(".nav_name").val("");
         $(".prisents_nav_price").val(0);
         $(".vendor").val("");
         $(".pris").val(0);
         $(".budget1").val(0);
         $(".special").val(0);
         $(".caption").val("");
         $("#oko_present").prop( "checked", false );
         $("#kunhos").prop( "checked", false );
         $("#layout_1").prop( "checked", false )
         $("#layout_2").prop( "checked", false )
          tinymce.get("shortDescription").execCommand('mceSetContent',false,"");
          tinymce.get("detailDescription").execCommand('mceSetContent',false,"");
    }

    self.insetDataIntoFormular = (data) => {

         //pt_layout:$("[name=layoutSelect]:checked").val(),
         img = data.pt_img;
         let price = {};

         if(img != ""){
            $(".img-container").css('background-image', 'url(https://system.gavefabrikken.dk/fjui4uig8s8893478/'+img+')');
         }
         imgSmall = data.pt_img_small;
         if(imgSmall != ""){
            $(".img-container-small").css('background-image', 'url(https://system.gavefabrikken.dk/fjui4uig8s8893478/'+imgSmall+')');
         }
         if(_lang == 1){
           price = JSON.parse(data.pt_price);
           $(".prisents_nav_price").val(data.prisents_nav_price);
         }
         if(_lang == 4){
           price = JSON.parse(data.pt_price_no);
           $(".prisents_nav_price").val(data.prisents_nav_price_no);
         }

         $(".nav_name").val(data.nav_name);


         $(".vendor").val(data.vendor);
         $(".pris").val(price.pris);
         $(".budget1").val(price.budget);
         $(".special").val(price.special);
         $(".caption").val(data.caption);
         data.oko_present != 0 ? $("#oko_present").prop( "checked", true ):"";
         data.kunhos != 0 ? $("#kunhos").prop( "checked", true ):"";
         $("#layout_"+data.pt_layout).prop( "checked", true )
          tinymce.get("shortDescription").execCommand('mceSetContent',false,Base64.decode(data.short_description));
          tinymce.get("detailDescription").execCommand('mceSetContent',false,Base64.decode(data.long_description));

    }


    self.upload = (mode) => {
      var mode = mode;
      if(mode == "small"){

     $("#pt-progress-small").show();
      $.ajax(
        {

        url: 'https://system.gavefabrikken.dk//gavefabrikken_backend/index.php?rt=upload/presentationSmall',
        type: 'POST',
        data: new FormData($('.uploadFileSmall')[0]),
        cache: false,
        contentType: false,
        processData: false,
        xhr: function () {
            var myXhr = $.ajaxSettings.xhr();
            if (myXhr.upload) {
              myXhr.upload.addEventListener('pt-progress-small', function (e)
                {
                  if (e.lengthComputable) {
                    $('pt-progress-small').attr(
                      {
                      value: e.loaded,
                      max: e.total,
                      }
                    );
                  }
                }, false
              );
            }
            return myXhr;
          }
        }
      ).done(function(res) {

        $("#pt-progress-small").hide();
            var imgData = JSON.parse(res);
             imgSmall = imgData.newName+".jpg";
             $(".img-container-small").css('background-image', 'url(https://system.gavefabrikken.dk/fjui4uig8s8893478/'+imgData.newName+'.jpg)');
        });



     } else {
      $("#pt-progress").show();
      $.ajax(
        {

        url: 'https://system.gavefabrikken.dk//gavefabrikken_backend/index.php?rt=upload/presentation',
        type: 'POST',
        data: new FormData($('.uploadFile')[0]),
        cache: false,
        contentType: false,
        processData: false,
        xhr: function () {
            var myXhr = $.ajaxSettings.xhr();
            if (myXhr.upload) {
              myXhr.upload.addEventListener('progress', function (e)
                {
                  if (e.lengthComputable) {
                    $('progress').attr(
                      {
                      value: e.loaded,
                      max: e.total,
                      }
                    );
                  }
                }, false
              );
            }
            return myXhr;
          }
        }
      ).done(function(res) {
        $("#pt-progress").hide();

            var imgData = JSON.parse(res);
            img = imgData.newName+".jpg";
            $(".img-container").css('background-image', 'url(https://system.gavefabrikken.dk/fjui4uig8s8893478/'+imgData.newName+'.jpg)');

        });
      }
    };
   //--------- Function ----------------
   self.create = () => {
   let id = this.getID();
   let pris = ($('.pris').val() ? $('.pris').val(): 0);
   let budget = ($('.budget1').val()? $('.budget1').val(): 0);
   let special = ($('.special').val() ? $('.special').val(): 0);
   let vis_pris = ($('.pris').val() ? "true": "false");
   let vis_budget = ($('.budget1').val()? "true": "false");
   let vis_special = ($('.special').val() ? "true": "false");

  var price = {
     pris:pris,
     vis_pris:vis_pris,
     budget:budget,
     vis_budget:vis_budget,
     special:special,
     vis_special:vis_special
   };

   var shortDescription  = Base64.encode(tinyMCE.get('shortDescription').getContent({format : 'HTML'}));
   var detailDescription = Base64.encode(tinyMCE.get('detailDescription').getContent({format : 'HTML'}));
     //tinyMCE.get('shopDa').setContent(Base64.decode(responce.data.shop[0].descriptions[0].description)
     //var shopDa = Base64.encode(tinyMCE.get('shopDa').getContent({format : 'HTML'}));
   var  oko_present;
   var kunhos;
   if( $('#oko_present').is(":checked")){
        oko_present = "1"
   } else {
        oko_present = "0"
   }
   if( $('#kunhos').is(":checked")){
        kunhos = "1"
   } else {
        kunhos = "0"
   }
   let error = false;


   var data = {
         pt_layout:$("[name=layoutSelect]:checked").val(),
         pt_img:img,
         pt_img_small:imgSmall,
         nav_name:$(".nav_name").val(),
         vendor:$(".vendor").val(),
         prisents_nav_price:$(".prisents_nav_price").val(),
         oko_present:oko_present,
         kunhos:kunhos,
         caption:$(".caption").val(),
         shortDescription:shortDescription,
         detailDescription:detailDescription,
         price:price
        }
        $("#skabelon").removeClass("error");
        $("#nav").removeClass("error");
        $("#bigPresentation").removeClass("error")
        if($("[name=layoutSelect]:checked").val() == undefined ){
            error = true;
            $("#skabelon").addClass("error");
        }
        if($(".nav_name").val() == ""){
            error = true;
            $("#nav").addClass("error");
        }
        if(img== ""){
            error = true;
            $("#bigPresentation").addClass("error")
        }

        return new Promise(function(resolve, reject) {
            if(error == true){
                alert("Du mangler at udfylde felter");
                resolve(false);
            } else {

            $.post(_ajaxPath+"present/create",{id:id,data:data,lang:_lang,user_id:_userId}, function(res, status) {
                    if(res.status == 0) {  AppPcmsError.reg(res.msg); return; }
                    else { resolve(res) }
                }, "json");
            }
        })

  }




});


