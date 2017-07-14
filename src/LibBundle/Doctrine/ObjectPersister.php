<?php

namespace LibBundle\Doctrine;

class ObjectPersister
{
    private static $autoFlush = true;
    private static $objectManagers = null;

    public static function isAutoFlush()
    {
        return self::$autoFlush;
    }

    public static function addObjectManager($objectManager)
    {
        if (is_array(self::$objectManagers)) {
            self::$objectManagers[] = $objectManager;
        }
    }

    public static function reset()
    {
        self::$autoFlush = true;
        self::$objectManagers = null;
    }

    public static function save($func)
    {
        if (!is_callable($func)) {
            throw new \InvalidArgumentException('Expected argument of type "callable", got "' . gettype($func) . '"');
        }

        self::$autoFlush = false;
        self::$objectManagers = array();

        try {
            $return = call_user_func($func);
            if (!empty(self::$objectManagers) && count(array_unique(self::$objectManagers, SORT_REGULAR)) === 1) {
                self::$objectManagers[0]->flush();
            }
            self::$autoFlush = true;
            self::$objectManagers = null;

            return $return ?: true;
        } catch (Exception $e) {
            self::$autoFlush = true;
            self::$objectManagers = null;

            throw $e;
        }
    }
}
