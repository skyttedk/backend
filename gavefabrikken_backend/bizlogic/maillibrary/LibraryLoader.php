<?php

namespace GFBiz\MailLibrary;


class LibraryLoader
{

    public static function getMailTemplateObject($handle,$language_id,$shop_id=0,$company_id=0) {

        // Find mails linked to shop or company
        $sql = "SELECT * FROM mail_library WHERE handle LIKE '".$handle."' && language_id = ".intval($language_id)." && (shop_id = 0 or shop_id = ".intval($shop_id).") && (company_id = 0 or company_id = ".intval($company_id).") && deleted IS NULL && active = 1 ORDER BY company_id DESC, shop_id DESC LIMIT 1";
        $mailList = \MailLibrary::find_by_sql($sql);
        
        // Return mail
        if($mailList == null || count($mailList) == 0) {
            return null;
        }
        else {
            return $mailList[0];
        }
        
    }

    
    
}