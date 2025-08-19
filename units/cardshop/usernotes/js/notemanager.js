function createNoteHTML(note) {

    var specialText = "";

    // Resolved
    if(note.is_resolved) {
        specialText += `<div>Løst d. ${note.resolved_datetime} af ${note.resolved_by}</div>`;
    }
    // Reminder
    else if(note.reminder_date != null) {
        specialText += `<div>Reminder d. ${note.reminder_date}</div>`;
    }

    // High priority
    else if(note.priority) {
        specialText += `<div style="color: red;">Høj prioritet</div>`;
    }

    else {
        specialText += `<div>&nbsp;</div>`;
    }

    var resolveButton = '<button class="btn btn-sm btn-info" onclick="noteManager.resolveNote('+note.id+')">Sæt til løst</button>'
    if(note.is_resolved) {
        resolveButton = '<button class="btn btn-sm btn-warning" onclick="noteManager.resolveNoteRemove('+note.id+')">Fjern løst</button>'
    }

    return $(`
        <div class="noteitem ${note.is_resolved ? 'noteitem-solved' : ''} ${note.priority ? 'noteitem-priority' : ''}" id="note_${note.id}">
            <div class="noteitemhead">
                <div style="float: right;">af: ${note.author}</div>
                <b>${note.created_at}</b>
            </div>
            <div class="noteitembody">
                ${note.text}
            </div>
            <div class="noteitemfooter">
                <div style="float: right;">
                    <button class="btn btn-sm btn-primary" onclick="noteManager.beginEdit(${note.id})">Rediger</button>
                    ${resolveButton}
                </div>
                ${specialText}
                
            </div>
        </div>
    `);
}

class NoteManager {
    constructor(endpoint, companyId, companyHash) {
        this.endpoint = endpoint;
        this.companyId = companyId;
        this.companyHash = companyHash;
        this.editingNoteId = null;
        this.setupEventListeners();
        this.updateUIForCreateState();
    }

    setupEventListeners() {

        // Save button
        $('#saveNoteButton').click(function() {
            // Indsamle data fra formular
            const text = $('textarea[name="notefield"]').val();
            const priority = $('input[name="priority"]').is(':checked') ? 1 : 0;
            const usereminder = $('input[name="addreminder"]').is(':checked') ? 1 : 0;
            const reminderDate = $('input[name="addreminder"]').is(':checked') ? $('input[name="reminderdate"]').val() : null;

            // Tjek om vi er i redigeringstilstand
            if (noteManager.editingNoteId) {
                // Opdater note
                noteManager.updateNote(noteManager.editingNoteId, text, priority,usereminder, reminderDate);
            } else {
                // Opret ny note
                noteManager.createNote(text, priority,usereminder, reminderDate);
            }
        });

        // Delete button
        $('#deleteNoteButton').click(function() {
            // Tjek om vi er i redigeringstilstand
            if (noteManager.editingNoteId) {
                // Bekræft sletning med brugeren
                if (confirm('Er du sikker på, at du vil slette denne note?')) {
                    noteManager.deleteNote(noteManager.editingNoteId);
                    noteManager.resetForm(); // Nulstil formen efter sletning
                }
            } else {
                this.setError('Ingen note er valgt til sletning.');
            }
        });

        $('#cancelEditButton').click(function() {
            noteManager.cancelEdit();
        });


        $('input[name="addreminder"]').change(function() {
            if ($(this).is(':checked')) {
                $('input[name="reminderdate"]').parent().show();
            } else {
                $('input[name="reminderdate"]').parent().hide();
            }
        });


        $('.newnote').bind('click', function() {
            noteManager.cancelEdit();
            noteManager.resetForm();
        });

    }

    // Hjælpefunktion til POST requests
    post(action, data) {
        this.clearError();
        return $.ajax({
            url: `${this.endpoint}${action}`,
            type: 'POST',
            data: { ...data, companyid: this.companyId, companyhash: this.companyHash },
            dataType: 'json', // Sørger for at forvente JSON respons
            contentType: 'application/x-www-form-urlencoded; charset=UTF-8' // Indstiller korrekt Content-Type for POST data
        });
    }



    // Henter og viser noter
    loadAndDisplayNotes() {
        this.post('getlist', {}).done(response => {
            
            if(response.success == false) {
                this.setError('Fejl ved indlæsning af noter: ' + response.error);
                return;
            }
            
            var notes = response.notes;
            console.log(notes);
            const notesContainer = $('.notelist');
            notesContainer.empty(); // Ryd eksisterende noter

            notes.forEach(note => {
                const noteElement = createNoteHTML(note);
                notesContainer.append(noteElement);
            });
        });
    }

    // Opretter en ny note
    createNote(text, priority, usereminder, reminderDate) {
        if (this.editingNoteId) {
            this.setError('Du er i redigeringstilstand. Annuller redigeringen eller gem den aktuelle note først.');
            return;
        }

        this.post('create', { text: text, priority:priority, usereminder: usereminder, reminderDate:reminderDate }).done(response => {
            if (!response.success) {
                this.setError('Fejl ved oprettelse af note: ' + response.error);
                return;
            }

            const note = response.note;
            const noteElement = createNoteHTML(note);
            $('.notelist').prepend(noteElement); // Tilføjer den nye note øverst i listen
            this.resetForm();

        }).fail(() => {
            this.setError('Netværksfejl ved oprettelse af note.');
        });
    }

    resetForm() {
        // Ryd tekstområde
        $('textarea[name="notefield"]').val('');

        // Fjern markering fra alle tjekbokse
        $('input[type="checkbox"]').prop('checked', false);

        // Nulstil eventuelle datavælgere
        $('input[type="date"]').val('');

        // Sæt editingNoteId til null
        this.editingNoteId = null;

        // Opdater UI til at reflektere en "ny note" tilstand
        this.updateUIForCreateState();

    }

    // Sletter en note
    deleteNote(noteId) {
        this.post('delete', { noteid: noteId }).done(response => {
            if (!response.success) {
                this.setError('Fejl ved sletning af note: ' + response.error);
                return;
            }
            // Fjern note fra DOM
            $(`#note_${noteId}`).remove();
        }).fail(() => {
            this.setError('Netværksfejl ved sletning af note.');
        });
    }

    // Starter redigering af en note
    beginEdit(noteId) {
        this.editingNoteId = noteId;
        this.post('edit', { noteid: noteId }).done(response => {
            if (!response.success) {
                this.setError('Fejl ved indlæsning af note: ' + response.error);
                return;
            }

            const note = response.note;
            // Udfyld formular med note data
            $('textarea[name="notefield"]').val(note.text);
            $('input[name="priority"]').prop('checked', note.priority);
            if (note.reminder_date) {
                $('input[name="addreminder"]').prop('checked', true);
                $('input[name="reminderdate"]').val(note.reminder_date);
            } else {
                $('input[name="addreminder"]').prop('checked', false);

            }

            // Opdater UI til at reflektere en "rediger note" tilstand
            this.updateUIForEditState();
        }).fail(() => {
            this.setError('Netværksfejl ved indlæsning af note til redigering.');
        });
    }
    
    clearError() {
        $('#notevieweeror').hide();
    }
    
    setError(message) {
        $('#notevieweeror').html(message).show();
    }

    // Annullerer redigering
    cancelEdit() {
        
        this.editingNoteId = null;
        
        // Nulstil formular
        this.resetForm();

        // Opdater UI til at reflektere en "ny note" tilstand
        this.updateUIForCreateState();
    }

    // Opdaterer en eksisterende note
    // Opdaterer en eksisterende note
    updateNote(noteId, text, priority,usereminder, reminderDate) {
        this.post('update', { noteid: noteId, text: text, priority: priority, usereminder: usereminder, reminderDate: reminderDate }).done(response => {
            if (!response.success) {
                this.setError('Fejl ved opdatering af note: ' + response.error);
                return;
            }
            // Opdater note i DOM
            const updatedNote = response.note;
            const noteElement = createNoteHTML(updatedNote);
            $(`#note_${noteId}`).replaceWith(noteElement);

            // Nulstil formular og opdater UI
            this.resetForm();
        }).fail(() => {
            this.setError('Netværksfejl ved opdatering af note.');
        });
    }

    // Løser en note
    resolveNote(noteId) {
        this.post('resolve', { noteid: noteId }).done(response => {
            if (!response.success) {
                this.setError('Fejl ved markering af note som løst: ' + response.error);
                return;
            }
            const updatedNote = response.note;
            const noteElement = createNoteHTML(updatedNote);
            $(`#note_${noteId}`).replaceWith(noteElement);

        }).fail(() => {
            this.setError('Netværksfejl ved markering af note som løst.');
        });
    }

    resolveNoteRemove(noteId) {
        this.post('resolveremove', { noteid: noteId }).done(response => {
            if (!response.success) {
                this.setError('Fejl ved markering af note som ikke løst: ' + response.error);
                return;
            }
            const updatedNote = response.note;
            const noteElement = createNoteHTML(updatedNote);
            $(`#note_${noteId}`).replaceWith(noteElement);

        }).fail(() => {
            this.setError('Netværksfejl ved markering af note som ikke løst.');
        });
    }


    // Hjælpefunktioner til at opdatere UI
    updateUIForEditState() {
        // Skift knapper og overskrifter til redigeringstilstand
        $('#saveNoteButton').text('Opdater note'); // Ændrer teksten på "Gem" knappen til "Opdater note"
        $('#cancelEditButton').show(); // Viser "Annuller" knappen
        $('#deleteNoteButton').show(); // Viser "Slet" knappen
        $('.notesubheaderedit').text('Rediger note'); // Opdaterer overskriften
        $('input[name="addreminder"]').trigger('change');
    }

    updateUIForCreateState() {
        // Skift knapper og overskrifter til oprettelsestilstand
        $('#saveNoteButton').text('Gem note'); // Ændrer teksten på "Gem" knappen tilbage til "Gem note"
        $('#cancelEditButton').hide(); // Skjuler "Annuller" knappen
        $('#deleteNoteButton').hide(); // Skjuler "Slet" knappen
        $('.notesubheaderedit').text('Opret ny note'); // Opdaterer overskriften
        $('input[name="addreminder"]').trigger('change');
    }

}

