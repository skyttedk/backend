<!DOCTYPE html>
<html>
<head>
    <title>Shop Tabel</title>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.js"></script>
</head>
<body>
<table id="shopTable" class="display">
    <thead>
    <tr>
        <th>Shop ID</th>
        <th>Shop Navn</th>
        <th>Sælger</th>
        <th>Ansvarlig</th>
        <th>Lokation (Pakkeri)</th>
        <th>Antal Varenr</th>
        <th>Antal Gaver</th>
        <th>Status</th>
        <th>Noter</th>
        <th>Leveringsdato</th>
        <th>Action</th>
    </tr>
    </thead>
    <tbody>
    <!-- Rækker vil blive tilføjet her -->
    </tbody>
</table>
<!-- Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <form id="editForm">
            <!-- Form fields vil blive tilføjet her -->
        </form>
    </div>
</div>
</body>
</html>

<script>
    var data = generateDummyData(10);

    $(document).ready(function(){
        // Initialiser DataTables
        var table = $('#shopTable').DataTable({
            data: data,
            columns: [
                { data: 'shopId', title: 'Shop ID' },
                { data: 'shopName', title: 'Shop Navn' },
                { data: 'seller', title: 'Sælger' },
                { data: 'responsible', title: 'Ansvarlig' },
                { data: 'location', title: 'Lokation (Pakkeri)'},
                { data: 'itemNumbers', title: 'Antal Varenr' },
                { data: 'giftNumbers', title: 'Antal Gaver' },
                { data: 'status', title: 'Status' },
                { data: 'notes', title: 'Noter' },
                { data: 'deliveryDate', title: 'Leveringsdato' },
                {
                    data: null,
                    title: 'Handlinger',
                    render: function(data, type, row) {
                        return '<button class="edit-btn">Rediger</button>';
                    }
                }
            ]
        });
        $('#shopTable').on('click', '.edit-btn', function() {

            const rowData = $('#shopTable').DataTable().row($(this).parents('tr')).data();
            openEditModal(rowData);
        });

        function openEditModal(rowData) {
            console.log(rowData)
            // Opdater modalen med data fra den valgte række
            const form = $('#editForm');
            form.empty();
            for (const [key, value] of Object.entries(rowData)) {
                form.append(`<label for="${key}">${key}</label>`);
                form.append(`<input type="text" id="${key}" name="${key}" value="${value}"><br><br>`);
            }
            form.append('<input type="submit" value="Opdater">');

            // Vis modalen
            const modal = $('#editModal');
            $('#editModal').show();
        }

// Luk modalen når brugeren klikker udenfor
        // Luk modalen når brugeren klikker udenfor
        $('.close').click(function() {
            $('#editModal').hide();
        });

// Opdater data når formen indsendes
        $('#editForm').submit(function(e) {
            e.preventDefault();
            // ... opdater din data og DataTables her ...
        });

    });

    function generateDummyData(count) {
        const dummyData = [];
        const locations = ['Pakkeri A', 'Pakkeri B', 'Pakkeri C'];
        const statuses = ['ikke klar', 'klar', 'pakning i gang', 'pakning færdig'];

        for (let i = 1; i <= count; i++) {
            dummyData.push({
                shopId: i,
                shopName: 'Butik ' + i,
                seller: 'Sælger ' + i,
                responsible: 'Ansvarlig ' + i,
                location: locations[Math.floor(Math.random() * locations.length)],
                itemNumbers: Math.floor(Math.random() * 100),
                giftNumbers: Math.floor(Math.random() * 10),
                status: statuses[Math.floor(Math.random() * statuses.length)],
                notes: 'Note ' + i,
                deliveryDate: `2023-${String(Math.floor(Math.random() * 12) + 1).padStart(2, '0')}-${String(Math.floor(Math.random() * 28) + 1).padStart(2, '0')}`
            });
        }

        return dummyData;
    }


</script>