var SALGSTATS_AJAX_URL = "https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/apps/salgstats/";
import Base from 'https://system.gavefabrikken.dk/gavefabrikken_backend/units/cardshop/main/js/base.js';

export default class SalgStats extends Base {
    constructor() {
        super();
        this.salesData = [];
        this.countryData = [];
        this.conceptData = [];
        this.concepts = [];
        this.salespersons = [];

        // Chart instances
        this.salesTimeChart = null;
        this.countryChart = null;
        this.revenueChart = null;
        this.conceptChart = null;

        // Chart colors
        this.colors = {
            primary: '#667eea',
            secondary: '#764ba2',
            success: '#28a745',
            danger: '#dc3545',
            warning: '#ffc107',
            info: '#17a2b8',
            danmark: '#d32f2f',
            norge: '#1976d2',
            sverige: '#f57c00'
        };

        this.chartColors = [
            '#667eea', '#764ba2', '#28a745', '#dc3545', '#ffc107',
            '#17a2b8', '#6f42c1', '#e83e8c', '#fd7e14', '#20c997'
        ];
    }

    async init() {
        try {
            console.log("Initializing SalgStats...");

            // Set default dates (current month)
            this.setDefaultDates();

            // Load initial data
            await this.loadConcepts();
            await this.loadSalespersons();

            // Setup event listeners
            this.setupEventListeners();

            // Load initial stats
            await this.updateStats();

            console.log("SalgStats initialized successfully");
        } catch (error) {
            console.error("Error initializing SalgStats:", error);
            this.showMessage('error', 'Fejl ved initialisering af systemet');
        }
    }

    setDefaultDates() {
        const now = new Date();
        const currentMonth = now.getMonth();
        const currentYear = now.getFullYear();

        // Set from date to first day of current month
        const fromDate = new Date(currentYear, currentMonth, 1);
        $('#dateFrom').val(fromDate.toISOString().split('T')[0]);

        // Set to date to last day of current month
        const toDate = new Date(currentYear, currentMonth + 1, 0);
        $('#dateTo').val(toDate.toISOString().split('T')[0]);
    }

    async loadConcepts() {
        try {
            const response = await this.makeRequest('getConcepts');
            if (response.status === 1 && response.data) {
                this.concepts = response.data;
                this.populateConceptsDropdown();
            }
        } catch (error) {
            console.error('Error loading concepts:', error);
        }
    }

    async loadSalespersons() {
        try {
            const response = await this.makeRequest('getSalespersons');
            if (response.status === 1 && response.data) {
                this.salespersons = response.data;
                this.populateSalespersonsDropdown();
            }
        } catch (error) {
            console.error('Error loading salespersons:', error);
        }
    }

    populateConceptsDropdown() {
        const conceptSelect = $('#conceptCode');
        conceptSelect.find('option:not(:first)').remove();

        this.concepts.forEach(concept => {
            conceptSelect.append(`<option value="${concept.concept_code}">${concept.concept_name || concept.concept_code}</option>`);
        });
    }

    populateSalespersonsDropdown() {
        const salespersonSelect = $('#salesperson');
        // Keep the "Alle" and "Import/Web" options, add others

        this.salespersons.forEach(sp => {
            if (sp.salesperson && sp.salesperson.toLowerCase() !== 'import') {
                salespersonSelect.append(`<option value="${sp.salesperson}">${sp.salesperson}</option>`);
            }
        });
    }

    setupEventListeners() {
        const self = this;

        // Update stats button
        $('#updateStats').on('click', function(e) {
            e.preventDefault();
            self.updateStats();
        });

        // Export CSV button
        $('#exportCSV').on('click', function(e) {
            e.preventDefault();
            self.exportCSV();
        });

        // Export Excel button
        $('#exportExcel').on('click', function(e) {
            e.preventDefault();
            self.exportExcel();
        });

        // Filter change listeners
        $('#dateFrom, #dateTo, #salesperson, #groupBy, #conceptCode').on('change', function() {
            // Auto-update when filters change (with debounce)
            clearTimeout(self.updateTimeout);
            self.updateTimeout = setTimeout(() => {
                self.updateStats();
            }, 500);
        });
    }

    async updateStats() {
        try {
            this.showLoading(true);

            // Get filter values
            const filters = this.getFilters();

            // Load all data in parallel
            const [salesResponse, countryResponse, conceptResponse] = await Promise.all([
                this.makeRequest('getSalesStats', filters),
                this.makeRequest('getSalesByLanguage', filters),
                this.makeRequest('getSalesByConcept', filters)
            ]);

            // Process data
            if (salesResponse.status === 1) {
                this.salesData = salesResponse.data;
            }

            if (countryResponse.status === 1) {
                this.countryData = countryResponse.data;
            }

            if (conceptResponse.status === 1) {
                this.conceptData = conceptResponse.data;
            }

            // Update UI
            this.updateStatsOverview();
            this.updateCharts();
            this.updateTable();

            this.showLoading(false);

        } catch (error) {
            console.error('Error updating stats:', error);
            this.showMessage('error', 'Fejl ved opdatering af statistik');
            this.showLoading(false);
        }
    }

    getFilters() {
        return {
            date_from: $('#dateFrom').val(),
            date_to: $('#dateTo').val(),
            salesperson: $('#salesperson').val(),
            group_by: $('#groupBy').val(),
            concept_code: $('#conceptCode').val()
        };
    }

    updateStatsOverview() {
        const totalSales = this.salesData.reduce((sum, item) => sum + parseInt(item.total_sold), 0);
        const totalRevenue = this.salesData.reduce((sum, item) => sum + parseFloat(item.total_revenue), 0);
        const avgPerPeriod = this.salesData.length > 0 ? Math.round(totalSales / this.salesData.length) : 0;
        const avgPrice = totalSales > 0 ? totalRevenue / totalSales : 0;

        // Update UI with animation
        this.animateNumber('#totalSales', totalSales);
        this.animateRevenue('#totalRevenue', totalRevenue);
        this.animateNumber('#avgPerPeriod', avgPerPeriod);
        this.animateRevenue('#avgPrice', avgPrice);
    }

    animateRevenue(selector, finalValue) {
        const element = $(selector);
        const currentValue = parseFloat(element.text().replace(/[^\d.,]/g, '').replace(',', '.')) || 0;
        const increment = (finalValue - currentValue) / 20;
        let current = currentValue;

        const timer = setInterval(() => {
            current += increment;
            if ((increment > 0 && current >= finalValue) || (increment < 0 && current <= finalValue)) {
                current = finalValue;
                clearInterval(timer);
            }
            element.text(Math.round(current).toLocaleString('da-DK') + ' kr');
        }, 50);
    }
    const element = $(selector);
    const currentValue = parseInt(element.text()) || 0;
    const increment = (finalValue - currentValue) / 20;
    let current = currentValue;

    const timer = setInterval(() => {
        current += increment;
        if ((increment > 0 && current >= finalValue) || (increment < 0 && current <= finalValue)) {
            current = finalValue;
            clearInterval(timer);
        }
        element.text(Math.round(current).toLocaleString('da-DK'));
    }, 50);
}

updateCharts() {
    this.updateSalesTimeChart();
    this.updateCountryChart();
    this.updateRevenueChart();
    this.updateConceptChart();
}

updateSalesTimeChart() {
    const ctx = document.getElementById('salesTimeChart');
    if (!ctx) return;

    // Destroy existing chart
    if (this.salesTimeChart) {
        this.salesTimeChart.destroy();
    }

    // Group data by period and land
    const periodData = {};
    const countries = new Set();

    this.salesData.forEach(item => {
        if (!periodData[item.period]) {
            periodData[item.period] = {};
        }
        periodData[item.period][item.land] = parseInt(item.total_sold);
        countries.add(item.land);
    });

    const periods = Object.keys(periodData).sort();
    const datasets = [];

    Array.from(countries).forEach((country, index) => {
        const data = periods.map(period => periodData[period][country] || 0);
        let color = this.chartColors[index % this.chartColors.length];

        // Use specific colors for countries
        if (country === 'Danmark') color = this.colors.danmark;
        if (country === 'Norge') color = this.colors.norge;
        if (country === 'Sverige') color = this.colors.sverige;

        datasets.push({
            label: country,
            data: data,
            borderColor: color,
            backgroundColor: color + '20',
            tension: 0.4,
            fill: false
        });
    });

    this.salesTimeChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: periods,
            datasets: datasets
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            animation: {
                duration: 1000,
                easing: 'easeOutQuart'
            }
        }
    });
}

updateCountryChart() {
    const ctx = document.getElementById('countryChart');
    if (!ctx) return;

    // Destroy existing chart
    if (this.countryChart) {
        this.countryChart.destroy();
    }

    if (this.countryData.length === 0) return;

    const labels = this.countryData.map(item => item.land);
    const data = this.countryData.map(item => parseInt(item.total_sold));
    const colors = labels.map(label => {
        if (label === 'Danmark') return this.colors.danmark;
        if (label === 'Norge') return this.colors.norge;
        if (label === 'Sverige') return this.colors.sverige;
        return this.colors.primary;
    });

    this.countryChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: colors,
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true
                    }
                }
            },
            animation: {
                duration: 1000,
                easing: 'easeOutQuart'
            }
        }
    });
}

updateRevenueChart() {
    const ctx = document.getElementById('revenueChart');
    if (!ctx) return;

    // Destroy existing chart
    if (this.revenueChart) {
        this.revenueChart.destroy();
    }

    if (this.countryData.length === 0) return;

    const labels = this.countryData.map(item => item.land);
    const data = this.countryData.map(item => parseFloat(item.total_revenue) || 0);
    const colors = labels.map(label => {
        if (label === 'Danmark') return this.colors.danmark;
        if (label === 'Norge') return this.colors.norge;
        if (label === 'Sverige') return this.colors.sverige;
        return this.colors.primary;
    });

    this.revenueChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: colors,
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const value = context.parsed;
                            return context.label + ': ' + value.toLocaleString('da-DK', {minimumFractionDigits: 2}) + ' kr';
                        }
                    }
                }
            },
            animation: {
                duration: 1000,
                easing: 'easeOutQuart'
            }
        }
    });
}
const ctx = document.getElementById('conceptChart');
if (!ctx) return;

// Destroy existing chart
if (this.conceptChart) {
    this.conceptChart.destroy();
}

if (this.conceptData.length === 0) return;

const labels = this.conceptData.map(item => item.concept_name || item.concept_code);
const data = this.conceptData.map(item => parseInt(item.total_sold));
const colors = this.chartColors.slice(0, labels.length);

this.conceptChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: labels,
        datasets: [{
            label: 'Antal Solgt',
            data: data,
            backgroundColor: colors,
            borderColor: colors.map(color => color.replace('0.8', '1')),
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            },
            x: {
                ticks: {
                    maxRotation: 45,
                    minRotation: 0
                }
            }
        },
        animation: {
            duration: 1000,
            easing: 'easeOutQuart'
        }
    }
});
}

updateTable() {
    const tbody = $('#salesTable tbody');
    tbody.empty();

    if (this.salesData.length === 0) {
        tbody.append('<tr><td colspan="6" class="text-center text-muted">Ingen data fundet</td></tr>');
        return;
    }

    // Sort data by period desc, then by total_sold desc
    const sortedData = [...this.salesData].sort((a, b) => {
        if (a.period !== b.period) {
            return b.period.localeCompare(a.period);
        }
        return parseInt(b.total_sold) - parseInt(a.total_sold);
    });

    sortedData.forEach(item => {
        const cardPrice = parseFloat(item.card_price) || 0;
        const totalRevenue = parseFloat(item.total_revenue) || 0;

        const row = `
                <tr>
                    <td><strong>${item.period}</strong></td>
                    <td>
                        <span class="badge" style="background-color: ${this.getCountryColor(item.land)}">
                            ${item.land}
                        </span>
                    </td>
                    <td>${item.concept_name || item.concept_code || '-'}</td>
                    <td>
                        <strong class="text-primary">${parseInt(item.total_sold).toLocaleString('da-DK')}</strong>
                    </td>
                    <td>
                        <span class="price-badge">${cardPrice.toLocaleString('da-DK', {minimumFractionDigits: 2})} kr</span>
                    </td>
                    <td>
                        <span class="revenue-highlight">${totalRevenue.toLocaleString('da-DK', {minimumFractionDigits: 2})} kr</span>
                    </td>
                </tr>
            `;
        tbody.append(row);
    });
}

getCountryColor(country) {
    switch (country) {
        case 'Danmark': return this.colors.danmark;
        case 'Norge': return this.colors.norge;
        case 'Sverige': return this.colors.sverige;
        default: return this.colors.primary;
    }
}

async exportExcel() {
    try {
        const filters = this.getFilters();

        // Create a form and submit it to trigger Excel download
        const form = $('<form>', {
            action: SALGSTATS_AJAX_URL + 'exportExcel',
            method: 'POST',
            target: '_blank'
        });

        // Add filters as hidden inputs
        Object.keys(filters).forEach(key => {
            form.append($('<input>', {
                type: 'hidden',
                name: key,
                value: filters[key]
            }));
        });

        $('body').append(form);
        form.submit();
        form.remove();

        this.showMessage('success', 'Excel eksport startet');

    } catch (error) {
        console.error('Error exporting Excel:', error);
        this.showMessage('error', 'Fejl ved eksport af data');
    }
}
try {
    const filters = this.getFilters();

    // Create a form and submit it to trigger CSV download
    const form = $('<form>', {
        action: SALGSTATS_AJAX_URL + 'exportCSV',
        method: 'POST',
        target: '_blank'
    });

    // Add filters as hidden inputs
    Object.keys(filters).forEach(key => {
        form.append($('<input>', {
            type: 'hidden',
            name: key,
            value: filters[key]
        }));
    });

    $('body').append(form);
    form.submit();
    form.remove();

    this.showMessage('success', 'CSV eksport startet');

} catch (error) {
    console.error('Error exporting CSV:', error);
    this.showMessage('error', 'Fejl ved eksport af data');
}
}

showLoading(show) {
    if (show) {
        $('#loading').show();
        $('#chartsContainer, #statsOverview').hide();
    } else {
        $('#loading').hide();
        $('#chartsContainer, #statsOverview').show();
    }
}

showMessage(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle';

    const messageHtml = `
            <div class="alert ${alertClass} alert-custom alert-dismissible fade show" role="alert">
                <i class="fas ${icon} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;

    $('#message-container').html(messageHtml);

    // Auto hide after 5 seconds
    setTimeout(() => {
        $('#message-container .alert').alert('close');
    }, 5000);
}

makeRequest(action, data = {}) {
    return new Promise((resolve, reject) => {
        $.post(SALGSTATS_AJAX_URL + action, data, function(response) {
            try {
                if (typeof response === 'string') {
                    response = JSON.parse(response);
                }
                resolve(response);
            } catch (error) {
                reject(error);
            }
        }, 'json').fail(function(xhr, status, error) {
            reject(error);
        });
    });
}
}