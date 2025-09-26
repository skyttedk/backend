class AdaptAccuracyAnalyzer {
    constructor() {
        this.currentShopId = null;
        this.currentData = null;
        this.charts = {};
        this.allShops = [];
        this.comparisonData = [];
        this.isComparing = false;

        // Set the correct base URL for API calls
        this.baseUrl = window.CONTROLLER_URL || 'controller.php?action=';
        console.log('🔧 AdaptAccuracyAnalyzer using base URL:', this.baseUrl);

        this.init();
    }

    init() {
        this.setupEventListeners();
        this.loadShops();
    }

    setupEventListeners() {
        // Shop search
        document.getElementById('shopSearch').addEventListener('input', (e) => {
            this.filterShops(e.target.value);
        });

        // Shop selection
        document.getElementById('shopSelect').addEventListener('change', (e) => {
            this.currentShopId = e.target.value;
            document.getElementById('analyzeBtn').disabled = !this.currentShopId;
        });

        // Analyze buttons
        document.getElementById('analyzeBtn').addEventListener('click', () => {
            this.analyzeShop();
        });

        document.getElementById('compareAllBtn').addEventListener('click', () => {
            this.compareAllShopsProgressive();
        });

        // Table search and filtering
        const productSearch = document.getElementById('productSearch');
        const accuracyFilter = document.getElementById('accuracyFilter');
        const typeFilter = document.getElementById('typeFilter');
        const statusFilter = document.getElementById('statusFilter');

        if (productSearch) {
            productSearch.addEventListener('input', (e) => {
                this.filterProductTable();
            });
        }

        if (accuracyFilter) {
            accuracyFilter.addEventListener('change', (e) => {
                this.filterProductTable();
            });
        }

        if (typeFilter) {
            typeFilter.addEventListener('change', (e) => {
                this.filterProductTable();
            });
        }

        if (statusFilter) {
            statusFilter.addEventListener('change', (e) => {
                this.filterProductTable();
            });
        }

        // Modal close
        const modalClose = document.querySelector('.modal-close');
        if (modalClose) {
            modalClose.addEventListener('click', () => {
                this.closeModal();
            });
        }

        // Close modal on outside click
        const productModal = document.getElementById('productModal');
        if (productModal) {
            productModal.addEventListener('click', (e) => {
                if (e.target.classList.contains('modal')) {
                    this.closeModal();
                }
            });
        }

        // Shop ranking table filters
        const shopRankingSearch = document.getElementById('shopRankingSearch');
        const accuracyRangeFilter = document.getElementById('accuracyRangeFilter');
        const productCountFilter = document.getElementById('productCountFilter');

        if (shopRankingSearch) {
            shopRankingSearch.addEventListener('input', () => {
                this.filterShopRankingTable();
            });
        }

        if (accuracyRangeFilter) {
            accuracyRangeFilter.addEventListener('change', () => {
                this.filterShopRankingTable();
            });
        }

        if (productCountFilter) {
            productCountFilter.addEventListener('change', () => {
                this.filterShopRankingTable();
            });
        }

        // Sortable column headers
        const sortableHeaders = document.querySelectorAll('.sortable');
        sortableHeaders.forEach(header => {
            header.addEventListener('click', (e) => {
                const sortBy = header.dataset.sort;
                this.sortShopRankingTable(sortBy);
            });
        });

        // Sidebar close
        const sidebarClose = document.getElementById('closeSidebar');
        if (sidebarClose) {
            sidebarClose.addEventListener('click', () => {
                this.closeSidebar();
            });
        }

        // Max adapt sort button
        const sortByMaxAdaptBtn = document.getElementById('sortByMaxAdaptBtn');
        if (sortByMaxAdaptBtn) {
            sortByMaxAdaptBtn.addEventListener('click', () => {
                this.sortByMaxAdapt();
            });
        }
    }



    async loadShops() {
        try {
            console.log('🔍 Loading shops from:', this.baseUrl + 'getShopList');
            const response = await fetch(this.baseUrl + 'getShopList');
            const result = await response.json();

            if (result.status === 1 && result.data) {
                this.allShops = result.data;
                this.populateShopSelect(this.allShops);
                console.log('✅ Shops loaded successfully:', result.data.length, 'shops');
            } else {
                this.allShops = [];
                const select = document.getElementById('shopSelect');
                select.innerHTML = '<option value="">Ingen shops fundet</option>';
                console.warn('⚠️ No shops found or API error:', result);
            }
        } catch (error) {
            console.error('❌ Error loading shops:', error);
            this.showError('Fejl ved indlæsning af shops: ' + error.message);
        }
    }

    populateShopSelect(shops) {
        const select = document.getElementById('shopSelect');
        select.innerHTML = '<option value="">Vælg en shop...</option>';
        
        shops.forEach(shop => {
            const option = document.createElement('option');
            option.value = shop.id;
            option.textContent = `${shop.name} (${shop.reservation_count} reservationer)`;
            select.appendChild(option);
        });
    }

    filterShops(searchTerm) {
        if (!searchTerm.trim()) {
            this.populateShopSelect(this.allShops);
            return;
        }

        const filteredShops = this.allShops.filter(shop => 
            shop.name.toLowerCase().includes(searchTerm.toLowerCase())
        );
        
        this.populateShopSelect(filteredShops);
    }

    async analyzeShop() {
        if (!this.currentShopId) return;

        this.showLoading(true);
        this.hideError();
        this.hideAllSections();

        try {
            const params = new URLSearchParams({
                shop_id: this.currentShopId
            });

            const url = this.baseUrl + 'analyzeAdaptAccuracy&' + params.toString();
            console.log('🔍 Analyzing shop with URL:', url);

            const response = await fetch(url);
            const result = await response.json();

            if (result.status === 1) {
                this.currentData = result.data;
                this.displaySingleShopAnalysis(result.data);
                console.log('✅ Shop analysis successful');
            } else {
                this.showError(result.error || 'Fejl ved analyse');
                console.error('❌ Shop analysis failed:', result);
            }
        } catch (error) {
            console.error('❌ Error analyzing shop:', error);
            this.showError('Netværksfejl ved analyse: ' + error.message);
        } finally {
            this.showLoading(false);
        }
    }

    async compareAllShops() {
        this.showLoading(true);
        this.hideError();
        this.hideAllSections();

        try {
            const url = this.baseUrl + 'compareAllShops';
            console.log('🔍 Comparing all shops with URL:', url);

            const response = await fetch(url);
            const result = await response.json();

            if (result.status === 1) {
                this.displayComparisonAnalysis(result.data);
                console.log('✅ Shop comparison successful');
            } else {
                this.showError(result.error || 'Fejl ved sammenligning');
                console.error('❌ Shop comparison failed:', result);
            }
        } catch (error) {
            console.error('❌ Error comparing shops:', error);
            this.showError('Netværksfejl ved sammenligning: ' + error.message);
        } finally {
            this.showLoading(false);
        }
    }

    async compareAllShopsProgressive() {
        if (this.isComparing) {
            console.log('⚠️ Comparison already in progress');
            return;
        }

        // Get shops with reservations for confirmation
        const allShopsWithReservations = this.allShops.filter(shop => shop.reservation_count > 0);
        
        if (allShopsWithReservations.length === 0) {
            this.showError('Ingen shops med reservationer fundet');
            return;
        }

        // TEST MODE: Limit to first 10 shops for testing
        const TEST_MODE = true;
        const MAX_TEST_SHOPS = 10;
        
        const shopsWithReservations = TEST_MODE ? 
            allShopsWithReservations.slice(0, MAX_TEST_SHOPS) : 
            allShopsWithReservations;
        
        const totalShops = shopsWithReservations.length;
        const totalAvailable = allShopsWithReservations.length;

        // Confirm dialog with test mode info
        let confirmMessage = `Du er ved at sammenligne ${totalShops} shops`;
        
        if (TEST_MODE && totalAvailable > MAX_TEST_SHOPS) {
            confirmMessage += ` (TEST MODE - kun første ${MAX_TEST_SHOPS} af ${totalAvailable} tilgængelige shops)`;
        }
        
        confirmMessage += `.\n\nDette kan tage flere minutter at gennemføre.\n\nVil du fortsætte?`;
        
        if (!confirm(confirmMessage)) {
            console.log('📋 User cancelled shop comparison');
            return;
        }

        this.isComparing = true;
        this.hideError();
        this.hideAllSections();
        this.showProgressBar(true);

        try {
            // Start with empty comparison data
            this.comparisonData = [];

            if (TEST_MODE) {
                console.log(`🧪 TEST MODE: Starting progressive comparison of ${totalShops} shops (limited from ${totalAvailable} total)`);
            } else {
                console.log(`🏁 Starting progressive comparison of ${totalShops} shops`);
            }

            const startTime = Date.now();
            let analyzedCount = 0;

            // Update initial progress
            this.updateProgress(0, totalShops, 'Starter analyse...', '-', 0);

            // Analyze each shop individually
            for (let i = 0; i < shopsWithReservations.length; i++) {
                const shop = shopsWithReservations[i];
                
                try {
                    // Update current shop progress
                    this.updateProgress(i, totalShops, 'Analyserer...', shop.name, startTime);

                    // Analyze single shop
                    const analysisData = await this.analyzeSingleShopForComparison(shop.id);
                    
                    if (analysisData) {
                        this.comparisonData.push({
                            shop_id: shop.id,
                            shop_name: shop.name,
                            reservation_count: shop.reservation_count,
                            ...analysisData
                        });
                        analyzedCount++;
                    }

                } catch (error) {
                    console.error(`❌ Error analyzing shop ${shop.name}:`, error);
                    // Continue with next shop instead of failing completely
                }

                // Small delay to prevent overwhelming the server
                await this.delay(100);
            }

            // Final progress update
            this.updateProgress(totalShops, totalShops, 'Færdig!', 'Genererer rapport...', startTime);

            // Generate comparison report
            const comparisonReport = this.generateComparisonReport(this.comparisonData);
            
            // Hide progress and show results
            this.showProgressBar(false);
            this.displayComparisonAnalysis(comparisonReport);

            console.log('✅ Progressive comparison completed:', analyzedCount, 'shops analyzed');

        } catch (error) {
            console.error('❌ Error in progressive comparison:', error);
            this.showError('Fejl ved sammenligning: ' + error.message);
        } finally {
            this.isComparing = false;
            this.showProgressBar(false);
        }
    }

    async analyzeSingleShopForComparison(shopId) {
        try {
            const params = new URLSearchParams({
                shop_id: shopId
            });

            const url = this.baseUrl + 'analyzeAdaptAccuracy&' + params.toString();
            console.log('🔍 Analyzing shop for comparison:', url);

            const response = await fetch(url);

            const result = await response.json();

            if (result.status === 1) {
                return result.data.analysis;
            } else {
                console.warn(`⚠️ No analysis data for shop ${shopId}:`, result.message);
                return null;
            }
        } catch (error) {
            console.error(`❌ Error analyzing shop ${shopId}:`, error);
            return null;
        }
    }

    generateComparisonReport(shopData) {
        if (shopData.length === 0) {
            return {
                comparison_data: [],
                overall_stats: { total_shops: 0, total_products: 0, avg_accuracy: 0 },
                chart_data: { shop_comparison: [], adapt_performance: [] }
            };
        }

        // Calculate overall statistics
        let totalProducts = 0;
        let overallAccuracies = [];

        const processedShops = shopData.map(shop => {
            const overallAccuracy = shop.overall_accuracy || {};
            const autopilotAccuracy = shop.autopilot_overall_accuracy || {};
            
            // Use autopilot accuracy if available, otherwise fall back to original
            const avgAccuracy = this.calculateAverageAccuracy(overallAccuracy, autopilotAccuracy);
            const adaptAccuracies = this.getIndividualAdaptAccuracies(overallAccuracy, autopilotAccuracy);
            
            totalProducts += shop.total_products_analyzed || 0;
            if (avgAccuracy > 0) {
                overallAccuracies.push(avgAccuracy);
            }

            return {
                shop_id: shop.shop_id,
                shop_name: shop.shop_name,
                avg_accuracy: avgAccuracy,
                total_products: shop.total_products_analyzed || 0,
                adapt_1_accuracy: adaptAccuracies.adapt_1,
                adapt_2_accuracy: adaptAccuracies.adapt_2,
                adapt_3_accuracy: adaptAccuracies.adapt_3
            };
        });

        // Sort by average accuracy (descending)
        processedShops.sort((a, b) => b.avg_accuracy - a.avg_accuracy);

        // Add ranking
        processedShops.forEach((shop, index) => {
            shop.rank = index + 1;
        });

        const avgOverallAccuracy = overallAccuracies.length > 0 
            ? overallAccuracies.reduce((a, b) => a + b, 0) / overallAccuracies.length 
            : 0;

        // Generate chart data in correct format
        const shopComparisonData = processedShops.map(shop => ({
            shop_name: shop.shop_name,
            accuracy: shop.avg_accuracy
        }));
        
        const adaptPerformanceData = {
            adapt_1: processedShops.map(shop => ({
                shop: shop.shop_name,
                accuracy: shop.adapt_1_accuracy
            })),
            adapt_2: processedShops.map(shop => ({
                shop: shop.shop_name,
                accuracy: shop.adapt_2_accuracy
            })),
            adapt_3: processedShops.map(shop => ({
                shop: shop.shop_name,
                accuracy: shop.adapt_3_accuracy
            }))
        };
        
        console.log('📊 CHART DATA GENERATION:', {
            shopCount: processedShops.length,
            adaptPerformanceData: adaptPerformanceData
        });
        
        const chartData = {
            shop_comparison: shopComparisonData,
            adapt_performance: adaptPerformanceData
        };

        return {
            comparison_data: processedShops,
            overall_stats: {
                total_shops: shopData.length,
                total_products: totalProducts,
                avg_accuracy: Math.round(avgOverallAccuracy)
            },
            chart_data: chartData,
            analysis_period: {
                from: 'Alle data',
                to: 'i dag'
            }
        };
    }

    calculateAverageAccuracy(overallAccuracy, autopilotAccuracy = null) {
        // Prefer autopilot accuracy when available, otherwise use original
        // Use highest available adapt (3 -> 2 -> 1) for overall accuracy
        
        // Try autopilot first if available
        if (autopilotAccuracy) {
            if (autopilotAccuracy.adapt_3?.accuracy_percent > 0) {
                return Math.round(autopilotAccuracy.adapt_3.accuracy_percent);
            } else if (autopilotAccuracy.adapt_2?.accuracy_percent > 0) {
                return Math.round(autopilotAccuracy.adapt_2.accuracy_percent);
            } else if (autopilotAccuracy.adapt_1?.accuracy_percent > 0) {
                return Math.round(autopilotAccuracy.adapt_1.accuracy_percent);
            }
        }
        
        // Fall back to original accuracy
        if (overallAccuracy.adapt_3?.accuracy_percent > 0) {
            return Math.round(overallAccuracy.adapt_3.accuracy_percent);
        } else if (overallAccuracy.adapt_2?.accuracy_percent > 0) {
            return Math.round(overallAccuracy.adapt_2.accuracy_percent);
        } else if (overallAccuracy.adapt_1?.accuracy_percent > 0) {
            return Math.round(overallAccuracy.adapt_1.accuracy_percent);
        }
        
        return 0;
    }

    getIndividualAdaptAccuracies(overallAccuracy, autopilotAccuracy = null) {
        // For individual adapt columns, prefer autopilot when available
        const result = {
            adapt_1: 0,
            adapt_2: 0,
            adapt_3: 0
        };

        ['adapt_1', 'adapt_2', 'adapt_3'].forEach(adapt => {
            // Try autopilot first
            if (autopilotAccuracy && autopilotAccuracy[adapt]?.accuracy_percent > 0) {
                result[adapt] = Math.round(autopilotAccuracy[adapt].accuracy_percent);
            }
            // Fall back to original
            else if (overallAccuracy[adapt]?.accuracy_percent > 0) {
                result[adapt] = Math.round(overallAccuracy[adapt].accuracy_percent);
            }
        });

        return result;
    }

    calculateFieldAverageAccuracy(shopData, field) {
        const accuracies = shopData
            .map(shop => shop.overall_accuracy?.[field]?.accuracy_percent)
            .filter(acc => acc && acc > 0);
        
        return accuracies.length > 0 ? Math.round(accuracies.reduce((a, b) => a + b, 0) / accuracies.length) : 0;
    }

    showProgressBar(show) {
        document.getElementById('progressContainer').style.display = show ? 'block' : 'none';
    }

    updateProgress(current, total, status, currentShop, startTime) {
        const percentage = Math.round((current / total) * 100);
        
        // Update progress bar
        document.getElementById('progressFill').style.width = percentage + '%';
        document.getElementById('progressText').textContent = `${percentage}% - ${status}`;
        
        // Update details
        document.getElementById('analyzedCount').textContent = current;
        document.getElementById('totalCount').textContent = total;
        document.getElementById('currentShop').textContent = currentShop;
        
        // Calculate estimated time remaining
        if (startTime && current > 0) {
            const elapsed = Date.now() - startTime;
            const avgTimePerShop = elapsed / current;
            const remaining = (total - current) * avgTimePerShop;
            const minutes = Math.floor(remaining / 60000);
            const seconds = Math.floor((remaining % 60000) / 1000);
            
            if (minutes > 0) {
                document.getElementById('timeRemaining').textContent = `${minutes}m ${seconds}s`;
            } else {
                document.getElementById('timeRemaining').textContent = `${seconds}s`;
            }
        }
    }

    delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    displaySingleShopAnalysis(data) {
        // Shop info
        const shopSelect = document.getElementById('shopSelect');
        const selectedShop = shopSelect.options[shopSelect.selectedIndex].text;
        document.getElementById('shopTitle').textContent = `${selectedShop} - Analyse`;

        document.getElementById('shopMetadata').textContent = 'Fuld historisk analyse af alle data';

        // Summary cards
        const analysis = data.analysis;
        const shopInfo = analysis.shop_info || {};
        
        document.getElementById('totalProducts').textContent = analysis.total_products_analyzed;
        
        // Shop autopilot info
        if (shopInfo.total_orders && shopInfo.procent_selected) {
            document.getElementById('shopAutopilotInfo').textContent = 
                `${shopInfo.total_orders} ordrer, ${shopInfo.procent_selected}% har valgt`;
        }

        this.updateAccuracyCard('adapt1', analysis.overall_accuracy.adapt_1);
        this.updateAccuracyCard('adapt2', analysis.overall_accuracy.adapt_2);
        this.updateAccuracyCard('adapt3', analysis.overall_accuracy.adapt_3);

        // Autopilot accuracy cards
        if (analysis.autopilot_overall_accuracy) {
            // Beregn adapt stage
            const totalOrders = shopInfo.total_orders || 0;
            const procentSelected = shopInfo.procent_selected || 0;
            const adaptStage = this.calculateAdaptStage(totalOrders, procentSelected);
            const forecastProcent = this.calculateForecastProcent(totalOrders, procentSelected);
            
            document.getElementById('autopilotStage').textContent = `Adapt ${adaptStage}`;
            document.getElementById('autopilotDetails').textContent = 
                `${forecastProcent}x multiplier`;

            this.updateAutopilotAccuracyCard('autopilotAdapt1', analysis.autopilot_overall_accuracy.adapt_1);
            this.updateAutopilotAccuracyCard('autopilotAdapt2', analysis.autopilot_overall_accuracy.adapt_2);
            this.updateAutopilotAccuracyCard('autopilotAdapt3', analysis.autopilot_overall_accuracy.adapt_3);
        }

        // Create charts
        this.createAccuracyChart(data.chart_data.accuracy_by_adapt);
        this.createHitRateChart(data.chart_data.hit_rate_distribution);
        this.createScatterChart(data.chart_data.prediction_vs_actual);

        // Product table
        this.populateProductTable(analysis.product_analyses);

        document.getElementById('singleShopSection').style.display = 'block';
    }

    displayComparisonAnalysis(data) {
        console.log('🎯 Displaying comparison analysis with data:', data);
        
        // Destroy existing comparison charts first
        this.destroyComparisonCharts();
        
        // Header info
        const period = data.analysis_period || {};
        document.getElementById('comparisonMetadata').textContent =
            `Analyse periode: ${period.from || 'Alle data'} til ${period.to || 'i dag'}`;

        // Summary cards
        const summary = data.summary_stats || data.overall_stats || {};
        document.getElementById('totalShopsAnalyzed').textContent = summary.total_shops || 0;
        document.getElementById('totalProductsAll').textContent = summary.total_products || 0;

        // Calculate overall accuracy
        const overallAccuracies = [];
        const overallAccuracyData = summary.overall_accuracy || {};
        Object.values(overallAccuracyData).forEach(adapt => {
            if (adapt && adapt.total > 0) {
                overallAccuracies.push(adapt.accuracy_percent);
            }
        });
        const avgOverallAccuracy = overallAccuracies.length > 0
            ? Math.round(overallAccuracies.reduce((a, b) => a + b, 0) / overallAccuracies.length)
            : summary.avg_accuracy || 0;
        document.getElementById('overallAccuracy').textContent = `${avgOverallAccuracy}%`;

        // Create new comparison charts
        const chartData = data.chart_data || {};
        const comparisonData = data.comparison_data || [];
        
        if (comparisonData.length > 0) {
            console.log('📊 Creating new shop performance visualizations');
            this.createShopDistributionChart(comparisonData);
            this.createTopBottomChart(comparisonData);
            this.createTopBottomProductsChart(data);
            this.createAdaptAverageChart(comparisonData);
        } else {
            console.warn('⚠️ No comparison data available for charts');
        }

        // Shop ranking table
        this.populateShopRankingTable(data.comparison_data || []);

        document.getElementById('comparisonSection').style.display = 'block';
    }

    destroyComparisonCharts() {
        // Destroy all comparison charts safely
        const chartIds = ['shopDistribution', 'topBottom', 'topBottomProducts', 'adaptAverage'];
        
        chartIds.forEach(chartId => {
            if (this.charts[chartId]) {
                try {
                    this.charts[chartId].destroy();
                    console.log(`✅ Destroyed chart: ${chartId}`);
                } catch (error) {
                    console.warn(`⚠️ Error destroying chart ${chartId}:`, error);
                }
                delete this.charts[chartId];
            }
        });
    }

    updateAccuracyCard(adaptNum, accuracyData) {
        const accuracy = accuracyData.accuracy_percent;
        const total = accuracyData.total;
        const correct = accuracyData.correct;

        document.getElementById(`${adaptNum}Accuracy`).textContent =
            total > 0 ? `${accuracy}%` : '-';
        document.getElementById(`${adaptNum}Stats`).textContent =
            total > 0 ? `${correct}/${total} korrekte` : 'Ingen data';
    }

    updateAutopilotAccuracyCard(adaptNum, accuracyData) {
        const accuracy = accuracyData.accuracy_percent;
        const total = accuracyData.total;
        const correct = accuracyData.correct;

        document.getElementById(`${adaptNum}Accuracy`).textContent =
            total > 0 ? `${accuracy}%` : '-';
        document.getElementById(`${adaptNum}Stats`).textContent =
            total > 0 ? `${correct}/${total} justeret` : 'Ingen data';
    }

    calculateAdaptStage(totalOrders, procentSelected) {
        let adapt = 0;
        if (totalOrders < 500) {
            if (procentSelected > 20) adapt = 1;
            if (procentSelected > 40) adapt = 2;
            if (procentSelected > 50) adapt = 3;
        } else if (totalOrders >= 500 && totalOrders < 1000) {
            if (procentSelected > 15) adapt = 1;
            if (procentSelected > 30) adapt = 2;
            if (procentSelected > 50) adapt = 3;
        } else if (totalOrders > 1000) {
            if (procentSelected > 10) adapt = 1;
            if (procentSelected > 20) adapt = 2;
            if (procentSelected > 50) adapt = 3;
        }
        return adapt;
    }

    calculateForecastProcent(totalOrders, procentSelected) {
        let forecastProcent = 1.3;
        if (totalOrders < 500) {
            if (procentSelected > 20) forecastProcent = 1.2;
            if (procentSelected > 40) forecastProcent = 1.1;
            if (procentSelected > 50) forecastProcent = 1.05;
            if (procentSelected > 75) forecastProcent = 1.05;
        } else if (totalOrders >= 500 && totalOrders < 1000) {
            if (procentSelected > 15) forecastProcent = 1.2;
            if (procentSelected > 30) forecastProcent = 1.1;
            if (procentSelected > 50) forecastProcent = 1.05;
        } else if (totalOrders > 1000) {
            if (procentSelected > 10) forecastProcent = 1.2;
            if (procentSelected > 20) forecastProcent = 1.1;
            if (procentSelected > 50) forecastProcent = 1.05;
        }
        return forecastProcent;
    }

    createAccuracyChart(data) {
        const ctx = document.getElementById('accuracyChart').getContext('2d');

        if (this.charts.accuracy) {
            this.charts.accuracy.destroy();
        }

        this.charts.accuracy = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.map(item => item.adapt.toUpperCase()),
                datasets: [{
                    label: 'Nøjagtighed (%)',
                    data: data.map(item => item.accuracy),
                    backgroundColor: [
                        'rgba(255, 107, 107, 0.8)',
                        'rgba(254, 202, 87, 0.8)',
                        'rgba(72, 219, 251, 0.8)'
                    ],
                    borderColor: [
                        'rgba(255, 107, 107, 1)',
                        'rgba(254, 202, 87, 1)',
                        'rgba(72, 219, 251, 1)'
                    ],
                    borderWidth: 2,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            afterLabel: function(context) {
                                const item = data[context.dataIndex];
                                return `Total forudsigelser: ${item.total}`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                }
            }
        });
    }

    createHitRateChart(data) {
        const ctx = document.getElementById('hitRateChart').getContext('2d');

        if (this.charts.hitRate) {
            this.charts.hitRate.destroy();
        }

        const labels = ['Præcis', 'Tæt på', 'God', 'Ramt forbi'];
        const values = [data.exact, data.close, data.good, data.miss];
        const colors = [
            'rgba(39, 174, 96, 0.8)',
            'rgba(243, 156, 18, 0.8)',
            'rgba(52, 152, 219, 0.8)',
            'rgba(231, 76, 60, 0.8)'
        ];

        this.charts.hitRate = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: colors,
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = values.reduce((a, b) => a + b, 0);
                                const percentage = total > 0 ? Math.round((context.parsed / total) * 100) : 0;
                                return `${context.label}: ${context.parsed} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    }

    createScatterChart(data) {
        const ctx = document.getElementById('scatterChart').getContext('2d');

        if (this.charts.scatter) {
            this.charts.scatter.destroy();
        }

        const datasets = {
            adapt_1: { color: 'rgba(255, 107, 107, 0.7)', data: [] },
            adapt_2: { color: 'rgba(254, 202, 87, 0.7)', data: [] },
            adapt_3: { color: 'rgba(72, 219, 251, 0.7)', data: [] }
        };

        data.forEach(point => {
            if (datasets[point.adapt]) {
                datasets[point.adapt].data.push({
                    x: point.predicted,
                    y: point.actual,
                    product: point.product_name
                });
            }
        });

        const chartDatasets = Object.keys(datasets).map(adapt => ({
            label: adapt.replace('_', ' ').toUpperCase(),
            data: datasets[adapt].data,
            backgroundColor: datasets[adapt].color,
            borderColor: datasets[adapt].color.replace('0.7', '1'),
            pointRadius: 6,
            pointHoverRadius: 8
        }));

        this.charts.scatter = new Chart(ctx, {
            type: 'scatter',
            data: {
                datasets: chartDatasets
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            title: function(context) {
                                return context[0].raw.product || 'Produkt';
                            },
                            label: function(context) {
                                return `Forudsagt: ${context.parsed.x}, Faktisk: ${context.parsed.y}`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Forudsagt Antal'
                        },
                        beginAtZero: true
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Faktisk Antal'
                        },
                        beginAtZero: true
                    }
                }
            }
        });
    }

    createShopComparisonChart(data) {
        const canvas = document.getElementById('shopComparisonChart');
        if (!canvas) {
            console.error('❌ Canvas element "shopComparisonChart" not found');
            return;
        }
        
        const ctx = canvas.getContext('2d');

        if (this.charts.shopComparison) {
            this.charts.shopComparison.destroy();
        }

        if (!Array.isArray(data) || data.length === 0) {
            console.warn('⚠️ Shop comparison data is empty or invalid');
            this.createNoDataChart(ctx, 'Ingen shop sammenligning data');
            return;
        }
        
        console.log('🏪 SHOP RANKING CHART DATA:', data);

        const sortedData = [...data].sort((a, b) => b.accuracy - a.accuracy);
        
        const shopNames = sortedData.map(shop => shop.shop_name);
        const accuracyValues = sortedData.map(shop => shop.accuracy);
        
        console.log('🏪 SHOP NAMES:', shopNames);
        console.log('📊 ACCURACY VALUES:', accuracyValues);

        try {
            this.charts.shopComparison = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: shopNames,
                    datasets: [{
                        label: 'Overall Accuracy (%)',
                        data: accuracyValues,
                        backgroundColor: sortedData.map((shop, index) => {
                            if (index === 0) return 'rgba(39, 174, 96, 0.8)';
                            if (index === 1) return 'rgba(241, 196, 15, 0.8)';
                            if (index === 2) return 'rgba(230, 126, 34, 0.8)';
                            return 'rgba(149, 165, 166, 0.8)';
                        }),
                        borderWidth: 2,
                        borderRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        }
                    }
                }
            });
            console.log('✅ SHOP RANKING CHART CREATED');
        } catch (error) {
            console.error('❌ Error creating shop chart:', error);
            this.createNoDataChart(ctx, 'Fejl ved shop chart: ' + error.message);
        }
    }

    // NEW: Shop Performance Distribution (Histogram)
    createShopDistributionChart(comparisonData) {
        const canvas = document.getElementById('shopDistributionChart');
        if (!canvas) {
            console.error('❌ Canvas element "shopDistributionChart" not found');
            return;
        }
        
        const ctx = canvas.getContext('2d');

        if (this.charts.shopDistribution) {
            this.charts.shopDistribution.destroy();
        }

        // Create histogram data (10% intervals)
        const intervals = [
            { range: '0-10%', min: 0, max: 10, count: 0 },
            { range: '10-20%', min: 10, max: 20, count: 0 },
            { range: '20-30%', min: 20, max: 30, count: 0 },
            { range: '30-40%', min: 30, max: 40, count: 0 },
            { range: '40-50%', min: 40, max: 50, count: 0 },
            { range: '50-60%', min: 50, max: 60, count: 0 },
            { range: '60-70%', min: 60, max: 70, count: 0 },
            { range: '70-80%', min: 70, max: 80, count: 0 },
            { range: '80-90%', min: 80, max: 90, count: 0 },
            { range: '90-100%', min: 90, max: 100, count: 0 }
        ];

        // Count shops in each interval
        comparisonData.forEach(shop => {
            const accuracy = shop.avg_accuracy || 0;
            for (let interval of intervals) {
                if (accuracy >= interval.min && accuracy < interval.max) {
                    interval.count++;
                    break;
                } else if (accuracy === 100 && interval.range === '90-100%') {
                    interval.count++;
                    break;
                }
            }
        });

        console.log('📊 Shop distribution:', intervals);

        try {
            this.charts.shopDistribution = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: intervals.map(i => i.range),
                    datasets: [{
                        label: 'Antal Shops',
                        data: intervals.map(i => i.count),
                        backgroundColor: intervals.map(i => {
                            if (i.min >= 90) return 'rgba(39, 174, 96, 0.8)'; // Grøn
                            if (i.min >= 75) return 'rgba(52, 152, 219, 0.8)'; // Blå
                            if (i.min >= 50) return 'rgba(241, 196, 15, 0.8)'; // Gul
                            return 'rgba(231, 76, 60, 0.8)'; // Rød
                        }),
                        borderWidth: 2,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        },
                        title: {
                            display: true,
                            text: `Performance fordeling (${comparisonData.length} shops total)`
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
            console.log('✅ Shop distribution chart created');
        } catch (error) {
            console.error('❌ Error creating distribution chart:', error);  
            this.createNoDataChart(ctx, 'Fejl ved distribution chart: ' + error.message);
        }
    }

    // NEW: Top & Bottom Products Forecast Accuracy
    createTopBottomProductsChart(data) {
        const canvas = document.getElementById('topBottomProductsChart');
        if (!canvas) {
            console.error('❌ Canvas element "topBottomProductsChart" not found');
            return;
        }
        
        const ctx = canvas.getContext('2d');

        if (this.charts.topBottomProducts) {
            this.charts.topBottomProducts.destroy();
        }

        // Collect all products from all shops with their best accuracy
        const allProducts = [];
        const comparisonData = data.comparison_data || [];
        
        console.log('🔍 Debug: Comparison data structure:', comparisonData);
        
        comparisonData.forEach((shopData, shopIndex) => {
            console.log(`🏪 Debug: Shop ${shopIndex + 1}:`, shopData.shop_name);
            
            const analysis = shopData.analysis;
            if (!analysis) {
                console.log('❌ No analysis data for shop:', shopData.shop_name);
                return;
            }
            
            if (!analysis.product_analyses) {
                console.log('❌ No product_analyses for shop:', shopData.shop_name);
                return;
            }
            
            console.log(`📦 Found ${analysis.product_analyses.length} products for shop:`, shopData.shop_name);
            
            analysis.product_analyses.forEach((product, productIndex) => {
                console.log(`📝 Product ${productIndex + 1}:`, product.present_name || 'No name');
                
                // Find best accuracy across all adapt levels for this product
                let bestAccuracy = 0;
                let bestAdapt = '';
                
                ['adapt_1', 'adapt_2', 'adapt_3'].forEach(adapt => {
                    const acc = product.accuracy_analysis && product.accuracy_analysis[adapt];
                    if (acc && acc.predicted !== null && acc.accuracy_percent !== null) {
                        console.log(`  ${adapt}: ${acc.accuracy_percent}%`);
                        if (acc.accuracy_percent > bestAccuracy) {
                            bestAccuracy = acc.accuracy_percent;
                            bestAdapt = adapt;
                        }
                    }
                });
                
                if (bestAccuracy >= 0) { // Changed from > 0 to >= 0 to include 0% accuracy
                    const productData = {
                        name: product.present_name || product.nav_name || 'Ukendt Produkt',
                        model: product.model_name || '',
                        shop: shopData.shop_name,
                        accuracy: bestAccuracy,
                        adapt: bestAdapt,
                        actual: product.actual_quantity || 0,
                        predicted: product.accuracy_analysis[bestAdapt]?.predicted || 0,
                        orders: product.order_count || 0
                    };
                    
                    allProducts.push(productData);
                    console.log('✅ Added product:', productData.name, `(${productData.accuracy}%)`);
                } else {
                    console.log('❌ No valid accuracy found for product:', product.present_name);
                }
            });
        });
        
        console.log(`📊 Total products collected: ${allProducts.length}`);

        if (allProducts.length === 0) {
            console.warn('⚠️ No product data available for top/bottom chart');
            // Create a simple message instead of another chart
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.font = '16px Arial';
            ctx.fillStyle = '#666';
            ctx.textAlign = 'center';
            ctx.fillText('Ingen produkt data tilgængelig', canvas.width / 2, canvas.height / 2);
            return;
        }

        // Sort by accuracy and get top 10 and bottom 10
        const sortedProducts = allProducts.sort((a, b) => b.accuracy - a.accuracy);
        const top10 = sortedProducts.slice(0, 10);
        const bottom10 = sortedProducts.slice(-10).reverse(); // Reverse so worst is first

        // Combine data with separation
        const chartLabels = [
            ...top10.map(p => `${p.name} (${p.shop})`),
            '--- SEPARATOR ---',
            ...bottom10.map(p => `${p.name} (${p.shop})`)
        ];

        const chartData = [
            ...top10.map(p => p.accuracy),
            null, // Gap for separator
            ...bottom10.map(p => p.accuracy)
        ];

        const backgroundColors = [
            ...top10.map((_, index) => {
                if (index === 0) return 'rgba(39, 174, 96, 0.9)'; // #1 Dark Green
                if (index < 3) return 'rgba(39, 174, 96, 0.7)'; // Top 3 Green
                return 'rgba(52, 152, 219, 0.6)'; // Rest Blue
            }),
            'transparent', // Separator
            ...bottom10.map(() => 'rgba(231, 76, 60, 0.8)') // Bottom 10 Red
        ];

        try {
            this.charts.topBottomProducts = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: chartLabels,
                    datasets: [{
                        label: 'Forecast Accuracy (%)',
                        data: chartData,
                        backgroundColor: backgroundColors,
                        borderWidth: 2,
                        borderRadius: 4
                    }]
                },
                options: {
                    indexAxis: 'y', // Horizontal bars
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        },
                        title: {
                            display: true,
                            text: `Top 10 & Bottom 10 Produkter (${allProducts.length} total produkter)`
                        },
                        tooltip: {
                            callbacks: {
                                afterBody: function(context) {
                                    const index = context[0].dataIndex;
                                    let product;
                                    
                                    if (index < 10) {
                                        product = top10[index];
                                    } else if (index > 10) {
                                        product = bottom10[index - 11];
                                    } else {
                                        return ['Separator'];
                                    }
                                    
                                    if (product) {
                                        return [
                                            `Bedste Adapt: ${product.adapt.toUpperCase()}`,
                                            `Forudsagt: ${product.predicted}`,
                                            `Faktisk: ${product.actual}`,
                                            `Ordrer: ${product.orders}`,
                                            `Shop: ${product.shop}`
                                        ];
                                    }
                                    return [];
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        },
                        y: {
                            ticks: {
                                maxRotation: 0,
                                callback: function(value, index) {
                                    const label = this.getLabelForValue(value);
                                    if (label.includes('SEPARATOR')) {
                                        return '───────────';
                                    }
                                    // Truncate long labels
                                    return label.length > 30 ? label.substring(0, 27) + '...' : label;
                                }
                            }
                        }
                    }
                }
            });
            console.log('✅ Top/Bottom products chart created');
        } catch (error) {
            console.error('❌ Error creating top/bottom products chart:', error);
            this.createNoDataChart(ctx, 'Fejl ved top/bottom products chart: ' + error.message);
        }
    }

    // NEW: Adapt Average Comparison
    createAdaptAverageChart(comparisonData) {
        const canvas = document.getElementById('adaptAverageChart');
        if (!canvas) {
            console.error('❌ Canvas element "adaptAverageChart" not found');
            return;
        }
        
        const ctx = canvas.getContext('2d');

        if (this.charts.adaptAverage) {
            this.charts.adaptAverage.destroy();
        }

        // Calculate averages for each adapt level
        const adaptLevels = ['adapt_1_accuracy', 'adapt_2_accuracy', 'adapt_3_accuracy'];
        const averages = adaptLevels.map(adaptLevel => {
            const values = comparisonData
                .map(shop => shop[adaptLevel])
                .filter(val => val !== null && val !== undefined && !isNaN(val));
            
            if (values.length > 0) {
                return values.reduce((sum, val) => sum + val, 0) / values.length;
            }
            return 0;
        });

        const labels = ['Adapt 1', 'Adapt 2', 'Adapt 3'];
        const colors = [
            'rgba(255, 107, 107, 0.8)', // Red
            'rgba(254, 202, 87, 0.8)',  // Yellow
            'rgba(72, 219, 251, 0.8)'   // Blue
        ];

        try {
            this.charts.adaptAverage = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Gennemsnitlig Accuracy (%)',
                        data: averages.map(avg => Math.round(avg * 100) / 100),
                        backgroundColor: colors,
                        borderWidth: 2,
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        },
                        title: {
                            display: true,
                            text: `Gennemsnitlig Performance per Adapt Level (${comparisonData.length} shops)`
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        }
                    }
                }
            });
            console.log('✅ Adapt average chart created');
        } catch (error) {
            console.error('❌ Error creating average chart:', error);
            this.createNoDataChart(ctx, 'Fejl ved average chart: ' + error.message);
        }
    }

    // NEW: Top & Bottom Performers
    createTopBottomChart(comparisonData) {
        const canvas = document.getElementById('topBottomChart');
        if (!canvas) {
            console.error('❌ Canvas element "topBottomChart" not found');
            return;
        }
        
        const ctx = canvas.getContext('2d');

        if (this.charts.topBottom) {
            this.charts.topBottom.destroy();
        }

        // Sort and get top 10 and bottom 10
        const sortedData = [...comparisonData].sort((a, b) => b.avg_accuracy - a.avg_accuracy);
        const top10 = sortedData.slice(0, 10);
        const bottom10 = sortedData.slice(-10).reverse(); // Reverse so worst is first

        // Combine data with separation
        const chartLabels = [
            ...top10.map(shop => shop.shop_name),
            '---',
            ...bottom10.map(shop => shop.shop_name)
        ];

        const chartData = [
            ...top10.map(shop => shop.avg_accuracy),
            null, // Gap for separator
            ...bottom10.map(shop => shop.avg_accuracy)
        ];

        const backgroundColors = [
            ...top10.map((_, index) => {
                if (index === 0) return 'rgba(39, 174, 96, 0.8)'; // #1 Green
                if (index < 3) return 'rgba(52, 152, 219, 0.8)'; // Top 3 Blue
                return 'rgba(149, 165, 166, 0.6)'; // Rest Gray
            }),
            'transparent', // Separator
            ...bottom10.map(() => 'rgba(231, 76, 60, 0.8)') // Bottom 10 Red
        ];

        try {
            this.charts.topBottom = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: chartLabels,
                    datasets: [{
                        label: 'Accuracy (%)',
                        data: chartData,
                        backgroundColor: backgroundColors,
                        borderWidth: 2,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        },
                        title: {
                            display: true,
                            text: 'Top 10 & Bottom 10 Shop Performance'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        },
                        x: {
                            ticks: {
                                maxRotation: 45,
                                minRotation: 45
                            }
                        }
                    }
                }
            });
            console.log('✅ Top/Bottom chart created');
        } catch (error) {
            console.error('❌ Error creating top/bottom chart:', error);
            this.createNoDataChart(ctx, 'Fejl ved top/bottom chart: ' + error.message);
        }
    }

    createAdaptComparisonChart(data) {
        const canvas = document.getElementById('adaptComparisonChart');
        if (!canvas) {
            console.error('❌ Canvas element "adaptComparisonChart" not found');
            return;
        }
        
        const ctx = canvas.getContext('2d');

        if (this.charts.adaptComparison) {
            this.charts.adaptComparison.destroy();
        }

        // Ensure data has the expected structure
        if (!data || typeof data !== 'object') {
            console.error('❌ Invalid adapt performance data structure:', data);
            // Create a simple "No data" chart
            this.createNoDataChart(ctx, 'Ingen adapt performance data tilgængelig');
            return;
        }

        // Get all unique shops from all adapts
        const allShopSets = Object.values(data).filter(adaptData => Array.isArray(adaptData));
        
        if (allShopSets.length === 0) {
            console.warn('⚠️ No valid adapt data arrays found');
            this.createNoDataChart(ctx, 'Ingen gyldige adapt data fundet');
            return;
        }

        const shops = [...new Set(allShopSets.flat().map(item => item.shop))];

        if (shops.length === 0) {
            console.warn('⚠️ No shops found in adapt performance data');
            this.createNoDataChart(ctx, 'Ingen shops fundet i data');
            return;
        }
        
        console.log('🏪 CHART WILL SHOW SHOPS:', shops);

        const colors = [
            'rgba(255, 107, 107, 0.8)',
            'rgba(254, 202, 87, 0.8)',
            'rgba(72, 219, 251, 0.8)'
        ];

        const datasets = Object.keys(data).map((adapt, index) => {
            const adaptData = data[adapt] || [];
            
            const chartData = shops.map(shop => {
                const item = adaptData.find(d => d.shop === shop);
                return item ? item.accuracy : 0;
            });
            
            console.log(`📊 ${adapt.toUpperCase()} DATA:`, chartData);
            
            return {
                label: adapt.replace('_', ' ').toUpperCase(),
                data: chartData,
                backgroundColor: colors[index] || 'rgba(128, 128, 128, 0.8)',
                borderColor: (colors[index] || 'rgba(128, 128, 128, 0.8)').replace('0.8', '1'),
                borderWidth: 2
            };
        });

        console.log('🎯 CREATING CHART WITH LABELS:', shops);
        console.log('🎯 CREATING CHART WITH DATASETS:', datasets);

        try {
            this.charts.adaptComparison = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: shops,
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top'
                        },
                        title: {
                            display: true,
                            text: 'Adapt Field Performance per Shop'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        }
                    }
                }
            });
            console.log('✅ CHART CREATED SUCCESSFULLY');
        } catch (error) {
            console.error('❌ Error creating Chart.js:', error);
            this.createNoDataChart(ctx, 'Fejl ved oprettelse af chart: ' + error.message);
            return;
        }
    }

    populateProductTable(products) {
        const tbody = document.getElementById('productTableBody');
        if (!tbody) return;

        tbody.innerHTML = '';

        products.forEach(product => {
            const row = tbody.insertRow();

            // Product name cell
            const nameCell = row.insertCell();
            nameCell.innerHTML = `
                <strong>${product.nav_name || product.present_name || 'N/A'}</strong><br>
                <small>${product.model_name || 'N/A'}</small><br>
                <small class="text-muted">ID: ${product.present_id} | ${product.model_present_no || 'N/A'}</small>
            `;

            // Type cell (Internal/External)
            const typeCell = row.insertCell();
            const isExternal = product.is_external && product.is_external > 0;
            typeCell.innerHTML = `
                <span class="${isExternal ? 'text-warning' : 'text-success'}">
                    ${isExternal ? '🌐 Ekstern' : '🏠 Intern'}
                </span>
                ${product.navision_type ? `<br><small class="text-muted">${product.navision_type}</small>` : ''}
            `;

            // Status cell
            const statusCell = row.insertCell();
            let statusHTML = '';
            if (product.is_close && product.is_close === 1) {
                statusHTML += '<span class="text-danger">🔒 Lukket</span><br>';
            } else {
                statusHTML += '<span class="text-success">✅ Aktiv</span><br>';
            }
            if (product.autotopilot && product.autotopilot === 1) {
                statusHTML += '<small class="text-info">🤖 Autopilot</small>';
            }
            if (product.warning_level && product.warning_level > 0) {
                statusHTML += `<br><small class="text-warning">⚠️ Warning: ${product.warning_level}</small>`;
            }
            statusCell.innerHTML = statusHTML;

            // Order count cell
            const orderCell = row.insertCell();
            orderCell.innerHTML = `<strong>${product.order_count || 0}</strong>`;
            if (product.order_count >= 20) {
                orderCell.style.color = '#27ae60';
            } else if (product.order_count >= 10) {
                orderCell.style.color = '#f39c12';
            }

            // Reservation quantity
            row.insertCell().textContent = product.reservation_quantity;

            // Actual quantity
            const actualCell = row.insertCell();
            actualCell.textContent = product.actual_quantity;
            if (product.actual_quantity > product.reservation_quantity) {
                actualCell.style.color = '#e74c3c';
                actualCell.style.fontWeight = 'bold';
            }

            // Adapt cells
            ['adapt_1', 'adapt_2', 'adapt_3'].forEach(adapt => {
                const cell = row.insertCell();
                const analysis = product.accuracy_analysis[adapt];

                if (analysis.predicted !== null) {
                    const accuracyClass = `accuracy-${analysis.accuracy_category}`;
                    cell.innerHTML = `
                        <div>${analysis.predicted}</div>
                        <small class="${accuracyClass}">${Math.round(analysis.accuracy_percent)}%</small>
                    `;
                } else {
                    cell.innerHTML = '<span class="text-muted">-</span>';
                }
            });

            // Best forecast cell
            const bestCell = row.insertCell();
            const bestForecast = this.getBestForecast(product.accuracy_analysis);
            if (bestForecast) {
                bestCell.innerHTML = `<span class="hit-${bestForecast.hit_rate}">${bestForecast.adapt.toUpperCase()}</span>`;
            } else {
                bestCell.innerHTML = '<span class="text-muted">-</span>';
            }

            // Store product data for filtering
            row.dataset.productData = JSON.stringify({
                name: (product.nav_name || product.present_name || '').toLowerCase(),
                isExternal: isExternal,
                isClosed: product.is_close === 1,
                isAutopilot: product.autotopilot === 1,
                accuracyCategories: ['adapt_1', 'adapt_2', 'adapt_3'].map(adapt => 
                    product.accuracy_analysis[adapt].accuracy_category
                )
            });

            row.style.cursor = 'pointer';
            row.addEventListener('click', () => {
                this.showProductDetails(product);
            });
        });
    }

    populateShopRankingTable(comparisonData) {
        const tbody = document.getElementById('shopRankingBody');
        if (!tbody) return;

        tbody.innerHTML = '';
        
        // Store the original data for filtering and sorting
        this.originalShopRankingData = comparisonData;

        // ComparisonData is already processed and sorted by rank
        comparisonData.forEach((shop, index) => {
            const row = tbody.insertRow();
            
            // Store shop data in row for filtering and sidebar
            row.dataset.shopData = JSON.stringify(shop);
            row.dataset.shopId = shop.shop_id;
            row.dataset.shopName = shop.shop_name;
            row.dataset.avgAccuracy = shop.avg_accuracy;
            row.dataset.totalProducts = shop.total_products;

            const rankCell = row.insertCell();
            rankCell.textContent = index + 1;
            if (index < 3) {
                rankCell.style.fontWeight = 'bold';
                rankCell.style.color = index === 0 ? '#f1c40f' : index === 1 ? '#95a5a6' : '#cd7f32';
            }

            row.insertCell().textContent = shop.shop_name;

            const avgCell = row.insertCell();
            avgCell.textContent = `${shop.avg_accuracy}%`;
            avgCell.className = this.getAccuracyClass(shop.avg_accuracy);

            // Individual adapt accuracies
            const adapt1Cell = row.insertCell();
            adapt1Cell.textContent = shop.adapt_1_accuracy > 0 ? `${shop.adapt_1_accuracy}%` : '-';
            adapt1Cell.className = shop.adapt_1_accuracy > 0 ? this.getAccuracyClass(shop.adapt_1_accuracy) : 'text-muted';

            const adapt2Cell = row.insertCell();
            adapt2Cell.textContent = shop.adapt_2_accuracy > 0 ? `${shop.adapt_2_accuracy}%` : '-';
            adapt2Cell.className = shop.adapt_2_accuracy > 0 ? this.getAccuracyClass(shop.adapt_2_accuracy) : 'text-muted';

            const adapt3Cell = row.insertCell();
            adapt3Cell.textContent = shop.adapt_3_accuracy > 0 ? `${shop.adapt_3_accuracy}%` : '-';
            adapt3Cell.className = shop.adapt_3_accuracy > 0 ? this.getAccuracyClass(shop.adapt_3_accuracy) : 'text-muted';

            row.insertCell().textContent = shop.total_products;
            
            // Add click event to row
            row.addEventListener('click', (e) => {
                console.log('🖱️ Shop row clicked:', shop.shop_name, shop.shop_id);
                e.preventDefault();
                e.stopPropagation();
                this.openShopSidebar(shop.shop_id, shop.shop_name);
            });
        });
    }

    getBestForecast(accuracyAnalysis) {
        let best = null;
        let bestAccuracy = -1;

        ['adapt_1', 'adapt_2', 'adapt_3'].forEach(adapt => {
            const analysis = accuracyAnalysis[adapt];
            if (analysis.predicted !== null && analysis.accuracy_percent > bestAccuracy) {
                bestAccuracy = analysis.accuracy_percent;
                best = {
                    adapt: adapt,
                    accuracy: analysis.accuracy_percent,
                    hit_rate: analysis.hit_rate
                };
            }
        });

        return best;
    }

    getAccuracyClass(accuracy) {
        if (accuracy >= 90) return 'accuracy-excellent';
        if (accuracy >= 75) return 'accuracy-good';
        if (accuracy >= 50) return 'accuracy-fair';
        return 'accuracy-poor';
    }

    filterProductTable() {
        const tbody = document.getElementById('productTableBody');
        if (!tbody) return;

        const searchTerm = document.getElementById('productSearch')?.value.toLowerCase() || '';
        const accuracyFilter = document.getElementById('accuracyFilter')?.value || '';
        const typeFilter = document.getElementById('typeFilter')?.value || '';
        const statusFilter = document.getElementById('statusFilter')?.value || '';

        const rows = tbody.querySelectorAll('tr');

        rows.forEach(row => {
            const productDataStr = row.dataset.productData;
            if (!productDataStr) return;

            const productData = JSON.parse(productDataStr);

            // Search filter
            const matchesSearch = !searchTerm || productData.name.includes(searchTerm);

            // Accuracy filter
            let matchesAccuracy = true;
            if (accuracyFilter) {
                matchesAccuracy = productData.accuracyCategories.includes(accuracyFilter);
            }

            // Type filter
            let matchesType = true;
            if (typeFilter === 'internal') {
                matchesType = !productData.isExternal;
            } else if (typeFilter === 'external') {
                matchesType = productData.isExternal;
            }

            // Status filter
            let matchesStatus = true;
            if (statusFilter === 'active') {
                matchesStatus = !productData.isClosed;
            } else if (statusFilter === 'closed') {
                matchesStatus = productData.isClosed;
            } else if (statusFilter === 'autopilot') {
                matchesStatus = productData.isAutopilot;
            }

            const showRow = matchesSearch && matchesAccuracy && matchesType && matchesStatus;
            row.style.display = showRow ? '' : 'none';
        });
    }

    async showProductDetails(product) {
        try {
            const params = new URLSearchParams({
                shop_id: this.currentShopId,
                present_id: product.present_id,
                model_id: product.model_id
            });

            const url = this.baseUrl + 'getProductDetails&' + params.toString();
            console.log('🔍 Getting product details from:', url);

            const response = await fetch(url);
            const result = await response.json();

            if (result.status === 1) {
                this.displayProductModal(product, result.data);
            } else {
                this.showError('Fejl ved hentning af produktdetaljer');
            }
        } catch (error) {
            console.error('❌ Error getting product details:', error);
            this.showError('Netværksfejl ved hentning af produktdetaljer: ' + error.message);
        }
    }

    displayProductModal(product, details) {
        const modalTitle = document.getElementById('modalProductTitle');
        const modalBody = document.getElementById('modalBody');

        if (modalTitle) {
            modalTitle.textContent = `${product.nav_name || product.present_name} - ${product.model_name}`;
        }

        if (modalBody) {
            modalBody.innerHTML = `
                <div class="product-detail-section">
                    <h4>📋 Produkt Information</h4>
                    <div class="info-grid">
                        <div><strong>Present ID:</strong> ${product.present_id || 'N/A'}</div>
                        <div><strong>Navision Navn:</strong> ${product.nav_name || 'N/A'}</div>
                        <div><strong>Type:</strong> ${product.is_external && product.is_external > 0 ? '🌐 Ekstern' : '🏠 Intern'}</div>
                        <div><strong>Status:</strong> ${product.is_close === 1 ? '🔒 Lukket' : '✅ Aktiv'}</div>
                        <div><strong>Autopilot:</strong> ${product.autotopilot === 1 ? '🤖 Ja' : '❌ Nej'}</div>
                        <div><strong>Ordrer:</strong> ${product.order_count || 0}</div>
                        <div><strong>Navision Type:</strong> ${product.navision_type || 'N/A'}</div>
                        <div><strong>Warning Level:</strong> ${product.warning_level || 'N/A'}</div>
                    </div>
                </div>

                <div class="product-detail-section">
                    <h4>📊 Original Forecast Analyse</h4>
                    <div class="forecast-grid">
                        ${['adapt_1', 'adapt_2', 'adapt_3'].map(adapt => {
                const analysis = product.accuracy_analysis[adapt];
                return `
                                <div class="forecast-card ${adapt}">
                                    <div class="forecast-title">${adapt.replace('_', ' ').toUpperCase()}</div>
                                    <div class="forecast-values">
                                        <div>Forudsagt: <strong>${analysis.predicted || 'N/A'}</strong></div>
                                        <div>Faktisk: <strong>${analysis.actual}</strong></div>
                                        <div>Forskel: <strong>${analysis.difference || 'N/A'}</strong></div>
                                    </div>
                                    <div class="forecast-accuracy ${this.getAccuracyClass(analysis.accuracy_percent)}">
                                        ${Math.round(analysis.accuracy_percent || 0)}% nøjagtighed
                                    </div>
                                    <div class="forecast-rating">
                                        <span class="hit-${analysis.hit_rate}">${this.getHitRateText(analysis.hit_rate)}</span>
                                    </div>
                                </div>
                            `;
            }).join('')}
                    </div>
                </div>

                ${product.autopilot_analysis && Object.keys(product.autopilot_analysis).length > 0 ? `
                <div class="product-detail-section">
                    <h4>🤖 Autopilot Justeret Forecast</h4>
                    <div class="forecast-grid">
                        ${['adapt_1', 'adapt_2', 'adapt_3'].map(adapt => {
                const autopilot = product.autopilot_analysis[adapt];
                if (!autopilot) return '';
                const analysis = autopilot.accuracy;
                return `
                                <div class="forecast-card ${adapt}">
                                    <div class="forecast-title">${adapt.replace('_', ' ').toUpperCase()} (${autopilot.forecast_procent}x)</div>
                                    <div class="forecast-values">
                                        <div>Original: <strong>${autopilot.original_predicted || 'N/A'}</strong></div>
                                        <div>Justeret: <strong>${autopilot.adjusted_predicted || 'N/A'}</strong></div>
                                        <div>Faktisk: <strong>${analysis.actual}</strong></div>
                                        <div>Grund: <small>${this.getAdjustmentReasonText(autopilot.adjustment_reason)}</small></div>
                                    </div>
                                    <div class="forecast-accuracy ${this.getAccuracyClass(analysis.accuracy_percent)}">
                                        ${Math.round(analysis.accuracy_percent || 0)}% nøjagtighed
                                    </div>
                                    <div class="forecast-rating">
                                        <span class="hit-${analysis.hit_rate}">${this.getHitRateText(analysis.hit_rate)}</span>
                                    </div>
                                </div>
                            `;
            }).join('')}
                    </div>
                </div>
                ` : ''}

                ${details.adapt_history && details.adapt_history.length > 0 ? `
                <div class="product-detail-section">
                    <h4>📈 Adapt Historik</h4>
                    <div class="order-history">
                        ${details.adapt_history.map(entry => `
                            <div class="order-day">
                                <span>${new Date(entry.created).toLocaleDateString('da-DK')}</span>
                                <span>A0:${entry.adapt_0||'N/A'} A1:${entry.adapt_1||'N/A'} A2:${entry.adapt_2||'N/A'} A3:${entry.adapt_3||'N/A'}</span>
                                <span>Qty: ${entry.quantity}${entry.autotopilot ? ' 🤖' : ''}</span>
                            </div>
                        `).join('')}
                    </div>
                </div>
                ` : ''}

                ${details.order_history && details.order_history.length > 0 ? `
                <div class="product-detail-section">
                    <h4>📅 Ordre Historik</h4>
                    <div class="order-history">
                        ${details.order_history.map(day => `
                            <div class="order-day">
                                <span>${day.order_date}</span>
                                <span>${day.daily_orders} ordrer</span>
                            </div>
                        `).join('')}
                    </div>
                </div>
                ` : ''}
            `;
        }

        document.getElementById('productModal').style.display = 'block';
    }

    getHitRateText(hitRate) {
        const texts = {
            'exact': 'Præcis',
            'close': 'Tæt på',
            'good': 'God',
            'miss': 'Ramt forbi'
        };
        return texts[hitRate] || 'N/A';
    }

    getAdjustmentReasonText(reason) {
        const texts = {
            'protected_external': 'Ekstern - beskyttet',
            'zero_selected': 'Nul ordrer - justeret',
            'forecast_procent': 'Procent multiplier',
            'low_selection_boost': 'Lav % valgt boost',
            'none': 'Ingen justering'
        };
        return texts[reason] || reason;
    }

    closeModal() {
        document.getElementById('productModal').style.display = 'none';
    }

    showLoading(show) {
        const loading = document.getElementById('loading');
        if (loading) {
            loading.style.display = show ? 'block' : 'none';
        }
    }

    showError(message) {
        const errorEl = document.getElementById('errorMessage');
        if (errorEl) {
            errorEl.textContent = message;
            errorEl.style.display = 'block';
        }
        console.error('🚨 ERROR:', message);
    }

    hideError() {
        const errorEl = document.getElementById('errorMessage');
        if (errorEl) {
            errorEl.style.display = 'none';
        }
    }

    filterShopRankingTable() {
        const tbody = document.getElementById('shopRankingBody');
        if (!tbody) return;

        const searchTerm = document.getElementById('shopRankingSearch')?.value.toLowerCase() || '';
        const accuracyFilter = document.getElementById('accuracyRangeFilter')?.value || '';
        const productCountFilter = document.getElementById('productCountFilter')?.value || '';

        const rows = tbody.querySelectorAll('tr');

        rows.forEach(row => {
            const shopName = row.dataset.shopName?.toLowerCase() || '';
            const avgAccuracy = parseFloat(row.dataset.avgAccuracy) || 0;
            const totalProducts = parseInt(row.dataset.totalProducts) || 0;

            // Search filter
            const matchesSearch = !searchTerm || shopName.includes(searchTerm);

            // Accuracy filter
            let matchesAccuracy = true;
            if (accuracyFilter === 'excellent') {
                matchesAccuracy = avgAccuracy >= 90;
            } else if (accuracyFilter === 'good') {
                matchesAccuracy = avgAccuracy >= 75 && avgAccuracy < 90;
            } else if (accuracyFilter === 'fair') {
                matchesAccuracy = avgAccuracy >= 50 && avgAccuracy < 75;
            } else if (accuracyFilter === 'poor') {
                matchesAccuracy = avgAccuracy < 50;
            }

            // Product count filter
            let matchesProductCount = true;
            if (productCountFilter === 'high') {
                matchesProductCount = totalProducts >= 20;
            } else if (productCountFilter === 'medium') {
                matchesProductCount = totalProducts >= 10 && totalProducts < 20;
            } else if (productCountFilter === 'low') {
                matchesProductCount = totalProducts < 10;
            }

            const showRow = matchesSearch && matchesAccuracy && matchesProductCount;
            row.style.display = showRow ? '' : 'none';
        });
    }

    sortShopRankingTable(sortBy) {
        const tbody = document.getElementById('shopRankingBody');
        if (!tbody || !this.originalShopRankingData) return;

        // Keep track of sort direction for each column
        if (!this.sortDirections) {
            this.sortDirections = {
                shop_name: 'asc',
                avg_accuracy: 'desc',
                adapt_1_accuracy: 'desc',
                adapt_2_accuracy: 'desc',
                adapt_3_accuracy: 'desc',
                total_products: 'desc'
            };
        }

        // Toggle sort direction for this column
        this.sortDirections[sortBy] = this.sortDirections[sortBy] === 'asc' ? 'desc' : 'asc';
        const isAscending = this.sortDirections[sortBy] === 'asc';

        let sortedData = [...this.originalShopRankingData];

        switch (sortBy) {
            case 'shop_name':
                sortedData.sort((a, b) => {
                    const result = a.shop_name.localeCompare(b.shop_name);
                    return isAscending ? result : -result;
                });
                break;
            case 'avg_accuracy':
                sortedData.sort((a, b) => {
                    const result = a.avg_accuracy - b.avg_accuracy;
                    return isAscending ? result : -result;
                });
                break;
            case 'adapt_1_accuracy':
                sortedData.sort((a, b) => {
                    const result = a.adapt_1_accuracy - b.adapt_1_accuracy;
                    return isAscending ? result : -result;
                });
                break;
            case 'adapt_2_accuracy':
                sortedData.sort((a, b) => {
                    const result = a.adapt_2_accuracy - b.adapt_2_accuracy;
                    return isAscending ? result : -result;
                });
                break;
            case 'adapt_3_accuracy':
                sortedData.sort((a, b) => {
                    const result = a.adapt_3_accuracy - b.adapt_3_accuracy;
                    return isAscending ? result : -result;
                });
                break;
            case 'total_products':
                sortedData.sort((a, b) => {
                    const result = a.total_products - b.total_products;
                    return isAscending ? result : -result;
                });
                break;
        }

        // Re-populate the table with sorted data and re-apply filters
        this.populateShopRankingTable(sortedData);
        
        // Re-apply current filters
        setTimeout(() => {
            this.filterShopRankingTable();
        }, 10);
    }

    async openShopSidebar(shopId, shopName) {
        console.log('🔍 Opening sidebar for shop:', shopId, shopName);
        
        const sidebar = document.getElementById('shopAnalysisSidebar');
        const sidebarTitle = document.getElementById('sidebarShopTitle');
        const sidebarContent = document.getElementById('sidebarContent');
        
        console.log('📋 Sidebar elements found:', {
            sidebar: !!sidebar,
            sidebarTitle: !!sidebarTitle,
            sidebarContent: !!sidebarContent
        });
        
        if (!sidebar || !sidebarContent) {
            console.error('❌ Sidebar elements not found');
            return;
        }

        // Set title
        if (sidebarTitle) {
            sidebarTitle.textContent = `${shopName} - Analyse`;
        }

        // Show loading
        sidebarContent.innerHTML = '<div class="loading">Indlæser shop analyse...</div>';
        console.log('✅ Loading content set');
        
        // Open sidebar
        sidebar.classList.add('open');
        console.log('✅ Sidebar opened');
        
        // Add backdrop
        let backdrop = document.querySelector('.sidebar-backdrop');
        if (!backdrop) {
            console.log('🆕 Creating new backdrop');
            backdrop = document.createElement('div');
            backdrop.className = 'sidebar-backdrop';
            document.body.appendChild(backdrop);
            
            backdrop.addEventListener('click', () => {
                console.log('🖱️ Backdrop clicked, closing sidebar');
                this.closeSidebar();
            });
        } else {
            console.log('♻️ Using existing backdrop');
        }
        backdrop.classList.add('active');
        console.log('✅ Backdrop activated');

        try {
            // Call the analyze API for this specific shop
            const params = new URLSearchParams({
                shop_id: shopId
            });

            const url = this.baseUrl + 'analyzeAdaptAccuracy&' + params.toString();
            console.log('🔍 Loading shop analysis for sidebar:', url);

            const response = await fetch(url);
            const result = await response.json();

            console.log('📊 API Response for sidebar:', result);

            if (result.status === 1) {
                console.log('✅ API success, displaying sidebar content');
                this.displayShopAnalysisInSidebar(result.data);
            } else {
                console.error('❌ API error:', result);
                sidebarContent.innerHTML = `<div class="error-message">Fejl ved indlæsning: ${result.error || 'Ukendt fejl'}</div>`;
            }
        } catch (error) {
            console.error('❌ Error loading shop analysis for sidebar:', error);
            sidebarContent.innerHTML = `<div class="error-message">Netværksfejl: ${error.message}</div>`;
        }
    }

    displayShopAnalysisInSidebar(data) {
        const sidebarContent = document.getElementById('sidebarContent');
        if (!sidebarContent) return;

        const analysis = data.analysis;
        const shopInfo = data.shop_info;

        sidebarContent.innerHTML = `
            <div class="product-detail-section">
                <h4>📋 Shop Information</h4>
                <div class="info-grid">
                    <div><strong>Shop:</strong> ${shopInfo.name || 'N/A'}</div>
                    <div><strong>Brugere:</strong> ${shopInfo.user_count || 'N/A'}</div>
                    <div><strong>Budget:</strong> ${shopInfo.budget || 'N/A'}</div>
                    <div><strong>Total Ordrer:</strong> ${shopInfo.total_orders || 0}</div>
                    <div><strong>% Valgt:</strong> ${shopInfo.procent_selected || 0}%</div>
                    <div><strong>Produkter Analyseret:</strong> ${analysis.total_products_analyzed || 0}</div>
                </div>
            </div>

            <div class="product-detail-section">
                <h4>📊 Original Forecast Accuracy</h4>
                <div class="forecast-grid">
                    ${['adapt_1', 'adapt_2', 'adapt_3'].map(adapt => {
                        const acc = analysis.overall_accuracy[adapt];
                        return `
                            <div class="forecast-card ${adapt}">
                                <div class="forecast-title">${adapt.replace('_', ' ').toUpperCase()}</div>
                                <div class="forecast-values">
                                    <div>Korrekte: <strong>${acc.correct}</strong></div>
                                    <div>Total: <strong>${acc.total}</strong></div>
                                    <div>Accuracy: <strong>${acc.accuracy_percent}%</strong></div>
                                </div>
                                <div class="forecast-accuracy ${this.getAccuracyClass(acc.accuracy_percent)}">
                                    ${this.getAccuracyClass(acc.accuracy_percent).replace('accuracy-', '').toUpperCase()}
                                </div>
                            </div>
                        `;
                    }).join('')}
                </div>
            </div>

            ${analysis.autopilot_overall_accuracy && Object.keys(analysis.autopilot_overall_accuracy).some(k => analysis.autopilot_overall_accuracy[k].total > 0) ? `
            <div class="product-detail-section">
                <h4>🤖 Autopilot Forecast Accuracy</h4>
                <div class="forecast-grid">
                    ${['adapt_1', 'adapt_2', 'adapt_3'].map(adapt => {
                        const acc = analysis.autopilot_overall_accuracy[adapt];
                        if (acc.total === 0) return '';
                        return `
                            <div class="forecast-card ${adapt}">
                                <div class="forecast-title">${adapt.replace('_', ' ').toUpperCase()}</div>
                                <div class="forecast-values">
                                    <div>Korrekte: <strong>${acc.correct}</strong></div>
                                    <div>Total: <strong>${acc.total}</strong></div>
                                    <div>Accuracy: <strong>${acc.accuracy_percent}%</strong></div>
                                </div>
                                <div class="forecast-accuracy ${this.getAccuracyClass(acc.accuracy_percent)}">
                                    ${this.getAccuracyClass(acc.accuracy_percent).replace('accuracy-', '').toUpperCase()}
                                </div>
                            </div>
                        `;
                    }).join('')}
                </div>
            </div>
            ` : ''}

            <div class="product-detail-section">
                <h4>📈 Top Produkter (Første 10)</h4>
                <div style="max-height: 300px; overflow-y: auto;">
                    ${analysis.product_analyses.slice(0, 10).map(product => `
                        <div style="border: 1px solid #eee; border-radius: 5px; padding: 10px; margin-bottom: 10px; font-size: 0.9rem;">
                            <div><strong>${product.present_name}</strong></div>
                            <div style="color: #666; font-size: 0.8rem;">${product.model_name}</div>
                            <div style="margin-top: 5px;">
                                Faktisk: <strong>${product.actual_quantity}</strong> | 
                                Reserveret: <strong>${product.reservation_quantity}</strong> |
                                Ordrer: <strong>${product.order_count}</strong>
                            </div>
                            <div style="margin-top: 5px;">
                                ${['adapt_1', 'adapt_2', 'adapt_3'].map(adapt => {
                                    const acc = product.accuracy_analysis[adapt];
                                    if (!acc.predicted) return '';
                                    return `<span class="${this.getAccuracyClass(acc.accuracy_percent)}" style="margin-right: 10px; font-size: 0.8rem;">
                                        ${adapt.replace('_', ' ').toUpperCase()}: ${Math.round(acc.accuracy_percent)}%
                                    </span>`;
                                }).join('')}
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
    }

    createNoDataChart(ctx, message) {
        console.log('📊 Creating no-data chart with message:', message);
        
        try {
            // Clear canvas first
            ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);
            
            const chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Ingen data'],
                    datasets: [{
                        label: 'Data',
                        data: [0],
                        backgroundColor: 'rgba(128, 128, 128, 0.3)',
                        borderColor: 'rgba(128, 128, 128, 0.5)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        },
                        title: {
                            display: true,
                            text: message,
                            color: '#666',
                            font: {
                                size: 14
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        }
                    }
                }
            });
            
            console.log('✅ No-data chart created successfully');
            return chart;
        } catch (error) {
            console.error('❌ Error creating no-data chart:', error);
            // Fallback to simple canvas drawing
            ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height);
            ctx.font = '16px Arial';
            ctx.fillStyle = '#666';
            ctx.textAlign = 'center';
            ctx.fillText(message, ctx.canvas.width / 2, ctx.canvas.height / 2);
            return null;
        }
    }

    sortByMaxAdapt() {
        if (!this.originalShopRankingData) {
            console.warn('⚠️ No ranking data available for max adapt sorting');
            return;
        }

        const sortedData = [...this.originalShopRankingData].sort((a, b) => {
            // Get the highest adapt accuracy for each shop
            const getMaxAdapt = (shop) => {
                return Math.max(
                    shop.adapt_3_accuracy || 0,
                    shop.adapt_2_accuracy || 0,
                    shop.adapt_1_accuracy || 0
                );
            };

            const aMax = getMaxAdapt(a);
            const bMax = getMaxAdapt(b);
            
            // Sort by highest max adapt (descending)
            return bMax - aMax;
        });

        console.log('🔢 Sorted by max adapt:', sortedData.map(s => ({
            name: s.shop_name,
            maxAdapt: Math.max(s.adapt_3_accuracy || 0, s.adapt_2_accuracy || 0, s.adapt_1_accuracy || 0)
        })));

        // Re-populate table and apply filters
        this.populateShopRankingTable(sortedData);
        setTimeout(() => {
            this.filterShopRankingTable();
        }, 10);
    }

    closeSidebar() {
        console.log('🚪 Closing sidebar');
        const sidebar = document.getElementById('shopAnalysisSidebar');
        const backdrop = document.querySelector('.sidebar-backdrop');
        
        if (sidebar) {
            sidebar.classList.remove('open');
            console.log('✅ Sidebar closed');
        }
        
        if (backdrop) {
            backdrop.classList.remove('active');
            console.log('✅ Backdrop deactivated');
        }
    }

    hideAllSections() {
        const singleShopSection = document.getElementById('singleShopSection');
        const comparisonSection = document.getElementById('comparisonSection');

        if (singleShopSection) singleShopSection.style.display = 'none';
        if (comparisonSection) comparisonSection.style.display = 'none';
    }
}

// Make available globally
window.AdaptAccuracyAnalyzer = AdaptAccuracyAnalyzer;