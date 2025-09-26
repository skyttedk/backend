<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System User Management</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <style>
        .action-buttons {
            white-space: nowrap;
        }
        .dataTables_filter {
            margin-bottom: 15px;
        }
        .user-info {
            font-size: 0.9em;
            color: #666;
        }
    </style>
</head>
<body>
<div class="container py-4">
    <h1>System User Management</h1>
    <div id="message-container" class="mb-3"></div>

    <!-- Container for the SystemUser.js to render content -->
    <div id="systemUserContainer">
        <div class="text-center">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    </div>

    <!-- Modal for User Form -->
    <div class="modal fade" id="userFormModal" tabindex="-1" aria-labelledby="formTitle" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="formTitle">Add New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="modal-message-container"></div>
                    <form id="userForm">
                        <input type="hidden" id="user_id" name="id" value="">

                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="name" name="name">
                        </div>

                        <div class="mb-3">
                            <label for="username" class="form-label">Username*</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Password*</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>

                        <div class="mb-3">
                            <label for="salespersoncode" class="form-label">Salesperson Code</label>
                            <select class="form-control" id="salespersoncode" name="salespersoncode">
                                <option value="">Ikke angivet</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="userlevel" class="form-label">User Level</label>
                            <select class="form-control" id="userlevel" name="userlevel">
                                <option value="1">1 - Standard User</option>
                                <option value="2">2 - Advanced User</option>
                                <option value="3">3 - Administrator</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="active" name="active" checked>
                                <label class="form-check-label" for="active">Active</label>
                            </div>
                        </div>

                        <!-- Permissions Section -->
                        <div id="permissionsSection" style="display: none;">
                            <hr>
                            <h6 class="mb-3">User Permissions</h6>

                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <tr class="table-warning">
                                        <td><strong>SÆLGERADGANG</strong></td>
                                        <td><input type="checkbox" class="systemUserAccess" id="tabAccess_1000" data-id="1000" /></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Valgshop</strong></td>
                                        <td><input type="checkbox" class="systemUserAccess" id="tabAccess_100" data-id="100" /></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Gavekort-shops</strong></td>
                                        <td><input type="checkbox" class="systemUserAccess" id="tabAccess_90" data-id="90" /></td>
                                    </tr>
                                    <tr>
                                        <td><strong>GaveAdmin</strong></td>
                                        <td><input type="checkbox" class="systemUserAccess" id="tabAccess_80" data-id="80" /></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Tilbud</strong></td>
                                        <td><input type="checkbox" class="systemUserAccess" id="tabAccess_70" data-id="70" /></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Salgsportal</strong></td>
                                        <td><input type="checkbox" class="systemUserAccess" id="tabAccess_71" data-id="71" /></td>
                                    </tr>
                                    <tr>
                                        <td><strong>System</strong></td>
                                        <td><input type="checkbox" class="systemUserAccess" id="tabAccess_60" data-id="60" /></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Lager-admin</strong></td>
                                        <td><input type="checkbox" class="systemUserAccess" id="tabAccess_50" data-id="50" /></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Shopboard</strong></td>
                                        <td><input type="checkbox" class="systemUserAccess" id="tabAccess_120" data-id="120" /></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Infoboard</strong></td>
                                        <td><input type="checkbox" class="systemUserAccess" id="tabAccess_110" data-id="110" /></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Arkiv</strong></td>
                                        <td><input type="checkbox" class="systemUserAccess" id="tabAccess_45" data-id="45" /></td>
                                    </tr>
                                </table>

                                <fieldset>
                                    <legend><strong>Valgshop</strong></legend>
                                    <table class="table table-sm">
                                        <tr>
                                            <td>Stamdata</td><td><input type="checkbox" class="systemUserAccess" id="tabAccess_1" data-id="1" /></td>
                                            <td>Forside</td><td><input type="checkbox" class="systemUserAccess" id="tabAccess_2" data-id="2" /></td>
                                        </tr>
                                        <tr>
                                            <td>Gaver</td><td><input type="checkbox" class="systemUserAccess" id="tabAccess_3" data-id="3" /></td>
                                            <td>Indstillinger</td><td><input type="checkbox" class="systemUserAccess" id="tabAccess_4" data-id="4" /></td>
                                        </tr>
                                        <tr>
                                            <td>felt Definition</td><td><input type="checkbox" class="systemUserAccess" id="tabAccess_5" data-id="5" /></td>
                                            <td>Brugerindløsning</td><td><input type="checkbox" class="systemUserAccess" id="tabAccess_6" data-id="6" /></td>
                                        </tr>
                                        <tr>
                                            <td>Rapporter</td><td><input type="checkbox" class="systemUserAccess" id="tabAccess_7" data-id="7" /></td>
                                            <td>Lagerovervågning</td><td><input type="checkbox" class="systemUserAccess" id="tabAccess_8" data-id="8" /></td>
                                        </tr>
                                    </table>
                                </fieldset>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="cancelUserBtn">Cancel</button>
                    <button type="button" class="btn btn-success" id="saveUserBtn">Save</button>
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

<!-- Load and initialize the SystemUser.js module -->
<script type="module">
    import SystemUser from '/gavefabrikken_backend/units/apps/user_list/js/SystemUser.js';

    $(document).ready(function() {
        window.systemUser = new SystemUser();
        window.systemUser.init();
    });
</script>

<script>
    $( function() {
        $( ".systemUserAccess" ).change(function() {
            systemUser.permissionController($(this).attr("data-id") );
        });
    } );
</script>
</body>
</html>