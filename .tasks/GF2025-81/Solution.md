# Opgave: Felt-definitioner (valgshops) – nye flags + QR-scanner tilpasninger

Dette oplæg beskriver de nødvendige ændringer i database, model, backend og frontend for at:

- Tilføje to nye boolean optioner til felt-definitioner i valgshops: "Søgbar" og "Vis ved søgning" (placeret efter "Vis").
- Opdatere QR-skanneren til gaveudlevering, så felter uden "Vis ved søgning" ikke vises i resultatvisning, og at man ikke kan søge på felter hvor "Søgbar" = false.

## Berørte systemdele og artefakter

### Database:
- **Tabel:** `shop_attribute` (felt-definitioner pr. shop)
- **Tabel:** `user_attribute` (værdi pr. bruger/ordre pr. attribute)

### Backend:
- **Model:** `model/shop_attribute.php` (ShopAttribute)
- **QR-scanner modul (endpoints):** `backend/app/qrscanner/index/*`
- **Routes anvendt fra UI:** `index.php?rt=app/qrscanner/index/getOrder`, `.../reg`, `.../undoReg`
- **Login-side:** `backend/app/qrscanner/index/login.php`
- **Shop/attribute administration (felt-definitioner):** controller/endpoints der håndterer oprettelse/opdatering af shop_attribute (typisk i `controller/shop.php` eller tilsvarende "shop" controller)

### Frontend/Views:
- **QR-scanner UI:** `backend/app/qrscanner/index/view.php`
- **Admin UI for felt-definitioner (valgshop):** form/view der allerede indeholder "Vis" (placer nye felter lige efter denne) – filnavn afhænger af nuværende opsætning, typisk i `views/shop/*.php`.
- **QR-bruger administration (kundepanel):** `backend/views/kundepanel_view.php` (ingen funktionsændring forventes, men vær OBS på evt. deling af attribute-lister).

## 1) Databaseændringer

Tabel `shop_attribute` udvides med to nye booleans. Navngivning følger eksisterende konvention (`is_*`):

```sql
ALTER TABLE `shop_attribute`
  ADD COLUMN `is_searchable` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_visible`,
  ADD COLUMN `is_visible_on_search` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_searchable`;
```

- **Søgbar** → kolonne: `is_searchable` (default 0)
- **Vis ved søgning** → kolonne: `is_visible_on_search` (default 0)

Eksisterende data påvirkes ikke funktionelt (defaults = 0). Administrator kan efterfølgende aktivere pr. felt.

## 2) Model- og controller-ændringer (backend)

### Model: `model/shop_attribute.php` (ShopAttribute)
- Tilføj felter/egenskaber: `is_searchable`, `is_visible_on_search`.
- Opdater hydrering (read) og persistence (create/update) til at inkludere de nye felter.
- Validering: cast til bool/tinyint (0/1).

### Admin endpoints (felt-definitioner)
I controller for shop/felt-definitioner (typisk `controller/shop.php`):
- Udvid create/update handlers til at modtage og gemme `is_searchable` og `is_visible_on_search`.
- Sørg for at værdierne sendes med fra admin UI-formen (checkboxes).

### QR-scanner endpoints: `backend/app/qrscanner/index/*`

#### getOrder:
- Når ordre/gave slås op (via QR eller søg), indlæses tilknyttede user_attribute-værdier.
- JOIN mod shop_attribute og filtrér de returned attributes til frontend, så `is_visible_on_search = 1` (kun disse må med i resultatsvar/visning).
- Bevar eksisterende sortering via `shop_attribute.index` hvis det anvendes til ordering.

#### Søge-endpoint/handler (den der bruges af "Søg"-knappen i skanneren):
- Ved indkommende søgning på et bestemt attribute_id (eller navn), valider at den valgte attribute har `is_searchable = 1`. Hvis ikke: returnér 400/fejl med besked (og log evt.).
- Byg søge-queries mod user_attribute kun på attributter der er `is_searchable = 1`.

#### reg og undoReg:
Ingen ændringer ift. denne opgave.

## 3) Frontend/Views

### Admin UI for felt-definitioner (valgshop)
I den eksisterende formular hvor "Vis" (til `is_visible`) konfigureres, tilføj to nye checkboxes lige efter denne:
- **Søgbar** (`is_searchable`)
- **Vis ved søgning** (`is_visible_on_search`)

Sørg for at POST payload inkluderer værdierne, og at controlleren gemmer dem (jf. ovenfor).

### QR-scanner UI: `backend/app/qrscanner/index/view.php`
- **Søgefelt/dropdown:** Hvis UI præsenterer en liste af felter man kan søge på, filtrér listen til kun felter hvor `is_searchable = 1` (alternativt disable + vis tooltip). Uanset hvad håndhæves reglen også server-side.
- **Resultatvisning:** Når getOrder-svaret gengives, vis kun de attributter som backend har returneret (som allerede er filtreret med `is_visible_on_search = 1`). Hvis der i dag vises "alle" felter i UI uafhængigt af respons, tilpas markup/JS så den dynamisk itererer over de returnerede felter.

## Implementeringsnoter

### Tabeller:
- `shop_attribute` (felt-definitioner) – allerede indeholder bl.a. `is_visible`, `is_list`, `is_mandatory` mv.
- `user_attribute` – relation: `user_attribute.attribute_id = shop_attribute.id`.

### Data-migrering:
Ingen bagudrettede ændringer – nye kolonner får default 0.

### Performance/caching:
Hvis QR-scanneren cacher attribute-lister, invalider/refresh efter deployment, ellers risikeres visning/søgning på ikke-autoriserede felter.

### Fejlhåndtering:
Returnér tydelig fejl hvis UI forsøger at søge på et felt der ikke er `is_searchable` (beskytter mod manipuleret klient).

## Test og acceptkriterier

- Admin kan åbne felt-definition for en shop og se to nye checkboxes "Søgbar" og "Vis ved søgning" lige efter "Vis".
- Gemning af felt-definition persisterer værdierne i `shop_attribute.is_searchable` og `shop_attribute.is_visible_on_search`.
- I QR-scanneren (`backend/app/qrscanner/index/login.php` → `view.php`):
  - Dropdown/valg for søgefelt indeholder kun felter med `is_searchable = 1`.
  - Forsøg på manuel søgning mod felt med `is_searchable = 0` afvises af backend.
  - Når en gave er slået op (QR eller søgning), vises kun de brugerfelter hvor `is_visible_on_search = 1`.
- Ingen regressions på reg/undoReg flow.

## Berørte filer (summary)

### DB:
`shop_attribute` (nye kolonner), læsning af `user_attribute` ifm. visning/søg.

### Model:
`model/shop_attribute.php` – tilføj felter i entity + mapper.

### Controllers:
- Shop attribute admin controller (fx `controller/shop.php`) – create/update.
- QR-scanner controller for getOrder og søgehandler i `backend/app/qrscanner/index/*`.

### Views/UI:
- Admin formular for felt-definitioner (placer checkboxes efter "Vis").
- QR-scanner UI: `backend/app/qrscanner/index/view.php` – filtrér søgefelter i UI og render kun returnerede attributter.
- Kundepanel: `backend/views/kundepanel_view.php` (ingen direkte ændring forventes – sanity check ift. eventuel genbrug af attribute-lister).

## Deployment

1. Kør DB migration (ALTER TABLE ovenfor) på alle relevante miljøer.
2. Deploy backend model/controller-ændringer.
3. Deploy UI-ændringer i admin og QR-scanner.
4. Ryd evt. caches for attribute-lister.
5. Giv gerne besked hvis vi skal for-sætte defaults (fx gøre bestemte felttyper søgbare pr. standard), ellers deployer vi med alle nye flags sat til 0 og lader admin konfigurere per shop.

---

vh
Kim – udvikling