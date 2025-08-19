<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bruger Oversigt og Mail Formular</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        .help-button {
            position: fixed;
            right: 20px;
            top: 20px;
            z-index: 1030;
        }
        .content-wrapper {
            padding-top: 60px;
        }
    </style>
</head>
<body>
<!-- Hjælp knap -->
<button type="button" class="btn btn-info help-button" data-bs-toggle="modal" data-bs-target="#helpModal">
    Hjælp
</button>

<div class="container content-wrapper">
    <h2>Bruger Oversigt</h2>
    <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#mailModal">
        Opret ny mail
    </button>
    <table id="userTable" class="table table-striped table-bordered">
        <thead>
        <tr>
            <th>User ID</th>
            <th>Navn</th>
            <th>Email</th>
            <th>Brugernavn</th>
            <th>Kode</th>
            <th>Send status</th>
            <th>Opret tidspunkt</th>
            <th>Send tidspunkt</th>
            <th>Handlinger</th>
        </tr>
        </thead>
        <tbody>
        <!-- Data vil blive indsat her via JavaScript -->
        </tbody>
    </table>
</div>

<!-- Mail Modal -->
<div class="modal fade" id="mailModal" tabindex="-1" aria-labelledby="mailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mailModalLabel">Opret Mail</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="mailForm">
                    <div class="mb-3 row">
                        <div class="col-md-8">
                            <input type="email" class="form-control" id="testMail" placeholder="Angiv test mail" required>
                        </div>
                        <div class="col-md-4">
                            <button type="button" class="btn btn-primary" id="sendTestMail">Send test mail</button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <input type="text" class="form-control" id="subject" placeholder="Angiv subject" required>
                    </div>
                    <div class="mb-3">
                        <textarea id="mailBody" class="form-control"></textarea>
                    </div>
                    <div class="mb-3">
                        <p>Du vælger din gave på denne url: <span id="giftUrl"></span></p>
                        <p>Dit brugernavn er følgende: <span id="username">***</span></p>
                        <p>Dit password er følgende: <span id="password">***</span></p>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Luk</button>
                <button type="button" class="btn btn-primary" id="createMail">Opret mail</button>
            </div>
        </div>
    </div>
</div>

<!-- Hjælp Modal -->
<div class="modal fade" id="helpModal" tabindex="-1" aria-labelledby="helpModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="helpModalLabel">Hjælp</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h6>Sådan bruges systemet:</h6>
                <ol>
                    <li>For at oprette en ny mail, klik på "Opret ny mail" knappen.</li>
                    <li>I mail-formularen kan du udfylde emne og indhold af mailen.</li>
                    <li>Du kan sende en test-mail ved at angive en email-adresse og klikke på "Send test mail".</li>
                    <li>For at redigere en eksisterende mail, klik på "Rediger mail" ud for den pågældende bruger i tabellen.</li>
                    <li>For at sende en mail igen, klik på "Send igen" ud for den pågældende bruger.</li>
                    <li>I tabellen kan du se oprettelsestidspunkt og seneste sendingstidspunkt for hver mail.</li>
                </ol>
                <p>Hvis du har yderligere spørgsmål, kontakt venligst support.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Luk</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function() {
        // Initialiserer Summernote
        $('#mailBody').summernote({
            height: 200,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture']],
                ['view', ['fullscreen', 'codeview', 'help']],
                ['custom', ['centerText']]
            ],
            buttons: {
                centerText: function(context) {
                    var ui = $.summernote.ui;
                    var button = ui.button({
                        contents: '<i class="note-icon-align-center"/>',
                        tooltip: 'Centrer tekst',
                        click: function() {
                            context.invoke('editor.formatBlock', 'div');
                            context.invoke('editor.applyStyle', {'text-align': 'center'});
                        }
                    });
                    return button.render();
                }
            },
            callbacks: {
                onImageUpload: function(files) {
                    for (let i = 0; i < files.length; i++) {
                        resizeAndUploadImage(files[i]);
                    }
                }
            }
        });

        // Event handler for send test mail knap
        $('#sendTestMail').click(function() {
            alert('Test mail sendt til: ' + $('#testMail').val());
        });

        // Event handler for opret mail knap
        $('#createMail').click(function(e) {
            e.preventDefault();
            if($('#mailForm')[0].checkValidity()) {
                alert('Mail oprettet og sendt!');
                $('#mailModal').modal('hide');
            } else {
                $('#mailForm')[0].reportValidity();
            }
        });

        // Generer tilfældige værdier når modalen åbnes
        $('#mailModal').on('show.bs.modal', function (e) {
            $('#giftUrl').text('https://example.com/gift/' + Math.random().toString(36).substring(7));
            $('#username').text(Math.random().toString(36).substring(7));
            $('#password').text(Math.random().toString(36).substring(7));
        });

        // Initialiserer DataTable
        var table = $('#userTable').DataTable({
            columns: [
                { data: "userid" },
                { data: "navn" },
                { data: "email" },
                { data: "brugernavn" },
                { data: "kode" },
                { data: "sendStatus" },
                { data: "opretTidspunkt" },
                { data: "sendTidspunkt" },
                {
                    data: null,
                    render: function(data, type, row) {
                        return '<button class="btn btn-primary btn-sm edit-mail">Rediger mail</button> ' +
                            '<button class="btn btn-secondary btn-sm send-again">Send igen</button>';
                    }
                }
            ],
            language: {
                url: "//cdn.datatables.net/plug-ins/1.10.25/i18n/Danish.json"
            }
        });

        // Eksempel data
        var exampleData = [
            {userid: 1, navn: "John Doe", email: "john@example.com", brugernavn: "johnd", kode: "******", sendStatus: "Sendt", opretTidspunkt: "2024-08-17 10:30", sendTidspunkt: "2024-08-17 10:35"},
            {userid: 2, navn: "Jane Smith", email: "jane@example.com", brugernavn: "janes", kode: "******", sendStatus: "Fejl", opretTidspunkt: "2024-08-17 11:00", sendTidspunkt: "N/A"},
            {userid: 3, navn: "Bob Johnson", email: "bob@example.com", brugernavn: "bobj", kode: "******", sendStatus: "Ikke sendt", opretTidspunkt: "2024-08-17 11:30", sendTidspunkt: "N/A"}
        ];

        // Tilføjer eksempel data til tabellen
        table.rows.add(exampleData).draw();

        // Event listeners for knapper i tabellen
        $('#userTable').on('click', '.edit-mail', function() {
            var data = table.row($(this).closest('tr')).data();
            $('#mailModal').modal('show');
            $('#testMail').val(data.email);
            $('#subject').val('Redigeret mail til ' + data.navn);
            $('#mailBody').summernote('code', '<p>Kære ' + data.navn + ',</p><p>Dette er en redigeret mail.</p>');
        });

        $('#userTable').on('click', '.send-again', function() {
            var data = table.row($(this).closest('tr')).data();
            if(confirm('Er du sikker på, at du vil sende mail igen til ' + data.email + '?')) {
                alert('Mail sendt igen til: ' + data.email);
                // Her ville du normalt opdatere sendTidspunkt og sendStatus
                var rowIndex = table.row($(this).closest('tr')).index();
                var updatedData = {...data, sendStatus: "Sendt", sendTidspunkt: new Date().toLocaleString('da-DK')};
                table.row(rowIndex).data(updatedData).draw();
            }
        });
    });

    function resizeAndUploadImage(file) {
        let reader = new FileReader();
        reader.onload = function(e) {
            let img = new Image();
            img.onload = function() {
                let canvas = document.createElement('canvas');
                let ctx = canvas.getContext('2d');
                let width = img.width;
                let height = img.height;

                let scaleFactor = 1;
                if (width > 600) {
                    scaleFactor = 600 / width;
                }
                if (height > 300) {
                    scaleFactor = Math.min(scaleFactor, 300 / height);
                }

                if (scaleFactor < 1) {
                    width = width * scaleFactor;
                    height = height * scaleFactor;
                }

                canvas.width = width;
                canvas.height = height;
                ctx.drawImage(img, 0, 0, width, height);

                let dataURL = canvas.toDataURL('image/jpeg');
                $('#mailBody').summernote('insertImage', dataURL);
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
</script>
</body>
</html>