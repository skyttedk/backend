var companyImport = {
  _importId:"",
  _exsistingCompanyId:"",
  _data:{},
  _index:"2204",
 _cardType: "",
 _standby:"",

  getTotal : function(){
       // ajax({},"company/getCompanyOrderCount","companyImport.getTatalResponse","");


  },
  getTatalResponse : function(response){


     //companyImport.getCompanyImports();
  },
  getCompanyImports : function(){
        $("#infoContainer").html("");
        _exsistingCompanyId = "";

        var obj = {};

        if(is_norge == "norge"){
            obj = {"norge":"norge"};
        }

        ajax(obj,"company/getCompanyImports","companyImport.getCompanyImportsResponse","");
  },
  getCompanyImportsResponse : function(response){
      // formular data vises
       console.log(response);

      if(response.data.companyorders.length != 0){
      if(response.data.companyorders[0].imported == "1"){
       alert("fejl, kontakt kim og giv ham dette id: "+response.data.companyorders[0].id)
     } else {
        this._standby = "";
        if(this._standby = response.data.companyorders[0].standby == "1"){
             this._standby = "Har været sat til: håndteres senere";
        } else {
             this._standby = "";
        }



      $("#mainContainer").html("");
      if(response.status == "1"){
         if(response.data.companyorders[0] != ""){
             this._importId = response.data.companyorders[0].id;
             this._data = response.data.companyorders[0];
             this._cardType = response.data.companyorders[0].shop_name;

/*
             if(response.data.companyorders[0].shop_name == "Julegavekortet"){

             } else if(response.data.companyorders[0].shop_name == ""){

             }
*/
            this.buildFormJgk();


             $.each( this._data, function( key, value ) {
                  if(key == "shipment_method"){
                    if(value == "e"){
                      value = "email"
                    } else {
                      value = "kort"
                    }
                  }

                  $("#"+key).val(value)
             });

             var combidata = this._data.noter.split("#@#");

             if(combidata[1] == "spdeal"){
                 $("#spdeal").val("Ja");
             } else {
                 $("#spdeal").val("Nej");

             }

             if(combidata.length > 3){
                $("#spdealTxt").val(combidata[3])
             }
             $("#ean").val(combidata[2]);



             $("#noter").val(combidata[0]);
             $("#shop_name").html(this._data.shop_name);
             var cvr = response.data.companyorders[0].cvr;
             this.getAdressOnCvr(cvr);
         } else {

               $("#mainContainer").html("<h3>Ikke flere ordre</h3>");
         }


      } else {
        alert("Der er sket en fejl");
      }
      $(".godkend").show();
      $(".afvis").show();
      $(".later").show()
    }
    } else {
       $("#mainContainer").html("<h3>Ikke flere ordre</h3>");
      $(".godkend").hide();
      $(".afvis").hide();
      $(".later").hide()
    }


  },
  showOrderCount : function(response){
    if(response.status == 1){
      $("#orderToGo").html(response.data.count)
    }
  },
  getAdressOnCvr : function(cvr){

    ajax({"text":cvr},"company/searchGiftCertificateCompanyCSV","companyImport.getAdressOnCvrResponse","");
  },
  getAdressOnCvrResponse : function(response){
    //$("#infoContainer").html(JSON.stringify(response))
   // alert(response.data.result.length)

   var html ="<table>";
    for(var i=0;response.data.result.length > i;i++){
      html+="<tr><td> <input type=\"radio\" name=\"companyList\" value=\""+response.data.result[i].id+"\"></td><td>";
        html+="<table width=300 style=\"border:1px solid black; margin-button:10px;\">"
        html+="<tr><td colspan=2><b>Faktura Adresse</b></td></tr>";
        html+="<tr><td width=100>Firma</td><td>"+response.data.result[i].name+"</td></tr>";
        html+="<tr><td>Cvr</td><td>"+response.data.result[i].cvr+"</td></tr>";
        html+="<tr><td>Vej</td><td>"+response.data.result[i].bill_to_address+"</td></tr>";
        html+="<tr><td>Postnr.</td><td>"+response.data.result[i].bill_to_postal_code+"</td></tr>";
        html+="<tr><td>By</td><td>"+response.data.result[i].bill_to_city+"</td></tr>";

        html+="<tr><td colspan=2><b>Leveringsadresse</b></td></tr>";
        html+="<tr><td>Virksonhed</td><td>"+response.data.result[i].ship_to_company+"</td></tr>";
        html+="<tr><td>Vej</td><td>"+response.data.result[i].ship_to_address+"</td></tr>";
        html+="<tr><td>Postnr.</td><td>"+response.data.result[i].ship_to_postal_code+"</td></tr>";
        html+="<tr><td>By</td><td>"+response.data.result[i].ship_to_city+"</td></tr>";
        html+="</table>"


      html+="</td></tr>"
    }
    html+="<tr><td> <input type=\"radio\" name=\"companyList\" checked value=\"createNew\"></td><td><div width=300 style=\"border:1px solid black; margin-button:10px; padding:10px;\">Opret som ny virksomhed</div></td></tr>"
    html+="</table>"

       $("#infoContainer").html(html)
         var obj = {};

        if(is_norge == "norge"){
            obj = {"norge":"norge"};
        }
        ajax(obj,"company/getCompanyImportCount","companyImport.showOrderCount","");
    /*
    var html = "<table>";
    $.each( response.data.result[], function( key, value ) {
                $("#"+key).val(value)
    });
    */


  },
  setToStandby : function(){
          $(".godkend").hide();
        $(".afvis").hide();
        $(".later").hide()

    ajax({"company_import_id":this._importId},"company/standbyCompanyImport","companyImport.setToStandbyResponse","");



  // ajax({"user_id":this._index},"shop/removeShopUser","companyImport.go","");
  },
 /*
  go : function(){
     if(this._index < 2293){
        this._index++
        companyImport.setToStandby();
     } else {
       alert("done")
     }

  },

 */

  setToStandbyResponse : function(response){
    this.getCompanyImports();
  },
  setToDelete : function(){
       if(confirm("Vil du afvise!") == true){

        $(".godkend").hide();
        $(".afvis").hide();
        $(".later").hide()
        ajax({"company_import_id":this._importId},"company/deleteCompanyImport","companyImport.setToDeleteResponse","");
       }
  },
  setToDeleteResponse : function(){
    this.getCompanyImports();
  },
  setToCreateOrder : function(){
      var spdeal = "";
      if($("#spdeal").val() == "Ja"){
        spdeal = "spdeal";
      }
      var  noter = $("#noter").val()+"#@#"+spdeal+"#@#"+$("#ean").val()+"#@#"+$("#spdealTxt").val();

      this._data.name  = $("#name").val();
      this._data.cvr  = $("#cvr").val();
      this._data.bill_to_address  = $("#bill_to_address").val();
      this._data.bill_to_postal_code   = $("#bill_to_postal_code").val();
      this._data.bill_to_city   = $("#bill_to_city").val();
      this._data.ship_to_company   = $("#ship_to_company").val();
      this._data.ship_to_address   = $("#ship_to_address").val();
      this._data.ship_to_postal_code   = $("#ship_to_postal_code").val();
      this._data.ship_to_city    = $("#ship_to_city").val();
      this._data.contact_name    = $("#contact_name").val();
      this._data.contact_phone   = $("#contact_phone").val();
      this._data.contact_email   = $("#contact_email").val();
      this._data.expire_date   = $("#expire_date").val();
      this._data.quantity     = $("#quantity").val();
      this._data.noter        = noter;
      ajax(this._data,"company/updateCompanyImport","companyImport.setToCreateOrderResponse","");
  },
  setToCreateOrderResponse : function(response){
    if(response.status == "1"){
            companyImport.getCompanyImports();
    } else {
        alert("Der er sket en fejl, tryk F5 / opdaterere browseren og start forfra")

    }
    $(".godkend").show();
        $(".afvis").show();
        $(".later").show()

  },
  doCreateOrder : function(){
        $(".godkend").hide();
        $(".afvis").hide();
        $(".later").hide()
        var exsistingCompanyId = "";
        if($('input[name=companyList]:radio:checked').val() != "createNew") {
           exsistingCompanyId = $('input[name=companyList]:radio:checked').val();
        }
        ajax({"company_import_id":this._importId,"company_id":exsistingCompanyId },"company/createCompanyOrder","companyImport.doCreateOrderResponse","");

  },
  doCreateOrderResponse : function(response){

        if(response.status=="0"){
              alert("Der er sket en fejl, tryk F5 / opdaterere browseren og start forfra")
        } else{
            this.getCompanyImports();
        }
        $(".godkend").show();
        $(".afvis").show();
        $(".later").show()

  },
  buildFormJgk : function(){
        html="";
        html+= "<h3>Order der venter på godkendelse: <span id=\"orderToGo\"></span></h3><hr />"
        html+=  this._standby+"<br />";
        html+= "<h1 id=\"shop_name\"></h1>"
        html+= "<table id=\"formDataToShow\" width=100% border=1 >"
        html+="<tr><td colspan=2><b>Faktura Adresse</b></td></tr>";
        html+="<tr><td width=100>Firma</td><td><input size=\"35\" id=\"name\" type=\"text\" /></td></tr>";
        html+="<tr><td>Cvr</td><td><input id=\"cvr\" size=\"35\" type=\"text\" /></td></tr>";
        html+="<tr><td>EAN nummer</td><td><input id=\"ean\" size=\"35\" type=\"text\" /></td></tr>";
        html+="<tr><td>Vej</td><td><input size=\"35\" id=\"bill_to_address\" type=\"text\" /></td></tr>";
        html+="<tr><td>Postnr.</td><td><input size=\"35\" id=\"bill_to_postal_code\" type=\"text\" /></td></tr>";
        html+="<tr><td>By</td><td><input size=\"35\" id=\"bill_to_city\" type=\"text\" /></td></tr>";

        html+="<tr><td colspan=2><b>Leveringsadresse</b></td></tr>";
        html+="<tr><td>Virksonhed</td><td><input size=\"35\" id=\"ship_to_company\" type=\"text\" /></td></tr>";
        html+="<tr><td>Vej</td><td><input size=\"35\" id=\"ship_to_address\" type=\"text\" /></td></tr>";
        html+="<tr><td>Postnr.</td><td><input size=\"35\" id=\"ship_to_postal_code\" type=\"text\" /></td></tr>";
        html+="<tr><td>By</td><td><input size=\"35\" id=\"ship_to_city\" type=\"text\" /></td></tr>";

        html+="<tr><td colspan=2><b>Kontrakt person</b></td></tr>";
        html+="<tr><td>Navn</td><td><input size=\"35\" id=\"contact_name\" type=\"text\" /></td></tr>";
        html+="<tr><td>Tlf.nr.</td><td><input size=\"35\" id=\"contact_phone\" type=\"text\" /></td></tr>";
        html+="<tr><td>Email</td><td><input size=\"35\" id=\"contact_email\" type=\"text\" /></td></tr>";

        html+="<tr><td colspan=2><b>Kort information</b></td></tr>";
        html+="<tr><td>Udløbsdata</td><td><input size=\"35\" id=\"expire_date\" type=\"text\" /></td></tr>";

        html+="<tr><td>Gaveindpakning</td><td><input size=\"35\" id=\"giftwrap\" type=\"text\"   /></td></tr>";

        if(this._cardType == "24Gaver" || this._cardType == "Julegavekortet NO") {
            html+="<tr><td>Kort værdi</td><td><input size=\"35\" id=\"value\" type=\"text\" disabled  /></td></tr>";
        }
        html+="<tr><td>Sendes som</td><td><input size=\"35\" id=\"shipment_method\" disabled type=\"text\" /></td></tr>";


        html+="<tr><td>Antal</td><td><input size=\"35\" id=\"quantity\" type=\"text\" /></td></tr>";
        html+="<tr><td>Sælger</td><td><input type=\"text\" disable id=\"salesperson\" /></td></tr>";
        html+="<tr><td>Noter</td><td></label><textarea  cols=\"50\" rows=\"7\" id=\"noter\"></textarea></td></tr>";
        html+="<tr><td>Special aftale text</td><td><textarea  cols=\"50\" rows=\"7\" id=\"spdealTxt\"></textarea></td></tr>";

        html+="<tr><td colspan=2><button class=\"update\" onclick=\"companyImport.setToCreateOrder()\">Opdatere</button></td></tr>";
        html+= "</table>";
        $("#mainContainer").html(html)
    },
       loadCompanyList:function(){
             ajax({},"company/getCompanyImportLast50Approved","companyImport.loadCompanyListResponse","");
    },
    loadCompanyListResponse:function(response){
        var html = "";

                        // alert(response.data.companyorders.length)

      $.each( response.data.companyorders, function( key, value ) {

            var deleveryWeek;

            if(value.expire_date == "2016-10-28"){ deleveryWeek = "Uge 48"; }
            if(value.expire_date == "2016-11-06"){ deleveryWeek = "Uge 48"; }

            if(value.expire_date == "2016-11-11"){ deleveryWeek = "Uge 50"; }
            if(value.expire_date == "2016-11-20"){ deleveryWeek = "Uge 50"; }

            if(value.expire_date == "2016-12-31"){ deleveryWeek = "Uge 4 (2017)"; }





            html+= "<h3>"+value.shop_name+"</h3>";
            html+= "<b>"+deleveryWeek+"</b>";

            html+= "<table width=300 border=0>"

            html+="<tr> <td><b>Ordernr.:</b></td> <td><b>"+value.order_no+"</b></td> <td></td> </tr>";

            html+="<tr> <td>Virksomhed:</td> <td>"+value.company_name+"</td> <td></td> </tr>";
            html+="<tr> <td>Cvr:</td> <td>"+value.cvr+"</td> <td></td> </tr>";
            html+="<tr> <td>Vej:</td> <td>"+value.ship_to_address+"</td> <td></td> </tr>";
            html+="<tr> <td>Postnummer:</td> <td>"+value.ship_to_postal_code+"</td> <td></td> </tr>";
            html+="<tr> <td>By:</td> <td>"+value.ship_to_city+"</td> <td></td> </tr>";
            html+="<tr> <td></td> <td><br /></td> <td></td> </tr>";
            html+="<tr> <td>Kontaktperson:</td> <td>"+value.contact_name+"</td> <td></td> </tr>";
            html+="<tr> <td>E-mail:</td> <td>"+value.contact_email+"</td> <td></td> </tr>";
            html+="<tr> <td>Tlf.nr.:</td> <td>"+value.contact_phone+"</td> <td></td> </tr>";
            html+= "</table>";
            html+="<br /><table  width=300 border=0>";
            html+="<tr> <td width=100>Gavekort</td> <td width=100>Start</td> <td width=100>Slut</td> <td></td>  </tr>";
            html+="<tr> <td>Antal: "+value.quantity+"</td> <td>"+value.certificate_no_begin+"</td> <td>"+value.certificate_no_end+"</td> </tr>";
            html+="</table><div class=\"footer\"></div><hr /></div>";

      });
      $("#orderHistory").html(html);
      }



}
