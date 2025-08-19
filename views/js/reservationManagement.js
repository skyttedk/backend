var valgshopsSidebar = (function () {
    var self = this;
    var isAllAutoSelected = false;

    self.init = function() {
        console.log("Initializing valgshopsSidebar");
        self.createSidebar();
        self.bindEvents();

    };

    self.createSidebar = function() {
        console.log("Creating sidebar");
        var sidebarHTML = `
        <div id="valgshopsSidebar" class="sidebar">
            <button id="toggleValgshopsSidebar" class="toggle-btn">☰</button>
            <div class="sidebar-content">
                <h2>VALGSHOPS Menu</h2>
                <div class="search-container">
                    <input type="text" id="shopSearch" placeholder="Søg efter shop...">
                    <button id="searchShopBtn">Søg</button>
                    
                </div>
                <button id="searchShopBoardBtn">Shopboard</button>
                <ul id="searchResults"></ul>
                <button id="toggleAllAutoBtn" class="valgshop-action-btn">Vælg alle AUTO checkboxes</button>
            </div>
        </div>
    `;
        $('#tabs-2').prepend(sidebarHTML);
    };

    self.bindEvents = function() {
        console.log("Binding events");
        $('#toggleValgshopsSidebar').on('click', self.toggleSidebar);
        $('#toggleAllAutoBtn').on('click', self.toggleAllAuto);
        $('#searchShopBtn').on('click', self.searchShops);
        $('#searchShopBoardBtn').on('click', self.searchShopBoard);
        $('#shopSearch').on('keypress', function(e) {
            if (e.which == 13) {
                self.searchShops();
            }
        });



        $('#tabs-main').on('tabsactivate', function(event, ui) {
            console.log("Tab activated:", ui.newPanel.attr('id'));
            if (ui.newPanel.attr('id') === 'tabs-2') {
                $('#valgshopsSidebar').show();
            } else {
                $('#valgshopsSidebar').hide();
            }
        });

        $(document).on('click', '#searchResults li', function() {
            var shopId = $(this).data('id');
            $(this).addClass('selected').siblings().removeClass('selected');
            RM.getValgshop(shopId);
        });

        $('#selectAllAutoBtn').on('click', self.selectAllAuto);


    };

    self.toggleSidebar = function() {
        console.log("Toggling sidebar");
        $('#valgshopsSidebar').toggleClass('open');
        $('#tabs-2').toggleClass('sidebar-open');
        self.adjustTableWidth();

        if (!$('#valgshopsSidebar').hasClass('open')) {
            self.clearSearchResults();
        }

    };

    self.adjustTableWidth = function() {
        if ($('#tabs-2').hasClass('sidebar-open')) {
            $('#tabs-2 .dataTables_scrollBody').css('width', 'calc(100% - 250px)');
        } else {
            $('#tabs-2 .dataTables_scrollBody').css('width', '100%');
        }
        if ($.fn.dataTable.isDataTable('#cardshopData')) {
            $('#cardshopData').DataTable().columns.adjust();
        }
    };

    self.searchShopBoard = function() {
        $.ajax({
            url: _ajaxPath + "searchShopBoard",
            method: 'POST',
            data: {},
            dataType: 'json',
            success: function(response) {
                if (response.status === "1") {
                    self.displaySearchResults(response.data);
                } else {
                    console.error("Error searching shops:", response.message);
                    $('#searchResults').html('<li>Fejl ved søgning. Prøv igen senere.</li>');
                }
            },
            error: function(xhr, status, error) {
                console.error("Error searching shops:", error);
                $('#searchResults').html('<li>Fejl ved søgning. Prøv igen senere.</li>');
            }
        });
    };

    self.searchShops = function() {
        var searchTerm = $('#shopSearch').val().trim();
        if (searchTerm === '') return;

        $.ajax({
            url: _ajaxPath + "searchShops",
            method: 'POST',
            data: { search: searchTerm },
            dataType: 'json',
            success: function(response) {
                if (response.status === "1") {
                    self.displaySearchResults(response.data);
                } else {
                    console.error("Error searching shops:", response.message);
                    $('#searchResults').html('<li>Fejl ved søgning. Prøv igen senere.</li>');
                }
            },
            error: function(xhr, status, error) {
                console.error("Error searching shops:", error);
                $('#searchResults').html('<li>Fejl ved søgning. Prøv igen senere.</li>');
            }
        });
    };

    self.displaySearchResults = function(results) {
        var $resultsList = $('#searchResults');
        $resultsList.empty();

        // Add the count of results at the top
        var countMessage = results.length === 0 ? 'Ingen resultater fundet' :
            results.length === 1 ? '1 shop fundet' :
                results.length + ' shops fundet';
        $resultsList.append('<li class="result-count">' + countMessage + '</li>');

        if (results.length === 0) {
            $resultsList.append('<li>Ingen resultater fundet</li>');
        } else {
            results.forEach(function(shopData) {
                var shop = shopData.attributes;
                $resultsList.append('<li data-id="' + shop.id + '">' + shop.name + '</li>');
            });
        }
    };

    self.clearSearchResults = function() {
        $('#searchResults').empty();
        $('#shopSearch').val('');
    };

    self.selectAllAuto = function() {
        $('.autoCheckbox').prop('checked', true).change();
        // Trigger any necessary events or updates after checking all AUTO checkboxes
        // For example:
        // RM.updateAutoStatus();
    };
    self.toggleAllAuto = function() {
        var confirmMessage = isAllAutoSelected
            ? "Er du sikker på, at du vil ændre alle AUTO checkboxes?"
            : "Er du sikker på, at du vil ændre alle AUTO checkboxes?";

        if (confirm(confirmMessage)) {
            isAllAutoSelected = !isAllAutoSelected;
            $('.autoCheckbox').prop('checked', isAllAutoSelected).change();
            self.updateToggleButtonText();
            // Trigger any necessary events or updates after changing AUTO checkboxes
            // For example: RM.updateAutoStatus();
        }
    };

    self.updateToggleButtonText = function() {
        var buttonText = isAllAutoSelected
            ? "Ændre AUTO checkboxes"
            : "Ændre AUTO checkboxes";
        $('#toggleAllAutoBtn').text(buttonText);
    };
    return self;
})();

$(document).ready(function() {
    console.log("Document ready");
    valgshopsSidebar.init();
});

var rm = (function () {
    var self = this;
    self.currentLang = '1';
    self.data = [];
    self.searchHTML = "";

    self.init = () => {

    }
    self.initSetEvent = () => {


    }


    self.initCardshop = () => {
        let self = this;
        $("#cardshopOverview").html("");

        /*
        let html = self.cardshopTemplate();
        $("#cardshopDataContainer").html(html);
        $("#loadCardshopBtn").unbind("click").click(()=> {
            self.getCardshop();
        })
        */

        $("#searchItemBtn").unbind("click").click(()=> {
            let itemNr = $("#searchItem").val();
            self.searchItemNrInModal(itemNr);
        })
        $(".doUpdate").unbind("click").click(()=> {
            self.update("cardshop-uprdate");
        })

        $('#languageSwitch').click(function() {

            if (self.currentLang === '1') {
                self.currentLang = '4';
                $(this).text('Bytt til dansk');
                $('html').attr('lang', '4');
            } else {
                self.currentLang = '1';
                $(this).text('Skift til norsk');
                $('html').attr('lang', '1');
            }
            loadCardshop()
        });


    }

    self.filterStatusValgshop = async () => {

        var filterActive = false;
        $(".itemRecord").hide();

        if($('#showWarningValgshop').is(":checked")){
            $(".expired").show();
            filterActive = true;
        }
        if($('#showExpiredValgshop').is(":checked")){
            $(".warning").show();
            filterActive = true;
        }
        if(filterActive == false){
            $(".itemRecord").show();
        }
    }

    self.filterStatus = async () => {

        var filterActive = false;
        $(".itemRecord").hide();

        if($('#showWarning').is(":checked")){
            $(".expired").show();
            filterActive = true;
        }
        if($('#showExpired').is(":checked")){
            $(".warning").show();
            filterActive = true;
        }
        if($('#showNoRes').is(":checked")){

            $(".nores").show();
            filterActive = true;

        }
        if(filterActive == false){
            $(".itemRecord").show();
        }
    }
    self.searchItemNr = async () => {
        $(".searchStatus").attr("disabled", true);
        $(".doUpdate").hide();
        let itemNr = $("#searchItem").val();
        let data = await self.doSearchItemNr(itemNr);
        let html = self.buildItemsearchTable(data.data);
        let overview= self.buildItemsearchTotalStats(data.data);

        $("#cardshopOverview").html(overview)
        $("#cardshopDataContainer").html(html);

        $('#itemSearchTable').DataTable({
            "scrollY": "calc(100vh - 280px)",
            "scrollCollapse": true,
            "paging": false,
            dom: 'Bfrtip',
            columnDefs: [
                {
                    targets: [5, 6, 7, 8], // R-tal, Antal valgte, forecast, Andel % columns
                    type: 'num-fmt',
                    render: function(data, type, row) {
                        if (type === 'sort') {
                            if (data === null || data === undefined || data === '') {
                                return 0;
                            }
                            return parseFloat(('' + data).replace(/[^\d.-]/g, '').replace(',', '.')) || 0;
                        }
                        return data;
                    }
                }
            ],
            language: {
                decimal: ",",
                thousands: "."
            },
            order: [[6, 'desc']] // Sort by R-tal column (index 6) in descending order by default
        });
        $(".doUpdateSearchItem").unbind("click").click(function(){
            self.update("SearchTable-uprdate");
        })
        $(".setCloseOpenAll").unbind("click").click(function(){
            self.setCloseOpenAll();
        })



        $('.quantityItemSearchTable').unbind().on('input', function() {
            $(this).attr("org-quantity") != $(this).val() ? $(this).addClass("markChanges") : $(this).removeClass("markChanges")
            $(this).attr("org-quantity") != $(this).val() ? $(this).addClass("SearchTable-uprdate") : $(this).removeClass("SearchTable-uprdate")

        });


    }


    self.doSearchItemNr = (itemNr) => {
        return new Promise(async resolve => {
           $.post(_ajaxPath+"searchItemNr",{itemNr:itemNr}, function(data, textStatus) {
                resolve(data);
            },'json');
        });
    }


    self.getCardshop = async () => {
        self.resetTabsContainer();
        $("#valgshopDataContainer").html("Systemet arbejder");
        $(".doUpdate").show();
        let cardshopData = await self.doGetCardshop();
        let html = self.buildCardshopTable(cardshopData.data);
        $("#cardshopDataContainer").html(html);
        $('#cardshopData').DataTable({
            "scrollY":        "calc(100vh - 280px)",
            "scrollCollapse": true,
            "paging":         false,
            dom: 'Bfrtip',
        })
        const concepts = [...new Set(cardshopData.data.map(item => item.attributes.concept_code))];
        $('#conceptFilter').html('<option value="">Alle</option>' + concepts.map(c => `<option value="${c}">${c}</option>`).join(''));
        $('#conceptFilter').on('change', function() {
            const concept = $(this).val();
            $('#cardshopData').DataTable().column(0).search(concept ? '^' + concept + '$' : '', true, false).draw();
        });
        $(".searchItemTable").unbind("click").click(function(){
            let itemNr = $(this).attr("data-id")
            self.searchItemNrInModal(itemNr);
        })
        $('.quantityCardshopTable').unbind().on('input', function() {
            $(this).attr("org-quantity") != $(this).val() ? $(this).addClass("markChanges") : $(this).removeClass("markChanges")
            $(this).attr("org-quantity") != $(this).val() ? $(this).addClass("cardshop-uprdate") : $(this).removeClass("cardshop-uprdate")
        });
        self.calcExpiredStatus("quantityCardshopTable");
        $(".searchStatus").removeAttr("disabled");
        $("#showWarning").unbind("click").click(()=> {
            self.filterStatus();
        })
        $("#showExpired").unbind("click").click(()=> {
            self.filterStatus();
        })
        $("#showNoRes").unbind("click").click(()=> {
            self.filterStatus();
        })
    }
    self.getValgshop = async (shopID) => {
        self.resetTabsContainer();
        $("#valgshopDataContainer").html("Systemet arbejder");
        $(".doUpdate").show();
        let valgshopData = await self.doGetValgshop(shopID);
        let html = self.buildValgshopTable(valgshopData.data);
        $("#valgshopDataContainer").html(html);
        $('#cardshopData').DataTable({
            "scrollY":        "calc(100vh - 280px)",
            "scrollCollapse": true,
            "paging":         false,
            dom: 'Bfrtip',
        })
        $(".searchItemTable").unbind("click").click(function(){
            let itemNr = $(this).attr("data-id")
            self.searchItemNrInModal(itemNr);
        })
        $('.quantityCardshopTable').unbind().on('input', function() {
            $(this).attr("org-quantity") != $(this).val() ? $(this).addClass("markChanges") : $(this).removeClass("markChanges")
            $(this).attr("org-quantity") != $(this).val() ? $(this).addClass("cardshop-uprdate") : $(this).removeClass("cardshop-uprdate")
        });
        self.calcExpiredStatus("quantityCardshopTable");
        $(".searchStatus").removeAttr("disabled");
        $("#showWarningValgshop").unbind("click").click(()=> {
            self.filterStatusValgshop();
        })
        $("#showExpiredValgshop").unbind("click").click(()=> {
            self.filterStatusValgshop();
        })
        $(".autoCheckbox").unbind("change").change( async function() {
            const prId = $(this).attr("data-pr-id");
            const isChecked = $(this).prop("checked") ? 1 : 0;
            let data = {
                id:prId,
                autopilot:isChecked
            }
            self.doUpdateModelData(data);
        });
        $(".autoLockCheckbox").unbind("change").change( async function() {
            const prId = $(this).attr("data-pr-id");
            const id = $(this).attr("pr_id");

            const isChecked = $(this).prop("checked") ? 1 : 0;
            let data = {
                id:prId,
                autopilot_lock:isChecked,
                autopilot:0
            }

            $(`#auto_`+id).prop("checked", false);
            self.doUpdateModelData(data);
        });

    }
    self.doUpdateModelData = (data) => {
            $.post(_ajaxPath+"doUpdateModelData",data, function(response) {
                if (response.status === "1") {
                    self.showMessage("Modeldata blev opdateret succesfuldt.", self.MessageType.SUCCESS);
                } else {
                    let errorMessage = "Der opstod en fejl under opdatering af modeldata.";
                    if (response.message) {
                        errorMessage += " " + response.message;
                    }
                    self.showMessage(errorMessage, self.MessageType.ERROR);
                    reject(new Error(errorMessage));
                }

            },'json');

    }
    self.MessageType = {
        SUCCESS: 'success',
        ERROR: 'error',
        WARNING: 'warning',
        INFO: 'info'
    };
    let toastTimeout;
    let messageCount = 0;
    self.showMessage = (message, type = self.MessageType.INFO) => {
        const toastContainer = $('#simpleToast');
        const toastMessage = toastContainer.find('.toast-message');
        const toastText = toastMessage.find('.toast-text');
        const toastCounter = toastMessage.find('.toast-counter');

        clearTimeout(toastTimeout);

        if (toastMessage.hasClass('show') && toastText.text() === message) {
            messageCount++;
            toastCounter.text(messageCount > 1 ? messageCount : '');
        } else {
            messageCount = 1;
            toastText.text(message);
            toastCounter.text('');
            toastMessage.removeClass().addClass('toast-message ' + type);
        }

        toastMessage.addClass('show');

        toastTimeout = setTimeout(() => {
            toastMessage.removeClass('show');
            setTimeout(() => {
                if (!toastMessage.hasClass('show')) {
                    toastText.text('');
                    toastCounter.text('');
                    messageCount = 0;
                }
            }, 300);
        }, 3000);
    };

    self.getExceeded = async () => {
        self.resetTabsContainer();
        $("#exceededDataContainer").html("Systemet arbejder");
        $(".doUpdate").show();
        let valgshopData = await self.doGetExceeded();
        let l = valgshopData["data"].length-1;
        $("#exceededDataFrom").html("Data er fra: "+valgshopData["data"][l].attributes.updated_at);
        let jobState = await self.getJobStatus();

        let jobTimeLeft = toHoursAndMinutes( Math.round((jobState.data.data[0].attributes.c*3)/60));
        let inJobTxt = "Systemet er ved at lave en ny liste | job statet: "+jobState.data.job[0].attributes.created_date+" | Antal mangler tjek:"+jobState.data.data[0].attributes.c+ " | Tid tilbage: "+jobTimeLeft;


        jobState.data.job[0].attributes.c == 1 ? $("#jobState").html(inJobTxt) : $("#jobState").html("<button id='newJob'>Bestil ny liste</button>")

        let html = self.exceededTable(valgshopData.data);
        $("#exceededDataContainer").html(html);
        $('#cardshopData').DataTable({
            "scrollY":        "calc(100vh - 280px)",
            "scrollCollapse": true,
            "paging":         false,
            dom: 'Bfrtip',
        })
        $(".searchItemTable").unbind("click").click(function(){
            let itemNr = $(this).attr("data-id")
            self.searchItemNrInModal(itemNr);
        })
        $('.quantityCardshopTable').unbind().on('input', function() {
            $(this).attr("org-quantity") != $(this).val() ? $(this).addClass("markChanges") : $(this).removeClass("markChanges")
            $(this).attr("org-quantity") != $(this).val() ? $(this).addClass("cardshop-uprdate") : $(this).removeClass("cardshop-uprdate")
        });
        $("#newJob").unbind("click").click(function(){
            alert("Nyt job er ved at blive oprettet");
            $("#jobState").html("Systemet er ved at lave en ny liste")
            $.post("https://system.gavefabrikken.dk/gavefabrikken_backend/component/reservationMonitoring.php?token=sdfsdalkkljdsflj4893478tr8gswfuf6478tf38qf&action=newjob",{}, function(data, textStatus) {

            });


        })


        self.calcExpiredStatus("quantityCardshopTable");


    }

    self.searchItemNrInModal = async (itemNr) => {
        let self = this;
        $(".modal-body").html("SYSTEMET ARBEJDER")
        $(".modal-title").html("SYSTEMET ARBEJDER");
        self.modalShow();
        let data = await self.doSearchItemNr(itemNr);
        self.searchHTML = self.buildItemsearchTable(data.data.searchData);
        let overview= self.buildItemsearchTotalStats(data.data);

        $(".modal-title").html(itemNr);
        $(".modal-body").html(overview+"<br>"+ self.searchHTML+"<br><br><br>");

        $('#itemSearchTable').DataTable({
            scrollY: "calc(100vh - 350px)",
            scrollX: true,
            scrollCollapse: true,
            paging: false,
            columnDefs: [
                { width: '20%', targets: 0 },
                {
                    targets: [5, 6, 7, 8], // R-tal, Antal valgte, forecast, Andel % columns
                    type: 'num-fmt',
                    render: function(data, type, row) {
                        if (type === 'sort') {
                            if (data === null || data === undefined || data === '') {
                                return 0;
                            }
                            return parseFloat(('' + data).replace(/[^\d.-]/g, '').replace(',', '.')) || 0;
                        }
                        return data;
                    }
                }
            ],
            fixedColumns: true,
            language: {
                decimal: ",",
                thousands: "."
            },
            order: [[6, 'desc']] // Sort by R-tal column (index 6) in descending order by default


        })
        $(".doUpdateSearchItem").unbind("click").click(function(){
            self.update("SearchTable-uprdate");
            if ($.fn.DataTable.isDataTable('#itemSearchTable')) {
                $('#itemSearchTable').DataTable().rows().invalidate().draw();
            }
        })

        $(".setCloseOpenAll").unbind("click").click(function(){
            self.setCloseOpenAll("SearchTable-uprdate");
        })
        $("#statsCSV").unbind("click").click(function(){
                        var $table = $(self.searchHTML);
            var csv = '\uFEFF';
            csv+= 'Firma; Varenr; Navn; Model;Lukket;; Reserverede;Antal valgte;forecast;Andel %;Antal valgte shop;Antal valgte shop %;Total gaver; Start;Luk;SO; SA;Sælger\n';

            $table.find('tr').each(function(index) {
                if (index === 0) return true;  // Skip first row (index 0)

                $(this).find('td, th').each(function(){
                    var text = $(this).text();
                    csv += text + ';';
                });
                csv += '\n';
            });

            // Create a downloadable link and click it
            var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            var a = document.createElement('a');
            a.href = window.URL.createObjectURL(blob);
            a.download = 'data.csv';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);

        })


        $('.quantityItemSearchTable').unbind().on('input', function() {
            $(this).attr("org-quantity") != $(this).val() ? $(this).addClass("markChanges") : $(this).removeClass("markChanges")
            $(this).attr("org-quantity") != $(this).val() ? $(this).addClass("SearchTable-uprdate") : $(this).removeClass("SearchTable-uprdate")
            const newQuantity = $(this).val();
            const row = $(this).closest('tr');
            console.log(newQuantity)
            row.find('td:nth-child(7)').text(newQuantity);

        });
        $(".modelOpenClose").unbind("click").click(function(){
            alert("Ikke aktiv endnu");
            // alert($(this).attr("data-id"));
           // alert($(this).attr("action"));
        });
        $("input[id^='do_close_']").unbind("click").click(function(){
            alert("Ikke aktiv endnu");
         //   alert($(this).attr("data-id"));
        });

        $("input[id^='autotopilot_']").unbind("click").click(function(){
            alert("Ikke aktiv endnu");
         //   alert($(this).attr("data-id"));
        });
    }
    self.doGetCardshop = () => {
        let self = this;
    
        return new Promise(async resolve => {
            $.post(_ajaxPath+"getCardshop",{lang:self.currentLang}, function(data, textStatus) {
                resolve(data);
            },'json');
        });
    }
    self.doGetValgshop = (shopID) => {
        return new Promise(async resolve => {
            $.post(_ajaxPath+"getValgshop",{shopID:shopID}, function(data, textStatus) {
                resolve(data);
            },'json');
        });
    }
    self.doGetExceeded = () => {
        return new Promise(async resolve => {
            $.post(_ajaxPath+"getGlobalItemNrStatus",{}, function(data, textStatus) {
                resolve(data);
            },'json');
        });
    }

    self.getJobStatus = () => {
        return new Promise(async resolve => {
            $.post(_ajaxPath+"jobStatus",{}, function(data, textStatus) {
                resolve(data);
            },'json');
        });
    }
    self.setCloseOpenAll = async (targetClass) => {
        let idArr = [];
        $('.quantityItemSearchTable').each(function(i, obj) {
            let id = $(obj).attr("pr_id");
            if (id !== "null" && id !== undefined) {
                idArr.push(id);
            }
        });
        let result = await self.doSetCloseOpenAll({list:idArr});
        if(result.status == 1){
            alert("Alle sat til at blive lukket")
            $(".do_close").html(1);
        } else {
            alert("Der er opsået et problem")
        }

    }
    self.doSetCloseOpenAll =  (postData) => {

        return new Promise(async resolve => {
            $.post(_ajaxPath+"doSetCloseOpenAll",postData, function(data, textStatus) {
                resolve(data);
            },'json');
        });
    }
    self.update = async (targetClass) => {

        let objArr = [];
        $('.'+targetClass).each(  function(i, obj) {
            objArr.push(obj);
        })
        for(const obj of objArr) {
            let id = $(obj).attr("pr_id")
            let quantity = $(obj).val();
            let oldval = $(obj).attr("org-quantity")

            let result = await self.doUpdate({id:id,quantity:quantity,old_quantity:oldval});
            if(result.status !=  "1") {
                alert("der er opstået en fejl, synkroniseringen afsluttes");
                return;
            }
            $(obj).attr("org-quantity",quantity);
            $(obj).removeClass("markChanges")
        }
        alert("Alle ændringer er gennemført")
    }
    self.doUpdate =  (postData) => {

        return new Promise(async resolve => {
            $.post(_ajaxPath+"updateQuantity",postData, function(data, textStatus) {
                resolve(data);
            },'json');
        });
    }

    self.modalShow = () => {
        $("#myModal").css("display", "block");
        $(".modal-close").unbind("click").click(function(){
            $("#myModal").css("display", "none");

        })
        window.onclick = function(event) {
            var modal = document.getElementById("myModal");
            if (event.target == modal) {
                $("#myModal").css("display", "none");
            }
        }
    }
    self.modalClose = () => {

    }
    self.calcExpiredStatus = (targetClass) => {
        $('.'+targetClass).parent().parent().removeClass("expired");
        $('.'+targetClass).parent().parent().removeClass("warning");
        $('.'+targetClass).each(  function(i, obj) {
            let reserverede = $(obj).attr("org-quantity");
            let antal = $(obj).attr("antal");

            let warning =  (reserverede * 0.5) > 10 ? 10 : (reserverede * 0.5)+1;

            if(reserverede*1 < antal*1 ){

                $(this).parent().parent().addClass("expired");

            } else if( (antal*1 + warning*1) > reserverede*1  ){
                $(this).parent().parent().addClass("warning");

            }

        })
    }


    //----------------------------------------------------
    self.resetTabsContainer = () => {
        $("#cardshopDataContainer").html("");
        $("#valgshopDataContainer").html("");
        $("#exceededDataContainer").html("");
    }
    self.cardshopTemplate = () => {
        return  `<div></div>`;
    }
    // 220147
    self.buildItemsearchTotalStats = (data) => {

        // omarrangere bomitem data
        const SAMquantityInfo = {};
        data.bomitemInfo.forEach(item => {
            const parentItemNo = item.attributes.parent_item_no;
            const quantityPer = item.attributes.quantity_per;
            SAMquantityInfo[parentItemNo] = quantityPer;
        });

        let forecast = 0;
        let quantity = 0;
        let antal = 0;
        data.searchData.map((i) => {

            i = i.attributes;
            if (isNaN(i.forcast.forecast)) {
                i.forcast.forecast = 0;
            }
            if (isNaN(i.antal)) {
                i.antal = 0;
            }
            if (isNaN(i.quantity)) {
                i.quantity = 0;
            }
            if((i.quantity*1) == 0 && (i.antal*1) == 0){
                console.log("do use")
                return;
            }
            // check if sam
            console.log("før:" +i.antal + " - " +(i.forcast.forecast*1) + " - " +(i.quantity*1)  + " - ")
            if (i.model_present_no.toLowerCase().includes("sam")) {
                try{
                    // adjust count of item in a sam from bomitem
                    let multifyer = (SAMquantityInfo[i.model_present_no] || 1);
                    i.antal = i.antal * multifyer
                    i.forcast.forecast = (i.forcast.forecast*1) * multifyer
                    i.quantity = (i.quantity*1) * multifyer
                } catch (e) {
                    console.log(e)

                }
                console.log("efter:" + i.antal + " - " +(i.forcast.forecast*1) + " - " +(i.quantity*1)  + " - ")
            }

            // test if a shop has sold more then 5%. <5% item current selected are benytte if  >5% benyttes forcast
            forecast+= (i.forcast.totalPercentageSelected*1) > 5 ? (i.forcast.forecast*1) : (i.antal*1);
           if(i.quantity != null){
               quantity+= i.quantity*1;
           }
           if(i.antal != null){
               antal+= i.antal*1;
           }
        });
        let avalible = quantity - antal;
        let forecastAvalible = quantity - forecast;
        const avalibleNAV = data?.navStock ?? 0;

        let css_avalible= avalible < 0 ? "color-red":"";
        let css_forecastAvalible = forecastAvalible < 0 ? "color-red":"";
        let css_avalibleNAV = (avalibleNAV*1) < 0 ? "color-red":"";
        return `<table id="totalStatsHeader-ItemSearch" width="700"><tr><td><b>Total antal Reserverede: </b> ${quantity}</td><td> </td>
                </td><td><b>Total antal valgte: </b> ${antal}</td>
                <td><b>Total tilgængelige: </b> <span class="${css_avalible}">${avalible}</span></td>
                <td><b>Total tilgængelige Forecast: </b> <span class="${css_forecastAvalible}">${forecastAvalible}</span></td>
                <td><b>Total tilgængelige NAV(rigtige tal): </b> <span class="${css_avalibleNAV}">${avalibleNAV}</span></td>
                </tr></table>`;
    }



    self.buildItemsearchTable = (data) => {
        console.log(data)
        return `<div class="setCloseOpenAll">Opdatere luk(ikke færdig)</div><div class="doUpdateSearchItem">Updatere</div><br><br><label style="cursor: pointer" id="statsCSV"><u>Download csv</u></label><table id="itemSearchTable">
             <thead>
            <tr>
            <th>Firma</th>            
            <th>Varenr</th>
            <th>Navn</th>
            <th>Model</th>
            <th>Lukket</th>
            <th>Reserverede</th>
            <th>R-tal</th>
            <th>Antal valgte</th>
            <th>forecast</th>
            <th>Andel %</th>
            <th>Antal valgte shop</th>
            <th>Antal valgte shop %</th>
            <th>Total gaver</th>
            <th>Start</th>
            <th>Luk</th>
            <th>SO</th>
            <th>SA</th>   
            <th>Sælger</th> 
            <th>At lukke</th>
            <th>Handling</th>            
            </tr> </thead><tbody>` +
            data.map((i) => {
                let notValid = "";
                i = i.attributes;
                if (isNaN(i.forcast.forecast)) {
                    i.forcast.forecast = 0;
                }
                if (isNaN(i.antal)) {
                    i.antal = 0;
                }
                if (isNaN(i.quantity)) {
                    i.quantity = 0;
                }
                /*
                if((i.quantity*1) == 0 && (i.antal*1) == 0){
                    return ;
                    notValid = "not-valid-search";
                }
*/
                let expired =  (i.quantity < i.antal ) ? "expired" : "";
                let Deadline = i.shop_is_gift_certificate == 1 ? "CardShop" : i.end_date;
              //  let forcast =  (i.forcast.forecast < i.antal) ? i.antal:i.forcast.forecast;
                let forecast = (i.forcast.totalPercentageSelected*1) > 5 ? (i.forcast.forecast*1) : "N/A";
                let closed = i.forcast.closed == 1 ? "Lukket":"Åben";
                // hvis  pm_active == 0, så er modellen aktive
                let modelOpenClose =  i.pm_active == 0 ? '<button class="modelOpenClose" action=0 data-id="'+i.pm_id+'">Å</button>' : '<button class="modelOpenClose" action=1 data-id="'+i.pm_id+'">L</button>' ;


                return `
                <tr class="${expired} ${notValid}">
                    <td>${i.shop_name}</td>
                    <td>${i.model_present_no}</td>
                    <td>${i.model_name}</td>
                    <td>${i.model_no}</td>
                    <td>${closed}</td>
                    <td><input class="quantityItemSearchTable" type="number"   pr_id="${i.pr_id}"  org-quantity="${i.quantity}" value="${i.quantity}" ></td>
                    <td>${i.quantity}</td>
                    <td>${i.antal}</td>
                    <td>${forecast}</td>        
                    <td>${i.forcast.percentage}%</td>
                    <td>${i.forcast.totalSelected}</td>
                    <td>${i.forcast.totalPercentageSelected}%</td>
                    <td>${i.forcast.totalPresent}</td>    
                    <td>${i.start_date}</td>
                    <td>${i.end_date}</td>
                    <td>${i.so_no}</td>
                     <td>${i.valgshopansvarlig}</td> 
                     <td>${i.salesperson_code}</td>
                     <td class="do_close">${i.do_close}</td>
                     <td >${modelOpenClose}
  
                        <div style="margin-top: 2px; font-size: 11px;">
            <label title="Do Close" style="display: inline-block; margin-right: 8px; cursor: pointer;">
                <input type="checkbox" name="do_close" id="do_close_${i.pm_id}" value="1" data-id="${i.pm_id}" ${i.do_close == 1 ? 'checked' : ''} style="margin-right: 2px;">
                LUK
            </label>
            <label title="Autotopilot" style="display: inline-block; cursor: pointer;">
                <input type="checkbox" name="autotopilot" id="autotopilot_${i.pm_id}" value="1" data-id="${i.pm_id}" ${i.autotopilot == 1 ? 'checked' : ''} style="margin-right: 2px;">
                AUTO
            </label>
        </div>
                     </td>                                                                                    
                </tr>

            `;
            }).join('') +`  </tbody> </table>`
    }

    self.buildValgshopTable = (data) => {

        return `<table id="cardshopData">
         <thead>
        <tr>
        <th>Konsept</th>
        <th>Varenr</th>
        <th>Navn</th>
        <th>Model</th>
        <th>Reserverede</th>
        <th>Antal valgte</th>
        <th>AUTO</th>
        <th>AUTOLÅS</th>
        <th>Søg varenr.</th>
        </tr> </thead><tbody>` +
            data.map((i) => {
                i = i.attributes;
                let isAuto = i.autopilot === 1 ? "checked":"";
                let isAutoLock = i.autopilot_lock === 1 ? "checked" : "";
                return `
            <tr class="itemRecord">
                <td>${i.concept_code}</td>
                <td>${i.model_present_no}</td>
                <td>${i.model_name}</td>
                <td>${i.model_no}</td>
                <td><input class="quantityCardshopTable" type="number"  pr_id="${i.pr_id}" antal="${i.antal}" org-quantity="${i.quantity}" value="${i.quantity}" ></td>
                <td>${i.antal}</td>
                 <td><input type="checkbox" class="largeCheckbox autoCheckbox" id="auto_${i.pr_id}" data-pr-id="${i.present_model_id}" ${isAuto}></td>
                <td>
                    <input type="checkbox" class="autoLockCheckbox" pr_id="${i.pr_id}" id="autolock_${i.pr_id}" data-pr-id="${i.present_model_id}" ${isAutoLock}>
                    <label for="autolock_${i.pr_id}" class="padlock-label">
                        <svg class="padlock-icon" viewBox="0 0 24 24" width="24" height="24">
                            <path class="padlock-body" d="M19 10h-1V7c0-3.3-2.7-6-6-6S6 3.7 6 7v3H5c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V12c0-1.1-.9-2-2-2zM8 7c0-2.2 1.8-4 4-4s4 1.8 4 4v3H8V7z"/>
                            <path class="padlock-keyhole" d="M12 17c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2z"/>
                        </svg>
                    </label>
                </td>
                <td><button class="searchItemTable" data-id="${i.model_present_no}">Søg</button></td>
            </tr>
        `;
            }).join('') +`  </tbody> </table>`;
    }


    self.buildCardshopTable = (data) => {
        return `<table id="cardshopData">
             <thead>
            <tr>
            <th>Konsept</th>
            <th>Varenr</th>
            <th>Navn</th>
            <th>Model</th>
            <th>Reserverede</th>
            <th>Antal valgte</th>
            <th>Søg varenr.</th>
            </tr> </thead><tbody>` +
            data.map((i) => {
                i = i.attributes;
                return `
                <tr class="itemRecord">
                    <td >${i.concept_code}</td>
                    <td>${i.model_present_no}</td>
                    <td>${i.model_name}</td>
                    <td>${i.model_no}</td>
                    <td><input class="quantityCardshopTable" type="number"  pr_id="${i.pr_id}" antal="${i.antal}" org-quantity="${i.quantity}" value="${i.quantity}" ></td>
                    <td>${i.antal}</td>
                    <td><button class="searchItemTable" data-id="${i.model_present_no}">Søg</button></td>
                                        
                </tr>

            `;
            }).join('') +`  </tbody> </table>`
    }
    self.exceededTable = (data) => {



        return `<table id="cardshopData">
             <thead>
            <tr>
            <th>Varenr</th>
            <th>Navn</th>
            <th>Model</th>
            <th>Reserverede</th>
            <th>Antal valgte</th>
            <th>forecast</th>
            <th>OS Valgte</th>
            <th>OS forecast</th>
             <th>OS forecast</th>
            <th>NAV</th>
            <th>Brand forecast </th>                                                
            <th>Søg varenr.</th>
            </tr> </thead><tbody>` +
            data.map((i) => {
                i = i.attributes;

                let model_name = Base64.decode(i.model_name)
                let model_no = Base64.decode(i.model_no)
                let is_exceeded_count = i.reserved - i.selected;
                let is_exceeded_forecast_count = i.reserved - i.forecast;
                let brand =   (i.reserved*1 > 0 && is_exceeded_count*1 < 0)  ?   parseFloat((  i.selected / i.reserved ) - 1).toFixed(2) : 0;
                let brandForecast =  (i.reserved*1 > 0 && is_exceeded_forecast_count*1 < 0)  ? parseFloat(padTo2Digits(   i.forecast / i.reserved) -1 ).toFixed(2)   : 0;
                let is_exceeded_count_class = is_exceeded_count < 0 ? "color-red":"";
                let is_exceeded_forecast_count_class = is_exceeded_forecast_count < 0 ? "color-red":"";
                let is_available_class = i.available < 0 ? "color-red":"";
                return `
                <tr class="itemRecord ">
                    <td>${i.item_nr}</td>
                    <td>${model_name}</td>
                    <td>${model_no}</td>
                    <td>${i.reserved}</td>
                    <td>${i.selected}</td>
                    <td>${i.forecast}</td>
                    <td class="${is_exceeded_count_class}">${is_exceeded_count}</td>
                    <td class="${is_exceeded_forecast_count_class}">${is_exceeded_forecast_count}</td>
                    <td class="${is_exceeded_count_class}">${brand}</td>
                    <td class="${is_available_class}" >${i.available}</td>
                    <td class="${is_exceeded_forecast_count_class}">${brandForecast}</td>                                                           
                    <td><button class="searchItemTable" data-id="${i.item_nr}">Søg</button></td>
                                        
                </tr>

            `;
            }).join('') +`  </tbody> </table>`
    }



})

function toHoursAndMinutes(totalMinutes) {
    const minutes = totalMinutes % 60;
    const hours = Math.floor(totalMinutes / 60);

    return `${padTo2Digits(hours)}:${padTo2Digits(minutes)}`;
}

function padTo2Digits(num) {
    return +(Math.round(num + "e+2")  + "e-2");
}