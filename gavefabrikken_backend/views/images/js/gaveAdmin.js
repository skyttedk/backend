

var gaveEditData_ = "";
var _headline = "";
var _shortDesp = "";
var _headline = "";
var _selectedPresent = "";


var _desc1 = "";
var _desc2 = "";
var _desc3 = "";
var _desc4 = "";
var _desc5 = "";
var _isNewPresent = false;



var gaveAdmin = {
editId: "",


 editItemnrInSampak:function(id) {
   /*
   var list = "";
   var lines = $("#sampak_"+id).val().split('\n');
   for(var i = 0;i < lines.length;i++){
        list+=
   }
   */

    var  postData = {
    'id':id,
    'list': $("#sampak_"+id).val()  }
    ajax(postData, "present/updateSampakList", "", "editItemnrInSampakResponse");
 },
editItemnrInSampakResponse:function(){
  alert("Gemt");
},


showSelectToCompany: function() {
    ajax({}, "present/readAll", "", "#shopPresentSelect");
  },
backToList: function() {
    $("#presentDetailBox").animate(
      {
      width: "toggle"
      }
    );
  },
show: function() {
    if(_localRouteToShopsOwnGift == true) {
      _localRouteToShopsOwnGift = false;
      window.location.search += '&localroute=editShopGifts';
      //location.reload();

    } else {

      _unik = false;
      _shopMode = false; // ligger i main
      _editShopID = ""
      html = ' <button class="button" onclick="gaveAdmin.showNew()">Opret ny gave</button>';
      $("#trailContainer").html(html);
      // ajax({},"present/readAll","gaveAdmin.showResponce","");
      ajax({}, "present/readTop10", "", "#content");

    }


  },
showResponce: function(response) {
    $("#trailContainer").html(response);
    /*
  $.each( response.data, function( key, value ) {
      $.each( value, function( key2, value2 ) {
           console.log(value2)
           alert(value2.name)
      });

          readAll
  });
  */
  },
showNew: function() {
    _dropTarget = "";
    //     html = '<button class="button" style="background-color: #4CAF50; color:white; font-size:14px; padding:5px; " onclick="gaveAdmin.save(\'\')">Gem gave</button>';
    //        $("#trailContainer").html(html);
    //       ajax({},"presentAdmin/showNew","","#content");
    gaveAdmin.preCreatePresent();

  },

preCreatePresent: function() {
    var formData = {};
    var present = {"name": Date.now(), "nav_name": "", "price": "0", "price_group": "0", "vendor": "", "indicative_price": "0","show_to_saleperson":"0","oko_present":"0","prisents_nav_price":"","prisents_nav_price_no":"","show_to_saleperson_no":"0"}
    var media =[]
    var logo =[{"logo": "logo/intet.jpg"}]
    var descriptions =[
        {"id": "", "language_id": 1, "caption": "###1","caption_presentation": "###", "short_description": "###", "long_description": "###"},
        {"id": "", "language_id": 2, "caption": "###","caption_presentation": "###", "short_description": "###", "long_description": "###"},
        {"id": "", "language_id": 3, "caption": "###","caption_presentation": "###", "short_description": "###", "long_description": "###"},
        {"id": "", "language_id": 4, "caption": "###","caption_presentation": "###", "short_description": "###", "long_description": "###"},
        {"id": "", "language_id": 5, "caption": "###","caption_presentation": "###", "short_description": "###", "long_description": "###"}]
    var variant =[];
    var formData = {
    'present': JSON.stringify(present),
    'media': JSON.stringify(media),
    'logo': JSON.stringify(logo),
    'descriptions': JSON.stringify(descriptions),
    'variant': JSON.stringify(variant)

    };
    ajax(formData, "present/create", "gaveAdmin.doneCreated", "");





  },
preCreatePresent2: function() {

    _desc1 = "";
    _desc2 = "";
    _desc3 = "";
    _desc4 = "";
    _desc5 = "";


    var formData = "";
    var present = {};

    present['name'] = Date.now(); //$("#presentsAdminName").val();
    present['nav_name'] = ""
    present['present_no'] = ""

    present['present_list'] = "";
    present['price'] = "";
    present['price_group'] = "";
    present['vendor'] = "";
    present['indicative_price'] = "";
    present['show_to_saleperson'] = "";
    present['prisents_nav_price'] = "";
    present['show_to_saleperson_no'] = "";
    present['prisents_nav_price_no'] = "";


    present['oko_present'] = "";

    var media =[];


    var logo =[];
    var descriptions =[];
    var short_description_1 = "###";
    var long_description_1 = "###";
    var cap_1 = "###";

    var short_description_2 = "###";
    var long_description_2 = "###";
    var cap_2 = "###";
    var short_description_3 = "###";
    var long_description_3 = "###";
    var cap_3 = "###";

    var short_description_4 = "###";
    var long_description_4 = "###";
    var cap_4 = "###";

    var short_description_5 = "###";
    var long_description_5 = "###";
    var cap_5 = "###";

    descriptions.push({'id': _desc1, 'language_id': 1, 'caption': cap_1, 'short_description': short_description_1, 'long_description': long_description_1});
    descriptions.push({'id': _desc2, 'language_id': 2, 'caption': cap_2, 'short_description': short_description_2, 'long_description': long_description_2});
    descriptions.push({'id': _desc3, 'language_id': 3, 'caption': cap_3, 'short_description': short_description_3, 'long_description': long_description_3});
    descriptions.push({'id': _desc4, 'language_id': 4, 'caption': cap_4, 'short_description': short_description_4, 'long_description': long_description_4});
    descriptions.push({'id': _desc5, 'language_id': 5, 'caption': cap_5, 'short_description': short_description_5, 'long_description': long_description_5});




    var variant =[];
    var langId = "";
    var variantId = "";
    var formData = {
    'present': JSON.stringify(present),
    'media': JSON.stringify(media),
    'logo': JSON.stringify(logo),
    'descriptions': JSON.stringify(descriptions),
    'variant': JSON.stringify(variant)

    };
    ajax(formData, "present/create", "gaveAdmin.doneCreated", "");


  },


createNew: function() {
    alert("create ny");
  },
editGiftUnik: function(id) {
    gaveAdmin.editId = id;
    _selectedPresent = id;
    ajax({"id": id}, "present/read", "gaveAdmin.editGiftReturn");
  },
editGift: function(id) {
    gaveAdmin.editId = id;
    _selectedPresent = id;
    _dropTarget = "";
    _unik = false;
    ajax({"id": id}, "present/read", "gaveAdmin.editGiftReturn");
  },
editGiftReturn: function(data) {
    gaveEditData_ = data;
    ajax({}, "presentAdmin/showNew", "gaveAdmin.editInsertData", "html");
  },
editInsertData: function(content) {

    /*
  if(_unik == false){
      $("#content").html(content);
  }
  if(_unik == true)
  {
      $("#contentUnikPresent").html(content);
  }
   */

    //  console.log(_unikUpdata +"--"+_unik)

    if(_unikUpdata == true && _unik == true) {

      //    $("#presentDetailBox").html("");
      $("#vsgEditBoxContent").html(content);

    } else {

      //   $("#vsgEditBoxContent").html("");
      $("#presentDetailBox").html(content);
    }

    var state = gaveEditData_.data.present[0].state;
    $('#presentState_'+state).prop("checked", true);

    // system.work();
    //

    //   $("#presentsTabs").tabs( "enable" , 3 )

    gaveEditData_.data.present[0].show_to_saleperson == 1 ? $('#show_to_saleperson').prop('checked', true) : "";
    gaveEditData_.data.present[0].show_to_saleperson_no == 1 ? $('#show_to_saleperson_no').prop('checked', true) : "";
    gaveEditData_.data.present[0].oko_present == 1 ? $('#oko_present').prop('checked', true) : "";

    gaveEditData_.data.present[0].in_stock == 1 ? $('#inStock').prop('checked', true) : "";
    $('#in_stock').prop('checked', true)
    $("#prisents_nav_price").val(gaveEditData_.data.present[0].prisents_nav_price);
    $("#prisents_nav_price_no").val(gaveEditData_.data.present[0].prisents_nav_price_no);
    $("#presentsAdminName").val(gaveEditData_.data.present[0].name);
    $("#NAVpresentsAdminName").val(gaveEditData_.data.present[0].nav_name);
    $("#presentsAdminNr").val(gaveEditData_.data.present[0].present_no);
    $("#presentsSubGiftList").val(gaveEditData_.data.present[0].present_list);
    $("#presentsAdminlev").val(gaveEditData_.data.present[0].vendor);
    $("#presentsAdminPrice").val(gaveEditData_.data.present[0].price);
    $("#prisentsAdminBudgetPrice").val(gaveEditData_.data.present[0].price_group);
    $("#prisentsAdminThePrice").val(gaveEditData_.data.present[0].indicative_price);


    $("#presentsAdminHeadlineDa").val(gaveEditData_.data.present[0].descriptions[0].caption)
    $("#presentsAdminHeadlineDaPT").val(gaveEditData_.data.present[0].descriptions[0].caption_presentation)
    $("#presentsAdminHeadlineDaPaper").val(gaveEditData_.data.present[0].descriptions[0].caption_paper)
    $("#presentsAdminShortDa").val(Base64.decode(gaveEditData_.data.present[0].descriptions[0].short_description))
    $("#presentsAdminLongDa").val(Base64.decode(gaveEditData_.data.present[0].descriptions[0].long_description))

    $("#presentsAdminHeadlineEn").val(gaveEditData_.data.present[0].descriptions[1].caption)
    $("#presentsAdminHeadlineEnPT").val(gaveEditData_.data.present[0].descriptions[0].caption_presentation)
    $("#presentsAdminShortEn").val(Base64.decode(gaveEditData_.data.present[0].descriptions[1].short_description))
    $("#presentsAdminLongEn").val(Base64.decode(gaveEditData_.data.present[0].descriptions[1].long_description))


    $("#presentsAdminHeadlineDe").val(gaveEditData_.data.present[0].descriptions[2].caption)
    $("#presentsAdminHeadlineDePT").val(gaveEditData_.data.present[0].descriptions[0].caption_presentation)
    $("#presentsAdminShortDe").val(Base64.decode(gaveEditData_.data.present[0].descriptions[2].short_description))
    $("#presentsAdminLongDe").val(Base64.decode(gaveEditData_.data.present[0].descriptions[2].long_description))

    $("#presentsAdminHeadlineNo").val(gaveEditData_.data.present[0].descriptions[3].caption)
    $("#presentsAdminHeadlineNoPT").val(gaveEditData_.data.present[0].descriptions[0].caption_presentation)
    $("#presentsAdminShortNo").val(Base64.decode(gaveEditData_.data.present[0].descriptions[3].short_description))
    $("#presentsAdminLongNo").val(Base64.decode(gaveEditData_.data.present[0].descriptions[3].long_description))

    $("#presentsAdminHeadlineSv").val(gaveEditData_.data.present[0].descriptions[4].caption)
    $("#presentsAdminHeadlineSvPT").val(gaveEditData_.data.present[0].descriptions[0].caption_presentation)
    $("#presentsAdminShortSv").val(Base64.decode(gaveEditData_.data.present[0].descriptions[4].short_description))
    $("#presentsAdminLongSv").val(Base64.decode(gaveEditData_.data.present[0].descriptions[4].long_description))




    _desc1 = gaveEditData_.data.present[0].descriptions[0].id
    _desc2 = gaveEditData_.data.present[0].descriptions[1].id
    _desc3 = gaveEditData_.data.present[0].descriptions[2].id
    _desc4 = gaveEditData_.data.present[0].descriptions[3].id
    _desc5 = gaveEditData_.data.present[0].descriptions[4].id
    htmlLogo = "";

    if(gaveEditData_.data.present[0].logo != "logo/intet.jpg") {
      var s1 = s2 = s3 = s4 = "";
      if(gaveEditData_.data.present[0].logo_size == "1") {s1 = "selected"}
      if(gaveEditData_.data.present[0].logo_size == "2") {s2 = "selected"}
      if(gaveEditData_.data.present[0].logo_size == "3") {s3 = "selected"}
      if(gaveEditData_.data.present[0].logo_size == "4") {s4 = "selected"}
      var htmlLogo = "<select class=\"log-admin-size\" style=\"display:none;\"> <option value='1' "+s1+"  >Lille</option>  <option value='2'  "+s2+">Medium</option>  <option value='3' "+s3+">Stor</option>  <option value='4' "+s4+">St&oslash;rst</option></select>"
    }
    $("#selectedLogo").html(htmlLogo+"<div class=\"logo-img\" data-id=\""+gaveEditData_.data.present[0].logo+"\" logoSize=\""+gaveEditData_.data.present[0].logo_size+"\" style=\"background-image: url(views/media/"+gaveEditData_.data.present[0].logo+");\"> </div>")



    addToMedia(gaveEditData_.data.present[0].present_media);


    if(_unik == true) {
      $("#gaveAdminBack").hide()
      //   var html = '<button class="button" style="background-color: #4CAF50; color:white; font-size:14px; padding:5px; " onclick="gaveAdmin.save(\''+_presentInShop_shopPresentId+'\')">Opret unik gave</button>'
    } else {
      var html = '<button class="button" style="background-color: #4CAF50; color:white; font-size:14px; padding:5px; " onclick="gaveAdmin.save(\''+gaveEditData_.data.present[0].id+'\')">Opdatere / opret gave</button>'
      $("#gaveAdminBack").show()
    }
    if(_unikUpdata == true) {


      //    var html = '<button class="button" style="background-color: #4CAF50; color:white; font-size:14px; padding:5px;"  data-id="unik" onclick="gaveAdmin.save(\''+gaveEditData_.data.present[0].id+'\')">Opdatere unik gave</button>'
    }



    // indset data to variant

    //data = $.parseJSON(gaveEditData_.data.present[0].models);
    data = gaveEditData_.data.present[0].models;


    var element = "";
    /*
  $.each(data, function(i, item) {
  //  console.log(item)

  })
    */
    let variantNrDK
    $.each(data, function(i, item){
        if(item.language_id == "1"){
            variantNrDK =  item.model_present_no;
        }
    });
    console.log(variantNrDK);
    $.each(data, function(i, item)
      {
       //console.log(item)
        if(item.language_id == "1") {targetElement = "#tabsVari-dk > table"}
        if(item.language_id == "2") {targetElement = "#tabsVari-en > table"}
        if(item.language_id == "3") {targetElement = "#tabsVari-de > table"}
        if(item.language_id == "4") {targetElement = "#tabsVari-no > table"}
        if(item.language_id == "5") {targetElement = "#tabsVari-sv > table"}

        var variant = "";
        var variantSub = "";
        var variantNr = "";
        var variantImg = "";
        var variantCheck = "";
        var modelId = "";
        var variantId = "";

        modelId = item.model_id;
        variant = item.model_name;
        variantSub = item.model_no;
        variantNr = item.model_present_no;
        variantImg = item.media_path;
        variantId = item.variantId;
        var htmlAction1 = "";
        if(item.language_id == "1") {

          var sampak = "<hr><textarea onclick=\"regEventAction('none')\"   id='sampak_"+modelId+"' rows='4' cols='35'>"+item.sampak_items+"</textarea><br><button style='font-size:10px;' onclick='gaveAdmin.editItemnrInSampak(\""+modelId+"\")'>Opdatere varer i sampak</button>";
           var lc  =  variantNr.toLowerCase();
          if(lc.search("sam") == -1){
             sampak = "";
          }
          console.log(variantImg);
          var variantImgArr = variantImg.split("/");
          variantImg = variantImgArr[variantImgArr.length-1];
            console.log(variantImg);
            htmlAction1 = "<tr bgcolor=\"#9EBEF5\" data-id=\""+item.language_id+"\" data-variantId=\""+modelId+"\"><td><input class=\"prisentVariantVal\" value=\""+variant+"\" type=\"text\"></td>";
          htmlAction1 += "<td><input class=\"prisentVariantVal\" value=\""+variantSub+"\" type=\"text\"></td>";
          htmlAction1 += "<td><input  class=\"prisentVariantVal\" value=\""+variantNr+"\" type=\"text\"> "+sampak+"</td>";
          htmlAction1 += "<td><img class=\"variantImg prisentVariantVal\" src=\"views/media/type/"+variantImg+"\" width=\"50\"></td>";
          htmlAction1 += "<td><img class=\"icon\" src=\"views/media/icon/1373253296_delete_64.png\" title=\"Slet\" onclick='variant.deleteItem(this)' height=\"25\" width=\"25\"><img class=\"icon\" src=\"views/media/icon/bill.png\" title=\"Vœlg billede\"  onclick=\"variant.showUploadDialog(this)\" height=\"30\" width=\"30\"> </td>";

        } else {
          htmlAction1 = "<tr bgcolor=\"#9EBEF5\" data-id=\""+item.language_id+"\" data-variantId=\""+modelId+"\"><td><input class=\"prisentVariantVal\" value=\""+variant+"\" type=\"text\"></td>";
          htmlAction1 += "<td><input class=\"prisentVariantVal\" value=\""+variantSub+"\" type=\"text\"></td>";
          htmlAction1 += "<td><input disabled  class=\"ddd prisentVariantVal\" value=\""+variantNr+"\" type=\"text\"></td>";
          htmlAction1 += "<td><img class=\"variantImg prisentVariantVal\" src=\"\" width=\"50\"></td>";
          htmlAction1 += "<td></td><td></td></tr>";
        }
        $(targetElement).append(htmlAction1);
      }
    );

    // Moms
    var moms = gaveEditData_.data.present[0].moms
    $("#moms").val(moms);







    /*

  $.each(data, function(i, item) {
       if(item.language_id == "1"){targetElement  = "#tabsVari-dk > table" }
       if(item.language_id == "2"){targetElement  = "#tabsVari-en > table" }
       if(item.language_id == "3"){targetElement  = "#tabsVari-de > table" }
       if(item.language_id == "4"){targetElement  = "#tabsVari-no > table" }
       if(item.language_id == "5"){targetElement  = "#tabsVari-sv > table" }

              var variant       = "";
              var variantSub    = "";
              var variantNr     = "";
              var variantImg    = "";
              var variantCheck  = "";


         $.each(item.feltData, function(j, item2) {
                  if(j == 0) { variant      = item2.variant  }
                  if(j == 1) { variantSub  = item2.variantSub  }
                  if(j == 2) { variantNr    = item2.variantNr  }
                  if(j == 3) { variantImg   = item2.variantImg  }
                  if(j == 4) { variantCheck = item2.variantCheck  }
         })
         var htmlAction1 = "";
         if(item.language_id == "1"){
             htmlAction1 = "<tr bgcolor=\"#9EBEF5\"><td><input class=\"prisentVariantVal\" value=\""+variant+"\" type=\"text\"></td>";
             htmlAction1+= "<td><input class=\"prisentVariantVal\" value=\""+variantSub+"\" type=\"text\"></td>";
             htmlAction1+= "<td><input disabled class=\"prisentVariantVal\" value=\""+variantNr+"\" type=\"text\"></td>";
             htmlAction1+= "<td><img class=\"variantImg prisentVariantVal\" src=\""+variantImg+"\" width=\"50\"></td>";
             htmlAction1+= "<td><img class=\"icon\" src=\"views/media/icon/1373253296_delete_64.png\" title=\"Slet\" onclick='variant.deleteItem(this)' height=\"25\" width=\"25\"><img class=\"icon\" src=\"views/media/icon/bill.png\" title=\"Vœlg billede\" onclick=\"variant.showUploadDialog(this)\" height=\"30\" width=\"30\"> </td>";
             if(variantCheck == ""){
                  htmlAction1 += "<td><input class=\"prisentVariantVal variantCheckbox\"  type=\"checkbox\"></td></tr>";
             } else {
                  htmlAction1 += "<td><input class=\"prisentVariantVal variantCheckbox\" checked=\"checked\" type=\"checkbox\"></td></tr>";
             }
         } else {
             htmlAction1 = "<tr bgcolor=\"#9EBEF5\"><td><input class=\"prisentVariantVal\" value=\""+variant+"\" type=\"text\"></td>";
             htmlAction1+= "<td><input class=\"prisentVariantVal\" value=\""+variantSub+"\" type=\"text\"></td>";
             htmlAction1+= "<td><input disabled class=\"prisentVariantVal\" value=\""+variantNr+"\" type=\"text\"></td>";
             htmlAction1+= "<td><img class=\"variantImg prisentVariantVal\" src=\""+variantImg+"\" width=\"50\"></td>";
             htmlAction1+= "<td></td><td></td></tr>";
         }
         $(targetElement).append(htmlAction1);
  });
  */
    if(_unik == true) {
      _tempId = _presentInShop_shopPresentId;

      //  window.setTimeout(gaveAdmin.goFromDelay, 3000);
    } else {
      system.endWork();
    }
    $("#presentDetailBox").animate(
      {
      width: "toggle"
      }
    );
    $("#presentDetailSaveBtn").html(html);
  },


goFromDelay: function() {
    gaveAdmin.save(_tempId);
  },
save: function(id) {


    system.work();
    if(id == "") {
      _desc1 = "";
      _desc2 = "";
      _desc3 = "";
      _desc4 = "";
      _desc5 = "";
    }

    var formData = "";
    var present = {};
    if(id != "") {
      present['id'] = id;
    }
    present['name'] = Date.now(); //$("#presentsAdminName").val();
    present['nav_name'] = $("#NAVpresentsAdminName").val();
    _headline = $("#NAVpresentsAdminName").val();
   if( $('#show_to_saleperson').is(":checked")){
        present['show_to_saleperson'] = "1"
   } else {
        present['show_to_saleperson'] = "0"
   }
   if( $('#show_to_saleperson_no').is(":checked")){
        present['show_to_saleperson_no'] = "1"
   } else {
        present['show_to_saleperson_no'] = "0"
   }



   if( $('#oko_present').is(":checked")){
        present['oko_present'] = "1"
   } else {
        present['oko_present'] = "0"
   }


    present['present_no'] = $("#presentsAdminNr").val();
    /*
  if(_unik == true){
       present['shop_id'] = _editShopID;
       present['id'] = _presentInShop_Id;
  };

  var _headline = "";
  var _shortDesp = "";


  */


    present['state'] = $("[name=state]:checked").val();
    present['present_list'] = $("#presentsSubGiftList").val();
    present['price'] = $("#presentsAdminPrice").val();
    present['price_group'] = $("#prisentsAdminBudgetPrice").val();
    present['vendor'] = $("#presentsAdminlev").val();
    present['indicative_price'] = $("#prisentsAdminThePrice").val();
    present['prisents_nav_price'] = $("#prisents_nav_price").val();
    present['prisents_nav_price_no'] = $("#prisents_nav_price_no").val();


    var media =[];
    $(".presentAdminImg").each(function(index)
      {
        var id = $(this).attr("data-id");
        media.push({'media_path': id, 'index': index});
      }
    );

    var logo =[];
    logo.push({'logo': $(".logo-img").attr("data-id"), 'logo_size': $(".log-admin-size").val()});
    var descriptions =[];
    _shortDesp = tinyMCE.get('presentsAdminShortDa').getContent({format: 'HTML'});

    var short_description_1 = Base64.encode(tinyMCE.get('presentsAdminShortDa').getContent({format: 'HTML'}));
    if(short_description_1 == "") {short_description_1 = "###";}
    var long_description_1 = Base64.encode(tinyMCE.get('presentsAdminLongDa').getContent({format: 'HTML'}));
    if(long_description_1 == "") {long_description_1 = "###";}

    var cap_pt_1 = $("#presentsAdminHeadlineDaPT").val();
    if(cap_pt_1 == "") {cap_pt_1 = "###";}

    var cap_paper_1 = $("#presentsAdminHeadlineDaPaper").val();
    if(cap_paper_1 == "") {cap_paper_1 = "###";}


    var cap_1 = $("#presentsAdminHeadlineDa").val();
    _caption = $("#presentsAdminHeadlineDa").val();
    if(cap_1 == "") {cap_1 = "###";}
    descriptions.push({'id': _desc1, 'language_id': 1, 'caption_paper':cap_paper_1, 'caption': cap_1, 'caption_presentation':cap_pt_1, 'short_description': short_description_1, 'long_description': long_description_1});

    var short_description_2 = Base64.encode(tinyMCE.get('presentsAdminShortEn').getContent({format: 'HTML'}));
    if(short_description_2 == "") {short_description_2 = "###";}
    var long_description_2 = Base64.encode(tinyMCE.get('presentsAdminLongEn').getContent({format: 'HTML'}));
    if(long_description_2 == "") {long_description_2 = "###";}

    var cap_pt_2 = $("#presentsAdminHeadlineEnPT").val();
    if(cap_pt_2 == "") {cap_pt_2 = "###";}

    var cap_2 = $("#presentsAdminHeadlineEn").val();
    if(cap_2 == "") {cap_2 = "###";}
    descriptions.push({'id': _desc2, 'language_id': 2, 'caption': cap_2, 'caption_presentation':cap_pt_2,'short_description': short_description_2, 'long_description': long_description_2});


    var short_description_3 = Base64.encode(tinyMCE.get('presentsAdminShortDe').getContent({format: 'HTML'}));
    if(short_description_3 == "") {short_description_3 = "###";}
    var long_description_3 = Base64.encode(tinyMCE.get('presentsAdminLongDe').getContent({format: 'HTML'}));
    if(long_description_3 == "") {long_description_3 = "###";}

    var cap_pt_3 = $("#presentsAdminHeadlineDePT").val();
    if(cap_pt_3 == "") {cap_pt_3 = "###";}

    var cap_3 = $("#presentsAdminHeadlineDePT").val();
    if(cap_3 == "") {cap_3 = "###";}
    descriptions.push({'id': _desc3, 'language_id': 3, 'caption': cap_3, 'caption_presentation':cap_pt_3,'short_description': short_description_3, 'long_description': long_description_3});

    var short_description_4 = Base64.encode(tinyMCE.get('presentsAdminShortNo').getContent({format: 'HTML'}));
    if(short_description_4 == "") {short_description_4 = "###";}
    var long_description_4 = Base64.encode(tinyMCE.get('presentsAdminLongNo').getContent({format: 'HTML'}));
    if(long_description_4 == "") {long_description_4 = "###";}
    var cap_pt_4 = $("#presentsAdminHeadlineNoPT").val();
    if(cap_pt_4 == "") {cap_pt_4 = "###";}

    var cap_4 = $("#presentsAdminHeadlineNo").val();
    if(cap_4 == "") {cap_4 = "###";}
    descriptions.push({'id': _desc4, 'language_id': 4, 'caption': cap_4, 'caption_presentation':cap_pt_4, 'short_description': short_description_4, 'long_description': long_description_4});

    var short_description_5 = Base64.encode(tinyMCE.get('presentsAdminShortSv').getContent({format: 'HTML'}));
    if(short_description_5 == "") {short_description_5 = "###";}
    var long_description_5 = Base64.encode(tinyMCE.get('presentsAdminLongSv').getContent({format: 'HTML'}));
    if(long_description_5 == "") {long_description_5 = "###";}
    var cap_pt_5 = $("#presentsAdminHeadlineSvPT").val();
    if(cap_pt_5 == "") {cap_pt_5 = "###";}

    var cap_5 = $("#presentsAdminHeadlineSv").val();
    if(cap_5 == "") {cap_5 = "###";}
    descriptions.push({'id': _desc5, 'language_id': 5, 'caption': cap_5, 'caption_presentation':cap_pt_5,'short_description': short_description_5, 'long_description': long_description_5});

    var variant =[];
    var langId = "";
    var variantId = "";
    var dkvariantNr = "";
    $(".presentsVariant").each(function(index)
      {

        var lineIndex = 0;
        $('tr', $(this)).each(function (obj, index)
          {
            console.log()
            if($(this).find('.prisentVariantVal').length != 0) {

              var feltData =[];
              feltData.push ({'variantId': $(this).attr("data-variantId")})
              langId = $(this).attr("data-id");
              var i = 0;
              $('input', $(this)).each(function (obj, index)
                {

                  if(i == 0) {feltData.push ({'variant': this.value})}
                  if(i == 1) {feltData.push ({'variantSub': this.value})}
                  if(i == 2) {
                      if(langId == 1){ dkvariantNr = this.value  }
                      feltData.push ({'variantNr': dkvariantNr})
                  }
                  i++;
                }
              )
              $('.variantImg', $(this)).each(function (obj, index)
                {
                  if(langId == "1") {
                      let srcParts = this.src.split("/");
                      let imgId = srcParts[srcParts.length-1];
                      feltData.push ({'variantImg': imgId})
                  } else {
                    feltData.push ({'variantImg': "###"})
                  }

                }
              )
              $('.variantCheckbox', $(this)).each(function (obj, index)
                {

                  feltData.push ({'variantCheck': this.value})
                }
              )
              variant.push ({'language_id': langId, 'feltData': feltData, 'sortOrder': lineIndex})
              lineIndex++;
            }
          }
        );
      }
    );



    var formData = {
    'present': JSON.stringify(present),
    'media': JSON.stringify(media),
    'logo': JSON.stringify(logo),
    'descriptions': JSON.stringify(descriptions),
    'variant': JSON.stringify(variant),
    'moms': $('#moms option:selected').val()

    };

    if(_unik == true) {
      system.endWork();
      //   ajax(formData,"present/createShopVariant","gaveAdmin.doneCreatedVariant","");
    } else {

      if(id != "") {

        ajax(formData, "present/update", "gaveAdmin.doneUpdate", "");
      } else {

        ajax(formData, "present/create", "gaveAdmin.doneCreated", "");
      }
    }
    if(_shopEdit == true) {
      // ajax(formData,"present/update","gaveAdmin.doneUpdate","");
    }




  },
doneCreated: function(response) {
    _isNewPresent = true
    system.endWork();
    gaveAdmin.editId = response.data.present[0].id;
    _selectedPresent = response.data.present[0].id;
    gaveAdmin.editGift(""+response.data.present[0].id+"")
  },
doneUpdate: function(response) {
    system.endWork();
    if(_unikUpdata == true) {

      gaveAdmin.editGift(""+response.data.present[0].id+"")
    } else {
      alert("Gave updateret");
      gaveAdmin.editId = response.data.present[0].id;
      _selectedPresent = response.data.present[0].id;
      if(_isNewPresent == false) {
        gaveAdmin.editGift(""+response.data.present[0].id+"")
        var id = response.data.present[0].id

        $("#present_no_"+id).html(_headline)
        $("#caption_"+id).html(_caption)
        $("#short_description_"+id).html(_shortDesp)
        $("#presentsAdminHeadlineDa").val(_captionPresentation);

      } else {

        _isNewPresent = false
        gaveAdmin.show()
      }




    }
  },
doneCreatedVariant: function(response) {
    system.endWork();
    alert("unik gave oprettet");
    _unik = false;
    _unikUpdata = true
    gaveAdmin.editId = response.data.present[0].id;
    _selectedPresent = response.data.present[0].id;
    gaveAdmin.editGift(""+response.data.present[0].id+"")

    //window.location.href = window.location.href;
  }


}