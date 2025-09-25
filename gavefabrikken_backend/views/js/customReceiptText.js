// DeliveryModule.js - Mail template customization module (updated to use mail_template)
const DeliveryModule = (function() {
    'use strict';

    // Private variables
    let currentLang = 'da';
    let currentShopId = null;
    let currentRecordId = null;
    let isEditMode = false;
    let moduleLoaded = false;
    let modalElement = null;
    const CSS_PREFIX = 'dlvm_'; // Delivery Language Version Module

    // Language mapping to database IDs
    const langMapping = {
        'da': 1,  // Dansk
        'en': 2,  // Engelsk
        'de': 3,  // Tysk
        'no': 4,  // Norsk
        'sv': 5   // Svensk
    };

    function clearForm() {
        console.log('clearForm: Clearing all fields to empty');
        $(`#${CSS_PREFIX}subject`).val('');
        $(`#${CSS_PREFIX}topText`).html('');
        $(`#${CSS_PREFIX}deliveryDate`).val('');
        $(`#${CSS_PREFIX}standardText`).html('');
        $(`#${CSS_PREFIX}bottomText`).html('');

        // Reset toolbar state
        $(`.${CSS_PREFIX}toolbar_btn`).removeClass('active');
    };

    // Translations
    const translations = {
        da: {
            modalTitle: 'Kvitteringstekster v1.2',
            subjectLabel: 'Emne:',
            topSection: 'Øverste sektion (før kvittering)',
            topText: 'Tekst øverst:',
            deliverySection: 'Leveringsinformation',
            deliveryDate: 'Leveringsdato:',
            deliveryNotes: 'Leveringsnoter:',
            standardSection: 'Standard tekst',
            standardText: 'Standard tekst:',
            bottomSection: 'Bundsektion (efter alt indhold)',
            bottomText: 'Tekst i bunden:',
            read: 'Læs',
            edit: 'Rediger',
            save: 'Gem',
            preview: 'Forhåndsvisning',
            close: 'Luk',
            loading: 'Henter data...',
            saved: 'Mail template gemt succesfuldt!',
            error: 'Der opstod en fejl',
            noContent: 'Ingen indhold at gemme',
            // Rich text toolbar
            bold: 'Fed',
            italic: 'Kursiv',
            underline: 'Understreg',
            strikethrough: 'Gennemstreg',
            insertLink: 'Indsæt link',
            removeFormat: 'Fjern formatering',
            bulletList: 'Punktliste',
            numberedList: 'Nummereret liste'
        },
        en: {
            modalTitle: 'Mail Template Customization',
            subjectLabel: 'Subject:',
            topSection: 'Top Section (before receipt)',
            topText: 'Top text ({text1}):',
            deliverySection: 'Delivery Information',
            deliveryInfo: 'Delivery info ({DELIVERY_INFO}):',
            standardSection: 'Standard Text',
            standardText: 'Standard text ({text2}):',
            bottomSection: 'Bottom Section (after receipt)',
            bottomText: 'Bottom text ({RECEIPT_POS1}):',
            read: 'Read',
            edit: 'Edit',
            save: 'Save',
            preview: 'Preview',
            close: 'Close',
            loading: 'Loading data...',
            saved: 'Mail template saved successfully!',
            error: 'An error occurred',
            noContent: 'No content to save',
            // Rich text toolbar
            bold: 'Bold',
            italic: 'Italic',
            underline: 'Underline',
            strikethrough: 'Strikethrough',
            insertLink: 'Insert link',
            removeFormat: 'Remove formatting',
            bulletList: 'Bullet list',
            numberedList: 'Numbered list'
        },
        de: {
            modalTitle: 'Mail Template Anpassung',
            subjectLabel: 'Betreff:',
            topSection: 'Oberer Bereich (vor Quittung)',
            topText: 'Text oben ({text1}):',
            deliverySection: 'Lieferinformationen',
            deliveryInfo: 'Lieferinfo ({DELIVERY_INFO}):',
            standardSection: 'Standardtext',
            standardText: 'Standardtext ({text2}):',
            bottomSection: 'Unterer Bereich (nach Quittung)',
            bottomText: 'Text unten ({RECEIPT_POS1}):',
            read: 'Lesen',
            edit: 'Bearbeiten',
            save: 'Speichern',
            preview: 'Vorschau',
            close: 'Schließen',
            loading: 'Daten werden geladen...',
            saved: 'Mail Template erfolgreich gespeichert!',
            error: 'Ein Fehler ist aufgetreten',
            noContent: 'Kein Inhalt zu speichern',
            // Rich text toolbar
            bold: 'Fett',
            italic: 'Kursiv',
            underline: 'Unterstreichen',
            strikethrough: 'Durchstreichen',
            insertLink: 'Link einfügen',
            removeFormat: 'Formatierung entfernen',
            bulletList: 'Aufzählung',
            numberedList: 'Nummerierte Liste'
        },
        no: {
            modalTitle: 'Mail Template Tilpasning',
            subjectLabel: 'Emne:',
            topSection: 'Øverste seksjon (før kvittering)',
            topText: 'Tekst øverst ({text1}):',
            deliverySection: 'Leveringsinformasjon',
            deliveryInfo: 'Leveringsinfo ({DELIVERY_INFO}):',
            standardSection: 'Standardtekst',
            standardText: 'Standardtekst ({text2}):',
            bottomSection: 'Bunnseksjon (etter kvittering)',
            bottomText: 'Tekst i bunnen ({RECEIPT_POS1}):',
            read: 'Les',
            edit: 'Rediger',
            save: 'Lagre',
            preview: 'Forhåndsvisning',
            close: 'Lukk',
            loading: 'Henter data...',
            saved: 'Mail template lagret!',
            error: 'En feil oppstod',
            noContent: 'Ingen innhold å lagre',
            // Rich text toolbar
            bold: 'Fet',
            italic: 'Kursiv',
            underline: 'Understreket',
            strikethrough: 'Gjennomstreket',
            insertLink: 'Sett inn lenke',
            removeFormat: 'Fjern formatering',
            bulletList: 'Punktliste',
            numberedList: 'Nummerert liste'
        },
        sv: {
            modalTitle: 'Mail Template Anpassning',
            subjectLabel: 'Ämne:',
            topSection: 'Översta sektionen (före kvitto)',
            topText: 'Text överst ({text1}):',
            deliverySection: 'Leveransinformation',
            deliveryInfo: 'Leveransinfo ({DELIVERY_INFO}):',
            standardSection: 'Standardtext',
            standardText: 'Standardtext ({text2}):',
            bottomSection: 'Nedersta sektionen (efter kvitto)',
            bottomText: 'Text nederst ({RECEIPT_POS1}):',
            read: 'Läs',
            edit: 'Redigera',
            save: 'Spara',
            preview: 'Förhandsvisning',
            close: 'Stäng',
            loading: 'Hämtar data...',
            saved: 'Mail template sparad!',
            error: 'Ett fel uppstod',
            noContent: 'Inget innehåll att spara',
            // Rich text toolbar
            bold: 'Fet',
            italic: 'Kursiv',
            underline: 'Understruken',
            strikethrough: 'Genomstruken',
            insertLink: 'Infoga länk',
            removeFormat: 'Ta bort formatering',
            bulletList: 'Punktlista',
            numberedList: 'Numrerad lista'
        }
    };

    // CSS styles (reusing most from DeliveryModule)
    const moduleCSS = `
        .${CSS_PREFIX}overlay {
            display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background-color: rgba(0, 0, 0, 0.5); z-index: 99999;
            animation: ${CSS_PREFIX}fadeIn 0.3s ease-in-out;
        }
        @keyframes ${CSS_PREFIX}fadeIn { from { opacity: 0; } to { opacity: 1; } }
        .${CSS_PREFIX}modal {
            position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
            background-color: #ffffff; border-radius: 8px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            width: 90%; max-width: 1000px; max-height: 90vh; overflow: hidden;
            animation: ${CSS_PREFIX}slideIn 0.3s ease-out;
        }
        @keyframes ${CSS_PREFIX}slideIn {
            from { transform: translate(-50%, -60%); opacity: 0; }
            to { transform: translate(-50%, -50%); opacity: 1; }
        }
        .${CSS_PREFIX}header {
            background-color: #2c3e50; color: #ffffff; padding: 20px;
            display: flex; justify-content: space-between; align-items: center;
        }
        .${CSS_PREFIX}title { font-size: 24px; font-weight: 600; margin: 0; }
        .${CSS_PREFIX}close_btn {
            background: none; border: none; color: #ffffff; font-size: 28px;
            cursor: pointer; transition: transform 0.2s;
        }
        .${CSS_PREFIX}close_btn:hover { transform: scale(1.1); }
        .${CSS_PREFIX}content { padding: 30px; overflow-y: auto; max-height: calc(90vh - 200px); }
        .${CSS_PREFIX}lang_selector { display: flex; gap: 10px; margin-bottom: 25px; flex-wrap: wrap; }
        .${CSS_PREFIX}lang_btn {
            padding: 10px 20px; border: 2px solid #3498db; background-color: #ffffff;
            color: #3498db; border-radius: 5px; cursor: pointer;
            transition: all 0.3s; font-weight: 500;
        }
        .${CSS_PREFIX}lang_btn:hover { background-color: #e8f4fd; }
        .${CSS_PREFIX}lang_btn.${CSS_PREFIX}active { background-color: #3498db; color: #ffffff; }
        .${CSS_PREFIX}form_section {
            margin-bottom: 25px; padding: 20px; background-color: #f8f9fa;
            border-radius: 8px; border: 1px solid #e9ecef;
        }
        .${CSS_PREFIX}section_title {
            font-size: 18px; font-weight: 600; color: #2c3e50; margin-bottom: 15px;
            padding-bottom: 10px; border-bottom: 2px solid #3498db;
        }
        .${CSS_PREFIX}injection_note {
            font-size: 13px; color: #7f8c8d; font-style: italic; margin-bottom: 10px;
        }
        .${CSS_PREFIX}form_group { margin-bottom: 20px; }
        .${CSS_PREFIX}label {
            display: block; margin-bottom: 8px; color: #34495e; font-weight: 500;
        }
        
        /* Rich Text Editor Styles */
        .${CSS_PREFIX}rich_text_container {
            border: 1px solid #bdc3c7; border-radius: 5px; background: #ffffff;
        }
        .${CSS_PREFIX}toolbar {
            background: #f8f9fa; border-bottom: 1px solid #dee2e6; padding: 8px;
            display: flex; gap: 5px; flex-wrap: wrap; border-radius: 5px 5px 0 0;
        }
        .${CSS_PREFIX}toolbar_btn {
            background: #ffffff; border: 1px solid #dee2e6; border-radius: 3px;
            padding: 5px 10px; cursor: pointer; font-size: 14px;
            display: flex; align-items: center; justify-content: center;
            min-width: 30px; height: 30px; transition: all 0.2s;
        }
        .${CSS_PREFIX}toolbar_btn:hover {
            background-color: #e9ecef; border-color: #adb5bd;
        }
        .${CSS_PREFIX}toolbar_btn.active {
            background-color: #007bff; color: #ffffff; border-color: #007bff;
        }
        .${CSS_PREFIX}toolbar_separator {
            width: 1px; background: #dee2e6; margin: 0 5px; height: 30px;
        }
        
        .${CSS_PREFIX}rich_editor {
            min-height: 100px; padding: 12px; border: none; border-radius: 0 0 5px 5px;
            font-family: inherit; font-size: 14px; outline: none; resize: vertical;
            line-height: 1.4;
        }
        .${CSS_PREFIX}rich_editor:focus {
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }
        
        .${CSS_PREFIX}input {
            width: 100%; padding: 12px; border: 1px solid #bdc3c7;
            border-radius: 5px; font-size: 14px; transition: border-color 0.3s;
        }
        .${CSS_PREFIX}input:focus {
            outline: none; border-color: #3498db; box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }
        .${CSS_PREFIX}actions {
            display: flex; gap: 15px; justify-content: flex-end;
            padding: 20px; background-color: #f8f9fa; border-top: 1px solid #e9ecef;
        }
        .${CSS_PREFIX}btn {
            padding: 12px 30px; border: none; border-radius: 5px; cursor: pointer;
            font-weight: 500; transition: all 0.3s; font-size: 16px;
        }
        .${CSS_PREFIX}btn_primary { background-color: #27ae60; color: #ffffff; }
        .${CSS_PREFIX}btn_primary:hover {
            background-color: #229954; transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .${CSS_PREFIX}btn_secondary { background-color: #95a5a6; color: #ffffff; }
        .${CSS_PREFIX}btn_secondary:hover { background-color: #7f8c8d; }
        .${CSS_PREFIX}btn_info { background-color: #3498db; color: #ffffff; }
        .${CSS_PREFIX}btn_info:hover { 
            background-color: #2980b9; transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .${CSS_PREFIX}loading { display: none; text-align: center; padding: 20px; color: #7f8c8d; }
        .${CSS_PREFIX}spinner {
            display: inline-block; width: 30px; height: 30px; border: 3px solid #f3f3f3;
            border-top: 3px solid #3498db; border-radius: 50%;
            animation: ${CSS_PREFIX}spin 1s linear infinite;
        }
        @keyframes ${CSS_PREFIX}spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        .${CSS_PREFIX}readonly { background-color: #f8f9fa !important; cursor: not-allowed !important; }
        .${CSS_PREFIX}message {
            padding: 10px 15px; margin: 10px 0; border-radius: 5px; font-weight: 500;
        }
        .${CSS_PREFIX}message_success {
            background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb;
        }
        .${CSS_PREFIX}message_error {
            background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;
        }
    `;

    // HTML template
    const modalHTML = `
        <div class="${CSS_PREFIX}overlay" id="${CSS_PREFIX}overlay">
            <div class="${CSS_PREFIX}modal">
                <div class="${CSS_PREFIX}header">
                    <h2 class="${CSS_PREFIX}title" id="${CSS_PREFIX}modalTitle">Mail Template Tilpasning</h2>
                    <button class="${CSS_PREFIX}close_btn" id="${CSS_PREFIX}closeModal">&times;</button>
                </div>
                <div class="${CSS_PREFIX}content">
                    <div class="${CSS_PREFIX}lang_selector">
                        <button class="${CSS_PREFIX}lang_btn ${CSS_PREFIX}active" data-lang="da">Dansk</button>
                        <button class="${CSS_PREFIX}lang_btn" data-lang="en">English</button>
                        <button class="${CSS_PREFIX}lang_btn" data-lang="de">Deutsch</button>
                        <button class="${CSS_PREFIX}lang_btn" data-lang="no">Norsk</button>
                        <button class="${CSS_PREFIX}lang_btn" data-lang="sv">Svenska</button>
                    </div>
                    <div id="${CSS_PREFIX}messageContainer"></div>
                    <div class="${CSS_PREFIX}loading" id="${CSS_PREFIX}loading">
                        <div class="${CSS_PREFIX}spinner"></div>
                        <p>Henter data...</p>
                    </div>
                    <form id="${CSS_PREFIX}form">
                        <div class="${CSS_PREFIX}form_section">
                            <div class="${CSS_PREFIX}form_group">
                                <label class="${CSS_PREFIX}label" data-translate="subjectLabel">Emne:</label>
                                <input type="text" class="${CSS_PREFIX}input" id="${CSS_PREFIX}subject" name="subject" 
                                       placeholder="Mail emne">
                            </div>
                        </div>
                        
                        <div class="${CSS_PREFIX}form_section">
                            <h3 class="${CSS_PREFIX}section_title" data-translate="topSection">Øverste sektion</h3>
                            <p class="${CSS_PREFIX}injection_note">📍 Placeres som {text1} i mail templaten</p>
                            <div class="${CSS_PREFIX}form_group">
                                <label class="${CSS_PREFIX}label" data-translate="topText">Tekst øverst:</label>
                                <div class="${CSS_PREFIX}rich_text_container">
                                    <div class="${CSS_PREFIX}toolbar" data-target="topText">
                                        <button type="button" class="${CSS_PREFIX}toolbar_btn" data-command="bold" title="Bold"><b>B</b></button>
                                        <button type="button" class="${CSS_PREFIX}toolbar_btn" data-command="italic" title="Italic"><i>I</i></button>
                                        <button type="button" class="${CSS_PREFIX}toolbar_btn" data-command="underline" title="Underline"><u>U</u></button>
                                        <button type="button" class="${CSS_PREFIX}toolbar_btn" data-command="strikeThrough" title="Strikethrough"><s>S</s></button>
                                        <div class="${CSS_PREFIX}toolbar_separator"></div>
                                        <button type="button" class="${CSS_PREFIX}toolbar_btn" data-command="insertUnorderedList" title="Bullet List">•</button>
                                        <button type="button" class="${CSS_PREFIX}toolbar_btn" data-command="insertOrderedList" title="Numbered List">1.</button>
                                        <div class="${CSS_PREFIX}toolbar_separator"></div>
                                        <button type="button" class="${CSS_PREFIX}toolbar_btn" data-command="createLink" title="Insert Link">🔗</button>
                                        <button type="button" class="${CSS_PREFIX}toolbar_btn" data-command="removeFormat" title="Remove Format">✂️</button>
                                    </div>
                                    <div class="${CSS_PREFIX}rich_editor" id="${CSS_PREFIX}topText" contenteditable="true"></div>
                                </div>
                            </div>
                        </div>

                        <div class="${CSS_PREFIX}form_section">
                            <h3 class="${CSS_PREFIX}section_title" data-translate="deliverySection">Leveringsinformation</h3>
                            <p class="${CSS_PREFIX}injection_note">📍 Placeres efter "Dine oplysninger" sektionen</p>
                            <div class="${CSS_PREFIX}form_group">
                                <label class="${CSS_PREFIX}label" data-translate="deliveryDate">Leveringsdato:</label>
                                <input type="text" class="${CSS_PREFIX}input" id="${CSS_PREFIX}deliveryDate" name="deliveryDate" 
                                       placeholder="F.eks. '15. marts 2024' eller 'Uge 12'">
                            </div>
                        </div>
                        
                        <div class="${CSS_PREFIX}form_section">
                            <h3 class="${CSS_PREFIX}section_title" data-translate="standardSection">Standard tekst</h3>
                            <p class="${CSS_PREFIX}injection_note">📍 Placeres som {text2} i mail templaten</p>
                            <div class="${CSS_PREFIX}form_group">
                                <label class="${CSS_PREFIX}label" data-translate="standardText">Standard tekst:</label>
                                <div class="${CSS_PREFIX}rich_text_container">
                                    <div class="${CSS_PREFIX}toolbar" data-target="standardText">
                                        <button type="button" class="${CSS_PREFIX}toolbar_btn" data-command="bold" title="Bold"><b>B</b></button>
                                        <button type="button" class="${CSS_PREFIX}toolbar_btn" data-command="italic" title="Italic"><i>I</i></button>
                                        <button type="button" class="${CSS_PREFIX}toolbar_btn" data-command="underline" title="Underline"><u>U</u></button>
                                        <button type="button" class="${CSS_PREFIX}toolbar_btn" data-command="strikeThrough" title="Strikethrough"><s>S</s></button>
                                        <div class="${CSS_PREFIX}toolbar_separator"></div>
                                        <button type="button" class="${CSS_PREFIX}toolbar_btn" data-command="insertUnorderedList" title="Bullet List">•</button>
                                        <button type="button" class="${CSS_PREFIX}toolbar_btn" data-command="insertOrderedList" title="Numbered List">1.</button>
                                        <div class="${CSS_PREFIX}toolbar_separator"></div>
                                        <button type="button" class="${CSS_PREFIX}toolbar_btn" data-command="createLink" title="Insert Link">🔗</button>
                                        <button type="button" class="${CSS_PREFIX}toolbar_btn" data-command="removeFormat" title="Remove Format">✂️</button>
                                    </div>
                                    <div class="${CSS_PREFIX}rich_editor" id="${CSS_PREFIX}standardText" contenteditable="true"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="${CSS_PREFIX}form_section">
                            <h3 class="${CSS_PREFIX}section_title" data-translate="bottomSection">Bundsektion</h3>
                            <p class="${CSS_PREFIX}injection_note">📍 Placeres som {RECEIPT_POS1} i mail templaten</p>
                            <div class="${CSS_PREFIX}form_group">
                                <label class="${CSS_PREFIX}label" data-translate="bottomText">Tekst i bunden:</label>
                                <div class="${CSS_PREFIX}rich_text_container">
                                    <div class="${CSS_PREFIX}toolbar" data-target="bottomText">
                                        <button type="button" class="${CSS_PREFIX}toolbar_btn" data-command="bold" title="Bold"><b>B</b></button>
                                        <button type="button" class="${CSS_PREFIX}toolbar_btn" data-command="italic" title="Italic"><i>I</i></button>
                                        <button type="button" class="${CSS_PREFIX}toolbar_btn" data-command="underline" title="Underline"><u>U</u></button>
                                        <button type="button" class="${CSS_PREFIX}toolbar_btn" data-command="strikeThrough" title="Strikethrough"><s>S</s></button>
                                        <div class="${CSS_PREFIX}toolbar_separator"></div>
                                        <button type="button" class="${CSS_PREFIX}toolbar_btn" data-command="insertUnorderedList" title="Bullet List">•</button>
                                        <button type="button" class="${CSS_PREFIX}toolbar_btn" data-command="insertOrderedList" title="Numbered List">1.</button>
                                        <div class="${CSS_PREFIX}toolbar_separator"></div>
                                        <button type="button" class="${CSS_PREFIX}toolbar_btn" data-command="createLink" title="Insert Link">🔗</button>
                                        <button type="button" class="${CSS_PREFIX}toolbar_btn" data-command="removeFormat" title="Remove Format">✂️</button>
                                    </div>
                                    <div class="${CSS_PREFIX}rich_editor" id="${CSS_PREFIX}bottomText" contenteditable="true"></div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="${CSS_PREFIX}actions">
                   <button type="button" class="${CSS_PREFIX}btn ${CSS_PREFIX}btn_info" id="${CSS_PREFIX}previewBtn">
                        <span data-translate="preview">Forhåndsvisning</span>
                    </button>
                   <button type="button" class="${CSS_PREFIX}btn ${CSS_PREFIX}btn_secondary" id="${CSS_PREFIX}closeBtn">
                        <span data-translate="close">Luk</span>
                    </button>
                   <button type="button" class="${CSS_PREFIX}btn ${CSS_PREFIX}btn_primary" id="${CSS_PREFIX}saveBtn">
                        <span data-translate="save">Gem</span>
                    </button>
                </div>
            </div>
        </div>
    `;

    // Private functions (reusing logic from DeliveryModule but adapted)
    function injectCSS() {
        if (!document.getElementById(`${CSS_PREFIX}styles`)) {
            const style = document.createElement('style');
            style.id = `${CSS_PREFIX}styles`;
            style.textContent = moduleCSS;
            document.head.appendChild(style);
        }
    }

    function injectHTML() {
        if (!document.getElementById(`${CSS_PREFIX}overlay`)) {
            $('body').append(modalHTML);
            modalElement = document.getElementById(`${CSS_PREFIX}overlay`);
        }
    }

    function updateTranslations() {
        const trans = translations[currentLang];
        $(`#${CSS_PREFIX}modalTitle`).text(trans.modalTitle);
        $('[data-translate]').each(function() {
            const key = $(this).data('translate');
            if (trans[key]) {
                $(this).text(trans[key]);
            }
        });
        $(`#${CSS_PREFIX}loading p`).text(trans.loading);
        updateToolbarTooltips();
    }

    function updateToolbarTooltips() {
        const trans = translations[currentLang];
        $(`.${CSS_PREFIX}toolbar_btn[data-command="bold"]`).attr('title', trans.bold);
        $(`.${CSS_PREFIX}toolbar_btn[data-command="italic"]`).attr('title', trans.italic);
        $(`.${CSS_PREFIX}toolbar_btn[data-command="underline"]`).attr('title', trans.underline);
        $(`.${CSS_PREFIX}toolbar_btn[data-command="strikeThrough"]`).attr('title', trans.strikethrough);
        $(`.${CSS_PREFIX}toolbar_btn[data-command="createLink"]`).attr('title', trans.insertLink);
        $(`.${CSS_PREFIX}toolbar_btn[data-command="removeFormat"]`).attr('title', trans.removeFormat);
        $(`.${CSS_PREFIX}toolbar_btn[data-command="insertUnorderedList"]`).attr('title', trans.bulletList);
        $(`.${CSS_PREFIX}toolbar_btn[data-command="insertOrderedList"]`).attr('title', trans.numberedList);
    }

    function setEditMode(enabled) {
        isEditMode = enabled;
        $(`#${CSS_PREFIX}form`).find('input').prop('readonly', !enabled);
        $(`.${CSS_PREFIX}rich_editor`).attr('contenteditable', enabled);
        $(`.${CSS_PREFIX}toolbar_btn`).prop('disabled', !enabled);

        if (enabled) {
            $(`#${CSS_PREFIX}form`).find('input').removeClass(`${CSS_PREFIX}readonly`);
            $(`.${CSS_PREFIX}rich_editor`).removeClass(`${CSS_PREFIX}readonly`);
            $(`.${CSS_PREFIX}toolbar`).show();

            setTimeout(() => {
                $(`.${CSS_PREFIX}toolbar_btn`).removeClass('active');
            }, 100);
        } else {
            $(`#${CSS_PREFIX}form`).find('input').addClass(`${CSS_PREFIX}readonly`);
            $(`.${CSS_PREFIX}rich_editor`).addClass(`${CSS_PREFIX}readonly`);
            $(`.${CSS_PREFIX}toolbar`).hide();
        }
    }

    function showMessage(message, type = 'success') {
        const messageClass = type === 'success' ? `${CSS_PREFIX}message_success` : `${CSS_PREFIX}message_error`;
        const icon = type === 'success' ? '✅' : '❌';
        const messageHtml = `<div class="${CSS_PREFIX}message ${messageClass}">${icon} ${message}</div>`;

        const $container = $(`#${CSS_PREFIX}messageContainer`);
        $container.html(messageHtml);

        $(`#${CSS_PREFIX}content`).scrollTop(0);

        setTimeout(() => {
            $container.fadeOut(500, function() {
                $(this).empty().show();
            });
        }, 4000);
    }

    function bindEvents() {
        // Language switching
        $(document).on('click', `.${CSS_PREFIX}lang_btn`, function() {
            const newLang = $(this).data('lang');
            if (newLang !== currentLang) {
                $(`.${CSS_PREFIX}lang_btn`).removeClass(`${CSS_PREFIX}active`);
                $(this).addClass(`${CSS_PREFIX}active`);
                currentLang = newLang;
                updateTranslations();
                loadData();
            }
        });

        // Modal controls
        $(document).on('click', `#${CSS_PREFIX}closeModal, #${CSS_PREFIX}closeBtn`, function(e) {
            e.preventDefault();
            $(`#${CSS_PREFIX}overlay`).fadeOut(300);
        });

        // ESC key
        $(document).on('keydown', function(e) {
            if (e.key === 'Escape' && $(`#${CSS_PREFIX}overlay`).is(':visible')) {
                $(`#${CSS_PREFIX}overlay`).fadeOut(300);
            }
        });

        // Rich text toolbar
        $(document).on('click', `.${CSS_PREFIX}toolbar_btn`, function(e) {
            e.preventDefault();
            if (!isEditMode) return;

            const command = $(this).data('command');
            const toolbar = $(this).closest(`.${CSS_PREFIX}toolbar`);
            const targetId = toolbar.data('target');
            const editor = $(`#${CSS_PREFIX}${targetId}`)[0];

            editor.focus();

            if (command === 'createLink') {
                const url = prompt('Indtast URL:');
                if (url) {
                    document.execCommand(command, false, url);
                }
            } else {
                document.execCommand(command, false, null);
            }

            updateToolbarState(toolbar, editor);
        });

        // Update toolbar state
        $(document).on('keyup mouseup', `.${CSS_PREFIX}rich_editor`, function() {
            if (!isEditMode) return;
            const toolbar = $(this).siblings(`.${CSS_PREFIX}toolbar`);
            updateToolbarState(toolbar, this);
        });

        // Preview button
        $(document).on('click', `#${CSS_PREFIX}previewBtn`, function(e) {
            e.preventDefault();
            showPreview();
        });

        // Save button
        $(document).on('click', `#${CSS_PREFIX}saveBtn`, function(e) {
            e.preventDefault();
            saveData();
        });
    }

    function updateToolbarState(toolbar, editor) {
        toolbar.find(`.${CSS_PREFIX}toolbar_btn`).each(function() {
            const command = $(this).data('command');
            const isActive = document.queryCommandState(command);
            $(this).toggleClass('active', isActive);
        });
    }

    function loadData() {
        $(`#${CSS_PREFIX}loading`).show();
        $(`#${CSS_PREFIX}form`).hide();
        $(`#${CSS_PREFIX}messageContainer`).empty();

        const postData = {
            shop_id: currentShopId || 0,
            language_id: langMapping[currentLang]
        };

        console.log('LoadData - Sending request with data:', postData);

        $.post('index.php?rt=mailTemplateCustom/getByLanguageAndShop', postData)
            .done(function(response) {
                console.log('LoadData - Raw response:', response);

                try {
                    const responseObj = typeof response === 'string' ? JSON.parse(response) : response;
                    console.log('LoadData - Parsed response object:', responseObj);

                    // Handle the response structure: {status: "1", data: {...}, message: ""}
                    let actualData = null;
                    if (responseObj.data && responseObj.data.id) {
                        actualData = responseObj.data;
                    } else if (responseObj.id) {
                        actualData = responseObj;
                    }

                    if (actualData && actualData.id) {
                        currentRecordId = actualData.id;
                        console.log('LoadData - Found record with ID:', currentRecordId);

                        setTimeout(() => {
                            $(`#${CSS_PREFIX}subject`).val(actualData.subject_receipt || '');
                            setFieldContent('topText', actualData.custom_text1 || '');
                            $(`#${CSS_PREFIX}deliveryDate`).val(actualData.custom_delivery_info || '');
                            setFieldContent('standardText', actualData.custom_text2 || '');
                            setFieldContent('bottomText', actualData.custom_receipt_pos1 || '');
                        }, 100);

                    } else {
                        console.log('LoadData - No record found, will create new when saved');
                        currentRecordId = null;
                        clearForm();
                    }

                    setTimeout(() => {
                        setEditMode(true);
                    }, 200);

                } catch (e) {
                    console.error('LoadData - Parse error:', e, 'Response:', response);
                    handleLoadError();
                }
            })
            .fail(function(xhr, status, error) {
                console.error('LoadData - Request failed:', {xhr, status, error});
                handleLoadError();
            })
            .always(function() {
                $(`#${CSS_PREFIX}loading`).hide();
                $(`#${CSS_PREFIX}form`).show();
            });
    }

    function extractPlaceholderContent(template, placeholder) {
        if (!template) return '';

        try {
            // Try to extract content that was inserted where placeholders should be
            switch(placeholder) {
                case 'text1':
                    // Look for content in text1 sections
                    const text1Match = template.match(/{text1}([^{]*)/i);
                    if (text1Match) return text1Match[1];

                    // Look for content that replaced {text1}
                    const text1Section = template.match(/<tr>\s*<td[^>]*colspan[^>]*>([^<]*(?:<[^>]*>[^<]*<\/[^>]*>)*[^<]*)<br><br><br>\s*<\/td>\s*<\/tr>/i);
                    if (text1Section && !text1Section[1].includes('{')) {
                        return text1Section[1];
                    }
                    break;

                case 'text2':
                    // Look for content in text2 sections
                    const text2Match = template.match(/{text2}([^{]*)/i);
                    if (text2Match) return text2Match[1];

                    // Look for content between {qr} and date section
                    const text2Section = template.match(/{qr}\s*<tr>\s*<td[^>]*colspan[^>]*>([^<]*(?:<[^>]*>[^<]*<\/[^>]*>)*[^<]*)<br><br><br>\s*<\/td>\s*<\/tr>/i);
                    if (text2Section && !text2Section[1].includes('{')) {
                        return text2Section[1];
                    }
                    break;

                case 'DELIVERY_INFO':
                    // Look for custom delivery info content that replaced the placeholder
                    const deliveryMatch = template.match(/{DELIVERY_INFO}([^{]*)/i);
                    if (deliveryMatch) return deliveryMatch[1];

                    // Look for content that completely replaced {DELIVERY_INFO}
                    // This is tricky since DELIVERY_INFO can be HTML table rows
                    const deliverySection = template.match(/\{ORDERNO\}<\/h3><\/td>\s*<\/tr>\s*([^<]*(?:<[^>]*>[^<]*<\/[^>]*>)*[^<]*)\s*<tr>/i);
                    if (deliverySection && !deliverySection[1].includes('{') && deliverySection[1].trim()) {
                        return deliverySection[1].trim();
                    }
                    break;

                case 'RECEIPT_POS1':
                    // Look for content at the end
                    const receiptMatch = template.match(/{RECEIPT_POS1}([^{]*)/i);
                    if (receiptMatch) return receiptMatch[1];
                    break;
            }
        } catch (e) {
            console.error('Error extracting placeholder content:', e);
        }

        return '';
    }

    function saveData() {
        if (!isEditMode) return;

        const formData = {
            shop_id: currentShopId || 0,
            language_id: langMapping[currentLang],
            sender_receipt: 'info@gavefabrikken.dk',
            subject_receipt: $(`#${CSS_PREFIX}subject`).val(),
            custom_text1: $(`#${CSS_PREFIX}topText`).html(),
            custom_delivery_info: $(`#${CSS_PREFIX}deliveryDate`).val(), // Use DELIVERY_INFO placeholder
            custom_text2: $(`#${CSS_PREFIX}standardText`).html(),
            custom_receipt_pos1: $(`#${CSS_PREFIX}bottomText`).html()
        };

        if (currentRecordId) {
            formData.id = currentRecordId;
        }

        const endpoint = 'index.php?rt=mailTemplateCustom/saveCustomTemplate';

        console.log('SaveData - Endpoint:', endpoint, 'Data:', formData);

        $.post(endpoint, formData)
            .done(function(response) {
                console.log('SaveData - Raw response:', response);

                try {
                    const responseObj = typeof response === 'string' ? JSON.parse(response) : response;

                    // Handle response structure with data field
                    let actualData = null;
                    if (responseObj.data && responseObj.data.id) {
                        actualData = responseObj.data;
                    } else if (responseObj.id) {
                        actualData = responseObj;
                    }

                    if (actualData && actualData.id) {
                        currentRecordId = actualData.id;
                        console.log('SaveData - Updated current record ID to:', currentRecordId);
                    }

                    const trans = translations[currentLang];
                    showMessage(trans.saved, 'success');
                } catch (e) {
                    console.error('SaveData - Parse error:', e);
                    const trans = translations[currentLang];
                    showMessage(trans.error, 'error');
                }
            })
            .fail(function(xhr, status, error) {
                console.error('SaveData - Request failed:', {xhr, status, error});
                const trans = translations[currentLang];
                showMessage(trans.error, 'error');
            });
    }

    function setFieldContent(fieldId, content) {
        if (!content || content.trim() === '') {
            setTimeout(() => {
                const field = $(`#${CSS_PREFIX}${fieldId}`);
                if (field.length) {
                    field.html('');
                }
            }, 50);
            return;
        }

        let cleanContent = content.replace(/\\"/g, '"').replace(/\\\//g, '/');
        const $temp = $('<div>').html(cleanContent);
        const decodedContent = $temp.html();

        setTimeout(() => {
            const field = $(`#${CSS_PREFIX}${fieldId}`);
            if (field.length) {
                field.html(decodedContent);
            }
        }, 50);
    }

    function showPreview() {
        // Use backend preview to get actual template
        const postData = {
            shop_id: currentShopId || 0,
            language_id: langMapping[currentLang] || 1,
            subject_receipt: $(`#${CSS_PREFIX}subject`).val(),
            custom_text1: $(`#${CSS_PREFIX}topText`).html(),
            custom_delivery_info: $(`#${CSS_PREFIX}deliveryDate`).val(),
            custom_text2: $(`#${CSS_PREFIX}standardText`).html(),
            custom_receipt_pos1: $(`#${CSS_PREFIX}bottomText`).html()
        };

        $.post('index.php?rt=mailTemplateCustom/previewTemplate', postData)
            .done(function(response) {
                try {
                    const result = JSON.parse(response);
                    const data = result.data || result;

                    if (!data.template) {
                        alert('Fejl: Ingen template data modtaget fra server');
                        return;
                    }

                    const previewHTML = `<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Email Preview</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .preview-note { 
            background: #e3f2fd; 
            border: 1px solid #1976d2; 
            padding: 10px; 
            margin-bottom: 20px; 
            border-radius: 4px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="preview-note">
        📧 <strong>Email Preview</strong> - Emne: ${data.subject || 'Kvittering for gavevalg'}
    </div>
    ${data.template}
    <div class="preview-note" style="margin-top: 30px;">
        💡 <strong>Bemærk:</strong> Dette er kun et preview med eksempel data
    </div>
</body>
</html>`;

                    const previewWindow = window.open('', 'MailPreview', 'width=800,height=900,scrollbars=yes,resizable=yes');
                    if (previewWindow) {
                        previewWindow.document.write(previewHTML);
                        previewWindow.document.close();
                        previewWindow.focus();
                    }
                } catch (e) {
                    alert('Fejl ved parsing af preview data: ' + e.message);
                }
            })
            .fail(function(xhr, status, error) {
                alert('Fejl ved hentning af preview: ' + error);
            });
    }

    function generatePreviewHTML(data) {
        // Generate a sample email with the custom content
        return `
        <html>
          <head>
            <meta charset='UTF-8'>
            <title>Mail Preview - ${data.subject || 'Kvittering for gavevalg'}</title>
            <style type="text/css">
              body { font-family: Arial, sans-serif; margin: 20px; }
              table { border-collapse: collapse; }
              td { padding: 8px; width: 30%; }
              .base { width: 150px; }
              h2, h3 { margin: 5px 0; }
              hr { border: 1px solid #ccc; margin: 10px 0; }
              .preview-note { 
                background: #fff3cd; 
                border: 1px solid #ffeaa7; 
                padding: 10px; 
                margin: 10px 0; 
                border-radius: 5px;
                font-size: 14px;
                color: #856404;
              }
            </style>
          </head>
          <body>
            <div class="preview-note">
              📧 <strong>Email Preview</strong> - Emne: ${data.subject || 'Kvittering for gavevalg'}
            </div>
            
            <table width='80%'>
              <tr>
                <td colspan=2>
                  <center>
                    <h2>Kvittering for gavevalg</h2><br><br>
                  </center>
                </td>
              </tr>
              
              ${data.topText ? `<tr><td colspan=2>${data.topText}<br><br><br></td></tr>` : ''}
              
              <!-- QR Code placeholder -->
              <tr><td colspan=2><div style="border: 1px dashed #ccc; padding: 20px; text-align: center; background: #f9f9f9;">📱 QR Code kommer her</div><br><br></td></tr>
              
              ${data.standardText ? `<tr><td colspan=2>${data.standardText}<br><br><br></td></tr>` : ''}
              
              <tr>
                <td align='left'><h3>Dato: </h3></td>
                <td align='right'><h3>${new Date().toLocaleDateString('da-DK')}</h3></td>
              </tr>
              <tr>
                <td align='left'><h3>Ordrenr: </h3></td>
                <td align='right'><h3>10001234</h3></td>
              </tr>
              
              ${data.deliveryInfo ? `<tr><td colspan=2>${data.deliveryInfo}<br></td></tr>` : ''}
              
              <tr><td align='left' colspan=2><hr></td></tr>
              
              <tr><td align='left'><h3>Gavevalg:</h3></td><td align='right'></td></tr>
              <tr>
                <td align='left'>Eksempel Gave - Preview</td>
                <td align='right'>
                  <table><tr><td width=70%></td><td width=30%>
                    <div style="width: 100px; height: 80px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; border: 1px dashed #ccc;">
                      📦 Billede
                    </div>
                  </td></tr></table>
                </td>
              </tr>
              
              <tr><td align='left' colspan=2><hr></td></tr>
              
              <tr><td align='left'><h3>Dine oplysninger</h3></td><td></td></tr>
              <tr><td align='left'>Navn</td><td align='right'>Eksempel Kunde</td></tr>
              <tr><td align='left'>Email</td><td align='right'>eksempel@email.dk</td></tr>
              
              ${data.bottomText ? `<tr><td colspan=2 style="padding: 20px 0;">${data.bottomText}</td></tr>` : ''}
            </table>
            
            <div class="preview-note" style="margin-top: 30px;">
              💡 <strong>Bemærk:</strong> Dette er kun et preview med eksempel data
            </div>
          </body>
        </html>`;
    }

    function handleLoadError() {
        clearForm();
        setEditMode(true);
        const trans = translations[currentLang];
        showMessage(trans.error, 'error');
    }

    // Public API
    return {
        init: function(options = {}) {
            if (!moduleLoaded) {
                currentShopId = options.shopId || window._shopId || null;
                injectCSS();
                injectHTML();
                bindEvents();
                moduleLoaded = true;
            }
        },

        open: function(options = {}) {
            console.log('CustomMailTemplate.open() called with options:', options);

            if (!moduleLoaded) {
                this.init(options);
            }

            if (options.shopId !== undefined) currentShopId = options.shopId;
            if (options.language) {
                currentLang = options.language;
                $(`.${CSS_PREFIX}lang_btn`).removeClass(`${CSS_PREFIX}active`);
                $(`.${CSS_PREFIX}lang_btn[data-lang="${currentLang}"]`).addClass(`${CSS_PREFIX}active`);
                updateTranslations();
            }

            $(`#${CSS_PREFIX}overlay`).fadeIn(300);
            loadData();
        },

        close: function() {
            $(`#${CSS_PREFIX}overlay`).fadeOut(300);
        },

        getCurrentLanguage: function() {
            return currentLang;
        },

        setLanguage: function(lang) {
            if (translations[lang]) {
                currentLang = lang;
                $(`.${CSS_PREFIX}lang_btn`).removeClass(`${CSS_PREFIX}active`);
                $(`.${CSS_PREFIX}lang_btn[data-lang="${currentLang}"]`).addClass(`${CSS_PREFIX}active`);
                updateTranslations();
                if ($(`#${CSS_PREFIX}overlay`).is(':visible')) {
                    loadData();
                }
            }
        },

        setShopId: function(id) {
            currentShopId = id;
        }
    };
})();

// Global access
window.DeliveryModule = DeliveryModule;