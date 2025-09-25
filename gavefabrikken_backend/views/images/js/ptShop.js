//alert(_selectedShop);
// ptShop.js
var ptShopClass = (function (presentId)
  {
    var self = this;

    //self.presentId = presentId;
    self.init = () => {
      var noExstraImg = ""

      $("#ptPdfLink").val("Ingen pdf");
      $("#ptLink").val("https://presentation.gavefabrikken.dk/presentation?user="+_shop_token)
      $("#salePersonLink").val("https://presentation.gavefabrikken.dk/presentation?mode=saleperson&user="+_shop_token)
      $("#pt_title").val(_pt_shopName);
      if(_pt_mere == 1){
         $( ".pt-mereAtGive" ).prop( "checked", true );
      }
      if(_pt_tree == 1){
         $( ".pt-plantTree" ).prop( "checked", true );
      }
      if(_pt_bag == 1){
         $( ".pt-bag" ).prop( "checked", true );
      }
      if(_pt_green_layout == 1){
         $( ".pt-green-layout" ).prop( "checked", true );
      }

      if(_pt_brands_united == 1){
         $( ".pt-brands-united" ).prop( "checked", true );
      }

      if(_pt_bag == 1){
         $( ".pt-bag" ).prop( "checked", true );
      }

      if(_pt_voucher_page == 1){
         $( ".pt-voucher-page" ).prop( "checked", true );
      }
      if(_pt_saleperson_page == 1){
         $( ".pt-saleperson-page" ).prop( "checked", true );
      }
      if(_pt_layout_language == 2){
         $( ".pt-layout-language-eng" ).prop( "checked", true );
      }




      $( "#pt-frontpage"+_pt_frontpage ).prop( "checked", true );
      self.setPDF();
      self.buildSaleman()
      self.setActions();
    }
    self.setActions = () => {
      $('#ptMakePdf').on('click', self.makePdf);
      $('#ptDeletePdf').on('click', self.deletePDF);
      $("#ptShop-progress").hide();
      $('#ptShop-upload').on('click', self.upload);
      $('#exstraImg').on('click', self.updateLinkWithExtraImg);
      $('#ptShopTitle').on('click', self.updateTitle);
      $('#ptLoadPdf').attr("href", "https://system.gavefabrikken.dk/gavefabrikken_backend/views/pdf-to-png.php?shopId="+_shopId);
      $('#openShop').on('click', self.openPresentation);
      $('#openShopAsSale').on('click', self.openPresentationAsSale);
      $('.pt-frontpage').on('change', self.updateFrontpage);
      $('.pt-mereAtGive').on('change', self.updateMereAtGive);
      $('.pt-plantTree').on('change', self.updatePlantTree);
      $('.pt-green-layout').on('change', self.updateGreenLayout);
      $('.pt-brands-united').on('change', self.updateBrandsUnited);
      $('.pt-bag').on('change', self.updateBag);
      $('.pt-voucher-page').on('change', self.updateVoucherPage);
      $('.pt-saleperson-page').on('change', self.updateSalepersonPage);
      $('.pt-layout-language-eng').on('change', self.updatelanguage);



    };

    self.setPDF = () => {
              if(_pt_pdfId != ""){
                    $('#pdfLink').html("<button onclick='return SaveToDisk(this.id)' id='"+_pt_pdfId+"'>Download PDF</button> <button id='showPDF' >Vis PDF</button>");
                    $('#ptDeletePdf').show();
                      $("#ptPdfLink").val("https://presentation.gavefabrikken.dk/presentation/pdf/"+_pt_pdfId+".pdf")
                      $('#showPDF').on('click', self.showPDF);

              }
    }
    self.deletePDF = () => {
           var r =  confirm("Vil du slette PDF")
            if(r){
            $.ajax(
            {
                url: 'index.php?rt=ptAdmin/deletePdf',
                type: 'GET',
                dataType: 'json',
                data: {id:_shopId}
            }
          ).done(function(res) {
                $('#ptDeletePdf').hide();
                $('#pdfLink').html("Ingen Pdf");
                $("#ptPdfLink").val("Ingen pdf");
            }
          )
          }
    }

    self.openPresentation = () => {
         window.open("https://presentation.gavefabrikken.dk/presentation?user="+_shop_token);
    }
      self.openPresentationAsSale = () => {
          window.open("https://presentation.gavefabrikken.dk/presentation?mode=sale&user="+_shop_token);
      }
    self.updateVoucherPage = () => {
        let isCheck = 0;
        if($(".pt-voucher-page").prop('checked') == true){
            isCheck = 1;
        }
        $.ajax(
            {
            url: 'index.php?rt=shop/update',
            type: 'POST',
            dataType: 'json',
            data: {id: _shopId, pt_voucher_page: isCheck }
            }).done(function(res) {
                alert("voucher siden er opdateret")
        })
    }
    self.updateSalepersonPage = () => {
        let isCheck = 0;
        if($(".pt-saleperson-page").prop('checked') == true){
            isCheck = 1;
        }
        $.ajax(
            {
            url: 'index.php?rt=shop/update',
            type: 'POST',
            dataType: 'json',
            data: {id: _shopId, pt_saleperson_page: isCheck }
            }).done(function(res) {
                alert("Bagsiden er opdateret")
        })
    }


    self.updatelanguage = () => {
        let isCheck = $(".pt-layout-language-eng").prop('checked') == true ? 2:0;
        $.ajax(
            {
            url: 'index.php?rt=shop/update',
            type: 'POST',
            dataType: 'json',
            data: {id: _shopId, pt_language: isCheck }
            }).done(function(res) {
                alert("Tilbudet er nu sat til Engelsk")
        })
    }
      self.updateBrandsUnited = () => {
          let isCheck = 0;
          if($(".pt-brands-united").prop('checked') == true){
              isCheck = 1;
          }
          $.ajax(
              {
                  url: 'index.php?rt=shop/update',
                  type: 'POST',
                  dataType: 'json',
                  data: {id: _shopId, pt_brands_united: isCheck }
              }).done(function(res) {
              alert("BrandsUnited er opdateret")
          })
      }

    self.updateGreenLayout = () => {
        let isCheck = 0;
        if($(".pt-green-layout").prop('checked') == true){
            isCheck = 1;
        }
        $.ajax(
            {
            url: 'index.php?rt=shop/update',
            type: 'POST',
            dataType: 'json',
            data: {id: _shopId, pt_layout_style: isCheck }
            }).done(function(res) {
                alert("layout er opdateret")
        })
    }
    self.updateBag = () => {
        let isCheck = 0;
        if($(".pt-bag").prop('checked') == true){
            isCheck = 1;
        }
        $.ajax(
            {
            url: 'index.php?rt=shop/update',
            type: 'POST',
            dataType: 'json',
            data: {id: _shopId, pt_bag_page: isCheck }
            }).done(function(res) {
                alert("Siden er opdateret")
        })
    }



    self.updateFrontpage = () => {
        var newFrontpage =  $('input[name=frontpage]:checked').val();

        $.ajax(
            {
            url: 'index.php?rt=shop/update',
            type: 'POST',
            dataType: 'json',
            data: {id: _shopId, pt_frontpage: newFrontpage }
            }).done(function(res) {
                alert("Forsiden er  opdateret")
        })

    }
    self.updateMereAtGive = () => {
        let isCheck = 0;
        if($(".pt-mereAtGive").prop('checked') == true){
            isCheck = 1;
        }

        $.ajax(
            {
            url: 'index.php?rt=shop/update',
            type: 'POST',
            dataType: 'json',
            data: {id: _shopId, pt_mere_at_give: isCheck }
            }).done(function(res) {
                alert("Forsiden er  opdateret")
        })

    }

    self.updatePlantTree = () => {
        let isCheck = 0;
        if($(".pt-plantTree").prop('checked') == true){
            isCheck = 1;
        }
        $.ajax(
            {
            url: 'index.php?rt=shop/update',
            type: 'POST',
            dataType: 'json',
            data: {id: _shopId, pt_tree: isCheck }
            }).done(function(res) {
                alert("Forsiden er  opdateret")
        })
    }


    self.updateTitle = () => {
        $.ajax(
            {
            url: 'index.php?rt=shop/update',
            type: 'POST',
            dataType: 'json',
            data: {id: _shopId, pt_shopName: $("#pt_title").val() }
            }).done(function(res) {
                alert("Titel opdateret")
            })
    };

    self.updateLinkWithExtraImg = () => {
        if($('#exstraImg').is(":checked")){
              $("#ptLink").val("https://presentation.gavefabrikken.dk/presentation?token="+_ptPdf+"&print&user="+_shop_token)
        } else {
             $("#ptLink").val("https://presentation.gavefabrikken.dk/presentation?token="+_ptPdf+"&user="+_shop_token)

        }
     }


     self.insertData = (pt_saleperson) => {
        // #server#
        if(pt_saleperson == null) return;

        pt_saleperson.split(",").map(function(id){
          if(id !="") {
            $("input:checkbox[name=salemanSelect][value=" + id + "]").prop('checked', true)
            $("#salepersonId_"+id).toggleClass("selected");
          }
      })

     }
    self.makePdf = () => {

            $('#ptMakePdf').prop('disabled', true);
            $('#ptMakePdf').html("Vent, PDF oprettes")
            let url = "https://system.gavefabrikken.dk/presentation/pdf.php?print-pdf&u=1&user="+_shop_token+"&print";
            // nove hack
            if(_shop_token == "8USX1xYuczY5dKtKqcFKOgw7ZGklIk") {
                url = "https://system.gavefabrikken.dk/novo/pdf.php?print-pdf&u=1&user="+_shop_token+"&print";
            }
            $.ajax(
            {
                url: 'index.php?rt=ptAdmin/uploadPDF',
                type: 'POST',
                dataType: 'json',
                data: {shopId:_shopId,url:url}
            }
          ).done(function(res) {
                  if(res.data.file){


                  var jdata =  res.data.file;
                  $('#ptMakePdf').prop('disabled', false);
                  $('#ptMakePdf').html("Lav ny pdf");
                  $('#pdfLink').html("<button onclick='return SaveToDisk(this.id)' id='"+jdata+"'>Download PDF</button><button id='showPDF' >Vis PDF</button>");
                   $('#ptDeletePdf').show();
                   $("#ptPdfLink").val("https://presentation.gavefabrikken.dk/presentation/pdf/"+jdata+".pdf")
                   $('#showPDF').on('click', self.showPDF);
                  } else {
                    alert("Noget gik galt i pdf oprettelsen")
                    $('#ptMakePdf').prop('disabled', false);
                    $('#ptMakePdf').html("Lav ny pdf");
                   $('#pdfLink').html("");
                   $('#ptDeletePdf').hide();
                   $("#ptPdfLink").val("")
                  }
            }
          )
       // $('#ptLoadPdf').find('a').trigger('click');



    }
    self.showPDF = () => {

      window.open($("#ptPdfLink").val());
    }

    self.upload = () => {
      $("#ptShop-progress").show();

      $.ajax(
        {

        url: 'index.php?rt=upload/presentationPdf',
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
          var j = JSON.parse(res);
          $("#ptShop-progress").hide();
          var filename = j.newName;

          $.ajax(
            {
            url: 'index.php?rt=shop/update',
            type: 'POST',
            dataType: 'json',
            data: {id: _shopId, pt_pdf: filename}
            }
          ).done(function(res) {
              $("#salePersonLink").val("https://presentation.gavefabrikken.dk/presentation?mode=saleperson&user="+_shop_token)
              $("#ptLink").val("https://presentation.gavefabrikken.dk/presentation?user="+_shop_token)
              $("#ptPdfLink").val("https://system.gavefabrikken.dk/fjui4uig8s8893478/"+j.newName)
              _ptPdf = j.newName;
             url = "https://system.gavefabrikken.dk/gavefabrikken_backend/views/pdf-to-png.php?shopId="+_shopId
             var win = window.open(url,"_self")


            }
          )



        }

      );
    };

    self.selectSalemanUI = (ele) => {
      if($("[name='salemanSelect']:checked").length > 2){
        alert("du kan kun afkrydse 2, du har valgt: "+$("[name='salemanSelect']:checked").length)
      }
      let id = ele.target.value;
      $("#salepersonId_"+id).toggleClass("selected");
      self.updataSaleman();

    };
    self.updataSaleman = () => {
       var salepersonList = [];
      $("input:checkbox[name=salemanSelect]:checked").each(function(){
            salepersonList.push($(this).val());
      });
        $.ajax(
            {
            url: 'index.php?rt=shop/update',
            type: 'POST',
            dataType: 'json',
            data: {id: _shopId, pt_saleperson: salepersonList.join(",")}
            }
          ).done(function(res) {   }
          )


    }


    self.buildSaleman = () => {
      var html = "<table width= 400>";
      $.getJSON("index.php?rt=ptAdmin/getSalePersonList",{localisation:_localisation}, function(result)
        {
          $.each(result.data, function(index, value)
            {
              html += "<tr id='salepersonId_"+value.attributes.id+"' style='border-bottom:1pt solid black;'><td><img width=90 src='https://presentation.gavefabrikken.dk/presentation/workers/"+value.attributes.img+"' /></td>";
              html += "<td><div>"+value.attributes.name+"</div><div>"+value.attributes.title+"</div><div>"+value.attributes.tel+"</div><div>"+value.attributes.mail+"</div></td>";
              html += "<td><input  type='checkbox' name='salemanSelect' value='"+value.attributes.id+"'></td>";
              html += "</tr>";
            }
          );
          $(".salesman").html(html+"</table>");

          $("[name='salemanSelect']").click(self.selectSalemanUI);
           self.insertData(_pt_saleperson)

        }
      );
    };


  }
)
function SaveToDisk(fileURL, fileName="Presentation.pdf") {
    // for non-IE
    fileURL = "https://presentation.gavefabrikken.dk/presentation/pdf/"+fileURL+".pdf";
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

