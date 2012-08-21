<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Allow a mock to be returned by the instance() method.
 */
abstract class Auth extends Kohana_Auth
{
    protected static $_mock_instance;

    public static function set_instance_returned($mock)
    {
        self::$_mock_instance = $mock;
    }

    public static function reset_instance_returned()
    {
        self::$_mock_instance = NULL;
    }

    public static function instance()
    {
        if (self::$_mock_instance)
        {
            return self::$_mock_instance;
        }
        return parent::instance();
    }
}