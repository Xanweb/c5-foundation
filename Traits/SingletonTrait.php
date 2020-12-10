<?php

namespace Xanweb\Foundation\Traits;

trait SingletonTrait
{
    private static $instance;

    /**
     * Gets a singleton instance of this class.
     *
     * @return static
     */
    public static function get(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}