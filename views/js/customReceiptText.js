// DeliveryModule.js - Integreret med ReceiptAdditionsController
const DeliveryModule = (function() {
    'use strict';
    // Private variabler
    let currentLang = 'da';
    let currentRecordId = null;
    let isEditMode = false;
    let moduleLoaded = false;
    let modalElement = null;
    let companyId = null;
    let shopId = null;
    const CSS_PREFIX = 'dlvm_'; // Delivery Language Version Module

    // Sprog mapping til database IDs
    const langMapping = {
        'da': 1,  // Dansk
        'en': 2,  // Engelsk
        'de': 3,  // Tysk
        'no': 4,  // Norsk
        'sv': 5   // Svensk
    }

    function clearForm() {
        console.log('clearForm v1.2: Clearing all fields to empty');
        $(`#${CSS_PREFIX}topText`).html('');
        $(`#${CSS_PREFIX}standardText`).html('');
        $(`#${CSS_PREFIX}bottomText`).html('');
        $(`#${CSS_PREFIX}deliveryDate`).val('');

        // Sørg for at toolbar state også nulstilles
        $(`.${CSS_PREFIX}toolbar_btn`).removeClass('active');
    };

    // Oversættelser (samme som før)
    const translations = {
        da: {
            modalTitle: 'Kvitteringstekster v1.2',
            topSection: 'Øverste sektion (før kvittering)',
            topText: 'Tekst øverst:',
            standardSection: 'Standard tekst',
            standardText: 'Standard tekst:',
            deliverySection: 'Leveringsinformation',
            deliveryDate: 'Leveringsdato:',
            deliveryNotes: 'Leveringsnoter:',
            bottomSection: 'Bundsektion (efter alt indhold)',
            bottomText: 'Tekst i bunden:',
            read: 'Læs',
            edit: 'Rediger',
            save: 'Gem',
            preview: 'Forhåndsvisning',
            close: 'Luk',
            loading: 'Henter data...',
            datePlaceholder: "F.eks. '15. marts 2024' eller 'Uge 12'",
            injectionInfo: 'Placering i kvittering:',
            saved: 'Data gemt succesfuldt!',
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
            modalTitle: 'Receipt Texts v1.2',
            topSection: 'Top Section (before receipt)',
            topText: 'Top text:',
            standardSection: 'Standard Text',
            standardText: 'Standard text:',
            deliverySection: 'Delivery Information',
            deliveryDate: 'Delivery date:',
            deliveryNotes: 'Delivery notes:',
            bottomSection: 'Bottom Section (after all content)',
            bottomText: 'Bottom text:',
            read: 'Read',
            edit: 'Edit',
            save: 'Save',
            preview: 'Preview',
            close: 'Close',
            loading: 'Loading data...',
            datePlaceholder: "E.g. 'March 15, 2024' or 'Week 12'",
            injectionInfo: 'Placement in receipt:',
            saved: 'Data saved successfully!',
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
            modalTitle: 'Quittungstexte v1.2',
            topSection: 'Oberer Bereich (vor Quittung)',
            topText: 'Text oben:',
            standardSection: 'Standardtext',
            standardText: 'Standardtext:',
            deliverySection: 'Lieferinformationen',
            deliveryDate: 'Lieferdatum:',
            deliveryNotes: 'Liefernotizen:',
            bottomSection: 'Unterer Bereich (nach allem Inhalt)',
            bottomText: 'Text unten:',
            read: 'Lesen',
            edit: 'Bearbeiten',
            save: 'Speichern',
            preview: 'Vorschau',
            close: 'Schließen',
            loading: 'Daten werden geladen...',
            datePlaceholder: "Z.B. '15. März 2024' oder 'Woche 12'",
            injectionInfo: 'Platzierung in Quittung:',
            saved: 'Daten erfolgreich gespeichert!',
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
            modalTitle: 'Kvitteringstekster v1.2',
            topSection: 'Øverste seksjon (før kvittering)',
            topText: 'Tekst øverst:',
            standardSection: 'Standardtekst',
            standardText: 'Standardtekst:',
            deliverySection: 'Leveringsinformasjon',
            deliveryDate: 'Leveringsdato:',
            deliveryNotes: 'Leveringsnotater:',
            bottomSection: 'Bunnseksjon (etter alt innhold)',
            bottomText: 'Tekst i bunnen:',
            read: 'Les',
            edit: 'Rediger',
            save: 'Lagre',
            preview: 'Forhåndsvisning',
            close: 'Lukk',
            loading: 'Henter data...',
            datePlaceholder: "F.eks. '15. mars 2024' eller 'Uke 12'",
            injectionInfo: 'Plassering i kvittering:',
            saved: 'Data lagret!',
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
            modalTitle: 'Kvittotexter v1.2',
            topSection: 'Översta sektionen (före kvitto)',
            topText: 'Text överst:',
            standardSection: 'Standardtext',
            standardText: 'Standardtext:',
            deliverySection: 'Leveransinformation',
            deliveryDate: 'Leveransdatum:',
            deliveryNotes: 'Leveransanteckningar:',
            bottomSection: 'Nedersta sektionen (efter allt innehåll)',
            bottomText: 'Text nederst:',
            read: 'Läs',
            edit: 'Redigera',
            save: 'Spara',
            preview: 'Förhandsvisning',
            close: 'Stäng',
            loading: 'Hämtar data...',
            datePlaceholder: "T.ex. '15 mars 2024' eller 'Vecka 12'",
            injectionInfo: 'Placering i kvitto:',
            saved: 'Data sparad!',
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

    // CSS som string (samme som før)
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
            width: 90%; max-width: 900px; max-height: 90vh; overflow: hidden;
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
                    <h2 class="${CSS_PREFIX}title" id="${CSS_PREFIX}modalTitle">Kvitteringstekster v1.2</h2>
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
                            <h3 class="${CSS_PREFIX}section_title" data-translate="topSection">Øverste sektion</h3>
                            <p class="${CSS_PREFIX}injection_note">📍 Placeres helt øverst før kvitteringen</p>
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
                            <p class="${CSS_PREFIX}injection_note">📍 Placeres efter leveringsinformation</p>
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
                            <p class="${CSS_PREFIX}injection_note">📍 Placeres helt nederst efter alt indhold</p>
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

    // Private funktioner
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
        $(`#${CSS_PREFIX}deliveryDate`).attr('placeholder', trans.datePlaceholder);
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

            // NULSTIL toolbar state så der ikke er fed/kursiv aktiv fra start
            setTimeout(() => {
                $(`.${CSS_PREFIX}toolbar_btn`).removeClass('active');
                console.log('setEditMode: Cleared all toolbar active states');
            }, 100);
        } else {
            $(`#${CSS_PREFIX}form`).find('input').addClass(`${CSS_PREFIX}readonly`);
            $(`.${CSS_PREFIX}rich_editor`).addClass(`${CSS_PREFIX}readonly`);
            $(`.${CSS_PREFIX}toolbar`).hide();
        }
    }

    function showMessage(message, type = 'success') {
        console.log(`showMessage v1.2: Showing ${type} message:`, message);

        const messageClass = type === 'success' ? `${CSS_PREFIX}message_success` : `${CSS_PREFIX}message_error`;
        const icon = type === 'success' ? '✅' : '❌';
        const messageHtml = `<div class="${CSS_PREFIX}message ${messageClass}">${icon} ${message}</div>`;

        const $container = $(`#${CSS_PREFIX}messageContainer`);
        $container.html(messageHtml);

        // Scroll til toppen så beskeden er synlig
        $(`#${CSS_PREFIX}content`).scrollTop(0);

        // Auto-hide efter 4 sekunder (lidt længere for at være sikker på folk ser det)
        setTimeout(() => {
            $container.fadeOut(500, function() {
                $(this).empty().show();
            });
        }, 4000);

        console.log(`showMessage v1.2: Message displayed successfully`);
    }

    function bindEvents() {
        // Sprog skift
        $(document).on('click', `.${CSS_PREFIX}lang_btn`, function() {
            const newLang = $(this).data('lang');
            if (newLang !== currentLang) {
                $(`.${CSS_PREFIX}lang_btn`).removeClass(`${CSS_PREFIX}active`);
                $(this).addClass(`${CSS_PREFIX}active`);
                currentLang = newLang;
                updateTranslations();
                loadData(); // Reload data for new language
            }
        });

        // Modal kontrol
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

        // Opdater toolbar state
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

        // Save button - forenklet til kun at gemme
        $(document).on('click', `#${CSS_PREFIX}saveBtn`, function(e) {
            e.preventDefault();
            saveData(); // Altid gem - ingen toggle logik
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
            language: langMapping[currentLang]
        };

        if (companyId) postData.company_id = companyId;
        if (shopId) postData.shop_id = shopId;

        console.log('LoadData v1.2 - Sending request with data:', postData);

        $.post('index.php?rt=ReceiptAdditions/getByLanguage', postData)
            .done(function(response) {
                console.log('LoadData v1.2 - Raw response:', response);

                try {
                    // Parse response hvis det er en string
                    const responseObj = typeof response === 'string' ? JSON.parse(response) : response;
                    console.log('LoadData v1.2 - Parsed response object:', responseObj);

                    // Server returnerer data i format: {status: "1", data: [...], message: ""}
                    // Vi skal bruge responseObj.data
                    let actualData = null;

                    if (responseObj.data && Array.isArray(responseObj.data)) {
                        actualData = responseObj.data;
                    } else if (Array.isArray(responseObj)) {
                        actualData = responseObj;
                    }

                    console.log('LoadData v1.2 - Extracted data array:', actualData);

                    if (actualData && actualData.length > 0) {
                        const record = actualData[0];
                        currentRecordId = record.id;
                        console.log('LoadData v1.2 - SUCCESS! Found record with ID:', currentRecordId);
                        console.log('LoadData v1.2 - Record details:', record);

                        // Load content with delay for proper rendering
                        setTimeout(() => {
                            setFieldContent('topText', record.top_text);
                            setFieldContent('standardText', record.standard_text);
                            setFieldContent('bottomText', record.bottom_text);
                            $(`#${CSS_PREFIX}deliveryDate`).val(record.delivery_date || '');

                            console.log('LoadData v1.2 - All content set!');
                        }, 100);

                    } else {
                        console.log('LoadData v1.2 - No records found in data array');
                        console.log('LoadData v1.2 - Will NOT create anything automatically');
                        console.log('LoadData v1.2 - Record will only be created when user clicks Save');
                        currentRecordId = null;
                        clearForm();
                    }

                    // Start in edit mode after a delay
                    setTimeout(() => {
                        setEditMode(true);
                        console.log('LoadData v1.2 - Edit mode enabled');
                    }, 200);

                } catch (e) {
                    console.error('LoadData v1.2 - Parse error:', e, 'Response:', response);
                    handleLoadError();
                }
            })
            .fail(function(xhr, status, error) {
                console.error('LoadData v1.2 - Request failed:', {xhr, status, error});
                console.error('LoadData v1.2 - Response text:', xhr.responseText);
                handleLoadError();
            })
            .always(function() {
                $(`#${CSS_PREFIX}loading`).hide();
                $(`#${CSS_PREFIX}form`).show();
            });
    }

    function saveData() {
        if (!isEditMode) return;

        const formData = {
            language: langMapping[currentLang],
            top_text: $(`#${CSS_PREFIX}topText`).html(),
            standard_text: $(`#${CSS_PREFIX}standardText`).html(),
            delivery_date: $(`#${CSS_PREFIX}deliveryDate`).val(),
            bottom_text: $(`#${CSS_PREFIX}bottomText`).html()
        };

        if (companyId) formData.company_id = companyId;
        if (shopId) formData.shop_id = shopId;

        // Tjek om der faktisk er noget indhold at gemme
        const hasContent = formData.top_text.trim() ||
            formData.standard_text.trim() ||
            formData.delivery_date.trim() ||
            formData.bottom_text.trim();

        if (!hasContent && !currentRecordId) {
            console.log('SaveData v1.2 - No content to save and no existing record');
            const trans = translations[currentLang];
            showMessage(trans.noContent, 'error');
            return;
        }

        const isNewRecord = !currentRecordId;
        const endpoint = currentRecordId ?
            'index.php?rt=ReceiptAdditions/update' :
            'index.php?rt=ReceiptAdditions/create';

        if (currentRecordId) {
            formData.id = currentRecordId;
        }

        console.log('SaveData v1.2 - Current record ID:', currentRecordId);
        console.log('SaveData v1.2 - Action:', isNewRecord ? 'CREATING NEW RECORD' : 'UPDATING EXISTING RECORD');
        console.log('SaveData v1.2 - Using endpoint:', endpoint);
        console.log('SaveData v1.2 - Form data:', formData);

        $.post(endpoint, formData)
            .done(function(response) {
                console.log('SaveData v1.2 - Raw response:', response);

                try {
                    // Tjek om response allerede er et object
                    const responseObj = typeof response === 'string' ? JSON.parse(response) : response;
                    console.log('SaveData v1.2 - Parsed response:', responseObj);

                    // Server kan returnere data i format: {status: "1", data: {...}, message: ""}
                    const result = responseObj.data || responseObj;
                    console.log('SaveData v1.2 - Extracted result:', result);

                    if (result && result.id) {
                        currentRecordId = result.id;
                        console.log('SaveData v1.2 - Updated current record ID to:', currentRecordId);

                        if (isNewRecord) {
                            console.log('SaveData v1.2 - SUCCESS: New record created with ID:', currentRecordId);
                        } else {
                            console.log('SaveData v1.2 - SUCCESS: Existing record updated');
                        }
                    }

                    const trans = translations[currentLang];
                    showMessage(trans.saved, 'success');
                    console.log('SaveData v1.2 - Showing success notification:', trans.saved);
                } catch (e) {
                    console.error('SaveData v1.2 - Parse error:', e, 'Response:', response);
                    const trans = translations[currentLang];
                    showMessage(trans.error, 'error');
                }
            })
            .fail(function(xhr, status, error) {
                console.error('SaveData v1.2 - Request failed:', {xhr, status, error});
                console.error('SaveData v1.2 - Response text:', xhr.responseText);
                const trans = translations[currentLang];
                showMessage(trans.error, 'error');
            });
    }

    function setFieldContent(fieldId, content) {
        // Hvis der ikke er indhold, sæt til tom
        if (!content || content.trim() === '') {
            console.log(`setFieldContent v1.2: Setting ${fieldId} to empty (no content provided)`);
            setTimeout(() => {
                const field = $(`#${CSS_PREFIX}${fieldId}`);
                if (field.length) {
                    field.html('');
                }
            }, 50);
            return;
        }

        console.log(`setFieldContent v1.2: Setting ${fieldId} with raw content:`, content);

        // Unescape JSON escaped content (fjern backslashes)
        let cleanContent = content.replace(/\\"/g, '"').replace(/\\\//g, '/');

        // Decode HTML entities
        const $temp = $('<div>').html(cleanContent);
        const decodedContent = $temp.html();

        console.log(`setFieldContent v1.2: Cleaned content for ${fieldId}:`, decodedContent);

        setTimeout(() => {
            const field = $(`#${CSS_PREFIX}${fieldId}`);
            if (field.length) {
                field.html(decodedContent);
                console.log(`setFieldContent v1.2: Successfully set content for ${fieldId}`);

                // Verify content was set
                setTimeout(() => {
                    const verifyContent = field.html();
                    console.log(`setFieldContent v1.2: Verification - ${fieldId} now contains:`, verifyContent);
                }, 50);
            } else {
                console.error(`setFieldContent v1.2: Field ${fieldId} not found!`);
            }
        }, 50);
    }

    function showPreview() {
        // Samle nuværende data fra formularen
        const previewData = {
            topText: $(`#${CSS_PREFIX}topText`).html(),
            standardText: $(`#${CSS_PREFIX}standardText`).html(),
            deliveryDate: $(`#${CSS_PREFIX}deliveryDate`).val(),
            bottomText: $(`#${CSS_PREFIX}bottomText`).html()
        };

        // Generer kvitterings HTML
        const receiptHTML = generateReceiptHTML(previewData);

        // Åbn i nyt vindue
        const previewWindow = window.open('', 'ReceiptPreview', 'width=800,height=900,scrollbars=yes,resizable=yes');

        if (previewWindow) {
            previewWindow.document.write(receiptHTML);
            previewWindow.document.close();
            previewWindow.focus();
        } else {
            const trans = translations[currentLang];
            showMessage('Kunne ikke åbne forhåndsvisning. Tillad pop-ups for denne side.', 'error');
        }
    }

    function generateReceiptHTML(data) {
        const currentDate = new Date().toLocaleString('da-DK');
        const orderNumber = '1000' + Math.floor(Math.random() * 1000);

        return `
        <html>
          <head>
            <meta charset='UTF-8'>
            <title>Kvittering - Forhåndsvisning</title>
            <style type="text/css">
              body { font-family: Arial, sans-serif; margin: 20px; }
              table { border-collapse: collapse; }
              td { padding: 8px; }
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
              a { color: #0066cc; }
            </style>
          </head>
          <body>
            <div class="preview-note">
              📋 <strong>Forhåndsvisning</strong> - Dette er kun et eksempel på hvordan kvitteringen vil se ud
            </div>
            
            <table width='80%'>
              ${data.topText ? `<tr><td colspan=2>${data.topText}<br><br></td></tr>` : ''}
              
              <tr>
                <td colspan=2>
                  <center>
                    <h2>Kvittering for gavevalg</h2><br><br>
                  </center>
                </td>
              </tr>
              
              <tr><td colspan=2><br><br><br></td></tr>
              <tr><td colspan=2><br><br><br></td></tr>
              
              <tr>
                <td align='left'><h3>Dato: </h3></td>
                <td align='right'><h3>${currentDate}</h3></td>
              </tr>
              <tr>
                <td align='left'><h3>Ordrenr: </h3></td>
                <td align='right'><h3>${orderNumber}</h3></td>
              </tr>
              
              <tr><td align='left' colspan=2><hr></td></tr>
              
              <tr>
                <td align='left'><h3>Gavevalg:</h3></td>
                <td align='right'></td>
              </tr>
              <tr>
                <td align='left'>Eksempel Gave - Forhåndsvisning</td>
                <td align='right'>
                  <table>
                    <tr>
                      <td width=70%></td>
                      <td width=30%>
                        <div style="width: 150px; height: 100px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; border: 1px dashed #ccc;">
                          📦 Gavebillede
                        </div>
                      </td>
                    </tr>
                  </table>
                </td>
              </tr>
              
              <tr><td align='left' colspan=2><hr></td></tr>
              
              <tr>
                <td align='left'><h3>Dine oplysninger</h3></td>
                <td></td>
              </tr>
              <tr><td align='left'>Navn</td><td align='right'>Eksempel Kunde</td></tr>
              <tr><td align='left'>Email</td><td align='right'>eksempel@email.dk</td></tr>
              <tr><td align='left'>Gaveklubben tilmelding</td><td align='right'>nej</td></tr>
              <tr><td align='left'>Adresse 1</td><td align='right'>Eksempel Vej 123</td></tr>
              <tr><td align='left'>Postnummer</td><td align='right'>1234</td></tr>
              <tr><td align='left'>By</td><td align='right'>Eksempel By</td></tr>
              <tr><td align='left'>Mobilnummer</td><td align='right'>12345678</td></tr>
              
              ${data.deliveryDate ? `
              <tr><td colspan="2"><hr style="margin: 20px 0;"></td></tr>
              <tr><td align="left"><h3>Leveringsinformation</h3></td><td></td></tr>
              <tr><td align="left">Leveringsdato</td><td align="right">${data.deliveryDate}</td></tr>
              ` : ''}
              
              ${data.standardText ? `
              <tr><td colspan="2" style="padding: 20px 0;">
                ${data.standardText}
              </td></tr>
              ` : ''}
              
              <tr><td colspan="2"><hr><br>
                <p>Dit gavevalg kan ændres i 24 timer. Derefter overføres dit valg til pakkeriet, og det vil herefter ikke være muligt at foretage ændringer.</p>
                <p>Der kan opleves ekstra leveringstid op til 20 arbejdsdage i december, januar og februar (normal leveringstid 10 arbejdsdage)</p>
                <p>Har du bestilt et oplevelsesgavekort, ophold eller cruise vil det endelige gavekort blive sendt til dig. Denne kvittering kan derfor ikke benyttes som gavebevis.</p>
                <p>Husk, at du selv kan tilkøbe flere designprodukter til gode priser på <a href="https://www.gaveklubben.dk/" target="_blank">Gaveklubben</a></p>
                <br><br>
              </td></tr>
              
              ${data.bottomText ? `
              <tr><td colspan="2" style="padding: 20px 0; border-top: 1px solid #ccc;">
                ${data.bottomText}
              </td></tr>
              ` : ''}
              
            </table>
            
            <div class="preview-note" style="margin-top: 30px;">
              💡 <strong>Bemærk:</strong> Personlige oplysninger er erstattet med eksempel data i denne forhåndsvisning
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
                // Set company and shop IDs
                companyId = options.companyId || window._companyId || null;
                shopId = options.shopId || window._shopId || null;

                injectCSS();
                injectHTML();
                bindEvents();
                moduleLoaded = true;
            }
        },

        open: function(options = {}) {
            console.log('DeliveryModule.open() called with options:', options);

            if (!moduleLoaded) {
                console.log('Module not loaded, initializing...');
                this.init(options);
            }

            // Update IDs if provided
            if (options.companyId !== undefined) companyId = options.companyId;
            if (options.shopId !== undefined) shopId = options.shopId;
            if (options.language) {
                currentLang = options.language;
                $(`.${CSS_PREFIX}lang_btn`).removeClass(`${CSS_PREFIX}active`);
                $(`.${CSS_PREFIX}lang_btn[data-lang="${currentLang}"]`).addClass(`${CSS_PREFIX}active`);
                updateTranslations();
            }

            console.log('Module state before opening:', {
                currentLang,
                companyId,
                shopId,
                currentRecordId
            });

            $(`#${CSS_PREFIX}overlay`).fadeIn(300);
            loadData();
        },

        close: function() {
            $(`#${CSS_PREFIX}overlay`).fadeOut(300);
        },

        getContentForReceipt: function(callback, options = {}) {
            const postData = {
                language: langMapping[options.language || currentLang]
            };

            if (options.companyId || companyId) postData.company_id = options.companyId || companyId;
            if (options.shopId || shopId) postData.shop_id = options.shopId || shopId;

            $.post('index.php?rt=ReceiptAdditions/getByLanguage', postData)
                .done(function(response) {
                    try {
                        const data = JSON.parse(response);
                        if (data && data.length > 0) {
                            const record = data[0];
                            callback({
                                topContent: record.top_text || '',
                                deliveryDate: record.delivery_date || '',
                                standardText: record.standard_text || '',
                                bottomContent: record.bottom_text || ''
                            });
                        } else {
                            callback(null);
                        }
                    } catch (e) {
                        callback(null);
                    }
                })
                .fail(function() {
                    callback(null);
                });
        },

        injectIntoReceipt: function(receiptSelector, options = {}) {
            this.getContentForReceipt(function(content) {
                if (!content) return;

                const $receipt = $(receiptSelector);
                if (!$receipt.length) return;

                // Øverste sektion
                if (content.topContent) {
                    $receipt.find('table').first().prepend(`
                        <tr><td colspan="2" style="padding: 20px 0; border-bottom: 1px solid #eee;">
                            ${content.topContent}
                        </td></tr>
                    `);
                }

                // Leveringsinfo
                if (content.deliveryDate) {
                    const $customerInfo = $receipt.find('h3:contains("Dine oplysninger"), h3:contains("Your information"), h3:contains("Ihre Informationen")').closest('tr');
                    if ($customerInfo.length) {
                        let deliveryHTML = '<tr><td colspan="2"><hr style="margin: 20px 0;"></td></tr>';
                        deliveryHTML += '<tr><td align="left"><h3>Leveringsinformation</h3></td><td></td></tr>';
                        deliveryHTML += `<tr><td align="left">Leveringsdato</td><td align="right">${content.deliveryDate}</td></tr>`;

                        $receipt.find('tr:contains("Mobilnummer"), tr:contains("Phone"), tr:contains("Telefon")').last().after(deliveryHTML);
                    }
                }

                // Standard tekst
                if (content.standardText) {
                    $receipt.find('table tr:last').before(`
                        <tr><td colspan="2" style="padding: 20px 0;">
                            ${content.standardText}
                        </td></tr>
                    `);
                }

                // Bund
                if (content.bottomContent) {
                    $receipt.find('table').last().append(`
                        <tr><td colspan="2" style="padding: 20px 0; border-top: 1px solid #ccc;">
                            ${content.bottomContent}
                        </td></tr>
                    `);
                }
            }, options);
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

        setCompanyId: function(id) {
            companyId = id;
        },

        setShopId: function(id) {
            shopId = id;
        }
    };
})();

// Global tilgængelighed
window.DeliveryModule = DeliveryModule;