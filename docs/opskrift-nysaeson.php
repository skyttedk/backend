<?php /*


NOTER:
slet ikke norske childs
tjek for nye tabeller
slet debug/backup tabeller

Opskrift: opstart af ny sæson

Denne opskrift beskriver de opgaver der skal udføres i forbindelse med opstart af en ny sæson.
Opgaver vdr. opsætning af cardshop til en ny sæson er flyttet til opskrift-cardshops.php da vi ikke nødvendigvis gør det på samme tid.


Note vdr. luksusgavekort

Alle åbne luksusgavekort føres med over i ny sæson og slettes i den gamle.
 Dvs company_order, shop_user, user_attributes, order, order_attributes flyttes til ny sæson og slettes fra gammel
 Alle erstatningskort på luksus kopieres over men beholdes også på gammel server
 Der blokkeres for valg på luksus erstatning på gammel server
 Der laves nye erstatningskort på en ny erstatningskort kunde i det nye år


********* Forberedelse - skal være på plads inden sæsonskift ***************

Vi skal vide til valgshops
 - Hvilke må slettes (indsættes i sql herunder), bemærk vi har nogle faste der ikke må slettes, cardshops, landingpages og previewshops

Gennemgå sidste års produktion
 - Slet midlertidige tabeller
 - Tøm alle store tabeller der kan tømmes, uden reel gdpr sletning
 - Se om der er nogle tabeller der skal udgå helt til næste år
 - Tjek om der er plads på sql serveren

Søren skal opdatere navision dokumentationen, se på hvad der er lavet af ændringer siden sidste sæson.
 - Senest udført april 2025


********* Step 1 - Flyt filer - 1-3 dage før ***************

Er normalt Ulrichs opgave
Der tages en kopi af produktionen og den flyttes til en mappe med sidste sæsons årstal
 - Skal gøres på backend
 - Skal gøres på valgserver

Husk kundesiden også

Ryd op i filer i den nye produktionsmappe så alt der kan slettes fra sidste år er slettet

Ryd op i filer i tidligere år så de ikke fylder mere end højest nødvendigt

Ulrich: beskriv gerne nærmere her!!



********** Step 2 - Arkiv - 1-2 dage før ***************

Er normalt Sørens opgave
Opsæt den nye sæsonmappe til den gamle sæson i arkivet

Søg og erstat på faste url'er i backend og ændre dem til den nye sæson mappe

Søg og erstat på farste url'er i valgshop mappen, bemærk den bruger både system.gavefabrikken.dk og system.findgaven.dk

Opdater forsiden på arkivet

Test arkivet


********** Step 3 - Sæson skift ***************

- Synkroniser koden ned fra server så søren har den nyeste kode
- Tag en kopi af den gamle sæson som backup: backup_gavefabrikkenxxxx
- Tag en kopi af den gamle sæson til ny sæson: gavefabrikkenxxxy
- Tilpas includes/config.php til ny sæson (sæson, db og url'er)
- Søg efter det gamle databasenavn i koden og se om der er andre steder det skal skiftes
- Opdater forsiden med arkiv og sæson: gavefabrikken_backend/views/main_view.php

- Lav cronjobs til den sidste sæson
 - navision sync af ordre og privatlevering
 - afsendelse af e-mails



KØR DB ÆNDRINGER PÅ NY DATABSASE:
Søg og erstat navnet i nedenstående så de får de rigtige navne på


Truncate det der kan truncates helt
TRUNCATE gavefabrikken2024.`debug_log`;
TRUNCATE gavefabrikken2024.`gift_certificate`;
TRUNCATE gavefabrikken2024.`navision_call_log`;
TRUNCATE gavefabrikken2024.`mail_event`;
TRUNCATE gavefabrikken2024.`mail_queue`;
TRUNCATE gavefabrikken2024.`navision_choice_doc`;
TRUNCATE gavefabrikken2024.system_log;
TRUNCATE gavefabrikken2024.system_log_pdf_download;
TRUNCATE gavefabrikken2024.system_user_activity;
TRUNCATE gavefabrikken2024.system_user_device;
TRUNCATE gavefabrikken2024.weborderlog;
TRUNCATE gavefabrikken2024.rm_data;
TRUNCATE gavefabrikken2024.rm_job;
TRUNCATE gavefabrikken2024.rm_shop_data;
TRUNCATE gavefabrikken2024.`varenrcache`;


Slet shops der ikke skal bruges
VIGTIGT: 3512,3543,3502,4594 må ikke slettes
DELETE FROM gavefabrikken2024.shop where id not in (3512,3543,3502,4594) and  id in (... id'er på dem der skal slettes i både dk, no og se....);


// Shop id
DELETE FROM gavefabrikken2024.`company_shop` WHERE shop_id > 0 && shop_id NOT IN (select id from gavefabrikken2024.shop);
DELETE FROM gavefabrikken2024.`order` WHERE shop_id > 0 && shop_id NOT IN (select id from gavefabrikken2024.shop);
DELETE FROM gavefabrikken2024.`order_present_entry` WHERE shop_id > 0 && shop_id NOT IN (select id from gavefabrikken2024.shop);
DELETE FROM gavefabrikken2024.`shop_address` WHERE shop_id > 0 && shop_id NOT IN (select id from gavefabrikken2024.shop);
DELETE FROM gavefabrikken2024.`shop_attribute` WHERE shop_id > 0 && shop_id NOT IN (select id from gavefabrikken2024.shop);
DELETE FROM gavefabrikken2024.`shop_description` WHERE shop_id > 0 && shop_id NOT IN (select id from gavefabrikken2024.shop);
DELETE FROM gavefabrikken2024.`shop_present` WHERE shop_id > 0 && shop_id NOT IN (select id from gavefabrikken2024.shop);
DELETE FROM gavefabrikken2024.`shop_board` WHERE fk_shop > 0 && fk_shop NOT IN (select id from gavefabrikken2024.shop);
DELETE FROM gavefabrikken2024.`shop_user` WHERE shop_id > 0 && shop_id NOT IN (select id from gavefabrikken2024.shop);
DELETE FROM gavefabrikken2024.`shop_user_autoselect` WHERE shop_id > 0 && shop_id NOT IN (select id from gavefabrikken2024.shop);
DELETE FROM gavefabrikken2024.`user_attribute` WHERE shop_id > 0 && shop_id NOT IN (select id from gavefabrikken2024.shop);
DELETE FROM gavefabrikken2024.`shop_user_autoselect` WHERE shop_id > 0 && shop_id NOT IN (select id from gavefabrikken2024.shop);
DELETE FROM gavefabrikken2024.`present` WHERE shop_id > 0 && shop_id NOT IN (select id from gavefabrikken2024.shop);
DELETE FROM gavefabrikken2024.cardshop_settings where shop_id not in (select id from gavefabrikken2024.shop);
 DELETE FROM gavefabrikken2024.cardshop_expiredate where shop_id not in (select id from gavefabrikken2024.shop);


// Present
DELETE FROM gavefabrikken2024.present_description where present_id > 0 && present_id not in (select id from gavefabrikken2024.present);
DELETE FROM gavefabrikken2024.present_log where present_id > 0 && present_id not in (select id from gavefabrikken2024.present);
DELETE FROM gavefabrikken2024.present_media where present_id > 0 && present_id not in (select id from gavefabrikken2024.present);
DELETE FROM gavefabrikken2024.present_model where present_id > 0 && present_id not in (select id from gavefabrikken2024.present);
DELETE FROM gavefabrikken2024.present_model_options where present_id > 0 && present_id not in (select id from gavefabrikken2024.present);
DELETE FROM gavefabrikken2024.shop_present_company_rules where present_id not in (select id from gavefabrikken2024.present);

// Company
DELETE FROM `company` where pid > 0;
DELETE FROM gavefabrikken2024.`app_log` WHERE company_id > 0 && company_id NOT IN (select id from gavefabrikken2024.company);
DELETE FROM gavefabrikken2024.`company_notes` WHERE company_id > 0 && company_id NOT IN (select id from gavefabrikken2024.company);
DELETE FROM gavefabrikken2024.`company_order` WHERE company_id > 0 && company_id NOT IN (select id from gavefabrikken2024.company);
DELETE FROM gavefabrikken2024.`company_shipping_cost` WHERE company_id > 0 && company_id NOT IN (select id from gavefabrikken2024.company);
DELETE FROM gavefabrikken2024.`company_shop` WHERE company_id > 0 && company_id NOT IN (select id from gavefabrikken2024.company);
DELETE FROM gavefabrikken2024.`shop_present_company_rules` WHERE company_id > 0 && company_id NOT IN (select id from gavefabrikken2024.company);

 / Company order
DELETE FROM gavefabrikken2024.company_order where shop_id not in (select shop_id from gavefabrikken2024.cardshop_settings where concept_parent LIKE 'LUKS');
DELETE FROM gavefabrikken2024.company_order where order_state NOT IN (1,2,3,4,5,20);
Tjek alle company orders, er der nogle der ikke er slettet endnu som bør være det?

DELETE FROM gavefabrikken2024.blockmessage WHERE company_order_id > 0 && company_order_id not in (select id from gavefabrikken2024.company_order);
DELETE FROM gavefabrikken2024.shipment where companyorder_id > 0 && companyorder_id not in (select id from gavefabrikken2024.company_order);
DELETE FROM gavefabrikken2024.company_order_item where companyorder_id > 0 && companyorder_id not in (select id from gavefabrikken2024.company_order);
DELETE FROM gavefabrikken2024.navision_order_doc where order_no not in (select order_no from gavefabrikken2024.company_order);
DELETE FROM gavefabrikken2024.`shop_user` WHERE company_order_id > 0 && company_order_id NOT IN (select id from gavefabrikken2024.company_order);

// Shipment
DELETE FROM gavefabrikken2024.blockmessage WHERE shipment_id > 0 && shipment_id not in (select id from gavefabrikken2024.shipment);

// SHOP USER ID
DELETE FROM gavefabrikken2024.`app_log` WHERE shopuser_id > 0 && shopuser_id NOT IN (select id from gavefabrikken2024.shop_user);
DELETE FROM gavefabrikken2024.`order` WHERE shopuser_id > 0 && shopuser_id NOT IN (select id from gavefabrikken2024.shop_user);
DELETE FROM gavefabrikken2024.`order_history` WHERE shopuser_id > 0 && shopuser_id NOT IN (select id from gavefabrikken2024.shop_user);
DELETE FROM gavefabrikken2024.`user_attribute` WHERE shopuser_id > 0 && shopuser_id NOT IN (select id from gavefabrikken2024.shop_user);
DELETE FROM gavefabrikken2024.`order_present_complaint` WHERE shopuser_id > 0 && shopuser_id NOT IN (select id from gavefabrikken2024.shop_user);
DELETE FROM gavefabrikken2024.`shop_user_log` WHERE shop_user_id > 0 && shop_user_id NOT IN (select id from gavefabrikken2024.shop_user);

// ORDERID
DELETE FROM gavefabrikken2024.`app_log` WHERE order_id > 0 && order_id NOT IN (select id from gavefabrikken2024.`order`);
DELETE FROM gavefabrikken2024.`order_attribute` WHERE order_id > 0 && order_id NOT IN (select id from gavefabrikken2024.`order`);
DELETE FROM gavefabrikken2024.`order_present_entry` WHERE order_id > 0 && order_id NOT IN (select id from gavefabrikken2024.`order`);

// ORDER HISTORY ID
DELETE FROM gavefabrikken2024.`order_history_attribute` WHERE orderhistory_id > 0 && orderhistory_id NOT IN (select id from gavefabrikken2024.`order_history`);

 // Andre
DELETE FROM accesstoken where Data NOT LIKE '%static%';
DELETE FROM gavefabrikken2024.blockmessage WHERE release_status != 0;

DELETE from actionlog WHERE shop_id > 0 and shop_id not in (select id from shop);
DELETE from actionlog WHERE company_id > 0 and company_id not in (select id from company);
DELETE from actionlog WHERE company_order_id > 0 and company_order_id not in (select id from company_order);
DELETE from actionlog WHERE shop_user_id > 0 and shop_user_id not in (select id from shop_user);

// Sæt ny numbeseries - VIGTIGT SKAL OPDATERES MED DE RIGTIGE TAL
UPDATE gavefabrikken2024.number_series SET current_no = 4000001 WHERE id = 1;
UPDATE gavefabrikken2024.number_series SET prefix = '24', current_no = 10000 where id = 2;
UPDATE gavefabrikken2024.number_series SET prefix = '34', current_no = 10000 where id = 3;
UPDATE gavefabrikken2024.number_series SET current_no = '108000', decimals = 6 where id = 13;
UPDATE gavefabrikken2024.number_series SET current_no = '3600' where id = 20;



KØR DB ÆNDRINGER PÅ GAMMEL DATABASE:

Udtræk de luksusgavekort der ligger i company_order på ny database
Slet dem i den gamle: TAG IKKE REPLACEMENT KORT MED, DE BS NR SKAL BLIVE

DELETE from gavefabrikken2023.company_order where order_no in (... listen over BS numre åben i det nye år...);
DELETE FROM gavefabrikken2023.shop_user WHERE company_order_id in (SELECT id from gavefabrikken2023.company_order where order_no in (... listen over BS numre åben i det nye år...));
DELETE FROM gavefabrikken2023.`order` WHERE shopuser_id in (SELECT id FROM gavefabrikken2023.shop_user WHERE company_order_id in (SELECT id from gavefabrikken2023.company_order where order_no in (... listen over BS numre åben i det nye år...)))
DELETE FROM gavefabrikken2023.user_attribute WHERE shopuser_id in (SELECT id FROM gavefabrikken2023.shop_user WHERE company_order_id in (SELECT id from gavefabrikken2023.company_order where order_no in (... listen over BS numre åben i det nye år...)))
DELETE FROM gavefabrikken2023.order_attribute WHERE shopuser_id in (SELECT id FROM gavefabrikken2023.shop_user WHERE company_order_id in (SELECT id from gavefabrikken2023.company_order where order_no in (... listen over BS numre åben i det nye år...)))
Mangler evt order history


Sæt onhold = 1 på company fra replacementkort i den gamle database så man ikke kan logge ind i det gamle system med erstatningskort, men KUN på luksusgavekort company

Luk ned for bestilling på luksusgavekort og andre i den gamle sæson i cardshopsettings


FORTSÆT OPSÆTNING

- Tjek at ny db er korrekt og der ikke er nogle tabeller der mangler
- Tjek størrelse på db og antal i de forskellige tabeller, se om det ser ok ud

Valgshop
 - Lav ændringer til login så der kan logges på gamle kort, er i login.php filer

Træk fysiske luksusgavekort ud fra sidste år og indlæs dem i næste år
 SELECT * FROM `gift_certificate` WHERE `reservation_group` = 13 && is_email = 0 && shop_id = 0  ORDER BY `gift_certificate`.`shop_id` DESC

Kør på sidste år så den ikke også kan trække fysiske kort:
 UPDATE gift_certificate set blocked = 1 where reservation_group = 13 && shop_id = 0;

Tjek datoer på luksusgavekort i cardshop_settings i ny database, tjek de ikke lukker inden udløb af sæson

Dan nye e-mail kort til luksusgavekortet

Dan nye erstatningskort til luksusgavekort
- Ny kunde til dem
- Ny ordre, 5000 kort til hvert beløb
- Indsæt kunde i cardshopsettings replacement company id
 - Tjek koden, fx privatedelivery har også id'er på replacement companies.


********** Step 4 -Test ***************


Test luksusgavekort bestilling fra cardshop
Test luksusgavekort bestilling fra hjemmeside
 Test dk replacement kort virker på arkiv
 Test dk replacement kort virker på ny
 Test svensk replacement kort virker
Test login med luksusgavekort
 Test login med svensk gavekort fra sidste år
 Test login med svensk erstatningsgavekort fra sidste år

Test kundesiden i arkivet, tjek at den viser en side fra sidste år og kan loade





