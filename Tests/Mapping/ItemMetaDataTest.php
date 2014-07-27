<?php

namespace Emhar\SearchDoctrineBundle\Tests\Mapping;

use PHPUnit_Framework_TestCase;
use Emhar\SearchDoctrineBundle\Mapping\ItemMetaData;
use Emhar\SearchDoctrineBundle\Tests\Models\Item\Item1;
use Emhar\SearchDoctrineBundle\Tests\Models\Item\Item2;

/**
 * ItemMetaDataTest.
 *
 * @author Emhar
 */
class ItemMetaDataTest extends PHPUnit_Framework_TestCase
{

	/**
	 * Test if good informations are set
	 * about hit position and necessarity
	 *
	 * @dataProvider initFromItemClassProvider
	 */
	public function testInitFromItemClass($itemClass, $scorePos, $typePos, $hitIdentifier, $hitPos, $isHitRequired)
	{
		$itemMetaData = ItemMetaData::initFromItemClass($itemClass);
		$hitPositions = $itemMetaData->getHitPositions();
		$this->assertEquals($scorePos, array_key_exists(ItemMetaData::SCORE, $hitPositions) ? $hitPositions[ItemMetaData::SCORE] : null);
		$this->assertEquals($typePos, array_key_exists(ItemMetaData::TYPE, $hitPositions) ? $hitPositions[ItemMetaData::TYPE] : null);
		$this->assertEquals($hitPos, $hitPositions[$hitIdentifier]);
		if($isHitRequired)
		{
			$this->assertContains($hitIdentifier, $itemMetaData->getOrderedRequiredHits());
		}
		else
		{
			$this->assertNotContains($hitIdentifier, $itemMetaData->getOrderedRequiredHits());
		}
	}

	public function initFromItemClassProvider()
	{
		return array(
			array(Item1::getClass(), 0, 1, 'id', 2, true),
			array(Item2::getClass(), null, null, 'param', 1, false)
		);
	}

	/**
	 * Test if good informations and default values are set
	 * about hit necessarity, score factor, sortability and entity mapping
	 *
	 * @dataProvider mapHitProvider
	 */
	public function testMapHit(array $hitDefinition)
	{
		$hitIdentifier = 'hit';
		$itemMetaData = new ItemMetaData();
		$itemMetaData->mapEntities(array(
			'A' => array('label' => 'A', 'entityClass' => 'A'),
			'B' => array('label' => 'B', 'entityClass' => 'B'),
			'C' => array('label' => 'C', 'entityClass' => 'C')
		));
		$itemMetaData->setHitPosition($hitIdentifier, 0);
		$itemMetaData->addRequiredHit($hitIdentifier, false);
		$itemMetaData->mapHits(array($hitIdentifier => $hitDefinition));

		$this->assertContains($hitIdentifier, $itemMetaData->getOrderedRequiredHits());
		$hitScoresFactors = $itemMetaData->getHitScoreFactors();
		if(isset($hitDefinition['scoreFactor']))
		{
			$this->assertEquals($hitDefinition['scoreFactor'], $hitScoresFactors[$hitIdentifier]);
		}
		else
		{
			$this->assertEquals(1, $hitScoresFactors[$hitIdentifier]);
		}
		if(isset($hitDefinition['sortable']) && $hitDefinition['sortable'] === true)
		{
			$this->assertContains($hitIdentifier, $itemMetaData->getSortableHits());
		}
		else
		{
			$this->assertNotContains($hitIdentifier, $itemMetaData->getSortableHits());
		}
		foreach($hitDefinition['mapping'] as $entityClass => $attribute)
		{
			$this->assertEquals($attribute, $itemMetaData->getHitEntityAttribute($hitIdentifier, $entityClass));
		}
	}

	public function mapHitProvider()
	{
		return array(
			array(array(
					'scoreFactor' => 3,
					'sortable' => true,
					'label' => 'Hit',
					'mapping' => array(
						'A' => 'B',
						'C' => 'B'
					)
				)),
			array(array(
					'scoreFactor' => 3,
					'sortable' => false,
					'label' => 'Hit',
					'mapping' => array(
						'A' => 'B',
						'C' => 'B'
					)
				)),
			array(array(
					'label' => 'Hit',
					'mapping' => array(
						'A' => 'B'
					)
				))
		);
	}
}
