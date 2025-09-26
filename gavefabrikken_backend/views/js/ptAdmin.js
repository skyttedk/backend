// ptAdmin.js
var ptAdminClass = (function (presentId)
  {
    var self = this;
    self.presentId = presentId;
    self.img = "";
    self.imgSmall = "";
    self.selectSalemanId = {};
    self.init = () => {
        self.setActions();
       // self.buildSaleman();
        self.buildLayout();
        setTimeout(self.load, 1000)
    };

    self.setActions = () => {

      $("#pt-progress-small").hide();
      $("#pt-progress").hide();
      $("#pt-save").on('click',self.save);
      $("#pt-preview").on('click',self.preview);
      $('#file-big').on('change', function () {self.fileCheck(this)});
      $('#pt-upload').on('click',function(){ self.upload("big") });
      $('#pt-uploadSmall').on('click',function(){ self.upload("small") });
      $('#kunhos').on('click',function(){ self.updateKunhos() });
      $('#omtanke').on('click',function(){ self.updateOmtanke() });
      $("#pt_delete_small_img").on('click', function(){ self.deleteShopSmallImg(); } );

           // vsgSavePresentBtn
    };

    self.deleteShopSmallImg = () => {
        var r = confirm("Vil du slette det lille billede")
        if(r==true)
        {

            $.ajax(
          {
              url: 'index.php?rt=present/ptSmallImgDelete',
              type: 'POST',
              dataType: 'json',
              data: {"id": self.presentId}
          }).done(function(res) {
              $(".img-container-small").css('background-image', 'url()');
              alert("Billede slettet")
          })
        }
    }

    self.preview = () => {
      var id = self.presentId
      $("#ptDialog").html('<iframe frameborder="0" style="overflow:hidden;height:99%;width:100%" height="100%" width="100%" src="https://system.gavefabrikken.dk/presentation/pdf.php?u=1&isSalePreview='+id+'"></iframe>');
     $( "#ptDialog" ).dialog({height: 700,
                          width: 800});
    }


    self.updateKunhos = () => {

        $.ajax(
        {
            url: 'index.php?rt=present/kunhos',
            type: 'POST',
            dataType: 'json',
            data: {"id": self.presentId,"state": $('#kunhos').is(":checked")}
        }).done(function(res) {
            if($('#kunhos').is(":checked") == true){
                alert("kunhos logo vises")
            } else {
              alert("kunhos vises ikke")
            }

        })


    }
    self.updateOmtanke = () => {

        $.ajax(
        {
            url: 'index.php?rt=present/omtanke',
            type: 'POST',
            dataType: 'json',
            data: {"id": self.presentId,"state": $('#omtanke').is(":checked")}
        }).done(function(res) {
            if($('#omtanke').is(":checked") == true){
                alert("Omtanke logo vises")
            } else {
              alert("Omtanke vises ikke")
            }

        })
    }

    self.load = () => {

        $.ajax(
        {
            url: 'index.php?rt=present/read',
            type: 'POST',
            dataType: 'json',
            data: {"id": self.presentId}
        }).done(function(res) {
            self.insertData(res.data.present[0]);
        })

    }
    self.insertData = (data) => {

      self.img = data.pt_img;
      self.imgSmall = data.pt_img_small;
      $(".img-container").css('background-image', 'url(../fjui4uig8s8893478/'+data.pt_img+')');
      $(".img-container-small").css('background-image', 'url(../fjui4uig8s8893478/'+data.pt_img_small+')');
      $("[name=layoutSelect][value=" + data.pt_layout + "]").prop('checked', true)
      $("#layoutId_"+data.pt_layout).addClass("selected");
      if(data.pt_img_small_show == "true"){
        $('#pt_show_small_img').prop("checked", true);
      }
      if(data.kunhos == "true"){
        $('#kunhos').prop("checked", true);
      }
      if(data.omtanke == "true"){
        $('#omtanke').prop("checked", true);
      }

      // danmark
      if(data.pt_price != "" ){

        var priceSettings = JSON.parse(data.pt_price);
        if(priceSettings.vis_pris == "true"){
          $('#pt_pris_show').prop("checked", true);
        }
        if(priceSettings.vis_budget == "true"){
          $('#pt_budget_show').prop("checked", true);
        }
        if(priceSettings.vis_special == "true"){
          $('#pt_special_show').prop("checked", true);
        }
        $('#pt_pris').val(priceSettings.pris)
        $('#pt_budget').val(priceSettings.budget)
        $('#pt_special').val(priceSettings.special)
      } else {
          $('#pt_pris_show').prop("checked", true);
          $('#pt_budget_show').prop("checked", true);
      }
      // norge
      if(data.pt_price_no != "" ){

        var priceSettingsNo = JSON.parse(data.pt_price_no);
        if(priceSettingsNo.vis_pris == "true"){
          $('#pt_pris_show_no').prop("checked", true);
        }
        if(priceSettingsNo.vis_budget == "true"){
          $('#pt_budget_show_no').prop("checked", true);
        }
        if(priceSettingsNo.vis_special == "true"){
          $('#pt_special_show_no').prop("checked", true);
        }
        $('#pt_pris_no').val(priceSettingsNo.pris)
        $('#pt_budget_no').val(priceSettingsNo.budget)
        $('#pt_special_no').val(priceSettingsNo.special)
      } else {
          $('#pt_pris_show_no').prop("checked", true);
          $('#pt_budget_show_no').prop("checked", true);
      }

        if(data.pt_price_se != "" ){

            var priceSettingSe = JSON.parse(data.pt_price_se);
            if(priceSettingSe.vis_pris == "true"){
                $('#pt_pris_show_se').prop("checked", true);
            }
            if(priceSettingSe.vis_budget == "true"){
                $('#pt_budget_show_se').prop("checked", true);
            }
            if(priceSettingSe.vis_special == "true"){
                $('#pt_special_show_se').prop("checked", true);
            }
            $('#pt_pris_se').val(priceSettingSe.pris)
            $('#pt_budget_se').val(priceSettingSe.budget)
            $('#pt_special_se').val(priceSettingSe.special)
        } else {
            $('#pt_pris_show_se').prop("checked", true);
            $('#pt_budget_show_se').prop("checked", true);
        }


    }



    self.save = () => {
       /*
      var salepersonList = [];
      $("input:checkbox[name=salemanSelect]:checked").each(function(){
            salepersonList.push($(this).val());
      });
         */

   var price = {
     pris:$('#pt_pris').val(),
     vis_pris:$('#pt_pris_show').is(":checked"),
     budget:$('#pt_budget').val(),
     vis_budget:$('#pt_budget_show').is(":checked"),
     special:$('#pt_special').val(),
     vis_special:$('#pt_special_show').is(":checked")
   };
   var price_no = {
     pris:$('#pt_pris_no').val(),
     vis_pris:$('#pt_pris_show_no').is(":checked"),
     budget:$('#pt_budget_no').val(),
     vis_budget:$('#pt_budget_show_no').is(":checked"),
     special:$('#pt_special_no').val(),
     vis_special:$('#pt_special_show_no').is(":checked")
   };
        var price_se = {
            pris:$('#pt_pris_se').val(),
            vis_pris:$('#pt_pris_show_se').is(":checked"),
            budget:$('#pt_budget_se').val(),
            vis_budget:$('#pt_budget_show_se').is(":checked"),
            special:$('#pt_special_se').val(),
            vis_special:$('#pt_special_show_se').is(":checked")
        };

   var postData = {
         id:self.presentId,
        // pt_saleperson:salepersonList.join(","),
         pt_layout:$("[name=layoutSelect]:checked").val(),
         pt_img:self.img,
         pt_imgSmall:self.imgSmall,
         pt_price:price,
         pt_price_no:price_no,
         pt_price_se:price_se
        }
        $.ajax(
        {
            url: 'index.php?rt=present/updatePresentation',
            type: 'POST',
            data: postData
        }).done(function(res) {
            alert("Saved")
        })


    }

    self.upload = (mode) => {
      var mode = mode;
      if(mode == "small"){

     $("#pt-progress-small").show();
      $.ajax(
        {

        url: 'index.php?rt=upload/presentationSmall',
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
             self.imgSmall = imgData.newName+".jpg";
             $(".img-container-small").css('background-image', 'url(../fjui4uig8s8893478/'+imgData.newName+'.jpg)');
        });



     } else {




      $("#pt-progress").show();
      $.ajax(
        {

        url: 'index.php?rt=upload/presentation',
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
            self.img = imgData.newName+".jpg";
            $(".img-container").css('background-image', 'url(../fjui4uig8s8893478/'+imgData.newName+'.jpg)');

        });
      }
    };
    self.fileCheck = (event) => {
      //var file = event.files[0];
      //console.log(file.size)
    };

    // events

    self.selectLayoutUI = (ele) => {
        let id = ele.target.value;
        $(".pt-admin .layout tr").removeClass("selected");
        $("#layoutId_"+id).addClass("selected");

    };
    // HTML

    self.buildLayout = () => {
      var html = "<table width= 200>";
      html+="<tr style='display: none' id='layoutId_1'><td> <img width=80 src='views/media/icon/layout1.jpg'  /> </td> <td><input  type='radio' name='layoutSelect' value='1' /></td></tr>";
      html+="<tr style='display: none' id='layoutId_2'><td><img width=80 src='views/media/icon/layout2.jpg'></td><td><input  type='radio' name='layoutSelect' value='2' /></td></tr>";
      html+="<tr  id='layoutId_3'><td>Kun tekst</td><td><input  type='radio' name='layoutSelect' value='3' /></td></tr>";
      html+="<tr style='display: none' id='layoutId_4'><td>2023 Layout</td><td><input  type='radio' name='layoutSelect' value='4' /></td></tr>";
      html+="<tr id='layoutId_5'><td>2023 Layout</td><td><input  type='radio' name='layoutSelect' value='5' /></td></tr>";
      html+="</table>"
      $(".layout").html(html);
      $("[name='layoutSelect']").click(self.selectLayoutUI);
    }






  }
);
//new ptAdminClass().init();




