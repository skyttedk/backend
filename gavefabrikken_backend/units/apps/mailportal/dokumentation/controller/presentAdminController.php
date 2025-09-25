<?php

Class presentAdminController Extends baseController {

public function index() {
    $this->registry->template->show('presentAdmin_view');
}

public function showNew() {
    $this->registry->template->show('presentAdminNewGift_view');
}



}

?>
