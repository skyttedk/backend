# Summary - GF2025-81: Felt-definitioner s�gbare flags + QR-scanner tilpasninger

## Implementerede �ndringer

" **Database model opdateret**: Tilf�jet to nye boolean felter til `ShopAttribute` modellen:
  - `is_searchable` (S�gbar)
  - `is_visible_on_search` (Vis ved s�gning)

" **Admin interface forbedret**: Udvidet felt-definitioner formularen i shop administrationen:
  - To nye checkboxes placeret efter "Vis" checkbox
  - JavaScript validering og h�ndtering tilf�jet

" **QR-scanner funktionalitet opdateret**:
  - Filtrering af s�gbare felter baseret p� `is_searchable` flag
  - Begr�nsning af visning til kun felter med `is_visible_on_search = true`
  - Opdateret `getOrder` endpoint til at respektere nye flags

## P�virkede filer

" `model/shopattribute.class.php` - Tilf�jet nye model felter
" `views/shop_view.php` - Udvidet admin formular med nye checkboxes
" `views/js/feltDeff.js` - JavaScript h�ndtering af nye felter
" `app/qrscanner/index/controller.php` - QR-scanner logik opdateret

## Funktionalitet

" Administratorer kan nu kontrollere hvilke felter der er s�gbare i QR-scanneren
" Administratorer kan styre hvilke felter der vises ved gaveudlevering
" QR-scanneren respekterer de nye indstillinger for b�de s�gning og visning
" Ingen breaking changes - alle eksisterende felter beholder deres funktion