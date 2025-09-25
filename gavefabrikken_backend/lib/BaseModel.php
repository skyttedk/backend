<?php
// Class BaseModel
// Date created  Wed, 20 Apr 2016 11:22:41 +0200
// Created by Bitworks
class BaseModel extends ActiveRecord\Model
{
        public function attributes()
        {
                $attrs = parent::attributes();
                $modelReflector = new ReflectionClass(get_class($this));
                if(isset($this::$calculated_attributes))    {
                    foreach ($this::$calculated_attributes as $calc)    {
                        $attrs[$calc] = $this->{$calc}();
                    }
                }
                return $attrs;
        }
}
