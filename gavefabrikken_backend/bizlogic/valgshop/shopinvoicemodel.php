<?php

namespace GFBiz\valgshop;

class ShopInvoiceModel
{


    private $shopInvoice;
    private $shopOrderModel;

    public function __construct($shopInvoice,$shopOrderModel=null)
    {

        if(!($shopInvoice instanceof \ShopInvoice)) {
            throw new \Exception("shopInvoice is not an instance of ShopInvoice");
        }

        $this->shopInvoice = $shopInvoice;

        if((!$shopOrderModel instanceof ShopOrderModel)) {
            $this->shopOrderModel = $shopOrderModel;
        } else {
            $this->shopOrderModel = new ShopOrderModel($shopInvoice->shop_id);
        }

    }

    public function getInvoiceID() {
        return $this->shopInvoice->id;
    }
    
    public function getBillToCustomer() {
        return $this->shopInvoice->nav_debitor_no;
    }

    public function isPrimaryCustomer() {
        return $this->getBillToCustomer() == $this->shopOrderModel->getNavDebitorNo();
    }

    public function useInvoiceFee() {
        return intvalgf($this->shopInvoice->invoice_fee) == 1;
    }

    public function getInvoiceFeePrice() {
        return intval(floatval($this->shopInvoice->invoice_fee_value)*100);
    }

    public function useEnvironmentFee() {
        $fee = floatval($this->shopInvoice->environment_fee);
        return ($fee > 0);
    }

    public function getEnvironmentFeePercent() {
        //$fee = floatval($this->shopInvoice->environment_fee);
        $fee = 3.85;
        return $fee;
    }

    // Receivers and presents

    public function getPresentCount() {
        return $this->shopInvoice->present_count;
    }

    // Gift wrap

    public function useGiftwrap() {
        return intval($this->shopInvoice->present_wrap) == 1;
    }

    public function getGiftwrapPrice() {
        return intval(floatval($this->shopInvoice->present_wrap_price)*100);
    }

    // Name tags

    public function useNametag() {
        return intval($this->shopInvoice->present_nametag) == 1;
    }

    public function getNametagPrice() {
        return intval(floatval($this->shopInvoice->present_nametag_price)*100);
    }

    // PAPER CARDS

    public function usePaperCards() {
        return $this->shopInvoice->present_papercard == 1;
    }

    public function getPaperCardPrice() {
        return intval(floatval($this->shopInvoice->present_papercard_price)*100);
    }

    public function getPaperCardLineCode() {
        return "JULEKORT";
    }

    // DOT
    public function useDOT() {
        return $this->shopInvoice->dot_use == 1;
    }

    public function getDOTAmount() {
        return intval($this->shopInvoice->dot_amount);
    }


    public function getDOTPrice() {
        return intval(floatval($this->shopInvoice->dot_price)*100);
    }

    // CARRYUP

    public function useCarryup() {
        return $this->shopInvoice->carryup_use == 1;
    }

    public function getCarryupAmount() {
        return intval($this->shopInvoice->carryup_amount);
    }

    public function getCarryupPrice() {
        return intval(floatval($this->shopInvoice->carryup_price)*100);
    }

    // PLANT TREE

    public function usePlantTree() {
        return $this->shopInvoice->plant_tree == 1;
    }

    public function getPlantTreePrice() {
        return 0;
    }

    // Private delivery

    public function usePrivateDelivery() {
        return $this->shopInvoice->private_delivery == 1;
    }

    public function getPrivateDeliveryPrice() {
        return intval(floatval($this->shopInvoice->privatedelivery_price)*100);
    }

    // FREE DELIVERY
    public function useFreeDelivery() {
        return $this->shopInvoice->deliveryprice_option == 1 && intval($this->shopInvoice->deliveryprice_amount) == 0;
    }

    // AGREED DELIVERY
    public function useAgreedDelivery() {
        return $this->shopInvoice->deliveryprice_option == 1 && intval($this->shopInvoice->deliveryprice_amount) > 0;
    }

    public function getAgreedDeliveryPrice() {
        return intval(floatval($this->shopInvoice->deliveryprice_amount)*100);
    }

    // TOTAL PRICE

    public function getShopBudget() {
        return $this->shopOrderModel->getShopBudget();
    }

    // DISCOUNT

    public function useDiscount() {
        return $this->shopInvoice->discount_option == 1;
    }

    public function getDiscountPercentage() {
        return intval($this->shopInvoice->discount_value);
    }

}