<?php

namespace Emhar\SearchDoctrineBundle\Tests;

use DoctrineExtensions\PHPUnit\Event\EntityManagerEventArgs;
use Doctrine\ORM\Tools\SchemaTool;

/**
 * SchemaSetupListener init schema.
 *
 * @author Emhar
 */
class SchemaSetupListener
{

	public function preTestSetUp(EntityManagerEventArgs $eventArgs)
	{
		$em = $eventArgs->getEntityManager();

		$schemaTool = new SchemaTool($em);

		$cmf = $em->getMetadataFactory();
		$classes = $cmf->getAllMetadata();

		$schemaTool->dropDatabase();
		$schemaTool->createSchema($classes);
	}
}