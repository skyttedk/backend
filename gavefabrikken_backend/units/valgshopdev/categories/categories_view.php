<?php
echo $_GET["shopID"];

?>

<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kategori Administration</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .hidden { display: none; }
        .error-message { color: #dc3545; font-size: 0.85rem; margin-top: 0.25rem; }
        .table-container { margin-top: 2rem; }
        .actions-column { width: 150px; }
        .nav-tabs { margin-bottom: 1rem; }
    </style>
</head>
<body>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Kategori Administration</h1>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#categoryModal">
            <i class="bi bi-plus-circle"></i> Opret ny kategori
        </button>
    </div>

    <!-- Alert box til feedback -->
    <div class="alert alert-success hidden" role="alert" id="feedbackAlert"></div>

    <!-- Tabel med kategorier -->
    <div class="table-container">
        <h2>Kategoriliste</h2>
        <table class="table table-striped table-hover">
            <thead>
            <tr>
                <th>ID</th>
                <th>Dansk</th>
                <th>Norsk</th>
                <th>Svensk</th>
                <th>Engelsk</th>
                <th>Tysk</th>
                <th class="actions-column">Handlinger</th>
            </tr>
            </thead>
            <tbody id="categoryTableBody">
            <!-- Kategorier indsættes her dynamisk -->
            </tbody>
        </table>
    </div>
</div>

<!-- Modal til oprettelse/redigering af kategori -->
<div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="categoryModalLabel">Opret ny kategori</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Luk"></button>
            </div>
            <div class="modal-body">
                <form id="categoryForm">
                    <input type="hidden" id="categoryId" value="">

                    <div class="mb-3">
                        <label for="categoryName_da" class="form-label">Dansk navn</label>
                        <textarea class="form-control" id="categoryName_da" rows="3"></textarea>
                        <div class="error-message hidden" id="nameError_da">Navnet må ikke indeholde specialtegn</div>
                    </div>

                    <div class="mb-3">
                        <label for="categoryName_no" class="form-label">Norsk navn</label>
                        <textarea class="form-control" id="categoryName_no" rows="3"></textarea>
                        <div class="error-message hidden" id="nameError_no">Navnet må ikke indeholde specialtegn</div>
                    </div>

                    <div class="mb-3">
                        <label for="categoryName_sv" class="form-label">Svensk navn</label>
                        <textarea class="form-control" id="categoryName_sv" rows="3"></textarea>
                        <div class="error-message hidden" id="nameError_sv">Navnet må ikke indeholde specialtegn</div>
                    </div>

                    <div class="mb-3">
                        <label for="categoryName_en" class="form-label">Engelsk navn</label>
                        <textarea class="form-control" id="categoryName_en" rows="3"></textarea>
                        <div class="error-message hidden" id="nameError_en">Navnet må ikke indeholde specialtegn</div>
                    </div>

                    <div class="mb-3">
                        <label for="categoryName_de" class="form-label">Tysk navn</label>
                        <textarea class="form-control" id="categoryName_de" rows="3"></textarea>
                        <div class="error-message hidden" id="nameError_de">Navnet må ikke indeholde specialtegn</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuller</button>
                <button type="button" class="btn btn-primary" id="saveButton">Gem kategori</button>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript dependencies -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script src="./units/common/js/performAjaxPost.js"></script>

<!-- Indkapslet kategori administration script -->
<script>
    // Global variabel til API adgang
    var APPR_AJAX_URL = "./index.php?rt=unit/valgshop/";

    // Kategori Administration Module
    // Category Administration Module
    var CategoryAdmin = (function(shopID) {

        // Private variabler
        var categories = [];
        var categoryModal;
        var _shopID;
        // Konstanter
        const LANGUAGES = ['da', 'no', 'sv', 'en', 'de'];

        const validCharactersRegex = /^[a-zA-Z0-9æøåÆØÅüÜäÄöÖßéèêëÉÈÊË\s.,!?()&-]*$/;
        const API_ENDPOINTS = {
            CREATE: APPR_AJAX_URL+'categories/create',
            READ: APPR_AJAX_URL+'categories/list',
            UPDATE: APPR_AJAX_URL+'categories/update',
            DELETE: APPR_AJAX_URL+'categories/delete'
        };

        // Initialiser modulet
        function init(shopID) {
            _shopID = shopID;

            categoryModal = new bootstrap.Modal(document.getElementById('categoryModal'));
            attachEventHandlers();
            loadCategories();
        }

        // Opret event handlers
        function attachEventHandlers() {
            $('#saveButton').on('click', saveCategory);
            $('#categoryModal').on('hidden.bs.modal', resetForm);
        }

        // Funktion til at validere og gemme kategorien
        function saveCategory() {
            // Hent værdier fra formularen
            const categoryId = $('#categoryId').val();

            // Opret data objekt
            const categoryData = {
                names: {},
                shopID: _shopID // Add shopID to all requests
            };

            // Validér input for hvert sprog
            let isValid = true;

            // Mindst ét sprog skal være udfyldt (dansk er påkrævet)


            // Validér alle sprogfelter
            LANGUAGES.forEach(lang => {
                const fieldValue = $(`#categoryName_${lang}`).val().trim();

                // Hvis feltet er udfyldt, validér det
                if (fieldValue) {
                    if (!validCharactersRegex.test(fieldValue)) {
                        showError(`nameError_${lang}`, 'Navnet må ikke indeholde specialtegn');
                        isValid = false;
                    } else {
                        hideError(`nameError_${lang}`);
                        // Tilføj til data objektet
                        categoryData.names[lang] = fieldValue;
                    }
                } else {
                    hideError(`nameError_${lang}`);
                }
            });

            // Hvis validering fejler, stop her
            if (!isValid) return;

            // Tilføj id hvis vi opdaterer
            if (categoryId) {
                categoryData.id = categoryId;
                updateCategory(categoryData);
            } else {
                createCategory(categoryData);
            }
        }

        // Funktion til at vise fejlbesked
        function showError(elementId, message) {
            $(`#${elementId}`).text(message).removeClass('hidden');
        }

        // Funktion til at skjule fejlbesked
        function hideError(elementId) {
            $(`#${elementId}`).addClass('hidden');
        }

        // Funktion til at vise feedback
        function showFeedback(message, isSuccess = true) {
            const $alert = $('#feedbackAlert');
            $alert.text(message);

            if (isSuccess) {
                $alert.removeClass('alert-danger').addClass('alert-success');
            } else {
                $alert.removeClass('alert-success').addClass('alert-danger');
            }

            $alert.removeClass('hidden');

            // Skjul beskeden efter 3 sekunder
            setTimeout(function() {
                $alert.addClass('hidden');
            }, 3000);
        }

        // Funktion til at nulstille formularen
        function resetForm() {
            $('#categoryId').val('');

            // Nulstil alle sprogfelter
            LANGUAGES.forEach(lang => {
                $(`#categoryName_${lang}`).val('');
                hideError(`nameError_${lang}`);
            });

            $('#categoryModalLabel').text('Opret ny kategori');
            $('#saveButton').text('Gem kategori');
        }

        // Funktion til at hente alle kategorier
        function loadCategories() {
            performAjaxPost(API_ENDPOINTS.READ, {shopID: _shopID})
                .then(function(result) {
                    categories = result || []; // Convert null to empty array if needed
                    renderCategoryTable(categories);
                })
                .catch(function(error) {
                    showFeedback('Fejl ved hentning af kategorier: ' + error, false);
                });
        }

        // Funktion til at oprette en ny kategori
        function createCategory(categoryData) {
            performAjaxPost(API_ENDPOINTS.CREATE, categoryData)
                .then(function(result) {
                    showFeedback('Kategori oprettet!');
                    resetForm();
                    categoryModal.hide();
                    loadCategories(); // Genindlæs kategorier
                })
                .catch(function(error) {
                    showFeedback('Fejl ved oprettelse af kategori: ' + error, false);
                });
        }

        // Funktion til at opdatere en kategori
        function updateCategory(categoryData) {
            performAjaxPost(API_ENDPOINTS.UPDATE, categoryData)
                .then(function(result) {
                    showFeedback('Kategori opdateret!');
                    resetForm();
                    categoryModal.hide();
                    loadCategories(); // Genindlæs kategorier
                })
                .catch(function(error) {
                    showFeedback('Fejl ved opdatering af kategori: ' + error, false);
                });
        }

        // Funktion til at slette en kategori
        function deleteCategory(categoryId) {
            if (confirm('Er du sikker på, at du vil slette denne kategori?')) {
                performAjaxPost(API_ENDPOINTS.DELETE, { id: categoryId, shopID: _shopID })
                    .then(function(result) {
                        showFeedback('Kategori slettet!');
                        loadCategories(); // Genindlæs kategorier
                    })
                    .catch(function(error) {
                        showFeedback('Fejl ved sletning af kategori: ' + error, false);
                    });
            }
        }

        // Funktion til at vise kategorier i tabellen
        function renderCategoryTable(categories) {
            const $tableBody = $('#categoryTableBody');
            $tableBody.empty();

            if (categories.length === 0 || categories == null) {
                $tableBody.append('<tr><td colspan="7" class="text-center">Ingen kategorier fundet</td></tr>');
                return;
            }

            categories.forEach(function(category) {
                const row = `
            <tr>
                <td>${category.id}</td>
                <td>${escapeHtml(category.names.da || '')}</td>
                <td>${escapeHtml(category.names.no || '')}</td>
                <td>${escapeHtml(category.names.sv || '')}</td>
                <td>${escapeHtml(category.names.en || '')}</td>
                <td>${escapeHtml(category.names.de || '')}</td>
                <td>
                    <button class="btn btn-sm btn-primary edit-btn" data-id="${category.id}">Rediger</button>
                    <button class="btn btn-sm btn-danger delete-btn" data-id="${category.id}">Slet</button>
                </td>
            </tr>
        `;

                $tableBody.append(row);
            });

            // Tilføj event handlers til knapper
            $('.edit-btn').on('click', function() {
                const categoryId = $(this).data('id');
                editCategory(categoryId);
            });

            $('.delete-btn').on('click', function() {
                const categoryId = $(this).data('id');
                deleteCategory(categoryId);
            });
        }

        // Funktion til at åbne redigeringsformular
        function editCategory(categoryId) {
            const category = categories.find(c => c.id == categoryId);

            if (category) {
                // Nulstil form først
                resetForm();

                // Udfyld form med data
                $('#categoryId').val(category.id);

                // Udfyld sprogfelter
                LANGUAGES.forEach(lang => {
                    if (category.names[lang]) {
                        $(`#categoryName_${lang}`).val(category.names[lang]);
                    }
                });

                // Opdater modal-titel
                $('#categoryModalLabel').text('Rediger kategori');
                $('#saveButton').text('Gem ændringer');

                // Åbn modal
                categoryModal.show();
            }
        }

        // Hjælpefunktion til at escape HTML for sikkerhed
        function escapeHtml(text) {
            if (!text) return '';

            return text
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        // Returner offentlige metoder
        return {
            init: init
        };
    })();
    // Initialiser modulet når dokumentet er klart
    $(document).ready(function() {
        let shopID = <?php echo $_GET["shopID"]; ?>;
        CategoryAdmin.init(shopID);
    });
</script>
</body>
</html>