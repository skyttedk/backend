<?php

namespace GFUnit\lister\rapporter;



class Dashboard
{


    public function __construct()
    {
        ?><html>
    <head>
        <title>GF Rapporter</title>
        <style>

            body { font-family: verdana; font-size: 14px; background: #F8F8F8; }
            .reportparam {padding-bottom: 10px; }

        </style>
        <script src="/gavefabrikken_backend/views/lib/jquery.min.js"></script>
    </head>
    <body><?php
        echo "<h2>Rapport oversigt</h2><br>";

        $rf = new ReportFactory();
        $types = $rf->getAvailableReportTypes();
        

        ?><form method="post" action="index.php?rt=unit/lister/rapporter/dashboard">
        <table>
            <tr>
                <td valign="top" style="width: 33%; padding: 15px;"><?php

                    echo "<div><b>Tilgængelige rapporter</b></div>";
                    echo "<div style='background: #FCFCFC; width: 100%; border: 1px solid #aaa; height: 85vh; overflow-x: hidden; overflow-y: auto;'>";
                    foreach ($types as $type) {
                        $report = $rf->createReport($type);
                        echo "<div style='padding: 10px; background: white; border-bottom: 1px solid #aaa;'><label><input name='reporttype' type='radio' value='".$type."' data-params='".json_encode($report->defineParameters())."'> <b>".$report->getReportName()."</b></label><div style='font-size: 0.8em; padding: 5px; padding-bottom:0px;'>".$report->getReportDescription()."</div></div>";
                    }
                    echo "</div>";

                ?></td><td valign="top" style="width: 33%; padding: 15px;"><?php

                    echo "<div style='padding-left: 10px;'><b>Kriterier</b></div>";

                    ?><div style="padding: 10px;">

                        <?php
                            ParameterInputs::CSShopSelect();
                        ParameterInputs::CSExpireDateSelect();
                        ?>

                    </div><?php


                ?></td><td valign="top" style="width: 33%; padding: 15px;">

                    <button type="submit" style="padding: 10px; width: 100%;">Hent rapport</button>
                    <input type="hidden" name="action" value="export">

                </td>
            </tr>
        </table></form>

    <script>

        $(document).ready(function() {
            $('.reportparam').hide();

            $('input[name=reporttype]').bind('change', function() {
                $('.reportparam').hide();
                var params = JSON.parse($(this).attr('data-params'));
                for (var i = 0; i < params.length; i++) {
                    $('.reportparam'+params[i]).show();
                }
            });

        });
    </script>

    </body></html><?php



    }

}
