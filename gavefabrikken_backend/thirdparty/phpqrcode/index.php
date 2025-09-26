<?php

    include "../../includes/config.php";

    include "qrlib.php";
    $errorCorrectionLevel = 'L';
    if (isset($_REQUEST['level']) && in_array($_REQUEST['level'], array('L','M','Q','H')))
        $errorCorrectionLevel = $_REQUEST['level'];

    $matrixPointSize = 8;
    if (isset($_REQUEST['size']))
        $matrixPointSize = min(max((int)$_REQUEST['size'], 1), 10);

    $codeString = "https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=order/register&orderno=".$_REQUEST["value"];
    QRcode::png($codeString,false, $errorCorrectionLevel, $matrixPointSize, 2);

?>
