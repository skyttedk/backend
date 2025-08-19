# Database Schema - Adapt Accuracy Analysis

## 📊 Tabel Oversigt

### Primære Tabeller

#### `present_reservation`
**Beskrivelse**: Hovedtabellen der indeholder adapt forudsigelser og reservationer

```sql
CREATE TABLE `present_reservation` (
  `id` int NOT NULL AUTO_INCREMENT,
  `shop_id` int NOT NULL,
  `present_id` int NOT NULL,
  `model_id` int DEFAULT '0',
  `quantity` int DEFAULT '0',              -- Reserveret antal
  `old_quantity` int DEFAULT '0',
  `warning_level` decimal(15,2) DEFAULT '0.00',
  `current_level` decimal(15,2) DEFAULT '0.00',
  `replacement_present_id` int DEFAULT NULL,
  `replacement_present_name` varchar(200) DEFAULT NULL,
  `do_close` tinyint(1) NOT NULL DEFAULT '0',    -- Markeret til lukning
  `is_close` tinyint(1) NOT NULL DEFAULT '0',    -- Faktisk lukket
  `warning_issued` tinyint DEFAULT '0',
  `quantity_done` int NOT NULL DEFAULT '0',
  `skip_navision` int NOT NULL DEFAULT '0',
  `ship_monitoring` int DEFAULT '0',
  `autotopilot` tinyint(1) NOT NULL DEFAULT '0', -- Autopilot aktiv
  `update_time` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `sync_time` timestamp NULL DEFAULT NULL,
  `last_change` timestamp NULL DEFAULT NULL,
  `sync_quantity` int DEFAULT NULL,
  `sync_note` varchar(100) DEFAULT NULL,
  `adapt_0` int DEFAULT NULL,              -- AI Forudsigelse 0
  `adapt_1` int DEFAULT NULL,              -- AI Forudsigelse 1
  `adapt_2` int DEFAULT NULL,              -- AI Forudsigelse 2
  `adapt_3` int DEFAULT NULL,              -- AI Forudsigelse 3
  PRIMARY KEY (`id`),
  UNIQUE KEY `shop_id` (`shop_id`,`present_id`,`model_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
```

**Vigtige Felter for Analyse**:
- `adapt_0-3`: AI/ML forudsigelser
- `quantity`: Faktisk reserveret antal
- `is_close`: Om produktet er lukket (1=lukket, 0=aktiv)  
- `autotopilot`: Om autopilot har justeret (1=ja, 0=nej)
- `warning_level`: Autopilot warning threshold

---

#### `order`
**Beskrivelse**: Faktiske kundeordrer til sammenligning med forudsigelser

```sql
CREATE TABLE `order` (
  `id` int NOT NULL,
  `order_no` int NOT NULL DEFAULT '0',
  `order_timestamp` datetime NOT NULL,     -- Ordre tidspunkt
  `shop_id` int NOT NULL,                  -- Shop ID
  `shop_is_gift_certificate` tinyint DEFAULT NULL,
  `shop_is_company` tinyint DEFAULT NULL,
  `company_id` int NOT NULL,
  `shopuser_id` int NOT NULL,
  `user_username` varchar(250) NOT NULL,
  `user_email` varchar(250) NOT NULL,
  `user_name` varchar(250) NOT NULL,
  `present_id` int NOT NULL,               -- Produkt ID
  `present_name` varchar(100) NOT NULL,
  `present_model_id` int DEFAULT '0',      -- Model ID
  `present_model_name` varchar(250) DEFAULT '',
  `is_demo` tinyint DEFAULT '0',           -- Demo ordre (ignoreres)
  `language_id` int DEFAULT '0',
  `is_delivery` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
```

**Filter Kriterier**:
- `is_demo = 0`: Kun reelle ordrer (ikke demo)
- Group by `shop_id`, `present_id`, `present_model_id`
- Count ordrer per produkt

---

#### `shop`
**Beskrivelse**: Shop information til autopilot beregninger

```sql
CREATE TABLE `shop` (
  `id` int NOT NULL,
  `name` varchar(50) NOT NULL DEFAULT '',
  `active` tinyint DEFAULT '1',
  `localisation` int NOT NULL DEFAULT '1', -- Sprog (1=dansk)
  -- ... andre felter
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
```

---

#### `navision_item`
**Beskrivelse**: Navision ERP integration for produkttype klassifikation

```sql
CREATE TABLE `navision_item` (
  `id` int NOT NULL,
  `language_id` int NOT NULL,
  `no` varchar(50) NOT NULL,               -- SKU nummer
  `description` varchar(50) NOT NULL,
  `type` varchar(50) NOT NULL,             -- Produkttype
  `unit_price` varchar(50) NOT NULL,
  `is_external` int NOT NULL DEFAULT '0',  -- 0=intern, >0=ekstern
  `deleted` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
```

**Ekstern vs Intern**:
- `is_external = 0`: Intern gave (kan justeres af autopilot)
- `is_external > 0`: Ekstern gave (beskyttet mod autopilot)

---

### Support Tabeller

#### `present_reservation_log`
**Beskrivelse**: Historik log af alle ændringer til reservationer

```sql
CREATE TABLE `present_reservation_log` (
  `id` int NOT NULL,
  `shop_id` int NOT NULL,
  `present_id` int NOT NULL,
  `model_id` int DEFAULT '0',
  `quantity` int DEFAULT '0',
  `adapt_0` int DEFAULT NULL,
  `adapt_1` int DEFAULT NULL,
  `adapt_2` int DEFAULT NULL,
  `adapt_3` int DEFAULT NULL,
  `autotopilot` tinyint(1) NOT NULL DEFAULT '0',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
```

#### `shop_present`
**Beskrivelse**: Kobling mellem shops og produkter

```sql
CREATE TABLE `shop_present` (
  `id` int NOT NULL,
  `shop_id` int NOT NULL,
  `present_id` int NOT NULL,
  `active` tinyint DEFAULT '1',            -- Aktivt i shop
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `shop_present` (`shop_id`,`present_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
```

---

## 🔍 Analyse Query Logik

### Hovedquery for Reservationer
```sql
SELECT 
    pr.id,
    pr.shop_id,
    pr.present_id,
    pr.model_id,
    pr.quantity,
    pr.adapt_1,
    pr.adapt_2,
    pr.adapt_3,
    pr.is_close,
    pr.warning_level,
    pr.current_level,
    pr.autotopilot,
    pm.model_name,
    pm.model_present_no,
    p.name as present_name,
    ni.is_external,
    ni.type as navision_type,
    -- Ordre count subquery
    (
        SELECT COUNT(*) 
        FROM gavefabrikken2024.order o 
        WHERE o.present_id = pr.present_id 
        AND o.present_model_id = pr.model_id 
        AND o.shop_id = pr.shop_id 
        AND o.is_demo = 0
    ) as order_count
FROM gavefabrikken2024.present_reservation pr
LEFT JOIN gavefabrikken2024.present_model pm 
    ON pr.model_id = pm.model_id AND pm.language_id = 1
LEFT JOIN gavefabrikken2024.present p 
    ON pr.present_id = p.id
LEFT JOIN gavefabrikken2024.navision_item ni 
    ON pm.model_present_no = ni.no AND ni.language_id = 1 AND ni.deleted IS NULL
WHERE pr.shop_id = ?
AND (pr.adapt_1 IS NOT NULL OR pr.adapt_2 IS NOT NULL OR pr.adapt_3 IS NOT NULL)
HAVING order_count >= 5  -- Minimum ordre filter
ORDER BY pr.present_id, pr.model_id;
```

### Shop Information Query
```sql
SELECT 
    s.id,
    s.name,
    s.localisation as language_id,
    -- Total ordrer for autopilot beregninger
    (
        SELECT COUNT(DISTINCT o.id) 
        FROM gavefabrikken2024.order o 
        WHERE o.shop_id = s.id AND o.is_demo = 0
    ) as total_orders,
    -- Total brugere
    (
        SELECT COUNT(DISTINCT su.id) 
        FROM gavefabrikken2024.shop_user su 
        WHERE su.shop_id = s.id
    ) as total_users,
    -- Procent der har valgt (til autopilot thresholds)
    CASE 
        WHEN (SELECT COUNT(DISTINCT su.id) FROM gavefabrikken2024.shop_user su WHERE su.shop_id = s.id) > 0
        THEN ROUND(
            (COUNT(DISTINCT o.shopuser_id) * 100.0) / 
            (SELECT COUNT(DISTINCT su.id) FROM gavefabrikken2024.shop_user su WHERE su.shop_id = s.id), 2
        )
        ELSE 0
    END as procent_selected
FROM gavefabrikken2024.shop s
LEFT JOIN gavefabrikken2024.order o ON s.id = o.shop_id AND o.is_demo = 0
WHERE s.id = ?
GROUP BY s.id, s.name, s.localisation;
```

---

## 🎯 Data Flow

### 1. Data Indsamling
```
present_reservation (adapt forudsigelser)
         ↓
      JOIN med
         ↓
present_model (produkt info) + navision_item (ekstern/intern)
         ↓
      FILTER
         ↓
Kun produkter med ≥5 ordrer (order tabel)
```

### 2. Autopilot Beregning
```
Shop info (total_orders, procent_selected)
         ↓
Beregn Adapt Stage (0-3)
         ↓
Beregn Forecast Multiplier (1.05x - 1.3x)
         ↓
Juster forudsigelser baseret på:
- Eksterne gaver (beskyttet)
- Zero selected (specielle værdier)  
- Procent thresholds
```

### 3. Accuracy Analyse
```
Original Forudsigelse vs Faktisk
         +
Autopilot Justeret vs Faktisk
         ↓
Beregn nøjagtigheds procenter
         ↓
Kategoriser (Excellent/Good/Fair/Poor)
```

---

## 📈 Performance Overvejelser

### Indexer
- `present_reservation`: (shop_id, present_id, model_id)
- `order`: (shop_id, present_id, present_model_id, is_demo)
- `navision_item`: (no, language_id, deleted)

### Query Optimering
- Brug HAVING til ordre count filter efter aggregering
- LEFT JOINs for at bevare alle reservationer  
- Subqueries i SELECT for at undgå komplekse GROUP BY

### Caching Strategi
- Shop info caches per analyse session
- Navision data er relativt statisk
- Ordre data opdateres real-time