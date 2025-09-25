<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Widget dashboard</title>
    <link href="<?php echo $assetPath; ?>/assets/bootstrap.min.css" rel="stylesheet">
    <script src="<?php echo $assetPath; ?>/assets/jquery.min.js"></script>
    <script src="<?php echo $assetPath; ?>/assets/popper.min.js"></script>
    <script src="<?php echo $assetPath; ?>/assets/bootstrap.min.js"></script>
    <link rel="stylesheet" href="<?php echo $assetPath; ?>/assets/fontawesome.css">
    <style>
        .card {
            margin-bottom: 10px;
            -webkit-box-shadow: 7px 10px 22px -13px rgba(0,0,0,0.59);
            -moz-box-shadow: 7px 10px 22px -13px rgba(0,0,0,0.59);
            box-shadow: 7px 10px 22px -13px rgba(0,0,0,0.59);
        }
        .card-body {
            padding: 0.5rem;
        }

        .run-history-box { display: inline-block; width: 10px; height: 10px; margin: 2px; }

        .card-status-0 { background-color: #fff3cd; }
        .card-status-1 { background-color: #d4edda; }
        .card-status-3, .card-status-5 { background-color: #f8d7da; }
        .run-history-0 { background-color: #ffeeba; }
        .run-history-1 { background-color: #c3e6cb; }
        .run-history-3, .run-history-5 { background-color: #f5c6cb; }

        .run-history .card-status-0 { background-color: #e6c300; } /* Mørkere gul */
        .run-history .card-status-1 { background-color: #a3c292; } /* Mørkere grøn */
        .run-history .card-status-3, .run-history .card-status-5 { background-color: #c9302c; } /* Mørkere rød */
        .run-history .run-history-0 { background-color: #d7b600; } /* Mørkere gul */
        .run-history .run-history-1 { background-color: #93b17a; } /* Mørkere grøn */
        .run-history .run-history-3, .run-history .run-history-5 { background-color: #e09292; } /* Mørkere rød */



        .last-update {
            text-align: right;
        }

        .next-run.overdue {
            color: #dc3545; /* Bootstrap danger color for red */
        }

        .jobhistory { display: inline-block; width: 15%; height: 20px; margin-bottom: -5px; border-radius: 10px; border: 1px solid white; margin-right: 4px;  }

    </style>
</head>
<body>
<div class=" mt-3" style="margin: 1vw;">
    <div class="row mb-2">
        <div class="col-md-8">
            <h4>Cronjob Dashboard</h4>
        </div>
        <div class="col-md-4 last-update">
            <span id="lastUpdate">Sidst opdateret: <span id="timesinceupdate">X</span> sekunder siden</span>
        </div>
    </div>
    <div class="row" id="dashboardcardrow">

    </div>
</div>

<div id="cronJobTemplate" class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2 d-none">
    <div class="card card-status-template">
        <div class="card-body">
            <h6 class="card-title mb-1 job-name">Jobnavn</h6>
            <p class="card-text mb-1 small last-run">Kørt: <span class="last-run-time"></span></p>
            <p class="card-text mb-1 small run-message"></p>
            <p class="card-text mb-1 small">Gns. køretid: <span class="average-runtime"></span> sekunder</p>
            <p class="card-text mb-1 small">Gns. interval: <span class="average-interval"></span></p>
            <p class="card-text mb-1 small next-run" id="nextRun-template">Næste kørsel: <span class="next-run-time"></span></p>
            <div class="mb-1 small run-history"></div>
        </div>
    </div>
</div>

<script src="<?php echo $assetPath; ?>/assets/dashboard.js"></script>

<script>

    var lastUpdate = null;

    function updateTimeSinceUpdate() {

        if(lastUpdate == null) {
            $('#timesinceupdate').text('X');
        } else {
            $('#timesinceupdate').text(Math.floor((new Date() - lastUpdate) / 1000));
        }
        setTimeout(updateTimeSinceUpdate, 250);
    }

    function loadCronJobData() {

        var url = "<?php echo $servicePath; ?>loadjobs";

        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function(data) {

                lastUpdate = new Date();

                // Tøm den eksisterende række for tidligere indhold
                $('#dashboardcardrow').empty();

                // Iterer over hvert job i den modtagne data
                $.each(data, function(index, job) {
                    // Klon skabelonen
                    var template = $('#cronJobTemplate').clone();

                    // Fjern id og skjul-klassen
                    template.removeAttr('id').removeClass('d-none');

                    // Sæt statusklassen baseret på seneste kørsel
                    var statusClass = 'card-status-' + job.latestStatus;
                    template.find('.card').addClass(statusClass);

                    // Opdater data
                    template.find('.job-name').text(job.jobname);

                    var lastRun = new Date(job.joblist[0].created);

                    var minutes = Math.floor((new Date() - lastRun) / 60000);
                    if(minutes < 1) {
                        template.find('.last-run-time').text('under 1 minut siden');
                    } else if(minutes < 60) {
                        template.find('.last-run-time').text(minutes + ' minutter siden');
                    } else {
                        template.find('.last-run-time').text(Math.floor(minutes / 60) + ' timer siden');
                    }
                    template.find('.last-run-time').text();

                    // Find message
                    var message = "";
                    if (job.joblist[0].error != null && job.joblist[0].error != "") {
                        message = job.joblist[0].error;
                    } else {
                        message = job.joblist[0].message;
                    }

                    template.find('.run-message').text(message);

                    template.find('.average-runtime').text(Math.floor(job.averageRuntime/1000));

                    if(job.averageInterval > 60) {
                        job.averageInterval = job.averageInterval / 60;
                        template.find('.average-interval').text(Math.floor(job.averageInterval)+' timer');
                    } else {
                        template.find('.average-interval').text(Math.floor(job.averageInterval)+' minutter');
                    }

                    template.find('.next-run-time').text(job.nextRun);

                    // Add all joblist to the run-history
                    $.each(job.joblist, function(index, joblist) {
                        template.find('.run-history').append('<div class="jobhistory card-status-' + joblist.status + '" title="' + joblist.created + ': ' + joblist.message + '"></div>');
                    });

                    // Tjek om næste kørsel er overskredet og sæt klassen 'overdue' hvis sandt
                    if (new Date(job.nextRun) < new Date()) {
                        template.find('.next-run').addClass('overdue');
                    }

                    // Tilføj til DOM
                    $('#dashboardcardrow').append(template);
                });
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('Error loading cron job data:', textStatus, errorThrown);
            }
        });

        setTimeout(loadCronJobData, 60000);
    }



    $(document).ready(function() {

        loadCronJobData()
        updateTimeSinceUpdate();
    });



</script>

</body>
</html>
