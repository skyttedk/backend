# Autopilot Logic - Komplet Algoritme Dokumentation

## 🤖 Autopilot System Oversigt

Autopilot systemet justerer automatically present reservationer baseret på komplekse forretningsregler. Logikken er baseret på `autopanel.php` og implementerer avancerede buffer- og threshold-baserede justeringer.

---

## 📊 Adapt Stage Beregning

Adapt stage bestemmer hvilket niveau af autopilot justering der skal anvendes baseret på shop størrelse og hvor mange procent af brugerne der har valgt gaver.

### Algoritme

```php
function getAdaptStage($total_orders, $procent_selected) {
    $adapt = 0;
    
    if ($total_orders < 500) {
        if ($procent_selected > 20) $adapt = 1;
        if ($procent_selected > 40) $adapt = 2;
        if ($procent_selected > 50) $adapt = 3;
        
    } elseif ($total_orders >= 500 && $total_orders < 1000) {
        if ($procent_selected > 15) $adapt = 1;
        if ($procent_selected > 30) $adapt = 2;
        if ($procent_selected > 50) $adapt = 3;
        
    } elseif ($total_orders > 1000) {
        if ($procent_selected > 10) $adapt = 1;
        if ($procent_selected > 20) $adapt = 2;
        if ($procent_selected > 50) $adapt = 3;
    }
    
    return $adapt;
}
```

### Threshold Tabel

| Shop Størrelse | Adapt 1 | Adapt 2 | Adapt 3 |
|----------------|---------|---------|---------|
| < 500 ordrer   | > 20%   | > 40%   | > 50%   |
| 500-1000 ordrer| > 15%   | > 30%   | > 50%   |
| > 1000 ordrer  | > 10%   | > 20%   | > 50%   |

### Rationale
- **Mindre shops**: Højere thresholds da mindre data = mindre pålidelighed
- **Store shops**: Lavere thresholds da mere data = højere pålidelighed  
- **Adapt 3**: Konsistent 50% threshold på tværs af alle størrelser

---

## 🎯 Forecast Procent Multipliers

Forecast procent multiplicerer den originale forudsigelse baseret på adapt stage.

### Algoritme

```php
function getForecastProcent($total_orders, $procent_selected) {
    $forecastProcent = 1.3; // Standard buffer
    
    if ($total_orders < 500) {
        if ($procent_selected > 20) $forecastProcent = 1.2;
        if ($procent_selected > 40) $forecastProcent = 1.1;
        if ($procent_selected > 50) $forecastProcent = 1.05;
        if ($procent_selected > 75) $forecastProcent = 1.05; // Special case
        
    } elseif ($total_orders >= 500 && $total_orders < 1000) {
        if ($procent_selected > 15) $forecastProcent = 1.2;
        if ($procent_selected > 30) $forecastProcent = 1.1;
        if ($procent_selected > 50) $forecastProcent = 1.05;
        
    } elseif ($total_orders > 1000) {
        if ($procent_selected > 10) $forecastProcent = 1.2;
        if ($procent_selected > 20) $forecastProcent = 1.1;
        if ($procent_selected > 50) $forecastProcent = 1.05;
    }
    
    return $forecastProcent;
}
```

### Multiplier Tabel

| Shop Størrelse | Adapt 1 | Adapt 2 | Adapt 3 | Special (>75%) |
|----------------|---------|---------|---------|----------------|
| < 500 ordrer   | 1.2x    | 1.1x    | 1.05x   | 1.05x         |
| 500-1000 ordrer| 1.2x    | 1.1x    | 1.05x   | -             |
| > 1000 ordrer  | 1.2x    | 1.1x    | 1.05x   | -             |

### Buffer Rationale
- **1.3x standard**: Conservative approach for unkategoriserede situationer
- **Faldende multipliers**: Højere confidence = mindre buffer nødvendig
- **1.05x minimum**: Selv ved høj confidence, bevares en minimal buffer

---

## 🎨 Zero Selected Forecast

For produkter hvor ingen brugere har valgt (order_count = 0), anvendes specielle forecast værdier.

### Algoritme

```php
function getZeroSelectedForecast($total_orders, $procent_selected) {
    $zeroforecast = 0;
    
    if ($total_orders < 500) {
        if ($procent_selected > 20) $zeroforecast = 5;
        if ($procent_selected > 40) $zeroforecast = 3;
        if ($procent_selected > 50) $zeroforecast = 2;
        
    } elseif ($total_orders >= 500 && $total_orders < 1000) {
        if ($procent_selected > 15) $zeroforecast = 7;
        if ($procent_selected > 30) $zeroforecast = 5;
        if ($procent_selected > 50) $zeroforecast = 3;
        
    } elseif ($total_orders > 1000) {
        if ($procent_selected > 10) $zeroforecast = 10;
        if ($procent_selected > 20) $zeroforecast = 8;
        if ($procent_selected > 50) $zeroforecast = 5;
    }
    
    return $zeroforecast;
}
```

### Zero Forecast Tabel

| Shop Størrelse | Adapt 1 | Adapt 2 | Adapt 3 |
|----------------|---------|---------|---------|
| < 500 ordrer   | 5       | 3       | 2       |
| 500-1000 ordrer| 7       | 5       | 3       |
| > 1000 ordrer  | 10      | 8       | 5       |

### Rationale
- **Store shops, tidlige stages**: Højere zero values pga. større potentiale
- **Mindre shops, senere stages**: Lavere values pga. højere confidence
- **Kun interne gaver**: Eksterne gaver får aldrig zero adjustment

---

## 🛡️ Beskyttelsesregler

### 1. Eksterne Gaver
```php
if ($is_external > 0) {
    return [
        'adjusted_forecast' => $original_predict,
        'adjustment_reason' => 'protected_external'
    ];
}
```

**Regel**: Eksterne gaver (`navision_item.is_external > 0`) justeres ALDRIG.

### 2. Automatisk Lukning
```php
if (($orderCount >= $quantity) && 
    ($stockavailable <= 0) && 
    ($is_external == 0) && 
    ($difference < 0) && 
    ($isClose == "0") && 
    ($protectedAuto == "0")) {
    
    // Luk produktet
    $action = 1; // "lukkes"
}
```

**Betingelser for automatisk lukning**:
- Ordrer ≥ reserveret antal
- Lager ≤ 0  
- Intern gave
- Negative difference (overforbrug)
- Ikke allerede lukket
- Ikke beskyttet

### 3. Beskyttet Status
- `autotopilot = 1`: Produktet er beskyttet mod autopilot
- Vises som "Beskyttet" i interface
- Ingen justeringer foretages

---

## 📦 Stock Buffer System

### Buffer Beregning
```php
function calculateStockBuffer($stock_available) {
    if ($stock_available <= 0) return 0;
    
    $buffer = ceil($stock_available * 0.05); // 5%
    return $buffer > 5 ? $buffer : 5;        // Minimum 5
}

$realStockavailable = $stock_available - $buffer;
```

### Buffer Regler
- **5% af lager**: Basis buffer procent
- **Minimum 5 stk**: Altid mindst 5 stks buffer
- **Real stock**: Tilgængeligt lager efter buffer

### Eksempler
| Lager | Buffer (5%) | Minimum | Faktisk Buffer | Real Stock |
|-------|-------------|---------|----------------|------------|
| 100   | 5           | 5       | 5              | 95         |
| 80    | 4           | 5       | 5              | 75         |
| 200   | 10          | 5       | 10             | 190        |
| 50    | 2.5 → 3     | 5       | 5              | 45         |

---

## ⚖️ Kompleks Justeringslogik

### Hovedalgoritme
```php
function getAutopilotAdjustedForecast($original_predict, $shop_info, $product_data) {
    // 1. Tjek beskyttelse
    if ($is_external > 0) return protected();
    
    // 2. Zero selected håndtering
    if ($order_count == 0 && $is_external == 0) {
        return zeroSelectedForecast();
    }
    
    // 3. Standard forecast justering
    if ($original_predict > 0) {
        $adjusted = ceil($original_predict * $forecast_procent);
        
        // 4. Low selection boost
        if ($procent_selected < 27) {
            $adjusted = ceil($adjusted * 1.5);
        }
        
        return $adjusted;
    }
}
```

### Low Selection Boost
**Betingelse**: `procent_selected < 27%`
**Action**: Multiplicer med ekstra 1.5x
**Rationale**: Ved lav deltagelse er der større usikkerhed, så conservative overestimering er bedre

### Difference Beregning
```php
$difference = $quantity - $forecast; // Reserveret - Forudsagt

if ($difference <= 0) {
    // Underreserveret: Har brug for flere varer
    // Tjek lager tilgængelighed og juster op
    
} elseif ($difference > 0) {
    // Overreserveret: Kan frigive varer
    // Juster ned baseret på forecast procent
}
```

---

## 🔄 Proces Flow

### 1. Initialization
```
Shop Data → Beregn adapt stage → Beregn forecast procent
```

### 2. Per Produkt Processing
```
Original Forecast
      ↓
Tjek Eksterne Gaver → [JA] → Beskyttet
      ↓ [NEJ]
Tjek Zero Orders → [JA] → Zero Forecast
      ↓ [NEJ]  
Standard Justering → Forecast Procent
      ↓
Low Selection Check → [<27%] → 1.5x Boost
      ↓
Final Adjusted Forecast
```

### 3. Accuracy Beregning
```
Original vs Actual → Original Accuracy
Adjusted vs Actual → Autopilot Accuracy
      ↓
Sammenlign forbedring
```

---

## 📊 Eksempel Beregninger

### Scenario 1: Lille Shop, Høj Deltagelse
- **Shop**: 300 ordrer, 60% har valgt
- **Adapt Stage**: 3 (60% > 50%)
- **Forecast Procent**: 1.05x
- **Original Forecast**: 20
- **Adjusted**: ceil(20 * 1.05) = 21

### Scenario 2: Stor Shop, Lav Deltagelse  
- **Shop**: 1500 ordrer, 15% har valgt
- **Adapt Stage**: 1 (15% > 10%)
- **Forecast Procent**: 1.2x
- **Low Selection**: 15% < 27% → 1.5x boost
- **Original Forecast**: 10
- **Adjusted**: ceil(10 * 1.2 * 1.5) = 18

### Scenario 3: Zero Orders, Ekstern Gave
- **Produkt**: is_external = 1, order_count = 0
- **Result**: Original forecast bevares (beskyttet)
- **Reason**: "protected_external"

### Scenario 4: Zero Orders, Intern Gave
- **Shop**: 800 ordrer, 35% har valgt
- **Zero Forecast**: 5 (35% > 30% for 500-1000 range)
- **Original**: NULL eller 0
- **Adjusted**: 5

---

## 🎯 Validation & Edge Cases

### Edge Case Håndtering
1. **NULL forecasts**: Behandles som 0, får zero forecast hvis intern
2. **Negative stock**: Buffer = 0, special handling
3. **100% deltagelse**: Specielle regler kan tilføjes
4. **Lukket produkt**: Ingen justeringer efter lukning

### Data Validation
- Shop info skal være tilgængelig for autopilot
- Navision data bruges til ekstern/intern klassifikation
- Ordre count minimum filter (≥5) før analyse

### Performance Considerations
- Beregninger er O(n) per produkt
- Shop info caches per analyse session  
- Komplekse queries optimeres med indexer