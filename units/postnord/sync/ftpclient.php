<?php

namespace GFUnit\postnord\sync;

class FTPClient
{

    private $tesMode;


    public function __construct($testMode)
    {
        $this->tesMode = $testMode;
    }

    public function getDownloadedFiles()
    {
        // TODO - IMPLEMENT
        return array();
    }


}

