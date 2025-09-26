var sm = {
    tabledata:[],
    updateCounter:0,
    numberOfItemToUpdate:0,
    falloverPresent:[],
    allGifts: [],
    activeGifts: [],
    inactiveGifts: [],
    originalResponse: null,
    sortAscending: true,
    currentSortField: 'present_name', // Nyt felt til at holde styr på hvilket felt der sorteres efter
    searchTerm: '', // Søgeterm
    originalSearchTerm: '', // Original søgeterm for visning
    searchFieldHadFocus: false, // Holder styr på om søgefeltet havde fokus
    cursorPosition: undefined, // Cursor position i søgefeltet
    searchTimeout: null, // For debouncing af søgning
    filteredActiveGifts: [], // Filtrerede aktive gaver
    filteredInactiveGifts: [], // Filtrerede ikke-aktive gaver

    load:function()
    {
        ajax({shop_id:_editShopID},"shop/getPresentStatsShop","sm.loadResponse");
    },

    calcReservation:function(){
        ajax({shop_id:_editShopID},"shop/getShopPresentsNew","sm.calcReservationResponse");
    },

    calcReservationResponse: async function (res){
        $("#storage-to-many").html("");
        let stop = false;
        res.data.forEach(function(present) {
            if(present.attributes.strength*1 == 0){
                stop = true;
            }
        });
        if(stop == true){
            alert("Ikke alle styrker er sat, der kan ikke udføres et foreslag!")
            return;
        }

        let calcRes =  await sm.doCalcReservation(res);
        let sum = 0;
        res.data.forEach(function(present) {
            calcRes[present.attributes.strength-1]
            let antal = sm.roundToNearest5(calcRes[present.attributes.strength-1]);
            sum+=antal
            if($("#model_"+present.attributes.model_id).val() == 0 || $("#model_"+present.attributes.model_id).val() == ""  ){
                $("#model_"+present.attributes.model_id).val(antal)
                $("#model_"+present.attributes.model_id).parent().parent().find( ".needToUpdata" ).show();
            } else {
                $("#suggestions_"+present.attributes.model_id).html(antal)
            }
        });
        let sold = $("#storage-present-sold").val();

        if( Math.round(sold*1.35) < Math.round(sum*1)  ){
            let tooMany = Math.round( Math.round(sum) - Math.round(sold*1.35));
            $("#storage-to-many").html("For mange er blevet reserveret, du må manuelt nedskrive med "+tooMany+" hvis du benytter dette forslag");
        }
        ajax({shop_id:_editShopID,amount:sold},"shop/setSold");
    },

    doCalcReservation: async function(res){
        return new Promise((resolve, reject) => {
            let  styrker = [];
            res.data.forEach(function(present) {
                styrker.push(present.attributes.strength);
            });
            let calcFre = sm.calculateFrequency(styrker)

            let sold = $("#storage-present-sold").val();
            if(sold == "" && sold==0){
                return;
            }
            let maxSold = Math.round(sold*1.3);
            $("#storage-max-res").val(maxSold);

            let co = sm.calculateOrder(calcFre,100);
            let resStrength1 = Math.round((maxSold * (co[0]/100 ) )/ calcFre[1] );
            let resStrength2 = Math.round((maxSold * (co[1]/100 ) )/ calcFre[2] );
            let resStrength3 = Math.round((maxSold * (co[2]/100 ) )/ calcFre[3] );
            resolve ([resStrength1, resStrength2, resStrength3]);
        });
    },

    calculateOrder:function(strength,presentSold){
        let quantity1 = strength[1];
        let quantity2 = strength[2];
        let quantity3 = strength[3];

        let sold = presentSold;
        let strength1 = 1;
        let strength2 = 2;
        let strength3 = 4;

        let totalStrengthUnits = strength1 * quantity1 + strength2 * quantity2 + strength3 * quantity3;

        let proportion1 = (strength1 * quantity1) / totalStrengthUnits;
        let proportion2 = (strength2 * quantity2) / totalStrengthUnits;
        let proportion3 = (strength3 * quantity3) / totalStrengthUnits;

        let orderStrength1 = Math.round(100 * proportion1);
        let orderStrength2 = Math.round(100 * proportion2);
        let orderStrength3 = Math.round(100 * proportion3);

        let totalOrder = orderStrength1 + orderStrength2 + orderStrength3;
        if (totalOrder > presentSold) {
            let excess = totalOrder - presentSold;
            if (orderStrength3 >= excess) {
                orderStrength3 -= excess;
            } else if (orderStrength2 >= excess) {
                orderStrength2 -= excess;
            } else {
                orderStrength1 -= excess;
            }
        } else if (totalOrder < presentSold) {
            let shortfall = presentSold - totalOrder;
            if (orderStrength3 + shortfall <= presentSold) {
                orderStrength3 += shortfall;
            } else if (orderStrength2 + shortfall <= presentSold) {
                orderStrength2 += shortfall;
            } else {
                orderStrength1 += shortfall;
            }
        }
        return [orderStrength1, orderStrength2, orderStrength3];
    },

    calculateFrequency:function (array){
        let frequency = {1:0,2:0,3:0};
        for (let i = 0; i < array.length; i++) {
            let num = array[i];
            if (frequency.hasOwnProperty(num)) {
                frequency[num]++;
            } else {
                frequency[num] = 1;
            }
        }
        return frequency;
    },

    roundToNearest5:function (number) {
        return Math.ceil(number / 5) * 5;
    },

    loadResponse:function(response){
        sm.tabledata = [];
        var is_active = 1;
        var problem = response.data.problem;
        var emailToMotify = response.data.email;
        console.log(problem)
        var emailToMotifyHtml = "";
        var problemHtml = "";
        if(problem != ""){  problemHtml = problem.join();  }
        if(emailToMotify == null ) { emailToMotify = ""; }

        emailToMotifyHtml = "<label><b>Notifikation email:</b> </label><input id=\"notifikation\" value='"+emailToMotify+"' type=\"text\" size=\"40\" />"

        // Gem den oprindelige response struktur
        sm.originalResponse = JSON.parse(JSON.stringify(response));
        response.data = response.data.data;

        // Nulstil arrays
        sm.allGifts = [];
        sm.activeGifts = [];
        sm.inactiveGifts = [];

        for (var key in response.data) {
            is_active = 1;

            // tjekker om gaven er lukket eller åben
            if(response.data[key].present_is_active == "1" || response.data[key].present_is_deletet == "1" ||  response.data[key].present_total_is_active == "0" ){
                is_active = 2;
            }
            if(response.data[key].present_total_is_deletet == "1"){
                is_active = 3;
            }

            var showFalloverIcon = response.data[key].reserved_quantity > 0 ? 1 : false;
            if(response.data[key].model_present_no == undefined) { response.data[key].model_present_no = ""; }
            if(response.data[key].model_present_name == undefined) { response.data[key].model_present_name = ""; }

            var data = {
                present_id: response.data[key].present_id,
                present_model_id: response.data[key].present_model_id,
                reservation_id: response.data[key].reservation_id,
                present_properties: response.data[key].present_properties,
                present_name: response.data[key].present_name,
                model_present_name: response.data[key].model_present_name
            }
            sm.tabledata.push(data);

            // Gem gave objektet med alle data
            var giftObj = {
                key: key,
                data: response.data[key],
                is_active: is_active,
                showFalloverIcon: showFalloverIcon,
                tableData: data
            };

            sm.allGifts.push(giftObj);

            if(is_active == 1) {
                sm.activeGifts.push(giftObj);
            } else if(is_active == 2) {
                sm.inactiveGifts.push(giftObj);
            }
        }

        // Initial filtering og sortering
        sm.filterAndSortGifts();

        // Byg HTML
        sm.buildFullHTML();

        system.endWork();
        if(typeof present !== 'undefined' && present.attributes && $("#model_"+present.attributes.model_id).val() == 0 || $("#model_"+present.attributes.model_id).val() == ""  ){
            ajax({shop_id:_editShopID},"shop/getSold","sm.getSoldRes");
        }
    },

    buildFullHTML: function() {
        var problem = sm.originalResponse.data.problem;
        var emailToMotify = sm.originalResponse.data.email;
        var emailToMotifyHtml = "";
        var problemHtml = "";

        if(problem != ""){  problemHtml = problem.join();  }
        if(emailToMotify == null ) { emailToMotify = ""; }

        emailToMotifyHtml = "<label><b>Notifikation email:</b> </label><input id=\"notifikation\" value='"+emailToMotify+"' type=\"text\" size=\"40\" />"

        var html = "<tr><td colspan=2>"+problemHtml+"</td><td colspan=5 style=\"text-align:center;\">"+emailToMotifyHtml+"</td><td colspan=4 ><button style=\"background-color: #f44336; color:white;\" type=\"button\" onclick=\"sm.initUpdate()\">Opdatere alle felter</button></td></tr>";

        // Tilføj søgefelt
        html += "<tr><td colspan=11 style='text-align:center; padding:10px;'>";
        html += "<label><b>Søg i Gave, Model eller Varenr:</b> </label>";
        html += "<input id='searchField' type='text' placeholder='Indtast søgeterm...' style='width:300px; padding:5px;' onkeyup='sm.search(this.value)' oninput='sm.search(this.value)' value='" + sm.originalSearchTerm + "' />";
        html += " <button onclick='sm.clearSearch()' style='margin-left:10px;'>Ryd søgning</button>";
        html += "</td></tr>";

        // Tilføj sorterbar header med alle tre felter
        var gaveSort = sm.currentSortField === 'present_name' ? (sm.sortAscending ? "↓" : "↑") : "";
        var modelSort = sm.currentSortField === 'model_present_name' ? (sm.sortAscending ? "↓" : "↑") : "";
        var varenrSort = sm.currentSortField === 'model_present_no' ? (sm.sortAscending ? "↓" : "↑") : "";

        html += "<tr>";
        html += "<th width=70 style='cursor:pointer; background-color: #e0e0e0;' onclick='sm.toggleSort(\"present_name\")' title='Klik for at sortere'>Gave " + gaveSort + "</th>";
        html += "<th width=70 style='cursor:pointer; background-color: #e0e0e0;' onclick='sm.toggleSort(\"model_present_name\")' title='Klik for at sortere'>Model " + modelSort + "</th>";
        html += "<th width=50 style='cursor:pointer; background-color: #e0e0e0;' onclick='sm.toggleSort(\"model_present_no\")' title='Klik for at sortere'>Varenr " + varenrSort + "</th>";
        html += "<th width=20>Antal valgte</th>";
        html += "<th width=20>Antal reserveret</th>";
        html += "<th width=20>Advarsel</th>";
        html += "<th width=20></th>";
        html += "<th width=90>Erstatningsgave</th>";
        html += "<th width=20>Luk</th>";
        html += "<th width=20></th>";
        html += "<th width=40>Autopilot</th>";
        html += "</tr>";

        html += "<tr><td valign=center colspan=11 style=\" text-align: center;background-color: #FFFF00;\">AKTIVE GAVER " + (sm.filteredActiveGifts.length < sm.activeGifts.length ? "(" + sm.filteredActiveGifts.length + " af " + sm.activeGifts.length + ")" : "") + "</td></tr>";

        // Byg HTML for filtrerede aktive gaver
        sm.filteredActiveGifts.forEach(function(gift) {
            html += sm.buildGiftRow(gift);
        });

        // Tilføj ikke-aktive gaver sektion
        html += "<tr><td valign=center colspan=11 style=\" text-align: center;background-color: #FFFF00;\"><span style=\"color:red;\">IKKE</span> AKTIVE GAVER " + (sm.filteredInactiveGifts.length < sm.inactiveGifts.length ? "(" + sm.filteredInactiveGifts.length + " af " + sm.inactiveGifts.length + ")" : "") + "</td></tr>";

        // Byg HTML for filtrerede ikke-aktive gaver
        sm.filteredInactiveGifts.forEach(function(gift) {
            html += sm.buildGiftRow(gift);
        });

        $("#storageMonitoring").html(html);

        // Genopret fokus på søgefeltet hvis det var aktivt
        if (sm.searchFieldHadFocus) {
            setTimeout(function() {
                var searchField = document.getElementById('searchField');
                if (searchField) {
                    searchField.focus();
                    // Sæt cursor position til slutningen eller til gemt position
                    var pos = sm.cursorPosition !== undefined ? sm.cursorPosition : sm.originalSearchTerm.length;
                    try {
                        searchField.setSelectionRange(pos, pos);
                    } catch(e) {
                        // Fallback hvis setSelectionRange ikke virker
                        searchField.value = sm.originalSearchTerm;
                    }
                }
            }, 10); // Kort delay for at sikre DOM er klar
        }

        sm.updateWarning();
    },

    buildGiftRow: function(gift) {
        var key = gift.key;
        var data = gift.data;
        var html = "";

        html += "<tr class='storageMonitoringData' data='" + key + "' id='rowid_" + key + "'>";
        html += "<td>" + data.present_name + "</td>";
        html += "<td class=\"resName\">" + data.model_present_name + "</td>";
        html += "<td>" + data.model_present_no + "</td>";
        html += "<td class=\"smOrder\">" + data.order_count + "</td>";
        html += "<td><input style=\"width:50px;\" id='model_" + data.present_model_id + "' class=\"smQuantity\" onchange=\"sm.showUpdateBtn(this)\" value='" + data.reserved_quantity + "' /><br><div style='color: red;' id='suggestions_" + data.present_model_id + "'></div></td>";
        html += "<td><input style=\"width:50px;\" class=\"smWarning\" onchange=\"sm.showUpdateBtn(this)\" value='" + data.warning_level + "' /></td>";

        var isCheck = "";
        if(data.do_close == 1) {
            isCheck = "checked";
        }
        var hasAutopilot = data.autotopilot == 1 ? "checked" : "";

        if(gift.is_active == 1) {
            if(gift.showFalloverIcon == true) {
                html += "<td><div class=\"needToUpdata\" style=\"color:red; display:none; font-size:16px;\"><b>!</b></div></td>";
                html += "<td>" + (data.replacement_present_name || "") + "</td>";
                html += "<td><input class=\"luk\" " + isCheck + " type=\"checkbox\" onchange=\"sm.showUpdateBtn(this)\" /></td>";
                html += "<td><img style=\"cursor:pointer;\" width=\"23\" height=\"23\" title=\"fallover gave\" src=\"views/media/icon/gave.png\" onclick=\"sm.showFalloverModal('" + data.present_id + "','" + data.present_model_id + "','" + data.reservation_id + "')\"></td>";
                html += "<td><input class=\"autopilot\" " + hasAutopilot + " type=\"checkbox\" onchange=\"sm.showUpdateBtn(this)\" /></td>";
            } else {
                html += "<td><div class=\"needToUpdata\" style=\"color:red; display:none; font-size:16px;\"><b>!</b></div></td>";
                html += "<td>" + (data.replacement_present_name || "") + "</td>";
                html += "<td><input class=\"luk\" " + isCheck + " type=\"checkbox\" onchange=\"sm.showUpdateBtn(this)\" /></td>";
                html += "<td><img width=\"23\" height=\"23\" title=\"fallover gave\" src=\"views/media/icon/1373253314_present_64.png\" /></td>";
                html += "<td><input class=\"autopilot\" " + hasAutopilot + " type=\"checkbox\" onchange=\"sm.showUpdateBtn(this)\" /></td>";
            }
        } else {
            html += "<td></td><td></td><td></td><td></td><td></td>";
        }

        html += "</tr>";
        return html;
    },

    // Opdateret toggleSort funktion til at håndtere forskellige felter
    toggleSort: function(field) {
        // Hvis det samme felt klikkes, skift retning
        if (sm.currentSortField === field) {
            sm.sortAscending = !sm.sortAscending;
        } else {
            // Hvis nyt felt klikkes, sæt til stigende og skift felt
            sm.currentSortField = field;
            sm.sortAscending = true;
        }
        sm.filterAndSortGifts();
    },

    // Opdateret funktion til at sortere både aktive og ikke-aktive gaver
    sortGifts: function() {
        var sortFunction = function(a, b) {
            var valueA, valueB;

            // Få værdier baseret på det valgte felt
            switch(sm.currentSortField) {
                case 'present_name':
                    valueA = a.data.present_name || "";
                    valueB = b.data.present_name || "";
                    break;
                case 'model_present_name':
                    valueA = a.data.model_present_name || "";
                    valueB = b.data.model_present_name || "";
                    break;
                case 'model_present_no':
                    valueA = a.data.model_present_no || "";
                    valueB = b.data.model_present_no || "";
                    break;
                default:
                    valueA = a.data.present_name || "";
                    valueB = b.data.present_name || "";
            }

            // Sorter baseret på retning
            if(sm.sortAscending) {
                return valueA.toString().localeCompare(valueB.toString(), 'da-DK');
            } else {
                return valueB.toString().localeCompare(valueA.toString(), 'da-DK');
            }
        };

        // Sorter begge arrays separat
        sm.activeGifts.sort(sortFunction);
        sm.inactiveGifts.sort(sortFunction);

        // Opdater også de filtrerede arrays
        sm.filterAndSortGifts();
    },

    // Bevar den gamle sortActiveGifts funktion for bagudkompatibilitet
    sortActiveGifts: function() {
        sm.currentSortField = 'present_name';
        sm.filterAndSortGifts();
    },

    // Søgefunktion med debouncing
    search: function(searchTerm) {
        // Gem fokus status og cursor position
        var searchField = document.getElementById('searchField');
        if (searchField) {
            sm.searchFieldHadFocus = (document.activeElement === searchField);
            sm.cursorPosition = searchField.selectionStart;
        }

        sm.originalSearchTerm = searchTerm; // Gem original for visning
        sm.searchTerm = searchTerm.toLowerCase().trim(); // Processeret version til søgning

        // Clear existing timeout
        if (sm.searchTimeout) {
            clearTimeout(sm.searchTimeout);
        }

        // Debounce søgning for at undgå for mange rebuilds
        sm.searchTimeout = setTimeout(function() {
            sm.filterAndSortGifts();
        }, 100); // 100ms delay
    },

    // Ryd søgning
    clearSearch: function() {
        if (sm.searchTimeout) {
            clearTimeout(sm.searchTimeout);
        }
        sm.searchTerm = '';
        sm.originalSearchTerm = '';
        sm.searchFieldHadFocus = true; // Bevar fokus efter clear
        sm.filterAndSortGifts();
    },

    // Filtrer gaver baseret på søgeterm
    filterGifts: function(gifts) {
        if (!sm.searchTerm) {
            return gifts; // Hvis ingen søgeterm, returner alle
        }

        return gifts.filter(function(gift) {
            var presentName = (gift.data.present_name || "").toLowerCase();
            var modelName = (gift.data.model_present_name || "").toLowerCase();
            var modelNo = (gift.data.model_present_no || "").toLowerCase();

            return presentName.includes(sm.searchTerm) ||
                modelName.includes(sm.searchTerm) ||
                modelNo.includes(sm.searchTerm);
        });
    },

    // Kombiner filtrering og sortering
    filterAndSortGifts: function() {
        var sortFunction = function(a, b) {
            var valueA, valueB;

            // Få værdier baseret på det valgte felt
            switch(sm.currentSortField) {
                case 'present_name':
                    valueA = a.data.present_name || "";
                    valueB = b.data.present_name || "";
                    break;
                case 'model_present_name':
                    valueA = a.data.model_present_name || "";
                    valueB = b.data.model_present_name || "";
                    break;
                case 'model_present_no':
                    valueA = a.data.model_present_no || "";
                    valueB = b.data.model_present_no || "";
                    break;
                default:
                    valueA = a.data.present_name || "";
                    valueB = b.data.present_name || "";
            }

            // Sorter baseret på retning
            if(sm.sortAscending) {
                return valueA.toString().localeCompare(valueB.toString(), 'da-DK');
            } else {
                return valueB.toString().localeCompare(valueA.toString(), 'da-DK');
            }
        };

        // Filtrer først, derefter sorter
        sm.filteredActiveGifts = sm.filterGifts(sm.activeGifts);
        sm.filteredInactiveGifts = sm.filterGifts(sm.inactiveGifts);

        sm.filteredActiveGifts.sort(sortFunction);
        sm.filteredInactiveGifts.sort(sortFunction);

        // Genopbyg HTML med den filtrerede og sorterede data
        sm.buildFullHTML();
    },

    getSoldRes:function(res){
        $("#storage-present-sold").val(res.data[0].sold);
    },

    showUpdateBtn:function(obj){
        $(obj).parent().parent().find( ".needToUpdata" ).show();
    },

    initUpdate:function(){
        system.work();
        sm.updateCounter = 0;
        sm.numberOfItemToUpdate =  $( ".storageMonitoringData" ).length;
        console.log(sm.numberOfItemToUpdate)
        sm.updateQueueController();
    },

    updateQueueController:function(){
        if(sm.numberOfItemToUpdate >  sm.updateCounter){
            var itemNumber = $( ".storageMonitoringData" ).eq( sm.updateCounter ).attr("data") ;
            var do_close = "";
            let autotopilot = "";
            var rowData =  sm.tabledata[itemNumber];
            var rowObj  =  $("#rowid_"+itemNumber);

            var quantity = rowObj.find(".smQuantity").val();
            var warning_level = rowObj.find(".smWarning").val();
            var do_closeObj =  rowObj.find(".luk")
            var do_autopilotObj =  rowObj.find(".autopilot")
            console.log(rowObj)
            $(do_closeObj).is(':checked') ? do_close = "1" : do_close = "0";
            $(do_autopilotObj).is(':checked') ? autotopilot = "1" : autotopilot = "0";

            console.log(rowData)
            if($(rowObj).find(".needToUpdata").css('display') !== 'none') {
                sm.update(quantity,warning_level,rowData.present_id,rowData.present_model_id,rowData.reservation_id,do_close,autotopilot)
            } else {
                sm.updateResponse();
            }
        } else {
            var formdata = {
                "id":_editShopID,
                "rapport_email":$("#notifikation").val()
            }
            ajax(formdata,"shop/updateEmailNotification","sm.updateEmailNotificationResponse");
        }
    },

    updateEmailNotificationResponse:function(){
        sm.load();
    },

    update:function(quantity,warning_level,present_id,present_model_id,reservation_id,do_close,autotopilot){
        var formdata = {
            "shop_id":_editShopID,
            "present_id" : present_id,
            "model_id" : present_model_id,
            "warning_level" : warning_level,
            "quantity" : quantity,
            "do_close" : do_close,
            "autotopilot":autotopilot
        }
        if(reservation_id == ""){
            ajax(formdata,"reservation/saveReservation","sm.updateResponse");
        } else {
            formdata.id = reservation_id;
            ajax(formdata,"reservation/saveReservation","sm.updateResponse");
        }
    },

    updateResponse:function(){
        sm.updateCounter++;
        sm.updateQueueController();
    },

    updateWarning:function()
    {
        $( "#storageMonitoring tr" ).each(function( index ) {
            var reserved =  $( this ).find(".smQuantity").val();
            var warningLevel = $( this ).find(".smWarning").val();
            var orderCount = $( this ).find(".smOrder").html();
            var warningColorRed = warningLevel / 100;
            $(this).css("background-color", "white");

            if(parseInt(reserved) != 0){
                if( (parseInt(warningLevel) + parseInt(orderCount) ) > parseInt(reserved)   ){
                    $(this).css("background-color", "#f4a688");
                }
            }
        });
    },

    chekIfActive:function(target_present_id, target_present_model_id){
    },

    showFalloverModal:function(target_present_id, target_present_model_id,id) {
        var html = "<table width=100% border=1><tr><th>Gavenavn</th><th></th></tr>";
        var doShow = true;
        var presentIsInList = [];
        html+="<tr><td>Nulstil erstatningsgave</td><td><input type=\"button\"  value=\"Benyt\"  onclick=\"sm.regFalloverPresent('"+target_present_id+"','"+target_present_model_id+"','0','','"+id+"')\"></td><td>Autopilot</td></tr>";
        for (var key in sm.tabledata) {
            doShow = true;
            if(sm.tabledata[key].present_properties == null){
                doShow = false;
            } else {
                var option = sm.tabledata[key].present_properties;
                option =  jQuery.parseJSON(option);
                var variantList = option.variantListOption;
                var variantListOptionArr = variantList.toString().split(",");
                if(option.aktivOption == true){
                    doShow = false;
                }
            }

            if(doShow == true && presentIsInList.indexOf(sm.tabledata[key].present_id.toString()) == -1){
                html+="<tr><td>"+sm.tabledata[key].present_name+"</td><td><input type=\"button\"  value=\"Benyt\"  onclick=\"sm.regFalloverPresent('"+target_present_id+"','"+target_present_model_id+"','"+sm.tabledata[key].present_id+"','"+sm.tabledata[key].present_name+"','"+id+"')\"></td></tr>";
            }
            presentIsInList.push(sm.tabledata[key].present_id.toString());
        }
        html+= "</table>";
        showModal(html, "Erstatningsgave",500,400)
    },

    regFalloverPresent:function(target_present_id,target_present_model_id,present_id,replacement_present_name,id){
        var formdata = {
            "id":id,
            "shop_id":_editShopID,
            "present_id" : target_present_id,
            "model_id" : target_present_model_id,
            "replacement_present_id" : present_id,
            "replacement_present_name" : replacement_present_name
        }

        ajax(formdata,"reservation/saveReservation","sm.regFalloverPresentResponse");
    },

    regFalloverPresentResponse:function(response){
        if(response.status == "1"){
            closeMedal();
            sm.load();
        }
    }
}