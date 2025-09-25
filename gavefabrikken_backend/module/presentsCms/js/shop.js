AppPcms.shop = (function () {
    self = this;
    self.init = () => {
        this.eventHandler();


    }
    self.initRange
    self.eventHandler = async () => {

        $(".menu-shop").click( async function(){

            let data = await AppPcmsShop.loadData();
            AppPcmsShop.buildUI(data);
            $("#modalNewShopView").modal('show');
        });
    }

    self.loadData = () => {
      return new Promise(function(resolve, reject) {

         $.post(_ajaxPath+"presentation/getAllWithPresent",{userId:_userId}, function(res, status) {
              if(res.status == 0) {  AppPcmsError.reg(res.msg); return; }
              else { resolve(res) }
          }, "json");
      })
    }
    self.buildUI = (data) => {
        var pData = [];
        data.data.forEach( ele => {
          if(pData[ele.id] == undefined){
            pData[ele.id] = [];
          }
          pData[ele.id].push(ele);
        })


        for (var key in pData) {
            console.log(pData[key]);
        }

        var html = '<div class="accordion" id="shopAccordion">    ';
        for (var key in pData) {
        // sub html
        var innerHtml = "";
        var pName = "";
        var hasShop = "";
        var showCreateBtn = "block";
        var subData = pData[key];
        for (var keySub in subData) {
            innerHtml+= "<div class='shop-sub-container'><div style='background-image: url(https://system.gavefabrikken.dk/fjui4uig8s8893478/"+subData[keySub].pt_img+")' class='shop-sub-img'  ></div><div class='shop-sub-title'>"+subData[keySub].nav_name+"</div><hr><div><center><input style='transform: scale(1.5);' type='checkbox' id='"+subData[keySub].present_id+"' class='"+subData[keySub].id+"'  ></center></div></div>"
            pName = subData[keySub].name
            if(subData[keySub].has_shop != "0" && subData[keySub].has_shop != ""){
               hasShop = " (Du har oprettet en shop for denne præsentation)";
               showCreateBtn = "none";
            }
        }
        html+=  `
              <div class="card z-depth-0 bordered">
                <div class="card-header" id="item_${key}">
                  <h5 class="mb-0">
                   <b>${pName} </b>  ${hasShop}   <button type="button"   shop-name="${pName}"  onclick="AppPcmsShop.createShop('${key}',this)" style="float: right; display:${showCreateBtn}">Opret Shop</button>

                </button>
              </h5>
            </div>
            <div id="collapse${key}" class="collapse" aria-labelledby="item_${key}" data-parent="#shopAccordion">
            <div class="card-body">
                <div>${innerHtml}</div>
            </div>
            <br>


        </div>

        </div>`;

         }
        html+= "</div>";

        $(".modalNewShop").html(html);

    }
    self.createShop = async (id,ele) => {
  


        r= confirm("Ønsker du at oprette en shop med de valgte gaver")
        if(r==true){
            // setter at shoppen er oprettet som valgshop
            let result = await AppPcmsShop.doCreateShop(id);
            shopName = $(ele).attr("shop-name");
            // opretter valgshop
            let shopData = await AppPcmsShop.doCreateValgShop(shopName);
            let shopID = shopData.data.shop[0].id;

            // hent gave listen
            let presentList = await AppPcmsShop.getPresentList(id);
            // opretter gaver
            for (let i in presentList.data){
                let res =   await AppPcmsShop.addPresent(presentList.data[i].present_id,shopID);
            }





                alert("Der er nu oprettet en shop ud fra din præsentation. \n Du skal nu gå til den oprettede shop og sætte manglende indstillinger")
                $("#modalNewShopView").modal('hide');


        }
    }
    self.addPresent = (presentID,shopID) => {
        return new Promise(function(resolve, reject) {
            var postData = {};
            postData["present_id"] = presentID;
            postData["shop_id"] = shopID;

            $.post("https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=present/createUnikPresent_v3",postData, function(res, status) {
                if(res.status == 0) {  AppPcmsError.reg(res.msg); return; }
                else {
                    resolve(res) }
            }, "json");

        })
    }

    self.getPresentList = (id) => {
        return new Promise(function(resolve, reject) {
            $.post(_ajaxPath+"presentation/getPresentList",{id:id}, function(res, status) {
                if(res.status == 0) {  AppPcmsError.reg(res.msg); return; }
                else { resolve(res) }
            }, "json");
        })
    }


    self.doCreateShop = (id) => {
         return new Promise(function(resolve, reject) {
         $.post(_ajaxPath+"presentation/createShop",{userId:_userId,id:id}, function(res, status) {
              if(res.status == 0) {  AppPcmsError.reg(res.msg); return; }
              else { resolve(res) }
          }, "json");
      })
    }
    self.doCreateValgShop = (shopName) => {

        return new Promise(function(resolve, reject) {

        var shop1 = {};
        shop1["name"] = shopName;
        shop1["link"] = shopName;


        var company1 = {};
        company1["name"] =         shopName;
        company1["cvr"] =          "12345678"
        company1["so_no"] =        "0";

        company1["username"] =     "admin";
        company1["password"] =      makeid(8);
        company1["contact_name"] =   "mangler";
        company1["contact_phone"] =  "12345678";
        company1["contact_email"] =   "mangler";

        var descriptions1 = [];
        _desc1_inshop = "";

        let descriptionDK = `PHA+PHN0cm9uZz5WZWxrb21tZW4gdGlsICZyZHF1bztGSVJNQXh4eHgmcmRxdW87IGp1bGVnYXZlc2hvcCAyMDIxPC9zdHJvbmc+Jm5ic3A7PC9wPgo8cD5JIHNhbWFyYmVqZGUgbWVkIEdhdmVGYWJyaWtrZW4gaGFyIHZpIHVkdmFsZ3Qgbm9nbGUgZmxvdHRlIGdhdmVyLCA8YnIgLz4gc29tIGR1IGZyaXQga2FuIHYmYWVsaWc7bGdlIGltZWxsZW0uJm5ic3A7PC9wPgo8cD5EdSBsb2dnZXIgaW5kIHZlZCBhdCB0YXN0ZSBkaW4gbWFpbCBpIGJlZ2dlIGZlbHRlciBoZXJ1bmRlci4mbmJzcDs8YnIgLz5EdSBsb2dnZXIgaW5kIHZlZCBhdCB0YXN0ZSBkaW4gbWFpbCBpJm5ic3A7YnJ1Z2VybmF2biBvZyBsJm9zbGFzaDtubnVtbWVyIGkgYWRnYW5nc2tvZGUuJm5ic3A7PC9wPgo8cD5IZXJlZnRlciBrYW4gZHUgdiZhZWxpZztsZ2UgaW1lbGxlbSBkZSB2aXN0ZSBqdWxlZ2F2ZXIgZnJlbSB0aWwgZGVuIFhYLlhYLjIwMjEuJm5ic3A7PC9wPgo8cD5GcmVtIHRpbCBkZWFkbGluZSBoYXIgZHUgbXVsaWdoZWQgZm9yIGF0IGZvcnRyeWRlLCBvZyB2JmFlbGlnO2xnZSBlbiBhbmRlbiBnYXZlLCA8YnIgLz4gYmxvdCB2ZWQgYXQgbG9nZ2UgaW5kIGlnZW4gb2cgZm9yZXRhZ2UgZXQgbnl0IGdhdmV2YWxnLiZuYnNwOzwvcD4KPHA+SGFyIGR1IGlra2UgdmFsZ3QgZW4gZ2F2ZSwgdmlsIGR1IGF1dG9tYXRpc2sgbW9kdGFnZSBYWFhYWFhYLiZuYnNwOzwvcD4KPHA+PHN0cm9uZz5SaWd0aWcgZ29kIGZvcm4mb3NsYXNoO2plbHNlIG9nIGdsJmFlbGlnO2RlbGlnIGp1bDwvc3Ryb25nPjwvcD4=`;

        let descriptionEN = `PHA+PHN0cm9uZz5XZWxjb21lIHRvICJGSVJNQXh4eHgiIENocmlzdG1hcyBnaWZ0IHNob3AgMjAyMTxiciAvPjxiciAvPjwvc3Ryb25nPldlIGhhdmUsIGluIGNvbGxhYm9yYXRpb24gd2l0aCBHYXZlRmFicmlra2VuLCBzZWxlY3RlZCBncmVhdCBnaWZ0cywgd2hpY2ggeW91IGNhbiBmcmVlbHkgY2hvb3NlIGZyb20uPGJyIC8+PGJyIC8+TG9nIGluIGJ5IGVudGVyaW5nIHlvdXIgbWFpbCBpbiBib3RoIGZpZWxkcyBiZWxvdy48YnIgLz5Mb2cgaW4gYnkgZW50ZXJpbmcgeW91ciBtYWlsIGluIHVzZXJuYW1lIGFuZCBwYXNzd29yZCBpbiBwYXNzd29yZC48YnIgLz48YnIgLz5Zb3UgY2FuIGNob29zZSBmcm9tIHRoZSBDaHJpc3RtYXMgcHJlc2VudHMgc2hvd24gdW50aWwgT2N0b2Jlci9Ob3ZlbWJlci9EZWNlbWJlciB4eHRoIDIwMjEuPGJyIC8+PGJyIC8+VW50aWwgZGVhZGxpbmUsIHlvdSBhcmUgZnJlZSB0byBtYWtlIGNoYW5nZXMgdG8geW91ciBzZWxlY3Rpb24sIGp1c3QgbG9nIGluIGFnYWluIHRvIG1ha2UgeW91ciBuZXcgY2hvaWNlLjxiciAvPjxiciAvPklmIHlvdSBkb24mcnNxdW87dCBzZWxlY3QgYSBnaWZ0LCB5b3Ugd2lsbCBhdXRvbWF0aWNhbGx5IHJlY2VpdmUgWFhYWFhYWC48YnIgLz5JZiB5b3UgZG9uJnJzcXVvO3Qgc2VsZWN0IGEgZ2lmdCwgYSBkb25hdGlvbiB3aWxsIGF1dG9tYXRpY2FsbHkgYmUgZ2l2ZW4uPGJyIC8+PGJyIC8+PHN0cm9uZz5NZXJyeSBDaHJpc3RtYXM8L3N0cm9uZz48L3A+`;

        descriptions1.push({'id':_desc1_inshop,'language_id':1,'description': descriptionDK});
        descriptions1.push({'id':_desc1_inshop,'language_id':2,'description': descriptionEN});
        descriptions1.push({'id':_desc1_inshop,'language_id':3,'description': "###"});
        descriptions1.push({'id':_desc1_inshop,'language_id':4,'description': "###"});
        descriptions1.push({'id':_desc1_inshop,'language_id':5,'description': "###"});


        var formData = {
            'shop':JSON.stringify(shop1),
            'company':JSON.stringify(company1),
            'descriptions':JSON.stringify(descriptions1)
        };
        shop1 = "";
        company1 = "";
        descriptions1 ="";



            $.post("https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=shop/createCompanyShop",formData, function(res, status) {
                if(res.status == 0) {  AppPcmsError.reg(res.msg); return; }
                else { resolve(res) }
            }, "json");
        })
    }






})

function makeid(length) {
    var result           = '';
    var characters       = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    var charactersLength = characters.length;
    for ( var i = 0; i < length; i++ ) {
        result += characters.charAt(Math.floor(Math.random() *
            charactersLength));
    }
    return result;
}