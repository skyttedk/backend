AppPcms.presentation = (function () {
    self = this;
    self.init = (data) => {

        this.setEvents();

    }
    self.setEvents = () => {

        $("#createPresentationCopy").unbind( "click" ).click(async function(){
            AppPcmsPresentation.copy()

        })

        $(".edit-allprice").click(function(){
          $("#modalChangeMultiPriceView").modal('show');
        })
        $(".closePresentation").click(function(){
          $("#cart-presentation-link").html("");
           $(".presentation-copy").hide();
          _presentationId = "";
        })

        $("#createNewPresentation").click( async function(){

            $("#newPresentationError").html("");
            let name = $("#newPresentationName").val();
            if(name !=""){
                let res = await AppPcmsPresentation.create();
                AppPcmsCart.resetUI();
                AppPcmsMain.toggleCartIconAndFolderOpenIcon();
                $(".presentation-set").find("li").remove();
                $("#newPresentation").modal('hide');
                $("#cart").modal('hide');
                message("Præsentationen, "+name+" er blevet oprettet")
                _presentSetting = new Map();
            }

        })
        $("#updatePresentation").click( async function(){
            $("#updatePresentation").html("arbejder..")
            await AppPcmsPresentation.removeAllPresent(_presentationId);
            await AppPcmsPresentation.updataConfig(_presentationId);
            await AppPcmsPresentation.updatePresens(_presentationId);
            message("Præsentationen er blevet opdateret")
            setTimeout(function(){
                $("#updatePresentation").html("Opdater")
            }, 500)

            // remove all present
            // updata config

        })

    }
    self.copy = (name = "",presentationId="") => {
       name = name == "" ? $("#newCopyPresentationName").val(): name;
       presentationId = presentationId == "" ? _presentationId: presentationId;
       let targetID = Math.random().toString(36).substring(7)+Math.random().toString(36).substring(7)+Math.random().toString(36).substring(7);
       if(name == ""){
           alert("Navn mangler")
           return;
       }
        $.post(_ajaxPath+"presentation/copy",{userId:_userId,name:name,presentationId:presentationId,targetID:targetID}, function(res, status) {
            if(res.status == 0) {  AppPcmsError.reg(res.msg); return; }
            else {
               $("#closeCopyModal").click();
                alert("Der er nu oprettet en kopi")
            }
        }, "json");
    }



    self.setPresentationId = (id) => {
      _presentationId = id
    }
    self.removeAllPresent = async (id) => {
         return new Promise( async function(resolve, reject) {
        $.post(_ajaxPath+"presentation/removeById",{id:id}, function(res, status) {
            if(res.status == 0) {  AppPcmsError.reg(res.msg); return; }
            else { resolve(res) }
        }, "json");

        })
    }
    self.updataConfig = async (id) => {
         return new Promise( async function(resolve, reject) {
        var salepersonList = [];
              $("input:checkbox[name=salemanSelect]:checked").each(function(){
                salepersonList.push($(this).val());
              });
                let pdf_front_companyname =  $("#pdf-front-companyname").val();
                let pdf_front_page = $("#pdf-front-page:checked").length > 0 ? "1":"0";
                let pdf_omtanke = $("#pdf-omtanke:checked").length > 0 ? "1":"0";
                let pdf_gaveklubben = $("#pdf-gaveklubben:checked").length > 0 ? "1":"0";
                let pdf_tree = $("#pdf-tree:checked").length > 0 ? "1":"0";
                let pdf_indpak = $("#pdf-indpak:checked").length > 0 ? "1":"0";
                let pdf_back_page = $("#pdf-back-page:checked").length > 0 ? "1":"0";
                let config = {
                  saleperson:salepersonList.join(","),
                  pdf_front_companyname:pdf_front_companyname,
                  pdf_front_page:pdf_front_page,
                  pdf_omtanke:pdf_omtanke,
                  pdf_gaveklubben:pdf_gaveklubben,
                  pdf_tree:pdf_tree,
                  pdf_indpak:pdf_indpak,
                  pdf_back_page:pdf_back_page
                }
            if(salepersonList.length > 2){
                $("#newPresentationError").html("Du har valgt mere end 2 sælgere");
            } else {
                $.post(_ajaxPath+"presentation/updateConfig",{id:id,config:config}, function(res, status) {
                    if(res.status == 0) {  AppPcmsError.reg(res.msg); return; }
                    else { resolve(res) }
                }, "json");
            }
            })
    };

    self.updateSort = () => {
        if(_presentationId != ""){
           let sortList = [];
            $(".presentation-elememt-set" ).each( async function( index ) {
                  sortList.push($( this ).attr("data-id"));
            })
            $.post(_ajaxPath+"presentation/updateSort",{sortlist:sortList.toString(),presentationId:_presentationId}, function(res, status) {
                  if(res.status == 0) {   }

            }, "json");

        }

    }



    self.buildPresentation = (data) => {
        $(".edit-allprice").show();
        let html = [" "];

        data.data.forEach(async ele => {
                  html.push("<div class='presentation-list' ><div class='presentation-list-element' data-id='"+ele.id+"'>"+ele.name+"</div><i data-id='"+ele.id+"' class='far fa-trash-alt presentation-list-delete'></i></div>")
        })

        $(".presentationListModal").html(html);
        $(".presentation-list-element").click( async function(){

           $(".presentation-set").find("li").remove();
           let id = $(this).attr("data-id");
           $("#cart-presentation-name2").html("Præsentation: " +$(this).html()+"<hr>");

           $("#cart-presentation-link").html("Link til præsentation: <a target='_blank' href='/presentation/slideshow.php?token="+id+"'><u> åben </u></a><br><textarea rows='2' style='width:98%;' readonly onclick='this.select();'>https://system.gavefabrikken.dk/presentation/slideshow.php?token="+id+"</textarea><br><br>" );

           $("#createPresentation").hide();
           $(".closeCart").hide();
           $(".menu-present").hide();
           $(".closePresentation").show();
           AppPcmsPresentation.setPresentationId(id);
           AppPcmsCart.UIinit();
           let res = await AppPcmsCart.loadConfig(id);
           let insert = await AppPcmsCart.insetConfig(res);
           await AppPcmsPresentSetting.loadData(id)
           let list = await AppPcmsCart.loadPresents(id);
        
           await AppPcmsCart.insertPresent(list);


        })
        $(".presentation-list-delete").click( async function(){
            var r = confirm("Er du sikker på du vil slette")
            if(r == true){
               await AppPcmsPresentation.remove($(this).attr("data-id"));
                $(this).parent().hide();
            }
        })
    }
    self.remove = (id) => {
          return new Promise( async function(resolve, reject) {
                  $.post(_ajaxPath+"presentation/remove",{id:id}, function(res, status) {
                      if(res.status == 0) {  AppPcmsError.reg(res.msg); return; }
                      else { resolve(res) }
                  }, "json");
          });
    }

    self.updatePresens = (presentation_id) => {
       return new Promise( async function(resolve, reject) {
              var presentationSlide = [];

              $('.presentation-elememt-set').each(function(ele) {
                        presentationSlide.push($(this).attr("data-id"))
              });
              let pdf = new AppPcms.pdf;
              presentationSlide.forEach(async function (value, i) {
                    await pdf.saveSlide(value,presentation_id,i)

              })
              resolve();
       })
    }
    self.create = () => {
         return new Promise( async function(resolve, reject) {

              let presentation_id = Math.random().toString(36).substring(7)+Math.random().toString(36).substring(7)+Math.random().toString(36).substring(7);
              var presentationSlide = [];

              $('.presentation-elememt-set').each(function(ele) {
                        presentationSlide.push($(this).attr("data-id"))
              });
              await AppPcmsPresentation.saveConfig(presentation_id);
              let pdf = new AppPcms.pdf;
              presentationSlide.forEach(async function (value, i) {
                    await pdf.saveSlide(value,presentation_id,i)

              })
              resolve(presentation_id)
    })
    }
    self.saveConfig = (presentation_id) => {
        var presentation_id = presentation_id;
        return new Promise(function(resolve, reject) {
               let author_id = _userId;
            let presentation_name = $("#newPresentationName").val()
            var salepersonList = [];
              $("input:checkbox[name=salemanSelect]:checked").each(function(){
                salepersonList.push($(this).val());
              });
                let pdf_front_companyname =  $("#pdf-front-companyname").val();
                let pdf_front_page = $("#pdf-front-page:checked").length > 0 ? "1":"0";
                let pdf_omtanke = $("#pdf-omtanke:checked").length > 0 ? "1":"0";
                let pdf_gaveklubben = $("#pdf-gaveklubben:checked").length > 0 ? "1":"0";
                let pdf_tree = $("#pdf-tree:checked").length > 0 ? "1":"0";
                let pdf_indpak = $("#pdf-indpak:checked").length > 0 ? "1":"0";
                let pdf_back_page = $("#pdf-back-page:checked").length > 0 ? "1":"0";
                let config = {
                  saleperson:salepersonList.join(","),
                  pdf_front_companyname:pdf_front_companyname,
                  pdf_front_page:pdf_front_page,
                  pdf_omtanke:pdf_omtanke,
                  pdf_tree:pdf_tree,
                  pdf_indpak:pdf_indpak,
                  pdf_gaveklubben:pdf_gaveklubben,
                  pdf_back_page:pdf_back_page
                }
            if(salepersonList.length > 2){
                $("#newPresentationError").html("Du har valgt mere end 2 sælgere");
            } else {
                $.post(_ajaxPath+"presentation/create",{lang:_lang,id:presentation_id,author_id:author_id,presentation_name:presentation_name,config:config}, function(res, status) {
                    $("#newPresentationError").html("");
                    if(res.status == 0) {  AppPcmsError.reg(res.msg); return; }
                    else { resolve(res) }
                }, "json");
            }
         })

    }
    self.loadOverview = () => {
       return new Promise(function(resolve, reject) {
       $.post(_ajaxPath+"presentation/getAll",{userId:_userId}, function(res, status) {
            if(res.status == 0) {  AppPcmsError.reg(res.msg); return; }
            else { resolve(res) }
        }, "json");
        })

    }


});

