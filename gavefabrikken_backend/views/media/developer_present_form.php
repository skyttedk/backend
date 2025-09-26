<b>Create Present</b>


<form action="index.php?rt=developer/test" method="GET">
 <fieldset>

 <legend>Oplysninger</legend>

    <?php
     createInput('name','Navn','',300);
         ?>


   </fieldset>



 <input type="submit" Value="Gem"/>

</form>


<?php


  function createInput($name,$caption,$value,$width,$type="text") {
    echo '<div style="width:450px;float:left;padding:2px">';


    echo '<div style="width:150px;float:left">';
    echo '<label>'.$caption.'</label>';
    echo '</div>';

    echo '<div style="float:left;width:'.$width.'px">';
    echo '<input value="'.$value.'" style="width:'.$width.'px" type="'.$type.'"/>';
    echo '</div>';

    echo '</div>';
     }
?>

