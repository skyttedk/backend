
var APPR_AJAX_URL = "https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/valgshop/approval/";
var BASE_AJAX_URL = "https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=";
import Base from 'https://system.gavefabrikken.dk/gavefabrikken_backend/units/cardshop/main/js/base.js';
var shop;

export default class MainApproval extends Base  {
    constructor() {
        super();

        $("#approvalFinalBtn").hide();
        $("#Lagerlokation-headline").hide();
        this.data = {
            hasStrength:{},
            hasValidItemNr:{},
            presents:{},
            presentCount:0

        }
    }
    async init(shopid,showMode=0,admin=0){
       
        // tjek om shoppen er godkendt
        let self = this;
        shop = shopid;
        let sold = await this.getSold();
        this.presentCount = sold;
        $("#ReservationSoldPresent").val(sold);

        let approvalState = await this.isApproval()
        let present = await this.getPresent();
        let hasStrengthsSet = await this.hasStrengthsSet();
        let hasValidItemNr = await this.hasValidItemNr();
        let hasDeativatedItems = await this.hasDeativatedItems();
        let currentWarehouse = await this.getCurrentWarehouse()
        let stockApprovalStatus = await this.getStockApprovalStatus();

        this.data.hasStrengthsSet =  hasStrengthsSet.data;
        this.data.hasValidItemNr =  hasValidItemNr.data;
        this.data.hasDeativatedItems =  hasDeativatedItems.data;

        // Two-state logic for stock approval status
        if (stockApprovalStatus.data === "godkendt") {
            this.data.stockApprovalStatus = 'Godkendt';
        } else {
            this.data.stockApprovalStatus = 'Ikke godkendt';
        }


        this.data.presents = present.data.data;
        console.log(currentWarehouse);
        this.initGUI(showMode);
        this.itemGUI();
        let cr = await this.calcReservation();
        this.calcReservationResponse(cr);
        await this.insetItemnrValidStatus();
        await this.insetStockStatus();
        $("#ReservationSoldPresentBtn").unbind("click").click(
            function(){
                self.updateSold()
            }
        );
        $(".editItemno").unbind("click").click(
            function(){
                self.updateItemno($(this).attr('model-id'),$(this).attr('present-id'))
            }
        );


        if(approvalState.data == 1){
            $("#vgshop_approval").prepend("<h3 style='color: red'>Reservationerne er godkendt og send til Navision</h3>");
            $("#resevation-headline").hide();
            $("#approvalFinalBtn").hide();

            this.hideAction()
          
        }


        if(this.data.hasStrengthsSet == 0 || this.data.hasValidItemNr  == 0 || this.data.hasDeativatedItems == 0){
            this.hideAction()

        } else {
          if(this.hasReservation() == 0){
              $("#approvalFinalBtn").hide();
          } else {
              $("#approvalFinalBtn").show();
          }


        }
        if (!currentWarehouse.data?.[0]?.attributes?.reservation_code) {
            $("#approvalFinalBtn").hide();
            $("#Lagerlokation-headline").show();
        }
        if(this.hasReservation() == 0){

          //  $("#approvalFinalBtn").show();
            //$("#approvalFinalBtn").hide(); Fjernet pga tidsmangel
        }
        this.setEvent();
        if(admin == 1){
            $("#navLinksPanel").show();
        }
        // lager lokationer
        $("#supportAutopilotBtn").unbind("click").click(
            function(){
                alert("Hvis du vælger autopil vil reservationerne automatisk blive justeret.")
            }
        );




    }
    getWarehouseLocations(){
        return new Promise(resolve => {
            $.post( APPR_AJAX_URL+"shop/readwarehouses", {} ,function(res ) {
                if(res.data.length == 0){
                    resolve(0);
                } else {
                    resolve(res)
                }
            }, "json")
        })
    }
    getCurrentWarehouse(){
        return new Promise(resolve => {
            $.post( APPR_AJAX_URL+"getReservation_code", {shop_id:shop} ,function(res ) {
                if(res.data.length == 0){
                    resolve(0);
                } else {
                    resolve(res)
                }

            }, "json")
        })
    }


    updateItemno(modelID,presentID){
        let self = this;
        var userInput = prompt('Skriv det nye varenummer:', '');
        if (userInput !== null) {
            if(userInput =="") return;
            let obj = {
                model_id:modelID,
                itemno:userInput,
                present_id:presentID
            }
            $.post( APPR_AJAX_URL+"updateItemno", obj ,function(res ) {
                self.init(shop)
            }, "json")



        }
        //alert(modelID)
    }
    updateSold()
    {
        let self = this;
        let count = $("#ReservationSoldPresent").val() == "" ? 0: $("#ReservationSoldPresent").val();
        $.post( APPR_AJAX_URL+"updateSold", {shop_id:shop,present_count:count} ,function(res ) {
            self.init(shop);
        }, "json")
    }
    getSold(){
        return new Promise(resolve => {
            $.post( APPR_AJAX_URL+"getSold", {shop_id:shop} ,function(res ) {
                if(res.data.length == 0){
                    resolve(0);
                } else {
                   resolve(res.data[0].attributes.user_count)
                }

            }, "json")
        })
    }

    isApproval(){
        return new Promise(resolve => {
            $.post( APPR_AJAX_URL+"isApproval", {shop_id:shop} ,function(res ) {
                resolve(res);
            }, "json")
        })
    }
     hideAction()
    {

        // $("#approvalFinalBtn").hide();  temp
      //  $("#approvalFinalBtn").show();
    }

    async save()
    {
        let saveData = [];
        $('.newsuggestion').each(function(){

            let quantity = $(this).val() == "" ? 0 : $(this).val();
            let shopID = $(this).attr("present_id");

            let data = {
                shop_id:shop,
                present_id:$(this).attr("present_id"),
                model_id:$(this).attr("model_id"),
                warning_level:0,
                quantity:quantity,
                do_close:0

            }
            if($(this).attr("resid") != 0 ){
                data.id = $(this).attr("resid");
            }
            saveData.push(data);
        });

        for (const data of saveData) {
            try {
                const response = await this.doSave(data);

                // Check if response indicates approval is required
                if (response) {
                    // Handle both object and string responses
                    let parsedResponse = response;
                    if (typeof response === 'string') {
                        try {
                            parsedResponse = JSON.parse(response);
                        } catch (e) {
                            continue;
                        }
                    }

                    if (parsedResponse.status === 'requires_approval') {
                        alert(parsedResponse.message || 'Ændringerne er sendt til godkendelse. Der foretages ingen ændringer før godkendelse.');
                    }
                }
            } catch (error) {
                console.error("Error in doSave:", error);
            }
        }

        
        this.init(shop);

    }

    doSave(obj)
    {
        return new Promise(resolve => {
            
            // Route to approval controller for shop ID 9808 normal controller for all others
            let controllerUrl = obj.shop_id == 9808 ?
                BASE_AJAX_URL+"reservationApproval/saveReservation" :
                BASE_AJAX_URL+"reservation/saveReservation";

            $.post( controllerUrl, obj ,function(res ) {
                //console.log("Raw server response:", res);
                resolve(res);
            }, "text")
        })
    }

    setEvent(){
        let self = this;

        $("#updateReservationBtn").unbind("click").click(
            function(){
                if(!self.calculateAndCheckMaxReservations()){
                    alert("Du prøver at reservere for mange, sæt antal ned")
                } else {
                    // Check if this will create an over-reservation that requires approval
                    if(self.willCreateOverReservation()) {
                        let confirmMessage = "Du er ved at over reservere. Der vil blive dannet en anmodning om godkendelse Vil du fortsætte?";
                        let r = confirm(confirmMessage);
                        if(r) self.save();
                    } else {
                        let r = confirm("Ønsker du at opdatere resevertionerne");
                        if(r) self.save();
                    }
                }
            }
        );
        $("#approvalFinalBtn").unbind("click").click(
            function(){

                let r = confirm("Øndsker du at godkende")
                if(r) self.approval()
            }
        );
        $('.newsuggestion').on('keyup', function(event){
            // Action to perform when a key is pressed
            self.calculateAndCheckMaxReservations();
        });
        self.calculateAndCheckMaxReservations();
    }

    calculateAndCheckMaxReservations(){
//
        $("#res-info").html("");
        let max = parseInt( $("#ReservationSoldPresent").val() ) ; // Example: Log the pressed key to the console
        max = parseInt(max*1.35);
        var total = 0;
        $('.newsuggestion').each(function() {
            var value = parseInt($(this).val()) || 0; // Henter værdien af hvert element og konverterer den til et tal
            value = (value === -1) ? 0 : value;
            total += value; // Tilføjer værdien til den samlede sum
        });
        let txt = "";

        let res = false;
        if(max < total){
            txt = `<div style="color: red">Max antal reservationer: ${max} | antal i ændringer: ${total} | Du har valgt for mange  </div>`;

        } else {
            txt = `<div style="color: green">Max antal reservationer: ${max} | antal i ændringer: ${total} </div>`;
            res = true;
        }
        $("#res-info").html(txt);
        return res;


    }

    willCreateOverReservation(){
        // Check if any reservation changes would create negative stock for external items
        let willOverReserve = false;
        let self = this;

        console.log("Checking for over-reservation...");

        $('.newsuggestion').each(function() {
            let newValue = parseInt($(this).val()) || 0;
            let presentId = $(this).attr("present_id");
            let modelId = $(this).attr("model_id");

            console.log(`Checking item ${modelId}: newValue=${newValue}`);

            // Find the corresponding present data
            let presentData = null;
            for(let present of self.data.presents) {
                if(present.present_model_id == modelId) {
                    presentData = present;
                    break;
                }
            }

            if(presentData) {
                let currentReservation = parseInt(presentData.reserved_quantity) || 0;
                let stockElement = $("#item_stock_status_" + modelId);
                let availableStockText = stockElement.text().trim();
                let availableStock = parseInt(availableStockText) || 0;

                console.log(`Item ${modelId}: current=${currentReservation}, new=${newValue}, available=${availableStock}, stockText="${availableStockText}"`);

                // Only check if new value is greater than current (increasing reservation)
                if(newValue > currentReservation) {
                    let increase = newValue - currentReservation;
                    let stockAfterChange = availableStock - increase;

                    console.log(`Item ${modelId}: increase=${increase}, stockAfterChange=${stockAfterChange}`);

                    // Check if this is an external item
                    let itemStatusElement = $("#item_status_" + modelId);
                    let itemStatusHTML = itemStatusElement.html() || "";
                    let isExternal = itemStatusHTML.toLowerCase().includes("ekstern");

                    console.log(`Item ${modelId}: isExternal=${isExternal}, itemStatusHTML="${itemStatusHTML}"`);

                    // Trigger for any over-reservation that makes stock negative (not just external items)
                    if(stockAfterChange < 0) {
                        console.log(`Over-reservation detected for item ${modelId} (external: ${isExternal})`);
                        willOverReserve = true;
                        return false; // Break out of each loop
                    }
                }
            }
        });

        console.log(`willCreateOverReservation result: ${willOverReserve}`);
        return willOverReserve;
    }

    approval()
    {
        let self = this;
        $.post( APPR_AJAX_URL+"approval", {shop_id:shop} ,function(res ) {
            self.init(shop);



        }, "json")
    }

    async insetItemnrValidStatus()
    {
        let self = this;
        return new Promise( async resolve => {
            for (const present of self.data.presents) {
                let html = "";
                let samhtml = "";
                const response =  await self.doItemnrExist(present.model_present_no);
                if(response.data == "1"){
                    let internal = response.item[0].is_external == "1" ? "<div>Ekstern</div>":"";
                    html =  `<p>Ok</p>${internal}`;
                } else {
                    html = `<br><button class='editItemno' model-id='${present.present_model_id}' present-id='${present.present_id}'>Fejl-Edit</button>`;
                }
                let no = present.model_present_no.toLowerCase();
                if (no.includes("sam")) {
                    let samNos = await self.getItemnoInSam(present.model_present_no);
                    samhtml = "<hr />";
                    if(samNos.data != "0"){
                     //   console.log(samNos.data)
                        samNos.data.forEach((element) => {
                         //   console.log(element)
                            let ext = element.is_external == 0 ? "" : " : ekstern";
                            samhtml+="<div style='font-size: 10px'>"+element.no+ext+"</div>";
                        });
                    }
                }
                $("#item_status_"+present.present_model_id).html(html+samhtml);
            }
            resolve();
        })
    }
    async getItemnoInSam(itemno){
        return new Promise(resolve => {
            $.post( APPR_AJAX_URL+"getItemnoInSam", {itemno:itemno} ,function(res ) {
                resolve(res);
            }, "json")
        })
    }
    async insetStockStatus()
    {
        let self = this;
        return new Promise( async resolve => {
            for (const present of self.data.presents) {
                const response =  await self.getStockStatus(present.model_present_no);
                let stock = response.data.length > 0 ? response.data[0].attributes.available : "N/A";
                $("#item_stock_status_"+present.present_model_id).html(stock);
            }
            resolve();
        })
    }

    async hasDeativatedItems() {
        return new Promise(resolve => {
            $.post( APPR_AJAX_URL+"hasDeativatedItems", {shopid:shop} ,function(res ) {
                resolve(res);
            }, "json")
        })
    }

    async hasStrengthsSet() {
        return new Promise(resolve => {
            $.post( APPR_AJAX_URL+"hasStrengthsSet", {shopid:shop} ,function(res ) {
                resolve(res);
            }, "json")
        })
    }

    async getStockApprovalStatus() {
        return new Promise(resolve => {
            $.post( APPR_AJAX_URL+"getStockApprovalStatus", {shopid:shop} ,function(res ) {
                resolve(res);
            }, "json")
        })
    }
    async hasValidItemNr() {
        return new Promise(resolve => {
            $.post( APPR_AJAX_URL+"hasValidItemNr", {shopid:shop} ,function(res ) {
                resolve(res);
            }, "json")
        })
    }
    async getPresent(){
        return new Promise(resolve => {
            $.post( BASE_AJAX_URL+"shop/getPresentStatsShop", {shop_id:shop} ,function(res ) {
                resolve(res);
            }, "json")
        })
    }


    // helpers
    hasReservation(){
        let status = 1;
        this.data.presents.forEach(function(item, index) {

            if(item.reserved_quantity == 0 || item.reserved_quantity == ""){
                status = 0
            }
        });
        return status;
    }
// calc

    calcReservation(){
        return new Promise(resolve => {
            $.post( BASE_AJAX_URL+"shop/getShopPresentsNew", {shop_id:shop} ,function(res ) {
                resolve(res);
            }, "json")
        })
    }
    async calcReservationResponse(res){
        let self = this;
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



        let calcRes =  await this.doCalcReservation(res);
        let sum = 0;

        res.data.forEach(function(present) {
            calcRes[present.attributes.strength-1]
            let antal = self.roundToNearest5(calcRes[present.attributes.strength-1]);
            //let antal = calcRes[present.attributes.strength-1];
            sum+=antal
            $("#suggestion_"+present.attributes.model_id).html(antal)
            if($("#newsuggestion_"+present.attributes.model_id).val() == "" ){
                $("#newsuggestion_"+present.attributes.model_id).val(antal)
            }


        });
        let sold = this.presentCount;

        if( Math.round(sold*1.35) < Math.round(sum*1)  ){
            let tooMany = Math.round( Math.round(sum) - Math.round(sold*1.35));
           // $("#res-info").html("Hvis du benytter 'Reservation Forslag', må du max manuelt nedskrive 'Reservation Ændring' med "+tooMany)
        }


    }
    doCalcReservation(res){
        return new Promise((resolve, reject) => {
            let  styrker = [];
            res.data.forEach(function(present) {
                styrker.push(present.attributes.strength);
            });
            let calcFre = this.calculateFrequency(styrker)


            let sold = this.presentCount
            if(sold == "" && sold==0){
                return;
            }
            let maxSold = Math.round(sold*1.3);


            let co = this.calculateOrder(calcFre,100);
            let resStrength1 = Math.round((maxSold * (co[0]/100 ) )/ calcFre[1] );
            let resStrength2 = Math.round((maxSold * (co[1]/100 ) )/ calcFre[2] );
            let resStrength3 = Math.round((maxSold * (co[2]/100 ) )/ calcFre[3] );
            resolve ([resStrength1, resStrength2, resStrength3]);
        });
    }
    calculateOrder(strength,presentSold){
        let quantity1 = strength[1];
        let quantity2 = strength[2];
        let quantity3 = strength[3];

        let sold = presentSold;
        let strength1 = 1; // Base strength
        let strength2 = 2; // Strength 3 is twice as large as strength 2
        let strength3 = 4; // Strength 3 is 4 times as large as strength 1

        // Calculate the total 'strength units'
        let totalStrengthUnits = strength1 * quantity1 + strength2 * quantity2 + strength3 * quantity3;

        // Calculate the proportion of each item to the total
        let proportion1 = (strength1 * quantity1) / totalStrengthUnits;
        let proportion2 = (strength2 * quantity2) / totalStrengthUnits;
        let proportion3 = (strength3 * quantity3) / totalStrengthUnits;

        // Now distribute the 100 items according to these proportions
        let orderStrength1 = Math.round(100 * proportion1);
        let orderStrength2 = Math.round(100 * proportion2);
        let orderStrength3 = Math.round(100 * proportion3);

        // Adjust if the total is not exactly 100 due to rounding
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
    }
    calculateFrequency(array){
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
    }
    roundToNearest5(number) {
        return Math.ceil(number / 5) * 5;
    }
    doItemnrExist(itemnr){
        return new Promise(resolve => {
            $.post( APPR_AJAX_URL+"itemnrExistExt", {itemnr:itemnr} ,function(res ) {
                resolve(res);
            }, "json")
        })
    }
    getStockStatus(itemnr){
        return new Promise(resolve => {
            $.post( APPR_AJAX_URL+"getStockStatus", {itemnr:itemnr,shopid:shop} ,function(res ) {
                resolve(res);
            }, "json")
        })
    }
    // GUI
    initGUI(showMode){
        let hasStrengthsSet = this.data.hasStrengthsSet == 0 ? "Ikke godkendt":"Godkendt";
        let hasValidItemNr = this.data.hasValidItemNr == 0 ? "Ikke godkendt":"Godkendt";
        let hasReservation = this.hasReservation() == 0 ? "Ikke godkendt":"Godkendt";
        let hasDeativated = this.data.hasDeativatedItems == 0 ? "Ikke godkendt":"Godkendt";

        // Only add red styling for "Over reservering" when "Ikke godkendt"
        let stockApprovalClass = this.data.stockApprovalStatus === 'Ikke godkendt' ? 'style="color: red;"' : '';

        let html =` <table class="table table-bordered w-100">
            <thead>
            <tr>
                <th scope="col">Element</th>
                <th scope="col">Status</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>Alle Styrker sat</td>
                <td id="strengthsStatus">${hasStrengthsSet}</td>
            </tr>
            <tr>
                <td>Alle varenumre er korrekte</td>
                <td id="itemNrStatus">${hasValidItemNr}</td>
            </tr>
            <tr>
                <td>Ingen varer er deaktiveret</td>
                <td id="strengthsStatus">${hasDeativated}</td>
            </tr>
            <tr>
                <td>Alle varenumre er reserveret </td>
                <td id="itemNrStatus">${hasReservation}</td>
            </tr>
            <tr>
                <td>Over reservering </td>
                <td id="stockApprovalStatus" ${stockApprovalClass}>${this.data.stockApprovalStatus || 'Ikke relevant'}</td>
            </tr>
            </tbody>
        </table>`;
        $("#vgshop_approval").html(html);
        if(showMode == 1) {
            $("#navLinksPanel").hide();
        }


    }
   async itemGUI(){

        let self = this;
        let html  = `<div id="res-info"></div><table class="table table-bordered w-100">
    <thead>
        <tr>
            <th>Varenr</th>
            <th>Varenr status</th>
            <th>Gave</th>
            <th>Antal reserverede</th>
            <th>Reservation Forslag</th>
            <th>Reservation Ændring </th>
            <th>Tilgængelige gaver</th>
        </tr>
    </thead>
    <tbody>`+ this.data.presents.map(function(obj){
            let deactivate = obj.present_total_is_active == 0 ? "<div style='color:red;'>De-aktiveret</div>":"";


            return `
        <tr>
            <td>${obj.model_present_no}${deactivate}</td>
            <td id="item_status_${obj.present_model_id}"></td>
            <td>${obj.model_present_name}</td>
            <td id="current_resevation_${obj.present_model_id}">${obj.reserved_quantity}</td>
            <td id="suggestion_${obj.present_model_id}"  ></td>
            <td>
                <input 
                    class="newsuggestion"
                    id="newsuggestion_${obj.present_model_id}"  type="number" min="0"
                    present_id ="${obj.present_id}"
                    resid ="${obj.reservation_id}"
                    model_id ="${obj.present_model_id}"
                    value="${obj.reserved_quantity}"
                />
            </td>
            <td id="item_stock_status_${obj.present_model_id}" >></td>
       </tr>
          
        `}).join('') +`
        </tbody>
        </table><button id="updateReservationBtn">Opdaterer reservation ændringer</button>`;
        $("#vgshop_approval").append(html+"<br><br><br>");
    }


}


