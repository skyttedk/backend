<?php /*

Opskrift: opret nyt koncept

Plan:

Opret en ny valgshop

Er det et nyt koncept?
 - Opret ny reservation group

Er der nye datoer der skal vælges på
 - Opret ny expire dates

Opret cardshop_expiredate der linker shop_id med expire_dates

Cardshop_settings
 - Find linje i cardshop_settings der minder mest om den nye shop og kopier
 - Ret linjen til med priser og datoer samt shop_id

På shop tabellen, gør følgende:
 - Opret ny shop med alias, is_gift_certificate, is_company, demo_username, password, reservation_group, language_id, subscribe_gaveklubben, receipt_link, receipt_recipent, card_value, shop_mode
 - Find demo brugeren i shop_user og sæt username og password til demo1234
 - Opret cardshop_settings, find en shop der minder om og kopier den
 - Sæt shop id, navne på cardshop_settings og rediger datoer og priser
 
Forslag til SQL:
UPDATE shop set 
 alias = '[SHOP ALIAS NAVN]',
 reservation_group = [RESERVATION GROUP], 
 language_id = [LANGUAGE ID],
 receipt_link = 'http://gavekortvalg.dk', 
 receipt_recipent = 'gavekort@gavefabrikken.dk', 
 card_value = [KORT VÆRDI], 
 is_gift_certificate = 1, is_company = 0, demo_username = 'demo1234', password = 'demo1234', subscribe_gaveklubben = 1, shop_mode = 1 WHERE id = [NYT SHOP ID]

Tjek at user attributter er sat korrekt op på shop, tilføj fx telefon nr eller privatlevering
Opret attributter og data om shoppen i GFBiz\Model\Cardshop\ShopMetadata

Lav search/replace på shop_id i koden for at finde steder der skal rettes (indsæt gerne herunder)
Gør det samme hvis der er nye expire_dates

 Giv besked til Gavefabrikken om at navision skal sættes op med nyt shop id i shopopsætningen i nav

===== NOTER STEDER I KODE VI SKAL HUSKE AT RETTE HER ======
 
