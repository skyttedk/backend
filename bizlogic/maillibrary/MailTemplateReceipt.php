<?php

namespace GFBiz\MailLibrary;


class MailTemplateReceipt extends MailTemplateBase {

    public function __construct($handle,$language_id,$shop_id=0,$company_id=0)
    {
        parent::__construct();
    }

    




    protected function replaceData($replaceParam) {
        $replacementData = [
            'username' => 'John Doe',
            'email' => 'john.doe@example.com',
        ];

        return isset($replacementData[$replaceParam]) ? $replacementData[$replaceParam] : '';
    }
}