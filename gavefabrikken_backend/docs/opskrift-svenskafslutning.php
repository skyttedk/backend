<?php

/*
 *
 * Der har i nogle sæsoner været lidt tvivl om hvordan svensk afslutning skal gennemføres.
 * Der bør gøres følgende, og dette bør blive gennemset af MHS, Søren og Thomas ved lejlighed
 *
 * Bemærk at order_state 10 er afsluttet, altså at der er sendt afslutningsdokument men den er ikke lukket endnu (alle gavevalg ikke sendt)
 * order_state = 12 betyder at alle gavevalg er sendt over
 *
 *
 * 1) Afslutning skal startes som normalt
 * Alle company_order får sat order_state til 9 så der sendes afslutninger over med de valg der er pt.
 * Det skal evt. kun være dem som har gavevalg. Lav evt. et dagligt script der tjekker at ordre skal være x dage gammel og der skal være valg før den afsluttes automatisk
 *
 * 2) Kør choices løbende
 * Giv mette beskes hvis der er fejl så de ikke hober sig op.
 *
 * 3) Når sæsonen er overstået skal der ses på hvilke ordre der ikke er afsluttet
 * a) send en liste over dem der slet ikke er valg på til svensk support så de kan vurdere om nogle skal lukkes
 * b) når der er afklaret så kør en forcechoise.
 *
 * Forcechoice
 * Lav et udtræk på hvilke der ikke er lukket endnu og hvor mange gaver de hver mangler.
 * BS nr og antal der skal force lukkes indsættes i units/navision/choicessync/forcechoice og forcechoise køres.
 * Den vil overføre de manglende gaver til nav og lukke dem med momskode 25.
 *
 *
 * Felter på company_order relateret til afslutning
 *
 * Feltbeskrivelser
force_choice_version
Dette felt findes ikke direkte i den viste kode, men der er referencer til en versionering af valg gennem NavisionChoiceDoc tabellen, hvor et valg gemmes med en version attribut, hvilket sandsynligvis er relateret til dette felt. Feltet ser ud til at tracke versionen af de tvungne valg for en ordre.

force_choice
Dette felt bruges til at indikere antallet af tvungne valg, der er blevet anvendt på en ordre.

Når force_choice = 0, er ordren berettiget til at få tilføjet tvungne valg
Efter succesfuld processering sættes force_choice = $quantity, hvor $quantity er antallet af emner der er blevet tvunget i ordren
Feltet bruges både som en flag (0 = ingen tvungne valg) og som en tæller for antal tvungne emner
choice_exception
Dette felt bruges til at gemme fejlbeskeder, hvis der opstår en exception under forsøg på at uploade valg til Navision:

Når processen er succesfuld, sættes choice_exception = ""
Når der opstår en fejl, gemmes exception-meddelelsen: choice_exception = $e->getMessage()
Feltet fungerer som fejllogning på ordreniveau
nav_wait
Dette felt fungerer som en status-kode, der indikerer forskellige tilstande for Navision-synkroniseringen:

nav_wait = 0: Ordren er klar til processering eller er blevet succesfuldt processeret
nav_wait = 1: Ordren er ikke svensk (språk kriterium ikke opfyldt)
nav_wait = 2: Ordren er ikke en privat leveringsordre
nav_wait = 3: Ordren har ikke den korrekte ordrestatus (ikke brugt i denne kode pga. tvungen synkronisering)
nav_wait = 5: Der opstod en fejl under forsøg på at uploade til Navision
nav_wait = 21: Ordren har allerede tvungne valg
nav_wait = 22: Der er ingen mængde at synkronisere
nav_wait = 23: Mængden er negativ
nav_wait = 24: Kan ikke finde sidste ordreversion
Disse felter udgør tilsammen et statussystem, der styrer og tracker processen med at tvinge specifikke valg til ordrer, der sendes til Navision-systemet.


 *
 */