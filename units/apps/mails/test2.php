<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mail Formular</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css" rel="stylesheet">
    <style>
        #image-container {
            max-height: 400px;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
        }
        #image-container img {
            max-width: 100%;
            max-height: 100%;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="form-tab" data-bs-toggle="tab" data-bs-target="#form" type="button" role="tab">Formular</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="users-tab" data-bs-toggle="tab" data-bs-target="#users" type="button" role="tab">Brugere</button>
        </li>
    </ul>
    <div class="tab-content" id="myTabContent">
        <div class="tab-pane fade show active" id="form" role="tabpanel">
            <form id="mailForm" class="mt-3">
                <div class="mb-3">
                    <input type="email" class="form-control" id="testMail" placeholder="Angiv test mail" required>
                </div>
                <div class="mb-3">
                    <input type="text" class="form-control" id="subject" placeholder="Angiv subject" required>
                </div>
                <div class="mb-3">
                    <button type="button" class="btn btn-secondary" id="uploadImageBtn">Upload og beskær billede</button>
                </div>
                <div class="mb-3">
                    <textarea id="mailBody" class="form-control"></textarea>
                </div>
                <div class="mb-3">
                    <button type="button" class="btn btn-primary" id="sendTestMail">Send test mail</button>
                    <button type="button" class="btn btn-secondary" id="cancel">Annuller</button>
                    <button type="submit" class="btn btn-success" id="createMail">Opret mail</button>
                </div>
            </form>
        </div>
        <div class="tab-pane fade" id="users" role="tabpanel">
            <div class="mt-3">
                <table id="userTable" class="table table-striped table-bordered">
                    <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Navn</th>
                        <th>Email</th>
                        <th>Brugernavn</th>
                        <th>Kode</th>
                        <th>Send status</th>
                        <th>Handlinger</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal for image upload and crop -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Upload og beskær billede</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="file" id="imageInput" accept="image/*" class="form-control mb-3">
                <div id="image-container"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Luk</button>
                <button type="button" class="btn btn-primary" id="cropImageBtn">Beskær og indsæt</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
<script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>

<script>
    $(document).ready(function() {
        let cropper;

        // Initialize Summernote
        $('#mailBody').summernote({
            height: 200,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ]
        });

        // Initialize DataTable
        $('#userTable').DataTable({
            "ajax": {
                "url": "https://jsonplaceholder.typicode.com/users",
                "dataSrc": function(json) {
                    return json.map(function(user) {
                        return {
                            "userid": user.id,
                            "navn": user.name,
                            "email": user.email,
                            "brugernavn": user.username,
                            "kode": "******",
                            "sendStatus": "Ikke sendt",
                            "actions": '<button class="btn btn-primary btn-sm edit-mail">Rediger mail</button> ' +
                                '<button class="btn btn-secondary btn-sm send-again">Send igen</button>'
                        };
                    });
                }
            },
            "columns": [
                { "data": "userid" },
                { "data": "navn" },
                { "data": "email" },
                { "data": "brugernavn" },
                { "data": "kode" },
                { "data": "sendStatus" },
                { "data": "actions", "orderable": false }
            ]
        });

        // Image upload and crop functionality
        $('#uploadImageBtn').click(function() {
            $('#imageModal').modal('show');
        });

        $('#imageInput').change(function(e) {
            const file = e.target.files[0];
            const reader = new FileReader();

            reader.onload = function(event) {
                $('#image-container').html('<img id="cropperImage" src="' + event.target.result + '">');
                cropper = new Cropper($('#cropperImage')[0], {
                    aspectRatio: NaN,
                    viewMode: 1
                });
            }

            reader.readAsDataURL(file);
        });

        $('#cropImageBtn').click(function() {
            if (cropper) {
                const croppedCanvas = cropper.getCroppedCanvas();
                const croppedImageData = croppedCanvas.toDataURL('image/jpeg');
                $('#mailBody').summernote('insertImage', croppedImageData);
                $('#imageModal').modal('hide');
            }
        });

        // Form submission
        $('#mailForm').submit(function(e) {
            e.preventDefault();
            if (this.checkValidity()) {
                alert('Mail oprettet og sendt!');
            } else {
                this.reportValidity();
            }
        });

        // Other event handlers
        $('#sendTestMail').click(function() {
            alert('Test mail sendt til: ' + $('#testMail').val());
        });

        $('#cancel').click(function() {
            if(confirm('Er du sikker på, at du vil annullere?')) {
                $('#mailForm')[0].reset();
                $('#mailBody').summernote('code', '');
            }
        });

        // Event handlers for DataTable buttons
        $('#userTable').on('click', '.edit-mail', function() {
            var data = $('#userTable').DataTable().row($(this).parents('tr')).data();
            $('#myTab button[data-bs-target="#form"]').tab('show');
            $('#testMail').val(data.email);
            $('#subject').val('Redigeret mail til ' + data.navn);
            $('#mailBody').summernote('code', '<p>Kære ' + data.navn + ',</p><p>Dette er en redigeret mail.</p>');
        });

        $('#userTable').on('click', '.send-again', function() {
            var data = $('#userTable').DataTable().row($(this).parents('tr')).data();
            if(confirm('Er du sikker på, at du vil sende mail igen til ' + data.email + '?')) {
                alert('Mail sendt igen til: ' + data.email);
            }
        });
    });
</script>
</body>
</html>