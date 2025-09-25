<?php

namespace GFUnit\cardshop\freight;

class CSFreightItem {

    private $cardshopfreight;
    private $isChild;
    private $companyOrderList;
    private $shopId;
    private $expireDate;
    private $companyId;
    private $inactive;
    private $companyName;
    private $parentId;
    private $shipToCompany;
    private $shipToAttention;
    private $shipToAddress;
    private $shipToAddress2;
    private $shipToPostalCode;
    private $shipToCity;
    private $shipToCountry;
    private $contactName;
    private $contactPhone;
    private $contactEmail;
    private $billingCompanyId;
    private $orderCount;
    private $totalQuantity;
    private $deliveryType;

    public function __construct($cardshopfreight = null, $isChild = false, $companyOrderList = [], $shopId = 0, $expireDate = '', $companyId = 0, $inactive = false, $companyName = '', $parentId = 0, $shipToCompany = '', $shipToAttention = '', $shipToAddress = '', $shipToAddress2 = '', $shipToPostalCode = '', $shipToCity = '', $shipToCountry = '', $contactName = '', $contactPhone = '', $contactEmail = '', $billingCompanyId = 0, $orderCount = 0, $totalQuantity = 0, $deliveryType = '') {
        $this->cardshopfreight = $cardshopfreight;
        $this->isChild = $isChild;
        $this->companyOrderList = $companyOrderList;
        $this->shopId = $shopId;
        $this->expireDate = $expireDate;
        $this->companyId = $companyId;
        $this->inactive = $inactive;
        $this->companyName = $companyName;
        $this->parentId = $parentId;
        $this->shipToCompany = $shipToCompany;
        $this->shipToAttention = $shipToAttention;
        $this->shipToAddress = $shipToAddress;
        $this->shipToAddress2 = $shipToAddress2;
        $this->shipToPostalCode = $shipToPostalCode;
        $this->shipToCity = $shipToCity;
        $this->shipToCountry = $shipToCountry;
        $this->contactName = $contactName;
        $this->contactPhone = $contactPhone;
        $this->contactEmail = $contactEmail;
        $this->billingCompanyId = $billingCompanyId;
        $this->orderCount = $orderCount;
        $this->totalQuantity = $totalQuantity;
        $this->deliveryType = $deliveryType;
    }

    public function getUniqueKey() {
        // made from company_id:shop_id:expire_date
        return $this->companyId . ':' . $this->shopId . ':' . $this->expireDate->format('Y-m-d');
    }

    public function isParent() {
        return $this->parentId == 0;
    }

    public function getCardshopFreight() {
        return $this->cardshopfreight;
    }

    public function isChild() {
        return $this->isChild;
    }

    public function getCompanyOrderList() {
        return $this->companyOrderList;
    }

    public function getFirstCompanyOrder() {
        return $this->companyOrderList[0];
    }

    public function getShopId() {
        return $this->shopId;
    }

    public function getExpireDate() {
        return $this->expireDate;
    }

    public function getExpireDateText() {
        return $this->expireDate->format('Y-m-d');
    }

    public function getCompanyId() {
        return $this->companyId;
    }

    public function isInactive() {
        return $this->inactive;
    }

    public function setInactive($inactive) {
        $this->inactive = $inactive;
    }

    public function getCompanyName() {
        return $this->companyName;
    }

    public function getParentId() {
        return $this->parentId;
    }

    public function getShipToCompany() {
        return $this->shipToCompany;
    }

    public function getShipToAttention() {
        return $this->shipToAttention;
    }

    public function getShipToAddress() {
        return $this->shipToAddress;
    }

    public function getShipToAddress2() {
        return $this->shipToAddress2;
    }

    public function getShipToPostalCode() {
        return $this->shipToPostalCode;
    }

    public function getShipToCity() {
        return $this->shipToCity;
    }

    public function getShipToCountry() {
        return $this->shipToCountry;
    }

    public function getContactName() {
        return $this->contactName;
    }

    public function getContactPhone() {
        return $this->contactPhone;
    }

    public function getContactEmail() {
        return $this->contactEmail;
    }

    public function getBillingCompanyId() {
        return $this->billingCompanyId;
    }

    public function getOrderCount() {
        return $this->orderCount;
    }

    public function getTotalQuantity() {
        return $this->totalQuantity;
    }

    public function getDeliveryType() {
        return $this->deliveryType;
    }
}
