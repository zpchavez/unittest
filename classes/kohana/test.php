<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Class that allows you to prepend paths to the cascading file system.
 */
class Kohana_Test extends Kohana
{
    protected static $_original_paths;

    /**
     * Prepend a path to the paths used by find_file.
     *
     * The new path will take precedence over the existing paths.
     *
     * @param string $path
     */
    public static function prepend_path($path)
    {
        if (self::$_original_paths === null)
        {
            self::$_original_paths = self::$_paths;
        }

        array_unshift(self::$_paths, $path);
    }

    /**
     * Reset paths to their original value (before prepending).
     */
    public static function reset_paths()
    {
        if (self::$_original_paths)
        {
            self::$_paths = self::$_original_paths;
        }
    }
}