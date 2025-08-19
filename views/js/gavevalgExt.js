         // [[['Verwerkende industrie sadfsadfas dfa d fsda ads sad ', 9],['Retail', 8], ['Primaire producent', 7],['Out of home', 6],['Groothandel', 5], ['Grondstof', 4], ['Consument', 3], ['Bewerkende industrie', 2]]]
    var plotCount = {};
    var plotTotal = 0;
    plotCount["Ej valgt" ] = 0;

    var gavevalg = {
    fieldsDeffData:{},
    fieldsDeffDB:[],
    userData:{},
    ShopPresent:{},
    userDataDB:[],
    userDataDBCopy:[],
    userOrderDataDB:[],
    presentList:{},
    presentsHtml:"",
    companyId:"",
    changeGiftUserId:"",
    tempUserId:"",
    dataToPass:{},
    fieldIdOrder:[],
    fieldsDeffDataResponse:{},
    pagenationOffset:1,
    pagenationLimit:100,
    pagenationTotal:0,
    pagenationStart:0,
    pagenationEnd:0,
    pagenationLeft:0,
    isDeleted:"",
    hit:[],
    colId:"",
    newUserSelectFeild:[],
    init:function(){


            plotCount = {};
            plotTotal = 0;
            plotCount["Ej valgt" ] = 0;

        $("#kundepanelOptionBtn").hide();
        $("#gavevalgContainer").html("");
        this.fieldsDeffData = {};
        this.fieldsDeffDB = [];
        this.userData={};
        this.userDataDB=[];
        this.userOrderDataDB=[];
        this.presentList={};
        this.fieldIdOrder=[];
        this.newUserSelectFeild=[];
        this.userDataDBCopy = []
        this.colId = "";
        this.loadPresentsList();


    },

       sog:function(sogStr){
         this.hit = [];
       // console.log(this.userDataDBCopy)
        if(sogStr == ""){
            this.pagenationLimit = 100;
          this.hit = [];
          this.userDataDB = this.userDataDBCopy;
            this.pagenationStart = 0;
            this.pagenationEnd =  this.pagenationLimit;
            if(this.pagenationEnd > this.userDataDB.length)
            {
                 this.pagenationEnd  = this.userDataDB.length;
            }
        } else {
            if(sogStr.length < 3){
                alert("Søgningen skal minimun indholde 3 bogstaver/tal")
                $("#sysMsg").hide();
                return;
            }
            this.pagenationLimit = 4000;
            for(var i=0;this.userDataDBCopy.length >i;i++){
                   if(this.userDataDBCopy[i].sog.indexOf( sogStr ) != -1){
                        this.hit.push(this.userDataDBCopy[i]);
                   }
            }
        }
        this.buildTableHtml();
    },
    loadPresentsList:function(){
            ajax({"shop_id":_editShopID,"token":_token},"shop/getShopPresentsExt","gavevalg.processPresentsListNew","");
    },
    processPresentsListNew:function(response){
     this.presentsHtml = ""; //"<div><h1>Denne funktion er under opdatering</h1></div><center><table border=0 >";
     var tempHtml = "";
     for(var i=0;response.data.length >i;i++){
            var rd = response.data[i].attributes;
            let mediaPathArr = rd.media_path.split("/");
            let mediaPath = "views/media/type/"+mediaPathArr[mediaPathArr.length-1];
            tempHtml+="<tr><td height=30 width=200><img width=60 src='"+mediaPath+"' /></td><td width=200>"+rd.model_name+"</td><td width=200>"+rd.model_no+"</td><td><button onclick=\"gavevalg.doChangeGift('"+rd.present_id+"','"+rd.model_name+"','"+rd.model_no+"','"+rd.model_id+"','"+rd.model_id+"') \">Vælg</button></td></tr>";

     }

            this.presentsHtml+=tempHtml ;
         this.presentsHtml+="</table></center>"
         $( "#gavevalgSkiftGave").html(this.presentsHtml)




         this.loadFieldsDeff();
    },

    processPresentsList:function(response)
    {
        var tempHtml = "";
        this.presentsHtml = "<center><table border=0 >";
        for(var i=0;response.data.length >i;i++){
            var modelJson = $.parseJSON(response.data[i].variant_list)
         //   console.log(modelJson)
            if(modelJson.length > 0){
                tempHtml = "";
                gaveId = response.data[i].id;
                gaveNavn =  response.data[i].name;
                tempHtml = "";

               $.each(modelJson, function(i, item) {

                    if(item.language_id == "1"){
                        var modelData = [];


                        for(var i=0;item.feltData.length > i;i++){
                            var key = Object.keys(item.feltData[i])[0];
                            modelData[key] = item.feltData[i][key]
                        }
                        // console.log(modelData)

                        tempHtml+="<tr><td height=30 width=200>"+gaveNavn+"</td><td width=200>"+modelData['variant']+"</td><td width=200>"+modelData['variantSub']+"</td><td><button onclick=\"gavevalg.doChangeGift('"+gaveId+"','"+modelData['variant']+"','"+modelData['variantSub']+"','"+modelData['variantNr']+"','"+modelData['variantId']+"') \">Vælg</button></td></tr>";
                     }
                })
                this.presentsHtml+=tempHtml ;
            } else {
                this.presentsHtml+= "<tr><td height=30>"+response.data[i].name+"</td><td></td><td></td><td><button onclick=\"gavevalg.doChangeGift('"+response.data[i].id+"','','','','0' ) \">Vælg</button></td></tr>";
            }

        }
         this.presentsHtml+="</table></center>"
         $( "#gavevalgSkiftGave").html(this.presentsHtml)

        this.loadFieldsDeff();
    },
    loadFieldsDeff:function(){
            ajax({"id":_editShopID,"token":_token},"shop/getShopAttExt","gavevalg.loadUserData","");
    },
    loadUserData:function(response){
        this.fieldsDeffDataResponse = response;
        this.fieldsDeffData = response.data.attributes;

        this.companyId = response.data.company_id;
        // work
        if(_editShopID == "580"){
            ajax({"id":_editShopID,"token":_token},"shopload/getUsers","gavevalg.buildDB","");
            //ajax({"id":_editShopID,"offset":this.pagenationOffset,"limit":this.pagenationLimit},"newshopload/getUsers","gavevalg.buildDB","");
        } else {
            ajax({"id":_editShopID,"token":_token},"shopload/getUsers","gavevalg.buildDB","");
        }

    },
    buildDB:function(response){

        //this.fieldsDeffDB

        var giftDescription = [];
        this.userData = response.data.users


              // work
        if(_editShopID == "580"){
        //    this.pagenationTotal = response.antal[0].attributes.antal;


        }

        for(var j=0;this.fieldsDeffData.length > j;j++)
        {
            this.fieldsDeffDB.push({"id":this.fieldsDeffData[j].id,"name":this.fieldsDeffData[j].name,"is_list":this.fieldsDeffData[j].is_list,"list_data":this.fieldsDeffData[j].list_data });
               if(this.fieldsDeffData[j].name == "Brugernavn"){
                this.colId = "id_"+this.fieldsDeffData[j].id;
            }
        }
       // console.log(this.fieldsDeffDB);

        for(var j=0;this.userData.length > j;j++)
        {

            var temp = [];
            var tempSogStr = "";
            for(var i=0;this.userData[j].user_attributes.length > i;i++)
            {
                tempSogStr+= this.userData[j].user_attributes[i].attributes.attribute_value+"#";
                temp["id_"+this.userData[j].user_attributes[i].attributes.attribute_id] = this.userData[j].user_attributes[i].attributes.attribute_value;
            }
           // console.log(temp);
            tempSogStr = tempSogStr.toLowerCase();
            this.userDataDBCopy.push( {"id":this.userData[j].id,"data":temp,"sog":tempSogStr} );
            this.userDataDB.push( {"id":this.userData[j].id,"data":temp,"sog":tempSogStr} );
           // plotTotal++;

            if(this.userData[j].has_orders == true){

                var gave =  this.userData[j].orders[0].present_model_name.split("###")

                var gaveId = this.userData[j].orders[0].present_model_id;
                var plotGift = gave[0]+", "+gave[1];
            /*
                    if( this.userData[j].orders[0].language_id == 1){
                        var plotGift = gave[0]+"-"+gave[1];
                        giftDescription[gaveId] = plotGift;
                    }
                    else if(giftDescription[gaveId] == undefined)
                    {
                      var plotGift = gave[0]+"-"+gave[1];
                      giftDescription[gaveId] = plotGift;
                    }


                    if(plotCount[gaveId] == undefined){
                       plotCount[gaveId] = 1;
                    } else {
                        plotCount[gaveId]+=1;
                    }
             */


                this.userOrderDataDB["gift_"+this.userData[j].id]  = {"id":this.userData[j].orders[0].present_id,"model":gave[1],"name":gave[0],"registered":this.userData[j].orders[0].registered,"registered_date":this.userData[j].orders[0].registered_date,"present_model_present_no":this.userData[j].orders[0].present_model_present_no};
            } else {
            //    plotCount["Ej valgt"]+=1
                this.userOrderDataDB["gift_"+this.userData[j].id] = {"id":""};
            }


        }
        // [[['Verwerkende industrie sadfsadfas dfa d fsda ads sad ', 9],['Retail', 8], ['Primaire producent', 7],['Out of home', 6],['Groothandel', 5], ['Grondstof', 4], ['Consument', 3], ['Bewerkende industrie', 2]]]
        //var data1 = [];
        //plotTotal Math.round(x)

        /*
        for (var key in plotCount) {
            var tal = (plotCount[key] / plotTotal)*100
            if(giftDescription[key] == undefined){
              giftDescription[key] = "Ej valgt";
            }


            data1.push([giftDescription[key]+" - "+plotCount[key],Math.round(tal)])
        }
        data = [];
        data.push(data1)
        */



        this.pagenationStart = 0;
        this.pagenationEnd =  this.pagenationLimit;
        if(this.pagenationEnd > this.userDataDB.length)
        {
         this.pagenationEnd  = this.userDataDB.length;
        }
        //this.buildTableHtml();
        ajax({"shopid":_editShopID,"token":_token},"order/getOrderSummary","gavevalg.preparePie","");
    },
    preparePie:function(response){

        var data = [];
        var tableViewData = [];
        var counter = 0;
        for (var key in response.data) {
            counter+= response.data[key].attributes.antal*1;
            var tal = ((response.data[key].attributes.antal*1) / this.userData.length)*100
            data.push([response.data[key].attributes.antal+" | "+response.data[key].attributes.model_name+" - "+response.data[key].attributes.model_no,Math.round(tal)])
            let tableView = {
                alias:response.data[key].attributes.fullalias,
                model_name:response.data[key].attributes.model_name+" - "+response.data[key].attributes.model_no,
                antal:response.data[key].attributes.antal,
                procent:Math.round(tal)
            }
            tableViewData.push(tableView);
         }

        var ejvalgtal = ((this.userData.length-counter) / this.userData.length)*100

        tableViewData.unshift({
            alias:'0',
            model_name:'Ej valgt',
            antal:this.userData.length-counter,
            procent:Math.round(ejvalgtal)
        })


        data.unshift([this.userData.length-counter+" | Ej valgt",Math.round(ejvalgtal)]);
        data = [data];
        buildPie(data)
        this.buildProductTable(tableViewData);
        this.buildTableHtml();
        this.getGiftData();
    },
    getGiftData:function ()
    {
        console.log(_editShopID)
    },


    buildProductTable:function (data){
        let currentSort = {
            field: 'antal',
            direction: 'desc'
        };

        $('.product-table').before('<button class="download-csv-btn" style="margin: 10px 0; padding: 8px 15px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">Download CSV</button>');

        function downloadCSV(data) {
            let csvContent = "\uFEFF";
            csvContent += "Alias;Model navn;Antal;Procent\n";

            data.forEach(function(item) {
                const row = [
                    item.alias || '-',
                    item.model_name || '-',
                    item.antal,
                    item.procent
                ];
                csvContent += row.join(";") + "\n";
            });

            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8' });
            const link = document.createElement("a");
            link.href = URL.createObjectURL(blob);
            link.setAttribute("download", "produktdata.csv");
            document.body.appendChild(link);

            link.click();
            document.body.removeChild(link);
            URL.revokeObjectURL(link.href);
        }

        function renderTable(data) {
            const tbody = $('.product-table tbody');
            tbody.empty();

            data.forEach(function(item) {
                tbody.append(
                    '<tr>' +
                    '<td>' + (item.alias || '-') + '</td>' +
                    '<td>' + (item.model_name || '-') + '</td>' +
                    '<td class="number">' + item.antal + '</td>' +
                    '<td class="number">' + item.procent + '%</td>' +
                    '</tr>'
                );
            });
        }

        function sortData(field) {
            if (currentSort.field === field) {
                currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
            } else {
                currentSort.field = field;
                currentSort.direction = 'desc';
            }

            $('.product-table th').removeClass('sorting sorting-asc sorting-desc');
            const th = $('.product-table th[data-sort="' + field + '"]');
            th.addClass('sorting sorting-' + currentSort.direction);

            const sortedData = data.slice().sort(function(a, b) {
                let aVal = a[field] || '';  // Default to empty string if undefined
                let bVal = b[field] || '';  // Default to empty string if undefined

                // Special handling for alias field
                if (field === 'alias') {
                    // Handle cases where alias is missing
                    if (!aVal && !bVal) return 0;
                    if (!aVal) return currentSort.direction === 'asc' ? 1 : -1;
                    if (!bVal) return currentSort.direction === 'asc' ? -1 : 1;

                    // Split into numeric and alphabetic parts
                    let aMatch = aVal.match(/^(\d+)([a-zA-Z]*)$/);
                    let bMatch = bVal.match(/^(\d+)([a-zA-Z]*)$/);

                    if (aMatch && bMatch) {
                        let aNum = parseInt(aMatch[1]);
                        let bNum = parseInt(bMatch[1]);
                        let aLetter = aMatch[2] || '';
                        let bLetter = bMatch[2] || '';

                        // First compare numbers
                        if (aNum !== bNum) {
                            return currentSort.direction === 'asc' ? aNum - bNum : bNum - aNum;
                        }
                        // If numbers are equal, compare letters
                        return currentSort.direction === 'asc'
                            ? aLetter.localeCompare(bLetter)
                            : bLetter.localeCompare(aLetter);
                    }
                    return currentSort.direction === 'asc'
                        ? String(aVal).localeCompare(String(bVal))
                        : String(bVal).localeCompare(String(aVal));
                }

                // Handle numeric fields
                if (typeof aVal === 'number' && typeof bVal === 'number') {
                    return currentSort.direction === 'asc' ? aVal - bVal : bVal - aVal;
                }

                // Handle text fields
                return currentSort.direction === 'asc'
                    ? String(aVal).localeCompare(String(bVal))
                    : String(bVal).localeCompare(String(aVal));
            });

            renderTable(sortedData);
            return sortedData;
        }

        // Initialize table
        renderTable(data);

        // Set up click handlers for sorting
        $('.product-table th').click(function() {
            const field = $(this).data('sort');
            sortData(field);
        });

        // Set up click handler for CSV download
        $('.download-csv-btn').click(function() {
            const currentData = sortData(currentSort.field);
            downloadCSV(currentData);
        });

        // Initial sort
        sortData('antal');
    },


    loadNextUsers:function(){

    this.pagenationStart = this.pagenationEnd;
    this.pagenationEnd = this.pagenationEnd + this.pagenationLimit;
    if(this.pagenationEnd > this.userDataDB.length)
    {
       this.pagenationLeft =  this.userDataDB.length - this.pagenationStart;
        this.pagenationEnd = this.userDataDB.length;
    }
    gavevalg.buildTableHtml();

    },
    loadBackUsers:function(){
        this.pagenationEnd =  this.pagenationStart
        this.pagenationStart = this.pagenationEnd - this.pagenationLimit;
    /*
        if(this.pagenationStart < 0 ){
          this.pagenationStart = 0
          this.pagenationEnd = this.pagenationEnd + this.pagenationLimit;
        }
        */
        gavevalg.buildTableHtml();
    },
    setCollId:function(id){
        this.colId = "id_"+id;
        var sog = $("#kundepanelSog").val();
        sogStr = sog.toLowerCase()
        //sortCollSelected
        //$(".gavevalg").find("[data-id='" + id + "']").hide()

        gavevalg.sog(sogStr)

    },


    buildTableHtml:function()
    {

        if(this.hit.length > 0 || $("#kundepanelSog").val() != ""){
            this.userDataDB = this.hit;

            this.pagenationStart = 0;
            this.pagenationEnd =  this.pagenationLimit;
            if(this.pagenationEnd > this.userDataDB.length)
            {
                 this.pagenationEnd  = this.userDataDB.length;
            }

        } else {

        }



      //     console.log(this.colId)




           var  sortArr = [];
           var  quick = [];
            for (var i = 0; i < this.userDataDB.length; i++) {

                // console.log(data[i].data[0][colId]);
                   sortArr["_" + this.userDataDB[i].id] = this.userDataDB[i].data[this.colId]
                quick["_" + this.userDataDB[i].id] = this.userDataDB[i];
            }
            var tuples = [];
            for (var key in sortArr) tuples.push([key, sortArr[key]]);
            tuples.sort(function (a, b) {
                a = a[1];
                b = b[1];
                return a < b ? -1 : (a > b ? 1 : 0);
            });
            for (var i = 0; i < tuples.length; i++) {
                var key = tuples[i][0];
                var value = tuples[i][1];
            }
            var sog = []
            for (var key in tuples) {
                sog.push(quick[tuples[key][0]]);
            }
            this.userDataDB =sog










        var ingenValgte = [];
        var dropDownLists = [];


        var dataOrder = [];
        var html = "";
        dialogHtml = "<br /><table width=500>" ;
        //work
//        if(_editShopID == "580"){
        html+= "<div id=\"htmlpagenation\" style=\" line-height: 20px; font-size: 20px;\"><center>";
        if(this.pagenationStart > 0 ){
            html+= "<span id=\"prevUsers\" onclick=\"gavevalg.loadBackUsers()\"  style=\" cursor:pointer; color:white;  margin-right:15px;font-size: 1.5rem;  \"> &#10229; forrige</span>"
        }
        html+= "<div style=\"  display: inline-block;  line-height: 20px;  \"><span id=\"pagenationStart\">"+(this.pagenationStart+1)+"</span>"
        html+= "<span> - </span><span  id=\"pagenationEnd\">"+ this.pagenationEnd +"</span><span style=\"margin-left:5px; font-size:12px; \">( ud af: "+this.userDataDB.length+" )</span></div>"
        if(this.pagenationEnd > (this.userDataDB.length-1)) {

        } else {
            html+= "<span id=\"nextUsers\" onclick=\"gavevalg.loadNextUsers()\"  style=\" cursor:pointer; color:white; margin-left:15px; line-height: 20px; font-size: 1.5rem; \"> næste &#10230; </span>"
        }

        html+= "</div> </center><br>";
 //       }

        html+= "<table class='gavevalg'><tr>";

        for(var i=0;this.fieldsDeffDB.length >i;i++){
         //console.log(this.fieldsDeffDB[i])
            if(this.fieldsDeffDB[i].is_list == "1"){

               dropDownLists["drop_"+this.fieldsDeffDB[i].id] = this.fieldsDeffDB[i].list_data;
                     html+="<th data-id='"+this.fieldsDeffDB[i].id+"'>"+this.fieldsDeffDB[i].name+"</th>";
            } else {
                 html+="<th  data-id='"+this.fieldsDeffDB[i].id+"'><span style=\"cursor:pointer;\" class=\"sortColl\" id=\"sortCollid_"+this.fieldsDeffDB[i].id+"\"  onclick=\"gavevalg.setCollId('"+this.fieldsDeffDB[i].id+"')\" >"+this.fieldsDeffDB[i].name+"</span></th>";
            }





            dataOrder.push(this.fieldsDeffDB[i].id);
            dialogHtml+="<tr><td>"+this.fieldsDeffDB[i].name+"</td><td><input data-id='"+this.fieldsDeffDB[i].id+"' type=\"text\" /></td>";





        }

        html+="<td>Gave Titel</td><td>Model</td><td class=\"ba_admin\" valign=top><label>Bruger admin:</label><br /><img width=\"20\" height=\"20\" src=\"views/media/icon/excel2.jpg\" title=\"Download liste over dem der ikke har valgt\" onclick=\"gavevalg.downloadExcel2() \"><img width=\"20\" height=\"20\" src=\"views/media/icon/excel.png\" title=\"Download en liste med dem som har valgt\" onclick=\"gavevalg.downloadExcel() \"><img width=\"20\" height=\"20\" class=\"notShowKundepanel\" src=\"views/media/icon/1373253494_plus_64.png\" title=\"Opret ny bruger\" onclick=\"gavevalg.showCreateNew() \"><img class='reminderMail' width=\"20\" title=\"Send en mail til dem som ikke har valgt gave\" height=\"20\" src=\"views/media/icon/1373253286_letter_64.png \" onclick=\"gavevalg.sendMail() \"></td><td class=\"qr_admin\" valign=top><label >Udlevering admin:</label><br><img width=\"20\" height=\"20\" src=\"views/media/icon/excel.png\" title=\"Download liste over dem der ikke har hentet deres gave\" onclick=\"gavevalg.qrExcel() \"><img width=\"20\" title=\"Send en mail til dem som ikke hentet deres gave\" height=\"20\" src=\"views/media/icon/1373253286_letter_64.png \" onclick=\"gavevalg.qrSendMail() \"><img width=\"20\" title=\"Download logfil\" height=\"20\" src=\"views/media/icon/1373253484_clipboard_64.png\" onclick=\"gavevalg.qrDownloadLog() \"></td></tr>";
        dialogHtml+="</table>";
        $( "#gavevalgDialog" ).html(dialogHtml);
        var savefieldIdOrder = true;
        var first = "";
       // console.log(this.userDataDB)

      //  for(var i=0;this.userDataDB.length >i;i++){
          for(var i=this.pagenationStart;this.pagenationEnd >i;i++){
            var delived = "";



            if(  this.isDeleted.indexOf(this.userDataDB[i].id) == -1 ){


            html+="<tr id=\"gavevalgrow_"+this.userDataDB[i].id+"\">";
            for(var j=0;dataOrder.length>j;j++){
               var tempNewUserSelectFeild = "";
               // html+="<td>"+this.userDataDB[i].data["id_"+dataOrder[j]]+"</td>";
               var selectHtml;

               if( dropDownLists["drop_"+dataOrder[j]]  != null )  {
                var selectHtml = "<select data="+dataOrder[j]+" class=\"selectBoxElement\" style=\" padding:2px;  \">";
                    var tempDrop = "";

                    var selectData = dropDownLists["drop_"+dataOrder[j]].split("\n");
                    //console.log(selectData)


                    if(this.userDataDB[i].data["id_"+dataOrder[j]] == ""){
                           selectHtml+="<option  selected  value=\"\">Vælg</option>"
                    } else {
                        tempDrop = this.userDataDB[i].data["id_"+dataOrder[j]];
                        selectHtml+="<option  selected value=\""+this.userDataDB[i].data["id_"+dataOrder[j]]+"\">"+this.userDataDB[i].data["id_"+dataOrder[j]]+"</option>"
                    }

                    for(var jj=0;selectData.length > jj;jj++){

                        if(tempDrop != selectData[jj]){
                            selectHtml+="<option  value=\""+this.userDataDB[i].data["id_"+dataOrder[j]]+"\">"+selectData[jj]+"</option>";
                        }
                        if( first != "done"){
                            tempNewUserSelectFeild+="<option  value=\""+this.userDataDB[i].data["id_"+dataOrder[j]]+"\">"+selectData[jj]+"</option>";
                        }
                    }
                    if(first == dataOrder[j]) { first = "done"   }
                    if(first == ""){  first = dataOrder[j];   }
                    if(tempNewUserSelectFeild != "" && first != "done"){
                        this.newUserSelectFeild["id_"+dataOrder[j]] = tempNewUserSelectFeild;
                        this.fieldIdOrder.push(dataOrder[j]);
                    }
                    tempNewUserSelectFeild = "";
                    selectHtml+= "</select>";
                  html+="<td>"+selectHtml+"</td>";




               } else {
                  html+="<td><input field-id='"+dataOrder[j]+"' type='text' value='"+this.userDataDB[i].data["id_"+dataOrder[j]]+"' /></td>";
                      if(savefieldIdOrder == true){
                               this.fieldIdOrder.push(dataOrder[j]);
                        }
               }




            }
                    savefieldIdOrder = false;
            if(this.userOrderDataDB["gift_"+this.userDataDB[i].id].id == ""){
                var selectedGift = "<td>Ikke valgt gave</td><td></td>";
                var selectedGift = "<td id='giftTitle_"+this.userDataDB[i].id+"'>Ikke valgt gave</td><td  id='giftModel_"+this.userDataDB[i].id+"'></td>";
            }  else {
                //console.log(this.userOrderDataDB)
                var modelStr =  this.userOrderDataDB["gift_"+this.userDataDB[i].id].model;
               
                modelStr = modelStr ?? "";

                //modelStr = modelStr.replace("###"," - ");
                var selectedGift = "<td id='giftTitle_"+this.userDataDB[i].id+"'>"+this.userOrderDataDB["gift_"+this.userDataDB[i].id].name +"</td><td  id='giftModel_"+this.userDataDB[i].id+"'>"+modelStr +"</td>";
                if(this.userOrderDataDB["gift_"+this.userDataDB[i].id].registered == "0"){
                    delived = "<img width=\"25\" height=\"25\" src=\"views/media/icon/notdelived.png\" id=\"delived_"+this.userDataDB[i].id+"\" title=\"Gave ikke udleveret\" onclick=\"gavevalg.newInitRegDelive('"+this.userDataDB[i].id+"') \" />";
                } else {
                 //   delived = "<img alert('Gaven er udleveret display: \n"+this.userOrderDataDB["gift_"+this.userDataDB[i].id].registered_date+"') width=\"25\" height=\"25\" src=\"views/media/icon/delived.png\" title=\""+this.userOrderDataDB["gift_"+this.userDataDB[i].id].registered_date+"\"  />"
                    delived = "<img width=\"25\" height=\"25\" src=\"views/media/icon/delived.png\" id=\"delived_"+this.userDataDB[i].id+"\" title=\"Gave er udleveret\" onclick=\"gavevalg.newInitUnRegDelive('"+this.userDataDB[i].id+"') \" />";
                }
            }


            //html+="<td> <input type=\"text\" disabled  title=\""+selectedGift+"\" name=\"usrname\" maxlength=\"20\" value=\""+selectedGift+"\"></td>";
            html+= selectedGift

//            html+="<td><select ><option value='0' style=\"color:blue;\">"+selectedGift+" </option>"+this.presentsHtml+"</select></td>";
            html+="<td class=\"ba_admin\"><img class=\"notShowKundepanel\" width=\"20\" height=\"20\" src=\"views/media/icon/1373253284_save_64.png\" title=\"Gem\" onclick=\"gavevalg.updateUserData('"+this.userDataDB[i].id+"') \">";







            if(this.userOrderDataDB["gift_"+this.userDataDB[i].id].id == ""){
                html+="<img width=\"23\" height=\"23\" title=\"Skift gave\" class=\"notShowKundepanel \" src=\"views/media/icon/gave.png\" title=\"Skift gave\"  onclick=\"gavevalg.changeGiftShowMenu('"+this.userDataDB[i].id+"')\">";
                html+="<img width=\"20\" height=\"20\" src=\"views/media/icon/PurchaseNoOrder-50.png\" title=\"Ingen kvittering, ej valgt gave\" ) \">";
            } else {
                html+="<img width=\"23\" height=\"23\" src=\"views/media/icon/history.png\" title=\"Vis tidligere valg\" onclick=\"gavevalg.loadHistory('"+this.userDataDB[i].id+"') \">" ;
                html+="<img width=\"23\" height=\"23\"  class=\"notShowKundepanel \" title=\"Skift gave\" src=\"views/media/icon/gave.png\" title=\"Skift gave\"  onclick=\"gavevalg.changeGiftShowMenu('"+this.userDataDB[i].id+"') \">";
                html+="<img width=\"20\" height=\"20\" src=\"views/media/icon/Purchase Order-50.png\" title=\"Send kvittering\"  onclick=\"gavevalg.printReceipt('"+this.userDataDB[i].id+"') \">";
            }

            html+="<img width=\"20\" class=\"notShowKundepanel\" height=\"20\" src=\"views/media/icon/1373253296_delete_64.png\" title=\"Slet\" onclick=\"gavevalg.deleteUser('"+this.userDataDB[i].id+"') \">" ;
            html+="<td class=\"qr_admin\" >"+delived+"</td></tr>";






        }

        }
        html+="</tr></table>";
        $("#gavevalgContainer").html(html);
        if(_is_closed == "1"){
            if(_is_gf_user == "0"){
                            $(".notShowKundepanel").hide()
            }



        }
         $("#sortColl"+this.colId).addClass("sortCollSelected")
        ajax({"shop_id":_editShopID,"token":_token},"shop/kundepanelSettings","initSettings");
    },
    addNewUserToTable:function(data){
    var newRow = $(".gavevalg").find("tr").last().html();
      //  console.log(newRow);
      // console.log(   this.fieldIdOrder);
    },


    loadHistory:function(userId){
            $( "#kundeDialog" ).html("systemt arbejder, vent venligt")
            dialog =  $( "#kundeDialog" ).dialog({
                title: 'Historik',
                autoOpen: true,
                height: 550,
                width: 550,
                modal: true,
                buttons: {
                    Cancel: function() {
                    dialog.dialog( "close" );
                }
            }
            });
        ajax({'userId':userId,"token":_token,"shop_id":_editShopID},"shop2/getCardHistory","gavevalg.loadHistoryResponse","");
    },
    loadHistoryResponse:function(response){
        if(response.data == 0){
            $( "#kundeDialog" ).html("Der blev ikke fundet noget")
        } else {
            $( "#kundeDialog" ).html(Base64.decode(response.data))
        }
    },
    updateUserData:function(id)
    {
         var attributes = []
         var len = ($( "#gavevalgrow_"+id+" :input").length) ;
         $( "#gavevalgrow_"+id+" :input").each(function( index, ele ) {
            if($(ele).get(0).tagName == "INPUT" && len > index ){
                attributes.push({"attribute_id":$(ele).attr("field-id"),"attribute_value":$(ele).val()})
            }
               //console.log(this.attr("field-id"))
         })
        $( "#gavevalgrow_"+id).find(".selectBoxElement").each(function( index ) {
            var feltKey = $( this ).attr('data');
            var feltVal = $( this ).find(":selected").text();
            attributes.push({"attribute_id":feltKey,"attribute_value":feltVal});

        });


        var tempUpdata = {};
        var sogData = "";
        for (i = 0; i < this.userDataDB.length; ++i) {
            if(this.userDataDB[i].id == id){
               for (j = 0; j < attributes.length; ++j) {
                    sogData+= attributes[j].attribute_value+"#";
                    this.userDataDB[i].data["id_"+attributes[j].attribute_id]  =  attributes[j].attribute_value;
                   this.userDataDB[i].sog = sogData
               }
            }
        }
        tempUpdata = {};
        for (i = 0; i < this.userDataDBCopy.length; ++i) {
            if(this.userDataDBCopy[i].id == id){
               for (j = 0; j < attributes.length; ++j) {
                    this.userDataDBCopy[i].data["id_"+attributes[j].attribute_id]  =  attributes[j].attribute_value;
               }
               this.userDataDBCopy[i].sog = sogData
            }
        }
        var skip_email = 0;

        if(_sendMailOnsave == false || _sendMail == false) {
            skip_email = 1;
        }



         var formData = {
             "attributes":JSON.stringify(attributes),
             "shop_id":_editShopID,
             "user_id":id,
             "skip_email":skip_email,
             "token":_token
            }

        ajax(formData,"order/updateShopUserCustomerPanel","gavevalg.updateUserDataResponse","");
    },
    updateUserDataResponse:function(response){
//this.userDataDB




        showSysMsg("Bruger opdateret")
    },
    deleteUser:function(id){
        if (confirm("Er du sikker p&aring; du vil slette!") == true) {
            $("#gavevalgrow_"+id).fadeOut(function(){
                $("#gavevalgrow_"+id).remove();
            });
            this.isDeleted+=id+"hej,"
            ajax({"user_id":id,"token":_token,"shop_id":_editShopID},"shop/kundepanelremoveUser","gavevalg.deleteUserResponse","");
        }
    },
    deleteUserResponse:function(response){
        showSysMsg("Bruger slettet")

    },
    downloadExcel:function(){

         window.open("../gavefabrikken_backend/index.php?rt=report/userReport&shop_id="+_editShopID+"&token="+_token);
    },
    downloadExcel2:function(){

         window.open("../gavefabrikken_backend/index.php?rt=report/manglerGaveValgRapport&shop_id="+_editShopID+"&token="+_token);
    },



    sendMail:function()
    {
         if (confirm("Er du sikker på du vil sende mails ud!") == true) {
            ajax({"shop_id":_editShopID,"token":_token},"external/sendMailsToUsersWithNoOrders2","gavevalg.sendMailResponse","");
        }

        //  ajax({"shop_id":_editShopID},"shop/sendMAil","","");
    },
    sendMailResponse:function(response){
       showSysMsg("Mails sendt")
    },

    showCreateNew:function()
    {
       $( "#gavevalgDialog" ).dialog({
            resizable: true,
            width:700,
            height:400,
            buttons: {
                    "Gem": function() {
                        gavevalg.doCreateUser();
                        $( this ).dialog( "close" );
                    },
                    Cancel: function() {
                        $( this ).dialog( "close" );
                    }
            }
        });
    },
    doCreateUser:function()
    {
                    var attributes = [];
                    $( "#gavevalgDialog :input").each(function( index, ele ) {
                        attributes.push({"id":$(ele).attr("data-id"),"value":$(ele).val()})
                    });
                      // console.log(attributes);

                      var formdata ={
                        "attributes_":JSON.stringify(attributes),
                        "data":JSON.stringify({"userId":null,"shopId":_editShopID,"companyId":this.companyId,"token":_token})
                    }
                    ajax(formdata,"shop/kundepanelAddSUser","gavevalg.doCreateUserResponse","");
    },
    doCreateUserResponse:function(response){


     //   console.log(response)
     //   console.log(this.userDataDB)
        var tempData = [];

       if(response.status == "1"){

            showSysMsg("Ny bruger oprettet")
         /*
        //gavevalg.addNewUserToTable(response);
         var tempHtml = "";


         var masterId  = response.data.shopuser[0].id;
         tempHtml+= '<tr id="gavevalgrow_'+masterId+'">';
         var f = this.newUserSelectFeild;
         jQuery.each(this.fieldIdOrder, function(index, item) {

            if(f["id_"+item] != undefined){
                tempHtml+= '<td><select data="'+item+'" class=\"selectBoxElement\" style=\" padding:2px;  \"><option selected  value=\"\">Vælg</option>';
                tempHtml+= f['id_'+item]+'</select></td>';
            } else {
                tempHtml+= '<td><input field-id="'+item+'" type="text" value=""></td>'
            }
            var key = "id_"+item;
            tempData.push({key:""});


        });
         this.userDataDB.push({data:tempData,id:masterId})
            this.userOrderDataDB["gift_"+masterId] = {"id":""};
         tempHtml+= '<td id="giftTitle_'+masterId+'">Ikke valgt gave</td> '
         tempHtml+= '<td id="giftModel_'+masterId+'"></td>'

         tempHtml+= '<td class="ba_admin">'
         tempHtml+= '   <img class="notShowKundepanel" width="20" height="20" src="views/media/icon/1373253284_save_64.png" title="Gem" onclick="gavevalg.updateUserData(\''+masterId+'\') ">'
        tempHtml+= '    <img width="23" height="23" title="Skift gave" class="notShowKundepanel" src="views/media/icon/gave.png" onclick="gavevalg.changeGiftShowMenu(\''+masterId+'\')">'
        tempHtml+= '    <img width="20" height="20" src="views/media/icon/PurchaseNoOrder-50.png" title="Ingen kvittering, ej valgt gave">'
         tempHtml+= '   <img width="20" class="notShowKundepanel" height="20" src="views/media/icon/1373253296_delete_64.png" title="Slet" onclick="gavevalg.deleteUser(\''+masterId+'\')"> '
        tempHtml+= ' </td> '
        tempHtml+= ' <td class="qr_admin" style="display: none;"></td></tr>'

        $(".gavevalg tr:first-child").after(tempHtml);
        jQuery.each(response.data.shopuser[0].user_attributes, function(index, item) {
            var  attribute_id = item.attributes.attribute_id;
            var attribute_value = item.attributes.attribute_value;
            $("#gavevalgrow_"+masterId).find("[field-id="+attribute_id+"]").val(attribute_value);

        })
        */
         gavevalg.init()

        } else {
            if(response.message == "dublet"){
                alert("Den bruger du vil oprette findes allerede i systemet. \nIngen bruger er blevet oprettet!")
            } else {
                alert("Der er opstået en fejl, tryk F5 og prøv igen")
            }

        }


    },
    printReceipt:function(id){
         ajax({"user_id":id,"token":_token,"shop_id":_editShopID},"order/kundepanelresend","gavevalg.printReceiptResponse","");
    },
    printReceiptResponse:function(response){
        showSysMsg("Kvittering sendt")
    },
    changeGiftShowMenu:function(id){
    //    console.log(id)
        this.changeGiftUserId = id;
    $( "#gavevalgSkiftGave" ).dialog({
        modal: true,

        width:800,
        buttons: {
        LUK: function() {
          $( this ).dialog( "close" );
        }
      }
    });
    },
    doChangeGift:function(presentsId,modelName,model,modelId,model_id){
        $( "#gavevalgSkiftGave" ).dialog( "close" );
        this.dataToPass = {giftTitle:modelName,giftModel:model};
         var skip_email = 0;
        if( _sendMail == false) {
            skip_email = 1;
        }
        var formdata = {
            "shopId":_editShopID,
            "userId":this.changeGiftUserId,
            "presentsId":presentsId,
            "modelName":modelName,
            "modelId":modelId,
            "model":model,
            "model_id":model_id,
            "skip_email":skip_email,
            "token":_token
        }
        ajax(formdata,"order/kundepanelcp","gavevalg.doChangeGiftResponse","");
    },
    doChangeGiftResponse:function(response)
    {


        if(response.status == "1"){
              showSysMsg("Ny gave valgt")
              $("#giftTitle_"+this.changeGiftUserId).html(this.dataToPass.giftTitle);
              $("#giftModel_"+this.changeGiftUserId).html(this.dataToPass.giftModel);
              this.userOrderDataDB["gift_"+this.changeGiftUserId].model = this.dataToPass.giftModel
              this.userOrderDataDB["gift_"+this.changeGiftUserId].name = this.dataToPass.giftTitle

                //gavevalg.init()



        } else {
            alert("Der er opstået en fejl! ")
        }
    },

    // new reg deliver
    newInitRegDelive:function(userId){
       if (confirm("Er du sikker paa du udlevere denne gave?") == true) {
            ajax({"user_id":userId},"order/getOrderNO","gavevalg.newDoRegDelive","");
            this.tempUserId = userId;
        }
    },
    newDoRegDelive:function(response){
        var orderID = response.data.orderno;
        ajax({"orderno":orderID},"registrer/doregister2","gavevalg.newDoRegDeliveResponse","");
    },
    newDoRegDeliveResponse:function(response){
        var html = "<img width=\"25\" height=\"25\" src=\"views/media/icon/delived.png\" id=\"delived_"+this.tempUserId+"\" title=\"Gave er udleveret\" onclick=\"gavevalg.newInitUnRegDelive('"+this.tempUserId+"') \" />";
        $("#delived_"+this.tempUserId).parent().html(html);

    },
    // new reg UNDO-deliver
    newInitUnRegDelive:function(userId){
       if (confirm("Vil du annullere  udleveringen") == true) {
            this.tempUserId = userId;
            ajax({"user_id":userId},"order/getOrderNO","gavevalg.newUndoRegDelive","");
        }
    },
    newUndoRegDelive:function(response){
        var orderID = response.data.orderno;
        ajax({"orderno":orderID},"registrer/dounregister2","gavevalg.newUndoRegDeliveResponse","");
    },
    newUndoRegDeliveResponse:function(response){
        var html = "<img width=\"25\" height=\"25\" src=\"views/media/icon/notdelived.png\" id=\"delived_"+this.tempUserId+"\" title=\"Gave ikke udleveret\" onclick=\"gavevalg.newInitRegDelive('"+this.tempUserId+"') \" />";

        $("#delived_"+this.tempUserId).parent().html(html);
    },

    qrRegDelive:function(userId){
         $("#delived_"+userId).attr("src","views/media/icon/delived.png");
        ajax({"user_id":userId},"order/RegisterOrder","gavevalg.qrRegDeliveResponse","");
    },
    qrRegDeliveResponse:function(response){
              showSysMsg("Gave registeret")
            notdelived
    },
    qrSendMail:function(){
        if (confirm("Er du sikker paa, du vil sende mails ud! \ntil dem der ikke har hentet deres gave") == true) {
              showSysMsg("Mail sendt")
            ajax({"shop_id":_editShopID},"shop/sendMailsToUsersHowHasNotPickedUpPresents","","");
        }


    },
    qrExcel:function(){
      //  alert("Funktionen er under opdatering")
       window.location = "../gavefabrikken_backend/index.php?rt=report/manglerGaveAfhentningRapport&shop_id="+_editShopID
    },
    qrDownloadLog:function(){
       window.location = "../gavefabrikken_backend/index.php?rt=report/qrlog&shop_id="+_editShopID
    }



}

var Base64 = {


    _keyStr: "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",


    encode: function(input) {
        var output = "";
        var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
        var i = 0;

        input = Base64._utf8_encode(input);

        while (i < input.length) {

            chr1 = input.charCodeAt(i++);
            chr2 = input.charCodeAt(i++);
            chr3 = input.charCodeAt(i++);

            enc1 = chr1 >> 2;
            enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
            enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
            enc4 = chr3 & 63;

            if (isNaN(chr2)) {
                enc3 = enc4 = 64;
            } else if (isNaN(chr3)) {
                enc4 = 64;
            }

            output = output + this._keyStr.charAt(enc1) + this._keyStr.charAt(enc2) + this._keyStr.charAt(enc3) + this._keyStr.charAt(enc4);

        }

        return output;
    },


    decode: function(input) {
        var output = "";
        var chr1, chr2, chr3;
        var enc1, enc2, enc3, enc4;
        var i = 0;

        input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");

        while (i < input.length) {

            enc1 = this._keyStr.indexOf(input.charAt(i++));
            enc2 = this._keyStr.indexOf(input.charAt(i++));
            enc3 = this._keyStr.indexOf(input.charAt(i++));
            enc4 = this._keyStr.indexOf(input.charAt(i++));

            chr1 = (enc1 << 2) | (enc2 >> 4);
            chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
            chr3 = ((enc3 & 3) << 6) | enc4;

            output = output + String.fromCharCode(chr1);

            if (enc3 != 64) {
                output = output + String.fromCharCode(chr2);
            }
            if (enc4 != 64) {
                output = output + String.fromCharCode(chr3);
            }

        }

        output = Base64._utf8_decode(output);

        return output;

    },

    _utf8_encode: function(string) {
        string = string.replace(/\r\n/g, "\n");
        var utftext = "";

        for (var n = 0; n < string.length; n++) {

            var c = string.charCodeAt(n);

            if (c < 128) {
                utftext += String.fromCharCode(c);
            }
            else if ((c > 127) && (c < 2048)) {
                utftext += String.fromCharCode((c >> 6) | 192);
                utftext += String.fromCharCode((c & 63) | 128);
            }
            else {
                utftext += String.fromCharCode((c >> 12) | 224);
                utftext += String.fromCharCode(((c >> 6) & 63) | 128);
                utftext += String.fromCharCode((c & 63) | 128);
            }

        }

        return utftext;
    },

    _utf8_decode: function(utftext) {
        var string = "";
        var i = 0;
        var c = c1 = c2 = 0;

        while (i < utftext.length) {

            c = utftext.charCodeAt(i);

            if (c < 128) {
                string += String.fromCharCode(c);
                i++;
            }
            else if ((c > 191) && (c < 224)) {
                c2 = utftext.charCodeAt(i + 1);
                string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
                i += 2;
            }
            else {
                c2 = utftext.charCodeAt(i + 1);
                c3 = utftext.charCodeAt(i + 2);
                string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
                i += 3;
            }

        }

        return string;
    }

}