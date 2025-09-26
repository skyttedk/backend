<?php

namespace GFUnit\vlager\utils;
use GFBiz\units\UnitController;

class Template
{



    public static function templateTop()
    {
        ?><!DOCTYPE html>
<html lang="da">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GF VLAGER</title>
    <link href="<?php echo \GFConfig::BACKEND_URL; ?>units/vlager/assets/bootstrap.min.css" rel="stylesheet">
    <script src="<?php echo \GFConfig::BACKEND_URL; ?>units/vlager/assets/jquery.min.js"></script>
    <script src="<?php echo \GFConfig::BACKEND_URL; ?>units/vlager/assets/popper.min.js"></script>
    <script src="<?php echo \GFConfig::BACKEND_URL; ?>units/vlager/assets/bootstrap.min.js"></script>
    <link rel="stylesheet" href="<?php echo \GFConfig::BACKEND_URL; ?>units/vlager/assets/fontawesome.css">
    <style>
        .card { margin-bottom: 15px; }
    </style>
    <script>
        var BASEURL = '<?php echo \GFConfig::BACKEND_URL; ?>index.php?rt=unit/vlager/';
    </script>
</head>
<body><?php
    }

    public static function templateBottom()
    {

        ?></body>
</html><?php


    }

    public static function outputFrontendHeader($url,$vlager) {
        ?><nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <a class="navbar-brand" href="#">Gavefabrikken - VLager - <?php echo $vlager->name; ?></a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $url."&action=logout"; ?>" >Log ud</a>
                </li>
            </ul>
        </div>
        </nav><br><br><?php
    }

    public static function outputError($errorHeadline,$errorMessage)
    {

        self::templateTop();

        // output bootstrap error page
        ?>
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <h1><?php echo $errorHeadline; ?></h1>
                    <p><?php echo $errorMessage; ?></p>
                </div>
            </div>
        <?php

        self::templateBottom();

    }

}