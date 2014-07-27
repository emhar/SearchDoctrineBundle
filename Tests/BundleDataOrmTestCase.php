<?php

namespace Emhar\SearchDoctrineBundle\Tests;

use PHPUnit_Extensions_Database_DataSet_CsvDataSet;

/**
 * Parent class for unit test using doctrine
 *
 * @author Emhar
 */
class BundleDataOrmTestCase extends BundleOrmTestCase
{
	protected function getDataSet()
	{
		$dataSet = new PHPUnit_Extensions_Database_DataSet_CsvDataSet();
		$dataSet->addTable('entity1', __DIR__ . '/_doctrine/dataset/entity1Fixture.csv');
		$dataSet->addTable('entity2', __DIR__ . '/_doctrine/dataset/entity2Fixture.csv');
		return $dataSet;
	}
}
