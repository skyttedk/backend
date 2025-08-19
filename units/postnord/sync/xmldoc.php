<?php

namespace GFUnit\postnord\sync;

abstract class XMLDoc
{


    protected static function xmlString($val,$maxLength = null) {
        if($maxLength != null && $maxLength > 0) {
            $val = substr($val,0,$maxLength);
        }
        return htmlspecialchars($val);
    }

    protected static function verifyXML($xml,$xsdFile=null)
    {

        // Check xsd file
        if($xsdFile != null && trimgf($xsdFile) != "") {
            if(!file_exists($xsdFile)) {
                throw new \Exception("Could not find xsd file to verify xml: ".$xsdFile);
            }
        }

        // Load xml document
        libxml_use_internal_errors(true);

        $domDocument= new \DOMDocument();
        $domLoaded = $domDocument->loadXML($xml);
        
        // CHeck dom
        if($domLoaded == false) {
            //throw new \Exception("Failed to verify xml document");
            $errors = libxml_get_errors();
            $errorStrings = array();

            foreach($errors as $error) {
                $errorStrings[] = self::xmlErrorString($error,$xml);
            }

            libxml_clear_errors();
            throw new \Exception("Failed to verify xml document: ".implode("<br>",$errorStrings));
        }

        // Validate against xsd
        if(trimgf($xsdFile) != "") {
            if(!$domDocument->schemaValidate($xsdFile)) {

                $errors = libxml_get_errors();
                $errorStrings = array();

                foreach($errors as $error) {
                    $errorStrings[] = self::xmlErrorString($error,$xml);
                }

                libxml_clear_errors();
                throw new \Exception("XSD Validation errors: ".implode("<br>",$errorStrings));

            }
        }

    }


    private static function xmlErrorString($error, $xml)
    {
        $return  = $xml[$error->line - 1] . "\n";
        $return .= str_repeat('-', $error->column) . "^\n";

        switch ($error->level) {
            case LIBXML_ERR_WARNING:
                $return .= "Warning $error->code: ";
                break;
            case LIBXML_ERR_ERROR:
                $return .= "Error $error->code: ";
                break;
            case LIBXML_ERR_FATAL:
                $return .= "Fatal Error $error->code: ";
                break;
        }

        $return .= trimgf($error->message) .
            "\n  Line: $error->line" .
            "\n  Column: $error->column";

        return "$return\n\n--------------------------------------------\n\n";
    }

}