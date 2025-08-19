
 var shopboard = {
    selectedTab:0,
    selectedTabId:"",
    selectedShop:"",
    saveType:"",
     localization:1,
     isInit: true,
    data:{},
     shopID:"",
    init:function(localization){

       // $(document).keypress(function(e) {
         // if (e.keyCode == '13') {   return false;  } });
        shopboard.localization = localization;
        $( "#shopboard_tabs" ).tabs();
        console.log("init")
        shopboard.loadTabsData(1);

    },
    // liste af oprettede shop til import
    updateTable:function(){
        console.log("updateTable")
        shopboard.loadTabsData( shopboard.selectedTabId )
    },
    setLocalization:function (){
        if ($("#localization").is(":checked")) {
            shopboard.localization = 4;

        } else {
            shopboard.localization = 1;
        }
        shopboard.updateLocalization()
        shopboard.updateTable();
    },

    updateLocalization:function(localization){
        let list = "";
        let valgshopansvarligList = "";
        if (shopboard.localization == 4) {
            shopboard.localization = 4
            list  = `            
                
                <option value='alle'>Alle</option>
                <option value='WT'>WT</option>
                <option value='AR'>AR</option>
                <option value='LLJ'>LLJ</option>
                <option value='JL'>JL</option>
                <option value='MA'>MA</option>
                <option value='ARRO'>ARRO</option>
                <option value='BHO'>BHO</option>                                                
                
            `;
            valgshopansvarligList = `
                <option value='alle'>Alle</option>
                <option value='CS'>CS</option>
                <option value='LCS'>LCS</option>                
                <option value='LBJ'>LBJ</option>                
                <option value='MG'>MG</option>
                <option value='JM'>JM</option>
                <option value='AT'>AT</option>
                <option value='PM'>PM</option>
                <option value='SEL'>SEL</option>
                <option value='LSG'>LSG</option>
            `;


        } else {
            shopboard.localization = 1
            list  = `            
         <option value='alle'>Alle</option>
            <option value='KT'>KT</option>
            <option value='CLE'>CLE</option>
            <option value='RTT'>RTT</option>
            <option value='AS'>AS</option>
            <option value='JHC'>JHC</option>
            <option value='CBR'>CBR</option>
            <option value='AMN'>AMN</option>
            <option value='LNO'>LNO</option>
            <option value='Ek'>Ek</option>
            <option value='MLI'>MLI</option>
            <option value='LKL'>LKL</option>
            <option value='HBL'>HBL</option>
            <option value='TMA'>TMA</option>
             <option value='CES'>CES</option>
             <option value='CH'>CH</option>
                         <option value='SEL'>SEL</option>
            <option value='LSG'>LSG</option>  
                 
            `;
            valgshopansvarligList = `
             <option value='alle'>Alle</option>
             <option value='KT'>KT</option>
             <option value='CLE'>CLE</option>
             <option value='RTT'>RTT</option>
             <option value='AS'>AS</option>
             <option value='JHC'>JHC</option>
             <option value='CBR'>CBR</option>
             <option value='AMN'>AMN</option>
             <option value='LNO'>LNO</option>
             <option value='TMA'>TMA</option>
             <option value='HBL'>HBL</option>
             <option value='CES'>CES</option>
             <option value='CH'>CH</option>
                         <option value='SEL'>SEL</option>
            <option value='LSG'>LSG</option>
            `;











        }
        $("#user").html(list)




    },
    csv:function(){
        const json  = shopboard.data
        var fields = Object.keys(json[0])
        var replacer = function(key, value) { return value === null ? '' : value }
        var csv = json.map(function(row){
          return fields.map(function(fieldName){
         return JSON.stringify(row[fieldName], replacer)
        }).join(';')
    })
    csv.unshift(fields.join(';')) // add header column
    csv = csv.join('\r\n');
    shopboard.download("shopboard.csv",csv)

    },
    download:function(filename, data) {
     
       var element = document.createElement('a');
      element.setAttribute('href', 'data:text/csv; charset=ISO-8859-1,' + escape(data));
      element.setAttribute('download', filename);

      element.style.display = 'none';
      document.body.appendChild(element);
      element.click();
      document.body.removeChild(element);
    },
    addNew:function(){
        shopboard.saveType = "new";
        shopboard.showAddShopForm();
    },

    showAddShopForm:function(){
       $( "#dialog-shopboard" ).dialog({
            resizable: true,
            height: 600,
            width: 600,
            modal: true,
            buttons: {
                "Gem": function() {
                    shopboard.add();
                },
                Cancel: function() {
                    $( this ).dialog( "close" );
                }
            }
        });

      $( "#shop_aabner" ).datepicker({
               dateFormat:"dd-mm-yy",
               numberOfMonths: 3,
               showButtonPanel: true
            });
        $( "#shop_lukker" ).datepicker({
               dateFormat:"dd-mm-yy",
               numberOfMonths: 3,
               showButtonPanel: true
            });
        $( "#levering" ).datepicker({
               dateFormat:"dd-mm-yy",
               numberOfMonths: 3,
               showButtonPanel: true
            });
        $( "#reminder" ).datepicker({
               dateFormat:"dd-mm-yy",
               numberOfMonths: 3,
               showButtonPanel: true
            });
        $( "#datepicker" ).datepicker({
               dateFormat:"dd-mm-yy",
               numberOfMonths: 3,
               showButtonPanel: true
            });
        $( "#demoshop" ).datepicker({
               dateFormat:"dd-mm-yy",
               numberOfMonths: 3,
               showButtonPanel: true
            });



         //    $("#dialog-shopboard").find("input").val("");
        $( "#info").val("");
        $('#dialog-shopboard option').attr('selected', false);

        $("#valgshopansvarlig").html("");
   //     var valgshopansvarligHtml = " <option value='alle'>Alle</option> <option value='SSP'>SSP</option><option value='KT'>KT</option><option value='KA'>KA</option> <option value='CLE'>CLE</option> <option value='SP'>SP</option>           <option value='RTT'>RTT</option>            <option value='KH'>KH</option>            <option value='SGH'>SGH</option> <option value='MLH'>MLH</option>           ";
        var valgshopansvarligHtml = `   
                <option value='alle'>Alle</option>
                <option value='KT'>KT</option>
                <option value='CLE'>CLE</option>
                <option value='RTT'>RTT</option>
                <option value='AS'>AS</option>
                <option value='JHC'>JHC</option>
                <option value='CBR'>CBR</option>
                <option value='AMN'>AMN</option>            
                <option value='LNO'>LNO</option>        
            <option value='Ek'>Ek</option>  
            <option value='MLI'>MLI</option>  
            <option value='LKL'>LKL</option>  
            <option value='TMA'>TMA</option>  
            <option value='HBL'>HBL</option>    
            <option value='CES'>CES</option>     
            <option value='CH'>CH</option>  
                        <option value='SEL'>SEL</option>
            <option value='LSG'>LSG</option>               
            `;

        if(shopboard.localization == 4){
            valgshopansvarligHtml = `   
                <option value='alle'>Alle</option>
                <option value='WT'>WT</option>
                <option value='AR'>AR</option>
                <option value='LLJ'>LLJ</option>
                <option value='JL'>JL</option>
                <option value='MA'>MA</option>
                <option value='ARRO'>ARRO</option>
                <option value='BHO'>BHO</option>                     
  
              `;
        }


        $("#valgshopansvarlig").html(valgshopansvarligHtml);
        $("#salger").html("");
        var salgerHtml = "";
        var saleMembers = [
            'Alle',
            'KM',
            'MM',
            'SG',
            'TE',
            'RO',
            'TF',
            'ELM',
            'BMO',
            'CJN',
            'AJO',
            'JLK',
            'ANA',
            'CAS',
            'SPE',
            'CSL',
            'DLA',
            'SL',
            'LDW',
            'CFS',
            'HBL',
            'TMA'
        ];
        if(shopboard.localization == 4){
            saleMembers = [
                'Alle',
                'US',
                'CS',
                'LCS',
                'LBJ',
                'JM',
                'AT',
                'PM'
                ];
        }


        saleMembers.forEach(function (item, index) {
            salgerHtml+= "<option value='"+item+"'>"+item+"</option>"
        });

/*
        salgerHtml+= "      <option value='alle'>Alle</option> "+
         " <option value='KM'>KM</option> " +
         "  <option value='MM'>MM</option> "  +
         "  <option value='SG'>SG</option> "  +
         "   <option value='TE'>TE</option> " +
         "   <option value='RO'>RO</option> "  +
         "   <option value='TF'>TF</option> " +
         "   <option value='ELM'>ELM</option> " +
         "   <option value='BMO'>BMO</option> " +
         "   <option value='CJN'>CJN</option> " +
         "   <option value='MHB'>MHB</option> " +
         "   <option value='DG'>DG</option>  " +
         "   <option value='MVO'>MVO</option>  " +
         "   <option value='GP'>GP</option>  " +
         "   <option value='DLA'>DLA</option>  " +
         "   <option value='SL'>SL</option>  " +
         "   <option value='CFS'>CFS</option> ";
*/


        $("#salger").html(salgerHtml);
        $("#ordretype").html("");
        var ordretypeHtml = "  <option value=\"valgshop\">Valgshop</option>     <option value=\"papirvalg\">Papirvalg</option>   ";
        $("#ordretype").html(ordretypeHtml);
        document.getElementById("flere_leveringsadresser").checked = false;
        document.getElementById("sprog_lag").checked = false;
        document.getElementById("navn_paa_gaver").checked = false;
        document.getElementById("julekort").checked = false;

     shopboard.showAvariableShopInform()

    },
    showAvariableShopInform:function(){
        if( shopboard.saveType == "new"  ){
            ajax({'local':shopboard.localization},"shopboard/getAllNotInShops","shopboard.showAvariableShopInformResponse","");
        }
    },
    showAvariableShopInformResponse:function(response){
        var  html = "<option id='0'>Custon shop</<option>";
        var obj = response.data.shop;
        for (var key in obj) {
            html+="<option id='"+obj[key].id+"'>"+obj[key].name+"</option>"
        }
        $("#notActiveShops").html(html);
    },
    notActiveShopsChange:function(element){
       // $(element).val() != "Custon shop" ? $("#shop_navn").hide() : $("#shop_navn").show();
    },
    add:function(){
        var formData = {};
        if($("#notActiveShops").attr('id') != 0 ){
            formData.shop_navn = $("#shop_navn").val();
          //  formData.fk_shop = 0;
        } else {
            formData.shop_navn = $( "#notActiveShops :selected").text();
         //   formData.fk_shop = $("#notActiveShops :selected").attr('id')
        }

        formData.salger             =  $( "#salger :selected").val();
        formData.valgshopansvarlig  =  $( "#valgshopansvarlig :selected").val();
        formData.ordretype          =  $( "#ordretype :selected").text();
        formData.salgsordrenummer   =  $( "#salgsordrenummer").val();



        formData.kontaktperson           =  $( "#kontaktperson").val();
        formData.mail                    =  $( "#mail").val();
        formData.telefon                 =  $( "#telefon").val();
        formData.antal_gaver             =  $( "#antal_gaver").val();
        formData.antal_gavevalg          =  $( "#antal_gavevalg").val();
        formData.shop_aabner             =  $( "#shop_aabner").val();
        formData.shop_lukker             =  $( "#shop_lukker").val();
        formData.levering                =  $( "#levering").val();
        formData.flere_leveringsadresser =  ($("#flere_leveringsadresser").is(':checked') ? 1 : 0 );
        formData.autogave                =  $( "#autogave").val();
        formData.sprog_lag               =  ($("#sprog_lag").is(':checked') ? 1 : 0 );
        formData.reminder                =  $( "#reminder").val();
        formData.login                   =  $( "#login").val();
        formData.navn_paa_gaver          =  ($("#navn_paa_gaver").is(':checked') ? 1 : 0 );
        formData.reserveret                =  ($("#reserveret").is(':checked') ? 1 : 0 );
        formData.julekort                =  ($("#julekort").is(':checked') ? 1 : 0 );
        formData.info                   =   $( "#info").val();

        formData.indpakning             =  ($("#indpakning").is(':checked') ? 1 : 0 );
        formData.privatlevering         =  ($("#privatlevering").is(':checked') ? 1 : 0 );
        formData.udland                   =   $( "#udland").val();
        formData.demoshop                   =   $( "#demoshop").val();
        formData.pakkeri                   =   $( "#pakkeri").val();
        formData.localization                  = shopboard.localization;


        var postData = {"data":formData};
        if( shopboard.saveType == "new"  ){
           ajax({'data':formData},"shopboard/addNew","shopboard.addResponse","");
        } else {
             formData.shop_navn = $("#shop_navn").val();
            formData.id = shopboard.selectedShop;
            ajax({'data':formData},"shopboard/updateData","shopboard.addResponse","");
        }




    },
    addResponse:function(){
        $( "#dialog-shopboard" ).dialog( "close" );




        var str = ""+shopboard.selectedTab+"";
            if(str == "9"){ str = "alle"; }
            console.log("addResponse")
        shopboard.loadTabsData(str)
    },
    changeUser:function(){
         var user =  $( "#user :selected").val();
          $(".statusTabs").html("");
          if(shopboard.selectedTab == "alle" || shopboard.selectedTab == 9){
              shopboard.selectedTab = 9;
              ajax({'user':user,'local':shopboard.localization},"shopboard/showAll","shopboard.buildTabTable","");
          } else {
              var user =  $( "#user :selected").val();
              ajax({'tabId':shopboard.selectedTab ,'user':user,'local':shopboard.localization},"shopboard/loadFaneData","shopboard.buildTabTable","");
          }
    },

    // henter data til tabellen under de enkelte tabs
    loadTabsData:function(tabId){
        console.log("loadTabsData")
         shopboard.selectedTabId = tabId;
         var user =  $( "#user :selected").val();
         $(".statusTabs").html("");

         if(tabId == "alle"){
             shopboard.selectedTab = 9;
              ajax({'user':user,'local':shopboard.localization},"shopboard/showAll","shopboard.buildTabTable","");

          } else {
              shopboard.selectedTab = tabId;
              ajax({'tabId':tabId,'user':user,'local':shopboard.localization},"shopboard/loadFaneData","shopboard.buildTabTable","");
          }

    },
    buildTabTable:function(response){
        shopboard.data = response.data.shop;
        var html = "<div class=\"shopboard-container\"><table width=100%  id=\"shopboardTable\"><thead><tr><th>Shopnavn</th><th>Sælger</th><th>VA</th><th>Ordretype</th><th>SO</th><th>Kunde</th><th>Kontaktperson</th><th>Mail</th><th>Telefon</th><th>Antal</th><th>Antal gavevalg</th><th>Indpakning</th><th>Julekort</th><th>Navn på gaver</th><th>Flere lev.adress.</th><th>Privatlevering</th><th>Udland</th><th>Info</th><th>Reserveret</th><th>demoshop</th><th>Shop åbner</th><th>Shop lukker</th><th>Levering</th><th>Pakkeri</th><th></th><th></th></tr></thead>";
        var obj = response.data.shop;
         html+="<tbody>";
        for (var key in obj) {
              html+="<tr data-id='"+obj[key].id+"' class='faneColor"+obj[key].fane+"' >"+
            "<td source='shop_navn'>"+obj[key].shop_navn+"</td>"+
            "<td source='salger'>"+obj[key].salger+"</td>"+
            "<td source='valgshopansvarlig'>"+obj[key].valgshopansvarlig+"</td>"+
            "<td source='ordretype'>"+obj[key].ordretype+"</td>"+
            "<td source='salgsordrenummer'>"+obj[key].salgsordrenummer+"</td>"+
            "<td source='kunde'>"+obj[key].kunde+"</td>"+
            "<td source='kontaktperson'>"+obj[key].kontaktperson+"</td>"+
            "<td source='mail'>"+obj[key].mail+"</td>"+
            "<td source='telefon'>"+obj[key].telefon+"</td>"+
            "<td source='antal_gaver'>"+obj[key].antal_gaver+"</td>"+
            "<td source='antal_gavevalg'>"+obj[key].antal_gavevalg+"</td>"+
            "<td ><label class='shopboard-switch'> <input type='checkbox' class='shopboard-single-update' source='indpakning'  "+shopboard.buildTabTableHelperInitCheckbox(obj[key].indpakning)+" />  <span class='shopboard-slider shopboard-round'></span></label></td>"+
            "<td ><label class='shopboard-switch'> <input type='checkbox' class='shopboard-single-update' source='julekort' "+shopboard.buildTabTableHelperInitCheckbox(obj[key].julekort)+" />  <span class='shopboard-slider shopboard-round'></span></label></td>"+
            "<td ><label class='shopboard-switch'> <input type='checkbox' class='shopboard-single-update' source='navn_paa_gaver' "+shopboard.buildTabTableHelperInitCheckbox(obj[key].navn_paa_gaver)+" />  <span class='shopboard-slider shopboard-round'></span></label></td>"+
            "<td ><label class='shopboard-switch'> <input type='checkbox' class='shopboard-single-update' source='flere_leveringsadresser'  "+shopboard.buildTabTableHelperInitCheckbox(obj[key].flere_leveringsadresser)+" />  <span class='shopboard-slider shopboard-round'></span></label></td>"+
            "<td ><label class='shopboard-switch'> <input type='checkbox' class='shopboard-single-update' source='privatlevering'  "+shopboard.buildTabTableHelperInitCheckbox(obj[key].privatlevering)+" />  <span class='shopboard-slider shopboard-round'></span></label></td>"+
            "<td source='udland'>"+obj[key].udland+"</td>"+
            "<td source='info'>"+obj[key].info+"</td>"+
            "<td ><label class='shopboard-switch'> <input type='checkbox' class='shopboard-single-update' source='reserveret'  "+shopboard.buildTabTableHelperInitCheckbox(obj[key].reserveret)+" />  <span class='shopboard-slider shopboard-round'></span></label></td>"+
            "<td source='demoshop'>"+shopboard.datoCorrection(obj[key].demoshop)+"</td>"+
            "<td source='shop_aabner'>"+shopboard.datoCorrection(obj[key].shop_aabner)+"</td>"+
            "<td source='shop_lukker'>"+shopboard.datoCorrection(obj[key].shop_lukker)+"</td>"+
            "<td source='levering'>"+shopboard.datoCorrection(obj[key].levering)+"</td>"+
            "<td source='pakkeri'>"+obj[key].pakkeri+"</td>"+
            "<td><button onclick=\"shopboard.status('"+obj[key].id+"')\">STATUS</button></td>"+
            "<td source=''><button onclick=\"shopboard.edit('"+obj[key].id+"')\">EDIT</button></td> </tr>"
        }
        html+="</tbody></table></div><br /><br /><br /><br /><br /><br />";
        var tab = "#tabs-"+shopboard.selectedTab;
         $(tab).html("");
        $(tab).html(html);
        shopboard.initTable('#shopboardTable');
        if(shopboard.isInit){
            shopboard.isInit = false;
            shopboard.updateLocalization()
        }

    },
    buildTabTableHelperInitCheckbox:function(val){
        return (val==1) ? "checked ":"";
    },
    datoCorrection:function(datoStr){


        if(datoStr != "" && datoStr != null){
          var datoArr =  datoStr.split("-");
          return datoArr[2] +"/"+ datoArr[1]+"/"+datoArr[0];
      } else {
        return datoStr;
      }

    },

    edit:function(id){
        shopboard.selectedShop = id;
         shopboard.saveType = "edit";
        ajax({'postData':id,'local':shopboard.localization},"shopboard/loadStatus","shopboard.editResponse","");
    },
    editResponse:function(response){
        shopboard.showAddShopForm();
        var data = response.data.status[0];

        $("#shop_navn").val(data.shop_navn);
        $( "#salgsordrenummer").val(data.salgsordrenummer);

        $( "#kontaktperson").val(data.kontaktperson);
        $( "#mail").val(data.mail);
        $( "#telefon").val(data.telefon);
        $( "#antal_gaver").val(data.antal_gaver);
        $( "#antal_gavevalg").val(data.antal_gavevalg);
        $( "#shop_lukker").val(data.shop_lukker);
        $( "#shop_aabner").val(data.shop_aabner);
        $( "#demoshop").val(data.demoshop);
        $( "#levering").val(data.levering);

        $( "#autogave").val(data.autogave);
        $( "#reminder").val(data.reminder);
        $( "#login").val(data.login);
        $( "#udland").val(data.udland);
        $( "#pakkeri").val(data.pakkeri);
        $( "#info").val(data.info);
        $( "#ordretype :selected").text();



        $("#salger option[value='"+data.salger+"']").attr("selected", true);
        $("#valgshopansvarlig option[value='"+data.valgshopansvarlig+"']").attr("selected", true);
        $("#ordretype option[value='"+data.ordretype+"']").attr("selected", true);

        document.getElementById("flere_leveringsadresser").checked = false;
        document.getElementById("sprog_lag").checked = false;
        document.getElementById("navn_paa_gaver").checked = false;
        document.getElementById("julekort").checked = false;
        document.getElementById("indpakning").checked = false;
        document.getElementById("privatlevering").checked = false;
        document.getElementById("reserveret").checked = false;


        if(data.flere_leveringsadresser == 1){
                document.getElementById("flere_leveringsadresser").checked = true;
        }
        if(data.sprog_lag == 1){
                document.getElementById("sprog_lag").checked = true;
        }
        if(data.navn_paa_gaver == 1){
                document.getElementById("navn_paa_gaver").checked = true;
        }
        if(data.julekort == 1){
                document.getElementById("julekort").checked = true;
        }
        if(data.indpakning == 1){
                document.getElementById("indpakning").checked = true;
        }
        if(data.privatlevering == 1){
                document.getElementById("privatlevering").checked = true;
        }
        if(data.reserveret == 1){
                document.getElementById("reserveret").checked = true;
        }


        $( "#info").val(data.info);

    },

    status:function(id){
        ajax({'postData':id,'local':shopboard.localization},"shopboard/loadStatus","shopboard.statusResponse","");
    },
    statusResponse:function(response){
       shopboard.selectedShop = response.data.status[0].id;

       $( "#dialog-status" ).dialog({
            resizable: true,
            height: 300,
            width: 600,
            modal: true,
            buttons: {
                "Gem": function() {
                    shopboard.updateStatus();
                },
                Cancel: function() {
                    $( this ).dialog( "close" );
                }
            }
        });
        var checkPos = response.data.status[0].fane;
          $("#statusMenu").html("");
//        $('input:radio[name="status"][value="'+checkPos+'"]').attr('checked',true);
            var html = "";
            var checked = "";
            for(var i=1;i<9;i++){
                if((checkPos*1) === i){

                checked = "checked"
                     } else {
                         checked = ""
                     }
                html+= '<td><input id="status'+i+'" '+checked+' type="radio" name="status" value="'+i+'"></td>';
            }
          $("#statusMenu").html(html);

    },
    updateStatus:function(){
        var formData = {};
        formData.id = shopboard.selectedShop;
        formData.fane = $("input[name='status']:checked").val();
        var postData = {"data":formData,"local":shopboard.localization};
        ajax(postData,"shopboard/updataStatus","shopboard.updateStatusResponse","");
    },
    updateStatusResponse:function(response){
        $( "#dialog-status" ).dialog( "close" );
        var str = ""+shopboard.selectedTab+"";
        console.log("updateStatusResponse")
        shopboard.loadTabsData(str)
    },
    clearTable:function(tableId){
        var table = $(tableId).DataTable();
        table.destroy();
    },
    initTable:function(tableId){


    shopboard.clearTable(tableId);
        $(tableId).DataTable( {
            "scrollY":        tableHeight+"px",
            "scrollX": true,
            "scrollCollapse": true,
            "paging":         false,
            "initComplete": function(settings, json) {
                $(".shopboard-single-update").unbind("click").click(function() {

                var field = $(this).attr("source");

                if(field != undefined){
                    var formData = {};
                    console.log("genner i table")
                    formData.id = $(this).parent().parent().parent().attr("data-id");
                    formData[field] = ($(this).is(':checked') ? 1 : 0 )
                    ajax({'data':formData,'local':shopboard.localization},"shopboard/updateData","","");
                  }
                });
              }
        } );

    },
    singleUpdate:function(response){
        alert(response);
    }















 }
function ascii (a) { return a.charCodeAt(0); }