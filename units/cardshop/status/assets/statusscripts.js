/**
 * LANGUAGE BUTTONS
 */

$(document).ready(function() {
    $('.country-flag').click(function() {
        // Toggle valgt status
        $(this).toggleClass('country-flag-selected');

        // Hent alle valgte sprog
        var selectedLangs = [];
        $('.country-flag-selected').each(function() {
            selectedLangs.push($(this).attr('data'));
        });

        // Byg lang parameter
        var langParam = '';
        if (selectedLangs.length > 0) {
            langParam = selectedLangs.join('-');
        }

        // Bevar eksisterende parametre
        var currentUrl = window.location.href;
        var baseUrl = currentUrl.split('?')[0];
        var queryParams = "";

        if (currentUrl.indexOf('?') !== -1) {
            // Hent eksisterende parametre
            var params = currentUrl.split('?')[1].split('&');
            var newParams = [];

            // Gennemgå alle parametre og erstat eller fjern lang
            var langFound = false;

            for (var i = 0; i < params.length; i++) {
                if (params[i].indexOf('lang=') === 0) {
                    langFound = true;
                    if (langParam) {
                        newParams.push('lang=' + langParam);
                    }
                } else {
                    newParams.push(params[i]);
                }
            }

            // Tilføj lang parameter hvis den ikke eksisterede
            if (!langFound && langParam) {
                newParams.push('lang=' + langParam);
            }

            queryParams = newParams.join('&');
        } else if (langParam) {
            // Ingen eksisterende parametre, tilføj lang
            queryParams = 'lang=' + langParam;
        }

        // Byg den fulde URL
        var newUrl = baseUrl + (queryParams ? '?' + queryParams : '');

        // Reload siden med den nye URL
        window.location.href = newUrl;
    });
});