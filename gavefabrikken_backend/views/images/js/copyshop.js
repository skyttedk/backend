var copyShop = (function () {
    let targetShopID;
    function showModal() {
        $("#copyshopModal").dialog({
            title: 'Kopiere gaver fra en anden shop',
            modal: true,
            width: 700,
            height: 500,
            buttons: {
                Luk: function() {
                    $(this).dialog("close");
                }
            },
            open: function() {
                // Add loading text and placeholder for search and table
                $(this).html(`
                    <div id="loadingText" style="text-align: center; padding: 20px;">
                        Henter butikker... Vent venligst.
                    </div>
                    <div id="shopContent" style="display: none;">
                        <div style="position: sticky; top: 0; background-color: white; padding: 10px 0; z-index: 1000;">
                            <input type="text" id="searchInput" placeholder="Søg efter shop navn" style="width: 98%; padding: 8px; margin-bottom: 10px;">
                        </div>
                        <div style="max-height: 280px; overflow-y: auto;">
                            <table id="shopTable" style="width: 100%; border-collapse: collapse;">
                                <thead>
                                    <tr>
                                        <th style="border: 1px solid #ddd; padding: 8px; background-color: #f2f2f2;">ID</th>
                                        <th style="border: 1px solid #ddd; padding: 8px; background-color: #f2f2f2;">Navn</th>
                                        <th style="border: 1px solid #ddd; padding: 8px; background-color: #f2f2f2;">Handling</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                `);
            }
        });
    }

    function loadShopList(targetShop) {
        return new Promise((resolve, reject) => {
            $.post("index.php?rt=shop/readAllShopsAndCardshops", {shopID: targetShop}, function(returnMsg) {
                resolve(returnMsg);
            }, "json")

        });
    }

    function populateTable(shops) {
        var tbody = $('#shopTable tbody');
        tbody.empty();
        $.each(shops, function(i, shop) {
            tbody.append(`
                <tr>
                    <td style="border: 1px solid #ddd; padding: 8px;">${shop.id}</td>
                    <td style="border: 1px solid #ddd; padding: 8px;">${shop.name}</td>
                    <td style="border: 1px solid #ddd; padding: 8px;">
                        <button class="copy-gifts-btn" data-shop-id="${shop.id}">Kopier gaver</button>
                    </td>
                </tr>
            `);
        });
    }

    function setupSearch(shops) {
        $('#searchInput').on('input', function() {
            var searchTerm = $(this).val().toLowerCase();
            var filteredShops = shops.filter(function(shop) {
                return shop.name.toLowerCase().includes(searchTerm);
            });
            populateTable(filteredShops);
        });
    }

    function copyGifts(sourceShopID) {
        // Show "copying gifts" message in modal
        $("#copyshopModal").html('<div style="text-align: center; padding: 20px;">Gaverne kopieres, vent venligst...</div>');

        $.post("index.php?rt=copyShop/copyPresents", {targetShopID: targetShopID, sourceShopID: sourceShopID}, function(returnMsg) {
            if (returnMsg.status === "1") {
                // Success: show message and refresh page
                alert("Gaverne er blevet kopieret. Siden vil nu blive genindlæst.");
                location.reload();
            } else {
                // Error: show error message and refresh page
                alert("Der opstod en fejl: " + (returnMsg.message || "Ukendt fejl"));
                location.reload();
            }
        }, "json")




    }

    return {
        init: async function (targetShop) {
            targetShopID = targetShop;
            showModal();
            try {
                var shopData = await loadShopList(targetShop);
                if (shopData.status === "1" && shopData.data && shopData.data.shops) {
                    // Hide loading text and show content
                    $("#loadingText").hide();
                    $("#shopContent").show();

                    populateTable(shopData.data.shops);
                    setupSearch(shopData.data.shops);

                    // Setup event delegation for copy buttons
                    $("#copyshopModal").on("click", ".copy-gifts-btn", function() {
                        var shopId = $(this).data("shop-id");
                        let c = confirm("Er du sikker på du vil kopiere gaverne")
                        if(c){
                            copyGifts(shopId);
                        }

                    });
                } else {
                    console.error("Unexpected data format:", shopData);
                    $("#loadingText").text("Der opstod en fejl ved indlæsning af butikker.");
                }
            } catch (error) {
                console.error("Failed to load shop list:", error);
                $("#loadingText").text("Der opstod en fejl ved indlæsning af butikker.");
            }
        }
    };
})();