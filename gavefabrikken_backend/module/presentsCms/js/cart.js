AppPcms.cart = (function () {
    self = this;
    self.init = () => {
    }

    self.UIinit = () => {
        $("#myPresentationModal").modal('hide');
        $("#cart").modal('show');
        $("#updatePresentation").show();
        $(".my-file").toggle();
        $(".fa-shopping-cart").toggle();
        $(".fa-folder").toggle();
        $(".presentation-copy").show();
    //cart

    }
    self.resetUI = () => {
        $("#cart-presentation-name").html("");
        $("#pdf-front-companyname").val("");
        $("#pdf-front-page").prop( "checked", false );
        $("#pdf-omtanke").prop( "checked", false );
        $("#pdf-gaveklubben").prop( "checked", false );
        $("#pdf-back-page").prop( "checked", false );
        $("#pdf-indpak").prop( "checked", false );
        $("#pdf-tree").prop( "checked", false );
        $("input:checkbox[name=salemanSelect]").prop( "checked", false );
        $("#newPresentationName").val("");
        $(".selected").removeClass("selected");

    }
    self.loadConfig = (id) => {
       this.resetUI();
       return new Promise(function(resolve, reject) {
               $.post(_ajaxPath+"presentation/getByConfig",{id:id}, function(res, status) {
                    if(res.status == 0) {  AppPcmsError.reg(res.msg); return; }
                    else { resolve(res) }
                }, "json");
        })
    }

    function upperCaseCompanyName($string){
        $string = str_replace("u00e6","Æ",$string);
        $string = str_replace("u00f8","Ø",$string);
        $string = str_replace("u00e5","Å",$string);
        $string = str_replace("u00c6","Æ",$string);
        $string = str_replace("u00d8","Ø",$string);
        $string = str_replace("u00c5","Å",$string);
        $string = str_replace("æ","Æ",$string);
        $string = str_replace("ø","Ø",$string);
        $string = str_replace("å","Å",$string);
  return $string;
}


    self.insetConfig = (data) => {
      return new Promise(function(resolve, reject) {
          let config = JSON.parse(data.data[0].config);
          var strCompanyName = config.pdf_front_companyname;
          strCompanyName = strCompanyName.replace("u00e6","æ");
          strCompanyName = strCompanyName.replace("u00f8","ø");
          strCompanyName = strCompanyName.replace("u00e5","å");
          strCompanyName = strCompanyName.replace("u00c6","Æ");
          strCompanyName = strCompanyName.replace("u00d8","Ø");
          strCompanyName = strCompanyName.replace("u00c5","Å");
          $("#pdf-front-companyname").val(strCompanyName);
          config.pdf_front_page == 1 ? $("#pdf-front-page").prop( "checked", true ) : $("#pdf-front-page").prop( "checked", false );
          config.pdf_omtanke == 1 ? $("#pdf-omtanke").prop( "checked", true ) : $("#pdf-omtanke").prop( "checked", false );
          config.pdf_gaveklubben == 1 ? $("#pdf-gaveklubben").prop( "checked", true ) : $("#pdf-gaveklubben").prop( "checked", false );
          config.pdf_back_page == 1 ? $("#pdf-back-page").prop( "checked", true ) : $("#pdf-back-page").prop( "checked", false );
          config.pdf_indpak == 1 ? $("#pdf-indpak").prop( "checked", true ) : $("#pdf-indpak").prop( "checked", false );
          config.pdf_tree == 1 ? $("#pdf-tree").prop( "checked", true ) : $("#pdf-tree").prop( "checked", false );
          let saleman = config.saleperson.split(",");
         saleman.forEach((item,index) => {
            $('input:checkbox[name="salemanSelect"][value="' + item + '"]').prop('checked',true);
            $("#salepersonId_"+item).addClass("selected")
         });
         resolve("done");
      })
    }
     self.loadPresents = (id) => {
        return new Promise(function(resolve, reject) {
               $.post(_ajaxPath+"presentation/getById",{id:id}, function(res, status) {
                    if(res.status == 0) {  AppPcmsError.reg(res.msg); return; }
                    else { resolve(res) }
                }, "json");
        })
     }
     self.insertPresent = (list) => {
        return new Promise(function(resolve, reject) {
             list.data.forEach((item,index) => {
                 let img = "https://system.gavefabrikken.dk/fjui4uig8s8893478//"+item.pt_img;
                 let html =   '<li data-id='+item.present_id+' class="presentation-elememt-set" class="ui-state-default">'+
                 '<img   src='+img+'><i data-id='+item.present_id+' class="fas fa-trash-alt presentation-elememt-set-trash"></i>'+
                 '<i data-id='+item.present_id+' class="fas fa-edit presentation-elememt-set-edit"></i>'+
                  '</li>';
                 $("#sortable").append(html);
                 $(".presentation-elememt-set-trash").unbind( "click" );
                 $(".presentation-elememt-set-trash").click(function(){
                      $(this).parent().remove();
                      AppPcmsPresentSetting.remove($(this).attr("data-id"));                      
                 })
                 $(".presentation-elememt-set-edit").unbind( "click" );
                 $(".presentation-elememt-set-edit").click(function(){
                    AppPcmsPresentSetting.show($(this).attr("data-id"))
                 })


             })
          })
     }





//      $('input:checkbox[name="salemanSelect"][value="' + v + '"]').prop('checked',true);

});

