/**
 * Data handling functionality for Finalbilling
 */
export default class FinalbillingData {
    constructor() {
        this.AJAX_URL = "https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/valgshop/finalbilling/";
    }

    // API methods
    getShopsForFinalbilling(shopId) {
        var url = this.AJAX_URL + "getShopsForFinalbilling&shopID=" + shopId;

        return new Promise(function(resolve) {
            $.get(url, function(res) {
                resolve(res);
            }, "json").fail(function(jqXHR, textStatus, errorThrown) {
                resolve({status: 0, error: "API call failed: " + textStatus});
            });
        });
    }

    getShopFinalbillingData(shopId) {
        return new Promise((resolve) => {
            $.post(this.AJAX_URL + "getShopFinalbillingData", {
                shop_id: shopId
            }, function(res) {
                resolve(res);
            }, "json");
        });
    }

    getFinalbillingRapporter() {
        return new Promise((resolve) => {
            $.get(this.AJAX_URL + "getFinalbillingRapporter", function(res) {
                resolve(res);
            }, "json");
        });
    }

    getShopInvoiceData(shopId, addressId) {
        return new Promise((resolve) => {
            $.post(this.AJAX_URL + "getShopInvoiceData", {
                shop_id: shopId,
                address_id: addressId
            }, function(res) {
                resolve(res);
            }, "json");
        });
    }

    loadShopMetadataForDefaults(shopId) {
        return new Promise((resolve) => {
            $.post(this.AJAX_URL + "getShopMetadataForDefaults", {
                shop_id: shopId
            }, function(res) {
                resolve(res.data || {});
            }, "json");
        });
    }

    postFinalbillingData(data) {
        return new Promise((resolve) => {
            $.post(this.AJAX_URL + "saveShopInvoiceData", data, function(res) {
                resolve(res);
            }, "json");
        });
    }

    approveFinalbilling(shopId, addressId) {
        return new Promise((resolve) => {
            $.post(this.AJAX_URL + "approveFinalbilling", {
                shop_id: shopId,
                address_id: addressId
            }, function(res) {
                resolve(res);
            }, "json").fail(function(jqXHR, textStatus, errorThrown) {
                resolve({status: 0, error: "API call failed: " + textStatus});
            });
        });
    }

    removeApprovalFinalbilling(shopId, addressId) {
        return new Promise((resolve) => {
            $.post(this.AJAX_URL + "removeApprovalFinalbilling", {
                shop_id: shopId,
                address_id: addressId
            }, function(res) {
                resolve(res);
            }, "json").fail(function(jqXHR, textStatus, errorThrown) {
                resolve({status: 0, error: "API call failed: " + textStatus});
            });
        });
    }
}