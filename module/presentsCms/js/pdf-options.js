AppPcms.pdfOptions= (function () {
    self = this;

    self.init = () => {

      this.buildSaleman();

    }
    self.buildSaleman = () => {
      var html = "<table width= 100%>";
      $.post(_ajaxPath+"saleperson/getAll",{lang:_lang}, function(res, status) {
                if(res.status == 0) {  AppPcmsError.reg(res.msg); return; }
                else {

            $.each(res.data, function(index, value)
            {

              html += "<tr id='salepersonId_"+value.id+"' style='border-bottom:1pt solid black;'><td><img width=90 src='https://system.gavefabrikken.dk/presentation/workers/"+value.img+"' /></td>";
              html += "<td><div>"+value.name+"</div><div>"+value.title+"</div><div>"+value.tel+"</div><div>"+value.mail+"</div></td>";
              html += "<td><input  type='checkbox' name='salemanSelect' value='"+value.id+"'></td>";
              html += "</tr>";
            }
          );
          let htmlOptions = "";
          htmlOptions+= '<div class="custom-control custom-checkbox"><input type="checkbox" class="custom-control-input" id="pdf-front-page"> <label class="custom-control-label" for="pdf-front-page">Forside</label><input id="pdf-front-companyname" style="margin-left:4px;" type="text" placeholder="Firma navn" /></div>';
          htmlOptions+= '<div class="custom-control custom-checkbox"><input type="checkbox" class="custom-control-input" id="pdf-omtanke"> <label class="custom-control-label" for="pdf-omtanke">Gaver med omtanke</label></div>';
          htmlOptions+= '<div class="custom-control custom-checkbox"><input type="checkbox" class="custom-control-input" id="pdf-indpak"> <label class="custom-control-label" for="pdf-indpak">Årets juleindpakning</label></div>';
          htmlOptions+= '<div class="custom-control custom-checkbox"><input type="checkbox" class="custom-control-input" id="pdf-tree"> <label class="custom-control-label" for="pdf-tree">Plant træer med GaveFabrikken</label></div>';
          htmlOptions+= '<div class="custom-control custom-checkbox"><input type="checkbox" class="custom-control-input" id="pdf-gaveklubben"> <label class="custom-control-label" for="pdf-gaveklubben">Gaveklubben</label></div><hr>';

          htmlOptions+= '<div class="custom-control custom-checkbox"><input type="checkbox" class="custom-control-input" id="pdf-back-page">  <label class="custom-control-label" for="pdf-back-page">Bagside (Husk at vælge max 2 personer fra nedenstående liste)</label></div>';
          $(".salepersonList").html(htmlOptions+html+"</table>");

          $("[name='salemanSelect']").click(AppPcmsPdfOptions.selectSalemanUI);
                 }
      }, "json");

    };
    self.selectSalemanUI = (ele) => {

      if($("[name='salemanSelect']:checked").length > 2){
        alert("du kan kun afkrydse 2, du har valgt: "+$("[name='salemanSelect']:checked").length)
      }
      let id = ele.target.value;
      $("#salepersonId_"+id).toggleClass("selected");


    };



})