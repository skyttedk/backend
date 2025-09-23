## GF2025-81: Felt-definitioner - Søgbar og Vis ved søgning felter

### Implementerede ændringer:

• **Database**: Tilføjet to nye boolean kolonner til `shop_attribute` tabellen:
  - `is_searchable` (Søgbar) - placeret efter `is_visible`
  - `is_visible_on_search` (Vis ved søgning) - placeret efter `is_searchable`

• **Model**: Opdateret `ShopAttribute` model til at håndtere de nye felter

• **Backend controller**: Udvidet shop controller til at gemme og hente de nye feltværdier

• **Frontend - Shop view**: Tilføjet header labels for "Søgbar" og "Vis ved søgning" i `views/shop_view.php` (linjer 550-551)

• **JavaScript**:
  - Opdateret `feltDeff.js` til at håndtere de nye felter i `loaditem()` og `saveItem()` funktionerne
  - **Fixet kritisk fejl**: `addNew()` funktionen manglede to checkboxes - tilføjet for at matche de 9 felter i systemet

• **QR-scanner**: Implementeret filtreringslogik så:
  - Kun felter med `is_searchable = 1` kan søges på
  - Kun felter med `is_visible_on_search = 1` vises i søgeresultater

### Status:
✅ Komplet implementering af felt-definitioner med de nye boolean optioner placeret korrekt efter "Vis" feltet som specificeret.