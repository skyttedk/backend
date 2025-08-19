

<div class="dbcalc"></div>
<script>
function initDbCalc()
{
    // Hent shop ID fra URL
    var urlParams = new URLSearchParams(window.location.search);
    var shopId = urlParams.get('editShopID');

    if (shopId) {
        // Byg iframe URL
        var iframeUrl = 'https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/valgshop/contributionmargin/view&shopID=' + shopId + '&localisation=1';

        // Indsæt iframe i .dbcalc med jQuery
        $('.dbcalc').html('<iframe src="' + iframeUrl + '" width="100%" height="600" frameborder="0"></iframe>');
    }
}
</script>



