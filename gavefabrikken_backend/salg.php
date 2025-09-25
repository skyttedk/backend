<?php
if($_GET["token"]=="givdenmegagas"){
     header("Location: ".GFConfig::BACKEND_URL."index.php?rt=page/cardShop&token=asdf43sdha4fdasdf34olif&systemuser_id=40 ");
    die();
}else{
  echo "No access";
}


?>


