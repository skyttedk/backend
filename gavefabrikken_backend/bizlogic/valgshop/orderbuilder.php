<?php

namespace GFBiz\valgshop;

use GFCommon\Model\Navision\CustomerWS;

class OrderBuilder
{

    /***
     * @var ShopOrderModel
     */
    private $order;

    private $debitor;

    public function __construct(ShopOrderModel $order)
    {
        $this->order = $order;
    }

    public static function buildOrderXML(ShopOrderModel $order) {
        $builder = new OrderBuilder($order);
        return $builder->buildOrder("xml");
    }

    public static function buildOrderHtml(ShopOrderModel $order) {
        $builder = new OrderBuilder($order);
        return $builder->buildOrder("html");
    }

    /**
     * @param $format
     * @return OrderExporter
     */
    public function buildOrder($format)
    {

        $exporter = $this->getExporter($format);
        
        // Construct header
        $this->buildOrderHeader($exporter);
        
        // Construct lines
        $this->buildOrderLines($exporter);
        
        // Construct notes
        $this->buildOrderNotes($exporter);

        return $exporter;

    }
    
    private function buildOrderHeader(OrderExporter $exporter)
    {

        // API KEY
        // Does not implement for now

        if($this->order->getLanguageID() == 0) {
            $exporter->addError("Order does not have a language set.");
        }

        // Orderno
        if($this->order->getVSNumber() === null) {
            $exporter->addError("Order does not have a order number yet!");
        } else if(trimgf($this->order->getVSNumber()) == "") {
            $exporter->addError("Order does not have a order number yet!");
        } else {
            $exporter->addHeader("orderno",$this->order->getVSNumber(),"Ordre nr.");
        }

        // Version
        $nextVersion = $this->order->getNextVersion();
        if($nextVersion <= 0) {
            $exporter->addError("Order does not have a valid version number.");
        } else {
            $exporter->addHeader("version",$nextVersion,"Ordre version");
        }

        // Customerno
        if($this->order->getNavDebitorNo() <= 0) {
            $exporter->addError("Order does not have a valid customer number.");
        } else {

            try {
                $client = new CustomerWS($this->order->getLanguageID());
                $this->debitor = $client->getByCustomerNo($this->order->getNavDebitorNo());
                if($this->debitor == null) {
                    $exporter->addError("Customer number is not valid: " . $this->order->getNavDebitorNo());
                }

            } catch (\Exception $e) {
                $exporter->addError("Customer number is not valid: " . $e->getMessage());
            }

            $exporter->addHeader("customerno",$this->order->getNavDebitorNo(),"Kundenummer");

        }

        // Reference
        if($this->order->getRequisitionNo() != "") {
            $exporter->addHeader("reference", $this->order->getRequisitionNo(), "Kundens reference");    
        }
        
        // Salesperson
        if($this->order->getSalesperson() == "") {
            $exporter->addError("No salesperson on order.");
        } else {
            $exporter->addHeader("salesperson",$this->order->getSalesperson(),"Sælger");
        }


        // Shopid - hardcoded
        $exporter->addHeader("shopid",$this->order->getShopID(),null);
        
        // Shopname - TK har bedt om at den bliver fjernet
        /*if(trimgf($this->order->getShopName()) == "") {
            $exporter->addError("Shop does not have a name");
        } else {
            $exporter->addHeader("shopname",$this->order->getShopName(),"Shop navn");
        }*/

        // Privatedelivery
        /* - Should not be delivered
        if($this->order->isPrivateDelivery()) {
            $exporter->addHeader("privatedelivery",true,"Privatlevering");
        } else {
            $exporter->addHeader("privatedelivery",false,"Privatlevering");
        }
        */

        // Prepayment
        if($this->order->usePrepayment()) {

            $minShopPrice = $this->order->getShopBudget();

            if($this->order->isFlexBudget()) {
                $modelList = $this->order->getPresentModelsData();
                foreach($modelList as $model) {
                    $price = $model["specialPrice"] == null ? $this->order->getShopBudget() : $model["specialPrice"];
                    if($price < $minShopPrice || $minShopPrice == 0) {
                        $minShopPrice = $price;
                    }
                }
            }

            $totalPrice = ($this->order->getPresentCount()*$minShopPrice)/100;
            if($totalPrice > 25000) {
                $exporter->addHeader("prepayment",true,"Forudbetaling");
            } else {
                $exporter->addHeader("prepayment",false,"Forudbetaling ikke aktiveret (total under 25000)");
            }

            if($minShopPrice == 0) {
                $exporter->addError("Shop budget is 0.");
            }

        } else {
            $exporter->addHeader("prepayment",false,"Forudbetaling ikke aktiveret");
        }


        
        // Prepayment_document_reissue
        /*
        if($this->order->usePrepaymentReissue() || $nextVersion == 1) {
            $exporter->addHeader("prepayment_document_reissue",1, "Forudbetaling dokument sendes (0: nej, 1: ja)");
        } else {
            $exporter->addHeader("prepayment_document_reissue",0, "Forudbetaling dokument sendes (0: nej, 1: ja)");
        }
        */
        
        // Prepayment_postering_date
        if($this->order->hasPrepaymentPostingDate()) {
            $exporter->addHeader("prepayment_posting_date", $this->order->getPrepaymentPostingDateString(), "Bogføringsdato på forudfakturering");
            if(!$this->order->usePrepayment()) {
                $exporter->addError("Prepayment posting date is set, but prepayment is not used.");
            }
        }

        // Prepayment_due-date
        if($this->order->hasPrepaymentDueDate()) {
            $exporter->addHeader("prepayment_due_date", $this->order->getPrepaymentDueDateString(), "Særlig betalingsfrist på forudfakturering");
            if(!$this->order->usePrepayment()) {
                $exporter->addError("Prepayment due date is set, but prepayment is not used.");
            }
        }

        // Payment terms code
        // Not supported, use default on customer

        // onhold
        // Not supported


        // Rollback
        if($this->order->isCancelled()) {
            $exporter->addHeader("rollback",true,"Krediter / luk ordre");
        } else {
            $exporter->addHeader("rollback",false,"Krediter / luk ordre");
        }

        // Supressconfirmation
        if($this->order->useSuppressOrderConfirmation() && $this->order->getNextVersion() > 1) {
            $exporter->addHeader("suppressconfirmation",false,"Stop ordrebekræftelse sendes til kunde");
        } else {
            // ALWAYS SUPRESS ORDER CONFIRMATION!!
            $exporter->addHeader("suppressconfirmation",false,"Stop ordrebekræftelse sendes til kunde");
            //$exporter->addHeader("suppressconfirmation",false,"Stop ordrebekræftelse");
        }

        if($this->order->isFinalInvoice()) {
            $exporter->addHeader("suppressprepaymentcheck",true,"Tjek ikke forudbetaling");
        }

        // Add e-mail to send order confirmation to
        $exporter->addHeader("confirmation_email","sc@interactive.dk","E-mail til ordrebekræftelse");

        // Delay prepayment
        if($this->order->hasPrepaymentPostingDate()) {
            $exporter->addHeader("delay_prepayment", $this->order->getPrepaymentPostingDateString(), "Udskyd forudbetaling til");
            if(!$this->order->usePrepayment()) {
                $exporter->addError("Prepayment posting date is set, but prepayment is not used.");
            }
        }
    }
    
    private function buildOrderLines(OrderExporter $exporter) {

        //addLine($type,$code,$metadesc="",$description=null,$quantity=null,$price=null,$billToCustomerNo=null,$discount_pct=0,$decimal_factory=1.00)


        
       
        
        /***** FINAL INVOICE ******/

        if($this->order->isFinalInvoice()) {
            
            // Create valgshopfordeling
            $fordelingModel = new ValgshopFordeling($this->order->getShopID());
            $fordelingWarnings = $fordelingModel->getWarnings();
            if(countgf($fordelingWarnings) > 0) {
                foreach($fordelingWarnings as $warning) {
                    $exporter->addError("Fordelingsliste problem: " . $warning);
                }
            }

            $shopPresentSum = 0;


            // For each invoice
            foreach($this->order->getShopInvoices() as $shopInvoice) {

                if($shopInvoice->isPrimaryCustomer()) {
                    $invoiceDebitor = $this->debitor;
                } else {
                    $invoiceDebitorNo = $shopInvoice->getBillToCustomer();
                    try {
                        $client = new CustomerWS($this->order->getLanguageID());
                        $invoiceDebitor = $client->getByCustomerNo($invoiceDebitorNo);
                        if($invoiceDebitor == null) {
                            $exporter->addError("Customer number is not valid: " . $this->order->getNavDebitorNo());
                        }

                    } catch (\Exception $e) {
                        $exporter->addError("Customer number is not valid: " . $e->getMessage());
                    }

                }

                $presentCount = $shopInvoice->getPresentCount();

                // Valgshop - not added to invoice lines
                if($shopInvoice->isPrimaryCustomer()) {
                    $exporter->addLine("VALGSHOP",2,"Valgshop opsætning",null,1,$this->order->getValgshopFee());
                }

                // Giftwrap
                if($shopInvoice->useGiftwrap()) {
                    $exporter->addLine("GIFTWRAP",2,"Indpakning",null,$presentCount,$shopInvoice->getGiftwrapPrice(),$shopInvoice->getBillToCustomer());
                }

                // Label
                if($shopInvoice->useNametag()) {
                    $exporter->addLine("NAMELABEL",2,"Navnelabels",null,$presentCount,$shopInvoice->getNametagPrice(),$shopInvoice->getBillToCustomer());
                }

                // Julekort
                if($shopInvoice->usePaperCards()) {
                    $exporter->addLine($shopInvoice->getPaperCardLineCode(),2,"Julekort",null,$presentCount,$shopInvoice->getPaperCardPrice(),$shopInvoice->getBillToCustomer());
                }

                // DOT
                if($shopInvoice->useDOT()) {
                    $exporter->addLine("DOT",2,"DOT",null,$shopInvoice->getDOTAmount(),$shopInvoice->getDOTPrice(),$shopInvoice->getBillToCustomer());
                }
                
                // Opbæring
                if($shopInvoice->useCarryup()) {
                    $exporter->addLine("CARRYUP",2,"Opbæring",null,$shopInvoice->getCarryupAmount(),$shopInvoice->getCarryupPrice(),$shopInvoice->getBillToCustomer());
                }

                // Plant et træ
                if($shopInvoice->usePlantTree()) {
                    $exporter->addLine("PLANTTREE",2,"Plant et træ",null,1,$shopInvoice->getPlantTreePrice(),$shopInvoice->getBillToCustomer());
                }

                // GLS
                if($shopInvoice->usePrivateDelivery()) {
                    $exporter->addLine("HOMEDELIVERY",2,"Privatlevering",null,$presentCount,$shopInvoice->getPrivateDeliveryPrice(),$shopInvoice->getBillToCustomer());
                }

                // Frit leveret
                if($shopInvoice->useFreeDelivery()) {
                    $exporter->addLine("FREEDELIVERY",2,"Frit leveret",null,1,0,$shopInvoice->getBillToCustomer());
                }

                // PL1
                if($shopInvoice->useAgreedDelivery()) {
                    $exporter->addLine("FREIGHT",2,"Fragt",null,1,$shopInvoice->getAgreedDeliveryPrice(),$shopInvoice->getBillToCustomer());
                }


                // Total
                $exporter->addLine("TOTAL",0,"TOTAL",null,$presentCount,$shopInvoice->getShopBudget(),$shopInvoice->getBillToCustomer());

                $exporter->addLine("TEXT3",2,"SPACE","",null,null, $shopInvoice->getBillToCustomer());

                // Gavevalg
                $sumListe = $fordelingModel->getPresentDataForInvoiceID($shopInvoice->getInvoiceID());
                
                $autogaveUse = false;
                $autogaveCount = 0;
                $autogaveVarenr = "";
                
                if($this->order->useAutogave()) {
                    $autogaveVarenr = $this->order->getAutogaveItemNo();
                    $autogaveUse = true;
                    foreach($sumListe as $presentSum) {
                        if (trimgf($presentSum["varenr"]) == "") {
                            $autogaveCount += $presentSum["count"];  
                        }
                    }
                }
                
                $autogaveUsed = false;
                $sumTotal = 0;

                foreach($sumListe as $presentSum) {

                    if(trimgf($presentSum["varenr"]) != "") {
                        if($autogaveUse && !$autogaveUsed && strtolower(trimgf($presentSum["varenr"])) == strtolower(trimgf($autogaveVarenr))) {
                            $presentSum["count"] += $autogaveCount;
                            $autogaveUsed = true;
                            echo "added ".$autogaveCount." autogaver<br>";
                        }

                        $price = $this->order->getSpecialPriceByVarenr($presentSum["varenr"]);
                        
                        $exporter->addLine($presentSum["varenr"], 3, "Gavevalg ".$presentSum["alias"], $presentSum["description"], $presentSum["count"], $price == null ? $this->order->getShopBudget() : $price, $shopInvoice->getBillToCustomer());
                        $sumTotal += $presentSum["count"];
                        $shopPresentSum += $presentSum["count"];
                    }
                }
                
                if($sumTotal != $shopInvoice->getPresentCount()) {
                    $exporter->addError("Shop invoice " . $shopInvoice->getInvoiceID() . " has a present count of " . $shopInvoice->getPresentCount() . " but the sum of the present count is " . $sumTotal . ".");
                }

                $exporter->addLine("TEXT3",2,"SPACE","",null,null, $shopInvoice->getBillToCustomer());

                // Add invoice fee
                if($invoiceDebitor != null && $shopInvoice->useInvoiceFee()) {

                    if($invoiceDebitor->getExcemptFromInvoiceFee()) {
                        $exporter->addWarning("Navision debitor is marked as excempt from invoice fee", "INVOICEFEE");
                    }

                    $exporter->addLine("INVOICEFEE", 4, "Fakturagebyr", null, 1, $shopInvoice->getInvoiceFeePrice(), $shopInvoice->getBillToCustomer());
                }

                // Add envfee
                if($invoiceDebitor != null && $shopInvoice->useEnvironmentFee()) {

                    if($invoiceDebitor->getExcemptFromEnvFee()) {
                        $exporter->addWarning("Navision debitor is marked as excempt from environment fee", "ENVFEE");
                    }

                    $totalAmount = $exporter->getTotalAmount($shopInvoice->getBillToCustomer());
                    $exporter->addLine("ENVFEE", 4, "Miljøbidrag", null, 1, $totalAmount*($shopInvoice->getEnvironmentFeePercent()/100), $shopInvoice->getBillToCustomer());
                }

                // Rabat
                if($shopInvoice->useDiscount() && $shopInvoice->getDiscountPercentage() > 0) {
                    $totalAmount = $exporter->getTotalAmount($shopInvoice->getBillToCustomer());
                    $discountPercentage = $shopInvoice->getDiscountPercentage();
                    $discountAmount = intval($totalAmount * ($discountPercentage / 100));
                    $exporter->addLine("LINJERABAT",2,"Linjerabat (".$shopInvoice->getDiscountPercentage()."%)",null,1,-1*$discountAmount,$shopInvoice->getBillToCustomer());
                }
                
            }

            if($shopPresentSum != $this->order->getPresentCount()) {
                $exporter->addError("Shop order has a present count of " . $this->order->getPresentCount() . " but the sum of the present count is " . $shopPresentSum . ".");
            }

        }

        /***** ORDER ******/
        
        else {

            $presentCount = $this->order->getPresentCount();

            // Valgshop - not added to invoice lines
            $exporter->addLine("VALGSHOP",2,"Valgshop opsætning",null,1,$this->order->getValgshopFee());
            
            // Giftwrap
            if($this->order->useGiftwrap()) {
                $exporter->addLine("GIFTWRAP",2,"Indpakning",null,$presentCount,$this->order->getGiftwrapPrice());
            }

            // Label
            if($this->order->useNametag()) {
                $exporter->addLine("NAMELABEL",2,"Navnelabels",null,$presentCount,$this->order->getNametagPrice());
            }

            // Julekort
            if($this->order->usePaperCards()) {
                $exporter->addLine($this->order->getPaperCardLineCode(),2,"Julekort",null,$presentCount,$this->order->getPaperCardPrice());
            }

            // DOT
            if($this->order->useDOT()) {
                $exporter->addLine("DOT",2,"DOT",null,$this->order->getDOTAmount(),$this->order->getDOTPrice());
            }

            // Opbæring
            if($this->order->useCarryup()) {
                $exporter->addLine("CARRYUP",2,"Opbæring",null,$this->order->getCarryupAmount(),$this->order->getCarryupPrice());
            }

            // Plant et træ
            if($this->order->usePlantTree()) {
                $exporter->addLine("PLANTTREE",2,"Plant et træ",null,1,$this->order->getPlantTreePrice());
            }

            $exporter->addLine("TEXT3",2,"SPACE","",null,null);

            // Gaveres
            $modelList = $this->order->getPresentModelsData();
            $minShopPrice = $this->order->getShopBudget();
            foreach($modelList as $model) {

                //echo "GAVE: ".$model["fullalias"]." - ".$model["full_name"]." (".$model["present_id"].")<br>";
                
                $price = $model["specialPrice"] == null ? $this->order->getShopBudget() : $model["specialPrice"];
                if($price < $minShopPrice) {
                    $minShopPrice = $price;
                }

                if($this->order->useAutogave() && trim(strtolower($model["itemno"])) == trim(strtolower($this->order->getAutogaveItemNo()))) {
                    $exporter->addLine("TEXT3",2,"AUTOGAVE","Autogave:",null,null);
                }
                
                $exporter->addLine("TEXT3",2,"Gave ".strtoupper(trimgf($model["fullalias"])),$model["full_name"],1, $price,null,0,1.00,"Gave ".strtoupper(trimgf($model["fullalias"])),$price);
            }


            $exporter->addLine("TEXT3",2,"SPACE","",null,null);

            // GLS
            if($this->order->usePrivateDelivery()) {
                $exporter->addLine("HOMEDELIVERY",2,"Privatlevering",null,$presentCount,$this->order->getPrivateDeliveryPrice());
            }

            // Frit leveret
            if($this->order->useFreeDelivery()) {
                $exporter->addLine("FREEDELIVERY",2,"Frit leveret",null,1,0);
            }

            // PL1
            if($this->order->useAgreedDelivery()) {
                $exporter->addLine("FREIGHT",2,"Aftalt fragt",null,1,$this->order->getAgreedDeliveryPrice());
            }

            // Total
            $exporter->addLine("TOTAL",0,"TOTAL",null,$presentCount,$minShopPrice);

            // Rabat
            if($this->order->useDiscount() && $this->order->getDiscountPercentage() > 0) {
                $totalAmount = $exporter->getTotalAmount();
                $discountPercentage = $this->order->getDiscountPercentage();
                $discountAmount = intval($totalAmount * ($discountPercentage / 100));
                $exporter->addLine("LINJERABAT",2,"Linjerabat (".$this->order->getDiscountPercentage()."%)",null,1,-1*$discountAmount);
            }

            $exporter->addLine("TEXT3",2,"SPACE","",null,null);
            $exporter->addLine("TEXT3",2,"SPACE","",null,null);

            // Deadlines
            $deadlineList = $this->order->getSortedDeadlineList();
            foreach($deadlineList as $deadline) {
                $exporter->addLine("TEXT3",2,"DEADLINE TEXT",$deadline["text"],null,null);
            }

            // Add invoice fee
            if($this->debitor != null && $this->order->useInvoiceFee()) {
                if($this->debitor->getExcemptFromInvoiceFee()) {
                    $exporter->addWarning("Navision debitor is marked as excempt from invoice fee", "INVOICEFEE");
                }
            }

            // Add envfee
            if($this->debitor != null && $this->order->useEnvironmentFee()) {
                if($this->debitor->getExcemptFromEnvFee()) {
                    $exporter->addWarning("Navision debitor is marked as excempt from environment fee", "ENVFEE");
                }
            }

        }

    }
    
    private function buildOrderNotes(OrderExporter $exporter) {

        // Find notes to add
        // WHAT HERE?

    }

    /**
     * @param $format
     * @return OrderExporter
     * @throws \Exception
     */
    private function getExporter($format)
    {
        switch ($format) {
            case "xml":
                return new OrderXMLExporter($this->order);
            case "html":
                return new OrderHTMLExporter($this->order);
            default:
                throw new \Exception("Unknown export format: " . $format);
        }
    }

}

