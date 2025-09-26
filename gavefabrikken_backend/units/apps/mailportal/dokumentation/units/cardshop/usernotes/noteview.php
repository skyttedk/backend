<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company notes</title>
    <link href="/gavefabrikken_backend/units/assets/libs/bootstrap.min.css" rel="stylesheet">
    <script src="/gavefabrikken_backend/units/assets/libs/jquery.min.js"></script>
    <script src="/gavefabrikken_backend/units/assets/libs/popper.min.js"></script>
    <script src="/gavefabrikken_backend/units/assets/libs/bootstrap.min.js"></script>
    <link rel="stylesheet" href="/gavefabrikken_backend/units/assets/fontawesome.css">
    <script src="<?php echo $assetPath; ?>js/notemanager.js"></script>

    <style>

        .noteheader { padding: 15px; background-color: #B8B8B8; }
        .notesubheader { padding: 15px; background-color: #f1f1f1; padding-top: 10px; padding-bottom: 10px; }
        .noteform { padding: 15px;}

        .noteitem {  border-bottom: 2px solid #A0A0A0; border-left: 6px solid #A0A0A0; }
        .noteitem-mine { border-left: 6px solid #6895D2; }
        .noteitem-priority { border-left: 6px solid #D04848; }
        .noteitem-date { border-left: 6px solid #FDE767; }
        .noteitem-solved { border-left: 6px solid #BFEA7C; }

        .noteitemhead { padding: 9px; padding-right: 15px; padding-top: 5px; padding-bottom: 5px; background: #FAFAFA;}
        .noteitembody {padding: 9px; padding-right: 15px;padding-top: 8px; padding-bottom: 8px; }
        .noteitemfooter { padding: 9px; padding-top: 10px; padding-right: 15px; padding-bottom: 10px; background: #FCFCFC; }

    </style>
</head>
<body>
    <div class="noteheader">
        <div style="float: right; margin-right: 10px;"><button class="btn btn-sm btn-primary newnote">ny note</button></div>
        Virksomhed: <b><?php echo $company->name; ?></b> - CVR: <?php echo $company->cvr; ?>

    </div>
    <div class="notebody" style="margin: 20px; margin-top: 0px;">
        <div class="row">
            <div class="col-md-6">
                <div class="noteeditor">

                    <div class="notesubheader notesubheaderedit">
                        Opret ny note
                    </div>
                    <div class="noteform">
                        <textarea style="width: 100%; height: 300px;" name="notefield"></textarea>
                        <div>
                            <label>
                                <input type="checkbox" name="priority" value="1"> Høj prioritet
                            </label>
                        </div>
                        <div>
                            <label>
                                <input type="checkbox" name="addreminder" value="1"> Tilføj huske-dato til denne note
                            </label>
                        </div>
                        <div style="display: none; padding-left: 20px;">
                            Dato: <input type="date" name="reminderdate">
                        </div>
                        <div style="text-align: right; margin-top: 10px; border-top: 1px solid #B8B8B8; padding-top: 10px; padding-bottom: 10px; border-bottom: 1px solid #B8B8B8;">
                            <button class="btn btn-sm btn-danger" id="deleteNoteButton" style="float: left;">Slet note</button>
                            <button class="btn btn-sm btn-secondary" id="cancelEditButton">Annuller</button>

                            <button class="btn btn-sm btn-primary" id="saveNoteButton">Gem note</button>
                        </div>
                        
                        <div id="notevieweeror" style="background: #FFDDDD; margin-top: 8px; border-radius: 4px; padding: 10px; text-align: center; color: red; font-size: 1.2em;"></div>
                        
                    </div>


                </div>
            </div>
            <div class="col-md-6">
                <div class="noteview">
                    <div class="notesubheader">
                        Note liste
                    </div>
                    <div class="notelist">

                    </div>
                </div>
            </div>
        </div>
    </div>

<script>

    // Brug af klassen
    var notesendpoint = '<?php echo $servicePath; ?>';
    var noteManager = new NoteManager(notesendpoint, <?php echo $company->id; ?>,'<?php echo $company->token; ?>');

    // Eksempel: Hente og vise noter ved indlæsning af siden
    $(document).ready(() => {
        noteManager.loadAndDisplayNotes();
    });


</script>

</body>
</html>
