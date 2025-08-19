<?php

Class myPageController Extends baseController
{

    public function index()
    {
        $this->registry->template->show('my_page_view');

    }
}