var helper = {
    test : function(){
        alert("testtet")
    },
    setValgshopPanelHeight : function(){
           /*
                var activeTab = $("#shopTabs .ui-tabs-panel:visible").attr("id");
                var windowHeight = $( window ).height()
                $("#"+activeTab).height(windowHeight - 200);
             */


    }
}
function loadScriptOnce(url, callback) {
    // Tilføj cache-busting parameter med tilfældigt tal
    var cacheBustingUrl = url + (url.indexOf('?') >= 0 ? '&' : '?') + 'v=' + Math.random();

    if (document.querySelector('script[src^="' + url + '"]')) {
        if (callback) callback();
        return;
    }

    var script = document.createElement('script');
    script.type = 'text/javascript';
    script.src = cacheBustingUrl;
    script.onload = function () {
        if (callback) callback();
    };
    document.head.appendChild(script);
}