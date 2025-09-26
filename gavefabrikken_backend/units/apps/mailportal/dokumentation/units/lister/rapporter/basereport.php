<?php

namespace GFUnit\lister\rapporter;

abstract class BaseReport
{
    protected $parameters;
    protected $formats;

    public function __construct()
    {
   
    }

    abstract public function getReportName();
    abstract public function getReportCode();
    abstract public function getReportDescription();
    
    abstract public function defineParameters();
    
    abstract public function generateReport($parameters);
    
}