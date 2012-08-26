<?php defined('SYSPATH') or die('No direct script access.');

class ORM extends Kohana_ORM
{
	protected static $_factory_return_values = array();

	/**
	 * Set what will be returned when factory is called with the specific params.
	 *
	 * @param ORM     $output
	 * @param string  $model
	 * @param mixed   $id
	 */
	public static function set_factory_output($output, $model, $id = NULL)
	{
		self::$_factory_return_values[$model.$id] = $output;
	}

	/**
	 * Reset any values set with set_factory_output.
	 */
	public static function reset_factory_output()
	{
		self::$_factory_return_values = array();
	}

	/**
	 * If a matching value set with set_factory_output is found, return it, otherwise call parent.
	 * @param  string  $model
	 * @param  mixed   $id
	 * @return ORM
	 */
	public static function factory($model, $id = NULL)
	{
		$preset_output = Arr::get(self::$_factory_return_values, $model.$id);
		if ($preset_output)
			return $preset_output;

		return parent::factory($model, $id);
	}
}