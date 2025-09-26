var BASE_AJAX_URL = "https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=";
//wh-portal-container

function WarehousePortal() {
    let self = this;
    let dataTable = {}
    this.init = async function(){
        $("#login-form").hide();
        this.initModal();
        this.initModalInfo();
        this.initApprovalValidation();
        let data = await this.loadData();

        $("#wh-portal-container").html(this.mainTemplate(data,_warehousename));
        this.initDataTable();
        this.setEvents();

        // Add window resize handler
        $(window).on('resize', function() {
            if (self.dataTable) {
                self.dataTable.columns.adjust();
                self.dataTable.fixedHeader.adjust();
            }
        });
    };
    this.initDataTable = function() {
        $.fn.dataTable.ext.type.order['date-dk-pre'] = function (data) {
            if (!data) return 0;

            let match = data.match(/(\d{2})-(\d{2})-(\d{4})/);
            if (!match) return 0;

            let [_, day, month, year] = match;
            return year + month + day;
        };

        this.dataTable = $('.styled-table').DataTable({
            "paging": false,
            "scrollY": "calc(100vh - 250px)",
            "scrollX": true,
            "scrollCollapse": true,
            "fixedHeader": {
                header: true,
                headerOffset: $('.wh-portal-top').outerHeight()
            },
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Danish.json",
                "search": "Søg:",
                "searchPlaceholder": "Søg i alle felter..."
            },
            "columnDefs": [
                {"orderable": false, "targets": -1},
                {"width": "50px", "targets": 1},
                {"width": "150px", "targets": 6},
                {"width": "80px", "targets": 13},   // Shop Type
                {"width": "100px", "targets": 14},  // Expire Date
                {"width": "200px", "targets": -1},
                {"type": "date-dk", "targets": 12},
                {"type": "date-dk", "targets": 14}
            ],
            "autoWidth": false,
            "dom": '<"table-search"f>rt', // Added search box above table
            "columns": [
                {"data": "0"},  // SO-NR
                {"data": "1"},  // Shop navn
                {"data": "2"},  // Ansvarlig
                {"data": "3"},  // Supporter
                {"data": "4"},  // Antal varenr
                {"data": "5"},  // Antal gaver
                {"data": "6"},  // Status
                {"data": "7"},  // Noter
                {"data": "8"},  // Flyt
                {"data": "9"},  // Flere lev
                {"data": "10"}, // Udland
                {"data": "11"}, // Udland-AF
                {"data": "12"}, // Leveringsdato
                {"data": "13"}, // Shop Type
                {"data": "14"}, // Expire Date
                {"data": "15"}  // Actions column
            ]
        });

        // Style the search input     <div class="status-filter" style="position: fixed; top:80px; right: 350px;">


        $('.dataTables_filter input').css({
            'width': '300px',
            'padding': '8px 12px',
            'border': '1px solid #ddd',
            'border-radius': '4px',
            'font-size': '14px'
        });

        // Update row counter when searching
        this.dataTable.on('search.dt', function () {
            const visibleRows = self.dataTable.rows({search: 'applied'}).nodes().length;
            $("#custom-counter").text(`Viser ${visibleRows} rækker`);
        });

        // Initial row count
        const initialVisibleRows = $("tr.all-shops:visible").length;
        $("#custom-counter").text(`Viser alle ${initialVisibleRows} rækker`);

        // Adjust columns on initial load
        setTimeout(() => {
            this.dataTable.columns.adjust();
            this.dataTable.fixedHeader.adjust();
        }, 100);
    };

    this.loadData = async function () {
        return new Promise((resolve, reject) => {
            var jqxhr = $.post(BASE_AJAX_URL+"warehousePortal/readShopData", {token: _token}, function(returnMsg, textStatus)
            {
                resolve(returnMsg);
            }, "json")
                .fail(function()
                {
                    alert("alert_problem");
                })
        });
    };
    this.loadShopDownloadData = async function (token, expireDateId){
        return new Promise((resolve, reject) => {
            // Convert empty string to null for normal shops
            if (expireDateId === "" || expireDateId === "undefined") {
                expireDateId = null;
            }

            let postData = {token: token};
            if (expireDateId) {
                postData.expire_date_id = expireDateId;
            }

            var jqxhr = $.post(BASE_AJAX_URL+"warehousePortal/readShopDownloadData", postData, function(returnMsg, textStatus)
            {
                resolve(returnMsg);
            }, "json")
                .fail(function()
                {
                    alert("alert_problem");
                })
        });
    };

    this.setEvents = function (){
        let self = this
        $("#export-csv").unbind("click").click(function() {
            self.exportTableToCSV();
        });
        $("#refresh-table").unbind("click").click(function() {
            location.reload();

        });
        $("#logout").unbind("click").click(function() {

            $.removeCookie('gfware', { path: '/' });
            location.reload();

        });

        $("#status-filter").on('change', function() {
            const selectedStatus = $(this).val();

            if (self.dataTable) {
                // Clear any existing search
                self.dataTable.search('').columns().search('');

                if (selectedStatus !== 'all') {
                    // Search the status column (index 6) for the selected status text
                    const statusText = $(this).find("option:selected").text();
                    self.dataTable.column(6).search(statusText, true, false);
                }

                self.dataTable.draw();

                // Update row counter
                const visibleRows = self.dataTable.rows({ search: 'applied' }).nodes().length;
                let counterText = selectedStatus === 'all'
                    ? `Viser alle ${visibleRows} rækker`
                    : `Viser ${visibleRows} rækker med status "${$(this).find("option:selected").text()}"`;
                $("#custom-counter").text(counterText);
            }
        });


        $("input[name='status']").change(function () {
            const selectedValue = $(this).val();

            // Skjul alle rækker først
            $(".all-shops").hide();

            // Vis de valgte rækker
            $("." + selectedValue).show();

            // Tæl synlige rækker
            const visibleRows = $("tr." + selectedValue + ":visible").length;

            // Opdater tælleren med relevant tekst
            let counterText = '';
            switch(selectedValue) {
                case 'all-shops':
                    counterText = `Viser alle ${visibleRows} rækker`;
                    break;
                case 'released':
                    counterText = `Viser ${visibleRows} godkendte rækker`;
                    break;
                case 'not-released':
                    counterText = `Viser ${visibleRows} ikke-godkendte rækker`;
                    break;
            }

            $("#custom-counter").text(counterText);

            // Eksisterende DataTables opdateringer
            if (self.dataTable) {
                self.dataTable.columns.adjust();
                self.dataTable.fixedHeader.adjust();
            }
        });
        $('.styled-table thead th').on('click', function() {
            // Kort forsinkelse for at give DataTables tid til at sortere
            setTimeout(() => {
                const selectedValue = $("input[name='status']:checked").val();
                const visibleRows = $("tr." + selectedValue + ":visible").length;

                // Opdater tælleren med samme logik som ovenfor
                let counterText = '';
                switch(selectedValue) {
                    case 'all-shops':
                        counterText = `Viser alle ${visibleRows} rækker`;
                        break;
                    case 'released':
                        counterText = `Viser ${visibleRows} godkendte rækker`;
                        break;
                    case 'not-released':
                        counterText = `Viser ${visibleRows} ikke-godkendte rækker`;
                        break;
                }

                $("#custom-counter").text(counterText);
            }, 100);
        });

        $(".hent-filer").unbind("click").click( async function(){
            let token = $(this).attr("data-id");
            let expireDateId = $(this).attr("data-expire-date-id");

            // Convert empty string to null for normal shops
            if (expireDateId === "" || expireDateId === "undefined") {
                expireDateId = null;
            }

            // Send notification før vi åbner modalen
            let buttonClickData = {token: token};
            if (expireDateId) {
                buttonClickData.expire_date_id = expireDateId;
            }
            await self.sendButtonClickNotification('files_status', buttonClickData);

            $("#myModal").html("");
            // Her åbner vi modalen
            $("#myModal").dialog("open");

            let data = await self.loadShopDownloadData(token, expireDateId);
            $("#myModal").html(self.fileDowonloadTemplate(data));
            let status = await self.readStatus(token, expireDateId);
            self.setPackagingStatus(status);
            self.setModalEvents(token, expireDateId);



        });

        $(".info").unbind("click").click( async function(){
            let token = $(this).attr("data-id");
            let expireDateId = $(this).attr("data-expire-date-id");

            // Convert empty string to null for normal shops
            if (expireDateId === "" || expireDateId === "undefined") {
                expireDateId = null;
            }

            // Send notification før vi åbner info modalen
            let buttonClickData = {token: token};
            if (expireDateId) {
                buttonClickData.expire_date_id = expireDateId;
            }
            await self.sendButtonClickNotification('info', buttonClickData);

            $("#myModalInfo").html("");
            $("#myModalInfo").dialog("open");
            $("#myModalInfo").html('<iframe src="https://system.gavefabrikken.dk//gavefabrikken_backend/index.php?rt=warehousePortal/showDeleveryDetail&token='+token+'" width="100%" height="600" frameBorder="0">Browser not compatible.</iframe>');
        });


        $("input[name='status']").change(function () {
            const selectedValue = $(this).val();
            $(".all-shops").hide()
            $("."+selectedValue).show()
        });



    }

    this.exportTableToCSV = function() {
        let csvContent = [];

        // Get headers (excluding the last "Handling" column)
        const headers = [];
        $('.styled-table thead th').each(function(index) {
            if (index < $('.styled-table thead th').length - 1) { // Skip last column (actions)
                headers.push($(this).text().trim());
            }
        });
        csvContent.push(headers);

        // Get data rows
        $('.styled-table tbody tr:visible').each(function() {
            const row = [];
            $(this).find('td').each(function(index) {
                if (index < $(this).parent().find('td').length - 1) { // Skip last column (actions)
                    // Clean the cell content: remove HTML tags and normalize newlines
                    let cellContent = $(this).html()
                        .replace(/<br\s*\/?>/gi, ' ')  // Replace <br> with space
                        .replace(/<[^>]*>/g, '')       // Remove other HTML tags
                        .replace(/&nbsp;/g, ' ')       // Replace &nbsp; with space
                        .trim();

                    // If the cell contains semicolons, wrap it in quotes
                    if (cellContent.includes(';')) {
                        cellContent = `"${cellContent}"`;
                    }

                    row.push(cellContent);
                }
            });
            csvContent.push(row);
        });

        // Convert to CSV string with semicolon separator
        const csvString = csvContent.map(row => row.join(';')).join('\n');

        // Create blob and download
        const blob = new Blob(['\ufeff' + csvString], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);

        // Get current date for filename
        const today = new Date();
        const date = today.getDate().toString().padStart(2, '0');
        const month = (today.getMonth() + 1).toString().padStart(2, '0');
        const year = today.getFullYear();
        const filename = `warehouse_data_${date}-${month}-${year}.csv`;

        link.setAttribute('href', url);
        link.setAttribute('download', filename);
        link.style.visibility = 'hidden';

        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    };
    this.sendButtonClickNotification = async function(button_type, buttonClickData) {
        try {
            let postData = {
                button_type: button_type
            };

            if (typeof buttonClickData === 'string') {
                // Backward compatibility - old token parameter
                postData.token = buttonClickData;
            } else {
                // New object format with token and expire_date_id
                postData.token = buttonClickData.token;
                if (buttonClickData.expire_date_id) {
                    postData.expire_date_id = buttonClickData.expire_date_id;
                }
            }

            const response = await $.post(BASE_AJAX_URL + "warehousePortal/buttonClick", postData);
            console.log("Button click notification sent:", button_type, buttonClickData);
        } catch (error) {
            console.error("Error sending button click notification:", error);
        }
    };
    this.setModalEvents = function(shopToken, expireDateId){
        let self = this;

        // Convert empty string to null for normal shops
        if (expireDateId === "" || expireDateId === "undefined") {
            expireDateId = null;
        }

        $(".swh-download").unbind("click").click(async function() {
            // Send notification før download
            let buttonClickData = {token: shopToken};
            if (expireDateId) {
                buttonClickData.expire_date_id = expireDateId;
            }
            await self.sendButtonClickNotification('download', buttonClickData);

            if($("#packaging-status").val() < 4) {
                alert("HUSK at ændre STATUS til 'Pakkeri igang', hvis der skal pakkes");
            }
            let token = $(this).parent().parent().attr("data-id");
            window.open(BASE_AJAX_URL + 'warehousePortal/download&token=' + token, '_blank');
        });
        $("#update-packaging-status").unbind("click").click(async function(){
            let new_packaging_status = $("#packaging-status").val();
            let current_status = self.presentStatus;
            let buttonClickData = {token: shopToken};
            if (expireDateId) {
                buttonClickData.expire_date_id = expireDateId;
            }
            await self.sendButtonClickNotification('status_update', buttonClickData);
            // Get status text for both current and new status
            const statList = [
                "Ingen status sat",
                "lister ikke klar",
                "",
                "lister godkendt",
                "",
                "Pakkeri igang",
                "Pakkeri færdig",
                "Gave plukket / leveret"
            ];

            let currentStatusText = statList[current_status] || "Ukendt status";
            let newStatusText = statList[new_packaging_status] || "Ukendt status";

            // Create confirmation message
            let confirmMessage =
                "Er du sikker på at du vil ændre status?\n\n" +
                "Fra: " + currentStatusText + "\n" +
                "Til: " + newStatusText + "\n\n" +
                "Tryk OK for at bekræfte ændringen.";

            if(confirm(confirmMessage)) {
                let updateData = {token: shopToken, packaging_status: new_packaging_status};
                if (expireDateId) {
                    updateData.expire_date_id = expireDateId;
                }
                $.post(BASE_AJAX_URL+"warehousePortal/updateStatus", updateData)
                    .done(function(returnMsg) {
                        if(returnMsg.status == 0){
                            alert("Der er opståen en fejl")
                            return;
                        }
                        alert("Status opdateret");

                        // Get references to elements that need updating - target specific expire_date_id for cardshops
                        let selector = `.styled-table tr[data-id="${shopToken}"]`;
                        if (expireDateId) {
                            selector += `[data-expire-date-id="${expireDateId}"]`;
                        } else {
                            selector += `[data-expire-date-id=""]`;
                        }
                        let row = $(selector);
                        let button = row.find('.hent-filer');
                        let statusCell = row.find('td:nth-child(7)');

                        // Update status text
                        statusCell.text(statList[new_packaging_status]);

                        // Remove old classes
                        button.removeClass('released not-released');
                        row.removeClass('released not-released');

                        // Determine new status class
                        let newStatusClass = new_packaging_status > 2 ? 'released' : 'not-released';

                        // Add new classes
                        button.addClass(newStatusClass);
                        row.addClass(newStatusClass);

                        // Update button styles
                        let backgroundColor = newStatusClass === 'released' ? '#98FB98' : '#FFCCCC';
                        let textColor = newStatusClass === 'released' ? '#006400' : '#8B0000';
                        let icon = newStatusClass === 'released' ? '✓' : '!';

                        button.css({
                            'backgroundColor': backgroundColor,
                            'color': textColor
                        });

                        // Update icon
                        button.html(`<span style="margin-right: 4px;">${icon}</span>Filer/Status`);

                        // Update presentStatus to reflect the new status
                        self.presentStatus = new_packaging_status;

                        // Don't redraw DataTable as it would overwrite our DOM changes
                        // The row-specific DOM update above is sufficient
                    })
                    .fail(function() {
                        alert("alert_problem");
                    });
            }
        });
        $("#save-note-to-gf").unbind("click").click(function(){

            let note =  $("#note-to-gf").val();
            let noteData = {token: shopToken, note_from_warehouse_to_gf: note};
            if (expireDateId) {
                noteData.expire_date_id = expireDateId;
            }
            $.post(BASE_AJAX_URL+"warehousePortal/updateNoteToGf", noteData)
                .done(function(returnMsg) {
                    if(returnMsg.status == 0){
                        alert("Der er opståen en fejl")
                        return;
                    }
                    alert("Note gemt");
                    //self.init()
                })
                .fail(function() {
                    alert("alert_problem");
                });


        });
        $("#approve-checklist").unbind("click").click(function() {
            let giftsChecked = $("#gifts-counted").prop("checked");
            let instructionsRead = $("#instructions-read").prop("checked");
            let packedOnTime = $("#packed-on-time").prop("checked");
            let countDate = $("#count-date").val();
            let countApprovedBy = $("#count-approved-by").val().trim();
            let instructionsApprovedBy = $("#instructions-approved-by").val().trim();
            let ontimeApprovedBy = $("#ontime-approved-by").val().trim();

            // Validation function for each section - only validates if either checkbox or name is filled
            const validateSection = (checked, approvedBy, sectionName) => {
                if (checked && !approvedBy) {
                    alert(`Fejl: Du har markeret "${sectionName}" men ikke angivet hvem der har godkendt det.`);
                    return false;
                }
                if (!checked && approvedBy) {
                    alert(`Fejl: Du har angivet godkender for "${sectionName}" men ikke sat kryds i checkboksen.`);
                    return false;
                }
                return true;
            };

            // Validate each section
            const giftCountValid = validateSection(
                giftsChecked,
                countApprovedBy,
                "Kundegaver optalt og godkendt"
            );

            const instructionsValid = validateSection(
                instructionsRead,
                instructionsApprovedBy,
                "Pakkevejledning læst og følges"
            );

            const ontimeValid = validateSection(
                packedOnTime,
                ontimeApprovedBy,
                "Pakket og sendt til tiden"
            );

            // Additional validation for count date only if gifts are checked
            if (giftsChecked && !countDate) {
                alert("Fejl: Når du markerer 'Kundegaver optalt og godkendt' skal du også angive en dato.");
                return;
            }

            // If any validation fails, stop the process
            if (!giftCountValid || !instructionsValid || !ontimeValid) {
                return;
            }

            if (!giftsChecked) {
                countDate = null;
            }

            let formattedDate = countDate ? new Date(countDate).toLocaleDateString('da-DK', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            }) : '';

            // Build confirmation message only for checked items
            let confirmMessage = "Er du sikker på at du vil gemme? \n\n";



            if (!giftsChecked && !instructionsRead && !packedOnTime) {
                confirmMessage += "Ingen punkter er markeret til godkendelse.\n\n";
            }

            confirmMessage += "Tryk OK for at gemme.";

            if(confirm(confirmMessage)) {
                const currentDate = new Date().toISOString();
                let approvalData = {
                    token: shopToken,
                    approved_count_date: formattedDate,
                    approved_count_date_approved_by: countApprovedBy,
                    approved_package_instructions_approved_by: instructionsApprovedBy,
                    approved_ontime_approved_by: ontimeApprovedBy,
                    approved_date: currentDate,
                    approved_ontime: packedOnTime,
                    approved_package_instructions: instructionsRead
                };
                if (expireDateId) {
                    approvalData.expire_date_id = expireDateId;
                }

                $.post(BASE_AJAX_URL+"warehousePortal/approval", approvalData, function(returnMsg, textStatus) {
                    alert("Ændringer er nu gemt i systemet!");
                    $("#myModal").dialog('close');
                    setTimeout(function() {
                        $(".hent-filer[data-id='" + shopToken + "']").click();
                    }, 500);
                }, "json")
                    .fail(function() {
                        alert("alert_problem");
                    });
            }
        });
    }
    this.initApprovalValidation = function() {
        const validateSection = (checkboxId, inputId) => {
            const $checkbox = $(`#${checkboxId}`);
            const $input = $(`#${inputId}`);
            const $panel = $input.closest('.approval-panel');

            const updateValidation = () => {
                const isChecked = $checkbox.prop('checked');
                const hasText = $input.val().trim().length > 0;

                // Only show validation errors if one of the fields is filled but not both
                if ((isChecked && !hasText) || (!isChecked && hasText)) {
                    $panel.css('border-color', '#dc3545');
                    $input.css('border-color', isChecked ? '#dc3545' : '#ccc');
                    $checkbox.parent().css('color', hasText ? '#dc3545' : '');
                } else {
                    // Reset styles when either both are empty or both are filled
                    $panel.css('border-color', '#dee2e6');
                    $input.css('border-color', '#ccc');
                    $checkbox.parent().css('color', '');
                }
            };

            $checkbox.on('change', updateValidation);
            $input.on('input', updateValidation);
        };

        validateSection('gifts-counted', 'count-approved-by');
        validateSection('instructions-read', 'instructions-approved-by');
        validateSection('packed-on-time', 'ontime-approved-by');
    };
    this.readStatus = async function (token, expireDateId){
        return new Promise((resolve, reject) => {
            // Convert empty string to null for normal shops
            if (expireDateId === "" || expireDateId === "undefined") {
                expireDateId = null;
            }

            let postData = {token: token};
            if (expireDateId) {
                postData.expire_date_id = expireDateId;
            }

            var jqxhr = $.post(BASE_AJAX_URL+"warehousePortal/readStatus", postData, function(returnMsg, textStatus)
            {
                resolve(returnMsg);
            }, "json")
                .fail(function()
                {
                    alert("alert_problem");
                })
        });
    }

    this.setNoteToGF = function (data){

        $("#note-to-gf").val("");  // Clear the field first


        const note = data?.data?.[0]?.attributes?.note_from_warehouse_to_gf ?? "";

        $("#note-to-gf").val(note);


    }


    this.setPackagingStatus = function (status){
        if(status.data.length == 0) {
            // Tøm modal indhold
            $("#myModal").html(`
                <div class="gf-modal-content" style="padding: 20px; text-align: center;">
                    <h3 style="color: #ff0000; margin-bottom: 20px;">
                        Shoppen er ikke frigivet / ingen status sat
                    </h3>
                </div>
            `);
            return;
        }

        let packaging_status = status.data[0].attributes.packaging_status;
        const approvalData = status.data[0].attributes;

        self.presentStatus = packaging_status;
        $("#packaging-status").val(packaging_status);

        // Sæt note_move_order og noter fra status data
        $("#move-order-note").val(approvalData.note_move_order || "");
        $("#general-note").val(approvalData.noter || "");

        // Only check packaging_status for disabling elements
        const shouldDisable = packaging_status === 0 || packaging_status === 1;

        // Handle UI elements based on status
        if(shouldDisable) {
            // Disable status update and download related elements
            $("#packaging-status").prop('disabled', true);
            $("#update-packaging-status").prop('disabled', true);
            $(".swh-download").prop('disabled', true);
            $("#save-note-to-gf").prop('disabled', true);

            // Add visual indication of disabled state
            $("button:disabled").css({
                'opacity': '0.5',
                'cursor': 'not-allowed',
                'background-color': '#cccccc'
            });
            $("#packaging-status:disabled").css({
                'opacity': '0.5',
                'cursor': 'not-allowed',
                'background-color': '#f5f5f5'
            });
        } else {
            // Enable all elements
            $("#packaging-status").prop('disabled', false);
            $("#update-packaging-status").prop('disabled', false);
            $(".swh-download").prop('disabled', false);
            $("#save-note-to-gf").prop('disabled', false);

            // Reset styles
            $("button, select").css({
                'opacity': '1',
                'cursor': 'pointer',
                'background-color': ''
            });
        }

        // Always show approval section
        $("#approval-section").show();

        // Reset form fields
        $("#count-date").val('');
        $("#approved-by").val('');
        $("#gifts-counted").prop('checked', false);
        $("#instructions-read").prop('checked', false);
        $("#packed-on-time").prop('checked', false);

        // Set approval form fields with data from status if data exists
        if (approvalData) {
            if (approvalData.approved_count_date) {
                $("#count-date").val(approvalData.approved_count_date.split(' ')[0]);
                $("#gifts-counted").prop('checked', true);
            }
            if (approvalData.approved_count_date_approved_by) {
                $("#count-approved-by").val(approvalData.approved_count_date_approved_by);
            }
            if (approvalData.approved_package_instructions === 1) {
                $("#instructions-read").prop('checked', true);
            }
            if (approvalData.approved_package_instructions_approved_by) {
                $("#instructions-approved-by").val(approvalData.approved_package_instructions_approved_by);
            }
            if (approvalData.approved_ontime === 1) {
                $("#packed-on-time").prop('checked', true);
            }
            if (approvalData.approved_ontime_approved_by) {
                $("#ontime-approved-by").val(approvalData.approved_ontime_approved_by);
            }
        }
        if($("#packaging-status").val() != packaging_status) {
            alert("Der er et problem med dropdown med status");
        }
    }
    this.initModal = function (){
        $( "#myModal" ).dialog({
            autoOpen: false,
            width: 'auto',
            height: 'auto',
            buttons: {
                "Luk": function() {
                    $(this).dialog("close");
                }
            }
        });
    }
    this.initModalInfo = function (){
        $( "#myModalInfo" ).dialog({
            autoOpen: false,
            width: 'auto',
            height: 'auto',
            buttons: {
                "Luk": function() {
                    $(this).dialog("close");
                }
            }
        });
    }

    this.fileDowonloadTemplate = function(data) {
        return `
        <div class="gf-modal-content">
            <!-- Status Section -->
            <div class="gf-modal-status-section" style="margin-bottom: 20px; display: flex; align-items: center;">
                <select id="packaging-status" class="gf-modal-select">
                    <option value="0">Ingen status sat</option>
                    <option value="1">lister ikke klar</option>
                    <option value="3">Lister godkendt</option>
                    <option value="7">Gave plukket / leveret</option>
                    <option value="5">Pakkeri igang</option>
                    <option value="6">Pakkeri færdig</option>
                </select>
                <button id="update-packaging-status" class="gf-modal-button gf-modal-button--update">
                    <span style="margin-right: 4px;">↻</span>
                    Opdater Status
                </button>
            </div>

            <!-- Files Table -->
            <div class="gf-modal-table-container" style="margin-bottom: 20px;">
                <table class="styled-table" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>Filnavn</th>
                            <th>Størrelse</th>
                            <th>Uploaded d.</th>
                            <th style="width: 120px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        ${data.data.map((i) => {
            let filename = i.attributes.real_filename;
            let token = i.attributes.token;
            let size = i.attributes.file_size == 0 ? 0 : Math.round((i.attributes.file_size*1)/1000000)
            size = size < 1 ? "<1" :size;
            let uploadTime = i.attributes.created_at.date.replace(/\.0+$/, "");
            return `
                                <tr data-id="${token}">
                                    <td>${filename}</td>
                                    <td>${size} MB</td>
                                    <td>${uploadTime}</td>
                                    <td>
                                        <button class="swh-download gf-modal-button gf-modal-button--download">
                                            <span style="margin-right: 4px;">↓</span>
                                            DOWNLOAD
                                        </button>
                                    </td>
                                </tr>
                            `
        }).join('')}
                    </tbody>
                </table>
            </div>

            <!-- Notes Section - Side by Side with spacing -->
            <div style="
                display: flex; 
                gap: 40px; 
                margin: 0 40px 20px 40px;
                justify-content: center;
            ">
                <!-- General Notes Column -->
                <div style="width: 500px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 500;">
                        Salgssupport
                    </label>
                    <textarea 
                        id="general-note" 
                        class="gf-modal-textarea"
                        rows="6" 
                        cols="50" 
                        readonly
                        style="
                            width: 100%;
                            padding: 8px;
                            border: 1px solid #dee2e6;
                            border-radius: 4px;
                            font-family: inherit;
                            resize: vertical;
                            background-color: #f8f9fa;
                            height: 100px;
                        "
                    ></textarea>
                </div>

                <!-- Move Order Notes Column -->
                <div style="width: 500px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 500;">
                        Overflytningsordre
                    </label>
                    <textarea 
                        id="move-order-note" 
                        class="gf-modal-textarea"
                        rows="6" 
                        cols="50" 
                        readonly
                        style="
                            width: 100%;
                            padding: 8px;
                            border: 1px solid #dee2e6;
                            border-radius: 4px;
                            font-family: inherit;
                            resize: vertical;
                            background-color: #f8f9fa;
                            height: 100px;
                        "
                    ></textarea>
                </div>
            </div>

            <!-- Note to GF Section - Hidden by default -->
            <div class="gf-modal-notes-section" style="display: none;">
                [... note to GF section remains unchanged ...]
            </div>

            <!-- Approval Section -->
          <div id="approval-section" class="gf-modal-approval-section" style="
            display: none;
            margin-top: 10px;
            padding: 20px;
            border: 2px solid #ff0000;
            border-radius: 4px;
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        ">
            <h3 class="gf-modal-approval-title" style="
                margin-bottom: 10px;
                color: #ff0000;
                border-bottom: 1px solid #ff0000;
                padding-bottom: 10px;
            ">Godkendelse</h3>
            
            <!-- Gift Count Panel -->
     <!-- Gift Count Panel -->
<div class="approval-panel" style="
    margin-bottom: 5px;
    padding: 10px;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    background-color: #f8f9fa;
">
    <div style="display: flex; align-items: center; margin-bottom: 10px;">
        <label style="display: flex; align-items: center; cursor: pointer; margin: 0;">
            <input type="checkbox" id="gifts-counted" class="gf-modal-checkbox" style="margin-right: 8px;">
            <span style="font-weight: 500;">Godkendt via tjekliste via pakkemodul med hensyn til optalt kundegaver d.</span>
        </label>
        <input 
            type="date" 
            id="count-date" 
            class="gf-modal-date-input"
            style="margin-left: 8px; padding: 4px; border: 1px solid #ccc; border-radius: 3px;"
        >
    </div>
    <div style="margin-top: 10px;">
        <label style="display: block; ">
            <span style="font-weight: 500;">Godkendt af (navn/initialer):</span>
            <input 
                type="text" 
                id="count-approved-by" 
                class="gf-modal-text-input"
                style="margin-left: 8px; padding: 6px; border: 1px solid #ccc; border-radius: 3px; width: 200px;"
            >
        </label>
    </div>
</div>

<!-- Instructions Panel -->
<div class="approval-panel" style="
    margin-bottom: 5px;
    padding: 10px;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    background-color: #f8f9fa;
">
    <div style="display: flex; align-items: center; margin-bottom: 10px;">
        <label style="display: flex; align-items: center; cursor: pointer; margin: 0;">
            <input type="checkbox" id="instructions-read" class="gf-modal-checkbox" style="margin-right: 8px;">
            <span style="font-weight: 500;">Kundens pakkevejledning er læst / bliver fulgt</span>
        </label>
    </div>
    <div style="margin-top: 10px;">
        <label style="display: block; ">
            <span style="font-weight: 500;">Godkendt af (navn/initialer):</span>
            <input 
                type="text" 
                id="instructions-approved-by" 
                class="gf-modal-text-input"
                style="margin-left: 8px; padding: 6px; border: 1px solid #ccc; border-radius: 3px; width: 200px;"
            >
        </label>
    </div>
</div>

<!-- On-Time Panel -->
<div class="approval-panel" style="
    margin-bottom: 5px;
    padding: 10px;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    background-color: #f8f9fa;
">
    <div style="display: flex; align-items: center; margin-bottom: 10px;">
        <label style="display: flex; align-items: center; cursor: pointer; margin: 0;">
            <input type="checkbox" id="packed-on-time" class="gf-modal-checkbox" style="margin-right: 8px;">
            <span style="font-weight: 500;">Pakket og sendt til tiden</span>
        </label>
    </div>
    <div style="margin-top: 10px;">
        <label style="display: block; ">
            <span style="font-weight: 500;">Godkendt af (navn/initialer):</span>
            <input 
                type="text" 
                id="ontime-approved-by" 
                class="gf-modal-text-input"
                style="margin-left: 8px; padding: 6px; border: 1px solid #ccc; border-radius: 3px; width: 200px;"
            >
        </label>
    </div>
</div>
                <!-- Approve Button -->
            <div style="
                display: flex;
                justify-content: flex-end;
                margin-top: 20px;
            ">
                <button id="approve-checklist" class="gf-modal-button gf-modal-button--approve" style="
                    display: inline-flex;
                    align-items: center;
                    padding: 8px 16px;
                    border: none;
                    border-radius: 4px;
                    font-weight: 500;
                    font-size: 14px;
                    cursor: pointer;
                    background-color: #4CAF50;
                    color: white;
                    transition: background-color 0.2s;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                ">
                    <span style="margin-right: 4px;">✓</span>
                    Gem
                </button>
            </div>
            </div>
        </div>
    `;
    };

    this.mainTemplate = function(data, warehouse) {
        const formatDate = (dateStr) => {
            if (!dateStr) return "";
            try {
                const date = new Date(dateStr);
                if (isNaN(date.getTime())) return dateStr; // Return original if invalid

                return date.toLocaleDateString('da-DK', {
                    day: '2-digit',
                    month: '2-digit',
                    year: 'numeric'
                }).replace(/\./g, '-');
            } catch (e) {
                console.error("Date formatting error:", e);
                return dateStr; // Return original if error
            }
        };

        const statusOptions = [
            { value: "all", text: "Alle status" },
            { value: "0", text: "Ingen status sat" },
            { value: "1", text: "Lister ikke klar" },
            { value: "3", text: "Lister godkendt" },
            { value: "5", text: "Pakkeri igang" },
            { value: "6", text: "Pakkeri færdig" },
            { value: "7", text: "Gave plukket / leveret" }
        ];


        const statusFilterHtml = `
            <div class="status-filter" style="position: fixed; top:85px; right: 250px;">
                <select id="status-filter" style="
                    padding: 8px;
                    border: 1px solid #ddd;
                    border-radius: 4px;
                    min-width: 200px;
                    font-size: 14px;
                ">
                    ${statusOptions.map(option =>
            `<option value="${option.value}">${option.text}</option>`
        ).join('')}
                </select>
            </div>
        `;



        let tableHeaders = `
        <tr>
            <th>SO-NR</th>
            <th width="50">Shop navn</th>
            <th>Ansvarlig</th>
            <th>Supporter</th>
            <th>Antal varenr</th>
            <th>Antal gaver</th>
            <th>Status</th>
            <th>Noter</th>
            <th width="150">Flyt</th>
            <th>Flere lev.</th>
            <th>Udland</th>
            <th>Udland-AF</th>
            <th>Leveringsdato</th>
            <th width="80">Shop Type</th>
            <th width="100">Expire Date</th>
            <th width="220">Handling</th>
        </tr>`;

        let tableBody = data.data.map((i) => {
            let statList = ["Ingen status sat","lister ikke klar","","lister godkendt","","Pakkeri igang","Pakkeri færdig","Gave plukket / leveret"];
            let packaging_status = i.attributes.packaging_status == null || i.attributes.packaging_status == "" ? 0 : i.attributes.packaging_status;
            let name = i.attributes.name.replace(/-/g, " - ");
            let Ansvarlig = i.attributes.valgshopansvarlig || "";
            let Supporter = i.attributes.gift_responsible || "";
            let itemno = i.attributes.order_no_count;
            let order_count = i.attributes.order_count;
            let udland = i.attributes.udland === 1 || i.attributes.udland === "1" ? "Ja" : "Nej";
            let moreDelevery = i.attributes.flere_leveringsadresser == 1 ? "Flere" : "";
            let note = (i.attributes.noter || "").replace(/\n/g, '<br>').replace(/,/g, ", ");
            let note_move_order = (i.attributes.note_move_order || "").replace(/\n/g, '<br>');
            let deleveri = formatDate(i.attributes.levering) || "";
            let token = i.attributes.token;
            let stat = statList[packaging_status];
            let pack_status = packaging_status > 2 ? "released" : "not-released";
            let so_on = i.attributes.so_no || "";

            // Cardshop data
            let shop_type = i.attributes.shop_type || "Normal";
            let expire_date_id = i.attributes.expire_date_id || "";
            let expire_display_date = "";

            if (shop_type === "Cardshop" && i.attributes.expire_display_date) {
                let weekText = i.attributes.expire_week_no ? ` (Uge ${i.attributes.expire_week_no})` : '';
                expire_display_date = `${i.attributes.expire_display_date}${weekText}`;
            }

            let earliestForeignDate = "";
            console.log('Processing row:', {
                udland: i.attributes.udland,
                foreign_delivery_date: i.attributes.foreign_delivery_date,
                name: name
            });

            if (i.attributes.udland === 1 || i.attributes.udland === "1") {
                console.log('Udland is 1 for:', name);

                if (i.attributes.foreign_delivery_date) {
                    try {
                        console.log('Raw foreign_delivery_date:', i.attributes.foreign_delivery_date);
                        const foreignDates = JSON.parse(i.attributes.foreign_delivery_date);
                        console.log('Parsed foreignDates:', foreignDates);

                        // Get all valid dates
                        const dates = Object.values(foreignDates)
                            .filter(date => date && date !== "0000-00-00" && date !== "")
                            .map(date => new Date(date));

                        console.log('Valid dates:', dates);

                        if (dates.length > 0) {
                            // Find earliest date
                            const earliestDate = new Date(Math.min(...dates));
                            console.log('Earliest date:', earliestDate);

                            // Format date
                            earliestForeignDate = earliestDate.toLocaleDateString('da-DK', {
                                day: '2-digit',
                                month: '2-digit',
                                year: 'numeric'
                            }).replace(/\./g, '-');

                            console.log('Formatted date:', earliestForeignDate);
                        }
                    } catch (e) {
                        console.error("Error processing foreign dates for " + name + ":", e);
                    }
                }
            }
            // Define button styles
            const buttonStyles = `
                display: inline-flex;
                align-items: center;
                justify-content: center;
                padding: 8px 16px;
                margin: 0 4px;
                border: none;
                border-radius: 4px;
                font-weight: 500;
                font-size: 14px;
                cursor: pointer;
                transition: all 0.2s ease;
                min-width: 90px;
                height: 32px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            `;

            // Define specific button styles for each type
            const fileButtonStyles = `
                ${buttonStyles}
                background-color: ${pack_status === 'released' ? '#98FB98' : '#FFCCCC'};
                color: ${pack_status === 'released' ? '#006400' : '#8B0000'};
                &:hover {
                    background-color: ${pack_status === 'released' ? '#90EE90' : '#FFB6B6'};
                }
            `;

            const infoButtonStyles = `
                ${buttonStyles}
                background-color: #f8f9fa;
                color: #495057;
                border: 1px solid #dee2e6;
                &:hover {
                    background-color: #e9ecef;
                }
            `;

            return `
            <tr data-id="${token}" data-expire-date-id="${expire_date_id}" class="${pack_status} all-shops ${shop_type.toLowerCase()}" style="${shop_type === 'Cardshop' ? 'background-color: #E3F2FD;' : ''}">
                <td>${so_on}</td>
                <td width="50">${name}</td>
                <td>${Ansvarlig}</td>
                <td>${Supporter}</td>
                <td>${itemno}</td>
                <td>${order_count}</td>
                <td width="150">${stat}</td>
                <td>${note}</td>
                <td>${note_move_order}</td>
                <td>${moreDelevery}</td>
                <td>${udland}</td>
                <td>${earliestForeignDate}</td>
                <td>${deleveri}</td>
                <td><strong>${shop_type}</strong></td>
                <td>${expire_display_date}</td>
                <td class="button-column" style="white-space: nowrap;">
                    <button data-id="${token}" data-expire-date-id="${expire_date_id}" class="hent-filer ${pack_status}" style="${fileButtonStyles}">
                        ${pack_status === 'released' ?
                '<span style="margin-right: 4px;">✓</span>' :
                '<span style="margin-right: 4px;">!</span>'}
                        Filer/Status
                    </button>
                    <button data-id="${token}" data-expire-date-id="${expire_date_id}" class="info" style="${infoButtonStyles}">
                        <span style="margin-right: 4px;">ℹ</span>
                        Leveringsinfo
                    </button>
                </td>
            </tr>`;
        }).join('');

        return `
            <div class="wh-portal-top">
                <h3>${warehouse}</h3>
                <div style="display: flex; gap: 10px; margin-bottom: 15px;">
                    <button id="logout">Logud</button>
                    <button id="refresh-table" class="refresh-button">Opdater tabel</button>
                    <button id="export-csv" class="export-button" style="
                        background-color: #4CAF50;
                        color: white;
                        border: none;
                        padding: 8px 16px;
                        border-radius: 4px;
                        cursor: pointer;
                        display: inline-flex;
                        align-items: center;
                        gap: 8px;
                    ">
                        <span>↓</span>
                        Download CSV
                    </button>
                </div>
                
                <div class="wh-portal-radio-group">
                    <label class="wh-portal-radio-option">
                        <input type="radio" name="status" checked="checked" value="all-shops">
                        <span>Vis alle</span>
                    </label>
                    <label class="wh-portal-radio-option">
                        <input type="radio" name="status" value="released">
                        <span>Godkendt</span>
                    </label>
                    <label class="wh-portal-radio-option">
                        <input type="radio" name="status" value="not-released">
                        <span>Vis ikke godkendt</span>
                    </label>
                </div>

                ${statusFilterHtml}
            </div>
            <hr>
            <br><br><br><br>
            <table class="styled-table">
                <thead>${tableHeaders}</thead>
                <tbody>${tableBody}</tbody>
            </table>
            <div id="custom-counter" style="
                margin-top: 10px;
                padding: 8px;
                color: #666;
                font-size: 14px;
            ">
                Viser ${data.data.length} rækker
            </div>
            <br>
        `;
    };






}
