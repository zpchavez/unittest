<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Workaround for the "Cannot truncate a table referenced in a foreign key constraint" error.
 *
 * From: http://stackoverflow.com/questions/10331445/phpunit-and-mysql-truncation-error
 */
class Kohana_Unittest_Database_Operation_Truncate
	extends PHPUnit_Extensions_Database_Operation_Truncate
{
	public function execute(PHPUnit_Extensions_Database_DB_IDatabaseConnection $connection,
		PHPUnit_Extensions_Database_DataSet_IDataSet $dataSet)
	{
		$connection->getConnection()->query("SET foreign_key_checks = 0");
		parent::execute($connection, $dataSet);
		$connection->getConnection()->query("SET foreign_key_checks = 1");
	}
}