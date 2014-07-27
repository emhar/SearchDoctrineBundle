<?php

namespace Emhar\SearchDoctrineBundle\Test\Mapping\Driver;

use PHPUnit_Framework_TestCase;
use Emhar\SearchDoctrineBundle\Mapping\Driver\AnnotationDriver;
use Doctrine\Common\Annotations\AnnotationReader;
use Emhar\SearchDoctrineBundle\Tests\Models\Item\Item1;
use Emhar\SearchDoctrineBundle\Tests\Models\Item\Item2;

/**
 * AnnotationDriverTest.
 *
 * @author Emhar
 */
class AnnotationDriverTest extends PHPUnit_Framework_TestCase
{

	/**
	 * Test if good informations are set
	 * about hit position and necessarity
	 *
	 * @dataProvider loadEntityDefinitionsProvider
	 */
	public function testLoadEntityDefinitions($itemClass, $entityIdentifier, $label, $entityClass)
	{
		$annotationDriver = new AnnotationDriver(new AnnotationReader());
		$entities = $annotationDriver->loadEntityDefinitions($itemClass);
		$this->assertEquals($label, $entities[$entityIdentifier]['label']);
		$this->assertEquals($entityClass, $entities[$entityIdentifier]['entityClass']);
	}

	public function loadEntityDefinitionsProvider()
	{
		return array(
			array(Item1::getClass(), 'entity1', 'Entity 1', 'Emhar\SearchDoctrineBundle\Tests\Models\Entity\Entity1'),
			array(Item2::getClass(), 'entity2', 'Entity 2', 'Emhar\SearchDoctrineBundle\Tests\Models\Entity\Entity2'),
		);
	}

	/**
	 * Test if good informations are set
	 * about hit position and necessarity
	 *
	 * @dataProvider loadHitDefinitionsProvider
	 */
	public function testLoadHitDefinitions($itemClass, $hitIdentifier, $scoreFactor, $sortable, $label, $mapping)
	{
		$annotationDriver = new AnnotationDriver(new AnnotationReader());
		$hits = $annotationDriver->loadHitDefinitions($itemClass);
		$this->assertEquals($scoreFactor, $hits[$hitIdentifier]['scoreFactor']);
		$this->assertEquals($sortable, $hits[$hitIdentifier]['sortable']);
		$this->assertEquals(count($mapping), count($hits[$hitIdentifier]['mapping']));
		$this->assertEquals($label, $hits[$hitIdentifier]['label']);
		foreach($hits[$hitIdentifier]['mapping'] as $entityIdentifier => $attribute)
		{
			$this->assertArrayHasKey($entityIdentifier, $mapping);
			$this->assertEquals($mapping[$entityIdentifier], $attribute);
		}
	}

	public function loadHitDefinitionsProvider()
	{
		return array(
			array(Item1::getClass(), 'id', 2, true, 'ID', array(
					'entity1' => 'id',
					'entity2' => 'id'
				)),
			array(Item2::getClass(), 'param', null, null, 'Param', array(
					'entity1' => 'string',
					'entity2' => 'integer'
				)),
		);
	}
}
