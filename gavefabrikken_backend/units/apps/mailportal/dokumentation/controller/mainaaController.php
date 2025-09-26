<?php

Class mainaaController Extends baseController {

public function index() {
    
               
    if(router::$systemUser == null) {
        header("Location: index.php?rt=login");
        return;
    }            
    
    $this->registry->template->show('main_view');
}

}

?>
