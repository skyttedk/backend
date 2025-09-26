var BASE_AJAX_URL = "https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=";

// VERSION: 2025-09-23-16:10:00
console.log('ShopWarehouse.js loaded - VERSION: 2025-09-23-16:10:00');


function ShopWarehouse(shopID, expireDateId = null) {
    if (typeof shopID !== 'number') {
        alert("Der er opstået en fejl")
        return;
    }
    let self = this;
    this.template = new ShopWarehouseTemplate;
    this.presentStatus = 0;
    this.shopID = shopID;
    this.expireDateId = expireDateId;
    this.data = {};

    this.checkAndInitializeCardshop = async function() {
        try {
            console.log('Checking cardshop for shopID:', this.shopID);
            const response = await $.post(BASE_AJAX_URL + "shopWarehouse/getDeliveryDates", {shop_id: this.shopID});

            // Parse JSON response
            let parsedResponse = JSON.parse(response);
            let deliveryDatesResponse = parsedResponse.data;

            console.log('Parsed response:', parsedResponse);
            console.log('Delivery data:', deliveryDatesResponse);

            if(deliveryDatesResponse && deliveryDatesResponse.is_cardshop && deliveryDatesResponse.delivery_dates.length > 0) {
                console.log('Building dropdown for cardshop');

                // Force show dropdown
                $("#delivery-date-selector").show().removeClass('d-none');

                // Build dropdown HTML
                let dropdownHtml = '';
                deliveryDatesResponse.delivery_dates.forEach(function(date) {
                    let weekText = date.week_no ? ` (Uge ${date.week_no})` : '';
                    dropdownHtml += `<option value="${date.id}">${date.display_date}${weekText}</option>`;
                });

                // Set dropdown content
                $("#delivery-date-dropdown").html(dropdownHtml);

                // Auto-select first date if none selected
                if(!this.expireDateId) {
                    this.expireDateId = deliveryDatesResponse.delivery_dates[0].id;
                }

                // Set selected value
                $("#delivery-date-dropdown").val(this.expireDateId);

                console.log('Dropdown built, selected expireDateId:', this.expireDateId);
                return true; // Initialize warehouse with selected date
            } else {
                // Regular shop - hide dropdown and initialize normally
                console.log('This is a regular shop, hiding dropdown');
                $("#delivery-date-selector").hide().addClass('d-none');
                return true; // Initialize warehouse
            }
        } catch(error) {
            // If AJAX fails, treat as regular shop
            console.log('AJAX failed, treating as regular shop:', error);
            $("#delivery-date-selector").hide().addClass('d-none');
            return true; // Initialize warehouse
        }
    };
    this.init = async function(){

        let data = await this.readFiles(self.shopID);
        $("#shopWarehouseContainer").html(this.template.buildTable(data));
        $("#update-packaging-status").hide();
        let status = await this.readStatus(self.shopID);
        this.setEvents()

        this.setPackagingStatus(status);
        //let template = ShopWarehouseTemplate();

        //template.buildTable("asdfasdfasd");
        //$("#shopWarehouseContainer").html();
    };
    this.setPackagingStatus = function (status){
        if(status.data.length == 0){
            $("#packaging_status").val(0);
        } else {
            let packaging_status = status.data[0].attributes.packaging_status;
            self.presentStatus = packaging_status;
            let note = status.data[0].attributes.noter;
            let note_move_order = status.data[0].attributes.note_move_order;
            let note_from_warehouse_to_gf = status.data[0].attributes.note_from_warehouse_to_gf

            $("#packaging-status").val(packaging_status);
            $("#packaging-note").html(note);
            $("#note-move-order").html(note_move_order);
            $("#note_from_warehouse_to_gf").html(note_from_warehouse_to_gf);


            if($("#packaging-status").val() != packaging_status){
                alert("Der er et problem med dropdown med status")
            }

        }
    }
    this.setEvents = function (){
        let self = this;
        $(".swh-download").unbind("click").click(
            function(){
                let token = $(this).parent().parent().attr("data-id")
                window.open(BASE_AJAX_URL + 'shopWarehouse/download&token=' + token, '_blank');
            }
        );

        $('.dropdown-item').click(function() {
            selectedWarehouse = $(this).data('warehouse');
            $('.dropdown-toggle').text($(this).text());
        });

        $('#getAddressBtn').click(function() {
            if(selectedWarehouse) {
                window.open(`https://system.gavefabrikken.dk//gavefabrikken_backend/index.php?rt=warehousePortal/makeAdressReport&token=${selectedWarehouse}`, '_blank');
            } else {
                alert('Vælg venligst et warehouse først');
            }
        });

        $(".swh-replace").unbind("click").click(
            function(){
                let token = $(this).parent().parent().attr("data-id");

                let fileInput = $('<input type="file" id="replaceFileInput">');
                fileInput.click();

                fileInput.change(function() {
                    let file = this.files[0];
                    let formData = new FormData();
                    formData.append('file', file);
                    formData.append('token', token);

                    $.ajax({
                        url: BASE_AJAX_URL + 'shopWarehouse/replace',
                        type: 'POST',
                        data: formData,
                        contentType: false,
                        processData: false,
                        success: function(response) {
                            self.init()
                            alert("Fil erstattet");
                        },
                        error: function() {
                            alert("Kunne ikke erstatte filen");
                        }
                    });
                });
            }
        );

        $(".swh-delete").unbind("click").click(
            function(){
                let token = $(this).parent().parent().attr("data-id");
                if (confirm("Er du sikker på, at du vil slette denne fil?")) {
                    $.ajax({
                        url: BASE_AJAX_URL + 'shopWarehouse/deactive',
                        type: 'POST',
                        data: { token: token },
                        success: function(response) {
                            self.init()
                            alert("Fil slettet");
                        },
                        error: function() {
                            alert("Kunne ikke slette filen");
                        }
                    });
                }
            }
        );
        $("#packaging-status").change(function() {
            $("#update-packaging-status").show();
        });
        $("#update-packaging-status").unbind("click").click(function(){
            let packaging_status = $("#packaging-status").val();
            /*
              if(self.presentStatus > 4 ){
                  alert("Du kan ikke længere ændre denne status, da lageret har godkendt lister")
              }
              */
            let postData = {shop_id: self.shopID, packaging_status: packaging_status};
            if(self.expireDateId) {
                postData.expire_date_id = self.expireDateId;
            }

            $.post(BASE_AJAX_URL+"shopWarehouse/updateStatus", postData)
                .done(function(returnMsg) {
                    alert("Status ændret")
                    location.reload();
                    $("#update-packaging-status").hide();
                })
                .fail(function() {
                    alert("alert_problem");
                });
        });
        $("#update-packaging-note").unbind("click").click(function(){
            let note = $("#packaging-note").val();
            let postData = {shop_id: self.shopID, note: note};
            if(self.expireDateId) {
                postData.expire_date_id = self.expireDateId;
            }

            $.post(BASE_AJAX_URL+"shopWarehouse/updateNote", postData)
                .done(function(returnMsg) {
                    // Do something with returnMsg
                    alert("Salgsnote er gemt")
                    location.reload();
                })
                .fail(function() {
                    alert("alert_problem");
                });
        });
        $("#update-note-move-order").unbind("click").click(function(){
            let note = $("#note-move-order").val();
            let postData = {shop_id: self.shopID, note: note};
            if(self.expireDateId) {
                postData.expire_date_id = self.expireDateId;
            }

            $.post(BASE_AJAX_URL+"shopWarehouse/updateNoteMoveOrder", postData)
                .done(function(returnMsg) {
                    // Do something with returnMsg
                    alert("Overflytningsordre er gemt")
                    location.reload();
                })
                .fail(function() {
                    alert("alert_problem");
                });
        });

    }

    this.readFiles = async function (shopID){
        return new Promise((resolve, reject) => {
            let postData = {shop_id: shopID};
            if(self.expireDateId) {
                postData.expire_date_id = self.expireDateId;
            }

            var jqxhr = $.post(BASE_AJAX_URL+"shopWarehouse/readByShop", postData, function(returnMsg, textStatus)
            {
                resolve(returnMsg);
            }, "json")
                .fail(function()
                {
                    alert("alert_problem");
                })
        });
    }
    this.readStatus = async function (shopID){
        return new Promise((resolve, reject) => {
            let postData = {shop_id: shopID};
            if(self.expireDateId) {
                postData.expire_date_id = self.expireDateId;
            }

            var jqxhr = $.post(BASE_AJAX_URL+"shopWarehouse/readStatus", postData, function(returnMsg, textStatus)
            {
                resolve(returnMsg);
            }, "json")
                .fail(function()
                {
                    alert("alert_problem");
                })
        });
    }
}



function ShopWarehouseTemplate() {
    this.buildTable = function(data) {
        return `
        <div class="mb-3 d-flex align-items-center">
            <select id="packaging-status" class="form-select mb-2 me-2" style="max-width: 200px;">
                <option value="0">Ingen status sat</option>
                <option value="1">Lister ikke klar</option>
                <option value="3">Lister godkendt</option>
                <option value="7">Gave plukket / leveret</option>
                <option value="5">Pakkeri igang</option>
                <option value="6">Pakkeri færdig</option>
            </select>
            <button id="update-packaging-status" class="btn btn-danger">Opdatere Status</button>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Filnavn</th>
                        <th>Størrelse</th>
                        <th>Uploaded d.</th>
                        <th>Handlinger</th>
                    </tr>
                </thead>
                <tbody>
                    ${data.data.map((i) => {
            let filename = i.attributes.real_filename;
            let token = i.attributes.token;
            let size = i.attributes.file_size == 0 ? 0 : Math.round((i.attributes.file_size*1)/1000000);
            size = size < 1 ? "<1" : size;
            let uploadTime = i.attributes.created_at.date.replace(/\.0+$/, "");
            return `
                        <tr data-id="${token}">
                            <td>${filename}</td>
                            <td>${size} MB</td>
                            <td>${uploadTime}</td>
                            <td>
                                <button class="btn btn-primary btn-sm swh-download">DOWNLOAD</button>
                                <button class="btn btn-warning btn-sm swh-replace">ERSTAT</button>
                                <button class="btn btn-danger btn-sm swh-delete">SLET</button>
                            </td>
                        </tr>
                        `;
        }).join('')}
                </tbody>
            </table>
        </div>

        <div class="row mt-4">
            <div class="col-md-6 mb-3">
                <div class="form-group">
                    <label for="packaging-note">Salgssupport</label>
                    <textarea id="packaging-note" class="form-control" rows="6" placeholder="Skriv note"></textarea>
                </div>
                <button id="update-packaging-note" class="btn btn-primary mt-2">Gem note</button>
            </div>
            <div class="col-md-6 mb-3">
                <div class="form-group">
                    <label for="note-move-order">Overflytningsordre</label>
                    <textarea id="note-move-order" class="form-control" rows="6" placeholder="Skriv note"></textarea>
                </div>
                <button id="update-note-move-order" class="btn btn-primary mt-2">Gem note</button>
            </div>
        </div>

        <hr>

        <div class="mt-4" style="display: none;">
            <div class="form-group">
                <label for="note_from_warehouse_to_gf">Note fra lageret</label>
                <textarea id="note_from_warehouse_to_gf" class="form-control" rows="6" disabled></textarea>
            </div>
        </div>
        `;
    }
}


/*
function shopWarehouseTemplate = {
    init: () => {
        return `
                <form id="uploadForm" enctype="multipart/form-data">
                <input type="file" name="file" id="file">
                <input type="button" value="Upload" id="uploadButton">
                </form>
                <div id="status"></div>
        `
    }
}
*/
