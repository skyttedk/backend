# API Reference - Adapt Accuracy Analysis

## 🌐 API Endpoints Overview

Base URL: `{BACKEND_URL}index.php?rt=unit/valgshop/adaptaccuracy/`

All endpoints return JSON responses with the following structure:
```json
{
    "status": 1|0,
    "message": "Success/Error message",
    "data": {...}
}
```

---

## 📋 Shop Management

### GET /getShopList
Henter liste over alle tilgængelige shops med reservation count.

**Parameters:** None

**Response:**
```json
{
    "status": 1,
    "message": "Success",
    "data": [
        {
            "id": "123",
            "name": "Shop Navn",
            "reservation_count": 45,
            "language_id": 1
        }
    ]
}
```

**Usage:**
```javascript
fetch(CONTROLLER_URL + 'getShopList')
    .then(response => response.json())
    .then(data => {
        if (data.status === 1) {
            console.log('Shops loaded:', data.data);
        }
    });
```

---

## 🎯 Single Shop Analysis

### POST /performAnalysis
Udfører komplet accuracy analyse for en specifik shop.

**Parameters:**
```json
{
    "shop_id": "123"
}
```

**Response:**
```json
{
    "status": 1,
    "message": "Analysis completed successfully",
    "data": {
        "shop_info": {
            "id": "123",
            "name": "Shop Navn",
            "total_orders": 1250,
            "total_users": 150,
            "procent_selected": 45.5,
            "adapt_stage": 2,
            "forecast_procent": 1.1,
            "low_selection_boost": false
        },
        "products": [
            {
                "id": 1,
                "shop_id": 123,
                "present_id": 456,
                "model_id": 0,
                "present_name": "Produkt Navn",
                "quantity": 25,
                "order_count": 18,
                "adapt_1": 20,
                "adapt_2": 18,
                "adapt_3": 22,
                "autopilot_adapt_1": 22,
                "autopilot_adapt_2": 20,
                "autopilot_adapt_3": 24,
                "is_external": 0,
                "navision_type": "Item",
                "is_close": 0,
                "autotopilot": 1,
                "accuracies": {
                    "adapt_1": {
                        "original": 90.0,
                        "autopilot": 87.78,
                        "improvement": -2.22
                    },
                    // ... adapt_2, adapt_3
                },
                "best_forecast": {
                    "field": "adapt_1",
                    "predicted": 20,
                    "accuracy": 90.0,
                    "category": "excellent"
                }
            }
        ],
        "summary_stats": {
            "total_products": 25,
            "adapt_1": {
                "total": 25,
                "accurate": 18,
                "accuracy": 72.0,
                "avg_accuracy": 78.5
            },
            // ... adapt_2, adapt_3
            "autopilot_stats": {
                "improved": 15,
                "degraded": 8,
                "unchanged": 2,
                "avg_improvement": 5.2
            },
            "categories": {
                "excellent": 8,
                "good": 12,
                "fair": 4,
                "poor": 1
            }
        }
    }
}
```

**Usage:**
```javascript
const formData = new FormData();
formData.append('shop_id', '123');

fetch(CONTROLLER_URL + 'performAnalysis', {
    method: 'POST',
    body: formData
})
.then(response => response.json())
.then(data => {
    if (data.status === 1) {
        console.log('Analysis completed:', data.data);
    }
});
```

---

## 🏆 Multi-Shop Comparison

### POST /compareAllShops
**DEPRECATED** - Sammenligner accuracy metrics på tværs af alle shops i én request.

**⚠️ Warning:** Denne endpoint kan føre til timeouts ved mange shops. Brug den progressive tilgang i stedet.

**Parameters:** None

**Response:**
```json
{
    "status": 1,
    "message": "Comparison completed successfully",
    "data": {
        "shops": [
            {
                "id": "123",
                "name": "Shop A",
                "avg_accuracy": 78.5,
                "total_products": 25,
                "adapt_1_accuracy": 72.0,
                "adapt_2_accuracy": 81.5,
                "adapt_3_accuracy": 82.0,
                "rank": 1,
                "weighted_score": 85.2
            }
        ],
        "overall_stats": {
            "total_shops": 15,
            "total_products": 420,
            "avg_accuracy": 73.8,
            "best_adapt_field": "adapt_3",
            "improvement_potential": 12.5
        },
        "adapt_field_comparison": {
            "adapt_1": {
                "avg_accuracy": 69.2,
                "total_predictions": 380,
                "rank": 3
            },
            "adapt_2": {
                "avg_accuracy": 75.8,
                "total_predictions": 395,
                "rank": 2
            },
            "adapt_3": {
                "avg_accuracy": 76.4,
                "total_predictions": 402,
                "rank": 1
            }
        }
    }
}
```

### Progressive Multi-Shop Comparison (Recommended)
**Frontend Implementation** - Bruger individuelle `analyzeAdaptAccuracy` kald for hver shop.

**Workflow:**
1. Hent shop liste med `getShopList`
2. Filtrer shops med reservationer 
3. Vis confirm dialog med antal shops
4. Loop gennem hver shop med `analyzeAdaptAccuracy`
5. Aggreger resultater i frontend
6. Vis progress bar under processeringen

**Usage:**
```javascript
class AdaptAccuracyAnalyzer {
    async compareAllShopsProgressive() {
        // Get shops with reservations
        const shopsWithReservations = this.allShops.filter(shop => shop.reservation_count > 0);
        
        // Confirm dialog
        if (!confirm(`Du er ved at sammenligne ${shopsWithReservations.length} shops.\n\nDette kan tage flere minutter at gennemføre.\n\nVil du fortsætte?`)) {
            return;
        }
        
        // Progress tracking
        for (let i = 0; i < shopsWithReservations.length; i++) {
            const shop = shopsWithReservations[i];
            
            // Individual shop analysis
            const analysisData = await this.analyzeSingleShopForComparison(shop.id);
            
            // Update progress bar
            this.updateProgress(i + 1, shopsWithReservations.length, 'Analyserer...', shop.name, startTime);
        }
        
        // Generate final report
        const comparisonReport = this.generateComparisonReport(this.comparisonData);
    }
    
    async analyzeSingleShopForComparison(shopId) {
        const params = new URLSearchParams({ shop_id: shopId });
        const url = this.baseUrl + 'analyzeAdaptAccuracy&' + params.toString();
        
        const response = await fetch(url);
        return response.json();
    }
}
```

**Advantages:**
- ✅ **Ingen timeouts** - hver shop analyse er hurtig
- ✅ **Visuel feedback** - real-time progress bar
- ✅ **Robust** - fortsætter selvom enkelte shops fejler
- ✅ **User-friendly** - confirm dialog og estimeret tid
- ✅ **Server venlig** - små forsinkelser mellem kald

---

## 🔍 Product Details

### POST /getProductDetails
Henter detaljerede informationer om et specifikt produkt.

**Parameters:**
```json
{
    "reservation_id": "456"
}
```

**Response:**
```json
{
    "status": 1,
    "message": "Product details retrieved",
    "data": {
        "basic_info": {
            "id": 456,
            "present_name": "Produkt Navn",
            "model_name": "Model Navn",
            "shop_id": 123,
            "shop_name": "Shop Navn"
        },
        "reservation_info": {
            "quantity": 25,
            "warning_level": 20.0,
            "current_level": 18.5,
            "is_close": 0,
            "autotopilot": 1
        },
        "predictions": {
            "adapt_0": 15,
            "adapt_1": 20,
            "adapt_2": 18,
            "adapt_3": 22
        },
        "autopilot_adjustments": {
            "adapt_1": {
                "original": 20,
                "adjusted": 22,
                "reason": "forecast_multiplier_1.1"
            },
            "adapt_2": {
                "original": 18,
                "adjusted": 20,
                "reason": "forecast_multiplier_1.1"
            },
            "adapt_3": {
                "original": 22,
                "adjusted": 24,
                "reason": "forecast_multiplier_1.1"
            }
        },
        "actual_performance": {
            "order_count": 18,
            "accuracy_original": {
                "adapt_1": 90.0,
                "adapt_2": 100.0,
                "adapt_3": 81.82
            },
            "accuracy_autopilot": {
                "adapt_1": 87.78,
                "adapt_2": 90.0,
                "adapt_3": 75.0
            }
        },
        "navision_info": {
            "is_external": 0,
            "type": "Item",
            "description": "Produkt beskrivelse"
        },
        "historical_data": [
            {
                "date": "2024-01-15",
                "quantity": 20,
                "adapt_1": 18,
                "autotopilot": 0
            }
        ]
    }
}
```

---

## ⚙️ Configuration & Settings

### GET /getSystemSettings
Henter system konfiguration og settings.

**Response:**
```json
{
    "status": 1,
    "data": {
        "minimum_orders": 5,
        "accuracy_thresholds": {
            "excellent": 90,
            "good": 75,
            "fair": 50
        },
        "autopilot_settings": {
            "buffer_percentage": 5,
            "minimum_buffer": 5,
            "low_selection_threshold": 27,
            "low_selection_multiplier": 1.5
        },
        "analysis_settings": {
            "include_external": false,
            "include_closed": true,
            "tolerance_percentage": 10
        }
    }
}
```

---

## 📊 Export Functions

### POST /exportAnalysisData
Eksporterer analyse data i forskellige formater.

**Parameters:**
```json
{
    "shop_id": "123",
    "format": "csv|json|xlsx",
    "include_autopilot": true,
    "include_details": true
}
```

**Response:**
```json
{
    "status": 1,
    "message": "Export completed",
    "data": {
        "download_url": "/path/to/export/file.csv",
        "file_size": "2.5 MB",
        "records_exported": 150,
        "format": "csv"
    }
}
```

---

## 🚨 Error Handling

### Standard Error Response
```json
{
    "status": 0,
    "message": "Error description",
    "error_code": "ERROR_CODE",
    "debug_info": "Additional debug information"
}
```

### Common Error Codes

| Error Code | HTTP Status | Description |
|------------|-------------|-------------|
| `SHOP_NOT_FOUND` | 404 | Specified shop ID not found |
| `NO_RESERVATIONS` | 204 | No reservations found for shop |
| `INSUFFICIENT_DATA` | 400 | Not enough data for analysis |
| `DATABASE_ERROR` | 500 | Database connection/query error |
| `INVALID_PARAMETERS` | 400 | Missing or invalid parameters |
| `UNAUTHORIZED` | 401 | Insufficient permissions |

---

## 🔧 Request/Response Examples

### Complete Analysis Workflow
```javascript
class AdaptAccuracyAPI {
    constructor(baseUrl) {
        this.baseUrl = baseUrl;
    }
    
    async getShops() {
        const response = await fetch(this.baseUrl + 'getShopList');
        return response.json();
    }
    
    async analyzeShop(shopId) {
        const formData = new FormData();
        formData.append('shop_id', shopId);
        
        const response = await fetch(this.baseUrl + 'performAnalysis', {
            method: 'POST',
            body: formData
        });
        return response.json();
    }
    
    async compareAllShops() {
        const response = await fetch(this.baseUrl + 'compareAllShops', {
            method: 'POST'
        });
        return response.json();
    }
    
    async getProductDetails(reservationId) {
        const formData = new FormData();
        formData.append('reservation_id', reservationId);
        
        const response = await fetch(this.baseUrl + 'getProductDetails', {
            method: 'POST',
            body: formData
        });
        return response.json();
    }
}

// Usage
const api = new AdaptAccuracyAPI(CONTROLLER_URL);

// Load shops and analyze first shop
api.getShops()
    .then(shopsData => {
        if (shopsData.status === 1 && shopsData.data.length > 0) {
            return api.analyzeShop(shopsData.data[0].id);
        }
    })
    .then(analysisData => {
        console.log('Analysis completed:', analysisData);
    })
    .catch(error => {
        console.error('API Error:', error);
    });
```

---

## 🔄 Rate Limiting & Performance

### Request Limits
- **Analysis requests**: Max 10 per minute per user
- **Comparison requests**: Max 2 per minute per user
- **Data export**: Max 5 per hour per user

### Caching Strategy
- Shop list: Cached for 5 minutes
- Analysis results: Cached for 15 minutes
- Product details: Cached for 30 minutes

### Performance Tips
1. Use comparison endpoint for multiple shops instead of individual analysis calls
2. Cache results locally when possible
3. Use pagination for large datasets
4. Implement request debouncing for user interactions

---

## 🛡️ Security Considerations

### Authentication
All endpoints require valid session authentication through the main GF system.

### Data Sanitization
- All input parameters are sanitized and validated
- SQL injection protection via prepared statements
- XSS protection for all output data

### Access Control
- Users can only access shops they have permissions for
- Admin users can access all shops and comparison data
- Audit logging for all analysis requests