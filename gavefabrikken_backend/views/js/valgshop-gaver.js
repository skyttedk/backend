var logData;
var logPresent;
var classValgshopGaver = (function ()
  {
    var _this = this;
    _this.isWorking = false;
    _this.offset = -30;
    _this.limit = 30;
    _this.hasTemplate4 = 0;
    _this.presentId = 0;
    _this.giftedChildrenCount = 0;
    _this.allCopyOfId = [];
    _this.init = function () {



      $(document).keypress(function (e)
        {
          if (e.which == 13) {
            _this.initSearch();
          }
        }
      );
      $("#vsg-content").scroll(function()
        {
            console.log($("#vsg-content").scrollTop())
            console.log($("#vsg-content-data").height())
          if((($("#vsg-content").scrollTop()+1500) > $("#vsg-content-data").height()) && _this.isWorking == false) {
            console.log("load")
            system.work();
            if($("#vsg-search").val() == "") {
              _this.load();
            } else {
              _this.search()
            }
          }
        }
      );



      _this.load();
      _this.initPresentInShop();
      _this.testTemplateCompatible()


        $("#vsg-search").keyup(function(){

            if($("#vsg-search").val().length > 0 ){
             //   $(".vsgNoFreeSearch").hide();
            } else {
             //   $(".vsgNoFreeSearch").show();
            }

        });

    };



    // ----------  søgning  ------------
    _this.load = function() {
        let  _budget = $("#pim-budget-budget").val();
        let _costStart = $("#kostpris-start").val();
        let _costSlut = $("#kostpris-end").val();
        let _stockMin = $("#searchStockMin").val();
        let _countrySearch = $('input[name="countrySearch"]:checked').val();
      _this.isWorking = true;
      _this.offset += 30;
      $.post("index.php?rt=present/readAllV2", {
              "offset": _this.offset,
              "limit": _this.limit,
              "budget": _budget,
              "cost_start":_costStart,
              "cost_end":_costSlut,
              "stockMin":_stockMin,
              "countrySearch":_countrySearch
          }, function(data, status)
        {

          $("#vsg-content-data").append(data)
          setTimeout(function()
            {
              _this.isWorking = false;
               _this.hideSelectedPresentInlist();
                system.endWork();
                 $("#vsg-content-data").show();
            }, 500
          );

        }
      );
    };

    _this.testTemplateCompatible = function (){

        $.post("index.php?rt=present/testTemplateCompatible", {shopID: _shopId}, function(returData, status){
            // 0:kun nyeste layout, 1:alle layout 4 , 3: layout 1,2 eller 3,  3:mix layout
            if(returData.data.test > 0){

                    //$("#vsg-leftpanel-menu").height("68px");
                    $(".update_to_template_4").css("display","block");
                    _this.setEventupdateToTemplate5();


          }
        },"json")

    }
    _this.setEventupdateToTemplate5 = function (){
        $(".update_to_template_4").unbind("click").click(
            function(){
                _this.doUpdateToTemplate5();
            })

    }
    _this.doUpdateToTemplate5 = function (){
        let doit = confirm("Ønsker du at ændre, så alle gaverne benytter den nye skabelon?")
        if(doit == false) return;
        $.post("index.php?rt=present/doUpdateToTemplate5", {shopID: _shopId}, function(returData, status){
            $("#vsg-leftpanel-menu").height("50px");
            $(".update_to_template_4").hide();
            alert("Alle gaver opdateret til den nye præsentationsskabelon")
        },"json")
    }


    _this.searchResetFields = function() {



        $("#vsg-search").val("");
        $("#pim-budget-budget").val("none");
        $("#kostpris-start").val("");
        $("#kostpris-end").val("");
        $("#searchStockMin").val("");
        $(".vsgNoFreeSearch").show();
        _this.initSearch()
    }

    _this.initSearch = function() {
        if ($("#showBrands").is(":checked")) {
            _this.showBrands();

        } else {


            let _costStart = $("#kostpris-start").val();
            let _costSlut = $("#kostpris-end").val();
            console.log(_costStart)
            console.log(_costSlut)
            if (_costStart * 1 <= 0 && _costStart != "") {
                alert("Start Kostpris skal være højere end 1");
                return;
            }
            if (_costStart == "" && _costSlut * 1 > 0) {
                alert("Start kostpris skal være udfyldt");
                return;
            }
            if (_costStart * 1 > 0 && _costSlut == "") {
                alert("Slut Kostpris skal være udfyldt");
                return;
            }
            if ((_costStart * 1 > 0 && _costSlut * 1 >= 0) && (_costStart * 1 > _costSlut * 1)) {
                alert("Kostpris start skal være mindre end Kostpris slut");
                return;
            }


            $("#vsg-content-data").html("")
            var searhcTxt = $("#vsg-search").val();
            _this.offset = -30;
            if (searhcTxt == "") {
                _this.load();
            } else {
                _this.search()
            }
        }
    }
    
    _this.search = function() {

        let  _budget = $("#pim-budget-budget").val();
        let _costStart = $("#kostpris-start").val();
        let _costSlut = $("#kostpris-end").val();
        let _stockMin = $("#searchStockMin").val();
        let _countrySearch = $('input[name="countrySearch"]:checked').val();
        _this.offset += 30;
      var searchTxt = $("#vsg-search").val();


        $.post("index.php?rt=present/searchPresentsV2", {
                "search": searchTxt,
                "offset": _this.offset,
                "limit": _this.limit,
                "budget": _budget,
                "cost_start":_costStart,
                "cost_end":_costSlut,
                "stockMin":_stockMin,
            "countrySearch":_countrySearch
                }, function(data, status)
          {
            if(_this.offset == 0)  $("#vsg-content-data").html(data)
            else $("#vsg-content-data").append(data)
            setTimeout(function()
              {
                _this.isWorking = false;
                _this.hideSelectedPresentInlist();
                system.endWork();


              }, 500
            );
          }
        );




    };
    _this.showBrands = function(){
        $.post("index.php?rt=present/showBrands", {}, function(data, status)
            {
                $("#vsg-content-data").html(data)
            }
        );
    },


    // ----------  søgning end  ------------
    // ----------  gaver valgt i shoppen  ------------
    _this.hideSelectedPresentInlist = function(){

        $.each( _this.allCopyOfId, function(key, value){
           if(value != 0){
//               $("#presentFlipId_"+value).hide();
            // $("#addPresent_"+value).html("<img src=\"views/media/icon/1373253494_plus_64_not.png\"  height=\"25\" width=\"25\" style=\"float:left; padding:5px;\">");
               $("#presentFlipId_"+value).hide();
           }
        })
    };
     _this.updateSortOrder = function(){
       var sortArr = [];
            $("#vsg-active-present .group h3").each(function(i, obj) {
                 sortArr.push($(obj).attr("data-id"));
            });
         system.work();
            $.post("index.php?rt=present/sortPresent_V2", {"sortList": sortArr.toString()}, function(returData, status){
                system.endWork();
       })
     };

    _this.initPresentInShop = function() {
        console.log("denne");
      var html = "";
      var htmlIsDeleted = "";
      var htmlIsDeactivated = "";
     // var cardShopList = [52,53,54,55,56,57,58,59,272,290,310,574,575,1832,1981,2395,2548,2549,2550,2558,2960,2961,2962,2963,4793,5117,4668];
      var cardShopList = [52,53,54,55,56,57,58,59,272,290,310,574,575,1832,9495,1981,2395,2550,2960,2961,2962,2963,4662,4668,4740,4793,5117,7121,8355, 8356, 8357, 8358, 8359, 8360, 8361, 8362, 8363, 8364, 8365, 8366,8271, 8336, 9321];
      //_compamyData deff in company.js
      var presentList = _compamyData.data.shop[0].presents;

        // Simpel søgefelt med checkbox
        html = '<div style="position: sticky; top: 0; z-index: 10; padding: 10px; background: #f5f5f5; margin-bottom: 10px;">';
        html += '<input type="text" id="shopPresentSearch" placeholder="Søg..." style="width: 70%; padding: 8px; border: 1px solid #ddd;">';
      //  html += ' <label><br><input type="checkbox" id="filterOriginal"> Kun originale 🎁</label>';
        html += '</div>';
//console.log( _compamyData.data.shop[0])
      $.each(presentList, function(key, value)
        {
            if (value.present.attributes.hasOwnProperty('media')) {
                if (value.present.attributes.media.isArray) {
                    value.present.attributes.first_image_media_path = value.present.attributes.media[0].attributes.media_path;
                }
            }
            _this.allCopyOfId.push(value.present.attributes.copy_of)
          if(value.active == 1 ) {

            if(value.is_deleted == 0){

                // value.present.attributes.nav_name;

                let presentHeadlineName = value.present.attributes.nav_name;
                if( _localisation == 4){
                    console.log("presentHeadlineName")
                    presentHeadlineName =  value.present.attributes.descriptions[3].attributes.caption
                    console.log("presentHeadlineName")
                }

                let catName = "";
                catName = findPresentCategoryName(_compamyData.data.shop[0],value.present.attributes.id,'dk');
                if(catName){
                        catName = "<hr><center><< "+catName+" >></center>";
                } else {
                    catName = "";
                }

                let originalGift = value?.present?.attributes?.original_gift === 1
                    ? '<span class="original-gift-icon" title="Original gift">🎁</span>'
                    : "";
                html += "<div class=\"group\" id=\"vsg-item_"+value.present.attributes.id+"\" > <h3 class=\"vsg-accordion\"  data-id=\""+value.present.attributes.id+"\">"+originalGift+" "+presentHeadlineName+"<div id=\"catOverview"+value.present.attributes.id+"\"  >"+catName+"</div> </h3> <div><p><center>"

                html += "<img src=\"views/media/user/"+value.present.attributes.first_image_media_path+".jpg\"  width=40% / ><hr />";
                html += "<div class=\"vsg-model-container_"+value.present.attributes.id+"\"></div>";
                html += "<hr /><img class=\"mouse salemane-noshow\" src=\"views/media/icon/1373253282_pencil_64.png\" onclick=\"valgshopGaver.edit('"+value.present.attributes.id+"')\"\" height=\"25\" width=\"25\" style=\"float:left; padding:5px;\">";
                html += "<img class=\"mouse salemane-noshow\" src=\"views/media/icon/notActive.png\" onclick=\"valgshopGaver.deactivate('"+value.present.attributes.id+"')\"\" height=\"25\" width=\"25\" style=\"float:left; padding:5px;\">";

                html += "<img class=\"mouse\"  src=\"views/media/icon/1373253296_delete_64.png\" onclick=\"valgshopGaver.deletePresentInShop('"+value.present.attributes.id+"')\" height=\"25\" width=\"25\"  style=\"float:right; padding:5px;\">"
                html += "</center></p></div></div>";
            }
  /*
            if(value.is_deleted == 1){
                htmlIsDeleted += "<div class=\"group\" id=\"vsg-delete-item_"+value.present.attributes.id+"\"> <h3 class=\"vsg-accordion-deleted\"  data-id=\""+value.present.attributes.id+"\">"+value.present.attributes.nav_name+" </h3> <div><p><center>"
                htmlIsDeleted += "<div class=\"vsg-model-container_"+value.present.attributes.id+"\"></div>";
                htmlIsDeleted += "<img class=\"mouse\"  src=\"views/media/icon/1373253494_plus_64.png\" onclick=\"valgshopGaver.activateDeletePresentInShop('"+value.present.attributes.id+"')\" height=\"25\" width=\"25\"  style=\"float:right; padding:5px;\">"
                htmlIsDeleted += "</center></p></div></div>";
            }
            if(value.is_deleted == 1){
                htmlIsDeactivated += "<div class=\"group\" id=\"vsg-deactive-item_"+value.present.attributes.id+"\"> <h3 class=\"vsg-accordion-deactive\"  data-id=\""+value.present.attributes.id+"\">"+value.present.attributes.nav_name+" </h3> <div><p><center>"
                htmlIsDeactivated += "<div class=\"vsg-model-container_"+value.present.attributes.id+"\"></div>";
                htmlIsDeactivated += "<img class=\"mouse\"  src=\"views/media/icon/1373253494_plus_64.png\" onclick=\"valgshopGaver.activateDeactivePresentInShop('"+value.present.attributes.id+"')\" height=\"25\" width=\"25\"  style=\"float:right; padding:5px;\">"
                htmlIsDeactivated += "</center></p></div></div>";
            }
   */

          }
        }
      )
      $("#vsg-inShopPresent-tab").tabs();

      $("#vsg-active-present").html(html)

        // Simpel søgefunktion
        function doSearch() {
            var searchText = $('#shopPresentSearch').val().toLowerCase();
            var onlyOriginal = $('#filterOriginal').is(':checked');

            $('#vsg-active-present .group').each(function() {
                var name = $(this).find('.vsg-accordion').text().toLowerCase();
                var isOriginal = $(this).attr('data-original') === 'yes';

                var show = true;

                // Check søgetekst
                if (searchText && !name.includes(searchText)) {
                    show = false;
                }

                // Check original filter
                if (onlyOriginal && !isOriginal) {
                    show = false;
                }

                $(this).toggle(show);
            });

            $("#vsg-active-present").accordion("refresh");
        }

        // Events
        $('#shopPresentSearch').on('keyup', doSearch);
        $('#filterOriginal').on('change', doSearch);
        let pimBudgetDropdownHtml = "";
        if( _localisation == 1 || _localisation == 5){
            pimBudgetDropdownHtml = `<option value="none">Intet budget valgt I DKK</option>
            <option value="60">60</option>
            <option value="75">75</option>
            <option value="80">80</option>
            <option value="85">85</option>
            <option value="90">90</option>
            <option value="100">100</option>
            <option value="150">150</option>
            <option value="200">200</option>
            <option value="250">250</option>
            <option value="300">300</option>            
            <option value="400">400</option>            
            <option value="560">560</option>            
            <option value="640">640</option>            
            <option value="720">720</option>                                    
            <option value="800">800</option>
            <option value="960">960</option>
            <option value="1040">1040</option>                        
            <option value="1200">1200</option>            
`;
        }
       if( _localisation == 4){
           pimBudgetDropdownHtml = `<option value="none">Intet budget valgt i NOK</option>
            <option value="65">65</option>
            <option value="90">90</option>
            <option value="130">130</option>
            <option value="135">135</option>
            <option value="140">140</option>
            <option value="150">150</option>
            <option value="170">170</option>
            <option value="175">175</option>
            <option value="190">190</option>
            <option value="225">225</option>                        
            <option value="230">230</option>            
            <option value="270">270</option>            
            <option value="300">300</option>            
            <option value="345">345</option>            
            <option value="400">400</option>            
            <option value="600">600</option>            
            <option value="690">690</option>
            <option value="800">800</option>
            <option value="1000">1000</option>
            <option value="1200">1200</option>
            <option value="2000">2000</option>                
            <option value="2400">2400</option>                                    
`;
       }
       $("#pim-budget-budget").html(pimBudgetDropdownHtml)

      $("#vsg-active-present")
      .accordion(
        {
        heightStyle: "content",
        collapsible: true,
        active: false,
        activate: function (event, ui) {
                var presentId;

                if($(this).find("h3").hasClass("ui-accordion-header-active")){

                    presentId = $(".ui-accordion-header-active.vsg-accordion").attr("data-id");
                    var html = "<div id='categoryContainer"+presentId+"'>asdf</div>"
                    html+= "<table width=100% class='vsg'>";
                    system.work();
                    $.post("index.php?rt=present/getModelsV2", {"present_id": presentId, "shop_id":_shopId}, function(returData, status){

                        var returData = JSON.parse(returData);
                        // hide_for_demo_user
                        returData.data[0].attributes.hide_for_demo_user == 0 ? hide_for_demo_user_checked = "" :  hide_for_demo_user_checked = "checked";
                        returData.data[0].attributes.lock_for_sync == 0 ? lock_checked = "" :  lock_checked = "checked";
                        returData.data[0].attributes.show_if_home_delivery == 1 ? show_if_home_delivery_checked = "" :  show_if_home_delivery_checked = "checked";



                        html+="<tr><td colspan=4 id='hide_week_"+presentId+"' ></td></tr>"
                        if( cardShopList.indexOf(parseInt(_shopId)) >=0 ){
                            html+="<tr class='salemane-noshow'><td colspan=3><label>Skjul gave ved hjemmelevering</label></td><td><input  onclick=\"valgshopGaver.updateHideHomeDelevery('"+presentId+"')\" "+show_if_home_delivery_checked+"  type=\"checkbox\" id=\"show_if_home_delivery_"+presentId+"\" /></td></tr>"
                        }

                            returData.data[0].attributes.show_master == 0 ? showMasterPresent = "" :  showMasterPresent = "checked";
                            if(returData.data.length == 2)
                            {
                                html+="<tr class='salemane-noshow'><td colspan=3><label>Vælg mellem – Mastergave vises, hvis én lukker</label></td><td><input  onclick=\"valgshopGaver.showMasterPresent('"+presentId+"')\" "+showMasterPresent+"  type=\"checkbox\" id=\"showMasterPresent_"+presentId+"\" /></td></tr>"
                            }
               

                        html+="<tr class='salemane-noshow'><td colspan=3><label>Skjul for test bruger1</label></td><td><input  onclick=\"valgshopGaver.updateHideForDemo('"+presentId+"')\" "+hide_for_demo_user_checked+"  type=\"checkbox\" id=\"hide_for_demo_user_"+presentId+"\" /></td></tr>"
                        html+="<tr class='salemane-noshow'><td colspan=3><label>Lås gave</label></td><td><input  onclick=\"valgshopGaver.lockPresent('"+presentId+"')\" "+lock_checked+"  type=\"checkbox\" id=\"lock_checked_"+presentId+"\" /></td></tr>"
                        $.each(returData.data, function(key, value){
                            if(value.attributes.active == 1){ // det er omvendet 1 betyder deaktiveret
                                html+="<tr><td width=45% rowspan='2'>"+value.attributes.model_name+"</td><td width=30% rowspan='2'>"+value.attributes.model_no+"</td><td width=20% rowspan='2'>"+value.attributes.model_present_no+"</td><td width=5% > <input  onclick=\"valgshopGaver.updatePresentState('"+value.attributes.id+"')\"  type=\"checkbox\" id=\"vsg-sampak-present_"+value.attributes.id+"\" /></td></tr>";
                                html+="<tr><td><img class='salemane-noshow' width='25' height='25' src='views/media/icon/PurchaseNoOrder-50.png' title='Ingen kvittering, ej valgt gave' /></td></tr>";
                            } else {
                                html+="<tr><td width=45% rowspan='2'>"+value.attributes.model_name+"</td><td width=30% rowspan='2'>"+value.attributes.model_no+"</td><td width=20% rowspan='2'>"+value.attributes.model_present_no+"</td><td width=5% > <input  onclick=\"valgshopGaver.updatePresentState('"+value.attributes.id+"')\" checked type=\"checkbox\" id=\"vsg-sampak-present_"+value.attributes.id+"\" /></td></tr>";
                                if(value.attributes.msg1 == 0){
                                   html+="<tr><td><img style='cursor:pointer' onclick=\"customReceipt.init('"+value.attributes.id+"','"+value.attributes.msg1+"','"+presentId+"')\"  width='25' height='25' src='views/media/icon/PurchaseNoOrder-50.png' title='' /></td></tr>";
                                } else {
                                   html+="<tr><td><img style='cursor:pointer' onclick=\"customReceipt.init('"+value.attributes.id+"','"+value.attributes.msg1+"','"+presentId+"')\"  width='25' height='25' src='views/media/icon/Purchase Order-50.png' title='' /></td></tr>";
                                }
                            }

                     });

                     html+="</table><div id='gift-presentation-child-"+presentId+"'></div>";

                     $(".vsg-model-container_"+presentId).html(html)

                     if( cardShopList.indexOf(parseInt(_shopId)) >=0 ){
                        _this.readDeadlineWeek(presentId);
                     }
                        system.endWork();
                        _this.readPresentationChilds(presentId)
                        console.log(returData.data[0])
                        if(returData.data[0].attributes.shop_present_category_list.length > 0 )
                        {
                            _this.handleCategories(
                                presentId,
                                returData.data[0].attributes.shop_present_category_list,
                                returData.data[0].attributes.shop_present_category_id
                            )
                        }


                    })
                }
                if (ui.newPanel.length > 0) {
                    _this.giftedChildrenCount = 0;
                    _this.presentId = presentId;
                } else {
                    _this.giftedChildrenCount = 0;
                    _this.presentId = 0;
                }
                // handle if item are added as parent or child

            },
        header: "> div > h3"
        }
      )
      .sortable(
        {
        axis: "y",
        handle: "h3",
        stop: function(event, ui) {
            // IE doesn't register the blur when sorting
            // so trigger focusout handlers to remove .ui-state-focus
            ui.item.children("h3").triggerHandler("focusout");

            // Refresh accordion to handle new order
            $(this).accordion("refresh");
            var sortArr = [];
            $("#vsg-active-present .group h3").each(function(i, obj) {
                const dataId = $(obj).attr("data-id");
                if (dataId && dataId.trim() !== "") {
                    sortArr.push(dataId);
                }
            });
            $.post("index.php?rt=present/sortPresent_V2", {"sortList": sortArr.toString()}, function(returData, status){
                var sortArr = [];
                $("#vsg-active-present .group h3").each(function(i, obj) {
                    const dataId = $(obj).attr("data-id");
                    if (dataId && dataId.trim() !== "") {
                        sortArr.push(dataId);
                    }
                });
                $.post("index.php?rt=present/checkIndexOrder", {"sortList": sortArr.toString(),"shop_id":_shopId}, function(returData, status){
                    if(!returData.status){
                        alert("Fejl i sortering")
                    }
                }, "json")
            })

          },
          activate: function (event, ui) {

          }
        }
      );
      htmlIsDeleted = "";
      $("#vsg-deleted-present").html(htmlIsDeleted);
      $("#vsg-deleted-present").accordion(        {
        heightStyle: "content",
        collapsible: true,
        active: false,
        activate: function (event, ui) {

            },
        header: "> div > h3"
        });

        htmlIsDeactivated = "";
      $("#vsg-deactive-present").html(htmlIsDeactivated);
      $("#vsg-deactive-present").accordion(        {
        heightStyle: "content",
        collapsible: true,
        active: false,
        activate: function (event, ui) {

            },
        header: "> div > h3"
        });
    };
      const notSelectedLabels = {
          "dk": "ej valgt",
          "en": "not selected",
          "de": "nicht ausgewählt",
          "no": "ikke valgt",
          "se": "ej vald"
      };
      _this.handleCategories = function(presentId, catList, selectedCatID){
          let langID = $('input[name="localisation"]:checked').attr('data-id');
          lang = "dk";
          if(langID == 4){ lang="no"  }
          if(langID == 5){ lang="se"  }

          const $container = $("#categoryContainer" + presentId);

          // Ryd eksisterende indhold
          $container.empty();

          // Opret select-element i containeren
          let $select = $('<select style="font-size: 1rem; padding: 5px; max-width: 100px ">')
              .attr('id', 'categorySelect' + presentId)
              .appendTo($container);

          // Tilføj "ej valgt" muligheden og vælg den hvis selectedCatID er 0
          $('<option>')
              .val("0")
              .text(notSelectedLabels[lang] || "ej valgt")
              .prop('selected', selectedCatID == 0 || !selectedCatID)
              .appendTo($select);

          // Tilføj muligheder fra data
          catList.forEach(item => {
              if (item.attributes && item.attributes.id) {
                  // Brug navnet for det valgte sprog, eller fald tilbage til name_dk
                  const langField = `name_${lang}`;
                  const displayText = item.attributes[langField] || item.attributes.name_dk || `ID: ${item.attributes.id}`;

                  // Opret og tilføj mulighed
                  $('<option>')
                      .val(item.attributes.id)
                      .text(displayText)
                      .prop('selected', item.attributes.id == selectedCatID)
                      .appendTo($select);
              }
          });

          // Tilføj change-event
          $select.off('change').on('change', function() {
              const selectedCategoryId = $(this).val();
              let selectedText = $(this).find('option:selected').text();

              $.post("index.php?rt=present/updateCategory", {"shopID": presentId,"categoryID":selectedCategoryId}, function(returData, status){
                    selectedText = selectedCategoryId == 0 ? "" : "<hr><center><< "+selectedText+" >></center>";
                    $("#catOverview"+presentId).html(selectedText)
              })

          });
      };

    _this.updateCategories = function (shopID,selectedCategoryId){

    }

      _this.removePresentationChilds = function(presentId){
          const userConfirmed = confirm("Vi du fjerne denne gave?");
          if (!userConfirmed) {
             return
          }
          $.post("index.php?rt=presentChild/remove", {present_id:presentId, shop_id: _shopId}, function(returData, status){
              _this.readPresentationChilds(_this.presentId);

          })
      }

      _this.readPresentationChilds = function(presentId) {
          system.work();
          _this.giftedChildrenCount = 0;
          $.post("index.php?rt=presentChild/read", {
              present_id: presentId,
              shop_id: _shopId
          }, function (returData, status) {
              if (returData?.data?.present?.length) {

                  _this.buildPresentationChilds(returData.data,presentId);
                  _this.presentationChildTypeEvents(returData.data)
                  system.endWork();
              } else {
                  $("#gift-presentation-child-"+presentId).html("");
                  system.endWork();
              }
          }, 'json');
      }
      _this.buildPresentationChilds = function(data,parentpresentId) {

          let tableHTML = ` <hr><h3>Præsentationsgaver</h3>

                    <div class="pc-custom-radio">
                      <input type="radio" id="pc-sampak${_this.presentId}" data-id="1" name="pc-option${_this.presentId}" class="pc-option${_this.presentId}">
                      <label for="pc-sampak${_this.presentId}">Sampak</label>
                    </div>
                    
                    <div class="pc-custom-radio">
                      <input type="radio" id="pc-vaelg${_this.presentId}" data-id="2" name="pc-option${_this.presentId}" class="pc-option${_this.presentId}">
                      <label for="pc-vaelg${_this.presentId}">Vælg mellem</label>
                    </div>
                            
                    <table class="present-table">
                        <thead>
                            <tr>
                                <th>Gave Navn</th>
                                <th>Billede</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
        `;
          // Sort presents according to order_index if it exists and has content
          let presents = [...data.present];
          const orderIndex = data.presentation_group?.attributes?.order_index;

          if (orderIndex && orderIndex.trim() !== '') {
              const orderArray = orderIndex.split(',').map(id => id.trim());

              // Sort presents based on order_index
              presents.sort((a, b) => {
                  const indexA = orderArray.indexOf(a.attributes.id.toString());
                  const indexB = orderArray.indexOf(b.attributes.id.toString());

                  // If both items aren't in order_index, maintain original order
                  if (indexA === -1 && indexB === -1) return 0;
                  // If only one item isn't in order_index, put it at the end
                  if (indexA === -1) return 1;
                  if (indexB === -1) return -1;

                  return indexA - indexB;
              });
          }
          _this.giftedChildrenCount = presents.length;

          presents.forEach(gift => {
              gift = gift.attributes;
              tableHTML += `
            <tr data-id="${gift.id}">
                <td>${gift.nav_name}</td>
                <td><img src="views/media/user/${gift.media_path}.jpg" width="40%"></td>
                <td>
                        <img class="pc-action-icon" src="views/media/icon/1373253282_pencil_64.png" onclick="valgshopGaver.edit('${gift.id}')" height="20" width="20" style="float:left; padding:5px;">
                        <img class="pc-action-icon" src="views/media/icon/1373253296_delete_64.png" onclick="valgshopGaver.removePresentationChilds('${gift.id}')" height="20" width="20" style="float:right; padding:5px;">
           
                    </div>
                </td>
            </tr>
        `;
          });

          tableHTML += `
        </tbody>
    </table>
    `;
          $("#gift-presentation-child-"+parentpresentId).html(tableHTML);

          // Initialize sortable after the table is added to DOM
          $(`#gift-presentation-child-${parentpresentId} .present-table tbody`).sortable({
              handle: 'td',  // Use first column as handle
              axis: 'y',
              cursor: 'move',
              helper: function(e, tr) {
                  // Maintain cell widths during drag
                  var $originals = tr.children();
                  var $helper = tr.clone();
                  $helper.children().each(function(index) {
                      $(this).width($originals.eq(index).width());
                  });
                  return $helper;
              },
              update: function(event, ui) {
                  // Get new order of gifts
                  const newOrder = [];
                  $(this).find('tr').each(function() {
                      newOrder.push($(this).data('id'));
                  });
                  _this.updatePresentationChildOrderIndex(newOrder)
              }
          }).disableSelection();
      }
    _this.presentationChildTypeEvents = function (data){
        let type = data?.presentation_group?.attributes?.type || "";
        if (type === 1) {
            $('#pc-sampak'+_this.presentId).prop('checked', true);
        } else if (type === 2) {
            $('#pc-vaelg'+_this.presentId).prop('checked', true);
        }
        $('.pc-option'+_this.presentId).off('change').on('change', function() {
            _this.updatePresentationChildType($(this).attr("data-id")) ;

        });
    }
    _this.updatePresentationChildType = function(type){
        $.post("index.php?rt=presentationGroup/update", {group_id: _this.presentId,type:type}, function(returData, status){
            if(returData.status==0){ alert(returData.message); return; }

        },"json")
    }
    _this.updatePresentationChildOrderIndex = function(order_index){
          $.post("index.php?rt=presentationGroup/update", {group_id: _this.presentId,order_index:order_index.toString()}, function(returData, status){
              if(returData.status==0){ alert(returData.message); return; }
          },"json")
    }

    /** ----- Gift hide in weeks ------- **/
     _this.readDeadlineWeek = function(presentId){
            $.post("index.php?rt=presentModelOptions/loadOption", {presentID: presentId,shopID:_shopId}, function(returData, status){
                let html ="<table><tr  rowspan='2'><td>Gaven vises ikke i følgende deadline </td></tr>"
                for (const [key, value] of Object.entries(returData.data)) {
                  let checked = "";
                  if(value != ""){
                    checked = "checked";
                  }
                  html+="<tr> <td>"+key+"</td> <td><input type='checkbox' id='vehicle1' value='' "+checked+" onclick=\"valgshopGaver.toggleVisibilityGiftDeadline('"+key+"','"+presentId+"')\"  ></td></tr>";
                }
                html+= "</table>";
                $("#hide_week_"+presentId).html(html);
            },"json")



     }
     _this.toggleVisibilityGiftDeadline = function(deadline,presentId){
            $.post("index.php?rt=presentModelOptions/updateOption", {presentId: presentId,deadline:deadline}, function(returData, status){
                alert("Opdateret");
            })


     }






    //  de-activate

      _this.deactivate = function(presentId){

        if(confirm("De-activate present ")){
            $.post("index.php?rt=present/deactivatePresent_v2", {"presentId":presentId,"shop_id":_shopId}, function(returnData, status)
            {
                $("#vsg-item_"+presentId).remove()
            })
        }
      }

          _this.showDeactivedPresents = function(){
            $.post("index.php?rt=present/getDeactivedPresents_v2", {"shop_id":_shopId}, function(returData, status)
            {
                 var returData = JSON.parse(returData);
                 console.log(returData)
                 var presentList = returData.data;
                 var htmlIsDeleted = "";

      $.each(presentList, function(key, value)
        {
        if( value.attributes.active == 0 && value.attributes.is_deleted == 0) {
                var hide_for_demo_user_checked = "";
                value.attributes.hide_for_demo_user == 0 ? hide_for_demo_user_checked = "" :  hide_for_demo_user_checked = "checked";
                htmlIsDeleted += "<div class=\"group\" id=\"vsg-deactive-item_"+value.attributes.present_id+"\"> <h3 class=\"vsg-accordion-deactive\"  data-id=\""+value.attributes.id+"\">"+value.attributes.nav_name+" </h3> <div><p><center>"
                htmlIsDeleted +=  "<label>Vis kun for test bruger</label><input onclick=\"valgshopGaver.updateOnlyForDemo(this)\""+hide_for_demo_user_checked+"  type=\"checkbox\" data-id=\""+value.attributes.present_id+"\" /><br><br>";
                htmlIsDeleted += "<div class=\"vsg-model-container_"+value.attributes.id+"\"></div>";
                htmlIsDeleted += "<hr /><img class=\"mouse\" src=\"views/media/icon/1373253282_pencil_64.png\" onclick=\"valgshopGaver.edit('"+value.attributes.present_id+"')\"\" height=\"25\" width=\"25\" style=\"float:left; padding:5px;\">";
                htmlIsDeleted += "<img class=\"mouse\"  src=\"views/media/icon/1373253494_plus_64.png\" onclick=\"valgshopGaver.activateDeactivatedPresentInShop('"+value.attributes.present_id+"')\" height=\"25\" width=\"25\"  style=\"float:right; padding:5px;\">"
                htmlIsDeleted += "</center></p></div></div>";
            }


        })

        $("#vsg-deactive-present").html(htmlIsDeleted);
        $("#vsg-deactive-present").accordion("refresh");
       })
    }


        _this.activateDeactivatedPresentInShop = function(presentId){
             $.post("index.php?rt=present/activateDeactivatedPresents_v2", {"presentId":presentId}, function(returData, status)
            {
                 var returData = JSON.parse(returData);


                html = "";

                html += "<div class=\"group\" id=\"vsg-item_"+returData.data[0].attributes.present_id+"\" > <h3 class=\"vsg-accordion\"  data-id=\""+returData.data[0].attributes.present_id+"\">"+returData.data[0].attributes.nav_name+" </h3> <div><p><center>"
                html += "<img src=\"views/media/user/"+returData.data[0].attributes.media_path+".jpg\"  width=40% / ><hr />";
                html += "<div class=\"vsg-model-container_"+returData.data[0].attributes.present_id+"\"></div>";
                html += "<hr /><img class=\"mouse salemane-noshow\" src=\"views/media/icon/1373253282_pencil_64.png\" onclick=\"valgshopGaver.edit('"+returData.data[0].attributes.present_id+"')\"\" height=\"25\" width=\"25\" style=\"float:left; padding:5px;\">";
                html += "<img class=\"mouse salemane-noshow\" src=\"views/media/icon/notActive.png\" onclick=\"valgshopGaver.deactivate('"+returData.data[0].attributes.present_id+"')\"\" height=\"25\" width=\"25\" style=\"float:left; padding:5px;\">";
                html += "<img class=\"mouse\"  src=\"views/media/icon/1373253296_delete_64.png\" onclick=\"valgshopGaver.deletePresentInShop('"+returData.data[0].attributes.present_id+"')\" height=\"25\" width=\"25\"  style=\"float:right; padding:5px;\">"
                html += "</center></p></div></div>";
                $("#vsg-deactive-item_"+returData.data[0].attributes.present_id).fadeOut();
                $("#vsg-active-present").append(html);
                $( "#vsg-active-present" ).accordion( "refresh" );
                _this.updateSortOrder();
            })
    };








     // ----------  CRUD  ------------


      _this.edit = function(presentId){
      
          // $("#vsgSavePresentBtn").html("<button class=\"button\" style=\"background-color: #4CAF50; color:white; font-size:14px; padding:5px;\"  data-id=\"unik\" onclick=\"saveFromValgshop(\'"+presentId+"\')\">Opdatere unik gave</button")
               $("#vsgSavePresentBtn").html("<img id=\"vsgSavePresentBtn\" style=\"cursor: pointer;\" onclick=\"saveFromValgshop(\'"+presentId+"\')\" src=\"views/media/icon/1373253284_save_64.png\" height=\"25\" width=\"25\" alt=\"gem og luk\" />");

                $("#frontMenu").hide()
                $("#vsg-leftpanel").hide()
              _dropTarget  = "";
              _shopEdit = true;

                _unik =true;
               _unikUpdata = true;
            gaveAdmin.editGiftUnik(presentId )
    $("#vsgEditBox").show();
/*
        $("#vsgEditBox").animate({
            width: "toggle"
        });
 */
     };

      _this.closeEdit = function(){
          $("#frontMenu").toggle()
           $("#vsg-leftpanel").toggle()
          _shopEdit = false;

        $("#vsgEditBox").animate({
            width: "toggle"
        });
        reInitTinyce()
        $(".shopTabs").show();
      }
      // lock present so et can't update
     _this.lockPresent = (presentId) => {
            var active = 0;
            if($('#lock_checked_' + presentId).is(":checked")){
                active = 1;
            }
            var r = confirm("Vil du låse gaven så den ikke kan synkroniseres");
            if (r == true) {
                $.post("index.php?rt=present/updateLockPresent_v2", {lock_for_sync:active,id:presentId }, function(returData, status){})
            } else {
              if(active == "1" ){
                $('#lock_checked_' + presentId).prop( "checked",false )
               }
              else {
                $('#lock_checked_' + presentId).prop( "checked",true )
              }
            }

     };
      _this.showMasterPresent = (presentId) => {
          var active = 0;
          if($('#showMasterPresent_' + presentId).is(":checked")){
              active = 1;
          }
          var r = confirm("Vil du vise master gave når en af gaverne lukker");
          if (r == true) {
              $.post("index.php?rt=present/updateShowMasterPresent", {show_master:active,id:presentId }, function(returData, status){})
          } else {
              if(active == "1" ){
                  $('#showMasterPresent_' + presentId).prop( "checked",false )
              }
              else {
                  $('#showMasterPresent_' + presentId).prop( "checked",true )
              }
          }

      };

     _this.updateHideHomeDelevery  = function(presentId){
            var active = 1;
            if($('#show_if_home_delivery_' + presentId).is(":checked")){
                active = 0;
            }
            var r = confirm("Vil du skjule/vise gaven for en bruger med hjemmelevering");
            if (r == true) {

                $.post("index.php?rt=present/updateHideForHomedelevery_v2", {"present_id": presentId,"active":active}, function(returData, status){})
            } else {
              if(active == "1" ){
                $('#show_if_home_delivery_' + presentId).prop( "checked",false )
               }
              else {
                $('#show_if_home_delivery_' + presentId).prop( "checked",true )
              }
            }

      };

     _this.updateHideForDemo = function(presentId){
            var active = 0;
            if($('#hide_for_demo_user_' + presentId).is(":checked")){
                active = 1;
            }
            var r = confirm("Vil du skjule gaven for test bruger.\n Ændringen foretages i system med det samme");
            if (r == true) {
                $.post("index.php?rt=present/updateHideForDemo_v2", {"present_id": presentId,"active":active}, function(returData, status){})
            } else {
              if(active == "1" ){
                $('#hide_for_demo_user_' + presentId).prop( "checked",false )
               }
              else {
                $('#hide_for_demo_user_' + presentId).prop( "checked",true )
              }
            }

      };
      _this.updateOnlyForDemo = function(ele){
            var active = 0;

            if($(ele).is(":checked")){
                active = 2;
            }
            var r = confirm(" Ændringen foretages i system med det samme");
            if (r == true) {
                var presentId = $(ele).attr("data-id")
                $.post("index.php?rt=present/updateHideForDemo_v2", {"present_id": presentId,"active":active}, function(returData, status){})
            } else {
              if(active == "2" ){
                $(ele).prop( "checked",false )
               }
              else {
                $(ele).prop( "checked",true )
              }
            }

      };
      _this.updatePresentState = function(presentId){
            var active = "1";
            if($('#vsg-sampak-present_' + presentId).is(":checked")){
                active = 0;
            }
            var r = confirm("Vil du ændre status for visning af gaven i shoppen.\n Ændringen foretages i system med det samme");
            if (r == true) {
                $.post("index.php?rt=present/updateSampakPresent_v2", {"present_id": presentId,"active":active}, function(returData, status){})
            } else {
              if(active == "1" ){
                $('#vsg-sampak-present_' + presentId).prop( "checked",true )
               }
              else {
                $('#vsg-sampak-present_' + presentId).prop( "checked",false )
              }
            }

      };
      _this.addToShop = function (presentId){
          let presentIdF = presentId;
          _this.doAddToShop(presentIdF)



      }
      _this.adChildState1 = function(presentId) {

          $.post("index.php?rt=present/addChildToPresentState1", {"present_id": presentId,"shop_id":_shopId,"parentPresent_id":_this.presentId }, function(returData, status){
              console.log(_localisation)


              var returData = JSON.parse(returData);
              if(returData.status == 1){
                  _this.adChildState2(returData.data.present[0]["id"]);

              } else {
                  system.endWork();
                  alert("Error adding gift")
              }
          })

      };
      _this.adChildState2 = function(presentId) {
          $.post("index.php?rt=present/addChildToPresentState2", {"present_id": presentId,"shop_id":_shopId}, function(returData, status){

             var returData = JSON.parse(returData);
              if(returData.status == 1){
                  _this.readPresentationChilds(_this.presentId)

              } else {
                  system.endWork();
                  alert("Error adding gift")
              }

          })
      }

      _this.doAddToShop = function(presentId) {

          system.work();

          if(_this.presentId != 0){
              // Count child elements dynamically to get accurate count
              let currentChildCount = 0;

              // Make an AJAX call to get the current count of children
              $.post("index.php?rt=presentChild/read", {
                  present_id: _this.presentId,
                  shop_id: _shopId
              }, function(returData, status) {
                  if (returData?.data?.present?.length) {
                      currentChildCount = returData.data.present.length;

                      // Now check if adding another would exceed the limit
                      if(currentChildCount >= 3){
                          system.endWork();
                          alert("Der kan ikke vælges mere end 3 gaver");
                          return;
                      } else {
                          // Safe to add another child
                          _this.adChildState1(presentId);
                      }
                  } else {
                      // No children yet, safe to add
                      _this.adChildState1(presentId);
                  }
              }, 'json');

              return; // Return early as we're handling this asynchronously
          }


           $.post("index.php?rt=present/createUnikPresent_v2", {"present_id": presentId,"shop_id":_shopId}, function(returData, status){



               var returData = JSON.parse(returData);
               if(returData.status == 1){
                    var html = "";
                   let pindex = _localisation-1;

                   let ptitle = returData.data.present[0].nav_name
                   if(_localisation == 4){
                       ptitle =  returData.data.present[0].descriptions[pindex].caption;
                   }

                    var value = returData.data.present[0];
                    html += "<div class=\"group\" id=\"vsg-item_"+value.id+"\" > <h3 class=\"vsg-accordion\"  data-id=\""+value.id+"\">"+ptitle+" <div id=\"catOverview"+value.id+"\"  ></div></h3> <div><p><center>"
                    html += "<img src=\"views/media/user/"+value.present_media[0].media_path+".jpg\"  width=40% / ><hr />";
                    html += "<div class=\"vsg-model-container_"+value.id+"\"></div>";
                    html += "<hr /><img class=\"mouse salemane-noshow\" src=\"views/media/icon/1373253282_pencil_64.png\" onclick=\"valgshopGaver.edit('"+value.id+"')\"\"  height=\"25\" width=\"25\" style=\"float:left; padding:5px;\">";
                    html += "<img class=\"mouse salemane-noshow\" src=\"views/media/icon/notActive.png\" onclick=\"valgshopGaver.deactivate('"+value.id+"')\"\" height=\"25\" width=\"25\" style=\"float:left; padding:5px;\">";
                    html += "<img class=\"mouse\"  src=\"views/media/icon/1373253296_delete_64.png\" onclick=\"valgshopGaver.deletePresentInShop('"+value.id+"')\" height=\"25\" width=\"25\"  style=\"float:right; padding:5px;\">"
                    html += "</center></p></div></div>";
                    $("#vsg-active-present").append(html);
                   $("#vsg-active-present").accordion("option", "active", false);
                    $( "#vsg-active-present" ).accordion( "refresh" );
                    $("#presentFlipId_"+presentId).fadeOut();
                    _this.allCopyOfId.push(presentId)
                    system.endWork();
                    _this.updateSortOrder();
               } else {
                 system.endWork();
                 alert("Error adding gift")
               }
           })
    };
    _this.deletePresentInShop = function(presentId){


        if(confirm("Remove presents from shop")){
            $.post("index.php?rt=present/removePresent_v2", {"presentId":presentId,"shop_id":_shopId}, function(returnData, status)
            {

                returnData = JSON.parse(returnData);
                if(returnData.status == 0){
                    alert("Gaven kan ikke slette, da der er foretaget valg")
                    return;
                }
                let copyID = returnData.data.attributes.copy_of
                $("#vsg-item_"+presentId).remove()
                const index =  _this.allCopyOfId.indexOf(copyID);
                if (index > -1) { // only splice array when item is found
                    _this.allCopyOfId.splice(index, 1); // 2nd parameter means remove one item only
                }
                $("#presentFlipId_"+copyID).show();
                _this.giftedChildrenCount = 0;
                _this.presentId = 0;
            })
        }
    }
      _this.showDeletePresents_kunliste  = function(){
          $.post("index.php?rt=present/getDeletedPresents_v3", {"shop_id":_shopId}, function(returData, status)
          {
              var returData = JSON.parse(returData);
              console.log(returData)
              var presentList = returData.data;
              var htmlIsDeleted = "<table width='100%' style='border:border-collapse: collapse;'>";

              $.each(presentList, function(key, value)
              {
                    dateOnly = value.attributes.modified_datetime.date.split(" ")[0];
                    htmlIsDeleted+= `<tr><td style="border: 1px solid black;">${dateOnly}</td><td style="border: 1px solid black;">${value.attributes.model_present_no}</td><td style="border: 1px solid black;">${value.attributes.nav_name}</td></tr>`
              })

              $("#vsg-deleted-present").html(htmlIsDeleted);
              $("#vsg-deleted-present").accordion("refresh");
          })
      }


    _this.showDeletePresents = function(){
            $.post("index.php?rt=present/getDeletedPresents_v2", {"shop_id":_shopId}, function(returData, status)
            {
                 var returData = JSON.parse(returData);
                 console.log(returData)
                 var presentList = returData.data;
                 var htmlIsDeleted = "";

      $.each(presentList, function(key, value)
        {

        if(value.attributes.deleted == 1 && value.attributes.active == 0) {
                var hide_for_demo_user_checked = "";
                value.attributes.hide_for_demo_user == 0 ? hide_for_demo_user_checked = "" :  hide_for_demo_user_checked = "checked";
                htmlIsDeleted += "<div class=\"group\" id=\"vsg-delete-item_"+value.attributes.present_id+"\"> <h3 class=\"vsg-accordion-deleted\"  data-id=\""+value.attributes.id+"\">"+value.attributes.nav_name+" </h3> <div><p><center>"
                htmlIsDeleted += "<div class=\"vsg-model-container_"+value.attributes.id+"\"></div>";
                htmlIsDeleted +=  "<label>Vis kun for test bruger</label><input onclick=\"valgshopGaver.updateOnlyForDemo(this)\""+hide_for_demo_user_checked+"  type=\"checkbox\" data-id=\""+value.attributes.present_id+"\" /><br><br>";
                htmlIsDeleted += "<hr /><img class=\"mouse \" src=\"views/media/icon/1373253282_pencil_64.png\" onclick=\"valgshopGaver.edit('"+value.attributes.present_id+"')\"\" height=\"25\" width=\"25\" style=\"float:left; padding:5px;\">";
                htmlIsDeleted += "<img class=\"mouse\"  src=\"views/media/icon/1373253494_plus_64.png\" onclick=\"valgshopGaver.activateDeletePresentInShop('"+value.attributes.present_id+"')\" height=\"25\" width=\"25\"  style=\"float:right; padding:5px;\">"
                htmlIsDeleted += "</center></p></div></div>";
            }


        })

        $("#vsg-deleted-present").html(htmlIsDeleted);
        $("#vsg-deleted-present").accordion("refresh");
       })
    }

    _this.activateDeletePresentInShop = function(presentId){
             $.post("index.php?rt=present/activateDeletedPresents_v2", {"presentId":presentId}, function(returData, status)
            {
                 var returData = JSON.parse(returData);


                html = "";

                html += "<div class=\"group\" id=\"vsg-item_"+returData.data[0].attributes.present_id+"\" > <h3 class=\"vsg-accordion\"  data-id=\""+returData.data[0].attributes.present_id+"\">"+returData.data[0].attributes.nav_name+" </h3> <div><p><center>"
                html += "<img src=\"views/media/user/"+returData.data[0].attributes.media_path+".jpg\"  width=40% / ><hr />";
                html += "<div class=\"vsg-model-container_"+returData.data[0].attributes.present_id+"\"></div>";
                html += "<hr /><img class=\"mouse\" src=\"views/media/icon/1373253282_pencil_64.png\" onclick=\"valgshopGaver.edit('"+returData.data[0].attributes.present_id+"')\"\" height=\"25\" width=\"25\" style=\"float:left; padding:5px;\">";
                html += "<img class=\"mouse\" src=\"views/media/icon/notActive.png\" onclick=\"valgshopGaver.deactivate('"+returData.data[0].attributes.present_id+"')\"\" height=\"25\" width=\"25\" style=\"float:left; padding:5px;\">";
                html += "<img class=\"mouse\"  src=\"views/media/icon/1373253296_delete_64.png\" onclick=\"valgshopGaver.deletePresentInShop('"+returData.data[0].attributes.present_id+"')\" height=\"25\" width=\"25\"  style=\"float:right; padding:5px;\">"
                html += "</center></p></div></div>";
                $("#vsg-delete-item_"+returData.data[0].attributes.present_id).fadeOut();
                $("#vsg-active-present").append(html);
                $( "#vsg-active-present" ).accordion( "refresh" );
                _this.updateSortOrder();
            })
    };




    // ----------  modal gave tilstede i andre shops  ------------
    _this.useInOtherShops = function(id) {
      $.post("index.php?rt=present/getAllVariants", {"id": id}, function(returnData, status)
        {
          returnData = JSON.parse(returnData);
          var shoplist =[];

          $.each(returnData.data.master.shops, function(key, value)
            {
              shoplist.push(value.name);
            }
          );
          $.each(returnData.data.master_shopvariants.shops, function(key, value)
            {
              shoplist.push(value.name);
            }
          );
          var html = "<table width=\"100%\" class=\"vsg\">";
          $.each(shoplist, function(key, value)
            {
              html += "<tr><td>"+value+"</td></tr>";
            }
          );
          html += "</table>";
          $("#dialog-other-shop").html(html).dialog();

          // console.log(returnData.data.master.shops[0].name)
          //$( "#dialog-other-shop" ).dialog();
        }
      )
    };

  }
);

   function saveFromValgshop(id) {


         system.work();

        if(id == ""){
            _desc1 = "";
            _desc2 = "";
            _desc3 = "";
            _desc4 = "";
            _desc5 = "";
        }

        var formData = "";
        var present = {};

                present['id'] = id;

            present['name']          = Date.now(); //$("#presentsAdminName").val();
            present['nav_name']     = $("#NAVpresentsAdminName").val();
            present['present_no']    = $("#presentsAdminNr").val();

            present['present_list']  =  $("#presentsSubGiftList").val();
            present['price']         = $("#presentsAdminPrice").val();
            present['price_group']   = $("#prisentsAdminBudgetPrice").val();
            present['vendor']        = $("#presentsAdminlev").val();
            present['indicative_price'] = $("#prisentsAdminThePrice").val();
            present['prisents_nav_price'] = $("#prisents_nav_price").val();
            present['prisents_nav_price_no'] = $("#prisents_nav_price_no").val();
           if( $('#show_to_saleperson').is(":checked")){
                present['show_to_saleperson'] = "1"
               } else {
                    present['show_to_saleperson'] = "0"
               }
          if( $('#oko_present').is(":checked")){
            present['oko_present'] = "1"
          } else {
            present['oko_present'] = "0"
         }
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





        var  media = [];
        $( ".presentAdminImg" ).each(function( index ) {
            var id = $(this).attr("data-id");

            if(id != undefined){


                media.push({'media_path':id,'index': index});
            }

        });

        var logo = [];
        logo.push({'logo':$(".logo-img").attr("data-id"),'logo_size':$( ".log-admin-size" ).val()});
        var descriptions = [];

        var short_description_1 = Base64.encode(tinyMCE.get('presentsAdminShortDa').getContent({format : 'HTML'}));
            if(short_description_1 == "") { short_description_1 = "###"; }
        var long_description_1  = Base64.encode(tinyMCE.get('presentsAdminLongDa').getContent({format : 'HTML'}));
            if(long_description_1 == "") { long_description_1 = "###"; }
        var cap_1               = $("#presentsAdminHeadlineDa").val();
            if(cap_1 == "") { cap_1 = "###"; }
        var cap_pt_1 = $("#presentsAdminHeadlineDaPT").val();
            if(cap_pt_1 == "") {cap_pt_1 = "###";}

       var cap_paper_1 = $("#presentsAdminHeadlineDaPaper").val();
       if(cap_paper_1 == "") {cap_paper_1 = "###";}

        descriptions.push({'id':_desc1,'language_id':1,'caption': cap_1, 'caption_paper':cap_paper_1,'caption_presentation':cap_pt_1,'short_description':short_description_1,'long_description':long_description_1});

        var short_description_2 = Base64.encode(tinyMCE.get('presentsAdminShortEn').getContent({format : 'HTML'}));
            if(short_description_2 == "") { short_description_2 = "###"; }
        var long_description_2  = Base64.encode(tinyMCE.get('presentsAdminLongEn').getContent({format : 'HTML'}));
            if(long_description_2 == "") { long_description_2 = "###"; }
        var cap_2               = $("#presentsAdminHeadlineEn").val();
            if(cap_2 == "") { cap_2 = "###"; }
        var cap_pt_2 = $("#presentsAdminHeadlineEnPT").val();
            if(cap_pt_2 == "") {cap_pt_2 = "###";}


        descriptions.push({'id':_desc2,'language_id':2,'caption': cap_2, 'caption_presentation':cap_pt_2,'short_description':short_description_2,'long_description':long_description_2});


        var short_description_3 = Base64.encode(tinyMCE.get('presentsAdminShortDe').getContent({format : 'HTML'}));
            if(short_description_3 == "") { short_description_3 = "###"; }
        var long_description_3  = Base64.encode(tinyMCE.get('presentsAdminLongDe').getContent({format : 'HTML'}));
            if(long_description_3 == "") { long_description_3 = "###"; }
        var cap_3               = $("#presentsAdminHeadlineDe").val();
            if(cap_3 == "") { cap_3 = "###"; }
        var cap_pt_3 = $("#presentsAdminHeadlineDePT").val();
            if(cap_pt_3 == "") {cap_pt_3 = "###";}


        descriptions.push({'id':_desc3,'language_id':3,'caption': cap_3, 'caption_presentation':cap_pt_3,'short_description':short_description_3,'long_description':long_description_3});

        var short_description_4 = Base64.encode(tinyMCE.get('presentsAdminShortNo').getContent({format : 'HTML'}));
            if(short_description_4 == "") { short_description_4 = "###"; }
        var long_description_4  = Base64.encode(tinyMCE.get('presentsAdminLongNo').getContent({format : 'HTML'}));
            if(long_description_4 == "") { long_description_4 = "###"; }
        var cap_4               = $("#presentsAdminHeadlineNo").val();
            if(cap_4 == "") { cap_4 = "###"; }
        var cap_pt_4 = $("#presentsAdminHeadlineNoPT").val();
            if(cap_pt_4 == "") {cap_pt_4 = "###";}
        descriptions.push({'id':_desc4,'language_id':4,'caption': cap_4, 'caption_presentation':cap_pt_4,'short_description':short_description_4,'long_description':long_description_4});

        var short_description_5 = Base64.encode(tinyMCE.get('presentsAdminShortSv').getContent({format : 'HTML'}));
            if(short_description_5 == "") { short_description_5 = "###"; }
        var long_description_5  = Base64.encode(tinyMCE.get('presentsAdminLongSv').getContent({format : 'HTML'}));
            if(long_description_5 == "") { long_description_5 = "###"; }
        var cap_5               = $("#presentsAdminHeadlineSv").val();
            if(cap_5 == "") { cap_5 = "###"; }
        var cap_pt_5 = $("#presentsAdminHeadlineSvPT").val();
            if(cap_pt_5 == "") {cap_pt_5 = "###";}

        descriptions.push({'id':_desc5,'language_id':5,'caption': cap_5, 'caption_presentation':cap_pt_5,'short_description':short_description_5,'long_description':long_description_5});




        var variant = [];
        var langId = "";
        var variantId = "";
        $(".presentsVariant").each(function( index ) {

                var lineIndex = 0;
                $('tr', $(this)).each(function (obj, index) {

                    if($(this).find('.prisentVariantVal').length != 0){

                       var feltData = [];
                       feltData.push ({'variantId':$(this).attr("data-variantId")} )
                       langId = $(this).attr("data-id");
                       var  i = 0;
                       $('input', $(this)).each(function (obj, index) {
                           if(i == 0) { feltData.push ({'variant':this.value} ) }
                           if(i == 1) { feltData.push ({'variantSub':this.value} ) }
                           if(i == 2) { feltData.push ({'variantNr':this.value} ) }
                           i++;
                       })
                       $('.variantImg', $(this)).each(function (obj, index) {
                            let srcParts = this.src.split("/");
                            let imgId = srcParts[srcParts.length-1];
                            feltData.push ({'variantImg':imgId} )
                       })
                       $('.variantCheckbox', $(this)).each(function (obj, index) {

                            feltData.push ({'variantCheck':this.value} )
                       })
                       variant.push ( {'language_id':langId,'feltData':feltData,'sortOrder':lineIndex} )
                       lineIndex++;
                    }
                });
        });
        var formData = {
            'present':JSON.stringify(present),
            'media':JSON.stringify(media),
            'logo':JSON.stringify(logo),
            'descriptions':JSON.stringify(descriptions),
            'variant':JSON.stringify(variant),
            'moms':$('#moms option:selected').val()

        };
        logData = encodeURI(JSON.stringify(formData));
        logPresent = id

        $.post( "index.php?rt=ping/saveInPresentLog", { presentId: logPresent, logData:logData } );
        ajax(formData,"present/update","saveFromValgshopReturn","");




     }
    function saveFromValgshopReturn(){
      system.endWork();
     // $("#vsg-main").hide();
    valgshopGaver.closeEdit()
     reInitTinyce()
      alert("gave gemt")
    }

function findPresentCategoryName(shopData, presentId, language = 'dk') {
    try {
        // Get shop data from array if needed
        const shop = Array.isArray(shopData) ? shopData[0] : shopData;

        // Find the present by ID
        const presentItem = shop.presents.find(item =>
            item.present &&
            item.present.attributes &&
            item.present.attributes.id === presentId
        );

        if (!presentItem) {
            return 'Present not found';
        }

        // Get the category ID from the present
        const categoryId = presentItem.present.attributes.shop_present_category_id;

        // Handle the case where shopPresentCategory is an array
        if (Array.isArray(shop.shopPresentCategory)) {
            const category = shop.shopPresentCategory.find(cat =>
                cat.attributes && cat.attributes.id === categoryId
            );

            if (category && category.attributes) {
                const languageField = `name_${language.toLowerCase()}`;

                // Return the category name in the requested language or fall back to Danish
                return category.attributes[languageField] ||
                    category.attributes.name_dk ||
                    'Category name not available';
            }
        }
        // Handle the case where shopPresentCategory is a single object (older format)
        else if (shop.shopPresentCategory &&
            shop.shopPresentCategory.attributes &&
            shop.shopPresentCategory.attributes.id === categoryId) {

            const languageField = `name_${language.toLowerCase()}`;

            return shop.shopPresentCategory.attributes[languageField] ||
                shop.shopPresentCategory.attributes.name_dk ||
                false;
        }

        return false;
    } catch (error) {
        console.error('Error finding category name:', error);
        return 'Error processing category';
    }
}