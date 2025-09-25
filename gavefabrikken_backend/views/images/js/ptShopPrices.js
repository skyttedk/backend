/*** vigigt der er bytte om p� pris og budget i data ***/

var ptShopPrices = (function ()
{
    self = this;
    self.data = {};
    self.lang;
    self.init = async () => {
      self.event();
    }
    self.event = () => {
      $(".ptSetMultiplePrice").unbind('click').click( async function () {
        $( "#ptShopPriceDialog" ).dialog({
              modal: true,
               height: "auto",
              width: 700,

              buttons: {
                Save:  function() {
                   self.evalSave();
                },
                Cancel: function() {
                  $( this ).dialog( "close" );
                }
              }
            });
            self.lang = 1;
            $("#ptShopPrice").html("");
            self.data.customConfig = await self.loadData();

            self.buildUI2();
      })
      $(".ptSetMultiplePriceNO").unbind('click').click( async function () {
            $( "#ptShopPriceDialog" ).dialog({
                modal: true,
                height: "auto",
                width: 700,

                buttons: {
                    Save:  function() {
                        self.evalSave();
                    },
                    Cancel: function() {
                        $( this ).dialog( "close" );
                    }
                }
            });
            self.lang = 4;
          $("#ptShopPrice").html("");
            self.data.customConfig = await self.loadData();
            self.buildUI2();
        })
    }
    $(".ptSetMultiplePriceSE").unbind('click').click( async function () {
        $( "#ptShopPriceDialog" ).dialog({
            modal: true,
            height: "auto",
            width: 700,

            buttons: {
                Save:  function() {
                    self.evalSave();
                },
                Cancel: function() {
                    $( this ).dialog( "close" );
                }
            }
        });
        self.lang = 5;
        $("#ptShopPrice").html("");
        self.data.customConfig = await self.loadData();
        self.buildUI2();
    })



    self.evalSave = async () => {
            await self.save();
            await self.savePC();
           $( "#ptShopPriceDialog" ).dialog( "close" );
           $("#ptPrisMsg").html("Priser opdateret").fadeIn(500)
           setTimeout(function(){ $("#ptPrisMsg").fadeOut(1000) }, 2000);

    }

    self.loadData = () => {
            return new Promise(function(resolve, reject) {
                $.post("index.php?rt=ptAdmin/loadPresentPrice",{id:_shopId,lang:mapCodeToLanguage(self.lang)}, function(res, status) {
                    if(res.status == 0) {  }
                    else { resolve(res) }
                }, "json");
            })
     }
    self.getNavCostPrice = (presentID) => {
        let languageId = self.lang;
        let jsonData = self.data.customConfig.data.pricesNAV;

        // Gennemgå hvert element i JSON-arrayet
        for (let i = 0; i < jsonData.length; i++) {
            const item = jsonData[i];
            // Tjek om både id og language_id matcher

            if (item.attributes.id === presentID && item.attributes.language_id === languageId) {
                console.log(item)
                return item;  // Returnér det matchende element
            }
        }
        return null;  // Returnér null hvis intet match findes

    }
    self.buildUI2 = () => {
        $("#pimPrices").remove();
        $("#ptShopPrice").html("");

        // Add Open All / Close All buttons at the top
        let controlButtons = `
    <div class="accordion-controls">
        <button id="toggleAllAccordions" class="accordion-control-btn">Åbn alle</button>
    </div>
    `;
        $("#ptShopPrice").append(controlButtons);

        // First, create a map of parents and their children
        const parentChildMap = {};

        // First pass: identify all parents (items where pchild = 0)
        $.each(self.data.customConfig.data.pricesPresentation, function(index, value) {
            const id = value.attributes.id;
            const pchild = value.attributes.pchild;

            if (pchild === 0) {
                // This is a parent
                parentChildMap[id] = {
                    parent: value,
                    children: [],
                    index: value.attributes.index_ !== undefined ? value.attributes.index_ : 9999 // Use high default for unset index
                };
            }
        });

        // Second pass: add children to their parents
        $.each(self.data.customConfig.data.pricesPresentation, function(index, value) {
            const pchild = value.attributes.pchild;

            if (pchild !== 0 && parentChildMap[pchild]) {
                // This is a child and its parent exists in our map
                // Sort children by index_ if available
                const childIndex = value.attributes.index_ !== undefined ? value.attributes.index_ : 9999;

                // Add child with its index
                value.childIndex = childIndex;
                parentChildMap[pchild].children.push(value);
            }
        });

        // Sort parent groups by index_
        const sortedParents = Object.values(parentChildMap).sort((a, b) => a.index - b.index);

        // Now build the UI with parent-child grouping in the correct order
        $.each(sortedParents, function(_, group) {
            const parentItem = group.parent;

            // Sort children by their index
            const childItems = group.children.sort((a, b) => a.childIndex - b.childIndex);

            // Process parent pricing data
            let pt_special_no = "", pt_pris_no = "", pt_budget_no = "";
            let pt_special_no_show = "", pt_pris_no_show = "", pt_budget_no_show = "";
            let settings = {};
            let pt_costprise = 100;

            try {
                if(parentItem.attributes.pt_price != null && parentItem.attributes.pt_price.length > 10 && self.lang == 1){
                    settings = JSON.parse(parentItem.attributes.pt_price);
                    pt_special_no = settings.special || "";
                    pt_pris_no = settings.pris || "";
                    pt_budget_no = settings.budget || "";
                    pt_special_no_show = settings.vis_special || "";
                    pt_pris_no_show = settings.vis_pris || "";
                    pt_budget_no_show = settings.vis_budget || "";
                }

                if(parentItem.attributes.pt_price_no != null && parentItem.attributes.pt_price_no.length > 10 && self.lang == 4){
                    settings = JSON.parse(parentItem.attributes.pt_price_no);
                    pt_special_no = settings.special || "";
                    pt_pris_no = settings.pris || "";
                    pt_budget_no = settings.budget || "";
                    pt_special_no_show = settings.vis_special || "";
                    pt_pris_no_show = settings.vis_pris || "";
                    pt_budget_no_show = settings.vis_budget || "";
                }

                if (parentItem.attributes.pt_price_se != null && parentItem.attributes.pt_price_se.length > 10 && self.lang == 5) {
                    settings = JSON.parse(parentItem.attributes.pt_price_se);
                    pt_special_no = settings.special || "";
                    pt_pris_no = settings.pris || "";
                    pt_budget_no = settings.budget || "";
                    pt_special_no_show = settings.vis_special || "";
                    pt_pris_no_show = settings.vis_pris || "";
                    pt_budget_no_show = settings.vis_budget || "";
                }
            } catch (e) {
                console.error("Fejl ved parsing af priser for produkt ID " + parentItem.attributes.id + ":", e);
            }

            // Safe image path handling
            const imagePath = parentItem.attributes.media_path ?
                'views/media/user/' + parentItem.attributes.media_path + '.jpg' :
                'views/media/placeholder.jpg';

            // Build parent HTML with header - compact layout with title and image side by side and toggle functionality
            let html = "<tr class='parent-header-row' data-parent-id='" + parentItem.attributes.id + "'>";
            html += "<td colspan=4 class='parent-header'><div class='parent-title-container'>";
            html += "<img src='" + imagePath + "' width=60 class='parent-image' onerror='this.src=\"views/media/placeholder.jpg\"' />";
            html += "<span class='parent-title'>" + parentItem.attributes.nav_name + "</span>";
            html += "<span class='toggle-indicator'>▼</span>"; // Add toggle indicator
            html += "</div></td></tr>";

            // Add parent item row (now part of collapsible content)
            html += "<tr class='present-item parent-item collapsible-content' data-parent-id='" + parentItem.attributes.id + "' item-data='" + parentItem.attributes.id + "'>";
            html += "<td></td>"; // Empty cell since we already showed the image with the title
            html += "<td><label class='price-label'>Specialaftale:</label><input class='pt_special_no small-input' cv-data='" + encodeEntities(pt_special_no) + "' show-data= '" + pt_special_no_show + "' type='text' value='" + encodeEntities(pt_special_no) + "' placeholder='Pris ej sat' /></td>";
            html += "<td><label class='price-label'>Budget:</label><input class='pt_pris_no small-input' cv-data='" + encodeEntities(pt_pris_no) + "' show-data='" + pt_pris_no_show + "' type='text' value='" + encodeEntities(pt_pris_no) + "' placeholder='Pris ej sat' /></td>";
            html += "<td><label class='price-label'>Vejl. udsalgspris:</label><input class='pt_budget_no small-input' cv-data='" + encodeEntities(pt_budget_no) + "' show-data= '" + pt_budget_no_show + "' type='text' value='" + encodeEntities(pt_budget_no) + "' placeholder='Pris ej sat' /></td>";
            html += "<td class='cost-price-col' style='display:none;'><label class='price-label'>Kostpris:</label><span>" + pt_costprise + "</span></td>";
            html += "</tr>";

            // Add PC price fields if pc_active exists (as part of collapsible content)
            if (parentItem.attributes.pc_active) {
                let pcPriceData = {};

                if (parentItem.attributes.pc_price) {
                    try {
                        pcPriceData = JSON.parse(parentItem.attributes.pc_price);
                    } catch (e) {
                        console.error("Fejl ved parsing af pc_price:", e);
                        pcPriceData = { special: '', budget: '', price: '' };
                    }
                }

                html += "<tr class='pc-price-fields collapsible-content' data-parent-id='" + parentItem.attributes.id + "' item-data='" + parentItem.attributes.id + "'>";
                html += "<td><span class='pc-label'>Vælg mellem eller sampak fælles priser</span></td>";
                html += "<td><label class='price-label'>Specialaftale:</label><input class='pc_special small-input' pc-data='" + encodeEntities(pcPriceData.special || '') + "' type='text' value='" + encodeEntities(pcPriceData.special || '') + "' placeholder='Pris ej sat' /></td>";
                html += "<td><label class='price-label'>Budget:</label><input class='pc_budget small-input' type='text' pc-data='" + encodeEntities(pcPriceData.budget || '') + "' value='" + encodeEntities(pcPriceData.budget || '') + "' placeholder='Pris ej sat' /></td>";
                html += "<td><label class='price-label'>Vejl. udsalgspris:</label><input class='pc_price small-input' pc-data='" + encodeEntities(pcPriceData.price || '') + "' type='text' value='" + encodeEntities(pcPriceData.price || '') + "' placeholder='Pris ej sat' /></td>";
                html += "<td></td>";
                html += "</tr>";
            }

            // Add child items if any (as part of collapsible content)
            if (childItems.length > 0) {
                // Add a divider to indicate child items section
                html += "<tr class='collapsible-content' data-parent-id='" + parentItem.attributes.id + "'><td colspan=4><div class='child-divider'><span>Vælg mellem eller sampak</span></div></td></tr>";

                // Process each child item
                $.each(childItems, function(childIndex, childItem) {
                    // Process child pricing data
                    let child_pt_special_no = "", child_pt_pris_no = "", child_pt_budget_no = "";
                    let child_pt_special_no_show = "", child_pt_pris_no_show = "", child_pt_budget_no_show = "";
                    let childSettings = {};

                    try {
                        if(childItem.attributes.pt_price != null && childItem.attributes.pt_price.length > 10 && self.lang == 1){
                            childSettings = JSON.parse(childItem.attributes.pt_price);
                            child_pt_special_no = childSettings.special || "";
                            child_pt_pris_no = childSettings.pris || "";
                            child_pt_budget_no = childSettings.budget || "";
                            child_pt_special_no_show = childSettings.vis_special || "";
                            child_pt_pris_no_show = childSettings.vis_pris || "";
                            child_pt_budget_no_show = childSettings.vis_budget || "";
                        }

                        if(childItem.attributes.pt_price_no != null && childItem.attributes.pt_price_no.length > 10 && self.lang == 4){
                            childSettings = JSON.parse(childItem.attributes.pt_price_no);
                            child_pt_special_no = childSettings.special || "";
                            child_pt_pris_no = childSettings.pris || "";
                            child_pt_budget_no = childSettings.budget || "";
                            child_pt_special_no_show = childSettings.vis_special || "";
                            child_pt_pris_no_show = childSettings.vis_pris || "";
                            child_pt_budget_no_show = childSettings.vis_budget || "";
                        }

                        if (childItem.attributes.pt_price_se != null && childItem.attributes.pt_price_se.length > 10 && self.lang == 5) {
                            childSettings = JSON.parse(childItem.attributes.pt_price_se);
                            child_pt_special_no = childSettings.special || "";
                            child_pt_pris_no = childSettings.pris || "";
                            child_pt_budget_no = childSettings.budget || "";
                            child_pt_special_no_show = childSettings.vis_special || "";
                            child_pt_pris_no_show = childSettings.vis_pris || "";
                            child_pt_budget_no_show = childSettings.vis_budget || "";
                        }
                    } catch (e) {
                        console.error("Fejl ved parsing af priser for child produkt ID " + childItem.attributes.id + ":", e);
                    }

                    // Safe image path handling for child
                    const childImagePath = childItem.attributes.media_path ?
                        'views/media/user/' + childItem.attributes.media_path + '.jpg' :
                        'views/media/placeholder.jpg';

                    // Add child item row with title and image side by side (as part of collapsible content)
                    html += "<tr class='present-item child-item collapsible-content' data-parent-id='" + parentItem.attributes.id + "' item-data='" + childItem.attributes.id + "' parent-data='" + parentItem.attributes.id + "'>";
                    html += "<td><div class='child-container'><div class='child-indicator'>└─</div><img src='" + childImagePath + "' width=45 class='child-image' onerror='this.src=\"views/media/placeholder.jpg\"' /><div class='child-title'>" + childItem.attributes.nav_name + "</div></div></td>";
                    html += "<td>";
                    html += "<label class='price-label'>Specialaftale:</label><input class='pt_special_no small-input' cv-data='" + encodeEntities(child_pt_special_no) + "' show-data= '" + child_pt_special_no_show + "' type='text' value='" + encodeEntities(child_pt_special_no) + "' placeholder='Pris ej sat' /></td>";
                    html += "<td><label class='price-label'>Budget:</label><input class='pt_pris_no small-input' cv-data='" + encodeEntities(child_pt_pris_no) + "' show-data='" + child_pt_pris_no_show + "' type='text' value='" + encodeEntities(child_pt_pris_no) + "' placeholder='Pris ej sat' /></td>";
                    html += "<td><label class='price-label'>Vejl. udsalgspris:</label><input class='pt_budget_no small-input' cv-data='" + encodeEntities(child_pt_budget_no) + "' show-data= '" + child_pt_budget_no_show + "' type='text' value='" + encodeEntities(child_pt_budget_no) + "' placeholder='Pris ej sat' /></td>";
                    html += "<td class='cost-price-col' style='display:none;'><label class='price-label'>Kostpris:</label><span>" + pt_costprise + "</span></td>";
                    html += "</tr>";

                    // Add PC price fields if pc_active exists for child
                    if (childItem.attributes.pc_active) {
                        let childPcPriceData = {};

                        if (childItem.attributes.pc_price) {
                            try {
                                childPcPriceData = JSON.parse(childItem.attributes.pc_price);
                            } catch (e) {
                                console.error("Fejl ved parsing af pc_price for child:", e);
                                childPcPriceData = { special: '', budget: '', price: '' };
                            }
                        }

                        html += "<tr class='pc-price-fields child-pc-fields collapsible-content' data-parent-id='" + parentItem.attributes.id + "' item-data='" + childItem.attributes.id + "' parent-data='" + parentItem.attributes.id + "'>";
                        html += "<td><div class='child-indicator'>└─</div></td>";
                        html += "<td><label class='price-label'>Specialaftale:</label><input class='pc_special small-input' pc-data='" + encodeEntities(childPcPriceData.special || '') + "' type='text' value='" + encodeEntities(childPcPriceData.special || '') + "' placeholder='Pris ej sat' /></td>";
                        html += "<td><label class='price-label'>Budget:</label><input class='pc_budget small-input' type='text' pc-data='" + encodeEntities(childPcPriceData.budget || '') + "' value='" + encodeEntities(childPcPriceData.budget || '') + "' placeholder='Pris ej sat' /></td>";
                        html += "<td><label class='price-label'>Vejl. udsalgspris:</label><input class='pc_price small-input' pc-data='" + encodeEntities(childPcPriceData.price || '') + "' type='text' value='" + encodeEntities(childPcPriceData.price || '') + "' placeholder='Pris ej sat' /></td>";
                        html += "<td></td>";
                        html += "</tr>";
                    }
                });
            }

            // Add a spacer after each group
            html += "<tr class='group-divider-row'><td colspan=4><hr class='group-divider'></td></tr>";

            // Append this entire group to the container
            $("#ptShopPrice").append(html);
        });

        // Initialize toggle functionality for collapsible sections
        $('.parent-header-row').each(function() {
            // Add collapsed class to all parents
            $(this).addClass('collapsed');

            // All sections start closed - no need to explicitly hide anything
            // as the CSS already sets display: none for .collapsible-content
        });

        // Add click handler for toggling content
        $('.parent-header-row').on('click', function() {
            const parentId = $(this).data('parent-id');

            // Toggle the collapsed class
            $(this).toggleClass('collapsed');

            // Toggle visibility of all elements with matching parent-id
            $('.collapsible-content[data-parent-id="' + parentId + '"]').slideToggle(200);
        });

        // Add handlers for toggle all button
        $('#toggleAllAccordions').on('click', function() {
            // Check if all are already open
            const allOpen = $('.parent-header-row.collapsed').length === 0;

            if (allOpen) {
                // If all are open, close them
                $('.parent-header-row').addClass('collapsed');
                $('.collapsible-content').slideUp(200);
                $(this).text('Åbn alle');
            } else {
                // If some or all are closed, open them all
                $('.parent-header-row').removeClass('collapsed');
                $('.collapsible-content').slideDown(200);
                $(this).text('Luk alle');
            }
        });

        // Add keyboard accessibility
        $('.parent-header-row').attr('tabindex', '0').attr('role', 'button').attr('aria-expanded', 'false');
        $('.parent-header-row').on('keydown', function(e) {
            // Enter or space key
            if (e.which === 13 || e.which === 32) {
                e.preventDefault();
                $(this).click();

                // Update aria-expanded attribute
                const isExpanded = !$(this).hasClass('collapsed');
                $(this).attr('aria-expanded', isExpanded.toString());
            }
        });

        // Add CSS for styling the parent-child relationships with smaller text and compact layout
        $("<style>")
            .prop("type", "text/css")
            .html(`
    .parent-header { 
        background-color: #f5f5f5; 
        padding: 6px;
        cursor: pointer;
    }
    .parent-header:hover {
        background-color: #e8e8e8;
    }
    .parent-title-container {
        display: flex;
        align-items: center;
        justify-content: space-between;
        min-height: 45px; /* Ensure minimum height for larger images */
    }
    .parent-image {
        margin-right: 10px;
        object-fit: contain;
    }
    .parent-title {
        font-weight: bold;
        font-size: 14px; /* Slightly increased from 13px */
        flex-grow: 1;
    }
    .toggle-indicator {
        font-size: 10px;
        color: #666;
        transition: transform 0.3s;
        margin-left: 10px;
    }
    .parent-header-row.collapsed .toggle-indicator {
        transform: rotate(-90deg);
    }
    .child-divider { 
        border-top: 1px dashed #ccc; 
        margin: 2px 0; /* Reduced from 3px */
        position: relative; 
        text-align: center; 
    }
    .child-divider span {
        background: white;
        padding: 0 10px;
        position: relative;
        top: -8px;
        font-size: 11px;
        color: #666;
    }
    .child-item td { 
        padding: 2px; /* Reduced from 3px */
        background-color: #f9f9f9;
    }
    .child-container {
        display: flex;
        align-items: center;
        min-height: 40px; /* Ensure minimum height for larger images */
    }
    .child-indicator { 
        display: inline-block; 
        margin-right: 5px; 
        color: #999; 
        font-size: 14px;
    }
    .child-image {
        margin-right: 5px;
        object-fit: contain;
    }
    .child-title {
        font-size: 12px; /* Increased from 11px */
        color: #555;
        max-width: 120px;
        display: inline-block;
    }
    .group-divider {
        margin: 5px 0; /* Reduced from 10px */
        border: 0;
        border-top: 1px solid #eee;
    }
    .price-label {
        font-size: 11px;
        display: block;
        margin-bottom: 2px;
    }
    .small-input {
        font-size: 12px;
        padding: 2px;
        width: 90%;
        height: 24px; /* Increased from 22px */
    }
    .pc-label {
        font-size: 11px;
        color: #666;
    }
    tr.present-item td {
        padding: 2px; /* Reduced from 3px */
        vertical-align: top;
    }
    tr.pc-price-fields td {
        padding: 2px;
        vertical-align: top;
    }
    /* Hide collapsible content by default */
    .collapsible-content {
        display: none;
    }
    /* Reduce space between parent items */
    .group-divider-row {
        line-height: 5px; /* Reduced to bring items closer */
    }
    /* Ensure proper alignment in all rows */
    table#ptShopPrice tr {
        line-height: 1.2;
    }
    /* Styling for accordion control buttons */
    .accordion-controls {
        display: flex;
        gap: 10px;
        margin-bottom: 10px;
        padding: 5px 0;
        border-bottom: 1px solid #ddd;
    }
    .accordion-control-btn {
        background-color: #f0f0f0;
        border: 1px solid #ccc;
        padding: 5px 10px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 12px;
        transition: background-color 0.2s;
    }
    .accordion-control-btn:hover {
        background-color: #e0e0e0;
    }
    /* Focus styles for accessibility */
    .parent-header-row:focus {
        outline: 2px solid #4d90fe;
        outline-offset: -2px;
    }
    /* Loading indicator */
    .loading-indicator {
        text-align: center;
        padding: 20px;
        font-style: italic;
        color: #666;
    }
`)
            .appendTo("head");

        // Helper function to safely encode HTML entities in strings
        function encodeEntities(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }
    };

    
    self.buildUI_3 = () => {
        $("#pimPrices").remove();
        $("#ptShopPrice").html("");

        // First, create a map of parents and their children
        const parentChildMap = {};

        // First pass: identify all parents (items where pchild = 0)
        $.each(self.data.customConfig.data.pricesPresentation, function(index, value) {
            const id = value.attributes.id;
            const pchild = value.attributes.pchild;

            if (pchild === 0) {
                // This is a parent
                parentChildMap[id] = {
                    parent: value,
                    children: []
                };
            }
        });

        // Second pass: add children to their parents
        $.each(self.data.customConfig.data.pricesPresentation, function(index, value) {
            const pchild = value.attributes.pchild;

            if (pchild !== 0 && parentChildMap[pchild]) {
                // This is a child and its parent exists in our map
                parentChildMap[pchild].children.push(value);
            }
        });

        // Now build the UI with parent-child grouping
        $.each(parentChildMap, function(parentId, group) {
            const parentItem = group.parent;
            const childItems = group.children;

            // Process parent pricing data
            let pt_special_no = "", pt_pris_no = "", pt_budget_no = "";
            let pt_special_no_show = "", pt_pris_no_show = "", pt_budget_no_show = "";
            let settings = {};
            let pt_costprise = 100;

            if(parentItem.attributes.pt_price != null && parentItem.attributes.pt_price.length > 10 && self.lang == 1){
                settings = JSON.parse(parentItem.attributes.pt_price);
                pt_special_no = settings.special;
                pt_pris_no = settings.pris;
                pt_budget_no = settings.budget;
                pt_special_no_show = settings.vis_special;
                pt_pris_no_show = settings.vis_pris;
                pt_budget_no_show = settings.vis_budget;
            }

            if(parentItem.attributes.pt_price_no != null && parentItem.attributes.pt_price_no.length > 10 && self.lang == 4){
                settings = JSON.parse(parentItem.attributes.pt_price_no);
                pt_special_no = settings.special;
                pt_pris_no = settings.pris;
                pt_budget_no = settings.budget;
                pt_special_no_show = settings.vis_special;
                pt_pris_no_show = settings.vis_pris;
                pt_budget_no_show = settings.vis_budget;
            }

            if (parentItem.attributes.pt_price_se != null && parentItem.attributes.pt_price_se.length > 10 && self.lang == 5) {
                settings = JSON.parse(parentItem.attributes.pt_price_se);
                pt_special_no = settings.special;
                pt_pris_no = settings.pris;
                pt_budget_no = settings.budget;
                pt_special_no_show = settings.vis_special;
                pt_pris_no_show = settings.vis_pris;
                pt_budget_no_show = settings.vis_budget;
            }

            // Build parent HTML with header - compact layout with title and image side by side
            let html = "<tr><td colspan=4 class='parent-header'><div class='parent-title-container'><img src='views/media/user/" + parentItem.attributes.media_path + ".jpg' width=40 class='parent-image' /><span class='parent-title'>" + parentItem.attributes.nav_name + "</span></div></td></tr>";

            // Add parent item row
            html += "<tr class='present-item parent-item' item-data='" + parentItem.attributes.id + "'>";
            html += "<td></td>"; // Empty cell since we already showed the image with the title
            html += "<td><label class='price-label'>Specialaftale:</label><input class='pt_special_no small-input' cv-data='" + pt_special_no + "' show-data= '" + pt_special_no_show + "' type='text' value='" + pt_special_no + "' placeholder='Pris ej sat' /></td>";
            html += "<td><label class='price-label'>Budget:</label><input class='pt_pris_no small-input' cv-data='" + pt_pris_no + "' show-data='" + pt_pris_no_show + "' type='text' value='" + pt_pris_no + "' placeholder='Pris ej sat' /></td>";
            html += "<td><label class='price-label'>Vejl. udsalgspris:</label><input class='pt_budget_no small-input' cv-data='" + pt_budget_no + "' show-data= '" + pt_budget_no_show + "' type='text' value='" + pt_budget_no + "' placeholder='Pris ej sat' /></td>";
            html += "<td class='cost-price-col' style='display:none;'><label class='price-label'>Kostpris:</label><span>" + pt_costprise + "</span></td>";
            html += "</tr>";

            // Add PC price fields if pc_active exists
            if (parentItem.attributes.pc_active) {
                let pcPriceData = {};

                if (parentItem.attributes.pc_price) {
                    try {
                        pcPriceData = JSON.parse(parentItem.attributes.pc_price);
                    } catch (e) {
                        console.error("Fejl ved parsing af pc_price:", e);
                    }
                }

                html += "<tr class='pc-price-fields' item-data='" + parentItem.attributes.id + "'>";
                html += "<td><span class='pc-label'>Vælg mellem eller sampak fælles priser</span></td>";
                html += "<td><label class='price-label'>Specialaftale:</label><input class='pc_special small-input' pc-data='" + (pcPriceData.special || '') + "' type='text' value='" + (pcPriceData.special || '') + "' placeholder='Pris ej sat' /></td>";
                html += "<td><label class='price-label'>Budget:</label><input class='pc_budget small-input' type='text' pc-data='" + (pcPriceData.budget || '') + "' value='" + (pcPriceData.budget || '') + "' placeholder='Pris ej sat' /></td>";
                html += "<td><label class='price-label'>Vejl. udsalgspris:</label><input class='pc_price small-input' pc-data='" + (pcPriceData.price || '') + "' type='text' value='" + (pcPriceData.price || '') + "' placeholder='Pris ej sat' /></td>";
                html += "<td></td>";
                html += "</tr>";
            }

            // Add child items if any
            if (childItems.length > 0) {
                // Add a divider to indicate child items section
                html += "<tr><td colspan=4><div class='child-divider'><span>Vælg mellem eller sampak</span></div></td></tr>";

                // Process each child item
                $.each(childItems, function(childIndex, childItem) {
                    // Process child pricing data
                    let child_pt_special_no = "", child_pt_pris_no = "", child_pt_budget_no = "";
                    let child_pt_special_no_show = "", child_pt_pris_no_show = "", child_pt_budget_no_show = "";
                    let childSettings = {};

                    if(childItem.attributes.pt_price != null && childItem.attributes.pt_price.length > 10 && self.lang == 1){
                        childSettings = JSON.parse(childItem.attributes.pt_price);
                        child_pt_special_no = childSettings.special;
                        child_pt_pris_no = childSettings.pris;
                        child_pt_budget_no = childSettings.budget;
                        child_pt_special_no_show = childSettings.vis_special;
                        child_pt_pris_no_show = childSettings.vis_pris;
                        child_pt_budget_no_show = childSettings.vis_budget;
                    }

                    if(childItem.attributes.pt_price_no != null && childItem.attributes.pt_price_no.length > 10 && self.lang == 4){
                        childSettings = JSON.parse(childItem.attributes.pt_price_no);
                        child_pt_special_no = childSettings.special;
                        child_pt_pris_no = childSettings.pris;
                        child_pt_budget_no = childSettings.budget;
                        child_pt_special_no_show = childSettings.vis_special;
                        child_pt_pris_no_show = childSettings.vis_pris;
                        child_pt_budget_no_show = childSettings.vis_budget;
                    }

                    if (childItem.attributes.pt_price_se != null && childItem.attributes.pt_price_se.length > 10 && self.lang == 5) {
                        childSettings = JSON.parse(childItem.attributes.pt_price_se);
                        child_pt_special_no = childSettings.special;
                        child_pt_pris_no = childSettings.pris;
                        child_pt_budget_no = childSettings.budget;
                        child_pt_special_no_show = childSettings.vis_special;
                        child_pt_pris_no_show = childSettings.vis_pris;
                        child_pt_budget_no_show = childSettings.vis_budget;
                    }

                    // Add child item row with title and image side by side
                    html += "<tr class='present-item child-item' item-data='" + childItem.attributes.id + "' parent-data='" + parentItem.attributes.id + "'>";
                    html += "<td><div class='child-container'><div class='child-indicator'>└─</div><img src='views/media/user/" + childItem.attributes.media_path + ".jpg' width=30 class='child-image' /><div class='child-title'>" + childItem.attributes.nav_name + "</div></div></td>";
                    html += "<td>";
                    html += "<label class='price-label'>Specialaftale:</label><input class='pt_special_no small-input' cv-data='" + child_pt_special_no + "' show-data= '" + child_pt_special_no_show + "' type='text' value='" + child_pt_special_no + "' placeholder='Pris ej sat' /></td>";
                    html += "<td><label class='price-label'>Budget:</label><input class='pt_pris_no small-input' cv-data='" + child_pt_pris_no + "' show-data='" + child_pt_pris_no_show + "' type='text' value='" + child_pt_pris_no + "' placeholder='Pris ej sat' /></td>";
                    html += "<td><label class='price-label'>Vejl. udsalgspris:</label><input class='pt_budget_no small-input' cv-data='" + child_pt_budget_no + "' show-data= '" + child_pt_budget_no_show + "' type='text' value='" + child_pt_budget_no + "' placeholder='Pris ej sat' /></td>";
                    html += "<td class='cost-price-col' style='display:none;'><label class='price-label'>Kostpris:</label><span>" + pt_costprise + "</span></td>";
                    html += "</tr>";

                    // Add PC price fields if pc_active exists for child
                    if (childItem.attributes.pc_active) {
                        let childPcPriceData = {};

                        if (childItem.attributes.pc_price) {
                            try {
                                childPcPriceData = JSON.parse(childItem.attributes.pc_price);
                            } catch (e) {
                                console.error("Fejl ved parsing af pc_price for child:", e);
                            }
                        }

                        html += "<tr class='pc-price-fields child-pc-fields' item-data='" + childItem.attributes.id + "' parent-data='" + parentItem.attributes.id + "'>";
                        html += "<td><div class='child-indicator'>└─</div></td>";
                        html += "<td><label class='price-label'>Specialaftale:</label><input class='pc_special small-input' pc-data='" + (childPcPriceData.special || '') + "' type='text' value='" + (childPcPriceData.special || '') + "' placeholder='Pris ej sat' /></td>";
                        html += "<td><label class='price-label'>Budget:</label><input class='pc_budget small-input' type='text' pc-data='" + (childPcPriceData.budget || '') + "' value='" + (childPcPriceData.budget || '') + "' placeholder='Pris ej sat' /></td>";
                        html += "<td><label class='price-label'>Vejl. udsalgspris:</label><input class='pc_price small-input' pc-data='" + (childPcPriceData.price || '') + "' type='text' value='" + (childPcPriceData.price || '') + "' placeholder='Pris ej sat' /></td>";
                        html += "<td></td>";
                        html += "</tr>";
                    }
                });
            }

            // Add a spacer after each group
            html += "<tr><td colspan=4><hr class='group-divider'></td></tr>";

            // Append this entire group to the container
            $("#ptShopPrice").append(html);
        });

        // Add CSS for styling the parent-child relationships with smaller text and compact layout
        $("<style>")
            .prop("type", "text/css")
            .html(`
        .parent-header { 
            background-color: #f5f5f5; 
            padding: 5px;
        }
        .parent-title-container {
            display: flex;
            align-items: center;
        }
        .parent-image {
            margin-right: 10px;
        }
        .parent-title {
            font-weight: bold;
            font-size: 13px;
        }
        .child-divider { 
            border-top: 1px dashed #ccc; 
            margin: 3px 0; 
            position: relative; 
            text-align: center; 
        }
        .child-divider span {
            background: white;
            padding: 0 10px;
            position: relative;
            top: -8px;
            font-size: 11px;
            color: #666;
        }
        .child-item td { 
            padding: 3px; 
            background-color: #f9f9f9;
        }
        .child-container {
            display: flex;
            align-items: center;
        }
        .child-indicator { 
            display: inline-block; 
            margin-right: 5px; 
            color: #999; 
            font-size: 14px;
        }
        .child-image {
            margin-right: 5px;
        }
        .child-title {
            font-size: 11px;
            color: #555;
            max-width: 120px;
            display: inline-block;
        }
        .group-divider {
            margin: 10px 0;
            border: 0;
            border-top: 1px solid #eee;
        }
        .price-label {
            font-size: 11px;
            display: block;
            margin-bottom: 2px;
        }
        .small-input {
            font-size: 12px;
            padding: 2px;
            width: 90%;
            height: 22px;
        }
        .pc-label {
            font-size: 11px;
            color: #666;
        }
        tr.present-item td {
            padding: 3px;
            vertical-align: top;
        }
        tr.pc-price-fields td {
            padding: 2px;
            vertical-align: top;
        }
    `)
            .appendTo("head");
    };

    self.buildUI = () => {
        $("#pimPrices").remove();
        $("#ptShopPrice").html("");
          $.each(self.data.customConfig.data.pricesPresentation, async function(index, value) {
              if(value.attributes.pchild != 0){
                  return;
              }

              let pt_special_no = "", pt_pris_no = "", pt_budget_no = "";
              let pt_special_sys = "", pt_pris_sys = "", pt_budget_sys = "";
              let systemPrice = {}, settings = {};
              let pt_special_no_show = "", pt_pris_no_show = "", pt_budget_no_show = "";
              let pt_costprise = 100;
            //  self.getNavCostPrice(value.attributes.id)
              /*
              if(_lang==1){ systemPrice = JSON.parse(present.data[0].pt_price);           }
              if(_lang==4){ systemPrice = JSON.parse(present.data[0].pt_price_no);           }
              */

              if(value.attributes.pt_price != null && value.attributes.pt_price.length > 10 && self.lang == 1){
                  settings = JSON.parse(value.attributes.pt_price)
                  pt_special_no = settings.special;
                  pt_pris_no    = settings.pris;
                  pt_budget_no  = settings.budget;
                  pt_special_no_show = settings.vis_special;
                  pt_pris_no_show = settings.vis_pris;
                  pt_budget_no_show = settings.vis_budget;
              }

              if(value.attributes.pt_price_no != null && value.attributes.pt_price_no.length > 10 && self.lang == 4){
                  settings = JSON.parse(value.attributes.pt_price_no)
                  pt_special_no = settings.special;
                  pt_pris_no    = settings.pris;
                  pt_budget_no  = settings.budget;
                  pt_special_no_show = settings.vis_special;
                  pt_pris_no_show = settings.vis_pris;
                  pt_budget_no_show = settings.vis_budget;
              }

                  if (value.attributes.pt_price_se != null && value.attributes.pt_price_se.length > 10 && self.lang == 5) {
                      settings = JSON.parse(value.attributes.pt_price_se)
                      pt_special_no = settings.special;
                      pt_pris_no = settings.pris;
                      pt_budget_no = settings.budget;
                      pt_special_no_show = settings.vis_special;
                      pt_pris_no_show = settings.vis_pris;
                      pt_budget_no_show = settings.vis_budget;

                  }


              let html = "<tr><td colspan=3><br><b><u>"+value.attributes.nav_name+"</u></b></td></tr>";
                  html+="<tr class='present-item' item-data='"+value.attributes.id+"'><td><img src='views/media/user/"+value.attributes.media_path+".jpg' width=60  /></td>";
                  html+="<td   ><label style='font-size:12px;'>Specialaftale:</label><br><input class='pt_special_no' cv-data='"+pt_special_no+"' show-data= '"+pt_special_no_show+"' type='text' size=10  value='"+pt_special_no+"' placeholder='Pris ej sat' /></td>";
                  html+="<td ><label style='font-size:12px;'>Budget:</label><br><input class='pt_pris_no' cv-data='"+pt_pris_no+"' show-data='"+pt_pris_no_show+"' type='text' size=10 value='"+pt_pris_no+"' placeholder='Pris ej sat' /></td>";
                  html+="<td ><label style='font-size:12px;'>Vejl. udsalgspris:</label><br><input class='pt_budget_no' cv-data='"+pt_budget_no+"' show-data= '"+pt_budget_no_show+"' type='text' size=10 value='"+pt_budget_no+"' placeholder='Pris ej sat' /></td>";
                  html+="<td ><label style='font-size:12px;'>Kostpris:</label><br><span>"+pt_costprise+"</span></td>";
                  html+="</tr>";

              // Add PC price fields if pc_price exists and is not null



              if (value.attributes.pc_active) {
                  let pcPriceData = {};

                  if (value.attributes.pc_price) { // Tjekker om pc_price ikke er null eller tom
                      try {
                          pcPriceData = JSON.parse(value.attributes.pc_price);
                      } catch (e) {
                          console.error("Fejl ved parsing af pc_price:", e);
                      }
                  }
                    console.log(pcPriceData)
                  html += "<tr class='pc-price-fields' item-data='" + value.attributes.id + "'>";
                  html += "<td><hr>Vælg mellem eller sampak fælles priser<br></td>"; // Empty cell for alignment
                  html += "<td><label style='font-size:12px;'>Specialaftale:</label><br><input class='pc_special'  pc-data='" + (pcPriceData.special || '') + "' type='text' size=10 value='" + (pcPriceData.special || '') + "' placeholder='Pris ej sat' /></td>";
                  html += "<td><label style='font-size:12px;'>Budget:</label><br><input class='pc_budget' type='text' pc-data='" + (pcPriceData.budget || '') + "' size=10 value='" + (pcPriceData.budget || '') + "' placeholder='Pris ej sat' /></td>";
                  html += "<td><label style='font-size:12px;'>Vejl. udsalgspris:</label><br><input class='pc_price' pc-data='" + (pcPriceData.price || '') + "' type='text' size=10 value='" + (pcPriceData.price || '') + "' placeholder='Pris ej sat' /></td>";
                  html += "<td></td>"; // Empty cell for alignment
                  html += "</tr>";
              }
              $("#ptShopPrice").append(html);



          });

    }
    self.initImportPriceFromPim = () => {

       $("#ptShopPrice").before("<button id='pimPrices'>adsfasdf</button>");
        $("#pimPrices").unbind('click').click( async function () {
            self.loadPriceFromPim();
        })

    }
    self.loadPriceFromPim  = () => {
        
        $.post("index.php?rt=ptAdmin/loadPriceFromPim",{id:_shopId,lang:mapCodeToLanguage(self.lang)}, function(res, status) {
            if(res.status == 0) {
                alert("Der er opstået en fejl")
                return;
            }


        }, "json");


    }
    self.savePC = () => {
        return new Promise(function(resolve, reject) {
            $(".pc-price-fields" ).each( async function( index ) {

                let $this = $(this); // Cache elementet for bedre performance
                let id = $(this).attr("item-data");
                let fields = ["pc_special", "pc_budget", "pc_price"];

                let pcData = fields.reduce((acc, field) => {
                    let $field = $this.find("." + field);
                    acc[field.replace("pc_", "")] = $field.val() || ""; // Fjerner "pc_" prefix
                    return acc;
                }, {});

                let doUpdate = fields.some(field => {
                    let $field = $this.find("." + field);
                    return $field.attr("pc-data") !== $field.val() ;
                });

                if (doUpdate) {
                    console.log("Der er ændringer:", pcData);
                    await self.doSavePc(id,pcData);
                    resolve();
                } else {
                    resolve();
                }


            })
        })
    }
    self.doSavePc = (id,data) => {
        return new Promise(function(resolve, reject) {
            const priceFieldName = "prices_"+mapCodeToLanguage(self.lang);
            $.post("index.php?rt=presentationGroup/update", {group_id: id,[priceFieldName]:JSON.stringify(data)}, function(returData, status){
                if(returData.status==0){ alert(returData.message);  }
                resolve()
            },"json")
        })
    }

    self.save = () => {
       return new Promise(function(resolve, reject) {
         let returnI = 1;

        $(".present-item" ).each( async function( index ) {
           let pt_special_no = "", pt_pris_no = "", pt_budget_no = "",pt_special_no_show = "", pt_pris_no_show = "", pt_budget_no_show = "";
           let doUpdate = false;
           let presentId = $( this ).attr("item-data");

           pt_special_no =  $( this ).find($(".pt_special_no")).attr("cv-data")
           pt_pris_no    =  $( this ).find($(".pt_pris_no")).attr("cv-data")
           pt_budget_no  =  $( this ).find($(".pt_budget_no")).attr("cv-data")
           pt_special_no_show =   $( this ).find($(".pt_special_no")).attr("show-data")
           pt_pris_no_show    =   $( this ).find($(".pt_pris_no")).attr("show-data")
           pt_budget_no_show  =   $( this ).find($(".pt_budget_no")).attr("show-data")

           if( $( this ).find($(".pt_special_no")).val() !=  $( this ).find($(".pt_special_no")).attr("cv-data") ){
               doUpdate = true;
               pt_special_no =  $( this ).find($(".pt_special_no")).val();
           }
           if($( this ).find($(".pt_pris_no")).val() != $( this ).find($(".pt_pris_no")).attr("cv-data")){
               doUpdate = true;
               pt_pris_no =  $( this ).find($(".pt_pris_no")).val();
           }

           if($( this ).find($(".pt_budget_no")).val() != $( this ).find($(".pt_budget_no")).attr("cv-data")){
               doUpdate = true;
               pt_budget_no = $( this ).find($(".pt_budget_no")).val();
           }

               if(pt_special_no == ""){
                 pt_special_no_show = "false"
               } else {
                 pt_special_no_show = "true"
               }
               if(pt_pris_no == ""){
                 pt_pris_no_show = "false"
               } else {
                 pt_pris_no_show = "true"
               }
               if(pt_budget_no == ""){
                 pt_budget_no_show = "false"
               } else {
                 pt_budget_no_show = "true"
               }
           
           if(doUpdate == true){
                let show = $("#use_custon_price:checked").length > 0 ? "1":"none";
                let obj = {
                    "pris":pt_pris_no ,
                    "vis_pris":pt_pris_no_show,
                    "budget":pt_budget_no,
                    "vis_budget":pt_budget_no_show,
                    "special":pt_special_no,
                    "vis_special":pt_special_no_show
            }
//             {"pris":"640","vis_pris":"true","budget":"1.398","vis_budget":"true","special":"","vis_special":"false"}
            await self.doSave(presentId,obj);
           }
           if($(".present-item" ).length > returnI){
             resolve();
           }
        });
     })
    }
    self.doSave = (id,data) => {
              return new Promise(function(resolve, reject) {
               $.post("index.php?rt=ptAdmin/savePresentPrice",{id:id,pt_price:data,lang:self.lang}, function(res, status) {
                    if(res.status == 0) {   }
                    else { resolve(res) }
                }, "json");
        })
    }

})
function mapCodeToLanguage(code) {
    const mapping = {
        1: "da",  // Dansk
        4: "no",  // Norsk
        5: "sv"   // Svensk
    };

    return mapping[code] || "ukendt"; // Returnerer "ukendt", hvis koden ikke findes
}
