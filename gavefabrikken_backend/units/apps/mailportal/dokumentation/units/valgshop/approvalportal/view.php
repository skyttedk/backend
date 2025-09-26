<!DOCTYPE html>
<html>
<head>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css" />
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.js"></script>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            font-size: 12px;
        }
        .header {
            background-color: #4CAF50;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
        }
        .state-color-1 {
            background-color: #FFD580;
        }
        .state-color-2 {
            background-color: #90EE90;
        }
        .state-color-3 {
            background-color: #F08080;
        }
        .state-color-4 {
            background-color: #ADD8E6;
        }
        .custom-width {
            width: 200px; /* Adjust the width as needed */
        }
        .status-dropdown {
            width: 120px;
            font-size: 12px;
            margin-top: 16px;
        }
        #success-alert {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1050;
            display: none;
        }
        #error-alert {
            position: fixed;
            bottom: 60px; /* Lige over success-alert */
            left: 50%;
            transform: translateX(-50%);
            z-index: 1050;
            display: none;
        }
        .actionButtonOrderconf,.action-button,.additional-info{
            font-size: 12px;
        }
        .update-button{
            font-size: 12px;
            margin-top: 16px;
        }
        .ui-dialog-titlebar-close{
            color: black;
        }
        .select-container {
            display: flex;
            align-items: center;
        }
        .custom-width {
            margin-right: 10px; /* Juster mellemrum mellem elementerne, hvis nødvendigt */
        }
    </style>
</head>
<body>
<div class="header">
    <h1>Godkendelsesværktøj</h1>
</div>
<div class="select-container">
    <div class="custom-width">
        <select class="form-control status-load-dropdown" id="status">
            <option value="">Alle</option>
            <option value="1">Pending</option>
            <option value="3">Rejected</option>
            <option value="4">Reapproved</option>
            <option value="2">Approved</option>
            <option value="5">Manuelt oprettet</option>
        </select>
    </div>
    <div class="custom-width">
        <select class="form-control language-select-dropdown" id="language">
            <option value="1">dansk</option>
            <option value="4">norsk</option>
        </select>
    </div>
</div>
<br>
<table id="myTable" class="display responsive nowrap" style="width:100%">
    <thead>
    <tr>
        <th>Shop Name</th>
        <th>Sælger</th>

        <th>Besked</th>
        <th></th>
        <th></th>
        <th>Status</th>
    </tr>
    </thead>
    <tbody>
    <!-- Data rækker vil blive indsat her -->
    </tbody>
</table>

<div style="display: none;" id="gaveEjValgteDialog"></div>
<!-- Success Alert Message -->
<div id="success-alert" class="alert alert-success alert-dismissible fade show" role="alert">
    <span id="success-message"><strong>Success!</strong> The operation was completed successfully.</span>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<div id="error-alert" class="alert alert-danger alert-dismissible fade show" role="alert" style="display: none;">
    <span id="error-message"><strong>Error!</strong> There was a problem with the operation.</span>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    var APPR_AJAX_URL = "https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/valgshop/approvalportal/";
    var BASE_AJAX_URL = "https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=";
    $(document).ready(function() {
        run();
    });

    async function run() {
        let shops = await loadAllShops("");
        initTable(shops);
        initStatusSelect();
    }

    function initStatusSelect() {
        $(document).on('change', '.status-load-dropdown', async function() {
            let id = $(this).val();
            let shops = await loadAllShops(id);
            initTable(shops);
        });
    }

    function loadAllShops(state) {
        return new Promise(resolve => {
            let postData = {
                state: state,
                country: $("#language").val()
            };
            $.post(APPR_AJAX_URL + "getAllShops", postData, function(res) {
                resolve(res);
            }, "json");
        });
    }

    function initTable(data) {
        let self = this;
        let tableData = [];
        data.data.forEach((item) => {
            let status = "";
            switch (item.attributes.orderdata_approval) {
                case 1:
                    status = "Pending";
                    break;
                case 2:
                    status = "Approved";
                    break;
                case 3:
                    status = "Rejected";
                    break;
                case 4:
                    status = "Reapproved";
                    break;
                case 5:
                    status = "Manuelt oprettet";
                    break;
                case 100:
                    status = "Susanne skal godkende";
                    break;
                default:
                    status = "Unknown";
                    break;
            }

            // Create a dropdown for status

            let statusDropdown = "";
            if(item.attributes.orderdata_approval == 100){
                statusDropdown = status;
            } else
            {
                statusDropdown = `
                <div class="form-group d-flex align-items-center">
                    <div class="custom-width mr-2">
                        <select class="status-dropdown form-control state-color-${item.attributes.orderdata_approval}" id="status_${item.attributes.shop_id}">
                            <option value="1" ${item.attributes.orderdata_approval === 1 ? 'selected' : ''}>Pending</option>
                            <option value="2" ${item.attributes.orderdata_approval === 2 ? 'selected' : ''}>Approved</option>
                            <option value="3" ${item.attributes.orderdata_approval === 3 ? 'selected' : ''}>Rejected</option>
                            <option value="4" ${item.attributes.orderdata_approval === 4 ? 'selected' : ''}>Reapproved</option>
                            <option value="5" ${item.attributes.orderdata_approval === 5 ? 'selected' : ''}>Manuelt oprettet</option>
                        </select>
                    </div>
                    <button class="btn btn-primary update-button" data-id="${item.attributes.shop_id}">Opdater</button>
                </div>
            `;
            }
            let actionButton = `<button class="btn btn-info action-button" data-id="${item.attributes.shop_id}">Vis skema</button>`;
            let actionButtonOrderconf = `<button class="btn btn-warning actionButtonOrderconf" data-id="${item.attributes.shop_id}">Orderbekræftigelse</button>`;

            // Create a textarea for additional information
            let orderdata_approval_note = item.attributes.orderdata_approval_note == null ? "" : item.attributes.orderdata_approval_note;
            let additionalInfoTextarea = `
                <textarea class="form-control additional-info" id="additional-info_${item.attributes.shop_id}" data-id="${item.attributes.shop_id}" rows="3" cols="30" placeholder="Kommentar">${orderdata_approval_note}</textarea>
            `;

            let obj = {
                name: item.attributes.name,
                salesperson:item.attributes.salesperson_code,
                status: statusDropdown,
                action2: actionButtonOrderconf,
                action: actionButton,
                additionalInfo: additionalInfoTextarea
            };
            tableData.push(obj);
        });
        console.log(tableData);

        if ($.fn.DataTable.isDataTable('#myTable')) {
            // Destroy the existing DataTable instance
            $('#myTable').DataTable().destroy();
            // Clear the table body
            $('#myTable tbody').empty();
        }

        $('#myTable').DataTable({
            data: tableData,
            responsive: true,
            paging: false,
            columns: [
                { data: 'name' },
                {data: 'salesperson'},
                { data: 'additionalInfo' },
                { data: 'action2' },
                { data: 'action' },
                { data: 'status' }
            ]
        });


         $(".update-button").unbind('click').click(function() {
             let id = $(this).data('id');
             updateState(id);
         });

        $(".action-button").unbind('click').click(function() {
            let id = $(this).data('id');
            // Perform the desired action with the ID
            console.log(`Button clicked for ID: ${id}`);
            ShowOrdredataMedal(id);
        });

        $(".actionButtonOrderconf").unbind('click').click(function() {
            let id = $(this).data('id');
            // Perform the desired action with the ID
            ShowOrderconfMedal(id);
        });
        $("#language").unbind('change').change(function() {
            self.run()
            
        });

    }

    function ShowOrdredataMedal(shopID) {
        $("#ejvalgteSaveStatus").html("");
        $('#gaveEjValgteDialog').html('<iframe id="shopdata" width="99%" style="height: 99%" src="https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/valgshop/main&saleperson=1&admin=1&shopID=' + shopID + '"></iframe>');
        let w = $(window).width() > 800 ? 800 : $(window).width();
        $('#gaveEjValgteDialog').dialog({
            title: 'Ordredata',
            modal: true,
            width: w,
            height: $(window).height() - 200,
            buttons: {
                Luk: function() {
                    $(this).dialog("close");
                }
            },
            open: function() {
                // Dette fjerner padding og marginer, så dialogen virkelig bliver fuldskærm
                $('.ui-widget-overlay').css('padding', 0);
                $('.ui-widget-overlay').css('margin', 0);
                $('.ui-dialog').css('padding', 0);
                $('.ui-dialog').css('margin', 0);
            }
        });
    }

    function ShowOrderconfMedal(shopID) {
        $("#ejvalgteSaveStatus").html("");
        $('#gaveEjValgteDialog').html('<iframe style="width: 100%; height: 600px; border: none;" src="index.php?rt=unit/valgshop/orderconf/html/' + shopID + '"></iframe>');
        let w = $(window).width() > 800 ? 800 : $(window).width();
        $('#gaveEjValgteDialog').dialog({
            title: 'Orderbekræftigelse',
            modal: true,
            width: w,
            height: $(window).height() - 200,
            buttons: {
                Luk: function() {
                    $(this).dialog("close");
                }
            },
            open: function() {
                // Dette fjerner padding og marginer, så dialogen virkelig bliver fuldskærm
                $('.ui-widget-overlay').css('padding', 0);
                $('.ui-widget-overlay').css('margin', 0);
                $('.ui-dialog').css('padding', 0);
                $('.ui-dialog').css('margin', 0);
            }
        });
    }

    function updateState(shopID) {
        let comment = $("#additional-info_" + shopID).val();
        let status = $('#status_' + shopID).val();

        let postData = {
            shopID: shopID,
            status: status,
            comment: comment
        };
        $.post(APPR_AJAX_URL + "updateState", postData, function(res) {
            if(res.status == 1){
                showSuccessMessage("The update is finished!");
            } else {
                showErrorMessage(res.message);
            }
        }, "json");
    }





    function showSuccessMessage(message) {
        var alert = document.getElementById('success-alert');
        var messageContainer = document.getElementById('success-message');
        messageContainer.innerHTML = '<strong>Success!</strong> ' + message;
        alert.style.display = 'block';
        setTimeout(function() {
            alert.style.display = 'none';
        }, 6000); // 10 seconds
    }
    function showErrorMessage(message) {
        var alert = document.getElementById('error-alert');
        var messageContainer = document.getElementById('error-message');
        messageContainer.innerHTML = '<strong>Error!</strong> ' + message;
        alert.style.display = 'block';
        setTimeout(function() {
            alert.style.display = 'none';
        }, 6000); // 10 sekunder
    }
</script>

</body>
</html>
