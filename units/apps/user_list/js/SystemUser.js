var SYSTEMUSER_AJAX_URL = "https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/apps/user_list/";
import Base from 'https://system.gavefabrikken.dk/gavefabrikken_backend/units/cardshop/main/js/base.js';

export default class SystemUser extends Base {
    constructor() {
        super();
        this.profiles = [];
        this.editMode = false;
        this.currentProfileId = 0;
        this.profileModal = null;
        this.dataTable = null;
        this.languageMap = {
            1: "Dansk",
            4: "Norsk",
            5: "Svensk"
        };
        // Base image URL for the workers directory
        this.imageBaseUrl = 'https://presentation.gavefabrikken.dk/presentation/workers/';
        // Base placeholder image URL (absolute path)
        this.placeholderImage = 'https://presentation.gavefabrikken.dk/presentation/workers/placeholder.jpg';
    }

    async init(langId = 1) {
        try {
            // Add custom CSS for larger images and zoom effect
            this.addCustomStyles();

            // Create the image zoom container
            this.createImageZoomContainer();

            // Load all profiles and initialize the UI
            await this.loadAllProfiles();
            this.renderProfileTable();

            // Initialize Bootstrap modal
            this.profileModal = new bootstrap.Modal(document.getElementById('profileFormModal'), {
                backdrop: 'static',
                keyboard: false
            });

            this.setupEventListeners();
            console.log("SystemUser initialized successfully");
        } catch (error) {
            console.error("Error initializing SaleProfile:", error);
            this.showMessage('error', 'Error initializing application');
        }
    }

    // Add custom CSS styles for the application
    addCustomStyles() {
        const css = `
            .profile-img {
                width: 75px; /* 50% larger than the original 50px */
                height: 75px; /* 50% larger than the original 50px */
                object-fit: cover;
                border-radius: 5px;
                cursor: zoom-in;
                transition: transform 0.2s;
            }
            .profile-img:hover {
                transform: scale(1.05);
            }
            .preview-img {
                max-width: 300px; /* 50% larger than the original 200px */
                max-height: 300px; /* 50% larger than the original 200px */
                border-radius: 5px;
                box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            }
            #imageZoomContainer {
                position: fixed;
                display: none;
                z-index: 1000;
                background-color: white;
                border-radius: 8px;
                box-shadow: 0 5px 15px rgba(0,0,0,0.3);
                padding: 10px;
                pointer-events: none;
            }
            .zoomed-img {
                max-width: 350px;
                max-height: 350px;
                border-radius: 5px;
            }
            .image-name {
                text-align: center;
                font-weight: bold;
                margin-top: 8px;
                font-size: 14px;
            }
            .image-title {
                text-align: center;
                font-style: italic;
                margin-top: 4px;
                font-size: 12px;
            }
        `;

        // Add the styles to the document head
        $('<style>').text(css).appendTo('head');
    }

    // Create a container for the zoomed image
    createImageZoomContainer() {
        // Remove any existing container
        $('#imageZoomContainer').remove();

        // Create the container
        $('body').append('<div id="imageZoomContainer"></div>');
    }

    // Set up the image zoom functionality
    setupImageZoom() {
        const self = this;

        // Add hover event to all profile images
        $('.profile-img').off('mouseenter mousemove mouseleave')
            .on('mouseenter', function(e) {
                const imgSrc = $(this).attr('src');
                const name = $(this).attr('alt');
                const row = $(this).closest('tr');
                const title = row.find('td:eq(2)').text(); // Get title from the third column

                // Create content for the zoom container
                let zoomContent = `<img src="${imgSrc}" alt="${name}" class="zoomed-img">`;

                // Add name and title if available
                if (name) {
                    zoomContent += `<div class="image-name">${name}</div>`;
                }
                if (title) {
                    zoomContent += `<div class="image-title">${title}</div>`;
                }

                // Set the content and show the container
                $('#imageZoomContainer').html(zoomContent).show();

                // Position the container on initial hover
                self.positionZoomContainer(e);
            })
            .on('mousemove', function(e) {
                // Update position as mouse moves
                self.positionZoomContainer(e);
            })
            .on('mouseleave', function() {
                // Hide the container when mouse leaves
                $('#imageZoomContainer').hide();
            });
    }

    // Position the zoom container based on mouse position
    positionZoomContainer(e) {
        const container = $('#imageZoomContainer');
        const windowWidth = $(window).width();
        const containerWidth = container.outerWidth();
        const containerHeight = container.outerHeight();

        // Calculate position based on mouse and window size
        let left = e.pageX + 20; // 20px offset from cursor
        let top = e.pageY - containerHeight / 2; // Center vertically with cursor

        // Make sure the container stays within the window
        if (left + containerWidth > windowWidth - 20) {
            left = e.pageX - containerWidth - 20; // Display on left side if not enough space on right
        }

        if (top < 20) {
            top = 20; // Prevent going above the top of the window
        } else if (top + containerHeight > $(window).height() - 20) {
            top = $(window).height() - containerHeight - 20; // Prevent going below the bottom
        }

        // Set the position
        container.css({
            left: left + 'px',
            top: top + 'px'
        });
    }

    async loadAllProfiles() {
        try {
            // Load profiles for each language and combine them
            this.profiles = [];

            // Get profiles for each language (1: Danish, 4: Norwegian, 5: Swedish)
            for (const langId of [1, 4, 5]) {
                const response = await this.makeRequest('getAll', { lang: langId });
                if (response.status === 1 && response.data) {
                    // Process each profile to ensure image URLs are correct
                    const processedProfiles = response.data.map(profile => {
                        return {
                            ...profile,
                            img: this.getImageUrl(profile.img)
                        };
                    });
                    this.profiles = [...this.profiles, ...processedProfiles];
                }
            }

            console.log("All profiles loaded:", this.profiles.length);
        } catch (error) {
            console.error('Error loading profiles:', error);
            this.showMessage('error', 'Error connecting to server');
        }
    }

    /**
     * Get the full image URL from a filename
     */
    getImageUrl(imgFileName) {
        if (!imgFileName) {
            return this.placeholderImage;
        }

        // If it's already a full URL, return it
        if (imgFileName.startsWith('http://') || imgFileName.startsWith('https://')) {
            return imgFileName;
        }

        // Combine base URL with filename
        return this.imageBaseUrl + imgFileName;
    }

    setupEventListeners() {
        const self = this;
        console.log("Setting up event listeners");

        // Add new profile button
        $('#addProfileBtn').off('click').on('click', function(e) {
            e.preventDefault();
            console.log("Add profile button clicked");
            self.showProfileForm();
        });

        // Save profile button
        $('#saveProfileBtn').off('click').on('click', function(e) {
            e.preventDefault();
            console.log("Save profile button clicked");
            self.saveProfile();
        });

        // Cancel button and close buttons for modal
        $('#cancelProfileBtn, #profileFormModal .btn-close').off('click').on('click', function(e) {
            e.preventDefault();
            console.log("Cancel/close button clicked");
            self.hideProfileForm();
        });

        // Image preview
        $('#profile_image').off('change').on('change', function() {
            console.log("Image input changed");
            self.previewImage(this);
        });

        // Language filter
        $('#langFilter').off('change').on('change', function() {
            const selectedLanguage = $(this).val();

            // Apply custom filtering to DataTable
            if (selectedLanguage === 'all') {
                self.dataTable.column(5).search('').draw();
            } else {
                const langName = self.languageMap[selectedLanguage];
                self.dataTable.column(5).search(langName, true, false).draw();
            }

            // Need to reinitialize zoom after table refresh
            setTimeout(() => {
                self.setupImageZoom();
            }, 100);
        });

        // Handle modal hidden event
        $('#profileFormModal').off('hidden.bs.modal').on('hidden.bs.modal', function () {
            console.log("Modal hidden event triggered");
            // Reset form when modal is closed
            $('#profileForm')[0].reset();
            $('#imagePreview').empty();
        });

        // Handle DataTables page changes to reinitialize zoom
        $('#profilesTable').on('page.dt', function() {
            setTimeout(() => {
                self.setupImageZoom();
            }, 100);
        });

        // Handle DataTables search to reinitialize zoom
        $('.dataTables_filter input').on('keyup', function() {
            setTimeout(() => {
                self.setupImageZoom();
            }, 100);
        });
    }

    setupTableEventListeners() {
        const self = this;

        // Edit profile buttons - use delegation for dynamic content
        $('#profilesTable').off('click', '.edit-profile-btn').on('click', '.edit-profile-btn', function(e) {
            e.preventDefault();
            const profileId = $(this).data('id');
            console.log("Edit button clicked for profile:", profileId);
            self.editProfile(profileId);
        });

        // Delete profile buttons - use delegation for dynamic content
        $('#profilesTable').off('click', '.delete-profile-btn').on('click', '.delete-profile-btn', function(e) {
            e.preventDefault();
            const profileId = $(this).data('id');
            console.log("Delete button clicked for profile:", profileId);
            if (confirm('Are you sure you want to delete this profile?')) {
                self.deleteProfile(profileId);
            }
        });

        // Set up image zoom after table is rendered
        this.setupImageZoom();
    }

    renderProfileTable() {
        console.log("Rendering profile table");

        // Create the basic table structure with language filter
        let html = `
            <div class="row">
                <div class="col-md-6 mb-3">
                    <button id="addProfileBtn" class="btn btn-primary">Add New Profile</button>
                </div>
                <div class="col-md-6 mb-3 lang-filter-container text-md-end">
                    <label for="langFilter" class="me-2">Filter by language:</label>
                    <select id="langFilter" class="form-select d-inline-block w-auto">
                        <option value="all">All Languages</option>
                        <option value="1">Dansk</option>
                        <option value="4">Norsk</option>
                        <option value="5">Svensk</option>
                    </select>
                </div>
            </div>
            
            <table id="profilesTable" class="table table-striped table-bordered w-100">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Title</th>
                        <th>Tel</th>
                        <th>Email</th>
                        <th>Language</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>`;

        // Add rows for each profile
        this.profiles.forEach(profile => {
            const imgSrc = this.getImageUrl(profile.img);
            const langName = this.languageMap[profile.lang] || profile.lang;

            html += `
            <tr>
                <td><img src="${imgSrc}" alt="${profile.name || ''}" class="profile-img"></td>
                <td>${profile.name || ''}</td>
                <td>${profile.title || ''}</td>
                <td>${profile.tel || ''}</td>
                <td>${profile.mail || ''}</td>
                <td>${langName}</td>
                <td class="action-buttons">
                    <button class="btn btn-sm btn-info edit-profile-btn" data-id="${profile.id}">Edit</button>
                    <button class="btn btn-sm btn-danger delete-profile-btn" data-id="${profile.id}">Delete</button>
                </td>
            </tr>`;
        });

        html += `
                </tbody>
            </table>`;

        $('#systemUserContainer').html(html);

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
        this.dataTable = $('#profilesTable').DataTable({
            responsive: true,
            language: {
                search: "Search profiles:",
                lengthMenu: "Show _MENU_ profiles per page",
                info: "Showing _START_ to _END_ of _TOTAL_ profiles",
                emptyTable: "No profiles available",
                zeroRecords: "No matching profiles found"
            },
            columnDefs: [
                { orderable: false, targets: [0, 6] }, // Disable sorting on image and actions columns
                { searchable: false, targets: [0, 6] } // Disable searching on image and actions columns
            ],
            order: [[1, 'asc']], // Sort by name by default
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50, 100],
            drawCallback: () => {
                // Reinitialize image zoom when table is redrawn
                this.setupImageZoom();
            }
        });
    }

    showProfileForm(profile = null) {
        console.log("Showing profile form", profile ? "for editing" : "for new profile");
        // Reset form
        $('#profileForm')[0].reset();
        $('#imagePreview').empty();

        if (profile) {
            // Edit mode
            this.editMode = true;
            this.currentProfileId = profile.id;
            $('#formTitle').text('Edit Profile');
            $('#profile_id').val(profile.id);
            $('#name').val(profile.name || '');
            $('#title').val(profile.title || '');
            $('#tel').val(profile.tel || '');
            $('#mail').val(profile.mail || '');
            $('#lang').val(profile.lang || '1');

            // Show image preview if exists with ensured valid URL
            if (profile.img) {
                const imgSrc = this.getImageUrl(profile.img);
                $('#imagePreview').html(`<img src="${imgSrc}" alt="Preview" class="preview-img">`);
            }
        } else {
            // Add mode
            this.editMode = false;
            this.currentProfileId = 0;
            $('#formTitle').text('Add New Profile');
        }

        // Show modal
        try {
            if (this.profileModal) {
                this.profileModal.show();
                console.log("Modal shown");
            } else {
                console.error("Modal instance not initialized");
                // Fallback to direct jQuery if Bootstrap modal isn't working
                $('#profileFormModal').modal('show');
            }
        } catch (error) {
            console.error("Error showing modal:", error);
            // Fallback
            $('#profileFormModal').modal('show');
        }
    }

    hideProfileForm() {
        console.log("Hiding profile form");
        // Hide modal
        try {
            if (this.profileModal) {
                this.profileModal.hide();
            } else {
                // Fallback
                $('#profileFormModal').modal('hide');
            }
        } catch (error) {
            console.error("Error hiding modal:", error);
            // Fallback
            $('#profileFormModal').modal('hide');
        }
    }

    previewImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();

            reader.onload = function(e) {
                $('#imagePreview').html(`<img src="${e.target.result}" alt="Preview" class="preview-img">`);
            }

            reader.readAsDataURL(input.files[0]);
        }
    }

    async saveProfile() {
        console.log("Saving profile...");
        if (!this.validateForm()) {
            return;
        }

        const formData = new FormData($('#profileForm')[0]);
        const method = this.editMode ? 'update' : 'create';

        try {
            // Use FormData for file uploads
            const response = await this.makeFormRequest(method, formData);

            if (response.status === 1) {
                this.showMessage('success', this.editMode ? 'Profile updated successfully' : 'Profile created successfully');
                this.hideProfileForm();
                // Reload profiles and redraw table
                await this.loadAllProfiles();
                this.refreshTable();
            } else {
                this.showMessage('error', response.message || 'Failed to save profile');
            }
        } catch (error) {
            console.error('Error saving profile:', error);
            this.showMessage('error', 'Error connecting to server');
        }
    }

    async editProfile(profileId) {
        console.log("Editing profile:", profileId);
        try {
            const response = await this.makeRequest('getOne', { id: profileId });

            if (response.status === 1 && response.data) {
                // Process the profile to get full image URL
                response.data.img = this.getImageUrl(response.data.img);
                this.showProfileForm(response.data);
            } else {
                this.showMessage('error', 'Failed to load profile details');
            }
        } catch (error) {
            console.error('Error loading profile details:', error);
            this.showMessage('error', 'Error connecting to server');
        }
    }

    async deleteProfile(profileId) {
        console.log("Deleting profile:", profileId);
        try {
            const response = await this.makeRequest('delete', { id: profileId });

            if (response.status === 1) {
                this.showMessage('success', 'Profile deleted successfully');
                // Reload profiles and redraw table
                await this.loadAllProfiles();
                this.refreshTable();
            } else {
                this.showMessage('error', response.message || 'Failed to delete profile');
            }
        } catch (error) {
            console.error('Error deleting profile:', error);
            this.showMessage('error', 'Error connecting to server');
        }
    }

    refreshTable() {
        // Option 1: Completely re-render the table
        this.renderProfileTable();

        // Add this line to reattach all event listeners including the Add button
        this.setupEventListeners();
    }

    validateForm() {
        // Basic validation
        const name = $('#name').val().trim();
        const email = $('#mail').val().trim();

        if (!name) {
            this.showMessage('error', 'Name is required');
            return false;
        }

        if (!email) {
            this.showMessage('error', 'Email is required');
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