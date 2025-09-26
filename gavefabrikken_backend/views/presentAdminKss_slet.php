<?php
echo "<table>";
foreach($presents as $present)
{
    echo "<tr><td><b>".$present->present_no."</b></td><td><b>".$present->name."</b></td></tr>";
    //Descriptions
    foreach($present->descriptions as $description) {
        echo "<tr><td></td><td>".$description->language_id."</td><td>".$description->caption."</td></tr>";
    }
    //Pictures
    foreach($present->present_media as $presentMedia) {
        echo "<tr><td></td><td>".$presentMedia->media->path."</td><td></td></tr>";
    }
}
echo "</table>";

/*
 bemærk at media attributter fås via

    $presentMedia->media->path

    ikke

    $presentMedia->path

    da presentMedia indeholder en relation "media" til media tabellen.


*/
?>