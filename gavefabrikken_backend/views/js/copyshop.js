var copyShop = ( function () {
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




    async function addCopyData(source, target) {
        try {
            // Step 1: Sync present order
            console.log("addCopyData sync 1");

            const response1 = await $.post("index.php?rt=copyShop/syncPresentOrder", {
                sourceShopID: source,
                targetShopID: target
            }, "json");
            console.log("syncPresentOrder response:", response1);

            await new Promise(resolve => setTimeout(resolve, 1000));

            // Step 2: Sync child presents
            console.log("addCopyData sync 2");
            const response2 = await $.post("index.php?rt=copyShop/syncChildPresents", {
                sourceShopID: source,
                targetShopID: target
            }, "json");
            console.log("syncChildPresents response:", response2);

            await new Promise(resolve => setTimeout(resolve, 1000));

            // Step 3: Sync prices
            console.log("addCopyData sync 3");
            const response3 = await $.post("index.php?rt=copyShop/syncPresentPrices", {
                sourceShopID: source,
                targetShopID: target
            }, "json");
            console.log("syncPresentPrices response:", response3);

            await new Promise(resolve => setTimeout(resolve, 1000));

            // Step 4: Sync child setup
            console.log("addCopyData sync 4");
            const response4 = await $.post("index.php?rt=copyShop/syncChildPresentationGroups", {
                sourceShopID: source,
                targetShopID: target
            }, "json");
            console.log("syncChildPresentationGroups response:", response4);

            console.log("addCopyData completed successfully");

        } catch (error) {
            console.error("Error in addCopyData:", error);
            throw error;
        }
    }

    async function syncChilds(source, target) {
        try {
            const response = await $.post("index.php?rt=copyShop/syncChildPresents", {
                sourceShopID: source,
                targetShopID: target
            }, "json");

            console.log("syncChildPresents response:", response);
            await new Promise(resolve => setTimeout(resolve, 100));
            return response;
        } catch (error) {
            console.error("Error in syncChildPresents:", error);
            throw error;
        }
    }


    async function syncPresentationGroups(source, target) {
        // TODO: Implement syncPresentationGroups functionality
        console.log(`syncPresentationGroups called with source: ${source}, target: ${target}`);
    }












// Fixed copyGifts function (ikke async)
    function copyGifts(sourceShopID) {
        console.log("copyGifts started with sourceShopID:", sourceShopID);
        console.log("targetShopID:", targetShopID);

        // Show "copying gifts" message in modal
        $("#copyshopModal").html('<div style="text-align: center; padding: 20px;">Gaverne kopieres, vent venligst...</div>');

        console.log("About to make AJAX call to: index.php?rt=copyShop/copyPresents");

        $.post("index.php?rt=copyShop/copyPresents", {
            targetShopID: targetShopID,
            sourceShopID: sourceShopID
        }, function(returnMsg) {
            console.log("AJAX SUCCESS - got response:", returnMsg);
            console.log("Response type:", typeof returnMsg);
            console.log("Response status:", returnMsg ? returnMsg.status : "no status");

            if (returnMsg && returnMsg.status === "1") {
                console.log("Status is 1 - success path");
                if(sourceShopID != "openforall") {
                    console.log("sourceShopID is 8935 - will call addCopyData");
                    addCopyData(sourceShopID, targetShopID).then(() => {
                        console.log("addCopyData completed successfully");
                        alert("Gaverne er blevet kopieret. Siden vil nu blive genindlæst.");
                        location.reload();
                    }).catch((error) => {
                        console.error("Error in addCopyData:", error);
                        alert("Der opstod en fejl under den ekstra synkronisering.");
                        location.reload();
                    });
                } else {
                    console.log("sourceShopID is not 8935");
                    alert("Gaverne er blevet kopieret. Siden vil nu blive genindlæst.");
                    location.reload();
                }
            } else {
                console.log("Status is not 1 or no status - error path");
                console.log("returnMsg:", returnMsg);
                alert("Der opstod en fejl: " + (returnMsg && returnMsg.message ? returnMsg.message : "Ukendt fejl"));
                location.reload();
            }
        }, "json").fail(function(xhr, status, error) {
            console.error("AJAX FAILED:");
            console.error("xhr:", xhr);
            console.error("status:", status);
            console.error("error:", error);
            console.error("responseText:", xhr.responseText);
            alert("Der opstod en netværksfejl: " + error);
            location.reload();
        });

        console.log("AJAX call initiated");
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