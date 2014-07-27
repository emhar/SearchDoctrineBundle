<?php

namespace Emhar\SearchDoctrineBundle\Tests\Query\Driver;

use Emhar\SearchDoctrineBundle\Tests\BundleOrmTestCase;
use Emhar\SearchDoctrineBundle\Query\Driver\ORMDriver;
use Emhar\SearchDoctrineBundle\Mapping\ItemMetaData;

/**
 * AnnotationDriverTest.
 *
 * @author Emhar
 */
class ORMDriverTest extends BundleOrmTestCase
{

	/**
	 * Test if good informations are set
	 * about hit position and necessarity
	 *
	 * @dataProvider loadHitDefinitionsProvider
	 */
	public function testLoadDatabaseMapping($entityClass, $entityAttribute, $type, $table, $joins, $expression)
	{
		$itemMetaDataStub = $this->getMock('Emhar\SearchDoctrineBundle\Mapping\ItemMetaData');
		$itemMetaDataStub->expects($this->any())
				->method('getEntityIdentifiers')
				->will($this->returnValue(array('entityId')));
		$itemMetaDataStub->expects($this->any())
				->method('getEntityClass')
				->will($this->returnValue($entityClass));
		$itemMetaDataStub->expects($this->any())
				->method('getHitScoreFactor')
				->will($this->returnValue(1));
		$itemMetaDataStub->expects($this->any())
				->method('getHitEntityAttribute')
				->will($this->returnValue($entityAttribute));
		$itemMetaDataStub->expects($this->any())
				->method('getOrderedRequiredHits')
				->will($this->returnValue(array(ItemMetaData::SCORE, 'hit', 'hit1', ItemMetaData::TYPE)));

		$ormDriver = new ORMDriver($this->getEntityManager());
		$databaseMapping = $ormDriver->loadDatabaseMapping($itemMetaDataStub);
		$this->assertEquals($table, $databaseMapping['entityId']['table']);
		//hit1 position equals 2, see getOrderedRequiredHits return value in $itemMetaDataStub
		$this->assertEquals($type, $databaseMapping['entityId']['columns'][2]['type']);
		$this->assertEquals($expression, $databaseMapping['entityId']['columns'][2]['expression']);
		foreach($databaseMapping['entityId']['joins'] as $key => $join)
		{
			$this->assertEquals($joins[$key]['table'], $join['table']);
			$this->assertEquals($joins[$key]['onClause'], $join['onClause']);
		}
	}

	public function loadHitDefinitionsProvider()
	{
		return array(
			array(
				'Emhar\SearchDoctrineBundle\Tests\Models\Entity\Entity2',
				'integer',
				'integer',
				'entity2',
				array(),
				't0.integer'
			),
			array(
				'Emhar\SearchDoctrineBundle\Tests\Models\Entity\Entity1',
				'entity_2.integer',
				'integer',
				'entity1',
				array('entity_2' => array('table' => 'entity2', 'onClause' => 't0.entity_2_id=j0.id')),
				'j0.integer'
			),
			array(
				'Emhar\SearchDoctrineBundle\Tests\Models\Entity\Entity1',
				'entity_2.entity_1.string',
				'string',
				'entity1',
				array(
					'entity_2' => array('table' => 'entity2', 'onClause' => 't0.entity_2_id=j0.id'),
					'entity_2entity_1' => array('table' => 'entity1', 'onClause' => 'j0.entity_1_id=j1.id')
				),
				'j1.string'
			)
		);
	}
}
