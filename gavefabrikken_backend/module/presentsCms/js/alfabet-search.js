AppPcms.alfabetSearch = (function () {
    self = this;
    self.data;
    self.alfabet;
    self.alfabetData = [];
    self.simplePresentList = [];
    self.itemNrData = [];


    self.init = async () => {
        this.data = await this.loadData();
        //await this.loadItemNr(); Anden l�sning er lavet
        this.buildInterface();
        this.buildBudget();
        this.buildSimplePresentList();

        this.eventHandler();

    };
    self.loadItemNr = () => {
        return new Promise(function(resolve, reject) {
            $.post(_ajaxPath+"present/getAllItemnr", function(res, status) {
                if(res.status == 0) {  AppPcmsError.reg(res.msg); return; }
                else { resolve(res) }
            }, "json");
        })
    };
    self.freeTextInit =  (res) => {
      var data = res.data;
      $(".simple").hide();
      $(".budget").show()
      $(".budget").prop("checked", false);
      $(".search").show()
      data.map(function(item){
            $("#"+item.id).show();
      })
      $(".simple").each(function(){
        let ele = $(this).attr("id");
        /*
        if($(this).is(":visible")){
            let budget = $(this).attr("budget-id");
            $(".budget:checkbox[value="+budget+"]").prop("checked", true);
        }
        */
      })


    };
    self.loadData = () => {
         return new Promise(function(resolve, reject) {
            $.post(_ajaxPath+"present/getAll",{lang:_lang}, function(res, status) {
                if(res.status == 0) {  AppPcmsError.reg(res.msg); return; }
                else { resolve(res) }
            }, "json");
         })
    };

    self.eventHandler = () => {
      var me = self;
        $(".letter-show-all").click(function(){
            $(".simple").show();
        })
        $(".letter-show-all-b").click(function(){
            $(".a").hide();
        })

         $(".simple-edit").click(function(){
           let id = $(this).attr("data-id");
           $("#modalPresentView").modal('show');
           AppPresentAdmin.edit(id);
        });
         $(".simple-delete").click(function(){
           if(!confirm("Vil du slette gaven ?")) return;
           let id = $(this).attr("data-id");
           AppPresentAdmin.doDelete(id)
        });




        $(".show-detail-present-event").click( async function(self) {
             let data = await AlfabetSearch.loadSinglePresent( $(this).attr("data-id") )

             let p = new AppPcms.present();
             p.showList(data);
             $(".search").removeClass("selected-search");
             $(this).addClass("selected-search");
        });

        $(".letter-search").click( async function(self) {
             $(".simple").hide();
             let vendor = $(this).attr("data-id");
             vendor = vendor.toLowerCase();
             $(".simple").each(function(){
                var str = $(this).attr("vendor");

                 if( str.search(vendor) != -1 ){
                    $(this).show();
                    //listOfChecked.push($(this).val())
                 }

          })

            /*
             let data = await AlfabetSearch.loadSinglePresent( $(this).attr("data-id") )
             let p = new AppPcms.present();
             p.showList(data);
             $(".search").removeClass("selected-search");
             $(this).addClass("selected-search");
             */
        });

        $(".letter-search-title").click( async function() {
          /*
             let data = await AlfabetSearch.loadLetterGroup( $(this).attr("data-id") )
             let p = new AppPcms.present();
             p.showList(data);
             $(".search").removeClass("selected-search");
             $(this).addClass("selected-search");
             */
        });

        $(".budget").click(function(){
          $(".kostpris").val("");
          $(".fulltxtsearch").val("")
          var isFullTextSearch = ($(".fulltxtsearch").val()) == "" ? false : true;
          let listOfChecked = [];
          if(isFullTextSearch == true){
             $(".simple").hide();
             $(".budget").each(function(){
                 if($(this).is(':checked') && $(this).is(":visible") ){
                    listOfChecked.push($(this).val())
                 }
              })
             /*
             if(listOfChecked.length == 0){
                  $(".letter-search").each(function(){
                    let id = $(this).attr("data-id");
                    $("#"+id).show();
                  })
             } else {
             $(".letter-search").each(function(){
                    let id = $(this).attr("data-id");
                    for(let i=0;i<listOfChecked.length;i++){
                       if($("#"+id).hasClass("gift_"+listOfChecked[i]) == true){
                          $("#"+id).show();
                       }
                    }
              });
             }
             */
          } else {

          $(".search").removeClass("selected-search");

          $(".budget").each(function(){

             if($(this).is(':checked')){
                $(".budget-"+$(this).val()).show();
                $(".budget-card-"+$(this).val()).show();
                listOfChecked.push($(this).val())
              } else {
                $(".budget-"+$(this).val()).hide();
                $(".budget-card-"+$(this).val()).hide();
              }
          })
          if(listOfChecked.length == 0){
                  $(".search").show();
                  $(".card").show();
                  $(".simple").show();
          } else {
              $(".simple").hide();
              for(let i=0;i<listOfChecked.length;i++){
                $(".gift_"+listOfChecked[i]).show();
              }
          }
          $(".letter-search-title").each(function(){
              /*
               let letter = $(this).attr("data-id")
                  let allHide = true;
                  $('[letter-id="'+letter+'"]').each(function(){
                        if( $(this).is(":visible") ){
                             allHide = false;
                        }
                  })
                  if(allHide == true){
                    $(this).hide();
                  } else {
                    $(this).show();
                  }
                */
              })
          }
        })
        $(".simple-pdf").click( async function(){
            message("Opretter pdf af den valgte slide, vent venligt")

              $(this).hide();
              $(this).parent().find("span").html("...arbejder");
              let id = $(this).attr("data-id");
              let pdf = new AppPcms.pdf;
              let presentation_id = Math.random().toString(36).substring(7)+Math.random().toString(36).substring(7)+Math.random().toString(36).substring(7);
              await pdf.make(id,presentation_id);
              SaveToDisk(presentation_id);
              $(this).parent().find("span").html("");
              $(this).show();



        })
        $(".simple-add").click(function(){
             var localMsg = $(this).parent().find("span");
             localMsg.html("...tilføjer til listen");
             let part = {id:$(this).attr("data-id"),title:$(this).attr("data-img")}
             message("Slide tilføjet")
             let img = "https://system.gavefabrikken.dk/fjui4uig8s8893478//"+$(this).attr("data-img");

             let html =   '<li data-id='+$(this).attr("data-id")+' class="presentation-elememt-set" class="ui-state-default">'+
             '<img   src='+img+'><i data-id='+$(this).attr("data-id")+' class="fas fa-trash-alt presentation-elememt-set-trash"></i>'+
             '<i data-id='+$(this).attr("data-id")+' class="fas fa-edit presentation-elememt-set-edit"></i> '+
             '<i data-id='+$(this).attr("data-id")+' class="fas fa-trash fa-2x simple-delete"></i> '+
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




             setTimeout(function(){
              localMsg.html("");
             }, 1000)
        })
        $(".itemNumber").click( function(){
            event.stopPropagation();

            AlfabetSearch.showShopItemNumber($(this).attr("data-id"));
        })

    };
    self.prepareData = () => {
        var l = this.hasFirstLetter;
        this.alfabet = this.buildAlfabetArray();
        var alfabetData = [];
        this.alfabet.map(function(letter){
            alfabetData[letter] = l(letter);
        })
        this.alfabetData = alfabetData;
    };
    self.buildSimplePresentList = () => {
        let presentationData = this.data.data;
        var display = "";
        var html = "";
        var visibleCont = 0;
        presentationData.map(function(item){
           display = "style='display: none'";
           visibleCont++;
           try {
           let pt_price;
           let canEdit = "";
           let canDelete = "";
           let frontnotShow = ".frontnotShow";
           if(_lang == 1){ pt_price =  JSON.parse(item.pt_price); }
           if(_lang == 4){ pt_price =  JSON.parse(item.pt_price_no); }
           let vendor = item.vendor;
           vendor = vendor.replace(/\s/g,'')
           vendor = vendor.toLowerCase();

           if( item.state == "c" ){
               canEdit = "<i class=\"far fa-2x fa-edit simple-edit\" data-id='"+item.id+"'></i>";
               canDelete = "<i class=\"fas fa-trash fa-2x simple-delete \" data-id='"+item.id+"'> </i> ";
           }
           /*
           if( item.state == "b" ||  item.state == "a"){

           }
*/


           item.pt_img = item.pt_img == "" ?  "noimg.jpeg" : item.pt_img;
           if(visibleCont < 100){
               display = "";
           }


           AlfabetSearch.simplePresentList.push(

           "<div class='simple gift_"+pt_price.pris+" "+item.state+"' vendor='"+vendor+"'  id='"+item.id+"' budget-id='"+item.caption+"' "+display+" >"+
           "<div class='simpleImg show-detail-present-event' data-id='"+item.id+"' style='background-image: url(https://system.gavefabrikken.dk/fjui4uig8s8893478/"+item.pt_img+")'><i data-id='"+item.id+"' class='fas fa-list-ol itemNumber' style='float:right;' ></i><div class='pt_price'>"+pt_price.pris+"</div></div> "+
            "<div  class='simpleTitle'>"+item.caption+"</div> "+
            "<div  class='simpleAction'> <span></span><i class='far fa-2x simple-pdf fa-file-pdf' data-id='"+item.id+"'></i> "+
            "<i class='fas fa-2x simple-add fa-plus-square' data-id='"+item.id+"' data-img='"+item.pt_img+"'></i> "+
            "<i  class=' fab fa-2x show-detail-present-event fa-sistrix' data-id='"+item.id+"'></i> "+ canEdit + " "+ canDelete +
            "</div></div>");
           } catch(error) {
                    console.log("error in present "+item.id)
           }
        })
        $(".pcms-main").html("<br>"+ AlfabetSearch.simplePresentList.join(" ") );

    };
    self.showShopItemNumber = (id) => {
        $("#itemNumberModal").modal('show');
        $(".itemNumberModal-body").html("<br><br>");

           $.post(_ajaxPath+"present/getItemnr",{itemnr:id}, function(res, status) {
                if(res.status == 0) {  AppPcmsError.reg(res.msg); return; }
                else {
                    res.data.map(function(item){
                        let html = `<center><table width=80% >
                        <tr><td>Varenummer</td><td><b>${item.model_present_no}<b></td></tr>
                        <tr><td colspan=2><img width=80% src="${item.media_path}" alt="" /></td></tr>
                        <tr><td>Gave navn</td><td>${item.model_name}</td></tr>
                        <tr><td>Gave model</td><td>${item.model_no}</td></tr>
                        </table></center><hr>
                        `;
                        $(".itemNumberModal-body").append(html);


                    });
                }
            }, "json");


/*
       this.alfabet.map(function(letter){
            let l = data[letter].length;
            if(l >0){

                for(let i = 0;l>i;i++){
                  try {
                  var  pt_price;

                  if(_lang == 1){  pt_price =  JSON.parse(data[letter][i].pt_price); }
                  if(_lang == 4){  pt_price =  JSON.parse(data[letter][i].pt_price_no); }
                      budget.push(pt_price.pris);
                  } catch(error) {

                  }
                }
            }
        })
  */

    //alert(id)
    }



    self.buildInterface = () => {
        var html = [];
        var brand = [];
        this.prepareData();
        $(".simple").hide();

        $(".letter-search-container").append("<div class='search letter-search-title letter-show-all'>VIS ALLE</div><div class='letter-show-all-b'>VIS ALLE QUICK GAVER</div><hr>");


        var data = this.alfabetData;
        this.alfabet.map(function(letter){
            $(".letter-search-container").append("<div class='search letter-search-title alfabet-"+letter+"' data-id='"+letter+"' style='display:none;' >"+letter.toUpperCase()+"</div><div  id='alfabet-"+letter+"'></div>");
            let l = data[letter].length;
            if(l >0){
              for(let i = 0;l>i;i++){
                let vendor = data[letter][i].vendor
                vendor = vendor.toLowerCase();

                brand.push(vendor.trim());

              }
            };
        })
        brand = brand.filter((v, i, a) => a.indexOf(v) === i);
        brand = brand.sort(function(a, b) {
                    return a - b;
        });

        let unik = unique(brand)
        unik.map(function(item){
            let firstLetter = item.charAt(0).toLowerCase();
            let vendor = item.replace(/\s/g,'');
            $("#alfabet-"+firstLetter).append("<div class='search letter-search'  data-id='"+vendor+"' >"+item+"</div>");
            $(".alfabet-"+firstLetter).show();
        })

    };
    self.buildBudget = () => {
        var data = this.alfabetData;
        var budget = [];
        this.alfabet.map(function(letter){
            let l = data[letter].length;
            if(l >0){

                for(let i = 0;l>i;i++){
                  try {
                  var  pt_price;

                  if(_lang == 1){  pt_price =  JSON.parse(data[letter][i].pt_price); }
                  if(_lang == 4){  pt_price =  JSON.parse(data[letter][i].pt_price_no); }
                      budget.push(pt_price.pris);
                  } catch(error) {

                  }
                }
            }
        })
        budget = budget.filter((v, i, a) => a.indexOf(v) === i);
        budget = budget.sort(function(a, b) {
                    return a - b;
        });
        let budgetHtml = [];
        budget.map(function(item){
            if(Number.isInteger((item*1))){
                budgetHtml.push(' <input type="checkbox" class="budget" value="'+item+'"> <label for="gift_'+item+'"> '+item+'</label><br>')
            }
        })
        $(".budget-filter").html(budgetHtml.join("")+"<hr>");

    }


    self.buildAlfabetArray = () => {
        let alfabetArr = [];
        let alfabet = "abcdefghijklmnopqrstuvwxyz";
        for (var i = 0; i < alfabet.length; i++) {
            alfabetArr.push(alfabet.charAt(i));
        }
        return alfabetArr;
    };
    self.hasFirstLetter = (letter) => {
          let list = [];
          this.data.data.map(function(ele){
             if(letter == ele.vendor.charAt(0).toLowerCase()){
                 list.push(ele)
             }
          })
          return list;
    };
    self.loadLetterGroup = (letter) => {
        return new Promise(function(resolve, reject) {
            $.post(_ajaxPath+"present/getByLetterGroup",{letter:letter,lang:_lang}, function(res, status) {
                if(res.status == 0) {  AppPcmsError.reg(res.msg); return; }
                else { resolve(res) }
            }, "json");
         })
    }
    self.loadSinglePresent = (id) => {
        return new Promise(function(resolve, reject) {
            $.post(_ajaxPath+"present/getById",{id:id,lang:_lang}, function(res, status) {
                if(res.status == 0) {  AppPcmsError.reg(res.msg); return; }
                else { resolve(res) }
            }, "json");
         })
    }
})
// helper functions

function capitalizeFirstLetter(string) {
  return string.charAt(0).toUpperCase() + string.slice(1);
}
function unique(array){
    return array.filter(function(el,index,arr){
        return index == arr.indexOf(el);
    });
}

