# Accuracy Definitions - Præcise Vurderingskriterier

## 📊 Oversigt

Dette dokument definerer præcist hvordan systemet vurderer forecast accuracy og kategoriserer resultater. Der er forskellige kriterier for **Original Forecasts** vs **Autopilot Forecasts**.

---

## 🎯 Accuracy Kategorier

### Beregning af Accuracy Procent

**Original Forecast Formula:**
```php
$accuracy_percent = round((1 - abs($difference) / max($predicted, $actual)) * 100, 2);
```

**Autopilot Forecast Formula:**
```php
if ($actual <= $predicted) {
    // We had enough - calculate based on overallocation efficiency
    $efficiency_ratio = $actual / $predicted;
    $accuracy_percent = round($efficiency_ratio * 100, 2);
    
    // Bonus for minimal waste (<=20% overallocation)
    if ($efficiency_ratio >= 0.8) {
        $accuracy_percent = min(100, $accuracy_percent + (($efficiency_ratio - 0.8) * 50));
    }
} else {
    // We didn't have enough - penalty for stockout
    $stockout_ratio = $actual / $predicted;
    $accuracy_percent = round(max(0, 100 - (($stockout_ratio - 1) * 100)), 2);
}
```

### Kategori Tresholds

| Kategori | Procent Range | Farve | Beskrivelse |
|----------|---------------|-------|-------------|
| **Excellent** | ≥90% | 🟢 Grøn | Meget præcis forudsigelse |
| **Good** | 75-89% | 🔵 Blå | God forudsigelse |
| **Fair** | 50-74% | 🟡 Gul | Acceptabel forudsigelse |
| **Poor** | <50% | 🔴 Rød | Upræcis forudsigelse |

**Kode Implementation:**
```php
$accuracy_category = 'poor';
if ($accuracy_percent >= 90) {
    $accuracy_category = 'excellent';
} elseif ($accuracy_percent >= 75) {
    $accuracy_category = 'good';
} elseif ($accuracy_percent >= 50) {
    $accuracy_category = 'fair';
}
```

---

## 🎯 Hit Rate Kategorier

Hit rate beskriver hvor "tæt på" en forudsigelse var, uafhængigt af den matematiske accuracy.

### Original Forecast Hit Rates

| Hit Rate | Betingelse | Beskrivelse | Eksempel |
|----------|------------|-------------|----------|
| **Exact** | `predicted == actual` | Perfect match | Forudsagt: 10, Faktisk: 10 |
| **Close** | `abs(difference) <= 1` | Inden for 1 styk | Forudsagt: 10, Faktisk: 9 eller 11 |
| **Good** | `accuracy_percent >= 75%` | Høj accuracy | Forudsagt: 10, Faktisk: 8 (80% accuracy) |
| **Miss** | Alle andre | Ramt forbi | Forudsagt: 10, Faktisk: 5 (50% accuracy) |

**Kode Implementation:**
```php
$hit_rate = 'miss';
if ($predicted == $actual) {
    $hit_rate = 'exact';
} elseif (abs($difference) <= 1) {
    $hit_rate = 'close';
} elseif ($accuracy_percent >= 75) {
    $hit_rate = 'good';
}
```

### Autopilot Forecast Hit Rates

Autopilot fokuserer på **service level** (undgå stockouts) i stedet for præcis match.

| Hit Rate | Betingelse | Beskrivelse | Eksempel |
|----------|------------|-------------|----------|
| **Excellent** | `actual <= predicted AND predicted <= actual * 1.3` | God service level + rimelig buffer | Forudsagt: 11, Faktisk: 10 |
| **Good** | `actual <= predicted` | Ingen stockout, men måske for meget buffer | Forudsagt: 15, Faktisk: 10 |
| **Fair** | `predicted >= actual * 0.8` | Mindre stockout (≤20%) | Forudsagt: 8, Faktisk: 10 |
| **Miss** | Alle andre | Betydelig stockout | Forudsagt: 5, Faktisk: 10 |

**Kode Implementation:**
```php
$hit_rate = 'miss';
if ($actual <= $predicted && $predicted <= $actual * 1.3) {
    $hit_rate = 'excellent'; // Good service level with reasonable buffer
} elseif ($actual <= $predicted) {
    $hit_rate = 'good'; // No stockout but maybe too much buffer
} elseif ($predicted >= $actual * 0.8) {
    $hit_rate = 'fair'; // Minor stockout
}
```

---

## ✅ "Is Accurate" Threshold

Systemet markerer en forudsigelse som "accurate" baseret på forskellige kriterier:

### Original Forecast
```php
$is_accurate = ($accuracy_percent >= 85) || ($predicted == $actual);
```

**Betingelser:**
- **85% accuracy eller højere** OR
- **Exact match** (perfect prediction)

### Autopilot Forecast
```php
$is_accurate = ($actual <= $predicted && $accuracy_percent >= 70);
```

**Betingelser:**
- **Ingen stockout** (actual ≤ predicted) AND
- **70% accuracy eller højere**

---

## 📋 Praktiske Eksempler

### Eksempel 1: Original Forecast - Excellent
- **Forudsagt:** 10
- **Faktisk:** 10
- **Difference:** 0
- **Accuracy:** 100%
- **Category:** Excellent
- **Hit Rate:** Exact
- **Is Accurate:** ✅ Yes

### Eksempel 2: Original Forecast - Good
- **Forudsagt:** 10
- **Faktisk:** 8
- **Difference:** -2
- **Accuracy:** 80% (1 - 2/10 = 0.8)
- **Category:** Good
- **Hit Rate:** Good
- **Is Accurate:** ✅ Yes (≥85% ikke opfyldt, men ikke exact match)

*Note: Dette eksempel ville faktisk være "Not Accurate" da 80% < 85%*

### Eksempel 3: Original Forecast - Fair
- **Forudsagt:** 10
- **Faktisk:** 6
- **Difference:** -4
- **Accuracy:** 60% (1 - 4/10 = 0.6)
- **Category:** Fair
- **Hit Rate:** Miss
- **Is Accurate:** ❌ No

### Eksempel 4: Autopilot Forecast - Excellent
- **Forudsagt:** 11 (autopilot buffer)
- **Faktisk:** 10
- **Service Level:** ✅ No stockout (10 ≤ 11)
- **Buffer Efficiency:** 10/11 = 90.9%
- **Within 30% buffer:** ✅ Yes (11 ≤ 10 * 1.3 = 13)
- **Category:** Excellent
- **Hit Rate:** Excellent
- **Is Accurate:** ✅ Yes

### Eksempel 5: Autopilot Forecast - Fair
- **Forudsagt:** 8 (autopilot underprediction)
- **Faktisk:** 10
- **Service Level:** ❌ Stockout (10 > 8)
- **Stockout Severity:** 8 ≥ 10 * 0.8 = 8 ✅ (Minor stockout)
- **Accuracy:** 20% (penalty for stockout)
- **Category:** Poor
- **Hit Rate:** Fair
- **Is Accurate:** ❌ No

---

## 🎨 Visuel Repræsentation

### Accuracy Color Coding
```css
.accuracy-excellent { background-color: #28a745; } /* Grøn */
.accuracy-good      { background-color: #17a2b8; } /* Blå */
.accuracy-fair      { background-color: #ffc107; } /* Gul */
.accuracy-poor      { background-color: #dc3545; } /* Rød */
```

### Hit Rate Icons
- **Exact:** 🎯 (bullseye)
- **Close:** 🔵 (blue circle)
- **Good:** ✅ (check mark)
- **Fair:** ⚠️ (warning)
- **Miss:** ❌ (cross mark)

---

## 🤖 Autopilot vs Original Sammenligning

### Filosofisk Forskel

**Original Forecasts:**
- Fokuserer på **præcision** 
- "Hvor tæt kom vi på det faktiske tal?"
- Symmetrisk penalty for over/under prediction

**Autopilot Forecasts:**
- Fokuserer på **service level**
- "Undgik vi stockouts og havde rimelig buffer?"
- Asymmetrisk penalty - stockouts er værre end overallocation

### Eksempel Sammenligning
- **Scenario:** 6 faktiske ordrer
- **Original Forecast:** 6 → 100% accuracy (Excellent)
- **Autopilot Forecast:** 11 → ~85% accuracy (Good)

**Konklusion:** Original var mere præcis, men autopilot sikrede service level med buffer mod uforudsete ordrer.

---

## 📊 Summary Tabel

| Metric | Original Forecast | Autopilot Forecast |
|--------|------------------|-------------------|
| **Focus** | Præcision | Service Level |
| **Excellent** | ≥90% accuracy | ≥90% + no stockout + ≤30% buffer |
| **Is Accurate** | ≥85% OR exact match | No stockout + ≥70% |
| **Hit Rate Logic** | Difference-based | Service-level-based |
| **Philosophy** | "Hvor tæt?" | "Var der nok varer?" |

Dette giver en komplet forståelse af hvordan systemet evaluerer forecast kvalitet på tværs af begge tilgange.