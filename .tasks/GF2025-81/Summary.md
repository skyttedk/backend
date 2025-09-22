# Summary - GF2025-81: Felt-definitioner søgbare flags + QR-scanner tilpasninger

## Implementerede ændringer

" **Database model opdateret**: Tilføjet to nye boolean felter til `ShopAttribute` modellen:
  - `is_searchable` (Søgbar)
  - `is_visible_on_search` (Vis ved søgning)

" **Admin interface forbedret**: Udvidet felt-definitioner formularen i shop administrationen:
  - To nye checkboxes placeret efter "Vis" checkbox
  - JavaScript validering og håndtering tilføjet

" **QR-scanner funktionalitet opdateret**:
  - Filtrering af søgbare felter baseret på `is_searchable` flag
  - Begrænsning af visning til kun felter med `is_visible_on_search = true`
  - Opdateret `getOrder` endpoint til at respektere nye flags

## Påvirkede filer

" `model/shopattribute.class.php` - Tilføjet nye model felter
" `views/shop_view.php` - Udvidet admin formular med nye checkboxes
" `views/js/feltDeff.js` - JavaScript håndtering af nye felter
" `app/qrscanner/index/controller.php` - QR-scanner logik opdateret

## Funktionalitet

" Administratorer kan nu kontrollere hvilke felter der er søgbare i QR-scanneren
" Administratorer kan styre hvilke felter der vises ved gaveudlevering
" QR-scanneren respekterer de nye indstillinger for både søgning og visning
" Ingen breaking changes - alle eksisterende felter beholder deres funktion