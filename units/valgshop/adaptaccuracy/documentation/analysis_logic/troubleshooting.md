# Troubleshooting Guide - Adapt Accuracy Analysis

## 🚨 Common Issues and Solutions

This guide provides comprehensive troubleshooting solutions for the Adapt Accuracy Analysis system.

---

## 🔧 Setup and Configuration Issues

### 1. Controller URL Not Found

**Symptom:** JavaScript errors about undefined CONTROLLER_URL or 404 errors on API calls like "Invalid unit path: performAnalysis".

**Possible Causes:**
- PHP configuration issue with GFConfig::BACKEND_URL
- Incorrect routing setup
- Missing controller file

**Solutions:**
```php
// Check in view.php that this line is present and correct:
window.CONTROLLER_URL = '<?php echo $CONTROLLER_URL; ?>';

// Verify $CONTROLLER_URL is properly set:
$CONTROLLER_URL = \GFConfig::BACKEND_URL."index.php?rt=unit/valgshop/adaptaccuracy/";

// Debug by adding this in view.php:
console.log('Controller URL:', '<?php echo $CONTROLLER_URL; ?>');
```

**Verification Steps:**
1. Check browser console for the controller URL value
2. Manually visit the URL in browser to test accessibility
3. Verify file permissions on controller.php

### 2. Database Connection Issues

**Symptom:** SQL errors or "Database connection failed" messages.

**Common SQL Errors:**
```sql
-- Error: Unknown column 'p.language_id' in 'on clause'
-- Solution: Remove language_id condition from present table JOIN

-- Before (incorrect):
LEFT JOIN gavefabrikken2024.present p 
    ON pr.present_id = p.id AND p.language_id = 1

-- After (correct):
LEFT JOIN gavefabrikken2024.present p 
    ON pr.present_id = p.id
```

**Database Debugging:**
```php
// Add to controller.php for debugging:
try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Database connection successful";
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['status' => 0, 'message' => 'Database error', 'debug' => $e->getMessage()]);
}
```

### 3. Missing Dependencies

**Symptom:** JavaScript errors about undefined Chart or other missing functions.

**Solutions:**
```html
<!-- Ensure Chart.js is loaded before your scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Verify in browser console: -->
<script>
if (typeof Chart === 'undefined') {
    console.error('Chart.js not loaded!');
} else {
    console.log('Chart.js version:', Chart.version);
}
</script>
```

---

## 📊 Data Issues

### 1. No Data Returned / Empty Results

**Symptom:** Analysis returns no products or "No reservations found" message.

**Debugging Steps:**
```sql
-- Check if shop has reservations:
SELECT COUNT(*) FROM gavefabrikken2024.present_reservation WHERE shop_id = YOUR_SHOP_ID;

-- Check if reservations have predictions:
SELECT COUNT(*) FROM gavefabrikken2024.present_reservation 
WHERE shop_id = YOUR_SHOP_ID 
AND (adapt_1 IS NOT NULL OR adapt_2 IS NOT NULL OR adapt_3 IS NOT NULL);

-- Check order counts:
SELECT pr.present_id, pr.model_id, COUNT(o.id) as order_count
FROM gavefabrikken2024.present_reservation pr
LEFT JOIN gavefabrikken2024.order o ON o.present_id = pr.present_id 
    AND o.present_model_id = pr.model_id 
    AND o.shop_id = pr.shop_id 
    AND o.is_demo = 0
WHERE pr.shop_id = YOUR_SHOP_ID
GROUP BY pr.present_id, pr.model_id
HAVING order_count >= 5;
```

**Common Solutions:**
1. **Minimum Order Filter:** Products need ≥5 orders to be analyzed
2. **Missing Predictions:** Ensure adapt_1/2/3 fields have values
3. **Demo Orders:** Verify is_demo = 0 filter is working

### 2. Accuracy Calculations Seem Wrong

**Symptom:** Accuracy percentages don't match expected values.

**Debug Accuracy Calculation:**
```php
// Add debug output in calculateAccuracy function:
function calculateAccuracy($predicted, $actual) {
    error_log("Accuracy calc: predicted=$predicted, actual=$actual");
    
    if ($predicted <= 0) {
        error_log("Predicted is 0 or negative, returning 0");
        return 0;
    }
    
    $difference = abs($predicted - $actual);
    $accuracy = max(0, (1 - ($difference / max($predicted, $actual))) * 100);
    
    error_log("Difference=$difference, Accuracy=$accuracy");
    return round($accuracy, 2);
}
```

**Verification Examples:**
```php
// Test cases:
echo calculateAccuracy(10, 10); // Should be 100%
echo calculateAccuracy(10, 8);  // Should be 80%
echo calculateAccuracy(8, 10);  // Should be 80%
echo calculateAccuracy(10, 0);  // Should be 0%
```

### 3. Autopilot Adjustments Not Working

**Symptom:** Autopilot forecasts are identical to original forecasts.

**Debug Autopilot Logic:**
```php
// Add debug output in autopilot functions:
private function getAdaptStage($total_orders, $procent_selected) {
    error_log("Adapt stage calc: orders=$total_orders, percent=$procent_selected");
    
    $adapt = 0;
    if ($total_orders < 500) {
        if ($procent_selected > 20) $adapt = 1;
        if ($procent_selected > 40) $adapt = 2;
        if ($procent_selected > 50) $adapt = 3;
    } // ... etc
    
    error_log("Calculated adapt stage: $adapt");
    return $adapt;
}
```

**Common Issues:**
1. **Shop Info Missing:** Verify total_orders and procent_selected are calculated correctly
2. **External Products:** Check that is_external products are properly protected
3. **Zero Forecast Logic:** Ensure zero selected products get proper handling

---

## 🎨 Frontend Issues

### 1. Charts Not Displaying

**Symptom:** Empty chart containers or JavaScript errors.

**Debugging Steps:**
```javascript
// Check Chart.js availability:
if (typeof Chart === 'undefined') {
    console.error('Chart.js not loaded');
}

// Check canvas elements exist:
const canvas = document.getElementById('accuracyChart');
if (!canvas) {
    console.error('Chart canvas not found');
}

// Check context creation:
const ctx = canvas.getContext('2d');
if (!ctx) {
    console.error('Cannot get 2D context');
}

// Add error handling to chart creation:
try {
    this.charts.accuracy = new Chart(ctx, chartConfig);
} catch (error) {
    console.error('Chart creation failed:', error);
}
```

**Common Solutions:**
1. **Canvas Size:** Ensure canvas has proper width/height
2. **Data Format:** Verify data arrays have correct structure
3. **Chart Destruction:** Destroy existing charts before creating new ones

### 2. Table Not Populating

**Symptom:** Product table remains empty despite having data.

**Debug Table Population:**
```javascript
populateProductTable(products) {
    console.log('Populating table with products:', products);
    
    if (!products || products.length === 0) {
        console.warn('No products to display');
        return;
    }
    
    const tbody = document.getElementById('productTableBody');
    if (!tbody) {
        console.error('Table body element not found');
        return;
    }
    
    products.forEach((product, index) => {
        console.log(`Processing product ${index}:`, product);
        const row = this.createProductRow(product);
        tbody.appendChild(row);
    });
}
```

### 3. Filters Not Working

**Symptom:** Search and filter inputs don't update the table.

**Debug Filter System:**
```javascript
setupFilterEvents() {
    const searchInput = document.getElementById('productSearch');
    
    if (!searchInput) {
        console.error('Search input not found');
        return;
    }
    
    searchInput.addEventListener('input', (e) => {
        console.log('Search input changed:', e.target.value);
        this.applyFilters();
    });
}

applyFilters() {
    console.log('Applying filters...');
    
    if (!this.currentAnalysisData) {
        console.warn('No analysis data available for filtering');
        return;
    }
    
    // Add filter debugging...
}
```

---

## 🔄 API and Communication Issues

### 1. AJAX Requests Failing

**Symptom:** API calls return errors or timeout.

**Debug AJAX Calls:**
```javascript
async makeRequest(endpoint, data = null) {
    const url = this.controllerUrl + endpoint;
    console.log('Making request to:', url);
    
    try {
        const response = await fetch(url, {
            method: data ? 'POST' : 'GET',
            body: data
        });
        
        console.log('Response status:', response.status);
        console.log('Response headers:', [...response.headers.entries()]);
        
        const text = await response.text();
        console.log('Raw response:', text.substring(0, 200));
        
        const json = JSON.parse(text);
        console.log('Parsed JSON:', json);
        
        return json;
    } catch (error) {
        console.error('Request failed:', error);
        throw error;
    }
}
```

### 2. Session/Authentication Issues

**Symptom:** Getting "Unauthorized" or session-related errors.

**Check Session:**
```php
// Add to controller.php:
session_start();
if (!isset($_SESSION['user_id'])) {
    error_log('No user session found');
    echo json_encode(['status' => 0, 'message' => 'Session expired']);
    exit;
}
```

### 3. PHP Errors in JSON Response

**Symptom:** JSON parsing errors due to PHP warnings/errors in output.

**Clean Output:**
```php
// At start of controller.php:
error_reporting(0); // Disable error output to browser
ini_set('display_errors', 0);

// Use error_log instead of echo for debugging:
error_log('Debug info: ' . print_r($data, true));

// Ensure clean JSON output:
header('Content-Type: application/json');
echo json_encode($response);
exit; // Prevent additional output
```

---

## 🔄 Progressive Comparison Issues

### 1. Progress Bar Not Updating

**Symptom:** Progress bar stays at 0% or doesn't show estimated time.

**Debugging Steps:**
```javascript
// Check if progress elements exist
const progressFill = document.getElementById('progressFill');
const progressText = document.getElementById('progressText');

if (!progressFill || !progressText) {
    console.error('Progress elements not found in DOM');
}

// Check if updateProgress is being called
updateProgress(current, total, status, currentShop, startTime) {
    console.log('Progress update:', {current, total, status, currentShop});
    // ... rest of function
}
```

**Common Solutions:**
1. **DOM Elements Missing:** Ensure progress HTML is present in view.php
2. **CSS Not Loaded:** Check if adaptaccuracy.css contains progress bar styles
3. **JavaScript Errors:** Check browser console for errors during progress updates

### 2. Progressive Comparison Hangs

**Symptom:** Comparison starts but gets stuck on one shop.

**Debugging:**
```javascript
async analyzeSingleShopForComparison(shopId) {
    console.log(`🔍 Starting analysis for shop ${shopId}`);
    
    try {
        const startTime = Date.now();
        const response = await fetch(url);
        const endTime = Date.now();
        
        console.log(`⏱️ Shop ${shopId} analyzed in ${endTime - startTime}ms`);
        return result;
    } catch (error) {
        console.error(`❌ Failed to analyze shop ${shopId}:`, error);
        return null; // Continue with next shop
    }
}
```

**Solutions:**
1. **Network Timeout:** Individual shop analysis taking too long
2. **API Endpoint Error:** Check if analyzeAdaptAccuracy endpoint works for problematic shop
3. **Memory Issues:** Large datasets causing browser to hang

### 3. Shop Search Not Working

**Symptom:** Typing in shop search field doesn't filter the dropdown.

**Debugging:**
```javascript
// Check if event listener is attached
document.getElementById('shopSearch').addEventListener('input', (e) => {
    console.log('Shop search input:', e.target.value);
    this.filterShops(e.target.value);
});

// Check if allShops array is populated
console.log('All shops loaded:', this.allShops.length);
```

**Solutions:**
1. **Event Listener Missing:** Ensure setupEventListeners() is called
2. **Empty allShops Array:** Check if loadShops() successfully populated data
3. **Case Sensitivity:** Ensure filtering uses toLowerCase() for both search term and shop names

---

## 🎯 Performance Issues

### 1. Slow Query Performance

**Symptom:** Analysis takes very long to complete.

**Query Optimization:**
```sql
-- Add indexes for better performance:
CREATE INDEX idx_present_reservation_shop_id ON present_reservation(shop_id);
CREATE INDEX idx_order_shop_present_model ON `order`(shop_id, present_id, present_model_id);
CREATE INDEX idx_order_demo ON `order`(is_demo);

-- Optimize the main query:
EXPLAIN SELECT ... -- Use EXPLAIN to check query execution plan
```

**PHP Optimization:**
```php
// Use prepared statements:
$stmt = $pdo->prepare("SELECT ... WHERE shop_id = ?");
$stmt->execute([$shopId]);

// Batch process large datasets:
$chunkSize = 100;
for ($i = 0; $i < count($products); $i += $chunkSize) {
    $chunk = array_slice($products, $i, $chunkSize);
    $this->processProductChunk($chunk);
}
```

### 2. Memory Issues

**Symptom:** Script timeouts or memory exhaustion errors.

**Memory Management:**
```php
// Increase memory limit if needed:
ini_set('memory_limit', '256M');

// Process data in chunks:
foreach ($products as $index => $product) {
    $this->processProduct($product);
    
    // Clear references to prevent memory leaks:
    unset($products[$index]);
    
    // Garbage collection every 100 products:
    if ($index % 100 === 0) {
        gc_collect_cycles();
    }
}
```

### 3. Frontend Performance

**Symptom:** Slow UI responses, browser freezing.

**Optimization Strategies:**
```javascript
// Debounce expensive operations:
const debouncedSearch = this.debounce(this.applyFilters.bind(this), 300);

// Use requestAnimationFrame for smooth animations:
requestAnimationFrame(() => {
    this.updateCharts();
});

// Lazy load charts:
const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            this.createChart(entry.target.id);
        }
    });
});
```

---

## 🔍 Debugging Tools and Techniques

### 1. Browser Developer Tools

**Console Debugging:**
```javascript
// Enable verbose logging:
window.DEBUG_MODE = true;

function debugLog(message, data = null) {
    if (window.DEBUG_MODE) {
        console.log('🐛 DEBUG:', message, data);
    }
}

// Network tab inspection:
// - Check request/response headers
// - Verify POST data format
// - Look for HTTP status codes
```

### 2. PHP Error Logging

**Setup Error Logging:**
```php
// In controller.php:
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/error.log');

// Custom logging function:
function debugLog($message, $data = null) {
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message";
    if ($data) {
        $logMessage .= " Data: " . print_r($data, true);
    }
    error_log($logMessage);
}
```

### 3. Database Query Debugging

**Query Logging:**
```php
// Log all queries:
class DebugPDO extends PDO {
    public function query($statement) {
        error_log("SQL Query: $statement");
        return parent::query($statement);
    }
    
    public function prepare($statement, $driver_options = []) {
        error_log("SQL Prepare: $statement");
        return parent::prepare($statement, $driver_options);
    }
}
```

---

## 📋 Diagnostic Checklist

### System Health Check

```bash
# Check file permissions:
ls -la adaptaccuracy/
# Should be readable by web server

# Check PHP error log:
tail -f /var/log/php_errors.log

# Check web server error log:
tail -f /var/log/apache2/error.log
```

### Data Validation

```sql
-- Check data integrity:
SELECT 
    COUNT(*) as total_reservations,
    COUNT(CASE WHEN adapt_1 IS NOT NULL THEN 1 END) as has_adapt_1,
    COUNT(CASE WHEN adapt_2 IS NOT NULL THEN 1 END) as has_adapt_2,
    COUNT(CASE WHEN adapt_3 IS NOT NULL THEN 1 END) as has_adapt_3
FROM present_reservation 
WHERE shop_id = YOUR_SHOP_ID;

-- Check order counts:
SELECT 
    pr.present_id,
    COUNT(o.id) as order_count,
    pr.quantity,
    pr.adapt_1,
    pr.adapt_2,
    pr.adapt_3
FROM present_reservation pr
LEFT JOIN `order` o ON o.present_id = pr.present_id 
    AND o.shop_id = pr.shop_id 
    AND o.is_demo = 0
WHERE pr.shop_id = YOUR_SHOP_ID
GROUP BY pr.present_id
ORDER BY order_count DESC
LIMIT 10;
```

### Frontend Validation

```javascript
// Check component initialization:
function systemHealthCheck() {
    const checks = {
        'Chart.js loaded': typeof Chart !== 'undefined',
        'Controller URL set': !!window.CONTROLLER_URL,
        'Main elements present': !!document.getElementById('shopSelect'),
        'Analyzer class available': typeof AdaptAccuracyAnalyzer !== 'undefined'
    };
    
    console.table(checks);
    
    return Object.values(checks).every(check => check);
}

// Run health check on page load:
document.addEventListener('DOMContentLoaded', systemHealthCheck);
```

---

## 🚨 Emergency Recovery

### 1. Reset System State

```javascript
// Clear all data and reset UI:
function emergencyReset() {
    // Destroy all charts:
    Object.values(this.charts || {}).forEach(chart => {
        if (chart && chart.destroy) chart.destroy();
    });
    
    // Clear data:
    this.currentAnalysisData = null;
    this.currentComparisonData = null;
    this.charts = {};
    
    // Reset UI:
    document.getElementById('singleShopSection').style.display = 'none';
    document.getElementById('comparisonSection').style.display = 'none';
    document.getElementById('errorMessage').style.display = 'none';
    document.getElementById('loading').style.display = 'none';
    
    // Reload page if necessary:
    if (confirm('System reset failed. Reload page?')) {
        window.location.reload();
    }
}
```

### 2. Fallback Data Loading

```php
// Simplified query for emergency data loading:
function getEmergencyShopData($shopId) {
    try {
        $sql = "SELECT id, shop_id, present_id, quantity, adapt_1, adapt_2, adapt_3 
                FROM present_reservation 
                WHERE shop_id = ? 
                AND quantity > 0 
                LIMIT 100"; // Limit for safety
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$shopId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (Exception $e) {
        error_log("Emergency data loading failed: " . $e->getMessage());
        return [];
    }
}
```

This troubleshooting guide covers the most common issues and provides systematic approaches to diagnosing and resolving problems in the Adapt Accuracy Analysis system.