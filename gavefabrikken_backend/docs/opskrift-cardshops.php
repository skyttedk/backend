<?php /*
 
 
 ********* Forberedelse ***************

 Vi skal vide til cardshops (kan evt. vente)
- Hvilke ugenr
- Hvilke koncepter
- Prisændringer
- Antal kort der skal printes pr. uge

 
  ********* Inden salg starter ***************
  
- Gennemgå opsætning og priser med en ansvarlig ved gavefabrikken
 
 
Skal opsættes
- expire_date - Opsæt de datoer der skal bruges næste år - ok
- opdater numberseries - tilføj nye nrserier- ok
- tjek reservation groups - ok

- opret nye shops, se opskrift for oprettelse af shop
- cardshop_settings opdateres på alle shops med datoer og priser
- cardshop_expiredate tjekkes

Brug dette værktøj til at tjekke: https://system.gavefabrikken.dk/gavefabrikken_backend/index.php?rt=unit/development/tools/viewexpiredates

Opret nye shops der mangler
Ret login scripts til
Ret shop expire date
Ret cardshop_settings datoer til

Træk nye gift_certificates ud på print kort når vi har info om dem
 
 Lav erstatningsgavekort til dem der skal have (nye ordre med e-mail kort)
 




******************** FLYT SHOPS GAVER *******************

Der opstættes tit nye shops med gaver der skal benyttes til de nye shops
Når gaverne skal flyttes ind i de rigtige shops, så gør følgende:

1) Slet gaver der ligger på nuværende valgshop (der hvor de nye skal flyttes ind)

DELETE FROM order_history where present_id in (select id from present where shop_id = SHOPID);
DELETE FROM order_present_entry where present_id in (select id from present where shop_id = SHOPID);
DELETE FROM presentation_sale_pdf where present_id in (select id from present where shop_id = SHOPID);
DELETE FROM presentation_sale_present where present_id in (select id from present where shop_id = SHOPID);
DELETE FROM present_description where present_id in (select id from present where shop_id = SHOPID);
DELETE FROM present_log where present_id in (select id from present where shop_id = SHOPID);
DELETE FROM present_media where present_id in (select id from present where shop_id = SHOPID);
DELETE FROM present_model where present_id in (select id from present where shop_id = SHOPID);
DELETE FROM present_model_options where present_id in (select id from present where shop_id = SHOPID);
DELETE FROM present_reservation where present_id in (select id from present where shop_id = SHOPID);
DELETE FROM present_reservation_log where present_id in (select id from present where shop_id = SHOPID);
DELETE FROM shop_loan where present_id in (select id from present where shop_id = SHOPID);
DELETE FROM shop_present where present_id in (select id from present where shop_id = SHOPID);
DELETE FROM shop_present_company_rules where present_id in (select id from present where shop_id = SHOPID);
DELETE FROM shop_present_rules where present_id in (select id from present where shop_id = SHOPID);
DELETE FROM shop_user_autoselect where present_id in (select id from present where shop_id = SHOPID);
DELETE FROM paper_order where present_id in (select id from present where shop_id = SHOPID);
DELETE from present where shop_id = SHOPID


DELETE FROM `navision_reservation_done` where shop_id = SHOPID;
DELETE FROM `navision_reservation_done_item` where shop_id = SHOPID;
DELETE FROM `navision_reservation_log` where shop_id = SHOPID;
 
2) Ændre shop_id på gaver der skal flyttes
UPDATE present SET shop_id = NEWSHOPID WHERE shop_id = OLDSHOPID;
UPDATE present_reservation SET shop_id = NEWSHOPID WHERE shop_id = OLDSHOPID;
UPDATE shop_present SET shop_id = NEWSHOPID WHERE shop_id = OLDSHOPID;






 ****************** AFSLUTNING AF SHOPS **********************
  *
  *
  *
  * SELECT * FROM `company_order` where expire_date = '2023-10-29' && shop_id in (select shop_id from cardshop_settings where language_code = 1) && order_state = 5;

 TJEK EVT OM DER ER NOGEN STATUS ANDET END 5 og 8 for at tjekke hvad der ligger
SELECT * FROM `company_order` where expire_date = '2023-10-29' && shop_id in (select shop_id from cardshop_settings where language_code = 1) && order_state not in (5,8);


UPDATE `company_order` set order_state = 9, nav_synced = 0 where expire_date = '2023-11-12' && shop_id in (select shop_id from cardshop_settings where language_code = 1) && order_state = 5;

  *
  * Når der er faktureret så husk at køre reservationer:
  * https://system.gavefabrikken.dk//gavefabrikken_backend/index.php?rt=unit/navision/syncreservations/countercs



 NORGE - HENT DEM DER ER HOLDT TILBAGE AF MANUEL FRAGT BEREGNING
SELECT * FROM `company_order` where expire_date = '2023-11-05' && shop_id in (select shop_id from cardshop_settings where language_code = 4) && order_state = 5 && company_id in (select id from company where `manual_freight` = 1);

SELECT * FROM `company_order` where expire_date = '2023-11-05' && shop_id in (select shop_id from cardshop_settings where language_code = 4) && order_state = 5 && company_id in (select id from company where `manual_freight` = 1);


SELECT id, order_no, company_name, cvr, shop_name, salesperson, quantity, expire_date, ship_to_company FROM `company_order` where expire_date = '2023-11-05' && shop_id in (select shop_id from cardshop_settings where language_code = 4) && order_state = 5 && company_id in (select id from company where `manual_freight` = 1);


 FRIGIV ORDRE TIL NORGE:

VIS
SELECT * FROM `company_order` where expire_date = '2023-11-05' && shop_id in (select shop_id from cardshop_settings where language_code = 4) && order_state = 5 && company_id in (select id from company where `manual_freight` = 0);

UPDATE `company_order` SET order_state = 9, nav_synced = 0 where expire_date = '2023-11-05' && shop_id in (select shop_id from cardshop_settings where language_code = 4) && order_state = 5 && company_id in (select id from company where `manual_freight` = 0);

HUSK AT AFSLUTTE RESERVATIONER MEN IKKE I NORGE!!1





 
 */