class ContributionMargin {
    constructor() {
        this.shopId = 0;
        this.localisation = 1;
        this.calculationData = null;
        this.showSamDetails = false;
        this.basePath = '';
    }

    init(shopId, localisation) {
        console.log('🚀 Initializing ContributionMargin with:', {shopId, localisation});

        this.shopId = shopId;
        this.localisation = localisation;
        this.basePath = window.location.origin + window.location.pathname;

        console.log('📍 Base path set to:', this.basePath);
        console.log('🏪 Shop ID set to:', this.shopId);

        this.bindEvents();

        // Add small delay to ensure page is fully loaded
        setTimeout(() => {
            console.log('⏰ Starting shop data load...');
            this.loadShopData();
        }, 100);
    }

    bindEvents() {
        $('#exportBtn').on('click', () => {
            this.exportToExcel();
        });

        // Search functionality
        $('#searchInput').on('input', () => {
            this.renderCalculationTable();
        });

        $('#clearSearchBtn').on('click', () => {
            $('#searchInput').val('');
            this.renderCalculationTable();
        });

        // SAM detail click handler (for modal only)
        $(document).on('click', '.sam-item', (e) => {
            const itemNo = $(e.currentTarget).data('item-no');
            this.showSamComponents(itemNo);
        });
    }

    loadShopData() {
        const url = `${this.basePath}?rt=unit/valgshop/contributionmargin/getShopData&shop_id=${this.shopId}`;

        console.log('🔄 Loading shop data...');
        console.log('📡 URL:', url);
        console.log('🏪 Shop ID:', this.shopId);

        $.get(url)
            .done((response) => {
                console.log('✅ Shop data response received:', response);
                console.log('🔍 Response type:', typeof response);

                // Parse response if it's a string
                let parsedResponse = response;
                if (typeof response === 'string') {
                    try {
                        parsedResponse = JSON.parse(response);
                        console.log('🔄 Parsed JSON response:', parsedResponse);
                    } catch (e) {
                        console.error('❌ JSON parse error:', e);
                        this.showError('Fejl ved parsing af shop data: Ugyldig JSON format');
                        return;
                    }
                }

                // Check if parsed response is valid
                if (parsedResponse && typeof parsedResponse === 'object') {
                    if (parsedResponse.status === 1) {
                        console.log('🎉 Shop data loaded successfully:', parsedResponse.data);
                        this.displayShopInfo(parsedResponse.data);
                    } else {
                        const errorMsg = parsedResponse.error || 'Ukendt fejl fra server';
                        console.error('❌ Shop data error:', errorMsg);
                        this.showError('Fejl ved indlæsning af shop data: ' + errorMsg);
                    }
                } else {
                    console.error('❌ Invalid response format after parsing:', parsedResponse);
                    this.showError('Fejl ved indlæsning af shop data: Ugyldig response format');
                }
            })
            .fail((xhr, status, error) => {
                console.error('💥 Shop data network error:', {
                    status: xhr.status,
                    statusText: xhr.statusText,
                    responseText: xhr.responseText,
                    error: error,
                    url: url
                });

                let errorMessage = 'Network fejl: ' + error;

                // Try to parse error response
                if (xhr.responseText) {
                    try {
                        const parsed = JSON.parse(xhr.responseText);
                        if (parsed.error) {
                            errorMessage = parsed.error;
                        }
                    } catch (e) {
                        // If not JSON, show part of response
                        errorMessage += '\nResponse: ' + xhr.responseText.substring(0, 200);
                    }
                }

                this.showError('Fejl ved indlæsning af shop data: ' + errorMessage);
            });
    }

    displayShopInfo(shopData) {
        $('#shopName').text(shopData.name || 'Ikke fundet');
        $('#employeeCount').text(shopData.user_count || '0');
        $('#shopBudget').text(this.formatCurrency(shopData.budget || 0) + ' pr. gave');

        const languageNames = {
            1: 'Dansk',
            4: 'Norsk',
            5: 'Svensk'
        };
        $('#shopLanguage').text(languageNames[shopData.language_id] || 'Ukendt');

        $('#shopInfoCard').show();

        // Automatisk start beregning efter shop data er loadet
        console.log('🚀 Starting automatic calculation...');
        this.calculateContributionMargin();
    }

    calculateContributionMargin() {
        console.log('🔄 Starting contribution margin calculation...');

        $.post(`${this.basePath}?rt=unit/valgshop/contributionmargin/calculateContributionMargin`, {
            shop_id: this.shopId
        })
            .done((response) => {
                // DEBUG: Log hele response
                console.log('API Success Response:', response);
                console.log('🔍 Response type:', typeof response);

                // Parse response if it's a string
                let parsedResponse = response;
                if (typeof response === 'string') {
                    try {
                        parsedResponse = JSON.parse(response);
                        console.log('🔄 Parsed calculation response:', parsedResponse);
                    } catch (e) {
                        console.error('❌ JSON parse error in calculation:', e);
                        this.showError('Fejl ved parsing af beregning: Ugyldig JSON format');
                        return;
                    }
                }

                // Check response status correctly
                if (parsedResponse && parsedResponse.status && parsedResponse.status === 1) {
                    // Success - we have valid data
                    if (parsedResponse.data && parsedResponse.data.calculations) {
                        this.calculationData = parsedResponse.data;
                        this.displayResults();

                        // Check for missing data warnings
                        this.checkMissingData(parsedResponse.data.calculations);

                        console.log('✅ Calculation completed successfully!');
                    } else {
                        this.showError('Fejl ved beregning: Ingen beregningsdata modtaget');
                    }
                } else {
                    // Error handling
                    let errorMessage = 'Ukendt fejl';

                    if (parsedResponse && parsedResponse.error) {
                        errorMessage = parsedResponse.error;
                    }

                    // Vis debug info hvis tilgængelig
                    if (parsedResponse && parsedResponse.debug_info) {
                        errorMessage += '\n\nDebug info:';
                        errorMessage += '\nShop ID: ' + parsedResponse.debug_info.shop_id;
                        errorMessage += '\nFil: ' + parsedResponse.debug_info.file;
                        errorMessage += '\nLinje: ' + parsedResponse.debug_info.line;
                        console.error('Debug Info:', parsedResponse.debug_info);
                    }

                    console.error('API Error Response:', parsedResponse);
                    this.showError('Fejl ved beregning: ' + errorMessage);
                }
            })
            .fail((xhr, status, error) => {
                // Network/HTTP errors
                console.error('Network Error:', {
                    status: xhr.status,
                    statusText: xhr.statusText,
                    responseText: xhr.responseText,
                    error: error
                });

                let errorMessage = 'Network fejl: ' + error;
                if (xhr.responseText) {
                    try {
                        const parsed = JSON.parse(xhr.responseText);
                        if (parsed.error) {
                            errorMessage = parsed.error;
                        }
                    } catch (e) {
                        errorMessage += '\nResponse: ' + xhr.responseText.substring(0, 200);
                    }
                }

                this.showError('Fejl ved beregning: ' + errorMessage);
            });
    }

    displayResults() {
        const summary = this.calculationData.summary;

        // Update total budget display
        $('#totalBudget').text(this.formatCurrency(summary.total_budget || 0) + ' kr.');

        // Update summary
        $('#totalReservations').text(summary.total_reservations);
        $('#adjustedTotal').text(Math.round(summary.total_reservations * summary.adjustment_ratio));
        $('#adjustmentRatio').text((summary.adjustment_ratio * 100).toFixed(1) + '%');

        $('#totalCost').text(this.formatCurrency(summary.total_cost));
        $('#totalSaleValue').text(this.formatCurrency(summary.total_sale_value));
        $('#budgetVsSale').text(this.formatCurrency(summary.budget_vs_sale_value));

        $('#totalContribution').text(this.formatCurrency(summary.total_contribution_margin));
        $('#totalMarginPercent').text(summary.total_margin_percent.toFixed(1));

        // Data quality indicators
        $('#missingDataCount').text(`${summary.items_with_missing_data || 0} / ${summary.total_calculation_items || 0}`);
        $('#unreliableCostCount').text(`${summary.unreliable_cost_items || 0} / ${summary.total_calculation_items || 0}`);

        // Color code data quality
        if (summary.items_with_missing_data > 0) {
            $('#missingDataCount').addClass('text-warning');
        }
        if (summary.unreliable_cost_items > 0) {
            $('#unreliableCostCount').addClass('text-warning');
        }

        // Show summary and table
        $('#summaryBox').show();
        $('#calculationsCard').show();

        // Render calculation table (always show SAM details)
        this.renderCalculationTable();
    }

    updateStatusIndicator(summary) {
        const marginPercent = summary.total_margin_percent;
        const budgetDiff = summary.budget_vs_sale_value;
        const totalBudget = summary.total_budget || 0;

        let statusClass = 'bg-secondary';
        let statusText = 'Ikke beregnet';

        if (marginPercent > 30 && budgetDiff >= 0) {
            statusClass = 'bg-success';
            statusText = 'Meget god';
        } else if (marginPercent > 20 && budgetDiff >= -totalBudget * 0.1) {
            statusClass = 'bg-success';
            statusText = 'God';
        } else if (marginPercent > 10) {
            statusClass = 'bg-warning';
            statusText = 'Acceptabel';
        } else {
            statusClass = 'bg-danger';
            statusText = 'Problematisk';
        }

        $('#statusIndicator').html(`<span class="badge ${statusClass}">${statusText}</span>`);
    }


    renderCalculationTable() {
        const tbody = $('#calculationTableBody');
        tbody.empty();

        // Filter data based on search if needed
        let filteredData = this.calculationData.calculations;
        const searchTerm = $('#searchInput').val();
        if (searchTerm && searchTerm.length > 0) {
            filteredData = this.calculationData.calculations.filter(item => {
                return item.item_no.toLowerCase().includes(searchTerm.toLowerCase()) ||
                    item.model_name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                    item.present_id.toString().includes(searchTerm);
            });
        }

        // Group data by present_id
        const groupedData = this.groupByPresentId(filteredData);

        let totalRowsRendered = 0;

        // Render groups
        Object.keys(groupedData).forEach(presentId => {
            const group = groupedData[presentId];

            // Only show group header if there are multiple items in the group
            if (group.length > 1) {
                const groupHeader = this.createGroupHeader(presentId, group);
                tbody.append(groupHeader);
            }

            // Render items in the group
            group.forEach((item, index) => {
                const row = this.createCalculationRow(item, group.length > 1, index === group.length - 1);
                tbody.append(row);
                totalRowsRendered++;
            });
        });

        // Update row count
        $('#rowCount').text(`${totalRowsRendered} / ${this.calculationData.calculations.length} varer`);
    }
    createGroupHeader(presentId, groupItems) {
        // Calculate group totals
        const groupTotalCost = groupItems.reduce((sum, item) => sum + (item.total_cost || 0), 0);
        const groupTotalSale = groupItems.reduce((sum, item) => sum + (item.total_sale_value || 0), 0);
        const groupTotalQuantity = groupItems.reduce((sum, item) => sum + (item.adjusted_quantity || 0), 0);
        const groupContribution = groupTotalSale - groupTotalCost;
        const groupMarginPercent = groupTotalSale > 0 ? (groupContribution / groupTotalSale) * 100 : 0;

        const marginClass = groupMarginPercent > 20 ? 'positive' : groupMarginPercent > 10 ? 'neutral' : 'negative';

        return $(`
        <tr class="group-header">
            <td colspan="5" class="group-title">
                <strong>Present ID: ${presentId}</strong> 
                <span class="badge bg-secondary ms-2">${groupItems.length} varianter</span>
            </td>
            <td><strong>${groupTotalQuantity}</strong></td>
            <td></td>
            <td></td>
            <td><strong>${this.formatCurrency(groupTotalCost)}</strong></td>
            <td><strong>${this.formatCurrency(groupTotalSale)}</strong></td>
            <td class="${marginClass}"><strong>${this.formatCurrency(groupContribution)}</strong></td>
            <td class="${marginClass}"><strong>${groupMarginPercent.toFixed(1)}%</strong></td>
        </tr>
    `);
    }
    groupByPresentId(data) {
        const grouped = {};

        data.forEach(item => {
            const presentId = item.present_id || 'ikke_angivet';

            if (!grouped[presentId]) {
                grouped[presentId] = [];
            }

            grouped[presentId].push(item);
        });

        // Sort groups by present_id, and sort items within each group by model_name
        const sortedGrouped = {};
        Object.keys(grouped).sort((a, b) => {
            // Put 'ikke_angivet' at the end
            if (a === 'ikke_angivet') return 1;
            if (b === 'ikke_angivet') return -1;
            return a.localeCompare(b);
        }).forEach(key => {
            // Sort items within group by model_name
            grouped[key].sort((a, b) => (a.model_name || '').localeCompare(b.model_name || ''));
            sortedGrouped[key] = grouped[key];
        });

        return sortedGrouped;
    }

    createCalculationRow(item, isGrouped = false, isLastInGroup = false) {
        const marginClass = item.margin_percent > 20 ? 'positive' : item.margin_percent > 10 ? 'neutral' : 'negative';
        const samClass = item.item_type === 'SAM' ? 'sam-item' : '';

        // Check for missing data
        const missingCost = !item.unit_cost || item.unit_cost === 0;
        const missingItemNo = !item.item_no || item.item_no.trim() === '';
        const missingName = !item.model_name || item.model_name.trim() === '';

        // Style for missing data
        const missingDataClass = (missingCost || missingItemNo || missingName) ? 'table-warning' : '';

        // Add grouping classes
        const groupingClass = isGrouped ? 'grouped-item' : '';
        const lastInGroupClass = isLastInGroup ? 'last-in-group' : '';

        // Display values with missing data indicators
        const presentIdDisplay = item.present_id && item.present_id !== '' ? item.present_id : '-';

        const itemNoDisplay = missingItemNo ?
            '<span class="text-muted">Mangler varenr</span>' :
            item.item_no;

        const nameDisplay = missingName ?
            '<span class="text-muted">Mangler navn</span>' :
            item.model_name;

        const costDisplay = missingCost ?
            '<span class="text-danger">Mangler kostpris</span>' :
            this.formatCurrency(item.unit_cost);

        // Contribution calculation might be unreliable if cost is missing
        const contributionDisplay = missingCost ?
            '<span class="text-muted">Usikker</span>' :
            this.formatCurrency(item.contribution_margin);

        const marginPercentDisplay = missingCost ?
            '<span class="text-muted">Usikker</span>' :
            item.margin_percent.toFixed(1) + '%';

        // Only show warning badge if there are data issues
        let warningBadge = '';
        if (missingCost || missingItemNo || missingName) {
            warningBadge = '<span class="badge bg-warning ms-1">⚠️</span>';
        }

        // Adjust present_id display for grouped items
        const presentIdCell = isGrouped ? `<td class="grouped-present-id">└ ${presentIdDisplay}</td>` : `<td>${presentIdDisplay}</td>`;

        return $(`
        <tr class="calculation-row ${samClass} ${missingDataClass} ${groupingClass} ${lastInGroupClass}" data-item-no="${item.item_no || ''}" data-present-id="${item.present_id || ''}" title="${item.item_type === 'SAM' ? 'Klik for at se SAM komponenter' : ''}">
            ${presentIdCell}
            <td>${itemNoDisplay}</td>
            <td>${nameDisplay}${warningBadge}</td>
            <td>${item.item_type}</td>
            <td>${item.original_quantity}</td>
            <td>${item.adjusted_quantity}</td>
            <td>${costDisplay}</td>
            <td>${this.formatCurrency(item.budget_price || 0)}</td>
            <td>${this.formatCurrency(item.total_cost)}</td>
            <td>${this.formatCurrency(item.total_sale_value)}</td>
            <td class="${marginClass}">${contributionDisplay}</td>
            <td class="${marginClass}">${marginPercentDisplay}</td>
        </tr>
    `);
    }
    createCalculationRow(item, isGrouped = false, isLastInGroup = false) {
        const marginClass = item.margin_percent > 20 ? 'positive' : item.margin_percent > 10 ? 'neutral' : 'negative';
        const samClass = item.item_type === 'SAM' ? 'sam-item' : '';

        // Check for missing data
        const missingCost = !item.unit_cost || item.unit_cost === 0;
        const missingItemNo = !item.item_no || item.item_no.trim() === '';
        const missingName = !item.model_name || item.model_name.trim() === '';

        // Style for missing data
        const missingDataClass = (missingCost || missingItemNo || missingName) ? 'table-warning' : '';

        // Add grouping classes
        const groupingClass = isGrouped ? 'grouped-item' : '';
        const lastInGroupClass = isLastInGroup ? 'last-in-group' : '';

        // Display values with missing data indicators
        const presentIdDisplay = item.present_id && item.present_id !== '' ? item.present_id : '-';

        const itemNoDisplay = missingItemNo ?
            '<span class="text-muted">Mangler varenr</span>' :
            item.item_no;

        const nameDisplay = missingName ?
            '<span class="text-muted">Mangler navn</span>' :
            item.model_name;

        const costDisplay = missingCost ?
            '<span class="text-danger">Mangler kostpris</span>' :
            this.formatCurrency(item.unit_cost);

        // Contribution calculation might be unreliable if cost is missing
        const contributionDisplay = missingCost ?
            '<span class="text-muted">Usikker</span>' :
            this.formatCurrency(item.contribution_margin);

        const marginPercentDisplay = missingCost ?
            '<span class="text-muted">Usikker</span>' :
            item.margin_percent.toFixed(1) + '%';

        // Only show warning badge if there are data issues
        let warningBadge = '';
        if (missingCost || missingItemNo || missingName) {
            warningBadge = '<span class="badge bg-warning ms-1">⚠️</span>';
        }

        // Adjust present_id display for grouped items
        const presentIdCell = isGrouped ? `<td class="grouped-present-id">└ ${presentIdDisplay}</td>` : `<td>${presentIdDisplay}</td>`;

        return $(`
        <tr class="calculation-row ${samClass} ${missingDataClass} ${groupingClass} ${lastInGroupClass}" data-item-no="${item.item_no || ''}" data-present-id="${item.present_id || ''}" title="${item.item_type === 'SAM' ? 'Klik for at se SAM komponenter' : ''}">
            ${presentIdCell}
            <td>${itemNoDisplay}</td>
            <td>${nameDisplay}${warningBadge}</td>
            <td>${item.item_type}</td>
            <td>${item.original_quantity}</td>
            <td>${item.adjusted_quantity}</td>
            <td>${costDisplay}</td>
            <td>${this.formatCurrency(item.budget_price || 0)}</td>
            <td>${this.formatCurrency(item.total_cost)}</td>
            <td>${this.formatCurrency(item.total_sale_value)}</td>
            <td class="${marginClass}">${contributionDisplay}</td>
            <td class="${marginClass}">${marginPercentDisplay}</td>
        </tr>
    `);
    }

    addSamComponentRows(tbody, itemNo) {
        $.get(`${this.basePath}?rt=unit/valgshop/contributionmargin/getBomComponents&item_no=${itemNo}&language_id=${this.localisation}`)
            .done((response) => {
                if (response.status === 1 && response.data.length > 0) {
                    response.data.forEach(component => {
                        const componentRow = $(`
                            <tr class="sam-component">
                                <td>└ ${component.component_no}</td>
                                <td>${component.nav_description || component.component_description}</td>
                                <td>Komponent</td>
                                <td colspan="2">${component.quantity_per} stk/SAM</td>
                                <td>${this.formatCurrency(component.component_unit_cost || 0)}</td>
                                <td>-</td>
                                <td>${this.formatCurrency((component.component_unit_cost || 0) * (component.quantity_per || 1))}</td>
                                <td>-</td>
                                <td>-</td>
                                <td>-</td>
                            </tr>
                        `);
                        tbody.append(componentRow);
                    });
                }
            })
            .fail((error) => {
                console.error('Fejl ved hentning af BOM komponenter:', error);
            });
    }

    showSamComponents(itemNo) {
        $.get(`${this.basePath}?rt=unit/valgshop/contributionmargin/getBomComponents&item_no=${itemNo}&language_id=${this.localisation}`)
            .done((response) => {
                if (response.status === 1) {
                    $('#samItemName').text(`SAM: ${itemNo}`);

                    const tbody = $('#samComponentsBody');
                    tbody.empty();

                    let totalCost = 0;

                    response.data.forEach(component => {
                        const componentCost = (component.component_unit_cost || 0) * (component.quantity_per || 1);
                        totalCost += componentCost;

                        const row = $(`
                            <tr>
                                <td>${component.component_no}</td>
                                <td>${component.nav_description || component.component_description}</td>
                                <td>${component.quantity_per}</td>
                                <td>${this.formatCurrency(component.component_unit_cost || 0)}</td>
                                <td>${this.formatCurrency(componentCost)}</td>
                            </tr>
                        `);
                        tbody.append(row);
                    });

                    // Add total row
                    const totalRow = $(`
                        <tr class="table-warning">
                            <td colspan="4"><strong>Total kostpris pr. SAM:</strong></td>
                            <td><strong>${this.formatCurrency(totalCost)}</strong></td>
                        </tr>
                    `);
                    tbody.append(totalRow);

                    $('#samDetailsModal').modal('show');
                }
            })
            .fail((error) => {
                this.showError('Fejl ved hentning af SAM komponenter: ' + error.message);
            });
    }

    exportToExcel() {
        if (!this.calculationData) {
            this.showError('Ingen data at exportere. Beregn først dækningsbidraget.');
            return;
        }

        // Create CSV content with proper encoding
        let csvContent = "\uFEFF"; // BOM for UTF-8

        // Headers
        csvContent += "Present ID;Varenr;Beskrivelse;Type;Opr. Antal;Just. Antal;Kostpris/stk;Budget pris/stk;Total Kost;Total Salg;DB;DB%\n";

        // Data rows - fix number formatting
        this.calculationData.calculations.forEach(item => {
            // Ensure proper number formatting for CSV
            const totalCost = this.formatNumberForCSV(item.total_cost);
            const totalSaleValue = this.formatNumberForCSV(item.total_sale_value);
            const contributionMargin = this.formatNumberForCSV(item.contribution_margin);
            const unitCost = this.formatNumberForCSV(item.unit_cost || 0);
            const budgetPrice = this.formatNumberForCSV(item.budget_price || 0);
            const marginPercent = (item.margin_percent || 0).toFixed(1).replace('.', ',');
            const presentId = item.present_id || '';

            csvContent += `${presentId};${item.item_no || ''};${item.model_name || ''};${item.item_type || ''};${item.original_quantity || 0};${item.adjusted_quantity || 0};${unitCost};${budgetPrice};${totalCost};${totalSaleValue};${contributionMargin};${marginPercent}\n`;
        });

        // Summary
        const summary = this.calculationData.summary;
        csvContent += "\n";
        csvContent += "SAMMENDRAG\n";
        csvContent += `Total medarbejdere;${summary.total_employees}\n`;
        csvContent += `Total reservationer;${summary.total_reservations}\n`;
        csvContent += `Justeringsratio;${(summary.adjustment_ratio * 100).toFixed(1).replace('.', ',')}%\n`;
        csvContent += `Budget pr. gave;${this.formatNumberForCSV(summary.budget)}\n`;
        csvContent += `Total budget;${this.formatNumberForCSV(summary.total_budget)}\n`;
        csvContent += `Total kostpris;${this.formatNumberForCSV(summary.total_cost)}\n`;
        csvContent += `Total salgspris;${this.formatNumberForCSV(summary.total_sale_value)}\n`;
        csvContent += `Total daekningsbidrag;${this.formatNumberForCSV(summary.total_contribution_margin)}\n`;
        csvContent += `Daekningsbidrag %;${summary.total_margin_percent.toFixed(1).replace('.', ',')}%\n`;

        // Download with proper encoding
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement("a");

        if (link.download !== undefined) {
            const url = URL.createObjectURL(blob);
            link.setAttribute("href", url);
            link.setAttribute("download", `daekningsbidrag_shop_${this.shopId}_${new Date().toISOString().slice(0,10)}.csv`);
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    }

    formatNumberForCSV(number) {
        // Format number for CSV (use comma as decimal separator for Danish locale)
        const formatted = (number || 0).toFixed(2).replace('.', ',');
        return formatted;
    }

    formatCurrency(amount) {
        return new Intl.NumberFormat('da-DK', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(amount);
    }

    setLoading(isLoading) {
        if (isLoading) {
            $('#calculateBtn').prop('disabled', true);
            $('.loading').show();
        } else {
            $('#calculateBtn').prop('disabled', false);
            $('.loading').hide();
        }
    }

    showError(message) {
        // Create or update error alert
        let alertDiv = $('#errorAlert');
        if (alertDiv.length === 0) {
            alertDiv = $(`
                <div id="errorAlert" class="alert alert-danger alert-dismissible fade show" role="alert">
                    <span id="errorMessage"></span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `);
            $('#main-container .row .col-12').prepend(alertDiv);
        }

        $('#errorMessage').text(message);
        alertDiv.show();

        // Auto-hide after 5 seconds
        setTimeout(() => {
            alertDiv.fadeOut();
        }, 5000);
    }

    showWarning(message) {
        // Create or update warning alert
        let alertDiv = $('#warningAlert');
        if (alertDiv.length === 0) {
            alertDiv = $(`
                <div id="warningAlert" class="alert alert-warning alert-dismissible fade show" role="alert">
                    <span id="warningMessage"></span>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `);
            $('#main-container .row .col-12').prepend(alertDiv);
        }

        $('#warningMessage').text(message);
        alertDiv.show();

        // Auto-hide after 8 seconds
        setTimeout(() => {
            alertDiv.fadeOut();
        }, 8000);
    }

    checkMissingData(calculations) {
        let missingDataItems = [];
        let totalMissingCost = 0;

        calculations.forEach(item => {
            let missingFields = [];

            // Check for missing or zero unit_cost
            if (!item.unit_cost || item.unit_cost === 0) {
                missingFields.push('kostpris');
            }

            // Check for missing item_no
            if (!item.item_no || item.item_no.trim() === '') {
                missingFields.push('varenummer');
            }

            // Check for missing model_name
            if (!item.model_name || item.model_name.trim() === '') {
                missingFields.push('produktnavn');
            }

            if (missingFields.length > 0) {
                missingDataItems.push({
                    item_no: item.item_no || 'Ukendt',
                    model_name: item.model_name || 'Ukendt produkt',
                    missing_fields: missingFields,
                    quantity: item.adjusted_quantity || 0
                });

                // Add to missing cost (assume worst case if no cost data)
                if (!item.unit_cost || item.unit_cost === 0) {
                    totalMissingCost += (item.adjusted_quantity || 0) * (item.budget_price || 0);
                }
            }
        });

        if (missingDataItems.length > 0) {
            let warningMessage = `⚠️ ${missingDataItems.length} varer mangler data:\n\n`;

            missingDataItems.slice(0, 5).forEach(item => { // Show max 5 items
                warningMessage += `• ${item.item_no}: ${item.missing_fields.join(', ')}\n`;
            });

            if (missingDataItems.length > 5) {
                warningMessage += `... og ${missingDataItems.length - 5} andre\n`;
            }

            if (totalMissingCost > 0) {
                warningMessage += `\nPotentiel påvirkning: ${this.formatCurrency(totalMissingCost)} kr i usikker kostpris.`;
            }

            warningMessage += '\n\nBeregningen fortsætter med tilgængelige data.';

            this.showWarning(warningMessage);

            // Log details to console
            console.warn('Missing data details:', missingDataItems);
        }
    }

    // DEBUG: Test funktion til console
    debugCalculation() {
        console.log('Testing calculation for shop:', this.shopId);

        $.post(`${this.basePath}?rt=unit/valgshop/contributionmargin/calculateContributionMargin`, {
            shop_id: this.shopId
        })
            .done((response) => {
                console.log('SUCCESS Response:', response);
            })
            .fail((xhr, status, error) => {
                console.log('FAIL Response:', {
                    status: xhr.status,
                    responseText: xhr.responseText,
                    error: error
                });
            });
    }

    // DEBUG: Test shop data loading
    debugShopData() {
        console.log('Testing shop data loading for shop:', this.shopId);

        $.get(`${this.basePath}?rt=unit/valgshop/contributionmargin/getShopData&shop_id=${this.shopId}`)
            .done((response) => {
                console.log('Shop data SUCCESS:', response);
            })
            .fail((xhr, status, error) => {
                console.log('Shop data FAIL:', {
                    status: xhr.status,
                    statusText: xhr.statusText,
                    responseText: xhr.responseText,
                    error: error
                });
            });
    }
}