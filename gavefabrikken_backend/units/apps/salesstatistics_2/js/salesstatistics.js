class salesStatisticsUnit {
    constructor() {
        this.charts = {};
        this.currentData = null;
        this.tableSort = {
            column: null,
            direction: 'asc'
        };
    }

    run(assetsPath, servicePath) {
        this.assetsPath = assetsPath;
        this.servicePath = servicePath;
        
        this.initEventListeners();
        this.loadData();
    }

    initEventListeners() {
        const self = this;
        
        $('#loadDataBtn').on('click', function() {
            self.loadData();
        });
        
        $('#exportCSVBtn').on('click', function() {
            self.exportCSV();
        });
        
        $('#databaseSelect').on('change', function() {
            self.updateDatesForDatabase();
            $('#loadDataBtn').addClass('btn-warning').removeClass('btn-primary');
        });
        
        $('#startDate, #endDate').on('change', function() {
            $('#loadDataBtn').addClass('btn-warning').removeClass('btn-primary');
        });
        
        $('#toggleDebugBtn').on('click', function() {
            if ($('#debugCard').is(':visible')) {
                $('#debugCard').hide();
                $(this).removeClass('btn-secondary').addClass('btn-outline-secondary');
            } else {
                $('#debugCard').show();
                $(this).removeClass('btn-outline-secondary').addClass('btn-secondary');
            }
        });
        
        // Concept bar chart filters
        $('input[name="conceptBarMode"], #conceptBarCountryFilter').on('change', function() {
            if (self.currentData) {
                self.updateConceptBarChart();
            }
        });
        
        // Monthly chart filters
        $('#monthlyRevenueCountryFilter').on('change', function() {
            if (self.currentData) {
                self.updateMonthlyRevenueChart();
            }
        });
        
        $('#monthlySalesCountryFilter').on('change', function() {
            if (self.currentData) {
                self.updateMonthlySalesChart();
            }
        });
        
        // Data table filters and sorting
        $('#dataTableCountryFilter').on('change', function() {
            if (self.currentData) {
                self.updateDataTable(self.currentData.rawData);
            }
        });
        
        // Table column sorting
        $('#dataTable th.sortable').on('click', function() {
            if (self.currentData) {
                const column = $(this).data('sort');
                
                // Toggle direction if same column, otherwise set to asc
                if (self.tableSort.column === column) {
                    self.tableSort.direction = self.tableSort.direction === 'asc' ? 'desc' : 'asc';
                } else {
                    self.tableSort.column = column;
                    self.tableSort.direction = 'asc';
                }
                
                // Update sort indicators
                $('#dataTable th.sortable').removeClass('sort-asc sort-desc');
                $(this).addClass('sort-' + self.tableSort.direction);
                
                self.updateDataTable(self.currentData.rawData);
            }
        });
    }

    loadData() {
        const self = this;
        const database = $('#databaseSelect').val();
        const startDate = $('#startDate').val();
        const endDate = $('#endDate').val();
        
        // Show simple overlay instead of modal
        $('#loadingOverlay').css('display', 'flex');
        $('#loadDataBtn').removeClass('btn-warning').addClass('btn-primary');
        
        $.ajax({
            url: self.servicePath + 'getSalesData/' + database + '/' + startDate + '/' + endDate,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                console.log('Response received:', response);
                $('#loadingOverlay').hide();
                
                if (response && response.success) {
                    self.currentData = response;
                    self.updateSummaryCards(response);
                    self.updateCharts(response.data);
                    self.updateDataTable(response.rawData);
                    self.showDebugInfo(response);
                } else {
                    const errorMsg = response && response.error ? response.error : 'Ukendt fejl';
                    self.showAlert('Fejl ved hentning af data: ' + errorMsg, 'danger');
                    self.showDebugInfo(response);
                }
            },
            error: function(xhr, status, error) {
                console.error('Ajax error:', status, error, xhr.responseText);
                $('#loadingOverlay').hide();
                self.showAlert('Der opstod en fejl ved hentning af data: ' + error, 'danger');
            }
        });
    }

    updateSummaryCards(response) {
        const formatter = new Intl.NumberFormat('da-DK', {
            style: 'currency',
            currency: 'DKK'
        });
        
        // Calculate revenue per country
        const countryRevenue = {
            'Danmark': 0,
            'Norge': 0,
            'Sverige': 0
        };
        
        if (response.rawData) {
            response.rawData.forEach(function(row) {
                const revenue = parseFloat(row.total_omsaetning) || 0;
                if (countryRevenue.hasOwnProperty(row.land)) {
                    countryRevenue[row.land] += revenue;
                }
            });
        }
        
        // Update country-specific revenue boxes
        $('#danmarkRevenue').text(formatter.format(countryRevenue.Danmark));
        $('#norgeRevenue').text(formatter.format(countryRevenue.Norge));
        $('#sverigeRevenue').text(formatter.format(countryRevenue.Sverige));
        
        // Update total revenue and other cards
        $('#totalRevenue').text(formatter.format(response.data.totalRevenue));
        $('#totalSales').text(response.data.totalSales.toLocaleString('da-DK'));
        $('#activeDatabase').text(response.database === 'gavefabrikken2024' ? '2024' : '2025');
        
        const startDate = new Date(response.period.start);
        const endDate = new Date(response.period.end);
        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        $('#activePeriod').html(
            startDate.toLocaleDateString('da-DK', options) + '<br>' + 
            endDate.toLocaleDateString('da-DK', options)
        );
    }

    updateCharts(data) {
        this.updateMonthlyRevenueChart();
        this.updateMonthlySalesChart();
        this.createCountryChart(data.countryRevenue);
        this.updateConceptBarChart();
    }

    updateMonthlyRevenueChart() {
        const ctx = document.getElementById('monthlyRevenueChart').getContext('2d');
        
        if (this.charts.monthlyRevenue) {
            this.charts.monthlyRevenue.destroy();
        }
        
        if (!this.currentData || !this.currentData.rawData) return;
        
        const countryFilter = $('#monthlyRevenueCountryFilter').val();
        const monthlyData = this.processMonthlyData(this.currentData.rawData, countryFilter, true);
        
        this.charts.monthlyRevenue = new Chart(ctx, {
            type: 'line',
            data: {
                labels: Object.keys(monthlyData),
                datasets: [{
                    label: 'Omsætning (kr)',
                    data: Object.values(monthlyData),
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.1)',
                    tension: 0.1,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Kr. ' + context.parsed.y.toLocaleString('da-DK', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                });
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString('da-DK');
                            }
                        }
                    }
                }
            }
        });
    }

    updateMonthlySalesChart() {
        const ctx = document.getElementById('monthlySalesChart').getContext('2d');
        
        if (this.charts.monthlySales) {
            this.charts.monthlySales.destroy();
        }
        
        if (!this.currentData || !this.currentData.rawData) return;
        
        const countryFilter = $('#monthlySalesCountryFilter').val();
        const monthlyData = this.processMonthlyData(this.currentData.rawData, countryFilter, false);
        
        this.charts.monthlySales = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: Object.keys(monthlyData),
                datasets: [{
                    label: 'Antal solgt',
                    data: Object.values(monthlyData),
                    backgroundColor: 'rgba(54, 162, 235, 0.8)',
                    borderColor: 'rgb(54, 162, 235)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Antal: ' + context.parsed.y.toLocaleString('da-DK');
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString('da-DK');
                            }
                        }
                    }
                }
            }
        });
    }

    createCountryChart(countryData) {
        const ctx = document.getElementById('countryChart').getContext('2d');
        
        if (this.charts.country) {
            this.charts.country.destroy();
        }
        
        const colors = [
            'rgba(255, 99, 132, 0.8)',
            'rgba(54, 162, 235, 0.8)',
            'rgba(255, 206, 86, 0.8)',
            'rgba(75, 192, 192, 0.8)',
            'rgba(153, 102, 255, 0.8)'
        ];
        
        this.charts.country = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: Object.keys(countryData),
                datasets: [{
                    data: Object.values(countryData),
                    backgroundColor: colors,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                return label + ': Kr. ' + value.toLocaleString('da-DK', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                }) + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });
    }

    updateConceptBarChart() {
        const ctx = document.getElementById('conceptBarChart').getContext('2d');
        
        if (this.charts.conceptBar) {
            this.charts.conceptBar.destroy();
        }
        
        if (!this.currentData || !this.currentData.rawData) return;
        
        const showRevenue = $('#conceptBarRevenue').is(':checked');
        const countryFilter = $('#conceptBarCountryFilter').val();
        
        // Process data based on filters
        const processedData = this.processConceptData(this.currentData.rawData, countryFilter, showRevenue);
        
        const labels = Object.keys(processedData);
        const values = Object.values(processedData);
        
        // Calculate dynamic height based on number of items 
        const calculatedHeight = Math.max(600, labels.length * 25 + 100);
        
        // Set canvas height dynamically
        ctx.canvas.style.height = calculatedHeight + 'px';
        
        this.charts.conceptBar = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels.map(label => label.length > 40 ? label.substring(0, 40) + '...' : label),
                datasets: [{
                    label: showRevenue ? 'Omsætning (kr)' : 'Antal kort',
                    data: values,
                    backgroundColor: 'rgba(153, 102, 255, 0.8)',
                    borderColor: 'rgb(153, 102, 255)',
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                if (showRevenue) {
                                    return 'Kr. ' + context.parsed.x.toLocaleString('da-DK', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    });
                                } else {
                                    return 'Antal: ' + context.parsed.x.toLocaleString('da-DK');
                                }
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString('da-DK');
                            }
                        }
                    }
                }
            }
        });
    }

    updateConceptPieChart() {
        const ctx = document.getElementById('conceptPieChart').getContext('2d');
        
        if (this.charts.conceptPie) {
            this.charts.conceptPie.destroy();
        }
        
        if (!this.currentData || !this.currentData.rawData) return;
        
        const showRevenue = $('#conceptPieRevenue').is(':checked');
        const countryFilter = $('#conceptPieCountryFilter').val();
        
        // Process data based on filters
        const processedData = this.processConceptData(this.currentData.rawData, countryFilter, showRevenue);
        
        // Sort and take top 10 for pie chart
        const sortedEntries = Object.entries(processedData).sort((a, b) => b[1] - a[1]);
        const top10 = sortedEntries.slice(0, 10);
        
        const labels = top10.map(entry => entry[0]);
        const values = top10.map(entry => entry[1]);
        
        const colors = [
            'rgba(255, 99, 132, 0.8)',
            'rgba(54, 162, 235, 0.8)',
            'rgba(255, 206, 86, 0.8)',
            'rgba(75, 192, 192, 0.8)',
            'rgba(153, 102, 255, 0.8)',
            'rgba(255, 159, 64, 0.8)',
            'rgba(199, 199, 199, 0.8)',
            'rgba(83, 102, 255, 0.8)',
            'rgba(255, 99, 255, 0.8)',
            'rgba(99, 255, 132, 0.8)'
        ];
        
        this.charts.conceptPie = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: labels.map(label => label.length > 25 ? label.substring(0, 25) + '...' : label),
                datasets: [{
                    data: values,
                    backgroundColor: colors,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            boxWidth: 12,
                            font: {
                                size: 11
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = labels[context.dataIndex];
                                const value = context.parsed;
                                const total = values.reduce((a, b) => a + b, 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                
                                if (showRevenue) {
                                    return label + ': Kr. ' + value.toLocaleString('da-DK', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    }) + ' (' + percentage + '%)';
                                } else {
                                    return label + ': ' + value.toLocaleString('da-DK') + ' kort (' + percentage + '%)';
                                }
                            }
                        }
                    }
                }
            }
        });
    }

    processMonthlyData(rawData, countryFilter, showRevenue) {
        const monthlyData = {};
        
        rawData.forEach(function(row) {
            // Apply country filter
            if (countryFilter !== 'all' && row.land !== countryFilter) {
                return;
            }
            
            const month = row.month_year;
            const value = showRevenue ? parseFloat(row.total_omsaetning) : parseInt(row.total_sold);
            
            if (!monthlyData[month]) {
                monthlyData[month] = 0;
            }
            monthlyData[month] += value;
        });
        
        // Sort by month
        const sortedEntries = Object.entries(monthlyData).sort((a, b) => a[0].localeCompare(b[0]));
        const sortedData = {};
        sortedEntries.forEach(([key, value]) => {
            sortedData[key] = value;
        });
        
        return sortedData;
    }

    processConceptData(rawData, countryFilter, showRevenue) {
        const conceptData = {};
        const conceptNames = {}; // Track concept names for sorting
        
        rawData.forEach(function(row) {
            // Apply country filter
            if (countryFilter !== 'all' && row.land !== countryFilter) {
                return;
            }
            
            const concept = row.concept_code; // Use concept_code as requested
            const country = row.land;
            const value = showRevenue ? parseFloat(row.total_omsaetning) : parseInt(row.total_sold);
            
            // Create unique key for concept + country combination when filtering by country
            let key;
            if (countryFilter === 'all') {
                key = concept;
            } else {
                key = concept; // Just concept name when filtering by specific country
            }
            
            if (!conceptData[key]) {
                conceptData[key] = 0;
            }
            conceptData[key] += value;
            conceptNames[key] = concept;
        });
        
        // Sort based on filter type
        let sortedEntries;
        if (countryFilter === 'all') {
            // When showing all countries: show total for each concept across all countries
            const conceptTotals = {};
            
            // Sum up all countries for each concept
            rawData.forEach(function(row) {
                const concept = row.concept_code;
                const value = showRevenue ? parseFloat(row.total_omsaetning) : parseInt(row.total_sold);
                
                if (!conceptTotals[concept]) {
                    conceptTotals[concept] = 0;
                }
                conceptTotals[concept] += value;
            });
            
            // Sort concepts alphabetically
            const sortedConcepts = Object.keys(conceptTotals).sort();
            const finalData = {};
            
            sortedConcepts.forEach(concept => {
                finalData[concept] = conceptTotals[concept];
            });
            
            return finalData;
        } else {
            // When filtering by specific country: sort by concept name
            sortedEntries = Object.entries(conceptData).sort((a, b) => a[0].localeCompare(b[0]));
            const sortedData = {};
            sortedEntries.forEach(([key, value]) => {
                sortedData[key] = value;
            });
            return sortedData;
        }
    }

    updateDataTable(rawData) {
        const tbody = $('#dataTableBody');
        tbody.empty();
        
        if (!rawData || rawData.length === 0) {
            tbody.append('<tr><td colspan="6" class="text-center">Ingen data fundet</td></tr>');
            return;
        }
        
        // Apply country filter
        const countryFilter = $('#dataTableCountryFilter').val();
        let filteredData = rawData.filter(function(row) {
            return countryFilter === 'all' || row.land === countryFilter;
        });
        
        // Apply sorting if set
        if (this.tableSort.column) {
            const column = this.tableSort.column;
            const direction = this.tableSort.direction;
            
            filteredData.sort(function(a, b) {
                let aVal = a[column];
                let bVal = b[column];
                
                // Convert to numbers for numeric columns
                if (column === 'pris_kr' || column === 'total_sold' || column === 'total_omsaetning') {
                    aVal = parseFloat(aVal) || 0;
                    bVal = parseFloat(bVal) || 0;
                }
                
                // String comparison for text columns
                if (typeof aVal === 'string' && typeof bVal === 'string') {
                    aVal = aVal.toLowerCase();
                    bVal = bVal.toLowerCase();
                }
                
                if (direction === 'asc') {
                    return aVal < bVal ? -1 : (aVal > bVal ? 1 : 0);
                } else {
                    return aVal > bVal ? -1 : (aVal < bVal ? 1 : 0);
                }
            });
        }
        
        // Render filtered and sorted data
        filteredData.forEach(function(row) {
            const tr = $('<tr>');
            tr.append('<td>' + row.land + '</td>');
            tr.append('<td>' + row.month_year + '</td>');
            tr.append('<td>' + row.concept_code + '</td>');
            tr.append('<td class="text-end">' + 
                     parseFloat(row.pris_kr).toLocaleString('da-DK', {
                         minimumFractionDigits: 2,
                         maximumFractionDigits: 2
                     }) + '</td>');
            tr.append('<td class="text-end">' + 
                     parseInt(row.total_sold).toLocaleString('da-DK') + '</td>');
            tr.append('<td class="text-end">' + 
                     parseFloat(row.total_omsaetning).toLocaleString('da-DK', {
                         minimumFractionDigits: 2,
                         maximumFractionDigits: 2
                     }) + '</td>');
            tbody.append(tr);
        });
        
        // Show count of filtered results
        const totalRows = rawData.length;
        const filteredRows = filteredData.length;
        if (filteredRows !== totalRows) {
            tbody.append('<tr class="table-secondary"><td colspan="6" class="text-center"><small>Viser ' + 
                        filteredRows + ' af ' + totalRows + ' rækker</small></td></tr>');
        }
    }

    showAlert(message, type) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        
        $('.container-fluid').prepend(alertHtml);
        
        setTimeout(function() {
            $('.alert').fadeOut('slow', function() {
                $(this).remove();
            });
        }, 5000);
    }

    updateDatesForDatabase() {
        const database = $('#databaseSelect').val();
        const year = database.includes('2024') ? '2024' : '2025';
        
        // Sæt start dato til 1. januar i det valgte år
        $('#startDate').val(year + '-01-01');
        
        // Sæt slut dato til 31. december i det valgte år
        $('#endDate').val(year + '-12-31');
    }

    showDebugInfo(response) {
        if (response && response.debug) {
            $('#debugSql').text(response.debug.sql || 'N/A');
            $('#debugParams').text(JSON.stringify(response.debug.params, null, 2) || 'N/A');
            $('#debugCount').text(response.debug.resultCount || '0');
            
            // Only show if debug button is active
            if ($('#toggleDebugBtn').hasClass('btn-secondary')) {
                $('#debugCard').show();
            }
        }
    }

    exportCSV() {
        const database = $('#databaseSelect').val();
        const startDate = $('#startDate').val();
        const endDate = $('#endDate').val();
        
        const url = this.servicePath + 'exportCSV/' + database + '/' + startDate + '/' + endDate;
        window.location.href = url;
    }
}

if (typeof window.salesStatisticsReady == "function") {
    salesStatisticsReady();
}