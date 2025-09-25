<?php Ramsey\Uuid\Exception\

/***********************************
 *
 *
 * Reservationer
 *
 * Reservationer er tricky for der er en del måder det skal køre på, specielt på cardshops
 *
 * Generelt så startes reservationer ved at sætte reservation_state = 1 på shop
 * Den bruger så reservation_code og reservation_language til at køre reservationer over på
 *
 * Er det en norsk shop skal reservation_language sættes til 4 og reservation_code til den norske lokation, men reservation_foreign_language skal sættes til 1 og reservation_foreign_code til HEDEHUSENE
 * Det er da de norske shop ofte har varer i både norge og danmark
 * 
 * Når reservationen er færdig så skal reservation_state sættes til 0
 * 
 * Cardshops
 * Cardshops er specielle fordi de frigiver reservationer løbende.
 * Når en deadline faktureres skal reservationer frigives for de fakturerede ordre
 * Det gøres i dette værktøj: https://system.gavefabrikken.dk//gavefabrikken_backend/index.php?rt=unit/navision/syncreservations/countercs
 * Her vælges en shop og expire date og der køres frigivelser på alle ordre på den
 * 
 * Derudover, privatleverings deadlines køre løbende frigivelser på de leverede varer.
 * 
 * Ofte bliver der sendt varer til Norge som også skal trækkes fra, det er enten med:
 * https://system.gavefabrikken.dk//gavefabrikken_backend/index.php?rt=unit/navision/syncreservations/countersobatch
 * eller
 * https://system.gavefabrikken.dk//gavefabrikken_backend/index.php?rt=unit/navision/syncreservations/counterso
 * 
 * Tjek dem før de køres.
 * Når der køres reservationer tilbage på denne måde gemmes de i navision_reservation_done
 * De trækkes fra ved at køre dette script:
 * 
 * https://system.gavefabrikken.dk//gavefabrikken_backend/index.php?rt=unit/navision/syncreservations/counterdone
 *
 *
 */