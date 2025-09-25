var _pt_saleperson;
var _demoUser
var _pt_shopName;
var _pt_frontpage;
var _pt_mere;
var _pt_tree;
var _pt_bag;
var _pt_voucher_page;
var _pt_saleperson_page;
var _pt_layout_language;
var _pt_brands_united;
var _pt_green_layout;
var _pt_show_frontpage_design;
var _shop_token;
var _id = "";
var _shopId = "";
var _companyID ="";

var _ptPdf = "";
var       _desc1_inshop = "";
var         _desc2_inshop = "";
var         _desc3_inshop = "";
var         _desc4_inshop = "";
var         _desc5_inshop = "";
var         _compamyData;
var        _localisation;


var _datoSat = false;
var _periodeStartEndControl = {};
var _hasLogoCheck = false;
var _hasWelcomeTxtCheck = false;
var _shop_mode;
var company = {

    setLocalisation : function(){
        _localisation =  $(".localisation.localisationSelected").find("input").attr("data-id");
    },

    deleteShop : function(){
      var r = prompt("Hvis du er sikker paa du vil slette shoppen tryk OK" );

     if(r==""){
       var random = Math.floor(Math.random() * 1000000001);
       var formData = {id:_shopId,deleted:1,link:random}
        ajax(formData,"shop/update","company.deleteShopResponse");
     }
     //
    },
    deleteShopResponse : function(res){
        alert("Shop slettet")
        window.location.href = "https://system.gavefabrikken.dk//gavefabrikken_backend/index.php?rt=mainaa&sysid=40";
    },
    testLoginResponse : function(){

    },
    testPresentSelection : function(){
     //   ajax({},"shop/readCompanyShopsSimple","company.testPresentSelectionResponse");
    },
    testPresentSelectionResponse : function(){

    },


    search : function(){
        var all = "";
       if( $('#sogAllShops').is(":checked")){
             all = "all"
            }


        ajax({"name":$("#menuSearch").val(),all:all},"shop/searchCompanyShopsSimple","company.searchResponse");

    },
    setTinyActiveOnStamdata:function(){
          /*
          myDropzone.off(); //removes all listeners attached with Emitter.on()
          myDropzone.destroy()
          myDropzone = new Dropzone(".dropzone", { url: "index.php?rt=upload/logo"});
          */
          _dropTarget = "forside_tab"
    if($("#shopDescriptionTabs").find(".mce-panel").length == 0){
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

       } else {
           //alert("er startat ikke startat")
       }

    },

    searchResponse : function(responce)
    {
        console.log(responce)
        var html = "";
        for(var i=0; responce.data.shops.length > i;i++){
                html+= "<div onclick=\"company.edit(this)\" class=\"companyMenuItem\" data-id=\""+responce.data.shops[i].id+"\">"+responce.data.shops[i].name+"</div>";
        }
        if(html == ""){
            html = "<h2>Intet resultat</h2>";
        }
        $("#companyList").html(html);

        $("#companyList").show();
        if($("#menuSearch").val() != "" ){
        //    company.doSearchMenu($("#menuSearch").val())
        } else {
           $('.companyMenuItem').show();
        }
    },
    doSearchMenu : function(txt) {
        txt = txt.toLowerCase();
        $('.companyMenuItem').each(function(i, obj) {
            element = $(obj).html();
            element = element.toLowerCase();
            if(element.indexOf(txt) > -1){
                $(obj).show();
            } else {
                $(obj).hide();
            }
        });

    },
    init : function()
    {
    if(_editShopID){
        ajax({shopId:_editShopID},"shop/readCompanyShopsSimple","company.buildMenuList");
    }
    //console.log("inint")

    },
    buildMenuList : function(responce)
    {
        console.log("d4")
        var html = "";
        for(var i=0; responce.data.shops.length > i;i++){
                html+= "<div onclick=\"company.edit(this)\" class=\"companyMenuItem\" data-id=\""+responce.data.shops[i].id+"\">"+responce.data.shops[i].name+"</div>";
        }
        $("#companyList").html(html);
        var currentLocation = window.location;
        currentLocation = currentLocation.toString();


        if(_editShopID != ""){
                _shopMode = true; // ligger i main
                company.searchClose();
                var html ="<button class=\"button\" onclick=\"company.save('"+_editShopID+"')\" style=\"background-color: #4CAF50; color:white; font-size:14px; padding:5px;\" >Opdatere Valgshop</button>"
                $("#trailContainer").html(html)
                ajax({},"shop/show","company.editShow","html");
        } else if(currentLocation.indexOf("#gave") != -1 ){
            gaveAdmin.show()
        }
        else if(currentLocation.indexOf("#system") != -1 ){
             systemUser.show()
        }




      //     $(".safeLayerTimer").hide();
       //     $(".safeLayer").hide();
    },
    doShowShopData : function(){
       var data = {};
       data['systemuser_id'] = _sysId;

       ajax(data,"tab/loadShopPermission","","#shopTabsItems");
    },



    searchClose: function()
    {
      $("#companyList").hide();
    },
    show : function(id)
    {
        $("#companyList").hide();

    },
    createNew : function()
    {
        var html ='<button class="button" onclick="company.saveNew(\'\')" style="background-color: #4CAF50; color:white; font-size:14px; padding:5px; ">Gem Valgshop</button>'
         $("#trailContainer").html(html)
         ajax({},"shop/showNew","","#content");
    },
    saveNew : function()
    {
            if(show_to_saleperson == true){
                if(
                    $("#shopName").val() == "" ||
                    $("#shopCVR").val() == "" ||
                    $("#shopKontakt").val() == "" ||
                    $("#shopTelefon").val() == "" ||
                    $("#shopEmail").val() == "" ||
                    $("#saleperson").val() == ""){
                    alert("Et eller flere felter mangler at blive udfyldt!")
                }


            }

            var shop1 = {};
            const thisYear = new Date().getFullYear();
            shop1["name"] = $("#shopName").val()+"-"+thisYear;
            shop1["link"] = $("#shopLink").val();
            shop1["pt_shopName"] = $("#shopName").val();
            shop1["paper_settings"] = '{"isEnabled":"false","isEditable":"false","entityType":"worker","isImported":"0","shopID":"0"}';

            var company1 = {};
            company1["name"] =         $("#shopName").val()+"-"+thisYear;
            company1["cvr"] =          $("#shopCVR").val();
            company1["so_no"] =        $("#so_no").val();

            company1["username"] =     $("#shopUsername").val();
            company1["password"] =      $("#shopPassword").val();
            company1["contact_name"] =   $("#shopKontakt").val();
            company1["contact_phone"] =    $("#shopTelefon").val();
            company1["contact_email"] =   $("#shopEmail").val();
            company1["sales_person"] =   $("#saleperson").val();


            var descriptions1 = [];
            _desc1_inshop = "";

            let descriptionDK = `PHA+PHN0cm9uZz5WZWxrb21tZW4gdGlsICZyZHF1bztGSVJNQXh4eHgmcmRxdW87IGp1bGVnYXZlc2hvcCAyMDI1PC9zdHJvbmc+Jm5ic3A7PC9wPgo8cD5JIHNhbWFyYmVqZGUgbWVkIEdhdmVGYWJyaWtrZW4gaGFyIHZpIHVkdmFsZ3Qgbm9nbGUgZmxvdHRlIGdhdmVyLCA8YnIgLz4gc29tIGR1IGZyaXQga2FuIHYmYWVsaWc7bGdlIGltZWxsZW0uJm5ic3A7PC9wPgo8cD5EdSBsb2dnZXIgaW5kIHZlZCBhdCB0YXN0ZSBkaW4gbWFpbCBpIGJlZ2dlIGZlbHRlciBoZXJ1bmRlci4mbmJzcDs8YnIgLz5EdSBsb2dnZXIgaW5kIHZlZCBhdCB0YXN0ZSBkaW4gbWFpbCBpJm5ic3A7YnJ1Z2VybmF2biBvZyBsJm9zbGFzaDtubnVtbWVyIGkgYWRnYW5nc2tvZGUuJm5ic3A7PC9wPgo8cD5IZXJlZnRlciBrYW4gZHUgdiZhZWxpZztsZ2UgaW1lbGxlbSBkZSB2aXN0ZSBqdWxlZ2F2ZXIgZnJlbSB0aWwgZGVuIFhYLiBva3RvYmVyL25vdmVtYmVyL2RlY2VtYmVyIDIwMjUuJm5ic3A7PC9wPgo8cD5GcmVtIHRpbCBkZWFkbGluZSBoYXIgZHUgbXVsaWdoZWQgZm9yIGF0IGZvcnRyeWRlLCBvZyB2JmFlbGlnO2xnZSBlbiBhbmRlbiBnYXZlLCA8YnIgLz4gYmxvdCB2ZWQgYXQgbG9nZ2UgaW5kIGlnZW4gb2cgZm9yZXRhZ2UgZXQgbnl0IGdhdmV2YWxnLiZuYnNwOzwvcD4KPHA+SGFyIGR1IGlra2UgdmFsZ3QgZW4gZ2F2ZSwgdmlsIGR1IGF1dG9tYXRpc2sgbW9kdGFnZSBYWFhYWFhYLiZuYnNwOzwvcD4KPHA+PHN0cm9uZz5SaWd0aWcgZ29kIGZvcm4mb3NsYXNoO2plbHNlIG9nIGdsJmFlbGlnO2RlbGlnIGp1bDwvc3Ryb25nPjwvcD4=`;
            let descriptionEN = `PHA+PHN0cm9uZz5XZWxjb21lIHRvICJGSVJNQXh4eHgiIENocmlzdG1hcyBnaWZ0IHNob3AgMjAyMjxiciAvPjxiciAvPjwvc3Ryb25nPldlIGhhdmUsIGluIGNvbGxhYm9yYXRpb24gd2l0aCBHYXZlRmFicmlra2VuLCBzZWxlY3RlZCBncmVhdCBnaWZ0cywgd2hpY2ggeW91IGNhbiBmcmVlbHkgY2hvb3NlIGZyb20uPGJyIC8+PGJyIC8+TG9nIGluIGJ5IGVudGVyaW5nIHlvdXIgbWFpbCBpbiBib3RoIGZpZWxkcyBiZWxvdy48YnIgLz5Mb2cgaW4gYnkgZW50ZXJpbmcgeW91ciBtYWlsIGluIHVzZXJuYW1lIGFuZCBwYXNzd29yZCBpbiBwYXNzd29yZC48YnIgLz48YnIgLz5Zb3UgY2FuIGNob29zZSBmcm9tIHRoZSBDaHJpc3RtYXMgcHJlc2VudHMgc2hvd24gdW50aWwgT2N0b2Jlci9Ob3ZlbWJlci9EZWNlbWJlciB4eHRoIDIwMjIuPGJyIC8+PGJyIC8+VW50aWwgZGVhZGxpbmUsIHlvdSBhcmUgZnJlZSB0byBtYWtlIGNoYW5nZXMgdG8geW91ciBzZWxlY3Rpb24sIGp1c3QgbG9nIGluIGFnYWluIHRvIG1ha2UgeW91ciBuZXcgY2hvaWNlLjxiciAvPjxiciAvPklmIHlvdSBkb24mcnNxdW87dCBzZWxlY3QgYSBnaWZ0LCB5b3Ugd2lsbCBhdXRvbWF0aWNhbGx5IHJlY2VpdmUgWFhYWFhYWC48YnIgLz5JZiB5b3UgZG9uJnJzcXVvO3Qgc2VsZWN0IGEgZ2lmdCwgYSBkb25hdGlvbiB3aWxsIGF1dG9tYXRpY2FsbHkgYmUgZ2l2ZW4uPGJyIC8+PGJyIC8+PHN0cm9uZz5NZXJyeSBDaHJpc3RtYXM8L3N0cm9uZz48L3A+`;
            let descriptionSE = 'PHA+PHN0cm9uZz5WJmF1bWw7bGtvbW1lbiB0aWxsIFhYWEZJUk1BWFhYWCBqdWxrbGFwcHNidXRpayAyMDIyPC9zdHJvbmc+PC9wPgo8cD4mbmJzcDs8L3A+CjxwPkkgc2FtYXJiZXRlIG1lZCBQcmVzZW50Qm9sYWdldCBoYXIgdmkgdmFsdCB1dCBuJmFyaW5nO2dyYSBmaW5hIHByZXNlbnRlciBzb20gZHUgZnJpdHQga2FuIHYmYXVtbDtsamEgbWVsbGFuLiZuYnNwOzwvcD4KPHA+RHUgbG9nZ2FyIGluIGdlbm9tIGF0dCBhbmdlIGRpbiBlLXBvc3RhZHJlc3MgaSBiJmFyaW5nO2RhIGYmYXVtbDtsdGVuIG5lZGFuLjxiciAvPkR1IGxvZ2dhciBpbiBnZW5vbSBhdHQgYW5nZSBkaW4gZS1wb3N0YWRyZXNzIHNvbSBhbnYmYXVtbDtuZGFybmFtbiBvY2ggZGl0dCBsJm91bWw7bmVudW1tZXIgc29tIGwmb3VtbDtzZW5vcmQuJm5ic3A7PC9wPgo8cD5OdSBrYW4gZHUgdiZhdW1sO2xqYSBibGFuZCBkZSB2aXNhZGUganVsa2xhcHBhcm5hIGZyYW0gdGlsbCBYWC5YWC4yMDIyLiZuYnNwOzwvcD4KPHA+RnJhbSB0aWxsIGRlYWRsaW5lIGthbiBkdSAmYXJpbmc7bmdyYSBkaWcgb2NoIHYmYXVtbDtsamEgZW4gYW5uYW4gcHJlc2VudCBnZW5vbSBhdHQgYmFyYSBsb2dnYSBpbiBpZ2VuIG9jaCB2JmF1bWw7bGphIGVuIG55IGcmYXJpbmc7dmEuJm5ic3A7PC9wPgo8cD5PbSBkdSBpbnRlIHYmYXVtbDtsamVyIGVuIGcmYXJpbmc7dmEgZiZhcmluZztyIGR1IGF1dG9tYXRpc2t0IFhYWFhYWFguJm5ic3A7PC9wPgo8cD5PbSBkdSBpbnRlIHYmYXVtbDtsamVyIGVuIGcmYXJpbmc7dmEga29tbWVyIHYmYXVtbDtyZGV0IHNvbSBtb3RzdmFyYXIgZGluIGcmYXJpbmc7dmEgYXR0IGRvbmVyYXMgdGlsbCBYWFhYPC9wPgo8cD4mbmJzcDs8L3A+CjxwPjxzdHJvbmc+VmkgJm91bWw7bnNrYXIgZGlnIG15Y2tldCBuJm91bWw7amUgb2NoIGVuIGdvZCBqdWwhPC9zdHJvbmc+PC9wPg==';

        var pageHeadlineDa = "Årets julegave";
        var pageHeadlineEn = "Present of the year";
        var pageHeadlineDe = "";
        var pageHeadlineNo = "Årets julegave";
        var pageHeadlineSv = "Årets julklapp";

            descriptions1.push({'id':_desc1_inshop,'language_id':1,'description': descriptionDK,'headline':pageHeadlineDa});
            descriptions1.push({'id':_desc1_inshop,'language_id':2,'description': descriptionEN,'headline':pageHeadlineEn});
            descriptions1.push({'id':_desc1_inshop,'language_id':3,'description': "###",'headline':pageHeadlineDe});
            descriptions1.push({'id':_desc1_inshop,'language_id':4,'description': "###",'headline':pageHeadlineNo});
            descriptions1.push({'id':_desc1_inshop,'language_id':5,'description': descriptionSE,'headline':pageHeadlineSv});


             var formData = {
                'shop':JSON.stringify(shop1),
                'company':JSON.stringify(company1),
                'descriptions':JSON.stringify(descriptions1)
            };
            shop1 = "";
            company1 = "";
            descriptions1 ="";

            ajax(formData,"shop/createCompanyShop","company.doneCreated");
    },

    save : function(id){
            if($("#shopDescriptionTabs").find(".mce-panel").length == 0){

           var initCount = 0
          tinymce.init({
             setup: function(editor) {
    editor.on('init', function() {

            if(initCount == 4){
                company.doSave(id)
            }
            initCount++;

    });
  },
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

       } else {
            company.doSave(id)
       }

    },


    doSave : function(id)
    {
            $(".safeLayerTimer").show();
            $(".safeLayer").show();
            var shop = {};
            shop['id']                    = id;
            shop['name']                = $("#shopName").val();

         



            var bg =  $(".selectedLogoForside").css('background-image');
            bg = bg.replace('url(','').replace(')','');
            bg = bg.split("/");
            var imgUrl = bg[bg.length-1];
            imgUrl = imgUrl.replace(".jpg\"", "");

                if(_hasLogoCheck == true && imgUrl == ""){
                    ajax({code:"1",shop:shop['name']},"shop/alarm","","");
                    $(".safeLayerTimer").hide();
                    $(".safeLayer").hide();
                    alert("Der er en fejl med logo, der er ikke gemt")
                    return;
                }



            shop['image_path']          =  imgUrl;
            shop['reservation_code']          =  $("#warehouseDropdown").val();


            shop['logo_enabled']        =  "0";
            shop['zoom_enabled']        =  "0";
            shop['language_enabled']    =  "0";
            shop['active']              = "1";
            shop['is_demo']             = "0";
            shop['expire_warning_date'] = "";
            if($("#sendMailToCustomer").val() != ""){
                var str = $("#sendMailToCustomer").val()
                res = str.split("-");
                shop['expire_warning_date'] =  res[2]+"-"+res[1]+"-"+res[0];
            }

            shop['location_attribute_id'] = $('#locationAttributeSelect').val();
            shop['location_type'] = $('#locationType').val();

            shop['blocked'] = "0";
            if($("#nodStopBtn").is(':checked')) { shop['blocked'] = "1"; }
            shop['blocked_text'] =  $("#nodStopText").val();

            if($("#logo_enabled").is(':checked')) { shop['logo_enabled']  = "1"  }
            if($("#zoom_enabled").is(':checked')) { shop['zoom_enabled'] = "1"  }
            if($("#language_enabled").is(':checked')) { shop['language_enabled'] = "1"  }
            if($("#closeShop").is(':checked')) { shop['active'] = "1"  }
            if($("#testShop").is(':checked')) { shop['is_demo']  = "1"  }
            if($("#final_finished").is(':checked')) { shop['final_finished']  = "1"  }


            var language_enabled = {};
            language_enabled['lang_de'] = "0"
            language_enabled['lang_se'] = "0"
            language_enabled['lang_eng'] = "0"
            language_enabled['lang_no'] = "0"
            language_enabled['lang_dk'] = "0"



            if($("#lang_dk").is(':checked')) {
                language_enabled['lang_dk'] = "1";
            }
            if($("#lang_no").is(':checked')) {
               language_enabled['lang_no'] = "1";
            }
            if($("#lang_eng").is(':checked')) {
                language_enabled['lang_eng'] = "1";
            }
            if($("#lang_se").is(':checked')) {
              language_enabled['lang_se'] = "1";
            }
            if($("#lang_de").is(':checked')) {
              language_enabled['lang_de'] = "1";
            }



            shop['language_settings']    =    '{"lang_dk":'+language_enabled['lang_dk']+',"lang_no":'+language_enabled['lang_no']+',"lang_eng":'+language_enabled['lang_eng']+',"lang_se":'+language_enabled['lang_se']+',"lang_de":'+language_enabled['lang_de']+'}'

            shop['start_date']          = "###";
            shop['start_time']          = "";     // Always include time fields, default to empty string
            shop['end_date']            = "###";
            shop['end_time']            = "";     // Always include time fields, default to empty string



            if($("#shopFrom2").val() != ""){
                var str = $("#shopFrom2").val()
                res = str.split("-");
                shop['start_date'] =  res[2]+"-"+res[1]+"-"+res[0];
            }
            if($("#shopFromTime").val() != ""){
                shop['start_time'] = $("#shopFromTime").val() + ":00"; // Add seconds
            }
            if($("#shopTo2").val() != "" ){
                var str = $("#shopTo2").val();
                res = str.split("-");
                shop['end_date'] =  res[2]+"-"+res[1]+"-"+res[0];
            }
            if($("#shopToTime").val() != ""){
                shop['end_time'] = $("#shopToTime").val() + ":59"; // Add seconds, default end of minute
            }
             shop['shipment_date'] = "###";
            if($("#showDeleveryDateOnReceipt").length > 0 && $("#showDeleveryDateOnReceipt").val() != "" ){
                var str = $("#showDeleveryDateOnReceipt").val();
                res = str.split("-");
                shop['shipment_date'] =  res[2]+"-"+res[1]+"-"+res[0];

            }
            // dato fra og til kan ændre andre steder derfor dette tjek
            if (!_periodeStartEndControl.start){
                shop['start_date'] = "stop";
            }
            if (!_periodeStartEndControl.end){
                shop['end_date'] = "stop";
            }



            shop["email_list"]              = $("#periodeMail").val();
            shop['link']                    = $("#shopLink").val();
            shop['subscribe_gaveklubben']   = ($("#activateGaveklubben").is(':checked') ? "1" : "0");


            shop['show_qr'] =  $("#show_qr").is(':checked') ? 1:0;
            shop['show_price'] =  $("#showPresentPrice").is(':checked') ? 1:0;
            shop['show_tree_front'] =  $("#plantTree").is(':checked') ? 1:0;


            if($("#layoutDefault").is(':checked') ){
              shop['login_design'] = 0;
            } else if($("#newLoginDesign").is(':checked') ){
                 shop['login_design'] = 1;
            } else if($("#layoutGuld").is(':checked') ){
               shop['login_design'] = 2;
            }
            else if($("#green2022").is(':checked') ){
                shop['login_design'] = 3;
            }
            else if($("#gold2022").is(':checked') ){
                shop['login_design'] = 4;
            }
            else if($("#design2023").is(':checked') ){
                shop['login_design'] = 10;
            }
            else if($("#sis").is(':checked') ){
                shop['login_design'] = 11;
            }
            else {
                shop['login_design'] = 0;
            }
//            shop['login_design']            = ($("#layoutDefault").is(':checked') ? "0" : "0");

            shop['edit_allowed']            = ($("#allowCustomerToMakeChange").is(':checked') ? "1" : "0");

            var company = {};
            company['name'] =         $("#shopName").val();
            company['cvr'] =          $("#shopCVR").val();
            company["so_no"] =        $("#so_no").val();
            company['username'] =     $("#shopUsername").val();
            company['password'] =      $("#shopPassword").val();
            company['contact_name'] =   $("#shopKontakt").val();
            company['contact_phone'] =    $("#shopTelefon").val();
            company['contact_email'] =   $("#shopEmail").val();
      //      company['sales_person'] =   $("#salesPerson").val();
            company['gift_responsible'] =   $("#giftResponsible").val();


            var pageHeadlineDa = $("#frontpageHeadlineDa").val();
            var pageHeadlineEn = $("#frontpageHeadlineEn").val();
            var pageHeadlineDe = $("#frontpageHeadlineDe").val();
            var pageHeadlineNo = $("#frontpageHeadlineNo").val();
            var pageHeadlineSv = $("#frontpageHeadlineSv").val();




            company['id']            = _companyID;


            var descriptions = [];
            var shopDa = Base64.encode(tinyMCE.get('shopDa').getContent({format : 'HTML'}));
            if(shopDa == "") { shopDa = "###"; }
            descriptions.push({'id':_desc1_inshop,'language_id':1,'description': shopDa,'headline':pageHeadlineDa});

            var shopEn = Base64.encode(tinyMCE.get('shopEn').getContent({format : 'HTML'}));
            if(shopEn == "") { shopEn = "###"; }
            descriptions.push({'id':_desc2_inshop,'language_id':2,'description': shopEn,'headline':pageHeadlineEn});

            var shopDe = Base64.encode(tinyMCE.get('shopDe').getContent({format : 'HTML'}));
            if(shopDe == "") { shopDe = "###"; }
            descriptions.push({'id':_desc3_inshop,'language_id':3,'description': shopDe,'headline':pageHeadlineDe});

            var shopNo = Base64.encode(tinyMCE.get('shopNo').getContent({format : 'HTML'}));
            if(shopNo == "") { shopNo = "###"; }
            descriptions.push({'id':_desc4_inshop,'language_id':4,'description': shopNo,'headline':pageHeadlineNo});

            var shopSv = Base64.encode(tinyMCE.get('shopSv').getContent({format : 'HTML'}));
            if(shopSv == "") { shopSv = "###"; }
            descriptions.push({'id':_desc5_inshop,'language_id':5,'description': shopSv,'headline':pageHeadlineSv});

            // var attributes = {};
            // attributes['list_data'] = '{"lang_dk":'+language_enabled['lang_dk']+',"lang_no":'+language_enabled['lang_no']+',"lang_eng":'+language_enabled['lang_eng']+',"lang_se":'+language_enabled['lang_se']+',"lang_de":'+language_enabled['lang_de']+'}';
            // shop['attributes_'] = '{"lang_dk":'+language_enabled['lang_dk']+',"lang_no":'+language_enabled['lang_no']+',"lang_eng":'+language_enabled['lang_eng']+',"lang_se":'+language_enabled['lang_se']+',"lang_de":'+language_enabled['lang_de']+'}';

             var formData = {
                'shop':JSON.stringify(shop),
                'company':JSON.stringify(company),
                'addresslist': JSON.stringify(getAllAddressData()),
                'descriptions':JSON.stringify(descriptions)

            };

            var testMsg = "";

            if($("#shopCVR").val() == ""){
              testMsg = testMsg + "\n Cvr feltet er tom, jeg tror, der sket en fejl,  tryk Annuller \n og herefter tryk F5 og start forfra";
            }
            ajax(formData,"shop/updateCompanyShop","company.doneUpdate","");


    },
    doneCreated : function(responce)
    {
        var id = responce.data.shop[0].id;
          window.location.href = "../gavefabrikken_backend/index.php?rt=mainaa&editShopID="+id;
        //  alert("husk Sprog indstillinger")
/*
        var html = "<div class=\"companyMenuItem\" data-id=\""+id+"\" onclick=\"company.edit(this)\">"+responce.data.shop[0].name+"</div>";

        $("#companyList").append(html);
        alert("Valgshop oprettet")
        company.editCreated(id);
  */
    },
    doneUpdate : function(responce)
    {
        if(responce.status=="1"){
            $(".safeLayerTimer").hide();
            $(".safeLayer").hide();
            alert("Valgshop Opdateret")
        } else {

         alert("Der er sket en fejl, tryk F5 / opdaterere browseren og start forfra")
        }



    },
    editCreated : function(id)
    {

        _editShopID = id;
        company.searchClose();
        var html ="<button class=\"button\" onclick=\"company.save('"+id+"')\" style=\"background-color: #4CAF50; color:white; font-size:14px; padding:5px;\" >Opdatere Valgshop</button>"
        $("#trailContainer").html(html)
        ajax({},"shop/show","company.editShow","html");
    },
    edit : function(element)
    {
        _editShopID = $(element).attr("data-id");
        window.location.href = "../gavefabrikken_backend/index.php?rt=mainaa&editShopID="+_editShopID;
      },
    editStart : function(id){
        _editShopID = id;
        _shopMode = true; // ligger i main
        company.searchClose();
        var html ="<button class=\"button\" onclick=\"company.save('"+_editShopID+"')\" style=\"background-color: #4CAF50; color:white; font-size:14px; padding:5px;\" >Opdatere Valgshop</button>"
        $("#trailContainer").html(html)

        ajax({},"shop/show","company.editShow","html");
    },
    editShow : function(content)
    {
       $("#content").html(content)
       $("#shopFrom2").unbind('change').change(function(){
           _periodeStartEndControl.start =  $("#shopFrom2").val();
           console.log(_periodeStartEndControl);
        })
       $("#shopTo2").unbind('change').change(function(){
           _periodeStartEndControl.end =  $("#shopTo2").val();
           console.log(_periodeStartEndControl);
        })



    },
    editLoadData : function()
    {
        $(".safeLayerTimer").show();
        $(".safeLayer").show();
        ajax({"id":_editShopID},"shop/read","company.editLoadDataResponse");
    },
    editLoadDataResponse : function(responce)
    {

        if(responce.status != "1"){
            $(".safeLayerTimer").hide();
            $(".safeLayer").hide();

        } else {
        _datoSat = false;

        _compamyData = responce;


        _desc1_inshop = responce.data.shop[0].descriptions[0].id;
        _desc2_inshop = responce.data.shop[0].descriptions[1].id;
        _desc3_inshop = responce.data.shop[0].descriptions[2].id;
        _desc4_inshop = responce.data.shop[0].descriptions[3].id;
        _desc5_inshop = responce.data.shop[0].descriptions[4].id;


        _shopId =  responce.data.shop[0].id;
        _ptPdf =  responce.data.shop[0].pt_pdf;
        _pt_saleperson = responce.data.shop[0].pt_saleperson;
        _pt_shopName = responce.data.shop[0].pt_shopname;
        _pt_frontpage = responce.data.shop[0].pt_frontpage;
        _pt_mere = responce.data.shop[0].pt_mere_at_give;
        _pt_tree = responce.data.shop[0].pt_tree;
        _pt_bag = responce.data.shop[0].pt_bag_page;
        _pt_voucher_page = responce.data.shop[0].pt_voucher_page
        _pt_saleperson_page = responce.data.shop[0].pt_saleperson_page;
        _pt_green_layout = responce.data.shop[0].pt_layout_style;
        _pt_show_frontpage_design = responce.data.shop[0].pt_show_frontpage_design;
        _pt_brands_united = responce.data.shop[0].pt_brands_united;

        _pt_layout_language = responce.data.shop[0].pt_language;

        _pt_pdfId =  responce.data.shop[0].pt_pdf;
        _demoUser = responce.data.shop[0].demo_user_id;
        _shop_token = responce.data.shop[0].token;
        _companyID = responce.data.shop[0].company.id;
        $("#shopName").val(responce.data.shop[0].company.name);
        $("#overskriftValgtShop").html("Valgt Shop: "+responce.data.shop[0].company.name);
        $("#menuSearch").val(responce.data.shop[0].company.name);
        $("#shopKontakt").val(responce.data.shop[0].company.contact_name);
        $("#shopTelefon").val(responce.data.shop[0].company.contact_phone);
        $("#shopEmail").val(responce.data.shop[0].company.contact_email);
        $("#shopCVR").val(responce.data.shop[0].company.cvr);
        $("#so_no").val(responce.data.shop[0].company.so_no);
        $("#salesPerson").val(responce.data.shop[0].company.sales_person);
        $("#giftResponsible").val(responce.data.shop[0].company.gift_responsible);




        $("#shopLink").val(responce.data.shop[0].link);
        $("#shopOwnLink").html("<a target=\"_blank\" href=\"https://findgaven.dk/gavevalg/"+responce.data.shop[0].link+"\">Link til shoppen</a> <button onclick=\"goToshop('https://findgaven.dk/gavevalg/gf.php?url="+responce.data.shop[0].link+"')\">Link til shoppen (logger dig ud)</button>")
        $("#shopRealLink").val("https://findgaven.dk/gavevalg/"+responce.data.shop[0].link)  /**** hardcode *****/



        if(responce.data.shop[0].close_date != null){
          $("#finish_shop").html("Shop afsluttet d. "+responce.data.shop[0].close_date);
          $("#finishBtn").html("Tryk for at &oelig;ndre afslutningsdato til i dag");
          $("#ejValgteBtn").show();
        }


         if(responce.data.shop[0].blocked == 1) { $("#nodStopBtn").attr('checked','checked') }
         $("#nodStopText").val(responce.data.shop[0].blocked_text);
       // #server#
        if(typeof updateLocationForm == 'function') updateLocationForm(responce);

        /*
        $("#nodStopBtn").val();
        $("#nodStopText").html();
       */



        $("#shopLinkBackend").val("https://findgaven.dk/kundepanel?"+responce.data.shop[0].link)
        $("#shopOwnLinkBackend").html("<a href=\"../kundepanel?"+responce.data.shop[0].link+"\" target=\"_blank\">G&aring; til kundens backend</a>")
        $("#shopUsername").val(responce.data.shop[0].company.username);
        $("#shopPassword").val(responce.data.shop[0].company.password);
            $("#frontpageHeadlineDa").val(responce.data.shop[0].descriptions[0].headline)
            $("#frontpageHeadlineEn").val(responce.data.shop[0].descriptions[1].headline)
            $("#frontpageHeadlineDe").val(responce.data.shop[0].descriptions[2].headline)
            $("#frontpageHeadlineNo").val(responce.data.shop[0].descriptions[3].headline)
            $("#frontpageHeadlineSv").val(responce.data.shop[0].descriptions[4].headline)

        setTimeout(function() {
            tinyMCE.get('shopDa').setContent(Base64.decode(responce.data.shop[0].descriptions[0].description), {format : 'HTML'});
            tinyMCE.get('shopEn').setContent(Base64.decode(responce.data.shop[0].descriptions[1].description), {format : 'HTML'});
            tinyMCE.get('shopDe').setContent(Base64.decode(responce.data.shop[0].descriptions[2].description), {format : 'HTML'});
            tinyMCE.get('shopNo').setContent(Base64.decode(responce.data.shop[0].descriptions[3].description), {format : 'HTML'});
            tinyMCE.get('shopSv').setContent(Base64.decode(responce.data.shop[0].descriptions[4].description), {format : 'HTML'});
        },1000);

        var imgHtml = responce.data.shop[0].image_path;

        if(imgHtml != ""){
          _hasLogoCheck = true;
        } else {
          _hasLogoCheck = false;
        }

        $("#selectedLogo").css("background-image", "url(views/media/logo/"+imgHtml+".jpg)");
 //       if(responce.data.shop[0].zoom_enabled == 1) { $("#zoom_enabled").attr('checked','checked') }
 //       if(responce.data.shop[0].logo_enabled == 1) { $("#logo_enabled").attr('checked','checked') }
// herher �ndre visning af close tilbage
//      if(responce.data.shop[0].active == 1) { $("#closeShop").attr('checked','checked'); }
        if(responce.data.shop[0].soft_close == 0) { $("#closeShop").attr('checked','checked'); }
        if(responce.data.shop[0].is_demo == 1) { $("#testShop").attr('checked','checked');   }
        if(responce.data.shop[0].final_finished == 1) { $("#final_finished").attr('checked','checked');   }
        if(responce.data.shop[0].subscribe_gaveklubben == 1 ) { $("#activateGaveklubben").attr('checked','checked');   }


            if( responce.data.shop[0].show_tree_front == 1  ){
                $("#plantTree").attr('checked','checked');
            }

            if( responce.data.shop[0].login_design == 0  ){
              $("#layoutDefault").attr('checked','checked');
            } else if( responce.data.shop[0].login_design == 1  ){
                 $("#newLoginDesign").attr('checked','checked');
            } else if( responce.data.shop[0].login_design == 2   ){
               $("#layoutGuld").attr('checked','checked');
            }
        else if( responce.data.shop[0].login_design == 3   ){
            $("#green2022").attr('checked','checked');
        }
        else if( responce.data.shop[0].login_design == 4   ){
            $("#gold2022").attr('checked','checked');
        }
        else if( responce.data.shop[0].login_design == 10   ){
                $("#design2023").attr('checked','checked');
        }
        else if( responce.data.shop[0].login_design == 11   ){
            $("#sis").attr('checked','checked');
        }
        else {
                $("#layoutDefault").attr('checked','checked');
            }

        $(".gf-layout").unbind("click").click(
            function(){
               $(".gf-layout").prop('checked',false).not(this)
               $(this).prop('checked',true);
            }
        )


    //    if(responce.data.shop[0].login_design == 1 ) { $("#newLoginDesign").attr('checked','checked');   }




        if(responce.data.shop[0].edit_allowed == 1 ) { $("#allowCustomerToMakeChange").attr('checked','checked');   }
        if(responce.data.shop[0].show_price == 1 ) { $("#showPresentPrice").attr('checked','checked');   }
        if(responce.data.shop[0].show_qr == 1 ) { $("#show_qr").attr('checked','checked');   }



            $( "#langliste" ).show();
            if(responce.data.shop[0].language_settings != ""){
                language_settingsObj = jQuery.parseJSON( responce.data.shop[0].language_settings  );
                if(language_settingsObj.lang_dk == 1) { $("#lang_dk").attr('checked','checked');  }
                if(language_settingsObj.lang_no == 1) { $("#lang_no").attr('checked','checked');  }
                if(language_settingsObj.lang_eng == 1) { $("#lang_eng").attr('checked','checked');  }
                if(language_settingsObj.lang_se == 1) { $("#lang_se").attr('checked','checked');  }
                if(language_settingsObj.lang_de == 1) { $("#lang_de").attr('checked','checked');  }
            }






        $("#periodeMail").val( responce.data.shop[0].email_list)


      //  $("#shopFrom").val("01/15/2010");
      //  $("#shopTo").val("01/02/2020");
            $( "#shopFrom2" ).datepicker({
               dateFormat:"dd-mm-yy",
               numberOfMonths: 3,
               showButtonPanel: true
            });
            $( "#shopTo2" ).datepicker({
               dateFormat:"dd-mm-yy",
               numberOfMonths: 3,
               showButtonPanel: true
            });
           $( "#sendMailToCustomer" ).datepicker({
               dateFormat:"dd-mm-yy",
               numberOfMonths: 3,
               showButtonPanel: true
            });
            $( "#showDeleveryDateOnReceipt" ).datepicker({
               dateFormat:"dd-mm-yy",
               numberOfMonths: 2,
               showButtonPanel: true
            });


     //       $( "#shopFrom2" ).datepicker( "option", "dateFormat", "dd-mm-yy");
     //       $( "#shopTo2" ).datepicker( "option", "dateFormat", "dd-mm-yy" );
     //       $( "#showDeleveryDateOnReceipt" ).datepicker( "option", "dateFormat", "dd-mm-yy");
     //       $( "#sendMailToCustomer" ).datepicker( "option", "dateFormat", "dd-mm-yy" );

                if( responce.data.shop[0].start_date  != null){
                    var str = responce.data.shop[0].start_date
                    res = str.split("-");
                    $("#shopFrom2").val(res[2]+"-"+res[1]+"-"+res[0]);
                }
                if( responce.data.shop[0].start_time  != null){
                    var timeStr = responce.data.shop[0].start_time;
                    // Remove seconds from HH:mm:ss -> HH:mm for time input
                    $("#shopFromTime").val(timeStr.substring(0, 5));
                }

                if(responce.data.shop[0].end_date != null){
                    var str = responce.data.shop[0].end_date;
                    res = str.split("-");
                    $("#shopTo2").val(res[2]+"-"+res[1]+"-"+res[0]);
                }
                if(responce.data.shop[0].end_time != null){
                    var timeStr = responce.data.shop[0].end_time;
                    // Remove seconds from HH:mm:ss -> HH:mm for time input
                    $("#shopToTime").val(timeStr.substring(0, 5));
                }
                if(responce.data.shop[0].shipment_date != null){
                    var str = responce.data.shop[0].shipment_date;
                    res = str.split("-");
                    $("#showDeleveryDateOnReceipt").val(res[2]+"-"+res[1]+"-"+res[0]);
                }
                if(responce.data.shop[0].expire_warning_date != null){
                    var str = responce.data.shop[0].expire_warning_date;
                    res = str.split("-");
                    $("#sendMailToCustomer").val(res[2]+"-"+res[1]+"-"+res[0]);
                }






         if(responce.data.shop[0].start_date != "" || responce.data.shop[0].end_date != ""){
                if(responce.data.shop[0].start_date != null){
                    _datoSat = true;
                }
                if(responce.data.shop[0].end_date != null){
                      _datoSat = true;
                }
         }



        for(var i=0;responce.data.shop[0].presents.length > i;i++)
        {
          var giftID =   responce.data.shop[0].presents[i].present_id;
          var shopPresentsId = responce.data.shop[0].presents[i].id;

          $("#"+giftID).hide();

          //var imgId = $("#"+giftID).attr("data-id");
          var imgId =  responce.data.shop[0].presents[i].present.attributes.first_image_media_path;
          var html = "";
          var isActive = "";
     
          if(responce.data.shop[0].presents[i].active == 0){
            isActive = "giftIsNoActive";
          }


          if(responce.data.shop[0].presents[i].present.attributes.shop_id != 0){
                html+= '<li  class="ui-state-default '+isActive+'"  data-id="'+giftID+'" data-shopPresentsId="'+shopPresentsId+'" data-unik="'+responce.data.shop[0].presents[i].present.attributes.shop_id+'" style="margin-left:15px;margin-top:40px; width:150px; border:1px solid red; "><div class="sort-img presentAdminImg"  style="background-image: url(views/media/user/'+imgId+'.jpg);"> ';
          } else {
                html+= '<li class="ui-state-default '+isActive+'"  data-id="'+giftID+'" data-shopPresentsId="'+shopPresentsId+'" data-unik="'+responce.data.shop[0].presents[i].present.attributes.shop_id+'" style="margin-left:15px;margin-top:40px; width:150px;"><div class="sort-img presentAdminImg"  style="background-image: url(views/media/user/'+imgId+'.jpg);"> ';
          }

          html+= '<img  data-id="'+giftID+'" style="z-index:100; position: relative;  right: -82px; top: -60px;" class="icon" src="views/media/icon/1373253296_delete_64.png"  onclick="removeFromShop(this,\''+giftID+'\')" height="25" width="25" />'
          html+= '<img  data-id="'+giftID+'" style="z-index:100; position: relative;  left: -60px; top: -60px;" class="icon" src="views/media/icon/1373253256_gear_48.png"  onclick="presentsOptions.options(\''+shopPresentsId+'\',\''+giftID+'\')" height="25" width="25" />'
          html+= '<img  data-id="'+giftID+'" style="z-index:100; position: relative;  left: -60px; top: -60px;" class="icon" src="views/media/icon/1373253282_pencil_64.png"  onclick="unikPresentInShop.show(this)" height="25" width="25" /></div>'
          html+= "<div style=\"background-color: white; color:black; font-size:10px;font-weight: normal; border-top:1px solid black; \">"+responce.data.shop[0].presents[i].present.attributes.present_no+"</div><div style=\"background-color: white; color:black; font-size:10px;font-weight: normal; \">"+responce.data.shop[0].presents[i].present.attributes.internal_name+"</div></li>";
          $("#sortable").append(html);


        }

          $( "#sortable" ).sortable({
                update: function( ) {
                   updateSelectedPresentsIndex()
            }
          });
          $( "#sortable" ).disableSelection();
          rapport.setRapportData( responce.data.shop[0].attributes_);
          feltDeff.loaditem(responce.data.shop[0].attributes_)


        /*
        console.log(responce)
        alert(responce.data.shop[0].id)
        alert(responce.data.shop[0].company.cvr)
        */
        /*
        $.each(responce.data, function(i, item) {
           console.log(item[0].company.name)


        })
        */
        if($("#nodStopText").val() == ""){
          $("#nodStopText").val("SYSTEMET ER UNDER OPDATERING");
        }
            $(".safeLayerTimer").hide();
            $(".safeLayer").hide();
      }


      if(_localroute == "editShopGifts" ){
            $("#shopTabs").tabs({ active: 2 });
            toogleSelectPresentView('selectGift')
      }
       _shop_mode = responce.data.shop[0].shop_mode;
      $(".shopState").find("[data-id='" + _shop_mode + "']").attr('checked','checked').parent().addClass("shopStateSelected");
      var localisation = responce.data.shop[0].localisation;
      $(".localisation").find("[data-id='" + localisation + "']").attr('checked','checked').parent().addClass("localisationSelected");





      company.setLocalisation()
      company.initShopState();
      company.initShopLocalisation();



      new ptShopClass().init();
      new ptShopPrices().init();
     
      new ptMakePaperPDF().init();

      shopWelcomeMail.init();
        company.warehouse(responce.data.shop[0].reservation_code);

      $(".safeLayerTimer").show();
      $(".safeLayer").show();
      setTimeout(function(){
          $(".safeLayerTimer").hide();
            $(".safeLayer").hide();
             company.initShopLocalisation();  
      }, 1000);

        // skjuler papir tab
        if ($("#shopState-6").is(":checked")  || $("#shopState-4").is(":checked") )  {
            $("#GavevalgLink").show();
        }

    },
    initShopState : function()
    {

        $(".shopState input[type=radio]").unbind("click").click(function(){
            let r = confirm("Ønsker du at ændre på shop status?")
            if(!r){
                $(".shopState").find("[data-id='" + _shop_mode + "']").attr('checked','checked').parent().addClass("shopStateSelected");
                return;
        }

        $(".shopState").removeClass("shopStateSelected");
        $(this).parent().addClass("shopStateSelected");
        _shop_mode = $(this).attr("data-id");
        $.ajax(
        {
            url: 'index.php?rt=shop/update',
            type: 'POST',
            dataType: 'json',
            data: {"id": _shopId,"shop_mode":$(this).attr("data-id")}
        }).done(function(res) {
           if(res.status != 1) { alert("System error")}

        })
    })
    },
    warehouse:function(code){
        $.ajax(
            {
                url: 'index.php?rt=shop/readwarehouses',
                type: 'POST',
                dataType: 'json',
                data: {}
            }).done(function(res) {
            if(res.status != 1) { alert("System error")}

            let dropdownHTML = '<select id="warehouseDropdown"><option value="">Ingen valgte</option>';
            res.data.warehouse.forEach(item => {
                dropdownHTML += `<option class="lang_${item.language_id}" value="${item.code}">${item.name} - ${item.code}</option>`;
            });
            dropdownHTML += '</select>';
            $("#LagerlokationContainer").html(dropdownHTML);
            code = code == null || code == "" ? "":code;
            console.log(code)

            $("#warehouseDropdown").val(code);
        })
    },
    initShopLocalisation : function()
    {
            $(".localisation input[type=radio]").unbind("click").click(function(){

        $(".localisation").removeClass("localisationSelected");
        $(this).parent().addClass("localisationSelected");
        $.ajax(
        {
            url: 'index.php?rt=shop/update',
            type: 'POST',
            dataType: 'json',
            data: {"id": _shopId,"localisation":$(this).attr("data-id")}
        }).done(function(res) {
           if(res.status != 1) { alert("System error")}
           else {location.reload();}

        })
    })
    }


}

   // shop status



function convertDate(dateString) {
  var date = new Date(dateString);
  var year = date.getFullYear();
  var month = (1 + date.getMonth()).toString();
  month = month.length > 1 ? month : '0' + month;
  var day = date.getDate().toString();
  day = day.length > 1 ? day : '0' + day;
  return month + '/' + day + '/' + year;
}

function goToshop(path){
    $.removeCookie('gf-data');
    window.open(path, "_blank");

}