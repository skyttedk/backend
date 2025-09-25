Her er den tekniske opgavebeskrivelse for at udvide valgshop-indstillingerne til at understøtte dato+tid samt opdatere cron-endpointet.

Resumé
- Admin-UI skal understøtte både dato og klokkeslæt for start og slut.
- Backend skal acceptere/gemme datetime (Y-m-d H:i[:s]) for start_date/end_date.
- Cron/endpoint-logik skal tage højde for klokkeslæt ved åbning/lukning af shops.
- Ingen DB-migration nødvendig (felter er allerede DATETIME).

Database (ingen ændringer)
- Table: shop (bekræftet)
  - start_date DATETIME
  - end_date DATETIME

Backend
1) Validering og parsing af dato/tid ved gem af shop
- Fil: backend/model/shop.class.php
  - Nuværende: validerer end_date (og tilsvarende start_date) med regex for dato (f.eks. /\d{4}-\d{2}-\d{2}/) og dermed uden tid.
  - Ændr til at acceptere og normalisere følgende formater til "Y-m-d H:i:s":
    - "Y-m-d"
    - "Y-m-d H:i"
    - "Y-m-d H:i:s"
  - Hvis kun dato leveres:
    - start_date -> 00:00:00
    - end_date -> 23:59:59 (for at bevare nuværende semantik)
  - Implementér med DateTime::createFromFormat med fallback på flere formater; kast exception ved ugyldig dato/tid.

2) Cron/endpoint – tidssensitiv åbne/lukke-logik
- Fil: backend/controller/externalController.php
  - Metode: openShops()
    - Nuværende udvælgelse bruger dagsvindue:
      - start_date >= date('Y-m-d') AND start_date < next day
    - Opdater til tidsbevidst udvælgelse (vælg én af nedenstående modeller):
      A) "Åben-nu" (idempotent tilstand)
         - $now = date('Y-m-d H:i:s');
         - Vælg shops: start_date <= $now AND (end_date IS NULL OR $now < end_date)
         - Sørg for at efterfølgende handlinger er idempotente (gentagne cron-kørsler må ikke give bivirkninger).
      B) "Vindue" (cron fx hvert 5. minut)
         - $windowStart = gulv til nærmeste minut
         - $windowEnd = +5 min
         - Vælg shops: start_date >= $windowStart AND start_date < $windowEnd
  - Overvej at tilføje closeShops() til at håndtere luk ved end_date:
    - Vælg shops: end_date IS NOT NULL AND end_date <= $now
    - Også idempotent (ingen dubletter/sideeffekter ved gentagne kald).
  - Timezone: Sørg for gennemgående Europe/Copenhagen (globalt eller i controller).

3) Øvrige backend-referencer
- Fil: backend/controller/warehousePortalController.php
  - Joiner shop.start_date/end_date for visning. Ingen logik-ændring nødvendig, men vær opmærksom på at tider nu vises.
- Fil: backend/model/shopuser.class.php
  - Shop adgang styres også via shop_user.blocked/expire_date. Denne opgave ændrer ikke den politik, men test at nyt open/close-flow ikke konflikter.

Frontend (Admin UI)
1) Formularfelter for periode (start/slut)
- Fil: backend/views/images/js/company.js
  - Referencer til #shopFrom2 og #shopTo2 findes (lyttere i company.js). Opdater til at understøtte datetime:
    - Opdater til input type="datetime-local" i tilhørende view-template (hvor #shopFrom2/#shopTo2 er defineret), eller brug en datetime-picker.
    - Indlæsning: parse værdier fra "Y-m-d H:i:s" til widgetens forventede format (ofte "YYYY-MM-DDTHH:mm" for datetime-local).
    - Afsendelse: send som "YYYY-MM-DD HH:mm" (backend normaliserer til sekunder).
    - Frontend-validering: sikr at start <= slut, og giv brugbar fejlbesked.
- View-template (PHP/HTML) med #shopFrom2/#shopTo2
  - Tilpas markup til datetime inputs og bevar eksisterende felt-navne (start_date, end_date) i POST/JSON payloads.
  - Tilføj hjælpetekst om format og tidszone.

Timezone
- Sæt/valider default timezone til Europe/Copenhagen både ved visning og i backend (date_default_timezone_set('Europe/Copenhagen'); eller via applikationskonfiguration).

Test og acceptkriterier
- Gem shop med:
  - Kun dato: start 2025-10-01 → 2025-10-01 00:00:00; slut 2025-10-10 → 2025-10-10 23:59:59
  - Dato+tid: 2025-11-01 08:30 til 2025-11-10 16:00 → gemmes nøjagtigt
- Cron/endpoint:
  - Shop åbner først når start_date-tidspunktet er passeret (ikke ved midnat).
  - Shop lukker når end_date-tidspunktet er passeret (hvis closeShops implementeres) eller optræder ikke som “åben nu” bagefter.
  - Gentagne cron-kørsler er idempotente (ingen dubletter eller utilsigtede sideeffekter).
- UI:
  - Admin kan indtaste og se klokkeslæt. Validering forhindrer slut < start. Visning matcher lokal tid (CET/CEST).

Fil-, klasse- og tabelreferencer
- Database
  - Table: shop (start_date DATETIME, end_date DATETIME)
- Backend
  - controller/externalController.php (openShops og evt. ny closeShops)
  - model/shop.class.php (udvidet validering/parsing af start_date/end_date)
  - controller/warehousePortalController.php (visning af start/end)
- Frontend (admin)
  - views/images/js/company.js (#shopFrom2, #shopTo2)
  - Tilhørende view-template for shop-indstillinger (hvor felterne defineres)

Estimat
- Backend model-ændringer: 1–2 timer
- Endpoint-logik (openShops + evt. closeShops) inkl. test: 2–4 timer
- Frontend UI (datetime felter + validering): 2–3 timer
- Test: 1–2 timer
