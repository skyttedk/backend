# Flow Diagrams - Adapt Accuracy Analysis

## 🔄 System Flow Overview

This document provides visual representations of the data flow and process diagrams for the Adapt Accuracy Analysis system.

---

## 📊 Overall System Architecture

```mermaid
graph TB
    A[User Interface<br/>view.php] --> B[JavaScript Controller<br/>AdaptAccuracyAnalyzer]
    B --> C[PHP Controller<br/>controller.php]
    C --> D[Database Queries<br/>MySQL]
    
    D --> E[present_reservation<br/>Adapt predictions]
    D --> F[order<br/>Actual sales data]
    D --> G[shop<br/>Shop information]
    D --> H[navision_item<br/>Product classification]
    
    C --> I[Autopilot Logic<br/>Calculations]
    I --> J[Accuracy Analysis<br/>Comparison]
    J --> K[Results Processing<br/>Statistics]
    K --> B
    B --> L[Chart.js<br/>Visualization]
    B --> M[HTML Tables<br/>Details]
```

---

## 🎯 Single Shop Analysis Flow

```mermaid
sequenceDiagram
    participant U as User
    participant JS as JavaScript
    participant API as PHP Controller
    participant DB as Database
    participant AL as Autopilot Logic
    
    U->>JS: Select Shop & Click Analyze
    JS->>API: POST /performAnalysis {shop_id}
    
    API->>DB: Query shop info (orders, users, %)
    DB-->>API: Shop data
    
    API->>AL: Calculate adapt stage & forecast %
    AL-->>API: Autopilot parameters
    
    API->>DB: Query present_reservation + JOINs
    DB-->>API: Product reservations with predictions
    
    loop For each product
        API->>AL: Calculate autopilot adjustments
        AL-->>API: Adjusted forecasts
        API->>API: Calculate original vs autopilot accuracy
    end
    
    API->>API: Generate summary statistics
    API-->>JS: Analysis results JSON
    
    JS->>JS: Update summary cards
    JS->>JS: Create Chart.js visualizations
    JS->>JS: Populate product table
    JS-->>U: Display analysis results
```

---

## 🤖 Autopilot Logic Flow

```mermaid
flowchart TD
    A[Start: Product Analysis] --> B{External Product?<br/>is_external > 0}
    
    B -->|Yes| C[🛡️ PROTECTED<br/>Return original forecast<br/>Reason: protected_external]
    
    B -->|No| D{Zero Orders?<br/>order_count = 0}
    
    D -->|Yes| E[Calculate Zero Forecast<br/>Based on shop size & % selected]
    E --> F[Apply zero forecast value<br/>5-10 items depending on thresholds]
    
    D -->|No| G[Calculate Adapt Stage<br/>Based on total_orders & procent_selected]
    G --> H[Determine Forecast Multiplier<br/>1.05x - 1.3x based on stage]
    
    H --> I[Apply Standard Adjustment<br/>adjusted = ceil(original × multiplier)]
    
    I --> J{Low Selection?<br/>procent_selected < 27%}
    
    J -->|Yes| K[Apply Low Selection Boost<br/>adjusted = ceil(adjusted × 1.5)]
    J -->|No| L[Final Adjusted Forecast]
    K --> L
    
    C --> M[Return Result]
    F --> M
    L --> M
    
    M --> N[Calculate Accuracy vs Actual Orders]
    N --> O[End: Accuracy Comparison]
```

---

## 📈 Accuracy Calculation Process

```mermaid
graph TD
    A[Start: Product with Predictions] --> B[Get Original Forecasts<br/>adapt_1, adapt_2, adapt_3]
    
    B --> C[Get Autopilot Adjustments<br/>Apply autopilot logic]
    
    C --> D[Get Actual Orders<br/>Count from order table]
    
    D --> E[Calculate Original Accuracies<br/>For each adapt field]
    E --> F[Calculate Autopilot Accuracies<br/>For each adjust field]
    
    F --> G[Determine Best Forecast<br/>Highest accuracy]
    
    G --> H[Categorize Accuracy<br/>Excellent/Good/Fair/Poor]
    
    H --> I[Calculate Improvements<br/>Autopilot vs Original]
    
    I --> J[Aggregate Shop Statistics<br/>Averages, counts, percentages]
    
    J --> K[Generate Summary Data<br/>Cards, charts, tables]
    
    K --> L[End: Display Results]
```

---

## 🔍 Data Query Flow

```mermaid
graph LR
    A[Query Start] --> B[Get Shop Information<br/>total_orders, procent_selected]
    
    B --> C[Main Reservation Query<br/>present_reservation base]
    
    C --> D[LEFT JOIN present_model<br/>Get model names]
    
    D --> E[LEFT JOIN present<br/>Get present names]
    
    E --> F[LEFT JOIN navision_item<br/>Get is_external classification]
    
    F --> G[Subquery: Count Orders<br/>Group by shop_id, present_id, model_id]
    
    G --> H[Filter: order_count >= 5<br/>Only analyze active products]
    
    H --> I[Filter: Has Predictions<br/>adapt_1/2/3 IS NOT NULL]
    
    I --> J[Return Dataset<br/>Ready for analysis]
```

---

## 🏆 Multi-Shop Comparison Flow

### Progressive Comparison (Recommended)

```mermaid
sequenceDiagram
    participant U as User
    participant JS as JavaScript
    participant API as PHP Controller
    participant Progress as Progress UI
    
    U->>JS: Click "Compare All Shops"
    JS->>JS: Filter shops with reservations
    JS->>U: Show confirm dialog with shop count
    U->>JS: Confirm comparison
    
    JS->>Progress: Show progress bar (0%)
    
    loop For each shop with reservations
        JS->>Progress: Update current shop name
        JS->>API: GET /analyzeAdaptAccuracy?shop_id=X
        API-->>JS: Individual shop analysis data
        JS->>JS: Store shop data in comparisonData array
        JS->>Progress: Update progress percentage
        JS->>Progress: Update estimated time remaining
        Note over JS: Small delay to prevent server overload
    end
    
    JS->>Progress: Show "Generating report..." (100%)
    JS->>JS: Generate comparison report from collected data
    JS->>JS: Sort shops by accuracy ranking
    JS->>Progress: Hide progress bar
    
    JS->>JS: Create shop ranking chart
    JS->>JS: Create adapt field comparison chart
    JS->>JS: Populate shop ranking table
    JS-->>U: Display comparison results
```

### Legacy Comparison (May Timeout)

```mermaid
sequenceDiagram
    participant U as User
    participant JS as JavaScript
    participant API as PHP Controller
    participant DB as Database
    
    U->>JS: Click "Compare All Shops" (legacy)
    JS->>API: POST /compareAllShops
    
    API->>DB: Query shop list with reservation counts
    DB-->>API: Shop list
    
    loop For each shop with reservations
        API->>API: Perform individual shop analysis
        Note over API: Same process as single shop analysis
        API->>API: Store shop results
    end
    
    API->>API: Calculate shop rankings
    API->>API: Generate comparison statistics
    API->>API: Identify best performing adapt fields
    
    API-->>JS: Comparison results JSON
    
    JS->>JS: Create shop ranking chart
    JS->>JS: Create adapt field comparison chart
    JS->>JS: Populate shop ranking table
    JS-->>U: Display comparison results
```

---

## 🎨 Frontend State Management Flow

```mermaid
stateDiagram-v2
    [*] --> Initial: Page Load
    Initial --> LoadingShops: Fetch shop list
    LoadingShops --> ShopsLoaded: API Success
    LoadingShops --> Error: API Error
    
    ShopsLoaded --> ShopSelected: User selects shop
    ShopSelected --> AnalyzingShop: Click Analyze
    AnalyzingShop --> AnalysisComplete: Analysis successful
    AnalyzingShop --> Error: Analysis failed
    
    AnalysisComplete --> ShowingResults: Display charts & tables
    ShowingResults --> ProductModal: Click product row
    ProductModal --> ShowingResults: Close modal
    
    ShowingResults --> FilteringResults: Apply filters
    FilteringResults --> ShowingResults: Update table
    
    ShopsLoaded --> ComparingAll: Click Compare All
    ComparingAll --> ComparisonComplete: All shops analyzed
    ComparisonComplete --> ShowingComparison: Display ranking
    
    Error --> ShopsLoaded: Retry/Reset
    ShowingComparison --> ShopsLoaded: Back to single shop
```

---

## 📊 Chart Creation Process

```mermaid
flowchart TD
    A[Analysis Data Ready] --> B{Chart Type}
    
    B -->|Accuracy| C[Bar Chart Creation<br/>Original vs Autopilot]
    B -->|Hit Rate| D[Doughnut Chart Creation<br/>Category distribution]
    B -->|Scatter| E[Scatter Plot Creation<br/>Predicted vs Actual]
    B -->|Comparison| F[Line Chart Creation<br/>Shop performance]
    
    C --> G[Configure Chart.js Options<br/>Colors, scales, tooltips]
    D --> G
    E --> G
    F --> G
    
    G --> H[Create Chart Instance<br/>Bind to canvas element]
    
    H --> I[Register Chart in Manager<br/>For cleanup & updates]
    
    I --> J[Chart Rendered<br/>Interactive & responsive]
    
    J --> K{Update Needed?}
    K -->|Yes| L[Destroy Old Chart<br/>Create new with updated data]
    K -->|No| M[Chart Complete]
    
    L --> H
```

---

## 🔍 Shop Search Flow

```mermaid
graph TD
    A[User Types in Shop Search] --> B[Input Event Fired<br/>Real-time processing]
    
    B --> C{Search Term Empty?}
    
    C -->|Yes| D[Show All Shops<br/>Reset to original list]
    C -->|No| E[Filter Shops Array<br/>Name contains search term]
    
    E --> F[Update Shop Dropdown<br/>Show filtered results]
    
    F --> G[Preserve Selection State<br/>If current shop still visible]
    
    G --> H[Maintain Scroll Position<br/>User-friendly UX]
    
    D --> I[Search Complete<br/>Dropdown updated]
    F --> I
    H --> I
```

## 🔄 Filter & Search Flow

```mermaid
graph TD
    A[User Input Event<br/>Search/Filter change] --> B[Debounce Timer<br/>300ms delay for search]
    
    B --> C[Get Current Products<br/>From analysis data]
    
    C --> D[Apply Search Filter<br/>Product name contains text]
    
    D --> E[Apply Accuracy Filter<br/>Match category thresholds]
    
    E --> F[Apply Type Filter<br/>Internal/External classification]
    
    F --> G[Apply Status Filter<br/>Active/Closed/Autopilot]
    
    G --> H[Filter Results<br/>Boolean AND logic]
    
    H --> I[Update Product Table<br/>Re-render filtered rows]
    
    I --> J[Update Row Count<br/>Show "X of Y products"]
    
    J --> K[Maintain Sort Order<br/>Preserve existing sort]
    
    K --> L[Re-attach Events<br/>Click handlers for new rows]
    
    L --> M[Filter Complete<br/>Table updated]
```

---

## 🎯 Product Detail Modal Flow

```mermaid
sequenceDiagram
    participant U as User
    participant JS as JavaScript
    participant Modal as Modal Component
    participant API as PHP Controller
    
    U->>JS: Click product table row
    JS->>JS: Extract product data from row
    
    alt Product has all data
        JS->>Modal: Generate modal HTML content
        Modal->>Modal: Show basic product information
        Modal->>Modal: Create accuracy comparison chart
    else Need additional data
        JS->>API: POST /getProductDetails {reservation_id}
        API-->>JS: Detailed product information
        JS->>Modal: Generate enhanced modal content
    end
    
    Modal->>Modal: Display modal with fade-in animation
    Modal->>U: Show detailed product analysis
    
    U->>Modal: Interact with modal (scroll, charts)
    U->>Modal: Click close button or overlay
    Modal->>Modal: Hide modal with fade-out
    Modal->>JS: Cleanup chart instances
```

---

## 🎨 Responsive Layout Flow

```mermaid
graph TD
    A[Page Load] --> B[Detect Screen Size<br/>CSS Media queries]
    
    B --> C{Screen Width}
    
    C -->|Desktop >1200px| D[Grid Layout<br/>4 columns for summary cards]
    C -->|Tablet 768-1200px| E[Grid Layout<br/>2 columns for summary cards]
    C -->|Mobile <768px| F[Single Column<br/>Stacked layout]
    
    D --> G[Charts Side by Side<br/>2 per row]
    E --> H[Charts Stacked<br/>1 per row]
    F --> I[Charts Full Width<br/>Scrollable tables]
    
    G --> J[Table Full Width<br/>All columns visible]
    H --> K[Table Responsive<br/>Horizontal scroll]
    I --> L[Table Mobile<br/>Hide less important columns]
    
    J --> M[Modal Standard Size<br/>80% viewport width]
    K --> N[Modal Medium Size<br/>90% viewport width]
    L --> O[Modal Full Screen<br/>95% viewport width]
```

---

## 🚨 Error Handling Flow

```mermaid
flowchart TD
    A[API Call Made] --> B{Response Status}
    
    B -->|Success 200| C[Parse JSON Response]
    B -->|Error 4xx/5xx| D[Network/Server Error]
    
    C --> E{JSON status field}
    
    E -->|status: 1| F[Success: Process Data]
    E -->|status: 0| G[API Error: Show message]
    
    D --> H[Log Error to Console]
    G --> H
    
    H --> I[Display Error Message<br/>Red notification banner]
    
    I --> J[Auto-hide After 5 Seconds<br/>Or user dismissal]
    
    J --> K[Reset UI State<br/>Enable retry options]
    
    F --> L[Update UI with Data<br/>Hide loading states]
    
    K --> M[User Can Retry<br/>Or navigate away]
    L --> N[Success Complete]
```

---

## 📊 Progress Bar Flow

```mermaid
graph TD
    A[Start Progressive Analysis] --> B[Show Progress Container<br/>0% initial state]
    
    B --> C[Initialize Progress Tracking<br/>startTime, currentShop, totalShops]
    
    C --> D[For Each Shop Loop<br/>Individual processing]
    
    D --> E[Update Progress Bar<br/>Calculate percentage]
    
    E --> F[Update Progress Text<br/>"X% - Analyserer..."]
    
    F --> G[Update Current Shop Name<br/>Show which shop is processing]
    
    G --> H[Calculate Time Remaining<br/>Based on elapsed time and progress]
    
    H --> I[Update Progress Details<br/>Analyzed count, ETA]
    
    I --> J{More Shops?}
    
    J -->|Yes| D
    J -->|No| K[Final Progress State<br/>100% - "Færdig!"]
    
    K --> L[Hide Progress Bar<br/>Show Results]
    
    L --> M[Progress Complete]
```

## 📈 Performance Optimization Flow

```mermaid
graph LR
    A[Initial Load] --> B[Lazy Load Charts<br/>Only when visible]
    
    B --> C[Debounce Search<br/>300ms delay]
    
    C --> D[Virtual Scrolling<br/>Large product tables]
    
    D --> E[Chart Reuse<br/>Update data vs recreate]
    
    E --> F[Memory Cleanup<br/>Destroy unused charts]
    
    F --> G[Request Caching<br/>Avoid duplicate API calls]
    
    G --> H[Progressive Loading<br/>100ms delays between requests]
    
    H --> I[CSS Animation<br/>Hardware acceleration]
    
    I --> J[Bundle Minification<br/>Smaller file sizes]
    
    J --> K[CDN Resources<br/>Chart.js from CDN]
```

This comprehensive flow documentation provides visual representations of all major processes in the Adapt Accuracy Analysis system, from high-level architecture to detailed component interactions.