<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Status DataTable</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <style>
        body { padding: 20px; }
        .dataTables_wrapper { margin-top: 20px; }
        .over-reserved { color: red; font-weight: bold; }
    </style>
</head>
<body>
<div class="container">
    <h1>Stock Status</h1>
    <div class="dropdown">
        <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
            Dropdown button
        </button>
        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
            <li><a class="dropdown-item" href="#" data-value="25">Tjellesen</a></li>
            <li><a class="dropdown-item" href="#" data-value="20">Rockwool</a></li>
            <li><a class="dropdown-item" href="#" data-value="58">dsv</a></li>
            <li><a class="dropdown-item" href="#" data-value="15">ALK Abello</a></li>
            <li><a class="dropdown-item" href="#" data-value="41">PWC</a></li>
            <li><a class="dropdown-item" href="#" data-value="19">SOLAR</a></li>
            <li><a class="dropdown-item" href="#" data-value="29">Lundbeck</a></li>
        </ul>
    </div>
    <table id="stockTable" class="table table-striped table-bordered">
        <thead>
        <tr>
            <th>SKU</th>
            <th>Varenavn</th>
            <th>Total valgte</th>
            <th>Total Reserveret</th>
            <th>Shop ID</th>
            <th>Yes for overskredet</th>
            <th>Tilgængelige</th>
            <th>Handling</th>
            <th>Aktiv</th>
        </tr>
        </thead>
        <tbody>
        <!-- Data will be inserted here by JavaScript -->
        </tbody>
    </table>
</div>

<!-- Modal -->
<div class="modal fade" id="updateModal" tabindex="-1" aria-labelledby="updateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateModalLabel">Opdater Reserveret Antal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="updateForm">
                    <div class="mb-3">
                        <label for="reservedQuantity" class="form-label">Reserveret Antal:</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="reservedQuantity" name="reservedQuantity" required>
                            <button type="submit" class="btn btn-primary">Opdater</button>
                        </div>
                    </div>
                    <input type="hidden" id="updateSku" name="sku">
                    <input type="hidden" id="updateValgshopModelId" name="valgshop_model_id">
                    <input type="hidden" id="updateShopId" name="shop_id">
                    <input type="hidden" id="updateTotal" name="total">
                </form>
            </div>
        </div>
    </div>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap5.min.js"></script>

<script>
    let dataTable;

    function displayStockStatus(data) {
        let tableBody = '';
        if ($.fn.DataTable.isDataTable('#stockTable')) {
            dataTable.destroy();
        }
        data.forEach(item => {
            tableBody += `
            <tr>
                <td>${item.sku}</td>
                <td>${item.valgshop_model_name}</td>
                <td>${item.total}</td>
                <td class="reserved-quantity">${item.reserved_quantity !== null ? item.reserved_quantity : 'N/A'}</td>
                <td>${item.shop_id}</td>
                <td class="${item.is_over_reserved === 'yes' ? 'over-reserved' : ''}">${item.is_over_reserved}</td>
                <td>${item.difference}</td>
                <td><button class="btn btn-primary btn-sm update-btn"
                    data-sku="${item.sku}"
                    data-reserved="${item.reserved_quantity}"
                    data-valgshop-model-id="${item.valgshop_model_id}"
                    data-shop-id="${item.shop_id}"
                    data-total="${item.total}">Opdater</button></td>
                    <td><input class="sku-monitor" data-valgshop-model-id="${item.valgshop_model_id}" type="checkbox" ${item.active == 1 ? 'checked' : ''} /> </td>
                </tr>

        `;
        });

        $('#stockTable tbody').html(tableBody);

        // Initialize or refresh DataTable


        dataTable = $('#stockTable').DataTable({
            responsive: true,
            order: [[5, 'desc'], [6, 'asc']], // Sort by 'Is Over Reserved' (desc) and then by 'Difference' (asc)
            columnDefs: [
                {
                    type: 'num',
                    targets: 2 // 'Total valgte' column
                },
                {
                    targets: 3, // 'Total Reserveret' column
                    type: 'num',
                    render: function(data, type, row) {
                        if (type === 'sort') {
                            return data === 'N/A' ? -Infinity : parseFloat(data);
                        }
                        return data;
                    }
                }
            ]
        });
    }

    // Fetch data using AJAX
    function fetchStockData(storeview) {

        $.ajax({
            url: 'https://system.gavefabrikken.dk//gavefabrikken_backend/index.php?rt=MagentoOrderStock/getStockStatus',
            method: 'POST',
            data: {storeview:storeview },
            dataType: 'json',
            success: function(response) {
                if (response.status === '1') {

                    displayStockStatus(response.data.result);
                    initEvent()
                } else {
                    alert('Error fetching data: ' + response.message);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                alert('Error fetching data: ' + textStatus);
            }
        });
    }

    $(document).ready(function() {

        fetchStockData(25);

        $('#dropdownMenuButton + .dropdown-menu .dropdown-item').on('click', function(e) {
            e.preventDefault();
            const value = $(this).data('value');
            const text = $(this).text();

            // Fjern active klasse fra alle items
            $('.dropdown-item').removeClass('active');
            // Tilføj active klasse til det valgte item
            $(this).addClass('active');

            onDropdownChange(value, text);
            $('#dropdownMenuButton').text(text);
        });

        // Sæt første element som aktivt ved start (valgfrit)
        $('#dropdownMenuButton + .dropdown-menu .dropdown-item:first').addClass('active');
        const firstItem = $('#dropdownMenuButton + .dropdown-menu .dropdown-item:first');
        $('#dropdownMenuButton').text(firstItem.text());
        onDropdownChange(firstItem.data('value'), firstItem.text());


    });

    function initEvent(){
        // Open modal when update button is clicked
        $(".sku-monitor").unbind("click").on('click', function() {
            const valgshopModelId = $(this).data('valgshop-model-id');

            // Make AJAX call to update active status
            $.ajax({
                url: 'https://system.gavefabrikken.dk//gavefabrikken_backend/index.php?rt=MagentoOrderStock/updateActiveStatus',
                method: 'POST',
                data: {
                    valgshop_model_id: valgshopModelId,
                    active: this.checked ? 1 : 0
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === '1') {
                        // Optional: Show success message
                        console.log('Status updated successfully');
                    } else {
                        alert('Error updating status: ' + response.message);
                        // Revert checkbox state on error
                        $(this).prop('checked', !this.checked);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error updating status: ' + textStatus);
                    // Revert checkbox state on error
                    $(this).prop('checked', !this.checked);
                }
            });
        });

        $(document).on('click', '.update-btn', function() {
            const sku = $(this).data('sku');
            const reservedQuantity = $(this).data('reserved');
            const valgshopModelId = $(this).data('valgshop-model-id');
            const shopId = $(this).data('shop-id');
            const total = $(this).data('total');

            $('#updateSku').val(sku);
            $('#reservedQuantity').val(reservedQuantity);
            $('#updateValgshopModelId').val(valgshopModelId);
            $('#updateShopId').val(shopId);
            $('#updateTotal').val(total);

            $('#updateModal').modal('show');
        });

        // Handle form submission
        $('#updateForm').on('submit', function(e) {
            e.preventDefault();
            const sku = $('#updateSku').val();
            const newReservedQuantity = $('#reservedQuantity').val();
            const valgshopModelId = $('#updateValgshopModelId').val();
            const shopId = $('#updateShopId').val();
            const total = $('#updateTotal').val();

            // AJAX call to update reserved quantity
            $.ajax({
                url: 'https://system.gavefabrikken.dk//gavefabrikken_backend/index.php?rt=MagentoOrderStock/updateReservedQuantity',
                method: 'POST',
                data: {
                    sku: sku,
                    reserved_quantity: newReservedQuantity,
                    valgshop_model_id: valgshopModelId,
                    shop_id: shopId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === '1') {
                        // Update the table cells
                        const rowIndex = dataTable.row($(`button[data-sku="${sku}"]`).closest('tr')).index();

                        // Update Total Reserveret
                        dataTable.cell(rowIndex, 3).data(newReservedQuantity);

                        // Update Yes for overskredet
                        const isOverReserved = parseInt(newReservedQuantity) < parseInt(total) ? 'yes' : 'no';
                        dataTable.cell(rowIndex, 5).data(isOverReserved);

                        // Update Tilgængelige
                        const difference =  parseInt(newReservedQuantity) - parseInt(total);
                        dataTable.cell(rowIndex, 6).data(difference);

                        // Update the button's data attribute
                        $(`button[data-sku="${sku}"]`).data('reserved', newReservedQuantity);

                        // Redraw the table
                        dataTable.draw();

                        // Close the modal
                        $('#updateModal').modal('hide');
                    } else {
                        alert('Error updating data: ' + response.message);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert('Error updating data: ' + textStatus);
                }
            });
        });
    }

    function onDropdownChange(value, text) {

        fetchStockData(value);
        $('#selectedValue').html(`Valgt værdi: ${value}, Tekst: ${text}`);
    }

</script>
</body>
</html>