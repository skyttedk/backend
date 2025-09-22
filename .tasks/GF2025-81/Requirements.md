1. Under felt definitioner i valgshops, skal der tilføjes 2 nye boolean optioner.

    Søgbar
    Vis ved søgning

  de skal placeres i felt definition efter  "Vis"

Husk at felter skal tilføjes modellen og databasen.

2. qr skanneren til gave udlevering finder under 

https://system.gavefabrikken.dk/gavefabrikken_backend/app/qrscanner/index/login.php

her skal det tilpasses således at hvis et felt definition ikke har markeres "Vis vedd søging" så skal den ikke vises når gaven er fundet frem.

der er også en søgeknap i skanneren, her skal det implementers at man ikke kan så  på en felt definition som har søgbar = false


Relevante kodefiler

Frontend (UI for scanneren): backend/app/qrscanner/index/view.php
– Poster til følgende endpoints:

index.php?rt=app/qrscanner/index/getOrder
index.php?rt=app/qrscanner/index/reg
index.php?rt=app/qrscanner/index/undoReg

QR-bruger administration (kundepanel): backend/views/kundepanel_view.php
– Endpoints: index.php?rt=kundepanel/qrNewUser, index.php?rt=kundepanel/qrReadUser