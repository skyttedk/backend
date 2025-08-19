<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Brugertabel</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        .action-buttons i {
            cursor: pointer;
            margin: 0 5px;
            font-size: 1.1rem;
        }
        .table-responsive {
            overflow-x: auto;
        }
        .bi-save { color: #198754; }
        .bi-clock-history { color: #0d6efd; }
        .bi-gift { color: #dc3545; }
        .bi-printer { color: #6c757d; }
        .bi-trash { color: #dc3545; }
    </style>
</head>
<body>
<div class="container-fluid mt-4">
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-dark">
            <tr>
                <th>Navn</th>
                <th>Brugernavn</th>
                <th>Password</th>
                <th>Email</th>
                <th>Mobil nr.</th>
                <th>Adresse</th>
                <th>Postnummer</th>
                <th>By</th>
                <th>Pakkeshop Adresse</th>
                <th>Pakkeshop ID</th>
                <th>Handlinger</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>John Doe</td>
                <td>johndoe</td>
                <td>********</td>
                <td>john@example.com</td>
                <td>12345678</td>
                <td>Hovedgaden 123</td>
                <td>2100</td>
                <td>København Ø</td>
                <td>Nørregade 45</td>
                <td>PS123</td>
                <td class="action-buttons">
                    <i class="bi bi-save" title="Gem"></i>
                    <i class="bi bi-clock-history" title="Vis tidligere valg"></i>
                    <i class="bi bi-gift" title="Ændre gave"></i>
                    <i class="bi bi-printer" title="Print"></i>
                    <i class="bi bi-trash" title="Slet"></i>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script>
    $(document).ready(function() {
        // Gem handling
        $('.bi-save').click(function() {
            alert('Gemmer ændringer...');
        });

        // Vis tidligere valg handling
        $('.bi-clock-history').click(function() {
            alert('Viser tidligere valg...');
        });

        // Ændre gave handling
        $('.bi-gift').click(function() {
            alert('Ændrer gave...');
        });

        // Print handling
        $('.bi-printer').click(function() {
            alert('Forbereder print...');
        });

        // Slet handling
        $('.bi-trash').click(function() {
            if(confirm('Er du sikker på, at du vil slette denne række?')) {
                alert('Sletter række...');
            }
        });
    });
</script>
</body>
</html>