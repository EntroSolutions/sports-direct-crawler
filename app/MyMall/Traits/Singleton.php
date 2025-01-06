<?php

namespace App\MyMall\Traits;

trait Singleton
{
    protected static $instance = null;

    public static function instance( array $arg = []) : self
    {
        if ( ! self::$instance) {
            self::$instance = new self($arg);
        }

        return self::$instance;
    }
}
