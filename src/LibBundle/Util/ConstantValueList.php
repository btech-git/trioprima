<?php

namespace LibBundle\Util;

class ConstantValueList
{
    public static function get($className, $constantPrefix = null, $isKeyName = false)
    {
        $prefix = empty($constantPrefix) ? '' : $constantPrefix;

        $class = new \ReflectionClass($className);
        $classConstants = $class->getConstants();
        $constantPrefixLength = strlen($prefix);

        $list = array();
        foreach ($classConstants as $key => $value) {
            if (substr($key, 0, $constantPrefixLength) === $prefix) {
                $name = $isKeyName ? substr($key, $constantPrefixLength) : $value;
                $list[ucfirst(strtolower($name))] = $value;
            }
        }

        return $list;
    }
}
