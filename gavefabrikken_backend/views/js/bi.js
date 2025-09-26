var _saleperson=""
var _activeTab = "";
var _currentDataSet
var _country = "dk";
var bi = {
    data:[],

    init:function(){
        $("#biTabs").tabs({
            collapsible: true,
            active: false,
            activate: function(event ,ui){
                switch(ui.newTab.index()) {
                    case 0:
                        _activeTab = "alle";
                        // Skjul dateContainer når "alle" er valgt
                        $(".dateContainer").hide();

                        if($("#from").val() != "" && $("#to").val() != ""){
                            bi.searchRange();
                        } else {
                            bi.showTotal();
                        }

                        break;
                    case 1:
                        _activeTab = "saledb";
                        // Vis dateContainer igen for andre tabs
                        $(".dateContainer").show();

                        if($("#from").val() != "" && $("#to").val() != ""){
                            bi.searchRange();
                        } else {
                            bi.showDBSalepersonShop();
                        }

                        break;
                    case 2:
                        // Vis dateContainer igen for andre tabs
                        $(".dateContainer").show();

                        bi.showChart();
                        //  $("#tabs-2").html("");
                        //  ajax({},"bi/getSalepersonList","bi.intFane1","");
                        break;
                    default:
                        // Vis dateContainer for default case
                        $(".dateContainer").show();
                }

            }
        });
        $(".range-search").click(()=>bi.searchRange())
        $(".range-reset").click(()=>bi.resetRange())
        $(".bi-flag").click( function(){
            $(".bi-flag").removeClass("bi-select-flag");
            $(this).addClass("bi-select-flag");
            bi.changeCountry( $(this).attr("data-id") )
        })

    },
 changeCountry:function(country){
    _country = country;
    if($("#from").val() != "" && $("#to").val() != ""){
        bi.searchRange();
    } else {
        if(_activeTab == "alle"){
            bi.showTotal();
        }
        if(_activeTab == "saledb"){
            bi.showDBSalepersonShop();
        }
    }
 },


 showChart:async function(){
     let result = await bi.loadChartData();
     console.log(result);

   var ctx = document.getElementById('myChart').getContext('2d');
var myChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Red', 'Blue', 'Yellow', 'Green', 'Purple', 'Orange'],
        datasets: [{
            label: '2',
            backgroundColor: "red",
            data: [12, 19, 3, 5, 2, 3],
        },{
          label: '1',
          backgroundColor: "green",
          data: [19, 2, 8, 2, 9, 1],
        }



        ]
    }
      });

 },

 loadChartData:function(){
            return new Promise(function(resolve, reject) {
         $.ajax(
            {
            url: 'index.php?rt=bi/loadDataForChart',
            type: 'POST',
            dataType: 'json',
            data: {}
            }).done(function(res) {
            if(res.status == 0) { resolve(res) }
            else { resolve(res) }
          })
        })

 },

 resetRange:function(){
    $("#from").val("");
    $("#to").val("");
    if(_activeTab == "alle"){
        bi.showTotal();
    }
    if(_activeTab == "saledb"){
        bi.showDBSalepersonShop();
    }
 },
 searchRange:function(){
    if(_activeTab == "alle"){
        let range = {start:$("#from").val(),end:$("#to").val(),country:_country}
        bi.showTotal(range);
    }
    if(_activeTab == "saledb"){
        let range = {start:$("#from").val(),end:$("#to").val(),country:_country}
        bi.showDBSalepersonShop(range);
    }
 },
    showDBSalepersonShop: async function(range=""){
        var totalval=0;
        var totalDB = 0;
        var totalCardSold = 0;
        var sumOrderCount = 0;
        var saleList = {};

        this.data = [];
        let csvData = [];
        csvData.push(["Sælger","Kort type","Antal solgt kort","Antal ordre","Sum","DB","Gennemsnit pr.order"]);
        $("#tabs-1-data").html("Indlæser data");

        response = {};

        // {orderantal: 27, antal: 1369, shop_id: 54, salesperson: 'AJO', shop_name: '24 Gaver DK 400 - 2024'}
        if(_country == "dk") {
            response = await this.loadDBSalepersonShop(range);
            let jgvRes = await this.loadDBSalepersonJGV(range);
            response = this.mergeSalesData(response.data.soldcard, jgvRes.data.jgv);
        }

        if(_country == "no") {
            response = await this.loadDBSalepersonShop(range);
        }
        if(_country == "se") {
            response = await this.loadDBSalepersonShopSE(range);
            console.log(response);
            //response = this.updateShopIds(response) // split af 400/440 da de var samme id
        }

        // DEBUG: Log hele response strukturen
        console.log("=== DEBUG showDBSalepersonShop ===");
        console.log("Full response:", response);
        console.log("Country:", _country);
        console.log("Response.data:", response.data);
        console.log("Response.data.soldcard:", response.data.soldcard);

        var html = "<button class='downloadCsvAlle'>Download csv</button><br>"
        html+="<table><tr><th>Sælger</th><th>Kort type</th><th>Antal solgt kort</th><th>Antal ordre</th><th>Sum</th><th>DB</th><th>Gennemsnit pr.order</th></tr>";

        $(response.data.soldcard).each(function( index,val ) {
            // DEBUG: Log hver enkelt post
            console.log(`=== Soldcard Item ${index} ===`);
            console.log("val object:", val);
            console.log("shop_id:", val.shop_id);
            console.log("shop_id type:", typeof val.shop_id);

            if(val.salesperson.toLowerCase() in saleList) {
                saleList[val.salesperson.toLowerCase()].push(val);
            } else {
                saleList[val.salesperson.toLowerCase()] = [];
                saleList[val.salesperson.toLowerCase()].push(val);
            }
        })

        console.log("Processed saleList:", saleList);

        for (let key in saleList) {
            console.log(`=== Processing salesperson: ${key} ===`);

            for(let i=0;saleList[key].length > i;i++){
                let val = saleList[key][i];

                // DEBUG: Log hver enkelt val i saleList
                console.log(`=== SalesList Item ${i} for ${key} ===`);
                console.log("val object:", val);
                console.log("shop_id:", val.shop_id);
                console.log("shop_id type:", typeof val.shop_id);

                // Test giftCardValue funktionen
                let giftCardResult = giftCardValue(val.shop_id);
                console.log("giftCardValue result:", giftCardResult);

                if (!giftCardResult) {
                    console.error(`ERROR: giftCardValue returned undefined for shop_id: '${val.shop_id}'`);
                    console.error("Salesperson:", val.salesperson);
                    console.error("Shop name:", val.shop_name);
                    // Spring denne post over for at undgå fejlen
                    continue;
                }

                var sum = (giftCardResult.kortVal*1)*(val.antal*1);
                let orderCountWS =  val.orderantal*1;
                let db = (giftCardResult.db*1)*(val.antal*1);
                let avgCardsPrSale =  (val.antal*1) / (orderCountWS*1);

                console.log("Calculated values:");
                console.log("- sum:", sum);
                console.log("- db:", db);
                console.log("- avgCardsPrSale:", avgCardsPrSale);

                html+="<tr class='"+val.salesperson.toLowerCase()+"'><td>"+val.salesperson.toLowerCase()+"</td><td>"+giftCardResult.kort+"</td><td  id='solgt"+val.shop_id+"'>"+val.antal+"</td><td>"+orderCountWS+"</td><td>"+ numberWithCommas(sum) +"</td> <td >"+numberWithCommas(db)+"</td><td >"+avgCardsPrSale.toFixed(0)+"</td></tr>"
                sumOrderCount+=orderCountWS;
                totalval+=sum*1;
                totalDB+=db*1;
                totalCardSold+=val.antal*1;

                if(saleList[key].length == i+1){
                    let totalAvgCardsPrSale =  totalCardSold / sumOrderCount;
                    var totalSaleHtml = "<tr class='"+val.salesperson.toLowerCase()+" total'><td>TOTAL</td><td></td><td>"+numberWithCommas(totalCardSold)+"</td><td>"+sumOrderCount+"</td><td>"+numberWithCommas(totalval)+"</td><td>"+numberWithCommas(totalDB)+"</td><td>"+totalAvgCardsPrSale.toFixed(0)+"</td></tr>";
                    totalAvgCardsPrSale = 0;
                    totalval=0;
                    totalDB = 0;
                    totalCardSold = 0;
                    sumOrderCount = 0;
                    html+= totalSaleHtml+"<tr><td colspan=5></td></tr>";
                }
                let tempdata = [
                    val.salesperson.toLowerCase(),
                    giftCardResult.kort,
                    val.antal,
                    orderCountWS,
                    numberWithCommas(sum),
                    numberWithCommas(db),
                    avgCardsPrSale.toFixed(0)
                ];
                csvData.push(tempdata)
            }
        }

        console.log("=== DEBUG END ===");

        this.data = csvData;
        $("#tabs-1-data").html(html+"</table>");
        this.setEventCSVdownload()
    },

    mergeSalesData: function(list1,list2) {


        // Forbered response struktur
        let response = {
            status: "1",
            data: {
                soldcard: []
            },
            message: ""
        };

        // Create maps to store processed data
        let jgvMap = new Map();

        // Process list2 (JGV data) first
        this.processJGVData(list2, jgvMap);

        // Process all data from list1
        list1.forEach(item => {
            // Hvis det er shop_id 7121, skal den special behandles
            if (Number(item.shop_id) === 7121) {
                this.processJGVItem(item, jgvMap);
            }
            // Alle andre shop_ids kopieres direkte
            else {
                response.data.soldcard.push({
                    orderantal: Number(item.orderantal),
                    antal: Number(item.antal),
                    shop_id: item.shop_id.toString(),
                    salesperson: item.salesperson,
                    shop_name: item.shop_name
                });
            }
        });

        // Tilføj alle JGV data til response
        jgvMap.forEach((salespersonMap) => {
            salespersonMap.forEach(entry => {
                response.data.soldcard.push({
                    orderantal: Number(entry.orderantal),
                    antal: Number(entry.antal),
                    shop_id: entry.shop_id,
                    salesperson: entry.salesperson,
                    shop_name: entry.shop_name
                });
            });
        });

        return response;
    },

    processJGVData: function(list2, jgvMap) {
        list2.forEach(item => {
            if (!jgvMap.has(item.salesperson)) {
                jgvMap.set(item.salesperson, new Map());
            }

            let shopId = item.present_list
                ? `7121_${item.present_list}`
                : '7121';

            let shopName = item.present_list
                ? `${item.shop_name.replace(' - 2024', '')}-${item.present_list} - 2024`
                : item.shop_name;

            jgvMap.get(item.salesperson).set(shopId, {
                orderantal: Number(item.orderantal),
                antal: Number(item.antal),
                shop_id: shopId,
                shop_name: shopName,
                salesperson: item.salesperson
            });
        });
    },

    processJGVItem: function(item, jgvMap) {
        if (!jgvMap.has(item.salesperson)) {
            jgvMap.set(item.salesperson, new Map());
        }

        let salespersonMap = jgvMap.get(item.salesperson);
        let totalPresentListAntal = 0;

        // Calculate total of present_list values
        salespersonMap.forEach(value => {
            if (value.shop_id !== '7121') {
                totalPresentListAntal += Number(value.antal);
            }
        });

        // Add adjusted base entry
        let adjustedAntal = Number(item.antal) - totalPresentListAntal;
        if (adjustedAntal > 0) {
            salespersonMap.set('7121', {
                orderantal: Number(item.orderantal),
                antal: adjustedAntal,
                shop_id: '7121',
                shop_name: item.shop_name,
                salesperson: item.salesperson
            });
        }
    },

    convertToSortedArray: function(jgvMap, result) {
        jgvMap.forEach(salespersonMap => {
            salespersonMap.forEach(entry => {
                result.push(entry);
            });
        });

        // Sort by salesperson and shop_id
        result.sort((a, b) => {
            if (a.salesperson !== b.salesperson) {
                return a.salesperson.localeCompare(b.salesperson);
            }
            return a.shop_id.localeCompare(b.shop_id);
        });
    },















 loadOrderCount:function(shop_id,salesperson){
         return new Promise(function(resolve, reject) {
         $.ajax(
            {
            url: 'index.php?rt=bi/getOrderCountOnSalesperson',
            type: 'POST',
            dataType: 'json',
            data: {saleperson:salesperson,shop_id:shop_id}

            }).done(function(res) {
            if(res.status == 0) { resolve(res) }
            else { resolve(res) }
          })
        })

 },
    loadDBSalepersonShopSE:function(range=""){
        if(range == "") range = {country:_country};
        return new Promise(function(resolve, reject) {
            $.ajax(
                {
                    url: 'index.php?rt=bi/loadDBSalepersonSE',
                    type: 'POST',
                    dataType: 'json',
                    data: range,

                }).done(function(res) {
                if(res.status == 0) { resolve(res) }
                else { resolve(res) }
            })
        })

    },

 loadDBSalepersonShop:function(range=""){
         if(range == "") range = {country:_country};
         return new Promise(function(resolve, reject) {
         $.ajax(
            {
            url: 'index.php?rt=bi/loadDBSaleperson',
            type: 'POST',
            dataType: 'json',
            data: range,

            }).done(function(res) {
            if(res.status == 0) { resolve(res) }
            else { resolve(res) }
          })
        })

 },
    updateShopIds: function(data) {
    // Går igennem hvert element i soldcard array
    data.data.soldcard.forEach(item => {
        // Tjek om card_values eksisterer og indeholder værdier
        if (item.card_values) {
            // Split card_values på komma for at håndtere multiple værdier
            const values = item.card_values.split(',');

            // Tjek for værdierne 440 og 400
            if (values.includes('440')) {
                item.shop_id = '1832_440';
            } else if (values.includes('400')) {
                item.shop_id = '1832_400';
            }
        }
    });

    return data;
},
    showTotal: async function(range=""){

        this.data = [];
        let csvData = [];
        csvData.push(["Kort type","Antal","solgt kort","Sum DB"]);
        response = {};

        if(_country == "dk"){
            response = await this.loadTotal(range);
            jgvRes = await this.loadJgvTotal(range,7121);
            let jgvFiltedData = this.handelJgvData(jgvRes,7121,"JGV");
            response = this.processArrayShowTotal(response,jgvFiltedData,7121);
        }
        if(_country == "no"){
            response = await this.loadTotal(range);
        }

        if(_country == "se"){
            response = await this.loadTotalSE(range);
            response = this.updateShopIds(response)
            console.log(response)
        }

        // DEBUG: Log hele response strukturen
        console.log("=== DEBUG showTotal ===");
        console.log("Full response:", response);
        console.log("Country:", _country);
        console.log("Response.data:", response.data);
        console.log("Response.data.soldcard:", response.data.soldcard);

        var html = "<button class='downloadCsvAlle'>Download csv</button><br>"
        html+="<table><tr><th>Kort type</th><th>Antal solgt kort</th><th>Sum</th><th>DB</th></tr>";
        var totalval=0;
        var totalDB = 0;
        var totalCardSold = 0;
        this.data = response;

        $(response.data.soldcard).each(function( index,val ) {
            // DEBUG: Log hver enkelt post
            console.log(`=== Item ${index} ===`);
            console.log("val object:", val);
            console.log("shop_id:", val.shop_id);
            console.log("shop_id type:", typeof val.shop_id);

            // Test giftCardValue funktionen
            let giftCardResult = giftCardValue(val.shop_id);
            console.log("giftCardValue result:", giftCardResult);

            if (!giftCardResult) {
                console.error(`ERROR: giftCardValue returned undefined for shop_id: '${val.shop_id}'`);
                console.error("Available shop_ids in giftCardValue function should include:", val.shop_id);
                // Spring denne post over for at undgå fejlen
                return true; // continue til næste iteration
            }

            var sum = (giftCardResult.kortVal*1)*(val.antal*1);
            let db = (giftCardResult.db*1)*(val.antal*1);

            console.log("Calculated sum:", sum);
            console.log("Calculated db:", db);

            html+="<tr><td>"+giftCardResult.kort+"</td><td  id='solgt"+val.shop_id+"'>"+val.antal+"</td><td>"+ numberWithCommas(sum)+"</td> <td >"+numberWithCommas(db)+"</td></tr>"
            totalval+=sum*1;
            totalDB+=db*1;
            totalCardSold+=val.antal*1;
            let tempdata = [
                giftCardResult.kort,
                val.antal,
                numberWithCommas(sum),
                numberWithCommas(db)
            ];
            csvData.push(tempdata)

        })

        console.log("=== DEBUG END ===");

        this.data = csvData;
        var totalSaleHtml = "<table><tr><th>Total antal kort solgt</th><th>Total salg i kr.</th><th>Total db i kr.</th></tr>";
        totalSaleHtml+= "<tr><td>"+numberWithCommas(totalCardSold)+"</td><td>"+numberWithCommas(totalval)+"</td><td>"+numberWithCommas(totalDB)+"</td></tr></table>";

        $("#tabs-0-data").html(totalSaleHtml+"<br><br>");
        $("#tabs-0-data").append(html+"</table>");
        this.setEventCSVdownload()
    },
    processArrayShowTotal: function(originalData, jgvData, shopId) {
        // Konverter shopId til string for at sikre konsistent sammenligning
        shopId = String(shopId);

        // Udtræk arrays fra objekterne
        if (jgvData.jgvFiltedData.length === 0 || originalData.data.length === 0) {
            return originalData;
        }

        const originalArray = originalData.data.soldcard;
        const jgvFiltedData = jgvData.jgvFiltedData;

        // Check om inputs er arrays
        if (!Array.isArray(originalArray) || !Array.isArray(jgvFiltedData)) {
            console.error('Inputs skal være arrays', {
                originalArray: originalArray,
                jgvFiltedData: jgvFiltedData
            });
            return {
                status: "0",
                data: {
                    soldcard: []
                },
                message: "Invalid input data"
            };
        }

        // Find det originale antal for det specificerede shop_id
        const originalShop = originalArray.find(item => String(item.shop_id) === shopId)?.antal || 0;

        // Filter og sorter entries for det specificerede shop_id
        const filteredShop = jgvFiltedData
            .filter(item => String(item.shop_id).startsWith(shopId))
            .sort((a, b) => String(a.shop_id).localeCompare(String(b.shop_id)));

        // Beregn total fra jgvFiltedData for det specificerede shop_id
        const totalShop = filteredShop.reduce((sum, item) => sum + item.antal, 0);

        // Opret nyt array med det specificerede shop_id først
        const result = [
            // Først den originale shop med justeret antal
            {
                ...originalArray.find(item => String(item.shop_id) === shopId),
                antal: originalShop - totalShop
            },
            // Derefter alle shop_XXX entries
            ...filteredShop,
            // Til sidst alle andre entries fra det originale array
            ...originalArray.filter(item => String(item.shop_id) !== shopId)
        ];

        return {
            status: "1",
            data: {
                soldcard: result
            },
            message: ""
        };
    },
handelJgvData:function (jgv,shopID,prefix){
    let jgvFiltedData = [];
    let total = 0;
    //{antal: 19790, shop_id: 56, shop_name: "24 Gaver DK 640 - 2024"
    $(jgv.data.jgv).each(function( index,val ) {
        total+=val.antal;
        let temp = {antal: val.antal, shop_id: shopID+"_"+val.present_list, shop_name: prefix+"-"+val.present_list}
        jgvFiltedData.push(temp);
    })
    return {
        total:total,
        jgvFiltedData:jgvFiltedData
    };
},

loadJgvTotal:function(range,shop_id){
    if(range == "") range = {country:_country,shop_id:shop_id};
    return new Promise(function(resolve, reject) {
        $.ajax(
            {
                url: 'index.php?rt=bi/getTotalJGV',
                type: 'POST',
                dataType: 'json',
                data: range
            }).done(function(res) {
            if(res.status == 0) { resolve(res) }
            else { resolve(res) }
        })
    })
},
loadDBSalepersonJGV:function(range){
        if(range == "") range = {country:_country};
        return new Promise(function(resolve, reject) {
            $.ajax(
                {
                    url: 'index.php?rt=bi/loadDBSalepersonJGV',
                    type: 'POST',
                    dataType: 'json',
                    data: range
                }).done(function(res) {
                if(res.status == 0) { resolve(res) }
                else { resolve(res) }
            })
        })
    },
    loadTotalSE:function(range){
        if(range == "") range = {country:_country};
        return new Promise(function(resolve, reject) {
            $.ajax(
                {
                    url: 'index.php?rt=bi/loadTotalSE',
                    type: 'POST',
                    dataType: 'json',
                    data: range
                }).done(function(res) {
                if(res.status == 0) { resolve(res) }
                else { resolve(res) }
            })
        })
    },

 loadTotal:function(range){
         if(range == "") range = {country:_country};
         return new Promise(function(resolve, reject) {
         $.ajax(
            {
            url: 'index.php?rt=bi/loadTotal',
            type: 'POST',
            dataType: 'json',
            data: range
            }).done(function(res) {
            if(res.status == 0) { resolve(res) }
            else { resolve(res) }
          })
        })
 },
 setEventCSVdownload:function() {
        let self = this
     $(".downloadCsvAlle").off("click").click(function () {
        self.downloadCSV();
     });
 },
 downloadCSV:function(){
     var data = this.data;
     var csvContent = "";

     data.forEach(function(row){
         csvContent += row.join(";") + "\n";
     });

     // Opret en blob af CSV data
     var blob = new Blob([csvContent], { type: 'text/csv;charset=UTF-8;' });

     // Generer en URL til blobben
     var url = URL.createObjectURL(blob);

     // Opret et midlertidigt download link og udløs det
     var downloadLink = document.createElement("a");
     downloadLink.href = url;
     downloadLink.download = "stats-data.csv";

     document.body.appendChild(downloadLink);
     downloadLink.click();
     document.body.removeChild(downloadLink);
 },
 intFane1:function(response){
    var html = '<select id="saleperson" >';
    $.each(response.data, function (index, value) {
        html+= '<option value="'+value.attributes.salesperson+'">'+value.attributes.salesperson+'</option>';
    })
    $("#tabs-1").html(html+"</select><button onclick='bi.loadDbSaleperson()' >Søg</button><br><br><b><hr></b><br><div id='total'></div><br><div id='kortRegnskab'></div><br><div id='tabs-1-content'></div>");

 },
 loadDbSaleperson:function(){
     $("#tabs-1-content").html("Systemet arbejder");
     $("#total").html("");

   var saleperson =  $("#tabs-1").find("#saleperson").val();
   _saleperson =   saleperson;
   ajax({saleperson:saleperson,datatype:"data"},"bi/getSaleOnCardShop","bi.showDbSaleperson","");

 },
 intFane2:function(response){
    var html = '<select id="salepersonShop" >';
    $.each(response.data, function (index, value) {
        html+= '<option value="'+value.attributes.salger+'">'+value.attributes.salger+'</option>';
    })
    $("#tabs-2").html(html+"</select><button onclick='bi.dbSalepersonShop()' >Søg</button><br><br><b><hr></b><br><div id='total'></div><br><div id='kortRegnskab'></div><br><div id='tabs-2-content'></div>");
 },
 dbSalepersonShop:function(){
     $("#tabs-2-content").html("Systemet arbejder");
     $("#total").html("");

   var saleperson =  $("#tabs-2").find("#salepersonShop").val();
   _saleperson =   saleperson;
   ajax({saleperson:saleperson,datatype:"data"},"bi/getSaleOnShop","bi.showDBSalepersonShop","");

 },





 showDbSaleperson:function(response){

    var html = "<table id='dbsale' ><thead><tr><th>VARERNR</th><th>BESKRIVELSE</th><th>KOSTPRIS</th><th>ENHEDSPRIS</th><th>DB PR STK.</th><th>ANTAL SOLGTE</th><th>DG TOTAL</th></tr></thead><tbody>";
        var obj = response.data;
        var total = 0;
        for (var key in obj) {
            jsonObj =  JSON.parse(obj[key]);
            total+= jsonObj.dg*1;
            html+="<tr class='faneColor'><td>"+jsonObj.varenr+"</td><td>"+jsonObj.vare_txt+"</td><td>"+jsonObj.kostpris+"</td><td>"+jsonObj.enhedspris+"</td><td>"+jsonObj.db_pr_stk+"</td><td>"+jsonObj.antal+"</td><td>"+jsonObj.dg+"</td></tr>"
        }
        html+="</tbody></table><br /><br /><br /><br /><br />";
        $("#tabs-1-content").html(html);
        $('#dbsale').DataTable();
        $("#total").html("Total d�kningsbidrag: "+numberWithCommas(Math.round(total))+" kr")
        bi.getSoldCardAmount();
        // ajax({saleperson:_saleperson,datatype:"dataNullPrice"},"bi/getSaleOnCardShop","bi.error1","");

 },
 getSoldCardAmount:function(){
   ajax({saleperson:_saleperson},"bi/getSoldCardAmount","bi.getSoldCardAmountResponse","");
 },
 getSoldCardAmountResponse:function(response){
     var html ="<table><tr><th>Kort type</th><th>Antal solgt kort</th><th>Sum</th><th>Antal kort med valg</th><th>Antal kort uden valg</th></tr>";
     var totalval=0;
     $(response.data.soldcard).each(function( index,val ) {
        var sum = (giftCardValue(val.shop_id).kortVal*1)*(val.antal*1);
        html+="<tr><td>"+giftCardValue(val.shop_id).kort+"</td><td  id='solgt"+val.shop_id+"'>"+val.antal+"</td><td>"+ numberWithCommas(sum)+" kr"  +"</td> <td id='valgt"+val.shop_id+"'></td><td id='ejValgt"+val.shop_id+"'></td> </tr>"
        totalval+=sum;
    })
    html+="<tr><td></td><td></td><td></td></tr><tr><td><b>SUM</b></td><td></td><td><b>"+numberWithCommas(totalval)+" kr.</b></td> <td></td><td></td>   </tr></table>";
    $("#kortRegnskab").html(html);
    bi.getSoldCardAmountWithNoOrder();

 },
  getSoldCardAmountWithNoOrder:function(){
   ajax({saleperson:_saleperson},"bi/getSoldCardAmountWithNoOrder ","bi.getSoldCardAmountWithNoOrderResponse","");
 },
 getSoldCardAmountWithNoOrderResponse:function(response ){
     $(response.data.soldcard).each(function( index,val ) {
         var solgt = $("#solgt"+val.shop_id).html();
         $("#valgt"+val.shop_id).html(val.antal);
         var diff = ( solgt*1 ) - (val.antal*1);
         $("#ejValgt"+val.shop_id).html(diff);
     })
     bi.error1();
 },

 error1:function(response){
    console.log("dataNullPrice")
    console.log(response)
           ajax({saleperson:_saleperson,datatype:"dataNoRecord"},"bi/getSaleOnCardShop","bi.error2","");
 },
 error2:function(response){
   console.log("dataNoRecord")
   console.log(response)
            ajax({saleperson:_saleperson,datatype:"datamultible"},"bi/getSaleOnCardShop","bi.error3","");
 },
  error3:function(response){
    console.log("datamultible")
   console.log(response)
  //   ajax({saleperson:_saleperson,datatype:"dataNullKostPrice"},"bi/getSaleOnCardShop","bi.error4","");

 },
  error4:function(response){
    console.log("dataNullKostPrice")
   console.log(response)

 },











}
function numberWithCommas(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}
function giftCardValue(id)
{

    id = id.toString();
    switch(id) {




                    case "7121":
                        return {kort:"JGV-ejvalgt",kortVal:600,db:200,lang:1};
                    break;
                    case "7121_400":
                        return {kort:"JGV-400",kortVal:400,db:130,lang:1};
                    break;
                    case "7121_600":
                        return {kort:"JGV-600",kortVal:600,db:200,lang:1};
                    break;
                    case "7121_800":
                        return {kort:"JGV-800",kortVal:800,db:260,lang:1};
                    break;


                    case "52":
                        return {kort:"Julegavekortet 560",kortVal:560,db:200,lang:1};
                    break;
                    case "4668":
                        return {kort:"Julegavekortet 720",kortVal:800 ,db:250,lang:1};
                    break
                    case "54":
                        return {kort:"24gaver-400",kortVal:400,db:100,lang:1};

                    break;
                    case "55":
                        return {kort:"24gaver-560",kortVal:560,db:200,lang:1};

                    break;
                    case "56":
                        return {kort:"24gaver-640",kortVal:640,db:230,lang:1};

                    break;
                    case "53":
                        return {kort:"Guldgavekortet-800",kortVal:800,db:250,lang:1};

                    break;
                    case "2395":
                        return {kort:"Guldgavekortet-1120",kortVal:960,db:300,lang:1};
                    break;
                    case "9321":
                        return {kort:"Guldgavekortet-1400",kortVal:1400,db:400,lang:1};
                    break;

                    case "2548":
                        return {kort:"Det grønne julegavekort",kortVal:640,db:230,lang:1};
                    break;
                    case "290":
                        return {kort:"Drømmegavekortet-200",kortVal:200,db:50,lang:1};

                    break;
                    case "310":
                        return {kort:"Drømmegavekortet-300",kortVal:300,db:75,lang:1};

                    break;
                    case "575":
                        return {kort:"Designjulegavekortet 640",kortVal:640,db:230,lang:1};
                    break;
                    case "4662":
                        return {kort:"Designjulegavekortet 960",kortVal:800 ,db:280,lang:1};
                    break

                    case "1832":
                        return {kort:"Svensk jgk 440",kortVal:400,db:100,lang:3};
                    break
                    case "1832_400":
                        return {kort:"Svensk jgk 400",kortVal:400,db:100,lang:3};
                    break
                    case "9495":
                        return {kort:"Svensk jgk 440 all inclusive",kortVal:440,db:110,lang:3};
                        break
                    case "1832_440":
                        return {kort:"Svensk jgk 440",kortVal:400,db:110,lang:3};
                    break
                    case "5117":
                        return {kort:"Svensk jgk 600",kortVal:600,db:150,lang:3};
                    break
                    case "8271":
                        return {kort:"Sommarpresent kortet",kortVal:440,db:110,lang:3};
                    break


                    case "1981":
                       return {kort:"Svensk jgk 800",kortVal:800,db:200,lang:3};
                    break
                    case "2558":
                       return {kort:"Svensk jgk 1200",kortVal:1200,db:300,lang:3};
                    break
                    case "4793":
                        return {kort:"Svensk jgk 300",kortVal:300,db:75,lang:3};
                    break


                    case "57":
                        return {kort:"jgk 400",kortVal:400,db:100,lang:4};

                    break
                    case "58":
                        return {kort:"jgk 600",kortVal:600,db:200,lang:4};

                    break
                    case "59":
                        return {kort:"jgk 800",kortVal:800,db:250,lang:4};

                    break
                    case "272":
                        return {kort:"jgk 300",kortVal:300,db:75,lang:4};

                    break
                    case "574":
                        return {kort:"Gullgavekortet-1000",kortVal:1000,db:300,lang:4};
                    break
                    case "2550":
                        return {kort:"Gullgavekortet-1200",kortVal:1200,db:400,lang:4};
                    break
                    case "2549":
                        return {kort:"Gullgavekortet-800",kortVal:800 ,db:400,lang:4};
                    break
                    case "4740":
                        return {kort:"Gullgavekortet-2000",kortVal:800 ,db:400,lang:4};
                    break



                    case "2960":
                        return {kort:"Luksusgavekortet-400 ",kortVal:400 ,db:100,lang:1};
                    break
                    case "2961":
                        return {kort:"Luksusgavekortet-200",kortVal:200 ,db:50,lang:1};
                    break
                    case "2962":
                        return {kort:"Luksusgavekortet-640",kortVal:640 ,db:230,lang:1};
                    break
                    case "2963":
                        return {kort:"Luksusgavekortet-800",kortVal:800 ,db:250,lang:1};
                    break





  }
}
 $( function() {
    var dateFormat = "mm/dd/yy",
      from = $( "#from" )
        .datepicker({
          defaultDate: "+1w",
          changeMonth: true,
          numberOfMonths: 3
        })
        .on( "change", function() {
          to.datepicker( "option", "minDate", getDate( this ) );
        }),
      to = $( "#to" ).datepicker({
        defaultDate: "+1w",
        changeMonth: true,
        numberOfMonths: 3
      })
      .on( "change", function() {
        from.datepicker( "option", "maxDate", getDate( this ) );
      });

    function getDate( element ) {
      var date;
      try {
        date = $.datepicker.parseDate( dateFormat, element.value );
      } catch( error ) {
        date = null;
      }

      return date;
    }
  } );
     /*
    buildTabTable:function(response){
        var html = "<div class=\"shopboard-container\"><table width=100%  id=\"shopboardTable\"><thead><tr><th>Shopnavn</th><th>S�lger</th><th>VA</th><th>Ordretype</th><th>SO</th><th>Kunde</th><th>Kontaktperson</th><th>Mail</th><th>Telefon</th><th>Antal</th><th>Antal gavevalg</th><th>Levering</th><th>Flere leveringsadresser</th><th>Info</th><th>Shop �bner</th><th>Shop lukker</th><th></th><th></th></tr></thead>";
        var obj = response.data.shop;
         html+="<tbody>";
        for (var key in obj) {
            html+="<tr class='faneColor"+obj[key].fane+"' ><td>"+obj[key].shop_navn+"</td><td>"+obj[key].salger+"</td><td>"+obj[key].valgshopansvarlig+"</td><td>"+obj[key].ordretype+"</td><td>"+obj[key].salgsordrenummer+"</td><td>"+obj[key].kunde+"</td><td>"+obj[key].kontaktperson+"</td><td>"+obj[key].mail+"</td><td>"+obj[key].telefon+"</td><td>"+obj[key].antal_gaver+"</td><td>"+obj[key].antal_gavevalg+"</td><td>"+obj[key].levering+"</td><td>"+obj[key].flere_leveringsadresser+"</td><td>"+obj[key].info+"</td> <td>"+obj[key].shop_aabner+"</td> <td>"+obj[key].shop_lukker+"</td><td><button onclick=\"shopboard.status('"+obj[key].id+"')\">STATUS</button></td><td><button onclick=\"shopboard.edit('"+obj[key].id+"')\">EDIT</button></td> </tr>"
        }
        html+="</tbody></table></div><br /><br /><br /><br /><br /><br />";
        var tab = "#tabs-"+shopboard.selectedTab;
         $(tab).html("");
        $(tab).html(html);
        shopboard.initTable('#shopboardTable');
    },

    */