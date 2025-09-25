<?php

namespace GFUnit\vlager\utils;


use GFCommon\Model\Navision\SalesHeaderWS;
use GFCommon\Model\Navision\SalesLineWS;
use GFCommon\Model\Navision\SalesShipmentHeadersWS;
use GFCommon\Model\Navision\SalesShipmentLinesWS;

class Navision
{

    private $order;
    private $orderlines;

    public function __construct()
    {

    }

    public function getOrder()
    {
        return $this->order;
    }

    public function getOrderLines() {
        return $this->orderlines;
    }
    
    public function loadSONo($soNo,$languageId=1)
    {
        // Load salesorder
        $client = new SalesHeaderWS($languageId);
        $this->order = $client->getHeader("ORDER", $soNo);

        // Check salesorder
        if ($this->order == null) {

            // No order found, look for invoice
            $invoicClient = new SalesShipmentHeadersWS($languageId);
            $invoiceList = $invoicClient->getByOrderNo($soNo);
            $invoice = $invoiceList[0] ?? null;
            $this->order = $invoice;

            // No invoice found, throw exception
            if ($invoice == null) {
                throw new \Exception("No salesorder or invoice found on " . $soNo);
            }
            // Invoice found, process lines
            else {

                $client = new SalesShipmentLinesWS();
                $lines = $client->getLines($invoice->getNo());
                if ($lines == null || count($lines) == 0) {
                    throw new \Exception("No lines found on invoice " . $invoice->getNo());
                }

            }

        }
        // Order found, process lines
        else
        {
            $client = new SalesLineWS($languageId);
            $lines = $client->getLines($soNo);
            if ($lines == null || count($lines) == 0) {
                throw new \Exception("No lines found on order " . $soNo);
            }
        }

        if($this->order == null) {
            throw new \Exception("No order found on " . $soNo);
        }

        // Set order lines
        $this->orderlines = $lines;

    }


}