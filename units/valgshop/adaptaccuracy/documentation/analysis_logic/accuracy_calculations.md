# Accuracy Calculations - Detaljeret Algoritme Dokumentation

## 📊 Nøjagtigheds Beregning Oversigt

Accuracy analysen sammenligner AI/ML forudsigelser med faktiske salgsdata og beregner præcisionsprocenter. Systemet beregner både original og autopilot-justeret nøjagtighed.

---

## 🎯 Grundlæggende Accuracy Formler

### Standard Accuracy Beregning
```php
function calculateAccuracy($predicted, $actual) {
    if ($predicted <= 0) return 0;
    
    $difference = abs($predicted - $actual);
    $accuracy = max(0, (1 - ($difference / max($predicted, $actual))) * 100);
    
    return round($accuracy, 2);
}
```

### Forbedret Accuracy med Tolerance
```php
function calculateAccuracyWithTolerance($predicted, $actual, $tolerance = 0.1) {
    if ($predicted <= 0) return 0;
    
    $difference = abs($predicted - $actual);
    $toleranceAmount = $predicted * $tolerance; // 10% tolerance
    
    if ($difference <= $toleranceAmount) {
        return 100; // Perfect within tolerance
    }
    
    $adjustedDifference = $difference - $toleranceAmount;
    $accuracy = max(0, (1 - ($adjustedDifference / max($predicted, $actual))) * 100);
    
    return round($accuracy, 2);
}
```

---

## 📋 Kategorisering System

### Accuracy Kategorier
```php
function categorizeAccuracy($accuracy) {
    if ($accuracy >= 90) return 'excellent';
    if ($accuracy >= 75) return 'good';
    if ($accuracy >= 50) return 'fair';
    return 'poor';
}

function getAccuracyColor($category) {
    $colors = [
        'excellent' => '#28a745',  // Grøn
        'good'      => '#17a2b8',  // Blå
        'fair'      => '#ffc107',  // Gul
        'poor'      => '#dc3545'   // Rød
    ];
    return $colors[$category] ?? '#6c757d';
}
```

### Kategorise Tresholds
| Kategori | Procentinterval | Farve | Beskrivelse |
|----------|----------------|-------|-------------|
| Excellent | ≥90% | Grøn | Meget præcis forudsigelse |
| Good | 75-89% | Blå | God forudsigelse |
| Fair | 50-74% | Gul | Acceptabel forudsigelse |
| Poor | <50% | Rød | Upræcis forudsigelse |

---

## 🔄 Dual Accuracy System

### Original vs Autopilot Sammenligning
```php
function calculateDualAccuracy($originalPredict, $autopilotPredict, $actual) {
    return [
        'original' => [
            'predicted' => $originalPredict,
            'accuracy' => calculateAccuracy($originalPredict, $actual),
            'category' => categorizeAccuracy(calculateAccuracy($originalPredict, $actual))
        ],
        'autopilot' => [
            'predicted' => $autopilotPredict,
            'accuracy' => calculateAccuracy($autopilotPredict, $actual),
            'category' => categorizeAccuracy(calculateAccuracy($autopilotPredict, $actual))
        ],
        'improvement' => calculateAccuracy($autopilotPredict, $actual) - calculateAccuracy($originalPredict, $actual)
    ];
}
```

### Improvement Tracking
```php
function calculateImprovement($originalAccuracy, $autopilotAccuracy) {
    $improvement = $autopilotAccuracy - $originalAccuracy;
    
    return [
        'absolute' => round($improvement, 2),
        'relative' => $originalAccuracy > 0 ? round(($improvement / $originalAccuracy) * 100, 2) : 0,
        'status' => $improvement > 0 ? 'improved' : ($improvement < 0 ? 'degraded' : 'unchanged')
    ];
}
```

---

## 📈 Aggregat Statistikker

### Shop-niveau Statistikker
```php
function calculateShopStatistics($products) {
    $stats = [
        'total_products' => count($products),
        'adapt_1' => ['total' => 0, 'accurate' => 0],
        'adapt_2' => ['total' => 0, 'accurate' => 0],
        'adapt_3' => ['total' => 0, 'accurate' => 0],
        'categories' => ['excellent' => 0, 'good' => 0, 'fair' => 0, 'poor' => 0]
    ];
    
    foreach ($products as $product) {
        foreach (['adapt_1', 'adapt_2', 'adapt_3'] as $adapt) {
            if (!empty($product[$adapt])) {
                $accuracy = calculateAccuracy($product[$adapt], $product['actual_orders']);
                $category = categorizeAccuracy($accuracy);
                
                $stats[$adapt]['total']++;
                if ($accuracy >= 75) $stats[$adapt]['accurate']++;
                $stats['categories'][$category]++;
            }
        }
    }
    
    // Beregn gennemsnitlige accuracies
    foreach (['adapt_1', 'adapt_2', 'adapt_3'] as $adapt) {
        $stats[$adapt]['accuracy'] = $stats[$adapt]['total'] > 0 
            ? round(($stats[$adapt]['accurate'] / $stats[$adapt]['total']) * 100, 2)
            : 0;
    }
    
    return $stats;
}
```

### Best Forecast Identifikation
```php
function findBestForecast($adapt1, $adapt2, $adapt3, $actual) {
    $forecasts = [
        'adapt_1' => ['value' => $adapt1, 'accuracy' => calculateAccuracy($adapt1, $actual)],
        'adapt_2' => ['value' => $adapt2, 'accuracy' => calculateAccuracy($adapt2, $actual)],
        'adapt_3' => ['value' => $adapt3, 'accuracy' => calculateAccuracy($adapt3, $actual)]
    ];
    
    // Filtrer ikke-eksisterende forecasts
    $forecasts = array_filter($forecasts, function($f) { return $f['value'] > 0; });
    
    if (empty($forecasts)) return null;
    
    // Find den mest præcise
    $best = array_reduce($forecasts, function($carry, $current) {
        return (!$carry || $current['accuracy'] > $carry['accuracy']) ? $current : $carry;
    });
    
    return $best;
}
```

---

## 🏆 Ranking og Performance

### Shop Ranking Algoritme
```php
function calculateShopRanking($shopStats) {
    $rankings = [];
    
    foreach ($shopStats as $shopId => $stats) {
        $avgAccuracy = ($stats['adapt_1']['accuracy'] + 
                       $stats['adapt_2']['accuracy'] + 
                       $stats['adapt_3']['accuracy']) / 3;
        
        $rankings[] = [
            'shop_id' => $shopId,
            'avg_accuracy' => round($avgAccuracy, 2),
            'total_products' => $stats['total_products'],
            'weighted_score' => calculateWeightedScore($stats)
        ];
    }
    
    // Sorter efter weighted score
    usort($rankings, function($a, $b) {
        return $b['weighted_score'] <=> $a['weighted_score'];
    });
    
    return $rankings;
}

function calculateWeightedScore($stats) {
    $weights = [
        'accuracy' => 0.7,      // 70% vægt på nøjagtighed
        'volume' => 0.2,        // 20% vægt på volumen
        'consistency' => 0.1    // 10% vægt på konsistens
    ];
    
    $avgAccuracy = ($stats['adapt_1']['accuracy'] + 
                   $stats['adapt_2']['accuracy'] + 
                   $stats['adapt_3']['accuracy']) / 3;
    
    $volumeScore = min(100, ($stats['total_products'] / 50) * 100); // Max ved 50 produkter
    
    $consistency = calculateConsistency($stats['adapt_1']['accuracy'], 
                                      $stats['adapt_2']['accuracy'], 
                                      $stats['adapt_3']['accuracy']);
    
    return ($avgAccuracy * $weights['accuracy']) + 
           ($volumeScore * $weights['volume']) + 
           ($consistency * $weights['consistency']);
}
```

---

## 📊 Hit Rate Beregninger

### Hit Rate Definition
Hit rate defineres som procenten af forudsigelser der ligger inden for en acceptabel tolerance.

```php
function calculateHitRate($predictions, $actuals, $tolerance = 0.2) {
    $hits = 0;
    $total = count($predictions);
    
    for ($i = 0; $i < $total; $i++) {
        $predicted = $predictions[$i];
        $actual = $actuals[$i];
        
        if ($predicted <= 0) continue;
        
        $toleranceAmount = $predicted * $tolerance; // 20% tolerance
        $difference = abs($predicted - $actual);
        
        if ($difference <= $toleranceAmount) {
            $hits++;
        }
    }
    
    return $total > 0 ? round(($hits / $total) * 100, 2) : 0;
}
```

### Hit Rate Kategorier
```php
function categorizeHitRate($hitRate) {
    if ($hitRate >= 80) return 'excellent';
    if ($hitRate >= 60) return 'good';
    if ($hitRate >= 40) return 'fair';
    return 'poor';
}
```

---

## 🎯 Advanced Metrics

### Mean Absolute Error (MAE)
```php
function calculateMAE($predictions, $actuals) {
    $totalError = 0;
    $count = 0;
    
    for ($i = 0; $i < count($predictions); $i++) {
        if ($predictions[$i] > 0) {
            $totalError += abs($predictions[$i] - $actuals[$i]);
            $count++;
        }
    }
    
    return $count > 0 ? round($totalError / $count, 2) : 0;
}
```

### Root Mean Square Error (RMSE)
```php
function calculateRMSE($predictions, $actuals) {
    $totalSquaredError = 0;
    $count = 0;
    
    for ($i = 0; $i < count($predictions); $i++) {
        if ($predictions[$i] > 0) {
            $error = $predictions[$i] - $actuals[$i];
            $totalSquaredError += $error * $error;
            $count++;
        }
    }
    
    return $count > 0 ? round(sqrt($totalSquaredError / $count), 2) : 0;
}
```

### Mean Absolute Percentage Error (MAPE)
```php
function calculateMAPE($predictions, $actuals) {
    $totalPercentageError = 0;
    $count = 0;
    
    for ($i = 0; $i < count($predictions); $i++) {
        if ($predictions[$i] > 0 && $actuals[$i] > 0) {
            $percentageError = abs(($actuals[$i] - $predictions[$i]) / $actuals[$i]) * 100;
            $totalPercentageError += $percentageError;
            $count++;
        }
    }
    
    return $count > 0 ? round($totalPercentageError / $count, 2) : 0;
}
```

---

## 🔍 Outlier Detection

### Statistical Outlier Detection
```php
function detectOutliers($accuracies) {
    if (count($accuracies) < 4) return [];
    
    $mean = array_sum($accuracies) / count($accuracies);
    $variance = 0;
    
    foreach ($accuracies as $accuracy) {
        $variance += pow($accuracy - $mean, 2);
    }
    
    $stdDev = sqrt($variance / count($accuracies));
    $threshold = 2; // 2 standard deviations
    
    $outliers = [];
    foreach ($accuracies as $index => $accuracy) {
        if (abs($accuracy - $mean) > ($threshold * $stdDev)) {
            $outliers[] = [
                'index' => $index,
                'value' => $accuracy,
                'deviation' => abs($accuracy - $mean) / $stdDev
            ];
        }
    }
    
    return $outliers;
}
```

---

## 📈 Trend Analysis

### Accuracy Trend Over Time
```php
function calculateAccuracyTrend($timeSeriesData) {
    if (count($timeSeriesData) < 2) return 0;
    
    $n = count($timeSeriesData);
    $sumX = 0; $sumY = 0; $sumXY = 0; $sumX2 = 0;
    
    foreach ($timeSeriesData as $i => $accuracy) {
        $x = $i + 1; // Time index
        $y = $accuracy;
        
        $sumX += $x;
        $sumY += $y;
        $sumXY += $x * $y;
        $sumX2 += $x * $x;
    }
    
    // Linear regression slope
    $slope = ($n * $sumXY - $sumX * $sumY) / ($n * $sumX2 - $sumX * $sumX);
    
    return round($slope, 4);
}
```

---

## 📊 Data Validation

### Input Validation
```php
function validateAccuracyInputs($predicted, $actual) {
    $errors = [];
    
    if (!is_numeric($predicted) || $predicted < 0) {
        $errors[] = 'Predicted value must be a non-negative number';
    }
    
    if (!is_numeric($actual) || $actual < 0) {
        $errors[] = 'Actual value must be a non-negative number';
    }
    
    if ($predicted == 0 && $actual == 0) {
        $errors[] = 'Both predicted and actual cannot be zero';
    }
    
    return $errors;
}
```

### Data Quality Metrics
```php
function calculateDataQuality($dataset) {
    $total = count($dataset);
    $complete = 0;
    $withPredictions = 0;
    $withActuals = 0;
    
    foreach ($dataset as $record) {
        if (isset($record['predicted']) && isset($record['actual'])) {
            $complete++;
        }
        if (isset($record['predicted']) && $record['predicted'] > 0) {
            $withPredictions++;
        }
        if (isset($record['actual']) && $record['actual'] > 0) {
            $withActuals++;
        }
    }
    
    return [
        'completeness' => round(($complete / $total) * 100, 2),
        'prediction_coverage' => round(($withPredictions / $total) * 100, 2),
        'actual_coverage' => round(($withActuals / $total) * 100, 2),
        'quality_score' => round((($complete + $withPredictions + $withActuals) / ($total * 3)) * 100, 2)
    ];
}
```

---

## 🎯 Eksempel Beregninger

### Scenario 1: Perfect Prediction
```php
$predicted = 10;
$actual = 10;
$accuracy = calculateAccuracy($predicted, $actual); // Result: 100%
$category = categorizeAccuracy($accuracy);           // Result: 'excellent'
```

### Scenario 2: Overprediction
```php
$predicted = 15;
$actual = 10;
$accuracy = calculateAccuracy($predicted, $actual); // Result: 66.67%
$category = categorizeAccuracy($accuracy);           // Result: 'fair'
```

### Scenario 3: Underprediction
```php
$predicted = 8;
$actual = 12;
$accuracy = calculateAccuracy($predicted, $actual); // Result: 66.67%
$category = categorizeAccuracy($accuracy);           // Result: 'fair'
```

### Scenario 4: Autopilot Improvement
```php
$originalPredict = 8;
$autopilotPredict = 11;
$actual = 12;

$originalAccuracy = calculateAccuracy($originalPredict, $actual);  // 66.67%
$autopilotAccuracy = calculateAccuracy($autopilotPredict, $actual); // 91.67%
$improvement = $autopilotAccuracy - $originalAccuracy;              // +25%
```