<!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sidepanel Navigation</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            display: flex;
            font-size: 10px;
        }
        #sidebar {
            width: 150px;
        }
        #myPageContent {
            flex: 1;
            padding: 5px;

        }
    </style>
</head>
<body>
<div id="sidebar" class="bg-light border-right">
    <div class="list-group">
        <button id="nav2" class="list-group-item list-group-item-action">Nyoprettede Gaver PIM</button>
        <button id="nav1" class="list-group-item list-group-item-action">Shop status stats</button>
        <button id="nav3" class="list-group-item list-group-item-action">Shop Åben/luk/levering booking overblik</button>
        <button id="nav4" class="list-group-item list-group-item-action">Magento</button>
        <button id="nav5" class="list-group-item list-group-item-action">Magento VIP</button>
        <button id="nav6" class="list-group-item list-group-item-action">Sælgerprofiler</button>
    </div>
</div>
<div id="myPageContent">

</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<script>
    
    $(document).ready(function() {
        $('.list-group-item').on('click', function() {
            let target = '#content' + this.id.slice(-1);
            let iframe = "";
            if(target == '#content1'){
                iframe = '<iframe  src="https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/apps/mypage/statusStats" style=" overflow: hidden; height: calc(100vh - 20px); width: calc(100vw - 100px);"></iframe>';
            }
            if(target == '#content2'){
                iframe = '<iframe  src="https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/apps/mypage/showThisWeekCreatePimItems" style=" overflow: hidden; height: calc(100vh - 20px); width: calc(100vw - 150px);"></iframe>';
            }
            if(target == '#content3'){
                iframe = '<iframe  src="https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/apps/shopreservation/overview" style=" overflow: hidden; height: calc(100vh - 20px); width: calc(100vw - 200px);"></iframe>';
            }
            if(target == '#content4'){
                iframe = '<iframe  src="https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/apps/mass_sync_magento" style=" overflow: hidden; height: calc(100vh - 20px); width: calc(100vw - 200px);"></iframe>';
            }
            if(target == '#content5'){
                iframe = '<iframe  src="https://system.gavefabrikken.dk//gavefabrikken_backend/index.php?rt=MagentoOrderStock" style=" overflow: hidden; height: calc(100vh - 20px); width: calc(100vw - 200px);"></iframe>';
            }
            if(target == '#content6'){
                iframe = '<iframe  src="https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/apps/sale_profile" style=" overflow: hidden; height: calc(100vh - 20px); width: calc(100vw - 200px);"></iframe>';
            }
            $('.content-section').addClass('d-none');
            $(target).removeClass('d-none');
            $("#myPageContent").html(iframe);


        });
    });
</script>
</body>
</html>
