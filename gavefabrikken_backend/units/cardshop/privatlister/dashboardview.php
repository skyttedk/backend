<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { padding: 0px; font-size: 12px; font-family: verdana; }
        td { font-size: 0.8em; padding: 5px; }
    </style>
    <script src="/gavefabrikken_backend/views/lib/jquery.min.js"></script>
    <script src="/gavefabrikken_backend/views/lib/jquery-ui/jquery-ui.js"></script>
    <link href="/gavefabrikken_backend/views/lib/jquery-ui/jquery-ui.css" rel="stylesheet">
    <style>
        td { padding: 0px; overflow: hidden; margin: 0px; }
        .pdcathead { padding: 15px; background: #457b9d; font-size: 1.2em; font-weight: bold; color: white; }
        .pdmenuitem { padding: 10px; padding-left: 15px; background: #a8dadc; border-bottom: 1px solid #555555; cursor: pointer; }
        .pdcatcount { padding: 10px; padding-left: 15px; background: #a8dadc; border-bottom: 1px solid #555555; }
        .pdcatlabel { float: right; background: #e63946; color: white; padding: 4px; margin-top: -4px; border-radius: 4px; padding-left: 8px; padding-right: 8px;}
        .pdlistheader {  background: #1d3557; color: white; padding: 15px;}
        .pdlistheader span { font-weight: bold; font-size: 1.2em; }
        .pddata { border-collapse: collapse;}
        .pddata td { background: #f1faee; padding: 5px; font-size: 13px; color: #333333; border-bottom: 1px solid #333333; text-align: left; }
        .pddata tr.pdheader th { cursor: pointer; background: #457b9d; padding: 5px; font-size: 14px; color: white; text-align: left; }
        .pdmenuitemselected { background: #1d3557; color: white; font-weight: bold;}
    </style>
</head>
<body style="padding: 0px; margin: 0px;">

<table style="width: 100%; height: 100%; margin: 0px; border-collapse: collapse;">
    <tr>
        <td style="width: 250px; border-right: 1px solid black; background: #C0C0C0" valign="top">

            <div style="text-align: center; padding: 20px;">
                Shop / land: <select id="langshopselect">
                    <?php echo $model->getShopLangOptions(); ?>
                </select>
            </div>
            <div id="pdcategorymenu">

            </div>
        </td>
        <td valign="top">


            <div class="pdlistcontent">

                <div style="padding: 100px; text-align: center; font-size: 26px;">Vælg en kategori for at se data!</div>

            </div>
        </td>
    </tr>


</table>

<script>

    function updateLangShopSelect() {
        $('#pdcategorymenu').html('<div style="text-align: center; padding: 30px;">Henter kategorier</div>');
        $.post('<?php echo $servicePath; ?>menudiv',{category: $('#langshopselect').val()},function(response) {
            $('#pdcategorymenu').html(response);
            selectState(lastState);
        });
    }

    var lastState = null;

    function selectState(state) {
        console.log('select state '+state)
        if(state == null) return;
        lastState = state;

        $('.pdmenuitemselected').removeClass('pdmenuitemselected');
        $('.pdmenuitemstate'+state).addClass('pdmenuitemselected');

        $('.pdlistcontent').html('<div style="text-align: center; padding: 30px;">Henter indhold</div>');
        $.post('<?php echo $servicePath; ?>pdlist',{state: state, category: $('#langshopselect').val()},function(response) {
            $('.pdlistcontent').html(response);
            initTableSorter();
        });
    }

    $(document).ready(function() {
        updateLangShopSelect();
        $('#langshopselect').bind('change',updateLangShopSelect);
    })


    function comparer(index) {
        return function(a, b) {
            var valA = getCellValue(a, index), valB = getCellValue(b, index)
            return $.isNumeric(valA) && $.isNumeric(valB) ? valA - valB : valA.toString().localeCompare(valB)
        }
    }
    function getCellValue(row, index){ return $(row).children('td').eq(index).text() }

    function initTableSorter()
    {
        $('th').click(function(){
            var table = $(this).parents('table').eq(0)
            var rows = table.find('tr:gt(0)').toArray().sort(comparer($(this).index()))
            this.asc = !this.asc
            if (!this.asc){rows = rows.reverse()}
            for (var i = 0; i < rows.length; i++){table.append(rows[i])}
        })
    }

    function startExport(p1,p2,p3) {
        document.location = '<?php echo $servicePath; ?>pdexport/'+p1+"/"+p2+"/"+p3;
    }

</script>

</body>
</html>