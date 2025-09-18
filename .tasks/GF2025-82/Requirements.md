# Teknisk opgavebeskrivelse - Reservationssystem

Hej Kim

Nedenfor er en samlet teknisk opgavebeskrivelse til udviklingsteamet for de to opgaver på Valgsshops → Gaver i Shoppen → Reservationer.

## Overblik

**Opgave 1:** Fjern tre kolonner fra reservationslisten: "Registreres i NAV", "Lager overvåges", "Autopilot".

**Opgave 2:** Indfør servervalidering så reservationer ikke kan skabe negativt NAV-lager for eksterne varer; i stedet oprettes en godkendelses-anmodning, vis status i top-tabel og send e-mail til kss@gavefabrikken.dk.

## Berørte dele i kodebasen

### Backend views (liste og shop-indstillinger)
- `views/valgshop-gaver_view.php` (reservationslisten og tilknyttet JS-events)
- `views/shop_view.php` og/eller `views/shop_view2.php` → inkluderer `shopSettings_view.php` (øverste infoboks/tabel over shop-status – her tilføjes ny status-række)

### Backend controller/endpoint for bulk-opdatering af reservationer
Den nuværende opdateringshandling kaldes fra JS i reservations-UI ("Opdaterer reservation ændringer"). Der findes tilsvarende kald i autopilot-paneler mod updateReservationer. Implementér validering i det endpoint som knappen bruger i reservationslisten (lokaliser via `views/valgshop-gaver_view.php`).

### Database
- `present_reservation` (eksisterende, hvor reservationerne gemmes)
- `shop_metadata` (eksisterende, benyttes til at aflæse sælgerkode; udvides med en godkendelsesflag for lagerantal)
- `navision_item` (eksisterende, bruges til at afgøre om en vare er ekstern via feltet `is_external` og korrekt sprog/deleted-filtering)
- `present_reservation_qty_approval` (NY, opsamler anmodninger der kræver godkendelse)
- `mail_queue` (eksisterende, kø til udsendelse – via model/MailQueue)
- Supplerende NAV-lagerkilde (genbrug den samme kilde som i kolonnen "Tilgængelige gaver" – fx via samme service/metode eller evt. tabeller som `navision_reservation_log`/`navision_reservation_done_item`/`present_reservation_forecast.stock_available` alt efter hvad UI'et allerede bruger)

## Opgave 1 – Fjern tre kolonner

Fjern nedenstående kolonner fra reservationslisten under Valgsshops → Gaver i Shoppen → Reservationer:

- "Registreres i NAV" (maps typisk til `present_reservation.skip_navision`)
- "Lager overvåges" (maps typisk til `present_reservation.ship_monitoring`)
- "Autopilot" (maps typisk til `present_reservation.autotopilot`)

### Gennemfør:

1. Opdater table header (`<th>`) og rækkeceller (`<td>`) i `views/valgshop-gaver_view.php` (og evt. indlejrede partials) så de tre kolonner ikke længere renderes
2. Hvis der er JS der læser/formatterer disse kolonner (DataTables, eksport, inline toggles), fjern/tilpas disse hooks. Eksempelvis event-handlers i `views/js/main.js` eller lokale scripts i `views/valgshop-gaver_view.php`
3. Sørg for at evt. POST payload ved opdatering ikke forventer disse felter (eller ignorer dem i backend, hvis de sendes)

## Opgave 2 – Godkendelsesflow når reservation ↓ NAV-lager < 0 (kun eksterne varer)

### 2.A – Servervalidering før gem

Når brugeren har udfyldt "Reservation Ændring" på én/flere linjer og trykker "Opdaterer reservation ændringer" skal backend:

1. Slå eksisterende reserveret antal op for hver linje (`present_reservation.quantity`)
2. Udregne nye ønskede antal ift. den måde UI i dag fortolker inputtet (samme logik som nu – absolut eller delta)
3. Slå aktuelt NAV-lager op for hver vare som vises i kolonnen "Tilgængelige gaver" (genbrug samme kildefunktion/SQL som driver den kolonne, så tallet matcher UI)
4. For hver linje afgør: vil ændringen give negativt lager? Dvs. `(nav_available - forbrug_af_ændring) < 0`
5. Identificér om varen er ekstern: join til `navision_item` med korrekt sprog og filtrér `deleted=0`. Felter: `navision_item.is_external = 1`, `language = shopens sprog` (jf. `shop.reservation_language` eller `shop_metadata/reservation_language`)
6. Hvis mindst én linje (ekstern vare) vil give negativt lager: Afbryd normal opdatering og igangsæt godkendelsesflow (pkt. 2.B), returnér status til UI (så der vises besked om at ændringerne er sendt til godkendelse og ikke er gemt)
7. Hvis ingen linjer giver negativt lager: Fortsæt eksisterende gemmelogik uændret

#### Implementering:

- Udvid den backend-handler som knappen i `views/valgshop-gaver_view.php` kalder (bulk update). Tjek JS for den præcise route (tidligere paneler bruger eksempelvis updateReservationer). Logikken skal ligge på serversiden og køre i en transaktion
- Vær opmærksom på sprog og slettede rækker: `navision_item.language` skal matche den aktuelle shop (1 = dk, 4 = no, 5 = se), og `deleted = 0`

### 2.B – Opret godkendelsesanmodning + mail ved negativ lager (eksterne varer)

Ved blokering (mindst én ekstern linje med negativt lager):

1. Opret én eller flere rækker i ny tabel `present_reservation_qty_approval` (én række per linje der udløser blokering). Gruppér dem med en `group_token` så hele batch kan identificeres samlet
2. Tilføj en post i mail-køen (`mail_queue`) til kss@gavefabrikken.dk med et sammendrag af shop, sælger, varenumre, nuværende NAV-lager og ønsket reserveret antal
3. Returnér et svar til klienten med `status = requires_approval` og evt. en liste over linjer som udløste blokeringen

### 2.C – Vis ny status i den øverste shop-tabel

I øverste status-tabel (samme sektion hvor teksten "Alle varenumre er reserveret" vises), tilføj en ny række:

- **Venstre kolonne:** "Lagerantal er godkendte"
- **Højre kolonne:** "Ikke godkendt" eller "Godkendt"

#### Datakilde for status:

- Tilføj et nyt flag i `shop_metadata`: `stock_qty_approved TINYINT(1) NOT NULL DEFAULT 0`
- UI viser "Ikke godkendt" når `stock_qty_approved = 0` og "Godkendt" når `=1`
- Flagget sættes til 0 når vi opretter en ny godkendelsesanmodning (pkt. 2.B). Et eksternt system kan efterfølgende sætte det til 1, som ønsket

#### Placering i kode:

shop-indstillinger/topboks: `views/shop_view.php` eller `views/shop_view2.php` → inkluderet `shopSettings_view.php`. Tilføj/renderer den nye række dér.

## Databaseændringer

### 1) Ny tabel: present_reservation_qty_approval

```sql
CREATE TABLE present_reservation_qty_approval (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  group_token CHAR(36) NOT NULL,
  shop_id INT NOT NULL,
  salesperson_code VARCHAR(10) DEFAULT NULL,
  language_id INT DEFAULT NULL,
  itemno VARCHAR(50) NOT NULL,
  nav_stock INT NOT NULL DEFAULT 0,
  requested_qty INT NOT NULL DEFAULT 0,
  is_external TINYINT(1) NOT NULL DEFAULT 0,
  approved TINYINT(1) NOT NULL DEFAULT 0,
  approved_at DATETIME DEFAULT NULL,
  email_sent TINYINT(1) NOT NULL DEFAULT 0,
  comment TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_group_token (group_token),
  INDEX idx_shop_created (shop_id, created_at),
  INDEX idx_item (itemno)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_danish_ci;
```

### 2) Udvidelse af shop_metadata (bruges til UI-status-rækken):

```sql
ALTER TABLE shop_metadata
  ADD COLUMN stock_qty_approved TINYINT(1) NOT NULL DEFAULT 0 AFTER salesperson_code;
```

**Bemærk:** `shop_metadata` eksisterer allerede (bruges bl.a. til `salesperson_code`). Kolonnens placering kan tilpasses hvis nødvendigt.

## Backendløsning (ændringer)

Find JS submit-handler i `views/valgshop-gaver_view.php` for knappen "Opdaterer reservation ændringer" og identificér endpoint'et (POST). Udvid den PHP-handler til at:

1. Parse alle ændringer og validér mod NAV-lager (brug samme kilde som UI-kolonnen "Tilgængelige gaver" anvender)
2. Tjek `navision_item.is_external = 1` (korrekt sprog; `deleted = 0`) for hver varelinje
3. Hvis én eller flere linjer (eksterne varer) vil gøre lager negativt:
   - Opret `group_token` (UUID) og indsæt én række i `present_reservation_qty_approval` per ramt varelinje med: `shop_id`, `salesperson_code` (fra `shop_metadata`), `language_id` (fra shop eller `shop_metadata`), `itemno`, `nav_stock`, `requested_qty`, `is_external = 1`, `email_sent = 1/0`, `approved = 0`
   - Queue en mail i `mail_queue` via `model/MailQueue` (se eksempel i `bizlogic/packingservice/controller.php`) til kss@gavefabrikken.dk med emnet "Reservation kræver godkendelse – Shop #{shop_id}" og et body-resumé (HTML-tabel) over de påvirkede linjer
   - Opdatér `shop_metadata.stock_qty_approved = 0`
   - Returnér JSON: `{ status: 'requires_approval', group_token, lines: [...] }`. Afbryd uden at gemme ændringerne i `present_reservation`
4. Hvis ingen linjer blokerer: gennemfør eksisterende gemmelogik (opdater `present_reservation` m.v.) og returnér success

### E-mails: Brug model/MailQueue

Se `model/mailqueue.class.php` og eksempel på indsættelse i `bizlogic/packingservice/controller.php` (`new MailQueue() ... ->save()`).

**Hardcodet modtager:** kss@gavefabrikken.dk

## Frontend-ændringer (UI)

### Reservationsliste (views/valgshop-gaver_view.php):
- Fjern kolonner: "Registreres i NAV", "Lager overvåges", "Autopilot"
- Sørg for at "Tilgængelige gaver" fortsat viser NAV-lageret uændret
- Ved svar `requires_approval` fra backend: vis en modal/toast med tekst á la "Ændringerne er sendt til godkendelse. Der foretages ingen ændringer før godkendelse."

### Top status-tabel (shopSettings_view.php – inkluderet fra shop_view[2].php):
Tilføj en ny række under "Alle varenumre er reserveret":
- **Label:** "Lagerantal er godkendte"
- **Værdi:** "Ikke godkendt" hvis `shop_metadata.stock_qty_approved = 0`, ellers "Godkendt"

## Forretning/regler og kanttilfælde

- Godkendelsesflow gælder kun for eksterne varer (`navision_item.is_external = 1`). Ikke-eksterne varer må gerne gå negativt ifølge kravspec (ingen blokering)
- **Sprogfilter:** Brug shop'ens sprog (`shop.reservation_language` eller relevant felt). Sprog-id: 1 = dk, 4 = no, 5 = se
- Filtrér bort slettede varenumre (`navision_item.deleted = 0`)
- **Batch-håndtering:** Hvis flere linjer i samme gem-mehandling udløser blokering, opret én mail for hele batch med oversigt (brug `group_token`), og én approval-række per linje

## Test og accept

Opret testshop med kendte eksterne varenumre og defineret NAV-lager.

### UI:
- De tre nævnte kolonner er fjernet fra reservationslisten
- Top-tabel viser "Lagerantal er godkendte: Ikke godkendt" som default (ny kolonne synlig)

### Flow uden blokering:
Forøg reservationer under NAV-lager – ændringer gemmes, ingen mail, ingen approval-rækker

### Flow med blokering (ekstern vare):
Forøg så der ville blive negativt lager – backend svarer `requires_approval`, ingen ændringer gemmes i `present_reservation`, én eller flere rækker oprettes i `present_reservation_qty_approval`, `mail_queue` får en ny mail til kss@gavefabrikken.dk, `shop_metadata.stock_qty_approved` forbliver/sættes til 0

### Ekstern godkendelse:
Eksternt system sætter `shop_metadata.stock_qty_approved = 1` → UI viser "Godkendt"

## Tidsestimat (overslag)

- **Kolonneoprydning + UI-tilpasninger:** 2–4 timer
- **Backend-validering, ny tabel, mail-kø integration og statusfelt:** 8–12 timer
- **Test og finpudsning:** 3–5 timer

## Referencer i kodebasen

### Views
- `views/valgshop-gaver_view.php` (reservationslisten; indeholder UI-script hooks såsom ShowReservationdataMedal osv.)
- `views/shop_view.php`, `views/shop_view2.php` (indeholder tabs og inkluderer `shopSettings_view.php`)

### Database (uddrag)
- `present_reservation` (kolonner bl.a. `quantity`, `ship_monitoring`, `autotopilot`, `skip_navision`)
- `shop_metadata` (kolonner bl.a. `salesperson_code` – udvides med `stock_qty_approved`)
- `navision_item` (bruges til `is_external` + sprog/deleted – filtrér korrekt)
- `mail_queue` (queuing – se `model/mailqueue.class.php`)

### Mail-kø
- `model/mailqueue.class.php` (sending job og ActiveRecord model)
- Eksempel på enqueuing: `bizlogic/packingservice/controller.php` (`new MailQueue() ... ->save()`)

Giv gerne besked hvis vi også skal implementere en simpel visning/rapport af pending godkendelser til Susanne (fx filter på `present_reservation_qty_approval`), ellers er database + mail-notifikation nok til første iteration.

---

Vh.  
Udviklingsteamet