//*************************************************************
//system user
//*************************************************************
function onDocumentLoaded() {
     $('#autoupdate').change(function() {
        checkMailBox();
    });
    ajax(null, "SystemLog/isProduction", "onQueryProduction", "");
    isChecked();

}

function isChecked() {
    return $('#autoupdate').prop('checked');
}


function checkMailBox() {
    var data = {};
    data['token'] = '59d98ef3-eba0-4d04-a173-236f5ad8b9fa';
    ajax(data,"mail/getMailStats","onQueryMail","");
}

function onQueryMail(res) {
    $("#mails_error").html(res.data.error);
    $("#mails_sent").html(res.data.sent);
    $("#mails_queue").html(res.data.queue);

    //temp udkommenteret
    //$("#last_run").html(res.data.last_run.date.toString().slice(0,19));


  // 17-04-2017
  //  if(isChecked())
  //    setTimeout(checkMailBox, 1000);
}

function onQueryProduction(response) {

  if(response.data.data[0].is_production=="1")
  {
     $("#prod").html('<h4 style="color:#FD3737">This is a production system<h4>');
     blink();
  }    else {
     $("#prod").html('<h4 style="color:#BCF517">This is a development system<h4>');
  }
 checkMailBox();
}
var blinkState =0;
function blink(){
   if(blinkState==0) {
     $("#prod").hide();
     blinkState =1;
   }   else {
     $("#prod").show();

   blinkState = 0;
   }

   setTimeout(blink, 1000);
}


var external = {
     newGiftCertificateOrder : function() {
     var data = {};
     var companydata = {};
     companydata['name'] ="KSS NY4";
     companydata['phone'] ="26190720";
     companydata['website'] ="https://www.website.com";
     companydata['cvr'] ="22334455";
     companydata['link'] ="somelink5";        //&nbsp;hovhovhvo limk skal logge på shoppen ikke company
     companydata['ship_to_attention'] ="Kim Skytte";
     companydata['ship_to_address'] ="Søgårdsvej 75";
     companydata['ship_to_address_2'] ="";
     companydata['ship_to_postal_code'] ="5270";
     companydata['ship_to_city'] ="Odense NV";
     companydata['ship_to_country'] ="Danmark";
     companydata['contact_name'] ="Kim Skytte";
     companydata['contact_phone'] ="12233445";
     companydata['contact_email'] ="sigurd.skytte@gmail.com";
     data['shop_id'] = 54;
     data['quantity'] = "10";
     data['value'] = "560";
     data['expire_date'] = "2016-11-06";
     data['companydata'] = companydata;
     showRequest(data);
     ajax(data,"external/newGiftCertificateOrder","onResult","");
    }
}

var giftcertificate = {
    findBatch : function() {
        var data = {};
        data['quantity'] = 100;
        data['value'] = 560;
        data['expire_date'] = "2016-11-06";
        data['shop_id'] = "46";
        showRequest(data);
     	ajax(data,"giftcertificate/findBatch","onResult","");
    },
    addToShop : function() {
        var data = {};
        data['certificate_id'] = 818516;
        data['shop_id'] = 52;
        data['company_id'] = 74;
        showRequest(data);
     	ajax(data,"giftcertificate/addToShop","onResult","");
    }, removeFromShop : function() {
  		var certificate_id = prompt("Gavekort id der skal fjernes", "");
        if (certificate_id) {
          var data = {};
          data['certificate_id'] = certificate_id;
          showRequest(data);
      	  ajax(data,"giftcertificate/removeFromShop","onResult","");

        }
    },
    createBatch : function() {
        var quantity = prompt("Antal Gavekort", "");
        if(quantity) {
            var value = prompt("Vaerdi (400,560,640)", "");
            if(value) {
              var delivery = prompt("Levering Uge (48,50,4)", "");
                if(delivery) {
                    if(delivery=="48") {
                      defaultDate = "2016-11-06";
                    } else if(delivery=="50") {
                      defaultDate = "2016-11-20";
                    } else if(delivery=="4") {
                      defaultDate = "2016-12-31";
                    }


                    var expire_date = prompt("Slut date(YYYY-mm-dd)", defaultDate);
                    if(expire_date) {
                        var noseries = prompt("Nummerserie\n"+"2	Julegavekortet DK\n3	Guldgavekortet DK\n4	Julegavekortet NO 400\n5	Julegavekortet NO 560\n6	Julegavekortet NO 640\n7	24Gaver DK 400\n8	24Gaver DK 560\n9	24Gaver DK 640\n", "");
                        if(noseries) {
                            var data = {};

                            data['is_printed'] = 1;
                            data['is_emailed'] = 0;

                            data['no_series'] = noseries;
                            data['quantity'] = quantity;
                            data['value'] = value;
                            data['delivery'] = delivery;
                            data['expire_date'] = expire_date;
                            ok = confirm("Opret gavekort?\nAntal : "+quantity + "\nVaerdi : " + value + "\n uge : "+delivery+ "\n Udlobsdato:  "+expire_date+"\nNr. serie: " +noseries);
                            if(ok) {
                                showRequest(data);
                                ajax(data,"giftcertificate/createBatch","onResult","");

                            }
                        }
                    }
                }
            }
        }
    }
}


// System User
var systemUser = {
    readAll : function() {
             ajax(null,"present/readAll","systemLog.onLogReplayed","");
    }  ,
    show : function(){
         ajax({},"systemUser/Index","","#content");
    },
    createNew : function(){
        var data = {};
        data['name']          =  $('tr[id^="0"] #systemUserName').val();
        data['username']      =  $('tr[id^="0"] #systemUserUsername').val();
        data['password']      =  $('tr[id^="0"] #systemUserPassword').val();
        data['userlevel']     =  $('tr[id^="0"] #systemUserUserlevel').val();
        data['active']        =  $('tr[id^="0"] #systemUserActive').val();
        ajax(data,"systemUser/create","systemUser.show","");
    },
    delete : function (id) {
        var data = {};
        data['id'] =id;
        ajax(data,"systemUser/delete","systemUser.show","");
    },
    save : function(id){
        var data = {};
        data['id'] =id;
        data['name']          =  $('tr[id^="'+id+'"] #systemUserName').val();
        data['username']      =  $('tr[id^="'+id+'"] #systemUserUsername').val();
        data['password']      =  $('tr[id^="'+id+'"] #systemUserPassword').val();
        data['userlevel']     =  $('tr[id^="'+id+'"] #systemUserUserlevel').val();
        data['active']        =  $('tr[id^="'+id+'"] #systemUserActive').val();
        ajax(data,"systemUser/update","systemUser.show","");
    }
 }


//*************************************************************
//Log getLoginActivity
//*************************************************************
var systemLog = {

    getLoginActivity : function () {
       showRequest(null);
       ajax(data,"SystemLog/getLoginActivity","onResult","");
    } ,
    replay : function(){
         var id = $('#replay').val();
         if(id != "" ) {
             var data = {};
             data['id'] =id;
             showRequest(data);
             ajax(data,"SystemLog/read","systemLog.onLogLoaded","");
         }  else {
             alert('angive log id');
         }
    } ,
    onLogLoaded(result) {
        var action = result.data.systemlog[0].action;
        var controller = result.data.systemlog[0].controller;
        var jsonData =JSON.parse(result.data.systemlog[0].data);

        $("#request").empty();
        $("#request").html('<pre>'+JSON.stringify(jsonData,null,2)+'</pre>')
        ajax(jsonData,controller+"/"+action,"onResult","");
    },
    deleteAll : function(id){
        if (confirm("Vil du slette hele log?"))
            {
                var data = {};
                showRequest(data);
                ajax(data,"SystemLog/deleteAll","onResult","");
            }
        },

    deleteErrors : function(id){
        if (confirm("Vil du slette fejlogs?"))
            {
                var data = {};
                showRequest(data);
                ajax(data,"SystemLog/deleteErrors","onResult","");
            }
        },
    readAll : function() {
         var data = {};
         showRequest(data);
         ajax(data,"SystemLog/readLast10","onResult","");
    },
    readError : function() {
         var data = {};
         showRequest(data);
         ajax(data,"SystemLog/readErrors","onResult","");
    },
    enableFullTrace : function(){
     var data = {};
     showRequest(data);
     ajax(data,"SystemLog/enableFullTrace","onResult","");
   },
   disableFullTrace : function(){
     var data = {};
     showRequest(data);
     ajax(data,"SystemLog/disableFullTrace","onResult","");
   } ,
   removeOrderData  : function(){
        var id = prompt("Shop User id for hvilken der skal slettes ordre", "");
         if(id) {
             var data = {};
             data['id'] =id;
             showRequest(data);
             ajax(data,"SystemLog/removeOrderData","onResult","");
         }

   }
}

//*************************************************************
//TEst
//*************************************************************

var test = {
    test : function() {
         var data = {};
         showRequest(data);
         ajax(data,"SystemLog/test","onResult","");
//                  ajax(data,"company/testOrderMail","onResult","");
    } , generictest : function () {
      var data = JSON.parse($('#data').val());
      showRequest(data);
      var url = $('#controller').val()+"/"+$('#action').val();
      ajax(data,url,"onResult","");
    }

};
//*************************************************************
//Report
//*************************************************************

var report = {
    test : function() {
         var data = {};
         showRequest(data);
         ajax(data,"report/genericReport","onResult","");
    }

};

//*************************************************************
//Company
//*************************************************************
var company = {
    getUsers : function () {
         var id = prompt("company_id", "");
         if(id) {
            data['company_id']  =id;
            showRequest(data);
            ajax(data,"company/getUsers","onResult","");
        }
    },
    addGiftCertificates : function () {
        alert('security switch');
        alsdkmfaslk
       data['company_id']  = 191;
       data['quantity']    = 65;
       data['weekno']      = 48;
       data['shop_id']     = 52;
       showRequest(data);
       ajax(data,"company/addGiftCertificates","onResult","");

    }     ,
        searchGiftCertificateCompany: function () {
         var value = prompt("Søgetekst", "");
         if(value) {
            data['text'] = value;
            showRequest(data);
            ajax(data,"company/searchGiftCertificateCompanyCSV","onResult","");
        }

    }  ,
    readGiftCertificateCompany: function () {
         var id = prompt("company_id", "");
         if(id) {
            data['company_id'] = id;
            showRequest(data);
            ajax(data,"company/readGiftCertificateCompany","onResult","");
        }

    },
    createGiftCertificateCompany : function() {
         var companydata = {};
         companydata['name'] ="TestShop";
         companydata['phone'] ="26190720";
         companydata['website'] ="https://www.website.com";
         companydata['cvr'] ="22334455";
         companydata['ship_to_attention'] ="Kim Skytte";
         companydata['ship_to_address'] ="Søgårdsvej 75";
         companydata['ship_to_address_2'] ="";
         companydata['ship_to_postal_code'] ="5270";
         companydata['ship_to_city'] ="Odense NV";
         companydata['ship_to_country'] ="Danmark";
         companydata['contact_name'] ="Kim Skytte";
         companydata['contact_phone'] ="12233445";
         companydata['contact_email'] ="sigurd.skytte@gmail.com";
         data['shop_id'] = 54;
         data['quantity'] = "10";
         data['value'] = "560";
         data['expire_date'] = "2016-11-06";
         data['companydata'] = companydata;
         showRequest(data);
         ajax(data,"company/createGiftCertificateCompany","onResult","");
    },
    getCompanyReservations: function () {
         var id = prompt("company_id", "");
         if(id) {
            data['company_id'] = id;
            showRequest(data);
            ajax(data,"reservation/getCompanyReservations","onResult","");
        }
    }    ,
    getCompanyOrders: function () {
        ajax(data,"company/getCompanyOrders","onResult","");
    },
    setCompanyOrderPrinted: function () {
        data['id'] = 5;
        data['is_printed'] = 1;
        ajax(data,"company/setCompanyOrderPrinted","onResult","");
    },
    setCompanyOrderShipped: function () {
        data['id'] = 5;
        data['is_shipped'] = 1;
        ajax(data,"company/setCompanyOrderShipped","onResult","");
    },
    getCompanyImports: function () {
        ajax(null,"company/getCompanyImports","onResult","");
    }

};

//*************************************************************
//Present
//*************************************************************
var present = {
        delete : function() {
          var id = prompt("Gave id der skal settes", "");
          if(id){
          var data = {};
          data['id'] = id;
          showRequest(data);
         ajax(data, "present/deleteReal", "onResult","");

          }
            } ,
    readTop10 : function() {
        var data = {};
        data['token'] = 'e5acec4e-9a24-41d7-ab8c-f95610b930f3';
        //data['token'] = '0dacc8f7-187e-4750-bf70-87d103e1123123';
        showRequest(data);
        ajax(data,"present/readTop10","onResult","");
    },
    read : function () {
      var id = prompt("Gave id", "");
      var data = {};
      data['id'] = id;
      showRequest(data);
      ajax(data, "present/read", "onResult","");
    },
    readAll : function() {
        var data = {};
        showRequest(data);
        ajax(null,"present/readAll","onResult","");
    },
    readVariants : function() {
      var id = prompt("Find varianter for gave id", "");
      var data = {};
      data['present_id'] = id;
      showRequest(data);
      ajax(data, "present/readVariants", "onResult","");


    }  ,
    searchPresents: function () {
      var id = prompt("Search for:", "");
      if(id) {
        var data = {};
        data['search'] = id;
        showRequest(data);
        ajax(data, "present/searchPresents", "onResult","");
      }
    },  searchVariants: function () {
      var id = prompt("Search for:", "");
      if(id) {
        var data = {};
        data['search'] = id;
        showRequest(data);
        ajax(data, "present/searchVariants", "onResult","");
      }
    },
    activate: function () {
      var id = prompt("Gave id der skal aktiveres", "");
      if(id) {
        var data = {};
        data['id'] = id;
        showRequest(data);
        ajax(data, "present/activate", "onResult","");
      }
    } ,
   deactivate: function () {
      var id = prompt("Gave id der skal deaktiveres", "");
      if(id) {
        var data = {};
        data['id'] = id;
        showRequest(data);
        ajax(data, "present/deactivate", "onResult","");
      }
    }
}

var order = {
    create : function(){
       var data = {};
       data['user_id'] = '731';
       data['present_id'] = '226';
       showRequest(data);
       ajax(data,"order/create","onResult","");
    }  ,
    changePresent : function () {
        var order_id = prompt("Ordre id", "");
        if(order_id) {
            var present_id = prompt("Ny gave id", "");
            if(present_id){
                var model = prompt("Model", "");
                if(model){
                    var data = {};
                    data['order_id'] = order_id;
                    data['present_id'] = present_id;
                    data['model'] = 'model';
                    showRequest(data);
                    ajax(data, "order/changePresent", "onResult","");
                }
            }

        }
    },
    getReceipt : function () {
      var order_id = prompt("Ordre id.", "");
      if(order_id) {
        data['order_id'] = order_id;
        ajax(data,"order/getReceipt","onResult","");
       }

    } , resendReceipt : function () {
      var order_id = prompt("Ordre id.", "");
      if(order_id) {
        data['order_id'] = order_id;
        ajax(data,"order/resendReceipt","onResult","");
       }

    } ,


}
//*************************************************************
//Login
//*************************************************************
var login = {
    loginSystemUser : function(){

       var data = {};
       data['username'] = 'skytte_dk';
       data['password'] = 'dit5740';
       showRequest(data);
       ajax(data,"login/loginSystemUser","onResult","");
    },
       loginShopUser : function(){
        var shopID = prompt("Shop id", "");
        if(shopID) {
            var username = prompt("Username", "");
            if(username) {
                var password = prompt("Password", "");
                if(password) {
                    var data = {};
                    data['logintype'] = 'shop';
                    data['shop_id']  = shopID;
                    data['username'] = username;
                    data['password'] = password;
                    showRequest(data);
                    ajax(data,"login/loginShopUser","onResult","");
                }
            }
        }
    },
   testBackendToken : function(){
       var token = prompt("Angiv token", "");
       var data = {};
       data['token'] = token;
       data['type'] = 'backend';
       showRequest(data);
       ajax(data,"login/testToken","onResult","");
    },
   testShopToken : function(){
       var token = prompt("Angiv token", "");
       var data = {};
       data['token'] = token;
       data['type'] = 'shop';
       showRequest(data);
       ajax(data,"login/testToken","onResult","");
    },
   testCustomerToken : function(){
       var token = prompt("Angiv token", "");
       var data = {};
       data['token'] = token;
       data['type'] = 'customer';
       showRequest(data);
       ajax(data,"login/testToken","onResult","");
    }
}
//*************************************************************
// Shop
//*************************************************************
var mail = {
     createMailQueue : function(){
       var data = {};
       data['token'] = '0dacc8f7-187e-4750-bf70-87d103e10191';
       data['sender_name'] ='Gavefabrikkos';
       data['sender_email'] ='kss@bitworks.dk';
       data['recipent_name'] ='Kim Skytte';
       data['recipent_email'] ='sigurd.skytte@gmail.com';
       data['subject'] ='Tilykke';
       data['body']  ='PGgxPndhbGxhaGE8L2gxPg==';

       showRequest(data);
       ajax(data,"mail/createMailQueue","onResult","");
    }, parseQueue : function(){
       var data = {};
      // data['token'] = '0dacc8f7-187e-4750-bf70-87d103e10191';
       showRequest(data);
       ajax(data,"mail/parseQueue","onResult","");
    }  , resendOrderMail : function() {
         var userid = prompt("user id", "");
         if(userid){
                      var data = {};
           data['user_id'] = userid;
           showRequest(data);
       ajax(data,"mail/resendOrderMail","onResult");

         }
    }
}
//*************************************************************
// Shop
//*************************************************************

var shop = {
    read : function(){
    var shopid = prompt("Shop id", "");
    if(shopid){
           var data = {};
           data['id'] = shopid;
           showRequest(data);
       ajax(data,"shop/read","onResult");
       }
    },
    readSimple : function(){
    var link = prompt("Shop id", "");
    if(link){
           var data = {};
           data['link'] = link;
           showRequest(data);
           ajax(data,"shop/readSimple","onResult");
       }
    },
    readCompanyShops : function(){
       showRequest({});
       ajax(null,"shop/readCompanyShops","onResult");
    },
    readGiftcertificateShops : function(){
       showRequest({});
       ajax(null,"shop/readGiftcertificateShops","onResult");
    },
    getShopCompanies : function () {
      var shopid = prompt("Shop id", "");
       if(shopid){
           var data = {};
           data['shop_id'] = shopid;
           showRequest(data);
           ajax(data,"shop/getShopCompanies","onResult");
       }

    } ,
    getShopPresents : function(){
       var shopid = prompt("Shop id", "");
       if(shopid){
           var data = {};
           data['shop_id'] = shopid;
           showRequest(data);
       ajax(data,"shop/getShopPresents","onResult");
       }
    },
    addPresent : function () {
        var shopid = prompt("Shop id", "");
        var presentid = prompt("Gave id", "");
       if(shopid && presentid){
       var data = {};
       data['shop_id'] = shopid;
       data['present_id'] = presentid;
       showRequest(data);
       ajax(data,"shop/addPresent","onResult");
       }

    } ,
     removePresent : function () {
    var shopid = prompt("Shop id", "");
        var presentid = prompt("Gave id", "");
       if(shopid && presentid){
       var data = {};
       data['shop_id'] = shopid;
       data['present_id'] = presentid;
       showRequest(data);
       ajax(data,"shop/removePresent","onResult");
       }
    } ,
 getShopUsers : function () {
    var shopid = prompt("Shop id", "");

       if(shopid){
       var data = {};
       data['id'] = shopid;

       showRequest(data);
       ajax(data,"shop/getUsers","onResult");
       }
    } ,  getUsersBatch : function () {
    var shopid = prompt("Shop id", "");

       if(shopid){
         var offset = prompt("Offset(1,2,3....)", "");
         if(offset) {
             var data = {};
             data['shop_id'] = shopid;
             data['offset'] = offset;
             showRequest(data);
             ajax(data,"shop/getUsersBatch","onResult");
         }
       }
    } , searchUsers : function () {
    var shopid = prompt("Shop id", "");
       if(shopid){
         var what = prompt("Søg efter", "");
         if(what) {
             var data = {};
             data['shop_id'] = shopid;
             data['what'] = what;
             showRequest(data);
             ajax(data,"shop/searchUsers","onResult");
         }
       }
    }  ,

     getUsersWithNoOrders : function () {
    var shopid = prompt("Shop id", "");

       if(shopid){
       var data = {};
       data['shop_id'] = shopid;

       showRequest(data);
       ajax(data,"shop/sendMailsToUsersWithNoOrders","onResult");
       }
    } ,

    createCompanyShop : function(){
        var data = {};
        data['name'] ='Matas';
        data['website'] ='http://www.matas.dk';
        data['cvr'] ='34233445';
        data['contact_name'] ='Jens Andersen';
        data['contact_phone'] ='261907210';
        data['contact_email'] ='info@matas.dk';
        data['username'] ='xxx';   // skal ikke have username på selve shoppennn.. men der oprettes en login.

        ajax(data,"shop/createCompanyShop","onResult","");
    },
    delete : function(){
        var id = prompt("Shop der skal slettes", "");
        if(id) {
        var data = {};
         data['id'] = id;
         showRequest(data);
           ajax(data,"shop/deleteShop","onResult");
       }

    } ,
    createShopUser : function()
    {
        var id = prompt("Shop der skal tjekkes", "");
        if(id) {
        var data = {};
         data['shop_id'] = id;
         data['company_id'] = 72;
         users = [];
         users.push(["skytte_dk","dit6000","kim","test1","test2"]);
         users.push(["bundy_dk","bundy6000","Ulrich","test3","test4"]);
         data['users'] = users;
         showRequest(data);
         ajax(data,"shop/addShopUser","onResult");
    }
    },
    updateShopUser : function()
    {
        var id = confirm("Data is hardcoded. Proceed?");
        if(id) {
        var data = {};
         users = [];
         users.push(["300","bundy_dk"]);
         users.push(["301","5742"]);
         data['attributes'] = users;
         showRequest(data);
         ajax(data,"shop/updateShopUser","onResult");
       }
    },

    removeShopUser : function() {
           var id = prompt("User Id der skal Fjernes", "");
           if(id) {
              var data = {};
              data['user_id'] = id;
              showRequest(data);
              ajax(data,"shop/removeShopUser","onResult");
            }
       },
    addAttribute : function() {
        var data = {};
        data["shop_id"] = 45;
        data["index"] = 1;
        data["name"] = "Username2";
        data["data_type"] =  1;
        data["is_username"] =1;
        data["is_password"] = 0;
        data["is_email"] = 0;
        data["is_name"] = 0;
        data["is_locked"] = 0;
        data["is_mandatory"] = 1;
        data["is_visible"] = 0;
        data["is_list"] =0;
        data["list_data"] ="";
        showRequest(data);
        ajax(data,"shop/addAttribute","onResult");
    },
    updateAttribute : function() {
        var data = {};
          var id = prompt("ShopAttribute der skal opdateres", "");
        if(id) {
        data["id"] = id;
        data["shop_id"] = 45;
        data["index"] = 1;
        data["name"] = "Username_xxx";
        data["data_type"] =  1;
        data["is_username"] =1;
        data["is_password"] = 0;
        data["is_email"] = 0;
        data["is_name"] = 0;
        data["is_locked"] = 0;
        data["is_mandatory"] = 1;
        data["is_visible"] = 0;
        data["is_list"] =0;
        data["list_data"] ="";
        showRequest(data);
        ajax(data,"shop/updateAttribute","onResult");
        }

    },
    removeAttribute : function() {
        var id = prompt("ShopAttribute der skal fjernes", "");
        if(id) {
            var data = {};
              data['id'] = id;
              showRequest(data);
              ajax(data,"shop/removeAttribute","onResult");
            }

    },
    getShopAttributes : function() {
        var id = prompt("Shop id", "");
        if(id) {
            var data = {};
              data['id'] = id;
              showRequest(data);
              ajax(data,"shop/getShopAttributes","onResult");
            }

    },
     getPresentProperties: function () {
      var id = prompt("shop_present id", "");
      if(id) {
        var data = {};
        data['id'] = id;
        showRequest(data);
        ajax(data, "shop/getPresentProperties", "onResult","");
      }
  },
     setPresentProperties: function () {
      var id = prompt("shop_present id", "");
      if(id) {
        var properties = prompt("properties", "");
        if(properties) {
            var data = {};
            data['id'] = id;
            data['data'] = properties;
            showRequest(data);
            ajax(data, "shop/setPresentProperties", "onResult","");
        }
      }
  } ,
  sendMailsToUsersWithNoOrders : function() {
     var shop_id = prompt("shop id", "");
      if(shop_id) {
            var data = {};
            data['shop_id'] = shop_id;
            showRequest(data);
            ajax(data, "shop/sendMailsToUsersWithNoOrders", "onResult","");
        }
  },
  sendMailsToUsersHowHasNotPickedUpPresents : function() {
     var shop_id = prompt("shop id", "");
      if(shop_id) {
            var data = {};
            data['shop_id'] = shop_id;
            showRequest(data);
            ajax(data, "shop/sendMailsToUsersHowHasNotPickedUpPresents", "onResult","");
        }
  },
  getToUsersHowHasNotPickedUpPresents : function() {
     var shop_id = prompt("shop id", "");
      if(shop_id) {
            var data = {};
            data['shop_id'] = shop_id;
            showRequest(data);
            ajax(data, "shop/getToUsersHowHasNotPickedUpPresents", "onResult","");
        }
  } ,
  testLogins : function() {
      var shop_id = prompt("shop id", "");
      if(shop_id) {
            var data = {};
            data['shop_id'] = shop_id;
            showRequest(data);
            ajax(data, "shop/testLogins", "onResult","");

      }

  } ,
  testGiftSelections : function() {
      var shop_id = prompt("shop id", "");
      if(shop_id) {
            var data = {};
            data['shop_id'] = shop_id;
            showRequest(data);
            ajax(data, "shop/testGiftSelections", "onResult","");

      }

}

}




