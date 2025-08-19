
var rm = (function () {
    var self = this;
    self.data = [];
    self.init = () => {

    }
    self.initSetEvent = () => {

    }


    self.initCardshop = () => {
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
            alert("sadfsad")
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
        console.log(overview)
        $("#cardshopOverview").html(overview)
        $("#cardshopDataContainer").html(html);

        $('#itemSearchTable').DataTable({
            "scrollY":        "calc(100vh - 280px)",
            "scrollCollapse": true,
            "paging":         false,

            dom: 'Bfrtip',
        })
        $(".doUpdateSearchItem").unbind("click").click(function(){
            self.update("SearchTable-uprdate");
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
    self.getValgshop = async () => {
        self.resetTabsContainer();
        $("#valgshopDataContainer").html("Systemet arbejder");
        $(".doUpdate").show();
        let valgshopData = await self.doGetValgshop();
        let html = self.buildCardshopTable(valgshopData.data);
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
    }
    self.getExceeded = async () => {
        self.resetTabsContainer();
        $("#exceededDataContainer").html("Systemet arbejder");
        $(".doUpdate").show();
        let valgshopData = await self.doGetExceeded();
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
        self.calcExpiredStatus("quantityCardshopTable");


    }

    self.searchItemNrInModal = async (itemNr) => {
        $(".modal-body").html("SYSTEMET ARBEJDER")
        $(".modal-title").html("SYSTEMET ARBEJDER");
        self.modalShow();
        let data = await self.doSearchItemNr(itemNr);
        let html = self.buildItemsearchTable(data.data);
        let overview= self.buildItemsearchTotalStats(data.data);

        $(".modal-title").html(itemNr);
        $(".modal-body").html(overview+"<br>"+html+"<br><br><br>");

        $('#itemSearchTable').DataTable({
            scrollY:        "calc(100vh - 350px)",
            scrollX:        true,
            scrollCollapse: true,
            paging:         false,
            columnDefs: [
                { width: '20%', targets: 0 }
            ],
            fixedColumns: true


        })
        $(".doUpdateSearchItem").unbind("click").click(function(){
            self.update("SearchTable-uprdate");
        })
        $('.quantityItemSearchTable').unbind().on('input', function() {
            $(this).attr("org-quantity") != $(this).val() ? $(this).addClass("markChanges") : $(this).removeClass("markChanges")
            $(this).attr("org-quantity") != $(this).val() ? $(this).addClass("SearchTable-uprdate") : $(this).removeClass("SearchTable-uprdate")
        });


    }
    self.doGetCardshop = () => {
        return new Promise(async resolve => {
            $.post(_ajaxPath+"getCardshop",{}, function(data, textStatus) {
                resolve(data);
            },'json');
        });
    }
    self.doGetValgshop = () => {
        return new Promise(async resolve => {
            $.post(_ajaxPath+"getValgshop",{}, function(data, textStatus) {
                resolve(data);
            },'json');
        });
    }
    self.doGetExceeded = () => {
        return new Promise(async resolve => {
            $.post(_ajaxPath+"getExceeded",{}, function(data, textStatus) {
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
        let quantity = 0;
        let antal = 0;
        data.map((i) => {
            i = i.attributes;
            if(i.quantity != null){
                quantity+= i.quantity*1;
            }
            if(i.antal != null){
                antal+= i.antal*1;
            }
        });
        let avalible = quantity - antal;
        return `<table width="500"><tr><td><b>Total antal Reserverede: </b> ${quantity}</td><td> </td></td><td><b>Total antal valgte: </b> ${antal}</td><td><b>Total tilgængelige: </b> <span style="color: blue">${avalible}</span></td></tr></table>`;
    }



    self.buildItemsearchTable = (data) => {
        return `<div class="doUpdateSearchItem">Updatere</div><br><br><table id="itemSearchTable">
             <thead>
            <tr>
            <th>Firma</th>            
            <th>Varenr</th>
            <th>Navn</th>
            <th>Model</th>
            <th>Reserverede</th>
            <th>Antal valgte</th>
            <th>forecast</th>
            <th>Andel %</th>
            <th>Antal valgte shop</th>
            <th>Antal valgte shop %</th>
            <th>Total gaver</th>
            <th>Deadline</th>            
            </tr> </thead><tbody>` +
            data.map((i) => {

                i = i.attributes;
                let expired =  (i.quantity < i.antal ) ? "expired" : "";
                let Deadline = i.shop_is_gift_certificate == 1 ? "CardShop" : i.end_date;
                let forcast =  (i.forcast.forecast < i.antal) ? i.antal:i.forcast.forecast;
                return `
                <tr class="${expired}">
                    <td>${i.shop_name}</td>
                    <td>${i.model_present_no}</td>
                    <td>${i.model_name}</td>
                    <td>${i.model_no}</td>
                    <td><input class="quantityItemSearchTable" type="number"   pr_id="${i.pr_id}"  org-quantity="${i.quantity}" value="${i.quantity}" ></td>
                    <td>${i.antal}</td>
                    <td>${forcast}</td>        
                    <td>${i.forcast.percentage}%</td>
                    <td>${i.forcast.totalSelected}</td>
                    <td>${i.forcast.totalPercentageSelected}%</td>
                    <td>${i.forcast.totalPresent}</td>    
                    
                
                    <td>${i.end_date}</td>                                        
                </tr>

            `;
            }).join('') +`  </tbody> </table>`
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
            <th>Søg varenr.</th>
            </tr> </thead><tbody>` +
            data.map((i) => {
                i = i.attributes;

                return `
                <tr class="itemRecord ">
                    <td>${i.model_present_no}</td>
                    <td>${i.model_name}</td>
                    <td>${i.model_no}</td>
                    <td><button class="searchItemTable" data-id="${i.model_present_no}">Søg</button></td>
                                        
                </tr>

            `;
            }).join('') +`  </tbody> </table>`
    }



})