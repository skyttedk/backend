<?php

namespace GFBiz\valgshop;

abstract class OrderExporter
{

    protected $headers = array();
    protected $lines = array();
    protected $notes = array();
    protected $errors = array();
    protected $warnings = array();



    public function __construct()
    {

    }


    public function addHeader($name,$value,$description)
    {
        $this->headers[] = array(
            "name" => $name,
            "value" => $value,
            "description" => $description
        );
    }
    
    public function addLine($type,$code,$metadesc="",$description=null,$quantity=null,$price=null,$billToCustomerNo=null,$discount_pct=0,$decimal_factory=1.00,$giftCode=null,$displayPrice=null)
    {
        $this->lines[] = array(
            "bill_to_customer_no" => $billToCustomerNo,
            "type" => $type,
            "code" => $code,
            "description" => $description,
            "quantity" => $quantity,
            "price" => $price,
            "discount_pct" => $discount_pct,
            "decimal_factory" => $decimal_factory,
            "metadesc" => $metadesc,
            "gift_code" => $giftCode,
            "display_price" => $displayPrice
        );
    }

    public function getTotalAmount($debitorNo=null) {

        $amount = 0;
        foreach($this->lines as $line) {
            if($debitorNo === null || $debitorNo == $line["bill_to_customer_no"]) {
                $amount += $line["quantity"] * $line["price"] * (1 - $line["discount_pct"] / 100);
            }
        }
        return $amount;

    }

    public function addNote($note)
    {
        if(is_array($note)) {
            foreach($note as $line) {
                if(is_string($line)) {
                    $lines = $this->splitTextToLines($line, 250);
                    foreach($lines as $noteLine) $this->notes[] = $noteLine;
                }
            }
        } else if(is_string($note) && tri($note) != "") {
            $lines = $this->splitTextToLines($note, 250);
            foreach($lines as $noteLine) $this->notes[] = $noteLine;
        }
    }


    public function getErrors() {
        return $this->errors;    
    }
    
    public function countErrors() {
        return count($this->errors);
    }
    
    public function countWarnings() {
        return count($this->warnings);
    }

    public function getWarnings() {
        return $this->warnings;
    }
    
    public function addError($errorMessage,$field="")
    {
        $this->errors[] = array(
            "message" => $errorMessage,
            "field" => $field
        );
    }

    public function addWarning($warningMessage,$field) {
        $this->warnings[] = array(
            "message" => $warningMessage,
            "field" => $field
        );
    }
    
    private function splitTextToLines($text,$maxLength) {

        $lines = explode("\n",trimgf($text));
        $noteLines = array();

        if(is_array($lines) && countgf($lines) > 0) {
            foreach($lines as $note) {
                $note = trimgf($note);
                if($note != "") {
                    if(strlen($note) > $maxLength) {
                        $wrappedLines = explode("\n",wordwrap($note,$maxLength,"\n",true));
                        foreach($wrappedLines as $nl) $noteLines[] = $nl;
                    } else {
                        $noteLines[] = $note;
                    }
                }
            }
        }
        return $noteLines;

    }

    public abstract function export();

}