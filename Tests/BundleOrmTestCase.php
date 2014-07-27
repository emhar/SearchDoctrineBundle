<?php

namespace Emhar\SearchDoctrineBundle\Tests;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\EventManager;
use Doctrine\ORM\Configuration;
use Doctrine\Common\Cache\ArrayCache;
use DoctrineExtensions\PHPUnit\OrmTestCase;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\Common\Annotations\AnnotationReader;
use PHPUnit_Extensions_Database_DataSet_CsvDataSet;

/**
 * Parent class for unit test using doctrine
 *
 * @author Emhar
 */
class BundleOrmTestCase extends OrmTestCase
{

	/**
	 * @return \Doctrine\ORM\EntityManager
	 */
	protected function createEntityManager()
	{
		// event manager used to create schema before tests
		$eventManager = new EventManager();
		$eventManager->addEventListener(array('preTestSetUp'), new SchemaSetupListener());

		$driver = new AnnotationDriver(new AnnotationReader(), array(
			__DIR__ . '/Models/Entity/'
		));
		// create config object
		$config = new Configuration();
		$config->setMetadataCacheImpl(new ArrayCache());
		$config->setMetadataDriverImpl($driver);
		$config->setProxyDir(__DIR__ . '/TestProxies');
		$config->setProxyNamespace('Emhar\SearchDoctrineBundle\Tests\TestProxies');
		$config->setAutoGenerateProxyClasses(true);
		//$config->setSQLLogger(new \Doctrine\DBAL\Logging\EchoSQLLogger());
		// create entity manager
		$em = EntityManager::create(
						array(
					'driver' => 'pdo_sqlite',
					'path' => sys_get_temp_dir() . '/sqlite-test-searchdoctrinebundle.db'
						), $config, $eventManager
		);

		return $em;
	}

	protected function getDataSet()
	{
		$dataSet = new PHPUnit_Extensions_Database_DataSet_CsvDataSet();
		return $dataSet;
	}
}
