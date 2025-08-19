<?php

namespace GFBiz\Model\Config;

class LanguageLogic
{

    public static function validLanguage($langid)
    {
        try {
            $language = \Language::find($langid);
            if ($language == null) return false;
            return true;
        } catch(\Exception $e) {
            return false;
        }
    }

    public static function getLanguage($langid) {
        try {
            $language = \Language::find($langid);
            return $language;
        } catch (\Exception $e) {
            return null;
        }
    }

}