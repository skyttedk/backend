<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Profile Management</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <style>
        .profile-img {
            width: 50px;
            height: 50px;
            object-fit: cover;
        }
        .action-buttons {
            white-space: nowrap;
        }
        .dataTables_filter {
            margin-bottom: 15px;
        }
        .lang-filter-container {
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
<div class="container py-4">
    <h1>Sales Profile Management</h1>
    <div id="message-container" class="mb-3"></div>

    <!-- Container for the SaleProfile.js to render content -->
    <div id="saleProfileContainer">
        <div class="text-center">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    </div>

    <!-- Modal for Profile Form -->
    <div class="modal fade" id="profileFormModal" tabindex="-1" aria-labelledby="formTitle" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="formTitle">Add New Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="modal-message-container"></div>
                    <form id="profileForm" enctype="multipart/form-data">
                        <input type="hidden" id="profile_id" name="id" value="">

                        <div class="mb-3">
                            <label for="name" class="form-label">Name*</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>

                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="title" name="title">
                        </div>

                        <div class="mb-3">
                            <label for="tel" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="tel" name="tel">
                        </div>

                        <div class="mb-3">
                            <label for="mail" class="form-label">Email*</label>
                            <input type="email" class="form-control" id="mail" name="mail" required>
                        </div>

                        <div class="mb-3">
                            <label for="profile_image" class="form-label">Profile Image</label>
                            <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*">
                            <div id="imagePreview" class="mt-2"></div>
                        </div>

                        <div class="mb-3">
                            <label for="lang" class="form-label">Language</label>
                            <select class="form-control" id="lang" name="lang">
                                <option value="1">Dansk</option>
                                <option value="4">Norsk</option>
                                <option value="5">Svensk</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="cancelProfileBtn">Cancel</button>
                    <button type="button" class="btn btn-success" id="saveProfileBtn">Save</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

<!-- Load and initialize the SaleProfile.js module -->
<script type="module">
    import SaleProfile from '/gavefabrikken_backend/units/apps/sale_profile/js/SaleProfile.js';

    $(document).ready(function() {
        window.saleProfile = new SaleProfile();
        window.saleProfile.init();
    });
</script>
</body>
</html>