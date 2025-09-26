var SYSTEMUSER_AJAX_URL = "https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/apps/user_list/";
import Base from 'https://system.gavefabrikken.dk/gavefabrikken_backend/units/cardshop/main/js/base.js';

export default class SystemUser extends Base {
    constructor() {
        super();
        this.users = [];
        this.editMode = false;
        this.currentUserId = 0;
        this.userModal = null;
        this.dataTable = null;
        this.selectedUserId = "";
        this.salespersonOptions = "";
    }

    async init() {
        try {
            console.log("SystemUser initialized successfully");

            // Load salesperson options first
            await this.loadSalespersonOptions();

            // Load users and initialize the UI
            await this.loadAllUsers();
            this.renderUserTable();

            // Initialize Bootstrap modal
            this.userModal = new bootstrap.Modal(document.getElementById('userFormModal'), {
                backdrop: 'static',
                keyboard: false
            });

            this.setupEventListeners();

        } catch (error) {
            console.error("Error initializing SystemUser:", error);
            this.showMessage('error', 'Error initializing application');
        }
    }

    async loadSalespersonOptions() {
        try {
            // This would normally load from Navision, but for now we'll provide a basic fallback
            // In production, you'd need to implement the Navision API call similar to the original
            this.salespersonOptions = "<option value=\"\">Ikke angivet</option>";

            // You can add static options for now or implement the Navision API call
            this.salespersonOptions += "<option value=\"TEST01\">TEST01: Test Salesperson</option>";
            this.salespersonOptions += "<option value=\"TEST02\">TEST02: Another Salesperson</option>";

        } catch (error) {
            console.error('Error loading salesperson options:', error);
            this.salespersonOptions = "<option value=''> - Fejl, kunne ikke hente fra navision!</option>";
        }
    }

    async loadAllUsers() {
        try {
            const response = await this.makeRequest('getAll');
            if (response.status === 1 && response.data) {
                this.users = response.data;
                console.log("Users loaded:", this.users.length);
            }
        } catch (error) {
            console.error('Error loading users:', error);
            this.showMessage('error', 'Error connecting to server');
        }
    }

    setupEventListeners() {
        const self = this;
        console.log("Setting up event listeners");

        // Add new user button
        $('#addUserBtn').off('click').on('click', function(e) {
            e.preventDefault();
            console.log("Add user button clicked");
            self.showUserForm();
        });

        // Save user button
        $('#saveUserBtn').off('click').on('click', function(e) {
            e.preventDefault();
            console.log("Save user button clicked");
            self.saveUser();
        });

        // Cancel button and close buttons for modal
        $('#cancelUserBtn, #userFormModal .btn-close').off('click').on('click', function(e) {
            e.preventDefault();
            console.log("Cancel/close button clicked");
            self.hideUserForm();
        });

        // Handle modal hidden event
        $('#userFormModal').off('hidden.bs.modal').on('hidden.bs.modal', function () {
            console.log("Modal hidden event triggered");
            // Reset form when modal is closed
            $('#userForm')[0].reset();
            // Hide permissions section
            $('#permissionsSection').hide();
        });


        // Handle DataTables page changes
        $('#usersTable').on('page.dt', function() {
            setTimeout(() => {
                self.setupTableEventListeners();
            }, 100);
        });
    }

    setupTableEventListeners() {
        const self = this;

        // Edit user buttons - use delegation for dynamic content (now image elements)
        $('#usersTable').off('click', '.edit-user-btn').on('click', '.edit-user-btn', function(e) {
            e.preventDefault();
            const userId = $(this).data('id');
            console.log("Edit button clicked for user:", userId);
            self.editUser(userId);
        });

        // Delete user buttons - use delegation for dynamic content (now image elements)
        $('#usersTable').off('click', '.delete-user-btn').on('click', '.delete-user-btn', function(e) {
            e.preventDefault();
            const userId = $(this).data('id');
            console.log("Delete button clicked for user:", userId);
            if (confirm('Er du sikker, vil du slette brugeren?')) {
                self.deleteUser(userId);
            }
        });

    }

    renderUserTable() {
        console.log("Rendering user table");

        // Create the basic table structure
        let html = `
            <div class="row">
                <div class="col-md-6 mb-3">
                    <button id="addUserBtn" class="btn btn-primary">Add New User</button>
                </div>
            </div>

            <table id="usersTable" class="table table-striped table-bordered w-100">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Salesperson Code</th>
                        <th>User Level</th>
                        <th>Active</th>
                        <th>Last Login</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>`;

        // Add rows for each user
        this.users.forEach(user => {
            const activeStatus = user.active == 1 ?
                '<span class="badge bg-success">Active</span>' :
                '<span class="badge bg-danger">Inactive</span>';

            const lastLogin = user.last_login ?
                new Date(user.last_login).toLocaleDateString() :
                '<span class="text-muted">Never</span>';

            html += `
            <tr>
                <td>${user.name || ''}</td>
                <td><strong>${user.username || ''}</strong></td>
                <td>${user.salespersoncode || '<span class="text-muted">Not set</span>'}</td>
                <td>Level ${user.userlevel || 1}</td>
                <td>${activeStatus}</td>
                <td>${lastLogin}</td>
                <td class="action-buttons">
                    <button class="btn btn-sm btn-info edit-user-btn" data-id="${user.id}">Edit</button>
                    <button class="btn btn-sm btn-danger delete-user-btn" data-id="${user.id}">Delete</button>
                </td>
            </tr>`;
        });

        html += `
                </tbody>
            </table>`;

        $('#systemUserContainer').html(html);

        // Update salesperson dropdown in modal
        $('#salespersoncode').html(this.salespersonOptions);

        // Initialize DataTable
        this.initializeDataTable();

        // Setup event listeners for edit/delete buttons
        this.setupTableEventListeners();
    }

    initializeDataTable() {
        // Destroy existing DataTable if it exists
        if (this.dataTable) {
            this.dataTable.destroy();
        }

        // Initialize DataTable with configuration
        this.dataTable = $('#usersTable').DataTable({
            responsive: true,
            language: {
                search: "Search users:",
                lengthMenu: "Show _MENU_ users per page",
                info: "Showing _START_ to _END_ of _TOTAL_ users",
                emptyTable: "No users available",
                zeroRecords: "No matching users found"
            },
            columnDefs: [
                { orderable: false, targets: [6] }, // Disable sorting on actions column
                { searchable: false, targets: [6] } // Disable searching on actions column
            ],
            order: [[1, 'asc']], // Sort by username by default
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50, 100]
        });
    }

    showUserForm(user = null) {
        console.log("Showing user form", user ? "for editing" : "for new user");
        // Reset form
        $('#userForm')[0].reset();

        // Reset all permission checkboxes
        $('.systemUserAccess').prop('checked', false);

        if (user) {
            // Edit mode
            this.editMode = true;
            this.currentUserId = user.id;
            this.selectedUserId = user.id;
            $('#formTitle').text('Edit User');
            $('#user_id').val(user.id);
            $('#name').val(user.name || '');
            $('#username').val(user.username || '');
            $('#password').prop('required', false); // Password not required for edit
            $('#salespersoncode').val(user.salespersoncode || '');
            $('#userlevel').val(user.userlevel || '1');
            $('#active').prop('checked', user.active == 1);

            // Show permissions section for existing users
            $('#permissionsSection').show();

            // Load permissions for this user
            this.loadPermission();
        } else {
            // Add mode
            this.editMode = false;
            this.currentUserId = 0;
            this.selectedUserId = "";
            $('#formTitle').text('Add New User');
            $('#password').prop('required', true); // Password required for new user

            // Hide permissions section for new users
            $('#permissionsSection').hide();
        }

        // Show modal
        try {
            if (this.userModal) {
                this.userModal.show();
                console.log("Modal shown");
            } else {
                console.error("Modal instance not initialized");
                $('#userFormModal').modal('show');
            }
        } catch (error) {
            console.error("Error showing modal:", error);
            $('#userFormModal').modal('show');
        }
    }

    hideUserForm() {
        console.log("Hiding user form");
        try {
            if (this.userModal) {
                this.userModal.hide();
            } else {
                $('#userFormModal').modal('hide');
            }
        } catch (error) {
            console.error("Error hiding modal:", error);
            $('#userFormModal').modal('hide');
        }
    }

    async saveUser() {
        console.log("Saving user...");
        if (!this.validateForm()) {
            return;
        }

        const formData = new FormData($('#userForm')[0]);
        const method = this.editMode ? 'update' : 'create';

        // Convert checkbox value
        formData.set('active', $('#active').is(':checked') ? '1' : '0');

        try {
            const response = await this.makeFormRequest(method, formData);

            if (response.status === 1) {
                this.showMessage('success', this.editMode ? 'User updated successfully' : 'User created successfully');
                this.hideUserForm();
                // Reload users and redraw table
                await this.loadAllUsers();
                this.refreshTable();
            } else {
                this.showMessage('error', response.message || 'Failed to save user');
            }
        } catch (error) {
            console.error('Error saving user:', error);
            this.showMessage('error', 'Error connecting to server');
        }
    }

    async editUser(userId) {
        console.log("Editing user:", userId);
        try {
            const response = await this.makeRequest('getOne', { id: userId });

            if (response.status === 1 && response.data) {
                this.showUserForm(response.data);
            } else {
                this.showMessage('error', 'Failed to load user details');
            }
        } catch (error) {
            console.error('Error loading user details:', error);
            this.showMessage('error', 'Error connecting to server');
        }
    }

    async deleteUser(userId) {
        console.log("Deleting user:", userId);
        try {
            const response = await this.makeRequest('delete', { id: userId });

            if (response.status === 1) {
                this.showMessage('success', 'User deleted successfully');
                // Reload users and redraw table
                await this.loadAllUsers();
                this.refreshTable();
            } else {
                this.showMessage('error', response.message || 'Failed to delete user');
            }
        } catch (error) {
            console.error('Error deleting user:', error);
            this.showMessage('error', 'Error connecting to server');
        }
    }


    loadPermission() {
        const data = {};
        data['systemuser_id'] = this.selectedUserId;

        // This would need to connect to the tab controller for loading permissions
        // For now, we'll leave it as a placeholder
        console.log('Loading permissions for user:', this.selectedUserId);

        // You would call: this.makeRequest('tab/loadPermission', data)
        // and then call this.updatePermissionForm(response)
    }

    updatePermissionForm(response) {
        console.log(response);
        if (response && response.data) {
            response.data.forEach(permission => {
                const checkboxId = "#tabAccess_" + permission.attributes.tap_id;
                $(checkboxId).prop('checked', true);
            });
        }
    }

    permissionController(id) {
        const data = {};
        data['systemuser_id'] = this.selectedUserId;
        data['tap_id'] = id;

        if ($('#tabAccess_'+id).is(':checked')) {
            // Create permission - would need tab controller
            console.log('Creating permission for tab:', id);
            // this.makeRequest('tab/createPermission', data)
        } else {
            // Remove permission - would need tab controller
            console.log('Removing permission for tab:', id);
            // this.makeRequest('tab/getId', data).then(response => this.removePermission(response))
        }
    }

    refreshTable() {
        // Completely re-render the table
        this.renderUserTable();
        // Reattach all event listeners
        this.setupEventListeners();
    }

    validateForm() {
        // Basic validation
        const username = $('#username').val().trim();
        const password = $('#password').val().trim();

        if (!username) {
            this.showMessage('error', 'Username is required');
            return false;
        }

        if (!this.editMode && !password) {
            this.showMessage('error', 'Password is required for new users');
            return false;
        }

        return true;
    }

    showMessage(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        $('#message-container, #modal-message-container').html(`<div class="alert ${alertClass}">${message}</div>`);

        // Auto hide after 5 seconds
        setTimeout(() => {
            $('#message-container, #modal-message-container').empty();
        }, 5000);
    }

    makeRequest(action, data = {}) {
        return new Promise((resolve, reject) => {
            $.post(SYSTEMUSER_AJAX_URL + action, data, function(response) {
                try {
                    if (typeof response === 'string') {
                        response = JSON.parse(response);
                    }
                    resolve(response);
                } catch (error) {
                    reject(error);
                }
            }, 'json').fail(function(xhr, status, error) {
                reject(error);
            });
        });
    }

    makeFormRequest(action, formData) {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: SYSTEMUSER_AJAX_URL + action,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    try {
                        if (typeof response === 'string') {
                            response = JSON.parse(response);
                        }
                        resolve(response);
                    } catch (error) {
                        reject(error);
                    }
                },
                error: function(xhr, status, error) {
                    reject(error);
                }
            });
        });
    }
}