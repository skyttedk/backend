var rangeTimer;
AppPcms.search = (function () {
    self = this;


    self.initRange = async () => {

          let navPrices =  await this.getNavPriceMaxMin();
          let navPricesMin = Math.min.apply(Math, navPrices)-10;
          let navPricesMax = Math.max.apply(Math, navPrices)+10;

          $( "#slider-range" ).slider({
              range: true,
              min: navPricesMin,
              max: navPricesMax,
              values: [ navPricesMin, navPricesMax ],
              slide: function( event, ui ) {
                $("#start-kost-pris").html(ui.values[ 0 ])
                $("#slut-kost-pris").html(ui.values[ 1 ])
                clearTimeout(rangeTimer);
                rangeTimer = setTimeout(function(){
                     AppPcmsSearch.doSearch()
                 }, 300)


              }
            });
            $("#start-kost-pris").html($( "#slider-range" ).slider( "values", 0 ) )
            $("#slut-kost-pris").html($( "#slider-range" ).slider( "values", 1 ) )
        }

    self.getNavPriceMaxMin = async () => {
        return new Promise(function(resolve, reject) {
        let navPrice = [];
        $.post(_ajaxPath+"present/getNavPrice",{lang:_lang}, function(res, status) {
            if(res.status == 0) {  AppPcmsError.reg(res.msg); return; }
            else {
                 $.each(res.data, function(index, value){
                   if(typeof value.nav_prise == "string"){
                     value.nav_prise = value.nav_prise.replace(",", ".");
                   }

                   if(Number.isInteger((value.nav_prise*1))){
                        navPrice.push(value.nav_prise)
                   }
                 });
                 resolve(navPrice) ;
            }
        }, "json");
        })
    }


    self.doSearch = async () => {
        $(".fulltxtsearch").val("");
            let start = $("#start-kost-pris").html();
            let end  = $("#slut-kost-pris").html();
            if(start != "" && end != ""){
                let range = {start:start,end:end,lang:_lang}
                let res = await AppPcmsSearch.getRange(range);
                if(res.data.length == 0){
                    message("Intet søgeresultat!")
                    AlfabetSearch.freeTextInit(res)
                } else {
                    AlfabetSearch.freeTextInit(res)
                }
            }
     }

    self.alfabet = (letter) => {
        $(".search").each(function(){
                if( $(this).attr("data-id") == letter || $(this).attr("letter-id") == letter ){
                    $(this).show();
                } else {
                    $(this).hide();
                }
        })

    };
    self.getFreeText = (text) => {
         return new Promise(function(resolve, reject) {
            $.post(_ajaxPath+"present/freeTextSearch",{text:text,lang:_lang}, function(res, status) {
                if(res.status == 0) {  AppPcmsError.reg(res.msg); return; }
                else { resolve(res) }
            }, "json");
         })
    }

    self.getRange = (range) => {
         var postData = range;
         return new Promise(function(resolve, reject) {
            $.post(_ajaxPath+"present/getRange",postData, function(res, status) {
                if(res.status == 0) {  AppPcmsError.reg(res.msg); return; }
                else { resolve(res) }
            }, "json");
         })
    }


})

